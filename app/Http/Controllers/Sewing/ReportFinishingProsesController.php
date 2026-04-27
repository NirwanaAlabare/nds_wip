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
                    $rawData = DB::connection('mysql_sb')->select("
                        SELECT
                            output_secondary_master.secondary AS proses,
                            userpassword.username AS line,
                            mastersupplier.supplier AS buyer,
                            act_costing.kpno AS no_ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            COUNT( DISTINCT CASE WHEN NOT EXISTS ( SELECT 1 FROM output_secondary_out oso_check WHERE oso_check.secondary_in_id = output_secondary_in.id ) THEN output_secondary_in.id END ) AS wip,
                            COUNT( DISTINCT output_secondary_in.id ) AS 'in',
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'defect' THEN output_secondary_out.id END ) AS defect,
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'rework' THEN output_secondary_out.id END ) AS rework,
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'reject' THEN output_secondary_out.id END ) AS reject,
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'rft' THEN output_secondary_out.id END ) AS output 
                        FROM
                            output_secondary_master
                            LEFT JOIN output_secondary_in ON output_secondary_in.secondary_id = output_secondary_master.id
                            LEFT JOIN output_secondary_out ON output_secondary_out.secondary_in_id = output_secondary_in.id
                            LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                            LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                            LEFT JOIN so ON so.id = so_det.id_so
                            LEFT JOIN act_costing ON act_costing.id = so.id_cost
                            LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                            LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                            LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer 
                        WHERE
                            output_secondary_in.updated_at BETWEEN ? AND ?
                            AND output_secondary_master.secondary = ?
                        GROUP BY
                            output_secondary_master.secondary,
                            userpassword.username,
                            act_costing.kpno,
                            act_costing.styleno,
                            so_det.color,
                            so_det.size
                    ", [
                        $start, $end, $request->proses
                    ]);
                }else{
                    $rawData = DB::connection('mysql_sb')->select("
                        SELECT
                            output_secondary_master.secondary AS proses,
                            userpassword.username AS line,
                            mastersupplier.supplier AS buyer,
                            act_costing.kpno AS no_ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            COUNT( DISTINCT CASE WHEN NOT EXISTS ( SELECT 1 FROM output_secondary_out oso_check WHERE oso_check.secondary_in_id = output_secondary_in.id ) THEN output_secondary_in.id END ) AS wip,
                            COUNT( DISTINCT output_secondary_in.id ) AS 'in',
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'defect' THEN output_secondary_out.id END ) AS defect,
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'rework' THEN output_secondary_out.id END ) AS rework,
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'reject' THEN output_secondary_out.id END ) AS reject,
                            COUNT( DISTINCT CASE WHEN output_secondary_out.STATUS = 'rft' THEN output_secondary_out.id END ) AS output 
                        FROM
                            output_secondary_master
                            LEFT JOIN output_secondary_in ON output_secondary_in.secondary_id = output_secondary_master.id
                            LEFT JOIN output_secondary_out ON output_secondary_out.secondary_in_id = output_secondary_in.id
                            LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                            LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                            LEFT JOIN so ON so.id = so_det.id_so
                            LEFT JOIN act_costing ON act_costing.id = so.id_cost
                            LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                            LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                            LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer 
                        WHERE
                            output_secondary_in.updated_at BETWEEN ? AND ?
                        GROUP BY
                            output_secondary_master.secondary,
                            userpassword.username,
                            act_costing.kpno,
                            act_costing.styleno,
                            so_det.color,
                            so_det.size
                    ", [
                        $start, $end
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
