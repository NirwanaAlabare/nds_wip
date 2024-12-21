<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use App\Exports\OutputExport;
use App\Exports\OutputExportCustomRange;
use App\Exports\ProductionExport;
use App\Exports\ProductionAllExport;
use App\Exports\ProductionDefectExport;
use App\Exports\OrderOutputExport;
use App\Exports\Sewing\DefectInOutExport;
use DB;
use Excel;

class ReportController extends Controller
{
    public function index($type) {
        return view('sewing.report', [
            'type' => $type,
            'subPageGroup' => 'sewing-sewing', 'subPage' => 'sewing-'.$type, 'page' => 'dashboard-sewing-eff'
        ]);
    }

    public function defectInOut() {
        return view('sewing.report-defect-in-out', [
            'subPageGroup' => 'sewing-defect', 'subPage' => 'report-defect-in-out', 'page' => 'dashboard-sewing-eff'
        ]);
    }

    public function exportOutput(Request $request) {
        $subtype = $request->subtype;
        $date = $request->date;
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $range = $request->range;

        if ($range == "custom") {
            return Excel::download(new OutputExportCustomRange($dateFrom, $dateTo, $subtype), 'output_export.xlsx');
        }

        return Excel::download(new OutputExport($date, $subtype), 'output_export.xlsx');
    }

    public function exportProduction(Request $request) {
        $date = $request->date;
        $line = $request->line;

        return Excel::download(new ProductionExport($date, $line), 'production_excel.xlsx');
    }

    public function exportProductionDefect(Request $request) {
        $date = $request->date;
        $line = $request->line;

        return Excel::download(new ProductionDefectExport($date, $line), 'production_defect_excel.xlsx');
    }

    public function exportProductionAll(Request $request) {
        $date = $request->date;

        return (new ProductionAllExport($date))->download('production_all_excel.xlsx');
    }

    public function exportOrderOutput(Request $request) {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $outputType = $request->outputType;
        $groupBy = $request->groupBy;
        $order = $request->order;
        $buyer = $request->buyer;

        return Excel::download(new OrderOutputExport($dateFrom, $dateTo, $outputType, $groupBy, $order, $buyer), 'order_output.xlsx');
    }

    public function exportDefectInOut(Request $request) {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $type = $request->type;

        return Excel::download(new DefectInOutExport($dateFrom, $dateTo, $type), 'defectInOut.xlsx');
    }
}
