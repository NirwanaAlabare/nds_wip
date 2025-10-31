<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\Stocker\Stocker;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\LoadingLine;
use App\Models\Dc\LoadingLinePlan;
use App\Models\Stocker\YearSequence;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

        $emptyOrder = LoadingLine::selectRaw("GROUP_CONCAT(loading_line.id) as loading_line_ids, loading_line.tanggal_loading, loading_line.line_id, master_sb_ws.buyer, master_sb_ws.id_act_cost as act_costing_id, stocker_input.act_costing_ws, master_sb_ws.styleno as style, stocker_input.color, MIN(loading_line_plan.id) as loading_plan_id")->
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
            $loadingLinePlan = LoadingLinePlan::where("act_costing_id", $eo->act_costing_id)->where("color", $eo->color)->where("line_id", $eo->line_id)->where("tanggal", $eo->tanggal_loading)->first();

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
            } else if ($eo->loading_plan_id) {
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
            } else {
                $currentLoadingLinePlan = LoadingLinePlan::where("act_costing_id", $eo->act_costing_id)->
                    where("act_costing_ws", $eo->act_costing_ws)->
                    where("style", $eo->style)->
                    where("color", $eo->color)->
                    where("tanggal", $eo->tanggal_loading)->
                    first();

                if ($currentLoadingLinePlan) {
                    $updateLoadingLine = LoadingLine::whereRaw("id in (".$eo->loading_line_ids.")")->update([
                        "loading_plan_id" => $currentLoadingLinePlan->id
                    ]);

                    if ($updateLoadingLine) {
                        array_push($success, $eo->loading_line_ids);
                    } else {
                        array_push($fails, $eo->loading_line_ids);
                    }
                } else {
                    $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                    $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                    $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                    $loadingLinePlan = LoadingLinePlan::create([
                        "kode" => $kodeLoadingPlan,
                        "tanggal" => $eo->tanggal_loading,
                        "line_id" => $eo->line_id,
                        "buyer" => $eo->buyer,
                        "act_costing_id" => $eo->act_costing_id,
                        "act_costing_ws" => $eo->act_costing_ws,
                        "style" => $eo->style,
                        "color" => $eo->color,
                    ]);

                    if ($loadingLinePlan) {
                        $updateLoadingLine = LoadingLine::whereRaw("id in (".$eo->loading_line_ids.")")->update([
                            "loading_plan_id" => $loadingLinePlan->id
                        ]);

                        if ($updateLoadingLine) {
                            array_push($success, $eo->loading_line_ids);
                        } else {
                            array_push($fails, $eo->loading_line_ids);
                        }
                    }
                }
            }
        }

        return array(
            'status' => 200,
            'message' => 'Berhasil mengubah '.count($success).' data <br> Gagal mengubah '.count($fails).' data',
        );
    }

    public function redundantLoadingPlan(Request $request) {
        $year = $request->input('year');
        $month = $request->input('month');
        $date = $request->input('date');

        $redundantPlan = collect(DB::select("select MAX(id) as id, act_costing_id, act_costing_ws, color, line_id, tanggal from loading_line_plan group by act_costing_id, color, line_id, tanggal having count(id) > 1"));

        if ($redundantPlan->count() < 1) {
            return array(
                'status' => 200,
                'message' => 'Tidak ada data yang perlu diubah',
            );
        }

        $success = [];
        $fails = [];
        foreach ($redundantPlan as $rp) {
            // Similar Loading Plan
            $loadingLinePlan = LoadingLinePlan::where("id", "!=", $rp->id)->where("act_costing_id", $rp->act_costing_id)->where("color", $rp->color)->where("line_id", $rp->line_id)->where("tanggal", $rp->tanggal)->get();

            foreach ($loadingLinePlan as $lp) {
                // Update Similar Loading Plan to current ID
                LoadingLine::where("loading_plan_id",  $lp->id)->update([
                    "loading_plan_id" => $rp->id
                ]);

                // Delete Loading Plan
                LoadingLinePlan::where("id", $lp->id)->delete();

                array_push($success, $lp);
            }
        }

        return array(
            'status' => 200,
            'message' => 'Berhasil mengubah '.count($success).' data <br> Gagal mengubah '.count($fails).' data',
        );
    }

    public function modifyDcQty(Request $request) {
        $lines = UserLine::where('Groupp', 'SEWING')->whereRaw('(Locked != 1 || Locked is NULL)')->orderBy('line_id', 'asc')->get();

        return view("dc.tools.modify-dc-qty", ['page' => 'dashboard-dc', "lines" => $lines]);
    }

    public function getDcQty(Request $request) {
        if ($request->id_qr_stocker) {
            $stocker = DB::select("
                select
                    stocker_input.id,
                    stocker_input.id_qr_stocker,
                    master_sb_ws.ws,
                    master_sb_ws.styleno,
                    master_sb_ws.color,
                    master_sb_ws.size,
                    dc_in_input.qty_awal dc_qty_awal,
                    dc_in_input.qty_reject dc_qty_reject,
                    dc_in_input.qty_replace dc_qty_replace,
                    secondary_inhouse_input.qty_awal inhouse_qty_awal,
                    secondary_inhouse_input.qty_reject inhouse_qty_reject,
                    secondary_inhouse_input.qty_replace inhouse_qty_replace,
                    secondary_inhouse_input.qty_in inhouse_qty_in,
                    secondary_in_input.qty_awal in_qty_awal,
                    secondary_in_input.qty_reject in_qty_reject,
                    secondary_in_input.qty_replace in_qty_replace,
                    secondary_in_input.qty_in in_qty_in,
                    loading_line.line_id,
                    loading_line.nama_line as line_name,
                    loading_line.tanggal_loading as line_tanggal,
                    loading_line.qty as line_qty
                from
                    stocker_input
                    left join dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    left join secondary_inhouse_input on secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                    left join secondary_in_input on secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    left join loading_line on loading_line.stocker_id = stocker_input.id
                    left join master_sb_ws on master_sb_ws.id_so_det = stocker_input.so_det_id
                where
                    stocker_input.id_qr_stocker = '".$request->id_qr_stocker."'
            ");

            if ($stocker) {
                return array(
                    "status" => 200,
                    "message" => "Data ditemukan",
                    "data" => $stocker[0]
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Data tidak ditemukan",
        );
    }

    public function updateDcQty(Request $request) {
        if ($request->id_qr_stocker) {
            $stocker = Stocker::selectRaw("stocker_input.*, master_sb_ws.buyer master_act_costing_buyer, master_sb_ws.id_act_cost master_act_costing_id, master_sb_ws.ws master_act_costing_ws, master_sb_ws.styleno master_act_costing_style, master_sb_ws.color master_act_costing_color")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->where("id_qr_stocker", $request->id_qr_stocker)->first();

            if ($stocker) {
                // DC in
                $dc = DcIn::where("id_qr_stocker", $request->id_qr_stocker)->first();
                if ($dc) {
                    $dc->qty_awal = $request->dc_qty_awal != null ? $request->dc_qty_awal : $dc->qty_awal;
                    $dc->qty_reject = $request->dc_qty_reject != null ? $request->dc_qty_reject : $dc->qty_reject;
                    $dc->qty_replace = $request->dc_qty_replace != null ? $request->dc_qty_replace : $dc->qty_replace;
                    $dc->save();
                }

                // Sec inhouse
                $secondaryInhouse = SecondaryInhouse::where("id_qr_stocker", $request->id_qr_stocker)->first();
                if ($secondaryInhouse) {
                    $secondaryInhouse->qty_awal = $request->inhouse_qty_awal != null ? $request->inhouse_qty_awal : $secondaryInhouse->qty_awal;
                    $secondaryInhouse->qty_reject = $request->inhouse_qty_reject != null ? $request->inhouse_qty_reject : $secondaryInhouse->qty_reject;
                    $secondaryInhouse->qty_replace = $request->inhouse_qty_replace != null ? $request->inhouse_qty_replace : $secondaryInhouse->qty_replace;
                    $secondaryInhouse->qty_in = $request->inhouse_qty_in != null ? $request->inhouse_qty_in : $secondaryInhouse->qty_in;
                    $secondaryInhouse->save();
                }

                // Sec in
                $secondaryIn = SecondaryIn::where("id_qr_stocker", $request->id_qr_stocker)->first();
                if ($secondaryIn) {
                    $secondaryIn->qty_awal = $request->in_qty_awal != null ? $request->in_qty_awal : $secondaryIn->qty_awal;
                    $secondaryIn->qty_reject = $request->in_qty_reject != null ? $request->in_qty_reject : $secondaryIn->qty_reject;
                    $secondaryIn->qty_replace = $request->in_qty_replace != null ? $request->in_qty_replace : $secondaryIn->qty_replace;
                    $secondaryIn->qty_in = $request->in_qty_in != null ? $request->in_qty_in : $secondaryIn->qty_in;
                    $secondaryIn->save();
                }

                $loadingLine = LoadingLine::where("stocker_id", $stocker->id)->first();
                if ($loadingLine) {
                    $loadingLinePlan = LoadingLinePlan::where("line_id", $request->line_id)->where("act_costing_id", $stocker->master_act_costing_id)->where("color", $stocker->master_act_costing_color)->where("tanggal", $request->line_tanggal)->first();

                    if (!$loadingLinePlan) {
                        $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                        $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                        $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                        $loadingLinePlan = LoadingLinePlan::create([
                            "kode" => $kodeLoadingPlan,
                            "line_id" => $request->line_id,
                            "buyer" => $stocker->master_act_costing_buyer,
                            "act_costing_id" => $stocker->master_act_costing_id,
                            "act_costing_ws" => $stocker->master_act_costing_ws,
                            "style" => $stocker->master_act_costing_style,
                            "color" => $stocker->master_act_costing_color,
                            "tanggal" => $request->line_tanggal
                        ]);
                    }

                    if ($loadingLinePlan) {
                        $similarStocker = Stocker::selectRaw("stocker_input.*, master_secondary.tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id")->
                            where(($stocker->form_reject_id > 0 ? "form_reject_id" : "form_cut_id"), ($stocker->form_reject_id > 0 ? $stocker->form_reject_id : $stocker->form_cut_id))->
                            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                            leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                            leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                            where("so_det_id", $stocker->so_det_id)->
                            where("group_stocker", $stocker->group_stocker)->
                            where("ratio", $stocker->ratio)->
                            get();

                        $stockerIds = $similarStocker->pluck("id")->toArray();

                        $loadingLinesUpdate = LoadingLine::whereIn("stocker_id", $stockerIds)->update([
                            "loading_plan_id" => $loadingLinePlan->id,
                            "line_id" => $request->line_id,
                            "nama_line" => $request->line_name,
                            "tanggal_loading" => $request->line_tanggal,
                            "qty" => $request->line_qty,
                        ]);
                    }
                }

                return array(
                    "dc" => $dc,
                    "secondaryInhouse" => $secondaryInhouse,
                    "secondaryIn" => $secondaryIn,
                    "loadingLine" => $loadingLine
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Data tidak valid"
        );
    }
}
