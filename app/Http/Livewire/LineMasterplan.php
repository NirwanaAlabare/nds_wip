<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use DB;

class LineMasterplan extends Component
{
    public $date;
    public $masterPlan;
    public $lineRow;
    public $baseUrl;

    public $search;

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->baseUrl = url('/');

        $this->search = "";
    }

    public function render()
    {
        $this->masterPlan = MasterPlan::selectRaw("
                master_plan.id,
                master_plan.tgl_plan,
                master_plan.sewing_line,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.styleno_prod style_production,
                master_plan.color,
                master_plan.smv,
                master_plan.jam_kerja,
                master_plan.man_power,
                master_plan.plan_target,
                master_plan.target_effy
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            join("so", "so.id_cost", "=", "act_costing.id")->
            join(DB::raw("(select so_det.id, so_det.id_so, so_det.color, so_det.styleno_prod from so_det group by id_so, color) so_det"), function ($join) {
                $join->on("so_det.id_so", "=", "so.id");
                $join->on("so_det.color", "=", "master_plan.color");
            })->
            where("tgl_plan", $this->date)->
            where("master_plan.cancel", "N")->
            whereRaw("(
                master_plan.tgl_plan LIKE '%".$this->search."%'
                OR
                REPLACE(master_plan.sewing_line, '_', ' ') LIKE '%".$this->search."%'
                OR
                REPLACE(master_plan.sewing_line, '_', '') LIKE '%".$this->search."%'
                OR
                master_plan.sewing_line LIKE '%".$this->search."%'
                OR
                act_costing.kpno LIKE '%".$this->search."%'
                OR
                act_costing.styleno LIKE '%".$this->search."%'
                OR
                so_det.styleno_prod LIKE '%".$this->search."%'
                OR
                master_plan.color LIKE '%".$this->search."%'
            )")->
            orderBy("sewing_line", "asc")->
            get();

        $this->lineRow = MasterPlan::selectRaw("sewing_line, COUNT(id) total_row, GROUP_CONCAT(jam_kerja), SUM(jam_kerja) total_jam, SUM(plan_target) total_target")->where("tgl_plan", $this->date)->where("cancel", "N")->groupBy("sewing_line", "tgl_plan")->get();

        return view('livewire.line-masterplan');
    }
}
