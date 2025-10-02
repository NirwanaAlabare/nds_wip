<?php

namespace App\Services;

use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use App\Models\Stocker\YearSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class SewingService
{
    public function missMasterPlan($numberingList = null, $updateOrigin = true)
    {
        ini_set("max_execution_time", 3600);

        $additionalQuery = "";
        if ($numberingList) {
            $additionalQuery .= " AND kode_numbering in (".$numberingList.")";
        }

        // Rft
        $masterPlan = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                actual.id as act_plan_id,
                actual.color as act_color,
                actual.id_ws as act_act_costing_id,
                output.actual_color as color,
                output.size,
                output.dest
            FROM
            (
                SELECT
                    output_rfts.id,
                    master_plan.id plan_id,
                    master_plan.color plan_color,
                    master_plan.id_ws plan_act_costing_id,
                    so_det.color actual_color,
                    act_costing.id actual_act_costing_id,
                    so_det.size,
                    so_det.dest,
                    userpassword.username line,
                    master_plan.tgl_plan
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                WHERE
                    (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
                    ".$additionalQuery."
                GROUP BY
                    output_rfts.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan = output.tgl_plan
            WHERE
                actual.id IS NULL OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        // Defect
        $masterPlanDef = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                actual.id as act_plan_id,
                actual.color as act_color,
                actual.id_ws as act_act_costing_id,
                output.actual_color as color,
                output.size,
                output.dest
            FROM
            (
                SELECT
                    output_defects.id,
                    master_plan.id plan_id,
                    master_plan.color plan_color,
                    master_plan.id_ws plan_act_costing_id,
                    so_det.color actual_color,
                    act_costing.id actual_act_costing_id,
                    so_det.size,
                    so_det.dest,
                    userpassword.username line,
                    master_plan.tgl_plan
                FROM
                    output_defects
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_defects.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_defects.master_plan_id
                WHERE
                    (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
                    ".$additionalQuery."
                GROUP BY
                    output_defects.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan = output.tgl_plan
            WHERE
                actual.id IS NULL OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        // Reject
        $masterPlanRej = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                actual.id as act_plan_id,
                actual.color as act_color,
                actual.id_ws as act_act_costing_id,
                output.actual_color as color,
                output.size,
                output.dest
            FROM
            (
                SELECT
                    output_rejects.id,
                    master_plan.id plan_id,
                    master_plan.color plan_color,
                    master_plan.id_ws plan_act_costing_id,
                    so_det.color actual_color,
                    act_costing.id actual_act_costing_id,
                    so_det.size,
                    so_det.dest,
                    userpassword.username line,
                    master_plan.tgl_plan
                FROM
                    output_rejects
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_rejects.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rejects.master_plan_id
                WHERE
                    (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
                    ".$additionalQuery."
                GROUP BY
                    output_rejects.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan = output.tgl_plan
            WHERE
                actual.id IS NULL OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        if (($masterPlan->count() + $masterPlanDef->count() + $masterPlanRej->count()) < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada master plan yang miss',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        $success = [];
        $fails = [];
        $unavailable = [];

        // RFT
        foreach ($masterPlan as $mp) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->plan_act_costing_id)->where("so_det.color", $mp->plan_color)->where("so_det.size", $mp->size)->where("so_det.dest", $mp->dest)->first();

            if ($updateOrigin) {
                // Update Origin
                $rft = Rft::where("id", $mp->id)->first();

                if ($rft) {
                    $rft->timestamps = false;
                    $rft->so_det_id = $soDet->id;
                    $rft->save();

                    $yearSequence = YearSequence::where("id_year_sequence", $rft->kode_numbering)->update(["so_det_id" => $rft->so_det_id]);

                    if ($yearSequence) {
                        array_push($success, [$mp, "change output origin"]);
                    }
                } else {
                    array_push($fails, [$mp, "change output origin"]);
                }
            } else {
                // Update Master Plan
                $updateRft = DB::connection("mysql_sb")->table("output_rfts")->where("id", $mp->id)->update([
                    "master_plan_id" => $mp->act_plan_id,
                ]);

                if ($updateRft) {
                    array_push($success, [$mp, "change output master plan"]);
                } else {
                    array_push($fails, [$mp, "change output master plan"]);
                }
            }
        }

        // Defect
        foreach ($masterPlanDef as $mpDef) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpDef->plan_act_costing_id)->where("so_det.color", $mpDef->plan_color)->where("so_det.size", $mpDef->size)->where("so_det.dest", $mpDef->dest)->first();

            if ($updateOrigin) {
                // Update Origin
                $defect = Defect::where("id", $mpDef->id)->first();

                if ($defect) {
                    $defect->timestamps = false;
                    $defect->so_det_id = $soDet->id;
                    $defect->save();

                    $yearSequence = YearSequence::where("id_year_sequence", $defect->kode_numbering)->update(["so_det_id" => $defect->so_det_id]);

                    if ($yearSequence) {
                        array_push($success, [$mpDef, "change output origin defect"]);
                    }
                } else {
                    array_push($fails, [$mpDef, "change output origin defect"]);
                }
            } else {
                // Update Master Plan
                $updateDefect = DB::connection("mysql_sb")->table("output_defects")->where("id", $mpDef->id)->update([
                    "master_plan_id" => $mpDef->act_plan_id,
                ]);

                if ($updateDefect) {
                    array_push($success, [$mpDef, "change output master plan defect"]);
                } else {
                    array_push($fails, [$mpDef, "change output master plan defect"]);
                }
            }
        }

        // Reject
        foreach ($masterPlanRej as $mpRej) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpRej->plan_act_costing_id)->where("so_det.color", $mpRej->plan_color)->where("so_det.size", $mpRej->size)->where("so_det.dest", $mpRej->dest)->first();

            if ($updateOrigin) {
                // Update Origin
                $reject = Reject::where("id", $mpRej->id)->first();
                if ($reject) {
                    $reject->timestamps = false;
                    $reject->so_det_id = $soDet->id;
                    $reject->save();

                    $yearSequence = YearSequence::where("id_year_sequence", $reject->kode_numbering)->update(["so_det_id" => $reject->so_det_id]);

                    if ($yearSequence) {
                        array_push($success, [$mpRej, "change output origin reject"]);
                    }
                } else {
                    array_push($fails, [$mpRej, "change output origin reject"]);
                }
            } else {
                // Update Master Plan
                $updateReject = DB::connection("mysql_sb")->table("output_rejects")->where("id", $mpRej->id)->update([
                    "master_plan_id" => $mpRej->act_plan_id,
                ]);

                if ($updateReject) {
                    array_push($success, [$mpRej, "change output master plan reject"]);
                } else {
                    array_push($fails, [$mpRej, "change output master plan reject"]);
                }
            }
        }

        Log::channel('missMasterPlanOutput')->info([
            "Repair Master Plan Missing Output",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Total Data ".count($success),
            "Success" => $success,
            "Fails" => $fails,
            "Unavailable" => $unavailable
        ]);

        return array(
            'status' => 200,
            'message' => (count($success) > 0 ? 'Berhasil mengubah '.count($success).' data </br>': '').' '.(count($unavailable) > 0 ? 'Tidak dapat menemukan master plan '.count($unavailable).' data </br>': '').' '.(count($fails) > 0 ? 'Gagal mengubah '.count($fails).' data </br>': ''),
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    function missPackingPo() {
        $missPackingPo = DB::connection("mysql_sb")->table("output_rfts_packing_po")->
            select("output_rfts_packing_po.id", "output_rfts_packing_po.po_id", "output_rfts_packing_po.kode_numbering", "output_rfts_packing_po.so_det_id", "ppic_master_so.id as po_id", "ppic_master_so.po")->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "output_rfts_packing_po.po_id")->
            whereRaw("output_rfts_packing_po.po_id is not null and (ppic_master_so.id_so_det is null OR ppic_master_so.id_so_det != output_rfts_packing_po.so_det_id)")->
            groupBy("output_rfts_packing_po.id")->
            get();

        $success = [];
        $fails = [];
        foreach ($missPackingPo as $packingPo) {
            $actualPo = DB::table("ppic_master_so")->select("id", "po", "id_so_det")->where("po", $packingPo->po)->where("id_so_det", $packingPo->so_det_id)->first();

            if (!$actualPo) {
                $actualPo = DB::table("ppic_master_so")->select("id", "po", "id_so_det")->where("id_so_det", $packingPo->so_det_id)->first();
            }

            if ($actualPo && $actualPo->id) {
                DB::connection("mysql_sb")->table("output_rfts_packing_po")->where("id", $packingPo->id)->update(["po_id" => $actualPo->id]);

                array_push($success, "PO Output Packing ".$packingPo->kode_numbering." / ".$packingPo->po_id." / ".$packingPo->so_det_id." diubah ke PO ".$actualPo->po." / ".$actualPo->id." / ".$actualPo->id_so_det);
            } else {
                array_push($fails, "PO Output Packing ".$packingPo->kode_numbering." tidak ditemukan");
            }

            $actualPo = null;
        }

        Log::channel('missPackingPo')->info([
            "Repair Packing Po Missing Po",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Total Data ".count($success),
            "Success" => $success,
            "Fails" => $fails
        ]);

        return array(
            'status' => 200,
            'message' => (count($success) > 0 ? 'Berhasil mengubah '.count($success).' data </br>': '').' '.(count($fails) > 0 ? 'Gagal mengubah '.count($fails).' data </br>': ''),
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }
}
