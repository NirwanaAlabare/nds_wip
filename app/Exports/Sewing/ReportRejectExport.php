<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use App\Models\SignalBit\RejectIn;
use DB;

class ReportRejectExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $query;
    protected $dateFrom;
    protected $dateTo;

    protected $defect_types;
    protected $base_ws;
    protected $department;
    protected $defect_status;
    protected $sewing_line;
    protected $buyer;
    protected $ws;
    protected $style;
    protected $color;
    protected $size;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $defect_types, $base_ws, $department, $defect_status, $sewing_line, $buyer, $ws, $style, $color, $size) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->defect_types = $defect_types;
        $this->base_ws = $base_ws;
        $this->department = $department;
        $this->defect_status = $defect_status;
        $this->sewing_line = $sewing_line;
        $this->buyer = $buyer;
        $this->ws = $ws;
        $this->style = $style;
        $this->color = $color;
        $this->size = $size;
    }

    public function title(): string
    {
        return 'ReportReject';
    }

    public function view(): View
    {
        $dateFrom = $this->dateFrom ? $this->dateFrom : date("Y-m-d");
        $dateTo = $this->dateTo ? $this->dateTo : date("Y-m-d");

        $defectTypeFilters = "";
        if ($this->defect_types) {
            $defectTypeFilters = "AND output_defect_types.id in (".$this->defect_types.")";
        }

        $reject = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                output_reject_in.output_type,
                output_reject_in.kode_numbering,
                userpassword.username as sewing_line,
                mastersupplier.Supplier as buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                reject_detail.defect_types_check,
                reject_detail.defect_areas_check,
                COUNT(output_reject_in.id) total_reject
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            join(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id where output_reject_in_detail.id is not null ".$defectTypeFilters." group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            // Conditional
            whereBetween("output_reject_in.created_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            whereRaw("
                (
                    output_reject_in.id IS NOT NULL AND
                    output_reject_in.status = 'rejected'
                    ".($this->base_ws ? "AND act_costing.kpno = '".$this->base_ws."'" : "")."
                    ".($this->department && $this->department != "all" ? "AND output_reject_in.output_type = '".$this->department."'" : "")."
                )
            ")->
            groupByRaw("DATE(output_reject_in.created_at), output_reject_in.id");

        if ($this->defect_status && count($this->defect_status) > 0) {
            $reject->whereIn("output_reject_in.status", $this->defect_status);
        }

        if ($this->sewing_line && count($this->sewing_line) > 0) {
            $reject->whereIn("master_plan.sewing_line", $this->sewing_line);
        }

        if ($this->buyer && count($this->buyer) > 0) {
            $reject->whereIn("mastersupplier.Supplier", $this->buyer);
        }

        if ($this->ws && count($this->ws) > 0) {
            $reject->whereIn("act_costing.kpno", $this->ws);
        }

        if ($this->style && count($this->style) > 0) {
            $reject->whereIn("act_costing.styleno", $this->style);
        }

        if ($this->color && count($this->color) > 0) {
            $reject->whereIn("so_det.color", $this->color);
        }

        if ($this->size && count($this->size) > 0) {
            $reject->whereIn("so_det.size", $this->size);
        }

        $rejectData = $reject->get();

        $this->rowCount = $rejectData->count();

        return view('sewing.export.report-reject-export', [
            'rowCount' => $this->rowCount,
            'rejectData' => $rejectData,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'defect_types' => $this->defect_types,
            'base_ws' => $this->base_ws,
            'department' => $this->department,
            'defect_status' => $this->defect_status,
            'sewing_line' => $this->sewing_line,
            'buyer' => $this->buyer,
            'ws' => $this->ws,
            'style' => $this->style,
            'color' => $this->color,
            'size' => $this->size
        ]);
    }
}
