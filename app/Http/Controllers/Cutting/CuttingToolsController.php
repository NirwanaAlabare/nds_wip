<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\ScannedItem;
use App\Models\FormCutInput;
use App\Models\Marker;
use App\Models\MarkerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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

    public function getFormRatio(Request $request) {
        $noForm = $request->no_form;

        if ($noForm) {
            $form = FormCutInput::where("no_form", $noForm)->orderBy("id", "desc")->first();

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
}
