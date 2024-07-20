<?php

namespace App\Exports;

use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class OutputExportCustomRange implements FromView, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $subtype;

    function __construct($dateFrom, $dateTo, $subtype) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->subtype = $subtype;
    }

    public function view(): View
    {
        $masterPlanDateFilter = " between '".$this->dateFrom."' and '".$this->dateTo."'";

        $lines = UserLine::selectRaw("
                MAX(act_costing.kpno) kpno,
                MAX(act_costing.styleno) styleno,
                SUM((IFNULL(rfts.rft, 0))) rft,
                SUM((IFNULL(defects.defect, 0))) defect,
                SUM((IFNULL(reworks.rework, 0))) rework,
                SUM((IFNULL(rejects.reject, 0))) reject,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))) total_actual,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0)++IFNULL(defects.defect, 0)+IFNULL(rejects.reject, 0))) total_output,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
                SUM(master_plan.man_power * master_plan.jam_kerja)*60 mins_avail,
                MAX(master_plan.man_power) man_power,
                MAX(master_plan.man_power)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60)), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
                FLOOR(MAX(master_plan.man_power)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))/AVG(master_plan.smv), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60)/AVG(master_plan.smv) ))) cumulative_target,
                max(master_plan.man_power) man_power,
                SUM(master_plan.plan_target) total_target,
                userpassword.FullName,
                userpassword.username,
                SUM(master_plan.jam_kerja) jam_kerja,
                DATE(MAX(master_plan.tgl_plan)) tgl_plan,
                GREATEST(IFNULL(MAX(rfts.last_rft), MAX(master_plan.tgl_plan)), IFNULL(MAX(defects.last_defect), MAX(master_plan.tgl_plan)), IFNULL(MAX(reworks.last_rework), MAX(master_plan.tgl_plan)), IFNULL(MAX(rejects.last_reject), MAX(master_plan.tgl_plan))) latest_output")->
            leftJoin("master_plan", "userpassword.username", "=", "master_plan.sewing_line")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id from output_rfts".$this->subtype." rfts inner join master_plan on master_plan.id = rfts.master_plan_id where DATE(rfts.updated_at) ".$masterPlanDateFilter." and status = 'NORMAL' GROUP BY master_plan.id, master_plan.tgl_plan) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id from output_defects".$this->subtype." defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and DATE(defects.updated_at) ".$masterPlanDateFilter." GROUP BY master_plan.id, master_plan.tgl_plan) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id from output_defects".$this->subtype." defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and DATE(defrew.updated_at) ".$masterPlanDateFilter." GROUP BY master_plan.id, master_plan.tgl_plan) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id from output_rejects".$this->subtype." rejects inner join master_plan on master_plan.id = rejects.master_plan_id where DATE(rejects.updated_at) ".$masterPlanDateFilter." GROUP BY master_plan.id, master_plan.tgl_plan) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
            where("userpassword.Groupp", 'SEWING')->
            where("master_plan.cancel", 'N')->
            whereRaw("(master_plan.tgl_plan ".$masterPlanDateFilter." OR (IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0)) > 0)")->
            whereRaw("(userpassword.Locked != 1 OR userpassword.Locked IS NULL)")->
            groupBy("userpassword.FullName","userpassword.username","master_plan.sewing_line","master_plan.id_ws")->
            orderBy("master_plan.sewing_line", "asc")->
            orderBy("master_plan.id_ws", "asc")->
            get();

        $defectTypes = DB::connection('mysql_sb')->table('output_defects'.$this->subtype)->
            selectRaw('defect_type_id, defect_type, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects".$this->subtype.".master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=","output_defects".$this->subtype.".defect_type_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("DATE(output_defects".$this->subtype.".updated_at) ".$masterPlanDateFilter."")->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->limit(5)->get();

        $defectTypeIds = [];
        foreach ($defectTypes as $type) {
            array_push($defectTypeIds, $type->defect_type_id);
        }

        $defectAreas = DB::connection('mysql_sb')->table('output_defects'.$this->subtype)->
            selectRaw('defect_type_id, defect_area_id, defect_area, count(defect_area_id) as defect_area_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects".$this->subtype.".master_plan_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=","output_defects".$this->subtype.".defect_area_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("DATE(output_defects".$this->subtype.".updated_at) ".$masterPlanDateFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("defect_type_id", "defect_area_id")->
            orderByRaw("defect_area_count desc")->get();

        $defectAreaIds = [];
        foreach ($defectAreas as $area) {
            array_push($defectAreaIds, $area->defect_area_id);
        }

        $lineDefects = DB::connection('mysql_sb')->table('output_defects'.$this->subtype)->
            selectRaw("master_plan.sewing_line, output_defects".$this->subtype.".defect_type_id, output_defects".$this->subtype.".defect_area_id, count(*) as total")->
            leftJoin('master_plan', 'master_plan.id', 'output_defects'.$this->subtype.'.master_plan_id')->
            where("master_plan.cancel", 'N')->
            whereRaw("DATE(output_defects".$this->subtype.".updated_at) ".$masterPlanDateFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("master_plan.sewing_line", "output_defects".$this->subtype.".defect_type_id", "output_defects".$this->subtype.".defect_area_id")->get();

        return view('sewing.export.output-export-custom-range', [
            'lines' => $lines,
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'lineDefects' => $lineDefects,
            'subtype' => $this->subtype,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'date' => $this->dateFrom.' - '.$this->dateTo
        ]);
    }
}
