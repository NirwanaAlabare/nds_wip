<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\PartDetail;
use App\Models\ModifySizeQty;
use App\Models\MonthCount;
use App\Models\YearSequence;
use App\Models\StockerAdditional;
use App\Models\StockerAdditionalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use QrCode;
use PDF;

class StockerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $formCutInputs = FormCutInput::selectRaw("
                    form_cut_input.id form_cut_id,
                    form_cut_input.id_marker,
                    form_cut_input.no_form,
                    DATE(form_cut_input.waktu_selesai) tanggal_selesai,
                    users.name nama_meja,
                    marker_input.id as marker_id,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.urutan_marker,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    form_cut_input.no_cut,
                    form_cut_input.total_lembar,
                    part_form.kode kode_part_form,
                    part.kode kode_part,
                    GROUP_CONCAT(DISTINCT CONCAT(marker_input_detail.size, '(', marker_input_detail.ratio, ')') SEPARATOR ' / ') marker_details,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ' || ') part_details
                ")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("part", "part.id", "=", "part_form.part_id")->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                whereRaw("part_form.id is not null")->
                groupBy("form_cut_input.id");

            return Datatables::of($formCutInputs)->filter(function ($query) {
                    if (request()->has('dateFrom') && request('dateFrom') != null && request('dateFrom') != "") {
                        $query->whereRaw('DATE(form_cut_input.waktu_selesai) >= "'.request('dateFrom').'"');
                    }

                    if (request()->has('dateTo') && request('dateTo') != null && request('dateTo') != "") {
                        $query->whereRaw('DATE(form_cut_input.waktu_selesai) <= "'.request('dateTo').'"');
                    }
                }, true)->
                filterColumn('id_marker', function ($query, $keyword) {
                    $query->whereRaw("LOWER(form_cut_input.id_marker) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('no_form', function ($query, $keyword) {
                    $query->whereRaw("LOWER(form_cut_input.no_form) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_meja', function ($query, $keyword) {
                    $query->whereRaw("LOWER(users.name) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('act_costing_ws', function ($query, $keyword) {
                    $query->whereRaw("LOWER(marker_input.act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('buyer', function ($query, $keyword) {
                    $query->whereRaw("LOWER(marker_input.buyer) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('style', function ($query, $keyword) {
                    $query->whereRaw("LOWER(marker_input.style) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('color', function ($query, $keyword) {
                    $query->whereRaw("LOWER(marker_input.color) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('panel', function ($query, $keyword) {
                    $query->whereRaw("LOWER(marker_input.panel) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('kode_part_form', function ($query, $keyword) {
                    $query->whereRaw("LOWER(part_form.kode) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('kode_part', function ($query, $keyword) {
                    $query->whereRaw("LOWER(part.kode) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_part', function ($query, $keyword) {
                    $query->whereRaw("LOWER(master_part.nama_part) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('no_cut', function ($query, $keyword) {
                    $query->whereRaw("LOWER(form_cut_input.no_cut) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('total_lembar', function ($query, $keyword) {
                    $query->whereRaw("LOWER(form_cut_input.total_lembar) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('marker_input.act_costing_ws', 'asc')->orderBy('form_cut_input.no_cut', 'asc');
                })->toJson();
        }

        return view("stocker.stocker.stocker", ["page" => "dashboard-stocker",  "subPageGroup" => "proses-stocker", "subPage" => "stocker"]);
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
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function show($formCutId = 0)
    {
        $dataSpreading = FormCutInput::selectRaw("
                part.id part_id,
                part_detail.id part_detail_id,
                form_cut_input.id form_cut_id,
                form_cut_input.no_meja,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                DATE(form_cut_input.waktu_selesai) tgl_form_cut,
                marker_input.id marker_id,
                marker_input.act_costing_ws ws,
                marker_input.buyer,
                marker_input.panel,
                marker_input.color,
                marker_input.style,
                form_cut_input.status,
                users.name nama_meja,
                marker_input.panjang_marker,
                UPPER(marker_input.unit_panjang_marker) unit_panjang_marker,
                marker_input.comma_marker,
                UPPER(marker_input.unit_comma_marker) unit_comma_marker,
                marker_input.lebar_marker,
                UPPER(marker_input.unit_lebar_marker) unit_lebar_marker,
                form_cut_input.qty_ply,
                marker_input.gelar_qty,
                marker_input.po_marker,
                marker_input.urutan_marker,
                marker_input.cons_marker,
                form_cut_input.total_lembar,
                form_cut_input.no_cut,
                UPPER(form_cut_input.shell) shell,
                GROUP_CONCAT(DISTINCT COALESCE(master_size_new.size, marker_input_detail.size) ORDER BY master_size_new.urutan ASC SEPARATOR ', ') sizes,
                GROUP_CONCAT(DISTINCT CONCAT(' ', COALESCE(master_size_new.size, marker_input_detail.size), '(', marker_input_detail.ratio * form_cut_input.total_lembar, ')') ORDER BY master_size_new.urutan ASC) marker_details,
                GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) SEPARATOR ', ') part
            ")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.id", $formCutId)->
            groupBy("form_cut_input.id")->
            first();

        $dataPartDetail = PartDetail::selectRaw("
                part_detail.id,
                master_part.nama_part,
                master_part.bag,
                COALESCE(master_secondary.tujuan, '-') tujuan,
                COALESCE(master_secondary.proses, '-') proses
            ")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("part", "part.id", "part_detail.part_id")->
            leftJoin("part_form", "part_form.part_id", "part.id")->
            leftJoin("form_cut_input", "form_cut_input.id", "part_form.form_id")->
            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
            where("form_cut_input.id", $formCutId)->
            groupBy("master_part.id")->
            get();

        $dataRatio = MarkerDetail::selectRaw("
                marker_input_detail.id marker_detail_id,
                marker_input_detail.so_det_id,
                COALESCE(master_sb_ws.size, marker_input_detail.size) size,
                COALESCE((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END), marker_input_detail.size) size_dest,
                marker_input_detail.ratio
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            where("marker_input.id", $dataSpreading->marker_id)->
            // where("marker_input_detail.ratio", ">", "0")->
            orderBy("marker_input_detail.id", "asc")->
            groupBy("marker_input_detail.id")->
            get();

        $dataStocker = MarkerDetail::selectRaw("
                MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                marker_input.color,
                marker_input_detail.so_det_id,
                COALESCE(stocker_input.ratio, marker_input_detail.ratio) ratio,
                MAX(form_cut_input.no_form) no_form,
                form_cut_input.no_cut,
                MAX(stocker_input.id) stocker_id,
                MAX(stocker_input.shade) shade,
                MAX(stocker_input.group_stocker) group_stocker,
                MAX(stocker_input.qty_ply) qty_ply,
                MAX(CAST(stocker_input.range_akhir as UNSIGNED)) range_akhir,
                modify_size_qty.difference_qty
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("stocker_input", function ($join) {
                $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            leftJoin("modify_size_qty", function ($join) {
                $join->on("modify_size_qty.no_form", "=", "form_cut_input.no_form");
                $join->on("modify_size_qty.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            where("part_form.part_id", $dataSpreading->part_id)->
            // where("marker_input_detail.ratio", ">", "0")->
            groupBy("form_cut_input.no_form", "form_cut_input.no_cut", "marker_input_detail.so_det_id")->
            orderBy("form_cut_input.no_cut", "desc")->
            orderBy("form_cut_input.no_form", "desc")->
            get();

        $dataNumbering = MarkerDetail::selectRaw("
                marker_input.color,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                MAX(form_cut_input.no_form) no_form,
                form_cut_input.no_cut,
                MAX(number.numbering_id) numbering_id,
                MAX(number.no_cut_size) no_cut_size,
                MAX(number.range_akhir) range_akhir,
                modify_size_qty.difference_qty
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin(
                DB::raw(
                    "(
                        SELECT
                            stocker_numbering.form_cut_id,
                            stocker_numbering.so_det_id,
                            MAX( stocker_numbering.no_cut_size ) no_cut_size,
                            MAX( stocker_numbering.id ) numbering_id,
                            MAX( stocker_numbering.number ) range_akhir
                        FROM
                            form_cut_input
                            INNER JOIN marker_input ON form_cut_input.id_marker = marker_input.kode
                            INNER JOIN `stocker_numbering` ON form_cut_input.id = stocker_numbering.form_cut_id
                        WHERE
                            `marker_input`.`act_costing_ws` = '".$dataSpreading->ws."'
                            AND `marker_input`.`color` = '".$dataSpreading->color."'
                            AND `marker_input`.`panel` = '".$dataSpreading->panel."'
                            AND ( stocker_numbering.cancel IS NULL OR stocker_numbering.cancel != 'Y' )
                            AND `form_cut_input`.`no_cut` <= ".$dataSpreading->no_cut."
                        GROUP BY
                            stocker_numbering.form_cut_id,
                            stocker_numbering.so_det_id
                    ) number"
                ), function ($join) {
                    $join->on("number.form_cut_id", "=", "form_cut_input.id");
                    $join->on("number.so_det_id", "=", "marker_input_detail.so_det_id");
                }
            )->
            leftJoin("modify_size_qty", function ($join) {
                $join->on("modify_size_qty.no_form", "=", "form_cut_input.no_form");
                $join->on("modify_size_qty.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            where("part_form.part_id", $dataSpreading->part_id)->
            // where("marker_input_detail.ratio", ">", "0")->
            groupBy("form_cut_input.no_form", "form_cut_input.no_cut", "marker_input_detail.so_det_id")->
            orderBy("form_cut_input.no_cut", "desc")->
            orderBy("form_cut_input.no_form", "desc")->
            get();

        $modifySizeQty = ModifySizeQty::where("no_form", $dataSpreading->no_form)->get();

        $dataAdditional = DB::table("stocker_ws_additional")->where("no_form", $dataSpreading->no_form)->first();

            // dd($dataAdditional);

        $dataRatioAdditional = DB::table("stocker_ws_additional_detail")->selectRaw("
            stocker_ws_additional_detail.id additional_detail_id,
            stocker_ws_additional_detail.so_det_id,
            COALESCE(master_sb_ws.size, stocker_ws_additional_detail.size) size,
            COALESCE((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END), stocker_ws_additional_detail.size) size_dest,
            stocker_ws_additional_detail.ratio
        ")->
        leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_ws_additional_detail.so_det_id")->
        leftJoin("stocker_ws_additional", "stocker_ws_additional.id", "=", "stocker_ws_additional_detail.stocker_additional_id")->
        leftJoin("form_cut_input", "form_cut_input.no_form", "=", "stocker_ws_additional.no_form")->
        where("stocker_ws_additional.id", ($dataAdditional ? $dataAdditional->id : ''))->
        // where("marker_input_detail.ratio", ">", "0")->
        orderBy("stocker_ws_additional_detail.id", "asc")->
        groupBy("stocker_ws_additional_detail.id")->
        get();

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view("stocker.stocker.stocker-detail", ["dataSpreading" => $dataSpreading, "dataPartDetail" => $dataPartDetail, "dataRatio" => $dataRatio, "dataStocker" => $dataStocker, "dataNumbering" => $dataNumbering, "modifySizeQty" => $modifySizeQty, "dataAdditional" => $dataAdditional, "dataRatioAdditional" => $dataRatioAdditional, "orders" => $orders, "page" => "dashboard-stocker", "subPageGroup" => "proses-stocker", "subPage" => "stocker"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function edit(Stocker $stocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stocker $stocker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }

    public function printStocker(Request $request, $index)
    {
        $formData = FormCutInput::where("id", $request['form_cut_id'])->first();

        $modifySizeQty = ModifySizeQty::where("no_form", $formData->no_form)->where("so_det_id", $request['so_det_id'][$index])->first();

        $stockerCount = Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first() ? str_replace("STK-", "", Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first()->id_qr_stocker) + 1 : 1;

        $rangeAwal = $request['range_awal'][$index];
        $rangeAkhir = $request['range_akhir'][$index];

        $cumRangeAwal = $rangeAwal;
        $cumRangeAkhir = $rangeAwal - 1;

        $ratio = $request['ratio'][$index];
        if ($ratio < 1 && $modifySizeQty) {
            $ratio += 1;
        }

        $storeItemArr = [];
        for ($i = 0; $i < $ratio; $i++) {
            $checkStocker = Stocker::select("id_qr_stocker", "qty_ply", "range_awal", "range_akhir")->whereRaw("
                part_detail_id = '" . $request['part_detail_id'][$index] . "' AND
                form_cut_id = '" . $request['form_cut_id'] . "' AND
                so_det_id = '" . $request['so_det_id'][$index] . "' AND
                color = '" . $request['color'] . "' AND
                panel = '" . $request['panel'] . "' AND
                shade = '" . $request['group'][$index] . "' AND
                " . ($request['group_stocker'][$index] && $request['group_stocker'][$index] != "" ? "group_stocker = '" . $request['group_stocker'][$index] . "' AND" : "") . "
                ratio = " . ($i + 1) . "
            ")->first();

            $ratio = $i + 1;
            $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $i);
            $cumRangeAwal = $cumRangeAkhir + 1;
            $cumRangeAkhir = $cumRangeAkhir + ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]);

            if (!$checkStocker) {
                array_push($storeItemArr, [
                    'id_qr_stocker' => $stockerId,
                    'act_costing_ws' => $request["no_ws"],
                    'part_detail_id' => $request['part_detail_id'][$index],
                    'form_cut_id' => $request['form_cut_id'],
                    'so_det_id' => $request['so_det_id'][$index],
                    'color' => $request['color'],
                    'panel' => $request['panel'],
                    'shade' => $request['group'][$index],
                    'group_stocker' => $request['group_stocker'][$index],
                    'ratio' => $i + 1,
                    'size' => $request["size"][$index],
                    'qty_ply' => ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]),
                    'qty_ply_mod' => (($request['group_stocker'][$index] == min($request['group_stocker'])) && (($i == ($request['ratio'][$index] - 1) && $modifySizeQty) || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]) + $modifySizeQty->difference_qty : null),
                    'qty_cut' => $request['qty_cut'][$index],
                    'notes' => $request['note'],
                    'range_awal' => $cumRangeAwal,
                    'range_akhir' => (($request['group_stocker'][$index] == min($request['group_stocker'])) && (($i == ($request['ratio'][$index] - 1) && $modifySizeQty)  || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                    'created_by' => Auth::user()->id,
                    'created_by_username' => Auth::user()->username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            } else if ($checkStocker && $checkStocker->qty_ply != ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index])) {
                $checkStocker->qty_ply = ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]);
                $checkStocker->qty_ply_mod = (($request['group_stocker'][$index] == min($request['group_stocker'])) && (($i == ($request['ratio'][$index] - 1) && $modifySizeQty) || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]) + $modifySizeQty->difference_qty : null);
                $checkStocker->range_awal = $cumRangeAwal;
                $checkStocker->range_akhir = (($request['group_stocker'][$index] == min($request['group_stocker'])) && (($i == ($request['ratio'][$index] - 1) && $modifySizeQty)  || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir);
                $checkStocker->save();
            }
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, stocker_input.size) size,
                stocker_input.range_awal,
                stocker_input.range_akhir,
                stocker_input.id_qr_stocker,
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
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            where("part_detail.id", $request['part_detail_id'][$index])->
            where("form_cut_input.id", $request['form_cut_id'])->
            where("marker_input_detail.so_det_id", $request['so_det_id'][$index])->
            where("stocker_input.so_det_id", $request['so_det_id'][$index])->
            where("stocker_input.shade", $request['group'][$index])->
            // where("stocker_input.qty_ply", $request['qty_ply_group'][$index])->
            where("stocker_input.group_stocker", $request['group_stocker'][$index])->
            groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $customPaper = array(0, 0, 300, 250);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'STOCKER_'.$request["no_ws"]."_".$request['color']."_".$request['panel']."_".$request['group'][$index]."_".$request["size"][$index] . '.pdf';
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function printStockerAllSize(Request $request, $partDetailId = 0)
    {
        $formData = FormCutInput::where("id", $request['form_cut_id'])->first();

        $storeItemArr = [];
        for ($i = 0; $i < count($request['part_detail_id']); $i++) {
            if ($request['part_detail_id'][$i] == $partDetailId) {
                $modifySizeQty = ModifySizeQty::where("no_form", $formData->no_form)->where("so_det_id", $request['so_det_id'][$i])->first();

                $stockerCount = Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first() ? str_replace("STK-", "", Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first()->id_qr_stocker) + 1 : 1;

                $rangeAwal = $request['range_awal'][$i];
                $rangeAkhir = $request['range_akhir'][$i];

                $cumRangeAwal = $rangeAwal;
                $cumRangeAkhir = $rangeAwal - 1;

                $ratio = $request['ratio'][$i];
                if ($ratio < 1 && $modifySizeQty) {
                    $ratio += 1;
                }

                for ($j = 0; $j < $ratio; $j++) {
                    $checkStocker = Stocker::select("id_qr_stocker", "qty_ply", "range_awal", "range_akhir", "notes")->whereRaw("
                        part_detail_id = '" . $request['part_detail_id'][$i] . "' AND
                        form_cut_id = '" . $request['form_cut_id'] . "' AND
                        so_det_id = '" . $request['so_det_id'][$i] . "' AND
                        color = '" . $request['color'] . "' AND
                        panel = '" . $request['panel'] . "' AND
                        shade = '" . $request['group'][$i] . "' AND
                        " . ( $request['group_stocker'][$i] && $request['group_stocker'][$i] != "" ? "group_stocker = '" . $request['group_stocker'][$i] . "' AND" : "" ) . "
                        ratio = " . ($j + 1) . "
                    ")->first();

                    $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $j);
                    $cumRangeAwal = $cumRangeAkhir + 1;
                    $cumRangeAkhir = $cumRangeAkhir + ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i]);

                    if (!$checkStocker) {
                        array_push($storeItemArr, [
                            'id_qr_stocker' => $stockerId,
                            'act_costing_ws' => $request["no_ws"],
                            'part_detail_id' => $request['part_detail_id'][$i],
                            'form_cut_id' => $request['form_cut_id'],
                            'so_det_id' => $request['so_det_id'][$i],
                            'color' => $request['color'],
                            'panel' => $request['panel'],
                            'shade' => $request['group'][$i],
                            'group_stocker' => $request['group_stocker'][$i],
                            'ratio' => ($j + 1),
                            'size' => $request["size"][$i],
                            'qty_ply' => ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i]),
                            'qty_ply_mod' => (($request['group_stocker'][$i] == min($request['group_stocker'])) && (($j == ($request['ratio'][$i] - 1) && $modifySizeQty) || ($request['ratio'][$i] < 1 && $modifySizeQty)) ? ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i]) + $modifySizeQty->difference_qty : null),
                            'qty_cut' => $request['qty_cut'][$i],
                            'notes' => $request['note'],
                            'range_awal' => $cumRangeAwal,
                            'range_akhir' => (($request['group_stocker'][$i] == min($request['group_stocker'])) && (($j == ($request['ratio'][$i] - 1) && $modifySizeQty) || ($request['ratio'][$i] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                            'created_by' => Auth::user()->id,
                            'created_by_username' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    } else if ($checkStocker && $checkStocker->qty_ply != ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i])) {
                        $checkStocker->qty_ply = ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i]);
                        $checkStocker->qty_ply_mod = (($request['group_stocker'][$i] == min($request['group_stocker'])) && (($j == ($request['ratio'][$i] - 1) && $modifySizeQty) || ($request['ratio'][$i] < 1 && $modifySizeQty)) ? ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i]) + $modifySizeQty->difference_qty : null);
                        $checkStocker->range_awal = $cumRangeAwal;
                        $checkStocker->range_akhir = (($request['group_stocker'][$i] == min($request['group_stocker'])) && (($j == ($request['ratio'][$i] - 1) && $modifySizeQty) || ($request['ratio'][$i] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir);
                        $checkStocker->save();
                    } else if ($checkStocker && $checkStocker->notes != $request['note']) {
                        $checkStocker->notes = $request['note'];
                        $checkStocker->save();
                    }
                }
            }
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, stocker_input.size) size,
                stocker_input.range_awal,
                stocker_input.range_akhir,
                stocker_input.id_qr_stocker,
                marker_input.act_costing_ws,
                marker_input.buyer,
                marker_input.style,
                marker_input.color,
                stocker_input.shade,
                stocker_input.group_stocker,
                stocker_input.notes,
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
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            where("part_detail.id", $partDetailId)->
            where("form_cut_input.id", $request['form_cut_id'])->
            groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.shade", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $customPaper = array(0, 0, 300, 250);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'stocker-' . $request['form_cut_id'] . '-' . $partDetailId . '.pdf';
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function printStockerChecked(Request $request)
    {
        ini_set('max_execution_time', 36000);

        $formData = FormCutInput::where("id", $request['form_cut_id'])->first();

        $stockerCount = Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first() ? str_replace("STK-", "", Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first()->id_qr_stocker) + 1 : 1;

        $partDetail = collect($request['part_detail_id']);

        $partDetailKeys = $partDetail->intersect($request['generate_stocker'])->keys();

        $i = 0;
        $storeItemArr = [];
        foreach ($partDetailKeys as $index) {
            $modifySizeQty = ModifySizeQty::where("no_form", $formData->no_form)->where("so_det_id", $request['so_det_id'][$index])->first();

            $rangeAwal = $request['range_awal'][$index];
            $rangeAkhir = $request['range_akhir'][$index];

            $cumRangeAwal = $rangeAwal;
            $cumRangeAkhir = $rangeAwal - 1;

            $ratio = $request['ratio'][$index];
            if ($ratio < 1 && $modifySizeQty) {
                $ratio += 1;
            }

            for ($j = 0; $j < $ratio; $j++) {
                $checkStocker = Stocker::whereRaw("
                    part_detail_id = '" . $request['part_detail_id'][$index] . "' AND
                    form_cut_id = '" . $request['form_cut_id'] . "' AND
                    so_det_id = '" . $request['so_det_id'][$index] . "' AND
                    color = '" . $request['color'] . "' AND
                    panel = '" . $request['panel'] . "' AND
                    shade = '" . $request['group'][$index] . "' AND
                    " . ( $request['group_stocker'][$index] && $request['group_stocker'][$index] != "" ? "group_stocker = '" . $request['group_stocker'][$index] . "' AND" : "" ) . "
                    ratio = " . ($j + 1) . "
                ")->first();

                $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $j + $i + 1);
                $cumRangeAwal = $cumRangeAkhir + 1;
                $cumRangeAkhir = $cumRangeAkhir + ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]);

                if (!$checkStocker) {
                    if ($request['qty_cut'][$index] > 0 || $modifySizeQty) {
                        array_push($storeItemArr, [
                            'id_qr_stocker' => $stockerId,
                            'act_costing_ws' => $request["no_ws"],
                            'part_detail_id' => $request['part_detail_id'][$index],
                            'form_cut_id' => $request['form_cut_id'],
                            'so_det_id' => $request['so_det_id'][$index],
                            'color' => $request['color'],
                            'panel' => $request['panel'],
                            'shade' => $request['group'][$index],
                            'group_stocker' => $request['group_stocker'][$index],
                            'ratio' => ($j + 1),
                            'size' => $request["size"][$index],
                            'qty_ply' => ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]),
                            'qty_ply_mod' => ($request['group_stocker'][$index] == (min($request['group_stocker'])) && (($j == ($request['ratio'][$index] - 1) && $modifySizeQty) || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]) + $modifySizeQty->difference_qty : null),
                            'qty_cut' => $request['qty_cut'][$index],
                            'notes' => $request['note'],
                            'range_awal' => $cumRangeAwal,
                            'range_akhir' => ($request['group_stocker'][$index] == (min($request['group_stocker'])) && (($j == ($request['ratio'][$index] - 1) && $modifySizeQty) || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                            'created_by' => Auth::user()->id,
                            'created_by_username' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                } else if ($checkStocker && ($checkStocker->qty_ply != ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]) || $checkStocker->range_awal != $cumRangeAwal || $checkStocker->range_akhir != ($request['group_stocker'][$index] == (min($request['group_stocker'])) && (($j == ($request['ratio'][$index] - 1) && $modifySizeQty) || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir) )) {
                    $checkStocker->qty_ply = ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]);
                    $checkStocker->qty_ply_mod = (($request['group_stocker'][$index] == min($request['group_stocker'])) && (($j == ($request['ratio'][$index] - 1) && $modifySizeQty) || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? ($request['ratio'][$index] < 1 ? 0 : $request['qty_ply_group'][$index]) + $modifySizeQty->difference_qty : null);
                    $checkStocker->range_awal = $cumRangeAwal;
                    $checkStocker->range_akhir = (($request['group_stocker'][$index] == min($request['group_stocker'])) && (($j == ($request['ratio'][$index] - 1) && $modifySizeQty)  || ($request['ratio'][$index] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir);
                    $checkStocker->save();
                }
            }

            $i += $j;
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, stocker_input.size) size,
                stocker_input.range_awal,
                stocker_input.range_akhir,
                MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                marker_input.act_costing_ws,
                marker_input.buyer,
                marker_input.style,
                marker_input.color,
                stocker_input.shade,
                stocker_input.group_stocker,
                stocker_input.notes,
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
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            whereIn("part_detail.id", $request['generate_stocker'])->
            where("form_cut_input.id", $request['form_cut_id'])->
            groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.shade", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $customPaper = array(0, 0, 300, 250);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'stocker-' . $request['form_cut_id'] . '-' . implode($request['generate_stocker']) . '.pdf';
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function printStockerAllSizeAdd(Request $request)
    {
        $formData = FormCutInput::where("id", $request['form_cut_id'])->first();

        $stockerCount = Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first() ? str_replace("STK-", "", Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first()->id_qr_stocker) + 1 : 1;

        $storeItemArr = [];
        for ($i = 0; $i < count($request['ratio_add']); $i++) {
            $modifySizeQty = ModifySizeQty::where("no_form", $formData->no_form)->where("so_det_id", $request['so_det_id'][$i])->first();

            $rangeAwal = $request['range_awal_add'][$i];
            $rangeAkhir = $request['range_akhir_add'][$i];

            $cumRangeAwal = $rangeAwal;
            $cumRangeAkhir = $rangeAwal - 1;

            $ratio = $request['ratio_add'][$i];
            if ($ratio < 1 && $modifySizeQty) {
                $ratio += 1;
            }

            for ($j = 0; $j < $ratio; $j++) {
                $checkStocker = Stocker::select("id_qr_stocker", "qty_ply", "range_awal", "range_akhir", "notes")->whereRaw("
                    form_cut_id = '" . $request['form_cut_id'] . "' AND
                    so_det_id = '" . $request['so_det_id_add'][$i] . "' AND
                    color = '" . $request['color_add'] . "' AND
                    panel = '" . $request['panel_add'] . "' AND
                    shade = '" . $request['group_add'][$i] . "' AND
                    " . ( $request['group_stocker_add'][$i] && $request['group_stocker_add'][$i] != "" ? "group_stocker = '" . $request['group_stocker_add'][$i] . "' AND" : "" ) . "
                    ratio = " . ($j + 1) . "
                ")->first();

                $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $i + $j);
                $cumRangeAwal = $cumRangeAkhir + 1;
                $cumRangeAkhir = $cumRangeAkhir + ($request['ratio_add'][$i] < 1 ? 0 : $request['qty_ply_group_add'][$i]);

                if (!$checkStocker) {
                    array_push($storeItemArr, [
                        'id_qr_stocker' => $stockerId,
                        'act_costing_ws' => $request["no_ws_add"],
                        'form_cut_id' => $request['form_cut_id'],
                        'so_det_id' => $request['so_det_id_add'][$i],
                        'color' => $request['color_add'],
                        'panel' => $request['panel_add'],
                        'shade' => $request['group'][$i],
                        'group_stocker' => $request['group_stocker_add'][$i],
                        'ratio' => ($j + 1),
                        'size' => $request["size_add"][$i],
                        'qty_ply' => ($request['ratio_add'][$i] < 1 ? 0 : $request['qty_ply_group_add'][$i]),
                        'qty_ply_mod' => (($request['group_stocker_add'][$i] == min($request['group_stocker_add'])) && (($j == ($request['ratio_add'][$i] - 1) && $modifySizeQty) || ($request['ratio_add'][$i] < 1 && $modifySizeQty)) ? ($request['ratio_add'][$i] < 1 ? 0 : $request['qty_ply_group_add'][$i]) + $modifySizeQty->difference_qty : null),
                        'qty_cut' => $request['qty_cut_add'][$i],
                        'notes' => "ADDITIONAL ".$request['note'],
                        'range_awal' => $cumRangeAwal,
                        'range_akhir' => (($request['group_stocker_add'][$i] == min($request['group_stocker_add'])) && (($j == ($request['ratio_add'][$i] - 1) && $modifySizeQty) || ($request['ratio_add'][$i] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                        'created_by' => Auth::user()->id,
                        'created_by_username' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                } else if ($checkStocker && $checkStocker->qty_ply != ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i])) {
                    $checkStocker->qty_ply = ($request['ratio'][$i] < 1 ? 0 : $request['qty_ply_group'][$i]);
                    $checkStocker->qty_ply_mod = (($request['group_stocker_add'][$i] == min($request['group_stocker_add'])) && (($j == ($request['ratio_add'][$i] - 1) && $modifySizeQty) || ($request['ratio_add'][$i] < 1 && $modifySizeQty)) ? ($request['ratio_add'][$i] < 1 ? 0 : $request['qty_ply_group_add'][$i]) + $modifySizeQty->difference_qty : null);
                    $checkStocker->range_awal = $cumRangeAwal;
                    $checkStocker->range_akhir = (($request['group_stocker_add'][$i] == min($request['group_stocker_add'])) && (($j == ($request['ratio_add'][$i] - 1) && $modifySizeQty) || ($request['ratio_add'][$i] < 1 && $modifySizeQty)) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir);
                    $checkStocker->save();
                } else if ($checkStocker && $checkStocker->notes != $request['note']) {
                    $checkStocker->notes = "ADDITIONAL ".$request['note'];
                    $checkStocker->save();
                }
            }
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, stocker_input.size) size,
                stocker_input.range_awal,
                stocker_input.range_akhir,
                stocker_input.id_qr_stocker,
                stocker_ws_additional.act_costing_ws,
                stocker_ws_additional.buyer,
                stocker_ws_additional.style,
                stocker_ws_additional.color,
                stocker_input.shade,
                stocker_input.group_stocker,
                stocker_input.notes,
                form_cut_input.no_cut,
                master_part.nama_part part,
                master_sb_ws.dest
            ")->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("part", "part.id", "=", "part_detail.part_id")->
            leftJoin("part_form", "part_form.part_id", "=", "part.id")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("stocker_ws_additional", "stocker_ws_additional.no_form", "=", "form_cut_input.no_form")->
            leftJoin("stocker_ws_additional_detail", "stocker_ws_additional_detail.stocker_additional_id", "=", "stocker_ws_additional.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "stocker_ws_additional_detail.size")->
            leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            where("stocker_ws_additional.act_costing_ws", $request['no_ws_add'])->
            where("stocker_ws_additional.style", $request['style_add'])->
            where("stocker_ws_additional.color", $request['color_add'])->
            where("form_cut_input.id", $request['form_cut_id'])->
            groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.shade", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $customPaper = array(0, 0, 300, 250);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'stocker-' . $request['form_cut_id'] .'.pdf';
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function submitStockerAdd(Request $request) {
        $totalQty = 0;

        $validatedRequest = $request->validate([
            "add_no_form" => "required",
            "add_ws" => "required",
            "add_ws_ws" => "required",
            "add_buyer" => "required",
            "add_style" => "required",
            "add_color" => "required",
            "add_panel" => "required",
        ]);

        foreach ($request["add_cut_qty"] as $qty) {
            $totalQty += $qty;
        }

        if ($totalQty > 0) {
            $stockerAddId = StockerAdditional::create([
                'no_form' => $validatedRequest['add_no_form'],
                'act_costing_id' => $validatedRequest['add_ws'],
                'act_costing_ws' => $validatedRequest['add_ws_ws'],
                'buyer' => $validatedRequest['add_buyer'],
                'style' => $validatedRequest['add_style'],
                'color' => $validatedRequest['add_color'],
                'panel' => $validatedRequest['add_panel'],
                'cancel' => 'N',
            ]);

            $timestamp = Carbon::now();
            $addId = $stockerAddId->id;
            $stockerAddDetailData = [];
            for ($i = 0; $i < intval($request['jumlah_so_det']); $i++) {
                array_push($stockerAddDetailData, [
                    "stocker_additional_id" => $addId,
                    "so_det_id" => $request["add_so_det_id"][$i],
                    "size" => $request["add_size"][$i],
                    "ratio" => $request["add_ratio"][$i],
                    "cut_qty" => $request["add_cut_qty"][$i],
                    "cancel" => 'N',
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ]);
            }

            $stockerAddDetailStore = StockerAdditionalDetail::insert($stockerAddDetailData);

            return array(
                "status" => 200,
                "message" => $validatedRequest['add_ws_ws'],
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Total Cut Qty Kosong",
            "additional" => [],
        );
    }

    public function printNumbering(Request $request, $index)
    {
        $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
        $stockerDetailMonth = StockerDetail::select('year_month_number')->where('year_month', Carbon::now()->format('Y-m'))->orderBy('year_month_number', 'desc');
        $stockerDetailMonthCount = $stockerDetailMonth->first() ? $stockerDetailMonth->first()->year_month_number + 1 : 1;

        $rangeAwal = $request['range_awal'][$index];
        $rangeAkhir = $request['range_akhir'][$index] + 1;

        $now = Carbon::now();
        $noCutSize = str_replace(" ", "", $request["size"][$index]) . "" . sprintf('%02s', $request['no_cut']);
        $detailItemArr = [];
        $storeDetailItemArr = [];

        $n = 0;
        for ($i = $rangeAwal; $i < $rangeAkhir; $i++) {
            $checkStockerDetailData = StockerDetail::where('form_cut_id', $request["form_cut_id"])->where('act_costing_ws', $request["no_ws"])->where('color', $request['color'])->where('panel', $request['panel'])->where('so_det_id', $request['so_det_id'][$index])->where('no_cut_size', $noCutSize . sprintf('%04s', ($i)))->orderBy("updated_at", "desc")->first();

            if (!$checkStockerDetailData) {
                array_push($storeDetailItemArr, [
                    'kode' => "WIP-" . ($stockerDetailCount + $n),
                    'form_cut_id' => $request['form_cut_id'],
                    'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
                    'so_det_id' => $request['so_det_id'][$index],
                    'act_costing_ws' => $request["no_ws"],
                    'color' => $request['color'],
                    'size' => $request['size'][$index],
                    'shade' => $request['shade'],
                    'panel' => $request['panel'],
                    'number' => $i,
                    'year_month' => $now->format('Y-m'),
                    'year_month_number' => ($stockerDetailMonthCount + $n),
                    'created_by' => Auth::user()->id,
                    'created_by_username' => Auth::user()->username,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // array_push($detailItemArr, [
            //     'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-" . ($stockerDetailCount + $n),
            //     'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
            //     'size' => $request['size'][$index],
            //     'so_det_id' => $request['so_det_id'][$index],
            //     'created_at' => $now,
            //     'updated_at' => $now
            // ]);

            array_push($detailItemArr, [
                'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-" . ($stockerDetailCount + $n),
                'year_month' => $checkStockerDetailData ? $checkStockerDetailData->year_month : $now->format('Y-m'),
                'year_month_number' => $checkStockerDetailData ? $checkStockerDetailData->year_month_number : ($stockerDetailMonthCount + $n)
            ]);

            $n++;
        }

        if (count($storeDetailItemArr) > 0) {
            $storeDetailItem = StockerDetail::insert($storeDetailItemArr);
        }

        // generate pdf
        // $customPaper = array(0, 0, 56.70, 33.39);
        // $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering', ["ws" => $request["no_ws"], "color" => $request["color"], "no_cut" => $request["no_cut"], "dataNumbering" => $detailItemArr])->setPaper($customPaper);

        $customPaper = array(0, 0, 35.35, 110.90);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearmonth', ["ws" => $request["no_ws"], "color" => $request["color"], "no_cut" => $request["no_cut"], "dataNumbering" => $detailItemArr])->setPaper($customPaper);

        $path = public_path('pdf/');
        $fileName = str_replace("/", "-", ($request["no_ws"]. '-' . $request["color"] . '-' . $request["no_cut"] . '-Numbering.pdf'));
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function printNumberingChecked(Request $request)
    {
        ini_set('max_execution_time', 36000);

        $type = $request->type ? $request->type : 'numbering';

        $detailItemArr = [];
        $detailItemMonthArr = [];

        $storeDetailItemArr = [];
        $updateDetailItemIds = [];

        $checkedSize = collect($request['generate_num']);

        $checkedSizeKeys = $checkedSize->keys();

        $stockerDetail = StockerDetail::orderBy("id", "desc");
        $stockerDetailCount = $stockerDetail->first() ? str_replace("WIP-", "", $stockerDetail->first()->kode) + 1 : 1;
        $stockerDetailMonth = StockerDetail::select('year_month_number')->where('year_month', Carbon::now()->format('Y-m'))->orderBy('year_month_number', 'desc');
        $stockerDetailMonthCount = $stockerDetailMonth->first() ? $stockerDetailMonth->first()->year_month_number + 1 : 1;

        $n = 0;
        foreach ($checkedSizeKeys as $index) {
            $rangeAwal = $request['range_awal'][$index];
            $rangeAkhir = $request['range_akhir'][$index] + 1;

            $now = Carbon::now();
            $noCutSize = str_replace(" ", "", $request["size"][$index]) . "" . sprintf('%02s', $request['no_cut']);

            for ($i = $rangeAwal; $i < $rangeAkhir; $i++) {
                $checkStockerDetailData = StockerDetail::where('form_cut_id', $request['form_cut_id'])->where('act_costing_ws', $request["no_ws"])->where('color', $request['color'])->where('panel', $request['panel'])->where('so_det_id', $request['so_det_id'][$index])->where('no_cut_size', $noCutSize . sprintf('%04s', ($i))    )->orderBy("updated_at", "desc")->first();

                if (!$checkStockerDetailData) {
                    array_push($storeDetailItemArr, [
                        'kode' => "WIP-" . ($stockerDetailCount + $n),
                        'form_cut_id' => $request['form_cut_id'],
                        'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
                        'so_det_id' => $request['so_det_id'][$index],
                        'act_costing_ws' => $request["no_ws"],
                        'color' => $request['color'],
                        'size' => $request['size'][$index],
                        'shade' => $request['shade'],
                        'panel' => $request['panel'],
                        'number' => $i,
                        'year_month' => $now->format('Y-m'),
                        'year_month_number' => ($stockerDetailMonthCount + $n),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else if ($checkStockerDetailData && $checkStockerDetailData->cancel == "Y") {
                    array_push($updateDetailItemIds, $checkStockerDetailData->id);
                }

                if ($type == "numbering") {
                    array_push($detailItemArr, [
                        'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-" . ($stockerDetailCount + $n),
                        'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
                        'size' => $request['size'][$index],
                        'so_det_id' => $request['so_det_id'][$index],
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
                }

                if ($type == "month_count") {
                    array_push($detailItemMonthArr , [
                        'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-" . ($stockerDetailCount + $n),
                        'year_month' => $checkStockerDetailData ? $checkStockerDetailData->year_month : $now->format('Y-m'),
                        'year_month_number' => $checkStockerDetailData ? $checkStockerDetailData->year_month_number : ($stockerDetailMonthCount + $n)
                    ]);
                }

                $n++;
            }
        }

        if (count($storeDetailItemArr) > 0) {
            $storeDetailItem = StockerDetail::insert($storeDetailItemArr);
        }

        if (count($updateDetailItemIds) > 0) {
            $updateDetailItem = StockerDetail::whereIn("id", $updateDetailItemIds)->
                update([
                    "cancel" => "N"
                ]);
        }

        // generate pdf
        if ($type == "numbering") {
            $customPaper = array(0, 0, 56.70, 33.39);
            $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering', ["ws" => $request["no_ws"], "color" => $request["color"], "no_cut" => $request["no_cut"], "dataNumbering" => $detailItemArr])->setPaper($customPaper);
        }

        // if ($type == "month_count") {
        //     $customPaper = array(0, 0, 35.35, 110.90);
        //     $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearmonth', ["ws" => $request["no_ws"], "color" => $request["color"], "no_cut" => $request["no_cut"], "dataNumbering" => $detailItemArr])->setPaper($customPaper);
        // }

        $path = public_path('pdf/');
        $fileName = str_replace("/", "-", ($request["no_ws"]. '-' . $request["color"] . '-' . $request["no_cut"] . '-Numbering.pdf'));
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function fullGenerateNumbering(Request $request) {
        ini_set('max_execution_time', 360000);

        $formCutInputs = FormCutInput::selectRaw("
                marker_input.color,
                form_cut_input.id as id_form,
                form_cut_input.no_form as no_form
            ")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            whereRaw("part_form.id is not null")->
            where("part.id", $request->id)->
            groupBy("form_cut_input.id")->
            orderBy("marker_input.color", "asc")->
            orderBy("form_cut_input.no_cut", "asc")->
            get();

        $stockerDetail = StockerDetail::orderBy("id", "desc");
        $stockerDetailCount = $stockerDetail->first() ? str_replace("WIP-", "", $stockerDetail->first()->kode) + 1 : 1;
        $stockerDetailMonth = StockerDetail::select('year_month_number')->where('year_month', Carbon::now()->format('Y-m'))->orderBy('year_month_number', 'desc');
        $stockerDetailMonthCount = $stockerDetailMonth->first() ? $stockerDetailMonth->first()->year_month_number + 1 : 1;

        $n = 0;
        foreach ($formCutInputs as $formCut) {
            $dataSpreading = FormCutInput::selectRaw("
                    part_detail.id part_detail_id,
                    form_cut_input.id form_cut_id,
                    form_cut_input.no_meja,
                    form_cut_input.id_marker,
                    form_cut_input.no_form,
                    DATE(form_cut_input.waktu_selesai) tgl_form_cut,
                    marker_input.id marker_id,
                    marker_input.act_costing_ws ws,
                    marker_input.buyer,
                    marker_input.panel,
                    marker_input.color,
                    marker_input.style,
                    form_cut_input.status,
                    users.name nama_meja,
                    marker_input.panjang_marker,
                    UPPER(marker_input.unit_panjang_marker) unit_panjang_marker,
                    marker_input.comma_marker,
                    UPPER(marker_input.unit_comma_marker) unit_comma_marker,
                    marker_input.lebar_marker,
                    UPPER(marker_input.unit_lebar_marker) unit_lebar_marker,
                    form_cut_input.qty_ply,
                    marker_input.gelar_qty,
                    marker_input.po_marker,
                    marker_input.urutan_marker,
                    marker_input.cons_marker,
                    form_cut_input.total_lembar,
                    form_cut_input.no_cut,
                    UPPER(form_cut_input.shell) shell,
                    GROUP_CONCAT(DISTINCT master_size_new.size ORDER BY master_size_new.urutan ASC SEPARATOR ', ') sizes,
                    GROUP_CONCAT(DISTINCT CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio * form_cut_input.total_lembar, ')') ORDER BY master_size_new.urutan ASC) marker_details,
                    GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) SEPARATOR ', ') part
                ")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("part", "part.id", "=", "part_form.part_id")->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                where("form_cut_input.id", $formCut->id_form)->
                groupBy("form_cut_input.id")->
                first();

            $dataPartDetail = PartDetail::selectRaw("part_detail.id, master_part.nama_part, master_part.bag, COALESCE(master_secondary.tujuan, '-') tujuan, COALESCE(master_secondary.proses, '-') proses")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("part", "part.id", "part_detail.part_id")->
                leftJoin("part_form", "part_form.part_id", "part.id")->
                leftJoin("form_cut_input", "form_cut_input.id", "part_form.form_id")->
                leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                where("form_cut_input.id", $formCut->id_form)->
                groupBy("master_part.id")->
                get();

            $dataRatio = MarkerDetail::selectRaw("
                    marker_input_detail.id marker_detail_id,
                    marker_input_detail.so_det_id,
                    COALESCE(master_sb_ws.size, marker_input_detail.size) size,
                    COALESCE((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END), marker_input_detail.size) size_dest,
                    marker_input_detail.ratio,
                    stocker_input.id stocker_id
                ")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
                leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("part", "part.id", "=", "part_form.part_id")->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("stocker_input", function ($join) {
                    $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                    $join->on("stocker_input.part_detail_id", "=", "part_detail.id");
                    $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
                })->
                where("marker_input.id", $dataSpreading->marker_id)->
                where("marker_input_detail.ratio", ">", "0")->
                orderBy("marker_input_detail.id", "asc")->
                groupBy("marker_input_detail.id")->
                get();

            $dataStocker = MarkerDetail::selectRaw("
                    marker_input.color,
                    marker_input_detail.so_det_id,
                    marker_input_detail.ratio,
                    part_detail.id part_detail_id,
                    form_cut_input.no_cut,
                    stocker_input.id stocker_id,
                    stocker_input.shade,
                    stocker_input.group_stocker,
                    stocker_input.qty_ply,
                    stocker_input.range_awal,
                    stocker_input.range_akhir
                ")->
                leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("part", "part.id", "=", "part_form.part_id")->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("stocker_input", function ($join) {
                    $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                    $join->on("stocker_input.part_detail_id", "=", "part_detail.id");
                    $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
                })->
                where("marker_input.act_costing_ws", $dataSpreading->ws)->
                where("marker_input.color", $dataSpreading->color)->
                where("marker_input.panel", $dataSpreading->panel)->
                where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
                groupBy("form_cut_input.no_cut", "marker_input.color", "marker_input_detail.so_det_id", "part_detail.id", "stocker_input.ratio", "stocker_input.range_awal", "stocker_input.range_akhir")->
                orderBy("form_cut_input.no_cut", "desc")->
                orderBy("stocker_input.shade", "asc")->
                orderBy("stocker_input.size", "desc")->
                orderBy("stocker_input.ratio", "desc")->
                orderBy("stocker_input.group_stocker", "asc")->
                orderBy("stocker_input.part_detail_id", "desc")->
                get();

            $dataNumbering = MarkerDetail::selectRaw("
                    marker_input.color,
                    marker_input_detail.so_det_id,
                    marker_input_detail.ratio,
                    form_cut_input.no_cut,
                    stocker_numbering.id numbering_id,
                    stocker_numbering.no_cut_size,
                    MAX(stocker_numbering.number) range_akhir
                ")->
                leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->leftJoin("stocker_numbering", function ($join) {
                    $join->on("stocker_numbering.form_cut_id", "=", "form_cut_input.id");
                    $join->on("stocker_numbering.so_det_id", "=", "marker_input_detail.so_det_id");
                })->
                where("marker_input.act_costing_ws", $dataSpreading->ws)->
                where("marker_input.color", $dataSpreading->color)->
                where("marker_input.panel", $dataSpreading->panel)->
                where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
                whereRaw("(stocker_numbering.cancel IS NULL OR stocker_numbering.cancel != 'Y')")->
                groupBy("form_cut_input.no_cut", "marker_input.color", "marker_input_detail.so_det_id")->
                orderBy("form_cut_input.no_cut", "desc")->
                get();

            $storeDetailItemArr = [];
            foreach ($dataRatio as $ratio) {
                $qty = intval($ratio->ratio) * intval($dataSpreading->total_lembar);

                $numberingThis = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("ratio", ">", "0")->first() : null;
                $numberingBefore = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("ratio", ">", "0")->sortByDesc('no_cut')->first() : null;

                if ($numberingThis->numbering_id == null) {
                    $rangeAwal = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + 1 : "-") : 1) : 1);
                    $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + $qty : "-") : $qty) : $qty);

                    $now = Carbon::now();
                    $noCutSize = $ratio->size . "" . sprintf('%02s', $dataSpreading->no_cut);

                    if (is_numeric($rangeAwal) && is_numeric($rangeAkhir))
                    for ($i = $rangeAwal; $i <= $rangeAkhir; $i++) {
                        array_push($storeDetailItemArr, [
                            'kode' => "WIP-" . ($stockerDetailCount + $n),
                            'form_cut_id' => $formCut->id_form,
                            'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
                            'so_det_id' => $ratio->so_det_id,
                            'act_costing_ws' => $dataSpreading->ws,
                            'color' => $dataSpreading->color,
                            'size' => $ratio->size,
                            'panel' => $dataSpreading->panel,
                            'number' => $i,
                            'year_month' => $now->format('Y-m'),
                            'year_month_number' => ($stockerDetailMonthCount + $n),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        $n++;
                    }
                }
            }

            StockerDetail::insert($storeDetailItemArr);
        }

        return $storeDetailItemArr;
    }

    public function fixRedundantStocker(Request $request)
    {
        ini_set('max_execution_time', 360000);

        $stockerCount = Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first() ? str_replace("STK-", "", Stocker::select("id_qr_stocker")->orderBy("id", "desc")->first()->id_qr_stocker) + 1 : 1;

        $redundantStockers = DB::select("
            select
                id_qr_stocker,
                count(stocker_input.id)
            from
                stocker_input
            group by id_qr_stocker having count(stocker_input.id) > 1
        ");

        if ($redundantStockers) {
            $i = 0;
            foreach($redundantStockers as $redundantStocker) {
                $stockers = Stocker::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->get();

                $j = 0;
                foreach ($stockers as $stocker) {
                    if ($j != 0) {
                        \Log::info($stockerCount + $i + $j +1);

                        $stocker->id_qr_stocker = "STK-".($stockerCount + $i + $j +1);

                        $stocker->save();
                    } else {
                        $stocker = $stocker->id_qr_stocker;
                    }

                    $j++;
                }

                $i += $j;
            }
        }

        return $redundantStocker;
    }

    public function fixRedundantNumbering()
    {
        ini_set('max_execution_time', 360000);

        $numberingCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;

        $redundantNumberings = DB::select("
            select
                kode,
                count(id)
            from
                stocker_numbering
            group by kode having count(id) > 1
        ");

        if ($redundantNumberings) {
            $i = 0;
            foreach($redundantNumberings as $redundantNumbering) {
                $numberings = StockerDetail::where("kode", $redundantNumbering->kode)->get();
                $j = 0;
                foreach ($numberings as $numbering) {
                    if ($j != 0) {
                        $numbering->kode = "WIP-".($numberingCount + $i + $j + 1);

                        $numbering->save();

                        \Log::info($numbering->kode.", "."WIP-".($numberingCount + $i + $j + 1).", ".$i.", ".$j);
                    } else {
                        // $numbering = $numbering->kode;

                        \Log::info($numbering->kode.", ".$i.", ".$j);
                    }

                    $j++;
                }

                $i += $j;
            }
        }
    }

    public function part(Request $request)
    {
        if ($request->ajax()) {
            $partQuery = Part::selectRaw("
                    part.id,
                    part.kode,
                    part.buyer,
                    part.act_costing_ws ws,
                    part.style,
                    part.color,
                    part.panel,
                    COUNT(DISTINCT form_cut_input.id) total_form,
                    GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) ORDER BY master_part.nama_part SEPARATOR ', ') part_details,
                    a.sisa
                ")->leftJoin("part_detail", "part_detail.part_id", "=", "part.id")
                ->leftJoin("master_part", "master_part.id", "part_detail.master_part_id")
                ->leftJoin("part_form", "part_form.part_id", "part.id")
                ->leftJoin("form_cut_input", "form_cut_input.id", "part_form.form_id")
                ->leftJoin(
                    DB::raw("
                        (
                            select
                                part_id,
                                count(id) total,
                                SUM(CASE WHEN cons IS NULL THEN 0 ELSE 1 END) terisi,
                                count(id) - SUM(CASE WHEN cons IS NULL THEN 0 ELSE 1 END) sisa
                            from
                                part_detail
                            group by part_id
                        ) a
                    "),
                    "part.id", "=", "a.part_id"
                )
                ->groupBy("part.id");

            return DataTables::eloquent($partQuery)->
                filterColumn('ws', function ($query, $keyword) {
                    $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('style', function ($query, $keyword) {
                    $query->whereRaw("LOWER(style) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('color', function ($query, $keyword) {
                    $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('panel', function ($query, $keyword) {
                    $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('part.kode', 'desc')->orderBy('part.updated_at', 'desc');
                })->toJson();
        }

        return view("stocker.part.part", ["page" => "dashboard-stocker", "subPageGroup" => "proses-stocker", "subPage" => "part"]);
    }

    public function destroyPart(Part $part, $id = 0)
    {
        $countPartForm = PartForm::where("part_id", $id)->count();

        if ($countPartForm < 1) {
            $deletePart = Part::where("id", $id)->delete();

            if ($deletePart) {
                return array(
                    'status' => 200,
                    'message' => 'Part berhasil dihapus',
                    'redirect' => '',
                    'table' => 'datatable-part',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => 400,
            'message' => 'Part ini tidak dapat dihapus',
            'redirect' => '',
            'table' => 'datatable-part',
            'additional' => [],
        );
    }

    public function managePartForm(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $formCutInputs = FormCutInput::selectRaw("
                    form_cut_input.id,
                    form_cut_input.id_marker,
                    form_cut_input.no_form,
                    form_cut_input.tgl_form_cut,
                    users.name nama_meja,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.urutan_marker,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    GROUP_CONCAT(DISTINCT CONCAT(master_size_new.size, '(', marker_input_detail.ratio, ')') SEPARATOR ', ') marker_details,
                    form_cut_input.qty_ply,
                    form_cut_input.no_cut
                ")->leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->where("form_cut_input.status", "SELESAI PENGERJAAN")->whereRaw("part_form.id is not null")->where("part_form.part_id", $id)->where("marker_input.act_costing_ws", $request->act_costing_ws)->where("marker_input.panel", $request->panel)->groupBy("form_cut_input.id");

            return Datatables::eloquent($formCutInputs)->filterColumn('act_costing_ws', function ($query, $keyword) {
                    $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('buyer', function ($query, $keyword) {
                    $query->whereRaw("LOWER(buyer) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('style', function ($query, $keyword) {
                    $query->whereRaw("LOWER(style) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('color', function ($query, $keyword) {
                    $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('panel', function ($query, $keyword) {
                    $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_meja', function ($query, $keyword) {
                    $query->whereRaw("LOWER(users.name) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('form_cut_input.no_cut', 'asc');
                })->toJson();
        }

        $part = Part::selectRaw("
                part.id,
                part.kode,
                part.buyer,
                part.act_costing_ws,
                part.style,
                part.color,
                part.panel,
                GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) ORDER BY master_part.nama_part SEPARATOR ', ') part_details
            ")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "part_detail.master_part_id")->
            where("part.id", $id)->
            groupBy("part.id")->
            first();

        return view("stocker.part.manage-part-form", ["part" => $part, "page" => "dashboard-stocker",  "subPageGroup" => "proses-stocker", "subPage" => "part"]);
    }

    public function managePartSecondary(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $formCutInputs = FormCutInput::selectRaw("
                    form_cut_input.id,
                    form_cut_input.id_marker,
                    form_cut_input.no_form,
                    form_cut_input.tgl_form_cut,
                    users.name nama_meja,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.urutan_marker,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    GROUP_CONCAT(DISTINCT CONCAT(master_size_new.size, '(', marker_input_detail.ratio, ')') SEPARATOR ', ') marker_details,
                    form_cut_input.qty_ply,
                    form_cut_input.no_cut
                ")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                whereRaw("part_form.id is not null")->
                where("part_form.part_id", $id)->
                where("marker_input.act_costing_ws", $request->act_costing_ws)->
                where("marker_input.panel", $request->panel)->
                groupBy("form_cut_input.id");

            return Datatables::eloquent($formCutInputs)->
                filterColumn('act_costing_ws', function ($query, $keyword) {
                    $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('buyer', function ($query, $keyword) {
                    $query->whereRaw("LOWER(buyer) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('style', function ($query, $keyword) {
                    $query->whereRaw("LOWER(style) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('color', function ($query, $keyword) {
                    $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('panel', function ($query, $keyword) {
                    $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_meja', function ($query, $keyword) {
                    $query->whereRaw("LOWER(users.name) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('form_cut_input.no_cut', 'asc');
                })->toJson();
        }

        $part = Part::selectRaw("
                part.id,
                part.kode,
                part.buyer,
                part.act_costing_ws,
                part.style,
                part.color,
                part.panel,
                GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) ORDER BY master_part.nama_part SEPARATOR ', ') part_details
            ")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "part_detail.master_part_id")->
            where("part.id", $id)->
            groupBy("part.id")->
            first();

        $masterPart = DB::select("
            select
                pd.id isi,
                concat(nama_part,' - ',bag) tampil
            from
                part_detail pd
                inner join master_part mp on pd.master_part_id = mp.id
            where
                part_id = '$id'
        ");

        $masterTujuan = DB::select("select tujuan isi, tujuan tampil from master_tujuan");

        return view("stocker.part.manage-part-secondary", ["part" => $part, "masterPart" => $masterPart, "masterTujuan" => $masterTujuan, "page" => "dashboard-stocker",  "subPageGroup" => "proses-stocker", "subPage" => "part"]);
    }

    // Fixing Things...
    public function rearrangeGroup(Request $request) {
        $formCutDetails = FormCutInputDetail::where("no_form_cut_input", $request->no_form)->orderBy("id", "asc")->get();

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

        return $formCutDetails;
    }

    public function reorderStockerNumbering(Request $request) {
        ini_set('max_execution_time', 360000);

        $formCutInputs = FormCutInput::selectRaw("
                marker_input.color,
                form_cut_input.id as id_form,
                form_cut_input.no_cut,
                form_cut_input.no_form as no_form
            ")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            whereRaw("part_form.id is not null")->
            where("part.id", $request->id)->
            groupBy("form_cut_input.id")->
            orderBy("marker_input.color", "asc")->
            orderBy("form_cut_input.waktu_selesai", "asc")->
            orderBy("form_cut_input.no_cut", "asc")->
            get();

        $rangeAwal = 0;
        $sizeRangeAkhir = collect();

        $currentColor = "";
        $currentNumber = 0;

        // Loop over all forms
        foreach ($formCutInputs as $formCut) {
            $modifySizeQty = ModifySizeQty::where("no_form", $formCut->no_form)->get();

            // Reset cumulative data on color switch
            if ($formCut->color != $currentColor) {
                $rangeAwal = 0;
                $sizeRangeAkhir = collect();

                $currentColor = $formCut->color;
                $currentNumber = 0;
            }

            // Adjust form data
            $currentNumber++;
            FormCutInput::where("id", $formCut->id_form)->update([
                "no_cut" => $currentNumber
            ]);

            // Adjust form cut detail data
            $formCutInputDetails = FormCutInputDetail::where("no_form_cut_input", $formCut->no_form)->orderBy("id", "asc")->get();

            $currentGroup = "";
            $currentGroupNumber = 0;
            foreach ($formCutInputDetails as $formCutInputDetail) {
                if ($currentGroup != $formCutInputDetail->group_roll) {
                    $currentGroup = $formCutInputDetail->group_roll;
                    $currentGroupNumber += 1;
                }

                $formCutInputDetail->group_stocker = $currentGroupNumber;
                $formCutInputDetail->save();
            }

            // Adjust stocker data
            $stockerForm = Stocker::where("form_cut_id", $formCut->id_form)->orderBy("group_stocker", "asc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

            $currentStockerPart = $stockerForm->first() ? $stockerForm->first()->part_detail_id : "";
            $currentStockerSize = "";
            $currentStockerGroup = "initial";
            $currentStockerRatio = 0;

            foreach ($stockerForm as $key => $stocker) {
                $lembarGelaran = 1;
                if ($stocker->group_stocker) {
                    $lembarGelaran = FormCutInputDetail::where("no_form_cut_input", $formCut->no_form)->where('group_stocker', $stocker->group_stocker)->sum('lembar_gelaran');
                } else {
                    $lembarGelaran = FormCutInputDetail::where("no_form_cut_input", $formCut->no_form)->where('group_roll', $stocker->shade)->sum('lembar_gelaran');
                }

                if ($currentStockerPart == $stocker->part_detail_id) {
                    if ($stockerForm->min("group_stocker") == $stocker->group_stocker && $stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {
                        $modifyThis = $modifySizeQty->where("so_det_id", $stocker->so_det_id)->first();

                        if ($modifyThis) {
                            $lembarGelaran = ($stocker->qty_ply < 1 ? 0 : $lembarGelaran) + $modifyThis->difference_qty;
                        }
                    }

                    if (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($currentStockerSize != $stocker->so_det_id || $currentStockerGroup != $stocker->group_stocker || $currentStockerRatio != $stocker->ratio)) {
                        $rangeAwal = $sizeRangeAkhir[$stocker->so_det_id] + 1;
                        $sizeRangeAkhir[$stocker->so_det_id] = ($sizeRangeAkhir[$stocker->so_det_id] + $lembarGelaran);

                        $currentStockerSize = $stocker->so_det_id;
                        $currentStockerGroup = $stocker->group_stocker;
                        $currentStockerRatio = $stocker->ratio;
                    } else if (!isset($sizeRangeAkhir[$stocker->so_det_id])) {
                        $rangeAwal =  1;
                        $sizeRangeAkhir->put($stocker->so_det_id, $lembarGelaran);
                    }
                }

                $stocker->so_det_id && (($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1)) != $stocker->qty || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1))) : $stocker->qty_ply_mod = 0;
                $stocker->range_awal = $rangeAwal;
                $stocker->range_akhir = $stocker->so_det_id ? $sizeRangeAkhir[$stocker->so_det_id] : 0;
                $stocker->save();

                if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) {
                    $stocker->delete();
                }
            }

            // Adjust numbering data
            $numbers = StockerDetail::selectRaw("
                    form_cut_id,
                    act_costing_ws,
                    color,
                    panel,
                    so_det_id,
                    size,
                    no_cut_size,
                    MAX(number) number
                ")->
                where("form_cut_id", $formCut->id_form)->
                whereRaw("(cancel is null OR cancel = 'N')")->
                groupBy("form_cut_id", "size")->
                get();

            foreach ($numbers as $number) {
                if (isset($sizeRangeAkhir[$number->so_det_id])) {
                    if ($number->number > $sizeRangeAkhir[$number->so_det_id]) {
                        StockerDetail::where("form_cut_id", $number->form_cut_id)->
                            where("so_det_id", $number->so_det_id)->
                            where("number", ">", $sizeRangeAkhir[$number->so_det_id])->
                            update([
                                "cancel" => "Y"
                            ]);
                    } else {
                        StockerDetail::where("form_cut_id", $number->form_cut_id)->
                            where("so_det_id", $number->so_det_id)->
                            where("number", "<=", $sizeRangeAkhir[$number->so_det_id])->
                            where("cancel", "Y")->
                            update([
                                "cancel" => "N"
                            ]);
                    }

                    if ($number->number < $sizeRangeAkhir[$number->so_det_id]) {
                        $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
                        $noCutSize = substr($number->no_cut_size, 0, strlen($number->size)+2);

                        $no = 0;
                        for ($i = $number->number; $i < $sizeRangeAkhir[$number->so_det_id]; $i++) {
                            StockerDetail::create([
                                "kode" => "WIP-".($stockerDetailCount+$no),
                                "form_cut_id" => $number->form_cut_id,
                                "act_costing_ws" => $number->act_costing_ws,
                                "color" => $number->color,
                                "panel" => $number->panel,
                                "so_det_id" => $number->so_det_id,
                                "size" => $number->size,
                                "no_cut_size" => $noCutSize. sprintf('%04s', ($i+1)),
                                "number" => $i+1
                            ]);

                            $no++;
                        }
                    }
                }
            }
        }

        return $sizeRangeAkhir;
    }

    public function countStockerUpdate(Request $request)
    {
        $stockerGroups = Stocker::groupBy("so_det_id", "color", "panel", "part_detail_id")->orderBy("id", "asc")->get();

        $updatedStocker = [];
        foreach ($stockerGroups as $stockerGroup) {
            $i = 0;
            $rangeAkhir = 0;
            $formBefore = null;

            $stockers = Stocker::where("so_det_id", $stockerGroup->so_det_id)->where("color", $stockerGroup->color)->where("panel", $stockerGroup->panel)->where("part_detail_id", $stockerGroup->part_detail_id)->orderBy("id", "asc")->orderBy("form_cut_id", "asc")->get();

            foreach ($stockers as $stocker) {
                $i++;

                if ($stocker->form_cut_input == $formBefore) {
                    $rangeAkhir = 0;
                }

                $rangeAwal = $rangeAkhir + 1;
                $rangeAkhir = $rangeAkhir + ($stocker->qty_ply);

                $updateStockerCount = Stocker::where("id", $stocker->id)->update([
                    "range_awal" => $rangeAwal,
                    "range_akhir" => $rangeAkhir
                ]);

                if ($updateStockerCount) {
                    array_push($updatedStocker, ["stocker" => $stocker->id_qr_stocker]);

                    $formBefore = $stocker->form_cut_id;
                }
            }
        }

        return $stocker;
    }

    public function modifySizeQty(Request $request) {
        ini_set('max_execution_time', 360000);

        $noForm = $request->no_form;

        $formData = FormCutInput::selectRaw("
                form_cut_input.id form_id,
                form_cut_input.no_form,
                form_cut_input.no_cut,
                part_form.part_id part_id
            ")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            where("form_cut_input.no_form", $noForm)->
            first();

        $message = "";
        $index = array_keys($request->mod_so_det_id);

        foreach ($index as $i) {
            $soDetId = $request->mod_so_det_id[$i];
            $ratio = $request->mod_ratio[$i];
            $size = $request->mod_size[$i];
            $originalQty = $request->mod_original_qty[$i];
            $modifiedQty = $request->mod_qty_cut[$i];
            $differenceQty = $request->mod_difference_qty[$i];
            $note = $request->mod_note[$i];

            $createModifySizeQty = ModifySizeQty::updateOrCreate([
                "no_form" => $noForm,
                "so_det_id" => $soDetId,
            ],[
                "original_qty" => $originalQty,
                "modified_qty" => $modifiedQty,
                "difference_qty" => $differenceQty,
                "note" => $note,
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username
            ]);

            if ($createModifySizeQty) {
                $message .= $size."(".(($differenceQty > 0) ? "+".$differenceQty : $differenceQty).") berhasil di simpan. <br>";
            } else {
                $message .= $size."(".(($differenceQty > 0) ? "+".$differenceQty : $differenceQty).") gagal di simpan. <br>";
            }
        }

        if ($message != "") {
            $formCutInputs = FormCutInput::selectRaw("
                    marker_input.color,
                    form_cut_input.id as id_form,
                    form_cut_input.no_cut,
                    form_cut_input.no_form as no_form
                ")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("part", "part.id", "=", "part_form.part_id")->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                whereRaw("part_form.id is not null")->
                where("part.id", $formData->part_id)->
                groupBy("form_cut_input.id")->
                orderBy("marker_input.color", "asc")->
                orderBy("form_cut_input.waktu_selesai", "asc")->
                orderBy("form_cut_input.no_cut", "asc")->
                get();

            $rangeAwal = 0;
            $sizeRangeAkhir = collect();

            $currentColor = "";
            $currentNumber = 0;

            // Loop over all forms
            foreach ($formCutInputs as $formCut) {
                $modifySizeQty = ModifySizeQty::where("no_form", $formCut->no_form)->get();

                // Reset cumulative data on color switch
                if ($formCut->color != $currentColor) {
                    $rangeAwal = 0;
                    $sizeRangeAkhir = collect();

                    $currentColor = $formCut->color;
                    $currentNumber = 0;
                }

                // Adjust form data
                $currentNumber++;
                FormCutInput::where("id", $formCut->id_form)->update([
                    "no_cut" => $currentNumber
                ]);

                // Adjust form cut detail data
                $formCutInputDetails = FormCutInputDetail::where("no_form_cut_input", $formCut->no_form)->orderBy("id", "asc")->get();

                $currentGroup = "";
                $currentGroupNumber = 0;
                foreach ($formCutInputDetails as $formCutInputDetail) {
                    if ($currentGroup != $formCutInputDetail->group_roll) {
                        $currentGroup = $formCutInputDetail->group_roll;
                        $currentGroupNumber += 1;
                    }

                    $formCutInputDetail->group_stocker = $currentGroupNumber;
                    $formCutInputDetail->save();
                }

                // Adjust stocker data
                $stockerForm = Stocker::where("form_cut_id", $formCut->id_form)->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPart = $stockerForm->first() ? $stockerForm->first()->part_detail_id : "";
                $currentStockerSize = "";
                $currentStockerGroup = "initial";
                $currentStockerRatio = 0;

                foreach ($stockerForm as $key => $stocker) {
                    $lembarGelaran = 1;

                    if ($stocker->group_stocker) {
                        $lembarGelaran = FormCutInputDetail::where("no_form_cut_input", $formCut->no_form)->where('group_stocker', $stocker->group_stocker)->sum('lembar_gelaran');
                    } else {
                        $lembarGelaran = FormCutInputDetail::where("no_form_cut_input", $formCut->no_form)->where('group_roll', $stocker->shade)->sum('lembar_gelaran');
                    }

                    if ($currentStockerPart == $stocker->part_detail_id) {
                        if ($stockerForm->min("group_stocker") == $stocker->group_stocker && $stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {
                            $modifyThis = $modifySizeQty->where("so_det_id", $stocker->so_det_id)->first();

                            if ($modifyThis) {
                                $lembarGelaran = ($stocker->qty_ply < 1 ? 0 : $lembarGelaran) + $modifyThis->difference_qty;
                            }
                        }

                        if (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($currentStockerSize != $stocker->so_det_id || $currentStockerGroup != $stocker->group_stocker || $currentStockerRatio != $stocker->ratio)) {
                            $rangeAwal = $sizeRangeAkhir[$stocker->so_det_id] + 1;
                            $sizeRangeAkhir[$stocker->so_det_id] = ($sizeRangeAkhir[$stocker->so_det_id] + $lembarGelaran);

                            $currentStockerSize = $stocker->so_det_id;
                            $currentStockerGroup = $stocker->group_stocker;
                            $currentStockerRatio = $stocker->ratio;
                        } else if (!isset($sizeRangeAkhir[$stocker->so_det_id])) {
                            $rangeAwal =  1;
                            $sizeRangeAkhir->put($stocker->so_det_id, $lembarGelaran);
                        }
                    }

                    $stocker->size && (($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1)) != $stocker->qty || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1))) : $stocker->qty_ply_mod = 0;
                    $stocker->range_awal = $rangeAwal;
                    $stocker->range_akhir = $stocker->size ? $sizeRangeAkhir[$stocker->so_det_id] : 0;
                    $stocker->save();

                    if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) {
                        $stocker->delete();
                    }
                    // if ($formCut->no_form == '14-05-17' && $stocker->size == 'M') {
                    //     dd($stocker);
                    // }
                }

                // Adjust numbering data
                $numbers = StockerDetail::selectRaw("
                        form_cut_id,
                        act_costing_ws,
                        color,
                        panel,
                        so_det_id,
                        size,
                        no_cut_size,
                        MAX(number) number
                    ")->
                    where("form_cut_id", $formCut->id_form)->
                    groupBy("form_cut_id", "size")->
                    get();

                foreach ($numbers as $number) {
                    if (isset($sizeRangeAkhir[$number->so_det_id])) {
                        if ($number->number > $sizeRangeAkhir[$number->so_det_id]) {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("so_det_id", $number->so_det_id)->
                                where("number", ">", $sizeRangeAkhir[$number->so_det_id])->
                                update([
                                    "cancel" => "Y"
                                ]);
                        } else {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("so_det_id", $number->so_det_id)->
                                where("number", "<=", $sizeRangeAkhir[$number->so_det_id])->
                                where("cancel", "Y")->
                                update([
                                    "cancel" => "N"
                                ]);
                        }

                        if ($number->number < $sizeRangeAkhir[$number->so_det_id]) {
                            $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
                            $noCutSize = substr($number->no_cut_size, 0, strlen($number->size)+2);

                            $no = 0;
                            for ($i = $number->number; $i < $sizeRangeAkhir[$number->so_det_id]; $i++) {
                                StockerDetail::create([
                                    "kode" => "WIP-".($stockerDetailCount+$no),
                                    "form_cut_id" => $number->form_cut_id,
                                    "act_costing_ws" => $number->act_costing_ws,
                                    "color" => $number->color,
                                    "panel" => $number->panel,
                                    "so_det_id" => $number->so_det_id,
                                    "size" => $number->size,
                                    "no_cut_size" => $noCutSize. sprintf('%04s', ($i+1)),
                                    "number" => $i+1
                                ]);

                                $no++;
                            }
                        }
                    }
                }
            }

            return array(
                'status' => 200,
                'message' => $message,
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Perubahan qty size gagal disimpan',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function stockerList(Request $request) {
        if ($request->ajax()) {
            $additionalQuery = "";

            $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
            $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

            $stockerList = DB::select("
                SELECT
                    year_sequence_num.updated_at,
                    GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
                    GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
                    stocker_input.form_cut_id,
                    stocker_input.act_costing_ws,
                    stocker_input.so_det_id,
                    master_sb_ws.buyer buyer,
                    master_sb_ws.styleno style,
                    master_sb_ws.color,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    form_cut_input.no_form,
                    form_cut_input.no_cut,
                    stocker_input.group_stocker,
                    stocker_input.shade,
                    stocker_input.ratio,
                    CONCAT( MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir)) stocker_range,
                    (MAX(stocker_input.range_akhir) - MIN(stocker_input.range_awal) + 1) qty_stocker,
                    year_sequence_num.year_sequence,
                    (MAX(year_sequence_num.range_akhir) - MIN(year_sequence_num.range_awal) + 1) qty,
                    CONCAT( MIN(year_sequence_num.range_awal), ' - ', MAX(year_sequence_num.range_akhir)) numbering_range
                FROM
                    stocker_input
                    LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                    LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                    LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                    INNER JOIN (
                        SELECT
                            form_cut_id,
                            so_det_id,
                            CONCAT(`year`, '_', year_sequence) year_sequence,
                            MIN( number ) range_numbering_awal,
                            MAX( number ) range_numbering_akhir,
                            MIN( year_sequence_number ) range_awal,
                            MAX( year_sequence_number ) range_akhir,
                            COALESCE(updated_at, created_at) updated_at
                        FROM
                            year_sequence
                        WHERE
                            year_sequence.so_det_id is not null
                            AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
                            AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
                        GROUP BY
                            form_cut_id,
                            so_det_id,
                            COALESCE(updated_at, created_at)
                    ) year_sequence_num on year_sequence_num.form_cut_id = stocker_input.form_cut_id and year_sequence_num.so_det_id = stocker_input.so_det_id and year_sequence_num.range_numbering_awal >= stocker_input.range_awal and year_sequence_num.range_numbering_akhir <= stocker_input.range_akhir
                WHERE
                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                    AND (
                        form_cut_input.waktu_mulai >= '".$dateFrom." 00:00:00'
                        OR form_cut_input.waktu_selesai >= '".$dateFrom." 00:00:00'
                        OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
                        OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
                        OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
                    )
                    AND (
                        form_cut_input.waktu_mulai <= '".$dateTo." 23:59:59'
                        OR form_cut_input.waktu_selesai <= '".$dateTo." 23:59:59'
                        OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
                        OR stocker_input.created_at <= '".$dateTo." 23:59:59'
                        OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
                    )
                GROUP BY
                    stocker_input.form_cut_id,
                    stocker_input.so_det_id,
                    stocker_input.group_stocker,
                    stocker_input.ratio,
                    year_sequence_num.updated_at
                ORDER BY
                    year_sequence_num.updated_at desc
            ");

            return DataTables::of($stockerList)->toJson();
        }

        $months = [['angka' => '01','nama' => 'Januari'],['angka' => '02','nama' => 'Februari'],['angka' => '03','nama' => 'Maret'],['angka' => '04','nama' => 'April'],['angka' => '05','nama' => 'Mei'],['angka' => '06','nama' => 'Juni'],['angka' => '07','nama' => 'Juli'],['angka' => '08','nama' => 'Agustus'],['angka' => '09','nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view("stocker.stocker.stocker-list", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "stocker-list", "months" => $months, "years" => $years]);
    }

    public function stockerListDetail($form_cut_id, $so_det_id) {
        if ($form_cut_id && $so_det_id) {
            $months = [['angka' => '01','nama' => 'Januari'],['angka' => '02','nama' => 'Februari'],['angka' => '03','nama' => 'Maret'],['angka' => '04','nama' => 'April'],['angka' => '05','nama' => 'Mei'],['angka' => '06','nama' => 'Juni'],['angka' => '07','nama' => 'Juli'],['angka' => '08','nama' => 'Agustus'],['angka' => '09','nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            $stockerList = DB::select("
                    SELECT
                        GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker) id_qr_stocker,
                        GROUP_CONCAT(DISTINCT master_part.nama_part) part,
                        stocker_input.form_cut_id,
                        stocker_input.act_costing_ws,
                        stocker_input.so_det_id,
                        master_sb_ws.buyer buyer,
                        master_sb_ws.styleno style,
                        master_sb_ws.color,
                        master_sb_ws.size,
                        master_sb_ws.dest,
                        form_cut_input.no_form,
                        form_cut_input.no_cut,
                        stocker_input.group_stocker,
                        stocker_input.shade,
                        stocker_input.ratio,
                        MIN(stocker_input.range_awal) range_awal,
                        MAX(stocker_input.range_akhir) range_akhir,
                        CONCAT(MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir)) stocker_range
                    FROM
                        stocker_input
                    LEFT JOIN
                        part_detail on part_detail.id = stocker_input.part_detail_id
                    LEFT JOIN
                        master_part on master_part.id = part_detail.master_part_id
                    LEFT JOIN
                        master_sb_ws on master_sb_ws.id_so_det = stocker_input.so_det_id
                    LEFT JOIN
                        form_cut_input on form_cut_input.id = stocker_input.form_cut_id
                    WHERE
                        (form_cut_input.cancel is null or form_cut_input.cancel != 'Y') AND
                        stocker_input.form_cut_id = '".$form_cut_id."' AND
                        stocker_input.so_det_id = '".$so_det_id."'
                    GROUP BY
                        stocker_input.form_cut_id,
                        stocker_input.so_det_id,
                        stocker_input.group_stocker,
                        stocker_input.ratio
                    ORDER BY
                        stocker_input.updated_at desc,
                        stocker_input.created_at desc,
                        form_cut_input.waktu_selesai desc,
                        form_cut_input.waktu_mulai desc
                    LIMIT 1
                ");

            if ($stockerList[0]) {
                $stockerListNumber = YearSequence::selectRaw("
                    year_sequence.id_year_sequence,
                    year_sequence.number,
                    year_sequence.year,
                    year_sequence.year_sequence,
                    year_sequence.year_sequence_number,
                    master_sb_ws.size,
                    master_sb_ws.dest
                ")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
                whereRaw("
                    year_sequence.form_cut_id = '".$form_cut_id."' and
                    year_sequence.so_det_id = '".$so_det_id."'
                ")->
                get();

                $output = DB::connection("mysql_sb")->
                    table("output_rfts")->
                    selectRaw("
                        output_rfts.kode_numbering,
                        so_det.id,
                        userpassword.username sewing_line,
                        coalesce(output_rfts.updated_at) sewing_update,
                        output_rfts_packing.created_by packing_line,
                        coalesce(output_rfts_packing.updated_at) packing_update
                    ")->
                    leftJoin("output_rfts_packing", "output_rfts_packing.kode_numbering", "=", "output_rfts.kode_numbering")->
                    leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->
                    leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rfts.created_by")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                    whereIn("output_rfts.kode_numbering", $stockerListNumber->pluck("id_year_sequence"))->
                    get();

                return view("stocker.stocker.stocker-list-detail", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "stocker-list", "stockerList" => $stockerList[0], "stockerListNumber" => $stockerListNumber, "output" => $output, "months" => $months, "years" => $years]);
            }
        }

        return redirect()->route('stocker-list');
    }

    public function setMonthCountNumber(Request $request) {
        $validatedRequest = $request->validate([
            "month" => 'required',
            "year" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_month_count" => 'required',
            "range_akhir_month_count" => 'required',
        ]);

        if ($validatedRequest) {
            $currentData = MonthCount::selectRaw("
                    number
                ")->
                where('form_cut_id', $validatedRequest['form_cut_id'])->
                where('so_det_id', $validatedRequest['so_det_id'])->
                orderBy('number')->
                get();

            if ($validatedRequest['range_awal_month_count'] > 0 && $validatedRequest['range_awal_month_count'] <= $validatedRequest['range_akhir_month_count']) {

                $upsertData = [];

                $n = 0;
                $n1 = 0;
                for ($i = $validatedRequest['range_awal_month_count']; $i <= $validatedRequest['range_akhir_month_count']; $i++) {

                    if ($currentData->where('number', $validatedRequest['range_awal_stocker']+$n)->count() < 1) {
                        array_push($upsertData, [
                            "id_month_year" => $validatedRequest['year']."-".$validatedRequest['month']."_".($validatedRequest['range_awal_month_count'] + $n1),
                            "month_year" => $validatedRequest['year']."-".$validatedRequest['month'],
                            "month_year_number" => ($validatedRequest['range_awal_month_count'] + $n1),
                            "form_cut_id" => $validatedRequest['form_cut_id'],
                            "so_det_id" => $validatedRequest['so_det_id'],
                            "size" => $validatedRequest['size'],
                            "number" => $validatedRequest['range_awal_stocker']+$n,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                        ]);

                        $n1++;
                    }

                    $n++;
                }

                if (count($upsertData) > 0) {
                    MonthCount::upsert($upsertData, ['id_month_year', 'month_year', 'month_year_number'], ['form_cut_id', 'so_det_id', 'size', 'number', 'created_at', 'updated_at']);

                    $customPaper = array(0, 0, 35.35, 110.90);
                    $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearmonth-1', ["data" => $upsertData])->setPaper($customPaper);

                    $path = public_path('pdf/');
                    $fileName = str_replace("/", "-", ('Month Count.pdf'));
                    $pdf->save($path . '/' . str_replace("/", "_", $fileName));
                    $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

                    return response()->download($generatedFilePath);
                }
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function setYearSequenceNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        $now = Carbon::now();

        $validatedRequest = $request->validate([
            "year" => 'required',
            "year_sequence" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_year_sequence" => 'required',
            "range_akhir_year_sequence" => 'required',
        ]);

        if ($validatedRequest) {
            if ($request->replace) {
                $deleteYearSequence = YearSequence::where("year", $validatedRequest['year'])->
                    where("year_sequence", $validatedRequest['year_sequence'])->
                    where("form_cut_id", $validatedRequest['form_cut_id'])->
                    where("so_det_id", $validatedRequest['so_det_id'])->
                    where("number", ">=", $validatedRequest['range_awal_stocker'])->
                    where("number", "<=", $validatedRequest['range_akhir_stocker'])->
                    delete();
            }

            $currentData = YearSequence::selectRaw("
                    number
                ")->
                where('form_cut_id', $validatedRequest['form_cut_id'])->
                where('so_det_id', $validatedRequest['so_det_id'])->
                where("number", ">=", $validatedRequest['range_awal_stocker'])->
                where("number", "<=", $validatedRequest['range_akhir_stocker'])->
                orderBy('number')->
                get();

            if ($validatedRequest['range_awal_year_sequence'] > 0 && $validatedRequest['range_awal_year_sequence'] <= $validatedRequest['range_akhir_year_sequence'] && $validatedRequest['range_akhir_year_sequence'] <= 999999 && $validatedRequest['year_sequence'] > 0) {
                $yearSequence = YearSequence::selectRaw("year_sequence, year_sequence_number")->where("year", $validatedRequest['year'])->where("year_sequence", $validatedRequest['year_sequence'])->orderBy("year_sequence", "desc")->orderBy("year_sequence_number", "desc")->first();
                $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : $validatedRequest['year_sequence'];
                $yearSequenceNumber = $yearSequence ? $yearSequence->year_sequence_number + 1 : 1;

                $upsertData = [];

                $n = 0;
                $n1 = 0;
                $largeCount = 0;

                for ($i = $validatedRequest['range_awal_year_sequence']; $i <= $validatedRequest['range_akhir_year_sequence']; $i++) {
                    if ($i > 999999) {
                        $yearSequenceSequence = $yearSequenceSequence + 1;
                        $yearSequenceNumber = 1;
                    }

                    if ($currentData->where('number', $validatedRequest['range_awal_stocker']+$n)->count() < 1 || $request->method == "add" ) {
                        $currentNumber = ($currentData->count() > 0 ? $currentData->max("number")+1+$n : $validatedRequest['range_awal_stocker']+$n);

                        array_push($upsertData, [
                            "id_year_sequence" => $validatedRequest['year']."_".($yearSequenceSequence)."_".($validatedRequest['range_awal_year_sequence']+$n1),
                            "year" => $validatedRequest['year'],
                            "year_sequence" => $yearSequenceSequence,
                            "year_sequence_number" => ($validatedRequest['range_awal_year_sequence']+$n1),
                            "form_cut_id" => $validatedRequest['form_cut_id'],
                            "so_det_id" => $validatedRequest['so_det_id'],
                            "size" => $validatedRequest['size'],
                            "number" => ($currentNumber > $validatedRequest['range_akhir_stocker'] ? $validatedRequest['range_akhir_stocker'] : ($currentNumber)),
                            "created_at" => $now,
                            "updated_at" => $now,
                        ]);

                        if (count($upsertData) % 5000 == 0) {
                            YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], ['form_cut_id', 'so_det_id', 'size', 'number', 'created_at', 'updated_at']);

                            $upsertData = [];

                            $largeCount++;
                        }

                        $n1++;
                    }

                    $n++;
                }

                if (count($upsertData) > 0 || $largeCount > 0) {
                    if (count($upsertData) > 0) {
                        YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], ['form_cut_id', 'so_det_id', 'size', 'number', 'created_at', 'updated_at']);
                    }

                    $stockerData = Stocker::where("id_qr_stocker", $request->id_qr_stocker)->first();

                    $customPaper = array(0,0,275,175);
                    $pdf = PDF::loadView('stocker.stocker.pdf.print-year-sequence-stock', ["stockerData" => $stockerData, "range_awal" => $validatedRequest['range_awal_year_sequence'], "range_akhir" => $validatedRequest['range_akhir_year_sequence']])->setPaper($customPaper);

                    $path = public_path('pdf/');
                    $fileName = str_replace("/", "-", ('Stock Year Sequence.pdf'));
                    $pdf->save($path . '/' . str_replace("/", "_", $fileName));
                    $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

                    return response()->download($generatedFilePath);
                } else {
                    return array(
                        "status" => 400,
                        "message" => "Fkin Hell"
                    );
                }
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function checkAllStockNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
        $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

        $tanggalFilter = $request->tanggalFilter ? $request->tanggalFilter : '';
        $stockerFilter = $request->stockerFilter ? $request->stockerFilter : '';
        $partFilter = $request->partFilter ? $request->partFilter : '';
        $buyerFilter = $request->buyerFilter ? $request->buyerFilter : '';
        $wsFilter = $request->wsFilter ? $request->wsFilter : '';
        $styleFilter = $request->styleFilter ? $request->styleFilter : '';
        $noFormFilter = $request->noFormFilter ? $request->noFormFilter : '';
        $noCutFilter = $request->noCutFilter ? $request->noCutFilter : '';
        $colorFilter = $request->colorFilter ? $request->colorFilter : '';
        $sizeFilter = $request->sizeFilter ? $request->sizeFilter : '';
        $destFilter = $request->destFilter ? $request->destFilter : '';
        $groupFilter = $request->groupFilter ? $request->groupFilter : '';
        $shadeFilter = $request->shadeFilter ? $request->shadeFilter : '';
        $ratioFilter = $request->ratioFilter ? $request->ratioFilter : '';
        $stockerRangeFilter = $request->stockerRangeFilter ? $request->stockerRangeFilter : '';
        $qtyFilter = $request->qtyFilter ? $request->qtyFilter : '';
        $yearSequenceFilter = $request->yearSequenceFilter ? $request->yearSequenceFilter : '';
        $numberingRangeFilter = $request->numberingRangeFilter ? $request->numberingRangeFilter : '';

        $filterQuery = "";

        if ($tanggalFilter || $stockerFilter || $partFilter || $buyerFilter || $wsFilter || $styleFilter || $noFormFilter || $noCutFilter || $colorFilter || $sizeFilter || $destFilter || $groupFilter || $shadeFilter || $ratioFilter || $stockerRangeFilter || $qtyFilter || $numberingRangeFilter) {
            $filterQuery = "HAVING year_sequence_num.updated_at IS NOT NULL";

            if ($tanggalFilter) {
                $filterQuery .= ' AND tanggal LIKE "%'.$tanggalFilter.'%"';
            }
            if ($stockerFilter) {
                $filterQuery .= ' AND GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) LIKE "%'.$stockerFilter.'%"';
            }
            if ($partFilter) {
                $filterQuery .= ' AND GROUP_CONCAT( DISTINCT master_part.nama_part ) LIKE "%'.$partFilter.'%"';
            }
            if ($buyerFilter) {
                $filterQuery .= ' AND buyer LIKE "%'.$buyerFilter.'%"';
            }
            if ($wsFilter) {
                $filterQuery .= ' AND ws LIKE "%'.$wsFilter.'%"';
            }
            if ($styleFilter) {
                $filterQuery .= ' AND styleno LIKE "%'.$styleFilter.'%"';
            }
            if ($noFormFilter) {
                $filterQuery .= ' AND no_form LIKE "%'.$noFormFilter.'%"';
            }
            if ($noCutFilter) {
                $filterQuery .= ' AND no_cut LIKE "%'.$noCutFilter.'%"';
            }
            if ($colorFilter) {
                $filterQuery .= ' AND color LIKE "%'.$colorFilter.'%"';
            }
            if ($sizeFilter) {
                $filterQuery .= ' AND size LIKE "%'.$sizeFilter.'%"';
            }
            if ($destFilter) {
                $filterQuery .= ' AND dest LIKE "%'.$destFilter.'%"';
            }
            if ($groupFilter) {
                $filterQuery .= ' AND group LIKE "%'.$groupFilter.'%"';
            }
            if ($shadeFilter) {
                $filterQuery .= ' AND shade LIKE "%'.$shadeFilter.'%"';
            }
            if ($ratioFilter) {
                $filterQuery .= ' AND ratio LIKE "%'.$ratioFilter.'%"';
            }
            if ($stockerRangeFilter) {
                $filterQuery .= ' AND CONCAT( MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir)) LIKE "%'.$stockerRangeFilter.'%"';
            }
            if ($qtyFilter) {
                $filterQuery .= ' AND (MAX(year_sequence_num.range_akhir) - MIN(year_sequence_num.range_awal) + 1) LIKE "%'.$qtyFilter.'%"';
            }
            if ($yearSequenceFilter) {
                $filterQuery .= ' AND year_seqeuence_num.year_sequence LIKE "%'.$yearSequenceFilter.'%"';
            }
            if ($numberingRangeFilter) {
                $filterQuery .= ' AND CONCAT( MIN(year_sequence_num.range_awal), ' - ', MAX(year_sequence_num.range_akhir)) LIKE "%'.$numberingRangeFilter.'%"';
            }
        }

        $stockerList = DB::select("
            SELECT
                year_sequence_num.updated_at,
                GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
                GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
                stocker_input.form_cut_id,
                stocker_input.act_costing_ws,
                master_sb_ws.styleno style,
                master_sb_ws.buyer buyer,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                form_cut_input.no_form,
                form_cut_input.no_cut,
                stocker_input.group_stocker,
                stocker_input.shade,
                stocker_input.ratio,
                year_sequence_num.year_sequence,
                CONCAT( MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir)) stocker_range,
                (MAX(stocker_input.range_akhir) - MIN(stocker_input.range_awal) + 1) qty_stocker,
                (MAX(year_sequence_num.range_akhir) - MIN(year_sequence_num.range_awal) + 1) qty,
                CONCAT( MIN(year_sequence_num.range_awal), ' - ', MAX(year_sequence_num.range_akhir)) numbering_range
            FROM
                stocker_input
                LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                INNER JOIN (
                    SELECT
                        form_cut_id,
                        so_det_id,
                        CONCAT(`year`, '_', year_sequence) year_sequence,
                        MIN( number ) range_numbering_awal,
                        MAX( number ) range_numbering_akhir,
                        MIN( year_sequence_number ) range_awal,
                        MAX( year_sequence_number ) range_akhir,
                        COALESCE(updated_at, created_at) updated_at
                    FROM
                        year_sequence
                    GROUP BY
                        form_cut_id,
                        so_det_id,
                        COALESCE(updated_at, created_at)
                ) year_sequence_num on year_sequence_num.form_cut_id = stocker_input.form_cut_id and year_sequence_num.so_det_id = stocker_input.so_det_id and year_sequence_num.range_numbering_awal >= stocker_input.range_awal and year_sequence_num.range_numbering_akhir <= stocker_input.range_akhir
            WHERE
                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                AND (
                    DATE ( form_cut_input.waktu_mulai ) >= '".$dateFrom."'
                    OR DATE ( form_cut_input.waktu_selesai ) >= '".$dateFrom."'
                    OR DATE ( stocker_input.updated_at ) >= '".$dateFrom."'
                    OR DATE ( stocker_input.created_at ) >= '".$dateFrom."'
                    OR year_sequence_num.updated_at >= '".$dateFrom."'
                )
                AND (
                    DATE ( form_cut_input.waktu_mulai ) <= '".$dateTo."'
                    OR DATE ( form_cut_input.waktu_selesai ) <= '".$dateTo."'
                    OR DATE ( stocker_input.updated_at ) <= '".$dateTo."'
                    OR DATE ( stocker_input.created_at ) <= '".$dateTo."'
                    OR year_sequence_num.updated_at <= '".$dateTo."'
                )
            GROUP BY
                stocker_input.form_cut_id,
                stocker_input.so_det_id,
                stocker_input.group_stocker,
                stocker_input.ratio,
                year_sequence_num.updated_at
                ".$filterQuery."
            ORDER BY
                stocker_input.updated_at DESC,
                stocker_input.created_at DESC,
                form_cut_input.waktu_selesai DESC,
                form_cut_input.waktu_mulai DESC
        ");

        return $stockerList;
    }

    public function printStockNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        if ($request->stockNumbers && count($request->stockNumbers) > 0) {
            $customPaper = array(0,0,275,175);
            $pdf = PDF::loadView('stocker.stocker.pdf.print-year-sequence-stocks', ["stockNumbers" => $request->stockNumbers])->setPaper($customPaper);

            $path = public_path('pdf/');
            $fileName = str_replace("/", "-", ('Stock Year Sequence.pdf'));
            $pdf->save($path . '/' . str_replace("/", "_", $fileName));
            $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

            return response()->download($generatedFilePath);
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function deleteYearSequence(Request $request) {
        ini_set("max_execution_time", 36000);

        $validatedRequest = $request->validate([
            "year" => 'required',
            "year_sequence" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_year_sequence" => 'required',
            "range_akhir_year_sequence" => 'required',
        ]);

        $deleteYearSequence = YearSequence::where("year", $validatedRequest['year'])->
            where("year_sequence", $validatedRequest['year_sequence'])->
            where("fomr_cut_id", $validatedRequest['fomr_cut_id'])->
            where("so_det_id", $validatedRequest['so_det_id'])->
            where("number", ">=", $validatedRequest['range_awal_stocker'])->
            delete();
    }

    public function customMonthCount() {
        $months = [['angka' => '01','nama' => 'Januari'],['angka' => '02','nama' => 'Februari'],['angka' => '03','nama' => 'Maret'],['angka' => '04','nama' => 'April'],['angka' => '05','nama' => 'Mei'],['angka' => '06','nama' => 'Juni'],['angka' => '07','nama' => 'Juli'],['angka' => '08','nama' => 'Agustus'],['angka' => '09','nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view("stocker.stocker.month-count", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "month-count", "months" => $months,  "years" => $years]);
    }

    public function yearSequence() {
        $years = array_reverse(range(1999, date('Y')));

        return view("stocker.stocker.year-sequence", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "year-sequence", "years" => $years]);
    }

    public function printMonthCount(Request $request) {
        ini_set("max_execution_time", 360000);

        $method = $request->method ? $request->method : 'qty';
        $qty = $request->qty ? $request->qty : 0;
        $rangeAwal = $request->rangeAwal ? $request->rangeAwal : 0;
        $rangeAkhir = $request->rangeAkhir ? $request->rangeAkhir : 0;

        if ($method == 'qty' && $qty > 0) {
            $insertData = [];

            $monthCount = MonthCount::select("month_year_number")->where("month_year", Carbon::now()->format('Y-m'))->orderBy("month_year_number", "desc")->first();
            $monthCountNumber = $monthCount ? $monthCount->month_year_number + 1 : 1;

            for ($i = 0; $i < $qty; $i++) {
                array_push($insertData, [
                    "id_month_year" => Carbon::now()->format('Y-m')."_".$monthCountNumber,
                    "month_year" => Carbon::now()->format('Y-m'),
                    "month_year_number" => $monthCountNumber,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);

                $monthCountNumber++;
            }

            if (count($insertData) > 0) {
                MonthCount::insert($insertData);

                $customPaper = array(0, 0, 35.35, 110.90);
                $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearmonth-1', ["data" => $insertData])->setPaper($customPaper);

                $path = public_path('pdf/');
                $fileName = str_replace("/", "-", ('Month Count.pdf'));
                $pdf->save($path . '/' . str_replace("/", "_", $fileName));
                $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

                return response()->download($generatedFilePath);
            }

            return array(
                "status" => 400,
                "message" => "Something went wrong",
            );
        } else if ($method == 'range' && $rangeAwal > 0 && $rangeAkhir > 0 && $rangeAwal <= $rangeAkhir) {
            $upsertData = [];

            for ($i = $rangeAwal; $i <= $rangeAkhir; $i++) {
                array_push($upsertData, [
                    "id_month_year" => Carbon::now()->format('Y-m')."_".$i,
                    "month_year" => Carbon::now()->format('Y-m'),
                    "month_year_number" => $i,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
            }

            if (count($upsertData) > 0) {
                MonthCount::upsert($upsertData, ['id_month_year', 'month_year', 'month_year_number'], ['created_at', 'updated_at']);

                $customPaper = array(0, 0, 35.35, 110.90);
                $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearmonth-1', ["data" => $upsertData])->setPaper($customPaper);

                $path = public_path('pdf/');
                $fileName = str_replace("/", "-", ('Month Count.pdf'));
                $pdf->save($path . '/' . str_replace("/", "_", $fileName));
                $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

                return response()->download($generatedFilePath);
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function printYearSequence(Request $request) {
        ini_set("max_execution_time", 360000);
        ini_set("memory_limit", '2048M');

        $method = $request->method ? $request->method : 'qty';
        $yearSequenceYear = $request->year ? $request->year : Carbon::now()->format('Y');
        $yearSequenceSequence = $request->yearSequence ? $request->yearSequence : 0;
        $qty = $request->qty ? $request->qty : 0;
        $rangeAwal = $request->rangeAwal ? $request->rangeAwal : 0;
        $rangeAkhir = $request->rangeAkhir ? $request->rangeAkhir : 0;

        if ($method == 'qty' && $qty > 0) {
            $insertData = [];

            $yearSequence = YearSequence::selectRaw("year_sequence, year_sequence_number")->where("year", $yearSequenceYear)->where("year_sequence", $yearSequenceSequence)->orderBy("year_sequence", "desc")->orderBy("year_sequence_number", "desc")->first();
            $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : 1;
            $yearSequenceNumber = $yearSequence ? $yearSequence->year_sequence_number + 1 : 1;

            for ($i = 0; $i < $qty; $i++) {
                if ($yearSequenceNumber > 999999) {
                    $yearSequenceSequence = $yearSequenceSequence + 1;
                    $yearSequenceNumber = 1;
                }

                array_push($insertData, [
                    "id_year_sequence" => $yearSequenceYear."_".sprintf('%03d', $yearSequenceSequence)."_".$yearSequenceNumber,
                    "year" => $yearSequenceYear,
                    "year_sequence" => $yearSequenceSequence,
                    "year_sequence_number" => $yearSequenceNumber,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);

                $yearSequenceNumber++;
            }

            if (count($insertData) > 0) {
                YearSequence::insert($insertData);

                // $customPaper = array(0, 0, 35.35, 110.90);
                // $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence', ["data" => $insertData])->setPaper($customPaper);

                // $path = public_path('pdf/');
                // $fileName = str_replace("/", "-", ('Year Sequence.pdf'));
                // $pdf->save($path . '/' . str_replace("/", "_", $fileName));
                // $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

                // return response()->download($generatedFilePath);
            }

            return array(
                "status" => 400,
                "message" => "Something went wrong",
            );
        } else if ($method == 'range' && $rangeAwal > 0 && $rangeAkhir > 0 && $rangeAwal <= $rangeAkhir && $rangeAkhir <= 999999) {
            $upsertData = [];

            $yearSequence = YearSequence::selectRaw("year_sequence, year_sequence_number")->where("year", $yearSequenceYear)->where("year_sequence", $yearSequenceSequence)->orderBy("year_sequence", "desc")->orderBy("year_sequence_number", "desc")->first();
            $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : 1;

            for ($i = $rangeAwal; $i <= $rangeAkhir; $i++) {

                array_push($upsertData, [
                    "id_year_sequence" => $yearSequenceYear."_".$yearSequenceSequence."_".$i,
                    "year" => $yearSequenceYear,
                    "year_sequence" => $yearSequenceSequence,
                    "year_sequence_number" => $i,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
            }

            if (count($upsertData) > 0) {
                YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], ['created_at', 'updated_at']);

                // $customPaper = array(0, 0, 35.35, 110.90);
                // $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence', ["data" => $upsertData])->setPaper($customPaper);

                // $path = public_path('pdf/');
                // $fileName = str_replace("/", "-", ('Year Sequence.pdf'));
                // $pdf->save($path . '/' . str_replace("/", "_", $fileName));
                // $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

                // return response()->download($generatedFilePath);
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function printYearSequenceNew(Request $request) {
        $yearSequence = YearSequence::selectRaw("size, id_year_sequence, year, year_sequence, year_sequence_number")->
            where("year", $request->year)->
            where("year_sequence", $request->yearSequence)->
            where("year_sequence_number", ">=", $request->rangeAwal)->
            where("year_sequence_number", "<=", $request->rangeAkhir)->
            orderBy("year_sequence", "asc")->
            orderBy("year_sequence_number", "asc")->
            get()->toArray();

        $customPaper = array(0, 0, 35.35, 110.90);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence-1', ["data" => $yearSequence])->setPaper($customPaper);

        $path = public_path('pdf/');
        $fileName = str_replace("/", "-", ('Year Sequence.pdf'));
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
    }

    public function getStocker(Request $request) {
        if ($request->stocker) {
            $stockerData = Stocker::selectRaw("
                    stocker_input.id_qr_stocker,
                    stocker_input.form_cut_id,
                    stocker_input.so_det_id,
                    stocker_input.act_costing_ws,
                    part.style,
                    stocker_input.color,
                    stocker_input.size,
                    master_part.nama_part part,
                    form_cut_input.no_form,
                    (
                        (COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                        (COALESCE ( MAX(dc_in_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(dc_in_input.qty_replace), 0 )) -
                        (COALESCE ( MAX(secondary_in_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(secondary_in_input.qty_replace), 0 )) -
                        (COALESCE ( MAX(secondary_inhouse_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(secondary_inhouse_input.qty_replace), 0 ))
                    ) qty,
                    stocker_input.range_awal,
                    stocker_input.range_akhir
                ")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                where("stocker_input.id_qr_stocker", $request->stocker)->
                first();

            if ($stockerData) {
                return json_encode($stockerData);
            }

            return array(
                "status" => "400",
                "message" => "Stocker tidak ditemukan",
            );
        }

        return array(
            "status" => "400",
            "message" => "Stocker tidak valid",
        );
    }

    public function getStockerMonthCount(Request $request) {
        $stockerListNumber = MonthCount::selectRaw("
                month_count.id_month_year,
                month_count.number,
                month_count.month_year,
                month_count.month_year_number,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "month_count.so_det_id")->
            whereRaw("
                month_count.form_cut_id = '".$request->form_cut_id."' and
                month_count.so_det_id = '".$request->so_det_id."' and
                (month_count.number >= '".$request->range_awal."' and month_count.number <= '".$request->range_akhir."')
            ")->
            get();

        return Datatables::of($stockerListNumber)->toJson();
    }

    public function getStockerYearSequence(Request $request) {
        $stockerListNumber = YearSequence::selectRaw("
                year_sequence.id_year_sequence,
                year_sequence.number,
                year_sequence.year,
                year_sequence.year_sequence,
                year_sequence.year_sequence_number,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            whereRaw("
                year_sequence.form_cut_id = '".$request->form_cut_id."' and
                year_sequence.so_det_id = '".$request->so_det_id."' and
                (year_sequence.number >= '".$request->range_awal."') and
                (year_sequence.number <= '".$request->range_akhir."')
            ")->
            orderBy("year_sequence.number", "asc")->
            get();

        return Datatables::of($stockerListNumber)->toJson();
    }

    public function getRangeMonthCount(Request $request) {
        if ($request->month && $request->year) {

            $monthYear = $request->year."-".$request->month;

            $availableMonthCount = MonthCount::selectRaw("
                    month_year,
                    month_year_number
                ")->
                where("month_count.month_year", $monthYear)->
                whereRaw('number IS NOT NULL')->
                whereRaw('form_cut_id IS NOT NULL')->
                whereRaw('so_det_id IS NOT NULL')->
                orderBy('month_year_number', 'desc')->
                first();

            if ($availableMonthCount) {
                return json_encode($availableMonthCount);
            } else {
                return json_encode(["month_year" => $monthYear, "month_year_number" => 1]);
            }
        }

        return array(
            "status" => 400,
            "message" => "Bulan dan tahun tidak valid",
        );
    }

    public function getSequenceYearSequence(Request $request) {
        if ($request->year) {
            $availableYearSequence = YearSequence::selectRaw("
                    year_sequence
                ")->
                where("year_sequence.year",  $request->year)->
                groupBy("year_sequence.year_sequence")->
                orderBy("year_sequence.year_sequence", "asc")->
                get();

            if ($availableYearSequence->count() > 0) {
                return json_encode($availableYearSequence);
            } else {
                return json_encode([["year" => $request->year, "year_sequence" => 1]]);
            }
        }

        return array(
            "status" => 400,
            "message" => "Tahun tidak valid",
        );
    }

    public function getRangeYearSequence(Request $request) {
        if ($request->year && $request->sequence) {

            // $availableYearSequence = YearSequence::selectRaw("
            //         year,
            //         year_sequence,
            //         year_sequence_number
            //     ")->
            //     where("year_sequence.year",  $request->year)->
            //     where("year_sequence.year_sequence",  $request->sequence)->
            //     whereRaw('number IS NOT NULL')->
            //     whereRaw('form_cut_id IS NOT NULL')->
            //     whereRaw('so_det_id IS NOT NULL')->
            //     orderBy('year_sequence_number', 'desc')->
            //     first();

            $availableYearSequence = DB::select("
                select
                    year,
                    year_sequence,
                    MAX(year_sequence_number) year_sequence_number
                from
                    `year_sequence`
                where
                    `year_sequence`.`year` = '".$request->year."'
                    and `year_sequence`.`year_sequence` = '".$request->sequence."'
                    and so_det_id IS NOT NULL
                GROUP BY
                    year,
                    year_sequence
            ");

            if ($availableYearSequence && $availableYearSequence[0]) {
                return json_encode($availableYearSequence[0]);
            } else {
                return json_encode(["year" => $request->year, "year_sequence" => $request->year_sequence, "year_sequence_number" => 1]);
            }
        }

        return array(
            "status" => 400,
            "message" => "Tahun tidak valid",
        );
    }

    // public function printMonthCountChecked(Request $request) {
    //     $checkedSize = collect($request['generate_num']);

    //     $checkedSizeKeys = $checkedSize->keys();

    //     $n = 0;
    //     foreach ($checkedSizeKeys as $index) {
    //         $rangeAwal = $request['range_awal'][$index];
    //         $rangeAkhir = $request['range_akhir'][$index] + 1;

    //         $now = Carbon::now();
    //     }
    // }
}

