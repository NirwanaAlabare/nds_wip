<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputLostTime;
use App\Models\ScannedItem;
use App\Models\Part;
use App\Models\PartForm;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use DB;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    public function cutting(Request $request) {
        $additionalQuery = "";

        if ($request->ajax()) {
            if ($request->dateFrom) {
                $additionalQuery .= " and DATE(a.waktu_selesai) >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and DATE(a.waktu_selesai) <= '" . $request->dateTo . "' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.id_marker like '%" . $request->search["value"] . "%' OR
                        a.no_meja like '%" . $request->search["value"] . "%' OR
                        a.no_form like '%" . $request->search["value"] . "%' OR
                        a.tgl_form_cut like '%" . $request->search["value"] . "%' OR
                        b.act_costing_ws like '%" . $request->search["value"] . "%' OR
                        panel like '%" . $request->search["value"] . "%' OR
                        b.color like '%" . $request->search["value"] . "%' OR
                        a.status like '%" . $request->search["value"] . "%' OR
                        users.name like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $data_spreading = DB::select("
                SELECT
                    a.id,
                    a.no_meja,
                    a.id_marker,
                    a.no_form,
                    a.no_cut,
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    COALESCE(a.qty_ply, 0) qty_ply,
                    COALESCE(b.gelar_qty, 0) gelar_qty,
                    COALESCE(a.total_lembar, '0') total_lembar,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ', ') marker_details,
                    cutting_plan.tgl_plan,
                    cutting_plan.app
                FROM `form_cut_input` a
                left join cutting_plan on cutting_plan.no_form_cut_input = a.no_form
                left join users on users.id = a.no_meja
                left join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                where
                    a.id is not null and
                    a.status = 'SELESAI PENGERJAAN'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY
                    FIELD(a.tipe_form_cut, null, 'PILOT', 'NORMAL', 'MANUAL'),
                    FIELD(a.app, 'Y', 'N', null),
                    a.no_form desc,
                    a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view('manager.cutting.cutting', ['meja' => $meja, 'page' => 'dashboard-cutting', "subPage" => "manage-cutting"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function detailCutting($id) {
        $formCutInputData = FormCutInput::leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->where('form_cut_input.id', $id)->first();

        $actCostingData = DB::connection("mysql_sb")->table('act_costing')->selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->groupBy('act_costing.id')->where('act_costing.id', $formCutInputData->act_costing_id)->get();

        $markerDetailData = MarkerDetail::selectRaw("
                marker_input.kode kode_marker,
                marker_input_detail.size,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            where("marker_input.kode", $formCutInputData->kode)->
            where("marker_input.cancel", "N")->
            get();

        $lostTimeData = FormCutInputLostTime::where('form_cut_input_id', $id)->get();

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view("manager.cutting.detail-cutting", [
            'id' => $id,
            'meja' => $meja,
            'formCutInputData' => $formCutInputData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'lostTimeData' => $lostTimeData,
            'page' => 'dashboard-cutting',
            "subPage" => "manage-cutting"
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateCutting(Request $request) {
        $validatedRequest = $request->validate([
            "current_id" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "current_id_item" => "required",
            "current_group" => "required",
            "current_group_stocker" => "nullable",
            "current_roll" => "nullable",
            "current_qty" => "required",
            "current_qty_real" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_remark" => "required",
            "current_sambungan" => "required",
            "p_act" => "required"
        ]);

        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        $updateTimeRecordSummary = FormCutInputDetail::selectRaw("form_cut_input_detail.*")->
            leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->
            where('form_cut_input.no_form', $validatedRequest['no_form_cut_input'])->
            where('form_cut_input_detail.id', $validatedRequest['current_id'])->
            update([
                "id_roll" => $validatedRequest['current_id_roll'],
                "id_item" => $validatedRequest['current_id_item'],
                "group_roll" => $validatedRequest['current_group'],
                "lot" => $request["current_lot"],
                "roll" => $validatedRequest['current_roll'],
                "qty" => $itemQty,
                "unit" => $itemUnit,
                "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                "sambungan" => $validatedRequest['current_sambungan'],
                "est_amparan" => $validatedRequest['current_est_amparan'],
                "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                "kepala_kain" => $validatedRequest['current_kepala_kain'],
                "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                "reject" => $validatedRequest['current_reject'],
                "sisa_kain" => $validatedRequest['current_sisa_kain'],
                "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                "short_roll" => $validatedRequest['current_short_roll'],
                "piping" => $validatedRequest['current_piping'],
                "remark" => $validatedRequest['current_remark'],
            ]);

        if ($updateTimeRecordSummary) {
            $itemRemain = $validatedRequest['current_sisa_kain'];

            ScannedItem::where("id_roll", $validatedRequest['current_id_roll'])->update([
                "id_item" => $validatedRequest['current_id_item'],
                "lot" => $request['current_lot'],
                "roll" => $validatedRequest['current_roll'],
                "qty" => $itemRemain,
                "unit" => $itemUnit,
            ]);

            $formCutDetails = FormCutInputDetail::where("no_form_cut_input", $validatedRequest['no_form_cut_input'])->orderBy("id", "asc")->get();
            $currentGroup = "";
            $groupNumber = 0;
            foreach ($formCutDetails as $formCutDetail) {
                if ($currentGroup != $formCutDetail->group_roll) {
                    $currentGroup = $formCutDetail->group_roll;
                    $groupNumber += 1;
                }

                $formCutDetail->group_stocker = $groupNumber;
                $formCutDetail->save();
            }

            $updateFormCut = FormCutInput::where('no_form', $validatedRequest['no_form_cut_input'])->update([
                "no_meja" => $validatedRequest['no_meja']
            ]);

            return array(
                "status" => 200,
                "message" => "alright",
            );
        }
    }

    public function updateFinish(Request $request, $id) {
        $formCutInputData = FormCutInput::where("id", $id)->first();

        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "cons_act" => $request->consAct,
            "unit_cons_act" => $request->unitConsAct,
            "cons_act_nosr" => $request->consActNoSr,
            "unit_cons_act_nosr" => $request->unitConsActNoSr,
            "cons_ws_uprate" => $request->consWsUprate,
            "cons_marker_uprate" => $request->consMarkerUprate,
            "cons_ws_uprate_nosr" => $request->consWsUprateNoSr,
            "cons_marker_uprate_nosr" => $request->consMarkerUprateNoSr,
            "total_lembar" => $request->totalLembar,
            "operator" => $request->operator,
        ]);

        // store to part form
        $partData = Part::select('part.id')->
            where("act_costing_id", $formCutInputData->marker->act_costing_id)->
            where("act_costing_ws", $formCutInputData->marker->act_costing_ws)->
            where("panel", $formCutInputData->marker->panel)->
            where("buyer", $formCutInputData->marker->buyer)->
            where("style", $formCutInputData->marker->style)->
            first();

        if ($updateFormCutInput && $partData) {
            $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
            $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
            $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

            $addToPartForm = PartForm::create([
                "kode" => $kodePartForm,
                "part_id" => $partData->id,
                "form_id" => $formCutInputData->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            return array(
                "status" => 200,
                "message" => "alright",
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function destroySpreadingRoll($id) {
        $formCutDetail = FormCutInputDetail::find($id);

        if ($formCutDetail) {
            if ($formCutDetail->id_roll) {
                $formCutDetailRoll = ScannedItem::where("id_roll", $formCutDetail->id_roll)->first();

                if ($formCutDetailRoll) {
                    $formCutDetailRoll->qty += ($formCutDetail->qty - $formCutDetail->sisa_kain);
                    $formCutDetailRoll->save();
                }
            }

            if ($formCutDetail->delete()) {
                return array(
                    "status" => 200,
                    "message" => "alright"
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore"
        );
    }
}
