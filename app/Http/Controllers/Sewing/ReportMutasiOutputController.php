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
        ini_set('memory_limit', '1024M');
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        if ($request->ajax()) {
            $data_mut = DB::connection('mysql_sb')->select("SELECT
			ac.kpno,
			ms.supplier buyer,
			ac.styleno,
			sd.color,
			sd.size,
			so_det_id,
			sum(sa_qc_line_awal) - sum(sa_loading_awal) sa_sewing,
			sum(qty_loading) qty_loading,
			sum(input_rework_sewing) input_rework_sewing,
			sum(input_rework_spotcleaning) input_rework_spotcleaning,
			sum(input_rework_mending) input_rework_mending,
			sum(output_def_sewing) output_def_sewing,
			sum(output_def_spotcleaning) output_def_spotcleaning,
			sum(output_def_mending) output_def_mending,
			sum(qty_reject) qty_reject,
			sum(out_sew_rft) out_sew_rft,
			sum(out_sew_rework) out_sew_rework,
			sum(sa_qc_line_awal) - sum(sa_loading_awal) + sum(qty_loading) - sum(qty_reject) - sum(output_def_sewing) - sum(output_def_spotcleaning) - sum(output_def_mending) - sum(out_sew_rft) - sum(out_sew_rework) saldo_akhir_qc_line,
			sum(sa_out_sew) - sum(sa_in_steam) sa_steam,
			sum(input_steam) input_steam,
			sum(output_steam) output_steam,
			sum(sa_out_sew) - sum(sa_in_steam) + sum(input_steam) - sum(output_steam) saldo_akhir_steam
FROM
(
SELECT
			so_det_id,
			count(so_det_id) sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at < '$tgl_awal' and STATUS = 'NORMAL'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			sum(sa_loading_awal) sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty sa_loading_awal
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
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			SUM(qty_loading) qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty qty_loading
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
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS input_rework_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'  and defect_status = 'reworked'
GROUP BY
    so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS output_def_sewing,
			SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS output_def_spotcleaning,
			SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir' and defect_status = 'defect'
GROUP BY
    so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			count(so_det_id) qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
		signalbit_erp.output_rejects
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			count(so_det_id) out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and STATUS = 'NORMAL'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			count(so_det_id) out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and STATUS = 'REWORK'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			count(so_det_id) sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at < '$tgl_awal'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			count(so_det_id) sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts_packing a
WHERE
     updated_at < '$tgl_awal'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			count(so_det_id) input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and status in ('NORMAL', 'REWORK')
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			count(so_det_id) output_steam
FROM
     signalbit_erp.output_rfts_packing a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and status in ('NORMAL', 'REWORK')
GROUP BY so_det_id
) d_rep
inner join signalbit_erp.so_det sd on d_rep.so_det_id = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join signalbit_erp.master_size_new msn on sd.size = msn.size
GROUP BY so_det_id
order by buyer asc, kpno asc, styleno asc, color asc, msn.urutan asc
      ");

            return DataTables::of($data_mut)->toJson();
        }
        return view('sewing.report.report_mutasi_output', ['page' => 'dashboard-sewing-eff', "subPageGroup" => "sewing-report", "subPage" => "report_mut_output", "containerFluid" => true,]);
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
			so_det_id,
			sum(sa_qc_line_awal) - sum(sa_loading_awal) sa_sewing,
			sum(qty_loading) qty_loading,
			sum(input_rework_sewing) input_rework_sewing,
			sum(input_rework_spotcleaning) input_rework_spotcleaning,
			sum(input_rework_mending) input_rework_mending,
			sum(output_def_sewing) output_def_sewing,
			sum(output_def_spotcleaning) output_def_spotcleaning,
			sum(output_def_mending) output_def_mending,
			sum(qty_reject) qty_reject,
			sum(out_sew_rft) out_sew_rft,
			sum(out_sew_rework) out_sew_rework,
			sum(sa_qc_line_awal) - sum(sa_loading_awal) + sum(qty_loading) - sum(qty_reject) - sum(output_def_sewing) - sum(output_def_spotcleaning) - sum(output_def_mending) - sum(out_sew_rft) - sum(out_sew_rework) saldo_akhir_qc_line,
			sum(sa_out_sew) - sum(sa_in_steam) sa_steam,
			sum(input_steam) input_steam,
			sum(output_steam) output_steam,
			sum(sa_out_sew) - sum(sa_in_steam) + sum(input_steam) - sum(output_steam) saldo_akhir_steam
FROM
(
SELECT
			so_det_id,
			count(so_det_id) sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at < '$tgl_awal' and STATUS = 'NORMAL'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			sum(sa_loading_awal) sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty sa_loading_awal
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
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			SUM(qty_loading) qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
		(
			SELECT
						so_det_id, qty qty_loading
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
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS input_rework_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'  and defect_status = 'reworked'
GROUP BY
    so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS output_def_sewing,
			SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS output_def_spotcleaning,
			SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM signalbit_erp.output_defects a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir' and defect_status = 'defect'
GROUP BY
    so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			count(so_det_id) qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
		signalbit_erp.output_rejects
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			count(so_det_id) out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and STATUS = 'NORMAL'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			count(so_det_id) out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and STATUS = 'REWORK'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			count(so_det_id) sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at < '$tgl_awal'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			count(so_det_id) sa_in_steam,
			'0' input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts_packing a
WHERE
     updated_at < '$tgl_awal'
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			count(so_det_id) input_steam,
			'0' output_steam
FROM
     signalbit_erp.output_rfts a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and status in ('NORMAL', 'REWORK')
GROUP BY so_det_id
UNION
SELECT
			so_det_id,
			'0' sa_qc_line_awal,
			'0' sa_loading_awal,
			'0' qty_loading,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0'input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' out_sew_rft,
			'0' out_sew_rework,
			'0' sa_out_sew,
			'0' sa_in_steam,
			'0' input_steam,
			count(so_det_id) output_steam
FROM
     signalbit_erp.output_rfts_packing a
WHERE
     updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir' and status in ('NORMAL', 'REWORK')
GROUP BY so_det_id
) d_rep
inner join signalbit_erp.so_det sd on d_rep.so_det_id = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join signalbit_erp.master_size_new msn on sd.size = msn.size
GROUP BY so_det_id
order by buyer asc, kpno asc, styleno asc, color asc, msn.urutan asc

              ");

        return response()->json($data);
    }
}
