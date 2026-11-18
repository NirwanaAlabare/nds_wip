<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\Stocker\Stocker;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\LoadingLine;
use App\Models\Dc\LoadingLinePlan;
use App\Models\Dc\TrolleyStocker;
use App\Models\Stocker\YearSequence;
use App\Models\SignalBit\UserLine;
use App\Services\DcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
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

    public function runRekapDc(Request $request, DcService $dcService) {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from|before:today',
        ], [
            'date_from.required' => 'Tanggal awal harus diisi.',
            'date_to.required' => 'Tanggal akhir harus diisi.',
            'date_to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
            'date_to.before' => 'Tanggal akhir harus sebelum hari ini.',
        ]);

        set_time_limit(0);

        $result = $dcService->runRekap($request->date_to, $request->date_from);

        return array(
            'status' => $result['status'],
            'message' => $result['message'],
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

        // Check Closing
        $dataCheckClosing = DB::table("dc_in_input")
            ->selectRaw("DATE_FORMAT(tgl_trans, '%Y-%m-%d') as tanggal")
            ->where("id_qr_stocker", $request->id_qr_stocker)
            ->first();
        if(!$dataCheckClosing){
            $dataCheckClosing = DB::table("secondary_inhouse_input")
                ->selectRaw("tgl_trans as tanggal")
                ->where("id_qr_stocker", $request->id_qr_stocker)
                ->first();
        }
        if(!$dataCheckClosing){
            $dataCheckClosing = DB::table("secondary_in_input")
                ->selectRaw("tgl_trans as tanggal")
                ->where("id_qr_stocker", $request->id_qr_stocker)
                ->first();
        }
        if(!$dataCheckClosing){
            $dataCheckClosing = DB::table("loading_line")
                ->selectRaw("loading_line.tanggal_loading as tanggal")
                ->leftJoin("stocker_input", "stocker_input.id", "=", "loading_line.stocker_id")
                ->where("stocker_input.id_qr_stocker", $request->id_qr_stocker)
                ->first();
        }

        if (checkClosingDate($dataCheckClosing->tanggal)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing",
            );
        }

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

    public function updateDcIn(Request $request) {
        $idQrStockers = explode("\n", $request->stocker_ids);
        $idQrStockers = array_filter(array_map('trim', $idQrStockers));

        // Check Closing
        $dataCheckClosing = DB::table("dc_in_input")
            ->selectRaw("DATE_FORMAT(tgl_trans, '%Y-%m-%d') as tanggal")
            ->whereIn("id_qr_stocker", $idQrStockers)
            ->get();

        foreach ($dataCheckClosing as $data) {
            if (checkClosingDate($data->tanggal)) {
                return [
                    "status" => 400,
                    "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                    "additional" => "Closing"
                ];
            }
        }

        if ($idQrStockers && $request->tanggal) {
            $errors = [];
            $validStockers = [];

            foreach ($idQrStockers as $idQrStocker) {
                // Get Stocker
                $stocker = Stocker::where("id_qr_stocker", $idQrStocker)->first();

                if (!$stocker) {
                    $errors[] = $idQrStocker . " - Stocker tidak ditemukan";
                    continue;
                }

                // Check SecondaryInhouse
                // if (SecondaryInhouse::where("id_qr_stocker", $idQrStocker)->exists()) {
                //     $errors[] = $idQrStocker . " - Secondary Inhouse sudah ada";
                //     continue;
                // }

                // // Check SecondaryIn
                // if (SecondaryIn::where("id_qr_stocker", $idQrStocker)->exists()) {
                //     $errors[] = $idQrStocker . " - Secondary In sudah ada";
                //     continue;
                // }

                // // Check TrolleyStocker
                // if (TrolleyStocker::where("stocker_id", $stocker->id)->exists()) {
                //     $errors[] = $idQrStocker . " - Trolley Stocker sudah ada";
                //     continue;
                // }

                // // Check LoadingLine
                // if (LoadingLine::where("stocker_id", $stocker->id)->exists()) {
                //     $errors[] = $idQrStocker . " - Loading Line sudah ada";
                //     continue;
                // }

                $validStockers[] = $idQrStocker;
            }

            if (count($errors) > 0) {
                return array(
                    "status" => 400,
                    "message" => implode("<br>", $errors)
                );
            }

            // Get DC IN
            $dcInList = DCIn::whereIn("id_qr_stocker", $validStockers)->get();

            $message = "";
            foreach ($dcInList as $dcIn) {
                $dcIn->tgl_trans = $request->tanggal;
                $dcIn->save();
                $message .= "<br>".$dcIn->id_qr_stocker." diubah ke tanggal ".$request->tanggal;
            }

            return array(
                "status" => 200,
                "message" => $message
            );
        }

        return array(
            "status" => 400,
            "message" => "Request tidak sesuai"
        );
    }

    public function deleteDcIn(Request $request) {
        $idQrStockers = explode("\n", $request->stocker_ids);
        $idQrStockers = array_filter(array_map('trim', $idQrStockers));

        // Check Closing
        $dataCheckClosing = DB::table("dc_in_input")
            ->selectRaw("DATE_FORMAT(tgl_trans, '%Y-%m-%d') as tanggal")
            ->whereIn("id_qr_stocker", $idQrStockers)
            ->get();

        foreach ($dataCheckClosing as $data) {
            if (checkClosingDate($data->tanggal)) {
                return [
                    "status" => 400,
                    "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                    "additional" => "Closing"
                ];
            }
        }

        if ($idQrStockers) {
            $errors = [];
            $validStockers = [];

            foreach ($idQrStockers as $idQrStocker) {
                // Get Stocker
                $stocker = Stocker::where("id_qr_stocker", $idQrStocker)->first();

                // if (!$stocker) {
                //     $errors[] = $idQrStocker . " - Stocker tidak ditemukan";
                //     continue;
                // }

                // Check SecondaryInhouse
                if (SecondaryInhouse::where("id_qr_stocker", $idQrStocker)->exists()) {
                    $errors[] = $idQrStocker . " - Secondary Inhouse sudah ada";
                    continue;
                }

                // Check SecondaryIn
                if (SecondaryIn::where("id_qr_stocker", $idQrStocker)->exists()) {
                    $errors[] = $idQrStocker . " - Secondary In sudah ada";
                    continue;
                }

                if ($stocker) {
                    // Check TrolleyStocker
                    if (TrolleyStocker::where("stocker_id", $stocker->id)->exists()) {
                        $errors[] = $idQrStocker . " - Trolley Stocker sudah ada";
                        continue;
                    }

                    // Check LoadingLine
                    if (LoadingLine::where("stocker_id", $stocker->id)->exists()) {
                        $errors[] = $idQrStocker . " - Loading Line sudah ada";
                        continue;
                    }
                }

                $validStockers[] = $idQrStocker;
            }

            if (count($errors) > 0) {
                return array(
                    "status" => 400,
                    "message" => implode("<br>", $errors)
                );
            }

            // Get DC IN
            $dcInList = DCIn::whereIn("id_qr_stocker", $validStockers)->get();

            $message = "";
            foreach ($dcInList as $dcIn) {
                $dcIn->delete();
                $message .= "<br>".$dcIn->id_qr_stocker." berhasil dihapus.";
            }

            return array(
                "status" => 200,
                "message" => $message
            );
        }

        return array(
            "status" => 400,
            "message" => "Request tidak sesuai"
        );
    }

    public function checkOrphanTransaction()
    {
        return view('dc.tools.check-orphan-transaction', [
            'page' => 'dashboard-dc',
        ]);
    }

    public function checkOrphanTransactionList(Request $request)
    {
        $tableMap = [
            'dc'                   => 'dc_in_input',
            'secondary_inhouse_in' => 'secondary_inhouse_in_input',
            'secondary_inhouse'    => 'secondary_inhouse_input',
            'secondary_in'         => 'secondary_in_input',
        ];

        $qtyColumnMap = [
            'dc'                   => 't.qty_awal',
            'secondary_inhouse_in' => 't.qty_in',
            'secondary_inhouse'    => 't.qty_awal',
            'secondary_in'         => 't.qty_awal',
        ];

        $type = $request->transaction_type;

        if (!isset($tableMap[$type])) {
            return response()->json(['error' => 'Jenis transaksi tidak valid'], 400);
        }

        $table     = $tableMap[$type];
        $qtyColumn = $qtyColumnMap[$type];

        $query = DB::table($table . ' as t')
            ->leftJoin('stocker_input as s', 's.id_qr_stocker', '=', 't.id_qr_stocker')
            ->leftJoin('part_detail as pd', 'pd.id', '=', 's.part_detail_id')
            ->leftJoin('master_part as mp', 'mp.id', '=', 'pd.master_part_id')
            ->whereNull('s.id')
            ->whereNotNull('t.tgl_trans')
            ->selectRaw("
                t.id,
                t.id_qr_stocker,
                t.tgl_trans,
                t.created_at,
                {$qtyColumn} as qty,
                s.act_costing_ws,
                s.color,
                s.size,
                s.ratio,
                s.group_stocker,
                CONCAT(COALESCE(s.range_awal, '-'), ' - ', COALESCE(s.range_akhir, '-')) as stocker_range,
                s.panel,
                mp.nama_part as part
            ");

        if ($request->date_from) {
            $query->where('t.tgl_trans', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->date_to) {
            $query->where('t.tgl_trans', '<=', $request->date_to . ' 23:59:59');
        }

        $query->orderBy('t.tgl_trans', 'desc');

        return DataTables::queryBuilder($query)->make(true);
    }

    public function deleteOrphanTransaction(Request $request)
    {
        $tableMap = [
            'dc'                   => 'dc_in_input',
            'secondary_inhouse_in' => 'secondary_inhouse_in_input',
            'secondary_inhouse'    => 'secondary_inhouse_input',
            'secondary_in'         => 'secondary_in_input',
        ];

        $type = $request->transaction_type;

        if (!isset($tableMap[$type])) {
            return ['status' => 400, 'message' => 'Jenis transaksi tidak valid.'];
        }

        $table = $tableMap[$type];

        $query = DB::table($table . ' as t')
            ->leftJoin('stocker_input as s', 's.id_qr_stocker', '=', 't.id_qr_stocker')
            ->whereNull('s.id')
            ->whereNotNull('t.tgl_trans');

        if ($request->date_from) {
            $query->where('t.tgl_trans', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->date_to) {
            $query->where('t.tgl_trans', '<=', $request->date_to . ' 23:59:59');
        }

        $ids   = $query->pluck('t.id');
        $count = $ids->count();

        if ($count === 0) {
            return ['status' => 400, 'message' => 'Tidak ada data yang dihapus.'];
        }

        DB::table($table)->whereIn('id', $ids)->delete();

        return ['status' => 200, 'message' => $count . ' data orphan transaction berhasil dihapus.'];
    }

    // ================= STOCKER CHECKER =================
    // Alur disamakan dengan modul opname /stocker-checker

    public function stockerChecker()
    {
        return view('dc.tools.stocker-checker', [
            'page' => 'dashboard-dc',
        ]);
    }

    // BY Nomor Form -> daftar id_qr_stocker
    public function stockerCheckerByForm(Request $request)
    {
        $noForm = trim((string) $request->input('no_form', ''));

        if ($noForm === '') {
            return ['success' => true, 'data' => []];
        }

        $rows = DB::select("
            SELECT a.id_qr_stocker
            FROM stocker_input a
            LEFT JOIN form_cut_input b ON a.form_cut_id = b.id
            WHERE b.no_form = ?
        ", [$noForm]);

        return ['success' => true, 'data' => collect($rows)->pluck('id_qr_stocker')->values()];
    }

    // BY Stocker (QR ID), bisa banyak dipisah koma
    public function stockerCheckerData(Request $request)
    {
        $qrList = collect(explode(',', (string) $request->input('id_qr_stocker', '')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (count($qrList) === 0) {
            return ['success' => true, 'data' => []];
        }

        $placeholders = implode(',', array_fill(0, count($qrList), '?'));

        $rows = DB::select("
            SELECT
                COALESCE(dc_in_input.tgl_trans, '-') AS tgl_trans,
                stocker_input.id_qr_stocker,
                CASE
                    WHEN stocker_input.qty_ply_mod IS NOT NULL
                        THEN stocker_input.qty_ply_mod
                    ELSE stocker_input.qty_ply
                END AS qty_awal_stocker,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.buyer,
                COALESCE(dc_in_input.qty_awal, 0) AS qty_awal,
                COALESCE(dc_in_input.qty_reject, 0) AS qty_reject,
                COALESCE(dc_in_input.qty_replace, 0) AS qty_replace,
                COALESCE((dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace), 0) AS qty_in,
                COALESCE(GROUP_CONCAT(DISTINCT multi_master_secondary.tujuan SEPARATOR ', '), master_secondary.tujuan, dc_in_input.tujuan, '-') AS tujuan,
                COALESCE(dc_in_input.lokasi, '-') AS lokasi,
                secondary_inhouse_in_input.created_at AS sec_inhouse_in,
                secondary_inhouse_input.created_at AS sec_inhouse_out,
                secondary_in_input.created_at AS sec_in,
                CONCAT(COALESCE(trolley.nama_trolley,'-'), ' (', COALESCE(trolley_stocker.created_at, '-'), ')') AS trolley,
                CONCAT(COALESCE(loading_line.nama_line,'-'), ' (', COALESCE(loading_line.created_at, '-'), ')') AS line,
                COALESCE(form_cut_input.no_form, '-') AS no_form,
                form_cut_input.no_cut AS no_cut_form_cut,
                form_cut_input.waktu_selesai AS waktu_selesai_form_cut,
                stocker_input.panel AS stocker_panel,
                stocker_input.created_at AS stocker_created_at,
                secondary_inhouse_in_input.qty_in AS qty_sec_inhouse_in,
                secondary_inhouse_input.qty_awal AS qty_sec_inhouse_awal,
                secondary_inhouse_input.qty_reject AS qty_sec_inhouse_reject,
                secondary_inhouse_input.qty_replace AS qty_sec_inhouse_replace,
                secondary_inhouse_input.qty_in AS qty_sec_inhouse_out,
                secondary_in_input.qty_awal AS qty_sec_in_awal,
                secondary_in_input.qty_reject AS qty_sec_in_reject,
                secondary_in_input.qty_replace AS qty_sec_in_replace,
                secondary_in_input.qty_in AS qty_sec_in_out,
                (
                    SELECT GROUP_CONCAT(b.proses SEPARATOR ', ')
                    FROM part_detail_secondary a
                    LEFT JOIN master_secondary b ON a.master_secondary_id = b.id
                    WHERE a.part_detail_id = stocker_input.part_detail_id
                ) AS proses_secondary,
                CONCAT(
                    stocker_input.range_awal, ' - ', stocker_input.range_akhir,
                    (CASE
                        WHEN dc_in_input.qty_reject IS NOT NULL AND dc_in_input.qty_replace IS NOT NULL
                        THEN CONCAT(' (', (COALESCE(dc_in_input.qty_replace, 0) + COALESCE(secondary_in_input.qty_replace, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) - COALESCE(secondary_in_input.qty_reject, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0)), ') ')
                        ELSE ' (0)'
                    END)
                ) AS stocker_range,
                COALESCE(part_detail.tujuan, '-') AS part_tujuan,
                COALESCE(master_part.nama_part, '-') AS nama_part,
                COALESCE(master_secondary.proses, '-') AS nama_secondary,

                COALESCE(
                    CASE WHEN loading_line.id IS NOT NULL THEN 'LINE' END,
                    CASE WHEN trolley_stocker.id IS NOT NULL THEN 'TROLLEY' END,
                    CASE WHEN COALESCE(mx_sec_in.id_qr_stocker, secondary_in_input.id) IS NOT NULL
                        THEN CONCAT(COALESCE(mms_in.proses, mx_sec_in.proses, master_secondary.proses), ' (SECONDARY-IN)') END,
                    CASE WHEN COALESCE(mx_sec_inhouse.id_qr_stocker, secondary_inhouse_input.id) IS NOT NULL
                        THEN CONCAT(COALESCE(mms_inhouse.proses, mx_sec_inhouse.proses, master_secondary.proses), ' (SECONDARY-INHOUSE)') END,
                    CASE WHEN dc_in_input.id IS NOT NULL THEN 'DC IN' END,
                    'BELUM MASUK DC'
                ) AS proses_terakhir,

                COALESCE(
                    CASE WHEN loading_line.id IS NOT NULL
                        THEN CONCAT(DATE(loading_line.tanggal_loading), ' ', TIME(loading_line.created_at)) END,
                    CASE WHEN trolley_stocker.id IS NOT NULL
                        THEN trolley_stocker.created_at END,
                    CASE WHEN COALESCE(mx_sec_in.id_qr_stocker, secondary_in_input.id) IS NOT NULL
                        THEN CONCAT(DATE(secondary_in_input.tgl_trans), ' ', TIME(secondary_in_input.created_at)) END,
                    CASE WHEN COALESCE(mx_sec_inhouse.id_qr_stocker, secondary_inhouse_input.id) IS NOT NULL
                        THEN CONCAT(DATE(secondary_inhouse_input.tgl_trans), ' ', TIME(secondary_inhouse_input.created_at)) END,
                    CASE WHEN dc_in_input.id IS NOT NULL
                        THEN CONCAT(DATE(dc_in_input.tgl_trans), ' ', TIME(dc_in_input.created_at)) END,
                    NULL
                ) AS tanggal_proses_terakhir

            FROM stocker_input
            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
            LEFT JOIN secondary_inhouse_in_input ON secondary_inhouse_in_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN trolley_stocker ON trolley_stocker.stocker_id = stocker_input.id
            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
            LEFT JOIN loading_line ON loading_line.stocker_id = stocker_input.id

            LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
            LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
            LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
            LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = part_detail.id
            LEFT JOIN master_secondary multi_master_secondary ON multi_master_secondary.id = part_detail_secondary.master_secondary_id

            LEFT JOIN (
                SELECT
                    secondary_inhouse_input.id_qr_stocker,
                    MAX(secondary_inhouse_input.urutan) AS max_urutan,
                    GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') AS proses
                FROM secondary_inhouse_input
                LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                    AND part_detail_secondary.urutan = secondary_inhouse_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                GROUP BY secondary_inhouse_input.id_qr_stocker
            ) mx_sec_inhouse ON mx_sec_inhouse.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN part_detail_secondary pds_inhouse
                ON pds_inhouse.urutan = mx_sec_inhouse.max_urutan
                AND pds_inhouse.part_detail_id = stocker_input.part_detail_id
            LEFT JOIN master_secondary mms_inhouse ON mms_inhouse.id = pds_inhouse.master_secondary_id

            LEFT JOIN (
                SELECT
                    secondary_in_input.id_qr_stocker,
                    MAX(secondary_in_input.urutan) AS max_urutan,
                    GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') AS proses
                FROM secondary_in_input
                LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                    AND part_detail_secondary.urutan = secondary_in_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                GROUP BY secondary_in_input.id_qr_stocker
            ) mx_sec_in ON mx_sec_in.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN part_detail_secondary pds_in
                ON pds_in.urutan = mx_sec_in.max_urutan
                AND pds_in.part_detail_id = stocker_input.part_detail_id
            LEFT JOIN master_secondary mms_in ON mms_in.id = pds_in.master_secondary_id

            WHERE stocker_input.id_qr_stocker IN (" . $placeholders . ")
            GROUP BY stocker_input.id
            ORDER BY stocker_input.id_qr_stocker ASC
        ", $qrList);

        return ['success' => true, 'data' => $rows];
    }

    // BY Nomor WS
    public function stockerCheckerByWs(Request $request)
    {
        $ws = trim((string) $request->input('ws', ''));

        if ($ws === '') {
            return ['success' => true, 'data' => []];
        }

        // Step 1: daftar panel unik untuk WS ini
        $panelRows = DB::select("
            SELECT DISTINCT b.panel
            FROM form_cut_input a
            LEFT JOIN marker_input b ON a.marker_id = b.id
            WHERE b.act_costing_ws = ?
        ", [$ws]);

        if (count($panelRows) === 0) {
            return ['success' => true, 'data' => []];
        }

        // Step 2: panel utama berdasarkan prioritas (BODY, PANTS, SHELL)
        $selectedPanel = $panelRows[0]->panel;
        foreach (['BODY', 'PANTS', 'SHELL'] as $keyword) {
            $match = collect($panelRows)->first(fn ($row) => str_contains(strtoupper((string) $row->panel), $keyword));
            if ($match) {
                $selectedPanel = $match->panel;
                break;
            }
        }

        // Step 3: data stocker + logistik DC
        $rows = DB::select("
            SELECT
                COALESCE(dc_in_input.tgl_trans, '-') AS tgl_trans,
                stocker_input.id_qr_stocker,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.buyer,
                COALESCE(dc_in_input.qty_awal, 0) AS qty_awal,
                COALESCE(dc_in_input.qty_reject, 0) AS qty_reject,
                COALESCE(dc_in_input.qty_replace, 0) AS qty_replace,
                COALESCE((dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace), 0) AS qty_in,
                COALESCE(dc_in_input.tujuan, '-') AS tujuan,
                COALESCE(dc_in_input.lokasi, '-') AS lokasi,
                secondary_inhouse_in_input.created_at AS sec_inhouse_in,
                secondary_inhouse_input.created_at AS sec_inhouse_out,
                secondary_in_input.created_at AS sec_in,
                CONCAT(COALESCE(trolley.nama_trolley,'-'), ' (', COALESCE(trolley_stocker.created_at, '-'), ')') AS trolley,
                CONCAT(COALESCE(loading_line.nama_line,'-'), ' (', COALESCE(loading_line.created_at, '-'), ')') AS line,
                form_cut_input.no_form,
                form_cut_input.waktu_selesai AS waktu_selesai_form_cut,
                stocker_input.panel AS stocker_panel,
                stocker_input.created_at AS stocker_created_at,
                secondary_inhouse_in_input.qty_in AS qty_sec_inhouse_in,
                secondary_inhouse_input.qty_awal AS qty_sec_inhouse_awal,
                secondary_inhouse_input.qty_reject AS qty_sec_inhouse_reject,
                secondary_inhouse_input.qty_replace AS qty_sec_inhouse_replace,
                secondary_inhouse_input.qty_in AS qty_sec_inhouse_out,
                secondary_in_input.qty_awal AS qty_sec_in_awal,
                secondary_in_input.qty_reject AS qty_sec_in_reject,
                secondary_in_input.qty_replace AS qty_sec_in_replace,
                secondary_in_input.qty_in AS qty_sec_in_out,
                COALESCE(part_detail.tujuan, '-') AS part_tujuan,
                COALESCE(master_part.nama_part, '-') AS nama_part,
                COALESCE(master_secondary.proses, '-') AS nama_secondary
            FROM stocker_input
            LEFT JOIN form_cut_input ON stocker_input.form_cut_id = form_cut_input.id
            LEFT JOIN marker_input ON form_cut_input.marker_id = marker_input.id
            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
            LEFT JOIN secondary_inhouse_in_input ON secondary_inhouse_in_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
            LEFT JOIN trolley_stocker ON trolley_stocker.stocker_id = stocker_input.id
            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
            LEFT JOIN loading_line ON loading_line.stocker_id = stocker_input.id
            LEFT JOIN part_detail ON stocker_input.part_detail_id = part_detail.id
            LEFT JOIN master_part ON part_detail.master_part_id = master_part.id
            LEFT JOIN master_secondary ON part_detail.master_secondary_id = master_secondary.id
            WHERE stocker_input.act_costing_ws = ?
            ORDER BY stocker_input.id_qr_stocker ASC
        ", [$ws]);

        return ['success' => true, 'data' => $rows, 'panelUsed' => $selectedPanel];
    }
}
