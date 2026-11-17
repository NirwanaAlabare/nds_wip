<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Data dokumen baca-saja (Detail, PDF, Excel) Sales Order dan BOM di DB baru (koneksi mysql_mkt).
 * Angka BOM dihitung sama dengan halaman Input BOM: qty SO per destinasi x color x size masuk ke rincian yang cocok.
 */
class MarketingNdsDocumentService
{
    private const CONNECTION = 'mysql_mkt';
    private const SET_ORDER_SQL = "CASE UPPER(TRIM(pi.product_set)) WHEN 'TOP' THEN 1 WHEN 'BOTTOM' THEN 2 ELSE 3 END";
    public const BOM_TABS = ['FAB' => 'Fabric', 'SEW' => 'Accessories Sewing', 'PACK' => 'Accessories Packing', 'MFG' => 'Manufacturing', 'OTH' => 'Other Cost'];

    private function db()
    {
        return DB::connection(self::CONNECTION);
    }

    private static function active($query, string $column)
    {
        return $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '!=', 'Y'));
    }

    private static function dateText($date): ?string
    {
        return $date ? date('d-m-Y', strtotime($date)) : null;
    }

    // ===== Sales Order

    /** Header SO, matriks color x size per WS, dan baris detail (PO, destinasi, ex-fty, color, size, qty). */
    public function salesOrder(int $soId): ?array
    {
        $db = $this->db();
        $so = $db->table('sales_orders as s')
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 's.currency_id')
            ->leftJoin('master_payment_terms as pt', 'pt.id', '=', 's.payment_terms_id')
            ->where('s.id', $soId)
            ->first(['s.*', 'cu.currency_code', 'pt.term_name']);
        if (!$so) {
            return null;
        }
        $costing = app(MarketingSalesOrderService::class)->costing((int) $so->costing_id);

        $rows = self::active($db->table('so_po as p'), 'p.cancel')
            ->join('so_production as sp', 'sp.id', '=', 'p.so_production_id')
            ->join('so_line as l', 'l.id', '=', 'sp.so_line_id')
            ->leftJoin('master_colors as c', 'c.id', '=', 'sp.color_id')
            ->leftJoin('master_sizes as sz', 'sz.id', '=', 'sp.sizes_id')
            ->leftJoin('master_destinations as d', 'd.id', '=', 'p.destination_id')
            ->leftJoin('master_countries as ct', 'ct.id', '=', 'd.country_id')
            ->leftJoin('master_ship_modes as sm', 'sm.id', '=', 'p.ship_modes_id')
            ->leftJoin('costing_set as cs', 'cs.id', '=', 'l.costing_set_id')
            ->leftJoin('master_product_items as pi', 'pi.id', '=', 'cs.product_items_id')
            ->where('l.sales_order_id', $soId)
            ->where(fn ($q) => $q->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y'))
            ->where(fn ($q) => $q->whereNull('l.cancel')->orWhere('l.cancel', '!=', 'Y'))
            ->orderByRaw(self::SET_ORDER_SQL)
            ->orderBy('l.id')->orderBy('p.buyer_po_no')->orderBy('c.color_name')->orderByRaw('CAST(sz.urutan AS UNSIGNED)')->orderBy('p.id')
            ->get([
                'l.id as line_id', 'l.no_ws', 'pi.product_set', 'pi.product_item_name', 'p.buyer_po_no', 'p.po_date', 'p.delivery_date', 'p.sku',
                'p.barcode', 'p.qty', 'sm.ship_mode_name', DB::raw("CONCAT_WS(' - ', NULLIF(ct.country_name, ''), d.destination_name) as destination_name"), 'c.color_name', 'sz.size_name', 'sz.urutan',
            ]);

        $worksheets = $rows->groupBy('line_id')->map(function ($lineRows) {
            $first = $lineRows->first();
            $sizes = $lineRows->sortBy(fn ($r) => sprintf('%08d|%s', (int) $r->urutan, $r->size_name))->pluck('size_name')->unique()->values()->all();
            $colors = $lineRows->groupBy('color_name')->map(function ($colorRows, $color) use ($sizes) {
                $qty = array_map(fn ($size) => (int) $colorRows->where('size_name', $size)->sum('qty'), $sizes);

                return ['name' => $color, 'qty' => $qty, 'total' => array_sum($qty)];
            })->values()->all();

            return [
                'no_ws' => $first->no_ws,
                'set' => strtoupper(trim((string) $first->product_set)),
                'item' => $first->product_item_name,
                'sizes' => $sizes,
                'colors' => $colors,
                'size_totals' => array_map(fn ($i) => array_sum(array_map(fn ($c) => $c['qty'][$i], $colors)), array_keys($sizes)),
                'total' => (int) $lineRows->sum('qty'),
            ];
        })->values()->all();

        $dates = $rows->pluck('delivery_date')->filter()->unique()->sort()->values();

        return [
            'so' => $so,
            'header' => [
                'so_no' => $so->so_no,
                'so_date' => self::dateText($so->so_date),
                'costing_no' => $costing->costing_no,
                'buyer' => $costing->buyer,
                'brand' => $costing->brand_name,
                'season' => $costing->season_name,
                'style' => $costing->style,
                'currency' => $so->currency_code,
                'payment' => $so->term_name,
                'ship_mode' => $rows->pluck('ship_mode_name')->filter()->unique()->implode(', '),
                'delivery' => $dates->count() > 1 ? self::dateText($dates->first()) . ' s/d ' . self::dateText($dates->last()) : self::dateText($dates->first()),
                'destinations' => $rows->pluck('destination_name')->filter()->unique()->implode(', '),
                'po' => $rows->pluck('buyer_po_no')->filter()->unique()->implode(', '),
                'worksheets' => collect($worksheets)->pluck('no_ws')->implode(', '),
                'total_qty' => (int) $rows->sum('qty'),
                'notes' => $so->notes,
            ],
            'worksheets' => $worksheets,
            'rows' => $rows->map(fn ($r) => [
                'no_ws' => $r->no_ws,
                'set' => strtoupper(trim((string) $r->product_set)),
                'po' => $r->buyer_po_no,
                'po_date' => self::dateText($r->po_date),
                'ex_fty' => self::dateText($r->delivery_date),
                'ship_mode' => $r->ship_mode_name,
                'destination' => $r->destination_name,
                'color' => $r->color_name,
                'size' => $r->size_name,
                'sku' => $r->sku,
                'barcode' => $r->barcode,
                'qty' => (int) $r->qty,
            ])->values()->all(),
        ];
    }

    /**
     * Isi PDF Sales Order portrait (acuan: "so potrait.pdf"): A. PO Buyer per Destination (color x size), B. Detail SKU & Barcode, Summary.
     * Costing set (TOP/BOTTOM) qty-nya sama per komponen dan Unit-nya SET, jadi qty dihitung sekali per PO/destinasi/color/size (bukan dijumlah antar set).
     * Price = so_inv.price; selama FOB per color/size belum diisi, memakai Confirm Price Costing.
     */
    public function salesOrderPrint(int $soId): ?array
    {
        $doc = $this->salesOrder($soId);
        if (!$doc) {
            return null;
        }
        $db = $this->db();
        $so = $doc['so'];
        $costing = app(MarketingSalesOrderService::class)->costing((int) $so->costing_id);

        $rows = self::active($db->table('so_po as p'), 'p.cancel')
            ->join('so_production as sp', 'sp.id', '=', 'p.so_production_id')
            ->join('so_line as l', 'l.id', '=', 'sp.so_line_id')
            ->leftJoin('so_inv as inv', 'inv.id', '=', 'sp.so_inv_id')
            ->leftJoin('master_colors as c', 'c.id', '=', 'sp.color_id')
            ->leftJoin('master_sizes as sz', 'sz.id', '=', 'sp.sizes_id')
            ->leftJoin('master_destinations as d', 'd.id', '=', 'p.destination_id')
            ->leftJoin('master_countries as ct', 'ct.id', '=', 'd.country_id')
            ->leftJoin('master_ship_modes as sm', 'sm.id', '=', 'p.ship_modes_id')
            ->where('l.sales_order_id', $soId)
            ->where(fn ($q) => $q->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y'))
            ->where(fn ($q) => $q->whereNull('l.cancel')->orWhere('l.cancel', '!=', 'Y'))
            ->get([
                'l.id as line_id', 'p.buyer_po_no', 'p.po_date', 'p.delivery_date', 'p.sku', 'p.barcode', 'p.qty', 'inv.price',
                'sm.ship_mode_name', DB::raw("CONCAT_WS(' - ', NULLIF(ct.country_name, ''), d.destination_name) as destination_name"), 'c.color_name', 'sz.size_name', 'sz.urutan',
            ]);

        $fob = (float) ($costing->fob_price ?? 0);
        // Destinasi tampil "NEGARA - DESTINASI" (nama bisa sama di negara lain, mis. RETAIL).
        $dest = fn ($r) => trim((string) $r->destination_name) === '' ? '-' : $r->destination_name;
        $sizeOrder = $rows->unique('size_name')->sortBy(fn ($r) => sprintf('%08d|%s', (int) $r->urutan, $r->size_name))->pluck('size_name')->values();
        $colorOrder = $rows->pluck('color_name')->unique()->values()->flip();

        // Satu baris = PO + destinasi + delivery + ship mode + color + size; qty = qty satu komponen set (maks antar set).
        $units = $rows->groupBy(fn ($r) => json_encode([$r->buyer_po_no, $dest($r), $r->delivery_date, $r->ship_mode_name, $r->color_name, $r->size_name]))
            ->map(function ($group) use ($dest, $fob) {
                $first = $group->first();
                $price = $group->pluck('price')->filter(fn ($p) => $p !== null && (float) $p > 0)->first();

                return [
                    'po' => (string) $first->buyer_po_no,
                    'po_date' => $group->pluck('po_date')->filter()->min(),
                    'destination' => $dest($first),
                    'date' => $first->delivery_date,
                    'ship_mode' => $first->ship_mode_name ?: '-',
                    'color' => (string) $first->color_name,
                    'size' => (string) $first->size_name,
                    'sku' => $group->pluck('sku')->filter()->first(),
                    'barcode' => $group->pluck('barcode')->filter()->first(),
                    'qty' => (int) $group->groupBy('line_id')->map(fn ($g) => $g->sum('qty'))->max(),
                    'price' => $price !== null ? (float) $price : $fob,
                ];
            })
            ->sortBy(fn ($u) => sprintf('%s|%s|%s|%s|%06d|%06d', $u['po'], $u['destination'], $u['date'], $u['ship_mode'], $colorOrder[$u['color']] ?? 0, $sizeOrder->search($u['size'])))
            ->values();

        $sizes = $sizeOrder->all();
        $pos = $units->groupBy('po')->map(function ($poUnits, $po) use ($sizes) {
            $lines = $poUnits->groupBy(fn ($u) => json_encode([$u['destination'], $u['date'], $u['ship_mode'], $u['color']]))->map(function ($g) use ($sizes) {
                $first = $g->first();
                $qty = array_map(fn ($s) => (int) $g->where('size', $s)->sum('qty'), $sizes);

                return [
                    'destination' => $first['destination'],
                    'date' => $first['date'],
                    'ship_mode' => $first['ship_mode'],
                    'color' => $first['color'],
                    'qty' => $qty,
                    'total' => array_sum($qty),
                    'price' => $g->pluck('price')->unique()->count() === 1 ? $first['price'] : null,
                    'value' => $g->sum(fn ($u) => $u['qty'] * $u['price']),
                ];
            })->values()->all();

            return [
                'po' => $po,
                'po_date' => $poUnits->pluck('po_date')->filter()->min(),
                'lines' => $lines,
                'size_totals' => array_map(fn ($i) => array_sum(array_map(fn ($l) => $l['qty'][$i], $lines)), array_keys($sizes)),
                'total' => array_sum(array_column($lines, 'total')),
                'value' => array_sum(array_column($lines, 'value')),
            ];
        })->values()->all();

        $creator = $so->created_by ?? null;
        $createdAt = $so->created_at;
        if (!$creator) {
            $log = $db->table('process_logs as l')->leftJoin('users as u', 'u.id', '=', 'l.action_by')
                ->where('l.sales_orders_id', $soId)->where('l.doc_type', 'so')->where('l.action', 'created')
                ->orderBy('l.id')->first(['u.username', 'l.action_at']);
            $creator = $log->username ?? null;
            $createdAt = $log->action_at ?? $createdAt;
        }
        $image = $db->table('costing_images')->where('costings_id', $so->costing_id)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))
            ->orderBy('id')->pluck('file_path')
            ->map(fn ($path) => public_path('uploads/costing/' . basename($path)))
            ->first(fn ($path) => is_file($path));

        // SO tanpa approval: kotak "Approved by" dibiarkan kosong (untuk tanda tangan manual).
        $approver = ['name' => null, 'at' => null];

        $totalQty = (int) $units->sum('qty');
        $totalValue = (float) $units->sum(fn ($u) => $u['qty'] * $u['price']);
        $date = fn ($d) => $d ? date('Y-m-d', strtotime($d)) : '-';

        return [
            'no' => $so->so_no,
            'revision' => (int) $so->revision_no,
            'currency' => $so->currency_code ?: '-',
            'image' => $image,
            'info_left' => [
                ['No SO', $so->so_no],
                ['SO Date', $date($so->so_date)],
                ['Status', 'APPROVED'],
                ['SO Type', mb_strtoupper((string) ($so->so_type ?: '-'))],
                ['Currency', $so->currency_code ?: '-'],
                ['Unit', $costing->unit_code ?: '-'],
                ['No WS', collect($doc['worksheets'])->pluck('no_ws')->filter()->implode(', ') ?: '-'],
                ['Jumlah PO', (string) count($pos)],
                ['Jumlah Destination', (string) $units->pluck('destination')->unique()->count()],
            ],
            'info_right' => [
                ['No Costing', $costing->costing_no ?: '-'],
                ['Buyer', $costing->buyer ?: '-'],
                ['Brand', $costing->brand_name ?: '-'],
                ['Season', $costing->season_name ?: '-'],
                ['Style', $costing->style ?: '-'],
                ['Item Description', mb_strtoupper($costing->sets->pluck('product_item_name')->filter()->unique()->implode(' + ')) ?: '-'],
                ['Total Qty', number_format($totalQty)],
                ['Total Value', ($so->currency_code ? $so->currency_code . ' ' : '') . number_format($totalValue, 2)],
            ],
            'sizes' => $sizes,
            'pos' => $pos,
            'grand_sizes' => array_map(fn ($i) => array_sum(array_map(fn ($p) => $p['size_totals'][$i], $pos)), array_keys($sizes)),
            'skus' => $units->map(fn ($u) => $u + ['value' => $u['qty'] * $u['price']])->all(),
            'by_destination' => $units->groupBy('destination')->map(fn ($g) => (int) $g->sum('qty'))->all(),
            'total_qty' => $totalQty,
            'total_value' => $totalValue,
            'creator' => $creator ?: '-',
            'created_at' => $createdAt ? date('d/m/Y H:i', strtotime($createdAt)) : '',
            'approver' => $approver['name'] ?? null,
            'approved_at' => !empty($approver['at']) ? date('d/m/Y H:i', strtotime($approver['at'])) : '',
        ];
    }

    public function salesOrderExcel(array $doc): Spreadsheet
    {
        $h = $doc['header'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Order');
        $sheet->fromArray([
            ['Sales Order', $h['so_no']], ['SO Date', $h['so_date']], ['No Costing', $h['costing_no']], ['Worksheet', $h['worksheets']],
            ['Buyer', $h['buyer']], ['Brand', $h['brand']], ['Season', $h['season']], ['Style', $h['style']],
            ['Currency', $h['currency']], ['Payment Terms', $h['payment']], ['Total Qty', $h['total_qty']],
            [],
            ['No Worksheet', 'Set', 'Buyer PO', 'PO Date', 'Ex FTY', 'Ship Mode', 'Destination', 'Color', 'Size', 'SKU', 'Barcode', 'Qty'],
        ]);
        $sheet->getStyle('A1:A11')->getFont()->setBold(true);
        $sheet->getStyle('A13:L13')->getFont()->setBold(true);
        $row = 14;
        foreach ($doc['rows'] as $r) {
            $sheet->fromArray([$r['no_ws'], $r['set'], $r['po'], $r['po_date'], $r['ex_fty'], $r['ship_mode'], $r['destination'], $r['color'], $r['size'], $r['sku'], $r['barcode'], $r['qty']], null, 'A' . $row++);
        }
        $sheet->fromArray(['TOTAL', null, null, null, null, null, null, null, null, null, null, $h['total_qty']], null, 'A' . $row);
        $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    // ===== BOM

    /**
     * Dokumen BOM satu WS: header + per tab baris siap cetak. Material yang dipecah per color / size tampil
     * satu baris per rincian. Kalau BOM belum disimpan, isinya isian awal dari Costing (bom = null).
     */
    public function bom(object $line): array
    {
        $boms = app(MarketingNdsBomService::class);
        $page = $boms->page($line);
        $bd = $page['breakdown'];
        $m = $page['masters'];
        $names = [
            'item' => array_column($m['items'], 'name', 'id'),
            'unit' => array_column($m['units'], 'name', 'id'),
            'sup' => array_column($m['suppliers'], 'name', 'id'),
            'dest' => array_column($bd['dests'], 'name', 'id'),
            'color' => array_column($bd['colors'], 'name', 'id'),
            'size' => array_column($bd['sizes'], 'name', 'id'),
        ];
        $sizeIds = array_column($bd['sizes'], 'id');
        $full = !empty($page['detailFull']);
        $isLot = fn ($unit) => strtoupper($names['unit'][$unit] ?? '') === 'LOT';
        $label = fn ($k, $v) => $v ? ($names[$k][$v] ?? null) : null;
        $bom = $boms->activeBom((int) $line->id);
        // Allowance garment (bom.garment_allowance_percent): orig qty material = qty SO + allowance, dibulatkan ke atas.
        $allow = (float) ($bom->garment_allowance_percent ?? 0);

        // Satu baris cetak dari nilai (cons, unit, loss, supplier, price, item, destinasi) dan qty asal.
        $line_ = function (array $base, array $v, int $orig, ?string $color, ?string $size, bool $charge) use ($label, $isLot, $allow) {
            if (!$charge && $allow > 0) {
                $orig = (int) ceil($orig * (1 + $allow / 100));
            }
            $cons = (float) ($v['cons'] ?? 0);
            $loss = $charge ? 0 : (float) ($v['loss'] ?? 0);
            $req = $isLot($v['unit'] ?? null) ? $cons : $orig * $cons;
            $total = $charge ? $req : ceil($req * (1 + $loss / 100));
            $price = $v['price'] ?? null;

            return [
                'content' => $base['content'],
                'shell' => $base['shell'] ?? null,
                'parts' => $base['parts'] ?? null,
                'rule' => $charge ? null : ($base['rule'] ?? null),
                'color' => $color,
                'size' => $size,
                'item' => $label('item', $v['item'] ?? null),
                'item_id' => $v['item'] ?? null,
                'dest' => $label('dest', $v['dest'] ?? null),
                'orig' => $orig,
                'cons' => $v['cons'] ?? null,
                'unit' => $label('unit', $v['unit'] ?? null),
                'req' => $req,
                'loss' => $charge ? null : $loss,
                'loss_value' => $req * $loss / 100,
                'total' => $total,
                'supplier' => $label('sup', $v['sup'] ?? null),
                'price' => $price,
                'price_costing' => $base['pc'] ?? null,
                'currency' => $base['cur'] ?? null,
                'value' => $total * (float) $price,
            ];
        };
        // Qty SO ke penampungnya: rincian terakhir yang cocok color & size, atau baris induk (null).
        $eachQty = function (array $subs, ?int $baseDest, callable $add, bool $perDest = false) use ($bd, $sizeIds, $full) {
            foreach ($bd['dests'] as $dest) {
                foreach ($bd['colors'] as $color) {
                    foreach ($bd['sizes'] as $i => $size) {
                        $qq = (int) ($bd['qty'][$dest['id']][$color['id']][$i] ?? 0);
                        if (!$qq) {
                            continue;
                        }
                        $best = null;
                        foreach ($subs as $k => $sub) {
                            // Rule per destinasi: rincian hanya menampung qty destinasinya sendiri.
                            if ($perDest && $sub['dest'] && $sub['dest'] !== $dest['id']) {
                                continue;
                            }
                            if ($sub['color'] && $sub['color'] !== $color['id']) {
                                continue;
                            }
                            if ($sub['sfrom']) {
                                $from = array_search($sub['sfrom'], $sizeIds, true);
                                $to = array_search($sub['sto'], $sizeIds, true);
                                if ($from === false || $to === false || $i < $from || $i > $to) {
                                    continue;
                                }
                            }
                            $best = $k;
                        }
                        $dest_ = $best === null ? $baseDest : ($full ? $subs[$best]['dest'] : $baseDest);
                        if ($dest_ && $dest_ !== $dest['id']) {
                            continue;
                        }
                        $add($best, $qq);
                    }
                }
            }
        };

        $sections = [];
        foreach (self::BOM_TABS as $tab => $tabName) {
            $sections[$tab] = ['label' => $tabName, 'charge' => in_array($tab, ['MFG', 'OTH'], true), 'items' => [], 'value' => 0];
        }
        foreach ($page['rows'] as $r) {
            $subs = $r['rule'] !== MarketingNdsBomService::RULES[0] ? $r['subs'] : [];
            $orig = array_fill_keys(array_keys($subs), 0) + ['base' => 0];
            $eachQty($subs, $r['dest'], function ($best, $qq) use (&$orig) {
                $orig[$best ?? 'base'] += $qq;
            }, str_ends_with((string) $r['rule'], 'Per Destination'));
            $lines = [];
            if (!$subs) {
                $lines[] = $line_($r, $r, $orig['base'], null, null, false);
            }
            foreach ($subs as $k => $sub) {
                // Tanpa kolom lengkap di bom_material_details, selain item & cons rincian ikut baris induk.
                $values = $full ? $sub : ['item' => $sub['item'], 'cons' => $sub['cons']] + $r;
                $size = $sub['sfrom'] ? ($sub['sfrom'] === $sub['sto'] ? $label('size', $sub['sfrom']) : $label('size', $sub['sfrom']) . ' - ' . $label('size', $sub['sto'])) : null;
                $lines[] = $line_($r, $values, $orig[$k], $label('color', $sub['color']), $size, false);
            }
            $sections[$r['tab']]['items'][] = ['lines' => $lines];
        }
        $allQty = 0;
        $eachQty([], null, function ($best, $qq) use (&$allQty) {
            $allQty += $qq;
        });
        foreach ($page['charges'] as $c) {
            $sections[$c['tab']]['items'][] = ['lines' => [$line_($c, $c, $allQty, null, null, true)]];
        }
        foreach ($sections as &$section) {
            $section['value'] = collect($section['items'])->flatMap(fn ($i) => $i['lines'])->sum('value');
        }
        unset($section);
        $sections = array_filter($sections, fn ($s) => $s['items']);

        return [
            'line' => $line,
            'bom' => $bom,
            'header' => $page['header'],
            'breakdown' => $bd,
            'stale' => $page['stale'],
            // Gambar produk dari Costing untuk kop PDF BOM (sama dengan PDF Costing & SO).
            'image' => $this->costingImage((int) DB::connection('mysql_mkt')->table('sales_orders')->where('id', $line->sales_order_id)->value('costing_id')),
            'sections' => $sections,
            'total_value' => array_sum(array_column($sections, 'value')),
            'print' => $this->bomPrint($page['header'], $bom, $sections, $allow, $allQty),
        ];
    }

    /**
     * Isi PDF / Excel BOM (acuan "pdf bom.pdf" / "excel bom.xlsx"): info header, A. MATERIAL per grup
     * (NO = urutan content, berlanjut antar grup), B. CHARGES, notes, created / approved by.
     */
    private function bomPrint(array $h, ?object $bom, array $sections, float $allow, int $qty): array
    {
        $statuses = ['awaiting' => 'AWAITING APPROVAL', 'approved' => 'APPROVED', 'unlocked' => 'UNLOCKED', 'return' => 'RETURN TO STAFF', 'cancel' => 'CANCEL'];
        $approvals = app(MarketingNdsApprovalService::class);
        $status = $bom ? ($statuses[$approvals->status('bom', (int) $bom->id)] ?? 'AWAITING APPROVAL') : 'BELUM DISIMPAN';
        $approver = $bom ? $approvals->approver('bom', (int) $bom->id) : null;
        $unit = $h['unit'] ?: 'PCS';
        $v = fn ($x) => $x === null || $x === '' ? '-' : (string) $x;

        $groups = [];
        $charges = [];
        $no = 0;
        $chargeItems = collect($sections)->filter(fn ($s) => $s['charge'])->flatMap(fn ($s) => $s['items'])->flatMap(fn ($i) => $i['lines']);
        $codes = $this->db()->table('master_items')->whereIn('id', $chargeItems->pluck('item_id')->filter()->unique()->all())->pluck('item_code', 'id');
        foreach ($sections as $section) {
            if ($section['charge']) {
                foreach ($section['items'] as $item) {
                    foreach ($item['lines'] as $l) {
                        $charges[] = [
                            'code' => $v($codes[$l['item_id']] ?? null),
                            'item' => $v($l['item'] ?? $l['content']),
                            'supplier' => $v($l['supplier']),
                            'cons' => $l['cons'],
                            'unit' => $v($l['unit']),
                            'price' => $l['price'],
                            'currency' => $v($l['currency']),
                            // Keterangan = charge Costing asalnya (hanya kalau item BOM sudah dipilih, supaya tidak dobel dengan ITEM).
                            'note' => $l['item'] ? $v($l['content']) : '-',
                        ];
                    }
                }
                continue;
            }
            $rows = [];
            foreach ($section['items'] as $item) {
                $no++;
                foreach ($item['lines'] as $l) {
                    $rows[] = [
                        'no' => $no,
                        'content' => $v($l['content']),
                        'shell' => $v($l['shell']),
                        'item' => $v($l['item']),
                        'color' => $v($l['color']),
                        'size' => $l['size'] ?? 'ALL',
                        'dest' => $l['dest'] ?? 'ALL',
                        'orig' => (int) $l['orig'],
                        'cons' => $l['cons'] === null ? null : (float) $l['cons'],
                        'unit' => $v($l['unit']),
                        'lot' => strtoupper((string) $l['unit']) === 'LOT',
                        'req' => (float) $l['req'],
                        'loss' => (float) $l['loss'],
                        'loss_value' => (float) $l['loss_value'],
                        'total' => (float) $l['total'],
                        'supplier' => $v($l['supplier']),
                        'price' => $l['price'] === null ? null : (float) $l['price'],
                        'price_costing' => $l['price_costing'] === null ? null : (float) $l['price_costing'],
                        'currency' => $l['currency'],
                    ];
                }
            }
            $groups[] = ['label' => strtoupper($section['label']), 'rows' => $rows];
        }
        $currencies = collect($groups)->flatMap(fn ($g) => $g['rows'])->pluck('currency')->filter()->unique();
        $date = fn ($d) => $d ? date('Y-m-d', strtotime($d)) : '-';
        $allowText = rtrim(rtrim(number_format($allow, 2, '.', ''), '0'), '.');

        return [
            'no' => $h['bom_no'] ?: '-',
            'revision' => (int) ($bom->revision_no ?? 0),
            'no_ws' => $h['no_ws'],
            'info' => [
                [['No BOM', $h['bom_no'] ?: '-'], ['Revision', (string) (int) ($bom->revision_no ?? 0)], ['BOM Date', $date($bom->bom_date ?? null)],
                    ['Status', $status], ['Buyer BOM Ref', $v($bom->buyer_bom_ref ?? null)], ['No WS', $v($h['no_ws'])]],
                [['No SO', $v($h['so_no'])], ['No Costing', $v($h['costing_no'])], ['Buyer', $v($h['buyer'])],
                    ['Brand', $v($h['brand'])], ['Season', $v($h['season'])], ['Style', $v($h['style'])]],
                [['Product Group', $v(mb_strtoupper((string) $h['product_set']))], ['Item Description', $v(mb_strtoupper((string) $h['product_item']))],
                    ['Qty Order', number_format($qty) . ' ' . $unit], ['Qty + Allowance ' . $allowText . '%', number_format((int) ceil($qty * (1 + $allow / 100))) . ' ' . $unit]],
            ],
            'qty' => $qty,
            'allow' => $allow,
            'unit' => $unit,
            'groups' => $groups,
            'charges' => $charges,
            'note' => 'Orig qty = qty + allowance garment (item khusus destinasi pakai qty destinasi itu). Qty req = orig qty x cons / pc. Value = qty req x loss %. '
                . 'Total qty req = qty req + value (dibulatkan ke atas).' . ($currencies->count() === 1 ? ' Price dalam ' . $currencies->first() . '.' : ''),
            'notes' => (string) ($bom->notes ?? ''),
            'creator' => $v($bom->created_by ?? null),
            'created_at' => !empty($bom->created_at) ? date('d/m/Y H:i', strtotime($bom->created_at)) : '',
            'approver' => $approver['name'] ?? null,
            'approved_at' => !empty($approver['at']) ? date('d/m/Y H:i', strtotime($approver['at'])) : '',
        ];
    }

    /** Path gambar Costing pertama yang filenya ada (null kalau tidak ada). */
    private function costingImage(int $costingId): ?string
    {
        return DB::connection('mysql_mkt')->table('costing_images')->where('costings_id', $costingId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))
            ->orderBy('id')->pluck('file_path')
            ->map(fn ($path) => public_path('uploads/costing/' . basename($path)))
            ->first(fn ($path) => is_file($path));
    }

    /** Dokumen BOM semua WS di SO (atau satu WS kalau $lineId diisi). */
    public function boms(int $soId, ?int $lineId = null): array
    {
        $lines = app(MarketingNdsBomService::class)->lines($soId)
            ->when($lineId, fn ($l) => $l->where('id', $lineId))
            ->map(fn ($ws) => $this->db()->table('so_line')->where('id', $ws->id)->first());

        return $lines->map(fn ($line) => $this->bom($line))->values()->all();
    }

    /** Excel BOM: satu sheet per WS, tampilan sama dengan PDF (lihat MarketingNdsBomExcel). */
    public function bomExcel(array $docs): Spreadsheet
    {
        return (new MarketingNdsBomExcel())->build($docs);
    }

    /** Response unduhan .xlsx. */
    public static function download(Spreadsheet $spreadsheet, string $name)
    {
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '_', $name) . '.xlsx';

        return response()->streamDownload(fn () => (new Xlsx($spreadsheet))->save('php://output'), $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
