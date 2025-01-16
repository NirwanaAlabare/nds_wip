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
        $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-7 days', strtotime($this->dateFrom)))."' and '".$this->dateTo."'";
        $outputFilter = " between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'";
        $leaderDate = $this->dateTo;

        $selectFilter = $masterPlanDateFilter;

        $lines = MasterPlan::selectRaw("
                output_leader_line.employee_nik leader_nik,
                output_leader_line.employee_name leader_name,
                MAX(act_costing.kpno) kpno,
                MAX(act_costing.styleno) styleno,
                SUM((IFNULL(rfts.rft, 0))) rft,
                SUM((IFNULL(defects.defect, 0))) defect,
                SUM((IFNULL(reworks.rework, 0))) rework,
                GROUP_CONCAT(CONCAT(IFNULL(reworks.rework, 0), reworks.created_by, master_plan.sewing_line)) reworkasd,
                SUM((IFNULL(rejects.reject, 0))) reject,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))) total_actual,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0)+IFNULL(defects.defect, 0)+IFNULL(rejects.reject, 0))) total_output,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
                SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power * master_plan.jam_kerja ) ELSE 0 END)*60 mins_avail,
                MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END) man_power,
                MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END)*(IF(cast(GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) as time) <= '13:00:00', ((TIME_TO_SEC(TIMEDIFF(cast(GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) as time), '07:00:00'))/60)), (((TIME_TO_SEC(TIMEDIFF(cast(GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
                FLOOR(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END )*(IF(cast(GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) as time) <= '13:00:00', ((TIME_TO_SEC(TIMEDIFF(cast(GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) as time), '07:00:00'))/60))/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END), (((TIME_TO_SEC(TIMEDIFF(cast(GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) as time), '07:00:00'))/60))-60)/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END) ))) cumulative_target,
                SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.plan_target ) ELSE 0 END) total_target,
                COALESCE(line.sewing_line, master_plan.sewing_line) FullName,
                COALESCE(line.sewing_line, master_plan.sewing_line) username,
                SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.jam_kerja ) ELSE 0 END) jam_kerja,
                MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( DATE(master_plan.tgl_plan) ) ELSE 0 END) tgl_plan,
                GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( defects.last_defect ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rejects.last_reject ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) latest_output
            ")->
            leftJoin("userpassword", "userpassword.username", "=", "master_plan.sewing_line")->
            leftJoin("output_leader_line", function ($join) use ($leaderDate) {
                $join->on("output_leader_line.line_id", "=", "userpassword.line_id");
                $join->on("output_leader_line.tanggal", "=", DB::raw("'".$leaderDate."'"));
            })->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            join(DB::raw("(
                SELECT
                    master_plan.id_ws,
                    output_rfts".($this->subtype).".master_plan_id,
                    COALESCE(userpassword.username, master_plan.sewing_line) sewing_line
                FROM
                    output_rfts".($this->subtype)."
                    ".($this->subtype != "_packing" ?
                    "LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts".($this->subtype).".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" :
                    "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->subtype).".created_by")."
                    LEFT JOIN master_plan on master_plan.id = output_rfts".($this->subtype).".master_plan_id
                WHERE
                    output_rfts".($this->subtype).".created_by IS NOT NULL
                    AND output_rfts".($this->subtype).".updated_at ".$outputFilter."
                GROUP BY
                    output_rfts".($this->subtype).".master_plan_id,
                    COALESCE(userpassword.username, master_plan.sewing_line)
            ) as line"), function ($join) {
                $join->on("line.master_plan_id", "=", "master_plan.id");
            })->
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rfts".$this->subtype." rfts inner join master_plan on master_plan.id = rfts.master_plan_id ".($this->subtype != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rfts.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rfts.created_by")." where rfts.updated_at ".$outputFilter." and status = 'NORMAL' GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("line.sewing_line", "=", "rfts.created_by"); } )->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->subtype." defects inner join master_plan on master_plan.id = defects.master_plan_id ".($this->subtype != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defects.created_by")." where defects.defect_status = 'defect' and defects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("line.sewing_line", "=", "defects.created_by"); } )->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->subtype." defrew inner join master_plan on master_plan.id = defrew.master_plan_id ".($this->subtype != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defrew.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defrew.created_by")." where defrew.defect_status = 'reworked' and defrew.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("line.sewing_line", "=", "reworks.created_by"); } )->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rejects".$this->subtype." rejects inner join master_plan on master_plan.id = rejects.master_plan_id ".($this->subtype != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rejects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rejects.created_by")." where rejects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("line.sewing_line", "=", "rejects.created_by"); } )->
            where("master_plan.cancel", 'N')->
            groupByRaw("COALESCE(line.sewing_line, master_plan.sewing_line), master_plan.id_ws")->
            orderByRaw("COALESCE(line.sewing_line, master_plan.sewing_line) asc")->
            orderBy("master_plan.id_ws", "asc")->
            get();

        $defectTypes = DB::connection("mysql_sb")->table('output_defects'.$this->subtype)->
            selectRaw('defect_type_id, defect_type, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects".$this->subtype.".master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=","output_defects".$this->subtype.".defect_type_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("output_defects".$this->subtype.".updated_at ".$outputFilter."")->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->limit(5)->get();

        $defectTypeIds = [];
        foreach ($defectTypes as $type) {
            array_push($defectTypeIds, $type->defect_type_id);
        }

        $defectAreas = DB::connection("mysql_sb")->table('output_defects'.$this->subtype)->
            selectRaw('defect_type_id, defect_area_id, defect_area, count(defect_area_id) as defect_area_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects".$this->subtype.".master_plan_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=","output_defects".$this->subtype.".defect_area_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("output_defects".$this->subtype.".updated_at ".$outputFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("defect_type_id", "defect_area_id")->
            orderByRaw("defect_area_count desc")->get();

        $defectAreaIds = [];
        foreach ($defectAreas as $area) {
            array_push($defectAreaIds, $area->defect_area_id);
        }

        $lineDefects = DB::connection("mysql_sb")->table('output_defects'.$this->subtype)->
            selectRaw("master_plan.sewing_line, output_defects".$this->subtype.".defect_type_id, output_defects".$this->subtype.".defect_area_id, count(*) as total")->
            leftJoin('master_plan', 'master_plan.id', 'output_defects'.$this->subtype.'.master_plan_id')->
            where("master_plan.cancel", 'N')->
            whereRaw("output_defects".$this->subtype.".updated_at ".$outputFilter."")->
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
