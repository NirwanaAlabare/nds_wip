<?php

namespace App\Http\Controllers;

use App\Exports\DC\Export_excel_rep_packing_mutasi as DCExport_excel_rep_packing_mutasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Export_excel_rep_packing_line_sum_range;
use App\Exports\Export_excel_rep_packing_line_sum_buyer;
use App\Exports\Export_excel_rep_packing_mutasi;
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

    // public function packing_rep_packing_mutasi_wip(Request $request)
    // {
    //     ini_set('memory_limit', '2048M');
    //     $tgl_awal = $request->dateFrom;
    //     $tgl_akhir = $request->dateTo;
    //     if ($request->ajax()) {
    //         $data_mut = DB::connection('mysql_sb')->select("WITH m as (
    //             SELECT
    //             ms.supplier buyer,
    //             ac.kpno ws,
    //             ac.styleno,
    //             sd.color,
    //             sd.size,
    //             sd.id id_so_det
    //             from signalbit_erp.act_costing ac
    //             inner join signalbit_erp.so on ac.id = so.id_cost
    //             inner join signalbit_erp.so_det sd on so.id = sd.id_so
    //             inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
    //             where ac.aktif = 'Y' and so.cancel_h = 'N' and sd.cancel = 'N'
    //             ),
    //             saldo_packing_awal as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_awal
    //             from signalbit_erp.output_rfts_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_packing_akhir as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_akhir
    //             from signalbit_erp.output_rfts_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_packing_defect_awal as(
    //                     SELECT
    //                     defect.so_det_id,
    //                     SUM(input_rework_sewing) input_rework_sewing,
    //                     SUM(input_rework_spotcleaning) input_rework_spotcleaning,
    //                     SUM(input_rework_mending) input_rework_mending,
    //                     SUM(defect_sewing) defect_sewing,
    //                     SUM(defect_spotcleaning) defect_spotcleaning,
    //                     SUM(defect_mending) defect_mending
    //                 FROM (
    //                     SELECT
    //                         so_det_id,
    //                         SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
    //                                     '0' as input_rework_sewing,
    //                                     '0' as input_rework_spotcleaning,
    //                                     '0' as input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.created_at >= '2025-07-01 00:00:00' AND a.created_at < '$tgl_awal 00:00:00')
    //                     GROUP BY
    //                         so_det_id
    //                         UNION ALL
    //                     SELECT
    //                         so_det_id,
    //                                     '0' as defect_sewing,
    //                                     '0' as defect_spotcleaning,
    //                                     '0' as defect_mending,
    //                         SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.updated_at >= '2025-07-01 00:00:00' AND a.updated_at < '$tgl_awal 00:00:00')
    //                         AND defect_status IN ('REWORKED','REJECTED')
    //                     GROUP BY
    //                         so_det_id
    //                 ) defect
    //                 GROUP BY
    //                     so_det_id
    //             ),
    //             saldo_packing_defect_akhir as(
    //                 SELECT
    //                     defect.so_det_id,
    //                     SUM(input_rework_sewing) input_rework_sewing,
    //                     SUM(input_rework_spotcleaning) input_rework_spotcleaning,
    //                     SUM(input_rework_mending) input_rework_mending,
    //                     SUM(defect_sewing) defect_sewing,
    //                     SUM(defect_spotcleaning) defect_spotcleaning,
    //                     SUM(defect_mending) defect_mending
    //                 FROM (
    //                     SELECT
    //                         so_det_id,
    //                         SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
    //                                     '0' as input_rework_sewing,
    //                                     '0' as input_rework_spotcleaning,
    //                                     '0' as input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.created_at >= '$tgl_awal 00:00:00' AND a.created_at <= '$tgl_akhir 23:59:59')
    //                     GROUP BY
    //                         so_det_id
    //                         UNION ALL
    //                     SELECT
    //                         so_det_id,
    //                                     '0' as defect_sewing,
    //                                     '0' as defect_spotcleaning,
    //                                     '0' as defect_mending,
    //                         SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.updated_at >= '$tgl_awal 00:00:00' AND a.updated_at <= '$tgl_akhir 23:59:59')
    //                         AND defect_status IN ('REWORKED','REJECTED')
    //                     GROUP BY
    //                         so_det_id
    //                 ) defect
    //                 GROUP BY
    //                     so_det_id
    //             ),
    //             saldo_packing_reject_awal as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_reject_awal
    //             from signalbit_erp.output_rejects_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_packing_reject_akhir as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_reject_akhir
    //             from signalbit_erp.output_rejects_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_adj_awal as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(sa_pck_line) sa_pck_line_awal,
    //             sum(sa_trf_gmt) sa_trf_gmt_awal,
    //             sum(sa_pck_central) sa_pck_central_awal
    //             from laravel_nds.report_output_adj where tgl_adj >= '2025-07-01 00:00:00' and tgl_adj < '$tgl_awal 00:00:00'
    //             group by id_so_det
    //             ),
    //             saldo_adj_akhir as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(sa_pck_line) sa_pck_line_akhir,
    //             sum(sa_trf_gmt) sa_trf_gmt_akhir,
    //             sum(sa_pck_central) sa_pck_central_akhir
    //             from laravel_nds.report_output_adj where tgl_adj >= '$tgl_awal 00:00:00' and tgl_adj <= '$tgl_akhir 23:59:59'
    //             group by id_so_det
    //             ),
    //             saldo_awal_upload as
    //             (
    //             select
    //             so_det_id,
    //             sum(loading) loading,
    //             sum(sewing) sewing,
    //             sum(steam) steam,
    //             sum(out_sew) out_sew,
    //             sum(packing_line) packing_line,
    //             sum(trf_gmt) trf_gmt,
    //             sum(packing_central) packing_central
    //             from laravel_nds.sa_report_output
    //             where tgl_saldo = '2025-07-01'
    //             group by so_det_id
    //             ),
    //             trf_gmt_awal as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_trf_gmt_awal
    //             from laravel_nds.packing_trf_garment
    //             where tgl_trans >= '2025-07-01' and tgl_trans < '$tgl_awal'
    //             group by id_so_det
    //             ),
    //             trf_gmt_akhir as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_trf_gmt_akhir
    //             from laravel_nds.packing_trf_garment
    //             where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
    //             group by id_so_det
    //             ),
    //             packing_in_awal as (
    //             SELECT
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_pck_in_awal
    //             from laravel_nds.packing_packing_in a
    //             WHERE a.tgl_penerimaan >= '2025-07-01' AND a.tgl_penerimaan < '$tgl_awal'
    //             group by id_so_det
    //             ),
    //             packing_in_akhir as (
    //             SELECT
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_pck_in_akhir
    //             from laravel_nds.packing_packing_in a
    //             WHERE a.tgl_penerimaan >= '$tgl_awal' AND a.tgl_penerimaan <= '$tgl_akhir'
    //             group by id_so_det
    //             ),
    //             ppic as (
    //                 SELECT
    //                     po,
    //                     barcode,
    //                     dest,
    //                     CAST(id_so_det AS UNSIGNED) as id_so_det
    //                 FROM laravel_nds.ppic_master_so
    //                 GROUP BY po, barcode, dest, id_so_det
    //             ),
    //             pck_scan_awal as (
    //                 SELECT
    //                     COUNT(a.barcode) AS qty_pck_scan_awal,
    //                     a.po,
    //                     a.barcode,
    //                     a.dest,
    //                     CAST(p.id_so_det AS UNSIGNED) as id_so_det
    //                 FROM laravel_nds.packing_packing_out_scan a
    //                 LEFT JOIN ppic p ON a.po = p.po AND a.barcode = p.barcode AND a.dest = p.dest
    //                 WHERE a.tgl_trans >= '2025-07-01' AND a.tgl_trans < '$tgl_awal'
    //                 GROUP BY p.id_so_det
    //             ),
    //             pck_scan_akhir as (
    //                 SELECT
    //                     COUNT(a.barcode) AS qty_pck_scan_akhir,
    //                     a.po,
    //                     a.barcode,
    //                     a.dest,
    //                     CAST(p.id_so_det AS UNSIGNED) as id_so_det
    //                 FROM laravel_nds.packing_packing_out_scan a
    //                 LEFT JOIN ppic p ON a.po = p.po AND a.barcode = p.barcode AND a.dest = p.dest
    //                 WHERE a.tgl_trans >= '$tgl_awal' AND a.tgl_trans <= '$tgl_akhir'
    //                 GROUP BY  p.id_so_det
    //             )

    //             SELECT
    //             buyer,
    //             ws,
    //             color,
    //             m.size,
    //             styleno,
    //             group_concat(m.id_so_det) id_so_det,
    //             sum(coalesce(sal.packing_line,0)) + sum(coalesce(sawal.qty_pck_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_line_awal,0))
    //             -
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.defect_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.defect_mending,0))
    //             )
    //             +
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_mending,0))
    //             )
    //             -
    //             sum(coalesce(saldo_packing_reject_awal.qty_pck_reject_awal,0)) - sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0))

    //             as packing_line_awal,



    //             sum(coalesce(sakhir.qty_pck_akhir,0)) in_pck_line,

    //             sum(coalesce(saldo_packing_defect_akhir.defect_sewing,0)) defect_sewing_akhir,
    //             sum(coalesce(saldo_packing_defect_akhir.defect_spotcleaning,0)) defect_spotcleaning_akhir,
    //             sum(coalesce(saldo_packing_defect_akhir.defect_mending,0)) defect_mending_akhir,

    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_sewing,0)) input_rework_sewing,
    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_spotcleaning,0)) input_rework_spotcleaning,
    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_mending,0)) input_rework_mending,

    //             sum(coalesce(saldo_packing_reject_akhir.qty_pck_reject_akhir,0)) qty_pck_reject,
    //             sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) out_pck_line,
    //             sum(coalesce(saldo_adj_akhir.sa_pck_line_akhir,0)) adj_pck_line_akhir,

    //             sum(coalesce(sal.packing_line,0)) + sum(coalesce(sawal.qty_pck_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_line_awal,0))
    //             -
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.defect_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.defect_mending,0))
    //             )
    //             +
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_mending,0))
    //             )
    //             -
    //             sum(coalesce(saldo_packing_reject_awal.qty_pck_reject_awal,0)) - sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0))
    //              + sum(coalesce(sakhir.qty_pck_akhir,0)) -
    //             (
    //             sum(coalesce(saldo_packing_defect_akhir.defect_sewing,0)) + sum(coalesce(saldo_packing_defect_akhir.defect_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_akhir.defect_mending,0))
    //             )
    //             +
    //             (
    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_sewing,0)) + sum(coalesce(saldo_packing_defect_akhir.input_rework_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_akhir.input_rework_mending,0))
    //             )
    //             -
    //             sum(coalesce(saldo_packing_reject_akhir.qty_pck_reject_akhir,0)) - sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) + sum(coalesce(saldo_adj_akhir.sa_pck_line_akhir,0)) as saldo_akhir_pck_line,
    //             sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0)) + sum(coalesce(saldo_adj_awal.sa_trf_gmt_awal,0)) - sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(sal.trf_gmt,0)) saldo_awal_trf_garment,
    //             sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) in_trf_garment,
    //             sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) out_trf_garment,
    //             sum(coalesce(saldo_adj_akhir.sa_trf_gmt_akhir,0)) adj_trf_garment,
    //             sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0)) + sum(coalesce(saldo_adj_awal.sa_trf_gmt_awal,0)) - sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(sal.trf_gmt,0))
    //              + sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) - sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) + sum(coalesce(saldo_adj_akhir.sa_trf_gmt_akhir,0)) as saldo_akhir_trf_garment,


    //              sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_central_awal,0)) - sum(coalesce(pck_scan_awal.qty_pck_scan_awal,0)) + sum(coalesce(sal.packing_central,0)) saldo_awal_packing_central,
    //             sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) in_packing_central,
    //             sum(coalesce(pck_scan_akhir.qty_pck_scan_akhir,0)) out_packing_central,
    //             sum(coalesce(saldo_adj_akhir.sa_pck_central_akhir,0)) adj_packing_central,
    //             sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_central_awal,0)) - sum(coalesce(pck_scan_awal.qty_pck_scan_awal,0)) + sum(coalesce(sal.packing_central,0))
    //             + sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) - sum(coalesce(pck_scan_akhir.qty_pck_scan_akhir,0)) + sum(coalesce(saldo_adj_akhir.sa_pck_central_akhir,0)) saldo_akhir_pck_central

    //             from m
    //             left join saldo_packing_awal sawal on m.id_so_det = sawal.so_det_id
    //             left join saldo_packing_akhir sakhir on m.id_so_det = sakhir.so_det_id
    //             left join saldo_awal_upload sal on m.id_so_det = sal.so_det_id
    //             left join saldo_packing_defect_awal on m.id_so_det = saldo_packing_defect_awal.so_det_id
    //             left join saldo_packing_defect_akhir on m.id_so_det = saldo_packing_defect_akhir.so_det_id
    //             left join saldo_packing_reject_awal on m.id_so_det = saldo_packing_reject_awal.so_det_id
    //             left join saldo_packing_reject_akhir on m.id_so_det = saldo_packing_reject_akhir.so_det_id
    //             left join trf_gmt_awal on m.id_so_det = trf_gmt_awal.id_so_det
    //             left join trf_gmt_akhir on m.id_so_det = trf_gmt_akhir.id_so_det
    //             left join packing_in_awal on m.id_so_det = packing_in_awal.id_so_det
    //             left join packing_in_akhir on m.id_so_det = packing_in_akhir.id_so_det
    //             left join pck_scan_awal on m.id_so_det = pck_scan_awal.id_so_det
    //             left join pck_scan_akhir on m.id_so_det = pck_scan_akhir.id_so_det
    //             left join saldo_adj_awal on m.id_so_det = saldo_adj_awal.id_so_det
    //             left join saldo_adj_akhir on m.id_so_det = saldo_adj_akhir.id_so_det
    //             left join laravel_nds.master_size_new msn on m.size = msn.size
    //             group by
    //             buyer,
    //             ws,
    //             color,
    //             m.size
    //             HAVING
    //             (
    //             packing_line_awal != 0 OR
    //             in_pck_line != 0 OR
    //             defect_sewing_akhir != 0 OR
    //             defect_spotcleaning_akhir != 0 OR
    //             defect_mending_akhir != 0 OR
    //             input_rework_sewing != 0 OR
    //             input_rework_spotcleaning != 0 OR
    //             input_rework_mending != 0 OR
    //             qty_pck_reject != 0 OR
    //             out_pck_line != 0 OR
    //             adj_pck_line_akhir != 0 OR
    //             saldo_akhir_pck_line != 0 OR
    //             saldo_awal_trf_garment != 0 OR
    //             in_trf_garment != 0 OR
    //             out_trf_garment != 0 OR
    //             adj_trf_garment != 0 OR
    //             saldo_akhir_trf_garment != 0 OR
    //             saldo_awal_packing_central != 0 OR
    //             in_packing_central != 0 OR
    //             out_packing_central != 0 OR
    //             adj_packing_central != 0 OR
    //             saldo_akhir_pck_central != 0
    //             )
    //             ORDER BY
    //             buyer asc,
    //             ws asc,
    //             color asc,
    //             msn.urutan asc
    //   ");

    //         return DataTables::of($data_mut)->toJson();
    //     }
    //     return view('packing.packing_rep_packing_mutasi_wip', ['page' => 'dashboard-packing', "subPageGroup" => "packing-report", "subPage" => "packing_rep_packing_mutasi_wip", "containerFluid" => true,]);
    // }

    public function packing_rep_packing_mutasi_wip(Request $request)
    {
        ini_set('memory_limit', '2048M');
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;

        $tanggal_saldo_awal = '2026-01-01';
        // if ($request->ajax()) {
        //     $data_mut = DB::select("WITH trx_union AS (
        //                                 SELECT
        //                                     so_det_id,
        //                                     SUM(type)                AS pl_saldo_awal,
        //                                     0 AS pl_rft,
        //                                     0 AS pl_reject,
        //                                     0 AS pl_keluar,

        //                                     0 AS tg_saldo_awal,
        //                                     0 AS tg_masuk,
        //                                     0 AS tg_keluar,

        //                                     0 AS pc_saldo_awal,
        //                                     0 AS pc_terima,
        //                                     0 AS pc_keluar
        //                                 FROM signalbit_erp.output_rfts_packing_po
        //                                 WHERE so_det_id IS NOT NULL
        //                                 AND updated_at BETWEEN CONCAT(''".$tanggal_saldo_awal."' 00:00:00')
        //                                                     AND CONCAT(''".$tgl_awal."' 23:59:59')
        //                                 GROUP BY so_det_id

        //                                 UNION ALL

        //                                 SELECT
        //                                     so_det_id,
        //                                     0,
        //                                     SUM(type = 'RFT'),
        //                                     SUM(type = 'REJECT'),
        //                                     0,

        //                                     0,0,0,
        //                                     0,0,0
        //                                 FROM signalbit_erp.output_rfts_packing_po
        //                                 WHERE so_det_id IS NOT NULL
        //                                 AND updated_at BETWEEN CONCAT(''".$tgl_awal."' 00:00:00')
        //                                                     AND CONCAT(''".$tgl_akhir."' 23:59:59')
        //                                 GROUP BY so_det_id

        //                                 UNION ALL

        //                                 SELECT
        //                                     id_so_det,
        //                                     0,0,0,0,

        //                                     SUM(qty),
        //                                     0,
        //                                     0,

        //                                     0,0,0
        //                                 FROM laravel_nds.packing_trf_garment
        //                                 WHERE id_so_det IS NOT NULL
        //                                 AND tgl_trans BETWEEN BETWEEN CONCAT(''".$tanggal_saldo_awal."' 00:00:00')
        //                                                     AND CONCAT(''".$tgl_awal."' 23:59:59')
        //                                 GROUP BY id_so_det

        //                                 UNION ALL


        //                                 SELECT
        //                                     tg.id_so_det,
        //                                     0,0,0,0,
        //                                     0,
        //                                     SUM(tg.qty),
        //                                     COALESCE(SUM(pi.qty),0),
        //                                     0,0,0
        //                                 FROM laravel_nds.packing_trf_garment tg
        //                                 LEFT JOIN laravel_nds.packing_packing_in pi
        //                                     ON pi.id_trf_garment = tg.id
        //                                     AND pi.tgl_penerimaan BETWEEN CONCAT(''".$tgl_awal."' 00:00:00')
        //                                                     AND CONCAT(''".$tgl_akhir."' 23:59:59')
        //                                 WHERE tg.id_so_det IS NOT NULL
        //                                 AND tg.tgl_trans BETWEEN CONCAT(@tgl,' 00:00:00')
        //                                                     AND CONCAT(@tgl_akhir,' 23:59:59')
        //                                 GROUP BY tg.id_so_det

        //                                 UNION ALL

        //                                 SELECT
        //                                     id_so_det,
        //                                     0,0,0,0,
        //                                     0,0,0,
        //                                     SUM(masuk),
        //                                     0,
        //                                     0
        //                                 FROM (
        //                                     SELECT id_so_det, SUM(qty) masuk, 0 keluar
        //                                     FROM laravel_nds.packing_packing_in
        //                                     WHERE id_so_det IS NOT NULL
        //                                     AND tgl_penerimaan < @tgl
        //                                     GROUP BY id_so_det

        //                                     UNION ALL

        //                                     SELECT id_so_det, 0, COUNT(*)
        //                                     FROM laravel_nds.packing_packing_out_scan
        //                                     WHERE id_so_det IS NOT NULL
        //                                     AND tgl_trans BETWEEN CONCAT(@tgl_awal,' 00:00:00')
        //                                                         AND CONCAT(@tgl,' 23:59:59')
        //                                     GROUP BY id_so_det
        //                                 ) x
        //                                 GROUP BY id_so_det

        //                                 UNION ALL

        //                                 SELECT
        //                                     id_so_det,
        //                                     0,0,0,0,

        //                                     0,0,0,

        //                                     0,
        //                                     SUM(masuk),
        //                                     SUM(keluar)
        //                                 FROM (
        //                                     SELECT id_so_det, SUM(qty) masuk, 0 keluar
        //                                     FROM laravel_nds.packing_packing_in
        //                                     WHERE id_so_det IS NOT NULL
        //                                     AND tgl_penerimaan BETWEEN CONCAT(''".$tgl_awal."' 00:00:00')
        //                                                     AND CONCAT(''".$tgl_akhir."' 23:59:59')
        //                                     GROUP BY id_so_det

        //                                     UNION ALL

        //                                     SELECT id_so_det, 0, COUNT(*)
        //                                     FROM laravel_nds.packing_packing_out_scan
        //                                     WHERE id_so_det IS NOT NULL
        //                                     AND tgl_trans BETWEEN CONCAT(@tgl,' 00:00:00')
        //                                                         AND CONCAT(@tgl_akhir,' 23:59:59')
        //                                     GROUP BY id_so_det
        //                                 ) x
        //                                 GROUP BY id_so_det
        //                             )

        //                             SELECT
        //                                 msw.id_so_det,
        //                                 msw.ws,
        //                                 msw.color,
        //                                 msw.styleno AS style,
        //                                 msw.size,
        //                                 msw.buyer,


        //                                 SUM(pl_saldo_awal) AS pl_saldo_awal,
        //                                 SUM(pl_rft)        AS pl_rft,
        //                                 SUM(pl_reject)    AS pl_reject,
        //                                     SUM(tg_masuk)    AS pl_keluar,
        //                                 SUM(pl_saldo_awal + pl_rft + pl_reject - pl_keluar) AS pl_saldo_akhir,


        //                                 SUM(tg_saldo_awal) AS tg_saldo_awal,
        //                                 SUM(tg_masuk)      AS tg_masuk,
        //                                 SUM(tg_keluar)     AS tg_keluar,
        //                                 SUM(tg_saldo_awal + tg_masuk - tg_keluar) AS tg_saldo_akhir,


        //                                 SUM(pc_saldo_awal) AS pc_saldo_awal,
        //                                 SUM(pc_terima)     AS pc_terima,
        //                                 SUM(pc_keluar)     AS pc_packing_scan,
        //                                 SUM(pc_saldo_awal + pc_terima - pc_keluar) AS pc_saldo_akhir

        //                             FROM trx_union t
        //                             JOIN master_sb_ws msw ON msw.id_so_det = t.so_det_id
        //                             GROUP BY
        //                                 msw.id_so_det,
        //                                 msw.ws,
        //                                 msw.color,
        //                                 msw.styleno,
        //                                 msw.size,
        //                                 msw.buyer
        //                             ORDER BY
        //                                 msw.ws,
        //                                 msw.color,
        //                                 msw.size;

        //     ");

        //     return DataTables::of($data_mut)->toJson();
        // }

        $tgl_saldo_akhir = date(
            'Y-m-d',
            strtotime($tgl_awal . ' -1 day')
        );

        if($request->ajax()){

            $data_mut = DB::select("
                WITH trx_union AS (

                    /* ================= PACKING LINE SALDO AWAL ================= */
                    SELECT
                        so_det_id,
                        COUNT(type) AS pl_saldo_awal,
                        0 pl_rft, 0 pl_reject, 0 pl_keluar,
                        0 tg_saldo_awal, 0 tg_masuk, 0 tg_keluar,
                        0 pc_saldo_awal, 0 pc_terima, 0 pc_keluar
                    FROM signalbit_erp.output_rfts_packing_po
                    WHERE so_det_id IS NOT NULL
                   AND updated_at BETWEEN '{$tanggal_saldo_awal} 00:00:00'
                    AND '{$tgl_saldo_akhir} 23:59:59'

                    GROUP BY so_det_id

                    UNION ALL

                    /* ================= PACKING LINE HARIAN ================= */
                    SELECT
                        so_det_id,
                        0,
                        SUM(type = 'RFT'),
                        SUM(type = 'REJECT'),
                        0,
                        0,0,0,
                        0,0,0
                    FROM signalbit_erp.output_rfts_packing_po
                    WHERE so_det_id IS NOT NULL
                    AND updated_at BETWEEN '{$tgl_awal} 00:00:00'
                                        AND '{$tgl_akhir} 23:59:59'
                    GROUP BY so_det_id

                    UNION ALL

                    /* ================= TRANSFER GARMENT SALDO AWAL ================= */
                    SELECT
                        id_so_det,
                        0,0,0,0,
                        SUM(qty),
                        0,
                        0,
                        0,0,0
                    FROM laravel_nds.packing_trf_garment
                    WHERE id_so_det IS NOT NULL
                    AND tgl_trans BETWEEN '{$tanggal_saldo_awal} 00:00:00'
                                AND '{$tgl_saldo_akhir} 23:59:59'

                    GROUP BY id_so_det

                    UNION ALL

                    /* ================= TRANSFER GARMENT HARIAN ================= */
                    SELECT
                        tg.id_so_det,
                        0,0,0,0,
                        0,
                        SUM(tg.qty),
                        COALESCE(SUM(pi.qty),0),
                        0,0,0
                    FROM laravel_nds.packing_trf_garment tg
                    LEFT JOIN laravel_nds.packing_packing_in pi
                        ON pi.id_trf_garment = tg.id
                        AND pi.tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00'
                                                AND '{$tgl_akhir} 23:59:59'
                    WHERE tg.id_so_det IS NOT NULL
                    AND tg.tgl_trans BETWEEN '{$tgl_awal} 00:00:00'
                                        AND '{$tgl_akhir} 23:59:59'
                    GROUP BY tg.id_so_det

                    UNION ALL

                    /* ================= PACKING CENTRAL SALDO AWAL ================= */
                    SELECT
                        id_so_det,
                        0,0,0,0,
                        0,0,0,
                        SUM(masuk) - SUM(keluar),
                        0,
                        0
                    FROM (
                        SELECT id_so_det, SUM(qty) masuk, 0 keluar
                        FROM laravel_nds.packing_packing_in
                        WHERE id_so_det IS NOT NULL
                        AND tgl_penerimaan BETWEEN '{$tanggal_saldo_awal} 00:00:00'
                                        AND '{$tgl_saldo_akhir} 23:59:59'

                        GROUP BY id_so_det
                          /* =================
                        UNION ALL

                        SELECT id_so_det, 0, COUNT(*)
                        FROM laravel_nds.packing_packing_out_scan
                        WHERE id_so_det IS NOT NULL
                        AND tgl_trans BETWEEN '{$tanggal_saldo_awal} 00:00:00'
                                            AND '{$tgl_awal} 23:59:59'
                        GROUP BY id_so_det
                         ================= */
                    ) x
                    GROUP BY id_so_det

                    UNION ALL

                    /* ================= PACKING CENTRAL HARIAN ================= */
                    SELECT
                        id_so_det,
                        0,0,0,0,
                        0,0,0,
                        0,
                        SUM(masuk),
                        SUM(keluar)
                    FROM (
                        SELECT id_so_det, SUM(qty) masuk, 0 keluar
                        FROM laravel_nds.packing_packing_in
                        WHERE id_so_det IS NOT NULL
                        AND tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00'
                                                AND '{$tgl_akhir} 23:59:59'
                        GROUP BY id_so_det

                        UNION ALL

                        SELECT id_so_det, 0, COUNT(*)
                        FROM laravel_nds.packing_packing_out_scan
                        WHERE id_so_det IS NOT NULL
                        AND tgl_trans BETWEEN '{$tgl_awal} 00:00:00'
                                            AND '{$tgl_akhir} 23:59:59'
                        GROUP BY id_so_det
                    ) x
                    GROUP BY id_so_det
                )

                /* ================= SELECT BEGIN ================= */
                SELECT
                    msw.id_so_det,
                    msw.ws,
                    msw.color,
                    msw.styleno AS style,
                    msw.size,
                    msw.buyer,

                    SUM(pl_saldo_awal) AS pl_saldo_awal,
                    SUM(pl_rft)        AS pl_rft,
                    SUM(pl_reject)    AS pl_reject,
                    SUM(tg_masuk)    AS pl_keluar,
                    SUM(pl_saldo_awal + pl_rft + pl_reject - tg_masuk) AS pl_saldo_akhir,

                    SUM(tg_saldo_awal) AS tg_saldo_awal,
                    SUM(tg_masuk)      AS tg_masuk,
                    SUM(tg_keluar)     AS tg_keluar,
                    SUM(tg_saldo_awal + tg_masuk - tg_keluar) AS tg_saldo_akhir,

                    SUM(pc_saldo_awal) AS pc_saldo_awal,
                    SUM(pc_terima)     AS pc_terima,
                    SUM(pc_keluar)     AS pc_packing_scan,
                    SUM(pc_saldo_awal + pc_terima - pc_keluar) AS pc_saldo_akhir

                FROM trx_union t
                JOIN master_sb_ws msw ON msw.id_so_det = t.so_det_id
                left join master_size_new msn on msw.size = msn.size
                GROUP BY
                    msw.id_so_det,
                    msw.ws,
                    msw.color,
                    msw.styleno,
                    msw.size,
                    msw.buyer
                HAVING
                    COALESCE(SUM(pl_rft),0)     <> 0
                OR COALESCE(SUM(pl_reject),0) <> 0
                OR COALESCE(SUM(tg_masuk),0)  <> 0
                OR COALESCE(SUM(tg_keluar),0) <> 0
                OR COALESCE(SUM(pc_terima),0) <> 0
                OR COALESCE(SUM(pc_keluar),0) <> 0
                ORDER BY
                    msw.ws ASC,
                    msw.color ASC,
                    msw.buyer ASC,
                    msn.urutan ASC
                ");

            return DataTables::of($data_mut)->toJson();

        }

        return view('packing.packing_rep_packing_mutasi_wip', ['page' => 'dashboard-packing', "subPageGroup" => "packing-report", "subPage" => "packing_rep_packing_mutasi_wip", "containerFluid" => true,]);
    }

    // public function export_excel_rep_packing_mutasi_wip(Request $request)
    // {
    //     // return Excel::download(new export_excel_rep_packing_mutasi, 'Laporan_Packing_In.xlsx');
    //     $tgl_awal = $request->dateFrom;
    //     $tgl_akhir = $request->dateTo;
    //     $data = DB::connection('mysql_sb')->select("WITH m as (
    //             SELECT
    //             ms.supplier buyer,
    //             ac.kpno ws,
    //             ac.styleno,
    //             sd.color,
    //             sd.size,
    //             sd.id id_so_det
    //             from signalbit_erp.act_costing ac
    //             inner join signalbit_erp.so on ac.id = so.id_cost
    //             inner join signalbit_erp.so_det sd on so.id = sd.id_so
    //             inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
    //             where ac.aktif = 'Y' and so.cancel_h = 'N' and sd.cancel = 'N'
    //             ),
    //             saldo_packing_awal as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_awal
    //             from signalbit_erp.output_rfts_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_packing_akhir as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_akhir
    //             from signalbit_erp.output_rfts_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_packing_defect_awal as(
    //                     SELECT
    //                     defect.so_det_id,
    //                     SUM(input_rework_sewing) input_rework_sewing,
    //                     SUM(input_rework_spotcleaning) input_rework_spotcleaning,
    //                     SUM(input_rework_mending) input_rework_mending,
    //                     SUM(defect_sewing) defect_sewing,
    //                     SUM(defect_spotcleaning) defect_spotcleaning,
    //                     SUM(defect_mending) defect_mending
    //                 FROM (
    //                     SELECT
    //                         so_det_id,
    //                         SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
    //                                     '0' as input_rework_sewing,
    //                                     '0' as input_rework_spotcleaning,
    //                                     '0' as input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.created_at >= '2025-07-01 00:00:00' AND a.created_at < '$tgl_awal 00:00:00')
    //                     GROUP BY
    //                         so_det_id
    //                         UNION ALL
    //                     SELECT
    //                         so_det_id,
    //                                     '0' as defect_sewing,
    //                                     '0' as defect_spotcleaning,
    //                                     '0' as defect_mending,
    //                         SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.updated_at >= '2025-07-01 00:00:00' AND a.updated_at < '$tgl_awal 00:00:00')
    //                         AND defect_status IN ('REWORKED','REJECTED')
    //                     GROUP BY
    //                         so_det_id
    //                 ) defect
    //                 GROUP BY
    //                     so_det_id
    //             ),
    //             saldo_packing_defect_akhir as(
    //                 SELECT
    //                     defect.so_det_id,
    //                     SUM(input_rework_sewing) input_rework_sewing,
    //                     SUM(input_rework_spotcleaning) input_rework_spotcleaning,
    //                     SUM(input_rework_mending) input_rework_mending,
    //                     SUM(defect_sewing) defect_sewing,
    //                     SUM(defect_spotcleaning) defect_spotcleaning,
    //                     SUM(defect_mending) defect_mending
    //                 FROM (
    //                     SELECT
    //                         so_det_id,
    //                         SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
    //                                     '0' as input_rework_sewing,
    //                                     '0' as input_rework_spotcleaning,
    //                                     '0' as input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.created_at >= '$tgl_awal 00:00:00' AND a.created_at <= '$tgl_akhir 23:59:59')
    //                     GROUP BY
    //                         so_det_id
    //                         UNION ALL
    //                     SELECT
    //                         so_det_id,
    //                                     '0' as defect_sewing,
    //                                     '0' as defect_spotcleaning,
    //                                     '0' as defect_mending,
    //                         SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
    //                         SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
    //                         SUM(CASE WHEN allocation = 'mending' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending
    //                     FROM signalbit_erp.output_defects_packing a
    //                     INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
    //                     WHERE
    //                         allocation IN ('SEWING', 'spotcleaning', 'mending')
    //                         AND (a.updated_at >= '$tgl_awal 00:00:00' AND a.updated_at <= '$tgl_akhir 23:59:59')
    //                         AND defect_status IN ('REWORKED','REJECTED')
    //                     GROUP BY
    //                         so_det_id
    //                 ) defect
    //                 GROUP BY
    //                     so_det_id
    //             ),
    //             saldo_packing_reject_awal as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_reject_awal
    //             from signalbit_erp.output_rejects_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '2025-07-01 00:00:00' and updated_at < '$tgl_awal 00:00:00' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_packing_reject_akhir as (
    //             select
    //             so_det_id,
    //             COUNT(so_det_id) qty_pck_reject_akhir
    //             from signalbit_erp.output_rejects_packing a
    //             inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
    //             where updated_at >= '$tgl_awal 00:00:00' and updated_at <= '$tgl_akhir 23:59:59' and mp.cancel = 'N'
    //             group by so_det_id
    //             ),
    //             saldo_adj_awal as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(sa_pck_line) sa_pck_line_awal,
    //             sum(sa_trf_gmt) sa_trf_gmt_awal,
    //             sum(sa_pck_central) sa_pck_central_awal
    //             from laravel_nds.report_output_adj where tgl_adj >= '2025-07-01 00:00:00' and tgl_adj < '$tgl_awal 00:00:00'
    //             group by id_so_det
    //             ),
    //             saldo_adj_akhir as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(sa_pck_line) sa_pck_line_akhir,
    //             sum(sa_trf_gmt) sa_trf_gmt_akhir,
    //             sum(sa_pck_central) sa_pck_central_akhir
    //             from laravel_nds.report_output_adj where tgl_adj >= '$tgl_awal 00:00:00' and tgl_adj <= '$tgl_akhir 23:59:59'
    //             group by id_so_det
    //             ),
    //             saldo_awal_upload as
    //             (
    //             select
    //             so_det_id,
    //             sum(loading) loading,
    //             sum(sewing) sewing,
    //             sum(steam) steam,
    //             sum(out_sew) out_sew,
    //             sum(packing_line) packing_line,
    //             sum(trf_gmt) trf_gmt,
    //             sum(packing_central) packing_central
    //             from laravel_nds.sa_report_output
    //             where tgl_saldo = '2025-07-01'
    //             group by so_det_id
    //             ),
    //             trf_gmt_awal as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_trf_gmt_awal
    //             from laravel_nds.packing_trf_garment
    //             where tgl_trans >= '2025-07-01' and tgl_trans < '$tgl_awal'
    //             group by id_so_det
    //             ),
    //             trf_gmt_akhir as (
    //             select
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_trf_gmt_akhir
    //             from laravel_nds.packing_trf_garment
    //             where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
    //             group by id_so_det
    //             ),
    //             packing_in_awal as (
    //             SELECT
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_pck_in_awal
    //             from laravel_nds.packing_packing_in a
    //             WHERE a.tgl_penerimaan >= '2025-07-01' AND a.tgl_penerimaan < '$tgl_awal'
    //             group by id_so_det
    //             ),
    //             packing_in_akhir as (
    //             SELECT
    //             CAST(id_so_det AS UNSIGNED) as id_so_det,
    //             sum(qty) qty_pck_in_akhir
    //             from laravel_nds.packing_packing_in a
    //             WHERE a.tgl_penerimaan >= '$tgl_awal' AND a.tgl_penerimaan <= '$tgl_akhir'
    //             group by id_so_det
    //             ),
    //             ppic as (
    //                 SELECT
    //                     po,
    //                     barcode,
    //                     dest,
    //                     CAST(id_so_det AS UNSIGNED) as id_so_det
    //                 FROM laravel_nds.ppic_master_so
    //                 GROUP BY po, barcode, dest, id_so_det
    //             ),
    //             pck_scan_awal as (
    //                 SELECT
    //                     COUNT(a.barcode) AS qty_pck_scan_awal,
    //                     a.po,
    //                     a.barcode,
    //                     a.dest,
    //                     CAST(p.id_so_det AS UNSIGNED) as id_so_det
    //                 FROM laravel_nds.packing_packing_out_scan a
    //                 LEFT JOIN ppic p ON a.po = p.po AND a.barcode = p.barcode AND a.dest = p.dest
    //                 WHERE a.tgl_trans >= '2025-07-01' AND a.tgl_trans < '$tgl_awal'
    //                 GROUP BY p.id_so_det
    //             ),
    //             pck_scan_akhir as (
    //                 SELECT
    //                     COUNT(a.barcode) AS qty_pck_scan_akhir,
    //                     a.po,
    //                     a.barcode,
    //                     a.dest,
    //                     CAST(p.id_so_det AS UNSIGNED) as id_so_det
    //                 FROM laravel_nds.packing_packing_out_scan a
    //                 LEFT JOIN ppic p ON a.po = p.po AND a.barcode = p.barcode AND a.dest = p.dest
    //                 WHERE a.tgl_trans >= '$tgl_awal' AND a.tgl_trans <= '$tgl_akhir'
    //                 GROUP BY  p.id_so_det
    //             )

    //             SELECT
    //             buyer,
    //             ws,
    //             color,
    //             m.size,
    //             styleno,
    //             group_concat(m.id_so_det) id_so_det,
    //             sum(coalesce(sal.packing_line,0)) + sum(coalesce(sawal.qty_pck_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_line_awal,0))
    //             -
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.defect_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.defect_mending,0))
    //             )
    //             +
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_mending,0))
    //             )
    //             -
    //             sum(coalesce(saldo_packing_reject_awal.qty_pck_reject_awal,0)) - sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0))

    //             as packing_line_awal,



    //             sum(coalesce(sakhir.qty_pck_akhir,0)) in_pck_line,

    //             sum(coalesce(saldo_packing_defect_akhir.defect_sewing,0)) defect_sewing_akhir,
    //             sum(coalesce(saldo_packing_defect_akhir.defect_spotcleaning,0)) defect_spotcleaning_akhir,
    //             sum(coalesce(saldo_packing_defect_akhir.defect_mending,0)) defect_mending_akhir,

    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_sewing,0)) input_rework_sewing,
    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_spotcleaning,0)) input_rework_spotcleaning,
    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_mending,0)) input_rework_mending,

    //             sum(coalesce(saldo_packing_reject_akhir.qty_pck_reject_akhir,0)) qty_pck_reject,
    //             sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) out_pck_line,
    //             sum(coalesce(saldo_adj_akhir.sa_pck_line_akhir,0)) adj_pck_line_akhir,

    //             sum(coalesce(sal.packing_line,0)) + sum(coalesce(sawal.qty_pck_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_line_awal,0))
    //             -
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.defect_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.defect_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.defect_mending,0))
    //             )
    //             +
    //             (
    //             sum(coalesce(saldo_packing_defect_awal.input_rework_sewing,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_awal.input_rework_mending,0))
    //             )
    //             -
    //             sum(coalesce(saldo_packing_reject_awal.qty_pck_reject_awal,0)) - sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0))
    //             + sum(coalesce(sakhir.qty_pck_akhir,0)) -
    //             (
    //             sum(coalesce(saldo_packing_defect_akhir.defect_sewing,0)) + sum(coalesce(saldo_packing_defect_akhir.defect_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_akhir.defect_mending,0))
    //             )
    //             +
    //             (
    //             sum(coalesce(saldo_packing_defect_akhir.input_rework_sewing,0)) + sum(coalesce(saldo_packing_defect_akhir.input_rework_spotcleaning,0)) + sum(coalesce(saldo_packing_defect_akhir.input_rework_mending,0))
    //             )
    //             -
    //             sum(coalesce(saldo_packing_reject_akhir.qty_pck_reject_akhir,0)) - sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) + sum(coalesce(saldo_adj_akhir.sa_pck_line_akhir,0)) as saldo_akhir_pck_line,
    //             sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0)) + sum(coalesce(saldo_adj_awal.sa_trf_gmt_awal,0)) - sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(sal.trf_gmt,0)) saldo_awal_trf_garment,
    //             sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) in_trf_garment,
    //             sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) out_trf_garment,
    //             sum(coalesce(saldo_adj_akhir.sa_trf_gmt_akhir,0)) adj_trf_garment,
    //             sum(coalesce(trf_gmt_awal.qty_trf_gmt_awal,0)) + sum(coalesce(saldo_adj_awal.sa_trf_gmt_awal,0)) - sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(sal.trf_gmt,0))
    //             + sum(coalesce(trf_gmt_akhir.qty_trf_gmt_akhir,0)) - sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) + sum(coalesce(saldo_adj_akhir.sa_trf_gmt_akhir,0)) as saldo_akhir_trf_garment,


    //             sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_central_awal,0)) - sum(coalesce(pck_scan_awal.qty_pck_scan_awal,0)) + sum(coalesce(sal.packing_central,0)) saldo_awal_packing_central,
    //             sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) in_packing_central,
    //             sum(coalesce(pck_scan_akhir.qty_pck_scan_akhir,0)) out_packing_central,
    //             sum(coalesce(saldo_adj_akhir.sa_pck_central_akhir,0)) adj_packing_central,
    //             sum(coalesce(packing_in_awal.qty_pck_in_awal,0)) + sum(coalesce(saldo_adj_awal.sa_pck_central_awal,0)) - sum(coalesce(pck_scan_awal.qty_pck_scan_awal,0)) + sum(coalesce(sal.packing_central,0))
    //             + sum(coalesce(packing_in_akhir.qty_pck_in_akhir,0)) - sum(coalesce(pck_scan_akhir.qty_pck_scan_akhir,0)) + sum(coalesce(saldo_adj_akhir.sa_pck_central_akhir,0)) saldo_akhir_pck_central

    //             from m
    //             left join saldo_packing_awal sawal on m.id_so_det = sawal.so_det_id
    //             left join saldo_packing_akhir sakhir on m.id_so_det = sakhir.so_det_id
    //             left join saldo_awal_upload sal on m.id_so_det = sal.so_det_id
    //             left join saldo_packing_defect_awal on m.id_so_det = saldo_packing_defect_awal.so_det_id
    //             left join saldo_packing_defect_akhir on m.id_so_det = saldo_packing_defect_akhir.so_det_id
    //             left join saldo_packing_reject_awal on m.id_so_det = saldo_packing_reject_awal.so_det_id
    //             left join saldo_packing_reject_akhir on m.id_so_det = saldo_packing_reject_akhir.so_det_id
    //             left join trf_gmt_awal on m.id_so_det = trf_gmt_awal.id_so_det
    //             left join trf_gmt_akhir on m.id_so_det = trf_gmt_akhir.id_so_det
    //             left join packing_in_awal on m.id_so_det = packing_in_awal.id_so_det
    //             left join packing_in_akhir on m.id_so_det = packing_in_akhir.id_so_det
    //             left join pck_scan_awal on m.id_so_det = pck_scan_awal.id_so_det
    //             left join pck_scan_akhir on m.id_so_det = pck_scan_akhir.id_so_det
    //             left join saldo_adj_awal on m.id_so_det = saldo_adj_awal.id_so_det
    //             left join saldo_adj_akhir on m.id_so_det = saldo_adj_akhir.id_so_det
    //             left join laravel_nds.master_size_new msn on m.size = msn.size
    //             group by
    //             buyer,
    //             ws,
    //             color,
    //             m.size
    //             HAVING
    //             (
    //             packing_line_awal != 0 OR
    //             in_pck_line != 0 OR
    //             defect_sewing_akhir != 0 OR
    //             defect_spotcleaning_akhir != 0 OR
    //             defect_mending_akhir != 0 OR
    //             input_rework_sewing != 0 OR
    //             input_rework_spotcleaning != 0 OR
    //             input_rework_mending != 0 OR
    //             qty_pck_reject != 0 OR
    //             out_pck_line != 0 OR
    //             adj_pck_line_akhir != 0 OR
    //             saldo_akhir_pck_line != 0 OR
    //             saldo_awal_trf_garment != 0 OR
    //             in_trf_garment != 0 OR
    //             out_trf_garment != 0 OR
    //             adj_trf_garment != 0 OR
    //             saldo_akhir_trf_garment != 0 OR
    //             saldo_awal_packing_central != 0 OR
    //             in_packing_central != 0 OR
    //             out_packing_central != 0 OR
    //             adj_packing_central != 0 OR
    //             saldo_akhir_pck_central != 0
    //             )
    //             ORDER BY
    //             buyer asc,
    //             ws asc,
    //             color asc,
    //             msn.urutan asc
    //                         ");

    //     return response()->json($data);
    // }

    public function export_excel_rep_packing_mutasi_wip(Request $request)
    {
        // return Excel::download(new export_excel_rep_packing_mutasi, 'Laporan_Packing_In.xlsx');
        $tgl_awal = $request->from;
        $tgl_akhir = $request->to;

        return Excel::download(
                                new Export_excel_rep_packing_mutasi(
                                    $tgl_awal,
                                    $tgl_akhir,
                                ),
                                'Laporan Mutasi Packing (WIP) '.$tgl_awal.' - '.$tgl_akhir.'.xlsx'
                            );

    }
}
