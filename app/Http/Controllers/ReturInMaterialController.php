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
use App\Models\InMaterialLokTemp;
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

class ReturInMaterialController extends Controller
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

            if ($request->status != 'ALL') {
                $where3 = " and a.status = '" . $request->status . "' ";
            }else{
                $where3 = "";
            }


            $data_inmaterial = DB::connection('mysql_sb')->select("select a.*,COALESCE(qty_lok,0) qty_lok,(COALESCE(qty,0) - COALESCE(qty_lok,0)) qty_balance from (select b.jns_retur,b.type_material,a.no_ws,b.id,b.no_dok,b.tgl_dok,b.tgl_shipp,b.type_dok,b.no_po,b.supplier,b.no_invoice,b.type_bc,b.no_daftar,b.tgl_daftar, b.type_pch,CONCAT(b.created_by,' (',b.created_at, ') ') user_create,b.status,sum(COALESCE(qty_good,0)) qty from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and b.no_dok like '%RI%' GROUP BY b.no_dok) a left JOIN
                (select no_dok nodok,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok) b on b.nodok = a.no_dok where a.tgl_dok BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." ".$where2." ".$where3." order by no_dok asc");


            return DataTables::of($data_inmaterial)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("retur_inmaterial.retur-inmaterial", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'Status KB In')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode FROM (
        SELECT 'GK/RI' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,IF(MAX(bpbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bpbno_int,12,5))+1,5,0)) nomor FROM bpb WHERE MONTH(bpbdate) = MONTH(CURRENT_DATE()) AND YEAR(bpbdate) = YEAR(CURRENT_DATE()) AND LEFT(bpbno_int,2) = 'GK') a");

        return view('retur_inmaterial.create-retur-inmaterial', ['kode_gr' => $kode_gr,'gr_type' => $gr_type,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
    }

    public function getNobppb(Request $request)
    {
        $nomorbppb = DB::connection('mysql_sb')->select("select a.*,(a.qty - COALESCE(qty_ri,0)) qty_sisa from (select bppbno isi,concat(if(bppbno_int!='',bppbno_int,bppbno),'|',supplier) tampil, sum(a.qty) qty from 
            bppb a inner join mastersupplier s on a.id_supplier=s.id_supplier 
            left join so_det sod on a.id_so_det=sod.id 
            left join jo_det jod on sod.id_so=jod.id_so 
            left join jo on jo.id=jod.id_jo  
            where bppbdate = '" . $request->tgl_ri . "' and LEFT(bppbno_int,2) = 'GK'
            group by bppbno order by bppbno) a left join (select b.ori_dok,a.id_jo,a.id_item,a.no_ws,sum(COALESCE(qty_good,0) - COALESCE(qty_reject,0)) qty_ri from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and ori_dok != '-' GROUP BY b.ori_dok) b on b.ori_dok = a.isi where (a.qty - COALESCE(qty_ri,0)) > 0");

        $html = "<option value=''>Pilih No SJ</option>";

        foreach ($nomorbppb as $nobppb) {
            $html .= " <option value='" . $nobppb->isi . "'>" . $nobppb->tampil . "</option> ";
        }

        return $html;
    }

    public function getTujuan(Request $request)
    {
        $tujuan = DB::connection('mysql_sb')->select("select nama_pilihan isi,nama_pilihan tampil 
            from masterpilihan where kode_pilihan = '" . $request->type_bc . "' ");

        $html = "<option value=''>Pilih Tujuan</option>";

        foreach ($tujuan as $tuj) {
            $html .= " <option value='" . $tuj->isi . "'>" . $tuj->tampil . "</option> ";
        }

        return $html;
    }


    public function getSuppri(Request $request)
    {
        
        $supplier = DB::connection('mysql_sb')->select("select a.id_supplier,s.supplier from bppb a inner join mastersupplier s on a.id_supplier=s.id_supplier where bppbno ='" . $request->no_bppb . "'");

        return $supplier;
    }

    public function getListBppb(Request $request)
    {

            $data_detail = DB::connection('mysql_sb')->select("select a.id_bppb,a.id_so_det,a.id_jo,a.id_item,a.kpno,a.goods_code,a.itemdesc,a.color,(a.qty - COALESCE(qty_ri,0)) qty, a.unit,a.confirm from (select bppbno,a.id id_bppb,a.id_so_det,a.id_jo,a.id_item,ac.kpno,s.goods_code,s.itemdesc itemdesc,s.color,a.qty, a.unit,a.confirm from bppb a inner join masteritem s on a.id_item=s.id_item inner join jo_det jd on a.id_jo = jd.id_jo inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where bppbno='" . $request->sj_asal . "' order by a.id_item desc) a left join (select b.ori_dok,a.id_jo,a.id_item,a.no_ws,sum(COALESCE(qty_good,0) - COALESCE(qty_reject,0)) qty_ri from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and b.ori_dok = '" . $request->sj_asal . "') b on b.ori_dok = a.bppbno and b.id_item = a.id_item and b.id_jo = a.id_jo");
        

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }


    public function store(Request $request)
    {

    // if (intval($request['jumlah_qty']) > 0) {

        $tglbpb = $request['txt_tgl_ri'];
        $Mattype1 = DB::connection('mysql_sb')->select("select CONCAT('GK-IN-', DATE_FORMAT('" . $tglbpb . "', '%Y')) Mattype,IF(MAX(bpbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bpbno_int,12,5))+1,5,0)) nomor,CONCAT('GK/RI/',DATE_FORMAT('" . $tglbpb . "', '%m'),DATE_FORMAT('" . $tglbpb . "', '%y'),'/',IF(MAX(bpbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bpbno_int,12,5))+1,5,0))) bpbno_int FROM bpb WHERE MONTH(bpbdate) = MONTH('" . $tglbpb . "') AND YEAR(bpbdate) = YEAR('" . $tglbpb . "') AND LEFT(bpbno_int,2) = 'GK'");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
        $m_type = $Mattype1[0]->Mattype;
        $no_type = $Mattype1[0]->nomor;
        $bpbno_int = $Mattype1[0]->bpbno_int;

        $cek_mattype = DB::connection('mysql_sb')->select("select * from tempbpb where Mattype = '" . $m_type . "'");
        $hasilcek = $cek_mattype ? $cek_mattype[0]->Mattype : 0;

        $Mattype2 = DB::connection('mysql_sb')->select("select 'RI.F' Mattype, IF(MAX(bpbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bpbno,2,5))+1,5,0)) nomor, CONCAT('F', IF(MAX(bpbno_int) IS NULL,'00001',LPAD(MAX(SUBSTR(bpbno,2,5))+1,5,0)),'-R') bpbno FROM bpb WHERE LEFT(bpbno_int,5) = 'GK/RI'");
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

            $timestamp = Carbon::now();
            // $nodok = $request['txt_no_ri'];
            $tgl_ri = $request['txt_tgl_ri'];
            $tgl_sj = $request['txt_tgl_sj'];
            $no_pengeluaran = $request['txt_sj_asal'];
            $supplier_name = $request['txt_supp'];
            $supplier_id = $request['txt_idsupp'];
            $jenis_retur = $request['txt_jns_rtr'];
            $type_bc = $request['txt_type_bc'];
            $tujuan_pemasukan = $request['txt_tujuan'];
            $no_kkbc = $request['txt_no_kk'];
            $no_aju = $request['txt_aju_num'];
            $tgl_aju = $request['txt_tgl_aju'];
            $no_faktur = $request['txt_faktur'];
            $tgl_faktur = $request['txt_tgl_faktur'];
            $no_reg = $request['txt_reg'];
            $tgl_reg = $request['txt_tgl_reg'];
            $no_invoice = $request['txt_noinvoice'];
            $tipe_material = $request['txt_tom'];
            $inmaterialDetailData = [];
            for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            if ($request["qty_retur"][$i] > 0 || $request["qty_reject"][$i] > 0) {
                    $detdata = DB::connection('mysql_sb')->select("select * from bppb where id ='" . $request["id_bppb"][$i] . "' ");
                    
                    $txtid_item_fg = $detdata[0]->id_item_fg;
                    $txtunit = $detdata[0]->unit;
                    $txtcurr = $detdata[0]->curr;
                    $txtprice = $detdata[0]->price;
                    $txtid_supplier = $detdata[0]->id_supplier;
                    $txtid_gudang = $detdata[0]->id_gudang;
                    $txtid_so_det = $detdata[0]->id_so_det;
                
                // dd($detdata);
                array_push($inmaterialDetailData, [
                    "id_item" => $request["id_item"][$i],
                    "id_item_fg" => $txtid_item_fg,
                    "qty" => '0',
                    "qty_temp" => $request["qty_retur"][$i],
                    "unit" => $txtunit,
                    "curr" => $txtcurr,
                    "price" => $txtprice,
                    "remark" => $request["keterangan"][$i],
                    "jam_masuk" => '',
                    "berat_bersih" => $request["bruto"][$i],
                    "berat_kotor" => $request["neto"][$i],
                    "nomor_mobil" => '',
                    "pono" => '',
                    "id_supplier" => $supplier_id,
                    "invno" => $no_invoice,
                    "bcno" => $no_reg,
                    "bcdate" => $tgl_reg,
                    "bpbno" => $bpbno,
                    "bpbno_int" => $bpbno_int,
                    "bpbdate" => $tgl_ri,
                    "jenis_dok" => $type_bc,
                    "tujuan" => $tujuan_pemasukan,
                    "username" => Auth::user()->name,
                    "use_kite" => '1',
                    "nomor_aju" => $no_aju,
                    "tanggal_aju" => $tgl_aju,
                    "kpno" => $request["no_ws"][$i],
                    "id_gudang" => $txtid_gudang,
                    "nomor_rak" => '',
                    "status_retur" => 'Y',
                    "bppbno_ri" => $no_pengeluaran ,
                    "bppbno" => $no_pengeluaran ,
                    "id_jo" => $request["id_jo"][$i],
                    "id_so_det" => $txtid_so_det,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $cari_supp = DB::connection('mysql_sb')->select("select Supplier from mastersupplier where Id_Supplier = '12'");
            $Supplier = $cari_supp[0]->Supplier;

            $inmaterialDetailStore = Bpb::insert($inmaterialDetailData);


                $inmaterialStore2 = InMaterialFabric::create([
                'no_dok' => $bpbno_int,
                'tgl_dok' => $request['txt_tgl_ri'],
                'tgl_shipp' => $request['txt_tgl_sj'],
                'supplier' => $request['txt_supp'],
                'type_dok' => '',
                'no_po' => '',
                'no_ws' => '',
                'type_bc' => $request['txt_type_bc'],
                'type_pch' => $request['txt_tujuan'],
                'ori_dok' => $request['txt_sj_asal'],
                'no_invoice' => $request['txt_noinvoice'],
                'no_aju' => $request['txt_aju_num'],
                'tgl_aju' => $request['txt_tgl_aju'],
                'no_daftar' => $request['txt_reg'],
                'tgl_daftar' => $request['txt_tgl_reg'],
                'no_kontrak' => $request['txt_no_kk'],
                'type_material' => 'Fabric',
                'deskripsi' => '',
                'status' => 'Pending',
                'created_by' => Auth::user()->name,
                'jns_retur' => $request['txt_jns_rtr'],
                'no_faktur' => $request['txt_faktur'],
                'tgl_faktur' => $request['txt_tgl_faktur'],
            ]);

            $inmaterialDetailData2 = [];
            for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            if ($request["qty_retur"][$i] > 0 || $request["qty_reject"][$i] > 0) {
                 $detdata_whs = DB::connection('mysql_sb')->select("select a.id,a.curr,a.price,a.id_jo,a.id_item,ac.kpno,s.goods_code,s.itemdesc itemdesc,s.color,a.qty, a.unit,a.confirm,s.matclass produk from bppb a inner join masteritem s on a.id_item=s.id_item inner join jo_det jd on a.id_jo = jd.id_jo inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id  where a.id='" . $request["id_bppb"][$i] . "' order by a.id_item desc ");
                    
                    $whs_goods_code = $detdata_whs[0]->goods_code;
                    $whs_itemdesc = $detdata_whs[0]->itemdesc;
                    $whs_unit = $detdata_whs[0]->unit;
                    $whs_produk = $detdata_whs[0]->produk;
                    $whs_curr = $detdata_whs[0]->curr;
                    $whs_price = $detdata_whs[0]->price;

                array_push($inmaterialDetailData2, [
                    "no_dok" => $bpbno_int,
                    "tgl_dok" => $tgl_ri,
                    "no_ws" => $request["no_ws"][$i],
                    "id_jo" => $request["id_jo"][$i],
                    "id_item" => $request["id_item"][$i],
                    "kode_item" => $whs_goods_code,
                    "produk_item" => $whs_produk,
                    "desc_item" => $whs_itemdesc,
                    "qty_po" => '0',
                    "qty_good" => $request["qty_retur"][$i],
                    "qty_reject" => $request["qty_reject"][$i],
                    "unit" => $whs_unit,
                    "curr" => $whs_curr,
                    "price" => $whs_price,
                    "status" => 'Y',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            $inmaterialDetailStore2 = InMaterialFabricDet::insert($inmaterialDetailData2);


            $massage = $bpbno_int . ' Saved Succesfully';
            $stat = 200;
    // }else{
    //     $massage = ' Please Input Data';
    //     $stat = 400;
    // }


            return array(
                "status" =>  $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/retur-inmaterial')
            );

    }


    public function lokreturmaterial($id)
    {

        $kode_gr = DB::connection('mysql_sb')->select("select * from whs_inmaterial_fabric where id = '$id'");
        $det_data = DB::connection('mysql_sb')->select("select *, round((a.qty_good - COALESCE(b.qty_lok,0)),2) qty_sisa  from (select a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '$id' and a.status = 'Y') a left join
(select no_dok nodok, no_ws ws,id_jo jo_id,id_item item_id,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok,no_ws,id_item,id_jo) b on b.nodok = a.no_dok and b.ws = a.no_ws and b.jo_id = a.id_jo and b.item_id = a.id_item");

        $no_bppb = DB::connection('mysql_sb')->table('bppb')->select('bppbno_int')->where('bppbno', '=', $kode_gr[0]->ori_dok)->get();

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->where('Supplier', '!=', $kode_gr[0]->supplier)->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->where('nama_pilihan', '!=', $kode_gr[0]->type_bc)->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('nama_pilihan', '!=', $kode_gr[0]->type_pch)->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('nama_pilihan', '!=', $kode_gr[0]->type_dok)->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $lokasi = DB::connection('mysql_sb')->select("select a.id,a.kode_lok, CONCAT(a.kode_lok,' (Used ',COALESCE(qty,0),' Of ',kapasitas,')') lokasi,a.kapasitas,COALESCE(qty,0) qty_used from (select id,kode_lok,kapasitas from whs_master_lokasi) a left join (select COUNT(id) qty,kode_lok from (select id,kode_lok from whs_lokasi_inmaterial where status = 'Y') a GROUP BY kode_lok) b on b.kode_lok = a.kode_lok ORDER BY kode_lok asc");

        return view('retur_inmaterial.lokasi-inmaterial', ['no_bppb' => $no_bppb,'det_data' => $det_data,'kode_gr' => $kode_gr,'gr_type' => $gr_type,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit,'lokasi' => $lokasi, 'page' => 'dashboard-warehouse']);
    }


    public function savelokasiretur(Request $request)
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
                $sql_barcode = DB::connection('mysql_sb')->select("select CONCAT('F',(if(kode is null,'19999',kode)  + 1)) kode from (select max(cast(SUBSTR(no_barcode,2,10) as SIGNED)) kode from whs_lokasi_inmaterial where no_barcode like '%F%') a");
            $barcode = $sql_barcode[0]->kode;

                $save_lokasi = InMaterialLokasi::create([
                    "no_barcode" => $barcode,
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

            // $inmaterialLokasiStore = InMaterialLokasi::insert($lokasiMaterial);


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
                "redirect" => url('retur-inmaterial/lokasi-retur-material/'.$iddok)
            );

    }


    public function UploadLokasiRetur($id)
    {

        $data_head = DB::connection('mysql_sb')->select("select id_dok,id,no_dok,no_ws,id_jo,id_item,kode_item,produk_item,desc_item,qty_good qty,unit,COALESCE(qty_lok,0) qty_lok,qty_sisa from (select *, (a.qty_good - COALESCE(b.qty_lok,0)) qty_sisa  from (select b.id id_dok,a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.id = '$id' and a.status = 'Y') a left join
        (select no_dok nodok, no_ws ws,id_jo jo_id,id_item item_id,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok,no_ws,id_item,id_jo) b on b.nodok = a.no_dok and b.ws = a.no_ws and b.jo_id = a.id_jo and b.item_id = a.id_item) a");

        $det_data = DB::connection('mysql_sb')->select("select *, (a.qty_good - COALESCE(b.qty_lok,0)) qty_sisa  from (select a.* from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where b.id = '8' and a.status = 'Y') a left join
        (select no_dok nodok, no_ws ws,id_jo jo_id,id_item item_id,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok,no_ws,id_item,id_jo) b on b.nodok = a.no_dok and b.ws = a.no_ws and b.jo_id = a.id_jo and b.item_id = a.id_item");

        $sum_data = DB::connection('mysql_sb')->select("select sum(qty_bpb) qty from whs_lokasi_material_temp where kode_lok != 'kode_lok' and created_by = '".Auth::user()->name."'");
        $count_data = DB::connection('mysql_sb')->select("select COUNT(qty_bpb) qty from (select * from whs_lokasi_material_temp where kode_lok != 'kode_lok' and created_by = '".Auth::user()->name."') a");

        return view('retur_inmaterial.upload-lokasi', ['det_data' => $det_data,'data_head' => $data_head,'sum_data' => $sum_data,'count_data' => $count_data, 'page' => 'dashboard-warehouse']);
    }


    public function saveuploadlokasirtr(Request $request)
    {
            $iddok = $request['txt_idgr'];
        // if (intval($request['qty_upload']) > 0 && intval($request['qty_upload']) <= intval($request['qty_bal'])) {
            $timestamp = Carbon::now();
            $nodok = $request['txt_gr_dok'];
            $nows = $request['m_no_ws'];
            $idjo = $request['txt_idjo'];
            $iditem = $request['txt_iditem'];
            $kodeitem = $request['m_kode_item'];
            $itemdesc = $request['txt_desc'];
            $satuan = $request['txt_unit'];
            $lokasiMaterial = [];
            $data_aktual = 0;
            for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            if ($request["qty_bpb"][$i] > 0) {
                // dd(intval($request["qty_ak"][$i]));
                if (intval($request["qty_aktual"][$i]) == 0) {
                    $data_aktual = $request["qty_bpb"][$i];
                }else{
                    $data_aktual = $request["qty_aktual"][$i]; 
                }
                $sql_barcode = DB::connection('mysql_sb')->select("select CONCAT('F',(if(kode is null,'19999',kode)  + 1)) kode from (select max(cast(SUBSTR(no_barcode,2,10) as SIGNED)) kode from whs_lokasi_inmaterial where no_barcode like '%F%') a");
            $barcode = $sql_barcode[0]->kode;

                $save_lokasi = InMaterialLokasi::create([
                    "no_barcode" => $barcode,
                    "no_dok" => $nodok,
                    "no_ws" => $nows,
                    "id_jo" => $idjo,
                    "id_item" => $iditem,
                    "kode_item" => $kodeitem,
                    "item_desc" => $itemdesc,
                    "no_roll" => $request["no_roll"][$i],
                    "no_roll_buyer" => $request["no_roll_buyer"][$i],
                    "no_lot" => $request["no_lot"][$i],
                    "qty_sj" => $request["qty_bpb"][$i],
                    "qty_aktual" => $data_aktual,
                    "satuan" => $satuan,
                    "kode_lok" => $request["kode_lok"][$i],
                    "status" => 'Y',
                    "created_by" => Auth::user()->name,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }
            }

            // $inmaterialLokasiStore = InMaterialLokasi::insert($lokasiMaterial);


            $delete_temp = InMaterialLokTemp::where('created_by',Auth::user()->name)->delete();


            $massage = $request['txt_gr_dok'] . ' Saved Location Succesfully';
            $stat = 200;
        // }elseif(intval($request['qty_upload']) <= 0){
        //     $massage = ' Please Input Data';
        //     $stat = 400;
        // }elseif(intval($request['qty_upload']) > intval($request['qty_bal'])){
        //     $massage = ' Qty BPB Melebihi Qty Balance';
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
                "redirect" => url('retur-inmaterial/lokasi-retur-material/'.$iddok)
            );

    }


    public function approvematerialretur(Request $request)
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
                "redirect" => url('/retur-inmaterial')
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
