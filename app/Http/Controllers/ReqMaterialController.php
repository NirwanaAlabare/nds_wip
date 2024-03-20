<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\Marker;
use App\Models\MasterLokasi;
use App\Models\UnitLokasi;
use App\Models\InMaterialFabric;
use App\Models\InMaterialFabricDet;
use App\Models\Bpb;
use App\Models\Tempbpb;
use App\Models\BppbReq;
use Illuminate\Support\Facades\Auth;
use App\Models\MarkerDetail;
use App\Models\InMaterialLokasi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;
use QrCode;
use DNS1D;
use PDF;

class ReqMaterialController extends Controller
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

            if ($request->tipe_data == 'header') {
                $data_request = DB::connection('mysql_sb')->select("select username,bppbno,bppbdate,supplier,kpno,styleno,buyer, idws_act ,mattype, format(sum(qty_req),2) qty_req, format(sum(qty_out),2) qty_out,  group_concat(distinct(bppbno_int)) bppbno_int, unit from (select a.username,a.bppbno,a.id_item, a.id_jo,a.bppbdate,s.supplier,ac.kpno,ac.styleno,ms.supplier buyer, a.idws_act, round(coalesce(sum(a.qty),0),2) qty_req, round(coalesce(sum(bppb.qty),0),2) qty_out, group_concat(distinct(bppb.bppbno_int)) bppbno_int, IF(bppb.unit is null,a.unit,bppb.unit) unit ,itm.mattype
        from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier 
                INNER JOIN (select id_item, mattype,matclass from masteritem GROUP BY id_item) itm on itm.id_item                                = a.id_item
        inner join jo_det jod on a.id_jo=jod.id_jo 
        inner join so on jod.id_so=so.id 
        inner join act_costing ac on so.id_cost=ac.id 
        inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
                left join (select bppbno,bppbno_int,bppbno_req, id_item, id_jo, sum(qty) qty, unit from bppb group by bppbno) bppb on a.id_item = bppb.id_item and a.id_jo = bppb.id_jo and a.bppbno = bppb.bppbno_req
        where a.bppbdate >='".$request->tgl_awal."' and a.bppbdate <='".$request->tgl_akhir."'
                group by a.bppbno, a.id_item, a.id_jo
        order by a.bppbdate desc) a GROUP BY bppbno");
            }else{
                $data_request = DB::connection('mysql_sb')->select("select a.username,a.bppbno,a.id_item, a.id_jo,a.bppbdate,s.supplier,ac.kpno,ac.styleno,ms.supplier buyer, a.idws_act, format(round(coalesce(sum(a.qty),0),2),2) qty_req, format(round(coalesce(sum(bppb.qty),0),2),2) qty_out, group_concat(distinct(bppb.bppbno_int)) bppbno_int, IF(bppb.unit is null,a.unit,bppb.unit) unit ,itm.mattype
        from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier 
                INNER JOIN (select id_item, mattype,matclass from masteritem GROUP BY id_item) itm on itm.id_item                                = a.id_item
        inner join jo_det jod on a.id_jo=jod.id_jo 
        inner join so on jod.id_so=so.id 
        inner join act_costing ac on so.id_cost=ac.id 
        inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
                left join (select bppbno,bppbno_int,bppbno_req, id_item, id_jo, sum(qty) qty, unit from bppb group by bppbno) bppb on a.id_item = bppb.id_item and a.id_jo = bppb.id_jo and a.bppbno = bppb.bppbno_req
        where a.bppbdate >='".$request->tgl_awal."' and a.bppbdate <='".$request->tgl_akhir."'
                group by a.bppbno, a.id_item, a.id_jo
        order by a.bppbdate desc");
            }


            return DataTables::of($data_request)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('area', '!=', 'LINE')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("reqmaterial.req-material", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('area', '!=', 'LINE')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT('RQ-F', IF(MAX(bppbno) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0))) kode, IF(MAX(bppbno) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0)) nomor FROM bppb_req WHERE LEFT(bppbno,4) = 'RQ-F'");

        $tipe_ws = DB::connection('mysql_sb')->select("select type_ws isi, type_ws tampil from act_costing group by type_ws order by 
    case type_ws when 'STD' then '1'
    when 'DTH' then '2'
    when 'GLOBAL' then '3'
    else '4'
    end");

        return view('reqmaterial.create-reqmaterial', ['tipe_ws' => $tipe_ws,'kode_gr' => $kode_gr,'gr_type' => $gr_type,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
    }

    public function editrequest($bppbno)
    {

        $data_head = DB::connection('mysql_sb')->select("select DISTINCT bppbno, bppbdate,id_supplier,remark  from bppb_req where bppbno = '$bppbno'");
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('area', '!=', 'LINE')->get();

        $tipe_ws = DB::connection('mysql_sb')->select("select type_ws isi, type_ws tampil from act_costing group by type_ws order by 
    case type_ws when 'STD' then '1'
    when 'DTH' then '2'
    when 'GLOBAL' then '3'
    else '4'
    end");

        $det_data = DB::connection('mysql_sb')->select("select ac.kpno,idws_act,ac.styleno,jo.jo_no,mi.id_item, a.id_jo, mi.goods_code, mi.itemdesc, a.qty, round(coalesce(sum(bppb.qty),0),2) qty_out, a.unit 
        from bppb_req a
        inner join masteritem mi on a.id_item = mi.id_item
        left join bppb on a.bppbno = bppb.bppbno_req and a.id_item = bppb.id_item and a.id_jo = bppb.id_jo
        inner join jo_det jd on a.id_jo = jd.id_jo
        inner join jo on jd.id_jo = jo.id
        inner join so on jd.id_so = so.id
        inner join act_costing ac on so.id_cost = ac.id
        where a.bppbno = '$bppbno'
        group by a.bppbno, a.id_item");

        $jml_det = DB::connection('mysql_sb')->select("select COUNT(id_item) jml_dok from (select ac.kpno,idws_act,ac.styleno,jo.jo_no,mi.id_item, a.id_jo, mi.goods_code, mi.itemdesc, a.qty, round(coalesce(sum(bppb.qty),0),2) qty_out, a.unit 
        from bppb_req a
        inner join masteritem mi on a.id_item = mi.id_item
        left join bppb on a.bppbno = bppb.bppbno_req and a.id_item = bppb.id_item and a.id_jo = bppb.id_jo
        inner join jo_det jd on a.id_jo = jd.id_jo
        inner join jo on jd.id_jo = jo.id
        inner join so on jd.id_so = so.id
        inner join act_costing ac on so.id_cost = ac.id
        where a.bppbno = '$bppbno'
        group by a.bppbno, a.id_item) a");

        return view('reqmaterial.edit-reqmaterial', ['tipe_ws' => $tipe_ws,'data_head' => $data_head,'det_data' => $det_data,'msupplier' => $msupplier,'jml_det' => $jml_det, 'page' => 'dashboard-warehouse']);
    }


    public function getWSReq(Request $request)
    {
        $nomorws = DB::connection('mysql_sb')->select("select a.id isi,concat(a.jo_no,' | ',ac.styleno,' | ',ac.kpno) tampil 
  from jo a inner join jo_det s on a.id=s.id_jo 
  inner join  so on s.id_so=so.id 
  inner join act_costing ac on so.id_cost=ac.id 
  where ac.type_ws = '" . $request->tipe_ws . "'
  group by a.id ");

        $html = "<option value=''>Pilih WS</option>";

        foreach ($nomorws as $ws) {
            $html .= " <option value='" . $ws->isi . "'>" . $ws->tampil . "</option> ";
        }

        return $html;
    }

    public function getWSact(Request $request)
    {
        $nomorwsact = DB::connection('mysql_sb')->select("select ac.kpno isi,concat(a.jo_no,' | ',ac.styleno,' | ',ac.kpno) tampil 
    from jo a inner join jo_det s on a.id=s.id_jo 
    inner join  so on s.id_so=so.id 
    inner join act_costing ac on so.id_cost=ac.id 
    inner join (select id_jo from bom_jo_item group by id_jo)   k on s.id_jo = k.id_jo
    where ac.type_ws = 'STD'
    group by a.id");

        $html = "<option value=''>Pilih WS</option>";

        foreach ($nomorwsact as $wsact) {
            $html .= " <option value='" . $wsact->isi . "'>" . $wsact->tampil . "</option> ";
        }

        return $html;
    }


    public function showdetail(Request $request)
    {
        $tipe = $request->tipe_ws;
        // dd($tipe);
        if ($tipe == 'STD') {
            $det_item = DB::connection('mysql_sb')->select("select a.id_item,ac.kpno,jo_no,a.goods_code,a.id_jo,a.mattype,a.matclass,a.itemdesc,    round( COALESCE ( sum( qty_bpb ), 0 ), 2 ) qty_bpb,
            round(COALESCE ( sum( qty_bppb ), 0 ),2) qty_bppb,
            round(COALESCE ( sum( qty_bpb ), 0 ) - COALESCE ( sum( qty_bppb ), 0 ),2) sisa_stok,
            round(COALESCE ( br.qty_br, 0 ),2) qty_br,
            round(COALESCE ( br.qty_br_out, 0 ),2) qty_br_out,
            round( COALESCE ( br.qty_br, 0 ) - COALESCE ( br.qty_br_out, 0 ), 2 ) sisa_req,  a.unit from
            (select id_item,goods_code,id_jo,mattype, matclass, itemdesc,round(sum(qty_bpb),2) qty_bpb, '0' qty_bppb,unit from (select 'SA' id,b.id_item,b.goods_code,a.id_jo,b.mattype, b.matclass, b.itemdesc,round(sum(qty_good),2) qty_bpb, '0' qty_bppb,unit from whs_inmaterial_fabric_det a inner join masteritem b on b.id_item = a.id_item where id_jo = '" . $request->id_jo . "' and status = 'Y' GROUP BY a.id_item,id_jo,unit
UNION
select 'TR' id,b.id_item,b.goods_code,a.id_jo,b.mattype, b.matclass, b.itemdesc,round(sum(qty),2) qty_bpb, '0' qty_bppb,unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item where id_jo = '" . $request->id_jo . "' and qty > 0 and qty_mut is null GROUP BY a.id_item,id_jo,unit
UNION
select 'OUT' id,b.id_item,b.goods_code,a.id_jo,b.mattype, b.matclass, b.itemdesc,'0' qty_bpb, round(sum(qty_out),2) qty_bppb,satuan from whs_bppb_det a inner join  masteritem b on b.id_item = a.id_item where id_jo = '" . $request->id_jo . "' and a.status = 'Y' GROUP BY a.id_item,id_jo,satuan) a GROUP BY a.id_item,id_jo,unit) a
            inner join jo_det jd on a.id_jo = jd.id_jo
            inner join jo on jd.id_jo = jo.id
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            left join 
            (select mi.id_item,mi.goods_code,br.id_jo,mi.mattype, mi.matclass, mi.itemdesc,round(sum(br.qty),2) qty_br,round(sum(bppb.qty),2) qty_br_out
            from bppb_req br
            inner join masteritem mi on br.id_item = mi.id_item
            left join 
            (select bppb.bppbno_req,id_jo, bppb.id_item, sum(qty) qty, unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item 
            where id_jo = '" . $request->id_jo . "' and mi.mattype = 'F' and bppbno like 'SJ-F%'
            group by mi.id_item, bppb.id_jo, bppbno_req) bppb on br.bppbno = bppb.bppbno_req and br.id_item = bppb.id_item and br.id_jo = bppb.id_jo
            where br.id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'  and br.unit is not null
            group by mi.id_item, br.id_jo
            ) br on a.id_item = br.id_item and a.id_jo = br.id_jo
            group by a.id_item, a.id_jo, a.unit");
        }else if ($tipe == 'GLOBAL') {
            $det_item = DB::connection('mysql_sb')->select("select a.id_item,ac.kpno,jo_no,a.goods_code,a.id_jo,a.mattype,a.matclass,a.itemdesc,    round( COALESCE ( sum( qty_bpb ), 0 ), 2 ) qty_bpb,
            round(COALESCE ( sum( qty_bppb ), 0 ),2) qty_bppb,
            round(COALESCE ( sum( qty_bpb ), 0 ) - COALESCE ( sum( qty_bppb ), 0 ),2) sisa_stok,
            round(COALESCE ( br.qty_br, 0 ),2) qty_br,
            round(COALESCE ( br.qty_br_out, 0 ),2) qty_br_out,
            round( COALESCE ( br.qty_br, 0 ) - COALESCE ( br.qty_br_out, 0 ), 2 ) sisa_req,  a.unit from
            (select mi.id_item,mi.goods_code,k.id_jo,mi.mattype, mi.matclass, mi.itemdesc, sum(bpb.qty) qty_bpb, '0' qty_bppb, bpb.unit
            from (select id_item,id_jo from bom_jo_global_item k where k.id_jo = '" . $request->id_jo . "' group by id_item) k 
            inner join masteritem mi on k.id_item = mi.id_item
            left join bpb on mi.id_item = bpb.id_item and k.id_jo = bpb.id_jo          
            where k.id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'
            group by mi.id_item, bpb.id_jo, bpb.unit
            union
            select mi.id_item,mi.goods_code,id_jo,mi.mattype, mi.matclass, mi.itemdesc, '0' qty_bpb, sum(bppb.qty) qty_bppb, bppb.unit
            from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            where bppb.id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'  and bppb.unit is not null
            group by mi.id_item, bppb.id_jo, bppb.unit                  
            ) a
            inner join jo_det jd on a.id_jo = jd.id_jo
            inner join jo on jd.id_jo = jo.id
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            left join 
            (select mi.id_item,mi.goods_code,br.id_jo,mi.mattype, mi.matclass, mi.itemdesc,round(sum(br.qty),2) qty_br,round(sum(bppb.qty),2) qty_br_out
            from bppb_req br
            inner join masteritem mi on br.id_item = mi.id_item
            left join bppb on br.bppbno = bppb.bppbno_req and br.id_item = bppb.id_item and br.id_jo = bppb.id_jo
            where br.id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'  and br.unit is not null
            group by mi.id_item, br.id_jo
            ) br on a.id_item = br.id_item and a.id_jo = br.id_jo
            group by a.id_item, a.id_jo, a.unit");           
        } else if ($tipe == 'DTH'){
            $sql = DB::connection('mysql_sb')->select("select mi.id_item, ac.kpno,jo.jo_no,mi.goods_code,mi.itemdesc, a.id_jo, 
            round(coalesce(sum(a.qty_in),0),2) qty_bpb, 
            round(coalesce(sum(a.qty_out),0),2) qty_bppb,
            round(coalesce(sum(a.qty_in) - sum(a.qty_out),0),2) sisa_stok, 
            round(coalesce(sum(qty_br),0),2) qty_br, 
            round(coalesce(sum(qty_br_out),0),2) qty_br_out,
            round(coalesce(sum(qty_br) - sum(qty_br_out),0),2) sisa_req,
            a.unit
            from (
            select mi.id_item,id_jo, sum(bpb.qty) qty_in, '0' qty_out,bpb.unit
            from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            where id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'
            group by id_item, id_jo, unit
            union 
            select mi.id_item,id_jo, '0' qty_in,sum(bppb.qty) qty_out,bppb.unit
            from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            where id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'
            group by id_item, id_jo, unit
            ) a
            left join masteritem mi on a.id_item = mi.id_item
            inner join jo_det jd on a.id_jo  = jd.id_jo
            inner join jo on a.id_jo = jo.id
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            left join (select mi.id_item, br.id_jo,round(coalesce(sum(br.qty),0),2) qty_br,round(coalesce(sum(bppb.qty),0),2) qty_br_out, br.unit from bppb_req br
            inner join masteritem mi on br.id_item = mi.id_item
            left join bppb on br.bppbno = bppb.bppbno_req and br.id_item = bppb.id_item and br.id_jo = bppb.id_jo
            where br.id_jo= '" . $request->id_jo . "' and mi.mattype = 'F' and br.bppbdate >= '2023-01-01'
            group by mi.id_item, br.unit) br on a.id_item = br.id_item and a.id_jo = br.id_jo and a.unit = br.unit
            group by mi.id_item, a.id_jo, unit
            having sum(a.qty_in) - sum(a.qty_out) >'0'");            
        }  

            $html = '';
            $x = 1;
            $sisa_req = 0;
            $qty_sisa = 0;
            $qty_sekarang = 0;
            $sisareq = 0;
            $status = '';
        if (is_array($det_item) || is_object($det_item)){
        foreach ($det_item as $detitem) {
            $sisa_req = $detitem->sisa_req ? $detitem->sisa_req : 0;
            if ($sisa_req > 0) {
                $sisareq = $sisa_req;
            }else{
                $sisareq = 0;
            }
            $qty_sisa = $detitem->sisa_stok ? $detitem->sisa_stok : 0;
            $qty_sekarang = $qty_sisa - $sisareq;
            if ($qty_sekarang > 0) {
                $status = '';
            }else{
                $status = 'readonly';
            }
            $html .= ' <tr>
                        <td >'.$detitem->jo_no.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="jo_no'.$x.'" name="jo_no['.$x.']" value="'.$detitem->jo_no.'" / readonly></td>
                        <td >'.$detitem->kpno.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="kpno'.$x.'" name="kpno['.$x.']" value="'.$detitem->kpno.'" / readonly></td>
                        <td >'.$detitem->id_item.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="id_item'.$x.'" name="id_item['.$x.']" value="'.$detitem->id_item.'" / readonly></td>
                        <td >'.$detitem->goods_code.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="goods_code'.$x.'" name="goods_code['.$x.']" value="'.$detitem->goods_code.'" / readonly></td>
                        <td >'.$detitem->itemdesc.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="itemdesc'.$x.'" name="itemdesc['.$x.']" value="'.$detitem->itemdesc.'" / readonly></td>
                        <td class="text-right">'.$detitem->qty_bpb.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="qty_bpb'.$x.'" name="qty_bpb['.$x.']" value="'.$detitem->qty_bpb.'" / readonly></td>
                        <td class="text-right">'.$detitem->qty_bppb.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="qty_bppb'.$x.'" name="qty_bppb['.$x.']" value="'.$detitem->qty_bppb.'" / readonly></td>
                        <td class="text-right">'.$detitem->sisa_stok.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="sisa_stok'.$x.'" name="sisa_stok['.$x.']" value="'.$detitem->sisa_stok.'" / readonly></td>
                        <td class="text-right">'.$sisareq.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="sisa_req'.$x.'" name="sisa_req['.$x.']" value="'.$sisareq.'" / readonly></td>
                        <td class="text-right">'.$qty_sekarang.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="qty_sekarang'.$x.'" name="qty_sekarang['.$x.']" value="'.$qty_sekarang.'" / readonly></td>
                        <td><input style="width:90px;text-align:right;" class="form-control" type="text" id="qty_request'.$x.'" name="qty_request['.$x.']" onkeyup="tambahqty()" / '.$status.'></td>
                       <td >'.$detitem->unit.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="unit'.$x.'" name="unit['.$x.']" value="'.$detitem->unit.'" / readonly></td>
                        <td hidden> <input type="hidden" id="id_jo'.$x.'" name="id_jo['.$x.']" value="'.$detitem->id_jo.'" / readonly></td>
                       </tr>';
                       $x++;
        }
    }

        return $html;
    }


    public function sumdetail(Request $request)
    {
        $tipe = $request->tipe_ws;
        // dd($tipe);
        if ($tipe == 'STD') {
            $sum_item = DB::connection('mysql_sb')->select("select COUNT(id_item) jml_item from (select a.id_item,ac.kpno,jo_no,a.goods_code,a.id_jo,a.mattype,a.matclass,a.itemdesc,    round( COALESCE ( sum( qty_bpb ), 0 ), 2 ) qty_bpb,
            round(COALESCE ( sum( qty_bppb ), 0 ),2) qty_bppb,
            round(COALESCE ( sum( qty_bpb ), 0 ) - COALESCE ( sum( qty_bppb ), 0 ),2) sisa_stok,
            round(COALESCE ( br.qty_br, 0 ),2) qty_br,
            round(COALESCE ( br.qty_br_out, 0 ),2) qty_br_out,
            round( COALESCE ( br.qty_br, 0 ) - COALESCE ( br.qty_br_out, 0 ), 2 ) sisa_req,  a.unit from
            (select id_item,goods_code,id_jo,mattype, matclass, itemdesc,round(sum(qty_bpb),2) qty_bpb, '0' qty_bppb,unit from (select 'SA' id,b.id_item,b.goods_code,a.id_jo,b.mattype, b.matclass, b.itemdesc,round(sum(qty_good),2) qty_bpb, '0' qty_bppb,unit from whs_inmaterial_fabric_det a inner join masteritem b on b.id_item = a.id_item where id_jo = '" . $request->id_jo . "' and status = 'Y' GROUP BY a.id_item,id_jo,unit
UNION
select 'TR' id,b.id_item,b.goods_code,a.id_jo,b.mattype, b.matclass, b.itemdesc,round(sum(qty),2) qty_bpb, '0' qty_bppb,unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item where id_jo = '" . $request->id_jo . "' and qty > 0 and qty_mut is null GROUP BY a.id_item,id_jo,unit
UNION
select 'OUT' id,b.id_item,b.goods_code,a.id_jo,b.mattype, b.matclass, b.itemdesc,'0' qty_bpb, round(sum(qty_out),2) qty_bppb,satuan from whs_bppb_det a inner join  masteritem b on b.id_item = a.id_item where id_jo = '" . $request->id_jo . "' and a.status = 'Y' GROUP BY a.id_item,id_jo,satuan) a GROUP BY a.id_item,id_jo,unit) a
            inner join jo_det jd on a.id_jo = jd.id_jo
            inner join jo on jd.id_jo = jo.id
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            left join 
            (select mi.id_item,mi.goods_code,br.id_jo,mi.mattype, mi.matclass, mi.itemdesc,round(sum(br.qty),2) qty_br,round(sum(bppb.qty),2) qty_br_out
            from bppb_req br
            inner join masteritem mi on br.id_item = mi.id_item
            left join 
            (select bppb.bppbno_req,id_jo, bppb.id_item, sum(qty) qty, unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item 
            where id_jo = '" . $request->id_jo . "' and mi.mattype = 'F' and bppbno like 'SJ-F%'
            group by mi.id_item, bppb.id_jo, bppbno_req) bppb on br.bppbno = bppb.bppbno_req and br.id_item = bppb.id_item and br.id_jo = bppb.id_jo
            where br.id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'  and br.unit is not null
            group by mi.id_item, br.id_jo
            ) br on a.id_item = br.id_item and a.id_jo = br.id_jo
            group by a.id_item, a.id_jo, a.unit) a");           
        } else if ($tipe == 'DTH'){
            $sql = DB::connection('mysql_sb')->select("select COUNT(id_item) jml_item from (select mi.id_item, ac.kpno,jo.jo_no,mi.goods_code,mi.itemdesc, a.id_jo, 
            round(coalesce(sum(a.qty_in),0),2) qty_bpb, 
            round(coalesce(sum(a.qty_out),0),2) qty_bppb,
            round(coalesce(sum(a.qty_in) - sum(a.qty_out),0),2) sisa_stok, 
            round(coalesce(sum(qty_br),0),2) qty_br, 
            round(coalesce(sum(qty_br_out),0),2) qty_br_out,
            round(coalesce(sum(qty_br) - sum(qty_br_out),0),2) sisa_req,
            a.unit
            from (
            select mi.id_item,id_jo, sum(bpb.qty) qty_in, '0' qty_out,bpb.unit
            from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            where id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'
            group by id_item, id_jo, unit
            union 
            select mi.id_item,id_jo, '0' qty_in,sum(bppb.qty) qty_out,bppb.unit
            from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            where id_jo = '" . $request->id_jo . "' and mi.mattype = 'F'
            group by id_item, id_jo, unit
            ) a
            left join masteritem mi on a.id_item = mi.id_item
            inner join jo_det jd on a.id_jo  = jd.id_jo
            inner join jo on a.id_jo = jo.id
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            left join (select mi.id_item, br.id_jo,round(coalesce(sum(br.qty),0),2) qty_br,round(coalesce(sum(bppb.qty),0),2) qty_br_out, br.unit from bppb_req br
            inner join masteritem mi on br.id_item = mi.id_item
            left join bppb on br.bppbno = bppb.bppbno_req and br.id_item = bppb.id_item and br.id_jo = bppb.id_jo
            where br.id_jo= '" . $request->id_jo . "' and mi.mattype = 'F' and br.bppbdate >= '2023-01-01'
            group by mi.id_item, br.unit) br on a.id_item = br.id_item and a.id_jo = br.id_jo and a.unit = br.unit
            group by mi.id_item, a.id_jo, unit
            having sum(a.qty_in) - sum(a.qty_out) >'0') a");            
        }    
        // dd($sum_item);
        
        return $sum_item;
    }


    public function approvematerial(Request $request)
    {
            $timestamp = Carbon::now();
            $updateLokasi = InMaterialFabric::where('no_dok', $request['txt_nodok'])->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
            ]);
        
        $massage = 'Approved Data Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/in-material')
            );
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    // if (intval($request['jumlah_qty']) > 0) {

        $tglbpb = $request['txt_tgl_gr'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('RQ-F', IF(MAX(bppbno) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0))) no_dok, IF(MAX(bppbno) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0)) nomor FROM bppb_req WHERE LEFT(bppbno,4) = 'RQ-F'");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
        $no_dok = $Mattype1[0]->no_dok;
        $no_type = $Mattype1[0]->nomor;

        $cek_mattype = DB::connection('mysql_sb')->select("select * from tempbpb where Mattype = 'R.F'");
        $hasilcek = $cek_mattype ? $cek_mattype[0]->Mattype : 0;


        if ($hasilcek != '0') {
            $update_tempbpb = Tempbpb::where('Mattype', 'R.F')->update([
                'BPBNo' => $no_type,
            ]);
        }else{
            $TempBpbData = [];
            array_push($TempBpbData, [
                "Mattype" => 'R.F',
                "BPBNo" => $no_type,
            ]);
            $TempBpbStore = Tempbpb::insert($TempBpbData);
        }


            $timestamp = Carbon::now();
            $tgldok = $request['req_date'];
            $id_supplier = $request['dikirim_ke'];
            $tipe_mat = $request['tipe_mat'];
            $tipe_ws = $request['tipe_ws'];
            $job_order = $request['job_order'];
            $ws_act = $request['ws_act'];
            $remark = $request['txt_notes'];
            $DatabppbReq = [];
            for ($i = 1; $i <= intval($request['jumlah_data']); $i++) {
            if ($request["qty_request"][$i] > 0 ) {

                // dd($detdata);
                array_push($DatabppbReq, [
                    "bppbno" => $no_dok,
                    "bppbdate" => $tgldok,
                    "id_item" => $request["id_item"][$i],
                    "qty" => $request["qty_request"][$i],
                    "remark" => $remark,
                    "username" => Auth::user()->name,
                    "unit" => $request["unit"][$i],
                    "id_supplier" => $id_supplier,
                    "id_jo" => $request["id_jo"][$i],
                    "cancel" => 'N',
                    "idws_act" => $ws_act,
                    "qty_out" => '0',
                    "use_kite" => '1',
                    "berat_bersih" => '0',
                    "berat_kotor" => '0',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            

            $StorebppbReq = BppbReq::insert($DatabppbReq);


            $massage = $no_dok . ' Saved Succesfully';
            $stat = 200;
    // }else{
    //     $massage = ' Please Input Data';
    //     $stat = 400;
    // }


            return array(
                "status" =>  $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/req-material')
            );

    }

    public function pdfreqmaterial(Request $request, $bppbno)
    {
       
       
            $dataHeader = DB::connection('mysql_sb')->select("select DISTINCT b.username,b.bppbno,b.bppbdate,ac.kpno,ac.styleno,b.tanggal_aju,supplier tujuan,b.idws_act,so.mindeldate as del_date FROM bppb_req b INNER JOIN masteritem mi on b.id_item = mi.id_item INNER JOIN mastersupplier msup on b.id_supplier=msup.id_supplier INNER JOIN (select id_so,id_jo from jo_det group by id_jo) jod on b.id_jo=jod.id_jo inner join (select so.id,id_cost,min(sod.deldate_det) mindeldate from so inner join so_det sod on so.id=sod.id_so group by so.id) so on jod.id_so=so.id inner join act_costing ac on so.id_cost=ac.id WHERE 1=1 AND bppbno = '$bppbno'");
            $dataDetail = DB::connection('mysql_sb')->select("select b.username,b.bppbno,b.bppbdate,ac.kpno,ac.styleno,b.tanggal_aju,supplier tujuan,so.mindeldate as del_date,concat(mi.goods_code,' ',mi.itemdesc) itemdesc,mi.color,no_rak as location,qtyloc as loc_qty,unitloc as loc_unit,b.qty as qty_request,qtysdhout as out_qty,unitsdhout as out_unit,'' as check_picker,'' as check_loader,'' as check_penerima,b.remark,b.idws_act FROM bppb_req b INNER JOIN masteritem mi on b.id_item = mi.id_item INNER JOIN mastersupplier msup on b.id_supplier=msup.id_supplier INNER JOIN (select id_so,id_jo from jo_det group by id_jo)  jod on b.id_jo=jod.id_jo inner join (select so.id,id_cost,min(sod.deldate_det) mindeldate from so inner join so_det sod on so.id=sod.id_so group by so.id) so on jod.id_so=so.id inner join act_costing ac on so.id_cost=ac.id 
                left join (select id_item,id_jo,group_concat(kode_rak,' ',qtyloc,' ',unitloc SEPARATOR ', ') no_rak,0 qtyloc,'' unitloc from (select a.id_item,a.id_jo,d.kode_rak,round(sum(roll_qty),2) qtyloc, round(sum(roll_qty_used),2) qtyused,round(sum(roll_qty),2) - round(sum(roll_qty_used),2) qtysisa,unit unitloc from bpb_roll_h a inner join bpb_roll s on a.id=s.id_h inner join master_rak d on s.id_rak_loc=d.id group by id_item,id_jo,d.kode_rak having round(sum(roll_qty),2) - round(sum(roll_qty_used),2) != '0') tmplok group by id_item,id_jo) tbllok on b.id_item=tbllok.id_item and b.id_jo=tbllok.id_jo left join (select bppbno_req,id_item,id_jo,sum(qty) qtysdhout,unit unitsdhout from bppb where bppbno_req='$bppbno' group by id_item,id_jo) tblsdhout on b.bppbno=tblsdhout.bppbno_req and b.id_item=tblsdhout.id_item WHERE 1=1 AND bppbno = '$bppbno'");
            $dataSum = DB::connection('mysql_sb')->select("select sum(qty) total_req FROM bppb_req where bppbno = '$bppbno'");
            $dataUser = DB::connection('mysql_sb')->select("select created_by,created_at,approved_by,approved_date from whs_inmaterial_fabric where id = '$bppbno' limit 1");


            PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
            $pdf = PDF::loadView('reqmaterial.pdf.print-pdf', ["dataHeader" => $dataHeader,"dataDetail" => $dataDetail,"dataSum" => $dataSum,"dataUser" => $dataUser])->setPaper('a4', 'potrait');

            $path = public_path('pdf/');
            $fileName = 'pdf-material.pdf';
            $pdf->save($path . '/' . $fileName);
            $generatedFilePath = public_path('pdf/'.$fileName);

            return response()->download($generatedFilePath);
        
    }

    public function updateReq(Request $request)
    {
                
            $updateInMaterial = BppbReq::where('bppbno', $request['no_req'])->update([
                'bppbdate' => $request['req_date'],
                'id_supplier' => $request['dikirim_ke'],
                'remark' => $request['txt_notes'],
            ]);

        for ($i = 1; $i <= intval($request['txt_jmldet']); $i++) {
                $updateInMaterialDet = BppbReq::where('bppbno', $request['no_req'])->where('id_item', $request["id_item"][$i])->update([
                'qty' => $request["qty_input"][$i],
            ]);
            }

        $massage = 'Edit Data Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/req-material')
            );
        
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function edit(Stocker $stocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
    
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }

  

    
}
