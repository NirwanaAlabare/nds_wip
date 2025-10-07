<?php

namespace App\Http\Controllers;

use App\Exports\Sewing\ReportHourlyExportMonthly;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPPICTracking;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
use App\Exports\Sewing\ReportHourlyExport;
use App\Imports\ImportPPIC_SO;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Style;

class ReportHourlyController extends Controller
{
    public function index(Request $request)
    {

        $tgl_skrg = date('Y-m-d');
        $tgl_filter = $request->tgl_filter;

        $start_date = $tgl_filter . ' 00:00:00';
        $end_date = $tgl_filter . ' 23:59:59';

        $start_date_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day')) . ' 00:00:00';
        $end_date_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day')) . ' 23:59:59';
        $tgl_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day'));

        $start_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 00:00:00';
        $end_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 23:59:59';
        $tgl_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day'));

        // $start_date = '2024-10-29 00:00:00';
        // $end_date = '2024-10-29 23:59:59';
        // $end_date_min_1 = '2024-10-28 23:59:59';
        // $end_date_min_2 = '2024-10-27 23:59:59';
        // $start_date_min_2 = '2024-10-27 00:00:00';

        // dd($tgl_filter, $start_date, $end_date);

        $user = Auth::user()->name;
        if ($request->ajax()) {

            // $cek_trans = DB::connection('mysql_sb')->select("
            //             SELECT tgl_update from rep_hourly_output_hist_trans where tgl_update = '$tgl_skrg'");
            // $cek_trans_input = $cek_trans[0]->tgl_update ?? null;

            // if ($cek_trans_input === null) {

            //     $del_data = DB::connection('mysql_sb')->delete("
            //                 DELETE FROM rep_hourly_output_hist_trans");

            //     $ins_data = DB::connection('mysql_sb')->insert("
            //                 INSERT INTO rep_hourly_output_hist_trans (tgl_trans, sewing_line, styleno, kpno, tgl_update)
            //                 select
            //                 date(a.updated_at) tgl_trans,
            //                 u.name,
            //                 ac.styleno,
            //                 ac.kpno,
            //                 '$tgl_skrg' tgl_update
            //                 from
            //                 (
            //                 select * from output_rfts
            //                 where date(updated_at) >= '2024-09-01'
            //                 group by master_plan_id, date(updated_at), created_by
            //                 ) a
            //                 inner join user_sb_wip u on a.created_by = u.id
            //                 inner join master_plan mp on a.master_plan_id = mp.id
            //                 inner join act_costing ac on mp.id_ws = ac.id
            //                 order by date(a.updated_at) asc
            //                 ");
            // }

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
    u.name sewing_line,
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
    inner join master_plan mp on a.master_plan_id = mp.id
    inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
    left join (
    select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
        case
        when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
        when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
        when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
        END as istirahat,
    created_by
    from output_rfts
    where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59' group by created_by, date(updated_at)
    ) op on a.tgl_trans = op.tgl_trans_line and a.created_by = op.created_by
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
    ) cmp on a.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
    left join
    (
                select
                sewing_line,
                sum(mins_avail),
                sum(mins_prod),
                round(sum(mins_prod) / sum(mins_avail) * 100,2) eff_skrg
                from (
                select
                u.name sewing_line,
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
                        inner join master_plan mp on z.master_plan_id = mp.id
                left join (
                select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
                    case
                    when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
                    when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
                    when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
                    END as istirahat,
                created_by
                from output_rfts
                where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59' group by created_by, date(updated_at)
                ) op on z.tgl_trans = op.tgl_trans_line and z.created_by = op.created_by
                left join
                (
                select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$tgl_cek 00:00:00' and  date(tgl_plan) <= '$tgl_cek 23:59:59' and cancel = 'N' group by sewing_line, tgl_plan
                ) cmp on z.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
                group by u.name, sd.styleno_prod, z.tgl_trans
                ) eff_hr_ini
                group by sewing_line
    ) e_skrg on u.name = e_skrg.sewing_line
    left join
    (
    select sum(mp.jam_kerja) jam_kerja, u.name, sd.styleno_prod, sum(mp.set_target) set_target, ac.kpno
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
        group by a.created_by, sd.styleno_prod
    ) jk on u.name = jk.name and sd.styleno_prod = jk.styleno_prod and ac.kpno = jk.kpno
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
) td on u.name = td.sewing_line and sd.styleno_prod = td.styleno
    group by u.name, sd.styleno_prod, a.tgl_trans
    order by a.tgl_trans asc, u.name asc, sd.styleno_prod asc
                                ");
                }
            }

            $data_tracking = DB::connection('mysql_sb')->select("SELECT
a.tgl_trans,
concat((DATE_FORMAT(tgl_trans,  '%d')), '-',left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')) tgl_trans_fix,
concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
u.name sewing_line,
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
    where updated_at >= '$start_date' and updated_at <= '$end_date'
    group by master_plan_id, created_by, date(updated_at)
)		a
inner join so_det sd on a.so_det_id = sd.id
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join masterproduct m on ac.id_product = m.id
inner join user_sb_wip u on a.created_by = u.id
inner join master_plan mp on a.master_plan_id = mp.id
inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join (
select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
    case
    when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
    when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
    when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
    END as istirahat,
created_by
from output_rfts
where updated_at >= '$start_date' and updated_at <= '$end_date' group by created_by, date(updated_at)
) op on a.tgl_trans = op.tgl_trans_line and a.created_by = op.created_by
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
    where updated_at >= '$start_date' and updated_at <= '$end_date' and status = 'NORMAL'
    group by master_plan_id, created_by, date(updated_at)
)		a
inner join master_plan mp on a.master_plan_id = mp.id
group by tgl_trans_rfts, master_plan_id
) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
left join
(
select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$start_date' and  date(tgl_plan) <= '$end_date' and cancel = 'N' group by sewing_line, tgl_plan
) cmp on a.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
left join
(
            select
            sewing_line,
            sum(mins_avail),
            sum(mins_prod),
            round(sum(mins_prod) / sum(mins_avail) * 100,2) eff_skrg
            from (
            select
            u.name sewing_line,
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
                where updated_at >= '$start_date' and updated_at <= '$end_date'
                group by master_plan_id, created_by, date(updated_at)
            )z
            inner join so_det sd on z.so_det_id = sd.id
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
                    inner join user_sb_wip u on z.created_by = u.id
                    inner join master_plan mp on z.master_plan_id = mp.id
            left join (
            select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
                case
                when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
                when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
                when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
                END as istirahat,
            created_by
            from output_rfts
            where updated_at >= '$start_date' and updated_at <= '$end_date' group by created_by, date(updated_at)
            ) op on z.tgl_trans = op.tgl_trans_line and z.created_by = op.created_by
            left join
            (
            select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$start_date' and  date(tgl_plan) <= '$end_date' and cancel = 'N' group by sewing_line, tgl_plan
            ) cmp on z.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
            group by u.name, ac.kpno, ac.Styleno, z.tgl_trans
            ) eff_hr_ini
            group by sewing_line
) e_skrg on u.name = e_skrg.sewing_line

left join
(
select format(sum(mp.jam_kerja),1) jam_kerja, u.name, sd.styleno_prod, sum(mp.set_target) set_target
from
	(
	select master_plan_id, created_by, so_det_id from output_rfts
	where updated_at >= '$start_date' and updated_at <= '$end_date' and status = 'NORMAL'
	GROUP BY master_plan_id, created_by
	) a
	inner join master_plan mp on a.master_plan_id = mp.id
	inner join so_det sd on a.so_det_id = sd.id
	inner join user_sb_wip u on a.created_by = u.id
	group by a.created_by, sd.styleno_prod
) jk on u.name = jk.name and sd.styleno_prod = jk.styleno_prod

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
                    '$tgl_filter' tgl_skrg
                FROM
                    rep_hourly_output_tot_days t
                WHERE
                    t.tanggal = (
                        SELECT MAX(t2.tanggal)
                        FROM rep_hourly_output_tot_days t2
                        WHERE t2.tanggal < '$tgl_filter'
                        AND t2.sewing_line = t.sewing_line
                    )
                    AND t.tanggal < '$tgl_filter'
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
) td on u.name = td.sewing_line and sd.styleno_prod = td.styleno
left join output_employee_line ol on a.tgl_trans = ol.tanggal and u.name	= ol.line_name
group by u.name, sd.styleno_prod, a.tgl_trans
order by a.tgl_trans asc, u.name asc, sd.styleno_prod asc
");

            $total_mins_avail = array_sum(array_column($data_tracking, 'mins_avail'));
            $total_mins_prod = array_sum(array_column($data_tracking, 'mins_prod'));

            $tot_eff_percent = round(($total_mins_prod  / $total_mins_avail) * 100, 2) . ' %';

            $tot_eff = round(($total_mins_prod  / $total_mins_avail) * 100, 2);

            // return DataTables::of($data_tracking)->toJson();

            return response()->json([

                'data' => $data_tracking,

                'tot_eff' => $tot_eff,

                'tot_eff_percent' => $tot_eff_percent

            ]);
        }

        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view(
            'ppic.report_hourly',
            [
                'page' => 'dashboard-sewing-eff',
                "subPageGroup" => "sewing-sewing",
                "subPage" => "report-hourly",
                "containerFluid" => true,
                "user" => $user,
                "months" => $months,
                "years" => $years,
            ]
        );
    }

    // $no = 0;
    // foreach ($data_tracking as $key => $value) {
    //     $i_buyer = $data_tracking[$no]->buyer;
    //     $i_ws = $data_tracking[$no]->ws;
    //     $i_color = $data_tracking[$no]->color;
    //     $i_size = $data_tracking[$no]->size;
    //     $i_tot_qc = $data_tracking[$no]->tot_qc;

    //     $insert_mut =  DB::insert("
    //         insert into ppic_laporan_tracking_tmp_qc_output
    //         (buyer,ws,color,size,tot_qc,created_by,created_at,updated_at)
    //         values('$i_buyer','$i_ws','$i_color','$i_size','$i_tot_qc','$user','$timestamp','$timestamp')");
    //     $no++;
    // }

    public function export_excel_tracking(Request $request)
    {
        $user = Auth::user()->name;
        return Excel::download(new ExportLaporanPPICTracking($request->buyer, $user), 'Laporan_Tracking.xlsx');
    }

    public function exportExcelHourlyMonthly(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        return Excel::download(new ReportHourlyExportMonthly($month,$year), "Report Hourly Monthly ".$year."-".$month.".xlsx");
    }

    public function exportExcelHourly(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_filter = $request->tgl_filter;

        $start_date = $tgl_filter . ' 00:00:00';
        $end_date = $tgl_filter . ' 23:59:59';

        $start_date_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day')) . ' 00:00:00';
        $end_date_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day')) . ' 23:59:59';
        $tgl_min_1 = date('Y-m-d', strtotime($tgl_filter . ' -1 day'));

        $start_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 00:00:00';
        $end_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 23:59:59';
        $tgl_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day'));

        $user = Auth::user()->name;

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
                        u.name sewing_line,
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
                        inner join master_plan mp on a.master_plan_id = mp.id
                        inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        left join (
                        select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
                            case
                            when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
                            when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
                            when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
                            END as istirahat,
                        created_by
                        from output_rfts
                        where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59' group by created_by, date(updated_at)
                        ) op on a.tgl_trans = op.tgl_trans_line and a.created_by = op.created_by
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
                        ) cmp on a.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
                        left join
                        (
                                    select
                                    sewing_line,
                                    sum(mins_avail),
                                    sum(mins_prod),
                                    round(sum(mins_prod) / sum(mins_avail) * 100,2) eff_skrg
                                    from (
                                    select
                                    u.name sewing_line,
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
                                            inner join master_plan mp on z.master_plan_id = mp.id
                                    left join (
                                    select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
                                        case
                                        when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
                                        when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
                                        when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
                                        END as istirahat,
                                    created_by
                                    from output_rfts
                                    where updated_at >= '$tgl_cek 00:00:00' and updated_at <= '$tgl_cek 23:59:59' group by created_by, date(updated_at)
                                    ) op on z.tgl_trans = op.tgl_trans_line and z.created_by = op.created_by
                                    left join
                                    (
                                    select min(id), man_power, sewing_line, tgl_plan from master_plan where date(tgl_plan) >= '$tgl_cek 00:00:00' and  date(tgl_plan) <= '$tgl_cek 23:59:59' and cancel = 'N' group by sewing_line, tgl_plan
                                    ) cmp on z.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
                                    group by u.name, sd.styleno_prod, z.tgl_trans
                                    ) eff_hr_ini
                                    group by sewing_line
                        ) e_skrg on u.name = e_skrg.sewing_line
                        left join
                        (
                        select sum(mp.jam_kerja) jam_kerja, u.name, sd.styleno_prod, sum(mp.set_target) set_target, ac.kpno
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
                            group by a.created_by, sd.styleno_prod
                        ) jk on u.name = jk.name and sd.styleno_prod = jk.styleno_prod and ac.kpno = jk.kpno
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
                    ) td on u.name = td.sewing_line and sd.styleno_prod = td.styleno
                        group by u.name, sd.styleno_prod, a.tgl_trans
                        order by a.tgl_trans asc, u.name asc, sd.styleno_prod asc
                                                    ");
            }
        }

        // return DataTables::of($data_tracking)->toJson();

        return Excel::download(new ReportHourlyExport($tgl_skrg,$tgl_filter,$start_date,$end_date,$start_date_min_1,$end_date_min_1,$tgl_min_1,$start_date_min_2,$end_date_min_2,$tgl_min_2), "Report Hourly.xlsx");
    }
}
