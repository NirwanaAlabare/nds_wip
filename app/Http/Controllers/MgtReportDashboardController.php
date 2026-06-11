<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MgtReportDashboardController extends Controller
{
    public function dashboard_mgt_report(Request $request)
    {
        return view('management_report.dashboard_mgt_report', [
            'page' => 'dashboard-mgt-report',
            'containerFluid' => true
        ]);
    }

    public function getFilterOptions(Request $request)
    {
        $start = $request->start_date ?? date('Y-m-01');
        $end   = $request->end_date   ?? date('Y-m-d');

        $buyers = DB::connection('mysql_sb')->select(
            "SELECT DISTINCT buyer FROM mgt_rep_tmp_earning
             WHERE tanggal >= ? AND tanggal <= ? ORDER BY buyer",
            [$start, $end]
        );

        $lines = DB::connection('mysql_sb')->select(
            "SELECT DISTINCT sewing_line FROM mgt_rep_tmp_earning
             WHERE tanggal >= ? AND tanggal <= ? ORDER BY sewing_line",
            [$start, $end]
        );

        return response()->json(['buyers' => $buyers, 'lines' => $lines]);
    }

    public function getSummary(Request $request)
    {
        $start = $request->start_date;
        $end   = $request->end_date;

        if (!$start || !$end) {
            return response()->json([
                'total_earning' => 0,
                'total_cost' => 0,
                'total_balance' => 0,
                'avg_margin' => 0,
                'total_output' => 0,
                'active_lines' => 0,
                'active_buyers' => 0
            ]);
        }

        [$filter, $params] = $this->buildFilter($request, [$start, $end]);

        $data = DB::connection('mysql_sb')->selectOne("
            SELECT
                COALESCE(SUM(tot_earning_rupiah), 0) AS total_earning,
                COALESCE(SUM(est_tot_cost), 0)       AS total_cost,
                COALESCE(SUM(blc), 0)                AS total_balance,
                CASE WHEN SUM(est_tot_cost) > 0
                     THEN (SUM(tot_earning_rupiah) / SUM(est_tot_cost)) * 100
                     ELSE 0 END                      AS avg_margin,
                COALESCE(SUM(tot_output), 0)         AS total_output,
                COUNT(DISTINCT sewing_line)          AS active_lines,
                COUNT(DISTINCT buyer)                AS active_buyers
            FROM mgt_rep_tmp_earning
            WHERE tanggal >= ? AND tanggal <= ? $filter
        ", $params);

        return response()->json($data);
    }

    public function getDailyChart(Request $request)
    {
        $start = $request->start_date;
        $end   = $request->end_date;
        if (!$start || !$end) return response()->json([]);

        [$filter, $params] = $this->buildFilter($request, [$start, $end]);

        $data = DB::connection('mysql_sb')->select("
            SELECT
                tanggal,
                DATE_FORMAT(tanggal, '%d %b') AS label,
                SUM(tot_earning_rupiah)        AS earning,
                SUM(est_tot_cost)              AS cost,
                SUM(blc)                       AS balance,
                SUM(tot_output)                AS output
            FROM mgt_rep_tmp_earning
            WHERE tanggal >= ? AND tanggal <= ? $filter
            GROUP BY tanggal
            ORDER BY tanggal
        ", $params);

        return response()->json($data);
    }

    public function getBuyerChart(Request $request)
    {
        $start = $request->start_date;
        $end   = $request->end_date;
        if (!$start || !$end) return response()->json([]);

        // Buyer chart ignores buyer filter but keeps line filter
        $params = [$start, $end];
        $filter = "";
        if ($request->line && $request->line !== 'all') {
            $filter .= " AND sewing_line = ?";
            $params[] = $request->line;
        }

        $data = DB::connection('mysql_sb')->select("
            SELECT
                buyer,
                SUM(tot_earning_rupiah) AS earning,
                SUM(est_tot_cost)       AS cost,
                SUM(blc)                AS balance,
                CASE WHEN SUM(est_tot_cost) > 0
                     THEN (SUM(tot_earning_rupiah) / SUM(est_tot_cost)) * 100
                     ELSE 0 END         AS margin,
                SUM(tot_output)         AS output
            FROM mgt_rep_tmp_earning
            WHERE tanggal >= ? AND tanggal <= ? $filter
            GROUP BY buyer
            ORDER BY earning DESC
            LIMIT 10
        ", $params);

        return response()->json($data);
    }

    public function getDetailTable(Request $request)
    {
        $start = $request->start_date;
        $end   = $request->end_date;
        if (!$start || !$end) return response()->json([]);

        [$filter, $params] = $this->buildFilter($request, [$start, $end]);

        $data = DB::connection('mysql_sb')->select("
            SELECT
                tanggal,
                tanggal_fix,
                sewing_line,
                buyer,
                kpno,
                tot_output,
                eff_line,
                tot_earning_rupiah,
                est_tot_cost,
                blc,
                percent_est_earn
            FROM mgt_rep_tmp_earning
            WHERE tanggal >= ? AND tanggal <= ? $filter
            ORDER BY tanggal DESC, sewing_line
            LIMIT 1000
        ", $params);

        return response()->json($data);
    }

    private function buildFilter(Request $request, array $baseParams): array
    {
        $filter = "";
        $params = $baseParams;

        if ($request->buyer && $request->buyer !== 'all') {
            $filter .= " AND buyer = ?";
            $params[] = $request->buyer;
        }
        if ($request->line && $request->line !== 'all') {
            $filter .= " AND sewing_line = ?";
            $params[] = $request->line;
        }

        return [$filter, $params];
    }
}
