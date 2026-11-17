<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sales Order di DB baru (koneksi mysql_mkt).
 * Upload Excel yang lolos pengecekan -> so_tmp. Save -> sales_orders, so_line, so_inv, so_production, so_po, lalu so_tmp dikosongkan.
 * Saat Create so_tmp dimiliki costing_id + user_id; saat mengubah SO yang sudah ada dimiliki sales_orders_id.
 * Tidak ada yang ditulis atau dibaca dari Signalbit; nomor SO dan WS dihitung dari nomor terakhir di DB baru.
 */
class MarketingSalesOrderService
{
    private const CONNECTION = 'mysql_mkt';
    private const REQUIRED_COLUMNS = ['PO', 'DESTINATION', 'EX FTY', 'PRODUCT SET', 'COLOR'];
    // Kolom Excel yang bukan size. DESC dan FOB belum punya tempat di DB baru, jadi tidak disimpan.
    // TOTAL / QTY / NO / REMARK sering ada di file buyer; tanpa ini kolom tersebut dibaca sebagai size dan ditolak "belum ada di master".
    private const NOT_SIZE_COLUMNS = ['PO', 'DESTINATION', 'EX FTY', 'PRODUCT SET', 'COLOR', 'DESC', 'FOB', 'STYLE', 'MARKET', 'TOTAL', 'NO', 'REMARK', 'SHIP MODE'];
    private const COLUMN_ALIASES = [
        'DESCRIPTION' => 'DESC',
        'PURCHASE ORDER' => 'PO',
        'NO PO' => 'PO',
        'PO NO' => 'PO',
        'EX FACTORY' => 'EX FTY',
        'EX FTY DATE' => 'EX FTY',
        'SET' => 'PRODUCT SET',
        'COLOUR' => 'COLOR',
        'STYLE NO' => 'STYLE',
        'TOTAL QTY' => 'TOTAL',
        'QTY' => 'TOTAL',
        'REMARKS' => 'REMARK',
        'NOTE' => 'REMARK',
        'NOTES' => 'REMARK',
        'KETERANGAN' => 'REMARK',
        'SHIPMODE' => 'SHIP MODE',
        'SHIPMENT MODE' => 'SHIP MODE',
        'SHIPPING MODE' => 'SHIP MODE',
    ];
    private const MAX_ERRORS_SHOWN = 30;
    // Tabel detail urut Set (TOP lalu BOTTOM), PO, Color, Size.
    private const SET_ORDER_SQL = "CASE UPPER(TRIM(pi.product_set)) WHEN 'TOP' THEN 1 WHEN 'BOTTOM' THEN 2 ELSE 3 END";

    /** SO yang belum tersimpan: id kosong, jadi so_tmp-nya dicari lewat Costing dan user yang login. */
    /** so_tmp.ship_modes_id ada setelah ALTER di nds_erd_penyesuaian.sql dijalankan. */
    private ?bool $shipReady = null;
    public function shipReady(): bool
    {
        return $this->shipReady ??= Schema::connection(self::CONNECTION)->hasColumn('so_tmp', 'ship_modes_id');
    }

    public function draft(int $costingId): object
    {
        return (object) ['id' => null, 'costing_id' => $costingId, 'so_no' => null];
    }

    private function tmp($db, object $so, string $alias = 'so_tmp')
    {
        $query = $db->table($alias === 'so_tmp' ? 'so_tmp' : "so_tmp as {$alias}");

        return $so->id
            ? $query->where("{$alias}.sales_orders_id", $so->id)
            : $query->whereNull("{$alias}.sales_orders_id")->where("{$alias}.costing_id", $so->costing_id)->where("{$alias}.user_id", Auth::id());
    }

    // Save dan upload yang bersamaan antre di baris SO, atau baris Costing kalau SO belum ada.
    private function lock($db, object $so): void
    {
        $so->id
            ? $db->table('sales_orders')->where('id', $so->id)->lockForUpdate()->first()
            : $db->table('costings')->where('id', $so->costing_id)->lockForUpdate()->first();
    }

    public function find(int $id): ?object
    {
        return DB::connection(self::CONNECTION)->table('sales_orders')->where('id', $id)->first();
    }

    /**
     * Costing beserta buyer dan set-nya. Kode WS memakai short name buyer, selain itu kode partner.
     */
    public function costing(int $costingId): object
    {
        $db = DB::connection(self::CONNECTION);
        $costing = $db->table('costings as c')
            ->leftJoin('master_brands as br', 'br.id', '=', 'c.brand_id')
            ->leftJoin('master_seasons as se', 'se.id', '=', 'c.season_id')
            ->leftJoin('master_units as u', 'u.id', '=', 'c.unit_id')
            ->leftJoin('master_destinations as d', 'd.id', '=', 'c.main_destination_id')
            ->leftJoin('master_marketing_office as o', 'o.id', '=', 'c.marketing_office_id')
            ->where('c.id', $costingId)
            ->first(['c.*', 'br.brand_name', 'se.season_name', 'u.unit_code', 'd.destination_name', 'o.office_name']);
        $buyer = $db->table('master_partners')->where('id', $costing->buyer_id)->first(['name', 'code', 'short_name']);
        $costing->buyer = $buyer->name ?? null;
        $costing->supplier_code = $buyer ? ($buyer->short_name ?: $buyer->code) : null;
        $sets = app(MarketingNdsCostingService::class)->costingSets($costingId);
        $costing->sets = $sets['sets'];
        $costing->shared_sets = $sets['shared'];

        return $costing;
    }

    /**
     * Mengganti isi so_tmp milik SO ini dengan isi Excel. so_tmp hanya menyimpan id master, jadi seluruh file harus lolos
     * pengecekan dulu. Mengembalikan daftar error; kosong berarti tersimpan.
     */
    /** Isi kolom utama tiap baris Excel dari upload terakhir (nomor baris => isi), untuk menampilkan error di baris yang tepat. */
    public array $uploadRows = [];

    /**
     * Label kolom => index dari satu baris header. Nama kolom dicocokkan longgar ("Ex-Fty", "EX  FTY", "No. PO", ganti baris di sel),
     * sedangkan label size dipakai apa adanya supaya "L+" tidak berubah jadi "L".
     */
    private function headerColumns(array $headers): array
    {
        $known = array_merge(self::NOT_SIZE_COLUMNS, array_keys(self::COLUMN_ALIASES));
        $loose = fn ($text) => trim(preg_replace('/[^A-Z0-9]+/', ' ', $text));
        $knownByLoose = [];
        foreach ($known as $name) {
            $knownByLoose[$loose($name)] = self::COLUMN_ALIASES[$name] ?? $name;
        }

        $columns = [];
        foreach ($headers as $index => $header) {
            $label = strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $header)));
            if ($label === '') {
                continue;
            }
            $label = $knownByLoose[$loose($label)] ?? $label;
            if (!isset($columns[$label])) {
                $columns[$label] = $index;
            }
        }
        // Market = Destination. Template lama punya dua kolom ini; Destination yang kosong memakai isi Market.
        if (!isset($columns['DESTINATION']) && isset($columns['MARKET'])) {
            $columns['DESTINATION'] = $columns['MARKET'];
        }

        return $columns;
    }

    public function upload(object $so, string $path): array
    {
        $this->uploadRows = [];
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);

        // Header tidak harus di baris 1 (file buyer sering punya judul / baris kosong di atasnya) dan tidak harus di sheet aktif:
        // sheet aktif dicek dulu, lalu sheet lain; header = baris pertama (maks. 30 baris teratas) yang memuat kolom wajib terbanyak.
        $sheets = collect($book->getAllSheets())->sortBy(fn ($s) => $s === $book->getActiveSheet() ? 0 : 1)->values();
        $best = null;
        foreach ($sheets as $sheet) {
            $data = $sheet->toArray();
            foreach (array_slice($data, 0, 30, true) as $rowIndex => $row) {
                $columns = $this->headerColumns($row);
                $found = count(array_intersect(self::REQUIRED_COLUMNS, array_keys($columns)));
                if ($found && (!$best || $found > $best['found'])) {
                    $best = ['sheet' => $sheet, 'data' => $data, 'header' => $rowIndex, 'columns' => $columns, 'found' => $found];
                }
                if ($found === count(self::REQUIRED_COLUMNS)) {
                    break 2;
                }
            }
        }
        if (!$best) {
            return ['Format Excel tidak sesuai. Header kolom PO, DESTINATION, EX FTY, PRODUCT SET, COLOR tidak ditemukan. Pakai file dari tombol Download Template Excel.'];
        }
        $missing = array_values(array_diff(self::REQUIRED_COLUMNS, array_keys($best['columns'])));
        if ($missing) {
            return ['Format Excel tidak sesuai. Kolom yang belum ada di header (baris ' . ($best['header'] + 1) . ', sheet "' . $best['sheet']->getTitle() . '"): ' . implode(', ', $missing)];
        }
        $columns = $best['columns'];
        $headerIndex = $best['header'];
        $data = $best['data'];
        // Ex Fty dibaca dari nilai asli sel supaya tidak bergantung pada format tampilan tanggalnya.
        $rawData = $best['sheet']->toArray(null, true, false);

        $sizeColumns = array_filter($columns, fn ($label) => !in_array($label, self::NOT_SIZE_COLUMNS, true), ARRAY_FILTER_USE_KEY);
        $cell = fn (array $row, string $column) => isset($columns[$column]) ? trim((string) ($row[$columns[$column]] ?? '')) : null;

        $rows = [];
        $errors = [];
        foreach (array_slice($data, $headerIndex + 1) as $offset => $row) {
            if (!array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '')) {
                continue;
            }
            // Nomor baris Excel yang sebenarnya (header boleh tidak di baris 1).
            $rowNo = $headerIndex + $offset + 2;
            $exFty = $rawData[$headerIndex + $offset + 1][$columns['EX FTY']] ?? '';
            $this->uploadRows[$rowNo] = [
                'po' => $cell($row, 'PO'),
                'product_set' => $cell($row, 'PRODUCT SET'),
                'color' => $cell($row, 'COLOR'),
                'destination' => $cell($row, 'DESTINATION') ?: $cell($row, 'MARKET'),
                'ex_fty' => $cell($row, 'EX FTY'),
            ];
            $rawRow = $rawData[$headerIndex + $offset + 1] ?? [];
            $rowHasQty = false;
            foreach ($sizeColumns as $size => $index) {
                // Qty dibaca dari nilai asli sel: tampilan format "#,##0" membulatkan 425,5 jadi 426 tanpa ketahuan.
                $raw = $rawRow[$index] ?? null;
                $qty = is_int($raw) || is_float($raw)
                    ? (floor($raw) == $raw ? (string) (int) $raw : (string) $raw)
                    : str_replace(',', '', preg_replace('/[\s\x{A0}]+/u', '', (string) ($row[$index] ?? '')));
                if ($qty === '' || (is_numeric($qty) && (float) $qty === 0.0)) {
                    continue;
                }
                $rowHasQty = true;
                if (!ctype_digit($qty)) {
                    $errors[] = "Baris {$rowNo} size {$size}: qty \"{$qty}\" harus bilangan bulat.";
                    continue;
                }
                $rows[] = [
                    'row_no' => $rowNo,
                    'style' => $cell($row, 'STYLE'),
                    'po' => $cell($row, 'PO'),
                    'destination' => $cell($row, 'DESTINATION') ?: $cell($row, 'MARKET'),
                    'ex_fty' => $exFty,
                    'product_set' => $cell($row, 'PRODUCT SET'),
                    'ship_mode' => (string) $cell($row, 'SHIP MODE'),
                    'color' => $cell($row, 'COLOR'),
                    'size' => (string) $size,
                    'qty' => (int) $qty,
                ];
            }
            // Baris berisi PO / Color tapi semua kolom size kosong dulu dilewati diam-diam, jadi qty-nya hilang tanpa ketahuan.
            if (!$rowHasQty && ($cell($row, 'PO') !== '' || $cell($row, 'COLOR') !== '')) {
                $errors[] = "Baris {$rowNo}: belum ada qty di kolom size (" . implode(', ', array_keys($sizeColumns)) . ').';
            }
        }
        if (!$sizeColumns) {
            return ['Format Excel tidak sesuai. Belum ada kolom size (mis. S, M, L, XL) di header.'];
        }
        if (!$errors && !$rows) {
            $errors[] = 'Tidak ada baris dengan qty di file Excel.';
        }

        // Error qty dan error isi (master, set, dobel) ditampilkan bersamaan supaya user cukup sekali memperbaiki file.
        $checked = $this->check($so, $rows, !$errors);
        if ($errors || $checked['errors']) {
            return $this->limitErrors(array_merge($errors, $checked['errors']));
        }

        $now = now();
        $records = array_map(fn ($row) => [
            'sales_orders_id' => $so->id,
            'costing_id' => $so->costing_id,
            'user_id' => Auth::id(),
            'buyer_po_no' => $row['po'],
            'destination_id' => $row['destination_id'],
            'delivery_date' => $row['delivery_date'],
            'color_id' => $row['color_id'],
            'size_id' => $row['size_id'],
            'qty' => $row['qty'],
            'costing_set_id' => $row['set_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ] + ($this->shipReady() ? ['ship_modes_id' => $row['ship_mode_id']] : []), $checked['rows']);

        DB::connection(self::CONNECTION)->transaction(function ($db) use ($so, $records) {
            $this->lock($db, $so);
            $this->tmp($db, $so)->delete();
            foreach (array_chunk($records, 500) as $chunk) {
                $db->table('so_tmp')->insert($chunk);
            }
        });

        return [];
    }

    /**
     * Membuka SO yang sudah tersimpan untuk diedit: isi so_po dimuat ke so_tmp, jadi tabelnya bisa diubah dan baru berlaku saat Save.
     * Kalau so_tmp sudah berisi (upload atau editan yang belum disimpan), isinya dipertahankan.
     * true = so_tmp baru saja disalin dari data tersimpan (belum ada editan), jadi boleh dibuang saat halaman ditinggal.
     */
    public function startEdit(object $so): bool
    {
        return DB::connection(self::CONNECTION)->transaction(function ($db) use ($so) {
            $this->lock($db, $so);
            if ($this->tmp($db, $so)->exists()) {
                return false;
            }
            $now = now();
            $rows = $db->table('so_po as p')
                ->join('so_production as sp', 'sp.id', '=', 'p.so_production_id')
                ->join('so_line as l', 'l.id', '=', 'sp.so_line_id')
                ->where('l.sales_order_id', $so->id)
                ->where(fn ($q) => $q->whereNull('p.cancel')->orWhere('p.cancel', '!=', 'Y'))
                ->where(fn ($q) => $q->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y'))
                ->orderBy('p.id')
                ->get(['p.buyer_po_no', 'p.destination_id', 'p.delivery_date', 'p.ship_modes_id', 'sp.color_id', 'sp.sizes_id', 'p.qty', 'l.costing_set_id'])
                ->map(fn ($row) => [
                    'sales_orders_id' => $so->id,
                    'costing_id' => $so->costing_id,
                    'user_id' => Auth::id(),
                    'buyer_po_no' => $row->buyer_po_no,
                    'destination_id' => $row->destination_id,
                    'delivery_date' => $row->delivery_date,
                    'color_id' => $row->color_id,
                    'size_id' => $row->sizes_id,
                    'qty' => $row->qty,
                    'costing_set_id' => $row->costing_set_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ] + ($this->shipReady() ? ['ship_modes_id' => $row->ship_modes_id] : []))->all();
            foreach (array_chunk($rows, 500) as $chunk) {
                $db->table('so_tmp')->insert($chunk);
            }

            return true;
        });
    }

    /**
     * Membuang hasil upload atau editan yang belum disimpan. Data yang sudah tersimpan tidak berubah.
     */
    public function discard(object $so): void
    {
        $db = DB::connection(self::CONNECTION);
        $this->tmp($db, $so)->delete();
    }

    /** WS milik SO beserta set, item, dan total qty-nya; TOP lebih dulu. Dipakai untuk pesan setelah Save. */
    public function worksheetSummary(int $soId): array
    {
        return DB::connection(self::CONNECTION)->table('so_line as l')
            ->leftJoin('costing_set as cs', 'cs.id', '=', 'l.costing_set_id')
            ->leftJoin('master_product_items as pi', 'pi.id', '=', 'cs.product_items_id')
            ->leftJoin('so_production as sp', fn ($j) => $j->on('sp.so_line_id', '=', 'l.id')
                ->where(fn ($w) => $w->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y')))
            ->where('l.sales_order_id', $soId)
            ->groupBy('l.id', 'l.no_ws', 'l.costing_set_id', 'pi.product_set', 'pi.product_item_name')
            ->orderByRaw(self::SET_ORDER_SQL)
            ->orderBy('l.id')
            ->get(['l.no_ws', 'l.costing_set_id', 'pi.product_set', 'pi.product_item_name', DB::raw('COALESCE(SUM(sp.qty), 0) as qty')])
            ->map(fn ($line) => [
                'no_ws' => $line->no_ws,
                'costing_set_id' => (int) $line->costing_set_id,
                'set' => strtoupper(trim((string) $line->product_set)),
                'item' => $line->product_item_name,
                'qty' => (float) $line->qty,
            ])
            ->all();
    }

    /**
     * Baris tabel Sales Order Detail (satu baris = satu color + size): hasil upload bila masih di so_tmp, selain itu yang sudah tersimpan.
     */
    public function rows(object $so): array
    {
        $db = DB::connection(self::CONNECTION);
        $setNames = $this->costing((int) $so->costing_id)->sets->pluck('set_type', 'id');
        // Cukup No WS saja (tanpa keterangan set TOP / BOTTOM).
        $lines = $so->id ? $this->worksheetSummary((int) $so->id) : [];
        $worksheets = array_map(fn ($ws) => $ws['no_ws'], $lines);
        // Nama set (sama dengan kolom Product Set di tabel) => No WS yang sudah ada.
        $worksheetBySet = collect($lines)->mapWithKeys(fn ($ws) => [strtoupper((string) ($setNames[$ws['costing_set_id']] ?? $ws['set'])) => $ws['no_ws']])->all();

        $temp = $this->tmp($db, $so, 't')
            ->leftJoin('master_colors as c', 'c.id', '=', 't.color_id')
            ->leftJoin('master_sizes as s', 's.id', '=', 't.size_id')
            ->leftJoin('master_destinations as d', 'd.id', '=', 't.destination_id')
            ->when($this->shipReady(), fn ($q) => $q->leftJoin('master_ship_modes as sm', 'sm.id', '=', 't.ship_modes_id'))
            ->leftJoin('costing_set as cs', 'cs.id', '=', 't.costing_set_id')
            ->leftJoin('master_product_items as pi', 'pi.id', '=', 'cs.product_items_id')
            // Baris baru yang Color/Size-nya belum diisi di paling bawah, supaya tombol Add tetap membawa ke baris itu.
            ->orderByRaw('t.color_id IS NULL OR t.size_id IS NULL')
            ->orderByRaw(self::SET_ORDER_SQL)
            ->orderBy('t.costing_set_id')->orderBy('c.color_name')->orderByRaw('CAST(s.urutan AS UNSIGNED)')->orderBy('s.size_name')
            ->orderBy('t.buyer_po_no')->orderBy('d.destination_name')->orderBy('t.delivery_date')->orderBy('t.id')
            ->get(array_merge(['t.id', 't.buyer_po_no as po', 't.delivery_date as ex_fty', 't.qty', 't.costing_set_id', 'c.color_name as color', 's.size_name as size', 'd.destination_name as destination_name', 'd.destination_code as destination'],
                $this->shipReady() ? ['sm.ship_mode_name as ship_mode'] : []));
        $isTemp = $temp->isNotEmpty();

        $rows = $isTemp ? $temp : $db->table('so_po as p')
            ->join('so_production as sp', 'sp.id', '=', 'p.so_production_id')
            ->join('so_line as l', 'l.id', '=', 'sp.so_line_id')
            ->join('master_colors as c', 'c.id', '=', 'sp.color_id')
            ->join('master_sizes as s', 's.id', '=', 'sp.sizes_id')
            ->join('master_destinations as d', 'd.id', '=', 'p.destination_id')
            ->leftJoin('master_ship_modes as sm', 'sm.id', '=', 'p.ship_modes_id')
            ->where('l.sales_order_id', $so->id)
            ->where(fn ($q) => $q->whereNull('p.cancel')->orWhere('p.cancel', '!=', 'Y'))
            ->where(fn ($q) => $q->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y'))
            ->leftJoin('costing_set as cs', 'cs.id', '=', 'l.costing_set_id')
            ->leftJoin('master_product_items as pi', 'pi.id', '=', 'cs.product_items_id')
            ->orderByRaw(self::SET_ORDER_SQL)
            ->orderBy('l.costing_set_id')->orderBy('c.color_name')->orderByRaw('CAST(s.urutan AS UNSIGNED)')->orderBy('s.size_name')
            ->orderBy('p.buyer_po_no')->orderBy('d.destination_name')->orderBy('p.delivery_date')->orderBy('p.id')
            ->get(['p.id', 'p.buyer_po_no as po', 'p.delivery_date as ex_fty', 'p.qty', 'l.costing_set_id', 'c.color_name as color', 's.size_name as size', 'd.destination_name as destination_name', 'd.destination_code as destination', 'sm.ship_mode_name as ship_mode']);

        return [
            'mode' => $isTemp ? 'temp' : 'saved',
            'worksheets' => $worksheets,
            'worksheet_by_set' => (object) $worksheetBySet,
            'rows' => $rows->map(fn ($row) => [
                'id' => (int) $row->id,
                'po' => $row->po,
                'destination' => $row->destination,
                'ex_fty' => $row->ex_fty,
                'ship_mode' => $row->ship_mode ?? null,
                'product_set' => $setNames[$row->costing_set_id] ?? null,
                'color' => $row->color,
                'size' => $row->size,
                'qty' => (int) $row->qty,
            ])->values()->all(),
        ];
    }

    /**
     * Edit sel tabel: changes[] = {id, field, value}. so_tmp menyimpan id master, jadi nilai dicocokkan dulu. Mengembalikan daftar sel yang gagal.
     */
    public function updateCells(object $so, array $changes): array
    {
        $db = DB::connection(self::CONNECTION);
        $lookup = $this->lookups($this->costing((int) $so->costing_id));
        $errors = [];
        $db->transaction(function ($db) use ($so, $changes, $lookup, &$errors) {
            foreach ($changes as $index => $change) {
                $field = $change['field'];
                $value = trim((string) ($change['value'] ?? ''));
                $fail = function ($message) use (&$errors, $index, $field) {
                    $errors[] = ['index' => $index, 'field' => $field, 'message' => $message];
                };
                $query = $this->tmp($db, $so)->where('id', (int) $change['id']);
                if (!(clone $query)->exists()) {
                    $fail('Baris tidak ditemukan.');
                    continue;
                }
                if ($value === '') {
                    $fail('Wajib diisi.');
                    continue;
                }
                $key = strtoupper($value);
                switch ($field) {
                    case 'po':
                        $update = ['buyer_po_no' => $value];
                        break;
                    case 'destination':
                        $update = ['destination_id' => $lookup['destinations'][$key] ?? null];
                        break;
                    case 'ex_fty':
                        $update = ['delivery_date' => self::parseDate($value)];
                        break;
                    case 'ship_mode':
                        if (!$this->shipReady()) {
                            $fail('Kolom Ship Mode belum ada di so_tmp. Jalankan SQL penyesuaian dulu.');
                            continue 2;
                        }
                        $update = ['ship_modes_id' => $lookup['ship_modes'][$key] ?? null];
                        break;
                    case 'color':
                        $update = ['color_id' => $lookup['colors'][$key] ?? null];
                        break;
                    case 'size':
                        $update = ['size_id' => $lookup['sizes'][self::sizeKey($value)] ?? null];
                        break;
                    case 'product_set':
                        if (isset($lookup['shared'][$key])) {
                            $fail("{$key} dipakai " . implode(' dan ', $lookup['shared'][$key]) . '. Tulis nama itemnya.');
                            continue 2;
                        }
                        $update = ['costing_set_id' => $lookup['sets'][$key] ?? $lookup['items'][$key] ?? null];
                        break;
                    case 'qty':
                        $qty = str_replace([',', '.'], '', $value);
                        $update = ['qty' => ctype_digit($qty) && (int) $qty > 0 ? (int) $qty : null];
                        break;
                    default:
                        $fail('Kolom tidak bisa diedit.');
                        continue 2;
                }
                if (reset($update) === null) {
                    $fail($field === 'qty' ? 'Qty harus angka bulat lebih dari 0.' : ($field === 'ex_fty' ? 'Ex Fty bukan tanggal yang valid.' : 'Nilai tidak ada di master.'));
                    continue;
                }
                $query->update($update + ['updated_at' => now()]);
            }
        });

        return $errors;
    }

    /**
     * Tombol Add: menambah satu baris kosong di so_tmp; semua kolom diisi di tabel.
     */
    public function addRow(object $so, ?int $sourceId): ?int
    {
        $db = DB::connection(self::CONNECTION);
        // Baris baru selalu kosong (tidak menyalin baris lain); Product Set langsung terisi kalau Costing hanya punya satu set.
        $sets = $this->costing((int) $so->costing_id)->sets;
        $base = (object) [
            'buyer_po_no' => null,
            'destination_id' => null,
            'delivery_date' => null,
            'ship_modes_id' => null,
            'costing_set_id' => $sets->count() === 1 ? (int) $sets->first()->id : null,
        ];

        return (int) $db->table('so_tmp')->insertGetId([
            'sales_orders_id' => $so->id,
            'costing_id' => $so->costing_id,
            'user_id' => Auth::id(),
            'buyer_po_no' => $base->buyer_po_no,
            'destination_id' => $base->destination_id,
            'delivery_date' => $base->delivery_date,
            'costing_set_id' => $base->costing_set_id,
            'qty' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ] + ($this->shipReady() ? ['ship_modes_id' => $base->ship_modes_id ?? null] : []));
    }

    public function deleteRows(object $so, array $ids): int
    {
        $db = DB::connection(self::CONNECTION);

        return $this->tmp($db, $so)->whereIn('id', $ids)->delete();
    }

    /** Nilai Product Set yang diterima: nama set dan nama item, huruf besar. */
    public function setNames(object $costing): array
    {
        return $costing->sets->flatMap(fn ($set) => [$set->set_type, $set->product_item_name])
            ->map(fn ($name) => strtoupper(trim((string) $name)))->filter()->unique()->values()->all();
    }

    /** Nama per set sesuai urutan Costing (set pertama = acuan, mis. TOP): [[SET_TYPE, PRODUCT ITEM], ...]. */
    public function setGroups(object $costing): array
    {
        return $costing->sets->map(fn ($set) => collect([$set->set_type, $set->product_item_name])
            ->map(fn ($name) => strtoupper(trim((string) $name)))->filter()->unique()->values()->all())
            ->filter()->values()->all();
    }

    /** URL gambar Costing untuk kartu Review Image. */
    public function costingImages(int $costingId): array
    {
        return DB::connection(self::CONNECTION)->table('costing_images')->where('costings_id', $costingId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))->orderBy('id')->pluck('file_path')
            ->map(fn ($path) => '/nds_wip/public/uploads/costing/' . rawurlencode(basename($path)))->values()->all();
    }

    /** Peta nama ke id untuk mencocokkan isi Excel dan sel tabel. */
    private function lookups(object $costing): array
    {
        $db = DB::connection(self::CONNECTION);
        $active = fn ($table) => $db->table($table)->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'));
        $upperKeys = fn ($map) => collect($map)->mapWithKeys(fn ($id, $name) => [strtoupper(trim((string) $name)) => $id]);

        return [
            'colors' => $upperKeys($active('master_colors')->pluck('id', 'color_name')),
            'sizes' => $active('master_sizes')->pluck('id', 'size_name')->mapWithKeys(fn ($id, $name) => [self::sizeKey($name) => $id]),
            // Kode destinasi unik, nama boleh sama di negara lain (mis. RETAIL). Nama hanya dipakai kalau tidak dobel.
            'destinations' => $upperKeys($active('master_destinations')->pluck('id', 'destination_code'))
                ->union($active('master_destinations')->get(['id', 'destination_name'])
                    ->groupBy(fn ($d) => strtoupper(trim((string) $d->destination_name)))
                    ->filter(fn ($group) => $group->count() === 1)->map(fn ($group) => $group->first()->id)),
            'sets' => $upperKeys($costing->sets->pluck('id', 'set_type')),
            'items' => $upperKeys($costing->sets->pluck('id', 'product_item_name')),
            'shared' => $costing->shared_sets,
            'ship_modes' => $upperKeys($active('master_ship_modes')->pluck('id', 'ship_mode_name')),
        ];
    }

    /** Pengecekan isi so_tmp sebelum Save: baris lengkap, tidak dobel, dan qty antar komponen sama. */
    private function tempIssues(object $costing, $temp): array
    {
        $issues = [];
        $incomplete = $temp->filter(fn ($row) => !$row->color_id || !$row->size_id || !$row->destination_id || !$row->delivery_date || !$row->costing_set_id || !$row->buyer_po_no || (int) $row->qty <= 0
            || ($this->shipReady() && !$row->ship_modes_id))->count();
        if ($incomplete) {
            $issues[] = "Masih ada {$incomplete} baris yang belum lengkap di Sales Order Detail.";

            return $issues;
        }
        $duplicates = $temp->groupBy(fn ($row) => json_encode([$row->costing_set_id, $row->color_id, $row->size_id, $row->buyer_po_no, $row->destination_id, $row->delivery_date]))
            ->filter(fn ($group) => $group->count() > 1)->count();
        if ($duplicates) {
            $issues[] = "Ada {$duplicates} Color + Size yang dobel untuk PO yang sama.";
        }
        if ($costing->sets->count() > 1) {
            $names = $costing->sets->pluck('set_type', 'id');
            foreach ($temp->groupBy(fn ($row) => $row->color_id . '_' . $row->size_id) as $group) {
                $totals = $names->map(fn ($name, $setId) => $group->where('costing_set_id', $setId)->sum('qty'));
                if ($totals->unique()->count() > 1) {
                    $text = $names->map(fn ($name, $setId) => "{$name} {$totals[$setId]}")->implode(', ');
                    $issues[] = "Qty antar komponen harus sama ({$text}).";
                }
            }
            // Ship Mode set lain boleh dipilih sendiri, tapi harus sama dengan pasangannya di set lain.
            if ($this->shipReady()) {
                $shipDiff = $temp->groupBy(fn ($row) => json_encode([strtoupper(trim((string) $row->buyer_po_no)), (int) $row->destination_id, (string) $row->delivery_date, (int) $row->color_id, (int) $row->size_id]))
                    ->filter(fn ($group) => $group->pluck('ship_modes_id')->map(fn ($v) => (string) $v)->unique()->count() > 1)->count();
                if ($shipDiff) {
                    $issues[] = "Ada {$shipDiff} Color + Size yang Ship Mode-nya beda antar " . $names->implode(' dan ') . '.';
                }
            }
        }

        return array_values(array_unique($issues));
    }

    /**
     * Memindahkan so_tmp ke so_line, so_inv, so_production, dan so_po. Mengembalikan daftar error; kosong berarti tersimpan.
     * SO yang belum ada dibuat di sini dari $header, jadi kalau detail gagal tidak ada header yang tertinggal.
     * Baris so_line lama dipakai lagi supaya nomor WS tetap; turunannya ditulis ulang dari upload terakhir.
     */
    public function commit(object $so, ?int $shipModeId = null, array $header = []): array
    {
        $saved = false;
        $isNew = !$so->id;
        $errors = DB::connection(self::CONNECTION)->transaction(function ($db) use ($so, $shipModeId, $header, $isNew, &$saved) {
            // Save yang bersamaan antre di sini; yang kedua mendapati so_tmp sudah kosong dan tidak menulis apa pun.
            $this->lock($db, $so);

            $temp = $this->tmp($db, $so)->orderBy('id')->get();
            if ($temp->isEmpty()) {
                return $isNew ? ['Upload file SO dulu sebelum menyimpan.'] : [];
            }
            if ($issues = app(MarketingNdsCostingService::class)->cmtIssues((int) $so->costing_id)) {
                return $issues;
            }

            $costing = $this->costing((int) $so->costing_id);
            if ($issues = $this->tempIssues($costing, $temp)) {
                return $issues;
            }
            if (!$this->shipReady() && !$shipModeId) {
                return ['Kolom Ship Mode belum ada di so_tmp. Jalankan SQL penyesuaian (nds_erd_penyesuaian.sql) dulu.'];
            }
            $lineIds = $isNew ? collect() : $db->table('so_line')->where('sales_order_id', $so->id)->pluck('id', 'costing_set_id');
            $missingSets = $temp->pluck('costing_set_id')->unique()->reject(fn ($setId) => isset($lineIds[$setId]));
            if ($missingSets->isNotEmpty() && trim((string) $costing->supplier_code) === '') {
                return ['Buyer ' . ($costing->buyer ?: '-') . ' belum punya kode (Short Name / Code) di master buyer, padahal kode ini dipakai untuk nomor WS. Isi dulu kodenya di master buyer, lalu Save lagi.'];
            }

            $now = now();
            if ($isNew) {
                $so->so_no = app(MarketingNdsCostingService::class)->nextNumber('sales_orders', 'so_no', 'SO', 5);
                $so->id = $db->table('sales_orders')->insertGetId($header + [
                    'so_no' => $so->so_no,
                    'costing_id' => $so->costing_id,
                    'status' => 'saved',
                    'created_at' => $now,
                    'updated_at' => $now,
                ] + MarketingNdsCostingService::creator('sales_orders'));
            }
            // Nomor dihitung satu per satu dari baris yang sudah tersimpan, jadi tiap set mendapat WS berbeda.
            foreach ($missingSets as $setId) {
                $lineIds[$setId] = $db->table('so_line')->insertGetId([
                    'no_ws' => $this->nextWorksheet($costing->supplier_code),
                    'costing_set_id' => $setId,
                    'sales_order_id' => $so->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ] + MarketingNdsCostingService::creator('so_line'));
            }

            // Detail lama tidak dihapus permanen (aturan marketing): di-cancel lalu ditulis ulang dari so_tmp.
            $active = fn ($q) => $q->where(fn ($w) => $w->whereNull('cancel')->orWhere('cancel', '!=', 'Y'));
            $productionIds = $active($db->table('so_production')->whereIn('so_line_id', $lineIds->values()))->pluck('id');
            $active($db->table('so_po')->whereIn('so_production_id', $productionIds))->update(['cancel' => 'Y', 'updated_at' => $now]);
            $active($db->table('so_production')->whereIn('id', $productionIds))->update(['cancel' => 'Y', 'updated_at' => $now]);
            $active($db->table('so_inv')->where('sales_orders_id', $so->id))->update(['cancel' => 'Y', 'updated_at' => $now]);

            // 1 set = 1 komponen per warna/ukuran dan qty antar komponen sudah dicek sama saat upload, jadi so_inv memakai qty satu komponen.
            foreach ($temp->groupBy(fn ($row) => $row->color_id . '_' . $row->size_id) as $rows) {
                $first = $rows->first();
                $invId = $db->table('so_inv')->insertGetId([
                    'sales_orders_id' => $so->id,
                    'color_id' => $first->color_id,
                    'size_id' => $first->size_id,
                    'qty' => $rows->groupBy('costing_set_id')->first()->sum('qty'),
                    'cancel' => 'N',
                    'created_at' => $now,
                    'updated_at' => $now,
                ] + MarketingNdsCostingService::creator('so_inv'));
                foreach ($rows->groupBy('costing_set_id') as $setId => $setRows) {
                    $productionId = $db->table('so_production')->insertGetId([
                        'so_line_id' => $lineIds[$setId],
                        'color_id' => $first->color_id,
                        'sizes_id' => $first->size_id,
                        'so_inv_id' => $invId,
                        'qty' => $setRows->sum('qty'),
                        'cancel' => 'N',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ] + MarketingNdsCostingService::creator('so_production'));
                    $db->table('so_po')->insert($setRows->map(fn ($row) => [
                        'so_production_id' => $productionId,
                        'buyer_po_no' => $row->buyer_po_no,
                        'delivery_date' => $row->delivery_date,
                        'qty' => $row->qty,
                        'ship_modes_id' => $row->ship_modes_id ?? $shipModeId,
                        'destination_id' => $row->destination_id,
                        'cancel' => 'N',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ] + MarketingNdsCostingService::creator('so_po'))->all());
                }
            }

            $db->table('so_tmp')->whereIn('id', $temp->pluck('id'))->delete();
            $db->table('sales_orders')->where('id', $so->id)->update(['status' => 'saved', 'updated_at' => $now]);
            $saved = true;

            return [];
        });

        if ($saved) {
            if ($isNew) {
                app(MarketingHistoryService::class)->salesOrder((int) $so->costing_id, (int) $so->id, 'created', MarketingHistoryService::actor() . " created sales order {$so->so_no}");
            }
            app(MarketingHistoryService::class)->salesOrder((int) $so->costing_id, (int) $so->id, 'details_saved',
                MarketingHistoryService::actor() . " saved sales order {$so->so_no} details");
        }

        return $errors;
    }

    /**
     * Mencocokkan baris Excel ke master dan set Costing.
     * Satu baris Excel tanpa Product Set pada Costing set berlaku untuk semua komponen. Product Set boleh tipe set atau nama item.
     */
    private function check(object $so, array $items, bool $compareSets = true): array
    {
        $costing = $this->costing((int) $so->costing_id);
        $lookup = $this->lookups($costing);
        $colors = $lookup['colors'];
        $sizes = $lookup['sizes'];
        $destinations = $lookup['destinations'];
        $sets = $lookup['sets'];
        $itemNames = $lookup['items'];

        $errors = [];
        if ($sets->isEmpty()) {
            $errors[] = 'Costing belum punya set di DB baru.';
        }


        $rows = [];
        foreach ($items as $item) {
            $messages = [];
            $style = strtoupper(trim((string) $item['style']));
            if ($style !== '' && $style !== strtoupper(trim((string) $costing->style))) {
                $messages[] = "Style \"{$item['style']}\" berbeda dengan Costing.";
            }
            if ($item['po'] === '') {
                $messages[] = 'No PO kosong.';
            }
            $colorId = $colors[strtoupper($item['color'])] ?? null;
            if (!$colorId) {
                $messages[] = "Color \"{$item['color']}\" belum ada di master.";
            }
            $sizeId = $sizes[self::sizeKey($item['size'])] ?? null;
            if (!$sizeId) {
                $messages[] = "Size \"{$item['size']}\" belum ada di master.";
            }
            $destinationId = (string) $item['destination'] === '' ? $costing->main_destination_id : ($destinations[strtoupper($item['destination'])] ?? null);
            if (!$destinationId) {
                $messages[] = (string) $item['destination'] === ''
                    ? 'Destination kosong dan Main Destination Costing belum diisi.'
                    : "Destination \"{$item['destination']}\" belum ada di master atau namanya dipakai di beberapa negara (tulis kode destinasinya).";
            }
            $shipModeId = null;
            if (($item['ship_mode'] ?? '') !== '') {
                $shipModeId = $lookup['ship_modes'][strtoupper($item['ship_mode'])] ?? null;
                if (!$shipModeId) {
                    $messages[] = "Ship Mode \"{$item['ship_mode']}\" belum ada di master.";
                }
            }
            $deliveryDate = self::parseDate($item['ex_fty']);
            if (!$deliveryDate) {
                $messages[] = "Ex Fty \"{$item['ex_fty']}\" bukan tanggal yang valid.";
            }

            $setName = strtoupper($item['product_set']);
            $setName = in_array($setName, ['-', 'NULL'], true) ? '' : $setName;
            if ($setName === '') {
                $setIds = $sets->values()->all();
            } elseif (isset($sets[$setName]) || isset($itemNames[$setName])) {
                $setIds = [$sets[$setName] ?? $itemNames[$setName]];
            } elseif (isset($costing->shared_sets[$setName])) {
                $setIds = [];
                $messages[] = "{$setName} dipakai " . implode(' dan ', $costing->shared_sets[$setName]) . '. Tulis nama itemnya.';
            } else {
                $setIds = [];
                $messages[] = "Product Set \"{$item['product_set']}\" tidak ada di Costing ({$sets->keys()->implode(', ')}).";
            }

            if ($messages) {
                foreach ($messages as $message) {
                    $errors[] = "Baris {$item['row_no']}: {$message}";
                }
                continue;
            }
            foreach ($setIds as $setId) {
                $rows[] = [
                    'row_no' => $item['row_no'],
                    'set_id' => $setId,
                    'color_id' => $colorId,
                    'size_id' => $sizeId,
                    'destination_id' => $destinationId,
                    'po' => $item['po'],
                    'delivery_date' => $deliveryDate,
                    'ship_mode_id' => $shipModeId,
                    'qty' => $item['qty'],
                    'label' => "{$item['color']} / {$item['size']}",
                ];
            }
        }

        $duplicates = collect($rows)->groupBy(fn ($row) => json_encode([$row['set_id'], $row['color_id'], $row['size_id'], $row['po'], $row['destination_id'], $row['delivery_date']]));
        foreach ($duplicates as $group) {
            if ($group->count() > 1) {
                foreach ($group as $row) {
                    $errors[] = "Baris {$row['row_no']}: {$row['label']} dobel untuk PO {$row['po']} yang sama.";
                }
            }
        }

        // TOP dan BOTTOM dengan PO, Destination, Ex Fty, Color, Size yang sama dikirim bareng, jadi Ship Mode-nya harus sama.
        if ($sets->count() > 1) {
            $byKey = collect($rows)->groupBy(fn ($row) => json_encode([$row['po'], $row['destination_id'], $row['delivery_date'], $row['color_id'], $row['size_id']]));
            foreach ($byKey as $group) {
                if ($group->pluck('ship_mode_id')->map(fn ($v) => (string) $v)->unique()->count() > 1) {
                    $errors[] = "Baris {$group->pluck('row_no')->unique()->implode(', ')}: Ship Mode {$group->first()['label']} PO {$group->first()['po']} antar set harus sama.";
                }
            }
        }

        // Dilewati kalau masih ada qty yang salah: baris itu tidak ikut dihitung, jadi totalnya pasti terlihat beda.
        if ($compareSets && $sets->count() > 1) {
            foreach (collect($rows)->groupBy(fn ($row) => $row['color_id'] . '_' . $row['size_id']) as $group) {
                $totals = [];
                foreach ($sets as $name => $setId) {
                    $totals[$name] = $group->where('set_id', $setId)->sum('qty');
                }
                if (count(array_unique($totals)) > 1) {
                    $text = collect($totals)->map(fn ($qty, $name) => "{$name} {$qty}")->implode(', ');
                    $errors[] = "Qty {$group->first()['label']} antar komponen harus sama ({$text}).";
                }
            }
        }

        return ['rows' => $rows, 'errors' => array_values(array_unique($errors))];
    }

    private function limitErrors(array $errors): array
    {
        $errors = array_values(array_unique($errors));
        $extra = count($errors) - self::MAX_ERRORS_SHOWN;

        return $extra > 0
            ? array_merge(array_slice($errors, 0, self::MAX_ERRORS_SHOWN), ["Dan {$extra} masalah lainnya."])
            : $errors;
    }

    private function nextWorksheet(string $supplierCode): string
    {
        $supplierCode = trim($supplierCode);

        return app(MarketingNdsCostingService::class)->nextNumber('so_line', 'no_ws', $supplierCode, 3);
    }

    /** Ship mode SO: tersimpan per baris so_po, yang ditampilkan di form adalah milik baris pertama. */
    public function shipModeId(object $so): ?int
    {
        $id = DB::connection(self::CONNECTION)->table('so_po as p')
            ->join('so_production as sp', 'sp.id', '=', 'p.so_production_id')
            ->join('so_line as l', 'l.id', '=', 'sp.so_line_id')
            ->where('l.sales_order_id', $so->id)
            ->where(fn ($q) => $q->whereNull('p.cancel')->orWhere('p.cancel', '!=', 'Y'))
            ->orderByDesc('p.id')
            ->value('p.ship_modes_id');

        return $id ? (int) $id : null;
    }

    // Menerima serial tanggal Excel atau teks; angka dengan pemisah / - . dibaca hari, bulan, tahun. Null bila bukan tanggal.
    private static function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if (is_numeric($value)) {
            return $value >= 20000 && $value <= 80000
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d')
                : null;
        }
        if (preg_match('#^(\d{1,2})[/.-](\d{1,2})[/.-](\d{4}|\d{2})$#', $value, $parts)) {
            $year = strlen($parts[3]) === 2 ? 2000 + (int) $parts[3] : (int) $parts[3];
            if (checkdate((int) $parts[2], (int) $parts[1], $year)) {
                return sprintf('%04d-%02d-%02d', $year, $parts[2], $parts[1]);
            }
            // Teks gaya US (bulan/hari, mis. 11/25/2026 dari template yang format selnya m/d/yyyy) juga diterima.
            return checkdate((int) $parts[1], (int) $parts[2], $year) ? sprintf('%04d-%02d-%02d', $year, $parts[1], $parts[2]) : null;
        }
        $timestamp = preg_match('/\d/', $value) ? strtotime($value) : false;

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    // "L+" dan "L +" dianggap size yang sama.
    private static function sizeKey($size): string
    {
        return preg_replace('/\s+/u', '', strtoupper((string) $size));
    }
}
