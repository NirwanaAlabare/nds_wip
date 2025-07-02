<?php

namespace App\Http\Controllers;

use App\Models\QcInspect;
use App\Models\MasterLokasi;
use App\Models\UnitLokasi;
use App\Models\DefectTemp;
use App\Models\QcDefectTemp;
use App\Models\QcInspectTemp;
use App\Models\QcInspectSum;
use App\Models\QcInspectDet;
use App\Models\QcDefectDef;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanQcpass;
use DB;
use QrCode;
use DNS1D;
use PDF;

class QcPassController extends Controller
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
            $additionalQuery .= " where a.tgl_insp BETWEEN '" . $request->tglawal . "' and '" . $request->tglakhir . "' ";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.no_insp like '%" . $request->search["value"] . "%' OR
                        a.tgl_insp like '%" . $request->search["value"] . "%' OR
                        a.no_style like '%" . $request->search["value"] . "%' OR
                        a.buyer like '%" . $request->search["value"] . "%' OR
                        a.fabric_name like '%" . $request->search["value"] . "%' OR
                    )
                ";
            }



            $data_m_lokasi = DB::connection('mysql_sb')->select("
            select * from (select * from whs_qc_insp) a LEFT JOIN
(select no_insp noinsp, GROUP_CONCAT(inspektor)inspektr from (select DISTINCT no_insp,inspektor from whs_qc_insp_det) a GROUP BY no_insp) b on b.noinsp = a.no_insp
                " . $additionalQuery . "
                " . $keywordQuery . "
            ");


             return DataTables::of($data_m_lokasi)->toJson();
        }

        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        $kode_ins = DB::connection('mysql_sb')->select("
            select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'INS' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_insp) is null,'00001',LPAD(SUBSTR(MAX(no_insp),10,5)+1,5,0)) nomor from whs_qc_insp where MONTH(tgl_insp) = MONTH(CURRENT_DATE()) and YEAR(tgl_insp) = YEAR(CURRENT_DATE())) a");

        return view("qc-pass.qc-pass", ['kode_ins' => $kode_ins,'arealok' => $arealok,'unit' => $unit,"page" => "dashboard-warehouse"]);
    }


    public function getListItem(Request $request)
    {
//         $data = DB::connection('mysql_sb')->select("
//                 select DISTINCT mi.id_item,mi.color,mi.itemdesc,ac.styleno,lot_no,supplier from bpb_roll_h a
// inner join bpb_roll b on a.id = b.id_h
// inner join (select bpbno,bpbno_int,bpbdate,b.supplier from bpb a INNER JOIN mastersupplier b on b.id_supplier = a.id_supplier where b.tipe_sup = 'S' group by bpbno) bpb on a.bpbno = bpb.bpbno
// inner join masteritem mi on a.id_item = mi.id_item
// inner join jo_det jd on a.id_jo = jd.id_jo
// inner join so on jd.id_so = so.id
// inner join act_costing ac on so.id_cost = ac.id
// where mi.id_item = '" . $request->id_item . "'
// order by bpbdate asc limit 1
//             ");

         $data = DB::connection('mysql_sb')->select("
         select DISTINCT mi.id_item,mi.color,mi.itemdesc,ac.styleno,no_lot lot_no,supplier from whs_inmaterial_fabric b INNER JOIN whs_lokasi_inmaterial a on a.no_dok = b.no_dok
         inner join masteritem mi on a.id_item = mi.id_item
         inner join jo_det jd on a.id_jo = jd.id_jo
         inner join so on jd.id_so = so.id
         inner join act_costing ac on so.id_cost = ac.id
         where a.no_barcode = '" . $request->id_item . "'
         order by tgl_dok asc limit 1");

        return $data;
    }


    public function getListItem2(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("
                select DISTINCT mi.id_item,mi.color,mi.itemdesc,ac.styleno,lot_no,supplier from bpb_roll_h a
inner join bpb_roll b on a.id = b.id_h
inner join (select bpbno,bpbno_int,bpbdate,b.supplier from bpb a INNER JOIN mastersupplier b on b.id_supplier = a.id_supplier where b.tipe_sup = 'S' group by bpbno) bpb on a.bpbno = bpb.bpbno
inner join masteritem mi on a.id_item = mi.id_item
inner join jo_det jd on a.id_jo = jd.id_jo
inner join so on jd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
where mi.id_item = '" . $request->id_item . "'
order by bpbdate asc limit 1
            ");

        return $data;
    }

    public function getdefect(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("
                select GROUP_CONCAT(kode) kode_def from whs_defect_temp where user_created = '".Auth::user()->name."'
            ");

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $gr_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Type_penerimaan')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();
        $lenght_qc = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Lenght_qc_pass')->where('status', '=', 'Active')->get();

        $status_insp = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'status_inspection')->where('status', '=', 'Active')->get();

        $kode_insp = DB::connection('mysql_sb')->select("select no_insp from whs_qc_insp where id = '".$id."'");

        $no_form = DB::connection('mysql_sb')->select("
            select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'FRM' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_form) is null,'00001',LPAD(SUBSTR(MAX(no_form),10,5)+1,5,0)) nomor from whs_qc_insp_det where MONTH(tgl_form) = MONTH(CURRENT_DATE()) and YEAR(tgl_form) = YEAR(CURRENT_DATE())) a");

        $formke = DB::connection('mysql_sb')->select("
            select (COUNT(no_form) + 1) ttl_form from (select DISTINCT b.no_form from whs_qc_insp a inner join whs_qc_insp_det b on b.no_insp = a.no_insp where a.id = '".$id."') a");

        $defect = DB::connection('mysql_sb')->table('whs_master_defect')->select('id', 'kategori', 'kode', 'nama_defect')->where('status', '=', 'active')->get();

        return view('qc-pass.create-qcpass', ['status_insp' => $status_insp,'formke' => $formke,'defect' => $defect,'no_form' => $no_form,'kode_insp' => $kode_insp,'gr_type' => $gr_type,'pch_type' => $pch_type,'lenght_qc' => $lenght_qc,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit, 'page' => 'dashboard-warehouse']);
    }

    public function getnoform(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'FRM' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_form) is null,'00001',LPAD(SUBSTR(MAX(no_form),10,5)+1,5,0)) nomor from whs_qc_insp_det where MONTH(tgl_form) = MONTH(CURRENT_DATE()) and YEAR(tgl_form) = YEAR(CURRENT_DATE())) a
            ");

        return $data;
    }


    public function showdata($id)
    {


        $kode_insp = DB::connection('mysql_sb')->select("select no_insp from whs_qc_insp where id = '".$id."'");
        $data_header = DB::connection('mysql_sb')->select("select *,UPPER(fabric_name) fabricname from whs_qc_insp where id = '".$id."'");
        $data_detail = DB::connection('mysql_sb')->select("select b.id,b.no_lot,a.no_form,a.tgl_form,a.weight_fabric,width_fabric,gramage,a.no_roll,fabric_supp,a.inspektor,no_mesin,c.lenght_barcode, lenght_actual, catatan from whs_qc_insp_det a inner join whs_qc_insp b on b.no_insp = a.no_insp inner join whs_qc_insp_sum c on c.no_form = a.no_form where b.id = '".$id."' GROUP BY a.no_roll,a.no_form order by a.no_form asc");
        $data_temuan = DB::connection('mysql_sb')->select("select * from (select id,no_form,lenght_fabric,GROUP_CONCAT(kode_def) kode_def,GROUP_CONCAT(nama_defect) nama_defect,GROUP_CONCAT(ROUND(upto3,0)) upto3,GROUP_CONCAT(ROUND(over3,0)) over3,GROUP_CONCAT(ROUND(over6,0)) over6,GROUP_CONCAT(ROUND(over9,0)) over9,GROUP_CONCAT(width_det) width_det from (select DISTINCT a.id,b.no_form,lenght_fabric,kode_def,CONCAT('(',UPPER(c.nama_defect),')') nama_defect,upto3, over3, over6, over9,CONCAT(width_det1,'->',width_det2) width_det  from whs_qc_insp a inner join whs_qc_insp_det b on b.no_insp = a.no_insp left join whs_qc_insp_def c on c.kode = b.kode_def and c.no_form = b.no_form and c.lenght = b.lenght_fabric where a.id = '".$id."') a GROUP BY lenght_fabric,no_form order by no_form asc, lenght_fabric asc) a left join (select id id_pil,nama_pilihan from whs_master_pilihan where type_pilihan = 'Lenght_qc_pass' and status = 'Active') b on b.nama_pilihan = a.lenght_fabric order by no_form asc,id_pil asc");
        $data_sum = DB::connection('mysql_sb')->select("select no_form,upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*,b.*,c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '".$id."'");
        $avg_poin = DB::connection('mysql_sb')->select("select ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) avg_poin,IF(ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) > 15,'-','PASS') status from (select sum(ttl_poin) ttl_poin, COUNT(width_fabric)ttl_width, SUM(width_fabric) akt_width, SUM(l_actual) akt_lenght from (select upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*, b.*, c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '".$id."') a) a");


        return view('qc-pass.show-qcpass', ['kode_insp' => $kode_insp,'data_header' => $data_header,'data_detail' => $data_detail,'data_temuan' => $data_temuan,'data_sum' => $data_sum,'avg_poin' => $avg_poin, 'page' => 'dashboard-warehouse']);
    }

    public function exportdata($id){
        // return Excel::download(new ExportLaporanQcpass($id), 'Laporan_qcpass.xlsx');
        $kode_insp = DB::connection('mysql_sb')->select("select no_insp from whs_qc_insp where id = '".$id."'");
        $data_header = DB::connection('mysql_sb')->select("select *,UPPER(fabric_name) fabricname from whs_qc_insp where id = '".$id."'");
        $data_detail = DB::connection('mysql_sb')->select("select b.id,b.no_lot,a.no_form,a.tgl_form,a.weight_fabric,width_fabric,gramage,a.no_roll,fabric_supp,a.inspektor,no_mesin,c.lenght_barcode, lenght_actual, catatan from whs_qc_insp_det a inner join whs_qc_insp b on b.no_insp = a.no_insp inner join whs_qc_insp_sum c on c.no_form = a.no_form where b.id = '".$id."' GROUP BY a.no_roll,a.no_form order by a.no_form asc");
        $data_temuan = DB::connection('mysql_sb')->select("select * from (select id,no_form,lenght_fabric,GROUP_CONCAT(kode_def) kode_def,GROUP_CONCAT(nama_defect) nama_defect,GROUP_CONCAT(ROUND(upto3,0)) upto3,GROUP_CONCAT(ROUND(over3,0)) over3,GROUP_CONCAT(ROUND(over6,0)) over6,GROUP_CONCAT(ROUND(over9,0)) over9,GROUP_CONCAT(width_det) width_det from (select DISTINCT a.id,b.no_form,lenght_fabric,kode_def,CONCAT('(',UPPER(c.nama_defect),')') nama_defect,upto3, over3, over6, over9,CONCAT(width_det1,'->',width_det2) width_det  from whs_qc_insp a inner join whs_qc_insp_det b on b.no_insp = a.no_insp left join whs_qc_insp_def c on c.kode = b.kode_def and c.no_form = b.no_form and c.lenght = b.lenght_fabric where a.id = '".$id."') a GROUP BY lenght_fabric,no_form order by no_form asc, lenght_fabric asc) a left join (select id id_pil,nama_pilihan from whs_master_pilihan where type_pilihan = 'Lenght_qc_pass' and status = 'Active') b on b.nama_pilihan = a.lenght_fabric order by no_form asc,id_pil asc");
        $data_sum = DB::connection('mysql_sb')->select("select no_form,upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*,b.*,c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '".$id."'");
        $avg_poin = DB::connection('mysql_sb')->select("select ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) avg_poin,IF(ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) > 15,'-','PASS') status from (select sum(ttl_poin) ttl_poin, COUNT(width_fabric)ttl_width, SUM(width_fabric) akt_width, SUM(l_actual) akt_lenght from (select upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*, b.*, c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '".$id."') a) a");

        // PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('qc-pass.pdf.pdf-qcpass', ['kode_insp' => $kode_insp,'data_header' => $data_header,'data_detail' => $data_detail,'data_temuan' => $data_temuan,'data_sum' => $data_sum,'avg_poin' => $avg_poin])->setPaper('a4', 'potrait');

        // $pdf = PDF::loadView('master.pdf.print-lokasi', ["dataLokasi" => $dataLokasi]);

        $fileName = 'pdf.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }


    public function updatestatus(Request $request)
    {

        $id = $request['id_lok'];
        $status = $request['status_lok'];
        if ($status == 'Active') {
            $updateLokasi = MasterLokasi::where('id', $request['id_lok'])->update([
                'status' => 'Deactive'
            ]);
        }else{
            $updateLokasi = MasterLokasi::where('id', $request['id_lok'])->update([
                'status' => 'Active'
            ]);
        }

        $massage = 'Change Status Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/master-lokasi')
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
        // $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
        // $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
        // $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);
        // $totalQty = 0;

        $validatedRequest = $request->validate([
            "txt_no_ins" => "required",
            "tgl_ins" => "required",
            "txt_id_item" => "required",
            "txt_style" => "required",
            "txt_color" => "required",
            "txt_lot" => "required",
            "txt_buyer" => "required",
            "txt_fab_name" => "required",
        ]);

         $kodeins = DB::connection('mysql_sb')->select("
            select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'INS' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_insp) is null,'00001',LPAD(SUBSTR(MAX(no_insp),10,5)+1,5,0)) nomor from whs_qc_insp where MONTH(tgl_insp) = MONTH(CURRENT_DATE()) and YEAR(tgl_insp) = YEAR(CURRENT_DATE())) a");
         $kode_ins = $kodeins ? $kodeins[0]->kode : null;


        $timestamp = Carbon::now();
        $nomor = $kode_ins;
        //$validatedRequest['txt_no_ins']

            $inspectStore = QcInspect::create([
                'no_insp' => $kode_ins,
                'tgl_insp' => $validatedRequest['tgl_ins'],
                'id_item' => $validatedRequest['txt_id_item'],
                'no_style' => $validatedRequest['txt_style'],
                'color' => $validatedRequest['txt_color'],
                'no_lot' => $validatedRequest['txt_lot'],
                'buyer' => $validatedRequest['txt_buyer'],
                'fabric_name' => $validatedRequest['txt_fab_name'],
                'inspektor' => '-',
                'status' => '-',
                'created_by' => Auth::user()->name,
                ]);

            $massage = 'Inspection Number ' . $nomor . ' Saved Succesfully';
            $massage2 = $nomor;

            $cariid = DB::connection('mysql_sb')->select("
            select id from whs_qc_insp where no_insp = '".$nomor."'");
            $id_ins = $cariid ? $cariid[0]->id : null;

            return array(
                "status" => 200,
                "message" => $massage,
                "message2" => $massage2,
                "additional" => [],
                "redirect" => url('qc-pass/create-qcpass/'.$id_ins)
            );

    }

    public function storedefect(Request $request)
    {
        $delete_tempdef = DefectTemp::where('user_created',Auth::user()->name)->delete();
        $timestamp = Carbon::now();
        $DefectTempData = [];
        for ($i = 1; $i < 21; $i++) {
            $check = isset($request['pilih_def'][$i]) ? $request['pilih_def'][$i] : 0;
            if ($check > 0) {
                array_push($DefectTempData, [
                    "kode" => $request["kode_def"][$i],
                    "nama_defect" => $request["nama_def"][$i],
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                    "lenght" => $request["def_lenght"],
                    "user_created" => Auth::user()->name,
                    "no_form" => $request["def_noform"],
                ]);
            }
            }
            // dd($DefectTempData);

            $DefectTempStore = DefectTemp::insert($DefectTempData);

            // $massage = 'Inspection Number ' . $nomor . ' Saved Succesfully';
            // $massage2 = $nomor;

            return array(
                "status" => 200,
                "message" => '',
                "message2" => '',
                "additional" => [],
                // "redirect" => url('/qc-pass')
            );

    }


    public function getDetailList(Request $request)
    {

            $data_detail = DB::connection('mysql_sb')->select("select id,lenght_fabric,kode_def,COALESCE(upto3,0) upto3, COALESCE(over3,0) over3,COALESCE(over6,0) over6, COALESCE(over9,0) over9,CONCAT(width_det1,'->',width_det2) width_det from whs_qc_insp_dettemp where user_created = '".Auth::user()->name."'");

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }


    public function getDataSum(Request $request)
    {

            $data_detail = DB::connection('mysql_sb')->select("select no_form,no_roll,upto3, over3,over6,over9,width_fabric,ttl_poin,akt_poin,IF(akt_poin > 20,'REJECT','PASS') status,stat_save from (select no_form,no_roll,upto3, over3,over6,over9,width_fabric,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin,'save' stat_save from (select *, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form,no_roll from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form ) a where no_insp = '".$request->no_insp."'
UNION select no_form,no_roll,upto3, over3,over6,over9,width_fabric,ttl_poin,round((x/(width_fabric * '".$request->akt_lenght."')),2) akt_poin,'-' stat_save from (select *, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x from (select (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form,no_roll from whs_qc_insp_dettemp where user_created = '".Auth::user()->name."') a inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_dettemp where width_det2 is not null and user_created = '".Auth::user()->name."') a GROUP BY no_form) c on c.form_no = a.no_form) a) a");

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_detail)),
            "recordsFiltered" => intval(count($data_detail)),
            "data" => $data_detail
        ]);
    }

    public function getavgpoin(Request $request)
    {
        $avgpoin = DB::connection('mysql_sb')->select("
                select CONCAT(status,' (',avg_poin, ')') avg_poin, avg_poin poin from (select ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) avg_poin,IF(ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) > 15,'-','PASS') status from (select sum(ttl_poin) ttl_poin, COUNT(width_fabric)ttl_width, SUM(width_fabric) akt_width, SUM(l_actual) akt_lenght from (select upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select *, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form) a where no_insp = '".$request->no_insp."' UNION select upto3, over3,over6,over9,width_fabric,'".$request->akt_lenght."' l_actual,ttl_poin,round((x/(width_fabric * '".$request->akt_lenght."')),2) akt_poin from (select *, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x from (select (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_dettemp where user_created = '".Auth::user()->name."') a inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_dettemp where width_det2 is not null and user_created = '".Auth::user()->name."') a GROUP BY no_form) c on c.form_no = a.no_form) a) a) a) a
            ");

        $html = "";

        foreach ($avgpoin as $poin) {
            $html .= " <label> Average Actual Point: " . $poin->avg_poin . "</label>";
        }

        return $html;
    }

    public function getpoin(Request $request)
    {
        $avgpoin = DB::connection('mysql_sb')->select("
                select CONCAT(status,' (',avg_poin, ')') avg_poin, avg_poin poin from (select ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) avg_poin,IF(ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) > 15,'-','PASS') status from (select sum(ttl_poin) ttl_poin, COUNT(width_fabric)ttl_width, SUM(width_fabric) akt_width, SUM(l_actual) akt_lenght from (select upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select *, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual from (select no_insp, (COALESCE(SUM(upto3)) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form,width_fabric from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form) a where no_insp = '".$request->no_insp."') a) a) a
            ");

        return $avgpoin;
    }

    public function finishdata(Request $request)
    {
        $updateLokasi = QcInspect::where('no_insp', $request->no_insp)->update([
                'status' => 'PASS',
            ]);

            $massage = 'Inspection ' . $request->no_insp . ' save Succesfully';

            return array(
                "status" => 200,
                "message" => $massage,
                 "message2" => $request->no_insp,
                "additional" => [],
                "redirect" => url('/qc-pass')
            );
    }

    public function finishdatamodal(Request $request)
    {

        $validatedRequest = $request->validate([
            "mdl_status" => "required",
        ]);

        $updateLokasi = QcInspect::where('no_insp', $request['mdl_no_insp'])->update([
                'status' => $validatedRequest['mdl_status'],
            ]);

            $massage = 'Inspection ' .$request['mdl_no_insp'] . ' save Succesfully';

            return array(
                "status" => 200,
                "message" => $massage,
                 "message2" => $request->no_insp,
                "additional" => [],
                "redirect" => url('/qc-pass')
            );
    }

    public function storeQcTemp(Request $request)
    {

        // $validatedRequest = $request->validate([
        //     "txt_no_ins" => "required",
        //     "tgl_ins" => "required",
        //     "txt_id_item" => "required",
        //     "txt_style" => "required",
        //     "txt_color" => "required",
        //     "txt_lot" => "required",
        //     "txt_buyer" => "required",
        //     "txt_fab_name" => "required",
        // ]);

        if ($request['txt_kode_qc'] != '') {
        $data_def = DB::connection('mysql_sb')->insert("insert into whs_qc_insp_deftemp select * from whs_defect_temp where user_created = '".Auth::user()->name."'");
        }


        $timestamp = Carbon::now();


            $inspectTempData = [];
            array_push($inspectTempData, [
                'no_insp' => $request['txt_noinsp'],
                'no_form' => $request['txt_no_form'],
                'tgl_form' => $request['txt_tgl_form'],
                'no_roll' => $request['txt_no_roll'],
                'weight_fabric' => $request['txt_berat'],
                'unit_convert' => $request['txt_unit'],
                'weight_convert' => $request['txt_berat_2'],
                'width_fabric' => $request['txt_lebar'],
                'fabric_supp' => $request['txt_fab_supp'],
                'inspektor' => $request['txt_inspektor'],
                'no_mesin' => $request['txt_no_mesin'],
                'aktual' => $request['txt_aktual'],
                'gramage' => $request['txt_gramasi'],
                'lenght_fabric' => $request['txt_panjang'],
                'kode_def' => $request['txt_kode_qc'],
                'upto3' => $request['txt_upto3'],
                'over3' => $request['txt_over3'],
                'over6' => $request['txt_over6'],
                'over9' => $request['txt_over9'],
                'width_det1' => $request['txt_lebar1'],
                'width_det2' => $request['txt_lebar2'],
                'user_created' => Auth::user()->name,
                'inp_width_fabric' => $request['lebar_txt'],
                'unit_width_fabric' => $request['txt_unit_lebar'],
                'inp_width_det1' => $request['txt_lebar_'],
                'unit_width_det1' => $request['txt_unitleb1'],
                'inp_width_det2' => $request['txt_lebar__'],
                'unit_width_det2' => $request['txt_unitleb2'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                ]);

             $inspectTempStore = QcInspectTemp::insert($inspectTempData);

            // $massage = 'Form Number ' . $request['txt_no_form'] . ' Saved Succesfully';
            // $massage2 = $request['txt_no_form'];

            return array(
                "status" => 200,
                "message" => '',
                "message2" => '',
                "additional" => [],
                // "redirect" => url('/qc-pass/create-qcpass/5')
            );

    }


    public function storeQcSave(Request $request)
    {

        $validatedRequest = $request->validate([
            "txt_akt_lenght" => "required",
        ]);

        $noform = DB::connection('mysql_sb')->select("
            select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'FRM' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_form) is null,'00001',LPAD(SUBSTR(MAX(no_form),10,5)+1,5,0)) nomor from whs_qc_insp_det where MONTH(tgl_form) = MONTH(CURRENT_DATE()) and YEAR(tgl_form) = YEAR(CURRENT_DATE())) a");
        $no_form = $noform ? $noform[0]->kode : null;

        $data_detail = DB::connection('mysql_sb')->insert("insert into whs_qc_insp_det select id,no_insp,'".$no_form."' no_form, tgl_form,no_roll,weight_fabric, unit_convert, weight_convert,width_fabric,fabric_supp,inspektor,no_mesin,aktual,gramage,lenght_fabric,kode_def,upto3,over3,over6,over9,width_det1,width_det2,user_created,inp_width_fabric,unit_width_fabric,inp_width_det1,unit_width_det1,inp_width_det2,unit_width_det2,created_at,updated_at from whs_qc_insp_dettemp where user_created = '".Auth::user()->name."'");
        $data_def = DB::connection('mysql_sb')->insert("insert into whs_qc_insp_def select id,kode,nama_defect,created_at,updated_at,lenght,user_created,'".$no_form."' no_form from whs_qc_insp_deftemp where user_created = '".Auth::user()->name."'");
        $delete_tempdef = DefectTemp::where('user_created',Auth::user()->name)->delete();
        $delete_qctempdef = QcDefectTemp::where('user_created',Auth::user()->name)->delete();

        $timestamp = Carbon::now();

            $inspectSumData = [];
            array_push($inspectSumData, [
                'no_form' => $no_form,
                'no_roll' => $request['txt_no_roll2'],
                'lenght_barcode' => $request['txt_barcode'],
                'lenght_actual' => $validatedRequest['txt_akt_lenght'],
                'catatan' => $request['txt_remark'],
                'inp_barcode' => $request['txt_barcode_'],
                'unit_barcode' => $request['txt_unitbar'],
                'inp_actual' => $request['txt_akt_lenght_'],
                'unit_actual' => $request['txt_unitakt_lenght'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                ]);

             $inspectSumStore = QcInspectSum::insert($inspectSumData);
             $delete_temp = QcInspectTemp::where('user_created', Auth::user()->name)
              ->delete();


            return array(
                "status" => 200,
                "message" => '',
                "message2" => '',
                "additional" => [],
                // "redirect" => url('/qc-pass/create-qcpass/5')
            );

    }

    public function deleteqctemp(Request $request)
    {

        $delete_temp = QcInspectTemp::where('id', $request->id_temp)
              ->delete();


            return array(
                "status" => 200,
                "message" => '',
                "message2" => '',
                "additional" => [],
                // "redirect" => url('/qc-pass/create-qcpass/5')
            );

    }

    public function deleteqcdet(Request $request)
    {

        $delete_det = QcInspectDet::where('no_form', $request->id_temp)
              ->delete();
        $delete_sum = QcInspectSum::where('no_form', $request->id_temp)
              ->delete();
        $delete_temp = QcDefectDef::where('no_form', $request->id_temp)
              ->delete();


            return array(
                "status" => 200,
                "message" => '',
                "message2" => '',
                "additional" => [],
                // "redirect" => url('/qc-pass/create-qcpass/5')
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

        $datanomor = DB::connection('mysql_sb')->select("
        select kode_lok from whs_master_lokasi where kode_lok = '".$lokCode."' and kode_lok != (select kode_lok from whs_master_lokasi where id = '".$validatedRequest['txt_id']."')");
        $nomor_lokasi = $datanomor ? $datanomor[0] : null;
      if($nomor_lokasi == null){

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
        }else{
        return array(
                "status" => 400,
                "message" => "Data Duplicate",
                "additional" => [],
            );
    }

    }


    public function printlokasi(Request $request, $id)
    {
        $dataLokasi = MasterLokasi::selectRaw("
                CONCAT(inisial_lok,baris_lok,level_lok,no_lok) kode_lok,
                kode_lok kode,
                id
            ")->
            where("id", $id)->
            first();

        // PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('master.pdf.print-lokasi', ["dataLokasi" => $dataLokasi])->setPaper('a7', 'landscape');

        // $pdf = PDF::loadView('master.pdf.print-lokasi', ["dataLokasi" => $dataLokasi]);

        $fileName = 'Lokasi-'.$dataLokasi->kode_lok.'.pdf';

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
        $dataLokasi = DB::connection('mysql_sb')->select("
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

    public function getdatadetailqc(Request $request)
    {
        // $det_lokasi = DB::connection('mysql_sb')->table('whs_lokasi_inmaterial')->select('no_roll','no_lot','ROUND(qty_sj,2) qty_sj','ROUND(qty_aktual,2) qty_aktual','kode_lok')->where('status', '=', 'Y')->where('no_dok', '=', $request->no_dok)->where('no_ws', '=', $request->no_ws)->where('id_jo', '=', $request->id_jo)->where('id_item', '=', $request->id_item)->get();
        $det_data = DB::connection('mysql_sb')->select("select a.id,a.no_insp,tgl_insp,no_form,lenght_fabric,kode_def,COALESCE(upto3,0) upto3, COALESCE(over3,0) over3,COALESCE(over6,0) over6, COALESCE(over9,0) over9,CONCAT(width_det1,'->',width_det2) width_det  from whs_qc_insp a inner join whs_qc_insp_det b on b.no_insp = a.no_insp where a.id = '". $request->id_h . "' ");

        $html = '<div class="table-responsive" style="max-height: 200px">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Lenght</th>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Code</th>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Up To 3"</th>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Over 3" - 6"</th>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Over 6" - 9"</th>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Over 9"</th>
                        <th class="text-center" style="font-size: 0.6rem;width:;">Width</th>
                    </tr>
                </thead>
                <tbody>';
            // $jml_qty_sj = 0;
            // $jml_qty_ak = 0;
        foreach ($det_data as $det) {

            $html .= ' <tr>
                        <td class="text-left">' . $det->lenght_fabric . '</td>
                        <td class="text-right">' . $det->kode_def . '</td>
                        <td class="text-right">' . $det->upto3 . '</td>
                        <td class="text-left">' . $det->over3 . '</td>
                        <td class="text-right">' . $det->over6 . '</td>
                        <td class="text-right">' . $det->over9 . '</td>
                        <td class="text-left">' . $det->width_det . '</td>
                       </tr>';
        }

        $html .= '</tbody>
            </table>
        </div>';

          // dd($html);
        return $html;
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
