<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;
use App\Services\MarketingBomService;
use App\Services\MarketingHistoryService;
use App\Services\MarketingApprovalService;

class Marketing_SOController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $mysql_sb = DB::connection('mysql_sb');

            $query = $mysql_sb->table('so')
                ->leftJoin('act_costing as act', 'so.id_cost', '=', 'act.id')
                ->leftJoin('mastersupplier as ms', 'act.id_buyer', '=', 'ms.Id_Supplier')
                ->leftJoin('masterproduct as mp', 'so.id_product', '=', 'mp.id')
                ->select([
                    'so.id',
                    'so.d_insert',
                    'so.so_no',
                    'so.no_po',
                    'so.market',
                    'so.qty',
                    'act.kpno',
                    'so.style',
                    'ms.Supplier as buyer',
                    'mp.product_group',
                    'mp.product_item'
                ])
                ->orderBy('so.id', 'desc');

            if ($request->date_from && $request->date_to) {
                $query->whereBetween(DB::raw('DATE(so.d_insert)'), [$request->date_from, $request->date_to]);
            }
            $query->where('cancel_h', 'N');


            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('d_insert', function($row) {
                    return date('Y-m-d', strtotime($row->d_insert));
                })
                ->editColumn('qty', function($row) {
                    return number_format($row->qty, 0, ',', '.');
                })
                ->addColumn('action', function($row) {
                    $host = request()->getHost();

                    if ($host == 'localhost' || $host == '127.0.0.1') {
                        $base_url = 'http://localhost:8080';
                    } else {
                        $base_url = 'http://' . $host . ':8080';
                    }

                    $url_pdf = $base_url . '/erp/pages/marketting/pdfSO.php?id=' . $row->id;

                    $url_pdf_so = route('print-pdf-so', $row->id);

                    // return '
                    //     <button class="btn btn-info btn-sm" onclick="showDetail('.$row->id.')" title="Detail SO">
                    //         <i class="fas fa-eye"></i>
                    //     </button>
                    //     <button class="btn btn-warning btn-sm" onclick="showDetailMaterial('.$row->id.')" title="Detail Material">
                    //         <i class="fas fa-boxes"></i>
                    //     </button>
                    //     <button class="btn btn-primary btn-sm" onclick="syncBom('.$row->id.')" title="Sync BOM">
                    //         <i class="fas fa-sync-alt"></i>
                    //     </button>
                    //     <button class="btn btn-secondary btn-sm" onclick="openMergeModal('.$row->id.')" title="Merge SO">
                    //         <i class="fas fa-code-branch"></i>
                    //     </button>
                    //     <a href="'.route('so-input-barcode', $row->id).'" class="btn btn-dark btn-sm" title="Input Barcode">
                    //         <i class="fas fa-barcode"></i>
                    //     </a>
                    //     <a href="'.$url_pdf.'" target="_blank" class="btn btn-danger btn-sm" title="Cetak PDF SO">
                    //         <i class="fas fa-file-pdf"></i>
                    //     </a>
                    //     <a href="'.$url_pdf_so.'" target="_blank" class="btn btn-success btn-sm" title="Cetak PDF SO Market">
                    //         <i class="fas fa-file-pdf"></i>
                    //     </a>
                    // ';

                    return '
                        <button class="btn btn-info btn-sm" onclick="showDetail('.$row->id.')" title="Detail SO">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="showDetailMaterial('.$row->id.')" title="Detail Material">
                            <i class="fas fa-boxes"></i>
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="syncBom('.$row->id.')" title="Sync BOM">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <a href="'.$url_pdf.'" target="_blank" class="btn btn-danger btn-sm" title="Cetak PDF SO">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href ="'.$url_pdf_so.'" target="_blank" class="btn btn-success btn-sm" title="Cetak PDF SO Market">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('marketing.so.index', [
            'page'         => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage'      => 'marketing-master-so',
            'containerFluid' => true
        ]);
    }

    public function create(Request $request)
    {
        return $this->soPage($request->id_costing);
    }

    /**
     * Detail SO: halaman Create SO dalam mode baca-saja.
     */
    public function show($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $so = $mysql_sb->table('so')->where('id', $id)->first();
        abort_if(!$so, 404);

        // Destination dari so_destination; so_det lama tanpa so_destination tetap tampil dengan so_det.dest.
        $details = $mysql_sb->table('so_det as sd')
            ->leftJoin('so_destination as d', fn ($join) => $join->on('d.id_so_det', '=', 'sd.id')->where('d.cancel', 'N'))
            ->leftJoin('master_size_new as s', 'sd.id_size', '=', 's.id')
            ->where('sd.id_so', $id)
            ->where(fn ($query) => $query->whereNull('sd.cancel')->orWhere('sd.cancel', '!=', 'Y'))
            ->whereRaw('(d.id IS NOT NULL OR NOT EXISTS (SELECT 1 FROM so_destination x WHERE x.id_so_det = sd.id))')
            ->select('sd.market', DB::raw("COALESCE(d.destination, NULLIF(sd.dest, '-')) as dest"), 'sd.color', 'sd.size', DB::raw('COALESCE(d.qty, sd.qty) as qty'), 'sd.deldate_det', 'sd.notes', 's.urutan')
            ->orderBy('sd.color')
            ->orderByRaw('COALESCE(s.urutan, 9999)')
            ->get();

        // Satu baris per warna x ukuran, seperti tabel Summary Detail di Costing.
        $rows = $details->map(fn ($d) => [
            'style' => $so->style,
            'description' => $d->notes,
            'po' => $so->no_po,
            'market' => $d->market,
            'destination' => $d->dest,
            'ex_fty' => $d->deldate_det ? date('d/m/Y', strtotime($d->deldate_det)) : null,
            'color' => $d->color,
            'size' => $d->size,
            'qty' => (float) $d->qty,
        ])->values()->all();

        $currency = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Curr')
            ->where(fn ($q) => $q->where('nama_pilihan', $so->curr)->orWhere('id', $so->curr))->value('id');
        $poCount = $mysql_sb->table('so')->where('id_cost', $so->id_cost)->where('cancel_h', '!=', 'Y')->count();

        return $this->soPage($so->id_cost, [
            'viewOnly' => true,
            'so' => $so,
            'soUnlock' => app(MarketingApprovalService::class)->soUnlockState((int) $so->id),
            'soBuyer' => $mysql_sb->table('act_costing as a')
                ->leftJoin('mastersupplier as b', 'a.id_buyer', '=', 'b.Id_Supplier')
                ->where('a.id', $so->id_cost)->value('b.Supplier'),
            'soView' => [
                'id_currency' => $currency,
                'jns_so' => $so->jns_so,
                'fob' => $so->fob,
                'notes' => $so->notes,
                'total_qty' => $details->sum('qty'),
                'jumlah_po' => $poCount,
                'po_type' => $poCount > 1 ? 'multiple' : 'single',
                'ex_fty' => $details->min('deldate_det'),
            ],
            'soRows' => $rows,
        ]);
    }

    private function soPage($id_costing, array $extra = [])
    {
        $mysql_sb = DB::connection('mysql_sb');

        $costingNo  = '-';
        if ($id_costing) {
            $costing = $mysql_sb->table('act_costing')->where('id', $id_costing)->first();
            if ($costing) {
                $costingNo = $costing->cost_no ?? '-';
            }
        }



        $buyers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'C')
            ->select('Id_Supplier', 'Supplier')->orderBy('Supplier', 'ASC')->get();

        $product_groups = $mysql_sb->table('masterproduct')
            ->select('product_group')
            ->distinct()
            ->whereNotNull('product_group')
            ->orderBy('product_group', 'asc')
            ->get();

        $currency = $mysql_sb->table('masterpilihan')
            ->where('kode_pilihan', 'Curr')
            ->select('id', 'nama_pilihan')
            ->get();
        $used_boms = $mysql_sb->table('so')->whereNotNull('id_bom')->pluck('id_bom')->toArray();

        $bom_catalog = $mysql_sb->table('bom_marketing')
            ->select('id', 'no_katalog_bom', 'style')
            ->whereNotNull('no_katalog_bom')
            ->where('approval', 'Y');

        if (!empty($used_boms)) {
            $bom_catalog = $bom_catalog->whereNotIn('id', $used_boms);
        }

        $bom_catalog = $bom_catalog->orderBy('created_at', 'desc')->get();

        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();
        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();
        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();

        $marketing_orders = $mysql_sb->table('master_mkt_order')
                               ->orderBy('mkt_order', 'asc')
                               ->get();

        $master_set = $mysql_sb->table('master_set')->orderBy('urutan', 'ASC')->get();


        return view('marketing.so.create', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-so',
            'buyers'         => $buyers,
            'currency'       => $currency,
            'product_groups' => $product_groups,
            'bom_catalog'    => $bom_catalog,
            'master_colors'    => $master_colors,
            'master_sizes'    => $master_sizes,
            'masterUnits'    => $masterUnits,
            'suppliers' => $suppliers,
            'marketing_orders' => $marketing_orders,
            'master_set'     => $master_set,
            'costingNo'      => $costingNo,
            'costingId'      => $id_costing,
            'containerFluid' => true
        ] + $extra);
    }

    public function getBomMasterData(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $bom = $mysql_sb->table('bom_marketing')->where('id', $request->id_bom)->first();

        $c_ids = $bom && $bom->colors ? json_decode($bom->colors) : [];
        $s_ids = $bom && $bom->sizes ? json_decode($bom->sizes) : [];

        $colors = $mysql_sb->table('master_colors_gmt')->whereIn('id', $c_ids)->select('id', 'name as text')->get();
        $sizes = $mysql_sb->table('master_size_new')->whereIn('id', $s_ids)->select('id', 'size as text')->orderBy('urutan', 'asc')->get();

        $existing = $mysql_sb->table('bom_marketing_detail')
            ->where('id_bom_marketing', $request->id_bom)
            ->where('cancel', 'N')
            ->select('id_contents', 'id_color', 'id_size')
            ->get();

        return response()->json([
            'colors' => $colors,
            'sizes' => $sizes,
            'existing' => $existing
        ]);
    }

    public function getCostingData(Request $request)
    {
        $idCosting = $request->input('id_costing');
        if (!is_numeric($idCosting) || (int) $idCosting < 1) {
            return response()->json(['status' => 400, 'message' => 'ID Costing kosong'], 400);
        }
        $idCosting = (int) $idCosting;

        $db = DB::connection('mysql_sb');
        $data = $db->table('act_costing as c')
            ->leftJoin('mastersupplier as ms', 'c.id_buyer', '=', 'ms.Id_Supplier')
            ->leftJoin('masterseason as seas', 'c.season_id', '=', 'seas.id_season')
            ->leftJoin('mastershipmode as sm', 'c.id_smode', '=', 'sm.id')
            ->leftJoin('masterpilihan as curr', function ($join) {
                $join->on('curr.nama_pilihan', '=', 'c.curr')
                    ->where('curr.kode_pilihan', '=', 'Curr');
            })
            ->leftJoin('master_mkt_order as mo', 'mo.mkt_order', '=', 'c.mkt_order')
            ->leftJoin('masterproduct as mp', 'c.id_product', '=', 'mp.id')
            ->where('c.id', $idCosting)
            ->select(
                'c.id',
                'c.cost_no',
                'c.kpno',
                'c.id_buyer',
                'ms.Supplier as buyer_name',
                'ms.supplier_code as buyer_code',
                'c.season_id',
                'seas.season as season_name',
                'seas.season_desc',
                'c.styleno as style',
                'c.brand',
                'c.market',
                'mo.id as marketing_order',
                'mo.mkt_order as marketing_order_name',
                'c.smv_min as smv',
                'curr.id as id_currency',
                'c.costing_type as type',
                'c.type_ws as tipe_ws',
                'c.qty_planned as planned_qty',
                'c.deldate as delivery_date',
                'c.unit',
                'c.id_smode as ship_mode',
                'sm.shipmode as ship_mode_name',
                'c.status',
                'c.shipment_type',
                'c.notes',
                'c.main_dest',
                'mp.product_group',
                'mp.product_item as nama_product_item',
                'mp.id as id_product_item',
                'c.product_set',
                'c.attach_file as gambar',
                'c.cfm_price as confirm_price',
                'c.app1'
            )
            ->first();

        if (!$data) {
            return response()->json(['status' => 404, 'message' => 'Data Costing tidak ditemukan'], 404);
        }

        $pictures = $db->table('act_costing_picture')
            ->where('id_act_cost', $idCosting)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('file_name')
            ->filter()
            ->unique()
            ->values();

        if ($pictures->isEmpty() && $data->gambar) {
            $pictures->push($data->gambar);
        }

        $data->images = $pictures->map(function ($imageName) {
            return '/nds_wip/public/uploads/costing/' . rawurlencode($imageName);
        })->values();
        $data->image_url = $data->images->first();
        $data->kpno = $data->kpno ?: '-';
        $data->approved = in_array(strtoupper((string) $data->app1), ['A', 'Y'], true);


        return response()->json(['status' => 200, 'data' => $data]);
    }

    public function getProductItems(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $items = $mysql_sb->table('masterproduct')
            ->where('product_group', $request->product_group)
            ->orderBy('product_item', 'asc')
            ->get();

        return response()->json($items);
    }

    // public function uploadExcelSO(Request $request)
    // {
    //     $request->validate([
    //         'file_so' => 'required|mimes:xls,xlsx',
    //         'id_bom'  => 'required'
    //     ], [
    //         'id_bom.required' => 'Pilih No Katalog BOM terlebih dahulu sebelum mengupload Excel.'
    //     ]);

    //     $user_id = auth()->id();
    //     $file = $request->file('file_so');
    //     $id_bom = $request->id_bom;

    //     $spreadsheet = \PhpOffice\\IOFactory::load($file->getPathname());
    //     $data = $spreadsheet->getActiveSheet()->toArray();
    //     $headers = $data[0];

    //     $temp_data = [];
    //     $errors_color = [];
    //     $errors_size = [];
    //     $errors_bom = [];
    //     $list_po = [];

    //     $mysql_sb = DB::connection('mysql_sb');
    //     $master_colors = $mysql_sb->table('master_colors_gmt')->pluck('id', 'name')->toArray();
    //     $master_sizes = $mysql_sb->table('master_size_new')->pluck('id', 'size')->toArray();

    //     // Reverse array untuk mendapatkan nama dari ID saat bikin pesan error
    //     $master_colors_reverse = array_flip($master_colors);
    //     $master_sizes_reverse = array_flip($master_sizes);

    //     // ==========================================
    //     // 1. BACA EXCEL & REKAM KOMBINASI SO
    //     // ==========================================
    //     $so_combinations = []; // Menyimpan kombinasi aktual (Warna & Size) dari Excel

    //     for ($i = 1; $i < count($data); $i++) {
    //         $row = $data[$i];
    //         if (empty($row[0])) continue;

    //         $style = trim($row[0]);
    //         $po    = trim($row[2]);
    //         $color_name = trim($row[5]);
    //         $list_po[] = $po;

    //         if (!isset($master_colors[$color_name])) {
    //             $errors_color[] = "PO: <b>$po</b> - Warna: <b>$color_name</b> tidak ditemukan di Master Color.";
    //             continue;
    //         }

    //         $id_color = $master_colors[$color_name];

    //         for ($col_index = 6; $col_index < count($row); $col_index++) {
    //             $size_name = trim($headers[$col_index]);
    //             if (empty($size_name)) continue;

    //             if (!isset($master_sizes[$size_name])) {
    //                 $errors_size[] = "PO: <b>$po</b> - Size: <b>$size_name</b> tidak ditemukan di Master Size.";
    //                 continue;
    //             }

    //             $qty = $row[$col_index];
    //             if (!empty($qty) && is_numeric($qty) && $qty > 0) {
    //                 $id_size = $master_sizes[$size_name];

    //                 // Rekam kombinasi warna & size dari Excel
    //                 $combo_key = $id_color . '_' . $id_size;
    //                 if (!isset($so_combinations[$combo_key])) {
    //                     $so_combinations[$combo_key] = [
    //                         'id_color'   => $id_color,
    //                         'id_size'    => $id_size,
    //                         'color_name' => $color_name,
    //                         'size_name'  => $size_name
    //                     ];
    //                 }

    //                 $temp_data[] = [
    //                     'user_id'    => $user_id,
    //                     'style'      => $style,
    //                     'desc'       => trim($row[1]),
    //                     'po'         => $po,
    //                     'market'     => trim($row[3]),
    //                     'ex_fty'     => trim($row[4]),
    //                     'id_color'   => $id_color,
    //                     'size'       => $id_size,
    //                     'qty'        => $qty,
    //                     'created_at' => now()
    //                 ];
    //             }
    //         }
    //     }

    //     // Kalau master data warna/size salah ketik, stop disini
    //     if (count($errors_color) > 0) return response()->json(['status' => 422, 'message' => 'Kesalahan warna', 'errors' => array_unique($errors_color)], 422);
    //     if (count($errors_size) > 0) return response()->json(['status' => 422, 'message' => 'Kesalahan size', 'errors' => array_unique($errors_size)], 422);


    //     $bom_details = $mysql_sb->table('bom_marketing_detail')
    //         ->where('id_bom_marketing', $id_bom)
    //         ->select('id_color', 'id_size', 'rule_bom')
    //         ->get();

    //     foreach ($so_combinations as $combo) {
    //         $is_covered = false;

    //         foreach ($bom_details as $bom) {
    //             if ($bom->rule_bom == 'All Color All Size') {
    //                 $is_covered = true; break; // Bebas, semua lolos
    //             } elseif ($bom->rule_bom == 'Per Color All Size' && $bom->id_color == $combo['id_color']) {
    //                 $is_covered = true; break;
    //             } elseif ($bom->rule_bom == 'All Color Range Size' && $bom->id_size == $combo['id_size']) {
    //                 $is_covered = true; break;
    //             } elseif ($bom->id_color == $combo['id_color'] && $bom->id_size == $combo['id_size']) {
    //                 $is_covered = true; break; // Fix Match Per Color Per Size
    //             }
    //         }

    //         if (!$is_covered) {
    //             $errors_bom[] = "Warna <b>{$combo['color_name']}</b> Size <b>{$combo['size_name']}</b> di-upload, materialnya tidak disetting di Detail BOM.";
    //         }
    //     }

    //     foreach ($bom_details as $bom) {
    //         $is_used = false;

    //         if ($bom->rule_bom == 'All Color All Size') {
    //             $is_used = count($so_combinations) > 0; // Asal excel nggak kosong, rule ini terpenuhi
    //         } elseif ($bom->rule_bom == 'Per Color All Size') {
    //             foreach ($so_combinations as $combo) {
    //                 if ($combo['id_color'] == $bom->id_color) { $is_used = true; break; }
    //             }
    //         } elseif ($bom->rule_bom == 'All Color Range Size') {
    //             foreach ($so_combinations as $combo) {
    //                 if ($combo['id_size'] == $bom->id_size) { $is_used = true; break; }
    //             }
    //         } else {
    //             // Per Color Per Size
    //             foreach ($so_combinations as $combo) {
    //                 if ($combo['id_color'] == $bom->id_color && $combo['id_size'] == $bom->id_size) { $is_used = true; break; }
    //             }
    //         }

    //         // Jika ada rule/material di BOM yang nganggur (tidak di-upload)
    //         if (!$is_used) {
    //             $c_name = ($bom->id_color && $bom->id_color != '0') ? ($master_colors_reverse[$bom->id_color] ?? 'Unknown') : 'ALL COLOR';
    //             $s_name = ($bom->id_size && $bom->id_size != '0') ? ($master_sizes_reverse[$bom->id_size] ?? 'Unknown') : 'ALL SIZE';

    //             if ($bom->rule_bom == 'All Color All Size') {
    //                 $errors_bom[] = "Data Excel kosong, BOM mensyaratkan data (All Color All Size).";
    //                 break; // Jangan diulang-ulang errornya
    //             } else {
    //                 $errors_bom[] = "Detail BOM mensyaratkan Warna <b>{$c_name}</b> Size <b>{$s_name}</b>, tetapi tidak ditemukan pesanannya di Excel.";
    //             }
    //         }
    //     }

    //     // Return error jika validasi A atau B ada yang gagal
    //     if (count($errors_bom) > 0) {
    //         return response()->json([
    //             'status' => 422,
    //             'message' => 'BOM Tidak Sesuai',
    //             'errors' => array_unique($errors_bom) // Pakai array_unique agar error yang sama (karena material banyak) cuma muncul 1x
    //         ], 422);
    //     }

    //     $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();

    //     if (count($temp_data) > 0) {
    //         foreach (array_chunk($temp_data, 500) as $chunk) {
    //             $mysql_sb->table('temp_so_detail')->insert($chunk);
    //         }
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Excel berhasil diproses.'
    //     ]);
    // }

    // public function uploadExcelSO(Request $request)
    // {
    //     $request->validate([
    //         'file_so' => 'required|mimes:xls,xlsx',
    //         'id_bom'  => 'required'
    //     ], [
    //         'id_bom.required' => 'Pilih No Katalog BOM terlebih dahulu sebelum mengupload Excel.'
    //     ]);

    //     $user_id = auth()->id();
    //     $file = $request->file('file_so');
    //     $id_bom = $request->id_bom;

    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
    //     $data = $spreadsheet->getActiveSheet()->toArray();
    //     $headers = $data[0];

    //     $temp_data = [];
    //     $errors_color = [];
    //     $errors_size = [];
    //     $errors_bom = [];

    //     $mysql_sb = DB::connection('mysql_sb');
    //     $master_colors = $mysql_sb->table('master_colors_gmt')->pluck('id', 'name')->toArray();
    //     $master_sizes = $mysql_sb->table('master_size_new')->pluck('id', 'size')->toArray();

    //     $master_colors_reverse = array_flip($master_colors);
    //     $master_sizes_reverse = array_flip($master_sizes);

    //     // dd($master_colors , $master_sizes, $master_colors_reverse, $master_sizes_reverse);

    //     $bom_details = $mysql_sb->table('bom_marketing_detail')
    //         ->where('id_bom_marketing', $id_bom)
    //         ->select('id_color', 'id_size')
    //         ->get();

    //     $bom_colors = [];
    //     $bom_sizes  = [];

    //     foreach ($bom_details as $d) {
    //         // Ambil ID Color jika isinya bukan kosong/0/NULL
    //         if (!empty($d->id_color) && $d->id_color != '0') {
    //             $bom_colors[] = $d->id_color;
    //         }
    //         // Ambil ID Size jika isinya bukan kosong/0/NULL
    //         if (!empty($d->id_size) && $d->id_size != '0') {
    //             $bom_sizes[] = $d->id_size;
    //         }
    //     }

    //     $bom_colors = array_unique($bom_colors);
    //     $bom_sizes  = array_unique($bom_sizes);

    //     $excel_colors = [];
    //     $excel_sizes  = [];

    //     for ($i = 1; $i < count($data); $i++) {
    //         $row = $data[$i];
    //         if (empty($row[0])) continue;

    //         $style = trim($row[0]);
    //         $po    = trim($row[2]);
    //         $color_name = trim($row[5]);

    //         if (!isset($master_colors[$color_name])) {
    //             $errors_color[] = "PO: <b>$po</b> - Warna: <b>$color_name</b> tidak ditemukan di Master Color.";
    //             continue;
    //         }

    //         $id_color = $master_colors[$color_name];
    //         $has_qty_in_row = false;

    //         for ($col_index = 6; $col_index < count($row); $col_index++) {
    //             $size_name = trim($headers[$col_index]);
    //             if (empty($size_name)) continue;

    //             if (!isset($master_sizes[$size_name])) {
    //                 $errors_size[] = "PO: <b>$po</b> - Size: <b>$size_name</b> tidak ditemukan di Master Size.";
    //                 continue;
    //             }

    //             $qty = $row[$col_index];
    //             if (!empty($qty) && is_numeric($qty) && $qty > 0) {
    //                 $id_size = $master_sizes[$size_name];
    //                 $has_qty_in_row = true;

    //                 if (!in_array($id_size, $excel_sizes)) {
    //                     $excel_sizes[] = $id_size;
    //                 }

    //                 $temp_data[] = [
    //                     'user_id'    => $user_id,
    //                     'style'      => $style,
    //                     'desc'       => trim($row[1]),
    //                     'po'         => $po,
    //                     'market'     => trim($row[3]),
    //                     'ex_fty'     => trim($row[4]),
    //                     'id_color'   => $id_color,
    //                     'size'       => $id_size,
    //                     'qty'        => $qty,
    //                     'created_at' => now()
    //                 ];
    //             }
    //         }

    //         if ($has_qty_in_row && !in_array($id_color, $excel_colors)) {
    //             $excel_colors[] = $id_color;
    //         }
    //     }

    //     if (count($errors_color) > 0) return response()->json(['status' => 422, 'message' => 'Kesalahan penulisan warna', 'errors' => array_unique($errors_color)], 422);
    //     if (count($errors_size) > 0) return response()->json(['status' => 422, 'message' => 'Kesalahan penulisan size', 'errors' => array_unique($errors_size)], 422);


    //     if (!empty($bom_colors)) {
    //         $salah_colors = array_diff($excel_colors, $bom_colors);
    //         foreach ($salah_colors as $sc) {
    //             $nama_warna = $master_colors_reverse[$sc] ?? 'Unknown';
    //             $errors_bom[] = "Warna salah: <b>{$nama_warna}</b> di-upload, tidak terdaftar di Material BOM.";
    //         }

    //         $missing_colors = array_diff($bom_colors, $excel_colors);
    //         foreach ($missing_colors as $mc) {
    //             $nama_warna = $master_colors_reverse[$mc] ?? 'Unknown';
    //             $errors_bom[] = "Kekurangan data: Material BOM mewajibkan Warna <b>{$nama_warna}</b>, tetapi tidak di-upload di Excel.";
    //         }
    //     }

    //     if (!empty($bom_sizes)) {
    //         $salah_sizes = array_diff($excel_sizes, $bom_sizes);
    //         foreach ($salah_sizes as $ss) {
    //             $nama_size = $master_sizes_reverse[$ss] ?? 'Unknown';
    //             $errors_bom[] = "Size salah: <b>{$nama_size}</b> di-upload, tidak terdaftar di Material BOM.";
    //         }

    //         $missing_sizes = array_diff($bom_sizes, $excel_sizes);
    //         foreach ($missing_sizes as $ms) {
    //             $nama_size = $master_sizes_reverse[$ms] ?? 'Unknown';
    //             $errors_bom[] = "Kekurangan data: Material BOM terdapat Size <b>{$nama_size}</b>, tetapi tidak di-upload Excel.";
    //         }
    //     }

    //     if (count($errors_bom) > 0) {
    //         return response()->json([
    //             'status' => 422,
    //             'message' => 'BOM Tidak Sesuai',
    //             'errors' => array_unique($errors_bom)
    //         ], 422);
    //     }

    //     $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();

    //     if (count($temp_data) > 0) {
    //         foreach (array_chunk($temp_data, 500) as $chunk) {
    //             $mysql_sb->table('temp_so_detail')->insert($chunk);
    //         }
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Excel berhasil diproses.'
    //     ]);
    // }

    // public function uploadExcelSO(Request $request)
    // {
    //     $request->validate(['file_so' => 'required|mimes:xls,xlsx', 'id_bom' => 'required']);
    //     $user_id = auth()->id();
    //     $file = $request->file('file_so');

    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
    //     $data = $spreadsheet->getActiveSheet()->toArray();
    //     $headers = $data[0];

    //     $temp_data = []; $errors_color = []; $errors_size = [];
    //     $mysql_sb = DB::connection('mysql_sb');
    //     $master_colors = $mysql_sb->table('master_colors_gmt')->pluck('id', 'name')->toArray();
    //     $master_sizes = $mysql_sb->table('master_size_new')->pluck('id', 'size')->toArray();

    //     for ($i = 1; $i < count($data); $i++) {
    //         $row = $data[$i];
    //         if (empty($row[0])) continue;

    //         $style = trim($row[0]);
    //         $po    = trim($row[2]);
    //         $color_name = trim($row[5]);

    //         if (!isset($master_colors[$color_name])) {
    //             $errors_color[] = "Warna: <b>$color_name</b> tidak ada di Master."; continue;
    //         }

    //         for ($col_index = 6; $col_index < count($row); $col_index++) {
    //             $size_name = trim($headers[$col_index]);
    //             if (empty($size_name)) continue;

    //             if (!isset($master_sizes[$size_name])) {
    //                 $errors_size[] = "Size: <b>$size_name</b> tidak ada di Master."; continue;
    //             }

    //             $qty = $row[$col_index];
    //             if (!empty($qty) && is_numeric($qty) && $qty > 0) {
    //                 $temp_data[] = [
    //                     'user_id'    => $user_id,
    //                     'style'      => $style,
    //                     'desc'       => trim($row[1]),
    //                     'po'         => $po,
    //                     'market'     => trim($row[3]),
    //                     'ex_fty'     => trim($row[4]),
    //                     'id_color'   => $master_colors[$color_name],
    //                     'size'       => $master_sizes[$size_name],
    //                     'qty'        => $qty,
    //                     'created_at' => now()
    //                 ];
    //             }
    //         }
    //     }

    //     if (count($errors_color) > 0) return response()->json(['status' => 422, 'errors' => array_unique($errors_color)], 422);
    //     if (count($errors_size) > 0) return response()->json(['status' => 422, 'errors' => array_unique($errors_size)], 422);

    //     $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();
    //     if (count($temp_data) > 0) {
    //         foreach (array_chunk($temp_data, 500) as $chunk) {
    //             $mysql_sb->table('temp_so_detail')->insert($chunk);
    //         }
    //     }
    //     return response()->json(['status' => 200, 'message' => 'Excel berhasil diproses ke Temp.']);
    // }

    public function uploadExcelSO(Request $request)
    {
        $request->validate([
            'file_so' => 'required|mimes:xls,xlsx',
        ]);

        $user_id = auth()->id();
        $file = $request->file('file_so');
        $mysql_sb = DB::connection('mysql_sb');
        $costing = null;

        if ($request->filled('id_costing')) {
            $costing = $mysql_sb->table('act_costing')->where('id', $request->id_costing)->first();
            if (!$costing) {
                return response()->json(['status' => 404, 'message' => 'Data Costing tidak ditemukan.'], 404);
            }
            if (!in_array(strtoupper((string) $costing->app1), ['A', 'Y'], true)) {
                return response()->json(['status' => 422, 'message' => 'Costing belum disetujui App 1.'], 422);
            }
            $id_bom = null;
        } else {
            $id_bom = $request->id_bom;
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $data = $spreadsheet->getActiveSheet()->toArray();
        $headers = $data[0] ?? [];
        $headerAliases = [
            'DESCRIPTION' => 'DESC',
            'PURCHASE ORDER' => 'PO',
            'NO PO' => 'PO',
            'EX FACTORY' => 'EX FTY',
        ];
        $columnIndexes = [];
        foreach ($headers as $index => $header) {
            $label = strtoupper(trim((string) $header));
            $label = $headerAliases[$label] ?? $label;
            if ($label !== '') {
                $columnIndexes[$label] = $index;
            }
        }
        $requiredColumns = ['DESC', 'PO', 'MARKET', 'DESTINATION', 'EX FTY', 'PRODUCT SET', 'COLOR', 'FOB'];
        $missingColumns = array_values(array_diff($requiredColumns, array_keys($columnIndexes)));
        if ($missingColumns) {
            return response()->json([
                'status' => 422,
                'message' => 'Format Excel tidak sesuai. Download template SO terbaru, kolom yang belum ada: ' . implode(', ', $missingColumns) . '.',
            ], 422);
        }
        if ($costing && trim((string) $costing->styleno) === '') {
            return response()->json([
                'status' => 422,
                'message' => 'Style pada Costing belum diisi. Lengkapi Style di Costing sebelum upload Excel SO.',
            ], 422);
        }
        $sizeColumns = array_values(array_filter(
            array_keys($headers),
            fn ($index) => trim((string) $headers[$index]) !== ''
                && !in_array(strtoupper(trim((string) $headers[$index])), array_merge($requiredColumns, ['STYLE']), true)
                && !in_array(strtoupper(trim((string) $headers[$index])), array_keys($headerAliases), true)
        ));
        $cell = fn (array $row, string $column) => isset($columnIndexes[$column])
            ? ($row[$columnIndexes[$column]] ?? null)
            : null;

        $temp_data = [];
        $errors_color = [];
        $errors_size = [];
        $errors_style = [];
        $mysql_sb = DB::connection('mysql_sb');

        // Tarik Master Data dan bersihkan spasi & case-nya
        $raw_master_colors = $mysql_sb->table('master_colors_gmt')->pluck('id', 'name')->toArray();
        $master_colors = [];
        foreach ($raw_master_colors as $k => $v) {
            $master_colors[strtoupper(trim($k))] = $v;
        }

        $raw_master_sizes = $mysql_sb->table('master_size_new')->pluck('id', 'size')->toArray();
        $master_sizes = [];
        foreach ($raw_master_sizes as $k => $v) {
            $master_sizes[strtoupper(trim($k))] = $v;
        }

        // Direct Costing -> SO does not have a BOM color/size restriction.
        $bom_colors = [];
        $bom_sizes = [];
        if ($id_bom) {
            $bom_colors = $mysql_sb->table('bom_marketing_detail')
                ->where('id_bom_marketing', $id_bom)
                ->whereNotNull('id_color')
                ->where('id_color', '!=', '0')
                ->where('id_color', '!=', '')
                ->where('cancel', 'N')
                ->pluck('id_color')
                ->unique()
                ->toArray();

            $bom_sizes = $mysql_sb->table('bom_marketing_detail')
                ->where('id_bom_marketing', $id_bom)
                ->whereNotNull('id_size')
                ->where('id_size', '!=', '0')
                ->where('id_size', '!=', '')
                ->where('cancel', 'N')
                ->pluck('id_size')
                ->unique()
                ->toArray();
        }

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];

            if (!array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '')) continue;

            $excelStyle = trim((string) $cell($row, 'STYLE'));
            if ($costing && $excelStyle !== '' && strtoupper($excelStyle) !== strtoupper(trim((string) $costing->styleno))) {
                $errors_style[] = "Baris " . ($i + 1) . ": Style Excel \"{$excelStyle}\" berbeda dengan Style Costing \"{$costing->styleno}\".";
                continue;
            }
            $style = $costing ? trim((string) $costing->styleno) : $excelStyle;
            $po = trim((string) $cell($row, 'PO'));
            $color_name = trim((string) $cell($row, 'COLOR'));

            // FOB
            $fob_raw = $cell($row, 'FOB');
            $fob = null;
            if ($fob_raw !== null && $fob_raw !== '') {
                $fob_clean = trim($fob_raw, " \t\n\r\0\x0B\xC2\xA0");
                $fob_clean = preg_replace('/\s+/u', '', $fob_clean);
                $fob_clean = str_replace(',', '', $fob_clean);
                $fob = is_numeric($fob_clean) ? $fob_clean : null;
            }

            // =======================================================
            // KONDISI SINGLE & MULTIPLE (PRODUCT SET)
            // =======================================================
            $raw_product_set = trim((string) $cell($row, 'PRODUCT SET'));

            if (empty($raw_product_set) || $raw_product_set === '-' || strtolower($raw_product_set) === 'null') {
                $product_set = null;
            } else {
                $product_set = $raw_product_set;
            }
            // =======================================================

            // Validasi Warna
            $color_key = strtoupper($color_name);
            if (!isset($master_colors[$color_key])) {
                $errors_color[] = "Warna: <b>$color_name</b> tidak ada di Master.";
                continue;
            }
            $color_id = $master_colors[$color_key];

            if (count($bom_colors) > 0 && !in_array($color_id, $bom_colors)) {
                $errors_color[] = "Warna: <b>$color_name</b> tidak terdaftar di Material BOM Detail.";
                continue;
            }

            foreach ($sizeColumns as $col_index) {
                $qty_raw = $row[$col_index];
                if ($qty_raw === null || $qty_raw === '') continue;

                $qty = trim($qty_raw, " \t\n\r\0\x0B\xC2\xA0");
                $qty = preg_replace('/\s+/u', '', $qty);
                $qty = str_replace(',', '', $qty);

                if ($qty === '' || !is_numeric($qty) || $qty <= 0) continue;

                $size_name = trim((string) $headers[$col_index]);

                if (empty($size_name)) continue;

                // Validasi Size
                $size_key = strtoupper($size_name);
                if (!isset($master_sizes[$size_key])) {
                    $errors_size[] = "Size: <b>$size_name</b> tidak ada di Master.";
                    continue;
                }
                $size_id = $master_sizes[$size_key];

                if (count($bom_sizes) > 0 && !in_array($size_id, $bom_sizes)) {
                    $errors_size[] = "Size: <b>$size_name</b> (pada warna $color_name) tidak terdaftar di Material BOM Detail.";
                    continue;
                }

                $temp_data[] = [
                    'user_id'     => $user_id,
                    'style'       => $style,
                    'desc'        => trim((string) $cell($row, 'DESC')),
                    'po'          => $po,
                    'market'      => trim((string) $cell($row, 'MARKET')),
                    'destination' => trim((string) $cell($row, 'DESTINATION')),
                    'ex_fty'      => trim((string) $cell($row, 'EX FTY')),
                    'id_color'    => $color_id,
                    'size'        => $size_id,
                    'qty'         => $qty,
                    'product_set' => $product_set,
                    'fob'         => $fob,
                    'created_at'  => now()
                ];
            }
        }

        // Tampilkan Error jika ada yang diblok
        if (count($errors_style) > 0) return response()->json([
            'status' => 422,
            'message' => 'Style pada Excel harus sama dengan Style Costing.',
            'errors' => array_unique($errors_style),
        ], 422);
        if (count($errors_color) > 0) return response()->json(['status' => 422, 'errors' => array_unique($errors_color)], 422);
        if (count($errors_size) > 0) return response()->json(['status' => 422, 'errors' => array_unique($errors_size)], 422);

        // Bersihkan data temp lama dan Insert data baru
        $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();
        if (count($temp_data) > 0) {
            foreach (array_chunk($temp_data, 500) as $chunk) {
                $mysql_sb->table('temp_so_detail')->insert($chunk);
            }
        }

        if ($request->filled('id_costing')) {
            session(['so_upload_costing_id' => (string) $request->id_costing]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Excel berhasil diproses.',
            'total_inserted' => count($temp_data),
        ]);
    }


    public function getTempData(Request $request)
    {
        $user_id = auth()->id();
        $mysql_sb = DB::connection('mysql_sb');

        $isCostingFlow = $request->filled('id_costing');
        if ($isCostingFlow) {
            $id_bom = null;
        } else {
            $id_bom = $request->id_bom;
        }

        if (!$isCostingFlow && $id_bom) {
            $bom_header = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
        } else {
            $bom_header = null;
        }

        $allowed_colors = $bom_header && $bom_header->colors ? json_decode($bom_header->colors, true) : [];
        $allowed_sizes  = $bom_header && $bom_header->sizes ? json_decode($bom_header->sizes, true) : [];

        $bom_details = $id_bom
            ? $mysql_sb->table('bom_marketing_detail')->where('id_bom_marketing', $id_bom)->where('cancel', 'N')->get()
            : collect();

        $required_contents = [];
        $content_combinations = [];

        foreach($bom_details as $det) {
            $content_id = $det->id_contents;
            if (!in_array($content_id, $required_contents)) {
                $required_contents[] = $content_id;
                $content_combinations[$content_id] = [];
            }
            $c = (!empty($det->id_color) && $det->id_color !== 'null') ? (string)$det->id_color : 'ALL';
            $s = (!empty($det->id_size) && $det->id_size !== 'null') ? (string)$det->id_size : 'ALL';
            $content_combinations[$content_id][] = "{$c}_{$s}";
        }

        $temp_data = $mysql_sb->table('temp_so_detail as t')
            ->leftJoin('master_colors_gmt as c', 't.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 't.size', '=', 's.id')
            ->where('t.user_id', $user_id)
            ->select(
                't.id', 't.style', 't.desc', 't.po', 't.market', 't.destination', 't.ex_fty', 't.product_set',
                't.id_color', 't.fob', 'c.name as color_name',
                't.size as id_size', 's.size as size_name', 's.urutan as size_urutan',
                't.qty'
            )
            ->orderBy('t.id')
            ->get();

        $uploaded_colors = [];
        $uploaded_sizes = [];
        foreach ($temp_data as $row) {
            $uploaded_colors[] = (string)$row->id_color;
            $uploaded_sizes[]  = (string)$row->id_size;
        }
        $uploaded_colors = array_unique($uploaded_colors);
        $uploaded_sizes  = array_unique($uploaded_sizes);

        $missing_colors_names = [];
        foreach ($allowed_colors as $bom_c) {
            if (!in_array((string)$bom_c, $uploaded_colors)) {
                $missing_colors_names[] = $mysql_sb->table('master_colors_gmt')->where('id', $bom_c)->value('name');
            }
        }

        $missing_sizes_names = [];
        foreach ($allowed_sizes as $bom_s) {
            if (!in_array((string)$bom_s, $uploaded_sizes)) {
                $missing_sizes_names[] = $mysql_sb->table('master_size_new')->where('id', $bom_s)->value('size');
            }
        }

        $pivot_data = [];
        $available_sizes_raw = [];
        $total_qty = 0;
        $unique_po = [];

        foreach ($temp_data as $row) {
            // Logika pemisah Single vs Multiple
            $prod_set_clean = trim((string) $row->product_set);
            $is_multiple = (!empty($prod_set_clean) && $prod_set_clean !== '-' && strtolower($prod_set_clean) !== 'null');
            $set_key = $is_multiple ? $prod_set_clean : 'SINGLE';

            // Key pemisah di preview: Market + Destination + Style + PO + ProductSet + Warna
            $key = $row->market . '_' . $row->destination . '_' . $row->style . '_' . $row->po . '_' . $set_key . '_' . $row->id_color;

            if (!isset($pivot_data[$key])) {
                $pivot_data[$key] = [
                    'style'       => $row->style,
                    'desc'        => $row->desc,
                    'po'          => $row->po,
                    'market'      => $row->market,
                    'destination' => $row->destination,
                    'ex_fty'      => $row->ex_fty,
                    'product_set' => $is_multiple ? $prod_set_clean : '-',
                    'id_color'    => $row->id_color,
                    'color'       => $row->color_name,
                    'fob'         => $row->fob,
                    'errors'      => [],
                    'id_sizes'    => [],
                ];
            }

            $pivot_data[$key][$row->size_name] = $row->qty;
            $pivot_data[$key]['id_sizes'][$row->size_name] = $row->id_size;

            if (!isset($available_sizes_raw[$row->size_name])) {
                $available_sizes_raw[$row->size_name] = $row->size_urutan ?? 999;
            }

            $total_qty += $row->qty;

            // Unik PO dihitung berdasar PO + Set biar akurat
            $unique_po[$row->po . '_' . $set_key] = true;
        }

        asort($available_sizes_raw);
        $available_sizes_array = array_keys($available_sizes_raw);

        $has_bom_error = false;

        if (!$isCostingFlow && (count($missing_colors_names) > 0 || count($missing_sizes_names) > 0)) {
            $has_bom_error = true;
        }

        foreach ($pivot_data as &$row) {
            $id_color = (string)$row['id_color'];

            $in_header_color = $isCostingFlow || in_array($id_color, $allowed_colors);
            $row['color_error'] = !$in_header_color;

            foreach ($available_sizes_array as $size_name) {
                if (isset($row[$size_name])) {
                    $id_size = (string)$row['id_sizes'][$size_name];
                    $in_header_size = $isCostingFlow || in_array($id_size, $allowed_sizes);
                    $is_valid = $isCostingFlow || ($in_header_color && $in_header_size);

                    if ($is_valid) {
                        foreach ($required_contents as $content_id) {
                            $combos = $content_combinations[$content_id];

                            $exact_match     = in_array("{$id_color}_{$id_size}", $combos);
                            $color_all_match = in_array("{$id_color}_ALL", $combos);
                            $all_size_match  = in_array("ALL_{$id_size}", $combos);
                            $all_all_match   = in_array("ALL_ALL", $combos);

                            if (!$exact_match && !$color_all_match && !$all_size_match && !$all_all_match) {
                                $is_valid = false;
                                break;
                            }
                        }
                    }

                    if (!$is_valid) {
                        $row['errors'][$size_name] = true;
                        $has_bom_error = true;
                    }
                }
            }
        }

        // Satu baris per color + size (tabel Sales Order Detail di Create SO), urut sesuai upload; error = kombinasi tidak cocok BOM;
        // duplicate = PO + Market + Destination + Color + Size (dan Style/Product Set) sama persis dengan baris lain.
        $keyCounts = $temp_data->filter(fn ($row) => $row->id_color && $row->id_size)
            ->countBy(fn ($row) => self::tempRowKey((object) ['style' => $row->style, 'po' => $row->po, 'market' => $row->market, 'destination' => $row->destination,
                'product_set' => $row->product_set, 'id_color' => $row->id_color, 'size' => $row->id_size]));
        $rows = $temp_data->map(function ($row) use ($pivot_data, $keyCounts) {
            $prod_set_clean = trim((string) $row->product_set);
            $is_multiple = ($prod_set_clean !== '' && $prod_set_clean !== '-' && strtolower($prod_set_clean) !== 'null');
            $key = $row->market . '_' . $row->destination . '_' . $row->style . '_' . $row->po . '_' . ($is_multiple ? $prod_set_clean : 'SINGLE') . '_' . $row->id_color;
            return [
                'id'          => (int) $row->id,
                'style'       => $row->style,
                'desc'        => $row->desc,
                'po'          => $row->po,
                'market'      => $row->market,
                'destination' => $row->destination,
                'ex_fty'      => $row->ex_fty,
                'product_set' => $is_multiple ? $prod_set_clean : '-',
                'id_color'    => $row->id_color !== null ? (int) $row->id_color : null,
                'color'       => $row->color_name,
                'id_size'     => $row->id_size,
                'size'        => $row->size_name,
                'qty'         => (int) $row->qty,
                'fob'         => $row->fob,
                'error'       => !empty($pivot_data[$key]['errors'][$row->size_name] ?? false),
                'duplicate'   => $row->id_color && $row->id_size && ($keyCounts[self::tempRowKey((object) ['style' => $row->style, 'po' => $row->po, 'market' => $row->market,
                    'destination' => $row->destination, 'product_set' => $row->product_set, 'id_color' => $row->id_color, 'size' => $row->id_size])] ?? 0) > 1,
            ];
        })->values();

        return response()->json([
            'data'            => array_values($pivot_data),
            'rows'            => $rows,
            'available_sizes' => $available_sizes_array,
            'total_qty'       => $total_qty,
            'jumlah_po'       => count($unique_po),
            'has_bom_error'   => $has_bom_error,
            'missing_colors'  => $missing_colors_names,
            'missing_sizes'   => $missing_sizes_names
        ]);
    }

    /** Teks PO/Style/dll tidak boleh berspasi di awal/akhir atau berspasi ganda (berpengaruh ke produksi). */
    private static function badSpacing(?string $value): bool
    {
        $value = (string) $value;
        return $value !== trim($value) || preg_match('/\s{2,}/u', $value) === 1;
    }

    /**
     * Edit sel tabel Sales Order Detail (ala Excel) ke temp_so_detail.
     * changes[] = {row: {style, po, market, product_set, id_color}, field, value}; field: style|desc|po|market|ex_fty|fob|product_set|color|size:<SIZE>.
     * Diproses berurutan dalam satu transaksi; perubahan kunci baris (style/po/market/product_set/color) diikuti untuk perubahan berikutnya.
     */
    public function updateTempPreviewCells(Request $request)
    {
        $validated = $request->validate([
            'id_costing' => ['nullable', 'integer', 'exists:mysql_sb.act_costing,id'],
            'changes' => ['required', 'array', 'min:1'],
            'changes.*.row' => ['required', 'array'],
            'changes.*.row.id_color' => ['required', 'integer'],
            'changes.*.field' => ['required', 'string'],
            'changes.*.value' => ['nullable'],
        ]);
        if ($request->filled('id_costing') && session('so_upload_costing_id') !== (string) $validated['id_costing']) {
            return response()->json(['message' => 'Data upload tidak sesuai dengan Costing ini. Silakan upload ulang.'], 409);
        }

        $db = DB::connection('mysql_sb');
        $userId = auth()->id();
        $textFields = ['desc', 'po', 'market', 'destination', 'ex_fty', 'product_set'];
        $keyFields = ['po', 'market', 'destination', 'product_set', 'id_color'];
        $normSet = function ($value) {
            $value = $value === null ? '' : (string) $value;
            return ($value === '' || $value === '-' || strtolower($value) === 'null') ? null : $value;
        };
        $rowKey = fn (array $row) => json_encode([(string) ($row['style'] ?? ''), (string) ($row['po'] ?? ''), (string) ($row['market'] ?? ''), (string) ($row['destination'] ?? ''), $normSet($row['product_set'] ?? null), (int) $row['id_color']]);
        $group = function (array $row) use ($db, $userId, $normSet) {
            $query = $db->table('temp_so_detail')->where('user_id', $userId)
                ->where('id_color', (int) $row['id_color']);
            foreach (['style', 'po', 'market', 'destination'] as $column) {
                $value = (string) ($row[$column] ?? '');
                $value === '' ? $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '')) : $query->where($column, $value);
            }
            $set = $normSet($row['product_set'] ?? null);
            $set === null ? $query->where(fn ($q) => $q->whereNull('product_set')->orWhereIn('product_set', ['', '-', 'null', 'NULL'])) : $query->where('product_set', $set);
            return $query;
        };

        $masterColors = [];
        foreach ($db->table('master_colors_gmt')->pluck('id', 'name') as $name => $id) {
            $masterColors[strtoupper((string) $name)] = $id;
        }

        $errors = [];
        $renamed = [];
        $db->transaction(function () use ($db, $validated, $textFields, $keyFields, $normSet, $rowKey, $group, $masterColors, &$errors, &$renamed) {
            foreach ($validated['changes'] as $index => $change) {
                $row = $change['row'];
                // Baris yang kuncinya sudah diubah oleh perubahan sebelumnya di batch yang sama.
                $row = $renamed[$rowKey($row)] ?? $row;
                $field = $change['field'];
                $value = $change['value'] === null ? '' : (string) $change['value'];
                $fail = function ($message) use (&$errors, $index, $field) { $errors[] = ['index' => $index, 'field' => $field, 'message' => $message]; };

                if (in_array($field, array_merge($textFields, ['color']), true) && self::badSpacing($value)) {
                    $fail('Tidak boleh ada spasi di awal/akhir atau spasi ganda.');
                    continue;
                }
                if (in_array($field, ['style', 'po', 'color'], true) && $value === '') {
                    $fail('Wajib diisi.');
                    continue;
                }

                $update = null;
                if (in_array($field, $textFields, true)) {
                    $update = [$field => $field === 'product_set' ? $normSet($value) : $value];
                } elseif ($field === 'fob') {
                    $fob = str_replace(',', '', $value);
                    if ($fob !== '' && !is_numeric($fob)) { $fail('FOB harus angka.'); continue; }
                    $update = ['fob' => $fob === '' ? null : $fob];
                } elseif ($field === 'color') {
                    $colorId = $masterColors[strtoupper($value)] ?? null;
                    if (!$colorId) { $fail('Color tidak ada di master.'); continue; }
                    $update = ['id_color' => $colorId];
                } elseif (str_starts_with($field, 'size:')) {
                    $sizeId = $db->table('master_size_new')->where('size', substr($field, 5))->value('id');
                    if (!$sizeId) { $fail('Size tidak ada di master.'); continue; }
                    $qty = str_replace([',', '.'], '', $value);
                    if ($qty !== '' && (!ctype_digit($qty) || (int) $qty <= 0)) { $fail('Qty harus angka bulat lebih dari 0.'); continue; }
                    $sizeRows = $group($row)->where('size', $sizeId);
                    if ($qty === '' ) {
                        $sizeRows->delete();
                    } elseif ((clone $sizeRows)->exists()) {
                        $sizeRows->update(['qty' => (int) $qty]);
                    } else {
                        $base = $group($row)->first();
                        if (!$base) { $fail('Baris tidak ditemukan.'); continue; }
                        $db->table('temp_so_detail')->insert([
                            'user_id' => $base->user_id, 'style' => $base->style, 'desc' => $base->desc, 'po' => $base->po,
                            'market' => $base->market, 'destination' => $base->destination, 'ex_fty' => $base->ex_fty, 'id_color' => $base->id_color,
                            'size' => $sizeId, 'qty' => (int) $qty, 'product_set' => $base->product_set, 'fob' => $base->fob,
                            'created_at' => now(),
                        ]);
                    }
                    continue;
                } else {
                    $fail('Kolom tidak bisa diedit.');
                    continue;
                }

                $group($row)->update($update);
                $column = array_key_first($update);
                if (in_array($column, $keyFields, true)) {
                    $next = $row;
                    $next[$column] = $update[$column];
                    $renamed[$rowKey($change['row'])] = $next;
                    $renamed[$rowKey($row)] = $next;
                }
            }
        });

        return response()->json([
            'status' => $errors ? 207 : 200,
            'errors' => $errors,
            'message' => $errors ? count($errors) . ' sel tidak disimpan.' : 'Perubahan tersimpan.',
        ]);
    }

    public function deleteTempPreviewRow(Request $request)
    {
        $validated = $request->validate([
            'id_costing' => ['nullable', 'integer', 'exists:mysql_sb.act_costing,id'],
            'style' => ['present', 'string'],
            'po' => ['present', 'string'],
            'market' => ['present', 'string'],
            'destination' => ['present', 'string'],
            'product_set' => ['present', 'nullable', 'string'],
            'id_color' => ['required', 'integer'],
        ]);

        if ($request->filled('id_costing') &&
            session('so_upload_costing_id') !== (string) $validated['id_costing']) {
            return response()->json([
                'message' => 'Data upload tidak sesuai dengan Costing ini. Silakan upload ulang.',
            ], 409);
        }

        $query = DB::connection('mysql_sb')
            ->table('temp_so_detail')
            ->where('user_id', auth()->id())
            ->where('style', $validated['style'])
            ->where('po', $validated['po'])
            ->where('market', $validated['market'])
            ->where('destination', $validated['destination'])
            ->where('id_color', $validated['id_color']);

        if ($validated['product_set'] === null) {
            $query->whereNull('product_set');
        } else {
            $query->where('product_set', $validated['product_set']);
        }

        $deletedCount = $query->delete();
        if ($deletedCount === 0) {
            return response()->json(['message' => 'Baris preview sudah tidak tersedia.'], 404);
        }

        return response()->json([
            'message' => 'Baris preview berhasil dihapus.',
            'deleted' => $deletedCount,
        ]);
    }

    // ===== Sales Order Detail per baris (satu baris = satu color + size di temp_so_detail) =====

    private function tempUploadMismatch(Request $request): ?\Illuminate\Http\JsonResponse
    {
        if ($request->filled('id_costing') && session('so_upload_costing_id') !== (string) $request->input('id_costing')) {
            return response()->json(['message' => 'Data upload tidak sesuai dengan Costing ini. Silakan upload ulang.'], 409);
        }
        return null;
    }

    /** Kunci satu baris SO detail: PO + Style + Market + Destination + Product Set + Color + Size (tidak boleh dobel). */
    private static function tempRowKey($row): string
    {
        $set = trim((string) ($row->product_set ?? ''));
        $set = ($set === '' || $set === '-' || strtolower($set) === 'null') ? '' : $set;
        return json_encode([(string) $row->style, (string) $row->po, (string) $row->market, (string) $row->destination, $set, (string) $row->id_color, (string) $row->size]);
    }

    /**
     * Edit sel per baris: changes[] = {id, field, value}; field: desc|po|market|destination|ex_fty|product_set|fob|color|size|qty.
     * Aturan sama dengan tabel lama (spasi, wajib, master color/size, qty bulat > 0) + kombinasi color/size tidak boleh dobel.
     */
    public function updateTempRowCells(Request $request)
    {
        $validated = $request->validate([
            'id_costing' => ['nullable', 'integer', 'exists:mysql_sb.act_costing,id'],
            'changes' => ['required', 'array', 'min:1'],
            'changes.*.id' => ['required', 'integer'],
            'changes.*.field' => ['required', 'string'],
            'changes.*.value' => ['nullable'],
        ]);
        if ($mismatch = $this->tempUploadMismatch($request)) {
            return $mismatch;
        }

        $db = DB::connection('mysql_sb');
        $userId = auth()->id();
        $textFields = ['desc', 'po', 'market', 'destination', 'ex_fty', 'product_set'];
        $masterColors = [];
        foreach ($db->table('master_colors_gmt')->pluck('id', 'name') as $name => $id) {
            $masterColors[strtoupper((string) $name)] = $id;
        }
        $masterSizes = [];
        foreach ($db->table('master_size_new')->pluck('id', 'size') as $name => $id) {
            $masterSizes[strtoupper(trim((string) $name))] = $id;
        }

        $errors = [];
        $db->transaction(function () use ($db, $userId, $validated, $textFields, $masterColors, $masterSizes, &$errors) {
            foreach ($validated['changes'] as $index => $change) {
                $field = $change['field'];
                $value = $change['value'] === null ? '' : (string) $change['value'];
                $fail = function ($message) use (&$errors, $index, $field) { $errors[] = ['index' => $index, 'field' => $field, 'message' => $message]; };
                $row = $db->table('temp_so_detail')->where('user_id', $userId)->where('id', (int) $change['id'])->first();
                if (!$row) { $fail('Baris tidak ditemukan.'); continue; }

                if (in_array($field, array_merge($textFields, ['color', 'size']), true) && self::badSpacing($value)) {
                    $fail('Tidak boleh ada spasi di awal/akhir atau spasi ganda.');
                    continue;
                }
                if (in_array($field, ['po', 'color', 'size', 'qty'], true) && $value === '') {
                    $fail('Wajib diisi.');
                    continue;
                }

                if (in_array($field, $textFields, true)) {
                    $set = $field === 'product_set' && ($value === '' || $value === '-' || strtolower($value) === 'null');
                    $update = [$field => $set ? null : $value];
                } elseif ($field === 'fob') {
                    $fob = str_replace(',', '', $value);
                    if ($fob !== '' && !is_numeric($fob)) { $fail('FOB harus angka.'); continue; }
                    $update = ['fob' => $fob === '' ? null : $fob];
                } elseif ($field === 'color') {
                    $colorId = $masterColors[strtoupper($value)] ?? null;
                    if (!$colorId) { $fail('Color tidak ada di master.'); continue; }
                    $update = ['id_color' => $colorId];
                } elseif ($field === 'size') {
                    $sizeId = $masterSizes[strtoupper($value)] ?? null;
                    if (!$sizeId) { $fail('Size tidak ada di master.'); continue; }
                    $update = ['size' => $sizeId];
                } elseif ($field === 'qty') {
                    $qty = str_replace([',', '.'], '', $value);
                    if (!ctype_digit($qty) || (int) $qty <= 0) { $fail('Qty harus angka bulat lebih dari 0.'); continue; }
                    $update = ['qty' => (int) $qty];
                } else {
                    $fail('Kolom tidak bisa diedit.');
                    continue;
                }

                // Kombinasi yang sama persis (PO/Market/Destination/Color/Size) tetap disimpan, tapi tabel menandai
                // Color & Size merah dan Save diblok (lihat getTempData 'duplicate' dan storeFromCosting).
                $db->table('temp_so_detail')->where('id', $row->id)->update($update + ['updated_at' => now()]);
            }
        });

        return response()->json([
            'status' => $errors ? 207 : 200,
            'errors' => $errors,
            'message' => $errors ? count($errors) . ' sel tidak disimpan.' : 'Perubahan tersimpan.',
        ]);
    }

    /** Hapus baris Sales Order Detail (per color + size) dari temp_so_detail. */
    public function deleteTempRows(Request $request)
    {
        $validated = $request->validate([
            'id_costing' => ['nullable', 'integer', 'exists:mysql_sb.act_costing,id'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);
        if ($mismatch = $this->tempUploadMismatch($request)) {
            return $mismatch;
        }
        $deleted = DB::connection('mysql_sb')->table('temp_so_detail')
            ->where('user_id', auth()->id())->whereIn('id', $validated['ids'])->delete();
        if ($deleted === 0) {
            return response()->json(['message' => 'Baris sudah tidak tersedia.'], 404);
        }
        return response()->json(['message' => 'Baris berhasil dihapus.', 'deleted' => $deleted]);
    }

    /**
     * Tombol Add: baris baru di temp_so_detail. PO/Style/Description/Market/Destination/Ex Fty/FOB/Product Set disalin dari
     * baris sumber (yang dipilih, atau baris terakhir); Color, Size & Qty kosong (merah sampai diisi; Save diblok).
     */
    public function addTempRow(Request $request)
    {
        $validated = $request->validate([
            'id_costing' => ['nullable', 'integer', 'exists:mysql_sb.act_costing,id'],
            'source_id' => ['nullable', 'integer'],
        ]);
        if ($mismatch = $this->tempUploadMismatch($request)) {
            return $mismatch;
        }
        $db = DB::connection('mysql_sb');
        $userId = auth()->id();
        $base = null;
        if (!empty($validated['source_id'])) {
            $base = $db->table('temp_so_detail')->where('user_id', $userId)->where('id', $validated['source_id'])->first();
        }
        $base = $base ?: $db->table('temp_so_detail')->where('user_id', $userId)->orderByDesc('id')->first();
        if (!$base) {
            return response()->json(['message' => 'Upload file SO dulu sebelum menambah baris.'], 422);
        }
        $id = $db->table('temp_so_detail')->insertGetId([
            'user_id' => $userId, 'style' => $base->style, 'desc' => $base->desc, 'po' => $base->po, 'market' => $base->market,
            'destination' => $base->destination, 'ex_fty' => $base->ex_fty, 'product_set' => $base->product_set, 'fob' => $base->fob,
            'id_color' => null, 'size' => null, 'qty' => 0, 'created_at' => now(),
        ]);
        return response()->json(['message' => 'Baris baru ditambahkan.', 'id' => (int) $id]);
    }

    // public function getTempData(Request $request)
    // {
    //     $user_id = auth()->id();
    //     $id_bom = $request->id_bom;
    //     $mysql_sb = DB::connection('mysql_sb');

    //     // 1. Ambil Data Header BOM
    //     $bom_header = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
    //     $allowed_colors = $bom_header && $bom_header->colors ? json_decode($bom_header->colors, true) : [];
    //     $allowed_sizes  = $bom_header && $bom_header->sizes ? json_decode($bom_header->sizes, true) : [];

    //     // 2. CEK DETAIL MATERIAL (PERBAIKAN LOGIKA KOMBINASI)
    //     $bom_details = $mysql_sb->table('bom_marketing_detail')->where('id_bom_marketing', $id_bom)->get();

    //     $valid_combinations = [];
    //     $is_all_all = false; // Penanda jika ada material tipe 'All Color All Size'

    //     foreach($bom_details as $det) {
    //         // Ubah null atau string 'null' menjadi 'ALL' sebagai wildcard
    //         $c = (!empty($det->id_color) && $det->id_color !== 'null') ? (string)$det->id_color : 'ALL';
    //         $s = (!empty($det->id_size) && $det->id_size !== 'null') ? (string)$det->id_size : 'ALL';

    //         if ($c === 'ALL' && $s === 'ALL') {
    //             $is_all_all = true;
    //         }

    //         // Simpan sebagai pasangan. Contoh: "2_14" (Red_M) atau "2_ALL" (Red_All Size)
    //         $valid_combinations[] = "{$c}_{$s}";
    //     }
    //     $valid_combinations = array_unique($valid_combinations);

    //     // 3. Ambil data Temp (Excel SO)
    //     $temp_data = $mysql_sb->table('temp_so_detail as t')
    //         ->leftJoin('master_colors_gmt as c', 't.id_color', '=', 'c.id')
    //         ->leftJoin('master_size_new as s', 't.size', '=', 's.id')
    //         ->where('t.user_id', $user_id)
    //         ->select(
    //             't.style', 't.desc', 't.po', 't.market', 't.ex_fty',
    //             't.id_color', 'c.name as color_name',
    //             't.size as id_size', 's.size as size_name', 's.urutan as size_urutan',
    //             't.qty'
    //         )
    //         ->get();

    //     // 4. PROSES GROUPING & PIVOT DATA
    //     $pivot_data = [];
    //     $available_sizes_raw = [];
    //     $total_qty = 0;
    //     $unique_po = [];

    //     foreach ($temp_data as $row) {
    //         $key = $row->style . '_' . $row->po . '_' . $row->id_color;

    //         if (!isset($pivot_data[$key])) {
    //             $pivot_data[$key] = [
    //                 'style'    => $row->style,
    //                 'desc'     => $row->desc,
    //                 'po'       => $row->po,
    //                 'market'   => $row->market,
    //                 'ex_fty'   => $row->ex_fty,
    //                 'id_color' => $row->id_color,
    //                 'color'    => $row->color_name,
    //                 'errors'   => [],
    //                 'id_sizes' => [],
    //             ];
    //         }

    //         $pivot_data[$key][$row->size_name] = $row->qty;
    //         $pivot_data[$key]['id_sizes'][$row->size_name] = $row->id_size;

    //         if (!isset($available_sizes_raw[$row->size_name])) {
    //             $available_sizes_raw[$row->size_name] = $row->size_urutan ?? 999;
    //         }

    //         $total_qty += $row->qty;
    //         $unique_po[$row->po] = true;
    //     }

    //     asort($available_sizes_raw);
    //     $available_sizes_array = array_keys($available_sizes_raw);

    //     // 5. VALIDASI KETAT (PERBAIKAN LOGIKA)
    //     $has_bom_error = false;

    //     foreach ($pivot_data as &$row) {
    //         $id_color = (string)$row['id_color'];

    //         // Cek Header Warna
    //         $in_header_color = in_array($id_color, $allowed_colors);
    //         $row['color_error'] = !$in_header_color;

    //         foreach ($available_sizes_array as $size_name) {
    //             if (isset($row[$size_name])) {
    //                 $id_size = (string)$row['id_sizes'][$size_name];

    //                 // Cek Header Size
    //                 $in_header_size = in_array($id_size, $allowed_sizes);

    //                 // Cek di BOM Detail (Mengecek Kombinasi Eksak)
    //                 $in_detail = false;

    //                 if ($is_all_all) {
    //                     $in_detail = true; // Langsung lolos jika ada material tipe 'All Color All Size'
    //                 } else {
    //                     $exact_match     = in_array("{$id_color}_{$id_size}", $valid_combinations);
    //                     $color_all_match = in_array("{$id_color}_ALL", $valid_combinations);
    //                     $all_size_match  = in_array("ALL_{$id_size}", $valid_combinations);

    //                     if ($exact_match || $color_all_match || $all_size_match) {
    //                         $in_detail = true;
    //                     }
    //                 }

    //                 // Syarat agar TIDAK MERAH: Warna & Size ada di Header, DAN kombinasinya ada di BOM
    //                 $is_valid = $in_header_color && $in_header_size && $in_detail;

    //                 if (!$is_valid) {
    //                     $row['errors'][$size_name] = true;
    //                     $has_bom_error = true;
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'data'            => array_values($pivot_data),
    //         'available_sizes' => $available_sizes_array,
    //         'total_qty'       => $total_qty,
    //         'jumlah_po'       => count($unique_po),
    //         'has_bom_error'   => $has_bom_error
    //     ]);
    // }
    // public function getTempData(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $user_id = auth()->id();

    //         $temp_data = DB::connection('mysql_sb')->table('temp_so_detail as t')
    //             ->join('master_colors_gmt as c', 't.id_color', '=', 'c.id')
    //             ->join('master_size_new as s', 't.size', '=', 's.id')
    //             ->select('t.*', 'c.name as color_name', 's.size as size_name', 's.urutan')
    //             ->where('t.user_id', $user_id)
    //             ->get();

    //         $data = $temp_data->groupBy(function($item) {
    //             return $item->style . '|' . $item->po . '|' . $item->color_name . '|' . $item->market . '|' . $item->ex_fty;
    //         })->map(function($group) {

    //             $first = $group->first();
    //             $data = [
    //                 'style'  => $first->style,
    //                 'desc'   => $first->desc,
    //                 'po'     => $first->po,
    //                 'market' => $first->market,
    //                 'ex_fty' => $first->ex_fty,
    //                 'color'  => $first->color_name,
    //             ];

    //             foreach ($group as $item) {
    //                 $data[$item->size_name] = ($data[$item->size_name] ?? 0) + $item->qty;
    //             }

    //             return $data;

    //         })->values();

    //         $all_sizes = $temp_data->sortBy('urutan')
    //             ->pluck('size_name')
    //             ->unique()
    //             ->values()
    //             ->toArray();

    //         return response()->json([
    //             'data' => $data,
    //             'available_sizes' => $all_sizes,
    //             'total_qty' => $temp_data->sum('qty'),
    //             'jumlah_po' => $temp_data->unique('po')->count()
    //         ]);
    //     }
    // }

    private function generate_kode($id_buyer, bool $includeCostingNumbers = true)
    {

        $mysql_sb = DB::connection('mysql_sb');
        $now = now();
        $bln_thn = $now->format('my');

        $cost_no = null;
        $kpno = null;
        if ($includeCostingNumbers) {
            $mattype_cost = 'CST-' . $now->format('Y');
            $cek_cost = $mysql_sb->table('tempbpb')->where('mattype', $mattype_cost)->first();

            if (!$cek_cost) {
                $next_costing = 1;
                $mysql_sb->table('tempbpb')->insert(['mattype' => $mattype_cost, 'BPBNo' => $next_costing]);
            } else {
                $next_costing = $cek_cost->BPBNo + 1;
                $mysql_sb->table('tempbpb')->where('mattype', $mattype_cost)->update(['BPBNo' => $next_costing]);
            }

            $cost_no = "CST/$bln_thn/" . str_pad($next_costing, 5, '0', STR_PAD_LEFT);

            $buyer = $mysql_sb->table('mastersupplier')->where('Id_Supplier', $id_buyer)->first();
            $kode_supplier = trim((string) ($buyer->supplier_code ?? ''));
            if ($kode_supplier === '') {
                throw new \RuntimeException('Kode supplier buyer wajib diisi untuk membuat WS/KP No.');
            }

            $mattype_kpno = $kode_supplier . '-' . $now->format('Y');
            $cek_kpno = $mysql_sb->table('tempbpb')->where('mattype', $mattype_kpno)->first();

            if (!$cek_kpno) {
                $next_kpno = 1;
                $mysql_sb->table('tempbpb')->insert(['mattype' => $mattype_kpno, 'BPBNo' => $next_kpno]);
            } else {
                $next_kpno = $cek_kpno->BPBNo + 1;
                $mysql_sb->table('tempbpb')->where('mattype', $mattype_kpno)->update(['BPBNo' => $next_kpno]);
            }

            $kpno = "$kode_supplier/$bln_thn/" . str_pad($next_kpno, 3, '0', STR_PAD_LEFT);
        }


         // get untuk no so
        $mattype_so = 'SO-' . $now->format('Y');
        $cek_so = $mysql_sb->table('tempbpb')->where('mattype', $mattype_so)->first();


        if (!$cek_so) {
            $next_so = 1;
            $mysql_sb->table('tempbpb')->insert(['mattype' => $mattype_so, 'BPBNo' => $next_so]);
        } else {
            $next_so = $cek_so->BPBNo + 1;
            $mysql_sb->table('tempbpb')->where('mattype', $mattype_so)->update(['BPBNo' => $next_so]);
        }

        $so_no = "SO/$bln_thn/" . str_pad($next_so, 5, '0', STR_PAD_LEFT);

        // JO NO
        $mattype_jo = 'JO-' . $now->format('Y');
        $cek_jo = $mysql_sb->table('tempbpb')->where('mattype', $mattype_jo)->first();

        if (!$cek_jo) {
            $next_jo = 1;
            $mysql_sb->table('tempbpb')->insert(['mattype' => $mattype_jo, 'BPBNo' => $next_jo]);
        } else {
            $next_jo = $cek_jo->BPBNo + 1;
            $mysql_sb->table('tempbpb')->where('mattype', $mattype_jo)->update(['BPBNo' => $next_jo]);
        }


        $jo_no = "JO/$bln_thn/" . str_pad($next_jo, 5, '0', STR_PAD_LEFT);

        return compact('cost_no', 'kpno', 'so_no' , 'jo_no');
    }

    // public function store(Request $request)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');
    //     $username = auth()->user()->name;
    //     $user_id = auth()->id();

    //     $temp_data = $mysql_sb->table('temp_so_detail')
    //         ->where('user_id', $user_id)
    //         ->get()
    //         ->groupBy('po');

    //     if ($temp_data->isEmpty()) return response()->json(['message' => 'Data Kosong!'], 400);

    //     $mysql_sb->beginTransaction();

    //     try {
    //         $file_name = null;
    //         if ($request->hasFile('images')) {
    //             $file = $request->file('images');
    //             $file_name = time() . '.' . $file->getClientOriginalExtension();

    //             $target_path = public_path('uploads/so');

    //             if (!File::isDirectory($target_path)) {
    //                 File::makeDirectory($target_path, 0755, true, true);
    //             }
    //             $file->move($target_path, $file_name);
    //         }

    //         foreach ($temp_data as $no_po => $details) {
    //             $total_qty_po = $details->sum('qty');
    //             $kode = $this->generate_kode($request->id_buyer);

    //             $id_cost = $mysql_sb->table('act_costing')->insertGetId([
    //                 'cost_no'     => $kode['cost_no'],
    //                 'cost_date'   => now(),
    //                 'kpno'        => $kode['kpno'],
    //                 'id_buyer'    => $request->id_buyer,
    //                 'styleno'     => $request->style,
    //                 'qty'         => $total_qty_po,
    //                 'curr'        => $request->id_currency,
    //                 'username'    => $username,
    //                 'brand'       => $request->brand,
    //                 'smv_min'     => $request->smv,
    //                 'id_product'  => $request->id_product_item,
    //                 'notes'       => $request->notes,
    //                 'vat'         => $request->vat,
    //                 'ga_cost'     => $request->ga_cost,
    //                 'comm_cost'   => $request->commission_fee,
    //                 'cfm_price'   => $request->confirm_price,
    //                 'mkt_order'   => $request->marketing_order,
    //                 'dateinput'   => now(),
    //                 'aktif'       => 'Y',
    //             ]);

    //             $id_so = $mysql_sb->table('so')->insertGetId([
    //                 'id_cost'  => $id_cost,
    //                 'buyerno'  => $request->id_buyer,
    //                 'so_no'    => $kode['so_no'],
    //                 'no_po'    => $no_po,
    //                 'so_date'  => now(),
    //                 'curr'     => $request->id_currency,
    //                 'qty'      => $total_qty_po,
    //                 'username' => $username,
    //                 'd_insert' => now(),
    //                 'id_bom'   => $request->id_bom,
    //                 'market'   => $request->market,
    //                 'nm_file'  => $file_name,

    //                 'style'    => $request->style,
    //                 'brand'    => $request->brand,
    //                 'id_product'  => $request->id_product_item,
    //                 'smv'       => $request->smv,
    //                 'marketing_order'       => $request->marketing_order,
    //                 'notes'       => $request->notes,

    //             ]);

    //             $id_jo = $mysql_sb->table('jo')->insertGetId([
    //                 'jo_no'    => $kode['jo_no'],
    //                 'jo_date'  => now(),
    //                 'username' => $username,
    //                 'app'      => 'A',
    //                 'd_insert' => now(),

    //             ]);

    //             $id_jo_det = $mysql_sb->table('jo_det')->insertGetId([
    //                 'id_so'    => $id_so,
    //                 'id_jo'    => $id_jo,
    //                 'cancel'   => 'N',
    //             ]);

    //             $details_insert = [];
    //             foreach ($details as $d) {

    //                 $color = $mysql_sb->table('master_colors_gmt')
    //                     ->where('id', $d->id_color)
    //                     ->first();

    //                 $size = $mysql_sb->table('master_size_new')
    //                     ->where('id', $d->size)
    //                     ->first();

    //                 $details_insert[] = [
    //                     'id_so'        => $id_so,
    //                     'color'        => $color->name,
    //                     'size'         => $size->size,
    //                     'qty'          => $d->qty,
    //                     'styleno_prod' => $d->style,
    //                     'deldate_det'  => date('Y-m-d' , strtotime($d->ex_fty)),
    //                     'created_by'   => $username,
    //                     'created_date' => now(),
    //                     'cancel'       => 'N',
    //                     'unit'         => 'PCS',
    //                     'id_color'     => $color->id,
    //                     'id_size'      => $size->id,
    //                     'product_set'  => $d->product_set ?? null,
    //                 ];
    //             }

    //             $mysql_sb->table('so_det')->insert($details_insert);
    //         }

    //         $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();

    //         $mysql_sb->commit();

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Berhasil menyimpan ' . $temp_data->count() . ' PO.'
    //         ]);

    //     } catch (\Exception $e) {
    //         $mysql_sb->rollBack();
    //         \Log::error("Gagal Simpan SO: " . $e->getMessage());

    //         return response()->json([
    //             'status' => 500,
    //             'message' => 'Gagal Simpan: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        if ($request->filled('id_costing')) {
            return $this->storeFromCosting($request);
        }

        $mysql_sb = DB::connection('mysql_sb');
        $username = auth()->user()->name;
        $username_2 = auth()->user()->username;
        $user_id = auth()->id();

        $temp_data = $mysql_sb->table('temp_so_detail')
            ->where('user_id', $user_id)
            ->get()
            ->groupBy('po');

        if ($temp_data->isEmpty()) return response()->json(['message' => 'Data Kosong!'], 400);

        $po_type = $request->po_type ?? 'single';

        if ($po_type === 'multiple') {
            $all_po_array = $temp_data->keys()->toArray();
            $no_po_combined = implode(', ', $all_po_array);
            if (strlen($no_po_combined) > 200) {
                $no_po_combined = substr($no_po_combined, 0, 195) . '...';
            }

            $all_details = $temp_data->flatten(1);

            $temp_data = collect([
                $no_po_combined => $all_details
            ]);
        }

        $mysql_sb->beginTransaction();

        try {
            $file_name = null;
            if ($request->hasFile('images')) {
                $file = $request->file('images');
                $file_name = time() . '.' . $file->getClientOriginalExtension();

                $target_path = public_path('uploads/so');

                if (!File::isDirectory($target_path)) {
                    File::makeDirectory($target_path, 0755, true, true);
                }
                $file->move($target_path, $file_name);
            }


            $bom = $mysql_sb->table('bom_marketing')->where('id', $request->id_bom)->first();

            $mat_details = collect();
            $mfg_details = collect();
            $oth_details = collect();

            if ($bom) {
                $act_costing_new = $mysql_sb->table('act_costing_new')->where('id', $bom->id_costing)->first();

                if ($act_costing_new) {
                    $mat_details = $mysql_sb->table('act_costing_detail_new')
                        ->where('id_costing', $act_costing_new->id)
                        ->whereIn('type', ['Fabric', 'Accessories Sewing', 'Accessories Packing'])
                        ->get();

                    $mfg_details = $mysql_sb->table('act_costing_detail_new')
                        ->where('id_costing', $act_costing_new->id)
                        ->where('type', 'Manufacturing')
                        ->get();

                    $oth_details = $mysql_sb->table('act_costing_detail_new')
                        ->where('id_costing', $act_costing_new->id)
                        ->where('type', 'Other Cost')
                        ->get();
                }
            }

            $jenis_rate_val = 'B';
            if (isset($act_costing_new) && $act_costing_new->curr) {
                $curr_record = $mysql_sb->table('masterpilihan')->where('id', $act_costing_new->curr)->first();
                if ($curr_record && strtoupper($curr_record->nama_pilihan) == 'USD') {
                    $jenis_rate_val = 'J';
                }
            }

            $rate_to_idr = 1;
            if (isset($act_costing_new) && $act_costing_new->rate_to_idr > 0) {
                $rate_to_idr = $act_costing_new->rate_to_idr;
            }

            foreach ($temp_data as $no_po => $details) {
                $total_qty_po = $details->sum('qty');
                $wsBuyerId = $request->id_buyer ?: ($bom->id_buyer ?? null);
                if (!$wsBuyerId) {
                    throw new \RuntimeException('Buyer wajib dipilih untuk membuat WS/KP No.');
                }

                $kode = $this->generate_kode($wsBuyerId);
                if (empty($kode['kpno'])) {
                    throw new \RuntimeException('WS/KP No. gagal dibuat.');
                }

                // save ke act_costing
                $curr_name = '';
                if ($act_costing_new && $act_costing_new->curr) {
                    $curr_record = $mysql_sb->table('masterpilihan')->where('id', $act_costing_new->curr)->first();
                    if ($curr_record) {
                        $curr_name = strtoupper($curr_record->nama_pilihan);
                    }
                }

                $id_cost = $mysql_sb->table('act_costing')->insertGetId([
                    'cost_no'     => $kode['cost_no'],
                    'cost_date'   => now(),
                    'kpno'        => $kode['kpno'],
                    'id_buyer'    => $wsBuyerId,
                    'styleno'     => $request->style ?? ($act_costing_new ? $act_costing_new->styleno : null),
                    'qty'         => $act_costing_new ? $act_costing_new->qty : $total_qty_po,
                    'curr'        => $curr_name ?: ($act_costing_new ? $act_costing_new->curr : $request->id_currency),
                    'username'    => $username_2 ?? ($act_costing_new ? $act_costing_new->username : null),
                    'brand'       => $request->brand ?? ($act_costing_new ? $act_costing_new->brand : null),
                    'smv_min'     => $request->smv ?? ($act_costing_new ? $act_costing_new->smv_min : 0),
                    'id_product'  => $request->id_product_item ?? null,
                    'notes'       => $request->notes ?? null,
                    'mkt_order'   => $request->marketing_order == 1 ? 'BANDUNG' : ($request->marketing_order == 2 ? 'JAKARTA' : ''),
                    'dateinput'   => now(),
                    'aktif'       => 'Y',
                    'cfm_price'   => $act_costing_new->confirm_price ?? 0,
                    'vat'         => $request->vat ?? 0,
                    'deldate'     => ($act_costing_new && $act_costing_new->deldate) ? $act_costing_new->deldate : ($details->min('ex_fty') ? date('Y-m-d', strtotime($details->min('ex_fty'))) : null),
                    'type_ws'     => $act_costing_new ? $act_costing_new->tipe_ws : null,
                    'id_smode'    => $act_costing_new ? $act_costing_new->ship_mode : null,
                    'status'      => ($act_costing_new && $act_costing_new->status) ? $act_costing_new->status : 'CONFIRM',
                    'unit'        => ($act_costing_new && $act_costing_new->unit) ? $act_costing_new->unit : 'PCS',
                    'main_dest'   => $act_costing_new ? $act_costing_new->main_dest : '-',
                ]);

                $curr_name_so = '';
                $curr_record = $mysql_sb->table('masterpilihan')->where('id', $request->id_currency)->first();
                if ($curr_record) {
                    $curr_name_so = strtoupper($curr_record->nama_pilihan);
                }

                // save ke so
                $id_so = $mysql_sb->table('so')->insertGetId([
                    'id_cost'   => $id_cost,
                    'buyerno'   => $no_po,
                    'so_no'     => $kode['so_no'],
                    'no_po'     => $no_po,
                    'so_date'   => now(),
                    'curr'      => $curr_name_so ?? ($request->id_currency ? $request->id_currency : null),
                    'qty'       => $total_qty_po,
                    'username'  => $username_2 ?? ($act_costing_new ? $act_costing_new->username : null),
                    'd_insert'  => now(),
                    'id_bom'    => $request->id_bom ?? null,
                    'market'    => $request->market ?? ($act_costing_new ? $act_costing_new->market : null),
                    'nm_file'   => $file_name,
                    'style'     => $request->style ?? ($act_costing_new ? $act_costing_new->style : null),
                    'brand'     => $request->brand ?? ($act_costing_new ? $act_costing_new->brand : null),
                    'id_product'=> $request->id_product_item ?? null,
                    'smv'       => $request->smv ?? ($act_costing_new ? $act_costing_new->smv : null),
                    'marketing_order' => $request->marketing_order ?? null,
                    'notes'     => $request->notes ?? null,
                    'unit'      => ($act_costing_new && $act_costing_new->unit) ? $act_costing_new->unit : 'PCS',
                    'id_season' => $act_costing_new->season_id ?? null,
                    'jns_so'    => $request->jns_so,
                    'fob'        => $request->fob ?? 0,
                ]);

                // save ke jo & jo_det
                $id_jo = $mysql_sb->table('jo')->insertGetId([
                    'jo_no'    => $kode['jo_no'],
                    'jo_date'  => now(),
                    'username' => $username_2 ?? ($act_costing_new ? $act_costing_new->username : null),
                    'app'      => 'A',
                    'd_insert' => now(),
                ]);

                $mysql_sb->table('jo_det')->insert([
                    'id_so'    => $id_so,
                    'id_jo'    => $id_jo,
                    'cancel'   => 'N',
                ]);

                // save ke act_costing_mat
                if ($mat_details->isNotEmpty()) {
                    $mat_insert_data = [];
                    foreach ($mat_details as $mat) {
                        $mat_insert_data[] = [
                            'id_act_cost'     => $id_cost,
                            'id_item'         => $mat->item_id,
                            'price'           => $mat->price != 0 ? $mat->price : (($jenis_rate_val == 'J') ? ($mat->value_usd ?? 0) : ($mat->value_idr ?? 0)),
                            'cons'            => $mat->cons ?? 0,
                            'unit'            => $mat->unit ?? '',
                            'allowance'       => $mat->allowance ?? 0,
                            'material_source' => $mat->origin ?? 'LOKAL',
                            'jenis_rate'      => $jenis_rate_val,
                        ];
                    }
                    $mysql_sb->table('act_costing_mat')->insert($mat_insert_data);
                 }
                 // save ke act_costing_mfg
                 if ($mfg_details->isNotEmpty()) {
                    $mfg_insert_data = [];
                    foreach ($mfg_details as $mfg) {
                        $mfg_insert_data[] = [
                            'id_act_cost'     => $id_cost,
                            'id_item'         => $mfg->item_id,
                            'smv'             => null,
                            'price'           => $mfg->price != 0 ? $mfg->price : (($jenis_rate_val == 'J') ? ($mfg->value_usd ?? 0) : ($mfg->value_idr ?? 0)),
                            'cons'            => $mfg->cons ?? 1,
                            'unit'            => $mfg->unit ?? 'PCS',
                            'allowance'       => $mfg->allowance ?? 0,
                            'material_source' => $mfg->origin ?? 'LOKAL',
                            'jenis_rate'      => $jenis_rate_val,
                        ];
                    }
                    $mysql_sb->table('act_costing_mfg')->insert($mfg_insert_data);
                 }
                 // save ke act_costing_oth
                 if ($oth_details->isNotEmpty()) {
                    $oth_insert_data = [];
                    foreach ($oth_details as $oth) {
                        $oth_insert_data[] = [
                            'id_act_cost'     => $id_cost,
                            'id_item'         => $oth->item_id,
                            'smv'             => null,
                            'price'           => $oth->price != 0 ? $oth->price : (($jenis_rate_val == 'J') ? ($oth->value_usd ?? 0) : ($oth->value_idr ?? 0)),
                            'cons'            => $oth->cons ?? 1,
                            'unit'            => $oth->unit ?? 'PCS',
                            'allowance'       => $oth->allowance ?? 0,
                            'material_source' => $oth->origin ?? 'LOKAL',
                            'jenis_rate'      => $jenis_rate_val,
                        ];
                    }
                    $mysql_sb->table('act_costing_oth')->insert($oth_insert_data);
                 }

                // save ke so_det

                // Baris Excel per destination digabung per color + size; qty per destination ke so_destination.
                $this->insertSoDetailsWithDestinations($mysql_sb, $details, function ($d) use ($mysql_sb, $id_so, $username, $act_costing_new) {
                    $color = $mysql_sb->table('master_colors_gmt')->where('id', $d->id_color)->first();
                    $size  = $mysql_sb->table('master_size_new')->where('id', $d->size)->first();

                    if($d->market == ""){
                        $d->market = '-';
                    }

                    return [
                        'id_so'        => $id_so,
                        'color'        => $color->name ?? '-',
                        'size'         => $size->size ?? '-',
                        'qty'          => $d->qty,
                        'styleno_prod' => $d->style,
                        'reff_no'      => $d->style ?? ($act_costing_new ? $act_costing_new->styleno : null),
                        'deldate_det'  => date('Y-m-d' , strtotime($d->ex_fty)),
                        'created_by'   => $username,
                        'created_date' => now(),
                        'cancel'       => 'N',
                        'unit'         => 'PCS',
                        'id_color'     => $color->id ?? null,
                        'id_size'      => $size->id ?? null,
                        'product_set'  => $d->product_set ?? null,
                        'market' => !empty($d->market) ? $d->market : '-',
                        'price' => $act_costing_new ? $act_costing_new->confirm_price : 0,
                        'sku'   => '-',
                        'fob'=> $d->fob,
                    ];
                }, $username);

                $this->logSo($id_so);
                $this->logAllSoDet($id_so);
                app(MarketingHistoryService::class)->record(
                    (int) $id_cost,
                    'so',
                    'created',
                    "Sales Order {$kode['so_no']} dibuat untuk Purchase Order {$no_po}.",
                    (int) $id_so
                );

                $bom_items = $mysql_sb->select("
                    SELECT DISTINCT id_item
                    FROM bom_marketing_detail
                    WHERE id_bom_marketing = ?
                    AND id_item IS NOT NULL
                    AND cancel = 'N'
                ", [$request->id_bom]);

                $posno_counter = $mysql_sb->table('bom_jo_item')
                    ->where('id_jo', $id_jo)
                    ->distinct()
                    ->count('posno');

                foreach ($bom_items as $bom_item) {
                    $posno_counter++;
                    $posno = str_pad($posno_counter, 3, '0', STR_PAD_LEFT);

                    $query_insert_bom_jo = "
                        INSERT INTO bom_jo_item (
                            id_jo,
                            id_so_det,
                            status,
                            id_item,
                            cons,
                            unit,
                            rule_bom,
                            cancel,
                            add_item,
                            username,
                            dateinput,
                            id_panel,
                            posno,
                            notes
                        )
                        SELECT
                            '$id_jo'                                                                    as id_jo,
                            sd.id                                                                       as id_so_det,
                            CASE WHEN acd.type = 'Manufacturing' THEN 'P' ELSE 'M' END                 as status,
                            CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE mi.id_gen END    as id_item,
                            bmd.qty                                                                     as cons,
                            bmd.unit,
                            UPPER(bmd.rule_bom)                                                         as rule_bom,
                            'N'                                                                         as cancel,
                            'N'                                                                         as add_item,
                            '$username_2'                                                               as username,
                            now()                                                                       as dateinput,
                            bmd.shell                                                                   as id_panel,
                            '$posno'                                                                    as posno,
                            bmd.notes                                                                   as notes
                        FROM so_det sd
                        INNER JOIN bom_marketing_detail bmd
                            ON  bmd.id_bom_marketing = '$request->id_bom'
                            AND bmd.id_item = {$bom_item->id_item}
                            AND (bmd.id_color IS NULL OR bmd.id_color = sd.id_color)
                            AND (bmd.id_size  IS NULL OR bmd.id_size  = sd.id_size)
                            AND (bmd.id_set   IS NULL OR bmd.id_set   = (SELECT id FROM master_set WHERE nama = sd.product_set LIMIT 1))
                        INNER JOIN masteritem mi
                            ON mi.id_item = bmd.id_item
                        LEFT JOIN act_costing_detail_new acd
                            ON bmd.id_costing_detail = acd.id
                        WHERE sd.id_so = '$id_so' AND bmd.cancel = 'N'
                        AND mi.id_gen IS NOT NULL
                    ";

                    $mysql_sb->statement($query_insert_bom_jo);
                }

            }

            $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();

            $mysql_sb->commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menyimpan ' . $temp_data->count() . ' PO berserta detail materialnya.'
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollBack();
            \Log::error("Gagal Simpan SO: " . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'Gagal Simpan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function storeFromCosting(Request $request)
    {
        $request->validate([
            'id_costing' => ['required', 'integer', 'exists:mysql_sb.act_costing,id'],
            'images' => ['nullable', 'image', 'max:10240'],
        ]);

        $db = DB::connection('mysql_sb');
        $user = auth()->user();
        $userId = auth()->id();
        $costingId = (string) $request->input('id_costing');

        if (session('so_upload_costing_id') !== $costingId) {
            return response()->json([
                'status' => 409,
                'message' => 'Upload Excel belum dilakukan untuk Costing ini. Silakan upload ulang.',
            ], 409);
        }

        $tempDetails = $db->table('temp_so_detail')
            ->where('user_id', $userId)
            ->get()
            ->groupBy('po');

        if ($tempDetails->isEmpty()) {
            return response()->json(['status' => 400, 'message' => 'Data Excel kosong.'], 400);
        }

        // PO/Style/dll berspasi ganda atau berspasi di awal/akhir berpengaruh ke produksi -> blok.
        $spacingIssues = $tempDetails->flatten(1)->filter(fn ($row) => collect(['style', 'desc', 'po', 'market', 'destination', 'product_set'])
            ->contains(fn ($column) => $row->{$column} !== null && self::badSpacing($row->{$column})));
        if ($spacingIssues->isNotEmpty()) {
            return response()->json([
                'status' => 422,
                'message' => 'Masih ada PO/Style/Description/Market/Destination/Product Set dengan spasi ganda atau spasi di awal/akhir. Perbaiki sel merah di Sales Order Detail.',
            ], 422);
        }
        // Baris dari tombol Add yang belum lengkap, atau kombinasi color + size dobel -> blok.
        $allTempRows = $tempDetails->flatten(1);
        $incomplete = $allTempRows->filter(fn ($row) => !$row->id_color || !$row->size || (int) $row->qty <= 0)->count();
        if ($incomplete) {
            return response()->json([
                'status' => 422,
                'message' => "Masih ada {$incomplete} baris di Sales Order Detail tanpa Color, Size atau Qty. Lengkapi atau hapus baris itu.",
            ], 422);
        }
        $duplicates = $allTempRows->groupBy(fn ($row) => self::tempRowKey($row))->filter(fn ($group) => $group->count() > 1)->count();
        if ($duplicates) {
            return response()->json([
                'status' => 422,
                'message' => "Ada {$duplicates} kombinasi Color + Size yang dobel untuk PO yang sama di Sales Order Detail.",
            ], 422);
        }

        if (($request->input('po_type') ?? 'single') === 'multiple') {
            $combinedPo = implode(', ', $tempDetails->keys()->toArray());
            if (strlen($combinedPo) > 200) {
                $combinedPo = substr($combinedPo, 0, 195) . '...';
            }
            $tempDetails = collect([$combinedPo => $tempDetails->flatten(1)]);
        }

        $costing = $db->table('act_costing')->where('id', $costingId)->first();
        if (!$costing) {
            return response()->json(['status' => 404, 'message' => 'Data Costing tidak ditemukan.'], 404);
        }
        if (!in_array(strtoupper((string) $costing->app1), ['A', 'Y'], true)) {
            return response()->json(['status' => 422, 'message' => 'Costing belum diApprove.'], 422);
        }
        $styleMismatch = $tempDetails->flatten(1)->first(
            fn ($row) => strtoupper(trim((string) $row->style)) !== strtoupper(trim((string) $costing->styleno))
        );
        if ($styleMismatch) {
            return response()->json([
                'status' => 422,
                'message' => 'Style pada data upload tidak sesuai dengan Style Costing. Upload ulang menggunakan template terbaru.',
            ], 422);
        }
        if ($db->table('so')->where('id_cost', $costingId)->where('cancel_h', 'N')->exists()) {
            return response()->json(['status' => 409, 'message' => 'Sales Order untuk Costing ini sudah dibuat.'], 409);
        }

        $fileName = null;
        $targetPath = public_path('uploads/so');
        try {
            $result = $db->transaction(function () use ($db, $request, $user, $userId, $costing, $costingId, $tempDetails, &$fileName, $targetPath) {
                if ($request->hasFile('images')) {
                    if (!File::isDirectory($targetPath)) {
                        File::makeDirectory($targetPath, 0755, true, true);
                    }
                    $file = $request->file('images');
                    $fileName = uniqid('so_', true) . '.' . $file->getClientOriginalExtension();
                    $file->move($targetPath, $fileName);
                }

                $resultCount = $tempDetails->count();
                $bom = app(MarketingBomService::class)->ensureForCosting((int) $costingId);
                foreach ($tempDetails as $po => $details) {
                    $totalQty = $details->sum('qty');
                    $codes = $this->generate_kode($costing->id_buyer, false);
                    $now = now();

                    $idSo = $db->table('so')->insertGetId([
                        'id_cost' => $costingId,
                        'buyerno' => $po,
                        'so_no' => $codes['so_no'],
                        'no_po' => $po,
                        'so_date' => $now,
                        'curr' => $costing->curr,
                        'qty' => $totalQty,
                        'username' => $user->username ?? $user->name,
                        'd_insert' => $now,
                        'id_bom' => $bom->id,
                        'market' => $costing->market,
                        'nm_file' => $fileName,
                        'style' => $costing->styleno,
                        'brand' => $costing->brand,
                        'id_product' => $costing->id_product,
                        'smv' => $costing->smv_min,
                        'marketing_order' => $request->input('marketing_order'),
                        'notes' => $request->input('notes', $costing->notes),
                        'unit' => $costing->unit ?: 'PCS',
                        'id_season' => $costing->season_id,
                        'jns_so' => $request->input('jns_so'),
                        'fob' => $request->input('fob', 0),
                    ]);

                    $idJo = $db->table('jo')->insertGetId([
                        'jo_no' => $codes['jo_no'],
                        'jo_date' => $now,
                        'username' => $user->username ?? $user->name,
                        'app' => 'A',
                        'd_insert' => $now,
                    ]);
                    $db->table('jo_det')->insert([
                        'id_so' => $idSo,
                        'id_jo' => $idJo,
                        'cancel' => 'N',
                    ]);

                    // Baris Excel per destination digabung per color + size; qty per destination ke so_destination.
                    $this->insertSoDetailsWithDestinations($db, $details, function ($detail) use ($db, $idSo, $costing, $user, $now) {
                        $color = $db->table('master_colors_gmt')->where('id', $detail->id_color)->first();
                        $size = $db->table('master_size_new')->where('id', $detail->size)->first();
                        return [
                            'id_so' => $idSo,
                            'color' => $color->name ?? '-',
                            'size' => $size->size ?? '-',
                            'qty' => $detail->qty,
                            'styleno_prod' => $costing->styleno,
                            'reff_no' => $costing->styleno,
                            'deldate_det' => $detail->ex_fty ? date('Y-m-d', strtotime($detail->ex_fty)) : null,
                            'created_by' => $user->name,
                            'created_date' => $now,
                            'cancel' => 'N',
                            'unit' => $costing->unit ?: 'PCS',
                            'id_color' => $color->id ?? null,
                            'id_size' => $size->id ?? null,
                            'product_set' => $detail->product_set,
                            'market' => $detail->market ?: '-',
                            'price' => $costing->cfm_price ?? 0,
                            'sku' => '-',
                            'fob' => $detail->fob,
                        ];
                    }, $user->name);
                    $this->logSo($idSo);
                    $this->logAllSoDet($idSo);
                    app(MarketingHistoryService::class)->soCreated((int) $costingId, (int) $idSo, (string) $codes['so_no']);
                }

                app(MarketingBomService::class)->refreshColorsSizes($bom->id);
                $db->table('temp_so_detail')->where('user_id', $userId)->delete();
                return $resultCount;
            });

            session()->forget('so_upload_costing_id');
            return response()->json([
                'status' => 200,
                'message' => "Berhasil menyimpan {$result} Sales Order dari Costing {$costing->cost_no}.",
            ]);
        } catch (\Exception $e) {
            if ($fileName && File::exists($targetPath . DIRECTORY_SEPARATOR . $fileName)) {
                File::delete($targetPath . DIRECTORY_SEPARATOR . $fileName);
            }

            \Log::error('Gagal membuat SO dari Costing.', [
                'id_costing' => $costingId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menyimpan Sales Order: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function get_detail($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $header = $mysql_sb->table('so')
            ->leftJoin('act_costing as ac', 'so.id_cost', '=', 'ac.id')
            ->leftJoin('mastersupplier as ms', 'ac.id_buyer', '=', 'ms.Id_Supplier')
            ->select('so.so_no', 'ac.kpno as kpno', 'ms.Supplier as buyer', 'so.style', 'so.market')
            ->where('so.id', $id)
            ->first();

        if (!$header) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $details = $mysql_sb->table('so_det')
            ->leftJoin('master_size_new', 'so_det.id_size', '=', 'master_size_new.id')
            ->select(
                'so_det.*',
                'master_size_new.urutan as size_urutan',
                DB::raw('(SELECT COUNT(so_det_id) FROM laravel_nds.marker_input_detail WHERE so_det_id = so_det.id) AS cek_marker')
            )
            ->where('so_det.id_so', $id)
            ->get();

        $lockedIds = DB::table('ppic_master_so')
            ->whereIn('id_so_det', $details->pluck('id'))
            ->pluck('id_so_det')
            ->flip();
        // dd($lockedIds, $details->pluck('id'));
        $details = $details->map(function ($item) use ($lockedIds) {
            $item->is_locked = isset($lockedIds[$item->id]);
            return $item;
        });

        return response()->json([
            'header' => $header,
            'details' => $details
        ]);
    }

    public function getDetailMaterialSo($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        // Ambil SO dan pastikan punya BOM
        $so = $mysql_sb->table('so')->where('id', $id)->first();
        if (!$so || !$so->id_bom) return response()->json(['data' => []]);

        // 1. TAMBAHKAN TARIKAN PRODUCT SET DARI SO
        $so_details = $mysql_sb->table('so_det as sd')
            ->leftJoin('master_colors_gmt as c', 'sd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'sd.id_size', '=', 's.id')
            ->where('sd.id_so', $id)
            ->select(
                'sd.id_color',
                'sd.id_size',
                'sd.product_set',
                'c.name as color_name',
                's.size as size_name',
                $mysql_sb->raw('SUM(sd.qty) as qty')
            )
            ->groupBy('sd.id_color', 'sd.id_size', 'sd.product_set', 'c.name', 's.size')
            ->get();

        // 2. TAMBAHKAN JOIN KE MASTER SET UNTUK BOM
        $bom_details_raw = $mysql_sb->table('bom_marketing_detail as d')
            ->join('bom_marketing as h', 'd.id_bom_marketing', '=', 'h.id')
            ->leftJoin('masteritem as i', 'd.id_item', '=', 'i.id_item')
            ->leftJoin('mastercontents as e', 'd.id_contents', '=', 'e.id')
            ->leftJoin('mastertype2 as d2', 'e.id_type', '=', 'd2.id')
            ->leftJoin('mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
            ->leftJoin('mastergroup as a', 's_grp.id_group', '=', 'a.id')
            ->leftJoin('masterpilihan as u', 'd.unit', '=', 'u.id')
            ->leftJoin('master_set as mset', 'd.id_set', '=', 'mset.id')
            ->where('d.id_bom_marketing', $so->id_bom)
            ->where('d.cancel','N')
            ->select(
                'd.id as detail_id',
                'd.id_item',
                'd.id_contents',
                'd.id_color as bom_id_color',
                'd.id_size as bom_id_size',
                'd.id_set',
                'mset.nama as bom_product_set',
                'd.rule_bom',
                $mysql_sb->raw("CONCAT(COALESCE(a.nama_group,''), ' ', COALESCE(s_grp.nama_sub_group,''), ' ', COALESCE(d2.nama_type,''), ' ', COALESCE(e.nama_contents,'')) as panel"),
                'h.market as dest',
                'i.itemdesc as item_desc',
                'd.qty as cons',
                'd.unit as unit',
                'd.notes'
            )
            ->get();

        // Mencegah duplikasi data BOM (Tambahkan filter id_set biar All Color All Size gak ketimpa)
        $bom_details = $bom_details_raw->unique(function ($item) {
            if ($item->rule_bom == 'All Color All Size') {
                return $item->id_item . '-' . $item->id_contents . '-' . $item->id_set;
            }
            return $item->detail_id;
        });

        // MAPPING
        $final_data = [];

        foreach ($bom_details as $bom) {
            foreach ($so_details as $sdet) {
                $is_match = false;

                // A. Cek pencocokan Rule Warna dan Size
                if ($bom->rule_bom == 'All Color All Size') {
                    $is_match = true;
                } elseif ($bom->rule_bom == 'Per Color All Size' && $sdet->id_color == $bom->bom_id_color) {
                    $is_match = true;
                } elseif ($bom->rule_bom == 'All Color Range Size' && $sdet->id_size == $bom->bom_id_size) {
                    $is_match = true;
                } elseif ($sdet->id_color == $bom->bom_id_color && $sdet->id_size == $bom->bom_id_size) {
                    $is_match = true; // Per Color Per Size
                }

                // B. Cek Pencocokan Product Set (TOP/BOTTOM)
                $bom_set = strtoupper(trim($bom->bom_product_set ?? ''));
                $so_set  = strtoupper(trim($sdet->product_set ?? ''));

                if ($bom_set !== '' && $bom_set !== $so_set) {
                    $is_match = false; // Batal match kalau beda Set
                }

                // Jika cocok, buat baris barunya
                if ($is_match) {
                    $final_data[] = [
                        'id_item'     => $bom->id_item,
                        'id_contents' => $bom->id_contents,
                        'panel'       => $bom->panel,
                        'dest'        => $bom->dest,
                        'product_set' => strtoupper($sdet->product_set ?: '-'),
                        'color_gmt'   => $sdet->color_name ?? '-',
                        'size_gmt'    => $sdet->size_name ?? '-',
                        'item_desc'   => $bom->item_desc,
                        'qty_gmt'     => (float) $sdet->qty,
                        'cons'        => (float) $bom->cons,
                        'qty_bom'     => (float) ($sdet->qty * $bom->cons),
                        'unit'        => $bom->unit,
                        'notes'       => $bom->notes,
                        'created_by'  => '-',
                        'rule_bom'    => $bom->rule_bom,
                        'status'      => '-'
                    ];
                }
            }
        }

        return response()->json(['data' => $final_data]);
    }

    public function updateBomHeader(Request $request)
    {
        DB::connection('mysql_sb')->table('bom_marketing')->where('id', $request->id_bom)->update([
            'colors' => $request->has('colors') ? json_encode($request->colors) : null,
            'sizes' => $request->has('sizes') ? json_encode($request->sizes) : null,
            'updated_at' => now()
        ]);
        return response()->json(['status' => 200, 'message' => 'Data Berhasil di Update!']);
    }

    public function storeMasterColorQuick(Request $request)
    {
        $name = strtoupper($request->color_name);
        $db = DB::connection('mysql_sb');
        $cek = $db->table('master_colors_gmt')->where('name', $name)->first();
        if($cek) return response()->json(['id' => $cek->id, 'name' => $cek->name]);

        $id = $db->table('master_colors_gmt')->insertGetId(['name' => $name, 'created_at' => now()]);
        return response()->json(['id' => $id, 'name' => $name]);
    }

    public function storeMasterSizeQuick(Request $request)
    {
        $name = strtoupper($request->size_name);
        $db = DB::connection('mysql_sb');
        $cek = $db->table('master_size_new')->where('size', $name)->first();
        if($cek) return response()->json(['id' => $cek->id, 'name' => $cek->size]);

        $last = $db->table('master_size_new')->max('urutan') ?? 0;
        $id = $db->table('master_size_new')->insertGetId(['size' => $name, 'urutan' => $last + 1]);
        return response()->json(['id' => $id, 'name' => $name]);
    }

    public function getItems(Request $request, $id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        if ($request->ajax()) {
            $data = $mysql_sb->table('bom_marketing_detail as d')
                ->leftJoin('masteritem as i', 'd.id_item', '=', 'i.id_item')
                ->leftJoin('master_colors_gmt as c', 'd.id_color', '=', 'c.id')
                ->leftJoin('master_size_new as s', 'd.id_size', '=', 's.id')
                ->leftJoin('mastercontents as e', 'd.id_contents', '=', 'e.id')
                ->leftJoin('mastertype2 as d2', 'e.id_type', '=', 'd2.id')
                ->leftJoin('mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
                ->leftJoin('mastergroup as a', 's_grp.id_group', '=', 'a.id')
                ->leftJoin('mastercf as mfg', 'd.id_contents', '=', 'mfg.id')
                ->leftJoin('masterpilihan as u', 'd.unit', '=', 'u.id')
                ->leftJoin('masterpilihan as cur', 'd.id_currency', '=', 'cur.id')
                ->select(
                    'd.*',
                    $mysql_sb->raw("
                        CASE
                            WHEN d.category = 'Manufacturing'
                            THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                            ELSE i.itemdesc
                        END as item_name
                    "),
                    $mysql_sb->raw("
                        CASE
                            WHEN d.category = 'Manufacturing'
                            THEN CONCAT(mfg.cfcode, ' ', mfg.cfdesc)
                            ELSE CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents)
                        END as content_name
                    "),
                    'c.name as color_name',
                    's.size as size_name',
                    'u.nama_pilihan as unit_name',
                    'cur.nama_pilihan as currency'
                )
                ->where('d.id_bom_marketing', $id)
                ->where('d.cancel', 'N')
                ->orderBy('a.root_group', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('item_name', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing'
                                THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                                ELSE i.itemdesc
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('content_name', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing'
                                THEN CONCAT(mfg.cfcode, ' ', mfg.cfdesc)
                                ELSE CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents)
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('color_name', function($query, $keyword) {
                    $query->where('c.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('size_name', function($query, $keyword) {
                    $query->where('s.size', 'like', "%{$keyword}%");
                })
                ->filterColumn('currency', function($query, $keyword) {
                    $query->where('cur.nama_pilihan', 'like', "%{$keyword}%");
                })
                ->filterColumn('unit_name', function($query, $keyword) {
                    $query->where('u.nama_pilihan', 'like', "%{$keyword}%");
                })
                ->make(true);
        }
    }

    public function getRuleBom(Request $request)
    {
        $rules = DB::connection('mysql_sb')->table('masterpilihan')
            ->where('kode_pilihan', 'Rule_BOM')
            ->select('nama_pilihan as isi', 'nama_pilihan as tampil')->get();

        $html = "<option value=''>Pilih Rule BOM</option>";
        foreach ($rules as $rule) {
            $html .= "<option value='{$rule->isi}'>{$rule->tampil}</option>";
        }
        return response()->json($html);
    }

    public function getItemContents(Request $request)
    {
        $kategori = $request->kategori;
        $html = "<option value=''>Pilih Item Contents</option>";

        if (empty($kategori)) {
            return response()->json($html);
        }

        $mysql_sb = DB::connection('mysql_sb');

        if ($kategori == 'Material') {
            $items = $mysql_sb->table('mastergroup as a')
                ->join('mastersubgroup as s', 'a.id', '=', 's.id_group')
                ->join('mastertype2 as d', 's.id', '=', 'd.id_sub_group')
                ->join('mastercontents as e', 'd.id', '=', 'e.id_type')
                ->select('e.id as isi', $mysql_sb->raw("CONCAT(e.id,' ',a.nama_group,' ',s.nama_sub_group,' ',d.nama_type,' ',e.nama_contents) as tampil"))
                ->get();

            foreach ($items as $item) {
                $html .= "<option value='{$item->isi}'>{$item->tampil}</option>";
            }

        } else {
            $items = $mysql_sb->table('mastercf')
                ->select('id as isi', $mysql_sb->raw("CONCAT(cfcode,' ',cfdesc) as tampil"))
                ->orderBy('id', 'DESC')
                ->get();

            foreach ($items as $item){
                $html .= "<option value='{$item->isi}'>{$item->tampil}</option>";
            }
        }

        return response()->json($html);
    }

    public function getListData(Request $request)
    {
        $id_contents = $request->id_contents;
        $category = $request->category;
        $mysql_sb = DB::connection('mysql_sb');

        if ($category == 'Manufacturing') {
            $master_items = $mysql_sb->table('masteritem as a')
                ->join('mastercf as s', 'a.matclass', '=', 's.cfdesc')
                ->where('a.mattype', 'C')
                ->where('s.id', $id_contents)
                ->select(
                    'a.id_item as isi',
                    DB::raw("CONCAT(a.itemdesc, ' ', a.color, ' ', a.size, ' ', a.add_info) as tampil")
                )
                ->orderBy('a.id_item', 'DESC')
                ->get();
        } else {
            $master_items = $mysql_sb->table('masteritem as a')
                ->join('masterdesc as b', 'a.id_gen', '=', 'b.id')
                ->join('mastercolor as c', 'b.id_color', '=', 'c.id')
                ->join('masterweight as d', 'c.id_weight', '=', 'd.id')
                ->join('masterlength as e', 'd.id_length', '=', 'e.id')
                ->join('masterwidth as f', 'e.id_width', '=', 'f.id')
                ->join('mastercontents as g', 'f.id_contents', '=', 'g.id')
                ->where('g.id', $id_contents)
                ->select(
                    'a.id_item as isi',
                    DB::raw("CONCAT(a.id_item, ' - ', a.itemdesc) as tampil")
                )
                ->groupBy('a.id_gen')
                ->get();
        }

        return response()->json(['items' => $master_items]);
    }

    public function storeDetail(Request $request)
    {
        $id_bom = $request->id_bom_marketing;
        $items = $request->id_item;
        $qtys = $request->qty_input;
        $prices = $request->price_input;
        $color_ids = $request->color_id_row;
        $size_ids = $request->size_id_row;
        $insertData = [];

        if ($items) {
            foreach ($items as $idx => $id_item) {

                $cId = (isset($color_ids[$idx]) && $color_ids[$idx] != '' && $color_ids[$idx] != 'null') ? $color_ids[$idx] : null;
                $sId = (isset($size_ids[$idx]) && $size_ids[$idx] != '' && $size_ids[$idx] != 'null') ? $size_ids[$idx] : null;

                $isAlreadyExist = DB::connection('mysql_sb')->table('bom_marketing_detail')
                    ->where('id_bom_marketing', $id_bom)
                    ->where('id_contents', $request->item_contents)
                    ->where('id_color', $cId)
                    ->where('id_size', $sId)
                    ->where('cancel', 'N')
                    ->exists();

                if (!$isAlreadyExist && $id_item != '') {
                    $insertData[] = [
                        'id_bom_marketing' => $id_bom,
                        'id_supplier'      => $request->id_supplier,
                        'category'         => $request->category,
                        'id_contents'      => $request->item_contents,
                        'rule_bom'         => $request->rule_bom,
                        'id_item'          => $id_item,
                        'id_color'         => $cId,
                        'id_size'          => $sId,
                        'qty'              => $qtys[$idx],
                        'price'            => $prices[$idx],
                        'unit'             => $request->unit,
                        'shell'            => $request->shell,
                        'notes'            => $request->notes,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                }
            }
        }

        if (count($insertData) > 0) {
            DB::connection('mysql_sb')->table('bom_marketing_detail')->insert($insertData);
            return response()->json(['status' => 200, 'message' => 'Data berhasil ditambahkan!']);
        }

        return response()->json(['status' => 200, 'message' => 'Tidak ada material baru yang ditambahkan (Semua sudah terisi).']);
        }

    public function addSoDetailRow(Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            $id_so = $request->id_so;
            $id_colors = $request->id_color;
            $id_sizes = $request->id_size;
            $qty = $request->qty;

            if (!$id_so || empty($id_colors) || empty($id_sizes) || !$qty) {
                return response()->json(['status' => 400, 'message' => 'Data tidak lengkap! Harap pilih minimal 1 Warna dan 1 Size.']);
            }


            if (!is_array($id_colors)) $id_colors = [$id_colors];
            if (!is_array($id_sizes)) $id_sizes = [$id_sizes];

            $inserted_count = 0;
            $skipped_count = 0;


            $existing_row = DB::connection('mysql_sb')->table('so_det')->where('id_so', $id_so)->first();

            foreach ($id_colors as $id_color) {
                foreach ($id_sizes as $id_size) {


                    $exists = DB::connection('mysql_sb')->table('so_det')
                        ->where('id_so', $id_so)
                        ->where('id_color', $id_color)
                        ->where('id_size', $id_size)
                        ->where('cancel', 'N')
                        ->first();

                    if ($exists) {
                        $skipped_count++;
                        continue;
                    }


                    $color_name = DB::connection('mysql_sb')->table('master_colors_gmt')->where('id', $id_color)->value('name');
                    $size_name = DB::connection('mysql_sb')->table('master_size_new')->where('id', $id_size)->value('size');

                    $id_inserted = DB::connection('mysql_sb')->table('so_det')->insertGetId([
                        'id_so'       => $id_so,
                        'id_color'    => $id_color,
                        'id_size'     => $id_size,
                        'color'       => $color_name ?? '-',
                        'size'        => $size_name ?? '-',
                        'qty'         => $qty,
                        'market'      => $existing_row ? $existing_row->market : '-',
                        'cancel'      => 'N',
                        'dest'        => $existing_row ? $existing_row->dest : '-',
                        'product_set' => $existing_row ? $existing_row->product_set : '-',
                        'barcode'     => $existing_row ? $existing_row->barcode : '-',
                        'reff_no'     => $existing_row ? $existing_row->reff_no : null,
                        'styleno_prod'=> $existing_row ? $existing_row->styleno_prod : null,
                        'price'       => $existing_row ? $existing_row->price : 0,
                        'deldate_det' => $existing_row ? $existing_row->deldate_det : null,
                        'created_by'  => auth()->user() ? auth()->user()->name : '',
                        'created_date'=> now(),
                        'updated_by'  => auth()->user() ? auth()->user()->name : '',
                        'updated_date'=> now()
                    ]);

                    $this->logSoDet($id_inserted);

                    $inserted_count++;
                }
            }

            if ($inserted_count === 0 && $skipped_count > 0) {
                DB::connection('mysql_sb')->rollBack();
                return response()->json(['status' => 400, 'message' => 'Semua kombinasi warna dan size tersebut sudah ada di SO ini. Silakan edit Qty-nya saja di tabel atas.']);
            }

            $totalQty = DB::connection('mysql_sb')->table('so_det')
                ->where('id_so', $id_so)
                ->where(function($query) {
                    $query->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
                })
                ->sum('qty');

            DB::connection('mysql_sb')->table('so')
                ->where('id', $id_so)
                ->update(['qty' => $totalQty]);

            $this->logSo($id_so);

            DB::connection('mysql_sb')->commit();
            return response()->json(['status' => 200, 'message' => "$inserted_count kombinasi baru berhasil ditambahkan!"]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            return response()->json(['status' => 500, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }
    public function updateQtySo(Request $request)
    {
        $newStyle = $request->input('style');
        $data = $request->input('data');


        if (!$data || !is_array($data)) {
            return response()->json(['status' => 400, 'message' => 'Data tidak valid!']);
        }

        DB::connection('mysql_sb')->beginTransaction();
        try {
            $firstDetailId = $data[0]['id'];
            $detailData = DB::connection('mysql_sb')->table('so_det')->where('id', $firstDetailId)->first();
            $so_id = $detailData->id_so;

            $allIds = collect($data)->pluck('id');
            $lockedIds = DB::table('ppic_master_so')
                ->whereIn('id_so_det', $allIds)
                ->pluck('id_so_det')
                ->flip();

            foreach ($data as $item) {
                $updateFields = ['qty' => $item['qty'], 'fob' => $item['fob']];

                if (array_key_exists('dest', $item) && !isset($lockedIds[$item['id']])) {
                    $updateFields['dest'] = $item['dest'];
                }

                DB::connection('mysql_sb')->table('so_det')
                    ->where('id', $item['id'])
                    ->update($updateFields);

                $this->logSoDet($item['id']);
            }

            $totalQty = DB::connection('mysql_sb')->table('so_det')
                ->where('id_so', $so_id)
                ->where(function($query) {
                    $query->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
                })
                ->sum('qty');

            $updateData = ['qty' => $totalQty];

            if ($newStyle !== null) {
                $updateData['style'] = $newStyle;
            }

            DB::connection('mysql_sb')->table('so')
                ->where('id', $so_id)
                ->update($updateData);

            $this->logSo($so_id);

            DB::connection('mysql_sb')->commit();

            return response()->json([
                'status' => 200,
                'message' => 'Style dan Semua Qty berhasil di Update'
            ]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Gagal mengupdate: ' . $e->getMessage()
            ], 500);
        }
    }


    public function cancelRestoreSO(Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            $detail_id = $request->id;
            $action = $request->action;
            $cancel_value = ($action === 'cancel') ? 'Y' : 'N';

            DB::connection('mysql_sb')->table('so_det')
                ->where('id', $detail_id)
                ->update([
                    'cancel' => $cancel_value
                ]);

            $this->logSoDet($detail_id);

            $detailData = DB::connection('mysql_sb')->table('so_det')->where('id', $detail_id)->first();
            $so_id = $detailData->id_so;

            $totalQty = DB::connection('mysql_sb')->table('so_det')
                ->where('id_so', $so_id)
                ->where(function($query) {
                    $query->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
                })
                ->sum('qty');

            DB::connection('mysql_sb')->table('so')
                ->where('id', $so_id)
                ->update([
                    'qty' => $totalQty
                ]);

            $this->logSo($so_id);

            DB::connection('mysql_sb')->commit();

            $action_text = ($action === 'cancel') ? 'dibatalkan' : 'direstore';
            return response()->json(['status' => 200, 'message' => "Item berhasil $action_text!"]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            return response()->json(['status' => 500, 'message' => 'Gagal memproses data: ' . $e->getMessage()]);
        }
    }

    public function printPdfSO(Request $request, $id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $header = $mysql_sb->table('so')
                ->select(
                    'so.*',
                    'so.so_no as kpno',
                    'so.style',
                    'ms.Supplier as buyer',
                    'mp.product_group',
                    'mp.product_item',
                    'acn.foto as costing_image',
                    'ac.kpno as ws'
                )
                ->leftJoin('bom_marketing as bm', 'so.id_bom', '=', 'bm.id')
                ->leftJoin('mastersupplier as ms', 'bm.id_buyer', '=', 'ms.Id_Supplier')
                ->leftJoin('masterproduct as mp', 'so.id_product', '=', 'mp.id')
                ->leftJoin('act_costing_new as acn', 'bm.id_costing', '=', 'acn.id')
                ->leftJoin('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('so.id', $id)
                ->first();

            if (!$header) return abort(404, 'Data SO tidak ditemukan');

            if (!empty($header->costing_image)) {
                $url = basename($header->costing_image);
                $file_path = public_path('uploads/costing/' . $url);
                if (file_exists($file_path)) {
                    $type = pathinfo($file_path, PATHINFO_EXTENSION);
                    $data_img = file_get_contents($file_path);
                    $header->image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data_img);
                } else {
                    $header->image_base64 = null;
                }
            } else {
                $header->image_base64 = null;
            }

        // 2. Tarik Detail SO
        $details = $mysql_sb->table('so_det')
            ->leftJoin('master_colors_gmt as c', 'so_det.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'so_det.id_size', '=', 's.id')
            ->where('so_det.id_so', $id)
            ->where('cancel', 'N')
            ->select('c.name as color', 's.size as size_name', 's.urutan', 'so_det.qty', 'so_det.deldate_det', 'so_det.id_color', 'so_det.id_size', 'so_det.product_set')
            ->orderBy('so_det.product_set', 'asc')
            ->orderBy('s.urutan', 'asc')
            ->get();

        $header->ex_fty_date = $details->first()->deldate_det ?? '-';

        // 3. Olah Array Tabel QTY (Group by Warna + Product Set)
        $list_size = [];
        $item_qty = [];

        foreach ($details as $row) {
            $color = $row->color ?? 'Warna Tidak Sesuai Master';
            $size = $row->size_name ?? 'Size Tidak Sesuai Master';
            $prod_set = (!empty($row->product_set) && $row->product_set !== '-') ? $row->product_set : '-';

            $key = $color . '|' . $prod_set;

            if (!isset($item_qty[$key])) {
                $item_qty[$key] = [
                    'color'       => $color,
                    'product_set' => $prod_set,
                    'sizes'       => []
                ];
            }
            if (!isset($item_qty[$key]['sizes'][$size])) {
                $item_qty[$key]['sizes'][$size] = 0;
            }

            $item_qty[$key]['sizes'][$size] += $row->qty;

            if (!in_array($size, $list_size)) $list_size[] = $size;
        }

        // 4. Tarik Master Group
        $master_groups = $mysql_sb->table('mastergroup')
            ->whereNotNull('root_group')
            ->orderBy('root_group', 'asc')
            ->pluck('nama_group')
            ->toArray();

        $group_names = array_map(function($n) { return strtoupper(trim($n)); }, $master_groups);

        // 5. Tarik BOM (Gabung Material & Manufacturing dalam 1 Query biar ngebut!)
        $all_bom_items = $mysql_sb->table('bom_marketing_detail as bmd')
            ->select(
                'bmd.category', 'mg.nama_group', 'c.name as color_gmt', 'bmd.id_color', 'bmd.id_size', 'bmd.id_set',
                'mpnl.nama_panel as shell', 'bmd.notes as description', 's.size as size_gmt', 'i.color as color_item', 'i.size as size_item',
                'bmd.qty as cons', 'bmd.unit', 'mset.nama as product_set',
                DB::raw("
                    CASE
                        WHEN bmd.category = 'Manufacturing'
                        THEN CONCAT(IFNULL(i.itemdesc,''), ' ', IFNULL(i.color,''), ' ', IFNULL(i.size,''), ' ', IFNULL(i.add_info,''))
                        ELSE i.itemdesc
                    END as item_name
                ")
            )
            ->leftJoin('masteritem as i', 'bmd.id_item', '=', 'i.id_item')
            ->leftJoin('mastercontents as mc', 'bmd.id_contents', '=', 'mc.id')
            ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
            ->leftJoin('mastersubgroup as msg', 'mt.id_sub_group', '=', 'msg.id')
            ->leftJoin('mastergroup as mg', 'msg.id_group', '=', 'mg.id')
            ->leftJoin('master_colors_gmt as c', 'bmd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'bmd.id_size', '=', 's.id')
            ->leftJoin('masterpilihan as u', 'bmd.unit', '=', 'u.id')
            ->leftJoin('master_set as mset', 'bmd.id_set', '=', 'mset.id')
            ->leftJoin('masterpanel as mpnl', 'bmd.shell', '=', 'mpnl.id')
            ->where('bmd.id_bom_marketing', $header->id_bom)
            ->whereNotNull('bmd.id_item')
            ->where('bmd.cancel', 'N')
            ->get();

        // 6. Olah Hitungan Kuantiti Material & Pisahkan per Kategori
        $materials_by_group = [];
        $materials_mfg = [];
        $detail_collection = collect($details);

        foreach ($all_bom_items as $mat) {
            // Filter kuantiti sesuai syarat material (Warna, Size, Product Set)
            $query = $detail_collection;
            if ($mat->id_color) $query = $query->where('id_color', $mat->id_color);
            if ($mat->id_size)  $query = $query->where('id_size', $mat->id_size);
            if ($mat->id_set)   $query = $query->where('product_set', $mat->product_set);

            $mat_qty = $query->sum('qty');

            if ($mat_qty <= 0) continue; // Skip kalau bahan ini nggak kepakai di SO ini

            $mat->qty = $mat_qty;
            $mat->cons = (float) ($mat->cons ?? 0);
            $c_name = strtoupper(trim($mat->color_gmt ?? '-'));

            // Pisahkan masuk ke Array Material atau Manufacturing
            if ($mat->category === 'Material' && in_array($mat->nama_group, $master_groups)) {
                $g_name = strtoupper(trim($mat->nama_group));
                $materials_by_group[$g_name][$c_name][] = $mat;
            } elseif ($mat->category === 'Manufacturing') {
                $materials_mfg[$c_name][] = $mat;
            }
        }

        foreach ($materials_by_group as $g_name => $color_groups) {
            $is_fabric = str_contains($g_name, 'FABRIC');
            foreach ($color_groups as $c_name => $items) {
                $merged = [];
                foreach ($items as $item) {
                    if ($is_fabric) {
                        $key = implode('|', [
                            trim($item->shell ?? ''),
                            trim($item->item_name ?? ''),
                            trim($item->description ?? ''),
                            trim($item->color_item ?? ''),
                            (string)(float)($item->cons ?? 0),
                            trim($item->unit ?? ''),
                            trim($item->product_set ?? '')
                        ]);
                    } else {
                        $key = implode('|', [
                            trim($item->item_name ?? ''),
                            trim($item->description ?? ''),
                            trim($item->color_item ?? ''),
                            trim($item->size_item ?? ''),
                            (string)(float)($item->cons ?? 0),
                            trim($item->unit ?? ''),
                            trim($item->product_set ?? '')
                        ]);
                    }

                    if (!isset($merged[$key])) {
                        $merged[$key] = clone $item;
                        if ($is_fabric) {
                            $merged[$key]->size_gmt_array = [];
                            if (!empty($item->size_gmt) && trim($item->size_gmt) !== '-') {
                                $merged[$key]->size_gmt_array[] = trim($item->size_gmt);
                            }
                        }
                    } else {
                        $merged[$key]->qty += $item->qty;
                        if ($is_fabric) {
                            $clean_size_gmt = trim($item->size_gmt ?? '');
                            if (!empty($clean_size_gmt) && $clean_size_gmt !== '-' && !in_array($clean_size_gmt, $merged[$key]->size_gmt_array)) {
                                $merged[$key]->size_gmt_array[] = $clean_size_gmt;
                            }
                        }
                    }
                }

                if ($is_fabric) {
                    foreach ($merged as $k => $m) {
                        if (count($m->size_gmt_array) > 0) {
                            $m->size_gmt = implode(', ', $m->size_gmt_array);
                        } else {
                            $m->size_gmt = 'ALL SIZE';
                        }
                        unset($m->size_gmt_array);
                    }
                }

                $materials_by_group[$g_name][$c_name] = array_values($merged);
            }
        }

        foreach ($materials_mfg as $c_name => $items) {
            $merged = [];
            foreach ($items as $item) {
                $key = implode('|', [
                    trim($item->item_name ?? ''),
                    trim($item->description ?? ''),
                    trim($item->color_item ?? ''),
                    trim($item->size_item ?? ''),
                    (string)(float)($item->cons ?? 0),
                    trim($item->unit ?? '')
                ]);

                if (!isset($merged[$key])) {
                    $merged[$key] = clone $item;
                } else {
                    $merged[$key]->qty += $item->qty;
                }
            }
            $materials_mfg[$c_name] = array_values($merged);
        }

        // Sorting Huruf Abjad
        foreach ($materials_by_group as $g_name => $color_groups) {
            ksort($color_groups);
            $materials_by_group[$g_name] = $color_groups;
        }
        ksort($materials_mfg);


        $view_data = [
            'header'        => $header,
            'details'       => array_values($item_qty), // Reset index biar gampang di loop di Blade
            'sizes'         => $list_size,
            'materials'     => $materials_by_group,
            'materials_mfg' => $materials_mfg,
            'group_names'   => $group_names
        ];

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'courier']);
        $pdf = PDF::loadView('marketing.so.pdf_ws', $view_data)->setPaper('a4', 'landscape');

        $fileName = 'Worksheet-SO-' . ($header->so_no ?? $id) . '.pdf';
        return $pdf->stream(str_replace("/", "_", $fileName));
    }

    public function exportExcelSO($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $header = $mysql_sb->table('so')
            ->leftJoin('act_costing as ac', 'so.id_cost', '=', 'ac.id')
            ->leftJoin('mastersupplier as ms', 'ac.id_buyer', '=', 'ms.Id_Supplier')
            ->select('so.so_no', 'so.no_po', 'so.style', 'so.market', 'ac.kpno', 'ms.Supplier as buyer')
            ->where('so.id', $id)
            ->first();

        if (!$header) {
            abort(404, 'Data SO tidak ditemukan.');
        }

        $details = $mysql_sb->table('so_det as sd')
            ->leftJoin('so_destination as d', fn ($join) => $join->on('d.id_so_det', '=', 'sd.id')->where('d.cancel', 'N'))
            ->leftJoin('master_colors_gmt as c', 'sd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'sd.id_size', '=', 's.id')
            ->select(
                'c.name as color',
                DB::raw("COALESCE(d.destination, NULLIF(sd.dest, '-')) as dest"),
                's.size as size',
                'sd.product_set',
                DB::raw('COALESCE(d.qty, sd.qty) as qty'),
                'sd.deldate_det'
            )
            ->where('sd.id_so', $id)
            ->where('sd.cancel', 'N')
            ->whereRaw('(d.id IS NOT NULL OR NOT EXISTS (SELECT 1 FROM so_destination x WHERE x.id_so_det = sd.id))')
            ->orderBy('sd.product_set')
            ->orderBy('c.name')
            ->orderBy('s.urutan')
            ->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Order');
        $sheet->fromArray([
            ['Sales Order', $header->so_no],
            ['No. PO', $header->no_po],
            ['No. Worksheet', $header->kpno],
            ['Buyer', $header->buyer],
            ['Style', $header->style],
            ['Market', $header->market],
            [],
            ['Color', 'Destination', 'Size', 'Product Set', 'Qty', 'Ex FTY']
        ]);

        $row = 9;
        foreach ($details as $detail) {
            $sheet->fromArray([
                $detail->color,
                $detail->dest,
                $detail->size,
                $detail->product_set,
                $detail->qty,
                $detail->deldate_det
            ], null, 'A' . $row++);
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'SO_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $header->so_no) . '.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // public function printPdfSO(Request $request, $id)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');

    //     $header = $mysql_sb->table('so')
    //             ->select(
    //                 'so.*',
    //                 'so.so_no as kpno',
    //                 'so.style',
    //                 'ms.Supplier as buyer',
    //                 'mp.product_group',
    //                 'mp.product_item',
    //                 'acn.foto as costing_image',
    //                 'ac.kpno as ws'
    //             )
    //             ->leftJoin('bom_marketing as bm', 'so.id_bom', '=', 'bm.id')
    //             ->leftJoin('mastersupplier as ms', 'bm.id_buyer', '=', 'ms.Id_Supplier')
    //             ->leftJoin('masterproduct as mp', 'so.id_product', '=', 'mp.id')
    //             ->leftJoin('act_costing_new as acn', 'bm.id_costing', '=', 'acn.id')
    //             ->leftJoin('act_costing as ac', 'so.id_cost', '=', 'ac.id')
    //             ->where('so.id', $id)
    //             ->first();

    //         if (!$header) return abort(404, 'Data SO tidak ditemukan');

    //         if (!empty($header->costing_image)) {
    //             $url = basename($header->costing_image);
    //             $file_path = public_path('uploads/costing/' . $url);
    //             if (file_exists($file_path)) {
    //                 $type = pathinfo($file_path, PATHINFO_EXTENSION);
    //                 $data_img = file_get_contents($file_path);
    //                 $header->image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data_img);
    //             } else {
    //                 $header->image_base64 = null;
    //             }
    //         } else {
    //             $header->image_base64 = null;
    //         }

    //     // 2. Tarik Detail SO
    //     $details = $mysql_sb->table('so_det')
    //         ->leftJoin('master_colors_gmt as c', 'so_det.id_color', '=', 'c.id')
    //         ->leftJoin('master_size_new as s', 'so_det.id_size', '=', 's.id')
    //         ->where('so_det.id_so', $id)
    //         ->select('c.name as color', 's.size as size_name', 's.urutan', 'so_det.qty', 'so_det.deldate_det', 'so_det.id_color', 'so_det.id_size', 'so_det.product_set')
    //         ->orderBy('so_det.product_set', 'asc')
    //         ->orderBy('s.urutan', 'asc')
    //         ->get();

    //     $header->ex_fty_date = $details->first()->deldate_det ?? '-';

    //     // 3. Olah Array Tabel QTY (Group by Warna + Product Set)
    //     $list_size = [];
    //     $item_qty = [];

    //     foreach ($details as $row) {
    //         $color = $row->color ?? 'Warna Tidak Sesuai Master';
    //         $size = $row->size_name ?? 'Size Tidak Sesuai Master';
    //         $prod_set = (!empty($row->product_set) && $row->product_set !== '-') ? $row->product_set : '-';

    //         $key = $color . '|' . $prod_set;

    //         if (!isset($item_qty[$key])) {
    //             $item_qty[$key] = [
    //                 'color'       => $color,
    //                 'product_set' => $prod_set,
    //                 'sizes'       => []
    //             ];
    //         }
    //         if (!isset($item_qty[$key]['sizes'][$size])) {
    //             $item_qty[$key]['sizes'][$size] = 0;
    //         }

    //         $item_qty[$key]['sizes'][$size] += $row->qty;

    //         if (!in_array($size, $list_size)) $list_size[] = $size;
    //     }

    //     // 4. Tarik Master Group
    //     $master_groups = $mysql_sb->table('mastergroup')
    //         ->whereNotNull('root_group')
    //         ->orderBy('root_group', 'asc')
    //         ->pluck('nama_group')
    //         ->toArray();

    //     $group_names = array_map(function($n) { return strtoupper(trim($n)); }, $master_groups);

    //     // 5. Tarik BOM (Gabung Material & Manufacturing dalam 1 Query biar ngebut!)
    //     $all_bom_items = $mysql_sb->table('bom_marketing_detail as bmd')
    //         ->select(
    //             'bmd.category', 'mg.nama_group', 'c.name as color_gmt', 'bmd.id_color', 'bmd.id_size', 'bmd.id_set',
    //             'bmd.shell', 'bmd.notes as description', 's.size as size_gmt', 'i.color as color_item', 'i.size as size_item',
    //             'bmd.qty as cons', 'bmd.unit', 'mset.nama as product_set',
    //             DB::raw("
    //                 CASE
    //                     WHEN bmd.category = 'Manufacturing'
    //                     THEN CONCAT(IFNULL(i.itemdesc,''), ' ', IFNULL(i.color,''), ' ', IFNULL(i.size,''), ' ', IFNULL(i.add_info,''))
    //                     ELSE i.itemdesc
    //                 END as item_name
    //             ")
    //         )
    //         ->leftJoin('masteritem as i', 'bmd.id_item', '=', 'i.id_item')
    //         ->leftJoin('mastercontents as mc', 'bmd.id_contents', '=', 'mc.id')
    //         ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
    //         ->leftJoin('mastersubgroup as msg', 'mt.id_sub_group', '=', 'msg.id')
    //         ->leftJoin('mastergroup as mg', 'msg.id_group', '=', 'mg.id')
    //         ->leftJoin('master_colors_gmt as c', 'bmd.id_color', '=', 'c.id')
    //         ->leftJoin('master_size_new as s', 'bmd.id_size', '=', 's.id')
    //         ->leftJoin('masterpilihan as u', 'bmd.unit', '=', 'u.id')
    //         ->leftJoin('master_set as mset', 'bmd.id_set', '=', 'mset.id')
    //         ->where('bmd.id_bom_marketing', $header->id_bom)
    //         ->whereNotNull('bmd.id_item')
    //         ->get();

    //     // 6. Olah Hitungan Kuantiti Material & Pisahkan per Kategori
    //     $materials_by_group = [];
    //     $materials_mfg = [];
    //     $detail_collection = collect($details);

    //     foreach ($all_bom_items as $mat) {
    //         // Filter kuantiti sesuai syarat material (Warna, Size, Product Set)
    //         $query = $detail_collection;
    //         if ($mat->id_color) $query = $query->where('id_color', $mat->id_color);
    //         if ($mat->id_size)  $query = $query->where('id_size', $mat->id_size);
    //         if ($mat->id_set)   $query = $query->where('product_set', $mat->product_set);

    //         $mat_qty = $query->sum('qty');

    //         if ($mat_qty <= 0) continue; // Skip kalau bahan ini nggak kepakai di SO ini

    //         $mat->qty = $mat_qty;
    //         $mat->cons = (float) ($mat->cons ?? 0);

    //         // Secara default, ambil nama warna bajunya
    //         $c_name = strtoupper(trim($mat->color_gmt ?? '-'));

    //         // ---------------------------------------------------------
    //         // [MODIFIKASI] PENGELOMPOKAN GLOBAL
    //         // Khusus Manufacturing, Packing, & Sewing -> Jadikan 1 grup global ('-')
    //         // agar QTY-nya tergabung & tidak terbelah-belah per warna baju.
    //         // ---------------------------------------------------------
    //         if ($mat->category === 'Manufacturing') {
    //             $c_name = '-';
    //         } elseif ($mat->category === 'Material') {
    //             $g_name_upper = strtoupper(trim($mat->nama_group ?? ''));
    //             if (str_contains($g_name_upper, 'PACKING') || str_contains($g_name_upper, 'SEWING')) {
    //                 $c_name = '-';
    //             }
    //         }

    //         // Pisahkan masuk ke Array Material atau Manufacturing
    //         if ($mat->category === 'Material' && in_array($mat->nama_group, $master_groups)) {
    //             $g_name = strtoupper(trim($mat->nama_group));
    //             $materials_by_group[$g_name][$c_name][] = $mat;
    //         } elseif ($mat->category === 'Manufacturing') {
    //             $materials_mfg[$c_name][] = $mat;
    //         }
    //     }

    //     foreach ($materials_by_group as $g_name => $color_groups) {
    //         $is_fabric = str_contains($g_name, 'FABRIC');
    //         foreach ($color_groups as $c_name => $items) {
    //             $merged = [];
    //             foreach ($items as $item) {
    //                 if ($is_fabric) {
    //                     $key = implode('|', [
    //                         trim($item->shell ?? ''),
    //                         trim($item->item_name ?? ''),
    //                         trim($item->description ?? ''),
    //                         trim($item->color_item ?? ''),
    //                         (string)(float)($item->cons ?? 0),
    //                         trim($item->unit ?? ''),
    //                         trim($item->product_set ?? '')
    //                     ]);
    //                 } else {
    //                     $key = implode('|', [
    //                         trim($item->item_name ?? ''),
    //                         trim($item->description ?? ''),
    //                         trim($item->color_item ?? ''),
    //                         trim($item->size_item ?? ''),
    //                         (string)(float)($item->cons ?? 0),
    //                         trim($item->unit ?? ''),
    //                         trim($item->product_set ?? '')
    //                     ]);
    //                 }

    //                 if (!isset($merged[$key])) {
    //                     $merged[$key] = clone $item;
    //                     if ($is_fabric) {
    //                         $merged[$key]->size_gmt_array = [];
    //                         if (!empty($item->size_gmt) && trim($item->size_gmt) !== '-') {
    //                             $merged[$key]->size_gmt_array[] = trim($item->size_gmt);
    //                         }
    //                     }
    //                 } else {
    //                     $merged[$key]->qty += $item->qty;
    //                     if ($is_fabric) {
    //                         $clean_size_gmt = trim($item->size_gmt ?? '');
    //                         if (!empty($clean_size_gmt) && $clean_size_gmt !== '-' && !in_array($clean_size_gmt, $merged[$key]->size_gmt_array)) {
    //                             $merged[$key]->size_gmt_array[] = $clean_size_gmt;
    //                         }
    //                     }
    //                 }
    //             }

    //             if ($is_fabric) {
    //                 foreach ($merged as $k => $m) {
    //                     if (count($m->size_gmt_array) > 0) {
    //                         $m->size_gmt = implode(', ', $m->size_gmt_array);
    //                     } else {
    //                         $m->size_gmt = 'ALL SIZE';
    //                     }
    //                     unset($m->size_gmt_array);
    //                 }
    //             }

    //             $materials_by_group[$g_name][$c_name] = array_values($merged);
    //         }
    //     }

    //     foreach ($materials_mfg as $c_name => $items) {
    //         $merged = [];
    //         foreach ($items as $item) {
    //             $key = implode('|', [
    //                 trim($item->item_name ?? ''),
    //                 trim($item->description ?? ''),
    //                 trim($item->color_item ?? ''),
    //                 trim($item->size_item ?? ''),
    //                 (string)(float)($item->cons ?? 0),
    //                 trim($item->unit ?? '')
    //             ]);

    //             if (!isset($merged[$key])) {
    //                 $merged[$key] = clone $item;
    //             } else {
    //                 $merged[$key]->qty += $item->qty;
    //             }
    //         }
    //         $materials_mfg[$c_name] = array_values($merged);
    //     }

    //     // Sorting Huruf Abjad
    //     foreach ($materials_by_group as $g_name => $color_groups) {
    //         ksort($color_groups);
    //         $materials_by_group[$g_name] = $color_groups;
    //     }
    //     ksort($materials_mfg);

    //     $view_data = [
    //         'header'        => $header,
    //         'details'       => array_values($item_qty), // Reset index biar gampang di loop di Blade
    //         'sizes'         => $list_size,
    //         'materials'     => $materials_by_group,
    //         'materials_mfg' => $materials_mfg,
    //         'group_names'   => $group_names
    //     ];

    //     PDF::setOption(['dpi' => 150, 'defaultFont' => 'courier']);
    //     $pdf = PDF::loadView('marketing.so.pdf_ws', $view_data)->setPaper('a4', 'landscape');

    //     $fileName = 'Worksheet-SO-' . ($header->so_no ?? $id) . '.pdf';
    //     return $pdf->stream(str_replace("/", "_", $fileName));
    // }


    public function getBomCostingData(Request $request)
    {
        $id_bom = $request->id_bom;

        if (empty($id_bom)) {
            return response()->json(['status' => 400, 'message' => 'ID BOM Kosong']);
        }

        $db = DB::connection('mysql_sb');

        $data = $db->table('bom_marketing as b')
            ->leftJoin('act_costing_new as c', 'b.id_costing', '=', 'c.id')
            ->leftJoin('mastersupplier as ms', 'c.buyer', '=', 'ms.Id_Supplier')
            ->leftJoin('masterseason as seas', 'c.season_id', '=', 'seas.id_season')
            ->leftJoin('mastershipmode as sm', 'c.ship_mode', '=', 'sm.id')
            ->leftJoin('master_mkt_order as mo', 'c.marketing_order', '=', 'mo.id')
            ->where('b.id', $id_bom)
            ->select(
                'b.id as id_bom',
                'b.no_katalog_bom',
                'c.no_costing',
                'c.buyer as id_buyer',
                'ms.Supplier as buyer_name',
                'c.product_group',
                'c.product_item as nama_product_item',
                'c.brand',
                'c.season_id',
                'seas.season as season_name',
                'seas.season_desc',
                'c.style',
                'c.market',
                'c.marketing_order',
                'mo.mkt_order as marketing_order_name',
                'c.smv',
                'c.curr as id_currency',
                'c.type',
                'c.tipe_ws',
                'c.qty as planned_qty',
                'c.unit',
                'c.ship_mode',
                'sm.shipmode as ship_mode_name',
                'c.status',
                'c.main_dest',
                'c.product_set',
                'c.foto as gambar'
            )
            ->first();
        if ($data) {
           if (!empty($data->gambar)) {
                $url = 'nds_wip/public/uploads/costing/' . $data->gambar;
                $data->image_url = str_replace('index.php/', '', $url);
            } else {
                $data->image_url = null;
            }
            return response()->json([
                'status' => 200,
                'data' => $data
            ]);
        }


        return response()->json(['status' => 404, 'message' => 'Data BOM tidak ditemukan']);
    }

    // public function printPdfSO(Request $request, $id)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');

    //     $header = $mysql_sb->table('so')
    //         ->select('so.*', 'act.kpno', 'act.styleno', 'ms.Supplier as buyer', 'mp.product_group', 'mp.product_item')
    //         ->leftJoin('act_costing as act', 'so.id_cost', '=', 'act.id')
    //         ->leftJoin('mastersupplier as ms', 'act.id_buyer', '=', 'ms.Id_Supplier')
    //         ->leftJoin('masterproduct as mp', 'act.id_product', '=', 'mp.id')
    //         ->where('so.id', $id)
    //         ->first();

    //     if (!$header) {
    //         return abort(404, 'Data SO tidak ditemukan');
    //     }

    //     $detail_data = $mysql_sb->table('so_det')
    //         ->leftJoin('master_colors_gmt as c', 'so_det.id_color', '=', 'c.id')
    //         ->leftJoin('master_size_new as s', 'so_det.id_size', '=', 's.id')
    //         ->where('so_det.id_so', $id)
    //         ->select('c.name as color', 's.size as size_name', 's.urutan', 'so_det.qty', 'so_det.deldate_det', 'so_det.id_color', 'so_det.id_size')
    //         ->orderBy('s.urutan', 'asc')
    //         ->get();

    //     $data = [];
    //     $list_size = [];
    //     $header->ex_fty_date = "";

    //     foreach ($detail_data as $row) {
    //         $header->ex_fty_date = $row->deldate_det;
    //         $color = $row->color ?? 'Warna Tidak Sesuai dengan Master';
    //         $size = $row->size_name ?? 'Warna Tidak Sesuai dengan Master';

    //         if (!isset($data[$color])) {
    //             $data[$color] = [];
    //         }

    //         // Jumlahkan jika ada qty duplikat (PO digabung)
    //         if (!isset($data[$color][$size])) {
    //             $data[$color][$size] = 0;
    //         }
    //         $data[$color][$size] += $row->qty;

    //         if (!in_array($size, $list_size)) {
    //             $list_size[] = $size;
    //         }
    //     }

    //     $bom_materials = $mysql_sb->table('bom_marketing_detail as bmd')
    //         ->select(
    //             'mg.nama_group',
    //             'c.name as color_gmt',
    //             'bmd.id_color',
    //             'bmd.id_size',
    //             'bmd.shell',
    //             'bmd.notes as description',
    //             's.size as size_gmt',
    //             'i.color as color_item',
    //             'i.size as size_item',
    //             'bmd.qty as cons',
    //             'u.nama_pilihan as unit',
    //             DB::raw("
    //                 CASE
    //                     WHEN bmd.category = 'Manufacturing'
    //                     THEN CONCAT(IFNULL(i.itemdesc,''), ' ', IFNULL(i.color,''), ' ', IFNULL(i.size,''), ' ', IFNULL(i.add_info,''))
    //                     ELSE i.itemdesc
    //                 END as item_name
    //             ")
    //         )
    //         ->leftJoin('masteritem as i', 'bmd.id_item', '=', 'i.id_item')
    //         ->leftJoin('mastercontents as mc', 'bmd.id_contents', '=', 'mc.id')
    //         ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
    //         ->leftJoin('mastersubgroup as msg', 'mt.id_sub_group', '=', 'msg.id')
    //         ->leftJoin('mastergroup as mg', 'msg.id_group', '=', 'mg.id')
    //         ->leftJoin('master_colors_gmt as c', 'bmd.id_color', '=', 'c.id')
    //         ->leftJoin('master_size_new as s', 'bmd.id_size', '=', 's.id')
    //         ->leftJoin('masterpilihan as u', 'bmd.unit', '=', 'u.id')
    //         ->where('bmd.id_bom_marketing', $header->id_bom)
    //         ->get();

    //     $total_so_qty = $detail_data->sum('qty');
    //     $materials_by_group = [];
    //     $added_groups = [];

    //     foreach ($bom_materials as $mat) {
    //         $mat_qty = 0;
    //         $has_color = !empty($mat->id_color);
    //         $has_size = !empty($mat->id_size);

    //         // Hitung QTY BOM sesuai rule (All Color/Per Color)
    //         if (!$has_color && !$has_size) {
    //             $mat_qty = $total_so_qty;
    //         } elseif ($has_color && !$has_size) {
    //             $mat_qty = $detail_data->where('id_color', $mat->id_color)->sum('qty');
    //         } elseif (!$has_color && $has_size) {
    //             $mat_qty = $detail_data->where('id_size', $mat->id_size)->sum('qty');
    //         } else {
    //             $mat_qty = $detail_data->where('id_color', $mat->id_color)->where('id_size', $mat->id_size)->sum('qty');
    //         }

    //         $mat->qty = $mat_qty;
    //         $mat->cons = (float) ($mat->cons ?? 0);

    //         $g_name = strtoupper(trim($mat->nama_group ?? 'OTHER'));

    //         // Otomatis tarik semua yang mengandung kata FABRIC atau ACC (tidak peduli salah ketik di database)
    //         if (str_contains($g_name, 'FABRIC') || str_contains($g_name, 'ACC')) {
    //             $materials_by_group[$g_name][] = $mat;
    //             if(!in_array($g_name, $added_groups)) {
    //                 $added_groups[] = $g_name;
    //             }
    //         }
    //     }

    //     usort($added_groups, function($a, $b) {
    //         $scoreA = str_contains($a, 'FABRIC') ? 1 : (str_contains($a, 'SEWING') ? 2 : (str_contains($a, 'PACKING') ? 3 : 4));
    //         $scoreB = str_contains($b, 'FABRIC') ? 1 : (str_contains($b, 'SEWING') ? 2 : (str_contains($b, 'PACKING') ? 3 : 4));
    //         return $scoreA <=> $scoreB;
    //     });

    //     $view_data = [
    //         'header'       => $header,
    //         'details'      => $data,
    //         'sizes'        => $list_size,
    //         'materials'    => $materials_by_group,
    //         'added_groups' => $added_groups // Array ini yang dipakai di Blade
    //     ];

    //     PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica']);
    //     $pdf = PDF::loadView('marketing.so.pdf_ws', $view_data)->setPaper('a4', 'landscape');

    //     $fileName = 'Worksheet-SO-' . ($header->so_no ?? $id) . '.pdf';
    //     return $pdf->stream(str_replace("/", "_", $fileName));
    // }

    // public static function executeSyncBom($id)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');
    //     $mysql_sb->beginTransaction();

    //     try {
    //         $so = $mysql_sb->table('so')->where('id', $id)->first();
    //         if (!$so) {
    //             return ['status' => 400, 'message' => 'SO tidak ditemukan'];
    //         }

    //         if (!$so->id_bom) {
    //             return ['status' => 400, 'message' => 'SO ini tidak memiliki Master BOM (id_bom kosong)'];
    //         }

    //         $jo_det = $mysql_sb->table('jo_det')->where('id_so', $id)->first();
    //         if (!$jo_det) {
    //             return ['status' => 400, 'message' => 'JO tidak ditemukan untuk SO ini'];
    //         }

    //         $id_jo = $jo_det->id_jo;
    //         $id_bom = $so->id_bom;
    //         $username = auth()->check() ? auth()->user()->username : 'system';

    //         // MATRIX EXPANSION: Auto-insert missing colors and sizes into so_det
    //         /*
    //         $base_so_det = $mysql_sb->table('so_det')->where('id_so', $id)->where('cancel', 'N')->first();
    //         if ($base_so_det) {
    //             $bom_colors = $mysql_sb->table('bom_marketing_detail')
    //                 ->where('id_bom_marketing', $id_bom)->whereNotNull('id_color')->pluck('id_color')->toArray();
    //             $bom_sizes = $mysql_sb->table('bom_marketing_detail')
    //                 ->where('id_bom_marketing', $id_bom)->whereNotNull('id_size')->pluck('id_size')->toArray();

    //             // Bersihkan tipe data untuk amannya menggunakan map (string agar konsisten saat array_diff)
    //             $bom_colors = array_unique(array_map('strval', $bom_colors));
    //             $bom_sizes = array_unique(array_map('strval', $bom_sizes));

    //             $so_colors_raw = $mysql_sb->table('so_det')->where('id_so', $id)->where('cancel', 'N')->whereNotNull('id_color')->pluck('id_color')->toArray();
    //             $so_sizes_raw = $mysql_sb->table('so_det')->where('id_so', $id)->where('cancel', 'N')->whereNotNull('id_size')->pluck('id_size')->toArray();

    //             $so_colors = array_unique(array_map('strval', $so_colors_raw));
    //             $so_sizes = array_unique(array_map('strval', $so_sizes_raw));

    //             $new_colors = array_diff($bom_colors, $so_colors);
    //             $new_sizes = array_diff($bom_sizes, $so_sizes);

    //             if (count($new_colors) > 0 || count($new_sizes) > 0) {
    //                 $so_colors_current = $so_colors;

    //                 // Ekspansi Warna Baru
    //                 foreach ($new_colors as $nc) {
    //                     $color_info = $mysql_sb->table('master_colors_gmt')->where('id', $nc)->first();
    //                     foreach ($so_sizes as $sz) {
    //                         $ref = $mysql_sb->table('so_det')->where('id_so', $id)->where('cancel', 'N')->where('id_size', $sz)->first();
    //                         if (!$ref) $ref = $base_so_det;

    //                         $mysql_sb->table('so_det')->insert([
    //                             'id_so' => $id,
    //                             'color' => $color_info ? $color_info->name : '-',
    //                             'id_color' => $nc,
    //                             'size' => $ref->size,
    //                             'id_size' => $sz,
    //                             'qty' => $ref->qty,
    //                             'styleno_prod' => $ref->styleno_prod,
    //                             'reff_no' => $ref->reff_no,
    //                             'price' => $ref->price,
    //                             'deldate_det' => $ref->deldate_det,
    //                             'created_by' => $username,
    //                             'created_date' => now(),
    //                             'cancel' => 'N',
    //                             'unit' => $ref->unit,
    //                             'product_set' => $ref->product_set,
    //                             'dest' => $ref->dest,
    //                         ]);
    //                     }
    //                     $so_colors_current[] = $nc;
    //                 }

    //                 // Ekspansi Size Baru
    //                 foreach ($new_sizes as $ns) {
    //                     $size_info = $mysql_sb->table('master_size_new')->where('id', $ns)->first();
    //                     foreach ($so_colors_current as $nc) {
    //                         $ref = $mysql_sb->table('so_det')->where('id_so', $id)->where('cancel', 'N')->where('id_color', $nc)->first();
    //                         if (!$ref) $ref = $base_so_det;

    //                         $mysql_sb->table('so_det')->insert([
    //                             'id_so' => $id,
    //                             'color' => $ref->color,
    //                             'id_color' => $nc,
    //                             'size' => $size_info ? $size_info->size : '-',
    //                             'id_size' => $ns,
    //                             'qty' => $ref->qty,
    //                             'styleno_prod' => $ref->styleno_prod,
    //                             'reff_no' => $ref->reff_no,
    //                             'price' => $ref->price,
    //                             'deldate_det' => $ref->deldate_det,
    //                             'created_by' => $username,
    //                             'created_date' => now(),
    //                             'cancel' => 'N',
    //                             'unit' => $ref->unit,
    //                             'product_set' => $ref->product_set,
    //                             'dest' => $ref->dest,
    //                         ]);
    //                     }
    //                 }

    //                 // Update SO header total qty
    //                 $total_qty = $mysql_sb->table('so_det')->where('id_so', $id)->where('cancel', 'N')->sum('qty');
    //                 $mysql_sb->table('so')->where('id', $id)->update(['qty' => $total_qty]);
    //             }
    //         }
    //         */

    //         $required_items = $mysql_sb->select("
    //             SELECT
    //                 ? as id_jo,
    //                 sd.id as id_so_det,
    //                 CASE WHEN acd.type = 'Manufacturing' THEN 'P' ELSE 'M' END as status,
    //                 CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE mi.id_gen END as id_item,
    //                 bmd.qty as cons,
    //                 bmd.unit,
    //                 bmd.id_supplier,
    //                 UPPER(bmd.rule_bom) as rule_bom,
    //                 bmd.shell as id_panel,
    //                 mi.itemdesc as notes
    //             FROM so_det sd
    //             INNER JOIN bom_marketing_detail bmd
    //                 ON  bmd.id_bom_marketing = ?
    //                 AND (bmd.id_color IS NULL OR bmd.id_color = sd.id_color)
    //                 AND (bmd.id_size  IS NULL OR bmd.id_size  = sd.id_size)
    //                 AND (bmd.id_set   IS NULL OR bmd.id_set   = (SELECT id FROM master_set WHERE nama = sd.product_set LIMIT 1))
    //             INNER JOIN masteritem mi
    //                 ON mi.id_item = bmd.id_item
    //             LEFT JOIN act_costing_detail_new acd
    //                 ON bmd.id_costing_detail = acd.id
    //             WHERE sd.id_so = ?
    //             AND mi.id_gen IS NOT NULL
    //         ", [$id_jo, $id_bom, $id]);



    //         $existing_items = $mysql_sb->table('bom_jo_item')
    //             ->where('id_jo', $id_jo)
    //             ->where('cancel', 'N')
    //             ->get();

    //         $existing_map = [];
    //         foreach ($existing_items as $item) {
    //             $key = $item->id_so_det . '_' . $item->id_item . '_' . $item->id_panel;
    //             $existing_map[$key] = $item;
    //         }

    //         // Ambil posno yang sudah ada per id_item
    //         $posno_map = [];
    //         foreach ($existing_items as $item) {
    //             if (!isset($posno_map[$item->id_item])) {
    //                 $posno_map[$item->id_item] = $item->posno;
    //             }
    //         }

    //         $current_max_posno = $mysql_sb->table('bom_jo_item')
    //             ->where('id_jo', $id_jo)
    //             ->max('posno');
    //         $posno_counter = (int)$current_max_posno;

    //         $processed_keys = [];
    //         $insert_count = 0;
    //         $update_count = 0;
    //         $cancel_count = 0;
    //         $to_insert = [];

    //         foreach ($required_items as $req) {
    //             $key = $req->id_so_det . '_' . $req->id_item . '_' . $req->id_panel;
    //             \Log::info("REQ key=$key | panel=$req->id_panel | exists=" . (isset($existing_map[$key]) ? 'YES' : 'NO'));
    //             $processed_keys[] = $key;

    //             if (isset($existing_map[$key])) {
    //                 // Sudah ada dan sama persis — skip atau update jika ada perubahan
    //                 $ext = $existing_map[$key];
    //                 if ($ext->cons != $req->cons ||
    //                     $ext->unit != $req->unit ||
    //                     $ext->rule_bom != $req->rule_bom ||
    //                     $ext->cancel == 'Y'
    //                 ) {
    //                     $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update([
    //                         'cons'       => $req->cons,
    //                         'unit'       => $req->unit,
    //                         'rule_bom'   => $req->rule_bom,
    //                         'id_panel'   => $req->id_panel,
    //                         'cancel'     => 'N'
    //                     ]);
    //                     $update_count++;
    //                 }
    //             } else {
    //                 // Gunakan posno yang sama untuk id_item yang sama (berlaku untuk semua size/color/panel dari item tersebut)
    //                 if (!isset($posno_map[$req->id_item])) {
    //                     $posno_counter++;
    //                     $posno_map[$req->id_item] = str_pad($posno_counter, 3, '0', STR_PAD_LEFT);
    //                 }
    //                 $existing_posno = $posno_map[$req->id_item];

    //                 $to_insert[] = [
    //                     'id_jo'      => $req->id_jo,
    //                     'id_so_det'  => $req->id_so_det,
    //                     'status'     => $req->status,
    //                     'id_item'    => $req->id_item,
    //                     'cons'       => $req->cons,
    //                     'unit'       => $req->unit,
    //                     'id_supplier'=> $req->id_supplier,
    //                     'rule_bom'   => $req->rule_bom,
    //                     'cancel'     => 'N',
    //                     'add_item'   => 'N',
    //                     'username'   => $username,
    //                     'dateinput'  => now(),
    //                     'id_panel'   => $req->id_panel,
    //                     'posno'      => $existing_posno,
    //                     'notes'      => null,
    //                 ];
    //                 $insert_count++;
    //             }
    //         }

    //         if (count($to_insert) > 0) {
    //             foreach (array_chunk($to_insert, 500) as $chunk) {
    //                 $mysql_sb->table('bom_jo_item')->insert($chunk);
    //             }
    //         }

    //         // Cancel yang tidak ada di required_items
    //         foreach ($existing_map as $key => $ext) {
    //             if (!in_array($key, $processed_keys)) {
    //                 \Log::info("AKAN DICANCEL: key=$key | id=$ext->id | id_panel=$ext->id_panel");
    //                 if ($ext->cancel != 'Y') {
    //                     $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update(['cancel' => 'Y']);
    //                     $cancel_count++;
    //                 }
    //             }
    //         }

    //         // 5. Sync Costing Native (act_costing_mat, mfg, oth)
    //         $bom = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
    //         if ($bom && $so->id_cost) {
    //             $act_costing_new = $mysql_sb->table('act_costing_new')->where('id', $bom->id_costing)->first();
    //             if ($act_costing_new) {
    //                 $mat_details = $mysql_sb->table('act_costing_detail_new')
    //                     ->where('id_costing', $act_costing_new->id)
    //                     ->whereIn('type', ['Fabric', 'Accessories Sewing', 'Accessories Packing'])
    //                     ->get();

    //                 $mfg_details = $mysql_sb->table('act_costing_detail_new')
    //                     ->where('id_costing', $act_costing_new->id)
    //                     ->where('type', 'Manufacturing')
    //                     ->get();

    //                 $oth_details = $mysql_sb->table('act_costing_detail_new')
    //                     ->where('id_costing', $act_costing_new->id)
    //                     ->where('type', 'Other Cost')
    //                     ->get();

    //                 $jenis_rate_val = 'B';
    //                 if ($act_costing_new->curr) {
    //                     $curr_record = $mysql_sb->table('masterpilihan')->where('id', $act_costing_new->curr)->first();
    //                     if ($curr_record && strtoupper($curr_record->nama_pilihan) == 'USD') {
    //                         $jenis_rate_val = 'J';
    //                     }
    //                 }

    //                 // Sync act_costing_mat
    //                 $processed_mat_items = [];
    //                 foreach ($mat_details as $mat) {
    //                     if (in_array($mat->item_id, $processed_mat_items)) continue; // Hindari update duplikat jika ada set berbeda
    //                     $exists = $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->where('id_item', $mat->item_id)->first();
    //                     $mat_data = [
    //                         'price'           => $mat->price != 0 ? $mat->price : (($jenis_rate_val == 'J') ? ($mat->value_usd ?? 0) : ($mat->value_idr ?? 0)),
    //                         'cons'            => $mat->cons ?? 0,
    //                         'unit'            => $mat->unit ?? '',
    //                         'allowance'       => $mat->allowance ?? 0,
    //                         'material_source' => $mat->origin ?? 'LOKAL',
    //                         'jenis_rate'      => $jenis_rate_val,
    //                     ];
    //                     if ($exists) {
    //                         $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->where('id_item', $mat->item_id)->update($mat_data);
    //                     } else {
    //                         $mat_data['id_act_cost'] = $so->id_cost;
    //                         $mat_data['id_item'] = $mat->item_id;
    //                         $mysql_sb->table('act_costing_mat')->insert($mat_data);
    //                     }
    //                     $processed_mat_items[] = $mat->item_id;
    //                 }
    //                 if (count($processed_mat_items) > 0) {
    //                     $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $processed_mat_items)->delete();
    //                 } else {
    //                     $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->delete();
    //                 }

    //                 // Sync act_costing_mfg
    //                 $processed_mfg_items = [];
    //                 foreach ($mfg_details as $mfg) {
    //                     if (in_array($mfg->item_id, $processed_mfg_items)) continue;
    //                     $exists = $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->where('id_item', $mfg->item_id)->first();
    //                     $mfg_data = [
    //                         'smv'             => null,
    //                         'price'           => $mfg->price != 0 ? $mfg->price : (($jenis_rate_val == 'J') ? ($mfg->value_usd ?? 0) : ($mfg->value_idr ?? 0)),
    //                         'cons'            => $mfg->cons ?? 1,
    //                         'unit'            => $mfg->unit ?? 'PCS',
    //                         'allowance'       => $mfg->allowance ?? 0,
    //                         'material_source' => $mfg->origin ?? 'LOKAL',
    //                         'jenis_rate'      => $jenis_rate_val,
    //                     ];
    //                     if ($exists) {
    //                         $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->where('id_item', $mfg->item_id)->update($mfg_data);
    //                     } else {
    //                         $mfg_data['id_act_cost'] = $so->id_cost;
    //                         $mfg_data['id_item'] = $mfg->item_id;
    //                         $mysql_sb->table('act_costing_mfg')->insert($mfg_data);
    //                     }
    //                     $processed_mfg_items[] = $mfg->item_id;
    //                 }
    //                 if (count($processed_mfg_items) > 0) {
    //                     $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $processed_mfg_items)->delete();
    //                 } else {
    //                     $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->delete();
    //                 }

    //                 // Sync act_costing_oth
    //                 $processed_oth_items = [];
    //                 foreach ($oth_details as $oth) {
    //                     if (in_array($oth->item_id, $processed_oth_items)) continue;
    //                     $exists = $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->where('id_item', $oth->item_id)->first();
    //                     $oth_data = [
    //                         'smv'             => null,
    //                         'price'           => $oth->price != 0 ? $oth->price : (($jenis_rate_val == 'J') ? ($oth->value_usd ?? 0) : ($oth->value_idr ?? 0)),
    //                         'cons'            => $oth->cons ?? null,
    //                         'unit'            => $oth->unit ?? null,
    //                         'allowance'       => $oth->allowance ?? null,
    //                         'material_source' => null,
    //                         'jenis_rate'      => $jenis_rate_val,
    //                     ];
    //                     if ($exists) {
    //                         $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->where('id_item', $oth->item_id)->update($oth_data);
    //                     } else {
    //                         $oth_data['id_act_cost'] = $so->id_cost;
    //                         $oth_data['id_item'] = $oth->item_id;
    //                         $mysql_sb->table('act_costing_oth')->insert($oth_data);
    //                     }
    //                     $processed_oth_items[] = $oth->item_id;
    //                 }
    //                 if (count($processed_oth_items) > 0) {
    //                     $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $processed_oth_items)->delete();
    //                 } else {
    //                     $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->delete();
    //                 }
    //             }
    //         }

    //         $mysql_sb->commit();
    //         return [
    //             'status' => 200,
    //             'message' => 'Sync berhasil',
    //             'inserted' => $insert_count,
    //             'updated' => $update_count,
    //             'canceled' => $cancel_count
    //         ];

    //     } catch (\Exception $e) {
    //         $mysql_sb->rollBack();
    //         return ['status' => 500, 'message' => $e->getMessage()];
    //     }
    // }

    // public static function executeSyncBom($id)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');
    //     $mysql_sb->beginTransaction();

    //     try {
    //         // ==========================================
    //         // 1. Validasi Awal SO & JO
    //         // ==========================================
    //         $so = $mysql_sb->table('so')->where('id', $id)->first();
    //         if (!$so) return ['status' => 400, 'message' => 'SO tidak ditemukan'];
    //         if (!$so->id_bom) return ['status' => 400, 'message' => 'SO ini tidak memiliki Master BOM (id_bom kosong)'];

    //         $jo_det = $mysql_sb->table('jo_det')->where('id_so', $id)->first();
    //         if (!$jo_det) return ['status' => 400, 'message' => 'JO tidak ditemukan untuk SO ini'];

    //         $id_jo = $jo_det->id_jo;
    //         $id_bom = $so->id_bom;
    //         $username = auth()->check() ? auth()->user()->username : 'system';

    //         // ==========================================
    //         // 2. Ambil Data Master BOM & Data Existing
    //         // ==========================================
    //         $required_items = $mysql_sb->select("
    //             SELECT
    //                 ? as id_jo,
    //                 sd.id as id_so_det,
    //                 CASE WHEN acd.type = 'Manufacturing' THEN 'P' ELSE 'M' END as status,
    //                 CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE mi.id_gen END as id_item,
    //                 bmd.qty as cons,
    //                 bmd.unit,
    //                 bmd.id_supplier,
    //                 UPPER(bmd.rule_bom) as rule_bom,
    //                 bmd.shell as id_panel,
    //                 bmd.notes as notes -- PERUBAHAN: Ambil notes dari bom_marketing_detail
    //             FROM so_det sd
    //             INNER JOIN bom_marketing_detail bmd
    //                 ON  bmd.id_bom_marketing = ?
    //                 AND (bmd.id_color IS NULL OR bmd.id_color = sd.id_color)
    //                 AND (bmd.id_size  IS NULL OR bmd.id_size  = sd.id_size)
    //                 AND (bmd.id_set   IS NULL OR bmd.id_set   = (SELECT id FROM master_set WHERE nama = sd.product_set LIMIT 1))
    //             INNER JOIN masteritem mi
    //                 ON mi.id_item = bmd.id_item
    //             LEFT JOIN act_costing_detail_new acd
    //                 ON bmd.id_costing_detail = acd.id
    //             WHERE sd.id_so = ? AND bmd.cancel = 'N'
    //             AND (mi.id_gen IS NOT NULL OR acd.type = 'Manufacturing')
    //         ", [$id_jo, $id_bom, $id]);

    //         $existing_items = $mysql_sb->table('bom_jo_item')
    //             ->where('id_jo', $id_jo)
    //             ->where('cancel', 'N')
    //             ->get();

    //         // Mapping Data Existing (Array Based) untuk handle baris kembar
    //         $existing_map = [];
    //         $posno_map = [];
    //         foreach ($existing_items as $item) {
    //             $key = $item->id_so_det . '_' . $item->id_item . '_' . $item->id_panel;
    //             $existing_map[$key][] = $item;

    //             if (!isset($posno_map[$item->id_item])) {
    //                 $posno_map[$item->id_item] = $item->posno;
    //             }
    //         }

    //         $posno_counter = (int) $mysql_sb->table('bom_jo_item')->where('id_jo', $id_jo)->max('posno');

    //         // ==========================================
    //         // 3. Proses Komparasi (Insert / Update)
    //         // ==========================================
    //         $processed_ids = [];
    //         $to_insert = [];
    //         $update_count = 0;

    //         foreach ($required_items as $req) {
    //             $key = $req->id_so_det . '_' . $req->id_item . '_' . $req->id_panel;
    //             $match_idx = null;

    //             // Jika ada di mapping, cari pasangan yang paling cocok (Prioritas: cons sama)
    //             if (!empty($existing_map[$key])) {
    //                 foreach ($existing_map[$key] as $idx => $ext) {
    //                     if (floatval($ext->cons) == floatval($req->cons)) {
    //                         $match_idx = $idx; break;
    //                     }
    //                 }
    //                 if ($match_idx === null) $match_idx = array_key_first($existing_map[$key]);
    //             }

    //             if ($match_idx !== null) {
    //                 // ---- UPDATE EXISTING ITEM ----
    //                 $ext = $existing_map[$key][$match_idx];
    //                 $processed_ids[] = $ext->id;

    //                 // PERUBAHAN: Tambahkan pengecekan apakah notes berubah
    //                 if (floatval($ext->cons) != floatval($req->cons) ||
    //                     $ext->unit != $req->unit ||
    //                     $ext->rule_bom != $req->rule_bom ||
    //                     $ext->notes != $req->notes ||
    //                     $ext->cancel == 'Y'
    //                 ) {
    //                     $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update([
    //                         'cons'     => $req->cons,
    //                         'unit'     => $req->unit,
    //                         'rule_bom' => $req->rule_bom,
    //                         'id_panel' => $req->id_panel,
    //                         'notes'    => $req->notes, // PERUBAHAN: Update kolom notes jika ada perubahan
    //                         'cancel'   => 'N'
    //                     ]);
    //                     $update_count++;
    //                 }
    //                 unset($existing_map[$key][$match_idx]);
    //             } else {
    //                 // ---- INSERT NEW ITEM ----
    //                 if (!isset($posno_map[$req->id_item])) {
    //                     $posno_counter++;
    //                     $posno_map[$req->id_item] = str_pad($posno_counter, 3, '0', STR_PAD_LEFT);
    //                 }

    //                 $to_insert[] = [
    //                     'id_jo'       => $req->id_jo,
    //                     'id_so_det'   => $req->id_so_det,
    //                     'status'      => $req->status,
    //                     'id_item'     => $req->id_item,
    //                     'cons'        => $req->cons,
    //                     'unit'        => $req->unit,
    //                     'id_supplier' => $req->id_supplier,
    //                     'rule_bom'    => $req->rule_bom,
    //                     'cancel'      => 'N',
    //                     'add_item'    => 'N',
    //                     'username'    => $username,
    //                     'dateinput'   => now(),
    //                     'id_panel'    => $req->id_panel,
    //                     'posno'       => $posno_map[$req->id_item],
    //                     'notes'       => $req->notes, // PERUBAHAN: Masukkan notes dari Master BOM
    //                 ];
    //             }
    //         }

    //         if (!empty($to_insert)) {
    //             foreach (array_chunk($to_insert, 500) as $chunk) {
    //                 $mysql_sb->table('bom_jo_item')->insert($chunk);
    //             }
    //         }

    //         // ==========================================
    //         // 4. Proses Cancel Item (Sisa di Existing)
    //         // ==========================================
    //         $cancel_count = 0;
    //         foreach ($existing_items as $ext) {
    //             if (!in_array($ext->id, $processed_ids) && $ext->cancel != 'Y') {
    //                 $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update(['cancel' => 'Y']);
    //                 $cancel_count++;
    //             }
    //         }

    //         // ==========================================
    //         // 5. Sync Costing Native (MAT, MFG, OTH)
    //         // ==========================================
    //         $bom = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
    //         if ($bom && $so->id_cost) {
    //             $costing = $mysql_sb->table('act_costing_new')->where('id', $bom->id_costing)->first();

    //             if ($costing) {
    //                 $jenis_rate_val = 'B';
    //                 if ($costing->curr) {
    //                     $curr_record = $mysql_sb->table('masterpilihan')->where('id', $costing->curr)->first();
    //                     if ($curr_record && strtoupper($curr_record->nama_pilihan) == 'USD') $jenis_rate_val = 'J';
    //                 }

    //                 $cost_details = $mysql_sb->table('act_costing_detail_new')->where('id_costing', $costing->id)->get();
    //                 $mat_items = []; $mfg_items = []; $oth_items = [];

    //                 foreach ($cost_details as $det) {
    //                     $price = $det->price != 0 ? $det->price : ($jenis_rate_val == 'J' ? ($det->value_usd ?? 0) : ($det->value_idr ?? 0));

    //                     if (in_array($det->type, ['Fabric', 'Accessories Sewing', 'Accessories Packing']) && !in_array($det->item_id, $mat_items)) {
    //                         $mysql_sb->table('act_costing_mat')->updateOrInsert(
    //                             ['id_act_cost' => $so->id_cost, 'id_item' => $det->item_id],
    //                             ['price' => $price, 'cons' => $det->cons ?? 0, 'unit' => $det->unit ?? '', 'allowance' => $det->allowance ?? 0, 'material_source' => $det->origin ?? 'LOKAL', 'jenis_rate' => $jenis_rate_val]
    //                         );
    //                         $mat_items[] = $det->item_id;
    //                     }
    //                     elseif ($det->type == 'Manufacturing' && !in_array($det->item_id, $mfg_items)) {
    //                         $mysql_sb->table('act_costing_mfg')->updateOrInsert(
    //                             ['id_act_cost' => $so->id_cost, 'id_item' => $det->item_id],
    //                             ['smv' => null, 'price' => $price, 'cons' => $det->cons ?? 1, 'unit' => $det->unit ?? 'PCS', 'allowance' => $det->allowance ?? 0, 'material_source' => $det->origin ?? 'LOKAL', 'jenis_rate' => $jenis_rate_val]
    //                         );
    //                         $mfg_items[] = $det->item_id;
    //                     }
    //                     elseif ($det->type == 'Other Cost' && !in_array($det->item_id, $oth_items)) {
    //                         $mysql_sb->table('act_costing_oth')->updateOrInsert(
    //                             ['id_act_cost' => $so->id_cost, 'id_item' => $det->item_id],
    //                             ['smv' => null, 'price' => $price, 'cons' => $det->cons ?? null, 'unit' => $det->unit ?? null, 'allowance' => $det->allowance ?? null, 'material_source' => null, 'jenis_rate' => $jenis_rate_val]
    //                         );
    //                         $oth_items[] = $det->item_id;
    //                     }
    //                 }

    //                 if (empty($mat_items)) $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->delete();
    //                 else $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $mat_items)->delete();

    //                 if (empty($mfg_items)) $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->delete();
    //                 else $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $mfg_items)->delete();

    //                 if (empty($oth_items)) $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->delete();
    //                 else $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $oth_items)->delete();
    //             }
    //         }

    //         $mysql_sb->commit();
    //         return [
    //             'status' => 200,
    //             'message' => 'Sync berhasil',
    //             'inserted' => count($to_insert),
    //             'updated' => $update_count,
    //             'canceled' => $cancel_count
    //         ];

    //     } catch (\Exception $e) {
    //         $mysql_sb->rollBack();
    //         return ['status' => 500, 'message' => $e->getMessage()];
    //     }
    // }

    // public static function executeSyncBom($id)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');
    //     $mysql_sb->beginTransaction();

    //     try {
    //         // 1. Validasi Awal SO & JO + lock

    //         $so = $mysql_sb->table('so')->where('id', $id)->lockForUpdate()->first();
    //         if (!$so) return ['status' => 400, 'message' => 'SO tidak ditemukan'];
    //         if (!$so->id_bom) return ['status' => 400, 'message' => 'SO ini tidak memiliki Master BOM (id_bom kosong)'];

    //         $jo_det = $mysql_sb->table('jo_det')->where('id_so', $id)->first();
    //         if (!$jo_det) return ['status' => 400, 'message' => 'JO tidak ditemukan untuk SO ini'];

    //         $id_jo = $jo_det->id_jo;
    //         $id_bom = $so->id_bom;
    //         $username = auth()->check() ? auth()->user()->username : 'system';

    //         // 2. Ambil Data Master BOM
    //         $required_items = $mysql_sb->select("
    //             SELECT
    //                 ? as id_jo,
    //                 sd.id as id_so_det,
    //                 MAX(CASE WHEN acd.type = 'Manufacturing' THEN 'P' ELSE 'M' END) as status,
    //                 CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE COALESCE(mi.id_gen, mi.id_item) END as id_item,
    //                 bmd.shell as id_panel,
    //                 MAX(bmd.id_supplier) as id_supplier,
    //                 MAX(UPPER(bmd.rule_bom)) as rule_bom,
    //                 MAX(bmd.qty) as cons,
    //                 MAX(bmd.unit) as unit,
    //                 MAX(bmd.notes) as notes,
    //                 COUNT(*) as jumlah_baris_source,
    //                 COUNT(DISTINCT bmd.id_supplier) as jumlah_supplier_beda,
    //                 COUNT(DISTINCT UPPER(bmd.rule_bom)) as jumlah_rule_beda
    //             FROM so_det sd
    //             INNER JOIN bom_marketing_detail bmd
    //                 ON  bmd.id_bom_marketing = ?
    //                 AND (bmd.id_color IS NULL OR bmd.id_color = sd.id_color)
    //                 AND (bmd.id_size  IS NULL OR bmd.id_size  = sd.id_size)
    //                 AND (bmd.id_set   IS NULL OR bmd.id_set   = (SELECT id FROM master_set WHERE nama = sd.product_set LIMIT 1))
    //             INNER JOIN masteritem mi
    //                 ON mi.id_item = bmd.id_item
    //             LEFT JOIN act_costing_detail_new acd
    //                 ON bmd.id_costing_detail = acd.id
    //             WHERE sd.id_so = ? AND bmd.cancel = 'N'
    //             AND (mi.id_gen IS NOT NULL OR acd.type = 'Manufacturing')
    //             GROUP BY
    //                sd.id,
    //                 CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE COALESCE(mi.id_gen, mi.id_item) END,
    //                 bmd.shell,
    //                 UPPER(bmd.rule_bom),
    //                 bmd.notes
    //         ", [$id_jo, $id_bom, $id]);

    //         // Deteksi anomali data Master BOM sebelum lanjut.
    //         foreach ($required_items as $req) {
    //             if ($req->jumlah_baris_source > 1) {
    //                 \Log::warning("BOM sync: ditemukan baris duplikat di bom_marketing_detail", [
    //                     'id_jo' => $id_jo,
    //                     'id_so_det' => $req->id_so_det,
    //                     'id_item' => $req->id_item,
    //                     'id_panel' => $req->id_panel,
    //                     'jumlah_baris_source' => $req->jumlah_baris_source,
    //                     'jumlah_supplier_beda' => $req->jumlah_supplier_beda,
    //                     'jumlah_rule_beda' => $req->jumlah_rule_beda,
    //                 ]);

    //                 if ($req->jumlah_supplier_beda > 1 || $req->jumlah_rule_beda > 1) {
    //                     $mysql_sb->rollBack();
    //                     return [
    //                         'status' => 409,
    //                         'message' => "Konflik data di Master BOM: item {$req->id_item} untuk SO Detail {$req->id_so_det} punya {$req->jumlah_baris_source} baris dengan supplier/rule_bom berbeda. Mohon perbaiki Master BOM terlebih dahulu sebelum sync."
    //                     ];
    //                 }
    //             }
    //         }

    //         $existing_items = $mysql_sb->table('bom_jo_item')
    //             ->where('id_jo', $id_jo)
    //             ->where('cancel', 'N')
    //             ->get();

    //         // Key matching: id_so_det + id_item + id_panel + rule + notes
    //         $existing_map = [];
    //         $posno_map = [];
    //         foreach ($existing_items as $item) {
    //             $key = $item->id_so_det . '_' . $item->id_item . '_' . $item->id_panel . '_' . $item->rule_bom . '_' . $item->notes;

    //             if (isset($existing_map[$key])) {
    //                 \Log::warning("BOM sync: existing bom_jo_item duplikat ditemukan, akan di-cancel otomatis", [
    //                     'id_jo' => $id_jo,
    //                     'key' => $key,
    //                     'id_lama' => $existing_map[$key]->id,
    //                     'id_dilewati' => $item->id,
    //                 ]);
    //                 continue;
    //             }

    //             $existing_map[$key] = $item;

    //             if (!isset($posno_map[$item->id_item])) {
    //                 $posno_map[$item->id_item] = $item->posno;
    //             }
    //         }

    //         $posno_counter = (int) $mysql_sb->table('bom_jo_item')->where('id_jo', $id_jo)->max('posno');

    //         //Proses compare (Insert / Update)
    //         $processed_ids = [];
    //         $to_insert = [];
    //         $update_count = 0;

    //         foreach ($required_items as $req) {
    //             $key = $req->id_so_det . '_' . $req->id_item . '_' . $req->id_panel . '_' . $req->rule_bom . '_' . $req->notes;

    //             if (isset($existing_map[$key])) {
    //                 $ext = $existing_map[$key];
    //                 $processed_ids[] = $ext->id;

    //                 if (floatval($ext->cons) != floatval($req->cons) ||
    //                     $ext->unit != $req->unit ||
    //                     $ext->rule_bom != $req->rule_bom ||
    //                     $ext->id_supplier != $req->id_supplier ||
    //                     $ext->notes != $req->notes ||
    //                     $ext->cancel == 'Y'
    //                 ) {
    //                     $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update([
    //                         'cons'        => $req->cons,
    //                         'unit'        => $req->unit,
    //                         'rule_bom'    => $req->rule_bom,
    //                         'id_supplier' => $req->id_supplier,
    //                         'notes'       => $req->notes,
    //                         'cancel'      => 'N'
    //                     ]);
    //                     $update_count++;
    //                 }
    //             } else {
    //                 if (!isset($posno_map[$req->id_item])) {
    //                     $posno_counter++;
    //                     $posno_map[$req->id_item] = str_pad($posno_counter, 3, '0', STR_PAD_LEFT);
    //                 }

    //                 $to_insert[] = [
    //                     'id_jo'       => $req->id_jo,
    //                     'id_so_det'   => $req->id_so_det,
    //                     'status'      => $req->status,
    //                     'id_item'     => $req->id_item,
    //                     'cons'        => $req->cons,
    //                     'unit'        => $req->unit,
    //                     'id_supplier' => $req->id_supplier,
    //                     'rule_bom'    => $req->rule_bom,
    //                     'cancel'      => 'N',
    //                     'add_item'    => 'N',
    //                     'username'    => $username,
    //                     'dateinput'   => now(),
    //                     'id_panel'    => $req->id_panel,
    //                     'posno'       => $posno_map[$req->id_item],
    //                     'notes'       => $req->notes,
    //                 ];
    //             }
    //         }

    //         if (!empty($to_insert)) {
    //             foreach (array_chunk($to_insert, 500) as $chunk) {
    //                 $mysql_sb->table('bom_jo_item')->insert($chunk);
    //             }
    //         }

    //         // Proses Cancel Item (Sisa di Existing)
    //         $cancel_count = 0;
    //         foreach ($existing_items as $ext) {
    //             if (!in_array($ext->id, $processed_ids) && $ext->cancel != 'Y') {
    //                 $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update(['cancel' => 'Y']);
    //                 $cancel_count++;
    //             }
    //         }

    //         // Sync Costing Native (MAT, MFG, OTH)
    //         $bom = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
    //         if ($bom && $so->id_cost) {
    //             $costing = $mysql_sb->table('act_costing_new')->where('id', $bom->id_costing)->first();

    //             if ($costing) {
    //                 $jenis_rate_val = 'B';
    //                 if ($costing->curr) {
    //                     $curr_record = $mysql_sb->table('masterpilihan')->where('id', $costing->curr)->first();
    //                     if ($curr_record && strtoupper($curr_record->nama_pilihan) == 'USD') $jenis_rate_val = 'J';
    //                 }

    //                 $cost_details = $mysql_sb->table('act_costing_detail_new')->where('id_costing', $costing->id)->get();
    //                 $mat_items = []; $mfg_items = []; $oth_items = [];

    //                 foreach ($cost_details as $det) {
    //                     $price = $det->price != 0 ? $det->price : ($jenis_rate_val == 'J' ? ($det->value_usd ?? 0) : ($det->value_idr ?? 0));

    //                     if (in_array($det->type, ['Fabric', 'Accessories Sewing', 'Accessories Packing']) && !in_array($det->item_id, $mat_items)) {
    //                         $mysql_sb->table('act_costing_mat')->updateOrInsert(
    //                             ['id_act_cost' => $so->id_cost, 'id_item' => $det->item_id],
    //                             ['price' => $price, 'cons' => $det->cons ?? 0, 'unit' => $det->unit ?? '', 'allowance' => $det->allowance ?? 0, 'material_source' => $det->origin ?? 'LOKAL', 'jenis_rate' => $jenis_rate_val]
    //                         );
    //                         $mat_items[] = $det->item_id;
    //                     }
    //                     elseif ($det->type == 'Manufacturing' && !in_array($det->item_id, $mfg_items)) {
    //                         $mysql_sb->table('act_costing_mfg')->updateOrInsert(
    //                             ['id_act_cost' => $so->id_cost, 'id_item' => $det->item_id],
    //                             ['smv' => null, 'price' => $price, 'cons' => $det->cons ?? 1, 'unit' => $det->unit ?? 'PCS', 'allowance' => $det->allowance ?? 0, 'material_source' => $det->origin ?? 'LOKAL', 'jenis_rate' => $jenis_rate_val]
    //                         );
    //                         $mfg_items[] = $det->item_id;
    //                     }
    //                     elseif ($det->type == 'Other Cost' && !in_array($det->item_id, $oth_items)) {
    //                         $mysql_sb->table('act_costing_oth')->updateOrInsert(
    //                             ['id_act_cost' => $so->id_cost, 'id_item' => $det->item_id],
    //                             ['smv' => null, 'price' => $price, 'cons' => $det->cons ?? null, 'unit' => $det->unit ?? null, 'allowance' => $det->allowance ?? null, 'material_source' => null, 'jenis_rate' => $jenis_rate_val]
    //                         );
    //                         $oth_items[] = $det->item_id;
    //                     }
    //                 }

    //                 if (empty($mat_items)) $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->delete();
    //                 else $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $mat_items)->delete();

    //                 if (empty($mfg_items)) $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->delete();
    //                 else $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $mfg_items)->delete();

    //                 if (empty($oth_items)) $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->delete();
    //                 else $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->whereNotIn('id_item', $oth_items)->delete();
    //             }
    //         }

    //         $mysql_sb->commit();
    //         return [
    //             'status' => 200,
    //             'message' => 'Sync berhasil',
    //             'inserted' => count($to_insert),
    //             'updated' => $update_count,
    //             'canceled' => $cancel_count
    //         ];

    //     } catch (\Exception $e) {
    //         $mysql_sb->rollBack();
    //         return ['status' => 500, 'message' => $e->getMessage()];
    //     }
    // }

    public static function executeSyncBom($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            // 1. Validasi Awal SO & JO + lock
            $so = $mysql_sb->table('so')->where('id', $id)->lockForUpdate()->first();
            if (!$so) return ['status' => 400, 'message' => 'SO tidak ditemukan'];
            if (!$so->id_bom) return ['status' => 400, 'message' => 'SO ini tidak memiliki Master BOM (id_bom kosong)'];

            $jo_det = $mysql_sb->table('jo_det')->where('id_so', $id)->first();
            if (!$jo_det) return ['status' => 400, 'message' => 'JO tidak ditemukan untuk SO ini'];

            $id_jo = $jo_det->id_jo;
            $id_bom = $so->id_bom;
            $username = auth()->check() ? auth()->user()->username : 'system';

            // 2. Ambil Data Master BOM
            $required_items = $mysql_sb->select("
                SELECT
                    ? as id_jo,
                    sd.id as id_so_det,
                    MAX(CASE WHEN acd.type = 'Manufacturing' THEN 'P' ELSE 'M' END) as status,
                    CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE COALESCE(mi.id_gen, mi.id_item) END as id_item,
                    bmd.shell as id_panel,
                    UPPER(TRIM(COALESCE(bmd.rule_bom, ''))) as rule_bom,
                    TRIM(COALESCE(bmd.notes, '')) as notes,
                    MAX(bmd.id_supplier) as id_supplier,
                    MAX(bmd.qty) as cons,
                    MAX(bmd.unit) as unit,
                    COUNT(*) as jumlah_baris_source,
                    COUNT(DISTINCT bmd.id_supplier) as jumlah_supplier_beda
                FROM so_det sd
                INNER JOIN bom_marketing_detail bmd
                    ON  bmd.id_bom_marketing = ?
                    AND (bmd.id_color IS NULL OR bmd.id_color = sd.id_color)
                    AND (bmd.id_size  IS NULL OR bmd.id_size  = sd.id_size)
                    AND (bmd.id_set   IS NULL OR bmd.id_set   = (SELECT id FROM master_set WHERE nama = sd.product_set LIMIT 1))
                INNER JOIN masteritem mi
                    ON mi.id_item = bmd.id_item
                LEFT JOIN act_costing_detail_new acd
                    ON bmd.id_costing_detail = acd.id
                WHERE sd.id_so = ? AND bmd.cancel = 'N'
                AND (mi.id_gen IS NOT NULL OR acd.type = 'Manufacturing')
                GROUP BY
                    sd.id,
                    CASE WHEN acd.type = 'Manufacturing' THEN mi.id_item ELSE COALESCE(mi.id_gen, mi.id_item) END,
                    bmd.shell,
                    UPPER(TRIM(COALESCE(bmd.rule_bom, ''))),
                    TRIM(COALESCE(bmd.notes, ''))
            ", [$id_jo, $id_bom, $id]);

            // ==========================================
            //Deteksi anomali data Master BOM.
            foreach ($required_items as $req) {
                if ($req->jumlah_baris_source > 1 && $req->jumlah_supplier_beda > 1) {
                    \Log::warning("BOM sync: konflik supplier di bom_marketing_detail", [
                        'id_jo' => $id_jo,
                        'id_so_det' => $req->id_so_det,
                        'id_item' => $req->id_item,
                        'id_panel' => $req->id_panel,
                        'rule_bom' => $req->rule_bom,
                        'notes' => $req->notes,
                        'jumlah_baris_source' => $req->jumlah_baris_source,
                        'jumlah_supplier_beda' => $req->jumlah_supplier_beda,
                    ]);

                    $mysql_sb->rollBack();
                    return [
                        'status' => 409,
                        'message' => "Konflik data di Master BOM: item {$req->id_item} (notes: \"{$req->notes}\") untuk SO Detail {$req->id_so_det} punya {$req->jumlah_supplier_beda} supplier berbeda pada baris identik. Mohon perbaiki Master BOM terlebih dahulu sebelum sync."
                    ];
                }
            }

            // Helper: bikin key matching yang konsisten & ternormalisasi,
            // dipakai baik untuk existing_map maupun required_items.
            $makeKey = function ($id_so_det, $id_item, $id_panel, $rule_bom, $notes) {
                return implode('|', [
                    $id_so_det,
                    $id_item,
                    $id_panel ?? '',
                    strtoupper(trim($rule_bom ?? '')),
                    trim($notes ?? ''),
                ]);
            };

            // posno key TANPA id_so_det — supaya 1 item (dgn panel/rule/notes sama) = 1 posno
            $makePosnoKey = function ($id_item, $id_panel, $rule_bom, $notes) {
                return implode('|', [
                    $id_item,
                    $id_panel ?? '',
                    strtoupper(trim($rule_bom ?? '')),
                    trim($notes ?? ''),
                ]);
            };

            $existing_items = $mysql_sb->table('bom_jo_item')
                ->where('id_jo', $id_jo)
                ->where('cancel', 'N')
                ->get();

            // Key matching: id_so_det + id_item + id_panel + rule_bom + notes
            // (semua sudah dinormalisasi lewat $makeKey supaya NULL vs ''
            // atau beda spasi/case tidak dianggap baris berbeda)
            $existing_map = [];
            $posno_map = [];
            foreach ($existing_items as $item) {
                $key = $makeKey($item->id_so_det, $item->id_item, $item->id_panel, $item->rule_bom, $item->notes);
                if (isset($existing_map[$key])) { continue; }
                $existing_map[$key] = $item;

                $posnoKey = $makePosnoKey($item->id_item, $item->id_panel, $item->rule_bom, $item->notes);
                if (!isset($posno_map[$posnoKey])) {
                    $posno_map[$posnoKey] = $item->posno;
                }
            }

            $posno_counter = (int) $mysql_sb->table('bom_jo_item')->where('id_jo', $id_jo)->max('posno');

            // 3. Proses Komparasi (Insert / Update)
            $processed_ids = [];
            $to_insert = [];
            $update_count = 0;

            foreach ($required_items as $req) {
                $key = $makeKey($req->id_so_det, $req->id_item, $req->id_panel, $req->rule_bom, $req->notes);

                if (isset($existing_map[$key])) {
                    // ---- UPDATE EXISTING ITEM ----
                    $ext = $existing_map[$key];
                    $processed_ids[] = $ext->id;

                    if (floatval($ext->cons) != floatval($req->cons) ||
                        $ext->unit != $req->unit ||
                        $ext->rule_bom != $req->rule_bom ||
                        $ext->id_supplier != $req->id_supplier ||
                        $ext->notes != $req->notes ||
                        $ext->cancel == 'Y'
                    ) {
                        $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update([
                            'cons'        => $req->cons,
                            'unit'        => $req->unit,
                            'rule_bom'    => $req->rule_bom,
                            'notes'       => $req->notes,
                            'cancel'      => 'N'
                        ]);
                        $update_count++;
                    }
                } else {
                    $posnoKey = $makePosnoKey($req->id_item, $req->id_panel, $req->rule_bom, $req->notes);
                    if (!isset($posno_map[$posnoKey])) {
                        $posno_counter++;
                        $posno_map[$posnoKey] = str_pad($posno_counter, 3, '0', STR_PAD_LEFT);
                    }

                    $to_insert[] = [
                        'id_jo'     => $req->id_jo,
                        'id_so_det' => $req->id_so_det,
                        'status'    => $req->status,
                        'id_item'   => $req->id_item,
                        'cons'      => $req->cons,
                        'unit'      => $req->unit,
                        'rule_bom'  => $req->rule_bom,
                        'cancel'    => 'N',
                        'add_item'  => 'N',
                        'username'  => $username,
                        'dateinput' => now(),
                        'id_panel'  => $req->id_panel,
                        'posno'     => $posno_map[$posnoKey],
                        'notes'     => $req->notes,
                    ];
                }
            }

            if (!empty($to_insert)) {
                foreach (array_chunk($to_insert, 500) as $chunk) {
                    $mysql_sb->table('bom_jo_item')->insert($chunk);
                }
            }

            // 4. Proses Cancel Item (Sisa di Existing) -> Soft Cancel
            $cancel_count = 0;
            foreach ($existing_items as $ext) {
                if (!in_array($ext->id, $processed_ids) && $ext->cancel != 'Y') {
                    $mysql_sb->table('bom_jo_item')->where('id', $ext->id)->update(['cancel' => 'Y']);
                    $cancel_count++;
                }
            }

            // 5. Sync Costing Native (MAT, MFG, OTH) -- tidak diubah
            $bom = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
            if ($bom && $so->id_cost) {
                $costing = $mysql_sb->table('act_costing_new')->where('id', $bom->id_costing)->first();

                if ($costing) {
                    $jenis_rate_val = 'B';
                    if ($costing->curr) {
                        $curr_record = $mysql_sb->table('masterpilihan')->where('id', $costing->curr)->first();
                        if ($curr_record && strtoupper($curr_record->nama_pilihan) == 'USD') $jenis_rate_val = 'J';
                    }

                    $cost_details = $mysql_sb->table('act_costing_detail_new')->where('id_costing', $costing->id)->get();

                    $mysql_sb->table('act_costing_mat')->where('id_act_cost', $so->id_cost)->delete();
                    $mysql_sb->table('act_costing_mfg')->where('id_act_cost', $so->id_cost)->delete();
                    $mysql_sb->table('act_costing_oth')->where('id_act_cost', $so->id_cost)->delete();

                    foreach ($cost_details as $det) {
                        $price = $det->price != 0 ? $det->price : ($jenis_rate_val == 'J' ? ($det->value_usd ?? 0) : ($det->value_idr ?? 0));

                        if (in_array($det->type, ['Fabric', 'Accessories Sewing', 'Accessories Packing'])) {
                            $mysql_sb->table('act_costing_mat')->insert([
                                'id_act_cost'     => $so->id_cost,
                                'id_item'         => $det->item_id,
                                'price'           => $price,
                                'cons'            => $det->cons ?? 0,
                                'unit'            => $det->unit ?? '',
                                'allowance'       => $det->allowance ?? 0,
                                'material_source' => $det->origin ?? 'LOKAL',
                                'jenis_rate'      => $jenis_rate_val
                            ]);
                        }
                        elseif ($det->type == 'Manufacturing') {
                            $mysql_sb->table('act_costing_mfg')->insert([
                                'id_act_cost'     => $so->id_cost,
                                'id_item'         => $det->item_id,
                                'smv'             => null,
                                'price'           => $price,
                                'cons'            => $det->cons ?? 1,
                                'unit'            => $det->unit ?? 'PCS',
                                'allowance'       => $det->allowance ?? 0,
                                'material_source' => $det->origin ?? 'LOKAL',
                                'jenis_rate'      => $jenis_rate_val
                            ]);
                        }
                        elseif ($det->type == 'Other Cost') {
                            $mysql_sb->table('act_costing_oth')->insert([
                                'id_act_cost'     => $so->id_cost,
                                'id_item'         => $det->item_id,
                                'smv'             => null,
                                'price'           => $price,
                                'cons'            => $det->cons ?? null,
                                'unit'            => $det->unit ?? null,
                                'allowance'       => $det->allowance ?? null,
                                'material_source' => null,
                                'jenis_rate'      => $jenis_rate_val
                            ]);
                        }
                    }
                }
            }

            $mysql_sb->commit();
            return [
                'status' => 200,
                'message' => 'Sync berhasil',
                'inserted' => count($to_insert),
                'updated' => $update_count,
                'canceled' => $cancel_count
            ];

        } catch (\Exception $e) {
            $mysql_sb->rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function syncBom(Request $request, $id)
    {
        $res = self::executeSyncBom($id);
        return response()->json($res);
    }

    // MERGE SO FEATURE

    public function getMergeCandidates($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $source = $mysql_sb->table('so')->where('id', $id)->first();
        if (!$source) {
            return response()->json(['status' => 404, 'message' => 'SO tidak ditemukan']);
        }

        $candidates = $mysql_sb->table('so as s')
            ->join('act_costing as ac', 's.id_cost', '=', 'ac.id')
            ->where('s.id_bom', $source->id_bom)
            ->where('s.id', '!=', $id)
            ->where('s.cancel_h', 'N')
            ->where('ac.aktif', 'Y')
            ->select('s.id', 's.so_no', 's.no_po', 's.qty', 'ac.kpno')
            ->get();

        return response()->json([
            'status'    => 200,
            'source'    => [
                'so_no' => $source->so_no,
                'no_po' => $source->no_po,
            ],
            'candidates' => $candidates
        ]);
    }

    /**
     * Ambil detail so_det dari SO Sumber untuk ditampilkan di modal (dengan checkbox)
     */
    public function getMergeSourceDetail($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $details = $mysql_sb->table('so_det')
            ->where('id_so', $id)
            ->where('cancel', 'N')
            ->select('id', 'color', 'size', 'qty', 'styleno_prod', 'deldate_det', 'product_set', 'dest')
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $details
        ]);
    }

    /**
     * Eksekusi Merge: pindahkan so_det yang dipilih dari SO Sumber ke SO Tujuan
     */
    public function executeMerge(Request $request)
    {
        $mysql_sb    = DB::connection('mysql_sb');
        $id_so_src   = $request->id_so_src;
        $id_so_dst   = $request->id_so_dst;
        $det_ids     = $request->det_ids; // array of so_det.id

        if (empty($id_so_src) || empty($id_so_dst) || empty($det_ids)) {
            return response()->json(['status' => 400, 'message' => 'Data tidak lengkap']);
        }

        if ($id_so_src == $id_so_dst) {
            return response()->json(['status' => 400, 'message' => 'SO Sumber dan Tujuan tidak boleh sama']);
        }

        $so_src = $mysql_sb->table('so')->where('id', $id_so_src)->first();
        $so_dst = $mysql_sb->table('so')->where('id', $id_so_dst)->first();

        if (!$so_src || !$so_dst) {
            return response()->json(['status' => 404, 'message' => 'SO tidak ditemukan']);
        }

        // Ambil id_jo dari SO Sumber & Tujuan (via jo_det)
        $jo_src = $mysql_sb->table('jo_det')->where('id_so', $id_so_src)->first();
        $jo_dst = $mysql_sb->table('jo_det')->where('id_so', $id_so_dst)->first();

        $mysql_sb->beginTransaction();
        try {
            // 1. Pindahkan so_det ke SO Tujuan
            $mysql_sb->table('so_det')
                ->whereIn('id', $det_ids)
                ->where('id_so', $id_so_src)
                ->update(['id_so' => $id_so_dst]);

            // 2. Pindahkan bom_jo_item ke JO Tujuan (jika ada)
            if ($jo_src && $jo_dst) {
                $mysql_sb->table('bom_jo_item')
                    ->whereIn('id_so_det', $det_ids)
                    ->where('id_jo', $jo_src->id_jo)
                    ->update(['id_jo' => $jo_dst->id_jo]);
            }

            // 3. Recalculate qty SO Sumber
            $new_qty_src = $mysql_sb->table('so_det')
                ->where('id_so', $id_so_src)
                ->where('cancel', 'N')
                ->sum('qty');

            // 4. Recalculate qty SO Tujuan
            $new_qty_dst = $mysql_sb->table('so_det')
                ->where('id_so', $id_so_dst)
                ->where('cancel', 'N')
                ->sum('qty');

            // 5. Tentukan no_po SO Tujuan berdasarkan pilihan user
            $po_merge_option = $request->po_merge_option ?? 'combine';

            if ($po_merge_option === 'use_dst') {
                // Pakai no_po SO Tujuan apa adanya, tidak berubah
                $no_po_new = $so_dst->no_po;

            } elseif ($po_merge_option === 'manual') {
                // Ketik sendiri oleh user
                $no_po_new = trim($request->po_manual ?? '');
                if (empty($no_po_new)) {
                    // Fallback ke combine jika user tidak mengisi
                    $po_src_parts = array_map('trim', explode(',', $so_src->no_po ?? ''));
                    $po_dst_parts = array_map('trim', explode(',', $so_dst->no_po ?? ''));
                    $po_merged    = array_unique(array_filter(array_merge($po_dst_parts, $po_src_parts)));
                    $no_po_new    = implode(', ', $po_merged);
                }

            } else {
                // Default: combine (gabungkan, hilangkan duplikat)
                $po_src_parts = array_map('trim', explode(',', $so_src->no_po ?? ''));
                $po_dst_parts = array_map('trim', explode(',', $so_dst->no_po ?? ''));
                $po_merged    = array_unique(array_filter(array_merge($po_dst_parts, $po_src_parts)));
                $no_po_new    = implode(', ', $po_merged);
            }

            if (strlen($no_po_new) > 200) {
                $no_po_new = substr($no_po_new, 0, 195) . '...';
            }

            // 6. Update header SO Sumber & Tujuan
            $mysql_sb->table('so')->where('id', $id_so_src)->update(['qty' => $new_qty_src]);
            $mysql_sb->table('so')->where('id', $id_so_dst)->update([
                'qty'   => $new_qty_dst,
                'no_po' => $no_po_new,
            ]);

            // 7. Update qty act_costing SO Sumber
            $mysql_sb->table('act_costing')->where('id', $so_src->id_cost)->update(['qty' => $new_qty_src]);
            // 8. Update qty act_costing SO Tujuan
            $mysql_sb->table('act_costing')->where('id', $so_dst->id_cost)->update(['qty' => $new_qty_dst]);

            // 9. Jika SO Sumber sudah kosong → void SO & act_costing sumber
            if ($new_qty_src == 0 || $new_qty_src === null) {
                $remaining = $mysql_sb->table('so_det')
                    ->where('id_so', $id_so_src)
                    ->where('cancel', 'N')
                    ->count();

                if ($remaining == 0) {
                    $mysql_sb->table('so')
                        ->where('id', $id_so_src)
                        ->update(['cancel_h' => 'Y']);

                    $mysql_sb->table('act_costing')
                        ->where('id', $so_src->id_cost)
                        ->update(['aktif' => 'N']);
                }
            }

            $mysql_sb->commit();

            return response()->json([
                'status'  => 200,
                'message' => count($det_ids) . ' baris berhasil dipindahkan ke ' . $so_dst->so_no,
                'qty_src' => $new_qty_src,
                'qty_dst' => $new_qty_dst,
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollBack();
            \Log::error('Merge SO Error: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // =============================================
    // INPUT BARCODE FEATURE
    // =============================================

    public function inputBarcodePage($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $so = $mysql_sb->table('so as s')
            ->leftJoin('act_costing as ac', 's.id_cost', '=', 'ac.id')
            ->leftJoin('mastersupplier as ms', 'ac.id_buyer', '=', 'ms.Id_Supplier')
            ->where('s.id', $id)
            ->select('s.id', 's.so_no', 's.no_po', 's.style', 's.brand', 's.qty', 'ac.kpno', 'ms.Supplier as buyer')
            ->first();

        if (!$so) {
            abort(404, 'SO tidak ditemukan');
        }

        $details = $mysql_sb->table('so_det')
            ->where('id_so', $id)
            ->where('cancel', 'N')
            ->orderBy('color')
            ->orderBy('size')
            ->select('id', 'color', 'size', 'qty', 'styleno_prod', 'barcode', 'product_set', 'deldate_det')
            ->get();

        // Ambil list unique color & size untuk filter
        $colors = $details->pluck('color')->unique()->filter()->values();
        $sizes  = $details->pluck('size')->unique()->filter()->values();

        return view('marketing.so.input_barcode', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-so',
            'so'             => $so,
            'details'        => $details,
            'colors'         => $colors,
            'sizes'          => $sizes,
            'containerFluid' => true
        ]);
    }

    public function saveBarcode(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $barcodes = $request->barcodes; // array: [ ['id' => ..., 'barcode' => ...], ... ]

        if (empty($barcodes) || !is_array($barcodes)) {
            return response()->json(['status' => 400, 'message' => 'Tidak ada data barcode']);
        }

        $mysql_sb->beginTransaction();
        try {
            $updated = 0;
            foreach ($barcodes as $item) {
                if (empty($item['id'])) continue;
                $mysql_sb->table('so_det')
                    ->where('id', $item['id'])
                    ->update(['barcode' => $item['barcode'] ?? null]);
                $updated++;
            }
            $mysql_sb->commit();

            return response()->json([
                'status'  => 200,
                'message' => $updated . ' baris barcode berhasil disimpan.'
            ]);
        } catch (\Exception $e) {
            $mysql_sb->rollBack();
            \Log::error('Save Barcode Error: ' . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // =============================================
    // INPUT BARCODE SO - MULTI SO (Halaman Terpisah)
    // =============================================

    public function barcodeInputPage()
    {
        return view('marketing.so.barcode_input', [
            'page'         => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage'      => 'marketing-so-barcode',
            'containerFluid' => true
        ]);
    }

    public function getBarcodeSOList(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $query = $mysql_sb->table('so as s')
            ->leftJoin('act_costing as ac', 's.id_cost', '=', 'ac.id')
            ->leftJoin('mastersupplier as ms', 'ac.id_buyer', '=', 'ms.Id_Supplier')
            ->where('s.cancel_h', 'N')
            ->orderByDesc('s.d_insert');

        // Filter by keyword jika ada (untuk search select2 AJAX)
        if ($request->search) {
            $q = $request->search;
            $query->where(function($w) use ($q) {
                $w->where('s.so_no', 'like', "%$q%")
                  ->orWhere('s.no_po', 'like', "%$q%")
                  ->orWhere('ac.kpno', 'like', "%$q%")
                  ->orWhere('s.style', 'like', "%$q%")
                  ->orWhere('ms.Supplier', 'like', "%$q%");
            });
        }

        $data = $query->select(
            's.id',
            's.so_no',
            's.no_po',
            's.style',
            's.qty',
            'ac.kpno',
            'ms.Supplier as buyer'
        )->limit(100)->get();

        // Format untuk select2
        $results = $data->map(fn($s) => [
            'id'   => $s->id,
            'text' => "{$s->so_no} | WS: {$s->kpno} | PO: {$s->no_po} | {$s->buyer}",
            'so_no'  => $s->so_no,
            'kpno'   => $s->kpno,
            'no_po'  => $s->no_po,
            'style'  => $s->style,
            'buyer'  => $s->buyer,
            'qty'    => $s->qty,
        ]);

        return response()->json(['results' => $results]);
    }

    public function getBarcodeDetails(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $so_ids   = $request->so_ids; // array of SO id

        if (empty($so_ids)) {
            return response()->json(['status' => 400, 'message' => 'Pilih minimal 1 SO']);
        }

        $details = $mysql_sb->table('so_det as sd')
            ->join('so as s', 'sd.id_so', '=', 's.id')
            ->leftJoin('act_costing as ac', 's.id_cost', '=', 'ac.id')
            ->whereIn('sd.id_so', $so_ids)
            ->where('sd.cancel', 'N')
            ->orderBy('s.so_no')
            ->orderBy('sd.color')
            ->orderBy('sd.size')
            ->select(
                'sd.id',
                'sd.id_so',
                's.so_no',
                'ac.kpno',
                's.no_po',
                'sd.color',
                'sd.size',
                'sd.product_set',
                'sd.styleno_prod',
                'sd.qty',
                'sd.barcode',
                'sd.deldate_det'
            )
            ->get();

        return response()->json([
            'status' => 200,
            'data'   => $details,
            'total'  => $details->count(),
            'filled' => $details->filter(fn($d) => !empty($d->barcode))->count(),
        ]);
    }

    /**
     * Status kunci SO karena WS sudah sampai marker (laravel_nds.marker_input / marker_input_detail aktif).
     * Kembalian null bila belum sampai marker, atau info marker paling awal (created_by, created_at).
     */
    private function soMarkerLock($so): ?array
    {
        $soDetIds = DB::connection('mysql_sb')->table('so_det')->where('id_so', $so->id)->pluck('id');

        $marker = DB::table('marker_input as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.created_by')
            ->where('m.cancel', '!=', 'Y')
            ->where(function ($query) use ($so, $soDetIds) {
                $query->where('m.act_costing_id', $so->id_cost);
                if ($soDetIds->isNotEmpty()) {
                    $query->orWhereIn('m.id', function ($sub) use ($soDetIds) {
                        $sub->select('marker_id')->from('marker_input_detail')
                            ->where('cancel', '!=', 'Y')
                            ->whereIn('so_det_id', $soDetIds);
                    });
                }
            })
            ->orderBy('m.created_at')
            ->orderBy('m.id')
            ->select('m.id', 'm.kode', 'm.created_at', DB::raw('COALESCE(NULLIF(m.created_by_username, ""), u.name, u.username) as created_by'))
            ->first();

        if (!$marker && $soDetIds->isNotEmpty()) {
            // Detail marker tanpa header aktif tetap dianggap sudah sampai marker.
            $detail = DB::table('marker_input_detail')->where('cancel', '!=', 'Y')->whereIn('so_det_id', $soDetIds)
                ->orderBy('created_at')->first();
            if ($detail) {
                $marker = (object) ['id' => $detail->marker_id, 'kode' => null, 'created_at' => $detail->created_at, 'created_by' => null];
            }
        }

        if (!$marker) {
            return null;
        }

        return [
            'kode' => $marker->kode,
            'created_by' => $marker->created_by ?: '-',
            'created_at' => $marker->created_at ? date('d/m/Y', strtotime($marker->created_at)) : '-',
        ];
    }

    /** Market: wajib, tanpa spasi di awal/akhir atau spasi ganda (berpengaruh ke produksi). */
    private static function soMarketError($market): ?string
    {
        if (!is_string($market) || $market === '') {
            return 'Market wajib diisi.';
        }
        if (self::badSpacing($market)) {
            return 'Market tidak boleh berspasi di awal/akhir atau berspasi ganda.';
        }
        if (mb_strlen($market) > 60) {
            return 'Market maksimal 60 karakter.';
        }
        return null;
    }

    private static function soDestinationError($destination): ?string
    {
        if ($destination === null || $destination === '') {
            return null;
        }
        if (!is_string($destination) || self::badSpacing($destination)) {
            return 'Destination tidak boleh berspasi di awal/akhir atau berspasi ganda.';
        }
        if (mb_strlen($destination) > 60) {
            return 'Destination maksimal 60 karakter.';
        }
        return null;
    }

    private static function soQtyError($qty): ?string
    {
        $valid = (is_int($qty) || (is_string($qty) && preg_match('/^\d+$/', $qty)) || (is_float($qty) && floor($qty) == $qty)) && (int) $qty > 0;
        return $valid ? null : 'Qty harus angka bulat lebih dari 0.';
    }

    private static function soFobError($fob): ?string
    {
        return $fob === null || $fob === '' || is_numeric($fob) ? null : 'FOB harus angka.';
    }

    /** Halaman Edit Sales Order: ubah Qty/Market/FOB, tambah dan hapus detail selama WS belum sampai marker. */
    public function editPage($id)
    {
        $db = DB::connection('mysql_sb');
        $so = $db->table('so')->where('id', $id)->first();
        abort_if(!$so || $so->cancel_h === 'Y', 404);

        $costing = $db->table('act_costing')->where('id', $so->id_cost)->select('id', 'cost_no', 'kpno', 'deldate')->first();

        $sizeOrder = $db->table('master_size_new')->whereNotNull('size')->orderBy('urutan')->get(['id', 'size', 'urutan']);
        $sizeUrutan = [];
        foreach ($sizeOrder as $size) {
            $key = strtoupper(trim($size->size));
            if (!isset($sizeUrutan[$key])) {
                $sizeUrutan[$key] = (int) $size->urutan;
            }
        }

        $rows = $this->soEditRows($db, $so, $sizeUrutan);

        return view('marketing.so.edit_detail', [
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-so',
            'containerFluid' => true,
            'so' => $so,
            'costing' => $costing,
            // Sementara dimatikan: DB local tidak sinkron (marker sudah Sep 2026, so_det masih Feb). Aktifkan lagi sebelum naik.
            // 'markerLock' => $this->soMarkerLock($so),
            'markerLock' => null,
            'soRows' => $rows,
            'sizeUrutan' => $sizeUrutan,
            'masterColors' => $db->table('master_colors_gmt')->whereNotNull('name')->orderBy('name')->get(['id', 'name']),
            'masterSizes' => $sizeOrder,
        ]);
    }

    /**
     * Baris detail SO untuk halaman Edit, termasuk baris yang sudah soft delete.
     */
    private function soEditRows($db, $so, array $sizeUrutan): array
    {
        // 1 baris = 1 destination (so_destination); qty so_det = jumlah qty destination-nya.
        $this->ensureSoDestinations($db, (int) $so->id);
        $details = $this->soDestinationLines($db, (int) $so->id);

        $ppicLocked = DB::table('ppic_master_so')->whereIn('id_so_det', $details->pluck('id_so_det')->unique())->pluck('id_so_det')
            ->map(fn ($soDetId) => (int) $soDetId)->flip();

        $rows = $details->map(fn ($d) => [
            'id' => (int) $d->id,
            'id_so_det' => (int) $d->id_so_det,
            'color' => $d->color,
            'size' => $d->size,
            'id_color' => $d->id_color !== null ? (int) $d->id_color : null,
            'id_size' => $d->id_size !== null ? (int) $d->id_size : null,
            'qty' => $d->qty !== null ? (float) $d->qty : null,
            'market' => $d->market,
            'destination' => $d->destination,
            'fob' => $d->fob !== null ? (float) $d->fob : null,
            'ex_fty' => $d->deldate_det ? date('d/m/Y', strtotime($d->deldate_det)) : null,
            'notes' => $d->notes,
            'product_set' => $d->product_set,
            'urutan' => $d->urutan !== null ? (int) $d->urutan : ($sizeUrutan[strtoupper(trim((string) $d->size))] ?? 9999),
            'deleted' => $d->line_cancel === 'Y' || $d->det_cancel === 'Y',
            // 'locked' => isset($ppicLocked[(int) $d->id_so_det]),
            'locked' => false,
        ])->values()->all();

        return $rows;
    }

    /**
     * Simpan semua perubahan Edit Sales Order sekaligus.
     * Payload: updates[] = {id, qty, market, fob}, adds[] = {id_color, id_size, qty, market, fob}, deletes[] = id so_det.
     * Hapus = soft cancel (cancel = 'Y'); so.qty dihitung ulang dari detail aktif.
     */
    public function updateDetails(Request $request, $id)
    {
        $db = DB::connection('mysql_sb');
        $so = $db->table('so')->where('id', $id)->first();
        if (!$so || $so->cancel_h === 'Y') {
            return response()->json(['status' => 404, 'message' => 'Sales Order tidak ditemukan.'], 404);
        }
        // Sementara dimatikan: DB local tidak sinkron (marker sudah Sep 2026, so_det masih Feb). Aktifkan lagi sebelum naik.
        // if ($marker = $this->soMarkerLock($so)) {
        //     return response()->json([
        //         'status' => 409,
        //         'message' => "WS sudah sampai marker (dibuat {$marker['created_by']}, {$marker['created_at']}). Sales Order tidak bisa diubah.",
        //     ], 409);
        // }

        $request->validate([
            'updates' => ['nullable', 'array'],
            'updates.*.id' => ['required', 'integer'],
            'adds' => ['nullable', 'array'],
            'adds.*.id_color' => ['required', 'integer'],
            'adds.*.id_size' => ['required', 'integer'],
            'deletes' => ['nullable', 'array'],
            'deletes.*' => ['integer'],
            'restores' => ['nullable', 'array'],
            'restores.*' => ['integer'],
        ]);

        $updates = collect($request->input('updates', []));
        $adds = collect($request->input('adds', []));
        $deletes = collect($request->input('deletes', []))->map(fn ($v) => (int) $v)->unique()->values();
        $restores = collect($request->input('restores', []))->map(fn ($v) => (int) $v)->unique()->values();
        if ($updates->isEmpty() && $adds->isEmpty() && $deletes->isEmpty() && $restores->isEmpty()) {
            return response()->json(['status' => 422, 'message' => 'Tidak ada perubahan untuk disimpan.'], 422);
        }

        // Baris di halaman Edit = 1 baris so_destination (so_det x destination); id di payload = id so_destination.
        $this->ensureSoDestinations($db, (int) $so->id);
        $lines = $this->soDestinationLines($db, (int) $so->id)->keyBy('id');
        $isCancelled = fn ($line) => $line->line_cancel === 'Y' || $line->det_cancel === 'Y';
        $active = $lines->reject($isCancelled);
        $cancelled = $lines->filter($isCancelled);
        $dets = $db->table('so_det')->where('id_so', $so->id)->get()->keyBy('id');
        $activeDets = $dets->filter(fn ($det) => $det->cancel !== 'Y');

        $errors = [];
        $restoredRows = $cancelled->only($restores->all());
        foreach ($restores as $restoreId) {
            if (!$cancelled->has($restoreId)) {
                $errors[] = "Detail #{$restoreId} tidak ditemukan sebagai detail yang sudah dihapus.";
            }
        }
        $editable = $active->union($restoredRows);
        // Sementara dimatikan: DB local tidak sinkron (ppic_master_so). Aktifkan lagi sebelum naik.
        // $ppicLocked = DB::table('ppic_master_so')->whereIn('id_so_det', $dets->keys())->pluck('id_so_det')
        //     ->map(fn ($soDetId) => (int) $soDetId)->flip();
        $ppicLocked = collect();

        $fobOf = fn ($fob) => $fob === null || $fob === '' ? null : round((float) $fob, 3);
        // Hasil akhir per baris destination.
        $lineState = fn ($line, bool $touched) => [
            'line' => (int) $line->id, 'det' => (int) $line->id_so_det, 'template' => (int) $line->id_so_det,
            'id_color' => $line->id_color, 'id_size' => $line->id_size, 'color' => $line->color, 'size' => $line->size,
            'market' => $line->market, 'destination' => $line->destination, 'qty' => (float) $line->qty, 'fob' => $fobOf($line->fob),
            'product_set' => $line->product_set, 'deldate' => $line->deldate_det, 'touched' => $touched,
        ];
        $final = [];
        foreach ($active as $line) {
            $final['d' . $line->id] = $lineState($line, false);
        }
        foreach ($restoredRows as $line) {
            $final['d' . $line->id] = $lineState($line, true);
        }

        foreach ($deletes as $deleteId) {
            if (!$active->has($deleteId)) {
                $errors[] = "Detail #{$deleteId} tidak ada di Sales Order ini atau sudah dihapus.";
            } elseif (isset($ppicLocked[(int) $active[$deleteId]->id_so_det])) {
                $errors[] = "Detail {$active[$deleteId]->color} / {$active[$deleteId]->size} sudah masuk PPIC, tidak bisa dihapus.";
            }
            unset($final['d' . $deleteId]);
        }

        foreach ($updates as $index => $update) {
            $lineId = (int) $update['id'];
            $label = 'Baris ' . ($index + 1);
            if (!$editable->has($lineId)) {
                $errors[] = "{$label}: detail tidak ada di Sales Order ini atau sudah dihapus.";
                continue;
            }
            $line = $editable[$lineId];
            $label = "{$line->color} / {$line->size}";
            if ($deletes->contains($lineId)) {
                continue;
            }
            if (isset($ppicLocked[(int) $line->id_so_det])) {
                $errors[] = "{$label}: sudah masuk PPIC, tidak bisa diubah.";
                continue;
            }
            $market = $update['market'] ?? null;
            $destination = $update['destination'] ?? null;
            foreach ([self::soQtyError($update['qty'] ?? null), self::soMarketError($market), self::soDestinationError($destination), self::soFobError($update['fob'] ?? null)] as $error) {
                if ($error) $errors[] = "{$label}: {$error}";
            }
            $final['d' . $lineId] = array_merge($final['d' . $lineId], [
                'qty' => (float) (int) ($update['qty'] ?? 0),
                'market' => $market,
                'destination' => $destination,
                'fob' => $fobOf($update['fob'] ?? null),
                'touched' => true,
            ]);
        }

        $colors = $db->table('master_colors_gmt')->whereIn('id', $adds->pluck('id_color')->filter())->pluck('name', 'id');
        $sizes = $db->table('master_size_new')->whereIn('id', $adds->pluck('id_size')->filter())->pluck('size', 'id');
        foreach ($adds as $index => $add) {
            $label = 'Add New ' . ($index + 1);
            $colorName = $colors[(int) $add['id_color']] ?? null;
            $sizeName = $sizes[(int) $add['id_size']] ?? null;
            if ($colorName === null) $errors[] = "{$label}: color tidak ada di master.";
            if ($sizeName === null) $errors[] = "{$label}: size tidak ada di master.";
            $market = $add['market'] ?? null;
            $destination = $add['destination'] ?? null;
            foreach ([self::soQtyError($add['qty'] ?? null), self::soMarketError($market), self::soDestinationError($destination), self::soFobError($add['fob'] ?? null)] as $error) {
                if ($error) $errors[] = "{$label}: {$error}";
            }
            // Set & ex fty disalin dari detail aktif SO ini (utamakan color yang sama).
            $template = $activeDets->first(fn ($det) => (int) $det->id_color === (int) $add['id_color'] || strtoupper((string) $det->color) === strtoupper((string) $colorName))
                ?? $activeDets->first() ?? $dets->first();
            $final['n' . $index] = [
                'line' => null, 'det' => null, 'template' => $template->id ?? null,
                'id_color' => (int) $add['id_color'], 'id_size' => (int) $add['id_size'], 'color' => $colorName, 'size' => $sizeName,
                'market' => $market, 'destination' => $destination, 'qty' => (float) (int) ($add['qty'] ?? 0), 'fob' => $fobOf($add['fob'] ?? null),
                'product_set' => $template->product_set ?? null, 'deldate' => $template->deldate_det ?? null, 'touched' => true,
            ];
        }

        // Kombinasi color + size + market + destination (+ set) tidak boleh dobel (hanya dicek untuk baris yang diubah/ditambah).
        $groups = [];
        foreach ($final as $item) {
            $key = strtoupper(trim((string) $item['color'])) . '|' . strtoupper(trim((string) $item['size'])) . '|' . strtoupper((string) $item['market'])
                . '|' . strtoupper((string) $item['destination']) . '|' . self::soNormSet($item['product_set']);
            $groups[$key][] = $item;
        }
        foreach ($groups as $items) {
            if (count($items) > 1 && collect($items)->contains('touched', true)) {
                $errors[] = "{$items[0]['color']} / {$items[0]['size']} / {$items[0]['market']} / {$items[0]['destination']} sudah ada, ubah qty di tabel.";
            }
        }

        if ($errors) {
            return response()->json(['status' => 422, 'message' => implode("\n", array_unique($errors)), 'errors' => array_values(array_unique($errors))], 422);
        }

        $userName = auth()->user()->name ?? auth()->user()->username ?? null;
        try {
            $db->transaction(function () use ($db, $so, $lines, $dets, $final, $deletes, $updates, $adds, $restores, $userName) {
                $now = now();
                $touched = [];

                // so_det = 1 baris per color + size + market + set + ex fty; qty = jumlah qty semua destination-nya.
                // FOB disimpan per destination; so_det.fob = rata-rata FOB tertimbang qty.
                $detKey = fn (array $item) => json_encode([
                    (int) $item['id_color'], (int) $item['id_size'], strtoupper(trim((string) $item['market'])),
                    self::soNormSet($item['product_set']), (string) $item['deldate'],
                ]);
                $currentKey = fn ($det) => $detKey([
                    'id_color' => $det->id_color, 'id_size' => $det->id_size, 'market' => $det->market, 'product_set' => $det->product_set,
                    'deldate' => $det->deldate_det,
                ]);
                $groups = collect($final)->groupBy($detKey);

                // Pakai ulang id so_det lama sebisa mungkin (id dipakai marker/PPIC):
                // 1) so_det yang kuncinya tidak berubah, 2) so_det lama dari baris yang kuncinya berubah (mis. market diganti).
                $assigned = [];
                $claimed = [];
                foreach ([true, false] as $sameKeyOnly) {
                    foreach ($groups as $key => $items) {
                        if (isset($assigned[$key])) continue;
                        foreach ($items as $item) {
                            $detId = $item['det'];
                            if ($detId && !isset($claimed[$detId]) && (!$sameKeyOnly || $currentKey($dets[$detId]) === $key)) {
                                $assigned[$key] = $detId;
                                $claimed[$detId] = true;
                                break;
                            }
                        }
                    }
                }

                foreach ($groups as $key => $items) {
                    $first = $items->first();
                    $fields = [
                        'qty' => $items->sum('qty'),
                        'market' => $first['market'],
                        'fob' => self::soAverageFob($items),
                        'dest' => '-',
                        'cancel' => 'N',
                    ];
                    if (isset($assigned[$key])) {
                        $detId = $assigned[$key];
                        $det = $dets[$detId];
                        if ((float) $det->qty !== (float) $fields['qty'] || (string) $det->market !== (string) $fields['market']
                            || ($det->fob === null ? null : round((float) $det->fob, 3)) !== $fields['fob']
                            || (string) $det->dest !== '-' || $det->cancel === 'Y') {
                            $db->table('so_det')->where('id', $detId)->update($fields + ['updated_by' => $userName, 'updated_date' => $now]);
                            $touched[] = $detId;
                        }
                    } else {
                        $template = $dets[$first['det'] ?? $first['template']] ?? $dets->first();
                        $detId = $db->table('so_det')->insertGetId($fields + [
                            'id_so' => $so->id,
                            'color' => $first['color'],
                            'size' => $first['size'],
                            'id_color' => $first['id_color'],
                            'id_size' => $first['id_size'],
                            'unit' => $template->unit ?? ($so->unit ?: 'PCS'),
                            'price' => $template->price ?? 0,
                            'deldate_det' => $first['deldate'],
                            'styleno_prod' => $template->styleno_prod ?? null,
                            'reff_no' => $template->reff_no ?? null,
                            'product_set' => $first['product_set'],
                            'sku' => '-',
                            'created_by' => $userName,
                            'created_date' => $now,
                        ]);
                        $touched[] = $detId;
                    }

                    foreach ($items as $item) {
                        $destRow = ['id_so_det' => $detId, 'destination' => $item['destination'] !== '' ? $item['destination'] : null, 'qty' => $item['qty'], 'fob' => $item['fob'], 'cancel' => 'N'];
                        if (!$item['line']) {
                            $db->table('so_destination')->insert($destRow + ['created_by' => $userName, 'created_at' => $now]);
                            continue;
                        }
                        $old = $lines[$item['line']];
                        if ((int) $old->id_so_det !== (int) $detId || (string) $old->destination !== (string) $destRow['destination']
                            || (float) $old->qty !== (float) $destRow['qty'] || ($old->line_fob === null ? null : round((float) $old->line_fob, 3)) !== $destRow['fob'] || $old->line_cancel === 'Y') {
                            $db->table('so_destination')->where('id', $item['line'])->update($destRow + ['updated_by' => $userName, 'updated_at' => $now]);
                        }
                    }
                }

                if ($deletes->isNotEmpty()) {
                    $db->table('so_destination')->whereIn('id', $deletes)->update(['cancel' => 'Y', 'updated_by' => $userName, 'updated_at' => $now]);
                }
                // so_det yang tidak punya destination aktif lagi = soft delete.
                foreach ($dets as $detId => $det) {
                    if ($det->cancel !== 'Y' && !isset($claimed[$detId])) {
                        $db->table('so_det')->where('id', $detId)->update(['cancel' => 'Y', 'updated_by' => $userName, 'updated_date' => $now]);
                        $touched[] = $detId;
                    }
                }

                $totalQty = $db->table('so_det')->where('id_so', $so->id)
                    ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))
                    ->sum('qty');
                $db->table('so')->where('id', $so->id)->update(['qty' => $totalQty]);

                $this->logSo($so->id);
                if ($touched) {
                    $this->logSoDet(array_values(array_unique($touched)));
                }
                if ($so->id_bom) {
                    app(MarketingBomService::class)->refreshColorsSizes((int) $so->id_bom);
                }

                app(MarketingHistoryService::class)->soUpdated((int) $so->id_cost, (int) $so->id, (string) $so->so_no, [
                    'updated' => $updates->count(),
                    'added' => $adds->count(),
                    'deleted' => $deletes->count(),
                    'restored' => $restores->count(),
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Gagal update detail SO.', ['id_so' => $so->id, 'error' => $e->getMessage()]);
            return response()->json(['status' => 500, 'message' => 'Gagal menyimpan Sales Order: ' . $e->getMessage()], 500);
        }

        $sizeUrutan = [];
        foreach ($db->table('master_size_new')->whereNotNull('size')->orderBy('urutan')->get(['size', 'urutan']) as $size) {
            $sizeUrutan[strtoupper(trim($size->size))] ??= (int) $size->urutan;
        }

        // Kirim balik detail terbaru supaya halaman langsung sinkron (baris NEW jadi baris biasa dengan id so_destination).
        return response()->json([
            'status' => 200,
            'message' => "Sales Order {$so->so_no} berhasil diupdate.",
            'rows' => $this->soEditRows($db, $so, $sizeUrutan),
        ]);
    }

    /** Rata-rata FOB tertimbang qty dari baris destination ({qty, fob}); null kalau semua FOB kosong. */
    private static function soAverageFob($items): ?float
    {
        $items = collect($items)->map(fn ($item) => (array) $item)->filter(fn ($item) => $item['fob'] !== null && $item['fob'] !== '');
        if ($items->isEmpty()) {
            return null;
        }
        $qty = $items->sum(fn ($item) => (float) $item['qty']);
        return round($qty > 0
            ? $items->sum(fn ($item) => (float) $item['qty'] * (float) $item['fob']) / $qty
            : $items->avg(fn ($item) => (float) $item['fob']), 3);
    }

    /** Product set kosong / '-' / 'null' dianggap tanpa set. */
    private static function soNormSet($set): string
    {
        $set = trim((string) $set);
        return ($set === '' || $set === '-' || strtolower($set) === 'null') ? '' : strtoupper($set);
    }

    /**
     * Simpan baris upload (1 baris per destination) ke so_det + so_destination.
     * Baris dengan market + set + color + size + ex fty sama digabung jadi 1 so_det (qty dijumlah),
     * qty & FOB per destination masuk so_destination; so_det.fob = rata-rata FOB tertimbang qty.
     * so_det.dest tidak dipakai lagi (diisi '-').
     */
    private function insertSoDetailsWithDestinations($db, $lines, callable $soDetRow, ?string $userName): void
    {
        $now = now();
        $groups = collect($lines)->groupBy(fn ($line) => json_encode([
            strtoupper(trim((string) $line->market)), self::soNormSet($line->product_set ?? null),
            (string) $line->id_color, (string) $line->size,
            $line->ex_fty ? date('Y-m-d', strtotime($line->ex_fty)) : '',
        ]));
        foreach ($groups as $group) {
            $idSoDet = $db->table('so_det')->insertGetId(array_merge($soDetRow($group->first()), [
                'qty' => $group->sum(fn ($line) => (float) $line->qty),
                'fob' => self::soAverageFob($group->map(fn ($line) => ['qty' => (float) $line->qty, 'fob' => $line->fob])),
                'dest' => '-',
            ]));
            $db->table('so_destination')->insert($group->groupBy(fn ($line) => trim((string) $line->destination))
                ->map(fn ($items, $dest) => [
                    'id_so_det' => $idSoDet,
                    'destination' => $dest !== '' ? $dest : null,
                    'qty' => $items->sum(fn ($line) => (float) $line->qty),
                    'fob' => $items->first()->fob === null || $items->first()->fob === '' ? null : round((float) $items->first()->fob, 3),
                    'cancel' => 'N',
                    'created_by' => $userName,
                    'created_at' => $now,
                ])->values()->all());
        }
    }

    /**
     * so_det lama (sebelum ada so_destination, atau dibuat dari menu lain) diberi 1 baris destination dari so_det.dest.
     * so_det dengan 1 destination aktif yang qty-nya beda (diubah dari menu lain) disamakan.
     */
    private function ensureSoDestinations($db, int $soId): void
    {
        $now = now();
        $missing = $db->table('so_det as sd')->where('sd.id_so', $soId)
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))->from('so_destination as x')->whereColumn('x.id_so_det', 'sd.id'))
            ->get(['sd.id', 'sd.dest', 'sd.qty', 'sd.fob', 'sd.cancel']);
        if ($missing->isNotEmpty()) {
            $db->table('so_destination')->insert($missing->map(function ($det) use ($now) {
                $dest = trim((string) $det->dest);
                return [
                    'id_so_det' => $det->id,
                    'destination' => $dest === '' || $dest === '-' ? null : $dest,
                    'qty' => (float) $det->qty,
                    'fob' => $det->fob,
                    'cancel' => $det->cancel === 'Y' ? 'Y' : 'N',
                    'created_by' => 'sync',
                    'created_at' => $now,
                ];
            })->all());
        }

        $single = $db->table('so_destination as d')->join('so_det as sd', 'sd.id', '=', 'd.id_so_det')
            ->where('sd.id_so', $soId)->where('d.cancel', 'N')
            ->groupBy('d.id_so_det', 'sd.qty')
            ->havingRaw('COUNT(*) = 1 AND MAX(d.qty) <> COALESCE(sd.qty, 0)')
            ->get(['d.id_so_det', 'sd.qty']);
        foreach ($single as $det) {
            $db->table('so_destination')->where('id_so_det', $det->id_so_det)->where('cancel', 'N')
                ->update(['qty' => (float) $det->qty, 'updated_by' => 'sync', 'updated_at' => $now]);
        }
    }

    /** Baris so_det x destination untuk satu SO (termasuk yang sudah soft delete). */
    private function soDestinationLines($db, int $soId)
    {
        return $db->table('so_destination as d')
            ->join('so_det as sd', 'sd.id', '=', 'd.id_so_det')
            ->leftJoin('master_size_new as s', 'sd.id_size', '=', 's.id')
            ->where('sd.id_so', $soId)
            ->select('d.id', 'd.id_so_det', 'd.destination', 'd.qty', 'd.cancel as line_cancel', 'sd.cancel as det_cancel',
                'sd.color', 'sd.size', 'sd.id_color', 'sd.id_size', 'sd.market', DB::raw('COALESCE(d.fob, sd.fob) as fob'), 'd.fob as line_fob', 'sd.product_set', 'sd.deldate_det', 'sd.notes', 's.urutan')
            ->orderBy('sd.color')
            ->orderBy('sd.id')
            ->orderBy('d.id')
            ->get();
    }

    private function logSo($id_so) {
        $so = DB::connection('mysql_sb')->table('so')->where('id', $id_so)->first();
        if ($so) {
            $log_data = (array) $so;
            unset($log_data['id']);
            unset($log_data['update_by']);
            unset($log_data['updated_by']);
            unset($log_data['updated_at']);
            unset($log_data['updated_date']);
            unset($log_data['fob']);
            DB::connection('mysql_sb')->table('so_log')->insert($log_data);
        }
    }

    private function logSoDet($id_so_det) {
        if (is_array($id_so_det)) {
            $details = DB::connection('mysql_sb')->table('so_det')->whereIn('id', $id_so_det)->get();
            if ($details->isNotEmpty()) {
                $log_data = [];
                foreach ($details as $det) {
                    $arr = (array) $det;
                    unset($arr['id']);
                    unset($arr['product_set']);
                    $log_data[] = $arr;
                }
                DB::connection('mysql_sb')->table('so_det_log')->insert($log_data);
            }
        } else {
            $det = DB::connection('mysql_sb')->table('so_det')->where('id', $id_so_det)->first();
            if ($det) {
                $arr = (array) $det;
                unset($arr['id']);
                unset($arr['product_set']);
                DB::connection('mysql_sb')->table('so_det_log')->insert($arr);
            }
        }
    }

    private function logAllSoDet($id_so) {
        $details = DB::connection('mysql_sb')->table('so_det')->where('id_so', $id_so)->get();
        if ($details->isNotEmpty()) {
            $log_data = [];
            foreach ($details as $det) {
                $arr = (array) $det;
                unset($arr['id']);
                unset($arr['product_set']);
                $log_data[] = $arr;
            }
            DB::connection('mysql_sb')->table('so_det_log')->insert($log_data);
        }
    }
}
