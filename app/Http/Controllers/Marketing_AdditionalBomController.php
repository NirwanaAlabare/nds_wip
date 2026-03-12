<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class Marketing_AdditionalBomController extends Controller
{

    public function index()
    {
        $mysql_sb = DB::connection('mysql_sb');

        $data = $mysql_sb->table('bom_marketing_additional as h')
            ->leftJoin('mastersupplier as b', 'h.id_buyer', '=', 'b.Id_Supplier')
            ->select('h.*', 'b.Supplier as nama_buyer')
            ->orderBy('h.created_at', 'desc')
            ->get();

        return view('marketing.bom_additional.index', [
            'data'           => $data,
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-bom-additional',
            'containerFluid' => true
        ]);
    }

    public function create()
    {
        $mysql_sb = DB::connection('mysql_sb');

        $list_so = $mysql_sb->table('so')
            ->select('id', 'so_no')
            ->whereNotNull('no_po')
            ->where('no_po', '!=', '')
            ->orderBy('id', 'DESC')
            ->get();

        $suppliers = $mysql_sb->table('mastersupplier')
            ->where('tipe_sup', 'S')
            ->select('Id_Supplier', 'Supplier')
            ->orderBy('Supplier', 'ASC')
            ->get();

        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();

        return view('marketing.bom_additional.create', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-bom-additional',
            'list_so'        => $list_so,
            'suppliers'      => $suppliers,
            'masterUnits'    => $masterUnits,
            'containerFluid' => true
        ]);
    }

    public function edit($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        // Ambil Data Header
        $bom = $mysql_sb->table('bom_marketing_additional')
            ->select('bom_marketing_additional.*', 'so.so_no')
            ->leftJoin('so', 'bom_marketing_additional.id_so', '=', 'so.id')
            ->where('bom_marketing_additional.id', $id)
            ->first();

        if (!$bom) {
            return redirect()->route('master-bom-additional')->with('error', 'Data BOM Additional tidak ditemukan!');
        }

        // PERBAIKAN: Ambil Daftar PO lalu gabungkan kembali menjadi format 'no_po_color_size'
        // agar terbaca oleh Javascript di halaman Edit
        $saved_pos = $mysql_sb->table('bom_marketing_additional_po')
            ->where('id_bom_additional', $id)
            ->select(DB::raw("CONCAT(no_po, '_', id_color, '_', id_size) as unique_key"))
            ->pluck('unique_key')
            ->toArray();

        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();
        $masterUnits = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->get();
        $master_colors = $mysql_sb->table('master_colors_gmt')->orderBy('name', 'ASC')->get();
        $master_sizes = $mysql_sb->table('master_size_new')->orderBy('urutan', 'ASC')->get();

        return view('marketing.bom_additional.edit', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-master',
            'subPage'        => 'marketing-bom-additional',
            'bom'            => $bom,
            'saved_pos'      => $saved_pos, // Format array-nya sekarang ["61341260_8_2", ...]
            'suppliers'      => $suppliers,
            'masterUnits'    => $masterUnits,
            'master_colors'  => $master_colors,
            'master_sizes'   => $master_sizes,
            'containerFluid' => true
        ]);
    }

    public function getPoBySo(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $so = $mysql_sb->table('so as s')
            ->join('bom_marketing as h', 's.id_bom', '=', 'h.id')
            ->leftJoin('mastersupplier as b', 'h.id_buyer', '=', 'b.Id_Supplier')
            ->where('s.id', $request->id_so)
            ->select('s.so_no', 's.no_po', 'h.style', 'h.market', 'b.Supplier as buyer')
            ->first();

        $po_list = $mysql_sb->table('so_det as sd')
            ->join('so as s', 'sd.id_so', '=', 's.id')
            ->leftJoin('master_colors_gmt as c', 'sd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as sz', 'sd.id_size', '=', 'sz.id')
            ->where('sd.id_so', $request->id_so)
            ->select(
                's.no_po',
                'sd.id_color',
                'c.name as color_name',
                'sd.id_size',
                'sz.size as size_name',
                $mysql_sb->raw('SUM(sd.qty) as qty')
            )
            ->groupBy('s.no_po', 'sd.id_color', 'c.name', 'sd.id_size', 'sz.size')
            ->get();

        return response()->json(['status' => 200, 'so_data' => $so, 'po_list' => $po_list]);
    }

    public function storeHeader(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            $monthYear = Carbon::now()->format('my');
            $prefix = 'BOM-A/' . $monthYear . '/';

            $lastBom = $mysql_sb->table('bom_marketing_additional')
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

            $so_data = $mysql_sb->table('so')
                ->join('bom_marketing', 'so.id_bom', '=', 'bom_marketing.id')
                ->where('so.id', $request->id_so)
                ->select('bom_marketing.id_buyer', 'bom_marketing.style', 'bom_marketing.market')
                ->first();

            $username = auth()->user()->username ?? 'admin';

            $id_bom_additional = $mysql_sb->table('bom_marketing_additional')->insertGetId([
                'id_so'          => $request->id_so,
                'no_katalog_bom' => $no_katalog_bom,
                'id_buyer'       => $so_data->id_buyer ?? null,
                'style'          => $so_data->style ?? null,
                'market'         => $so_data->market ?? null,
                'username'       => $username,
                'created_by'     => $username,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // PERBAIKAN: Pecah (Explode) string dari Javascript kembali ke bagian-bagiannya
            if ($request->has('po_list') && is_array($request->po_list)) {
                $po_inserts = [];
                foreach ($request->po_list as $po_value) {
                    $parts = explode('_', $po_value); // misal: 61341260_8_2 dipecah

                    $po_inserts[] = [
                        'id_bom_additional' => $id_bom_additional,
                        'no_po'             => $parts[0] ?? null,  // Masuk ke no_po dengan bersih
                        'id_color'          => $parts[1] ?? null,  // Masuk ke id_color
                        'id_size'           => $parts[2] ?? null,  // Masuk ke id_size
                        'created_at'        => now()
                    ];
                }

                if (!empty($po_inserts)) {
                    $mysql_sb->table('bom_marketing_additional_po')->insert($po_inserts);
                }
            }

            $mysql_sb->commit();
            return response()->json([
                'status'  => 200,
                'id'      => $id_bom_additional,
                'message' => 'BOM Additional berhasil dibuat: ' . $no_katalog_bom
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollback();
            return response()->json(['status' => 500, 'message' => 'Gagal simpan: ' . $e->getMessage()]);
        }
    }

    public function updatePo(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            $id_bom_additional = $request->id_bom_additional;
            $po_list = $request->po_list ?? [];

            // Hapus data lama
            $mysql_sb->table('bom_marketing_additional_po')
                ->where('id_bom_additional', $id_bom_additional)
                ->delete();

            // PERBAIKAN: Sama seperti create, pecah string sebelum save
            if (count($po_list) > 0) {
                $po_inserts = [];
                foreach ($po_list as $po_value) {
                    $parts = explode('_', $po_value);

                    $po_inserts[] = [
                        'id_bom_additional' => $id_bom_additional,
                        'no_po'             => $parts[0] ?? null,
                        'id_color'          => $parts[1] ?? null,
                        'id_size'           => $parts[2] ?? null,
                        'created_at'        => now()
                    ];
                }
                $mysql_sb->table('bom_marketing_additional_po')->insert($po_inserts);
            }

            $mysql_sb->commit();
            return response()->json(['status' => 200, 'message' => 'Daftar PO berhasil diperbarui']);
        } catch (\Exception $e) {
            $mysql_sb->rollback();
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function storeDetail(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $mysql_sb->beginTransaction();

        try {
            $bom_id      = $request->id_bom_additional;
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

                    if ($id_item && $qty > 0) {
                        $details[] = [
                            'id_bom_additional' => $bom_id,
                            'id_contents'       => $request->item_contents,
                            'rule_bom'          => $rule,
                            'id_unit'           => $request->unit,
                            'id_item'           => $id_item,
                            'id_supplier'       => $id_supplier,
                            'id_color'          => $data_color,
                            'id_size'           => $data_size,
                            'qty'               => $qty,
                            'price'             => $price,
                            'category'          => $category,
                            'notes'             => $request->notes,
                            'shell'             => $request->shell,
                            'created_at'        => now(),
                        ];
                    }
                }
            }

            if (count($details) > 0) {
                $mysql_sb->table('bom_marketing_additional_detail')->insert($details);
            }

            $mysql_sb->commit();
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Ditambahkan!']);
        } catch (\Exception $e) {
            $mysql_sb->rollback();
            return response()->json(['status' => 500, 'message' => 'Gagal simpan detail: ' . $e->getMessage()]);
        }
    }

    public function getItems(Request $request, $id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        if ($request->ajax()) {
            $data = $mysql_sb->table('bom_marketing_additional_detail as d')
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
                            WHEN d.category = 'Manufacturing' OR d.category = 'Manufacturing - Complexity'
                            THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                            ELSE i.itemdesc
                        END as item_name
                    "),
                    $mysql_sb->raw("
                        CASE
                            WHEN d.category = 'Manufacturing' OR d.category = 'Manufacturing - Complexity'
                            THEN CONCAT(mfg.cfcode, ' ', mfg.cfdesc)
                            ELSE CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents)
                        END as content_name
                    "),
                    'c.name as color_name',
                    's.size as size_name',
                    'u.nama_pilihan as unit_name'
                )
                ->where('d.id_bom_additional', $id)
                ->orderBy('d.id', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->filterColumn('item_name', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing' OR d.category = 'Manufacturing - Complexity'
                                THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                                ELSE i.itemdesc
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->filterColumn('content_name', function($query, $keyword) {
                    $sql = "CASE
                                WHEN d.category = 'Manufacturing' OR d.category = 'Manufacturing - Complexity'
                                THEN CONCAT(mfg.cfcode, ' ', mfg.cfdesc)
                                ELSE CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents)
                            END like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })
                ->make(true);
        }
    }

    public function getItemRow($id)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $data = $mysql_sb->table('bom_marketing_additional_detail as d')
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
            $mysql_sb->table('bom_marketing_additional_detail')->where('id', $id)->update([
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

    public function deleteBatch(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        try {
            $ids = $request->ids;
            $mysql_sb->table('bom_marketing_additional_detail')->whereIn('id', $ids)->delete();
            return response()->json(['status' => 200, 'message' => count($ids) . ' item berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
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
        $id_contents = $request->id_contents;
        $category = $request->category;
        $mysql_sb = DB::connection('mysql_sb');

        if ($category == 'Manufacturing' || $category == 'Manufacturing - Complexity') {
            $masterItems = $mysql_sb->table('masteritem as a')
                ->join('mastercf as s', 'a.matclass', '=', 's.cfdesc')
                ->where('a.mattype', 'C')
                ->where('s.id', $id_contents)
                ->select('a.id_item as isi', DB::raw("CONCAT(a.itemdesc, ' ', a.color, ' ', a.size, ' ', a.add_info) as tampil"))
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
                ->select('a.id_item as isi', DB::raw("CONCAT(a.id_item, ' - ', a.itemdesc) as tampil"))
                ->groupBy('a.id_gen')
                ->get();
        }

        return response()->json(['items' => $masterItems]);
    }
}
