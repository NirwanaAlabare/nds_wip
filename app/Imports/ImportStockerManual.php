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
use App\Models\SignalBit\UserLine;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use DB;
use DateTime;
use Exception;

class ImportStockerManual implements ToCollection, WithStartRow
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
                    so_det.size,
                    so_det.id
                FROM
                    so_det
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                WHERE
                    act_costing.kpno = '".$row[0]."' and
                    so_det.color = '".$row[1]."' and
                    so_det.size = '".$row[2]."'
                LIMIT 1
            ");

            if ($orderInfo && $orderInfo[0]) {
                $partBagian = explode(' - ', $row[4]);
                $namaPart = trim($partBagian[0] ? $partBagian[0] : null, " \t\n\r\0\x0B");
                $namaBagian = trim($partBagian[1] ? $partBagian[1] : null, " \t\n\r\0\x0B");

                $partDetailInfo = DB::select("
                    SELECT
                        part_detail.id,
                        master_secondary.tujuan,
                        master_secondary.proses
                    FROM
                        part_detail
                        LEFT JOIN part ON part.id = part_detail.part_id
                        LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
                    WHERE
                        part.act_costing_ws = '".$row[0]."' and
                        part.panel = '".$row[3]."' and
                        master_part.nama_part = '".$namaPart."'
                    LIMIT 1
                ");

                if (!($partDetailInfo && $partDetailInfo[0])) {
                    $part = Part::where("act_costing_ws", $row[0])->
                        where("panel", $row[3])->
                        first();

                    if (!$part) {
                        $part = Part::select("kode")->orderBy("kode", "desc")->first();
                        $partNumber = $part ? intval(substr($part->kode, -5)) + 1 : 1;
                        $partCode = 'PRT' . sprintf('%05s', $partNumber);

                        $panel = DB::connection("mysql_sb")->table("masterpanel")->where("nama_panel", $row[3])->first();

                        $part = Part::create([
                            "kode" => $partCode,
                            "act_costing_id" => $orderInfo[0]->id_cost,
                            "act_costing_ws" => $orderInfo[0]->ws,
                            "color" => $orderInfo[0]->color,
                            "panel_id" => $panel ? $panel->id : null,
                            "panel" => $row[3],
                            "buyer" => $orderInfo[0]->buyer,
                            "style" => $orderInfo[0]->style,
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]);
                    }

                    $masterPart = DB::table("master_part")->where("nama_part", "LIKE", "%".$namaPart."%")->where("bag", "%".$namaBagian."%")->first();
                    if (!$masterPart) {
                        $masterPart = DB::table("master_part")->where("nama_part", "LIKE", "%".$namaPart."%")->first();
                    }
                    $masterSecondary = DB::table("master_secondary")->where("proses", "LIKE", "%".$row[5]."%")->first();

                    $partDetail = PartDetail::create([
                        "part_id" => $part->id,
                        "master_part_id" => $masterPart->id,
                        "master_secondary_id" => $masterSecondary->id,
                        "cons" => '0.01',
                        "unit" => 'METER',
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);

                    if ($partDetail) {
                        $partDetailInfo = DB::select("
                            SELECT
                                part_detail.id,
                                master_secondary.tujuan,
                                master_secondary.proses
                            FROM
                                part_detail
                                LEFT JOIN part ON part.id = part_detail.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                                LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
                            WHERE
                                part.act_costing_ws = '".$row[0]."' and
                                part.panel = '".$row[3]."' and
                                master_part.nama_part = '".$namaPart."'
                            LIMIT 1
                        ");
                    }
                }

                if ($partDetailInfo && $partDetailInfo[0]) {
                    $stockerCount = Stocker::lastId()+1;
                    $stockerId = "STK-" . ($stockerCount + $i);

                    $createStocker = Stocker::create([
                        'id_qr_stocker' => $stockerId,
                        'act_costing_ws' => $row[0],
                        'part_detail_id' => $partDetailInfo[0]->id,
                        'so_det_id' => $orderInfo[0]->id,
                        'color' => $row[1],
                        'panel' => $row[3],
                        'shade' => $stockerId,
                        'group_stocker' => $stockerId,
                        'ratio' => 1,
                        'size' => $row[2],
                        'qty_ply' => $row[7],
                        'qty_ply_mod' => null,
                        'qty_cut' => $row[7],
                        'notes' => $row[8],
                        'status' => $row[9],
                        'created_by' => Auth::user()->id,
                        'created_by_username' => Auth::user()->username
                    ]);

                    if ($createStocker) {
                        $rawValue = $row[14];
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

                        // Optionally format with Carbon
                        $formattedDate = Carbon::instance($convertedDate)->format('Y-m-d');

                        if ($row[9] > 0) {
                            DCIn::create([
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "tujuan" => $partDetailInfo[0]->tujuan,
                                "lokasi" => $partDetailInfo[0]->proses,
                                "qty_awal" => $row[7],
                                "qty_reject" => (($row[7]-$row[10]) > 0 ? ($row[7]-$row[10]) : 0),
                                "qty_replace" => (($row[7]-$row[10]) < 0 ? ($row[7]-$row[10])*(-1) : 0),
                                "tempat" => $partDetailInfo[0]->proses,
                                "tgl_trans" => $formattedDate,
                                "user" => Auth::user()->name,
                                "status" => "N"
                            ]);
                        }

                        if ($row[11] > 0) {
                            SecondaryInhouse::create([
                                "tgl_trans" => $formattedDate,
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "qty_awal" => $row[10],
                                "qty_reject" => (($row[10]-$row[11]) > 0 ? ($row[10]-$row[11]) : 0),
                                "qty_replace" => (($row[10]-$row[11]) < 0 ? ($row[10]-$row[11])*(-1) : 0),
                                "qty_in" => $row[11],
                                "user" => Auth::user()->name
                            ]);
                        }

                        if ($row[12] > 0) {
                            SecondaryIn::create([
                                "tgl_trans" => $formattedDate,
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "qty_awal" => $row[11],
                                "qty_reject" => (($row[11]-$row[12]) > 0 ? ($row[11]-$row[12]) : 0),
                                "qty_replace" => (($row[11]-$row[12]) < 0 ? ($row[11]-$row[12])*(-1) : 0),
                                "qty_in" => $row[12],
                                "user" => Auth::user()->name
                            ]);
                        }

                        if ($row[13] > 0) {
                            if ($row[15]) {

                                if (is_numeric($row[15])) {
                                    $line_id = $row[15];
                                    $line_username = "line_".(sprintf('%02d', $row[15]));
                                } else {
                                    $line = UserLine::where("Groupp", "SEWING")->whereRaw("FullName LIKE '%".$row[15]."%'")->first();
                                    $line_id = $line->line_id;
                                    $line_username = $line->username;
                                }

                                $loadingLinePlan = LoadingLinePlan::where("line_id", $line_id)->
                                    where("act_costing_id", $orderInfo[0]->id_cost)->
                                    where("color", $row[1])->
                                    where("tanggal", $formattedDate)->
                                    first();

                                $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
                                $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;
                                if ($loadingLinePlan) {
                                    LoadingLine::create([
                                        "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                        "line_id" => $line_id,
                                        "loading_plan_id" => $loadingLinePlan['id'],
                                        "nama_line" => $line_username,
                                        "stocker_id" => $createStocker->id,
                                        "qty" => $row[13],
                                        "status" => "active",
                                        "tanggal_loading" => $formattedDate,
                                        "no_bon" => $row[16],
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username,
                                    ]);
                                } else {
                                    $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                                    $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                                    $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                                    $newLoadingPlan = LoadingLinePlan::create([
                                        "line_id" => $line_id,
                                        "kode" => $kodeLoadingPlan,
                                        "act_costing_id" => $orderInfo[0]->id_cost,
                                        "act_costing_ws" => $row[0],
                                        "buyer" => $orderInfo[0]->buyer,
                                        "style" => $orderInfo[0]->style,
                                        "color" => $row[1],
                                        "tanggal" => $formattedDate
                                    ]);

                                    LoadingLine::create([
                                        "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                        "line_id" => $line_id,
                                        "loading_plan_id" => $newLoadingPlan['id'],
                                        "nama_line" => $line_username,
                                        "stocker_id" => $createStocker->id,
                                        "qty" => $row[13],
                                        "status" => "active",
                                        "tanggal_loading" => $formattedDate,
                                        "no_bon" => $row[16],
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username,
                                    ]);
                                }
                            }
                        }
                    } else {
                        \Log::info("Fail Import Stocker Manual on create stocker ROW :".$i, $createStocker);
                    }
                } else {
                    \Log::info("Fail Import Stocker Manual on part_detailing ROW :".$i, $partDetailInfo);
                }
            } else {
                \Log::info("Fail Import Stocker Manual on order_info ROW :".$i, $orderInfo);
            }
        }
    }
}
