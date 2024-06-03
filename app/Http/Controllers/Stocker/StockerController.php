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
                marker_input_detail.ratio
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            where("marker_input.id", $dataSpreading->marker_id)->
            where("marker_input_detail.ratio", ">", "0")->
            orderBy("marker_input_detail.id", "asc")->
            groupBy("marker_input_detail.id")->
            get();

        $dataStocker = MarkerDetail::selectRaw("
                MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                marker_input.color,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                MAX(form_cut_input.no_form) no_form,
                form_cut_input.no_cut,
                MAX(stocker_input.id) stocker_id,
                MAX(stocker_input.shade) shade,
                MAX(stocker_input.group_stocker) group_stocker,
                MAX(stocker_input.qty_ply) qty_ply,
                MAX(CAST(stocker_input.range_akhir as UNSIGNED)) range_akhir
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("stocker_input", function ($join) {
                $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            where("marker_input_detail.ratio", ">", "0")->
            groupBy("no_form", "form_cut_input.no_cut", "marker_input_detail.so_det_id")->
            orderBy("form_cut_input.no_cut", "desc")->
            orderBy("form_cut_input.no_form", "desc")->
            get();

        $dataNumbering = MarkerDetail::selectRaw("
                marker_input.color,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                MAX(form_cut_input.no_form) no_form,
                form_cut_input.no_cut,
                MAX(stocker_numbering.id) numbering_id,
                MAX(stocker_numbering.no_cut_size) no_cut_size,
                MAX(stocker_numbering.number) range_akhir
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("stocker_numbering", function ($join) {
                $join->on("stocker_numbering.form_cut_id", "=", "form_cut_input.id");
                $join->on("stocker_numbering.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            where("marker_input_detail.ratio", ">", "0")->
            whereRaw("(stocker_numbering.cancel IS NULL OR stocker_numbering.cancel != 'Y')")->
            groupBy("no_form", "no_cut", "marker_input_detail.so_det_id")->
            orderBy("form_cut_input.no_cut", "desc")->
            orderBy("form_cut_input.no_form", "desc")->
            get();

            
        $modifySizeQty = ModifySizeQty::where("no_form", $dataSpreading->no_form)->get();

        return view("stocker.stocker.stocker-detail", ["dataSpreading" => $dataSpreading, "dataPartDetail" => $dataPartDetail, "dataRatio" => $dataRatio, "dataStocker" => $dataStocker, "dataNumbering" => $dataNumbering, "modifySizeQty" => $modifySizeQty, "page" => "dashboard-stocker", "subPageGroup" => "proses-stocker", "subPage" => "stocker"]);
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

        $storeItemArr = [];
        for ($i = 0; $i < $request['ratio'][$index]; $i++) {
            $checkStocker = Stocker::select("id_qr_stocker", "range_awal", "range_akhir")->whereRaw("
                part_detail_id = '" . $request['part_detail_id'][$index] . "' AND
                form_cut_id = '" . $request['form_cut_id'] . "' AND
                so_det_id = '" . $request['so_det_id'][$index] . "' AND
                color = '" . $request['color'] . "' AND
                panel = '" . $request['panel'] . "' AND
                shade = '" . $request['group'][$index] . "' AND
                qty_ply = '" . $request['qty_ply_group'][$index] . "' AND
                " . ($request['group_stocker'][$index] && $request['group_stocker'][$index] != "" ? "group_stocker = '" . $request['group_stocker'][$index] . "' AND" : "") . "
                ratio = " . ($i + 1) . "
            ")->first();

            $ratio = $i + 1;
            $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $i);
            $cumRangeAwal = $cumRangeAkhir + 1;
            $cumRangeAkhir = $cumRangeAkhir + $request['qty_ply_group'][$index];

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
                    'qty_ply' => $request['qty_ply_group'][$index],
                    'qty_ply_mod' => (($request['group_stocker'][$i] == min($request['group_stocker'])) && ($i == ($request['ratio'][$index] - 1) && $modifySizeQty) ? $request['qty_ply_group'][$index] + $modifySizeQty->difference_qty : null),
                    'qty_cut' => $request['qty_cut'][$index],
                    'notes' => $request['note'],
                    'range_awal' => $cumRangeAwal,
                    'range_akhir' => (($request['group_stocker'][$i] == min($request['group_stocker'])) && ($i == ($request['ratio'][$index] - 1) && $modifySizeQty) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                    'created_by' => Auth::user()->id,
                    'created_by_username' => Auth::user()->username,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, marker_input_detail.size) size,
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
            where("stocker_input.qty_ply", $request['qty_ply_group'][$index])->
            where("stocker_input.group_stocker", $request['group_stocker'][$index])->
            groupBy("form_cut_input.id", "stocker_input.id")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('a7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'stocker-' . $stockerId . '.pdf';
        $pdf->save($path . '/' . $fileName);
        $generatedFilePath = public_path('pdf/' . $fileName);

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

                for ($j = 0; $j < $request['ratio'][$i]; $j++) {
                    $checkStocker = Stocker::select("id_qr_stocker", "range_awal", "range_akhir")->whereRaw("
                        part_detail_id = '" . $request['part_detail_id'][$i] . "' AND
                        form_cut_id = '" . $request['form_cut_id'] . "' AND
                        so_det_id = '" . $request['so_det_id'][$i] . "' AND
                        color = '" . $request['color'] . "' AND
                        panel = '" . $request['panel'] . "' AND
                        shade = '" . $request['group'][$i] . "' AND
                        " . ($request['group_stocker'][$i] && $request['group_stocker'][$i] != "" ? "group_stocker = '" . $request['group_stocker'][$i] . "' AND" : "") . "
                        qty_ply = '" . $request['qty_ply_group'][$i] . "' AND
                        ratio = " . ($j + 1) . "
                    ")->first();

                    $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $j);
                    $cumRangeAwal = $cumRangeAkhir + 1;
                    $cumRangeAkhir = $cumRangeAkhir + $request['qty_ply_group'][$i];

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
                            'qty_ply' => $request['qty_ply_group'][$i],
                            'qty_ply_mod' => (($request['group_stocker'][$i] == min($request['group_stocker'])) && ($j == ($request['ratio'][$i] - 1) && $modifySizeQty) ? $request['qty_ply_group'][$i] + $modifySizeQty->difference_qty : null),
                            'qty_cut' => $request['qty_cut'][$i],
                            'notes' => $request['note'],
                            'range_awal' => $cumRangeAwal,
                            'range_akhir' => (($request['group_stocker'][$i] == min($request['group_stocker'])) && ($j == ($request['ratio'][$i] - 1) && $modifySizeQty) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                            'created_by' => Auth::user()->id,
                            'created_by_username' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, marker_input_detail.size) size,
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
            groupBy("form_cut_input.id", "stocker_input.id")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.shade", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('a7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'stocker-' . $request['form_cut_id'] . '-' . $partDetailId . '.pdf';
        $pdf->save($path . '/' . $fileName);
        $generatedFilePath = public_path('pdf/' . $fileName);

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
            $rangeAwal = $request['range_awal'][$index];
            $rangeAkhir = $request['range_akhir'][$index];

            $cumRangeAwal = $rangeAwal;
            $cumRangeAkhir = $rangeAwal - 1;

            for ($j = 0; $j < $request['ratio'][$index]; $j++) {
                $modifySizeQty = ModifySizeQty::where("no_form", $formData->no_form)->where("so_det_id", $request['so_det_id'][$index])->first();

                $checkStocker = Stocker::select("id_qr_stocker", "range_awal", "range_akhir")->whereRaw("
                    part_detail_id = '" . $request['part_detail_id'][$index] . "' AND
                    form_cut_id = '" . $request['form_cut_id'] . "' AND
                    so_det_id = '" . $request['so_det_id'][$index] . "' AND
                    color = '" . $request['color'] . "' AND
                    panel = '" . $request['panel'] . "' AND
                    shade = '" . $request['group'][$index] . "' AND
                    " . ($request['group_stocker'][$index] && $request['group_stocker'][$index] != "" ? "group_stocker = '" . $request['group_stocker'][$index] . "' AND" : "") . "
                    qty_ply = '" . $request['qty_ply_group'][$index] . "' AND
                    ratio = " . ($j + 1) . "
                ")->first();

                $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-" . ($stockerCount + $j + $i + 1);
                $cumRangeAwal = $cumRangeAkhir + 1;
                $cumRangeAkhir = $cumRangeAkhir + $request['qty_ply_group'][$index];

                \Log::info($stockerId);

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
                        'ratio' => ($j + 1),
                        'size' => $request["size"][$index],
                        'qty_ply' => $request['qty_ply_group'][$index],
                        'qty_ply_mod' => ($request['group_stocker'][$index] == (min($request['group_stocker'])) && ($j == ($request['ratio'][$index] - 1) && $modifySizeQty) ? $request['qty_ply_group'][$index] + $modifySizeQty->difference_qty : null),
                        'qty_cut' => $request['qty_cut'][$index],
                        'notes' => $request['note'],
                        'range_awal' => $cumRangeAwal,
                        'range_akhir' => ($request['group_stocker'][$index] == (min($request['group_stocker'])) && ($j == ($request['ratio'][$index] - 1) && $modifySizeQty) ? $cumRangeAkhir + $modifySizeQty->difference_qty : $cumRangeAkhir),
                        'created_by' => Auth::user()->id,
                        'created_by_username' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            $i += $j;
        }

        if (count($storeItemArr) > 0) {
            $storeItem = Stocker::insert($storeItemArr);
        }

        $dataStockers = Stocker::selectRaw("
                (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
                COALESCE(master_sb_ws.size, marker_input_detail.size) size,
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
            whereIn("part_detail.id", $request['generate_stocker'])->
            where("form_cut_input.id", $request['form_cut_id'])->
            groupBy("form_cut_input.id", "stocker_input.id")->
            orderBy("stocker_input.group_stocker", "desc")->
            orderBy("stocker_input.shade", "desc")->
            orderBy("stocker_input.so_det_id", "asc")->
            orderBy("stocker_input.ratio", "asc")->
            get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('a7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'stocker-' . $request['form_cut_id'] . '-' . implode($request['generate_stocker']) . '.pdf';
        $pdf->save($path . '/' . $fileName);
        $generatedFilePath = public_path('pdf/' . $fileName);

        return response()->download($generatedFilePath);
    }

    public function printNumbering(Request $request, $index)
    {
        $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;

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
                    'created_by' => Auth::user()->id,
                    'created_by_username' => Auth::user()->username,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            array_push($detailItemArr, [
                'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-" . ($stockerDetailCount + $n),
                'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
                'size' => $request['size'][$index],
                'so_det_id' => $request['so_det_id'][$index],
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $n++;
        }

        if (count($storeDetailItemArr) > 0) {
            $storeDetailItem = StockerDetail::insert($storeDetailItemArr);
        }

        // generate pdf
        $customPaper = array(0, 0, 56.70, 33.39);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering', ["ws" => $request["no_ws"], "color" => $request["color"], "no_cut" => $request["no_cut"], "dataNumbering" => $detailItemArr])->setPaper($customPaper);

        $path = public_path('pdf/');
        $fileName = str_replace("/", "-", ($request["no_ws"]. '-' . $request["color"] . '-' . $request["no_cut"] . '-Numbering.pdf'));
        $pdf->save($path . '/' . $fileName);
        $generatedFilePath = public_path('pdf/' . $fileName);

        return response()->download($generatedFilePath);
    }

    public function printNumberingChecked(Request $request)
    {
        ini_set('max_execution_time', 36000);

        $detailItemArr = [];
        $storeDetailItemArr = [];

        $checkedSize = collect($request['generate_num']);

        $checkedSizeKeys = $checkedSize->keys();

        $stockerDetail = StockerDetail::orderBy("id", "desc");
        $stockerDetailCount = $stockerDetail->first() ? str_replace("WIP-", "", $stockerDetail->first()->kode) + 1 : 1;

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
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                array_push($detailItemArr, [
                    'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-" . ($stockerDetailCount + $n),
                    'no_cut_size' => $noCutSize . sprintf('%04s', ($i)),
                    'size' => $request['size'][$index],
                    'so_det_id' => $request['so_det_id'][$index],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                $n++;
            }
        }

        if (count($storeDetailItemArr) > 0) {
            $storeDetailItem = StockerDetail::insert($storeDetailItemArr);
        }

        // generate pdf
        $customPaper = array(0, 0, 56.70, 33.39);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering', ["ws" => $request["no_ws"], "color" => $request["color"], "no_cut" => $request["no_cut"], "dataNumbering" => $detailItemArr])->setPaper($customPaper);

        $path = public_path('pdf/');
        $fileName = str_replace("/", "-", ($request["no_ws"]. '-' . $request["color"] . '-' . $request["no_cut"] . '-Numbering.pdf'));
        $pdf->save($path . '/' . $fileName);
        $generatedFilePath = public_path('pdf/' . $fileName);

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
            $stockerForm = Stocker::where("form_cut_id", $formCut->id_form)->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

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
                    if (isset($sizeRangeAkhir[$stocker->size]) && ($currentStockerSize != $stocker->size || $currentStockerGroup != $stocker->group_stocker || $currentStockerRatio != $stocker->ratio)) {
                        if ($stockerForm->min("group_stocker") == $stocker->group_stocker && $stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {
                            $modifyThis = $modifySizeQty->where("so_det_id", $stocker->so_det_id)->first();

                            if ($modifyThis) {
                                $lembarGelaran = $lembarGelaran + $modifyThis->difference_qty;
                            }
                        }

                        $rangeAwal = $sizeRangeAkhir[$stocker->size] + 1;
                        $sizeRangeAkhir[$stocker->size] = $sizeRangeAkhir[$stocker->size] + $lembarGelaran;

                        $currentStockerSize = $stocker->size;
                        $currentStockerGroup = $stocker->group_stocker;
                        $currentStockerRatio = $stocker->ratio;
                    } else if (!isset($sizeRangeAkhir[$stocker->size])) {
                        $rangeAwal =  1;
                        $sizeRangeAkhir->put($stocker->size, $lembarGelaran);
                    }
                }

                ($stocker->size && (($sizeRangeAkhir[$stocker->size] - ($rangeAwal-1)) != $stocker->qty) ? $stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->size] - ($rangeAwal-1)) : $stocker->qty_ply_mod = 0);
                $stocker->range_awal = $rangeAwal;
                $stocker->range_akhir = $stocker->size ? $sizeRangeAkhir[$stocker->size] : 0;
                $stocker->save();
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
                if (isset($sizeRangeAkhir[$number->size])) {
                    if ($number->number > $sizeRangeAkhir[$number->size]) {
                        StockerDetail::where("form_cut_id", $number->form_cut_id)->
                            where("size", $number->size)->
                            where("number", ">", $sizeRangeAkhir[$number->size])->
                            update([
                                "cancel" => "Y"
                            ]);
                    } else {
                        StockerDetail::where("form_cut_id", $number->form_cut_id)->
                            where("size", $number->size)->
                            where("number", "<=", $sizeRangeAkhir[$number->size])->
                            where("cancel", "Y")->
                            update([
                                "cancel" => "N"
                            ]);
                    }

                    if ($number->number < $sizeRangeAkhir[$number->size]) {
                        $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
                        $noCutSize = substr($number->no_cut_size, 0, strlen($number->size)+2);

                        $no = 0;
                        for ($i = $number->number; $i < $sizeRangeAkhir[$number->size]; $i++) {
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
                $stockerForm = Stocker::where("form_cut_id", $formCut->id_form)->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

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
                        if (isset($sizeRangeAkhir[$stocker->size]) && ($currentStockerSize != $stocker->size || $currentStockerGroup != $stocker->group_stocker || $currentStockerRatio != $stocker->ratio)) {
                            if ($stockerForm->min("group_stocker") == $stocker->group_stocker && $stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {
                                $modifyThis = $modifySizeQty->where("so_det_id", $stocker->so_det_id)->first();

                                if ($modifyThis) {
                                    $lembarGelaran = $lembarGelaran + $modifyThis->difference_qty;
                                }
                            }

                            $rangeAwal = $sizeRangeAkhir[$stocker->size] + 1;
                            $sizeRangeAkhir[$stocker->size] = $sizeRangeAkhir[$stocker->size] + $lembarGelaran;

                            $currentStockerSize = $stocker->size;
                            $currentStockerGroup = $stocker->group_stocker;
                            $currentStockerRatio = $stocker->ratio;
                        } else if (!isset($sizeRangeAkhir[$stocker->size])) {
                            $rangeAwal =  1;
                            $sizeRangeAkhir->put($stocker->size, $lembarGelaran);
                        }
                    }

                    ($sizeRangeAkhir[$stocker->size] - ($rangeAwal-1)) != $stocker->qty && $stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->size] - ($rangeAwal-1));
                    $stocker->range_awal = $rangeAwal;
                    $stocker->range_akhir = $sizeRangeAkhir[$stocker->size];
                    $stocker->save();
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
                    if (isset($sizeRangeAkhir[$number->size])) {
                        if ($number->number > $sizeRangeAkhir[$number->size]) {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("size", $number->size)->
                                where("number", ">", $sizeRangeAkhir[$number->size])->
                                update([
                                    "cancel" => "Y"
                                ]);
                        } else {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("size", $number->size)->
                                where("number", "<=", $sizeRangeAkhir[$number->size])->
                                where("cancel", "Y")->
                                update([
                                    "cancel" => "N"
                                ]);
                        }

                        if ($number->number < $sizeRangeAkhir[$number->size]) {
                            $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
                            $noCutSize = substr($number->no_cut_size, 0, strlen($number->size)+2);

                            $no = 0;
                            for ($i = $number->number; $i < $sizeRangeAkhir[$number->size]; $i++) {
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
}
