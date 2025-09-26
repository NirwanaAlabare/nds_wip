<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RftPacking;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\ReworkPacking;
use App\Models\SignalBit\RejectPacking;
use App\Models\SignalBit\Undo;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\UserSbWip;
use App\Models\SignalBit\RejectIn;
use App\Models\SignalBit\RejectInDetail;
use App\Models\SignalBit\RejectInDetailPosition;
use App\Models\SignalBit\RejectOut;
use App\Models\SignalBit\RejectOutDetail;
use App\Models\Stocker\YearSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\Sewing\CheckOutputDetailListExport;
use Carbon\Carbon;
use DB;
use Excel;

class SewingToolsController extends Controller
{
    public function index() {
        return view('sewing.tools.tools', [
            "page" => "dashboard-sewing-eff"
        ]);
    }

    public function missUser() {
        ini_set("max_execution_time", 3600);

        // Rft
        $masterUser = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_rfts.id,
                userpassword.username as actual_sewing_line,
                userpassword.line_id as actual_line_id,
                master_plan.sewing_line as plan_sewing_line,
                plan_line.line_id as plan_line_id,
                actual_plan.id as actual_plan_id,
                master_plan.id as plan_plan_id,
                MAX(plan_user.id) as plan_user_id
            FROM
                output_rfts
                LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                LEFT JOIN userpassword plan_line on plan_line.username = master_plan.sewing_line
                LEFT JOIN user_sb_wip plan_user on plan_user.line_id = plan_line.line_id
                LEFT JOIN master_plan actual_plan on actual_plan.id_ws = master_plan.id_ws and actual_plan.color = master_plan.color and actual_plan.tgl_plan = master_plan.tgl_plan and actual_plan.sewing_line = userpassword.username
            WHERE
                output_rfts.updated_at between '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                AND (userpassword.username != master_plan.sewing_line)
            GROUP BY
                output_rfts.id
        "));

        // Defect
        $masterUserDef = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_defects.id,
                userpassword.username as actual_sewing_line,
                userpassword.line_id as actual_line_id,
                master_plan.sewing_line as plan_sewing_line,
                plan_line.line_id as plan_line_id,
                actual_plan.id as actual_plan_id,
                master_plan.id as plan_plan_id,
                MAX(plan_user.id) as plan_user_id
            FROM
                output_defects
                LEFT JOIN user_sb_wip on user_sb_wip.id = output_defects.created_by
                LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN master_plan on master_plan.id = output_defects.master_plan_id
                LEFT JOIN userpassword plan_line on plan_line.username = master_plan.sewing_line
                LEFT JOIN user_sb_wip plan_user on plan_user.line_id = plan_line.line_id
                LEFT JOIN master_plan actual_plan on actual_plan.id_ws = master_plan.id_ws and actual_plan.color = master_plan.color and actual_plan.tgl_plan = master_plan.tgl_plan and actual_plan.sewing_line = userpassword.username
            WHERE
                output_defects.updated_at between '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                AND (userpassword.username != master_plan.sewing_line)
            GROUP BY
                output_defects.id
        "));

        // Reject
        $masterUserRej = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_rejects.id,
                userpassword.username as actual_sewing_line,
                userpassword.line_id as actual_line_id,
                master_plan.sewing_line as plan_sewing_line,
                plan_line.line_id as plan_line_id,
                actual_plan.id as actual_plan_id,
                master_plan.id as plan_plan_id,
                MAX(plan_user.id) as plan_user_id
            FROM
                output_rejects
                LEFT JOIN user_sb_wip on user_sb_wip.id = output_rejects.created_by
                LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN master_plan on master_plan.id = output_rejects.master_plan_id
                LEFT JOIN userpassword plan_line on plan_line.username = master_plan.sewing_line
                LEFT JOIN user_sb_wip plan_user on plan_user.line_id = plan_line.line_id
                LEFT JOIN master_plan actual_plan on actual_plan.id_ws = master_plan.id_ws and actual_plan.color = master_plan.color and actual_plan.tgl_plan = master_plan.tgl_plan and actual_plan.sewing_line = userpassword.username
            WHERE
                output_rejects.updated_at between '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                AND (userpassword.username != master_plan.sewing_line)
            GROUP BY
                output_rejects.id
        "));

        if (($masterUser->count() + $masterUserDef->count() + $masterUserRej->count()) < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada output user line yang miss',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        $success = [];
        $fails = [];
        $unavailable = [];

        // Rft
        foreach ($masterUser as $mu) {
            if ($mu->actual_plan_id) {
                $updateRft = DB::connection("mysql_sb")->table("output_rfts")->where("id", $mu->id)->update([
                    "master_plan_id" => $mu->actual_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mu->id, "change output master plan"]);
                } else {
                    array_push($fails, [$mu->id, "change output master plan"]);
                }
            } else if ($mu->plan_user_id) {
                $updateRft = DB::connection("mysql_sb")->table("output_rfts")->where("id", $mu->id)->update([
                    "created_by" => $mu->plan_user_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mu->id, "change output user"]);
                } else {
                    array_push($fails, [$mu->id, "change output user"]);
                }
            }
        }

        // Defect
        foreach ($masterUserDef as $muDef) {
            if ($muDef->actual_plan_id) {
                $updateDef = DB::connection("mysql_sb")->table("output_defects")->where("id", $muDef->id)->update([
                    "master_plan_id" => $muDef->actual_plan_id
                ]);

                if ($updateDef) {
                    array_push($success, [$muDef->id, "change output master plan"]);
                } else {
                    array_push($fails, [$muDef->id, "change output master plan"]);
                }
            } else if ($muDef->plan_user_id) {
                $updateDef = DB::connection("mysql_sb")->table("output_defects")->where("id", $muDef->id)->update([
                    "created_by" => $muDef->plan_user_id
                ]);

                if ($updateDef) {
                    array_push($success, [$muDef->id, "change output user defect"]);
                } else {
                    array_push($fails, [$muDef->id, "change output user defect"]);
                }
            }
        }

        // Reject
        foreach ($masterUserRej as $muRej) {
            if ($muRej->actual_plan_id) {
                $updateRej = DB::connection("mysql_sb")->table("output_rejects")->where("id", $muRej->id)->update([
                    "master_plan_id" => $muRej->actual_plan_id
                ]);

                if ($updateRej) {
                    array_push($success, [$muRej->id, "change output master plan"]);
                } else {
                    array_push($fails, [$muRej->id, "change output master plan"]);
                }
            } else if ($muRej->plan_user_id) {
                $updateRej = DB::connection("mysql_sb")->table("output_rejects")->where("id", $muRej->id)->update([
                    "created_by" => $muRej->plan_user_id
                ]);

                if ($updateRej) {
                    array_push($success, [$muRej->id, "change output user defect"]);
                } else {
                    array_push($fails, [$muRej->id, "change output user defect"]);
                }
            }
        }

        Log::channel('missUserOutput')->info([
            "Repair User Output",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Total Data ".count($success),
            "Success" => $success,
            "Fails" => $fails,
            "Unavailable" => $unavailable
        ]);

        return array(
            'status' => 200,
            'message' => (count($success) > 0 ? 'Berhasil mengubah '.count($success).' data </br>': '').' '.(count($unavailable) > 0 ? 'Tidak dapat menemukan '.count($unavailable).' data </br>': '').' '.(count($fails) > 0 ? 'Gagal mengubah '.count($fails).' data </br>': ''),
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function missMasterPlan() {
        ini_set("max_execution_time", 3600);

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
                    output_rfts.updated_at BETWEEN '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
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
                    output_defects.updated_at BETWEEN '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
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
                    output_rejects.updated_at BETWEEN '".date("Y-m-d", strtotime(date("Y-m-d")." - 30 days"))." 00:00:00' AND '".date("Y-m-d")." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
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

            if ($soDet) {
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

            if ($soDet) {
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

            if ($soDet) {
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

    public function missRework() {
        ini_set("max_execution_time", 3600);

        // Get Defects with Missing Rework
        $defects = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.id as defect_id, 'NORMAL' as status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects left join output_reworks on output_reworks.defect_id = output_defects.id where output_reworks.id is null and defect_status = 'reworked'"));

        $defectArr = $defects->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRework = Rework::insert($defectArr);

        // Get Reworks Data with Of Course Missing RFT
        $reworks = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'REWORK' as status, output_reworks.id as rework_id, output_defects.created_by, output_reworks.created_at, output_reworks.updated_at, output_defects.kode_numbering, output_defects.kode_numbering no_cut_size from output_reworks left join output_defects on output_defects.id = output_reworks.defect_id left join output_rfts on output_rfts.rework_id = output_reworks.id where output_rfts.id is null"));

        if ($reworks->count() < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada rework yang hilang',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        $reworkArr = $reworks->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRft = Rft::insert($reworkArr);

        // Get Defects with Missing Rework Packing
        $defectsPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects_packing.id as defect_id, 'NORMAL' as status, output_defects_packing.created_by, output_defects_packing.created_at, output_defects_packing.updated_at from output_defects_packing left join output_reworks_packing on output_reworks_packing.defect_id = output_defects_packing.id where output_reworks_packing.id is null and defect_status = 'reworked'"));

        $defectPackingArr = $defectsPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToReworkPacking = ReworkPacking::insert($defectPackingArr);

        // Get Reworks Data with Of Course Missing RFT Packing
        $reworksPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects_packing.master_plan_id, output_defects_packing.so_det_id, 'REWORK' as status, output_reworks_packing.id as rework_id, output_defects_packing.created_by, output_reworks_packing.created_at, output_reworks_packing.updated_at, output_defects_packing.kode_numbering, output_defects_packing.kode_numbering no_cut_size from output_reworks_packing left join output_defects_packing on output_defects_packing.id = output_reworks_packing.defect_id left join output_rfts_packing on output_rfts_packing.rework_id = output_reworks_packing.id where output_rfts_packing.id is null"));

        $reworkPackingArr = $reworksPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRftPacking = RftPacking::insert($reworkPackingArr);

        if ($storeToRework && $storeToRft) {
            Log::channel('missReworkOutput')->info([
                "Repair Defect->Rework->RFT Chain Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                "Total Data ".count($defects)." - ".$defects,
                "Total Data Packing ".count($defectsPacking)." - ".$defectsPacking,
                $reworks
            ]);

            return array(
                'status' => 200,
                'message' => 'Berhasil memperbaiki <br> Data Defect = '.count($defects).' <br> Data Rework = '.count($reworks).'',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Terjadi kesalahan',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function missReject() {
        ini_set("max_execution_time", 3600);

        // Get Defects with Missing Reject
        $defects = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'NORMAL' as status, output_defects.id as defect_id, output_defects.defect_type_id as reject_type_id, output_defects.defect_area_id as reject_area_id, output_defects.defect_area_x as reject_area_x, output_defects.defect_area_y as reject_area_y, 'defect' as reject_status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects left join output_rejects on output_rejects.defect_id = output_defects.id where output_rejects.id is null and defect_status = 'rejected'"));

        $defectArr = $defects->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToReject = Reject::insert($defectArr);

        // Get Defects with Missing Reject Packing
        $defectsPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'NORMAL' as status, output_defects.id as defect_id, output_defects.defect_type_id as reject_type_id, output_defects.defect_area_id as reject_area_id, output_defects.defect_area_x as reject_area_x, output_defects.defect_area_y as reject_area_y, 'defect' as reject_status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects_packing as output_defects left join output_rejects_packing as output_rejects on output_rejects.defect_id = output_defects.id where output_rejects.id is null and defect_status = 'rejected'"));

        $defectPackingArr = $defectsPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRejectPacking = RejectPacking::insert($defectPackingArr);

        if ($storeToReject && $storeToRejectPacking) {
            Log::channel('missRejectOutput')->info([
                "Repair Defect->Reject Chain Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                "Total Data ".count($defects)." - ".$defects,
                "Total Data Packing ".count($defectsPacking)." - ".$defectsPacking
            ]);

            return array(
                'status' => 200,
                'message' => 'Berhasil memperbaiki <br> Data Defect = '.count($defects).' <br>',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Terjadi kesalahan',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function checkOutputDetail() {
        $buyers = DB::connection("mysql_sb")->select("
            SELECT
                Id_Supplier as id,
                Supplier as buyer
            FROM
                mastersupplier
                LEFT JOIN act_costing ON act_costing.id_buyer = mastersupplier.Id_Supplier
            WHERE
                tipe_sup = 'C'
                AND act_costing.cost_date > DATE_SUB( CURRENT_DATE, INTERVAL 1 YEAR )
            GROUP BY
                mastersupplier.Id_Supplier
            ORDER BY
                mastersupplier.Supplier ASC
        ");

        $orders = DB::connection("mysql_sb")->table("act_costing")->selectRaw("act_costing.id, act_costing.kpno as ws, act_costing.styleno as style")->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        $lines = DB::connection("mysql_sb")->table("userpassword")->select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        $defectTypes = DB::connection("mysql_sb")->table("output_defect_types")->whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy("updated_at", "desc")->get();

        return view("sewing.tools.check-output-detail", ["buyers" => $buyers, "orders" => $orders, "lines" => $lines, "defectTypes" => $defectTypes, "page" => "dashboard-sewing-eff"]);
    }

    public function checkOutputDetailList(Request $request) {
        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($request->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$request->buyer."'";
            $buyerFilterOutput = " and mastersupplier.Supplier = '".$request->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($request->ws) {
            $wsFilterYs = " and msb.id_act_cost = '".$request->ws."'";
            $wsFilterOutput = " and act_costing.id = '".$request->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->style) {
            $styleFilterYs = " and msb.styleno = '".$request->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$request->style."'";
        }

        $colorFilterYs = "";
        $colorFilterOutput = "";
        if ($request->color) {
            $colorFilterYs = " and msb.color = '".$request->color."'";
            $colorFilterOutput = " and so_det.color = '".$request->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($request->size && count($request->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $request->size));

            $sizeFilterYs = " and msb.id_so_det in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.id in (".$sizeList.")";
        }

        $kodeFilterYs = "";
        $kodeFilterOutput = "";
        if ($request->kode && strlen($request->kode) > 0) {
            $kodeList = addQuotesAround($request->kode);

            $kodeFilterYs = " and ys.id_year_sequence in (".$kodeList.")";
            $kodeFilterOutput = " and kode_numbering in (".$kodeList.")";
        }

        $additionalFilter = "";

        $tglLoading = "";
        if ($request->tanggal_loading_awal) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) >= '".$request->tanggal_loading_awal."'";
        }

        if ($request->tanggal_loading_akhir) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading)<= '".$request->tanggal_loading_akhir."'";
        }

        $lineLoading = "";
        if ($request->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$request->line_loading."'";
        }

        $tglPlan = "";
        if ($request->tanggal_plan_awal || $request->tanggal_plan_akhir) {
            if ($request->tanggal_plan_awal) {
                $tglPlan .= " and master_plan.tgl_plan >= '".$request->tanggal_plan_awal."'";
            }
            if ($request->tanggal_plan_akhir) {
                $tglPlan .= " and master_plan.tgl_plan <= '".$request->tanggal_plan_akhir."'";
            }
            $additionalFilter .= "output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($request->tanggal_output_awal || $request->tanggal_output_akhir) {
            $tglAwalOutput = $request->tanggal_output_awal ? $request->tanggal_output_awal : date("Y-m-d");
            $tglAkhirOutput = $request->tanggal_output_akhir ? $request->tanggal_output_akhir : date("Y-m-d");

            $tglOutput = " and output_rfts.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";

            $additionalFilter .= " and output.tgl is not null";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($request->tanggal_packing_awal || $request->tanggal_packing_akhir) {
            $tglAwalPacking = $request->tanggal_packing_awal ? $request->tanggal_packing_awal : date("Y-m-d");
            $tglAkhirPacking = $request->tanggal_packing_akhir ? $request->tanggal_packing_akhir : date("Y-m-d");

            $tglOutputPck = " and output_rfts.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";

            $additionalFilter .= " and output_packing.tgl is not null";
        }

        // Sewing
        $lineOutput = "";
        if ($request->line_output) {
            $lineOutput = " and userpassword.username = '".$request->line_output."'";
            $additionalFilter .= " and output.line is not null";
        }

        $statusOutput = "";
        if ($request->status_output && count($request->status_output) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status_output));

            $statusOutput = " and output.status in (".$statusList.")";
        }

        $defectOutput = "";
        if ($request->defect_output && count($request->defect_output) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_output));

            $defectOutput = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output.defect_type is not null";
        }

        $allocationOutput = "";
        if ($request->allocation_output && count($request->allocation_output) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_output));

            $allocationOutput = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output.allocation is not null";
        }

        // Packing
        $linePacking = "";
        if ($request->line_packing) {
            $linePacking = " and userpassword.username = '".$request->line_packing."'";
            $additionalFilter .= " and output_packing.line is not null";
        }

        $statusPacking = "";
        if ($request->status_packing && count($request->status_packing) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($request->defect_packing && count($request->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_packing));

            $defectPacking = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($request->allocation_packing && count($request->allocation_packign) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_packing));

            $allocationPacking = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output_packing.allocation is not null";
        }

        // Cross-line loading
        $crossLineLoading = "";
        if ($request->crossline_loading) {
            $crossLineLoading = " and output.line != COALESCE(loading.nama_line, loading_bk.nama_line)";
            $additionalFilter .= " and output.line is not null and COALESCE(loading.nama_line, loading_bk.nama_line) is not null";
        }

        // Cross-line output
        $crossLineOutput = "";
        if ($request->crossline_output) {
            $crossLineOutput = " and output.line != output_packing.line";
            $additionalFilter .= " and output.line is not null and output_packing.line is not null";
        }

        // Missmatch
        $missmatchOutput = "";
        $missmatchDefect = "";
        $missmatchReject = "";
        if ($request->missmatch_code) {
            $missmatchOutput = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefect = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchReject = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output.kode_numbering is not null";
        }

        // Missmatch
        $missmatchOutputPck = "";
        $missmatchDefectPck = "";
        $missmatchRejectPck = "";
        if ($request->missmatch_code_packing) {
            $missmatchOutputPck = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefectPck = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchRejectPck = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output_packing.kode_numbering is not null";
        }

        // Backdate
        $backDateOutput = "";
        $backDateDefect = "";
        $backDateReject = "";
        if ($request->back_date) {
            $backDateOutput = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefect = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateReject = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output.tgl is not null";
        }

        // Backdate
        $backDateOutputPck = "";
        $backDateDefectPck = "";
        $backDateRejectPck = "";
        if ($request->back_date_packing) {
            $backDateOutputPck = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefectPck = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateRejectPck = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output_packing.tgl is not null";
        }

        $filterYs = $buyerFilterYs."
                    ".$wsFilterYs."
                    ".$styleFilterYs."
                    ".$colorFilterYs."
                    ".$sizeFilterYs."
                    ".$kodeFilterYs;

        $filterDefectOutput = $tglPlan."
                    ".$tglDefect."
                    ".$backDateDefect."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchDefect."
                    ".$backDateDefect;

        $filterRftOutput = $tglPlan."
                    ".$tglOutput."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$missmatchOutput."
                    ".$backDateOutput;

        $filterRejectOutput = $tglPlan."
                    ".$tglReject."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchReject."
                    ".$backDateReject;

        $filterDefectPck = $tglPlan."
                    ".$tglDefectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$lineOutput."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchDefectPck."
                    ".$backDateDefectPck;

        $filterRftPck = $tglPlan."
                    ".$tglOutputPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$lineOutput."
                    ".$missmatchOutputPck."
                    ".$backDateOutputPck;

        $filterRejectPck = $tglPlan."
                    ".$tglRejectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$lineOutput."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchRejectPck."
                    ".$backDateRejectPck;

        // Callback
        $callbackFilterYs = "";
        if (!trim(str_replace("\n", "", $filterYs)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterRejectOutput)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterYs = " and DATE(ys.updated_at) > CURRENT_DATE()";
        }

        $callbackFilterOutput = "";
        if (!trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRejectOutput))) {
            $callbackFilterOutput = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $callbackFilterPacking = "";
        if (!trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterPacking = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        ini_set("max_execution_time", 120);

        $outputQuery = DB::connection("mysql_sb")->select("
            SELECT
                DISTINCT kode_numbering
            FROM (
                -- Output defects
                SELECT
                    output_defects.kode_numbering
                FROM output_defects
                    LEFT JOIN master_plan ON master_plan.id = output_defects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                WHERE
                    output_defects.id IS NOT NULL
                    {$filterDefectOutput}
                    {$callbackFilterOutput}

                UNION ALL

                -- Output RFT
                SELECT
                    output_rfts.kode_numbering
                FROM output_rfts
                    LEFT JOIN master_plan ON master_plan.id = output_rfts.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                WHERE
                    output_rfts.id IS NOT NULL
                    AND output_rfts.status = 'NORMAL'
                    {$filterRftOutput}
                    {$callbackFilterOutput}

                UNION ALL

                -- Output rejects
                SELECT
                    output_rejects.kode_numbering
                FROM output_rejects
                    LEFT JOIN master_plan ON master_plan.id = output_rejects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                WHERE
                    output_rejects.reject_status = 'mati'
                    {$filterRejectOutput}
                    {$callbackFilterOutput}

                UNION ALL

                -- Output defects packing
                SELECT
                    output_defects.kode_numbering
                FROM output_defects_packing AS output_defects
                    LEFT JOIN master_plan ON master_plan.id = output_defects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN userpassword ON userpassword.username = output_defects.created_by
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                WHERE
                    output_defects.id IS NOT NULL
                    {$filterDefectPck}
                    {$callbackFilterPacking}

                UNION ALL

                -- Output RFT packing
                SELECT
                    output_rfts.kode_numbering
                FROM output_rfts_packing AS output_rfts
                    LEFT JOIN master_plan ON master_plan.id = output_rfts.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN userpassword ON userpassword.username = output_rfts.created_by
                WHERE
                    output_rfts.id IS NOT NULL
                    AND output_rfts.status = 'NORMAL'
                    {$filterRftPck}
                    {$callbackFilterPacking}

                UNION ALL

                -- Output rejects packing
                SELECT
                    output_rejects.kode_numbering
                FROM output_rejects_packing AS output_rejects
                    LEFT JOIN master_plan ON master_plan.id = output_rejects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN userpassword ON userpassword.username = output_rejects.created_by
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                WHERE
                    output_rejects.reject_status = 'mati'
                    {$filterRejectPck}
                    {$callbackFilterPacking}
            ) AS kode_list
        ");

        $kodeList = "'none'";
        if (count($outputQuery) > 0) {
            $kodeList = addQuotesAround(implode("\n", array_column($outputQuery, 'kode_numbering')));
        }

        $outputList = DB::connection("mysql_sb")->select("
            select
                COALESCE(output.kode_numbering, output_packing.kode_numbering, id_year_sequence) kode,
                COALESCE(output.Supplier, ys.buyer) buyer,
                COALESCE(output.ws, ys.ws) ws,
                COALESCE(output.styleno, ys.styleno) style,
                COALESCE(output.color, ys.color) color,
                COALESCE(output.size, ys.size) size,
                COALESCE(stk.id_qr_stocker, stk_bk.id_qr_stocker) as stocker,
                COALESCE(loading.nama_line, loading_bk.nama_line) as line_loading,
                COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) as tanggal_loading,
                output.tgl_plan tanggal_plan,
                output.tgl tanggal_output,
                output.line line_output,
                output.status status_output,
                output.defect_type as defect_output,
                output.allocation as allocation_output,
                output_packing.tgl tanggal_output_packing,
                output_packing.line line_output_packing,
                output_packing.status status_output_packing,
                output_packing.defect_type as defect_output_packing,
                output_packing.allocation as allocation_output_packing
            from
                (
                    select
                        ys.*,
                        msb.buyer,
                        msb.ws,
                        msb.styleno,
                        msb.color
                    from
                        laravel_nds.year_sequence as ys
                        left join laravel_nds.master_sb_ws as msb on msb.id_so_det = ys.so_det_id
                    where
                        ys.id is not null
                        ".$filterYs."
                        ".$callbackFilterYs."
                ) as ys
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_defects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rejects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        and kode_numbering in (".$kodeList.")
                ) output ON output.kode_numbering = ys.id_year_sequence
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects_packing as output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_defects.created_by
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts_packing as output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rfts.created_by
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects_packing as output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rejects.created_by
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        and kode_numbering in (".$kodeList.")
                ) output_packing ON output_packing.kode_numbering = output.kode_numbering
            left join laravel_nds.stocker_input as stk on stk.id_qr_stocker = ys.id_qr_stocker
            left join laravel_nds.stocker_input as stk_bk on (stk_bk.form_cut_id = ys.form_cut_id and stk_bk.form_reject_id = ys.form_reject_id and stk_bk.form_piece_id = ys.form_piece_id) and stk_bk.so_det_id = ys.so_det_id and CAST(stk_bk.range_awal AS UNSIGNED) <= CAST(ys.number AS UNSIGNED) and CAST(stk_bk.range_akhir AS UNSIGNED) >= CAST(ys.number AS UNSIGNED)
            left join laravel_nds.loading_line as loading on loading.stocker_id = stk.id
            left join laravel_nds.loading_line as loading_bk on loading_bk.stocker_id = stk_bk.id
            WHERE
                ys.id is not null
                ".$tglLoading."
                ".$lineLoading."
                ".$statusOutput."
                ".$statusPacking."
                ".$crossLineLoading."
                ".$crossLineOutput."
                ".$additionalFilter."
        ");

        return Datatables::of($outputList)->toJson();
    }

    public function checkOutputDetailExport(Request $request) {
        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($request->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$request->buyer."'";
            $buyerFilterOutput = " and mastersupplier.Supplier = '".$request->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($request->ws) {
            $wsFilterYs = " and msb.id_act_cost = '".$request->ws."'";
            $wsFilterOutput = " and act_costing.id = '".$request->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->style) {
            $styleFilterYs = " and msb.styleno = '".$request->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$request->style."'";
        }

        $colorFilterYs = "";
        $colorFilterOutput = "";
        if ($request->color) {
            $colorFilterYs = " and msb.color = '".$request->color."'";
            $colorFilterOutput = " and so_det.color = '".$request->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($request->size && count($request->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $request->size));

            $sizeFilterYs = " and msb.id_so_det in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.id in (".$sizeList.")";
        }

        $kodeFilterYs = "";
        $kodeFilterOutput = "";
        if ($request->kode && strlen($request->kode) > 0) {
            $kodeList = addQuotesAround($request->kode);

            $kodeFilterYs = " and ys.id_year_sequence in (".$kodeList.")";
            $kodeFilterOutput = " and kode_numbering in (".$kodeList.")";
        }

        $additionalFilter = "";

        $tglLoading = "";
        if ($request->tanggal_loading_awal) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) >= '".$request->tanggal_loading_awal."'";
        }

        if ($request->tanggal_loading_akhir) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading)<= '".$request->tanggal_loading_akhir."'";
        }

        $lineLoading = "";
        if ($request->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$request->line_loading."'";
        }

        $tglPlan = "";
        if ($request->tanggal_plan_awal || $request->tanggal_plan_akhir) {
            if ($request->tanggal_plan_awal) {
                $tglPlan .= " and master_plan.tgl_plan >= '".$request->tanggal_plan_awal."'";
            }
            if ($request->tanggal_plan_akhir) {
                $tglPlan .= " and master_plan.tgl_plan <= '".$request->tanggal_plan_akhir."'";
            }
            $additionalFilter .= "output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($request->tanggal_output_awal || $request->tanggal_output_akhir) {
            $tglAwalOutput = $request->tanggal_output_awal ? $request->tanggal_output_awal : date("Y-m-d");
            $tglAkhirOutput = $request->tanggal_output_akhir ? $request->tanggal_output_akhir : date("Y-m-d");

            $tglOutput = " and output_rfts.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";

            $additionalFilter .= " and output.tgl is not null";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($request->tanggal_packing_awal || $request->tanggal_packing_akhir) {
            $tglAwalPacking = $request->tanggal_packing_awal ? $request->tanggal_packing_awal : date("Y-m-d");
            $tglAkhirPacking = $request->tanggal_packing_akhir ? $request->tanggal_packing_akhir : date("Y-m-d");

            $tglOutputPck = " and output_rfts.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";

            $additionalFilter .= " and output_packing.tgl is not null";
        }

        // Sewing
        $lineOutput = "";
        if ($request->line_output) {
            $lineOutput = " and userpassword.username = '".$request->line_output."'";
            $additionalFilter .= " and output.line is not null";
        }

        $statusOutput = "";
        if ($request->status_output && count($request->status_output) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status_output));

            $statusOutput = " and output.status in (".$statusList.")";
        }

        $defectOutput = "";
        if ($request->defect_output && count($request->defect_output) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_output));

            $defectOutput = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output.defect_type is not null";
        }

        $allocationOutput = "";
        if ($request->allocation_output && count($request->allocation_output) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_output));

            $allocationOutput = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output.allocation is not null";
        }

        // Packing
        $linePacking = "";
        if ($request->line_packing) {
            $linePacking = " and userpassword.username = '".$request->line_packing."'";
            $additionalFilter .= " and output_packing.line is not null";
        }

        $statusPacking = "";
        if ($request->status_packing && count($request->status_packing) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($request->defect_packing && count($request->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_packing));

            $defectPacking = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($request->allocation_packing && count($request->allocation_packign) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_packing));

            $allocationPacking = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output_packing.allocation is not null";
        }

        // Cross-line loading
        $crossLineLoading = "";
        if ($request->crossline_loading) {
            $crossLineLoading = " and output.line != COALESCE(loading.nama_line, loading_bk.nama_line)";
            $additionalFilter .= " and output.line is not null and COALESCE(loading.nama_line, loading_bk.nama_line) is not null";
        }

        // Cross-line output
        $crossLineOutput = "";
        if ($request->crossline_output) {
            $crossLineOutput = " and output.line != output_packing.line";
            $additionalFilter .= " and output.line is not null and output_packing.line is not null";
        }

        // Missmatch
        $missmatchOutput = "";
        $missmatchDefect = "";
        $missmatchReject = "";
        if ($request->missmatch_code) {
            $missmatchOutput = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefect = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchReject = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output.kode_numbering is not null";
        }

        // Missmatch
        $missmatchOutputPck = "";
        $missmatchDefectPck = "";
        $missmatchRejectPck = "";
        if ($request->missmatch_code_packing) {
            $missmatchOutputPck = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefectPck = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchRejectPck = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output_packing.kode_numbering is not null";
        }

        // Backdate
        $backDateOutput = "";
        $backDateDefect = "";
        $backDateReject = "";
        if ($request->back_date) {
            $backDateOutput = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefect = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateReject = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output.tgl is not null";
        }

        // Backdate
        $backDateOutputPck = "";
        $backDateDefectPck = "";
        $backDateRejectPck = "";
        if ($request->back_date_packing) {
            $backDateOutputPck = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefectPck = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateRejectPck = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output_packing.tgl is not null";
        }

        $filterYs = $buyerFilterYs."
                    ".$wsFilterYs."
                    ".$styleFilterYs."
                    ".$colorFilterYs."
                    ".$sizeFilterYs."
                    ".$kodeFilterYs;

        $filterDefectOutput = $tglPlan."
                    ".$tglDefect."
                    ".$backDateDefect."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchDefect."
                    ".$backDateDefect;

        $filterRftOutput = $tglPlan."
                    ".$tglOutput."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$missmatchOutput."
                    ".$backDateOutput;

        $filterRejectOutput = $tglPlan."
                    ".$tglReject."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchReject."
                    ".$backDateReject;

        $filterDefectPck = $tglPlan."
                    ".$tglDefectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchDefectPck."
                    ".$backDateDefectPck;

        $filterRftPck = $tglPlan."
                    ".$tglOutputPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$linePacking."
                    ".$missmatchOutputPck."
                    ".$backDateOutputPck;

        $filterRejectPck = $tglPlan."
                    ".$tglRejectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchRejectPck."
                    ".$backDateRejectPck;

        // Callback
        $callbackFilterYs = "";
        if (!trim(str_replace("\n", "", $filterYs)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterRejectOutput)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterYs = " and DATE(ys.updated_at) > CURRENT_DATE()";
        }

        $callbackFilterOutput = "";
        if (!trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRejectOutput))) {
            $callbackFilterOutput = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $callbackFilterPacking = "";
        if (!trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterPacking = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $outputQuery = DB::connection("mysql_sb")->select("
            SELECT
                DISTINCT kode_numbering
            FROM (
                -- Output defects
                SELECT
                    output_defects.kode_numbering
                FROM output_defects
                    LEFT JOIN master_plan ON master_plan.id = output_defects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                WHERE
                    output_defects.id IS NOT NULL
                    {$filterDefectOutput}
                    {$callbackFilterOutput}

                UNION ALL

                -- Output RFT
                SELECT
                    output_rfts.kode_numbering
                FROM output_rfts
                    LEFT JOIN master_plan ON master_plan.id = output_rfts.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                WHERE
                    output_rfts.id IS NOT NULL
                    AND output_rfts.status = 'NORMAL'
                    {$filterRftOutput}
                    {$callbackFilterOutput}

                UNION ALL

                -- Output rejects
                SELECT
                    output_rejects.kode_numbering
                FROM output_rejects
                    LEFT JOIN master_plan ON master_plan.id = output_rejects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                WHERE
                    output_rejects.reject_status = 'mati'
                    {$filterRejectOutput}
                    {$callbackFilterOutput}

                UNION ALL

                -- Output defects packing
                SELECT
                    output_defects.kode_numbering
                FROM output_defects_packing AS output_defects
                    LEFT JOIN master_plan ON master_plan.id = output_defects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN userpassword ON userpassword.username = output_defects.created_by
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                WHERE
                    output_defects.id IS NOT NULL
                    {$filterDefectPck}
                    {$callbackFilterPacking}

                UNION ALL

                -- Output RFT packing
                SELECT
                    output_rfts.kode_numbering
                FROM output_rfts_packing AS output_rfts
                    LEFT JOIN master_plan ON master_plan.id = output_rfts.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN userpassword ON userpassword.username = output_rfts.created_by
                WHERE
                    output_rfts.id IS NOT NULL
                    AND output_rfts.status = 'NORMAL'
                    {$filterRftPck}
                    {$callbackFilterPacking}

                UNION ALL

                -- Output rejects packing
                SELECT
                    output_rejects.kode_numbering
                FROM output_rejects_packing AS output_rejects
                    LEFT JOIN master_plan ON master_plan.id = output_rejects.master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    LEFT JOIN userpassword ON userpassword.username = output_rejects.created_by
                    LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                WHERE
                    output_rejects.reject_status = 'mati'
                    {$filterRejectPck}
                    {$callbackFilterPacking}
            ) AS kode_list
        ");

        $kodeList = "'none'";
        if (count($outputQuery) > 0) {
            $kodeList = addQuotesAround(implode("\n", array_column($outputQuery, 'kode_numbering')));
        }

        $outputList ="
            select
                COALESCE(output.kode_numbering, output_packing.kode_numbering, id_year_sequence) kode,
                COALESCE(output.Supplier, ys.buyer) buyer,
                COALESCE(output.ws, ys.ws) ws,
                COALESCE(output.styleno, ys.styleno) style,
                COALESCE(output.color, ys.color) color,
                COALESCE(output.size, ys.size) size,
                COALESCE(stk.id_qr_stocker, stk_bk.id_qr_stocker) as stocker,
                COALESCE(loading.nama_line, loading_bk.nama_line) as line_loading,
                COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) as tanggal_loading,
                output.tgl_plan tanggal_plan,
                output.tgl tanggal_output,
                output.line line_output,
                output.status status_output,
                output.defect_type as defect_output,
                output.allocation as allocation_output,
                output_packing.tgl tanggal_output_packing,
                output_packing.line line_output_packing,
                output_packing.status status_output_packing,
                output_packing.defect_type as defect_output_packing,
                output_packing.allocation as allocation_output_packing
            from
                (
                    select
                        ys.*,
                        msb.buyer,
                        msb.ws,
                        msb.styleno,
                        msb.color
                    from
                        laravel_nds.year_sequence as ys
                        left join laravel_nds.master_sb_ws as msb on msb.id_so_det = ys.so_det_id
                    where
                        ys.id is not null
                        ".$filterYs."
                        ".$callbackFilterYs."
                ) as ys
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_defects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rejects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        and kode_numbering in (".$kodeList.")
                ) output ON output.kode_numbering = ys.id_year_sequence
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects_packing as output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_defects.created_by
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts_packing as output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rfts.created_by
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        and kode_numbering in (".$kodeList.")
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects_packing as output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rejects.created_by
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        and kode_numbering in (".$kodeList.")
                ) output_packing ON output_packing.kode_numbering = output.kode_numbering
            left join laravel_nds.stocker_input as stk on stk.id_qr_stocker = ys.id_qr_stocker
            left join laravel_nds.stocker_input as stk_bk on (stk_bk.form_cut_id = ys.form_cut_id and stk_bk.form_reject_id = ys.form_reject_id and stk_bk.form_piece_id = ys.form_piece_id) and stk_bk.so_det_id = ys.so_det_id and CAST(stk_bk.range_awal AS UNSIGNED) <= CAST(ys.number AS UNSIGNED) and CAST(stk_bk.range_akhir AS UNSIGNED) >= CAST(ys.number AS UNSIGNED)
            left join laravel_nds.loading_line as loading on loading.stocker_id = stk.id
            left join laravel_nds.loading_line as loading_bk on loading_bk.stocker_id = stk_bk.id
            WHERE
                ys.id is not null
                ".$tglLoading."
                ".$lineLoading."
                ".$statusOutput."
                ".$statusPacking."
                ".$crossLineLoading."
                ".$crossLineOutput."
                ".$additionalFilter."
        ";

        $buyer = $request->buyer;
        $ws = $request->ws;
        $style = $request->style;
        $color = $request->color;
        $size = $request->size;
        $kode = $request->kode;
        $tanggal_loading = $request->tanggal_loading;
        $line_loading = $request->line_loading;
        $tanggal_plan = $request->tanggal_plan;
        $tanggal_output = $request->tanggal_output;
        $tanggal_packing = $request->tanggal_packing;
        $line_output = $request->line_output;
        $status_output = $request->status_output;
        $defect_output = $request->defect_output;
        $allocation_output = $request->allocation_output;
        $line_packing = $request->line_packing;
        $status_packing = $request->status_packing;
        $defect_packing = $request->defect_packing;
        $allocation_packing = $request->allocation_packing;
        $crossline_loading = $request->crossline_loading;
        $crossline_output = $request->crossline_output;
        $missmatch_code = $request->missmatch_code;
        $missmatch_code_packing = $request->missmatch_code_packing;
        $back_date = $request->back_date;
        $back_date_packing = $request->back_date_packing;

        return Excel::download(new CheckOutputDetailListExport($outputList, $buyer, $ws, $style, $color, $size, $kode, $tanggal_loading, $line_loading, $tanggal_plan, $tanggal_output, $tanggal_packing, $line_output, $status_output, $defect_output, $allocation_output, $line_packing, $status_packing, $defect_packing, $allocation_packing, $crossline_loading, $crossline_output, $missmatch_code, $missmatch_code_packing, $back_date, $back_date_packing), 'Laporan Output Detail.xlsx');
    }

    public function undoOutput(Request $request) {
        return view("sewing.tools.undo-output", ["page" => "dashboard-sewing-eff"]);
    }

    public function undoOutputSubmit(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);

            if ($request->department) {
                $department = $request->department == "packing" ? "_packing" : "";
            } else {
                $department = "";
            }

            if ($kodeNumbering) {
                $kodeNumberingOutput = collect(
                    DB::connection("mysql_sb")->select("
                        SELECT output.*, act_costing.kpno as ws, act_costing.styleno style, so_det.color, so_det.size, userpassword.username as sewing_line, ".($department && $department == "_packing" ? "'packing' as type" : "'qc' as type")." FROM (
                            select master_plan_id, so_det_id, created_by, kode_numbering, id, created_at, updated_at, 'rft' as status, '-' as defect, '-' as allocation from output_rfts".$department." as output_rfts WHERE status = 'NORMAL' and kode_numbering in (".$kodeNumbering.")
                            UNION
                            select master_plan_id, so_det_id, created_by, kode_numbering, output_defects.id, output_defects.created_at, output_defects.updated_at, defect_status as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_defects".$department." as output_defects left join output_defect_types on output_defect_types.id = output_defects.defect_type_id WHERE kode_numbering in (".$kodeNumbering.")
                            UNION
                            select master_plan_id, so_det_id, created_by, kode_numbering, output_rejects.id, output_rejects.created_at, output_rejects.updated_at, reject_status as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_rejects".$department." as output_rejects left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id WHERE reject_status = 'mati' and kode_numbering in (".$kodeNumbering.")
                        ) output
                        ".
                        (
                            $department && $department == "_packing" ?
                            "
                                left join userpassword on userpassword.username = output.created_by
                            " :
                            "
                                left join user_sb_wip on user_sb_wip.id = output.created_by
                                left join userpassword on userpassword.line_id = user_sb_wip.line_id
                            "
                        )."
                        left join so_det on so_det.id = output.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                    ")
                );

                $result = [];
                foreach ($kodeNumberingOutput as $output) {
                    switch ($output->status) {
                        case 'rft' :
                            // Undo RFT
                            $rft = DB::connection("mysql_sb")->table("output_rfts".$department)->where('id', $output->id)->first();

                            if ($rft) {
                                $deleteRft = DB::connection("mysql_sb")->table("output_rfts".$department)->where('id', $rft->id)->delete();

                                if ($deleteRft) {
                                    DB::connection("mysql_sb")->table("output_undo".$department)->insert(['master_plan_id' => $rft->master_plan_id, 'so_det_id' => $rft->so_det_id, 'output_rft_id' => $rft->id, 'kode_numbering' => $rft->kode_numbering, 'keterangan' => 'rft', 'created_by' => $rft->created_by, 'created_at' => $rft->created_at, 'updated_at' => $rft->updated_at, 'undo_by_nds' => Auth::user()->id]);

                                    array_push($result, "RFT '".$rft->kode_numbering."' -> DELETED");
                                }
                            }

                            break;
                        case 'defect' :
                            // Undo DEFECT
                            $defect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $output->id)->first();

                            if ($defect) {
                                $deleteDefect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $defect->id)->delete();

                                if ($deleteDefect) {
                                    DB::connection("mysql_sb")->table("output_undo".$department)->insert(['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_defect_id' => $defect->id, 'kode_numbering' => $defect->kode_numbering, 'keterangan' => 'defect', 'defect_type_id' => $defect->defect_type_id, 'defect_area_id' => $defect->defect_area_id, 'defect_area_x' => $defect->defect_area_x, 'defect_area_y' => $defect->defect_area_y,'created_by' => $defect->created_by, 'created_at' => $defect->created_at, 'updated_at' => $defect->updated_at, 'undo_by_nds' => Auth::user()->id]);

                                    array_push($result, "DEFECT '".$defect->kode_numbering."' -> DELETED");
                                }
                            }

                            break;
                        case 'rejected' :
                            // Undo Reject
                            $defect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $output->id)->first();

                            if ($defect) {
                                $reject = DB::connection("mysql_sb")->table("output_rejects".$department)->where('defect_id', $defect->id)->first();

                                if ($reject) {
                                    $deleteReject = DB::connection("mysql_sb")->table("output_rejects".$department)->where("id", $reject->id)->delete();

                                    if ($deleteReject) {
                                        DB::connection("mysql_sb")->table("output_undo".$department)->insert(['master_plan_id' => $reject->master_plan_id, 'so_det_id' => $reject->so_det_id, 'output_defect_id' => $defect->id, 'output_reject_id' => $reject->id, 'kode_numbering' => $reject->kode_numbering, 'defect_type_id' => $reject->reject_type_id, 'defect_area_id' => $reject->reject_area_id, 'defect_area_x' => $reject->reject_area_x, 'defect_area_y' => $reject->reject_area_y, 'keterangan' => 'defect-reject', 'created_by' => $reject->created_by, 'created_at' => $reject->created_at, 'updated_at' => $reject->updated_at, 'undo_by_nds' => Auth::user()->id]);

                                        DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $defect->id)->update([
                                            "defect_status" => "defect"
                                        ]);

                                        array_push($result, "REJECT '".$reject->kode_numbering."' -> DEFECT");
                                    }
                                }
                            }

                            break;
                        case 'reworked' :
                            // Undo REWORK
                            $defect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $output->id)->first();

                            $rework = DB::connection("mysql_sb")->table("output_reworks".$department)->where('defect_id', $defect->id)->first();

                            $rft = DB::connection("mysql_sb")->table("output_rfts".$department)->where('rework_id', $rework->id)->first();

                            $deleteRework = DB::connection("mysql_sb")->table("output_reworks".$department)->where('id', $rework->id)->delete();

                            if ($deleteRework) {
                                DB::connection("mysql_sb")->table("output_undo".$department)->insert(['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_defect_id' => $defect->id, 'output_rft_id' => $rft->id, 'output_rework_id' => $rework->id, 'kode_numbering' => $defect->kode_numbering, 'keterangan' => 'defect-rework', 'created_by' => $defect->created_by, 'created_at' => $defect->created_at, 'updated_at' => $defect->updated_at, 'undo_by_nds' => Auth::user()->id]);

                                DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $defect->id)->update([
                                    "defect_status" => "defect"
                                ]);

                                DB::connection("mysql_sb")->table("output_rfts".$department)->where("rework_id", $rework->id)->delete();

                                array_push($result, "REWORK '".$defect->kode_numbering."' -> DEFECT");
                            }

                            break;
                        case 'mati' :
                            // Undo REJECT
                            $reject = DB::connection("mysql_sb")->table("output_rejects".$department)->where('id', $output->id)->first();

                            $deleteReject = DB::connection("mysql_sb")->table("output_rejects".$department)->where('id', $reject->id)->delete();

                            if ($deleteReject) {
                                DB::connection("mysql_sb")->table("output_undo".$department)->insert(['master_plan_id' => $reject->master_plan_id, 'so_det_id' => $reject->so_det_id, 'output_reject_id' => $reject->id, 'kode_numbering' => $reject->kode_numbering, 'keterangan' => 'reject', 'defect_type_id' => $reject->reject_type_id, 'defect_area_id' => $reject->reject_area_id, 'defect_area_x' => $reject->reject_area_x, 'defect_area_y' => $reject->reject_area_y, 'created_by' => $reject->created_by, 'created_at' => $reject->created_at, 'updated_at' => $reject->updated_at, 'undo_by_nds' => Auth::user()->id]);

                                array_push($result, "REJECT '".$reject->kode_numbering."' -> DELETED");
                            }

                            break;
                    }
                }

                return $result;
            }
        }
    }

    public function lineMigration() {
        $lines = UserLine::select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("sewing.tools.line-migration", [
            "page" => "dashboard-sewing-eff",
            "lines" => $lines,
        ]);
    }

    public function lineMigrationSubmit(Request $request) {
        $validatedRequest = $request->validate([
            'tanggal_from' => 'required',
            'line_from' => 'required',
            'master_plan_from' => 'required',
            'line_to' => 'required'
        ]);

        if ($validatedRequest) {
            $tanggalFrom = $request->tanggal_from;
            $lineFrom = $request->line_from;
            $masterPlanFrom = $request->master_plan_from;
            $lineTo = $request->line_to;

            $masterPlan = MasterPlan::where('id', $masterPlanFrom)->first();

            if ($masterPlan) {
                $newMasterPlan = MasterPlan::create([
                    "id_ws" => $masterPlan->id_ws,
                    "color" => $masterPlan->color,
                    "tgl_plan" => $masterPlan->tgl_plan,
                    "jam_kerja" => $masterPlan->jam_kerja,
                    "smv" => $masterPlan->smv,
                    "man_power" => $masterPlan->man_power,
                    "plan_target" => $masterPlan->plan_target,
                    "sewing_line" => $lineTo,
                ]);

                if ($newMasterPlan) {
                    $lineId = UserLine::where('username', $lineTo)->value('line_id');

                    // RFT
                    $updateRft = DB::connection("mysql_sb")->table('output_rfts')->where('master_plan_id', $masterPlan->id)->update([
                        "master_plan_id" => $newMasterPlan->id,
                        "created_by" => $lineId,
                    ]);

                    // Defect & Rework
                    $updateDefect = Defect::where('master_plan_id', $masterPlan->id)->get();
                    foreach ($updateDefect as $defect) {
                        $defect->timestamps = false;
                        $defect->master_plan_id = $newMasterPlan->id;
                        $defect->created_by = $lineId;
                        $defect->save();

                        DB::connection("mysql_sb")->table('output_reworks')->where('defect_id', $defect->id)->update([
                            "created_by" => $lineId,
                        ]);
                    }

                    // Reject
                    $updateReject = DB::connection("mysql_sb")->table('output_rejects')->where('master_plan_id', $masterPlan->id)->update([
                        "master_plan_id" => $newMasterPlan->id,
                        "created_by" => $lineId,
                    ]);

                    $masterPlan->cancel = 'Y';
                    $masterPlan->save();

                    $orderInfo = ActCosting::where('id', $masterPlan->id_ws)->first();

                    return array(
                        "status" => "200",
                        "message" => "Migrasi Line berhasil dari '".$lineFrom."' ke '".$lineTo."' <br> Master Plan ID : '".$masterPlan->id."' <br> Tanggal : ".$masterPlan->tgl_plan." <br> WS : ".$orderInfo->kpno." <br> Color : ".$masterPlan->color."",
                    );
                }

                return array(
                    "status" => "400",
                    "message" => "Terjadi kesalahan.",
                );
            }

            return array(
                "status" => "400",
                "message" => "Master Plan tidak ditemukan.",
            );
        }

        return array(
            "status" => "400",
            "message" => "Harap tentukan line dan masterplan.",
        );
    }

    public function modifyOutput(Request $request) {
        $lines = UserLine::select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno', 'styleno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view("sewing.tools.modify-output", ["lines" => $lines, "orders" => $orders, "page" => "dashboard-sewing-eff"]);
    }

    public function modifyOutputAction(Request $request) {
        switch ($request->type) {
            case 'rft_' :
                // Take Rft
                $rfts = DB::connection("mysql_sb")->table("output_rfts".$request->dept." as output_rfts")
                    ->select("output_rfts.*")
                    ->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id");
                    if ($request->dept == "_packing") {
                        $rfts->leftJoin("userpassword", "userpassword.username", "=", "output_rfts.created_by");
                    } else {
                        $rfts->leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rfts.created_by")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id");
                    }
                    $rfts->where("output_rfts.status", "NORMAL")
                    ->where("userpassword.username", $request->line)
                    ->where("master_plan.id", $request->master_plan_id)
                    ->where("output_rfts.so_det_id", $request->so_det_id)
                    ->take($request->qty);

                $rftIds = $rfts->pluck("id")->toArray();

                if (count($rftIds) > 0) {
                    // Undo
                    if ($request->action == "undo") {
                        // Log
                        $undoArray = [];
                        foreach ($rftIds as $rftId) {
                            $rft = DB::connection("mysql_sb")->table("output_rfts".$request->dept)->where('id', $rftId)->first();

                            if ($rft) {
                                array_push($undoArray, ['master_plan_id' => $rft->master_plan_id, 'so_det_id' => $rft->so_det_id, 'output_rft_id' => $rft->id, 'kode_numbering' => $rft->kode_numbering, 'keterangan' => 'rft', 'created_by' => $rft->created_by, 'undo_by_nds' => Auth::user()->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                            }
                        }

                        DB::connection("mysql_sb")->table("output_undo".$request->dept)->insert($undoArray);

                        // Delete
                        $deleteRft = DB::connection("mysql_sb")->table("output_rfts".$request->dept)->whereIn('id', $rftIds)->delete();

                        if ($deleteRft) {
                            return [
                                "status"  => 200,
                                "message" => $deleteRft." berhasil di UNDO.",
                            ];
                        }
                    // Modify
                    } else {
                        // Check So Det
                        $modSoDet = SoDet::selectRaw("so_det.id, act_costing.id id_ws, so_det.color, so_det.size")
                            ->leftJoin("so", "so.id", "=", "so_det.id_so")
                            ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
                            ->where("so_det.id", $request->mod_so_det_id)
                            ->first();

                        if ($modSoDet) {
                            // Check Master Plan
                            $modMasterPlan = MasterPlan::select("master_plan.id", "master_plan.sewing_line")
                                ->where("tgl_plan", $request->tanggal)
                                ->where("id_ws", $modSoDet->id_ws)
                                ->where("color", $modSoDet->color)
                                ->where("sewing_line", $request->line)
                                ->whereRaw("(cancel IS NULL or cancel = 'N')")
                                ->first();

                            $userPlan = UserSbWip::select("user_sb_wip.id")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")
                                ->where("userpassword.username", $modMasterPlan->sewing_line)
                                ->orderBy("user_sb_wip.id", "desc")
                                ->first();

                            if ($modMasterPlan) {
                                $updateRfts = DB::connection("mysql_sb")->table("output_rfts".$request->dept)->whereIn("id", $rftIds)->update([
                                    "so_det_id"       => $modSoDet->id,
                                    "master_plan_id"  => $modMasterPlan->id,
                                    "created_by" => ($request->dept == '_packing' ? $modMasterPlan->sewing_line : $userPlan->id)
                                ]);

                                if ($updateRfts) {
                                    return [
                                        "status"  => 200,
                                        "message" => $updateRfts." RFT berhasil di ubah.",
                                    ];
                                }
                            } else {
                                return [
                                    "status"  => 400,
                                    "message" => "Master Plan untuk size tujuan tidak ditemukan.",
                                ];
                            }
                        } else {
                            return [
                                "status"  => 400,
                                "message" => "Size tujuan tidak ditemukan.",
                            ];
                        }
                    }
                }

                break;

            case 'defect_' :
                // Take Defect
                $defects = DB::connection("mysql_sb")->table("output_defects".$request->dept." as output_defects")
                    ->select("output_defects.*")
                    ->leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id");
                    if ($request->dept == "_packing") {
                        $defects->leftJoin("userpassword", "userpassword.username", "=", "output_defects.created_by");
                    } else {
                        $defects->leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id");
                    }
                    $defects->where("output_defects.defect_status", "defect")
                    ->where("userpassword.username", $request->line)
                    ->where("master_plan.id", $request->master_plan_id)
                    ->where("output_defects.so_det_id", $request->so_det_id)
                    ->take($request->qty);

                $defectIds = $defects->pluck("id")->toArray();

                if (count($defectIds) > 0) {
                    // Undo
                    if ($request->action == "undo") {
                        // Log
                        $undoArray = [];
                        foreach ($defectIds as $defectId) {
                            $defect = DB::connection("mysql_sb")->table("output_defects".$request->dept)->where('id', $defectId)->first();

                            if ($defect) {
                                array_push($undoArray, ['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_defect_id' => $defect->id, 'kode_numbering' => $defect->kode_numbering, 'keterangan' => 'defect', 'defect_type_id' => $defect->defect_type_id, 'defect_area_id' => $defect->defect_area_id, 'defect_area_x' => $defect->defect_area_x, 'defect_area_y' => $defect->defect_area_y, 'created_by' => $defect->created_by, 'undo_by_nds' => Auth::user()->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                            }
                        }

                        // Delete
                        $deleteDefect = DB::connection("mysql_sb")->table("output_defects".$request->dept)->whereIn('id', $defectIds)->delete();

                        if ($deleteDefect) {
                            return [
                                "status"  => 200,
                                "message" => $deleteDefect." berhasil di UNDO.",
                            ];
                        }
                    // Modify
                    } else {
                        // Check So Det
                        $modSoDet = SoDet::selectRaw("so_det.id, act_costing.id id_ws, so_det.color, so_det.size")
                            ->leftJoin("so", "so.id", "=", "so_det.id_so")
                            ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
                            ->where("so_det.id", $request->mod_so_det_id)
                            ->first();

                        if ($modSoDet) {
                            // Check Master Plan
                            $modMasterPlan = MasterPlan::select("master_plan.id", "sewing_line")
                                ->where("tgl_plan", $request->tanggal)
                                ->where("id_ws", $modSoDet->id_ws)
                                ->where("color", $modSoDet->color)
                                ->where("sewing_line", $request->line)
                                ->whereRaw("(cancel IS NULL or cancel = 'N')")
                                ->first();

                            $userPlan = UserSbWip::select("user_sb_wip.id")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")
                                ->where("userpassword.username", $modMasterPlan->sewing_line)
                                ->orderBy("user_sb_wip.id", "desc")
                                ->first();

                            if ($modMasterPlan) {
                                $updateDefects = DB::connection("mysql_sb")->table("output_defects".$request->dept)->whereIn("id", $defectIds)->update([
                                    "so_det_id"       => $modSoDet->id,
                                    "master_plan_id"  => $modMasterPlan->id,
                                    "created_by" => ($request->dept == '_packing' ? $modMasterPlan->sewing_line : $userPlan->id)
                                ]);

                                if ($updateDefects) {
                                    return [
                                        "status"  => 200,
                                        "message" => $updateDefects." Defect berhasil di ubah.",
                                    ];
                                }
                            } else {
                                return [
                                    "status"  => 400,
                                    "message" => "Master Plan untuk size tujuan tidak ditemukan.",
                                ];
                            }
                        } else {
                            return [
                                "status"  => 400,
                                "message" => "Size tujuan tidak ditemukan.",
                            ];
                        }
                    }
                }

                break;

            case 'rework_' :
                // Take Reworks
                $defects = DB::connection("mysql_sb")->table("output_defects".$request->dept." as output_defects")
                    ->select("output_defects.*")
                    ->leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id");
                    if ($request->dept == "_packing") {
                        $defects->leftJoin("userpassword", "userpassword.username", "=", "output_defects.created_by");
                    } else {
                        $defects->leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id");
                    }
                    $defects->where("output_defects.defect_status", "reworked")
                    ->where("userpassword.username", $request->line)
                    ->where("master_plan.id", $request->master_plan_id)
                    ->where("output_defects.so_det_id", $request->so_det_id)
                    ->take($request->qty);

                $defectIds = $defects->pluck("id")->toArray();

                if (count($defectIds) > 0) {
                    // Undo
                    if ($request->action == "undo") {
                        // Log
                        $undoArray = [];
                        foreach ($defectIds as $defectId) {
                            $rework = DB::connection("mysql_sb")->table("output_reworks".$request->dept)->selectRaw("output_reworks.*, output_rfts.id as rft_id, output_rfts.master_plan_id, output_rfts.so_det_id, output_rfts.kode_numbering")->leftJoin("output_rfts", "output_rfts.rework_id", "=", "output_reworks.id")->where('defect_id', $defectId)->first();

                            if ($rework) {
                                array_push($undoArray, ['master_plan_id' => $rework->master_plan_id, 'so_det_id' => $rework->so_det_id, 'output_rework_id' => $rework->id, 'output_rft_id' => $rework->rft_id, 'kode_numbering' => $rework->kode_numbering, 'keterangan' => 'rework', 'created_by' => $rework->created_by, 'undo_by_nds' => Auth::user()->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                            }
                        }

                        DB::connection("mysql_sb")->table("output_undo".$request->dept)->insert($undoArray);

                        // Delete
                        $reworkIds = DB::connection("mysql_sb")->table("output_reworks".$request->dept)->whereIn('defect_id', $defectIds)->pluck("id")->toArray();
                        $deleteReworks = DB::connection("mysql_sb")->table("output_reworks".$request->dept)->whereIn('defect_id', $defectIds)->delete();
                        $deleteRfts = DB::connection("mysql_sb")->table("output_rfts".$request->dept)->whereIn('rework_id', $reworkIds)->delete();

                        if ($deleteReworks) {
                            $updateDefects = DB::connection("mysql_sb")->table("output_defects".$request->dept)->whereIn("id", $defectIds)->update([
                                "defect_status" => "defect",
                            ]);

                            return [
                                "status"  => 200,
                                "message" => $deleteReworks." Rework berhasil di UNDO.",
                            ];
                        }
                    // Modify
                    } else {
                        // Check So Det
                        $modSoDet = SoDet::selectRaw("so_det.id, act_costing.id id_ws, so_det.color, so_det.size")
                            ->leftJoin("so", "so.id", "=", "so_det.id_so")
                            ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
                            ->where("so_det.id", $request->mod_so_det_id)
                            ->first();

                        if ($modSoDet) {
                            // Check Master Plan
                            $modMasterPlan = MasterPlan::select("master_plan.id", "sewing_line")
                                ->where("tgl_plan", $request->tanggal)
                                ->where("id_ws", $modSoDet->id_ws)
                                ->where("color", $modSoDet->color)
                                ->where("sewing_line", $request->line)
                                ->whereRaw("(cancel IS NULL or cancel = 'N')")
                                ->first();

                            $userPlan = UserSbWip::select("user_sb_wip.id")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")
                                ->where("userpassword.username", $modMasterPlan->sewing_line)
                                ->orderBy("user_sb_wip.id", "desc")
                                ->first();

                            if ($modMasterPlan) {
                                $updateDefects = DB::connection("mysql_sb")->table("output_defects".$request->dept)->whereIn("id", $defectIds)->update([
                                    "so_det_id"       => $modSoDet->id,
                                    "master_plan_id"  => $modMasterPlan->id,
                                    "created_by" => ($request->dept == '_packing' ? $modMasterPlan->sewing_line : $userPlan->id)
                                ]);

                                if ($updateDefects) {
                                    $reworkIds = DB::connection("mysql_sb")->table("output_reworks".$request->dept)->whereIn("defect_id", $defectIds)->pluck("id")->toArray();

                                    if ($reworkIds && count($reworkIds) > 0) {
                                        $updateRft = DB::connection("mysql_sb")->table("output_rfts".$request->dept)->whereIn("rework_id", $reworkIds)->update([
                                            "so_det_id"       => $modSoDet->id,
                                            "master_plan_id"  => $modMasterPlan->id,
                                        ]);
                                    }

                                    return [
                                        "status"  => 200,
                                        "message" => $updateDefects." Rework berhasil di ubah.",
                                    ];
                                }
                            } else {
                                return [
                                    "status"  => 400,
                                    "message" => "Master Plan untuk size tujuan tidak ditemukan.",
                                ];
                            }
                        } else {
                            return [
                                "status"  => 400,
                                "message" => "Size tujuan tidak ditemukan.",
                            ];
                        }
                    }
                }

                break;

            case 'reject_' :
                // Take Rejects
                $rejects = DB::connection("mysql_sb")->table("output_rejects".$request->dept." as output_rejects")
                    ->select("output_rejects.*")
                    ->leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id");
                    if ($request->dept == "_packing") {
                        $rejects->leftJoin("userpassword", "userpassword.username", "=", "output_rejects.created_by");
                    } else {
                        $rejects->leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id");
                    }
                    $rejects
                    ->where("userpassword.username", $request->line)
                    ->where("master_plan.id", $request->master_plan_id)
                    ->where("output_rejects.so_det_id", $request->so_det_id)
                    ->take($request->qty);

                $rejectIds = $rejects->pluck("id")->toArray();

                if (count($rejectIds) > 0) {
                    // Undo
                    if ($request->action == "undo") {
                        // Log
                        $undoArray = [];
                        foreach ($rejectIds as $rejectId) {
                            $reject = DB::connection("mysql_sb")->table("output_rejects".$request->dept)->where('id', $rejectId)->first();

                            if ($reject) {
                                array_push($undoArray, ['master_plan_id' => $reject->master_plan_id, 'so_det_id' => $reject->so_det_id, 'output_defect_id' => $reject->defect_id, 'output_reject_id' => $reject->id, 'defect_type_id' => $reject->reject_type_id, 'defect_area_id' => $reject->reject_area_id, 'defect_area_x' => $reject->reject_area_x, 'defect_area_y' => $reject->reject_area_y, 'kode_numbering' => $reject->kode_numbering, 'keterangan' => 'reject', 'created_by' => $reject->created_by, 'undo_by_nds' => Auth::user()->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                            }
                        }

                        DB::connection("mysql_sb")->table("output_undo".$request->dept)->insert($undoArray);

                        // Delete
                        $defectIds = DB::connection("mysql_sb")->table("output_rejects".$request->dept)->whereNotNull("defect_id")->whereIn('id', $rejectIds)->pluck("defect_id")->toArray();
                        $deleteRejects = DB::connection("mysql_sb")->table("output_rejects".$request->dept)->whereIn('id', $rejectIds)->delete();

                        if ($deleteRejects) {
                            $updateDefects = DB::connection("mysql_sb")->table("output_defects".$request->dept)->whereIn("id", $defectIds)->update([
                                "defect_status" => "defect",
                            ]);

                            return [
                                "status"  => 200,
                                "message" => $deleteRejects." Reject berhasil di UNDO.",
                            ];
                        }
                    // Modify
                    } else {
                        // Check So Det
                        $modSoDet = SoDet::selectRaw("so_det.id, act_costing.id id_ws, so_det.color, so_det.size")
                            ->leftJoin("so", "so.id", "=", "so_det.id_so")
                            ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
                            ->where("so_det.id", $request->mod_so_det_id)
                            ->first();

                        if ($modSoDet) {
                            // Check Master Plan
                            $modMasterPlan = MasterPlan::select("master_plan.id", "sewing_line")
                                ->where("tgl_plan", $request->tanggal)
                                ->where("id_ws", $modSoDet->id_ws)
                                ->where("color", $modSoDet->color)
                                ->where("sewing_line", $request->line)
                                ->whereRaw("(cancel IS NULL or cancel = 'N')")
                                ->first();

                            $userPlan = UserSbWip::select("user_sb_wip.id")->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")
                                ->where("userpassword.username", $modMasterPlan->sewing_line)
                                ->orderBy("user_sb_wip.id", "desc")
                                ->first();

                            if ($modMasterPlan) {
                                $updateRejects = DB::connection("mysql_sb")->table("output_rejects".$request->dept)->whereIn("id", $rejectIds)->update([
                                    "so_det_id"       => $modSoDet->id,
                                    "master_plan_id"  => $modMasterPlan->id,
                                    "created_by" => ($request->dept == '_packing' ? $modMasterPlan->sewing_line : $userPlan->id)
                                ]);

                                if ($updateRejects) {
                                    $defectIds = DB::connection("mysql_sb")->table("output_rejects".$request->dept)->whereIn("id", $rejectIds)->whereNotNull("defect_id")->pluck("defect_id")->toArray();

                                    if ($defectIds && count($defectIds) > 0) {
                                        $updateDefects = DB::connection("mysql_sb")->table("output_defects".$request->dept)->whereIn("id", $defectIds)->update([
                                            "so_det_id"       => $modSoDet->id,
                                            "master_plan_id"  => $modMasterPlan->id,
                                        ]);
                                    }

                                    return [
                                        "status"  => 200,
                                        "message" => $updateRejects." Reject berhasil di ubah.",
                                    ];
                                }
                            } else {
                                return [
                                    "status"  => 400,
                                    "message" => "Master Plan untuk size tujuan tidak ditemukan.",
                                ];
                            }
                        } else {
                            return [
                                "status"  => 400,
                                "message" => "Size tujuan tidak ditemukan.",
                            ];
                        }
                    }
                }

                break;

            default:
                return [
                    "status"  => 400,
                    "message" => "Terjadi kesalahan.",
                ];
        }
    }

    public function getMasterPlan(Request $request) {
        // Master Plan for From
        $fromMasterPlanSql = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color,
                master_plan.cancel
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws');
            // where('master_plan.cancel', '!=', 'Y');

        // Date Filter
        if ($request->date) {
            $fromMasterPlanSql->whereRaw('master_plan.tgl_plan = "'.$request->date.'"');
        } else {
            $fromMasterPlanSql->whereRaw('YEAR(master_plan.tgl_plan) = "'.date('Y').'"');
        }
        // Line Filter
        if ($request->line) {
            $fromMasterPlanSql->where('master_plan.sewing_line', $request->line);
        }
        $fromMasterPlans = $fromMasterPlanSql->
            orderBy('master_plan.tgl_plan', 'desc')->
            orderBy('act_costing.kpno', 'asc')->
            get();

        return $fromMasterPlans;
    }

    public function undoDefectInOut(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);

            if ($request->department) {
                $department = $request->department == "packing" ? "_packing" : "";
            } else {
                $department = "";
            }

            if ($kodeNumbering) {
                $kodeNumberingOutput = collect(
                    DB::connection("mysql_sb")->select("
                        SELECT output.*, act_costing.kpno as ws, act_costing.styleno style, so_det.color, so_det.size, userpassword.username as sewing_line, ".($department && $department == "_packing" ? "'packing' as type" : "'qc' as type")." FROM (
                            select master_plan_id, so_det_id, created_by, kode_numbering, id, created_at, updated_at, 'rft' as status, '-' as defect, '-' as allocation from output_rfts".$department." as output_rfts WHERE status = 'NORMAL' and kode_numbering in (".$kodeNumbering.")
                            UNION
                            select master_plan_id, so_det_id, created_by, kode_numbering, output_defects.id, output_defects.created_at, output_defects.updated_at, defect_status as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_defects".$department." as output_defects left join output_defect_types on output_defect_types.id = output_defects.defect_type_id WHERE kode_numbering in (".$kodeNumbering.")
                            UNION
                            select master_plan_id, so_det_id, created_by, kode_numbering, output_rejects.id, output_rejects.created_at, output_rejects.updated_at, reject_status as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_rejects".$department." as output_rejects left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id WHERE reject_status = 'mati' and kode_numbering in (".$kodeNumbering.")
                        ) output
                        ".
                        (
                            $department && $department == "_packing" ?
                            "
                                left join userpassword on userpassword.username = output.created_by
                            " :
                            "
                                left join user_sb_wip on user_sb_wip.id = output.created_by
                                left join userpassword on userpassword.line_id = user_sb_wip.line_id
                            "
                        )."
                        left join so_det on so_det.id = output.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                    ")
                );

                $result = [];
                foreach ($kodeNumberingOutput as $output) {
                    switch ($output->status) {
                        case 'rft' :
                            // Undo RFT
                            $rft = DB::connection("mysql_sb")->table("output_rfts".$department)->where('id', $output->id)->first();

                            if ($rft) {
                                $deleteRft = DB::connection("mysql_sb")->table("output_rfts".$department)->where('id', $rft->id)->delete();

                                if ($deleteRft) {
                                    if ($department && $department != "_packing") {
                                        Undo::create(['master_plan_id' => $rft->master_plan_id, 'so_det_id' => $rft->so_det_id, 'output_rft_id' => $rft->id, 'kode_numbering' => $rft->kode_numbering, 'keterangan' => 'rft', 'created_by' => $rft->created_by, 'undo_by_nds' => Auth::user()->id]);
                                    }

                                    array_push($result, "RFT '".$rft->kode_numbering."' -> DELETED");
                                }
                            }

                            break;
                        case 'defect' :
                            // Undo DEFECT
                            $defect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $output->id)->first();

                            if ($defect) {
                                $deleteDefect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $defect->id)->delete();

                                if ($deleteDefect) {
                                    if ($department && $department != "_packing") {
                                        Undo::create(['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_defect_id' => $defect->id, 'kode_numbering' => $defect->kode_numbering, 'keterangan' => 'defect', 'defect_type_id' => $defect->defect_type_id, 'defect_area_id' => $defect->defect_area_id, 'defect_area_x' => $defect->defect_area_x, 'defect_area_y' => $defect->defect_area_y,'created_by' => $defect->created_by, 'undo_by_nds' => Auth::user()->id]);
                                    }

                                    array_push($result, "DEFECT '".$defect->kode_numbering."' -> DELETED");
                                }
                            }

                            break;
                        case 'rejected' :
                            // Undo Reject
                            $defect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $output->id)->first();

                            if ($defect) {
                                $reject = DB::connection("mysql_sb")->table("output_rejects".$department)->where('defect_id', $defect->id)->first();

                                if ($reject) {
                                    $deleteReject = DB::connection("mysql_sb")->table("output_rejects".$department)->where("id", $reject->id)->delete();

                                    if ($deleteReject) {
                                        if ($department && $department != "_packing") {
                                            Undo::create(['master_plan_id' => $reject->master_plan_id, 'so_det_id' => $reject->so_det_id, 'output_defect_id' => $defect->id, 'output_reject_id' => $reject->id, 'kode_numbering' => $reject->kode_numbering, 'defect_type_id' => $reject->reject_type_id, 'defect_area_id' => $reject->reject_area_id, 'defect_area_x' => $reject->reject_area_x, 'defect_area_y' => $reject->reject_area_y, 'keterangan' => 'defect-reject', 'created_by' => $reject->created_by, 'undo_by_nds' => Auth::user()->id]);
                                        }

                                        DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $defect->id)->update([
                                            "defect_status" => "defect"
                                        ]);

                                        array_push($result, "REJECT '".$reject->kode_numbering."' -> DEFECT");
                                    }
                                }
                            }

                            break;
                        case 'reworked' :
                            // Undo REWORK
                            $defect = DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $output->id)->first();

                            $rework = DB::connection("mysql_sb")->table("output_reworks".$department)->where('defect_id', $defect->id)->first();

                            $rft = DB::connection("mysql_sb")->table("output_rfts".$department)->where('rework_id', $rework->id)->first();

                            $deleteRework = DB::connection("mysql_sb")->table("output_reworks".$department)->where('id', $rework->id)->delete();

                            if ($deleteRework) {
                                if ($department && $department != "_packing") {
                                    Undo::create(['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_rft_id' => $rft->id, 'output_rework_id' => $rework->id, 'kode_numbering' => $defect->kode_numbering, 'keterangan' => 'defect-rework', 'created_by' => $defect->created_by, 'undo_by_nds' => Auth::user()->id]);
                                }

                                DB::connection("mysql_sb")->table("output_defects".$department)->where('id', $defect->id)->update([
                                    "defect_status" => "defect"
                                ]);

                                DB::connection("mysql_sb")->table("output_rfts".$department)->where("rework_id", $rework->id)->delete();

                                array_push($result, "REWORK '".$defect->kode_numbering."' -> DEFECT");
                            }

                            break;
                        case 'mati' :
                            // Undo REJECT
                            $reject = DB::connection("mysql_sb")->table("output_rejects".$department)->where('id', $output->id)->first();

                            $deleteReject = DB::connection("mysql_sb")->table("output_rejects".$department)->where('id', $reject->id)->delete();

                            if ($deleteReject) {
                                if ($department && $department != "_packing") {
                                    Undo::create(['master_plan_id' => $reject->master_plan_id, 'so_det_id' => $reject->so_det_id, 'output_reject_id' => $reject->id, 'kode_numbering' => $reject->kode_numbering, 'keterangan' => 'reject', 'defect_type_id' => $reject->reject_type_id, 'defect_area_id' => $reject->reject_area_id, 'defect_area_x' => $reject->reject_area_x, 'defect_area_y' => $reject->reject_area_y, 'created_by' => $reject->created_by, 'undo_by_nds' => Auth::user()->id]);
                                }

                                array_push($result, "REJECT '".$reject->kode_numbering."' -> DELETED");
                            }

                            break;
                    }
                }

                return $result;
            }
        }
    }

    public function undoReject(Request $request) {
        return view("sewing.tools.undo-reject", ["page" => "dashboard-sewing-eff"]);
    }

    public function undoRejectSubmit(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);

            if ($request->department) {
                $department = $request->department == "packing" ? "_packing" : "";
            } else {
                $department = "";
            }

            if ($kodeNumbering) {
                $kodeNumberingOutput = RejectIn::selectRaw("
                        output_reject_in.id,
                        output_reject_in.kode_numbering,
                        DATE(output_reject_in.created_at) as tanggal,
                        output_reject_in.created_at time_in,
                        output_reject_in.updated_at time_out,
                        master_plan.sewing_line sewing_line,
                        (CASE WHEN output_reject_in.output_type = 'packing' THEN 'finishing' ELSE output_reject_in.output_type END) as output_type,
                        output_reject_in.kode_numbering,
                        mastersupplier.Supplier as buyer,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color color,
                        so_det.size size,
                        master_plan.gambar gambar,
                        output_reject_in.reject_area_x reject_area_x,
                        output_reject_in.reject_area_y reject_area_y,
                        output_reject_in.status,
                        output_reject_in.grade,
                        COALESCE(reject_detail.defect_types_check, output_defect_types.defect_type) as defect_type,
                        COALESCE(reject_detail.defect_areas_check, output_defect_areas.defect_area) as defect_area,
                        output_reject_out.tujuan as allocation
                    ")->
                    // Reject
                    leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
                    // Reject Packing
                    leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
                    // Reject Finishing
                    leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
                    // Reject Detail
                    leftJoin("output_reject_out_detail", "output_reject_out_detail.reject_in_id", "=", "output_reject_in.id")->
                    leftJoin("output_reject_out", "output_reject_out.id", "=", "output_reject_out_detail.reject_out_id")->
                    leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
                    leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
                    leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
                    leftJoin("so", "so.id", "=", "so_det.id_so")->
                    leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                    leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
                    leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
                    leftJoin(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id where output_reject_in_detail.id is not null group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
                    whereRaw("
                        output_reject_in.kode_numbering IN (".$kodeNumbering.")
                        AND
                        output_reject_in.output_type = '".($request->department ? $request->department : 'qc')."'
                    ")->
                    groupByRaw("output_reject_in.id")->
                    get();

                $result = [];
                foreach ($kodeNumberingOutput as $output) {
                    $message = "";

                    // Reject In
                    $rejectInDetail = RejectInDetail::where("reject_in_id", $output->id)->get();
                    if ($rejectInDetail && $rejectInDetail->count() > 0) {
                        $rejectInDetailIds = $rejectInDetail->pluck("id")->toArray();

                        // Delete Detail Position
                        $deleteRejectInDetailPosition = RejectInDetailPosition::whereIn("reject_in_detail_id", $rejectInDetailIds)->delete();

                        // Delete Detail
                        if ($deleteRejectInDetailPosition) {
                            RejectInDetail::where("reject_in_id", $output->id)->delete();

                            $message .= "Reject In ".$output->kode_numbering." -> DELETED <br>";
                        }
                    }

                    // Reject Out
                    $rejectOutDetail = RejectOutDetail::where("reject_in_id", $output->id)->get();
                    if ($rejectOutDetail && $rejectOutDetail->count() > 0) {
                        $rejectOutIds = $rejectOutDetail->pluck("reject_out_id")->toArray();

                        // Delete Group
                        $deleteRejectOut = RejectOut::whereIn("id", $rejectOutIds)->delete();

                        // Delete Detail
                        if ($deleteRejectOut) {
                            RejectOutDetail::where("reject_in_id", $output->id)->delete();

                            $message .= "Reject Out ".$output->kode_numbering." -> DELETED <br>";
                        }
                    }

                    // Reject In
                    $deleteRejectIn = RejectIn::where("id", $output->id)->delete();

                    array_push($result, $message);
                }

                return $result;
            }
        }
    }

    public function restoreUndoSubmit(Request $request)
    {
        $kodeNumbering = addQuotesAround($request->kode_numbering);

        if ($kodeNumbering) {
            $restoreData = Undo::selectRaw("*, output_undo.id as undo_id")->leftJoin("master_plan", "master_plan.id", "=", "output_undo.master_plan_id")->whereRaw("output_undo.kode_numbering is not null and output_undo.kode_numbering in (".$kodeNumbering.")")->get();
            $rft = [];
            $rftNds = [];
            $defect = [];
            $rework = [];
            $reject = [];
            $deleteUndoIds = [];

            foreach ($restoreData as $restore) {
                // RFT
                if ($restore->output_rft_id && $restore->keterangan == "rft") {
                    array_push($rft, [
                        "master_plan_id" => $restore->master_plan_id,
                        "so_det_id" => $restore->so_det_id,
                        'status' => 'NORMAL',
                        "kode_numbering" => $restore->kode_numbering,
                        "no_cut_size" => $restore->kode_numbering,
                        "created_by" => $restore->created_by,
                        "created_at" => $restore->created_at,
                        "updated_at" => $restore->updated_at,
                    ]);

                    // array_push($rftNds, [
                    //     "master_plan_id" => $restore->master_plan_id,
                    //     "so_det_id" => $restore->so_det_id,
                    //     'status' => 'NORMAL',
                    //     "sewing_line" => Auth::user()->username,
                    //     "created_by" => Auth::user()->username,
                    //     "created_at" => $restore->created_at,
                    //     "updated_at" => $restore->updated_at,
                    // ]);
                }

                // DEFECT
                if ($restore->output_defect_id) {
                    array_push($defect, [
                        "master_plan_id" => $restore->master_plan_id,
                        "so_det_id" => $restore->so_det_id,
                        'status' => 'NORMAL',
                        'defect_status' => 'defect',
                        "kode_numbering" => $restore->kode_numbering,
                        "no_cut_size" => $restore->kode_numbering,
                        "defect_type_id" => $restore->defect_type_id,
                        "defect_area_id" => $restore->defect_area_id,
                        "defect_area_x" => $restore->defect_area_x,
                        "defect_area_y" => $restore->defect_area_y,
                        "created_by" => $restore->created_by,
                        "created_at" => $restore->created_at,
                        "updated_at" => $restore->updated_at,
                    ]);
                }

                // REWORK
                if ($restore->output_rework_id) {

                    // With Defect
                    $currentDefect = null;
                    if ($restore->output_defect_id) {
                        // Update Defect
                        $currentDefect = Defect::where("id", $restore->output_defect_id)->first();
                        $currentDefect->timestamps = false;
                        $currentDefect->defect_status = 'reworked';
                        $currentDefect->save();
                    }

                    // No Defect
                    if (!$currentDefect) {
                        // Create Defect
                        $createDefect = Defect::create([
                            "master_plan_id" => $restore->master_plan_id,
                            "so_det_id" => $restore->so_det_id,
                            "kode_numbering" => $restore->kode_numbering,
                            "no_cut_size" => $restore->kode_numbering,
                            "defect_type_id" => $restore->defect_type_id,
                            "defect_area_id" => $restore->defect_area_id,
                            "defect_area_x" => $restore->defect_area_x,
                            "defect_area_y" => $restore->defect_area_y,
                            'status' => 'NORMAL',
                            'defect_status' => 'reworked',
                            "created_by" => $restore->created_by,
                            "created_at" => $restore->created_at,
                            "updated_at" => $restore->updated_at,
                        ]);

                        $currentDefect = $createDefect;
                    }

                    // Create Rework
                    $createRework = Rework::create([
                        "defect_id" => $currentDefect ? $currentDefect->id : '',
                        "status" => "NORMAL",
                        "created_at" => $restore->created_at,
                        "updated_at" => $restore->updated_at,
                    ]);

                    if ($createRework) {
                        // Create RFT
                        $createRft = Rft::create([
                            "master_plan_id" => $restore->master_plan_id,
                            "so_det_id" => $restore->so_det_id,
                            'status' => 'REWORK',
                            'rework_id' => $createRework ? $createRework->id : '',
                            "kode_numbering" => $restore->kode_numbering,
                            "no_cut_size" => $restore->kode_numbering,
                            "created_by" => $restore->created_by,
                            "created_at" => $restore->created_at,
                            "updated_at" => $restore->updated_at,
                        ]);
                    }

                    // Log
                    array_push($rework, [
                        "master_plan_id" => $restore->master_plan_id,
                        "so_det_id" => $restore->so_det_id,
                        'defect_id' => $currentDefect ? $currentDefect->id : '',
                        'rework_id' => $createRework ? $createRework->id : '',
                        'rft_id' => $createRft ? $createRft->id : '',
                        "kode_numbering" => $restore->kode_numbering,
                        "no_cut_size" => $restore->kode_numbering,
                        "created_by" => $restore->created_by,
                        "created_at" => $restore->created_at,
                        "updated_at" => $restore->updated_at,
                    ]);
                }

                // REJECT
                if ($restore->output_reject_id) {
                    array_push($reject, [
                        "master_plan_id" => $restore->master_plan_id,
                        "so_det_id" => $restore->so_det_id,
                        "created_at" => $restore->created_at,
                        "updated_at" => $restore->updated_at,
                    ]);

                    // With REJECT
                    $currentDefect = null;
                    if ($restore->output_defect_id) {
                        // Update REJECT
                        $currentDefect = Defect::where("id", $restore->output_defect_id)->first();

                        // No REJECT
                        if (!$currentDefect) {
                            // Create REJECT
                            $createDefect = Defect::create([
                                "master_plan_id" => $restore->master_plan_id,
                                "so_det_id" => $restore->so_det_id,
                                "kode_numbering" => $restore->kode_numbering,
                                "no_cut_size" => $restore->kode_numbering,
                                "defect_type_id" => $restore->defect_type_id,
                                "defect_area_id" => $restore->defect_area_id,
                                "defect_area_x" => $restore->defect_area_x,
                                "defect_area_y" => $restore->defect_area_y,
                                'status' => 'NORMAL',
                                'defect_status' => 'rejected',
                                "created_by" => $restore->created_by,
                                "created_at" => $restore->created_at,
                                "updated_at" => $restore->updated_at,
                            ]);

                            $currentDefect = $createDefect;
                        } else {
                            $currentDefect->timestamps = false;
                            $currentDefect->defect_status = 'rejected';
                            $currentDefect->save();
                        }
                    }

                    // Create REJECT
                    array_push($reject, [
                        "master_plan_id" => $restore->master_plan_id,
                        "so_det_id" => $restore->so_det_id,
                        'defect_id' => $currentDefect ? $currentDefect->id : '',
                        "kode_numbering" => $restore->kode_numbering,
                        "no_cut_size" => $restore->kode_numbering,
                        'status' => "NORMAL",
                        'reject_status' => $currentDefect ? 'defect' : 'mati',
                        "reject_type_id" => $restore->defect_type_id,
                        "reject_area_id" => $restore->defect_area_id,
                        "reject_area_x" => $restore->defect_area_x,
                        "reject_area_y" => $restore->defect_area_y,
                        "created_by" => $restore->created_by,
                        "created_at" => $restore->created_at,
                        "updated_at" => $restore->updated_at,
                    ]);
                }

                array_push($deleteUndoIds, $restore->undo_id);
            }

            if (count($rft) > 0) {
                Rft::insert($rft);
                // RftPacking::insert($rft);
            }

            if (count($defect) > 0) {
                Defect::insert($defect);
            }

            if (count($reject) > 0) {
                Reject::insert($reject);
            }

            // if (count($rework) > 0) {
            //     Rework::insert($rework);
            // }

            if (count($deleteUndoIds) > 0) {
                Undo::whereIn("id", $deleteUndoIds)->delete();
            }

            return array("rft" => $rft, "defect" => $defect, "reject" => $reject, "rework" => $rework);
        }
    }

}
