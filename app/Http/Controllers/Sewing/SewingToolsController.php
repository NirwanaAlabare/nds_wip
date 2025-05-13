<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\ReworkPacking;
use App\Models\SignalBit\RftPacking;
use App\Models\YearSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class SewingToolsController extends Controller
{
    public function index() {
        return view('sewing.tools.tools', [
            "page" => "dashboard-sewing-eff"
        ]);
    }

    public function missUser() {
        ini_set("max_execution_time", 3600);

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
                output_rfts.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y-m-d")." 23:59:59'
                AND (userpassword.username != master_plan.sewing_line)
            GROUP BY
                output_rfts.id
        "));

        if ($masterUser->count() < 1) {
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
        foreach ($masterUser as $mu) {
            if ($mu->actual_plan_id) {
                $updateRft = Rft::where("id", $mu->id)->update([
                    "master_plan_id" => $mu->actual_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mu->id, "change output master plan"]);
                } else {
                    array_push($fails, [$mu->id, "change output master plan"]);
                }
            } else if ($mu->plan_user_id) {
                $updateRft = Rft::where("id", $mu->id)->update([
                    "created_by" => $mu->plan_user_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mu->id, "change output user"]);
                } else {
                    array_push($fails, [$mu->id, "change output user"]);
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
                output.size
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
                    output_rfts.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y-m-d")." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan = output.tgl_plan
            WHERE
                actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        if ($masterPlan->count() < 1) {
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
        foreach ($masterPlan as $mp) {
            if ($mp->act_plan_id) {
                $updateRft = Rft::where("id", $mp->id)->update([
                    "master_plan_id" => $mp->act_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mp, "change output master plan"]);
                } else {
                    array_push($fails, [$mp, "change output master plan"]);
                }
            } else {
                $soDet = SoDet::select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->plan_act_costing_id)->where("so_det.color", $mp->plan_color)->where("so_det.size", $mp->size)->first();

                if ($soDet) {
                    $rft = Rft::where("id", $mp->id)->first();
                    $rft->so_det_id = $soDet->id;
                    $rft->save();

                    if ($rft) {
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
}
