<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ReportDefectController extends Controller
{
    public function index(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

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
            whereRaw("((output_defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects.updated_at", "desc");

        if ($request->ajax()) {
            if ($request->defect_types && count($request->defect_types) > 0) {
                $defect->whereIn("output_defect_types.id", $request->defect_types);
            }

            if ($request->defect_areas && count($request->defect_areas) > 0) {
                $defect->whereIn("output_defect_areas.id", $request->defect_areas);
            }

            if ($request->defect_status && count($request->defect_status) > 0) {
                $defect->whereIn("output_defects.defect_status", $request->defect_status);
            }

            if ($request->sewing_line && count($request->sewing_line) > 0) {
                $defect->whereIn("userpassword.username", $request->sewing_line);
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

            // dd($defect->toSql(), $request->defect_types, $request->defect_areas);

            return DataTables::eloquent($defect)->toJson();
        }

        $defectTypes = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();
        $defectAreas = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();

        $lines = $defect->get()->groupBy('sewing_line')->keys();
        $orders = $defect->get()->groupBy('ws')->keys();
        $styles = $defect->get()->groupBy('style')->keys();
        $suppliers = $defect->get()->groupBy('buyer')->keys();
        $colors = $defect->get()->groupBy('color')->keys();
        $sizes = $defect->get()->groupBy('size')->keys();
        $externalTypes = $defect->get()->groupBy('external_type')->keys();

        return view("sewing.report.report-defect", [
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

    public function total(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

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
            whereRaw("((output_defects.created_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59') or (output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'))")->
            orderBy("output_defects.updated_at", "desc");

        if ($request->defect_types && count($request->defect_types) > 0) {
            $defect->whereIn("output_defect_types.id", $request->defect_types);
        }

        if ($request->defect_areas && count($request->defect_areas) > 0) {
            $defect->whereIn("output_defect_areas.id", $request->defect_areas);
        }

        if ($request->defect_status && count($request->defect_status) > 0) {
            $defect->whereIn("output_defects.defect_status", $request->defect_status);
        }

        if ($request->sewing_line && count($request->sewing_line) > 0) {
            $defect->whereIn("userpassword.username", $request->sewing_line);
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
}
