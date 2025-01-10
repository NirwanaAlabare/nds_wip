<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Reject;
use DB;

class ReportOutput extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = "";
    public $date;
    public $range;
    public $dateFrom;
    public $dateTo;
    public $group;
    public $period;

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->dateFrom = date('Y-m-d');
        $this->dateTo = date('Y-m-d');
        $this->qcType = '';
        $this->group = 'line';
        $this->period = 'daily';
        $this->range = 'single';
    }

    public function filter($group, $period, $range)
    {
        $this->group = $group;
        $this->period = $period;
        $this->range = $range;
    }

    public function render()
    {
        ini_set("max_execution_time", "3600");

        // Alternate Query
            // SELECT
            //     a.sewing_line,
            //     kpno,
            //     styleno,
            //     rft,
            //     defect_val,
            //     rework,
            //     reject,
            //     per_rft,
            //     per_defect,
            //     per_reject,
            //     actual,
            //     target_min,
            //     effi,
            //     jml_ws,
            //     nama_line
            // FROM (
            //     select
            //         *,
            //         COALESCE(ROUND((rft / (rft + (defect_val + rework) + reject) * 100),2),0) per_rft,
            //         COALESCE(ROUND(((defect_val + rework) / (rft + (defect_val + rework) + reject) * 100),2),0) per_defect,
            //         COALESCE(ROUND((reject / (rft + (defect_val + rework) + reject) * 100),2),0) per_reject, (rft + rework) actual
            //     from (
            //         select
            //             a.sewing_line,
            //             a.id_ws,
            //             kpno,
            //             styleno,
            //             COALESCE(rft,0) rft,
            //             COALESCE(defect_val,0) defect_val,
            //             COALESCE(rework,0) rework,
            //             COALESCE(reject,0) reject
            //         from (
            //             select
            //                 sewing_line,
            //                 id_ws,
            //                 kpno,
            //                 styleno
            //             from (
            //                 select
            //                     a.id,
            //                     tgl_plan,
            //                     sewing_line,
            //                     id_ws,
            //                     a.color,
            //                     b.kpno,
            //                     b.styleno
            //                 from
            //                     master_plan a
            //                 inner join
            //                     act_costing b on b.id =  a. id_ws
            //                 where
            //                     tgl_plan = CURRENT_DATE() and a.cancel != 'Y'
            //                 order by a.id ASC
            //             ) a
            //             GROUP BY
            //                 a.sewing_line,a.id_ws order by a.sewing_line asc
            //         ) a
            //         left join
            //             (
            //                 SELECT
            //                     b.sewing_line,
            //                     b.id_ws,MAX(updated_at) last_input,
            //                     count(a.id) rft
            //                 from
            //                     output_rfts a
            //                 inner join
            //                     master_plan b
            //                 on
            //                     b.id = a.master_plan_id
            //                 where
            //                     tgl_plan = CURRENT_DATE()
            //                 and
            //                     status = 'NORMAL'
            //                 GROUP BY
            //                     b.sewing_line, b.id_ws
            //             ) b
            //         on
            //             b.id_ws = a.id_ws and b.sewing_line = a.sewing_line
            //         left join
            //         (
            //             SELECT
            //                 b.sewing_line,
            //                 b.id_ws,
            //                 count(a.id) defect_val
            //             from
            //                 output_defects a
            //             inner join
            //                 master_plan b
            //             on
            //                 b.id = a.master_plan_id
            //             where
            //                 tgl_plan = CURRENT_DATE() and defect_status = 'defect'
            //             GROUP BY
            //                 b.sewing_line,b.id_ws
            //         ) c
            //         on
            //             c.id_ws = a.id_ws and c.sewing_line = a.sewing_line
            //         left join
            //         (
            //             SELECT
            //                 b.sewing_line,
            //                 b.id_ws,
            //                 count(a.id) rework
            //             from
            //                 output_defects a
            //             inner join
            //                 master_plan b on b.id = a.master_plan_id
            //             where
            //                 tgl_plan = CURRENT_DATE() and defect_status != 'defect'
            //             GROUP BY
            //                 b.sewing_line,b.id_ws
            //         ) d
            //         on
            //             d.id_ws = a.id_ws and d.sewing_line = a.sewing_line
            //         left join
            //         (
            //             SELECT
            //                 b.sewing_line,
            //                 b.id_ws,
            //                 count(a.id) reject
            //             from
            //                 output_rejects a
            //             inner join
            //                 master_plan b
            //             on
            //                 b.id = a.master_plan_id
            //             where
            //                 tgl_plan = CURRENT_DATE()
            //             GROUP BY
            //                 b.sewing_line,b.id_ws
            //         ) e
            //         on
            //             e.id_ws = a.id_ws and e.sewing_line = a.sewing_line
            //     ) a
            // ) a
            // left JOIN
            //     (
            //         select
            //             sewing_line,
            //             ROUND((min_prod / (man_power * menit_real) * 100),2) effi,
            //             if(jam_real > jam_kerja,plan_target,ROUND((target_min * menit_real),0)) target_min
            //         from (
            //             Select
            //                 a.sewing_line,
            //                 actual,
            //                 min_prod,
            //                 man_power,
            //                 if(menit_real > (jam_kerja * 60),jam_kerja,jam_real) jam_real,
            //                 if(menit_real > (jam_kerja * 60),(jam_kerja * 60),menit_real) menit_real,
            //                 jam_kerja,
            //                 plan_target,
            //                 (plan_target / (jam_kerja * 60)) target_min
            //             from (
            //                 SELECT
            //                     a.sewing_line,
            //                     sum(actual) actual,
            //                     sum(min_prod) min_prod,
            //                     man_power,
            //                     jam_real,
            //                     menit_real
            //                 FROM (
            //                     SELECT
            //                         id,
            //                         sewing_line,
            //                         smv,
            //                         actual,
            //                         round(smv * actual,4) min_prod
            //                     FROM (
            //                         SELECT
            //                             sewing_line,
            //                             id,
            //                             smv
            //                         from
            //                             master_plan
            //                         where
            //                             tgl_plan = CURRENT_DATE()
            //                     ) a
            //                     inner join
            //                         (
            //                             SELECT
            //                                 master_plan_id,
            //                                 count(a.id) actual
            //                             from
            //                                 output_rfts a
            //                             inner join
            //                                 master_plan b
            //                             on
            //                                 b.id = a.master_plan_id
            //                             where
            //                                 tgl_plan = CURRENT_DATE()
            //                             GROUP BY
            //                                 master_plan_id
            //                         ) b
            //                     on
            //                     b.master_plan_id = a.id
            //                 ) a
            //                 inner join
            //                 (
            //                     SELECT
            //                         sewing_line,
            //                         man_power,
            //                         jam_masuk,
            //                         jam_sekarang,
            //                         if(jam_kerja >= '6',jam_kerja -1,jam_kerja) jam_real,
            //                         if(jam_kerja >= '6',menit_kerja -60,menit_kerja) menit_real
            //                     from (
            //                         SELECT
            //                             sewing_line,
            //                             man_power,
            //                             CONCAT(CURRENT_DATE,' ','07:00:00') jam_masuk,
            //                             CURRENT_DATE() as jam_sekarang,
            //                             TIMESTAMPDIFF(hour,CONCAT(CURRENT_DATE,' ','07:00:00'),CURRENT_TIMESTAMP) jam_kerja,
            //                             TIMESTAMPDIFF(minute,CONCAT(CURRENT_DATE,' ','07:00:00'),CURRENT_TIMESTAMP) menit_kerja
            //                         from
            //                             master_plan
            //                         where
            //                             tgl_plan = CURRENT_DATE()
            //                         GROUP BY
            //                             sewing_line
            //                         order by
            //                             sewing_line
            //                         asc
            //                     ) a
            //                 ) b
            //                 on
            //                     b.sewing_line = a.sewing_line
            //                 GROUP BY
            //                     a.sewing_line
            //             ) a
            //             inner join
            //                 (
            //                     SELECT
            //                         sewing_line,
            //                         COALESCE(sum(jam_kerja),0) as jam_kerja,
            //                         COALESCE(sum(plan_target),0) as plan_target
            //                     from
            //                         master_plan
            //                     where
            //                         tgl_plan = CURRENT_DATE()
            //                     GROUP BY
            //                         sewing_line
            //                 ) b
            //             on
            //                 b.sewing_line = a.sewing_line
            //         ) a
            //     ) b
            //     on
            //         b.sewing_line = a.sewing_line
            //     left JOIN
            //         (
            //             select
            //                 sewing_line,
            //                 COUNT(sewing_line) jml_ws
            //             from (
            //                 select
            //                     sewing_line,
            //                     id_ws,
            //                     kpno,
            //                     styleno
            //                 from (
            //                     select
            //                         a.id,
            //                         tgl_plan,
            //                         sewing_line,
            //                         id_ws,
            //                         a.color,
            //                         b.kpno,
            //                         b.styleno
            //                     from
            //                         master_plan a
            //                     inner join
            //                         act_costing b on b.id =  a.id_ws
            //                     where
            //                         tgl_plan = CURRENT_DATE()
            //                     and
            //                         a.cancel != 'Y'
            //                     order by
            //                         a.id
            //                     ASC
            //                 ) a
            //                 GROUP BY
            //                     a.sewing_line,
            //                     a.id_ws
            //                 order by
            //                     a.sewing_line
            //                 asc
            //             ) a
            //             GROUP BY
            //                 sewing_line
            //         ) c
            //     on
            //         c.sewing_line = a.sewing_line
            //     LEFT JOIN
            //         (
            //             select
            //                 username,
            //                 SUBSTR(FullName FROM 8) nama_line
            //             from
            //                 userpassword
            //             where
            //                 Groupp = 'sewing'
            //             order by
            //                 username
            //             asc
            //         ) d
            //     on
            //         d.username = a.sewing_line

            // $lines = UserLine::select("
            //     select
            //         a.sewing_line,
            //         kpno,
            //         styleno,
            //         rft,
            //         defect_val,
            //         rework,
            //         reject,
            //         per_rft,
            //         per_defect,
            //         per_reject,
            //         actual,
            //         target_min,
            //         effi,
            //         jml_ws,
            //         nama_line,
            //     from
            //         (
            //             select
            //                 rft,
            //                 defect,
            //                 rework,
            //                 reject,
            //                 GREATEST(IFNULL(MAX(last_rft), MAX(master_plan.tgl_plan)), IFNULL(MAX(last_defect), MAX(master_plan.tgl_plan)), IFNULL(MAX(last_rework), MAX(master_plan.tgl_plan)), IFNULL(MAX(last_reject), MAX(master_plan.tgl_plan))) latest_output
            //             from
            //                 (
            //                     select
            //                         master_plan.id_ws,
            //                         count(output_rfts.id) rft,
            //                         MAX(updated_at) last_rft
            //                     from
            //                         output_rft
            //                 )
            //         )
            // ");
            //         leftJoin("master_plan", "userpassword.username", "=", "master_plan.sewing_line")->
            //         leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            //         leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, rfts.master_plan_id from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where DATE(rfts.updated_at) = '".$this->date."' and status = 'NORMAL' GROUP BY rfts.master_plan_id) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
            //         leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, defects.master_plan_id from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and DATE(defects.updated_at) = '".$this->date."' GROUP BY defects.master_plan_id) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
            //         leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, defrew.master_plan_id from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and DATE(defrew.updated_at) = '".$this->date."' GROUP BY defrew.master_plan_id) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
            //         leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, rejects.master_plan_id from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where DATE(rejects.updated_at) = '".$this->date."' GROUP BY rejects.master_plan_id) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
            //         where("userpassword.Groupp", 'SEWING')->
            //         where("master_plan.cancel", 'N')->
            //         where("master_plan.tgl_plan", $this->date)->
            //         whereRaw("(userpassword.Locked != 1 OR userpassword.Locked IS NULL)")->
            //         whereRaw("(
            //             userpassword.username LIKE '%".$this->search."%' OR
            //             userpassword.FullName LIKE '%".$this->search."%'
            //         )")->
            //         groupBy("userpassword.FullName","userpassword.username","master_plan.sewing_line")->get();

        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        if ($this->range == 'custom') {
            $masterPlanDateFilter = " between '".$this->dateFrom."' and '".$this->dateTo."'";
            $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-7 days', strtotime($this->dateFrom)))."' and '".$this->dateTo."'";
            $outputFilter = " between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'";
        } else {
            $masterPlanDateFilter = " = '".$this->date."'";
            $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-7 days', strtotime($this->date)))."' and '".$this->date."'";
            $outputFilter = " between '".$this->date." 00:00:00' and '".$this->date." 23:59:59'";
        }

        if (($this->range == "custom" && date('Y-m-d H:i:s') >= $this->dateFrom.' 16:00:00') || date('Y-m-d H:i:s') >= $this->date.' 16:00:00') {
            $selectFilter = $masterPlanDateFilter;
        } else {
            $selectFilter = $masterPlanDateFilter1;
        }

        if ($this->group == 'line') {
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
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60)), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
                    FLOOR(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END )*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60)/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END) ))) cumulative_target,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.plan_target ) ELSE 0 END) total_target,
                    COALESCE(line.sewing_line, master_plan.sewing_line) FullName,
                    COALESCE(line.sewing_line, master_plan.sewing_line) username,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.jam_kerja ) ELSE 0 END) jam_kerja,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( DATE(master_plan.tgl_plan) ) ELSE 0 END) tgl_plan,
                    GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( defects.last_defect ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rejects.last_reject ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) latest_output")->
                leftJoin("userpassword", "userpassword.username", "=", "master_plan.sewing_line")->
                leftJoin("output_leader_line", function ($join) {
                    $join->on("output_leader_line.line_id", "=", "userpassword.line_id");
                    $join->on("output_leader_line.tanggal", "=", "master_plan.tgl_plan");
                })->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                join("so", "so.id_cost", "=", "act_costing.id")->
                join(DB::raw("(select * from so_det group by id_so, color) so_det"), function ($join) {
                    $join->on("so_det.id_so", "=", "so.id");
                    $join->on("so_det.color", "=", "master_plan.color");
                })->
                join(DB::raw("(
                    SELECT
                        master_plan.id_ws,
                        output_rfts".($this->qcType).".master_plan_id,
                        COALESCE(userpassword.username, master_plan.sewing_line) sewing_line
                    FROM
                        output_rfts".($this->qcType)."
                        ".($this->qcType != "_packing" ?
                        "LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts".($this->qcType).".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" :
                        "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->qcType).".created_by")."
                        LEFT JOIN master_plan on master_plan.id = output_rfts".($this->qcType).".master_plan_id
                    WHERE
                        output_rfts".($this->qcType).".created_by IS NOT NULL
                        AND output_rfts".($this->qcType).".updated_at ".$outputFilter."
                    GROUP BY
                        output_rfts".($this->qcType).".master_plan_id,
                        COALESCE(userpassword.username, master_plan.sewing_line)
                ) as line"), function ($join) {
                    $join->on("line.master_plan_id", "=", "master_plan.id");
                })->
                leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rfts".$this->qcType." rfts inner join master_plan on master_plan.id = rfts.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rfts.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rfts.created_by")." where rfts.updated_at ".$outputFilter." and status = 'NORMAL' GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("line.sewing_line", "=", "rfts.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->qcType." defects inner join master_plan on master_plan.id = defects.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defects.created_by")." where defects.defect_status = 'defect' and defects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("line.sewing_line", "=", "defects.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->qcType." defrew inner join master_plan on master_plan.id = defrew.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defrew.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defrew.created_by")." where defrew.defect_status = 'reworked' and defrew.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("line.sewing_line", "=", "reworks.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rejects".$this->qcType." rejects inner join master_plan on master_plan.id = rejects.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rejects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rejects.created_by")." where rejects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("line.sewing_line", "=", "rejects.created_by"); } )->
                where("master_plan.cancel", 'N')->
                whereRaw("(
                    COALESCE(line.sewing_line, master_plan.sewing_line) LIKE '%".$this->search."%' OR
                    act_costing.kpno LIKE '%".$this->search."%' OR
                    act_costing.styleno LIKE '%".$this->search."%'
                )")->
                groupByRaw("COALESCE(line.sewing_line, master_plan.sewing_line), master_plan.id_ws")->
                orderByRaw("COALESCE(line.sewing_line, master_plan.sewing_line) asc")->
                orderBy("master_plan.id_ws", "asc")->
                get();
        } else {
            $lines = collect([]);
        }

        if ($this->group == 'ws' || $this->group == 'style') {
            if ($this->group == 'ws') {
                $masterPlans = MasterPlan::all();

                $orders = MasterPlan::selectRaw("
                    output_leader_line.employee_nik leader_nik,
                    output_leader_line.employee_name leader_name,
                    master_plan.id_ws,
                    act_costing.kpno,
                    act_costing.styleno,
                    SUM((IFNULL(rfts.rft, 0))) rft,
                    SUM((IFNULL(defects.defect, 0))) defect,
                    SUM((IFNULL(reworks.rework, 0))) rework,
                    SUM((IFNULL(rejects.reject, 0))) reject,
                    SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))) total_actual,
                    SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0)++IFNULL(defects.defect, 0)+IFNULL(rejects.reject, 0))) total_output,
                    SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power * master_plan.jam_kerja ) ELSE 0 END)*60 mins_avail,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END) man_power,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60)), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
                    FLOOR(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END )*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60)/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END) ))) cumulative_target,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.plan_target ) ELSE 0 END) total_target,
                    COALESCE(line.sewing_line, master_plan.sewing_line) FullName,
                    COALESCE(line.sewing_line, master_plan.sewing_line) username,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.jam_kerja ) ELSE 0 END) jam_kerja,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( DATE(master_plan.tgl_plan) ) ELSE 0 END) tgl_plan,
                    GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( defects.last_defect ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rejects.last_reject ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) latest_output
                ")->
                leftJoin("userpassword", "userpassword.username", "=", "master_plan.sewing_line")->
                leftJoin("output_leader_line", function ($join) {
                    $join->on("output_leader_line.line_id", "=", "userpassword.line_id");
                    $join->on("output_leader_line.tanggal", "=", "master_plan.tgl_plan");
                })->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                join("so", "so.id_cost", "=", "act_costing.id")->
                join(DB::raw("(select * from so_det group by id_so, color) so_det"), function ($join) {
                    $join->on("so_det.id_so", "=", "so.id");
                    $join->on("so_det.color", "=", "master_plan.color");
                })->
                join(DB::raw("(
                    SELECT
                        master_plan.id_ws,
                        output_rfts".($this->qcType).".master_plan_id,
                        COALESCE(userpassword.username, master_plan.sewing_line) sewing_line
                    FROM
                        output_rfts".($this->qcType)."
                        ".($this->qcType != "_packing" ?
                        "LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts".($this->qcType).".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" :
                        "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->qcType).".created_by")."
                        LEFT JOIN master_plan on master_plan.id = output_rfts".($this->qcType).".master_plan_id
                    WHERE
                        output_rfts".($this->qcType).".created_by IS NOT NULL
                        AND output_rfts".($this->qcType).".updated_at ".$outputFilter."
                    GROUP BY
                        output_rfts".($this->qcType).".master_plan_id,
                        COALESCE(userpassword.username, master_plan.sewing_line)
                ) as line"), function ($join) {
                    $join->on("line.master_plan_id", "=", "master_plan.id");
                })->
                leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rfts".$this->qcType." rfts inner join master_plan on master_plan.id = rfts.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rfts.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rfts.created_by")." where rfts.updated_at ".$outputFilter."  and status = 'NORMAL' GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("line.sewing_line", "=", "rfts.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->qcType." defects inner join master_plan on master_plan.id = defects.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defects.created_by")." where defects.defect_status = 'defect' and defects.updated_at ".$outputFilter."  GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("line.sewing_line", "=", "defects.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->qcType." defrew inner join master_plan on master_plan.id = defrew.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defrew.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defrew.created_by")." where defrew.defect_status = 'reworked' and defrew.updated_at ".$outputFilter."  GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("line.sewing_line", "=", "reworks.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rejects".$this->qcType." rejects inner join master_plan on master_plan.id = rejects.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rejects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rejects.created_by")." where rejects.updated_at ".$outputFilter."  GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("line.sewing_line", "=", "rejects.created_by"); } )->
                where("master_plan.cancel", 'N')->
                whereRaw("(
                    COALESCE(line.sewing_line, master_plan.sewing_line) LIKE '%".$this->search."%' OR
                    act_costing.kpno LIKE '%".$this->search."%' OR
                    act_costing.styleno LIKE '%".$this->search."%'
                )")->
                groupByRaw("act_costing.kpno, master_plan.id_ws, act_costing.styleno, COALESCE(line.sewing_line, master_plan.sewing_line)")->
                get();
            } else if ($this->group == "style") {
                $masterPlans = MasterPlan::all();

                $orders = MasterPlan::selectRaw("
                    output_leader_line.employee_nik leader_nik,
                    output_leader_line.employee_name leader_name,
                    MAX(master_plan.id_ws) as id_ws,
                    MAX(act_costing.kpno) as kpno,
                    act_costing.styleno,
                    SUM((IFNULL(rfts.rft, 0))) rft,
                    SUM((IFNULL(defects.defect, 0))) defect,
                    SUM((IFNULL(reworks.rework, 0))) rework,
                    SUM((IFNULL(rejects.reject, 0))) reject,
                    SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))) total_actual,
                    SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0)++IFNULL(defects.defect, 0)+IFNULL(rejects.reject, 0))) total_output,
                    SUM((IFNULL(rfts.rft, 0)+IFNULL(reworks.rework, 0))*master_plan.smv) mins_prod,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power * master_plan.jam_kerja ) ELSE 0 END)*60 mins_avail,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END) man_power,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END)*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60)), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60))) cumulative_mins_avail,
                    FLOOR(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.man_power ) ELSE 0 END )*(IF(cast(CURRENT_TIMESTAMP as time) <= '13:00:00', (FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END), ((FLOOR(TIME_TO_SEC(TIMEDIFF(cast(CURRENT_TIMESTAMP as time), '07:00:00'))/60))-60)/AVG(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN master_plan.smv ELSE 0 END) ))) cumulative_target,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.plan_target ) ELSE 0 END) total_target,
                    COALESCE(line.sewing_line, master_plan.sewing_line) FullName,
                    COALESCE(line.sewing_line, master_plan.sewing_line) username,
                    SUM(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.jam_kerja ) ELSE 0 END) jam_kerja,
                    MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( DATE(master_plan.tgl_plan) ) ELSE 0 END) tgl_plan,
                    GREATEST(IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rfts.last_rft ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( defects.last_defect ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( reworks.last_rework ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)), IFNULL(MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( rejects.last_reject ) ELSE 0 END), MAX(CASE WHEN master_plan.tgl_plan ".$selectFilter." THEN ( master_plan.tgl_plan ) ELSE 0 END)) ) latest_output
                ")->
                leftJoin("userpassword", "userpassword.username", "=", "master_plan.sewing_line")->
                leftJoin("output_leader_line", function ($join) {
                    $join->on("output_leader_line.line_id", "=", "userpassword.line_id");
                    $join->on("output_leader_line.tanggal", "=", "master_plan.tgl_plan");
                })->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                join("so", "so.id_cost", "=", "act_costing.id")->
                join(DB::raw("(select * from so_det group by id_so, color) so_det"), function ($join) {
                    $join->on("so_det.id_so", "=", "so.id");
                    $join->on("so_det.color", "=", "master_plan.color");
                })->
                leftJoin(DB::raw("(
                    SELECT
                        master_plan.id_ws,
                        output_rfts".($this->qcType).".master_plan_id,
                        COALESCE(userpassword.username, master_plan.sewing_line) sewing_line
                    FROM
                        output_rfts".($this->qcType)."
                        ".($this->qcType != "_packing" ?
                        "LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts".($this->qcType).".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" :
                        "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->qcType).".created_by")."
                        LEFT JOIN master_plan on master_plan.id = output_rfts".($this->qcType).".master_plan_id
                    WHERE
                        output_rfts".($this->qcType).".created_by IS NOT NULL
                        AND output_rfts".($this->qcType).".updated_at ".$outputFilter."
                    GROUP BY
                        output_rfts".($this->qcType).".master_plan_id,
                        COALESCE(userpassword.username, master_plan.sewing_line)
                ) as line"), function ($join) {
                    $join->on("line.master_plan_id", "=", "master_plan.id");
                })->
                leftJoin(DB::raw("(SELECT max(rfts.updated_at) last_rft, count(rfts.id) rft, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rfts".$this->qcType." rfts inner join master_plan on master_plan.id = rfts.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rfts.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rfts.created_by")." where rfts.updated_at ".$outputFilter." and status = 'NORMAL' GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("line.sewing_line", "=", "rfts.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(defects.updated_at) last_defect, count(defects.id) defect, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->qcType." defects inner join master_plan on master_plan.id = defects.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defects.created_by")." where defects.defect_status = 'defect' and defects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("line.sewing_line", "=", "defects.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(defrew.updated_at) last_rework, count(defrew.id) rework, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_defects".$this->qcType." defrew inner join master_plan on master_plan.id = defrew.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = defrew.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = defrew.created_by")." where defrew.defect_status = 'reworked' and defrew.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("line.sewing_line", "=", "reworks.created_by"); } )->
                leftJoin(DB::raw("(SELECT max(rejects.updated_at) last_reject, count(rejects.id) reject, master_plan.id master_plan_id, COALESCE(userpassword.username, master_plan.sewing_line) created_by from output_rejects".$this->qcType." rejects inner join master_plan on master_plan.id = rejects.master_plan_id ".($this->qcType != "_packing" ? "LEFT JOIN user_sb_wip ON user_sb_wip.id = rejects.created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" : "LEFT JOIN userpassword ON userpassword.username = rejects.created_by")." where rejects.updated_at ".$outputFilter." GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at), COALESCE ( userpassword.username, master_plan.sewing_line ) ) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("line.sewing_line", "=", "rejects.created_by"); } )->
                where("master_plan.cancel", 'N')->
                whereRaw("(
                    COALESCE(line.sewing_line, master_plan.sewing_line) LIKE '%".$this->search."%' OR
                    act_costing.kpno LIKE '%".$this->search."%' OR
                    act_costing.styleno LIKE '%".$this->search."%'
                )")->
                groupByRaw("act_costing.styleno, COALESCE(line.sewing_line, master_plan.sewing_line)")->
                get();
            }
        } else {
            $masterPlans = collect([]);
            $orders = collect([]);
        }

        $defectTypes = DB::connection("mysql_sb")->table('output_defects'.$this->qcType)->
            selectRaw('defect_type_id, defect_type, count(defect_type_id) as defect_type_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects".$this->qcType.".master_plan_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=","output_defects".$this->qcType.".defect_type_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("output_defects".$this->qcType.".updated_at ".$outputFilter."")->
            groupBy("defect_type_id")->
            orderByRaw("defect_type_count desc")->limit(5)->get();

        $defectTypeIds = [];
        foreach ($defectTypes as $type) {
            array_push($defectTypeIds, $type->defect_type_id);
        }

        $defectAreas = DB::connection("mysql_sb")->table('output_defects'.$this->qcType)->
            selectRaw('defect_type_id, defect_area_id, defect_area, count(defect_area_id) as defect_area_count')->
            leftJoin("master_plan", "master_plan.id", "=","output_defects".$this->qcType.".master_plan_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=","output_defects".$this->qcType.".defect_area_id")->
            where("master_plan.cancel", 'N')->
            whereRaw("output_defects".$this->qcType.".updated_at ".$outputFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("defect_type_id", "defect_area_id")->
            orderByRaw("defect_area_count desc")->get();

        $defectAreaIds = [];
        foreach ($defectAreas as $area) {
            array_push($defectAreaIds, $area->defect_area_id);
        }

        $lineDefects = DB::connection("mysql_sb")->table('output_defects'.$this->qcType)->
            selectRaw("master_plan.sewing_line, output_defects".$this->qcType.".defect_type_id, output_defects".$this->qcType.".defect_area_id, count(*) as total")->
            leftJoin('master_plan', 'master_plan.id', 'output_defects'.$this->qcType.'.master_plan_id')->
            where("master_plan.cancel", 'N')->
            whereRaw("output_defects".$this->qcType.".updated_at ".$outputFilter."")->
            whereIn("defect_type_id", $defectTypeIds)->
            groupBy("master_plan.sewing_line", "output_defects".$this->qcType.".defect_type_id", "output_defects".$this->qcType.".defect_area_id")->get();

        return view('livewire.report-output', [
            'masterPlans' => $masterPlans,
            'lines' => $lines,
            'orders' => $orders,
            'defectTypes' => $defectTypes,
            'defectAreas' => $defectAreas,
            'lineDefects' => $lineDefects,
            'months' => $months,
            'years' => $years,
        ]);
    }
}
