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
        $buyer = $this->buyer;

        if ($buyer != "") {
            $having_buyer = "HAVING a.buyer = '$buyer'";
            $cond_buyer = "and ms.supplier = '$buyer'";
            $cond_na = "";
        } else {
            $having_buyer = "";
            $cond_buyer = "";
            $cond_na = "UNION
SELECT
'N/A' AS buyer,
ROUND(0,2) AS tot_target,
ROUND(0,2) AS tot_output,
ROUND(0,2) AS sum_mins_avail,
ROUND(0,2) AS sum_mins_prod,
ROUND(0,2) AS eff,
ROUND(0,2) AS earn_prod,
ROUND(COALESCE(s.total_cost, 0),2) AS est_tot_cost,
ROUND(0 - COALESCE(s.total_cost, 0),2) as blc,
CASE
    WHEN 0 = 0 THEN 0.00
    ELSE round(((0 - COALESCE(s.total_cost, 0)) / 0) * 100,2)
END AS percent_earning,


ROUND(0,2) sum_est_full_earning,
ROUND(0 - COALESCE(s.total_cost, 0),2) as blc_full_earn_cost_prod,
CASE
    WHEN 0 = 0 THEN 0.00
    ELSE round(((0 - COALESCE(s.total_cost, 0)) / 0) * 100,2)
END  AS percent_full_earning_cost,


ROUND(0,2) AS sum_est_earning_prod,
ROUND(0,2) AS sum_est_cost_prod,
ROUND((0 - 0),2) as blc_earn_cost_prod,
CASE
    WHEN 0 = 0 THEN 0.00
    ELSE round(((0 - COALESCE(s.total_cost, 0)) / 0) * 100,2)
END  as percent_earn_cost_prod,

ROUND(0,2)  AS sum_est_earning_mkt,
ROUND(0,2)  AS sum_est_cost_mkt,
ROUND(0,2)  as blc_earn_cost_mkt,
CASE
    WHEN 0 = 0 THEN 0.00
    ELSE round(((0 - COALESCE(s.total_cost, 0)) / 0) * 100,2)
END   as percent_earn_cost_mkt
FROM sum_full_earn_wo_buyer s";
        }



        $rawData = DB::connection('mysql_sb')->select("WITH sum_cost as (
 select a.cost_no,kpno,supplier,styleno,product_item,season_desc,curr,so_date,status,qty_so,price_so,cost_date,status_cost,qty_cost,COALESCE(ttl_fabric,0) ttl_fabric,COALESCE(ttl_accsew,0) ttl_accsew,COALESCE(ttl_accpack,0) ttl_accpack,(COALESCE(ttl_fabric,0) + COALESCE(ttl_accsew,0) + COALESCE(ttl_accpack,0)) ttl_material,COALESCE(ttl_cmt,0) ttl_cmt,COALESCE(ttl_embro,0) ttl_embro,COALESCE(ttl_wash,0) ttl_wash,COALESCE(ttl_print,0) ttl_print,COALESCE(ttl_wrapbut,0) ttl_wrapbut,COALESCE(ttl_compbut,0) ttl_compbut,COALESCE(ttl_label,0) ttl_label,COALESCE(ttl_laser,0) ttl_laser,(COALESCE(ttl_cmt,0) + COALESCE(ttl_embro,0) + COALESCE(ttl_wash,0) + COALESCE(ttl_print,0) + COALESCE(ttl_wrapbut,0) + COALESCE(ttl_compbut,0) + COALESCE(ttl_label,0) + COALESCE(ttl_laser,0)) ttl_manufacturing,COALESCE(ttl_develop,0) ttl_develop,COALESCE(ttl_overhead,0) ttl_overhead,COALESCE(ttl_market,0) ttl_market,COALESCE(ttl_shipp,0) ttl_shipp,COALESCE(ttl_import,0) ttl_import,COALESCE(ttl_handl,0) ttl_handl,COALESCE(ttl_test,0) ttl_test,COALESCE(ttl_fabhandl,0) ttl_fabhandl,COALESCE(ttl_service,0) ttl_service, COALESCE(ttl_clearcost,0) ttl_clearcost ,COALESCE(ttl_development,0) ttl_development ,COALESCE(ttl_unexcost,0) ttl_unexcost ,COALESCE(ttl_managementfee,0) ttl_managementfee ,COALESCE(ttl_profit,0) ttl_profit ,(COALESCE(ttl_develop,0) + COALESCE(ttl_overhead,0) + COALESCE(ttl_market,0) + COALESCE(ttl_shipp,0) + COALESCE(ttl_import,0) + COALESCE(ttl_handl,0) + COALESCE(ttl_test,0) + COALESCE(ttl_fabhandl,0) + COALESCE(ttl_service,0) + COALESCE(ttl_clearcost,0) + COALESCE(ttl_development,0) + COALESCE(ttl_unexcost,0) + COALESCE(ttl_managementfee,0) + COALESCE(ttl_profit,0)) ttl_others
           from (select a.cost_no,a.kpno,b.supplier,styleno,product_item,season_desc,if(so.curr is null,a.curr,so.curr) curr,so_date,IF(so.cancel_h = 'Y','CANCEL','-') status,so.qty qty_so,so.fob price_so,cost_date,a.status status_cost, a.qty qty_cost  from act_costing a INNER JOIN mastersupplier b ON a.id_buyer=b.Id_Supplier inner join masterproduct mp on a.id_product=mp.id left join so on so.id_cost = a.id left join masterseason ms on ms.id_season = so.id_season where cost_date >= '2025-01-01' GROUP BY cost_no) a left join (select cost_no, sum(ttl_fabric) ttl_fabric, sum(ttl_accsew) ttl_accsew, sum(ttl_accpack) ttl_accpack from (select cost_no,case when mattype = 'FABRIC' then total end as ttl_fabric,
           case when mattype = 'ACCESORIES SEWING' then total end as ttl_accsew,
           case when mattype = 'ACCESORIES PACKING' then total end as ttl_accpack from (SELECT cost_no,mattype,IF(curr = 'IDR',val_idr,val_usd) total from act_material where cost_date >= '2025-01-01') a) a GROUP BY cost_no) b on b.cost_no = a.cost_no left join (select cost_no, sum(ttl_cmt) ttl_cmt, sum(ttl_embro) ttl_embro, sum(ttl_wash) ttl_wash, sum(ttl_print) ttl_print, sum(ttl_wrapbut) ttl_wrapbut, sum(ttl_compbut) ttl_compbut, sum(ttl_label) ttl_label, sum(ttl_laser) ttl_laser from (select cost_no,case when mattype = 'CMT' then total end as ttl_cmt,
           case when mattype = 'EMBRODEIRY' then total end as ttl_embro,
           case when mattype = 'WASHING' then total end as ttl_wash,
           case when mattype = 'PRINTING' then total end as ttl_print,
           case when mattype = 'WRAPPED BUTTON' then total end as ttl_wrapbut,
           case when mattype = 'COMPLEXITY MAKLOON BUTTON' then total end as ttl_compbut,
           case when mattype = 'LABEL PRINT' then total end as ttl_label,
           case when mattype = 'LASER CUTTING' then total end as ttl_laser from (SELECT cost_no,mattype,IF(curr = 'IDR',val_idr,val_usd) total from act_manufacturing where cost_date >='2025-01-01') a) a GROUP BY cost_no) c on c.cost_no = a.cost_no left join (select cost_no, sum(ttl_develop) ttl_develop, sum(ttl_overhead) ttl_overhead, sum(ttl_market) ttl_market, sum(ttl_shipp) ttl_shipp, sum(ttl_import) ttl_import, sum(ttl_handl) ttl_handl, sum(ttl_test) ttl_test, sum(ttl_fabhandl) ttl_fabhandl, sum(ttl_service) ttl_service, sum(ttl_clearcost) ttl_clearcost , sum(ttl_development) ttl_development, sum(ttl_unexcost) ttl_unexcost, sum(ttl_managementfee) ttl_managementfee, sum(ttl_profit) ttl_profit from (select cost_no,case when mattype = 'DEVELOPMENT' then total end as ttl_develop,
           case when mattype = 'OVERHEAD' then total end as ttl_overhead,
           case when mattype = 'MARKETING' then total end as ttl_market,
           case when mattype = 'SHIPPING' then total end as ttl_shipp,
           case when mattype = 'IMPORT COST' then total end as ttl_import,
           case when mattype = 'HANDLING' then total end as ttl_handl,
           case when mattype = 'TESTING' then total end as ttl_test,
           case when mattype = 'FABRIC HANDLING' then total end as ttl_fabhandl,
           case when mattype = 'SERVICE CHARGE' then total end as ttl_service,
           case when mattype = 'CLEARANCE  COST' then total end as ttl_clearcost,
           case when mattype = 'DEVELOPMENT' then '0' end as ttl_development,
           case when mattype = 'UNEXPECTED COST' then total end as ttl_unexcost,
           case when mattype = 'MANAGEMENT FEE' then total end as ttl_managementfee,
           case when mattype = 'PROFIT' then total end as ttl_profit
            from (SELECT cost_no,mattype,IF(curr = 'IDR',val_idr,val_usd) total from act_others where cost_date >= '2025-01-01') a) a GROUP BY cost_no) d on d.cost_no = a.cost_no

 ),
earn as (
 SELECT
                    a.tgl_trans,
                    concat((DATE_FORMAT(a.tgl_trans,  '%d')), '-',left(DATE_FORMAT(a.tgl_trans,  '%M'),3),'-',DATE_FORMAT(a.tgl_trans,  '%Y')) tgl_trans_fix,
                    concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
										a.master_plan_id,
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
                    CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate_jual, acm.price)
                    ELSE acm.price end AS cm_price,
										acm.allowance,
                    round(
                    sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate_jual, acm.price)
                    ELSE acm.price end,2) AS earning,
                    mkb.kurs_tengah,
                    round(
                    if (so.curr = 'IDR',
                    sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate_jual, acm.price)
                    ELSE acm.price end,
                    sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * konv_sb.rate_jual, acm.price)
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
                    where updated_at >= '$this->start_date 00:00:00' and updated_at <= '$this->end_date 23:59:59'
                    group by master_plan_id, created_by, date(updated_at)
                ) a
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
                    where updated_at >= '$this->start_date 00:00:00' and updated_at <= '$this->end_date 23:59:59' group by created_by, date(updated_at)
                ) op on a.tgl_trans = op.tgl_trans_line and a.created_by = op.created_by
                left join (
                    select * from act_costing_mfg where id_item = '8' group by id_act_cost
                ) acm on ac.id = acm.id_act_cost
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal
                ) konv_sb on ac.deldate = konv_sb.tanggal
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
                        where updated_at >= '$this->start_date 00:00:00' and updated_at <= '$this->end_date 23:59:59' and status = 'NORMAL'
                        group by master_plan_id, created_by, date(updated_at)
                    ) a
                    inner join master_plan mp on a.master_plan_id = mp.id
                    group by tgl_trans_rfts, master_plan_id
                ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
                left join
                (
                    select min(id), man_power, sewing_line, tgl_plan from master_plan
                    where tgl_plan >= '$this->start_date' and  tgl_plan <= '$this->end_date' and cancel = 'N'
                    group by sewing_line, tgl_plan
                ) cmp on a.tgl_trans = cmp.tgl_plan and u.username = cmp.sewing_line

                -- Kurs join for pre-MySQL 8
                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.kurs_tengah
                    FROM (
                        SELECT a_dates.tgl_trans, MAX(mkb.tanggal_kurs_bi) AS max_kurs_date
                        FROM (
                            SELECT DISTINCT date(updated_at) AS tgl_trans
                            FROM output_rfts
                            WHERE updated_at >= '$this->start_date 00:00:00' AND updated_at <= '$this->end_date 23:59:59'
                        ) a_dates
                        JOIN master_kurs_bi mkb
                        ON mkb.tanggal_kurs_bi <= a_dates.tgl_trans
                        GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN master_kurs_bi k
                    ON k.tanggal_kurs_bi = x.max_kurs_date
                ) mkb ON a.tgl_trans = mkb.tgl_trans
				where u.name != 'line sample prod'
                $cond_buyer
                group by u.name, ac.kpno, ac.Styleno, a.tgl_trans
                order by a.tgl_trans asc, u.name asc, ac.kpno asc
),
dd as (
SELECT
a.bulan,
a.nama_bulan,
CAST(a.tahun AS UNSIGNED) AS tahun,
COUNT(tanggal) AS tot_working_days
FROM dim_date a
LEFT JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
WHERE status_prod = 'KERJA'
AND (status_absen != 'LN' OR status_absen IS NULL)
AND a.bulan >= '$bulan_awal' and a.tahun >= '$tahun_awal' and a.bulan <= '$bulan_akhir' and a.tahun <= '$tahun_akhir'
GROUP BY bulan, tahun
ORDER BY
CAST(a.tahun AS UNSIGNED) ASC,
CAST(a.bulan AS UNSIGNED) ASC
),
dim_tgl as (
SELECT
tanggal,
bulan,
tahun,
case
		when status_prod = 'KERJA' AND status_absen = 'LP' THEN 'KERJA'
		when status_prod = 'KERJA' AND status_absen = 'LN' THEN 'KERJA'
		when status_prod = 'KERJA' AND status_absen is null THEN 'KERJA'
		when status_prod = 'LIBUR' AND status_absen = 'LP' THEN 'LIBUR'
		when status_prod = 'LIBUR' AND status_absen = 'LN' THEN 'LIBUR'
		when status_prod = 'LIBUR' AND status_absen is null THEN 'LIBUR'

		END AS stat_kerja
FROM dim_date a
left join mgt_rep_hari_libur b on a.tanggal = b.tanggal_libur
where tanggal >= '$this->start_date' and tanggal <= '$this->end_date'
),
dc as (
SELECT
no_coa,
dd.bulan,
nama_bulan,
dd.tahun,
projection,
round(sum(projection / tot_working_days),2) AS daily_cost
FROM mgt_rep_daily_cost a
LEFT JOIN dd ON a.bulan = dd.bulan AND a.tahun = dd.tahun
WHERE a.bulan >= '$bulan_awal' and a.tahun >= '$tahun_awal' and a.bulan <= '$bulan_akhir' and a.tahun <= '$tahun_akhir'
GROUP BY no_coa, dd.bulan, dd.tahun
),
coa_direct as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
coalesce(daily_cost,0)AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'DIRECT LABOR COST'
),
coa_indirect as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
coalesce(daily_cost,0)AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'INDIRECT LABOR COST'
),
coa_overhead as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
coalesce(daily_cost,0)AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'FIXED OVERHEAD COST'
),
coa_selling as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
coalesce(daily_cost,0)AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'SELLING EXPENSE'
),
coa_ga as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
coalesce(daily_cost,0)AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'GENERAL & ADMINISTRATION EXPENSE'
),
coa_expense as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
coalesce(daily_cost,0)AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori3 = 'OTHER EXPENSE'
),
map_coa as (
select no_coa, nama_coa, no_cc, cc_name, group2, id_pc from (select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' support_gen_adm from b_master_cc where group2 = 'SUPPORTING GENERAL & ADMINISTRATION' and status = 'Active') b on b.support_gen_adm = a. support_gen_adm
                                UNION
                                select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' support_prod from b_master_cc where group2 = 'SUPPORTING PRODUCTION' and status = 'Active') b on b.support_prod = a. support_prod
                                UNION
                                select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' prod from b_master_cc where group2 = 'PRODUCTION' and status = 'Active') b on b.prod = a.prod
                                UNION
                                select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' support_sell from b_master_cc where group2 = 'SUPPORTING SELLING' and status = 'Active') b on b.support_sell = a.support_sell)a where id_pc != 'NAK' GROUP BY no_coa, no_cc, id_pc
                                ORDER BY no_coa asc
),
m_labor as (
select
tanggal_berjalan,
sub_dept_id,
group_department,
sum(bruto) wage,
sum(bpjs_tk) bpjs_tk,
sum(bpjs_ks) bpjs_ks,
sum(thr) thr
from mgt_rep_labor
WHERE tanggal_berjalan BETWEEN '$this->start_date' AND '$this->end_date' AND status_staff = 'NON STAFF' -- dynamic filter
group by sub_dept_id, group_department, tanggal_berjalan
),
daily_cost as (
(
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
CASE
    WHEN COALESCE(a.daily_cost, 0) = 0 THEN 0
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'LIBUR' THEN
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'KERJA' THEN
        COALESCE(a.daily_cost, 0) +
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    ELSE 0
END AS tot_labor,
'direct labor' as nm_labor
from coa_direct a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan and group_department = 'PRODUCTION'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
CASE
    WHEN COALESCE(a.daily_cost, 0) = 0 THEN 0
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'LIBUR' THEN
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'KERJA' THEN
        COALESCE(a.daily_cost, 0) +
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    ELSE 0
END AS tot_labor,
'indirect labor' as nm_labor
from coa_indirect  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan and group_department = 'SUPPORTING PRODUCTION'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
CASE
    WHEN COALESCE(a.daily_cost, 0) = 0 THEN 0
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'LIBUR' THEN
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'KERJA' THEN
        COALESCE(a.daily_cost, 0) +
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    ELSE 0
END AS tot_labor,
'overhead labor' as nm_labor
from coa_overhead  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
CASE
    WHEN COALESCE(a.daily_cost, 0) = 0 THEN 0
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'LIBUR' THEN
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'KERJA' THEN
        COALESCE(a.daily_cost, 0) +
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    ELSE 0
END AS tot_labor,
'selling expense' as nm_labor
from coa_selling  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan  AND c.group_department = 'SUPPORTING SELLING'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
CASE
    WHEN COALESCE(a.daily_cost, 0) = 0 THEN 0
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'LIBUR' THEN
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'KERJA' THEN
        COALESCE(a.daily_cost, 0) +
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    ELSE 0
END AS tot_labor,
'ga expense' as nm_labor
from coa_ga  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan  AND c.group_department = 'SUPPORTING GENERAL & ADMINISTRATION'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
CASE
    WHEN a.nama_coa LIKE '%SGT%'  AND stat_kerja = 'KERJA' THEN COALESCE(a.daily_cost, 0)
    WHEN a.nama_coa LIKE '%SGT%'  AND stat_kerja = 'LIBUR' THEN 0
	WHEN a.nama_coa LIKE '%GS%' THEN 0
	WHEN a.nama_coa LIKE '%SA%' THEN 0
    WHEN COALESCE(a.daily_cost, 0) = 0 THEN 0
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'LIBUR' THEN
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    WHEN COALESCE(a.daily_cost, 0) != 0 AND stat_kerja = 'KERJA' THEN
        COALESCE(a.daily_cost, 0) +
        CASE
            WHEN a.nama_coa LIKE '%GAJI%' THEN COALESCE(SUM(wage), 0)
            WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN COALESCE(SUM(bpjs_tk), 0)
            WHEN a.nama_coa LIKE '%BPJS KESEHATAN%' THEN COALESCE(SUM(bpjs_ks), 0)
            WHEN a.nama_coa LIKE '%THR%' THEN COALESCE(SUM(thr), 0)
            ELSE 0
        END
    ELSE 0
END AS tot_labor,
'other expense' as nm_labor
from coa_expense  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan
GROUP BY no_coa, a.tanggal
)
ORDER BY tanggal asc,
no_coa asc
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

ROUND((COALESCE(sum_tot_labor, 0) / COALESCE(c.sum_mins_avail, 0)) * COALESCE(a.mins_avail, 0), 2) AS est_tot_cost,
ROUND((COALESCE(tot_earning_rupiah, 0) - ((COALESCE(sum_tot_labor, 0) / COALESCE(c.sum_mins_avail, 0)) * COALESCE(a.mins_avail, 0))), 2) AS blc,
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
where dt.tanggal >= '$this->start_date' and dt.tanggal <= '$this->end_date'
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
WHERE tanggal_berjalan BETWEEN '2025-10-01' AND '2025-10-08'
group by tanggal_berjalan
order by tanggal_berjalan asc
),
m_kurs_bi as (
select * from master_kurs_bi where tanggal_kurs_bi BETWEEN '2025-10-01' AND '2025-10-08'
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

from earn a
left join sum_earning_buyer b on a.buyer = b.buyer
group by a.buyer
$having_buyer
$cond_na
        ");


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
