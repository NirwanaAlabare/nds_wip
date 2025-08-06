<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\ReworkPacking;
use App\Models\SignalBit\RftPacking;
use App\Models\YearSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\Sewing\CheckOutputDetailListExport;
use DB;
use Excel;

class SewingToolsController extends Controller
{
    public function index() {
        return view('sewing.tools.tools', [
            "page" => "dashboard-sewing-eff"
        ]);
    }

    public function missUser() {
        ini_set("max_execution_time", 3600);

        $masterUser = collect(DB::connection("mysql_sb")->select("
            SELECT
                output_rfts.id,
                userpassword.username as actual_sewing_line,
                userpassword.line_id as actual_line_id,
                master_plan.sewing_line as plan_sewing_line,
                plan_line.line_id as plan_line_id,
                actual_plan.id as actual_plan_id,
                master_plan.id as plan_plan_id,
                MAX(plan_user.id) as plan_user_id
            FROM
                output_rfts
                LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                LEFT JOIN userpassword plan_line on plan_line.username = master_plan.sewing_line
                LEFT JOIN user_sb_wip plan_user on plan_user.line_id = plan_line.line_id
                LEFT JOIN master_plan actual_plan on actual_plan.id_ws = master_plan.id_ws and actual_plan.color = master_plan.color and actual_plan.tgl_plan = master_plan.tgl_plan and actual_plan.sewing_line = userpassword.username
            WHERE
                output_rfts.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y-m-d")." 23:59:59'
                AND (userpassword.username != master_plan.sewing_line)
            GROUP BY
                output_rfts.id
        "));

        if ($masterUser->count() < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada output user line yang miss',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        $success = [];
        $fails = [];
        $unavailable = [];
        foreach ($masterUser as $mu) {
            if ($mu->actual_plan_id) {
                $updateRft = Rft::where("id", $mu->id)->update([
                    "master_plan_id" => $mu->actual_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mu->id, "change output master plan"]);
                } else {
                    array_push($fails, [$mu->id, "change output master plan"]);
                }
            } else if ($mu->plan_user_id) {
                $updateRft = Rft::where("id", $mu->id)->update([
                    "created_by" => $mu->plan_user_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mu->id, "change output user"]);
                } else {
                    array_push($fails, [$mu->id, "change output user"]);
                }
            }
        }

        Log::channel('missUserOutput')->info([
            "Repair User Output",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Total Data ".count($success),
            "Success" => $success,
            "Fails" => $fails,
            "Unavailable" => $unavailable
        ]);

        return array(
            'status' => 200,
            'message' => (count($success) > 0 ? 'Berhasil mengubah '.count($success).' data </br>': '').' '.(count($unavailable) > 0 ? 'Tidak dapat menemukan '.count($unavailable).' data </br>': '').' '.(count($fails) > 0 ? 'Gagal mengubah '.count($fails).' data </br>': ''),
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function missMasterPlan() {
        ini_set("max_execution_time", 3600);

        $masterPlan = collect(DB::connection("mysql_sb")->select("
            SELECT
                output.id,
                output.plan_id,
                output.plan_color,
                output.plan_act_costing_id,
                actual.id as act_plan_id,
                actual.color as act_color,
                actual.id_ws as act_act_costing_id,
                output.actual_color as color,
                output.size
            FROM
            (
                SELECT
                    output_rfts.id,
                    master_plan.id plan_id,
                    master_plan.color plan_color,
                    master_plan.id_ws plan_act_costing_id,
                    so_det.color actual_color,
                    act_costing.id actual_act_costing_id,
                    so_det.size,
                    userpassword.username line,
                    master_plan.tgl_plan
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip on user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword on userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                WHERE
                    output_rfts.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y-m-d")." 23:59:59'
                    and (master_plan.id_ws != act_costing.id OR master_plan.color != so_det.color)
            ) output
            LEFT JOIN master_plan actual on
                actual.id_ws = output.actual_act_costing_id AND
                actual.color = output.actual_color and
                actual.sewing_line = output.line and
                actual.tgl_plan = output.tgl_plan
            WHERE
                actual.id != output.plan_id
            GROUP BY
                output.id
        "));

        if ($masterPlan->count() < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada master plan yang miss',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        $success = [];
        $fails = [];
        $unavailable = [];
        foreach ($masterPlan as $mp) {
            if ($mp->act_plan_id) {
                $updateRft = Rft::where("id", $mp->id)->update([
                    "master_plan_id" => $mp->act_plan_id
                ]);

                if ($updateRft) {
                    array_push($success, [$mp, "change output master plan"]);
                } else {
                    array_push($fails, [$mp, "change output master plan"]);
                }
            } else {
                $soDet = SoDet::select("so_det.id")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("act_costing.id", $mp->plan_act_costing_id)->where("so_det.color", $mp->plan_color)->where("so_det.size", $mp->size)->first();

                if ($soDet) {
                    $rft = Rft::where("id", $mp->id)->first();
                    $rft->so_det_id = $soDet->id;
                    $rft->save();

                    if ($rft) {
                        $yearSequence = YearSequence::where("id_year_sequence", $rft->kode_numbering)->update(["so_det_id" => $rft->so_det_id]);

                        if ($yearSequence) {
                            array_push($success, [$mp, "change output origin"]);
                        }
                    } else {
                        array_push($fails, [$mp, "change output origin"]);
                    }
                } else {
                    array_push($unavailable, [$mp, "change output origin"]);
                }
            }
        }

        Log::channel('missMasterPlanOutput')->info([
            "Repair Master Plan Missing Output",
            "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
            "Total Data ".count($success),
            "Success" => $success,
            "Fails" => $fails,
            "Unavailable" => $unavailable
        ]);

        return array(
            'status' => 200,
            'message' => (count($success) > 0 ? 'Berhasil mengubah '.count($success).' data </br>': '').' '.(count($unavailable) > 0 ? 'Tidak dapat menemukan master plan '.count($unavailable).' data </br>': '').' '.(count($fails) > 0 ? 'Gagal mengubah '.count($fails).' data </br>': ''),
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function missRework() {
        ini_set("max_execution_time", 3600);

        // Get Defects with Missing Rework
        $defects = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.id as defect_id, 'NORMAL' as status, output_defects.created_by, output_defects.created_at, output_defects.updated_at from output_defects left join output_reworks on output_reworks.defect_id = output_defects.id where output_reworks.id is null and defect_status = 'reworked'"));

        $defectArr = $defects->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRework = Rework::insert($defectArr);

        // Get Reworks Data with Of Course Missing RFT
        $reworks = collect(DB::connection("mysql_sb")->select("select null as id, output_defects.master_plan_id, output_defects.so_det_id, 'REWORK' as status, output_reworks.id as rework_id, output_defects.created_by, output_reworks.created_at, output_reworks.updated_at, output_defects.kode_numbering, output_defects.kode_numbering no_cut_size from output_reworks left join output_defects on output_defects.id = output_reworks.defect_id left join output_rfts on output_rfts.rework_id = output_reworks.id where output_rfts.id is null"));

        if ($reworks->count() < 1) {
            return array(
                'status' => 400,
                'message' => 'Tidak ada rework yang hilang',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        $reworkArr = $reworks->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRft = Rft::insert($reworkArr);

        // Get Defects with Missing Rework Packing
        $defectsPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects_packing.id as defect_id, 'NORMAL' as status, output_defects_packing.created_by, output_defects_packing.created_at, output_defects_packing.updated_at from output_defects_packing left join output_reworks_packing on output_reworks_packing.defect_id = output_defects_packing.id where output_reworks_packing.id is null and defect_status = 'reworked'"));

        $defectPackingArr = $defectsPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToReworkPacking = ReworkPacking::insert($defectPackingArr);

        // Get Reworks Data with Of Course Missing RFT Packing
        $reworksPacking = collect(DB::connection("mysql_sb")->select("select null as id, output_defects_packing.master_plan_id, output_defects_packing.so_det_id, 'REWORK' as status, output_reworks_packing.id as rework_id, output_defects_packing.created_by, output_reworks_packing.created_at, output_reworks_packing.updated_at, output_defects_packing.kode_numbering, output_defects_packing.kode_numbering no_cut_size from output_reworks_packing left join output_defects_packing on output_defects_packing.id = output_reworks_packing.defect_id left join output_rfts_packing on output_rfts_packing.rework_id = output_reworks_packing.id where output_rfts_packing.id is null"));

        $reworkPackingArr = $reworksPacking->map(function ($item, $key) {
            return (array) $item;
        })->toArray();

        $storeToRftPacking = RftPacking::insert($reworkPackingArr);

        if ($storeToRework && $storeToRft) {
            Log::channel('missReworkOutput')->info([
                "Repair Defect->Rework->RFT Chain Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                "Total Data ".count($defects)." - ".$defects,
                "Total Data Packing ".count($defectsPacking)." - ".$defectsPacking,
                $reworks
            ]);

            return array(
                'status' => 200,
                'message' => 'Berhasil memperbaiki <br> Data Defect = '.count($defects).' <br> Data Rework = '.count($reworks).'',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Terjadi kesalahan',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function checkOutputDetail() {
        $buyers = DB::connection("mysql_sb")->select("
            SELECT
                Id_Supplier as id,
                Supplier as buyer
            FROM
                mastersupplier
                LEFT JOIN act_costing ON act_costing.id_buyer = mastersupplier.Id_Supplier
            WHERE
                tipe_sup = 'C'
                AND act_costing.cost_date > DATE_SUB( CURRENT_DATE, INTERVAL 1 YEAR )
            GROUP BY
                mastersupplier.Id_Supplier
            ORDER BY
                mastersupplier.Supplier ASC
        ");

        $orders = DB::connection("mysql_sb")->table("act_costing")->selectRaw("act_costing.id, act_costing.kpno as ws, act_costing.styleno as style")->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        $lines = DB::connection("mysql_sb")->table("userpassword")->select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        $defectTypes = DB::connection("mysql_sb")->table("output_defect_types")->whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy("updated_at", "desc")->get();

        return view("sewing.tools.check-output-detail", ["buyers" => $buyers, "orders" => $orders, "lines" => $lines, "defectTypes" => $defectTypes, "page" => "dashboard-sewing-eff"]);
    }

    public function checkOutputDetailList(Request $request) {
        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($request->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$request->buyer."'";
            $buyerFilterOutput = " and act_costing.buyer = '".$request->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($request->ws) {
            $wsFilterYs = " and msb.ws = '".$request->ws."'";
            $wsFilterOutput = " and act_costing.kpno = '".$request->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->style) {
            $styleFilterYs = " and msb.styleno = '".$request->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$request->style."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->color) {
            $styleFilterYs = " and msb.color = '".$request->color."'";
            $styleFilterOutput = " and so_det.color = '".$request->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($request->size && count($request->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $request->size));

            $sizeFilterYs = " and msb.color in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.color in (".$sizeList.")";
        }

        $kodeFilterYs = "";
        $kodeFilterOutput = "";
        if ($request->kode && strlen($request->kode) > 0) {
            $kodeList = addQuotesAround($request->kode);

            $kodeFilterYs = " and ys.id_year_sequence in (".$kodeList.")";
            $kodeFilterOutput = " and kode_numbering in (".$kodeList.")";
        }

        $additionalFilter = "";

        $tglLoading = "";
        if ($request->tanggal_loading) {
            $tglLoading = " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) = '".$request->tanggal_loading."'";
        }

        $lineLoading = "";
        if ($request->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$request->line_loading."'";
        }

        $tglPlan = "";
        if ($request->tanggal_plan) {
            $tglPlan = " and master_plan.tgl_plan = '".$request->tanggal_plan."'";
            $additionalFilter .= "output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($request->tanggal_output) {
            $tglOutput = " and output_rfts.updated_at between '".$request->tanggal_output." 00:00:00' and '".$request->tanggal_output." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$request->tanggal_output." 00:00:00' and '".$request->tanggal_output." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$request->tanggal_output." 00:00:00' and '".$request->tanggal_output." 23:59:59'";
            $additionalFilter .= " and output.tgl is not null";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($request->tanggal_packing) {
            $tglOutputPck = " and output_rfts.updated_at between '".$request->tanggal_packing." 00:00:00' and '".$request->tanggal_packing." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$request->tanggal_packing." 00:00:00' and '".$request->tanggal_packing." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$request->tanggal_packing." 00:00:00' and '".$request->tanggal_packing." 23:59:59'";
            $additionalFilter .= " and output_packing.tgl is not null";
        }

        // Sewing
        $lineOutput = "";
        if ($request->line_output) {
            $lineOutput = " and userpassword.username = '".$request->line_output."'";
            $additionalFilter .= " and output.line is not null";
        }

        $statusOutput = "";
        if ($request->status_output && count($request->status_output) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status_output));

            $statusOutput = " and output.status in (".$statusList.")";
        }

        $defectOutput = "";
        if ($request->defect_output && count($request->defect_output) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_output));

            $defectOutput = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output.defect_type is not null";
        }

        $allocationOutput = "";
        if ($request->allocation_output && count($request->allocation_output) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_output));

            $allocationOutput = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output.allocation is not null";
        }

        // Packing
        $linePacking = "";
        if ($request->line_packing) {
            $linePacking = " and userpassword.username = '".$request->line_packing."'";
            $additionalFilter .= " and output_packing.line is not null";
        }

        $statusPacking = "";
        if ($request->status_packing && count($request->status_packing) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($request->defect_packing && count($request->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_packing));

            $defectPacking = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($request->allocation_packing && count($request->allocation_packign) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_packing));

            $allocationPacking = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output_packing.allocation is not null";
        }

        // Cross-line loading
        $crossLineLoading = "";
        if ($request->crossline_loading) {
            $crossLineLoading = " and output.line != COALESCE(loading.nama_line, loading_bk.nama_line)";
            $additionalFilter .= " and output.line is not null and COALESCE(loading.nama_line, loading_bk.nama_line) is not null";
        }

        // Cross-line output
        $crossLineOutput = "";
        if ($request->crossline_output) {
            $crossLineOutput = " and output.line != output_packing.line";
            $additionalFilter .= " and output.line is not null and output_packing.line is not null";
        }

        // Missmatch
        $missmatchOutput = "";
        $missmatchDefect = "";
        $missmatchReject = "";
        if ($request->missmatch_code) {
            $missmatchOutput = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefect = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchReject = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output.kode_numbering is not null";
        }

        // Missmatch
        $missmatchOutputPck = "";
        $missmatchDefectPck = "";
        $missmatchRejectPck = "";
        if ($request->missmatch_code_packing) {
            $missmatchOutputPck = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefectPck = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchRejectPck = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output_packing.kode_numbering is not null";
        }

        // Backdate
        $backDateOutput = "";
        $backDateDefect = "";
        $backDateReject = "";
        if ($request->back_date) {
            $backDateOutput = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefect = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateReject = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output.tgl is not null";
        }

        // Backdate
        $backDateOutputPck = "";
        $backDateDefectPck = "";
        $backDateRejectPck = "";
        if ($request->back_date_packing) {
            $backDateOutputPck = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefectPck = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateRejectPck = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output_packing.tgl is not null";
        }

        $filterYs = $buyerFilterYs."
                    ".$wsFilterYs."
                    ".$styleFilterYs."
                    ".$styleFilterYs."
                    ".$sizeFilterYs."
                    ".$kodeFilterYs;

        $filterDefectOutput = $tglPlan."
                    ".$tglDefect."
                    ".$backDateDefect."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchDefect."
                    ".$backDateDefect;

        $filterRftOutput = $tglPlan."
                    ".$tglOutput."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$missmatchOutput."
                    ".$backDateOutput;

        $filterRejectOutput = $tglPlan."
                    ".$tglReject."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchReject."
                    ".$backDateReject;

        $filterDefectPck = $tglPlan."
                    ".$tglDefectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchDefectPck."
                    ".$backDateDefectPck;

        $filterRftPck = $tglPlan."
                    ".$tglOutputPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$missmatchOutputPck."
                    ".$backDateOutputPck;

        $filterRejectPck = $tglPlan."
                    ".$tglRejectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchRejectPck."
                    ".$backDateRejectPck;

        // Callback
        $callbackFilterYs = "";
        $callbackFilterOutput = "";
        if (!trim(str_replace("\n", "", $filterYs)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterRejectOutput)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterYs = " and DATE(ys.updated_at) > CURRENT_DATE()";
            $callbackFilterOutput = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $outputList = DB::connection("mysql_sb")->select("
            select
                COALESCE(output.kode_numbering, output_packing.kode_numbering, id_year_sequence) kode,
                COALESCE(output.Supplier, ys.buyer) buyer,
                COALESCE(output.ws, ys.ws) ws,
                COALESCE(output.styleno, ys.styleno) style,
                COALESCE(output.color, ys.color) color,
                COALESCE(output.size, ys.size) size,
                COALESCE(stk.id_qr_stocker, stk_bk.id_qr_stocker) as stocker,
                COALESCE(loading.nama_line, loading_bk.nama_line) as line_loading,
                COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) as tanggal_loading,
                output.tgl_plan tanggal_plan,
                output.tgl tanggal_output,
                output.line line_output,
                output.status status_output,
                output.defect_type as defect_output,
                output.allocation as allocation_output,
                output_packing.tgl tanggal_output_packing,
                output_packing.line line_output_packing,
                output_packing.status status_output_packing,
                output_packing.defect_type as defect_output_packing,
                output_packing.allocation as allocation_output_packing
            from
                (
                    select
                        ys.*,
                        msb.buyer,
                        msb.ws,
                        msb.styleno,
                        msb.color
                    from
                        laravel_nds.year_sequence as ys
                        left join laravel_nds.master_sb_ws as msb on msb.id_so_det = ys.so_det_id
                    where
                        ys.id is not null
                        ".$filterYs."
                        ".$callbackFilterYs."
                ) as ys
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_defects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        ".$filterDefectOutput."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        ".$filterRftOutput."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rejects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        ".$filterRejectOutput."
                        ".$callbackFilterOutput."
                ) output ON output.kode_numbering = ys.id_year_sequence
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects_packing as output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_defects.created_by
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        ".$filterDefectPck."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts_packing as output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rfts.created_by
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        ".$filterRftPck."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects_packing as output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rejects.created_by
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        ".$filterRejectPck."
                        ".$callbackFilterOutput."
                ) output_packing ON output_packing.kode_numbering = ys.id_year_sequence
            left join laravel_nds.stocker_input as stk on stk.id_qr_stocker = ys.id_qr_stocker
            left join laravel_nds.stocker_input as stk_bk on (stk_bk.form_cut_id = ys.form_cut_id and stk_bk.form_reject_id = ys.form_reject_id and stk_bk.form_piece_id = ys.form_piece_id) and stk_bk.so_det_id = ys.so_det_id and CAST(stk_bk.range_awal AS UNSIGNED) <= CAST(ys.number AS UNSIGNED) and CAST(stk_bk.range_akhir AS UNSIGNED) >= CAST(ys.number AS UNSIGNED)
            left join laravel_nds.loading_line as loading on loading.stocker_id = stk.id
            left join laravel_nds.loading_line as loading_bk on loading_bk.stocker_id = stk_bk.id
            WHERE
                ys.id is not null
                ".$tglLoading."
                ".$lineLoading."
                ".$statusOutput."
                ".$statusPacking."
                ".$crossLineLoading."
                ".$crossLineOutput."
                ".$additionalFilter."
        ");

        return Datatables::of($outputList)->toJson();
    }

    public function checkOutputDetailExport(Request $request) {
        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($request->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$request->buyer."'";
            $buyerFilterOutput = " and act_costing.buyer = '".$request->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($request->ws) {
            $wsFilterYs = " and msb.ws = '".$request->ws."'";
            $wsFilterOutput = " and act_costing.kpno = '".$request->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->style) {
            $styleFilterYs = " and msb.styleno = '".$request->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$request->style."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->color) {
            $styleFilterYs = " and msb.color = '".$request->color."'";
            $styleFilterOutput = " and so_det.color = '".$request->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($request->size && count($request->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $request->size));

            $sizeFilterYs = " and msb.color in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.color in (".$sizeList.")";
        }

        $kodeFilterYs = "";
        $kodeFilterOutput = "";
        if ($request->kode && strlen($request->kode) > 0) {
            $kodeList = addQuotesAround($request->kode);

            $kodeFilterYs = " and ys.id_year_sequence in (".$kodeList.")";
            $kodeFilterOutput = " and kode_numbering in (".$kodeList.")";
        }

        $additionalFilter = "";

        $tglLoading = "";
        if ($request->tanggal_loading) {
            $tglLoading = " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) = '".$request->tanggal_loading."'";
        }

        $lineLoading = "";
        if ($request->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$request->line_loading."'";
        }

        $tglPlan = "";
        if ($request->tanggal_plan) {
            $tglPlan = " and master_plan.tgl_plan = '".$request->tanggal_plan."'";
            $additionalFilter .= "output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($request->tanggal_output) {
            $tglOutput = " and output_rfts.updated_at between '".$request->tanggal_output." 00:00:00' and '".$request->tanggal_output." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$request->tanggal_output." 00:00:00' and '".$request->tanggal_output." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$request->tanggal_output." 00:00:00' and '".$request->tanggal_output." 23:59:59'";
            $additionalFilter .= " and output.tgl is not null";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($request->tanggal_packing) {
            $tglOutputPck = " and output_rfts.updated_at between '".$request->tanggal_packing." 00:00:00' and '".$request->tanggal_packing." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$request->tanggal_packing." 00:00:00' and '".$request->tanggal_packing." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$request->tanggal_packing." 00:00:00' and '".$request->tanggal_packing." 23:59:59'";
            $additionalFilter .= " and output_packing.tgl is not null";
        }

        // Sewing
        $lineOutput = "";
        if ($request->line_output) {
            $lineOutput = " and userpassword.username = '".$request->line_output."'";
            $additionalFilter .= " and output.line is not null";
        }

        $statusOutput = "";
        if ($request->status_output && count($request->status_output) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status_output));

            $statusOutput = " and output.status in (".$statusList.")";
        }

        $defectOutput = "";
        if ($request->defect_output && count($request->defect_output) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_output));

            $defectOutput = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output.defect_type is not null";
        }

        $allocationOutput = "";
        if ($request->allocation_output && count($request->allocation_output) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_output));

            $allocationOutput = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output.allocation is not null";
        }

        // Packing
        $linePacking = "";
        if ($request->line_packing) {
            $linePacking = " and userpassword.username = '".$request->line_packing."'";
            $additionalFilter .= " and output_packing.line is not null";
        }

        $statusPacking = "";
        if ($request->status_packing && count($request->status_packing) > 0) {
            $statusList = addQuotesAround(implode("\n", $request->status));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($request->defect_packing && count($request->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_packing));

            $defectPacking = " and output_defect_types.defect_type_id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($request->allocation_packing && count($request->allocation_packign) > 0) {
            $allocationList = addQuotesAround(implode("\n", $request->allocation_packing));

            $allocationPacking = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output_packing.allocation is not null";
        }

        // Cross-line loading
        $crossLineLoading = "";
        if ($request->crossline_loading) {
            $crossLineLoading = " and output.line != COALESCE(loading.nama_line, loading_bk.nama_line)";
            $additionalFilter .= " and output.line is not null and COALESCE(loading.nama_line, loading_bk.nama_line) is not null";
        }

        // Cross-line output
        $crossLineOutput = "";
        if ($request->crossline_output) {
            $crossLineOutput = " and output.line != output_packing.line";
            $additionalFilter .= " and output.line is not null and output_packing.line is not null";
        }

        // Missmatch
        $missmatchOutput = "";
        $missmatchDefect = "";
        $missmatchReject = "";
        if ($request->missmatch_code) {
            $missmatchOutput = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefect = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchReject = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output.kode_numbering is not null";
        }

        // Missmatch
        $missmatchOutputPck = "";
        $missmatchDefectPck = "";
        $missmatchRejectPck = "";
        if ($request->missmatch_code_packing) {
            $missmatchOutputPck = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefectPck = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchRejectPck = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output_packing.kode_numbering is not null";
        }

        // Backdate
        $backDateOutput = "";
        $backDateDefect = "";
        $backDateReject = "";
        if ($request->back_date) {
            $backDateOutput = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefect = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateReject = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output.tgl is not null";
        }

        // Backdate
        $backDateOutputPck = "";
        $backDateDefectPck = "";
        $backDateRejectPck = "";
        if ($request->back_date_packing) {
            $backDateOutputPck = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefectPck = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateRejectPck = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output_packing.tgl is not null";
        }

        $filterYs = $buyerFilterYs."
                    ".$wsFilterYs."
                    ".$styleFilterYs."
                    ".$styleFilterYs."
                    ".$sizeFilterYs."
                    ".$kodeFilterYs;

        $filterDefectOutput = $tglPlan."
                    ".$tglDefect."
                    ".$backDateDefect."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchDefect."
                    ".$backDateDefect;

        $filterRftOutput = $tglPlan."
                    ".$tglOutput."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$missmatchOutput."
                    ".$backDateOutput;

        $filterRejectOutput = $tglPlan."
                    ".$tglReject."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchReject."
                    ".$backDateReject;

        $filterDefectPck = $tglPlan."
                    ".$tglDefectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchDefectPck."
                    ".$backDateDefectPck;

        $filterRftPck = $tglPlan."
                    ".$tglOutputPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$missmatchOutputPck."
                    ".$backDateOutputPck;

        $filterRejectPck = $tglPlan."
                    ".$tglRejectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchRejectPck."
                    ".$backDateRejectPck;

        // Callback
        $callbackFilterYs = "";
        $callbackFilterOutput = "";
        if (!trim(str_replace("\n", "", $filterYs)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterRejectOutput)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterYs = " and DATE(ys.updated_at) > CURRENT_DATE()";
            $callbackFilterOutput = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $outputList ="
            select
                COALESCE(output.kode_numbering, output_packing.kode_numbering, id_year_sequence) kode,
                COALESCE(output.Supplier, ys.buyer) buyer,
                COALESCE(output.ws, ys.ws) ws,
                COALESCE(output.styleno, ys.styleno) style,
                COALESCE(output.color, ys.color) color,
                COALESCE(output.size, ys.size) size,
                COALESCE(stk.id_qr_stocker, stk_bk.id_qr_stocker) as stocker,
                COALESCE(loading.nama_line, loading_bk.nama_line) as line_loading,
                COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) as tanggal_loading,
                output.tgl_plan tanggal_plan,
                output.tgl tanggal_output,
                output.line line_output,
                output.status status_output,
                output.defect_type as defect_output,
                output.allocation as allocation_output,
                output_packing.tgl tanggal_output_packing,
                output_packing.line line_output_packing,
                output_packing.status status_output_packing,
                output_packing.defect_type as defect_output_packing,
                output_packing.allocation as allocation_output_packing
            from
                (
                    select
                        ys.*,
                        msb.buyer,
                        msb.ws,
                        msb.styleno,
                        msb.color
                    from
                        laravel_nds.year_sequence as ys
                        left join laravel_nds.master_sb_ws as msb on msb.id_so_det = ys.so_det_id
                    where
                        ys.id is not null
                        ".$filterYs."
                        ".$callbackFilterYs."
                ) as ys
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_defects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        ".$filterDefectOutput."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        ".$filterRftOutput."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join user_sb_wip on user_sb_wip.id = output_rejects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        ".$filterRejectOutput."
                        ".$callbackFilterOutput."
                ) output ON output.kode_numbering = ys.id_year_sequence
                left join (
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_defects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        kode_numbering,
                        UPPER(defect_status) as status,
                        CONCAT(UPPER(output_defects.defect_status), ' - ', output_defect_types.defect_type) defect_type,
                        output_defect_types.allocation
                    from
                        output_defects_packing as output_defects
                        left join master_plan on master_plan.id = output_defects.master_plan_id
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_defects.created_by
                        left join output_defect_types on output_defect_types.id = output_defects.defect_type_id
                    where
                        output_defects.id is not null
                        ".$filterDefectPck."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rfts.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rfts.kode_numbering,
                        'RFT' as status,
                        'RFT',
                        '-'
                    from
                        output_rfts_packing as output_rfts
                        left join master_plan on master_plan.id = output_rfts.master_plan_id
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rfts.created_by
                    where
                        output_rfts.id is not null
                        and output_rfts.status = 'NORMAL'
                        ".$filterRftPck."
                        ".$callbackFilterOutput."
                UNION ALL
                    select
                        mastersupplier.Supplier,
                        act_costing.kpno ws,
                        act_costing.styleno,
                        master_plan.tgl_plan,
                        DATE(output_rejects.updated_at) as tgl,
                        so_det.color,
                        so_det.size,
                        userpassword.username line,
                        output_rejects.kode_numbering,
                        'REJECT' as status,
                        CONCAT('REJECT - ', output_defect_types.defect_type),
                        output_defect_types.allocation
                    from
                        output_rejects_packing as output_rejects
                        left join master_plan on master_plan.id = output_rejects.master_plan_id
                        left join so_det on so_det.id = output_rejects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                        left join userpassword on userpassword.username = output_rejects.created_by
                        left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id
                    where
                        output_rejects.reject_status = 'mati'
                        ".$filterRejectPck."
                        ".$callbackFilterOutput."
                ) output_packing ON output_packing.kode_numbering = ys.id_year_sequence
            left join laravel_nds.stocker_input as stk on stk.id_qr_stocker = ys.id_qr_stocker
            left join laravel_nds.stocker_input as stk_bk on (stk_bk.form_cut_id = ys.form_cut_id and stk_bk.form_reject_id = ys.form_reject_id and stk_bk.form_piece_id = ys.form_piece_id) and stk_bk.so_det_id = ys.so_det_id and CAST(stk_bk.range_awal AS UNSIGNED) <= CAST(ys.number AS UNSIGNED) and CAST(stk_bk.range_akhir AS UNSIGNED) >= CAST(ys.number AS UNSIGNED)
            left join laravel_nds.loading_line as loading on loading.stocker_id = stk.id
            left join laravel_nds.loading_line as loading_bk on loading_bk.stocker_id = stk_bk.id
            WHERE
                ys.id is not null
                ".$tglLoading."
                ".$lineLoading."
                ".$statusOutput."
                ".$statusPacking."
                ".$crossLineLoading."
                ".$crossLineOutput."
                ".$additionalFilter."
        ";

        $buyer = $request->buyer;
        $ws = $request->ws;
        $style = $request->style;
        $color = $request->color;
        $size = $request->size;
        $kode = $request->kode;
        $tanggal_loading = $request->tanggal_loading;
        $line_loading = $request->line_loading;
        $tanggal_plan = $request->tanggal_plan;
        $tanggal_output = $request->tanggal_output;
        $tanggal_packing = $request->tanggal_packing;
        $line_output = $request->line_output;
        $status_output = $request->status_output;
        $defect_output = $request->defect_output;
        $allocation_output = $request->allocation_output;
        $line_packing = $request->line_packing;
        $status_packing = $request->status_packing;
        $defect_packing = $request->defect_packing;
        $allocation_packing = $request->allocation_packing;
        $crossline_loading = $request->crossline_loading;
        $crossline_output = $request->crossline_output;
        $missmatch_code = $request->missmatch_code;
        $missmatch_code_packing = $request->missmatch_code_packing;
        $back_date = $request->back_date;
        $back_date_packing = $request->back_date_packing;

        return Excel::download(new CheckOutputDetailListExport($outputList, $buyer, $ws, $style, $color, $size, $kode, $tanggal_loading, $line_loading, $tanggal_plan, $tanggal_output, $tanggal_packing, $line_output, $status_output, $defect_output, $allocation_output, $line_packing, $status_packing, $defect_packing, $allocation_packing, $crossline_loading, $crossline_output, $missmatch_code, $missmatch_code_packing, $back_date, $back_date_packing), 'Laporan Output Detail.xlsx');
    }

    public function undoOutput(Request $request) {
        return view("sewing.tools.undo-output", ["page" => "dashboard-sewing-eff"]);
    }

    public function undoOutputSubmit(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);

            $kodeNumberingList = YearSequence::selectRaw("
                year_sequence.id_year_sequence,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            whereRaw("year_sequence.id_year_sequence in (".$kodeNumbering.")")->
            get();

            if ($kodeNumberingList) {
                $kodeNumberingOutput = collect(
                    DB::connection("mysql_sb")->select("
                        SELECT output.*, userpassword.username as sewing_line FROM (
                            select created_by, kode_numbering, id, created_at, updated_at, 'rft' as status from output_rfts WHERE status = 'NORMAL' and kode_numbering in (".$kodeNumbering.")
                            UNION
                            select created_by, kode_numbering, id, created_at, updated_at, defect_status as status from output_defects WHERE kode_numbering in (".$kodeNumbering.")
                            UNION
                            select created_by, kode_numbering, id, created_at, updated_at, reject_status as status from output_rejects WHERE reject_status = 'mati' kode_numbering in (".$kodeNumbering.")
                        ) output
                        left join user_sb_wip on user_sb_wip.id = output.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    ")
                );

                foreach ($kodeNumberingOutput as $output) {
                    switch ($output->type) {
                        case 'rft' :
                            // Undo RFT
                            $rftSql = Rft::where('master_plan_id', $this->orderInfo->id)->
                                where('so_det_id', $this->undoSize)->
                                where('status', 'NORMAL')->
                                orderBy('updated_at', 'DESC')->
                                orderBy('created_at', 'DESC')->
                                take($this->undoQty);

                            $getRfts = $rftSql->get();

                            foreach ($getRfts as $getRft) {
                                $addUndoHistory = Undo::create([
                                    'master_plan_id' => $getRft->master_plan_id,
                                    'so_det_id' => $getRft->so_det_id,
                                    'output_rft_id' => $getRft->id,
                                    'kode_numbering' => $getRft->kode_numbering,
                                    'keterangan' => 'rft',
                                ]);
                            }

                            $deleteRft = $rftSql->delete();

                            if ($deleteRft)  {
                                $this->emit('updateOutputRft');

                                $this->emit('alert', 'success', 'Output RFT dengan ukuran '.$size[0]->size.' berhasil di UNDO sebanyak '.$deleteRft.' kali.');

                                $this->emit('hideModal', 'undo');
                            } else {
                                $this->emit('alert', 'error', 'Output RFT dengan ukuran '.$size[0]->size.' gagal di UNDO.');
                            }

                            break;
                        case 'defect' :
                            // Undo DEFECT
                            $defectQuery = Defect::selectRaw('output_defects.id as defect_id, output_defects.*')->
                                leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects.defect_area_id')->
                                leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects.defect_type_id')->
                                where('master_plan_id', $this->orderInfo->id)->
                                where('so_det_id', $this->undoSize)->
                                where('defect_status', 'defect');
                            if ($this->undoDefectType) {
                                $defectQuery->where('output_defects.defect_type_id', $this->undoDefectType);
                            };
                            if ($this->undoDefectArea) {
                                $defectQuery->where('output_defects.defect_area_id', $this->undoDefectArea);
                            };
                            $defectQuery->orderBy('output_defects.updated_at', 'DESC')->
                                orderBy('output_defects.created_at', 'DESC')->
                                take($this->undoQty);

                            $getDefects = $defectQuery->get();

                            foreach ($getDefects as $getDefect) {
                                $addUndoHistory = Undo::create([
                                    'master_plan_id' => $getDefect->master_plan_id,
                                    'so_det_id' => $getDefect->so_det_id,
                                    'output_defect_id' => $getDefect->defect_id,
                                    'kode_numbering' => $getDefect->kode_numbering,
                                    'keterangan' => 'defect',
                                ]);

                                $deleteDefect = Defect::find($getDefect->id)->delete();
                            }

                            $defectTypeText = $defectType ? ' dengan defect type = '.$defectType->defect_type : '';
                            $defectAreaText = $defectArea ? 'dengan defect area = '.$defectArea->defect_area.' ' : '';

                            if ($getDefects->count() > 0) {
                                $this->emit('updateOutputDefect');

                                $this->emit('alert', 'success', 'Output DEFECT dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'berhasil di UNDO sebanyak '.$getDefects->count().' kali.');

                                $this->emit('hideModal', 'undo');
                            } else {
                                $this->emit('alert', 'error', 'Output DEFECT dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'gagal di UNDO.');
                            }

                            break;
                        case 'reject' :
                            $defectIds = [];

                            // Undo REJECT
                            $rejectSql = Reject::where('master_plan_id', $this->orderInfo->id)->
                                where('so_det_id', $this->undoSize)->
                                orderBy('updated_at', 'DESC')->
                                orderBy('created_at', 'DESC')->
                                take($this->undoQty);

                            $getRejects = $rejectSql->get();

                            foreach ($getRejects as $reject) {
                                $addUndoHistory = Undo::create([
                                    'master_plan_id' => $reject->master_plan_id,
                                    'so_det_id' => $reject->so_det_id,
                                    'output_reject_id' => $reject->id,
                                    'kode_numbering' => $reject->kode_numbering,
                                    'keterangan' => 'reject',
                                ]);

                                array_push($defectIds, $reject->defect_id);
                            }

                            $deleteReject = $rejectSql->delete();

                            $updateDefect = Defect::whereIn('id', $defectIds)->update(['defect_status' => 'defect']);

                            if ($deleteReject) {
                                $this->emit('updateOutputReject');

                                $this->emit('alert', 'success', 'Output REJECT dengan ukuran '.$size[0]->size.' berhasil di UNDO sebanyak '.$deleteReject.' kali.');

                                $this->emit('hideModal', 'undo');
                            } else {
                                $this->emit('alert', 'error', 'Output REJECT dengan ukuran '.$size[0]->size.' gagal di UNDO.');
                            }

                            break;
                        case 'rework' :
                            // Undo REWORK
                            $defectQuery = Defect::selectRaw('output_defects.id as defect_id, output_defects.*')->
                                leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects.defect_area_id')->
                                leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects.defect_type_id')->
                                where('master_plan_id', $this->orderInfo->id)->
                                where('so_det_id', $this->undoSize)->
                                where('defect_status', 'reworked');
                            if ($this->undoDefectType) {
                                $defectQuery->where('output_defects.defect_type_id', $this->undoDefectType);
                            }
                            if ($this->undoDefectArea) {
                                $defectQuery->where('output_defects.defect_area_id', $this->undoDefectArea);
                            }
                            $getDefects = $defectQuery->orderBy('output_defects.updated_at', 'DESC')->
                                orderBy('output_defects.created_at', 'DESC')->
                                limit($this->undoQty)->
                                get();

                            // update defect & delete rework
                            foreach ($getDefects as $defect) {
                                Undo::create(['master_plan_id' => $defect->master_plan_id, 'so_det_id' => $defect->so_det_id, 'output_rework_id' => $defect->rework->id, 'kode_numbering' => $defect->kode_numbering, 'keterangan' => 'rework',]);
                                Defect::where('id', $defect->defect_id)->update(['defect_status' => 'defect']);
                                Rft::leftJoin('output_reworks', 'output_reworks.id', '=', 'output_rfts.rework_id')->where('output_reworks.defect_id', $defect->defect_id)->delete();
                                Rework::where('defect_id', $defect->defect_id)->delete();
                            }

                            $defectTypeText = $defectType ? ' dengan defect type = '.$defectType->defect_type : '';
                            $defectAreaText = $defectArea ? 'dengan defect area = '.$defectArea->defect_area.' ' : '';

                            if ($getDefects->count() > 0) {
                                $this->emit('updateOutputRework');

                                $this->emit('alert', 'success', 'Output REWORK dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'berhasil di UNDO sebanyak '.$getDefects->count().' kali.');

                                $this->emit('hideModal', 'undo');
                            } else {
                                $this->emit('alert', 'error', 'Output REWORK dengan ukuran '.$size[0]->size.''.$defectTypeText.' '.$defectAreaText.'gagal di UNDO.');
                            }

                            break;
                    }
                }
            }
        }
    }
}
