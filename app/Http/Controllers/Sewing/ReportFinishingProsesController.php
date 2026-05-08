<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use DB;
use App\Exports\Sewing\ExportReportFinishingProses;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportFinishingProsesController extends Controller
{
    public function index(Request $request)
    {

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $start = $start_date . ' 00:00:00';
        $end   = $end_date . ' 23:59:59';

        if ($request->ajax()) {
            if ($start_date === null || $end_date === null) {
                return response()->json(['data' => []]);
            } else {
                if (!empty($request->proses)) {
                    $rawData = DB::connection('mysql_sb')->select("SELECT
                            output_secondary_master.secondary AS proses,
                            userpassword.username AS line,
                            mastersupplier.supplier AS buyer,
                            act_costing.kpno AS no_ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            SUM(wip) wip,
                            SUM(`in`) `in`,
                            SUM(defect) defect,
                            SUM(rework) rework,
                            SUM(reject) reject,
                            SUM(output) + SUM(rework) output
                        FROM
                        (
                                -- IN
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        COUNT( DISTINCT CASE WHEN output_secondary_out.id IS NULL THEN output_secondary_in.id END ) AS wip,
                                        COUNT( DISTINCT output_secondary_in.id ) AS 'in',
                                        0 AS defect,
                                        0 AS rework,
                                        0 AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_in
                                        LEFT JOIN output_secondary_out on output_secondary_out.secondary_in_id = output_secondary_in.id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_in.updated_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- DEFECT
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        COUNT( DISTINCT output_secondary_out_defect.id ) AS defect,
                                        0 AS rework,
                                        0 AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_out_defect
                                        LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out_defect.created_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- REWORK
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        0 AS defect,
                                        COUNT( DISTINCT output_secondary_out_defect.id ) AS rework,
                                        0 AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_out_defect
                                        LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out_defect.updated_at BETWEEN ? and ?
                                        AND output_secondary_out_defect.`status` = 'reworked'
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- REJECT
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        0 AS defect,
                                        0 AS rework,
                                        COUNT( DISTINCT output_secondary_out_reject.id ) AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_out_reject
                                        LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_reject.secondary_out_id
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out.updated_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- OUTPUT RFT & REWORK
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        0 AS defect,
                                        0 AS rework,
                                        0 AS reject,
                                        COUNT(output_rfts.id) AS output
                                FROM
                                        output_secondary_out
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out.updated_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                        AND output_secondary_out.`status` = 'rft'
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by

                        ) secondary_process
                        LEFT JOIN output_secondary_master ON output_secondary_master.id = secondary_process.secondary_id
                        LEFT JOIN so_det ON so_det.id = secondary_process.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = secondary_process.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        WHERE
                            output_secondary_master.secondary = ?
                        group by
                            proses,
                            line,
                            no_ws,
                            style,
                            color,
                            size
                    ", [
                        $start, $end,
                        $start, $end,
                        $start, $end,
                        $start, $end,
                        $start, $end,
                        $request->proses
                    ]);
                }else{
                    $rawData = DB::connection('mysql_sb')->select("SELECT
                            output_secondary_master.secondary AS proses,
                            userpassword.username AS line,
                            mastersupplier.supplier AS buyer,
                            act_costing.kpno AS no_ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            SUM(wip) wip,
                            SUM(`in`) `in`,
                            SUM(defect) defect,
                            SUM(rework) rework,
                            SUM(reject) reject,
                            SUM(output) + SUM(rework) output
                        FROM
                        (
                                -- IN
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        COUNT( DISTINCT CASE WHEN output_secondary_out.id IS NULL THEN output_secondary_in.id END ) AS wip,
                                        COUNT( DISTINCT output_secondary_in.id ) AS 'in',
                                        0 AS defect,
                                        0 AS rework,
                                        0 AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_in
                                        LEFT JOIN output_secondary_out on output_secondary_out.secondary_in_id = output_secondary_in.id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_in.updated_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- DEFECT
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        COUNT( DISTINCT output_secondary_out_defect.id ) AS defect,
                                        0 AS rework,
                                        0 AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_out_defect
                                        LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out_defect.created_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- REWORK
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        0 AS defect,
                                        COUNT( DISTINCT output_secondary_out_defect.id ) AS rework,
                                        0 AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_out_defect
                                        LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out_defect.updated_at BETWEEN ? and ?
                                        AND output_secondary_out_defect.`status` = 'reworked'
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- REJECT
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        0 AS defect,
                                        0 AS rework,
                                        COUNT( DISTINCT output_secondary_out_reject.id ) AS reject,
                                        0 AS output
                                FROM
                                        output_secondary_out_reject
                                        LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_reject.secondary_out_id
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out.updated_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by
                                UNION ALL

                                -- OUTPUT RFT & REWORK
                                SELECT
                                        output_rfts.so_det_id,
                                        output_rfts.created_by,
                                        output_secondary_in.secondary_id,
                                        0 AS wip,
                                        0 AS 'in',
                                        0 AS defect,
                                        0 AS rework,
                                        0 AS reject,
                                        COUNT(output_rfts.id) AS output
                                FROM
                                        output_secondary_out
                                        LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                        LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                                WHERE
                                        output_secondary_out.updated_at BETWEEN ? and ?
                                        AND output_rfts.id is not null
                                        AND output_secondary_out.`status` = 'rft'
                                GROUP BY
                                        output_secondary_in.secondary_id,
                                        output_rfts.so_det_id,
                                        output_rfts.created_by

                        ) secondary_process
                        LEFT JOIN output_secondary_master ON output_secondary_master.id = secondary_process.secondary_id
                        LEFT JOIN so_det ON so_det.id = secondary_process.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = secondary_process.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        group by
                            proses,
                            line,
                            no_ws,
                            style,
                            color,
                            size
                    ", [
                        $start, $end,
                        $start, $end,
                        $start, $end,
                        $start, $end,
                        $start, $end,
                    ]);
                }

                return response()->json([
                    'data' => $rawData
                ]);
            }
        }

        $proses = DB::connection('mysql_sb')->table('output_secondary_master')->get();

        return view(
            'sewing.report.report-finishing-proses',
            [
                'page' => 'dashboard-sewing-eff',
                "subPageGroup" => "sewing-report",
                "subPage" => "report-finishing-proses",
                "proses" => $proses
            ]
        );
    }

    public function export_excel_report_finishing_proses(Request $request)
    {
        return Excel::download(new ExportReportFinishingProses($request->start_date, $request->end_date, $request->proses), 'Laporan Finishing Proses.xlsx');
    }
}
