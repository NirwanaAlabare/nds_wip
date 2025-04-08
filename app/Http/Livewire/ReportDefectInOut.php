<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserDefect;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\DefectInOut;
use App\Models\SignalBit\OutputFinishing;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use DB;

class ReportDefectInOut extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $dateFrom;
    public $dateTo;

    public $defectTypes;
    public $selectedDefectType;
    public $selectedOutputType;

    public $defectInOutShowPage;

    public $loading;

    public function mount()
    {
        $this->dateFrom = date('Y-m-d');
        $this->dateTo = date('Y-m-d');

        $this->defectTypes = UserDefect::whereIn('Groupp', ['MENDING', 'SPOTCLEANING'])->whereRaw("(Locked != '1' OR Locked IS NULL)")->orderBy('FullName', 'asc')->get();

        $this->selectedDefectType = $this->defectTypes->count() > 1 ? $this->defectTypes->first()->Groupp : '';
        $this->selectedOutputType = "qc";

        $this->defectInOutShowPage = 10;

        $this->loading = false;
    }

    public function render()
    {
        $this->loadingLine = false;

        if ($this->selectedOutputType == 'packing') {
            $outputAll = collect(DB::connection("mysql_sb")->select("
                SELECT
                    line,
                    style,
                    SUM(total_output) total_output
                FROM (
                    select
                        userpassword.FullName as line,
                        act_costing.styleno as style,
                        COUNT(*) total_output
                    from
                        output_rfts_packing
                        left join so_det on so_det.id = output_rfts_packing.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join userpassword on userpassword.username = output_rfts_packing.created_by
                    where
                        output_rfts_packing.updated_at between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'
                    group by
                        output_rfts_packing.created_by,
                        act_costing.styleno
                    union
                    select
                        userpassword.FullName as line,
                        act_costing.styleno as style,
                        COUNT(*) total_output
                    from
                        output_defects_packing
                        left join so_det on so_det.id = output_defects_packing.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join userpassword on userpassword.username = output_defects_packing.created_by
                        left join output_defect_in_out on output_defect_in_out.defect_id = output_defects_packing.id and output_defect_in_out.output_type = 'packing'
                    where
                        (
                            output_defects_packing.updated_at between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59' OR
                            output_defect_in_out.updated_at between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'
                        ) and
                        output_defects_packing.defect_status = 'defect'
                    group by
                        output_defects_packing.created_by,
                        act_costing.styleno
                ) output
                GROUP BY
                    line,
                    style
            "));

            $defectInOutQuery = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
                    userpassword.username,
                    userpassword.FullName,
                    output_defect_in_out.output_type,
                    act_costing.kpno,
                    act_costing.styleno,
                    so_det.color,
                    so_det.size,
                    output_defect_types.defect_type,
                    COUNT(output_defect_in_out.id) defect_qty
                ")->
                leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
                whereNotNull("output_defects_packing.id")->
                where("output_defect_in_out.type", strtolower($this->selectedDefectType))->
                where("output_defect_in_out.output_type", strtolower($this->selectedOutputType))->
                whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
                groupBy("output_defect_in_out.created_at", "output_defects_packing.so_det_id");
        } else if ($this->selectedOutputType == 'qcf') {
            $defectInOutQuery = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
                    userpassword.username,
                    userpassword.FullName,
                    output_defect_in_out.output_type,
                    act_costing.kpno,
                    act_costing.styleno,
                    so_det.color,
                    so_det.size,
                    output_defect_types.defect_type,
                    COUNT(output_defect_in_out.id) defect_qty
                ")->
                leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("userpassword", "userpassword.username", "=", "output_check_finishing.created_by")->
                whereNotNull("output_check_finishing.id")->
                where("output_defect_in_out.type", strtolower($this->selectedDefectType))->
                where("output_defect_in_out.output_type", strtolower($this->selectedOutputType))->
                whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
                groupBy("output_defect_in_out.created_at", "output_check_finishing.so_det_id");
        } else {
            $outputAll = collect(DB::connection("mysql_sb")->select("
                SELECT
                    line,
                    style,
                    SUM(total_output) total_output
                FROM (
                    select
                        userpassword.FullName as line,
                        act_costing.styleno as style,
                        COUNT(*) total_output
                    from
                        output_rfts
                        left join so_det on so_det.id = output_rfts.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where
                        output_rfts.updated_at between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'
                    group by
                        userpassword.username,
                        act_costing.styleno
                    union
                    select
                        userpassword.FullName as line,
                        act_costing.styleno as style,
                        COUNT(*) total_output
                    from
                        output_defects
                        left join so_det on so_det.id = output_defects.so_det_id
                        left join so on so.id = so_det.id_so
                        left join act_costing on act_costing.id = so.id_cost
                        left join user_sb_wip on user_sb_wip.id = output_defects.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        left join output_defect_in_out on output_defect_in_out.defect_id = output_defects.id and output_defect_in_out.output_type = 'qc'
                    where
                        (
                            output_defects.updated_at between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59' OR
                            output_defect_in_out.updated_at between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'
                        ) and
                        output_defects.defect_status = 'defect'
                    group by
                        output_defects.created_by,
                        act_costing.styleno
                ) output
                GROUP BY
                    line,
                    style
            "));

            $defectInOutQuery = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
                    userpassword.username,
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
                whereNotNull("output_defects.id")->
                where("output_defect_in_out.type", strtolower($this->selectedDefectType))->
                where("output_defect_in_out.output_type", strtolower($this->selectedOutputType))->
                whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
                groupBy("output_defect_in_out.created_at", "output_defects.so_det_id");
        }

        $defectInOutTotalQty = $defectInOutQuery->get()->sum("defect_qty");

        $defectInOutList = $defectInOutQuery->
            orderBy("output_defect_in_out.created_at", "desc")->
            get();

        return view('livewire.report-defect-in-out', ['defectInOutList' => $defectInOutList, 'defectInOutTotalQty' => $defectInOutTotalQty, 'outputAll' => $outputAll]);
    }
}
