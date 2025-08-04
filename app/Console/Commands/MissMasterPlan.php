<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\RftPacking;
use DB;

class MissMasterPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:missmasterplan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Missing Master Plan Output';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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
                LEFT JOIN master_plan actual on actual.id_ws = act_costing.id and actual.sewing_line = userpassword.username and actual.tgl_plan <= master_plan.tgl_plan
            WHERE
                output_rfts.updated_at between '".date("Y-m-d H:i:s", strtotime(date("Y-m-d").' -1 month'))."' and '".date("Y-m-d H:i:s")."'
                AND act_costing_plan.id != act_costing.id
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
                    array_push($success, $mp->id);
                } else {
                    array_push($fails, $mp->id);
                }
            } else {
                $soDet = SoDet::select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->act_costing_plan_id)->where("so_det.color", $mp->master_plan_color)->where("so_det.size", $mp->size)->first();

                if ($soDet) {
                    $rft = Rft::where("id", $mp->id)->first();
                    $rft->so_det_id = $soDet->id;
                    $rft->save();

                    if ($rft) {
                        $yearSequence = YearSequence::where("id_year_sequence", $rft->kode_numbering)->update(["so_det_id" => $rft->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, $mp->id);
                        }
                    } else {
                        array_push($fails, $mp->id);
                    }
                } else {
                    array_push($unavailable, $mp->id);
                }
            }
        }

        $masterPlanPacking = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_rfts_packing.id,
                master_plan.id master_plan_id,
                master_plan.color master_plan_color,
                act_costing_plan.id act_costing_plan_id,
                actual.id actual_master_plan_id,
                so_det.color actual_color,
                act_costing.id actual_act_costing_id,
                output_rfts_packing.updated_at,
                so_det.size
            FROM
                output_rfts_packing
                LEFT JOIN userpassword on userpassword.username = output_rfts_packing.created_by
                LEFT JOIN so_det ON so_det.id = output_rfts_packing.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN master_plan on master_plan.id = output_rfts_packing.master_plan_id
                LEFT JOIN act_costing act_costing_plan on act_costing_plan.id = master_plan.id_ws
                LEFT JOIN master_plan actual on actual.id_ws = act_costing.id and actual.sewing_line = userpassword.username and actual.tgl_plan <= master_plan.tgl_plan
            WHERE
                output_rfts_packing.updated_at between '".date("Y-m-d H:i:s", strtotime(date("Y-m-d").' -1 month'))."' and '".date("Y-m-d H:i:s")."'
                AND act_costing_plan.id != act_costing.id
        "));

        foreach ($masterPlanPacking as $mp) {
            if ($mp->actual_master_plan_id) {
                $updateRft = RftPacking::where("id", $mp->id)->update([
                    "master_plan_id" => $mp->actual_master_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, $mp->id);
                } else {
                    array_push($fails, $mp->id);
                }
            } else {
                $soDet = SoDet::select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->act_costing_plan_id)->where("so_det.color", $mp->master_plan_color)->where("so_det.size", $mp->size)->first();

                if ($soDet) {
                    $rft = RftPacking::where("id", $mp->id)->first();
                    $rft->so_det_id = $soDet->id;
                    $rft->save();

                    if ($rft) {
                        $yearSequence = YearSequence::where("id_year_sequence", $rft->kode_numbering)->update(["so_det_id" => $rft->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, $mp->id);
                        }
                    } else {
                        array_push($fails, $mp->id);
                    }
                } else {
                    array_push($unavailable, $mp->id);
                }
            }
        }

        return array(
            'status' => 200,
            'message' => 'Berhasil mengubah '.count($success).' data <br> Tidak dapat menemukan master plan '.count($unavailable).' data <br> Gagal mengubah '.count($fails).' data',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }
}
