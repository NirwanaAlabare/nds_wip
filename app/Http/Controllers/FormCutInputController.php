<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use Illuminate\Http\Request;
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

            $formCutInputQuery = FormCutInput::selectRaw("
                    form_cut_input.id id,
                    form_cut_input.no_form no_form,
                    form_cut_input.tgl_form_cut tgl_form,
                    markers.kode kode_marker,
                    markers.act_costing_ws no_ws,
                    markers.color,
                    markers.panel,
                    form_cut_input.status
                ")->leftJoin("markers", "markers.kode", "=", "form_cut_input.id_marker");

            if ($tglAwal) {
                $formCutInputQuery->whereRaw("tgl_form_cut >= '".$tglAwal."'");
            }

            if ($tglAkhir) {
                $formCutInputQuery->whereRaw("tgl_form_cut <= '".$tglAkhir."'");
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
        $actCostingData = DB::connection("mysql_sb")->
            table('act_costing')->
            selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->
            leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->
            groupBy('act_costing.id')->
            get();

        $markerDetailData = MarkerDetail::selectRaw("
                markers.kode kode_marker,
                marker_details.so_det_id,
                marker_details.ratio,
                marker_details.cut_qty
            ")->
            leftJoin("markers", "markers.id", "=", "marker_details.marker_id")->get();

        $soDetData = DB::connection("mysql_sb")->
            table('so_det')->
            selectRaw('id, size')->
            get();

        $formCutInputData = FormCutInput::leftJoin("markers", "markers.kode", "=", "form_cut_input.id_marker")->
            where('form_cut_input.id', $id)->
            first();

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

        return $updateFormCutInput;
    }

    public function nextProcessOne($id, Request $request) {
        $updateFormCutInput = FormCutInput::where("id", $id)->
            update([
                "status" => "PENGERJAAN FORM CUTTING DETAIL",
            ]);

        return $updateFormCutInput;
    }

    public function nextProcessTwo($id, Request $request) {
        $validatedRequest = $request->validate([
            "p_actual" => "required",
            "unit_p_actual" => "required",
            "comma_actual" => "required",
            "unit_comma_actual" => "required",
            "l_actual" => "required",
            "unit_l_actual" => "required",
            "cons_actual" => "required",
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
                "p_act" => $validatedRequest['p_actual'],
                "unit_p_act" => $validatedRequest['unit_p_actual'],
                "comma_p_act" => $validatedRequest['comma_actual'],
                "unit_comma_p_act" => $validatedRequest['unit_comma_actual'],
                "l_act" => $validatedRequest['l_actual'],
                "unit_l_act" => $validatedRequest['unit_l_actual'],
                "cons_act" => $validatedRequest['cons_actual'],
                // "cons_pipping" => $validatedRequest['cons_pipping'],
                // "cons_ampar" => $validatedRequest['cons_ampar'],
                // "est_pipping" => $validatedRequest['est_pipping'],
                // "est_pipping_unit" => $validatedRequest['est_pipping_unit'],
                // "est_kain" => $validatedRequest['est_kain'],
                // "est_kain_unit" => $validatedRequest['est_kain_unit']
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

    public function storeTimeRecord(Request $request) {
        $storeTimeRecordSummary = FormCutInputDetail::create([
            "no_form_cut_input" => $request->no_form_cut_input,
            "id_item" => $request->id_item,
            "group" => $request->group,
            "lot" => $request->lot,
            "roll" => $request->roll,
            "qty" => $request->qty,
            "unit" => $request->unit,
            "lap" => $request->lap
        ]);

        if ($storeTimeRecordSummary) {
            $storeTimeRecordDetail = FormCutInputDetailLap::create([

            ]);
        }
    }
}
