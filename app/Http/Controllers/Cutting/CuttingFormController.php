<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailLap;
use App\Models\Cutting\FormCutInputDetailSambungan;
use App\Models\Cutting\FormCutInputLostTime;
use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\FormCutPieceDetailSizes;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\CutPlan;
use App\Models\Part\Part;
use App\Models\Part\PartForm;
use App\Models\Auth\User;
use App\Services\CuttingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use DB;

class CuttingFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            $dateFrom = $request->dateFrom ?? date("Y-m-d");
            $dateTo = $request->dateTo ?? date("Y-m-d");

            if ($dateFrom) {
                $additionalQuery .= " and (cutting_plan.tgl_plan >= '" . $dateFrom . "' or a.updated_at >= '". $dateFrom ."')";
            }

            if ($dateTo) {
                $additionalQuery .= " and (cutting_plan.tgl_plan <= '" . $dateTo . "' or a.updated_at <= '". $dateTo ."')";
            }

            if (Auth::user()->type == "meja") {
                $additionalQuery .= " and a.no_meja = '" . Auth::user()->id . "' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.id_marker like '%" . $request->search["value"] . "%' OR
                        a.no_meja like '%" . $request->search["value"] . "%' OR
                        a.no_form like '%" . $request->search["value"] . "%' OR
                        COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai), a.tgl_form_cut) like '%" . $request->search["value"] . "%' OR
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
                    COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai), a.tgl_form_cut) tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color color,
                    a.status,
                    UPPER(users.name) nama_meja,
                    b.panjang_marker panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a2.total_lembar, a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    COALESCE(a.qty_ply, 0) qty_ply,
                    COALESCE(b.gelar_qty, 0) gelar_qty,
                    COALESCE(a2.total_lembar, a.total_lembar, '0') total_lembar,
                    b.po_marker po_marker,
                    b.urutan_marker urutan_marker,
                    b.cons_marker cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    cutting_plan.app,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(COALESCE(master_size_new.size, master_sb_ws.size, marker_input_detail.size), '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ' / ') marker_details,
                    a.created_by_username,
                    a.created_at,
                    a.updated_at,
                    user_app.username as app_by
                FROM cutting_plan
                left join form_cut_input a on a.id = cutting_plan.form_cut_id
                left join (select form_cut_input_detail.form_cut_id, SUM(form_cut_input_detail.lembar_gelaran) total_lembar from form_cut_input_detail group by form_cut_input_detail.form_cut_id) a2 on a2.form_cut_id = a.id
                left outer join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left outer join marker_input_detail on b.id = marker_input_detail.marker_id and marker_input_detail.ratio > 0
                left join master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                left join master_size_new on master_size_new.size = master_sb_ws.size
                left join users on users.id = a.no_meja
                left join users as user_app on user_app.id = cutting_plan.app_by
                where
                    a.id is not null
                    AND a.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH)
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY
                    FIELD(a.status, 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'),
                    FIELD(a.tipe_form_cut, null, 'NORMAL', 'MANUAL'),
                    FIELD(cutting_plan.app, 'Y', 'N', null),
                    a.no_form desc,
                    a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        return view('cutting.cutting-form.cutting-form', ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "form-cut-input"]);
    }

    public function getRatio(Request $request)
    {
        $markerId = $request->cbomarker ? $request->cbomarker : 0;

        $data_ratio = DB::select("
            select
                *
            from
                marker_input_detail
            where
                marker_id = '" . $markerId . "'
        ");

        return DataTables::of($data_ratio)->toJson();
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
     * @param  \App\Models\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function show(FormCut $formCut)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCut $formCut)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormCut $formCut)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCut $formCut)
    {
        //
    }

    /**
     * Process the form cut input.
     *
     * @param  \App\Models\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function process($id = 0)
    {
        $formCutInputData = FormCutInput::leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->where('form_cut_input.id', $id)->first();

        $actCostingData = DB::connection("mysql_sb")->table('act_costing')->selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->groupBy('act_costing.id')->where('act_costing.id', $formCutInputData->act_costing_id)->get();

        $markerDetailData = MarkerDetail::selectRaw("
                marker_input.kode kode_marker,
                concat(master_sb_ws.size, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN ' - ' ELSE '' END, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN master_sb_ws.dest ELSE '' END) size,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
            where("marker_input.kode", $formCutInputData->kode)->
            where("marker_input.cancel", "N")->
            groupBy("marker_input_detail.so_det_id")->
            get();

        if (Auth::user()->type == "meja" && Auth::user()->id != $formCutInputData->no_meja) {
            return Redirect::to('/home');
        }

        return view("cutting.cutting-form.cutting-form-process", [
            'id' => $id,
            'formCutInputData' => $formCutInputData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'page' => 'dashboard-cutting',
            "subPageGroup" => "proses-cutting",
            "subPage" => "form-cut-input"
        ]);
    }

    public function getNumberData(Request $request)
    {
        $numberData = DB::connection('mysql_sb')->table("bom_jo_item")->selectRaw("
                bom_jo_item.cons cons_ws
            ")->
            leftJoin("so_det", "so_det.id", "=", "bom_jo_item.id_so_det")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("masteritem", "masteritem.id_gen", "=", "bom_jo_item.id_item")->
            leftJoin("masterpanel", "masterpanel.id", "=", "bom_jo_item.id_panel")->
            where("act_costing.id", $request->act_costing_id)->
            where("so_det.color", $request->color)->
            where("masterpanel.nama_panel", $request->panel)->
            where("bom_jo_item.status", "M")->
            where("bom_jo_item.cancel", "N")->
            where("so_det.cancel", "N")->
            where("so.cancel_h", "N")->
            where("act_costing.status", "CONFIRM")->
            where("masteritem.mattype", "F")->
            where("masteritem.mattype", "F")->
            groupBy("so_det.color", "bom_jo_item.id_item", "bom_jo_item.unit")->first();

        return json_encode($numberData);
    }

    public function startProcess($id = 0, Request $request)
    {
        $startTime = $request->startTime;

        $waktuMulai = (empty($startTime) || !strtotime($startTime)) ? Carbon::now() : Carbon::parse($startTime);

        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "status" => "PENGERJAAN FORM CUTTING",
            "waktu_mulai" => $waktuMulai,
        ]);

        if ($updateFormCutInput) {
            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function nextProcessOne($id = 0, Request $request)
    {
        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "status" => "PENGERJAAN FORM CUTTING DETAIL",
            "shell" => $request->shell
        ]);

        if ($updateFormCutInput) {
            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function nextProcessTwo($id = 0, Request $request)
    {
        $validatedRequest = $request->validate([
            "p_act" => "required",
            "unit_p_act" => "required",
            "comma_act" => "required",
            "unit_comma_act" => "required",
            "l_act" => "required|min:0.1",
            "unit_l_act" => "required",
            "cons_act" => "required",
            "cons_pipping" => "required",
            "cons_ampar" => "required",
            "est_pipping" => "required",
            "est_pipping_unit" => "required",
            "est_kain" => "required",
            "est_kain_unit" => "required",
        ]);

        if ($validatedRequest["p_act"] + $validatedRequest["comma_act"] > 0 && $validatedRequest["l_act"] > 0) {
            $updateFormCutInput = FormCutInput::where("id", $id)->update([
                "status" => "PENGERJAAN FORM CUTTING SPREAD",
                "p_act" => $validatedRequest['p_act'],
                "unit_p_act" => $validatedRequest['unit_p_act'],
                "comma_p_act" => $validatedRequest['comma_act'],
                "unit_comma_p_act" => $validatedRequest['unit_comma_act'],
                "l_act" => $validatedRequest['l_act'],
                "unit_l_act" => $validatedRequest['unit_l_act'],
                // "cons_act" => $validatedRequest['cons_act'],
                "cons_pipping" => $validatedRequest['cons_pipping'],
                "cons_ampar" => $validatedRequest['cons_act'],
                "est_pipping" => $validatedRequest['est_pipping'],
                "est_pipping_unit" => $validatedRequest['est_pipping_unit'],
                "est_kain" => $validatedRequest['est_kain'],
                "est_kain_unit" => $validatedRequest['est_kain_unit']
            ]);

            if ($updateFormCutInput) {
                return array(
                    "status" => 200,
                    "message" => "alright",
                    "additional" => [],
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function getTimeRecord($id = 0, $noForm = 0)
    {
        $timeRecordSummary = FormCutInputDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where("form_cut_input_detail.form_cut_id", $id)->where("form_cut_input_detail.no_form_cut_input", $noForm)->where('form_cut_input_detail.status', '!=', 'not complete')->where('form_cut_input_detail.status', '!=', 'extension')->groupBy('form_cut_input_detail.id')->orderByRaw('form_cut_input_detail.created_at asc')->get();

        return json_encode($timeRecordSummary);
    }

    public function storeTimeRecord(Request $request, CuttingService $cuttingService)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 300);

        $validatedRequest = $request->validate([
            "id" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "color_act" => "nullable",
            "current_id_item" => "required",
            "detail_item" => "nullable",
            "current_group" => "required",
            "current_roll" => "nullable",
            "current_roll_buyer" => "nullable",
            "current_qty" => "required",
            "current_qty_real" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_average_time" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "required",
            "current_pemakaian_lembar" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_sambungan" => "required",
            "p_act" => "required"
        ],[
            'required' => 'Harap tentukan :attribute.',
        ]);
        Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Request Validation Complete #1");

        $user = Auth::user();

        // Set Roll Usage Status
        $status = 'complete';
        if ($validatedRequest['current_sisa_gelaran'] > 0) {
            $status = 'need extension';
        }

        // Set Current Group Stocker and Qty
        $beforeData = DB::table("form_cut_input_detail")->select('group_roll', 'group_stocker')->where('form_cut_id', $validatedRequest['id'])->where('no_form_cut_input', $validatedRequest['no_form_cut_input'])->whereRaw('(form_cut_input_detail.status = "complete" || form_cut_input_detail.status = "need extension" || form_cut_input_detail.status = "extension complete")')->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->orderBy('created_at', 'desc')->first();
        $groupStocker = $beforeData ? ($beforeData->group_roll == $validatedRequest['current_group'] ? $beforeData->group_stocker : $beforeData->group_stocker + 1) : 1;
        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        $checkTimeRecord = DB::table("form_cut_input_detail")->where("form_cut_id", $validatedRequest['id'])->where("status", "not complete")->first();

        $now = Carbon::now();

        // Update or Create Form Cut Input Detail (Roll Usage)
        $storeTimeRecordSummary = null;
        if ($checkTimeRecord) {
            $storeTimeRecordSummary = $checkTimeRecord;
            $updateTimeRecordSummary = FormCutInputDetail::where("form_cut_id", $validatedRequest['id'])->where('status', 'not complete')->
                update([
                    "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                    "id_roll" => $validatedRequest['current_id_roll'],
                    "id_item" => $validatedRequest['current_id_item'],
                    "color_act" => $validatedRequest['color_act'],
                    "detail_item" => $validatedRequest['detail_item'],
                    "group_roll" => $validatedRequest['current_group'],
                    "lot" => $request["current_lot"],
                    "roll" => $validatedRequest['current_roll'],
                    "roll_buyer" => $validatedRequest['current_roll_buyer'],
                    "qty" => $itemQty,
                    "unit" => $itemUnit,
                    "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                    "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                    "sambungan" => $validatedRequest['current_sambungan'],
                    "est_amparan" => $validatedRequest['current_est_amparan'],
                    "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                    "average_time" => $validatedRequest['current_average_time'],
                    "kepala_kain" => $validatedRequest['current_kepala_kain'],
                    "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                    "reject" => $validatedRequest['current_reject'],
                    "sisa_kain" => $validatedRequest['current_sisa_kain'],
                    "pemakaian_lembar" => $validatedRequest['current_pemakaian_lembar'],
                    "short_roll" => $validatedRequest['current_short_roll'],
                    "piping" => $validatedRequest['current_piping'],
                    "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                    "status" => $status,
                    "metode" => $request->metode ? $request->metode : "scan",
                    "group_stocker" => $groupStocker,
                    "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                ]);
        } else {
            // Prevent redundant
            $checkSimilarTimeRecord = DB::table("form_cut_input_detail")->where("form_cut_id", $validatedRequest['id'])->where("id_roll", $validatedRequest['current_id_roll'])->where("qty", $itemQty)->first();
            if (!$checkSimilarTimeRecord || !$validatedRequest['current_id_roll']) {
                $storeTimeRecordSummary = FormCutInputDetail::create([
                        "form_cut_id" => $validatedRequest['id'],
                        "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                        "id_roll" => $validatedRequest['current_id_roll'],
                        "id_item" => $validatedRequest['current_id_item'],
                        "color_act" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "group_roll" => $validatedRequest['current_group'],
                        "lot" => $request["current_lot"],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemQty,
                        "unit" => $itemUnit,
                        "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                        "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                        "sambungan" => $validatedRequest['current_sambungan'],
                        "est_amparan" => $validatedRequest['current_est_amparan'],
                        "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                        "average_time" => $validatedRequest['current_average_time'],
                        "kepala_kain" => $validatedRequest['current_kepala_kain'],
                        "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                        "reject" => $validatedRequest['current_reject'],
                        "sisa_kain" => $validatedRequest['current_sisa_kain'],
                        "pemakaian_lembar" => $validatedRequest['current_pemakaian_lembar'],
                        "short_roll" => $validatedRequest['current_short_roll'],
                        "piping" => $validatedRequest['current_piping'],
                        "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                        "status" => $status,
                        "metode" => $request->metode ? $request->metode : "scan",
                        "group_stocker" => $groupStocker,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                        "created_by" => $user ? $user->id : null,
                        "created_by_username" => $user ? $user->username : null,
                        "created_at" => $now,
                        "updated_at" => $now,
                    ]);
            }
        }

        if ($storeTimeRecordSummary) {
            Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Store Time Record Complete with Status Actual = '".$storeTimeRecordSummary->status."' and Status Request = '".$status."' #2");

            // Sambungan Dalam Roll
            $sambunganRoll = $request['sambungan_roll'] ? array_filter($request['sambungan_roll'], fn($v) => $v > 0) : null;

            if ($sambunganRoll && count($sambunganRoll) > 0) {
                $sambunganArr = [];
                for ($i = 0; $i < count($sambunganRoll); $i++) {
                    if ($sambunganRoll[$i] > 0) {
                        array_push($sambunganArr, [
                            "form_cut_input_detail_id" => $storeTimeRecordSummary->id,
                            "sambungan_ke" => $i+1,
                            "sambungan_roll" => $sambunganRoll[$i]
                        ]);
                    }
                }

                FormCutInputDetailSambungan::upsert($sambunganArr, ["form_cut_input_detail_id", "sambungan_ke"], ["sambungan_roll"]);
            }
            Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Sambungan Dalam Roll Completed #3");

            // $itemRemain = $itemQty - floatval($validatedRequest['current_total_pemakaian_roll']) - floatval($validatedRequest['current_kepala_kain']) - floatval($validatedRequest['current_sisa_tidak_bisa']) - floatval($validatedRequest['current_reject']) - floatval($validatedRequest['current_piping']);
            $itemRemain = $validatedRequest['current_sisa_kain'];

            if ($status == 'need extension') {
                $postNow = $now->addSecond();
                // When the roll need an extension

                // Update scanned item (roll detail & qty)
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['current_id_roll']],
                    [
                        "id_item" => $validatedRequest['current_id_item'],
                        "color" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemRemain > 0 ? 0 : $itemRemain,
                        "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + COALESCE(".$validatedRequest['current_total_pemakaian_roll'].", 0)"),
                        "unit" => $itemUnit,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                    ]
                );
                Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Update Item Completed #4");

                // Create an extension
                $storeTimeRecordSummaryExt = FormCutInputDetail::create([
                    "form_cut_id" => $validatedRequest['id'],
                    "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                    "group_roll" => $validatedRequest['current_group'],
                    "id_sambungan" => $storeTimeRecordSummary->id,
                    "status" => "extension",
                    "group_stocker" => $groupStocker,
                    "created_by" => $user ? $user->id : null,
                    "created_by_username" => $user ? $user->username : null,
                    "created_at" => $postNow,
                    "updated_at" => $postNow,
                ]);
                if ($storeTimeRecordSummaryExt) {
                    Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' The Extension Completed #5");

                    // Delete Redundant if it still passed the prevention attempt at last (somehow this one returning error sometimes)
                    $cuttingService->deleteRedundant($storeTimeRecordSummary->form_cut_id, $storeTimeRecordSummary->id_roll, $storeTimeRecordSummary->qty, $storeTimeRecordSummary->status);
                    Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Delete Redundant Roll Usage Completed #6");

                    // Return the recorded data with the extension
                    return array(
                        "status" => 200,
                        "message" => "alright",
                        "additional" => [
                            DB::table("form_cut_input_detail")->selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->first(),
                            DB::table("form_cut_input_detail")->selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummaryExt->id)->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->first()
                        ],
                    );
                }
            } else {
                // Update scanned item (roll detail & qty)
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['current_id_roll']],
                    [
                        "id_item" => $validatedRequest['current_id_item'],
                        "color" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemRemain,
                        "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + COALESCE(".$validatedRequest['current_total_pemakaian_roll'].", 0)"),
                        "unit" => $itemUnit,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                    ]
                );
                Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Update Item Completed #4");
            }

            // Delete Redundant if it still passed the prevention attempt at last (somehow this one returning error sometimes)
            $cuttingService->deleteRedundant($storeTimeRecordSummary->form_cut_id, $storeTimeRecordSummary->id_roll, $storeTimeRecordSummary->qty, $storeTimeRecordSummary->status);
            Log::channel("cuttingRollUsageProcess")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Delete Redundant Roll Completed #5");

            // Return the recorded data
            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [
                    DB::table("form_cut_input_detail")->selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->first(),
                    null
                ],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function storeThisTimeRecord(Request $request)
    {
        $user = Auth::user();

        // Current Lap
        $lap = $request->lap;

        // Current Qty
        $itemQty = ($request["current_unit"] != "KGM" ? floatval($request['current_qty']) : floatval($request['current_qty_real']));
        $itemUnit = ($request["current_unit"] != "KGM" ? "METER" : $request['current_unit']);

        // Get the current not completed roll usage
        $checkTimeRecord = DB::table("form_cut_input_detail")->where("form_cut_id", $request->id)->where("status", "not complete")->first();

        // Update or Create Form Cut Input Detail (Roll Usage)
        $storeTimeRecordSummary = null;
        if ($checkTimeRecord) {
            $storeTimeRecordSummary = $checkTimeRecord;
            $updateTimeRecordSummary = FormCutInputDetail::where("form_cut_id", $request->id)->where("status", "not complete")->
                update(
                    [
                        "no_form_cut_input" => $request->no_form_cut_input,
                        "id_roll" => $request->current_id_roll,
                        "id_item" => $request->current_id_item,
                        "color_act" => $request->color_act,
                        "detail_item" => $request->detail_item,
                        "group_roll" => $request->current_group,
                        "lot" => $request->current_lot,
                        "roll" => $request->current_roll,
                        "roll_buyer" => $request->current_roll_buyer,
                        "qty" => $itemQty,
                        "unit" => $itemUnit,
                        "sisa_gelaran" => $request->current_sisa_gelaran,
                        "sambungan" => $request->current_sambungan,
                        "est_amparan" => $request->current_est_amparan,
                        "lembar_gelaran" => $request->current_lembar_gelaran,
                        "average_time" => $request->current_average_time,
                        "kepala_kain" => $request->current_kepala_kain,
                        "sisa_tidak_bisa" => $request->current_sisa_tidak_bisa,
                        "reject" => $request->current_reject,
                        "sisa_kain" => $request->current_sisa_kain,
                        "pemakaian_lembar" => $request->current_pemakaian_lembar,
                        "total_pemakaian_roll" => $request->current_total_pemakaian_roll,
                        "short_roll" => $request->current_short_roll,
                        "piping" => $request->current_piping,
                        "status" => "not complete",
                        "metode" => $request->metode ? $request->metode : "scan",
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0
                    ]
                );
        } else {
            // Prevent redundant
            $checkSimilarTimeRecord = DB::table("form_cut_input_detail")->where("form_cut_id", $request->id)->where("id_roll", $request->current_id_roll)->where("qty", $itemQty)->first();
            if (!$checkSimilarTimeRecord || !$request->current_id_roll) {
                $storeTimeRecordSummary = FormCutInputDetail::
                    create(
                        [
                            "form_cut_id" => $request->id,
                            "id_roll" => $request->current_id_roll,
                            "id_item" => $request->current_id_item,
                            "color_act" => $request->color_act,
                            "detail_item" => $request->detail_item,
                            "group_roll" => $request->current_group,
                            "lot" => $request->current_lot,
                            "roll" => $request->current_roll,
                            "roll_buyer" => $request->current_roll_buyer,
                            "qty" => $itemQty,
                            "unit" => $itemUnit,
                            "sisa_gelaran" => $request->current_sisa_gelaran,
                            "sambungan" => $request->current_sambungan,
                            "est_amparan" => $request->current_est_amparan,
                            "lembar_gelaran" => $request->current_lembar_gelaran,
                            "average_time" => $request->current_average_time,
                            "kepala_kain" => $request->current_kepala_kain,
                            "sisa_tidak_bisa" => $request->current_sisa_tidak_bisa,
                            "reject" => $request->current_reject,
                            "sisa_kain" => $request->current_sisa_kain,
                            "pemakaian_lembar" => $request->current_pemakaian_lembar,
                            "total_pemakaian_roll" => $request->current_total_pemakaian_roll,
                            "short_roll" => $request->current_short_roll,
                            "piping" => $request->current_piping,
                            "status" => "not complete",
                            "metode" => $request->metode ? $request->metode : "scan",
                            "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                            "created_by" => $user ? $user->id : null,
                            "created_by_username" => $user ? $user->username : null,
                        ]
                    );
            }
        }

        if ($storeTimeRecordSummary) {
            // Create or Update the Lap
            if ($lap > 0) {
                $storeTimeRecordLap = FormCutInputDetailLap::updateOrCreate(
                    ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "lembar_gelaran_ke" => $lap],
                    [
                        "waktu" => $request["time_record"][$lap],
                        "created_by" => $user ? $user->id : null,
                        "created_by_username" => $user ? $user->username : null,
                    ]
                );
            }

            // If there is Sambungan dalam Roll
            if ($request['sambungan_roll'] && count($request['sambungan_roll']) > 0) {
                for ($i = 0; $i < count($request['sambungan_roll']); $i++) {
                    if ($request['sambungan_roll'][$i] > 0) {

                        // Create or Update Sambungan dalam Roll
                        $storeSambungan = FormCutInputDetailSambungan::updateOrCreate(
                            ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "sambungan_ke" => $i+1],
                            [
                                "sambungan_roll" => $request['sambungan_roll'][$i],
                                "created_by" => $user ? $user->id : null,
                                "created_by_username" => $user ? $user->username : null,
                            ]
                        );
                    }
                }
            }

            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function storeTimeRecordExtension(Request $request, CuttingService $cuttingService)
    {
        $validatedRequest = $request->validate([
            "id" => "required",
            "status_sambungan" => "required",
            "id_sambungan" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "color_act" => "nullable",
            "current_id_item" => "required",
            "detail_item" => "required",
            "current_group" => "required",
            "current_roll" => "nullable",
            "current_roll_buyer" => "nullable",
            "current_qty" => "required",
            "current_qty_real" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_average_time" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "nullable",
            "current_pemakaian_lembar" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_sambungan" => "required"
        ]);
        Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Request Validation Complete #1");

        $user = Auth::user();

        // Now
        $now = Carbon::now();

        // Set Lap to only has 1
        $lap = 1;

        // Set Current Group and Qty
        $beforeData = DB::table("form_cut_input_detail")->select('group_roll', 'group_stocker')->where('form_cut_id', $validatedRequest['id'])->where('no_form_cut_input', $validatedRequest['no_form_cut_input'])->whereRaw('(form_cut_input_detail.status = "complete" || form_cut_input_detail.status = "need extension" || form_cut_input_detail.status = "extension complete")')->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->orderBy('created_at', 'desc')->first();
        $groupStocker = $beforeData ? ($beforeData->group_roll  == $validatedRequest['current_group'] ? $beforeData->group_stocker : $beforeData->group_stocker + 1) : 1;
        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        // Get Current Roll Usage
        $checkTimeRecord  = DB::table("form_cut_input_detail")->where("form_cut_id", $validatedRequest['id'])->where("status", "extension")->first();

        // Create or Update Form Cut Input Detail (Roll Usage)
        $storeTimeRecordSummary = null;
        if ($checkTimeRecord) {
            $storeTimeRecordSummary = $checkTimeRecord;
            $updateTimeRecordSummary = FormCutInputDetail::where("form_cut_id", $validatedRequest['id'])->where("status", "extension")->
                update(
                    [
                        "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                        "id_roll" => $validatedRequest['current_id_roll'],
                        "id_item" => $validatedRequest['current_id_item'],
                        "color_act" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "group_roll" => $validatedRequest['current_group'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemQty,
                        "unit" => $itemUnit,
                        "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                        "sambungan" => $validatedRequest['current_sambungan'],
                        "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                        "est_amparan" => $validatedRequest['current_est_amparan'],
                        "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                        "average_time" => $validatedRequest['current_average_time'],
                        "kepala_kain" => $validatedRequest['current_kepala_kain'],
                        "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                        "reject" => $validatedRequest['current_reject'],
                        "sisa_kain" => ($validatedRequest['current_sisa_kain'] ? $validatedRequest['current_sisa_kain'] : 0),
                        "pemakaian_lembar" => $validatedRequest['current_pemakaian_lembar'],
                        "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                        "short_roll" => $validatedRequest['current_short_roll'],
                        "piping" => $validatedRequest['current_piping'],
                        "status" => "extension complete",
                        "metode" => $request->metode ? $request->metode : "scan",
                        "group_stocker" => $groupStocker,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                        "updated_at" => $now,
                    ]
                );
        } else {
            // Prevent redundant
            $checkSimilarTimeRecord = DB::table("form_cut_input_detail")->where("form_cut_id", $validatedRequest['id'])->where("id_roll", $validatedRequest['current_id_roll'])->where("qty", $itemQty)->first();
            if (!$checkSimilarTimeRecord || !$validatedRequest['current_id_roll']) {
                $storeTimeRecordSummary = FormCutInputDetail::
                    create(
                        [
                            "form_cut_id" => $validatedRequest['id'],
                            "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                            "id_roll" => $validatedRequest['current_id_roll'],
                            "id_item" => $validatedRequest['current_id_item'],
                            "color_act" => $validatedRequest['color_act'],
                            "detail_item" => $validatedRequest['detail_item'],
                            "group_roll" => $validatedRequest['current_group'],
                            "lot" => $request['current_lot'],
                            "roll" => $validatedRequest['current_roll'],
                            "roll_buyer" => $validatedRequest['current_roll_buyer'],
                            "qty" => $itemQty,
                            "unit" => $itemUnit,
                            "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                            "sambungan" => $validatedRequest['current_sambungan'],
                            "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                            "est_amparan" => $validatedRequest['current_est_amparan'],
                            "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                            "average_time" => $validatedRequest['current_average_time'],
                            "kepala_kain" => $validatedRequest['current_kepala_kain'],
                            "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                            "reject" => $validatedRequest['current_reject'],
                            "sisa_kain" => ($validatedRequest['current_sisa_kain'] ? $validatedRequest['current_sisa_kain'] : 0),
                            "pemakaian_lembar" => $validatedRequest['current_pemakaian_lembar'],
                            "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                            "short_roll" => $validatedRequest['current_short_roll'],
                            "piping" => $validatedRequest['current_piping'],
                            "status" => "extension complete",
                            "metode" => $request->metode ? $request->metode : "scan",
                            "group_stocker" => $groupStocker,
                            "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                            "created_by" => $user ? $user->id : null,
                            "created_by_username" => $user ? $user->username : null,
                            "created_at" => $now,
                            "updated_at" => $now,
                        ]
                    );
            }
        }

        if ($storeTimeRecordSummary) {
            Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Store Time Record Complete with Status Actual = '".$storeTimeRecordSummary->status."' and Status Request = 'extension complete' #2");

            // Sambungan dalam Roll
            $sambunganRoll = $request['sambungan_roll'] ? array_filter($request['sambungan_roll'], function ($var) {
                return ($var > 0);
            }) : [];

            if ($sambunganRoll && count($sambunganRoll) > 0) {
                for ($i = 0; $i < count($sambunganRoll); $i++) {
                    if ($sambunganRoll[$i] > 0) {
                        $storeSambungan = FormCutInputDetailSambungan::updateOrCreate(
                            ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "sambungan_ke" => $i+1],
                            [
                                "sambungan_roll" => $sambunganRoll[$i],
                                "created_by" => $user ? $user->id : null,
                                "created_by_username" => $user ? $user->username : null,
                            ]
                        );
                    }
                }
            }
            Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Sambungan Dalam Roll Complete #3");

            // Update Scanned Item (Roll Detail Data & Qty Stock)
            $itemRemain = $itemQty - floatval($validatedRequest['current_total_pemakaian_roll']);
            // $itemRemain = $validatedRequest['current_sisa_kain'];
            ScannedItem::updateOrCreate(
                ["id_roll" => $validatedRequest['current_id_roll']],
                [
                    "id_item" => $validatedRequest['current_id_item'],
                    "color" => $validatedRequest['color_act'],
                    "detail_item" => $validatedRequest['detail_item'],
                    "lot" => $request['current_lot'],
                    "roll" => $validatedRequest['current_roll'],
                    "roll_buyer" => $validatedRequest['current_roll_buyer'],
                    "qty" => $itemRemain,
                    "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + ".$validatedRequest['current_total_pemakaian_roll']),
                    "unit" => $itemUnit,
                    "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                    "created_by" => $user ? $user->id : null,
                    "created_by_username" => $user ? $user->username : null,
                ]
            );
            Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' ID Roll : '".$validatedRequest["current_id_roll"]."' Update Item Completed #4");

            $postNow = $now->addSecond();

            // Create post-extension roll spreading
            $storeTimeRecordSummaryNext = FormCutInputDetail::create([
                "form_cut_id" => $validatedRequest['id'],
                "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                "id_roll" => $validatedRequest['current_id_roll'],
                "id_item" => $validatedRequest['current_id_item'],
                "color_act" => $validatedRequest['color_act'],
                "detail_item" => $validatedRequest['detail_item'],
                "group_roll" => $validatedRequest['current_group'],
                "lot" => $request['current_lot'],
                "roll" => $validatedRequest['current_roll'],
                "roll_buyer" => $validatedRequest['current_roll_buyer'],
                "qty" => $itemRemain,
                "unit" => $itemUnit,
                "sambungan" => 0,
                "status" => "not complete",
                "metode" => $request->metode ? $request->metode : null,
                "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                "created_at" => $postNow,
                "updated_at" => $postNow,
            ]);
            Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' Post-Extension Roll Completed #5");

            if ($lap > 0) {
                // Create the lap
                $storeTimeRecordLap = FormCutInputDetailLap::updateOrCreate(
                    ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "lembar_gelaran_ke" => $lap],
                    [
                        "waktu" => $request["time_record"][$lap]
                    ]
                );
            }
            Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' Lap Completed #6");

            // Delete Redundant if it still passed the prevention attempt
            $cuttingService->deleteRedundant($storeTimeRecordSummary->form_cut_id, $storeTimeRecordSummary->id_roll, $storeTimeRecordSummary->qty, "extension complete");
            Log::channel("cuttingRollUsageProcessExtension")->info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Form ID : '".$validatedRequest["id"]."' No. Form : '".$validatedRequest["no_form_cut_input"]."' Delete Redundant Roll Completed #7");

            // Return the current extension roll with the post-extension roll
            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [
                    DB::table("form_cut_input_detail")->selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->first(),
                    DB::table("form_cut_input_detail")->selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummaryNext->id)->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->first(),
                ],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function checkSpreadingForm($id = 0, $noForm = 0, $noMeja = 0)
    {
        $formCutInputDetailData = FormCutInputDetail::selectRaw('
                form_cut_input_detail.*,
                scanned_item.qty_in
            ')->
            leftJoin('scanned_item', 'scanned_item.id_roll', '=', 'form_cut_input_detail.id_roll')->
            leftJoin('form_cut_input', 'form_cut_input.id', '=', 'form_cut_input_detail.form_cut_id')->
            whereRaw('form_cut_input.id = ? '.($noForm ? ' and form_cut_input.no_form = "'.$noForm.'" ' : '').' '.($noMeja ? ' and form_cut_input.no_meja = "'.$noMeja.'" ' : '').'', [$id])->
            whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->
            orderBy('form_cut_input_detail.created_at', 'desc')->
            first();

        $formCutInputDetailCount = $formCutInputDetailData ? $formCutInputDetailData->count() : 0;

        if ($formCutInputDetailCount > 0) {
            if ($formCutInputDetailData->status == 'extension') {
                $thisFormCutInputDetail = DB::table("form_cut_input_detail")->select("sisa_gelaran", "unit")->where('id', $formCutInputDetailData->id_sambungan)->first();

                return array(
                    "count" => $formCutInputDetailCount,
                    "data" => $formCutInputDetailData,
                    "sisaGelaran" => $thisFormCutInputDetail->sisa_gelaran,
                    "unitSisaGelaran" => $thisFormCutInputDetail->unit,
                );
            } else if ($formCutInputDetailData->status == 'not complete') {

                return array(
                    "count" => $formCutInputDetailCount,
                    "data" => $formCutInputDetailData,
                    "sisaGelaran" => 0,
                    "unitSisaGelaran" => null,
                );
            }
        }

        return array(
            "count" => null,
            "data" => null
        );
    }

    public function checkTimeRecordLap($detailId = 0)
    {
        $formCutInputDetailLapData = FormCutInputDetailLap::where('form_cut_input_detail_id', $detailId)->get();

        return array(
            "count" => $formCutInputDetailLapData->count(),
            "data" => $formCutInputDetailLapData,
        );
    }

    public function storeLostTime(Request $request, $id = 0)
    {
        $now = Carbon::now();

        $current = $request["current_lost_time"];

        $storeTimeRecordLap = FormCutInputLostTime::updateOrCreate(
            ["form_cut_input_id" => $id, "lost_time_ke" => $request["current_lost_time"]],
            [
                "lost_time_ke" => $request["current_lost_time"],
                "waktu" => $request["lost_time"][$current],
            ]
        );
    }

    public function checkLostTime($id = 0)
    {
        $formCutInputLostTimeData = FormCutInputLostTime::where('form_cut_input_id', $id)->get();

        return array(
            "count" => $formCutInputLostTimeData->count(),
            "data" => $formCutInputLostTimeData,
        );
    }

    public function checkSambungan($id = 0)
    {
        $sambungan = FormCutInputDetailSambungan::where('form_cut_input_detail_id', $id)->get();

        return array(
            "count" => $sambungan->count(),
            "data" => $sambungan,
        );
    }

    public function finishProcess($id = 0, Request $request)
    {
        $formCutInputData = FormCutInput::where("id", $id)->first();

        // Get latest no. cut
        $formCutInputSimilarLatest = DB::table("form_cut_input")->leftJoin("marker_input", "marker_input.id", "=", "form_cut_input.marker_id")->
            where("marker_input.act_costing_ws", $formCutInputData->marker->act_costing_ws)->
            where("marker_input.color", $formCutInputData->marker->color)->
            where("marker_input.panel", $formCutInputData->marker->panel)->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            max("form_cut_input.no_cut");

        // Update the Form to be Finished
        $finishTime = $request->finishTime;
        $waktuSelesai = (empty($finishTime) || !strtotime($finishTime)) ? Carbon::now() : Carbon::parse($finishTime);

        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "status" => "SELESAI PENGERJAAN",
            "waktu_selesai" => $waktuSelesai,
            "cons_act" => $request->consAct,
            "unit_cons_act" => $request->unitConsAct,
            "cons_act_nosr" => $request->consActNoSr,
            "unit_cons_act_nosr" => $request->unitConsActNoSr,
            "total_lembar" => $request->totalLembar,
            "no_cut" => $formCutInputSimilarLatest + 1,
            "cons_ws_uprate" => $request->consWsUprate,
            "cons_marker_uprate" => $request->consMarkerUprate,
            "cons_ws_uprate_nosr" => $request->consWsUprateNoSr,
            "cons_marker_uprate_nosr" => $request->consMarkerUprateNoSr,
            "operator" => $request->operator,
        ]);

        // Backup the incomplete Form Cut Detail (Roll Spreading)
        $notCompletedDetails = DB::table("form_cut_input_detail")->where("form_cut_id", $formCutInputData->id)->where("no_form_cut_input", $formCutInputData->no_form)->whereRaw("(status = 'not complete' OR status = 'extension')")->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->get();
        if ($notCompletedDetails->count() > 0) {
            foreach ($notCompletedDetails as $notCompletedDetail) {
                DB::table("form_cut_input_detail_delete")->insert([
                    "form_cut_id" => $notCompletedDetail->form_cut_id,
                    "no_form_cut_input" => $notCompletedDetail->no_form_cut_input,
                    "id_roll" => $notCompletedDetail->id_roll,
                    "id_item" => $notCompletedDetail->id_item,
                    "color_act" => $notCompletedDetail->color_act,
                    "detail_item" => $notCompletedDetail->detail_item,
                    "group_roll" => $notCompletedDetail->group_roll,
                    "lot" => $notCompletedDetail->lot,
                    "roll" => $notCompletedDetail->roll,
                    "roll_buyer" => $notCompletedDetail->roll_buyer,
                    "qty" => $notCompletedDetail->qty,
                    "unit" => $notCompletedDetail->unit,
                    "sisa_gelaran" => $notCompletedDetail->sisa_gelaran,
                    "sambungan_roll" => $notCompletedDetail->sambungan_roll,
                    "sambungan" => $notCompletedDetail->sambungan,
                    "est_amparan" => $notCompletedDetail->est_amparan,
                    "lembar_gelaran" => $notCompletedDetail->lembar_gelaran,
                    "average_time" => $notCompletedDetail->average_time,
                    "kepala_kain" => $notCompletedDetail->kepala_kain,
                    "sisa_tidak_bisa" => $notCompletedDetail->sisa_tidak_bisa,
                    "reject" => $notCompletedDetail->reject,
                    "sisa_kain" => ($notCompletedDetail->sisa_kain ? $notCompletedDetail->sisa_kain : 0),
                    "pemakaian_lembar" => $notCompletedDetail->pemakaian_lembar,
                    "short_roll" => $notCompletedDetail->short_roll,
                    "piping" => $notCompletedDetail->piping,
                    "total_pemakaian_roll" => $notCompletedDetail->total_pemakaian_roll,
                    "status" => $notCompletedDetail->status,
                    "metode" => $notCompletedDetail->metode,
                    "group_stocker" => $notCompletedDetail->group_stocker,
                    "created_at" => $notCompletedDetail->created_at,
                    "updated_at" => $notCompletedDetail->updated_at,
                    "deleted_by" => Auth::user()->username,
                    "deleted_at" => Carbon::now(),
                ]);

                // delete incomplete lap
                FormCutInputDetailLap::where("form_cut_input_detail_id", $notCompletedDetail->id)->delete();
            }
        }

        // delete incomplete detail
        FormCutInputDetail::where("form_cut_id", $formCutInputData->id)->where("no_form_cut_input", $formCutInputData->no_form)->whereRaw("(status = 'not complete' OR status = 'extension')")->delete();

        // store to part form
        $partData = DB::table("part")->select('part.id')->
            where("act_costing_id", $formCutInputData->marker->act_costing_id)->
            where("act_costing_ws", $formCutInputData->marker->act_costing_ws)->
            where("panel", $formCutInputData->marker->panel)->
            first();

        if ($partData) {
            $lastPartForm = DB::table("part_form")->select("kode")->orderBy("kode", "desc")->first();
            $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
            $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

            $addToPartForm = PartForm::create([
                "kode" => $kodePartForm,
                "part_id" => $partData->id,
                "form_id" => $formCutInputData->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);
        }

        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), (($formCutInputData && $formCutInputData->alokasiMeja) ? $formCutInputData->alokasiMeja->username : null));

        return $updateFormCutInput;
    }

    public function updateNoCut(Request $request) {
        $updatedForm = [];

        $markerGroups = Marker::select("act_costing_ws", "color", "panel")->groupBy("act_costing_ws", "color", "panel")->get();
        foreach ($markerGroups as $markerGroup) {
            $i = 0;

            $formCuts = FormCutInput::selectRaw("form_cut_input.id as id, form_cut_input.no_form, form_cut_input.status")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                where("marker_input.act_costing_ws", $markerGroup->act_costing_ws)->
                where("marker_input.color", $markerGroup->color)->
                where("marker_input.panel", $markerGroup->panel)->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                orderBy("form_cut_input.waktu_selesai", "asc")->
                get();

            foreach ($formCuts as $formCut) {
                $i++;

                $updateFormCut = FormCutInput::where("id", $formCut->id)->
                    update([
                        "no_cut" => $i
                    ]);

                if ($updateFormCut) {
                    array_push($updatedForm, ["ws_no_form" => $markerGroup->act_costing_ws."-".$markerGroup->color."-".$markerGroup->panel."-".$formCut->no_form."-".$formCut->status."-".$i]);
                }
            }
        }

        return $updatedForm;
    }

    public function formCutLock(Request $request) {
        $validatedRequest = $request->validate([
            "id" => "required",
        ]);

        $formCut = FormCutInput::where("id", $validatedRequest['id'])->update([
            "locked" => 1,
            "unlocked_by" => null,
        ]);

        return $formCut;
    }

    public function formCutUnlock(Request $request) {
        $validatedRequest = $request->validate([
            "id" => "required",
            "username" => "required",
            "password" => "required",
        ]);

        if (Auth::validate(['username' => $validatedRequest['username'], 'password' => $validatedRequest['password']])) {
            $unlocker = User::where("username", $validatedRequest['username'])->whereIn("type", ["admin", "superadmin"])->where("cutting_unlocker", 1)->first();

            if ($unlocker) {
                FormCutInput::where("id", $validatedRequest['id'])->update([
                    "locked" => 0,
                    "unlocked_by" => $unlocker->id,
                ]);

                $formCut = FormCutInput::where("id", $validatedRequest['id'])->first();

                if ($formCut) {
                    return $formCut;
                }

                // $unlocker->unlock_token = ($unlocker->unlock_token ? $unlocker->id."".Carbon::now()->format('ymd')."".substr($unlocker->unlock_token, -1)+1 : $unlocker->id."".Carbon::now()->format('Ymd')."1");
                // $unlocker->save();
            } else {
                return response()->json(['message' => 'Unauthorized: User is not a cutting unlocker'], 401);
            }
        } else {
            return response()->json(['message' => 'Unauthorized: Invalid credentials'], 401);
        }
    }
}
