<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\FormCut;
use App\Models\YearSequence;
use App\Models\Stocker;
use App\Models\DCIn;
use App\Models\SecondaryIn;
use App\Models\SecondaryInHouse;
use App\Models\RackDetailStocker;
use App\Models\TrolleyStocker;
use App\Models\LoadingLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PDF;
use DB;

class StockerToolsController extends Controller
{
    public function index() {
        return view('stocker.tools.tools', [
            "page" => "dashboard-stocker"
        ]);
    }

    public function resetStockerForm(Request $request) {
        ini_set('max_execution_time', 3600);

        $validatedRequest = $request->validate([
            "form_cut_id" => "required",
            "no_form" => "required",
        ]);

        if ($request->form_group) {
            if ($request->form_stocker && count(explode(",", $request->form_stocker)) > 0) {
                $checkYearSequence = YearSequence::leftJoin("stocker_input", function($join) {
                    $join->on("stocker_input.form_cut_id", "=", "year_sequence.form_cut_id");
                    $join->on("stocker_input.so_det_id", "=", "year_sequence.so_det_id");
                    $join->on("stocker_input.range_awal", "<=", "year_sequence.number");
                    $join->on("stocker_input.range_akhir", ">=", "year_sequence.number");
                })->
                where('year_sequence.form_cut_id', $validatedRequest['form_cut_id'])->
                where('stocker_input.group_stocker', $request->form_group)->
                whereIn('stocker_input.id', explode(",", $request->form_stocker))->
                count();
            } else {
                $checkYearSequence = YearSequence::leftJoin("stocker_input", function($join) {
                    $join->on("stocker_input.form_cut_id", "=", "year_sequence.form_cut_id");
                    $join->on("stocker_input.so_det_id", "=", "year_sequence.so_det_id");
                    $join->on("stocker_input.range_awal", "<=", "year_sequence.number");
                    $join->on("stocker_input.range_akhir", ">=", "year_sequence.number");
                })->
                where('year_sequence.form_cut_id', $validatedRequest['form_cut_id'])->
                where('stocker_input.group_stocker', $request->form_group)->
                count();
            }
        } else {
            $checkYearSequence = YearSequence::where('form_cut_id', $validatedRequest['form_cut_id'])->count();
        }

        if ($checkYearSequence > 0) {
            return array(
                'status' => 400,
                'message' => 'Stocker Form <br> "'.$validatedRequest['no_form'].'" <br> memiliki data year sequence (label).',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        if ($validatedRequest) {
            // Delete related stocker input
            if ($request->form_group) {
                if ($request->form_stocker && count(explode(",", $request->form_stocker)) > 0) {
                    $stockers = Stocker::where('form_cut_id', $validatedRequest['form_cut_id'])->where('group_stocker', $request->form_group)->whereIn('id', explode(",", $request->form_stocker))->get();
                } else {
                    $stockers = Stocker::where('form_cut_id', $validatedRequest['form_cut_id'])->where('group_stocker', $request->form_group)->get();
                }
            } else {
                $stockers = Stocker::where('form_cut_id', $validatedRequest['form_cut_id'])->get();
            }
            $stockerIdQrs = $stockers->pluck('id_qr_stocker')->toArray();
            $stockerIds = $stockers->pluck('id')->toArray();

            // Log the deletion
            Log::channel('resetStockerForm')->info([
                "Deleting Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                DB::table("stocker_input")->whereIn('id', $stockerIds)->get(),
                DB::table("dc_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("rack_detail_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("trolley_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("loading_line")->whereIn('stocker_id', $stockerIds)->get()
            ]);

            if (!($request->form_stocker && count(explode(",", $request->form_stocker)) > 0)) {
                $deleteStocker = Stocker::whereIn('id', $stockerIds)->delete();
            }
            $deleteDc = DCIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryIn = SecondaryIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryInHouse = SecondaryInHouse::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteRackDetailStocker = RackDetailStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteTrolleyStocker = TrolleyStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteLoadingLine = LoadingLine::whereIn('stocker_id', $stockerIds)->delete();

            return array(
                'status' => 200,
                'message' => 'Stocker Form <br> "'.$validatedRequest['no_form'].'" <br> berhasil direset.',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker Form <br> "'.$validatedRequest['no_form'].'" <br> gagal direset.',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function resetRedundantStocker(Request $request) {
        ini_set('max_execution_time', 3600);

        $redundantStockers = collect(DB::select("
            select stocker_input.* from stocker_input where id_qr_stocker in (
                select id_qr_stocker from stocker_input group by id_qr_stocker having count(id) > 1
            ) order by CAST(SUBSTRING_INDEX(id_qr_stocker, '-', -1) AS UNSIGNED) asc, updated_at desc
        "));

        $currentStocker = "";
        $updatedStockerIds = [];
        foreach ($redundantStockers as $redundantStocker) {
            if ($currentStocker != $redundantStocker->id_qr_stocker) {
                $currentStocker = $redundantStocker->id_qr_stocker;
            } else {
                // Update Stocker
                $stockerCount = Stocker::lastId();

                $newIdQrStocker = "STK-".($stockerCount+1);

                // update stocker
                Stocker::where("id", $redundantStocker->id)->update([
                    "id_qr_stocker" => $newIdQrStocker,
                    "id_qr_stocker_old" => $redundantStocker->id_qr_stocker,
                ]);

                // // update dc
                // DCIn::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->
                //     update([
                //         "id_qr_stocker" => $newIdQrStocker,
                //     ]);

                // // update secondary inhouse
                // SecondaryInhouse::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->
                //     update([
                //         "id_qr_stocker" => $newIdQrStocker,
                //     ]);

                // // update secondary in
                // SecondaryIn::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->
                //     update([
                //         "id_qr_stocker" => $newIdQrStocker,
                //     ]);

                array_push($updatedStockerIds, $redundantStocker->id);
            }
        }

        $dataStockers = Stocker::selectRaw("
            (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
            COALESCE(master_sb_ws.size, stocker_input.size) size,
            stocker_input.range_awal,
            stocker_input.range_akhir,
            stocker_input.id_qr_stocker,
            stocker_input.id_qr_stocker_old,
            marker_input.act_costing_ws,
            marker_input.buyer,
            marker_input.style,
            marker_input.color,
            stocker_input.shade,
            stocker_input.group_stocker,
            COALESCE(stocker_input.notes) notes,
            form_cut_input.no_cut,
            master_part.nama_part part,
            master_sb_ws.dest
        ")->
        leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
        leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
        leftJoin("part", "part.id", "=", "part_detail.part_id")->
        leftJoin("part_form", "part_form.part_id", "=", "part.id")->
        leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
        leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
        leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
        leftJoin("master_size_new", "master_size_new.size", "=", "stocker_input.size")->
        leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
        leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
        whereIn("stocker_input.id", $updatedStockerIds)->
        groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
        orderBy("stocker_input.group_stocker", "desc")->
        orderBy("stocker_input.so_det_id", "asc")->
        orderBy("stocker_input.ratio", "asc")->
        get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $customPaper = array(0, 0, 300, 250);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker-redundant', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

        $fileName = 'STOCKER_REDUNDANT.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }
}
