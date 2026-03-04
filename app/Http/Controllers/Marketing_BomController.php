<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Export_excel_bom_listing;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Marketing_BomController extends Controller
{
    // ==========================================
    // 1. VIEW PAGES (Index, Create, Edit)
    // ==========================================

    public function index()
    {
        $mysql_sb = DB::connection('mysql_sb');

        $data = $mysql_sb->table('bom_marketing as h')
            ->leftJoin('mastersupplier as b', 'h.id_buyer', '=', 'b.Id_Supplier')
            ->select('h.*', 'b.Supplier as nama_buyer')
            ->orderBy('h.created_at', 'desc')
            ->get();

        return view('marketing.bom.index', [
            'data'           => $data,
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-bom',
            'containerFluid' => true
        ]);
    }

    public function create()
    {
        $mysql_sb = DB::connection('mysql_sb');

        $buyers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'C')
            ->select('Id_Supplier', 'Supplier')->orderBy('Supplier', 'ASC')->get();

        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')
            ->select('Id_Supplier', 'Supplier')->orderBy('Supplier', 'ASC')->get();

        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();

        return view('marketing.bom.create', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-bom',
            'buyers'         => $buyers,
            'suppliers'      => $suppliers,
            'master_colors'  => $master_colors,
            'master_sizes'   => $master_sizes,
            'masterUnits'    => $masterUnits,
            'containerFluid' => true
        ]);
    }

    public function edit($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $bom = $mysql_sb->table('bom_marketing')->where('id', $id)->first();

        if (!$bom) {
            return redirect()->route('master-bom')->with('error', 'Data BOM Utama tidak ditemukan!');
        }

        $buyers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'C')->orderBy('Supplier', 'ASC')->get();
        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();

        $selectedColors = $bom->colors ? json_decode($bom->colors, true) : [];
        $selectedSizes  = $bom->sizes ? json_decode($bom->sizes, true) : [];

        $master_items_other = $mysql_sb->table('masterothers')
            ->select('id as isi', DB::raw("CONCAT(otherscode,' ',othersdesc) as tampil"))
            ->orderBy('id', 'DESC')
            ->get();

        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();

        return view('marketing.bom.edit', [
            'page'               => 'dashboard-marketing',
            'subPageGroup'       => 'marketing-master',
            'subPage'            => 'marketing-master-bom',
            'bom'                => $bom,
            'buyers'             => $buyers,
            'suppliers'          => $suppliers,
            'masterUnits'        => $masterUnits,
            'master_colors'      => $master_colors,
            'master_sizes'       => $master_sizes,
            'selectedColors'     => $selectedColors,
            'selectedSizes'      => $selectedSizes,
            'master_items_other' => $master_items_other,
            'containerFluid'     => true
        ]);
    }


    // ==========================================
    // 2. HEADER BOM
    // ==========================================

    public function storeHeader(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            $monthYear = Carbon::now()->format('my');
            $prefix = 'BOM/' . $monthYear . '/';

            $lastBom = $mysql_sb->table('bom_marketing')
                ->where('no_katalog_bom', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastBom && !empty($lastBom->no_katalog_bom)) {
                $full_no_katalog_bom = explode('/', $lastBom->no_katalog_bom);
                $last_no_urut = (int) end($full_no_katalog_bom);
                $nomor_urut = $last_no_urut + 1;
            } else {
                $nomor_urut = 1;
            }

            $no_katalog_bom = $prefix . str_pad($nomor_urut, 4, '0', STR_PAD_LEFT);

            $colors_json = $request->has('colors') ? json_encode($request->colors) : null;
            $sizes_json = $request->has('sizes') ? json_encode($request->sizes) : null;
            $username = auth()->user()->username ?? 'admin';

            $id_bom = $mysql_sb->table('bom_marketing')->insertGetId([
                'no_katalog_bom' => $no_katalog_bom,
                'id_buyer'       => $request->id_buyer,
                'style'          => $request->style,
                'market'         => $request->market,
                'colors'         => $colors_json,
                'sizes'          => $sizes_json,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $mysql_sb->commit();
            return response()->json([
                'status'  => 200,
                'id'      => $id_bom,
                'message' => 'Header BOM berhasil dibuat: ' . $no_katalog_bom
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollback();
            return response()->json(['status' => 500, 'message' => 'Gagal simpan header: ' . $e->getMessage()]);
        }
    }


    // ==========================================
    // 3. MATERIAL & MANUFACTURING DETAILS
    // ==========================================

    public function storeDetail(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            $bom_id      = $request->id_bom_marketing;
            $rule        = $request->rule_bom;
            $colors      = $request->colors ?? [null];
            $sizes       = $request->sizes ?? [null];
            $id_supplier = $request->id_supplier;
            $category    = $request->category;
            $details     = [];

            foreach ($colors as $color_index => $data_color) {
                foreach ($sizes as $size_index => $data_size) {

                    if ($rule == "All Color All Size") { $idx = 1; }
                    elseif ($rule == "All Color Range Size") { $idx = $size_index + 1; }
                    elseif ($rule == "Per Color All Size") { $idx = $color_index + 1; }
                    else { $idx = ($color_index * count($sizes)) + $size_index + 1; }

                    $id_item = $request->id_item[$idx] ?? null;
                    $qty     = $request->qty_input[$idx] ?? 0;
                    $price   = $request->price_input[$idx] ?? 0;

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
                            'price'            => $price,
                            'category'         => $category,
                            'created_at'       => now(),
                        ];
                    }
                }
            }

            if (count($details) > 0) {
                $mysql_sb->table('bom_marketing_detail')->insert($details);
            }

            $mysql_sb->commit();
            return response()->json(['status' => 200, 'message' => 'Item BOM Berhasil Ditambahkan!']);
        } catch (\Exception $e) {
            $mysql_sb->rollback();
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
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
                ->leftJoin('masterpilihan as u', 'd.id_unit', '=', 'u.id')
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
                    'u.nama_pilihan as unit_name'
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
                ->filterColumn('unit_name', function($query, $keyword) {
                    $query->where('u.nama_pilihan', 'like', "%{$keyword}%");
                })
                ->make(true);
        }
    }

    public function getItemRow($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $data = $mysql_sb->table('bom_marketing_detail as d')
            ->leftJoin('masteritem as i', 'd.id_item', '=', 'i.id_item')
            ->select('d.*', 'i.itemdesc as item_name')
            ->where('d.id', $id)
            ->first();

        return response()->json($data);
    }

    public function updateItemRow(Request $request, $id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        try {
            $mysql_sb->table('bom_marketing_detail')->where('id', $id)->update([
                'id_color' => $request->id_color,
                'id_size'  => $request->id_size,
                'qty'      => $request->qty,
                'price'    => $request->price,
                'id_unit'  => $request->id_unit,
                'shell'    => $request->shell,
            ]);
            return response()->json(['status' => 200, 'message' => 'Item berhasil diupdate']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }


    // ==========================================
    // 4. OTHER COST (BOM OTHER)
    // ==========================================

    public function storeOther(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $request->validate([
            'id_bom_marketing' => 'required',
            'id_item_others'   => 'required',
        ]);

        try {
            $mysql_sb->table('bom_marketing_others')->insert([
                'id_bom_marketing' => $request->id_bom_marketing,
                'item_id'          => $request->id_item_others,
                'price_usd'        => $request->price_usd ?? 0,
                'price_idr'        => $request->price_idr ?? 0,
                'created_at'       => now(),
                'created_by'       => Auth::user()->name ?? 'admin',
            ]);

            return response()->json(['status'  => 200, 'message' => 'Data berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOther($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $data = $mysql_sb->table('bom_marketing_others as a')
            ->join('masterothers as b', 'a.item_id', '=', 'b.id')
            ->select('a.*', DB::raw("CONCAT(b.otherscode, ' ', b.othersdesc) as tampil"))
            ->where('a.id_bom_marketing', $id)
            ->get();

        return response()->json($data);
    }

    public function destroyOther($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->table('bom_marketing_others')->where('id', $id)->delete();
        return response()->json(['message' => 'Item berhasil dihapus']);
    }


    // ==========================================
    // 5. FETCH & MASTER DATA HELPER
    // ==========================================

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

        if ($kategori == 'Material' || $kategori == 'Costing Detail') {
            $items = $mysql_sb->table('mastergroup as a')
                ->join('mastersubgroup as s', 'a.id', '=', 's.id_group')
                ->join('mastertype2 as d', 's.id', '=', 'd.id_sub_group')
                ->join('mastercontents as e', 'd.id', '=', 'e.id_type')
                ->select('e.id as isi', $mysql_sb->raw("CONCAT(e.id,' ',a.nama_group,' ',s.nama_sub_group,' ',d.nama_type,' ',e.nama_contents) as tampil"))
                ->get();

            foreach ($items as $item) { $html .= "<option value='{$item->isi}'>{$item->tampil}</option>"; }

        } elseif ($kategori == 'Manufacturing' || $kategori == 'Manufacturing - Complexity') {
            $items = $mysql_sb->table('mastercf')
                ->select('id as isi', $mysql_sb->raw("CONCAT(cfcode,' ',cfdesc) as tampil"))
                ->orderBy('id', 'DESC')
                ->get();

            foreach ($items as $item) { $html .= "<option value='{$item->isi}'>{$item->tampil}</option>"; }
        }

        return response()->json($html);
    }

    public function getListData(Request $request)
    {
        $id_contents = $request->id_contents;
        $category = $request->category;
        $mysql_sb = DB::connection('mysql_sb');

        if ($category == 'Manufacturing') {
            $masterItems = $mysql_sb->table('masteritem as a')
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
            $masterItems = $mysql_sb->table('masteritem as a')
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

        return response()->json(['items' => $masterItems]);
    }

    public function storeColor(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->table('master_colors_gmt')->updateOrInsert(
            ['name' => strtoupper($request->color_name)],
            ['created_at' => now()]
        );
        return response()->json(['status' => 200, 'message' => 'Warna Berhasil Disimpan']);
    }

    public function storeSize(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        try {
            $lastUrutan = $mysql_sb->table('master_size_new')->max('urutan') ?? 0;
            $mysql_sb->table('master_size_new')->updateOrInsert(
                ['size' => strtoupper($request->size_name)],
                ['urutan' => $lastUrutan + 1]
            );
            return response()->json(['status' => 200, 'message' => 'Size Berhasil Disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }


    // ==========================================
    // 6. DELETE BATCH & EXPORT
    // ==========================================

    public function deleteBatch(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        try {
            $ids = $request->ids;
            $mysql_sb->table('bom_marketing_detail')->whereIn('id', $ids)->delete();
            return response()->json(['status' => 200, 'message' => count($ids) . ' item berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function exportExcel(Request $request)
    {
        $id = $request->id;
        return Excel::download(
            new Export_excel_bom_listing($id),
            'Laporan List Item BOM.xlsx'
        );
    }

    public function showDetail(Request $request, $id)
    {
        return $this->getItems($request, $id);
    }
}
