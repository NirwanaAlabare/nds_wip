<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Services\MarketingBomService;
use App\Services\MarketingHistoryService;
use App\Services\MarketingApprovalService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Marketing_CostingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;
            $db = DB::connection('mysql_sb');
            $latestSalesOrders = $db->table('so')
                ->select('id_cost', DB::raw('MAX(id) as latest_id'))
                ->where('cancel_h', 'N')
                ->groupBy('id_cost');
            $latestBoms = $db->table('bom_marketing')
                ->select('id_costing', DB::raw('MAX(id) as latest_id'))
                ->where(function ($status) {
                    $status->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
                })
                ->groupBy('id_costing');
            // Header BOM dibuat saat Costing disimpan, jadi "sudah ada BOM" dilihat dari jumlah item aktifnya.
            $bomItemCounts = $db->table('bom_marketing_detail')
                ->select('id_bom_marketing', DB::raw('COUNT(*) as item_count'))
                ->where('cancel', 'N')
                ->groupBy('id_bom_marketing');
            $statusSql = MarketingApprovalService::statusSql('costing', 'a');

            $data = $db->table('act_costing as a')
                ->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
                ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
                ->leftJoinSub($latestSalesOrders, 'so_latest', function ($join) {
                    $join->on('so_latest.id_cost', '=', 'a.id');
                })
                ->leftJoin('so as s', 's.id', '=', 'so_latest.latest_id')
                ->leftJoinSub($latestBoms, 'bom_latest', function ($join) {
                    $join->on('bom_latest.id_costing', '=', 'a.id');
                })
                ->leftJoin('bom_marketing as bm', 'bm.id', '=', 'bom_latest.latest_id')
                ->leftJoinSub($bomItemCounts, 'bom_items', function ($join) {
                    $join->on('bom_items.id_bom_marketing', '=', 'bm.id');
                })
                ->select(
                    'a.id',
                    'a.cost_no',
                    'a.cost_date',
                    'a.deldate',
                    'b.Supplier as nama_buyer',
                    'a.brand',
                    'a.styleno',
                    'a.kpno',
                    'a.mkt_order',
                    'p.product_group',
                    'p.product_item',
                    'a.main_dest',
                    'a.market',
                    'a.qty',
                    'a.cfm_price',
                    'a.app1',
                    'a.app1_by',
                    'a.app1_date',
                    'a.username',
                    'a.dateinput',
                    'p.product_group as product_group_text',
                    'p.product_item as product_item_text',
                    's.id as so_id',
                    's.so_no',
                    's.d_insert as so_created_at',
                    'bm.id as bom_id',
                    'bm.no_katalog_bom as bom_no',
                    'bm.created_at as bom_created_at',
                    DB::raw('COALESCE(bom_items.item_count, 0) as bom_item_count'),
                    DB::raw($statusSql . ' as approval_status'),
                    DB::raw(MarketingApprovalService::latestNoteSql('costing', 'a.id') . ' as approval_note'),
                    DB::raw(MarketingApprovalService::statusSql('bom', 'bm') . ' as bom_approval_status'),
                    DB::raw(MarketingApprovalService::latestNoteSql('bom', 'bm.id') . ' as bom_approval_note'),
                    DB::raw(MarketingApprovalService::pendingUnlockSql('costing', 'a.id') . ' as costing_unlock_pending'),
                    DB::raw('IF(bm.id IS NULL, 0, ' . MarketingApprovalService::pendingUnlockSql('bom', 'bm.id') . ') as bom_unlock_pending')
                )
                ->orderBy('a.id', 'desc');

            // Jumlah per status mengikuti filter tanggal/buyer, tapi tidak terfilter status itu sendiri.
            $statusCounts = $db->table('act_costing as a')
                ->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
                ->select(DB::raw($statusSql . ' as approval_status'), DB::raw('COUNT(*) as total'))
                ->groupBy('approval_status');

            foreach ([$data, $statusCounts] as $query) {
                if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                    $query->whereDate('a.deldate', '>=', $tgl_awal)
                        ->whereDate('a.deldate', '<=', $tgl_akhir);
                }
                if ($request->filled('buyers')) {
                    $query->whereIn('b.Id_Supplier', (array) $request->buyers);
                }
            }

            // product group dan product item pake ucwords


            if (in_array($request->approval_status, ['awaiting', 'return', 'cancel', 'approved', 'unlocked'], true)) {
                $data->whereRaw($statusSql . ' = ?', [$request->approval_status]);
            }

            return DataTables::of($data)
                ->with('status_counts', $statusCounts->pluck('total', 'approval_status'))
                ->addIndexColumn()
                ->addColumn('tgl_costing', function ($row) {
                    return $row->cost_date ? date('d-m-Y', strtotime($row->cost_date)) : '-';
                })
                ->addColumn('due_date', function ($row) {
                    return $row->deldate ? date('d M Y', strtotime($row->deldate)) : '-';
                })
                ->editColumn('product_group_text', function ($row) {
                    if (!$row->product_group_text) return '-';
                    return ucwords(strtolower($row->product_group_text), " \t\r\n\f\v-");
                })
                ->editColumn('product_item_text', function ($row) {
                    if (!$row->product_item_text) return '-';
                    return ucwords(strtolower($row->product_item_text), " \t\r\n\f\v-");
                })
                ->make(true);
        }

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')
            ->select('Id_Supplier as id', 'Supplier as name')
            ->where('tipe_sup', 'C')
            ->orderBy('Supplier')
            ->get();

        return view('marketing.costing.index', [
            'buyers'               => $buyers,
            'containerFluid'       => true,
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing",
        ]);
    }

    public function costingTimeline(int $id)
    {
        $db = DB::connection('mysql_sb');
        $costing = $db->table('act_costing')
            ->where('id', $id)
            ->first(['cost_no', 'app1', 'app1_date', 'app1_by']);

        if (!$costing) {
            return response()->json(['message' => 'Data Costing tidak ditemukan.'], 404);
        }

        if (!Schema::connection('mysql_sb')->hasTable('mkt_hist')) {
            return response()->json(['message' => 'Tabel mkt_hist belum tersedia. Jalankan script database costing_legacy_columns.sql terlebih dahulu.'], 503);
        }

        $history = $db->table('mkt_hist')
            ->where('id_act_cost', $id)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'type', 'type_id', 'desc', 'user', 'created_at', 'action']);

        return response()->json([
            'data' => $history,
            'costing' => [
                'cost_no' => $costing->cost_no,
                'approved' => in_array(strtoupper((string) $costing->app1), ['A', 'Y'], true),
                'approved_at' => $costing->app1_date,
                'approved_by' => $costing->app1_by,
            ],
        ]);
    }

    public function create()
    {
        $db = DB::connection('mysql_sb');

        $buyers = $db->table('mastersupplier')
                     ->where('tipe_sup', 'C')
                     ->orderBy('supplier', 'asc')
                     ->get();

        $product_groups = $db->table('masterproduct')
                             ->select('product_group')
                             ->groupBy('product_group')
                             ->get();

        $currencies = $db->table('masterpilihan')
                         ->where('kode_pilihan', 'Curr')
                         ->get();

        $shipmodes = $db->table('mastershipmode')
                        ->orderBy('shipmode', 'asc')
                        ->get();

        $marketing_orders = $db->table('master_mkt_order')
                               ->orderBy('mkt_order', 'asc')
                               ->get();

        $status_cst = $db->table('masterpilihan')
                         ->where('kode_pilihan', 'ST_CST')
                         ->get();

        $units = $db->table('masterpilihan')
           ->where('kode_pilihan', 'Satuan')
           ->get();

        $suppliers = $db->table('mastersupplier')->orderBy('supplier', 'asc')->get();

        $destinations = $db->table('master_destination')->orderBy('country_name', 'asc')->get();

         $master_set = $db->table('master_set')
           ->get();

        $seasons = $db->table('masterseason')
                     ->orderBy('season', 'asc')
                     ->get();


       return view('marketing.costing.create', [
            'buyers'           => $buyers,
            'product_groups'   => $product_groups,
            'currencies'       => $currencies,
            'shipmodes'        => $shipmodes,
            'marketing_orders' => $marketing_orders,
            'status_cst'       => $status_cst,
            'units'       => $units,
            'master_set'         => $master_set,
            'suppliers'       => $suppliers,
            'destinations'        => $destinations,
            'seasons'          => $seasons,
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing",
            'containerFluid'   => true
        ]);
    }

    /**
     * Style masuk ke act_costing.styleno (dan ikut ke SO/BOM): tidak boleh ada spasi di awal/akhir
     * atau spasi ganda di tengah, karena berpengaruh ke produksi.
     */
    private static function styleSpacingRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $value = (string) $value;
            if ($value !== trim($value) || preg_match('/\s{2,}/u', $value) === 1) {
                $fail('Style tidak boleh ada spasi di awal/akhir atau spasi ganda.');
            }
        };
    }

    public function store(Request $request)
    {
        $request->validate([
            'buyer' => ['required', 'exists:mysql_sb.mastersupplier,Id_Supplier'],
            'product_item' => ['required', 'exists:mysql_sb.masterproduct,id'],
            'ship_mode' => ['required', 'exists:mysql_sb.mastershipmode,id'],
            'curr' => ['required', 'exists:mysql_sb.masterpilihan,id'],
            'qty_planned' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'numeric', 'min:0'],
            'smv' => ['required', 'numeric', 'min:0'],
            'deldate' => ['required', 'date'],
            'style' => ['nullable', 'string', self::styleSpacingRule()],
            'upload_foto' => ['sometimes', 'array'],
            'upload_foto.*' => ['image', 'max:10240'],
        ]);

        $db = DB::connection('mysql_sb');
        $now = now();
        $insertId = $db->transaction(function () use ($db, $request, $now) {
            $numbers = $this->generateCostingNumbers($request->buyer, $now);
            $currency = $db->table('masterpilihan')
                ->where('id', $request->curr)
                ->where('kode_pilihan', 'Curr')
                ->value('nama_pilihan');
            if (!$currency) {
                throw new \RuntimeException('Currency yang dipilih tidak ditemukan.');
            }
            $marketingOrder = $db->table('master_mkt_order')
                ->where('id', $request->marketing_order)
                ->value('mkt_order');

            $productSet = is_array($request->product_set) ? implode(',', $request->product_set) : null;
            $mainDest = is_array($request->main_dest) ? implode(', ', $request->main_dest) : $request->main_dest;
            $id = $db->table('act_costing')->insertGetId([
                'id_pre_cost' => 0,
                'cost_no' => $numbers['cost_no'],
                'cost_date' => $now,
                'kpno' => $numbers['kpno'],
                'id_smode' => $request->ship_mode,
                'smv_min' => $request->smv,
                'smv_sec' => 0,
                'book_min' => 0,
                'book_sec' => 0,
                'notes' => Str::upper($request->notes ?? ''),
                'deldate' => $request->deldate,
                'attach_file' => null,
                'status' => $request->status,
                'status_order' => '',
                'id_buyer' => $request->buyer,
                'id_product' => $request->product_item,
                'styleno' => Str::upper($request->style ?? ''),
                'qty_planned' => $request->qty_planned,
                'qty' => $request->qty,
                'username' => Auth::user()->name,
                'curr' => $currency,
                'vat' => $request->vat,
                'deal_allow' => 0,
                'unit' => $request->unit,
                'dateinput' => $now,
                'ga_cost' => 0,
                'cfm_price' => $request->confirm_price,
                'comm_cost' => 0,
                'brand' => Str::upper($request->brand ?? ''),
                'main_dest' => $mainDest,
                'aktif' => 'Y',
                'type_ws' => $request->tipe_ws,
                'mkt_order' => $marketingOrder,
                'rate' => $request->rate_to_idr,
                'costing_type' => $request->type,
                'season_id' => $request->season_id,
                'product_set' => $productSet,
                'shipment_type' => $request->shipment_type,
                'rate_from_idr' => $request->rate_from_idr,
                'market' => Str::upper($request->market ?? ''),
                'ga_percent' => 3,
            ]);

            $pictureNames = $this->saveCostingPictures($request, $id);
            if ($pictureNames) {
                $db->table('act_costing')->where('id', $id)->update([
                    'attach_file' => $pictureNames[0],
                ]);
            }

            $mandatoryOthers = $db->table('masterothers')->where('costing_header', 'Y')->get();
            foreach ($mandatoryOthers as $other) {
                $description = strtoupper(($other->otherscode ?? '') . ' ' . ($other->othersdesc ?? ''));
                $this->saveCostingDetail($id, 'Other Cost', [
                    'item' => $other->id,
                    'allowance' => str_contains($description, 'OVERHEAD') ? 6 : 0,
                    'cons' => 1,
                    'unit' => 'PCS',
                    'curr' => $currency,
                ]);
            }

            app(MarketingBomService::class)->ensureForCosting($id);

            app(MarketingHistoryService::class)->costingCreated($id);

            return $id;
        });

        return redirect()->route('edit-costing', $insertId)->with('success', 'Costing berhasil dibuat');
    }

    // Costing Approved tidak boleh diubah sebelum di-unlock (lihat MarketingApprovalService).
    // Untuk aksi simpan, costing Return / Cancel / Unlocked dikembalikan ke Awaiting Approval.
    private function costingLockedResponse(Request $request, $costingId, bool $markEdited = true)
    {
        $approvals = app(MarketingApprovalService::class);
        if ($approvals->canEditCosting((int) $costingId)) {
            if ($markEdited) {
                $approvals->markEdited('costing', (int) $costingId);
            }
            return null;
        }

        $message = $approvals->status('costing', (int) $costingId) === 'unlocked'
            ? 'Costing sedang di-unlock untuk user lain. Hanya peminta unlock yang bisa mengubahnya.'
            : 'Costing sudah di-approve dan terkunci. Ajukan Request Unlock terlebih dahulu.';
        if ($request->ajax() || $request->expectsJson()) {
            // HTTP 200 + status 423: front-end costing membaca res.status/res.message di callback success.
            return response()->json(['status' => 423, 'message' => $message]);
        }

        return redirect()->route('master-costing')->with('warning', $message);
    }

    private function generateCostingNumbers($buyerId, $now): array
    {
        $db = DB::connection('mysql_sb');
        $buyerCode = $db->table('mastersupplier')
            ->where('Id_Supplier', $buyerId)
            ->value('supplier_code');
        if (!$buyerCode) {
            throw new \RuntimeException('Kode buyer tidak ditemukan untuk membuat KP No.');
        }

        $costSequence = $this->nextCostingSequence('CST-' . $now->format('Y'));
        $kpSequence = $this->nextCostingSequence($buyerCode . '-' . $now->format('Y'));

        return [
            'cost_no' => 'CST/' . $now->format('my') . '/' . str_pad($costSequence, 5, '0', STR_PAD_LEFT),
            'kpno' => $buyerCode . '/' . $now->format('my') . '/' . str_pad($kpSequence, 3, '0', STR_PAD_LEFT),
        ];
    }

    private function nextCostingSequence(string $matter): int
    {
        $db = DB::connection('mysql_sb');
        $counter = $db->table('tempbpb')->where('mattype', $matter)->lockForUpdate()->first();

        if (!$counter) {
            $db->table('tempbpb')->insert(['mattype' => $matter, 'BPBNo' => 1]);
            return 1;
        }

        $next = (int) $counter->BPBNo + 1;
        $db->table('tempbpb')->where('mattype', $matter)->update(['BPBNo' => $next]);
        return $next;
    }

    private function costingDetailTable(string $category): string
    {
        return match ($category) {
            'Fabric', 'Accessories Sewing', 'Accessories Packing' => 'act_costing_mat',
            'Manufacturing' => 'act_costing_mfg',
            'Other Cost' => 'act_costing_oth',
            default => throw new \InvalidArgumentException('Kategori detail Costing tidak valid.'),
        };
    }

    private function saveCostingDetail($costingId, string $category, array $detail): int
    {
        $db = DB::connection('mysql_sb');
        $table = $this->costingDetailTable($category);
        $this->validateCostingItem($category, $detail['item']);
        $headerCurrency = strtoupper((string) $db->table('act_costing')
            ->where('id', $costingId)
            ->value('curr'));
        $currency = strtoupper((string) (($detail['curr'] ?? '') ?: $headerCurrency ?: 'IDR'));
        $price = (float) ($detail['price'] ?? 0);

        $insertData = [
            'id_act_cost' => $costingId,
            'id_item' => $detail['item'],
            'price' => $price,
            'cons' => $detail['cons'] ?? 0,
            'unit' => $detail['unit'] ?? '',
            'allowance' => $detail['allowance'] ?? 0,
            'material_source' => $category === 'Other Cost' ? null : 'LOKAL',
            'jenis_rate' => $headerCurrency === 'USD' ? 'J' : 'B',
            'item_desc' => $detail['desc'] ?? null,
            'supplier_id' => ($detail['supplier'] ?? null) ?: null,
            'curr' => $currency,
            'price_px_idr' => $detail['price_px_idr'] ?? 0,
            'price_px_usd' => $detail['price_px_usd'] ?? 0,
            'value_idr' => $detail['value_idr'] ?? 0,
            'value_usd' => $detail['value_usd'] ?? 0,
            'set' => $category === 'Other Cost' ? null : (($detail['set'] ?? null) ?: null),
        ];
        if ($table !== 'act_costing_mat') {
            $insertData['smv'] = null;
        }

        return $db->table($table)->insertGetId($insertData);
    }

    private function validateCostingItem(string $category, $itemId): void
    {
        $db = DB::connection('mysql_sb');
        if ($category === 'Manufacturing') {
            $exists = $db->table('mastercf')->where('id', $itemId)->exists();
        } elseif ($category === 'Other Cost') {
            $exists = $db->table('masterothers')->where('id', $itemId)->exists();
        } else {
            $groupKeyword = match ($category) {
                'Fabric' => 'FABRIC',
                'Accessories Sewing' => 'SEWING',
                'Accessories Packing' => 'PACKING',
                default => '',
            };
            $exists = $db->table('mastercontents as c')
                ->join('mastertype2 as t', 'c.id_type', '=', 't.id')
                ->join('mastersubgroup as sg', 't.id_sub_group', '=', 'sg.id')
                ->join('mastergroup as g', 'sg.id_group', '=', 'g.id')
                ->where('c.id', $itemId)
                ->whereRaw('UPPER(g.nama_group) LIKE ?', ['%' . $groupKeyword . '%'])
                ->exists();
        }

        if (!$exists) {
            throw new \InvalidArgumentException('Item tidak sesuai dengan kategori Costing.');
        }
    }

    public function copyCosting($id)
    {
        $db = DB::connection('mysql_sb');
        $original = $db->table('act_costing')->where('id', $id)->first();
        if (!$original) {
            return redirect()->route('master-costing')->with('error', 'Data Costing tidak ditemukan.');
        }

        $now = now();
        $copiedFiles = [];

        try {
            [$newId, $costNo] = $db->transaction(function () use ($db, $original, $id, $now, &$copiedFiles) {
            $numbers = $this->generateCostingNumbers($original->id_buyer, $now);
            $pictures = $db->table('act_costing_picture')
                ->where('id_act_cost', $id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($pictures->isEmpty() && $original->attach_file) {
                $pictures = collect([(object) [
                    'file_name' => $original->attach_file,
                    'original_name' => $original->attach_file,
                    'sort_order' => 0,
                ]]);
            }

            $directory = public_path('uploads/costing');
            $newPictures = [];
            foreach ($pictures as $picture) {
                $sourceName = basename($picture->file_name);
                $sourcePath = $directory . DIRECTORY_SEPARATOR . $sourceName;
                if (!$sourceName || !is_file($sourcePath)) {
                    throw new \RuntimeException('File gambar Costing tidak ditemukan: ' . $sourceName);
                }

                $extension = pathinfo($sourceName, PATHINFO_EXTENSION);
                $newName = (string) Str::uuid() . ($extension ? '.' . $extension : '');
                $destinationPath = $directory . DIRECTORY_SEPARATOR . $newName;
                if (!copy($sourcePath, $destinationPath)) {
                    throw new \RuntimeException('Gagal menyalin gambar Costing: ' . $sourceName);
                }
                $copiedFiles[] = $destinationPath;
                $newPictures[] = [
                    'file_name' => $newName,
                    'original_name' => $picture->original_name ?: $sourceName,
                    'sort_order' => $picture->sort_order,
                ];
            }

            $header = [
                'id_pre_cost' => 0,
                'cost_no' => $numbers['cost_no'],
                'cost_date' => $now,
                'kpno' => $numbers['kpno'],
                'id_smode' => $original->id_smode,
                'smv_min' => $original->smv_min,
                'smv_sec' => $original->smv_sec,
                'book_min' => $original->book_min,
                'book_sec' => $original->book_sec,
                'notes' => $original->notes,
                'deldate' => $original->deldate,
                'attach_file' => $newPictures[0]['file_name'] ?? null,
                'status' => $original->status,
                'status_order' => '',
                'id_buyer' => $original->id_buyer,
                'id_product' => $original->id_product,
                'styleno' => $original->styleno,
                'qty_planned' => $original->qty_planned,
                'qty' => $original->qty,
                'username' => Auth::user()->name,
                'curr' => $original->curr,
                'vat' => $original->vat,
                'deal_allow' => $original->deal_allow,
                'unit' => $original->unit,
                'dateinput' => $now,
                'ga_cost' => $original->ga_cost,
                'cfm_price' => $original->cfm_price,
                'comm_cost' => $original->comm_cost,
                'brand' => $original->brand,
                'main_dest' => $original->main_dest,
                'aktif' => 'Y',
                'type_ws' => $original->type_ws,
                'mkt_order' => $original->mkt_order,
                'rate' => $original->rate,
                'costing_type' => $original->costing_type,
                'season_id' => $original->season_id,
                'product_set' => $original->product_set,
                'shipment_type' => $original->shipment_type,
                'rate_from_idr' => $original->rate_from_idr,
                'market' => $original->market,
                'ga_percent' => $original->ga_percent,
            ];
            $newId = $db->table('act_costing')->insertGetId($header);

            foreach (['act_costing_mat', 'act_costing_mfg', 'act_costing_oth'] as $table) {
                $rows = $db->table($table)->where('id_act_cost', $id)->get();
                foreach ($rows as $row) {
                    $row = (array) $row;
                    unset($row['id']);
                    $row['id_act_cost'] = $newId;
                    $db->table($table)->insert($row);
                }
            }

            foreach ($newPictures as $picture) {
                $db->table('act_costing_picture')->insert([
                    'id_act_cost' => $newId,
                    'file_name' => $picture['file_name'],
                    'original_name' => $picture['original_name'],
                    'sort_order' => $picture['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            app(MarketingBomService::class)->ensureForCosting($newId);

            app(MarketingHistoryService::class)->record(
                $newId,
                'costing',
                'copied',
                "Costing {$numbers['cost_no']} disalin dari {$original->cost_no}.",
                $newId
            );

            return [$newId, $numbers['cost_no']];
            });
        } catch (\Throwable $exception) {
            foreach ($copiedFiles as $copiedFile) {
                if (is_file($copiedFile) && !unlink($copiedFile)) {
                    logger()->error('Failed to remove copied Costing image after copy failure.', [
                        'path' => $copiedFile,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }

            throw $exception;
        }

        return redirect()->route('edit-costing', $newId)->with('success', "Costing berhasil dicopy! No. Costing baru: $costNo");
    }

    public function edit($id)
    {
        return $this->costingPage($id, false);
    }

    // Detail Costing = halaman Edit dalam mode baca saja (semua kategori tampil, tanpa form simpan).
    public function show($id)
    {
        return $this->costingPage($id, true);
    }

    private function costingPage($id, bool $viewOnly)
    {
        $db = DB::connection('mysql_sb');
        $costing = $db->table('act_costing as a')
            ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
            ->select('a.*', 'p.product_group', 'p.product_item as product_item_name')
            ->where('a.id', $id)
            ->first();
        if (!$costing) {
            return redirect()->route('master-costing')->with('error', 'Data Costing tidak ditemukan!');
        }

        $currencyId = $db->table('masterpilihan')
            ->where('kode_pilihan', 'Curr')
            ->where('nama_pilihan', $costing->curr)
            ->value('id');
        $costing->no_costing = $costing->cost_no;
        $costing->buyer = $costing->id_buyer;
        $costing->buyer_code = $db->table('mastersupplier')
            ->where('Id_Supplier', $costing->id_buyer)
            ->value('supplier_code');
        $costing->style = $costing->styleno;
        $costing->ship_mode = $costing->id_smode;
        $costing->curr = $currencyId;
        $costing->type = $costing->costing_type ?? 'single';
        $costing->tipe_ws = $costing->type_ws;
        $costing->product_item = $costing->id_product;
        $costing->marketing_order = $db->table('master_mkt_order')
            ->where('mkt_order', $costing->mkt_order)
            ->value('id');
        $costing->rate_to_idr = $costing->rate;
        $costing->confirm_price = $costing->cfm_price;
        $costing->smv = $costing->smv_min;
        $costing->foto = $costing->attach_file;
        $costing->created_by = $costing->username;
        $costing->created_at = $costing->dateinput;

        $details = $this->getCostingDetail($id, $costing->rate);

        $buyers = $db->table('mastersupplier')
                     ->where('tipe_sup', 'C')
                     ->orderBy('supplier', 'asc')
                     ->get();

        $product_groups = $db->table('masterproduct')
                             ->select('product_group')
                             ->groupBy('product_group')
                             ->get();

        $currencies = $db->table('masterpilihan')
                         ->where('kode_pilihan', 'Curr')
                         ->get();

        $shipmodes = $db->table('mastershipmode')
                        ->orderBy('shipmode', 'asc')
                        ->get();

        $marketing_orders = $db->table('master_mkt_order')
                               ->orderBy('mkt_order', 'asc')
                               ->get();

        $status_cst = $db->table('masterpilihan')
                         ->where('kode_pilihan', 'ST_CST')
                         ->get();

        $units = $db->table('masterpilihan')
           ->where('kode_pilihan', 'Satuan')
           ->get();

        $set = $db->table('master_set')
           ->get();

        $pictures = $db->table('act_costing_picture')
            ->where('id_act_cost', $id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        if ($pictures->isEmpty() && !empty($costing->foto)) {
            $pictures = collect([(object) [
                'file_name' => $costing->foto,
                'original_name' => $costing->foto,
            ]]);
        }

        $suppliers = $db->table('mastersupplier')->orderBy('supplier', 'asc')->get();

        $destinations = $db->table('master_destination')->orderBy('country_name', 'asc')->get();

        $master_set = $db->table('master_set')->orderBy('urutan', 'asc')->get();

        $seasons = $db->table('masterseason')->orderBy('season', 'asc')->get();

        return view($viewOnly ? 'marketing.costing.detail' : 'marketing.costing.edit', [
            'costing'        => $costing,
            'buyers'           => $buyers,
            'product_groups'   => $product_groups,
            'currencies'       => $currencies,
            'shipmodes'        => $shipmodes,
            'marketing_orders' => $marketing_orders,
            'status_cst'       => $status_cst,
            'units'       => $units,
            'suppliers'       => $suppliers,
            'details'        => $details,
            'pictures'       => $pictures,
            'master_set'        => $master_set,
            'set'        => $set,
            'destinations'        => $destinations,
            'seasons'             => $seasons,
            'unlock'              => app(MarketingApprovalService::class)->costingUnlockState((int) $id),
            'viewOnly'            => $viewOnly,
            'buyerName'           => $db->table('mastersupplier')->where('Id_Supplier', $costing->id_buyer)->value('Supplier'),
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing",
            'containerFluid' => true
        ]);
    }

    public function cancelUnlockRequest($id)
    {
        return $this->lockAction(fn () => app(MarketingApprovalService::class)->cancelUnlockRequest((int) $id), 'Request unlock dibatalkan.');
    }

    public function cancelEditing($id)
    {
        return $this->lockAction(fn () => app(MarketingApprovalService::class)->relockCosting((int) $id, false), 'Edit dibatalkan, Costing dikunci lagi.');
    }

    private function lockAction(callable $action, string $message)
    {
        try {
            $action();
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', $message);
    }


    private function getCostingDetail($costingId, $rateToIdr)
    {
        $db = DB::connection('mysql_sb');
        $details = collect([
            'Fabric' => collect(),
            'Accessories Sewing' => collect(),
            'Accessories Packing' => collect(),
            'Manufacturing' => collect(),
            'Other Cost' => collect(),
        ]);
        $rateToIdr = (float) $rateToIdr;

        $materials = $db->table('act_costing_mat as d')
            ->leftJoin('mastercontents as c', 'd.id_item', '=', 'c.id')
            ->leftJoin('mastertype2 as t', 'c.id_type', '=', 't.id')
            ->leftJoin('mastersubgroup as sg', 't.id_sub_group', '=', 'sg.id')
            ->leftJoin('mastergroup as g', 'sg.id_group', '=', 'g.id')
            ->leftJoin('mastersupplier as s', 'd.supplier_id', '=', 's.Id_Supplier')
            ->leftJoin('master_set as ms', 'd.set', '=', 'ms.id')
            ->where('d.id_act_cost', $costingId)
            ->select('d.*', 'c.nama_contents', 'g.nama_group', 'sg.nama_sub_group', 't.nama_type', 's.Supplier as nama_supplier', 'ms.nama as nama_set')
            ->get();

        foreach ($materials as $detail) {
            $groupName = strtoupper((string) $detail->nama_group);
            $category = str_contains($groupName, 'FABRIC')
                ? 'Fabric'
                : (str_contains($groupName, 'SEWING')
                    ? 'Accessories Sewing'
                    : (str_contains($groupName, 'PACKING') ? 'Accessories Packing' : null));
            if (!$category) {
                continue;
            }

            $detail->type = $category;
            $detail->item_id = $detail->id_item;
            $detail->nama_item = trim(implode(' ', array_filter([
                $detail->id_item,
                $detail->nama_group,
                $detail->nama_sub_group,
                $detail->nama_type,
                $detail->nama_contents,
            ])));
            $detail->item_desc = $detail->item_desc ?? '';
            $this->collectCostingDetail($detail, $rateToIdr);
            $details[$category]->push($detail);
        }

        $manufacturing = $db->table('act_costing_mfg as d')
            ->leftJoin('mastercf as m', 'd.id_item', '=', 'm.id')
            ->leftJoin('mastersupplier as s', 'd.supplier_id', '=', 's.Id_Supplier')
            ->leftJoin('master_set as ms', 'd.set', '=', 'ms.id')
            ->where('d.id_act_cost', $costingId)
            ->select('d.*', 'm.cfcode', 'm.cfdesc', 's.Supplier as nama_supplier', 'ms.nama as nama_set')
            ->get();
        foreach ($manufacturing as $detail) {
            $detail->type = 'Manufacturing';
            $detail->item_id = $detail->id_item;
            $detail->nama_item = trim(($detail->cfcode ?? '') . ' ' . ($detail->cfdesc ?? ''));
            $detail->item_desc = $detail->item_desc ?? '';
            $this->collectCostingDetail($detail, $rateToIdr);
            $details['Manufacturing']->push($detail);
        }

        $otherCosts = $db->table('act_costing_oth as d')
            ->leftJoin('masterothers as o', 'd.id_item', '=', 'o.id')
            ->leftJoin('mastersupplier as s', 'd.supplier_id', '=', 's.Id_Supplier')
            ->where('d.id_act_cost', $costingId)
            ->select('d.*', 'o.otherscode', 'o.othersdesc', 's.Supplier as nama_supplier')
            ->get();
        foreach ($otherCosts as $detail) {
            $detail->type = 'Other Cost';
            $detail->item_id = $detail->id_item;
            $detail->nama_item = trim(($detail->otherscode ?? '') . ' ' . ($detail->othersdesc ?? ''));
            $detail->item_desc = $detail->item_desc ?? '';
            $this->collectCostingDetail($detail, $rateToIdr);
            $details['Other Cost']->push($detail);
        }

        return $details;
    }

    private function collectCostingDetail($detail, float $rateToIdr): void
    {
        $price = (float) ($detail->price ?? 0);
        $isUsd = $detail->jenis_rate !== null
            ? strtoupper((string) $detail->jenis_rate) === 'J'
            : strtoupper((string) ($detail->curr ?? '')) === 'USD';
        $priceIdr = $detail->price_px_idr ?? ($isUsd ? $price * $rateToIdr : $price);
        $priceUsd = $detail->price_px_usd ?? ($isUsd ? $price : ($rateToIdr > 0 ? $price / $rateToIdr : 0));

        $detail->price = $price;
        $detail->price_px_idr = (float) $priceIdr;
        $detail->price_px_usd = (float) $priceUsd;
        $detail->cons = (float) ($detail->cons ?? 0);
        $detail->allowance = (float) ($detail->allowance ?? 0);
        $detail->value_idr = (float) ($detail->value_idr ?? ($detail->cons * $detail->price_px_idr));
        $detail->value_usd = (float) ($detail->value_usd ?? ($detail->cons * $detail->price_px_usd));
        $detail->set = $detail->set ?? '';
    }

    private function decorateLegacyCosting($costing): void
    {
        $costing->no_costing = $costing->cost_no;
        $costing->buyer = $costing->nama_buyer ?? $costing->id_buyer;
        $costing->style = $costing->styleno;
        $costing->ship_mode = $costing->nama_ship_mode ?? $costing->id_smode;
        $costing->type = $costing->costing_type ?? 'single';
        $costing->rate_to_idr = $costing->rate;
        $costing->confirm_price = $costing->cfm_price;
        $costing->foto = $costing->attach_file;
        $costing->created_by = $costing->username;
        $costing->created_at = $costing->dateinput;
        $costing->smv = $costing->smv_min;
        $costing->product_item = $costing->nama_product_item ?? $costing->id_product;
        $costing->nama_curr = $costing->nama_curr ?? $costing->curr;
    }

    public function getItemContents(Request $request)
    {
        $kategori = $request->kategori;
        $html = "<option value=''>Choose Content</option>";

        if (empty($kategori)) {
            return response()->json($html);
        }

        $mysql_sb = DB::connection('mysql_sb');


        if ($kategori == 'Manufacturing') {

            $items = $mysql_sb->table('mastercf')
                ->select('id as isi', $mysql_sb->raw("CONCAT(cfcode, ' ', cfdesc) as tampil"))
                ->orderBy('id', 'DESC')
                ->get();

            foreach ($items as $item){
                $html .= "<option value='{$item->isi}'>{$item->tampil}</option>";
            }

        } else if ($kategori == 'Other Cost') {

            $others = $mysql_sb->table('masterothers')
                ->select('id as isi', $mysql_sb->raw("CONCAT(otherscode, ' ', othersdesc) as tampil"))
                ->orderBy('id', 'desc')
                ->get();

            foreach ($others as $o) {
                $html .= '<option value="' . $o->isi . '">' . $o->tampil . '</option>';
            }

        } else {

            $db_kategori = strtoupper($kategori);


            if ($db_kategori == 'ACCESSORIES SEWING') $db_kategori = 'ACCESORIES SEWING';
            if ($db_kategori == 'ACCESSORIES PACKING') $db_kategori = 'ACCESORIES PACKING';

            $items = $mysql_sb->table('mastergroup as a')
                ->join('mastersubgroup as s', 'a.id', '=', 's.id_group')
                ->join('mastertype2 as d', 's.id', '=', 'd.id_sub_group')
                ->join('mastercontents as e', 'd.id', '=', 'e.id_type')
                ->where('e.aktif', 'Y')
                ->where('a.nama_group', $db_kategori)
                ->select(
                    'e.id as isi',
                    $mysql_sb->raw("CONCAT(e.id, ' ', a.nama_group, ' ', s.nama_sub_group, ' ', d.nama_type, ' ', e.nama_contents) as tampil")
                )
                ->get();

            foreach ($items as $item) {
                $html .= "<option value='{$item->isi}'>{$item->tampil}</option>";
            }
        }

        return response()->json($html);
    }

    public function storeDetail(Request $request)
    {
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }
        $request->validate([
            'id_costing' => ['required', 'exists:mysql_sb.act_costing,id'],
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'item' => ['required', 'integer'],
            'cons' => ['nullable', 'numeric'],
            'price' => ['nullable', 'numeric'],
            'allowance' => ['nullable', 'numeric'],
            'set' => ['nullable', 'integer'],
        ]);
        $db = DB::connection('mysql_sb');
        try {
            $detailId = $db->transaction(function () use ($request) {
                $detailId = $this->saveCostingDetail($request->id_costing, $request->category, [
                    'item' => $request->item,
                    'desc' => $request->desc,
                    'supplier' => $request->supplier,
                    'curr' => $request->curr,
                    'price' => $request->price,
                    'cons' => $request->cons,
                    'unit' => $request->unit,
                    'price_px_idr' => $request->price_px_idr,
                    'price_px_usd' => $request->price_px_usd,
                    'allowance' => $request->allowance,
                    'set' => $request->set,
                    'value_idr' => $request->value_idr,
                    'value_usd' => $request->value_usd,
                ]);
                app(MarketingHistoryService::class)->costingItemsChanged((int) $request->id_costing, 'items_added', (string) $request->category);

                return $detailId;
            });

            return response()->json([
                'status' => 200,
                'message' => 'Item berhasil ditambahkan!',
                'insert_id' => $detailId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }

    public function updateHeader(Request $request, $id)
    {
        if ($locked = $this->costingLockedResponse($request, $id)) {
            return $locked;
        }
        $request->validate([
            'buyer' => ['required', 'exists:mysql_sb.mastersupplier,Id_Supplier'],
            'product_item' => ['required', 'exists:mysql_sb.masterproduct,id'],
            'ship_mode' => ['required', 'exists:mysql_sb.mastershipmode,id'],
            'curr' => ['required', 'exists:mysql_sb.masterpilihan,id'],
            'qty_planned' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'numeric', 'min:0'],
            'smv' => ['required', 'numeric', 'min:0'],
            'deldate' => ['required', 'date'],
            'style' => ['nullable', 'string', self::styleSpacingRule()],
            'upload_foto' => ['sometimes', 'array'],
            'upload_foto.*' => ['image', 'max:10240'],
            'clear_existing_pictures' => ['sometimes', 'boolean'],
            'remove_picture_files' => ['sometimes', 'array'],
            'remove_picture_files.*' => ['string', 'max:255'],
        ]);

        $db = DB::connection('mysql_sb');

        try {
            $currency = $db->table('masterpilihan')
                ->where('id', $request->curr)
                ->where('kode_pilihan', 'Curr')
                ->value('nama_pilihan');
            if (!$currency) {
                throw new \RuntimeException('Currency yang dipilih tidak ditemukan.');
            }
            $marketingOrder = $db->table('master_mkt_order')
                ->where('id', $request->marketing_order)
                ->value('mkt_order');

            $updateData = [
                'id_buyer'        => $request->buyer,
                'brand'           => Str::upper($request->brand),
                'season_id'       => $request->season_id,
                'id_product'      => $request->product_item,
                'styleno'         => Str::upper($request->style),
                'id_smode'        => $request->ship_mode,
                'main_dest'       => is_array($request->main_dest) ? implode(', ', $request->main_dest) : $request->main_dest,
                'market'          => Str::upper($request->market),
                'curr'            => $currency,
                'mkt_order'       => $marketingOrder,
                'shipment_type'   => $request->shipment_type,
                'costing_type'    => $request->type,
                'type_ws'         => $request->tipe_ws,
                'product_set'      => is_array($request->product_set) ? implode(',', $request->product_set) : null,
                'notes'           => Str::upper($request->notes),
                'qty'             => $request->qty,
                'smv_min'         => $request->smv,
                'vat'             => $request->vat,
                'qty_planned'     => $request->qty_planned,
                'rate'            => $request->rate_to_idr,
                'rate_from_idr'   => $request->rate_from_idr,
                'cfm_price'       => $request->confirm_price,
                'deldate'         => $request->deldate,
                'status'          => $request->status,
                'unit'            => $request->unit,
                'ga_percent'      => $request->ga_percent ?? 3,
            ];

            if ($request->boolean('clear_existing_pictures')) {
                $this->clearCostingPictures($id);
                $updateData['attach_file'] = null;
            } elseif ($request->filled('remove_picture_files')) {
                $this->removeCostingPictures($id, $request->input('remove_picture_files', []));
            }

            $pictureNames = $this->saveCostingPictures($request, $id);
            if ($pictureNames) {
                $updateData['attach_file'] = $pictureNames[0];
            }

            $db->transaction(function () use ($db, $id, $request, $updateData) {
                $db->table('act_costing')->where('id', $id)->update($updateData);
                $this->updateStyle($id, $request->style, $request->product_item);
                app(MarketingHistoryService::class)->costingUpdated((int) $id);
            });

            if ($request->boolean('lock_after_save')) {
                app(MarketingApprovalService::class)->relockCosting((int) $id, true);

                return redirect()->back()->with('success', 'Costing disimpan dan dikunci lagi (Approved).');
            }

            if ($request->ajax()) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Data berhasil di update'
                ]);
            }

            return redirect()->back()->with('success', 'Data berhasil di update');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Gagal update: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    private function saveCostingPictures(Request $request, $costingId)
    {
        $files = array_filter((array) $request->file('upload_foto', []));
        if (!$files) {
            return [];
        }

        $directory = public_path('uploads/costing');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the costing image directory.');
        }

        $db = DB::connection('mysql_sb');
        $hasPictures = $db->table('act_costing_picture')
            ->where('id_act_cost', $costingId)
            ->exists();
        if (!$hasPictures) {
            $legacyPicture = $db->table('act_costing')
                ->where('id', $costingId)
                ->value('attach_file');
            if ($legacyPicture) {
                $db->table('act_costing_picture')->insert([
                    'id_act_cost' => $costingId,
                    'file_name' => $legacyPicture,
                    'original_name' => $legacyPicture,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $lastOrder = $db->table('act_costing_picture')
            ->where('id_act_cost', $costingId)
            ->max('sort_order');
        $nextOrder = $lastOrder === null ? 0 : $lastOrder + 1;
        $savedNames = [];

        foreach ($files as $file) {
            $filename = Str::uuid() . '.' . $file->extension();
            $file->move($directory, $filename);
            $savedNames[] = $filename;

            $db->table('act_costing_picture')->insert([
                'id_act_cost' => $costingId,
                'file_name' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'sort_order' => $nextOrder++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $savedNames;
    }

    private function clearCostingPictures($costingId)
    {
        $db = DB::connection('mysql_sb');
        $pictureNames = $db->table('act_costing_picture')
            ->where('id_act_cost', $costingId)
            ->pluck('file_name')
            ->all();
        $legacyPicture = $db->table('act_costing')
            ->where('id', $costingId)
            ->value('attach_file');

        if ($legacyPicture) {
            $pictureNames[] = $legacyPicture;
        }

        $db->table('act_costing_picture')->where('id_act_cost', $costingId)->delete();
        $db->table('act_costing')->where('id', $costingId)->update(['attach_file' => null]);

        $directory = public_path('uploads/costing');
        foreach (array_unique(array_filter($pictureNames)) as $fileName) {
            $safeFileName = basename($fileName);
            $path = $directory . DIRECTORY_SEPARATOR . $safeFileName;
            if (is_file($path) && !unlink($path)) {
                throw new \RuntimeException('Gagal menghapus file gambar Costing: ' . $safeFileName);
            }
        }
    }

    private function removeCostingPictures($costingId, array $fileNames)
    {
        $db = DB::connection('mysql_sb');
        $fileNames = array_values(array_unique(array_filter($fileNames)));
        if (!$fileNames) {
            return;
        }

        $pictures = $db->table('act_costing_picture')
            ->where('id_act_cost', $costingId)
            ->whereIn('file_name', $fileNames)
            ->get(['file_name']);
        $removedNames = $pictures->pluck('file_name')->all();
        $legacyPicture = $db->table('act_costing')
            ->where('id', $costingId)
            ->value('attach_file');
        if ($legacyPicture && in_array($legacyPicture, $fileNames, true)) {
            $removedNames[] = $legacyPicture;
        }

        if (!$removedNames) {
            return;
        }

        $db->table('act_costing_picture')
            ->where('id_act_cost', $costingId)
            ->whereIn('file_name', $removedNames)
            ->delete();

        $nextPicture = $db->table('act_costing_picture')
            ->where('id_act_cost', $costingId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('file_name');
        $db->table('act_costing')->where('id', $costingId)->update([
            'attach_file' => $nextPicture,
        ]);

        $directory = public_path('uploads/costing');
        foreach (array_unique($removedNames) as $fileName) {
            $safeFileName = basename($fileName);
            $path = $directory . DIRECTORY_SEPARATOR . $safeFileName;
            if (is_file($path) && !unlink($path)) {
                throw new \RuntimeException('Gagal menghapus file gambar Costing: ' . $safeFileName);
            }
        }
    }

    function updateStyle($id, $style, $product_item)
    {
        $db = DB::connection('mysql_sb');

        $bom = $db->table('bom_marketing')->where('id_costing', $id)->first();
        if (!$bom) {
            return;
        }
        $db->table('bom_marketing')->where('id_costing', $id)->update(['style' => $style]);

        $so = $db->table('so')->where('id_bom', $bom->id)->first();
        if (!$so) {
            return;
        }
        $db->table('so')->where('id_bom', $bom->id)->update(['style' => $style, 'id_product' => $product_item]);

        $actCosting = $db->table('act_costing')->where('id', $so->id_cost)->first();
        if (!$actCosting) {
            return;
        }
        $db->table('act_costing')->where('id', $so->id_cost)->update(['styleno' => $style]);
    }
    public function destroyDetail(Request $request, $id)
    {
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }
        $request->validate([
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'id_costing' => ['required', 'exists:mysql_sb.act_costing,id'],
        ]);
        try {
            $db = DB::connection('mysql_sb');
            $wasDeleted = $db->transaction(function () use ($db, $request, $id) {
                $deleted = $db->table($this->costingDetailTable($request->category))
                    ->where('id', $id)
                    ->where('id_act_cost', $request->id_costing)
                    ->delete();
                if (!$deleted) {
                    return false;
                }
                app(MarketingHistoryService::class)->costingItemsChanged((int) $request->id_costing, 'items_deleted', (string) $request->category);

                return true;
            });
            if (!$wasDeleted) {
                return response()->json(['status' => 404, 'message' => 'Data detail tidak ditemukan.']);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Data berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ]);
        }
    }

    public function printPdf($id)
    {
        $pdfData = $this->getCostingPdfData($id);
        if (!$pdfData) {
            return redirect()->back()->with('error', 'Data Costing tidak ditemukan.');
        }

        $pdf = Pdf::loadView('marketing.costing.pdf', $pdfData)
                ->setPaper('a4', 'landscape');

        return $pdf->stream('Costing_'.$pdfData['costing']->no_costing.'.pdf');
    }

    public function printBatchPdf(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:mysql_sb.act_costing,id'],
        ]);

        $documents = [];
        $styles = '';
        foreach ($validated['ids'] as $id) {
            $pdfData = $this->getCostingPdfData($id);
            if (!$pdfData) {
                abort(404, 'Data Costing tidak ditemukan.');
            }

            $html = view('marketing.costing.pdf', $pdfData)->render();
            if (!preg_match('/<style\b[^>]*>(.*?)<\/style>/is', $html, $styleMatch)
                || !preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $html, $bodyMatch)) {
                throw new \RuntimeException('Template PDF Costing tidak memiliki struktur dokumen yang valid.');
            }
            if ($styles === '') {
                $styles = $styleMatch[1];
            }
            if ($documents) {
                $documents[] = '<div style="page-break-before: always;"></div>';
            }
            $documents[] = $bodyMatch[1];
        }

        $combinedHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $styles
            . '</style></head><body>' . implode("\n", $documents) . '</body></html>';

        return Pdf::loadHTML($combinedHtml)
            ->setPaper('a4', 'landscape')
            ->stream('Costing_Batch.pdf');
    }

    private function getCostingPdfData($id)
    {
        $db = DB::connection('mysql_sb');
        $costing = $db->table('act_costing as a')
            ->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
            ->leftJoin('mastershipmode as sm', 'a.id_smode', '=', 'sm.id')
            ->select(
                'a.*',
                'b.Supplier as nama_buyer',
                'p.product_item as nama_product_item',
                'sm.shipmode as nama_ship_mode',
                'p.product_group',
                'mp.nama_pilihan as nama_curr'
            )
            ->leftJoin('masterpilihan as mp', function ($join) {
                $join->on('a.curr', '=', 'mp.nama_pilihan')
                    ->where('mp.kode_pilihan', '=', 'Curr');
            })
            ->where('a.id', $id)
            ->first();
        if (!$costing) {
            return null;
        }

        $this->decorateLegacyCosting($costing);
        $costing->nama_dest = '-';
        if (!empty($costing->main_dest)) {
            $destinationIds = array_map('trim', explode(',', $costing->main_dest));
            $destinations = $db->table('master_destination')
                ->whereIn('id', $destinationIds)
                ->pluck('country_name')
                ->toArray();
            if ($destinations) {
                $costing->nama_dest = implode(', ', $destinations);
            }
        }

        return [
            'costing' => $costing,
            'details' => $this->getCostingDetail($id, $costing->rate),
            'master_set' => $db->table('master_set')->get(),
        ];
    }

    public function costingItems($id)
    {
        $costing = DB::connection('mysql_sb')->table('act_costing')
            ->select('id', 'cost_no', 'rate')
            ->where('id', $id)
            ->first();

        if (!$costing) {
            return response()->json([
                'status' => 404,
                'message' => 'Data Costing tidak ditemukan.',
            ], 404);
        }

        $details = $this->getCostingDetail($costing->id, $costing->rate);
        $items = [];
        foreach ($details as $category => $rows) {
            $items[$category] = $rows->map(function ($row) {
                return [
                    'item' => $row->nama_item,
                    'desc' => $row->item_desc,
                    'set' => $row->nama_set ?? '',
                    'supplier' => $row->nama_supplier ?? '',
                    'cons' => $row->cons,
                    'curr' => $row->curr ?? '',
                    'price' => $row->price,
                    'unit' => $row->unit ?? '',
                ];
            })->values();
        }

        return response()->json([
            'status' => 200,
            'cost_no' => $costing->cost_no,
            'items' => $items,
        ]);
    }

    public function getDetailRow(Request $request, $id)
    {
        $request->validate([
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'id_costing' => ['required', 'exists:mysql_sb.act_costing,id'],
        ]);
        $db = DB::connection('mysql_sb');
        $data = $db->table($this->costingDetailTable($request->category))
            ->where('id', $id)
            ->where('id_act_cost', $request->id_costing)
            ->first();
        if ($data) {
            $data->item_id = $data->id_item;
            return response()->json(['status' => 200, 'data' => $data]);
        }
        return response()->json(['status' => 404, 'message' => 'Data tidak ditemukan']);
    }


    public function updateDetail(Request $request)
    {
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }
        $request->validate([
            'id_costing' => ['required', 'exists:mysql_sb.act_costing,id'],
            'id_detail' => ['required', 'integer'],
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'item' => ['required', 'integer'],
            'cons' => ['nullable', 'numeric'],
            'price' => ['nullable', 'numeric'],
            'allowance' => ['nullable', 'numeric'],
            'set' => ['nullable', 'integer'],
        ]);
        $db = DB::connection('mysql_sb');
        $table = $this->costingDetailTable($request->category);
        $detail = $db->table($table)
            ->where('id', $request->id_detail)
            ->where('id_act_cost', $request->id_costing)
            ->first();
        if (!$detail) {
            return response()->json(['status' => 404, 'message' => 'Data detail tidak ditemukan.']);
        }
        $this->validateCostingItem($request->category, $request->item);

        $headerCurrency = strtoupper((string) $db->table('act_costing')
            ->where('id', $request->id_costing)
            ->value('curr'));
        $currency = strtoupper((string) $request->curr);
        $updateData = [
            'id_item' => $request->item,
            'price' => $request->price,
            'cons' => $request->cons,
            'unit' => $request->unit,
            'allowance' => $request->allowance,
            'material_source' => $request->category === 'Other Cost' ? null : 'LOKAL',
            'jenis_rate' => $headerCurrency === 'USD' ? 'J' : 'B',
            'item_desc' => $request->desc,
            'supplier_id' => $request->supplier,
            'curr' => $request->curr,
            'price_px_idr' => $request->price_px_idr,
            'price_px_usd' => $request->price_px_usd,
            'set' => $request->category === 'Other Cost' ? null : $request->set,
            'value_idr' => $request->value_idr,
            'value_usd' => $request->value_usd,
        ];
        if ($table !== 'act_costing_mat') {
            $updateData['smv'] = null;
        }
        // Histori hanya kalau ada nilai yang benar-benar berubah (Save tabel bisa mengirim ulang baris yang sama).
        $changed = collect($updateData)->contains(function ($value, $column) use ($detail) {
            if (!property_exists($detail, $column)) {
                return false;
            }
            $old = $detail->{$column};
            if (is_numeric($old) && is_numeric($value)) {
                return round((float) $old, 7) !== round((float) $value, 7);
            }
            return trim((string) $old) !== trim((string) $value);
        });

        $db->transaction(function () use ($db, $table, $request, $updateData, $changed) {
            $db->table($table)
                ->where('id', $request->id_detail)
                ->where('id_act_cost', $request->id_costing)
                ->update($updateData);
            if ($changed) {
                app(MarketingHistoryService::class)->costingItemsChanged((int) $request->id_costing, 'items_updated', (string) $request->category);
            }
        });

        return response()->json(['status' => 200, 'message' => 'Success']);
    }

    // public function printExcel($id)
    // {
    //     $db = DB::connection('mysql_sb');

    //     $costing = $db->table('act_costing_new as a')
    //         ->leftJoin('mastersupplier as b', 'a.buyer', '=', 'b.Id_Supplier')
    //         ->leftJoin('masterproduct as p', 'a.product_item', '=', 'p.id')
    //         ->leftJoin('mastershipmode as sm', 'a.ship_mode', '=', 'sm.id')
    //         ->select(
    //             'a.*',
    //             'b.Supplier as nama_buyer',
    //             'p.product_item as nama_product_item',
    //             'sm.shipmode as nama_ship_mode'
    //         )
    //         ->where('a.id', $id)
    //         ->first();

    //     if (!$costing) {
    //         return redirect()->back()->with('error', 'Data Costing tidak ditemukan.');
    //     }

    //     $nama_destinasi = '-';
    //     if (!empty($costing->main_dest)) {
    //         $arr_id_dest = array_map('trim', explode(',', $costing->main_dest));

    //         $destinations = $db->table('master_destination')
    //             ->whereIn('id', $arr_id_dest)
    //             ->pluck('country_name')
    //             ->toArray();

    //         if (count($destinations) > 0) {
    //             $nama_destinasi = implode(', ', $destinations);
    //         }
    //     }
    //     $costing->nama_dest = $nama_destinasi;

    //     $details_raw = $db->table('act_costing_detail_new as ac')
    //         ->leftJoin('mastersupplier as s', 'ac.supplier_id', '=', 's.Id_Supplier')
    //         ->leftJoin('mastercf as cf', 'ac.item_id', '=', 'cf.id')
    //         ->leftJoin('masterothers as ot', 'ac.item_id', '=', 'ot.id')
    //         ->leftJoin('mastercontents as mc', 'ac.item_id', '=', 'mc.id')
    //         ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
    //         ->leftJoin('mastersubgroup as ms', 'mt.id_sub_group', '=', 'ms.id')
    //         ->leftJoin('mastergroup as mg', 'ms.id_group', '=', 'mg.id')
    //         ->leftJoin('master_set as set', 'ac.set', '=', 'set.id')
    //         ->select(
    //             'ac.*',
    //             's.Supplier as nama_supplier',
    //             'set.nama as nama_set',
    //             $db->raw("
    //                 CASE
    //                     WHEN ac.type = 'Manufacturing' THEN CONCAT(cf.cfcode, ' ', cf.cfdesc)
    //                     WHEN ac.type = 'Other Cost' THEN CONCAT(ot.otherscode, ' ', ot.othersdesc)
    //                     ELSE CONCAT(mg.nama_group, ' ', ms.nama_sub_group, ' ', mt.nama_type, ' ', mc.nama_contents)
    //                 END as nama_item
    //             "),
    //             $db->raw("
    //                 CASE
    //                     WHEN ac.type = 'Manufacturing' THEN cf.cfdesc
    //                     WHEN ac.type = 'Other Cost' THEN ot.othersdesc
    //                     ELSE mc.nama_contents
    //                 END as item_desc
    //             ")
    //         )
    //         ->where('ac.id_costing', $id)
    //         ->orderBy('ac.id', 'asc')
    //         ->get();

    //     $details = [
    //         'Fabric' => [],
    //         'Accessories Sewing' => [],
    //         'Accessories Packing' => [],
    //         'Manufacturing' => [],
    //         'Other Cost' => []
    //     ];

    //     foreach ($details_raw as $det) {
    //         $cat = $det->type;
    //         if (array_key_exists($cat, $details)) {
    //             $details[$cat][] = $det;
    //         }
    //     }

    //     return view('marketing.costing.excel', compact('costing', 'details'));
    // }

    // public function printExcel($id)
    // {
    //     $db = DB::connection('mysql_sb');

    //     $costing = $db->table('act_costing_new as a')
    //         ->leftJoin('mastersupplier as b', 'a.buyer', '=', 'b.Id_Supplier')
    //         ->leftJoin('masterproduct as p', 'a.product_item', '=', 'p.id')
    //         ->leftJoin('mastershipmode as sm', 'a.ship_mode', '=', 'sm.id')
    //         ->select('a.*', 'b.Supplier as nama_buyer', 'p.product_item as nama_product_item', 'sm.shipmode as nama_ship_mode')
    //         ->where('a.id', $id)->first();

    //     if (!$costing) return redirect()->back()->with('error', 'Data Costing tidak ditemukan.');

    //     $nama_destinasi = '-';
    //     if (!empty($costing->main_dest)) {
    //         $arr_id_dest = array_map('trim', explode(',', $costing->main_dest));
    //         $destinations = $db->table('master_destination')->whereIn('id', $arr_id_dest)->pluck('country_name')->toArray();
    //         if (count($destinations) > 0) $nama_destinasi = implode(', ', $destinations);
    //     }
    //     $costing->nama_dest = $nama_destinasi;

    //     $details_raw = $db->table('act_costing_detail_new as ac')
    //         ->leftJoin('mastersupplier as s', 'ac.supplier_id', '=', 's.Id_Supplier')
    //         ->leftJoin('mastercf as cf', 'ac.item_id', '=', 'cf.id')
    //         ->leftJoin('masterothers as ot', 'ac.item_id', '=', 'ot.id')
    //         ->leftJoin('mastercontents as mc', 'ac.item_id', '=', 'mc.id')
    //         ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
    //         ->leftJoin('mastersubgroup as ms', 'mt.id_sub_group', '=', 'ms.id')
    //         ->leftJoin('mastergroup as mg', 'ms.id_group', '=', 'mg.id')
    //         ->leftJoin('master_set as set', 'ac.set', '=', 'set.id')
    //         ->select('ac.*', 's.Supplier as nama_supplier', 'set.nama as nama_set',
    //             $db->raw("CASE WHEN ac.type = 'Manufacturing' THEN CONCAT(cf.cfcode, ' ', cf.cfdesc) WHEN ac.type = 'Other Cost' THEN CONCAT(ot.otherscode, ' ', ot.othersdesc) ELSE CONCAT(mg.nama_group, ' ', ms.nama_sub_group, ' ', mt.nama_type, ' ', mc.nama_contents) END as nama_item"),
    //             $db->raw("CASE WHEN ac.type = 'Manufacturing' THEN cf.cfdesc WHEN ac.type = 'Other Cost' THEN ot.othersdesc ELSE mc.nama_contents END as item_desc")
    //         )->where('ac.id_costing', $id)->orderBy('ac.id', 'asc')->get();

    //     $details = ['Fabric' => [], 'Accessories Sewing' => [], 'Accessories Packing' => [], 'Manufacturing' => [], 'Other Cost' => []];
    //     foreach ($details_raw as $det) { if (array_key_exists($det->type, $details)) $details[$det->type][] = $det; }


    //     $sum_fab_idr = 0; $sum_sew_idr = 0; $sum_pack_idr = 0; $sum_mfg_idr = 0; $sum_oth_norm_idr = 0;
    //     $sum_fab_usd = 0; $sum_sew_usd = 0; $sum_pack_usd = 0; $sum_mfg_usd = 0; $sum_oth_norm_usd = 0;
    //     $overhead_row = null;


    //     foreach(['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $key) {
    //         if (isset($details[$key])) {
    //             foreach($details[$key] as $det) {
    //                 if ($key == 'Fabric') {
    //                     $sum_fab_idr += $det->value_idr; $sum_fab_usd += $det->value_usd;
    //                 } elseif ($key == 'Accessories Sewing') {
    //                     $sum_sew_idr += $det->value_idr; $sum_sew_usd += $det->value_usd;
    //                 } elseif ($key == 'Accessories Packing') {
    //                     $sum_pack_idr += $det->value_idr; $sum_pack_usd += $det->value_usd;
    //                 } elseif ($key == 'Manufacturing') {
    //                     $sum_mfg_idr += $det->value_idr; $sum_mfg_usd += $det->value_usd;
    //                 }
    //             }
    //         }
    //     }


    //     $base_material_idr = $sum_fab_idr + $sum_sew_idr + $sum_pack_idr;
    //     $base_material_usd = $sum_fab_usd + $sum_sew_usd + $sum_pack_usd;


    //     if (isset($details['Other Cost'])) {
    //         foreach($details['Other Cost'] as $det) {
    //             if (str_contains(strtoupper($det->nama_item), 'OVERHEAD')) {
    //                 $overhead_row = $det;
    //             } else {
    //                 if ($det->allowance > 0) {
    //                     $det->value_idr = $base_material_idr * ($det->allowance / 100);
    //                     $det->value_usd = $base_material_usd * ($det->allowance / 100);
    //                 }
    //                 $sum_oth_norm_idr += $det->value_idr;
    //                 $sum_oth_norm_usd += $det->value_usd;
    //             }
    //         }
    //     }


    //     $base_overhead_idr = $base_material_idr + $sum_oth_norm_idr;
    //     $base_overhead_usd = $base_material_usd + $sum_oth_norm_usd;

    //     $overhead_idr = 0; $overhead_usd = 0;
    //     if ($overhead_row) {
    //         $oh_allow = $overhead_row->allowance > 0 ? $overhead_row->allowance : 6;
    //         $overhead_idr = $base_overhead_idr * ($oh_allow / 100);
    //         $overhead_usd = $base_overhead_usd * ($oh_allow / 100);


    //         $overhead_row->value_idr = $overhead_idr;
    //         $overhead_row->value_usd = $overhead_usd;
    //     }

    //     $tot_other_idr = $sum_oth_norm_idr + $overhead_idr;
    //     $tot_other_usd = $sum_oth_norm_usd + $overhead_usd;


    //     $base_ga_idr = $base_material_idr + $sum_mfg_idr + $tot_other_idr;
    //     $base_ga_usd = $base_material_usd + $sum_mfg_usd + $tot_other_usd;

    //     $ga_idr = $base_ga_idr * 0.03;
    //     $ga_usd = $base_ga_usd * 0.03;

    //     $grand_idr = $base_ga_idr + $ga_idr;
    //     $rate_from_idr = $costing->rate_from_idr > 0 ? $costing->rate_from_idr : 15000;
    //     $grand_usd = $grand_idr / $rate_from_idr;

    //     $pembagi_persen = $grand_idr;

    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $sheet->setTitle('Costing');

    //     $styleBoldCenter = ['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
    //     $styleBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    //     $styleHead = array_merge($styleBorder, $styleBoldCenter, ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']]]);
    //     $styleDark = array_merge($styleBorder, ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF343A40']]]);

    //     if (file_exists(public_path('assets/dist/img/nag-logo.png'))) {
    //         $drawing = new Drawing();
    //         $drawing->setName('Logo');
    //         $drawing->setPath(public_path('assets/dist/img/nag-logo.png'));
    //         $drawing->setHeight(50);
    //         $drawing->setCoordinates('A1');
    //         $drawing->setWorksheet($sheet);
    //     }

    //     $sheet->mergeCells('C1:J1'); $sheet->setCellValue('C1', 'PT NIRWANA ALABARE GARMENT')->getStyle('C1')->applyFromArray($styleBoldCenter);
    //     $sheet->mergeCells('C2:J2'); $sheet->setCellValue('C2', 'COSTING')->getStyle('C2')->applyFromArray($styleBoldCenter);

    //     $sheet->setCellValue('A4', 'No Costing')->setCellValue('B4', ': ' . $costing->no_costing);
    //     $sheet->setCellValue('D4', 'Style')->setCellValue('E4', ': ' . $costing->style);
    //     $sheet->setCellValue('G4', 'Ship Mode')->setCellValue('H4', ': ' . ($costing->nama_ship_mode ?? $costing->ship_mode));

    //     $sheet->setCellValue('A5', 'Buyer')->setCellValue('B5', ': ' . $costing->nama_buyer);
    //     $sheet->setCellValue('D5', 'SMV')->setCellValue('E5', ': ' . $costing->smv);
    //     $sheet->setCellValue('G5', 'Ship Type')->setCellValue('H5', ': ' . strtoupper($costing->shipment_type));

    //     $sheet->setCellValue('A6', 'Brand')->setCellValue('B6', ': ' . $costing->brand);
    //     $sheet->setCellValue('D6', 'Qty (PCS)')->setCellValue('E6', ': ' . $costing->qty);
    //     $sheet->setCellValue('G6', 'Rate IDR')->setCellValue('H6', ': ' . $costing->rate_to_idr);

    //     if (!empty($costing->foto) && file_exists(public_path('uploads/costing/' . $costing->foto))) {
    //         $drawing2 = new Drawing();
    //         $drawing2->setName('Costing Photo');
    //         $drawing2->setPath(public_path('uploads/costing/' . $costing->foto));
    //         $drawing2->setHeight(100);
    //         $drawing2->setCoordinates('K3');
    //         $drawing2->setWorksheet($sheet);
    //     }

    //     $row = 9;
    //     $categories_list = [
    //         'Fabric' => 'FABRIC', 'Accessories Sewing' => 'ACCESSORIES SEWING',
    //         'Accessories Packing' => 'ACCESSORIES PACKING', 'Manufacturing' => 'MANUFACTURING', 'Other Cost' => 'OTHER COST'
    //     ];


    //     $grand_tot_val = 0;

    //     foreach ($categories_list as $key => $title) {
    //         $sub_idr = 0; $sub_usd = 0; $sum_val = 0;

    //         if ($key !== 'Other Cost') {
    //             $sheet->mergeCells("A$row:O$row"); $sheet->setCellValue("A$row", $title)->getStyle("A$row:O$row")->applyFromArray($styleDark);
    //             $row++;
    //             $headers = ['NO', 'ITEM', 'SET', 'DESC', 'SUPPLIER', 'PRICE IDR', 'PRICE USD', 'CONS/PC', 'UNIT', 'ALLOW %', 'VALUE IDR', 'VALUE USD', '%', 'QTY BOM', 'VALUE'];
    //             $col = 'A'; foreach ($headers as $h) { $sheet->setCellValue($col.$row, $h); $col++; }
    //             $sheet->getStyle("A$row:O$row")->applyFromArray($styleHead);
    //             $row++;

    //             if (isset($details[$key]) && count($details[$key]) > 0) {
    //                 foreach ($details[$key] as $idx => $det) {
    //                     $sub_idr += $det->value_idr; $sub_usd += $det->value_usd;
    //                     $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) : 0;
    //                     $allow = $det->allowance > 0 ? $det->allowance : 0;
    //                     $qty_bom = ceil((1 + ($allow / 100)) * $costing->qty * $det->cons);
    //                     $tot_val = $qty_bom * $det->price_px_idr;
    //                     $sum_val += $tot_val;

    //                     $sheet->setCellValue("A$row", $idx + 1)
    //                           ->setCellValue("B$row", $det->nama_item)
    //                           ->setCellValue("C$row", $det->nama_set)
    //                           ->setCellValue("D$row", $det->item_desc)
    //                           ->setCellValue("E$row", $det->nama_supplier)
    //                           ->setCellValue("F$row", $det->price_px_idr)
    //                           ->setCellValue("G$row", $det->price_px_usd)
    //                           ->setCellValue("H$row", $det->cons)
    //                           ->setCellValue("I$row", $det->unit)
    //                           ->setCellValue("J$row", $det->allowance)
    //                           ->setCellValue("K$row", $det->value_idr)
    //                           ->setCellValue("L$row", $det->value_usd)
    //                           ->setCellValue("M$row", $persen)
    //                           ->setCellValue("N$row", $qty_bom)
    //                           ->setCellValue("O$row", $tot_val);

    //                     $sheet->getStyle("A$row:O$row")->applyFromArray($styleBorder);
    //                     $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
    //                     $row++;
    //                 }
    //             }
    //             $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) : 0;
    //             $grand_tot_val += $sum_val;

    //             $sheet->mergeCells("A$row:J$row"); $sheet->setCellValue("A$row", "TOTAL $title :")->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //             $sheet->setCellValue("K$row", $sub_idr)->setCellValue("L$row", $sub_usd)->setCellValue("M$row", $sub_persen)->setCellValue("O$row", $sum_val);
    //             $sheet->getStyle("A$row:O$row")->applyFromArray($styleHead);
    //             $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
    //             $row += 1;


    //             if ($key === 'Manufacturing') {
    //                 $sheet->mergeCells("A$row:N$row");
    //                 $sheet->setCellValue("A$row", "GRAND TOTAL VALUE :")->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //                 $sheet->setCellValue("O$row", $grand_tot_val);


    //                 $styleWarning = array_merge($styleBorder, $styleBoldCenter, ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFC107']]]);
    //                 $sheet->getStyle("A$row:O$row")->applyFromArray($styleWarning);
    //                 $row += 1;
    //             }

    //             $row += 1;

    //         } else {

    //             $sheet->mergeCells("A$row:F$row"); $sheet->setCellValue("A$row", $title)->getStyle("A$row:F$row")->applyFromArray($styleDark);
    //             $row++;
    //             $headers = ['NO', 'ITEM', 'ALLOW %', 'VALUE IDR', 'VALUE USD', '%'];
    //             $col = 'A'; foreach ($headers as $h) { $sheet->setCellValue($col.$row, $h); $col++; }
    //             $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
    //             $row++;

    //             if (isset($details[$key]) && count($details[$key]) > 0) {
    //                 foreach ($details[$key] as $idx => $det) {
    //                     $sub_idr += $det->value_idr; $sub_usd += $det->value_usd;
    //                     $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) : 0;

    //                     $sheet->setCellValue("A$row", $idx + 1)->setCellValue("B$row", $det->nama_item)->setCellValue("C$row", $det->allowance)
    //                           ->setCellValue("D$row", $det->value_idr)->setCellValue("E$row", $det->value_usd)->setCellValue("F$row", $persen);
    //                     $sheet->getStyle("A$row:F$row")->applyFromArray($styleBorder);
    //                     $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
    //                     $row++;
    //                 }
    //             }
    //             $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) : 0;
    //             $sheet->mergeCells("A$row:C$row"); $sheet->setCellValue("A$row", "TOTAL OTHER COST :")->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    //             $sheet->setCellValue("D$row", $sub_idr)->setCellValue("E$row", $sub_usd)->setCellValue("F$row", $sub_persen);
    //             $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
    //             $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
    //             $row += 2;
    //         }
    //     }

    //     $actual_vat = strtolower($costing->shipment_type) == 'export' ? 0 : $costing->vat;
    //     $vat_multiplier = 1 + ($actual_vat / 100);
    //     $rate_from_idr = $costing->rate_from_idr > 0 ? $costing->rate_from_idr : 15000;

    //     $ga_idr = $grand_idr * 0.03; // 3%
    //     $ga_usd = ($grand_idr / $rate_from_idr) * 0.03;
    //     $grand_usd = $grand_idr / $rate_from_idr;
    //     $vat_idr = $grand_idr * $vat_multiplier;
    //     $vat_usd = $grand_usd * $vat_multiplier;
    //     $profit_idr = $vat_idr * 1.06;
    //     $profit_usd = $vat_usd * 1.06;
    //     $ga_pct = $grand_idr > 0 ? ($ga_idr / $grand_idr) : 0;

    //     $start_footer_row = $row;

    //     $sheet->setCellValue("K$row", "VALUE IDR")->setCellValue("L$row", "VALUE USD")->setCellValue("M$row", "%")->getStyle("K$row:M$row")->applyFromArray($styleHead); $row++;

    //     $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "G&A (3%)")->getStyle("I$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //     $sheet->setCellValue("K$row", $ga_idr)->setCellValue("L$row", $ga_usd)->setCellValue("M$row", $ga_pct);
    //     $sheet->getStyle("K$row:M$row")->applyFromArray($styleBorder);
    //     $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
    //     $row++;

    //     $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "TOTAL COST")->getStyle("I$row")->applyFromArray($styleBoldCenter)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //     $sheet->setCellValue("K$row", $grand_idr)->setCellValue("L$row", $grand_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder)->getFont()->setBold(true)->getColor()->setARGB('FFFF0000'); // Warna Merah
    //     $row++;

    //     $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "VAT ($actual_vat%)")->getStyle("I$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //     $sheet->setCellValue("K$row", $vat_idr)->setCellValue("L$row", $vat_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder);
    //     $row++;

    //     $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "PROFIT (6%)")->getStyle("I$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    //     $sheet->setCellValue("K$row", $profit_idr)->setCellValue("L$row", $profit_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder);

    //     $sig_row = $start_footer_row + 1;

    //     $created_name = $costing->created_by ?? '()';
    //     $created_time = $costing->created_at ? date('d/m/Y H:i', strtotime($costing->created_at)) : '()';

    //     $approved_name = '';
    //     $approved_time = '';

    //     $sheet->setCellValue("A$sig_row", "Created by");
    //     $sheet->setCellValue("B$sig_row", ": " . $created_name ." (". $created_time . ")");
    //     $sig_row++;

    //     $sheet->setCellValue("A$sig_row", "Approved by");
    //     $sheet->setCellValue("B$sig_row", ": " . $approved_name ." (". $approved_time . ")");

    //     foreach (range('A', 'O') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

    //     $writer = new Xlsx($spreadsheet);
    //     $filename = "Costing_{$costing->no_costing}.xlsx";

    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header("Content-Disposition: attachment; filename=\"$filename\"");
    //     header('Cache-Control: max-age=0');

    //     $writer->save('php://output');
    //     exit;
    // }

    public function printExcel($id)
    {
        $db = DB::connection('mysql_sb');

        $costing = $db->table('act_costing as a')
            ->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
            ->leftJoin('mastershipmode as sm', 'a.id_smode', '=', 'sm.id')
            ->leftJoin('masterseason as seas', 'a.season_id', '=', 'seas.id_season')
            ->leftJoin('masterpilihan as mp', function ($join) {
                $join->on('a.curr', '=', 'mp.nama_pilihan')
                    ->where('mp.kode_pilihan', '=', 'Curr');
            })
            ->select('a.*', 'b.Supplier as nama_buyer', 'p.product_item as nama_product_item', 'p.product_group', 'sm.shipmode as nama_ship_mode', 'seas.season as nama_season', 'mp.nama_pilihan as nama_curr')
            ->where('a.id', $id)->first();

        if (!$costing) return redirect()->back()->with('error', 'Data Costing tidak ditemukan.');

        $this->decorateLegacyCosting($costing);
        $nama_destinasi = '-';
        if (!empty($costing->main_dest)) {
            $arr_id_dest = array_map('trim', explode(',', $costing->main_dest));
            $destinations = $db->table('master_destination')->whereIn('id', $arr_id_dest)->pluck('country_name')->toArray();
            if (count($destinations) > 0) $nama_destinasi = implode(', ', $destinations);
        }
        $costing->nama_dest = $nama_destinasi;

        $details = $this->getCostingDetail($id, $costing->rate);

        $sum_fab_idr = 0; $sum_sew_idr = 0; $sum_pack_idr = 0; $sum_mfg_idr = 0; $sum_oth_norm_idr = 0;
        $sum_fab_usd = 0; $sum_sew_usd = 0; $sum_pack_usd = 0; $sum_mfg_usd = 0; $sum_oth_norm_usd = 0;
        $overhead_row = null;

        $rate_to_idr = $costing->rate_to_idr > 0 ? $costing->rate_to_idr : 0;

        $saved_sets = $costing->product_set ? explode(',', $costing->product_set) : [];
        $saved_sets = array_map('trim', $saved_sets);
        $master_set = $db->table('master_set')->get();

        $active_sets = [];
        foreach ($master_set as $m_set) {
            if (in_array($m_set->id, $saved_sets)) {
                $active_sets[] = strtoupper($m_set->nama ?? $m_set->id);
            }
        }
        sort($active_sets);

        $cat_set_totals = [];
        $grand_set_totals = ['bom' => 0, 'val' => 0, 'sets' => []];
        foreach(['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $k) {
            foreach($active_sets as $s) {
                $cat_set_totals[$k][$s] = ['idr' => 0, 'usd' => 0, 'bom' => 0, 'val' => 0];
            }
        }
        foreach($active_sets as $s) {
            $grand_set_totals['sets'][$s] = ['bom' => 0, 'val' => 0];
        }

        // HITUNG MATERIAL (Akumulasi sesuai SET)
        foreach(['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $key) {
            if (isset($details[$key])) {
                foreach($details[$key] as $det) {

                    // Deteksi nama set
                    $set_name = strtoupper(trim($det->nama_set ?? $det->set ?? ''));

                    $allow = $det->allowance > 0 ? $det->allowance : 0;
                    $qty_bom = ceil((1 + ($allow / 100)) * $costing->qty * $det->cons);
                    $tot_val = $qty_bom * $det->price_px_idr;

                    // Masuk ke Total Kategori
                    if ($key == 'Fabric') {
                        $sum_fab_idr += $det->value_idr; $sum_fab_usd += $det->value_usd;
                    } elseif ($key == 'Accessories Sewing') {
                        $sum_sew_idr += $det->value_idr; $sum_sew_usd += $det->value_usd;
                    } elseif ($key == 'Accessories Packing') {
                        $sum_pack_idr += $det->value_idr; $sum_pack_usd += $det->value_usd;
                    } elseif ($key == 'Manufacturing') {
                        $sum_mfg_idr += $det->value_idr; $sum_mfg_usd += $det->value_usd;
                    }

                    // Masuk ke Total Spesifik SET (Inner, Top, dll)
                    if ($set_name && in_array($set_name, $active_sets)) {
                        $cat_set_totals[$key][$set_name]['idr'] += $det->value_idr;
                        $cat_set_totals[$key][$set_name]['usd'] += $det->value_usd;
                        $cat_set_totals[$key][$set_name]['bom'] += $qty_bom;
                        $cat_set_totals[$key][$set_name]['val'] += $tot_val;

                        $grand_set_totals['sets'][$set_name]['bom'] += $qty_bom;
                        $grand_set_totals['sets'][$set_name]['val'] += $tot_val;
                    }

                    $grand_set_totals['bom'] += $qty_bom;
                    $grand_set_totals['val'] += $tot_val;
                }
            }
        }

        $base_material_idr = $sum_fab_idr + $sum_sew_idr + $sum_pack_idr;
        $base_material_usd = $sum_fab_usd + $sum_sew_usd + $sum_pack_usd;

        // HITUNG ULANG OTHER COST (USD = IDR / Rate)
        if (isset($details['Other Cost'])) {
            foreach($details['Other Cost'] as $det) {
                if (str_contains(strtoupper($det->nama_item), 'OVERHEAD')) {
                    $overhead_row = $det;
                } else {
                    $det->value_usd = $det->value_idr / $rate_to_idr;
                    $sum_oth_norm_idr += $det->value_idr;
                    $sum_oth_norm_usd += $det->value_usd;
                }
            }
        }

        $base_overhead_idr = $base_material_idr;
        $base_overhead_usd = $base_material_usd;

        $overhead_idr = 0; $overhead_usd = 0;
        if ($overhead_row) {
            $oh_allow = $overhead_row->allowance > 0 ? $overhead_row->allowance : 6;
            $overhead_idr = $base_overhead_idr * ($oh_allow / 100);
            $overhead_usd = $base_overhead_usd * ($oh_allow / 100);

            $overhead_row->value_idr = $overhead_idr;
            $overhead_row->value_usd = $overhead_usd;
        }

        $tot_other_idr = $sum_oth_norm_idr + $overhead_idr;
        $tot_other_usd = $sum_oth_norm_usd + $overhead_usd;

        $base_ga_idr = $base_material_idr + $sum_mfg_idr + $tot_other_idr;
        $base_ga_usd = $base_material_usd + $sum_mfg_usd + $tot_other_usd;

        $input_ga_pct = isset($costing->ga_percent) ? floatval($costing->ga_percent) : 3;
        $ga_idr = $base_ga_idr * ($input_ga_pct / 100);
        $ga_usd = $base_ga_usd * ($input_ga_pct / 100);

        $grand_idr = $base_ga_idr + $ga_idr;
        $grand_usd = $grand_idr / $rate_to_idr;

        $pembagi_persen = $grand_idr;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Costing');

        $styleBoldCenter = ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $styleBorder = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $styleHead = array_merge($styleBorder, $styleBoldCenter, ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']]]);
        $styleDark = array_merge($styleBorder, ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF343A40']]]);

        if (file_exists(public_path('assets/dist/img/nag-logo.png'))) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setPath(public_path('assets/dist/img/nag-logo.png'));
            $drawing->setHeight(50);
            $drawing->setCoordinates('A1');
            $drawing->setWorksheet($sheet);
        }

        $sheet->mergeCells('C1:J1'); $sheet->setCellValue('C1', 'PT NIRWANA ALABARE GARMENT')->getStyle('C1')->applyFromArray($styleBoldCenter);
        $sheet->mergeCells('C2:J2'); $sheet->setCellValue('C2', 'COSTING')->getStyle('C2')->applyFromArray($styleBoldCenter);

        $sheet->setCellValue('A4', 'No Costing')->setCellValue('B4', ': ' . $costing->no_costing);
        $sheet->setCellValue('D4', 'Style')->setCellValue('E4', ': ' . $costing->style);
        $sheet->setCellValue('G4', 'Ship Mode')->setCellValue('H4', ': ' . ($costing->nama_ship_mode ?? $costing->ship_mode));

        $sheet->setCellValue('A5', 'Buyer')->setCellValue('B5', ': ' . $costing->nama_buyer);
        $sheet->setCellValue('D5', 'SMV')->setCellValue('E5', ': ' . $costing->smv);
        $sheet->setCellValue('G5', 'Ship Type')->setCellValue('H5', ': ' . strtoupper($costing->shipment_type));

        $sheet->setCellValue('A6', 'Brand')->setCellValue('B6', ': ' . $costing->brand);
        $sheet->setCellValue('D6', 'Qty (PCS)')->setCellValue('E6', ': ' . $costing->qty);
        $sheet->setCellValue('G6', 'Rate to IDR')->setCellValue('H6', ': ' . $costing->rate_to_idr);

        $set_string = '-';
        if (strtolower($costing->type) === 'multiple' && count($active_sets) > 0) {
            $set_string = implode(', ', $active_sets);
        }

        $sheet->setCellValue('A7', 'Season')->setCellValue('B7', ': ' . ($costing->nama_season ?? '-'));
        $sheet->setCellValue('D7', 'Type')->setCellValue('E7', ': ' . strtoupper($costing->type));
        $sheet->setCellValue('G7', 'Rate from IDR')->setCellValue('H7', ': ' . $costing->rate_from_idr);

        $sheet->setCellValue('A8', 'Product Group')->setCellValue('B8', ': ' . $costing->product_group);
        $sheet->setCellValue('D8', 'Set')->setCellValue('E8', ': ' . $set_string);
        $sheet->setCellValue('G8', 'VAT')->setCellValue('H8', ': ' . $costing->vat . ' %');

        $sheet->setCellValue('A9', 'Product Item')->setCellValue('B9', ': ' . ($costing->nama_product_item ?? $costing->product_item));

        if (!empty($costing->foto) && file_exists(public_path('uploads/costing/' . $costing->foto))) {
            $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing2->setName('Costing Photo');
            $drawing2->setPath(public_path('uploads/costing/' . $costing->foto));
            $drawing2->setHeight(100);
            $drawing2->setCoordinates('K3');
            $drawing2->setWorksheet($sheet);
        }

        $row = 11;

        $categories_list = [
            'Fabric' => 'FABRIC', 'Accessories Sewing' => 'ACCESSORIES SEWING',
            'Accessories Packing' => 'ACCESSORIES PACKING', 'Manufacturing' => 'MANUFACTURING', 'Other Cost' => 'OTHER COST'
        ];

        $rate_to_idr = $costing->rate_to_idr > 0 ? $costing->rate_to_idr : 1;
        $rate_from_idr = $costing->rate_from_idr > 0 ? $costing->rate_from_idr : 1;
        $jenis_rate = 'B';
        $curr_record = $db->table('masterpilihan')->where('id', $costing->curr)->first();
        if ($curr_record && strtoupper($curr_record->nama_pilihan) == 'USD') {
            $jenis_rate = 'J';
        }

        foreach ($categories_list as $key => $title) {
            $sub_idr = 0; $sub_usd = 0; $sum_val = 0;

            if ($key !== 'Other Cost') {
                $sheet->mergeCells("A$row:O$row"); $sheet->setCellValue("A$row", $title)->getStyle("A$row:O$row")->applyFromArray($styleDark);
                $row++;
                $headers = ['NO', 'ITEM', 'SET', 'DESC', 'SUPPLIER', 'PRICE IDR', 'PRICE USD', 'CONS/PC', 'UNIT', 'ALLOW %', 'VALUE IDR', 'VALUE USD', '%', 'QTY BOM', 'VALUE'];
                $col = 'A'; foreach ($headers as $h) { $sheet->setCellValue($col.$row, $h); $col++; }
                $sheet->getStyle("A$row:O$row")->applyFromArray($styleHead);
                $row++;

                if (isset($details[$key]) && count($details[$key]) > 0) {
                    foreach ($details[$key] as $idx => $det) {
                        $allow = $det->allowance > 0 ? $det->allowance : 0;

                        if ($jenis_rate == 'J') {
                            $det->price_px_idr = $det->price * $rate_to_idr;
                            $det->price_px_usd = $det->price;
                        } else {
                            $det->price_px_idr = $det->price;
                            $det->price_px_usd = $det->price / $rate_from_idr;
                        }

                        $allowcs_usd = ($det->price_px_usd * $det->cons) * ($allow / 100);
                        $allowcs_idr = ($det->price_px_idr * $det->cons) * ($allow / 100);

                        $det->value_usd = ($det->price_px_usd * $det->cons) + $allowcs_usd;
                        $det->value_idr = ($det->price_px_idr * $det->cons) + $allowcs_idr;

                        $sub_idr += $det->value_idr; $sub_usd += $det->value_usd;
                        $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) : 0;
                        $qty_bom = ceil((1 + ($allow / 100)) * $costing->qty * $det->cons);
                        $tot_val = $qty_bom * $det->price_px_idr;
                        $sum_val += $tot_val;

                        $sheet->setCellValue("A$row", $idx + 1)
                              ->setCellValue("B$row", $det->nama_item)
                              ->setCellValue("C$row", $det->nama_set ?? $det->set)
                              ->setCellValue("D$row", $det->item_desc)
                              ->setCellValue("E$row", $det->nama_supplier)
                              ->setCellValue("F$row", $det->price_px_idr)
                              ->setCellValue("G$row", $det->price_px_usd)
                              ->setCellValue("H$row", $det->cons)
                              ->setCellValue("I$row", $det->unit)
                              ->setCellValue("J$row", $det->allowance)
                              ->setCellValue("K$row", $det->value_idr)
                              ->setCellValue("L$row", $det->value_usd)
                              ->setCellValue("M$row", $persen)
                              ->setCellValue("N$row", $qty_bom)
                              ->setCellValue("O$row", $tot_val);

                        $sheet->getStyle("A$row:O$row")->applyFromArray($styleBorder);
                        $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                        $row++;
                    }
                }

                // LOOP CETAK SET DINAMIS
                foreach($active_sets as $s) {
                    if (isset($cat_set_totals[$key][$s])) {
                        $d = $cat_set_totals[$key][$s];
                        $sheet->mergeCells("A$row:J$row");
                        $sheet->setCellValue("A$row", "$title - TOTAL $s :")->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        $sheet->setCellValue("K$row", $d['idr'])->setCellValue("L$row", $d['usd'])->setCellValue("N$row", $d['bom'])->setCellValue("O$row", $d['val']);
                        $sheet->getStyle("A$row:O$row")->applyFromArray($styleBorder);
                        $sheet->getStyle("A$row:O$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA'); // Light gray
                        $row++;
                    }
                }

                $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) : 0;
                $sheet->mergeCells("A$row:J$row"); $sheet->setCellValue("A$row", "TOTAL $title :")->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue("K$row", $sub_idr)->setCellValue("L$row", $sub_usd)->setCellValue("M$row", $sub_persen)->setCellValue("O$row", $sum_val);
                $sheet->getStyle("A$row:O$row")->applyFromArray($styleHead);
                $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $row += 2;

            } else {

                $sheet->mergeCells("A$row:F$row"); $sheet->setCellValue("A$row", $title)->getStyle("A$row:F$row")->applyFromArray($styleDark);
                $row++;
                $headers = ['NO', 'ITEM', 'ALLOW %', 'VALUE IDR', 'VALUE USD', '%'];
                $col = 'A'; foreach ($headers as $h) { $sheet->setCellValue($col.$row, $h); $col++; }
                $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
                $row++;

                if (isset($details[$key]) && count($details[$key]) > 0) {
                    foreach ($details[$key] as $idx => $det) {
                        if (!str_contains(strtoupper($det->nama_item), 'OVERHEAD')) {
                            if ($jenis_rate == 'J') {
                                $det->price_px_idr = $det->price * $rate_to_idr;
                                $det->price_px_usd = $det->price;
                            } else {
                                $det->price_px_idr = $det->price;
                                $det->price_px_usd = $det->price / $rate_from_idr;
                            }
                            $det->value_idr = $det->price_px_idr;
                            $det->value_usd = $det->price_px_usd;
                        }

                        $sub_idr += $det->value_idr; $sub_usd += $det->value_usd;
                        $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) : 0;
                        $allow_other = $det->allowance > 0 ? $det->allowance : (str_contains(strtoupper($det->nama_item), 'OVERHEAD') ? 6 : 0);

                        $sheet->setCellValue("A$row", $idx + 1)
                            ->setCellValue("B$row", $det->nama_item)
                            ->setCellValue("C$row", $allow_other)
                            ->setCellValue("D$row", $det->value_idr)
                            ->setCellValue("E$row", $det->value_usd)
                            ->setCellValue("F$row", $persen);

                        $sheet->getStyle("A$row:F$row")->applyFromArray($styleBorder);
                        $sheet->getStyle("D$row:E$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
                        $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                        $row++;
                    }
                }
                $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) : 0;
                $sheet->mergeCells("A$row:C$row"); $sheet->setCellValue("A$row", "TOTAL OTHER COST :")->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue("D$row", $sub_idr)->setCellValue("E$row", $sub_usd)->setCellValue("F$row", $sub_persen);
                $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
                $sheet->getStyle("D$row:E$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
                $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $row++;

                $ga_pct = $pembagi_persen > 0 ? ($ga_idr / $pembagi_persen) : 0;
                $sheet->mergeCells("A$row:C$row"); $sheet->setCellValue("A$row", "G&A (" . $input_ga_pct . "%)")->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue("D$row", $ga_idr)->setCellValue("E$row", $ga_usd)->setCellValue("F$row", $ga_pct);
                $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
                $sheet->getStyle("D$row:E$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
                $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $row += 2;
            }
        }

        // CETAK GRAND TOTAL BOM & VALUE TABEL
        $sheet->setCellValue("M$row", "TOTAL QTY BOM")->setCellValue("N$row", "TOTAL VALUE")->getStyle("M$row:N$row")->applyFromArray($styleHead); $row++;

        $sheet->mergeCells("K$row:L$row"); $sheet->setCellValue("K$row", "GRAND TOTAL")->getStyle("K$row")->applyFromArray($styleBoldCenter)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue("M$row", $grand_set_totals['bom'])->setCellValue("N$row", $grand_set_totals['val'])->getStyle("K$row:N$row")->applyFromArray($styleBorder)->getFont()->setBold(true);
        $row++;

        if(isset($grand_set_totals['sets'])) {
            foreach($active_sets as $s) {
                if(isset($grand_set_totals['sets'][$s])) {
                    $d = $grand_set_totals['sets'][$s];
                    $sheet->mergeCells("K$row:L$row"); $sheet->setCellValue("K$row", "TOTAL $s :")->getStyle("K$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $sheet->setCellValue("M$row", $d['bom'])->setCellValue("N$row", $d['val']);
                    $sheet->getStyle("K$row:N$row")->applyFromArray($styleBorder);
                    $sheet->getStyle("K$row:N$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
                    $row++;
                }
            }
        }
        $row++;

        // CETAK GRAND TOTAL COST, PAJAK, PROFIT
        $actual_vat = strtolower($costing->shipment_type) == 'export' ? 0 : $costing->vat;
        $vat_multiplier = 1 + ($actual_vat / 100);

        $vat_idr = round($grand_idr * $vat_multiplier, 2);
        $vat_usd = round($grand_usd * $vat_multiplier, 7);
        $profit_idr = round($vat_idr * 1.06, 2);
        $profit_usd = round($vat_usd * 1.06, 7);
        $ga_pct = $grand_idr > 0 ? ($ga_idr / $grand_idr) : 0;

        $start_footer_row = $row;

        $sheet->setCellValue("K$row", "VALUE IDR")->setCellValue("L$row", "VALUE USD")->getStyle("K$row:L$row")->applyFromArray($styleHead); $row++;

        // $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "G&A (3%)")->getStyle("I$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        // $sheet->setCellValue("K$row", $ga_idr)->setCellValue("L$row", $ga_usd)->setCellValue("M$row", $ga_pct);
        // $sheet->getStyle("K$row:M$row")->applyFromArray($styleBorder);
        // $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
        // $row++;

        $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "TOTAL COST")->getStyle("I$row")->applyFromArray($styleBoldCenter)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("K$row", $grand_idr)->setCellValue("L$row", $grand_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder)->getFont()->setBold(true);
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
        $row++;

        $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "VAT ($actual_vat%)")->getStyle("I$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("K$row", $vat_idr)->setCellValue("L$row", $vat_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder);
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
        $row++;

        $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "PROFIT (6%)")->getStyle("I$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("K$row", $profit_idr)->setCellValue("L$row", $profit_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder);
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode('#,##0.0000000');
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode('#,##0.0000000');

        $sig_row = $start_footer_row + 1;

        $created_name = $costing->created_by ?? '()';
        $created_time = $costing->created_at ? date('d/m/Y H:i', strtotime($costing->created_at)) : '()';

        $sheet->setCellValue("A$sig_row", "Created by");
        $sheet->setCellValue("B$sig_row", ": " . $created_name ." (". $created_time . ")");
        $sig_row++;

        $sheet->setCellValue("A$sig_row", "Approved by");
        $sheet->setCellValue("B$sig_row", ": ()");

        foreach (range('A', 'O') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = "Costing_{$costing->no_costing}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function approval(Request $request)
    {
        if ($request->ajax()) {
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;
            $db = DB::connection('mysql_sb');

            $data = $db->table('act_costing as a')
                ->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
                ->leftJoin('masterproduct as p', 'a.id_product', '=', 'p.id')
                ->select(
                    'a.id',
                    'a.cost_no as no_costing',
                    'a.dateinput as created_at',
                    'b.Supplier as nama_buyer',
                    'a.brand',
                    'a.styleno as style',
                    'a.mkt_order as marketing_order',
                    'p.product_group',
                    'p.product_item',
                    'a.main_dest',
                    'a.market'
                )
                ->where(function($query) {
                    $query->whereNull('a.app1')
                          ->orWhere('a.app1', '!=', 'Y');
                })
                ->orderBy('a.id', 'desc');

            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $data->whereDate('a.dateinput', '>=', $tgl_awal)
                     ->whereDate('a.dateinput', '<=', $tgl_akhir);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tgl_costing', function ($row) {
                    return $row->created_at ? date('d-m-Y', strtotime($row->created_at)) : '-';
                })
                ->make(true);
        }


        return view('marketing.costing.approval', [
            'containerFluid'       => true,
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing-approval",
        ]);
    }

    public function submitApproval(Request $request, $id)
    {
        try {
            $db = DB::connection('mysql_sb');
            $costing = $db
                ->table('act_costing')
                ->where('id', $id)
                ->first(['cost_no']);
            if (!$costing) {
                return response()->json(['status' => 404, 'message' => 'Data Costing tidak ditemukan.'], 404);
            }

            $db->transaction(function () use ($db, $id, $costing) {
                $db->table('act_costing')
                    ->where('id', $id)
                    ->update([
                        'app1' => 'Y',
                        'app1_date' => now(),
                        'app1_by' => Auth::user()->name
                    ]);
                app(MarketingHistoryService::class)->approved('costing', (int) $id, (int) $id, (string) $costing->cost_no);
            });

            return response()->json(['status' => 200, 'message' => 'Costing berhasil di Approve.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
