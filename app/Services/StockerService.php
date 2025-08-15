<?php

namespace App\Services;

use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\FormCutReject;
use App\Models\FormCutPiece;
use App\Models\FormCutPieceDetail;
use App\Models\FormCutPieceDetailSize;
use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\Part;
use App\Models\PartDetail;
use App\Models\PartForm;
use App\Models\ModifySizeQty;
use App\Models\MonthCount;
use App\Models\YearSequence;
use App\Models\StockerAdditional;
use App\Models\StockerAdditionalDetail;
use Illuminate\Http\Request;
use DB;

class StockerService
{
    public function reorderStockerNumbering($partId)
    {
        ini_set('max_execution_time', 360000);

        $formCutInputs = collect(DB::select("
            SELECT
                marker_input.color,
                form_cut_input.id AS id_form,
                form_cut_input.no_cut,
                form_cut_input.no_form AS no_form,
                form_cut_input.waktu_selesai,
                'GENERAL' AS type
            FROM
                `form_cut_input`
                LEFT JOIN `part_form` ON `part_form`.`form_id` = `form_cut_input`.`id`
                LEFT JOIN `part` ON `part`.`id` = `part_form`.`part_id`
                LEFT JOIN `part_detail` ON `part_detail`.`part_id` = `part`.`id`
                LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                LEFT JOIN `marker_input_detail` ON `marker_input_detail`.`marker_id` = `marker_input`.`id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `marker_input_detail`.`size`
                LEFT JOIN `users` ON `users`.`id` = `form_cut_input`.`no_meja`
            WHERE
                part_form.id IS NOT NULL
                AND `part`.`id` = ".$partId."
                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_input`.`id`

            UNION

            SELECT
                form_cut_piece.color,
                form_cut_piece.id AS id_form,
                form_cut_piece.no_cut,
                form_cut_piece.no_form AS no_form,
                form_cut_piece.updated_at as waktu_selesai,
                'PIECE' AS type
            FROM
                `form_cut_piece`
                LEFT JOIN `part_form` ON `part_form`.`form_pcs_id` = `form_cut_piece`.`id`
                LEFT JOIN `part` ON `part`.`id` = `part_form`.`part_id`
                LEFT JOIN `part_detail` ON `part_detail`.`part_id` = `part`.`id`
                LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `form_cut_piece_detail_size`.`size`
            WHERE
                part_form.id IS NOT NULL
                AND `part`.`id` = ".$partId."
                AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_piece`.`id`
            ORDER BY
                `color` ASC,
                `waktu_selesai` ASC,
                `no_cut` ASC
        "));

        $rangeAwal = 0;
        $sizeRangeAkhir = collect();

        $rangeAwalAdd = 0;
        $sizeRangeAkhirAdd = collect();

        $currentColor = "";
        $currentNumber = 0;

        // Loop over all forms
        foreach ($formCutInputs as $formCut) {
            // Reset cumulative data on color switch
            if ($formCut->color != $currentColor) {
                $rangeAwal = 0;
                $sizeRangeAkhir = collect();

                $rangeAwalAdd = 0;
                $sizeRangeAkhirAdd = collect();

                $currentColor = $formCut->color;
                $currentNumber = 0;
            }

            // Type Checking
            if ($formCut->type == "PIECE") {
                // Adjust form data
                $currentNumber++;
                FormCutPiece::where("id", $formCut->id_form)->update([
                    "no_cut" => $currentNumber
                ]);

                $stockerForm = Stocker::where("form_piece_id", $formCut->id_form)->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPart = $stockerForm->first() ? $stockerForm->first()->part_detail_id : "";
                $currentStockerSize = "";

                foreach ($stockerForm as $key => $stocker) {
                    $lembarGelaran = FormCutPieceDetailSize::selectRaw("form_cut_piece_detail_size.*")->leftJoin("form_cut_piece_detail", "form_cut_piece_detail.id", "=", "form_cut_piece_detail_size.form_detail_id")->where("form_id", $formCut->id_form)->where("so_det_id", $stocker->so_det_id)->sum("form_cut_piece_detail_size.qty");

                    if (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($currentStockerSize != $stocker->so_det_id)) {
                        $rangeAwal = $sizeRangeAkhir[$stocker->so_det_id] + 1;
                        $sizeRangeAkhir[$stocker->so_det_id] = ($sizeRangeAkhir[$stocker->so_det_id] + $lembarGelaran);

                        $currentStockerSize = $stocker->so_det_id;
                    } else if (!isset($sizeRangeAkhir[$stocker->so_det_id])) {
                        $rangeAwal =  1;
                        $sizeRangeAkhir->put($stocker->so_det_id, $lembarGelaran);
                    }

                    $stocker->so_det_id && (($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1)) != $stocker->qty || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1))) : $stocker->qty_ply_mod = 0;
                    $stocker->range_awal = $rangeAwal;
                    $stocker->range_akhir = $stocker->so_det_id ? $sizeRangeAkhir[$stocker->so_det_id] : 0;
                    $stocker->save();

                    if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) {
                        $stocker->cancel = "y";
                        $stocker->save();
                    }
                }
            } else {
                $modifySizeQty = ModifySizeQty::selectRaw("modify_size_qty.*, master_sb_ws.size, master_sb_ws.dest ")->leftJoin("master_sb_ws","master_sb_ws.id_so_det", "=", "modify_size_qty.so_det_id")->where("form_cut_id", $formCut->id_form)->get();

                // Adjust form data
                $currentNumber++;
                FormCutInput::where("id", $formCut->id_form)->update([
                    "no_cut" => $currentNumber
                ]);

                // Adjust form cut detail data
                $formCutInputDetails = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->orderBy("id", "asc")->get();

                $currentGroup = "";
                $currentGroupNumber = 0;
                foreach ($formCutInputDetails as $formCutInputDetail) {
                    if ($currentGroup != $formCutInputDetail->group_roll) {
                        $currentGroup = $formCutInputDetail->group_roll;
                        $currentGroupNumber += 1;
                    }

                    $formCutInputDetail->group_stocker = $currentGroupNumber;
                    $formCutInputDetail->save();
                }

                // Adjust stocker data
                $stockerForm = Stocker::where("form_cut_id", $formCut->id_form)->whereRaw("(`notes` IS NULL OR `notes` NOT LIKE '%ADDITIONAL%')")->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPart = $stockerForm->first() ? $stockerForm->first()->part_detail_id : "";
                $currentStockerSize = "";
                $currentStockerGroup = "initial";
                $currentStockerRatio = 0;

                foreach ($stockerForm as $key => $stocker) {
                    $lembarGelaran = 1;
                    if ($stocker->group_stocker) {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_stocker', $stocker->group_stocker)->sum('lembar_gelaran');
                    } else {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_roll', $stocker->shade)->sum('lembar_gelaran');
                    }

                    if ($currentStockerPart == $stocker->part_detail_id) {
                        if ($stockerForm->min("group_stocker") == $stocker->group_stocker && $stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {
                            $modifyThis = $modifySizeQty->where("so_det_id", $stocker->so_det_id)->first();

                            if ($modifyThis) {
                                $lembarGelaran = ($stocker->qty_ply < 1 ? 0 : $lembarGelaran) + $modifyThis->difference_qty;
                            }
                        }

                        if (isset($sizeRangeAkhir[$stocker->so_det_id]) && ($currentStockerSize != $stocker->so_det_id || $currentStockerGroup != $stocker->group_stocker || $currentStockerRatio != $stocker->ratio)) {
                            $rangeAwal = $sizeRangeAkhir[$stocker->so_det_id] + 1;
                            $sizeRangeAkhir[$stocker->so_det_id] = ($sizeRangeAkhir[$stocker->so_det_id] + $lembarGelaran);

                            $currentStockerSize = $stocker->so_det_id;
                            $currentStockerGroup = $stocker->group_stocker;
                            $currentStockerRatio = $stocker->ratio;
                        } else if (!isset($sizeRangeAkhir[$stocker->so_det_id])) {
                            $rangeAwal =  1;
                            $sizeRangeAkhir->put($stocker->so_det_id, $lembarGelaran);
                        }
                    }

                    $stocker->so_det_id && (($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1)) != $stocker->qty || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhir[$stocker->so_det_id] - ($rangeAwal-1))) : $stocker->qty_ply_mod = 0;
                    $stocker->range_awal = $rangeAwal;
                    $stocker->range_akhir = $stocker->so_det_id ? $sizeRangeAkhir[$stocker->so_det_id] : 0;
                    $stocker->save();

                    if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) {
                        $stocker->cancel = "y";
                        $stocker->save();
                    }
                }

                // Stocker Additional
                $stockerFormAdd = Stocker::selectRaw("stocker_input.*, master_sb_ws.dest")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->where("form_cut_id", $formCut->id_form)->where("notes", "ADDITIONAL")->orderBy("group_stocker", "desc")->orderBy("size", "asc")->orderBy("so_det_id", "asc")->orderBy("ratio", "asc")->orderBy("part_detail_id", "asc")->get();

                $currentStockerPartAdd = $stockerFormAdd->first() ? $stockerFormAdd->first()->part_detail_id : "";
                $currentStockerSizeAdd = "";
                $currentStockerGroupAdd = "initial";
                $currentStockerRatioAdd = 0;

                foreach ($stockerFormAdd as $key => $stocker) {
                    $lembarGelaran = 1;
                    if ($stocker->group_stocker) {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_stocker', $stocker->group_stocker)->sum('lembar_gelaran');
                    } else {
                        $lembarGelaran = FormCutInputDetail::where("form_cut_id", $formCut->id_form)->where("no_form_cut_input", $formCut->no_form)->where('group_roll', $stocker->shade)->sum('lembar_gelaran');
                    }

                    if ($currentStockerPartAdd == $stocker->part_detail_id) {
                        if ($stockerForm->min("group_stocker") == $stocker->group_stocker && $stockerForm->filter(function ($item) use ($stocker) { return $item->size == $stocker->size; })->max("ratio") == $stocker->ratio) {
                            $modifyThis = $modifySizeQty->where("size", $stocker->size)->where("dest", $stocker->dest)->first();

                            if ($modifyThis) {
                                $lembarGelaran = ($stocker->qty_ply < 1 ? 0 : $lembarGelaran) + $modifyThis->difference_qty;
                            }
                        }

                        if (isset($sizeRangeAkhirAdd[$stocker->so_det_id]) && ($currentStockerSizeAdd != $stocker->so_det_id || $currentStockerGroupAdd != $stocker->group_stocker || $currentStockerRatioAdd != $stocker->ratio)) {
                            $rangeAwalAdd = $sizeRangeAkhirAdd[$stocker->so_det_id] + 1;
                            $sizeRangeAkhirAdd[$stocker->so_det_id] = ($sizeRangeAkhirAdd[$stocker->so_det_id] + $lembarGelaran);

                            $currentStockerSizeAdd = $stocker->so_det_id;
                            $currentStockerGroupAdd = $stocker->group_stocker;
                            $currentStockerRatioAdd = $stocker->ratio;
                        } else if (!isset($sizeRangeAkhirAdd[$stocker->so_det_id])) {
                            $rangeAwalAdd =  1;
                            $sizeRangeAkhirAdd->put($stocker->so_det_id, $lembarGelaran);
                        }
                    }

                    $stocker->so_det_id && (($sizeRangeAkhirAdd[$stocker->so_det_id] - ($rangeAwalAdd-1)) != $stocker->qty || $stocker->qty_ply < 1) ? ($stocker->qty_ply_mod = ($sizeRangeAkhirAdd[$stocker->so_det_id] - ($rangeAwalAdd-1))) : $stocker->qty_ply_mod = 0;
                    $stocker->range_awal = $rangeAwalAdd;
                    $stocker->range_akhir = $stocker->so_det_id ? $sizeRangeAkhirAdd[$stocker->so_det_id] : 0;
                    $stocker->save();

                    if ($stocker->qty_ply < 1 && $stocker->qty_ply_mod < 1) {
                        $stocker->cancel = "y";
                        $stocker->save();
                    }
                }

                // Adjust numbering data
                $numbers = StockerDetail::selectRaw("
                        form_cut_id,
                        act_costing_ws,
                        color,
                        panel,
                        so_det_id,
                        size,
                        no_cut_size,
                        MAX(number) number
                    ")->
                    where("form_cut_id", $formCut->id_form)->
                    whereRaw("(cancel is null OR cancel = 'N')")->
                    groupBy("form_cut_id", "size")->
                    get();

                // Numbering Data
                foreach ($numbers as $number) {
                    if (isset($sizeRangeAkhir[$number->so_det_id])) {
                        if ($number->number > $sizeRangeAkhir[$number->so_det_id]) {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("so_det_id", $number->so_det_id)->
                                where("number", ">", $sizeRangeAkhir[$number->so_det_id])->
                                update([
                                    "cancel" => "Y"
                                ]);
                        } else {
                            StockerDetail::where("form_cut_id", $number->form_cut_id)->
                                where("so_det_id", $number->so_det_id)->
                                where("number", "<=", $sizeRangeAkhir[$number->so_det_id])->
                                where("cancel", "Y")->
                                update([
                                    "cancel" => "N"
                                ]);
                        }

                        if ($number->number < $sizeRangeAkhir[$number->so_det_id]) {
                            $stockerDetailCount = StockerDetail::select("kode")->orderBy("id", "desc")->first() ? str_replace("WIP-", "", StockerDetail::select("kode")->orderBy("id", "desc")->first()->kode) + 1 : 1;
                            $noCutSize = substr($number->no_cut_size, 0, strlen($number->size)+2);

                            $no = 0;
                            for ($i = $number->number; $i < $sizeRangeAkhir[$number->so_det_id]; $i++) {
                                StockerDetail::create([
                                    "kode" => "WIP-".($stockerDetailCount+$no),
                                    "form_cut_id" => $number->form_cut_id,
                                    "act_costing_ws" => $number->act_costing_ws,
                                    "color" => $number->color,
                                    "panel" => $number->panel,
                                    "so_det_id" => $number->so_det_id,
                                    "size" => $number->size,
                                    "no_cut_size" => $noCutSize. sprintf('%04s', ($i+1)),
                                    "number" => $i+1
                                ]);

                                $no++;
                            }
                        }
                    }
                }
            }
        }

        return $sizeRangeAkhir;
    }
}
