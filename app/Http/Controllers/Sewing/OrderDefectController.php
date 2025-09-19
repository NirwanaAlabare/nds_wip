<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use DB;

class OrderDefectController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->
                selectRaw('Id_Supplier as id, Supplier as name')->
                leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
                leftJoin('master_plan', 'master_plan.id_ws', '=', 'act_costing.id')->
                where('mastersupplier.tipe_sup', 'C')->
                where('master_plan.cancel', 'N')->
                whereRaw("tgl_plan between '".$request->dateFrom."' AND '".$request->dateTo."'")->
                orderBy('Supplier', 'ASC')->
                groupBy('Id_Supplier', 'Supplier')->
                get();

            return $suppliers;
        }

        $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            leftJoin('master_plan', 'master_plan.id_ws', '=', 'act_costing.id')->
            where('mastersupplier.tipe_sup', 'C')->
            where('master_plan.cancel', 'N')->
            whereRaw('tgl_plan between cast((now() - interval 14 day) as date) AND cast(CURRENT_DATE() as date)')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return view('sewing.order-defects', [
            'suppliers' => $suppliers,
            "subPageGroup" => "sewing-defect",
            "subPage" => "sewing-pareto",
            "page" => "dashboard-sewing-eff"
        ]);
    }

    public function getOrderDefects($buyerId, $dateFrom, $dateTo, $type) {
        if ($type == "packing") {
            $orderDefectsQuery = DefectPacking::selectRaw('count(output_defects_packing.id) as total_defect, COALESCE(output_defect_types.defect_type, "-") defect_type')
                ->leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')
                ->leftJoin('master_plan', 'master_plan.id', '=', 'output_defects_packing.master_plan_id')
                ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
                ->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer');

            if ($buyerId != "all") {
                $orderDefectsQuery->where('mastersupplier.Id_Supplier', $buyerId);
            }

            $orderDefects = $orderDefectsQuery->where('master_plan.cancel', 'N')
                ->whereRaw("output_defects_packing.created_at between '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'")
                ->groupBy('output_defect_types.id', 'output_defect_types.defect_type')
                ->orderBy('total_defect', 'desc')
                ->limit(10)
                ->get();
        } else {
            $orderDefectsQuery = Defect::selectRaw('count(output_defects.id) as total_defect, COALESCE(output_defect_types.defect_type, "-") defect_type')
                ->leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects.defect_type_id')
                ->leftJoin('master_plan', 'master_plan.id', '=', 'output_defects.master_plan_id')
                ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
                ->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer');

            if ($buyerId != "all") {
                $orderDefectsQuery->where('mastersupplier.Id_Supplier', $buyerId);
            }

            $orderDefects = $orderDefectsQuery->where('master_plan.cancel', 'N')
                ->whereRaw("output_defects.created_at between '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'")
                ->groupBy('output_defect_types.id', 'output_defect_types.defect_type')
                ->orderBy('total_defect', 'desc')
                ->limit(10)
                ->get();
        }

        return json_encode($orderDefects);
    }
}
