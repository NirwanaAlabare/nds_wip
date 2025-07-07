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

class OutMaterialController extends Controller
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

            if ($request->jns_pengeluaran != 'ALL') {
                $where = " and jenis_pengeluaran = '" . $request->jns_pengeluaran . "' ";
            }else{
                $where = "";
            }

            if ($request->bc_type != 'ALL') {
                $where2 = " and dok_bc = '" . $request->bc_type . "' ";
            }else{
                $where2 = "";
            }

            if ($request->buyer != 'ALL') {
                $where3 = " and buyer = '" . $request->buyer . "' ";
            }else{
                $where3 = "";
            }

            if ($request->status != 'ALL') {
                $where4 = " and status = '" . $request->status . "' ";
            }else{
                $where4 = "";
            }


            $data_inmaterial = DB::connection('mysql_sb')->select("select no_bppb,tgl_bppb,no_req,no_jo,buyer,tujuan,dok_bc,jenis_pengeluaran,no_invoice,no_daftar,tgl_daftar,CONCAT(created_by,' (',created_at, ') ') user_create,status,id from whs_bppb_h where no_bppb like '%OUT%' and tgl_bppb BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." ".$where2." ".$where3." ".$where4." order by no_bppb asc");


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

        return view("outmaterial.out-material", ['jns_klr' => $jns_klr,'status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
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
        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT(CURRENT_DATE(), '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/OUT/',DATE_FORMAT(CURRENT_DATE(), '%m'),DATE_FORMAT(CURRENT_DATE(), '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) kode FROM bppb WHERE MONTH(bppbdate) = MONTH(CURRENT_DATE()) AND YEAR(bppbdate) = YEAR(CURRENT_DATE()) AND LEFT(bppbno_int,2) = 'GK'");

        $jns_klr = DB::connection('mysql_sb')->select("
            select nama_trans isi,nama_trans tampil from mastertransaksi where jenis_trans='OUT' and jns_gudang = 'FACC' order by id");

        $no_req = DB::connection('mysql_sb')->select("
            select a.bppbno isi,concat(a.bppbno,'|',ac.kpno,'|',ac.styleno,'|',mb.supplier) tampil from bppb_req a inner join jo_det s on a.id_jo=s.id_jo inner join so on s.id_so=so.id inner join act_costing ac on so.id_cost=ac.id inner join mastersupplier mb on ac.id_buyer=mb.id_supplier and a.cancel='N' and bppbdate >= '2023-01-01' where bppbno like 'RQ-F%' and qty_out < 1 group by bppbno order by bppbdate desc");

       $no_po = DB::connection('mysql_sb')->select("select pono from po_header where podate >= '2024-01-01' and app = 'A'");

        return view('outmaterial.create-outmaterial', ['no_req' => $no_req,'kode_gr' => $kode_gr,'jns_klr' => $jns_klr,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit ,'no_po' => $no_po, 'page' => 'dashboard-warehouse']);
    }

    public function getdetailreq(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("select a.id_jo,a.id_supplier,s.supplier,jo_no,ac.kpno idws_act,b.supplier buyer from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier inner join jo on a.id_jo=jo.id left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id inner join mastersupplier b on ac.id_buyer=b.id_supplier where bppbno='".$request->no_req."' limit 1");

        return $data;
    }

    public function lokmaterial($id)
    {

        $kode_gr = DB::connection('mysql_sb')->select("select * from whs_inmaterial_fabric where id = '$id'");
        $det_data = DB::connection('mysql_sb')->select("select *, (a.qty_good - COALESCE(b.qty_lok,0)) qty_sisa  from (select a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y') a left join
(select no_dok nodok, no_ws ws,id_jo jo_id,id_item item_id,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok,no_ws,id_item,id_jo) b on b.nodok = a.no_dok and b.ws = a.no_ws and b.jo_id = a.id_jo and b.item_id = a.id_item");

         $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->where('Supplier', '!=', $kode_gr[0]->supplier)->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->where('nama_pilihan', '!=', $kode_gr[0]->type_bc)->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('nama_pilihan', '!=', $kode_gr[0]->type_pch)->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('nama_pilihan', '!=', $kode_gr[0]->type_dok)->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $lokasi = DB::connection('mysql_sb')->table('whs_master_lokasi')->select('id', 'kode_lok')->where('status', '=', 'active')->get();

        return view('inmaterial.lokasi-inmaterial', ['det_data' => $det_data,'kode_gr' => $kode_gr,'gr_type' => $gr_type,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,'lokasi' => $lokasi, 'page' => 'dashboard-warehouse']);
    }


    public function editmaterial($id)
    {

        $kode_gr = DB::connection('mysql_sb')->select("select * from whs_inmaterial_fabric where id = '$id'");
        $det_data = DB::connection('mysql_sb')->select("select *, (a.qty_good - COALESCE(b.qty_lok,0)) qty_sisa  from (select a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y') a left join
(select no_dok nodok, no_ws ws,id_jo jo_id,id_item item_id,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok,no_ws,id_item,id_jo) b on b.nodok = a.no_dok and b.ws = a.no_ws and b.jo_id = a.id_jo and b.item_id = a.id_item");

        $jml_det = DB::connection('mysql_sb')->select("select COUNT(no_dok) jml_dok from (select a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y') a");

         $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->where('Supplier', '!=', $kode_gr[0]->supplier)->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->where('nama_pilihan', '!=', $kode_gr[0]->type_bc)->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('nama_pilihan', '!=', $kode_gr[0]->type_pch)->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('nama_pilihan', '!=', $kode_gr[0]->type_dok)->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $lokasi = DB::connection('mysql_sb')->table('whs_master_lokasi')->select('id', 'kode_lok')->where('status', '=', 'active')->get();

        return view('inmaterial.edit-inmaterial', ['det_data' => $det_data,'jml_det' => $jml_det,'kode_gr' => $kode_gr,'gr_type' => $gr_type,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,'lokasi' => $lokasi, 'page' => 'dashboard-warehouse']);
    }

    public function getPOList(Request $request)
    {
        $nomorpo = DB::connection('mysql_sb')->select("
                select pono isi, pono tampil, ms.supplier
  from po_header ph
  inner join po_item pi on ph.id = pi.id_po
  inner join jo_det jd on pi.id_jo = jd.id_jo
  inner join so on jd.id_so = so.id
  inner join act_costing ac on so.id_cost = ac.id
    inner join mastersupplier ms on ms.id_supplier = ph.id_supplier
  where app = 'A' and podate >= '2022-10-01' and jenis = 'M' and ms.Supplier = '" . $request->txt_supp . "' group by ph.id
            ");

        $html = "<option value=''>Pilih PO</option>";

        foreach ($nomorpo as $nopo) {
            $html .= " <option value='" . $nopo->isi . "'>" . $nopo->tampil . "</option> ";
        }

        return $html;
    }


    public function getWSList(Request $request)
    {
        $nomorws = DB::connection('mysql_sb')->select("
                select ac.kpno,ms.supplier from bom_jo_global_item bom INNER JOIN jo_det jd on jd.id_jo = bom.id_jo INNER JOIN so on so.id = jd.id_so INNER JOIN act_costing ac on ac.id = so.id_cost INNER JOIN mastersupplier ms on ms.id_supplier = bom.id_supplier where ms.Supplier = '" . $request->txt_supp . "' GROUP BY ac.kpno
            ");

        $html = "<option value=''>Pilih WS</option>";

        foreach ($nomorws as $ws) {
            $html .= " <option value='" . $ws->kpno . "'>" . $ws->kpno . "</option> ";
        }

        return $html;
    }

    public function getdetaillok(Request $request)
    {
        $kode_lok = $request->lokasi;
        $lokasi = DB::connection('mysql_sb')->table('whs_master_lokasi')->select('id', 'kode_lok')->where('status', '=', 'active')->get();

        $datanomor = DB::connection('mysql_sb')->select("select COUNT(no_roll) noroll from whs_lokasi_inmaterial where no_dok = '".$request->no_dok."' and no_lot = '".$request->lot."' and status = 'Y'");
        $noroll = $datanomor ? $datanomor[0]->noroll : 0;
        if ($noroll == 0) {
            $nomor = 1;
        }else{
            $nomor = $noroll + 1;
        }

        $html = '<div class="table-responsive"style="max-height: 200px">
            <table id="datatable_list" class="table table-head-fixed table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 5%;">No</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 20%;">Lot</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">Qty BPB</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">Qty Aktual</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 30%;">Lokasi</th>
                        <th hidden></th>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">No Roll</th>
                    </tr>
                </thead>
                <tbody>';
        $pilih_lokasi = '';
        foreach ($lokasi as $lok) {
            if ($lok->kode_lok == $kode_lok) {
                $pilih_lokasi .= " <option selected='selected' value='" . $lok->kode_lok . "'>" . $lok->kode_lok . "</option> ";
            }else{
                $pilih_lokasi .= " <option value='" . $lok->kode_lok . "'>" . $lok->kode_lok . "</option> ";
            }
        }
        $y = $nomor;
        for ( $x = 1; $x <= $request->jml_baris; $x++) {
            $html .= ' <tr>
                        <td>' . $y . '</td>
                        <td ><input style="width:100%;align:center;" class="form-control" type="text" id="no_lot'.$x.'" name="no_lot['.$x.']" value="'.$request->lot.'" / readonly></td>
                        <td ><input style="width:100%;text-align:right;" class="form-control" type="text" id="qty_sj'.$x.'" name="qty_sj['.$x.']" value="" onkeyup="sum_qty_sj()" /></td>
                        <td ><input style="width:100%;text-align:right;" class="form-control" type="text" id="qty_ak'.$x.'" name="qty_ak['.$x.']" value="" onkeyup="sum_qty_aktual()"/></td>
                        <td ><select class="form-control select2lok" id="selectlok'.$x.'" name="selectlok['.$x.']" style="width: 100%;">
                                '.$pilih_lokasi.'
                             </select></td>
                        <td style="display:none"><input class="form-control-sm" type="text" id="no_roll'.$x.'" name="no_roll['.$x.']" value="'.$y.'" /></td>
                        <td ><input style="width:100%;text-align:right;" class="form-control" type="text" id="roll_buyer'.$x.'" name="roll_buyer['.$x.']" value="" /></td>
                       </tr>';
                       $y++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }


    public function showdetailitem(Request $request)
    {

        $det_item = DB::connection('mysql_sb')->select("select * from (select id_roll,id_item,id_jo,kode_rak,itemdesc,raknya,lot_no,roll_no,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,unit from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(qty) qty_in,c.qty_out,a.unit from (select * from whs_sa_fabric where id_jo='" . $request->id_jo . "' and id_item='" . $request->id_item . "') a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det where id_jo='" . $request->id_jo . "' and id_item='" . $request->id_item . "' GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.qty_sisa > 0
        UNION
        select id, id_item, id_jo, kode_lok, item_desc, raknya, no_lot,no_roll, qty_sisa,satuan from (select a.no_barcode id, a.id_item, a.id_jo, a.kode_lok, a.item_desc, a.kode_lok raknya, a.no_lot,a.no_roll, sum(a.qty_aktual) qty_aktual,a.satuan,COALESCE(c.qty_out,0) qty_out,(sum(a.qty_aktual) - COALESCE(a.qty_mutasi,0) - COALESCE(c.qty_out,0)) qty_sisa from ( select * from whs_lokasi_inmaterial where id_jo='" . $request->id_jo . "' and id_item='" . $request->id_item . "') a left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det where id_jo='" . $request->id_jo . "' and id_item='" . $request->id_item . "' GROUP BY id_roll) c on c.id_roll = a.no_barcode GROUP BY a.no_barcode) a where a.qty_sisa > 0");

        // $det_item = DB::connection('mysql_sb')->select("select id_roll,id_item,id_jo,kode_rak,itemdesc,raknya,lot_no, roll_no, qty_sisa, unit from (select br.id id_roll,br.id_h,brh.id_item,brh.id_jo,roll_no,lot_no,roll_qty,roll_qty_used,roll_qty - roll_qty_used qty_sisa,roll_foc,br.unit, concat(kode_rak,' ',nama_rak) raknya,kode_rak,br.barcode, mi.itemdesc from bpb_roll br inner join
        //         bpb_roll_h brh on br.id_h=brh.id
        //         inner join masteritem mi on brh.id_item = mi.id_item
        //         inner join master_rak mr on br.id_rak_loc=mr.id where
        //         brh.id_jo='" . $request->id_jo . "' and brh.id_item='" . $request->id_item . "' and br.id_rak_loc!=''
        //         order by br.id) a where qty_sisa > 0
        //         UNION
        //         select id, id_item, id_jo, kode_lok, item_desc, raknya, no_lot,no_roll, qty_aktual,satuan from (select a.id, a.id_item, a.id_jo, a.kode_lok, a.item_desc, a.kode_lok raknya, a.no_lot,a.no_roll, a.qty_aktual,a.satuan,COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo='" . $request->id_jo . "' and a.id_item='" . $request->id_item . "') a where a.qty_sisa > 0");

        $html = '<div class="table-responsive" style="max-height: 300px">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 3%;">Check</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">No Barcode</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Lokasi</th>
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
                        <td >'.$detitem->id_roll.'</td>
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


// select br.id id_roll, brh.id_item, brh.id_jo, roll_no,lot_no,mi.goods_code, mi.itemdesc, roll_qty - roll_qty_used sisa,br.unit, kode_rak, ac.kpno from bpb_roll br
// inner join bpb_roll_h brh on br.id_h = brh.id
// inner join masteritem mi on brh.id_item = mi.id_item
// inner join jo_det jd on brh.id_jo = jd.id_jo
// inner join so on jd.id_so = so.id
// inner join act_costing ac on so.id_cost = ac.id
// inner join master_rak mr on br.id_rak_loc = mr.id
// where br.id in

    public function showdetailbarcode(Request $request)
    {
        // dd(str_replace(",","','",$request->id_barcode));
        // dd($request->id_barcode);
        $det_item = DB::connection('mysql_sb')->select("select no_barcode id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,(qty_aktual - COALESCE(c.qty_out,0)) sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial left join (select id_roll,sum(qty_out) qty_out,no_rak from whs_bppb_det GROUP BY id_roll) c on c.id_roll = whs_lokasi_inmaterial.no_barcode and c.no_rak = whs_lokasi_inmaterial.kode_lok where (qty_aktual - COALESCE(c.qty_out,0)) > 0 and no_barcode in (" . $request->id_barcode . ")
            UNION
            select * from (select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,unit,kode_rak,no_ws from (select a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc, b.goods_code, a.no_ws,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.qty != 0 and qty_mut is null GROUP BY a.no_barcode) a) a where a.id_roll in (" . $request->id_barcode . ") and a.qty_sisa > 0");

//         $det_item = DB::connection('mysql_sb')->select("select id id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,qty_aktual sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial where id in (" . $request->id_barcode . ")
//             UNION
// select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc, qty_sisa, unit,kode_rak,'' ws from (select br.id id_roll,br.id_h,brh.id_item,brh.id_jo,roll_no,lot_no,roll_qty,roll_qty_used,roll_qty - roll_qty_used qty_sisa,roll_foc,br.unit, concat(kode_rak,' ',nama_rak) raknya,kode_rak,br.barcode, mi.itemdesc,mi.goods_code from bpb_roll br inner join
//                 bpb_roll_h brh on br.id_h=brh.id
//                 inner join masteritem mi on brh.id_item = mi.id_item
//                 inner join master_rak mr on br.id_rak_loc=mr.id where br.id in (" . $request->id_barcode . ") and br.id_rak_loc!=''
//                 order by br.id) a where qty_sisa > 0");

        $sum_item = DB::connection('mysql_sb')->select("select count(id_roll) ttl_roll from (select no_barcode id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,(qty_aktual - COALESCE(c.qty_out,0)) sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial left join (select id_roll,sum(qty_out) qty_out,no_rak from whs_bppb_det GROUP BY id_roll) c on c.id_roll = whs_lokasi_inmaterial.no_barcode and c.no_rak = whs_lokasi_inmaterial.kode_lok where (qty_aktual - COALESCE(c.qty_out,0)) > 0 and no_barcode in (" . $request->id_barcode . ")
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
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">No Barcode</th>
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
                        <td> '.$detitem->id_roll.'</td>
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

    // <tr>
    //     <td ><input style="width:100%;align:center;" class="form-control" type="text" id="rak'.$x.'" name="rak['.$x.']" value="'.$detitem->kode_rak.'" / readonly></td>
    //     <td ><input style="width:100%;align:center;" class="form-control" type="text" id="no_roll'.$x.'" name="no_roll['.$x.']" value="'.$detitem->roll_no.'" / readonly></td>
    //     <td ><input style="width:100%;align:center;" class="form-control" type="text" id="no_lot'.$x.'" name="no_lot['.$x.']" value="'.$detitem->lot_no.'" / readonly></td>
    //     <td class="text-right"><input style="width:100%;align:center;" class="form-control" type="text" id="id_item'.$x.'" name="id_item['.$x.']" value="'.$detitem->id_item.'" / readonly></td>
    //     <td class="text-right"><input style="width:100%;align:center;" class="form-control" type="text" id="nama_barang'.$x.'" name="nama_barang['.$x.']" value="'.$detitem->itemdesc.'" / readonly></td>
    //     <td class="text-right"><input style="width:100%;align:center;" class="form-control" type="text" id="qty_stok'.$x.'" name="qty_stok['.$x.']" value="'.$detitem->sisa.'" / readonly></td>
    //     <td ><input style="width:100%;align:center;" class="form-control" type="text" id="unit'.$x.'" name="unit['.$x.']" value="'.$detitem->unit.'" / readonly></td>
    //     <td><input style="width:100%;text-align:right;" class="form-control" type="text" id="qty_out'.$x.'" name="qty_out['.$x.']" value="" onkeyup="sum_qty_item(this.value)" /></td>
    //     <td ><input style="width:100%;text-align:right;" class="form-control" type="text" id="qty_sisa'.$x.'" name="qty_sisa['.$x.']" value="" /></td>
    // </tr>


    public function getDetailList(Request $request)
    {
        $user = Auth::user()->name;
                $data_detail = DB::connection('mysql_sb')->select("select no_req,jo_no ,id_supplier,qtyreq,qty_sdh_out,qty_sisa_out,id_item,goods_code,itemdesc,id_jo,qty_in,qty_out,(qty_in - qty_out) qty_sisa,unit,kpno,styleno,buyer,round((qty_in - qty_out),2) qtyitem_sisa,Coalesce(qty_input,0) qty_input from (select breq.bppbno no_req,jod.jo_no,breq.id_supplier,breq.qty qtyreq,breq.qty_out qty_sdh_out,breq.qty - breq.qty_out qty_sisa_out,mi.id_item,mi.goods_code,
                concat(mi.itemdesc,' ',mi.color,' ',mi.size,' ',mi.add_info) itemdesc,
                tbl_in.id_jo,tbl_in.qty_in,
                ifnull(tbl_out.qty_out,0) qty_out,
                tbl_in.unit,
                ac.kpno,ac.styleno,mbuyer.supplier buyer
                from bppb_req breq
                inner join masteritem mi on mi.id_item=breq.id_item inner join
                (select id_item,id_jo,sum(qty_in) qty_in,unit from ( select 'S' id, id_item,id_jo,sum(qty) qty_in,unit from whs_sa_fabric group by id_item,id_jo
               UNION
               select 'T' id, id_item,id_jo,sum(qty_sj - COALESCE(qty_mutasi,0)) qty_in,satuan from whs_lokasi_inmaterial where qty_mutasi is null group by id_item,id_jo) a group by id_item,id_jo) as tbl_in
                on mi.id_item=tbl_in.id_item and breq.id_jo=tbl_in.id_jo
                left join
                (select id_item,id_jo,sum(qty_out) qty_out from whs_bppb_det
                    group by id_item,id_jo) as tbl_out
                on tbl_in.id_item=tbl_out.id_item and tbl_in.id_jo=tbl_out.id_jo
                INNER JOIN
                (select jo_no,id_so,id_jo from jo_det a inner join jo s on a.id_jo=s.id
                    group by id_jo)  jod on breq.id_jo=jod.id_jo
                inner join
                (select so.id,id_cost,min(sod.deldate_det) mindeldate from so inner join so_det sod on
                so.id=sod.id_so group by so.id) so on jod.id_so=so.id
                inner join act_costing ac on so.id_cost=ac.id
                inner join mastersupplier mbuyer on ac.id_buyer=mbuyer.id_supplier
                where breq.bppbno='".$request->no_req."') a left join (select id_item iditem,sum(qty_out) qty_input from whs_bppb_det_temp where created_by = '".$user."' GROUP BY id_item) b on b.iditem = a.id_item");

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }

    public function getListbarcode(Request $request)
    {
        $listbarcode = DB::connection('mysql_sb')->select("select id_roll isi, concat_ws('',id_roll,' - ' ,itemdesc, ' - ', no_ws) tampil,concat(id_roll,' - ', itemdesc) tampil2 from (select bppbno,id_roll,itemdesc,ROUND(COALESCE(qty_in,0) - COALESCE(qty_out,0),2) qty_sisa,no_ws from (select breq.bppbno,a.no_barcode id_roll,a.id_item,a.id_jo,a.kode_lok kode_rak,b.itemdesc, b.goods_code, a.no_ws,a.kode_lok raknya,no_lot lot_no,no_roll roll_no,sum(a.qty) qty_in,c.qty_out,a.unit from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item inner join (select bppbno,id_item,id_jo from bppb_req where bppbno = '" . $request->noreq . "' ) breq on a.id_item = breq.id_item and a.id_jo = breq.id_jo left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.no_barcode where a.qty != 0 and qty_mut is null and a.id_item = '" . $request->id_item . "' and a.id_jo = '" . $request->id_jo . "' GROUP BY a.no_barcode) a) a where a.qty_sisa > 0
  UNION
  select no_barcode,tampil,tampil2 from (select a.no_barcode, concat(a.no_barcode,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.no_barcode,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(sum(a.qty_aktual) - COALESCE(a.qty_mutasi,0) - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a inner join bppb_req b on b.id_item = a.id_item inner join jo on a.id_jo=jo.id left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id and a.no_ws = ac.kpno left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.no_barcode where b.bppbno = '" . $request->noreq . "' and a.id_item = '" . $request->id_item . "' and a.id_jo = '" . $request->id_jo . "' group by a.no_barcode) a where a.qty_sisa > 0");


//   UNION
//   select id,tampil,tampil2 from (select a.no_barcode, concat(a.no_barcode,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.no_barcode,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a inner join bppb_req b on b.id_item = a.id_item and b.idws_act = a.no_ws left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where b.bppbno = '" . $request->noreq . "') a where a.qty_sisa > 0


  //       $listbarcode = DB::connection('mysql_sb')->select("select br.id isi, concat(br.id,' - ' ,mi.itemdesc, ' - ', ac.kpno) tampil,concat(br.id,' - ', mi.itemdesc) tampil2
  // from bpb_roll br
  // inner join bpb_roll_h brh on br.id_h = brh.id
  // inner join bppb_req breq on brh.id_item = breq.id_item and brh.id_jo = breq.id_jo
  // inner join masteritem mi on brh.id_item = mi.id_item
  // inner join jo_det jd on brh.id_jo = jd.id_jo
  // inner join so on jd.id_so = so.id
  // inner join act_costing ac on so.id_cost = ac.id
  // where (br.roll_qty - br.roll_qty_used) > 0 and breq.bppbno = '" . $request->noreq . "'
  // union
  // select br.id isi, concat(br.id,' - ' ,mi.itemdesc, ' - ', ac.kpno) tampil,concat(br.id,' - ', mi.itemdesc) tampil2
  // from bpb_roll br
  // inner join bpb_roll_h brh on br.id_h = brh.id
  // inner join jo_det jd on brh.id_jo = jd.id_jo
  // inner join so on jd.id_so = so.id
  // inner join act_costing ac on so.id_cost = ac.id
  // inner join bppb_req breq on brh.id_item = breq.id_item and ac.kpno = breq.idws_act
  // inner join masteritem mi on brh.id_item = mi.id_item
  // where (br.roll_qty - br.roll_qty_used) > 0 and breq.bppbno = '" . $request->noreq . "'
  // UNION
  // select id,tampil,tampil2 from (select a.id, concat(a.id,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.id,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a inner join bppb_req b on b.id_item = a.id_item and b.idws_act = a.no_ws left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where b.bppbno = '" . $request->noreq . "') a where a.qty_sisa > 0");

        $html = "";

        foreach ($listbarcode as $barcode) {
            $html .= " <option value='" . $barcode->isi . "'>" . $barcode->isi . "</option> ";
        }

        return $html;
    }


    public function approveOutMaterial(Request $request)
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
                "redirect" => url('/out-material')
            );

    }

    public function deletescantemp(Request $request)
    {

        $deletescan = BppbDetTemp::where('id_jo',$request['id_jo'])->where('id_item',$request['id_item'])->where('created_by',Auth::user()->name)->delete();

    }

    public function deletealltemp(Request $request)
    {

        $deletescan = BppbDetTemp::where('no_bppb',$request['no_bppb'])->where('created_by',Auth::user()->name)->delete();

    }


    public function updatedet(Request $request)
    {

        $id = $request['txt_idgr'];

            $updateInMaterial = InMaterialFabric::where('id', $request['txt_idgr'])->update([
                'tgl_dok' => $request['txt_tgl_gr'],
                'tgl_shipp' => $request['txt_tgl_ship'],
                'type_bc' => $request['txt_type_bc'],
                'type_pch' => $request['txt_type_pch'],
                'ori_dok' => $request['txt_oridok'],
                'no_invoice' => $request['txt_invdok'],
                'deskripsi' => $request['txt_notes'],
            ]);

        for ($i = 1; $i <= intval($request['txt_jmldet']); $i++) {
            if ($request["qty_good"][$i] > 0 || $request["qty_reject"][$i] > 0) {
                $updateInMaterialDet = InMaterialFabricDet::where('id', $request["id_det"][$i])->update([
                'qty_good' => $request["qty_good"][$i],
                'qty_reject' => $request["qty_reject"][$i],
            ]);
            }
            }

        $massage = 'Edit Data Successfully';

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
        $validatedRequest = $request->validate([
            "txt_noreq" => "required",
            "txt_jns_klr" => "required",
            "txt_dok_bc" => "required",
        ]);
        // if (intval($request['jumlah_qty']) > 0) {

        $tglbppb = $request['txt_tgl_bppb'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/OUT/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) bppbno_int FROM bppb WHERE MONTH(bppbdate) = MONTH('" . $tglbppb . "') AND YEAR(bppbdate) = YEAR('" . $tglbppb . "') AND LEFT(bppbno_int,2) = 'GK'");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
        $m_type = $Mattype1[0]->Mattype;
        $no_type = $Mattype1[0]->nomor;
        $bppbno_int = $Mattype1[0]->bppbno_int;

        $cek_mattype = DB::connection('mysql_sb')->select("select * from tempbpb where Mattype = '" . $m_type . "'");
        $hasilcek = $cek_mattype ? $cek_mattype[0]->Mattype : 0;

        $Mattype2 = DB::connection('mysql_sb')->select("select 'O.F' Mattype, IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0)) nomor, CONCAT('SJ-F', IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bppbno,5,5))+1,5,0))) bpbno FROM bppb WHERE LEFT(bppbno_int,6) = 'GK/OUT'");
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
                'bppbno_req' => $request['txt_noreq'],
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
                'bcno' => $request['txt_no_daftar'],
                'bcdate' => $request['txt_tgl_daftar'],
                'jenis_dok' => $request['txt_dok_bc'],
                'id_supplier' => $request['txt_idsupp'],
                'id_jo' => $request['txt_id_jo'],
                'jenis_trans' => $request['txt_jns_klr'],
            ]);
            $jml_qtyout = $request["qty_sdh_out"][$i] + $request["input_qty"][$i];

            $update_BppbReq = BppbReq::where('bppbno', $request['txt_noreq'])->where('id_item', $request["id_item"][$i])->update([
                'qty_out' => $jml_qtyout,
            ]);
        }

        $bppb_header = BppbHeader::create([
            'no_bppb' => $bppbno_int,
            'tgl_bppb' => $request['txt_tgl_bppb'],
            'no_req' => $request['txt_noreq'],
            'jenis_pengeluaran' => $request['txt_jns_klr'],
            'no_jo' => $request['txt_nojo'],
            'tujuan' => $request['txt_dikirim'],
            'dok_bc' => $request['txt_dok_bc'],
            'no_ws' => $request['txt_nows'],
            'no_ws_aktual' => $request['txt_nows_act'],
            'buyer' => $request['txt_buyer'],
            'no_aju' => $request['txt_no_aju'],
            'tgl_aju' => $request['txt_tgl_aju'],
            'no_daftar' => $request['txt_no_daftar'],
            'tgl_daftar' => $request['txt_tgl_daftar'],
            'no_kontrak' => $request['txt_kontrak'],
            'no_invoice' => $request['txt_invoice'],
            'catatan' => $request['txt_notes'],
            'status' => 'Pending',
            'created_by' => Auth::user()->name,
            'no_po_subkon' => $request['txt_po_sub'],
        ]);


            // $bppb_detail = DB::connection('mysql_sb')->insert("insert into whs_bppb_det select '','".$bppbno_int."' no_bppb, id_roll,id_jo,id_item, no_rak, no_lot,no_roll,item_desc,qty_stok,satuan,qty_out,'','0',status,created_by,deskripsi,created_at,updated_at from whs_bppb_det_temp where created_by = '".Auth::user()->name."'");

        // $bppb_detail = DB::connection('mysql_sb')->insert("insert into whs_bppb_det
        //     select a.*, price, nilai_barang, type_bc, no_aju, tgl_aju, no_daftar, tgl_daftar from (select '','".$bppbno_int."' no_bppb, id_roll,id_jo,id_item, no_rak, no_lot,no_roll,item_desc,qty_stok,satuan,qty_out,'' a,'0',status,created_by,deskripsi,created_at,updated_at from whs_bppb_det_temp where created_by = '".Auth::user()->name."') a INNER JOIN (select b.no_dok,a.type_pch, b.no_barcode ,type_bc, no_aju, tgl_aju, no_daftar, tgl_daftar,price, nilai_barang from (select a.*,id_jo, id_item, price, nilai_barang from whs_inmaterial_fabric a INNER JOIN whs_inmaterial_fabric_det c on c.no_dok = a.no_dok) a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok and a.id_jo = b.id_jo and a.id_item = b.id_item where b.status = 'Y' ) b on b.no_barcode = a.id_roll");
        $bppb_detail = DB::connection('mysql_sb')->insert("insert into whs_bppb_det select '',no_bppb, id_roll, id_jo, id_item, no_rak, no_lot, no_roll,item_desc,qty_stok,satuan,qty_out,curr,'0',status,created_by,deskripsi,created_at,updated_at, price, nilai_barang, type_bc, no_aju, tgl_aju, no_daftar, tgl_daftar from (select '','".$bppbno_int."' no_bppb, id_roll,id_jo,id_item, no_rak, no_lot,no_roll,item_desc,qty_stok,satuan,qty_out,'0',status,created_by,deskripsi,created_at,updated_at from whs_bppb_det_temp where created_by = '".Auth::user()->name."') a left JOIN (select a.* from (select b.id,b.no_dok,a.type_pch, b.no_barcode , b.kode_lok,type_bc, no_aju, tgl_aju, no_daftar, tgl_daftar,IF(price is null or price = '',b.nilai_barang,price) price, a.nilai_barang,qty_aktual,curr from (select a.*,id_jo, id_item, price, nilai_barang,curr from whs_inmaterial_fabric a INNER JOIN whs_inmaterial_fabric_det c on c.no_dok = a.no_dok) a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok and a.id_jo = b.id_jo and a.id_item = b.id_item where b.status = 'Y') a left join (select id_roll, no_rak, SUM(qty_out) qty_out from whs_bppb_det where status = 'Y' GROUP BY id_roll) b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok where (qty_aktual - coalesce(qty_out,0)) > 0 ORDER BY no_barcode asc) b on b.no_barcode = a.id_roll");
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
            "redirect" => url('/out-material')
        );

    }

    public function saveoutmanual(Request $request)
    {
        $tglbppb = $request['m_tgl_bppb'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/OUT/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) bppbno_int FROM bppb WHERE MONTH(bppbdate) = MONTH('" . $tglbppb . "') AND YEAR(bppbdate) = YEAR('" . $tglbppb . "') AND LEFT(bppbno_int,2) = 'GK'");

        $bppbno_int = $Mattype1[0]->bppbno_int;

        $qtyOut = collect($request['qty_out']);

        $qtyOutKeys = $qtyOut->keys();

        if (intval($request['t_roll']) > 0 && intval($request['m_qty_bal_h']) >= 0) {
            $timestamp = Carbon::now();
            $no_bppb = $request['m_no_bppb'];
            $bppb_temp_det = [];
            $data_aktual = 0;
            foreach ($qtyOut as $key => $value) {
                if ($request['qty_out'][$key] > 0) {
                // dd(intval($request["qty_ak"][$i]));
                    array_push($bppb_temp_det, [
                        "no_bppb" => $bppbno_int,
                        "id_roll" => $request["id_roll"][$key],
                        "id_jo" => $request["id_jo"][$key],
                        "id_item" => $request["id_item"][$key],
                        "no_rak" => $request["rak"][$key],
                        "no_lot" => $request["no_lot"][$key],
                        "no_roll" => $request["no_roll"][$key],
                        "item_desc" => $request["itemdesc"][$key],
                        "qty_stok" => $request["qty_stok"][$key],
                        "satuan" => $request["unit"][$key],
                        "qty_out" => $request["qty_out"][$key],
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

    public function saveoutscan(Request $request)
    {
        $tglbppb = $request['m_tgl_bppb2'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,IF(MAX(bppbno_int) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0)) nomor,CONCAT('GK/OUT/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',IF(MAX(RIGHT(bppbno_int,5)) IS NULL,'00001',LPAD(MAX(RIGHT(bppbno_int,5))+1,5,0))) bppbno_int FROM bppb WHERE MONTH(bppbdate) = MONTH('" . $tglbppb . "') AND YEAR(bppbdate) = YEAR('" . $tglbppb . "') AND LEFT(bppbno_int,2) = 'GK'");

        $bppbno_int = $Mattype1[0]->bppbno_int;
        // if (intval($request['m_qty_bal_h2']) >= 0) {
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

    public function simpanedit(Request $request)
    {
        // $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
        // $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
        // $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);
        // $totalQty = 0;

        $validatedRequest = $request->validate([
            "txt_id" => "required",
            "txt_area" => "required",
            "txt_inisial" => "required",
            "txt_baris" => "required",
            "txt_level" => "required",
            "txt_num" => "required",
            "txt_capacity" => "required",
        ]);

        $lokCode = $validatedRequest['txt_inisial'] . '.' . $validatedRequest['txt_baris'] . '.' . $validatedRequest['txt_level'] . '.' . $validatedRequest['txt_num'];

        $delete_unit = UnitLokasi::where('kode_lok', $lokCode)
        ->delete();

        if ($request['ROLL_edit'] == 'on') {
           $unitStore1 = UnitLokasi::create([
            'kode_lok' => $lokCode,
            'unit' => 'ROLL',
            'status' => 'Y',
        ]);

       }
       if ($request['BUNDLE_edit'] == 'on') {
           $unitStore2 = UnitLokasi::create([
            'kode_lok' => $lokCode,
            'unit' => 'BUNDLE',
            'status' => 'Y',
        ]);

       }
       if ($request['BOX_edit'] == 'on') {
           $unitStore3 = UnitLokasi::create([
            'kode_lok' => $lokCode,
            'unit' => 'BOX',
            'status' => 'Y',
        ]);

       }
       if ($request['PACK_edit'] == 'on') {
           $unitStore4 = UnitLokasi::create([
            'kode_lok' => $lokCode,
            'unit' => 'PACK',
            'status' => 'Y',
        ]);

       }

       $timestamp = Carbon::now();

       if ($request['ROLL_edit'] == 'on' || $request['BUNDLE_edit'] == 'on' || $request['BOX_edit'] == 'on' || $request['PACK_edit'] == 'on') {
        $updateLokasi = MasterLokasi::where('id', $validatedRequest['txt_id'])->update([
            'kode_lok' => $lokCode,
            'area_lok' => $validatedRequest['txt_area'],
            'inisial_lok' => $validatedRequest['txt_inisial'],
            'baris_lok' => $validatedRequest['txt_baris'],
            'level_lok' => $validatedRequest['txt_level'],
            'no_lok' => $validatedRequest['txt_num'],
            'kapasitas' => $validatedRequest['txt_capacity'],
            'status' => 'Active',
            'create_by' => Auth::user()->name,
            'create_date' => $timestamp,

        ]);

        $massage = 'Location ' . $lokCode . ' Edit Succesfully';

        return array(
            "status" => 200,
            "message" => $massage,
            "additional" => [],
            "redirect" => url('/master-lokasi')
        );
    }

}

public function barcodeinmaterial(Request $request, $id)
{


    $dataItem = DB::connection('mysql_sb')->select("select a.*,CONCAT(a.no_roll,' Of ',all_roll) roll, ac.styleno from (select b.id,item_desc,kode_item,id_jo,id_item,supplier,a.no_dok,no_po,b.no_ws,no_roll,no_roll_buyer,no_lot,ROUND(qty_aktual,2) qty,satuan,'-' grouping,kode_lok from whs_inmaterial_fabric a inner join whs_lokasi_inmaterial b on b.no_dok = a.no_dok where a.id = '$id' and b.status = 'Y') a INNER JOIN
        (select no_dok nodok,no_lot nolot,COUNT(no_roll) all_roll from (select item_desc,kode_item,id_item,supplier,a.no_dok,no_po,b.no_ws,no_roll,no_lot,ROUND(qty_aktual,2) qty,satuan,'-' grouping from whs_inmaterial_fabric a inner join whs_lokasi_inmaterial b on b.no_dok = a.no_dok where a.id = '$id' and b.status = 'Y') a GROUP BY no_lot) b on b.nodok = a.no_dok and a.no_lot = b.nolot
        inner join jo_det jd on a.id_jo = jd.id_jo
        inner join so on jd.id_so = so.id
        inner join act_costing ac on so.id_cost = ac.id order by a.no_lot,a.id asc");

            // decode qr code
            // $qrCodeDecode = base64_encode(Barcode::format('svg')->size(100)->generate($dataLokasi->kode_lok));

            // generate pdf
            // dd($dataItem);
    PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
    $pdf = PDF::loadView('inmaterial.pdf.print-barcode', ["dataItem" => $dataItem])->setPaper('a7', 'landscape');

    $fileName = 'barcode-material.pdf';

    return $pdf->download(str_replace("/", "_", $fileName));

}


public function pdfoutmaterial(Request $request, $id)
{


    $dataHeader = DB::connection('mysql_sb')->select("select * from whs_bppb_h where id = '$id' limit 1");
    $dataDetail = DB::connection('mysql_sb')->select("select a.no_bppb no_dok,b.no_ws,a.item_desc,ROUND(sum(a.qty_out),2) qty ,a.satuan unit,b.catatan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.id = '$id' and a.status = 'Y' group by id_jo,id_item");
    $dataSum = DB::connection('mysql_sb')->select("select sum(qty) qty_all from (select a.no_bppb no_dok,b.no_ws,a.item_desc,ROUND(a.qty_out,2) qty ,a.satuan unit,b.catatan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.id = '$id' and a.status = 'Y') a");
    $dataUser = DB::connection('mysql_sb')->select("select created_by,created_at,approved_by,approved_date from whs_inmaterial_fabric where id = '$id' limit 1");
    $dataHead = DB::connection('mysql_sb')->select("select CONCAT('Bandung, ',DATE_FORMAT(a.tgl_bppb,'%d %b %Y')) tgl_dok,a.tujuan,b.alamat, CURRENT_TIMESTAMP() tgl_cetak from whs_bppb_h a inner join mastersupplier b on b.supplier = a.tujuan where a.id = '$id' limit 1");


    PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
    $pdf = PDF::loadView('outmaterial.pdf.print-pdf', ["dataHeader" => $dataHeader,"dataDetail" => $dataDetail,"dataSum" => $dataSum,"dataUser" => $dataUser,"dataHead" => $dataHead])->setPaper('a4', 'potrait');

    $fileName = 'pdf-material.pdf';

    return $pdf->download(str_replace("/", "_", $fileName));

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
        $dataLokasi = DB::select("
            select  id,
            kode_lok,
            area_lok,
            inisial_lok,
            baris_lok,
            level_lok,
            no_lok,
            unit,
            kapasitas,
            CONCAT(create_by, ' ',create_date) create_user,
            status from whs_master_lokasi where id = '$id'");
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view('master.update-lokasi', ["dataLokasi" => $dataLokasi,'arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
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
