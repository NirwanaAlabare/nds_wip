<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPPICTracking;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
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

            $cek_trans = DB::connection('mysql_sb')->select("
            SELECT tgl_update from rep_hourly_output_hist_trans where tgl_update = '$tgl_skrg'");
            $cek_trans_input = $cek_trans[0]->tgl_update ?? null;

            if ($cek_trans_input === null) {

                $del_data = DB::connection('mysql_sb')->delete("
                DELETE FROM rep_hourly_output_hist_trans");

                $ins_data = DB::connection('mysql_sb')->insert("
                INSERT INTO rep_hourly_output_hist_trans (created_by, sewing_line, styleno, tot_days, tgl_update)
                SELECT
created_by,
u.name sewing_line,
ac.styleno,
COUNT(DISTINCT DATE(a.updated_at)) AS tot_days,
curdate() tgl_update
from output_rfts a
inner join so_det sd on a.so_det_id = sd.id
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join user_sb_wip u on a.created_by = u.id
where a.updated_at >= '2024-08-01' and a.updated_at <= curdate() -1
group by  ac.styleno, created_by
order by sewing_line asc
                ");
            }

            $data_tracking = DB::connection('mysql_sb')->select("SELECT
a.tgl_trans,
concat((DATE_FORMAT(tgl_trans,  '%d')), '-',left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')) tgl_trans_fix,
concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
u.name sewing_line,
ms.supplier buyer,
ac.kpno,
ac.styleno,
mp.color,
'-' tot_days,
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
mp.set_target set_target_perhari,
mp.target_effy,
sum(mp.jam_kerja) jam_kerja,
round((((mp.jam_kerja * 60) * cmp.man_power) / mp.smv),0) target_100,
round(((((mp.jam_kerja * 60) * cmp.man_power) / mp.smv) * mp.target_effy) / 100,0) target_output_eff,
round(if (mp.jam_kerja < 1,mp.set_target, mp.set_target / mp.jam_kerja)) plan_target_perjam,
concat(coalesce(e_kmrn_1.eff_kmrn_1,0), ' %') kemarin_1,
concat(coalesce(e_kmrn_2.eff_kmrn_2,0), ' %') kemarin_2,
concat(coalesce(e_skrg.eff_skrg,0), ' %') eff_skrg,
coalesce(e_skrg.eff_skrg,0) eff_skrg_angka
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
            select
            sewing_line,
            styleno,
            sum(mins_avail),
            sum(mins_prod),
            round(sum(mins_prod) / sum(mins_avail) * 100,2) eff_kmrn_1
            from (
            select
            u.name sewing_line,
            ac.styleno,
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
                where updated_at >= '$start_date_min_1' and updated_at <= '$end_date_min_1'
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
            where updated_at >= '$start_date_min_1' and updated_at <= '$end_date_min_1' group by created_by, date(updated_at)
            ) op on z.tgl_trans = op.tgl_trans_line and z.created_by = op.created_by
            left join
            (
            select min(id), man_power, sewing_line, tgl_plan from master_plan
            where date(tgl_plan) >= '$start_date_min_1' and  date(tgl_plan) <= '$end_date_min_1' and cancel = 'N'
            group by sewing_line, tgl_plan
            ) cmp on z.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
            group by u.name, ac.kpno, ac.Styleno, z.tgl_trans
            ) eff_hr_ini
            group by sewing_line, styleno
) e_kmrn_1 on u.name = e_kmrn_1.sewing_line and ac.styleno = e_kmrn_1.styleno
left join
(
            select
            sewing_line,
            styleno,
            sum(mins_avail),
            sum(mins_prod),
            round(sum(mins_prod) / sum(mins_avail) * 100,2) eff_kmrn_2
            from (
            select
            u.name sewing_line,
            ac.styleno,
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
                where updated_at >= '$start_date_min_2' and updated_at <= '$end_date_min_2'
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
            where updated_at >= '$start_date_min_2' and updated_at <= '$end_date_min_2' group by created_by, date(updated_at)
            ) op on z.tgl_trans = op.tgl_trans_line and z.created_by = op.created_by
            left join
            (
            select min(id), man_power, sewing_line, tgl_plan from master_plan
            where date(tgl_plan) >= '$start_date_min_2' and  date(tgl_plan) <= '$end_date_min_2' and cancel = 'N'
             group by sewing_line, tgl_plan
            ) cmp on z.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
            group by u.name, ac.kpno, ac.Styleno, z.tgl_trans
            ) eff_hr_ini
            group by sewing_line, styleno
) e_kmrn_2 on u.name = e_kmrn_2.sewing_line and ac.styleno = e_kmrn_2.styleno
group by u.name, ac.kpno, ac.Styleno, a.tgl_trans
order by a.tgl_trans asc, u.name asc, ac.kpno asc
");
            return DataTables::of($data_tracking)->toJson();
        }

        return view(
            'ppic.report_hourly',
            [
                'page' => 'dashboard-sewing-eff',
                "subPageGroup" => "sewing-sewing",
                "subPage" => "report-hourly",
                "containerFluid" => true,
                "user" => $user
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
}
