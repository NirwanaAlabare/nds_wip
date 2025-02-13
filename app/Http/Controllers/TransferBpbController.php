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

class TransferBpbController extends Controller
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

            if ($request->nama_supp != 'ALL') {
                $where = " and nama_supp = '" . $request->nama_supp . "' ";
            }else{
                $where = "";
            }


            $dataMutlokas = DB::connection('mysql_sb')->select("select *,CONCAT(no_transfer,tgl_transfer,no_bpb,nama_supp,status,create_user) filter from (select no_transfer,tgl_transfer,no_bpb,nama_supp,status,FORMAT(sum(total),2) total,CONCAT(created_by,' (',created_at,')') create_user from ir_trans_bpb where tgl_transfer BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' ".$where." group by no_transfer order by no_transfer asc) a");


            return DataTables::of($dataMutlokas)->toJson();
        }

         $supp = DB::connection('mysql_sb')->select("select DISTINCT id_supplier, supplier from mastersupplier where tipe_sup = 'S' order by Supplier ASC");

        return view("transfer-bpb.transfer-bpb", ['supp' => $supp,"page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $nama_supp = DB::connection('mysql_sb')->select("select DISTINCT id_supplier, supplier from mastersupplier where tipe_sup = 'S' order by Supplier ASC");

        $kode_gr = DB::connection('mysql_sb')->select("
        select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'TBPB/NAG' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_trans) is null,'00001',LPAD(SUBSTR(max(SUBSTR(no_trans,15)),1,5)+1,5,0)) nomor from ir_log_trans where kode_trans = 'TBPB') a");

        if ($request->ajax()) {

            if ($request->nama_supp != 'ALL') {
                $where = " and supplier = '" . $request->nama_supp . "' ";
            }else{
                $where = "";
            }


            $data_trfbpb = DB::connection('mysql_sb')->select("select bpb.id ,bpb.bpbno_int, bpb.pono, bpb.bpbdate, mastersupplier.Supplier, po_header.jml_pterms, masterpterms.kode_pterms, bpb.curr, bpb.confirm_by,DATE_FORMAT(bpb.confirm_date,'%Y-%m-%d') confirm_date,round(sum((((IF(bpb.qty_reject IS NULL,(bpb.qty), (bpb.qty - bpb.qty_reject))) * bpb.price) + (((IF(bpb.qty_reject IS NULL,(bpb.qty), (bpb.qty - bpb.qty_reject))) * bpb.price) * (po_header.tax /100)))),2) as total, po_header.podate, po_header_draft.tipe_com
            from bpb
            INNER JOIN po_header on po_header.pono = bpb.pono
            left JOIN po_header_draft on po_header_draft.id = po_header.id_draft
            INNER JOIN mastersupplier on mastersupplier.Id_Supplier = bpb.id_supplier
            inner join masterpterms on masterpterms.id = po_header.id_terms
            where stat_trf is null and bpbno_int like '%GK%' and bpb.confirm='Y' and bpb.cancel='N' and bpb.bpbdate between '".$request->tgl_awal."' and '".$request->tgl_akhir."' and po_header_draft.tipe_com is null  ".$where." || stat_trf is null and bpbno_int like '%GK%' and bpb.confirm='Y' and bpb.cancel='N' and bpb.bpbdate between '".$request->tgl_awal."' and '".$request->tgl_akhir."' and po_header_draft.tipe_com IN ('REGULAR','BUYER','FOC') ".$where." group by bpb.bpbno_int
                    UNION
                  select id,bppb.bppbno_int, '-' pono, bppb.bppbdate, mastersupplier.Supplier , '' ,'' , bppb.curr,bppb.confirm_by,DATE_FORMAT(bppb.confirm_date,'%Y-%m-%d') confirm_date, sum(bppb.qty * bppb.price) as total, '','' from bppb inner join mastersupplier on mastersupplier.Id_Supplier = bppb.id_supplier where bppbno_int like '%GK%' and confirm = 'Y' and cancel != 'Y' and  bppb.bppbdate between '".$request->tgl_awal."' and '".$request->tgl_akhir."' and stat_trf is null and tipe_sup = 'S' group by bppbno_int");

            // dd($data_trfbpb);
            return DataTables::of($data_trfbpb)->toJson();
        }

        return view('transfer-bpb.create-transfer-bpb', ['kode_gr' => $kode_gr,'nama_supp' => $nama_supp, 'page' => 'dashboard-warehouse']);
    }



    public function store(Request $request)
    {

        $sql_trf = DB::connection('mysql_sb')->select("select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'TBPB/NAG' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_trans) is null,'00001',LPAD(SUBSTR(max(SUBSTR(no_trans,15)),1,5)+1,5,0)) nomor from ir_log_trans where kode_trans = 'TBPB') a");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
        $no_trf = $sql_trf[0]->kode;


        $jml_qtyout = 0;

    for ($i = 0; $i < intval($request['jumlah_data']); $i++) {
        $check = isset($request['chek_id'][$i]) ? $request['chek_id'][$i] : 0;
        if ($check > 0) {
        $bppb_headerSB = IrTransBpb::create([
                'no_transfer' => $no_trf,
                'tgl_transfer' => $request['txt_tgl_trf'],
                'nama_supp' => $request["Supplier"][$i],
                'no_bpb' => $request["bpbno_int"][$i],
                'tgl_bpb' => $request["bpbdate"][$i],
                'no_po' => $request["pono"][$i],
                'top' => $request["jml_pterms"][$i],
                'p_terms' => $request["kode_pterms"][$i],
                'curr' => $request["curr"][$i],
                'total' => $request["total"][$i],
                'bpb_confirm' => $request["confirm_by"][$i],
                'bpb_confirm_date' => $request["confirm_date"][$i],
                'status' => 'Transfer',
                'keterangan' => $request["keterangan"][$i],
                'created_by' => Auth::user()->name,
            ]);
        // $jml_qtyout = $request["qty_sdh_out"][$i] + $request["input_qty"][$i];

        $update_BppbReq = BpbSB::where('bpbno_int', $request["bpbno_int"][$i])->update([
                'stat_trf' => 'Transfer',
        ]);
        }
    }

        $bppb_header = IrLogTrans::create([
                'kode_trans' => 'TBPB',
                'no_trans' => $no_trf,
                'status' => 'Y',
                'created_by' => Auth::user()->name,
            ]);


            $massage = $no_trf . ' Saved Succesfully';
            $stat = 200;

            return array(
                "status" =>  $stat,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/transfer-bpb')
            );

    }


    public function canceltransfer(Request $request)
    {
            $timestamp = Carbon::now();
            $updateLokasi = IrTransBpb::where('no_transfer', $request['txt_nodok'])->update([
                'status' => 'Cancel',
                'cancel_by' => Auth::user()->name,
                'cancel_date' => $timestamp,
            ]);

            $updatebpb = DB::connection('mysql_sb')->select("update bpb a INNER JOIN ir_trans_bpb b ON b.no_bpb = a.bpbno_int SET a.stat_trf = null where b.no_transfer = '".$request['txt_nodok']."'");

            $massage = 'Cancel Data Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/transfer-bpb')
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
