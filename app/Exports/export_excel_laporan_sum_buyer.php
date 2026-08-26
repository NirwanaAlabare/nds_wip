<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class export_excel_laporan_sum_buyer implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $buyer, $rowCount;

    public function __construct($start_date, $end_date, $buyer)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->buyer = $buyer;
    }

    public function view(): View
    {

        $bulan_awal = date('n', strtotime($this->start_date)); // Returns month as number without leading zero (e.g., 9)
        $tahun_awal = date('Y', strtotime($this->start_date)); // Returns full year (e.g., 2025)

        $bulan_akhir = date('n', strtotime($this->end_date)); // Returns month as number without leading zero (e.g., 9)
        $tahun_akhir = date('Y', strtotime($this->end_date)); // Returns full year (e.g., 2025)

        $today = date('Y-m-d');
        $month = date('m');
        $year = date('Y');

        $buyer = $this->buyer;



        if ($buyer === null || $buyer === '') {
            $cond = "";
            $hav = "";
        } else {
            $cond = "AND a.buyer = '$buyer'";
            $hav = "having buyer = '$buyer'";
        }

        if ($this->start_date < $today && $this->end_date < $today) {

            $rawData = DB::connection('mysql_sb')->select("WITH sum_earning_buyer as (
select
buyer,
sum(tot_earning_rupiah) as sum_tot_earning_rupiah,
sum(est_full_earning) as sum_est_full_earning,
sum(est_earning_prod) as sum_est_earning_prod,
sum(est_tot_cost) as est_tot_cost,
sum(est_cost_prod) as sum_est_cost_prod,
sum(est_earning_mkt) as sum_est_earning_mkt,
sum(est_cost_mkt) as sum_est_cost_mkt
from mgt_rep_tmp_earning
where tanggal >= '$this->start_date' and tanggal <= '$this->end_date'
group by buyer
)


select
a.buyer,
sum(coalesce(target,0)) as tot_target,
sum(coalesce(tot_output,0)) as tot_output,
sum(mins_avail) as sum_mins_avail,
sum(mins_prod) as sum_mins_prod,
ROUND(((sum(mins_prod) / sum(mins_avail)) * 100),2) as eff,
sum(tot_earning_rupiah) as earn_prod,
est_tot_cost,
sum(tot_earning_rupiah) - est_tot_cost as blc,
ROUND((((sum(tot_earning_rupiah) - est_tot_cost) / sum(tot_earning_rupiah)) * 100),2) as percent_earning,

sum_est_full_earning,
sum_est_full_earning - est_tot_cost as blc_full_earn_cost_prod,
ROUND((((sum_est_full_earning - est_tot_cost) / sum_est_full_earning) * 100),2) as percent_full_earning_cost,

sum_est_earning_prod,
sum_est_cost_prod,
sum_est_earning_prod - sum_est_cost_prod as blc_earn_cost_prod,
ROUND(((sum_est_earning_prod - sum_est_cost_prod) / sum_est_earning_prod) * 100,2) as percent_earn_cost_prod,

sum_est_earning_mkt,
sum_est_cost_mkt,
sum_est_earning_mkt - sum_est_cost_mkt as blc_earn_cost_mkt,
ROUND((((sum_est_earning_mkt - sum_est_cost_mkt) / sum_est_earning_mkt) * 100),2) as percent_earn_cost_mkt

from mgt_rep_tmp_earn a
left join sum_earning_buyer b on a.buyer = b.buyer
where a.tgl_trans >= '$this->start_date' and a.tgl_trans <= '$this->end_date' $cond
group by a.buyer
order by buyer asc");
        } else {

            $rawData = DB::connection('mysql_sb')->select("WITH sum_cost as (
 SELECT
    a.cost_no, kpno, supplier, styleno, product_item, season_desc, curr,
    so_date, status, qty_so, price_so, cost_date, status_cost, qty_cost,

    COALESCE(b.ttl_fabric,0)  ttl_fabric,
    COALESCE(b.ttl_accsew,0)  ttl_accsew,
    COALESCE(b.ttl_accpack,0) ttl_accpack,
    (COALESCE(b.ttl_fabric,0) + COALESCE(b.ttl_accsew,0) + COALESCE(b.ttl_accpack,0)) ttl_material,

    COALESCE(c.ttl_cmt,0)     ttl_cmt,
    COALESCE(c.ttl_embro,0)   ttl_embro,
    COALESCE(c.ttl_wash,0)    ttl_wash,
    COALESCE(c.ttl_print,0)   ttl_print,
    COALESCE(c.ttl_wrapbut,0) ttl_wrapbut,
    COALESCE(c.ttl_compbut,0) ttl_compbut,
    COALESCE(c.ttl_label,0)   ttl_label,
    COALESCE(c.ttl_laser,0)   ttl_laser,
    (COALESCE(c.ttl_cmt,0) + COALESCE(c.ttl_embro,0) + COALESCE(c.ttl_wash,0) + COALESCE(c.ttl_print,0)
     + COALESCE(c.ttl_wrapbut,0) + COALESCE(c.ttl_compbut,0) + COALESCE(c.ttl_label,0) + COALESCE(c.ttl_laser,0)) ttl_manufacturing,

    COALESCE(d.ttl_develop,0)       ttl_develop,
    COALESCE(d.ttl_overhead,0)      ttl_overhead,
    COALESCE(d.ttl_market,0)        ttl_market,
    COALESCE(d.ttl_shipp,0)         ttl_shipp,
    COALESCE(d.ttl_import,0)        ttl_import,
    COALESCE(d.ttl_handl,0)         ttl_handl,
    COALESCE(d.ttl_test,0)          ttl_test,
    COALESCE(d.ttl_fabhandl,0)      ttl_fabhandl,
    COALESCE(d.ttl_service,0)       ttl_service,
    COALESCE(d.ttl_clearcost,0)     ttl_clearcost,
    COALESCE(d.ttl_development,0)   ttl_development,
    COALESCE(d.ttl_unexcost,0)      ttl_unexcost,
    COALESCE(d.ttl_managementfee,0) ttl_managementfee,
    COALESCE(d.ttl_profit,0)        ttl_profit,
    (COALESCE(d.ttl_develop,0) + COALESCE(d.ttl_overhead,0) + COALESCE(d.ttl_market,0) + COALESCE(d.ttl_shipp,0)
     + COALESCE(d.ttl_import,0) + COALESCE(d.ttl_handl,0) + COALESCE(d.ttl_test,0) + COALESCE(d.ttl_fabhandl,0)
     + COALESCE(d.ttl_service,0) + COALESCE(d.ttl_clearcost,0) + COALESCE(d.ttl_development,0)
     + COALESCE(d.ttl_unexcost,0) + COALESCE(d.ttl_managementfee,0) + COALESCE(d.ttl_profit,0)) ttl_others

FROM (
    SELECT a.cost_no, a.kpno, b.supplier, styleno, product_item, season_desc,
           IF(so.curr IS NULL, a.curr, so.curr) curr,
           so_date, IF(so.cancel_h = 'Y','CANCEL','-') status,
           so.qty qty_so, so.fob price_so, cost_date, a.status status_cost, a.qty qty_cost
    FROM act_costing a
    INNER JOIN mastersupplier b ON a.id_buyer = b.Id_Supplier
    INNER JOIN masterproduct mp ON a.id_product = mp.id
    LEFT JOIN so ON so.id_cost = a.id
    LEFT JOIN masterseason ms ON ms.id_season = so.id_season
    WHERE cost_date >= '2025-01-01' AND a.aktif = 'Y'
    GROUP BY cost_no
) a

LEFT JOIN (
    SELECT cost_no,
        SUM(CASE WHEN mattype = 'FABRIC'              THEN total END) ttl_fabric,
        SUM(CASE WHEN mattype = 'ACCESORIES SEWING'    THEN total END) ttl_accsew,
        SUM(CASE WHEN mattype = 'ACCESORIES PACKING'   THEN total END) ttl_accpack
    FROM (
        SELECT cost_no, mattype, IF(curr='IDR', val_idr, val_usd) total
        FROM act_material
        WHERE cost_date >= '2025-01-01'
    ) x
    GROUP BY cost_no
) b ON b.cost_no = a.cost_no

LEFT JOIN (
    SELECT cost_no,
        SUM(CASE WHEN mattype = 'CMT'                        THEN total END) ttl_cmt,
        SUM(CASE WHEN mattype = 'EMBRODEIRY'                 THEN total END) ttl_embro,
        SUM(CASE WHEN mattype = 'WASHING'                    THEN total END) ttl_wash,
        SUM(CASE WHEN mattype = 'PRINTING'                   THEN total END) ttl_print,
        SUM(CASE WHEN mattype = 'WRAPPED BUTTON'              THEN total END) ttl_wrapbut,
        SUM(CASE WHEN mattype = 'COMPLEXITY MAKLOON BUTTON'   THEN total END) ttl_compbut,
        SUM(CASE WHEN mattype = 'LABEL PRINT'                 THEN total END) ttl_label,
        SUM(CASE WHEN mattype = 'LASER CUTTING'               THEN total END) ttl_laser
    FROM (
        SELECT cost_no, mattype, IF(curr='IDR', val_idr, val_usd) total
        FROM act_manufacturing
        WHERE cost_date >= '2025-01-01'
    ) x
    GROUP BY cost_no
) c ON c.cost_no = a.cost_no

LEFT JOIN (
    SELECT cost_no,
        SUM(CASE WHEN mattype = 'DEVELOPMENT'       THEN total END) ttl_develop,
        SUM(CASE WHEN mattype = 'OVERHEAD'          THEN total END) ttl_overhead,
        SUM(CASE WHEN mattype = 'MARKETING'         THEN total END) ttl_market,
        SUM(CASE WHEN mattype = 'SHIPPING'          THEN total END) ttl_shipp,
        SUM(CASE WHEN mattype = 'IMPORT COST'       THEN total END) ttl_import,
        SUM(CASE WHEN mattype = 'HANDLING'          THEN total END) ttl_handl,
        SUM(CASE WHEN mattype = 'TESTING'           THEN total END) ttl_test,
        SUM(CASE WHEN mattype = 'FABRIC HANDLING'   THEN total END) ttl_fabhandl,
        SUM(CASE WHEN mattype = 'SERVICE CHARGE'    THEN total END) ttl_service,
        SUM(CASE WHEN mattype = 'CLEARANCE  COST'   THEN total END) ttl_clearcost,
        0                                                              ttl_development,
        SUM(CASE WHEN mattype = 'UNEXPECTED COST'   THEN total END) ttl_unexcost,
        SUM(CASE WHEN mattype = 'MANAGEMENT FEE'    THEN total END) ttl_managementfee,
        SUM(CASE WHEN mattype = 'PROFIT'            THEN total END) ttl_profit
    FROM (
        SELECT cost_no, mattype, IF(curr='IDR', val_idr, val_usd) total
        FROM act_others
        WHERE cost_date >= '2025-01-01'
    ) x
    GROUP BY cost_no
) d ON d.cost_no = a.cost_no

 ),
earn as (
SELECT
                    a.tgl_trans,
                    concat((DATE_FORMAT(a.tgl_trans,  '%d')), '-',left(DATE_FORMAT(a.tgl_trans,  '%M'),3),'-',DATE_FORMAT(a.tgl_trans,  '%Y')) tgl_trans_fix,
                    concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
                    ul.username sewing_line,
                    ms.supplier buyer,
					a.master_plan_id,
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
					ac.curr,
					acm.allowance,
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
                    round((cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
                    round(sum(a.tot_output) * mp.smv,2) mins_prod,
                    round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line,
                    round(((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)),2) jam_kerja_act,
                    round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts
                from
                (
                    select
                    date(a.updated_at)tgl_trans,
                    so_det_id,
                    master_plan_id,
                    count(so_det_id) tot_output,
                    time(max(a.updated_at)) jam_akhir_input,
                    userpassword.username
                    from output_rfts a
                    left join user_sb_wip on user_sb_wip.id = a.created_by
                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where a.updated_at >= '$today 00:00:00' and a.updated_at <= '$today 23:59:59'
                    group by master_plan_id, userpassword.username, date(a.updated_at)
										having userpassword.username != 'line_sample_prod'
                ) a
                inner join so_det sd on a.so_det_id = sd.id
                inner join so on sd.id_so = so.id
                inner join act_costing ac on so.id_cost = ac.id
                inner join userpassword ul on ul.username = a.username
                inner join master_plan mp on a.master_plan_id = mp.id
                inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                left join (
                    select date(output_rfts.updated_at) tgl_trans_line,max(time(output_rfts.updated_at)) jam_akhir_input_line,count(output_rfts.so_det_id) tot_output_line,
                            case
                            when time(max(output_rfts.updated_at)) >= '12:00:00' and time(max(output_rfts.updated_at)) <= '18:44:59' THEN '01:00:00'
                            when time(max(output_rfts.updated_at)) <= '12:00:00'  THEN '00:00:00'
                            when time(max(output_rfts.updated_at)) >= '18:45:00'  THEN '01:30:00'
                            END as istirahat,
                    userpassword.username
                    from output_rfts
                    left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where output_rfts.updated_at >= '$today 00:00:00' and output_rfts.updated_at <= '$today 23:59:59' group by userpassword.username, date(output_rfts.updated_at)
                ) op on a.tgl_trans = op.tgl_trans_line and ul.username = op.username
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
                            where a.updated_at >= '$today 00:00:00' and a.updated_at <= '$today 23:59:59' and status = 'NORMAL'
                            group by master_plan_id, userpassword.username, date(a.updated_at)
                    ) a
                    inner join master_plan mp on a.master_plan_id = mp.id
                    group by tgl_trans_rfts, master_plan_id
                ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
                left join
                (
                    select min(id), man_power, sewing_line, tgl_plan from master_plan
                    where tgl_plan >= '$today' and  tgl_plan <= '$today' and cancel = 'N'
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
                                    WHERE updated_at >= '$today 00:00:00' AND updated_at <= '$today 23:59:59'
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
                            WHERE updated_at >= '$today 00:00:00' AND updated_at <= '$today 23:59:59'
                        ) a_dates
                        JOIN masterrate mr
                        ON mr.tanggal <= a_dates.tgl_trans
                        GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN masterrate k
                    ON k.tanggal = x.max_kurs_date
                    WHERE k.v_codecurr = 'HARIAN'
                ) mr ON a.tgl_trans = mr.tgl_trans
                group by ul.username, ac.kpno, ac.Styleno, a.tgl_trans
                order by a.tgl_trans asc, ul.username asc, ac.kpno asc
),
dd AS (
        SELECT a.bulan,
               a.nama_bulan,
               CAST(a.tahun AS UNSIGNED) AS tahun,
               COUNT(a.tanggal)          AS tot_working_days
        FROM   dim_date a
        LEFT   JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
        WHERE  a.status_prod = 'KERJA'
          AND  (b.status_absen <> 'LN' OR b.status_absen IS NULL)
AND CAST(a.bulan AS UNSIGNED) >= '$year'
AND CAST(a.tahun AS UNSIGNED) >= '$month'
AND CAST(a.tahun AS UNSIGNED) <= '$month'
        GROUP  BY a.bulan, a.nama_bulan, a.tahun
),
dim_tgl AS (
        SELECT tanggal,
               CASE WHEN status_prod IN ('KERJA','LIBUR') THEN status_prod END AS stat_kerja
        FROM   dim_date
        WHERE  tanggal >= '$this->start_date' and tanggal <= '$this->end_date'
),
dc AS (
SELECT
no_coa,
dd.bulan,
nama_bulan,
dd.tahun,
projection,
round(sum(projection / tot_working_days),2) AS daily_cost
FROM mgt_rep_daily_cost a
LEFT JOIN dd ON a.bulan = dd.bulan AND a.tahun = dd.tahun
WHERE CAST(a.bulan AS UNSIGNED) >= '$year'
AND CAST(a.tahun AS UNSIGNED) >= '$month'
AND CAST(a.tahun AS UNSIGNED) <= '$month'
GROUP BY no_coa, dd.bulan, dd.tahun
),
coa AS (
        SELECT no_coa,
               nama_coa,
               CASE
                 WHEN eng_categori4 = 'DIRECT LABOR COST'                THEN 'direct labor'
                 WHEN eng_categori4 = 'INDIRECT LABOR COST'              THEN 'indirect labor'
                 WHEN eng_categori4 = 'FIXED OVERHEAD COST'              THEN 'overhead labor'
                 WHEN eng_categori4 = 'SELLING EXPENSE'                  THEN 'selling expense'
                 WHEN eng_categori4 = 'GENERAL & ADMINISTRATION EXPENSE' THEN 'ga expense'
                 WHEN eng_categori3 = 'OTHER EXPENSE'                    THEN 'other expense'
               END AS nm_labor,
               CASE
                 WHEN eng_categori4 = 'DIRECT LABOR COST'                THEN 'PRODUCTION'
                 WHEN eng_categori4 = 'INDIRECT LABOR COST'              THEN 'SUPPORTING PRODUCTION'
                 WHEN eng_categori4 = 'SELLING EXPENSE'                  THEN 'SUPPORTING SELLING'
                 WHEN eng_categori4 = 'GENERAL & ADMINISTRATION EXPENSE' THEN 'SUPPORTING GENERAL & ADMINISTRATION'
               END AS dept_filter
        FROM   mastercoa_v2
        WHERE  mgt_report = 'Y'
),
map_coa AS (
        SELECT DISTINCT a.no_coa, b.no_cc
        FROM   mastercoa_v2 a
        JOIN   b_master_cc  b
               ON CASE b.group2
                    WHEN 'SUPPORTING GENERAL & ADMINISTRATION' THEN a.support_gen_adm
                    WHEN 'SUPPORTING PRODUCTION'               THEN a.support_prod
                    WHEN 'PRODUCTION'                          THEN a.prod
                    WHEN 'SUPPORTING SELLING'                  THEN a.support_sell
                  END = 'Y'
        WHERE  b.status = 'Active' AND b.id_pc <> 'NAK'
),
m_labor AS (
        SELECT tanggal_berjalan, sub_dept_id, group_department,
               SUM(bruto)   AS wage,
               SUM(bpjs_tk) AS bpjs_tk,
               SUM(bpjs_ks) AS bpjs_ks,
               SUM(thr)     AS thr
        FROM   mgt_rep_labor
        WHERE  tanggal_berjalan BETWEEN '$this->start_date' AND '$this->end_date'
          AND  status_staff = 'NON STAFF'
        GROUP  BY sub_dept_id, group_department, tanggal_berjalan
),
daily_cost as (
SELECT d.tanggal,
           d.stat_kerja,
           a.no_coa,
           a.nama_coa,
           COALESCE(dc.projection, 0) AS projection,
           COALESCE(dc.daily_cost, 0) AS daily_cost,
           CASE
             WHEN TRIM(a.no_coa) IN ('8.97.01','8.98.01','8.99.01')
               THEN CASE WHEN d.stat_kerja = 'KERJA' THEN COALESCE(dc.daily_cost, 0) ELSE 0 END
             ELSE
               CASE WHEN d.stat_kerja = 'KERJA' THEN COALESCE(dc.daily_cost, 0) ELSE 0 END
               + CASE WHEN d.stat_kerja IS NULL THEN 0 ELSE
                   COALESCE(SUM(CASE
                     WHEN a.nama_coa LIKE '%GAJI%'                 THEN c.wage
                     WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN c.bpjs_tk
                     WHEN a.nama_coa LIKE '%BPJS KESEHATAN%'       THEN c.bpjs_ks
                     WHEN a.nama_coa LIKE '%THR%'                  THEN c.thr
                   END), 0)
                 END
           END AS tot_labor,
           a.nm_labor
    FROM   dim_tgl d
    CROSS  JOIN coa a
    LEFT   JOIN dc ON dc.no_coa = a.no_coa
                  AND CAST(dc.bulan AS UNSIGNED) = MONTH(d.tanggal)
                  AND CAST(dc.tahun AS UNSIGNED) = YEAR(d.tanggal)
    LEFT   JOIN map_coa b  ON b.no_coa = a.no_coa
    LEFT   JOIN m_labor c  ON c.sub_dept_id      = b.no_cc
                          AND c.tanggal_berjalan = d.tanggal
                          AND (a.dept_filter IS NULL OR c.group_department = a.dept_filter)
    WHERE  a.nm_labor IS NOT NULL
    GROUP  BY d.tanggal, a.no_coa
    ORDER  BY d.tanggal ASC, a.no_coa ASC
),
sum_daily_cost as (
select
tanggal,
SUM(tot_labor) sum_tot_labor,
SUM(CASE WHEN nm_labor = 'direct labor' THEN tot_labor ELSE 0 END) AS sum_direct_labor,
SUM(CASE WHEN nm_labor = 'indirect labor' THEN tot_labor ELSE 0 END) AS sum_indirect_labor,
SUM(CASE WHEN nm_labor = 'overhead labor' THEN tot_labor ELSE 0 END) AS sum_overhead_labor,
SUM(CASE WHEN nm_labor = 'selling expense' THEN tot_labor ELSE 0 END) AS sum_selling_expense_labor,
SUM(CASE WHEN nm_labor = 'ga expense' THEN tot_labor ELSE 0 END) AS sum_ga_expense_labor,
SUM(CASE WHEN nm_labor = 'other expense' THEN tot_labor ELSE 0 END) AS sum_other_expense_labor
from daily_cost group by tanggal
),
sum_earn as (
select tgl_trans, sum(mins_avail) sum_mins_avail from earn group by tgl_trans
),
earning as (
select
-- est earning
dt.tanggal,
concat((DATE_FORMAT(dt.tanggal,  '%d')), '-',left(DATE_FORMAT(dt.tanggal,  '%M'),3),'-',DATE_FORMAT(dt.tanggal,  '%Y')) as tanggal_fix,
dt.stat_kerja,
a.tgl_trans,
a.master_plan_id,
sewing_line,
a.buyer,
a.kpno,
coalesce(tot_earning_rupiah,0) as tot_earning_rupiah,
COALESCE(a.mins_avail, 0) AS mins_avail,
COALESCE(a.mins_prod, 0) AS mins_prod,
COALESCE(a.eff_line, 0) AS eff_line,
COALESCE(sum_mins_avail, 0) AS sum_mins_avail,

CASE
    WHEN stat_kerja = 'libur' THEN
        ROUND(COALESCE(sum_tot_labor, 0), 2)
    WHEN COALESCE(c.sum_mins_avail, 0) = 0
      OR COALESCE(a.mins_avail, 0) = 0 THEN
        ROUND(COALESCE(sum_tot_labor, 0), 2)
    ELSE
        ROUND(
            (COALESCE(sum_tot_labor, 0) / c.sum_mins_avail)
            * COALESCE(a.mins_avail, 0),
        2)
END AS est_tot_cost,
ROUND(
    COALESCE(tot_earning_rupiah, 0) -
CASE
    WHEN stat_kerja = 'libur' THEN
        ROUND(COALESCE(sum_tot_labor, 0), 2)
    WHEN COALESCE(c.sum_mins_avail, 0) = 0
      OR COALESCE(a.mins_avail, 0) = 0 THEN
        ROUND(COALESCE(sum_tot_labor, 0), 2)
    ELSE
        ROUND(
            (COALESCE(sum_tot_labor, 0) / c.sum_mins_avail)
            * COALESCE(a.mins_avail, 0),
        2)
END,
    2
) AS blc,
  ROUND((
    (COALESCE(tot_earning_rupiah, 0) - ((COALESCE(sum_tot_labor, 0) / COALESCE(c.sum_mins_avail, 0)) * COALESCE(a.mins_avail, 0)))
    / NULLIF(COALESCE(tot_earning_rupiah, 0), 0)
  ) * 100, 2) AS percent_est_earn,
-- Full earning
COALESCE(a.cm_price,0) AS cm_price,
allowance,
kurs_tengah,
a.curr,
COALESCE(tot_output,0) AS tot_output,

(a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp)) full_cm_price,
round(
case
		when a.curr = 'IDR' then tot_output * (a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp))
		else ((tot_output * kurs_tengah)  * (a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp)))
		end,2) as est_full_earning,
ROUND(
  (CASE WHEN COALESCE(a.curr, '') = 'IDR'
    THEN COALESCE(tot_output, 0) * (COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
         (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0)))
    ELSE (COALESCE(tot_output, 0) * COALESCE(kurs_tengah, 0)) * (COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
         (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0)))
  END)
  -
  ((COALESCE(sum_tot_labor, 0) / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)) * COALESCE(a.mins_avail, 0)),
2
) AS blc_full_earn,
ROUND((
  (CASE WHEN COALESCE(a.curr, '') = 'IDR'
    THEN COALESCE(tot_output, 0) * (COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
         (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0)))
    ELSE (COALESCE(tot_output, 0) * COALESCE(kurs_tengah, 0)) * (COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
         (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0)))
  END)
  - ((COALESCE(sum_tot_labor, 0) / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)) * COALESCE(a.mins_avail, 0)))
  /
  NULLIF(
    (CASE WHEN COALESCE(a.curr, '') = 'IDR'
      THEN COALESCE(tot_output, 0) * (COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
           (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0)))
      ELSE (COALESCE(tot_output, 0) * COALESCE(kurs_tengah, 0)) * (COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
           (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0)))
    END),
    0
  ) * 100, 2) AS percent_full_earn,

-- est earning production
COALESCE(tot_earning_rupiah, 0) AS est_earning_prod,
ROUND(((COALESCE(b.sum_direct_labor, 0) + COALESCE(b.sum_indirect_labor, 0) + COALESCE(b.sum_overhead_labor, 0)) / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)) * COALESCE(a.mins_avail, 0), 2) AS est_cost_prod,
ROUND(COALESCE(tot_earning_rupiah, 0) - (((COALESCE(b.sum_direct_labor, 0) + COALESCE(b.sum_indirect_labor, 0) + COALESCE(b.sum_overhead_labor, 0)) / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)) * COALESCE(a.mins_avail, 0)), 2) AS blc_est_cost_prod,
ROUND(((COALESCE(tot_earning_rupiah, 0) - (((COALESCE(b.sum_direct_labor, 0) + COALESCE(b.sum_indirect_labor, 0) + COALESCE(b.sum_overhead_labor, 0)) / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)) * COALESCE(a.mins_avail, 0))) / NULLIF(COALESCE(tot_earning_rupiah, 0), 0)) * 100, 2) AS percent_est_cost_prod,

-- est earning mkt
ROUND((
  CASE
    WHEN COALESCE(a.curr, '') = 'IDR' THEN
      COALESCE(tot_output, 0) * (
        COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
        (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0))
      )
    ELSE
      (COALESCE(tot_output, 0) * COALESCE(kurs_tengah, 0)) * (
        COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
        (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0))
      )
  END
  - COALESCE(tot_earning_rupiah, 0)
), 2) AS est_earning_mkt,

ROUND(
  COALESCE(
    (
      (COALESCE(b.sum_selling_expense_labor, 0) + COALESCE(b.sum_ga_expense_labor, 0) + COALESCE(b.sum_other_expense_labor, 0))
      / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)
    ) * COALESCE(a.mins_avail, 0),
  0), 2) AS est_cost_mkt,

ROUND((
  (
    CASE
      WHEN COALESCE(a.curr, '') = 'IDR' THEN
        COALESCE(tot_output, 0) * (
          COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
          (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0))
        )
      ELSE
        (COALESCE(tot_output, 0) * COALESCE(kurs_tengah, 0)) * (
          COALESCE(a.cm_price, 0) + (COALESCE(a.cm_price, 0) * (COALESCE(allowance, 0) / 100)) +
          (COALESCE(d.ttl_others, 0) - COALESCE(ttl_service, 0) - COALESCE(ttl_handl, 0) - COALESCE(ttl_import, 0) - COALESCE(ttl_shipp, 0))
        )
    END
    - COALESCE(tot_earning_rupiah, 0)
  )
  - (
    (COALESCE(b.sum_selling_expense_labor, 0) + COALESCE(b.sum_ga_expense_labor, 0) + COALESCE(b.sum_other_expense_labor, 0))
    / NULLIF(COALESCE(c.sum_mins_avail, 0), 0)
  ) * COALESCE(a.mins_avail, 0)
), 2) AS blc_earn_mkt,


coalesce(round(round((((case
		when a.curr = 'IDR' then tot_output * (a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp))
		else ((tot_output * kurs_tengah)  * (a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp)))
		end) -
tot_earning_rupiah)) - ((((b.sum_selling_expense_labor + b.sum_ga_expense_labor + b.sum_other_expense_labor) / c.sum_mins_avail) * a.mins_avail)),2)
/
round(((case
		when a.curr = 'IDR' then tot_output * (a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp))
		else ((tot_output * kurs_tengah)  * (a.cm_price + (a.cm_price * (allowance / 100)) + (d.ttl_others - ttl_service - ttl_handl - ttl_import - ttl_shipp)))
		end) -
tot_earning_rupiah),2) * 100,2),0) as percent_earn_mkt

from dim_tgl dt
left join earn a on dt.tanggal = a.tgl_trans
left join sum_daily_cost b on dt.tanggal = b.tanggal
left join sum_earn c on dt.tanggal = c.tgl_trans
left join sum_cost d on a.kpno = d.kpno
where dt.tanggal = '$today'
order by dt.tanggal asc, sewing_line asc
),
sum_earning as (
select
tanggal,
sum(tot_earning_rupiah) as sum_tot_earning_rupiah,
sum(est_full_earning) as sum_est_full_earning,
sum(est_earning_prod) as sum_est_earning_prod,
sum(est_cost_prod) as sum_est_cost_prod,
sum(est_earning_mkt) as sum_est_earning_mkt,
sum(est_cost_mkt) as sum_est_cost_mkt
from earning group by tanggal
),
sum_labor as (
select
tanggal_berjalan,
SUM(CASE WHEN department_name = 'sewing' and status_staff = 'NON STAFF' THEN man_power ELSE 0 END) AS sewing_man_power,
SUM(CASE WHEN department_name = 'sewing' and status_staff = 'NON STAFF' THEN absen_menit ELSE 0 END) AS sewing_absen_menit,
SUM(man_power)  AS tot_man_power
from mgt_rep_labor
WHERE tanggal_berjalan = '$today'
group by tanggal_berjalan
order by tanggal_berjalan asc
),
m_kurs_bi as (
select * from master_kurs_bi where tanggal_kurs_bi = '$today'
),
sum_full_earning as (
select
a.tanggal,
concat((DATE_FORMAT(a.tanggal,  '%d')), '-',left(DATE_FORMAT(a.tanggal,  '%M'),3),'-',DATE_FORMAT(a.tanggal,  '%Y')) as tanggal_fix,

ROUND(coalesce(b.sum_tot_earning_rupiah,0),2) as sum_tot_earning_rupiah,
ROUND(coalesce(sum_tot_labor,0),2) as est_tot_cost,
ROUND(coalesce(b.sum_tot_earning_rupiah,0) - coalesce(sum_tot_labor,0),2) as blc,


ROUND(coalesce(b.sum_est_full_earning,0),2) as sum_est_full_earning,
ROUND(coalesce(b.sum_est_full_earning,0) - coalesce(sum_tot_labor,0),2) as blc_full_earning,

ROUND(coalesce(sum_est_earning_prod,0),2) as sum_est_earning_prod,
ROUND(coalesce(sum_est_cost_prod,0),2) as sum_est_cost_prod,
ROUND(coalesce(sum_est_earning_prod,0) - coalesce(sum_est_cost_prod,0),2) as blc_est_earn_cost_prod,

ROUND(coalesce(sum_est_earning_mkt,0),2) as sum_est_earning_mkt,
ROUND(coalesce(sum_est_cost_mkt,0),2) as sum_est_cost_mkt,
ROUND(coalesce(sum_est_earning_mkt,0) - coalesce(sum_est_cost_mkt,0),2) as blc_est_earn_cost_mkt

from dim_tgl a
left join sum_earning b on a.tanggal = b.tanggal
left join sum_daily_cost c on a.tanggal = c.tanggal
left join sum_labor d on a.tanggal = d.tanggal_berjalan
left join m_kurs_bi e on a.tanggal = e.tanggal_kurs_bi
order by a.tanggal asc ),
sum_earning_buyer as (
select
buyer,
sum(tot_earning_rupiah) as sum_tot_earning_rupiah,
sum(est_full_earning) as sum_est_full_earning,
sum(est_earning_prod) as sum_est_earning_prod,
sum(est_tot_cost) as est_tot_cost,
sum(est_cost_prod) as sum_est_cost_prod,
sum(est_earning_mkt) as sum_est_earning_mkt,
sum(est_cost_mkt) as sum_est_cost_mkt
from earning group by buyer),
sum_full_earn_wo_buyer as (
  SELECT SUM(est_tot_cost) AS total_cost
  FROM sum_full_earning
  WHERE sum_tot_earning_rupiah = 0
),
sum_buyer as (
select
a.buyer,
sum(coalesce(target,0)) as tot_target,
sum(coalesce(tot_output,0)) as tot_output,
sum(mins_avail) as sum_mins_avail,
sum(mins_prod) as sum_mins_prod,
ROUND(((sum(mins_prod) / sum(mins_avail)) * 100),2) as eff,
sum(tot_earning_rupiah) as earn_prod,
est_tot_cost,
sum(tot_earning_rupiah) - est_tot_cost as blc,
ROUND((((sum(tot_earning_rupiah) - est_tot_cost) / sum(tot_earning_rupiah)) * 100),2) as percent_earning,

sum_est_full_earning,
sum_est_full_earning - est_tot_cost as blc_full_earn_cost_prod,
ROUND((((sum_est_full_earning - est_tot_cost) / sum_est_full_earning) * 100),2) as percent_full_earning_cost,

sum_est_earning_prod,
sum_est_cost_prod,
sum_est_earning_prod - sum_est_cost_prod as blc_earn_cost_prod,
ROUND(((sum_est_earning_prod - sum_est_cost_prod) / sum_est_earning_prod) * 100,2) as percent_earn_cost_prod,

sum_est_earning_mkt,
sum_est_cost_mkt,
sum_est_earning_mkt - sum_est_cost_mkt as blc_earn_cost_mkt,
ROUND((((sum_est_earning_mkt - sum_est_cost_mkt) / sum_est_earning_mkt) * 100),2) as percent_earn_cost_mkt

from earn a
left join sum_earning_buyer b on a.buyer = b.buyer
group by a.buyer
)

        SELECT
        a.buyer,
        sum(tot_target) as tot_target,
        sum(tot_output) as tot_output,
        sum(sum_mins_avail) as sum_mins_avail,
        sum(sum_mins_prod) as sum_mins_prod,
        ROUND(((sum(sum_mins_prod) / sum(sum_mins_avail)) * 100),2) as eff,
        sum(earn_prod) as earn_prod,
        sum(est_tot_cost) as est_tot_cost,
        sum(blc) as blc,
        ROUND((((sum(earn_prod) - sum(est_tot_cost)) / sum(earn_prod)) * 100),2) as percent_earning,

        sum(sum_est_full_earning) as sum_est_full_earning,
        sum(blc_full_earn_cost_prod) as blc_full_earn_cost_prod,
        ROUND((((sum(sum_est_full_earning) - sum(est_tot_cost)) / sum(sum_est_full_earning)) * 100),2) as percent_full_earning_cost,

        sum(sum_est_earning_prod) as sum_est_earning_prod,
        sum(sum_est_cost_prod) as sum_est_cost_prod,
        sum(blc_earn_cost_prod) as blc_earn_cost_prod,
        ROUND(((sum(sum_est_earning_prod) - sum(sum_est_cost_prod)) / sum(sum_est_earning_prod)) * 100,2) as percent_earn_cost_prod,

        sum(sum_est_earning_mkt) as sum_est_earning_mkt,
        sum(sum_est_cost_mkt) as sum_est_cost_mkt,
        sum(blc_earn_cost_mkt) as blc_earn_cost_mkt,
        ROUND((((sum(sum_est_earning_mkt) - sum(sum_est_cost_mkt)) / sum(sum_est_earning_mkt)) * 100),2) as percent_earn_cost_mkt

        FROM (
        SELECT * from sum_buyer
        UNION ALL
        select
        a.buyer,
        sum(coalesce(target,0)) as tot_target,
        sum(coalesce(tot_output,0)) as tot_output,
        sum(mins_avail) as sum_mins_avail,
        sum(mins_prod) as sum_mins_prod,
        ROUND(((sum(mins_prod) / sum(mins_avail)) * 100),2) as eff,
        sum(tot_earning_rupiah) as earn_prod,
        est_tot_cost,
        sum(tot_earning_rupiah) - est_tot_cost as blc,
        ROUND((((sum(tot_earning_rupiah) - est_tot_cost) / sum(tot_earning_rupiah)) * 100),2) as percent_earning,

        sum_est_full_earning,
        sum_est_full_earning - est_tot_cost as blc_full_earn_cost_prod,
        ROUND((((sum_est_full_earning - est_tot_cost) / sum_est_full_earning) * 100),2) as percent_full_earning_cost,

        sum_est_earning_prod,
        sum_est_cost_prod,
        sum_est_earning_prod - sum_est_cost_prod as blc_earn_cost_prod,
        ROUND(((sum_est_earning_prod - sum_est_cost_prod) / sum_est_earning_prod) * 100,2) as percent_earn_cost_prod,

        sum_est_earning_mkt,
        sum_est_cost_mkt,
        sum_est_earning_mkt - sum_est_cost_mkt as blc_earn_cost_mkt,
        ROUND((((sum_est_earning_mkt - sum_est_cost_mkt) / sum_est_earning_mkt) * 100),2) as percent_earn_cost_mkt

        from mgt_rep_tmp_earn a
        left join sum_earning_buyer b on a.buyer = b.buyer
        where a.tgl_trans >= '$this->start_date' and a.tgl_trans <= '$this->end_date'
        group by a.buyer
        ) a
        group by buyer
        $hav
        order by buyer asc
        ");
        }
        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('management_report.export_excel_laporan_sum_buyer', [
            'rawData' => $rawData,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // e.g. 'Z'
                $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                for ($i = 1; $i <= $columnIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $cell = $colLetter . '2'; // Only row 2

                    $sheet->getStyle($cell)->applyFromArray([
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFD9EDF7'], // Light blue
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FF000000'], // Black text
                        ],
                    ]);
                }

                // ===== 3. Apply border to whole table =====
                $range = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                for ($i = 2; $i <= $columnIndex; $i++) { // Start from column 2 (i.e., column 'B')
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

                    for ($row = 3; $row <= $highestRow; $row++) { // Start from row 3 to skip header
                        $cell = $colLetter . $row;
                        $value = $sheet->getCell($cell)->getValue();

                        // If cell is null or empty, set it to 0
                        if ($value === null || $value === '') {
                            $sheet->setCellValue($cell, 0);
                        }

                        // Apply number format
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');
                    }
                }
            }
        ];
    }
}
