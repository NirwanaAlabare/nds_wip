<?php

namespace App\Http\Controllers;

use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailLap;
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
use App\Models\Marker\MarkerDetail;
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
        DB::connection('mysql_sb')->delete("DELETE FROM whs_bppb_det_temp WHERE created_by = ? ", [Auth::user()->name]);


        return view('outmaterial.create-outmaterial', ['no_req' => $no_req,'kode_gr' => $kode_gr,'jns_klr' => $jns_klr,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit ,'no_po' => $no_po, 'page' => 'dashboard-warehouse']);
    }

    public function editoutmaterial($id)
    {

        // $kode_gr = DB::connection('mysql_sb')->select("select * from whs_inmaterial_fabric where id = '$id'");
        $det_data = DB::connection('mysql_sb')->select("select b.no_bppb, b.kpno, b.styleno, b.id_item, b.id_jo, b.item_desc, COALESCE(stok,0) stok, qty_req, COALESCE(qty_out,0) qty_out, (qty_req - COALESCE(qty_out,0)) qty_sisa_req, b.satuan, b.no_req from (select a.no_bppb, c.kpno, c.styleno, b.id_item, b.id_jo, sum(qty) qty_req, mi.itemdesc item_desc, b.unit satuan, a.no_req from whs_bppb_h a INNER JOIN bppb_req b on b.bppbno = a.no_req left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) c on c.id_jo=b.id_jo INNER JOIN masteritem mi on mi.id_item = b.id_item where a.id = '$id' GROUP BY b.id_item, b.id_jo) b LEFT JOIN (select a.no_bppb, styleno, a.id_item, a.id_jo, a.item_desc, sum(a.qty_out) qty_out, a.satuan,kpno from whs_bppb_det a INNER JOIN whs_bppb_h b on b.no_bppb = a.no_bppb left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) c on c.id_jo=a.id_jo where b.id = '$id' GROUP BY a.id_item, a.id_jo) a on b.id_jo = a.id_jo AND b.id_item = a.id_item LEFT JOIN (select id_jo, id_item, SUM(sal_akhir) stok from data_stock_fabric GROUP BY id_jo, id_item) c on c.id_jo = b.id_jo and c.id_item = b.id_item");

        // $jml_det = DB::connection('mysql_sb')->select("select COUNT(no_dok) jml_dok from (select a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y') a");

        // $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->where('Supplier', '!=', $kode_gr[0]->supplier)->get();
        // $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->where('nama_pilihan', '!=', $kode_gr[0]->type_bc)->get();
        // $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('nama_pilihan', '!=', $kode_gr[0]->type_pch)->where('status', '=', 'Active')->get();
        // $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('nama_pilihan', '!=', $kode_gr[0]->type_dok)->where('status', '=', 'Active')->get();
        // $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        // $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        // $lokasi = DB::connection('mysql_sb')->table('whs_master_lokasi')->select('id', 'kode_lok')->where('status', '=', 'active')->get();

        $data_out = DB::connection('mysql_sb')->table('whs_bppb_h')->where('id', $id)->first();

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '!=', 'C')->get();
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
        DB::connection('mysql_sb')->delete("DELETE FROM whs_bppb_det_temp WHERE created_by = ? ", [Auth::user()->name]);


        return view('outmaterial.edit-outmaterial', ['det_data' => $det_data, 'data_out' => $data_out, 'no_req' => $no_req, 'kode_gr' => $kode_gr,'jns_klr' => $jns_klr,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit ,'no_po' => $no_po, 'page' => 'dashboard-warehouse']);
    }

    public function getdetailreq(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("select a.id_jo,a.id_supplier,s.supplier,jo_no,ac.kpno idws,b.supplier buyer, a.idws_act, a.style_act from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier inner join jo on a.id_jo=jo.id left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id inner join mastersupplier b on ac.id_buyer=b.id_supplier where bppbno='".$request->no_req."' limit 1");

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
        $det_item = DB::connection('mysql_sb')->select("select no_barcode id_roll, a.id_item, id_jo ,no_roll roll_no, no_lot lot_no, b.goods_code, b.itemdesc, sal_akhir sisa, satuan unit, kode_lok kode_rak, kpno from data_stock_fabric a INNER JOIN masteritem b on a.id_item = b.id_item where no_barcode in (" . $request->id_barcode . ") and sal_akhir > 0");

//         $det_item = DB::connection('mysql_sb')->select("select id id_roll,id_item ,id_jo ,no_roll roll_no, no_lot lot_no,kode_item goods_code,item_desc itemdesc,qty_aktual sisa,satuan unit,kode_lok kode_rak,no_ws kpno from whs_lokasi_inmaterial where id in (" . $request->id_barcode . ")
//             UNION
// select id_roll,id_item,id_jo,roll_no,lot_no,goods_code,itemdesc, qty_sisa, unit,kode_rak,'' ws from (select br.id id_roll,br.id_h,brh.id_item,brh.id_jo,roll_no,lot_no,roll_qty,roll_qty_used,roll_qty - roll_qty_used qty_sisa,roll_foc,br.unit, concat(kode_rak,' ',nama_rak) raknya,kode_rak,br.barcode, mi.itemdesc,mi.goods_code from bpb_roll br inner join
//                 bpb_roll_h brh on br.id_h=brh.id
//                 inner join masteritem mi on brh.id_item = mi.id_item
//                 inner join master_rak mr on br.id_rak_loc=mr.id where br.id in (" . $request->id_barcode . ") and br.id_rak_loc!=''
//                 order by br.id) a where qty_sisa > 0");

        $sum_item = DB::connection('mysql_sb')->select("select count(id_roll) ttl_roll from (select no_barcode id_roll, a.id_item, id_jo ,no_roll roll_no, no_lot lot_no, b.goods_code, b.itemdesc, sal_akhir sisa, satuan unit, kode_lok kode_rak, kpno from data_stock_fabric a INNER JOIN masteritem b on a.id_item = b.id_item where no_barcode in (" . $request->id_barcode . ") and sal_akhir > 0) a");

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
        $data_detail = DB::connection('mysql_sb')->select("select styleno, a.id_item, a.id_jo, itemdesc, qtyitem_sisa, qtyreq, qty_sdh_out, (qtyreq - qty_sdh_out) qty_sisa_out, Coalesce(qty_input,0) qty_input, unit from (select a.bppbno, ac.styleno, a.id_item, mi.itemdesc, a.qty qtyreq, COALESCE(a.qty_out,0) qty_sdh_out, a.id_jo,a.unit  from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier inner join jo on a.id_jo=jo.id left join jo_det jod on a.id_jo=jod.id_jo left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id inner join mastersupplier b on ac.id_buyer=b.id_supplier inner join masteritem mi on a.id_item=mi.id_item where bppbno='".$request->no_req."' GROUP BY a.id) a LEFT JOIN
            (select id_jo, id_item, sum(sal_akhir) qtyitem_sisa from data_stock_fabric GROUP BY id_jo, id_item) b on a.id_item = b.id_item and a.id_jo = b.id_jo LEFT JOIN
            (select id_item iditem,sum(qty_out) qty_input from whs_bppb_det_temp where created_by = '".$user."' GROUP BY id_item) c on c.iditem = a.id_item");

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }

    public function getListbarcode(Request $request)
    {
        $listbarcode = DB::connection('mysql_sb')->select("select no_barcode isi, concat_ws(no_barcode,' - ' ,itemdesc, ' - ', kpno) tampil,concat(no_barcode,' - ', itemdesc) tampil2 from data_stock_fabric where id_item = '" . $request->id_item . "' and id_jo = '" . $request->id_jo . "' and sal_akhir > 0");


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

    // Update detail tabel lain
        DB::connection('mysql_sb')->update("
            UPDATE bppb
            SET qty_old = qty, qty = 0, cancel = 'Y'
            WHERE bppbno_int = ?
            ", [$request['txt_nodok']]);

        if ($request->type === 'cancel_with_mr') {
            DB::connection('mysql_sb')->update("
                UPDATE bppb_req
                SET qty_old = qty, qty = 0, cancel = 'Y', qty_out = 0
                WHERE bppbno = (
                SELECT bppbno_req FROM bppb WHERE bppbno_int = ? limit 1
                )
                ", [$request['txt_nodok']]);
        } else{
            DB::connection('mysql_sb')->update("
                UPDATE bppb_req
                SET qty_out = null
                WHERE bppbno = (
                SELECT bppbno_req FROM bppb WHERE bppbno_int = ? limit 1
                )
                ", [$request['txt_nodok']]);
        }

        DB::connection('mysql_sb')->update("
            UPDATE whs_bppb_h
            SET status = 'Cancel'
            WHERE no_bppb = ?
            ", [$request['txt_nodok']]);

        DB::connection('mysql_sb')->update("
            UPDATE whs_bppb_det
            SET qty_stok = qty_out, qty_out = 0, status = 'N'
            WHERE no_bppb = ?
            ", [$request['txt_nodok']]);

        return [
            "status"     => 200,
            "message"    => "Cancel Data Successfully",
            "additional" => [],
            "redirect"   => url('/out-material')
        ];
    }



    // public function approveOutMaterial(Request $request)
    // {
    //         $timestamp = Carbon::now();
    //         $updateBppbnew = BppbHeader::where('no_bppb', $request['txt_nodok'])->update([
    //             'status' => 'Approved',
    //             'approved_by' => Auth::user()->name,
    //             'approved_date' => $timestamp,
    //         ]);

    //         $updateBppbSB = BppbSB::where('bppbno_int', $request['txt_nodok'])->update([
    //             'confirm' => 'Y',
    //             'confirm_by' => Auth::user()->name,
    //             'confirm_date' => $timestamp,
    //         ]);

    //     $massage = 'Approved Data Successfully';

    //         return array(
    //             "status" => 200,
    //             "message" => $massage,
    //             "additional" => [],
    //             "redirect" => url('/out-material')
    //         );

    // }

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
            'style_aktual' => $request['txt_style_act'],
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
        $bppb_detail = DB::connection('mysql_sb')->insert("insert into whs_bppb_det select '',no_bppb, id_roll, id_jo, id_item, no_rak, no_lot, no_roll, no_roll_buyer,item_desc,qty_stok,satuan,qty_out,curr,'0',status,created_by,deskripsi,created_at,updated_at, price, nilai_barang, type_bc, no_aju, tgl_aju, no_daftar, tgl_daftar, np_curr, np_tgl_in, np_price, null, null from (select '','".$bppbno_int."' no_bppb, id_roll,id_jo,id_item, no_rak, no_lot,no_roll,item_desc,qty_stok,satuan,qty_out,'0',status,created_by,deskripsi,created_at,updated_at from whs_bppb_det_temp where created_by = '".Auth::user()->name."') a left JOIN (select a.* from (select b.id,b.no_dok,a.type_pch, b.no_barcode , b.kode_lok,type_bc, no_aju, tgl_aju, no_daftar, tgl_daftar,IF(price is null or price = '',b.nilai_barang,price) price, a.nilai_barang,qty_aktual,curr, IFNULL(np_curr_rev,np_curr) np_curr, np_tgl_in, IFNULL(np_price_rev,np_price) np_price, no_roll_buyer from (select a.*,id_jo, id_item, price, nilai_barang,curr from whs_inmaterial_fabric a INNER JOIN whs_inmaterial_fabric_det c on c.no_dok = a.no_dok) a INNER JOIN (select a.* from whs_lokasi_inmaterial a INNER JOIN data_stock_fabric b on b.no_barcode = a.no_barcode and b.kode_lok = a.kode_lok) b on b.no_dok = a.no_dok and a.id_jo = b.id_jo and a.id_item = b.id_item where b.status = 'Y') a left join (select id_roll, no_rak, SUM(qty_out) qty_out from whs_bppb_det where status = 'Y' GROUP BY id_roll) b on b.id_roll = a.no_barcode and b.no_rak = a.kode_lok where (qty_aktual - coalesce(qty_out,0)) > 0 ORDER BY no_barcode asc) b on b.no_barcode = a.id_roll");
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
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
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
     * @param  \App\Models\Stocker\Stocker  $stocker
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
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }


    public function updateOut(Request $request)
    {

        $id = $request['txt_idbppb'];

        $bppb = DB::connection('mysql_sb')->select("select * from whs_bppb_h where id = '" . $id . "' LIMIT 1");
        $bppbno_int = $bppb ? $bppb[0]->no_bppb : null;

        $updateInMaterial = BppbHeader::where('id', $request['txt_idbppb'])->update([
            'tgl_bppb' => $request['txt_tgl_bppb'],
            'jenis_pengeluaran' => $request['txt_jns_klr'],
            'tujuan' => $request['txt_dikirim'],
            'dok_bc' => $request['txt_dok_bc'],
            'no_kontrak' => $request['txt_kontrak'],
            'no_invoice' => $request['txt_invoice'],
            'no_po_subkon' => $request['txt_po_sub'],
            'catatan' => $request['txt_notes'],
        ]);

        DB::connection('mysql_sb')->table('bppb')
        ->where('bppbno_int', $bppbno_int)
        ->update([
            'bppbdate'    => $request['txt_tgl_bppb'],
            'jenis_trans'    => $request['txt_jns_klr'],
        ]);

        $massage = 'Edit Data Successfully';

        return array(
            "status" => 200,
            "message" => $massage,
            "additional" => [],
            "redirect" => url('/out-material')
        );

    }

    public function showdetailBppb(Request $request)
    {
        $det_bppb = DB::connection('mysql_sb')->select(" select id, id_roll, no_roll, no_lot, satuan, qty_out, no_rak from whs_bppb_det where no_bppb = '".$request->no_bppb."' and id_item = '".$request->id_item."' and id_jo = '".$request->id_jo."'");
    // dd($det_bppb);

        $html = '
        <div class="table-responsive" style="max-height: 250px">
        <table id="tableshow" class="table table-head-fixed table-bordered table-striped table w-100 text-nowrap">
        <thead>
        <tr>
        <th class="text-center" style="font-size: 0.7rem;">No Barcode</th>
        <th class="text-center" style="font-size: 0.7rem;">No Roll</th>
        <th class="text-center" style="font-size: 0.7rem;">No Lot</th>
        <th class="text-center" style="font-size: 0.7rem;">Lokasi</th>
        <th class="text-center" style="font-size: 0.7rem;">Satuan</th>
        <th class="text-center" style="font-size: 0.7rem;">Qty Out</th>
        <th class="text-center" style="font-size: 0.7rem;">id</th>
        </tr>
        </thead>
        <tbody>
        ';

        foreach ($det_bppb as $det) {
            $html .= '
            <tr data-barcode="'.$det->id_roll.'">
            <td class="text-center">'.$det->id_roll.'</td>
            <td class="text-center">'.$det->no_roll.'</td>
            <td class="text-center">'.$det->no_lot.'</td>
            <td class="text-center">'.$det->no_rak.'</td>
            <td class="text-center">'.$det->satuan.'</td>
            <td class="text-left editable" contenteditable="true">'.$det->qty_out.'</td>
            <td class="text-center">'.$det->id.'</td>
            </tr>
            ';
        }

        $html .= '
        </tbody>
        </table>
        </div>';

        return $html;
    }


    public function updateBarcodeBppb(Request $request)
    {
        $rows = $request->data;
    // dd($rows);
        foreach ($rows as $row) {
            $no_bppb = $row['no_bppb'];
            $id_item = $row['id_item'];
            $id_jo = $row['id_jo'];
            $qty_out_h = $row['qty_out_h'];

            DB::connection('mysql_sb')
            ->table('bppb')
            ->where('bppbno_int', $no_bppb)
            ->where('id_item', $id_item)
            ->where('id_jo', $id_jo)
            ->update([
                'qty_old' => DB::raw('qty'),
                'qty' => $qty_out_h,
            ]);

            DB::connection('mysql_sb')
            ->table('whs_bppb_det')
            ->where('id', $row['id_bppbdet'])
            ->update([
                'qty_out' => $row['qty_out'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function DeleteDataBarcodeBppb(Request $request)
    {
        $no_bppb = $request->input('no_bppb');
        $id_jo = $request->input('id_jo');
        $id_item = $request->input('id_item');

        if (!$no_bppb || !$id_item) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap.'], 400);
        }

        try {
            DB::beginTransaction();

            $insertSql = "INSERT INTO whs_bppb_det_cancel
            SELECT * FROM whs_bppb_det
            WHERE no_bppb = ? AND id_jo = ? AND id_item = ?";

            DB::connection('mysql_sb')->statement($insertSql, [$no_bppb, $id_jo, $id_item]);

            DB::connection('mysql_sb')
            ->table('bppb')
            ->where('bppbno_int', $no_bppb)
            ->where('id_item', $id_item)
            ->where('id_jo', $id_jo)
            ->update([
                'qty_old' => DB::raw('qty'),
                'qty' => 0,
            ]);

            $deleted = DB::connection('mysql_sb')
            ->table('whs_bppb_det')
            ->where('no_bppb', $no_bppb)
            ->where('id_jo', $id_jo)
            ->where('id_item', $id_item)
            ->delete();

            DB::commit();

            if ($deleted) {
                return response()->json(['success' => true, 'deleted' => $deleted]);
            } else {
                return response()->json(['success' => false, 'message' => 'Tidak ada data yang dihapus.'], 404);
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DeleteDataBarcode error: '.$e->getMessage(), [
                'no_bppb' => $no_bppb,
                'id_jo' => $id_jo,
                'id_item' => $id_item
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
    }


    public function saveoutscanEdit(Request $request)
    {
        $tglbppb = $request['m_tgl_bppb2'];
        $timestamp = Carbon::now();
        $no_bppb = $request['m_no_bppb2'];
        $bppb_temp_det = [];
        $sumQty = [];
        $data_aktual = 0;
        for ($i = 1; $i <= $request['tot_roll']; $i++) {
            if ($request["qty_out"][$i] > 0) {

                $barcode_in = DB::connection('mysql_sb')->select("select no_roll_buyer, IFNULL(np_curr_rev,np_curr) np_curr, np_tgl_in, IFNULL(np_price_rev,np_price) np_price from whs_lokasi_inmaterial where no_barcode = '" . $request["id_roll"][$i] . "' ORDER BY id ASC LIMIT 1");
                $no_roll_buyer = $barcode_in ? $barcode_in[0]->no_roll_buyer : null;
                $np_curr = $barcode_in ? $barcode_in[0]->np_curr : null;
                $np_tgl_in = $barcode_in ? $barcode_in[0]->np_tgl_in : null;
                $np_price = $barcode_in ? $barcode_in[0]->np_price : null;

                array_push($bppb_temp_det, [
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
                    "curr" => '',
                    "price" => '',
                    "status" => 'Y',
                    "created_by" => Auth::user()->name,
                    "deskripsi" => 'scan',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                    "price_in" => '',
                    "nilai_barang" => '',
                    "bc_in" => '',
                    "no_aju_in" => '',
                    "tgl_aju_in" => '',
                    "no_daftar_in" => '',
                    "tgl_daftar_in" => '',
                    "np_curr" => $np_curr,
                    "np_tgl_in" => $np_tgl_in,
                    "np_price" => $np_price,
                    "np_curr_rev" => null,
                    "np_price_rev" => null,
                ]);

                $groupKey = $no_bppb . '-' . $request["id_item"][$i] . '-' . $request["id_jo"][$i];

                if (!isset($sumQty[$groupKey])) {
                    $sumQty[$groupKey] = 0;
                }

                $sumQty[$groupKey] += $request['qty_out'][$i];
            }
        }

        // dd($bppb_temp_det);

        $BppbdetStore = BppbDet::insert($bppb_temp_det);

        foreach ($sumQty as $groupKey => $totalQty) {

            list($bppbno_int, $id_item, $id_jo) = explode('-', $groupKey);

            DB::connection('mysql_sb')
            ->table('bppb')
            ->where('bppbno_int', $bppbno_int)
            ->where('id_item', $id_item)
            ->where('id_jo', $id_jo)
            ->update([
                'qty_old' => DB::raw('qty'),
                'qty'     => $totalQty,
            ]);
        }


        $massage = 'Add data Succesfully';
        $stat = 200;

        return array(
            "status" => $stat,
            "message" => $massage,
            "additional" => [],
            "redirect" => ''
        );

    }


    public function saveoutmanualEdit(Request $request)
    {
        $tglbppb = $request['m_tgl_bppb'];
        $qtyOut = collect($request['qty_out']);

        $qtyOutKeys = $qtyOut->keys();

        if (intval($request['t_roll']) > 0 && intval($request['m_qty_bal_h']) >= 0) {
            $timestamp = Carbon::now();
            $no_bppb = $request['m_no_bppb'];
            $bppb_temp_det = [];
            $sumQty = [];
            $data_aktual = 0;
            foreach ($qtyOut as $key => $value) {
                if ($request['qty_out'][$key] > 0) {

                    $barcode_in = DB::connection('mysql_sb')->select("select no_roll_buyer, IFNULL(np_curr_rev,np_curr) np_curr, np_tgl_in, IFNULL(np_price_rev,np_price) np_price from whs_lokasi_inmaterial where no_barcode = '" . $request["id_roll"][$key] . "' ORDER BY id ASC LIMIT 1");
                    $no_roll_buyer = $barcode_in ? $barcode_in[0]->no_roll_buyer : null;
                    $np_curr = $barcode_in ? $barcode_in[0]->np_curr : null;
                    $np_tgl_in = $barcode_in ? $barcode_in[0]->np_tgl_in : null;
                    $np_price = $barcode_in ? $barcode_in[0]->np_price : null;

                    array_push($bppb_temp_det, [
                        "no_bppb" => $no_bppb,
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
                        "curr" => '',
                        "price" => '',
                        "status" => 'Y',
                        "created_by" => Auth::user()->name,
                        "deskripsi" => 'manual',
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp,
                        "price_in" => '',
                        "nilai_barang" => '',
                        "bc_in" => '',
                        "no_aju_in" => '',
                        "tgl_aju_in" => '',
                        "no_daftar_in" => '',
                        "tgl_daftar_in" => '',
                        "np_curr" => $np_curr,
                        "np_tgl_in" => $np_tgl_in,
                        "np_price" => $np_price,
                        "np_curr_rev" => null,
                        "np_price_rev" => null,
                    ]);

                    $groupKey = $no_bppb . '-' . $request["id_item"][$key] . '-' . $request["id_jo"][$key];

                    if (!isset($sumQty[$groupKey])) {
                        $sumQty[$groupKey] = 0;
                    }

                    $sumQty[$groupKey] += $request['qty_out'][$key];
                }
            }

            $BppbdetStore = BppbDet::insert($bppb_temp_det);

            foreach ($sumQty as $groupKey => $totalQty) {

                list($bppbno_int, $id_item, $id_jo) = explode('-', $groupKey);

                DB::connection('mysql_sb')
                ->table('bppb')
                ->where('bppbno_int', $bppbno_int)
                ->where('id_item', $id_item)
                ->where('id_jo', $id_jo)
                ->update([
                    'qty_old' => DB::raw('qty'),
                    'qty'     => $totalQty,
                ]);
            }


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

    public function out_barcode_fabric(Request $request)
    {


            $data_inmaterial = DB::connection('mysql_sb')->select("select no_bppb, tgl_bppb, id_roll, no_lot, no_roll, CONCAT(no_rak,'  FABRIC WAREHOUSE RACK') no_rak, id_item, itemdesc, color, size, IFNULL(no_invoice,'-') no_invoice, dok_bc, no_aju, tgl_aju, no_daftar, tgl_daftar, tujuan, qty_out, satuan, berat_bersih, IFNULL(catatan,'-') catatan, username, kpno, no_ws_aktual, styleno, IFNULL(np_curr,'-') np_curr, IFNULL(np_price,0) np_price, jenis_pengeluaran, IFNULL(np_price,0) price_unit, (qty_out * IFNULL(np_price,0)) total, IFNULL(rate,1) rate, ((qty_out * IFNULL(np_price,0)) * IFNULL(rate,1)) total_idr from (select  a.no_bppb, a.tgl_bppb, id_roll, no_lot, no_roll, no_rak, b.id_item, c.itemdesc, c.color, c.size, a.no_invoice, a.dok_bc, no_aju, tgl_aju, no_daftar, tgl_daftar, a.tujuan, b.qty_out, b.satuan, 0 berat_bersih, a.catatan, CONCAT(a.created_by,' (',a.created_at, ') ') username, kpno, styleno, IFNULL(b.np_curr_rev,b.np_curr) np_curr, np_tgl_in, IFNULL(b.np_price_rev,b.np_price) np_price, jenis_pengeluaran, no_ws_aktual from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb INNER JOIN masteritem c on c.id_item = b.id_item left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=b.id_jo where a.tgl_bppb BETWEEN '" . $request->start_date . "' and '" . $request->end_date . "' and a.status != 'Cancel' and b.status = 'Y') a left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.np_tgl_in and cr.curr = a.np_curr");


            return DataTables::of($data_inmaterial)->toJson();

    }


    public function mutasi_barcode_fabric(Request $request)
    {


            $data_inmaterial = DB::connection('mysql_sb')->select("WITH
            saldo_awal as (select a.no_barcode, IFNULL(CASE
                WHEN map.no_barcode = 'F244111' THEN 'F246063'
                WHEN map.no_barcode = 'F246105' THEN 'F246785'
                WHEN map.no_barcode = 'F244115' THEN 'F246065'
                WHEN map.no_barcode = 'F244107' THEN 'F246061'
                WHEN map.no_barcode = 'F244108' THEN 'F246062'
                WHEN map.no_barcode = 'F244112' THEN 'F246064'
                WHEN map.no_barcode = 'F246099' THEN 'F246779'
                WHEN map.no_barcode = 'F246100' THEN 'F246780'
                WHEN map.no_barcode = 'F246101' THEN 'F246781'
                WHEN map.no_barcode = 'F246102' THEN 'F246782'
                WHEN map.no_barcode = 'F246103' THEN 'F246783'
                WHEN map.no_barcode = 'F246104' THEN 'F246784'
                WHEN map.no_barcode = 'F246106' THEN 'F246786'
                WHEN map.no_barcode = 'F245995' THEN 'F249329'
                WHEN map.no_barcode = 'F245996' THEN 'F249330'
                WHEN map.no_barcode = 'F245997' THEN 'F249331'
                ELSE map.no_barcode END ,a.no_barcode) barcode_mapping, id_jo, a.id_item, b.goods_code, b.itemdesc, satuan, ws, price, rate, ROUND(sum(qty),4) saldo_awal_qty, ROUND(IF(qty > 0,(price * rate)/count(a.no_barcode),0),4) saldo_awal_price, (qty * (price * rate)) saldo_awal_total from whs_saldo_awal_nilai_persediaan a INNER JOIN masteritem b on b.id_item = a.id_item LEFT JOIN (select idbpb_det, no_barcode from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where a.status = 'Y' GROUP BY no_barcode) map on map.idbpb_det = a.no_barcode where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_saldo_awal_nilai_persediaan WHERE tgl_periode <= '" . $request->start_date . "') GROUP BY a.no_barcode),

            trx_in AS (select b.no_barcode, IFNULL(map.no_barcode,b.no_barcode) barcode_mapping, b.id_jo, b.id_item, mi.goods_code, mi.itemdesc, b.satuan, kpno no_ws, type_pch, qty_sj, COALESCE(IFNULL(np_curr_rev,np_curr),'-') curr, ROUND(COALESCE(IFNULL(np_price_rev,np_price),0),4) price, (qty_sj * (COALESCE(IFNULL(np_price_rev,np_price),0))) total_price, np_tgl_in, IFNULL(rate,1) rate from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem mi on mi.id_item = b.id_item INNER JOIN (select id_jo, kpno, styleno from act_costing ac inner join so on ac.id = so.id_cost inner join jo_det jod on so.id = jod.id_so group by id_jo) tmpjo on tmpjo.id_jo = b.id_jo LEFT JOIN (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = b.np_tgl_in and cr.curr_rate = COALESCE(IFNULL(b.np_curr_rev,b.np_curr),'-') LEFT JOIN (select idbpb_det, no_barcode from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where a.status = 'Y' GROUP BY no_barcode) map on map.idbpb_det = b.no_barcode where a.tgl_dok BETWEEN '" . $request->start_date . "' and '" . $request->end_date . "' and b.status = 'Y'),

            trx_out AS (select CASE
                WHEN id_roll = 'F229331' THEN 'F246048'
                WHEN id_roll = 'F238451' THEN 'F246050'
                ELSE id_roll END
                id_roll, id_jo, id_item, CASE
                WHEN a.jenis_pengeluaran IS NULL THEN '-'
                WHEN a.jenis_pengeluaran = 'penjualan' AND sg.supplier IS NULL THEN 'Sales Nongroup'
                WHEN a.jenis_pengeluaran = 'penjualan' AND sg.supplier IS NOT NULL THEN 'Sales Group'
                ELSE a.jenis_pengeluaran
                END type_pch, (COALESCE(qty_out,0)) qty_sj, COALESCE(IFNULL(np_curr_rev,np_curr),'-') curr, ROUND(COALESCE(IFNULL(np_price_rev,np_price),0),4) price, (qty_out * (COALESCE(IFNULL(np_price_rev,np_price),0))) total_price, np_tgl_in, IFNULL(rate,1) rate from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb LEFT JOIN (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = b.np_tgl_in and cr.curr_rate = COALESCE(IFNULL(b.np_curr_rev,b.np_curr),'-') left join (select id_supplier, supplier from ca_sales_group) sg on sg.supplier = a.tujuan where tgl_bppb BETWEEN '" . $request->start_date . "' and '" . $request->end_date . "' and a.status != 'Cancel' and b.status = 'Y'
            ),

            trx_in_detail as (SELECT
                no_barcode, barcode_mapping, id_jo, id_item, goods_code, itemdesc, satuan, no_ws,
    -- Pembelian Lokal
    SUM(CASE WHEN type_pch='Pembelian Lokal' THEN qty_sj ELSE 0 END) AS in_lokal_qty,
    CASE
    WHEN SUM(CASE WHEN type_pch='Pembelian Lokal' THEN qty_sj ELSE 0 END) > 0
    THEN ROUND(SUM(CASE WHEN type_pch='Pembelian Lokal' THEN (price * rate) ELSE 0 END)
       / COUNT(CASE WHEN type_pch='Pembelian Lokal' THEN 1 END),4)
    ELSE 0
    END AS in_lokal_price,
    SUM(CASE WHEN type_pch='Pembelian Lokal' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_lokal_total,

        -- Pembelian Impor
        SUM(CASE WHEN type_pch='Pembelian Impor' THEN qty_sj ELSE 0 END) AS in_impor_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pembelian Impor' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pembelian Impor' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pembelian Impor' THEN 1 END),4)
        ELSE 0
        END AS in_impor_price,
        SUM(CASE WHEN type_pch='Pembelian Impor' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_impor_total,

        -- Pengembalian dari Subkontraktor Jasa
        SUM(CASE WHEN type_pch IN ('Pengembalian dari Subkontraktor CMT', 'Pengembalian dari Subkontraktor Jasa') THEN qty_sj ELSE 0 END) AS in_subcont_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch IN ('Pengembalian dari Subkontraktor CMT', 'Pengembalian dari Subkontraktor Jasa') THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch IN ('Pengembalian dari Subkontraktor CMT', 'Pengembalian dari Subkontraktor Jasa') THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch IN ('Pengembalian dari Subkontraktor CMT', 'Pengembalian dari Subkontraktor Jasa') THEN 1 END),4)
        ELSE 0
        END AS in_subcont_price,
        SUM(CASE WHEN type_pch IN ('Pengembalian dari Subkontraktor CMT', 'Pengembalian dari Subkontraktor Jasa') THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_subcont_total,

        -- Pengembalian dari Produksi
        SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN qty_sj ELSE 0 END) AS in_produksi_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pengembalian dari Produksi' THEN 1 END),4)
        ELSE 0
        END AS in_produksi_price,
        SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_produksi_total,

        -- Pengembalian dari Sample Room
        SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN qty_sj ELSE 0 END) AS in_sample_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN 1 END),4)
        ELSE 0
        END AS in_sample_price,
        SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_sample_total
        FROM trx_in GROUP BY no_barcode),

            trx_in_fix as (select *, (in_lokal_qty + in_impor_qty + in_subcont_qty + in_produksi_qty + in_sample_qty) jumlah_in_qty, ROUND((in_lokal_total + in_impor_total + in_subcont_total + in_produksi_total + in_sample_total) / (in_lokal_qty + in_impor_qty + in_subcont_qty + in_produksi_qty + in_sample_qty),4) jumlah_in_price, (in_lokal_total + in_impor_total + in_subcont_total + in_produksi_total + in_sample_total) jumlah_in_total from trx_in_detail),

            trx_out_detail as (SELECT
                id_roll, id_jo, id_item,
    -- Pemakaian produksi
    SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN qty_sj ELSE 0 END) AS out_prod_qty,
    CASE
    WHEN SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN qty_sj ELSE 0 END) > 0
    THEN ROUND(SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN (price * rate) ELSE 0 END)
       / COUNT(CASE WHEN type_pch='Pemakaian Produksi' THEN 1 END),4)
    ELSE 0
    END AS out_prod_price,
    SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_prod_total,

        -- Jasa Subcont
        SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN qty_sj ELSE 0 END) AS out_subcont_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN 1 END),4)
        ELSE 0
        END AS out_subcont_price,
        SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_subcont_total,

        -- Retur Pembelian Lokal
        SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN qty_sj ELSE 0 END) AS out_lokal_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN 1 END),4)
        ELSE 0
        END AS out_lokal_price,
        SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_lokal_total,

        -- Retur Pembelian Import
        SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN qty_sj ELSE 0 END) AS out_impor_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Retur Pembelian Impor' THEN 1 END),4)
        ELSE 0
        END AS out_impor_price,
        SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_impor_total,

        -- Pemakaian Sample Room
        SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN qty_sj ELSE 0 END) AS out_sample_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pemakaian Sample Room' THEN 1 END),4)
        ELSE 0
        END AS out_sample_price,
        SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_sample_total,
        -- Sales Nongroup
        SUM(CASE WHEN type_pch='Sales Nongroup' THEN qty_sj ELSE 0 END) AS out_salnongroup_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Sales Nongroup' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Sales Nongroup' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Sales Nongroup' THEN 1 END),4)
        ELSE 0
        END AS out_salnongroup_price,
        SUM(CASE WHEN type_pch='Sales Nongroup' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_salnongroup_total,
        -- Sales Group
        SUM(CASE WHEN type_pch='Sales Group' THEN qty_sj ELSE 0 END) AS out_salgroup_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Sales Group' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Sales Group' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Sales Group' THEN 1 END),4)
        ELSE 0
        END AS out_salgroup_price,
        SUM(CASE WHEN type_pch='Sales Nongroup' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_salgroup_total,
        -- Other
        SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN qty_sj ELSE 0 END) AS out_other_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN (price * rate) ELSE 0 END)
        / COUNT(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN 1 END),4)
        ELSE 0
        END AS out_other_price,
        SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_other_total
        FROM trx_out GROUP BY id_roll),

trx_out_fix as (select *, (out_prod_qty + out_subcont_qty + out_lokal_qty + out_impor_qty + out_sample_qty + out_salnongroup_qty + out_salgroup_qty + out_other_qty) jumlah_out_qty, ROUND((out_prod_total + out_subcont_total + out_lokal_total + out_impor_total + out_sample_total + out_salnongroup_total + out_salgroup_total + out_other_total) / (out_prod_qty + out_subcont_qty + out_lokal_qty + out_impor_qty + out_sample_qty + out_salnongroup_qty + out_salgroup_qty + out_other_qty),4) jumlah_out_price, (out_prod_total + out_subcont_total + out_lokal_total + out_impor_total + out_sample_total + out_salnongroup_total + out_salgroup_total + out_other_total) jumlah_out_total from trx_out_detail),

pemasukan as (select a.no_barcode, a.barcode_mapping, a.id_jo, a.id_item, a.goods_code, a.itemdesc, a.satuan, a.ws no_ws, COALESCE(saldo_awal_qty,0) saldo_awal_qty, COALESCE(saldo_awal_price,0) saldo_awal_price, COALESCE(saldo_awal_total,0) saldo_awal_total, COALESCE(in_lokal_qty,0) in_lokal_qty, COALESCE(in_lokal_price,0) in_lokal_price, COALESCE(in_lokal_total,0) in_lokal_total, COALESCE(in_impor_qty,0) in_impor_qty, COALESCE(in_impor_price,0) in_impor_price, COALESCE(in_impor_total,0) in_impor_total, COALESCE(in_subcont_qty,0) in_subcont_qty, COALESCE(in_subcont_price,0) in_subcont_price, COALESCE(in_subcont_total,0) in_subcont_total, COALESCE(in_produksi_qty,0) in_produksi_qty, COALESCE(in_produksi_price,0) in_produksi_price, COALESCE(in_produksi_total,0) in_produksi_total, COALESCE(in_sample_qty,0) in_sample_qty, COALESCE(in_sample_price,0) in_sample_price, COALESCE(in_sample_total,0) in_sample_total, COALESCE(jumlah_in_qty,0) jumlah_in_qty, COALESCE(jumlah_in_price,0) jumlah_in_price, COALESCE(jumlah_in_total,0) jumlah_in_total from saldo_awal a left join trx_in_fix b on b.no_barcode = a.no_barcode
    UNION
    select a.no_barcode, a.barcode_mapping, a.id_jo, a.id_item, a.goods_code, a.itemdesc, a.satuan, a.no_ws, COALESCE(saldo_awal_qty,0) saldo_awal_qty, COALESCE(saldo_awal_price,0) saldo_awal_price, COALESCE(saldo_awal_total,0) saldo_awal_total, COALESCE(in_lokal_qty,0) in_lokal_qty, COALESCE(in_lokal_price,0) in_lokal_price, COALESCE(in_lokal_total,0) in_lokal_total, COALESCE(in_impor_qty,0) in_impor_qty, COALESCE(in_impor_price,0) in_impor_price, COALESCE(in_impor_total,0) in_impor_total, COALESCE(in_subcont_qty,0) in_subcont_qty, COALESCE(in_subcont_price,0) in_subcont_price, COALESCE(in_subcont_total,0) in_subcont_total, COALESCE(in_produksi_qty,0) in_produksi_qty, COALESCE(in_produksi_price,0) in_produksi_price, COALESCE(in_produksi_total,0) in_produksi_total, COALESCE(in_sample_qty,0) in_sample_qty, COALESCE(in_sample_price,0) in_sample_price, COALESCE(in_sample_total,0) in_sample_total, COALESCE(jumlah_in_qty,0) jumlah_in_qty, COALESCE(jumlah_in_price,0) jumlah_in_price, COALESCE(jumlah_in_total,0) jumlah_in_total from trx_in_fix a left join saldo_awal b on b.no_barcode = a.no_barcode where b.no_barcode IS NULL),

Pemasukan_fix as (SELECT
    no_barcode,
    barcode_mapping,
    id_jo,
    id_item,
    goods_code,
    itemdesc,
    satuan,
    no_ws,

    -- SALDO AWAL
    COALESCE(SUM(saldo_awal_qty),0) AS saldo_awal_qty,
    COALESCE(SUM(saldo_awal_total),0) AS saldo_awal_total,
    IF(SUM(saldo_awal_qty)=0, 0, SUM(saldo_awal_total)/SUM(saldo_awal_qty)) AS saldo_awal_price,

    -- IN LOKAL
    COALESCE(SUM(in_lokal_qty),0) AS in_lokal_qty,
    COALESCE(SUM(in_lokal_total),0) AS in_lokal_total,
    IF(SUM(in_lokal_qty)=0, 0, SUM(in_lokal_total)/SUM(in_lokal_qty)) AS in_lokal_price,

    -- IN IMPOR
    COALESCE(SUM(in_impor_qty),0) AS in_impor_qty,
    COALESCE(SUM(in_impor_total),0) AS in_impor_total,
    IF(SUM(in_impor_qty)=0, 0, SUM(in_impor_total)/SUM(in_impor_qty)) AS in_impor_price,

    -- IN SUBCONT
    COALESCE(SUM(in_subcont_qty),0) AS in_subcont_qty,
    COALESCE(SUM(in_subcont_total),0) AS in_subcont_total,
    IF(SUM(in_subcont_qty)=0, 0, SUM(in_subcont_total)/SUM(in_subcont_qty)) AS in_subcont_price,

    -- IN PRODUKSI
    COALESCE(SUM(in_produksi_qty),0) AS in_produksi_qty,
    COALESCE(SUM(in_produksi_total),0) AS in_produksi_total,
    IF(SUM(in_produksi_qty)=0, 0, SUM(in_produksi_total)/SUM(in_produksi_qty)) AS in_produksi_price,

    -- IN SAMPLE
    COALESCE(SUM(in_sample_qty),0) AS in_sample_qty,
    COALESCE(SUM(in_sample_total),0) AS in_sample_total,
    IF(SUM(in_sample_qty)=0, 0, SUM(in_sample_total)/SUM(in_sample_qty)) AS in_sample_price,

    -- TOTAL IN
    COALESCE(SUM(jumlah_in_qty),0) AS jumlah_in_qty,
    COALESCE(SUM(jumlah_in_total),0) AS jumlah_in_total,
    IF(SUM(jumlah_in_qty)=0, 0, SUM(jumlah_in_total)/SUM(jumlah_in_qty)) AS jumlah_in_price

    FROM pemasukan
    GROUP BY barcode_mapping),

pengeluaran_fix as (SELECT
    id_roll,
    id_jo,
    id_item,

    -- OUT PRODUKSI
    COALESCE(SUM(out_prod_qty),0) AS out_prod_qty,
    COALESCE(SUM(out_prod_total),0) AS out_prod_total,
    IF(SUM(out_prod_qty)=0, 0, SUM(out_prod_total)/SUM(out_prod_qty)) AS out_prod_price,

    -- OUT SUBCONT
    COALESCE(SUM(out_subcont_qty),0) AS out_subcont_qty,
    COALESCE(SUM(out_subcont_total),0) AS out_subcont_total,
    IF(SUM(out_subcont_qty)=0, 0, SUM(out_subcont_total)/SUM(out_subcont_qty)) AS out_subcont_price,

    -- OUT LOKAL
    COALESCE(SUM(out_lokal_qty),0) AS out_lokal_qty,
    COALESCE(SUM(out_lokal_total),0) AS out_lokal_total,
    IF(SUM(out_lokal_qty)=0, 0, SUM(out_lokal_total)/SUM(out_lokal_qty)) AS out_lokal_price,

    -- OUT IMPOR
    COALESCE(SUM(out_impor_qty),0) AS out_impor_qty,
    COALESCE(SUM(out_impor_total),0) AS out_impor_total,
    IF(SUM(out_impor_qty)=0, 0, SUM(out_impor_total)/SUM(out_impor_qty)) AS out_impor_price,

    -- OUT SAMPLE
    COALESCE(SUM(out_sample_qty),0) AS out_sample_qty,
    COALESCE(SUM(out_sample_total),0) AS out_sample_total,
    IF(SUM(out_sample_qty)=0, 0, SUM(out_sample_total)/SUM(out_sample_qty)) AS out_sample_price,

    -- OUT SAL NON GROUP
    COALESCE(SUM(out_salnongroup_qty),0) AS out_salnongroup_qty,
    COALESCE(SUM(out_salnongroup_total),0) AS out_salnongroup_total,
    IF(SUM(out_salnongroup_qty)=0, 0, SUM(out_salnongroup_total)/SUM(out_salnongroup_qty)) AS out_salnongroup_price,

    -- OUT SAL GROUP
    COALESCE(SUM(out_salgroup_qty),0) AS out_salgroup_qty,
    COALESCE(SUM(out_salgroup_total),0) AS out_salgroup_total,
    IF(SUM(out_salgroup_qty)=0, 0, SUM(out_salgroup_total)/SUM(out_salgroup_qty)) AS out_salgroup_price,

    -- OUT OTHER
    COALESCE(SUM(out_other_qty),0) AS out_other_qty,
    COALESCE(SUM(out_other_total),0) AS out_other_total,
    IF(SUM(out_other_qty)=0, 0, SUM(out_other_total)/SUM(out_other_qty)) AS out_other_price,

    -- TOTAL OUT
    COALESCE(SUM(jumlah_out_qty),0) AS jumlah_out_qty,
    COALESCE(SUM(jumlah_out_total),0) AS jumlah_out_total,
    IF(SUM(jumlah_out_qty)=0, 0, SUM(jumlah_out_total)/SUM(jumlah_out_qty)) AS jumlah_out_price

    FROM trx_out_fix
    GROUP BY id_roll),

mutasi as (select a.*, COALESCE(out_prod_qty,0) out_prod_qty,   COALESCE(out_prod_total,0) out_prod_total,   COALESCE(out_prod_price,0) out_prod_price,   COALESCE(out_subcont_qty,0) out_subcont_qty,   COALESCE(out_subcont_total,0) out_subcont_total,   COALESCE(out_subcont_price,0) out_subcont_price,   COALESCE(out_lokal_qty,0) out_lokal_qty,   COALESCE(out_lokal_total,0) out_lokal_total,   COALESCE(out_lokal_price,0) out_lokal_price,   COALESCE(out_impor_qty,0) out_impor_qty,   COALESCE(out_impor_total,0) out_impor_total,   COALESCE(out_impor_price,0) out_impor_price,   COALESCE(out_sample_qty,0) out_sample_qty,   COALESCE(out_sample_total,0) out_sample_total,   COALESCE(out_sample_price,0) out_sample_price,   COALESCE(out_salnongroup_qty,0) out_salnongroup_qty,   COALESCE(out_salnongroup_total,0) out_salnongroup_total,   COALESCE(out_salnongroup_price,0) out_salnongroup_price,   COALESCE(out_salgroup_qty,0) out_salgroup_qty,   COALESCE(out_salgroup_total,0) out_salgroup_total,   COALESCE(out_salgroup_price,0) out_salgroup_price,   COALESCE(out_other_qty,0) out_other_qty,   COALESCE(out_other_total,0) out_other_total,   COALESCE(out_other_price,0) out_other_price,   COALESCE(jumlah_out_qty,0) jumlah_out_qty,   COALESCE(jumlah_out_total,0) jumlah_out_total,   COALESCE(jumlah_out_price,0) jumlah_out_price from pemasukan_fix a left join pengeluaran_fix b on b.id_roll = a.barcode_mapping)

select *, (saldo_awal_qty + jumlah_in_qty - jumlah_out_qty) saldo_akhir_qty, (saldo_awal_total + jumlah_in_total - jumlah_out_total) saldo_akhir_total, ((saldo_awal_total + jumlah_in_total - jumlah_out_total) / (saldo_awal_qty + jumlah_in_qty - jumlah_out_qty)) saldo_akhir_price from mutasi");


            return DataTables::of($data_inmaterial)->toJson();

    }

     public function mutasi_item_fabric(Request $request)
    {


            $data_inmaterial = DB::connection('mysql_sb')->select("WITH
            saldo_awal as (select a.no_barcode, IFNULL(CASE
                WHEN map.no_barcode = 'F244111' THEN 'F246063'
                WHEN map.no_barcode = 'F246105' THEN 'F246785'
                ELSE map.no_barcode END ,a.no_barcode) barcode_mapping, id_jo, a.id_item, b.goods_code, b.itemdesc, satuan, ws, price, rate, ROUND(sum(qty),4) saldo_awal_qty, ROUND(IF(qty > 0,(price * rate)/count(a.no_barcode),0),4) saldo_awal_price, (qty * (price * rate)) saldo_awal_total from whs_saldo_awal_nilai_persediaan a INNER JOIN masteritem b on b.id_item = a.id_item LEFT JOIN (select idbpb_det, no_barcode from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where a.status = 'Y' GROUP BY no_barcode) map on map.idbpb_det = a.no_barcode where tgl_periode = (SELECT MAX(tgl_periode) FROM whs_saldo_awal_nilai_persediaan WHERE tgl_periode <= '" . $request->start_date . "') GROUP BY a.no_barcode),

            trx_in AS (select b.no_barcode, IFNULL(map.no_barcode,b.no_barcode) barcode_mapping, b.id_jo, b.id_item, mi.goods_code, mi.itemdesc, b.satuan, kpno no_ws, type_pch, qty_sj, COALESCE(IFNULL(np_curr_rev,np_curr),'-') curr, ROUND(COALESCE(IFNULL(np_price_rev,np_price),0),4) price, (qty_sj * (COALESCE(IFNULL(np_price_rev,np_price),0))) total_price, np_tgl_in, IFNULL(rate,1) rate from whs_inmaterial_fabric a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok INNER JOIN masteritem mi on mi.id_item = b.id_item INNER JOIN (select id_jo, kpno, styleno from act_costing ac inner join so on ac.id = so.id_cost inner join jo_det jod on so.id = jod.id_so group by id_jo) tmpjo on tmpjo.id_jo = b.id_jo LEFT JOIN (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = b.np_tgl_in and cr.curr_rate = COALESCE(IFNULL(b.np_curr_rev,b.np_curr),'-') LEFT JOIN (select idbpb_det, no_barcode from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where a.status = 'Y' GROUP BY no_barcode) map on map.idbpb_det = b.no_barcode where a.tgl_dok BETWEEN '" . $request->start_date . "' and '" . $request->end_date . "' and b.status = 'Y'),

            trx_out AS (select CASE
                WHEN id_roll = 'F229331' THEN 'F246048'
                WHEN id_roll = 'F238451' THEN 'F246050'
                ELSE id_roll END
                id_roll, id_jo, id_item, CASE
                WHEN a.jenis_pengeluaran IS NULL THEN '-'
                WHEN a.jenis_pengeluaran = 'penjualan' AND sg.supplier IS NULL THEN 'Sales Nongroup'
                WHEN a.jenis_pengeluaran = 'penjualan' AND sg.supplier IS NOT NULL THEN 'Sales Group'
                ELSE a.jenis_pengeluaran
                END type_pch, (COALESCE(qty_out,0)) qty_sj, COALESCE(IFNULL(np_curr_rev,np_curr),'-') curr, ROUND(COALESCE(IFNULL(np_price_rev,np_price),0),4) price, (qty_out * (COALESCE(IFNULL(np_price_rev,np_price),0))) total_price, np_tgl_in, IFNULL(rate,1) rate from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb LEFT JOIN (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = b.np_tgl_in and cr.curr_rate = COALESCE(IFNULL(b.np_curr_rev,b.np_curr),'-') left join (select id_supplier, supplier from ca_sales_group) sg on sg.supplier = a.tujuan where tgl_bppb BETWEEN '" . $request->start_date . "' and '" . $request->end_date . "' and a.status != 'Cancel' and b.status = 'Y'
            ),

            trx_in_detail as (SELECT
                no_barcode, barcode_mapping, id_jo, id_item, goods_code, itemdesc, satuan, no_ws,
    -- Pembelian Lokal
    SUM(CASE WHEN type_pch='Pembelian Lokal' THEN qty_sj ELSE 0 END) AS in_lokal_qty,
    CASE
    WHEN SUM(CASE WHEN type_pch='Pembelian Lokal' THEN qty_sj ELSE 0 END) > 0
    THEN ROUND(SUM(CASE WHEN type_pch='Pembelian Lokal' THEN (price * rate) ELSE 0 END)
       / COUNT(CASE WHEN type_pch='Pembelian Lokal' THEN 1 END),4)
    ELSE 0
    END AS in_lokal_price,
    SUM(CASE WHEN type_pch='Pembelian Lokal' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_lokal_total,

        -- Pembelian Impor
        SUM(CASE WHEN type_pch='Pembelian Impor' THEN qty_sj ELSE 0 END) AS in_impor_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pembelian Impor' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pembelian Impor' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pembelian Impor' THEN 1 END),4)
        ELSE 0
        END AS in_impor_price,
        SUM(CASE WHEN type_pch='Pembelian Impor' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_impor_total,

        -- Pengembalian dari Subkontraktor Jasa
        SUM(CASE WHEN type_pch='Pengembalian dari Subkontraktor Jasa' THEN qty_sj ELSE 0 END) AS in_subcont_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pengembalian dari Subkontraktor Jasa' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pengembalian dari Subkontraktor Jasa' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pengembalian dari Subkontraktor Jasa' THEN 1 END),4)
        ELSE 0
        END AS in_subcont_price,
        SUM(CASE WHEN type_pch='Pengembalian dari Subkontraktor Jasa' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_subcont_total,

        -- Pengembalian dari Produksi
        SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN qty_sj ELSE 0 END) AS in_produksi_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pengembalian dari Produksi' THEN 1 END),4)
        ELSE 0
        END AS in_produksi_price,
        SUM(CASE WHEN type_pch='Pengembalian dari Produksi' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_produksi_total,

        -- Pengembalian dari Sample Room
        SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN qty_sj ELSE 0 END) AS in_sample_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN 1 END),4)
        ELSE 0
        END AS in_sample_price,
        SUM(CASE WHEN type_pch='Pengembalian dari Sample Room' THEN ROUND(total_price * rate,4) ELSE 0 END) AS in_sample_total
        FROM trx_in GROUP BY no_barcode),

            trx_in_fix as (select *, (in_lokal_qty + in_impor_qty + in_subcont_qty + in_produksi_qty + in_sample_qty) jumlah_in_qty, ROUND((in_lokal_total + in_impor_total + in_subcont_total + in_produksi_total + in_sample_total) / (in_lokal_qty + in_impor_qty + in_subcont_qty + in_produksi_qty + in_sample_qty),4) jumlah_in_price, (in_lokal_total + in_impor_total + in_subcont_total + in_produksi_total + in_sample_total) jumlah_in_total from trx_in_detail),

            trx_out_detail as (SELECT
                id_roll, id_jo, id_item,
    -- Pemakaian produksi
    SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN qty_sj ELSE 0 END) AS out_prod_qty,
    CASE
    WHEN SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN qty_sj ELSE 0 END) > 0
    THEN ROUND(SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN (price * rate) ELSE 0 END)
       / COUNT(CASE WHEN type_pch='Pemakaian Produksi' THEN 1 END),4)
    ELSE 0
    END AS out_prod_price,
    SUM(CASE WHEN type_pch='Pemakaian Produksi' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_prod_total,

        -- Jasa Subcont
        SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN qty_sj ELSE 0 END) AS out_subcont_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN 1 END),4)
        ELSE 0
        END AS out_subcont_price,
        SUM(CASE WHEN type_pch IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa') THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_subcont_total,

        -- Retur Pembelian Lokal
        SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN qty_sj ELSE 0 END) AS out_lokal_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN 1 END),4)
        ELSE 0
        END AS out_lokal_price,
        SUM(CASE WHEN type_pch = 'Retur Pembelian Lokal' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_lokal_total,

        -- Retur Pembelian Import
        SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN qty_sj ELSE 0 END) AS out_impor_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Retur Pembelian Impor' THEN 1 END),4)
        ELSE 0
        END AS out_impor_price,
        SUM(CASE WHEN type_pch='Retur Pembelian Impor' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_impor_total,

        -- Pemakaian Sample Room
        SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN qty_sj ELSE 0 END) AS out_sample_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Pemakaian Sample Room' THEN 1 END),4)
        ELSE 0
        END AS out_sample_price,
        SUM(CASE WHEN type_pch='Pemakaian Sample Room' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_sample_total,
        -- Sales Nongroup
        SUM(CASE WHEN type_pch='Sales Nongroup' THEN qty_sj ELSE 0 END) AS out_salnongroup_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Sales Nongroup' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Sales Nongroup' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Sales Nongroup' THEN 1 END),4)
        ELSE 0
        END AS out_salnongroup_price,
        SUM(CASE WHEN type_pch='Sales Nongroup' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_salnongroup_total,
        -- Sales Group
        SUM(CASE WHEN type_pch='Sales Group' THEN qty_sj ELSE 0 END) AS out_salgroup_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch='Sales Group' THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch='Sales Group' THEN (price * rate) ELSE 0 END)
           / COUNT(CASE WHEN type_pch='Sales Nongroup' THEN 1 END),4)
        ELSE 0
        END AS out_salgroup_price,
        SUM(CASE WHEN type_pch='Sales Nongroup' THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_salgroup_total,
        -- Other
        SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN qty_sj ELSE 0 END) AS out_other_qty,
        CASE
        WHEN SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN qty_sj ELSE 0 END) > 0
        THEN ROUND(SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN (price * rate) ELSE 0 END)
        / COUNT(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN 1 END),4)
        ELSE 0
        END AS out_other_price,
        SUM(CASE WHEN type_pch NOT IN ('Pengiriman ke Subkontraktor CMT', 'Pengiriman ke Subkontraktor Jasa',
            'Retur Pembelian Lokal', 'Pemakaian Produksi', 'Retur Pembelian Impor', 'Pemakaian Sample Room','Sales Nongroup', 'Sales Group') THEN ROUND(total_price * rate,4) ELSE 0 END) AS out_other_total
        FROM trx_out GROUP BY id_roll),

trx_out_fix as (select *, (out_prod_qty + out_subcont_qty + out_lokal_qty + out_impor_qty + out_sample_qty + out_salnongroup_qty + out_salgroup_qty + out_other_qty) jumlah_out_qty, ROUND((out_prod_total + out_subcont_total + out_lokal_total + out_impor_total + out_sample_total + out_salnongroup_total + out_salgroup_total + out_other_total) / (out_prod_qty + out_subcont_qty + out_lokal_qty + out_impor_qty + out_sample_qty + out_salnongroup_qty + out_salgroup_qty + out_other_qty),4) jumlah_out_price, (out_prod_total + out_subcont_total + out_lokal_total + out_impor_total + out_sample_total + out_salnongroup_total + out_salgroup_total + out_other_total) jumlah_out_total from trx_out_detail),

pemasukan as (select a.no_barcode, a.barcode_mapping, a.id_jo, a.id_item, a.goods_code, a.itemdesc, a.satuan, a.ws no_ws, COALESCE(saldo_awal_qty,0) saldo_awal_qty, COALESCE(saldo_awal_price,0) saldo_awal_price, COALESCE(saldo_awal_total,0) saldo_awal_total, COALESCE(in_lokal_qty,0) in_lokal_qty, COALESCE(in_lokal_price,0) in_lokal_price, COALESCE(in_lokal_total,0) in_lokal_total, COALESCE(in_impor_qty,0) in_impor_qty, COALESCE(in_impor_price,0) in_impor_price, COALESCE(in_impor_total,0) in_impor_total, COALESCE(in_subcont_qty,0) in_subcont_qty, COALESCE(in_subcont_price,0) in_subcont_price, COALESCE(in_subcont_total,0) in_subcont_total, COALESCE(in_produksi_qty,0) in_produksi_qty, COALESCE(in_produksi_price,0) in_produksi_price, COALESCE(in_produksi_total,0) in_produksi_total, COALESCE(in_sample_qty,0) in_sample_qty, COALESCE(in_sample_price,0) in_sample_price, COALESCE(in_sample_total,0) in_sample_total, COALESCE(jumlah_in_qty,0) jumlah_in_qty, COALESCE(jumlah_in_price,0) jumlah_in_price, COALESCE(jumlah_in_total,0) jumlah_in_total from saldo_awal a left join trx_in_fix b on b.no_barcode = a.no_barcode
    UNION
    select a.no_barcode, a.barcode_mapping, a.id_jo, a.id_item, a.goods_code, a.itemdesc, a.satuan, a.no_ws, COALESCE(saldo_awal_qty,0) saldo_awal_qty, COALESCE(saldo_awal_price,0) saldo_awal_price, COALESCE(saldo_awal_total,0) saldo_awal_total, COALESCE(in_lokal_qty,0) in_lokal_qty, COALESCE(in_lokal_price,0) in_lokal_price, COALESCE(in_lokal_total,0) in_lokal_total, COALESCE(in_impor_qty,0) in_impor_qty, COALESCE(in_impor_price,0) in_impor_price, COALESCE(in_impor_total,0) in_impor_total, COALESCE(in_subcont_qty,0) in_subcont_qty, COALESCE(in_subcont_price,0) in_subcont_price, COALESCE(in_subcont_total,0) in_subcont_total, COALESCE(in_produksi_qty,0) in_produksi_qty, COALESCE(in_produksi_price,0) in_produksi_price, COALESCE(in_produksi_total,0) in_produksi_total, COALESCE(in_sample_qty,0) in_sample_qty, COALESCE(in_sample_price,0) in_sample_price, COALESCE(in_sample_total,0) in_sample_total, COALESCE(jumlah_in_qty,0) jumlah_in_qty, COALESCE(jumlah_in_price,0) jumlah_in_price, COALESCE(jumlah_in_total,0) jumlah_in_total from trx_in_fix a left join saldo_awal b on b.no_barcode = a.no_barcode where b.no_barcode IS NULL),

Pemasukan_fix as (SELECT
    no_barcode,
    barcode_mapping,
    id_jo,
    id_item,
    goods_code,
    itemdesc,
    satuan,
    no_ws,

    -- SALDO AWAL
    COALESCE(SUM(saldo_awal_qty),0) AS saldo_awal_qty,
    COALESCE(SUM(saldo_awal_total),0) AS saldo_awal_total,
    IF(SUM(saldo_awal_qty)=0, 0, SUM(saldo_awal_total)/SUM(saldo_awal_qty)) AS saldo_awal_price,

    -- IN LOKAL
    COALESCE(SUM(in_lokal_qty),0) AS in_lokal_qty,
    COALESCE(SUM(in_lokal_total),0) AS in_lokal_total,
    IF(SUM(in_lokal_qty)=0, 0, SUM(in_lokal_total)/SUM(in_lokal_qty)) AS in_lokal_price,

    -- IN IMPOR
    COALESCE(SUM(in_impor_qty),0) AS in_impor_qty,
    COALESCE(SUM(in_impor_total),0) AS in_impor_total,
    IF(SUM(in_impor_qty)=0, 0, SUM(in_impor_total)/SUM(in_impor_qty)) AS in_impor_price,

    -- IN SUBCONT
    COALESCE(SUM(in_subcont_qty),0) AS in_subcont_qty,
    COALESCE(SUM(in_subcont_total),0) AS in_subcont_total,
    IF(SUM(in_subcont_qty)=0, 0, SUM(in_subcont_total)/SUM(in_subcont_qty)) AS in_subcont_price,

    -- IN PRODUKSI
    COALESCE(SUM(in_produksi_qty),0) AS in_produksi_qty,
    COALESCE(SUM(in_produksi_total),0) AS in_produksi_total,
    IF(SUM(in_produksi_qty)=0, 0, SUM(in_produksi_total)/SUM(in_produksi_qty)) AS in_produksi_price,

    -- IN SAMPLE
    COALESCE(SUM(in_sample_qty),0) AS in_sample_qty,
    COALESCE(SUM(in_sample_total),0) AS in_sample_total,
    IF(SUM(in_sample_qty)=0, 0, SUM(in_sample_total)/SUM(in_sample_qty)) AS in_sample_price,

    -- TOTAL IN
    COALESCE(SUM(jumlah_in_qty),0) AS jumlah_in_qty,
    COALESCE(SUM(jumlah_in_total),0) AS jumlah_in_total,
    IF(SUM(jumlah_in_qty)=0, 0, SUM(jumlah_in_total)/SUM(jumlah_in_qty)) AS jumlah_in_price

    FROM pemasukan
    GROUP BY barcode_mapping),

pengeluaran_fix as (SELECT
    id_roll,
    id_jo,
    id_item,

    -- OUT PRODUKSI
    COALESCE(SUM(out_prod_qty),0) AS out_prod_qty,
    COALESCE(SUM(out_prod_total),0) AS out_prod_total,
    IF(SUM(out_prod_qty)=0, 0, SUM(out_prod_total)/SUM(out_prod_qty)) AS out_prod_price,

    -- OUT SUBCONT
    COALESCE(SUM(out_subcont_qty),0) AS out_subcont_qty,
    COALESCE(SUM(out_subcont_total),0) AS out_subcont_total,
    IF(SUM(out_subcont_qty)=0, 0, SUM(out_subcont_total)/SUM(out_subcont_qty)) AS out_subcont_price,

    -- OUT LOKAL
    COALESCE(SUM(out_lokal_qty),0) AS out_lokal_qty,
    COALESCE(SUM(out_lokal_total),0) AS out_lokal_total,
    IF(SUM(out_lokal_qty)=0, 0, SUM(out_lokal_total)/SUM(out_lokal_qty)) AS out_lokal_price,

    -- OUT IMPOR
    COALESCE(SUM(out_impor_qty),0) AS out_impor_qty,
    COALESCE(SUM(out_impor_total),0) AS out_impor_total,
    IF(SUM(out_impor_qty)=0, 0, SUM(out_impor_total)/SUM(out_impor_qty)) AS out_impor_price,

    -- OUT SAMPLE
    COALESCE(SUM(out_sample_qty),0) AS out_sample_qty,
    COALESCE(SUM(out_sample_total),0) AS out_sample_total,
    IF(SUM(out_sample_qty)=0, 0, SUM(out_sample_total)/SUM(out_sample_qty)) AS out_sample_price,

    -- OUT SAL NON GROUP
    COALESCE(SUM(out_salnongroup_qty),0) AS out_salnongroup_qty,
    COALESCE(SUM(out_salnongroup_total),0) AS out_salnongroup_total,
    IF(SUM(out_salnongroup_qty)=0, 0, SUM(out_salnongroup_total)/SUM(out_salnongroup_qty)) AS out_salnongroup_price,

    -- OUT SAL GROUP
    COALESCE(SUM(out_salgroup_qty),0) AS out_salgroup_qty,
    COALESCE(SUM(out_salgroup_total),0) AS out_salgroup_total,
    IF(SUM(out_salgroup_qty)=0, 0, SUM(out_salgroup_total)/SUM(out_salgroup_qty)) AS out_salgroup_price,

    -- OUT OTHER
    COALESCE(SUM(out_other_qty),0) AS out_other_qty,
    COALESCE(SUM(out_other_total),0) AS out_other_total,
    IF(SUM(out_other_qty)=0, 0, SUM(out_other_total)/SUM(out_other_qty)) AS out_other_price,

    -- TOTAL OUT
    COALESCE(SUM(jumlah_out_qty),0) AS jumlah_out_qty,
    COALESCE(SUM(jumlah_out_total),0) AS jumlah_out_total,
    IF(SUM(jumlah_out_qty)=0, 0, SUM(jumlah_out_total)/SUM(jumlah_out_qty)) AS jumlah_out_price

    FROM trx_out_fix
    GROUP BY id_roll),

mutasi as (select a.*, COALESCE(out_prod_qty,0) out_prod_qty,   COALESCE(out_prod_total,0) out_prod_total,   COALESCE(out_prod_price,0) out_prod_price,   COALESCE(out_subcont_qty,0) out_subcont_qty,   COALESCE(out_subcont_total,0) out_subcont_total,   COALESCE(out_subcont_price,0) out_subcont_price,   COALESCE(out_lokal_qty,0) out_lokal_qty,   COALESCE(out_lokal_total,0) out_lokal_total,   COALESCE(out_lokal_price,0) out_lokal_price,   COALESCE(out_impor_qty,0) out_impor_qty,   COALESCE(out_impor_total,0) out_impor_total,   COALESCE(out_impor_price,0) out_impor_price,   COALESCE(out_sample_qty,0) out_sample_qty,   COALESCE(out_sample_total,0) out_sample_total,   COALESCE(out_sample_price,0) out_sample_price,   COALESCE(out_salnongroup_qty,0) out_salnongroup_qty,   COALESCE(out_salnongroup_total,0) out_salnongroup_total,   COALESCE(out_salnongroup_price,0) out_salnongroup_price,   COALESCE(out_salgroup_qty,0) out_salgroup_qty,   COALESCE(out_salgroup_total,0) out_salgroup_total,   COALESCE(out_salgroup_price,0) out_salgroup_price,   COALESCE(out_other_qty,0) out_other_qty,   COALESCE(out_other_total,0) out_other_total,   COALESCE(out_other_price,0) out_other_price,   COALESCE(jumlah_out_qty,0) jumlah_out_qty,   COALESCE(jumlah_out_total,0) jumlah_out_total,   COALESCE(jumlah_out_price,0) jumlah_out_price from pemasukan_fix a left join pengeluaran_fix b on b.id_roll = a.barcode_mapping),

                            mutasi_barcode as (select *, (saldo_awal_qty + jumlah_in_qty - jumlah_out_qty) saldo_akhir_qty, (saldo_awal_total + jumlah_in_total - jumlah_out_total) saldo_akhir_total, ((saldo_awal_total + jumlah_in_total - jumlah_out_total) / (saldo_awal_qty + jumlah_in_qty - jumlah_out_qty)) saldo_akhir_price from mutasi)

                        select id_jo, id_item, goods_code, itemdesc, satuan, no_ws, SUM(saldo_awal_qty)   AS saldo_awal_qty,  SUM(saldo_awal_total) AS saldo_awal_total,  COALESCE(SUM(saldo_awal_total) / NULLIF(SUM(saldo_awal_qty),0), 0) AS saldo_awal_price,    SUM(in_lokal_qty)   AS in_lokal_qty,  SUM(in_lokal_total) AS in_lokal_total,  COALESCE(SUM(in_lokal_total) / NULLIF(SUM(in_lokal_qty),0), 0) AS in_lokal_price,    SUM(in_impor_qty)   AS in_impor_qty,  SUM(in_impor_total) AS in_impor_total,  COALESCE(SUM(in_impor_total) / NULLIF(SUM(in_impor_qty),0), 0) AS in_impor_price,    SUM(in_subcont_qty)   AS in_subcont_qty,  SUM(in_subcont_total) AS in_subcont_total,  COALESCE(SUM(in_subcont_total) / NULLIF(SUM(in_subcont_qty),0), 0) AS in_subcont_price,    SUM(in_produksi_qty)   AS in_produksi_qty,  SUM(in_produksi_total) AS in_produksi_total,  COALESCE(SUM(in_produksi_total) / NULLIF(SUM(in_produksi_qty),0), 0) AS in_produksi_price,    SUM(in_sample_qty)   AS in_sample_qty,  SUM(in_sample_total) AS in_sample_total,  COALESCE(SUM(in_sample_total) / NULLIF(SUM(in_sample_qty),0), 0) AS in_sample_price,    SUM(jumlah_in_qty)   AS jumlah_in_qty,  SUM(jumlah_in_total) AS jumlah_in_total,  COALESCE(SUM(jumlah_in_total) / NULLIF(SUM(jumlah_in_qty),0), 0) AS jumlah_in_price,    SUM(out_prod_qty)   AS out_prod_qty,  SUM(out_prod_total) AS out_prod_total,  COALESCE(SUM(out_prod_total) / NULLIF(SUM(out_prod_qty),0), 0) AS out_prod_price,    SUM(out_subcont_qty)   AS out_subcont_qty,  SUM(out_subcont_total) AS out_subcont_total,  COALESCE(SUM(out_subcont_total) / NULLIF(SUM(out_subcont_qty),0), 0) AS out_subcont_price,    SUM(out_lokal_qty)   AS out_lokal_qty,  SUM(out_lokal_total) AS out_lokal_total,  COALESCE(SUM(out_lokal_total) / NULLIF(SUM(out_lokal_qty),0), 0) AS out_lokal_price,    SUM(out_impor_qty)   AS out_impor_qty,  SUM(out_impor_total) AS out_impor_total,  COALESCE(SUM(out_impor_total) / NULLIF(SUM(out_impor_qty),0), 0) AS out_impor_price,    SUM(out_sample_qty)   AS out_sample_qty,  SUM(out_sample_total) AS out_sample_total,  COALESCE(SUM(out_sample_total) / NULLIF(SUM(out_sample_qty),0), 0) AS out_sample_price,    SUM(out_salnongroup_qty)   AS out_salnongroup_qty,  SUM(out_salnongroup_total) AS out_salnongroup_total,  COALESCE(SUM(out_salnongroup_total) / NULLIF(SUM(out_salnongroup_qty),0), 0) AS out_salnongroup_price,    SUM(out_salgroup_qty)   AS out_salgroup_qty,  SUM(out_salgroup_total) AS out_salgroup_total,  COALESCE(SUM(out_salgroup_total) / NULLIF(SUM(out_salgroup_qty),0), 0) AS out_salgroup_price,    SUM(out_other_qty)   AS out_other_qty,  SUM(out_other_total) AS out_other_total,  COALESCE(SUM(out_other_total) / NULLIF(SUM(out_other_qty),0), 0) AS out_other_price,    SUM(jumlah_out_qty)   AS jumlah_out_qty,  SUM(jumlah_out_total) AS jumlah_out_total,  COALESCE(SUM(jumlah_out_total) / NULLIF(SUM(jumlah_out_qty),0), 0) AS jumlah_out_price,    SUM(saldo_akhir_qty)   AS saldo_akhir_qty,  SUM(saldo_akhir_total) AS saldo_akhir_total,  COALESCE(SUM(saldo_akhir_total) / NULLIF(SUM(saldo_akhir_qty),0), 0) AS saldo_akhir_price from mutasi_barcode GROUP BY id_jo, id_item, satuan");


            return DataTables::of($data_inmaterial)->toJson();

    }


}
