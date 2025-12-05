<?php

namespace App\Services;

use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
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
        if ($idRoll) {
            $currentDetails = FormCutInputDetail::where("form_cut_id", $idForm)->where("id_roll", $idRoll)->where("qty", $qtyRoll)->where("status", $status)->get();

            // Set Exception
            $exceptionId = $currentDetails->last() ? $currentDetails->last()->id : null;

            if ($currentDetails->count() > 1) {
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
}
