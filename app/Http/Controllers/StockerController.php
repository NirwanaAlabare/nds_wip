<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\PartDetail;
use Illuminate\Http\Request;
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
                    form_cut_input.tgl_form_cut,
                    users.name nama_meja,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.urutan_marker,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    GROUP_CONCAT(DISTINCT CONCAT(master_size_new.size, '(', marker_input_detail.ratio, ')') SEPARATOR ', ') marker_details,
                    form_cut_input.no_cut,
                    form_cut_input.total_lembar,
                    part_form.kode kode_part_form,
                    part.kode kode_part,
                    GROUP_CONCAT(DISTINCT master_part.nama_part) nama_part
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

                return Datatables::of($formCutInputs)->
                    filter(function ($query) {
                        if (request()->has('dateFrom')) {
                            $query->where('form_cut_input.tgl_form_cut', '>=', request('dateFrom'));
                        }

                        if (request()->has('dateTo')) {
                            $query->where('form_cut_input.tgl_form_cut', '<=', request('dateTo'));
                        }
                    }, true)->filterColumn('id_marker', function ($query, $keyword) {
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
                        $query->
                            orderBy('marker_input.act_costing_ws', 'desc')->
                            orderBy('form_cut_input.no_cut', 'asc')->
                            orderBy('form_cut_input.waktu_selesai', 'asc')->
                            orderByRaw('FIELD(form_cut_input.tipe_form_cut, null, "NORMAL", "MANUAL")');
                    })->toJson();
        }

        return view("stocker.stocker", ["page" => "dashboard-stocker"]);
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
    public function show($partDetailId = 0, $formCutId = 0)
    {
        $dataSpreading = FormCutInput::selectRaw("
                part_detail.id part_detail_id,
                form_cut_input.id form_cut_id,
                form_cut_input.no_meja,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                form_cut_input.tgl_form_cut,
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
                GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') part
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

        $dataPartDetail = PartDetail::select("part_detail.id", "master_part.nama_part")->leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("part", "part.id", "part_detail.part_id")->
            leftJoin("part_form", "part_form.part_id", "part.id")->
            leftJoin("form_cut_input", "form_cut_input.id", "part_form.form_id")->
            where("form_cut_input.id", $formCutId)->
            get();

        $dataRatio = MarkerDetail::selectRaw("
                marker_input_detail.id marker_detail_id,
                marker_input_detail.so_det_id,
                marker_input_detail.size,
                marker_input_detail.ratio,
                stocker_input.id stocker_id
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("stocker_input", function($join) {
                $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                $join->on("stocker_input.part_detail_id", "=", "part_detail.id");
                $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_id", $dataSpreading->marker_id)->
            where("marker_input_detail.ratio", ">", "0")->
            orderBy("marker_input_detail.id", "asc")->
            groupBy("marker_input_detail.id")->
            get();

        $dataStocker = MarkerDetail::selectRaw("
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                part_detail.id part_detail_id,
                form_cut_input.no_cut,
                stocker_input.id stocker_id,
                stocker_input.range_awal,
                stocker_input.range_akhir
            ")->
            leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
            leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
            leftJoin("part", "part.id", "=", "part_form.part_id")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("stocker_input", function($join) {
                $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                $join->on("stocker_input.part_detail_id", "=", "part_detail.id");
                $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
            })->
            where("marker_input.act_costing_ws", $dataSpreading->ws)->
            where("marker_input.color", $dataSpreading->color)->
            where("marker_input.panel", $dataSpreading->panel)->
            where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
            groupBy("no_cut", "marker_input_detail.so_det_id", "part_detail.id")->
            get();

        return view("stocker.stocker-detail", ["dataSpreading" => $dataSpreading, "dataPartDetail" => $dataPartDetail,"dataRatio" => $dataRatio, "dataStocker" => $dataStocker, "page" => "dashboard-stocker"]);
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

    public function countStockerUpdate(Request $request) {
        $stockerGroups = Stocker::groupBy("so_det_id", "color", "panel", "part_detail_id")->
            orderBy("id", "asc")->
            get();

        $updatedStocker = [];
        foreach ($stockerGroups as $stockerGroup) {
            $i = 0;
            $rangeAkhir = 0;
            $formBefore = null;

            $stockers = Stocker::where("so_det_id", $stockerGroup->so_det_id)->
                where("color", $stockerGroup->color)->
                where("panel", $stockerGroup->panel)->
                where("part_detail_id", $stockerGroup->part_detail_id)->
                orderBy("id", "asc")->
                orderBy("form_cut_id", "asc")->
                get();

            foreach ($stockers as $stocker) {
                $i++;

                if ($stocker->form_cut_input == $formBefore) {
                    $rangeAkhir = 0;
                }

                $rangeAwal = $rangeAkhir + 1;
                $rangeAkhir = $rangeAkhir + ($stocker->qty_cut);

                $updateStockerCount = Stocker::where("id", $stocker->id)->
                    update([
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

    public function printStocker(Request $request, $index)
    {
        $stockerCount = Stocker::count() + 1;

        $checkStocker = Stocker::select("id_qr_stocker", "range_awal", "range_akhir")->whereRaw("
                part_detail_id = '".$request['part_detail_id'][$index]."' AND
                form_cut_id = '".$request['form_cut_id']."' AND
                so_det_id = '".$request['so_det_id'][$index]."' AND
                color = '".$request['color']."' AND
                panel = '".$request['panel']."' AND
                shade = '".$request['shade']."' AND
                ratio = '".$request['ratio'][$index]."'
            ")->first();

        $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-".$stockerCount;
        $rangeAwal = $checkStocker ? $checkStocker->range_awal :  $request['range_awal'][$index];
        $rangeAkhir = $checkStocker ? $checkStocker->range_akhir : $request['range_akhir'][$index];

        $storeItem = Stocker::updateOrCreate(
            [
                'part_detail_id' => $request['part_detail_id'][$index],
                'form_cut_id' => $request['form_cut_id'],
                'so_det_id' => $request['so_det_id'][$index],
                'color' => $request['color'],
                'panel' => $request['panel'],
                'shade' => $request['shade'],
                'ratio' => $request['ratio'][$index],
                'id_qr_stocker' => $stockerId
            ],
            [
                'act_costing_ws' => $request["no_ws"],
                'size' => $request["size"][$index],
                'qty_ply' => $request['qty_ply'],
                'qty_cut' => $request['qty_cut'][$index],
                'range_awal' => $rangeAwal,
                'range_akhir' => $rangeAkhir
            ]
        );

        if ($storeItem) {
            $dataStocker = Stocker::selectRaw("
                    stocker_input.qty_cut bundle_qty,
                    stocker_input.size,
                    stocker_input.range_awal,
                    stocker_input.range_akhir,
                    stocker_input.id_qr_stocker,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.style,
                    marker_input.color,
                    stocker_input.shade
                ")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_form", "part_form.part_id", "=", "part.id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                where("part_detail.id", $storeItem->part_detail_id)->
                where("form_cut_input.id", $storeItem->form_cut_id)->
                where("stocker_input.id", $storeItem->id)->
                where("marker_input_detail.size", $storeItem->size)->
                groupBy("form_cut_input.id")->
                first();

            // decode qr code
            $qrCodeDecode = base64_encode(QrCode::format('svg')->size(100)->generate($storeItem->id."-".$storeItem->id_qr_stocker));

            // generate pdf
            PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
            $pdf = PDF::loadView('stocker.pdf.print-stocker', ["dataStocker" => $dataStocker, "qrCode" => $qrCodeDecode])->setPaper('a7', 'landscape');

            $path = public_path('pdf/');
            $fileName = 'stocker-'.$storeItem->id.'.pdf';
            $pdf->save($path . '/' . $fileName);
            $generatedFilePath = public_path('pdf/'.$fileName);

            return response()->download($generatedFilePath);
        }
    }

    public function printNumbering(Request $request, $index)
    {
        $stockerCount = Stocker::count() + 1;

        $checkStocker = Stocker::whereRaw("
                part_detail_id = '".$request['part_detail_id']."' AND
                form_cut_id = '".$request['form_cut_id']."' AND
                so_det_id = '".$request['so_det_id'][$index]."' AND
                panel = '".$request['panel']."' AND
                shade = '".$request['shade']."' AND
                ratio = '".$request['ratio'][$index]."'
            ")->first();

        $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-".$stockerCount;

        $idStocker = "";
        $kodeStocker = "";
        $wsStocker = "";
        $colorStocker = "";
        $stockerDetailCount = StockerDetail::count();
        if ($checkStocker) {
            $idStocker = $checkStocker->id;
            $kodeStocker = $checkStocker->id_qr_stocker;
            $wsStocker = $checkStocker->act_costing_ws;
            $colorStocker = $checkStocker->color;
        } else {
            $storeItem = Stocker::create([
                'part_detail_id' => $request['part_detail_id'],
                'form_cut_id' => $request['form_cut_id'],
                'so_det_id' => $request['so_det_id'][$index],
                'color' => $request['color'],
                'panel' => $request['panel'],
                'shade' => $request['shade'],
                'ratio' => $request['ratio'][$index],
                'id_qr_stocker' => $stockerId,
                'act_costing_ws' => $request["no_ws"],
                'size' => $request["size"][$index],
                'qty_ply' => $request['qty_ply'],
                'qty_cut' => $request['qty_cut'][$index],
                'range_awal' => 1,
                'range_akhir' => $request['qty_cut'][$index],
            ]);

            $idStocker = $storeItem->id;
            $kodeStocker = $storeItem->id_qr_stocker;
            $wsStocker = $storeItem->act_costing_ws;
            $colorStocker = $storeItem->color;
        }

        $now = Carbon::now();
        $noCutSize = $request["size"][$index]."".sprintf('%02s', $idStocker);
        $detailItemArr = [];
        $storeDetailItemArr = [];
        $qrCodeDetailItemArr = [];

        for ($i = 0; $i < intval($request['qty_cut'][$index]); $i++) {
            $checkStockerDetailData = StockerDetail::where('id_stocker', $idStocker)->where('no_cut_size', $noCutSize.sprintf('%04s', ($i+1)))->first();

            if (!$checkStockerDetailData) {
                array_push($storeDetailItemArr, [
                    'kode' => "WIP-".(($stockerDetailCount+1)+$i),
                    'no_cut_size' => $noCutSize.sprintf('%04s', ($i+1)),
                    'id_stocker' => $idStocker,
                    'size' => $request['size'][$index],
                    'id_so_det' => $request['so_det_id'][$index],
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            array_push($detailItemArr, [
                'kode' => $checkStockerDetailData ? $checkStockerDetailData->kode : "WIP-".(($stockerDetailCount+1)+$i),
                'no_cut_size' => $noCutSize.sprintf('%04s', ($i+1)),
                'id_stocker' => $idStocker,
                'size' => $request['size'][$index],
                'id_so_det' => $request['so_det_id'][$index],
                'created_at' => $now,
                'updated_at' => $now
            ]);

            array_push($qrCodeDetailItemArr, base64_encode(QrCode::format('svg')->size(100)->generate("WIP-".($stockerDetailCount+1)."-".$noCutSize.($i+1)."-".$idStocker."-".$request['so_det_id'][$index])));
        }

        $storeDetailItem = StockerDetail::insert($storeDetailItemArr);

        // decode qr code
        // $qrCodeDecode = base64_encode(QrCode::format('svg')->size(100)->generate($storeDeItem->id."-".$storeItem->id_qr_stocker));

        // generate pdf
        $customPaper = array(0,0,56.70,28.38);
        $pdf = PDF::loadView('stocker.pdf.print-numbering', ["kode" => $kodeStocker, "ws" => $wsStocker, "color" => $colorStocker, "dataNumbering" => $detailItemArr, "qrCode" => $qrCodeDetailItemArr])->setPaper($customPaper);

        $path = public_path('pdf/');
        $fileName = 'stocker-'.$idStocker.'.pdf';
        $pdf->save($path . '/' . $fileName);
        $generatedFilePath = public_path('pdf/'.$fileName);

        return response()->download($generatedFilePath);
    }
}
