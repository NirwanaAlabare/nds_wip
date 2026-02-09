<?php

namespace App\Services;

use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryIn;
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
    function storeStockerProcessReject(Request $request)
    {
        if ($request->stocker_id && count($request->stocker_id) > 0) {
            $stocker = Stocker::where("id", $request->stocker_id);

            // stocker reject
            $createStockerReject = StockerReject::updateOrCreate([
                'dc_in_id' => $request['dc_in_id'],
                'secondary_inhouse_id' => $request['secondary_inhouse_id'],
                'secondary_in_id' => $request['secondary_in_id'],
            ],[
                "tanggal" => date("Y-m-d"),
                'qty_reject' => $request['qty_reject'],
                'created_by' => Auth::user()->id,
                'created_by_username' => Auth::user()->username,
            ]);

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
                        $checkStocker->qty_ply = $request['qty'];
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
                            'ratio' => $request['ratio'][$i],
                            'size' => $request['size'][$i],
                            'qty_ply' => $request['qty'],
                            // Process IDs
                            'stocker_reject' => $createStockerReject->id,
                            // End of Process IDs
                            'notes' => 'Stocker Reject Process',
                            'urutan' => $request['urutan'][$i],
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

                if ($storedStocker->count() > 0) {
                    // Copy created Stocker's process
                    foreach ($storedStocker as $stocker) {
                        // When DC
                        $currentDc = null;
                        if ($request['dc_in_id']) {
                            // Current DC
                            $currentDc = DCIn::where("id", $request['dc_in_id'])->first();
                            if ($currentDc) {
                                $createDc = DCIn::updateOrCreate([
                                    "id_qr_stocker" => $stocker->id_qr_stocker,
                                ],[
                                    "no_form" => $currentDc->no_form,
                                    "tujuan" => $currentDc->tujuan,
                                    "lokasi" => $currentDc->lokasi,
                                    "rak" => $currentDc->rak,
                                    "qty_awal" => 0,
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

                            $currentSecondaryInhouse = SecondaryInhouse::where("id", $request['secondary_inhouse_id'])->first();

                            if ($currentSecondaryInhouse) {
                                // Copy DC
                                $this->copyDcInTransaction($currentSecondaryInhouse->id_qr_stocker, $stocker->id_qr_stocker);

                                // Current Secondary Inhouse
                                $createSecondaryInhouse = SecondaryInhouse::updateOrCreate([
                                    "id_qr_stocker" => $stocker->id_qr_stocker,
                                    "urutan" => $currentSecondaryInhouse->urutan,
                                ],[
                                    "tgl_trans" => date("Y-m-d"),
                                    "no_form" => $currentSecondaryInhouse->no_form,
                                    "qty_awal" => $stocker->qty,
                                    "qty_reject" => 0,
                                    "qty_replace" => 0,
                                    "qty_in" => $stocker->qty,
                                    "ket" => $currentSecondaryInhouse->ket,
                                    "user" => Auth::user()->username,
                                ]);
                            }
                        }

                        // When Secondary In
                        $currentSecondaryIn = null;
                        if ($request['secondary_in_id']) {

                            $currentSecondaryIn = SecondaryIn::where("id", $request['secondary_in_id'])->first();

                            if ($currentSecondaryIn) {
                                // Copy Inhouse & DC
                                $copyInhouse = $this->copySecondaryInhouseTransaction($currentSecondaryIn->id_qr_stocker, $stocker->id_qr_stocker);
                                if (!$copyInhouse) {
                                    // Copy DC (if there is no inhouse)
                                    $copyDc = $this->copyDcInTransaction($currentSecondaryIn->id_qr_stocker, $stocker->id_qr_stocker);
                                }

                                // Current Secondary Inhouse
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

    protected function copySecondaryInhouseTransaction ($idQrStockerSource, $idQrStocker)
    {
        // When Secondary Inhouse
        $currentSecondaryInhouse = SecondaryInhouse::where("id", $idQrStockerSource)->get();

        if ($currentSecondaryInhouse) {

            // Copy DC
            $this->copyDcInTransaction($idQrStockerSource, $idQrStocker);

            foreach ($currentSecondaryInhouse as $secInHouse) {
                // Current Secondary Inhouse
                $createSecondaryInhouse = SecondaryInhouse::createOrUpdate([
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

    protected function copySecondaryInTransaction ($idQrStockerSource, $idQrStocker)
    {
        // When Secondary Inhouse
        $currentSecondaryIn = SecondaryIn::where("id", $idQrStockerSource)->first();

        if ($currentSecondaryIn) {
            // Copy Inhouse & DC
            $copyInhouse = $this->copySecondaryInhouseTransaction($idQrStockerSource, $idQrStocker);
            if (!$copyInhouse) {
                // Copy DC (if there is no inhouse)
                $copyDc = $this->copyDcInTransaction($idQrStockerSource, $idQrStocker);
            }

            foreach ($currentSecondaryIn as $secIn) {
                // Current Secondary Inhouse
                $createSecondaryIn = SecondaryIn::updateOrCreate([
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

        return null;
    }
}
