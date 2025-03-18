<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class ChiefSewingRangeExport implements FromView, ShouldAutoSize
{
    protected $from;
    protected $to;

    function __construct($from, $to) {
        $this->from = $from;
        $this->to = $to;
    }

    public function view(): View
    {
        $chiefPerformance = collect(DB::connection("mysql_sb")->select("
            select
                *,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail_old,
                SUM(cumulative_mins_avail) mins_avail
            from (
                select
                    output_employee_line.*,
                    output.sewing_line,
                    SUM(rft) rft,
                    SUM(output) output,
                    SUM(mins_prod) mins_prod,
                    SUM(mins_avail) mins_avail,
                    SUM(cumulative_mins_avail) cumulative_mins_avail
                from
                    output_employee_line
                    left join userpassword on userpassword.line_id = output_employee_line.line_id
                    inner join (
                        SELECT
                            output.tgl_output,
                            output.tgl_plan,
                            output.sewing_line,
                            SUM(rft) rft,
                            SUM(output) output,
                            SUM(output * output.smv) mins_prod,
                            SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                            MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                            MAX(output.last_update) last_update,
                            (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                            (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                            MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                            FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                        FROM
                            (
                                SELECT
                                    DATE( rfts.updated_at ) tgl_output,
                                    COUNT( rfts.id ) output,
                                    SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                    MAX(rfts.updated_at) last_update,
                                    master_plan.id master_plan_id,
                                    master_plan.tgl_plan,
                                    master_plan.sewing_line,
                                    master_plan.man_power,
                                    master_plan.jam_kerja,
                                    master_plan.smv
                                FROM
                                    output_rfts rfts
                                    inner join master_plan on master_plan.id = rfts.master_plan_id
                                where
                                    rfts.updated_at >= '".$this->from." 00:00:00' AND rfts.updated_at <= '".$this->to." 23:59:59'
                                    AND master_plan.tgl_plan >= DATE_SUB('".$this->from."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$this->to."'
                                    AND master_plan.cancel = 'N'
                                GROUP BY
                                    master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                                order by
                                    sewing_line
                            ) output
                        GROUP BY
                            output.sewing_line,
                            output.tgl_output
                    ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
                group by
                    tanggal,
                    leader_id,
                    chief_id,
                    line_id
                order by
                    chief_id asc,
                    tanggal asc
            ) chief_sewing
            group by
                chief_id,
                tanggal
            order by
                chief_id,
                tanggal
        "));

        return view('sewing.export.chief-sewing-range-export', [
            'from' => $this->from,
            'to' => $this->to,
            'chiefPerformance' => $chiefPerformance
        ]);
    }
}
