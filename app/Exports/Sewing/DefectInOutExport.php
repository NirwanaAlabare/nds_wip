<?php

namespace App\Exports;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class DefectInOutExport implements FromView, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $type;

    function __construct($dateFrom, $dateTo, $type) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->type = $type;
    }

    public function view(): View
    {
        $defectInOutList = DefectInOut::selectRaw("
                output_defect_in_out.updated_at,
                userpassword.FullName,
                output_defect_in_out.output_type,
                act_costing.kpno,
                act_costing.styleno,
                so_det.color,
                so_det.size,
                output_defect_types.defect_type,
                COUNT(output_defect_in_out.id) defect_qty
            ")->
            leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
            leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            where("output_defect_in_out.type", strtolower($this->type))->
            whereBetween("output_defect_in_out.updated_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
            groupBy("output_defect_in_out.updated_at", "output_defects.so_det_id")->
            orderBy("output_defect_in_out.updated_at", "desc")->
            get();

        return view('sewing.export.output-export', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'type' => $this->type,
            'defectInOutList' => $defectInOutList
        ]);
    }
}
