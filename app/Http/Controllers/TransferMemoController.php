<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use DB;
use QrCode;
use DNS1D;
use PDF;

class TransferMemoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function ApproveTransferMemo(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $keywordQuery = "";

            $data = DB::connection('mysql_sb')->select("select a.no_trans, tgl_trans, CONCAT(a.created_by,' (',a.created_at,')') create_user, a.status, a.id, upper(IFNULL(a.keterangan,b.keterangan)) keterangan from transfer_memo_exim_h a INNER JOIN transfer_memo_exim_det b on b.no_trans = a.no_trans where tgl_trans BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' and a.status = 'POST' and a.no_trans like '%TETM%' GROUP BY a.id");


            $result = DB::connection('mysql_sb')->select("select count(id) total_pending from (select a.no_trans, tgl_trans, CONCAT(a.created_by,' (',a.created_at,')') create_user, a.status, a.id, upper(IFNULL(a.keterangan,b.keterangan)) keterangan from transfer_memo_exim_h a INNER JOIN transfer_memo_exim_det b on b.no_trans = a.no_trans where a.status = 'POST' and a.no_trans like '%TETM%' GROUP BY a.id) a");

        $total_pending = $result[0]->total_pending;

return DataTables::of($data)
    ->with([
        'total_pending' => $total_pending
    ])
    ->toJson();
        }

        $result = DB::connection('mysql_sb')->select("select count(id) total_pending from (select a.no_trans, tgl_trans, CONCAT(a.created_by,' (',a.created_at,')') create_user, a.status, a.id, upper(IFNULL(a.keterangan,b.keterangan)) keterangan from transfer_memo_exim_h a INNER JOIN transfer_memo_exim_det b on b.no_trans = a.no_trans where a.status = 'POST' and a.no_trans like '%TETM%' GROUP BY a.id) a");

        $total_pending = $result[0]->total_pending;


        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();
        $mtypebc = DB::connection('mysql_sb')->table('masterpilihan')->select('id', 'nama_pilihan')->where('kode_pilihan', '=', 'JENIS_DOK_IN')->get();
        $pch_type = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Purchasing_type')->where('status', '=', 'Active')->get();
        $status = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'Status_material')->where('status', '=', 'Active')->get();
        $arealok = DB::connection('mysql_sb')->table('whs_master_area')->select('id', 'area')->where('status', '=', 'active')->get();
        $unit = DB::connection('mysql_sb')->table('whs_master_unit')->select('id', 'nama_unit')->where('status', '=', 'active')->get();

        return view("transfer-memo.approve-transfer-memo", ['status' => $status,'pch_type' => $pch_type,'mtypebc' => $mtypebc,'msupplier' => $msupplier,'arealok' => $arealok,'unit' => $unit, 'total_pending' => $total_pending,'page' => 'dashboard-marketing']);
    }


    public function DetailTransferMemo($id)
    {
        $header = DB::connection('mysql_sb')->selectOne("select * from transfer_memo_exim_h WHERE id = ?", [$id]);

        $detail = DB::connection('mysql_sb')->select("select c.id_h, b.nm_memo, c.tgl_memo, ms.supplier, jns_trans, jns_pengiriman, mb.supplier buyer, upper(IFNULL(b.keterangan,a.keterangan)) keterangan from transfer_memo_exim_h a INNER JOIN transfer_memo_exim_det b on b.no_trans = a.no_trans INNER JOIN memo_h c on c.nm_memo = b.nm_memo INNER JOIN mastersupplier ms on ms.id_supplier = c.id_supplier INNER JOIN mastersupplier mb on mb.id_supplier = c.id_buyer where a.id = ? GROUP BY b.nm_memo", [$id]);

        return response()->json([
            'header' => $header,
            'detail' => $detail
        ]);
    }


    public function UpdateTransferMemoApprove(Request $request)
{
    $no_trans   = $request->no_trans;
    $approveIds = $request->approve_ids ?? [];
    $cancelIds  = $request->cancel_ids ?? [];

    DB::connection('mysql_sb')->beginTransaction();

    try {

        if (!empty($approveIds)) {

            DB::connection('mysql_sb')->table('memo_h')
                ->whereIn('id_h', $approveIds)
                ->update([
                    'status_transfer'     => 'A-TETM',
                    'app_tetm_by' => Auth::user()->name,
                    'app_tetm_date' => now()
                ]);
        }

        if (!empty($cancelIds)) {

            $cancelMemo = DB::connection('mysql_sb')->table('memo_h')
                ->whereIn('id_h', $cancelIds)
                ->pluck('nm_memo')
                ->toArray();


            DB::connection('mysql_sb')->table('memo_h')
                ->whereIn('id_h', $cancelIds)
                ->update([
                    'status_transfer' => 'PENDING',
                    'tetm_by' => null,
                    'tetm_date' => null
                ]);

            DB::connection('mysql_sb')->table('transfer_memo_exim_det')
                ->where('no_trans', $no_trans)
                ->whereIn('nm_memo', $cancelMemo)
                ->update([
                    'status'     => 'N',
                    'updated_at' => now()
                ]);
        }


        DB::connection('mysql_sb')->table('transfer_memo_exim_h')
            ->where('no_trans', $no_trans)
            ->update([
                'status'     => 'APPROVED',
                'approved_by' => Auth::user()->name,
                'approved_date' => now(),
                'updated_at' => now()
            ]);

        DB::connection('mysql_sb')->commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diproses'
        ]);

    } catch (\Exception $e) {

        DB::connection('mysql_sb')->rollBack();

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}



    /**
     * Show the form for creating a new resource.
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


            $dataMutlokas = DB::connection('mysql_sb')->select("select a.no_trans, tgl_trans, CONCAT(a.created_by,' (',a.created_at,')') create_user, a.status, a.id, upper(IFNULL(a.keterangan,b.keterangan)) keterangan from transfer_memo_exim_h a INNER JOIN transfer_memo_exim_det b on b.no_trans = a.no_trans where tgl_trans BETWEEN '".$request->tgl_awal."' and '".$request->tgl_akhir."' and a.no_trans like '%TMTE%' GROUP BY a.id");


            return DataTables::of($dataMutlokas)->toJson();
        }

         $supp = DB::connection('mysql_sb')->select("select DISTINCT id_supplier, supplier from mastersupplier where tipe_sup = 'S' order by Supplier ASC");

        return view("transfer-memo.transfer-memo", ['supp' => $supp,"page" => "dashboard-marketing"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $nama_supp = DB::connection('mysql_sb')->select("select DISTINCT id_supplier, supplier from mastersupplier where tipe_sup = 'S' order by Supplier ASC");

        $kode_gr = DB::connection('mysql_sb')->select("select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'TMTE/NAG' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_trans) is null,'00001',LPAD(SUBSTR(max(SUBSTR(no_trans,16)),1,5)+1,5,0)) nomor from transfer_memo_exim_h where no_trans LIKE '%TMTE%') a");

        if ($request->ajax()) {

            if ($request->nama_supp != 'ALL') {
                $where = " and ms.supplier = '" . $request->nama_supp . "' ";
            }else{
                $where = "";
            }


            $data_trfbpb = DB::connection('mysql_sb')->select("select a.*, ms.supplier supplier, mb.supplier buyer from memo_h a
                    inner join mastersupplier ms on a.id_supplier = ms.id_supplier
                    inner join mastersupplier mb on a.id_buyer = mb.id_supplier where a.status != 'CANCEL' and tgl_memo between '".$request->tgl_awal."' and '".$request->tgl_akhir."' and a.status_transfer = 'A-TETM' ".$where." order by id_h desc");

            // dd($data_trfbpb);
            return DataTables::of($data_trfbpb)->toJson();
        }

        return view('transfer-memo.create-transfer-memo', ['kode_gr' => $kode_gr,'nama_supp' => $nama_supp, 'page' => 'dashboard-marketing']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    // Validasi: keterangan utama tidak boleh kosong
        if (empty($request->input('txt_keterangan'))) {
            return response()->json([
                "status" => 400,
                "message" => "Keterangan tidak boleh kosong.",
                "additional" => [],
                "redirect" => null
            ]);
        }

    // Generate no dokumen otomatis
        $sql_trf = DB::connection('mysql_sb')->select("select CONCAT(kode,'/',bulan,tahun,'/',nomor) kode from (select 'TMTE/NAG' kode, DATE_FORMAT(CURRENT_DATE(), '%m') bulan, DATE_FORMAT(CURRENT_DATE(), '%y') tahun,if(MAX(no_trans) is null,'00001',LPAD(SUBSTR(max(SUBSTR(no_trans,16)),1,5)+1,5,0)) nomor from transfer_memo_exim_h where no_trans LIKE '%TMTE%') a");
        $kodeDokumen = $sql_trf[0]->kode;


    // Insert header
        DB::connection('mysql_sb')->table('transfer_memo_exim_h')->insert([
            'no_trans'    => $kodeDokumen,
            'tgl_trans'   => $request->input('txt_tgl_trf'),
            'keterangan'     => $request->input('txt_keterangan'),
            'status'         => 'POST',
            'created_by'     => Auth::user()->name,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);


    // Insert detail
        foreach ($request->nm_memo as $i => $nm_memo) {

    $checked = $request->chek_id[$i] ?? 0;

    // ❌ skip kalau tidak diceklis
    if (!$checked) continue;

    if (!$nm_memo) continue;

    $ket = $request->keterangan[$i] ?? null;

    DB::connection('mysql_sb')->table('transfer_memo_exim_det')->insert([
        'no_trans'   => $kodeDokumen,
        'nm_memo'    => $nm_memo,
        'keterangan' => $ket,
        'status'     => 'Y',
        'created_by' => Auth::user()->name,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::connection('mysql_sb')->table('memo_h')
        ->where('nm_memo', $nm_memo)
        ->update([
            'status_transfer' => 'TMTE',
            'tmte_by'         => Auth::user()->name,
            'tmte_date'       => now()
        ]);
}



        return response()->json([
            "status" => 200,
            "message" => $kodeDokumen . ' Saved Successfully',
            "additional" => [],
            "redirect" => url('/transfer-memo')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function canceltransfer(Request $request)
    {
            $timestamp = Carbon::now();
           
            $cancelMemo = DB::connection('mysql_sb')->table('transfer_memo_exim_det')
                ->where('no_trans', $request['txt_nodok'])
                ->pluck('nm_memo')
                ->toArray();


            DB::connection('mysql_sb')->table('memo_h')
                ->whereIn('nm_memo', $cancelMemo)
                ->update([
                    'status_transfer' => 'A-TETM',
                    'tmte_by' => null,
                    'tmte_date' => null
                ]);

            DB::connection('mysql_sb')->table('transfer_memo_exim_det')
                ->where('no_trans', $request['txt_nodok'])
                ->whereIn('nm_memo', $cancelMemo)
                ->update([
                    'status'     => 'N',
                    'updated_at' => now()
                ]);

            DB::connection('mysql_sb')->table('transfer_memo_exim_h')
            ->where('no_trans', $request['txt_nodok'])
            ->update([
                'status'     => 'CANCEL',
                'cancel_by' => Auth::user()->name,
                'cancel_date' => now(),
                'updated_at' => now()
            ]);

            $massage = 'Cancel Data Successfully';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                "redirect" => url('/transfer-memo')
            );

    }

    public function UpdateTransferMemoCancel(Request $request)
{
    $no_trans   = $request->no_trans;
    $cancelIds  = $request->cancel_ids ?? [];

    DB::connection('mysql_sb')->beginTransaction();

    try {


        if (!empty($cancelIds)) {

            $cancelMemo = DB::connection('mysql_sb')->table('memo_h')
                ->whereIn('id_h', $cancelIds)
                ->pluck('nm_memo')
                ->toArray();


            DB::connection('mysql_sb')->table('memo_h')
                ->whereIn('id_h', $cancelIds)
                ->update([
                    'status_transfer' => 'PENDING',
                    'tetm_by' => null,
                    'tetm_date' => null
                ]);

            DB::connection('mysql_sb')->table('transfer_memo_exim_det')
                ->where('no_trans', $no_trans)
                ->whereIn('nm_memo', $cancelMemo)
                ->update([
                    'status'     => 'N',
                    'updated_at' => now()
                ]);
        }


        DB::connection('mysql_sb')->table('transfer_memo_exim_h')
            ->where('no_trans', $no_trans)
            ->update([
                'status'     => 'CANCEL',
                'cancel_by' => Auth::user()->name,
                'cancel_date' => now(),
                'updated_at' => now()
            ]);

        DB::connection('mysql_sb')->commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diproses'
        ]);

    } catch (\Exception $e) {

        DB::connection('mysql_sb')->rollBack();

        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
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
