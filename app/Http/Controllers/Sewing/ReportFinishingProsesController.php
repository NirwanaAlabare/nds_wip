<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
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
        
        if ($request->ajax()) {
            if ($start_date === null || $end_date === null) {
                return response()->json(['data' => []]);
            } else {
                if ($request->department == "_packing") {
                    $rawData = DefectPacking::selectRaw("
                        output_defects_packing.kode_numbering,
                        mastersupplier.Supplier buyer,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size
                    ")
                    ->leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")
                    ->leftJoin("so", "so.id", "=", "so_det.id_so")
                    ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
                    ->leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")
                    ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")
                    ->when($request->ws != "", function ($q) use ($request) {
                        $q->where('act_costing.kpno', $request->ws);
                    })
                    ->where(function ($q) use ($start_date, $end_date) {
                        $q->whereBetween('output_defects_packing.created_at', [$start_date.' 00:00:00', $end_date.' 23:59:59'])
                        ->orWhereBetween('output_defects_packing.updated_at', [$start_date.' 00:00:00', $end_date.' 23:59:59']);
                    })
                    ->orderBy("output_defects_packing.updated_at", "desc")
                    ->get();
                } else {
                    $rawData = Defect::selectRaw("
                        output_defects.kode_numbering,
                        mastersupplier.Supplier buyer,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size
                    ")
                    ->leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")
                    ->leftJoin("so", "so.id", "=", "so_det.id_so")
                    ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
                    ->leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")
                    ->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")
                    ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")
                    ->when($request->ws != "", function ($q) use ($request) {
                        $q->where('act_costing.kpno', $request->ws);
                    })
                    ->where(function ($q) use ($start_date, $end_date) {
                        $q->whereBetween('output_defects.created_at', [$start_date.' 00:00:00', $end_date.' 23:59:59'])
                        ->orWhereBetween('output_defects.updated_at', [$start_date.' 00:00:00', $end_date.' 23:59:59']);
                    })
                    ->orderBy("output_defects.updated_at", "desc")
                    ->get();
                }

                return response()->json([
                    'data' => $rawData
                ]);
            }
        }

        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');

        return view(
            'sewing.report.report-finishing-proses',
            [
                'page' => 'dashboard-sewing-eff',
                "subPageGroup" => "sewing-report",
                "subPage" => "reportFinishingProses",
                "orders" => $orders
            ]
        );
    }

    public function export_excel_report_finishing_proses(Request $request)
    {
        return Excel::download(new ExportReportFinishingProses($request->start_date, $request->end_date, $request->department, $request->ws), 'Laporan Finishing Proses.xlsx');
    }
}
