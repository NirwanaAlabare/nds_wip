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
            $defectInOutQuery = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
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
            $defectInOutQuery = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
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

        return view('livewire.report-defect-in-out', ['defectInOutList' => $defectInOutList, 'defectInOutTotalQty' => $defectInOutTotalQty]);
    }
}
