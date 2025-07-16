<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\FormCutPiece;
use App\Models\FormCutPieceDetail;
use App\Models\FormCutPieceDetailSize;
use App\Models\PartDetail;
use App\Models\Stocker;
use App\Models\ScannedItem;
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

            $formCutPiece = FormCutPiece::whereBetween("tanggal", [$dateFrom, $dateTo]);

            return DataTables::eloquent($formCutPiece)->
                addColumn('sizes', function ($row) {
                    if ($row->formCutPieceDetailSizes) {
                        $sizeArr = $row->formCutPieceDetailSizes->groupBy("so_det_id");
                        $sizeText = "";
                        foreach ($sizeArr as $sizes) {
                            $size = $sizes->first();
                            $sizeText .= $size->size.(($size->dest ? " - ".$size->dest : "").($sizes->sum("qty") > 0 ? "(".$sizes->sum("qty").")" : "")." / ");
                        }

                        return $sizeText;
                    }
                })->
                addColumn('qty', function ($row) {
                    $qty = $row->formCutPieceDetails ? $row->formCutPieceDetails->sum("qty_use") : "-";

                    return $qty;
                })->
                toJSON();
        }

        return view("cutting.cutting-form-piece.cutting-form-piece", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piece", "subPage" => "cutting-piece"]);
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

        return view("cutting.cutting-form-piece.create-cutting-form-piece", ["orders" => $orders, "page" => "dashboard-cutting", "subPageGroup" => "cutting-piece", "subPage" => "cutting-piece"]);
    }

    public function process($id = 0)
    {
        $cuttingFormPiece = FormCutPiece::find($id);

        if (!$cuttingFormPiece) {
            session()->forget('currentFormCutPiece');

            return redirect()->route('create-cutting-piece');
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('cutting.cutting-form-piece.create-cutting-form-piece', ['cuttingFormPiece' => $cuttingFormPiece, 'orders' => $orders, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piece", "subPage" => "cutting-piece"]);
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

        return $noForm;
    }

    public function incompleteItem($id = 0)
    {
        if ($id) {
            $incomplete = FormCutPieceDetail::where("form_id", $id)->where("status", "incomplete")->first();

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

                $storeFormCutPiece = FormCutPiece::create([
                    "no_form" => $validatedRequest["no_form"],
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

                        $scannedItem = ScannedItem::where("id_roll", $validatedRequestDetail["kode_barang"])->first();

                        if ($scannedItem) {
                            $storeFormCutPieceDetailArr = [
                                "form_id" => $validatedRequest["id"],
                                "method" => $validatedRequest["method"],
                                "id_roll" => $validatedRequestDetail["kode_barang"],
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
                            return array(
                                "status" => 200,
                                "message" => "Item berhasil disimpan.",
                                "additional" => $storeFormCutPieceDetail,
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
                        if ($request->so_det_id && count($request->so_det_id) > 1) {
                            $cuttingPieceDetailSizeArr = [];
                            for($i = 0; $i < count($request->so_det_id); $i++) {
                                if ($request->so_det_id[$i] && $request->size[$i] && $request->qty_detail[$i] && $request->qty_detail[$i] > 0) {
                                    array_push($cuttingPieceDetailSizeArr, [
                                        "form_detail_id" => $validatedRequest["id_detail"],
                                        "so_det_id" => $request->so_det_id[$i],
                                        "size" => $request->size[$i],
                                        "dest" => $request->dest[$i],
                                        "qty" => $request->qty_detail[$i],
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username
                                    ]);
                                }
                            }

                            $storeCuttingPieceDetailSize = FormCutPieceDetailSize::upsert($cuttingPieceDetailSizeArr, ['form_detail_id', 'so_det_id'], ['size', 'qty', 'created_by', 'created_by_username']);
                        }

                        // Update Form Cut Piece
                        $updateCuttingPiece = $cuttingPieceModel->update([
                            "process" => $request->process,
                            "status" => 'complete',
                        ]);

                        session()->forget('currentFormCutPiece');

                        return array(
                            "status" => 200,
                            "message" => "Data Cutting Pcs berhasil disimpan.",
                            "additional" => $cuttingPieceModel->first()
                        );
                    }
                }

                break;
            default :
                return array(
                    "status" => 400,
                    "message" => "Proses tidak ditemukan",
                    "additional" => [],
                );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FormCutPiece  $formCutPiece
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

            return view("cutting.cutting-form-piece.show-cutting-form-piece", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piece", "subPage" => "cutting-piece", "formCutPiece" => $formCutPiece, "partDetails" => $partDetails]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCutPiece $formCutPiece, $id = 0)
    {
        $form = FormCutPiece::where("id", $id)->first();

        return view("cutting.cutting-form-piece.edit-cutting-form-piece", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piece", "subPage" => "cutting-piece", "form" => $form]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormCutPiece $formCutPiece)
    {
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
            "group" => "required",
        ]);

        $totalStocker = Stocker::where("form_reject_id", $validatedRequest["id"])->count();

        if ($totalStocker < 1) {
            if ($validatedRequest) {
                $updateFormCutPiece = FormCutPiece::where("id", $validatedRequest["id"])->
                    update([
                        "no_form" => $validatedRequest["no_form"],
                        "tanggal" => $validatedRequest["tanggal"],
                        "act_costing_id" => $validatedRequest["act_costing_id"],
                        "act_costing_ws" => $validatedRequest["act_costing_ws"],
                        "buyer_id" => $validatedRequest["buyer_id"],
                        "buyer" => $validatedRequest["buyer"],
                        "style" => $validatedRequest["style"],
                        "color" => $validatedRequest["color"],
                        "panel" => $validatedRequest["panel"],
                        "group" => $validatedRequest["group"],
                        "created_by"=> Auth::user()->id,
                        "created_by_username"=> Auth::user()->username,
                    ]);

                if ($updateFormCutPiece) {
                    $formCutPieceDetails = [];

                    $soDet = DB::connection("mysql_sb")->table("so_det")->whereIn("id", $request["so_det_id"])->get();
                    for ($i = 0; $i < count($request["so_det_id"]); $i++) {
                        $currentSoDet = $soDet->where("id", $request["so_det_id"][$i])->first();

                        array_push($formCutPieceDetails, [
                            "form_id" => $validatedRequest["id"],
                            "so_det_id" => $request["so_det_id"][$i],
                            "size" => $currentSoDet->size,
                            "qty" => $request["qty"][$i],
                            "created_by" => Auth::user()->id,
                            "created_by" => Auth::user()->username
                        ]);
                    }

                    $upsertFormCutPieceDetail = FormCutPieceDetail::upsert($formCutPieceDetails, ['form_id', 'so_det_id'], ['qty']);

                    if ($upsertFormCutPieceDetail) {
                        return array(
                            "status" => 200,
                            "message" => "Form Ganti Reject Berhasil disimpan."
                        );
                    }

                    return array(
                        "status" => 400,
                        "message" => "Terjadi Kesalahan."
                    );
                }

                return array(
                    "status" => 400,
                    "message" => "Terjadi Kesalahan."
                );
            }
        } else {
            return array(
                "status" => 400,
                "message" => "Form sudah memiliki Stocker."
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan."
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FormCutPiece  $formCutPiece
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCutPiece $formCutPiece, $id)
    {
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
            where("form_cut_reject.id", $id)->
            first();

        if (!$stocker) {
            $deleteFormCutPiece = FormCutPiece::where("id", $id)->delete();

            if ($deleteFormCutPiece) {
                $deleteFormCutPieceDetail = FormCutPieceDetail::where("form_id", $id)->delete();

                return array(
                    "status" => 200,
                    "message" => "Form Reject berhasil dihapus.",
                    "table" => "cutting-piece-table"
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Form Reject sudah memiliki stocker.",
            "table" => "cutting-piece-table"
        );
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

        return view("cutting.cutting-form-piece.stock-cutting-piece", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piece", "subPage" => "cutting-piece"]);
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

    public function exportExcel(Request $request) {
        return Excel::download(new ExportCuttingFormReject($request->dateFrom, $request->dateTo), 'Report Cutting.xlsx');
    }
}
