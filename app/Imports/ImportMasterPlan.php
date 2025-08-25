<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\UserLine;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use DB;

class ImportMasterPlan implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        $i = 0;
        foreach ($rows as $row)
        {
            $i++;

            $orderInfo = DB::connection("mysql_sb")->select("
                SELECT
                    mastersupplier.Supplier as buyer,
                    act_costing.id as id_cost,
                    act_costing.kpno as ws,
                    act_costing.styleno as style,
                    so_det.color,
                    GROUP_CONCAT(so_det.size) sizes,
                    GROUP_CONCAT(so_det.id) so_det_ids
                FROM
                    so_det
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                WHERE
                    act_costing.kpno = '".$row[2]."' and
                    so_det.color LIKE '%".$row[3]."%'
                GROUP BY
                    act_costing.id
                LIMIT 1
            ");

            if ($orderInfo && $orderInfo[0]) {
                $excelDate = $row[0];
                $convertedDate = Date::excelToDateTimeObject($excelDate);
                $formattedDate = Carbon::instance($convertedDate)->format('Y-m-d');

                $storeMasterPlan = MasterPlan::create([
                    "id_plan" => str_replace("-", "", $formattedDate),
                    "sewing_line" => $row[1],
                    "tgl_plan" => $formattedDate,
                    "tgl_input" => Carbon::now(),
                    "id_ws" => $orderInfo[0]->id_cost,
                    "color" => $orderInfo[0]->color,
                    "smv" => $row[4],
                    "jam_kerja" => $row[5],
                    "man_power" => $row[6],
                    "plan_target" => $row[7],
                    "target_effy" => $row[8],
                    "create_by" => Auth::user()->username,
                    "cancel" => "N",
                ]);

                if ($storeMasterPlan) {
                    \Log::info("Import Master Plan on 'store_master_plan' ROW :".$i);
                } else {
                    \Log::info("Fail Import Master Plan on 'store_master_plan' ROW :".$i);
                }
            } else {
                \Log::info("Fail Import Master Plan on 'order_info' ROW :".$i);
            }
        }
    }
}
