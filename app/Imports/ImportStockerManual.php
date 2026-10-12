<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Stocker\Stocker;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouseIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\LoadingLinePlan;
use App\Models\Dc\LoadingLine;
use App\Models\SignalBit\UserLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
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
        // Helper konversi tanggal
        $dateConvert = function ($date) {
            if (empty($date)) return null;
            
            if (is_numeric($date)) {
                return Date::excelToDateTimeObject($date);
            }

            $formats = ['d/m/Y', 'Y/m/d', 'Y-m-d', 'd-m-Y'];
            foreach ($formats as $format) {
                $dt = DateTime::createFromFormat($format, $date);
                if ($dt !== false) {
                    return $dt;
                }
            }

            try {
                return new DateTime($date);
            } catch (Exception $e) {
                throw new Exception("Gagal membaca format tanggal: '{$date}'");
            }
        };

        $batch = Str::uuid();
        $i = 1; // Counter baris Excel (Header = row 1)

        foreach ($rows as $row) {
            $i++;

            $tanggalStocker   = $row[0] ?? null;
            $actCostingWs     = $row[1] ?? null;
            $color            = $row[2] ?? null;
            $size             = $row[3] ?? null;
            $panelText        = $row[4] ?? null;
            $partText         = $row[5] ?? null;
            $secondaryProcess = $row[6] ?? null;
            $stockerQty       = $row[8] ?? 0;
            $stockerNotes     = $row[9] ?? null;
            $stockerStatus    = $row[10] ?? null;
            $tanggal          = $row[11] ?? null;
            $dcQty            = $row[12] ?? 0;
            $secInhouseInQty  = $row[13] ?? 0;
            $secInhouseOutQty = $row[14] ?? 0;
            $secInQty         = $row[15] ?? 0;
            $wipOutQty        = $row[16] ?? 0;
            $wipOutTanggal    = $row[17] ?? null;
            $loadingQty       = $row[18] ?? 0;
            $loadingTanggal   = $row[19] ?? null;
            $loadingLine      = $row[20] ?? null;
            $loadingBon       = $row[21] ?? null;

            // Validasi data penting
            if (empty($actCostingWs)) {
                throw new Exception("WS Kosong pada baris ke-{$i}");
            }

            // 1. Ambil Order Info
            $orderInfo = DB::connection("mysql_sb")->select("
                SELECT
                    mastersupplier.Supplier as buyer,
                    act_costing.id as id_cost,
                    act_costing.kpno as ws,
                    act_costing.styleno as style,
                    so_det.color,
                    so_det.size,
                    so_det.id
                FROM so_det
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                WHERE act_costing.kpno = ? AND so_det.color = ? AND so_det.size = ?
                LIMIT 1
            ", [$actCostingWs, $color, $size]);

            if (!($orderInfo && isset($orderInfo[0]))) {
                Log::channel("importStockerManual")->error("Order Info tidak ditemukan pada baris ke-{$i}", [
                    'ws' => $actCostingWs, 'color' => $color, 'size' => $size
                ]);
                throw new Exception("Order Info tidak ditemukan (WS: {$actCostingWs}, Color: {$color}, Size: {$size}) pada baris ke-{$i}");
            }

            $partBagian = explode('-', $partText);
            $namaPart   = trim($partBagian[0] ?? '');
            $namaBagian = trim($partBagian[1] ?? '');

            // 2. Ambil Part Detail Info
            $partDetailInfo = DB::select("
                SELECT
                    part_detail.id,
                    master_secondary.tujuan,
                    master_secondary.proses
                FROM part_detail
                LEFT JOIN part ON part.id = part_detail.part_id
                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
                WHERE part.act_costing_ws = ? AND part.panel = ? AND master_part.nama_part = ?
                " . ($namaBagian ? " AND master_part.bag = ?" : "") . "
                LIMIT 1
            ", $namaBagian ? [$actCostingWs, $panelText, $namaPart, $namaBagian] : [$actCostingWs, $panelText, $namaPart]);

            // Jika Part Detail belum ada, buat Part & Part Detail baru
            if (!($partDetailInfo && isset($partDetailInfo[0]))) {
                $part = Part::where("act_costing_ws", $actCostingWs)
                    ->where("panel", $panelText)
                    ->first();

                if (!$part) {
                    $lastPart = Part::select("kode")->orderBy("kode", "desc")->first();
                    $partNumber = $lastPart ? intval(substr($lastPart->kode, -5)) + 1 : 1;
                    $partCode = 'PRT' . sprintf('%05s', $partNumber);

                    $panel = DB::connection("mysql_sb")->table("masterpanel")->where("nama_panel", $panelText)->first();

                    $part = Part::create([
                        "kode" => $partCode,
                        "act_costing_id" => $orderInfo[0]->id_cost,
                        "act_costing_ws" => $orderInfo[0]->ws,
                        "color" => $orderInfo[0]->color,
                        "panel_id" => $panel ? $panel->id : null,
                        "panel" => $panelText,
                        "buyer" => $orderInfo[0]->buyer,
                        "style" => $orderInfo[0]->style,
                        "created_by" => Auth::user()->id,
                        "created_by_username" => Auth::user()->username,
                    ]);
                }

                $masterPart = DB::table("master_part")
                    ->where("nama_part", "LIKE", "%".$namaPart."%")
                    ->where("bag", "LIKE", "%".$namaBagian."%")
                    ->first();

                if (!$masterPart) {
                    $masterPart = DB::table("master_part")->where("nama_part", "LIKE", "%".$namaPart."%")->first();
                }

                $masterSecondary = DB::table("master_secondary")->where("proses", "LIKE", "%".$secondaryProcess."%")->first();

                if (!$part) {
                    throw new Exception("Gagal membuat/menemukan Part '{$actCostingWs}' - '{$panelText}' pada baris ke-{$i}");
                }

                if (!$masterPart) {
                    throw new Exception("Master Part '{$namaPart}' tidak ditemukan pada baris ke-{$i}");
                }

                if (!$masterSecondary) {
                    throw new Exception("Master Secondary Process '{$secondaryProcess}' tidak ditemukan pada baris ke-{$i}");
                }

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
                        FROM part_detail
                        LEFT JOIN part ON part.id = part_detail.part_id
                        LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail.master_secondary_id
                        WHERE part.act_costing_ws = ? AND part.panel = ? AND master_part.nama_part = ?
                        LIMIT 1
                    ", [$actCostingWs, $panelText, $namaPart]);
                }
            }

            if (!($partDetailInfo && isset($partDetailInfo[0]))) {
                throw new Exception("Gagal mendapatkan data Part Detail Info pada baris ke-{$i}");
            }

            // 3. Simpan Data Stocker
            $stockerCount = Stocker::lastId() + 1;
            $stockerId = "STK-" . ($stockerCount + $i);

            $parsedTanggalStocker = $dateConvert($tanggalStocker);
            $formattedTanggalStocker = $parsedTanggalStocker ? Carbon::instance($parsedTanggalStocker)->format('Y-m-d') . " 01:00:00" : Carbon::now();

            $createStocker = Stocker::create([
                'id_qr_stocker' => $stockerId,
                'act_costing_ws' => $actCostingWs,
                'part_detail_id' => $partDetailInfo[0]->id,
                'so_det_id' => $orderInfo[0]->id,
                'color' => $color,
                'panel' => $panelText,
                'shade' => $batch,
                'group_stocker' => $batch,
                'ratio' => 1,
                'size' => $size,
                'qty_ply' => $stockerQty,
                'qty_ply_mod' => null,
                'qty_cut' => $stockerQty,
                'notes' => $stockerNotes,
                'status' => $stockerStatus,
                'created_by' => Auth::user()->id,
                'created_by_username' => Auth::user()->username,
                'created_at' => $formattedTanggalStocker,
            ]);

            if (!$createStocker) {
                throw new Exception("Gagal membuat data Stocker pada baris ke-{$i}");
            }

            Log::channel("importStockerManual")->info("Success Create Stocker ROW : " . $i);

            $convertedDate = $dateConvert($tanggal);
            $formattedDate = $convertedDate ? Carbon::instance($convertedDate)->format('Y-m-d') : date('Y-m-d');

            // 4. Input DC Qty
            if ($dcQty != 0) {
                DCIn::create([
                    "id_qr_stocker" => $createStocker->id_qr_stocker,
                    "tujuan" => $partDetailInfo[0]->tujuan,
                    "lokasi" => $partDetailInfo[0]->proses,
                    "qty_awal" => $stockerQty,
                    "qty_reject" => (($stockerQty - $dcQty) > 0 ? ($stockerQty - $dcQty) : 0),
                    "qty_replace" => (($stockerQty - $dcQty) < 0 ? ($stockerQty - $dcQty) * (-1) : 0),
                    "tempat" => $partDetailInfo[0]->proses,
                    "tgl_trans" => $formattedDate,
                    "user" => Auth::user()->name,
                    "status" => "N"
                ]);
            }

            // 5. Input Secondary Inhouse IN
            if ($secInhouseInQty != 0) {
                SecondaryInhouseIn::create([
                    "tgl_trans" => $formattedDate,
                    "id_qr_stocker" => $createStocker->id_qr_stocker,
                    "qty_in" => $secInhouseInQty,
                    "user" => Auth::user()->name
                ]);
            }

            // 6. Input Secondary Inhouse OUT
            if ($secInhouseOutQty != 0) {
                SecondaryInhouse::create([
                    "tgl_trans" => $formattedDate,
                    "id_qr_stocker" => $createStocker->id_qr_stocker,
                    "qty_awal" => $secInhouseInQty,
                    "qty_reject" => (($secInhouseInQty - $secInhouseOutQty) > 0 ? ($secInhouseInQty - $secInhouseOutQty) : 0),
                    "qty_replace" => (($secInhouseInQty - $secInhouseOutQty) < 0 ? ($secInhouseInQty - $secInhouseOutQty) * (-1) : 0),
                    "qty_in" => $secInhouseOutQty,
                    "user" => Auth::user()->name
                ]);
            }

            // 7. Input Secondary IN
            if ($secInQty != 0) {
                SecondaryIn::create([
                    "tgl_trans" => $formattedDate,
                    "id_qr_stocker" => $createStocker->id_qr_stocker,
                    "qty_awal" => $secInhouseOutQty,
                    "qty_reject" => (($secInhouseOutQty - $secInQty) > 0 ? ($secInhouseOutQty - $secInQty) : 0),
                    "qty_replace" => (($secInhouseOutQty - $secInQty) < 0 ? ($secInhouseOutQty - $secInQty) * (-1) : 0),
                    "qty_in" => $secInQty,
                    "user" => Auth::user()->name
                ]);
            }

            // 8. Input WIP Out
            if ($wipOutQty != 0) {
                $convertedDateWipOut = $dateConvert($wipOutTanggal);
                $dateWipOut = Carbon::instance($convertedDateWipOut);
                $formattedDateWipOut = $dateWipOut->format('Y-m-d');
                $prefixDateWipOut = $dateWipOut->format('my');
                $monthWipOut = $dateWipOut->format('m');
                $yearWipOut = $dateWipOut->format('Y');

                $getLastNumber = DB::select("
                    SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
                    FROM wip_out
                    WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
                ", [$monthWipOut, $yearWipOut]);

                $lastNumber = $getLastNumber[0]->last_number ?? 0;
                $formCounter = $lastNumber + 1;
                $noForm = 'SCP/OUT/' . $prefixDateWipOut . '/' . $formCounter;

                $createdWipOutId = DB::table('wip_out')->insertGetId([
                    'no_form' => $noForm,
                    'tgl_form' => $formattedDateWipOut,
                    'ket' => $stockerNotes ?? "STOCKER INJECT",
                    'created_by' => Auth::user()->id,
                    'created_at' => $formattedTanggalStocker,
                    'updated_at' => Carbon::now()
                ]);

                if ($createdWipOutId) {
                    DB::table('wip_out_det')->insert([
                        'id_wip_out' => $createdWipOutId,
                        'id_qr_stocker' => $stockerId,
                        'qty' => $wipOutQty,
                        'created_at' => $formattedTanggalStocker,
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            // 9. Input Loading Line
            if ($loadingQty != 0) {
                $convertedDateLoading = $dateConvert($loadingTanggal);
                $formattedDateLoading = Carbon::instance($convertedDateLoading)->format("Y-m-d");

                if ($loadingLine) {
                    if (is_numeric($loadingLine)) {
                        $line_id = $loadingLine;
                        $line_username = "line_" . sprintf('%02d', $loadingLine);
                    } else {
                        $line = UserLine::where("Groupp", "SEWING")->whereRaw("FullName LIKE '%" . $loadingLine . "%'")->first();
                        if (!$line) {
                            throw new Exception("Line Sewing '{$loadingLine}' tidak ditemukan pada baris ke-{$i}");
                        }
                        $line_id = $line->line_id;
                        $line_username = $line->username;
                    }

                    $loadingLinePlan = LoadingLinePlan::where("line_id", $line_id)
                        ->where("act_costing_id", $orderInfo[0]->id_cost)
                        ->where("color", $color)
                        ->where("tanggal", $formattedDateLoading)
                        ->first();

                    $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
                    $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;

                    if ($loadingLinePlan) {
                        LoadingLine::create([
                            "kode" => "LOAD" . sprintf('%05s', ($lastLoadingLineNumber + $i)),
                            "line_id" => $line_id,
                            "loading_plan_id" => $loadingLinePlan->id,
                            "nama_line" => $line_username,
                            "stocker_id" => $createStocker->id,
                            "qty" => $loadingQty,
                            "status" => "active",
                            "tanggal_loading" => $formattedDateLoading,
                            "no_bon" => $loadingBon,
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]);
                    } else {
                        $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                        $lastLoadingPlanNumber = $lastLoadingPlan ? intval(substr($lastLoadingPlan->latest_kode, -5)) + 1 : 1;
                        $kodeLoadingPlan = 'LLP' . sprintf('%05s', $lastLoadingPlanNumber);

                        $newLoadingPlan = LoadingLinePlan::create([
                            "line_id" => $line_id,
                            "kode" => $kodeLoadingPlan,
                            "act_costing_id" => $orderInfo[0]->id_cost,
                            "act_costing_ws" => $actCostingWs,
                            "buyer" => $orderInfo[0]->buyer,
                            "style" => $orderInfo[0]->style,
                            "color" => $color,
                            "tanggal" => $formattedDateLoading
                        ]);

                        LoadingLine::create([
                            "kode" => "LOAD" . sprintf('%05s', ($lastLoadingLineNumber + $i)),
                            "line_id" => $line_id,
                            "loading_plan_id" => $newLoadingPlan->id,
                            "nama_line" => $line_username,
                            "stocker_id" => $createStocker->id,
                            "qty" => $loadingQty,
                            "status" => "active",
                            "tanggal_loading" => $formattedDateLoading,
                            "no_bon" => $loadingBon,
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]);
                    }
                }
            }
        }
    }
}