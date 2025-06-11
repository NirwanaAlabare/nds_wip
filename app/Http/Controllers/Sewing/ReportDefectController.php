<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Exports\Sewing\ReportDefectExport;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ReportDefectController extends Controller
{
    public function index(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        if ($request->department == "_packing") {
            $defect = DefectPacking::selectRaw("
                output_defects_packing.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects_packing.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects_packing.created_at,
                output_defects_packing.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects_packing.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
            })->
            whereRaw($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects_packing.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects_packing.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects_packing.updated_at", "desc");
        } else {
            $defect = Defect::selectRaw("
                output_defects.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects.created_at,
                output_defects.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
            })->
            whereRaw($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects.updated_at", "desc");
        }

        if ($request->ajax()) {
            if ($request->defect_types && count($request->defect_types) > 0) {
                $defect->whereIn("output_defect_types.id", $request->defect_types);
            }

            if ($request->defect_areas && count($request->defect_areas) > 0) {
                $defect->whereIn("output_defect_areas.id", $request->defect_areas);
            }

            if ($request->defect_status && count($request->defect_status) > 0) {
                $defect->whereIn("defect_status", $request->defect_status);
            }

            if ($request->sewing_line && count($request->sewing_line) > 0) {
                $defect->whereIn("userpassword.username", $request->sewing_line);
            }

            if ($request->buyer && count($request->buyer) > 0) {
                $defect->whereIn("mastersupplier.Supplier", $request->buyer);
            }

            if ($request->ws && count($request->ws) > 0) {
                $defect->whereIn("act_costing.kpno", $request->ws);
            }

            if ($request->style && count($request->style) > 0) {
                $defect->whereIn("act_costing.styleno", $request->style);
            }

            if ($request->color && count($request->color) > 0) {
                $defect->whereIn("so_det.color", $request->color);
            }

            if ($request->size && count($request->size) > 0) {
                $defect->whereIn("so_det.size", $request->size);
            }

            if ($request->external_type && count($request->external_type) > 0) {
                $defect->whereIn("output_defect_in_out.type", $request->external_type);
            }

            if ($request->external_in && count($request->external_in) > 0) {
                $defect->whereIn("output_defect_in_out.created_at", $request->external_in);
            }

            if ($request->external_out && count($request->external_out) > 0) {
                $defect->whereIn("output_defect_in_out.reworked_at", $request->external_out);
            }

            return DataTables::eloquent($defect)->toJson();
        }

        $defectTypes = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();
        $defectAreas = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();

        $lines = $defect->get()->groupBy('sewing_line')->keys();
        // $orders = $defect->get()->groupBy('ws')->keys();
        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');
        $styles = $defect->get()->groupBy('style')->keys();
        $suppliers = $defect->get()->groupBy('buyer')->keys();
        $colors = $defect->get()->groupBy('color')->keys();
        $sizes = $defect->get()->groupBy('size')->keys();
        $externalTypes = $defect->get()->groupBy('external_type')->keys();

        return view("sewing.report.report-defect", [
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
            'externalTypes' => $externalTypes
        ]);
    }

    public function filter(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        if ($request->department == "_packing") {
            $defect = DefectPacking::selectRaw("
                output_defects_packing.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects_packing.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects_packing.created_at,
                output_defects_packing.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects_packing.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
            })->
            whereRaw($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects_packing.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects_packing.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects_packing.updated_at", "desc");
        } else {
            $defect = Defect::selectRaw("
                output_defects.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects.created_at,
                output_defects.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
            })->
            whereRaw($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects.updated_at", "desc");
        }

        $lines = $defect->get()->groupBy('sewing_line')->keys();
        // $orders = $defect->get()->groupBy('ws')->keys();
        $orders = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->pluck('kpno');
        $styles = $defect->get()->groupBy('style')->keys();
        $suppliers = $defect->get()->groupBy('buyer')->keys();
        $colors = $defect->get()->groupBy('color')->keys();
        $sizes = $defect->get()->groupBy('size')->keys();
        $externalTypes = $defect->get()->groupBy('external_type')->keys();

        // dd($lines, $orders, $styles, $suppliers, $colors, $sizes, $externalTypes);

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

        if ($request->department == '_packing') {
            $defect = DefectPacking::selectRaw("
                output_defects_packing.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects_packing.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects_packing.created_at,
                output_defects_packing.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects_packing.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
            })->
            whereRaw($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects_packing.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects_packing.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects_packing.updated_at", "desc");
        } else {
            $defect = Defect::selectRaw("
                output_defects.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects.created_at,
                output_defects.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
            })->
            whereRaw($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects.updated_at", "desc");
        }

        if ($request->defect_types && count($request->defect_types) > 0) {
            $defect->whereIn("output_defect_types.id", $request->defect_types);
        }

        if ($request->defect_areas && count($request->defect_areas) > 0) {
            $defect->whereIn("output_defect_areas.id", $request->defect_areas);
        }

        if ($request->defect_status && count($request->defect_status) > 0) {
            $defect->whereIn("defect_status", $request->defect_status);
        }

        if ($request->sewing_line && count($request->sewing_line) > 0) {
            $defect->whereIn("userpassword.username", $request->sewing_line);
        }

        if ($request->buyer && count($request->buyer) > 0) {
            $defect->whereIn("mastersupplier.Supplier", $request->buyer);
        }

        if ($request->ws && count($request->ws) > 0) {
            $defect->whereIn("act_costing.kpno", $request->ws);
        }

        if ($request->style && count($request->style) > 0) {
            $defect->whereIn("act_costing.styleno", $request->style);
        }

        if ($request->color && count($request->color) > 0) {
            $defect->whereIn("so_det.color", $request->color);
        }

        if ($request->size && count($request->size) > 0) {
            $defect->whereIn("so_det.size", $request->size);
        }

        if ($request->external_type && count($request->external_type) > 0) {
            $defect->whereIn("output_defect_in_out.type", $request->external_type);
        }

        if ($request->external_in && count($request->external_in) > 0) {
            $defect->whereIn("output_defect_in_out.created_at", $request->external_in);
        }

        if ($request->external_out && count($request->external_out) > 0) {
            $defect->whereIn("output_defect_in_out.reworked_at", $request->external_out);
        }

        return $defect->count();
    }

    public function reportDefectExport(Request $request) {
        $types = $request->types ? $request->types : ['defect_rate'];
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

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

        // Deprecated
            // $defectReportQuery = "";
            // if (in_array('defect_rate', $types)) {
            //     $defectReportQuery = "
            //         SELECT
            //             output.tgl_output,
            //             output.sewing_line,
            //             output.buyer,
            //             output.ws,
            //             output.style,
            //             output.color,
            //             output.rft,
            //             output.defect,
            //             output.rework,
            //             output.reject,
            //             output.output,
            //             output.mins_prod,
            //             output.mins_avail,
            //             output.cumulative_mins_avail,
            //             coalesce(output.rft/coalesce(coalesce(output.output, 0)+coalesce(output.defect, 0)+coalesce(output.reject, 0),1)*100, 0) rft_rate,
            //             coalesce(output.all_defect/coalesce(coalesce(output.output, 0)+coalesce(output.defect, 0)+coalesce(output.reject, 0),1)*100, 0) defect_rate,
            //             coalesce(output.reject/coalesce(coalesce(output.output, 0)+coalesce(output.all_defect, 0)+coalesce(output.reject, 0),1)*100, 0) reject_rate,
            //             output.mins_prod/output.mins_avail*100 eff,
            //             output.mins_prod/output.cumulative_mins_avail*100 cumulative_eff
            //         FROM (
            //             SELECT
            //                 output.tgl_output,
            //                 output.tgl_plan,
            //                 group_concat(distinct output.sewing_line) sewing_line,
            //                 group_concat(distinct output.ws) ws,
            //                 group_concat(distinct output.style) style,
            //                 group_concat(distinct output.buyer) buyer,
            //                 group_concat(distinct output.color) color,
            //                 SUM(COALESCE(rft, 0)) rft,
            //                 SUM(COALESCE(all_defect, 0)) all_defect,
            //                 SUM(COALESCE(defect, 0)) defect,
            //                 SUM(COALESCE(rework, 0)) rework,
            //                 SUM(COALESCE(reject, 0)) reject,
            //                 SUM(COALESCE(output, 0)) output,
            //                 SUM(COALESCE(output * output.smv, 0)) mins_prod,
            //                 SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
            //                 MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
            //                 MAX(output.last_update) last_update,
            //                 (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
            //                 (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
            //                 MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
            //                 FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
            //             FROM
            //                 (
            //                     SELECT
            //                         DATE( rfts.updated_at ) tgl_output,
            //                         COUNT( rfts.id ) output,
            //                         SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
            //                         MAX(rfts.updated_at) last_update,
            //                         master_plan.id master_plan_id,
            //                         master_plan.tgl_plan,
            //                         master_plan.sewing_line,
            //                         master_plan.man_power,
            //                         master_plan.jam_kerja,
            //                         master_plan.smv,
            //                         mastersupplier.Supplier buyer,
            //                         act_costing.kpno ws,
            //                         act_costing.styleno style,
            //                         so_det.color
            //                     FROM
            //                         output_rfts rfts
            //                         inner join master_plan on master_plan.id = rfts.master_plan_id
            //                         left join so_det on so_det.id = rfts.so_det_id
            //                         left join so on so.id = so_det.id_so
            //                         left join act_costing on act_costing.id = so.id_cost
            //                         left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
            //                     where
            //                         rfts.updated_at >= '".$dateFrom." 00:00:00' AND rfts.updated_at <= '".$dateTo." 23:59:59'
            //                         AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$dateTo."'
            //                         AND master_plan.cancel = 'N'
            //                         ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
            //                         ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
            //                         ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
            //                         ".($color ? "AND so_det.color in (".$color.")" : "")."
            //                         ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
            //                     GROUP BY
            //                         master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), so_det.color
            //                     order by
            //                         tgl_output,
            //                         sewing_line
            //                 ) output
            //                 left join
            //                 (
            //                     SELECT
            //                         DATE( defects.updated_at ) tgl_defect,
            //                         SUM( CASE WHEN defects.defect_status = 'defect' THEN 1 ELSE 0 END ) defect,
            //                         SUM( CASE WHEN defects.defect_status = 'reworked' THEN 1 ELSE 0 END ) rework,
            //                         COUNT( defects.id ) all_defect,
            //                         MAX(defects.updated_at) last_defect,
            //                         sewing_line sewing_line_defect,
            //                         master_plan.id master_plan_id_defect,
            //                         master_plan.tgl_plan tgl_plan_defect,
            //                         DATE(defects.updated_at) tgl_output_defect,
            //                         so_det.color
            //                     FROM
            //                         output_defects defects
            //                         inner join master_plan on master_plan.id = defects.master_plan_id
            //                         left join so_det on so_det.id = defects.so_det_id
            //                         left join so on so.id = so_det.id_so
            //                         left join act_costing on act_costing.id = so.id_cost
            //                         left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
            //                     where
            //                         defects.updated_at >= '".$dateFrom." 00:00:00' AND defects.updated_at <= '".$dateTo." 23:59:59'
            //                         AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$dateTo."'
            //                         AND master_plan.cancel = 'N'
            //                         ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
            //                         ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
            //                         ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
            //                         ".($color ? "AND so_det.color in (".$color.")" : "")."
            //                         ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
            //                     GROUP BY
            //                         master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), so_det.color
            //                     order by
            //                         tgl_defect,
            //                         sewing_line
            //                 ) defect on defect.master_plan_id_defect = output.master_plan_id and defect.tgl_plan_defect = output.tgl_plan and defect.tgl_defect = output.tgl_output and defect.color = output.color
            //                 left join
            //                 (
            //                     SELECT
            //                         DATE( rejects.updated_at ) tgl_reject,
            //                         COUNT( rejects.id ) reject,
            //                         MAX(rejects.updated_at) last_reject,
            //                         sewing_line sewing_line_reject,
            //                         master_plan.id master_plan_id_reject,
            //                         master_plan.tgl_plan tgl_plan_reject,
            //                         DATE(rejects.updated_at) tgl_output_reject,
            //                         so_det.color
            //                     FROM
            //                         output_rejects rejects
            //                         inner join master_plan on master_plan.id = rejects.master_plan_id
            //                         left join so_det on so_det.id = rejects.so_det_id
            //                         left join so on so.id = so_det.id_so
            //                         left join act_costing on act_costing.id = so.id_cost
            //                         left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
            //                     where
            //                         rejects.updated_at >= '".$dateFrom." 00:00:00' AND rejects.updated_at <= '".$dateTo." 23:59:59'
            //                         AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$dateTo."'
            //                         AND master_plan.cancel = 'N'
            //                         ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
            //                         ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
            //                         ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
            //                         ".($color ? "AND so_det.color in (".$color.")" : "")."
            //                         ".($sewingLine ? "AND master_plan.sewing_line in (".$sewingLine.")" : "")."
            //                     GROUP BY
            //                         master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), so_det.color
            //                     order by
            //                         tgl_reject,
            //                         sewing_line
            //                 ) reject on reject.master_plan_id_reject = output.master_plan_id and reject.tgl_plan_reject = output.tgl_plan and reject.tgl_reject = output.tgl_output and reject.color = output.color
            //             GROUP BY
            //                 output.tgl_output,
            //                 output.style
            //         ) output
            //         order by
            //             output.tgl_output,
            //             output.style
            //     ";
            // }

        $defectRateQuery = "";
        if (in_array('defect_rate', $types)) {
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
                                ".($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((rfts.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (rfts.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59')) AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 30 DAY) AND master_plan.tgl_plan <= '".$dateTo."'")."
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
                                ".($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59')) AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 30 DAY) AND master_plan.tgl_plan <= '".$dateTo."'")."
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
                                ".($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((rejects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (rejects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59')) AND master_plan.tgl_plan >= DATE_SUB('".$dateFrom."', INTERVAL 30 DAY) AND master_plan.tgl_plan <= '".$dateTo."'")."
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
                        output.style,
                        output.ws,
                        output.color,
                        output.sewing_line
                ) output
                order by
                    output.tgl_output,
                    output.style,
                    output.ws,
                    output.color,
                    output.sewing_line
            ";
        }

        $topDefectQuery = "";
        if (in_array('top_defect', $types)) {
            $topDefectQuery = "
                SELECT
                    CONCAT(userpassword.username, act_costing.styleno, so_det.color, output_defect_types.id) as grouping,
                    CONCAT(userpassword.username, output_defect_types.id) as line_grouping,
                    CONCAT(act_costing.styleno, output_defect_types.id) as style_grouping,
                    userpassword.username sewing_line,
                    act_costing.styleno style,
                    so_det.color,
                    DATE( output_defects.updated_at ) tanggal,
                    output_defect_types.defect_type,
                    COUNT( output_defects.id ) total_defect
                FROM
                    output_defects".$request->department." as output_defects
                    LEFT JOIN output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    ".(
                        $request->department == "_packing" ?
                        "
                            LEFT JOIN userpassword ON userpassword.username = output_defects.created_by
                        "
                        :
                        "
                            LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                            LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                        "
                    )."
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                WHERE
                    ".($request->base_ws ? "act_costing.kpno = '".$request->base_ws."'" : "((output_defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59')) ")."
                    ".($buyer ? "AND mastersupplier.Supplier in (".$buyer.")" : "")."
                    ".($ws ? "AND act_costing.kpno in (".$ws.")" : "")."
                    ".($style ? "AND act_costing.styleno in (".$style.")" : "")."
                    ".($color ? "AND so_det.color in (".$color.")" : "")."
                    ".($sewingLine ? "AND userpassword.username in (".$sewingLine.")" : "")."
                GROUP BY
                    userpassword.username,
                    act_costing.styleno,
                    so_det.color,
                    output_defect_types.id,
                    DATE(output_defects.updated_at)
                ORDER BY
                    userpassword.username,
                    act_costing.styleno,
                    so_det.color,
                    DATE(output_defects.updated_at),
                    COUNT(output_defects.id) desc
            ";
        }

        if ($defectRateQuery || $topDefectQuery) {
            return Excel::download(new ReportDefectExport($defectRateQuery, $topDefectQuery, $dateFrom, $dateTo, $ws, $style, $color, $sewingLine, $request->department), 'report defect.xlsx');
        }

        return array(
            'status' => 400,
            'message' => 'Terjadi kesalahan',
        );
    }

    public function defectRate(Request $request) {
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
         if ($request->department == "_packing") {
            $defect = DefectPacking::selectRaw("
                output_defects_packing.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects_packing.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects_packing.created_at,
                output_defects_packing.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects_packing.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects_packing.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'packing'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects_packing.id");
            })->
            whereRaw("act_costing.kpno = '".$request->base_ws."'")->
            orderBy("output_defects_packing.updated_at", "desc")->
            get();
        } else {
            $defect = Defect::selectRaw("
                output_defects.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                so_det.dest,
                userpassword.username as sewing_line,
                output_defect_types.defect_type defect_type,
                output_defect_areas.defect_area defect_area,
                output_defects.defect_status,
                output_defect_in_out.status external_status,
                output_defect_in_out.id external_id,
                output_defect_in_out.type external_type,
                output_defect_in_out.created_at external_in,
                output_defect_in_out.reworked_at external_out,
                output_reworks.id rework_id,
                output_defects.created_at,
                output_defects.updated_at
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("output_reworks", "output_reworks.defect_id", "=", "output_defects.id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
            leftJoin("output_defect_in_out", function($join) {
                $join->on("output_defect_in_out.output_type", "=", DB::raw("'qc'"));
                $join->on("output_defect_in_out.defect_id", "=", "output_defects.id");
            })->
            whereRaw("act_costing.kpno = '".$request->base_ws."'")->
            orderBy("output_defects.updated_at", "desc")->
            get();
        }

        $dateFrom = ($defect->last() ? $defect->last()->created_at->format('Y-m-d') : date("Y-m-d"));
        $dateTo = ($defect->first() ? $defect->first()->updated_at->format('Y-m-d') : date("Y-m-d"));

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
