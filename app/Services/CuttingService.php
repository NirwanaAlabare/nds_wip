<?php

namespace App\Services;

use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\ScannedItem;
use Illuminate\Http\Request;
use Illuminate\HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;
use Carbon\Carbon;

class CuttingService
{
    public function recalculateForm($formId)
    {
        ini_set('max_execution_time', 360000);

        if ($formId) {
            $formCut = FormCutInput::where("id", $formId)->first();

            if ($formCut) {
                // group stocker
                $formCutDetailsGroup = FormCutInputDetail::where("form_cut_id", $formCut->id)->where("no_form_cut_input", $formCut->no_form)->orderBy("created_at", "asc")->orderBy("updated_at", "asc")->get();
                $currentGroup = "";
                $groupNumber = 0;
                foreach ($formCutDetailsGroup as $formCutDetailGroup) {
                    if ($currentGroup != $formCutDetailGroup->group_roll) {
                        $currentGroup = $formCutDetailGroup->group_roll;
                        $groupNumber += 1;
                    }

                    $formCutDetailGroup->group_stocker = $groupNumber;
                    $formCutDetailGroup->save();
                }

                // Calculate
                $formCutDetails = $formCut->formCutInputDetails()->orderBy("form_cut_input_detail.created_at")->orderBy("form_cut_input_detail.updated_at")->get();

                if ($formCutDetails) {
                    // Recalculate

                    // Cons Data
                    $consWs = $formCut->marker->cons_ws;
                    $consMarker = $formCut->marker->cons_marker;
                    $consPiping = $formCut->marker->cons_piping;

                    // Ratio & Qty Cut
                    $totalRatio = $formCut->marker->markerDetails->sum("ratio");
                    $totalQtyCut = $totalRatio * $formCut->qty_ply;

                    // Details
                    $currentIdRoll = 0;
                    $currentSambungan = 0;
                    $currentQty = 0;
                    $totalLembar = 0;
                    $totalQtyFabric = 0;
                    $totalSisaFabric = 0;
                    $totalShortRoll = 0;
                    $latestStatus = "";
                    $currentStatus = "";
                    foreach ($formCutDetails as $formCutDetail) {
                        // Sambungan Roll
                        // $sambunganRoll = $formCutDetail->formCutInputDetailSambungan ? $formCutDetail->formCutInputDetailSambungan->sum("sambungan_roll") : 0;
                        $sambunganRoll = $formCutDetail->sambungan_roll;

                        // Check Qty
                        $qty = $currentQty > 0 && $formCutDetail->id_roll == $currentIdRoll ? $currentQty : $formCutDetail->qty;

                        // Panjang Act
                        $pAct = $formCut->p_act + ($formCut->comma_p_act/100);

                        if ($formCutDetail->berat_amparan > 0) {
                            $pAct = $formCutDetail->berat_amparan;
                        }

                        // Normal
                        if ($formCutDetail->sambungan == 0) {
                            // Sambungan
                            $sambungan = 0;

                            // Est. Ampar
                            $estAmpar = $qty / $pAct;

                            // Pemakaian Lembar
                            $pemakaianLembar = ($pAct * $formCutDetail->lembar_gelaran) + $sambunganRoll + $formCutDetail->sisa_gelaran;

                            // Total Pemakaian
                            $totalPemakaian = (($pAct * $formCutDetail->lembar_gelaran) + $formCutDetail->sisa_gelaran + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping + $sambunganRoll);

                            // Short Roll
                            $shortRoll = (($pAct * $formCutDetail->lembar_gelaran) + $formCutDetail->sambungan + $formCutDetail->sisa_gelaran + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping + $formCutDetail->sisa_kain + $sambunganRoll) - $qty;

                            // Sambungan
                            if ($formCutDetail->sisa_gelaran > 0) {
                                $currentSambungan = $pAct - $formCutDetail->sisa_gelaran;

                                $currentStatus = 'need extension';
                            } else {
                                $currentStatus = 'complete';
                            }

                            // Sisa Kain
                            $sisaKain = $formCutDetail->sisa_kain;

                            // Reset Qty
                            $currentQty = $sisaKain;
                            $currentIdRoll = $formCutDetail->id_roll;
                        // Sambungan
                        } else {
                            // Check Sambungan
                            $sambungan = $currentSambungan > 0 ? $currentSambungan : $formCutDetail->sambungan;
                            // Reset Sambungan
                            $currentSambungan = 0;

                            // Est. Ampar
                            $estAmpar = $qty / $pAct;

                            // Pemakaian Lembar
                            $pemakaianLembar = ($sambungan * $formCutDetail->lembar_gelaran) + $sambunganRoll + $formCutDetail->sisa_gelaran;

                            // Total Pemakaian
                            $totalPemakaian = (($sambungan * $formCutDetail->lembar_gelaran) + $formCutDetail->sisa_gelaran + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping + $sambunganRoll);

                            // Short Roll
                            $shortRoll = 0;

                            // Sisa Kain
                            $sisaKain = 0;

                            $currentStatus = 'extension complete';

                            // Reset Qty
                            $currentQty = $qty - (($sambungan * $formCutDetail->lembar_gelaran) + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping);
                            $currentIdRoll = $formCutDetail->id_roll;
                        }

                        // Save Detail
                        $formCutDetail->status = $currentStatus;
                        $formCutDetail->est_amparan = $estAmpar;
                        $formCutDetail->pemakaian_lembar = $pemakaianLembar;
                        $formCutDetail->total_pemakaian_roll = $totalPemakaian;
                        $formCutDetail->sambungan = $sambungan;
                        $formCutDetail->sisa_kain = $sisaKain;
                        $formCutDetail->short_roll = $shortRoll;
                        \Log::info("Detail Value = pemakaian : $pemakaianLembar, totalpemakaian : $totalPemakaian, sisakain : $sisaKain, shortroll : $shortRoll, currentIdRoll : $currentIdRoll, currentQty : $currentQty, currentSambungan : $currentSambungan");
                        $formCutDetail->save();

                        $totalLembar += $formCutDetail->lembar_gelaran;
                        $totalSisaFabric += $sisaKain;
                        $totalShortRoll += $shortRoll;
                        if ($latestStatus != 'extension complete') {
                            $totalQtyFabric += $qty;
                        }
                        $latestStatus = $formCutDetail->status;
                    }

                    // Cons. Calculate
                    $consActualGelaran = ($totalLembar * $totalRatio) > 0 ? ($totalQtyFabric - $totalSisaFabric)/($totalLembar * $totalRatio) : 0;
                    $consActualGelaranShortRolless = ($totalLembar * $totalRatio) > 0 ? ($totalQtyFabric - $totalSisaFabric + $totalSisaFabric)/($totalLembar * $totalRatio) : 0;

                    $consUpRateWs = (($consActualGelaran - $consWs)/$consWs) * 100;
                    $consUpRateMarker = (($consActualGelaran - $consMarker)/$consMarker) * 100;

                    $consUpRateWsNoSr = (($consActualGelaranShortRolless - $consWs)/$consWs) * 100;
                    $consUpRateMarkerNoSr = (($consActualGelaranShortRolless - $consMarker)/$consMarker) * 100;

                    $consAmpar = $formCut->gramasi * ($formCut->p_act + ($formCut->comma_p_act/100)) * $formCut->l_act / 1000;
                    $estPiping = $consPiping * $totalQtyCut;
                    $estKain = $consMarker * $totalQtyCut;

                    // Cons. Update
                    $formCut->cons_act = $consActualGelaran;
                    $formCut->cons_act_nosr = $consActualGelaranShortRolless;

                    $formCut->cons_ws_uprate = $consUpRateWs;
                    $formCut->cons_marker_uprate = $consUpRateMarker;

                    $formCut->cons_ws_uprate_nosr = $consUpRateWsNoSr;
                    $formCut->cons_marker_uprate_nosr = $consUpRateMarkerNoSr;

                    $formCut->cons_ampar = $consAmpar;
                    $formCut->est_pipping = $estPiping;
                    $formCut->est_kain = $estKain;

                    // Total Lembar
                    $formCut->total_lembar = $totalLembar;

                    // Save Form Cut
                    $formCut->save();
                }
            }
        }
    }

    public function deleteRedundant($idForm, $idRoll, $qtyRoll, $status) {
        if ($idForm && $idRoll && $qtyRoll && $status) {
            // Get the current roll usage
            $currentDetails = FormCutInputDetail::where("form_cut_id", $idForm)->where("id_roll", $idRoll)->where("qty", $qtyRoll)->where("status", $status)->whereNotNull("form_cut_id")->whereNotNull("id_roll")->get();
            if ($currentDetails && $currentDetails->count() > 1) {

                // Set Exception for the last row
                $exceptionId = $currentDetails->last() ? $currentDetails->last()->id : null;

                // Loop Over to delete all except the last row (exception)
                foreach ($currentDetails as $currentDetail) {
                    if ($exceptionId && $currentDetail->id != $exceptionId) {
                        // Delete history
                        DB::table("form_cut_input_detail_delete")->insert([
                            "form_cut_id" => $currentDetail->form_cut_id,
                            "no_form_cut_input" => $currentDetail->no_form_cut_input,
                            "id_roll" => $currentDetail->id_roll,
                            "id_item" => $currentDetail->id_item,
                            "color_act" => $currentDetail->color_act,
                            "detail_item" => $currentDetail->detail_item,
                            "group_roll" => $currentDetail->group_roll,
                            "lot" => $currentDetail->lot,
                            "roll" => $currentDetail->roll,
                            "qty" => $currentDetail->qty,
                            "unit" => $currentDetail->unit,
                            "sisa_gelaran" => $currentDetail->sisa_gelaran,
                            "sambungan" => $currentDetail->sambungan,
                            "sambungan_roll" => $currentDetail->sambungan_roll,
                            "est_amparan" => $currentDetail->est_amparan,
                            "lembar_gelaran" => $currentDetail->lembar_gelaran,
                            "average_time" => $currentDetail->average_time,
                            "kepala_kain" => $currentDetail->kepala_kain,
                            "sisa_tidak_bisa" => $currentDetail->sisa_tidak_bisa,
                            "reject" => $currentDetail->reject,
                            "sisa_kain" => ($currentDetail->sisa_kain ? $currentDetail->sisa_kain : 0),
                            "pemakaian_lembar" => $currentDetail->pemakaian_lembar,
                            "total_pemakaian_roll" => $currentDetail->total_pemakaian_roll,
                            "short_roll" => $currentDetail->short_roll,
                            "piping" => $currentDetail->piping,
                            "status" => $currentDetail->status,
                            "metode" => $currentDetail->metode,
                            "group_stocker" => $currentDetail->group_stocker,
                            "created_at" => $currentDetail->created_at,
                            "updated_at" => $currentDetail->updated_at,
                            "deleted_by" => "REDUNDANT",
                            "deleted_at" => Carbon::now(),
                        ]);

                        // Delete the redundant
                        Log::channel("deleteRedundantRollUsage")->info($currentDetail);
                        $currentDetail->delete();
                    }
                }
            }
        }

        return true;
    }

    public function deleteRedundantRoll($idRoll) {
        $currentDetails = FormCutInputDetail::selectRaw("
                form_cut_id,
                id_roll,
                qty,
                status,
                COUNT(id) as total
            ")->
            where("id_roll", $idRoll)->
            groupBy("form_cut_id", "id_roll", "qty", "status")->
            having("total", ">", 1)->
            get();

        $deletedRoll = [];
        if ($currentDetails && $currentDetails->count() > 0) {
            foreach ($currentDetails as $currentDetail) {
                $formDetails = FormCutInputDetail::where("form_cut_id", $currentDetail->form_cut_id)->where("id_roll", $currentDetail->id_roll)->where("qty", $currentDetail->qty)->where("status", $currentDetail->status)->get();

                $exceptionId = $formDetails->last() ? $formDetails->last()->id : null;
                foreach ($formDetails as $formDetail) {
                    if ($exceptionId && $formDetail->id != $exceptionId) {
                        // Delete history
                        DB::table("form_cut_input_detail_delete")->insert([
                            "form_cut_id" => $formDetail->form_cut_id,
                            "no_form_cut_input" => $formDetail->no_form_cut_input,
                            "id_roll" => $formDetail->id_roll,
                            "id_item" => $formDetail->id_item,
                            "color_act" => $formDetail->color_act,
                            "detail_item" => $formDetail->detail_item,
                            "group_roll" => $formDetail->group_roll,
                            "lot" => $formDetail->lot,
                            "roll" => $formDetail->roll,
                            "qty" => $formDetail->qty,
                            "unit" => $formDetail->unit,
                            "sisa_gelaran" => $formDetail->sisa_gelaran,
                            "sambungan" => $formDetail->sambungan,
                            "sambungan_roll" => $formDetail->sambungan_roll,
                            "est_amparan" => $formDetail->est_amparan,
                            "lembar_gelaran" => $formDetail->lembar_gelaran,
                            "average_time" => $formDetail->average_time,
                            "kepala_kain" => $formDetail->kepala_kain,
                            "sisa_tidak_bisa" => $formDetail->sisa_tidak_bisa,
                            "reject" => $formDetail->reject,
                            "sisa_kain" => ($formDetail->sisa_kain ? $formDetail->sisa_kain : 0),
                            "pemakaian_lembar" => $formDetail->pemakaian_lembar,
                            "total_pemakaian_roll" => $formDetail->total_pemakaian_roll,
                            "short_roll" => $formDetail->short_roll,
                            "piping" => $formDetail->piping,
                            "status" => $formDetail->status,
                            "metode" => $formDetail->metode,
                            "group_stocker" => $formDetail->group_stocker,
                            "created_at" => $formDetail->created_at,
                            "updated_at" => $formDetail->updated_at,
                            "deleted_by" => "REDUNDANT",
                            "deleted_at" => Carbon::now(),
                        ]);

                        // Delete the redundant
                        array_push($deletedRoll, $formDetail);
                        Log::channel("deleteRedundantRollUsage")->info($formDetail);
                        $formDetail->delete();
                    }
                }
            }
        }

        return array(
            "status" => 200,
            "message" => "Deleted ".count($deletedRoll)." Rows."
        );
    }

    public function fixRollQty($idRoll, $qty = null) {
        $rollId = $request->id_roll;
        $rollQty = $request->qty;
        $rollUse = null;

        // When there are no input
        if (!$rollQty) {

            // Check Last Input
            $lastInput = FormCutInputDetail::selectRaw("
                SUM(total_pemakaian_roll) total_pakai,
                MIN( CASE WHEN form_cut_input_detail.STATUS = 'extension' OR form_cut_input_detail.STATUS = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ) as sisa_kain
            ")->
            where("id_roll", $request->id_roll)->
            groupBy("id_roll")->
            first();

            if ($lastInput) {

                // Set Qty based on Last Input
                $rollQty = $lastInput->sisa_kain;
                $rollUse = $lastInput->total_pakai;
            } else {

                // Check Origin
                $newItem = DB::connection("mysql_sb")->select("
                    SELECT
                        id_roll,
                        id_jo,
                        detail_item,
                        detail_item_color,
                        detail_item_size,
                        id_item,
                        lot,
                        roll,
                        roll_buyer,
                        qty_stok,
                        SUM(qty)-COALESCE(qty_ri, 0) as qty,
                        unit,
                        rule_bom,
                        so_det_list,
                        size_list
                    FROM (
                        SELECT
                            whs_bppb_det.id_roll,
                            whs_bppb_det.id_jo,
                            masteritem.itemdesc detail_item,
                            masteritem.color detail_item_color,
                            masteritem.size detail_item_size,
                            whs_bppb_det.id_item,
                            whs_bppb_det.no_lot lot,
                            whs_bppb_det.no_roll roll,
                            whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                            whs_bppb_det.qty_stok,
                            whs_bppb_det.qty_out qty,
                            whs_bppb_det.satuan unit,
                            bji.rule_bom,
                            GROUP_CONCAT(DISTINCT so_det.id ORDER BY so_det.id ASC SEPARATOR ', ') as so_det_list,
                            GROUP_CONCAT(DISTINCT so_det.size ORDER BY so_det.id ASC SEPARATOR ', ') as size_list
                        FROM
                            whs_bppb_det
                            LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                            LEFT JOIN (SELECT no_barcode, id_item, no_roll_buyer FROM whs_lokasi_inmaterial where no_barcode = '".$request->id_roll."' GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                            LEFT JOIN masteritem ON masteritem.id_item = whs_lokasi_inmaterial.id_item
                            LEFT JOIN bom_jo_item bji ON bji.id_item = masteritem.id_gen
                            LEFT JOIN so_det ON so_det.id = bji.id_so_det
                            LEFT JOIN so ON so.id = so_det.id_so
                            LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        WHERE
                            whs_bppb_det.id_roll = '".$request->id_roll."'
                            AND whs_bppb_h.tujuan = 'Production - Cutting'
                            AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                            AND whs_bppb_det.no_bppb LIKE '%GK/OUT%'
                        GROUP BY
                            whs_bppb_det.id
                    ) item
                    LEFT JOIN (select a.no_barcode, (CASE WHEN supplier_in.no_barcode IS NULL THEN 0 ELSE sum(qty_aktual) END) qty_ri from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok LEFT JOIN (select b.no_barcode from whs_inmaterial_fabric a left join whs_lokasi_inmaterial b on b.no_dok = a.no_dok where b.no_barcode = '".$request->id_roll."' and supplier != 'Production - Cutting' and b.status = 'Y' GROUP BY no_barcode) supplier_in on supplier_in.no_barcode = a.no_barcode where a.no_barcode = '".$request->id_roll."' and supplier = 'Production - Cutting' and a.status = 'Y' GROUP BY no_barcode) as ri on ri.no_barcode = item.id_roll
                    GROUP BY
                        id_roll
                    LIMIT 1
                ");

                // Set Qty based on Origin Source
                $rollQty = $newItem && $newItem[0] ? ($newItem[0]->unit == 'YARD' || $newItem[0]->unit == 'YRD' ? round(($newItem[0]->qty * 0.9144), 2) : $newItem[0]->qty) : null;
                $rollUse = 0;
            }
        }

        // Roll Filter Query
        $additionalQuery = "";
        if ($rollId) {
            $additionalQuery = "WHERE scanned_item.id_roll = '".$rollId."'";
        } else {
            $additionalQuery = "WHERE scanned_item.qty != sub.sisa_kain";
        }

        // Roll Query
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

            // Single Item
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
            }

            // Multi Item
            else {
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

    public function fixMultipleRollQty($idRolls) {
        $rollSuccessArr = [];
        $rollFailedArr = [];
        for ($i = 0; $i < count($idRolls); $i++) {
            $idRoll = $idRolls[$i];
            $rollId = $idRoll;
            $rollQty = null;
            $rollUse = null;

            $lastInput = FormCutInputDetail::selectRaw("
                SUM(total_pemakaian_roll) total_pakai,
                MIN( CASE WHEN form_cut_input_detail.STATUS = 'extension' OR form_cut_input_detail.STATUS = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ) as sisa_kain
            ")->
            where("id_roll", $idRoll)->
            where("status")->
            groupBy("id_roll")->
            first();

            if ($lastInput) {
                $rollQty = $lastInput->sisa_kain;
                $rollUse = $lastInput->total_pakai;
            } else {
                $newItem = DB::connection("mysql_sb")->select("
                    SELECT
                        id_roll,
                        id_jo,
                        detail_item,
                        detail_item_color,
                        detail_item_size,
                        id_item,
                        lot,
                        roll,
                        roll_buyer,
                        qty_stok,
                        SUM(qty)-COALESCE(qty_ri, 0) as qty,
                        unit,
                        rule_bom,
                        so_det_list,
                        size_list
                    FROM (
                        SELECT
                            whs_bppb_det.id_roll,
                            whs_bppb_det.id_jo,
                            masteritem.itemdesc detail_item,
                            masteritem.color detail_item_color,
                            masteritem.size detail_item_size,
                            whs_bppb_det.id_item,
                            whs_bppb_det.no_lot lot,
                            whs_bppb_det.no_roll roll,
                            whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                            whs_bppb_det.qty_stok,
                            whs_bppb_det.qty_out qty,
                            whs_bppb_det.satuan unit,
                            bji.rule_bom,
                            GROUP_CONCAT(DISTINCT so_det.id ORDER BY so_det.id ASC SEPARATOR ', ') as so_det_list,
                            GROUP_CONCAT(DISTINCT so_det.size ORDER BY so_det.id ASC SEPARATOR ', ') as size_list
                        FROM
                            whs_bppb_det
                            LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                            LEFT JOIN (SELECT no_barcode, id_item, no_roll_buyer FROM whs_lokasi_inmaterial where no_barcode = '".$idRoll."' GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                            LEFT JOIN masteritem ON masteritem.id_item = whs_lokasi_inmaterial.id_item
                            LEFT JOIN bom_jo_item bji ON bji.id_item = masteritem.id_gen
                            LEFT JOIN so_det ON so_det.id = bji.id_so_det
                            LEFT JOIN so ON so.id = so_det.id_so
                            LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        WHERE
                            whs_bppb_det.id_roll = '".$idRoll."'
                            AND whs_bppb_h.tujuan = 'Production - Cutting'
                            AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                            AND whs_bppb_det.no_bppb LIKE '%GK/OUT%'
                        GROUP BY
                            whs_bppb_det.id
                    ) item
                    LEFT JOIN (select a.no_barcode, (CASE WHEN supplier_in.no_barcode IS NULL THEN 0 ELSE sum(qty_aktual) END) qty_ri from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok LEFT JOIN (select b.no_barcode from whs_inmaterial_fabric a left join whs_lokasi_inmaterial b on b.no_dok = a.no_dok where b.no_barcode = '".$idRoll."' and supplier != 'Production - Cutting' and b.status = 'Y' GROUP BY no_barcode) supplier_in on supplier_in.no_barcode = a.no_barcode where a.no_barcode = '".$idRoll."' and supplier = 'Production - Cutting' and a.status = 'Y' GROUP BY no_barcode) as ri on ri.no_barcode = item.id_roll
                    GROUP BY
                        id_roll
                    LIMIT 1
                ");

                $rollQty = $newItem && $newItem[0] ? ($newItem[0]->unit == 'YARD' || $newItem[0]->unit == 'YRD' ? round(($newItem[0]->qty * 0.9144), 2) : $newItem[0]->qty) : null;
                $rollUse = 0;
            }

            $additionalQuery = "WHERE scanned_item.id_roll = '".$rollId."'";

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

                    array_push($rollSuccessArr, $scannedItem->id_roll);

                    continue;
                }
            }

            array_push($rollFailedArr, $rollId);
        }

        return array(
            "status" => 200,
            "message" => count($rollSuccessArr)." berhasil diupdate <br> ".count($rollFailedArr)." gagal diupdate <br>",
            "success" => $rollSuccessArr,
            "failed" => $rollFailedArr
        );
    }
}
