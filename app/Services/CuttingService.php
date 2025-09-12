<?php

namespace App\Services;

use App\Models\Cutting\FormCutInput;
use Illuminate\Http\Request;
use DB;

class CuttingService
{
    public function recalculateForm($formId)
    {
        ini_set('max_execution_time', 360000);

        if ($formId) {
            $formCut = FormCutInput::where("id", $formId)->first();

            if ($formCut) {

                $formCutDetails = $formCut->formCutInputDetails()->orderBy("form_cut_input_detail.id")->get();

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
                    foreach ($formCutDetails as $formCutDetail) {
                        // Sambungan Roll
                        $sambunganRoll = $formCutDetail->formCutInputDetailSambungan ? $formCutDetail->formCutInputDetailSambungan->sum("sambungan_roll") : 0;

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

                            // Reset Qty
                            $currentQty = $qty - (($sambungan * $formCutDetail->lembar_gelaran) + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping);
                            $currentIdRoll = $formCutDetail->id_roll;
                        }

                        // Save Detail
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
}
