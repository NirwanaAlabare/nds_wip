<?php

namespace App\Exports\Sewing;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
use DB;

class ReportHourlyExport implements FromView, WithTitle
{
    protected $tgl_skrg;
    protected $tgl_filter;
    protected $start_date;
    protected $end_date;
    protected $start_date_min_1;
    protected $end_date_min_1;
    protected $tgl_min_1;
    protected $start_date_min_2;
    protected $end_date_min_2;
    protected $tgl_min_2;

    public function __construct($tgl_skrg,$tgl_filter,$start_date,$end_date,$start_date_min_1,$end_date_min_1,$tgl_min_1,$start_date_min_2,$end_date_min_2,$tgl_min_2)
    {
        ini_set('max_execution_time', 3600); // boost only once here
        ini_set('memory_limit', '1024M'); // adjust as needed

        $this->tgl_skrg = $tgl_skrg;
        $this->tgl_filter = $tgl_filter;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->start_date_min_1 = $start_date_min_1;
        $this->end_date_min_1 = $end_date_min_1;
        $this->tgl_min_1 = $tgl_min_1;
        $this->start_date_min_2 = $start_date_min_2;
        $this->end_date_min_2 = $end_date_min_2;
        $this->tgl_min_2 = $tgl_min_2;
    }

    public function title(): string
    {
        return $this->tgl_filter;
    }

    public function view(): View
    {
        $data_tracking = collect(DB::connection('mysql_sb')->select("SELECT
            a.tgl_trans,
            concat((DATE_FORMAT(tgl_trans,  '%d')), '-',left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')) tgl_trans_fix,
            concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
            REPLACE(ul.username, '_', ' ') sewing_line,
            SUBSTRING_INDEX(ol.chief_name, ' ', 1) AS nm_chief,
            ol.leader_nik nik_leader,
            SUBSTRING_INDEX(ol.leader_name, ' ', 1) AS nm_leader,
            ms.supplier buyer,
            ac.kpno,
            sd.styleno_prod,
            mp.color,
            m.product_group,
            if(td.tot_days is null or '0', '1',td.tot_days + 1) tot_days,
            mp.id,
            mp.smv,
            mp.man_power man_power_ori,
            cmp.man_power,
            mp.jam_kerja_awal,
            istirahat,
            op.jam_akhir_input_line,
            round(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600,2) AS jam_kerja_act_line,
            round(((((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)) * 60) * cmp.man_power) / mp.smv) target,
            sum(a.tot_output) tot_output,
            sum(d_rfts.tot_rfts) tot_rfts,
            op.tot_output_line,
            round((cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
            round(sum(a.tot_output) * mp.smv,2) mins_prod,
            concat(round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2), ' %') eff_line,
            round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line_angka,
            round(((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)),2) jam_kerja_act,
            round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts,
            sum(jam_1) o_jam_1,
            sum(jam_2) o_jam_2,
            sum(jam_3) o_jam_3,
            sum(jam_4) o_jam_4,
            sum(jam_5) o_jam_5,
            sum(jam_6) o_jam_6,
            sum(jam_7) o_jam_7,
            sum(jam_8) o_jam_8,
            sum(jam_9) o_jam_9,
            sum(jam_10) o_jam_10,
            sum(jam_11) o_jam_11,
            sum(jam_12) o_jam_12,
            sum(jam_13) o_jam_13,
            jk.set_target set_target_perhari,
            mp.target_effy,
            jk.jam_kerja jam_kerja,
            round((((jk.jam_kerja * 60) * cmp.man_power) / mp.smv),0) target_100,
            round(((((jk.jam_kerja * 60) * cmp.man_power) / mp.smv) * mp.target_effy) / 100,0) target_output_eff,
            case when
                    IF(
                    INSTR(CAST(if (jk.jam_kerja < 1,jk.set_target , jk.set_target  / jk.jam_kerja) AS CHAR), '.') > 0,
                    SUBSTRING(SUBSTRING_INDEX(CAST(if (jk.jam_kerja < 1,jk.set_target , jk.set_target  / jk.jam_kerja) AS CHAR), '.', -1), 1, 1),
                    '0'
                ) >= 5 then CEILING(if (jk.jam_kerja < 1,jk.set_target , jk.set_target  / jk.jam_kerja))
                    else
                    floor(if (jk.jam_kerja < 1,jk.set_target , jk.set_target  / jk.jam_kerja))
            end as plan_target_perjam,
            if (td.eff is not null, concat(coalesce(td.eff,0), ' %'), '-') kemarin_1,
            if (td.eff_1 is not null, concat(coalesce(td.eff_1,0), ' %'), '-') kemarin_2,
            concat(coalesce(e_skrg.eff_skrg,0), ' %') eff_skrg,
            coalesce(e_skrg.eff_skrg,0) eff_skrg_angka,
            if (td.eff is not null, td.eff, '0') kemarin_1_angka,
            if (td.eff is not null, td.eff_1, '0') kemarin_2_angka
            from
            (
                select
                date(updated_at)tgl_trans,
                so_det_id,
                master_plan_id,
                count(so_det_id) tot_output,
            COUNT(CASE WHEN jam = 1 THEN so_det_id END) AS jam_1,
            COUNT(CASE WHEN jam = 2 THEN so_det_id END) AS jam_2,
            COUNT(CASE WHEN jam = 3 THEN so_det_id END) AS jam_3,
            COUNT(CASE WHEN jam = 4 THEN so_det_id END) AS jam_4,
            COUNT(CASE WHEN jam = 5 THEN so_det_id END) AS jam_5,
            COUNT(CASE WHEN jam = 6 THEN so_det_id END) AS jam_6,
            COUNT(CASE WHEN jam = 7 THEN so_det_id END) AS jam_7,
            COUNT(CASE WHEN jam = 8 THEN so_det_id END) AS jam_8,
            COUNT(CASE WHEN jam = 9 THEN so_det_id END) AS jam_9,
            COUNT(CASE WHEN jam = 10 THEN so_det_id END) AS jam_10,
            COUNT(CASE WHEN jam = 11 THEN so_det_id END) AS jam_11,
            COUNT(CASE WHEN jam = 12 THEN so_det_id END) AS jam_12,
            COUNT(CASE WHEN jam = 13 THEN so_det_id END) AS jam_13,
                time(max(a.updated_at)) jam_akhir_input,
                created_by
                from output_rfts a
                    left join dim_jam_kerja_sewing b on time(a.updated_at) >= b.jam_kerja_awal and time(a.updated_at) <= b.jam_kerja_akhir
                where updated_at >= '$this->start_date' and updated_at <= '$this->end_date'
                group by master_plan_id, created_by, date(updated_at)
            )		a
            inner join so_det sd on a.so_det_id = sd.id
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join masterproduct m on ac.id_product = m.id
            inner join user_sb_wip u on a.created_by = u.id
            inner join userpassword ul on ul.line_id = u.line_id
            inner join master_plan mp on a.master_plan_id = mp.id
            inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
            left join (
            select date(output_rfts.updated_at) tgl_trans_line,max(time(output_rfts.updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
                case
                when time(max(output_rfts.updated_at)) >= '12:00:00' and time(max(output_rfts.updated_at)) <= '18:44:59' THEN '01:00:00'
                when time(max(output_rfts.updated_at)) <= '12:00:00'  THEN '00:00:00'
                when time(max(output_rfts.updated_at)) >= '18:45:00'  THEN '01:30:00'
                END as istirahat,
            REPLACE(ul.username, '_', ' ') username
            from output_rfts
            inner join user_sb_wip u on output_rfts.created_by = u.id
            inner join userpassword ul on ul.line_id = u.line_id
            where output_rfts.updated_at >= '$this->start_date' and output_rfts.updated_at <= '$this->end_date' group by REPLACE(ul.username, '_', ' '), date(output_rfts.updated_at)
            ) op on a.tgl_trans = op.tgl_trans_line and REPLACE(ul.username, '_', ' ') = op.username
            left join (
            select * from act_costing_mfg where id_item = '8' group by id_act_cost
            ) acm on ac.id = acm.id_act_cost
            left join (
            SELECT
            master_plan_id,
            tgl_trans_rfts,
            sum(tot_rfts)tot_rfts
            from
            (
                select
                date(updated_at)tgl_trans_rfts,
                master_plan_id,
                count(so_det_id) tot_rfts,
                created_by
                from output_rfts a
                where updated_at >= '$this->start_date' and updated_at <= '$this->end_date' and status = 'NORMAL'
                group by master_plan_id, created_by, date(updated_at)
            )		a
            inner join master_plan mp on a.master_plan_id = mp.id
            group by tgl_trans_rfts, master_plan_id
            ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
            left join
            (
            select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$this->start_date' and  date(tgl_plan) <= '$this->end_date' and cancel = 'N' group by sewing_line, tgl_plan
            ) cmp on a.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line
            left join
            (
                        select
                        sewing_line,
                        sum(mins_avail),
                        sum(mins_prod),
                        round(sum(mins_prod) / sum(mins_avail) * 100,2) eff_skrg
                        from (
                        select
                        REPLACE(ul.username, '_', ' ') sewing_line,
                        round((cmp.man_power * (sum(z.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
                        round(sum(z.tot_output) * mp.smv,2) mins_prod
                        from (
                                select
                            date(updated_at)tgl_trans,
                            so_det_id,
                            master_plan_id,
                            count(so_det_id) tot_output,
                            time(max(a.updated_at)) jam_akhir_input,
                            created_by
                            from output_rfts a
                                left join dim_jam_kerja_sewing b on time(a.updated_at) >= b.jam_kerja_awal and time(a.updated_at) <= b.jam_kerja_akhir
                            where updated_at >= '$this->start_date' and updated_at <= '$this->end_date'
                            group by master_plan_id, created_by, date(updated_at)
                        )z
                        inner join so_det sd on z.so_det_id = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join user_sb_wip u on z.created_by = u.id
                        inner join userpassword ul on ul.line_id = u.line_id
                        inner join master_plan mp on z.master_plan_id = mp.id
                        left join (
                        select date(output_rfts.updated_at) tgl_trans_line,max(time(output_rfts.updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
                            case
                            when time(max(output_rfts.updated_at)) >= '12:00:00' and time(max(output_rfts.updated_at)) <= '18:44:59' THEN '01:00:00'
                            when time(max(output_rfts.updated_at)) <= '12:00:00'  THEN '00:00:00'
                            when time(max(output_rfts.updated_at)) >= '18:45:00'  THEN '01:30:00'
                            END as istirahat,
                        REPLACE('_', ' ', userpassword.username) username
                        from output_rfts
                        left join user_sb_wip on output_rfts.created_by = user_sb_wip.id
                        left join userpassword on user_sb_wip.line_id = userpassword.line_id
                        where output_rfts.updated_at >= '$this->start_date' and output_rfts.updated_at <= '$this->end_date' group by REPLACE('_', ' ', userpassword.username), date(output_rfts.updated_at)
                        ) op on z.tgl_trans = op.tgl_trans_line and REPLACE(ul.username, '_', ' ') = op.username
                        left join
                        (
                        select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$this->start_date' and  date(tgl_plan) <= '$this->end_date' and cancel = 'N' group by sewing_line, tgl_plan
                        ) cmp on z.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line
                        group by REPLACE(ul.username, '_', ' '), ac.kpno, ac.Styleno, z.tgl_trans
                        ) eff_hr_ini
                        group by sewing_line
            ) e_skrg on REPLACE(ul.username, '_', ' ') = e_skrg.sewing_line

            left join
            (
            select format(sum(mp.jam_kerja),1) jam_kerja, REPLACE(ul.username, '_', ' ') username, sd.styleno_prod, sum(mp.set_target) set_target
            from
                (
                select master_plan_id, created_by, so_det_id from output_rfts
                where updated_at >= '$this->start_date' and updated_at <= '$this->end_date' and status = 'NORMAL'
                GROUP BY master_plan_id, created_by
                ) a
                inner join master_plan mp on a.master_plan_id = mp.id
                inner join so_det sd on a.so_det_id = sd.id
                inner join user_sb_wip u on a.created_by = u.id
                inner join userpassword ul on ul.line_id = u.line_id
                group by REPLACE(ul.username, '_', ' '), sd.styleno_prod
            ) jk on REPLACE(ul.username, '_', ' ') = jk.username and sd.styleno_prod = jk.styleno_prod

            left join
            (
                    select sewing_line, styleno,
                    if((sequence_number_skrg - sequence_number) > 1, '0',a.td) tot_days,
                    if((sequence_number_skrg - sequence_number) > 1, null,eff) eff,
                    if((sequence_number_skrg - sequence_number) > 1, null,eff_1) eff_1,
                            if((sequence_number_skrg - sequence_number) > 1, null,eff_2) eff_2
                    from
                    (
                                SELECT
                                t.tanggal,
                                t.sewing_line,
                                t.styleno,
                                t.tot_days td,
                                t.eff,
                                t.eff_1,
                                t.eff_2,
                                '$this->tgl_filter' tgl_skrg
                            FROM
                                rep_hourly_output_tot_days t
                            WHERE
                                t.tanggal = (
                                    SELECT MAX(t2.tanggal)
                                    FROM rep_hourly_output_tot_days t2
                                    WHERE t2.tanggal < '$this->tgl_filter'
                                    AND t2.sewing_line = t.sewing_line
                                )
                                AND t.tanggal < '$this->tgl_filter'
                                            order by sewing_line asc
                    )	a
                    left join
                    (
                    SELECT
                        ROW_NUMBER() OVER (ORDER BY update_date) AS sequence_number,
                        update_date
                    FROM (
                        SELECT
                            DATE(updated_at) AS update_date
                        FROM
                            output_rfts
                        WHERE
                            updated_at >= '2025-04-06 00:00:00'
                        GROUP BY
                            DATE(updated_at)
                    ) AS tbl_tgl
                    ) b on a.tanggal = b.update_date
                    left join
                    (
                    SELECT
                        ROW_NUMBER() OVER (ORDER BY update_date_skrg) AS sequence_number_skrg,
                        update_date_skrg
                    FROM (
                        SELECT
                            DATE(updated_at) AS update_date_skrg
                        FROM
                            output_rfts
                        WHERE
                            updated_at >= '2025-04-06 00:00:00'
                        GROUP BY
                            DATE(updated_at)
                    ) AS tbl_tgl
                    ) c on a.tgl_skrg = c.update_date_skrg
                    GROUP BY sewing_line, styleno
            ) td on REPLACE(ul.username, '_', ' ') = td.sewing_line and sd.styleno_prod = td.styleno
            left join output_employee_line ol on a.tgl_trans = ol.tanggal and ul.line_id = ol.line_id
            group by REPLACE(ul.username, '_', ' '), sd.styleno_prod, a.tgl_trans
            order by a.tgl_trans asc, REPLACE(ul.username, '_', ' ') asc, sd.styleno_prod asc
        "));

        $total_mins_avail = $data_tracking->sum('mins_avail');
        $total_mins_prod = $data_tracking->sum('mins_prod');

        $tot_eff_percent = $total_mins_avail > 0 ? round(($total_mins_prod  / $total_mins_avail) * 100, 2) . ' %' : 0;

        $tot_eff = $total_mins_avail > 0 ? round(($total_mins_prod  / $total_mins_avail) * 100, 2) : 0;

        return view('sewing.export.report-hourly-export',[

            'data' => $data_tracking,

            'tot_eff' => $tot_eff,

            'tot_eff_percent' => $tot_eff_percent
        ]);
    }
}
