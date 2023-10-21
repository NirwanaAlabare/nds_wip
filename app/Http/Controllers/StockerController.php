<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\Marker;
use App\Models\MarkerDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
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
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
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
                    panel,
                    b.color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    a.qty_ply,
                    b.gelar_qty,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    a.total_lembar,
                    GROUP_CONCAT(CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio * a.total_lembar, ')') ORDER BY master_size_new.urutan ASC) marker_details
                FROM `form_cut_input` a
                left join marker_input b on a.id_marker = b.kode
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                where a.status = 'SELESAI PENGERJAAN'
                " . $additionalQuery . "
                " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY a.updated_at desc
            ");

            return json_encode([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval(count($data_spreading)),
                "recordsFiltered" => intval(count($data_spreading)),
                "data" => $data_spreading
            ]);
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
    public function show($id)
    {
        $dataSpreading = FormCutInput::selectRaw("
                form_cut_input.id,
                form_cut_input.no_meja,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                form_cut_input.tgl_form_cut,
                marker_input.id marker_id,
                marker_input.act_costing_ws ws,
                marker_input.buyer,
                panel,
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
                GROUP_CONCAT(CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio * form_cut_input.total_lembar, ')') ORDER BY master_size_new.urutan ASC) marker_details
            ")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
            leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            where("form_cut_input.id", $id)->
            where("marker_input_detail.ratio", ">", "0")->
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
                no_form_cut_input = '".$request['no_form_cut']."' AND
                tgl_form_cut_input = '".$request['tgl_form_cut']."' AND
                so_det_id = '".$request['so_det_id'][$index]."' AND
                color = '".$request['color']."' AND
                panel = '".$request['panel']."' AND
                shade = '".$request['shade']."' AND
                ratio = '".$request['ratio'][$index]."'
            ")->first();

        $stockerId = $checkStocker ? $checkStocker->id_qr_stocker : "STK-".$stockerCount;

        $storeItem = Stocker::updateOrCreate(
            [
                'no_form_cut_input' => $request['no_form_cut'],
                'tgl_form_cut_input' => $request['tgl_form_cut'],
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
            $dataSpreading = Stocker::selectRaw("
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
                leftJoin("form_cut_input", "form_cut_input.no_form", "=", "stocker_input.no_form_cut_input")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "marker_input_detail.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                where("form_cut_input.no_form", $storeItem->no_form_cut_input)->
                where("stocker_input.id", $storeItem->id)->
                where("marker_input_detail.size", $storeItem->size)->
                groupBy("form_cut_input.id")->
                first();

            // decode qr code
            $qrCodeDecode = base64_encode(QrCode::format('svg')->size(100)->generate($storeItem->id."-".$storeItem->id_qr_stocker));

            // generate pdf
            PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
            $pdf = PDF::loadView('stocker.pdf.print-stocker', ["dataSpreading" => $dataSpreading, "qrCode" => $qrCodeDecode])->setPaper('a7', 'landscape');

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
                no_form_cut_input = '".$request['no_form_cut']."' AND
                tgl_form_cut_input = '".$request['tgl_form_cut']."' AND
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
                'no_form_cut_input' => $request['no_form_cut'],
                'tgl_form_cut_input' => $request['tgl_form_cut'],
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
