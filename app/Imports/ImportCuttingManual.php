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
use App\Models\SignalBit\UserLine;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use DB;
use DateTime;
use Exception;

class importCuttingManual implements ToCollection, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        // Group rows by first 5 columns (row[0], row[1], row[2], row[3], row[4])
        $groupedRows = $rows->groupBy(function($row) {
            return $row[0] . '|' . $row[1] . '|' . $row[2] . '|' . $row[4];
        });

        $i = 0;
        foreach ($groupedRows as $group)
        {
            $i++;

            // Take first group child
            $firstRow = $group->first();

            // Date
            $rawValue = $firstRow[0];
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
                    act_costing.kpno = '".$firstRow[1]."' and
                    so_det.color = '".$firstRow[2]."'
                LIMIT 1
            ");

            if ($orderInfo && $orderInfo[0]) {

                // Take Panel
                $panel = DB::connection("mysql_sb")->table("masterpanel")->
                    select("id", "nama_panel")->
                    where("nama_panel", $firstRow[4])->
                    first();

                if ($panel) {

                    // Create Marker
                    $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
                    $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
                    $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);

                    $markerStore = Marker::create([
                        'kode' => $markerCode,
                        'tgl_cutting' => $formattedDate,
                        'act_costing_id' => $orderInfo[0]->id_cost,
                        'act_costing_ws' => $orderInfo[0]->ws,
                        'buyer' => $orderInfo[0]->buyer,
                        'style' => $orderInfo[0]->style,
                        'color' => $orderInfo[0]->color,
                        'panel_id' => $panel->id,
                        'panel' => $panel->nama_panel,
                        'notes' => "MARKER INJECT",
                        'cancel' => 'N',
                        'created_by' => Auth::user()->id,
                        'created_by_username' => Auth::user()->username
                    ]);

                    if ($markerStore) {
                        $markerId = $markerStore->id;

                        // Get all qty values from child rows (column 5) to calculate GCD
                        $qtyValues = $group->pluck(5)->filter()->values()->toArray();

                        // Calculate GCD of all qty values
                        $gcdRatio = $this->calculateGCD($qtyValues);

                        // Log the GCD calculation
                        \Log::channel("importCuttingManual")->info("GCD Calculation - Qty Values: " . json_encode($qtyValues) . ", GCD Result: " . $gcdRatio);

                        // Loop over child rows in group
                        foreach ($group as $childRow) {

                            // Take Order Info for Detail
                            $orderDetailInfo = DB::connection("mysql_sb")->select("
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
                                    act_costing.kpno = '".$childRow[1]."' and
                                    so_det.color = '".$childRow[2]."' and
                                    so_det.size = '".$childRow[3]."'
                                LIMIT 1
                            ");

                            if ($orderDetailInfo && $orderDetailInfo[0]) {

                                // Calculate ratio: child qty divided by GCD
                                $childQty = intval($childRow[5]);
                                $ratioValue = $gcdRatio > 0 ? $childQty / $gcdRatio : $childQty;

                                // Create Marker Detail
                                $markerDetailStore = MarkerDetail::create([
                                    "marker_id" => $markerId,
                                    "so_det_id" => $orderDetailInfo[0]->id,
                                    "size" => $orderDetailInfo[0]->size,
                                    "ratio" => $ratioValue,
                                    "cut_qty" => 0,
                                    "cancel" => 'N'
                                ]);

                                if ($markerDetailStore) {
                                    \Log::channel("importCuttingManual")->info(["Success Import Cutting Manual Marker:".$i, $markerStore, $markerDetailStore]);
                                } else {
                                    \Log::channel("importCuttingManual")->info(["Fail Import Cutting Manual on order_info Marker Detail ROW :".$i, $childRow]);
                                }
                            } else {
                                \Log::channel("importCuttingManual")->info(["Fail Import Cutting Manual on order_info Marker Detail ROW :".$i, $childRow]);
                            }

                        }

                        // Take No Form
                        $hari = Carbon::instance($convertedDate)->format('d');
                        $bulan = Carbon::instance($convertedDate)->format('m');

                        $lastForm = FormCutInput::select("no_form")->whereRaw("no_form LIKE '".$hari."-".$bulan."%'")->orderByRaw("CAST(SUBSTR(no_form, 7) as unsigned) desc")->first();
                        $urutan = $lastForm ? (str_replace($hari."-".$bulan."-", "", $lastForm->no_form) + 1) : 1;
                        $no_form = "$hari-$bulan-$urutan";

                        // Create Cutting Form
                        $cuttingForm = FormCutInput::create([
                            "marker_id" => $markerStore->id,
                            "id_marker" => $markerStore->kode,
                            "no_form" => $no_form,
                            "tgl_form_cut" => $formattedDate,
                            "status" => "SELESAI PENGERJAAN",
                            "user" => Auth::user()->username,
                            "cancel" => "N",
                            "tgl_input" => $formattedDateTime,
                            "waktu_mulai" => $formattedDateTime,
                            "waktu_selesai" => $formattedDateTime,
                            "qty_ply" => $gcdRatio,
                            "total_lembar" => $gcdRatio,
                            "tipe_form_cut" => "MANUAL",
                            "app" => "Y",
                            "app_by" => Auth::user()->id,
                            "app_notes" => "CUTTING INJECT",
                            "app_at" => $formattedDateTime,
                            "operator" => Auth::user()->username,
                            "notes" => "CUTTING INJECT",
                            "notes_new" => "CUTTING INJECT",
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]);

                        if ($cuttingForm) {

                            // Create Cutting Plan
                            $dateFormat = date("dmY", strtotime($formattedDate));
                            $noCutPlan = "CP-" . $dateFormat;

                            $addToCutPlan = CutPlan::create([
                                "no_cut_plan" => $noCutPlan,
                                "tgl_plan" => $formattedDate,
                                "form_cut_id" => $cuttingForm->id,
                                "no_form_cut_input" => $cuttingForm->no_form,
                                "app" => "Y",
                                "app_by" => Auth::user()->id,
                                "app_at" => $formattedDateTime,
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username,
                            ]);

                            // Create Detail Cutting Form
                            $cuttingFormDetail = FormCutInputDetail::create([
                                "form_cut_id" => $cuttingForm->id,
                                "no_form_cut_input" => $cuttingForm->no_form,
                                "group_roll" => "-",
                                "group_stocker" => 1,
                                "lembar_gelaran" => $gcdRatio,
                                "status" => "completed",
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username,
                            ]);

                            if ($cuttingFormDetail) {
                                \Log::channel("importCuttingManual")->info(["Success Import Cutting Manual :".$i, $cuttingForm, $cuttingFormDetail]);
                            } else {
                                \Log::channel("importCuttingManual")->info(["Fail Import Cutting Manual on Cutting Form Detail ROW :".$i, $orderInfo]);
                            }
                        } else {
                            \Log::channel("importCuttingManual")->info(["Fail Import Cutting Manual on Cutting Form ROW :".$i, $orderInfo]);
                        }
                    } else {
                        \Log::channel("importCuttingManual")->info(["Fail Import Cutting Manual on Marker ROW :".$i, $orderInfo]);
                    }

                } else {
                    \Log::channel("importCuttingManual")->info("Panel NOT FOUND ROW : ".$i);
                }
            } else {
                \Log::channel("importCuttingManual")->info("Fail Import Cutting Manual on order_info Marker ROW :".$i);
            }
        }
    }

    /**
     * Calculate Greatest Common Divisor (GCD) of multiple numbers
     *
     * @param array $numbers
     * @return int
     */
    private function calculateGCD($numbers)
    {
        if (empty($numbers)) {
            return 1;
        }

        $gcd = array_shift($numbers);
        foreach ($numbers as $number) {
            $gcd = $this->gcd($gcd, $number);
        }
        return $gcd;
    }

    /**
     * Calculate GCD using Euclidean algorithm
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    private function gcd($a, $b)
    {
        $a = abs((int)$a);
        $b = abs((int)$b);

        while ($b != 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }
        return $a ?: 1;
    }
}
