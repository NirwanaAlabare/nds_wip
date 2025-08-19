<?php

namespace App\Http\Controllers;

use App\Models\IrLogTrans;
use App\Models\BpbSB;
use App\Models\IrTransBpb;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;
use QrCode;
use DNS1D;
use PDF;

class MaintainBpbController extends Controller
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

            if ($request->status != 'ALL') {
                $where = " and status = '" . $request->status . "' ";
            }else{
                $where = "";
            }


            $dataMutlokas = DB::connection('mysql_sb')->select("select id, no_maintain,tgl_maintain,status,CONCAT(created_by,' (',created_date,')') create_user from maintain_bpb_h where tgl_maintain BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." order by no_maintain asc");


            return DataTables::of($dataMutlokas)->toJson();
        }

        $pilihan = DB::connection('mysql_sb')->select("select DISTINCT id, nama_pilihan from whs_master_pilihan where type_pilihan = 'status_maintain' and status = 'Active' order by id ASC");

        return view("maintain-bpb.maintain-bpb", ['pilihan' => $pilihan,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $nama_supp = DB::connection('mysql_sb')->select("select DISTINCT id_supplier, supplier from mastersupplier where tipe_sup = 'S' order by Supplier ASC");

        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'RVS/BPB' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_maintain) is null,'00001',LPAD(SUBSTR(max(SUBSTR(no_maintain,15)),1,5)+1,5,0)) nomor from maintain_bpb_h) a");

        if ($request->ajax()) {

            if ($request->nama_supp != 'ALL') {
                $where = " and supplier = '" . $request->nama_supp . "' ";
            }else{
                $where = "";
            }


            $data_trfbpb = DB::connection('mysql_sb')->select("select a.*, concat(b.status_closing, if(c.no_bpb is null,'',' - Verif')) status_closing from (select bpb.id ,bpb.bpbno_int, bpb.pono, bpb.bpbdate, mastersupplier.Supplier, po_header.jml_pterms, masterpterms.kode_pterms, bpb.curr, bpb.confirm_by,DATE_FORMAT(bpb.confirm_date,'%Y-%m-%d') confirm_date,round(sum((((IF(bpb.qty_reject IS NULL,(bpb.qty), (bpb.qty - bpb.qty_reject))) * bpb.price) + (((IF(bpb.qty_reject IS NULL,(bpb.qty), (bpb.qty - bpb.qty_reject))) * bpb.price) * (po_header.tax /100)))),2) as total, po_header.podate, po_header_draft.tipe_com, bpb.dateinput, sum(bpb.qty) qty, bpb.price
                from bpb
                left JOIN po_header on po_header.pono = bpb.pono
                left JOIN po_header_draft on po_header_draft.id = po_header.id_draft
                INNER JOIN mastersupplier on mastersupplier.Id_Supplier = bpb.id_supplier
                left join masterpterms on masterpterms.id = po_header.id_terms
                where status_maintain is null and bpbno_int like '%GK%' and bpb.confirm='Y' and bpb.cancel='N' and bpb.bpbdate between '".$request->tgl_awal."' and '".$request->tgl_akhir."' and po_header_draft.tipe_com is null  ".$where." || status_maintain is null and bpbno_int like '%GK%' and bpb.confirm='Y' and bpb.cancel='N' and bpb.bpbdate between '".$request->tgl_awal."' and '".$request->tgl_akhir."' and po_header_draft.tipe_com IN ('REGULAR','BUYER','FOC') ".$where." group by bpb.bpbno_int
                UNION
                select id,bppb.bppbno_int, '-' pono, bppb.bppbdate, mastersupplier.Supplier , '' ,'' , bppb.curr,bppb.confirm_by,DATE_FORMAT(bppb.confirm_date,'%Y-%m-%d') confirm_date, sum(bppb.qty * bppb.price) as total, '','', bppb.dateinput, sum(bppb.qty) qty, bppb.price from bppb inner join mastersupplier on mastersupplier.Id_Supplier = bppb.id_supplier where bppbno_int like '%GK%' and confirm = 'Y' and cancel != 'Y' and  bppb.bppbdate between '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." and status_maintain is null and tipe_sup = 'S' group by bppbno_int) a LEFT JOIN tbl_closing_periode b on a.bpbdate BETWEEN b.tgl_awal AND b.tgl_akhir LEFT JOIN (select * from (select no_bpb, supplier from bpb_new where tgl_bpb between '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." and status != 'Cancel' GROUP BY no_bpb
                UNION
                select no_bppb, supplier from bppb_new where tgl_bppb between '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." and status != 'Cancel' GROUP BY no_bppb) a) c on c.no_bpb = a.bpbno_int ");

            // dd($data_trfbpb);
            return DataTables::of($data_trfbpb)->toJson();
        }

        return view('maintain-bpb.create-maintain-bpb', ['kode_gr' => $kode_gr,'nama_supp' => $nama_supp, 'page' => 'dashboard-warehouse']);
    }

    public function detailmodal(Request $request)
    {
        $id = $request->id;

        $data = DB::connection('mysql_sb')->table('maintain_bpb_h')
        ->where('id', $id)
        ->first();

        $detail = DB::connection('mysql_sb')->table('maintain_bpb_det')
        ->where('no_maintain', $data->no_maintain)
        ->get();

        $html = view('maintain-bpb.detail_modal', compact('data', 'detail'))->render();

        return response()->json(['html' => $html]);
    }



    public function store(Request $request)
    {
    // Validasi: keterangan utama tidak boleh kosong
        if (empty($request->input('txt_keterangan'))) {
            return response()->json([
                "status" => 400,
                "message" => "Keterangan utama tidak boleh kosong.",
                "additional" => [],
                "redirect" => null
            ]);
        }

    // Validasi: Pastikan setiap baris yang dicentang memiliki keterangan
        for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            $check = isset($request['chek_id'][$i]) ? $request['chek_id'][$i] : 0;
            if ($check > 0 && empty($request["keterangan"][$i])) {
                return response()->json([
                    "status" => 400,
                    "message" => "Keterangan tidak boleh kosong pada baris ke-" . ($i + 1),
                    "additional" => [],
                    "redirect" => null
                ]);
            }
        }

    // Generate no dokumen otomatis
        $sql_trf = DB::connection('mysql_sb')->select("
            SELECT CONCAT(kode,'/',bulan,tahun,'/',nomor) kode
            FROM (
                SELECT
                'RVS/BPB' kode,
                DATE_FORMAT(CURRENT_DATE(), '%m') bulan,
                DATE_FORMAT(CURRENT_DATE(), '%y') tahun,
                IF(MAX(no_maintain) IS NULL, '00001', LPAD(SUBSTR(MAX(SUBSTR(no_maintain,15)),1,5)+1,5,0)) nomor
                FROM maintain_bpb_h
                ) a
            ");
        $kodeDokumen = $sql_trf[0]->kode;

    // Insert header
        DB::connection('mysql_sb')->table('maintain_bpb_h')->insert([
            'no_maintain'    => $kodeDokumen,
            'tgl_maintain'   => $request->input('txt_tgl_trf'),
            'status'         => 'POST',
            'keterangan'     => $request->input('txt_keterangan'),
            'created_by'     => Auth::user()->name,
            'created_date'   => now(),
        ]);

    // Insert detail
        for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
            $check = isset($request['chek_id'][$i]) ? $request['chek_id'][$i] : 0;
            if ($check > 0 && !empty($request["keterangan"][$i])) {
                DB::connection('mysql_sb')->table('maintain_bpb_det')->insert([
                    'no_maintain'        => $kodeDokumen,
                    'nama_supp'          => $request["Supplier"][$i],
                    'no_bpb'             => $request["bpbno_int"][$i],
                    'tgl_bpb'            => $request["bpbdate"][$i],
                    'no_po'              => ($request["pono"][$i] === 'null' || empty($request["pono"][$i])) ? '-' : $request["pono"][$i],
                    'top'                => ($request["jml_pterms"][$i] === 'null' || empty($request["jml_pterms"][$i])) ? '-' : $request["jml_pterms"][$i],
                    'p_terms'            => ($request["kode_pterms"][$i] === 'null' || empty($request["kode_pterms"][$i])) ? '-' : $request["kode_pterms"][$i],
                    'curr'               => $request["curr"][$i],
                    'qty'                => $request["txt_qty"][$i],
                    'total'              => $request["total"][$i],
                    'bpb_input_date'     => $request["dateinput_bpb"][$i],
                    'bpb_confirm'        => $request["confirm_by"][$i],
                    'bpb_confirm_date'   => $request["confirm_date"][$i],
                    'status'             => 'Y',
                    'keterangan'         => $request["keterangan"][$i],
                    'created_by'         => Auth::user()->name,
                    'created_date'       => now(),
                ]);

            // Update status di tabel bpb
                DB::connection('mysql_sb')->table('bpb')
                ->where('bpbno_int', $request["bpbno_int"][$i])
                ->update(['status_maintain' => 'Maintain']);

            // Update status di tabel bppb
                DB::connection('mysql_sb')->table('bppb')
                ->where('bppbno_int', $request["bpbno_int"][$i])
                ->update(['status_maintain' => 'Maintain']);
            }
        }

        return response()->json([
            "status" => 200,
            "message" => $kodeDokumen . ' Saved Successfully',
            "additional" => [],
            "redirect" => url('/maintain-bpb')
        ]);
    }



    public function cancelmaintain(Request $request)
    {
        $timestamp = Carbon::now();
        $updateHeader = DB::connection('mysql_sb')->table('maintain_bpb_h')->where('no_maintain', $request['txt_nodok'])->update([
            'status' => 'CANCEL',
            'cancel_by' => Auth::user()->name,
            'cancel_date' => $timestamp,
        ]);

        $updateDetail = DB::connection('mysql_sb')->table('maintain_bpb_det')->where('no_maintain', $request['txt_nodok'])->update([
            'status' => 'N',
        ]);

        $updatebpb = DB::connection('mysql_sb')->select("update bpb a INNER JOIN maintain_bpb_det b ON b.no_bpb = a.bpbno_int SET a.status_maintain = null where b.no_maintain = '".$request['txt_nodok']."'");

        $massage = 'Cancel Data Successfully';

        return array(
            "status" => 200,
            "message" => $massage,
            "additional" => [],
            "redirect" => url('/maintain-bpb')
        );

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


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




}
