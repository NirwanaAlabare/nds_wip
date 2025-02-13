<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class ReportDocController extends Controller
{
    public function report_doc_laporan_wip(Request $request)
    {
        return view('report_doc.laporan_wip', ['page' => 'dashboard-report-doc', "subPageGroup" => "report-doc-laporan", "subPage" => "report-doc-laporan-wip"]);
    }

    public function show_report_doc_lap_wip(Request $request)
    {
        $timestamp = Carbon::now();
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        $data_wip = DB::connection('mysql_sb')->select("SELECT
                                            ws,
                                            sum(dc_saldo_awal) - sum(pck_saldo_awal) saldo_awal,
                                            sum(dc_in) dc_in,
                                            sum(pck_out) pck_out,
                                            sum(dc_saldo_awal) - sum(pck_saldo_awal) + sum(dc_in) -  sum(pck_out) saldo_akhir
FROM
(
SELECT
	stocker_dc.act_costing_ws ws,
	SUM(stocker_dc.dc_qty) dc_saldo_awal,
	'0' pck_saldo_awal,
	'0' dc_in,
	'0' pck_out
FROM (
	SELECT
		stocker_input.act_costing_ws,
		stocker_input.color,
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio,
		(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0)) dc_qty,
		GROUP_CONCAT(stocker_input.id_qr_stocker) stocker_list,
		SUM(CASE WHEN dc_in_input.id IS NULL THEN 1 ELSE 0 END) not_complete
	FROM
		laravel_nds.stocker_input
		LEFT JOIN laravel_nds.dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
	WHERE
			dc_in_input.updated_at < '$tgl_awal'
	GROUP BY
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio
	HAVING
		not_complete = 0
) stocker_dc
group by stocker_dc.act_costing_ws
UNION ALL
SELECT
			ac.kpno ws,
			'0' dc_saldo_awal,
			sum(pck_out) pck_saldo_awal,
			'0' dc_in,
			'0' pck_out
FROM
(
select count(barcode) pck_out, barcode, po from laravel_nds.packing_packing_out_scan
where updated_at < '$tgl_awal'
group by barcode, po
) a
inner join laravel_nds.ppic_master_so p on a.barcode = p.barcode and a.po = p.po
inner join signalbit_erp.so_det sd on p.id_so_det = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
group by ac.kpno
UNION ALL
SELECT
	stocker_dc.act_costing_ws ws,
	'0' dc_saldo_awal,
	'0' pck_saldo_awal,
	SUM(stocker_dc.dc_qty) dc_in,
	'0' pck_out
FROM (
	SELECT
		stocker_input.act_costing_ws,
		stocker_input.color,
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio,
		(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0)) dc_qty,
		GROUP_CONCAT(stocker_input.id_qr_stocker) stocker_list,
		SUM(CASE WHEN dc_in_input.id IS NULL THEN 1 ELSE 0 END) not_complete
	FROM
		laravel_nds.stocker_input
		LEFT JOIN laravel_nds.dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
	WHERE
			dc_in_input.updated_at >= '$tgl_awal' and dc_in_input.updated_at <= '$tgl_akhir'
	GROUP BY
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio
	HAVING
		not_complete = 0
) stocker_dc
group by stocker_dc.act_costing_ws
UNION ALL
SELECT
			ac.kpno ws,
			'0' dc_saldo_awal,
			'0' pck_saldo_awal,
			'0' dc_in,
			sum(pck_out) pck_out
FROM
(
select count(barcode) pck_out, barcode, po from laravel_nds.packing_packing_out_scan
where updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
group by barcode, po
) a
inner join laravel_nds.ppic_master_so p on a.barcode = p.barcode and a.po = p.po
inner join signalbit_erp.so_det sd on p.id_so_det = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
group by ac.kpno
)a
GROUP BY
		ws
");

        return DataTables::of($data_wip)->toJson();
    }


    public function export_excel_doc_lap_wip(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $data = DB::connection('mysql_sb')->select("SELECT
                                            ws,
                                            sum(dc_saldo_awal) - sum(pck_saldo_awal) saldo_awal,
                                            sum(dc_in) dc_in,
                                            sum(pck_out) pck_out,
                                            sum(dc_saldo_awal) - sum(pck_saldo_awal) + sum(dc_in) -  sum(pck_out) saldo_akhir
FROM
(
SELECT
	stocker_dc.act_costing_ws ws,
	SUM(stocker_dc.dc_qty) dc_saldo_awal,
	'0' pck_saldo_awal,
	'0' dc_in,
	'0' pck_out
FROM (
	SELECT
		stocker_input.act_costing_ws,
		stocker_input.color,
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio,
		(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0)) dc_qty,
		GROUP_CONCAT(stocker_input.id_qr_stocker) stocker_list,
		SUM(CASE WHEN dc_in_input.id IS NULL THEN 1 ELSE 0 END) not_complete
	FROM
		laravel_nds.stocker_input
		LEFT JOIN laravel_nds.dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
	WHERE
			dc_in_input.updated_at < '$tgl_awal'
	GROUP BY
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio
	HAVING
		not_complete = 0
) stocker_dc
group by stocker_dc.act_costing_ws
UNION ALL
SELECT
			ac.kpno ws,
			'0' dc_saldo_awal,
			sum(pck_out) pck_saldo_awal,
			'0' dc_in,
			'0' pck_out
FROM
(
select count(barcode) pck_out, barcode, po from laravel_nds.packing_packing_out_scan
where updated_at < '$tgl_awal'
group by barcode, po
) a
inner join laravel_nds.ppic_master_so p on a.barcode = p.barcode and a.po = p.po
inner join signalbit_erp.so_det sd on p.id_so_det = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
group by ac.kpno
UNION ALL
SELECT
	stocker_dc.act_costing_ws ws,
	'0' dc_saldo_awal,
	'0' pck_saldo_awal,
	SUM(stocker_dc.dc_qty) dc_in,
	'0' pck_out
FROM (
	SELECT
		stocker_input.act_costing_ws,
		stocker_input.color,
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio,
		(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0)) dc_qty,
		GROUP_CONCAT(stocker_input.id_qr_stocker) stocker_list,
		SUM(CASE WHEN dc_in_input.id IS NULL THEN 1 ELSE 0 END) not_complete
	FROM
		laravel_nds.stocker_input
		LEFT JOIN laravel_nds.dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
	WHERE
			dc_in_input.updated_at >= '$tgl_awal' and dc_in_input.updated_at <= '$tgl_akhir'
	GROUP BY
		stocker_input.form_cut_id,
		stocker_input.so_det_id,
		stocker_input.group_stocker,
		stocker_input.ratio
	HAVING
		not_complete = 0
) stocker_dc
group by stocker_dc.act_costing_ws
UNION ALL
SELECT
			ac.kpno ws,
			'0' dc_saldo_awal,
			'0' pck_saldo_awal,
			'0' dc_in,
			sum(pck_out) pck_out
FROM
(
select count(barcode) pck_out, barcode, po from laravel_nds.packing_packing_out_scan
where updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
group by barcode, po
) a
inner join laravel_nds.ppic_master_so p on a.barcode = p.barcode and a.po = p.po
inner join signalbit_erp.so_det sd on p.id_so_det = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
group by ac.kpno
)a
GROUP BY
		ws

              ");

        return response()->json($data);
    }
}
