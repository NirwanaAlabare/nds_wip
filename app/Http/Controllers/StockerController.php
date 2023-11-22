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
            $formCutInputs = PartDetail::selectRaw("
                    part_detail.id part_detail_id,
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
                    form_cut_input.qty_ply total_lembar,
                    part_form.kode kode_part_form,
                    part.kode kode_part,
                    master_part.nama_part
                ")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_form", "part_form.part_id", "=", "part.id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "part_form.form_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                whereRaw("part_form.id is not null")->
                groupBy("part_detail.id", "form_cut_input.id")->
                orderBy("no_form", "desc");

                return Datatables::eloquent($formCutInputs)->
                    filter(function ($query) {
                        if (request()->has('dateFrom')) {
                            $query->where('form_cut_input.tgl_form_cut', '>=', request('dateFrom'));
                        }

                        if (request()->has('dateTo')) {
                            $query->where('form_cut_input.tgl_form_cut', '<=', request('dateTo'));
                        }
                    })->
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
                    })->filterColumn('kode_part_form', function ($query, $keyword) {
                        $query->whereRaw("LOWER(part_form.kode) LIKE LOWER('%" . $keyword . "%')");
                    })->filterColumn('kode_part', function ($query, $keyword) {
                        $query->whereRaw("LOWER(part.kode) LIKE LOWER('%" . $keyword . "%')");
                    })->order(function ($query) {
                        $query->
                            orderBy('form_cut_input.updated_at', 'desc')->
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
        $dataSpreading = PartDetail::selectRaw("
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
                UPPER(form_cut_input.shell) shell,
                GROUP_CONCAT(CONCAT(master_size_new.size, ' ') ORDER BY master_size_new.urutan ASC) sizes,
                GROUP_CONCAT(CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio * form_cut_input.total_lembar, ')') ORDER BY master_size_new.urutan ASC) marker_details,
                master_part.nama_part part
            ")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("part", "part.id", "=", "part_detail.part_id")->
            leftJoin("part_form", "part_form.part_id", "=", "part.id")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "part_form.form_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("part_detail.id", $partDetailId)->
            where("form_cut_input.id", $formCutId)->
            groupBy("form_cut_input.id")->
            first();

        $dataRatio = MarkerDetail::where("marker_id", $dataSpreading->marker_id)->where("ratio", ">", "0")->orderBy("id", "asc")->get();

        return view("stocker.stocker-detail", ["dataSpreading" => $dataSpreading, "dataRatio" => $dataRatio, "page" => "dashboard-stocker"]);
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
        $stockerCount = Stocker::count() + 1;

        $checkStocker = Stocker::select("id_qr_stocker")->whereRaw("
                part_detail_id = '".$request['part_detail_id']."' AND
                form_cut_id = '".$request['form_cut_id']."' AND
                so_det_id = '".$request['so_det_id'][$index]."' AND
                color = '".$request['color']."' AND
                panel = '".$request['panel']."' AND
                shade = '".$request['shade']."' AND
                ratio = '".$request['ratio'][$index]."'
            ")->first();

        $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-".$stockerCount;

        $storeItem = Stocker::updateOrCreate(
            [
                'part_detail_id' => $request['part_detail_id'],
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
                'range_awal' => 1,
                'range_akhir' => $request['qty_cut'][$index],
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
