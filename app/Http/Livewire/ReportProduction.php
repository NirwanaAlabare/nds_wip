<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use DB;

class ReportProduction extends Component
{
    public $date;
    public $selectedLine;
    public $defectTypes;
    public $defectAreas;
    public $loadingLine;
    public $backDateOutput;
    public $qcType;

    public function mount()
    {
        $lines = UserLine::with('masterPlans')->where('Groupp', 'SEWING')->whereRaw("(Locked != '1' OR Locked IS NULL)")->orderBy('FullName', 'asc')->get();

        $this->productionHours = [];
        $this->selectedLine = $lines->count() > 1 ? $lines[0]->username : '';
        $this->date = date('Y-m-d');
        $this->loadingLine = false;
        $this->backDateOutput = [];
        $this->qcType = false;
    }

    public function render()
    {
        $this->loadingLine = false;

        $lines = UserLine::with([
            "masterPlans" => function ($query) {
                $query->whereBetween('master_plan.tgl_plan', [date('Y-m-d', strtotime('-7 days', strtotime($this->date))), $this->date]);
            },
            "masterPlans.rfts" => function ($query) {
                $query->whereRaw('output_rfts.updated_at BETWEEN "'.$this->date.' 00:00:00" AND "'.$this->date.' 23:59:59"');
            },
            "masterPlans.defects" => function ($query) {
                $query->whereRaw('output_defects.updated_at BETWEEN "'.$this->date.' 00:00:00" AND "'.$this->date.' 23:59:59"');
            },
            "masterPlans.rejects" => function ($query) {
                $query->whereRaw('output_rejects.updated_at BETWEEN "'.$this->date.' 00:00:00" AND "'.$this->date.' 23:59:59"');
            }
        ])->where('Groupp', 'SEWING')->whereRaw("(Locked != '1' OR Locked IS NULL)")->orderBy('FullName', 'asc')->get();

        $hours = array(
            "08:00", "09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00"
        );

        // for ($i = 0; $i < count($hours); $i++) {
        //     if ($i < 1) {
        //         $production = MasterPlan::selectRaw("
        //                 SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
        //                 MAX(master_plan.man_power)*SUM(master_plan.jam_kerja)*60 mins_avail,
        //                 MAX(master_plan.man_power)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60)), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
        //                 FLOOR(MAX(master_plan.man_power)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))/AVG(master_plan.smv), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60)/AVG(master_plan.smv) ))) cumulative_target,
        //                 SUM(rfts.rft) total_rft,
        //                 SUM(defects.defect) total_defect,
        //                 SUM(reworks.rework) total_rework,
        //                 SUM(rejects.reject) total_reject
        //             ")->
        //             leftJoin(DB::raw("(SELECT count(rfts.id) rft, rfts.master_plan_id from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where status = 'NORMAL' and (cast(rfts.updated_at as time) BETWEEN '00:00' and '".$hours[$i]."') GROUP BY rfts.master_plan_id) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
        //             leftJoin(DB::raw("(SELECT count(defects.id) defect, defects.master_plan_id from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and (cast(defects.updated_at as time) BETWEEN '00:00' and '".$hours[$i]."') GROUP BY defects.master_plan_id) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
        //             leftJoin(DB::raw("(SELECT count(defrew.id) rework, defrew.master_plan_id from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and (cast(defrew.updated_at as time) BETWEEN '00:00' and '".$hours[$i]."') GROUP BY defrew.master_plan_id) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
        //             leftJoin(DB::raw("(SELECT count(rejects.id) reject, rejects.master_plan_id from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where (cast(rejects.updated_at as time) BETWEEN '00:00' and '".$hours[$i]."') GROUP BY rejects.master_plan_id) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
        //             where("master_plan.tgl_plan", $this->date)->
        //             where("master_plan.sewing_line", $this->selectedLine)->
        //             groupBy("master_plan.sewing_line")->get();
        //     } else {
        //         $production = MasterPlan::selectRaw("
        //                 SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
        //                 MAX(master_plan.man_power)*SUM(master_plan.jam_kerja)*60 mins_avail,
        //                 MAX(master_plan.man_power)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60)), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
        //                 FLOOR(MAX(master_plan.man_power)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))/AVG(master_plan.smv), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60)/AVG(master_plan.smv) ))) cumulative_target,
        //                 SUM(rfts.rft) total_rft,
        //                 SUM(defects.defect) total_defect,
        //                 SUM(reworks.rework) total_rework,
        //                 SUM(rejects.reject) total_reject
        //             ")->
        //             leftJoin(DB::raw("(SELECT count(rfts.id) rft, rfts.master_plan_id from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where status = 'NORMAL' and (cast(rfts.updated_at as time) BETWEEN '".$hours[$i-1]."' and '".$hours[$i]."') GROUP BY rfts.master_plan_id) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
        //             leftJoin(DB::raw("(SELECT count(defects.id) defect, defects.master_plan_id from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and (cast(defects.updated_at as time) BETWEEN '".$hours[$i-1]."' and '".$hours[$i]."') GROUP BY defects.master_plan_id) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
        //             leftJoin(DB::raw("(SELECT count(defrew.id) rework, defrew.master_plan_id from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and (cast(defrew.updated_at as time) BETWEEN '".$hours[$i-1]."' and '".$hours[$i]."') GROUP BY defrew.master_plan_id) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
        //             leftJoin(DB::raw("(SELECT count(rejects.id) reject, rejects.master_plan_id from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where (cast(rejects.updated_at as time) BETWEEN '".$hours[$i-1]."' and '".$hours[$i]."') GROUP BY rejects.master_plan_id) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
        //             where("master_plan.tgl_plan", $this->date)->
        //             where("master_plan.sewing_line", $this->selectedLine)->
        //             groupBy("master_plan.sewing_line")->get();
        //     }

        //     array_push($productions, $production);
        // }

        $this->defectTypes = Defect::selectRaw('defect_type_id, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects.master_plan_id")->
            where("master_plan.cancel", 'N')->
            where("master_plan.tgl_plan", $this->date)->
            where("master_plan.sewing_line", $this->selectedLine)->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->limit(5)->get();

        $defectTypeIds = [];
        foreach ($this->defectTypes as $type) {
            array_push($defectTypeIds, $type->defect_type_id);
        }

        $this->defectAreas = Defect::selectRaw('defect_type_id, defect_area_id, count(defect_area_id) as defect_area_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects.master_plan_id")->
            where("master_plan.cancel", 'N')->
            where("master_plan.tgl_plan", $this->date)->
            where("master_plan.sewing_line", $this->selectedLine)->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("defect_type_id", "defect_area_id")->
            orderByRaw("defect_area_count desc")->get();

        return view('livewire.report-production', ['lines' => $lines, 'hours' => $hours]);
    }
}
