<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use DB;
use App\Exports\Sewing\ExportReportDefectReject;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportDefectRejectController extends Controller
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
                if ($request->department == "_packing") {
                    $rawData = DB::connection('mysql_sb')->select("
                        SELECT
                            output_defects_packing.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            userpassword.username AS sewing_line,
                            'FINISHING-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            output_defects_packing.defect_status AS status,
                            DATE_FORMAT(output_defects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                            DATE_FORMAT(output_defects_packing.updated_at, '%d-%m-%Y') AS tgl_rework,
                            output_defect_types.allocation AS proses_type,
                            output_defect_in_out.status AS proses_status,
                            DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                            DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                        FROM output_defects_packing
                        LEFT JOIN so_det ON so_det.id = output_defects_packing.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN userpassword ON userpassword.username = output_defects_packing.created_by
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects_packing.id
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_defects_packing.defect_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects_packing.defect_area_id
                        LEFT JOIN output_defect_in_out 
                            ON output_defect_in_out.output_type = 'packing' 
                            AND output_defect_in_out.defect_id = output_defects_packing.id
                        WHERE
                            (
                                output_defects_packing.created_at BETWEEN ? AND ?
                                OR
                                output_defects_packing.updated_at BETWEEN ? AND ?
                            )
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        UNION ALL

                        SELECT
                            output_rejects_packing.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            output_rejects_packing.created_by AS sewing_line,
                            'FINISHING-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            'Reject Mati' AS status,
                            DATE_FORMAT(output_rejects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                            '' AS tgl_rework,
                            '' AS proses_type,
                            '' AS proses_status,
                            '' AS tgl_proses_in,
                            '' AS tgl_proses_out
                        FROM output_rejects_packing
                        LEFT JOIN so_det ON so_det.id = output_rejects_packing.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects_packing.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects_packing.reject_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects_packing.reject_area_id
                        WHERE 
                            output_rejects_packing.reject_status = 'mati'
                            AND output_rejects_packing.created_at BETWEEN ? AND ?
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        ORDER BY tgl_defect DESC
                    ", [
                        $start, $end, $start, $end, $request->ws, $request->ws, $request->ws, // defects
                        $start, $end, $request->ws, $request->ws, $request->ws  // rejects
                    ]);

                } else if($request->department == "_end") {

                    $rawData = DB::connection('mysql_sb')->select("
                        SELECT
                            output_defects.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            userpassword.username AS sewing_line,
                            'END-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            output_defects.defect_status AS status,
                            DATE_FORMAT(output_defects.created_at, '%d-%m-%Y') AS tgl_defect,
                            DATE_FORMAT(output_defects.updated_at, '%d-%m-%Y') AS tgl_rework,
                            output_defect_types.allocation AS proses_type,
                            output_defect_in_out.STATUS AS proses_status,
                            DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                            DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                        FROM output_defects
                        LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects.id
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects.defect_area_id
                        LEFT JOIN output_defect_in_out 
                            ON output_defect_in_out.output_type = 'qc'
                            AND output_defect_in_out.defect_id = output_defects.id
                        WHERE
                            (
                                output_defects.created_at BETWEEN ? AND ?
                                OR
                                output_defects.updated_at BETWEEN ? AND ?
                            )
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        UNION ALL

                        SELECT
                            output_rejects.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            userpassword.username AS sewing_line,
                            'END-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            'Reject Mati' AS status,
                            DATE_FORMAT(output_rejects.created_at, '%d-%m-%Y') AS tgl_defect,
                            '' AS tgl_rework,
                            '' AS proses_type,
                            '' AS proses_status,
                            '' AS tgl_proses_in,
                            '' AS tgl_proses_out
                        FROM output_rejects
                        LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects.reject_area_id
                        WHERE 
                            output_rejects.reject_status = 'mati'
                            AND output_rejects.created_at BETWEEN ? AND ?
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        ORDER BY tgl_defect DESC
                    ", [
                        $start, $end, $start, $end, $request->ws, $request->ws, $request->ws, // defects
                        $start, $end, $request->ws, $request->ws, $request->ws  // rejects
                    ]);

                }else{
                    $rawData = DB::connection('mysql_sb')->select("
                        SELECT
                            output_defects.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            userpassword.username AS sewing_line,
                            'END-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            output_defects.defect_status AS status,
                            DATE_FORMAT(output_defects.created_at, '%d-%m-%Y') AS tgl_defect,
                            DATE_FORMAT(output_defects.updated_at, '%d-%m-%Y') AS tgl_rework,
                            output_defect_types.allocation AS proses_type,
                            output_defect_in_out.STATUS AS proses_status,
                            DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                            DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                        FROM output_defects
                        LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects.id
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects.defect_area_id
                        LEFT JOIN output_defect_in_out 
                            ON output_defect_in_out.output_type = 'qc'
                            AND output_defect_in_out.defect_id = output_defects.id
                        WHERE
                            (
                                output_defects.created_at BETWEEN ? AND ?
                                OR
                                output_defects.updated_at BETWEEN ? AND ?
                            )
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        UNION ALL

                        SELECT
                            output_rejects.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            userpassword.username AS sewing_line,
                            'END-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            'Reject Mati' AS status,
                            DATE_FORMAT(output_rejects.created_at, '%d-%m-%Y') AS tgl_defect,
                            '' AS tgl_rework,
                            '' AS proses_type,
                            '' AS proses_status,
                            '' AS tgl_proses_in,
                            '' AS tgl_proses_out
                        FROM output_rejects
                        LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects.reject_area_id
                        WHERE 
                            output_rejects.reject_status = 'mati'
                            AND output_rejects.created_at BETWEEN ? AND ?
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        UNION ALL

                        SELECT
                            output_defects_packing.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            userpassword.username AS sewing_line,
                            'FINISHING-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            output_defects_packing.defect_status AS status,
                            DATE_FORMAT(output_defects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                            DATE_FORMAT(output_defects_packing.updated_at, '%d-%m-%Y') AS tgl_rework,
                            output_defect_types.allocation AS proses_type,
                            output_defect_in_out.status AS proses_status,
                            DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                            DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                        FROM output_defects_packing
                        LEFT JOIN so_det ON so_det.id = output_defects_packing.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN userpassword ON userpassword.username = output_defects_packing.created_by
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects_packing.id
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_defects_packing.defect_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects_packing.defect_area_id
                        LEFT JOIN output_defect_in_out 
                            ON output_defect_in_out.output_type = 'packing'
                            AND output_defect_in_out.defect_id = output_defects_packing.id
                        WHERE
                            (
                                output_defects_packing.created_at BETWEEN ? AND ?
                                OR
                                output_defects_packing.updated_at BETWEEN ? AND ?
                            )
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        UNION ALL

                        SELECT
                            output_rejects_packing.kode_numbering,
                            mastersupplier.Supplier AS buyer,
                            act_costing.kpno AS ws,
                            act_costing.styleno AS style,
                            so_det.color,
                            so_det.size,
                            so_det.dest,
                            output_rejects_packing.created_by AS sewing_line,
                            'FINISHING-LINE' AS dept,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            'Reject Mati' AS status,
                            DATE_FORMAT(output_rejects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                            '' AS tgl_rework,
                            '' AS proses_type,
                            '' AS proses_status,
                            '' AS tgl_proses_in,
                            '' AS tgl_proses_out
                        FROM output_rejects_packing
                        LEFT JOIN so_det ON so_det.id = output_rejects_packing.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects_packing.created_by
                        LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                        LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects_packing.reject_type_id
                        LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects_packing.reject_area_id
                        WHERE 
                            output_rejects_packing.reject_status = 'mati'
                            AND output_rejects_packing.created_at BETWEEN ? AND ?
                            AND (
                                ? IS NULL 
                                OR ? = '' 
                                OR act_costing.kpno = ?
                            )

                        ORDER BY tgl_defect DESC
                    ", [
                        $start, $end, $start, $end, $request->ws, $request->ws, $request->ws,
                        $start, $end, $request->ws, $request->ws, $request->ws,
                        $start, $end, $start, $end, $request->ws, $request->ws, $request->ws,
                        $start, $end, $request->ws, $request->ws, $request->ws
                    ]);
                }

                return response()->json([
                    'data' => $rawData
                ]);
            }
        }

        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');

        return view(
            'sewing.report.report-defect-reject',
            [
                'page' => 'dashboard-sewing-eff',
                "subPageGroup" => "sewing-report",
                "subPage" => "reportDefectReject",
                "orders" => $orders
            ]
        );
    }

    public function export_excel_report_defect_reject(Request $request)
    {
        return Excel::download(new ExportReportDefectReject($request->start_date, $request->end_date, $request->department, $request->ws), 'Laporan Defect & Reject.xlsx');
    }
}
