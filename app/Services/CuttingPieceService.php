<?php

namespace App\Services;

use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\Cutting\FormCutPieceDetailSize;
use App\Models\Cutting\ScannedItem;
use App\Models\Stocker\Stocker;
use Illuminate\Http\Request;
use Illuminate\HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;
use Carbon\Carbon;

class CuttingPieceService
{
    public function checkStockerForm($id) {
        $exists = Stocker::where("form_piece_id", $id)
            ->lockForUpdate()
            ->exists();

        return !$exists;
    }

    private function validateStock($idRoll, $diffQty)
    {
        $scannedItem = ScannedItem::where("id_roll", $idRoll)
            ->lockForUpdate()
            ->first();

        if (!$scannedItem) {
            throw new \Exception("Scanned item tidak ditemukan");
        }

        if ($scannedItem->qty + $diffQty < 0) {
            throw new \Exception("Stock tidak mencukupi, transaksi dibatalkan");
        }
    }

    public function fixChainedQty($idDetail, $diffQty)
    {
        // Get Form Detail
        $formDetail = FormCutPieceDetail::where("id", $idDetail)
            ->lockForUpdate()
            ->first();

        if (!$formDetail) {
            throw new \Exception("Form detail tidak ditemukan (chain)");
        }

        // Get Similar Form Detail
        $similarDetails = FormCutPieceDetail::where("id_roll", $formDetail->id_roll)
            ->where("created_at", ">", $formDetail->created_at)
            ->lockForUpdate()
            ->get();

        // Loop over Similar Form Detail
        $lastQty = $formDetail->qty_sisa;
        foreach ($similarDetails as $detail) {

            $newQty = $detail->qty + $diffQty;

            if ($newQty < 0) {
                throw new \Exception("Chained qty menjadi negatif");
            }

            // Update Similar Form Detail
            $detail->update([
                "qty" => $newQty,
                "qty_sisa" => $newQty - $detail->qty_pemakaian,
                "edited_by" => auth()->id(),
                "edited_by_username" => auth()->user()->username,
                "edited_at" => now(),
                "edited_notes" => "Chained update diff: " . ($diffQty > 0 ? "+$diffQty" : $diffQty)
            ]);

            $lastQty = $detail->qty_sisa;
        }

        // Update scanned item
        $scannedItem = ScannedItem::where("id_roll", $formDetail->id_roll)
            ->lockForUpdate()
            ->first();

        if ($scannedItem) {
            if ($lastQty < 0) {
                throw new \Exception("Final stock negatif");
            }

            $scannedItem->update([
                "qty" => $lastQty
            ]);
        }
    }

    public function updateFormCutPiece($request)
    {
        return DB::transaction(function () use ($request) {

            // Check Form
            $form = FormCutPiece::find($request->id);
            if (!$form) {
                throw new \Exception("Form tidak ditemukan");
            }

            // Check Form Stocker
            if (!$this->checkStockerForm($form->id)) {
                throw new \Exception("Stocker sudah diprint");
            }

            // Check Form Detail
            $formDetail = FormCutPieceDetail::where("form_id", $form->id)
                ->where("id", $request->id_detail)
                ->lockForUpdate()
                ->first();

            if (!$formDetail) {
                throw new \Exception("Form detail tidak ditemukan");
            }

            // Lock all similar form detail
            FormCutPieceDetail::where("id_roll", $formDetail->id_roll)
                ->lockForUpdate()
                ->get();

            // Update form detail size
            [$qtyUsage, $updateMessage] = $this->updateDetailSizes($request, $formDetail);

            // Update form detail
            $qtyUsageBefore = $formDetail->qty_pemakaian;

            $formDetail->update([
                "qty_pemakaian" => $qtyUsage,
                "qty_sisa" => $formDetail->qty - $qtyUsage,
                "edited_by" => auth()->id(),
                "edited_by_username" => auth()->user()->username,
                "edited_at" => now(),
                "edited_notes" => "Update Qty Usage from $qtyUsageBefore to $qtyUsage"
            ]);

            // Define Diff Qty
            $diffQty = $qtyUsageBefore - $qtyUsage;

            // Check Scanned Item Stock
            $this->validateStock($formDetail->id_roll, $diffQty);

            // Update Chained Qty
            $this->fixChainedQty($formDetail->id, $diffQty);

            return "Form {$form->no_form} berhasil diubah <br>$updateMessage";
        });
    }

    private function updateDetailSizes($request, $formDetail)
    {
        $qtyUsage = 0;
        $updateMessage = "";

        // Incomplete Request
        if (count($request->so_det_id) !== count($request->qty_detail)) {
            throw new \Exception("Data tidak valid (size mismatch)");
        }

        // Loop Over Form Detail Sizes
        foreach ($request->so_det_id as $i => $soDetId) {

            if (!array_key_exists($i, $request->qty_detail)) {
                throw new \Exception("Qty detail tidak valid (index missing)");
            }

            $detailSize = FormCutPieceDetailSize::where("form_detail_id", $formDetail->id)
                ->where("so_det_id", $soDetId)
                ->lockForUpdate()
                ->first();

            if (!$detailSize) {
                throw new \Exception("Detail size tidak ditemukan");
            }

            // Define Qty Form Detail Size
            $qtyBefore = $detailSize->qty;
            $qtyAfter = $request->qty_detail[$i];

            if (!is_numeric($qtyAfter)) {
                throw new \Exception("Qty harus berupa angka");
            }

            if ($qtyAfter < 0) {
                throw new \Exception("Qty tidak boleh negatif");
            }

            // Update Qty Form Detail Size
            if ($qtyAfter != $qtyBefore) {
                $detailSize->update([
                    "qty" => $qtyAfter,
                    "edited_by" => auth()->id(),
                    "edited_by_username" => auth()->user()->username,
                    "edited_at" => now(),
                    "edited_notes" => "Update Qty $detailSize->size from $qtyBefore to $qtyAfter"
                ]);

                $updateMessage .= "<br>Update Qty $detailSize->size from $qtyBefore to $qtyAfter";
            }

            // Add to Total Qty Usage
            $qtyUsage += $qtyAfter;
        }

        return [$qtyUsage, $updateMessage];
    }

    // public function fixChainedQty($idDetail, $diffQty) {
    //     $formDetail = FormCutPieceDetail::where("id", $idDetail)->first();

    //     if ($formDetail) {
    //         // Get Similar Form Details
    //         $similarFormDetails = FormCutPieceDetail::where("id_roll", $formDetail->id_roll)->where("created_at", ">", $formDetail->created_at)->get();

    //         $notes = "";
    //         $lastQty = $formDetail->qty_sisa;
    //         foreach ($similarFormDetails as $similarFormDetail) {
    //             // Update Similar Form Detail
    //             $similarFormDetail->qty += $diffQty;
    //             $similarFormDetail->qty_sisa = $similarFormDetail->qty - $similarFormDetail->qty_pemakaian;
    //             $formDetail->edited_by = Auth::user()->id;
    //             $formDetail->edited_by_username = Auth::user()->username;
    //             $formDetail->edited_at = Carbon::now();
    //             $formDetail->edited_notes = "Update Form Cut Piece Detail Qty by Chained Qty with Diff Qty : ".($diffQty > 0 ? "+".$diffQty : $diffQty);
    //             $notes .= "<br>".$formDetail->edited_notes;
    //             $similarFormDetail->save();

    //             $lastQty = $similarFormDetail->qty_sisa;
    //         }

    //         // Update Scanned Item
    //         $scannedItem = ScannedItem::where("id_roll", $formDetail->id_roll)->first();
    //         if ($scannedItem) {
    //             $scannedItem->qty = $lastQty;
    //             $scannedItem->save();
    //         }

    //         Log::channel("formCutPieceChainedQty")->info([
    //             "UPDATE FORM CUT PIECE CHAINED QTY ROLL ".$formDetail->id_roll,
    //             $formDetail,
    //             $diffQty,
    //             $similarFormDetails,
    //             $scannedItem,
    //             $notes
    //         ]);

    //         return $formDetail;
    //     }

    //     return "Form Detail tidak ditemukan";
    // }
}
