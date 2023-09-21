<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use DB;

class FormCutInputController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tglAwal = $request->tgl_awal;
            $tglAkhir = $request->tgl_akhir;
            $keyword = $request->search["value"];

            $formCutInputQuery = FormCutInput::selectRaw("
                    form_cut_input.id id,
                    form_cut_input.no_form no_form,
                    form_cut_input.tgl_form_cut tgl_form,
                    marker_input.kode kode_marker,
                    marker_input.act_costing_ws no_ws,
                    marker_input.color,
                    marker_input.panel,
                    form_cut_input.status
                ")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker");

            if ($tglAwal) {
                $formCutInputQuery->whereRaw("tgl_form_cut >= '".$tglAwal."'");
            }

            if ($tglAkhir) {
                $formCutInputQuery->whereRaw("tgl_form_cut <= '".$tglAkhir."'");
            }

            if ($keyword) {
                $formCutInputQuery->whereRaw("(
                    marker_input.no_form like '%".$keyword."%' OR
                    form_cut_input.tgl_form_cut like '%".$keyword."%' OR
                    marker_input.kode like '%".$keyword."%' OR
                    marker_input.act_costing_ws like '%".$keyword."%' OR
                    marker_input.color like '%".$keyword."%' OR
                    marker_input.panel like '%".$keyword."%' OR
                    form_cut_input.status like '%".$keyword."%'
                )");
            }

            if (Auth::user()->type == "meja") {
                $formCutInputQuery->where("form_cut_input.no_meja", Auth::user()->id);
            }

            $formCutInput = $formCutInputQuery->get();

            return json_encode([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval(count($formCutInput)),
                "recordsFiltered" => intval(count($formCutInput)),
                "data" => $formCutInput
            ]);
        }

        return view('form-cut.form-cut-input');
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
    public function process($id)
    {
        $formCutInputData = FormCutInput::leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where('form_cut_input.id', $id)->
            first();

        $actCostingData = DB::connection("mysql_sb")->
            table('act_costing')->
            selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->
            leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->
            groupBy('act_costing.id')->
            where('act_costing.id', $formCutInputData->act_costing_id)->
            get();

        $markerDetailData = MarkerDetail::selectRaw("
                marker_input.kode kode_marker,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            where("marker_input.kode", $formCutInputData->kode)->
            get();

        $soDetData = DB::connection("mysql_sb")->
            table('so_det')->
            selectRaw('so_det.id id, so_det.size size')->
            leftJoin('so', 'so.id', '=', 'so_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->
            where("act_costing.id", $formCutInputData->act_costing_id)->
            get();

        if (Auth::user()->type == "meja" && Auth::user()->id != $formCutInputData->no_meja) {
            return Redirect::to('/home');
        }

        return view("form-cut.process-form-cut-input", [
            'id' => $id,
            'formCutInputData' => $formCutInputData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'soDetData' => $soDetData
        ]);
    }

    public function getNumberData(Request $request) {
        $numberData = DB::connection('mysql_sb')->
            table("bom_jo_item")->
            selectRaw("
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
            groupBy("so_det.color", "bom_jo_item.id_item", "bom_jo_item.unit")->
            first();

        return json_encode($numberData);
    }

    public function getScannedItem($id) {
        $item = DB::connection("mysql_sb")->select("
            select br.id,
            mi.itemdesc,
            mi.id_item,
            goods_code,
            supplier,
            bpbno_int,
            pono,
            invno,
            ac.kpno,
            roll_no,
            roll_qty,
            lot_no,
            bpb.unit,
            kode_rak
            from bpb_roll br
            inner join bpb_roll_h brh on br.id_h = brh.id
            inner join masteritem mi on brh.id_item = mi.id_item
            inner join bpb on brh.bpbno = bpb.bpbno and brh.id_jo = bpb.id_jo and brh.id_item = bpb.id_item
            inner join mastersupplier ms on bpb.id_supplier = ms.Id_Supplier
            inner join jo_det jd on brh.id_jo = jd.id_jo
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join master_rak mr on br.id_rak_loc = mr.id
            where br.id = '".$id."'
            limit 1
        ");

        return json_encode($item ? $item[0] : null);
    }

    public function startProcess($id, Request $request) {
        $updateFormCutInput = FormCutInput::where("id", $id)->
            update([
                "status" => "PENGERJAAN FORM CUTTING",
                "waktu_mulai" => $request->startTime
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

    public function nextProcessOne($id, Request $request) {
        $updateFormCutInput = FormCutInput::where("id", $id)->
            update([
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

    public function nextProcessTwo($id, Request $request) {
        $validatedRequest = $request->validate([
            "p_act" => "required",
            "unit_p_act" => "required",
            "comma_act" => "required",
            "unit_comma_act" => "required",
            "l_act" => "required",
            "unit_l_act" => "required",
            "cons_act" => "required",
            "cons_pipping" => "required",
            "cons_ampar" => "required",
            "est_pipping" => "required",
            "est_pipping_unit" => "required",
            "est_kain" => "required",
            "est_kain_unit" => "required",
        ]);

        $updateFormCutInput = FormCutInput::where("id", $id)->
            update([
                "status" => "PENGERJAAN FORM CUTTING SPREAD",
                "p_act" => $validatedRequest['p_act'],
                "unit_p_act" => $validatedRequest['unit_p_act'],
                "comma_p_act" => $validatedRequest['comma_act'],
                "unit_comma_p_act" => $validatedRequest['unit_comma_act'],
                "l_act" => $validatedRequest['l_act'],
                "unit_l_act" => $validatedRequest['unit_l_act'],
                "cons_act" => $validatedRequest['cons_act'],
                "cons_pipping" => $validatedRequest['cons_pipping'],
                "cons_ampar" => $validatedRequest['cons_ampar'],
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

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function getTimeRecord($noForm) {
        $timeRecordSummary = FormCutInputDetail::where("no_form_cut_input", $noForm)->get();

        return json_encode($timeRecordSummary);
    }

    public function storeTimeRecord(Request $request) {
        $validatedRequest = $request->validate([
            "no_form_cut_input" => "required",
            "current_id_item" => "required",
            "current_group" => "required",
            "current_lot" => "required",
            "current_roll" => "required",
            "current_qty" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_average_time" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_remark" => "required"
        ]);

        $storeTimeRecordSummary = FormCutInputDetail::create([
            "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
            "id_item" => $validatedRequest['current_id_item'],
            "group" => $validatedRequest['current_group'],
            "lot" => $validatedRequest['current_lot'],
            "roll" => $validatedRequest['current_roll'],
            "qty" => $validatedRequest['current_qty'],
            "unit" => $validatedRequest['current_unit'],
            "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
            "est_amparan" => $validatedRequest['current_est_amparan'],
            "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
            "average_time" => $validatedRequest['current_average_time'],
            "kepala_kain" => $validatedRequest['current_kepala_kain'],
            "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
            "reject" => $validatedRequest['current_reject'],
            "sisa_kain" => $validatedRequest['current_sisa_kain'],
            "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
            "short_roll" => $validatedRequest['current_short_roll'],
            "piping" => $validatedRequest['current_piping'],
            "remark" => $validatedRequest['current_remark'],
        ]);

        $now = Carbon::now();

        if ($storeTimeRecordSummary) {
            $timeRecordLap = [];
            for ($i = 1; $i <= $validatedRequest['current_lembar_gelaran']; $i++) {
                array_push($timeRecordLap, [
                    "form_cut_input_detail_id" => $storeTimeRecordSummary->id,
                    "lembar_gelaran_ke" => $i,
                    "waktu" => $request["time_record"][$i],
                    "created_at" => $now,
                    "updated_at" => $now,
                ]);
            }

            $storeTimeRecordLap = FormCutInputDetailLap::insert($timeRecordLap);

            if (count($timeRecordLap) > 0) {
                return array(
                    "status" => 200,
                    "message" => "alright",
                    "additional" => [FormCutInputDetail::where('id', $storeTimeRecordSummary->id)->first()],
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function finishProcess($id, Request $request) {
        $updateFormCutInput = FormCutInput::where("id", $id)->
            update([
                "status" => "SELESAI PENGERJAAN",
                "waktu_selesai" => $request->finishTime
            ]);

        return $updateFormCutInput;
    }
}
