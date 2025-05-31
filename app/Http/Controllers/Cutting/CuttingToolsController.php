<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\ScannedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class CuttingToolsController extends Controller
{
    public function index() {
        return view('cutting.tools.tools', [
            "page" => "dashboard-cutting"
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

        if ($scannedItem && $scannedItem->buyer) {
            return json_encode($scannedItem);
        }

        return  null;
    }

    public function fixRollQty(Request $request) {
        $rollId = $request->id_roll;
        $rollQty = $request->qty;

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
}
