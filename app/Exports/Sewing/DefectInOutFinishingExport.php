<?php

namespace App\Exports\Sewing;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\DefectInOut;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class DefectInOutFinishingExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $rowCount;

    function __construct($dateFrom, $dateTo) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $masterPlanDateFilter = " = '".$this->dateFrom."'";
        $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-7 days', strtotime($this->dateFrom)))."' and '".$this->dateTo."'";
        $outputFilter = " between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'";
        $leaderDate = $this->dateTo;

        $selectFilter = $masterPlanDateFilter1;

        $lines = MasterPlan::selectRaw("
                output_employee_line.leader_nik leader_nik,
                output_employee_line.leader_name leader_name,
                MAX(act_costing.kpno) kpno,
                MAX(act_costing.styleno) styleno,
                SUM((IFNULL(rfts.rft, 0))) rft,
                SUM((IFNULL(defects.defect, 0))) defect,
                SUM((IFNULL(reworks.rework, 0))) rework,
                SUM((IFNULL(rejects.reject, 0))) reject,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))) total_actual,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0)+IFNULL(defects.defect, 0)+IFNULL(rejects.reject, 0))) total_output,
                SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
                COALESCE(line.sewing_line, master_plan.sewing_line) FullName,
                COALESCE(line.sewing_line, master_plan.sewing_line) username,
                GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( defects.last_defect ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rejects.last_reject ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) latest_output,
                IFNULL(finish.defect_in, 0) defect_in,
                IFNULL(finish.rework_out, 0) rework_out,
                IFNULL(finish.rejected, 0) rejected
            ")->
            leftJoin("userpassword", "userpassword.username", "=", "master_plan.sewing_line")->
            leftJoin("output_employee_line", function ($join)  use ($leaderDate) {
                $join->on("output_employee_line.line_id", "=", "userpassword.line_id");
                $join->on("output_employee_line.tanggal", "=", DB::raw("'".$leaderDate."'"));
            })->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            join(DB::raw("(
                SELECT
                    master_plan.id_ws,
                    master_plan.id master_plan_id,
                    master_plan.sewing_line sewing_line,
                    COUNT(output_check_finishing.id) output,
                    SUM(CASE WHEN output_check_finishing.status = 'defect' THEN 1 ELSE 0 END) defect_in,
                    SUM(CASE WHEN output_check_finishing.status = 'reworked' THEN 1 ELSE 0 END) rework_out,
                    SUM(CASE WHEN output_check_finishing.status = 'rejected' THEN 1 ELSE 0 END) rejected
                FROM
                    output_check_finishing
                    LEFT JOIN master_plan ON master_plan.id = output_check_finishing.master_plan_id
                WHERE
                    output_check_finishing.created_by IS NOT NULL
                    AND ( output_check_finishing.created_at ".$outputFilter." OR output_check_finishing.updated_at ".$outputFilter." )
                GROUP BY
                    master_plan.id
            ) as finish"), function ($join) {
                $join->on("finish.master_plan_id", "=", "master_plan.id");
            })->
            leftJoin(DB::raw("(
                SELECT
                    master_plan.id_ws,
                    output_rfts.master_plan_id,
                    COALESCE(userpassword.username, master_plan.sewing_line) sewing_line
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                WHERE
                    output_rfts.created_by IS NOT NULL
                    AND output_rfts.updated_at ".$outputFilter."
                GROUP BY
                    output_rfts.master_plan_id,
                    COALESCE(userpassword.username, master_plan.sewing_line)
            ) as line"), function ($join) {
                $join->on("line.master_plan_id", "=", "finish.master_plan_id");
            })->
            leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id LEFT JOIN user_sb_wip ON user_sb_wip.id = rfts.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id where rfts.updated_at ".$outputFilter." and status = 'NORMAL' GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("line.sewing_line", "=", "rfts.created_by"); } )->
            leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id LEFT JOIN user_sb_wip ON user_sb_wip.id = defects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id where defects.defect_status = 'defect' and defects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("line.sewing_line", "=", "defects.created_by"); } )->
            leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id LEFT JOIN user_sb_wip ON user_sb_wip.id = defrew.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id where defrew.defect_status = 'reworked' and defrew.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("line.sewing_line", "=", "reworks.created_by"); } )->
            leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id LEFT JOIN user_sb_wip ON user_sb_wip.id = rejects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id where rejects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("line.sewing_line", "=", "rejects.created_by"); } )->
            where("master_plan.cancel", 'N')->
            groupByRaw("COALESCE(line.sewing_line, master_plan.sewing_line), master_plan.id_ws")->
            orderByRaw("COALESCE(line.sewing_line, master_plan.sewing_line) asc")->
            orderBy("master_plan.id_ws", "asc")->
            get();

            $defectTypes = DB::connection("mysql_sb")->table('output_check_finishing')->
            selectRaw('defect_type_id, defect_type, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_check_finishing.master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=","output_check_finishing.defect_type_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("output_check_finishing.updated_at ".$outputFilter."")->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->limit(5)->get();

        $defectTypeIds = [];
        foreach ($defectTypes as $type) {
            array_push($defectTypeIds, $type->defect_type_id);
        }

        $defectAreas = DB::connection("mysql_sb")->table('output_check_finishing')->
            selectRaw('defect_type_id, defect_area_id, defect_area, count(defect_area_id) as defect_area_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_check_finishing.master_plan_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=","output_check_finishing.defect_area_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("output_check_finishing.updated_at ".$outputFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("defect_type_id", "defect_area_id")->
            orderByRaw("defect_area_count desc")->get();

        $defectAreaIds = [];
        foreach ($defectAreas as $area) {
            array_push($defectAreaIds, $area->defect_area_id);
        }

        $lineDefects = DB::connection("mysql_sb")->table('output_check_finishing')->
            selectRaw("master_plan.sewing_line, output_check_finishing.defect_type_id, output_check_finishing.defect_area_id, count(*) as total")->
            leftJoin('master_plan', 'master_plan.id', 'output_check_finishing.master_plan_id')->
            where("master_plan.cancel", 'N')->
            whereRaw("output_check_finishing.updated_at ".$outputFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("master_plan.sewing_line", "output_check_finishing.defect_type_id", "output_check_finishing.defect_area_id")->get();

        return view('sewing.export.defect-in-out-finishing-export', [
            'lines' => $lines,
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'lineDefects' => $lineDefects,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        // $event->sheet->styleCells(
        //     'A2:J' . ($event->getConcernable()->rowCount+4),
        //     [
        //         'borders' => [
        //             'allBorders' => [
        //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //                 'color' => ['argb' => '000000'],
        //             ],
        //         ],
        //     ]
        // );
    }
}
