<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectPacking;
use App\Models\SignalBit\RejectIn;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Exports\Sewing\ReportRejectExport;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ReportRejectController extends Controller
{
    public function index(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $defectTypeFilters = "";
        if ($request->defect_types) {
            $defectTypeFilters = "AND output_defect_types.id in (".addQuotesAround(implode("\r", $request->defect_types)).")";
        }

        $reject = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                mastersupplier.Supplier as buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                reject_detail.defect_types_check,
                reject_detail.defect_areas_check,
                COUNT(output_reject_in.id) qty
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id where output_reject_in_detail.id is not null ".$defectTypeFilters." group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in.id IS NOT NULL AND
                    output_reject_in.status = 'rejected'
                    ".($request->base_ws ? "AND act_costing.kpno = '".$request->base_ws."'" : "")."
                    ".($request->department && $request->department != "all" ? "AND output_reject_in.output_type = '".$request->department."'" : "")."
                )
            ")->
            groupByRaw("DATE(output_reject_in.created_at), output_reject_in.so_det_id, reject_detail.defect_types_check");

        if ($request->ajax()) {

            if ($request->defect_status && count($request->defect_status) > 0) {
                $reject->whereIn("output_reject_in.status", $request->defect_status);
            }

            if ($request->sewing_line && count($request->sewing_line) > 0) {
                $reject->whereIn("master_plan.sewing_line", $request->sewing_line);
            }

            if ($request->buyer && count($request->buyer) > 0) {
                $reject->whereIn("mastersupplier.Supplier", $request->buyer);
            }

            if ($request->ws && count($request->ws) > 0) {
                $reject->whereIn("act_costing.kpno", $request->ws);
            }

            if ($request->style && count($request->style) > 0) {
                $reject->whereIn("act_costing.styleno", $request->style);
            }

            if ($request->color && count($request->color) > 0) {
                $reject->whereIn("so_det.color", $request->color);
            }

            if ($request->size && count($request->size) > 0) {
                $reject->whereIn("so_det.size", $request->size);
            }

            return DataTables::eloquent($reject)->toJson();
        }

        $defectTypes = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();
        $defectAreas = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();

        $lines = $reject->get()->groupBy('sewing_line')->keys();
        // $orders = $reject->get()->groupBy('ws')->keys();
        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');
        $styles = $reject->get()->groupBy('style')->keys();
        $suppliers = $reject->get()->groupBy('buyer')->keys();
        $colors = $reject->get()->groupBy('color')->keys();
        $sizes = $reject->get()->groupBy('size')->keys();

        return view("sewing.report.report-reject", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'subPageGroup' => 'sewing-defect',
            'subPage' => 'report-defect',
            'page' => 'dashboard-sewing-eff',
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'lines' => $lines,
            'orders' => $orders,
            'styles' => $styles,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    public function filter(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $reject = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                userpassword.username as sewing_line,
                mastersupplier.Supplier as buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                reject_detail.defect_types_check,
                reject_detail.defect_areas_check,
                COUNT(output_reject_in.id) qty
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in.id IS NOT NULL AND
                    output_reject_in.status = 'rejected'
                    ".($request->base_ws ? "AND act_costing.kpno = '".$request->base_ws."'" : "")."
                    ".($request->department && $request->department != "all" ? "AND output_reject_in.output_type = '".$request->department."'" : "")."
                )
            ")->
            groupByRaw("DATE(output_reject_in.created_at), output_reject_in.so_det_id, reject_detail.defect_types_check");

        $lines = $reject->get()->groupBy('sewing_line')->keys();
        // $orders = $reject->get()->groupBy('ws')->keys();
        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');
        $styles = $reject->get()->groupBy('style')->keys();
        $suppliers = $reject->get()->groupBy('buyer')->keys();
        $colors = $reject->get()->groupBy('color')->keys();
        $sizes = $reject->get()->groupBy('size')->keys();
        $externalTypes = $reject->get()->groupBy('external_type')->keys();

        return array(
            'lines' => $lines,
            'orders' => $orders,
            'styles' => $styles,
            'suppliers' => $suppliers,
            'colors' => $colors,
            'sizes' => $sizes,
            'externalTypes' => $externalTypes
        );
    }

    public function total(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $defectTypeFilters = "";
        if ($request->defect_types) {
            $defectTypeFilters = "AND output_defect_types.id in (".addQuotesAround(implode("\r", $request->defect_types)).")";
        }

        $reject = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                userpassword.username as sewing_line,
                mastersupplier.Supplier as buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                reject_detail.defect_types_check,
                reject_detail.defect_areas_check,
                COUNT(output_reject_in.id) total_reject
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id where output_reject_in_detail.id is not null ".$defectTypeFilters." group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in.id IS NOT NULL AND
                    output_reject_in.status = 'rejected'
                    ".($request->base_ws ? "AND act_costing.kpno = '".$request->base_ws."'" : "")."
                    ".($request->department && $request->department != "all" ? "AND output_reject_in.output_type = '".$request->department."'" : "")."
                )
            ")->
            groupByRaw("DATE(output_reject_in.created_at), output_reject_in.so_det_id, reject_detail.defect_types_check")->
            get();

        if ($request->defect_status && count($request->defect_status) > 0) {
            $reject->whereIn("output_reject_in.status", $request->defect_status);
        }

        if ($request->sewing_line && count($request->sewing_line) > 0) {
            $reject->whereIn("master_plan.sewing_line", $request->sewing_line);
        }

        if ($request->buyer && count($request->buyer) > 0) {
            $reject->whereIn("mastersupplier.Supplier", $request->buyer);
        }

        if ($request->ws && count($request->ws) > 0) {
            $reject->whereIn("act_costing.kpno", $request->ws);
        }

        if ($request->style && count($request->style) > 0) {
            $reject->whereIn("act_costing.styleno", $request->style);
        }

        if ($request->color && count($request->color) > 0) {
            $reject->whereIn("so_det.color", $request->color);
        }

        if ($request->size && count($request->size) > 0) {
            $reject->whereIn("so_det.size", $request->size);
        }

        return $reject->sum("total_reject");
    }

    public function top(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $defectTypeFilters = "";
        if ($request->defect_types) {
            $defectTypeFilters = "AND output_defect_types.id in (".addQuotesAround(implode("\r", $request->defect_types)).")";
        }

        $reject = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                userpassword.username as sewing_line,
                mastersupplier.Supplier as buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                reject_detail.defect_types_check,
                reject_detail.defect_areas_check,
                COUNT(output_reject_in.id) total_reject
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id where output_reject_in_detail.id is not null ".$defectTypeFilters." group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in.id IS NOT NULL AND
                    output_reject_in.status = 'rejected'
                    ".($request->base_ws ? "AND act_costing.kpno = '".$request->base_ws."'" : "")."
                    ".($request->department && $request->department != "all" ? "AND output_reject_in.output_type = '".$request->department."'" : "")."
                )
            ")->
            groupByRaw("DATE(output_reject_in.created_at), reject_detail.defect_types_check");

        if ($request->defect_status && count($request->defect_status) > 0) {
            $reject->whereIn("output_reject_in.status", $request->defect_status);
        }

        if ($request->sewing_line && count($request->sewing_line) > 0) {
            $reject->whereIn("master_plan.sewing_line", $request->sewing_line);
        }

        if ($request->buyer && count($request->buyer) > 0) {
            $reject->whereIn("mastersupplier.Supplier", $request->buyer);
        }

        if ($request->ws && count($request->ws) > 0) {
            $reject->whereIn("act_costing.kpno", $request->ws);
        }

        if ($request->style && count($request->style) > 0) {
            $reject->whereIn("act_costing.styleno", $request->style);
        }

        if ($request->color && count($request->color) > 0) {
            $reject->whereIn("so_det.color", $request->color);
        }

        if ($request->size && count($request->size) > 0) {
            $reject->whereIn("so_det.size", $request->size);
        }

        $rejectData = $reject->get();

        return json_encode($rejectData);
    }

    public function reportRejectExport(Request $request) {
        ini_set("max_execution_time", 3600);

        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        $defectTypeFilters = "";
        if ($request->defect_types) {
            $defectTypeFilters = addQuotesAround(implode("\r", $request->defect_types));
        }

        return Excel::download(new ReportRejectExport($dateFrom, $dateTo, $defectTypeFilters, $request->base_ws, $request->department, $request->defect_status, $request->sewing_line, $request->buyer, $request->ws, $request->style, $request->color, $request->size), 'report defect.xlsx');
    }

    public function defectRate(Request $request) {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $buyer = $request->buyer;
        $ws = $request->ws;
        $style = $request->style;
        $color = $request->color;
        $sewingLine = $request->sewingLine;

        $defectRateQuery = "
            SELECT
                output.tgl_output,
                output.sewing_line,
                output.buyer,
                output.ws,
                output.style,
                output.color,
                output.rft,
                output.defect,
                output.rework,
                output.reject,
                output.output,
                output.mins_prod,
                output.mins_avail,
                output.cumulative_mins_avail,
                coalesce(output.rft/coalesce(coalesce(output.output, 0)+coalesce(output.defect, 0)+coalesce(output.reject, 0),1)*100, 0) rft_rate,
                coalesce(output.all_defect/coalesce(coalesce(output.output, 0)+coalesce(output.defect, 0)+coalesce(output.reject, 0),1)*100, 0) defect_rate,
                coalesce(output.reject/coalesce(coalesce(output.output, 0)+coalesce(output.all_defect, 0)+coalesce(output.reject, 0),1)*100, 0) reject_rate,
                output.mins_prod/output.mins_avail*100 eff,
                output.mins_prod/output.cumulative_mins_avail*100 cumulative_eff
            FROM (
                SELECT
                    output.tgl_output,
                    output.tgl_plan,
                    group_concat(distinct output.sewing_line) sewing_line,
                    group_concat(distinct output.ws) ws,
                    group_concat(distinct output.style) style,
                    group_concat(distinct output.buyer) buyer,
                    group_concat(distinct output.color) color,
                    SUM(COALESCE(rft, 0)) rft,
                    SUM(COALESCE(all_defect, 0)) all_defect,
                    SUM(COALESCE(defect, 0)) defect,
                    SUM(COALESCE(rework, 0)) rework,
                    SUM(COALESCE(reject, 0)) reject,
                    SUM(COALESCE(output, 0)) output,
                    SUM(COALESCE(output * output.smv, 0)) mins_prod,
                    SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                    MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                    MAX(output.last_update) last_update,
                    (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                    (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                    MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                    FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                FROM
                    (
                        SELECT
                            DATE( rfts.updated_at ) tgl_output,
                            COUNT( rfts.id ) output,
                            SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                            MAX(rfts.updated_at) last_update,
                            master_plan.id master_plan_id,
                            master_plan.tgl_plan,
                            master_plan.sewing_line,
                            master_plan.man_power,
                            master_plan.jam_kerja,
                            master_plan.smv,
                            mastersupplier.Supplier buyer,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color
                        FROM
                            output_rfts".$request->department." rfts
                            inner join master_plan on master_plan.id = rfts.master_plan_id
                            left join so_det on so_det.id = rfts.so_det_id
                            left join so on so.id = so_det.id_so
                            left join act_costing on act_costing.id = so.id_cost
                            left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        where
                            rfts.updated_at >= '".$dateFrom." 00:00:00' AND rfts.updated_at <= '".$dateTo." 23:59:59'
                            AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$dateTo."'
                            AND master_plan.cancel = 'N'
                            ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
                            ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
                            ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
                            ".($color ? "AND so_det.color in (".$color.")" : "")."
                            ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
                        GROUP BY
                            master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), so_det.color
                        order by
                            tgl_output,
                            sewing_line
                    ) output
                    left join
                    (
                        SELECT
                            DATE( defects.updated_at ) tgl_defect,
                            SUM( CASE WHEN defects.defect_status = 'defect' THEN 1 ELSE 0 END ) defect,
                            SUM( CASE WHEN defects.defect_status = 'reworked' THEN 1 ELSE 0 END ) rework,
                            COUNT( defects.id ) all_defect,
                            MAX(defects.updated_at) last_defect,
                            sewing_line sewing_line_defect,
                            master_plan.id master_plan_id_defect,
                            master_plan.tgl_plan tgl_plan_defect,
                            DATE(defects.updated_at) tgl_output_defect,
                            so_det.color
                        FROM
                            output_defects".$request->department." defects
                            inner join master_plan on master_plan.id = defects.master_plan_id
                            left join so_det on so_det.id = defects.so_det_id
                            left join so on so.id = so_det.id_so
                            left join act_costing on act_costing.id = so.id_cost
                            left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        where
                            defects.updated_at >= '".$dateFrom." 00:00:00' AND defects.updated_at <= '".$dateTo." 23:59:59'
                            AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$dateTo."'
                            AND master_plan.cancel = 'N'
                            ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
                            ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
                            ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
                            ".($color ? "AND so_det.color in (".$color.")" : "")."
                            ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
                        GROUP BY
                            master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), so_det.color
                        order by
                            tgl_defect,
                            sewing_line
                    ) defect on defect.master_plan_id_defect = output.master_plan_id and defect.tgl_plan_defect = output.tgl_plan and defect.tgl_defect = output.tgl_output and defect.color = output.color
                    left join
                    (
                        SELECT
                            DATE( rejects.updated_at ) tgl_reject,
                            COUNT( rejects.id ) reject,
                            MAX(rejects.updated_at) last_reject,
                            sewing_line sewing_line_reject,
                            master_plan.id master_plan_id_reject,
                            master_plan.tgl_plan tgl_plan_reject,
                            DATE(rejects.updated_at) tgl_output_reject,
                            so_det.color
                        FROM
                            output_rejects".$request->department." rejects
                            inner join master_plan on master_plan.id = rejects.master_plan_id
                            left join so_det on so_det.id = rejects.so_det_id
                            left join so on so.id = so_det.id_so
                            left join act_costing on act_costing.id = so.id_cost
                            left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        where
                            rejects.updated_at >= '".$dateFrom." 00:00:00' AND rejects.updated_at <= '".$dateTo." 23:59:59'
                            AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$dateTo."'
                            AND master_plan.cancel = 'N'
                            ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
                            ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
                            ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
                            ".($color ? "AND so_det.color in (".$color.")" : "")."
                            ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
                        GROUP BY
                            master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), so_det.color
                        order by
                            tgl_reject,
                            sewing_line
                    ) reject on reject.master_plan_id_reject = output.master_plan_id and reject.tgl_plan_reject = output.tgl_plan and reject.tgl_reject = output.tgl_output and reject.color = output.color
                GROUP BY
                    output.tgl_output,
                    output.style
            ) output
            order by
                output.tgl_output,
                output.style
        ";

        $defectRates = collect(DB::connection("mysql_sb")->select($defectRateQuery));

        return views('sewing.defect.defect-rate', [
            'defectRates' => $defectRates,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'ws' => $ws,
            'style' => $style,
            'color' => $color,
            'sewingLine' => $sewingLine,
        ]);
    }

    public function updateDateFrom(Request $request) {
        $reject = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                userpassword.username as sewing_line,
                mastersupplier.Supplier as buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                GROUP_CONCAT(output_defect_types_reject.defect_type SEPARATOR ' , ') defect_types_check,
                GROUP_CONCAT(output_defect_areas_reject.defect_area SEPARATOR ' , ') defect_areas_check,
                GROUP_CONCAT(CONCAT_WS(' // ', output_defect_types_reject.defect_type, output_reject_in_detail.reject_area_x, output_reject_in_detail.reject_area_y) SEPARATOR ' | ') reject_area_position,
                COUNT(output_reject_in.id) total_reject
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin("output_reject_in_detail", "output_reject_in_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("output_defect_types as output_defect_types_reject", "output_defect_types_reject.id", "=", "output_reject_in_detail.reject_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_reject", "output_defect_areas_reject.id", "=", "output_reject_in_detail.reject_area_id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$request->dateFrom." 00:00:00", $request->dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in.id IS NOT NULL AND
                    output_reject_in.status = 'rejected'
                    ".($request->base_ws ? "AND act_costing.kpno = '".$request->base_ws."'" : "")."
                    ".($request->department && $request->department != "all" ? "AND output_reject_in.output_type = '".$request->department."'" : "")."
                )
            ")->
            groupByRaw("DATE(output_reject_in.created_at), output_reject_in.so_det_id, GROUP_CONCAT(output_defect_types_reject.defect_type SEPARATOR ' , ')")->
            get();

        $dateFrom = ($reject->last() ? $reject->last()->tanggal : date("Y-m-d"));
        $dateTo = ($reject->first() ? $reject->first()->tanggal : date("Y-m-d"));

        return response()->json([
            'ws' => $request->base_ws,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function defectMap(Request $request) {
        $defectTypes = DefectType::whereRaw("(hidden != 'Y' OR hidden is NULL)")->get();
        $defectAreas = DefectArea::whereRaw("(hidden != 'Y' OR hidden is NULL)")->get();

        return view("sewing.defect.defect-map", [
            'page' => 'dashboard-sewing-eff',
            'subPageGroup' => 'sewing-defect',
            'subPage' => 'defect-map',
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas
        ]);
    }

    public function defectMapData(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d', strtotime('-7 days'));
        $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

        $sewingLine = "";
        if ($request->sewing_line && count($request->sewing_line) > 0) {
            $sewingLine = addQuotesAround(implode("\n", $request->sewing_line));
        }

        $buyer = "";
        if ($request->buyer && count($request->buyer) > 0) {
            $buyer = addQuotesAround(implode("\n", $request->buyer));
        }

        $ws = "";
        if ($request->ws && count($request->ws) > 0) {
            $ws = addQuotesAround(implode("\n", $request->ws));
        }

        $style = "";
        if ($request->style && count($request->style) > 0) {
            $style = addQuotesAround(implode("\n", $request->style));
        }

        $color = "";
        if ($request->color && count($request->color) > 0) {
            $color = addQuotesAround(implode("\n", $request->color));
        };

        if ($request->department == "_packing") {
            $defect = DefectPacking::select(
                "act_costing.id",
                "act_costing.kpno",
                "act_costing.styleno",
                "master_plan.gambar",
                "output_defects_packing.defect_type_id",
                "output_defects_packing.defect_area_id",
                "output_defects_packing.defect_area_x",
                "output_defects_packing.defect_area_y",
                "output_defect_types.defect_type"
            )->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->
            whereBetween("output_defects_packing.updated_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                output_defects_packing.id is not null
                ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
                ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
                ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
                ".($color ? "AND so_det.color in (".$color.")" : "")."
                ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
            ");
            if ($request->defect_types && count($request->defect_types) > 0) {
                $defect->whereIn("output_defect_types.id", $request->defect_types);
            }
            if ($request->defect_areas && count($request->defect_areas) > 0) {
                $defect->whereIn("output_defect_areas.id", $request->defect_areas);
            }
        } else {
            $defect = Defect::select(
                "act_costing.id",
                "act_costing.kpno",
                "act_costing.styleno",
                "master_plan.gambar",
                "output_defects.defect_type_id",
                "output_defects.defect_area_id",
                "output_defects.defect_area_x",
                "output_defects.defect_area_y",
                "output_defect_types.defect_type"
            )->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
            whereBetween("output_defects.updated_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                output_defects.id is not null
                ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
                ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
                ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
                ".($color ? "AND so_det.color in (".$color.")" : "")."
                ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
            ");
            if ($request->defect_types && count($request->defect_types) > 0) {
                $defect->whereIn("output_defect_types.id", $request->defect_types);
            }
            if ($request->defect_areas && count($request->defect_areas) > 0) {
                $defect->whereIn("output_defect_areas.id", $request->defect_areas);
            }
        }

        $defectMap = $defect->get();

        return json_encode($defectMap);
    }
}
