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

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $mysql_sb = DB::connection('mysql_sb');

            $dateFrom = $request->get('date_from', date('Y-m-d'));
            $dateTo = $request->get('date_to', date('Y-m-d'));

            $data = $mysql_sb->table('bom_marketing as h')
                ->leftJoin('mastersupplier as b', 'h.id_buyer', '=', 'b.Id_Supplier')
                ->select('h.*', 'b.Supplier as nama_buyer', DB::raw('(SELECT SUM(qty) FROM bom_marketing_detail WHERE id_bom_marketing = h.id) as total_cons'))
                ->where('h.created_at', '>=', $dateFrom . ' 00:00:00')
                ->where('h.created_at', '<=', $dateTo . ' 23:59:59')
                ->orderBy('h.created_at', 'desc')
                ->get();

            return response()->json(['data' => $data]);
        }

        return view('marketing.bom.index', [
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

        $costings = $mysql_sb->table('act_costing_new')->select('id', 'no_costing', 'style', 'market', 'buyer')->where('approval', 'Y')->orderBy('id', 'desc')->get();

        return view('marketing.bom.create', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-bom',
            'buyers'         => $buyers,
            'suppliers'      => $suppliers,
            'master_colors'  => $master_colors,
            'master_sizes'   => $master_sizes,
            'masterUnits'    => $masterUnits,
            'costings' => $costings,
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

        $master_units = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();
        $master_currency = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'curr')->get();

        $costings = $mysql_sb->table('act_costing_new')->select('id', 'no_costing', 'style')->orderBy('id', 'desc')->get();

         $shell = $mysql_sb->table('masterpanel')
                ->where('nama_panel', 'LIKE', 'shell' . '%')
                ->orderBy('id', 'desc')
                ->get();

        return view('marketing.bom.edit', [
            'page'               => 'dashboard-marketing',
            'subPageGroup'       => 'marketing-master',
            'subPage'            => 'marketing-master-bom',
            'bom'                => $bom,
            'buyers'             => $buyers,
            'suppliers'          => $suppliers,
            'master_units'       => $master_units,
            'master_colors'      => $master_colors,
            'master_sizes'       => $master_sizes,
            'master_currency'    => $master_currency,
            'selectedColors'     => $selectedColors,
            'selectedSizes'      => $selectedSizes,
            'master_items_other' => $master_items_other,
            'costings'           => $costings,
            'shell'           => $shell,
            'containerFluid'     => true
        ]);
    }


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
                'id_buyer'       => $request->buyer,
                'style'          => $request->style,
                'market'         => $request->market,
                'id_costing'     => $request->id_costing,
                'colors'         => $colors_json,
                'sizes'          => $sizes_json,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $mysql_sb->commit();
            return response()->json([
                'status'  => 200,
                'id'      => $id_bom,
                'message' => 'BOM berhasil dibuat: ' . $no_katalog_bom
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollback();
            return response()->json(['status' => 500, 'message' => 'Gagal simpan BOM: ' . $e->getMessage()]);
        }
    }


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
            $currency    = $request->currency;
            $details     = [];

            $item_content = explode('_', $request->item_contents);
            $id_content = $item_content[0] ?? null;
            $id_set     = (!empty($item_content[1])) ? $item_content[1] : null;
            $item_desc  = (!empty($item_content[2])) ? $item_content[2] : null;

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
                            'id_contents'      => $id_content,
                            'id_set'           => $id_set,
                            'item_desc'        => $item_desc,
                            'rule_bom'         => $rule,
                            'unit'             => $request->unit,
                            'notes'            => $request->notes,
                            'shell'            => $request->shell,
                            'id_color'         => $data_color,
                            'id_size'          => $data_size,
                            'id_item'          => $id_item,
                            'id_supplier'      => $id_supplier,
                            'id_currency'      => $currency,
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

    public function updateBomHeader(Request $request)
    {
        $id_bom = $request->id_bom ?? $request->id_bom_marketing;
        $id_costing = $request->id_costing;


        if (!$id_bom) {
            return response()->json(['status' => 500, 'message' => 'Gagal: ID BOM tidak terdeteksi dari form!']);
        }

        try {
            $colors = $request->colors ? array_values(array_unique($request->colors)) : [];
            $sizes  = $request->sizes ? array_values(array_unique($request->sizes)) : [];

            DB::connection('mysql_sb')->table('bom_marketing')
                ->where('id', $id_bom)
                ->update([
                    'id_costing'     => $id_costing,
                    'colors'     => json_encode($colors),
                    'sizes'      => json_encode($sizes),
                    'updated_at' => now(),
                ]);

            return response()->json(['status' => 200, 'message' => 'Data Berhasil Diperbarui!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }


    public function storeDetailEdit(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            $bom_id        = $request->id_bom_marketing;
            $id_supplier   = $request->id_supplier;
            $category      = $request->category;
            $currency      = $request->currency;
            $rule_bom      = $request->rule_bom;

            $item_contents = $request->item_contents;
            $parts = explode('|', $item_contents);

            $id_costing_detail = $parts[0] ?? null;
            $id_content        = $parts[1] ?? null;
            $id_set            = (isset($parts[2]) && $parts[2] !== '') ? $parts[2] : null;
            $item_desc         = (isset($parts[3]) && $parts[3] !== '') ? $parts[3] : null;

            $bom_header = $mysql_sb->table('bom_marketing')->where('id', $bom_id)->first();
            $header_colors = $bom_header && $bom_header->colors ? json_decode($bom_header->colors, true) : [];
            $header_sizes  = $bom_header && $bom_header->sizes ? json_decode($bom_header->sizes, true) : [];

            $items  = $request->id_item ?? [];
            $colors = $request->id_color ?? [];
            $sizes  = $request->id_size ?? [];
            $qtys   = $request->qty_input ?? [];
            $prices = $request->price_input ?? [];

            $details_to_insert = [];

            foreach ($items as $idx => $id_item) {
                $qty   = $qtys[$idx] ?? 0;
                $price = $prices[$idx] ?? 0;

                if (!empty($id_item) && $qty > 0) {

                    $row_color = (!empty($colors[$idx]) && $colors[$idx] !== 'null') ? $colors[$idx] : null;
                    $row_size  = (!empty($sizes[$idx]) && $sizes[$idx] !== 'null') ? $sizes[$idx] : null;

                    $target_colors = $row_color ? [$row_color] : (count($header_colors) > 0 ? $header_colors : [null]);
                    $target_sizes  = $row_size  ? [$row_size]  : (count($header_sizes) > 0  ? $header_sizes  : [null]);

                    foreach ($target_colors as $cId) {
                        foreach ($target_sizes as $sId) {

                            $existingQuery = $mysql_sb->table('bom_marketing_detail')
                                ->where('id_bom_marketing', $bom_id)
                                ->where('id_contents', $id_content)
                                ->where('id_color', $cId)
                                ->where('id_size', $sId)
                                ->where('id_set', $id_set);

                            if ($item_desc) {
                                $existingQuery->where('item_desc', $item_desc);
                            } else {
                                $existingQuery->where(function($q) {
                                    $q->whereNull('item_desc')->orWhere('item_desc', '');
                                });
                            }

                            $existing = $existingQuery->first();

                            if ($existing) {
                                $mysql_sb->table('bom_marketing_detail')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'id_item'           => $id_item,
                                        'id_set'            => $id_set,
                                        'item_desc'         => $item_desc,
                                        'id_supplier'       => $id_supplier,
                                        'unit'              => $request->unit,
                                        'id_currency'       => $currency,
                                        'qty'               => $qty,
                                        // 'price'             => $price,
                                        'notes'             => $request->notes,
                                        'shell'             => $request->shell,
                                        'id_costing_detail' => $id_costing_detail,

                                    ]);
                            } else {
                                $details_to_insert[] = [
                                    'id_bom_marketing'  => $bom_id,
                                    'id_contents'       => $id_content,
                                    'id_set'            => $id_set,
                                    'item_desc'         => $item_desc,
                                    'rule_bom'          => $rule_bom,
                                    'unit'              => $request->unit,
                                    'notes'             => $request->notes,
                                    'shell'             => $request->shell,
                                    'id_color'          => $cId,
                                    'id_size'           => $sId,
                                    'id_item'           => $id_item,
                                    'id_supplier'       => $id_supplier,
                                    'id_currency'       => $currency,
                                    'qty'               => $qty,
                                    // 'price'             => $price,
                                    'category'          => $category,
                                    'created_at'        => now(),
                                    'id_costing_detail' => $id_costing_detail,

                                ];
                            }
                        }
                    }
                }
            }

            if (count($details_to_insert) > 0) {
                $mysql_sb->table('bom_marketing_detail')->insert($details_to_insert);
            }

            $mysql_sb->commit();
            return response()->json(['status' => 200, 'message' => 'Item Material Berhasil Disimpan!']);
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
                ->leftJoin('masterpilihan as u', 'd.unit', '=', 'u.id')
                ->leftJoin('masterpilihan as cur', 'd.id_currency', '=', 'cur.id')
                ->leftJoin('master_set as st', 'd.id_set', '=', 'st.id')
                ->leftJoin('masterpanel as mp', 'd.shell', '=', 'mp.id')
                ->select(
                    'd.*',
                    'mp.nama_panel',
                    $mysql_sb->raw("
                        CASE
                            WHEN d.category = 'Manufacturing'
                            THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                            ELSE CONCAT(i.id_item, ' ', i.itemdesc)
                        END as item_name
                    "),
                    $mysql_sb->raw("
                        CASE
                            WHEN d.category = 'Manufacturing'
                            THEN CONCAT(mfg.cfdesc, IF(d.item_desc IS NOT NULL AND d.item_desc != '', CONCAT(' [', d.item_desc, ']'), ''), IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), ''))
                            ELSE CONCAT(a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents, IF(d.item_desc IS NOT NULL AND d.item_desc != '', CONCAT(' [', d.item_desc, ']'), ''), IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), ''))
                        END as content_name
                    "),
                    $mysql_sb->raw("
                        CASE
                            WHEN d.category = 'Manufacturing'
                            THEN mfg.cfcode
                            ELSE e.id
                        END as id_content
                    "),
                    'c.name as color_name',
                    's.size as size_name',
                    'u.nama_pilihan as unit_name',
                    'cur.nama_pilihan as currency'
                )
                ->where('d.id_bom_marketing', $id)
                ->orderByRaw("
                    CASE
                        WHEN d.category = 'Manufacturing' THEN 999
                        WHEN a.root_group IS NULL THEN 998
                        ELSE a.root_group
                    END ASC
                ")
                ->orderBy('d.id', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('item_name', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing'
                                THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                                ELSE CONCAT(i.id_item, ' ', i.itemdesc)
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('content_name', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing'
                                THEN CONCAT(mfg.cfcode, ' ', mfg.cfdesc, IF(d.item_desc IS NOT NULL AND d.item_desc != '', CONCAT(' [', d.item_desc, ']'), ''), IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), ''))
                                ELSE CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents, IF(d.item_desc IS NOT NULL AND d.item_desc != '', CONCAT(' [', d.item_desc, ']'), ''), IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), ''))
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('id_content', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing' THEN mfg.cfcode
                                ELSE e.id
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('shell', function($query, $keyword) {
                    $query->whereRaw("mp.nama_panel like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('currency', function($query, $keyword) {
                    $query->whereRaw("cur.nama_pilihan like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('unit', function($query, $keyword) {
                    $query->whereRaw("u.nama_pilihan like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('color_name', function($query, $keyword) {
                    $query->whereRaw("c.name like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('size_name', function($query, $keyword) {
                    $query->whereRaw("s.size like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('category', function($query, $keyword) {
                    $query->whereRaw("d.category like ?", ["%{$keyword}%"]);
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
                'unit'  => $request->unit,
                'shell'    => $request->shell,
            ]);
            return response()->json(['status' => 200, 'message' => 'Item berhasil diupdate']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }


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

    // public function getItemContents(Request $request)
    // {
    //     $kategori = $request->kategori;
    //     $id_costing = $request->id_costing;

    //     $html = "<option value=''>Pilih Item...</option>";

    //     if (empty($kategori) || empty($id_costing)) {
    //         return response()->json($html);
    //     }

    //     $mysql_sb = DB::connection('mysql_sb');

    //     if ($kategori == 'Manufacturing') {

    //         $items = $mysql_sb->table('act_costing_detail_new as ac')
    //             ->join('mastercf as m', 'ac.item_id', '=', 'm.id')
    //             ->where('ac.id_costing', $id_costing)
    //             ->where('ac.type', 'Manufacturing')
    //             ->select(
    //                 'm.id as isi',
    //                 $mysql_sb->raw("CONCAT(m.cfcode, ' ', m.cfdesc) as tampil")
    //             )
    //             ->distinct()
    //             ->orderBy('m.id', 'DESC')
    //             ->get();

    //         foreach ($items as $item){
    //             $html .= "<option value='{$item->isi}'>{$item->tampil}</option>";
    //         }

    //     } else if ($kategori == 'Other Cost') {

    //         $others = $mysql_sb->table('act_costing_detail_new as ac')
    //             ->join('masterothers as m', 'ac.item_id', '=', 'm.id')
    //             ->where('ac.id_costing', $id_costing)
    //             ->where('ac.type', 'Other Cost')
    //             ->select(
    //                 'm.id as isi',
    //                 $mysql_sb->raw("CONCAT(m.otherscode, ' ', m.othersdesc) as tampil")
    //             )
    //             ->distinct()
    //             ->orderBy('m.id', 'desc')
    //             ->get();

    //         foreach ($others as $o) {
    //             $html .= '<option value="' . $o->isi . '">' . $o->tampil . '</option>';
    //         }

    //     } else {

    //         $types_in_material = ['Fabric', 'Accessories Sewing', 'Accessories Packing'];

    //         $items = $mysql_sb->table('act_costing_detail_new as ac')
    //             ->join('mastercontents as e', 'ac.item_id', '=', 'e.id')
    //             ->join('mastertype2 as d', 'e.id_type', '=', 'd.id')
    //             ->join('mastersubgroup as s', 'd.id_sub_group', '=', 's.id')
    //             ->join('mastergroup as a', 's.id_group', '=', 'a.id')
    //             ->where('ac.id_costing', $id_costing)
    //             ->whereIn('ac.type', $types_in_material)
    //             ->where('e.aktif', 'Y')
    //             ->select(
    //                 'e.id as isi',
    //                 $mysql_sb->raw("CONCAT(e.id, ' ', a.nama_group, ' ', s.nama_sub_group, ' ', d.nama_type, ' ', e.nama_contents) as tampil") // Teks panjang yang muncul di Dropdown
    //             )
    //             ->distinct()
    //             ->get();

    //         foreach ($items as $item) {
    //             $html .= "<option value='{$item->isi}'>{$item->tampil}</option>";
    //         }
    //     }

    //     return response()->json($html);
    // }

    // public function getItemContents(Request $request)
    // {
    //     $kategori = $request->kategori;
    //     $id_costing = $request->id_costing;


    //     $html = "<option value=''>Pilih Item...</option>";

    //     if (empty($kategori) || empty($id_costing)) {
    //         return response()->json($html);
    //     }

    //     $mysql_sb = DB::connection('mysql_sb');

    //     if ($kategori == 'Manufacturing') {

    //         $items = $mysql_sb->table('act_costing_detail_new as ac')
    //             ->join('mastercf as m', 'ac.item_id', '=', 'm.id')
    //             ->leftJoin('master_set as st', 'ac.set', '=', 'st.id')
    //             ->where('ac.id_costing', $id_costing)
    //             ->where('ac.type', 'Manufacturing')
    //             ->select(
    //                 $mysql_sb->raw("CONCAT(m.id, '_', IFNULL(ac.set, '')) as isi"),
    //                 $mysql_sb->raw("CONCAT(m.cfcode, ' ', m.cfdesc, IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), '')) as tampil"),
    //                 'ac.unit',
    //             )
    //             ->distinct()
    //             ->orderBy('m.id', 'DESC')
    //             ->get();

    //         foreach ($items as $item){
    //             $html .= "<option value='{$item->isi}' data-unit='{$item->unit}'>{$item->tampil}</option>";
    //         }

    //     } else if ($kategori == 'Other Cost') {

    //         $others = $mysql_sb->table('act_costing_detail_new as ac')
    //             ->join('masterothers as m', 'ac.item_id', '=', 'm.id')
    //             ->where('ac.id_costing', $id_costing)
    //             ->where('ac.type', 'Other Cost')
    //             ->select(
    //                 $mysql_sb->raw("CONCAT(m.id, '_', IFNULL(ac.set, '')) as isi"),
    //                 $mysql_sb->raw("CONCAT(m.otherscode, ' ', m.othersdesc) as tampil")
    //             )
    //             ->distinct()
    //             ->orderBy('m.id', 'desc')
    //             ->get();

    //         foreach ($others as $o) {
    //             $html .= '<option value="' . $o->isi . '" data-unit="">' . $o->tampil . '</option>';
    //         }

    //     } else {

    //         $types_in_material = ['Fabric', 'Accessories Sewing', 'Accessories Packing'];

    //         $items = $mysql_sb->table('act_costing_detail_new as ac')
    //             ->join('mastercontents as e', 'ac.item_id', '=', 'e.id')
    //             ->join('mastertype2 as d', 'e.id_type', '=', 'd.id')
    //             ->join('mastersubgroup as s', 'd.id_sub_group', '=', 's.id')
    //             ->join('mastergroup as a', 's.id_group', '=', 'a.id')
    //             ->leftJoin('master_set as st', 'ac.set', '=', 'st.id')
    //             ->where('ac.id_costing', $id_costing)
    //             ->whereIn('ac.type', $types_in_material)
    //             ->where('e.aktif', 'Y')
    //             ->select(
    //                 $mysql_sb->raw("CONCAT(e.id, '_', IFNULL(ac.set, '')) as isi"),
    //                 $mysql_sb->raw("CONCAT(e.id, ' ', a.nama_group, ' ', s.nama_sub_group, ' ', d.nama_type, ' ', e.nama_contents, IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), '')) as tampil"),
    //                 'ac.unit'
    //             )
    //             ->distinct()
    //             ->get();

    //         foreach ($items as $item) {
    //             $html .= "<option value='{$item->isi}' data-unit='{$item->unit}'>{$item->tampil}</option>";
    //         }
    //     }

    //     return response()->json($html);
    // }

    public function getItemContents(Request $request)
    {
        $kategori = $request->kategori;
        $id_costing = $request->id_costing;

        $html = "<option value=''>Pilih Item...</option>";

        if (empty($kategori) || empty($id_costing)) {
            return response()->json($html);
        }

        $mysql_sb = DB::connection('mysql_sb');

        if ($kategori == 'Manufacturing') {

            $items = $mysql_sb->table('act_costing_detail_new as ac')
                ->join('mastercf as m', 'ac.item_id', '=', 'm.id')
                ->leftJoin('master_set as st', 'ac.set', '=', 'st.id')
                ->where('ac.id_costing', $id_costing)
                ->where('ac.type', 'Manufacturing')
                ->select(
                    $mysql_sb->raw("CONCAT(ac.id, '|', m.id, '|', IFNULL(ac.set, ''), '|', IFNULL(ac.item_desc, '')) as isi"),
                    $mysql_sb->raw("CONCAT(m.cfcode, ' ', m.cfdesc, IF(ac.item_desc IS NOT NULL AND ac.item_desc != '', CONCAT(' [', ac.item_desc, ']'), ''), IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), '')) as tampil"),
                    'ac.unit'
                )
                ->distinct()
                ->orderBy('m.id', 'DESC')
                ->get();

            foreach ($items as $item){
                $html .= "<option value='{$item->isi}' data-unit='{$item->unit}'>{$item->tampil}</option>";
            }

        } else if ($kategori == 'Other Cost') {

            $others = $mysql_sb->table('act_costing_detail_new as ac')
                ->join('masterothers as m', 'ac.item_id', '=', 'm.id')
                ->where('ac.id_costing', $id_costing)
                ->where('ac.type', 'Other Cost')
                ->select(
                    $mysql_sb->raw("CONCAT(ac.id, '|', m.id, '|', IFNULL(ac.set, ''), '|', IFNULL(ac.item_desc, '')) as isi"),
                    $mysql_sb->raw("CONCAT(m.otherscode, ' ', m.othersdesc, IF(ac.item_desc IS NOT NULL AND ac.item_desc != '', CONCAT(' [', ac.item_desc, ']'), '')) as tampil")
                )
                ->distinct()
                ->orderBy('m.id', 'desc')
                ->get();

            foreach ($others as $o) {
                $html .= '<option value="' . $o->isi . '" data-unit="">' . $o->tampil . '</option>';
            }

        } else {

            $types_in_material = ['Fabric', 'Accessories Sewing', 'Accessories Packing'];

            $items = $mysql_sb->table('act_costing_detail_new as ac')
                ->join('mastercontents as e', 'ac.item_id', '=', 'e.id')
                ->join('mastertype2 as d', 'e.id_type', '=', 'd.id')
                ->join('mastersubgroup as s', 'd.id_sub_group', '=', 's.id')
                ->join('mastergroup as a', 's.id_group', '=', 'a.id')
                ->leftJoin('master_set as st', 'ac.set', '=', 'st.id')
                ->where('ac.id_costing', $id_costing)
                ->whereIn('ac.type', $types_in_material)
                ->where('e.aktif', 'Y')
                ->select(
                    $mysql_sb->raw("CONCAT(ac.id, '|', e.id, '|', IFNULL(ac.set, ''), '|', IFNULL(ac.item_desc, '')) as isi"),
                    $mysql_sb->raw("CONCAT(e.id, ' ', a.nama_group, ' ', s.nama_sub_group, ' ', d.nama_type, ' ', e.nama_contents, IF(ac.item_desc IS NOT NULL AND ac.item_desc != '', CONCAT(' [', ac.item_desc, ']'), ''), IF(st.nama IS NOT NULL, CONCAT(' [', st.nama, ']'), '')) as tampil"),
                    'ac.unit'
                )
                ->distinct()
                ->get();

            foreach ($items as $item) {
                $html .= "<option value='{$item->isi}' data-unit='{$item->unit}'>{$item->tampil}</option>";
            }
        }

        return response()->json($html);
    }

    // public function getListData(Request $request)
    // {
    //     $id_contents = $request->id_contents;
    //     $category = $request->category;

    //     $id_bom = $request->id_bom;

    //     $mysql_sb = DB::connection('mysql_sb');

    //     dd($id_contents, $category, $id_bom);

    //     if ($category == 'Manufacturing') {
    //         $masterItems = $mysql_sb->table('masteritem as a')
    //             ->join('mastercf as s', 'a.matclass', '=', 's.cfdesc')
    //             ->where('a.mattype', 'C')
    //             ->where('s.id', $id_contents)
    //             ->select(
    //                 'a.id_item as isi',
    //                 DB::raw("CONCAT(a.itemdesc, ' ', a.color, ' ', a.size, ' ', a.add_info) as tampil")
    //             )
    //             ->orderBy('a.id_item', 'DESC')
    //             ->get();
    //     } else {
    //         $masterItems = $mysql_sb->table('masteritem as a')
    //             ->join('masterdesc as b', 'a.id_gen', '=', 'b.id')
    //             ->join('mastercolor as c', 'b.id_color', '=', 'c.id')
    //             ->join('masterweight as d', 'c.id_weight', '=', 'd.id')
    //             ->join('masterlength as e', 'd.id_length', '=', 'e.id')
    //             ->join('masterwidth as f', 'e.id_width', '=', 'f.id')
    //             ->join('mastercontents as g', 'f.id_contents', '=', 'g.id')
    //             ->where('g.id', $id_contents)
    //             ->select(
    //                 'a.id_item as isi',
    //                 DB::raw("CONCAT(a.id_item, ' - ', a.itemdesc) as tampil")
    //             )
    //             ->groupBy('a.id_gen')
    //             ->get();
    //     }


    //     $existingDetails = [];
    //     if ($id_bom && $id_contents) {
    //         $savedItems = $mysql_sb->table('bom_marketing_detail')
    //             ->where('id_bom_marketing', $id_bom)
    //             ->where('id_contents', $id_contents)
    //             ->select('id_color', 'id_size', 'id_item', 'qty', 'price')
    //             ->get();

    //         foreach ($savedItems as $item) {
    //             $cId = $item->id_color ?? 'null';
    //             $sId = $item->id_size ?? 'null';

    //             $existingDetails["{$cId}_{$sId}"] = [
    //                 'id_item' => $item->id_item,
    //                 'qty'     => $item->qty,
    //                 'price'   => $item->price
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'items'    => $masterItems,
    //         'existing' => $existingDetails
    //     ]);
    // }

    public function getListData(Request $request)
    {
        $category = $request->category;
        $id_bom   = $request->id_bom;

        $data_contents = $request->id_contents;
        $parts        = explode('|', $data_contents);

        $id_contents       = $parts[1] ?? $data_contents;
        $id_set            = (isset($parts[2]) && $parts[2] !== '') ? $parts[2] : null;
        $item_desc         = (isset($parts[3]) && $parts[3] !== '') ? $parts[3] : null;

        $mysql_sb = DB::connection('mysql_sb');

        if ($category == 'Manufacturing') {
            $masterItems = $mysql_sb->table('masteritem as a')
                ->join('mastercf as s', 'a.matclass', '=', 's.cfdesc')
                ->where('a.mattype', 'C')
                ->where('s.id', $id_contents)
                ->select(
                    'a.id_item as isi',
                    DB::raw("CONCAT(a.itemdesc, ' ', IFNULL(a.color, ''), ' ', IFNULL(a.size, ''), ' ', IFNULL(a.add_info, '')) as tampil")
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

        $existingDetails = [];
        if ($id_bom && $id_contents) {

            $savedItemsQuery = $mysql_sb->table('bom_marketing_detail')
                ->where('id_bom_marketing', $id_bom)
                ->where('id_contents', $id_contents)
                ->where('id_set', $id_set);

            if ($item_desc) {
                $savedItemsQuery->where('item_desc', $item_desc);
            } else {
                $savedItemsQuery->where(function($q) {
                    $q->whereNull('item_desc')->orWhere('item_desc', '');
                });
            }

            $savedItems = $savedItemsQuery->select('id_color', 'id_size', 'id_item', 'qty', 'price')->get();

            foreach ($savedItems as $item) {
                $cId = $item->id_color ?? 'null';
                $sId = $item->id_size ?? 'null';

                $existingDetails["{$cId}_{$sId}"] = [
                    'id_item' => $item->id_item,
                    'qty'     => $item->qty,
                    'price'   => $item->price
                ];
            }
        }

        return response()->json([
            'items'    => $masterItems,
            'existing' => $existingDetails
        ]);
    }

    public function storeColor(Request $request)
    {
        $name = strtoupper($request->color_name);

        // Menggunakan updateOrCreate agar langsung mengembalikan objek model
        // Jika tidak pakai Model (pake DB::table), kita cari manual setelah insert
        $color = DB::connection('mysql_sb')->table('master_colors_gmt')->updateOrInsert(
            ['name' => $name],
            ['created_at' => now()]
        );

        $getData = DB::connection('mysql_sb')->table('master_colors_gmt')
                    ->where('name', $name)
                    ->first();

        return response()->json([
            'status' => 200,
            'data' => [
                'id'   => $getData->id,
                'name' => $getData->name
            ]
        ]);
    }

    public function storeSize(Request $request)
    {
        try {
            $sizeName = strtoupper($request->size_name);
            $mysql_sb = DB::connection('mysql_sb');

            $lastUrutan = $mysql_sb->table('master_size_new')->max('urutan') ?? 0;

            $mysql_sb->table('master_size_new')->updateOrInsert(
                ['size' => $sizeName],
                ['urutan' => $lastUrutan + 1]
            );

            $getData = $mysql_sb->table('master_size_new')->where('size', $sizeName)->first();

            return response()->json([
                'status' => 200,
                'data' => [
                    'id'   => $getData->id,
                    'name' => $getData->size
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }


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

    public function approval(Request $request)
    {
        if ($request->ajax()) {
            $mysql_sb = DB::connection('mysql_sb');

            $dateFrom = $request->get('date_from', date('Y-m-d'));
            $dateTo = $request->get('date_to', date('Y-m-d'));

            $data = $mysql_sb->table('bom_marketing as h')
                ->leftJoin('mastersupplier as b', 'h.id_buyer', '=', 'b.Id_Supplier')
                ->select('h.*', 'b.Supplier as nama_buyer', DB::raw('(SELECT SUM(qty) FROM bom_marketing_detail WHERE id_bom_marketing = h.id) as total_cons'))
                ->where(function($query) {
                    $query->whereNull('h.approval')
                          ->orWhere('h.approval', '!=', 'Y');
                })
                ->where('h.created_at', '>=', $dateFrom . ' 00:00:00')
                ->where('h.created_at', '<=', $dateTo . ' 23:59:59')
                ->orderBy('h.created_at', 'desc')
                ->get();

            return response()->json(['data' => $data]);
        }

        return view('marketing.bom.approval', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-master-bom-approval',
            'containerFluid' => true
        ]);
    }

    public function submitApproval(Request $request, $id)
    {
        try {
            DB::connection('mysql_sb')
                ->table('bom_marketing')
                ->where('id', $id)
                ->update([
                    'approval' => 'Y',
                    'approved_at' => now(),
                    'approved_by' => Auth::user()->name
                ]);

            return response()->json(['status' => 200, 'message' => 'BOM berhasil di Approve.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
