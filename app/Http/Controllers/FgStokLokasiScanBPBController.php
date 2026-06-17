<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPenerimaanFGStokLokasiScanBPB;
use App\Http\Controllers\Controller;
use App\Models\FGStokLokasiScanBPB;
use App\Models\FGStokScanBPB;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class FGStokLokasiScanBPBController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        if ($request->ajax()) {
            $data_input = DB::select("
                select
                    a.id,
                    a.no_trans,
                    tgl_terima,
                    concat((DATE_FORMAT(tgl_terima,  '%d')), '-', left(DATE_FORMAT(tgl_terima,  '%M'),3),'-',DATE_FORMAT(tgl_terima,  '%Y')
                    ) tgl_terima_fix,
                    buyer,
                    ws,
                    brand,
                    styleno,
                    color,
                    size,
                    b.qty,
                    b.grade,
                    b.no_carton,
                    lokasi,
                    sumber_pemasukan,
                    a.created_by,
                    a.created_at,
                    a.qr_code
                from fg_stok_bpb_lokasi_scan a
                left join fg_stok_bpb_scan b ON b.qr_code = a.qr_code
                left join master_sb_ws m on b.id_so_det = m.id_so_det
                where tgl_terima >= '$tgl_awal' and tgl_terima <= '$tgl_akhir'
                order by a.id desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.bpb_fg_stock_lokasi_scan', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock-lokasi-scan"]);
    }

    public function create(){

        $lokasi = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok where cancel = 'N'");

        return view("fg-stock.create_bpb_fg_stock_lokasi_scan", [
            "page" => "dashboard-fg-stock",
            "subPageGroup" => "fgstock-bpb",
            "subPage" => "bpb-fg-stock-lokasi-scan",
            "lokasi" => $lokasi
        ]);
    }

    public function store(Request $request){

        DB::beginTransaction();

        try {

            $user = Auth::user();
            $now = Carbon::now();

            $items = json_decode($request->items, true);

            $no_trans = DB::selectOne("
                SELECT
                    CONCAT('FGS/SCAN/IN/', DATE_FORMAT(CURRENT_DATE(), '%m%y'), '/') AS prefix,

                    LPAD(
                        COALESCE(
                            MAX(CAST(RIGHT(no_trans, 5) AS UNSIGNED)),
                            0
                        ) + 1,
                        5,
                        '0'
                    ) AS nomor,

                    CONCAT(
                        'FGS/SCAN/IN/',
                        DATE_FORMAT(CURRENT_DATE(), '%m%y'),
                        '/',
                        LPAD(
                            COALESCE(
                                MAX(CAST(RIGHT(no_trans, 5) AS UNSIGNED)),
                                0
                            ) + 1,
                            5,
                            '0'
                        )
                    ) AS kode

                FROM fg_stok_bpb_scan
                WHERE
                    MONTH(tgl_terima) = MONTH(CURRENT_DATE())
                    AND YEAR(tgl_terima) = YEAR(CURRENT_DATE())
                    AND LEFT(no_trans, 3) = 'FGS'
            ");

            FGStokScanBPB::where('tgl_terima', $request->tanggal_penerimaan)
            ->where('no_carton', $request->no_karton)
            ->update([
                'no_trans'   => $no_trans->kode,
                'lokasi'     => $request->lokasi,
                'updated_at' => $now,
            ]);

            foreach ($items as $item) {
                $data = FGStokLokasiScanBPB::create([
                    'qr_code'             => $item['qr_code'],
                    'no_trans'            => $no_trans->kode,
                    "created_by"          => $user ? $user->name : null,
                    "created_at"          => $now,
                ]);
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data berhasil disimpan.<br>No Transaksi: " . $no_trans->kode,
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


    public function getDataStockScan(Request $request)
    {
        $data = DB::select("
            select
                a.id,
                no_trans,
                tgl_terima,
                concat((DATE_FORMAT(tgl_terima,  '%d')), '-', left(DATE_FORMAT(tgl_terima,  '%M'),3),'-',DATE_FORMAT(tgl_terima,  '%Y')) tgl_terima_fix,
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
                a.created_at,
                a.qr_code
            from fg_stok_bpb_scan a
            left join master_sb_ws m on a.id_so_det = m.id_so_det
            where DATE(tgl_terima) = ?
            and no_carton = ?
            and (a.no_trans is null or a.no_trans = '')
            order by id desc
        ", [
            $request->tanggal_terima,
            $request->no_karton
        ]);

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    public function export_excel_bpb_fg_stok_lokasi_scan(Request $request)
    {
        return Excel::download(new ExportLaporanPenerimaanFGStokLokasiScanBPB($request->from, $request->to), 'Laporan_Penerimaan FG_Stok_Scan.xlsx');
    }
}
