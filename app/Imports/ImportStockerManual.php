<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Stocker;
use App\Models\DCIn;
use App\Models\SecondaryInhouse;
use App\Models\SecondaryIn;
use App\Models\LoadingLinePlan;
use App\Models\LoadingLine;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use DB;

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
                $partDetailInfo = DB::select("
                    SELECT
                        part_detail.id,
                        master_secondary.tujuan,
                        master_secondary.proses
                    FROM
                        part_detail
                        LEFT JOIN part ON part.id = part_detail.id
                        LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
                    WHERE
                        part.act_costing_ws = '".$row[0]."' and
                        part.panel = '".$row[3]."' and
                        master_part.nama_part = '".$row[4]."'
                    LIMIT 1
                ");

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
                        'shade' => $row[5],
                        'group_stocker' => $stockerId,
                        'ratio' => 1,
                        'size' => $row[2],
                        'qty_ply' => $row[6],
                        'qty_ply_mod' => null,
                        'qty_cut' => $row[6],
                        'notes' => $row[7],
                        'status' => $row[8],
                        'created_by' => Auth::user()->id,
                        'created_by_username' => Auth::user()->username
                    ]);

                    if ($createStocker) {
                        $excelDate = $row[13]; // e.g., 45778
                        $convertedDate = Date::excelToDateTimeObject($excelDate);

                        // Optionally format with Carbon
                        $formattedDate = Carbon::instance($convertedDate)->format('Y-m-d');

                        if ($row[9] > 0) {
                            DCIn::create([
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "tujuan" => $partDetailInfo[0]->tujuan,
                                "lokasi" => $partDetailInfo[0]->proses,
                                "qty_awal" => $row[6],
                                "qty_reject" => (($row[6]-$row[9]) > 0 ? ($row[6]-$row[9]) : 0),
                                "qty_replace" => (($row[6]-$row[9]) < 0 ? ($row[6]-$row[9])*(-1) : 0),
                                "tempat" => $partDetailInfo[0]->proses,
                                "tgl_trans" => $formattedDate,
                                "user" => Auth::user()->name,
                                "status" => "N"
                            ]);
                        }

                        if ($row[10] > 0) {
                            SecondaryInhouse::create([
                                "tgl_trans" => $formattedDate,
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "qty_awal" => $row[9],
                                "qty_reject" => (($row[9]-$row[10]) > 0 ? ($row[9]-$row[10]) : 0),
                                "qty_replace" => (($row[9]-$row[10]) < 0 ? ($row[9]-$row[10])*(-1) : 0),
                                "qty_in" => $row[10],
                                "user" => Auth::user()->name
                            ]);
                        }

                        if ($row[11] > 0) {
                            SecondaryIn::create([
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
                            if ($row[14]) {
                                $loadingLinePlan = LoadingLinePlan::where("line_id", $row[14])->
                                    where("act_costing_id", $orderInfo[0]->id_cost)->
                                    where("color", $row[1])->
                                    where("tanggal", $formattedDate)->
                                    first();

                                $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
                                $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;
                                if ($loadingLinePlan) {
                                    LoadingLine::create([
                                        "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                        "line_id" => $row[14],
                                        "loading_plan_id" => $loadingLinePlan['id'],
                                        "nama_line" => "line_".(sprintf('%02d', $row[14])),
                                        "stocker_id" => $createStocker->id,
                                        "qty" => $row[12],
                                        "status" => "active",
                                        "tanggal_loading" => $formattedDate,
                                        "no_bon" => $row[15],
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username,
                                    ]);
                                } else {
                                    $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                                    $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                                    $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                                    $newLoadingPlan = LoadingLinePlan::create([
                                        "line_id" => $row[14],
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
                                        "line_id" => $row[14],
                                        "loading_plan_id" => $newLoadingPlan['id'],
                                        "nama_line" => "line_".(sprintf('%02d', $row[14])),
                                        "stocker_id" => $createStocker->id,
                                        "qty" => $row[12],
                                        "status" => "active",
                                        "tanggal_loading" => $formattedDate,
                                        "no_bon" => $row[15],
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
