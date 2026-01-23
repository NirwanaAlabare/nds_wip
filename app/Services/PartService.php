<?php

namespace App\Services;

use App\Models\Part\Part;
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
}
