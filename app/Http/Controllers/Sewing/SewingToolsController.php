<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Rft;
use App\Models\YearSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;

class SewingToolsController extends Controller
{
    public function index() {
        return view('sewing.tools.tools', [
            "page" => "dashboard-sewing-eff"
        ]);
    }

    public function missUser() {
        $masterUser = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_rfts.id,
                userpassword.line_id as actual_line_id,
                userpassword.username as sewing_line,
                master_plan.sewing_line as plan_sewing_line
                plan_line.line_id as plan_line_id
            FROM
                output_rfts
                LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                LEFT JOIN userpassword as plan_line on plan_line.line_id = userpassword.line_id
            WHERE
                output_rfts.updated_at between '".date("Y-m-d H:i:s", strtotime(date("Y-m-d").' -1 month'))."' and '".date("Y-m-d H:i:s")."'
                AND (userpassword.username != master_plan.sewing_line)
        "));

        $success = [];
        $fails = [];
        $unavailable = [];
        foreach ($masterUser as $mu) {
            if ($mu->sewing_line && $mu->plan_sewing_line) {
                $updateRft = Rft::where("id", $mu->id)->update([
                    "created_by" => $mu->plan_line_id
                ]);

                if ($updateRft) {
                    array_push($success, $mu->id);
                } else {
                    array_push($fails, $mu->id);
                }
            }
        }

        Log::channel('missUserOutput')->info([
            "Repair User Output Based on Master Plan",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Total Data ".count($success),
            "Success" => $success,
            "Fails" => $fails,
            "Unavailable" => $unavailable
        ]);

        return array(
            'status' => 200,
            'message' => 'Berhasil mengubah '.count($success).' data <br> Tidak dapat menemukan master plan '.count($unavailable).' data <br> Gagal mengubah '.count($fails).' data',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function missMasterPlan() {
        $masterPlan = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_rfts.id,
                master_plan.id master_plan_id,
                master_plan.color master_plan_color,
                act_costing_plan.id act_costing_plan_id,
                actual.id actual_master_plan_id,
                so_det.color actual_color,
                act_costing.id actual_act_costing_id,
                output_rfts.updated_at,
                so_det.size
            FROM
                output_rfts
                LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                LEFT JOIN act_costing act_costing_plan on act_costing_plan.id = master_plan.id_ws
                LEFT JOIN master_plan actual on actual.id_ws = act_costing.id and actual.color = so_det.color and actual.sewing_line = userpassword.username and actual.tgl_plan = master_plan.tgl_plan
            WHERE
                output_rfts.updated_at between '".date("Y-m-d H:i:s", strtotime(date("Y-m-d").' -1 month'))."' and '".date("Y-m-d H:i:s")."'
                AND (act_costing_plan.id != act_costing.id OR actual.color != so_det.color)
        "));

        $success = [];
        $fails = [];
        $unavailable = [];
        foreach ($masterPlan as $mp) {
            if ($mp->actual_master_plan_id) {
                $updateRft = Rft::where("id", $mp->id)->update([
                    "master_plan_id" => $mp->actual_master_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mp, "change output master plan"]);
                } else {
                    array_push($fails, [$mp, "change output master plan"]);
                }
            } else {
                if ($mp->actual_act_costing_id) {
                    $updateRft = Rft::where("id", $mp->id)->update([
                        "master_plan_id" => $mp->master_plan_id
                    ]);
                } 
                $soDet = SoDet::select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->act_costing_plan_id)->where("so_det.color", $mp->master_plan_color)->where("so_det.size", $mp->size)->first();

                if ($soDet) {
                    $rft = Rft::where("id", $mp->id)->first();
                    $rft->so_det_id = $soDet->id;
                    $rft->created_by = $soDet->id;
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
            'message' => 'Berhasil mengubah '.count($success).' data <br> Tidak dapat menemukan master plan '.count($unavailable).' data <br> Gagal mengubah '.count($fails).' data',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function missRework() {
        // Get Defects with Missing Rework
        $defects = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.id as defect_id, 'NORMAL' as status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects left join output_reworks on output_reworks.defect_id = output_defects.id where output_reworks.id is null and defect_status = 'reworked'"));

        $defectArr = $defects->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRework = Rework::insert($defectArr);

        // Get Reworks Data with Of Course Missing RFT
        $reworks = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'REWORK' as status, output_reworks.id as rework_id, output_defects.created_by, output_reworks.created_at, output_reworks.updated_at, output_defects.kode_numbering, output_defects.kode_numbering no_cut_size from output_reworks left join output_defects on output_defects.id = output_reworks.defect_id left join output_rfts on output_rfts.rework_id = output_reworks.id where output_rfts.id is null"));

        if ($reworks->count() > 0) {
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

        if ($storeToRework && $storeToRft) {
            Log::channel('missReworkOutput')->info([
                "Repair Defect->Rework->RFT Chain Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                "Total Data ".count($defects),
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
