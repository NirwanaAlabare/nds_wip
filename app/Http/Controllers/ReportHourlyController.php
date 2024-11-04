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

        $start_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 00:00:00';
        $end_date_min_2 = date('Y-m-d', strtotime($tgl_filter . ' -2 day')) . ' 23:59:59';

        // $start_date = '2024-10-29 00:00:00';
        // $end_date = '2024-10-29 23:59:59';
        // $end_date_min_1 = '2024-10-28 23:59:59';
        // $end_date_min_2 = '2024-10-27 23:59:59';
        // $start_date_min_2 = '2024-10-27 00:00:00';

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
a.created_by,
tgl_input,
sewing_line,
a.styleno,
max(man_power) man_power,
max(smv) smv,
max(target_effy) target_effy,
coalesce(sum(tot_days),0) tot_days,
concat(coalesce(f.eff_line,0),' %') kemarin_1,
concat(coalesce(g.eff_line,0),' %') kemarin_2,
round(
round(
max(man_power) * round(sum(tot_input) / tot_input_line *
ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2),2) * 60 / max(smv),0)
* target_effy / 100,0) target_output_eff,
set_target perhari,
target_effy,
round(
max(man_power) * round(sum(tot_input) / tot_input_line *
ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2),2) * 60 / max(smv),0)
target_100_eff,
min(jam_kerja_awal) jam_kerja_awal,
jam_kerja_akhir,
istirahat,
TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE AS waktu_kerja,
ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2) AS kerja_total,
round(sum(tot_input) / tot_input_line *
ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2),2) as jam_kerja_act,
round(
max(man_power) *
sum(tot_input) / tot_input_line *
ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2) * 60,0) as min_avl,
round(sum(tot_input) * max(smv),0) min_produce,
sum(jam_1) jam_1,
sum(jam_2) jam_2,
sum(jam_3) jam_3,
sum(jam_4) jam_4,
sum(jam_5) jam_5,
sum(jam_6) jam_6,
sum(jam_7) jam_7,
sum(jam_8) jam_8,
sum(jam_9) jam_9,
sum(jam_10) jam_10,
sum(jam_11) jam_11,
sum(jam_12) jam_12,
sum(jam_13) jam_13,
sum(tot_input) tot_input,
tot_input_line,
round(sum(tot_input) * max(smv),0) earned_minutes,
concat(
round(
round(sum(tot_input) * max(smv),0) /
round(
max(man_power) *
sum(tot_input) / tot_input_line *
ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2) * 60,0) * 100,2), ' %' )
as eff,
concat(e.eff_line,' %') eff_line
from
(
            select
            a.created_by,
            date(a.updated_at) tgl_input,
            u.name sewing_line,
            ac.styleno,
            mpr.product_item,
            mp.man_power,
            mp.smv,
            mp.target_effy,
            master_plan_id,
            d.tot_days,
            count(so_det_id) tot_input,
            c.tot_input_line,
            mp.plan_target,
            mp.set_target,
            mp.jam_kerja_awal,
            last_input jam_kerja_akhir,
            CASE
                    WHEN last_input >= '16:00:00' AND last_input <= '18:30:00' THEN '01:00:00'
                    WHEN last_input >= '18:30:00' THEN '01:30:00'
                    ELSE '00:00:00'
            END AS istirahat,
            COUNT(CASE WHEN jam = 1 THEN 1 END) AS jam_1,
            COUNT(CASE WHEN jam = 2 THEN 1 END) AS jam_2,
            COUNT(CASE WHEN jam = 3 THEN 1 END) AS jam_3,
            COUNT(CASE WHEN jam = 4 THEN 1 END) AS jam_4,
            COUNT(CASE WHEN jam = 5 THEN 1 END) AS jam_5,
            COUNT(CASE WHEN jam = 6 THEN 1 END) AS jam_6,
            COUNT(CASE WHEN jam = 7 THEN 1 END) AS jam_7,
            COUNT(CASE WHEN jam = 8 THEN 1 END) AS jam_8,
            COUNT(CASE WHEN jam = 9 THEN 1 END) AS jam_9,
            COUNT(CASE WHEN jam = 10 THEN 1 END) AS jam_10,
            COUNT(CASE WHEN jam = 11 THEN 1 END) AS jam_11,
            COUNT(CASE WHEN jam = 12 THEN 1 END) AS jam_12,
            COUNT(CASE WHEN jam = 13 THEN 1 END) AS jam_13
            from output_rfts a
            left join dim_jam_kerja_sewing b on time(a.updated_at) >= b.jam_kerja_awal and time(a.updated_at) <= b.jam_kerja_akhir
            inner join master_plan mp on a.master_plan_id = mp.id
            inner join user_sb_wip u on a.created_by = u.id
            inner join so_det sd on a.so_det_id = sd.id
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join masterproduct mpr on ac.id_product = mpr.id
            left join (
            select created_by,COUNT(so_det_id)tot_input_line, max(time(updated_at)) last_input from output_rfts a
            where a.updated_at >= '$start_date' and a.updated_at <= '$end_date' and status = 'NORMAL'
            group by created_by
            ) c on a.created_by = c.created_by
            left join (
            select * from rep_hourly_output_hist_trans
            ) d on a.created_by = d.created_by and ac.styleno = d.styleno
            where a.updated_at >= '$start_date' and a.updated_at <= '$end_date' and a.status = 'NORMAL'
            group by a.created_by, master_plan_id, ac.styleno
            order by u.name asc
            ) a
    left join (
                select created_by,
                round(sum(min_produce) / sum(min_avl) * 100,2) eff_line
                from (SELECT
                        a.created_by,
                        sewing_line,
                        round(
                        max(man_power) *
                        sum(tot_input) / tot_input_line *
                        ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
                        MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
                        SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2) * 60,0) as min_avl,
                        round(sum(tot_input) * max(smv),0) min_produce
                        from
                            (
                            select
                            date(a.updated_at) tgl_input,
                            a.created_by,
                            u.name sewing_line,
                            ac.styleno,
                            mp.man_power,
                            mp.smv,
                            count(so_det_id) tot_input,
                            c.tot_input_line,
                            mp.jam_kerja_awal,
                            last_input jam_kerja_akhir,
                            CASE
                                WHEN last_input >= '16:00:00' AND last_input <= '18:30:00' THEN '01:00:00'
                                WHEN last_input >= '18:30:00' THEN '01:30:00'
                                ELSE '00:00:00'
                            END AS istirahat
                            from output_rfts a
                            left join dim_jam_kerja_sewing b on time(a.updated_at) >= b.jam_kerja_awal and time(a.updated_at) <= b.jam_kerja_akhir
                            inner join master_plan mp on a.master_plan_id = mp.id
                            inner join user_sb_wip u on a.created_by = u.id
                            inner join so_det sd on a.so_det_id = sd.id
                            inner join so on sd.id_so = so.id
                            inner join act_costing ac on so.id_cost = ac.id
                            left join (
                            select created_by,COUNT(so_det_id)tot_input_line, max(time(updated_at)) last_input from output_rfts a
                            where a.updated_at >= '$start_date' and a.updated_at <= '$end_date' and status = 'NORMAL'
                            group by created_by
                            ) c on a.created_by = c.created_by
                            where a.updated_at >= '$start_date' and a.updated_at <= '$end_date' and a.status = 'NORMAL'
                            group by a.created_by, master_plan_id, ac.styleno
                            order by u.name asc
                            ) a
                            group by sewing_line, styleno
                            ) x
                            group by sewing_line
                            ) e on a.created_by = e.created_by

     left join (
                select created_by,
                styleno,
                round(sum(min_produce) / sum(min_avl) * 100,2) eff_line
                from (SELECT
                        a.created_by,
                        sewing_line,
                        styleno,
                        round(
                        max(man_power) *
                        sum(tot_input) / tot_input_line *
                        ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
                        MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
                        SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2) * 60,0) as min_avl,
                        round(sum(tot_input) * max(smv),0) min_produce
                        from
                            (
                            select
                            date(a.updated_at) tgl_input,
                            a.created_by,
                            u.name sewing_line,
                            ac.styleno,
                            mp.man_power,
                            mp.smv,
                            count(so_det_id) tot_input,
                            c.tot_input_line,
                            mp.jam_kerja_awal,
                            last_input jam_kerja_akhir,
                            CASE
                                WHEN last_input >= '16:00:00' AND last_input <= '18:30:00' THEN '01:00:00'
                                WHEN last_input >= '18:30:00' THEN '01:30:00'
                                ELSE '00:00:00'
                            END AS istirahat
                            from output_rfts a
                            left join dim_jam_kerja_sewing b on time(a.updated_at) >= b.jam_kerja_awal and time(a.updated_at) <= b.jam_kerja_akhir
                            inner join master_plan mp on a.master_plan_id = mp.id
                            inner join user_sb_wip u on a.created_by = u.id
                            inner join so_det sd on a.so_det_id = sd.id
                            inner join so on sd.id_so = so.id
                            inner join act_costing ac on so.id_cost = ac.id
                            left join (
                            select created_by,COUNT(so_det_id)tot_input_line, max(time(updated_at)) last_input from output_rfts a
                            where a.updated_at >= '$start_date_min_1' and a.updated_at <= '$end_date_min_1' and status = 'NORMAL'
                            group by created_by
                            ) c on a.created_by = c.created_by
                            where a.updated_at >= '$start_date_min_1' and a.updated_at <= '$end_date_min_1' and a.status = 'NORMAL'
                            group by a.created_by, master_plan_id, ac.styleno
                            order by u.name asc
                            ) a
                            group by sewing_line, styleno
                            ) x
                            group by sewing_line
                            ) f on a.created_by = f.created_by and a.styleno = f.styleno
         left join (
                select created_by,
                styleno,
                round(sum(min_produce) / sum(min_avl) * 100,2) eff_line
                from (SELECT
                        a.created_by,
                        sewing_line,
                        styleno,
                        round(
                        max(man_power) *
                        sum(tot_input) / tot_input_line *
                        ROUND(HOUR(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) +
                        MINUTE(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 60 +
                        SECOND(TIMEDIFF(jam_kerja_akhir, jam_kerja_awal) - INTERVAL (istirahat * 60) MINUTE) / 3600,2) * 60,0) as min_avl,
                        round(sum(tot_input) * max(smv),0) min_produce
                        from
                            (
                            select
                            date(a.updated_at) tgl_input,
                            a.created_by,
                            u.name sewing_line,
                            ac.styleno,
                            mp.man_power,
                            mp.smv,
                            count(so_det_id) tot_input,
                            c.tot_input_line,
                            mp.jam_kerja_awal,
                            last_input jam_kerja_akhir,
                            CASE
                                WHEN last_input >= '16:00:00' AND last_input <= '18:30:00' THEN '01:00:00'
                                WHEN last_input >= '18:30:00' THEN '01:30:00'
                                ELSE '00:00:00'
                            END AS istirahat
                            from output_rfts a
                            left join dim_jam_kerja_sewing b on time(a.updated_at) >= b.jam_kerja_awal and time(a.updated_at) <= b.jam_kerja_akhir
                            inner join master_plan mp on a.master_plan_id = mp.id
                            inner join user_sb_wip u on a.created_by = u.id
                            inner join so_det sd on a.so_det_id = sd.id
                            inner join so on sd.id_so = so.id
                            inner join act_costing ac on so.id_cost = ac.id
                            left join (
                            select created_by,COUNT(so_det_id)tot_input_line, max(time(updated_at)) last_input from output_rfts a
                            where a.updated_at >= '$start_date_min_2' and a.updated_at <= '$end_date_min_2' and status = 'NORMAL'
                            group by created_by
                            ) c on a.created_by = c.created_by
                            where a.updated_at >= '$start_date_min_2' and a.updated_at <= '$end_date_min_2' and a.status = 'NORMAL'
                            group by a.created_by, master_plan_id, ac.styleno
                            order by u.name asc
                            ) a
                            group by sewing_line, styleno
                            ) x
                            group by sewing_line
                            ) g on a.created_by = f.created_by and a.styleno = g.styleno

group by sewing_line, styleno
order by sewing_line asc
");
            return DataTables::of($data_tracking)->toJson();
        }

        return view(
            'ppic.report_hourly',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-laporan",
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


    public function show_lap_tracking_ppic(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $buyer = $request->buyer;

        $delete_tmp_qc =  DB::delete("
        delete from ppic_laporan_tracking_tmp_qc_output where created_by = '$user' and buyer = '$buyer'");

        $delete_tmp_p_line =  DB::delete("
        delete from ppic_laporan_tracking_tmp_packing_line where created_by = '$user' and buyer = '$buyer'");

        $data_qc = DB::connection('mysql_sb')->select("SELECT
ms.supplier buyer, ac.kpno ws, sd.color, sd.size, dest, sum(a.tot) tot_qc from
(select so_det_id,count(so_det_id) tot from output_rfts group by so_det_id) a
inner join so_det sd on a.so_det_id = sd.id
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
left join master_size_new msn on sd.size = msn.size
where ms.supplier = '$buyer'
group by ac.kpno, sd.color, sd.size, ac.styleno
order by ac.kpno asc, sd.color asc, msn.urutan asc
            ");
        for ($i = 0; $i < count($data_qc); $i++) {
            $i_buyer = $data_qc[$i]->buyer;
            $i_ws = $data_qc[$i]->ws;
            $i_color = $data_qc[$i]->color;
            $i_size = $data_qc[$i]->size;
            $i_tot_qc = $data_qc[$i]->tot_qc;

            $insert_mut =  DB::insert("
                insert into ppic_laporan_tracking_tmp_qc_output
                (buyer,ws,color,size,tot_qc,created_by,created_at,updated_at)
                values('$i_buyer','$i_ws','$i_color','$i_size','$i_tot_qc','$user','$timestamp','$timestamp')");
        }

        $data_packing_line = DB::select("SELECT
        buyer, ws, color, m.size, tot_p_line from
        (select so_det_id,count(so_det_id) tot_p_line from output_rfts_packing a group by so_det_id) a
        inner join master_sb_ws m on a.so_det_id = m.id_so_det
        left join master_size_new msn on m.size = msn.size
        where m.buyer = '$buyer'
        group by ws, color, m.size, m.styleno
        order by ws asc, color asc, msn.urutan asc
                            ");
        for ($i = 0; $i < count($data_packing_line); $i++) {
            $i_buyer = $data_packing_line[$i]->buyer;
            $i_ws = $data_packing_line[$i]->ws;
            $i_color = $data_packing_line[$i]->color;
            $i_size = $data_packing_line[$i]->size;
            $i_tot_qc = $data_packing_line[$i]->tot_p_line;

            $insert_mut =  DB::insert("
                                    insert into ppic_laporan_tracking_tmp_packing_line
                                    (buyer,ws,color,size,tot_p_line,created_by,created_at,updated_at)
                                    values('$i_buyer','$i_ws','$i_color','$i_size','$i_tot_qc','$user','$timestamp','$timestamp')");
        }

        $data_tracking = DB::select("SELECT
buyer,
ws,
color,
a.size,
coalesce(sum(tot_qc),0) tot_qc,
coalesce(sum(tot_p_line),0) tot_p_line,
coalesce(sum(qty_trf_garment),0) qty_trf_garment,
coalesce(sum(qty_packing_in),0) qty_packing_in,
coalesce(sum(qty_packing_out),0) qty_packing_out
from
(
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from master_sb_ws where buyer = '$buyer'
group by ws, color, size, styleno
union
select
buyer,
ws,
color,
size,
tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from ppic_laporan_tracking_tmp_qc_output
where buyer = '$buyer' and created_by = '$user'
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from ppic_laporan_tracking_tmp_packing_line
where buyer = '$buyer' and created_by = '$user'
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
sum(t.qty) as qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from packing_trf_garment t
inner join ppic_master_so p on t.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where buyer = '$buyer'
group by ws, color, size
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
sum(pi.qty) qty_packing_in,
'0' qty_packing_out
from packing_packing_in pi
inner join ppic_master_so p on pi.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.buyer = '$buyer'
group by ws, color, size
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
sum(o.qty_packing_out) qty_packing_out
from
    (
        select count(barcode) qty_packing_out,po, barcode, dest from packing_packing_out_scan
        group by barcode, po, dest
    ) o
inner join ppic_master_so p on o.barcode = p.barcode and o.po = p.po and o.dest = p.dest
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.buyer = '$buyer'
group by ws, color, size
) a
left join master_size_new msn on a.size = msn.size
group by ws, color, a.size
order by ws asc, color asc, urutan asc, a.size asc
                    ");
        return DataTables::of($data_tracking)->toJson();
    }


    public function export_excel_tracking(Request $request)
    {
        $user = Auth::user()->name;
        return Excel::download(new ExportLaporanPPICTracking($request->buyer, $user), 'Laporan_Tracking.xlsx');
    }
}
