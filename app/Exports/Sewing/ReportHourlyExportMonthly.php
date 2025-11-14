<?php
    namespace App\Exports\Sewing;

    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithMultipleSheets;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use DB;

    class ReportHourlyExportMonthly implements WithMultipleSheets, ShouldQueue
    {
        use Exportable;

        protected $month;
        protected $year;

        public function __construct($month, $year)
        {
            ini_set('max_execution_time', 3600); // boost only once here
            ini_set('memory_limit', '1024M'); // adjust as needed

            $this->month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $this->year = $year;
        }

        public function sheets(): array
        {
            $sheets = [];

            // Day Count
            $dayCount = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);

            // Loop over Day Count
            for ($i = 1; $i <= $dayCount; $i++) {
                $date = str_pad($i, 2, '0', STR_PAD_LEFT);

                $tgl_skrg = date('Y-m-d');
                $tgl_filter = $this->year."-".$this->month."-".$date;

                $start_date = $tgl_filter . ' 00:00:00';
                $end_date = $tgl_filter . ' 23:59:59';

                $start_date_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day')) . ' 00:00:00';
                $end_date_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day')) . ' 23:59:59';
                $tgl_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day'));

                $start_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 00:00:00';
                $end_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 23:59:59';
                $tgl_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day'));

                $cek_trans_terakhir = DB::connection('mysql_sb')->select("SELECT tgl_trans from
                        (
                        select date(updated_at) tgl_trans from output_rfts where updated_at >= '2025-04-06 00:00:00'
                        group by date(updated_at)
                        ) a
                        left join
                        (
                        select tanggal from rep_hourly_output_tot_days group by tanggal
                        ) b on a.tgl_trans = b.tanggal
                        where tgl_trans < '$tgl_skrg' and tanggal is null");

                $tgl_cek = $cek_trans_terakhir[0]->tgl_trans ?? null;

                foreach ($cek_trans_terakhir as $trans) {
                    $tgl_cek = $trans->tgl_trans;
                    if ($tgl_cek != null) {

                    $insert = DB::connection('mysql_sb')->insert("INSERT into rep_hourly_output_tot_days
                            (tanggal, sewing_line, styleno,tot_days, eff, eff_1, eff_2)
                            SELECT
                            a.tgl_trans,
                            REPLACE(ul.username, '_', ' ') sewing_line,
                            sd.styleno_prod,
                            if(td.tot_days is not null, td.tot_days + 1, '1') tot_days,
                        round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line_angka,
                            if (td.eff is not null, coalesce(td.eff,0), null) kemarin_1,
                            if (td.eff_1 is not null, coalesce(td.eff_1,0), null) kemarin_2
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
                                where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59'
                                group by master_plan_id, created_by, date(updated_at)
                            )		a
                            inner join so_det sd on a.so_det_id = sd.id
                            inner join so on sd.id_so = so.id
                            inner join act_costing ac on so.id_cost = ac.id
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
                            REPLACE(userpassword.username, '_', ' ') username
                            from output_rfts
                            left join user_sb_wip on output_rfts.created_by = user_sb_wip.id
                            left join userpassword on user_sb_wip.line_id = userpassword.line_id
                            where output_rfts.updated_at >= '$tgl_cek 00:00:00' and output_rfts.updated_at <= '$tgl_cek 23:59:59' group by REPLACE(userpassword.username, '_', ' '), date(output_rfts.updated_at)
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
                                where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59' and status = 'NORMAL'
                                group by master_plan_id, created_by, date(updated_at)
                            )		a
                            inner join master_plan mp on a.master_plan_id = mp.id
                            group by tgl_trans_rfts, master_plan_id
                            ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
                            left join
                            (
                            select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$tgl_cek 00:00:00' and  date(tgl_plan) <= '$tgl_cek 23:59:59' and cancel = 'N' group by sewing_line, tgl_plan
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
                                            where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59'
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
                                            REPLACE(userpassword.username, '_', ' ') username
                                        from output_rfts
                                        left join user_sb_wip on output_rfts.created_by = user_sb_wip.id
                                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                                        where output_rfts.updated_at >= '$tgl_cek 00:00:00' and output_rfts.updated_at <= '$tgl_cek 23:59:59' group by created_by, date(output_rfts.updated_at)
                                        ) op on z.tgl_trans = op.tgl_trans_line and REPLACE(ul.username, '_', ' ') = op.username
                                        left join
                                        (
                                        select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$tgl_cek 00:00:00' and  date(tgl_plan) <= '$tgl_cek 23:59:59' and cancel = 'N' group by sewing_line, tgl_plan
                                        ) cmp on z.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line
                                        group by REPLACE(ul.username, '_', ' '), sd.styleno_prod, z.tgl_trans
                                        ) eff_hr_ini
                                        group by sewing_line
                            ) e_skrg on REPLACE(ul.username, '_', ' ') = e_skrg.sewing_line
                            left join
                            (
                            select sum(mp.jam_kerja) jam_kerja, REPLACE(ul.username, '_', ' ') username, sd.styleno_prod, sum(mp.set_target) set_target, ac.kpno
                            from
                                (
                                select master_plan_id, created_by, so_det_id from output_rfts
                                where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59' and status = 'NORMAL'
                                GROUP BY master_plan_id, created_by
                                ) a
                                inner join master_plan mp on a.master_plan_id = mp.id
                                inner join act_costing ac on mp.id_ws = ac.id
                                        inner join so_det sd on a.so_det_id = sd.id
                                inner join user_sb_wip u on a.created_by = u.id
                                inner join userpassword ul on ul.line_id = u.line_id
                                group by REPLACE(ul.username, '_', ' '), sd.styleno_prod
                            ) jk on REPLACE(ul.username, '_', ' ') = jk.username and sd.styleno_prod = jk.styleno_prod and ac.kpno = jk.kpno
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
                                            '$tgl_cek' tgl_skrg
                                        FROM
                                            rep_hourly_output_tot_days t
                                        WHERE
                                            t.tanggal = (
                                                SELECT MAX(t2.tanggal)
                                                FROM rep_hourly_output_tot_days t2
                                                WHERE t2.tanggal < '$tgl_cek'
                                                AND t2.sewing_line = t.sewing_line
                                            )
                                            AND t.tanggal < '$tgl_cek'
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
                        ) td on REPLACE(ul.username, '_', ' ') = td.sewing_line and sd.styleno_prod = td.styleno
                            group by REPLACE(ul.username, '_', ' '), sd.styleno_prod, a.tgl_trans
                            order by a.tgl_trans asc, REPLACE(ul.username, '_', ' ') asc, sd.styleno_prod asc
                        ");
                    }
                }

                // checking if there is any plan in the day
                $dayPlan = DB::connection("mysql_sb")->table("master_plan")->select("id")->where("tgl_plan", $tgl_filter)->count();
                if ($dayPlan > 0) {
                    $sheets[] = new ReportHourlyExport($tgl_skrg,$tgl_filter,$start_date,$end_date,$start_date_min_1,$end_date_min_1,$tgl_min_1,$start_date_min_2,$end_date_min_2,$tgl_min_2);
                }
            }

            // if(count($sheets) < 1) {
            //     $sheets[] = new NoDataExport();
            // }

            return $sheets;
        }
    }
?>
