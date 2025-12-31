<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Exports\PLPackingOutExport;
use App\Models\PackingOutDetTemp;
use App\Models\PackingOutH;
use App\Models\BppbSB;
use App\Models\Tempbpb;
use DB;
use PDF;

class PackingSubcontController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $keywordQuery = "";

            // if ($request->supplier != 'ALL') {
            //     $where = " and a.supplier = '" . $request->supplier . "' ";
            // }else{
            //     $where = "";
            // }


            $data_inmaterial = DB::connection('mysql_sb')->select("select a.id, a.no_bppb, a.tgl_bppb, a.no_po, supplier, buyer, jenis_pengeluaran, jenis_dok, CONCAT(a.created_by,' (',a.created_at,')') created_by, a.status from packing_out_h a INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier left join (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer group by id_jo) d on d.id_jo=b.id_jo where a.tgl_bppb BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' GROUP BY a.no_bppb");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("packing-subcont.packing-out", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,'page' => 'dashboard-packing', "subPageGroup" => "packing-packing-out", "subPage" => "packing-out-subcont"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'Status KB Out')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT('SPCK-OUT-', DATE_FORMAT(CURRENT_DATE(), '%Y')) Mattype,IF(MAX(no_bppb) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0)) nomor,CONCAT('SPCK/OUT/',DATE_FORMAT(CURRENT_DATE(), '%m'),DATE_FORMAT(CURRENT_DATE(), '%y'),'/',IF(MAX(RIGHT(no_bppb,5)) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0))) no_bppb FROM packing_out_h WHERE MONTH(tgl_bppb) = MONTH(CURRENT_DATE()) AND YEAR(tgl_bppb) = YEAR(CURRENT_DATE()) AND LEFT(no_bppb,4) = 'SPCK'");

        $jns_klr = DB::connection('mysql_sb')->select("
            select nama_trans isi,nama_trans tampil from mastertransaksi where jenis_trans='OUT' and jns_gudang = 'FACC' order by id");

        $no_req = DB::connection('mysql_sb')->select("
            select a.bppbno isi,concat(a.bppbno,'|',ac.kpno,'|',ac.styleno,'|',mb.supplier) tampil from bppb_req a inner join jo_det s on a.id_jo=s.id_jo inner join so on s.id_so=so.id inner join act_costing ac on so.id_cost=ac.id inner join mastersupplier mb on ac.id_buyer=mb.id_supplier and a.cancel='N' and bppbdate >= '2023-01-01' where bppbno like 'RQ-F%' and qty_out < 1 group by bppbno order by bppbdate desc");

        $no_po = DB::connection('mysql_sb')->select("select pono from po_header where podate >= '2025-01-01' and jenis = 'P' and app = 'A'");
        DB::connection('mysql_sb')->delete("DELETE FROM packing_out_det_temp WHERE created_by = ? ", [Auth::user()->name]);


        return view('packing-subcont.create-packing-out', ['no_req' => $no_req,'kode_gr' => $kode_gr,'jns_klr' => $jns_klr,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit ,'no_po' => $no_po, 'page' => 'dashboard-packing', "subPageGroup" => "packing-packing-out", "subPage" => "packing-out-subcont"]);
    }


    public function getDetailList(Request $request)
    {
        $user = Auth::user()->name;
        // $data_detail = DB::connection('mysql_sb')->select("select styleno, a.id_item, a.id_jo, itemdesc, qtyitem_sisa, qtyreq, qty_sdh_out, (qtyreq - qty_sdh_out) qty_sisa_out, Coalesce(qty_input,0) qty_input, unit from (select a.bppbno, ac.styleno, a.id_item, mi.itemdesc, a.qty qtyreq, COALESCE(a.qty_out,0) qty_sdh_out, a.id_jo,a.unit  from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier inner join jo on a.id_jo=jo.id left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id inner join mastersupplier b on ac.id_buyer=b.id_supplier inner join masteritem mi on a.id_item=mi.id_item where bppbno='".$request->no_req."' GROUP BY a.id) a LEFT JOIN
        //     (select id_jo, id_item, sum(sal_akhir) qtyitem_sisa from data_stock_fabric GROUP BY id_jo, id_item) b on a.id_item = b.id_item and a.id_jo = b.id_jo LEFT JOIN 
        //     (select id_item iditem,sum(qty_out) qty_input from whs_bppb_det_temp where created_by = '".$user."' GROUP BY id_item) c on c.iditem = a.id_item");

         $data_detail = DB::connection('mysql_sb')->select(" WITH detail_po as (select pono, kpno, styleno, jo.jo_no, c.id_jo, e.id_item, e.itemdesc, b.unit, b.qty, b.id_po, g.id_buyer, h.supplier buyer from po_header a 
                INNER JOIN po_item b on b.id_po = a.id 
                INNER JOIN bom_jo_item c on c.id_jo = b.id_jo and c.id_item = b.id_gen
                left join jo on jo.id = c.id_jo
                left join jo_det d on d.id_jo = c.id_jo
                left join so on so.id = d.id_so
                left join masteritem e on c.id_item = e.id_item 
                left join so_det f on f.id_so = so.id
                left join act_costing g on g.id = so.id_cost
                left join mastersupplier h on h.id_supplier = g.id_buyer
                where pono = '".$request->pono."' and a.app = 'A' GROUP BY b.id_gen, b.id_jo),
                                
                detail_input as (select id_po, id_jo, id_item, sum(qty) qty_input from packing_out_det_temp where created_by = '".$user."' GROUP BY id_po, id_jo, id_item),
                                
                detail_out as (select id_po, id_jo, id_item, sum(qty) qty_out from packing_out_det where status = 'Y' GROUP BY id_po, id_jo, id_item)
                                
                select a.*, COALESCE(qty_out,0) qty_out, COALESCE(qty_input,0) qty_input, (a.qty - COALESCE(qty_input,0) - COALESCE(qty_out,0)) qty_balance from detail_po a LEFT JOIN detail_input b on b.id_po = a.id_po and b.id_jo = a.id_jo and b.id_item = a.id_item LEFT JOIN detail_out c on c.id_po = a.id_po and c.id_jo = a.id_jo and c.id_item = a.id_item order by a.kpno asc");

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }

    public function showdetailitem(Request $request)
    {

        $det_item = DB::connection('mysql_sb')->select("select pono, kpno, styleno, jo.jo_no, c.id_jo, e.id_item, e.itemdesc, b.id_po, g.id_buyer, h.supplier buyer, f.color, f.size, f.unit from po_header a 
                INNER JOIN po_item b on b.id_po = a.id 
                INNER JOIN bom_jo_item c on c.id_jo = b.id_jo and c.id_item = b.id_gen
                left join jo on jo.id = c.id_jo
                left join jo_det d on d.id_jo = c.id_jo
                left join so on so.id = d.id_so
                left join masteritem e on c.id_item = e.id_item 
                left join so_det f on f.id_so = so.id
                left join act_costing g on g.id = so.id_cost
                                left join mastersupplier h on h.id_supplier = g.id_buyer
                where b.id_po = '" . $request->id_po . "' and c.id_jo = '" . $request->id_jo . "' and a.app = 'A' and f.cancel = 'N' GROUP BY c.id_jo, f.color, f.size");

        $html = '<div class="table-responsive">
        <table id="tableshow" class="table table-head-fixed table-bordered table-striped w-100 text-nowrap">
        <thead>
        <tr>
        <th class="text-center" style="font-size: 0.6rem;width: 20%;">Style</th>
        <th class="text-center" style="font-size: 0.6rem;width: 20%;">Color</th>
        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Size</th>
        <th class="text-center" style="font-size: 0.6rem;width: 13%;">Qty</th>
        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Unit</th>
        <th hidden></th>
        <th hidden></th>
        <th hidden></th>
        </tr>
        </thead>
        <tbody>';
        $jml_qty_sj = 0;
        $jml_qty_ak = 0;
        $x = 1;
        foreach ($det_item as $detitem) {
            $html .= ' <tr>
            <td >'.$detitem->styleno.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="det_style'.$x.'" name="det_style['.$x.']" value="'.$detitem->styleno.'" / readonly></td>
            <td >'.$detitem->color.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="det_color'.$x.'" name="det_color['.$x.']" value="'.$detitem->color.'" / readonly></td>
            <td >'.$detitem->size.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="det_size'.$x.'" name="det_size['.$x.']" value="'.$detitem->size.'" / readonly></td>
            <td><input style="width:90px;text-align:right;" class="form-control" type="text" id="det_qty'.$x.'" name="det_qty['.$x.']" value="" onkeyup="sum_qty_item(this.value)"></td>
            <td >'.$detitem->unit.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="det_unit'.$x.'" name="det_unit['.$x.']" value="'.$detitem->unit.'" / readonly></td>
            <td hidden> <input type="hidden" id="det_id_po'.$x.'" name="det_id_po['.$x.']" value="'.$detitem->id_po.'" / readonly></td>
            <td hidden> <input type="hidden" id="det_id_jo'.$x.'" name="det_id_jo['.$x.']" value="'.$detitem->id_jo.'" / readonly></td>
            <td hidden> <input type="hidden" id="det_id_item'.$x.'" name="det_id_item['.$x.']" value="'.$detitem->id_item.'" / readonly></td>
            </tr>';
            $x++;
        }

        $html .= '</tbody>
        </table>
        </div>';

        return $html;
    }


    public function SaveOutDetailTemp(Request $request)
{
    $qtyDet = $request->input('det_qty', []);   // <-- pastikan selalu array

    $totalQty = floatval($request->mdl_qty_h);

    if ($totalQty <= 0) {
        return [
            "status" => 400,
            "message" => "Please input data",
        ];
    }

    $timestamp = now();
    $rows = [];

    foreach ($qtyDet as $key => $qty) {

        $qty = floatval($qty ?? 0);

        if ($qty <= 0) {
            continue;
        }

        $rows[] = [
            "id_po"      => $request->det_id_po[$key]   ?? null,
            "id_jo"      => $request->det_id_jo[$key]   ?? null,
            "id_item"    => $request->det_id_item[$key] ?? null,
            "color"      => $request->det_color[$key]   ?? null,
            "size"       => $request->det_size[$key]    ?? null,
            "unit"       => $request->det_unit[$key]    ?? null,
            "qty"        => $qty,
            "status"     => 'Y',
            "created_by" => Auth::user()->name,
            "created_at" => $timestamp,
            "updated_at" => $timestamp,
        ];
    }

    if (empty($rows)) {
        return [
            "status" => 400,
            "message" => "Tidak ada qty yang diisi",
        ];
    }

    DB::transaction(function () use ($rows) {
        PackingOutDetTemp::insert($rows);
    });

    return [
        "status" => 200,
        "message" => "Add data successfully",
        "redirect" => ''
    ];
}

public function DeleteOutDetailTemp(Request $request)
    {

        $deletescan = PackingOutDetTemp::where('id_po',$request['id_po'])->where('id_jo',$request['id_jo'])->where('id_item',$request['id_item'])->where('created_by',Auth::user()->name)->delete();

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedRequest = $request->validate([
            "txt_no_po" => "required",
            "txt_supp" => "required",
            "txt_jns_klr" => "required",
            "txt_dok_bc" => "required",
            "txt_qty_garment" => "required",
            "txt_qty_karton" => "required",
        ]);

        $tglbppb = $request['txt_tgl_bppb'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('SPCK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(no_bppb) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0)) nomor,CONCAT('SPCK/OUT/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(no_bppb,5)) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0))) no_bppb FROM packing_out_h WHERE MONTH(tgl_bppb) = MONTH('" . $tglbppb . "') AND YEAR(tgl_bppb) = YEAR('" . $tglbppb . "') AND LEFT(no_bppb,4) = 'SPCK'");

        $m_type = $Mattype1[0]->Mattype;
        $no_type = $Mattype1[0]->nomor;
        $bppbno_int = $Mattype1[0]->no_bppb;

        $cek_mattype = DB::connection('mysql_sb')->select("select * from tempbpb where Mattype = '" . $m_type . "'");
        $hasilcek = $cek_mattype ? $cek_mattype[0]->Mattype : 0;

        $Mattype2 = DB::connection('mysql_sb')->select("select 'O.SPCK' Mattype, IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0)) nomor, CONCAT('SJ-SPCK', IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0))) bpbno FROM bppb WHERE LEFT(bppbno_int,8) = 'SPCK/OUT'");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
        $m_type2 = $Mattype2[0]->Mattype;
        $no_type2 = $Mattype2[0]->nomor;
        $bpbno = $Mattype2[0]->bpbno;

        $cek_mattype2 = DB::connection('mysql_sb')->select("select * from tempbpb where Mattype = '" . $m_type2 . "'");
        $hasilcek2 = $cek_mattype2 ? $cek_mattype2[0]->Mattype : 0;

        if ($hasilcek != '0') {
            $update_tempbpb = Tempbpb::where('Mattype', $m_type)->update([
                'BPBNo' => $no_type,
            ]);
        }else{
            $TempBpbData = [];
            array_push($TempBpbData, [
                "Mattype" => $m_type,
                "BPBNo" => $no_type,
            ]);
            $TempBpbStore = Tempbpb::insert($TempBpbData);
        }

        if ($hasilcek2 != '0') {
            $update_tempbpb2 = Tempbpb::where('Mattype', $m_type2)->update([
                'BPBNo' => $no_type2,
            ]);
        }else{
            $TempBpbData2 = [];
            array_push($TempBpbData2, [
                "Mattype" => $m_type2,
                "BPBNo" => $no_type2,
            ]);
            $TempBpbStore2 = Tempbpb::insert($TempBpbData2);
        }
        $jml_qtyout = 0;

        for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            $bppb_headerSB = BppbSB::create([
                'bppbno' => $bpbno,
                'bppbno_int' => $bppbno_int,
                'bppbdate' => $request['txt_tgl_bppb'],
                'id_item' => $request["id_item"][$i],
                'qty' => $request["input_qty"][$i],
                'price' => '0',
                'remark' => $request['txt_notes'],
                'use_kite' => '1',
                'berat_bersih' => '0',
                'berat_kotor' => '0',
                'username' => Auth::user()->name,
                'unit' => $request["unit"][$i],
                'qty_karton' => '0',
                'tanggal_aju' => $request['txt_tgl_bppb'],
                'bcdate' => $request['txt_tgl_bppb'],
                'jenis_dok' => $request['txt_dok_bc'],
                'id_supplier' => $request['txt_supp'],
                'id_jo' => $request["id_jo"][$i],
                'jenis_trans' => $request['txt_jns_klr'],
                'id_po' => $request["id_po"][$i],
            ]);
            
        }


        $bppb_header = PackingOutH::create([
            'no_bppb' => $bppbno_int,
            'tgl_bppb' => $request['txt_tgl_bppb'],
            'no_po' => $request['txt_no_po'],
            'id_supplier' => $request['txt_supp'],
            'jenis_pengeluaran' => $request['txt_jns_klr'],
            'jenis_dok' => $request['txt_dok_bc'],
            'berat_garment' => $request['txt_qty_garment'],
            'berat_karton' => $request['txt_qty_karton'],
            'keterangan' => $request['txt_notes'],
            'status' => 'DRAFT',
            'created_by' => Auth::user()->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);



        $bppb_detail = DB::connection('mysql_sb')->insert("insert into packing_out_det select '', '".$bppbno_int."', id_po, id_jo, id_item, color, size, unit, qty, status, created_by, created_at, updated_at from packing_out_det_temp where created_by = '".Auth::user()->name."'");
        $bppb_temp = PackingOutDetTemp::where('created_by',Auth::user()->name)->delete();

        $massage = $bppbno_int . ' Saved Succesfully';
        $stat = 200;
    // }else{
    //     $massage = ' Please Input Data';
    //     $stat = 400;
    // }


        return array(
            "status" =>  $stat,
            "message" => $massage,
            "additional" => [],
            "redirect" => route('packing-out-subcont')
        );

    }


    public function DetailPackingOut($id)
    {
        $header = DB::connection('mysql_sb')->selectOne("select a.id, a.no_bppb, a.tgl_bppb, a.no_po, supplier, buyer, jenis_pengeluaran, jenis_dok, CONCAT(a.created_by,' (',a.created_at,')') created_by, a.status from packing_out_h a INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier left join (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer group by id_jo) d on d.id_jo=b.id_jo WHERE a.id = ? GROUP BY a.no_bppb", [$id]);

        $detail = DB::connection('mysql_sb')->select("select b.id, kpno, styleno, mi.itemdesc, b.color, b.size, b.qty, b.unit from packing_out_h a INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier left join (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer group by id_jo) d on d.id_jo=b.id_jo INNER JOIN masteritem mi on mi.id_item = b.id_item WHERE a.id = ? and b.status = 'Y' GROUP BY b.id
                ", [$id]);

        return response()->json([
            'header' => $header,
            'detail' => $detail
        ]);
    }



public function PLPackingOut($id)
{
    return Excel::download(new PLPackingOutExport($id), 'Packing List.xlsx');
}


public function ApprovePackingOutSubcont(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $keywordQuery = "";

            $data_inmaterial = DB::connection('mysql_sb')->select("select a.id, a.no_bppb, a.tgl_bppb, a.no_po, supplier, buyer, jenis_pengeluaran, jenis_dok, CONCAT(a.created_by,' (',a.created_at,')') created_by, a.status from packing_out_h a INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier left join (select id_jo,kpno,styleno, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer group by id_jo) d on d.id_jo=b.id_jo where a.tgl_bppb BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' and a.status = 'DRAFT' GROUP BY a.no_bppb");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("packing-subcont.approve-packing-out", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,'page' => 'dashboard-packing', "subPageGroup" => "approve-packing-packing-out", "subPage" => "approve-packing-out-subcont"]);
    }

    public function SaveApprovePackingOut(Request $request)
    {
        $timestamp = Carbon::now();

        foreach ($request->id_bpb as $i => $id_bpb) {

            $check = $request->chek_id[$i] ?? 0;
            if ($check <= 0) continue;

        // Update status di nds
            PackingOutH::where('no_bppb', $id_bpb)->update([
                'status' => 'APPROVED',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
            ]);

        // Update status di signalbit
            BppbSB::where('bppbno_int', $id_bpb)->update([
                'confirm' => 'Y',
                'confirm_by' => Auth::user()->name,
                'confirm_date' => $timestamp,
            ]);

        }

        return response()->json([
            "status" => 200,
            "message" => "Approved Data Successfully",
            "additional" => [],
        ]);
    }


    public function ReportOutSubcont(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_dok >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_dok <= '" . $request->dateTo . "' ";
            }


            $data_pemasukan = DB::connection('mysql_sb')->select("select *, CONCAT_WS('',no_dok,tgl_dok,no_mut,supplier,rak,barcode,no_roll,no_lot,qty,qty_mut,satuan,id_item,id_jo,no_ws,goods_code,itemdesc,color,size,deskripsi,username,confirm_by) cari_data from (select a.no_dok,b.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,b.id_item,b.id_jo,b.no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by from whs_inmaterial_fabric a inner join whs_inmaterial_fabric_det b on b.no_dok = a.no_dok  inner join whs_lokasi_inmaterial c on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item where c.status = 'Y' and left(a.no_dok,2) ='GK' " . $additionalQuery . " group by c.id) a");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("packing-subcont.report-packing-out", ["page" => "dashboard-warehouse"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
