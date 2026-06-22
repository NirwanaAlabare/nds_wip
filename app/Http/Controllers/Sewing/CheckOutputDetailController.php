<?php

namespace App\Http\Controllers\Sewing;

use \avadim\FastExcelLaravel\Excel as FastExcel;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CheckOutputDetailController extends Controller
{
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

        return view("sewing.check-output-detail.index", ["buyers" => $buyers, "orders" => $orders, "lines" => $lines, "defectTypes" => $defectTypes, "page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-sewing", "subPage" => "check-output-detail",]);
    }

    public function checkOutputDetailList(Request $request) {
        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($request->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$request->buyer."'";
            $buyerFilterOutput = " and mastersupplier.Supplier = '".$request->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($request->ws) {
            $wsFilterYs = " and msb.id_act_cost = '".$request->ws."'";
            $wsFilterOutput = " and act_costing.id = '".$request->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->style) {
            $styleFilterYs = " and msb.styleno = '".$request->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$request->style."'";
        }

        $colorFilterYs = "";
        $colorFilterOutput = "";
        if ($request->color) {
            $colorFilterYs = " and msb.color = '".$request->color."'";
            $colorFilterOutput = " and so_det.color = '".$request->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($request->size && count($request->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $request->size));

            $sizeFilterYs = " and msb.id_so_det in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.id in (".$sizeList.")";
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
        if ($request->tanggal_loading_awal) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) >= '".$request->tanggal_loading_awal."'";
        }

        if ($request->tanggal_loading_akhir) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) <= '".$request->tanggal_loading_akhir."'";
        }

        $lineLoading = "";
        if ($request->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$request->line_loading."'";
        }

        $tglPlan = "";
        if ($request->tanggal_plan_awal || $request->tanggal_plan_akhir) {
            if ($request->tanggal_plan_awal) {
                $tglPlan .= " and master_plan.tgl_plan >= '".$request->tanggal_plan_awal."'";
            }
            if ($request->tanggal_plan_akhir) {
                $tglPlan .= " and master_plan.tgl_plan <= '".$request->tanggal_plan_akhir."'";
            }
            $additionalFilter .= " and output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($request->tanggal_output_awal || $request->tanggal_output_akhir) {
            $tglAwalOutput = $request->tanggal_output_awal ? $request->tanggal_output_awal : date("Y-m-d");
            $tglAkhirOutput = $request->tanggal_output_akhir ? $request->tanggal_output_akhir : date("Y-m-d");

            $tglOutput = " and output_rfts.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";

            $additionalFilter .= " and output.tgl is not null and output.tgl between '".$tglAwalOutput."' and '".$tglAkhirOutput."'";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($request->tanggal_packing_awal || $request->tanggal_packing_akhir) {
            $tglAwalPacking = $request->tanggal_packing_awal ? $request->tanggal_packing_awal : date("Y-m-d");
            $tglAkhirPacking = $request->tanggal_packing_akhir ? $request->tanggal_packing_akhir : date("Y-m-d");

            $tglOutputPck = " and output_rfts.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";

            $additionalFilter .= " and output_packing.tgl is not null and output_packing.tgl between '".$tglAwalPacking."' and '".$tglAkhirPacking."'";
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

            $defectOutput = " and output_defect_types.id in (".$defectList.")";
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
            $statusList = addQuotesAround(implode("\n", $request->status_packing));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($request->defect_packing && count($request->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_packing));

            $defectPacking = " and output_defect_types.id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($request->allocation_packing && count($request->allocation_packing) > 0) {
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
            $additionalFilter .= " and output.tgl is not null and (output.tgl_plan != output.tgl)";
        }

        // Backdate
        $backDateOutputPck = "";
        $backDateDefectPck = "";
        $backDateRejectPck = "";
        if ($request->back_date_packing) {
            $backDateOutputPck = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefectPck = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateRejectPck = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output_packing.tgl is not null and (output_packing.tgl_plan != output_packing.tgl)";
        }

        $filterYs = $buyerFilterYs."
                    ".$wsFilterYs."
                    ".$styleFilterYs."
                    ".$colorFilterYs."
                    ".$sizeFilterYs."
                    ".$kodeFilterYs;

        $filterDefectOutput = $tglPlan."
                    ".$tglDefect."
                    ".$backDateDefect."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchDefect."
                    ".$backDateDefect;

        $filterRftOutput = $tglPlan."
                    ".$tglOutput."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
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
                    ".$colorFilterOutput."
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
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$lineOutput."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchDefectPck."
                    ".$backDateDefectPck;

        $filterRftPck = $tglPlan."
                    ".$tglOutputPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$lineOutput."
                    ".$missmatchOutputPck."
                    ".$backDateOutputPck;

        $filterRejectPck = $tglPlan."
                    ".$tglRejectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$linePacking."
                    ".$lineOutput."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchRejectPck."
                    ".$backDateRejectPck;

        // Callback
        $callbackFilterYs = "";
        if (!trim(str_replace("\n", "", $filterYs)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterRejectOutput)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterYs = " and DATE(ys.updated_at) > CURRENT_DATE()";
        }

        $callbackFilterOutput = "";
        if (!trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRejectOutput))) {
            $callbackFilterOutput = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $callbackFilterPacking = "";
        if (!trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterPacking = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        ini_set("max_execution_time", 3600);

        $outputList = DB::connection("mysql_sb")->table(DB::raw("
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
            "))->selectRaw("
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
            ")->leftJoin(DB::raw("(
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
                        {$filterDefectOutput}
                        {$callbackFilterOutput}
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
                        AND output_rfts.status = 'NORMAL'
                        {$filterRftOutput}
                        {$callbackFilterOutput}
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
                        {$filterRejectOutput}
                        {$callbackFilterOutput}
                ) output
            "), "output.kode_numbering", "=", "ys.id_year_sequence")->
            leftJoin(DB::raw("(
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
                        {$filterDefectPck}
                        {$callbackFilterPacking}
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
                        {$filterRftPck}
                        {$callbackFilterPacking}
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
                        {$filterRejectPck}
                        {$callbackFilterPacking}
                ) output_packing
            "), "output_packing.kode_numbering", "=", "output.kode_numbering")->
            leftJoin(DB::raw("laravel_nds.stocker_input as stk"), "stk.id_qr_stocker", "=", "ys.id_qr_stocker")->
            leftJoin(DB::raw("laravel_nds.stocker_input as stk_bk"), function ($join) {
                $join->on("stk_bk.form_cut_id", "=", "ys.form_cut_id");
                $join->on("stk_bk.form_reject_id", "=", "ys.form_reject_id");
                $join->on("stk_bk.form_piece_id", "=", "ys.form_piece_id");
                $join->on("stk_bk.so_det_id", "=", "ys.so_det_id");
                $join->on(DB::raw("CAST(stk_bk.range_awal AS UNSIGNED)"), "<=", DB::raw("CAST(ys.number AS UNSIGNED)"));
                $join->on(DB::raw("CAST(stk_bk.range_akhir AS UNSIGNED)"), ">=", DB::raw("CAST(ys.number AS UNSIGNED)"));
            })->
            leftJoin(DB::raw("laravel_nds.loading_line as loading"), "loading.stocker_id", "=", "stk.id")->
            leftJoin(DB::raw("laravel_nds.loading_line as loading_bk"), "loading_bk.stocker_id", "=", "stk_bk.id")->
            whereRaw("
                ys.id is not null
                ".$tglLoading."
                ".$lineLoading."
                ".$statusOutput."
                ".$statusPacking."
                ".$crossLineLoading."
                ".$crossLineOutput."
                ".$crossLineOutput."
                ".$additionalFilter."
            ");

        return Datatables::queryBuilder($outputList)->toJson();
    }

    public function checkOutputDetailExport(Request $request) {
        ini_set("max_execution_time", 3600000);
        ini_set('memory_limit', '5120000M');

        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($request->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$request->buyer."'";
            $buyerFilterOutput = " and mastersupplier.Supplier = '".$request->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($request->ws) {
            $wsFilterYs = " and msb.id_act_cost = '".$request->ws."'";
            $wsFilterOutput = " and act_costing.id = '".$request->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($request->style) {
            $styleFilterYs = " and msb.styleno = '".$request->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$request->style."'";
        }

        $colorFilterYs = "";
        $colorFilterOutput = "";
        if ($request->color) {
            $colorFilterYs = " and msb.color = '".$request->color."'";
            $colorFilterOutput = " and so_det.color = '".$request->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($request->size && count($request->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $request->size));

            $sizeFilterYs = " and msb.id_so_det in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.id in (".$sizeList.")";
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
        if ($request->tanggal_loading_awal) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) >= '".$request->tanggal_loading_awal."'";
        }

        if ($request->tanggal_loading_akhir) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading)<= '".$request->tanggal_loading_akhir."'";
        }

        $lineLoading = "";
        if ($request->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$request->line_loading."'";
        }

        $tglPlan = "";
        if ($request->tanggal_plan_awal || $request->tanggal_plan_akhir) {
            if ($request->tanggal_plan_awal) {
                $tglPlan .= " and master_plan.tgl_plan >= '".$request->tanggal_plan_awal."'";
            }
            if ($request->tanggal_plan_akhir) {
                $tglPlan .= " and master_plan.tgl_plan <= '".$request->tanggal_plan_akhir."'";
            }
            $additionalFilter .= "output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($request->tanggal_output_awal || $request->tanggal_output_akhir) {
            $tglAwalOutput = $request->tanggal_output_awal ? $request->tanggal_output_awal : date("Y-m-d");
            $tglAkhirOutput = $request->tanggal_output_akhir ? $request->tanggal_output_akhir : date("Y-m-d");

            $tglOutput = " and output_rfts.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";

            $additionalFilter .= " and output.tgl is not null";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($request->tanggal_packing_awal || $request->tanggal_packing_akhir) {
            $tglAwalPacking = $request->tanggal_packing_awal ? $request->tanggal_packing_awal : date("Y-m-d");
            $tglAkhirPacking = $request->tanggal_packing_akhir ? $request->tanggal_packing_akhir : date("Y-m-d");

            $tglOutputPck = " and output_rfts.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";

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

            $defectOutput = " and output_defect_types.id in (".$defectList.")";
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
            $statusList = addQuotesAround(implode("\n", $request->status_packing));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($request->defect_packing && count($request->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $request->defect_packing));

            $defectPacking = " and output_defect_types.id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($request->allocation_packing && count($request->allocation_packing) > 0) {
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
                    ".$colorFilterYs."
                    ".$sizeFilterYs."
                    ".$kodeFilterYs;

        $filterDefectOutput = $tglPlan."
                    ".$tglDefect."
                    ".$backDateDefect."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$defectOutput."
                    ".$allocationOutput."
                    ".$missmatchDefect."
                    ".$backDateDefect;

        $filterRftOutput = $tglPlan."
                    ".$tglOutput."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
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
                    ".$colorFilterOutput."
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
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
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
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$linePacking."
                    ".$missmatchOutputPck."
                    ".$backDateOutputPck;

        $filterRejectPck = $tglPlan."
                    ".$tglRejectPck."
                    ".$buyerFilterOutput."
                    ".$wsFilterOutput."
                    ".$styleFilterOutput."
                    ".$colorFilterOutput."
                    ".$sizeFilterOutput."
                    ".$kodeFilterOutput."
                    ".$lineOutput."
                    ".$linePacking."
                    ".$defectPacking."
                    ".$allocationPacking."
                    ".$missmatchRejectPck."
                    ".$backDateRejectPck;

        // Callback
        $callbackFilterYs = "";
        if (!trim(str_replace("\n", "", $filterYs)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterRejectOutput)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterYs = " and DATE(ys.updated_at) > CURRENT_DATE()";
        }

        $callbackFilterOutput = "";
        if (!trim(str_replace("\n", "", $filterRftOutput)) && !trim(str_replace("\n", "", $filterDefectOutput)) && !trim(str_replace("\n", "", $filterRejectOutput))) {
            $callbackFilterOutput = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $callbackFilterPacking = "";
        if (!trim(str_replace("\n", "", $filterRftPck)) && !trim(str_replace("\n", "", $filterDefectPck)) && !trim(str_replace("\n", "", $filterRejectPck))) {
            $callbackFilterPacking = " and master_plan.tgl_plan > CURRENT_DATE()";
        }

        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        $area = $sheet->beginArea();

        $sheet->writeTo('A1', 'Check Output Detail', ['font-size' => 16]);
        $sheet->mergeCells('A1:S1');

        $sheet->writeTo('A2', "Kode")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', "Buyer")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', "WS")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', "Style")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', "Color")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', "Size")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', "Tanggal Loading")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', "Line Loading")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', "Tanggal Plan")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', "Tanggal Sewing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', "Line Sewing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L2', "Status Sewing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', "Defect Sewing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', "Alokasi Sewing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', "Tanggal Finishing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P2', "Line Finishing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q2', "Status Finishing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R2', "Defect Finishing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S2', "Alokasi Finishing")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        DB::connection("mysql_sb")->table(DB::raw("
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
            "))->selectRaw("
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
            ")->leftJoin(DB::raw("(
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
                        {$filterDefectOutput}
                        {$callbackFilterOutput}
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
                        AND output_rfts.status = 'NORMAL'
                        {$filterRftOutput}
                        {$callbackFilterOutput}
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
                        {$filterRejectOutput}
                        {$callbackFilterOutput}
                ) output
            "), "output.kode_numbering", "=", "ys.id_year_sequence")->
            leftJoin(DB::raw("(
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
                        {$filterDefectPck}
                        {$callbackFilterPacking}
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
                        {$filterRftPck}
                        {$callbackFilterPacking}
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
                        {$filterRejectPck}
                        {$callbackFilterPacking}
                ) output_packing
            "), "output_packing.kode_numbering", "=", "output.kode_numbering")->
            leftJoin(DB::raw("laravel_nds.stocker_input as stk"), "stk.id_qr_stocker", "=", "ys.id_qr_stocker")->
            leftJoin(DB::raw("laravel_nds.stocker_input as stk_bk"), function ($join) {
                $join->on("stk_bk.form_cut_id", "=", "ys.form_cut_id");
                $join->on("stk_bk.form_reject_id", "=", "ys.form_reject_id");
                $join->on("stk_bk.form_piece_id", "=", "ys.form_piece_id");
                $join->on("stk_bk.so_det_id", "=", "ys.so_det_id");
                $join->on(DB::raw("CAST(stk_bk.range_awal AS UNSIGNED)"), "<=", DB::raw("CAST(ys.number AS UNSIGNED)"));
                $join->on(DB::raw("CAST(stk_bk.range_akhir AS UNSIGNED)"), ">=", DB::raw("CAST(ys.number AS UNSIGNED)"));
            })->
            leftJoin(DB::raw("laravel_nds.loading_line as loading"), "loading.stocker_id", "=", "stk.id")->
            leftJoin(DB::raw("laravel_nds.loading_line as loading_bk"), "loading_bk.stocker_id", "=", "stk_bk.id")->
            orderBy("ys.id_year_sequence")->
            chunk(100000, function ($rows) use ($sheet) {
                $sheet->writeAreas();

                foreach ($rows as $row) {
                    $rowArr = [
                        $row->kode ?? "-",
                        $row->buyer ?? "-",
                        $row->ws ?? "-",
                        $row->style ?? "-",
                        $row->color ?? "-",
                        $row->size ?? "-",
                        $row->tanggal_loading ?? "-",
                        $row->line_loading ?? "-",
                        $row->tanggal_plan ?? "-",
                        $row->tanggal_output ?? "-",
                        $row->line_output ?? "-",
                        $row->status_output ?? "-",
                        $row->defect_output ?? "-",
                        $row->allocation_output ?? "-",
                        $row->tanggal_output_packing ?? "-",
                        $row->line_output_packing ?? "-",
                        $row->status_output_packing ?? "-",
                        $row->defect_output_packing ?? "-",
                        $row->allocation_output_packing ?? "-",
                    ];

                    $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
            });

        $filename = date('Y-m-d') . ' Check Output Detail.xlsx';

        return $excel->download($filename);
    }
}