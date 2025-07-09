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


class ReportMutasiOutputController extends Controller
{

    public function index(Request $request)
    {
        $data_buyer = DB::connection('mysql_sb')->select("SELECT supplier isi, supplier tampil
                            from laravel_nds.ppic_master_so a
                            inner join signalbit_erp.so_det sd on a.id_so_det = sd.id
                            inner join signalbit_erp.so on sd.id_so = so.id
                            inner join signalbit_erp.act_costing  ac on so.id_cost = ac.id
                            inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                            group by supplier
                            order by supplier asc
                ");

        return view('sewing.report.report_mutasi_output', ['page' => 'dashboard-sewing-eff', "subPageGroup" => "sewing-report", "subPage" => "report_mut_output", "containerFluid" => true, "data_buyer" => $data_buyer]);
    }


    public function show_mut_output(Request $request)
    {
        ini_set('memory_limit', '4096M');
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $buyer = $request->cbobuyer;

        if (!empty($buyer)) {
            $filter = " where buyer  = '" . $buyer  . "'";
        } else {
            $filter = "";
        }

        if ($request->ajax()) {
            $data_mut = DB::connection('mysql_sb')->select("WITH m as (
SELECT
ms.supplier buyer,
ac.kpno ws,
ac.styleno,
sd.color,
sd.size,
sd.id id_so_det
from signalbit_erp.act_costing ac
inner join signalbit_erp.so on ac.id = so.id_cost
inner join signalbit_erp.so_det sd on so.id = sd.id_so
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
where ac.aktif = 'Y' and so.cancel_h = 'N' and sd.cancel = 'N'
),

saldo_loading as (
SELECT
	id_so_det,
	SUM(CASE
		WHEN tanggal_loading >= '2025-07-01' and tanggal_loading < '$tgl_awal' THEN qty_loading
		ELSE 0
	END) as qty_loading_awal,
	SUM(CASE
		WHEN tanggal_loading >= '$tgl_awal' and tanggal_loading <= '$tgl_akhir' THEN qty_loading
		ELSE 0
	END) as qty_loading
FROM (
	SELECT
		b.so_det_id AS id_so_det,
		a.tanggal_loading,
		MIN( qty ) AS qty_loading
	FROM
		laravel_nds.loading_line a
		INNER JOIN laravel_nds.stocker_input b ON a.stocker_id = b.id
	WHERE
		b.form_cut_id > 0
	GROUP BY
		b.so_det_id,
		b.form_cut_id,
		b.group_stocker,
		b.ratio,
		a.tanggal_loading
	UNION ALL
	SELECT
		so_det_id AS id_so_det,
		tanggal_loading,
		MIN( qty ) AS qty_loading
	FROM
		laravel_nds.loading_line
		LEFT JOIN laravel_nds.stocker_input ON stocker_input.id = loading_line.stocker_id
	WHERE
		form_reject_id IS NOT NULL
	GROUP BY
		so_det_id,
		form_reject_id,
		tanggal_loading
) loading
GROUP BY
	id_so_det
),

saldo_sewing_awal as (
select
so_det_id,
COUNT(so_det_id) qty_sew_awal
from signalbit_erp.output_rfts a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
group by so_det_id
),
saldo_sewing_akhir as (
select
so_det_id,
COUNT(so_det_id) qty_sew
from signalbit_erp.output_rfts a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
group by so_det_id
),

saldo_sewing_defect_awal as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending
FROM signalbit_erp.output_defects a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '2025-07-01 00:00:00' and a.updated_at < '$tgl_awal 00:00:00'
GROUP BY
                so_det_id
),
saldo_sewing_defect_akhir as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending
FROM signalbit_erp.output_defects a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal 00:00:00' and a.updated_at <= '$tgl_akhir 23:59:59'
GROUP BY
                so_det_id
),
saldo_sewing_reject_awal as (
select
so_det_id,
COUNT(so_det_id) qty_sew_reject_awal
from signalbit_erp.output_rejects a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
group by so_det_id
),
saldo_sewing_reject_akhir as (
select
so_det_id,
COUNT(so_det_id) qty_sew_reject
from signalbit_erp.output_rejects a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
group by so_det_id
),

saldo_packing_awal as (
select
so_det_id,
COUNT(so_det_id) qty_pck_awal
from signalbit_erp.output_rfts_packing a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
group by so_det_id
),
saldo_packing_akhir as (
select
so_det_id,
COUNT(so_det_id) qty_pck_akhir
from signalbit_erp.output_rfts_packing a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
group by so_det_id
),

saldo_packing_defect_awal as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_pck_mending
FROM signalbit_erp.output_defects_packing a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '2025-07-01 00:00:00' and a.updated_at < '$tgl_awal 00:00:00'
GROUP BY
                so_det_id
),
saldo_packing_defect_akhir as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_pck_mending
FROM signalbit_erp.output_defects_packing a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal 00:00:00' and a.updated_at <= '$tgl_akhir 23:59:59'
GROUP BY
                so_det_id
),
saldo_adj_awal as (
select
id_so_det,
sum(sa_sewing) sa_adj_awal,
sum(sa_steam) sa_steam_adj_awal,
sum(sa_def_sewing) sa_def_adj_sewing_awal,
sum(sa_def_spotcleaning) sa_def_adj_spotcleaning_awal,
sum(sa_def_mending) sa_def_adj_mending_awal,
sum(sa_def_pck_sewing) sa_def_pck_adj_sewing_awal,
sum(sa_def_pck_spotcleaning) sa_def_pck_adj_spotcleaning_awal,
sum(sa_def_pck_mending) sa_def_pck_adj_mending_awal
from laravel_nds.report_output_adj where tgl_adj >= '2025-07-01 00:00:00' and tgl_adj < '$tgl_awal 00:00:00'
group by id_so_det
),
saldo_adj_akhir as (
select
id_so_det,
sum(sa_sewing) sa_adj_akhir,
sum(sa_steam) sa_steam_adj_akhir,
sum(sa_def_sewing) sa_def_adj_sewing_akhir,
sum(sa_def_spotcleaning) sa_def_adj_spotcleaning_akhir,
sum(sa_def_mending) sa_def_adj_mending_akhir,
sum(sa_def_pck_sewing) sa_def_pck_adj_sewing_akhir,
sum(sa_def_pck_spotcleaning) sa_def_pck_adj_spotcleaning_akhir,
sum(sa_def_pck_mending) sa_def_pck_adj_mending_akhir
from laravel_nds.report_output_adj where tgl_adj >= '$tgl_awal 00:00:00' and tgl_adj <= '$tgl_akhir 23:59:59'
group by id_so_det
),
saldo_awal_upload as
(
select
so_det_id,
sum(loading) loading,
sum(sewing) sewing,
sum(steam) steam,
sum(out_sew) out_sew,
sum(packing_line) packing_line,
sum(trf_gmt) trf_gmt,
sum(packing_central) packing_central
from laravel_nds.sa_report_output
where tgl_saldo = '2025-07-01'
group by so_det_id
)

SELECT
buyer,
ws,
color,
m.size,
styleno,
group_concat(m.id_so_det) id_so_det,
sum(coalesce(a.qty_loading_awal,0)) + sum(coalesce(a.qty_loading,0)) qty_loading_awal,
sum(coalesce(a.qty_loading,0)) qty_loading,

sum(coalesce(b.qty_sew_awal,0)) qty_sew_awal_before_adj,
sum(coalesce(sawal.sa_adj_awal,0)) qty_sew_adj_awal,
sum(coalesce(b.qty_sew_awal,0)) +  sum(coalesce(sawal.sa_adj_awal,0)) qty_sew_awal_after_adj,
sum(coalesce(c.qty_sew,0)) qty_sew,
sum(coalesce(sakhir.sa_adj_akhir,0)) qty_sew_adj,

sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) defect_sewing_awal,
sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) defect_spotcleaning_awal,
sum(coalesce(saldo_sewing_defect_awal.defect_mending,0)) defect_mending_awal,

sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) input_rework_sewing_awal,
sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) input_rework_spotcleaning_awal,
sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0)) input_rework_mending_awal,

sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) defect_sewing_akhir,
sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) defect_spotcleaning_akhir,
sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0)) defect_mending_akhir,

sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) input_rework_sewing,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) input_rework_spotcleaning,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0)) input_rework_mending,

sum(coalesce(d.qty_sew_reject_awal,0)) qty_sew_reject_awal,
sum(coalesce(e.qty_sew_reject,0)) qty_sew_reject,

/* saldo awal */


coalesce(

sum(coalesce(sal.sewing,0)) +

sum(coalesce(a.qty_loading_awal,0)) -
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_mending,0))
+
sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0))
-
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(d.qty_sew_reject_awal,0)) + sum(coalesce(sawal.sa_adj_awal,0)),0)  as saldo_awal_sewing,


(
coalesce(

sum(coalesce(sal.sewing,0)) +

sum(coalesce(a.qty_loading_awal,0)) -
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_mending,0))
+
sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0))
-
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(d.qty_sew_reject_awal,0)) + sum(coalesce(sawal.sa_adj_awal,0)),0)
)
+
/* saldo akhir */
sum(coalesce(a.qty_loading,0)) -
sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0))
+
sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) + sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) + sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0))
-
sum(coalesce(c.qty_sew,0)) - sum(coalesce(e.qty_sew_reject,0)) + sum(coalesce(sakhir.sa_adj_akhir,0))  as saldo_akhir_sewing,


sum(coalesce(sal.steam,0)) +
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(saldo_packing_awal.qty_pck_awal,0)) +  sum(coalesce(sawal.sa_steam_adj_awal,0)) saldo_awal_steam,
sum(coalesce(c.qty_sew,0)) in_steam,
sum(coalesce(saldo_packing_akhir.qty_pck_akhir,0)) out_steam,
sum(coalesce(sakhir.sa_steam_adj_akhir,0)) adj_steam,

sum(coalesce(sal.steam,0)) +
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(saldo_packing_awal.qty_pck_awal,0)) +  sum(coalesce(sawal.sa_steam_adj_awal,0)) +
sum(coalesce(c.qty_sew,0)) - sum(coalesce(saldo_packing_akhir.qty_pck_akhir,0)) + sum(coalesce(sakhir.sa_steam_adj_akhir,0)) saldo_akhir_steam,

-- saldo defect sewing
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) + sum(coalesce(sawal.sa_def_adj_sewing_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) saldo_awal_def_sewing,
sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) in_def_sewing,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) out_def_sewing,
sum(coalesce(sakhir.sa_def_adj_sewing_akhir,0)) adj_def_sewing,
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) + sum(coalesce(sawal.sa_def_adj_sewing_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) +
sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) + sum(coalesce(sakhir.sa_def_adj_sewing_akhir,0)) saldo_akhir_def_sewing,

sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(sawal.sa_def_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) saldo_awal_def_spotcleaning,
sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) in_def_spotcleaning,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) out_def_spotcleaning,
sum(coalesce(sakhir.sa_def_adj_spotcleaning_akhir,0)) adj_def_spotcleaning,
sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(sawal.sa_def_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) +
sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) + sum(coalesce(sakhir.sa_def_adj_spotcleaning_akhir,0)) saldo_akhir_def_spotcleaning,

sum(coalesce(saldo_sewing_defect_awal.defect_mending,0)) + sum(coalesce(sawal.sa_def_adj_mending_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0)) saldo_awal_def_mending,
sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0)) in_def_mending,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0)) out_def_mending,
sum(coalesce(sakhir.sa_def_adj_mending_akhir,0)) adj_def_mending,
sum(coalesce(saldo_sewing_defect_awal.defect_mending,0)) + sum(coalesce(sawal.sa_def_adj_mending_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0)) +
sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0)) - sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0)) + sum(coalesce(sakhir.sa_def_adj_mending_akhir,0)) saldo_akhir_def_mending,

sum(coalesce(saldo_packing_defect_awal.defect_pck_sewing,0)) + sum(coalesce(sawal.sa_def_pck_adj_sewing_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_sewing,0)) saldo_awal_def_pck_sewing,
sum(coalesce(saldo_packing_defect_akhir.defect_pck_sewing,0)) in_def_pck_sewing,
sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_sewing,0)) out_def_pck_sewing,
sum(coalesce(sakhir.sa_def_pck_adj_sewing_akhir,0)) adj_def_pck_sewing,
sum(coalesce(saldo_packing_defect_awal.defect_pck_sewing,0)) + sum(coalesce(sawal.sa_def_pck_adj_sewing_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_sewing,0)) +
sum(coalesce(saldo_packing_defect_akhir.defect_pck_sewing,0)) - sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_sewing,0)) + sum(coalesce(sakhir.sa_def_pck_adj_sewing_akhir,0)) saldo_akhir_def_pck_sewing,

sum(coalesce(saldo_packing_defect_awal.defect_pck_spotcleaning,0)) + sum(coalesce(sawal.sa_def_pck_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_spotcleaning,0)) saldo_awal_def_pck_spotcleaning,
sum(coalesce(saldo_packing_defect_akhir.defect_pck_spotcleaning,0)) in_def_pck_spotcleaning,
sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_spotcleaning,0)) out_def_pck_spotcleaning,
sum(coalesce(sakhir.sa_def_pck_adj_spotcleaning_akhir,0)) adj_def_pck_spotcleaning,
sum(coalesce(saldo_packing_defect_awal.defect_pck_spotcleaning,0)) + sum(coalesce(sawal.sa_def_pck_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_spotcleaning,0)) +
sum(coalesce(saldo_packing_defect_akhir.defect_pck_spotcleaning,0)) - sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_spotcleaning,0)) + sum(coalesce(sakhir.sa_def_pck_adj_spotcleaning_akhir,0)) saldo_akhir_def_pck_spotcleaning,


sum(coalesce(saldo_packing_defect_awal.defect_pck_mending,0)) + sum(coalesce(sawal.sa_def_pck_adj_mending_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_mending,0)) saldo_awal_def_pck_mending,
sum(coalesce(saldo_packing_defect_akhir.defect_pck_mending,0)) in_def_pck_mending,
sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_mending,0)) out_def_pck_mending,
sum(coalesce(sakhir.sa_def_pck_adj_mending_akhir,0)) adj_def_pck_mending,
sum(coalesce(saldo_packing_defect_awal.defect_pck_mending,0)) + sum(coalesce(sawal.sa_def_pck_adj_mending_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_mending,0)) +
sum(coalesce(saldo_packing_defect_akhir.defect_pck_mending,0)) - sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_mending,0)) + sum(coalesce(sakhir.sa_def_pck_adj_mending_akhir,0)) saldo_akhir_def_pck_mending

from m
left join saldo_loading a on m.id_so_det = a.id_so_det
left join saldo_sewing_awal b on m.id_so_det = b.so_det_id
left join saldo_sewing_akhir c on m.id_so_det = c.so_det_id
left join saldo_adj_awal sawal on m.id_so_det = sawal.id_so_det
left join saldo_adj_akhir sakhir on m.id_so_det = sakhir.id_so_det
left join saldo_sewing_defect_awal on m.id_so_det = saldo_sewing_defect_awal.so_det_id
left join saldo_sewing_defect_akhir on m.id_so_det = saldo_sewing_defect_akhir.so_det_id
left join saldo_sewing_reject_awal d on m.id_so_det = d.so_det_id
left join saldo_sewing_reject_akhir e on m.id_so_det = e.so_det_id
left join saldo_packing_awal on m.id_so_det = saldo_packing_awal.so_det_id
left join saldo_packing_akhir on m.id_so_det = saldo_packing_akhir.so_det_id
left join saldo_packing_defect_awal on m.id_so_det = saldo_packing_defect_awal.so_det_id
left join saldo_packing_defect_akhir on m.id_so_det = saldo_packing_defect_akhir.so_det_id
left join saldo_awal_upload sal on m.id_so_det = sal.so_det_id
left join laravel_nds.master_size_new msn on m.size = msn.size
$filter
group by
buyer,
ws,
color,
m.size
HAVING
(
    qty_loading_awal != 0 OR
    qty_loading != 0 OR
    defect_sewing_akhir != 0 OR
    defect_spotcleaning_akhir != 0 OR
    defect_mending_akhir != 0 OR
    input_rework_sewing != 0 OR
    input_rework_spotcleaning != 0 OR
    input_rework_mending != 0 OR
    qty_sew_reject != 0 OR
    qty_sew != 0 OR
    qty_sew_adj != 0 OR
    saldo_akhir_sewing != 0 OR
    saldo_awal_steam != 0 OR
    in_steam != 0 OR
    out_steam != 0 OR
    adj_steam != 0 OR
    saldo_akhir_steam != 0 OR
    saldo_awal_def_sewing != 0 OR
    in_def_sewing != 0 OR
    out_def_sewing != 0 OR
    adj_def_sewing != 0 OR
    saldo_akhir_def_sewing != 0 OR
    saldo_awal_def_spotcleaning != 0 OR
    in_def_spotcleaning != 0 OR
    out_def_spotcleaning != 0 OR
    adj_def_spotcleaning != 0 OR
    saldo_akhir_def_spotcleaning != 0 OR
    saldo_awal_def_mending != 0 OR
    in_def_mending != 0 OR
    out_def_mending != 0 OR
    adj_def_mending != 0 OR
    saldo_akhir_def_mending != 0 OR
    saldo_awal_def_pck_sewing != 0 OR
    in_def_pck_sewing != 0 OR
    out_def_pck_sewing != 0 OR
    adj_def_pck_sewing != 0 OR
    saldo_akhir_def_pck_sewing != 0 OR
    saldo_awal_def_pck_spotcleaning != 0 OR
    in_def_pck_spotcleaning != 0 OR
    out_def_pck_spotcleaning != 0 OR
    adj_def_pck_spotcleaning != 0 OR
    saldo_akhir_def_pck_spotcleaning != 0 OR
    saldo_awal_def_pck_mending != 0 OR
    in_def_pck_mending != 0 OR
    out_def_pck_mending != 0 OR
    adj_def_pck_mending != 0 OR
    saldo_akhir_def_pck_mending != 0
)
ORDER BY
buyer asc,
ws asc,
color asc,
msn.urutan asc
      ");

            return DataTables::of($data_mut)->toJson();
        }
    }

    public function export_excel_mut_output(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $buyer = $request->cbobuyer;

        if (!empty($buyer)) {
            $filter = " where buyer  = '" . $buyer  . "'";
        } else {
            $filter = "";
        }

        $data = DB::connection('mysql_sb')->select("WITH m as (
SELECT
ms.supplier buyer,
ac.kpno ws,
ac.styleno,
sd.color,
sd.size,
sd.id id_so_det
from signalbit_erp.act_costing ac
inner join signalbit_erp.so on ac.id = so.id_cost
inner join signalbit_erp.so_det sd on so.id = sd.id_so
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
where ac.aktif = 'Y' and so.cancel_h = 'N' and sd.cancel = 'N'
),

saldo_loading as (
SELECT
	id_so_det,
	SUM(CASE
		WHEN tanggal_loading >= '2025-07-01' and tanggal_loading < '$tgl_awal' THEN qty_loading
		ELSE 0
	END) as qty_loading_awal,
	SUM(CASE
		WHEN tanggal_loading >= '$tgl_awal' and tanggal_loading <= '$tgl_akhir' THEN qty_loading
		ELSE 0
	END) as qty_loading
FROM (
	SELECT
		b.so_det_id AS id_so_det,
		a.tanggal_loading,
		MIN( qty ) AS qty_loading
	FROM
		laravel_nds.loading_line a
		INNER JOIN laravel_nds.stocker_input b ON a.stocker_id = b.id
	WHERE
		b.form_cut_id > 0
	GROUP BY
		b.so_det_id,
		b.form_cut_id,
		b.group_stocker,
		b.ratio,
		a.tanggal_loading
	UNION ALL
	SELECT
		so_det_id AS id_so_det,
		tanggal_loading,
		MIN( qty ) AS qty_loading
	FROM
		laravel_nds.loading_line
		LEFT JOIN laravel_nds.stocker_input ON stocker_input.id = loading_line.stocker_id
	WHERE
		form_reject_id IS NOT NULL
	GROUP BY
		so_det_id,
		form_reject_id,
		tanggal_loading
) loading
GROUP BY
	id_so_det
),

saldo_sewing_awal as (
select
so_det_id,
COUNT(so_det_id) qty_sew_awal
from signalbit_erp.output_rfts a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
group by so_det_id
),
saldo_sewing_akhir as (
select
so_det_id,
COUNT(so_det_id) qty_sew
from signalbit_erp.output_rfts a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
group by so_det_id
),

saldo_sewing_defect_awal as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending
FROM signalbit_erp.output_defects a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '2025-07-01 00:00:00' and a.updated_at < '$tgl_awal 00:00:00'
GROUP BY
                so_det_id
),
saldo_sewing_defect_akhir as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending
FROM signalbit_erp.output_defects a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal 00:00:00' and a.updated_at <= '$tgl_akhir 23:59:59'
GROUP BY
                so_det_id
),
saldo_sewing_reject_awal as (
select
so_det_id,
COUNT(so_det_id) qty_sew_reject_awal
from signalbit_erp.output_rejects a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
group by so_det_id
),
saldo_sewing_reject_akhir as (
select
so_det_id,
COUNT(so_det_id) qty_sew_reject
from signalbit_erp.output_rejects a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
group by so_det_id
),

saldo_packing_awal as (
select
so_det_id,
COUNT(so_det_id) qty_pck_awal
from signalbit_erp.output_rfts_packing a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
group by so_det_id
),
saldo_packing_akhir as (
select
so_det_id,
COUNT(so_det_id) qty_pck_akhir
from signalbit_erp.output_rfts_packing a
inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
group by so_det_id
),

saldo_packing_defect_awal as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_pck_mending
FROM signalbit_erp.output_defects_packing a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '2025-07-01 00:00:00' and a.updated_at < '$tgl_awal 00:00:00'
GROUP BY
                so_det_id
),
saldo_packing_defect_akhir as(
select
						so_det_id,
						    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_pck_mending,
                SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_pck_sewing,
                SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_pck_spotcleaning,
                SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_pck_mending
FROM signalbit_erp.output_defects_packing a
INNER JOIN
                signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
                allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal 00:00:00' and a.updated_at <= '$tgl_akhir 23:59:59'
GROUP BY
                so_det_id
),
saldo_adj_awal as (
select
id_so_det,
sum(sa_sewing) sa_adj_awal,
sum(sa_steam) sa_steam_adj_awal,
sum(sa_def_sewing) sa_def_adj_sewing_awal,
sum(sa_def_spotcleaning) sa_def_adj_spotcleaning_awal,
sum(sa_def_mending) sa_def_adj_mending_awal,
sum(sa_def_pck_sewing) sa_def_pck_adj_sewing_awal,
sum(sa_def_pck_spotcleaning) sa_def_pck_adj_spotcleaning_awal,
sum(sa_def_pck_mending) sa_def_pck_adj_mending_awal
from laravel_nds.report_output_adj where tgl_adj >= '2025-07-01 00:00:00' and tgl_adj < '$tgl_awal 00:00:00'
group by id_so_det
),
saldo_adj_akhir as (
select
id_so_det,
sum(sa_sewing) sa_adj_akhir,
sum(sa_steam) sa_steam_adj_akhir,
sum(sa_def_sewing) sa_def_adj_sewing_akhir,
sum(sa_def_spotcleaning) sa_def_adj_spotcleaning_akhir,
sum(sa_def_mending) sa_def_adj_mending_akhir,
sum(sa_def_pck_sewing) sa_def_pck_adj_sewing_akhir,
sum(sa_def_pck_spotcleaning) sa_def_pck_adj_spotcleaning_akhir,
sum(sa_def_pck_mending) sa_def_pck_adj_mending_akhir
from laravel_nds.report_output_adj where tgl_adj >= '$tgl_awal 00:00:00' and tgl_adj <= '$tgl_akhir 23:59:59'
group by id_so_det
),
saldo_awal_upload as
(
select
so_det_id,
sum(loading) loading,
sum(sewing) sewing,
sum(steam) steam,
sum(out_sew) out_sew,
sum(packing_line) packing_line,
sum(trf_gmt) trf_gmt,
sum(packing_central) packing_central
from laravel_nds.sa_report_output
where tgl_saldo = '2025-07-01'
group by so_det_id
)

SELECT
buyer,
ws,
color,
m.size,
styleno,
group_concat(m.id_so_det) id_so_det,
sum(coalesce(a.qty_loading_awal,0)) + sum(coalesce(a.qty_loading,0)) qty_loading_awal,
sum(coalesce(a.qty_loading,0)) qty_loading,

sum(coalesce(b.qty_sew_awal,0)) qty_sew_awal_before_adj,
sum(coalesce(sawal.sa_adj_awal,0)) qty_sew_adj_awal,
sum(coalesce(b.qty_sew_awal,0)) +  sum(coalesce(sawal.sa_adj_awal,0)) qty_sew_awal_after_adj,
sum(coalesce(c.qty_sew,0)) qty_sew,
sum(coalesce(sakhir.sa_adj_akhir,0)) qty_sew_adj,

sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) defect_sewing_awal,
sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) defect_spotcleaning_awal,
sum(coalesce(saldo_sewing_defect_awal.defect_mending,0)) defect_mending_awal,

sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) input_rework_sewing_awal,
sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) input_rework_spotcleaning_awal,
sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0)) input_rework_mending_awal,

sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) defect_sewing_akhir,
sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) defect_spotcleaning_akhir,
sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0)) defect_mending_akhir,

sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) input_rework_sewing,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) input_rework_spotcleaning,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0)) input_rework_mending,

sum(coalesce(d.qty_sew_reject_awal,0)) qty_sew_reject_awal,
sum(coalesce(e.qty_sew_reject,0)) qty_sew_reject,

/* saldo awal */


coalesce(

sum(coalesce(sal.sewing,0)) +

sum(coalesce(a.qty_loading_awal,0)) -
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_mending,0))
+
sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0))
-
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(d.qty_sew_reject_awal,0)) + sum(coalesce(sawal.sa_adj_awal,0)),0)  as saldo_awal_sewing,


(
coalesce(

sum(coalesce(sal.sewing,0)) +

sum(coalesce(a.qty_loading_awal,0)) -
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_awal.defect_mending,0))
+
sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0))
-
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(d.qty_sew_reject_awal,0)) + sum(coalesce(sawal.sa_adj_awal,0)),0)
)
+
/* saldo akhir */
sum(coalesce(a.qty_loading,0)) -
sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0))
+
sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) + sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) + sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0))
-
sum(coalesce(c.qty_sew,0)) - sum(coalesce(e.qty_sew_reject,0)) + sum(coalesce(sakhir.sa_adj_akhir,0))  as saldo_akhir_sewing,


sum(coalesce(sal.steam,0)) +
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(saldo_packing_awal.qty_pck_awal,0)) +  sum(coalesce(sawal.sa_steam_adj_awal,0)) saldo_awal_steam,
sum(coalesce(c.qty_sew,0)) in_steam,
sum(coalesce(saldo_packing_akhir.qty_pck_akhir,0)) out_steam,
sum(coalesce(sakhir.sa_steam_adj_akhir,0)) adj_steam,

sum(coalesce(sal.steam,0)) +
sum(coalesce(b.qty_sew_awal,0)) - sum(coalesce(saldo_packing_awal.qty_pck_awal,0)) +  sum(coalesce(sawal.sa_steam_adj_awal,0)) +
sum(coalesce(c.qty_sew,0)) - sum(coalesce(saldo_packing_akhir.qty_pck_akhir,0)) + sum(coalesce(sakhir.sa_steam_adj_akhir,0)) saldo_akhir_steam,

-- saldo defect sewing
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) + sum(coalesce(sawal.sa_def_adj_sewing_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) saldo_awal_def_sewing,
sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) in_def_sewing,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) out_def_sewing,
sum(coalesce(sakhir.sa_def_adj_sewing_akhir,0)) adj_def_sewing,
sum(coalesce(saldo_sewing_defect_awal.defect_sewing,0)) + sum(coalesce(sawal.sa_def_adj_sewing_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_sewing,0)) +
sum(coalesce(saldo_sewing_defect_akhir.defect_sewing,0)) - sum(coalesce(saldo_sewing_defect_akhir.input_rework_sewing,0)) + sum(coalesce(sakhir.sa_def_adj_sewing_akhir,0)) saldo_akhir_def_sewing,

sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(sawal.sa_def_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) saldo_awal_def_spotcleaning,
sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) in_def_spotcleaning,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) out_def_spotcleaning,
sum(coalesce(sakhir.sa_def_adj_spotcleaning_akhir,0)) adj_def_spotcleaning,
sum(coalesce(saldo_sewing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(sawal.sa_def_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_spotcleaning,0)) +
sum(coalesce(saldo_sewing_defect_akhir.defect_spotcleaning,0)) - sum(coalesce(saldo_sewing_defect_akhir.input_rework_spotcleaning,0)) + sum(coalesce(sakhir.sa_def_adj_spotcleaning_akhir,0)) saldo_akhir_def_spotcleaning,

sum(coalesce(saldo_sewing_defect_awal.defect_mending,0)) + sum(coalesce(sawal.sa_def_adj_mending_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0)) saldo_awal_def_mending,
sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0)) in_def_mending,
sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0)) out_def_mending,
sum(coalesce(sakhir.sa_def_adj_mending_akhir,0)) adj_def_mending,
sum(coalesce(saldo_sewing_defect_awal.defect_mending,0)) + sum(coalesce(sawal.sa_def_adj_mending_awal,0)) - sum(coalesce(saldo_sewing_defect_awal.input_rework_mending,0)) +
sum(coalesce(saldo_sewing_defect_akhir.defect_mending,0)) - sum(coalesce(saldo_sewing_defect_akhir.input_rework_mending,0)) + sum(coalesce(sakhir.sa_def_adj_mending_akhir,0)) saldo_akhir_def_mending,

sum(coalesce(saldo_packing_defect_awal.defect_pck_sewing,0)) + sum(coalesce(sawal.sa_def_pck_adj_sewing_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_sewing,0)) saldo_awal_def_pck_sewing,
sum(coalesce(saldo_packing_defect_akhir.defect_pck_sewing,0)) in_def_pck_sewing,
sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_sewing,0)) out_def_pck_sewing,
sum(coalesce(sakhir.sa_def_pck_adj_sewing_akhir,0)) adj_def_pck_sewing,
sum(coalesce(saldo_packing_defect_awal.defect_pck_sewing,0)) + sum(coalesce(sawal.sa_def_pck_adj_sewing_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_sewing,0)) +
sum(coalesce(saldo_packing_defect_akhir.defect_pck_sewing,0)) - sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_sewing,0)) + sum(coalesce(sakhir.sa_def_pck_adj_sewing_akhir,0)) saldo_akhir_def_pck_sewing,

sum(coalesce(saldo_packing_defect_awal.defect_pck_spotcleaning,0)) + sum(coalesce(sawal.sa_def_pck_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_spotcleaning,0)) saldo_awal_def_pck_spotcleaning,
sum(coalesce(saldo_packing_defect_akhir.defect_pck_spotcleaning,0)) in_def_pck_spotcleaning,
sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_spotcleaning,0)) out_def_pck_spotcleaning,
sum(coalesce(sakhir.sa_def_pck_adj_spotcleaning_akhir,0)) adj_def_pck_spotcleaning,
sum(coalesce(saldo_packing_defect_awal.defect_pck_spotcleaning,0)) + sum(coalesce(sawal.sa_def_pck_adj_spotcleaning_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_spotcleaning,0)) +
sum(coalesce(saldo_packing_defect_akhir.defect_pck_spotcleaning,0)) - sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_spotcleaning,0)) + sum(coalesce(sakhir.sa_def_pck_adj_spotcleaning_akhir,0)) saldo_akhir_def_pck_spotcleaning,


sum(coalesce(saldo_packing_defect_awal.defect_pck_mending,0)) + sum(coalesce(sawal.sa_def_pck_adj_mending_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_mending,0)) saldo_awal_def_pck_mending,
sum(coalesce(saldo_packing_defect_akhir.defect_pck_mending,0)) in_def_pck_mending,
sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_mending,0)) out_def_pck_mending,
sum(coalesce(sakhir.sa_def_pck_adj_mending_akhir,0)) adj_def_pck_mending,
sum(coalesce(saldo_packing_defect_awal.defect_pck_mending,0)) + sum(coalesce(sawal.sa_def_pck_adj_mending_awal,0)) - sum(coalesce(saldo_packing_defect_awal.input_rework_pck_mending,0)) +
sum(coalesce(saldo_packing_defect_akhir.defect_pck_mending,0)) - sum(coalesce(saldo_packing_defect_akhir.input_rework_pck_mending,0)) + sum(coalesce(sakhir.sa_def_pck_adj_mending_akhir,0)) saldo_akhir_def_pck_mending

from m
left join saldo_loading a on m.id_so_det = a.id_so_det
left join saldo_sewing_awal b on m.id_so_det = b.so_det_id
left join saldo_sewing_akhir c on m.id_so_det = c.so_det_id
left join saldo_adj_awal sawal on m.id_so_det = sawal.id_so_det
left join saldo_adj_akhir sakhir on m.id_so_det = sakhir.id_so_det
left join saldo_sewing_defect_awal on m.id_so_det = saldo_sewing_defect_awal.so_det_id
left join saldo_sewing_defect_akhir on m.id_so_det = saldo_sewing_defect_akhir.so_det_id
left join saldo_sewing_reject_awal d on m.id_so_det = d.so_det_id
left join saldo_sewing_reject_akhir e on m.id_so_det = e.so_det_id
left join saldo_packing_awal on m.id_so_det = saldo_packing_awal.so_det_id
left join saldo_packing_akhir on m.id_so_det = saldo_packing_akhir.so_det_id
left join saldo_packing_defect_awal on m.id_so_det = saldo_packing_defect_awal.so_det_id
left join saldo_packing_defect_akhir on m.id_so_det = saldo_packing_defect_akhir.so_det_id
left join saldo_awal_upload sal on m.id_so_det = sal.so_det_id
left join laravel_nds.master_size_new msn on m.size = msn.size
$filter
group by
buyer,
ws,
color,
m.size
HAVING
(
    qty_loading_awal != 0 OR
    qty_loading != 0 OR
    defect_sewing_akhir != 0 OR
    defect_spotcleaning_akhir != 0 OR
    defect_mending_akhir != 0 OR
    input_rework_sewing != 0 OR
    input_rework_spotcleaning != 0 OR
    input_rework_mending != 0 OR
    qty_sew_reject != 0 OR
    qty_sew != 0 OR
    qty_sew_adj != 0 OR
    saldo_akhir_sewing != 0 OR
    saldo_awal_steam != 0 OR
    in_steam != 0 OR
    out_steam != 0 OR
    adj_steam != 0 OR
    saldo_akhir_steam != 0 OR
    saldo_awal_def_sewing != 0 OR
    in_def_sewing != 0 OR
    out_def_sewing != 0 OR
    adj_def_sewing != 0 OR
    saldo_akhir_def_sewing != 0 OR
    saldo_awal_def_spotcleaning != 0 OR
    in_def_spotcleaning != 0 OR
    out_def_spotcleaning != 0 OR
    adj_def_spotcleaning != 0 OR
    saldo_akhir_def_spotcleaning != 0 OR
    saldo_awal_def_mending != 0 OR
    in_def_mending != 0 OR
    out_def_mending != 0 OR
    adj_def_mending != 0 OR
    saldo_akhir_def_mending != 0 OR
    saldo_awal_def_pck_sewing != 0 OR
    in_def_pck_sewing != 0 OR
    out_def_pck_sewing != 0 OR
    adj_def_pck_sewing != 0 OR
    saldo_akhir_def_pck_sewing != 0 OR
    saldo_awal_def_pck_spotcleaning != 0 OR
    in_def_pck_spotcleaning != 0 OR
    out_def_pck_spotcleaning != 0 OR
    adj_def_pck_spotcleaning != 0 OR
    saldo_akhir_def_pck_spotcleaning != 0 OR
    saldo_awal_def_pck_mending != 0 OR
    in_def_pck_mending != 0 OR
    out_def_pck_mending != 0 OR
    adj_def_pck_mending != 0 OR
    saldo_akhir_def_pck_mending != 0
)
ORDER BY
buyer asc,
ws asc,
color asc,
msn.urutan asc
              ");

        return response()->json($data);
    }
}
