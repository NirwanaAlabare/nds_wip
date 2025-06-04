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
}
