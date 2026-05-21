<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FGStokScanBPB;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputan;
use App\Models\WhsSoljer\PengeluaranGudangInputanDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputanHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class FGStokScanBPBController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("
            select
            a.id,
            no_trans,
            tgl_terima,
            concat((DATE_FORMAT(tgl_terima,  '%d')), '-', left(DATE_FORMAT(tgl_terima,  '%M'),3),'-',DATE_FORMAT(tgl_terima,  '%Y')
            ) tgl_terima_fix,
            buyer,
            ws,
            brand,
            styleno,
            color,
            size,
            a.qty,
            a.grade,
            no_carton,
            lokasi,
            sumber_pemasukan,
            a.created_by,
            created_at
            from fg_stok_bpb_scan a
            left join master_sb_ws m on a.id_so_det = m.id_so_det
            where tgl_terima >= '$tgl_awal' and tgl_terima <= '$tgl_akhir'
            order by substr(no_trans,13) desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $sql_temp = DB::select("select * from fg_tmp_stok_bpb where created_by = '$user' group by created_by");
        $cek_temp = $sql_temp ? $sql_temp[0]->id : null;


        return view('fg-stock.bpb_fg_stock_scan', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock", "cek_temp" => $cek_temp]);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $data = FGStokScanBPB::create([
                'no_trans'         => NULL,
                'tgl_terima'       => $request->tanggal_penerimaan,
                'id_so_det'        => $request->so_det_id,
                'qty'              => 1,
                'grade'            => $request->grade,
                'no_carton'        => $request->no_karton,
                'lokasi'           => NULL,
                'sumber_pemasukan' => $request->sumber_penerimaan,
                'mutasi'           => "N",
                'no_mutasi'        => NULL,
                'cancel'           => "N",
                "created_by"       => $user ? $user->name : null,
                "created_at"       => $now,
            ]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data berhasil disimpan.",
            );

        } catch (Exception $e) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => "Terjadi Kesalahan",
                "messages" => $e->getMessage(), 
                "additional" => [],
            );
        }
    }


    public function getDataBarcode(Request $request)
    {
        $barcode = $request->barcode;

        $data = DB::connection('mysql_sb')
            ->table('output_reject_out_detail')
            ->leftJoin('output_reject_in', 'output_reject_in.id', '=', 'output_reject_out_detail.reject_in_id')
            ->select('output_reject_in.*')
            ->where('output_reject_in.kode_numbering', $barcode)
            ->first();

        if (!$data) {
            return response()->json([
                'status' => 404,
                'message' => 'Barcode ' . $barcode .  ' tidak valid'
            ]);
        }

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }
}
