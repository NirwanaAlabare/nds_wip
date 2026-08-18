<?php

namespace App\Http\Controllers\Sewing;

use \avadim\FastExcelLaravel\Excel as FastExcel;
use App\Exports\export_excel_mut_output;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Yajra\DataTables\Facades\DataTables;



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


    private function buildQueryMutasiOutput($start_date, $end_date, $buyer)
    {
        $start_date = $start_date;
        $end_date = $end_date;
        $prev_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $tgl_saldo = '2026-03-01';
        $buyer = $buyer;

        if (!empty($buyer)) {
            $filter = "WHERE buyer = '$buyer'";
            $filter_subcont = " AND buyer = '$buyer'";
            $filter_loading = " AND buyer = '$buyer'";
            $filter_terima_gudang = " AND Supplier = '$buyer'";
        } else {
            $filter = '';
            $filter_subcont = '';
            $filter_loading = '';
            $filter_terima_gudang = '';
        }

        $query = "WITH
            saldo_loading as (
                WITH loading_line_qty as (
                    SELECT
                        ll.tanggal_loading,
                        s.id_qr_stocker,
                        pd.id AS part_detail_id,
                        s.so_det_id,
                        s.size,
                        COALESCE(
                            MIN(ll.qty) OVER (
                                PARTITION BY
                                    COALESCE(p_com.panel, p.panel),
                                    s.form_cut_id,
                                    s.form_reject_id,
                                    s.form_piece_id,
                                    s.so_det_id,
                                    s.size,
                                    s.group_stocker,
                                    s.ratio,
                                    s.stocker_reject
                            ),
                            ll.qty
                        ) AS loading_qty
                    FROM laravel_nds.loading_line ll
                    JOIN laravel_nds.stocker_input s ON s.id = ll.stocker_id
                    LEFT JOIN laravel_nds.part_detail pd ON pd.id = s.part_detail_id
                    LEFT JOIN laravel_nds.part p ON p.id = pd.part_id
                    LEFT JOIN laravel_nds.part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                    LEFT JOIN laravel_nds.part p_com ON p_com.id = pd_com.part_id
                    WHERE
                        ll.tanggal_loading BETWEEN '$start_date' AND '$end_date'
                        AND COALESCE(s.cancel, 'n') != 'y'
                        AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                )

                select tanggal_loading, so_det_id id_so_det, MIN(total) qty_loading FROM (
                    select tanggal_loading, panel, ws, color, style, size, so_det_id, MIN(total) total from (
                        select
                            tanggal_loading,
                            GROUP_CONCAT(loading_line_qty.id_qr_stocker),
                            COALESCE(p_com.panel, p.panel) panel,
                            mp.nama_part,
                            msb.ws,
                            msb.styleno style,
                            msb.color,
                            msb.size,
                            so_det_id,
                            part_detail_id,
                            SUM(loading_qty) total
                        from loading_line_qty
                        LEFT JOIN laravel_nds.part_detail pd ON pd.id = loading_line_qty.part_detail_id
                        LEFT JOIN laravel_nds.part p ON p.id = pd.part_id
                        LEFT JOIN laravel_nds.part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                        LEFT JOIN laravel_nds.part p_com ON p_com.id = pd_com.part_id
                        LEFT JOIN laravel_nds.master_part mp on mp.id = pd.master_part_id
                        LEFT JOIN laravel_nds.master_sb_ws msb on msb.id_so_det = loading_line_qty.so_det_id
                        group by
                            so_det_id, mp.nama_part
                    ) loading
                    group by
                        so_det_id, panel
                ) loading
                group by
                    so_det_id
            ),
            saldo_loading_awal as (
                WITH loading_line_qty as (
                    SELECT
                        ll.tanggal_loading,
                        s.id_qr_stocker,
                        pd.id AS part_detail_id,
                        s.so_det_id,
                        s.size,
                        COALESCE(
                            MIN(ll.qty) OVER (
                                PARTITION BY
                                    COALESCE(p_com.panel, p.panel),
                                    s.form_cut_id,
                                    s.form_reject_id,
                                    s.form_piece_id,
                                    s.so_det_id,
                                    s.size,
                                    s.group_stocker,
                                    s.ratio,
                                    s.stocker_reject
                            ),
                            ll.qty
                        ) AS loading_qty
                    FROM laravel_nds.loading_line ll
                    JOIN laravel_nds.stocker_input s ON s.id = ll.stocker_id
                    LEFT JOIN laravel_nds.part_detail pd ON pd.id = s.part_detail_id
                    LEFT JOIN laravel_nds.part p ON p.id = pd.part_id
                    LEFT JOIN laravel_nds.part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                    LEFT JOIN laravel_nds.part p_com ON p_com.id = pd_com.part_id
                    WHERE
                        ll.tanggal_loading >= '2026-06-01' AND ll.tanggal_loading <= '$prev_date'
                        AND COALESCE(s.cancel, 'n') != 'y'
                        AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                )

                select tanggal_loading, so_det_id id_so_det, MIN(total) qty_loading FROM (
                    select tanggal_loading, panel, ws, color, style, size, so_det_id, MIN(total) total from (
                        select
                            tanggal_loading,
                            GROUP_CONCAT(loading_line_qty.id_qr_stocker),
                            COALESCE(p_com.panel, p.panel) panel,
                            mp.nama_part,
                            msb.ws,
                            msb.styleno style,
                            msb.color,
                            msb.size,
                            so_det_id,
                            part_detail_id,
                            SUM(loading_qty) total
                        from loading_line_qty
                        LEFT JOIN laravel_nds.part_detail pd ON pd.id = loading_line_qty.part_detail_id
                        LEFT JOIN laravel_nds.part p ON p.id = pd.part_id
                        LEFT JOIN laravel_nds.part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                        LEFT JOIN laravel_nds.part p_com ON p_com.id = pd_com.part_id
                        LEFT JOIN laravel_nds.master_part mp on mp.id = pd.master_part_id
                        LEFT JOIN laravel_nds.master_sb_ws msb on msb.id_so_det = loading_line_qty.so_det_id
                        group by
                            so_det_id, mp.nama_part
                    ) loading
                    group by
                        so_det_id, panel
                ) loading
                group by
                    so_det_id
            ),
            saldo_sewing as (
                            SELECT
                            so_det_id,
                            DATE(updated_at) tgl_sewing,
                            COUNT(*) qty_sew
                            from signalbit_erp.output_rfts a
                            inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
                            where updated_at >= '$start_date 00:00:00' and updated_at <= '$end_date 23:59:59' and mp.cancel = 'N'
                            group by so_det_id, DATE(updated_at)
            ),
            saldo_sewing_defect as (
                SELECT
                                    tgl_defect,
                                    so_det_id,
                                    SUM(input_rework_sewing) input_rework_sewing,
                                    SUM(input_rework_spotcleaning) input_rework_spotcleaning,
                                    SUM(input_rework_mending) input_rework_mending,
                                    SUM(defect_sewing) defect_sewing,
                                    SUM(defect_spotcleaning) defect_spotcleaning,
                                    SUM(defect_mending) defect_mending
                                FROM (
                                    SELECT
                                        so_det_id,
                                        date(a.created_at) tgl_defect,
                                        SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
                                        SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
                                        SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
                                                    '0' as input_rework_sewing,
                                                    '0' as input_rework_spotcleaning,
                                                    '0' as input_rework_mending
                                    FROM signalbit_erp.output_defects a
                                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                                    WHERE
                                        allocation IN ('SEWING', 'spotcleaning', 'mending')
                                        AND (a.created_at >= '$start_date 00:00:00') AND (a.created_at <= '$end_date 23:59:59')
                                    GROUP BY
                                        so_det_id,
                                        date(a.created_at)
                                        UNION ALL
                                    SELECT
                                        so_det_id,
                                                    date(a.updated_at) tgl_defect,
                                                    '0' as defect_sewing,
                                                    '0' as defect_spotcleaning,
                                                    '0' as defect_mending,
                                        SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
                                        SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
                                        SUM(CASE WHEN allocation = 'mending' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending
                                    FROM signalbit_erp.output_defects a
                                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                                    WHERE
                                        allocation IN ('SEWING', 'spotcleaning', 'mending')
                                        AND (a.updated_at >= '$start_date 00:00:00') AND (a.updated_at <= '$end_date 23:59:59')
                                        AND defect_status IN ('REWORKED','REJECTED')
                                    GROUP BY
                                        so_det_id,
                                        date(a.updated_at)
                                ) defect
                                GROUP BY
                                    so_det_id,
                                    tgl_defect
            ),
            saldo_sewing_reject as (
                            select
                            so_det_id,
                            date(updated_at) tgl_reject,
                            COUNT(*) qty_sew_reject
                            from signalbit_erp.output_rejects a
                            inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
                            where updated_at >= '$start_date 00:00:00' and updated_at <= '$end_date 23:59:59' and mp.cancel = 'N'
                            group by so_det_id, date(updated_at)
            ),
            saldo_finishing as (
                select
                so_det_id,
                date(updated_at) tgl_finishing,
                COUNT(*) qty_finishing
                from signalbit_erp.output_rfts_packing a
                inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
                where updated_at >= '$start_date 00:00:00' and updated_at <= '$end_date 23:59:59' and mp.cancel = 'N'
                group by so_det_id, date(updated_at)
            ),
            saldo_finishing_defect as(
                                SELECT
                                    defect.so_det_id,
                                    tgl_defect,
                                    SUM(input_rework_sewing) input_rework_sewing,
                                    SUM(input_rework_spotcleaning) input_rework_spotcleaning,
                                    SUM(input_rework_mending) input_rework_mending,
                                    SUM(defect_sewing) defect_sewing,
                                    SUM(defect_spotcleaning) defect_spotcleaning,
                                    SUM(defect_mending) defect_mending
                                FROM (
                                    SELECT
                                        so_det_id,
                                                                    DATE(a.created_at) as tgl_defect,
                                        SUM(CASE WHEN allocation = 'SEWING' THEN 1 ELSE 0 END) AS defect_sewing,
                                        SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning,
                                        SUM(CASE WHEN allocation = 'mending' THEN 1 ELSE 0 END) AS defect_mending,
                                                    '0' as input_rework_sewing,
                                                    '0' as input_rework_spotcleaning,
                                                    '0' as input_rework_mending
                                    FROM signalbit_erp.output_defects_packing a
                                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                                    WHERE
                                        allocation IN ('SEWING', 'spotcleaning', 'mending')
                                        AND (a.created_at >= '$start_date 00:00:00') AND (a.created_at <= '$end_date 23:59:59')
                                    GROUP BY
                                        so_det_id,
                                        date(a.created_at)
                                        UNION ALL
                                    SELECT
                                        so_det_id,
                                        DATE(a.updated_at) as tgl_defect,
                                                    '0' as defect_sewing,
                                                    '0' as defect_spotcleaning,
                                                    '0' as defect_mending,
                                        SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing,
                                        SUM(CASE WHEN allocation = 'spotcleaning' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_spotcleaning,
                                        SUM(CASE WHEN allocation = 'mending' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_mending
                                    FROM signalbit_erp.output_defects_packing a
                                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                                    WHERE
                                        allocation IN ('SEWING', 'spotcleaning', 'mending')
                                        AND (a.updated_at >= '$start_date 00:00:00') AND (a.updated_at <= '$end_date 23:59:59')
                                        AND defect_status IN ('REWORKED','REJECTED')
                                    GROUP BY
                                        so_det_id,
                                        date(a.updated_at)
                                ) defect
                                GROUP BY
                                    so_det_id,
                                    tgl_defect
            ),
            saldo_finishing_reject as (
                            select
                            so_det_id,
                            date(updated_at) tgl_fin_reject,
                            COUNT(*) qty_fin_reject
                            from signalbit_erp.output_rejects_packing a
                            inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
                            where updated_at >= '$start_date 00:00:00' and updated_at <= '$end_date 23:59:59' and mp.cancel = 'N'
                            group by so_det_id, date(updated_at)
            ),
            secondary_proses as (
                                            SELECT
                                            os.so_det_id,
                                            tgl_proses,
                                            SUM(COALESCE(os.total_in, 0))        AS total_in,
                                            SUM(COALESCE(os.rft, 0))             AS rft,
                                            SUM(COALESCE(os.defect, 0))          AS defect,
                                            SUM(COALESCE(os.rework, 0))          AS rework,
                                            SUM(COALESCE(os.reject, 0))          AS reject,
                                            SUM(COALESCE(os.reject_defect, 0))   AS reject_defect
                                            FROM (
                                            /* ================= TOTAL IN ================= */
                                            SELECT
                                                    r.so_det_id,
                                                    date(osi.updated_at) tgl_proses,
                                                    COUNT(osi.id) AS total_in,
                                                    NULL AS rft,
                                                    NULL AS defect,
                                                    NULL AS rework,
                                                    NULL AS reject,
                                                    NULL AS reject_defect
                                            FROM output_secondary_in osi
                                            LEFT JOIN output_rfts r ON r.id = osi.rft_id
                                            WHERE osi.updated_at >= '$start_date 00:00:00' and osi.updated_at <= '$end_date 23:59:59'
                                            GROUP BY r.so_det_id, date(osi.updated_at)

                                            UNION ALL

                                            /* ================= RFT & REWORK ================= */
                                            SELECT
                                                    r.so_det_id,
                                                    date(oso.updated_at) tgl_proses,
                                                    NULL AS total_in,
                                                    SUM(CASE WHEN oso.status = 'rft'    THEN 1 ELSE 0 END) AS rft,
                                                    NULL AS defect,
                                                    SUM(CASE WHEN oso.status = 'rework' THEN 1 ELSE 0 END) AS rework,
                                                    NULL AS reject,
                                                    NULL AS reject_defect
                                            FROM output_secondary_out oso
                                            LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                                            LEFT JOIN output_rfts r ON r.id = osi.rft_id
                                            WHERE oso.status NOT IN ('defect', 'reject')
                                                AND oso.updated_at >= '$start_date 00:00:00' and oso.updated_at <= '$end_date 23:59:59'
                                            GROUP BY r.so_det_id, date(oso.updated_at)

                                            UNION ALL

                                            /* ================= DEFECT ================= */
                                            SELECT
                                                    r.so_det_id,
                                                    date(osod.created_at) tgl_proses,
                                                    NULL AS total_in,
                                                    NULL AS rft,
                                                    COUNT(osod.id) AS defect,
                                                    NULL AS rework,
                                                    NULL AS reject,
                                                    NULL AS reject_defect
                                            FROM output_secondary_out_defect osod
                                            LEFT JOIN output_secondary_out oso ON oso.id = osod.secondary_out_id
                                            LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                                            LEFT JOIN output_rfts r ON r.id = osi.rft_id
                                            WHERE
                                            osod.created_at >= '$start_date 00:00:00' and osod.created_at <= '$end_date 23:59:59'
                                            GROUP BY r.so_det_id, date(osod.created_at)

                                            UNION ALL

                                            /* ================= REJECT ================= */
                                            SELECT
                                                    r.so_det_id,
                                                    date(osor.updated_at) tgl_proses,
                                                    NULL AS total_in,
                                                    NULL AS rft,
                                                    NULL AS defect,
                                                    NULL AS rework,
                                                    SUM(CASE WHEN osor.status = 'mati'   THEN 1 ELSE 0 END) AS reject,
                                                    SUM(CASE WHEN osor.status = 'defect' THEN 1 ELSE 0 END) AS reject_defect
                                            FROM output_secondary_out_reject osor
                                            LEFT JOIN output_secondary_out oso ON oso.id = osor.secondary_out_id
                                            LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                                            LEFT JOIN output_rfts r ON r.id = osi.rft_id
                                            WHERE osor.updated_at >= '$start_date 00:00:00' and osor.updated_at <= '$end_date 23:59:59'
                                            GROUP BY r.so_det_id, date(osor.updated_at)
                                    ) os
                                    GROUP BY os.so_det_id, tgl_proses
            ),
            secondary_proses_per_proses AS (
                SELECT
                    os.so_det_id,
                    os.secondary_id,
                    tgl_proses,
                    SUM(COALESCE(os.total_in_before, 0)) AS total_in_before,
                    SUM(COALESCE(os.total_in, 0)) AS total_in,
                    SUM(COALESCE(os.rft_before, 0)) AS rft_before,
                    SUM(COALESCE(os.rft, 0)) AS rft,
                    SUM(COALESCE(os.defect_before, 0)) AS defect_before,
                    SUM(COALESCE(os.defect, 0)) AS defect,
                    SUM(COALESCE(os.rework_before, 0)) AS rework_before,
                    SUM(COALESCE(os.rework, 0)) AS rework,
                    SUM(COALESCE(os.reject_before, 0)) AS reject_before,
                    SUM(COALESCE(os.reject, 0)) AS reject,
                    SUM(COALESCE(os.reject_defect_before, 0)) AS reject_defect_before,
                    SUM(COALESCE(os.reject_defect, 0)) AS reject_defect
                FROM (
                    /* TOTAL IN */
                    SELECT
                        r.so_det_id,
                        osi.secondary_id,
                        DATE(osi.updated_at) AS tgl_proses,
                        SUM(CASE WHEN DATE(osi.updated_at) >= '2026-03-01' AND DATE(osi.updated_at) <= '$prev_date' THEN 1 ELSE 0 END) AS total_in_before,
                        SUM(CASE WHEN DATE(osi.updated_at) >= '$start_date' THEN 1 ELSE 0 END) AS total_in,
                        0 AS rft_before, 0 AS rft,
                        0 AS defect_before, 0 AS defect,
                        0 AS rework_before, 0 AS rework,
                        0 AS reject_before, 0 AS reject,
                        0 AS reject_defect_before, 0 AS reject_defect
                    FROM output_secondary_in osi
                    LEFT JOIN output_rfts r ON r.id = osi.rft_id
                    WHERE osi.updated_at <= '$end_date 23:59:59'
                    GROUP BY r.so_det_id, osi.secondary_id, DATE(osi.updated_at)

                    UNION ALL

                    /* RFT & REWORK */
                    SELECT
                        r.so_det_id,
                        osi.secondary_id,
                        DATE(oso.updated_at) AS tgl_proses,
                        0 AS total_in_before, 0 AS total_in,
                        SUM(CASE WHEN DATE(oso.updated_at) >= '2026-03-01' AND DATE(oso.updated_at) <= '$prev_date' AND oso.status = 'rft' THEN 1 ELSE 0 END) AS rft_before,
                        SUM(CASE WHEN DATE(oso.updated_at) >= '$start_date' AND oso.status = 'rft' THEN 1 ELSE 0 END) AS rft,
                        0 AS defect_before, 0 AS defect,
                        SUM(CASE WHEN DATE(oso.updated_at) >= '2026-03-01' AND DATE(oso.updated_at) <= '$prev_date' AND oso.status = 'rework' THEN 1 ELSE 0 END) AS rework_before,
                        SUM(CASE WHEN DATE(oso.updated_at) >= '$start_date' AND oso.status = 'rework' THEN 1 ELSE 0 END) AS rework,
                        0 AS reject_before, 0 AS reject,
                        0 AS reject_defect_before, 0 AS reject_defect
                    FROM output_secondary_out oso
                    LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                    LEFT JOIN output_rfts r ON r.id = osi.rft_id
                    WHERE oso.status NOT IN ('defect', 'reject')
                        AND oso.updated_at <= '$end_date 23:59:59'
                    GROUP BY r.so_det_id, osi.secondary_id, DATE(oso.updated_at)

                    UNION ALL

                    /* DEFECT */
                    SELECT
                        r.so_det_id,
                        osi.secondary_id,
                        DATE(osod.created_at) AS tgl_proses,
                        0 AS total_in_before, 0 AS total_in,
                        0 AS rft_before, 0 AS rft,
                        SUM(CASE WHEN DATE(osod.created_at) >= '2026-03-01' AND DATE(osod.created_at) <= '$prev_date' THEN 1 ELSE 0 END) AS defect_before,
                        SUM(CASE WHEN DATE(osod.created_at) >= '$start_date' THEN 1 ELSE 0 END) AS defect,
                        0 AS rework_before, 0 AS rework,
                        0 AS reject_before, 0 AS reject,
                        0 AS reject_defect_before, 0 AS reject_defect
                    FROM output_secondary_out_defect osod
                    LEFT JOIN output_secondary_out oso ON oso.id = osod.secondary_out_id
                    LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                    LEFT JOIN output_rfts r ON r.id = osi.rft_id
                    WHERE osod.created_at <= '$end_date 23:59:59'
                    GROUP BY r.so_det_id, osi.secondary_id, DATE(osod.created_at)

                    UNION ALL

                    /* REJECT */
                    SELECT
                        r.so_det_id,
                        osi.secondary_id,
                        DATE(osor.updated_at) AS tgl_proses,
                        0 AS total_in_before, 0 AS total_in,
                        0 AS rft_before, 0 AS rft,
                        0 AS defect_before, 0 AS defect,
                        0 AS rework_before, 0 AS rework,
                        SUM(CASE WHEN DATE(osor.updated_at) >= '2026-03-01' AND DATE(osor.updated_at) <= '$prev_date' AND osor.status = 'mati' THEN 1 ELSE 0 END) AS reject_before,
                        SUM(CASE WHEN DATE(osor.updated_at) >= '$start_date' AND osor.status = 'mati' THEN 1 ELSE 0 END) AS reject,
                        SUM(CASE WHEN DATE(osor.updated_at) >= '2026-03-01' AND DATE(osor.updated_at) <= '$prev_date' AND osor.status = 'defect' THEN 1 ELSE 0 END) AS reject_defect_before,
                        SUM(CASE WHEN DATE(osor.updated_at) >= '$start_date' AND osor.status = 'defect' THEN 1 ELSE 0 END) AS reject_defect
                    FROM output_secondary_out_reject osor
                    LEFT JOIN output_secondary_out oso ON oso.id = osor.secondary_out_id
                    LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                    LEFT JOIN output_rfts r ON r.id = osi.rft_id
                    WHERE osor.updated_at <= '$end_date 23:59:59'
                    GROUP BY r.so_det_id, osi.secondary_id, DATE(osor.updated_at)
                ) os
                GROUP BY os.so_det_id, os.secondary_id, tgl_proses
            ),
            qc_reject as (
                                            SELECT
                            so_det_id,
                                            date(a.created_at) tgl_reject,
                            COUNT(*) qty_reject_in
                            from signalbit_erp.output_reject_in a
                            inner join signalbit_erp.master_plan mp on a.master_plan_id = mp.id
                            where a.created_at >= '$start_date 00:00:00' and a.created_at <= '$end_date 23:59:59' and mp.cancel = 'N'
                            group by so_det_id, date(a.created_at)
            ),
            qc_reject_out as (
                                            SELECT
                                            so_det_id,
                                            date(a.created_at) tgl_reject,
                                            COUNT(CASE WHEN b.status = 'rejected' THEN 1 END) AS qty_rejected,
                                            COUNT(CASE WHEN b.status = 'reworked' THEN 1 END) AS qty_reworked
                                            from output_reject_out_detail a
                                            inner join output_reject_in b on a.reject_in_id = b.id
                                            inner join signalbit_erp.master_plan mp on b.master_plan_id = mp.id
                                            where a.created_at >= '$start_date 00:00:00' and a.created_at <= '$end_date 23:59:59' and mp.cancel = 'N'
                                            group by so_det_id, date(a.created_at)
            ),
            qty_transit_terima_sewing_before AS (
                SELECT
                    so_det_id,
                    date(updated_at) tgl_reject,
                    COUNT(*) AS qty_transit_terima_sewing_before
                FROM signalbit_erp.output_rejects a
                INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
                WHERE updated_at >= '2026-07-01 00:00:00' AND updated_at < '$start_date 00:00:00' AND mp.cancel = 'N'
                GROUP BY so_det_id, date(updated_at)
            ),
            qty_transit_terima_qc_finishing_before AS (
                SELECT
                    so_det_id,
                    DATE(updated_at) AS tgl_fin_reject,
                    COUNT(*) AS qty_transit_terima_qc_finishing_before
                FROM signalbit_erp.output_rejects_packing a
                INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
                WHERE updated_at >= '2026-07-01 00:00:00' AND updated_at < '$start_date 00:00:00' AND mp.cancel = 'N'
                GROUP BY so_det_id, DATE(updated_at)
            ),
            qty_transit_terima_finishing_before AS (
                SELECT
                    r.so_det_id,
                    DATE(osor.updated_at) AS tgl_proses,
                    COUNT(*) AS qty_transit_terima_finishing_before
                FROM output_secondary_out_reject osor
                LEFT JOIN output_secondary_out oso ON oso.id = osor.secondary_out_id
                LEFT JOIN output_secondary_in osi ON osi.id = oso.secondary_in_id
                LEFT JOIN output_rfts r ON r.id = osi.rft_id
                WHERE osor.updated_at >= '2026-07-01 00:00:00' AND osor.updated_at < '$start_date 00:00:00' AND osor.status = 'mati'
                GROUP BY r.so_det_id, DATE(osor.updated_at)
            ),
            qty_transit_keluar_qc_reject_before AS (
                SELECT
                    so_det_id,
                    DATE(a.created_at) AS tgl_reject,
                    COUNT(*) AS qty_transit_keluar_qc_reject_before
                FROM signalbit_erp.output_reject_in a
                INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
                WHERE a.created_at >= '2026-07-01 00:00:00' AND a.created_at < '$start_date 00:00:00' AND mp.cancel = 'N'
                GROUP BY so_det_id, DATE(a.created_at)
            ),

            saldo_sew as(
                SELECT
                    mut_sew.id_so_det,
                    SUM(qty_loading) AS qty_loading,
                    SUM(qty_sewing) AS qty_sewing,
                    SUM(input_rework_sewing) AS input_rework_sewing,
                    SUM(input_rework_spotcleaning) AS input_rework_spotcleaning,
                    SUM(input_rework_mending) AS input_rework_mending,
                    SUM(defect_sewing) AS defect_sewing,
                    SUM(defect_spotcleaning) AS defect_spotcleaning,
                    SUM(defect_mending) AS defect_mending,
                    SUM(qty_sew_reject) AS qty_sew_reject,
                    SUM(qty_finishing) AS qty_finishing,
                    SUM(input_rework_sewing_f) AS input_rework_sewing_f,
                    SUM(input_rework_spotcleaning_f) AS input_rework_spotcleaning_f,
                    SUM(input_rework_mending_f) AS input_rework_mending_f,
                    SUM(defect_sewing_f) AS defect_sewing_f,
                    SUM(defect_spotcleaning_f) AS defect_spotcleaning_f,
                    SUM(defect_mending_f) AS defect_mending_f,
                    SUM(qty_fin_reject) AS qty_fin_reject,
                    SUM(total_in_sp) AS total_in_sp,
                    SUM(rft_sp) AS rft_sp,
                    SUM(defect_sp) AS defect_sp,
                    SUM(rework_sp) AS rework_sp,
                    SUM(reject_sp) AS reject_sp,
                    SUM(reject_defect_sp) AS reject_defect_sp,
                    SUM(qty_reject_in) AS qty_reject_in,
                    SUM(qty_rejected) AS qty_rejected,
                    SUM(qty_reworked) AS qty_reworked,
                    SUM(qty_transit_terima_sewing_before) AS qty_transit_terima_sewing_before,
                    SUM(qty_transit_terima_qc_finishing_before) AS qty_transit_terima_qc_finishing_before,
                    SUM(qty_transit_terima_finishing_before) AS qty_transit_terima_finishing_before,
                    SUM(qty_transit_keluar_qc_reject_before) AS qty_transit_keluar_qc_reject_before,

                    SUM(saldo_awal_sp_pasang_kancing) AS saldo_awal_sp_pasang_kancing,
                    SUM(saldo_awal_sp_bartack) AS saldo_awal_sp_bartack,
                    SUM(saldo_awal_sp_heatseal) AS saldo_awal_sp_heatseal,
                    SUM(saldo_awal_sp_snap) AS saldo_awal_sp_snap,
                    SUM(saldo_awal_sp_embro) AS saldo_awal_sp_embro,

                    SUM(total_in_sp_pasang_kancing) AS total_in_sp_pasang_kancing,
                    SUM(rft_sp_pasang_kancing) AS rft_sp_pasang_kancing,
                    SUM(defect_sp_pasang_kancing) AS defect_sp_pasang_kancing,
                    SUM(rework_sp_pasang_kancing) AS rework_sp_pasang_kancing,
                    SUM(reject_sp_pasang_kancing) AS reject_sp_pasang_kancing,
                    SUM(total_in_sp_bartack) AS total_in_sp_bartack,
                    SUM(rft_sp_bartack) AS rft_sp_bartack,
                    SUM(defect_sp_bartack) AS defect_sp_bartack,
                    SUM(rework_sp_bartack) AS rework_sp_bartack,
                    SUM(reject_sp_bartack) AS reject_sp_bartack,
                    SUM(total_in_sp_heatseal) AS total_in_sp_heatseal,
                    SUM(rft_sp_heatseal) AS rft_sp_heatseal,
                    SUM(defect_sp_heatseal) AS defect_sp_heatseal,
                    SUM(rework_sp_heatseal) AS rework_sp_heatseal,
                    SUM(reject_sp_heatseal) AS reject_sp_heatseal,
                    SUM(total_in_sp_snap) AS total_in_sp_snap,
                    SUM(rft_sp_snap) AS rft_sp_snap,
                    SUM(defect_sp_snap) AS defect_sp_snap,
                    SUM(rework_sp_snap) AS rework_sp_snap,
                    SUM(reject_sp_snap) AS reject_sp_snap,
                    SUM(total_in_sp_embro) AS total_in_sp_embro,
                    SUM(rft_sp_embro) AS rft_sp_embro,
                    SUM(defect_sp_embro) AS defect_sp_embro,
                    SUM(rework_sp_embro) AS rework_sp_embro,
                    SUM(reject_sp_embro) AS reject_sp_embro
                FROM
                (
                    SELECT
                    tanggal_loading, id_so_det, qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_loading
                    UNION ALL
                    SELECT
                    tgl_sewing, so_det_id, 0 AS qty_loading, qty_sew AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_sewing
                    UNION ALL
                    SELECT
                    tgl_defect, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, input_rework_sewing, input_rework_spotcleaning, input_rework_mending,
                    defect_sewing, defect_spotcleaning, defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_sewing_defect
                    UNION ALL
                    SELECT
                    tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_sewing_reject
                    UNION ALL
                    SELECT
                    tgl_finishing, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject,  qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS qty_fin_reject,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_finishing
                    UNION ALL
                    SELECT
                    tgl_defect, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    input_rework_sewing AS input_rework_sewing_f, input_rework_spotcleaning AS input_rework_spotcleaning_f, input_rework_mending AS input_rework_mending_f,
                    defect_sewing AS defect_sewing_f, defect_spotcleaning AS defect_spotcleaning_f, defect_mending AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_finishing_defect
                    UNION ALL
                    SELECT
                    tgl_fin_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    qty_fin_reject AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM saldo_finishing_reject
                    UNION ALL
                    SELECT
                    tgl_proses, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    total_in AS total_in_sp, rft AS rft_sp, defect AS defect_sp, rework AS rework_sp, reject AS reject_sp, reject_defect AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM secondary_proses
                    UNION ALL
                    SELECT
                    tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    qty_reject_in AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM qc_reject
                    UNION ALL
                    SELECT
                    tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, qty_rejected AS qty_rejected, qty_reworked AS qty_reworked,
                    0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM qc_reject_out
                    UNION ALL
                    SELECT
                    tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM qty_transit_terima_sewing_before
                    UNION ALL
                    SELECT
                    tgl_fin_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 AS qty_transit_terima_sewing_before, qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM qty_transit_terima_qc_finishing_before
                    UNION ALL
                    SELECT
                    tgl_proses, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 AS qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM qty_transit_terima_finishing_before
                    UNION ALL
                    SELECT
                    tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 AS qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, qty_transit_keluar_qc_reject_before,
                    0 AS saldo_awal_sp_pasang_kancing, 0 AS saldo_awal_sp_bartack, 0 AS saldo_awal_sp_heatseal, 0 AS saldo_awal_sp_snap, 0 AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                    FROM qty_transit_keluar_qc_reject_before
                    UNION ALL
                    SELECT
                    tgl_proses, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
                    0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 qty_sew_reject, 0 AS qty_finishing,
                    0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
                    0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
                    0 AS qty_fin_reject,
                    0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
                    0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked,
                    0 AS qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,

                    (
                        CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN total_in_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rework_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN reject_defect_before ELSE 0 END
                        - CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN defect_before ELSE 0 END
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN reject_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN reject_defect_before ELSE 0 END
                        )
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rft_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rework_before ELSE 0 END
                        )
                    ) AS saldo_awal_sp_pasang_kancing,
                    (
                        CASE WHEN output_secondary_master.secondary = 'Bartack' THEN total_in_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rework_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Bartack' THEN reject_defect_before ELSE 0 END
                        - CASE WHEN output_secondary_master.secondary = 'Bartack' THEN defect_before ELSE 0 END
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Bartack' THEN reject_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Bartack' THEN reject_defect_before ELSE 0 END
                        )
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rft_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rework_before ELSE 0 END
                        )
                    ) AS saldo_awal_sp_bartack,
                    (
                        CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN total_in_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rework_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN reject_defect_before ELSE 0 END
                        - CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN defect_before ELSE 0 END
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN reject_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN reject_defect_before ELSE 0 END
                        )
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rft_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rework_before ELSE 0 END
                        )
                    ) AS saldo_awal_sp_heatseal,
                    (
                        CASE WHEN output_secondary_master.secondary = 'Snap' THEN total_in_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Snap' THEN rework_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Snap' THEN reject_defect_before ELSE 0 END
                        - CASE WHEN output_secondary_master.secondary = 'Snap' THEN defect_before ELSE 0 END
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Snap' THEN reject_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Snap' THEN reject_defect_before ELSE 0 END
                        )
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Snap' THEN rft_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Snap' THEN rework_before ELSE 0 END
                        )
                    ) AS saldo_awal_sp_snap,
                    (
                        CASE WHEN output_secondary_master.secondary = 'Embro' THEN total_in_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Embro' THEN rework_before ELSE 0 END
                        + CASE WHEN output_secondary_master.secondary = 'Embro' THEN reject_defect_before ELSE 0 END
                        - CASE WHEN output_secondary_master.secondary = 'Embro' THEN defect_before ELSE 0 END
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Embro' THEN reject_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Embro' THEN reject_defect_before ELSE 0 END
                        )
                        - (
                            CASE WHEN output_secondary_master.secondary = 'Embro' THEN rft_before ELSE 0 END
                            + CASE WHEN output_secondary_master.secondary = 'Embro' THEN rework_before ELSE 0 END
                        )
                    ) AS saldo_awal_sp_embro,
                    -- (
                    --     CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN total_in_before ELSE 0 END
                    --     + CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rework_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN defect_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN reject_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rft_before ELSE 0 END
                    -- ) AS saldo_awal_sp_pasang_kancing,
                    -- (
                    --     CASE WHEN output_secondary_master.secondary = 'Bartack' THEN total_in_before ELSE 0 END
                    --     + CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rework_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Bartack' THEN defect_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Bartack' THEN reject_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rft_before ELSE 0 END
                    -- ) AS saldo_awal_sp_bartack,
                    -- (
                    --     CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN total_in_before ELSE 0 END
                    --     + CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rework_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN defect_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN reject_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rft_before ELSE 0 END
                    -- ) AS saldo_awal_sp_heatseal,
                    -- (
                    --     CASE WHEN output_secondary_master.secondary = 'Snap' THEN total_in_before ELSE 0 END
                    --     + CASE WHEN output_secondary_master.secondary = 'Snap' THEN rework_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Snap' THEN defect_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Snap' THEN reject_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Snap' THEN rft_before ELSE 0 END
                    -- ) AS saldo_awal_sp_snap,
                    -- (
                    --     CASE WHEN output_secondary_master.secondary = 'Embro' THEN total_in_before ELSE 0 END
                    --     + CASE WHEN output_secondary_master.secondary = 'Embro' THEN rework_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Embro' THEN defect_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Embro' THEN reject_before ELSE 0 END
                    --     - CASE WHEN output_secondary_master.secondary = 'Embro' THEN rft_before ELSE 0 END
                    -- ) AS saldo_awal_sp_embro,

                    CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN total_in ELSE 0 END AS total_in_sp_pasang_kancing,
                    CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rft ELSE 0 END AS rft_sp_pasang_kancing,
                    CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN defect ELSE 0 END AS defect_sp_pasang_kancing,
                    CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN rework ELSE 0 END AS rework_sp_pasang_kancing,
                    CASE WHEN output_secondary_master.secondary = 'Pasang Kancing' THEN reject ELSE 0 END AS reject_sp_pasang_kancing,
                    CASE WHEN output_secondary_master.secondary = 'Bartack' THEN total_in ELSE 0 END AS total_in_sp_bartack,
                    CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rft ELSE 0 END AS rft_sp_bartack,
                    CASE WHEN output_secondary_master.secondary = 'Bartack' THEN defect ELSE 0 END AS defect_sp_bartack,
                    CASE WHEN output_secondary_master.secondary = 'Bartack' THEN rework ELSE 0 END AS rework_sp_bartack,
                    CASE WHEN output_secondary_master.secondary = 'Bartack' THEN reject ELSE 0 END AS reject_sp_bartack,
                    CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN total_in ELSE 0 END AS total_in_sp_heatseal,
                    CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rft ELSE 0 END AS rft_sp_heatseal,
                    CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN defect ELSE 0 END AS defect_sp_heatseal,
                    CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN rework ELSE 0 END AS rework_sp_heatseal,
                    CASE WHEN output_secondary_master.secondary = 'Heatseal' THEN reject ELSE 0 END AS reject_sp_heatseal,
                    CASE WHEN output_secondary_master.secondary = 'Snap' THEN total_in ELSE 0 END AS total_in_sp_snap,
                    CASE WHEN output_secondary_master.secondary = 'Snap' THEN rft ELSE 0 END AS rft_sp_snap,
                    CASE WHEN output_secondary_master.secondary = 'Snap' THEN defect ELSE 0 END AS defect_sp_snap,
                    CASE WHEN output_secondary_master.secondary = 'Snap' THEN rework ELSE 0 END AS rework_sp_snap,
                    CASE WHEN output_secondary_master.secondary = 'Snap' THEN reject ELSE 0 END AS reject_sp_snap,
                    CASE WHEN output_secondary_master.secondary = 'Embro' THEN total_in ELSE 0 END AS total_in_sp_embro,
                    CASE WHEN output_secondary_master.secondary = 'Embro' THEN rft ELSE 0 END AS rft_sp_embro,
                    CASE WHEN output_secondary_master.secondary = 'Embro' THEN defect ELSE 0 END AS defect_sp_embro,
                    CASE WHEN output_secondary_master.secondary = 'Embro' THEN rework ELSE 0 END AS rework_sp_embro,
                    CASE WHEN output_secondary_master.secondary = 'Embro' THEN reject ELSE 0 END AS reject_sp_embro
                    FROM secondary_proses_per_proses
                    LEFT JOIN output_secondary_master ON output_secondary_master.id = secondary_proses_per_proses.secondary_id
                ) mut_sew
                GROUP BY id_so_det
            ),
            secondary_process_mapping_awal AS (
                SELECT
                    r.so_det_id AS id_so_det,
                    CASE
                        WHEN MAX(CASE WHEN osm.secondary = 'Pasang Kancing' THEN 1 ELSE 0 END) = 1
                            THEN 'Pasang Kancing'
                        WHEN MAX(CASE WHEN osm.secondary = 'Bartack' THEN 1 ELSE 0 END) = 1
                            THEN 'Bartack'
                        WHEN MAX(CASE WHEN osm.secondary = 'Heatseal' THEN 1 ELSE 0 END) = 1
                            THEN 'Heatseal'
                        WHEN MAX(CASE WHEN osm.secondary = 'Snap' THEN 1 ELSE 0 END) = 1
                            THEN 'Snap'
                        WHEN MAX(CASE WHEN osm.secondary = 'Embro' THEN 1 ELSE 0 END) = 1
                            THEN 'Embro'
                        ELSE 'TIDAK ADA MAPPING'
                    END AS secondary_process
                FROM output_rfts r
                INNER JOIN output_secondary_in osi
                    ON osi.rft_id = r.id
                INNER JOIN output_secondary_master osm
                    ON osm.id = osi.secondary_id
                GROUP BY r.so_det_id
            ),
            saldo_awal as (
                SELECT
                mut_wip_tmp.id_so_det,
                SUM(CASE WHEN tgl_trans < '2026-06-01' THEN qty_loading ELSE 0 END) qty_loading_awal,
                SUM(qty_sewing) qty_sewing_awal,
                SUM(input_rework_sewing) input_rework_sewing_awal,
                SUM(input_rework_spotcleaning) input_rework_spotcleaning_awal,
                SUM(input_rework_mending) input_rework_mending_awal,
                SUM(defect_sewing) defect_sewing_awal,
                SUM(defect_spotcleaning) defect_spotcleaning_awal,
                SUM(defect_mending) defect_mending_awal,
                SUM(qty_sew_reject) qty_sew_reject_awal,

                SUM(qty_finishing) qty_finishing_awal,
                SUM(input_rework_sewing_f) input_rework_sewing_f_awal,
                SUM(input_rework_spotcleaning_f) input_rework_spotcleaning_f_awal,
                SUM(input_rework_mending_f) input_rework_mending_f_awal,
                SUM(defect_sewing_f) defect_sewing_f_awal,
                SUM(defect_spotcleaning_f) defect_spotcleaning_f_awal,
                SUM(defect_mending_f) defect_mending_f_awal,
                SUM(qty_fin_reject) qty_fin_reject_awal,

                SUM(total_in_sp) total_in_sp_awal,
                SUM(rft_sp) rft_sp_awal,
                SUM(defect_sp) defect_sp_awal,
                SUM(rework_sp) rework_sp_awal,
                SUM(reject_sp) reject_sp_awal,
                SUM(reject_defect_sp) reject_defect_sp_awal,
                SUM(qty_reject_in) qty_reject_in_awal,
                SUM(qty_rejected) qty_rejected_awal,
                SUM(qty_reworked) qty_reworked_awal

                -- SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN total_in_sp ELSE 0 END) AS total_in_sp_pasang_kancing_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN rft_sp ELSE 0 END) AS rft_sp_pasang_kancing_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN defect_sp ELSE 0 END) AS defect_sp_pasang_kancing_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN rework_sp ELSE 0 END) AS rework_sp_pasang_kancing_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN reject_sp ELSE 0 END) AS reject_sp_pasang_kancing_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN reject_defect_sp ELSE 0 END) AS reject_defect_sp_pasang_kancing_awal,

                -- SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN total_in_sp ELSE 0 END) AS total_in_sp_bartack_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN rft_sp ELSE 0 END) AS rft_sp_bartack_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN defect_sp ELSE 0 END) AS defect_sp_bartack_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN rework_sp ELSE 0 END) AS rework_sp_bartack_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN reject_sp ELSE 0 END) AS reject_sp_bartack_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN reject_defect_sp ELSE 0 END) AS reject_defect_sp_bartack_awal,

                -- SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN total_in_sp ELSE 0 END) AS total_in_sp_heatseal_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN rft_sp ELSE 0 END) AS rft_sp_heatseal_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN defect_sp ELSE 0 END) AS defect_sp_heatseal_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN rework_sp ELSE 0 END) AS rework_sp_heatseal_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN reject_sp ELSE 0 END) AS reject_sp_heatseal_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN reject_defect_sp ELSE 0 END) AS reject_defect_sp_heatseal_awal,

                -- SUM(CASE WHEN spm.secondary_process = 'Snap' THEN total_in_sp ELSE 0 END) AS total_in_sp_snap_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Snap' THEN rft_sp ELSE 0 END) AS rft_sp_snap_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Snap' THEN defect_sp ELSE 0 END) AS defect_sp_snap_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Snap' THEN rework_sp ELSE 0 END) AS rework_sp_snap_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Snap' THEN reject_sp ELSE 0 END) AS reject_sp_snap_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Snap' THEN reject_defect_sp ELSE 0 END) AS reject_defect_sp_snap_awal,

                -- SUM(CASE WHEN spm.secondary_process = 'Embro' THEN total_in_sp ELSE 0 END) AS total_in_sp_embro_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Embro' THEN rft_sp ELSE 0 END) AS rft_sp_embro_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Embro' THEN defect_sp ELSE 0 END) AS defect_sp_embro_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Embro' THEN rework_sp ELSE 0 END) AS rework_sp_embro_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Embro' THEN reject_sp ELSE 0 END) AS reject_sp_embro_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'Embro' THEN reject_defect_sp ELSE 0 END) AS reject_defect_sp_embro_awal,

                -- SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING' THEN total_in_sp ELSE 0 END) AS total_in_sp_other_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING' THEN rft_sp ELSE 0 END) AS rft_sp_other_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING' THEN defect_sp ELSE 0 END) AS defect_sp_other_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING' THEN rework_sp ELSE 0 END) AS rework_sp_other_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING' THEN reject_sp ELSE 0 END) AS reject_sp_other_awal,
                -- SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING' THEN reject_defect_sp ELSE 0 END) AS reject_defect_sp_other_awal
                FROM mut_wip_tmp
                -- LEFT JOIN secondary_process_mapping_awal spm ON spm.id_so_det = mut_wip_tmp.id_so_det
                where tgl_trans >= '$tgl_saldo' AND tgl_trans <= '$prev_date'
                group by mut_wip_tmp.id_so_det
                UNION
                SELECT
                    id_so_det,
                    SUM(qty_loading) qty_loading_awal,
                    0 qty_sewing_awal,
                    0 input_rework_sewing_awal,
                    0 input_rework_spotcleaning_awal,
                    0 input_rework_mending_awal,
                    0 defect_sewing_awal,
                    0 defect_spotcleaning_awal,
                    0 defect_mending_awal,
                    0 qty_sew_reject_awal,

                    0 qty_finishing_awal,
                    0 input_rework_sewing_f_awal,
                    0 input_rework_spotcleaning_f_awal,
                    0 input_rework_mending_f_awal,
                    0 defect_sewing_f_awal,
                    0 defect_spotcleaning_f_awal,
                    0 defect_mending_f_awal,
                    0 qty_fin_reject_awal,

                    0 total_in_sp_awal,
                    0 rft_sp_awal,
                    0 defect_sp_awal,
                    0 rework_sp_awal,
                    0 reject_sp_awal,
                    0 reject_defect_sp_awal,
                    0 qty_reject_in_awal,
                    0 qty_rejected_awal,
                    0 qty_reworked_awal
                FROM
                    saldo_loading_awal
            ),
            saldo_awal_upload as(
                SELECT
                sa_report_output.so_det_id,
                sum(loading) loading,
                sum(sewing) sewing,
                sum(finishing) finishing,
                sum(secondary_proses) secondary_proses,
                sum(defect_sewing) defect_sewing,
                sum(defect_mending) defect_mending,
                sum(defect_spotcleaning) defect_spotcleaning,
                sum(qty_reject) qty_reject,
                sum(out_sew) out_sew,
                sum(packing_line) packing_line,
                sum(trf_gmt) trf_gmt,
                sum(packing_central) packing_central,
                SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing'
                    THEN secondary_proses ELSE 0 END) AS secondary_proses_pasang_kancing,
                SUM(CASE WHEN spm.secondary_process = 'Bartack'
                    THEN secondary_proses ELSE 0 END) AS secondary_proses_bartack,
                SUM(CASE WHEN spm.secondary_process = 'Heatseal'
                    THEN secondary_proses ELSE 0 END) AS secondary_proses_heatseal,
                SUM(CASE WHEN spm.secondary_process = 'Snap'
                    THEN secondary_proses ELSE 0 END) AS secondary_proses_snap,
                SUM(CASE WHEN spm.secondary_process = 'Embro'
                    THEN secondary_proses ELSE 0 END) AS secondary_proses_embro,
                SUM(CASE WHEN spm.secondary_process = 'TIDAK ADA MAPPING'
                    THEN secondary_proses ELSE 0 END) AS secondary_proses_other
                from laravel_nds.sa_report_output
                LEFT JOIN secondary_process_mapping_awal spm ON spm.id_so_det = sa_report_output.so_det_id
                where tgl_saldo = '$tgl_saldo'
                group by sa_report_output.so_det_id
            ),
            m as (
                SELECT id_so_det FROM saldo_awal
                UNION
                SELECT id_so_det FROM saldo_sew
                UNION
                SELECT so_det_id FROM saldo_awal_upload
            ),
            mut as (
                SELECT
                    m.id_so_det,
                    buyer,
                    ws,
                    styleno,
                    color,
                    mb.size,
                    dest,
                    -- SEWING
                    COALESCE(su.sewing, 0)
                    + COALESCE(qty_loading_awal, 0)
                    + COALESCE(input_rework_sewing_awal, 0)
                    + COALESCE(input_rework_spotcleaning_awal, 0)
                    + COALESCE(input_rework_mending_awal, 0)
                    - COALESCE(defect_sewing_awal, 0)
                    - COALESCE(defect_spotcleaning_awal, 0)
                    - COALESCE(defect_mending_awal, 0)
                    - COALESCE(qty_sew_reject_awal, 0)
                    - COALESCE(qty_sewing_awal, 0)
                    AS saldo_awal_sewing,
                    COALESCE(ss.qty_loading, 0) AS qty_loading,
                    COALESCE(ss.input_rework_sewing, 0) AS input_rework_sewing,
                    COALESCE(ss.input_rework_spotcleaning, 0) AS input_rework_spotcleaning,
                    COALESCE(ss.input_rework_mending, 0) AS input_rework_mending,
                    COALESCE(ss.defect_sewing, 0) AS defect_sewing,
                    COALESCE(ss.defect_spotcleaning, 0) AS defect_spotcleaning,
                    COALESCE(ss.defect_mending, 0) AS defect_mending,
                    COALESCE(ss.qty_sew_reject, 0) AS qty_sew_reject,
                    COALESCE(ss.qty_sewing, 0) AS qty_sewing,
                    (
                    COALESCE(su.sewing, 0)
                    + COALESCE(qty_loading_awal, 0)
                    + COALESCE(input_rework_sewing_awal, 0)
                    + COALESCE(input_rework_spotcleaning_awal, 0)
                    + COALESCE(input_rework_mending_awal, 0)
                    - COALESCE(defect_sewing_awal, 0)
                    - COALESCE(defect_spotcleaning_awal, 0)
                    - COALESCE(defect_mending_awal, 0)
                    - COALESCE(qty_sew_reject_awal, 0)
                    - COALESCE(qty_sewing_awal, 0)
                    ) + COALESCE(ss.qty_loading, 0) + COALESCE(ss.input_rework_sewing, 0) + COALESCE(ss.input_rework_mending, 0) + COALESCE(ss.input_rework_spotcleaning, 0)
                    - COALESCE(ss.defect_sewing, 0) - COALESCE(ss.defect_spotcleaning, 0) - COALESCE(ss.defect_mending, 0)
                    - COALESCE(ss.qty_sew_reject, 0) - COALESCE(ss.qty_sewing, 0)
                    AS saldo_akhir_sewing,
                    -- FINISHING
                    COALESCE(su.finishing, 0)
                    + COALESCE(qty_sewing_awal, 0)
                    + COALESCE(input_rework_sewing_f_awal, 0)
                    + COALESCE(input_rework_spotcleaning_f_awal, 0)
                    + COALESCE(input_rework_mending_f_awal, 0)
                    - COALESCE(defect_sewing_f_awal, 0)
                    - COALESCE(defect_spotcleaning_f_awal, 0)
                    - COALESCE(defect_mending_f_awal, 0)
                    - COALESCE(qty_fin_reject_awal, 0)
                    - COALESCE(qty_finishing_awal, 0)
                    AS saldo_awal_finishing,
                    COALESCE(ss.input_rework_sewing_f, 0) AS input_rework_sewing_f,
                    COALESCE(ss.input_rework_spotcleaning_f, 0) AS input_rework_spotcleaning_f,
                    COALESCE(ss.input_rework_mending_f, 0) AS input_rework_mending_f,
                    COALESCE(ss.defect_sewing_f, 0) AS defect_sewing_f,
                    COALESCE(ss.defect_spotcleaning_f, 0) AS defect_spotcleaning_f,
                    COALESCE(ss.defect_mending_f, 0) AS defect_mending_f,
                    COALESCE(ss.qty_fin_reject, 0) AS qty_fin_reject,
                    COALESCE(ss.qty_finishing, 0) AS qty_finishing,
                    (
                    COALESCE(su.finishing, 0)
                    + COALESCE(qty_sewing_awal, 0)
                    + COALESCE(input_rework_sewing_f_awal, 0)
                    + COALESCE(input_rework_spotcleaning_f_awal, 0)
                    + COALESCE(input_rework_mending_f_awal, 0)
                    - COALESCE(defect_sewing_f_awal, 0)
                    - COALESCE(defect_spotcleaning_f_awal, 0)
                    - COALESCE(defect_mending_f_awal, 0)
                    - COALESCE(qty_fin_reject_awal, 0)
                    - COALESCE(qty_finishing_awal, 0)
                    )
                    + COALESCE(qty_sewing, 0) + COALESCE(ss.input_rework_sewing_f, 0) + COALESCE(ss.input_rework_spotcleaning_f, 0) + COALESCE(ss.input_rework_mending_f, 0)
                    - COALESCE(ss.defect_sewing_f, 0) - COALESCE(ss.defect_spotcleaning_f, 0) - COALESCE(ss.defect_mending_f, 0)
                    - COALESCE(ss.qty_fin_reject, 0) - COALESCE(ss.qty_finishing, 0)
                    AS saldo_akhir_finishing,
                    -- SECONDARY PROSES
                    COALESCE(su.secondary_proses, 0)
                    + COALESCE(total_in_sp_awal, 0)
                    + COALESCE(rework_sp_awal, 0) + COALESCE(reject_defect_sp_awal, 0)
                    - COALESCE(defect_sp_awal, 0)
                    - (COALESCE(reject_sp_awal, 0) + COALESCE(reject_defect_sp_awal, 0))
                    - (COALESCE(rft_sp_awal, 0) + COALESCE(rework_sp_awal, 0))
                    AS saldo_awal_secondary_proses,
                    COALESCE(ss.total_in_sp, 0) AS total_in_sp,
                    COALESCE(ss.rework_sp, 0) + COALESCE(ss.reject_defect_sp, 0) AS rework_sp,
                    COALESCE(ss.defect_sp, 0) AS defect_sp,
                    COALESCE(ss.reject_sp, 0) + COALESCE(ss.reject_defect_sp, 0) AS reject_sp,
                    COALESCE(ss.rft_sp, 0) + COALESCE(ss.rework_sp, 0) AS rft_sp,
                    (
                    COALESCE(su.secondary_proses, 0)
                    + COALESCE(total_in_sp_awal, 0)
                    + COALESCE(rework_sp_awal, 0) + COALESCE(reject_defect_sp_awal, 0)
                    - COALESCE(defect_sp_awal, 0)
                    - (COALESCE(reject_sp_awal, 0) + COALESCE(reject_defect_sp_awal, 0))
                    - (COALESCE(rft_sp_awal, 0) + COALESCE(rework_sp_awal, 0))
                    ) + COALESCE(ss.total_in_sp, 0) + (COALESCE(ss.rework_sp, 0) + COALESCE(ss.reject_defect_sp, 0))
                    - COALESCE(ss.defect_sp, 0) - (COALESCE(ss.reject_sp, 0) + COALESCE(ss.reject_defect_sp, 0))
                    - (COALESCE(ss.rft_sp, 0) + COALESCE(ss.rework_sp, 0) )
                    AS saldo_akhir_secondary_proses,
                    -- DEFECT SEWING
                    COALESCE(su.defect_sewing, 0)
                    + COALESCE(defect_sewing_awal, 0)
                    + COALESCE(defect_sewing_f_awal, 0)
                    - COALESCE(input_rework_sewing_awal, 0)
                    - COALESCE(input_rework_sewing_f_awal, 0)
                    AS saldo_awal_defect_sewing,
                    COALESCE(ss.defect_sewing, 0) + COALESCE(ss.defect_sewing_f, 0) AS total_defect_sewing,
                    COALESCE(ss.input_rework_sewing, 0) + COALESCE(ss.input_rework_sewing_f, 0) AS total_input_rework_sewing,
                    (
                    COALESCE(su.defect_sewing, 0)
                    + COALESCE(defect_sewing_awal, 0)
                    + COALESCE(defect_sewing_f_awal, 0)
                    - COALESCE(input_rework_sewing_awal, 0)
                    - COALESCE(input_rework_sewing_f_awal, 0)
                    )
                    + (COALESCE(ss.defect_sewing, 0) + COALESCE(ss.defect_sewing_f, 0))
                    - (COALESCE(ss.input_rework_sewing, 0) + COALESCE(ss.input_rework_sewing_f, 0)) AS saldo_akhir_defect_sewing,
                    -- DEFECT SPOTCLEANING
                    COALESCE(su.defect_spotcleaning, 0)
                    + COALESCE(defect_spotcleaning_awal, 0)
                    + COALESCE(defect_spotcleaning_f_awal, 0)
                    - COALESCE(input_rework_spotcleaning_awal, 0)
                    - COALESCE(input_rework_spotcleaning_f_awal, 0)
                    AS saldo_awal_defect_spotcleaning,
                    COALESCE(ss.defect_spotcleaning, 0) + COALESCE(ss.defect_spotcleaning_f, 0) AS total_defect_spotcleaning,
                    COALESCE(ss.input_rework_spotcleaning, 0) + COALESCE(ss.input_rework_spotcleaning_f, 0) AS total_input_rework_spotcleaning,
                    (
                    COALESCE(su.defect_spotcleaning, 0)
                    + COALESCE(defect_spotcleaning_awal, 0)
                    + COALESCE(defect_spotcleaning_f_awal, 0)
                    - COALESCE(input_rework_spotcleaning_awal, 0)
                    - COALESCE(input_rework_spotcleaning_f_awal, 0)
                    )
                    + (COALESCE(ss.defect_spotcleaning, 0) + COALESCE(ss.defect_spotcleaning_f, 0))
                    - (COALESCE(ss.input_rework_spotcleaning, 0) + COALESCE(ss.input_rework_spotcleaning_f, 0)) AS saldo_akhir_defect_spotcleaning,
                    -- DEFECT MENDING
                    COALESCE(su.defect_mending, 0)
                    + COALESCE(defect_mending_awal, 0)
                    + COALESCE(defect_mending_f_awal, 0)
                    - COALESCE(input_rework_mending_awal, 0)
                    - COALESCE(input_rework_mending_f_awal, 0)
                    AS saldo_awal_defect_mending,
                    COALESCE(ss.defect_mending, 0) + COALESCE(ss.defect_mending_f, 0) AS total_defect_mending,
                    COALESCE(ss.input_rework_mending, 0) + COALESCE(ss.input_rework_mending_f, 0) AS total_input_rework_mending,
                    (
                    COALESCE(su.defect_mending, 0)
                    + COALESCE(defect_mending_awal, 0)
                    + COALESCE(defect_mending_f_awal, 0)
                    - COALESCE(input_rework_mending_awal, 0)
                    - COALESCE(input_rework_mending_f_awal, 0)
                    )
                    + (COALESCE(ss.defect_mending, 0) + COALESCE(ss.defect_mending_f, 0))
                    - (COALESCE(ss.input_rework_mending, 0) + COALESCE(ss.input_rework_mending_f, 0)) AS saldo_akhir_mending,
                    -- QC REJECT
                    COALESCE(su.qty_reject, 0)
                    + COALESCE(qty_reject_in_awal, 0)
                    - COALESCE(qty_rejected_awal, 0)
                    - COALESCE(qty_reworked_awal, 0)
                    AS saldo_awal_reject,
                    COALESCE(ss.qty_reject_in, 0) AS qty_reject_in,
                    COALESCE(ss.qty_rejected, 0) AS qty_rejected,
                    COALESCE(ss.qty_reworked, 0) AS qty_reworked,
                    (
                    COALESCE(su.qty_reject, 0)
                    + COALESCE(qty_reject_in_awal, 0)
                    - COALESCE(qty_rejected_awal, 0)
                    - COALESCE(qty_reworked_awal, 0)
                    )
                    + COALESCE(ss.qty_reject_in, 0)
                    - COALESCE(ss.qty_rejected, 0)
                    - COALESCE(ss.qty_reworked, 0) AS saldo_akhir_qc_reject,
                    COALESCE(ss.qty_transit_terima_sewing_before, 0) AS qty_transit_terima_sewing_before,
                    COALESCE(ss.qty_transit_terima_qc_finishing_before, 0) AS qty_transit_terima_qc_finishing_before,
                    COALESCE(ss.qty_transit_terima_finishing_before, 0) AS qty_transit_terima_finishing_before,
                    COALESCE(ss.qty_transit_keluar_qc_reject_before, 0) AS qty_transit_keluar_qc_reject_before,

                    -- (
                    --     COALESCE(su.secondary_proses_pasang_kancing, 0)
                    --     + COALESCE(sa.total_in_sp_pasang_kancing_awal, 0)
                    --     + COALESCE(sa.rework_sp_pasang_kancing_awal, 0)
                    --     + COALESCE(sa.reject_defect_sp_pasang_kancing_awal, 0)
                    --     - COALESCE(sa.defect_sp_pasang_kancing_awal, 0)
                    --     - (
                    --         COALESCE(sa.reject_sp_pasang_kancing_awal, 0)
                    --         + COALESCE(sa.reject_defect_sp_pasang_kancing_awal, 0)
                    --     )
                    --     - (
                    --         COALESCE(sa.rft_sp_pasang_kancing_awal, 0)
                    --         + COALESCE(sa.rework_sp_pasang_kancing_awal, 0)
                    --     )
                    -- ) AS saldo_awal_sp_pasang_kancing,

                    -- (
                    --     COALESCE(su.secondary_proses_bartack, 0)
                    --     + COALESCE(sa.total_in_sp_bartack_awal, 0)
                    --     + COALESCE(sa.rework_sp_bartack_awal, 0)
                    --     + COALESCE(sa.reject_defect_sp_bartack_awal, 0)
                    --     - COALESCE(sa.defect_sp_bartack_awal, 0)
                    --     - (
                    --         COALESCE(sa.reject_sp_bartack_awal, 0)
                    --         + COALESCE(sa.reject_defect_sp_bartack_awal, 0)
                    --     )
                    --     - (
                    --         COALESCE(sa.rft_sp_bartack_awal, 0)
                    --         + COALESCE(sa.rework_sp_bartack_awal, 0)
                    --     )
                    -- ) AS saldo_awal_sp_bartack,

                    -- (
                    --     COALESCE(su.secondary_proses_heatseal, 0)
                    --     + COALESCE(sa.total_in_sp_heatseal_awal, 0)
                    --     + COALESCE(sa.rework_sp_heatseal_awal, 0)
                    --     + COALESCE(sa.reject_defect_sp_heatseal_awal, 0)
                    --     - COALESCE(sa.defect_sp_heatseal_awal, 0)
                    --     - (
                    --         COALESCE(sa.reject_sp_heatseal_awal, 0)
                    --         + COALESCE(sa.reject_defect_sp_heatseal_awal, 0)
                    --     )
                    --     - (
                    --         COALESCE(sa.rft_sp_heatseal_awal, 0)
                    --         + COALESCE(sa.rework_sp_heatseal_awal, 0)
                    --     )
                    -- ) AS saldo_awal_sp_heatseal,

                    -- (
                    --     COALESCE(su.secondary_proses_snap, 0)
                    --     + COALESCE(sa.total_in_sp_snap_awal, 0)
                    --     + COALESCE(sa.rework_sp_snap_awal, 0)
                    --     + COALESCE(sa.reject_defect_sp_snap_awal, 0)
                    --     - COALESCE(sa.defect_sp_snap_awal, 0)
                    --     - (
                    --         COALESCE(sa.reject_sp_snap_awal, 0)
                    --         + COALESCE(sa.reject_defect_sp_snap_awal, 0)
                    --     )
                    --     - (
                    --         COALESCE(sa.rft_sp_snap_awal, 0)
                    --         + COALESCE(sa.rework_sp_snap_awal, 0)
                    --     )
                    -- ) AS saldo_awal_sp_snap,

                    -- (
                    --     COALESCE(su.secondary_proses_embro, 0)
                    --     + COALESCE(sa.total_in_sp_embro_awal, 0)
                    --     + COALESCE(sa.rework_sp_embro_awal, 0)
                    --     + COALESCE(sa.reject_defect_sp_embro_awal, 0)
                    --     - COALESCE(sa.defect_sp_embro_awal, 0)
                    --     - (
                    --         COALESCE(sa.reject_sp_embro_awal, 0)
                    --         + COALESCE(sa.reject_defect_sp_embro_awal, 0)
                    --     )
                    --     - (
                    --         COALESCE(sa.rft_sp_embro_awal, 0)
                    --         + COALESCE(sa.rework_sp_embro_awal, 0)
                    --     )
                    -- ) AS saldo_awal_sp_embro,

                    (
                        COALESCE(su.secondary_proses_pasang_kancing, 0)
                        + COALESCE(ss.saldo_awal_sp_pasang_kancing, 0)
                    ) AS saldo_awal_sp_pasang_kancing,
                    (
                        COALESCE(su.secondary_proses_bartack, 0)
                        + COALESCE(ss.saldo_awal_sp_bartack, 0)
                    ) AS saldo_awal_sp_bartack,
                    (
                        COALESCE(su.secondary_proses_heatseal, 0)
                        + COALESCE(ss.saldo_awal_sp_heatseal, 0)
                    ) AS saldo_awal_sp_heatseal,
                    (
                        COALESCE(su.secondary_proses_snap, 0)
                        + COALESCE(ss.saldo_awal_sp_snap, 0)
                    ) AS saldo_awal_sp_snap,
                    (
                        COALESCE(su.secondary_proses_embro, 0)
                        + COALESCE(ss.saldo_awal_sp_embro, 0)
                    ) AS saldo_awal_sp_embro,

                    COALESCE(ss.total_in_sp_pasang_kancing, 0) AS total_in_sp_pasang_kancing,
                    COALESCE(ss.rft_sp_pasang_kancing, 0) AS rft_sp_pasang_kancing,
                    COALESCE(ss.defect_sp_pasang_kancing, 0) AS defect_sp_pasang_kancing,
                    COALESCE(ss.rework_sp_pasang_kancing, 0) AS rework_sp_pasang_kancing,
                    COALESCE(ss.reject_sp_pasang_kancing, 0) AS reject_sp_pasang_kancing,
                    COALESCE(ss.total_in_sp_bartack, 0) AS total_in_sp_bartack,
                    COALESCE(ss.rft_sp_bartack, 0) AS rft_sp_bartack,
                    COALESCE(ss.defect_sp_bartack, 0) AS defect_sp_bartack,
                    COALESCE(ss.rework_sp_bartack, 0) AS rework_sp_bartack,
                    COALESCE(ss.reject_sp_bartack, 0) AS reject_sp_bartack,
                    COALESCE(ss.total_in_sp_heatseal, 0) AS total_in_sp_heatseal,
                    COALESCE(ss.rft_sp_heatseal, 0) AS rft_sp_heatseal,
                    COALESCE(ss.defect_sp_heatseal, 0) AS defect_sp_heatseal,
                    COALESCE(ss.rework_sp_heatseal, 0) AS rework_sp_heatseal,
                    COALESCE(ss.reject_sp_heatseal, 0) AS reject_sp_heatseal,
                    COALESCE(ss.total_in_sp_snap, 0) AS total_in_sp_snap,
                    COALESCE(ss.rft_sp_snap, 0) AS rft_sp_snap,
                    COALESCE(ss.defect_sp_snap, 0) AS defect_sp_snap,
                    COALESCE(ss.rework_sp_snap, 0) AS rework_sp_snap,
                    COALESCE(ss.reject_sp_snap, 0) AS reject_sp_snap,
                    COALESCE(ss.total_in_sp_embro, 0) AS total_in_sp_embro,
                    COALESCE(ss.rft_sp_embro, 0) AS rft_sp_embro,
                    COALESCE(ss.defect_sp_embro, 0) AS defect_sp_embro,
                    COALESCE(ss.rework_sp_embro, 0) AS rework_sp_embro,
                    COALESCE(ss.reject_sp_embro, 0) AS reject_sp_embro
                FROM m
                    LEFT JOIN saldo_awal sa on m.id_so_det = sa.id_so_det
                    LEFT JOIN saldo_sew ss on m.id_so_det = ss.id_so_det
                    LEFT JOIN saldo_awal_upload su on m.id_so_det = su.so_det_id
                    LEFT JOIN (
                        SELECT
                        sd.id as id_so_det,
                        ac.kpno as ws,
                        supplier as buyer,
                        styleno,
                        color,
                        size,
                        dest
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on m.id_so_det = mb.id_so_det
                    $filter
                HAVING NOT (
                    saldo_awal_sewing = 0 AND
                    qty_loading = 0 AND
                    input_rework_sewing = 0 AND
                    input_rework_spotcleaning = 0 AND
                    input_rework_mending = 0 AND
                    defect_sewing = 0 AND
                    defect_spotcleaning = 0 AND
                    defect_mending = 0 AND
                    qty_sew_reject = 0 AND
                    qty_sewing = 0 AND
                    saldo_akhir_sewing = 0 AND
                    saldo_awal_finishing = 0 AND
                    input_rework_sewing_f = 0 AND
                    input_rework_spotcleaning_f = 0 AND
                    input_rework_mending_f = 0 AND
                    defect_sewing_f = 0 AND
                    defect_spotcleaning_f = 0 AND
                    defect_mending_f = 0 AND
                    qty_fin_reject = 0 AND
                    qty_finishing = 0 AND
                    saldo_akhir_finishing = 0 AND
                    saldo_awal_secondary_proses = 0 AND
                    total_in_sp = 0 AND
                    rework_sp = 0 AND
                    defect_sp = 0 AND
                    reject_sp = 0 AND
                    rft_sp = 0 AND
                    saldo_akhir_secondary_proses = 0 AND
                    saldo_awal_defect_sewing = 0 AND
                    total_defect_sewing = 0 AND
                    total_input_rework_sewing = 0 AND
                    saldo_akhir_defect_sewing = 0 AND
                    saldo_awal_defect_spotcleaning = 0 AND
                    total_defect_spotcleaning = 0 AND
                    total_input_rework_spotcleaning = 0 AND
                    saldo_akhir_defect_spotcleaning = 0 AND
                    saldo_awal_defect_mending = 0 AND
                    total_defect_mending = 0 AND
                    total_input_rework_mending = 0 AND
                    saldo_akhir_mending = 0 AND
                    saldo_awal_reject = 0 AND
                    qty_reject_in = 0 AND
                    qty_rejected = 0 AND
                    qty_reworked = 0 AND
                    saldo_akhir_qc_reject = 0 AND
                    qty_transit_terima_sewing_before = 0 AND
                    qty_transit_terima_qc_finishing_before = 0 AND
                    qty_transit_terima_finishing_before = 0 AND
                    qty_transit_keluar_qc_reject_before = 0 AND
                    saldo_awal_sp_pasang_kancing = 0 AND
                    saldo_awal_sp_bartack = 0 AND
                    saldo_awal_sp_heatseal = 0 AND
                    saldo_awal_sp_snap = 0 AND
                    saldo_awal_sp_embro = 0 AND
                    total_in_sp_pasang_kancing = 0 AND
                    rft_sp_pasang_kancing = 0 AND
                    defect_sp_pasang_kancing = 0 AND
                    rework_sp_pasang_kancing = 0 AND
                    reject_sp_pasang_kancing = 0 AND
                    total_in_sp_bartack = 0 AND
                    rft_sp_bartack = 0 AND
                    defect_sp_bartack = 0 AND
                    rework_sp_bartack = 0 AND
                    reject_sp_bartack = 0 AND
                    total_in_sp_heatseal = 0 AND
                    rft_sp_heatseal = 0 AND
                    defect_sp_heatseal = 0 AND
                    rework_sp_heatseal = 0 AND
                    reject_sp_heatseal = 0 AND
                    total_in_sp_snap = 0 AND
                    rft_sp_snap = 0 AND
                    defect_sp_snap = 0 AND
                    rework_sp_snap = 0 AND
                    reject_sp_snap = 0 AND
                    total_in_sp_embro = 0 AND
                    rft_sp_embro = 0 AND
                    defect_sp_embro = 0 AND
                    rework_sp_embro = 0 AND
                    reject_sp_embro = 0
                )
            ),
            secondary_process_mapping AS (
                SELECT
                    msw.buyer,
                    msw.ws,
                    msw.styleno,
                    msw.color,
                    msw.size,
                    CASE
                        WHEN MAX(CASE WHEN osm.secondary = 'Pasang Kancing' THEN 1 ELSE 0 END) = 1
                            THEN 'Pasang Kancing'
                        WHEN MAX(CASE WHEN osm.secondary = 'Bartack' THEN 1 ELSE 0 END) = 1
                            THEN 'Bartack'
                        WHEN MAX(CASE WHEN osm.secondary = 'Heatseal' THEN 1 ELSE 0 END) = 1
                            THEN 'Heatseal'
                        WHEN MAX(CASE WHEN osm.secondary = 'Snap' THEN 1 ELSE 0 END) = 1
                            THEN 'Snap'
                        WHEN MAX(CASE WHEN osm.secondary = 'Embro' THEN 1 ELSE 0 END) = 1
                            THEN 'Embro'
                        ELSE 'TIDAK ADA MAPPING'
                    END AS secondary_process
                FROM laravel_nds.master_sb_ws msw
                INNER JOIN output_rfts r
                    ON r.so_det_id = msw.id_so_det
                INNER JOIN output_secondary_in osi
                    ON osi.rft_id = r.id
                INNER JOIN output_secondary_master osm
                    ON osm.id = osi.secondary_id
                GROUP BY
                    msw.buyer,
                    msw.ws,
                    msw.styleno,
                    msw.color,
                    msw.size
            ),
            saldo_awal_inject as (
                SELECT
                    inject_mutasi_sewing.buyer,
                    inject_mutasi_sewing.ws,
                    inject_mutasi_sewing.styleno,
                    inject_mutasi_sewing.color,
                    inject_mutasi_sewing.size,
                    -- SEWING AKHIR
                    SUM(COALESCE(saldo_awal_sewing, 0) +
                    COALESCE(qty_loading, 0) +
                    COALESCE(input_rework_sewing, 0) +
                    COALESCE(input_rework_mending, 0) +
                    COALESCE(input_rework_spotcleaning, 0) -
                    COALESCE(defect_sewing, 0) -
                    COALESCE(defect_spotcleaning, 0) -
                    COALESCE(defect_mending, 0) -
                    COALESCE(qty_sew_reject, 0) -
                    COALESCE(qty_sewing, 0)) as saldo_awal_sewing,
                    0 qty_loading,
                    0 input_rework_sewing,
                    0 input_rework_spotcleaning,
                    0 input_rework_mending,
                    0 defect_sewing,
                    0 defect_spotcleaning,
                    0 defect_mending,
                    0 qty_sew_reject,
                    0 qty_sewing,
                    0 saldo_akhir_sewing,
                    -- FINISHING AKHIR
                    SUM(COALESCE(saldo_awal_finishing, 0) +
                    COALESCE(qty_sewing, 0) +
                    COALESCE(input_rework_sewing_f, 0) +
                    COALESCE(input_rework_spotcleaning_f, 0) +
                    COALESCE(input_rework_mending_f, 0) -
                    COALESCE(defect_sewing_f, 0) -
                    COALESCE(defect_spotcleaning_f, 0) -
                    COALESCE(defect_mending_f, 0) -
                    COALESCE(qty_fin_reject, 0) -
                    COALESCE(qty_finishing, 0)) as saldo_awal_finishing,
                    0 input_rework_sewing_f,
                    0 input_rework_spotcleaning_f,
                    0 input_rework_mending_f,
                    0 defect_sewing_f,
                    0 defect_spotcleaning_f,
                    0 defect_mending_f,
                    0 qty_fin_reject,
                    0 qty_finishing,
                    0 saldo_akhir_finishing,
                        -- SECONDARY PROCESS AKHIR
                    SUM(COALESCE(total_in_sp, 0) +
                    COALESCE(rework_sp, 0) -
                    COALESCE(defect_sp, 0) -
                    COALESCE(reject_sp, 0) -
                    COALESCE(rft_sp, 0)) as saldo_awal_secondary_proses,
                    0 total_in_sp,
                    0 rework_sp,
                    0 defect_sp,
                    0 reject_sp,
                    0 rft_sp,
                    0 saldo_akhir_secondary_proses,
                    -- DEFECT AKHIR
                    SUM(COALESCE(saldo_awal_defect_sewing, 0) +
                    COALESCE(total_defect_sewing, 0) -
                    COALESCE(total_input_rework_sewing, 0)) as saldo_awal_defect_sewing,
                    0 total_defect_sewing,
                    0 total_input_rework_sewing,
                    0 saldo_akhir_defect_sewing,
                    -- SPOTCLEANING AKHIR
                    SUM(COALESCE(saldo_awal_defect_spotcleaning, 0) +
                    COALESCE(total_defect_spotcleaning, 0) -
                    COALESCE(total_input_rework_spotcleaning, 0)) as saldo_awal_defect_spotcleaning,
                    0 total_defect_spotcleaning,
                    0 total_input_rework_spotcleaning,
                    0 saldo_akhir_defect_spotcleaning,
                    -- MENDING AKHIR
                    SUM(COALESCE(saldo_awal_defect_mending, 0) +
                    COALESCE(total_defect_mending, 0) -
                    COALESCE(total_input_rework_mending, 0)) as saldo_awal_defect_mending,
                    0 total_defect_mending,
                    0 total_input_rework_mending,
                    0 saldo_akhir_mending,
                    -- REJECT AKHIR
                    SUM(COALESCE(saldo_awal_reject, 0)
                    + COALESCE(qty_reject_in, 0)
                    - COALESCE(qty_rejected, 0)
                    - COALESCE(qty_reworked, 0)) as saldo_awal_reject,
                    0 qty_reject_in,
                    0 qty_reworked,
                    0 qty_rejected,
                    0 saldo_akhir_qc_reject,
                    COALESCE(SUM(CASE WHEN tgl_saldo >= '2026-07-01' THEN qty_sew_reject ELSE 0 END), 0) AS qty_transit_terima_sewing_before,
                    COALESCE(SUM(CASE WHEN tgl_saldo >= '2026-07-01' THEN qty_fin_reject ELSE 0 END), 0) AS qty_transit_terima_qc_finishing_before,
                    COALESCE(SUM(CASE WHEN tgl_saldo >= '2026-07-01' THEN rft_sp ELSE 0 END), 0) AS qty_transit_terima_finishing_before,
                    COALESCE(SUM(CASE WHEN tgl_saldo >= '2026-07-01' THEN qty_rejected ELSE 0 END), 0) AS qty_transit_keluar_qc_reject_before,
                    SUM(
                        CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN
                            COALESCE(total_in_sp, 0)
                            + COALESCE(rework_sp, 0)
                            - COALESCE(defect_sp, 0)
                            - COALESCE(reject_sp, 0)
                            - COALESCE(rft_sp, 0)
                        ELSE 0 END
                    ) AS saldo_awal_sp_pasang_kancing,

                    SUM(
                        CASE WHEN spm.secondary_process = 'Bartack' THEN
                            COALESCE(total_in_sp, 0)
                            + COALESCE(rework_sp, 0)
                            - COALESCE(defect_sp, 0)
                            - COALESCE(reject_sp, 0)
                            - COALESCE(rft_sp, 0)
                        ELSE 0 END
                    ) AS saldo_awal_sp_bartack,

                    SUM(
                        CASE WHEN spm.secondary_process = 'Heatseal' THEN
                            COALESCE(total_in_sp, 0)
                            + COALESCE(rework_sp, 0)
                            - COALESCE(defect_sp, 0)
                            - COALESCE(reject_sp, 0)
                            - COALESCE(rft_sp, 0)
                        ELSE 0 END
                    ) AS saldo_awal_sp_heatseal,

                    SUM(
                        CASE WHEN spm.secondary_process = 'Snap' THEN
                            COALESCE(total_in_sp, 0)
                            + COALESCE(rework_sp, 0)
                            - COALESCE(defect_sp, 0)
                            - COALESCE(reject_sp, 0)
                            - COALESCE(rft_sp, 0)
                        ELSE 0 END
                    ) AS saldo_awal_sp_snap,

                    SUM(
                        CASE WHEN spm.secondary_process = 'Embro' THEN
                            COALESCE(total_in_sp, 0)
                            + COALESCE(rework_sp, 0)
                            - COALESCE(defect_sp, 0)
                            - COALESCE(reject_sp, 0)
                            - COALESCE(rft_sp, 0)
                        ELSE 0 END
                    ) AS saldo_awal_sp_embro,
                    0 AS total_in_sp_pasang_kancing, 0 AS rft_sp_pasang_kancing, 0 AS defect_sp_pasang_kancing, 0 AS rework_sp_pasang_kancing, 0 AS reject_sp_pasang_kancing,
                    0 AS total_in_sp_bartack, 0 AS rft_sp_bartack, 0 AS defect_sp_bartack, 0 AS rework_sp_bartack, 0 AS reject_sp_bartack,
                    0 AS total_in_sp_heatseal, 0 AS rft_sp_heatseal, 0 AS defect_sp_heatseal, 0 AS rework_sp_heatseal, 0 AS reject_sp_heatseal,
                    0 AS total_in_sp_snap, 0 AS rft_sp_snap, 0 AS defect_sp_snap, 0 AS rework_sp_snap, 0 AS reject_sp_snap,
                    0 AS total_in_sp_embro, 0 AS rft_sp_embro, 0 AS defect_sp_embro, 0 AS rework_sp_embro, 0 AS reject_sp_embro
                FROM
                    inject_mutasi_sewing
                LEFT JOIN secondary_process_mapping spm
                ON spm.buyer = inject_mutasi_sewing.buyer
                AND spm.ws = inject_mutasi_sewing.ws
                AND spm.styleno = inject_mutasi_sewing.styleno
                AND spm.color = inject_mutasi_sewing.color
                AND spm.size = inject_mutasi_sewing.size
                WHERE
                    tgl_saldo < '$start_date'
                GROUP BY
                    inject_mutasi_sewing.buyer, inject_mutasi_sewing.ws, inject_mutasi_sewing.styleno, inject_mutasi_sewing.color, inject_mutasi_sewing.size
                HAVING NOT (
                    saldo_awal_sewing = 0 AND
                    qty_loading = 0 AND
                    input_rework_sewing = 0 AND
                    input_rework_spotcleaning = 0 AND
                    input_rework_mending = 0 AND
                    defect_sewing = 0 AND
                    defect_spotcleaning = 0 AND
                    defect_mending = 0 AND
                    qty_sew_reject = 0 AND
                    qty_sewing = 0 AND
                    saldo_akhir_sewing = 0 AND
                    saldo_awal_finishing = 0 AND
                    input_rework_sewing_f = 0 AND
                    input_rework_spotcleaning_f = 0 AND
                    input_rework_mending_f = 0 AND
                    defect_sewing_f = 0 AND
                    defect_spotcleaning_f = 0 AND
                    defect_mending_f = 0 AND
                    qty_fin_reject = 0 AND
                    qty_finishing = 0 AND
                    saldo_akhir_finishing = 0 AND
                    saldo_awal_secondary_proses = 0 AND
                    total_in_sp = 0 AND
                    rework_sp = 0 AND
                    defect_sp = 0 AND
                    reject_sp = 0 AND
                    rft_sp = 0 AND
                    saldo_akhir_secondary_proses = 0 AND
                    saldo_awal_defect_sewing = 0 AND
                    total_defect_sewing = 0 AND
                    total_input_rework_sewing = 0 AND
                    saldo_akhir_defect_sewing = 0 AND
                    saldo_awal_defect_spotcleaning = 0 AND
                    total_defect_spotcleaning = 0 AND
                    total_input_rework_spotcleaning = 0 AND
                    saldo_akhir_defect_spotcleaning = 0 AND
                    saldo_awal_defect_mending = 0 AND
                    total_defect_mending = 0 AND
                    total_input_rework_mending = 0 AND
                    saldo_akhir_mending = 0 AND
                    saldo_awal_reject = 0 AND
                    qty_reject_in = 0 AND
                    qty_rejected = 0 AND
                    qty_reworked = 0 AND
                    saldo_akhir_qc_reject = 0 AND
                    qty_transit_terima_sewing_before = 0 AND
                    qty_transit_terima_qc_finishing_before = 0 AND
                    qty_transit_terima_finishing_before = 0 AND
                    qty_transit_keluar_qc_reject_before = 0 AND
                    saldo_awal_sp_pasang_kancing = 0 AND
                    saldo_awal_sp_bartack = 0 AND
                    saldo_awal_sp_heatseal = 0 AND
                    saldo_awal_sp_snap = 0 AND
                    saldo_awal_sp_embro = 0 AND
                    total_in_sp_pasang_kancing = 0 AND
                    rft_sp_pasang_kancing = 0 AND
                    defect_sp_pasang_kancing = 0 AND
                    rework_sp_pasang_kancing = 0 AND
                    reject_sp_pasang_kancing = 0 AND
                    total_in_sp_bartack = 0 AND
                    rft_sp_bartack = 0 AND
                    defect_sp_bartack = 0 AND
                    rework_sp_bartack = 0 AND
                    reject_sp_bartack = 0 AND
                    total_in_sp_heatseal = 0 AND
                    rft_sp_heatseal = 0 AND
                    defect_sp_heatseal = 0 AND
                    rework_sp_heatseal = 0 AND
                    reject_sp_heatseal = 0 AND
                    total_in_sp_snap = 0 AND
                    rft_sp_snap = 0 AND
                    defect_sp_snap = 0 AND
                    rework_sp_snap = 0 AND
                    reject_sp_snap = 0 AND
                    total_in_sp_embro = 0 AND
                    rft_sp_embro = 0 AND
                    defect_sp_embro = 0 AND
                    rework_sp_embro = 0 AND
                    reject_sp_embro = 0
                )
            ),
            current_inject as (
                SELECT
                    inject_mutasi_sewing.buyer,
                    inject_mutasi_sewing.ws,
                    inject_mutasi_sewing.styleno,
                    inject_mutasi_sewing.color,
                    inject_mutasi_sewing.size,
                    SUM(COALESCE(saldo_awal_sewing, 0)) as saldo_awal_sewing,
                    SUM(COALESCE(qty_loading, 0)) as qty_loading,
                    SUM(COALESCE(input_rework_sewing, 0)) as input_rework_sewing,
                    SUM(COALESCE(input_rework_spotcleaning, 0)) as input_rework_spotcleaning,
                    SUM(COALESCE(input_rework_mending, 0)) as input_rework_mending,
                    SUM(COALESCE(defect_sewing, 0)) as defect_sewing,
                    SUM(COALESCE(defect_spotcleaning, 0)) as defect_spotcleaning,
                    SUM(COALESCE(defect_mending, 0)) as defect_mending,
                    SUM(COALESCE(qty_sew_reject, 0)) as qty_sew_reject,
                    SUM(COALESCE(qty_sewing, 0)) as qty_sewing,
                    -- SEWING AKHIR
                    SUM(COALESCE(saldo_awal_sewing, 0) +
                    COALESCE(qty_loading, 0) +
                    COALESCE(input_rework_sewing, 0) +
                    COALESCE(input_rework_mending, 0) +
                    COALESCE(input_rework_spotcleaning, 0) -
                    COALESCE(defect_sewing, 0) -
                    COALESCE(defect_spotcleaning, 0) -
                    COALESCE(defect_mending, 0) -
                    COALESCE(qty_sew_reject, 0) -
                    COALESCE(qty_sewing, 0))
                    AS saldo_akhir_sewing,
                    -- FINISHING AWAL
                    SUM(COALESCE(saldo_awal_finishing, 0)) as saldo_awal_finishing,
                    SUM(COALESCE(input_rework_sewing_f, 0)) as input_rework_sewing_f,
                    SUM(COALESCE(input_rework_spotcleaning_f, 0)) as input_rework_spotcleaning_f,
                    SUM(COALESCE(input_rework_mending_f, 0)) as input_rework_mending_f,
                    SUM(COALESCE(defect_sewing_f, 0)) as defect_sewing_f,
                    SUM(COALESCE(defect_spotcleaning_f, 0)) as defect_spotcleaning_f,
                    SUM(COALESCE(defect_mending_f, 0)) as defect_mending_f,
                    SUM(COALESCE(qty_fin_reject, 0)) as qty_fin_reject,
                    SUM(COALESCE(qty_finishing, 0)) as qty_finishing,
                    -- FINISHING AKHIR
                    SUM(COALESCE(saldo_awal_finishing, 0) +
                    COALESCE(qty_sewing, 0) +
                    COALESCE(input_rework_sewing_f, 0) +
                    COALESCE(input_rework_spotcleaning_f, 0) +
                    COALESCE(input_rework_mending_f, 0) -
                    COALESCE(defect_sewing_f, 0) -
                    COALESCE(defect_spotcleaning_f, 0) -
                    COALESCE(defect_mending_f, 0) -
                    COALESCE(qty_fin_reject, 0) -
                    COALESCE(qty_finishing, 0)) as saldo_akhir_finishing,
                    -- SECONDARY PROCESS AWAL
                    SUM(COALESCE(saldo_awal_secondary_proses, 0)) as saldo_awal_secondary_proses,
                    SUM(COALESCE(total_in_sp, 0)) as total_in_sp,
                    SUM(COALESCE(rework_sp, 0)) as rework_sp,
                    SUM(COALESCE(defect_sp, 0)) as defect_sp,
                    SUM(COALESCE(reject_sp, 0)) as reject_sp,
                    SUM(COALESCE(rft_sp, 0)) as rft_sp,
                    -- SECONDARY PROCESS AKHIR
                    SUM(COALESCE(total_in_sp, 0) +
                    COALESCE(rework_sp, 0) -
                    COALESCE(defect_sp, 0) -
                    COALESCE(reject_sp, 0) -
                    COALESCE(rft_sp, 0)) as saldo_akhir_secondary_proses,
                    -- DEFECT AWAL
                    SUM(COALESCE(saldo_awal_defect_sewing, 0)) as saldo_awal_defect_sewing,
                    SUM(COALESCE(total_defect_sewing, 0)) as total_defect_sewing,
                    SUM(COALESCE(total_input_rework_sewing, 0)) as total_input_rework_sewing,
                    -- DEFECT AKHIR
                    SUM(COALESCE(saldo_awal_defect_sewing, 0) +
                    COALESCE(total_defect_sewing, 0) -
                    COALESCE(total_input_rework_sewing, 0)) as saldo_akhir_defect_sewing,
                    -- SPOTCLEANING AWAL
                    SUM(COALESCE(saldo_awal_defect_spotcleaning, 0)) as saldo_awal_defect_spotcleaning,
                    SUM(COALESCE(total_defect_spotcleaning, 0)) as total_defect_spotcleaning,
                    SUM(COALESCE(total_input_rework_spotcleaning, 0)) as total_input_rework_spotcleaning,
                    -- SPOTCLEANING AKHIR
                    SUM(COALESCE(saldo_awal_defect_spotcleaning, 0) +
                    COALESCE(total_defect_spotcleaning, 0) -
                    COALESCE(total_input_rework_spotcleaning, 0)) as saldo_akhir_defect_spotcleaning,
                    -- MENDING AWAL
                    SUM(COALESCE(saldo_awal_defect_mending, 0)) as saldo_awal_defect_mending,
                    SUM(COALESCE(total_defect_mending, 0)) as total_defect_mending,
                    SUM(COALESCE(total_input_rework_mending, 0)) as total_input_rework_mending,
                    SUM(COALESCE(saldo_awal_defect_mending, 0) +
                    COALESCE(total_defect_mending, 0) -
                    COALESCE(total_input_rework_mending, 0)) saldo_akhir_mending,
                    SUM(COALESCE(saldo_awal_reject, 0)) as saldo_awal_reject,
                    SUM(COALESCE(qty_reject_in, 0)) as qty_reject_in,
                    SUM(COALESCE(qty_reworked, 0)) as qty_reworked,
                    SUM(COALESCE(qty_rejected, 0)) as qty_rejected,
                    SUM(COALESCE(saldo_awal_reject, 0)
                    + COALESCE(qty_reject_in, 0)
                    - COALESCE(qty_rejected, 0)
                    - COALESCE(qty_reworked, 0))  as saldo_akhir_qc_reject,
                    0 AS qty_transit_terima_sewing_before,
                    0 AS qty_transit_terima_qc_finishing_before,
                    0 AS qty_transit_terima_finishing_before,
                    0 AS qty_transit_keluar_qc_reject_before,

                    0 AS saldo_awal_sp_pasang_kancing,
                    0 AS saldo_awal_sp_bartack,
                    0 AS saldo_awal_sp_heatseal,
                    0 AS saldo_awal_sp_snap,
                    0 AS saldo_awal_sp_embro,

                    SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN COALESCE(total_in_sp, 0) ELSE 0 END) AS total_in_sp_pasang_kancing,
                    SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN COALESCE(rft_sp, 0) ELSE 0 END) AS rft_sp_pasang_kancing,
                    SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN COALESCE(defect_sp, 0) ELSE 0 END) AS defect_sp_pasang_kancing,
                    SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN COALESCE(rework_sp, 0) ELSE 0 END) AS rework_sp_pasang_kancing,
                    SUM(CASE WHEN spm.secondary_process = 'Pasang Kancing' THEN COALESCE(reject_sp, 0) ELSE 0 END) AS reject_sp_pasang_kancing,
                    SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN COALESCE(total_in_sp, 0) ELSE 0 END) AS total_in_sp_bartack,
                    SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN COALESCE(rft_sp, 0) ELSE 0 END) AS rft_sp_bartack,
                    SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN COALESCE(defect_sp, 0) ELSE 0 END) AS defect_sp_bartack,
                    SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN COALESCE(rework_sp, 0) ELSE 0 END) AS rework_sp_bartack,
                    SUM(CASE WHEN spm.secondary_process = 'Bartack' THEN COALESCE(reject_sp, 0) ELSE 0 END) AS reject_sp_bartack,
                    SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN COALESCE(total_in_sp, 0) ELSE 0 END) AS total_in_sp_heatseal,
                    SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN COALESCE(rft_sp, 0) ELSE 0 END) AS rft_sp_heatseal,
                    SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN COALESCE(defect_sp, 0) ELSE 0 END) AS defect_sp_heatseal,
                    SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN COALESCE(rework_sp, 0) ELSE 0 END) AS rework_sp_heatseal,
                    SUM(CASE WHEN spm.secondary_process = 'Heatseal' THEN COALESCE(reject_sp, 0) ELSE 0 END) AS reject_sp_heatseal,
                    SUM(CASE WHEN spm.secondary_process = 'Snap' THEN COALESCE(total_in_sp, 0) ELSE 0 END) AS total_in_sp_snap,
                    SUM(CASE WHEN spm.secondary_process = 'Snap' THEN COALESCE(rft_sp, 0) ELSE 0 END) AS rft_sp_snap,
                    SUM(CASE WHEN spm.secondary_process = 'Snap' THEN COALESCE(defect_sp, 0) ELSE 0 END) AS defect_sp_snap,
                    SUM(CASE WHEN spm.secondary_process = 'Snap' THEN COALESCE(rework_sp, 0) ELSE 0 END) AS rework_sp_snap,
                    SUM(CASE WHEN spm.secondary_process = 'Snap' THEN COALESCE(reject_sp, 0) ELSE 0 END) AS reject_sp_snap,
                    SUM(CASE WHEN spm.secondary_process = 'Embro' THEN COALESCE(total_in_sp, 0) ELSE 0 END) AS total_in_sp_embro,
                    SUM(CASE WHEN spm.secondary_process = 'Embro' THEN COALESCE(rft_sp, 0) ELSE 0 END) AS rft_sp_embro,
                    SUM(CASE WHEN spm.secondary_process = 'Embro' THEN COALESCE(defect_sp, 0) ELSE 0 END) AS defect_sp_embro,
                    SUM(CASE WHEN spm.secondary_process = 'Embro' THEN COALESCE(rework_sp, 0) ELSE 0 END) AS rework_sp_embro,
                    SUM(CASE WHEN spm.secondary_process = 'Embro' THEN COALESCE(reject_sp, 0) ELSE 0 END) AS reject_sp_embro

                FROM
                    inject_mutasi_sewing
                LEFT JOIN secondary_process_mapping spm
                ON spm.buyer = inject_mutasi_sewing.buyer
                AND spm.ws = inject_mutasi_sewing.ws
                AND spm.styleno = inject_mutasi_sewing.styleno
                AND spm.color = inject_mutasi_sewing.color
                AND spm.size = inject_mutasi_sewing.size
                WHERE
                    tgl_saldo >= '$start_date' AND tgl_saldo <= '$end_date'
                GROUP BY
                    inject_mutasi_sewing.buyer, inject_mutasi_sewing.ws, inject_mutasi_sewing.styleno, inject_mutasi_sewing.color, inject_mutasi_sewing.size
                HAVING NOT (
                    saldo_awal_sewing = 0 AND
                    qty_loading = 0 AND
                    input_rework_sewing = 0 AND
                    input_rework_spotcleaning = 0 AND
                    input_rework_mending = 0 AND
                    defect_sewing = 0 AND
                    defect_spotcleaning = 0 AND
                    defect_mending = 0 AND
                    qty_sew_reject = 0 AND
                    qty_sewing = 0 AND
                    saldo_akhir_sewing = 0 AND
                    saldo_awal_finishing = 0 AND
                    input_rework_sewing_f = 0 AND
                    input_rework_spotcleaning_f = 0 AND
                    input_rework_mending_f = 0 AND
                    defect_sewing_f = 0 AND
                    defect_spotcleaning_f = 0 AND
                    defect_mending_f = 0 AND
                    qty_fin_reject = 0 AND
                    qty_finishing = 0 AND
                    saldo_akhir_finishing = 0 AND
                    saldo_awal_secondary_proses = 0 AND
                    total_in_sp = 0 AND
                    rework_sp = 0 AND
                    defect_sp = 0 AND
                    reject_sp = 0 AND
                    rft_sp = 0 AND
                    saldo_akhir_secondary_proses = 0 AND
                    saldo_awal_defect_sewing = 0 AND
                    total_defect_sewing = 0 AND
                    total_input_rework_sewing = 0 AND
                    saldo_akhir_defect_sewing = 0 AND
                    saldo_awal_defect_spotcleaning = 0 AND
                    total_defect_spotcleaning = 0 AND
                    total_input_rework_spotcleaning = 0 AND
                    saldo_akhir_defect_spotcleaning = 0 AND
                    saldo_awal_defect_mending = 0 AND
                    total_defect_mending = 0 AND
                    total_input_rework_mending = 0 AND
                    saldo_akhir_mending = 0 AND
                    saldo_awal_reject = 0 AND
                    qty_reject_in = 0 AND
                    qty_rejected = 0 AND
                    qty_reworked = 0 AND
                    saldo_akhir_qc_reject = 0 AND
                    qty_transit_terima_sewing_before = 0 AND
                    qty_transit_terima_qc_finishing_before = 0 AND
                    qty_transit_terima_finishing_before = 0 AND
                    qty_transit_keluar_qc_reject_before = 0 AND
                    saldo_awal_sp_pasang_kancing = 0 AND
                    saldo_awal_sp_bartack = 0 AND
                    saldo_awal_sp_heatseal = 0 AND
                    saldo_awal_sp_snap = 0 AND
                    saldo_awal_sp_embro = 0 AND
                    total_in_sp_pasang_kancing = 0 AND
                    rft_sp_pasang_kancing = 0 AND
                    defect_sp_pasang_kancing = 0 AND
                    rework_sp_pasang_kancing = 0 AND
                    reject_sp_pasang_kancing = 0 AND
                    total_in_sp_bartack = 0 AND
                    rft_sp_bartack = 0 AND
                    defect_sp_bartack = 0 AND
                    rework_sp_bartack = 0 AND
                    reject_sp_bartack = 0 AND
                    total_in_sp_heatseal = 0 AND
                    rft_sp_heatseal = 0 AND
                    defect_sp_heatseal = 0 AND
                    rework_sp_heatseal = 0 AND
                    reject_sp_heatseal = 0 AND
                    total_in_sp_snap = 0 AND
                    rft_sp_snap = 0 AND
                    defect_sp_snap = 0 AND
                    rework_sp_snap = 0 AND
                    reject_sp_snap = 0 AND
                    total_in_sp_embro = 0 AND
                    rft_sp_embro = 0 AND
                    defect_sp_embro = 0 AND
                    rework_sp_embro = 0 AND
                    reject_sp_embro = 0
                )
            ),

            query_fix as (SELECT
                    buyer,
                    ws,
                    styleno,
                    color,
                    size,
                    0 terima_gudang_before,
                    0 terima_gudang,
                    0 qty_in_before,
                    0 qty_in,
                    0 qty_out_before,
                    0 qty_out,
                    SUM(saldo_awal_sewing) as saldo_awal_sewing,
                    SUM(qty_loading) as qty_loading,
                    SUM(input_rework_sewing) as input_rework_sewing,
                    SUM(input_rework_spotcleaning) as input_rework_spotcleaning,
                    SUM(input_rework_mending) as input_rework_mending,
                    SUM(defect_sewing) as defect_sewing,
                    SUM(defect_spotcleaning) as defect_spotcleaning,
                    SUM(defect_mending) as defect_mending,
                    SUM(qty_sew_reject) as qty_sew_reject,
                    SUM(qty_sewing) as qty_sewing,
                    SUM(saldo_akhir_sewing) as saldo_akhir_sewing,
                    SUM(saldo_awal_finishing) as saldo_awal_finishing,
                    SUM(input_rework_sewing_f) as input_rework_sewing_f,
                    SUM(input_rework_spotcleaning_f) as input_rework_spotcleaning_f,
                    SUM(input_rework_mending_f) as input_rework_mending_f,
                    SUM(defect_sewing_f) as defect_sewing_f,
                    SUM(defect_spotcleaning_f) as defect_spotcleaning_f,
                    SUM(defect_mending_f) as defect_mending_f,
                    SUM(qty_fin_reject) as qty_fin_reject,
                    SUM(qty_finishing) as qty_finishing,
                    SUM(saldo_akhir_finishing) as saldo_akhir_finishing,
                    SUM(saldo_awal_secondary_proses) as saldo_awal_secondary_proses,
                    SUM(total_in_sp) as total_in_sp,
                    SUM(rework_sp) as rework_sp,
                    SUM(defect_sp) as defect_sp,
                    SUM(reject_sp) as reject_sp,
                    SUM(rft_sp) as rft_sp,
                    SUM(saldo_akhir_secondary_proses) as saldo_akhir_secondary_proses,
                    SUM(saldo_awal_defect_sewing) as saldo_awal_defect_sewing,
                    SUM(total_defect_sewing) as total_defect_sewing,
                    SUM(total_input_rework_sewing) as total_input_rework_sewing,
                    SUM(saldo_akhir_defect_sewing) as saldo_akhir_defect_sewing,
                    SUM(saldo_awal_defect_spotcleaning) as saldo_awal_defect_spotcleaning,
                    SUM(total_defect_spotcleaning) as total_defect_spotcleaning,
                    SUM(total_input_rework_spotcleaning) as total_input_rework_spotcleaning,
                    SUM(saldo_akhir_defect_spotcleaning) as saldo_akhir_defect_spotcleaning,
                    SUM(saldo_awal_defect_mending) as saldo_awal_defect_mending,
                    SUM(total_defect_mending) as total_defect_mending,
                    SUM(total_input_rework_mending) as total_input_rework_mending,
                    SUM(saldo_akhir_mending) as saldo_akhir_mending,
                    SUM(saldo_awal_reject) as saldo_awal_reject,
                    SUM(qty_reject_in) as qty_reject_in,
                    SUM(qty_reworked) as qty_reworked,
                    SUM(qty_rejected) as qty_rejected,
                    SUM(saldo_akhir_qc_reject) as saldo_akhir_qc_reject,
                    0 loading_inject_bef,
                    0 loading_inject,
                    SUM(qty_transit_terima_sewing_before) AS qty_transit_terima_sewing_before,
                    SUM(qty_transit_terima_qc_finishing_before) AS qty_transit_terima_qc_finishing_before,
                    SUM(qty_transit_terima_finishing_before) AS qty_transit_terima_finishing_before,
                    SUM(qty_transit_keluar_qc_reject_before) AS qty_transit_keluar_qc_reject_before,

                    SUM(saldo_awal_sp_pasang_kancing) AS saldo_awal_sp_pasang_kancing,
                    SUM(saldo_awal_sp_bartack) AS saldo_awal_sp_bartack,
                    SUM(saldo_awal_sp_heatseal) AS saldo_awal_sp_heatseal,
                    SUM(saldo_awal_sp_snap) AS saldo_awal_sp_snap,
                    SUM(saldo_awal_sp_embro) AS saldo_awal_sp_embro,

                    SUM(total_in_sp_pasang_kancing) AS total_in_sp_pasang_kancing,
                    SUM(rft_sp_pasang_kancing) AS rft_sp_pasang_kancing,
                    SUM(defect_sp_pasang_kancing) AS defect_sp_pasang_kancing,
                    SUM(rework_sp_pasang_kancing) AS rework_sp_pasang_kancing,
                    SUM(reject_sp_pasang_kancing) AS reject_sp_pasang_kancing,
                    SUM(total_in_sp_bartack) AS total_in_sp_bartack,
                    SUM(rft_sp_bartack) AS rft_sp_bartack,
                    SUM(defect_sp_bartack) AS defect_sp_bartack,
                    SUM(rework_sp_bartack) AS rework_sp_bartack,
                    SUM(reject_sp_bartack) AS reject_sp_bartack,
                    SUM(total_in_sp_heatseal) AS total_in_sp_heatseal,
                    SUM(rft_sp_heatseal) AS rft_sp_heatseal,
                    SUM(defect_sp_heatseal) AS defect_sp_heatseal,
                    SUM(rework_sp_heatseal) AS rework_sp_heatseal,
                    SUM(reject_sp_heatseal) AS reject_sp_heatseal,
                    SUM(total_in_sp_snap) AS total_in_sp_snap,
                    SUM(rft_sp_snap) AS rft_sp_snap,
                    SUM(defect_sp_snap) AS defect_sp_snap,
                    SUM(rework_sp_snap) AS rework_sp_snap,
                    SUM(reject_sp_snap) AS reject_sp_snap,
                    SUM(total_in_sp_embro) AS total_in_sp_embro,
                    SUM(rft_sp_embro) AS rft_sp_embro,
                    SUM(defect_sp_embro) AS defect_sp_embro,
                    SUM(rework_sp_embro) AS rework_sp_embro,
                    SUM(reject_sp_embro) AS reject_sp_embro
                FROM (

                    select
                        buyer,
                        ws,
                        styleno,
                        color,
                        mut.size,
                        SUM(saldo_awal_sewing) AS saldo_awal_sewing,
                        SUM(qty_loading) AS qty_loading,
                        SUM(input_rework_sewing) AS input_rework_sewing,
                        SUM(input_rework_spotcleaning) AS input_rework_spotcleaning,
                        SUM(input_rework_mending) AS input_rework_mending,
                        SUM(defect_sewing) AS defect_sewing,
                        SUM(defect_spotcleaning) AS defect_spotcleaning,
                        SUM(defect_mending) AS defect_mending,
                        SUM(qty_sew_reject) AS qty_sew_reject,
                        SUM(qty_sewing) AS qty_sewing,
                        SUM(saldo_akhir_sewing) AS saldo_akhir_sewing,
                        SUM(saldo_awal_finishing) AS saldo_awal_finishing,
                        SUM(input_rework_sewing_f) AS input_rework_sewing_f,
                        SUM(input_rework_spotcleaning_f) AS input_rework_spotcleaning_f,
                        SUM(input_rework_mending_f) AS input_rework_mending_f,
                        SUM(defect_sewing_f) AS defect_sewing_f,
                        SUM(defect_spotcleaning_f) AS defect_spotcleaning_f,
                        SUM(defect_mending_f) AS defect_mending_f,
                        SUM(qty_fin_reject) AS qty_fin_reject,
                        SUM(qty_finishing) AS qty_finishing,
                        SUM(saldo_akhir_finishing) AS saldo_akhir_finishing,
                        SUM(saldo_awal_secondary_proses) AS saldo_awal_secondary_proses,
                        SUM(total_in_sp) AS total_in_sp,
                        SUM(rework_sp) AS rework_sp,
                        SUM(defect_sp) AS defect_sp,
                        SUM(reject_sp) AS reject_sp,
                        SUM(rft_sp) AS rft_sp,
                        SUM(saldo_akhir_secondary_proses) AS saldo_akhir_secondary_proses,
                        SUM(saldo_awal_defect_sewing) AS saldo_awal_defect_sewing,
                        SUM(total_defect_sewing) AS total_defect_sewing,
                        SUM(total_input_rework_sewing) AS total_input_rework_sewing,
                        SUM(saldo_akhir_defect_sewing) AS saldo_akhir_defect_sewing,
                        SUM(saldo_awal_defect_spotcleaning) AS saldo_awal_defect_spotcleaning,
                        SUM(total_defect_spotcleaning) AS total_defect_spotcleaning,
                        SUM(total_input_rework_spotcleaning) AS total_input_rework_spotcleaning,
                        SUM(saldo_akhir_defect_spotcleaning) AS saldo_akhir_defect_spotcleaning,
                        SUM(saldo_awal_defect_mending) AS saldo_awal_defect_mending,
                        SUM(total_defect_mending) AS total_defect_mending,
                        SUM(total_input_rework_mending) AS total_input_rework_mending,
                        SUM(saldo_akhir_mending) AS saldo_akhir_mending,
                        SUM(saldo_awal_reject) AS saldo_awal_reject,
                        SUM(qty_reject_in) AS qty_reject_in,
                        SUM(qty_reworked) AS qty_reworked,
                        SUM(qty_rejected) AS qty_rejected,
                        SUM(saldo_akhir_qc_reject) AS saldo_akhir_qc_reject,
                        SUM(qty_transit_terima_sewing_before) AS qty_transit_terima_sewing_before,
                        SUM(qty_transit_terima_qc_finishing_before) AS qty_transit_terima_qc_finishing_before,
                        SUM(qty_transit_terima_finishing_before) AS qty_transit_terima_finishing_before,
                        SUM(qty_transit_keluar_qc_reject_before) AS qty_transit_keluar_qc_reject_before,

                        SUM(saldo_awal_sp_pasang_kancing) AS saldo_awal_sp_pasang_kancing,
                        SUM(saldo_awal_sp_bartack) AS saldo_awal_sp_bartack,
                        SUM(saldo_awal_sp_heatseal) AS saldo_awal_sp_heatseal,
                        SUM(saldo_awal_sp_snap) AS saldo_awal_sp_snap,
                        SUM(saldo_awal_sp_embro) AS saldo_awal_sp_embro,

                        SUM(total_in_sp_pasang_kancing) AS total_in_sp_pasang_kancing,
                        SUM(rft_sp_pasang_kancing) AS rft_sp_pasang_kancing,
                        SUM(defect_sp_pasang_kancing) AS defect_sp_pasang_kancing,
                        SUM(rework_sp_pasang_kancing) AS rework_sp_pasang_kancing,
                        SUM(reject_sp_pasang_kancing) AS reject_sp_pasang_kancing,
                        SUM(total_in_sp_bartack) AS total_in_sp_bartack,
                        SUM(rft_sp_bartack) AS rft_sp_bartack,
                        SUM(defect_sp_bartack) AS defect_sp_bartack,
                        SUM(rework_sp_bartack) AS rework_sp_bartack,
                        SUM(reject_sp_bartack) AS reject_sp_bartack,
                        SUM(total_in_sp_heatseal) AS total_in_sp_heatseal,
                        SUM(rft_sp_heatseal) AS rft_sp_heatseal,
                        SUM(defect_sp_heatseal) AS defect_sp_heatseal,
                        SUM(rework_sp_heatseal) AS rework_sp_heatseal,
                        SUM(reject_sp_heatseal) AS reject_sp_heatseal,
                        SUM(total_in_sp_snap) AS total_in_sp_snap,
                        SUM(rft_sp_snap) AS rft_sp_snap,
                        SUM(defect_sp_snap) AS defect_sp_snap,
                        SUM(rework_sp_snap) AS rework_sp_snap,
                        SUM(reject_sp_snap) AS reject_sp_snap,
                        SUM(total_in_sp_embro) AS total_in_sp_embro,
                        SUM(rft_sp_embro) AS rft_sp_embro,
                        SUM(defect_sp_embro) AS defect_sp_embro,
                        SUM(rework_sp_embro) AS rework_sp_embro,
                        SUM(reject_sp_embro) AS reject_sp_embro
                    FROM mut
                    LEFT JOIN signalbit_erp.master_size_new msn on mut.size = msn.size
                    GROUP BY buyer, ws, styleno, color, size

                    UNION ALL

                    select
                        buyer,
                        ws,
                        styleno,
                        color,
                        size,
                        SUM(COALESCE(saldo_awal_sewing, 0)) as saldo_awal_sewing,
                        SUM(COALESCE(qty_loading, 0)) as qty_loading,
                        SUM(COALESCE(input_rework_sewing, 0)) as input_rework_sewing,
                        SUM(COALESCE(input_rework_spotcleaning, 0)) as input_rework_spotcleaning,
                        SUM(COALESCE(input_rework_mending, 0)) as input_rework_mending,
                        SUM(COALESCE(defect_sewing, 0)) as defect_sewing,
                        SUM(COALESCE(defect_spotcleaning, 0)) as defect_spotcleaning,
                        SUM(COALESCE(defect_mending, 0)) as defect_mending,
                        SUM(COALESCE(qty_sew_reject, 0)) as qty_sew_reject,
                        SUM(COALESCE(qty_sewing, 0)) as qty_sewing,
                        -- SEWING AKHIR
                        SUM(COALESCE(saldo_awal_sewing, 0) +
                        COALESCE(qty_loading, 0) +
                        COALESCE(input_rework_sewing, 0) +
                        COALESCE(input_rework_mending, 0) +
                        COALESCE(input_rework_spotcleaning, 0) -
                        COALESCE(defect_sewing, 0) -
                        COALESCE(defect_spotcleaning, 0) -
                        COALESCE(defect_mending, 0) -
                        COALESCE(qty_sew_reject, 0) -
                        COALESCE(qty_sewing, 0))
                        AS saldo_akhir_sewing,
                        -- FINISHING AWAL
                        SUM(COALESCE(saldo_awal_finishing, 0)) as saldo_awal_finishing,
                        SUM(COALESCE(input_rework_sewing_f, 0)) as input_rework_sewing_f,
                        SUM(COALESCE(input_rework_spotcleaning_f, 0)) as input_rework_spotcleaning_f,
                        SUM(COALESCE(input_rework_mending_f, 0)) as input_rework_mending_f,
                        SUM(COALESCE(defect_sewing_f, 0)) as defect_sewing_f,
                        SUM(COALESCE(defect_spotcleaning_f, 0)) as defect_spotcleaning_f,
                        SUM(COALESCE(defect_mending_f, 0)) as defect_mending_f,
                        SUM(COALESCE(qty_fin_reject, 0)) as qty_fin_reject,
                        SUM(COALESCE(qty_finishing, 0)) as qty_finishing,
                        -- FINISHING AKHIR
                        SUM(COALESCE(saldo_awal_finishing, 0) +
                        COALESCE(qty_sewing, 0) +
                        COALESCE(input_rework_sewing_f, 0) +
                        COALESCE(input_rework_spotcleaning_f, 0) +
                        COALESCE(input_rework_mending_f, 0) -
                        COALESCE(defect_sewing_f, 0) -
                        COALESCE(defect_spotcleaning_f, 0) -
                        COALESCE(defect_mending_f, 0) -
                        COALESCE(qty_fin_reject, 0) -
                        COALESCE(qty_finishing, 0)) as saldo_akhir_finishing,
                        -- SECONDARY PROCESS AWAL
                        SUM(COALESCE(saldo_awal_secondary_proses, 0)) as saldo_awal_secondary_proses,
                        SUM(COALESCE(total_in_sp, 0)) as total_in_sp,
                        SUM(COALESCE(rework_sp, 0)) as rework_sp,
                        SUM(COALESCE(defect_sp, 0)) as defect_sp,
                        SUM(COALESCE(reject_sp, 0)) as reject_sp,
                        SUM(COALESCE(rft_sp, 0)) as rft_sp,
                        -- SECONDARY PROCESS AKHIR
                        SUM(COALESCE(total_in_sp, 0) +
                        COALESCE(rework_sp, 0) -
                        COALESCE(defect_sp, 0) -
                        COALESCE(reject_sp, 0) -
                        COALESCE(rft_sp, 0)) as saldo_akhir_secondary_proses,
                        -- DEFECT AWAL
                        SUM(COALESCE(saldo_awal_defect_sewing, 0)) as saldo_awal_defect_sewing,
                        SUM(COALESCE(total_defect_sewing, 0)) as total_defect_sewing,
                        SUM(COALESCE(total_input_rework_sewing, 0)) as total_input_rework_sewing,
                        -- DEFECT AKHIR
                        SUM(COALESCE(saldo_awal_defect_sewing, 0) +
                        COALESCE(total_defect_sewing, 0) -
                        COALESCE(total_input_rework_sewing, 0)) as saldo_akhir_defect_sewing,
                        -- SPOTCLEANING AWAL
                        SUM(COALESCE(saldo_awal_defect_spotcleaning, 0)) as saldo_awal_defect_spotcleaning,
                        SUM(COALESCE(total_defect_spotcleaning, 0)) as total_defect_spotcleaning,
                        SUM(COALESCE(total_input_rework_spotcleaning, 0)) as total_input_rework_spotcleaning,
                        -- SPOTCLEANING AKHIR
                        SUM(COALESCE(saldo_awal_defect_spotcleaning, 0) +
                        COALESCE(total_defect_spotcleaning, 0) -
                        COALESCE(total_input_rework_spotcleaning, 0)) as saldo_akhir_defect_spotcleaning,
                        -- MENDING AWAL
                        SUM(COALESCE(saldo_awal_defect_mending, 0)) as saldo_awal_defect_mending,
                        SUM(COALESCE(total_defect_mending, 0)) as total_defect_mending,
                        SUM(COALESCE(total_input_rework_mending, 0)) as total_input_rework_mending,
                        SUM(COALESCE(saldo_awal_defect_mending, 0) +
                        COALESCE(total_defect_mending, 0) -
                        COALESCE(total_input_rework_mending, 0)) saldo_akhir_mending,
                        SUM(COALESCE(saldo_awal_reject, 0)) as saldo_awal_reject,
                        SUM(COALESCE(qty_reject_in, 0)) as qty_reject_in,
                        SUM(COALESCE(qty_reworked, 0)) as qty_reworked,
                        SUM(COALESCE(qty_rejected, 0)) as qty_rejected,
                        SUM(COALESCE(saldo_awal_reject, 0)
                        + COALESCE(qty_reject_in, 0)
                        - COALESCE(qty_rejected, 0)
                        - COALESCE(qty_reworked, 0))  as saldo_akhir_qc_reject,
                        SUM(COALESCE(qty_transit_terima_sewing_before, 0)) AS qty_transit_terima_sewing_before,
                        SUM(COALESCE(qty_transit_terima_qc_finishing_before, 0)) AS qty_transit_terima_qc_finishing_before,
                        SUM(COALESCE(qty_transit_terima_finishing_before, 0)) AS qty_transit_terima_finishing_before,
                        SUM(COALESCE(qty_transit_keluar_qc_reject_before, 0)) AS qty_transit_keluar_qc_reject_before,

                        SUM(COALESCE(saldo_awal_sp_pasang_kancing, 0)) AS saldo_awal_sp_pasang_kancing,
                        SUM(COALESCE(saldo_awal_sp_bartack, 0)) AS saldo_awal_sp_bartack,
                        SUM(COALESCE(saldo_awal_sp_heatseal, 0)) AS saldo_awal_sp_heatseal,
                        SUM(COALESCE(saldo_awal_sp_snap, 0)) AS saldo_awal_sp_snap,
                        SUM(COALESCE(saldo_awal_sp_embro, 0)) AS saldo_awal_sp_embro,

                        SUM(COALESCE(total_in_sp_pasang_kancing, 0)) AS total_in_sp_pasang_kancing,
                        SUM(COALESCE(rft_sp_pasang_kancing, 0)) AS rft_sp_pasang_kancing,
                        SUM(COALESCE(defect_sp_pasang_kancing, 0)) AS defect_sp_pasang_kancing,
                        SUM(COALESCE(rework_sp_pasang_kancing, 0)) AS rework_sp_pasang_kancing,
                        SUM(COALESCE(reject_sp_pasang_kancing, 0)) AS reject_sp_pasang_kancing,
                        SUM(COALESCE(total_in_sp_bartack, 0)) AS total_in_sp_bartack,
                        SUM(COALESCE(rft_sp_bartack, 0)) AS rft_sp_bartack,
                        SUM(COALESCE(defect_sp_bartack, 0)) AS defect_sp_bartack,
                        SUM(COALESCE(rework_sp_bartack, 0)) AS rework_sp_bartack,
                        SUM(COALESCE(reject_sp_bartack, 0)) AS reject_sp_bartack,
                        SUM(COALESCE(total_in_sp_heatseal, 0)) AS total_in_sp_heatseal,
                        SUM(COALESCE(rft_sp_heatseal, 0)) AS rft_sp_heatseal,
                        SUM(COALESCE(defect_sp_heatseal, 0)) AS defect_sp_heatseal,
                        SUM(COALESCE(rework_sp_heatseal, 0)) AS rework_sp_heatseal,
                        SUM(COALESCE(reject_sp_heatseal, 0)) AS reject_sp_heatseal,
                        SUM(COALESCE(total_in_sp_snap, 0)) AS total_in_sp_snap,
                        SUM(COALESCE(rft_sp_snap, 0)) AS rft_sp_snap,
                        SUM(COALESCE(defect_sp_snap, 0)) AS defect_sp_snap,
                        SUM(COALESCE(rework_sp_snap, 0)) AS rework_sp_snap,
                        SUM(COALESCE(reject_sp_snap, 0)) AS reject_sp_snap,
                        SUM(COALESCE(total_in_sp_embro, 0)) AS total_in_sp_embro,
                        SUM(COALESCE(rft_sp_embro, 0)) AS rft_sp_embro,
                        SUM(COALESCE(defect_sp_embro, 0)) AS defect_sp_embro,
                        SUM(COALESCE(rework_sp_embro, 0)) AS rework_sp_embro,
                        SUM(COALESCE(reject_sp_embro, 0)) AS reject_sp_embro
                    from
                    (
                        SELECT
                            *
                        FROM
                            current_inject
                        UNION ALL
                        SELECT
                            *
                        FROM
                            saldo_awal_inject
                    ) inject
                    GROUP BY
                        buyer, ws, styleno, color, size
                ) data
                group by buyer, ws, styleno, color, size
                order by buyer asc, ws asc, color asc, size asc
            ),

            in_subcont_before as (
                select
                    b.id_item,
                    a.no_po,
                    supplier,
                    buyer,
                    kpno,
                    styleno,
                    b.color,
                    b.size,
                    sum(
                    COALESCE ( b.qty, 0 )) qty_in
                from
                    packing_in_h a
                    INNER JOIN packing_in_det b on b.no_bpb = a.no_bpb
                    INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier
                    left join (
                    select
                        id_jo,
                        kpno,
                        styleno,
                        supplier buyer
                    from
                        act_costing ac
                        inner join so on ac.id = so.id_cost
                        inner join jo_det jod on so.id = jod.id_so
                        INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer
                    group by
                        id_jo
                    ) d on d.id_jo = b.id_jo
                where
                    a.tgl_bpb < '$start_date'
                    and b.status = 'Y' $filter_subcont
                GROUP BY
                    a.no_po,
                    kpno,
                    b.color,
                    b.size,
                    b.id_item
            ),

            out_subcont_before as (
                select
                    b.id_item,
                    itemdesc,
                    a.no_po,
                    supplier,
                    buyer,
                    kpno,
                    styleno,
                    b.color,
                    b.size,
                    sum(
                    COALESCE ( b.qty, 0 )) qty_out
                from
                    packing_out_h a
                    INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb
                    INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier
                    left join (
                    select
                        id_jo,
                        kpno,
                        styleno,
                        supplier buyer
                    from
                        act_costing ac
                        inner join so on ac.id = so.id_cost
                        inner join jo_det jod on so.id = jod.id_so
                        INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer
                    group by
                        id_jo
                    ) d on d.id_jo = b.id_jo
                    INNER JOIN masteritem mi on mi.id_item = b.id_item
                where
                    a.tgl_bppb < '$start_date'
                    and b.status = 'Y' $filter_subcont
                GROUP BY
                    a.no_po,
                    kpno,
                    b.color,
                    b.size,
                    b.id_item
            ),
            in_subcont_trx as (
                select
                    b.id_item,
                    a.no_po,
                    supplier,
                    buyer,
                    kpno,
                    styleno,
                    b.color,
                    b.size,
                    sum(
                    COALESCE ( b.qty, 0 )) qty_in
                from
                    packing_in_h a
                    INNER JOIN packing_in_det b on b.no_bpb = a.no_bpb
                    INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier
                    left join (
                    select
                        id_jo,
                        kpno,
                        styleno,
                        supplier buyer
                    from
                        act_costing ac
                        inner join so on ac.id = so.id_cost
                        inner join jo_det jod on so.id = jod.id_so
                        INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer
                    group by
                        id_jo
                    ) d on d.id_jo = b.id_jo
                where
                    a.tgl_bpb BETWEEN '$start_date'
                    and '$end_date'
                    and b.status = 'Y' $filter_subcont
                GROUP BY
                    a.no_po,
                    kpno,
                    b.color,
                    b.size,
                    b.id_item
            ),
            out_subcont_trx as (
                select
                    b.id_item,
                    itemdesc,
                    a.no_po,
                    supplier,
                    buyer,
                    kpno,
                    styleno,
                    b.color,
                    b.size,
                    sum(
                    COALESCE ( b.qty, 0 )) qty_out
                from
                    packing_out_h a
                    INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb
                    INNER JOIN mastersupplier c on c.id_supplier = a.id_supplier
                    left join (
                    select
                        id_jo,
                        kpno,
                        styleno,
                        supplier buyer
                    from
                        act_costing ac
                        inner join so on ac.id = so.id_cost
                        inner join jo_det jod on so.id = jod.id_so
                        INNER JOIN mastersupplier mb on mb.id_supplier = ac.id_buyer
                    group by
                        id_jo
                    ) d on d.id_jo = b.id_jo
                    INNER JOIN masteritem mi on mi.id_item = b.id_item
                where
                    a.tgl_bppb BETWEEN '$start_date'
                    and '$end_date'
                    and b.status = 'Y' $filter_subcont
                GROUP BY
                    a.no_po,
                    kpno,
                    b.color,
                    b.size,
                    b.id_item
            ),
            query_fix_subcont as (
                select
                        buyer,
                        ws,
                        styleno,
                        color,
                        size,
                        0 terima_gudang_before,
                        0 terima_gudang,
                        SUM( qty_in_before ) qty_in_before,
                        SUM( qty_in ) qty_in,
                        SUM( qty_out_before ) qty_out_before,
                        SUM( qty_out ) qty_out,
                        0 saldo_awal_sewing,
                        0 qty_loading,
                        0 input_rework_sewing,
                        0 input_rework_spotcleaning,
                        0 input_rework_mending,
                        0 defect_sewing,
                        0 defect_spotcleaning,
                        0 defect_mending,
                        0 qty_sew_reject,
                        0 qty_sewing,
                        0 saldo_akhir_sewing,
                        0 saldo_awal_finishing,
                        0 input_rework_sewing_f,
                        0 input_rework_spotcleaning_f,
                        0 input_rework_mending_f,
                        0 defect_sewing_f,
                        0 defect_spotcleaning_f,
                        0 defect_mending_f,
                        0 qty_fin_reject,
                        0 qty_finishing,
                        0 saldo_akhir_finishing,
                        0 saldo_awal_secondary_proses,
                        0 total_in_sp,
                        0 rework_sp,
                        0 defect_sp,
                        0 reject_sp,
                        0 rft_sp,
                        0 saldo_akhir_secondary_proses,
                        0 saldo_awal_defect_sewing,
                        0 total_defect_sewing,
                        0 total_input_rework_sewing,
                        0 saldo_akhir_defect_sewing,
                        0 saldo_awal_defect_spotcleaning,
                        0 total_defect_spotcleaning,
                        0 total_input_rework_spotcleaning,
                        0 saldo_akhir_defect_spotcleaning,
                        0 saldo_awal_defect_mending,
                        0 total_defect_mending,
                        0 total_input_rework_mending,
                        0 saldo_akhir_mending,
                        0 saldo_awal_reject,
                        0 qty_reject_in,
                        0 qty_reworked,
                        0 qty_rejected,
                        0 saldo_akhir_qc_reject,
                        0 loading_inject_bef,
                        0 loading_inject,
                        0 qty_transit_terima_sewing_before,
                        0 qty_transit_terima_qc_finishing_before,
                        0 qty_transit_terima_finishing_before,
                        0 qty_transit_keluar_qc_reject_before,

                        0 saldo_awal_sp_pasang_kancing,
                        0 saldo_awal_sp_bartack,
                        0 saldo_awal_sp_heatseal,
                        0 saldo_awal_sp_snap,
                        0 saldo_awal_sp_embro,

                        0 total_in_sp_pasang_kancing,
                        0 rft_sp_pasang_kancing,
                        0 defect_sp_pasang_kancing,
                        0 rework_sp_pasang_kancing,
                        0 reject_sp_pasang_kancing,
                        0 total_in_sp_bartack,
                        0 rft_sp_bartack,
                        0 defect_sp_bartack,
                        0 rework_sp_bartack,
                        0 reject_sp_bartack,
                        0 total_in_sp_heatseal,
                        0 rft_sp_heatseal,
                        0 defect_sp_heatseal,
                        0 rework_sp_heatseal,
                        0 reject_sp_heatseal,
                        0 total_in_sp_snap,
                        0 rft_sp_snap,
                        0 defect_sp_snap,
                        0 rework_sp_snap,
                        0 reject_sp_snap,
                        0 total_in_sp_embro,
                        0 rft_sp_embro,
                        0 defect_sp_embro,
                        0 rework_sp_embro,
                        0 reject_sp_embro
                from
                        (
                        select
                                buyer,
                                kpno ws,
                                styleno,
                                color,
                                size,
                                qty_in qty_in_before,
                                0 qty_in,
                                0 qty_out_before,
                                0 qty_out
                        from
                                in_subcont_before UNION ALL
                        select
                                buyer,
                                kpno ws,
                                styleno,
                                color,
                                size,
                                0 qty_in_before,
                                qty_in,
                                0 qty_out_before,
                                0 qty_out
                        from
                                in_subcont_trx UNION ALL
                        select
                                buyer,
                                kpno ws,
                                styleno,
                                color,
                                size,
                                0 qty_in_before,
                                0 qty_in,
                                qty_out qty_out_before,
                                0 qty_out
                        from
                                out_subcont_before UNION ALL
                        select
                                buyer,
                                kpno ws,
                                styleno,
                                color,
                                size,
                                0 qty_in_before,
                                0 qty_in,
                                0 qty_out_before,
                                qty_out
                        from
                                out_subcont_trx
                        ) a
                GROUP BY
                        buyer,
                        ws,
                        styleno,
                        color,
                        size
            ),

            terima_gudang_before as (
                    select
                            tujuan_pengeluaran,
                            tujuan,
                            mastersupplier.Supplier as buyer,
                            act_costing.kpno ws,
                            act_costing.styleno,
                            so_det.color,
                            so_det.size,
                            SUM(fg_stok_bppb.qty_out) total_before,
                            0 total
                    from laravel_nds.fg_stok_bppb
                    left join so_det on so_det.id = fg_stok_bppb.id_so_det
                    left join so on so.id = so_det.id_so
                    left join act_costing on act_costing.id = so.id_cost
                    left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                    where tujuan = 'PRODUCTION-SEWING' and
                    tgl_pengeluaran > '2026-03-31' and
                    tgl_pengeluaran < '$start_date'
                    $filter_terima_gudang
                    group by act_costing.kpno, so_det.color, so_det.size
                    having total_before > 0
            ),

            terima_gudang as (
                    select
                            tujuan_pengeluaran,
                            tujuan,
                            mastersupplier.Supplier as buyer,
                            act_costing.kpno ws,
                            act_costing.styleno,
                            so_det.color,
                            so_det.size,
                            0 total_before,
                            SUM(fg_stok_bppb.qty_out) total
                    from laravel_nds.fg_stok_bppb
                    left join so_det on so_det.id = fg_stok_bppb.id_so_det
                    left join so on so.id = so_det.id_so
                    left join act_costing on act_costing.id = so.id_cost
                    left join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                    where tujuan = 'PRODUCTION-SEWING' and
                    tgl_pengeluaran > '2026-03-31' and
                    tgl_pengeluaran between '$start_date' and '$end_date'
                    $filter_terima_gudang
                    group by act_costing.kpno, so_det.color, so_det.size
                    having total > 0
            ),

            query_terima_gudang as (
                    select
                            buyer,
                            ws,
                            styleno,
                            color,
                            size,
                            SUM( total_before ) terima_gudang_before,
                            SUM( total ) terima_gudang,
                            0 qty_in_before,
                            0 qty_in,
                            0 qty_out_before,
                            0 qty_out,
                            0 saldo_awal_sewing,
                            0 qty_loading,
                            0 input_rework_sewing,
                            0 input_rework_spotcleaning,
                            0 input_rework_mending,
                            0 defect_sewing,
                            0 defect_spotcleaning,
                            0 defect_mending,
                            0 qty_sew_reject,
                            0 qty_sewing,
                            0 saldo_akhir_sewing,
                            0 saldo_awal_finishing,
                            0 input_rework_sewing_f,
                            0 input_rework_spotcleaning_f,
                            0 input_rework_mending_f,
                            0 defect_sewing_f,
                            0 defect_spotcleaning_f,
                            0 defect_mending_f,
                            0 qty_fin_reject,
                            0 qty_finishing,
                            0 saldo_akhir_finishing,
                            0 saldo_awal_secondary_proses,
                            0 total_in_sp,
                            0 rework_sp,
                            0 defect_sp,
                            0 reject_sp,
                            0 rft_sp,
                            0 saldo_akhir_secondary_proses,
                            0 saldo_awal_defect_sewing,
                            0 total_defect_sewing,
                            0 total_input_rework_sewing,
                            0 saldo_akhir_defect_sewing,
                            0 saldo_awal_defect_spotcleaning,
                            0 total_defect_spotcleaning,
                            0 total_input_rework_spotcleaning,
                            0 saldo_akhir_defect_spotcleaning,
                            0 saldo_awal_defect_mending,
                            0 total_defect_mending,
                            0 total_input_rework_mending,
                            0 saldo_akhir_mending,
                            0 saldo_awal_reject,
                            0 qty_reject_in,
                            0 qty_reworked,
                            0 qty_rejected,
                            0 saldo_akhir_qc_reject,
                            0 loading_inject_bef,
                            0 loading_inject,
                            0 qty_transit_terima_sewing_before,
                            0 qty_transit_terima_qc_finishing_before,
                            0 qty_transit_terima_finishing_before,
                            0 qty_transit_keluar_qc_reject_before,

                            0 saldo_awal_sp_pasang_kancing,
                            0 saldo_awal_sp_bartack,
                            0 saldo_awal_sp_heatseal,
                            0 saldo_awal_sp_snap,
                            0 saldo_awal_sp_embro,

                            0 total_in_sp_pasang_kancing,
                            0 rft_sp_pasang_kancing,
                            0 defect_sp_pasang_kancing,
                            0 rework_sp_pasang_kancing,
                            0 reject_sp_pasang_kancing,
                            0 total_in_sp_bartack,
                            0 rft_sp_bartack,
                            0 defect_sp_bartack,
                            0 rework_sp_bartack,
                            0 reject_sp_bartack,
                            0 total_in_sp_heatseal,
                            0 rft_sp_heatseal,
                            0 defect_sp_heatseal,
                            0 rework_sp_heatseal,
                            0 reject_sp_heatseal,
                            0 total_in_sp_snap,
                            0 rft_sp_snap,
                            0 defect_sp_snap,
                            0 rework_sp_snap,
                            0 reject_sp_snap,
                            0 total_in_sp_embro,
                            0 rft_sp_embro,
                            0 defect_sp_embro,
                            0 rework_sp_embro,
                            0 reject_sp_embro
                    from
                            (
                                    select * from terima_gudang_before
                                    UNION ALL
                                    select * from terima_gudang
                            ) terima_gudang
                    group by buyer, ws, styleno, color, size

            ),

            loading_inject as (
                SELECT
                    buyer,
                    ws,
                    styleno,
                    color,
                    size,
                    CASE WHEN sewing_loading_inject.tanggal < '$start_date' THEN SUM(sewing_loading_inject.loading_diff) ELSE 0 END AS loading_inject_bef,
                    CASE WHEN sewing_loading_inject.tanggal >= '$start_date' THEN SUM(sewing_loading_inject.loading_diff) ELSE 0 END AS loading_inject
                FROM laravel_nds.sewing_loading_inject
                LEFT JOIN laravel_nds.master_sb_ws msb on msb.id_so_det = sewing_loading_inject.so_det_id
                WHERE sewing_loading_inject.tanggal <= '$end_date'
                $filter_loading
                GROUP BY so_det_id
            ),

            query_loading_inject as (
                SELECT
                    buyer,
                    ws,
                    styleno,
                    color,
                    size,
                    0 terima_gudang_before,
                    0 terima_gudang,
                    0 qty_in_before,
                    0 qty_in,
                    0 qty_out_before,
                    0 qty_out,
                    0 saldo_awal_sewing,
                    0 qty_loading,
                    0 input_rework_sewing,
                    0 input_rework_spotcleaning,
                    0 input_rework_mending,
                    0 defect_sewing,
                    0 defect_spotcleaning,
                    0 defect_mending,
                    0 qty_sew_reject,
                    0 qty_sewing,
                    0 saldo_akhir_sewing,
                    0 saldo_awal_finishing,
                    0 input_rework_sewing_f,
                    0 input_rework_spotcleaning_f,
                    0 input_rework_mending_f,
                    0 defect_sewing_f,
                    0 defect_spotcleaning_f,
                    0 defect_mending_f,
                    0 qty_fin_reject,
                    0 qty_finishing,
                    0 saldo_akhir_finishing,
                    0 saldo_awal_secondary_proses,
                    0 total_in_sp,
                    0 rework_sp,
                    0 defect_sp,
                    0 reject_sp,
                    0 rft_sp,
                    0 saldo_akhir_secondary_proses,
                    0 saldo_awal_defect_sewing,
                    0 total_defect_sewing,
                    0 total_input_rework_sewing,
                    0 saldo_akhir_defect_sewing,
                    0 saldo_awal_defect_spotcleaning,
                    0 total_defect_spotcleaning,
                    0 total_input_rework_spotcleaning,
                    0 saldo_akhir_defect_spotcleaning,
                    0 saldo_awal_defect_mending,
                    0 total_defect_mending,
                    0 total_input_rework_mending,
                    0 saldo_akhir_mending,
                    0 saldo_awal_reject,
                    0 qty_reject_in,
                    0 qty_reworked,
                    0 qty_rejected,
                    0 saldo_akhir_qc_reject,
                    loading_inject.loading_inject_bef,
                    loading_inject.loading_inject,
                    0 qty_transit_terima_sewing_before,
                    0 qty_transit_terima_qc_finishing_before,
                    0 qty_transit_terima_finishing_before,
                    0 qty_transit_keluar_qc_reject_before,

                    0 saldo_awal_sp_pasang_kancing,
                    0 saldo_awal_sp_bartack,
                    0 saldo_awal_sp_heatseal,
                    0 saldo_awal_sp_snap,
                    0 saldo_awal_sp_embro,

                    0 total_in_sp_pasang_kancing,
                    0 rft_sp_pasang_kancing,
                    0 defect_sp_pasang_kancing,
                    0 rework_sp_pasang_kancing,
                    0 reject_sp_pasang_kancing,
                    0 total_in_sp_bartack,
                    0 rft_sp_bartack,
                    0 defect_sp_bartack,
                    0 rework_sp_bartack,
                    0 reject_sp_bartack,
                    0 total_in_sp_heatseal,
                    0 rft_sp_heatseal,
                    0 defect_sp_heatseal,
                    0 rework_sp_heatseal,
                    0 reject_sp_heatseal,
                    0 total_in_sp_snap,
                    0 rft_sp_snap,
                    0 defect_sp_snap,
                    0 rework_sp_snap,
                    0 reject_sp_snap,
                    0 total_in_sp_embro,
                    0 rft_sp_embro,
                    0 defect_sp_embro,
                    0 rework_sp_embro,
                    0 reject_sp_embro
                FROM
                    loading_inject
                GROUP BY
                    buyer, ws, styleno, color, size
            ),

            query_final as (
                    select
                            buyer,
                            ws,
                            styleno,
                            color,
                            size,
                            SUM( saldo_awal_sewing + (qty_in_before - qty_out_before) + (terima_gudang_before) + loading_inject_bef) saldo_awal_sewing,
                            SUM( qty_loading) qty_loading,
                            SUM( terima_gudang ) terima_gudang,
                            SUM( qty_in ) qty_in_subcont,
                            SUM( input_rework_sewing ) input_rework_sewing,
                            SUM( input_rework_spotcleaning ) input_rework_spotcleaning,
                            SUM( input_rework_mending ) input_rework_mending,
                            SUM( defect_sewing ) defect_sewing,
                            SUM( defect_spotcleaning ) defect_spotcleaning,
                            SUM( defect_mending ) defect_mending,
                            SUM( qty_sew_reject ) qty_sew_reject,
                            SUM( qty_sewing ) qty_sewing,
                            ROUND(SUM( qty_out ),0) qty_out_subcont,
                            SUM( saldo_akhir_sewing + ((qty_in_before - qty_out_before) + qty_in - qty_out) + ((terima_gudang_before) + terima_gudang) + ((loading_inject_bef) + loading_inject) ) saldo_akhir_sewing,
                            SUM( saldo_awal_finishing ) saldo_awal_finishing,
                            SUM( input_rework_sewing_f ) input_rework_sewing_f,
                            SUM( input_rework_spotcleaning_f ) input_rework_spotcleaning_f,
                            SUM( input_rework_mending_f ) input_rework_mending_f,
                            SUM( defect_sewing_f ) defect_sewing_f,
                            SUM( defect_spotcleaning_f ) defect_spotcleaning_f,
                            SUM( defect_mending_f ) defect_mending_f,
                            SUM( qty_fin_reject ) qty_fin_reject,
                            SUM( qty_finishing ) qty_finishing,
                            SUM( saldo_akhir_finishing ) saldo_akhir_finishing,
                            SUM( saldo_awal_secondary_proses ) saldo_awal_secondary_proses,
                            SUM( total_in_sp ) total_in_sp,
                            SUM( rework_sp ) rework_sp,
                            SUM( defect_sp ) defect_sp,
                            SUM( reject_sp ) reject_sp,
                            SUM( rft_sp ) rft_sp,
                            SUM( saldo_akhir_secondary_proses ) saldo_akhir_secondary_proses,
                            SUM( saldo_awal_defect_sewing ) saldo_awal_defect_sewing,
                            SUM( total_defect_sewing ) total_defect_sewing,
                            SUM( total_input_rework_sewing ) total_input_rework_sewing,
                            SUM( saldo_akhir_defect_sewing ) saldo_akhir_defect_sewing,
                            SUM( saldo_awal_defect_spotcleaning ) saldo_awal_defect_spotcleaning,
                            SUM( total_defect_spotcleaning ) total_defect_spotcleaning,
                            SUM( total_input_rework_spotcleaning ) total_input_rework_spotcleaning,
                            SUM( saldo_akhir_defect_spotcleaning ) saldo_akhir_defect_spotcleaning,
                            SUM( saldo_awal_defect_mending ) saldo_awal_defect_mending,
                            SUM( total_defect_mending ) total_defect_mending,
                            SUM( total_input_rework_mending ) total_input_rework_mending,
                            SUM( saldo_akhir_mending ) saldo_akhir_mending,
                            SUM( saldo_awal_reject ) saldo_awal_reject,
                            SUM( qty_reject_in ) qty_reject_in,
                            SUM( qty_reworked ) qty_reworked,
                            SUM( qty_rejected ) qty_rejected,
                            SUM( saldo_akhir_qc_reject ) saldo_akhir_qc_reject,
                            SUM( qty_transit_terima_sewing_before ) qty_transit_terima_sewing_before,
                            SUM( qty_transit_terima_qc_finishing_before ) qty_transit_terima_qc_finishing_before,
                            SUM( qty_transit_terima_finishing_before ) qty_transit_terima_finishing_before,
                            SUM( qty_transit_keluar_qc_reject_before ) qty_transit_keluar_qc_reject_before,

                            SUM(saldo_awal_sp_pasang_kancing) AS saldo_awal_sp_pasang_kancing,
                            SUM(saldo_awal_sp_bartack) AS saldo_awal_sp_bartack,
                            SUM(saldo_awal_sp_heatseal) AS saldo_awal_sp_heatseal,
                            SUM(saldo_awal_sp_snap) AS saldo_awal_sp_snap,
                            SUM(saldo_awal_sp_embro) AS saldo_awal_sp_embro,

                            SUM(total_in_sp_pasang_kancing) total_in_sp_pasang_kancing,
                            SUM(rft_sp_pasang_kancing) rft_sp_pasang_kancing,
                            SUM(defect_sp_pasang_kancing) defect_sp_pasang_kancing,
                            SUM(rework_sp_pasang_kancing) rework_sp_pasang_kancing,
                            SUM(reject_sp_pasang_kancing) reject_sp_pasang_kancing,
                            SUM(total_in_sp_bartack) total_in_sp_bartack,
                            SUM(rft_sp_bartack) rft_sp_bartack,
                            SUM(defect_sp_bartack) defect_sp_bartack,
                            SUM(rework_sp_bartack) rework_sp_bartack,
                            SUM(reject_sp_bartack) reject_sp_bartack,
                            SUM(total_in_sp_heatseal) total_in_sp_heatseal,
                            SUM(rft_sp_heatseal) rft_sp_heatseal,
                            SUM(defect_sp_heatseal) defect_sp_heatseal,
                            SUM(rework_sp_heatseal) rework_sp_heatseal,
                            SUM(reject_sp_heatseal) reject_sp_heatseal,
                            SUM(total_in_sp_snap) total_in_sp_snap,
                            SUM(rft_sp_snap) rft_sp_snap,
                            SUM(defect_sp_snap) defect_sp_snap,
                            SUM(rework_sp_snap) rework_sp_snap,
                            SUM(reject_sp_snap) reject_sp_snap,
                            SUM(total_in_sp_embro) total_in_sp_embro,
                            SUM(rft_sp_embro) rft_sp_embro,
                            SUM(defect_sp_embro) defect_sp_embro,
                            SUM(rework_sp_embro) rework_sp_embro,
                            SUM(reject_sp_embro) reject_sp_embro
                    from
                            (
                            select
                                    buyer,
                                    ws,
                                    styleno,
                                    color,
                                    size,
                                    0 terima_gudang_before,
                                    0 terima_gudang,
                                    0 qty_in_before,
                                    0 qty_in,
                                    0 qty_out_before,
                                    0 qty_out,
                                    saldo_awal_sewing,
                                    qty_loading,
                                    input_rework_sewing,
                                    input_rework_spotcleaning,
                                    input_rework_mending,
                                    defect_sewing,
                                    defect_spotcleaning,
                                    defect_mending,
                                    qty_sew_reject,
                                    qty_sewing,
                                    saldo_akhir_sewing,
                                    saldo_awal_finishing,
                                    input_rework_sewing_f,
                                    input_rework_spotcleaning_f,
                                    input_rework_mending_f,
                                    defect_sewing_f,
                                    defect_spotcleaning_f,
                                    defect_mending_f,
                                    qty_fin_reject,
                                    qty_finishing,
                                    saldo_akhir_finishing,
                                    saldo_awal_secondary_proses,
                                    total_in_sp,
                                    rework_sp,
                                    defect_sp,
                                    reject_sp,
                                    rft_sp,
                                    saldo_akhir_secondary_proses,
                                    saldo_awal_defect_sewing,
                                    total_defect_sewing,
                                    total_input_rework_sewing,
                                    saldo_akhir_defect_sewing,
                                    saldo_awal_defect_spotcleaning,
                                    total_defect_spotcleaning,
                                    total_input_rework_spotcleaning,
                                    saldo_akhir_defect_spotcleaning,
                                    saldo_awal_defect_mending,
                                    total_defect_mending,
                                    total_input_rework_mending,
                                    saldo_akhir_mending,
                                    saldo_awal_reject,
                                    qty_reject_in,
                                    qty_reworked,
                                    qty_rejected,
                                    saldo_akhir_qc_reject,
                                    loading_inject_bef,
                                    loading_inject,
                                    qty_transit_terima_sewing_before,
                                    qty_transit_terima_qc_finishing_before,
                                    qty_transit_terima_finishing_before,
                                    qty_transit_keluar_qc_reject_before,

                                    saldo_awal_sp_pasang_kancing,
                                    saldo_awal_sp_bartack,
                                    saldo_awal_sp_heatseal,
                                    saldo_awal_sp_snap,
                                    saldo_awal_sp_embro,

                                    total_in_sp_pasang_kancing,
                                    rft_sp_pasang_kancing,
                                    defect_sp_pasang_kancing,
                                    rework_sp_pasang_kancing,
                                    reject_sp_pasang_kancing,
                                    total_in_sp_bartack,
                                    rft_sp_bartack,
                                    defect_sp_bartack,
                                    rework_sp_bartack,
                                    reject_sp_bartack,
                                    total_in_sp_heatseal,
                                    rft_sp_heatseal,
                                    defect_sp_heatseal,
                                    rework_sp_heatseal,
                                    reject_sp_heatseal,
                                    total_in_sp_snap,
                                    rft_sp_snap,
                                    defect_sp_snap,
                                    rework_sp_snap,
                                    reject_sp_snap,
                                    total_in_sp_embro,
                                    rft_sp_embro,
                                    defect_sp_embro,
                                    rework_sp_embro,
                                    reject_sp_embro
                            from
                                    query_fix UNION ALL
                            select
                                    *
                            from
                                    query_fix_subcont UNION ALL
                            select
                                    *
                            from
                                    query_terima_gudang UNION ALL
                            select
                                    *
                            from
                                    query_loading_inject
                            ) a
                    group by
                            buyer,
                            ws,
                            styleno,
                            color,
                            size
                    order by
                            buyer asc,
                            ws asc,
                            color asc,
                            size asc
            ),

            query_adjust as (SELECT
                    wip_adjustment.buyer,
                    wip_adjustment.no_ws,
                    wip_adjustment.style,
                    wip_adjustment.color,
                    wip_adjustment.size,

                    -- SEWING
                    SUM(
                        CASE
                            WHEN type_report = 'SEWING'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS sewing_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'SEWING'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS sewing_adjust,

                    -- QC FINISHING
                    SUM(
                        CASE
                            WHEN type_report = 'QC FINISHING'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS qc_finishing_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'QC FINISHING'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS qc_finishing_adjust,

                    -- FINISHING ALL
                    SUM(
                        CASE
                            WHEN type_report = 'FINISHING'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS finishing_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'FINISHING'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS finishing_adjust,

                    -- FINISHING
                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Pasang Kancing'
                        AND tgl_saldo < '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_pasang_kancing_before,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Pasang Kancing'
                        AND tgl_saldo >= '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_pasang_kancing,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Bartack'
                        AND tgl_saldo < '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_bartack_before,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Bartack'
                        AND tgl_saldo >= '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_bartack,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Heatseal'
                        AND tgl_saldo < '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_heatseal_before,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Heatseal'
                        AND tgl_saldo >= '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_heatseal,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Snap'
                        AND tgl_saldo < '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_snap_before,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Snap'
                        AND tgl_saldo >= '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_snap,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Embro'
                        AND tgl_saldo < '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_embro_before,

                    SUM(CASE
                        WHEN type_report = 'FINISHING'
                        AND spm.secondary_process = 'Embro'
                        AND tgl_saldo >= '$start_date'
                        THEN qty ELSE 0
                    END) AS finishing_adjust_embro,

                    -- DEFECT SEWING
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SEWING'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS defect_sewing_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SEWING'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS defect_sewing_adjust,

                    -- DEFECT SPOTCLEANING
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SPOTCLEANING'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS defect_spotcleaning_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SPOTCLEANING'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS defect_spotcleaning_adjust,

                    -- DEFECT MENDING
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT MENDING'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS defect_mending_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT MENDING'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS defect_mending_adjust,

                    -- QC REJECT
                    SUM(
                        CASE
                            WHEN type_report = 'QC REJECT'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS qc_reject_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'QC REJECT'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS qc_reject_adjust,

                    -- QC REJECT
                    SUM(
                        CASE
                            WHEN type_report = 'TRANSIT TERIMA QC REJECT'
                                AND tgl_saldo < '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS qc_transit_terima_qc_reject_adjust_before,

                    SUM(
                        CASE
                            WHEN type_report = 'TRANSIT TERIMA QC REJECT'
                                AND tgl_saldo >= '$start_date'
                            THEN qty ELSE 0
                        END
                    ) AS qc_transit_terima_qc_reject_adjust

                FROM wip_adjustment
                LEFT JOIN secondary_process_mapping spm
                ON spm.buyer = wip_adjustment.buyer
                AND spm.ws = wip_adjustment.no_ws
                AND spm.styleno = wip_adjustment.style
                AND spm.color = wip_adjustment.color
                AND spm.size = wip_adjustment.size
                AND wip_adjustment.type_report = 'FINISHING'
                WHERE tgl_saldo <= '$end_date'
                AND type_report IN (
                    'SEWING',
                    'QC FINISHING',
                    'FINISHING',
                    'DEFECT SEWING',
                    'DEFECT SPOTCLEANING',
                    'DEFECT MENDING',
                    'QC REJECT',
                    'TRANSIT TERIMA QC REJECT'
                )
                AND status = 'Y'

                GROUP BY
                    wip_adjustment.buyer,
                    wip_adjustment.no_ws,
                    wip_adjustment.style,
                    wip_adjustment.color,
                    wip_adjustment.size
            ),

            query_switching as (
                SELECT
                    buyer,
                    no_ws,
                    style,
                    color,
                    size,
                    SUM(
                        CASE
                            WHEN type_report = 'SEWING'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS sewing_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'SEWING'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS sewing_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'QC FINISHING'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_finishing_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'QC FINISHING'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_finishing_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'FINISHING'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS finishing_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'FINISHING'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS finishing_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SEWING'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_sewing_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SEWING'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_sewing_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SPOTCLEANING'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_spotcleaning_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SPOTCLEANING'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_spotcleaning_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT MENDING'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_mending_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT MENDING'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_mending_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'QC REJECT'
                                AND tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_reject_switching_in_before,
                    SUM(
                        CASE
                            WHEN type_report = 'QC REJECT'
                                AND tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_reject_switching_in,
                    0 AS sewing_switching_out_before,
                    0 AS sewing_switching_out,
                    0 AS qc_finishing_switching_out_before,
                    0 AS qc_finishing_switching_out,
                    0 AS finishing_switching_out_before,
                    0 AS finishing_switching_out,
                    0 AS defect_sewing_switching_out_before,
                    0 AS defect_sewing_switching_out,
                    0 AS defect_spotcleaning_switching_out_before,
                    0 AS defect_spotcleaning_switching_out,
                    0 AS defect_mending_switching_out_before,
                    0 AS defect_mending_switching_out,
                    0 AS qc_reject_switching_out_before,
                    0 AS qc_reject_switching_out
                FROM
                    wip_switching_adj
                WHERE
                    tgl_saldo <= '{$end_date}' AND
                    type_report IN (
                        'SEWING',
                        'QC FINISHING',
                        'FINISHING',
                        'DEFECT SEWING',
                        'DEFECT SPOTCLEANING',
                        'DEFECT MENDING',
                        'QC REJECT'
                    )
                GROUP BY
                    from_no_ws, from_color, from_size, from_panel, from_part, no_ws, color, size, panel, part

                UNION ALL

                SELECT
                    from_buyer as buyer,
                    from_no_ws as no_ws,
                    from_style as style,
                    from_color as color,
                    from_size as size,
                    0 AS sewing_switching_in_before,
                    0 AS sewing_switching_in,
                    0 AS qc_finishing_switching_in_before,
                    0 AS qc_finishing_switching_in,
                    0 AS finishing_switching_in_before,
                    0 AS finishing_switching_in,
                    0 AS defect_sewing_switching_in_before,
                    0 AS defect_sewing_switching_in,
                    0 AS defect_spotcleaning_switching_in_before,
                    0 AS defect_spotcleaning_switching_in,
                    0 AS defect_mending_switching_in_before,
                    0 AS defect_mending_switching_in,
                    0 AS qc_reject_switching_in_before,
                    0 AS qc_reject_switching_in,
                    SUM(
                        CASE
                            WHEN type_report = 'SEWING'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS sewing_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'SEWING'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS sewing_switching_out,
                    SUM(
                        CASE
                            WHEN type_report = 'QC FINISHING'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_finishing_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'QC FINISHING'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_finishing_switching_out,
                    SUM(
                        CASE
                            WHEN type_report = 'FINISHING'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS finishing_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'FINISHING'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS finishing_switching_out,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SEWING'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_sewing_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SEWING'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_sewing_switching_out,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SPOTCLEANING'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_spotcleaning_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT SPOTCLEANING'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_spotcleaning_switching_out,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT MENDING'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_mending_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'DEFECT MENDING'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS defect_mending_switching_out,
                    SUM(
                        CASE
                            WHEN type_report = 'QC REJECT'
                                AND from_tgl_saldo < '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_reject_switching_out_before,
                    SUM(
                        CASE
                            WHEN type_report = 'QC REJECT'
                                AND from_tgl_saldo >= '{$start_date}'
                            THEN qty
                            ELSE 0
                        END
                    ) AS qc_reject_switching_out
                FROM
                    wip_switching_adj
                WHERE
                    from_tgl_saldo <= '{$end_date}' AND
                    type_report IN (
                        'SEWING',
                        'QC FINISHING',
                        'FINISHING',
                        'DEFECT SEWING',
                        'DEFECT SPOTCLEANING',
                        'DEFECT MENDING',
                        'QC REJECT'
                    )
                GROUP BY
                    from_no_ws, from_color, from_size, from_panel, from_part, no_ws, color, size, panel, part
            ),

            query_keluar_packing AS (
                SELECT
                    buyer,
                    ws,
                    styleno,
                    color,
                    size,
                    SUM(CASE WHEN DATE(updated_at) >= '2026-07-01 ' AND DATE(updated_at) < '$start_date' THEN 1 ELSE 0 END) AS qty_transit_keluar_packing_before,
                    SUM(CASE WHEN DATE(updated_at) >= '$start_date' THEN 1 ELSE 0 END) AS qty_transit_keluar_packing
                FROM signalbit_erp.output_rfts_packing_po
                LEFT JOIN laravel_nds.master_sb_ws ON master_sb_ws.id_so_det = output_rfts_packing_po.so_det_id
                WHERE so_det_id IS NOT NULL
                    AND type = 'REJECT'
                    AND DATE(updated_at) <= '$end_date'
                GROUP BY
                    buyer,
                    ws,
                    styleno,
                    color,
                    size
            )

            select
            buyer,
            ws,
            styleno,
            color,
            size,

            SUM(saldo_awal_sewing + COALESCE(sewing_adjust_before,0) + COALESCE(sewing_switching_in_before,0) - COALESCE(sewing_switching_out_before,0)) saldo_awal_sewing,
            SUM(qty_loading) qty_loading,
            SUM(terima_gudang) terima_gudang,
            SUM(qty_in_subcont) qty_in_subcont,
            SUM(input_rework_sewing) input_rework_sewing,
            SUM(input_rework_spotcleaning) input_rework_spotcleaning,
            SUM(input_rework_mending) input_rework_mending,
            SUM(defect_sewing) defect_sewing,
            SUM(defect_spotcleaning) defect_spotcleaning,
            SUM(defect_mending) defect_mending,
            SUM(qty_sew_reject) qty_sew_reject,
            SUM(qty_sewing) qty_sewing,
            SUM(qty_out_subcont) qty_out_subcont,
            SUM(COALESCE(sewing_adjust,0)) sewing_adjust,
            SUM(COALESCE(sewing_switching_in,0)) sewing_switching_in,
            SUM(COALESCE(sewing_switching_out,0)) sewing_switching_out,
            SUM(saldo_akhir_sewing + COALESCE(sewing_adjust_before,0) + COALESCE(sewing_switching_in_before,0) - COALESCE(sewing_switching_out_before,0) + COALESCE(sewing_adjust,0) + COALESCE(sewing_switching_in,0) - COALESCE(sewing_switching_out,0)) saldo_akhir_sewing,

            SUM(saldo_awal_finishing + COALESCE(qc_finishing_adjust_before,0) + COALESCE(qc_finishing_switching_in_before,0) - COALESCE(qc_finishing_switching_out_before,0)) saldo_awal_finishing,
            SUM(input_rework_sewing_f) input_rework_sewing_f,
            SUM(input_rework_spotcleaning_f) input_rework_spotcleaning_f,
            SUM(input_rework_mending_f) input_rework_mending_f,
            SUM(defect_sewing_f) defect_sewing_f,
            SUM(defect_spotcleaning_f) defect_spotcleaning_f,
            SUM(defect_mending_f) defect_mending_f,
            SUM(qty_fin_reject) qty_fin_reject,
            SUM(qty_finishing) qty_finishing,
            SUM(COALESCE(qc_finishing_adjust,0)) qc_finishing_adjust,
            SUM(COALESCE(qc_finishing_switching_in,0)) qc_finishing_switching_in,
            SUM(COALESCE(qc_finishing_switching_out,0)) qc_finishing_switching_out,
            SUM(saldo_akhir_finishing + COALESCE(qc_finishing_adjust_before,0) + COALESCE(qc_finishing_switching_in_before,0) - COALESCE(qc_finishing_switching_out_before,0) + COALESCE(qc_finishing_adjust,0) + COALESCE(qc_finishing_switching_in,0) - COALESCE(qc_finishing_switching_out,0)) saldo_akhir_finishing,

            -- DEPRECATED FINISHING FOR ALL PROSES
            SUM(saldo_awal_secondary_proses + COALESCE(finishing_adjust_before,0) + COALESCE(finishing_switching_in_before,0) - COALESCE(finishing_switching_out_before,0)) saldo_awal_secondary_proses,
            SUM(total_in_sp) total_in_sp,
            SUM(rework_sp) rework_sp,
            SUM(defect_sp) defect_sp,
            SUM(reject_sp) reject_sp,
            SUM(rft_sp) rft_sp,
            SUM(COALESCE(finishing_adjust,0)) finishing_adjust,
            SUM(COALESCE(finishing_switching_in,0)) finishing_switching_in,
            SUM(COALESCE(finishing_switching_out,0)) finishing_switching_out,
            SUM(saldo_akhir_secondary_proses + COALESCE(finishing_adjust_before,0) + COALESCE(finishing_switching_in_before,0) - COALESCE(finishing_switching_out_before,0) + COALESCE(finishing_adjust,0) + COALESCE(finishing_switching_in,0) - COALESCE(finishing_switching_out,0)) saldo_akhir_secondary_proses,

            -- FINISHING PER PROSES
            -- PASANG KANCING
            SUM(saldo_awal_sp_pasang_kancing) + SUM(COALESCE(finishing_adjust_pasang_kancing_before, 0)) AS saldo_awal_finishing_pasang_kancing,
            SUM(total_in_sp_pasang_kancing) total_in_sp_pasang_kancing,
            SUM(rework_sp_pasang_kancing) rework_sp_pasang_kancing,
            SUM(defect_sp_pasang_kancing) defect_sp_pasang_kancing,
            SUM(reject_sp_pasang_kancing) reject_sp_pasang_kancing,
            SUM(rft_sp_pasang_kancing) rft_sp_pasang_kancing,
            SUM(COALESCE(finishing_adjust_pasang_kancing, 0)) finishing_adjust_pasang_kancing,
            (SUM(saldo_awal_sp_pasang_kancing) + SUM(COALESCE(finishing_adjust_pasang_kancing_before, 0)) + SUM(total_in_sp_pasang_kancing) + SUM(rework_sp_pasang_kancing) - SUM(defect_sp_pasang_kancing) - SUM(reject_sp_pasang_kancing) - SUM(rft_sp_pasang_kancing) + SUM(COALESCE(finishing_adjust_pasang_kancing, 0))) AS saldo_akhir_finishing_pasang_kancing,

            -- BARTACK
            SUM(saldo_awal_sp_bartack) + SUM(COALESCE(finishing_adjust_bartack_before, 0)) AS saldo_awal_finishing_bartack,
            SUM(total_in_sp_bartack) total_in_sp_bartack,
            SUM(rework_sp_bartack) rework_sp_bartack,
            SUM(defect_sp_bartack) defect_sp_bartack,
            SUM(reject_sp_bartack) reject_sp_bartack,
            SUM(rft_sp_bartack) rft_sp_bartack,
            SUM(COALESCE(finishing_adjust_bartack, 0)) finishing_adjust_bartack,
            (SUM(saldo_awal_sp_bartack) + SUM(COALESCE(finishing_adjust_bartack_before, 0)) + SUM(total_in_sp_bartack) + SUM(rework_sp_bartack) - SUM(defect_sp_bartack) - SUM(reject_sp_bartack) - SUM(rft_sp_bartack) + SUM(COALESCE(finishing_adjust_bartack, 0))) AS saldo_akhir_finishing_bartack,

            -- HEATSEAL
            SUM(saldo_awal_sp_heatseal) + SUM(COALESCE(finishing_adjust_heatseal_before, 0)) AS saldo_awal_finishing_heatseal,
            SUM(total_in_sp_heatseal) total_in_sp_heatseal,
            SUM(rework_sp_heatseal) rework_sp_heatseal,
            SUM(defect_sp_heatseal) defect_sp_heatseal,
            SUM(reject_sp_heatseal) reject_sp_heatseal,
            SUM(rft_sp_heatseal) rft_sp_heatseal,
            SUM(COALESCE(finishing_adjust_heatseal, 0)) finishing_adjust_heatseal,
            (SUM(saldo_awal_sp_heatseal) + SUM(COALESCE(finishing_adjust_heatseal_before, 0)) + SUM(total_in_sp_heatseal) + SUM(rework_sp_heatseal) - SUM(defect_sp_heatseal) - SUM(reject_sp_heatseal) - SUM(rft_sp_heatseal) + SUM(COALESCE(finishing_adjust_heatseal, 0))) AS saldo_akhir_finishing_heatseal,

            -- SNAP
            SUM(saldo_awal_sp_snap) + SUM(COALESCE(finishing_adjust_snap_before, 0)) AS saldo_awal_finishing_snap,
            SUM(total_in_sp_snap) total_in_sp_snap,
            SUM(rework_sp_snap) rework_sp_snap,
            SUM(defect_sp_snap) defect_sp_snap,
            SUM(reject_sp_snap) reject_sp_snap,
            SUM(rft_sp_snap) rft_sp_snap,
            SUM(COALESCE(finishing_adjust_snap, 0)) finishing_adjust_snap,
            (SUM(saldo_awal_sp_snap) + SUM(COALESCE(finishing_adjust_snap_before, 0)) + SUM(total_in_sp_snap) + SUM(rework_sp_snap) - SUM(defect_sp_snap) - SUM(reject_sp_snap) - SUM(rft_sp_snap) + SUM(COALESCE(finishing_adjust_snap, 0))) AS saldo_akhir_finishing_snap,

            -- EMBRO
            SUM(saldo_awal_sp_embro) + SUM(COALESCE(finishing_adjust_embro_before, 0)) AS saldo_awal_finishing_embro,
            SUM(total_in_sp_embro) total_in_sp_embro,
            SUM(rework_sp_embro) rework_sp_embro,
            SUM(defect_sp_embro) defect_sp_embro,
            SUM(reject_sp_embro) reject_sp_embro,
            SUM(rft_sp_embro) rft_sp_embro,
            SUM(COALESCE(finishing_adjust_embro, 0)) finishing_adjust_embro,
            (SUM(saldo_awal_sp_embro) + SUM(COALESCE(finishing_adjust_embro_before, 0)) + SUM(total_in_sp_embro) + SUM(rework_sp_embro) - SUM(defect_sp_embro) - SUM(reject_sp_embro) - SUM(rft_sp_embro) + SUM(COALESCE(finishing_adjust_embro, 0))) AS saldo_akhir_finishing_embro,

            SUM(saldo_awal_defect_sewing + COALESCE(defect_sewing_adjust_before,0) + COALESCE(defect_sewing_switching_in_before,0) - COALESCE(defect_sewing_switching_out_before,0)) saldo_awal_defect_sewing,
            SUM(total_defect_sewing) total_defect_sewing,
            SUM(total_input_rework_sewing) total_input_rework_sewing,
            SUM(COALESCE(defect_sewing_adjust,0)) defect_sewing_adjust,
            SUM(COALESCE(defect_sewing_switching_in,0)) defect_sewing_switching_in,
            SUM(COALESCE(defect_sewing_switching_out,0)) defect_sewing_switching_out,
            SUM(saldo_akhir_defect_sewing + COALESCE(defect_sewing_adjust_before,0) + COALESCE(defect_sewing_switching_in_before,0) - COALESCE(defect_sewing_switching_out_before,0) + COALESCE(defect_sewing_adjust,0) + COALESCE(defect_sewing_switching_in,0) - COALESCE(defect_sewing_switching_out,0)) saldo_akhir_defect_sewing,

            SUM(saldo_awal_defect_spotcleaning + COALESCE(defect_spotcleaning_adjust_before,0) + COALESCE(defect_spotcleaning_switching_in_before,0) - COALESCE(defect_spotcleaning_switching_out_before,0)) saldo_awal_defect_spotcleaning,
            SUM(total_defect_spotcleaning) total_defect_spotcleaning,
            SUM(total_input_rework_spotcleaning) total_input_rework_spotcleaning,
            SUM(COALESCE(defect_spotcleaning_adjust,0)) defect_spotcleaning_adjust,
            SUM(COALESCE(defect_spotcleaning_switching_in,0)) defect_spotcleaning_switching_in,
            SUM(COALESCE(defect_spotcleaning_switching_out,0)) defect_spotcleaning_switching_out,
            SUM(saldo_akhir_defect_spotcleaning + COALESCE(defect_spotcleaning_adjust_before,0) + COALESCE(defect_spotcleaning_switching_in_before,0) - COALESCE(defect_spotcleaning_switching_out_before,0) + COALESCE(defect_spotcleaning_adjust,0) + COALESCE(defect_spotcleaning_switching_in,0) - COALESCE(defect_spotcleaning_switching_out,0)) saldo_akhir_defect_spotcleaning,

            SUM(saldo_awal_defect_mending + COALESCE(defect_mending_adjust_before,0) + COALESCE(defect_mending_switching_in_before,0) - COALESCE(defect_mending_switching_out_before,0)) saldo_awal_defect_mending,
            SUM(total_defect_mending) total_defect_mending,
            SUM(total_input_rework_mending) total_input_rework_mending,
            SUM(COALESCE(defect_mending_adjust,0)) defect_mending_adjust,
            SUM(COALESCE(defect_mending_switching_in,0)) defect_mending_switching_in,
            SUM(COALESCE(defect_mending_switching_out,0)) defect_mending_switching_out,
            SUM(saldo_akhir_mending + COALESCE(defect_mending_adjust_before,0) + COALESCE(defect_mending_switching_in_before,0) - COALESCE(defect_mending_switching_out_before,0) + COALESCE(defect_mending_adjust,0) + COALESCE(defect_mending_switching_in,0) - COALESCE(defect_mending_switching_out,0)) saldo_akhir_mending,

            SUM(qty_transit_terima_sewing_before) + SUM(qty_transit_terima_qc_finishing_before) + SUM(qty_transit_terima_finishing_before) - SUM(qty_transit_keluar_qc_reject_before) - SUM(qty_transit_keluar_packing_before) + SUM(qc_transit_terima_qc_reject_adjust_before) AS qty_transit_saldo_awal,
            SUM(qty_sew_reject) AS qty_transit_terima_sewing,
            SUM(qty_fin_reject) AS qty_transit_terima_qc_finishing,
            SUM(reject_sp) AS qty_transit_terima_finishing,
            SUM(qty_reject_in) AS qty_transit_keluar_qc_reject,
            SUM(qty_transit_keluar_packing) AS qty_transit_keluar_packing,
            SUM(qc_transit_terima_qc_reject_adjust) AS qty_transit_adjustment,
            (
                SUM(qty_transit_terima_sewing_before) + SUM(qty_transit_terima_qc_finishing_before) + SUM(qty_transit_terima_finishing_before) - SUM(qty_transit_keluar_qc_reject_before) - SUM(qty_transit_keluar_packing_before) + SUM(qc_transit_terima_qc_reject_adjust_before)
                + SUM(qty_sew_reject) + SUM(qty_fin_reject) + SUM(reject_sp) - SUM(qty_reject_in) - SUM(qty_transit_keluar_packing) + SUM(qc_transit_terima_qc_reject_adjust)
            ) AS qty_transit_keluar_saldo_akhir,

            SUM(saldo_awal_reject + COALESCE(qc_reject_adjust_before,0) + COALESCE(qc_reject_switching_in_before,0) - COALESCE(qc_reject_switching_out_before,0)) saldo_awal_reject,
            SUM(qty_reject_in) qty_reject_in,
            SUM(qty_reworked) qty_reworked,
            SUM(qty_rejected) qty_rejected,
            SUM(COALESCE(qc_reject_adjust,0)) qc_reject_adjust,
            SUM(COALESCE(qc_reject_switching_in,0)) qc_reject_switching_in,
            SUM(COALESCE(qc_reject_switching_out,0)) qc_reject_switching_out,
            SUM(saldo_akhir_qc_reject + COALESCE(qc_reject_adjust_before,0) + COALESCE(qc_reject_switching_in_before,0) - COALESCE(qc_reject_switching_out_before,0) + COALESCE(qc_reject_adjust,0) + COALESCE(qc_reject_switching_in,0) - COALESCE(qc_reject_switching_out,0)) saldo_akhir_qc_reject
            FROM (select *,0 sewing_adjust_before, 0 sewing_adjust, 0 qc_finishing_adjust_before, 0 qc_finishing_adjust, 0 finishing_adjust_before, 0 finishing_adjust, 0 defect_sewing_adjust_before, 0 defect_sewing_adjust, 0 defect_spotcleaning_adjust_before, 0 defect_spotcleaning_adjust, 0 defect_mending_adjust_before, 0 defect_mending_adjust, 0 qc_reject_adjust_before, 0 qc_reject_adjust, 0 qc_transit_terima_qc_reject_adjust_before, 0 qc_transit_terima_qc_reject_adjust,
            0 finishing_adjust_pasang_kancing_before, 0 finishing_adjust_pasang_kancing, 0 finishing_adjust_bartack_before, 0 finishing_adjust_bartack, 0 finishing_adjust_heatseal_before, 0 finishing_adjust_heatseal, 0 finishing_adjust_snap_before, 0 finishing_adjust_snap, 0 finishing_adjust_embro_before, 0 finishing_adjust_embro,
            0 sewing_switching_in_before, 0 sewing_switching_in, 0 qc_finishing_switching_in_before, 0 qc_finishing_switching_in, 0 finishing_switching_in_before, 0 finishing_switching_in, 0 defect_sewing_switching_in_before, 0 defect_sewing_switching_in, 0 defect_spotcleaning_switching_in_before, 0 defect_spotcleaning_switching_in, 0 defect_mending_switching_in_before, 0 defect_mending_switching_in, 0 qc_reject_switching_in_before, 0 qc_reject_switching_in,
            0 sewing_switching_out_before, 0 sewing_switching_out, 0 qc_finishing_switching_out_before, 0 qc_finishing_switching_out, 0 finishing_switching_out_before, 0 finishing_switching_out, 0 defect_sewing_switching_out_before, 0 defect_sewing_switching_out, 0 defect_spotcleaning_switching_out_before, 0 defect_spotcleaning_switching_out, 0 defect_mending_switching_out_before, 0 defect_mending_switching_out, 0 qc_reject_switching_out_before, 0 qc_reject_switching_out,
            0 qty_transit_keluar_packing_before, 0 qty_transit_keluar_packing
            from query_final
            UNION ALL
            select buyer, no_ws as ws, style as styleno, color, size, 0 saldo_awal_sewing, 0 qty_loading, 0 terima_gudang, 0 qty_in_subcont, 0 input_rework_sewing, 0 input_rework_spotcleaning, 0 input_rework_mending, 0 defect_sewing, 0 defect_spotcleaning, 0 defect_mending, 0 qty_sew_reject, 0 qty_sewing, 0 qty_out_subcont, 0 saldo_akhir_sewing, 0 saldo_awal_finishing, 0 input_rework_sewing_f, 0 input_rework_spotcleaning_f, 0 input_rework_mending_f, 0 defect_sewing_f, 0 defect_spotcleaning_f, 0 defect_mending_f, 0 qty_fin_reject, 0 qty_finishing, 0 saldo_akhir_finishing, 0 saldo_awal_secondary_proses, 0 total_in_sp, 0 rework_sp, 0 defect_sp, 0 reject_sp, 0 rft_sp, 0 saldo_akhir_secondary_proses, 0 saldo_awal_defect_sewing, 0 total_defect_sewing, 0 total_input_rework_sewing, 0 saldo_akhir_defect_sewing, 0 saldo_awal_defect_spotcleaning, 0 total_defect_spotcleaning, 0 total_input_rework_spotcleaning, 0 saldo_akhir_defect_spotcleaning, 0 saldo_awal_defect_mending, 0 total_defect_mending, 0 total_input_rework_mending, 0 saldo_akhir_mending, 0 saldo_awal_reject, 0 qty_reject_in, 0 qty_reworked, 0 qty_rejected, 0 saldo_akhir_qc_reject, 0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
            0 saldo_awal_sp_pasang_kancing, 0 saldo_awal_sp_bartack, 0 saldo_awal_sp_heatseal, 0 saldo_awal_sp_snap, 0 saldo_awal_sp_embro,
            0 total_in_sp_pasang_kancing, 0 rft_sp_pasang_kancing, 0 defect_sp_pasang_kancing, 0 rework_sp_pasang_kancing, 0 reject_sp_pasang_kancing, 0 total_in_sp_bartack, 0 rft_sp_bartack, 0 defect_sp_bartack, 0 rework_sp_bartack, 0 reject_sp_bartack, 0 total_in_sp_heatseal, 0 rft_sp_heatseal, 0 defect_sp_heatseal, 0 rework_sp_heatseal, 0 reject_sp_heatseal, 0 total_in_sp_snap, 0 rft_sp_snap, 0 defect_sp_snap, 0 rework_sp_snap, 0 reject_sp_snap, 0 total_in_sp_embro, 0 rft_sp_embro, 0 defect_sp_embro, 0 rework_sp_embro, 0 reject_sp_embro,
            sewing_adjust_before, sewing_adjust, qc_finishing_adjust_before, qc_finishing_adjust, finishing_adjust_before, finishing_adjust, defect_sewing_adjust_before, defect_sewing_adjust, defect_spotcleaning_adjust_before, defect_spotcleaning_adjust, defect_mending_adjust_before, defect_mending_adjust, qc_reject_adjust_before, qc_reject_adjust, qc_transit_terima_qc_reject_adjust_before, qc_transit_terima_qc_reject_adjust,
            finishing_adjust_pasang_kancing_before, finishing_adjust_pasang_kancing, finishing_adjust_bartack_before, finishing_adjust_bartack, finishing_adjust_heatseal_before, finishing_adjust_heatseal, finishing_adjust_snap_before, finishing_adjust_snap, finishing_adjust_embro_before, finishing_adjust_embro,
            0 sewing_switching_in_before, 0 sewing_switching_in, 0 qc_finishing_switching_in_before, 0 qc_finishing_switching_in, 0 finishing_switching_in_before, 0 finishing_switching_in, 0 defect_sewing_switching_in_before, 0 defect_sewing_switching_in, 0 defect_spotcleaning_switching_in_before, 0 defect_spotcleaning_switching_in, 0 defect_mending_switching_in_before, 0 defect_mending_switching_in, 0 qc_reject_switching_in_before, 0 qc_reject_switching_in,
            0 sewing_switching_out_before, 0 sewing_switching_out, 0 qc_finishing_switching_out_before, 0 qc_finishing_switching_out, 0 finishing_switching_out_before, 0 finishing_switching_out, 0 defect_sewing_switching_out_before, 0 defect_sewing_switching_out, 0 defect_spotcleaning_switching_out_before, 0 defect_spotcleaning_switching_out, 0 defect_mending_switching_out_before, 0 defect_mending_switching_out, 0 qc_reject_switching_out_before, 0 qc_reject_switching_out,
            0 qty_transit_keluar_packing_before, 0 qty_transit_keluar_packing
            from query_adjust
            UNION ALL
            select buyer, no_ws as ws, style as styleno, color, size, 0 saldo_awal_sewing, 0 qty_loading, 0 terima_gudang, 0 qty_in_subcont, 0 input_rework_sewing, 0 input_rework_spotcleaning, 0 input_rework_mending, 0 defect_sewing, 0 defect_spotcleaning, 0 defect_mending, 0 qty_sew_reject, 0 qty_sewing, 0 qty_out_subcont, 0 saldo_akhir_sewing, 0 saldo_awal_finishing, 0 input_rework_sewing_f, 0 input_rework_spotcleaning_f, 0 input_rework_mending_f, 0 defect_sewing_f, 0 defect_spotcleaning_f, 0 defect_mending_f, 0 qty_fin_reject, 0 qty_finishing, 0 saldo_akhir_finishing, 0 saldo_awal_secondary_proses, 0 total_in_sp, 0 rework_sp, 0 defect_sp, 0 reject_sp, 0 rft_sp, 0 saldo_akhir_secondary_proses, 0 saldo_awal_defect_sewing, 0 total_defect_sewing, 0 total_input_rework_sewing, 0 saldo_akhir_defect_sewing, 0 saldo_awal_defect_spotcleaning, 0 total_defect_spotcleaning, 0 total_input_rework_spotcleaning, 0 saldo_akhir_defect_spotcleaning, 0 saldo_awal_defect_mending, 0 total_defect_mending, 0 total_input_rework_mending, 0 saldo_akhir_mending, 0 saldo_awal_reject, 0 qty_reject_in, 0 qty_reworked, 0 qty_rejected, 0 saldo_akhir_qc_reject, 0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
            0 saldo_awal_sp_pasang_kancing, 0 saldo_awal_sp_bartack, 0 saldo_awal_sp_heatseal, 0 saldo_awal_sp_snap, 0 saldo_awal_sp_embro,
            0 total_in_sp_pasang_kancing, 0 rft_sp_pasang_kancing, 0 defect_sp_pasang_kancing, 0 rework_sp_pasang_kancing, 0 reject_sp_pasang_kancing, 0 total_in_sp_bartack, 0 rft_sp_bartack, 0 defect_sp_bartack, 0 rework_sp_bartack, 0 reject_sp_bartack, 0 total_in_sp_heatseal, 0 rft_sp_heatseal, 0 defect_sp_heatseal, 0 rework_sp_heatseal, 0 reject_sp_heatseal, 0 total_in_sp_snap, 0 rft_sp_snap, 0 defect_sp_snap, 0 rework_sp_snap, 0 reject_sp_snap, 0 total_in_sp_embro, 0 rft_sp_embro, 0 defect_sp_embro, 0 rework_sp_embro, 0 reject_sp_embro,
            0 sewing_adjust_before, 0 sewing_adjust, 0 qc_finishing_adjust_before, 0 qc_finishing_adjust, 0 finishing_adjust_before, 0 finishing_adjust, 0 defect_sewing_adjust_before, 0 defect_sewing_adjust, 0 defect_spotcleaning_adjust_before, 0 defect_spotcleaning_adjust, 0 defect_mending_adjust_before, 0 defect_mending_adjust, 0 qc_reject_adjust_before, 0 qc_reject_adjust, 0 qc_transit_terima_qc_reject_adjust_before, 0 qc_transit_terima_qc_reject_adjust,
            0 finishing_adjust_pasang_kancing_before, 0 finishing_adjust_pasang_kancing, 0 finishing_adjust_bartack_before, 0 finishing_adjust_bartack, 0 finishing_adjust_heatseal_before, 0 finishing_adjust_heatseal, 0 finishing_adjust_snap_before, 0 finishing_adjust_snap, 0 finishing_adjust_embro_before, 0 finishing_adjust_embro,
            sewing_switching_in_before, sewing_switching_in, qc_finishing_switching_in_before, qc_finishing_switching_in, finishing_switching_in_before, finishing_switching_in, defect_sewing_switching_in_before, defect_sewing_switching_in, defect_spotcleaning_switching_in_before, defect_spotcleaning_switching_in, defect_mending_switching_in_before, defect_mending_switching_in, qc_reject_switching_in_before, qc_reject_switching_in,
            sewing_switching_out_before, sewing_switching_out, qc_finishing_switching_out_before, qc_finishing_switching_out, finishing_switching_out_before, finishing_switching_out, defect_sewing_switching_out_before, defect_sewing_switching_out, defect_spotcleaning_switching_out_before, defect_spotcleaning_switching_out, defect_mending_switching_out_before, defect_mending_switching_out, qc_reject_switching_out_before, qc_reject_switching_out,
            0 qty_transit_keluar_packing_before, 0 qty_transit_keluar_packing
            from query_switching
            UNION ALL
            select buyer, ws, styleno, color, size, 0 saldo_awal_sewing, 0 qty_loading, 0 terima_gudang, 0 qty_in_subcont, 0 input_rework_sewing, 0 input_rework_spotcleaning, 0 input_rework_mending, 0 defect_sewing, 0 defect_spotcleaning, 0 defect_mending, 0 qty_sew_reject, 0 qty_sewing, 0 qty_out_subcont, 0 saldo_akhir_sewing, 0 saldo_awal_finishing, 0 input_rework_sewing_f, 0 input_rework_spotcleaning_f, 0 input_rework_mending_f, 0 defect_sewing_f, 0 defect_spotcleaning_f, 0 defect_mending_f, 0 qty_fin_reject, 0 qty_finishing, 0 saldo_akhir_finishing, 0 saldo_awal_secondary_proses, 0 total_in_sp, 0 rework_sp, 0 defect_sp, 0 reject_sp, 0 rft_sp, 0 saldo_akhir_secondary_proses, 0 saldo_awal_defect_sewing, 0 total_defect_sewing, 0 total_input_rework_sewing, 0 saldo_akhir_defect_sewing, 0 saldo_awal_defect_spotcleaning, 0 total_defect_spotcleaning, 0 total_input_rework_spotcleaning, 0 saldo_akhir_defect_spotcleaning, 0 saldo_awal_defect_mending, 0 total_defect_mending, 0 total_input_rework_mending, 0 saldo_akhir_mending, 0 saldo_awal_reject, 0 qty_reject_in, 0 qty_reworked, 0 qty_rejected, 0 saldo_akhir_qc_reject, 0 qty_transit_terima_sewing_before, 0 qty_transit_terima_qc_finishing_before, 0 qty_transit_terima_finishing_before, 0 qty_transit_keluar_qc_reject_before,
            0 saldo_awal_sp_pasang_kancing, 0 saldo_awal_sp_bartack, 0 saldo_awal_sp_heatseal, 0 saldo_awal_sp_snap, 0 saldo_awal_sp_embro,
            0 total_in_sp_pasang_kancing, 0 rft_sp_pasang_kancing, 0 defect_sp_pasang_kancing, 0 rework_sp_pasang_kancing, 0 reject_sp_pasang_kancing, 0 total_in_sp_bartack, 0 rft_sp_bartack, 0 defect_sp_bartack, 0 rework_sp_bartack, 0 reject_sp_bartack, 0 total_in_sp_heatseal, 0 rft_sp_heatseal, 0 defect_sp_heatseal, 0 rework_sp_heatseal, 0 reject_sp_heatseal, 0 total_in_sp_snap, 0 rft_sp_snap, 0 defect_sp_snap, 0 rework_sp_snap, 0 reject_sp_snap, 0 total_in_sp_embro, 0 rft_sp_embro, 0 defect_sp_embro, 0 rework_sp_embro, 0 reject_sp_embro,
            0 sewing_adjust_before, 0 sewing_adjust, 0 qc_finishing_adjust_before, 0 qc_finishing_adjust, 0 finishing_adjust_before, 0 finishing_adjust, 0 defect_sewing_adjust_before, 0 defect_sewing_adjust, 0 defect_spotcleaning_adjust_before, 0 defect_spotcleaning_adjust, 0 defect_mending_adjust_before, 0 defect_mending_adjust, 0 qc_reject_adjust_before, 0 qc_reject_adjust, 0 qc_transit_terima_qc_reject_adjust_before, 0 qc_transit_terima_qc_reject_adjust,
            0 finishing_adjust_pasang_kancing_before, 0 finishing_adjust_pasang_kancing, 0 finishing_adjust_bartack_before, 0 finishing_adjust_bartack, 0 finishing_adjust_heatseal_before, 0 finishing_adjust_heatseal, 0 finishing_adjust_snap_before, 0 finishing_adjust_snap, 0 finishing_adjust_embro_before, 0 finishing_adjust_embro,
            0 sewing_switching_in_before, 0 sewing_switching_in, 0 qc_finishing_switching_in_before, 0 qc_finishing_switching_in, 0 finishing_switching_in_before, 0 finishing_switching_in, 0 defect_sewing_switching_in_before, 0 defect_sewing_switching_in, 0 defect_spotcleaning_switching_in_before, 0 defect_spotcleaning_switching_in, 0 defect_mending_switching_in_before, 0 defect_mending_switching_in, 0 qc_reject_switching_in_before, 0 qc_reject_switching_in,
            0 sewing_switching_out_before, 0 sewing_switching_out, 0 qc_finishing_switching_out_before, 0 qc_finishing_switching_out, 0 finishing_switching_out_before, 0 finishing_switching_out, 0 defect_sewing_switching_out_before, 0 defect_sewing_switching_out, 0 defect_spotcleaning_switching_out_before, 0 defect_spotcleaning_switching_out, 0 defect_mending_switching_out_before, 0 defect_mending_switching_out, 0 qc_reject_switching_out_before, 0 qc_reject_switching_out,
            qty_transit_keluar_packing_before, qty_transit_keluar_packing
            from query_keluar_packing
            ) a GROUP BY buyer, ws, styleno, color, size
        ";

        return $query;
    }

    public function show_mut_output(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $prev_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $tgl_saldo = '2026-03-01';
        $buyer = $request->buyer;

        if (!empty($buyer)) {
            $filter = "WHERE buyer = '$buyer'";
            $filter_subcont = " AND buyer = '$buyer'";
            $filter_terima_gudang = " AND Supplier = '$buyer'";
        } else {
            $filter = '';
            $filter_subcont = '';
            $filter_terima_gudang = '';
        }

        if ($request->ajax()) {
            // ✅ If bulan or tahun is missing, return no data
            if ($start_date === null || $end_date === null) {
                return response()->json(['data' => []]);
            } else {
                {
                    $query = $this->buildQueryMutasiOutput($start_date, $end_date, $buyer);
                    $rawData = DB::connection('mysql_sb')->select($query);
                }
                return response()->json([
                    'data' => $rawData // ✅ simplified response
                ]);
            }
        }
    }

    // public function export_excel_mut_output(Request $request)
    // {
    //     return Excel::download(new export_excel_mut_output($request->start_date, $request->end_date, $request->buyer), 'Laporan_Penerimaan FG_Stok.xlsx');
    // }



    public function export_excel_mut_output(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $prev_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $tgl_saldo = '2026-03-01';
        $buyer = $request->buyer;

        $batasTanggal = '2026-07-01';
        $finishingOld = $end_date < $batasTanggal;

        if (!empty($buyer)) {
            $filter = "WHERE buyer = '$buyer'";
            $filter_subcont = " AND buyer = '$buyer'";
            $filter_terima_gudang = " AND Supplier = '$buyer'";
        } else {
            $filter = '';
            $filter_subcont = '';
            $filter_terima_gudang = '';
        }

        $query = $this->buildQueryMutasiOutput($start_date, $end_date, $buyer);
        $data = DB::connection('mysql_sb')->select($query);

        // Create Excel file using FastExcel
        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Report Mutasi WIP Sewing ' . Carbon::parse($start_date)->format('d-m-Y') . ' - ' . Carbon::parse($end_date)->format('d-m-Y'), ['font-size' => 12]);
        if ($finishingOld) {
            $sheet->mergeCells('A1:BI1');
        } else {
            $sheet->mergeCells('A1:CW1');
        }

        // ======================================================
        // MERGE HEADER
        // ======================================================

        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('F2:T2');
        $sheet->mergeCells('U2:AF2');

        if ($finishingOld) {

            // Sebelum 1 Juli 2026
            $sheet->mergeCells('AG2:AN2'); // Finishing

            $sheet->mergeCells('AO2:AS2'); // Defect Sewing
            $sheet->mergeCells('AT2:AX2'); // Defect Spotcleaning
            $sheet->mergeCells('AY2:BC2'); // Defect Mending
            $sheet->mergeCells('BD2:BI2'); // QC Reject

        } else {

            // 1 Juli 2026 dan seterusnya
            $sheet->mergeCells('AG2:AN2'); // Pasang Kancing
            $sheet->mergeCells('AO2:AV2'); // Bartack
            $sheet->mergeCells('AW2:BD2'); // Heatseal
            $sheet->mergeCells('BE2:BL2'); // Snap
            $sheet->mergeCells('BM2:BT2'); // Embro

            $sheet->mergeCells('BU2:BY2'); // Defect Sewing
            $sheet->mergeCells('BZ2:CD2'); // Defect Spotcleaning
            $sheet->mergeCells('CE2:CI2'); // Defect Mending
            $sheet->mergeCells('CJ2:CQ2'); // Transit
            $sheet->mergeCells('CR2:CW2'); // QC Reject
        }

        // Isi value + apply bold + border
        $headers = [
            'A2:E2'  => ['text' => 'Jenis Produk', 'color' => '#ADD8E6'],
            'F2:T2'  => ['text' => 'Sewing', 'color' => '#FFF2CC'],
            'U2:AF2' => ['text' => 'QC Finishing', 'color' => '#F4CCCC'],
        ];

        if ($finishingOld) {
            $headers += [
                'AG2:AN2' => [
                    'text' => 'Finishing',
                    'color' => '#5DADE2'
                ],

                'AO2:AS2' => [
                    'text' => 'Defect Sewing',
                    'color' => '#FFE5B4'
                ],

                'AT2:AX2' => [
                    'text' => 'Defect Spotcleaning',
                    'color' => '#E6E6FA'
                ],

                'AY2:BC2' => [
                    'text' => 'Defect Mending',
                    'color' => '#FFF2CC'
                ],

                'BD2:BI2' => [
                    'text' => 'QC Reject',
                    'color' => '#F4CCCC'
                ],
            ];

        } else {

            $headers += [
                'AG2:AN2' => [
                    'text' => 'Finishing - Pasang Kancing',
                    'color' => '#90EE90'
                ],

                'AO2:AV2' => [
                    'text' => 'Finishing - Bartack',
                    'color' => '#87CEEB'
                ],

                'AW2:BD2' => [
                    'text' => 'Finishing - Heatseal',
                    'color' => '#D8BFD8'
                ],

                'BE2:BL2' => [
                    'text' => 'Finishing - Snap',
                    'color' => '#5DADE2'
                ],

                'BM2:BT2' => [
                    'text' => 'Finishing - Embro',
                    'color' => '#C8E6C9'
                ],

                'BU2:BY2' => [
                    'text' => 'Defect Sewing',
                    'color' => '#FFE5B4'
                ],

                'BZ2:CD2' => [
                    'text' => 'Defect Spotcleaning',
                    'color' => '#E6E6FA'
                ],

                'CE2:CI2' => [
                    'text' => 'Defect Mending',
                    'color' => '#FFF2CC'
                ],

                'CJ2:CQ2' => [
                    'text' => 'Transit Terima QC Reject',
                    'color' => '#FFA94D'
                ],

                'CR2:CW2' => [
                    'text' => 'QC Reject',
                    'color' => '#F4CCCC'
                ],
            ];
        }

        foreach ($headers as $range => $value) {

            // Ambil start & end cell dari merge
            [$start, $end] = explode(':', $range);

            // Konversi kolom huruf
            $getColumn = function ($cell) {
                return preg_replace('/[0-9]/', '', $cell);
            };
            $getRow = function ($cell) {
                return preg_replace('/[A-Z]/i', '', $cell);
            };

            $startCol = $getColumn($start);
            $endCol   = $getColumn($end);
            $row      = $getRow($start);

            // Konversi huruf kolom ke angka
            $letterToNumber = function ($letters) {
                $letters = strtoupper($letters);
                $num = 0;
                for ($i = 0; $i < strlen($letters); $i++) {
                    $num = $num * 26 + (ord($letters[$i]) - 64);
                }
                return $num;
            };

            $numberToLetter = function ($num) {
                $str = '';
                while ($num > 0) {
                    $mod = ($num - 1) % 26;
                    $str = chr(65 + $mod) . $str;
                    $num = intval(($num - 1) / 26);
                }
                return $str;
            };

            $startNum = $letterToNumber($startCol);
            $endNum   = $letterToNumber($endCol);

            // Loop semua kolom di range -> apply border & value hanya di start cell
            for ($i = $startNum; $i <= $endNum; $i++) {
                $col = $numberToLetter($i);
                $cell = $col . $row;

                if ($cell === $start) {
                    $sheet->writeTo($cell, $value['text'], [
                        'font-style'     => 'bold',
                        'border'         => 'thin',
                        'fill-color'     => $value['color'],
                        'text-align'     => 'center',
                        'vertical-align' => 'center',
                    ]);
                } else {
                    $sheet->writeTo($cell, '', [
                        'border'     => 'thin',
                        'fill-color' => $value['color'],
                    ]);
                }
            }
        }

        // Merge semua range
        foreach (array_keys($headers) as $range) {
            $sheet->mergeCells($range);
        }

        $style = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#ADD8E6',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $sheet->writeTo('A3', 'Buyer', $style);
        $sheet->writeTo('B3', 'WS', $style);
        $sheet->writeTo('C3', 'Style', $style);
        $sheet->writeTo('D3', 'Color', $style);
        $sheet->writeTo('E3', 'Size', $style);

        // SEWING (F:T = 15 kolom)
        $styleLightYellow = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#FFF2CC',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $sheet->writeTo('F3', 'Saldo Awal', $styleLightYellow);
        $sheet->writeTo('G3', 'Terima Loading', $styleLightYellow);
        $sheet->writeTo('H3', 'Terima Gudang Stok', $styleLightYellow);
        $sheet->writeTo('I3', 'In Subcont', $styleLightYellow);
        $sheet->writeTo('J3', 'Output Rework Sewing', $styleLightYellow);
        $sheet->writeTo('K3', 'Output Rework Spotcleaning', $styleLightYellow);
        $sheet->writeTo('L3', 'Output Rework Mending', $styleLightYellow);
        $sheet->writeTo('M3', 'Defect Sewing', $styleLightYellow);
        $sheet->writeTo('N3', 'Defect Spotcleaning', $styleLightYellow);
        $sheet->writeTo('O3', 'Defect Mending', $styleLightYellow);
        $sheet->writeTo('P3', 'Reject', $styleLightYellow);
        $sheet->writeTo('Q3', 'Output', $styleLightYellow);
        $sheet->writeTo('R3', 'Out Subcont', $styleLightYellow);
        $sheet->writeTo('S3', 'Adjustment', $styleLightYellow);
        $sheet->writeTo('T3', 'Saldo Akhir', $styleLightYellow);

        // QC FINISHING (U:AF = 12 kolom)
        $stylePink = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#F4CCCC',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $sheet->writeTo('U3', 'Saldo Awal', $stylePink);
        $sheet->writeTo('V3', 'Terima Sewing', $stylePink);
        $sheet->writeTo('W3', 'Output Rework Sewing', $stylePink);
        $sheet->writeTo('X3', 'Output Rework Spotcleaning', $stylePink);
        $sheet->writeTo('Y3', 'Output Rework Mending', $stylePink);
        $sheet->writeTo('Z3', 'Defect Sewing', $stylePink);
        $sheet->writeTo('AA3', 'Defect Spotcleaning', $stylePink);
        $sheet->writeTo('AB3', 'Defect Mending', $stylePink);
        $sheet->writeTo('AC3', 'Reject', $stylePink);
        $sheet->writeTo('AD3', 'Output', $stylePink);
        $sheet->writeTo('AE3', 'Adjustment', $stylePink);
        $sheet->writeTo('AF3', 'Saldo Akhir', $stylePink);

        $styleFinishing = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#5DADE2',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $styleLightGreen = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#90EE90',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $styleLightBlue = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#87CEEB',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $stylePurple = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#D8BFD8',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $styleSnap = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#5DADE2',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $styleEmbro = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#C8E6C9',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $stylePeach = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#FFE5B4',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $styleLavender = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#E6E6FA',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        $styleOrange = [
            'font-style'     => 'bold',
            'border'         => 'thin',
            'fill-color'     => '#FFA94D',
            'text-align'     => 'center',
            'vertical-align' => 'center',
        ];

        if ($finishingOld) {

            // ==========================================
            // FINISHING LAMA AG:AN
            // ==========================================

            $styleFinishing = [
                'font-style'     => 'bold',
                'border'         => 'thin',
                'fill-color'     => '#5DADE2',
                'text-align'     => 'center',
                'vertical-align' => 'center',
            ];

            $sheet->writeTo('AG3', 'Saldo Awal', $styleFinishing);
            $sheet->writeTo('AH3', 'Terima', $styleFinishing);
            $sheet->writeTo('AI3', 'Rework', $styleFinishing);
            $sheet->writeTo('AJ3', 'Defect', $styleFinishing);
            $sheet->writeTo('AK3', 'Reject', $styleFinishing);
            $sheet->writeTo('AL3', 'Output', $styleFinishing);
            $sheet->writeTo('AM3', 'Adjustment', $styleFinishing);
            $sheet->writeTo('AN3', 'Saldo Akhir', $styleFinishing);


            // ==========================================
            // DEFECT SEWING AO:AS
            // ==========================================

            $sheet->writeTo('AO3', 'Saldo Awal', $stylePeach);
            $sheet->writeTo('AP3', 'Terima', $stylePeach);
            $sheet->writeTo('AQ3', 'Keluar', $stylePeach);
            $sheet->writeTo('AR3', 'Adjustment', $stylePeach);
            $sheet->writeTo('AS3', 'Saldo Akhir', $stylePeach);


            // ==========================================
            // DEFECT SPOTCLEANING AT:AX
            // ==========================================

            $sheet->writeTo('AT3', 'Saldo Awal', $styleLavender);
            $sheet->writeTo('AU3', 'Terima', $styleLavender);
            $sheet->writeTo('AV3', 'Keluar', $styleLavender);
            $sheet->writeTo('AW3', 'Adjustment', $styleLavender);
            $sheet->writeTo('AX3', 'Saldo Akhir', $styleLavender);


            // ==========================================
            // DEFECT MENDING AY:BC
            // ==========================================

            $sheet->writeTo('AY3', 'Saldo Awal', $styleLightYellow);
            $sheet->writeTo('AZ3', 'Terima', $styleLightYellow);
            $sheet->writeTo('BA3', 'Keluar', $styleLightYellow);
            $sheet->writeTo('BB3', 'Adjustment', $styleLightYellow);
            $sheet->writeTo('BC3', 'Saldo Akhir', $styleLightYellow);


            // ==========================================
            // QC REJECT BD:BI
            // ==========================================

            $sheet->writeTo('BD3', 'Saldo Awal', $stylePink);
            $sheet->writeTo('BE3', 'Terima', $stylePink);
            $sheet->writeTo('BF3', 'Keluar Sewing', $stylePink);
            $sheet->writeTo('BG3', 'Keluar Gudang Stok', $stylePink);
            $sheet->writeTo('BH3', 'Adjustment', $stylePink);
            $sheet->writeTo('BI3', 'Saldo Akhir', $stylePink);
        }else {

            // ==========================================
            // PASANG KANCING AG:AN
            // ==========================================

            $sheet->writeTo('AG3', 'Saldo Awal', $styleLightGreen);
            $sheet->writeTo('AH3', 'Terima', $styleLightGreen);
            $sheet->writeTo('AI3', 'Rework', $styleLightGreen);
            $sheet->writeTo('AJ3', 'Defect', $styleLightGreen);
            $sheet->writeTo('AK3', 'Reject', $styleLightGreen);
            $sheet->writeTo('AL3', 'Output', $styleLightGreen);
            $sheet->writeTo('AM3', 'Adjustment', $styleLightGreen);
            $sheet->writeTo('AN3', 'Saldo Akhir', $styleLightGreen);


            // BARTACK AO:AV
            $sheet->writeTo('AO3', 'Saldo Awal', $styleLightBlue);
            $sheet->writeTo('AP3', 'Terima', $styleLightBlue);
            $sheet->writeTo('AQ3', 'Rework', $styleLightBlue);
            $sheet->writeTo('AR3', 'Defect', $styleLightBlue);
            $sheet->writeTo('AS3', 'Reject', $styleLightBlue);
            $sheet->writeTo('AT3', 'Output', $styleLightBlue);
            $sheet->writeTo('AU3', 'Adjustment', $styleLightBlue);
            $sheet->writeTo('AV3', 'Saldo Akhir', $styleLightBlue);


            // HEATSEAL AW:BD
            $sheet->writeTo('AW3', 'Saldo Awal', $stylePurple);
            $sheet->writeTo('AX3', 'Terima', $stylePurple);
            $sheet->writeTo('AY3', 'Rework', $stylePurple);
            $sheet->writeTo('AZ3', 'Defect', $stylePurple);
            $sheet->writeTo('BA3', 'Reject', $stylePurple);
            $sheet->writeTo('BB3', 'Output', $stylePurple);
            $sheet->writeTo('BC3', 'Adjustment', $stylePurple);
            $sheet->writeTo('BD3', 'Saldo Akhir', $stylePurple);


            // SNAP BE:BL
            $sheet->writeTo('BE3', 'Saldo Awal', $styleSnap);
            $sheet->writeTo('BF3', 'Terima', $styleSnap);
            $sheet->writeTo('BG3', 'Rework', $styleSnap);
            $sheet->writeTo('BH3', 'Defect', $styleSnap);
            $sheet->writeTo('BI3', 'Reject', $styleSnap);
            $sheet->writeTo('BJ3', 'Output', $styleSnap);
            $sheet->writeTo('BK3', 'Adjustment', $styleSnap);
            $sheet->writeTo('BL3', 'Saldo Akhir', $styleSnap);


            // EMBRO BM:BT
            $sheet->writeTo('BM3', 'Saldo Awal', $styleEmbro);
            $sheet->writeTo('BN3', 'Terima', $styleEmbro);
            $sheet->writeTo('BO3', 'Rework', $styleEmbro);
            $sheet->writeTo('BP3', 'Defect', $styleEmbro);
            $sheet->writeTo('BQ3', 'Reject', $styleEmbro);
            $sheet->writeTo('BR3', 'Output', $styleEmbro);
            $sheet->writeTo('BS3', 'Adjustment', $styleEmbro);
            $sheet->writeTo('BT3', 'Saldo Akhir', $styleEmbro);


            // DEFECT SEWING BU:BY
            $sheet->writeTo('BU3', 'Saldo Awal', $stylePeach);
            $sheet->writeTo('BV3', 'Terima', $stylePeach);
            $sheet->writeTo('BW3', 'Keluar', $stylePeach);
            $sheet->writeTo('BX3', 'Adjustment', $stylePeach);
            $sheet->writeTo('BY3', 'Saldo Akhir', $stylePeach);


            // DEFECT SPOTCLEANING BZ:CD
            $sheet->writeTo('BZ3', 'Saldo Awal', $styleLavender);
            $sheet->writeTo('CA3', 'Terima', $styleLavender);
            $sheet->writeTo('CB3', 'Keluar', $styleLavender);
            $sheet->writeTo('CC3', 'Adjustment', $styleLavender);
            $sheet->writeTo('CD3', 'Saldo Akhir', $styleLavender);


            // DEFECT MENDING CE:CI
            $sheet->writeTo('CE3', 'Saldo Awal', $styleLightYellow);
            $sheet->writeTo('CF3', 'Terima', $styleLightYellow);
            $sheet->writeTo('CG3', 'Keluar', $styleLightYellow);
            $sheet->writeTo('CH3', 'Adjustment', $styleLightYellow);
            $sheet->writeTo('CI3', 'Saldo Akhir', $styleLightYellow);


            // TRANSIT CJ:CQ
            $sheet->writeTo('CJ3', 'Saldo Awal', $styleOrange);
            $sheet->writeTo('CK3', 'Terima Sewing', $styleOrange);
            $sheet->writeTo('CL3', 'Terima QC Finishing', $styleOrange);
            $sheet->writeTo('CM3', 'Terima Finishing', $styleOrange);
            $sheet->writeTo('CN3', 'Keluar QC Reject', $styleOrange);
            $sheet->writeTo('CO3', 'Keluar Packing', $styleOrange);
            $sheet->writeTo('CP3', 'Adjustment', $styleOrange);
            $sheet->writeTo('CQ3', 'Saldo Akhir', $styleOrange);


            // QC REJECT CR:CW
            $sheet->writeTo('CR3', 'Saldo Awal', $stylePink);
            $sheet->writeTo('CS3', 'Terima', $stylePink);
            $sheet->writeTo('CT3', 'Keluar Sewing', $stylePink);
            $sheet->writeTo('CU3', 'Keluar Gudang Stok', $stylePink);
            $sheet->writeTo('CV3', 'Adjustment', $stylePink);
            $sheet->writeTo('CW3', 'Saldo Akhir', $stylePink);
        }

        $rowNumber = 4;
        collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$rowNumber, $finishingOld) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->buyer ?? "-",
                    $row->ws ?? "-",
                    $row->styleno ?? "-",
                    $row->color ?? "-",
                    $row->size ?? "-",

                    // SEWING (15 kolom)
                    $row->saldo_awal_sewing ?? 0,
                    $row->qty_loading ?? 0,
                    $row->terima_gudang ?? 0,
                    $row->qty_in_subcont ?? 0,
                    $row->input_rework_sewing ?? 0,
                    $row->input_rework_spotcleaning ?? 0,
                    $row->input_rework_mending ?? 0,
                    $row->defect_sewing ?? 0,
                    $row->defect_spotcleaning ?? 0,
                    $row->defect_mending ?? 0,
                    $row->qty_sew_reject ?? 0,
                    $row->qty_sewing ?? 0,
                    $row->qty_out_subcont ?? 0,
                    $row->sewing_adjust ?? 0,
                    $row->saldo_akhir_sewing ?? 0,

                    // QC FINISHING (12 kolom)
                    $row->saldo_awal_finishing ?? 0,
                    $row->qty_sewing ?? 0,
                    $row->input_rework_sewing_f ?? 0,
                    $row->input_rework_spotcleaning_f ?? 0,
                    $row->input_rework_mending_f ?? 0,
                    $row->defect_sewing_f ?? 0,
                    $row->defect_spotcleaning_f ?? 0,
                    $row->defect_mending_f ?? 0,
                    $row->qty_fin_reject ?? 0,
                    $row->qty_finishing ?? 0,
                    $row->qc_finishing_adjust ?? 0,
                    $row->saldo_akhir_finishing ?? 0,
                ];

                if ($finishingOld) {

                    $rowArr = array_merge($rowArr, [

                        // FINISHING ALL AG:AN
                        $row->saldo_awal_secondary_proses ?? 0,
                        $row->total_in_sp ?? 0,
                        $row->rework_sp ?? 0,
                        $row->defect_sp ?? 0,
                        $row->reject_sp ?? 0,
                        $row->rft_sp ?? 0,
                        $row->finishing_adjust ?? 0,
                        $row->saldo_akhir_secondary_proses ?? 0,

                        // DEFECT SEWING AO:AS
                        $row->saldo_awal_defect_sewing ?? 0,
                        $row->total_defect_sewing ?? 0,
                        $row->total_input_rework_sewing ?? 0,
                        $row->defect_sewing_adjust ?? 0,
                        $row->saldo_akhir_defect_sewing ?? 0,

                        // DEFECT SPOTCLEANING AT:AX
                        $row->saldo_awal_defect_spotcleaning ?? 0,
                        $row->total_defect_spotcleaning ?? 0,
                        $row->total_input_rework_spotcleaning ?? 0,
                        $row->defect_spotcleaning_adjust ?? 0,
                        $row->saldo_akhir_defect_spotcleaning ?? 0,

                        // DEFECT MENDING AY:BC
                        $row->saldo_awal_defect_mending ?? 0,
                        $row->total_defect_mending ?? 0,
                        $row->total_input_rework_mending ?? 0,
                        $row->defect_mending_adjust ?? 0,
                        $row->saldo_akhir_mending ?? 0,

                        // QC REJECT BD:BI
                        $row->saldo_awal_reject ?? 0,
                        $row->qty_reject_in ?? 0,
                        $row->qty_reworked ?? 0,
                        $row->qty_rejected ?? 0,
                        $row->qc_reject_adjust ?? 0,
                        $row->saldo_akhir_qc_reject ?? 0,
                    ]);

                } else {

                    $rowArr = array_merge($rowArr, [

                        // PASANG KANCING AG:AN
                        $row->saldo_awal_finishing_pasang_kancing ?? 0,
                        $row->total_in_sp_pasang_kancing ?? 0,
                        $row->rework_sp_pasang_kancing ?? 0,
                        $row->defect_sp_pasang_kancing ?? 0,
                        $row->reject_sp_pasang_kancing ?? 0,
                        $row->rft_sp_pasang_kancing ?? 0,
                        $row->finishing_adjust_pasang_kancing ?? 0,
                        $row->saldo_akhir_finishing_pasang_kancing ?? 0,

                        // BARTACK AO:AV
                        $row->saldo_awal_finishing_bartack ?? 0,
                        $row->total_in_sp_bartack ?? 0,
                        $row->rework_sp_bartack ?? 0,
                        $row->defect_sp_bartack ?? 0,
                        $row->reject_sp_bartack ?? 0,
                        $row->rft_sp_bartack ?? 0,
                        $row->finishing_adjust_bartack ?? 0,
                        $row->saldo_akhir_finishing_bartack ?? 0,

                        // HEATSEAL AW:BD
                        $row->saldo_awal_finishing_heatseal ?? 0,
                        $row->total_in_sp_heatseal ?? 0,
                        $row->rework_sp_heatseal ?? 0,
                        $row->defect_sp_heatseal ?? 0,
                        $row->reject_sp_heatseal ?? 0,
                        $row->rft_sp_heatseal ?? 0,
                        $row->finishing_adjust_heatseal ?? 0,
                        $row->saldo_akhir_finishing_heatseal ?? 0,

                        // SNAP BE:BL
                        $row->saldo_awal_finishing_snap ?? 0,
                        $row->total_in_sp_snap ?? 0,
                        $row->rework_sp_snap ?? 0,
                        $row->defect_sp_snap ?? 0,
                        $row->reject_sp_snap ?? 0,
                        $row->rft_sp_snap ?? 0,
                        $row->finishing_adjust_snap ?? 0,
                        $row->saldo_akhir_finishing_snap ?? 0,

                        // EMBRO BM:BT
                        $row->saldo_awal_finishing_embro ?? 0,
                        $row->total_in_sp_embro ?? 0,
                        $row->rework_sp_embro ?? 0,
                        $row->defect_sp_embro ?? 0,
                        $row->reject_sp_embro ?? 0,
                        $row->rft_sp_embro ?? 0,
                        $row->finishing_adjust_embro ?? 0,
                        $row->saldo_akhir_finishing_embro ?? 0,

                        // DEFECT SEWING BU:BY
                        $row->saldo_awal_defect_sewing ?? 0,
                        $row->total_defect_sewing ?? 0,
                        $row->total_input_rework_sewing ?? 0,
                        $row->defect_sewing_adjust ?? 0,
                        $row->saldo_akhir_defect_sewing ?? 0,

                        // DEFECT SPOTCLEANING BZ:CD
                        $row->saldo_awal_defect_spotcleaning ?? 0,
                        $row->total_defect_spotcleaning ?? 0,
                        $row->total_input_rework_spotcleaning ?? 0,
                        $row->defect_spotcleaning_adjust ?? 0,
                        $row->saldo_akhir_defect_spotcleaning ?? 0,

                        // DEFECT MENDING CE:CI
                        $row->saldo_awal_defect_mending ?? 0,
                        $row->total_defect_mending ?? 0,
                        $row->total_input_rework_mending ?? 0,
                        $row->defect_mending_adjust ?? 0,
                        $row->saldo_akhir_mending ?? 0,

                        // TRANSIT CJ:CQ
                        $row->qty_transit_saldo_awal ?? 0,
                        $row->qty_transit_terima_sewing ?? 0,
                        $row->qty_transit_terima_qc_finishing ?? 0,
                        $row->qty_transit_terima_finishing ?? 0,
                        $row->qty_transit_keluar_qc_reject ?? 0,
                        $row->qty_transit_keluar_packing ?? 0,
                        $row->qty_transit_adjustment ?? 0,
                        $row->qty_transit_keluar_saldo_akhir ?? 0,

                        // QC REJECT CR:CW
                        $row->saldo_awal_reject ?? 0,
                        $row->qty_reject_in ?? 0,
                        $row->qty_reworked ?? 0,
                        $row->qty_rejected ?? 0,
                        $row->qc_reject_adjust ?? 0,
                        $row->saldo_akhir_qc_reject ?? 0,
                    ]);
                }

                $sheet->writeRow($rowArr, [
                    'border'     => 'thin',
                    'text-align' => 'left'
                ]);

                for ($col = 6; $col <= count($rowArr); $col++) {
                    $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $rowNumber;

                    $val = $rowArr[$col - 1] ?? '';

                    if (!is_scalar($val) || $val === null) {
                        $val = '';
                    }

                    $sheet->writeTo($cell, (float) $val, [
                        'text-align'    => 'right',
                        'number-format' => '#,##0'
                    ]);
                }

                $rowNumber++;
            }
        });

        $filename = 'Laporan dc in  (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }
}
