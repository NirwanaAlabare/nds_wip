<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Marketing_BomController extends Controller
{
    public function index()
    {
        $data = DB::table('bom_marketing as h')
            ->leftJoin('signalbit_erp.mastersupplier as b', 'h.buyer', '=', 'b.Id_Supplier')
            ->select('h.*', 'b.Supplier as nama_buyer')
            ->orderBy('h.created_at', 'desc')
            ->get();

        return view('marketing.bom.index', [
            'data'         => $data,
            'page'         => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage'      => 'marketing-master-bom'
        ]);
    }

    public function create()
    {
        $mysql_sb = DB::connection('mysql_sb');

        $buyers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'C')
            ->select('Id_Supplier', 'Supplier')->orderBy('Supplier', 'ASC')->get();

        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')
            ->select('Id_Supplier', 'Supplier')->orderBy('Supplier', 'ASC')->get();

        $itemContents = $mysql_sb->table('mastergroup as a')
            ->join('mastersubgroup as s', 'a.id', '=', 's.id_group')
            ->join('mastertype2 as d', 's.id', '=', 'd.id_sub_group')
            ->join('mastercontents as e', 'd.id', '=', 'e.id_type')
            ->select('e.id as isi', DB::raw("CONCAT(e.id,' ',nama_group,' ',nama_sub_group,' ',nama_type,' ',nama_contents) as tampil"))
            ->orderBy('a.id', 'asc')->get();

        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();

        $master_colors = DB::table('master_colors')->orderBy('name', 'ASC')->get();
        $master_sizes = DB::table('master_size_new')->orderBy('urutan', 'ASC')->get();

        return view('marketing.bom.create', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-bom',
            'buyers'         => $buyers,
            'suppliers'      => $suppliers,
            'master_colors'  => $master_colors,
            'master_sizes'   => $master_sizes,
            'itemContents'   => $itemContents,
            'masterUnits'    => $masterUnits,
            'containerFluid' => true
        ]);
    }


    public function storeColor(Request $request)
    {
        DB::table('master_colors')->updateOrInsert(
            ['name' => strtoupper($request->color_name)],
            ['created_at' => now()]
        );
        return response()->json(['status' => 200, 'message' => 'Warna Berhasil Disimpan']);
    }

    public function storeSize(Request $request)
    {
        try {
            $lastUrutan = DB::table('master_size_new')->max('urutan') ?? 0;
            DB::table('master_size_new')->updateOrInsert(
                ['size' => strtoupper($request->size_name)],
                ['urutan' => $lastUrutan + 1]
            );
            return response()->json(['status' => 200, 'message' => 'Size Berhasil Disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
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

    public function getListData(Request $request)
    {
        $masterItems = DB::connection('mysql_sb')->table('masteritem as a')
            ->join('masterdesc as b', 'a.id_gen', '=', 'b.id')
            ->join('mastercolor as c', 'b.id_color', '=', 'c.id')
            ->join('masterweight as d', 'c.id_weight', '=', 'd.id')
            ->join('masterlength as e', 'd.id_length', '=', 'e.id')
            ->join('masterwidth as f', 'e.id_width', '=', 'f.id')
            ->join('mastercontents as g', 'f.id_contents', '=', 'g.id')
            ->where('g.id', $request->id_contents)
            ->select('a.id_item as isi', DB::raw("CONCAT(a.id_item, ' - ', a.itemdesc) as tampil"))
            ->groupBy('a.id_gen')->get();

        return response()->json(['items' => $masterItems]);
    }


    public function storeHeader(Request $request)
    {
        try {
            $id = DB::table('bom_marketing')->insertGetId([
                'buyer'      => $request->buyer,
                'style'      => $request->style,
                'market'     => $request->market,
                'colors'     => json_encode($request->colors),
                'sizes'      => json_encode($request->sizes),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 200, 'id' => $id, 'message' => 'Katalog berhasil dibuat']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function storeDetail(Request $request)
    {
        DB::beginTransaction();
        try {
            $bom_id  = $request->id_bom_marketing;
            $rule    = $request->rule_bom;
            $colors  = $request->colors ?? [null];
            $sizes   = $request->sizes ?? [null];
            $id_supplier    = $request->id_supplier;
            $details = [];

            foreach ($colors as $color_index => $data_color) {
                foreach ($sizes as $size_index => $data_size) {

                    if ($rule == "All Color All Size") {
                        $idx = 1;
                    } elseif ($rule == "All Color Range Size") {
                        $idx = $size_index + 1;
                    } elseif ($rule == "Per Color All Size") {
                        $idx = $color_index + 1;
                    } else {
                        $idx = ($color_index * count($sizes)) + $size_index + 1;
                    }

                    $id_item = $request->id_item[$idx] ?? null;
                    $qty     = $request->qty_input[$idx] ?? 0;

                    if ($id_item) {
                        $details[] = [
                            'id_bom_marketing' => $bom_id,
                            'id_contents'      => $request->item_contents,
                            'rule_bom'         => $rule,
                            'id_unit'          => $request->unit,
                            'notes'            => $request->notes,
                            'shell'            => $request->shell,
                            'id_color'         => $data_color,
                            'id_size'          => $data_size,
                            'id_item'          => $id_item,
                            'id_supplier'      => $id_supplier,
                            'qty'              => $qty,
                            'created_at'       => now(),
                        ];
                    }
                }
            }

            if (count($details) > 0) {
                DB::table('bom_marketing_detail')->insert($details);
            }

            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Item BOM Berhasil Ditambahkan!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    public function getItems(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('bom_marketing_detail as d')
                ->leftJoin('signalbit_erp.masteritem as i', 'd.id_item', '=', 'i.id_item')
                ->leftJoin('master_colors as c', 'd.id_color', '=', 'c.id')
                ->leftJoin('master_size_new as s', 'd.id_size', '=', 's.id')
                ->leftJoin('signalbit_erp.mastercontents as e', 'd.id_contents', '=', 'e.id')
                ->leftJoin('signalbit_erp.mastertype2 as d2', 'e.id_type', '=', 'd2.id')
                ->leftJoin('signalbit_erp.mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
                ->leftJoin('signalbit_erp.mastergroup as a', 's_grp.id_group', '=', 'a.id')
                ->leftJoin('signalbit_erp.masterpilihan as u', 'd.id_unit', '=', 'u.id')
                ->select(
                    'd.*',
                    'i.itemdesc as item_name',
                    DB::raw("CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents) as content_name"),
                    'c.name  as color_name',
                    's.size as size_name',
                    'u.nama_pilihan as unit_name'
                )
                ->where('d.id_bom_marketing', $id)
                ->orderBy('d.id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->filterColumn('content_name', function($query, $keyword) {
                    $sql = "CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents) like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('color_name', function($query, $keyword) {
                   $query->where('c.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('size_name', function($query, $keyword) {
                     $query->where('s.size', 'like', "%{$keyword}%");
                })
                ->filterColumn('unit_name', function($query, $keyword) {
                    $query->where('u.nama_pilihan', 'like', "%{$keyword}%");
                })
                ->filterColumn('i.itemdesc', function($query, $keyword) {
                    $query->where('i.itemdesc', 'like', "%{$keyword}%");
                })

                ->make(true);
        }
    }
    public function edit($id)
    {
        $bom = DB::table('bom_marketing')->where('id', $id)->first();

        if (!$bom) {
            return redirect()->route('master-bom')->with('error', 'Data tidak ditemukan!');
        }

        $mysql_sb = DB::connection('mysql_sb');

        $buyers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'C')->orderBy('Supplier', 'ASC')->get();
        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();

        $selectedColors = $bom->colors ? json_decode($bom->colors, true) : [];
        $selectedSizes  = $bom->sizes ? json_decode($bom->sizes, true) : [];

        $itemContents = $mysql_sb->table('mastergroup as a')
            ->join('mastersubgroup as s', 'a.id', '=', 's.id_group')
            ->join('mastertype2 as d', 's.id', '=', 'd.id_sub_group')
            ->join('mastercontents as e', 'd.id', '=', 'e.id_type')
            ->select('e.id as isi', DB::raw("CONCAT(e.id,' ',nama_group,' ',nama_sub_group,' ',nama_type,' ',nama_contents) as tampil"))
            ->orderBy('a.id', 'asc')->get();

        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $master_colors = DB::table('master_colors')->orderBy('name', 'ASC')->get();
        $master_sizes = DB::table('master_size_new')->orderBy('urutan', 'ASC')->get();

        return view('marketing.bom.edit', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-bom',
            'bom'            => $bom,
            'buyers'         => $buyers,
            'suppliers'      => $suppliers,
            'itemContents'   => $itemContents,
            'masterUnits'    => $masterUnits,
            'master_colors'  => $master_colors,
            'master_sizes'   => $master_sizes,
            'selectedColors' => $selectedColors,
            'selectedSizes'  => $selectedSizes,
            'containerFluid' => true
        ]);
    }

    public function showDetail(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = DB::table('bom_marketing_detail as d')
                ->leftJoin('signalbit_erp.masteritem as i', 'd.id_item', '=', 'i.id_item')
                ->leftJoin('master_colors as c', 'd.id_color', '=', 'c.id')
                ->leftJoin('master_size_new as s', 'd.id_size', '=', 's.id')
                ->leftJoin('signalbit_erp.mastercontents as e', 'd.id_contents', '=', 'e.id')
                ->leftJoin('signalbit_erp.mastertype2 as d2', 'e.id_type', '=', 'd2.id')
                ->leftJoin('signalbit_erp.mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
                ->leftJoin('signalbit_erp.mastergroup as a', 's_grp.id_group', '=', 'a.id')
                ->leftJoin('signalbit_erp.masterpilihan as u', 'd.id_unit', '=', 'u.id')
                ->select(
                    'd.*',
                    'i.itemdesc as item_name',
                    DB::raw("CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents) as content_name"),
                    'c.name  as color_name',
                    's.size as size_name',
                    'u.nama_pilihan as unit_name'
                )
                ->where('d.id_bom_marketing', $id)
                ->orderBy('d.id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->filterColumn('content_name', function($query, $keyword) {
                    $sql = "CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents) like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('color_name', function($query, $keyword) {
                   $query->where('c.name', 'like', "%{$keyword}%");
                })
                ->filterColumn('size_name', function($query, $keyword) {
                     $query->where('s.size', 'like', "%{$keyword}%");
                })
                ->filterColumn('unit_name', function($query, $keyword) {
                    $query->where('u.nama_pilihan', 'like', "%{$keyword}%");
                })
                ->filterColumn('i.itemdesc', function($query, $keyword) {
                    $query->where('i.itemdesc', 'like', "%{$keyword}%");
                })

                ->make(true);
        }
    }
}
