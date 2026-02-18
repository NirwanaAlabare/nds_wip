<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\DC\ExportReportDc;
use DB;
use Excel;

class DcReportController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {

            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");



            $dataReport = DB::select("
                WITH
                    dc_before_saldo AS (
                        -- before saldo
                        WITH
                            dc_rekap AS (
                                SELECT
                                    dc_report_rekap.*
                                FROM dc_report_rekap
                                INNER JOIN (
                                    SELECT
                                        MAX(tanggal) tanggal
                                    FROM
                                        dc_report_rekap
                                    WHERE
                                        tanggal <= '".$dateFrom."'
                                ) tanggal_akhir_rekap on tanggal_akhir_rekap.tanggal = dc_report_rekap.tanggal
                            ),
                            dc as (
                                SELECT
                                        a.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc_main,
                                        null qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        null sec_in_in,
                                        null sec_in_rep_main,
                                        null sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out
                                from
                                        dc_in_input a
                                        left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input f on f.id = s.form_cut_id
                                        left join form_cut_reject fr on fr.id = s.form_reject_id
                                        left join form_cut_piece fp on fp.id = s.form_piece_id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on pd.part_id = p.id
                                        left join master_part mp on mp.id = pd.master_part_id
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                where
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN a.tgl_trans > tanggal_akhir_rekap.tanggal ELSE a.tgl_trans > '2026-01-01' END) AND
                                        a.tgl_trans < '".$dateFrom."' AND
                -- 						a.tgl_trans < '".$dateFrom."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        pd.part_status = 'main'
                                UNION ALL
                                SELECT
                                        a.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        null qty_in_dc_main,
                                        (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        null sec_in_in,
                                        null sec_in_rep_main,
                                        null sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out
                                from
                                        dc_in_input a
                                        left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input f on f.id = s.form_cut_id
                                        left join form_cut_reject fr on fr.id = s.form_reject_id
                                        left join form_cut_piece fp on fp.id = s.form_piece_id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on pd.part_id = p.id
                                        left join part_detail pdcom on pdcom.id = pd.from_part_detail
                                        left join part pcom on pcom.id = pdcom.part_id
                                        left join master_part mp on mp.id = pd.master_part_id
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                where
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN a.tgl_trans > tanggal_akhir_rekap.tanggal ELSE a.tgl_trans > '2026-01-01' END) AND
                                        a.tgl_trans < '".$dateFrom."' AND
                -- 						a.tgl_trans < '".$dateFrom."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),
                            sii_in as (
                                SELECT
                                    sii_in.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    (sii_in.qty_in) sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_in_input sii_in
                                    left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                    LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                WHERE
                                    (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN sii_in.tgl_trans > tanggal_akhir_rekap.tanggal ELSE sii_in.tgl_trans > '2026-01-01' END) AND
                                    sii_in.tgl_trans < '".$dateFrom."' AND
                -- 					sii_in.tgl_trans < '".$dateFrom."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    pd.part_status = 'main'
                                UNION ALL
                                SELECT
                                    sii_in.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    (sii_in.qty_in) sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_in_input sii_in
                                    left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                    LEFT JOIN (
                                        SELECT
                                            MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                            so_det_id,
                                            part_detail_id
                                        FROM
                                            dc_report_rekap
                                        WHERE
                                            tanggal < '".$dateFrom."'
                                        GROUP BY
                                            so_det_id,
                                            part_detail_id
                                    ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                WHERE
                                    (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN sii_in.tgl_trans > tanggal_akhir_rekap.tanggal ELSE sii_in.tgl_trans > '2026-01-01' END) AND
                                    sii_in.tgl_trans < '".$dateFrom."' AND
                -- 					sii_in.tgl_trans < '".$dateFrom."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),
                            sii as (
                                SELECT
                                    sii.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    sii.qty_replace sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    sii.qty_in sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_input sii
                                    left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                    LEFT JOIN (
                                        SELECT
                                            MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                            so_det_id,
                                            part_detail_id
                                        FROM
                                            dc_report_rekap
                                        WHERE
                                            tanggal < '".$dateFrom."'
                                        GROUP BY
                                            so_det_id,
                                            part_detail_id
                                    ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                WHERE
                                    (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN sii.tgl_trans > tanggal_akhir_rekap.tanggal ELSE sii.tgl_trans > '2026-01-01' END) AND
                                    sii.tgl_trans < '".$dateFrom."' AND
                                    -- sii.tgl_trans < '".$dateFrom."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    pd.part_status= 'main'
                                UNION ALL
                                SELECT
                                    sii.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    sii.qty_replace sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    sii.qty_in sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_input sii
                                    left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                    LEFT JOIN (
                                        SELECT
                                            MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                            so_det_id,
                                            part_detail_id
                                        FROM
                                            dc_report_rekap
                                        WHERE
                                            tanggal < '".$dateFrom."'
                                        GROUP BY
                                            so_det_id,
                                            part_detail_id
                                    ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                WHERE
                                    (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN sii.tgl_trans > tanggal_akhir_rekap.tanggal ELSE sii.tgl_trans > '2026-01-01' END) AND sii.tgl_trans < '".$dateFrom."' AND
                -- 					sii.tgl_trans < '".$dateFrom."' AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),
                            wod as (
                                    SELECT
                                        wod.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        null qty_in_dc_main,
                                        null qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        wod.qty sec_in_in_main,
                                        null sec_in_in,
                                        null sec_in_rep_main,
                                        null sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out
                                    FROM
                                        wip_out_det wod
                                        left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                    WHERE
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN DATE(wod.updated_at) > tanggal_akhir_rekap.tanggal ELSE DATE(wod.updated_at) > '2026-01-01' END) AND wod.updated_at <'".$dateFrom." 00:00:00' and
                -- 						wod.updated_at < '".$dateFrom." 00:00:00' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        pd.part_status= 'main'
                                    UNION ALL
                                    SELECT
                                        wod.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        null qty_in_dc_main,
                                        null qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        wod.qty sec_in_in,
                                        null sec_in_rep_main,
                                        null sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out
                                    FROM
                                        wip_out_det wod
                                        left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                    WHERE
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN DATE(wod.updated_at) > tanggal_akhir_rekap.tanggal ELSE DATE(wod.updated_at) > '2026-01-01' END) AND wod.updated_at < '".$dateFrom." 00:00:00' and
                -- 						wod.updated_at < '".$dateFrom." 00:00:00' and
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                                ),
                            si as (
                                SELECT
                                        si.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        null qty_in_dc_main,
                                        null qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        null sec_in_in,
                                        si.qty_replace sec_in_rep_main,
                                        null sec_in_rep,
                                        si.qty_in sec_in_out_main,
                                        null sec_in_out
                                    FROM
                                        secondary_in_input si
                                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join master_secondary ms on ms.id = pd.master_secondary_id
                                        left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                        left join master_secondary mms on mms.id = pds.master_secondary_id
                                        left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                    WHERE
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN si.tgl_trans > tanggal_akhir_rekap.tanggal ELSE si.tgl_trans > '2026-01-01' END) AND si.tgl_trans < '".$dateTo."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        pd.part_status= 'main' AND
                                        COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                                    UNION ALL
                                    SELECT
                                        si.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        null qty_in_dc_main,
                                        null qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        null sec_in_in,
                                        null sec_in_rep_main,
                                        si.qty_replace sec_in_rep,
                                        null sec_in_out_main,
                                        si.qty_in sec_in_out
                                    FROM
                                        secondary_in_input si
                                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join master_secondary ms on ms.id = pd.master_secondary_id
                                        left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                        left join master_secondary mms on mms.id = pds.master_secondary_id
                                        left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                    WHERE
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN si.tgl_trans > tanggal_akhir_rekap.tanggal ELSE si.tgl_trans > '2026-01-01' END) AND si.tgl_trans < '".$dateTo."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                        COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                            ),
                            loading_line as (
                                select
                                    panel,
                                    so_det_id,
                                    GROUP_CONCAT(stocker_id) stockers,
                                    SUM(loading_qty) loading_qty
                                from (
                                    select
                                        COALESCE(p_com.panel, p.panel) as panel,
                                        GROUP_CONCAT(ll.stocker_id) stocker_id,
                                        s.so_det_id,
                                        MIN(ll.qty) loading_qty
                                    from
                                        loading_line ll
                                        left join stocker_input s on s.id = ll.stocker_id
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p on p.id = pd.part_id
                                        left join part p_com on p_com.id = pd_com.part_id
                                        LEFT JOIN (
                                            SELECT
                                                MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                                so_det_id,
                                                part_detail_id
                                            FROM
                                                dc_report_rekap
                                            WHERE
                                                tanggal < '".$dateFrom."'
                                            GROUP BY
                                                so_det_id,
                                                part_detail_id
                                        ) tanggal_akhir_rekap on s.so_det_id = tanggal_akhir_rekap.so_det_id and s.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                    where
                                        (CASE WHEN tanggal_akhir_rekap.tanggal IS NOT NULL THEN ll.tanggal_loading > tanggal_akhir_rekap.tanggal ELSE ll.tanggal_loading > '2026-01-01' END) AND ll.tanggal_loading < '".$dateFrom."' AND
                                        -- ll.tanggal_loading < '".$dateFrom."' AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                    group by
                                        COALESCE(p_com.panel, p.panel),
                                        s.form_cut_id,
                                        s.form_reject_id,
							            s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject
                                ) as loading
                                group by
                                    panel,
                                    so_det_id
                            )

                                SELECT
                                    CURRENT_DATE as tanggal,
                                    saldo_dc.stockers,
                                    saldo_dc.buyer,
                                    saldo_dc.ws,
                                    saldo_dc.color,
                                    saldo_dc.id_so_det,
                                    saldo_dc.panel,
                                    saldo_dc.panel_status,
                                    saldo_dc.part_detail_id,
                                    saldo_dc.nama_part,
                                    saldo_dc.part_status,
                                    saldo_dc.saldo_awal,
                                    saldo_dc.qty_in,
                                    saldo_dc.kirim_secondary_dalam,
                                    saldo_dc.terima_repaired_secondary_dalam,
                                    saldo_dc.terima_good_secondary_dalam,
                                    saldo_dc.kirim_secondary_luar,
                                    saldo_dc.terima_repaired_secondary_luar,
                                    saldo_dc.terima_good_secondary_luar,
                                    saldo_dc.loading_qty,
                                    saldo_dc.saldo_awal+qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir,
                                    CURRENT_TIMESTAMP created_at,
                                    CURRENT_TIMESTAMP updated_at
                                FROM (
                                    SELECT
                                        GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                        msb.buyer,
                                        msb.ws,
                                        msb.styleno as style,
                                        msb.color,
                                        msb.id_so_det,
                                        p.panel,
                                        p.panel_status,
                                        pd.id as part_detail_id,
                                        GROUP_CONCAT(DISTINCT mp.nama_part) as nama_part,
                                        GROUP_CONCAT(DISTINCT pd.part_status) as part_status,
                                        COALESCE(rekap.saldo_akhir, 0) as saldo_awal,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                -- 		loading.stockers,
                                        COALESCE(loading_line.loading_qty, 0) loading_qty
                                    FROM (
                                        SELECT
                                            *
                                        FROM
                                            dc
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            sii_in
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            sii
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            wod
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            si
                                    ) saldo_dc
                                    LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                    LEFT JOIN part_detail pd on pd.id = saldo_dc.part_detail_id
                                    LEFT JOIN part p on p.id = pd.part_id
                                    LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and loading_line.panel = p.panel
                                    LEFT JOIN (
                                        SELECT
                                            MAX(COALESCE(tanggal, '2023-01-01')) tanggal,
                                            so_det_id,
                                            part_detail_id
                                        FROM
                                            dc_report_rekap
                                        WHERE
                                            tanggal < '".$dateFrom."'
                                        GROUP BY
                                            so_det_id,
                                            part_detail_id
                                    ) tanggal_akhir_rekap on saldo_dc.so_det_id = tanggal_akhir_rekap.so_det_id and saldo_dc.part_detail_id = tanggal_akhir_rekap.part_detail_id
                                    LEFT JOIN dc_report_rekap rekap on rekap.so_det_id = saldo_dc.so_det_id and rekap.part_detail_id = saldo_dc.part_detail_id and rekap.tanggal = tanggal_akhir_rekap.tanggal
                                GROUP BY
                                    saldo_dc.so_det_id,
                                    saldo_dc.part_detail_id
                            ) saldo_dc
                    ),
                    dc_current_saldo AS (
                        -- current saldo
                        WITH
                            dc as (
                                SELECT
                                        a.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc_main,
                                        null qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        null sec_in_in,
                                        null sec_in_rep_main,
                                        null sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out
                                from
                                        dc_in_input a
                                        left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input f on f.id = s.form_cut_id
                                        left join form_cut_reject fr on fr.id = s.form_reject_id
                                        left join form_cut_piece fp on fp.id = s.form_piece_id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on pd.part_id = p.id
                                        left join master_part mp on mp.id = pd.master_part_id
                                where
                                        a.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        pd.part_status = 'main'
                                UNION ALL
                                SELECT
                                        a.id_qr_stocker,
                                        pd.id as part_detail_id,
                                        s.so_det_id,
                                        null qty_in_dc_main,
                                        (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc,
                                        null sec_inhouse_in_main,
                                        null sec_inhouse_in,
                                        null sec_inhouse_rep_main,
                                        null sec_inhouse_rep,
                                        null sec_inhouse_out_main,
                                        null sec_inhouse_out,
                                        null sec_in_in_main,
                                        null sec_in_in,
                                        null sec_in_rep_main,
                                        null sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out
                                from
                                        dc_in_input a
                                        left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input f on f.id = s.form_cut_id
                                        left join form_cut_reject fr on fr.id = s.form_reject_id
                                        left join form_cut_piece fp on fp.id = s.form_piece_id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on pd.part_id = p.id
                                        left join part_detail pdcom on pdcom.id = pd.from_part_detail
                                        left join part pcom on pcom.id = pdcom.part_id
                                        left join master_part mp on mp.id = pd.master_part_id
                                where
                                        a.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),

                            sii_in as (
                                SELECT
                                    sii_in.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    (sii_in.qty_in) sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_in_input sii_in
                                    left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                WHERE
                                    sii_in.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    pd.part_status = 'main'
                                UNION ALL
                                SELECT
                                    sii_in.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    (sii_in.qty_in) sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_in_input sii_in
                                    left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                WHERE
                                    sii_in.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),

                            sii as (
                                SELECT
                                    sii.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    sii.qty_replace sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    sii.qty_in sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_input sii
                                    left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                WHERE
                                    sii.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    pd.part_status= 'main'
                                UNION ALL
                                SELECT
                                    sii.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    sii.qty_replace sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    sii.qty_in sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_inhouse_input sii
                                    left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                WHERE
                                    sii.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),

                            wod as (
                                SELECT
                                    wod.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    wod.qty sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    wip_out_det wod
                                    left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                WHERE
                                    wod.created_at between '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59' and
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    pd.part_status= 'main'
                                UNION ALL
                                SELECT
                                    wod.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    wod.qty sec_in_in,
                                    null sec_in_rep_main,
                                    null sec_in_rep,
                                    null sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    wip_out_det wod
                                    left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                WHERE
                                    wod.created_at between '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59' and
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL)
                            ),

                            si as (
                                SELECT
                                    si.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    si.qty_replace sec_in_rep_main,
                                    null sec_in_rep,
                                    si.qty_in sec_in_out_main,
                                    null sec_in_out
                                FROM
                                    secondary_in_input si
                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                WHERE
                                    si.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    pd.part_status= 'main' AND
                                    COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                                UNION ALL
                                SELECT
                                    si.id_qr_stocker,
                                    pd.id as part_detail_id,
                                    s.so_det_id,
                                    null qty_in_dc_main,
                                    null qty_in_dc,
                                    null sec_inhouse_in_main,
                                    null sec_inhouse_in,
                                    null sec_inhouse_rep_main,
                                    null sec_inhouse_rep,
                                    null sec_inhouse_out_main,
                                    null sec_inhouse_out,
                                    null sec_in_in_main,
                                    null sec_in_in,
                                    null sec_in_rep_main,
                                    si.qty_replace sec_in_rep,
                                    null sec_in_out_main,
                                    si.qty_in sec_in_out
                                FROM
                                    secondary_in_input si
                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                    left join part_detail pd on pd.id = s.part_detail_id
                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                WHERE
                                    si.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                    COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                            ),

                            loading_line as (
                                select
                                    panel,
                                    so_det_id,
                                    GROUP_CONCAT(stocker_id) stockers,
                                    SUM(loading_qty) loading_qty
                                from (
                                    select
                                        COALESCE(p_com.panel, p.panel) as panel,
                                        GROUP_CONCAT(ll.stocker_id) stocker_id,
                                        s.so_det_id,
                                        MIN(ll.qty) loading_qty
                                    from
                                        loading_line ll
                                        left join stocker_input s on s.id = ll.stocker_id
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p on p.id = pd.part_id
                                        left join part p_com on p_com.id = pd_com.part_id
                                    where
                                        ll.tanggal_loading between '".$dateFrom."' AND '".$dateTo."' AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                    group by
                                        COALESCE(p_com.panel, p.panel),
                                        s.form_cut_id,
                                        s.form_reject_id,
                                        s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject
                                ) as loading
                                group by
                                    panel,
                                    so_det_id
                            )

                            SELECT
                                *,
                                qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir
                            FROM (
                                    SELECT
                                        GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                        msb.buyer,
                                        msb.ws,
                                        msb.styleno as style,
                                        msb.color,
                                        msb.size,
                                        msb.id_so_det,
                                        p.panel,
                                        p.panel_status,
                                        pd.id as part_detail_id,
                                        GROUP_CONCAT(DISTINCT mp.nama_part) as nama_part,
                                        GROUP_CONCAT(DISTINCT pd.part_status) as part_status,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                -- 		loading.stockers,
                                        COALESCE(loading_line.loading_qty, 0) loading_qty
                                    FROM (
                                        SELECT
                                            *
                                        FROM
                                            dc
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            sii_in
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            sii
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            wod
                                        UNION ALL
                                        SELECT
                                            *
                                        FROM
                                            si
                                    ) saldo_dc
                                    LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                    LEFT JOIN part_detail pd on pd.id = saldo_dc.part_detail_id
                                    LEFT JOIN part p on p.id = pd.part_id
                                    LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and loading_line.panel = p.panel
                                GROUP BY
                                    saldo_dc.so_det_id,
                                    saldo_dc.part_detail_id
                            ) saldo_dc
                    )

                    select
                        GROUP_CONCAT(dc_current_saldo.stockers) as stockers,
                        dc_current_saldo.buyer,
                        dc_current_saldo.ws,
                        dc_current_saldo.style,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        GROUP_CONCAT(dc_current_saldo.id_so_det) id_so_det,
                        dc_current_saldo.panel,
                        dc_current_saldo.panel_status,
                        dc_current_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.nama_part) as nama_part,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.part_status) as part_status,
                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                        sum(dc_current_saldo.qty_in) qty_in,
                        sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                        sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                        sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                        sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                        sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                        sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                        sum(dc_current_saldo.loading_qty) loading,
                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0))+SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir
                    from
                        dc_current_saldo
                        left join dc_before_saldo on dc_before_saldo.id_so_det = dc_current_saldo.id_so_det and dc_before_saldo.part_detail_id = dc_current_saldo.part_detail_id
                    GROUP BY
                        dc_current_saldo.ws,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        dc_current_saldo.part_detail_id
            ");

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report', [
            "page" => "dashboard-dc"
        ]);
    }

    public function exportReportDc(Request $request) {
        ini_set("max_execution_time", 36000);

        $from = $request->from ? $request->from : date("Y-m-d");
        $to = $request->to ? $request->to : date("Y-m-d");

        return Excel::download(
                                new ExportReportDc(
                                    $from,
                                    $to,
                                    $request->noWsColorSizeFilter,
                                    $request->noWsColorPartFilter,
                                    $request->noWsFilter,
                                    $request->buyerFilter,
                                    $request->styleFilter,
                                    $request->colorFilter,
                                    $request->sizeFilter,
                                    $request->partFilter,
                                    $request->saldoAwalFilter,
                                    $request->masukFilter,
                                    $request->kirimSecDalamFilter,
                                    $request->terimaRepairedSecDalamFilter,
                                    $request->terimaGoodSecDalamFilter,
                                    $request->kirimSecLuarFilter,
                                    $request->terimaRepairedSecLuarFilter,
                                    $request->terimaGoodSecLuarFilter,
                                    $request->loadingFilter,
                                    $request->saldoAkhirFilter
                                ),
                                'Laporan DC '.$from.' - '.$to.'.xlsx'
                            );

    }
}
