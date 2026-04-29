<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
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

            $data = $db->table('act_costing_new as a')
                ->leftJoin('mastersupplier as b', 'a.buyer', '=', 'b.Id_Supplier')
                ->select(
                    'a.id',
                    'a.no_costing',
                    'a.created_at',
                    'b.Supplier as nama_buyer',
                    'a.brand',
                    'a.style',
                    'a.marketing_order',
                    'a.product_group',
                    'a.product_item',
                    'a.main_dest',
                    'a.market'
                )->orderBy('a.id', 'desc');

            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $data->whereDate('a.created_at', '>=', $tgl_awal)
                     ->whereDate('a.created_at', '<=', $tgl_akhir);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tgl_costing', function ($row) {
                    return $row->created_at ? date('d-m-Y', strtotime($row->created_at)) : '-';
                })
                ->make(true);
        }


        return view('marketing.costing.index', [
            'containerFluid'       => true,
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing",
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
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing",
            'containerFluid'   => true
        ]);
    }

    public function store(Request $request)
    {
        $ym = date('ym');

        $db = DB::connection('mysql_sb');
        $now = now();

        $bln_thn = $now->format('my');

        $last_costing = $db->table('act_costing_new')
            ->where('no_costing', 'like', "CST/$bln_thn/%")
            ->orderBy('id', 'desc')
            ->first();

        if ($last_costing) {
            $parts = explode('/', $last_costing->no_costing);
            $last_number = (int) end($parts);
            $next_costing = $last_number + 1;
        } else {
            $next_costing = 1;
        }

        $cost_no = "CST/$bln_thn/" . str_pad($next_costing, 5, '0', STR_PAD_LEFT);

        $filename = null;

        if ($request->hasFile('upload_foto')) {
            $file = $request->file('upload_foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/costing'), $filename);
        }

        $product_set_val = null;
        if ($request->has('product_set') && is_array($request->product_set)) {
            $product_set_val = implode(',', $request->product_set);
        }

        $insertId = $db->table('act_costing_new')->insertGetId([
            'no_costing'      => $cost_no,
            'buyer'           => $request->buyer,
            'brand'           => Str::upper($request->brand),
            'product_group'   => $request->product_group,
            'product_item'    => $request->product_item,
            'style'           => Str::upper($request->style),
            'ship_mode'       => $request->ship_mode,
            'curr'            => $request->curr,
            'type'            => $request->type,
            'product_set'      => $product_set_val,
            'marketing_order' => $request->marketing_order,
            'shipment_type'   => $request->shipment_type,
            'notes'           => Str::upper($request->notes),
            'qty'             => str_replace(',', '', $request->qty),
            'smv'             => str_replace(',', '', $request->smv),
            'vat'             => str_replace(',', '', $request->vat),
            'rate_to_idr'     => str_replace(',', '', $request->rate_to_idr),
            'rate_from_idr'   => str_replace(',', '', $request->rate_from_idr),
            'foto'            => $filename,
            'created_at'      => now(),
            'updated_at'      => now(),
            'created_by'      => Auth::user()->name,
            'confirm_price'   => str_replace(',', '', $request->confirm_price),
        ]);

        $mandatory_others = $db->table('masterothers')->where('costing_header', 'Y')->get();

        $detail_inserts = [];
        foreach ($mandatory_others as $other) {
            $nama_item = strtoupper(($other->otherscode ?? '') . ' ' . ($other->othersdesc ?? ''));
            $overhead = str_contains($nama_item, 'OVERHEAD');

            $detail_inserts[] = [
                'id_costing'   => $insertId,
                'type'         => 'Other Cost',
                'item_id'      => $other->id,
                'allowance'    => $overhead ? 6 : 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (count($detail_inserts) > 0) {
            $db->table('act_costing_detail_new')->insert($detail_inserts);
        }

        return redirect()->route('edit-costing', $insertId)->with('success', 'Costing berhasil dibuat');
    }

    public function edit($id)
    {

        $db = DB::connection('mysql_sb');
        $costing = $db->table('act_costing_new')->where('id', $id)->first();
        if (!$costing) {
            return redirect()->route('master-costing')->with('error', 'Data Costing tidak ditemukan!');
        }


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

        $details = $db->table('act_costing_detail_new as det')
            ->select(
                'det.*',
                's.Supplier as nama_supplier',
                'set.nama as nama_set',
                $db->raw("
                    CASE
                        WHEN det.type IN ('Fabric', 'Accessories Sewing', 'Accessories Packing')
                            THEN CONCAT(mat_e.id, ' ', mat_a.nama_group, ' ', mat_s.nama_sub_group, ' ', mat_d.nama_type, ' ', mat_e.nama_contents)

                        WHEN det.type = 'Other Cost'
                            THEN CONCAT(o.otherscode, ' ', o.othersdesc)

                        WHEN det.type = 'Manufacturing'
                            THEN CONCAT(m.cfcode, ' ', m.cfdesc)

                        ELSE det.item_id
                    END as nama_item
                ")
            )
            ->leftJoin('mastersupplier as s', 'det.supplier_id', '=', 's.Id_Supplier')
            ->leftJoin('mastercontents as mat_e', function($join) {
                $join->on('det.item_id', '=', 'mat_e.id')
                     ->whereIn('det.type', ['Fabric', 'Accessories Sewing', 'Accessories Packing']);
            })
            ->leftJoin('mastertype2 as mat_d', 'mat_e.id_type', '=', 'mat_d.id')
            ->leftJoin('mastersubgroup as mat_s', 'mat_d.id_sub_group', '=', 'mat_s.id')
            ->leftJoin('mastergroup as mat_a', 'mat_s.id_group', '=', 'mat_a.id')
            ->leftJoin('masterothers as o', function($join) {
                $join->on('det.item_id', '=', 'o.id')
                     ->where('det.type', '=', 'Other Cost');
            })
            ->leftJoin('mastercf as m', function($join) {
                $join->on('det.item_id', '=', 'm.id')
                     ->where('det.type', '=', 'Manufacturing');
            })
            ->leftJoin('master_set as set', 'det.set', '=', 'set.id')
            ->where('det.id_costing', $id)
            ->get()
            ->groupBy('type');

        $suppliers = $db->table('mastersupplier')->orderBy('supplier', 'asc')->get();

        $destinations = $db->table('master_destination')->orderBy('country_name', 'asc')->get();

        $master_set = $db->table('master_set')->orderBy('urutan', 'asc')->get();

        return view('marketing.costing.edit', [
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
            'master_set'        => $master_set,
            'set'        => $set,
            'destinations'        => $destinations,
            'page' => 'dashboard-marketing',
            "subPageGroup" => "marketing-master",
            "subPage" => "marketing-master-costing",
            'containerFluid' => true
        ]);
    }

    public function getItemContents(Request $request)
    {
        $kategori = $request->kategori;
        $html = "<option value=''>Pilih Item...</option>";

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

        $db = DB::connection('mysql_sb');
        try {
            $detailId = $db->table('act_costing_detail_new')->insertGetId([
                'id_costing'   => $request->id_costing,
                'type'         => $request->category,
                'item_id'      => $request->item,
                'item_desc'    => $request->desc,
                'supplier_id'  => $request->supplier,
                'curr'         => $request->curr,
                'price'        => $request->price,
                'cons'         => $request->cons,
                'unit'         => $request->unit,
                'price_px_idr' => $request->price_px_idr,
                'price_px_usd' => $request->price_px_usd,
                'allowance'    => $request->allowance,
                'set'          => $request->set,
                'value_idr'    => $request->value_idr,
                'value_usd'    => $request->value_usd,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

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
        $db = DB::connection('mysql_sb');

        try {
            $updateData = [
                'buyer'           => $request->buyer,
                'brand'           => Str::upper($request->brand),
                'product_group'   => $request->product_group,
                'product_item'    => $request->product_item,
                'style'           => Str::upper($request->style),
                'ship_mode'       => $request->ship_mode,
                'main_dest'       => is_array($request->main_dest) ? implode(', ', $request->main_dest) : $request->main_dest,
                'market'          => Str::upper($request->market),
                'curr'            => $request->curr,
                'marketing_order' => $request->marketing_order,
                'shipment_type'   => $request->shipment_type,
                'type'            => $request->type,
                'product_set'      => is_array($request->product_set) ? implode(',', $request->product_set) : null,
                'notes'           => Str::upper($request->notes),
                'qty'             => str_replace(',', '', $request->qty),
                'smv'             => str_replace(',', '', $request->smv),
                'vat'             => str_replace(',', '', $request->vat),
                'rate_to_idr'     => str_replace(',', '', $request->rate_to_idr),
                'rate_from_idr'   => str_replace(',', '', $request->rate_from_idr),
                'confirm_price'   => str_replace(',', '', $request->confirm_price),
                'updated_at'      => now(),
            ];

            if ($request->hasFile('upload_foto')) {
                $file = $request->file('upload_foto');

                $filename = time() . '_' . $file->getClientOriginalName();

                $file->move(public_path('uploads/costing'), $filename);

                $updateData['foto'] = $filename;
            }


            $db->table('act_costing_new')->where('id', $id)->update($updateData);

            return redirect()->back()->with('success', 'Data berhasil di update');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }
    public function destroyDetail($id)
    {

        $db = DB::connection('mysql_sb');

        try {
            $db->table('act_costing_detail_new')->where('id', $id)->delete();

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
        $db = DB::connection('mysql_sb');

        $costing = $db->table('act_costing_new as a')
            ->leftJoin('mastersupplier as b', 'a.buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.product_item', '=', 'p.id')
            ->leftJoin('mastershipmode as sm', 'a.ship_mode', '=', 'sm.id')
            ->select(
                'a.*',
                'b.Supplier as nama_buyer',
                'p.product_item as nama_product_item',
                'sm.shipmode as nama_ship_mode'
            )
            ->where('a.id', $id)
            ->first();

        if (!$costing) {
            return redirect()->back()->with('error', 'Data Costing tidak ditemukan.');
        }

        $nama_destinasi = '-';
        if (!empty($costing->main_dest)) {
            $arr_id_dest = array_map('trim', explode(',', $costing->main_dest));

            $destinations = $db->table('master_destination')
                ->whereIn('id', $arr_id_dest)
                ->pluck('country_name')
                ->toArray();

            if (count($destinations) > 0) {
                $nama_destinasi = implode(', ', $destinations);
            }
        }
        $costing->nama_dest = $nama_destinasi;
        $details_raw = $db->table('act_costing_detail_new as ac')
            ->leftJoin('mastersupplier as s', 'ac.supplier_id', '=', 's.Id_Supplier')
            ->leftJoin('mastercf as cf', 'ac.item_id', '=', 'cf.id')
            ->leftJoin('masterothers as ot', 'ac.item_id', '=', 'ot.id')
            ->leftJoin('mastercontents as mc', 'ac.item_id', '=', 'mc.id')
            ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
            ->leftJoin('mastersubgroup as ms', 'mt.id_sub_group', '=', 'ms.id')
            ->leftJoin('mastergroup as mg', 'ms.id_group', '=', 'mg.id')
            ->leftJoin('master_set as set', 'ac.set', '=', 'set.id')
            ->select(
                'ac.*',
                's.Supplier as nama_supplier',
                'set.nama as nama_set',
                $db->raw("
                    CASE
                        WHEN ac.type = 'Manufacturing' THEN CONCAT(cf.cfcode, ' ', cf.cfdesc)
                        WHEN ac.type = 'Other Cost' THEN CONCAT(ot.otherscode, ' ', ot.othersdesc)
                        ELSE CONCAT(mg.nama_group, ' ', ms.nama_sub_group, ' ', mt.nama_type, ' ', mc.nama_contents)
                    END as nama_item
                "),
                $db->raw("
                    CASE
                        WHEN ac.type = 'Manufacturing' THEN cf.cfdesc
                        WHEN ac.type = 'Other Cost' THEN ot.othersdesc
                        ELSE mc.nama_contents
                    END as item_desc
                ")
            )
            ->where('ac.id_costing', $id)
            ->orderBy('ac.id', 'asc')
            ->get();

        $details = [
            'Fabric' => [],
            'Accessories Sewing' => [],
            'Accessories Packing' => [],
            'Manufacturing' => [],
            'Other Cost' => []
        ];

        foreach ($details_raw as $det) {
            $cat = $det->type;
            if (array_key_exists($cat, $details)) {
                $details[$cat][] = $det;
            }
        }

         $master_set = $db->table('master_set')
           ->get();


        $pdf = Pdf::loadView('marketing.costing.pdf', compact('costing', 'details', 'master_set'))
                ->setPaper('a4', 'landscape');

        return $pdf->stream('Costing_'.$costing->no_costing.'.pdf');
    }

    public function getDetailRow($id)
    {
        $db = DB::connection('mysql_sb');
        $data = $db->table('act_costing_detail_new')->where('id', $id)->first();
        if ($data) {
            return response()->json(['status' => 200, 'data' => $data]);
        }
        return response()->json(['status' => 404, 'message' => 'Data tidak ditemukan']);
    }

    public function updateDetail(Request $request)
    {
        $db = DB::connection('mysql_sb');
        $db->table('act_costing_detail_new')->where('id', $request->id_detail)->update([
            'type' => $request->category,
            'item_id' => $request->item,
            'item_desc' => $request->desc,
            'supplier_id' => $request->supplier,
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

        $costing = $db->table('act_costing_new as a')
            ->leftJoin('mastersupplier as b', 'a.buyer', '=', 'b.Id_Supplier')
            ->leftJoin('masterproduct as p', 'a.product_item', '=', 'p.id')
            ->leftJoin('mastershipmode as sm', 'a.ship_mode', '=', 'sm.id')
            ->select('a.*', 'b.Supplier as nama_buyer', 'p.product_item as nama_product_item', 'sm.shipmode as nama_ship_mode')
            ->where('a.id', $id)->first();

        if (!$costing) return redirect()->back()->with('error', 'Data Costing tidak ditemukan.');

        $nama_destinasi = '-';
        if (!empty($costing->main_dest)) {
            $arr_id_dest = array_map('trim', explode(',', $costing->main_dest));
            $destinations = $db->table('master_destination')->whereIn('id', $arr_id_dest)->pluck('country_name')->toArray();
            if (count($destinations) > 0) $nama_destinasi = implode(', ', $destinations);
        }
        $costing->nama_dest = $nama_destinasi;

        $details_raw = $db->table('act_costing_detail_new as ac')
            ->leftJoin('mastersupplier as s', 'ac.supplier_id', '=', 's.Id_Supplier')
            ->leftJoin('mastercf as cf', 'ac.item_id', '=', 'cf.id')
            ->leftJoin('masterothers as ot', 'ac.item_id', '=', 'ot.id')
            ->leftJoin('mastercontents as mc', 'ac.item_id', '=', 'mc.id')
            ->leftJoin('mastertype2 as mt', 'mc.id_type', '=', 'mt.id')
            ->leftJoin('mastersubgroup as ms', 'mt.id_sub_group', '=', 'ms.id')
            ->leftJoin('mastergroup as mg', 'ms.id_group', '=', 'mg.id')
            ->leftJoin('master_set as set', 'ac.set', '=', 'set.id')
            ->select('ac.*', 's.Supplier as nama_supplier', 'set.nama as nama_set',
                $db->raw("CASE WHEN ac.type = 'Manufacturing' THEN CONCAT(cf.cfcode, ' ', cf.cfdesc) WHEN ac.type = 'Other Cost' THEN CONCAT(ot.otherscode, ' ', ot.othersdesc) ELSE CONCAT(mg.nama_group, ' ', ms.nama_sub_group, ' ', mt.nama_type, ' ', mc.nama_contents) END as nama_item"),
                $db->raw("CASE WHEN ac.type = 'Manufacturing' THEN cf.cfdesc WHEN ac.type = 'Other Cost' THEN ot.othersdesc ELSE mc.nama_contents END as item_desc")
            )->where('ac.id_costing', $id)->orderBy('ac.id', 'asc')->get();

        $details = ['Fabric' => [], 'Accessories Sewing' => [], 'Accessories Packing' => [], 'Manufacturing' => [], 'Other Cost' => []];
        foreach ($details_raw as $det) { if (array_key_exists($det->type, $details)) $details[$det->type][] = $det; }

        $sum_fab_idr = 0; $sum_sew_idr = 0; $sum_pack_idr = 0; $sum_mfg_idr = 0; $sum_oth_norm_idr = 0;
        $sum_fab_usd = 0; $sum_sew_usd = 0; $sum_pack_usd = 0; $sum_mfg_usd = 0; $sum_oth_norm_usd = 0;
        $overhead_row = null;

        $rate_from_idr = $costing->rate_from_idr > 0 ? $costing->rate_from_idr : 15000;

        // AMBIL SET DINAMIS DARI HEADER (Seperti Logika di Blade)
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

        // Inisialisasi angka 0 agar Set-nya tetap ke-print di Excel walau isinya kosong
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
                    $det->value_usd = $det->value_idr / $rate_from_idr;
                    $sum_oth_norm_idr += $det->value_idr;
                    $sum_oth_norm_usd += $det->value_usd;
                }
            }
        }

        $base_overhead_idr = $base_material_idr + $sum_oth_norm_idr;
        $base_overhead_usd = $base_material_usd + $sum_oth_norm_usd;

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

        $ga_idr = $base_ga_idr * 0.03;
        $ga_usd = $base_ga_usd * 0.03;

        $grand_idr = $base_ga_idr + $ga_idr;
        $grand_usd = $grand_idr / $rate_from_idr;

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

        $sheet->setCellValue('A7', 'Product Group')->setCellValue('B7', ': ' . $costing->product_group);
        $sheet->setCellValue('D7', 'Type')->setCellValue('E7', ': ' . strtoupper($costing->type));
        $sheet->setCellValue('G7', 'Rate from IDR')->setCellValue('H7', ': ' . $costing->rate_from_idr);

        $sheet->setCellValue('A8', 'Product Item')->setCellValue('B8', ': ' . ($costing->nama_product_item ?? $costing->product_item));
        $sheet->setCellValue('D8', 'Set')->setCellValue('E8', ': ' . $set_string);
        $sheet->setCellValue('G8', 'VAT')->setCellValue('H8', ': ' . $costing->vat . ' %');

        if (!empty($costing->foto) && file_exists(public_path('uploads/costing/' . $costing->foto))) {
            $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing2->setName('Costing Photo');
            $drawing2->setPath(public_path('uploads/costing/' . $costing->foto));
            $drawing2->setHeight(100);
            $drawing2->setCoordinates('K3');
            $drawing2->setWorksheet($sheet);
        }

        $row = 10;

        $categories_list = [
            'Fabric' => 'FABRIC', 'Accessories Sewing' => 'ACCESSORIES SEWING',
            'Accessories Packing' => 'ACCESSORIES PACKING', 'Manufacturing' => 'MANUFACTURING', 'Other Cost' => 'OTHER COST'
        ];

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
                        $sub_idr += $det->value_idr; $sub_usd += $det->value_usd;
                        $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) : 0;
                        $allow = $det->allowance > 0 ? $det->allowance : 0;
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
                        // Font Hitam Normal
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
                        $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                        $row++;
                    }
                }
                $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) : 0;
                $sheet->mergeCells("A$row:C$row"); $sheet->setCellValue("A$row", "TOTAL OTHER COST :")->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue("D$row", $sub_idr)->setCellValue("E$row", $sub_usd)->setCellValue("F$row", $sub_persen);
                $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
                $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $row++;

                // === TAMBAHAN G&A PINDAH KESINI ===
                $ga_pct = $pembagi_persen > 0 ? ($ga_idr / $pembagi_persen) : 0;
                $sheet->mergeCells("A$row:C$row"); $sheet->setCellValue("A$row", "G&A (3%)")->getStyle("A$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue("D$row", $ga_idr)->setCellValue("E$row", $ga_usd)->setCellValue("F$row", $ga_pct);
                $sheet->getStyle("A$row:F$row")->applyFromArray($styleHead);
                $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $row += 2;
            }
        }

        // CETAK GRAND TOTAL BOM & VALUE TABEL (Dinamis Sesuai Set)
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

        $vat_idr = $grand_idr * $vat_multiplier;
        $vat_usd = $grand_usd * $vat_multiplier;
        $profit_idr = $vat_idr * 1.06;
        $profit_usd = $vat_usd * 1.06;
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
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode('#,##0.000000');
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "VAT ($actual_vat%)")->getStyle("I$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("K$row", $vat_idr)->setCellValue("L$row", $vat_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder);
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode('#,##0.000000');
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode('#,##0.00');
        $row++;

        $sheet->mergeCells("I$row:J$row"); $sheet->setCellValue("I$row", "PROFIT (6%)")->getStyle("I$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->setCellValue("K$row", $profit_idr)->setCellValue("L$row", $profit_usd)->getStyle("K$row:L$row")->applyFromArray($styleBorder);
        $sheet->getStyle("K$row")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("L$row")->getNumberFormat()->setFormatCode('#,##0.00');

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

            $data = $db->table('act_costing_new as a')
                ->leftJoin('mastersupplier as b', 'a.buyer', '=', 'b.Id_Supplier')
                ->select(
                    'a.id',
                    'a.no_costing',
                    'a.created_at',
                    'b.Supplier as nama_buyer',
                    'a.brand',
                    'a.style',
                    'a.marketing_order',
                    'a.product_group',
                    'a.product_item',
                    'a.main_dest',
                    'a.market'
                )
                ->where(function($query) {
                    $query->whereNull('a.approval')
                          ->orWhere('a.approval', '!=', 'Y');
                })
                ->orderBy('a.id', 'desc');

            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $data->whereDate('a.created_at', '>=', $tgl_awal)
                     ->whereDate('a.created_at', '<=', $tgl_akhir);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tgl_costing', function ($row) {
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
            DB::connection('mysql_sb')
                ->table('act_costing_new')
                ->where('id', $id)
                ->update([
                    'approval' => 'Y',
                    'approved_at' => now(),
                    'approved_by' => Auth::user()->name
                ]);

            return response()->json(['status' => 200, 'message' => 'Costing berhasil di Approve.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
