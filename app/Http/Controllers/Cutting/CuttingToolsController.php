<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\MutasiCuttingPcsSaldoTmp;
use App\Models\Cutting\MutasiCuttingPcsSaldo;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartForm;
use App\Models\Stocker\Stocker;
use App\Models\SignalBit\ActCosting;
use App\Services\StockerService;
use App\Services\CuttingService;
use App\Imports\ImportCuttingManual;
use App\Imports\ImportSaldoAwalCutting;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class CuttingToolsController extends Controller
{
    public function index() {
        $orders = ActCosting::select('id', 'kpno', 'styleno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->get();

        return view('cutting.tools.tools', [
            "page" => "dashboard-cutting",
            "orders" => $orders
        ]);
    }

    public function getRollQty(Request $request) {
        $id = $request->id;

        $scannedItem = ScannedItem::selectRaw("
            marker_input.buyer,
            marker_input.act_costing_ws no_ws,
            marker_input.style style,
            marker_input.color color,
            scanned_item.id_roll,
            scanned_item.id_item,
            scanned_item.detail_item,
            scanned_item.lot,
            COALESCE(scanned_item.roll, scanned_item.roll_buyer) no_roll,
            scanned_item.qty,
            scanned_item.qty_in,
            scanned_item.qty_stok,
            scanned_item.unit,
            COALESCE(scanned_item.updated_at, scanned_item.created_at) updated_at
        ")->
        leftJoin('form_cut_input_detail', 'form_cut_input_detail.id_roll', '=', 'scanned_item.id_roll')->
        leftJoin('form_cut_input', 'form_cut_input.id', '=', 'form_cut_input_detail.form_cut_id')->
        leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->
        where('scanned_item.id_roll', $id)->
        first();

        if ($scannedItem) {
            return json_encode($scannedItem);
        }

        return  null;
    }

    public function fixRollQty(Request $request, CuttingService $cuttingService) {
        $rollId = $request->id_roll;
        $rollQty = $request->qty;
        $rollUse = null;

        return $cuttingService->fixRollQty($rollId, $rollQty);
    }

    public function getFormRatio(Request $request) {
        $noForm = $request->no_form;

        if ($noForm) {
            $form = FormCutInput::where("no_form", $noForm)->orderBy("created_at", "desc")->first();

            if ($form && $form->marker && $form->marker->markerDetails) {
                return array(
                    "form_id" => $form->id,
                    "kode_marker" => $form->marker->kode,
                    "no_ws" => $form->marker->act_costing_ws,
                    "style" => $form->marker->style,
                    "color" => $form->marker->color,
                    "panel" => $form->marker->panel,
                    "qty_ply" => $form->total_lembar,
                    "ratio" => $form->marker->markerDetails
                );
            }

            return null;
        }

        return null;
    }

    public function updateFormRatio(Request $request) {
        $validatedRequest = $request->validate([
            "modify_ratio_form_id" => "required",
            "modify_ratio_kode_marker" => "required",
            "modify_ratio_no_ws" => "required",
            "modify_ratio_style" => "required",
            "modify_ratio_color" => "required",
            "modify_ratio_panel" => "required",
            "modify_ratio_qty_ply" => "required",
        ]);

        if ($validatedRequest) {
            $checkStocker = Stocker::where("form_cut_id", $validatedRequest['modify_ratio_form_id'])->first();

            if (Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() < 1) {
                if ($checkStocker) {
                    return array(
                        "status" => 400,
                        "message" => "Form sudah memiliki Stocker."
                    );
                }
            }

            $oldMarker = Marker::where("kode", $validatedRequest['modify_ratio_kode_marker'])->first();

            $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
            $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
            $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);
            $totalQty = 0;

            foreach ($request["modify_ratio_cut_qty"] as $qty) {
                $totalQty += $qty;
            }

            if ($totalQty > 0) {
                $markerStore = Marker::create([
                    'tgl_cutting' => $oldMarker->tgl_cutting,
                    'kode' => $markerCode,
                    'act_costing_id' => $oldMarker->act_costing_id,
                    'act_costing_ws' => $oldMarker->act_costing_ws,
                    'buyer' => $oldMarker->buyer,
                    'style' => $oldMarker->style,
                    'cons_ws' => $oldMarker->cons_ws,
                    'color' => $oldMarker->color,
                    'panel' => $oldMarker->panel,
                    'panjang_marker' => $oldMarker->panjang_marker,
                    'unit_panjang_marker' => $oldMarker->unit_panjang_marker,
                    'comma_marker' => $oldMarker->comma_marker,
                    'unit_comma_marker' => $oldMarker->unit_comma_marker,
                    'lebar_marker' => $oldMarker->lebar_marker,
                    'unit_lebar_marker' => $oldMarker->unit_lebar_marker,
                    'gelar_qty' => $oldMarker->gelar_qty,
                    'gelar_qty_balance' => $oldMarker->gelar_qty_balance,
                    'po_marker' => $oldMarker->po_marker,
                    'urutan_marker' => $oldMarker->urutan_marker,
                    'cons_marker' => $oldMarker->cons_marker,
                    'gramasi' => $oldMarker->gramasi,
                    'tipe_marker' => $oldMarker->tipe_marker,
                    'notes' => $oldMarker->notes,
                    'cons_piping' => $oldMarker->cons_piping,
                    'cancel' => 'N',
                ]);

                $timestamp = Carbon::now();
                $markerId = $markerStore->id;
                $markerDetailData = [];
                for ($i = 0; $i < count($request["modify_ratio_so_det_id"]); $i++) {
                    array_push($markerDetailData, [
                        "marker_id" => $markerId,
                        "so_det_id" => $request["modify_ratio_so_det_id"][$i],
                        "size" => $request["modify_ratio_size"][$i],
                        "ratio" => $request["modify_ratio_ratio"][$i],
                        "cut_qty" => $request["modify_ratio_cut_qty"][$i],
                        "cancel" => 'N',
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp,
                    ]);
                }

                $markerDetailStore = MarkerDetail::insert($markerDetailData);

                if ($markerStore && $markerDetailStore) {
                    $updateFormCut = FormCutInput::where("id", $validatedRequest["modify_ratio_form_id"])->update([
                        "marker_id" => $markerId,
                        "id_marker" => $markerCode
                    ]);

                    if ($oldMarker->id) {
                        $oldMarkerForm = FormCutInput::where("marker_id", $oldMarker->id)->count();

                        if ($oldMarkerForm < 1) {
                            $deleteOldMarker = Marker::where("id", $oldMarker->id)->delete();
                        }
                    }

                    return array(
                        "status" => 200,
                        "message" => "Ratio Form berhasil diubah.",
                        "additional" => [],
                    );
                }
            }

            return array(
                "status" => 400,
                "message" => "Total Cut Qty Kosong",
                "additional" => [],
            );
        }
    }

    public function getFormMarker(Request $request) {
        $noForm = $request->no_form;

        if ($noForm) {
            $form = FormCutInput::where("no_form", $noForm)->orderBy("created_at", "desc")->first();

            if ($form && $form->marker && $form->marker->markerDetails) {
                return array(
                    "form_id" => $form->id,
                    "kode_marker" => $form->marker->kode,
                    "no_ws" => $form->marker->act_costing_id,
                    "no_ws_input" => $form->marker->act_costing_ws,
                    "style" => $form->marker->style,
                    "color" => $form->marker->color,
                    "panel" => $form->marker->panel,
                );
            }

            return null;
        }

        return null;
    }

    public function updateFormMarker(Request $request, StockerService $stockerService) {
        $validatedRequest = $request->validate([
            "modify_marker_form_id" => "required",
            "modify_marker_kode_marker" => "required",
            "modify_marker_no_ws" => "required",
            "modify_marker_color" => "required",
            "modify_marker_panel" => "required",
        ]);

        if ($validatedRequest) {
            // Check Current Form
            $currentForm = FormCutInput::where("id", $validatedRequest['modify_marker_form_id'])->first();

            // If not Bypassed
            if (!isset($request['modify_bypass_stocker'])) {

                // Check Stocker Availability
                $checkStocker = Stocker::where("form_cut_id", $validatedRequest['modify_marker_form_id'])->first();

                if ($checkStocker) {
                    return array(
                        "status" => 400,
                        "message" => "Form sudah memiliki Stocker."
                    );
                }
            }

            // Old Marker
            $oldMarker = Marker::where("kode", $validatedRequest['modify_marker_kode_marker'])->first();

            // if (
            //     $oldMarker->act_costing_id != $validatedRequest["modify_marker_no_ws"] ||
            //     $oldMarker->color != $validatedRequest["modify_marker_color"] ||
            //     $oldMarker->panel != $validatedRequest["modify_marker_panel"]
            // ) {

                // Marker Code
                $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
                $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
                $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);

                // Order Detail Data
                $data = collect(DB::connection('mysql_sb')->select("
                    select
                        ac.id,
                        ac.id_buyer,
                        ac.kpno,
                        ac.styleno,
                        sd.color,
                        so_det_color.color as colors,
                        ac.qty order_qty,
                        ms.supplier buyer,
                        k.cons cons_ws,
                        k.unit unit_cons_ws,
                        mp.nama_panel panel,
                        sd.id as so_det_id,
                        sd.size,
                        sd.dest,
                        sum(sd.qty) order_qty
                    from
                        bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        inner join masteritem mi on k.id_item = mi.id_gen
                        inner join masterpanel mp on k.id_panel = mp.id
                        left join (
                            select
                                ac.id,
                                GROUP_CONCAT(DISTINCT sd.color) as color
                            from
                                so_det sd
                                inner join so on so.id = sd.id_so
                                inner join act_costing ac on ac.id = so.id_cost
                            where
                                ac.id = '" . $validatedRequest["modify_marker_no_ws"] . "'
                            group by
                                ac.id
                        ) so_det_color on so_det_color.id = ac.id
                    where
                        ac.id = '" . $validatedRequest["modify_marker_no_ws"] . "' and sd.color = '" . $validatedRequest["modify_marker_color"] . "' and mp.nama_panel ='" . $validatedRequest["modify_marker_panel"] . "' and k.status = 'M' and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                    group by
                        sd.id, k.id_item, k.unit
                "));

                // Current Order Data
                $currentData = $data->first();

                // Create New Marker
                $markerStore = Marker::create([
                    'tgl_cutting' => $oldMarker->tgl_cutting,
                    'kode' => $markerCode,
                    'act_costing_id' => $currentData->id,
                    'act_costing_ws' => $currentData->kpno,
                    'buyer' => $currentData->buyer,
                    'style' => $currentData->styleno,
                    'cons_ws' => $currentData->cons_ws,
                    'unit_cons_ws' => $currentData->unit_cons_ws,
                    'color' => $currentData->color,
                    'panel' => $currentData->panel,
                    'panjang_marker' => $oldMarker->panjang_marker,
                    'unit_panjang_marker' => $oldMarker->unit_panjang_marker,
                    'comma_marker' => $oldMarker->comma_marker,
                    'unit_comma_marker' => $oldMarker->unit_comma_marker,
                    'lebar_marker' => $oldMarker->lebar_marker,
                    'unit_lebar_marker' => $oldMarker->unit_lebar_marker,
                    'lebar_ws' => $oldMarker->lebar_ws,
                    'unit_lebar_ws' => $oldMarker->unit_lebar_ws,
                    'gelar_qty' => $oldMarker->gelar_qty,
                    'gelar_qty_balance' => $oldMarker->gelar_qty_balance,
                    'po_marker' => $oldMarker->po_marker,
                    'urutan_marker' => $oldMarker->urutan_marker,
                    'cons_marker' => $oldMarker->cons_marker,
                    'unit_cons_marker' => $oldMarker->unit_cons_marker,
                    'gramasi' => $oldMarker->gramasi,
                    'tipe_marker' => $oldMarker->tipe_marker,
                    'notes' => $oldMarker->notes,
                    'cons_piping' => $oldMarker->cons_piping,
                    'unit_cons_piping' => $oldMarker->unit_cons_piping,
                    'cancel' => 'N',
                ]);

                Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Create New Marker : ".($markerStore->id));

                // Generate Marker Detail Array
                $timestamp = Carbon::now();
                $markerId = $markerStore->id;
                $markerDetailData = [];
                if ($oldMarker && $oldMarker->markerDetails) {

                    // Loop through old marker detail
                    foreach ($oldMarker->markerDetails as $markerDetail) {

                        // When Marker Detail's Order is available
                        if ($markerDetail->masterSbWs) {

                            // Search for Similar So Det
                            $currentSoDet = $data->where("size", ($markerDetail->masterSbWs->size))->where("dest", $markerDetail->masterSbWs->dest)->first();
                            if (!$currentSoDet) {
                                $currentSoDet = $data->where("size", ($markerDetail->masterSbWs->size))->first();
                            }
                            if (!$currentSoDet) {
                                $currentSoDet = $data->filter(function($item) use ($markerDetail) {
                                    return Str::startsWith($item->size, $markerDetail->masterSbWs->size);
                                })->first();
                            }
                            if (!$currentSoDet) {
                                $currentSoDet = $data->filter(function($item) use ($markerDetail) {
                                    return Str::endsWith($item->size, $markerDetail->masterSbWs->size);
                                })->first();
                            }

                            // When So Det Found
                            if ($currentSoDet) {

                                // Check marker detail array availability
                                $filtered = array_filter($markerDetailData, function($value) use ($currentSoDet, $markerDetail) {
                                    return $value["so_det_id"] == $currentSoDet->so_det_id && $value["ratio"] > $markerDetail->ratio;
                                });

                                // If not found, push to marker detail array
                                if (count($filtered) < 1) {

                                    // Push to Marker Detail Array
                                    array_push($markerDetailData, [
                                        "marker_id" => $markerId,
                                        "so_det_id" => $currentSoDet->so_det_id,
                                        "size" => $currentSoDet->size.($currentSoDet->dest && $currentSoDet->dest != "-" ? " - ".$currentSoDet->dest : ""),
                                        "ratio" => $markerDetail->ratio,
                                        "cut_qty" => ($markerDetail->cut_qty > 0 ? $markerDetail->cut_qty : $markerDetail->ratio * $markerStore->gelar_qty),
                                        "cancel" => 'N',
                                        "created_at" => $timestamp,
                                        "updated_at" => $timestamp,
                                    ]);
                                }
                            }
                        }
                    }
                }

                // Upsert to marker detail
                $markerDetailStore = null;
                if (count($markerDetailData) > 0) {
                    $markerDetailStore = MarkerDetail::upsert(
                            $markerDetailData, // array of rows
                            ['marker_id', 'so_det_id'], // unique constraint columns
                            ['ratio', 'cut_qty', 'cancel', 'updated_at'] // columns to update if a match is found
                        );

                    Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Modify Marker Detail : ".json_encode($markerDetailData));
                } else {
                    return array(
                        "status" => 400,
                        "message" => "Tidak ditemukan size.",
                        "additional" => [],
                    );
                }

                // When marker and marker detail successfully stored
                if ($markerStore && $markerDetailStore) {

                    // Update Form Cut with new marker
                    $updateFormCut = FormCutInput::where("id", $validatedRequest["modify_marker_form_id"])->update([
                        "marker_id" => $markerId,
                        "id_marker" => $markerCode
                    ]);

                    Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Update Form Marker : ".($markerId)." ".($markerCode));

                    // Check Part Form
                    $partForm = PartForm::where("form_id", $validatedRequest["modify_marker_form_id"])->first();

                    // If Part Form exist, update the part form with new part
                    if ($partForm) {

                        // Check Part
                        $part = Part::where("act_costing_id", $currentData->id)->where("panel", $currentData->panel)->first();

                        // When Part is not exist then create it
                        if (!$part) {

                            // Part Code
                            $partCount = Part::selectRaw("MAX(kode) latest_kode")->first();
                            $latestPartNumber = intval(substr($partCount->latest_kode, -5)) + 1;
                            $partNumber = 'PRT' . str_pad($latestPartNumber, 5, '0', STR_PAD_LEFT);

                            // Create Part
                            $part = Part::create([
                                "kode" => $partNumber,
                                "act_costing_id" => $currentData->id,
                                "act_costing_ws" => $currentData->kpno,
                                "buyer" => $currentData->buyer,
                                "style" => $currentData->styleno,
                                "color" => $data->unique('colors')->pluck('colors')->implode(', '),
                                "panel" => $data->unique('panel')->pluck('panel')->implode(', '),
                                "panel_status" => $data->unique('panel_status')->pluck('panel_status')->implode(', '),
                            ]);

                            Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Create New Part : ".($part->id));

                            // Check Old Part
                            $oldPart = Part::where("act_costing_id", $oldMarker->act_costing_id)->
                                where("panel", $oldMarker->panel)->
                                first();
                            if ($oldPart) {

                                // Check Old Part Detail
                                $oldPartDetail = PartDetail::where("part_id", $oldPart->id)->get();

                                // Loop through old part detail to create new part detail
                                foreach ($oldPartDetail as $partDetail) {

                                    // New Part Detail
                                    $currentPartDetail = PartDetail::create([
                                        "part_id" => $part->id,
                                        "master_part_id" => $partDetail->master_part_id,
                                        "master_secondary_id" => $partDetail->master_secondary_id,
                                    ]);

                                    Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Create Part's Part Detail : ".($currentPartDetail));

                                    // New Part Detail Secondaries
                                    foreach ($partDetail->partDetailSecondaries as $partDetailSecondary) {
                                        $currentPartDetailSecondary = PartDetailSecondary::create([
                                            "part_detail_id" => $currentPartDetail->id,
                                            "master_secondary" => $partDetailSecondary->master_part_id,
                                        ]);

                                        Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Create Part Detail's Secondaries : ".($currentPartDetailSecondary));
                                    }
                                }
                            }
                        }

                        // Get Part Detail
                        $partId = $part->id;
                        $partDetail = PartDetail::where("part_id", $partId)->get();

                        // Part Form Code
                        $partFormCount = PartForm::selectRaw("MAX(kode) latest_kode")->first();
                        $latestPartFormNumber = intval(substr($partFormCount->latest_kode, -5)) + 1;
                        $partFormNumber = 'PFM' . str_pad($latestPartFormNumber, 5, '0', STR_PAD_LEFT);

                        // Part Form Update
                        $partFormCut = PartForm::where("form_id", $validatedRequest["modify_marker_form_id"])->update([
                            "part_id" => $partId,
                        ]);

                        Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Update Part Form Allocation : ".($partFormCut));

                        // Update Stocker
                        if ($oldMarker && $oldMarker->markerDetails) {

                            // Loop through old marker detail to find matching so det id with new marker detail
                            foreach ($oldMarker->markerDetails as $markerDetail) {

                                // Check marker detail master_sb_ws
                                if ($markerDetail->masterSbWs) {

                                    // Search for Similar So Det
                                    $currentSoDet = $data->where("size", ($markerDetail->masterSbWs->size))->where("dest", $markerDetail->masterSbWs->dest)->first();
                                    if (!$currentSoDet) {
                                        $currentSoDet = $data->where("size", ($markerDetail->masterSbWs->size))->first();
                                    }
                                    if (!$currentSoDet) {
                                        $currentSoDet = $data->filter(function($item) use ($markerDetail) {
                                            return Str::startsWith($item->size, $markerDetail->masterSbWs->size);
                                        })->first();
                                    }
                                    if (!$currentSoDet) {
                                        $currentSoDet = $data->filter(function($item) use ($markerDetail) {
                                            return Str::endsWith($item->size, $markerDetail->masterSbWs->size);
                                        })->first();
                                    }

                                    // When Similar So Det was Found, update Stocker with matching so det id
                                    if ($currentSoDet) {

                                        // Get Stocker List
                                        $checkStocker = Stocker::where("form_cut_id", $validatedRequest["modify_marker_form_id"])
                                            ->where("so_det_id", $markerDetail->masterSbWs->id_so_det)
                                            ->get();

                                        // Loop through stocker list to update
                                        foreach ($checkStocker as $stocker) {

                                            // Current Master Part
                                            $currentMasterPart = $stocker->partDetail ? $stocker->partDetail->master_part_id : null;

                                            // Update Stocker when Master Part was found
                                            if ($currentMasterPart) {

                                                // Get matching Master Part
                                                $currentPartDetail = $partDetail->filter(function ($item) use ($currentMasterPart) {
                                                    return $item->master_part_id == $currentMasterPart;
                                                })->first();

                                                if ($currentPartDetail) {
                                                    // Update Stocker
                                                    $stocker->part_detail_id = $currentPartDetail->id;
                                                    $stocker->act_costing_ws = $currentSoDet->kpno;
                                                    $stocker->color = $currentSoDet->color;
                                                    $stocker->so_det_id = $currentSoDet->so_det_id;
                                                    $stocker->size = $currentSoDet->size;
                                                    $stocker->notes .= $stocker->notes." MODIFY MARKER UPDATE SO DET AND PART DETAIL";
                                                    $stocker->save();

                                                    Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Update Stocker MASTER_PART found : ".($partFormCut));
                                                } else {
                                                    $stocker->cancel = 'Y';
                                                    $stocker->notes .= $stocker->notes." MODIFY MARKER CANCEL NO PART DETAIL FOUND";
                                                    $stocker->save();

                                                    Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Cancel Stocker PART DETAIL not found : ".($partFormCut));
                                                }

                                            }

                                            // Cancel Stocker when master part was not found
                                            else {
                                                $stocker->cancel = 'Y';
                                                $stocker->notes .= $stocker->notes." MODIFY MARKER CANCEL NO MASTER PART FOUND";
                                                $stocker->save();

                                                Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Cancel Stocker MASTER_PART not found : ".($partFormCut));
                                            }
                                        }
                                    }

                                    // When Similar So Det was not found, cancel all stocker with matching so det id
                                    else {
                                        // Get Stocker List
                                        $checkStocker = Stocker::where("form_cut_id", $validatedRequest["modify_marker_form_id"])
                                            ->where("so_det_id", $markerDetail->masterSbWs->id_so_det)
                                            ->update([
                                                "notes" => DB::raw("CONCAT(notes, ' MODIFY MARKER CANCEL NO SIMILAR SO DET FOUND')"),
                                                "cancel" => 'Y',
                                            ]);

                                        Log::channel("Modify Form Marker")->info($currentForm->no_form." / ".$validatedRequest["modify_marker_form_id"]." Cancel Stocker SIMILAR SO DET not found : ".($partFormCut));
                                    }
                                }
                            }
                        }

                        // Reorder Stocker Range Numbering
                        $stockerService->reorderStockerNumbering($partId, $validatedRequest["modify_marker_color"], $currentForm->no_cut);
                    }

                    return array(
                        "status" => 300,
                        "message" => "Marker Form berhasil diubah.",
                        "additional" => [],
                    );
                } else {
                    return array(
                        "status" => 400,
                        "message" => "Marker tidak berhasil diubah.",
                        "additional" => [],
                    );
                }
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan.",
            "additional" => [],
        );
    }

    public function updateFormSwap(Request $request, StockerService $stockerService) {
        ini_set("max_execution_time", 36000);
        ini_set("memory_limit", "2048M");

        $validatedRequest = $request->validate([
            "modify_swap_form_id" => "required",
            "modify_swap_from" => "required",
            "modify_swap_to" => "required",
        ]);

        if ($validatedRequest) {
            $form = FormCutInput::where("id", $validatedRequest['modify_swap_form_id'])->first();

            $oldMarker = Marker::where("id", $form->marker->id)->first();

            $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
            $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
            $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);

            $markerStore = Marker::create([
                'tgl_cutting' => $oldMarker->tgl_cutting,
                'kode' => $markerCode,
                'act_costing_id' => $oldMarker->act_costing_id,
                'act_costing_ws' => $oldMarker->act_costing_ws,
                'buyer' => $oldMarker->buyer,
                'style' => $oldMarker->style,
                'cons_ws' => $oldMarker->cons_ws,
                'color' => $oldMarker->color,
                'panel' => $oldMarker->panel,
                'panjang_marker' => $oldMarker->panjang_marker,
                'unit_panjang_marker' => $oldMarker->unit_panjang_marker,
                'comma_marker' => $oldMarker->comma_marker,
                'unit_comma_marker' => $oldMarker->unit_comma_marker,
                'lebar_marker' => $oldMarker->lebar_marker,
                'unit_lebar_marker' => $oldMarker->unit_lebar_marker,
                'gelar_qty' => $oldMarker->gelar_qty,
                'gelar_qty_balance' => $oldMarker->gelar_qty_balance,
                'po_marker' => $oldMarker->po_marker,
                'urutan_marker' => $oldMarker->urutan_marker,
                'cons_marker' => $oldMarker->cons_marker,
                'gramasi' => $oldMarker->gramasi,
                'tipe_marker' => $oldMarker->tipe_marker,
                'notes' => $oldMarker->notes,
                'cons_piping' => $oldMarker->cons_piping,
                'cancel' => 'N',
            ]);

            $timestamp = Carbon::now();
            $markerId = $markerStore->id;
            $markerDetailData = [];

            if ($oldMarker && $oldMarker->markerDetails()) {
                $fromSize = $oldMarker->markerDetails()->firstWhere("so_det_id", $validatedRequest["modify_swap_from"]);
                $toSize = $oldMarker->markerDetails()->firstWhere("so_det_id", $validatedRequest["modify_swap_to"]);

                foreach ($oldMarker->markerDetails()->get() as $markerDetail) {
                    if ($fromSize && $markerDetail->so_det_id == $fromSize->so_det_id) {
                        array_push($markerDetailData, [
                            "marker_id" => $markerId,
                            "so_det_id" => $fromSize->so_det_id,
                            "size" => $fromSize->size,
                            "ratio" => $toSize->ratio,
                            "cut_qty" => ($toSize->cut_qty > 0 ? $toSize->cut_qty : $toSize->ratio * $markerStore->gelar_qty),
                            "cancel" => 'N',
                            "created_at" => $timestamp,
                            "updated_at" => $timestamp,
                        ]);
                    } else if ($toSize && $markerDetail->so_det_id == $toSize->so_det_id) {
                        array_push($markerDetailData, [
                            "marker_id" => $markerId,
                            "so_det_id" => $toSize->so_det_id,
                            "size" => $toSize->size,
                            "ratio" => $fromSize->ratio,
                            "cut_qty" => ($fromSize->cut_qty > 0 ? $fromSize->cut_qty : $fromSize->ratio * $markerStore->gelar_qty),
                            "cancel" => 'N',
                            "created_at" => $timestamp,
                            "updated_at" => $timestamp,
                        ]);
                    } else {
                        array_push($markerDetailData, [
                            "marker_id" => $markerId,
                            "so_det_id" => $markerDetail->so_det_id,
                            "size" => $markerDetail->size,
                            "ratio" => $markerDetail->ratio,
                            "cut_qty" => ($markerDetail->cut_qty > 0 ? $markerDetail->cut_qty : $markerDetail->ratio * $markerStore->gelar_qty),
                            "cancel" => 'N',
                            "created_at" => $timestamp,
                            "updated_at" => $timestamp,
                        ]);
                    }
                }

                $markerDetailStore = null;
                if (count($markerDetailData) > 0) {
                    $markerDetailStore = MarkerDetail::upsert(
                            $markerDetailData, // array of rows
                            ['marker_id', 'so_det_id'], // unique constraint columns
                            ['ratio', 'cut_qty', 'cancel', 'updated_at'] // columns to update if a match is found
                        );
                }

                if ($markerStore && $markerDetailStore && ($fromSize && $toSize)) {
                    $updateFormCut = FormCutInput::where("id", $form->id)->update([
                        "marker_id" => $markerId,
                        "id_marker" => $markerCode
                    ]);

                    // Stocker
                    if ($fromSize && $toSize) {
                        $tempId = -1 * time(); // use a unique negative temp value

                        // Step 1: Temporarily move one of the rows to a temp so_det_id
                        Stocker::where("form_cut_id", $form->id)
                            ->where("so_det_id", $fromSize->so_det_id)
                            ->update([
                                "so_det_id" => $tempId,
                                "size" => $toSize->size,
                                "notes" => "SWAP SIZE"
                            ]);

                        // Step 2: Move second row to first's original so_det_id
                        Stocker::where("form_cut_id", $form->id)
                            ->where("so_det_id", $toSize->so_det_id)
                            ->update([
                                "so_det_id" => $fromSize->so_det_id,
                                "size" => $fromSize->size,
                                "notes" => "SWAP SIZE"
                            ]);

                        // Step 3: Move temp row to second's original so_det_id
                        Stocker::where("form_cut_id", $form->id)
                            ->where("so_det_id", $tempId)
                            ->update([
                                "so_det_id" => $toSize->so_det_id
                            ]);
                    }
                    $partForm = PartForm::where("form_id", $form->id)->first();
                    $partId = $partForm->part->id;

                    $stockerService->reorderStockerNumbering($partId);

                    return array(
                        "status" => 300,
                        "message" => "Proses Selesai.",
                        "additional" => [],
                    );
                }
            }
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan.",
            "additional" => [],
        );
    }

    public function updateFormGroup(Request $request) {
        ini_set('max_execution_time', 3600);

        $validatedRequest = $request->validate([
            "form_cut_id" => "required",
            "no_form" => "required",
            "form_group" => "required",
            "form_group_new" => "required",
        ]);

        $formTable = 'form_cut_input_detail';
        $formType = 'form_cut_id';
        switch ($request->form_type) {
            case 'reject' :
                $formTable = 'form_cut_reject';
                $formType = 'form_reject_id';
                break;
            case 'piece' :
                $formTable = 'form_cut_piece_detail';
                $formType = 'form_piece_id';
                break;
            default :
                $formTable = 'form_cut_input_detail';
                $formType = 'form_cut_id';
                break;
        }

        if ($validatedRequest['form_group']) {
            if ($validatedRequest['form_group_new']) {
                // Update Form Group
                $updateFormGroup = DB::table($formTable)->where(($formTable == "form_cut_reject" ? "id" : ($formTable == "form_cut_piece_detail" ? "form_id" : ($formTable == "form_cut_input_detail" ? "form_cut_id" : ""))), $validatedRequest["form_cut_id"])->where("group_stocker", $validatedRequest["form_group"])->update([
                    ($formTable == "form_cut_reject" ? "group" : "group_roll") => $validatedRequest["form_group_new"]
                ]);

                // Update Stocker Group
                $updateStockerGroup = DB::table("stocker_input")->where($formType, $validatedRequest["form_cut_id"])->where("group_stocker", $validatedRequest["form_group"])->update([
                    "shade" => $validatedRequest["form_group_new"]
                ]);

                if ($updateFormGroup && $updateStockerGroup) {
                    return array(
                        'status' => 200,
                        'message' => 'Form Group <br> "'.$validatedRequest['no_form'].'" <br> "'.$validatedRequest['form_group'].'" <br> berhasil diubah ke <br> <b>"'.$validatedRequest['form_group_new'].'"</b>',
                        'redirect' => '',
                        'table' => '',
                        'additional' => [],
                    );
                }
            }
        }

        return array(
            'status' => 400,
            'message' => 'Terjadi Kesalahan.',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function deleteRedundantRoll(Request $request, CuttingService $cuttingService) {
        if ($request->id_roll) {
            return $cuttingService->deleteRedundantRoll($request->id_roll);
        }

        return array([
            "status" => 400,
            "message" => "Gagal"
        ]);
    }

    public function importCuttingManual(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = rand().$file->getClientOriginalName();

        $file->move('file_upload',$nama_file);

        $import = Excel::import(new importCuttingManual, public_path('/file_upload/'.$nama_file));

        if ($import) {
            return array(
                "status" => 200,
                "message" => 'Data Berhasil Di Upload',
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => 'Terjadi Kesalahan',
            "additional" => [],
        );
    }

    public function importSaldoAwalCutting(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = rand().$file->getClientOriginalName();

        $file->move('file_upload',$nama_file);

        $import = Excel::import(new importSaldoAwalCutting, public_path('/file_upload/'.$nama_file));

        if ($import) {
            return array(
                "status" => 200,
                "message" => 'Data Berhasil Di Upload',
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => 'Terjadi Kesalahan',
            "additional" => [],
        );
    }

    public function getSaldoAwalCuttingTmp(Request $request)
    {
        $saldo = MutasiCuttingPcsSaldoTmp::selectRaw("
                mut_cut_pcs_tmp_pre.tgl_trans,
                mut_cut_pcs_tmp_pre.id_so_det,
                mastersupplier.Supplier as buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size,
                masterpanel.nama_panel as panel,
                mut_cut_pcs_tmp_pre.saldo
            ")->
            leftJoin("signalbit_erp.so_det", "so_det.id", "=", "mut_cut_pcs_tmp_pre.id_so_det")->
            leftJoin("signalbit_erp.so", "so.id", "=", "so_det.id_so")->
            leftJoin("signalbit_erp.act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("signalbit_erp.mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("signalbit_erp.masterpanel", "masterpanel.nama_panel", "=", "mut_cut_pcs_tmp_pre.panel")->
            where("mut_cut_pcs_tmp_pre.created_by", Auth::user()->id)->
            get();

        return DataTables::of($saldo)->toJson();
    }

    public function saveSaldoAwalCutting(Request $request)
    {
        $saldoTmps = MutasiCuttingPcsSaldoTmp::where("created_by", Auth::user()->id)->get();

        $status = null;
        $message = "";
        if ($saldoTmps && $saldoTmps->count() > 0) {

            foreach ($saldoTmps as $saldoTmp) {
                // Take Order Info
                $orderInfo = DB::connection("mysql_sb")->select("
                    SELECT
                        mastersupplier.Supplier as buyer,
                        act_costing.id as id_cost,
                        act_costing.kpno as ws,
                        act_costing.styleno as style,
                        so_det.color,
                        so_det.size,
                        so_det.id
                    FROM
                        so_det
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    WHERE
                        so_det.id = '".$saldoTmp->id_so_det."' and
                        (so_det.cancel != 'Y' OR so_det.cancel IS NULL)
                    LIMIT 1
                ");

                if ($orderInfo && isset($orderInfo[0])) {

                    // Take Panel
                    $panel = DB::connection("mysql_sb")->table("masterpanel")->
                        select("id", "nama_panel")->
                        where("nama_panel", $saldoTmp->panel)->
                        first();

                    if ($panel) {

                        // Create Cutting Pcs Saldo
                        $createMutasiCuttingPcsSaldo = MutasiCuttingPcsSaldo::create([
                            "tgl_trans" => $saldoTmp->tgl_trans,
                            "id_so_det" => $orderInfo[0]->id,
                            "panel" => $panel->nama_panel,
                            "saldo" => $saldoTmp->saldo,
                        ]);

                        if ($createMutasiCuttingPcsSaldo) {
                            // Delete Created Saldo Temporary
                            $saldoTmp->delete();

                            $message .= "Saldo ".$orderInfo[0]->ws." / ".$orderInfo[0]->color." / ".$orderInfo[0]->size." / ".$orderInfo[0]->id." untuk panel ".$panel->nama_panel." dengan QTY : ".$saldoTmp->saldo." berhasil disimpan <br>";
                        }
                    } else {
                        $message .= "Saldo ".$orderInfo[0]->ws." / ".$orderInfo[0]->color." / ".$orderInfo[0]->size." / ".$orderInfo[0]->id." Panel tidak ditemukan <br>";
                    }
                } else {
                    $message .= "Saldo ".$saldoTmp->id_so_det." Gagal disimpan <br>";
                }
            }
        } else {
            $message .= "Saldo Cutting Temporary tidak ditemukan";
        }

        return array(
            "status" => 200,
            "message" => $message,
        );
    }
}
