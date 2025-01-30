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
        ini_set('memory_limit', '2048M');
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        if ($request->ajax()) {
            $data_mut = DB::connection('mysql_sb')->select("SELECT
			ac.kpno,
			ms.supplier buyer,
			ac.styleno,
			sd.color,
			sd.size,
			d_rep.so_det_id,
sum(sa_loading) - sum(sa_rft) - sum(sa_reject) - sum(sa_defect) + sum(sa_rework) saldo_awal_sewing,
sum(qty_loading) qty_loading,
sum(input_rework_sewing) input_rework_sewing,
sum(input_rework_spotcleaning) input_rework_spotcleaning,
sum(input_rework_mending) input_rework_mending,
sum(defect_sewing) defect_sewing,
sum(defect_spotcleaning) defect_spotcleaning,
sum(defect_mending) defect_mending,
sum(output_rejects) output_rejects,
sum(output_rfts) output_rfts,
sum(sa_loading) - sum(sa_rft) - sum(sa_reject) - sum(sa_defect) + sum(sa_rework) + sum(qty_loading) + sum(input_rework_sewing) + sum(input_rework_spotcleaning) + sum(input_rework_mending) - sum(defect_sewing) - sum(defect_spotcleaning) - sum(defect_mending) - sum(output_rejects) - sum(output_rfts) saldo_akhir,
sum(sa_out_sew) - sum(sa_steam) saldo_awal_steam,
sum(input_steam) input_steam,
sum(output_steam) output_steam,
sum(sa_out_sew) - sum(sa_steam) + sum(input_steam) - sum(output_steam) saldo_akhir_steam,
coalesce(saldo_awal_def_sew,0) saldo_awal_def_sew,
coalesce(defect_trans_sewing,0) defect_trans_sewing,
coalesce(defect_trans_rew_sewing,0) defect_trans_rew_sewing,
coalesce(saldo_akhir_def_sewing,0) saldo_akhir_def_sewing,
coalesce(saldo_awal_def_spotcleaning,0) saldo_awal_def_spotcleaning,
coalesce(defect_trans_spotcleaning,0) defect_trans_spotcleaning,
coalesce(defect_trans_rew_spotcleaning,0) defect_trans_rew_spotcleaning,
coalesce(saldo_akhir_def_spotcleaning,0) saldo_akhir_def_spotcleaning,
coalesce(saldo_awal_def_mending,0) saldo_awal_def_mending,
coalesce(defect_trans_mending,0) defect_trans_mending,
coalesce(defect_trans_rew_mending,0) defect_trans_rew_mending,
coalesce(saldo_akhir_def_mending,0) saldo_akhir_def_mending,
coalesce(saldo_awal_def_sew_pck,0) saldo_awal_def_sew_pck,
coalesce(defect_trans_sewing_pck,0) defect_trans_sewing_pck,
coalesce(defect_trans_rew_sewing_pck,0) defect_trans_rew_sewing_pck,
coalesce(saldo_akhir_def_sewing_pck,0) saldo_akhir_def_sewing_pck,
coalesce(saldo_awal_def_spotcleaning_pck,0) saldo_awal_def_spotcleaning_pck,
coalesce(defect_trans_spotcleaning_pck,0) defect_trans_spotcleaning_pck,
coalesce(defect_trans_rew_spotcleaning_pck,0) defect_trans_rew_spotcleaning_pck,
coalesce(saldo_akhir_def_spotcleaning_pck,0) saldo_akhir_def_spotcleaning_pck,
coalesce(saldo_awal_def_mending_pck,0) saldo_awal_def_mending_pck,
coalesce(defect_trans_mending_pck,0) defect_trans_mending_pck,
coalesce(defect_trans_rew_mending_pck,0) defect_trans_rew_mending_pck,
coalesce(saldo_akhir_def_mending_pck,0) saldo_akhir_def_mending_pck
FROM
(
SELECT
			so_det_id,
			sum(qty) sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty
			FROM
						laravel_nds.loading_line a
			INNER JOIN
						laravel_nds.stocker_input b on a.stocker_id = b.id
			WHERE
						a.updated_at < '$tgl_awal'
			GROUP BY
						b.so_det_id,
						b.form_cut_id,
						b.group_stocker,
						b.ratio
		) a
GROUP BY so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			count(so_det_id) sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			count(so_det_id) sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rejects a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			count(so_det_id) sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			count(so_det_id) sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal' and a.defect_status in ('REWORKED','REJECTED')
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			sum(qty) qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty
			FROM
						laravel_nds.loading_line a
			INNER JOIN
						laravel_nds.stocker_input b on a.stocker_id = b.id
			WHERE
						a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
			GROUP BY
						b.so_det_id,
						b.form_cut_id,
						b.group_stocker,
						b.ratio
		) a
GROUP BY so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			count(so_det_id) output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rejects a
WHERE
     a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			count(so_det_id) output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			count(so_det_id) sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			count(so_det_id) sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts_packing a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			count(so_det_id) input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			count(so_det_id) output_steam
FROM signalbit_erp.output_rfts_packing a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
) d_rep
LEFT JOIN
(
SELECT
so_det_id,
sum(sa_defect_trans_sewing) - sum(sa_defect_trans_rew_sewing) saldo_awal_def_sew,
sum(defect_trans_sewing) defect_trans_sewing,
sum(defect_trans_rew_sewing) defect_trans_rew_sewing,
sum(sa_defect_trans_sewing) - sum(sa_defect_trans_rew_sewing) + sum(defect_trans_sewing) - sum(defect_trans_rew_sewing) saldo_akhir_def_sewing,
sum(sa_defect_trans_spotcleaning) - sum(sa_defect_trans_rew_spotcleaning) saldo_awal_def_spotcleaning,
sum(defect_trans_spotcleaning) defect_trans_spotcleaning,
sum(defect_trans_rew_spotcleaning) defect_trans_rew_spotcleaning,
sum(sa_defect_trans_spotcleaning) - sum(sa_defect_trans_rew_spotcleaning) + sum(defect_trans_spotcleaning) - sum(defect_trans_rew_spotcleaning) saldo_akhir_def_spotcleaning,
sum(sa_defect_trans_mending) - sum(sa_defect_trans_rew_mending) saldo_awal_def_mending,
sum(defect_trans_mending) defect_trans_mending,
sum(defect_trans_rew_mending) defect_trans_rew_mending,
sum(sa_defect_trans_mending) - sum(sa_defect_trans_rew_mending) + sum(defect_trans_mending) - sum(defect_trans_rew_mending) saldo_akhir_def_mending
FROM
(
SELECT
			so_det_id,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS sa_defect_trans_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS sa_defect_trans_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS sa_defect_trans_mending,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_sewing,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_spotcleaning,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_mending,
			'0' defect_trans_sewing,
			'0' defect_trans_spotcleaning,
			'0' defect_trans_mending,
			'0' defect_trans_rew_sewing,
			'0' defect_trans_rew_spotcleaning,
			'0' defect_trans_rew_mending
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_defect_trans_sewing,
			'0' sa_defect_trans_spotcleaning,
			'0' sa_defect_trans_mending,
			'0' sa_defect_trans_rew_sewing,
			'0' sa_defect_trans_rew_spotcleaning,
			'0' sa_defect_trans_rew_mending,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_trans_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_trans_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_trans_mending,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_sewing,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_spotcleaning,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_mending
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
        so_det_id
) mut_def
group by so_det_id
) mut_def on d_rep.so_det_id = mut_def.so_det_id
LEFT JOIN
(
SELECT
so_det_id,
sum(sa_defect_trans_sewing_pck) - sum(sa_defect_trans_rew_sewing_pck) saldo_awal_def_sew_pck,
sum(defect_trans_sewing_pck) defect_trans_sewing_pck,
sum(defect_trans_rew_sewing_pck) defect_trans_rew_sewing_pck,
sum(sa_defect_trans_sewing_pck) - sum(sa_defect_trans_rew_sewing_pck) + sum(defect_trans_sewing_pck) - sum(defect_trans_rew_sewing_pck) saldo_akhir_def_sewing_pck,
sum(sa_defect_trans_spotcleaning_pck) - sum(sa_defect_trans_rew_spotcleaning_pck) saldo_awal_def_spotcleaning_pck,
sum(defect_trans_spotcleaning_pck) defect_trans_spotcleaning_pck,
sum(defect_trans_rew_spotcleaning_pck) defect_trans_rew_spotcleaning_pck,
sum(sa_defect_trans_spotcleaning_pck) - sum(sa_defect_trans_rew_spotcleaning_pck) + sum(defect_trans_spotcleaning_pck) - sum(defect_trans_rew_spotcleaning_pck) saldo_akhir_def_spotcleaning_pck,
sum(sa_defect_trans_mending_pck) - sum(sa_defect_trans_rew_mending_pck) saldo_awal_def_mending_pck,
sum(defect_trans_mending_pck) defect_trans_mending_pck,
sum(defect_trans_rew_mending_pck) defect_trans_rew_mending_pck,
sum(sa_defect_trans_mending_pck) - sum(sa_defect_trans_rew_mending_pck) + sum(defect_trans_mending_pck) - sum(defect_trans_rew_mending_pck) saldo_akhir_def_mending_pck
FROM
(
SELECT
			so_det_id,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS sa_defect_trans_sewing_pck,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS sa_defect_trans_spotcleaning_pck,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS sa_defect_trans_mending_pck,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_sewing_pck,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_spotcleaning_pck,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_mending_pck,
			'0' defect_trans_sewing_pck,
			'0' defect_trans_spotcleaning_pck,
			'0' defect_trans_mending_pck,
			'0' defect_trans_rew_sewing_pck,
			'0' defect_trans_rew_spotcleaning_pck,
			'0' defect_trans_rew_mending_pck
FROM signalbit_erp.output_defects_packing a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_defect_trans_sewing_pck,
			'0' sa_defect_trans_spotcleaning_pck,
			'0' sa_defect_trans_mending_pck,
			'0' sa_defect_trans_rew_sewing_pck,
			'0' sa_defect_trans_rew_spotcleaning_pck,
			'0' sa_defect_trans_rew_mending_pck,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_trans_sewing_pck,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_trans_spotcleaning_pck,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_trans_mending_pck,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_sewing_pck,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_spotcleaning_pck,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_mending_pck
FROM signalbit_erp.output_defects_packing a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
        so_det_id
) mut_pck
group by so_det_id
) mut_pck on d_rep.so_det_id = mut_pck.so_det_id
inner join signalbit_erp.so_det sd on d_rep.so_det_id = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join signalbit_erp.master_size_new msn on sd.size = msn.size
GROUP BY ac.kpno, ac.styleno, sd.color, sd.size
order by buyer asc, kpno asc, styleno asc, color asc, msn.urutan asc
      ");

            return DataTables::of($data_mut)->toJson();
        }
    }

    public function export_excel_mut_output(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $data = DB::connection('mysql_sb')->select("SELECT
			ac.kpno,
			ms.supplier buyer,
			ac.styleno,
			sd.color,
			sd.size,
			d_rep.so_det_id,
sum(sa_loading) - sum(sa_rft) - sum(sa_reject) - sum(sa_defect) + sum(sa_rework) saldo_awal_sewing,
sum(qty_loading) qty_loading,
sum(input_rework_sewing) input_rework_sewing,
sum(input_rework_spotcleaning) input_rework_spotcleaning,
sum(input_rework_mending) input_rework_mending,
sum(defect_sewing) defect_sewing,
sum(defect_spotcleaning) defect_spotcleaning,
sum(defect_mending) defect_mending,
sum(output_rejects) output_rejects,
sum(output_rfts) output_rfts,
sum(sa_loading) - sum(sa_rft) - sum(sa_reject) - sum(sa_defect) + sum(sa_rework) + sum(qty_loading) + sum(input_rework_sewing) + sum(input_rework_spotcleaning) + sum(input_rework_mending) - sum(defect_sewing) - sum(defect_spotcleaning) - sum(defect_mending) - sum(output_rejects) - sum(output_rfts) saldo_akhir,
sum(sa_out_sew) - sum(sa_steam) saldo_awal_steam,
sum(input_steam) input_steam,
sum(output_steam) output_steam,
sum(sa_out_sew) - sum(sa_steam) + sum(input_steam) - sum(output_steam) saldo_akhir_steam,
coalesce(saldo_awal_def_sew,0) saldo_awal_def_sew,
coalesce(defect_trans_sewing,0) defect_trans_sewing,
coalesce(defect_trans_rew_sewing,0) defect_trans_rew_sewing,
coalesce(saldo_akhir_def_sewing,0) saldo_akhir_def_sewing,
coalesce(saldo_awal_def_spotcleaning,0) saldo_awal_def_spotcleaning,
coalesce(defect_trans_spotcleaning,0) defect_trans_spotcleaning,
coalesce(defect_trans_rew_spotcleaning,0) defect_trans_rew_spotcleaning,
coalesce(saldo_akhir_def_spotcleaning,0) saldo_akhir_def_spotcleaning,
coalesce(saldo_awal_def_mending,0) saldo_awal_def_mending,
coalesce(defect_trans_mending,0) defect_trans_mending,
coalesce(defect_trans_rew_mending,0) defect_trans_rew_mending,
coalesce(saldo_akhir_def_mending,0) saldo_akhir_def_mending,
coalesce(saldo_awal_def_sew_pck,0) saldo_awal_def_sew_pck,
coalesce(defect_trans_sewing_pck,0) defect_trans_sewing_pck,
coalesce(defect_trans_rew_sewing_pck,0) defect_trans_rew_sewing_pck,
coalesce(saldo_akhir_def_sewing_pck,0) saldo_akhir_def_sewing_pck,
coalesce(saldo_awal_def_spotcleaning_pck,0) saldo_awal_def_spotcleaning_pck,
coalesce(defect_trans_spotcleaning_pck,0) defect_trans_spotcleaning_pck,
coalesce(defect_trans_rew_spotcleaning_pck,0) defect_trans_rew_spotcleaning_pck,
coalesce(saldo_akhir_def_spotcleaning_pck,0) saldo_akhir_def_spotcleaning_pck,
coalesce(saldo_awal_def_mending_pck,0) saldo_awal_def_mending_pck,
coalesce(defect_trans_mending_pck,0) defect_trans_mending_pck,
coalesce(defect_trans_rew_mending_pck,0) defect_trans_rew_mending_pck,
coalesce(saldo_akhir_def_mending_pck,0) saldo_akhir_def_mending_pck
FROM
(
SELECT
			so_det_id,
			sum(qty) sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty
			FROM
						laravel_nds.loading_line a
			INNER JOIN
						laravel_nds.stocker_input b on a.stocker_id = b.id
			WHERE
						a.updated_at < '$tgl_awal'
			GROUP BY
						b.so_det_id,
						b.form_cut_id,
						b.group_stocker,
						b.ratio
		) a
GROUP BY so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			count(so_det_id) sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			count(so_det_id) sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rejects a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			count(so_det_id) sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			count(so_det_id) sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal' and a.defect_status in ('REWORKED','REJECTED')
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			sum(qty) qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty
			FROM
						laravel_nds.loading_line a
			INNER JOIN
						laravel_nds.stocker_input b on a.stocker_id = b.id
			WHERE
						a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
			GROUP BY
						b.so_det_id,
						b.form_cut_id,
						b.group_stocker,
						b.ratio
		) a
GROUP BY so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
    SUM(CASE WHEN allocation = 'SEWING' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			count(so_det_id) output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rejects a
WHERE
     a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			count(so_det_id) output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			count(so_det_id) sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			count(so_det_id) sa_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts_packing a
WHERE
     a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			count(so_det_id) input_steam,
			'0' output_steam
FROM signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_loading,
			'0' sa_rft,
			'0' sa_reject,
			'0' sa_defect,
			'0' sa_rework,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' defect_sewing,
			'0' defect_spotcleaning,
			'0' defect_mending,
			'0' output_rejects,
			'0' output_rfts,
			'0' sa_out_sew,
			'0' sa_steam,
			'0' input_steam,
			count(so_det_id) output_steam
FROM signalbit_erp.output_rfts_packing a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
) d_rep
LEFT JOIN
(
SELECT
so_det_id,
sum(sa_defect_trans_sewing) - sum(sa_defect_trans_rew_sewing) saldo_awal_def_sew,
sum(defect_trans_sewing) defect_trans_sewing,
sum(defect_trans_rew_sewing) defect_trans_rew_sewing,
sum(sa_defect_trans_sewing) - sum(sa_defect_trans_rew_sewing) + sum(defect_trans_sewing) - sum(defect_trans_rew_sewing) saldo_akhir_def_sewing,
sum(sa_defect_trans_spotcleaning) - sum(sa_defect_trans_rew_spotcleaning) saldo_awal_def_spotcleaning,
sum(defect_trans_spotcleaning) defect_trans_spotcleaning,
sum(defect_trans_rew_spotcleaning) defect_trans_rew_spotcleaning,
sum(sa_defect_trans_spotcleaning) - sum(sa_defect_trans_rew_spotcleaning) + sum(defect_trans_spotcleaning) - sum(defect_trans_rew_spotcleaning) saldo_akhir_def_spotcleaning,
sum(sa_defect_trans_mending) - sum(sa_defect_trans_rew_mending) saldo_awal_def_mending,
sum(defect_trans_mending) defect_trans_mending,
sum(defect_trans_rew_mending) defect_trans_rew_mending,
sum(sa_defect_trans_mending) - sum(sa_defect_trans_rew_mending) + sum(defect_trans_mending) - sum(defect_trans_rew_mending) saldo_akhir_def_mending
FROM
(
SELECT
			so_det_id,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS sa_defect_trans_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS sa_defect_trans_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS sa_defect_trans_mending,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_sewing,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_spotcleaning,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_mending,
			'0' defect_trans_sewing,
			'0' defect_trans_spotcleaning,
			'0' defect_trans_mending,
			'0' defect_trans_rew_sewing,
			'0' defect_trans_rew_spotcleaning,
			'0' defect_trans_rew_mending
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_defect_trans_sewing,
			'0' sa_defect_trans_spotcleaning,
			'0' sa_defect_trans_mending,
			'0' sa_defect_trans_rew_sewing,
			'0' sa_defect_trans_rew_spotcleaning,
			'0' sa_defect_trans_rew_mending,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_trans_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_trans_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_trans_mending,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_sewing,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_spotcleaning,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_mending
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
        so_det_id
) mut_def
group by so_det_id
) mut_def on d_rep.so_det_id = mut_def.so_det_id
LEFT JOIN
(
SELECT
so_det_id,
sum(sa_defect_trans_sewing_pck) - sum(sa_defect_trans_rew_sewing_pck) saldo_awal_def_sew_pck,
sum(defect_trans_sewing_pck) defect_trans_sewing_pck,
sum(defect_trans_rew_sewing_pck) defect_trans_rew_sewing_pck,
sum(sa_defect_trans_sewing_pck) - sum(sa_defect_trans_rew_sewing_pck) + sum(defect_trans_sewing_pck) - sum(defect_trans_rew_sewing_pck) saldo_akhir_def_sewing_pck,
sum(sa_defect_trans_spotcleaning_pck) - sum(sa_defect_trans_rew_spotcleaning_pck) saldo_awal_def_spotcleaning_pck,
sum(defect_trans_spotcleaning_pck) defect_trans_spotcleaning_pck,
sum(defect_trans_rew_spotcleaning_pck) defect_trans_rew_spotcleaning_pck,
sum(sa_defect_trans_spotcleaning_pck) - sum(sa_defect_trans_rew_spotcleaning_pck) + sum(defect_trans_spotcleaning_pck) - sum(defect_trans_rew_spotcleaning_pck) saldo_akhir_def_spotcleaning_pck,
sum(sa_defect_trans_mending_pck) - sum(sa_defect_trans_rew_mending_pck) saldo_awal_def_mending_pck,
sum(defect_trans_mending_pck) defect_trans_mending_pck,
sum(defect_trans_rew_mending_pck) defect_trans_rew_mending_pck,
sum(sa_defect_trans_mending_pck) - sum(sa_defect_trans_rew_mending_pck) + sum(defect_trans_mending_pck) - sum(defect_trans_rew_mending_pck) saldo_akhir_def_mending_pck
FROM
(
SELECT
			so_det_id,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS sa_defect_trans_sewing_pck,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS sa_defect_trans_spotcleaning_pck,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS sa_defect_trans_mending_pck,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_sewing_pck,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_spotcleaning_pck,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS sa_defect_trans_rew_mending_pck,
			'0' defect_trans_sewing_pck,
			'0' defect_trans_spotcleaning_pck,
			'0' defect_trans_mending_pck,
			'0' defect_trans_rew_sewing_pck,
			'0' defect_trans_rew_spotcleaning_pck,
			'0' defect_trans_rew_mending_pck
FROM signalbit_erp.output_defects_packing a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at < '$tgl_awal'
GROUP BY
    so_det_id
UNION ALL
SELECT
			so_det_id,
			'0' sa_defect_trans_sewing_pck,
			'0' sa_defect_trans_spotcleaning_pck,
			'0' sa_defect_trans_mending_pck,
			'0' sa_defect_trans_rew_sewing_pck,
			'0' sa_defect_trans_rew_spotcleaning_pck,
			'0' sa_defect_trans_rew_mending_pck,
        SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_trans_sewing_pck,
        SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_trans_spotcleaning_pck,
        SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_trans_mending_pck,
		SUM(CASE WHEN allocation = 'SEWING'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_sewing_pck,
		SUM(CASE WHEN allocation = 'spotcleaning'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_spotcleaning_pck,
		SUM(CASE WHEN allocation = 'mending'  AND  defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END ) AS defect_trans_rew_mending_pck
FROM signalbit_erp.output_defects_packing a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
        so_det_id
) mut_pck
group by so_det_id
) mut_pck on d_rep.so_det_id = mut_pck.so_det_id
inner join signalbit_erp.so_det sd on d_rep.so_det_id = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join signalbit_erp.master_size_new msn on sd.size = msn.size
GROUP BY ac.kpno, ac.styleno, sd.color, sd.size
order by buyer asc, kpno asc, styleno asc, color asc, msn.urutan asc
              ");

        return response()->json($data);
    }
}
