<?php

namespace App\Exports\Sewing;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class CheckOutputDetailListExport implements FromQuery, WithMapping, WithChunkReading, ShouldAutoSize
{
    protected $buyer;
    protected $ws;
    protected $style;
    protected $color;
    protected $size;
    protected $kode;
    protected $tanggal_loading_awal;
    protected $tanggal_loading_akhir;
    protected $line_loading;
    protected $tanggal_plan_awal;
    protected $tanggal_plan_akhir;
    protected $tanggal_output_awal;
    protected $tanggal_output_akhir;
    protected $tanggal_packing_awal;
    protected $tanggal_packing_akhir;
    protected $line_output;
    protected $status_output;
    protected $defect_output;
    protected $allocation_output;
    protected $line_packing;
    protected $status_packing;
    protected $defect_packing;
    protected $allocation_packing;
    protected $crossline_loading;
    protected $crossline_output;
    protected $missmatch_code;
    protected $missmatch_code_packing;
    protected $back_date;
    protected $back_date_packing;
    function __construct($buyer, $ws, $style, $color, $size, $kode, $tanggal_loading_awal, $tanggal_loading_akhir, $line_loading, $tanggal_plan_awal, $tanggal_plan_akhir, $tanggal_output_awal, $tanggal_output_akhir, $tanggal_packing_awal, $tanggal_packing_akhir, $line_output, $status_output, $defect_output, $allocation_output, $line_packing, $status_packing, $defect_packing, $allocation_packing, $crossline_loading, $crossline_output, $missmatch_code, $missmatch_code_packing, $back_date, $back_date_packing) {
        $this->buyer = $buyer;
        $this->ws = $ws;
        $this->style = $style;
        $this->color = $color;
        $this->size = $size;
        $this->kode = $kode;
        $this->tanggal_loading_awal = $tanggal_loading_awal;
        $this->tanggal_loading_akhir = $tanggal_loading_akhir;
        $this->line_loading = $line_loading;
        $this->tanggal_plan_awal = $tanggal_plan_awal;
        $this->tanggal_plan_akhir = $tanggal_plan_akhir;
        $this->tanggal_output_awal = $tanggal_output_awal;
        $this->tanggal_output_akhir = $tanggal_output_akhir;
        $this->tanggal_packing_awal = $tanggal_packing_awal;
        $this->tanggal_packing_akhir = $tanggal_packing_akhir;
        $this->line_output = $line_output;
        $this->status_output = $status_output;
        $this->defect_output = $defect_output;
        $this->allocation_output = $allocation_output;
        $this->line_packing = $line_packing;
        $this->status_packing = $status_packing;
        $this->defect_packing = $defect_packing;
        $this->allocation_packing = $allocation_packing;
        $this->crossline_loading = $crossline_loading;
        $this->crossline_output = $crossline_output;
        $this->missmatch_code = $missmatch_code;
        $this->missmatch_code_packing = $missmatch_code_packing;
        $this->back_date = $back_date;
        $this->back_date_packing = $back_date_packing;
    }
    public function query()
    {
        $buyerFilterYs = "";
        $buyerFilterOutput = "";
        if ($this->buyer) {
            $buyerFilterYs = " and msb.buyer = '".$this->buyer."'";
            $buyerFilterOutput = " and mastersupplier.Supplier = '".$this->buyer."'";
        }

        $wsFilterYs = "";
        $wsFilterOutput = "";
        if ($this->ws) {
            $wsFilterYs = " and msb.id_act_cost = '".$this->ws."'";
            $wsFilterOutput = " and act_costing.id = '".$this->ws."'";
        }

        $styleFilterYs = "";
        $styleFilterOutput = "";
        if ($this->style) {
            $styleFilterYs = " and msb.styleno = '".$this->style."'";
            $styleFilterOutput = " and act_costing.styleno = '".$this->style."'";
        }

        $colorFilterYs = "";
        $colorFilterOutput = "";
        if ($this->color) {
            $colorFilterYs = " and msb.color = '".$this->color."'";
            $colorFilterOutput = " and so_det.color = '".$this->color."'";
        }

        $sizeFilterYs = "";
        $sizeFilterOutput = "";
        if ($this->size && count($this->size) > 0) {
            $sizeList = addQuotesAround(implode("\n", $this->size));

            $sizeFilterYs = " and msb.id_so_det in (".$sizeList.")";
            $sizeFilterOutput = " and so_det.id in (".$sizeList.")";
        }

        $kodeFilterYs = "";
        $kodeFilterOutput = "";
        if ($this->kode && strlen($this->kode) > 0) {
            $kodeList = addQuotesAround($this->kode);

            $kodeFilterYs = " and ys.id_year_sequence in (".$kodeList.")";
            $kodeFilterOutput = " and kode_numbering in (".$kodeList.")";
        }

        $additionalFilter = "";

        $tglLoading = "";
        if ($this->tanggal_loading_awal) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading) >= '".$this->tanggal_loading_awal."'";
        }

        if ($this->tanggal_loading_akhir) {
            $tglLoading .= " and COALESCE(loading.tanggal_loading, loading_bk.tanggal_loading)<= '".$this->tanggal_loading_akhir."'";
        }

        $lineLoading = "";
        if ($this->line_loading) {
            $lineLoading = " and COALESCE(loading.nama_line, loading_bk.nama_line) = '".$this->line_loading."'";
        }

        $tglPlan = "";
        if ($this->tanggal_plan_awal || $this->tanggal_plan_akhir) {
            if ($this->tanggal_plan_awal) {
                $tglPlan .= " and master_plan.tgl_plan >= '".$this->tanggal_plan_awal."'";
            }
            if ($this->tanggal_plan_akhir) {
                $tglPlan .= " and master_plan.tgl_plan <= '".$this->tanggal_plan_akhir."'";
            }
            $additionalFilter .= "output.kode_numbering is not null";
        }

        // Sewing/Packing
        $tglOutput = "";
        $tglDefect = "";
        $tglReject = "";
        if ($this->tanggal_output_awal || $this->tanggal_output_akhir) {
            $tglAwalOutput = $this->tanggal_output_awal ? $this->tanggal_output_awal : date("Y-m-d");
            $tglAkhirOutput = $this->tanggal_output_akhir ? $this->tanggal_output_akhir : date("Y-m-d");

            $tglOutput = " and output_rfts.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglDefect = " and output_defects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";
            $tglReject = " and output_rejects.updated_at between '".$tglAwalOutput." 00:00:00' and '".$tglAkhirOutput." 23:59:59'";

            $additionalFilter .= " and output.tgl is not null";
        }

        $tglOutputPck = "";
        $tglDefectPck = "";
        $tglRejectPck = "";
        if ($this->tanggal_packing_awal || $this->tanggal_packing_akhir) {
            $tglAwalPacking = $this->tanggal_packing_awal ? $this->tanggal_packing_awal : date("Y-m-d");
            $tglAkhirPacking = $this->tanggal_packing_akhir ? $this->tanggal_packing_akhir : date("Y-m-d");

            $tglOutputPck = " and output_rfts.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglDefectPck = " and output_defects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";
            $tglRejectPck = " and output_rejects.updated_at between '".$tglAwalPacking." 00:00:00' and '".$tglAkhirPacking." 23:59:59'";

            $additionalFilter .= " and output_packing.tgl is not null";
        }

        // Sewing
        $lineOutput = "";
        if ($this->line_output) {
            $lineOutput = " and userpassword.username = '".$this->line_output."'";
            $additionalFilter .= " and output.line is not null";
        }

        $statusOutput = "";
        if ($this->status_output && count($this->status_output) > 0) {
            $statusList = addQuotesAround(implode("\n", $this->status_output));

            $statusOutput = " and output.status in (".$statusList.")";
        }

        $defectOutput = "";
        if ($this->defect_output && count($this->defect_output) > 0) {
            $defectList = addQuotesAround(implode("\n", $this->defect_output));

            $defectOutput = " and output_defect_types.id in (".$defectList.")";
            $additionalFilter .= " and output.defect_type is not null";
        }

        $allocationOutput = "";
        if ($this->allocation_output && count($this->allocation_output) > 0) {
            $allocationList = addQuotesAround(implode("\n", $this->allocation_output));

            $allocationOutput = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output.allocation is not null";
        }

        // Packing
        $linePacking = "";
        if ($this->line_packing) {
            $linePacking = " and userpassword.username = '".$this->line_packing."'";
            $additionalFilter .= " and output_packing.line is not null";
        }

        $statusPacking = "";
        if ($this->status_packing && count($this->status_packing) > 0) {
            $statusList = addQuotesAround(implode("\n", $this->status_packing));

            $statusPacking = " and output_packing.status in (".$statusList.")";
        }

        $defectPacking = "";
        if ($this->defect_packing && count($this->defect_packing) > 0) {
            $defectList = addQuotesAround(implode("\n", $this->defect_packing));

            $defectPacking = " and output_defect_types.id in (".$defectList.")";
            $additionalFilter .= " and output_packing.defect_type is not null";
        }

        $allocationPacking = "";
        if ($this->allocation_packing && count($this->allocation_packing) > 0) {
            $allocationList = addQuotesAround(implode("\n", $this->allocation_packing));

            $allocationPacking = " and output_defect_types.allocation in (".$allocationList.")";
            $additionalFilter .= " and output_packing.allocation is not null";
        }

        // Cross-line loading
        $crossLineLoading = "";
        if ($this->crossline_loading) {
            $crossLineLoading = " and output.line != COALESCE(loading.nama_line, loading_bk.nama_line)";
            $additionalFilter .= " and output.line is not null and COALESCE(loading.nama_line, loading_bk.nama_line) is not null";
        }

        // Cross-line output
        $crossLineOutput = "";
        if ($this->crossline_output) {
            $crossLineOutput = " and output.line != output_packing.line";
            $additionalFilter .= " and output.line is not null and output_packing.line is not null";
        }

        // Missmatch
        $missmatchOutput = "";
        $missmatchDefect = "";
        $missmatchReject = "";
        if ($this->missmatch_code) {
            $missmatchOutput = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefect = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchReject = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output.kode_numbering is not null";
        }

        // Missmatch
        $missmatchOutputPck = "";
        $missmatchDefectPck = "";
        $missmatchRejectPck = "";
        if ($this->missmatch_code_packing) {
            $missmatchOutputPck = " and output_rfts.kode_numbering != output_rfts.no_cut_size";
            $missmatchDefectPck = " and output_defects.kode_numbering != output_defects.no_cut_size";
            $missmatchRejectPck = " and output_rejects.kode_numbering != output_rejects.no_cut_size";
            $additionalFilter .= " and output_packing.kode_numbering is not null";
        }

        // Backdate
        $backDateOutput = "";
        $backDateDefect = "";
        $backDateReject = "";
        if ($this->back_date) {
            $backDateOutput = " and DATE(output_rfts.updated_at) != master_plan.tgl_plan";
            $backDateDefect = " and DATE(output_defects.updated_at) != master_plan.tgl_plan";
            $backDateReject = " and DATE(output_rejects.updated_at) != master_plan.tgl_plan";
            $additionalFilter .= " and output.tgl is not null";
        }

        // Backdate
        $backDateOutputPck = "";
        $backDateDefectPck = "";
        $backDateRejectPck = "";
        if ($this->back_date_packing) {
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

        ini_set("max_execution_time", 3600);

        return DB::connection("mysql_sb")->table(DB::raw("
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
            orderByRaw("
                COALESCE(output.ws, ys.ws),
                COALESCE(output.styleno, ys.styleno),
                COALESCE(output.color, ys.color),
                COALESCE(output.size, ys.size),
                ys.id_year_sequence
            ");
    }

    public function map($row): array
    {
        return [
            $row->kode,
            $row->buyer,
            $row->ws,
            $row->style,
            $row->color,
            $row->size,
            $row->tanggal_loading,
            $row->line_loading,
            $row->tanggal_plan,
            $row->tanggal_output,
            $row->line_output,
            $row->status_output,
            $row->defect_output,
            $row->allocation_output,
            $row->tanggal_output_packing,
            $row->line_output_packing,
            $row->status_output_packing,
            $row->defect_output_packing,
            $row->allocation_output_packing,
        ];
    }
    public function headings(): array
    {
        return [
            "Kode",
            "Buyer",
            "WS",
            "Style",
            "Color",
            "Size",
            "Tanggal Loading",
            "Line Loading",
            "Tanggal Plan",
            "Tanggal Sewing",
            "Line Sewing",
            "Status Sewing",
            "Defect Sewing",
            "Alokasi Sewing",
            "Tanggal Finishing",
            "Line Finishing",
            "Status Finishing",
            "Defect Finishing",
            "Alokasi Finishing",
        ];
    }

    public function chunkSize(): int
    {
        return 25000; // fetch 25000 rows at a time
    }
}
