<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\LoadingLine;
use App\Models\LoadingLinePlan;
use App\Models\YearSequence;
use Illuminate\Http\Request;
use DB;

class DcToolsController extends Controller
{
    public function index() {
        return view('dc.tools.tools', [
            "page" => "dashboard-dc"
        ]);
    }

    public function emptyOrderLoading(Request $request) {
        $year = $request->input('year');
        $month = $request->input('month');
        $date = $request->input('date');

        $emptyOrder = LoadingLine::selectRaw("GROUP_CONCAT(loading_line.id) as loading_line_ids, loading_line.tanggal_loading, loading_line.line_id, master_sb_ws.buyer, master_sb_ws.id_act_cost as act_costing_id, stocker_input.act_costing_ws, master_sb_ws.styleno as style, stocker_input.color, MIN(loading_plan_id) as loading_plan_id")->
            leftJoin("stocker_input", "stocker_input.id", "=", "loading_line.stocker_id")->
            leftJoin("loading_line_plan", "loading_line_plan.id", "=", "loading_line.loading_plan_id")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
            whereRaw("(loading_line_plan.act_costing_id IS NULL OR loading_line_plan.act_costing_id = '')")->
            groupBy("stocker_input.act_costing_ws", "stocker_input.color", "loading_line.line_id", "loading_line.tanggal_loading")->
            get();

        if ($emptyOrder->count() < 1) {
            return array(
                'status' => 200,
                'message' => 'Tidak ada data yang perlu diubah',
            );
        }

        $success = [];
        $fails = [];
        foreach ($emptyOrder as $eo) {
            $loadingLinePlan = LoadingLinePlan::where("act_costing_id", $eo->act_costing_ws)->where("color", $eo->color)->where("line_id", $eo->line_id)->where("tanggal", $eo->tanggal_loading)->first();

            if ($loadingLinePlan) {
                if (strlen($eo->loading_line_ids) > 0) {
                    $updateLoadingLine = LoadingLine::whereRaw("id in (".$eo->loading_line_ids.")")->update([
                        "loading_plan_id" => $loadingLinePlan->id
                    ]);

                    if ($updateLoadingLine) {
                        array_push($success, $eo->loading_line_ids);
                    } else {
                        array_push($fails, $eo->loading_line_ids);
                    }
                }
            } else {
                $updateLoadingLinePlan = LoadingLinePlan::where("id", $eo->loading_plan_id)->update([
                    "buyer" => $eo->buyer,
                    "act_costing_id" => $eo->act_costing_id,
                    "act_costing_ws" => $eo->act_costing_ws,
                    "style" => $eo->style,
                    "color" => $eo->color
                ]);

                if (strlen($eo->loading_line_ids) > 0) {
                    $updateLoadingLine = LoadingLine::whereRaw("id in (".$eo->loading_line_ids.")")->update([
                        "loading_plan_id" => $eo->loading_plan_id
                    ]);

                    if ($updateLoadingLinePlan && $updateLoadingLine) {
                        array_push($success, $eo->loading_line_ids);
                    } else {
                        array_push($fails, $eo->loading_line_ids);
                    }
                }
            }
        }

        return array(
            'status' => 200,
            'message' => 'Berhasil mengubah '.count($success).' data <br> Gagal mengubah '.count($fails).' data',
        );
    }
}
