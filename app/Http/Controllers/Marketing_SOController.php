<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
                    return '<button type="button" class="btn btn-sm btn-info" onclick="showDetail('.$row->id.')">
                                <i class="fas fa-eye"></i>
                            </button>';
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
            ->select('id', 'no_katalog_bom')
            ->whereNotNull('no_katalog_bom')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('marketing.so.create', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-so',
            'buyers'         => $buyers,
            'currency'       => $currency,
            'product_groups' => $product_groups,
            'bom_catalog'    => $bom_catalog,
            'containerFluid' => true
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

    public function uploadExcelSO(Request $request)
    {
        $request->validate(['file_so' => 'required|mimes:xls,xlsx']);
        $user_id = auth()->id();
        $file = $request->file('file_so');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $data = $spreadsheet->getActiveSheet()->toArray();
        $headers = $data[0];

        $temp_data = [];
        $errors_color = [];
        $errors_size = [];
        $list_po = [];

        $mysql_sb = DB::connection('mysql_sb');
        $master_colors = $mysql_sb->table('master_colors_gmt')->pluck('id', 'name')->toArray();
        $master_sizes = $mysql_sb->table('master_size_new')->pluck('id', 'size')->toArray();

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            if (empty($row[0])) continue;

            $style = trim($row[0]);
            $po    = trim($row[2]);
            $color_name = trim($row[5]);
            $list_po[] = $po;

            if (!isset($master_colors[$color_name])) {
                $errors_color[] = "PO: <b>$po</b> - Warna: <b>$color_name</b> tidak ditemukan di Master Color.";
                continue;
            }

            $id_color = $master_colors[$color_name];

            for ($col_index = 6; $col_index < count($row); $col_index++) {
                $size_name = trim($headers[$col_index]);
                if (empty($size_name)) continue;

                if (!isset($master_sizes[$size_name])) {
                    $errors_size[] = "PO: <b>$po</b> - Size: <b>$size_name</b> tidak ditemukan di Master Size.";
                    continue;
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
                        'id_color'   => $id_color,
                        'size'       => $master_sizes[$size_name],
                        'qty'        => $qty,
                        'created_at' => now()
                    ];
                }
            }
        }

        if (count($errors_color) > 0) return response()->json(['status' => 422, 'message' => 'Kesalahan warna', 'errors' => $errors_color], 422);
        if (count($errors_size) > 0) return response()->json(['status' => 422, 'message' => 'Kesalahan size', 'errors' => $errors_size], 422);

        $mysql_sb->table('temp_so_detail')->where('user_id', $user_id)->delete();

        if (count($temp_data) > 0) {
            foreach (array_chunk($temp_data, 500) as $chunk) {
                $mysql_sb->table('temp_so_detail')->insert($chunk);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Excel berhasil diproses.'
        ]);
    }

    public function getTempData(Request $request)
    {
        if ($request->ajax()) {
            $user_id = auth()->id();

            $temp_data = DB::connection('mysql_sb')->table('temp_so_detail as t')
                ->join('master_colors_gmt as c', 't.id_color', '=', 'c.id')
                ->join('master_size_new as s', 't.size', '=', 's.id')
                ->select('t.*', 'c.name as color_name', 's.size as size_name', 's.urutan')
                ->where('t.user_id', $user_id)
                ->get();

            $data = $temp_data->groupBy(function($item) {
                return $item->style . '|' . $item->po . '|' . $item->color_name . '|' . $item->market . '|' . $item->ex_fty;
            })->map(function($group) {

                $first = $group->first();
                $data = [
                    'style'  => $first->style,
                    'desc'   => $first->desc,
                    'po'     => $first->po,
                    'market' => $first->market,
                    'ex_fty' => $first->ex_fty,
                    'color'  => $first->color_name,
                ];

                foreach ($group as $item) {
                    $data[$item->size_name] = ($data[$item->size_name] ?? 0) + $item->qty;
                }

                return $data;

            })->values();

            $all_sizes = $temp_data->sortBy('urutan')
                ->pluck('size_name')
                ->unique()
                ->values()
                ->toArray();

            return response()->json([
                'data' => $data,
                'available_sizes' => $all_sizes,
                'total_qty' => $temp_data->sum('qty'),
                'jumlah_po' => $temp_data->unique('po')->count()
            ]);
        }
    }

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
                    'dateinput'   => now(),
                    'aktif'       => 'Y',
                    'attach_file' => $file_name,
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
                    'id_bom'      => $request->id_bom,
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
}
