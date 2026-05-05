<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\MasterPart;
use App\Models\Part\MasterSecondary;
use App\Models\Stocker\Stocker;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\LoadingLinePlan;
use App\Models\Dc\LoadingLine;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\CutPlan;
use App\Models\Cutting\MutasiCuttingPcsSaldoDetail;
use App\Models\Cutting\MutasiCuttingPcsSaldoDetailTmp;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\StockerManual;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use DB;
use DateTime;
use Exception;

class importSaldoAwalCuttingDetail implements ToCollection, WithStartRow
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

            // Date
            $rawValue = $row[0];
            $convertedDate = null;

            if (is_numeric($rawValue)) {
                // Treat as Excel serial date
                $convertedDate = Date::excelToDateTimeObject($rawValue);
            } else {
                // Try parsing as a normal date string
                $formats = ['d/m/Y', 'Y/m/d', 'Y-m-d', 'd-m-Y']; // add more if needed
                foreach ($formats as $format) {
                    $dt = DateTime::createFromFormat($format, $rawValue);
                    if ($dt !== false) {
                        $convertedDate = $dt;
                        break;
                    }
                }

                // fallback if none of the formats matched
                if ($convertedDate === null) {
                    try {
                        $convertedDate = new DateTime($rawValue); // let PHP guess
                    } catch (Exception $e) {
                        // handle invalid date
                        echo "Could not parse date: $rawValue";
                    }
                }
            }

            // Format with Carbon
            $formattedDate = Carbon::instance($convertedDate)->format('Y-m-d');
            $formattedDateTime = Carbon::instance($convertedDate)->format('Y-m-d H:i:s');

            // Check Id SO Detail
            $idSoDet = null;
            if ($row[1]) {
                $idSoDet = $row[1];
            } else {
                // Take Order Info
                $orderInfo = DB::connection("mysql_sb")->select("
                    SELECT
                        mastersupplier.Supplier as buyer,
                        act_costing.id as id_cost,
                        act_costing.kpno as ws,
                        act_costing.styleno as style,
                        so_det.color,
                        so_det.size,
                        so_det.id
                    FROM
                        so_det
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                    WHERE
                        act_costing.kpno = '".$row[2]."' and
                        so_det.color = '".$row[5]."' and
                        so_det.size = '".$row[6]."'
                    LIMIT 1
                ");

                if ($orderInfo && isset($orderInfo[0])) {
                    $idSoDet = $orderInfo[0]->id;
                }
            }

            // Part
            $partId = null;
            if ($row[8]) {
                $partId = $row[8];
            } else {
                $partDetail = PartDetail::selectRaw("
                        part_detail.id
                    ")->
                    leftJoin("part", "part.id", "=", "part_detail.part_id")->
                    leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                    where("part.act_costing_ws", $row[2])->
                    where("part.panel", $row[7])->
                    where("master_part.nama_part", "LIKE", "%".$row[9]."%")->
                    first();

                if ($partDetail) {
                    $partId = $partDetail->id;
                }
            }

            // Create Temporary Injection
            MutasiCuttingPcsSaldoDetailTmp::create([
                "tgl_trans" => $formattedDate,
                "id_so_det" => $idSoDet,
                "panel" => $row[7],
                "part_detail_id" => $partId,
                "saldo" => $row[10],
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username,
            ]);
        }
    }
}
