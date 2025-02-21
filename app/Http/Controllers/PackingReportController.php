<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Export_excel_rep_packing_line_sum_range;
use App\Exports\Export_excel_rep_packing_line_sum_buyer;
use App\Exports\export_excel_rep_packing_mutasi;
use App\Exports\ExportDataTemplatePackingListVertical;


class PackingReportController extends Controller
{
    public function packing_rep_packing_line_sum(Request $request)
    {
        $tgl_akhir_fix = date('Y-m-d', strtotime("+90 days"));
        $tgl_awal_fix = date('Y-m-d', strtotime("-90 days"));
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;

        $data_tipe = DB::select("SELECT 'RANGE' isi , 'RANGE' tampil
        UNION
        SELECT 'BUYER' isi , 'BUYER' tampil
        ");

        $data_po = DB::select("SELECT buyer isi, buyer tampil from ppic_master_so p
        inner join master_sb_ws m on  p.id_so_det = m.id_so_det
        group by buyer
        order by buyer asc
        ");



        return view(
            'packing.packing_rep_packing_line',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-report",
                "subPage" => "packing_rep_packing_line_sum",
                "data_tipe" => $data_tipe,
                "data_po" => $data_po,
                "user" => $user,
                "tgl_skrg" => $tgl_skrg,
                "tgl_awal_fix" => $tgl_awal_fix,
                "tgl_akhir_fix" => $tgl_akhir_fix,
            ]
        );
    }

    public function packing_rep_packing_line_sum_range(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        if ($request->ajax()) {
            $data_pl = DB::select("SELECT
                UPPER(REPLACE(a.created_by, '_', ' ')) sew_line,
                a.created_by,
                m.buyer,
                m.ws,
                m.color,
                m.size,
                a.qty
                from
                (
                select
                so_det_id,
                count(so_det_id) qty,
                created_by
                from output_rfts_packing where date(updated_at) >= '$tgl_awal' and date(updated_at) <= '$tgl_akhir'
                group by so_det_id, created_by
                ) a
                inner join master_sb_ws m on a.so_det_id = m.id_so_det
                left join master_size_new msn on m.size = msn.size
                where created_by is not null
                order by a.created_by asc,ws asc, color asc, urutan asc
              ");

            return DataTables::of($data_pl)->toJson();
        }
    }

    public function packing_rep_packing_line_sum_buyer(Request $request)
    {
        $user = Auth::user()->name;
        $buyer = $request->cbobuyer;

        if ($request->ajax()) {
            $data_pl = DB::select("SELECT
			buyer,
            so_det_id,
			ws,
			color,
			b.size,
            count(so_det_id) qty
            from output_rfts_packing a
			inner join
			(
			select buyer,p.id_so_det, ws, color, size from ppic_master_so p
			inner join master_sb_ws m on p.id_so_det = m.id_so_det
			where m.buyer = '$buyer'
			) b on a.so_det_id = b.id_so_det
		    inner join master_size_new msn on b.size = msn.size
             group by so_det_id
			order by ws asc, color asc, urutan asc
              ");

            return DataTables::of($data_pl)->toJson();
        }
    }

    public function export_excel_rep_packing_line_sum_range(Request $request)
    {
        return Excel::download(new Export_excel_rep_packing_line_sum_range($request->from, $request->to), 'Laporan_Packing_In.xlsx');
    }

    public function export_excel_rep_packing_line_sum_buyer(Request $request)
    {
        return Excel::download(new Export_excel_rep_packing_line_sum_buyer($request->buyer), 'Laporan_Packing_In.xlsx');
    }


    public function packing_rep_packing_mutasi(Request $request)
    {
        return view(
            'packing.packing_rep_packing_mutasi',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-report",
                "subPage" => "packing_rep_packing_mutasi",
                "containerFluid" => true,
            ]
        );
    }

    public function packing_rep_packing_mutasi_load(Request $request)
    {
        ini_set('memory_limit', '1024M');
        // if ($request->ajax()) {
        $data_mut = DB::select("WITH Totals AS (
    SELECT
        po,
        barcode,
        no_carton,
        COUNT(barcode) AS tot_scan
    FROM packing_packing_out_scan
    GROUP BY po, barcode, no_carton
),
FgIn AS (
    SELECT
        po,
        barcode,
        no_carton,
        SUM(qty) AS qty_fg_in,
        lokasi
    FROM fg_fg_in
    WHERE status = 'NORMAL'
    GROUP BY po, barcode, no_carton, lokasi
),
FgOut AS (
    SELECT
        po,
        barcode,
        no_carton,
        SUM(qty) AS qty_fg_out
    FROM fg_fg_out
    WHERE status = 'NORMAL'
    GROUP BY po, barcode, no_carton
)

SELECT
    p.po,
    m.buyer,
    m.ws,
    m.color,
    m.size,
    a.dest,
    a.barcode,
    a.no_carton,
    a.qty AS qty_pl,
    COALESCE(b.tot_scan, 0) AS tot_scan,
    COALESCE(c.qty_fg_in, 0) AS qty_fg_in,
    COALESCE(d.qty_fg_out, 0) AS qty_fg_out,
    c.lokasi,
    COALESCE(a.qty, 0) - COALESCE(d.qty_fg_out, 0) AS balance
FROM packing_master_packing_list a
LEFT JOIN Totals b ON a.barcode = b.barcode AND a.po = b.po AND a.no_carton = b.no_carton
LEFT JOIN FgIn c ON a.barcode = c.barcode AND a.po = c.po AND a.no_carton = c.no_carton
LEFT JOIN FgOut d ON a.barcode = d.barcode AND a.po = d.po AND a.no_carton = d.no_carton
INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
LEFT JOIN master_size_new msn ON m.size = msn.size
ORDER BY a.po ASC, m.buyer ASC, a.no_carton ASC;

      ");

        return DataTables::of($data_mut)->toJson();
        // }
    }


    public function export_excel_rep_packing_mutasi(Request $request)
    {
        // return Excel::download(new export_excel_rep_packing_mutasi, 'Laporan_Packing_In.xlsx');

        $data = DB::select("WITH Totals AS (
            SELECT
                po,
                barcode,
                no_carton,
                COUNT(barcode) AS tot_scan
            FROM packing_packing_out_scan
            GROUP BY po, barcode, no_carton
        ),
        FgIn AS (
            SELECT
                po,
                barcode,
                no_carton,
                SUM(qty) AS qty_fg_in,
                lokasi
            FROM fg_fg_in
            WHERE status = 'NORMAL'
            GROUP BY po, barcode, no_carton, lokasi
        ),
        FgOut AS (
            SELECT
                po,
                barcode,
                no_carton,
                SUM(qty) AS qty_fg_out
            FROM fg_fg_out
            WHERE status = 'NORMAL'
            GROUP BY po, barcode, no_carton
        )

        SELECT
            p.po,
            m.buyer,
            m.ws,
            m.color,
            m.size,
            a.dest,
            a.barcode,
            a.no_carton,
            a.qty AS qty_pl,
            COALESCE(b.tot_scan, 0) AS tot_scan,
            COALESCE(c.qty_fg_in, 0) AS qty_fg_in,
            COALESCE(d.qty_fg_out, 0) AS qty_fg_out,
            c.lokasi,
            COALESCE(a.qty, 0) - COALESCE(d.qty_fg_out, 0) AS balance
        FROM packing_master_packing_list a
        LEFT JOIN Totals b ON a.barcode = b.barcode AND a.po = b.po AND a.no_carton = b.no_carton
        LEFT JOIN FgIn c ON a.barcode = c.barcode AND a.po = c.po AND a.no_carton = c.no_carton
        LEFT JOIN FgOut d ON a.barcode = d.barcode AND a.po = d.po AND a.no_carton = d.no_carton
        INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
        INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
        LEFT JOIN master_size_new msn ON m.size = msn.size
        ORDER BY a.po ASC, m.buyer ASC, a.no_carton ASC;

              ");

        return response()->json($data);
    }

    public function packing_rep_packing_mutasi_wip(Request $request)
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
					sum(sa_pck_line_awal) - sum(sa_trf_gmt_awal) sa_pck_line_awal,
					sum(qty_in_pck_line) qty_in_pck_line,
					sum(input_rework_sewing) input_rework_sewing,
					sum(input_rework_spotcleaning) input_rework_spotcleaning,
					sum(input_rework_mending) input_rework_mending,
					sum(output_def_sewing) output_def_sewing,
					sum(output_def_spotcleaning) output_def_spotcleaning,
					sum(output_def_mending) output_def_mending,
					sum(qty_reject) qty_reject,
					sum(qty_trf_gmt) qty_trf_gmt,
					sum(sa_pck_line_awal) - sum(sa_trf_gmt_awal) + sum(qty_in_pck_line) - sum(qty_trf_gmt) saldo_akhir_pck_line,
					sum(sa_trf_gmt_awal) - sum(sa_pck_in_awal) sa_trf_gmt,
					sum(qty_trf_gmt) qty_trf_gmt_in,
					sum(qty_pck_in) qty_trf_gmt_out,
					sum(sa_trf_gmt_awal) - sum(sa_pck_in_awal) +  sum(qty_trf_gmt) - sum(qty_pck_in) saldo_akhir_trf_gmt,
					sum(sa_pck_in_awal) - sum(qty_pck_out_awal) sa_pck_in,
					sum(qty_pck_in)	qty_pck_in,
					sum(qty_pck_out) qty_pck_out,
					sum(sa_pck_in_awal) - sum(qty_pck_out_awal) + 	sum(qty_pck_in)	- sum(qty_pck_out) saldo_akhir_packing_central
FROM
(
SELECT
      so_det_id,
			count(so_det_id) sa_pck_line_awal,
			'0' sa_trf_gmt_awal,
			'0' qty_in_pck_line,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' output_def_sewing,
      '0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' qty_trf_gmt,
			'0' sa_pck_in_awal,
			'0' qty_pck_in,
			'0' qty_pck_out_awal,
			'0' qty_pck_out
FROM
     signalbit_erp.output_rfts_packing a
WHERE
     updated_at < '$tgl_awal'
GROUP BY so_det_id
UNION
SELECT
      id_so_det so_det_id,
			'0' sa_pck_line_awal,
			sum(qty) sa_trf_gmt_awal,
			'0' qty_in_pck_line,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' output_def_sewing,
      '0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' qty_trf_gmt,
			'0' sa_pck_in_awal,
			'0' qty_pck_in,
			'0' qty_pck_out_awal,
			'0' qty_pck_out
FROM
     laravel_nds.packing_trf_garment a
WHERE
     updated_at < '$tgl_awal'
GROUP BY id_so_det
UNION
SELECT
			so_det_id,
			'0' sa_pck_line_awal,
			'0' sa_trf_gmt_awal,
			count(so_det_id) qty_in_pck_line,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' qty_trf_gmt,
			'0' sa_pck_in_awal,
			'0' qty_pck_in,
			'0' qty_pck_out_awal,
			'0' qty_pck_out
FROM signalbit_erp.output_rfts_packing a
where
			updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY so_det_id
UNION
SELECT
    so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in_pck_line,
    SUM(CASE WHEN allocation = 'SEWING' AND defect_status = 'REWORKED' THEN 1 ELSE 0 END) AS input_rework_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status = 'REWORKED' THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' AND defect_status = 'REWORKED' THEN 1 ELSE 0 END) AS input_rework_mending,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS output_def_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS output_def_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
    signalbit_erp.output_defects_packing a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION
SELECT
		so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in_pck_line,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		count(so_det_id) qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		signalbit_erp.output_rejects_packing
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY so_det_id
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in_pck_line,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		sum(qty) qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		laravel_nds.packing_trf_garment
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		sum(qty) sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		laravel_nds.packing_packing_in
WHERE updated_at < '$tgl_awal'
GROUP BY id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		sum(qty) qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		laravel_nds.packing_packing_in
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		sum(tot_scan) qty_pck_out
		from
		(
		select po, barcode,dest, count(barcode) tot_scan from laravel_nds.packing_packing_out_scan
		where updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
		group by po, barcode, dest
		) a
		inner join laravel_nds.ppic_master_so p on a.po = p.po and a.barcode = p.barcode and a.dest = p.dest
		group by id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		sum(tot_scan) qty_pck_out_awal,
		'0' qty_pck_out
		from
		(
		select po, barcode,dest, count(barcode) tot_scan from laravel_nds.packing_packing_out_scan
		where updated_at < '$tgl_awal'
		group by po, barcode, dest
		) a
		inner join laravel_nds.ppic_master_so p on a.po = p.po and a.barcode = p.barcode and a.dest = p.dest
		group by id_so_det
)	d_rep
inner join signalbit_erp.so_det sd on d_rep.so_det_id = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join signalbit_erp.master_size_new msn on sd.size = msn.size
group by kpno, buyer, styleno, color, size
order by buyer asc, kpno asc, styleno asc, color asc, msn.urutan asc
      ");

            return DataTables::of($data_mut)->toJson();
        }
        return view('packing.packing_rep_packing_mutasi_wip', ['page' => 'dashboard-packing', "subPageGroup" => "packing-report", "subPage" => "packing_rep_packing_mutasi_wip", "containerFluid" => true,]);
    }

    public function export_excel_rep_packing_mutasi_wip(Request $request)
    {
        // return Excel::download(new export_excel_rep_packing_mutasi, 'Laporan_Packing_In.xlsx');
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $data = DB::connection('mysql_sb')->select("SELECT
                    ac.kpno,
                    ms.supplier buyer,
                    ac.styleno,
                    sd.color,
                    sd.size,
                    sd.dest,
					sum(sa_pck_line_awal) - sum(sa_trf_gmt_awal) sa_pck_line_awal,
					sum(qty_in_pck_line) qty_in_pck_line,
					sum(input_rework_sewing) input_rework_sewing,
					sum(input_rework_spotcleaning) input_rework_spotcleaning,
					sum(input_rework_mending) input_rework_mending,
					sum(output_def_sewing) output_def_sewing,
					sum(output_def_spotcleaning) output_def_spotcleaning,
					sum(output_def_mending) output_def_mending,
					sum(qty_reject) qty_reject,
					sum(qty_trf_gmt) qty_trf_gmt,
					sum(sa_pck_line_awal) - sum(sa_trf_gmt_awal) + sum(qty_in_pck_line) - sum(qty_trf_gmt) saldo_akhir_pck_line,
					sum(sa_trf_gmt_awal) - sum(sa_pck_in_awal) sa_trf_gmt,
					sum(qty_trf_gmt) qty_trf_gmt_in,
					sum(qty_pck_in) qty_trf_gmt_out,
					sum(sa_trf_gmt_awal) - sum(sa_pck_in_awal) +  sum(qty_trf_gmt) - sum(qty_pck_in) saldo_akhir_trf_gmt,
					sum(sa_pck_in_awal) - sum(qty_pck_out_awal) sa_pck_in,
					sum(qty_pck_in)	qty_pck_in,
					sum(qty_pck_out) qty_pck_out,
					sum(sa_pck_in_awal) - sum(qty_pck_out_awal) + 	sum(qty_pck_in)	- sum(qty_pck_out) saldo_akhir_packing_central
FROM
(
SELECT
      so_det_id,
			count(so_det_id) sa_pck_line_awal,
			'0' sa_trf_gmt_awal,
			'0' qty_in_pck_line,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' output_def_sewing,
      '0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' qty_trf_gmt,
			'0' sa_pck_in_awal,
			'0' qty_pck_in,
			'0' qty_pck_out_awal,
			'0' qty_pck_out
FROM
     signalbit_erp.output_rfts_packing a
WHERE
     updated_at < '$tgl_awal'
GROUP BY so_det_id
UNION
SELECT
      id_so_det so_det_id,
			'0' sa_pck_line_awal,
			sum(qty) sa_trf_gmt_awal,
			'0' qty_in_pck_line,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' output_def_sewing,
      '0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' qty_trf_gmt,
			'0' sa_pck_in_awal,
			'0' qty_pck_in,
			'0' qty_pck_out_awal,
			'0' qty_pck_out
FROM
     laravel_nds.packing_trf_garment a
WHERE
     updated_at < '$tgl_awal'
GROUP BY id_so_det
UNION
SELECT
			so_det_id,
			'0' sa_pck_line_awal,
			'0' sa_trf_gmt_awal,
			count(so_det_id) qty_in_pck_line,
			'0' input_rework_sewing,
			'0' input_rework_spotcleaning,
			'0' input_rework_mending,
			'0' output_def_sewing,
			'0' output_def_spotcleaning,
			'0' output_def_mending,
			'0' qty_reject,
			'0' qty_trf_gmt,
			'0' sa_pck_in_awal,
			'0' qty_pck_in,
			'0' qty_pck_out_awal,
			'0' qty_pck_out
FROM signalbit_erp.output_rfts_packing a
where
			updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY so_det_id
UNION
SELECT
    so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in_pck_line,
    SUM(CASE WHEN allocation = 'SEWING' AND defect_status = 'REWORKED' THEN 1 ELSE 0 END) AS input_rework_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status = 'REWORKED' THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' AND defect_status = 'REWORKED' THEN 1 ELSE 0 END) AS input_rework_mending,
    SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS output_def_sewing,
    SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS output_def_spotcleaning,
    SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
    signalbit_erp.output_defects_packing a
INNER JOIN
    signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
WHERE
    allocation IN ('SEWING', 'spotcleaning', 'mending') and a.updated_at >= '$tgl_awal' and a.updated_at <= '$tgl_akhir'
GROUP BY
    so_det_id
UNION
SELECT
		so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in_pck_line,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		count(so_det_id) qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		signalbit_erp.output_rejects_packing
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY so_det_id
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in_pck_line,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		sum(qty) qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		laravel_nds.packing_trf_garment
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		sum(qty) sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		laravel_nds.packing_packing_in
WHERE updated_at < '$tgl_awal'
GROUP BY id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		sum(qty) qty_pck_in,
		'0' qty_pck_out_awal,
		'0' qty_pck_out
FROM
		laravel_nds.packing_packing_in
WHERE updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
GROUP BY id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		'0' qty_pck_out_awal,
		sum(tot_scan) qty_pck_out
		from
		(
		select po, barcode,dest, count(barcode) tot_scan from laravel_nds.packing_packing_out_scan
		where updated_at >= '$tgl_awal' and updated_at <= '$tgl_akhir'
		group by po, barcode, dest
		) a
		inner join laravel_nds.ppic_master_so p on a.po = p.po and a.barcode = p.barcode and a.dest = p.dest
		group by id_so_det
UNION
SELECT
			id_so_det so_det_id,
		'0'sa_pck_line_awal,
		'0' sa_trf_gmt_awal,
		'0' qty_in,
		'0' input_rework_sewing,
		'0' input_rework_spotcleaning,
		'0'input_rework_mending,
    '0' output_def_sewing,
    '0' AS output_def_spotcleaning,
    '0' AS output_def_mending,
		'0' qty_reject,
		'0' qty_trf_gmt,
		'0' sa_pck_in_awal,
		'0' qty_pck_in,
		sum(tot_scan) qty_pck_out_awal,
		'0' qty_pck_out
		from
		(
		select po, barcode,dest, count(barcode) tot_scan from laravel_nds.packing_packing_out_scan
		where updated_at < '$tgl_awal'
		group by po, barcode, dest
		) a
		inner join laravel_nds.ppic_master_so p on a.po = p.po and a.barcode = p.barcode and a.dest = p.dest
		group by id_so_det
)	d_rep
inner join signalbit_erp.so_det sd on d_rep.so_det_id = sd.id
inner join signalbit_erp.so so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join signalbit_erp.master_size_new msn on sd.size = msn.size
group by so_det_id
order by buyer asc, kpno asc, styleno asc, color asc, msn.urutan asc

              ");

        return response()->json($data);
    }
}
