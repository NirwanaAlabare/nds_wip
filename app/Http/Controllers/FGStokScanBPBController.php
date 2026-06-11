<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FGStokScanBPB;
use App\Exports\ExportLaporanPenerimaanFGStokScanBPB;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
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
                SELECT
                    qr_code,
                    CONCAT(
                        DATE_FORMAT(tgl_terima, '%d'), '-',
                        LEFT(DATE_FORMAT(tgl_terima, '%M'), 3), '-',
                        DATE_FORMAT(tgl_terima, '%Y')
                    ) AS tgl_terima_fix,
                    buyer,
                    brand,
                    styleno,
                    ws,
                    color,
                    size,
                    a.no_carton,
                    a.qty,
                    sumber_pemasukan,
                    a.id_so_det,
                    created_by,
                    created_at
                FROM fg_stok_bpb_scan a
                LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                WHERE tgl_terima >= '$tgl_awal'
                AND tgl_terima <= '$tgl_akhir'
                ORDER BY a.id DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.bpb_fg_stock_scan', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock-scan"]);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $exists = FGStokScanBPB::where('qr_code', $request->barcode_scan)->exists();
            if ($exists) {
                return response()->json([
                    "status" => 422,
                    "message" => "Barcode " . $request->barcode_scan . " sudah ada!"
                ], 422);
            }

            $data = FGStokScanBPB::create([
                'no_trans'         => NULL,
                'tgl_terima'       => $request->tanggal_penerimaan,
                'id_so_det'        => $request->so_det_id,
                'qty'              => $request->qty,
                'grade'            => $request->grade,
                'no_carton'        => $request->no_karton,
                'lokasi'           => NULL,
                'sumber_pemasukan' => $request->sumber_penerimaan,
                'mutasi'           => "N",
                'no_mutasi'        => NULL,
                'cancel'           => "N",
                'qr_code'          => $request->barcode_scan,
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
        ->select(
            'output_reject_in.id',
            'output_reject_in.kode_numbering',
            'output_reject_in.grade',
            'output_reject_in.so_det_id',
            DB::raw('COUNT(output_reject_in.so_det_id) as qty')
        )
        ->where('output_reject_in.kode_numbering', $barcode)
        ->groupBy(
            'output_reject_in.id',
            'output_reject_in.kode_numbering',
            'output_reject_in.grade',
            'output_reject_in.so_det_id'
        )
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

    public function getDataDetail(Request $request){
        $data = DB::table('fg_stok_bpb_scan as a')
            ->leftJoin('master_sb_ws as m', 'a.id_so_det', '=', 'm.id_so_det')
            ->selectRaw("
                a.id,
                a.no_trans,
                CONCAT(
                    DATE_FORMAT(a.tgl_terima, '%d'), '-',
                    LEFT(DATE_FORMAT(a.tgl_terima, '%M'), 3), '-',
                    DATE_FORMAT(a.tgl_terima, '%Y')
                ) AS tgl_terima_fix,
                a.lokasi,
                a.no_carton,
                a.qty,
                a.qr_code,
                buyer,
                brand,
                styleno,
                ws,
                color,
                size,
                a.sumber_pemasukan
            ")
            ->where('a.id_so_det', $request->id_so_det)
            ->where('a.no_trans', $request->no_trans)
            ->orderByRaw('a.id DESC');

        return DataTables::queryBuilder($data)->make(true);
    }

    public function export_excel_bpb_fg_stok_scan(Request $request)
    {
        return Excel::download(new ExportLaporanPenerimaanFGStokScanBPB($request->from, $request->to), 'Laporan_Penerimaan FG_Stok_Scan.xlsx');
    }
}
