<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLokasiMaterial;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use DB;
use PDF;

class PackingSubcontController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function PackingOutSubcont(Request $request)
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


            $data_inmaterial = DB::connection('mysql_sb')->select("select * from (select a.*,COALESCE(qty_lok,0) qty_lok,(round(COALESCE(qty,0),4) - round(COALESCE(qty_lok,0),4)) qty_balance from (select b.id,b.no_dok,b.tgl_dok,b.tgl_shipp,b.type_dok,b.no_po,b.supplier,b.no_invoice,b.type_bc,b.no_daftar,b.tgl_daftar, b.type_pch,CONCAT(b.created_by,' (',b.created_at, ') ') user_create,b.status,sum(COALESCE(qty_good,0)) qty from whs_inmaterial_fabric_det a inner join whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and b.no_dok like '%IN%' GROUP BY b.id) a left JOIN
                (select no_dok nodok,SUM(qty_aktual) qty_lok from whs_lokasi_inmaterial where status = 'Y' GROUP BY no_dok) b on b.nodok = a.no_dok UNION select kode_lok id,concat(no_bpb,' ',kode_lok) no_bpb,tgl_bpb,shipp,type_dok,no_po,b.supplier,no_sj,type_bc,no_daftar,tgl_daftar,type_pch,user_craete,status,round(qty,2) qty,round(qty_lok,2) qty_lok,qty_bal from (select a.kode_lok,a.no_bpb,a.tgl_bpb,'' shipp,'-' type_dok, a.no_po,a.no_sj,'' type_bc,'-' no_daftar,'' tgl_daftar,'Saldo Awal'  type_pch, '-' user_craete,'Approved' status,sum(a.qty) qty,sum(a.qty) qty_lok,'0' qty_bal from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item where a.qty != 0 GROUP BY kode_lok order by a.no_bpb asc) a left join (select a.bpbno_int,b.supplier from bpb a inner join mastersupplier b on b.id_supplier = a.id_supplier WHERE bpbdate >= '2021-10-01' and LEFT(bpbno_int,2) = 'GK' GROUP BY bpbno_int) b on b.bpbno_int = a.no_bpb GROUP BY kode_lok) a where a.tgl_dok BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." ".$where2." ".$where3." ".$where4." order by no_dok asc");


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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
