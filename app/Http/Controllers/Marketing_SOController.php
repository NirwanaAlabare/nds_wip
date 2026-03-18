<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\File;

class Marketing_SOController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $mysql_sb = DB::connection('mysql_sb');

            $query = $mysql_sb->table('so')
                ->leftJoin('act_costing as act', 'so.id_cost', '=', 'act.id')
                ->join('mastersupplier as ms', 'act.id_buyer', '=', 'ms.Id_Supplier')
                ->join('masterproduct as mp', 'act.id_product', '=', 'mp.id')
                ->select([
                    'so.id',
                    'so.d_insert',
                    'so.so_no',
                    'so.no_po',
                    'so.qty',
                    'act.kpno',
                    'act.styleno',
                    'ms.Supplier as buyer',
                    'mp.product_group',
                    'mp.product_item'
                ]);

            if ($request->date_from && $request->date_to) {
                $query->whereBetween(DB::raw('DATE(so.d_insert)'), [$request->date_from, $request->date_to]);
            }


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
                        $base_url = 'http://10.10.5.62:8080';
                    }

                    $url_pdf = $base_url . '/erp/pages/marketting/pdfSO.php?id=' . $row->id;

                    $url_pdf_so = route('print-pdf-so', $row->id);

                   return '
                        <button class="btn btn-info btn-sm" onclick="showDetail('.$row->id.')" title="Detail SO">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="showDetailMaterial('.$row->id.')" title="Detail Material">
                            <i class="fas fa-boxes"></i>
                        </button>
                        <a href="'.$url_pdf.'" target="_blank" class="btn btn-danger btn-sm" title="Cetak PDF SO">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <a href="'.$url_pdf_so.'" target="_blank" class="btn btn-success btn-sm" title="Cetak PDF SO Market">
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

    public function create()
    {
        $mysql_sb = DB::connection('mysql_sb');

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
        $bom_catalog = $mysql_sb->table('bom_marketing')
            ->select('id', 'no_katalog_bom', 'style')
            ->whereNotNull('no_katalog_bom')
            ->orderBy('created_at', 'desc')
            ->get();

        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();
        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();
        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();


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
            'master_colors' => $master_colors,
            'master_sizes' => $master_sizes,
            'containerFluid' => true
        ]);
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
            ->select('id_contents', 'id_color', 'id_size')
            ->get();

        return response()->json([
            'colors' => $colors,
            'sizes' => $sizes,
            'existing' => $existing
        ]);
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

    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
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

    public function uploadExcelSO(Request $request)
    {
        $request->validate(['file_so' => 'required|mimes:xls,xlsx', 'id_bom' => 'required']);
        $user_id = auth()->id();
        $file = $request->file('file_so');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $data = $spreadsheet->getActiveSheet()->toArray();
        $headers = $data[0];

        $temp_data = []; $errors_color = []; $errors_size = [];
        $mysql_sb = DB::connection('mysql_sb');
        $master_colors = $mysql_sb->table('master_colors_gmt')->pluck('id', 'name')->toArray();
        $master_sizes = $mysql_sb->table('master_size_new')->pluck('id', 'size')->toArray();

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (empty($row[0])) continue;

            $style = trim($row[0]);
            $po    = trim($row[2]);
            $color_name = trim($row[5]);

            if (!isset($master_colors[$color_name])) {
                $errors_color[] = "Warna: <b>$color_name</b> tidak ada di Master."; continue;
            }

            for ($col_index = 6; $col_index < count($row); $col_index++) {
                $size_name = trim($headers[$col_index]);
                if (empty($size_name)) continue;

                if (!isset($master_sizes[$size_name])) {
                    $errors_size[] = "Size: <b>$size_name</b> tidak ada di Master."; continue;
                }

                $qty = $row[$col_index];
                if (!empty($qty) && is_numeric($qty) && $qty > 0) {
                    $temp_data[] = [
                        'user_id'    => $user_id,
                        'style'      => $style,
                        'desc'       => trim($row[1]),
                        'po'         => $po,
                        'market'     => trim($row[3]),
                        'ex_fty'     => trim($row[4]),
                        'id_color'   => $master_colors[$color_name],
                        'size'       => $master_sizes[$size_name],
                        'qty'        => $qty,
                        'created_at' => now()
                    ];
                }
            }
        }

        if (count($errors_color) > 0) return response()->json(['status' => 422, 'errors' => array_unique($errors_color)], 422);
        if (count($errors_size) > 0) return response()->json(['status' => 422, 'errors' => array_unique($errors_size)], 422);

        $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();
        if (count($temp_data) > 0) {
            foreach (array_chunk($temp_data, 500) as $chunk) {
                $mysql_sb->table('temp_so_detail')->insert($chunk);
            }
        }
        return response()->json(['status' => 200, 'message' => 'Excel berhasil diproses ke Temp.']);
    }

    public function getTempData(Request $request)
    {
        $user_id = auth()->id();
        $id_bom = $request->id_bom;
        $mysql_sb = DB::connection('mysql_sb');

        // Ambil Data Header BOM
        $bom_header = $mysql_sb->table('bom_marketing')->where('id', $id_bom)->first();
        $allowed_colors = $bom_header && $bom_header->colors ? json_decode($bom_header->colors, true) : [];
        $allowed_sizes  = $bom_header && $bom_header->sizes ? json_decode($bom_header->sizes, true) : [];

        $bom_details = $mysql_sb->table('bom_marketing_detail')->where('id_bom_marketing', $id_bom)->get();

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
                't.style', 't.desc', 't.po', 't.market', 't.ex_fty',
                't.id_color', 'c.name as color_name',
                't.size as id_size', 's.size as size_name', 's.urutan as size_urutan',
                't.qty'
            )
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
            $key = $row->style . '_' . $row->po . '_' . $row->id_color;

            if (!isset($pivot_data[$key])) {
                $pivot_data[$key] = [
                    'style'    => $row->style,
                    'desc'     => $row->desc,
                    'po'       => $row->po,
                    'market'   => $row->market,
                    'ex_fty'   => $row->ex_fty,
                    'id_color' => $row->id_color,
                    'color'    => $row->color_name,
                    'errors'   => [],
                    'id_sizes' => [],
                ];
            }

            $pivot_data[$key][$row->size_name] = $row->qty;
            $pivot_data[$key]['id_sizes'][$row->size_name] = $row->id_size;

            if (!isset($available_sizes_raw[$row->size_name])) {
                $available_sizes_raw[$row->size_name] = $row->size_urutan ?? 999;
            }

            $total_qty += $row->qty;
            $unique_po[$row->po] = true;
        }

        asort($available_sizes_raw);
        $available_sizes_array = array_keys($available_sizes_raw);

        $has_bom_error = false;

        if (count($missing_colors_names) > 0 || count($missing_sizes_names) > 0) {
            $has_bom_error = true;
        }

        foreach ($pivot_data as &$row) {
            $id_color = (string)$row['id_color'];

            $in_header_color = in_array($id_color, $allowed_colors);
            $row['color_error'] = !$in_header_color;

            foreach ($available_sizes_array as $size_name) {
                if (isset($row[$size_name])) {
                    $id_size = (string)$row['id_sizes'][$size_name];
                    $in_header_size = in_array($id_size, $allowed_sizes);
                    $is_valid = $in_header_color && $in_header_size;

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

        return response()->json([
            'data'            => array_values($pivot_data),
            'available_sizes' => $available_sizes_array,
            'total_qty'       => $total_qty,
            'jumlah_po'       => count($unique_po),
            'has_bom_error'   => $has_bom_error,
            'missing_colors'  => $missing_colors_names,
            'missing_sizes'   => $missing_sizes_names
        ]);
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

    private function generate_kode($id_buyer)
    {

        $mysql_sb = DB::connection('mysql_sb');
        $now = now();
        $bln_thn = $now->format('my');

        $last_costing = $mysql_sb->table('act_costing')
            ->where('cost_no', 'like', "CST/$bln_thn/%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last_costing) {
            $parts = explode('/', $last_costing->cost_no);
            $last_number = (int) end($parts);
            $next_costing = $last_number + 1;
        } else {
            $next_costing = 1;
        }

        $cost_no = "CST/$bln_thn/" . str_pad($next_costing, 5, '0', STR_PAD_LEFT);

        // get untuk kpno/ws
        $buyer = $mysql_sb->table('mastersupplier')->where('Id_Supplier', $id_buyer)->first();
        $kode_supplier = $buyer ? $buyer->supplier_code : '';
        $last_kpno = $mysql_sb->table('act_costing')
            ->where('kpno', 'like', "$kode_supplier/$bln_thn/%")
            ->orderBy('id', 'desc')->first();
        $next_kpno = $last_kpno ? (int)substr($last_kpno->kpno, -3) + 1 : 1;
        $kpno = "$kode_supplier/$bln_thn/" . str_pad($next_kpno, 3, '0', STR_PAD_LEFT);


         // get untuk no so
        $last_no_so = $mysql_sb->table('so')
            ->where('so_no', 'like', "SO/$bln_thn/%")
            ->orderBy('so_no', 'desc')
            ->first();

        if ($last_no_so) {
            $parts = explode('/', $last_no_so->so_no);
            $next_so = (int)end($parts) + 1;
        } else {
            $next_so = 1;
        }

        $so_no = "SO/$bln_thn/" . str_pad($next_so, 5, '0', STR_PAD_LEFT);

        return compact('cost_no', 'kpno', 'so_no');
    }

    public function store(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $username = auth()->user()->name;
        $user_id = auth()->id();

        $temp_data = $mysql_sb->table('temp_so_detail')
            ->where('user_id', $user_id)
            ->get()
            ->groupBy('po');

        if ($temp_data->isEmpty()) return response()->json(['message' => 'Data Kosong!'], 400);

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

            foreach ($temp_data as $no_po => $details) {
                $total_qty_po = $details->sum('qty');
                $kode = $this->generate_kode($request->id_buyer);

                $id_cost = $mysql_sb->table('act_costing')->insertGetId([
                    'cost_no'     => $kode['cost_no'],
                    'cost_date'   => now(),
                    'kpno'        => $kode['kpno'],
                    'id_buyer'    => $request->id_buyer,
                    'styleno'     => $request->style,
                    'qty'         => $total_qty_po,
                    'curr'        => $request->id_currency,
                    'username'    => $username,
                    'brand'       => $request->brand,
                    'smv_min'     => $request->smv,
                    'id_product'  => $request->id_product_item,
                    'notes'       => $request->notes,
                    'vat'         => $request->vat,
                    'ga_cost'     => $request->ga_cost,
                    'comm_cost'   => $request->commission_fee,
                    'cfm_price'   => $request->confirm_price,
                    'mkt_order'   => $request->marketing_order,
                    'dateinput'   => now(),
                    'aktif'       => 'Y',

                ]);

                $id_so = $mysql_sb->table('so')->insertGetId([
                    'id_cost'  => $id_cost,
                    'buyerno'  => $request->id_buyer,
                    'so_no'    => $kode['so_no'],
                    'no_po'    => $no_po,
                    'so_date'  => now(),
                    'curr'     => $request->id_currency,
                    'qty'      => $total_qty_po,
                    'username' => $username,
                    'd_insert' => now(),
                    'id_bom'   => $request->id_bom,
                    'market'   => $request->market,
                    'nm_file'  => $file_name,

                ]);

                $details_insert = [];
                foreach ($details as $d) {

                    $color = $mysql_sb->table('master_colors_gmt')
                        ->where('id', $d->id_color)
                        ->first();

                    $size = $mysql_sb->table('master_size_new')
                        ->where('id', $d->size)
                        ->first();

                    $details_insert[] = [
                        'id_so'        => $id_so,
                        'color'        => $color->name,
                        'size'         => $size->size,
                        'qty'          => $d->qty,
                        'styleno_prod' => $d->style,
                        'deldate_det'  => date('Y-m-d' , strtotime($d->ex_fty)),
                        'created_by'   => $username,
                        'created_date' => now(),
                        'cancel'       => 'N',
                        'unit'         => 'PCS',
                        'id_color'     => $color->id,
                        'id_size'      => $size->id,
                    ];
                }

                $mysql_sb->table('so_det')->insert($details_insert);
            }

            $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();

            $mysql_sb->commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menyimpan ' . $temp_data->count() . ' PO.'
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

    public function get_detail($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $header = $mysql_sb->table('so')
            ->leftJoin('act_costing as act', 'so.id_cost', '=', 'act.id')
            ->join('mastersupplier as ms', 'act.id_buyer', '=', 'ms.Id_Supplier')
            ->select('so.so_no', 'act.kpno', 'ms.Supplier as buyer', 'act.styleno')
            ->where('so.id', $id)
            ->first();

        if (!$header) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $details = $mysql_sb->table('so_det')
            ->where('id_so', $id)
            ->get();

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

        // Ini memastikan mendapatkan varian unik yang ada di tabel SO
        $so_details = $mysql_sb->table('so_det as sd')
            ->leftJoin('master_colors_gmt as c', 'sd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'sd.id_size', '=', 's.id')
            ->where('sd.id_so', $id)
            ->select(
                'sd.id_color',
                'sd.id_size',
                'c.name as color_name',
                's.size as size_name',
                $mysql_sb->raw('SUM(sd.qty) as qty')
            )
            ->groupBy('sd.id_color', 'sd.id_size', 'c.name', 's.size')
            ->get();

        // Ambil Master BOM (Material)
        $bom_details_raw = $mysql_sb->table('bom_marketing_detail as d')
            ->join('bom_marketing as h', 'd.id_bom_marketing', '=', 'h.id')
            ->leftJoin('masteritem as i', 'd.id_item', '=', 'i.id_item')
            ->leftJoin('mastercontents as e', 'd.id_contents', '=', 'e.id')
            ->leftJoin('mastertype2 as d2', 'e.id_type', '=', 'd2.id')
            ->leftJoin('mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
            ->leftJoin('mastergroup as a', 's_grp.id_group', '=', 'a.id')
            ->leftJoin('masterpilihan as u', 'd.unit', '=', 'u.id')
            ->where('d.id_bom_marketing', $so->id_bom)
            ->select(
                'd.id as detail_id',
                'd.id_item',
                'd.id_contents',
                'd.id_color as bom_id_color',
                'd.id_size as bom_id_size',
                'd.rule_bom',
                $mysql_sb->raw("CONCAT(COALESCE(a.nama_group,''), ' ', COALESCE(s_grp.nama_sub_group,''), ' ', COALESCE(d2.nama_type,''), ' ', COALESCE(e.nama_contents,'')) as panel"),
                'h.market as dest',
                'i.itemdesc as item_desc',
                'd.qty as cons',
                'd.unit as unit',
                'd.notes'
            )
            ->get();

        // Jika ada 2 material yang sama persis dan "All Color All Size", jadikan 1 saja.
        $bom_details = $bom_details_raw->unique(function ($item) {
            if ($item->rule_bom == 'All Color All Size') {
                return $item->id_item . '-' . $item->id_contents;
            }
            return $item->detail_id;
        });

        // MAPPING
        $final_data = [];

        foreach ($bom_details as $bom) {
            foreach ($so_details as $sdet) {
                $is_match = false;

                // Cek pencocokan Rule
                if ($bom->rule_bom == 'All Color All Size') {
                    $is_match = true;
                } elseif ($bom->rule_bom == 'Per Color All Size' && $sdet->id_color == $bom->bom_id_color) {
                    $is_match = true;
                } elseif ($bom->rule_bom == 'All Color Range Size' && $sdet->id_size == $bom->bom_id_size) {
                    $is_match = true;
                } elseif ($sdet->id_color == $bom->bom_id_color && $sdet->id_size == $bom->bom_id_size) {
                    $is_match = true; // Per Color Per Size
                }

                // Jika cocok, buat baris barunya
                if ($is_match) {
                    $final_data[] = [
                        'id_item'    => $bom->id_item,
                        'id_contents'=> $bom->id_contents,
                        'panel'      => $bom->panel,
                        'dest'       => $bom->dest,
                        'color_gmt'  => $sdet->color_name ?? '-',
                        'size_gmt'   => $sdet->size_name ?? '-',
                        'item_desc'  => $bom->item_desc,
                        'qty_gmt'    => (float) $sdet->qty,
                        'cons'       => (float) $bom->cons,
                        'qty_bom'    => (float) ($sdet->qty * $bom->cons),
                        'unit'       => $bom->unit,
                        'notes'      => $bom->notes,
                        'created_by' => '-',
                        'rule_bom'   => $bom->rule_bom,
                        'status'     => '-'
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
        return response()->json(['status' => 200, 'message' => 'Kombinasi Warna & Size BOM Diperbarui!']);
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
                ->orderBy('d.id', 'desc');

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
            return response()->json(['status' => 200, 'message' => 'Material yang belum ada berhasil ditambahkan!']);
        }

        return response()->json(['status' => 200, 'message' => 'Tidak ada material baru yang ditambahkan (Semua sudah terisi).']);
        }

    public function updateQtySO(Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            $data = $request->input('data');

            if (!$data || !is_array($data)) {
                return response()->json(['status' => 400, 'message' => 'Data tidak valid!']);
            }

            $firstDetailId = $data[0]['id'];
            $detailData = DB::connection('mysql_sb')->table('so_det')->where('id', $firstDetailId)->first();
            $so_id = $detailData->id_so;

            foreach ($data as $item) {
                DB::connection('mysql_sb')->table('so_det')
                    ->where('id', $item['id'])
                    ->update(['qty' => $item['qty']]);
            }

            $totalQty = DB::connection('mysql_sb')->table('so_det')
                ->where('id_so', $so_id)
                ->where(function($query) {
                    $query->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
                })
                ->sum('qty');

            DB::connection('mysql_sb')->table('so')
                ->where('id', $so_id)
                ->update(['qty' => $totalQty]);

            DB::connection('mysql_sb')->commit();
            return response()->json(['status' => 200, 'message' => 'Semua Qty berhasil diperbarui!']);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            return response()->json(['status' => 500, 'message' => 'Gagal mengupdate Qty: ' . $e->getMessage()]);
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
            ->select('so.*', 'act.kpno', 'act.styleno', 'ms.Supplier as buyer', 'mp.product_group', 'mp.product_item')
            ->leftJoin('act_costing as act', 'so.id_cost', '=', 'act.id')
            ->leftJoin('mastersupplier as ms', 'act.id_buyer', '=', 'ms.Id_Supplier')
            ->leftJoin('masterproduct as mp', 'act.id_product', '=', 'mp.id')
            ->where('so.id', $id)
            ->first();

        if (!$header) {
            return abort(404, 'Data SO tidak ditemukan');
        }

        $detail_data = $mysql_sb->table('so_det')
            ->leftJoin('master_colors_gmt as c', 'so_det.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'so_det.id_size', '=', 's.id')
            ->where('so_det.id_so', $id)
            ->select('c.name as color', 's.size as size_name', 's.urutan', 'so_det.qty', 'so_det.deldate_det', 'so_det.id_color', 'so_det.id_size')
            ->orderBy('s.urutan', 'asc')
            ->get();

        $item_qty = [];
        $list_size = [];
        $header->ex_fty_date = $detail_data->first()->deldate_det ?? '-';

        foreach ($detail_data as $row) {
            $color = $row->color ?? 'Warna Tidak Sesuai Master';
            $size = $row->size_name ?? 'Size Tidak Sesuai Master';

            if (!isset($item_qty[$color][$size])) {
                $item_qty[$color][$size] = 0;
            }
            $item_qty[$color][$size] += $row->qty;

            if (!in_array($size, $list_size)) {
                $list_size[] = $size;
            }
        }

        $master_groups = $mysql_sb->table('mastergroup')
            ->whereNotNull('root_group')
            ->orderBy('root_group', 'asc')
            ->pluck('nama_group')
            ->toArray();

        $group_names = array_map(function($name) {
            return strtoupper(trim($name));
        }, $master_groups);

        $bom_materials = $mysql_sb->table('bom_marketing_detail as bmd')
            ->select(
                'mg.nama_group',
                'c.name as color_gmt',
                'bmd.id_color',
                'bmd.id_size',
                'bmd.shell',
                'bmd.notes as description',
                's.size as size_gmt',
                'i.color as color_item',
                'i.size as size_item',
                'bmd.qty as cons',
                'bmd.unit as unit',
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
            ->where('bmd.id_bom_marketing', $header->id_bom)
            ->where('bmd.category', 'Material')
            ->whereIn('mg.nama_group', $master_groups)
            ->get();

        $total_so_qty = $detail_data->sum('qty');
        $materials_by_group = [];
        $detail_collection = collect($detail_data);

        foreach ($bom_materials as $mat) {
            $has_color = !empty($mat->id_color);
            $has_size = !empty($mat->id_size);

            if (!$has_color && !$has_size) {
                $mat_qty = $total_so_qty;
            } elseif ($has_color && !$has_size) {
                $mat_qty = $detail_collection->where('id_color', $mat->id_color)->sum('qty');
            } elseif (!$has_color && $has_size) {
                $mat_qty = $detail_collection->where('id_size', $mat->id_size)->sum('qty');
            } else {
                $mat_qty = $detail_collection->where('id_color', $mat->id_color)->where('id_size', $mat->id_size)->sum('qty');
            }

            $mat->qty = $mat_qty;
            $mat->cons = (float) ($mat->cons ?? 0);

            $g_name = strtoupper(trim($mat->nama_group));
            $c_name = strtoupper(trim($mat->color_gmt ?? '-'));

            $materials_by_group[$g_name][$c_name][] = $mat;
        }

        foreach ($materials_by_group as $g_name => $color_groups) {
            ksort($color_groups);
            $materials_by_group[$g_name] = $color_groups;
        }

        // manufacturing

        $bom_materials_manufacturing = $mysql_sb->table('bom_marketing_detail as bmd')
            ->select(
                'c.name as color_gmt',
                'bmd.id_color',
                'bmd.id_size',
                'bmd.shell',
                'bmd.notes as description',
                's.size as size_gmt',
                'i.color as color_item',
                'i.size as size_item',
                'bmd.qty as cons',
                'bmd.unit as unit',
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
            ->where('bmd.id_bom_marketing', $header->id_bom)
            ->where('bmd.category', 'Manufacturing')
            ->get();


        $total_so_qty_manufacturing = $detail_data->sum('qty');
        $materials_manufacturing = [];

        foreach ($bom_materials_manufacturing as $mat) {
            $has_color = !empty($mat->id_color);
            $has_size = !empty($mat->id_size);

            if (!$has_color && !$has_size) {
                $mat_qty = $total_so_qty_manufacturing;
            } elseif ($has_color && !$has_size) {
                $mat_qty = $detail_collection->where('id_color', $mat->id_color)->sum('qty');
            } elseif (!$has_color && $has_size) {
                $mat_qty = $detail_collection->where('id_size', $mat->id_size)->sum('qty');
            } else {
                $mat_qty = $detail_collection->where('id_color', $mat->id_color)->where('id_size', $mat->id_size)->sum('qty');
            }

            $mat->qty = $mat_qty;
            $mat->cons = (float) ($mat->cons ?? 0);

            $c_name = strtoupper(trim($mat->color_gmt ?? '-'));

            $materials_manufacturing[$c_name][] = $mat;
        }

        ksort($materials_manufacturing);


        $view_data = [
            'header'       => $header,
            'details'      => $item_qty,
            'sizes'        => $list_size,
            'materials'    => $materials_by_group,
            'materials_manufacturing'    => $materials_manufacturing,
            'group_names'  => $group_names
        ];

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'courier']);
        $pdf = PDF::loadView('marketing.so.pdf_ws', $view_data)->setPaper('a4', 'landscape');

        $fileName = 'Worksheet-SO-' . ($header->so_no ?? $id) . '.pdf';
        return $pdf->stream(str_replace("/", "_", $fileName));
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
}
