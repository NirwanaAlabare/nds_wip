<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\MasterLokasi;
use App\Models\UnitLokasi;
use App\Models\InMaterialFabric;
use App\Models\InMaterialFabricDet;
use App\Models\BppbDetTemp;
use App\Models\BppbDet;
use App\Models\BppbRO;
use App\Models\BppbReq;
use App\Models\BppbHeader;
use App\Models\BppbSB;
use App\Models\Tempbpb;
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

class ReturMaterialController extends Controller
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

            if ($request->bc_type != 'ALL') {
                $where = " and a.dok_bc = '" . $request->bc_type . "' ";
            }else{
                $where = "";
            }

            if ($request->status != 'ALL') {
                $where2 = " and a.status = '" . $request->status . "' ";
            }else{
                $where2 = "";
            }


            $data_inmaterial = DB::connection('mysql_sb')->select("select a.no_bppb,a.tgl_bppb,ac.styleno,a.no_ws,'Fabric' jns_material,'' jns_retur,a.dok_bc,a.jns_pemasukan,CONCAT(a.created_by,' (',a.created_at, ') ') user_create,a.status,a.id,SUM(qty_out) qty_ro,coalesce(qty_barcode,0) qty_barcode, ROUND(SUM(qty_out) - coalesce(qty_barcode,0),2 ) balance from whs_bppb_h a inner join whs_bppb_ro b on b.no_bppb = a.no_bppb left join jo_det jod on b.id_jo=jod.id_jo left join jo on jod.id_jo=jo.id left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id left join (select no_bppb,id_jo,id_item,SUM(qty_out) qty_barcode from whs_bppb_det where no_bppb like '%RO%' and id_roll is not null GROUP BY no_bppb) c on c.no_bppb = a.no_bppb where a.no_bppb like '%RO%' and tgl_bppb BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." ".$where2." GROUP BY a.no_bppb");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '!=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $jns_klr = DB::connection('mysql_sb')->select("
            select nama_trans isi,nama_trans tampil from mastertransaksi where jenis_trans='OUT' and jns_gudang = 'FACC' order by id");

        return view("returmaterial.retur-material", ['jns_klr' => $jns_klr,'status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
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
        $def_type = DB::connection('mysql_sb')->table('master_defect')->select('nama_defect')->where('mattype', '=', 'FABRIC')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT(CURRENT_DATE(), '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/RO/',DATE_FORMAT(CURRENT_DATE(), '%m'),DATE_FORMAT(CURRENT_DATE(), '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) kode FROM bppb WHERE MONTH(bppbdate) = MONTH(CURRENT_DATE()) AND YEAR(bppbdate) = YEAR(CURRENT_DATE()) AND LEFT(bppbno_int,2) = 'GK'");

        $jns_klr = DB::connection('mysql_sb')->select("
            select nama_trans isi,nama_trans tampil from mastertransaksi where jenis_trans='OUT' and jns_gudang = 'FACC' order by id");

        $no_req = DB::connection('mysql_sb')->select("
            select a.bppbno isi,concat(a.bppbno,'|',ac.kpno,'|',ac.styleno,'|',mb.supplier) tampil from bppb_req a inner join jo_det s on a.id_jo=s.id_jo inner join so on s.id_so=so.id inner join act_costing ac on so.id_cost=ac.id inner join mastersupplier mb on ac.id_buyer=mb.id_supplier and a.cancel='N' and bppbdate >= '2023-01-01' where bppbno like 'RQ-F%' group by bppbno order by bppbdate desc");

        $status_replac = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'status_replacement')->get();

        return view('returmaterial.create-returmaterial', ['no_req' => $no_req,'kode_gr' => $kode_gr,'jns_klr' => $jns_klr,'def_type' => $def_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit, 'status_replac' => $status_replac, 'page' => 'dashboard-warehouse']);
    }

    public function getNobpb(Request $request)
    {
        $nomorbpb = DB::connection('mysql_sb')->select("select *,(a.qty - COALESCE(b.qty_out,0)) qty_sisa from (select bpbno_int isi,concat(if(bpbno_int!='',bpbno_int,bpbno),'|',supplier) tampil,sum(qty) qty from 
            bpb a inner join mastersupplier s on a.id_supplier=s.id_supplier 
            inner join masteritem mi on a.id_item=mi.id_item 
            where a.bpbno_int like '%GK%' and bpbdate = '" . $request->tgl_bpb . "' group by bpbno order by bpbno) a left join (select b.no_bpb,sum(COALESCE(qty_out,0)) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where no_bpb != '' and a.status = 'Y' GROUP BY b.no_bpb) b on b.no_bpb = a.isi where (a.qty - COALESCE(b.qty_out,0)) > 0 ");

        $html = "<option value=''>Pilih No BPB</option>";

        foreach ($nomorbpb as $nobpb) {
            $html .= " <option value='" . $nobpb->isi . "'>" . $nobpb->tampil . "</option> ";
        }

        return $html;
    }

    public function getDetailBpb(Request $request)
    {
            $user = Auth::user()->name;
            $data_detail = DB::connection('mysql_sb')->select("select a.*,COALESCE(qty_input,0) qty_input, (COALESCE(qty,0) - COALESCE(qty_input,0) - COALESCE(qty_out,0)) qty_stok from (select a.id id_bpb,a.bpbno,a.bpbno_int,a.id_jo,a.id_item,s.mattype,s.goods_code,concat(s.itemdesc,' ',s.color,' ',s.size,' ',s.add_info) itemdesc,s.color,a.qty,a.unit,a.id_po_item,jd.styleno,jd.kpno no_ws,jo_no from bpb a 
inner join masteritem s on a.id_item=s.id_item 
inner join (select jo_no,ac.id_buyer, supplier buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd
         inner join jo on jd.id_jo = jo.id
                 inner join so on jd.id_so = so.id
         inner join act_costing ac on so.id_cost = ac.id
                 inner join mastersupplier mb on ac.id_buyer = mb.id_supplier
         where jd.cancel = 'N'
         group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo 
where bpbno_int = '" . $request->no_bpb . "' and bpbno_int != '' order by s.mattype desc) a
left join (select id_item iditem,id_jo idjo,sum(qty_out) qty_input from whs_bppb_det_temp where created_by = '".$user."' GROUP BY id_item,id_jo) b on b.iditem = a.id_item and b.idjo = a.id_jo
left join (select id_item iditem,id_jo idjo,sum(qty_out) qty_out from (select 'OUT' id,no_dok,a.id_item,a.id_jo,sum(a.qty_out) qty_out from whs_bppb_det a inner join whs_lokasi_inmaterial b on b.no_barcode = a.id_roll where no_dok = '" . $request->no_bpb . "' and a.status = 'Y' GROUP BY no_dok,a.id_item,a.id_jo
UNION
select 'RO' id,b.no_bpb,id_item iditem,id_jo idjo,sum(qty_out) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where no_bpb = '" . $request->no_bpb . "' and a.status = 'Y' GROUP BY id_item,id_jo) a GROUP BY id_item,id_jo) c on c.iditem = a.id_item and c.idjo = a.id_jo");
        

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }


    public function showdetailitemro(Request $request)
    {
        $det_item = DB::connection('mysql_sb')->select("select * from (select id_roll,id_item,id_jo,kode_rak,itemdesc,raknya,lot_no,roll_no,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,unit from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.id_jo='" . $request->id_jo . "' and a.id_item='" . $request->id_item . "' and a.qty_sisa > 0
                UNION
                select id, id_item, id_jo, kode_lok, item_desc, raknya, no_lot,no_roll, qty_aktual,satuan from (select a.nobarcode id, a.id_item, a.id_jo, a.kode_lok, a.item_desc, a.kode_lok raknya, a.no_lot,a.no_roll, a.qty_aktual,a.satuan,COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo='" . $request->id_jo . "' and a.id_item='" . $request->id_item . "') a where a.qty_sisa > 0");

        // $det_item = DB::connection('mysql_sb')->select("select id_roll,id_item,id_jo,kode_rak,itemdesc,raknya,lot_no, roll_no, qty_sisa, unit from (select br.id id_roll,br.id_h,brh.id_item,brh.id_jo,roll_no,lot_no,roll_qty,roll_qty_used,roll_qty - roll_qty_used qty_sisa,roll_foc,br.unit, concat(kode_rak,' ',nama_rak) raknya,kode_rak,br.barcode, mi.itemdesc from bpb_roll br inner join 
        //         bpb_roll_h brh on br.id_h=brh.id 
        //         inner join masteritem mi on brh.id_item = mi.id_item
        //         inner join master_rak mr on br.id_rak_loc=mr.id where 
        //         brh.id_jo='" . $request->id_jo . "' and brh.id_item='" . $request->id_item . "' and br.id_rak_loc!='' 
        //         order by br.id) a where qty_sisa > 0
        //         UNION
        //         select id, id_item, id_jo, kode_lok, item_desc, raknya, no_lot,no_roll, qty_aktual,satuan from (select a.id, a.id_item, a.id_jo, a.kode_lok, a.item_desc, a.kode_lok raknya, a.no_lot,a.no_roll, a.qty_aktual,a.satuan,COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo='" . $request->id_jo . "' and a.id_item='" . $request->id_item . "') a where a.qty_sisa > 0");

        $html = '<div class="table-responsive" style="max-height: 300px">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 3%;">Check</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 20%;">Lokasi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 13%;">No Lot</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">No Roll</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 13%;">Stok</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 13%;">Satuan</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 13%;">Qty Out</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 13%;">Qty Sisa</th>
                        <th hidden></th>
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
                        <td ><input type="checkbox" id="pil_item'.$x.'" name="pil_item['.$x.']" class="flat" value="1" onchange="enableinput()"></td>
                        <td >'.$detitem->raknya.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="rak'.$x.'" name="rak['.$x.']" value="'.$detitem->kode_rak.'" / readonly></td>
                        <td >'.$detitem->lot_no.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="no_lot'.$x.'" name="no_lot['.$x.']" value="'.$detitem->lot_no.'" / readonly></td>
                        <td >'.$detitem->roll_no.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="no_roll'.$x.'" name="no_roll['.$x.']" value="'.$detitem->roll_no.'" / readonly></td>
                        <td class="text-right">'.$detitem->qty_sisa.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="qty_stok'.$x.'" name="qty_stok['.$x.']" value="'.$detitem->qty_sisa.'" / readonly></td>
                       <td >'.$detitem->unit.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="unit'.$x.'" name="unit['.$x.']" value="'.$detitem->unit.'" / readonly></td>
                        <td><input style="width:90px;text-align:right;" class="form-control" type="text" id="qty_out'.$x.'" name="qty_out['.$x.']" value="" onkeyup="sum_qty_item(this.value)" / disabled></td>
                        <td ><input style="width:80px;text-align:right;" class="form-control" type="text" id="qty_sisa'.$x.'" name="qty_sisa['.$x.']" value="" / disabled></td>
                        <td hidden> <input type="hidden" id="id_roll'.$x.'" name="id_roll['.$x.']" value="'.$detitem->id_roll.'" / readonly></td>
                        <td hidden> <input type="hidden" id="id_item'.$x.'" name="id_item['.$x.']" value="'.$detitem->id_item.'" / readonly></td>
                        <td hidden> <input type="hidden" id="id_jo'.$x.'" name="id_jo['.$x.']" value="'.$detitem->id_jo.'" / readonly></td>
                        <td hidden> <input type="hidden" id="itemdesc'.$x.'" name="itemdesc['.$x.']" value="'.$detitem->itemdesc.'" / readonly></td>
                       </tr>';
                       $x++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }

    public function getListbarcodero(Request $request)
    {
        $listbarcode = DB::connection('mysql_sb')->select("select id isi,tampil,tampil2 from (select a.id, concat(a.id,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.id,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo='" . $request->id_jo . "' and a.id_item='" . $request->id_item . "') a where a.qty_sisa > 0");

        $html = "";

        foreach ($listbarcode as $barcode) {
            $html .= " <option value='" . $barcode->isi . "'>" . $barcode->isi . "</option> ";
        }

        return $html;
    }

    public function showdetailbarcodeRo(Request $request)
    {
        // dd(str_replace(",","','",$request->id_barcode));
        // dd($request->id_barcode);
        $det_item = DB::connection('mysql_sb')->select("select no_barcode id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,(qty_aktual - COALESCE(c.qty_out,0)) sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = whs_lokasi_inmaterial.no_barcode where no_barcode in (" . $request->id_barcode . ")
            UNION
            select * from (select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,unit,kode_rak,no_ws from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc, b.goods_code, a.no_ws,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.id_roll in (" . $request->id_barcode . ") and a.qty_sisa > 0");

//         $det_item = DB::connection('mysql_sb')->select("select id id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,qty_aktual sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial where id in (" . $request->id_barcode . ")
//             UNION
// select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc, qty_sisa, unit,kode_rak,'' ws from (select br.id id_roll,br.id_h,brh.id_item,brh.id_jo,roll_no,lot_no,roll_qty,roll_qty_used,roll_qty - roll_qty_used qty_sisa,roll_foc,br.unit, concat(kode_rak,' ',nama_rak) raknya,kode_rak,br.barcode, mi.itemdesc,mi.goods_code from bpb_roll br inner join 
//                 bpb_roll_h brh on br.id_h=brh.id 
//                 inner join masteritem mi on brh.id_item = mi.id_item
//                 inner join master_rak mr on br.id_rak_loc=mr.id where br.id in (" . $request->id_barcode . ") and br.id_rak_loc!='' 
//                 order by br.id) a where qty_sisa > 0");

        $sum_item = DB::connection('mysql_sb')->select("select count(id_roll) ttl_roll from (select no_barcode id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,qty_aktual sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial where no_barcode in (" . $request->id_barcode . ")
        UNION
        select * from (select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,unit,kode_rak,no_ws from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc, b.goods_code, a.no_ws,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.id_roll in (" . $request->id_barcode . ") and a.qty_sisa > 0) a");

//     $sum_item = DB::connection('mysql_sb')->select("select count(id_roll) ttl_roll from (select id id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,qty_aktual sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial where id in (" . $request->id_barcode . ")
// UNION
// select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc, qty_sisa, unit,kode_rak,'' ws from (select br.id id_roll,br.id_h,brh.id_item,brh.id_jo,roll_no,lot_no,roll_qty,roll_qty_used,roll_qty - roll_qty_used qty_sisa,roll_foc,br.unit, concat(kode_rak,' ',nama_rak) raknya,kode_rak,br.barcode, mi.itemdesc,mi.goods_code from bpb_roll br inner join 
//             bpb_roll_h brh on br.id_h=brh.id 
//             inner join masteritem mi on brh.id_item = mi.id_item
//             inner join master_rak mr on br.id_rak_loc=mr.id where br.id IN (" . $request->id_barcode . ") and br.id_rak_loc!='' 
//             order by br.id) a where qty_sisa > 0) a");
        foreach ($sum_item as $sumitem) {
        $html = '<input style="width:100%;align:center;" class="form-control" type="hidden" id="tot_roll" name="tot_roll" value="'.$sumitem->ttl_roll.'" / readonly>';
        }

        $html .= '<div class="table-responsive" style="max-height: 300px">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Lokasi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">No Roll</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">No Lot</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 14%;">Nama Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">Stok</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">Satuan</th>
                        <th hidden>Qty Out</th>
                        <th hidden>Qty Sisa</th>
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
                        <td> '.$detitem->kode_rak.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="rak'.$x.'" name="rak['.$x.']" value="'.$detitem->kode_rak.'" / readonly></td>
                        <td> '.$detitem->roll_no.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="no_roll'.$x.'" name="no_roll['.$x.']" value="'.$detitem->roll_no.'" / readonly></td>
                        <td> '.$detitem->lot_no.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="no_lot'.$x.'" name="no_lot['.$x.']" value="'.$detitem->lot_no.'" / readonly></td>
                        <td> '.$detitem->id_item.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="id_item'.$x.'" name="id_item['.$x.']" value="'.$detitem->id_item.'" / readonly></td>
                        <td> '.$detitem->itemdesc.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="itemdesc'.$x.'" name="itemdesc['.$x.']" value="'.$detitem->itemdesc.'" / readonly></td>
                        <td> '.$detitem->sisa.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="qty_stok'.$x.'" name="qty_stok['.$x.']" value="'.$detitem->sisa.'" / readonly></td>
                        <td> '.$detitem->unit.' <input style="width:100%;align:center;" class="form-control" type="hidden" id="unit'.$x.'" name="unit['.$x.']" value="'.$detitem->unit.'" / readonly></td>
                        <td hidden><input style="width:100px;text-align:right;" class="form-control" type="hidden" id="qty_out'.$x.'" name="qty_out['.$x.']" value="'.$detitem->sisa.'" onkeyup="sum_qty_barcode(this.value)" /></td>
                        <td hidden><input style="width:100px;text-align:right;" class="form-control" type="hidden" id="qty_sisa'.$x.'" name="qty_sisa['.$x.']" value="0" /></td>
                        <td style="display:none"><input style="width:100%;align:center;" class="form-control" type="text" id="qty_stok'.$x.'" name="qty_stok['.$x.']" value="'.$detitem->sisa.'" / readonly></td>
                        <td hidden> <input type="hidden" id="id_roll'.$x.'" name="id_roll['.$x.']" value="'.$detitem->id_roll.'" / readonly></td>
                        <td hidden> <input type="hidden" id="id_item'.$x.'" name="id_item['.$x.']" value="'.$detitem->id_item.'" / readonly></td>
                        <td hidden> <input type="hidden" id="id_jo'.$x.'" name="id_jo['.$x.']" value="'.$detitem->id_jo.'" / readonly></td>
                       </tr>';
                       $x++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }


    public function saveoutscanRo(Request $request)
    {
            $tglbppb = $request['m_tgl_bppb2'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/RO/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) bppbno_int FROM bppb WHERE MONTH(bppbdate) = MONTH('" . $tglbppb . "') AND YEAR(bppbdate) = YEAR('" . $tglbppb . "') AND LEFT(bppbno_int,2) = 'GK'");

        $bppbno_int = $Mattype1[0]->bppbno_int;
        if (intval($request['m_qty_bal_h2']) >= 0) {
            $timestamp = Carbon::now();
            $no_bppb = $request['m_no_bppb2'];
            $bppb_temp_det = [];
            $data_aktual = 0;
            for ($i = 1; $i <= $request['tot_roll']; $i++) {
            if ($request["qty_out"][$i] > 0) {
                // dd(intval($request["qty_ak"][$i]));
                array_push($bppb_temp_det, [
                    "no_bppb" => $bppbno_int ,
                    "id_roll" => $request["id_roll"][$i],
                    "id_jo" => $request["id_jo"][$i],
                    "id_item" => $request["id_item"][$i],
                    "no_rak" => $request["rak"][$i],
                    "no_lot" => $request["no_lot"][$i],
                    "no_roll" => $request["no_roll"][$i],
                    "item_desc" => $request["itemdesc"][$i],
                    "qty_stok" => $request["qty_stok"][$i],
                    "satuan" => $request["unit"][$i],
                    "qty_out" => $request["qty_out"][$i],
                    "status" => 'Y',
                    "created_by" => Auth::user()->name,
                    "deskripsi" => 'scan',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $BppbdetStore = BppbDetTemp::insert($bppb_temp_det);


            $massage = 'Add data Succesfully';
            $stat = 200;
        }elseif(intval($request['t_roll']) <= 0){
            $massage = ' Please Input Data';
            $stat = 400;
        }elseif(intval($request['m_qty_bal_h']) >= 0){
            $massage = ' Qty Out Melebihi Qty Request';
            $stat = 400;
        }else{
            $massage = ' Data Error';
            $stat = 400;
        }
        // dd($iddok);

            return array(
                "status" => $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => ''
            );

    }



    public function saveoutmanualRo(Request $request)
    {
        $tglbppb = $request['m_tgl_bppb'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/RO/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) bppbno_int FROM bppb WHERE MONTH(bppbdate) = MONTH('" . $tglbppb . "') AND YEAR(bppbdate) = YEAR('" . $tglbppb . "') AND LEFT(bppbno_int,2) = 'GK'");

        $bppbno_int = $Mattype1[0]->bppbno_int;

        if (intval($request['t_roll']) > 0 && intval($request['m_qty_bal_h']) >= 0) {
            $timestamp = Carbon::now();
            $no_bppb = $request['m_no_bppb'];
            $bppb_temp_det = [];
            $data_aktual = 0;
            for ($i = 1; $i <= intval($request['t_roll']); $i++) {
            if ($request["qty_out"][$i] > 0) {
                // dd(intval($request["qty_ak"][$i]));
                array_push($bppb_temp_det, [
                    "no_bppb" => $bppbno_int,
                    "id_roll" => $request["id_roll"][$i],
                    "id_jo" => $request["id_jo"][$i],
                    "id_item" => $request["id_item"][$i],
                    "no_rak" => $request["rak"][$i],
                    "no_lot" => $request["no_lot"][$i],
                    "no_roll" => $request["no_roll"][$i],
                    "item_desc" => $request["itemdesc"][$i],
                    "qty_stok" => $request["qty_stok"][$i],
                    "satuan" => $request["unit"][$i],
                    "qty_out" => $request["qty_out"][$i],
                    "status" => 'Y',
                    "created_by" => Auth::user()->name,
                    "deskripsi" => 'manual',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $BppbdetStore = BppbDetTemp::insert($bppb_temp_det);


            $massage = 'Add data Succesfully';
            $stat = 200;
        }elseif(intval($request['t_roll']) <= 0){
            $massage = ' Please Input Data';
            $stat = 400;
        }elseif(intval($request['m_qty_bal_h']) >= 0){
            $massage = ' Qty Out Melebihi Qty Request';
            $stat = 400;
        }else{
            $massage = ' Data Error';
            $stat = 400;
        }
        // dd($iddok);

            return array(
                "status" => $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => ''
            );

    }

    public function saveroscan(Request $request)
    {
            $tglbppb = $request['m_tgl_bppb2'];
            $timestamp = Carbon::now();
            $no_bppb = $request['m_no_bppb2'];
            $bppb_det = [];
            $data_aktual = 0;
            for ($i = 1; $i <= $request['tot_roll']; $i++) {
            if ($request["qty_out"][$i] > 0) {
                // dd(intval($request["qty_ak"][$i]));
                array_push($bppb_det, [
                    "no_bppb" => $no_bppb ,
                    "id_roll" => $request["id_roll"][$i],
                    "id_jo" => $request["id_jo"][$i],
                    "id_item" => $request["id_item"][$i],
                    "no_rak" => $request["rak"][$i],
                    "no_lot" => $request["no_lot"][$i],
                    "no_roll" => $request["no_roll"][$i],
                    "item_desc" => $request["itemdesc"][$i],
                    "qty_stok" => $request["qty_stok"][$i],
                    "satuan" => $request["unit"][$i],
                    "qty_out" => $request["qty_out"][$i],
                    "status" => 'Y',
                    "created_by" => Auth::user()->name,
                    "deskripsi" => 'scan',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $BppbdetStore = BppbDet::insert($bppb_det);


            $massage = 'Add data Succesfully';
            $stat = 200;
        // }elseif(intval($request['t_roll2']) <= 0){
        //     $massage = ' Please Input Data';
        //     $stat = 400;
        // }elseif(intval($request['m_qty_bal_h2']) >= 0){
        //     $massage = ' Qty Out Melebihi Qty Request';
        //     $stat = 400;
        // }else{
        //     $massage = ' Data Error';
        //     $stat = 400;
        // }
        // dd($iddok);

            return array(
                "status" => $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => ''
            );

    }

    public function getSuppro(Request $request)
    {
        
        $supplier = DB::connection('mysql_sb')->select("select a.id_supplier,a.kpno,a.pono,s.supplier from bpb a inner join mastersupplier s on a.id_supplier=s.id_supplier where bpbno_int ='" . $request->no_bpb . "'");

        return $supplier;
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

        $tglbppb = $request['txt_tgl_ro'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/RO/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) bppbno_int FROM bppb WHERE MONTH(bppbdate) = MONTH('" . $tglbppb . "') AND YEAR(bppbdate) = YEAR('" . $tglbppb . "') AND LEFT(bppbno_int,2) = 'GK'");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
        $m_type = $Mattype1[0]->Mattype;
        $no_type = $Mattype1[0]->nomor;
        $bppbno_int = $Mattype1[0]->bppbno_int;

        $cek_mattype = DB::connection('mysql_sb')->select("select * from tempbpb where Mattype = '" . $m_type . "'");
        $hasilcek = $cek_mattype ? $cek_mattype[0]->Mattype : 0;

        $Mattype2 = DB::connection('mysql_sb')->select("select 'RO.F' Mattype, IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0)) nomor, CONCAT('SJ-F', IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0)),'-R') bpbno FROM bppb WHERE LEFT(bppbno_int,5) = 'GK/RO'");
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

            $no_ro = $request['txt_noro'];
            $tgl_ro = $request['txt_tgl_ro'];
            $tgl_bpb = $request['txt_tgl_bpb'];
            $no_bpb = $request['txt_nobpb'];
            $jenis_def = $request['txt_jns_def'];
            $no_po = $request['txt_nopo'];
            $nama_supp = $request['txt_dikirim'];
            $id_supp = $request['txt_idsupp'];
            $tipe_bc = $request['txt_type_bc'];
            $tujuan = $request['txt_tujuan'];
            $no_aju = $request['txt_no_aju'];
            $tgl_aju = $request['txt_tgl_aju'];
            $no_reg = $request['txt_no_daftar'];
            $tgl_reg = $request['txt_tgl_daftar'];
            $catatan = $request['txt_notes'];
            $txt_nows = $request['txt_nows'];
            $stat_rtn = $request['txt_stat_rtn'];

    for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
        if ( $request["input_qty"][$i] > 0) {
            $detdata = DB::connection('mysql_sb')->select("select * from bpb where id ='" . $request["id_bpb"][$i] . "' ");
             // dd($request["id_bpb"][$i]);       
            $txtid_item_fg = $detdata ? $detdata[0]->id_item_fg : '';
            $txtunit = $detdata ? $detdata[0]->unit : '';
            $txtcurr = $detdata ? $detdata[0]->curr : '';
            $txtprice = $detdata ? $detdata[0]->price : 0;
            $txtid_supplier = $detdata ? $detdata[0]->id_supplier : '';
            $txtbpbno = $detdata ? $detdata[0]->bpbno : '';

        $bppb_headerSB = BppbSB::create([
                'id_item' => $request["id_item"][$i],
                'id_item_fg' => $txtid_item_fg,
                'qty' => $request["input_qty"][$i],
                'unit' => $txtunit,
                'curr' => $txtcurr,
                'price' => $txtprice,
                'remark' => $request["txt_notes"],
                'berat_bersih' => '0',
                'berat_kotor' => '0',
                'nomor_mobil' => '',
                'id_supplier' => $txtid_supplier,
                'invno' => '',
                'bcno' => $no_reg,
                'bcdate' => $tgl_reg,
                'bppbno' => $bpbno,
                'bppbno_int' => $bppbno_int,
                'bppbdate' => $tgl_ro,
                'jenis_dok' => $tipe_bc,
                'username' =>  Auth::user()->name,
                'use_kite' => '1',
                'nomor_aju' => $no_aju,
                'tanggal_aju' => $tgl_aju,
                'kpno' => $request["no_ws"][$i],
                'nomor_rak' => '',
                'status_retur' => 'Y',
                'bpbno_ro' => $txtbpbno,
                'id_jo' => $request["id_jo"][$i],
                'status_return' => $stat_rtn,
            ]);

        $whsdetdata = DB::connection('mysql_sb')->select("select itemdesc from masteritem where id_item ='" . $request["id_item"][$i] . "' GROUP BY id_item ");
        $whsdet = $whsdetdata[0]->itemdesc;


        $Bppb_Det = BppbRO::create([
                'no_bppb' => $bppbno_int,
                'id_jo' => $request["id_jo"][$i],
                'id_item' => $request["id_item"][$i],
                'item_desc' => $whsdet,
                'qty_stok' => $request["input_qty"][$i],
                'satuan' => $txtunit,
                'qty_out' => $request["input_qty"][$i],
                'curr' => $txtcurr,
                'price' => $txtprice,
                'status' => 'Y',
                'created_by' => Auth::user()->name,
                'deskripsi' => $request["txt_notes"],
            ]);
        
    }
    }

        $bppb_header = BppbHeader::create([
                'no_bppb' => $bppbno_int,
                'tgl_bppb' => $tgl_ro,
                'no_bpb' => $no_bpb,
                'jns_defect' => $jenis_def,
                'tujuan' => $nama_supp,
                'dok_bc' => $tipe_bc,
                'no_ws' => $txt_nows,
                'no_ws_aktual' => $txt_nows,
                'no_aju' => $no_aju,
                'tgl_aju' => $tgl_aju,
                'no_daftar' => $no_reg,
                'tgl_daftar' => $tgl_reg,
                'catatan' => $catatan,
                'status' => 'Pending',
                'created_by' => Auth::user()->name,
                'jns_pemasukan' => $tujuan,
                'status_return' => $stat_rtn,
            ]);


        // $bppb_detail = DB::connection('mysql_sb')->insert("insert into whs_bppb_det select id,'".$bppbno_int."' no_bppb, id_roll,id_jo,id_item, no_rak, no_lot,no_roll,item_desc,qty_stok,satuan,qty_out,'','0',status,created_by,deskripsi,created_at,updated_at from whs_bppb_det_temp where created_by = '".Auth::user()->name."'");
        $update_roll = DB::connection('mysql_sb')->insert("update whs_lokasi_inmaterial a INNER JOIN whs_bppb_det_temp b ON b.id_roll = a.id SET a.qty_out = b.qty_out where b.created_by = '".Auth::user()->name."'");
        $bppb_temp = BppbDetTemp::where('created_by',Auth::user()->name)->delete();

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
                "redirect" => url('/retur-material')
            );

    }


    public function getTujuanRo(Request $request)
    {
        $tujuan = DB::connection('mysql_sb')->select("select nama_pilihan isi,nama_pilihan tampil 
            from masterpilihan where kode_pilihan = '" . $request->type_bc . "' ");

        $html = "<option value=''>Pilih Tujuan</option>";

        foreach ($tujuan as $tuj) {
            $html .= " <option value='" . $tuj->isi . "'>" . $tuj->tampil . "</option> ";
        }

        return $html;
    }

    public function approveReturMaterial(Request $request)
    {
            $timestamp = Carbon::now();
            $updateBppbnew = BppbHeader::where('no_bppb', $request['txt_nodok'])->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->name,
                'approved_date' => $timestamp,
            ]);

            $updateBppbSB = BppbSB::where('bppbno_int', $request['txt_nodok'])->update([
                'confirm' => 'Y',
                'confirm_by' => Auth::user()->name,
                'confirm_date' => $timestamp,
            ]);
        
        $massage = 'Approved Data Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/retur-material')
            );
        
    }

    public function barcodeRO($id)
    {

        $d_header = DB::connection('mysql_sb')->select("select a.id,a.no_bppb,a.tgl_bppb,a.tujuan,a.dok_bc,a.no_bpb,COALESCE(a.jns_defect,'-') jns_defect,COALESCE(a.no_po,'-') no_po,b.tgl_dok from whs_bppb_h a left join whs_inmaterial_fabric b on b.no_dok = a.no_bpb where a.id = '".$id."'");

        $det_data = DB::connection('mysql_sb')->select("select *, (round(a.qty_good,4) - round(COALESCE(b.qty_lok,0),4)) qty_sisa  from (select a.id,a.no_bppb no_dok,a.tgl_bppb tgl_dok,kpno no_ws,b.id_jo,b.id_item,mi.goods_code kode_item, mi.matclass produk_item,mi.itemdesc desc_item, b.qty_stok qty_po,b.qty_out qty_good,'0' qty_reject,'0' qty_foc,satuan unit, b.curr, b.price,a.status,a.created_at,a.updated_at from whs_bppb_h a inner join whs_bppb_ro b on b.no_bppb = a.no_bppb INNER JOIN masteritem mi on mi.id_item = b.id_item left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=b.id_jo where a.id = '".$id."') a left join
(select no_bppb nodok,id_jo jo_id,id_item item_id,SUM(qty_out) qty_lok from whs_bppb_det where status = 'Y' and id_roll is not null GROUP BY no_bppb,id_item,id_jo) b on b.nodok = a.no_dok and b.jo_id = a.id_jo and b.item_id = a.id_item");

        // $lokasi = DB::connection('mysql_sb')->select("select a.id,a.kode_lok, CONCAT(a.kode_lok,' (Used ',COALESCE(qty,0),' Of ',kapasitas,')') lokasi,a.kapasitas,COALESCE(qty,0) qty_used from (select id,kode_lok,kapasitas from whs_master_lokasi) a left join (select COUNT(id) qty,kode_lok from (select id,kode_lok from whs_lokasi_inmaterial where status = 'Y') a GROUP BY kode_lok) b on b.kode_lok = a.kode_lok where (a.kapasitas - COALESCE(qty,0)) > 0 ORDER BY kode_lok asc");

        $lokasi = DB::connection('mysql_sb')->select("select a.id,a.kode_lok, CONCAT(a.kode_lok,' (Used ',COALESCE(qty,0),' Of ',kapasitas,')') lokasi,a.kapasitas,COALESCE(qty,0) qty_used from (select id,kode_lok,kapasitas from whs_master_lokasi) a left join (select COUNT(id) qty,kode_lok from (select id,kode_lok from whs_lokasi_inmaterial where status = 'Y') a GROUP BY kode_lok) b on b.kode_lok = a.kode_lok ORDER BY kode_lok asc");

        return view('returmaterial.barcode-ro', ['det_data' => $det_data,'d_header' => $d_header,'lokasi' => $lokasi, 'page' => 'dashboard-warehouse']);
    }

    public function ROListbarcode(Request $request)
    {
        $listbarcode = DB::connection('mysql_sb')->select("select id_roll isi, concat_ws('',id_roll,' - ' ,itemdesc, ' - ', no_ws) tampil,concat(id_roll,' - ', itemdesc) tampil2 from (select id_roll,itemdesc,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,no_ws from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc, b.goods_code, a.no_ws,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(a.qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det where id_jo = '" . $request->id_jo . "' and id_item = '" . $request->id_item . "' GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.id_jo = '" . $request->id_jo . "' and a.id_item = '" . $request->id_item . "' and a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.qty_sisa > 0
  UNION
  select no_barcode,tampil,tampil2 from (select a.no_barcode, concat(a.no_barcode,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.no_barcode,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id and a.no_ws = ac.kpno left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det where id_jo = '" . $request->id_jo . "' and id_item = '" . $request->id_item . "' GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo = '" . $request->id_jo . "' and a.id_item = '" . $request->id_item . "' ) a where a.qty_sisa > 0");

//   $listbarcode = DB::connection('mysql_sb')->select("select id_roll isi, concat_ws('',id_roll,' - ' ,itemdesc, ' - ', no_ws) tampil,concat(id_roll,' - ', itemdesc) tampil2 from (select id_roll,itemdesc,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,no_ws from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc, b.goods_code, a.no_ws,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(a.qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det where id_jo = '" . $request->id_jo . "' and id_item = '" . $request->id_item . "' GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.id_jo = '" . $request->id_jo . "' and a.id_item = '" . $request->id_item . "' and a.no_bpb = '" . $request->no_bpb . "' and a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.qty_sisa > 0
//   UNION
//   select no_barcode,tampil,tampil2 from (select a.no_barcode, concat(a.no_barcode,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.no_barcode,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id and a.no_ws = ac.kpno left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det where id_jo = '" . $request->id_jo . "' and id_item = '" . $request->id_item . "' GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo = '" . $request->id_jo . "' and a.id_item = '" . $request->id_item . "' and a.no_dok = '" . $request->no_bpb . "') a where a.qty_sisa > 0");


        $html = "";

        foreach ($listbarcode as $barcode) {
            $html .= " <option value='" . $barcode->isi . "'>" . $barcode->isi . "</option> ";
        }

        return $html;
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
