<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class MgtReportSumProdEarnController extends Controller
{
    public function mgt_report_sum_prod_earn(Request $request)
    {
        $thn_view = $request->periode_tahun_view;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("WITH b AS (
                SELECT
                    a.bulan,
                    a.nama_bulan,
                    CAST(a.tahun AS UNSIGNED) AS tahun,
                    COUNT(tanggal) AS tot_working_days
                FROM dim_date a
                LEFT JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
                WHERE status_prod = 'KERJA'
                    AND (status_absen != 'LN' OR status_absen IS NULL)
                    AND tahun >= '2025' AND tahun <= '2030'
                GROUP BY bulan, tahun
                ORDER BY
                    CAST(a.tahun AS UNSIGNED) ASC,
                    CAST(a.bulan AS UNSIGNED) ASC
            )

            SELECT
                b.bulan,
                nama_bulan,
                b.tahun,
                round(sum(projection / tot_working_days),2) AS tot_daily_cost
            FROM mgt_rep_daily_cost a
            LEFT JOIN b ON a.bulan = b.bulan AND a.tahun = b.tahun
            WHERE a.tahun >= ?
            GROUP BY b.bulan, b.nama_bulan, b.tahun
            ORDER BY
                CAST(b.tahun AS UNSIGNED) ASC,
                CAST(b.bulan AS UNSIGNED) ASC
        ", [$thn_view]);

            return response()->json([
                'data' => $data_input // ✅ simplified response
            ]);
        }

        // For non-AJAX (initial page load)
        return view('management_report.laporan_sum_prod_earn', [
            'page' => 'dashboard-mgt-report',
            'subPageGroup' => 'mgt-report-laporan',
            'subPage' => 'mgt-report-laporan-sum-prod-earn',
            'containerFluid' => true,
            'user' => $user,
        ]);
    }
}
