<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutReject;
use App\Models\Cutting\FormCutRejectDetail;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\TrolleyStocker;
use App\Models\Dc\LoadingLine;
use App\Models\Part\PartDetail;
use App\Models\Stocker\Stocker;
use App\Models\Cutting\ScannedItem;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Cutting\ExportCuttingFormReject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class CuttingFormRejectController extends Controller
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

            $formCutReject = FormCutReject::whereBetween("tanggal", [$dateFrom, $dateTo]);

            return DataTables::eloquent($formCutReject)->addColumn('sizes', function ($row) {
                $sizes = $row->formCutRejectDetails->filter(function ($item) {
                    return $item->qty > 0;
                });

                $sizeList = "";
                foreach ($sizes as $size) {
                    $sizeList .= $size->size . ($size->soDet && $size->soDet->dest && $size->soDet->dest != '-' ? " - " . $size->soDet->dest . " / " : " / ");
                }

                return $sizeList;
            })->addColumn('qty', function ($row) {
                $qty = $row->formCutRejectDetails ? $row->formCutRejectDetails->sum("qty") : "-";

                return $qty;
            })->addColumn('jml_barcode', function ($row) {
                $jml_barcode = $row->formCutRejectDetailsBarcode->count();
                return $jml_barcode;
            })->toJSON();
        }

        return view("cutting.cutting-form-reject.cutting-form-reject", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-reject", "subPage" => "cutting-reject"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view("cutting.cutting-form-reject.create-cutting-form-reject", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-reject", "subPage" => "cutting-reject", "orders" => $orders]);
    }

    public function generateCode()
    {
        $date = date('Y-m-d');
        $hari = substr($date, 8, 2);
        $bulan = substr($date, 5, 2);
        $now = Carbon::now();

        $lastForm = FormCutReject::select("no_form")->whereRaw("no_form LIKE 'GR" . $hari . "-" . $bulan . "%'")->orderBy("id", "desc")->first();
        $urutan =  $lastForm ? (str_replace("GR" . $hari . "-" . $bulan . "-", "", $lastForm->no_form) + 1) : 1;

        $noForm = "GR" . $hari . "-" . $bulan . "-" . $urutan;

        $form = FormCutReject::create([
            "no_form" => $noForm,
            "tanggal" => date("Y-m-d")
        ]);

        return $form;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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

        if ($validatedRequest) {
            $storeFormCutReject = FormCutReject::updateOrCreate([
                "id" => $validatedRequest["id"],
                "no_form" => $validatedRequest["no_form"],
            ], [
                "tanggal" => $validatedRequest["tanggal"],
                "act_costing_id" => $validatedRequest["act_costing_id"],
                "act_costing_ws" => $validatedRequest["act_costing_ws"],
                "buyer_id" => $validatedRequest["buyer_id"],
                "buyer" => $validatedRequest["buyer"],
                "style" => $validatedRequest["style"],
                "color" => $validatedRequest["color"],
                "panel" => $validatedRequest["panel"],
                "group" => $validatedRequest["group"],
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username,
            ]);

            if ($storeFormCutReject) {
                $formCutRejectDetails = [];

                $soDet = DB::connection("mysql_sb")->table("so_det")->whereIn("id", $request["so_det_id"])->get();
                for ($i = 0; $i < count($request["so_det_id"]); $i++) {
                    $currentSoDet = $soDet->where("id", $request["so_det_id"][$i])->first();

                    array_push($formCutRejectDetails, [
                        "form_id" => $storeFormCutReject->id,
                        "so_det_id" => $request["so_det_id"][$i],
                        "size" => $currentSoDet->size,
                        "qty" => $request["qty"][$i],
                        "created_by" => Auth::user()->id,
                        "created_by" => Auth::user()->username
                    ]);
                }

                $upsertFormCutRejectDetail = FormCutRejectDetail::upsert($formCutRejectDetails, ['form_id', 'so_det_id']);

                if ($upsertFormCutRejectDetail) {
                    return array(
                        "status" => 200,
                        "message" => "Form Ganti Reject Berhasil disimpan.",
                        "redirect" => route("cutting-reject")
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

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan."
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function show($id = 0)
    {
        if ($id) {
            $formCutReject = FormCutReject::with("formCutRejectDetails")->where("id", $id)->first();

            $partDetails = PartDetail::selectRaw("
                    part_detail.id,
                    master_part.nama_part,
                    master_part.bag,
                    COALESCE(master_secondary.tujuan, '-') tujuan,
                    COALESCE(master_secondary.proses, '-') proses
                ")->leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->leftJoin("part", "part.id", "part_detail.part_id")->leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->where("part.act_costing_id", $formCutReject->act_costing_id)->where("part.panel", $formCutReject->panel)->groupBy("master_part.id")->get();

            return view("cutting.cutting-form-reject.show-cutting-form-reject", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-reject", "subPage" => "cutting-reject", "formCutReject" => $formCutReject, "partDetails" => $partDetails]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCutReject $formCutReject, $id = 0)
    {
        $form = FormCutReject::where("id", $id)->first();

        return view("cutting.cutting-form-reject.edit-cutting-form-reject", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-reject", "subPage" => "cutting-reject", "form" => $form]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormCutReject $formCutReject)
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
                $updateFormCutReject = FormCutReject::where("id", $validatedRequest["id"])->update([
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
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username,
                ]);

                if ($updateFormCutReject) {
                    $formCutRejectDetails = [];

                    $soDet = DB::connection("mysql_sb")->table("so_det")->whereIn("id", $request["so_det_id"])->get();
                    for ($i = 0; $i < count($request["so_det_id"]); $i++) {
                        $currentSoDet = $soDet->where("id", $request["so_det_id"][$i])->first();

                        array_push($formCutRejectDetails, [
                            "form_id" => $validatedRequest["id"],
                            "so_det_id" => $request["so_det_id"][$i],
                            "size" => $currentSoDet->size,
                            "qty" => $request["qty"][$i],
                            "created_by" => Auth::user()->id,
                            "created_by" => Auth::user()->username
                        ]);
                    }

                    $upsertFormCutRejectDetail = FormCutRejectDetail::upsert($formCutRejectDetails, ['form_id', 'so_det_id'], ['qty']);

                    if ($upsertFormCutRejectDetail) {
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
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCutReject $formCutReject, $id)
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
            ")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->where("form_cut_reject.id", $id)->first();

        if (!$stocker || (Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0)) {

            try {
                // Begin Transaction
                DB::transaction(function () use ($id) {
                    $deleteFormCutReject = FormCutReject::where('id', $id)->delete();

                    if (! $deleteFormCutReject) {
                        throw new \Exception('Form Cut Reject not found.');
                    }

                    // Detail
                    FormCutRejectDetail::where('form_id', $id)->delete();

                    // Barcode (Roll)
                    $formCutRejectRolls = FormCutRejectBarcode::where('form_id', $id)->get();
                    foreach ($formCutRejectRolls as $roll) {

                        // Synchronize scanned_item table Qty
                        $scannedItem = ScannedItem::where('id_roll', $roll->no_barcode)->first();

                        if ($scannedItem) {
                            $scannedItem->qty =
                                ($scannedItem->qty ?? 0) + ($roll->qty_roll - $roll->sisa_kain);

                            $scannedItem->save();
                        }

                        // Delete
                        $roll->delete();
                    }

                    // Stocker
                    $stockers = Stocker::where('form_reject_id', $id)->get();

                    if ($stockers->isNotEmpty()) {
                        $stockerIds = $stockers->pluck('id');
                        $stockerIdQrs = $stockers->pluck('id_qr_stocker');

                        LoadingLine::whereIn('stocker_id', $stockerIds)->delete();
                        TrolleyStocker::whereIn('stocker_id', $stockerIds)->delete();
                        DCIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
                        SecondaryInhouse::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
                        SecondaryIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();

                        Stocker::where('form_reject_id', $id)->delete();
                    }
                });

                return array(
                    "status" => 200,
                    "message" => "Form Reject berhasil dihapus.",
                    "table" => "cutting-reject-table"
                );
            } catch (\Throwable $e) {
                \Log::error('Delete Form Reject failed', [
                        'form_id' => $id,
                        'error'   => $e->getMessage(),
                    ]);

                return [
                    'status'  => 500,
                    'message' => 'Gagal menghapus Form Reject.',
                ];
            }

            return array(
                "status" => 400,
                "message" => "Form Reject sudah memiliki stocker.",
                "table" => "cutting-reject-table"
            );
        }
    }

    public function stock(Request $request)
    {
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
                ")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->whereBetween("form_cut_reject.tanggal", [$dateFrom, $dateTo])->whereNotNull("stocker_input.form_reject_id")->get();

            return DataTables::of($stocker)->toJSON();
        }

        return view("cutting.cutting-form-reject.stock-cutting-reject", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-reject", "subPage" => "cutting-reject"]);
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
            ")->where("master_sb_ws.id_act_cost", $request->act_costing_id)->where("master_sb_ws.color", $request->color)->leftJoin('form_cut_reject_detail', 'form_cut_reject_detail.so_det_id', '=', 'master_sb_ws.id_so_det')->leftJoin('form_cut_reject', 'form_cut_reject.id', '=', 'form_cut_reject_detail.form_id')->leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size");

        $totalFormDetail = FormCutRejectDetail::where("form_id", $request->id)->count();
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

    public function exportExcel(Request $request)
    {
        return Excel::download(new ExportCuttingFormReject($request->dateFrom, $request->dateTo), 'Report Cutting.xlsx');
    }

    public function save_fabric_form_reject(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $barcode = $request->barcode;
        $id = $request->id;
        $qty_roll = $request->qty_roll;
        $qty_sisa = $request->qty_sisa;
        $qty_pakai = $request->qty_pakai;
        $qty_reject = $request->qty_reject;
        $tot_pakai = $request->tot_pakai;
        $today     = date('Y-m-d');

        // Update scanned item (roll detail & qty)
        ScannedItem::updateOrCreate(
            ["id_roll" => $barcode],
            [
                "qty" => $qty_sisa,
            ]
        );


        $id = DB::table('form_cut_reject_barcode')->insertGetId([
            'form_id'               => $id,
            'tgl_trans'             => $today,
            'barcode'               => $barcode,
            'qty_roll'              => $qty_roll,
            'qty_pakai'             => $qty_pakai,
            'qty_reject'            => $qty_reject,
            'sisa_kain'             => $qty_sisa,
            'qty_total'             => $tot_pakai,
            'created_by'            => $user,
            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ]);

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form berhasil disimpan.'
        ]);
    }

    public function show_fabric_form_reject(Request $request)
    {
        $id = $request->id;

        $data_input = DB::select("SELECT
barcode,
lot,
roll_buyer,
qty_in,
a.qty_roll,
a.qty_pakai,
a.qty_reject,
a.sisa_kain,
round(a.qty_pakai + a.qty_reject + a.sisa_kain - a.qty_roll,2) as short_roll
from form_cut_reject_barcode a
left join scanned_item b on a.barcode = b.id_roll
where a.form_id = '$id'
            ");

        return DataTables::of($data_input)->toJson();
    }
}
