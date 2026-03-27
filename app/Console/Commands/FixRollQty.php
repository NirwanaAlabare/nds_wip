<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\ScannedItem;
use DB;

class FixRollQty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:fixrollqty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Roll Qty ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $idRoll = null;
        $qty = null;

        $rollId = $idRoll;
        $rollQty = $qty;
        $rollUse = null;

        // When there are no input
        if (!$rollQty) {

            // Check Last Input
            $lastInput = FormCutInputDetail::selectRaw("
                SUM(total_pemakaian_roll) total_pakai,
                ROUND(MIN( CASE WHEN form_cut_input_detail.STATUS = 'extension' OR form_cut_input_detail.STATUS = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ), 2) as sisa_kain
            ")->
            where("id_roll", $idRoll)->
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
                    ROUND(MIN( CASE WHEN form_cut_input_detail.STATUS = 'extension' OR form_cut_input_detail.STATUS = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ), 2) sisa_kain
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
                            ROUND(
                                MIN(
                                    CASE
                                        WHEN form_cut_input_detail.status IN ('extension', 'extension complete')
                                        THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll
                                        ELSE form_cut_input_detail.sisa_kain
                                    END
                                ),
                                2
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
