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


class export_excel_mut_output implements FromView, ShouldAutoSize, WithEvents
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

        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $prev_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        $buyer = $this->buyer;

        if (!empty($buyer)) {
            $filter = "WHERE buyer = '$buyer'";
        } else {
            $filter = '';
        }

        $rawData = DB::connection('mysql_sb')->select("WITH
saldo_loading as (
				SELECT
                    id_so_det,
					tanggal_loading,
                    SUM(qty_loading) as qty_loading
                FROM (
                    SELECT
                        b.so_det_id AS id_so_det,
                        a.tanggal_loading,
                        MIN( qty ) AS qty_loading
                    FROM
                        laravel_nds.loading_line a
                        INNER JOIN laravel_nds.stocker_input b ON a.stocker_id = b.id
                    WHERE
                        b.form_cut_id > 0 and tanggal_loading >= '$start_date'and  tanggal_loading <= '$end_date'
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
						and tanggal_loading >= '$start_date' and tanggal_loading <= '$end_date'
                    GROUP BY
                        so_det_id,
                        form_reject_id,
                        tanggal_loading
                ) loading
                GROUP BY
					tanggal_loading,
                    id_so_det
				ORDER BY
					tanggal_loading asc,
					id_so_det asc
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
								GROUP BY r.so_det_id,date(osod.created_at)

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
SUM(qty_reworked) AS qty_reworked

FROM
(
SELECT
tanggal_loading, id_so_det, qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_loading
UNION ALL
SELECT
tgl_sewing, so_det_id, 0 AS qty_loading, qty_sew AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_sewing
UNION ALL
SELECT
tgl_defect, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, input_rework_sewing, input_rework_spotcleaning, input_rework_mending,
 defect_sewing, defect_spotcleaning, defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_sewing_defect
UNION ALL
SELECT
tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_sewing_reject
UNION ALL
SELECT
tgl_finishing, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject,  qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS qty_fin_reject,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_finishing
UNION ALL
SELECT
tgl_defect, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
input_rework_sewing AS input_rework_sewing_f, input_rework_spotcleaning AS input_rework_spotcleaning_f, input_rework_mending AS input_rework_mending_f,
defect_sewing AS defect_sewing_f, defect_spotcleaning AS defect_spotcleaning_f, defect_mending AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_finishing_defect
UNION ALL
SELECT
tgl_fin_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
qty_fin_reject AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM saldo_finishing_reject
UNION ALL
SELECT
tgl_proses, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
total_in AS total_in_sp, rft AS rft_sp, defect AS defect_sp, rework AS rework_sp, reject AS reject_sp, reject_defect AS reject_defect_sp,
0 AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM secondary_proses
UNION ALL
SELECT
tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
qty_reject_in AS qty_reject_in, 0 AS qty_rejected, 0 AS qty_reworked
FROM qc_reject
UNION ALL
SELECT
tgl_reject, so_det_id, 0 AS qty_loading, 0 AS qty_sewing, 0 AS input_rework_sewing, 0 AS input_rework_spotcleaning, 0 AS input_rework_mending,
0 AS defect_sewing, 0 AS defect_spotcleaning, 0 AS defect_mending, 0 AS qty_sew_reject, 0 AS qty_finishing,
0 AS input_rework_sewing_f, 0 AS input_rework_spotcleaning_f, 0 AS input_rework_mending_f,
0 AS defect_sewing_f, 0 AS defect_spotcleaning_f, 0 AS defect_mending_f,
0 AS qty_fin_reject,
0 AS total_in_sp, 0 AS rft_sp, 0 AS defect_sp, 0 AS rework_sp, 0 AS reject_sp, 0 AS reject_defect_sp,
0 AS qty_reject_in, qty_rejected AS qty_rejected, qty_reworked AS qty_reworked
FROM qc_reject_out

)	mut_sew
GROUP BY id_so_det
),
saldo_awal as (
SELECT
id_so_det,
SUM(qty_loading) qty_loading_awal,
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
FROM mut_wip_tmp
where tgl_trans <= '$prev_date'
group by id_so_det
),
saldo_awal_upload as(
SELECT
so_det_id,
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
sum(packing_central) packing_central
from laravel_nds.sa_report_output
where tgl_saldo = '2025-07-01'
group by so_det_id
),
m as (
SELECT id_so_det FROM saldo_awal
UNION
SELECT id_so_det FROM saldo_sew
UNION
SELECT so_det_id FROM saldo_awal_upload
)


SELECT
m.id_so_det,
buyer,
ws,
styleno,
color,
mb.size,

-- SEWING

COALESCE(su.loading, 0)
+ COALESCE(qty_loading_awal, 0)
+ COALESCE(input_rework_sewing_awal, 0)
+ COALESCE(input_rework_spotcleaning_awal, 0)
+ COALESCE(input_rework_mending_awal, 0)

- COALESCE(defect_sewing_awal, 0)
- COALESCE(defect_spotcleaning_awal, 0)
- COALESCE(defect_mending_awal, 0)

- COALESCE(qty_sew_reject_awal, 0)
- COALESCE(qty_sewing_awal, 0)
 AS saldo_awal_loading,

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
COALESCE(su.loading, 0)
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
COALESCE(qty_sewing_awal, 0)
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
COALESCE(qty_sewing_awal, 0)
+ COALESCE(input_rework_sewing_f_awal, 0)
+ COALESCE(input_rework_spotcleaning_f_awal, 0)
+ COALESCE(input_rework_mending_f_awal, 0)
- COALESCE(defect_sewing_f_awal, 0)
- COALESCE(defect_spotcleaning_f_awal, 0)
- COALESCE(defect_mending_f_awal, 0)
- COALESCE(qty_fin_reject_awal, 0)
- COALESCE(qty_finishing_awal, 0)
)
+ COALESCE(ss.qty_sewing, 0)
+ COALESCE(ss.input_rework_sewing_f, 0) + COALESCE(ss.input_rework_spotcleaning_f, 0) + COALESCE(ss.input_rework_mending_f, 0)
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
- COALESCE(ss.qty_reworked, 0) AS saldo_akhir_qc_reject

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
				size
				FROM signalbit_erp.so_det sd
				INNER JOIN signalbit_erp.so ON sd.id_so = so.id
				INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
				INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
				WHERE jd.cancel = 'N'
) mb on m.id_so_det = mb.id_so_det
LEFT JOIN signalbit_erp.master_size_new msn on mb.size = msn.size
$filter
ORDER BY buyer asc, ws asc, color asc, urutan asc
");


        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('sewing.report.excel.export_excel_mut_output', [

            'rawData' => $rawData,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow    = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // ===== 1. Center header (row 2 & 3) =====
                $headerRange = 'A2:' . $highestColumn . '3';

                $sheet->getStyle($headerRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Optional: kasih tinggi biar vertical center keliatan
                $sheet->getRowDimension(2)->setRowHeight(30);
                $sheet->getRowDimension(3)->setRowHeight(40);

                // ===== 2. Border seluruh tabel =====
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            }
        ];
    }
}
