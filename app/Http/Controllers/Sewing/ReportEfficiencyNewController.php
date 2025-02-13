<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Report_eff_new_export;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Style;

class ReportEfficiencyNewController extends Controller
{
    public function index(Request $request)
    {

        $tgl_skrg = date('Y-m-d');
        $tgl_awal_n = $request->tgl_awal;
        $tgl_akhir_n = $request->tgl_akhir;
        $start_date = $tgl_awal_n . ' 00:00:00';
        $end_date = $tgl_akhir_n . ' 23:59:59';

        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
a.tgl_trans,
concat((DATE_FORMAT(tgl_trans,  '%d')), '-',left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')) tgl_trans_fix,
concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
u.name sewing_line,
ms.supplier buyer,
ac.kpno,
ac.styleno,
mp.color,
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
so.curr,
CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate, acm.price)
ELSE acm.price end AS cm_price,
round(
sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate, acm.price)
ELSE acm.price end,2) AS earning,
mkb.kurs_tengah,
round(
if (so.curr = 'IDR',
sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate, acm.price)
ELSE acm.price end,
sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate, acm.price)
ELSE acm.price end * mkb.kurs_tengah
),2) tot_earning_rupiah,
round((cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
round(sum(a.tot_output) * mp.smv,2) mins_prod,
round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line,
round(((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)),2) jam_kerja_act,
round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts
 from
(
    select
    date(updated_at)tgl_trans,
    so_det_id,
    master_plan_id,
    count(so_det_id) tot_output,
    time(max(a.updated_at)) jam_akhir_input,
    created_by
    from output_rfts a
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
select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal
)konv_sb on ac.deldate = konv_sb.tanggal
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
select min(id), man_power, sewing_line, tgl_plan from master_plan where tgl_plan >= '$tgl_awal_n' and  tgl_plan <= '$tgl_akhir_n' and cancel = 'N' group by sewing_line, tgl_plan
) cmp on a.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line
left join master_kurs_bi mkb on a.tgl_trans = mkb.tanggal_kurs_bi
group by u.name, ac.kpno, ac.Styleno, a.tgl_trans
order by a.tgl_trans asc, u.name asc, ac.kpno asc

            ");

            return DataTables::of($data_input)->toJson();
        }


        return view(
            'sewing.report.report_efficiency_new',
            [
                'page' => 'dashboard-sewing-eff',
                "subPageGroup" => "sewing-report",
                "subPage" => "reportEfficiencynew",
                "user" => $user
            ]
        );
    }

    public function export_excel_rep_eff_new(Request $request)
    {
        $user = Auth::user()->name;
        return Excel::download(new Report_eff_new_export($request->tgl_awal, $request->tgl_akhir), 'Laporan_Tracking.xlsx');
    }
}
