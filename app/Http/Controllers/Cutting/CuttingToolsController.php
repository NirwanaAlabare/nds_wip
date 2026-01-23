<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\FormCutInput;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Part\Part;
use App\Models\Part\PartForm;
use App\Models\Stocker\Stocker;
use App\Models\SignalBit\ActCosting;
use App\Services\StockerService;
use App\Services\CuttingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
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

    public function fixRollQty(Request $request) {
        $rollId = $request->id_roll;
        $rollQty = $request->qty;
        $rollUse = null;

        if (!$rollQty) {
            $lastInput = FormCutInputDetail::selectRaw("
                SUM(total_pemakaian_roll) total_lembar,
                MIN(sisa_kain) as sisa_kain
            ")->
            where("id_roll", $request->id_roll)->
            groupBy("id_roll")->
            first();

            if ($lastInput) {
                $rollQty = $lastInput->sisa_kain;
                $rollUse = $lastInput->total_lembar;
            }
        }

        $additionalQuery = "";
        if ($rollId) {
            $additionalQuery = "WHERE scanned_item.id_roll = '".$rollId."'";
        } else {
            $additionalQuery = "WHERE scanned_item.qty != sub.sisa_kain";
        }

        $roll = collect(DB::select("
            SELECT
                scanned_item.id_roll,
                scanned_item.qty_in,
                scanned_item.qty,
                sub.total_pakai_qty,
                sub.sisa_kain
            FROM scanned_item
            INNER JOIN (
                SELECT
                        id_roll,
                    MAX(qty) AS max_qty,
                        SUM(total_pemakaian_roll + short_roll) AS total_pakai_qty,
                        MIN( CASE WHEN form_cut_input_detail.STATUS = 'extension' OR form_cut_input_detail.STATUS = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ) sisa_kain
                FROM form_cut_input_detail
                WHERE id_roll IS NOT NULL
                GROUP BY id_roll
            ) sub ON scanned_item.id_roll = sub.id_roll
            ".$additionalQuery."
        "));

        if ($roll) {
            if ($rollId) {
                $scannedItem = ScannedItem::where("id_roll", $rollId)->first();

                if ($scannedItem) {
                    Log::channel('fixRollQty')->info([
                        "Fix Roll Qty",
                        "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                        $scannedItem
                    ]);

                    if ($scannedItem->qty != $rollQty) {
                        $scannedItem->qty = $rollQty;
                    } else {
                        $currentRoll = $roll->where("id_roll", $rollId)->first();

                        if ($currentRoll) {
                            if ($scannedItem->qty != $currentRoll->sisa_kain) {
                                $scannedItem->qty = $currentRoll->sisa_kain;
                            }
                        }
                    }

                    if ($rollUse > 0 && $scannedItem->qty_pakai != $rollUse) {
                        $scannedItem->qty_pakai = $rollUse;
                    }

                    $scannedItem->save();

                    return array(
                        "status" => 200,
                        "message" => $scannedItem->id_roll." berhasil diubah."
                    );
                }
            } else {
                Log::channel('fixRollQty')->info([
                    "Fix Roll Qty",
                    "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                    $roll
                ]);

                $updateRollQty = DB::statement("
                    UPDATE scanned_item
                    INNER JOIN (
                        SELECT
                            id_roll,
                            MIN(
                                CASE
                                    WHEN form_cut_input_detail.status IN ('extension', 'extension complete')
                                    THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll
                                    ELSE form_cut_input_detail.sisa_kain
                                END
                            ) AS sisa_kain
                        FROM form_cut_input_detail
                        WHERE id_roll IS NOT NULL
                        GROUP BY id_roll
                    ) sub ON scanned_item.id_roll = sub.id_roll
                    SET scanned_item.qty = sub.sisa_kain
                    WHERE scanned_item.qty != sub.sisa_kain
                ");

                return array(
                    "status" => 200,
                    "message" => $roll->count()." roll berhasil diubah."
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Roll yang tidak sesuai tidak ditemukan."
        );
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

            $oldMarker = Marker::where("kode", $validatedRequest['modify_marker_kode_marker'])->first();

            // if (
            //     $oldMarker->act_costing_id != $validatedRequest["modify_marker_no_ws"] ||
            //     $oldMarker->color != $validatedRequest["modify_marker_color"] ||
            //     $oldMarker->panel != $validatedRequest["modify_marker_panel"]
            // ) {
                $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
                $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
                $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);

                $data = collect(DB::connection('mysql_sb')->select("
                    select
                        ac.id,
                        ac.id_buyer,
                        ac.kpno,
                        ac.styleno,
                        sd.color,
                        ac.qty order_qty,
                        ms.supplier buyer,
                        k.cons cons_ws,
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
                    where
                        ac.id = '" . $validatedRequest["modify_marker_no_ws"] . "' and sd.color = '" . $validatedRequest["modify_marker_color"] . "' and mp.nama_panel ='" . $validatedRequest["modify_marker_panel"] . "' and k.status = 'M' and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                    group by
                        sd.id, k.id_item, k.unit
                "));

                $currentData = $data->first();

                $markerStore = Marker::create([
                    'tgl_cutting' => $oldMarker->tgl_cutting,
                    'kode' => $markerCode,
                    'act_costing_id' => $currentData->id,
                    'act_costing_ws' => $currentData->kpno,
                    'buyer' => $currentData->buyer,
                    'style' => $currentData->styleno,
                    'cons_ws' => $currentData->cons_ws,
                    'color' => $currentData->color,
                    'panel' => $currentData->panel,
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
                if ($oldMarker && $oldMarker->markerDetails) {
                    foreach ($oldMarker->markerDetails as $markerDetail) {
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

                            // When Found
                            if ($currentSoDet) {
                                $filtered = array_filter($markerDetailData, function($value) use ($currentSoDet, $markerDetail) {
                                    return $value["so_det_id"] == $currentSoDet->so_det_id && $value["ratio"] > $markerDetail->ratio;
                                });

                                if (count($filtered) < 1) {
                                    // Mass Upsert Marker Detail
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

                $markerDetailStore = null;
                if (count($markerDetailData) > 0) {
                    $markerDetailStore = MarkerDetail::upsert(
                            $markerDetailData, // array of rows
                            ['marker_id', 'so_det_id'], // unique constraint columns
                            ['ratio', 'cut_qty', 'cancel', 'updated_at'] // columns to update if a match is found
                        );
                }

                if ($markerStore && $markerDetailStore) {
                    $updateFormCut = FormCutInput::where("id", $validatedRequest["modify_marker_form_id"])->update([
                        "marker_id" => $markerId,
                        "id_marker" => $markerCode
                    ]);

                    $partForm = PartForm::where("form_id", $validatedRequest["modify_marker_form_id"])->first();
                    if ($partForm) {
                        // Part
                        $part = Part::where("act_costing_id", $currentData->id)->where("panel", $currentData->panel)->first();
                        if (!$part) {
                            $partCount = Part::selectRaw("MAX(kode) latest_kode")->first();
                            $latestPartNumber = intval(substr($partCount->latest_kode, -5)) + 1;
                            $partNumber = 'PRT' . str_pad($latestPartNumber, 5, '0', STR_PAD_LEFT);

                            $part = Part::create([
                                "kode" => $partNumber,
                                "act_costing_id" => $currentData->id,
                                "act_costing_ws" => $currentData->kpno,
                                "buyer" => $currentData->buyer,
                                "style" => $currentData->styleno,
                                "color" => $data->unique('color')->pluck('color')->implode(', '),
                                "panel" => $data->unique('panel')->pluck('panel')->implode(', '),
                            ]);
                        }

                        $partId = $part->id;

                        // Part Form
                        $partFormCount = PartForm::selectRaw("MAX(kode) latest_kode")->first();
                        $latestPartFormNumber = intval(substr($partFormCount->latest_kode, -5)) + 1;
                        $partFormNumber = 'PFM' . str_pad($latestPartFormNumber, 5, '0', STR_PAD_LEFT);

                        $partFormCut = PartForm::where("form_id", $validatedRequest["modify_marker_form_id"])->update([
                            "part_id" => $partId,
                        ]);

                        // Update Stocker
                        if ($oldMarker && $oldMarker->markerDetails) {
                            if ($markerDetailStore) {
                                foreach ($oldMarker->markerDetails as $markerDetail) {
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

                                        // When Found
                                        if ($currentSoDet) {
                                            $filtered = array_filter($markerDetailData, function($value) use ($currentSoDet, $markerDetail) {
                                                return $value["so_det_id"] == $currentSoDet->so_det_id && $value["ratio"] > $markerDetail->ratio;
                                            });

                                            if (count($filtered) < 1) {
                                                // Update Stocker
                                                Stocker::where("form_cut_id", $validatedRequest["modify_marker_form_id"])
                                                    ->where("so_det_id", $markerDetail->masterSbWs->id_so_det)
                                                    ->update([
                                                        // "part_id" => $currentSoDet->kpno,
                                                        // "act_costing_ws" => $currentSoDet->kpno,
                                                        // "color" => $currentSoDet->color,
                                                        // "so_det_id" => $currentSoDet->so_det_id,
                                                        // "size" => $currentSoDet->size . ($currentSoDet->dest && $currentSoDet->dest != "-" ? " - " . $currentSoDet->dest : ""),
                                                        "notes" => DB::raw("CONCAT(notes, ' MODIFY MARKER CANCEL')"),
                                                        "cancel" => 'Y'
                                                    ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $stockerService->reorderStockerNumbering($partId, $validatedRequest["modify_marker_color"], $currentForm->no_cut);
                    }

                    return array(
                        "status" => 300,
                        "message" => "Marker Form berhasil diubah.",
                        "additional" => [],
                    );
                }
            // } else {
            //     return array(
            //         "status" => 400,
            //         "message" => "Tidak ada perubahan.",
            //         "additional" => [],
            //     );
            // }
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
}
