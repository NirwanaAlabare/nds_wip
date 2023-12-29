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

class InMaterialController extends Controller
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

            if ($request->supplier != 'ALL') {
                $where = " and a.supplier = '" . $request->supplier . "' ";
            }else{
                $where = "";
            }

            if ($request->bc_type != 'ALL') {
                $where2 = " and a.type_bc = '" . $request->bc_type . "' ";
            }else{
                $where2 = "";
            }

            if ($request->pch_type != 'ALL') {
                $where3 = " and a.type_pch = '" . $request->pch_type . "' ";
            }else{
                $where3 = "";
            }

            if ($request->status != 'ALL') {
                $where4 = " and a.status = '" . $request->status . "' ";
            }else{
                $where4 = "";
            }


            $data_inmaterial = DB::connection('mysql_sb')->select("select a.*,COALESCE(qty_lok,0) qty_lok,(COALESCE(qty,0) - COALESCE(qty_lok,0)) qty_balance from (select b.id,b.no_dok,b.tgl_dok,b.tgl_shipp,b.type_dok,b.no_po,b.supplier,b.no_invoice,b.type_bc,b.no_daftar,b.tgl_daftar, b.type_pch,CONCAT(b.created_by,' (',b.created_at, ') ') user_create,b.status,sum(COALESCE(qty_good,0)) qty from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' GROUP BY b.no_dok) a left JOIN
                (select no_dok nodok,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok) b on b.nodok = a.no_dok where a.tgl_dok BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." ".$where2." ".$where3." ".$where4." order by no_dok asc");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("inmaterial.in-material", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $kode_gr = DB::connection('mysql_sb')->select("
            select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'GK/IN' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_dok) is null,'00001',LPAD(SUBSTR(MAX(no_dok),12,5)+1,5,0)) nomor from whs_inmaterial_fabric where MONTH(tgl_dok) = MONTH(CURRENT_DATE()) and YEAR(tgl_dok) = YEAR(CURRENT_DATE())) a");

        return view('inmaterial.create-inmaterial', ['kode_gr' => $kode_gr,'gr_type' => $gr_type,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
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
            <table id="datatable_list" class="table table-head-fixed table-bordered table-striped table-sm w-100 text-nowrap">
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


    public function showdetaillok(Request $request)
    {
        // $det_lokasi = DB::connection('mysql_sb')->table('whs_lokasi_inmaterial')->select('no_roll','no_lot','ROUND(qty_sj,2) qty_sj','ROUND(qty_aktual,2) qty_aktual','kode_lok')->where('status', '=', 'Y')->where('no_dok', '=', $request->no_dok)->where('no_ws', '=', $request->no_ws)->where('id_jo', '=', $request->id_jo)->where('id_item', '=', $request->id_item)->get();
        $det_lokasi = DB::connection('mysql_sb')->select("
                select no_roll,no_lot,ROUND(qty_sj,2) qty_sj,ROUND(qty_aktual,2) qty_aktual,kode_lok from whs_lokasi_inmaterial where status = 'Y' and no_dok = '" . $request->no_dok . "' and no_ws = '" . $request->no_ws . "' and id_jo = '" . $request->id_jo . "' and id_item = '" . $request->id_item . "' ");

        $html = '<div class="table-responsive" style="max-height: 200px">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 5%;">No Roll</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 29%;">Lot</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 18%;">Qty BPB</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 18%;">Qty Aktual</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 30%;">Lokasi</th>
                        <th hidden></th>
                    </tr>
                </thead>
                <tbody>';
            $jml_qty_sj = 0;
            $jml_qty_ak = 0;
        foreach ($det_lokasi as $detlok) {
            $jml_qty_sj += $detlok->qty_sj;
            $jml_qty_ak += $detlok->qty_aktual;
            $html .= ' <tr>
                        <td class="text-center">' . $detlok->no_roll . '</td>
                        <td class="text-left">' . $detlok->no_lot . '</td>
                        <td class="text-right">' . $detlok->qty_sj . '</td>
                        <td class="text-right">' . $detlok->qty_aktual . '</td>
                        <td class="text-left">' . $detlok->kode_lok . '</td>
                       </tr>';
        }

            $html .= ' <tr>
                        <td colspan="2" class="text-center"><b>Total</b></td>
                        <td class="text-right">' . $jml_qty_sj . '</td>
                        <td class="text-right">' . $jml_qty_ak . '</td>
                        <td class="text-left"></td>
                       </tr>';

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }


    public function getDetailList(Request $request)
    {
        if ($request->name_fill == 'PO') {
            $data_detail = DB::connection('mysql_sb')->select("select ms.supplier,s.id_jo,ac.kpno,d.id_item,d.goods_code,IF(d.matclass = '-',d.itemdesc,d.matclass) produk,concat(d.itemdesc,' ',d.color,' ',d.size,' ',d.add_info) itemdesc,s.qty,s.unit,s.price,s.curr,tmpbpb.qty_bpb,mp.product_group,jo_no from po_header a inner join po_item s on a.id=s.id_po inner join masteritem d on s.id_gen=d.id_gen inner join jo_det jod on s.id_jo=jod.id_jo inner join jo on jod.id_jo=jo.id inner join so on jod.id_so=so.id inner join act_costing ac on so.id_cost=ac.id inner join masterproduct mp on ac.id_product=mp.id INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier left join (select id_po_item,sum(qty)-sum(coalesce(qty_reject)) qty_bpb from bpb where pono='" . $request->txt_fill . "' group by id_po_item) tmpbpb on tmpbpb.id_po_item=s.id where a.pono='" . $request->txt_fill . "' and s.cancel='N' group by s.id order by d.id_item");
        }elseif ($request->name_fill == 'WS'){
            $data_detail = DB::connection('mysql_sb')->select(" select ms.supplier,jd.id_jo,ac.kpno,mi.id_item,mi.goods_code,IF(mi.matclass = '-',itemdesc,matclass) produk,mi.itemdesc,sum(bom.qty) qty,bom.unit from bom_jo_global_item bom INNER JOIN jo_det jd on jd.id_jo = bom.id_jo INNER JOIN so on so.id = jd.id_so INNER JOIN act_costing ac on ac.id = so.id_cost INNER JOIN mastersupplier ms on ms.id_supplier = bom.id_supplier INNER JOIN masteritem mi on mi.id_item = bom.id_item where bom.qty != 0 and ms.supplier = '" . $request->txt_supp . "' and ac.kpno = '" . $request->txt_fill . "' GROUP BY bom.id_item");
        }else{
            $data_detail = ""; 
        }

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
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

    if (intval($request['jumlah_qty']) > 0) {
        $inmaterialStore = InMaterialFabric::create([
                'no_dok' => $request['txt_gr_dok'],
                'tgl_dok' => $request['txt_tgl_gr'],
                'tgl_shipp' => $request['txt_tgl_ship'],
                'supplier' => $request['txt_supp'],
                'type_dok' => $request['txt_type_gr'],
                'no_po' => $request['txt_po'],
                'no_ws' => $request['txt_wsglobal'],
                'type_bc' => $request['txt_type_bc'],
                'type_pch' => $request['txt_type_pch'],
                'ori_dok' => '-',
                'no_invoice' => $request['txt_invdok'],
                'no_aju' => $request['txt_aju_num'],
                'tgl_aju' => $request['txt_tgl_aju'],
                'no_daftar' => $request['txt_reg_num'],
                'tgl_daftar' => $request['txt_reg_aju'],
                'no_kontrak' => $request['txt_kontrak'],
                'type_material' => 'Fabric',
                'deskripsi' => $request['txt_notes'],
                'status' => 'Pending',
                'created_by' => Auth::user()->name,
            ]);

            $timestamp = Carbon::now();
            $nodok = $request['txt_gr_dok'];
            $tgldok = $request['txt_tgl_gr'];
            $inmaterialDetailData = [];
            for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            if ($request["qty_good"][$i] > 0 || $request["qty_reject"][$i] > 0) {
                array_push($inmaterialDetailData, [
                    "no_dok" => $nodok,
                    "tgl_dok" => $tgldok,
                    "no_ws" => $request["det_kpno"][$i],
                    "id_jo" => $request["det_idjo"][$i],
                    "id_item" => $request["det_iditem"][$i],
                    "kode_item" => $request["det_code"][$i],
                    "produk_item" => $request["det_produk"][$i],
                    "desc_item" => $request["det_itemdesc"][$i],
                    "qty_po" => $request["det_qty"][$i],
                    "qty_good" => $request["qty_good"][$i],
                    "qty_reject" => $request["qty_reject"][$i],
                    "unit" => $request["det_unit"][$i],
                    "status" => 'Y',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $inmaterialDetailStore = InMaterialFabricDet::insert($inmaterialDetailData);


            $massage = $request['txt_gr_dok'] . ' Saved Succesfully';
            $stat = 200;
    }else{
        $massage = ' Please Input Data';
        $stat = 400;
    }


            return array(
                "status" =>  $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/in-material')
            );

    }

    public function savelokasi(Request $request)
    {
            $iddok = $request['txtidgr'];
        if (intval($request['ttl_qty_sj']) > 0 && intval($request['ttl_qty_sj']) <= intval($request['m_balance'])) {
            $timestamp = Carbon::now();
            $nodok = $request['m_gr_dok'];
            $nows = $request['m_no_ws'];
            $idjo = $request['m_idjo'];
            $iditem = $request['m_iditem'];
            $kodeitem = $request['m_kode_item'];
            $itemdesc = $request['m_desc'];
            $satuan = $request['m_unit'];
            $lokasiMaterial = [];
            $data_aktual = 0;
            for ($i = 1; $i <= intval($request['m_qty_det']); $i++) {
            if ($request["qty_sj"][$i] > 0) {
                // dd(intval($request["qty_ak"][$i]));
                if (intval($request["qty_ak"][$i]) == 0) {
                    $data_aktual = $request["qty_sj"][$i];
                }else{
                    $data_aktual = $request["qty_ak"][$i]; 
                }
                array_push($lokasiMaterial, [
                    "no_dok" => $nodok,
                    "no_ws" => $nows,
                    "id_jo" => $idjo,
                    "id_item" => $iditem,
                    "kode_item" => $kodeitem,
                    "item_desc" => $itemdesc,
                    "no_roll" => $request["no_roll"][$i],
                    "no_roll_buyer" => $request["roll_buyer"][$i],
                    "no_lot" => $request["no_lot"][$i],
                    "qty_sj" => $request["qty_sj"][$i],
                    "qty_aktual" => $data_aktual,
                    "satuan" => $satuan,
                    "kode_lok" => $request["selectlok"][$i],
                    "status" => 'Y',
                    "created_by" => Auth::user()->name,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $inmaterialLokasiStore = InMaterialLokasi::insert($lokasiMaterial);


            $massage = $request['m_gr_dok'] . ' Saved Location Succesfully';
            $stat = 200;
        }elseif(intval($request['ttl_qty_sj']) <= 0){
            $massage = ' Please Input Data';
            $stat = 400;
        }elseif(intval($request['ttl_qty_sj']) > intval($request['m_balance'])){
            $massage = ' Qty BPB Melebihi Qty Balance';
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
                "redirect" => url('in-material/lokasi-material/'.$iddok)
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

            $path = public_path('pdf/');
            $fileName = 'barcode-material.pdf';
            $pdf->save($path . '/' . $fileName);
            $generatedFilePath = public_path('pdf/'.$fileName);

            return response()->download($generatedFilePath);
        
    }


    public function pdfinmaterial(Request $request, $id)
    {
       
       
            $dataHeader = DB::connection('mysql_sb')->select("select * from whs_inmaterial_fabric where id = '$id' limit 1");
            $dataDetail = DB::connection('mysql_sb')->select("select a.no_dok,a.no_ws,a.desc_item,ROUND(a.qty_good,2) qty ,a.unit from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y'");
            $dataSum = DB::connection('mysql_sb')->select("select sum(qty) qty_all from (select a.no_dok,a.no_ws,a.desc_item,ROUND(a.qty_good,2) qty ,a.unit from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y') a");
            $dataUser = DB::connection('mysql_sb')->select("select created_by,created_at,approved_by,approved_date from whs_inmaterial_fabric where id = '$id' limit 1");
            $dataHead = DB::connection('mysql_sb')->select("select CONCAT('Bandung, ',DATE_FORMAT(a.tgl_dok,'%d %b %Y')) tgl_dok,a.supplier,b.alamat, CURRENT_TIMESTAMP() tgl_cetak from whs_inmaterial_fabric a inner join mastersupplier b on b.supplier = a.supplier where id = '$id' and b.tipe_sup = 'S' limit 1");


            PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
            $pdf = PDF::loadView('inmaterial.pdf.print-pdf', ["dataHeader" => $dataHeader,"dataDetail" => $dataDetail,"dataSum" => $dataSum,"dataUser" => $dataUser,"dataHead" => $dataHead])->setPaper('a4', 'potrait');

            $path = public_path('pdf/');
            $fileName = 'pdf-material.pdf';
            $pdf->save($path . '/' . $fileName);
            $generatedFilePath = public_path('pdf/'.$fileName);

            return response()->download($generatedFilePath);
        
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
