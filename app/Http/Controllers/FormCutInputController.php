<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\FormCutInputLostTime;
use App\Models\ScannedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
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
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= "and (cutting_plan.tgl_plan >= '" . $request->dateFrom . "' or a.updated_at >= '". $request->dateFrom ."')";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and (cutting_plan.tgl_plan <= '" . $request->dateTo . "' or a.updated_at <= '". $request->dateTo ."')";
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
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    panel panel,
                    b.color color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    a.qty_ply,
                    b.gelar_qty gelar_qty,
                    b.po_marker po_marker,
                    b.urutan_marker urutan_marker,
                    b.cons_marker cons_marker,
                    cutting_plan.app,
                    a.tipe_form_cut,
                    b.notes notes,
                    GROUP_CONCAT(DISTINCT CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC) marker_details
                FROM cutting_plan
                left join form_cut_input a on a.no_form = cutting_plan.no_form_cut_input
                left outer join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left outer join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                where
                    a.id is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY FIELD(a.status, 'PENGERJAAN FORM CUTTING', 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'), a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        return view('form-cut.form-cut-input', ["page" => "dashboard-cutting"]);
    }

    public function getRatio(Request $request)
    {
        $markerId = $request->cbomarker ? $request->cbomarker : 0;

        $data_ratio = DB::select("
            select
                *
            from
                marker_input_detail
            where marker_id = '" . $markerId . "'
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
                marker_input_detail.size,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            where("marker_input.kode", $formCutInputData->kode)->
            where("marker_input.cancel", "N")->
            get();

        if (Auth::user()->type == "meja" && Auth::user()->id != $formCutInputData->no_meja) {
            return Redirect::to('/home');
        }

        return view("form-cut.process-form-cut-input", [
            'id' => $id,
            'formCutInputData' => $formCutInputData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'page' => 'dashboard-cutting'
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
            where("act_costing.id", $request->act_costing_id)->where("so_det.color", $request->color)->
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

    public function getScannedItem($id = 0)
    {
        $scannedItem = ScannedItem::where('id_roll', $id)->first();

        if ($scannedItem) {
            if (floatval($scannedItem->qty) > 0) {
                return json_encode($scannedItem);
            }

            return json_encode(null);
        }

        $item = DB::connection("mysql_sb")->select("
            select br.id id_roll,
            mi.itemdesc detail_item,
            mi.id_item,
            goods_code,
            supplier,
            bpbno_int,
            pono,
            invno,
            ac.kpno,
            roll_no roll,
            roll_qty qty,
            lot_no lot,
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
            where br.id = '" . $id . "'
            and cast(roll_qty as decimal(11,3)) > 0.000
            limit 1
        ");

        return json_encode($item ? $item[0] : null);
    }

    public function getItem(Request $request) {
        $items = DB::connection("mysql_sb")->select("
            SELECT
                item.id_item,
                item.itemdesc
            FROM
                jo_det jd
                INNER JOIN so ON jd.id_so = so.id
                INNER JOIN act_costing ac ON so.id_cost = ac.id
                INNER JOIN (
                SELECT
                    mi.id_item,
                    mi.itemdesc,
                    k.id_jo
                FROM
                    bom_jo_item k
                    INNER JOIN masteritem mi ON k.id_item = mi.id_gen
                WHERE
                    mi.Mattype = 'F'
                GROUP BY
                    k.id_jo,
                    k.id_item
                ) item ON item.id_jo = jd.id
            WHERE
                jd.cancel = 'N'
                AND ac.id = '".$request->act_costing_id."'
            GROUP BY
                item.id_item,
                id_cost
            ORDER BY
                jd.id_jo ASC
        ");

        return json_encode($items ? $items : null);
    }

    public function startProcess($id = 0, Request $request)
    {
        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "status" => "PENGERJAAN FORM CUTTING",
            "waktu_mulai" => $request->startTime,
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

        $updateFormCutInput = FormCutInput::where("id", $id)->update([
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

    public function getTimeRecord($noForm = 0)
    {
        $timeRecordSummary = FormCutInputDetail::where("no_form_cut_input", $noForm)->where('status', '!=', 'not complete')->where('status', '!=', 'extension')->get();

        return json_encode($timeRecordSummary);
    }

    public function storeTimeRecord(Request $request)
    {
        $validatedRequest = $request->validate([
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "color_act" => "nullable",
            "current_id_item" => "required",
            "detail_item" => "nullable",
            "current_group" => "required",
            "current_roll" => "nullable",
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
            "current_total_pemakaian_roll" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_remark" => "required",
            "current_sambungan" => "required",
            "p_act" => "required"
        ]);

        $status = 'complete';

        if ($validatedRequest['current_sisa_gelaran'] > 0) {
            $status = 'need extension';
        }

        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        $storeTimeRecordSummary = FormCutInputDetail::selectRaw("form_cut_input_detail.*")->leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->where('form_cut_input.no_meja', $validatedRequest['no_meja'])->where('form_cut_input_detail.status', 'not complete')->updateOrCreate(
            ["no_form_cut_input" => $validatedRequest['no_form_cut_input']],
            [
                "id_roll" => $validatedRequest['current_id_roll'],
                "id_item" => $validatedRequest['current_id_item'],
                "color_act" => $validatedRequest['color_act'],
                "detail_item" => $validatedRequest['detail_item'],
                "group" => $validatedRequest['current_group'],
                "lot" => $request["current_lot"],
                "roll" => $validatedRequest['current_roll'],
                "qty" => $itemQty,
                "unit" => $itemUnit,
                "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                "sambungan" => $validatedRequest['current_sambungan'],
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
                "status" => $status,
                "metode" => $request->metode ? $request->metode : "scan",
            ]
        );

        if ($storeTimeRecordSummary) {
            $itemRemain = $itemQty - floatval($validatedRequest['current_total_pemakaian_roll']) - floatval($validatedRequest['current_kepala_kain']) - floatval($validatedRequest['current_sisa_tidak_bisa']) - floatval($validatedRequest['current_reject']) - floatval($validatedRequest['current_piping']);

            if ($status == 'need extension') {
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['current_id_roll']],
                    [
                        "id_item" => $validatedRequest['current_id_item'],
                        "color" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "qty" => $itemRemain > 0 ? 0 : $itemRemain,
                        "unit" => $itemUnit,
                    ]
                );

                $storeTimeRecordSummaryExt = FormCutInputDetail::create([
                    "group" => $validatedRequest['current_group'],
                    "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                    "id_sambungan" => $storeTimeRecordSummary->id,
                    "status" => "extension",
                ]);

                if ($storeTimeRecordSummaryExt) {
                    return array(
                        "status" => 200,
                        "message" => "alright",
                        "additional" => [
                            FormCutInputDetail::where('id', $storeTimeRecordSummary->id)->first(),
                            FormCutInputDetail::where('id', $storeTimeRecordSummaryExt->id)->first()
                        ],
                    );
                }
            } else {
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['current_id_roll']],
                    [
                        "id_item" => $validatedRequest['current_id_item'],
                        "color" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "qty" => $itemRemain,
                        "unit" => $itemUnit,
                    ]
                );
            }

            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [
                    FormCutInputDetail::where('id', $storeTimeRecordSummary->id)->first(),
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
        $lap = $request->lap;

        $itemQty = ($request["current_unit"] != "KGM" ? floatval($request['current_qty']) : floatval($request['current_qty_real']));
        $itemUnit = ($request["current_unit"] != "KGM" ? "METER" : $request['current_unit']);

        $storeTimeRecordSummary = FormCutInputDetail::selectRaw("form_cut_input_detail.*")->leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->where('form_cut_input.no_meja', $request->no_meja)->where('form_cut_input_detail.status', 'not complete')->updateOrCreate(
            ["no_form_cut_input" => $request->no_form_cut_input],
            [
                "id_roll" => $request->current_id_roll,
                "id_item" => $request->current_id_item,
                "color_act" => $request->color_act,
                "detail_item" => $request->detail_item,
                "group" => $request->current_group,
                "lot" => $request->current_lot,
                "roll" => $request->current_roll,
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
                "total_pemakaian_roll" => $request->current_total_pemakaian_roll,
                "short_roll" => $request->current_short_roll,
                "piping" => $request->current_piping,
                "remark" => $request->current_remark,
                "status" => "not complete",
                "metode" => $request->metode ? $request->metode : "scan",
            ]
        );

        if ($storeTimeRecordSummary) {
            $now = Carbon::now();

            if ($lap > 0) {
                $storeTimeRecordLap = FormCutInputDetailLap::updateOrCreate(
                    ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "lembar_gelaran_ke" => $lap],
                    [
                        "waktu" => $request["time_record"][$lap]
                    ]
                );

                if ($storeTimeRecordLap) {
                    return array(
                        "status" => 200,
                        "message" => "alright",
                        "additional" => [],
                    );
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

    public function storeTimeRecordExtension(Request $request)
    {
        $lap = 1;

        $validatedRequest = $request->validate([
            "status_sambungan" => "required",
            "id_sambungan" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "color_act" => "required",
            "current_id_item" => "required",
            "detail_item" => "required",
            "current_group" => "required",
            "current_roll" => "required",
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
            "current_total_pemakaian_roll" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_remark" => "required",
            "current_sambungan" => "required"
        ]);

        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        $storeTimeRecordSummary = FormCutInputDetail::selectRaw("form_cut_input_detail.*")->leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->where('form_cut_input.no_meja', $validatedRequest['no_meja'])->where('form_cut_input_detail.status', 'extension')->updateOrCreate(
            ['form_cut_input_detail.no_form_cut_input' => $validatedRequest['no_form_cut_input']],
            [
                "id_roll" => $validatedRequest['current_id_roll'],
                "id_item" => $validatedRequest['current_id_item'],
                "color_act" => $validatedRequest['color_act'],
                "detail_item" => $validatedRequest['detail_item'],
                "group" => $validatedRequest['current_group'],
                "lot" => $request['current_lot'],
                "roll" => $validatedRequest['current_roll'],
                "qty" => $itemQty,
                "unit" => $itemUnit,
                "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                "sambungan" => $validatedRequest['current_sambungan'],
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
                "status" => "extension complete",
                "metode" => $request->metode ? $request->metode : "scan",
            ]
        );

        if ($storeTimeRecordSummary) {
            $itemRemain = $itemQty - floatval($validatedRequest['current_total_pemakaian_roll']) - floatval($validatedRequest['current_kepala_kain']) - floatval($validatedRequest['current_sisa_tidak_bisa']) - floatval($validatedRequest['current_reject']) - floatval($validatedRequest['current_piping']);;

            ScannedItem::updateOrCreate(
                ["id_roll" => $validatedRequest['current_id_roll']],
                [
                    "id_item" => $validatedRequest['current_id_item'],
                    "color" => $validatedRequest['color_act'],
                    "detail_item" => $validatedRequest['detail_item'],
                    "lot" => $request['current_lot'],
                    "roll" => $validatedRequest['current_roll'],
                    "qty" => $itemRemain,
                    "unit" => $itemUnit,
                ]
            );

            $now = Carbon::now();

            if ($lap > 0) {
                $storeTimeRecordLap = FormCutInputDetailLap::updateOrCreate(
                    ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "lembar_gelaran_ke" => $lap],
                    [
                        "waktu" => $request["time_record"][$lap]
                    ]
                );

                if ($storeTimeRecordLap) {
                    $storeTimeRecordSummaryNext = FormCutInputDetail::create([
                        "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                        "id_roll" => $validatedRequest['current_id_roll'],
                        "id_item" => $validatedRequest['current_id_item'],
                        "color_act" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "group" => $validatedRequest['current_group'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "qty" => $itemRemain,
                        "unit" => $itemUnit,
                        "sambungan" => 0,
                        "status" => "not complete",
                        "metode" => $request->metode ? $request->metode : "scan",
                    ]);

                    if ($storeTimeRecordSummaryNext) {
                        return array(
                            "status" => 200,
                            "message" => "alright",
                            "additional" => [
                                FormCutInputDetail::where('id', $storeTimeRecordSummary->id)->first(),
                                FormCutInputDetail::where('id', $storeTimeRecordSummaryNext->id)->first(),
                            ],
                        );
                    }
                }
            }

            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [
                    FormCutInputDetail::where('id', $storeTimeRecordSummary->id)->first()
                ],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function checkSpreadingForm($noForm = 0, $noMeja = 0, Request $request)
    {
        $formCutInputDetailData = FormCutInputDetail::selectRaw('
                form_cut_input_detail.*
            ')->leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->where('no_form_cut_input', $noForm)->where('no_meja', $noMeja)->orderBy('form_cut_input_detail.id', 'desc')->first();

        $formCutInputDetailCount = $formCutInputDetailData ? $formCutInputDetailData->count() : 0;

        if ($formCutInputDetailCount > 0) {
            if ($formCutInputDetailData->status == 'extension') {
                $sisaGelaran = FormCutInputDetail::where('id', $formCutInputDetailData->id_sambungan)->first()->sisa_gelaran;

                return array(
                    "count" => $formCutInputDetailCount,
                    "data" => $formCutInputDetailData,
                    "sisaGelaran" => $sisaGelaran
                );
            } else if ($formCutInputDetailData->status == 'not complete') {
                return array(
                    "count" => $formCutInputDetailCount,
                    "data" => $formCutInputDetailData,
                    "sisaGelaran" => 0
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

    public function finishProcess($id = 0, Request $request)
    {
        $formCutInputData = FormCutInput::where("id", $id)->first();
        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "status" => "SELESAI PENGERJAAN",
            "waktu_selesai" => $request->finishTime,
            "cons_act" => $request->consAct,
            "unit_cons_act" => $request->unitConsAct,
            "total_lembar" => $request->totalLembar,
            "operator" => $request->operator,
        ]);

        $notCompleted = FormCutInputDetail::where("no_form_cut_input", $formCutInputData->no_form)->where("status", "not complete")->first();
        if ($notCompleted) {
            FormCutInputDetailLap::where("form_cut_input_detail_id", $notCompleted->id)->delete();
            FormCutInputDetail::where("no_form_cut_input", $formCutInputData->no_form)->where("status", "not complete")->delete();
        }

        return $updateFormCutInput;
    }
}
