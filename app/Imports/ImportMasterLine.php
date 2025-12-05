<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\MasterLine;
use App\Models\SignalBit\UserLine;
use App\Models\Hris\MasterEmployee;
use App\Models\SignalBit\EmployeeLineTmp;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use DB;

class ImportMasterLine implements ToCollection, WithStartRow
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
            // Master Plan
            $tanggal = $row[0];
            $sewing_line = $row[1];
            $kpno = $row[2];
            $color = $row[3];
            $smv = $row[4];
            $jam_kerja = $row[5];
            $jam_kerja_awal = $row[6];
            $man_power = $row[7];
            $plan_target = $row[8];
            $target_effy = $row[9];

            // Master Line
            $chief_id = $row[10];
            $chief_name = $row[11];
            $leader_id = $row[12];
            $leader_name = $row[13];
            $ie_id = $row[14];
            $ie_name = $row[15];
            $leaderqc_id = $row[16];
            $leaderqc_name = $row[17];
            $mechanic_id = $row[18];
            $mechanic_name = $row[19];
            $technical_id = $row[20];
            $technical_name = $row[21];

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
                    act_costing.kpno = '".$kpno."' and
                    so_det.color LIKE '%".$color."%'
                GROUP BY
                    act_costing.id
                LIMIT 1
            ");

            if ($orderInfo && $orderInfo[0]) {
                // Date
                $convertedDate = Date::excelToDateTimeObject($tanggal);
                $formattedDate = Carbon::instance($convertedDate)->format('Y-m-d');

                // Time
                $convertedTime = Date::excelToDateTimeObject($jam_kerja_awal);
                $formattedTime = Carbon::instance($convertedTime)->format('H:i:s');

                $storeMasterPlan = EmployeeLineTmp::create([
                    "tanggal" => $formattedDate,
                    "sewing_line" => $sewing_line,
                    "id_ws" => $orderInfo[0]->id_cost,
                    "ws" => $orderInfo[0]->ws,
                    "color" => $orderInfo[0]->color,
                    "smv" => $smv,
                    "jam_kerja" => $jam_kerja,
                    "jam_kerja_awal" => $formattedTime,
                    "man_power" => $man_power,
                    "plan_target" => $plan_target,
                    "target_effy" => $target_effy,
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username,
                ]);

                if ($storeMasterPlan) {
                    $chief = MasterEmployee::where("enroll_id", $chief_id)->first() ?? MasterEmployee::where("employee_name", "LIKE", "%".$chief_name."%")->first();
                    $leader = MasterEmployee::where("enroll_id", $leader_id)->first() ?? MasterEmployee::where("employee_name", "LIKE", "%".$leader_name."%")->first();
                    $ie = MasterEmployee::where("enroll_id", $ie_id)->first() ?? MasterEmployee::where("employee_name", "LIKE", "%".$ie_name."%")->first();
                    $leaderqc = MasterEmployee::where("enroll_id", $leaderqc_id)->first() ?? MasterEmployee::where("employee_name", "LIKE", "%".$leaderqc_name."%")->first();
                    $mechanic = MasterEmployee::where("enroll_id", $mechanic_id)->first() ?? MasterEmployee::where("employee_name", "LIKE", "%".$mechanic_name."%")->first();
                    $technical = MasterEmployee::where("enroll_id", $technical_id)->first() ?? MasterEmployee::where("employee_name", "LIKE", "%".$technical_name."%")->first();

                    $updateEmployeeLine = EmployeeLineTmp::where("id", $storeMasterPlan->id)->update([
                        "chief_id" => $chief ? $chief->enroll_id : null,
                        "chief_nik" => $chief ? $chief->nik : null,
                        "chief_name" => $chief ? $chief->employee_name : null,
                        "leader_id" => $leader ? $leader->enroll_id : null,
                        "leader_nik" => $leader ? $leader->nik : null,
                        "leader_name" => $leader ? $leader->employee_name : null,
                        "ie_id" => $ie ? $ie->enroll_id : null,
                        "ie_nik" => $ie ? $ie->nik : null,
                        "ie_name" => $ie ? $ie->employee_name : null,
                        "leaderqc_id" => $leaderqc ? $leaderqc->enroll_id : null,
                        "leaderqc_nik" => $leaderqc ? $leaderqc->nik : null,
                        "leaderqc_name" => $leaderqc ? $leaderqc->employee_name : null,
                        "mechanic_id" => $mechanic ? $mechanic->enroll_id : null,
                        "mechanic_nik" => $mechanic ? $mechanic->nik : null,
                        "mechanic_name" => $mechanic ? $mechanic->employee_name : null,
                        "technical_id" => $technical ? $technical->enroll_id : null,
                        "technical_nik" => $technical ? $technical->nik : null,
                        "technical_name" => $technical ? $technical->employee_name : null,
                    ]);

                    \Log::info("Import Master Line on 'store_master_Line_tmp' ROW :".$i);
                } else {
                    \Log::info("Fail Import Master Line on 'store_master_Line_tmp' ROW :".$i);
                }
            } else {
                \Log::info("Fail Import Master Line on 'order_info' ROW :".$i);
            }
        }
    }
}
