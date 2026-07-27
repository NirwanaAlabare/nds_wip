<?php

namespace App\Services;

use App\Models\SignalBit\Rft;
use App\Models\SignalBit\RftPacking;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectPacking;
use App\Models\SignalBit\RftPackingPo;
use App\Models\Stocker\YearSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class SewingService
{
    public function missMasterPlan($numberingList = null, $updateOrigin = false)
    {
        ini_set("max_execution_time", 36000);

        $additionalQuery = "";
        if ($numberingList) {
            $additionalQuery .= " AND kode_numbering in (".$numberingList.")";
        }

        $dateFrom = date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"));
        $dateTo = date("Y-m-d");

        // Rft
        $masterPlan = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
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
                    COALESCE(userpassword.username, master_plan.sewing_line) line,
                    COALESCE(DATE(output_rfts.created_at), master_plan.tgl_plan) tgl_plan
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                WHERE
                    output_rfts.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_rfts.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
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
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
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
                    COALESCE(userpassword.username, master_plan.sewing_line) line,
                    COALESCE(DATE(output_defects.created_at), master_plan.tgl_plan) tgl_plan
                FROM
                    output_defects
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_defects.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_defects.master_plan_id
                WHERE
                    output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_defects.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
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
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
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
                    COALESCE(userpassword.username, master_plan.sewing_line) line,
                    COALESCE(DATE(output_rejects.created_at), master_plan.tgl_plan) tgl_plan
                FROM
                    output_rejects
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_rejects.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rejects.master_plan_id
                WHERE
                    output_rejects.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_rejects.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        $success = [];
        $fails = [];
        $unavailable = [];

        if (($masterPlan->count() + $masterPlanDef->count() + $masterPlanRej->count()) < 1) {
            goto packing;
        }

        // RFT
        foreach ($masterPlan as $mp) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->plan_act_costing_id)->where("so_det.color", $mp->plan_color)->where("so_det.size", $mp->size)->where("so_det.dest", $mp->dest)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->plan_act_costing_id)->where("so_det.color", $mp->plan_color)->where("so_det.size", $mp->size)->first();
            }

            if ($updateOrigin) {
                // Update Origin
                if ($soDet) {
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
                    array_push($unavailable, [$mp, "change output origin"]);
                }
            } else {
                if ($mp->act_plan_id) {
                    // Update Master Plan
                    $updateRft = DB::connection("mysql_sb")->table("output_rfts")->where("id", $mp->id)->update([
                        "master_plan_id" => $mp->act_plan_id,
                    ]);

                    if ($updateRft) {
                        // Update Master Plan Cancel if Cancelled
                        $updateMasterPlan = DB::connection("mysql_sb")->table("master_plan")->where("id", $mp->act_plan_id)->update([
                            "cancel" => "N"
                        ]);

                        array_push($success, [$mp, "change output master plan"]);
                    } else {
                        array_push($fails, [$mp, "change output master plan"]);
                    }
                } else {
                    array_push($unavailable, [$mp, "change output master plan"]);
                }
            }
        }

        // Defect
        foreach ($masterPlanDef as $mpDef) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpDef->plan_act_costing_id)->where("so_det.color", $mpDef->plan_color)->where("so_det.size", $mpDef->size)->where("so_det.dest", $mpDef->dest)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpDef->plan_act_costing_id)->where("so_det.color", $mpDef->plan_color)->where("so_det.size", $mpDef->size)->first();
            }

            if ($updateOrigin) {
                // Update Origin
                if ($soDet) {
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
                    array_push($unavailable, [$mpDef, "change output origin defect"]);
                }
            } else {
                if ($mpDef->act_plan_id) {
                    // Update Master Plan
                    $updateDefect = DB::connection("mysql_sb")->table("output_defects")->where("id", $mpDef->id)->update([
                        "master_plan_id" => $mpDef->act_plan_id,
                    ]);

                    if ($updateDefect) {
                        // Update Master Plan Cancel if Cancelled
                        $updateMasterPlan = DB::connection("mysql_sb")->table("master_plan")->where("id", $mpDef->act_plan_id)->update([
                            "cancel" => "N"
                        ]);

                        array_push($success, [$mpDef, "change output master plan defect"]);
                    } else {
                        array_push($fails, [$mpDef, "change output master plan defect"]);
                    }
                } else {
                    array_push($unavailable, [$mpDef, "change output master plan defect"]);
                }
            }
        }

        // Reject
        foreach ($masterPlanRej as $mpRej) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpRej->plan_act_costing_id)->where("so_det.color", $mpRej->plan_color)->where("so_det.size", $mpRej->size)->where("so_det.dest", $mpRej->dest)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpRej->plan_act_costing_id)->where("so_det.color", $mpRej->plan_color)->where("so_det.size", $mpRej->size)->first();
            }

            if ($updateOrigin) {
                // Update Origin
                if ($soDet) {
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
                    array_push($unavailable, [$mpRej, "change output origin reject"]);
                }
            } else {
                if ($mpRej->act_plan_id) {
                    // Update Master Plan
                    $updateReject = DB::connection("mysql_sb")->table("output_rejects")->where("id", $mpRej->id)->update([
                        "master_plan_id" => $mpRej->act_plan_id,
                    ]);

                    if ($updateReject) {
                        // Update Master Plan Cancel if Cancelled
                        $updateMasterPlan = DB::connection("mysql_sb")->table("master_plan")->where("id", $mpRej->act_plan_id)->update([
                            "cancel" => "N"
                        ]);

                        array_push($success, [$mpRej, "change output master plan reject"]);
                    } else {
                        array_push($fails, [$mpRej, "change output master plan reject"]);
                    }
                } else {
                    array_push($unavailable, [$mpRej, "change output master plan reject"]);
                }
            }
        }

        // PACKING
        packing:

        // Rft
        $masterPlanPac = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
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
                    TRIM(so_det.color) actual_color,
                    act_costing.id actual_act_costing_id,
                    so_det.size,
                    so_det.dest,
                    COALESCE(master_plan.sewing_line, userpassword.username) line,
                    COALESCE(DATE(output_rfts.created_at), master_plan.tgl_plan) as tgl_plan
                FROM
                    output_rfts_packing as output_rfts
                    LEFT JOIN userpassword on userpassword.username = output_rfts.created_by
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                WHERE
                    output_rfts.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_rfts.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        // Defect
        $masterPlanDefPac = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
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
                    COALESCE(master_plan.sewing_line, userpassword.username) line,
                    COALESCE(DATE(output_defects.created_at), master_plan.tgl_plan) as tgl_plan
                FROM
                    output_defects_packing as output_defects
                    LEFT JOIN userpassword on userpassword.username = output_defects.created_by
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_defects.master_plan_id
                WHERE
                    output_defects.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_defects.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        // Reject
        $masterPlanRejPac = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
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
                    COALESCE(master_plan.sewing_line, userpassword.username) line,
                    COALESCE(DATE(output_rejects.created_at), master_plan.tgl_plan) as tgl_plan
                FROM
                    output_rejects_packing as output_rejects
                    LEFT JOIN userpassword on userpassword.username = output_rejects.created_by
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rejects.master_plan_id
                WHERE
                    output_rejects.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_rejects.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        if (($masterPlanPac->count() + $masterPlanDefPac->count() + $masterPlanRejPac->count()) < 1) {
            goto packingPo;
        }

        // RFT
        foreach ($masterPlanPac as $mpPac) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpPac->plan_act_costing_id)->where("so_det.color", $mpPac->plan_color)->where("so_det.size", $mpPac->size)->where("so_det.dest", $mpPac->dest)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpPac->plan_act_costing_id)->where("so_det.color", $mpPac->plan_color)->where("so_det.size", $mpPac->size)->first();
            }

            if ($updateOrigin) {
                if ($soDet) {
                    // Update Origin
                    $rft = RftPacking::where("id", $mpPac->id)->first();

                    if ($rft) {
                        $rft->timestamps = false;
                        $rft->so_det_id = $soDet->id;
                        $rft->save();

                        $yearSequence = YearSequence::where("id_year_sequence", $rft->kode_numbering)->update(["so_det_id" => $rft->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, [$mpPac, "change output origin packing"]);
                        }
                    } else {
                        array_push($fails, [$mpPac, "change output origin packing"]);
                    }
                } else {
                    array_push($unavailable, [$mpPac, "change output origin packing"]);
                }
            } else {
                if ($mpPac->act_plan_id) {
                    // Update Master Plan
                    $updateRft = DB::connection("mysql_sb")->table("output_rfts_packing")->where("id", $mpPac->id)->update([
                        "master_plan_id" => $mpPac->act_plan_id,
                    ]);

                    if ($updateRft) {
                        // Update Master Plan Cancel if Cancelled
                        $updateMasterPlan = DB::connection("mysql_sb")->table("master_plan")->where("id", $mpPac->act_plan_id)->update([
                            "cancel" => "N"
                        ]);

                        array_push($success, [$mpPac, "change output master plan"]);
                    } else {
                        array_push($fails, [$mpPac, "change output master plan packing"]);
                    }
                } else {
                    array_push($unavailable, [$mpPac, "change output master plan packing"]);
                }
            }
        }

        // Defect
        foreach ($masterPlanDefPac as $mpDefPac) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpDefPac->plan_act_costing_id)->where("so_det.color", $mpDefPac->plan_color)->where("so_det.size", $mpDefPac->size)->where("so_det.dest", $mpDefPac->dest)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpDefPac->plan_act_costing_id)->where("so_det.color", $mpDefPac->plan_color)->where("so_det.size", $mpDefPac->size)->first();
            }

            if ($updateOrigin) {
                if ($soDet) {
                    // Update Origin
                    $defect = DefectPacking::where("id", $mpDefPac->id)->first();

                    if ($defect) {
                        $defect->timestamps = false;
                        $defect->so_det_id = $soDet->id;
                        $defect->save();

                        $yearSequence = YearSequence::where("id_year_sequence", $defect->kode_numbering)->update(["so_det_id" => $defect->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, [$mpDefPac, "change output origin defect packing"]);
                        }
                    } else {
                        array_push($fails, [$mpDefPac, "change output origin defect packing"]);
                    }
                } else {
                    array_push($unavailable, [$mpDefPac, "change output origin defect packing"]);
                }
            } else {
                if ($mpDefPac->act_plan_id) {
                    // Update Master Plan
                    $updateDefect = DB::connection("mysql_sb")->table("output_defects_packing")->where("id", $mpDefPac->id)->update([
                        "master_plan_id" => $mpDefPac->act_plan_id,
                    ]);

                    if ($updateDefect) {
                        // Update Master Plan Cancel if Cancelled
                        $updateMasterPlan = DB::connection("mysql_sb")->table("master_plan")->where("id", $mpDefPac->act_plan_id)->update([
                            "cancel" => "N"
                        ]);

                        array_push($success, [$mpDefPac, "change output master plan defect"]);
                    } else {
                        array_push($fails, [$mpDefPac, "change output master plan defect packing"]);
                    }
                } else {
                    array_push($unavailable, [$mpDefPac, "change output master plan defect packing"]);
                }
            }
        }

        // Reject
        foreach ($masterPlanRejPac as $mpRejPac) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpRejPac->plan_act_costing_id)->where("so_det.color", $mpRejPac->plan_color)->where("so_det.size", $mpRejPac->size)->where("so_det.dest", $mpRejPac->dest)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mpRejPac->plan_act_costing_id)->where("so_det.color", $mpRejPac->plan_color)->where("so_det.size", $mpRejPac->size)->first();
            }

            if ($updateOrigin) {
                if ($soDet) {
                    // Update Origin
                    $reject = RejectPacking::where("id", $mpRejPac->id)->first();
                    if ($reject) {
                        $reject->timestamps = false;
                        $reject->so_det_id = $soDet->id;
                        $reject->save();

                        $yearSequence = YearSequence::where("id_year_sequence", $reject->kode_numbering)->update(["so_det_id" => $reject->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, [$mpRejPac, "change output origin reject packing"]);
                        }
                    } else {
                        array_push($fails, [$mpRejPac, "change output origin reject packing"]);
                    }
                } else {
                    array_push($unavailable, [$mpRejPac, "change output origin reject packing"]);
                }
            } else {
                if ($mpRejPac->act_plan_id) {
                    // Update Master Plan
                    $updateReject = DB::connection("mysql_sb")->table("output_rejects_packing")->where("id", $mpRejPac->id)->update([
                        "master_plan_id" => $mpRejPac->act_plan_id,
                    ]);

                    if ($updateReject) {
                        $updateMasterPlan = DB::connection("mysql_sb")->table("master_plan")->where("id", $mpRejPac->act_plan_id)->update([
                            "cancel" => "N"
                        ]);

                        array_push($success, [$mpRejPac, "change output master plan reject"]);
                    } else {
                        array_push($fails, [$mpRejPac, "change output master plan reject packing"]);
                    }
                } else {
                    array_push($unavailable, [$mpRejPac, "change output master plan reject packing"]);
                }
            }
        }

        packingPo:

        // Rft
        $masterPlanPacPo = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                MAX(actual.id) as act_plan_id,
                MAX(actual.color) as act_color,
                MAX(actual.id_ws) as act_act_costing_id,
                output.actual_color as color,
                output.po,
                output.size,
                output.dest
            FROM
            (
                SELECT
                    output_rfts.id,
                    master_plan.id plan_id,
                    master_plan.color plan_color,
                    master_plan.id_ws plan_act_costing_id,
                    TRIM(so_det.color) actual_color,
                    act_costing.id actual_act_costing_id,
                    so_det.size,
                    so_det.dest,
                    ppic_master_so.po,
                    COALESCE(master_plan.sewing_line, userpassword.username) line,
                    COALESCE(DATE(output_rfts.created_at), master_plan.tgl_plan) as tgl_plan
                FROM
                    output_rfts_packing_po as output_rfts
                    LEFT JOIN userpassword on userpassword.username = output_rfts.created_by
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                    LEFT JOIN laravel_nds.ppic_master_so on ppic_master_so.id = output_rfts.po_id
                WHERE
                    output_rfts.updated_at BETWEEN '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color OR master_plan.id is null OR master_plan.cancel = 'Y')
                    ".$additionalQuery."
                GROUP BY
                    output_rfts.id
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan <= output.tgl_plan and
                (actual.cancel is null or actual.cancel != 'Y')
            WHERE
                actual.id IS NULL OR output.plan_id is null OR actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        if (($masterPlanPacPo->count()) < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada master plan yang miss',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        // RFT
        foreach ($masterPlanPacPo as $mpPacPo) {
            $soDet = DB::connection("mysql_sb")->table("so_det")->selectRaw("so_det.id, ppic_master_so.id as po_id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id_so_det", "=", "so_det.id")->where("act_costing.id", $mpPacPo->plan_act_costing_id)->where("so_det.color", $mpPacPo->plan_color)->where("so_det.size", $mpPacPo->size)->where("so_det.dest", $mpPacPo->dest)->where("ppic_master_so.po", $mpPacPo->po)->first();

            if (!$soDet) {
                $soDet = DB::connection("mysql_sb")->table("so_det")->selectRaw("so_det.id, ppic_master_so.id as po_id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id_so_det", "=", "so_det.id")->where("act_costing.id", $mpPacPo->plan_act_costing_id)->where("so_det.color", $mpPacPo->plan_color)->where("so_det.size", $mpPacPo->size)->where("ppic_master_so.po", $mpPacPo->po)->first();
            }

            if ($updateOrigin) {
                if ($soDet) {
                    // Update Origin
                    $rft = RftPackingPo::where("id", $mpPacPo->id)->first();

                    if ($rft) {
                        $rft->timestamps = false;
                        $rft->so_det_id = $soDet->id;
                        $rft->save();

                        $yearSequence = YearSequence::where("id_year_sequence", $rft->kode_numbering)->update(["so_det_id" => $rft->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, [$mpPacPo, "change output origin packing po"]);
                        }
                    } else {
                        array_push($fails, [$mpPacPo, "change output origin packing po"]);
                    }
                } else {
                    array_push($unavailable, [$mpPacPo, "change output origin packing po"]);
                }
            } else {
                if ($mpPacPo->act_plan_id) {
                    // Update Master Plan
                    $updateRft = DB::connection("mysql_sb")->table("output_rfts_packing_po")->where("id", $mpPacPo->id)->update([
                        "master_plan_id" => $mpPacPo->act_plan_id,
                    ]);

                    if ($updateRft) {
                        array_push($success, [$mpPacPo, "change output master plan packing po"]);
                    } else {
                        array_push($fails, [$mpPacPo, "change output master plan packing po"]);
                    }
                } else {
                    array_push($unavailable, [$mpPacPo, "change output master plan packing po"]);
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

    function missPackingPo($numberingList = null) {
        $additionalQuery = "";
        if ($numberingList) {
            $additionalQuery .= " AND kode_numbering in (".$numberingList.")";
        }

        $missPackingPo = DB::connection("mysql_sb")->table("output_rfts_packing_po")->
            select("output_rfts_packing_po.id", "output_rfts_packing_po.po_id", "output_rfts_packing_po.kode_numbering", "output_rfts_packing_po.so_det_id", "ppic_master_so.id as po_id", "ppic_master_so.po")->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "output_rfts_packing_po.po_id")->
            whereRaw("output_rfts_packing_po.po_id is not null and (ppic_master_so.id_so_det is null OR ppic_master_so.id_so_det != output_rfts_packing_po.so_det_id)".$additionalQuery)->
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

    function updateMgtRepTmpEarn($date = null) {
        $date = $date ? $date : date("Y-m-d");
        $dateFrom = $date;
        $dateTo = $date;

        return "Not yet";

        try {
            DB::transaction(function () use ($dateFrom, $dateTo) {
                // Delete existing data for the specified date range
                DB::delete("DELETE FROM mgt_rep_tmp_earn WHERE tgl_trans BETWEEN ? AND ?", [$dateFrom, $dateTo]);

                // Insert new calculated data
                DB::insert("
                    INSERT INTO
                    mgt_rep_tmp_earn
                SELECT
                    '',
                    a.tgl_trans,
                    mp.tgl_plan,
                    a.master_plan_id,
                    acm.allowance,
                    ul.username sewing_line,
                    ms.supplier buyer,
                    ac.kpno,
                    ac.styleno,
                    mp.color,
                    mp.id,
                    mp.smv,
                    mp.man_power man_power_ori,
                    cmp.man_power,
                    mp.jam_kerja_awal,
                    istirahat,
                    op.jam_akhir_input_line,
                    round(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600,2) AS jam_kerja_act_line,
                    round(((((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)) * 60) * cmp.man_power) / mp.smv) target,
                    sum(a.tot_output) tot_output,
                    sum(d_rfts.tot_rfts) tot_rfts,
                    op.tot_output_line,
                    ac.curr,
                    acm.price AS cm_price,
                    ROUND(SUM(a.tot_output) * acm.price, 2) AS earning,
                    COALESCE(mr.kurs_tengah,mkb.kurs_tengah) kurs_tengah,
                    ROUND( SUM(a.tot_output) * CASE WHEN acm.jenis_rate = 'B' THEN acm.price ELSE acm.price * COALESCE(mr.kurs_tengah,mkb.kurs_tengah) END , 2) tot_earning_rupiah,
                    round((cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
                    round(sum(a.tot_output) * mp.smv,2) mins_prod,
                    round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line,
                    round(((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)),2) jam_kerja_act,
                    round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts,
                    CURRENT_TIMESTAMP()
                from
                (
                    select
                    date(a.updated_at)tgl_trans,
                    so_det_id,
                    master_plan_id,
                    count(so_det_id) tot_output,
                    time(max(a.updated_at)) jam_akhir_input,
                    userpassword.username
                    from output_rfts a
                    left join user_sb_wip on user_sb_wip.id = a.created_by
                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where a.updated_at >= ? and a.updated_at <= ?
                    group by master_plan_id, userpassword.username, date(a.updated_at)
                ) a
                inner join so_det sd on a.so_det_id = sd.id
                inner join so on sd.id_so = so.id
                inner join act_costing ac on so.id_cost = ac.id
                inner join userpassword ul on ul.username = a.username
                inner join master_plan mp on a.master_plan_id = mp.id
                inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                left join (
                    select
                        date(output_rfts.updated_at) tgl_trans_line,max(time(output_rfts.updated_at)) jam_akhir_input_line,count(output_rfts.so_det_id) tot_output_line,
                        case
                            when time(max(output_rfts.updated_at)) >= '12:00:00' and time(max(output_rfts.updated_at)) <= '18:44:59' THEN '01:00:00'
                            when time(max(output_rfts.updated_at)) <= '12:00:00'  THEN '00:00:00'
                            when time(max(output_rfts.updated_at)) >= '18:45:00'  THEN '01:30:00'
                        END as istirahat,
                        userpassword.username
                    from output_rfts
                    left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where output_rfts.updated_at >= ? and output_rfts.updated_at <= ? group by userpassword.username, date(output_rfts.updated_at)
                ) op on a.tgl_trans = op.tgl_trans_line and ul.username = op.username
                left join (
                    select * from act_costing_mfg where id_item = '8' group by id_act_cost
                ) acm on ac.id = acm.id_act_cost
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal
                ) konv_sb on ac.deldate = konv_sb.tanggal
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal ORDER BY tanggal DESC limit 1
                ) last_konv_sb on ac.deldate >= last_konv_sb.tanggal
                left join (
                    SELECT
                        master_plan_id,
                        tgl_trans_rfts,
                        sum(tot_rfts)tot_rfts
                    from
                    (
                        select
                        date(a.updated_at)tgl_trans_rfts,
                        master_plan_id,
                        count(so_det_id) tot_rfts,
                        userpassword.username
                        from output_rfts a
                        left join user_sb_wip on user_sb_wip.id = a.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        where a.updated_at >= ? and a.updated_at <= ? and status = 'NORMAL'
                        group by master_plan_id, userpassword.username, date(a.updated_at)
                    ) a
                    inner join master_plan mp on a.master_plan_id = mp.id
                    group by tgl_trans_rfts, master_plan_id
                ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
                left join
                (
                    select min(id), man_power, sewing_line, tgl_plan from master_plan
                    where tgl_plan >= ? and  tgl_plan <= ? and cancel = 'N'
                    group by sewing_line, tgl_plan
                ) cmp on a.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line

                -- Kurs join for pre-MySQL 8
                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.kurs_tengah
                    FROM (
                            SELECT a_dates.tgl_trans, MAX(mkb.tanggal_kurs_bi) AS max_kurs_date
                            FROM (
                                    SELECT DISTINCT date(updated_at) AS tgl_trans
                                    FROM output_rfts
                            WHERE updated_at >= ? AND updated_at <= ?
                            ) a_dates
                            JOIN master_kurs_bi mkb
                            ON mkb.tanggal_kurs_bi <= a_dates.tgl_trans
                            GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN master_kurs_bi k
                    ON k.tanggal_kurs_bi = x.max_kurs_date
                ) mkb ON a.tgl_trans = mkb.tgl_trans

                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.rate as kurs_tengah
                    FROM (
                        SELECT a_dates.tgl_trans, MAX(mr.tanggal) AS max_kurs_date
                        FROM (
                            SELECT DISTINCT date(updated_at) AS tgl_trans
                            FROM output_rfts
                            WHERE updated_at >= ? AND updated_at <= ?
                        ) a_dates
                        JOIN masterrate mr
                        ON mr.tanggal <= a_dates.tgl_trans
                        GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN masterrate k
                    ON k.tanggal = x.max_kurs_date
                    WHERE k.v_codecurr = 'HARIAN'
                ) mr ON a.tgl_trans = mr.tgl_trans

                group by ul.username, ac.kpno, ac.Styleno, a.tgl_trans
                HAVING ul.username NOT LIKE '%sample%'
                order by a.tgl_trans asc, ul.username asc, ac.kpno asc;
            ", [
                    $dateFrom . " 00:00:00", $dateTo . " 23:59:59",
                    $dateFrom . " 00:00:00", $dateTo . " 23:59:59",
                    $dateFrom . " 00:00:00", $dateTo . " 23:59:59",
                    $dateFrom, $dateTo,
                    $dateFrom . " 00:00:00", $dateTo . " 23:59:59",
                    $dateFrom . " 00:00:00", $dateTo . " 23:59:59",
                ]);
            });

            Log::channel('mgtRepEarnTmp')->info("Query executed");

        } catch (\Throwable $th) {
            Log::channel('mgtRepEarnTmp')->info($th);
        }
    }
}
