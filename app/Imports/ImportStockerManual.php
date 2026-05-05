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
use App\Models\Dc\SecondaryInhouseIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\LoadingLinePlan;
use App\Models\Dc\LoadingLine;
use App\Models\SignalBit\UserLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        function dateConvert($date) {
            // Date Set
            $rawValue = $date;
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

            return $convertedDate;
        }

        $batch = Str::uuid();

        $i = 0;
        foreach ($rows as $row)
        {
            $i++;

            $tanggal = $row[0];
            $actCostingWs = $row[1];
            $color = $row[2];
            $size = $row[3];
            $panelText = $row[4];
            $partText = $row[5];
            $secondaryProcess = $row[6];
            $stockerQty = $row[8];
            $stockerNotes = $row[9];
            $stockerStatus = $row[10];
            $dcQty = $row[11];
            $secInhouseInQty = $row[12];
            $secInhouseOutQty = $row[13];
            $secInQty = $row[14];
            $wipOutQty = $row[15];
            $wipOutTanggal = $row[16];
            $loadingQty = $row[17];
            $loadingTanggal = $row[18];
            $loadingLine = $row[19];
            $loadingBon = $row[20];

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
                    act_costing.kpno = '".$actCostingWs."' and
                    so_det.color = '".$color."' and
                    so_det.size = '".$size."'
                LIMIT 1
            ");

            if ($orderInfo && $orderInfo[0]) {
                $partBagian = explode('-', $partText);

                $namaPart = trim(isset($partBagian[0]) ? $partBagian[0] : null);
                $namaBagian = trim(isset($partBagian[1]) ? $partBagian[1] : null);

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
                        part.act_costing_ws = '".$actCostingWs."' and
                        part.panel = '".$panelText."' and
                        master_part.nama_part = '".$namaPart."'
                        ".( $namaBagian ? " and master_part.bag = '".$namaBagian."'" : "")."
                    LIMIT 1
                ");

                if (!($partDetailInfo && $partDetailInfo[0])) {
                    $part = Part::where("act_costing_ws", $actCostingWs)->
                        where("panel", $panelText)->
                        first();

                    if (!$part) {
                        $part = Part::select("kode")->orderBy("kode", "desc")->first();
                        $partNumber = $part ? intval(substr($part->kode, -5)) + 1 : 1;
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

                    $masterPart = DB::table("master_part")->where("nama_part", "LIKE", "%".$namaPart."%")->where("bag", "%".$namaBagian."%")->first();
                    if (!$masterPart) {
                        $masterPart = DB::table("master_part")->where("nama_part", "LIKE", "%".$namaPart."%")->first();
                    }
                    $masterSecondary = DB::table("master_secondary")->where("proses", "LIKE", "%".$secondaryProcess."%")->first();

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
                                part.act_costing_ws = '".$actCostingWs."' and
                                part.panel = '".$panelText."' and
                                master_part.nama_part = '".$namaPart."'
                            LIMIT 1
                        ");
                    }
                }

                // Stocker About
                if ($partDetailInfo && $partDetailInfo[0]) {
                    $stockerCount = Stocker::lastId()+1;
                    $stockerId = "STK-" . ($stockerCount + $i);

                    // Create Stocker
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
                        'created_at' => Carbon::instance(dateConvert($tanggal))->format('Y-m-d')." 01:00:00",
                    ]);

                    if ($createStocker) {

                        \Log::channel("importStockerManual")->info(["Success Create Stocker ROW :".$i, $createStocker]);

                        // Date Set
                        $convertedDate = dateConvert($tanggal);

                        // Optionally format date with Carbon
                        $formattedDate = Carbon::instance($convertedDate)->format('Y-m-d');

                        // DC Qty
                        if ($dcQty > 0) {
                            DCIn::create([
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "tujuan" => $partDetailInfo[0]->tujuan,
                                "lokasi" => $partDetailInfo[0]->proses,
                                "qty_awal" => $stockerQty,
                                "qty_reject" => (($stockerQty-$dcQty) > 0 ? ($stockerQty-$dcQty) : 0),
                                "qty_replace" => (($stockerQty-$dcQty) < 0 ? ($stockerQty-$dcQty)*(-1) : 0),
                                "tempat" => $partDetailInfo[0]->proses,
                                "tgl_trans" => $formattedDate,
                                "user" => Auth::user()->name,
                                "status" => "N"
                            ]);

                            \Log::channel("importStockerManual")->info(["Success Create DC IN ROW :".$i, $createStocker]);
                        }

                        // Secondary Inhouse IN Qty
                        if ($secInhouseInQty > 0) {
                            SecondaryInhouseIn::create([
                                "tgl_trans" => $formattedDate,
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "qty_in" => $secInhouseInQty,
                                "user" => Auth::user()->name
                            ]);

                            \Log::channel("importStockerManual")->info(["Success Create Secondary INHOUSE IN ROW :".$i, $createStocker]);
                        }

                        // Secondary Inhouse OUT Qty
                        if ($secInhouseOutQty > 0) {
                            SecondaryInhouse::create([
                                "tgl_trans" => $formattedDate,
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "qty_awal" => $secInhouseInQty,
                                "qty_reject" => (($secInhouseInQty-$secInhouseOutQty) > 0 ? ($secInhouseInQty-$secInhouseOutQty) : 0),
                                "qty_replace" => (($secInhouseInQty-$secInhouseOutQty) < 0 ? ($secInhouseInQty-$secInhouseOutQty)*(-1) : 0),
                                "qty_in" => $secInhouseOutQty,
                                "user" => Auth::user()->name
                            ]);

                            \Log::channel("importStockerManual")->info(["Success Create Secondary INHOUSE OUT ROW :".$i, $createStocker]);
                        }

                        // Secondary In
                        if ($secInQty > 0) {
                            SecondaryIn::create([
                                "tgl_trans" => $formattedDate,
                                "id_qr_stocker" => $createStocker->id_qr_stocker,
                                "qty_awal" => $secInhouseOutQty,
                                "qty_reject" => (($secInhouseOutQty-$secInQty) > 0 ? ($secInhouseOutQty-$secInQty) : 0),
                                "qty_replace" => (($secInhouseOutQty-$secInQty) < 0 ? ($secInhouseOutQty-$secInQty)*(-1) : 0),
                                "qty_in" => $secInQty,
                                "user" => Auth::user()->name
                            ]);

                            \Log::channel("importStockerManual")->info(["Success Create SECONDARY IN ROW :".$i, $createStocker]);
                        }

                        // WIP Out
                        if ($wipOutQty > 0) {
                            // Date
                            $convertedDateWipOut = dateConvert($wipOutTanggal);

                            // Optionally format date with Carbon
                            $dateWipOut = Carbon::instance($convertedDate);
                            $formattedDateWipOut = $dateWipOut->format('Y-m-d');
                            $prefixDateWipOut = $dateWipOut->format('my');
                            $monthWipOut = $dateWipOut->format('m');
                            $yearWipOut = $dateWipOut->format('Y');

                            // Get Last WIP OUT
                            $getLastNumber = DB::select("
                                SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
                                FROM wip_out
                                WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
                            ", [$monthWipOut, $yearWipOut]);
                            $lastNumber = $getLastNumber[0]->last_number ?? 0;
                            $formCounter = $lastNumber + 1;

                            // Generate No. Form
                            $noForm = 'SCP/OUT/' . $prefixDateWipOut . '/' . $formCounter++;

                            // Create WIP OUT
                            $createdWipOutId = DB::table('wip_out')->insertGetId([
                                'no_form' => $noForm,
                                'tgl_form' => $formattedDateWipOut,
                                'ket' => "STOCKER INJECT",
                                'created_by' => Auth::user()->id,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);

                            if ($createdWipOutId) {
                                // Create WIP OUT DETAIL
                                DB::table('wip_out_det')->insert([
                                    'id_wip_out' => $createdWipOutId,
                                    'id_qr_stocker' => $stockerId,
                                    'qty' => $wipOutQty,
                                ]);

                                \Log::channel("importStockerManual")->info(["Success Create WIP OUT ROW :".$i, $createStocker]);
                            }
                        }

                        // Loading Line
                        if ($loadingQty > 0) {

                            // Date
                            $convertedDateLoading = dateConvert($loadingTanggal);
                            $formattedDateLoading = Carbon::instance($convertedDateLoading)->format("Y-m-d");

                            if ($loadingLine) {

                                // Define Line id and username
                                if (is_numeric($loadingLine)) {
                                    $line_id = $loadingLine;
                                    $line_username = "line_".(sprintf('%02d', $loadingLine));
                                } else {
                                    $line = UserLine::where("Groupp", "SEWING")->whereRaw("FullName LIKE '%".$loadingLine."%'")->first();
                                    $line_id = $line->line_id;
                                    $line_username = $line->username;
                                }

                                // Get Loading Line Plan
                                $loadingLinePlan = LoadingLinePlan::where("line_id", $line_id)->
                                    where("act_costing_id", $orderInfo[0]->id_cost)->
                                    where("color", $color)->
                                    where("tanggal", $formattedDateLoading)->
                                    first();

                                // Loading Line Iteration
                                $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
                                $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;


                                if ($loadingLinePlan) {


                                    // Create Loading Line when Loading Line Plan Exist
                                    LoadingLine::create([
                                        "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                        "line_id" => $line_id,
                                        "loading_plan_id" => $loadingLinePlan['id'],
                                        "nama_line" => $line_username,
                                        "stocker_id" => $createStocker->id,
                                        "qty" => $loadingQty,
                                        "status" => "active",
                                        "tanggal_loading" => $formattedDateLoading,
                                        "no_bon" => $loadingBon,
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username,
                                    ]);

                                    \Log::channel("importStockerManual")->info(["Success Create Loading Line ROW :".$i, $createStocker]);
                                } else {
                                    // Create Loading Line Plan when Loading Line Plan is not exist
                                    $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                                    $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                                    $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

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

                                    // Create Loading Line
                                    LoadingLine::create([
                                        "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                        "line_id" => $line_id,
                                        "loading_plan_id" => $newLoadingPlan['id'],
                                        "nama_line" => $line_username,
                                        "stocker_id" => $createStocker->id,
                                        "qty" => $loadingQty,
                                        "status" => "active",
                                        "tanggal_loading" => $formattedDateLoading,
                                        "no_bon" => $loadingBon,
                                        "created_by" => Auth::user()->id,
                                        "created_by_username" => Auth::user()->username,
                                    ]);

                                    \Log::channel("importStockerManual")->info(["Success Create Loading Line and Plan ROW :".$i, $createStocker]);
                                }
                            }
                        }
                    } else {
                        \Log::channel("importStockerManual")->info(["Fail Import Stocker Manual on create stocker ROW :".$i, $createStocker]);
                    }
                } else {
                    \Log::channel("importStockerManual")->info(["Fail Import Stocker Manual on part_detailing ROW :".$i, $partDetailInfo]);
                }
            } else {
                \Log::channel("importStockerManual")->info(["Fail Import Stocker Manual on order_info ROW :".$i, $orderInfo]);
            }
        }
    }
}
