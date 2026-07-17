<?php

namespace App\Services;

use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartDetailSecondary;
use App\Models\Stocker\Stocker;
use App\Models\DC\DCIn;
use App\Models\DC\SecondaryIn;
use App\Models\DC\SecondaryInhouseIn;
use App\Models\DC\SecondaryInhouse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use DB;

class PartService
{
    public function checkRemainingPanel($act_costing_id, $current_panel_id, $current_panel, $current_panel_status)
    {
        $notInclude = "";

        $mainPanel = Part::where("act_costing_id", $act_costing_id)->where("panel_status", "main")->first();

        // Check Current Panel status
        if ($current_panel_status == "main") {
            if ($mainPanel) {
                return array(
                    "status" => 400,
                    "message" => "Main Panel sudah ada"
                );
            }
        }
        // Check if there is any remaining panel
        else {
            $existParts = Part::where("act_costing_id", $act_costing_id)->get();
            if ($existParts->count() > 0) {
                $i = 0;
                $notInclude = "and nama_panel not in (";
                foreach ($existParts as $existPart) {
                    $notInclude .= ($i == 0 ? "'" . $existPart->panel . "'" : ", '" . $existPart->panel . "'");
                    $i++;
                }
                $notInclude .= ")";
            }
            $panels = DB::connection('mysql_sb')->select("
                select mp.id, nama_panel panel from
                    (select id_panel from bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join masteritem mi on k.id_item = mi.id_gen
                        where ac.id = '" . $act_costing_id . "' and k.status = 'M' and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and mi.mattype = 'F'
                        group by id_panel
                    ) a
                inner join masterpanel mp on a.id_panel = mp.id
                where nama_panel != '".$current_panel."'
                " . $notInclude . "
            ");

            if (!$mainPanel && count($panels) < 1) {
                return array(
                    "status" => 400,
                    "message" => "Harap tentukan salah satu panel sebagai Main Panel."
                );
            }
        }

        return array(
                "status" => 200,
                "message" => "Validasi."
            );
    }

    public function updateDcTransaction($partDetailID)
    {
        $actor = Auth::user() ? Auth::user()->id . " " . Auth::user()->username : "System";

        Log::channel("updateDcTransaction")->info("Starting updateDcTransaction", [
            "part_detail_id" => $partDetailID,
            "by" => $actor,
        ]);

        // Check Part Detail
        $partDetail = PartDetail::where("id", $partDetailID)->first();

        if (!$partDetail) {
            Log::channel("updateDcTransaction")->warning("Part Detail not found", [
                "part_detail_id" => $partDetailID,
            ]);
            return;
        }

        // Part Detail Secondary
        $partDetailSecondary = PartDetailSecondary::select("part_detail_secondary.part_detail_id", "master_secondary.id as master_secondary_id", "master_secondary.tujuan", "master_secondary.proses", 'part_detail_secondary.urutan')->leftJoin("master_secondary", "master_secondary.id", "=", "part_detail_secondary.master_secondary_id")->where("part_detail_id", $partDetail->id)->orderBy("urutan", "asc")->get();
        if ($partDetailSecondary->count() < 1) {
            $partDetailSecondary = PartDetail::selectRaw("part_detail.id as part_detail_id, master_secondary.id as master_secondary_id, master_secondary.tujuan, master_secondary.proses, 0 as urutan")->leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->where("id", $partDetail->id)->get();
        }

        Log::channel("updateDcTransaction")->info("Part Detail Secondary loaded", [
            "part_detail_id" => $partDetail->id,
            "secondary_count" => $partDetailSecondary->count(),
            "secondaries" => $partDetailSecondary->toArray(),
        ]);

        // Secondary Dalam dan Luar
        $withSecondaryInhouse = $partDetailSecondary->where("tujuan", "SECONDARY DALAM")->count();
        $withSecondaryIn = $partDetailSecondary->where("tujuan", "SECONDARY LUAR")->count();

        // Loop over Stockers
        $stockers = Stocker::where("part_detail_id", $partDetail->id)->get();

        Log::channel("updateDcTransaction")->info("Processing Stockers", [
            "part_detail_id" => $partDetail->id,
            "stocker_count" => $stockers->count(),
            "with_secondary_inhouse" => $withSecondaryInhouse,
            "with_secondary_in" => $withSecondaryIn,
        ]);

        foreach ($stockers as $stocker) {
            // Stocker Update
            if ($stocker->urutan > 0) {
                $currentPartDetailSecondary = $partDetailSecondary->where("urutan", $stocker->urutan+1)->first();
                if (!$currentPartDetailSecondary) {
                    $currentPartDetailSecondary = $partDetailSecondary->where("urutan", $stocker->urutan)->first();
                }
            } else {
                $currentPartDetailSecondary = $partDetailSecondary->first();
            }

            Log::channel("updateDcTransaction")->info("Updating Stocker", [
                "stocker_id" => $stocker->id,
                "id_qr_stocker" => $stocker->id_qr_stocker,
                "urutan" => $stocker->urutan,
                "tujuan_lama" => $stocker->tujuan,
                "tujuan_baru" => $currentPartDetailSecondary->tujuan ?? null,
                "tempat_lama" => $stocker->tempat,
                "tempat_baru" => $currentPartDetailSecondary->proses ?? null,
            ]);

            // Update Part Detail Secondary Tujuan
            $stocker->tujuan = $currentPartDetailSecondary->tujuan;
            $stocker->tempat = $currentPartDetailSecondary->proses;
            $stocker->save();

            // Delete Secondary Dalam
            if ($withSecondaryInhouse < 1) {
                Log::channel("updateDcTransaction")->info("Deleting Secondary Inhouse (Dalam)", [
                    "id_qr_stocker" => $stocker->id_qr_stocker,
                ]);

                // Secondary Inhouse IN
                $deleteSecondaryInhouseIn = SecondaryInhouseIn::where("id_qr_stocker", $stocker->id_qr_stocker)->delete();

                // Get Secondary Inhouse OUT
                $getSecondaryInhouse = SecondaryInhouse::where("id_qr_stocker", $stocker->id_qr_stocker)->get();

                Log::channel("updateDcTransaction")->info("Secondary Inhouse OUT found", [
                    "id_qr_stocker" => $stocker->id_qr_stocker,
                    "count" => $getSecondaryInhouse->count(),
                ]);

                // Delete Secondary Inhouse OUT & Secondary IN
                foreach ($getSecondaryInhouse as $secInhouse) {
                    // Secondary IN
                    $deleteSecondaryIn = SecondaryIn::where("id_qr_stocker", $stocker->id_qr_stocker)->where(DB::raw("IFNULL(urutan, '-')"), ($secInhouse->urutan ?? '-'))->delete();
                    $deleteSecondaryInhouse = $secInhouse->delete();

                    Log::channel("updateDcTransaction")->info("Deleted Secondary Inhouse OUT & Secondary IN", [
                        "id_qr_stocker" => $stocker->id_qr_stocker,
                        "urutan" => $secInhouse->urutan ?? null,
                    ]);
                }
            }

            // Delete Secondary Luar
            if ($withSecondaryIn < 1) {
                Log::channel("updateDcTransaction")->info("Processing Secondary Luar deletion", [
                    "id_qr_stocker" => $stocker->id_qr_stocker,
                ]);

                // Secondary IN
                $secondaryIn = SecondaryIn::where("id_qr_stocker", $stocker->id_qr_stocker)->get();

                foreach ($secondaryIn as $sec) {
                    // Check Secondary Inhouse
                    $totalSecondaryInhouse = SecondaryInhouse::where("id_qr_stocker", $stocker->id_qr_stocker)->where(DB::raw("IFNULL(urutan, '-')"), ($sec->urutan ?? '-'))->count();

                    // When it doesn't have secondary inhouse then it is secondary luar
                    if ($totalSecondaryInhouse < 1) {
                        $deleteSecondaryIn = $sec->delete();

                        Log::channel("updateDcTransaction")->info("Deleted Secondary Luar", [
                            "id_qr_stocker" => $stocker->id_qr_stocker,
                            "secondary_in_id" => $sec->id,
                        ]);
                    }
                }
            }

            // Update DC
            $dc = DCIn::where("id_qr_stocker", $stocker->id_qr_stocker)->first();

            if ($dc) {
                $firstPartDetailSecondary = $partDetailSecondary->first();

                Log::channel("updateDcTransaction")->info("Updating DC", [
                    "id_qr_stocker" => $stocker->id_qr_stocker,
                    "dc_id" => $dc->id,
                    "tujuan_lama" => $dc->tujuan,
                    "tujuan_baru" => $firstPartDetailSecondary->tujuan ?? null,
                    "lokasi_lama" => $dc->lokasi,
                    "lokasi_baru" => $firstPartDetailSecondary->proses ?? null,
                ]);

                $dc->tujuan = $firstPartDetailSecondary->tujuan;
                $dc->lokasi = $firstPartDetailSecondary->proses;
                $dc->save();
            } else {
                Log::channel("updateDcTransaction")->info("No DC found for stocker", [
                    "id_qr_stocker" => $stocker->id_qr_stocker,
                ]);
            }
        }

        Log::channel("updateDcTransaction")->info("Finished updateDcTransaction", [
            "part_detail_id" => $partDetailID,
        ]);
    }
}
