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


            $realtimeStartDate = date('Y-m-d', strtotime($end_date . ' -3 day'));
            $queryTemp = "";

            if ($start_date < $realtimeStartDate) {
                $tempEndDate = date('Y-m-d', strtotime($realtimeStartDate . ' -1 day'));

                $queryTemp = "
                    SELECT
                        tgl_trans,
                        concat((DATE_FORMAT(tgl_trans, '%d')), '-', left(DATE_FORMAT(tgl_trans, '%M'),3), '-', DATE_FORMAT(tgl_trans, '%Y')) tgl_trans_fix,
                        concat((DATE_FORMAT(tgl_plan, '%d')), '-', left(DATE_FORMAT(tgl_plan, '%M'),3), '-', DATE_FORMAT(tgl_plan, '%Y')) tgl_plan_fix,
                        sewing_line,
                        buyer,
                        kpno,
                        styleno,
                        color,
                        id,
                        smv,
                        man_power_ori,
                        man_power,
                        jam_kerja_awal,
                        istirahat,
                        jam_akhir_input_line,
                        jam_kerja_act_line,
                        target,
                        tot_output,
                        tot_rfts,
                        tot_output_line,
                        curr,
                        cm_price,
                        earning,
                        kurs_tengah,
                        tot_earning_rupiah,
                        mins_avail,
                        mins_prod,
                        eff_line,
                        jam_kerja_act,
                        rfts
                    FROM mgt_rep_tmp_earn
                    WHERE tgl_trans >= '$start_date'
                    AND tgl_trans <= '$tempEndDate'
                ";
            }


            $realStart = $start_date >= $realtimeStartDate
                ? $start_date
                : $realtimeStartDate;

            $queryRealtime = "
                SELECT
                    a.tgl_trans,
                    concat((DATE_FORMAT(a.tgl_trans, '%d')), '-',left(DATE_FORMAT(a.tgl_trans, '%M'),3),'-',DATE_FORMAT(a.tgl_trans, '%Y')) tgl_trans_fix,
                    concat((DATE_FORMAT(mp.tgl_plan, '%d')), '-',left(DATE_FORMAT(mp.tgl_plan, '%M'),3),'-',DATE_FORMAT(mp.tgl_plan, '%Y')) tgl_plan_fix,
                    ul.username sewing_line,
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

                    round(
                        TIME_TO_SEC(
                            TIMEDIFF(
                                TIMEDIFF(jam_akhir_input_line, istirahat),
                                mp.jam_kerja_awal
                            )
                        ) / 3600,2
                    ) AS jam_kerja_act_line,

                    round(
                        (
                            (
                                (
                                    (sum(a.tot_output) / op.tot_output_line)
                                    *
                                    (
                                        TIME_TO_SEC(
                                            TIMEDIFF(
                                                TIMEDIFF(jam_akhir_input_line, istirahat),
                                                mp.jam_kerja_awal
                                            )
                                        ) / 3600
                                    )
                                ) * 60
                            ) * cmp.man_power
                        ) / mp.smv
                    ) target,

                    sum(a.tot_output) tot_output,
                    sum(d_rfts.tot_rfts) tot_rfts,
                    op.tot_output_line,
                    acm.price AS cm_price,
                    ac.curr,
ROUND(
    SUM(a.tot_output) * acm.price
, 2) AS earning,
                    COALESCE(mr.kurs_tengah,mkb.kurs_tengah) kurs_tengah,

ROUND(
    SUM(a.tot_output) * CASE
        WHEN acm.jenis_rate = 'B'
            THEN acm.price
        ELSE
            acm.price * COALESCE(mr.kurs_tengah,mkb.kurs_tengah)
    END
, 2) tot_earning_rupiah,

                    round(
                        (
                            cmp.man_power *
                            (sum(a.tot_output) / op.tot_output_line) *
                            (
                                TIME_TO_SEC(
                                    TIMEDIFF(
                                        TIMEDIFF(jam_akhir_input_line, istirahat),
                                        mp.jam_kerja_awal
                                    )
                                ) / 3600
                            ) * 60
                        ),2
                    ) mins_avail,

                    round(sum(a.tot_output) * mp.smv,2) mins_prod,

                    round(
                        (
                            (
                                (sum(a.tot_output) * mp.smv)
                                /
                                (
                                    (
                                        cmp.man_power *
                                        (sum(a.tot_output) / op.tot_output_line) *
                                        (
                                            TIME_TO_SEC(
                                                TIMEDIFF(
                                                    TIMEDIFF(jam_akhir_input_line, istirahat),
                                                    mp.jam_kerja_awal
                                                )
                                            ) / 3600
                                        ) * 60
                                    )
                                )
                            ) * 100
                        ),2
                    ) eff_line,

                    round(
                        (
                            (sum(a.tot_output) / op.tot_output_line)
                            *
                            (
                                TIME_TO_SEC(
                                    TIMEDIFF(
                                        TIMEDIFF(jam_akhir_input_line, istirahat),
                                        mp.jam_kerja_awal
                                    )
                                ) / 3600
                            )
                        ),2
                    ) jam_kerja_act,

                    round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts

                from
                (
                    select
                        date(a.updated_at) tgl_trans,
                        so_det_id,
                        master_plan_id,
                        count(so_det_id) tot_output,
                        time(max(a.updated_at)) jam_akhir_input,
                        userpassword.username

                    from output_rfts a

                    left join user_sb_wip
                        on user_sb_wip.id = a.created_by

                    left join userpassword
                        on userpassword.line_id = user_sb_wip.line_id

                    where a.updated_at >= '$realStart'
                    and a.updated_at <= '$end_date'

                    group by master_plan_id, userpassword.username, date(a.updated_at)

                ) a

                inner join so_det sd on a.so_det_id = sd.id
                inner join so on sd.id_so = so.id
                inner join act_costing ac on so.id_cost = ac.id
                inner join userpassword ul on ul.username = a.username
                inner join master_plan mp on a.master_plan_id = mp.id
                inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier

                left join (
                    select
                        date(output_rfts.updated_at) tgl_trans_line,
                        max(time(output_rfts.updated_at)) jam_akhir_input_line,
                        count(output_rfts.so_det_id) tot_output_line,

                        case
                            when time(max(output_rfts.updated_at)) >= '12:00:00'
                            and time(max(output_rfts.updated_at)) <= '18:44:59'
                            THEN '01:00:00'

                            when time(max(output_rfts.updated_at)) <= '12:00:00'
                            THEN '00:00:00'

                            when time(max(output_rfts.updated_at)) >= '18:45:00'
                            THEN '01:30:00'
                        END as istirahat,

                        userpassword.username

                    from output_rfts

                    left join user_sb_wip
                        on user_sb_wip.id = output_rfts.created_by

                    left join userpassword
                        on userpassword.line_id = user_sb_wip.line_id

                    where output_rfts.updated_at >= '$realStart'
                    and output_rfts.updated_at <= '$end_date'

                    group by userpassword.username, date(output_rfts.updated_at)

                ) op
                on a.tgl_trans = op.tgl_trans_line
                and ul.username = op.username

                left join (
                    select * from act_costing_mfg where id_item = '8' group by id_act_cost
                ) acm on ac.id = acm.id_act_cost
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal
                ) konv_sb on ac.deldate = konv_sb.tanggal
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal ORDER BY tanggal DESC limit 1
                ) last_konv_sb on ac.deldate >= last_konv_sb.tanggal
                left join (
                    SELECT
                            master_plan_id,
                            tgl_trans_rfts,
                            sum(tot_rfts)tot_rfts
                    from
                    (
                            select
                            date(a.updated_at)tgl_trans_rfts,
                            master_plan_id,
                            count(so_det_id) tot_rfts,
                            userpassword.username
                            from output_rfts a
                            left join user_sb_wip on user_sb_wip.id = a.created_by
                            left join userpassword on userpassword.line_id = user_sb_wip.line_id
                            where a.updated_at >= '$start_date' and a.updated_at <= '$end_date' and status = 'NORMAL'
                            group by master_plan_id, userpassword.username, date(a.updated_at)
                    ) a
                    inner join master_plan mp on a.master_plan_id = mp.id
                    group by tgl_trans_rfts, master_plan_id
                ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
                left join
                (
                    select min(id), man_power, sewing_line, tgl_plan from master_plan
                    where tgl_plan >= '$tgl_awal_n' and  tgl_plan <= '$tgl_akhir_n' and cancel = 'N'
                    group by sewing_line, tgl_plan
                ) cmp on a.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line

                -- Kurs join for pre-MySQL 8
                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.kurs_tengah
                    FROM (
                            SELECT a_dates.tgl_trans, MAX(mkb.tanggal_kurs_bi) AS max_kurs_date
                            FROM (
                                    SELECT DISTINCT date(updated_at) AS tgl_trans
                                    FROM output_rfts
                                    WHERE updated_at >= '$start_date' AND updated_at <= '$end_date'
                            ) a_dates
                            JOIN master_kurs_bi mkb
                            ON mkb.tanggal_kurs_bi <= a_dates.tgl_trans
                            GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN master_kurs_bi k
                    ON k.tanggal_kurs_bi = x.max_kurs_date
                ) mkb ON a.tgl_trans = mkb.tgl_trans

                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.rate as kurs_tengah
                    FROM (
                        SELECT a_dates.tgl_trans, MAX(mr.tanggal) AS max_kurs_date
                        FROM (
                            SELECT DISTINCT date(updated_at) AS tgl_trans
                            FROM output_rfts
                            WHERE updated_at >= '$start_date' AND updated_at <= '$end_date'
                        ) a_dates
                        JOIN masterrate mr
                        ON mr.tanggal <= a_dates.tgl_trans
                        GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN masterrate k
                    ON k.tanggal = x.max_kurs_date
                    WHERE k.v_codecurr = 'HARIAN'
                ) mr ON a.tgl_trans = mr.tgl_trans

                group by
                    ul.username,
                    ac.kpno,
                    ac.Styleno,
                    a.tgl_trans
            ";

            if ($queryTemp != "") {

                $sql = "
                    $queryTemp
                    UNION ALL
                    $queryRealtime
                    ORDER BY tgl_trans ASC, sewing_line ASC, kpno ASC
                ";
            } else {

                $sql = "
                    $queryRealtime
                    ORDER BY tgl_trans ASC, sewing_line ASC, kpno ASC
                ";
            }

            if ($request->has('debug_sql')) {
                dd($sql);
            }

            $data_input = DB::connection('mysql_sb')->select($sql);

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

    // public function export_excel_rep_eff_new(Request $request)
    // {
    //     $user = Auth::user()->name;
    //     return Excel::download(new Report_eff_new_export($request->tgl_awal, $request->tgl_akhir), 'Laporan_Tracking.xlsx');
    // }

    public function export_excel_rep_eff_new(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_awal_n = $request->tgl_awal;
        $tgl_akhir_n = $request->tgl_akhir;
        $start_date = $tgl_awal_n . ' 00:00:00';
        $end_date = $tgl_akhir_n . ' 23:59:59';

        $realtimeStartDate = date('Y-m-d', strtotime($end_date . ' -3 day'));
        $queryTemp = "";

        if ($start_date < $realtimeStartDate) {
            $tempEndDate = date('Y-m-d', strtotime($realtimeStartDate . ' -1 day'));

            $queryTemp = "
                SELECT
                    tgl_trans,
                    concat((DATE_FORMAT(tgl_trans, '%d')), '-', left(DATE_FORMAT(tgl_trans, '%M'),3), '-', DATE_FORMAT(tgl_trans, '%Y')) tgl_trans_fix,
                    concat((DATE_FORMAT(tgl_plan, '%d')), '-', left(DATE_FORMAT(tgl_plan, '%M'),3), '-', DATE_FORMAT(tgl_plan, '%Y')) tgl_plan_fix,
                    sewing_line,
                    buyer,
                    kpno,
                    styleno,
                    color,
                    id,
                    smv,
                    man_power_ori,
                    man_power,
                    jam_kerja_awal,
                    istirahat,
                    jam_akhir_input_line,
                    jam_kerja_act_line,
                    target,
                    tot_output,
                    tot_rfts,
                    tot_output_line,
                    curr,
                    cm_price,
                    earning,
                    kurs_tengah,
                    tot_earning_rupiah,
                    mins_avail,
                    mins_prod,
                    eff_line,
                    jam_kerja_act,
                    rfts
                FROM mgt_rep_tmp_earn
                WHERE tgl_trans >= '$start_date'
                AND tgl_trans <= '$tempEndDate'
            ";
        }


        $realStart = $start_date >= $realtimeStartDate
            ? $start_date
            : $realtimeStartDate;

        $queryRealtime = "
            SELECT
                a.tgl_trans,
                concat((DATE_FORMAT(a.tgl_trans, '%d')), '-',left(DATE_FORMAT(a.tgl_trans, '%M'),3),'-',DATE_FORMAT(a.tgl_trans, '%Y')) tgl_trans_fix,
                concat((DATE_FORMAT(mp.tgl_plan, '%d')), '-',left(DATE_FORMAT(mp.tgl_plan, '%M'),3),'-',DATE_FORMAT(mp.tgl_plan, '%Y')) tgl_plan_fix,
                ul.username sewing_line,
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

                round(
                    TIME_TO_SEC(
                        TIMEDIFF(
                            TIMEDIFF(jam_akhir_input_line, istirahat),
                            mp.jam_kerja_awal
                        )
                    ) / 3600,2
                ) AS jam_kerja_act_line,

                round(
                    (
                        (
                            (
                                (sum(a.tot_output) / op.tot_output_line)
                                *
                                (
                                    TIME_TO_SEC(
                                        TIMEDIFF(
                                            TIMEDIFF(jam_akhir_input_line, istirahat),
                                            mp.jam_kerja_awal
                                        )
                                    ) / 3600
                                )
                            ) * 60
                        ) * cmp.man_power
                    ) / mp.smv
                ) target,

                sum(a.tot_output) tot_output,
                sum(d_rfts.tot_rfts) tot_rfts,
                op.tot_output_line,
                    ac.curr,

                    acm.price AS cm_price,
ROUND(
    SUM(a.tot_output) * acm.price
, 2) AS earning,
                    COALESCE(mr.kurs_tengah,mkb.kurs_tengah) kurs_tengah,

ROUND(
    SUM(a.tot_output) * CASE
        WHEN acm.jenis_rate = 'B'
            THEN acm.price
        ELSE
            acm.price * COALESCE(mr.kurs_tengah,mkb.kurs_tengah)
    END
, 2) tot_earning_rupiah,

                round(
                    (
                        cmp.man_power *
                        (sum(a.tot_output) / op.tot_output_line) *
                        (
                            TIME_TO_SEC(
                                TIMEDIFF(
                                    TIMEDIFF(jam_akhir_input_line, istirahat),
                                    mp.jam_kerja_awal
                                )
                            ) / 3600
                        ) * 60
                    ),2
                ) mins_avail,

                round(sum(a.tot_output) * mp.smv,2) mins_prod,

                round(
                    (
                        (
                            (sum(a.tot_output) * mp.smv)
                            /
                            (
                                (
                                    cmp.man_power *
                                    (sum(a.tot_output) / op.tot_output_line) *
                                    (
                                        TIME_TO_SEC(
                                            TIMEDIFF(
                                                TIMEDIFF(jam_akhir_input_line, istirahat),
                                                mp.jam_kerja_awal
                                            )
                                        ) / 3600
                                    ) * 60
                                )
                            )
                        ) * 100
                    ),2
                ) eff_line,

                round(
                    (
                        (sum(a.tot_output) / op.tot_output_line)
                        *
                        (
                            TIME_TO_SEC(
                                TIMEDIFF(
                                    TIMEDIFF(jam_akhir_input_line, istirahat),
                                    mp.jam_kerja_awal
                                )
                            ) / 3600
                        )
                    ),2
                ) jam_kerja_act,

                round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts

            from
            (
                select
                    date(a.updated_at) tgl_trans,
                    so_det_id,
                    master_plan_id,
                    count(so_det_id) tot_output,
                    time(max(a.updated_at)) jam_akhir_input,
                    userpassword.username

                from output_rfts a

                left join user_sb_wip
                    on user_sb_wip.id = a.created_by

                left join userpassword
                    on userpassword.line_id = user_sb_wip.line_id

                where a.updated_at >= '$realStart'
                and a.updated_at <= '$end_date'

                group by master_plan_id, userpassword.username, date(a.updated_at)

            ) a

            inner join so_det sd on a.so_det_id = sd.id
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join userpassword ul on ul.username = a.username
            inner join master_plan mp on a.master_plan_id = mp.id
            inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier

            left join (
                select
                    date(output_rfts.updated_at) tgl_trans_line,
                    max(time(output_rfts.updated_at)) jam_akhir_input_line,
                    count(output_rfts.so_det_id) tot_output_line,

                    case
                        when time(max(output_rfts.updated_at)) >= '12:00:00'
                        and time(max(output_rfts.updated_at)) <= '18:44:59'
                        THEN '01:00:00'

                        when time(max(output_rfts.updated_at)) <= '12:00:00'
                        THEN '00:00:00'

                        when time(max(output_rfts.updated_at)) >= '18:45:00'
                        THEN '01:30:00'
                    END as istirahat,

                    userpassword.username

                from output_rfts

                left join user_sb_wip
                    on user_sb_wip.id = output_rfts.created_by

                left join userpassword
                    on userpassword.line_id = user_sb_wip.line_id

                where output_rfts.updated_at >= '$realStart'
                and output_rfts.updated_at <= '$end_date'

                group by userpassword.username, date(output_rfts.updated_at)

            ) op
            on a.tgl_trans = op.tgl_trans_line
            and ul.username = op.username

            left join (
                select * from act_costing_mfg where id_item = '8' group by id_act_cost
            ) acm on ac.id = acm.id_act_cost
            left join (
                select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal
            ) konv_sb on ac.deldate = konv_sb.tanggal
            left join (
                select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal ORDER BY tanggal DESC limit 1
            ) last_konv_sb on ac.deldate >= last_konv_sb.tanggal
            left join (
                SELECT
                        master_plan_id,
                        tgl_trans_rfts,
                        sum(tot_rfts)tot_rfts
                from
                (
                        select
                        date(a.updated_at)tgl_trans_rfts,
                        master_plan_id,
                        count(so_det_id) tot_rfts,
                        userpassword.username
                        from output_rfts a
                        left join user_sb_wip on user_sb_wip.id = a.created_by
                        left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        where a.updated_at >= '$start_date' and a.updated_at <= '$end_date' and status = 'NORMAL'
                        group by master_plan_id, userpassword.username, date(a.updated_at)
                ) a
                inner join master_plan mp on a.master_plan_id = mp.id
                group by tgl_trans_rfts, master_plan_id
            ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
            left join
            (
                select min(id), man_power, sewing_line, tgl_plan from master_plan
                where tgl_plan >= '$tgl_awal_n' and  tgl_plan <= '$tgl_akhir_n' and cancel = 'N'
                group by sewing_line, tgl_plan
            ) cmp on a.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line

            -- Kurs join for pre-MySQL 8
            LEFT JOIN (
                SELECT x.tgl_trans, x.max_kurs_date, k.kurs_tengah
                FROM (
                        SELECT a_dates.tgl_trans, MAX(mkb.tanggal_kurs_bi) AS max_kurs_date
                        FROM (
                                SELECT DISTINCT date(updated_at) AS tgl_trans
                                FROM output_rfts
                                WHERE updated_at >= '$start_date' AND updated_at <= '$end_date'
                        ) a_dates
                        JOIN master_kurs_bi mkb
                        ON mkb.tanggal_kurs_bi <= a_dates.tgl_trans
                        GROUP BY a_dates.tgl_trans
                ) x
                JOIN master_kurs_bi k
                ON k.tanggal_kurs_bi = x.max_kurs_date
            ) mkb ON a.tgl_trans = mkb.tgl_trans

            LEFT JOIN (
                SELECT x.tgl_trans, x.max_kurs_date, k.rate as kurs_tengah
                FROM (
                    SELECT a_dates.tgl_trans, MAX(mr.tanggal) AS max_kurs_date
                    FROM (
                        SELECT DISTINCT date(updated_at) AS tgl_trans
                        FROM output_rfts
                        WHERE updated_at >= '$start_date' AND updated_at <= '$end_date'
                    ) a_dates
                    JOIN masterrate mr
                    ON mr.tanggal <= a_dates.tgl_trans
                    GROUP BY a_dates.tgl_trans
                ) x
                JOIN masterrate k
                ON k.tanggal = x.max_kurs_date
                WHERE k.v_codecurr = 'HARIAN'
            ) mr ON a.tgl_trans = mr.tgl_trans

            group by
                ul.username,
                ac.kpno,
                ac.Styleno,
                a.tgl_trans
        ";

        if ($queryTemp != "") {

            $sql = "
                $queryTemp
                UNION ALL
                $queryRealtime
                ORDER BY tgl_trans ASC, sewing_line ASC, kpno ASC
            ";
        } else {

            $sql = "
                $queryRealtime
                ORDER BY tgl_trans ASC, sewing_line ASC, kpno ASC
            ";
        }

        $data = DB::connection('mysql_sb')->select($sql);

        $totalManPower = collect($data)->groupBy('sewing_line')->flatMap(function ($group) {

            return $group->pluck('man_power')->unique();
        })->sum();

        // $totalManPower = collect($data)->sum('man_power');
        $totalTarget = collect($data)->sum('target');
        $totalOutput = collect($data)->sum('tot_output');
        $totalMinsAvail = collect($data)->sum('mins_avail');
        $totalEarningRupiah = collect($data)->sum('tot_earning_rupiah');
        $formattedEarningRupiah = 'Rp ' . number_format($totalEarningRupiah, 2, ',', '.');
        $totalMinsProd = collect($data)->sum('mins_prod');

        $fileName = 'report-efficiency';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Report Efficiency'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tgl_awal_n . ' s/d ' . $tgl_akhir_n],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $headerTop = [
            '',
            '',
            '',
            '',
            '',
            '',
            'Jumlah',
            $totalManPower,
            $totalMinsAvail,
            $totalTarget,
            $totalOutput,
            '',
            '',
            '',
            '',
            $formattedEarningRupiah,
            $totalMinsProd,
            '',
            '',
            ''
        ];

        $sheet->writeRow(
            $headerTop,
            [
                'font-style' => 'bold',
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->mergeCells('A4:F4');
        $sheet->mergeCells('L4:O4');
        $sheet->mergeCells('R4:T4');

        $header = [
            'Tgl. Plan',
            'Tgl. Trans',
            'Line',
            'WS',
            'Buyer',
            'Style',
            'SMV',
            'MP',
            'Mins Avail',
            'Target',
            'Output',
            'Currency',
            'CM Price',
            'Earning',
            'Kurs Tengah BI',
            'Earning Rupiah',
            'Mins. Prod',
            'Efficiency',
            'RFT',
            'Jam Kerja Aktual',
        ];

        $sheet->writeRow(
            $header,
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->setCellStyle('A5:T5', [
            'fill' => '#ADD8E6',
        ]);

        foreach ($data as $row) {

            $rows = [
                $row->tgl_plan_fix ?? '',
                $row->tgl_trans_fix ?? '',
                $row->sewing_line ?? '',
                $row->kpno ?? '',
                $row->buyer ?? '',
                $row->styleno ?? '',

                (float) ($row->smv ?? 0),
                (float) ($row->man_power ?? 0),
                (float) ($row->mins_avail ?? 0),
                (float) ($row->target ?? 0),
                (float) ($row->tot_output ?? 0),

                $row->curr ?? '',

                (float) ($row->cm_price ?? 0),
                (float) ($row->earning ?? 0),
                (float) ($row->kurs_tengah ?? 0),
                (float) ($row->tot_earning_rupiah ?? 0),
                (float) ($row->mins_prod ?? 0),
                (float) ($row->eff_line ?? 0),
                (float) ($row->rfts ?? 0),
                (float) ($row->jam_kerja_act ?? 0),
            ];

            $sheet->writeRow(
                $rows,
                [
                    'border' => 'thin',
                ]
            );
        }

        foreach (range('A', 'T') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}
