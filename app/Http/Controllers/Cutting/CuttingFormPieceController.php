<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\Cutting\FormCutPieceDetailSize;
use App\Services\CuttingPieceService;
use App\Models\Part\PartDetail;
use App\Models\Cutting\ScannedItem;
use App\Models\Part\Part;
use App\Models\Part\PartForm;
use App\Models\Stocker\Stocker;
use App\Models\Hris\MasterEmployee;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Cutting\ExportCuttingFormReject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class CuttingFormPieceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

            // $formCutPiece = FormCutPiece::whereNull("tanggal")->orWhereBetween("tanggal", [$dateFrom, $dateTo]);
            $formCutPiece = FormCutPiece::selectRaw("
                form_cut_piece.*,
                EXISTS (
                    SELECT 1
                    FROM stocker_input
                    WHERE stocker_input.form_piece_id = form_cut_piece.id
                ) as has_stocker_input
            ")
            ->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereNull("tanggal")
                ->orWhereBetween("tanggal", [$dateFrom, $dateTo]);
            });

            return DataTables::eloquent($formCutPiece)->
                addColumn('sizes', function ($row) {
                    if ($row->formCutPieceDetailSizes) {
                        $sizeArr = $row->formCutPieceDetailSizes->groupBy("so_det_id");
                        $sizeText = "";
                        foreach ($sizeArr as $sizes) {
                            $size = $sizes->first();
                            $sizeText .= $size->size.(($size->dest && $size->dest != "-" ? " - ".$size->dest : "").($sizes->sum("qty") > 0 ? " (".$sizes->sum("qty").")" : "")." / ");
                        }

                        return $sizeText;
                    }
                })->
                addColumn('qty', function ($row) {
                    $qty = $row->formCutPieceDetails ? $row->formCutPieceDetails->sum("qty_pemakaian") : "-";

                    return $qty;
                })->
                toJSON();
        }

        return view("cutting.cutting-form-piece.cutting-form-piece", ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "cutting-piece"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (session('currentFormCutPiece')) {
            return redirect()->route('process-cutting-piece', ["id" => session('currentFormCutPiece')]);
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view("cutting.cutting-form-piece.create-cutting-form-piece", ["orders" => $orders, "page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "cutting-piece"]);
    }

    public function process($id = 0)
    {
        $cuttingFormPiece = FormCutPiece::find($id);

        if (!$cuttingFormPiece) {
            session()->forget('currentFormCutPiece');

            return redirect()->route('create-cutting-piece');
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('cutting.cutting-form-piece.create-cutting-form-piece', ['cuttingFormPiece' => $cuttingFormPiece, 'orders' => $orders, 'page' => 'dashboard-cutting', "subPageGroup" => "proses-cutting", "subPage" => "cutting-piece"]);
    }

    public function createNew() {
        session()->forget('currentFormCutPiece');

        return redirect()->route('create-cutting-piece');
    }

    public function generateCode()
    {
        $date = date('Y-m-d');
        $hari = substr($date, 8, 2);
        $bulan = substr($date, 5, 2);
        $now = Carbon::now();

        $lastForm = FormCutPiece::select("no_form")->whereRaw("no_form LIKE 'FP".$hari."-".$bulan."%'")->orderBy("id", "desc")->first();
        $urutan =  $lastForm ? (str_replace("FP".$hari."-".$bulan."-", "", $lastForm->no_form) + 1) : 1;

        $noForm = "FP".$hari."-".$bulan."-".$urutan;

        $form = FormCutPiece::create([
            "no_form" => $noForm,
            "waktu_mulai" => Carbon::now()
        ]);

        session(['currentFormCutPiece' => $form->id]);

        return $form;
    }

    public function incompleteItem($id = 0)
    {
        if ($id) {
            $incomplete = FormCutPieceDetail::with("scannedItem", "scannedItem.penerimaanCutting", "formCutPieceDetailSizes")->where("form_id", $id)->orderBy("id", "asc")->get();

            return $incomplete ? $incomplete : null;
        }

        return array(
            "status" => 400,
            "message" => "Incomplete tidak valid.",
            "additional" => [],
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        switch ($request->process) {
            case 1 :
                $validatedRequest = $request->validate([
                    "id" => "required",
                    "no_form" => "required",
                    "tanggal" => "required",
                    "act_costing_id" => "required",
                    "act_costing_ws" => "required",
                    "buyer_id" => "required",
                    "buyer" => "required",
                    "style" => "required",
                    "color" => "required",
                    "panel" => "required",
                    "cons_ws" => "required|gt:0",
                    "unit_cons_ws" => "required|in:PCS",
                    "employee_id" => "required",
                    "employee_nik" => "required",
                    "employee_name" => "required",
                ],
                [
                    "cons_ws.gt" => "Cons. WS harus lebih dari 0.",
                    "unit_cons_ws.in" => "Unit Cons. WS harus bernilai 'PCS'."
                ]);

                $storeFormCutPiece = FormCutPiece::updateOrCreate([
                    "id" => $validatedRequest["id"],
                    "no_form" => $validatedRequest["no_form"]
                ],[
                    "tanggal" => $validatedRequest["tanggal"],
                    "process" => $request->process,
                    "act_costing_id" => $validatedRequest["act_costing_id"],
                    "act_costing_ws" => $validatedRequest["act_costing_ws"],
                    "buyer_id" => $validatedRequest["buyer_id"],
                    "buyer" => $validatedRequest["buyer"],
                    "style" => $validatedRequest["style"],
                    "color" => $validatedRequest["color"],
                    "panel" => $validatedRequest["panel"],
                    "cons_ws" => $validatedRequest["cons_ws"],
                    "unit_cons_ws" => $validatedRequest["unit_cons_ws"],
                    "employee_id" => $validatedRequest["employee_id"],
                    "employee_nik" => $validatedRequest["employee_nik"],
                    "employee_name" => $validatedRequest["employee_name"],
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username
                ]);

                if ($storeFormCutPiece) {
                    logHistory(
                        $storeFormCutPiece->id,
                        $storeFormCutPiece->toArray()
                    );

                    session(['currentFormCutPiece' => $storeFormCutPiece->id]);

                    return array(
                        "status" => 200,
                        "message" => "Process Form Cut Piece berhasil disimpan.",
                        "additional" => $storeFormCutPiece,
                    );
                }

                break;
            case 2 :
                $validatedRequest = $request->validate([
                    "id" => "required",
                    "method" => "required",
                    "id_item" => "required",
                    "detail_item" => "required",
                    "qty_item" => "required",
                    "unit_qty_item" => "required"
                ]);

                $idRoll = $validatedRequest["method"] == "scan" ? $request->kode_barang : "-";
                $idItem = $validatedRequest["id_item"];
                $detailItem = $validatedRequest["detail_item"];
                $qtyItem = $validatedRequest["qty_item"];
                $unitQtyItem = $validatedRequest["unit_qty_item"];

                $formCutPieceModel = FormCutPiece::where("id", $validatedRequest["id"]);

                if ($formCutPieceModel) {
                    $storeFormCutPieceDetailArr = [];

                    if ($validatedRequest["method"] == "scan") {
                        $validatedRequestDetail = $request->validate([
                            "kode_barang" => "required",
                        ]);

                        $scannedItem = ScannedItem::where("id_roll", strtoupper($validatedRequestDetail["kode_barang"]))->first();

                        if ($scannedItem) {
                            $storeFormCutPieceDetailArr = [
                                "form_id" => $validatedRequest["id"],
                                "method" => $validatedRequest["method"],
                                "id_roll" => strtoupper($validatedRequestDetail["kode_barang"]),
                                "id_item" => $scannedItem->id_item,
                                "detail_item" => $scannedItem->detail_item,
                                "lot" => $scannedItem->lot,
                                "roll" => $scannedItem->roll,
                                "roll_buyer" => $scannedItem->roll_buyer,
                                "rule_bom" => $scannedItem->rule_bom,
                                "qty_pengeluaran" => $scannedItem->qty_in,
                                "qty" => $scannedItem->qty,
                                "qty_unit" => $scannedItem->unit,
                                "status" => "incomplete",
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username
                            ];
                        } else {
                            return array(
                                "status" => 400,
                                "message" => "Item tidak ditemukan.",
                                "additional" => [],
                            );
                        }
                    } else if ($validatedRequest["method"] == "select") {
                        $storeFormCutPieceDetailArr = [
                            "form_id" => $validatedRequest["id"],
                            "method" => $validatedRequest["method"],
                            "id_item" => $validatedRequest["id_item"],
                            "detail_item" => $validatedRequest["detail_item"],
                            "qty_pengeluaran" => $validatedRequest["qty_item"],
                            "qty" => $validatedRequest["qty_item"],
                            "qty_unit" => $validatedRequest["unit_qty_item"],
                            "status" => "incomplete",
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username
                        ];
                    }

                    $storeFormCutPieceDetail = FormCutPieceDetail::create($storeFormCutPieceDetailArr);

                    if ($storeFormCutPieceDetail) {
                        $updateFormCutPiece = $formCutPieceModel->update([
                            "process" => $request->process,
                        ]);

                        if ($updateFormCutPiece) {

                            $dataLog = FormCutPiece::where("id", $validatedRequest["id"])->first();
                            logHistory(
                                $dataLog->id,
                                $dataLog->toArray()
                            );

                            $thisFormCutPieceDetail = FormCutPieceDetail::with("scannedItem", "scannedItem.penerimaanCutting")->where("id", $storeFormCutPieceDetail->id)->first();

                            return array(
                                "status" => 200,
                                "message" => "Item berhasil disimpan.",
                                "additional" => $thisFormCutPieceDetail,
                            );
                        }
                    }
                }

                break;
            case 3 :
                $validatedRequest = $request->validate([
                    "id" => "required",
                    "id_detail" => "required",
                    "group_roll" => "required",
                    "lot" => "nullable",
                    "roll" => "nullable",
                    "roll_buyer" => "nullable",
                    "rule_bom" => "nullable",
                    "qty_pengeluaran" => "required",
                    "qty" => "required",
                    "qty_pemakaian" => "required|gt:0",
                    "qty_sisa" => "required",
                    "qty_unit" => "required|in:PCS",
                ], [
                    "qty_pemakaian.gt" => "Harap isi qty piece."
                ]);

                $cuttingPieceModel = FormCutPiece::where("id", $validatedRequest["id"]);
                $cuttingPieceDetailModel = FormCutPieceDetail::where("id", $validatedRequest["id_detail"]);

                if ($cuttingPieceDetailModel) {
                    $updateCuttingPieceDetail = $cuttingPieceDetailModel->update([
                        "id" => $validatedRequest['id_detail'],
                        "group_roll" => $validatedRequest['group_roll'],
                        "lot" => $validatedRequest['lot'],
                        "roll" => $validatedRequest['roll'],
                        "roll_buyer" => $validatedRequest['roll_buyer'],
                        "rule_bom" => $validatedRequest['rule_bom'],
                        "qty_pemakaian" => $validatedRequest['qty_pemakaian'],
                        "qty_sisa" => $validatedRequest['qty_sisa'],
                        "qty_unit" => $validatedRequest['qty_unit'],
                        "status" => "complete",
                    ]);

                    if ($updateCuttingPieceDetail) {
                        // Scanned Item Qty
                        if ($cuttingPieceDetailModel->first() && $cuttingPieceDetailModel->first()->method == "scan" && $cuttingPieceDetailModel->first()->id_roll) {
                            $scannedItem = ScannedItem::where("id_roll", $cuttingPieceDetailModel->first()->id_roll)->first();

                            if ($scannedItem) {
                                $scannedItem->qty = $validatedRequest['qty_sisa'];
                                $scannedItem->qty_pakai += $validatedRequest['qty_pemakaian'];
                                $scannedItem->save();
                            }
                        }

                        // Upsert Form Cut Piece Detail Size
                        if ($request->so_det_id && count($request->so_det_id) > 0) {
                            $cuttingPieceDetailSizeArr = [];
                            for($i = 0; $i < count($request->so_det_id); $i++) {
                                if ($request->so_det_id[$i] && $request->size[$i]) {
                                    array_push($cuttingPieceDetailSizeArr, [
                                        "form_detail_id" => $validatedRequest["id_detail"],
                                        "so_det_id" => $request->so_det_id[$i],
                                        "size" => $request->size[$i],
                                        "dest" => $request->dest[$i],
                                        "qty" => $request->qty_detail[$i] ? $request->qty_detail[$i] : 0,
                                        "qty_aktual" => $request->qty_detail[$i] ? ($request->qty_detail[$i] / ($cuttingPieceModel && $cuttingPieceModel->first() && $cuttingPieceModel->first()->cons_ws ? $cuttingPieceModel->first()->cons_ws : 1)) : 0,
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username
                                    ]);
                                }
                            }

                            $storeCuttingPieceDetailSize = FormCutPieceDetailSize::upsert($cuttingPieceDetailSizeArr, ['form_detail_id', 'so_det_id'], ['size', 'qty', 'created_by', 'created_by_username']);
                        }

                        // Update Form Cut Piece
                        $updateCuttingPiece = $cuttingPieceModel->update([
                            "process" => 1,
                            // "status" => 'complete',
                        ]);

                        // session()->forget('currentFormCutPiece');

                        // // finishing
                        // $this->finishProcess($validatedRequest["id"]);

                        $dataLog = FormCutPiece::where("id", $validatedRequest["id"])->first();
                        logHistory(
                            $dataLog->id,
                            $dataLog->toArray()
                        );

                        return array(
                            "status" => 200,
                            "message" => "Data Cutting Pcs berhasil disimpan.",
                            "additional" => $cuttingPieceModel->first()
                        );
                    }
                }

                break;
            case 4 :
                $validatedRequest = $request->validate([
                    "id" => "required"
                ]);

                $cuttingPieceModel = FormCutPiece::where("id", $validatedRequest["id"]);

                // Update Form Cut Piece
                $updateCuttingPiece = $cuttingPieceModel->update([
                    "process" => 3,
                    "waktu_selesai" => Carbon::now(),
                    "status" => 'complete',
                ]);

                $dataLog = FormCutPiece::where("id", $validatedRequest["id"])->first();
                logHistory(
                    $dataLog->id,
                    $dataLog->toArray()
                );

                session()->forget('currentFormCutPiece');

                // finishing
                $this->finishProcess($validatedRequest["id"]);

                return array(
                    "status" => 200,
                    "message" => "Data Cutting Pcs berhasil disimpan.",
                    "additional" => $cuttingPieceModel->first()
                );

                break;
            default :
                return array(
                    "status" => 400,
                    "message" => "Proses tidak ditemukan",
                    "additional" => [],
                );
        }
    }

    public function finishProcess($id = 0)
    {
        if ($id) {
            $currentForm = FormCutPiece::where("id", $id)->first();

            $formCutPieceSimilarLatestData = FormCutPiece::
                where("form_cut_piece.act_costing_ws", $currentForm->act_costing_ws)->
                where("form_cut_piece.color", $currentForm->color)->
                where("form_cut_piece.panel", $currentForm->panel)->
                where("form_cut_piece.status", "complete")->
                orderBy("form_cut_piece.waktu_selesai", "desc")->
                first();
            $formCutPieceSimilarLatest = $formCutPieceSimilarLatestData ? $formCutPieceSimilarLatestData->no_cut : 0;

            // delete incomplete detail
            $currentFormDetail = $currentForm->formCutPieceDetails()->where('status', 'incomplete')->get();
            foreach ($currentFormDetail as $formDetail) {
                $formDetail->formCutPieceDetailSizes()->delete();
                $formDetail->delete();
            }

            // store to part form
            $partData = Part::select('part.id')->
                where("act_costing_id", $currentForm->act_costing_id)->
                where("act_costing_ws", $currentForm->act_costing_ws)->
                where("panel", $currentForm->panel)->
                first();

            if ($partData) {
                $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
                $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
                $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

                $addToPartForm = PartForm::create([
                    "kode" => $kodePartForm,
                    "part_id" => $partData->id,
                    "form_pcs_id" => $currentForm->id,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
            }

            // update form
            $currentForm->no_cut = $formCutPieceSimilarLatest+1;
            $currentForm->waktu_selesai = Carbon::now();
            $currentForm->status = "complete";
            $currentForm->save();

            logHistory(
                $currentForm->id,
                $currentForm->toArray()
            );

            return true;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cutting\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function show($id = 0)
    {
        if ($id) {
            $formCutPiece = FormCutPiece::with("formCutPieceDetails")->where("id", $id)->first();

            $partDetails = PartDetail::selectRaw("
                    part_detail.id,
                    master_part.nama_part,
                    master_part.bag,
                    COALESCE(master_secondary.tujuan, '-') tujuan,
                    COALESCE(master_secondary.proses, '-') proses
                ")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("part", "part.id", "part_detail.part_id")->
                leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                where("part.act_costing_id", $formCutPiece->act_costing_id)->
                where("part.panel", $formCutPiece->panel)->
                groupBy("master_part.id")->
                get();

            return view("cutting.cutting-form-piece.show-cutting-form-piece", ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "cutting-piece", "formCutPiece" => $formCutPiece, "partDetails" => $partDetails]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cutting\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCutPiece $formCutPiece, $id = 0)
    {
        $cuttingFormPiece = FormCutPiece::find($id);
        $wsList = DB::table('master_sb_ws')->select('tgl_kirim', 'id_act_cost', 'ws')->distinct()->orderBy('tgl_kirim', 'desc')->limit(1000)->get();

        if ($cuttingFormPiece->process < 3 || $cuttingFormPiece->status != 'complete') {
            return redirect()->route("process-cutting-piece", ["id" => $cuttingFormPiece->id]);
        }

        return view("cutting.cutting-form-piece.edit-cutting-form-piece", ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "cutting-piece", 'cuttingFormPiece' => $cuttingFormPiece, 'wsList' => $wsList]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cutting\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormCutPiece $formCutPiece)
    {
        // Check Stocker
        $checkStocker = Stocker::where("form_piece_id", $request["id"])->first();
        if ($checkStocker) {
            return array(
                "status" => 400,
                "message" => "Form sudah memiliki Stocker",
                "additional" => [],
            );
        }

        switch ($request->process) {
            case 1 :
                $validatedRequest = $request->validate([
                    "id" => "required",
                    "no_form" => "required",
                    "tanggal" => "required",
                    "act_costing_id" => "required",
                    "act_costing_ws" => "required",
                    "buyer_id" => "required",
                    "buyer" => "required",
                    "style" => "required",
                    "color" => "required",
                    "panel" => "required",
                    "cons_ws" => "required|gt:0",
                    "unit_cons_ws" => "required|in:PCS",
                    "employee_id" => "required",
                    "employee_nik" => "required",
                    "employee_name" => "required",
                ],
                [
                    "cons_ws.gt" => "Cons. WS harus lebih dari 0.",
                    "unit_cons_ws.in" => "Unit Cons. WS harus bernilai 'PCS'."
                ]);

                // Update Cutting Piece
                $updateFormCutPiece = FormCutPiece::where("id", $validatedRequest["id"])->
                    where("no_form", $validatedRequest["no_form"])->
                    update([
                        "tanggal" => $validatedRequest["tanggal"],
                        "act_costing_id" => $validatedRequest["act_costing_id"],
                        "act_costing_ws" => $validatedRequest["act_costing_ws"],
                        "buyer_id" => $validatedRequest["buyer_id"],
                        "buyer" => $validatedRequest["buyer"],
                        "style" => $validatedRequest["style"],
                        "color" => $validatedRequest["color"],
                        "panel" => $validatedRequest["panel"],
                        "cons_ws" => $validatedRequest["cons_ws"],
                        "unit_cons_ws" => $validatedRequest["unit_cons_ws"],
                        "employee_id" => $validatedRequest["employee_id"],
                        "employee_nik" => $validatedRequest["employee_nik"],
                        "employee_name" => $validatedRequest["employee_name"],
                        "edited_by" => Auth::user()->id,
                        "edited_by_username" => Auth::user()->username,
                    ]);

                // Update so_det_id in detail size
                $details = DB::table('form_cut_piece_detail')
                    ->where('form_id', $validatedRequest['id'])
                    ->get();

                foreach ($details as $detail) {

                    $detailSizes = DB::table('form_cut_piece_detail_size')
                        ->where('form_detail_id', $detail->id)
                        ->get();

                    foreach ($detailSizes as $sizeDetail) {

                        $masterWs = DB::table('master_sb_ws')
                            ->select('id_so_det')
                            ->where('ws', $validatedRequest['act_costing_ws'])
                            ->where('size', $sizeDetail->size)
                            ->where('color', $validatedRequest['color'])
                            ->first();

                        if ($masterWs) {

                            DB::table('form_cut_piece_detail_size')
                                ->where('id', $sizeDetail->id)
                                ->update([
                                    'so_det_id' => $masterWs->id_so_det
                                ]);
                        }
                    }
                }

                if ($updateFormCutPiece) {
                    $updatedFormCutPiece = FormCutPiece::where("id", $validatedRequest["id"])->where("no_form", $validatedRequest["no_form"])->first();

                    logHistory(
                        $updatedFormCutPiece->id,
                        $updatedFormCutPiece->toArray()
                    );

                    session(['currentFormCutPiece' => $updatedFormCutPiece->id]);

                    return array(
                        "status" => 200,
                        "message" => "Process Form Cut Piece berhasil diupdate.",
                        "additional" => $updatedFormCutPiece,
                    );
                }

                break;
            // case 2 :
            //     $validatedRequest = $request->validate([
            //         "id" => "required",
            //         "method" => "required",
            //         "id_item" => "required",
            //         "detail_item" => "required",
            //         "qty_item" => "required",
            //         "unit_qty_item" => "required"
            //     ]);

            //     $idRoll = $validatedRequest["method"] == "scan" ? $request->kode_barang : "-";
            //     $idItem = $validatedRequest["id_item"];
            //     $detailItem = $validatedRequest["detail_item"];
            //     $qtyItem = $validatedRequest["qty_item"];
            //     $unitQtyItem = $validatedRequest["unit_qty_item"];

            //     $formCutPieceModel = FormCutPiece::where("id", $validatedRequest["id"]);

            //     if ($formCutPieceModel) {
            //         $storeFormCutPieceDetailArr = [];

            //         if ($validatedRequest["method"] == "scan") {
            //             $validatedRequestDetail = $request->validate([
            //                 "kode_barang" => "required",
            //             ]);

            //             $scannedItem = ScannedItem::where("id_roll", strtoupper($validatedRequestDetail["kode_barang"]))->first();

            //             if ($scannedItem) {
            //                 $storeFormCutPieceDetailArr = [
            //                     "form_id" => $validatedRequest["id"],
            //                     "method" => $validatedRequest["method"],
            //                     "id_roll" => strtoupper($validatedRequestDetail["kode_barang"]),
            //                     "id_item" => $scannedItem->id_item,
            //                     "detail_item" => $scannedItem->detail_item,
            //                     "lot" => $scannedItem->lot,
            //                     "roll" => $scannedItem->roll,
            //                     "roll_buyer" => $scannedItem->roll_buyer,
            //                     "rule_bom" => $scannedItem->rule_bom,
            //                     "qty_pengeluaran" => $scannedItem->qty_in,
            //                     "qty" => $scannedItem->qty,
            //                     "qty_unit" => $scannedItem->unit,
            //                     "status" => "incomplete",
            //                     "created_by" => Auth::user()->id,
            //                     "created_by_username" => Auth::user()->username
            //                 ];
            //             } else {
            //                 return array(
            //                     "status" => 400,
            //                     "message" => "Item tidak ditemukan.",
            //                     "additional" => [],
            //                 );
            //             }
            //         } else if ($validatedRequest["method"] == "select") {
            //             $storeFormCutPieceDetailArr = [
            //                 "form_id" => $validatedRequest["id"],
            //                 "method" => $validatedRequest["method"],
            //                 "id_item" => $validatedRequest["id_item"],
            //                 "detail_item" => $validatedRequest["detail_item"],
            //                 "qty_pengeluaran" => $validatedRequest["qty_item"],
            //                 "qty" => $validatedRequest["qty_item"],
            //                 "qty_unit" => $validatedRequest["unit_qty_item"],
            //                 "status" => "incomplete",
            //                 "created_by" => Auth::user()->id,
            //                 "created_by_username" => Auth::user()->username
            //             ];
            //         }

            //         $storeFormCutPieceDetail = FormCutPieceDetail::create($storeFormCutPieceDetailArr);

            //         if ($storeFormCutPieceDetail) {
            //             $updateFormCutPiece = $formCutPieceModel->update([
            //                 "process" => $request->process,
            //             ]);

            //             if ($updateFormCutPiece) {
            //                 $thisFormCutPieceDetail = FormCutPieceDetail::with("scannedItem")->where("id", $storeFormCutPieceDetail->id)->first();

            //                 return array(
            //                     "status" => 200,
            //                     "message" => "Item berhasil disimpan.",
            //                     "additional" => $thisFormCutPieceDetail,
            //                 );
            //             }
            //         }
            //     }

            //     break;
            // case 3 :
            //     $validatedRequest = $request->validate([
            //         "id" => "required",
            //         "id_detail" => "required",
            //         "group_roll" => "required",
            //         "lot" => "nullable",
            //         "roll" => "nullable",
            //         "roll_buyer" => "nullable",
            //         "rule_bom" => "nullable",
            //         "qty_pengeluaran" => "required",
            //         "qty" => "required",
            //         "qty_pemakaian" => "required|gt:0",
            //         "qty_sisa" => "required",
            //         "qty_unit" => "required|in:PCS",
            //     ], [
            //         "qty_pemakaian.gt" => "Harap isi qty piece."
            //     ]);

            //     $cuttingPieceModel = FormCutPiece::where("id", $validatedRequest["id"]);
            //     $cuttingPieceDetailModel = FormCutPieceDetail::where("id", $validatedRequest["id_detail"]);

            //     if ($cuttingPieceDetailModel) {
            //         $updateCuttingPieceDetail = $cuttingPieceDetailModel->update([
            //             "id" => $validatedRequest['id_detail'],
            //             "group_roll" => $validatedRequest['group_roll'],
            //             "lot" => $validatedRequest['lot'],
            //             "roll" => $validatedRequest['roll'],
            //             "roll_buyer" => $validatedRequest['roll_buyer'],
            //             "rule_bom" => $validatedRequest['rule_bom'],
            //             "qty_pemakaian" => $validatedRequest['qty_pemakaian'],
            //             "qty_sisa" => $validatedRequest['qty_sisa'],
            //             "qty_unit" => $validatedRequest['qty_unit'],
            //             "status" => "complete",
            //         ]);

            //         if ($updateCuttingPieceDetail) {
            //             // Scanned Item Qty
            //             if ($cuttingPieceDetailModel->first() && $cuttingPieceDetailModel->first()->method == "scan" && $cuttingPieceDetailModel->first()->id_roll) {
            //                 $scannedItem = ScannedItem::where("id_roll", $cuttingPieceDetailModel->first()->id_roll)->first();

            //                 if ($scannedItem) {
            //                     $scannedItem->qty = $validatedRequest['qty_sisa'];
            //                     $scannedItem->qty_pakai += $validatedRequest['qty_pemakaian'];
            //                     $scannedItem->save();
            //                 }
            //             }

            //             // Upsert Form Cut Piece Detail Size
            //             if ($request->so_det_id && count($request->so_det_id) > 0) {
            //                 $cuttingPieceDetailSizeArr = [];
            //                 for($i = 0; $i < count($request->so_det_id); $i++) {
            //                     if ($request->so_det_id[$i] && $request->size[$i] && $request->qty_detail[$i] && $request->qty_detail[$i] > 0) {
            //                         array_push($cuttingPieceDetailSizeArr, [
            //                             "form_detail_id" => $validatedRequest["id_detail"],
            //                             "so_det_id" => $request->so_det_id[$i],
            //                             "size" => $request->size[$i],
            //                             "dest" => $request->dest[$i],
            //                             "qty" => $request->qty_detail[$i],
            //                             "created_by" => Auth::user()->id,
            //                             "created_by_username" => Auth::user()->username
            //                         ]);
            //                     }
            //                 }

            //                 $storeCuttingPieceDetailSize = FormCutPieceDetailSize::upsert($cuttingPieceDetailSizeArr, ['form_detail_id', 'so_det_id'], ['size', 'qty', 'created_by', 'created_by_username']);
            //             }

            //             // Update Form Cut Piece
            //             $updateCuttingPiece = $cuttingPieceModel->update([
            //                 "process" => 1,
            //                 // "status" => 'complete',
            //             ]);

            //             // session()->forget('currentFormCutPiece');

            //             // // finishing
            //             // $this->finishProcess($validatedRequest["id"]);

            //             return array(
            //                 "status" => 200,
            //                 "message" => "Data Cutting Pcs berhasil disimpan.",
            //                 "additional" => $cuttingPieceModel->first()
            //             );
            //         }
            //     }

            //     break;
            // case 4 :
            //     $validatedRequest = $request->validate([
            //         "id" => "required"
            //     ]);

            //     $cuttingPieceModel = FormCutPiece::where("id", $validatedRequest["id"]);

            //     // Update Form Cut Piece
            //     $updateCuttingPiece = $cuttingPieceModel->update([
            //         "process" => 3,
            //         "status" => 'complete',
            //     ]);

            //     session()->forget('currentFormCutPiece');

            //     // finishing
            //     $this->finishProcess($validatedRequest["id"]);

            //     return array(
            //         "status" => 200,
            //         "message" => "Data Cutting Pcs berhasil disimpan.",
            //         "additional" => $cuttingPieceModel->first()
            //     );

            //    break;
            default :
                return array(
                    "status" => 400,
                    "message" => "Proses tidak ditemukan",
                    "additional" => [],
                );
        }
    }

    public function updateDetail(Request $request, CuttingPieceService $cuttingPieceService)
    {
        try {
            $result = $cuttingPieceService->updateFormCutPiece($request);

            return [
                "status" => 200,
                "message" => $result
            ];

        } catch (\Exception $e) {
            return [
                "status" => 400,
                "message" => $e->getMessage()
            ];
        }
    }

    // public function updateDetail(Request $request, CuttingPieceService $cuttingPieceService)
    // {
    //     $form = FormCutPiece::where("id", $request->id)->first();

    //     if ($form) {
    //         // Check Stocker
    //         $checkStockerForm = $cuttingPieceService->checkStockerForm($form->id);
    //         if (!$checkStockerForm) {
    //             return array(
    //                 "status" => 400,
    //                 "message" => "Stocker sudah diprint"
    //             );
    //         }

    //         $formDetail = FormCutPieceDetail::where("form_id", $form->id)->where("id", $request->id_detail)->first();

    //         if ($formDetail) {
    //             $updateMessage = "";

    //             $qtyUsage = 0;
    //             for ($i = 0; $i < count($request->so_det_id); $i++) {
    //                 // Update Form Detail Size
    //                 $formDetailSize = FormCutPieceDetailSize::where("form_detail_id", $formDetail->id)->where("so_det_id", $request->so_det_id[$i])->first();
    //                 $qtyBefore = $formDetailSize->qty;
    //                 $formDetailSize->qty = $request->qty_detail[$i];
    //                 $formDetailSize->edited_by = Auth::user()->id;
    //                 $formDetailSize->edited_by_username = Auth::user()->username;
    //                 $formDetailSize->edited_at = Carbon::now();
    //                 $formDetailSize->edited_notes = "Update Form Cut Piece Detail Size Qty From ".$qtyBefore." to ".$formDetailSize->qty;
    //                 $updateMessage .= "<br>".$formDetailSize->edited_notes;
    //                 $formDetailSize->save();

    //                 $qtyUsage += $request->qty_detail[$i];
    //             }

    //             // Update Form Detail
    //             $qtyUsageBefore = $formDetail->qty_pemakaian;
    //             $formDetail->qty_pemakaian = $qtyUsage;
    //             $formDetail->qty_sisa = $formDetail->qty - $qtyUsage;
    //             $formDetail->edited_by = Auth::user()->id;
    //             $formDetail->edited_by_username = Auth::user()->username;
    //             $formDetail->edited_at = Carbon::now();
    //             $formDetail->edited_notes = "Update Form Cut Piece Detail Qty Usage From ".$qtyUsageBefore." to ".$formDetail->qty_pemakaian;
    //             $updateMessage .= "<br>".$formDetail->edited_notes;
    //             $formDetail->save();

    //             // Update Chained Roll Qty
    //             $diffQty = ($qtyUsageBefore-$formDetail->qty_pemakaian);
    //             $cuttingPieceService->fixChainedQty($formDetail->id, $diffQty);

    //             // Get Scanned Item
    //             $scannedItem = ScannedItem::where("id_roll", $formDetail->id_roll)->first();

    //             if ($scannedItem->qty + $diffQty < 0) {
    //                 // Cancel transaction
    //             }

    //             return array(
    //                 "status" => 200,
    //                 "message" => "Form ".$form->no_form." berhasil diubah <br>".$updateMessage,
    //             );
    //         }
    //     }

    //     return array(
    //         "status" => 400,
    //         "message" => "Terjadi kesalahan",
    //     );
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cutting\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCutPiece $formCutPiece, $id)
    {
        $stocker = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.form_piece_id,
                form_cut_piece.tanggal,
                stocker_input.id_qr_stocker,
                form_cut_piece.no_form,
                COALESCE(master_sb_ws.ws, stocker_input.act_costing_ws, form_cut_piece.act_costing_ws) act_costing_ws,
                COALESCE(master_sb_ws.styleno, form_cut_piece.style) style,
                COALESCE(master_sb_ws.color, stocker_input.color, form_cut_piece.color) color,
                COALESCE(master_sb_ws.id_so_det, stocker_input.so_det_id) so_det_id,
                COALESCE(master_sb_ws.size, stocker_input.size) size,
                COALESCE(stocker_input.panel, form_cut_piece.panel) panel,
                master_part.nama_part part,
                stocker_input.qty_ply qty,
                stocker_input.notes
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
            where("form_cut_piece.id", $id)->
            first();

        if (!$stocker) {

            $dataLog = FormCutPiece::where("id", $id)->first();
            logHistory(
                $dataLog->id,
                $dataLog->toArray()
            );

            $deleteFormCutPiece = FormCutPiece::where("id", $id)->delete();

            if ($deleteFormCutPiece) {
                $formCutPieceDetails = FormCutPieceDetail::where("form_id", $id)->get();
                if ($formCutPieceDetails) {
                    $formCutPieceDetailIds = [];
                    $formCutPieceDetailSizeIds = [];
                    foreach ($formCutPieceDetails as $d) {
                        // Piece Detail
                        array_push($formCutPieceDetailIds, $d->id);

                        // Piece Detail Size
                        $currentCutPieceDetailSizeIds = $d->formCutPieceDetailSizes ? $d->formCutPieceDetailSizes->pluck("id") : null;
                        if (count($currentCutPieceDetailSizeIds) > 0) {
                            array_push($formCutPieceDetailSizeIds, ...$currentCutPieceDetailSizeIds);
                        }

                        // Update Scanned Item
                        ScannedItem::where("id_roll", $d->id_roll)->update([
                            "qty" => DB::raw("qty + ".$d->qty_pemakaian),
                            "qty_pakai" => DB::raw("qty_pakai - ".$d->qty_pemakaian),
                        ]);
                    }

                    FormCutPieceDetail::whereIn("id", $formCutPieceDetailIds)->delete();
                    FormCutPieceDetailSize::whereIn("id", $formCutPieceDetailSizeIds)->delete();
                }

                return array(
                    "status" => 200,
                    "message" => "Form Piece berhasil dihapus.",
                    "table" => "cutting-piece-table"
                );
            }
        } else {
            return array(
                "status" => 400,
                "message" => "Form Piece sudah memiliki stocker.",
                "table" => "cutting-piece-table"
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan.",
            "table" => "cutting-piece-table"
        );
    }

    public function deleteDetail(Request $request, CuttingPieceService $cuttingPieceService)
    {
        try {

            $result = $cuttingPieceService->deleteFormCutPieceDetail($request);

            return [
                "status" => 200,
                "message" => $result
            ];

        } catch (\Exception $e) {

            return [
                "status" => 400,
                "message" => $e->getMessage()
            ];
        }
    }

    public function stock(Request $request) {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

            $stocker = Stocker::selectRaw("
                    stocker_input.id,
                    stocker_input.form_reject_id,
                    form_cut_reject.tanggal,
                    stocker_input.id_qr_stocker,
                    form_cut_reject.no_form,
                    COALESCE(master_sb_ws.ws, stocker_input.act_costing_ws, form_cut_reject.act_costing_ws) act_costing_ws,
                    COALESCE(master_sb_ws.styleno, form_cut_reject.style) style,
                    COALESCE(master_sb_ws.color, stocker_input.color, form_cut_reject.color) color,
                    COALESCE(master_sb_ws.id_so_det, stocker_input.so_det_id) so_det_id,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    COALESCE(stocker_input.panel, form_cut_reject.panel) panel,
                    COALESCE(stocker_input.shade, form_cut_reject.group) group_reject,
                    master_part.nama_part part,
                    stocker_input.qty_ply qty,
                    stocker_input.notes
                ")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
                whereBetween("form_cut_reject.tanggal", [$dateFrom, $dateTo])->
                whereNotNull("stocker_input.form_reject_id")->
                get();

            return DataTables::of($stocker)->toJSON();
        }

        return view("cutting.cutting-form-piece.stock-cutting-piece", ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "cutting-piece"]);
    }

    public function getSizeList(Request $request)
    {
        $sizeQuery = DB::table("master_sb_ws")->selectRaw("
                master_sb_ws.id_so_det so_det_id,
                master_sb_ws.ws no_ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END) size_dest,
                master_sb_ws.qty order_qty,
                COALESCE(form_cut_reject_detail.qty, 0) qty
            ")->
            where("master_sb_ws.id_act_cost", $request->act_costing_id)->
            where("master_sb_ws.color", $request->color)->
            leftJoin('form_cut_reject_detail', 'form_cut_reject_detail.so_det_id', '=', 'master_sb_ws.id_so_det')->
            leftJoin('form_cut_reject', 'form_cut_reject.id', '=', 'form_cut_reject_detail.form_id')->
            leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size");

        $totalFormDetail = FormCutPieceDetail::where("form_id", $request->id)->count();
        if ($totalFormDetail > 0) {
            $sizeQuery->where("form_cut_reject_detail.form_id", $request->id);
        }

        $sizes = $sizeQuery->groupBy("id_so_det")->orderBy("master_size_new.urutan")->get();

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($sizes)),
            "recordsFiltered" => intval(count($sizes)),
            "data" => $sizes
        ]);
    }

    public function getDataWs(Request $request)
    {
        $data = DB::table('master_sb_ws')
            ->select(
                'id_act_cost',
                'ws',
                'buyer',
                'styleno',
                'color'
            )
            ->where('ws', $request->ws)
            ->distinct()
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    // public function getDataDetail(Request $request)
    // {
    //     $data = DB::table('master_sb_ws')
    //         ->select(
    //             'id_act_cost',
    //             'ws',
    //             'buyer',
    //             'styleno',
    //             'color',
    //             'id_so_det'
    //         )
    //         ->where('id_act_cost', $request->id_act_cost)
    //         ->where('color', $request->color)
    //         ->where('size', $request->size)
    //         ->first();

    //     return response()->json([
    //         'status' => 200,
    //         'data' => $data
    //     ]);
    // }

    public function updateProcessStatus(Request $request)
    {
        DB::table('form_cut_piece')
            ->where('id', $request->id)
            ->update([
                'process' => $request->process,
                'status' => $request->status,
                'updated_at' => now()
            ]);

        $data = DB::table('form_cut_piece')
            ->where('id', $request->id)
            ->first();

        logHistory(
            $data->id,
            (array) $data
        );

        return array(
            "status" => 200,
            "message" => "Process Form Cut Piece berhasil diupdate.",
        );
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ExportCuttingFormReject($request->dateFrom, $request->dateTo), 'Report Cutting.xlsx');
    }

    public function summary($id = 0) {
        $formCutPieceSummary = FormCutPiece::selectRaw("
            master_sb_ws.id_so_det,
            master_sb_ws.ws,
            master_sb_ws.color,
            master_sb_ws.size,
            master_sb_ws.dest,
            SUM(form_cut_piece_detail_size.qty) qty,
            SUM(form_cut_piece_detail_size.qty_aktual) qty_aktual
        ")->
        leftJoin("form_cut_piece_detail", "form_cut_piece_detail.form_id", "=", "form_cut_piece.id")->
        leftJoin("form_cut_piece_detail_size", "form_cut_piece_detail_size.form_detail_id", "=", "form_cut_piece_detail.id")->
        leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "form_cut_piece_detail_size.so_det_id")->
        where("form_cut_piece.id", $id)->
        groupBy("master_sb_ws.id_so_det")->
        get();

        return Datatables::of($formCutPieceSummary)->toJson();
    }
}
