<?php

namespace App\Services;

use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\SecondaryInhouseIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\LoadingLine;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailLap;
use App\Models\Cutting\FormCutReject;
use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\Cutting\FormCutPieceDetailSize;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartForm;
use App\Models\Stocker\ModifySizeQty;
use App\Models\Stocker\MonthCount;
use App\Models\Stocker\YearSequence;
use App\Models\Stocker\StockerAdditional;
use App\Models\Stocker\StockerAdditionalDetail;
use App\Models\Stocker\StockerSeparate;
use App\Models\Stocker\StockerSeparateDetail;
use App\Models\Stocker\StockerReject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use PDF;

class StockerProcessRejectService
{
    function storeStockerProcessReject(Request $request, $create = false)
    {
        return DB::transaction(function () use ($request, $create) {
            return $this->saveStockerProcessReject($request, $create);
        });
    }

    protected function saveStockerProcessReject(Request $request, $create = false)
    {
        if ($request->stocker_id && count($request->stocker_id) > 0) {
            $stocker = Stocker::where("id", $request->stocker_id);

            // Check All Stocker Reject
            $stockerReject = StockerReject::where('dc_in_id', $request['dc_in_id'])->
                where('secondary_inhouse_id', $request['secondary_inhouse_id'])->
                where('secondary_in_id', $request['secondary_in_id'])->
                get();

            // Check Stocker Reject Ratio
            $stockerRejectNextRatio = $stockerReject->count()+1;

            if ($create) {

                // Check Stocker Reject's Process
                $additionalReject = 0;
                $stockerRejectProcess = null;
                if ($request['dc_in_id']) {
                    $stockerRejectProcess = DCIn::where("id", $request['dc_in_id'])->first();
                }
                if ($request['secondary_inhouse_id']) {
                    $stockerRejectProcess = SecondaryInhouse::where("id", $request['secondary_inhouse_id'])->first();
                }
                if ($request['secondary_in_id']) {
                    $stockerRejectProcess = SecondaryIn::where("id", $request['secondary_in_id'])->first();

                    $stockerRejectProcessUpdate = $stockerRejectProcess->secondaryInUpdate();
                    $additionalReject += ($stockerRejectProcessUpdate->sum("reject") + $stockerRejectProcessUpdate->sum("replace"));
                }

                if ($stockerRejectProcess) {
                    $stockerRejectQty = $stockerReject->sum("qty_reject");
                    $stockerRejectProcessQty = $stockerRejectProcess->qty_reject + $stockerRejectProcess->qty_replace + $additionalReject;

                    if ($stockerRejectQty >= $stockerRejectProcessQty) {
                        return array(
                            "status" => 400,
                            "message" => "Qty Input tidak dapat melebihi Sisa",
                        );
                    }
                }

                // create stocker reject
                $createStockerReject = StockerReject::create([
                    'dc_in_id' => $request['dc_in_id'],
                    'secondary_inhouse_id' => $request['secondary_inhouse_id'],
                    'secondary_in_id' => $request['secondary_in_id'],
                    "tanggal" => date("Y-m-d"),
                    'qty_reject' => $request['qty_input'],
                    'ratio' => $stockerRejectNextRatio,
                    'created_by' => Auth::user()->id,
                    'created_by_username' => Auth::user()->username,
                ]);
            } else {
                // updateorcreate stocker reject
                $createStockerReject = StockerReject::updateOrCreate([
                    'dc_in_id' => $request['dc_in_id'],
                    'secondary_inhouse_id' => $request['secondary_inhouse_id'],
                    'secondary_in_id' => $request['secondary_in_id'],
                ],[
                    "tanggal" => date("Y-m-d"),
                    'qty_reject' => $request['qty_input'],
                    'created_by' => Auth::user()->id,
                    'created_by_username' => Auth::user()->username,
                ]);
            }

            if ($createStockerReject) {
                // stocker
                $storeItemArr = [];
                $batch = Str::uuid();
                for ($i = 1; $i <= count($request->stocker_id); $i++) {
                    $checkStocker = Stocker::where('part_detail_id', $request['part_detail_id'][$i])->
                        where('form_cut_id', $request['form_cut_id'])->
                        where('so_det_id', $request['so_det_id'][$i])->
                        where('stocker_reject', $createStockerReject->id)->
                        first();

                    if ($checkStocker) {
                        // Update when exist
                        $checkStocker->qty_ply = $request['qty_input'];
                        $checkStocker->cancel = 'n';
                        $checkStocker->save();
                    } else {
                        $stockerCount = Stocker::lastId()+1;
                        $stockerId = "STK-" . ($stockerCount+$i);

                        // Create when does not exist (Add to Mass insert Array)
                        array_push($storeItemArr, [
                            'id_qr_stocker' => $stockerId,
                            'act_costing_ws' => $request['act_costing_ws'],
                            'part_detail_id' => $request['part_detail_id'][$i],
                            'form_cut_id' => $request['form_cut_id'],
                            'so_det_id' => $request['so_det_id'][$i],
                            'color' => $request['color'],
                            'panel' => $request['panel'],
                            'shade' => $request['shade'][$i],
                            'group_stocker' => $request['group_stocker'][$i],
                            'ratio' => $createStockerReject->ratio,
                            'size' => $request['size'][$i],
                            'qty_ply' => $request['qty_input'],
                            // Process IDs
                            'stocker_reject' => $createStockerReject->id,
                            // End of Process IDs
                            'notes' => 'Stocker Reject Process',
                            'urutan' => 1,
                            'created_by' => Auth::user()->id,
                            'created_by_username' => Auth::user()->username,
                            'batch' => $batch,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }

                // Mass Insert Stocker
                Stocker::insert($storeItemArr);

                // Get Created Stocker
                $storedStocker = Stocker::where("stocker_reject", $createStockerReject->id)->get();

                $partDetailIds = $request->input('part_detail_id', []);
                $formCutId    = $request->input('form_cut_id');
                $soDetIds      = $request->input('so_det_id', []);
                $stockerIds    = $request->input('stocker_id', []);

                $findStocker = function ($targetPartDetail, $targetFormCut, $targetSoDet) use ($partDetailIds, $formCutId, $soDetIds, $stockerIds) {
                    $key = collect($partDetailIds)->search(function ($value, $index) use ($formCutId, $soDetIds, $targetPartDetail, $targetFormCut, $targetSoDet) {
                        return (string) $value === (string) $targetPartDetail
                            && (string) ($formCutId ?? null) === (string) $targetFormCut
                            && (string) ($soDetIds[$index] ?? null) === (string) $targetSoDet;
                    });

                    return $key !== false ? ($stockerIds[$key] ?? null) : null;
                };

                if ($storedStocker->count() > 0) {
                    // Copy created Stocker's process
                    $wipOutHeaders = [];
                    foreach ($storedStocker as $stocker) {
                        // Tujuan
                        $partDetail = $stocker->partDetail;
                        $secondary = $partDetail && $partDetail->secondary ? $partDetail->secondary : null;
                        $secondaries = $partDetail && count($partDetail->secondaries) ? $partDetail->secondaries : null;
                        $currentSecondary = null;
                        if ($secondaries) {
                            $currentSecondary = $secondaries->where("urutan", 1)->first();
                            $currentSecondary = $currentSecondary ? $currentSecondary->secondary : null;
                        }
                        if (!$currentSecondary) {
                            $currentSecondary = $secondary;
                        }

                        // Similar Stocker
                        $currentSimilar = $findStocker($stocker->part_detail_id, $stocker->form_cut_id, $stocker->so_det_id);
                        $currentSimilarStocker = Stocker::where("id", $currentSimilar)->first();
                        $currentSimilarIdQrStocker = $currentSimilarStocker ? $currentSimilarStocker->id_qr_stocker : null;

                        // When DC
                        $currentDc = null;
                        if ($request['dc_in_id']) {
                            // Current DC
                            $currentDc = DCIn::where("id", $request['dc_in_id'])->first();
                            if ($currentDc) {
                                $currentStockerTujuan = ($stocker->partDetail->secondaries->first() ? $stocker->partDetail->secondaries->first()->secondary->tujuan : ($stocker->partDetail->secondary ? $stocker->partDetail->secondary->tujuan : null));
                                $currentStockerLokasi = ($stocker->partDetail->secondaries->first() ? $stocker->partDetail->secondaries->first()->secondary->proses : ($stocker->partDetail->secondary ? $stocker->partDetail->secondary->proses : null));

                                $createDc = DCIn::updateOrCreate([
                                    "id_qr_stocker" => $stocker->id_qr_stocker,
                                ],[
                                    "no_form" => $currentDc->no_form,
                                    "tujuan" => $currentStockerTujuan,
                                    "lokasi" => $currentStockerLokasi,
                                    "qty_awal" => $stocker->qty_ply,
                                    "qty_reject" => 0,
                                    "qty_replace" => 0,
                                    "tempat" => $currentDc->tempat,
                                    "tgl_trans" => date("Y-m-d"),
                                    "user" => $currentDc->user,
                                    "created_by" => Auth::user()->id,
                                    "created_by_username" => Auth::user()->username,
                                ]);
                            }
                        }

                        // When Secondary Inhouse
                        $currentSecondaryInhouse = null;
                        if ($request['secondary_inhouse_id']) {

                            // Current Secondary
                            $currentSecondaryInhouse = SecondaryInhouse::where("id", $request['secondary_inhouse_id'])->first();

                            // Secondaries Checking
                            $currentSecondary = null;
                            if ($secondaries) {
                                $currentSecondary = $secondaries->where("urutan", $currentSecondaryInhouse->urutan)->first();
                                $currentSecondary = $currentSecondary ? $currentSecondary->secondary : null;
                            } else {
                                $currentSecondary = $secondary;
                            }

                            if ($currentSecondaryInhouse && ($currentSecondary && $currentSecondary->tujuan == "SECONDARY DALAM")) {

                                // Copy proses sebelum Inhouse (qty 0)
                                $this->copyPreviousProcessTransaction($currentSimilarIdQrStocker, $stocker->id_qr_stocker, $currentSecondaryInhouse->urutan);

                                // Current Secondary Inhouse IN
                                $createSecondaryInhouseIn = SecondaryInhouseIn::updateOrCreate([
                                    "id_qr_stocker" => $stocker->id_qr_stocker,
                                    "urutan" => $currentSecondaryInhouse->urutan,
                                ],[
                                    "tgl_trans" => date("Y-m-d"),
                                    "no_form" => $currentSecondaryInhouse->no_form,
                                    "qty_in" => $stocker->qty_ply,
                                    "ket" => $currentSecondaryInhouse->ket,
                                    "user" => Auth::user()->username,
                                ]);

                                // Current Secondary Inhouse
                                $createSecondaryInhouse = SecondaryInhouse::updateOrCreate([
                                    "id_qr_stocker" => $stocker->id_qr_stocker,
                                    "urutan" => $currentSecondaryInhouse->urutan,
                                ],[
                                    "tgl_trans" => date("Y-m-d"),
                                    "no_form" => $currentSecondaryInhouse->no_form,
                                    "qty_awal" => $stocker->qty_ply,
                                    "qty_reject" => 0,
                                    "qty_replace" => 0,
                                    "qty_in" => $stocker->qty_ply,
                                    "ket" => $currentSecondaryInhouse->ket,
                                    "user" => Auth::user()->username,
                                ]);

                                // Update urutan stocker
                                $stocker->urutan = $currentSecondaryInhouse->urutan + 1;
                                $stocker->save();
                            } else {
                                // Copy DC
                                $this->copyDcInTransaction($currentSimilarIdQrStocker, $stocker->id_qr_stocker);
                            }
                        }

                        // When Secondary In
                        $currentSecondaryIn = null;
                        if ($request['secondary_in_id']) {

                            $currentSecondaryIn = SecondaryIn::where("id", $request['secondary_in_id'])->first();

                            // Secondaries Checking
                            $currentSecondary = null;
                            if ($secondaries) {
                                $currentSecondary = $secondaries->where("urutan", $currentSecondaryIn->urutan)->first();
                                $currentSecondary = $currentSecondary ? $currentSecondary->secondary : null;
                            } else {
                                $currentSecondary = $secondary;
                            }

                            if ($currentSecondaryIn && ($currentSecondary && ($currentSecondary->tujuan == "SECONDARY DALAM" || $currentSecondary->tujuan == "SECONDARY LUAR"))) {
                                // Copy proses sebelum urutan ini (qty 0)
                                $this->copyPreviousProcessTransaction($currentSimilarIdQrStocker, $stocker->id_qr_stocker, $currentSecondaryIn->urutan);

                                // Copy Inhouse urutan ini (qty 0)
                                if ($currentSecondary->tujuan == "SECONDARY DALAM") {
                                    $this->copySecondaryInhouseTransaction($currentSimilarIdQrStocker, $stocker->id_qr_stocker, (int) $currentSecondaryIn->urutan);
                                }
                                // Copy WIP OUT (qty reject)
                                else if ($currentSecondary->tujuan == "SECONDARY LUAR") {
                                    $this->copyLoadingOutTransaction($currentSimilarIdQrStocker, $stocker->id_qr_stocker, $stocker->qty_ply, $wipOutHeaders);
                                }

                                // Current Secondary In
                                $createSecondaryIn = SecondaryIn::updateOrCreate([
                                    "id_qr_stocker" => $stocker->id_qr_stocker,
                                    "urutan" => $currentSecondaryIn->urutan,
                                ],[
                                    "tgl_trans" => date("Y-m-d"),
                                    "no_form" => $currentSecondaryIn->no_form,
                                    "tujuan" => $currentSecondaryIn->tujuan,
                                    "alokasi" => $currentSecondaryIn->alokasi,
                                    "qty_awal" => $stocker->qty_ply,
                                    "qty_reject" => 0,
                                    "qty_replace" => 0,
                                    "qty_in" => $stocker->qty_ply,
                                    "ket" => $currentSecondaryIn->ket,
                                    "user" => Auth::user()->username,
                                ]);

                                // Update urutan stocker
                                $stocker->urutan = $currentSecondaryIn->urutan + 1;
                                $stocker->save();
                            } else {
                                // Copy DC
                                $this->copyDcInTransaction($currentSimilarIdQrStocker, $stocker->id_qr_stocker);
                            }
                        }

                        // Update Current DC
                        $newSecondary = $secondaries ? $secondaries->where("urutan", $stocker->urutan)->first() : null;
                        if ($newSecondary && $newSecondary->secondary) {
                            $currentDcIn = DCIn::where("id_qr_stocker", $stocker->id_qr_stocker)->first();
                            if ($currentDcIn) {
                                $currentDcIn->tujuan = $newSecondary->secondary->tujuan;
                                $currentDcIn->lokasi = $newSecondary->secondary->proses;
                                $currentDcIn->save();
                            }
                        }
                    }

                    return array(
                        "status" => 200,
                        "message" => "Data Stocker Reject berhasil disimpan",
                    );
                }
            }
        }
    }

    protected function copyPreviousProcessTransaction ($idQrStockerSource, $idQrStocker, $urutan)
    {
        // Copy DC
        $this->copyDcInTransaction($idQrStockerSource, $idQrStocker);

        // Copy proses urutan sebelumnya
        $urutan = (int) $urutan;
        if ($urutan > 1) {
            $this->copySecondaryInhouseTransaction($idQrStockerSource, $idQrStocker, $urutan - 1);
            $this->copySecondaryInTransaction($idQrStockerSource, $idQrStocker, $urutan - 1);
        }
    }

    protected function copyDcInTransaction ($idQrStockerSource, $idQrStocker)
    {
        // Current DC
        $currentDc = DCIn::where("id_qr_stocker", $idQrStockerSource)->first();
        if ($currentDc) {
            $createDc = DCIn::updateOrCreate([
                "id_qr_stocker" => $idQrStocker,
            ],[
                "no_form" => $currentDc->no_form,
                "tujuan" => $currentDc->tujuan,
                "lokasi" => $currentDc->lokasi,
                "qty_awal" => 0,
                "qty_reject" => 0,
                "qty_replace" => 0,
                "tempat" => $currentDc->tempat,
                "tgl_trans" => $currentDc->tgl_trans,
                "user" => $currentDc->user,
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username,
            ]);

            return $createDc;
        }

        return null;
    }

    protected function copySecondaryInhouseTransaction ($idQrStockerSource, $idQrStocker, $maxUrutan)
    {
        // When Secondary Inhouse (urutan null = data lama)
        $currentSecondaryInhouse = SecondaryInhouse::where("id_qr_stocker", $idQrStockerSource)->
            where(function ($query) use ($maxUrutan) {
                $query->whereNull("urutan")->orWhere("urutan", "<=", $maxUrutan);
            })->
            get();

        if ($currentSecondaryInhouse->isNotEmpty()) {
            foreach ($currentSecondaryInhouse as $secInHouse) {
                // Current Secondary Inhouse IN
                $createSecondaryInhouseIn = SecondaryInhouseIn::updateOrCreate([
                    "id_qr_stocker" => $idQrStocker,
                    "urutan" => $secInHouse->urutan,
                ],[
                    "tgl_trans" => $secInHouse->tgl_trans,
                    "no_form" => $secInHouse->no_form,
                    "qty_in" => 0,
                    "ket" => $secInHouse->ket,
                    "user" => Auth::user()->username,
                ]);

                // Current Secondary Inhouse
                $createSecondaryInhouse = SecondaryInhouse::updateOrCreate([
                    "id_qr_stocker" => $idQrStocker,
                    "urutan" => $secInHouse->urutan,
                ],[
                    "tgl_trans" => $secInHouse->tgl_trans,
                    "no_form" => $secInHouse->no_form,
                    "qty_awal" => 0,
                    "qty_reject" => 0,
                    "qty_replace" => 0,
                    "qty_in" => 0,
                    "ket" => $secInHouse->ket,
                    "user" => Auth::user()->username,
                ]);
            }

            return $currentSecondaryInhouse;
        }

        return null;
    }

    protected function copySecondaryInTransaction ($idQrStockerSource, $idQrStocker, $maxUrutan)
    {
        // When Secondary In (urutan null = data lama)
        $currentSecondaryIn = SecondaryIn::where("id_qr_stocker", $idQrStockerSource)->
            where(function ($query) use ($maxUrutan) {
                $query->whereNull("urutan")->orWhere("urutan", "<=", $maxUrutan);
            })->
            get();

        foreach ($currentSecondaryIn as $secIn) {
            SecondaryIn::updateOrCreate([
                "id_qr_stocker" => $idQrStocker,
                "urutan" => $secIn->urutan,
            ],[
                "tgl_trans" => $secIn->tgl_trans,
                "no_form" => $secIn->no_form,
                "tujuan" => $secIn->tujuan,
                "alokasi" => $secIn->alokasi,
                "qty_awal" => 0,
                "qty_reject" => 0,
                "qty_replace" => 0,
                "qty_in" => 0,
                "ket" => $secIn->ket,
                "user" => Auth::user()->username,
            ]);
        }

        return $currentSecondaryIn;
    }

    protected function copyLoadingOutTransaction ($idQrStockerSource, $idQrStocker, $qty, array &$wipOutHeaders)
    {
        // Already has Loading Out, keep its header
        $existingWipOutDet = DB::table('wip_out_det')->where("id_qr_stocker", $idQrStocker)->first();
        if ($existingWipOutDet) {
            return DB::table('wip_out_det')->where("id", $existingWipOutDet->id)->update([
                "qty" => $qty,
                "updated_at" => Carbon::now(),
            ]);
        }

        // Search Source's Loading Out (wip_out_det)
        $currentWipOutDet = DB::table('wip_out_det')->where("id_qr_stocker", $idQrStockerSource)->first();
        if (!$currentWipOutDet) {
            return null;
        }

        // One header for each source's header in a request
        if (!isset($wipOutHeaders[$currentWipOutDet->id_wip_out])) {
            $currentWipOut = DB::table('wip_out')->where("id", $currentWipOutDet->id_wip_out)->first();

            // Generate No. Form
            $date = Carbon::parse(date("Y-m-d"));
            $datePrefix = $date->format('my');
            $month = $date->format('m');
            $year = $date->format('Y');
            $getLastNumber = DB::select("
                SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
                FROM wip_out
                WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
            ", [$month, $year]);
            $lastNumber = $getLastNumber[0]->last_number ?? 0;
            $formCounter = $lastNumber + 1;
            $noForm = 'SCP/OUT/' . $datePrefix . '/' . $formCounter++;

            // Create WIP Out
            $wipOutHeaders[$currentWipOutDet->id_wip_out] = DB::table('wip_out')->insertGetId([
                'no_form'         => $noForm,
                'tgl_form'        => $date,
                'id_po'           => ($currentWipOut ? $currentWipOut->id_po : null),
                'id_supplier'     => ($currentWipOut ? $currentWipOut->id_supplier : null),
                'jns_dok'         => ($currentWipOut ? $currentWipOut->jns_dok : null),
                'jns_pengeluaran' => ($currentWipOut ? $currentWipOut->jns_pengeluaran : null),
                'ket'             => ($currentWipOut ? $currentWipOut->ket : null),
                'berat_panel'     => ($currentWipOut ? $currentWipOut->berat_panel : null),
                'berat_karung'    => ($currentWipOut ? $currentWipOut->berat_karung : null),
                'created_by'      => Auth::user()->name,
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ]);
        }

        return DB::table('wip_out_det')->insert([
            "id_qr_stocker" => $idQrStocker,
            "id_wip_out" => $wipOutHeaders[$currentWipOutDet->id_wip_out],
            "no_karung" => $currentWipOutDet->no_karung,
            "id_item" => $currentWipOutDet->id_item,
            "id_jo" => $currentWipOutDet->id_jo,
            "qty" => $qty,
            "created_by" => Auth::user()->name,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
        ]);
    }
}
