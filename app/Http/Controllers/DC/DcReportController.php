<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\DC\ExportReportDc;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;

class DcReportController extends Controller
{
    public function index(Request $request){
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        if ($request->ajax()) {

            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

            $dataReport = DB::select("
                WITH
                dc as (
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
                                                        tanggal >= '2026-01-01' and
                                                        tanggal < '".$dateFrom."'
                                        ) tanggal_akhir_rekap on tanggal_akhir_rekap.tanggal = dc_report_rekap.tanggal
                                ),
                                dc as (
                                        SELECT
                                                a.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                a.qty_awal qty_in_dc_main,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                a.tgl_trans < '".$dateFrom."' AND
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
                                                a.qty_awal qty_in_dc,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                a.tgl_trans < '".$dateFrom."' AND
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii_in.tgl_trans < '".$dateFrom."' AND
                                                sii_in.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status = 'main'
                                        GROUP BY s.id, sii_in.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii_in.tgl_trans < '".$dateFrom."' AND
                                                sii_in.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii_in.urutan
                                ),

                                sii as (
                                        -- SECONDARY DALAM ( < May 01 2026 )
                                        SELECT
                                                sii.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                null qty_in_dc_main,
                                                null qty_in_dc,
                                                null sec_inhouse_in_main,
                                                null sec_inhouse_in,
                                                (CASE WHEN tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep_main,
                                                null sec_inhouse_rep,
                                                sii.qty_in sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii.tgl_trans < '".$dateFrom."' AND
                                                sii.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        GROUP BY s.id, sii.urutan
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
                                                (CASE WHEN tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                sii.qty_in sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii.tgl_trans < '".$dateFrom."' AND
                                                sii.tgl_trans >= '2026-05-01' AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii.urutan

                                        -- SECONDARY IN DALAM ( >= May 01 2026 )
                                        UNION ALL
                                                SELECT
                                                si.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                null qty_in_dc_main,
                                                null qty_in_dc,
                                                null sec_inhouse_in_main,
                                                null sec_inhouse_in,
                                                (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep_main,
                                                null sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                si.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                        GROUP BY s.id, si.urutan
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
                                                (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                si.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                        GROUP BY s.id, si.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                wo.tgl_form < '".$dateFrom."' AND
                                                wo.tgl_form >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                wo.tgl_form < '".$dateFrom."' AND
                                                wo.tgl_form >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                si.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
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
                                                si.qty_in sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                si.tgl_trans >= '2026-05-01' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
                                ),

                                loading_line_qty as (
                                        SELECT
                                                s.id_qr_stocker,
                                                pd.id AS part_detail_id,
                                                s.so_det_id,

                                                NULL AS qty_in_dc_main,
                                                NULL AS qty_in_dc,
                                                NULL AS sec_inhouse_in_main,
                                                NULL AS sec_inhouse_in,
                                                NULL AS sec_inhouse_rep_main,
                                                NULL AS sec_inhouse_rep,
                                                NULL AS sec_inhouse_out_main,
                                                NULL AS sec_inhouse_out,
                                                NULL AS sec_in_in_main,
                                                NULL AS sec_in_in,
                                                NULL AS sec_in_rep_main,
                                                NULL AS sec_in_rep,
                                                NULL AS sec_in_out_main,
                                                NULL AS sec_in_out,

                                                COALESCE(
                                                        MIN(ll.qty) OVER (
                                                                PARTITION BY
                                                                        COALESCE(p_com.panel, p.panel),
                                                                        s.form_cut_id,
                                                                        s.form_reject_id,
                                                                        s.form_piece_id,
                                                                        s.so_det_id,
                                                                        s.group_stocker,
                                                                        s.ratio,
                                                                        s.stocker_reject
                                                        ),
                                                        ll.qty
                                                ) AS loading_qty
                                        FROM loading_line ll
                                        JOIN stocker_input s ON s.id = ll.stocker_id
                                        LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                        LEFT JOIN part p ON p.id = pd.part_id
                                        LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                        LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                        WHERE
                                                ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                ll.tanggal_loading < '".$dateFrom."'
                                                AND COALESCE(s.cancel, 'n') != 'y'
                                                AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                        ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                        ll.tanggal_loading < '".$dateFrom."' and
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
                                MAX(tanggal) tanggal,
                                stockers,
                                act_costing_ws,
                                buyer,
                                color,
                                so_det_id,
                                panel,
                                panel_status,
                                part_detail_id,
                                nama_part,
                                part_status,
                                SUM(saldo_awal) saldo_awal,
                                SUM(qty_in) qty_in,
                                SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                                SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                                SUM(kirim_secondary_luar) kirim_secondary_luar,
                                SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                                SUM(loading_qty) loading_qty,
                                SUM(saldo_awal)+SUM(saldo_akhir) saldo_akhir,
                                CURRENT_TIMESTAMP() created_at,
                                CURRENT_TIMESTAMP() updated_at
                        FROM (
                                        SELECT
                                                '2026-03-31' tanggal,
                                                stockers,
                                                buyer,
                                                ws act_costing_ws,
                                                color,
                                                id_so_det so_det_id,
                                                panel,
                                                panel_status,
                                                part_detail_id,
                                                nama_part,
                                                part_status,
                                                0 saldo_awal,
                                                qty_in,
                                                kirim_secondary_dalam,
                                                terima_repaired_secondary_dalam,
                                                terima_good_secondary_dalam,
                                                kirim_secondary_luar,
                                                terima_repaired_secondary_luar,
                                                terima_good_secondary_luar,
                                                loading_qty,
                                                qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir,
                                                CURRENT_TIMESTAMP() created_at,
                                                CURRENT_TIMESTAMP() updated_at
                                        FROM (
                                                SELECT
                                                        GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                                        msb.buyer,
                                                        msb.ws,
                                                        msb.styleno as style,
                                                        msb.color,
                                                        msb.size,
                                                        msb.id_so_det,
                                                        COALESCE(p_com.panel, p.panel) panel,
                                                        COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                                        pd.id as part_detail_id,
                                                        COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                        COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                        -- loading.stockers,
                                                        SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                                        COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                        UNION ALL
                                                        SELECT
                                                                *
                                                        FROM
                                                                loading_line_qty
                                                ) saldo_dc
                                                LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                                left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                                left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                                left join part p on p.id = pd.part_id
                                                left join part p_com on p_com.id = pd_com.part_id
                                                LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                                LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                                LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                                GROUP BY
                                                        saldo_dc.so_det_id,
                                                        saldo_dc.part_detail_id
                                        ) saldo_dc
                                        UNION ALL
                                        select
                                                tanggal,
                                                stockers,
                                                buyer,
                                                act_costing_ws,
                                                color,
                                                so_det_id,
                                                panel,
                                                panel_status,
                                                part_detail_id,
                                                nama_part,
                                                part_status,
                                                saldo_akhir saldo_awal,
                                                0 qty_in,
                                                0 kirim_secondary_dalam,
                                                0 terima_repaired_secondary_dalam,
                                                0 terima_good_secondary_dalam,
                                                0 kirim_secondary_luar,
                                                0 terima_repaired_secondary_luar,
                                                0 terima_good_secondary_luar,
                                                0 loading_qty,
                                                0 saldo_akhir,
                                                CURRENT_TIMESTAMP() created_at,
                                                CURRENT_TIMESTAMP() updated_at
                                        from
                                                dc_report_rekap
                                        where
                                                tanggal < '".$dateFrom."'
                        ) saldo_dc
                        group by
                                so_det_id,
                                part_detail_id
                    ),
                    dc_current_saldo AS (
                        -- current saldo
                        WITH
                                dc as (
                                        SELECT
                                                a.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                a.qty_awal qty_in_dc_main,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                                a.qty_awal qty_in_dc,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status = 'main'
                                        GROUP BY s.id, sii_in.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii_in.urutan
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
                                                (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep_main,
                                                null sec_inhouse_rep,
                                                sii.qty_in sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        GROUP BY s.id, sii.urutan
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
                                                (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                sii.qty_in sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii.urutan
                                        UNION ALL
                                        SELECT
                                                si.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                null qty_in_dc_main,
                                                null qty_in_dc,
                                                null sec_inhouse_in_main,
                                                null sec_inhouse_in,
                                                (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep_main,
                                                null sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                        GROUP BY s.id, si.urutan
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
                                                (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                        GROUP BY s.id, si.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
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
                                                si.qty_in sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
                                ),

                                loading_line_qty as (
                                        SELECT
                                                s.id_qr_stocker,
                                                pd.id AS part_detail_id,
                                                s.so_det_id,

                                                NULL AS qty_in_dc_main,
                                                NULL AS qty_in_dc,
                                                NULL AS sec_inhouse_in_main,
                                                NULL AS sec_inhouse_in,
                                                NULL AS sec_inhouse_rep_main,
                                                NULL AS sec_inhouse_rep,
                                                NULL AS sec_inhouse_out_main,
                                                NULL AS sec_inhouse_out,
                                                NULL AS sec_in_in_main,
                                                NULL AS sec_in_in,
                                                NULL AS sec_in_rep_main,
                                                NULL AS sec_in_rep,
                                                NULL AS sec_in_out_main,
                                                NULL AS sec_in_out,

                                                COALESCE(
                                                        MIN(ll.qty) OVER (
                                                                                PARTITION BY
                                                                                        COALESCE(p_com.panel, p.panel),
                                                                                        s.form_cut_id,
                                                                                        s.form_reject_id,
                                                                                        s.form_piece_id,
                                                                                        s.so_det_id,
                                                                                        s.group_stocker,
                                                                                        s.ratio,
                                                                                        s.stocker_reject
                                                        ),
                                                        ll.qty
                                                ) AS loading_qty
                                        FROM loading_line ll
                                        JOIN stocker_input s ON s.id = ll.stocker_id
                                        LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                        LEFT JOIN part p ON p.id = pd.part_id
                                        LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                        LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                        WHERE
                                                ll.tanggal_loading BETWEEN '$dateFrom' AND '$dateTo'
                                                AND COALESCE(s.cancel, 'n') != 'y'
                                                AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                                ll.tanggal_loading between '".$dateFrom."' AND '$dateTo' AND
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
                                                COALESCE(p_com.panel, p.panel) panel,
                                                COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                                pd.id as part_detail_id,
                                                COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                -- loading.stockers,
                                                SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                                COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                        UNION ALL
                                                        SELECT
                                                                        *
                                                        FROM
                                                                        loading_line_qty
                                        ) saldo_dc
                                        LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                        left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p on p.id = pd.part_id
                                        left join part p_com on p_com.id = pd_com.part_id
                                        LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                        LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                        LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                        GROUP BY
                                                saldo_dc.so_det_id,
                                                saldo_dc.part_detail_id
                                ) saldo_dc
                    ),
                    dc_in_dump_before AS (
                        select
                                '' stockers,
                                dc_in_dump.buyer,
                                dc_in_dump.ws as act_costing_ws,
                                dc_in_dump.style,
                                dc_in_dump.color,
                                dc_in_dump.size,
                                '' so_det_id,
                                dc_in_dump.panel,
                                part.panel_status,
                                part_detail.id part_detail_id,
                                part nama_part,
                                part_detail.part_status,
                                qty_in current_saldo_awal,
                                0 qty_in,
                                0 kirim_secondary_dalam,
                                0 terima_repaired_secondary_dalam,
                                0 terima_good_secondary_dalam,
                                0 kirim_secondary_luar,
                                0 terima_repaired_secondary_luar,
                                0 terima_good_secondary_luar,
                                0 loading,
                                0 current_saldo_akhir,
                                0 kirim_secondary_dalam_before,
                                0 terima_repaired_secondary_dalam_before,
                                0 terima_good_secondary_dalam_before,
                                0 kirim_secondary_luar_before,
                                0 terima_repaired_secondary_luar_before,
                                0 terima_good_secondary_luar_before
                        from
                                dc_in_dump
                                left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                                left join part_detail on part_detail.part_id = part.id
                                inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                        where
                                dc_in_dump.tgl_trans < '$dateFrom'
                        group by
                                ws,
                                color,
                                size,
                                part_detail_id
                    ),
                    dc_saldo AS (
                            select
                                stockers,
                                ws,
                                buyer,
                                style,
                                UPPER(TRIM(color)) color,
                                size,
                                panel,
                                nama_part,
                                SUM(current_saldo_awal) current_saldo_awal,
                                SUM(qty_in) qty_in,
                                SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                                SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                                SUM(kirim_secondary_luar) kirim_secondary_luar,
                                SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                                SUM(loading) loading_qty,
                                SUM(current_saldo_awal)+SUM(current_saldo_akhir) as current_saldo_akhir,
                                SUM(kirim_secondary_dalam_before) kirim_secondary_dalam_before,
                                SUM(terima_repaired_secondary_dalam_before) terima_repaired_secondary_dalam_before,
                                SUM(terima_good_secondary_dalam_before) terima_good_secondary_dalam_before,
                                SUM(kirim_secondary_luar_before) kirim_secondary_luar_before,
                                SUM(terima_repaired_secondary_luar_before) terima_repaired_secondary_luar_before,
                                SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before
                            from (
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
                                        0 as current_saldo_awal,
                                        sum(dc_current_saldo.qty_in) qty_in,
                                        sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                                        sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                        sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                                        sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                                        sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                        sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                                        sum(dc_current_saldo.loading_qty) loading,
                                        SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir,
                                        0 as kirim_secondary_dalam_before,
                                        0 as terima_repaired_secondary_dalam_before,
                                        0 as terima_good_secondary_dalam_before,
                                        0 as kirim_secondary_luar_before,
                                        0 as terima_repaired_secondary_luar_before,
                                        0 as terima_good_secondary_luar_before
                                from
                                        dc_current_saldo
                                GROUP BY
                                        dc_current_saldo.ws,
                                        dc_current_saldo.color,
                                        dc_current_saldo.size,
                                        dc_current_saldo.part_detail_id
                                UNION ALL
                                select
                                        GROUP_CONCAT(dc_before_saldo.stockers) as stockers,
                                        msb.buyer,
                                        msb.ws as act_costing_ws,
                                        msb.styleno as style,
                                        msb.color,
                                        msb.size,
                                        GROUP_CONCAT(dc_before_saldo.so_det_id) so_det_id,
                                        dc_before_saldo.panel,
                                        dc_before_saldo.panel_status,
                                        dc_before_saldo.part_detail_id,
                                        GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) as nama_part,
                                        GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) as part_status,
                                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                                        0 qty_in,
                                        0 kirim_secondary_dalam,
                                        0 terima_repaired_secondary_dalam,
                                        0 terima_good_secondary_dalam,
                                        0 kirim_secondary_luar,
                                        0 terima_repaired_secondary_luar,
                                        0 terima_good_secondary_luar,
                                        0 loading,
                                        0 as current_saldo_akhir,
                                        SUM(kirim_secondary_dalam) as kirim_secondary_dalam_before,
                                        SUM(terima_repaired_secondary_dalam) as terima_repaired_secondary_dalam_before,
                                        SUM(terima_good_secondary_dalam) as terima_good_secondary_dalam_before,
                                        SUM(kirim_secondary_luar) as kirim_secondary_luar_before,
                                        SUM(terima_repaired_secondary_luar) as terima_repaired_secondary_luar_before,
                                        SUM(terima_good_secondary_luar) as terima_good_secondary_luar_before
                                from
                                        dc_before_saldo
                                        left join master_sb_ws msb on msb.id_so_det = dc_before_saldo.so_det_id
                                GROUP BY
                                        msb.ws,
                                        msb.color,
                                        msb.size,
                                        dc_before_saldo.part_detail_id
                                HAVING
                                        current_saldo_awal != 0
                                UNION ALL
                                select
                                        '' stockers,
                                        dc_in_dump.buyer,
                                        dc_in_dump.ws as act_costing_ws,
                                        dc_in_dump.style,
                                        dc_in_dump.color,
                                        dc_in_dump.size,
                                        '' so_det_id,
                                        dc_in_dump.panel,
                                        part.panel_status,
                                        part_detail.id part_detail_id,
                                        part nama_part,
                                        part_detail.part_status,
                                        0 current_saldo_awal,
                                        qty_in qty_in,
                                        0 kirim_secondary_dalam,
                                        0 terima_repaired_secondary_dalam,
                                        0 terima_good_secondary_dalam,
                                        0 kirim_secondary_luar,
                                        0 terima_repaired_secondary_luar,
                                        0 terima_good_secondary_luar,
                                        0 loading,
                                        qty_in current_saldo_akhir,
                                        0 as kirim_secondary_dalam_before,
                                        0 as terima_repaired_secondary_dalam_before,
                                        0 as terima_good_secondary_dalam_before,
                                        0 as kirim_secondary_luar_before,
                                        0 as terima_repaired_secondary_luar_before,
                                        0 as terima_good_secondary_luar_before
                                from
                                        dc_in_dump
                                        left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                                        left join part_detail on part_detail.part_id = part.id
                                        inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                                where
                                        dc_in_dump.tgl_trans between '$dateFrom' AND '$dateTo'
                                group by
                                        ws,
                                        color,
                                        size,
                                        part_detail_id
                                UNION ALL
                                select
                                        stockers,
                                        buyer,
                                        act_costing_ws,
                                        style,
                                        color,
                                        size,
                                        so_det_id,
                                        panel,
                                        panel_status,
                                        part_detail_id,
                                        nama_part,
                                        part_status,
                                        current_saldo_awal,
                                        qty_in,
                                        kirim_secondary_dalam,
                                        terima_repaired_secondary_dalam,
                                        terima_good_secondary_dalam,
                                        kirim_secondary_luar,
                                        terima_repaired_secondary_luar,
                                        terima_good_secondary_luar,
                                        loading,
                                        current_saldo_akhir,
                                        kirim_secondary_dalam_before,
                                        terima_repaired_secondary_dalam_before,
                                        terima_good_secondary_dalam_before,
                                        kirim_secondary_luar_before,
                                        terima_repaired_secondary_luar_before,
                                        terima_good_secondary_luar_before

                                from
                                        dc_in_dump_before
                            ) current_saldo
                            group by
                                ws,
                                color,
                                size,
                                panel,
                                nama_part
                    )

                    select
                        stockers,
                        ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        nama_part,
                        SUM(current_saldo_awal) current_saldo_awal,
                        SUM(qty_adjustment_before) adjustment_before,
                        SUM(switching_in_before) switching_in_before,
                        SUM(switching_out_before) switching_out_before,
                        SUM(current_saldo_awal) + SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before) current_saldo_awal_adjustment,
                        SUM(qty_in) qty_in,
                        SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                        SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                        SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                        SUM(kirim_secondary_luar) kirim_secondary_luar,
                        SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                        SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                        SUM(loading_qty) loading_qty,
                        SUM(current_saldo_akhir) current_saldo_akhir,
                        SUM(kirim_secondary_dalam_before) kirim_secondary_dalam_before,
                        SUM(terima_repaired_secondary_dalam_before) terima_repaired_secondary_dalam_before,
                        SUM(terima_good_secondary_dalam_before) terima_good_secondary_dalam_before,
                        SUM(kirim_secondary_luar_before) kirim_secondary_luar_before,
                        SUM(terima_repaired_secondary_luar_before) terima_repaired_secondary_luar_before,
                        SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before,
                        SUM(qty_adjustment) adjustment,
                        SUM(switching_in) switching_in,
                        SUM(switching_out) switching_out,
                        (SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before)) + SUM(current_saldo_akhir) + (SUM(qty_adjustment) + SUM(switching_in) - SUM(switching_out)) current_saldo_akhir_adjustment,
                        SUM(qty_adjustment_secondary_dalam_before) qty_adjustment_secondary_dalam_before,
                        SUM(qty_adjustment_secondary_dalam) qty_adjustment_secondary_dalam,
                        SUM(qty_adjustment_secondary_luar_before) qty_adjustment_secondary_luar_before,
                        SUM(qty_adjustment_secondary_luar) qty_adjustment_secondary_luar
                    from (
                        select
                            stockers,
                            ws,
                            buyer,
                            style,
                            color,
                            size,
                            panel,
                            nama_part,
                            current_saldo_awal,
                            qty_in,
                            kirim_secondary_dalam,
                            terima_repaired_secondary_dalam,
                            terima_good_secondary_dalam,
                            kirim_secondary_luar,
                            terima_repaired_secondary_luar,
                            terima_good_secondary_luar,
                            loading_qty,
                            current_saldo_akhir,
                            kirim_secondary_dalam_before,
                            terima_repaired_secondary_dalam_before,
                            terima_good_secondary_dalam_before,
                            kirim_secondary_luar_before,
                            terima_repaired_secondary_luar_before,
                            terima_good_secondary_luar_before,
                            0 as qty_adjustment_before,
                            0 qty_adjustment,
                            0 as switching_in_before,
                            0 switching_in,
                            0 as switching_out_before,
                            0 switching_out,
                            0 as qty_adjustment_secondary_dalam_before,
                            0 as qty_adjustment_secondary_dalam,
                            0 as qty_adjustment_secondary_luar_before,
                            0 as qty_adjustment_secondary_luar
                        FROM
                            dc_saldo
                        UNION ALL
                        select
                            null stockers,
                            no_ws ws,
                            buyer,
                            style,
                            color,
                            size,
                            panel,
                            part nama_part,
                            0 current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_before,
                            SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment,
                            0 switching_in_before,
                            0 as switching_in,
                            0 as switching_out_before,
                            0 as switching_out,
                            0 as qty_adjustment_secondary_dalam_before,
                            0 as qty_adjustment_secondary_dalam,
                            0 as qty_adjustment_secondary_luar_before,
                            0 as qty_adjustment_secondary_luar
                        FROM
                            wip_adjustment
                        WHERE
                            tgl_saldo <= '$dateTo' and
                            type_report = 'DC'
                        GROUP BY
                            ws, color, size, panel, part
                        UNION ALL
                        select
                            null stockers,
                            from_no_ws ws,
                            from_buyer,
                            from_style,
                            from_color,
                            from_size,
                            from_panel,
                            from_part nama_part,
                            0 current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            0 as qty_adjustment_before,
                            0 as qty_adjustment,
                            0 as switching_in_before,
                            0 as switching_in,
                            SUM(IF(from_tgl_saldo < '".$dateFrom."',qty,0)) switching_out_before,
                            SUM(IF(from_tgl_saldo >= '".$dateFrom."',qty,0)) as switching_out,
                            0 as qty_adjustment_secondary_dalam_before,
                            0 as qty_adjustment_secondary_dalam,
                            0 as qty_adjustment_secondary_luar_before,
                            0 as qty_adjustment_secondary_luar
                        FROM
                            wip_switching_adj
                        where
                            from_tgl_saldo <= '$dateTo' and
                            type_report = 'DC'
                        GROUP BY
                            from_no_ws, from_color, from_size, from_panel, from_part
                        UNION ALL
                        select
                            null stockers,
                            no_ws ws,
                            buyer,
                            style,
                            color,
                            size,
                            panel,
                            part nama_part,
                            0 current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            0 as qty_adjustment_before,
                            0 as qty_adjustment,
                            SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) switching_in_before,
                            SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as switching_in,
                            0 as switching_out_before,
                            0 as switching_out,
                            0 as qty_adjustment_secondary_dalam_before,
                            0 as qty_adjustment_secondary_dalam,
                            0 as qty_adjustment_secondary_luar_before,
                            0 as qty_adjustment_secondary_luar
                        FROM
                            wip_switching_adj
                        WHERE
                            tgl_saldo <= '$dateTo' and
                            type_report = 'DC'
                        GROUP BY
                            no_ws, color, size, panel, part
                        UNION ALL
                        select
                            null stockers,
                            no_ws ws,
                            buyer,
                            style,
                            color,
                            size,
                            panel,
                            part nama_part,
                            0 current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 switching_in_before,
                            0 as switching_in,
                            0 as switching_out_before,
                            0 as switching_out,
                            SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_secondary_dalam_before,
                            SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_secondary_dalam,
                            0 as qty_adjustment_secondary_luar_before,
                            0 as qty_adjustment_secondary_luar
                        FROM
                            wip_adjustment
                        WHERE
                            tgl_saldo <= '$dateTo' and
                            type_report = 'DC_SECONDARY_DALAM'
                        GROUP BY
                            ws, color, size, panel, part
                        UNION ALL
                        select
                            null stockers,
                            no_ws ws,
                            buyer,
                            style,
                            color,
                            size,
                            panel,
                            part nama_part,
                            0 current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 switching_in_before,
                            0 as switching_in,
                            0 as switching_out_before,
                            0 as switching_out,
                            0 as qty_adjustment_secondary_dalam_before,
                            0 as qty_adjustment_secondary_dalam,
                            SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_secondary_luar_before,
                            SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_secondary_luar
                        FROM
                            wip_adjustment
                        WHERE
                            tgl_saldo <= '$dateTo' and
                            type_report = 'DC_SECONDARY_LUAR'
                        GROUP BY
                            ws, color, size, panel, part
                        ) dc
                        group by
                                ws, color, size, panel, COALESCE(nama_part, '')
                    having
                        (
                            current_saldo_awal_adjustment != 0 OR
                            qty_in != 0 OR
                            kirim_secondary_dalam != 0 OR
                            terima_repaired_secondary_dalam != 0 OR
                            terima_good_secondary_dalam != 0 OR
                            kirim_secondary_luar != 0 OR
                            terima_repaired_secondary_luar != 0 OR
                            terima_good_secondary_luar != 0 OR
                            loading_qty != 0 OR
                            current_saldo_akhir_adjustment != 0 OR
                            adjustment != 0 OR
                            switching_in != 0 OR
                            switching_out != 0
                        )
                ),

                form_list as (
                        select
                                dc.stockers,
                                dc.ws,
                                dc.buyer,
                                dc.style,
                                dc.color,
                                dc.size,
                                part.panel,
                                mp.nama_part,
                                0 current_saldo_awal,
                                0 adjustment_before,
                                0 switching_in_before,
                                0 switching_out_before,
                                0 current_saldo_awal_adjustment,
                                0 qty_in,
                                0 kirim_secondary_dalam,
                                0 terima_repaired_secondary_dalam,
                                0 terima_good_secondary_dalam,
                                0 kirim_secondary_luar,
                                0 terima_repaired_secondary_luar,
                                0 terima_good_secondary_luar,
                                0 loading_qty,
                                0 current_saldo_akhir,
                                0 kirim_secondary_dalam_before,
                                0 terima_repaired_secondary_dalam_before,
                                0 terima_good_secondary_dalam_before,
                                0 kirim_secondary_luar_before,
                                0 terima_repaired_secondary_luar_before,
                                0 terima_good_secondary_luar_before,
                                0 adjustment,
                                0 switching_in,
                                0 switching_out,
                                0 current_saldo_akhir_adjustment,
                                0 as qty_adjustment_secondary_dalam_before,
                                0 as qty_adjustment_secondary_dalam,
                                0 as qty_adjustment_secondary_luar_before,
                                0 as qty_adjustment_secondary_luar
                        from
                                dc
                                left join part on part.act_costing_ws = dc.ws and part.panel = dc.panel
                                left join part_detail on part_detail.part_id = part.id
                                left join master_part mp on mp.id = part_detail.master_part_id
                        where
                                part.panel_status != 'COMPLEMENT' AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                        group by
                                dc.ws, dc.color, dc.size, part.panel, COALESCE(mp.nama_part, '')
                )

                SELECT
                    stockers,
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    panel,
                    nama_part,
                    SUM(current_saldo_awal) current_saldo_awal,
                    SUM(adjustment_before) adjustment_before,
                    SUM(switching_in_before) switching_in_before,
                    SUM(switching_out_before) switching_out_before,
                    SUM(current_saldo_awal_adjustment) current_saldo_awal_adjustment,
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                    SUM(loading_qty) loading_qty,
                    SUM(current_saldo_akhir) current_saldo_akhir,
                    SUM(adjustment) adjustment,
                    SUM(switching_in) switching_in,
                    SUM(switching_out) switching_out,
                    SUM(current_saldo_akhir_adjustment) current_saldo_akhir_adjustment,
                    SUM(qty_adjustment_secondary_dalam) qty_adjustment_secondary_dalam,
                    SUM(qty_adjustment_secondary_luar) qty_adjustment_secondary_luar,
                    (
                    CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_dalam_before)
                                -
                                SUM(terima_repaired_secondary_dalam_before)
                                -
                                SUM(terima_good_secondary_dalam_before)
                                +
                                SUM(qty_adjustment_secondary_dalam_before)
                        )
                    END
                    ) saldo_awal_secondary_dalam,
                    (
                    (
                        CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                                (
                                SUM(kirim_secondary_dalam_before)
                                -
                                SUM(terima_repaired_secondary_dalam_before)
                                -
                                SUM(terima_good_secondary_dalam_before)
                                +
                                SUM(qty_adjustment_secondary_dalam_before)
                                )
                        END
                    )
                    +
                    SUM(kirim_secondary_dalam)
                    -
                    SUM(terima_repaired_secondary_dalam)
                    -
                    SUM(terima_good_secondary_dalam)
                    +
                    SUM(qty_adjustment_secondary_dalam)
                    ) saldo_akhir_secondary_dalam,
                    (
                    CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_luar_before)
                                -
                                SUM(terima_repaired_secondary_luar_before)
                                -
                                SUM(terima_good_secondary_luar_before)
                                +
                                SUM(qty_adjustment_secondary_luar_before)
                        )
                    END
                    ) saldo_awal_secondary_luar,
                    (
                    (
                        CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                                (
                                SUM(kirim_secondary_luar_before)
                                -
                                SUM(terima_repaired_secondary_luar_before)
                                -
                                SUM(terima_good_secondary_luar_before)
                                +
                                SUM(qty_adjustment_secondary_luar_before)
                                )
                        END
                    )
                    +
                    SUM(kirim_secondary_luar)
                    -
                    SUM(terima_repaired_secondary_luar)
                    -
                    SUM(terima_good_secondary_luar)
                    +
                    SUM(qty_adjustment_secondary_luar)
                    ) saldo_akhir_secondary_luar
                FROM (
                    select * from dc
                    UNION
                    select * from form_list
                ) dc
                group by
                    ws, color, size, panel, COALESCE(nama_part, '')
                order by
                    ws, color, size, panel, COALESCE(nama_part, '')
            ");

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report', [
            "page" => "dashboard-dc",
            "subPageGroup" => "report",
            "subPage" => "dc-report",
        ]);
    }

    public function exportReportDc(Request $request) {
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        $from = $request->from ? $request->from : date("Y-m-d");
        $to = $request->to ? $request->to : date("Y-m-d");

        $dateFrom = $from;
        $dateTo = $to;

        $dataReport = DB::select("
            WITH
            dc as (
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
                                                    tanggal >= '2026-01-01' and
                                                    tanggal < '".$dateFrom."'
                                    ) tanggal_akhir_rekap on tanggal_akhir_rekap.tanggal = dc_report_rekap.tanggal
                            ),
                            dc as (
                                    SELECT
                                            a.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            a.qty_awal qty_in_dc_main,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
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
                                            a.qty_awal qty_in_dc,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            sii_in.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status = 'main'
                                    GROUP BY s.id, sii_in.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            sii_in.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii_in.urutan
                            ),

                            sii as (
                                    -- SECONDARY DALAM ( < May 01 2026 )
                                    SELECT
                                            sii.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            (CASE WHEN tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep_main,
                                            null sec_inhouse_rep,
                                            sii.qty_in sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii.tgl_trans < '".$dateFrom."' AND
                                            sii.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    GROUP BY s.id, sii.urutan
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
                                            (CASE WHEN tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            sii.qty_in sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii.tgl_trans < '".$dateFrom."' AND
                                            sii.tgl_trans >= '2026-05-01' AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii.urutan

                                    -- SECONDARY IN DALAM ( >= May 01 2026 )
                                    UNION ALL
                                            SELECT
                                            si.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep_main,
                                            null sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            si.tgl_trans < '".$dateFrom."' AND
                                            si.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
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
                                            (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            si.tgl_trans < '".$dateFrom."' AND
                                            si.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            wo.tgl_form < '".$dateFrom."' AND
                                            wo.tgl_form >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            wo.tgl_form < '".$dateFrom."' AND
                                            wo.tgl_form >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            si.tgl_trans < '".$dateFrom."' AND
                                            si.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
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
                                            si.qty_in sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            si.tgl_trans < '".$dateFrom."' AND
                                            si.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
                            ),

                            loading_line_qty as (
                                    SELECT
                                            s.id_qr_stocker,
                                            pd.id AS part_detail_id,
                                            s.so_det_id,

                                            NULL AS qty_in_dc_main,
                                            NULL AS qty_in_dc,
                                            NULL AS sec_inhouse_in_main,
                                            NULL AS sec_inhouse_in,
                                            NULL AS sec_inhouse_rep_main,
                                            NULL AS sec_inhouse_rep,
                                            NULL AS sec_inhouse_out_main,
                                            NULL AS sec_inhouse_out,
                                            NULL AS sec_in_in_main,
                                            NULL AS sec_in_in,
                                            NULL AS sec_in_rep_main,
                                            NULL AS sec_in_rep,
                                            NULL AS sec_in_out_main,
                                            NULL AS sec_in_out,

                                            COALESCE(
                                                    MIN(ll.qty) OVER (
                                                            PARTITION BY
                                                                    COALESCE(p_com.panel, p.panel),
                                                                    s.form_cut_id,
                                                                    s.form_reject_id,
                                                                    s.form_piece_id,
                                                                    s.so_det_id,
                                                                    s.group_stocker,
                                                                    s.ratio,
                                                                    s.stocker_reject
                                                    ),
                                                    ll.qty
                                            ) AS loading_qty
                                    FROM loading_line ll
                                    JOIN stocker_input s ON s.id = ll.stocker_id
                                    LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    WHERE
                                            ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            ll.tanggal_loading < '".$dateFrom."'
                                            AND COALESCE(s.cancel, 'n') != 'y'
                                            AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                    ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    ll.tanggal_loading < '".$dateFrom."' and
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
                            MAX(tanggal) tanggal,
                            stockers,
                            act_costing_ws,
                            buyer,
                            color,
                            so_det_id,
                            panel,
                            panel_status,
                            part_detail_id,
                            nama_part,
                            part_status,
                            SUM(saldo_awal) saldo_awal,
                            SUM(qty_in) qty_in,
                            SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                            SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                            SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                            SUM(kirim_secondary_luar) kirim_secondary_luar,
                            SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                            SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                            SUM(loading_qty) loading_qty,
                            SUM(saldo_awal)+SUM(saldo_akhir) saldo_akhir,
                            CURRENT_TIMESTAMP() created_at,
                            CURRENT_TIMESTAMP() updated_at
                    FROM (
                                    SELECT
                                            '2026-03-31' tanggal,
                                            stockers,
                                            buyer,
                                            ws act_costing_ws,
                                            color,
                                            id_so_det so_det_id,
                                            panel,
                                            panel_status,
                                            part_detail_id,
                                            nama_part,
                                            part_status,
                                            0 saldo_awal,
                                            qty_in,
                                            kirim_secondary_dalam,
                                            terima_repaired_secondary_dalam,
                                            terima_good_secondary_dalam,
                                            kirim_secondary_luar,
                                            terima_repaired_secondary_luar,
                                            terima_good_secondary_luar,
                                            loading_qty,
                                            qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir,
                                            CURRENT_TIMESTAMP() created_at,
                                            CURRENT_TIMESTAMP() updated_at
                                    FROM (
                                            SELECT
                                                    GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                                    msb.buyer,
                                                    msb.ws,
                                                    msb.styleno as style,
                                                    msb.color,
                                                    msb.size,
                                                    msb.id_so_det,
                                                    COALESCE(p_com.panel, p.panel) panel,
                                                    COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                                    pd.id as part_detail_id,
                                                    COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                    COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                    -- loading.stockers,
                                                    SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                                    COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                    UNION ALL
                                                    SELECT
                                                            *
                                                    FROM
                                                            loading_line_qty
                                            ) saldo_dc
                                            LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                            left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                            left join part p on p.id = pd.part_id
                                            left join part p_com on p_com.id = pd_com.part_id
                                            LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                            LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                            LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                            GROUP BY
                                                    saldo_dc.so_det_id,
                                                    saldo_dc.part_detail_id
                                    ) saldo_dc
                                    UNION ALL
                                    select
                                            tanggal,
                                            stockers,
                                            buyer,
                                            act_costing_ws,
                                            color,
                                            so_det_id,
                                            panel,
                                            panel_status,
                                            part_detail_id,
                                            nama_part,
                                            part_status,
                                            saldo_akhir saldo_awal,
                                            0 qty_in,
                                            0 kirim_secondary_dalam,
                                            0 terima_repaired_secondary_dalam,
                                            0 terima_good_secondary_dalam,
                                            0 kirim_secondary_luar,
                                            0 terima_repaired_secondary_luar,
                                            0 terima_good_secondary_luar,
                                            0 loading_qty,
                                            0 saldo_akhir,
                                            CURRENT_TIMESTAMP() created_at,
                                            CURRENT_TIMESTAMP() updated_at
                                    from
                                            dc_report_rekap
                                    where
                                            tanggal < '".$dateFrom."'
                    ) saldo_dc
                    group by
                            so_det_id,
                            part_detail_id
                ),
                dc_current_saldo AS (
                    -- current saldo
                    WITH
                            dc as (
                                    SELECT
                                            a.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            a.qty_awal qty_in_dc_main,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                            a.qty_awal qty_in_dc,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status = 'main'
                                    GROUP BY s.id, sii_in.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii_in.urutan
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
                                            (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep_main,
                                            null sec_inhouse_rep,
                                            sii.qty_in sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    GROUP BY s.id, sii.urutan
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
                                            (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE null END) sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            sii.qty_in sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii.urutan
                                    UNION ALL
                                    SELECT
                                            si.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep_main,
                                            null sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
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
                                            (CASE WHEN si.tgl_trans >= '2026-05-01' THEN si.qty_replace ELSE null END) sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
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
                                            si.qty_in sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
                            ),

                            loading_line_qty as (
                                    SELECT
                                            s.id_qr_stocker,
                                            pd.id AS part_detail_id,
                                            s.so_det_id,

                                            NULL AS qty_in_dc_main,
                                            NULL AS qty_in_dc,
                                            NULL AS sec_inhouse_in_main,
                                            NULL AS sec_inhouse_in,
                                            NULL AS sec_inhouse_rep_main,
                                            NULL AS sec_inhouse_rep,
                                            NULL AS sec_inhouse_out_main,
                                            NULL AS sec_inhouse_out,
                                            NULL AS sec_in_in_main,
                                            NULL AS sec_in_in,
                                            NULL AS sec_in_rep_main,
                                            NULL AS sec_in_rep,
                                            NULL AS sec_in_out_main,
                                            NULL AS sec_in_out,

                                            COALESCE(
                                                    MIN(ll.qty) OVER (
                                                                            PARTITION BY
                                                                                    COALESCE(p_com.panel, p.panel),
                                                                                    s.form_cut_id,
                                                                                    s.form_reject_id,
                                                                                    s.form_piece_id,
                                                                                    s.so_det_id,
                                                                                    s.group_stocker,
                                                                                    s.ratio,
                                                                                    s.stocker_reject
                                                    ),
                                                    ll.qty
                                            ) AS loading_qty
                                    FROM loading_line ll
                                    JOIN stocker_input s ON s.id = ll.stocker_id
                                    LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    WHERE
                                            ll.tanggal_loading BETWEEN '$dateFrom' AND '$dateTo'
                                            AND COALESCE(s.cancel, 'n') != 'y'
                                            AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                            ll.tanggal_loading between '".$dateFrom."' AND '$dateTo' AND
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
                                            COALESCE(p_com.panel, p.panel) panel,
                                            COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                            pd.id as part_detail_id,
                                            COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                            COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                            -- loading.stockers,
                                            SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                            COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                    UNION ALL
                                                    SELECT
                                                                    *
                                                    FROM
                                                                    loading_line_qty
                                    ) saldo_dc
                                    LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                    left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                    left join part p on p.id = pd.part_id
                                    left join part p_com on p_com.id = pd_com.part_id
                                    LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                    LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                    GROUP BY
                                            saldo_dc.so_det_id,
                                            saldo_dc.part_detail_id
                            ) saldo_dc
                ),
                dc_in_dump_before AS (
                    select
                            '' stockers,
                            dc_in_dump.buyer,
                            dc_in_dump.ws as act_costing_ws,
                            dc_in_dump.style,
                            dc_in_dump.color,
                            dc_in_dump.size,
                            '' so_det_id,
                            dc_in_dump.panel,
                            part.panel_status,
                            part_detail.id part_detail_id,
                            part nama_part,
                            part_detail.part_status,
                            qty_in current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before
                    from
                            dc_in_dump
                            left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                            left join part_detail on part_detail.part_id = part.id
                            inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                    where
                            dc_in_dump.tgl_trans < '$dateFrom'
                    group by
                            ws,
                            color,
                            size,
                            part_detail_id
                ),
                dc_saldo AS (
                        select
                            stockers,
                            ws,
                            buyer,
                            style,
                            UPPER(TRIM(color)) color,
                            size,
                            panel,
                            nama_part,
                            SUM(current_saldo_awal) current_saldo_awal,
                            SUM(qty_in) qty_in,
                            SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                            SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                            SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                            SUM(kirim_secondary_luar) kirim_secondary_luar,
                            SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                            SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                            SUM(loading) loading_qty,
                            SUM(current_saldo_awal)+SUM(current_saldo_akhir) as current_saldo_akhir,
                            SUM(kirim_secondary_dalam_before) kirim_secondary_dalam_before,
                            SUM(terima_repaired_secondary_dalam_before) terima_repaired_secondary_dalam_before,
                            SUM(terima_good_secondary_dalam_before) terima_good_secondary_dalam_before,
                            SUM(kirim_secondary_luar_before) kirim_secondary_luar_before,
                            SUM(terima_repaired_secondary_luar_before) terima_repaired_secondary_luar_before,
                            SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before
                        from (
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
                                    0 as current_saldo_awal,
                                    sum(dc_current_saldo.qty_in) qty_in,
                                    sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                                    sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                    sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                                    sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                                    sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                    sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                                    sum(dc_current_saldo.loading_qty) loading,
                                    SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir,
                                    0 as kirim_secondary_dalam_before,
                                    0 as terima_repaired_secondary_dalam_before,
                                    0 as terima_good_secondary_dalam_before,
                                    0 as kirim_secondary_luar_before,
                                    0 as terima_repaired_secondary_luar_before,
                                    0 as terima_good_secondary_luar_before
                            from
                                    dc_current_saldo
                            GROUP BY
                                    dc_current_saldo.ws,
                                    dc_current_saldo.color,
                                    dc_current_saldo.size,
                                    dc_current_saldo.part_detail_id
                            UNION ALL
                            select
                                    GROUP_CONCAT(dc_before_saldo.stockers) as stockers,
                                    msb.buyer,
                                    msb.ws as act_costing_ws,
                                    msb.styleno as style,
                                    msb.color,
                                    msb.size,
                                    GROUP_CONCAT(dc_before_saldo.so_det_id) so_det_id,
                                    dc_before_saldo.panel,
                                    dc_before_saldo.panel_status,
                                    dc_before_saldo.part_detail_id,
                                    GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) as nama_part,
                                    GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) as part_status,
                                    SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                                    0 qty_in,
                                    0 kirim_secondary_dalam,
                                    0 terima_repaired_secondary_dalam,
                                    0 terima_good_secondary_dalam,
                                    0 kirim_secondary_luar,
                                    0 terima_repaired_secondary_luar,
                                    0 terima_good_secondary_luar,
                                    0 loading,
                                    0 as current_saldo_akhir,
                                    SUM(kirim_secondary_dalam) as kirim_secondary_dalam_before,
                                    SUM(terima_repaired_secondary_dalam) as terima_repaired_secondary_dalam_before,
                                    SUM(terima_good_secondary_dalam) as terima_good_secondary_dalam_before,
                                    SUM(kirim_secondary_luar) as kirim_secondary_luar_before,
                                    SUM(terima_repaired_secondary_luar) as terima_repaired_secondary_luar_before,
                                    SUM(terima_good_secondary_luar) as terima_good_secondary_luar_before
                            from
                                    dc_before_saldo
                                    left join master_sb_ws msb on msb.id_so_det = dc_before_saldo.so_det_id
                            GROUP BY
                                    msb.ws,
                                    msb.color,
                                    msb.size,
                                    dc_before_saldo.part_detail_id
                            HAVING
                                    current_saldo_awal != 0
                            UNION ALL
                            select
                                    '' stockers,
                                    dc_in_dump.buyer,
                                    dc_in_dump.ws as act_costing_ws,
                                    dc_in_dump.style,
                                    dc_in_dump.color,
                                    dc_in_dump.size,
                                    '' so_det_id,
                                    dc_in_dump.panel,
                                    part.panel_status,
                                    part_detail.id part_detail_id,
                                    part nama_part,
                                    part_detail.part_status,
                                    0 current_saldo_awal,
                                    qty_in qty_in,
                                    0 kirim_secondary_dalam,
                                    0 terima_repaired_secondary_dalam,
                                    0 terima_good_secondary_dalam,
                                    0 kirim_secondary_luar,
                                    0 terima_repaired_secondary_luar,
                                    0 terima_good_secondary_luar,
                                    0 loading,
                                    qty_in current_saldo_akhir,
                                    0 as kirim_secondary_dalam_before,
                                    0 as terima_repaired_secondary_dalam_before,
                                    0 as terima_good_secondary_dalam_before,
                                    0 as kirim_secondary_luar_before,
                                    0 as terima_repaired_secondary_luar_before,
                                    0 as terima_good_secondary_luar_before
                            from
                                    dc_in_dump
                                    left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                                    left join part_detail on part_detail.part_id = part.id
                                    inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                            where
                                    dc_in_dump.tgl_trans between '$dateFrom' AND '$dateTo'
                            group by
                                    ws,
                                    color,
                                    size,
                                    part_detail_id
                            UNION ALL
                            select
                                    stockers,
                                    buyer,
                                    act_costing_ws,
                                    style,
                                    color,
                                    size,
                                    so_det_id,
                                    panel,
                                    panel_status,
                                    part_detail_id,
                                    nama_part,
                                    part_status,
                                    current_saldo_awal,
                                    qty_in,
                                    kirim_secondary_dalam,
                                    terima_repaired_secondary_dalam,
                                    terima_good_secondary_dalam,
                                    kirim_secondary_luar,
                                    terima_repaired_secondary_luar,
                                    terima_good_secondary_luar,
                                    loading,
                                    current_saldo_akhir,
                                    kirim_secondary_dalam_before,
                                    terima_repaired_secondary_dalam_before,
                                    terima_good_secondary_dalam_before,
                                    kirim_secondary_luar_before,
                                    terima_repaired_secondary_luar_before,
                                    terima_good_secondary_luar_before
                            from
                                    dc_in_dump_before
                        ) current_saldo
                        group by
                            ws,
                            color,
                            size,
                            panel,
                            nama_part
                )

                select
                    stockers,
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    panel,
                    nama_part,
                    SUM(current_saldo_awal) current_saldo_awal,
                    SUM(qty_adjustment_before) adjustment_before,
                    SUM(switching_in_before) switching_in_before,
                    SUM(switching_out_before) switching_out_before,
                    SUM(current_saldo_awal) + SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before) current_saldo_awal_adjustment,
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                    SUM(loading_qty) loading_qty,
                    SUM(current_saldo_akhir) current_saldo_akhir,
                    SUM(kirim_secondary_dalam_before) kirim_secondary_dalam_before,
                    SUM(terima_repaired_secondary_dalam_before) terima_repaired_secondary_dalam_before,
                    SUM(terima_good_secondary_dalam_before) terima_good_secondary_dalam_before,
                    SUM(kirim_secondary_luar_before) kirim_secondary_luar_before,
                    SUM(terima_repaired_secondary_luar_before) terima_repaired_secondary_luar_before,
                    SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before,
                    SUM(qty_adjustment) adjustment,
                    SUM(switching_in) switching_in,
                    SUM(switching_out) switching_out,
                    (SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before)) + SUM(current_saldo_akhir) + (SUM(qty_adjustment) + SUM(switching_in) - SUM(switching_out)) current_saldo_akhir_adjustment,
                    SUM(qty_adjustment_secondary_dalam_before) qty_adjustment_secondary_dalam_before,
                    SUM(qty_adjustment_secondary_dalam) qty_adjustment_secondary_dalam,
                    SUM(qty_adjustment_secondary_luar_before) qty_adjustment_secondary_luar_before,
                    SUM(qty_adjustment_secondary_luar) qty_adjustment_secondary_luar
                from (
                    select
                        stockers,
                        ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        nama_part,
                        current_saldo_awal,
                        qty_in,
                        kirim_secondary_dalam,
                        terima_repaired_secondary_dalam,
                        terima_good_secondary_dalam,
                        kirim_secondary_luar,
                        terima_repaired_secondary_luar,
                        terima_good_secondary_luar,
                        loading_qty,
                        current_saldo_akhir,
                        kirim_secondary_dalam_before,
                        terima_repaired_secondary_dalam_before,
                        terima_good_secondary_dalam_before,
                        kirim_secondary_luar_before,
                        terima_repaired_secondary_luar_before,
                        terima_good_secondary_luar_before,
                        0 as qty_adjustment_before,
                        0 qty_adjustment,
                        0 as switching_in_before,
                        0 switching_in,
                        0 as switching_out_before,
                        0 switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar
                    FROM
                        dc_saldo
                    UNION ALL
                    select
                        null stockers,
                        no_ws ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 kirim_secondary_dalam_before,
                        0 terima_repaired_secondary_dalam_before,
                        0 terima_good_secondary_dalam_before,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar
                    FROM
                        wip_adjustment
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC'
                    GROUP BY
                        ws, color, size, panel, part
                    UNION ALL
                    select
                        null stockers,
                        from_no_ws ws,
                        from_buyer,
                        from_style,
                        from_color,
                        from_size,
                        from_panel,
                        from_part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 kirim_secondary_dalam_before,
                        0 terima_repaired_secondary_dalam_before,
                        0 terima_good_secondary_dalam_before,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 as qty_adjustment_before,
                        0 as qty_adjustment,
                        0 as switching_in_before,
                        0 as switching_in,
                        SUM(IF(from_tgl_saldo < '".$dateFrom."',qty,0)) switching_out_before,
                        SUM(IF(from_tgl_saldo >= '".$dateFrom."',qty,0)) as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar
                    FROM
                        wip_switching_adj
                    where
                        from_tgl_saldo <= '$dateTo' and
                        type_report = 'DC'
                    GROUP BY
                        from_no_ws, from_color, from_size, from_panel, from_part
                    UNION ALL
                    select
                        null stockers,
                        no_ws ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 kirim_secondary_dalam_before,
                        0 terima_repaired_secondary_dalam_before,
                        0 terima_good_secondary_dalam_before,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 as qty_adjustment_before,
                        0 as qty_adjustment,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) switching_in_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar
                    FROM
                        wip_switching_adj
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC'
                    GROUP BY
                        no_ws, color, size, panel, part
                    UNION ALL
                    select
                        null stockers,
                        no_ws ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 kirim_secondary_dalam_before,
                        0 terima_repaired_secondary_dalam_before,
                        0 terima_good_secondary_dalam_before,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_secondary_dalam_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar
                    FROM
                        wip_adjustment
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC_SECONDARY_DALAM'
                    GROUP BY
                        ws, color, size, panel, part
                    UNION ALL
                    select
                        null stockers,
                        no_ws ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 kirim_secondary_dalam_before,
                        0 terima_repaired_secondary_dalam_before,
                        0 terima_good_secondary_dalam_before,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_secondary_luar_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_secondary_luar
                    FROM
                        wip_adjustment
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC_SECONDARY_LUAR'
                    GROUP BY
                        ws, color, size, panel, part
                ) dc
                group by
                    ws, color, size, panel, COALESCE(nama_part, '')
                having
                    (
                        current_saldo_awal_adjustment != 0 OR
                        qty_in != 0 OR
                        kirim_secondary_dalam != 0 OR
                        terima_repaired_secondary_dalam != 0 OR
                        terima_good_secondary_dalam != 0 OR
                        kirim_secondary_luar != 0 OR
                        terima_repaired_secondary_luar != 0 OR
                        terima_good_secondary_luar != 0 OR
                        loading_qty != 0 OR
                        current_saldo_akhir_adjustment != 0 OR
                        adjustment != 0 OR
                        switching_in != 0 OR
                        switching_out != 0
                    )
            ),

            form_list as (
                    select
                            dc.stockers,
                            dc.ws,
                            dc.buyer,
                            dc.style,
                            dc.color,
                            dc.size,
                            part.panel,
                            mp.nama_part,
                            0 current_saldo_awal,
                            0 adjustment_before,
                            0 switching_in_before,
                            0 switching_out_before,
                            0 current_saldo_awal_adjustment,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 current_saldo_akhir,
                            0 kirim_secondary_dalam_before,
                            0 terima_repaired_secondary_dalam_before,
                            0 terima_good_secondary_dalam_before,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            0 adjustment,
                            0 switching_in,
                            0 switching_out,
                            0 current_saldo_akhir_adjustment,
                            0 qty_adjustment_secondary_dalam_before,
                            0 qty_adjustment_secondary_dalam,
                            0 qty_adjustment_secondary_luar_before,
                            0 qty_adjustment_secondary_luar
                    from
                            dc
                            left join part on part.act_costing_ws = dc.ws and part.panel = dc.panel
                            left join part_detail on part_detail.part_id = part.id
                            left join master_part mp on mp.id = part_detail.master_part_id
                    where
                            part.panel_status != 'COMPLEMENT' AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                    group by
                            dc.ws, dc.color, dc.size, part.panel, COALESCE(mp.nama_part, '')
            )

            SELECT
                stockers,
                ws,
                buyer,
                style,
                color,
                size,
                panel,
                nama_part,
                SUM(current_saldo_awal) current_saldo_awal,
                SUM(adjustment_before) adjustment_before,
                SUM(switching_in_before) switching_in_before,
                SUM(switching_out_before) switching_out_before,
                SUM(current_saldo_awal_adjustment) current_saldo_awal_adjustment,
                SUM(qty_in) qty_in,
                SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                SUM(kirim_secondary_luar) kirim_secondary_luar,
                SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                SUM(loading_qty) loading_qty,
                SUM(current_saldo_akhir) current_saldo_akhir,
                SUM(adjustment) adjustment,
                SUM(switching_in) switching_in,
                SUM(switching_out) switching_out,
                SUM(current_saldo_akhir_adjustment) current_saldo_akhir_adjustment,
                SUM(qty_adjustment_secondary_dalam) qty_adjustment_secondary_dalam,
                SUM(qty_adjustment_secondary_luar) qty_adjustment_secondary_luar,
                (
                CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_dalam_before)
                                -
                                SUM(terima_repaired_secondary_dalam_before)
                                -
                                SUM(terima_good_secondary_dalam_before)
                                +
                                SUM(qty_adjustment_secondary_dalam_before)
                        )
                END
                ) saldo_awal_secondary_dalam,
                (
                (
                        CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                                (
                                SUM(kirim_secondary_dalam_before)
                                -
                                SUM(terima_repaired_secondary_dalam_before)
                                -
                                SUM(terima_good_secondary_dalam_before)
                                +
                                SUM(qty_adjustment_secondary_dalam_before)
                                )
                        END
                )
                +
                SUM(kirim_secondary_dalam)
                -
                SUM(terima_repaired_secondary_dalam)
                -
                SUM(terima_good_secondary_dalam)
                +
                SUM(qty_adjustment_secondary_dalam)
                ) saldo_akhir_secondary_dalam,
                (
                CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_luar_before)
                                -
                                SUM(terima_repaired_secondary_luar_before)
                                -
                                SUM(terima_good_secondary_luar_before)
                                +
                                SUM(qty_adjustment_secondary_luar_before)
                        )
                END
                ) saldo_awal_secondary_luar,
                (
                (
                        CASE 
                        WHEN '".$dateFrom."' < '2026-06-01'
                        THEN 0
                        ELSE
                                (
                                SUM(kirim_secondary_luar_before)
                                -
                                SUM(terima_repaired_secondary_luar_before)
                                -
                                SUM(terima_good_secondary_luar_before)
                                +
                                SUM(qty_adjustment_secondary_luar_before)
                                )
                        END
                )
                +
                SUM(kirim_secondary_luar)
                -
                SUM(terima_repaired_secondary_luar)
                -
                SUM(terima_good_secondary_luar)
                +
                SUM(qty_adjustment_secondary_luar)
                ) saldo_akhir_secondary_luar
            FROM (
                select * from dc
                UNION
                select * from form_list
            ) dc
            group by
                ws, color, size, panel, COALESCE(nama_part, '')
            order by
                ws, color, size, panel, COALESCE(nama_part, '')
        ");

        $fileName = 'laporan-dc';

        $excel = FastExcel::create($fileName);
        
        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Laporan DC'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $dateFrom . ' s/d ' . $dateTo],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
            'No. WS','Buyer','Style','Color','Size', 'Panel', 'Part',
            'Saldo Awal','Masuk','Kirim Sec Dalam','Terima Rep Sec Dalam','Terima Good Sec Dalam', 'Kirim Sec Luar', 'Terima Rep Sec Luar', 'Terima Good Sec Luar', 'Loading', 'Adjustment', 'Saldo Akhir',
            'Mutasi Secondary Dalam','','','','','',
            'Mutasi Secondary Luar','','','','',''
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');
        $sheet->mergeCells('E4:E5');
        $sheet->mergeCells('F4:F5');
        $sheet->mergeCells('G4:G5');
        $sheet->mergeCells('H4:H5');
        $sheet->mergeCells('I4:I5');
        $sheet->mergeCells('J4:J5');
        $sheet->mergeCells('K4:K5');
        $sheet->mergeCells('L4:L5');
        $sheet->mergeCells('M4:M5');
        $sheet->mergeCells('N4:N5');
        $sheet->mergeCells('O4:O5');
        $sheet->mergeCells('P4:P5');
        $sheet->mergeCells('Q4:Q5');
        $sheet->mergeCells('R4:R5');

        $sheet->mergeCells('S4:X4');
        $sheet->mergeCells('Y4:AD4');

        $sheet->setCellStyle('A4:G4', [
            'fill'   => '#ADD8E6',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('H4:R4', [
            'fill'   => '#FFFFE0',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('S4:X4', [
            'fill'   => '#FFD966',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('Y4:AD4', [
            'fill'   => '#90EE90',
            'text-align' => 'center',
        ]);

        $sheet->writeRow([
            '','','','','','','','','','','','','','','','','','',
            'Saldo Awal Secondary',
            'Terima DC',
            'Kirim Rep ke DC',
            'Kirim Good ke DC',
            'Adjustment',
            'Saldo Akhir Secondary',
            'Saldo Awal Secondary',
            'Terima DC',
            'Kirim Rep ke DC',
            'Kirim Good ke DC',
            'Adjustment',
            'Saldo Akhir Secondary',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $sheet->setCellStyle('A5:G5', [
            'fill'   => '#ADD8E6',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('H5:R5', [
            'fill'   => '#FFFFE0',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('S5:X5', [
            'fill'   => '#FFD966',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('Y5:AD5', [
            'fill'   => '#90EE90',
            'text-align' => 'center',
        ]);

        foreach ($dataReport as $row) {
                $saldoAwal = $row->current_saldo_awal_adjustment ?? 0;
                $masuk = $row->qty_in ?? 0;
                $kirimDalam = $row->kirim_secondary_dalam ?? 0;
                $repDalam = $row->terima_repaired_secondary_dalam ?? 0;
                $goodDalam = $row->terima_good_secondary_dalam ?? 0;
                $kirimLuar = $row->kirim_secondary_luar ?? 0;
                $repLuar = $row->terima_repaired_secondary_luar ?? 0;
                $goodLuar = $row->terima_good_secondary_luar ?? 0;
                $loading = $row->loading_qty ?? 0;
                $adjustment = $row->adjustment ?? 0;
                $switchingIn = $row->switching_in ?? 0;
                $switchingOut = $row->switching_out ?? 0;

                $saldoAkhir = $saldoAwal + $masuk - $kirimDalam + $repDalam + $goodDalam - $kirimLuar + $repLuar + $goodLuar - $loading + ($adjustment + $switchingIn - $switchingOut);

                $rows = [
                        $row->ws,
                        $row->buyer,
                        $row->style,
                        $row->color,
                        $row->size,
                        $row->panel,
                        $row->nama_part,

                        (float) ($saldoAwal),
                        (float) ($masuk),
                        (float) ($kirimDalam),
                        (float) ($repDalam),
                        (float) ($goodDalam),
                        (float) ($kirimLuar),
                        (float) ($repLuar),
                        (float) ($goodLuar),
                        (float) ($loading),
                        (float) ($adjustment),
                        (float) ($saldoAkhir),

                        (float) ($row->saldo_awal_secondary_dalam ?? 0),
                        (float) ($row->kirim_secondary_dalam ?? 0),
                        (float) ($row->terima_repaired_secondary_dalam ?? 0),
                        (float) ($row->terima_good_secondary_dalam ?? 0),
                        (float) ($row->qty_adjustment_secondary_dalam ?? 0),
                        (float) ($row->saldo_akhir_secondary_dalam ?? 0),
                        (float) ($row->saldo_awal_secondary_luar ?? 0),
                        (float) ($row->kirim_secondary_luar ?? 0),
                        (float) ($row->terima_repaired_secondary_luar ?? 0),
                        (float) ($row->terima_good_secondary_luar ?? 0),
                        (float) ($row->qty_adjustment_secondary_luar ?? 0),
                        (float) ($row->saldo_akhir_secondary_luar ?? 0),
            ];

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'Z') as $col) {
            $sheet->setColWidth($col, 20);
        }

        $sheet->setColWidth('AA', 20);
        $sheet->setColWidth('AB', 20);
        $sheet->setColWidth('AC', 20);
        $sheet->setColWidth('AD', 20);

        return $excel->download();
    }

    public function report_mutasi_wip_dc_set(Request $request){
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;

        if ($request->ajax()) {
            if ($dateFrom === null || $dateTo === null) {
                return response()->json(['data' => []]);
            } else {
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
                                                        tanggal >= '2026-01-01' and
                                                        tanggal < '".$dateFrom."'
                                        ) tanggal_akhir_rekap on tanggal_akhir_rekap.tanggal = dc_report_rekap.tanggal
                                ),
                                dc as (
                                        SELECT
                                                a.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                a.qty_awal qty_in_dc_main,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                a.tgl_trans < '".$dateFrom."' AND
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
                                                a.qty_awal qty_in_dc,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                a.tgl_trans < '".$dateFrom."' AND
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii_in.tgl_trans < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status = 'main'
                                        GROUP BY s.id, sii_in.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii_in.tgl_trans < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii_in.urutan
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
                                                (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep_main,
                                                null sec_inhouse_rep,
                                                sii.qty_in sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii.tgl_trans < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        GROUP BY s.id, sii.urutan
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
                                                (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                sii.qty_in sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                sii.tgl_trans < '".$dateFrom."' AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii.urutan
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
                                                (CASE WHEN si.tgl_trans < '2026-05-01' THEN 0 ELSE si.qty_replace END) sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                        GROUP BY s.id, si.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                wo.tgl_form < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                wo.tgl_form < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
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
                                                si.qty_in sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                si.tgl_trans < '".$dateFrom."' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
                                ),

                                loading_line_qty as (
                                        SELECT
                                                s.id_qr_stocker,
                                                pd.id AS part_detail_id,
                                                s.so_det_id,

                                                NULL AS qty_in_dc_main,
                                                NULL AS qty_in_dc,
                                                NULL AS sec_inhouse_in_main,
                                                NULL AS sec_inhouse_in,
                                                NULL AS sec_inhouse_rep_main,
                                                NULL AS sec_inhouse_rep,
                                                NULL AS sec_inhouse_out_main,
                                                NULL AS sec_inhouse_out,
                                                NULL AS sec_in_in_main,
                                                NULL AS sec_in_in,
                                                NULL AS sec_in_rep_main,
                                                NULL AS sec_in_rep,
                                                NULL AS sec_in_out_main,
                                                NULL AS sec_in_out,

                                                COALESCE(
                                                        MIN(ll.qty) OVER (
                                                                PARTITION BY
                                                                        COALESCE(p_com.panel, p.panel),
                                                                        s.form_cut_id,
                                                                        s.form_reject_id,
                                                                        s.form_piece_id,
                                                                        s.so_det_id,
                                                                        s.group_stocker,
                                                                        s.ratio,
                                                                        s.stocker_reject
                                                        ),
                                                        ll.qty
                                                ) AS loading_qty
                                        FROM loading_line ll
                                        JOIN stocker_input s ON s.id = ll.stocker_id
                                        LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                        LEFT JOIN part p ON p.id = pd.part_id
                                        LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                        LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                        WHERE
                                                ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                ll.tanggal_loading < '".$dateFrom."'
                                                AND COALESCE(s.cancel, 'n') != 'y'
                                                AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                        ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                        ll.tanggal_loading < '".$dateFrom."' and
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
                                MAX(tanggal) tanggal,
                                stockers,
                                act_costing_ws,
                                buyer,
                                color,
                                so_det_id,
                                panel,
                                panel_status,
                                part_detail_id,
                                nama_part,
                                part_status,
                                SUM(saldo_awal) saldo_awal,
                                SUM(qty_in) qty_in,
                                SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                                SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                                SUM(kirim_secondary_luar) kirim_secondary_luar,
                                SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                                SUM(kirim_secondary_luar) kirim_secondary_luar_before,
                                SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar_before,
                                SUM(terima_good_secondary_luar) terima_good_secondary_luar_before,
                                SUM(loading_qty) loading_qty,
                                SUM(saldo_awal)+SUM(saldo_akhir) saldo_akhir,
                                CURRENT_TIMESTAMP() created_at,
                                CURRENT_TIMESTAMP() updated_at
                        FROM (
                                        SELECT
                                                '2026-03-31' tanggal,
                                                stockers,
                                                buyer,
                                                ws act_costing_ws,
                                                color,
                                                id_so_det so_det_id,
                                                panel,
                                                panel_status,
                                                part_detail_id,
                                                nama_part,
                                                part_status,
                                                0 saldo_awal,
                                                qty_in,
                                                kirim_secondary_dalam,
                                                terima_repaired_secondary_dalam,
                                                terima_good_secondary_dalam,
                                                kirim_secondary_luar,
                                                terima_repaired_secondary_luar,
                                                terima_good_secondary_luar,
                                                loading_qty,
                                                qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir,
                                                CURRENT_TIMESTAMP() created_at,
                                                CURRENT_TIMESTAMP() updated_at
                                        FROM (
                                                SELECT
                                                        GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                                        msb.buyer,
                                                        msb.ws,
                                                        msb.styleno as style,
                                                        msb.color,
                                                        msb.size,
                                                        msb.id_so_det,
                                                        COALESCE(p_com.panel, p.panel) panel,
                                                        COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                                        pd.id as part_detail_id,
                                                        COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                        COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                        (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                        -- loading.stockers,
                                                        SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                                        COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                        UNION ALL
                                                        SELECT
                                                                *
                                                        FROM
                                                                loading_line_qty
                                                ) saldo_dc
                                                LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                                left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                                left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                                left join part p on p.id = pd.part_id
                                                left join part p_com on p_com.id = pd_com.part_id
                                                LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                                LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                                LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                                GROUP BY
                                                        saldo_dc.so_det_id,
                                                        saldo_dc.part_detail_id
                                        ) saldo_dc
                                        UNION ALL
                                        select
                                                tanggal,
                                                stockers,
                                                buyer,
                                                act_costing_ws,
                                                color,
                                                so_det_id,
                                                panel,
                                                panel_status,
                                                part_detail_id,
                                                nama_part,
                                                part_status,
                                                saldo_akhir saldo_awal,
                                                0 qty_in,
                                                0 kirim_secondary_dalam,
                                                0 terima_repaired_secondary_dalam,
                                                0 terima_good_secondary_dalam,
                                                0 kirim_secondary_luar,
                                                0 terima_repaired_secondary_luar,
                                                0 terima_good_secondary_luar,
                                                0 loading_qty,
                                                0 saldo_akhir,
                                                CURRENT_TIMESTAMP() created_at,
                                                CURRENT_TIMESTAMP() updated_at
                                        from
                                                dc_report_rekap
                                        where
                                                tanggal < '".$dateFrom."'
                        ) saldo_dc
                        group by
                                so_det_id,
                                part_detail_id
                        ),
                        dc_current_saldo AS (
                        -- current saldo
                        WITH
                                dc as (
                                        SELECT
                                                a.id_qr_stocker,
                                                pd.id as part_detail_id,
                                                s.so_det_id,
                                                a.qty_awal qty_in_dc_main,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                                a.qty_awal qty_in_dc,
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
                                                null sec_in_out,
                                                null loading_qty
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
                                                a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status = 'main'
                                        GROUP BY s.id, sii_in.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_in_input sii_in
                                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii_in.urutan
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
                                                (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep_main,
                                                null sec_inhouse_rep,
                                                sii.qty_in sec_inhouse_out_main,
                                                null sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        GROUP BY s.id, sii.urutan
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
                                                (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep,
                                                null sec_inhouse_out_main,
                                                sii.qty_in sec_inhouse_out,
                                                null sec_in_in_main,
                                                null sec_in_in,
                                                null sec_in_rep_main,
                                                null sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_inhouse_input sii
                                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                        WHERE
                                                sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        GROUP BY s.id, sii.urutan
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
                                                (CASE WHEN si.tgl_trans < '2026-05-01' THEN 0 ELSE si.qty_replace END) sec_in_rep,
                                                null sec_in_out_main,
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                        GROUP BY s.id, si.urutan
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main'
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                wip_out_det wod
                                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join wip_out wo on wo.id = wod.id_wip_out
                                        WHERE
                                                wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                                        group by
                                                s.id
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
                                                null sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                pd.part_status= 'main' AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
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
                                                si.qty_in sec_in_out,
                                                null loading_qty
                                        FROM
                                                secondary_in_input si
                                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                        WHERE
                                                si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                s.id is not null AND
                                                (s.cancel IS NULL OR s.cancel != 'y') and
                                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                                COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                        GROUP BY s.id, si.urutan
                                ),

                                loading_line_qty as (
                                        SELECT
                                                s.id_qr_stocker,
                                                pd.id AS part_detail_id,
                                                s.so_det_id,

                                                NULL AS qty_in_dc_main,
                                                NULL AS qty_in_dc,
                                                NULL AS sec_inhouse_in_main,
                                                NULL AS sec_inhouse_in,
                                                NULL AS sec_inhouse_rep_main,
                                                NULL AS sec_inhouse_rep,
                                                NULL AS sec_inhouse_out_main,
                                                NULL AS sec_inhouse_out,
                                                NULL AS sec_in_in_main,
                                                NULL AS sec_in_in,
                                                NULL AS sec_in_rep_main,
                                                NULL AS sec_in_rep,
                                                NULL AS sec_in_out_main,
                                                NULL AS sec_in_out,

                                                COALESCE(
                                                        MIN(ll.qty) OVER (
                                                                                PARTITION BY
                                                                                        COALESCE(p_com.panel, p.panel),
                                                                                        s.form_cut_id,
                                                                                        s.form_reject_id,
                                                                                        s.form_piece_id,
                                                                                        s.so_det_id,
                                                                                        s.group_stocker,
                                                                                        s.ratio,
                                                                                        s.stocker_reject
                                                        ),
                                                        ll.qty
                                                ) AS loading_qty
                                        FROM loading_line ll
                                        JOIN stocker_input s ON s.id = ll.stocker_id
                                        LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                        LEFT JOIN part p ON p.id = pd.part_id
                                        LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                        LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                        WHERE
                                                ll.tanggal_loading BETWEEN '$dateFrom' AND '$dateTo'
                                                AND COALESCE(s.cancel, 'n') != 'y'
                                                AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                                ll.tanggal_loading between '".$dateFrom."' AND '$dateTo' AND
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
                                                COALESCE(p_com.panel, p.panel) panel,
                                                COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                                pd.id as part_detail_id,
                                                COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                -- loading.stockers,
                                                SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                                COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                        UNION ALL
                                                        SELECT
                                                                        *
                                                        FROM
                                                                        loading_line_qty
                                        ) saldo_dc
                                        LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                        left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p on p.id = pd.part_id
                                        left join part p_com on p_com.id = pd_com.part_id
                                        LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                        LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                        LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                        GROUP BY
                                                saldo_dc.so_det_id,
                                                saldo_dc.part_detail_id
                                ) saldo_dc
                        ),
                        dc_in_dump_before AS (
                        select
                                '' stockers,
                                dc_in_dump.buyer,
                                dc_in_dump.ws as act_costing_ws,
                                dc_in_dump.style,
                                dc_in_dump.color,
                                dc_in_dump.size,
                                '' so_det_id,
                                dc_in_dump.panel,
                                part.panel_status,
                                part_detail.id part_detail_id,
                                part nama_part,
                                part_detail.part_status,
                                qty_in current_saldo_awal,
                                0 qty_in,
                                0 kirim_secondary_dalam,
                                0 terima_repaired_secondary_dalam,
                                0 terima_good_secondary_dalam,
                                0 kirim_secondary_luar,
                                0 terima_repaired_secondary_luar,
                                0 terima_good_secondary_luar,
                                0 kirim_secondary_luar_before,
                                0 terima_repaired_secondary_luar_before,
                                0 terima_good_secondary_luar_before,
                                0 loading,
                                0 current_saldo_akhir
                        from
                                dc_in_dump
                                left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                                left join part_detail on part_detail.part_id = part.id
                                inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                        where
                                dc_in_dump.tgl_trans < '$dateFrom'
                        group by
                                ws,
                                color,
                                size,
                                part_detail_id
                        ),

                        bom AS (
                                SELECT DISTINCT
                                        ac.kpno ws,
                                        sd.color,
                                        mp.nama_panel
                                FROM signalbit_erp.bom_jo_item k
                                INNER JOIN signalbit_erp.so_det sd ON k.id_so_det = sd.id
                                INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                INNER JOIN signalbit_erp.masteritem mi ON k.id_item = mi.id_gen
                                INNER JOIN signalbit_erp.masterpanel mp ON mp.id = k.id_panel
                                WHERE
                                        k.status = 'M'
                                        AND k.cancel = 'N'
                                        AND sd.cancel = 'N'
                                        AND so.cancel_h = 'N'
                                        AND ac.status = 'confirm'
                                        AND mi.mattype = 'F'
                                        AND ac.dateinput > NOW() - INTERVAL 1 YEAR 
                        ),

                        dc_saldo AS (
                                select
                                stockers,
                                ws,
                                buyer,
                                style,
                                UPPER(TRIM(color)) color,
                                size,
                                panel,
                                nama_part,
                                SUM(current_saldo_awal) current_saldo_awal,
                                SUM(qty_in) qty_in,
                                SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                                SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                                SUM(kirim_secondary_luar) kirim_secondary_luar,
                                SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                                SUM(kirim_secondary_luar_before) kirim_secondary_luar_before,
                                SUM(terima_repaired_secondary_luar_before) terima_repaired_secondary_luar_before,
                                SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before,
                                SUM(loading) loading_qty,
                                SUM(current_saldo_awal)+SUM(current_saldo_akhir) as current_saldo_akhir
                                from (
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
                                        0 as current_saldo_awal,
                                        sum(dc_current_saldo.qty_in) qty_in,
                                        sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                                        sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                        sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                                        sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                                        sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                        sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                                        0 kirim_secondary_luar_before,
                                        0 terima_repaired_secondary_luar_before,
                                        0 terima_good_secondary_luar_before,
                                        sum(dc_current_saldo.loading_qty) loading,
                                        SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir
                                from
                                        dc_current_saldo
                                GROUP BY
                                        dc_current_saldo.ws,
                                        dc_current_saldo.color,
                                        dc_current_saldo.size,
                                        dc_current_saldo.part_detail_id
                                UNION ALL
                                select
                                        GROUP_CONCAT(dc_before_saldo.stockers) as stockers,
                                        msb.buyer,
                                        msb.ws as act_costing_ws,
                                        msb.styleno as style,
                                        msb.color,
                                        msb.size,
                                        GROUP_CONCAT(dc_before_saldo.so_det_id) so_det_id,
                                        dc_before_saldo.panel,
                                        dc_before_saldo.panel_status,
                                        dc_before_saldo.part_detail_id,
                                        GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) as nama_part,
                                        GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) as part_status,
                                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                                        0 qty_in,
                                        0 kirim_secondary_dalam,
                                        0 terima_repaired_secondary_dalam,
                                        0 terima_good_secondary_dalam,
                                        0 kirim_secondary_luar,
                                        0 terima_repaired_secondary_luar,
                                        0 terima_good_secondary_luar,
                                        SUM(dc_before_saldo.kirim_secondary_luar) kirim_secondary_luar_before,
                                        SUM(dc_before_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar_before,
                                        SUM(dc_before_saldo.terima_good_secondary_luar) terima_good_secondary_luar_before,
                                        0 loading,
                                        0 as current_saldo_akhir
                                from
                                        dc_before_saldo
                                        left join master_sb_ws msb on msb.id_so_det = dc_before_saldo.so_det_id
                                GROUP BY
                                        msb.ws,
                                        msb.color,
                                        msb.size,
                                        dc_before_saldo.part_detail_id
                                HAVING
                                        current_saldo_awal != 0
                                UNION ALL
                                select
                                        '' stockers,
                                        dc_in_dump.buyer,
                                        dc_in_dump.ws as act_costing_ws,
                                        dc_in_dump.style,
                                        dc_in_dump.color,
                                        dc_in_dump.size,
                                        '' so_det_id,
                                        dc_in_dump.panel,
                                        part.panel_status,
                                        part_detail.id part_detail_id,
                                        part nama_part,
                                        part_detail.part_status,
                                        0 current_saldo_awal,
                                        qty_in qty_in,
                                        0 kirim_secondary_dalam,
                                        0 terima_repaired_secondary_dalam,
                                        0 terima_good_secondary_dalam,
                                        0 kirim_secondary_luar,
                                        0 terima_repaired_secondary_luar,
                                        0 terima_good_secondary_luar,
                                        0 kirim_secondary_luar_before,
                                        0 terima_repaired_secondary_luar_before,
                                        0 terima_good_secondary_luar_before,
                                        0 loading,
                                        qty_in current_saldo_akhir
                                from
                                        dc_in_dump
                                        left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                                        left join part_detail on part_detail.part_id = part.id
                                        inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                                where
                                        dc_in_dump.tgl_trans between '$dateFrom' AND '$dateTo'
                                group by
                                        ws,
                                        color,
                                        size,
                                        part_detail_id
                                UNION ALL
                                select
                                        stockers,
                                        buyer,
                                        act_costing_ws,
                                        style,
                                        color,
                                        size,
                                        so_det_id,
                                        panel,
                                        panel_status,
                                        part_detail_id,
                                        nama_part,
                                        part_status,
                                        current_saldo_awal,
                                        qty_in,
                                        kirim_secondary_dalam,
                                        terima_repaired_secondary_dalam,
                                        terima_good_secondary_dalam,
                                        kirim_secondary_luar,
                                        terima_repaired_secondary_luar,
                                        terima_good_secondary_luar,
                                        kirim_secondary_luar_before,
                                        terima_repaired_secondary_luar_before,
                                        terima_good_secondary_luar_before,
                                        loading,
                                        current_saldo_akhir
                                from
                                        dc_in_dump_before
                                ) current_saldo
                                group by
                                ws,
                                color,
                                size,
                                panel,
                                nama_part
                        ),

                        dc_panel AS (
                        select
                        stockers,
                        ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        nama_part,
                        SUM(current_saldo_awal) current_saldo_awal,
                        SUM(qty_adjustment_before) adjustment_before,
                        SUM(switching_in_before) switching_in_before,
                        SUM(switching_out_before) switching_out_before,
                        SUM(current_saldo_awal) + SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before) current_saldo_awal_adjustment,
                        SUM(qty_in) qty_in,
                        SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                        SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                        SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                        (
                                SUM(kirim_secondary_luar_before)
                                - SUM(terima_repaired_secondary_luar_before)
                                - SUM(terima_good_secondary_luar_before)
                        ) AS saldo_awal_secondary,
                        SUM(kirim_secondary_luar) kirim_secondary_luar,
                        SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                        SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                        (
                        (
                                SUM(kirim_secondary_luar_before)
                                - SUM(terima_repaired_secondary_luar_before)
                                - SUM(terima_good_secondary_luar_before)
                        )
                        + SUM(kirim_secondary_luar)
                        - SUM(terima_repaired_secondary_luar)
                        - SUM(terima_good_secondary_luar)
                        ) AS saldo_akhir_secondary,
                        SUM(loading_qty) loading_qty,
                        SUM(current_saldo_akhir) current_saldo_akhir,
                        SUM(qty_adjustment) adjustment,
                        SUM(switching_in) switching_in,
                        SUM(switching_out) switching_out,
                        (SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before)) + SUM(current_saldo_akhir) + (SUM(qty_adjustment) + SUM(switching_in) - SUM(switching_out)) current_saldo_akhir_adjustment
                        from (
                        select
                                stockers,
                                ws,
                                buyer,
                                style,
                                color,
                                size,
                                panel,
                                nama_part,
                                current_saldo_awal,
                                qty_in,
                                kirim_secondary_dalam,
                                terima_repaired_secondary_dalam,
                                terima_good_secondary_dalam,
                                kirim_secondary_luar,
                                terima_repaired_secondary_luar,
                                terima_good_secondary_luar,
                                kirim_secondary_luar_before,
                                terima_repaired_secondary_luar_before,
                                terima_good_secondary_luar_before,
                                loading_qty,
                                current_saldo_akhir,
                                0 as qty_adjustment_before,
                                0 qty_adjustment,
                                0 as switching_in_before,
                                0 switching_in,
                                0 as switching_out_before,
                                0 switching_out
                        FROM
                                dc_saldo
                        UNION ALL
                        select
                                null stockers,
                                no_ws ws,
                                buyer,
                                style,
                                color,
                                size,
                                panel,
                                part nama_part,
                                0 current_saldo_awal,
                                0 qty_in,
                                0 kirim_secondary_dalam,
                                0 terima_repaired_secondary_dalam,
                                0 terima_good_secondary_dalam,
                                0 kirim_secondary_luar,
                                0 terima_repaired_secondary_luar,
                                0 terima_good_secondary_luar,
                                0 kirim_secondary_luar_before,
                                0 terima_repaired_secondary_luar_before,
                                0 terima_good_secondary_luar_before,
                                0 loading_qty,
                                0 current_saldo_akhir,
                                SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_before,
                                SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment,
                                0 switching_in_before,
                                0 as switching_in,
                                0 as switching_out_before,
                                0 as switching_out
                        FROM
                                wip_adjustment
                        WHERE
                                tgl_saldo <= '$dateTo' and
                                type_report = 'DC'
                        GROUP BY
                                ws, color, size, panel, part
                        UNION ALL
                        select
                                null stockers,
                                from_no_ws ws,
                                from_buyer,
                                from_style,
                                from_color,
                                from_size,
                                from_panel,
                                from_part nama_part,
                                0 current_saldo_awal,
                                0 qty_in,
                                0 kirim_secondary_dalam,
                                0 terima_repaired_secondary_dalam,
                                0 terima_good_secondary_dalam,
                                0 kirim_secondary_luar,
                                0 terima_repaired_secondary_luar,
                                0 terima_good_secondary_luar,
                                0 kirim_secondary_luar_before,
                                0 terima_repaired_secondary_luar_before,
                                0 terima_good_secondary_luar_before,
                                0 loading_qty,
                                0 current_saldo_akhir,
                                0 as qty_adjustment_before,
                                0 as qty_adjustment,
                                0 as switching_in_before,
                                0 as switching_in,
                                SUM(IF(from_tgl_saldo < '".$dateFrom."',qty,0)) switching_out_before,
                                SUM(IF(from_tgl_saldo >= '".$dateFrom."',qty,0)) as switching_out
                        FROM
                                wip_switching_adj
                        where
                                from_tgl_saldo <= '$dateTo' and
                                type_report = 'DC'
                        GROUP BY
                                from_no_ws, from_color, from_size, from_panel, from_part
                        UNION ALL
                        select
                                null stockers,
                                no_ws ws,
                                buyer,
                                style,
                                color,
                                size,
                                panel,
                                part nama_part,
                                0 current_saldo_awal,
                                0 qty_in,
                                0 kirim_secondary_dalam,
                                0 terima_repaired_secondary_dalam,
                                0 terima_good_secondary_dalam,
                                0 kirim_secondary_luar,
                                0 terima_repaired_secondary_luar,
                                0 terima_good_secondary_luar,
                                0 kirim_secondary_luar_before,
                                0 terima_repaired_secondary_luar_before,
                                0 terima_good_secondary_luar_before,
                                0 loading_qty,
                                0 current_saldo_akhir,
                                0 as qty_adjustment_before,
                                0 as qty_adjustment,
                                SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) switching_in_before,
                                SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as switching_in,
                                0 as switching_out_before,
                                0 as switching_out
                        FROM
                                wip_switching_adj
                        WHERE
                                tgl_saldo <= '$dateTo' and
                                type_report = 'DC'
                        GROUP BY
                                no_ws, color, size, panel, part
                        ) dc
                        group by
                        ws, color, size, panel, COALESCE(nama_part, '')
                        having
                        (
                                current_saldo_awal_adjustment != 0 OR
                                qty_in != 0 OR
                                kirim_secondary_dalam != 0 OR
                                terima_repaired_secondary_dalam != 0 OR
                                terima_good_secondary_dalam != 0 OR
                                kirim_secondary_luar != 0 OR
                                terima_repaired_secondary_luar != 0 OR
                                terima_good_secondary_luar != 0 OR
                                loading_qty != 0 OR
                                current_saldo_akhir_adjustment != 0 OR
                                adjustment != 0 OR
                                switching_in != 0 OR
                                switching_out != 0
                        )
                        ),

                        dc_panel_bom AS (
                                SELECT
                                        d.*
                                FROM dc_panel d
                                INNER JOIN bom b
                                        ON b.ws = d.ws
                                        AND b.color = d.color
                                        AND b.nama_panel = d.panel
                        ),

                        dc_garment AS (
                        SELECT
                                ws,
                                buyer,
                                style,
                                color,
                                size,

                                MIN(current_saldo_awal) current_saldo_awal_adjustment,
                                MIN(qty_in) qty_in,
                                COALESCE(MIN(NULLIF(saldo_awal_secondary,0)),0) saldo_awal_secondary,
                                COALESCE(MIN(NULLIF(kirim_secondary_luar, 0)), 0) kirim_secondary_luar,
                                COALESCE(MIN(NULLIF(terima_repaired_secondary_luar, 0)), 0) terima_repaired_secondary_luar,
                                COALESCE(MIN(NULLIF(terima_good_secondary_luar, 0)), 0) terima_good_secondary_luar,
                                COALESCE(MIN(NULLIF(saldo_akhir_secondary,0)),0) saldo_akhir_secondary,
                                MIN(loading_qty) loading,
                                (
                                MIN(current_saldo_awal)
                                + MIN(qty_in)
                                - COALESCE(MIN(NULLIF(kirim_secondary_luar,0)),0)
                                + COALESCE(MIN(NULLIF(terima_repaired_secondary_luar,0)),0)
                                + COALESCE(MIN(NULLIF(terima_good_secondary_luar,0)),0)
                                - MIN(loading_qty)
                                ) current_saldo_akhir_adjustment
                        FROM dc_panel_bom
                        GROUP BY
                                ws,
                                buyer,
                                style,
                                color,
                                size
                        )

                        SELECT *
                        FROM dc_garment

                ");
            }

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report-mutasi-set', [
            "page" => "dashboard-dc",
            "subPageGroup" => "report",
            "subPage" => "report_mutasi_wip_dc_set",
        ]);
    }

    public function export_excel_report_mutasi_wip_dc_set(Request $request)
    {
        $start_date = $request->from;
        $end_date = $request->to;
        $dateFrom = $request->from;
        $dateTo = $request->to;

        $data = DB::select("
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
                                                    tanggal >= '2026-01-01' and
                                                    tanggal < '".$dateFrom."'
                                    ) tanggal_akhir_rekap on tanggal_akhir_rekap.tanggal = dc_report_rekap.tanggal
                            ),
                            dc as (
                                    SELECT
                                            a.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            a.qty_awal qty_in_dc_main,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
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
                                            a.qty_awal qty_in_dc,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status = 'main'
                                    GROUP BY s.id, sii_in.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii_in.urutan
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
                                            (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep_main,
                                            null sec_inhouse_rep,
                                            sii.qty_in sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    GROUP BY s.id, sii.urutan
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
                                            (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            sii.qty_in sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii.tgl_trans < '".$dateFrom."' AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii.urutan
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
                                        (CASE WHEN si.tgl_trans < '2026-05-01' THEN 0 ELSE si.qty_replace END) sec_in_rep,
                                        null sec_in_out_main,
                                        null sec_in_out,
                                        null loading_qty
                                    FROM
                                        secondary_in_input si
                                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                        left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join master_secondary ms on ms.id = pd.master_secondary_id
                                        left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                        left join master_secondary mms on mms.id = pds.master_secondary_id
                                        left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                        si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                        si.tgl_trans < '".$dateFrom."' AND
                                        s.id is not null AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                        pd.part_status= 'main' AND
                                        COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            wo.tgl_form < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            wo.tgl_form < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            si.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
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
                                            si.qty_in sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            si.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
                            ),

                            loading_line_qty as (
                                    SELECT
                                            s.id_qr_stocker,
                                            pd.id AS part_detail_id,
                                            s.so_det_id,

                                            NULL AS qty_in_dc_main,
                                            NULL AS qty_in_dc,
                                            NULL AS sec_inhouse_in_main,
                                            NULL AS sec_inhouse_in,
                                            NULL AS sec_inhouse_rep_main,
                                            NULL AS sec_inhouse_rep,
                                            NULL AS sec_inhouse_out_main,
                                            NULL AS sec_inhouse_out,
                                            NULL AS sec_in_in_main,
                                            NULL AS sec_in_in,
                                            NULL AS sec_in_rep_main,
                                            NULL AS sec_in_rep,
                                            NULL AS sec_in_out_main,
                                            NULL AS sec_in_out,

                                            COALESCE(
                                                    MIN(ll.qty) OVER (
                                                            PARTITION BY
                                                                    COALESCE(p_com.panel, p.panel),
                                                                    s.form_cut_id,
                                                                    s.form_reject_id,
                                                                    s.form_piece_id,
                                                                    s.so_det_id,
                                                                    s.group_stocker,
                                                                    s.ratio,
                                                                    s.stocker_reject
                                                    ),
                                                    ll.qty
                                            ) AS loading_qty
                                    FROM loading_line ll
                                    JOIN stocker_input s ON s.id = ll.stocker_id
                                    LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    WHERE
                                            ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            ll.tanggal_loading < '".$dateFrom."'
                                            AND COALESCE(s.cancel, 'n') != 'y'
                                            AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                    ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    ll.tanggal_loading < '".$dateFrom."' and
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
                            MAX(tanggal) tanggal,
                            stockers,
                            act_costing_ws,
                            buyer,
                            color,
                            so_det_id,
                            panel,
                            panel_status,
                            part_detail_id,
                            nama_part,
                            part_status,
                            SUM(saldo_awal) saldo_awal,
                            SUM(qty_in) qty_in,
                            SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                            SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                            SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                            SUM(kirim_secondary_luar) kirim_secondary_luar,
                            SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                            SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                            SUM(kirim_secondary_luar) kirim_secondary_luar_before,
                            SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar_before,
                            SUM(terima_good_secondary_luar) terima_good_secondary_luar_before,
                            SUM(loading_qty) loading_qty,
                            SUM(saldo_awal)+SUM(saldo_akhir) saldo_akhir,
                            CURRENT_TIMESTAMP() created_at,
                            CURRENT_TIMESTAMP() updated_at
                    FROM (
                                    SELECT
                                            '2026-03-31' tanggal,
                                            stockers,
                                            buyer,
                                            ws act_costing_ws,
                                            color,
                                            id_so_det so_det_id,
                                            panel,
                                            panel_status,
                                            part_detail_id,
                                            nama_part,
                                            part_status,
                                            0 saldo_awal,
                                            qty_in,
                                            kirim_secondary_dalam,
                                            terima_repaired_secondary_dalam,
                                            terima_good_secondary_dalam,
                                            kirim_secondary_luar,
                                            terima_repaired_secondary_luar,
                                            terima_good_secondary_luar,
                                            loading_qty,
                                            qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir,
                                            CURRENT_TIMESTAMP() created_at,
                                            CURRENT_TIMESTAMP() updated_at
                                    FROM (
                                            SELECT
                                                    GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                                    msb.buyer,
                                                    msb.ws,
                                                    msb.styleno as style,
                                                    msb.color,
                                                    msb.size,
                                                    msb.id_so_det,
                                                    COALESCE(p_com.panel, p.panel) panel,
                                                    COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                                    pd.id as part_detail_id,
                                                    COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                    COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                    (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                    -- loading.stockers,
                                                    SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                                    COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                    UNION ALL
                                                    SELECT
                                                            *
                                                    FROM
                                                            loading_line_qty
                                            ) saldo_dc
                                            LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                            left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                            left join part p on p.id = pd.part_id
                                            left join part p_com on p_com.id = pd_com.part_id
                                            LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                            LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                            LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                            GROUP BY
                                                    saldo_dc.so_det_id,
                                                    saldo_dc.part_detail_id
                                    ) saldo_dc
                                    UNION ALL
                                    select
                                            tanggal,
                                            stockers,
                                            buyer,
                                            act_costing_ws,
                                            color,
                                            so_det_id,
                                            panel,
                                            panel_status,
                                            part_detail_id,
                                            nama_part,
                                            part_status,
                                            saldo_akhir saldo_awal,
                                            0 qty_in,
                                            0 kirim_secondary_dalam,
                                            0 terima_repaired_secondary_dalam,
                                            0 terima_good_secondary_dalam,
                                            0 kirim_secondary_luar,
                                            0 terima_repaired_secondary_luar,
                                            0 terima_good_secondary_luar,
                                            0 loading_qty,
                                            0 saldo_akhir,
                                            CURRENT_TIMESTAMP() created_at,
                                            CURRENT_TIMESTAMP() updated_at
                                    from
                                            dc_report_rekap
                                    where
                                            tanggal < '".$dateFrom."'
                    ) saldo_dc
                    group by
                            so_det_id,
                            part_detail_id
                ),
                dc_current_saldo AS (
                    -- current saldo
                    WITH
                            dc as (
                                    SELECT
                                            a.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            a.qty_awal qty_in_dc_main,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                            a.qty_awal qty_in_dc,
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
                                            null sec_in_out,
                                            null loading_qty
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
                                            a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status = 'main'
                                    GROUP BY s.id, sii_in.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii_in.urutan
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
                                            (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep_main,
                                            null sec_inhouse_rep,
                                            sii.qty_in sec_inhouse_out_main,
                                            null sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    GROUP BY s.id, sii.urutan
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
                                            (CASE WHEN sii.tgl_trans < '2026-05-01' THEN sii.qty_replace ELSE 0 END) sec_inhouse_rep,
                                            null sec_inhouse_out_main,
                                            sii.qty_in sec_inhouse_out,
                                            null sec_in_in_main,
                                            null sec_in_in,
                                            null sec_in_rep_main,
                                            null sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                    WHERE
                                            sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    GROUP BY s.id, sii.urutan
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
                                            (CASE WHEN si.tgl_trans < '2026-05-01' THEN 0 ELSE si.qty_replace END) sec_in_rep,
                                            null sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main'
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL)
                                    group by
                                            s.id
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
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
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
                                            si.qty_in sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
                            ),

                            loading_line_qty as (
                                    SELECT
                                            s.id_qr_stocker,
                                            pd.id AS part_detail_id,
                                            s.so_det_id,

                                            NULL AS qty_in_dc_main,
                                            NULL AS qty_in_dc,
                                            NULL AS sec_inhouse_in_main,
                                            NULL AS sec_inhouse_in,
                                            NULL AS sec_inhouse_rep_main,
                                            NULL AS sec_inhouse_rep,
                                            NULL AS sec_inhouse_out_main,
                                            NULL AS sec_inhouse_out,
                                            NULL AS sec_in_in_main,
                                            NULL AS sec_in_in,
                                            NULL AS sec_in_rep_main,
                                            NULL AS sec_in_rep,
                                            NULL AS sec_in_out_main,
                                            NULL AS sec_in_out,

                                            COALESCE(
                                                    MIN(ll.qty) OVER (
                                                                            PARTITION BY
                                                                                    COALESCE(p_com.panel, p.panel),
                                                                                    s.form_cut_id,
                                                                                    s.form_reject_id,
                                                                                    s.form_piece_id,
                                                                                    s.so_det_id,
                                                                                    s.group_stocker,
                                                                                    s.ratio,
                                                                                    s.stocker_reject
                                                    ),
                                                    ll.qty
                                            ) AS loading_qty
                                    FROM loading_line ll
                                    JOIN stocker_input s ON s.id = ll.stocker_id
                                    LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail AND pd.part_status = 'complement'
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    WHERE
                                            ll.tanggal_loading BETWEEN '$dateFrom' AND '$dateTo'
                                            AND COALESCE(s.cancel, 'n') != 'y'
                                            AND (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
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
                                                            ll.tanggal_loading between '".$dateFrom."' AND '$dateTo' AND
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
                                            COALESCE(p_com.panel, p.panel) panel,
                                            COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                            pd.id as part_detail_id,
                                            COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                            COALESCE(GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                            (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                            -- loading.stockers,
                                            SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                            COALESCE(loading_line.loading_qty, 0) loading_qty1
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
                                                    UNION ALL
                                                    SELECT
                                                                    *
                                                    FROM
                                                                    loading_line_qty
                                    ) saldo_dc
                                    LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                                    left join part_detail pd on pd.id = saldo_dc.part_detail_id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                    left join part p on p.id = pd.part_id
                                    left join part p_com on p_com.id = pd_com.part_id
                                    LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                    LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                    GROUP BY
                                            saldo_dc.so_det_id,
                                            saldo_dc.part_detail_id
                            ) saldo_dc
                ),
                dc_in_dump_before AS (
                    select
                            '' stockers,
                            dc_in_dump.buyer,
                            dc_in_dump.ws as act_costing_ws,
                            dc_in_dump.style,
                            dc_in_dump.color,
                            dc_in_dump.size,
                            '' so_det_id,
                            dc_in_dump.panel,
                            part.panel_status,
                            part_detail.id part_detail_id,
                            part nama_part,
                            part_detail.part_status,
                            qty_in current_saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 kirim_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before,
                            0 terima_good_secondary_luar_before,
                            0 loading,
                            0 current_saldo_akhir
                    from
                            dc_in_dump
                            left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                            left join part_detail on part_detail.part_id = part.id
                            inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                    where
                            dc_in_dump.tgl_trans < '$dateFrom'
                    group by
                            ws,
                            color,
                            size,
                            part_detail_id
                ),

                bom AS (
                        SELECT DISTINCT
                                ac.kpno ws,
                                sd.color,
                                mp.nama_panel
                        FROM signalbit_erp.bom_jo_item k
                        INNER JOIN signalbit_erp.so_det sd ON k.id_so_det = sd.id
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.masteritem mi ON k.id_item = mi.id_gen
                        INNER JOIN signalbit_erp.masterpanel mp ON mp.id = k.id_panel
                        WHERE
                                k.status = 'M'
                                AND k.cancel = 'N'
                                AND sd.cancel = 'N'
                                AND so.cancel_h = 'N'
                                AND ac.status = 'confirm'
                                AND mi.mattype = 'F'
                                AND ac.dateinput > NOW() - INTERVAL 1 YEAR 
                ),

                dc_saldo AS (
                        select
                            stockers,
                            ws,
                            buyer,
                            style,
                            UPPER(TRIM(color)) color,
                            size,
                            panel,
                            nama_part,
                            SUM(current_saldo_awal) current_saldo_awal,
                            SUM(qty_in) qty_in,
                            SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                            SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                            SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                            SUM(kirim_secondary_luar) kirim_secondary_luar,
                            SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                            SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                            SUM(kirim_secondary_luar_before) kirim_secondary_luar_before,
                            SUM(terima_repaired_secondary_luar_before) terima_repaired_secondary_luar_before,
                            SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before,
                            SUM(loading) loading_qty,
                            SUM(current_saldo_awal)+SUM(current_saldo_akhir) as current_saldo_akhir
                        from (
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
                                    0 as current_saldo_awal,
                                    sum(dc_current_saldo.qty_in) qty_in,
                                    sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                                    sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                    sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                                    sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                                    sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                    sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                                    0 kirim_secondary_luar_before,
                                    0 terima_repaired_secondary_luar_before,
                                    0 terima_good_secondary_luar_before,
                                    sum(dc_current_saldo.loading_qty) loading,
                                    SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir
                            from
                                    dc_current_saldo
                            GROUP BY
                                    dc_current_saldo.ws,
                                    dc_current_saldo.color,
                                    dc_current_saldo.size,
                                    dc_current_saldo.part_detail_id
                            UNION ALL
                            select
                                    GROUP_CONCAT(dc_before_saldo.stockers) as stockers,
                                    msb.buyer,
                                    msb.ws as act_costing_ws,
                                    msb.styleno as style,
                                    msb.color,
                                    msb.size,
                                    GROUP_CONCAT(dc_before_saldo.so_det_id) so_det_id,
                                    dc_before_saldo.panel,
                                    dc_before_saldo.panel_status,
                                    dc_before_saldo.part_detail_id,
                                    GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) as nama_part,
                                    GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) as part_status,
                                    SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                                    0 qty_in,
                                    0 kirim_secondary_dalam,
                                    0 terima_repaired_secondary_dalam,
                                    0 terima_good_secondary_dalam,
                                    0 kirim_secondary_luar,
                                    0 terima_repaired_secondary_luar,
                                    0 terima_good_secondary_luar,
                                    SUM(dc_before_saldo.kirim_secondary_luar) kirim_secondary_luar_before,
                                    SUM(dc_before_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar_before,
                                    SUM(dc_before_saldo.terima_good_secondary_luar) terima_good_secondary_luar_before,
                                    0 loading,
                                    0 as current_saldo_akhir
                            from
                                    dc_before_saldo
                                    left join master_sb_ws msb on msb.id_so_det = dc_before_saldo.so_det_id
                            GROUP BY
                                    msb.ws,
                                    msb.color,
                                    msb.size,
                                    dc_before_saldo.part_detail_id
                            HAVING
                                    current_saldo_awal != 0
                            UNION ALL
                            select
                                    '' stockers,
                                    dc_in_dump.buyer,
                                    dc_in_dump.ws as act_costing_ws,
                                    dc_in_dump.style,
                                    dc_in_dump.color,
                                    dc_in_dump.size,
                                    '' so_det_id,
                                    dc_in_dump.panel,
                                    part.panel_status,
                                    part_detail.id part_detail_id,
                                    part nama_part,
                                    part_detail.part_status,
                                    0 current_saldo_awal,
                                    qty_in qty_in,
                                    0 kirim_secondary_dalam,
                                    0 terima_repaired_secondary_dalam,
                                    0 terima_good_secondary_dalam,
                                    0 kirim_secondary_luar,
                                    0 terima_repaired_secondary_luar,
                                    0 terima_good_secondary_luar,
                                    0 kirim_secondary_luar_before,
                                    0 terima_repaired_secondary_luar_before,
                                    0 terima_good_secondary_luar_before,
                                    0 loading,
                                    qty_in current_saldo_akhir
                            from
                                    dc_in_dump
                                    left join part on part.act_costing_ws = dc_in_dump.ws and part.panel = dc_in_dump.panel
                                    left join part_detail on part_detail.part_id = part.id
                                    inner join master_part ON master_part.id = part_detail.master_part_id and master_part.nama_part = dc_in_dump.part
                            where
                                    dc_in_dump.tgl_trans between '$dateFrom' AND '$dateTo'
                            group by
                                    ws,
                                    color,
                                    size,
                                    part_detail_id
                            UNION ALL
                            select
                                    stockers,
                                    buyer,
                                    act_costing_ws,
                                    style,
                                    color,
                                    size,
                                    so_det_id,
                                    panel,
                                    panel_status,
                                    part_detail_id,
                                    nama_part,
                                    part_status,
                                    current_saldo_awal,
                                    qty_in,
                                    kirim_secondary_dalam,
                                    terima_repaired_secondary_dalam,
                                    terima_good_secondary_dalam,
                                    kirim_secondary_luar,
                                    terima_repaired_secondary_luar,
                                    terima_good_secondary_luar,
                                    kirim_secondary_luar_before,
                                    terima_repaired_secondary_luar_before,
                                    terima_good_secondary_luar_before,
                                    loading,
                                    current_saldo_akhir
                            from
                                    dc_in_dump_before
                        ) current_saldo
                        group by
                            ws,
                            color,
                            size,
                            panel,
                            nama_part
                ),

                dc_panel AS (
                select
                    stockers,
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    panel,
                    nama_part,
                    SUM(current_saldo_awal) current_saldo_awal,
                    SUM(qty_adjustment_before) adjustment_before,
                    SUM(switching_in_before) switching_in_before,
                    SUM(switching_out_before) switching_out_before,
                    SUM(current_saldo_awal) + SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before) current_saldo_awal_adjustment,
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    (
                        SUM(kirim_secondary_luar_before)
                        - SUM(terima_repaired_secondary_luar_before)
                        - SUM(terima_good_secondary_luar_before)
                    ) AS saldo_awal_secondary,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                    (
                    (
                        SUM(kirim_secondary_luar_before)
                        - SUM(terima_repaired_secondary_luar_before)
                        - SUM(terima_good_secondary_luar_before)
                    )
                    + SUM(kirim_secondary_luar)
                    - SUM(terima_repaired_secondary_luar)
                    - SUM(terima_good_secondary_luar)
                    ) AS saldo_akhir_secondary,
                    SUM(loading_qty) loading_qty,
                    SUM(current_saldo_akhir) current_saldo_akhir,
                    SUM(qty_adjustment) adjustment,
                    SUM(switching_in) switching_in,
                    SUM(switching_out) switching_out,
                    (SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before)) + SUM(current_saldo_akhir) + (SUM(qty_adjustment) + SUM(switching_in) - SUM(switching_out)) current_saldo_akhir_adjustment
                from (
                    select
                        stockers,
                        ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        nama_part,
                        current_saldo_awal,
                        qty_in,
                        kirim_secondary_dalam,
                        terima_repaired_secondary_dalam,
                        terima_good_secondary_dalam,
                        kirim_secondary_luar,
                        terima_repaired_secondary_luar,
                        terima_good_secondary_luar,
                        kirim_secondary_luar_before,
                        terima_repaired_secondary_luar_before,
                        terima_good_secondary_luar_before,
                        loading_qty,
                        current_saldo_akhir,
                        0 as qty_adjustment_before,
                        0 qty_adjustment,
                        0 as switching_in_before,
                        0 switching_in,
                        0 as switching_out_before,
                        0 switching_out
                    FROM
                        dc_saldo
                    UNION ALL
                    select
                        null stockers,
                        no_ws ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out
                    FROM
                        wip_adjustment
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC'
                    GROUP BY
                        ws, color, size, panel, part
                    UNION ALL
                    select
                        null stockers,
                        from_no_ws ws,
                        from_buyer,
                        from_style,
                        from_color,
                        from_size,
                        from_panel,
                        from_part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 as qty_adjustment_before,
                        0 as qty_adjustment,
                        0 as switching_in_before,
                        0 as switching_in,
                        SUM(IF(from_tgl_saldo < '".$dateFrom."',qty,0)) switching_out_before,
                        SUM(IF(from_tgl_saldo >= '".$dateFrom."',qty,0)) as switching_out
                    FROM
                        wip_switching_adj
                    where
                        from_tgl_saldo <= '$dateTo' and
                        type_report = 'DC'
                    GROUP BY
                        from_no_ws, from_color, from_size, from_panel, from_part
                    UNION ALL
                    select
                        null stockers,
                        no_ws ws,
                        buyer,
                        style,
                        color,
                        size,
                        panel,
                        part nama_part,
                        0 current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 kirim_secondary_luar_before,
                        0 terima_repaired_secondary_luar_before,
                        0 terima_good_secondary_luar_before,
                        0 loading_qty,
                        0 current_saldo_akhir,
                        0 as qty_adjustment_before,
                        0 as qty_adjustment,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) switching_in_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as switching_in,
                        0 as switching_out_before,
                        0 as switching_out
                    FROM
                        wip_switching_adj
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC'
                    GROUP BY
                        no_ws, color, size, panel, part
                ) dc
                group by
                    ws, color, size, panel, COALESCE(nama_part, '')
                having
                    (
                        current_saldo_awal_adjustment != 0 OR
                        qty_in != 0 OR
                        kirim_secondary_dalam != 0 OR
                        terima_repaired_secondary_dalam != 0 OR
                        terima_good_secondary_dalam != 0 OR
                        kirim_secondary_luar != 0 OR
                        terima_repaired_secondary_luar != 0 OR
                        terima_good_secondary_luar != 0 OR
                        loading_qty != 0 OR
                        current_saldo_akhir_adjustment != 0 OR
                        adjustment != 0 OR
                        switching_in != 0 OR
                        switching_out != 0
                    )
                ),

                dc_panel_bom AS (
                        SELECT
                                d.*
                        FROM dc_panel d
                        INNER JOIN bom b
                                ON b.ws = d.ws
                                AND b.color = d.color
                                AND b.nama_panel = d.panel
                ),

                dc_garment AS (
                SELECT
                        ws,
                        buyer,
                        style,
                        color,
                        size,

                        MIN(current_saldo_awal) current_saldo_awal_adjustment,
                        MIN(qty_in) qty_in,
                        COALESCE(MIN(NULLIF(saldo_awal_secondary,0)),0) saldo_awal_secondary,
                        COALESCE(MIN(NULLIF(kirim_secondary_luar, 0)), 0) kirim_secondary_luar,
                        COALESCE(MIN(NULLIF(terima_repaired_secondary_luar, 0)), 0) terima_repaired_secondary_luar,
                        COALESCE(MIN(NULLIF(terima_good_secondary_luar, 0)), 0) terima_good_secondary_luar,
                        COALESCE(MIN(NULLIF(saldo_akhir_secondary,0)),0) saldo_akhir_secondary,
                        MIN(loading_qty) loading,
                        (
                        MIN(current_saldo_awal)
                        + MIN(qty_in)
                        - COALESCE(MIN(NULLIF(kirim_secondary_luar,0)),0)
                        + COALESCE(MIN(NULLIF(terima_repaired_secondary_luar,0)),0)
                        + COALESCE(MIN(NULLIF(terima_good_secondary_luar,0)),0)
                        - MIN(loading_qty)
                        ) current_saldo_akhir_adjustment
                FROM dc_panel_bom
                GROUP BY
                        ws,
                        buyer,
                        style,
                        color,
                        size
                )

                SELECT *
                FROM dc_garment

        ");

        $fileName = 'report-mutasi-wip-set-dc';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Report Mutasi WIP Set DC'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $start_date . ' s/d ' . $end_date],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
                'No. WS',
                'Buyer',
                'Style',
                'Color',
                'Size',
                'Mutasi DC',
                '',
                '',
                '',
                '',
                '',
                '',
                'Mutasi Secondary Luar',
                '',
                '',
                '',
                ''
        ], [
                'font-style' => 'bold',
                'border' => 'thin',
                'halign' => 'center',
                'valign'     => 'center',
                'text-align' => 'center',
        ]);

        $sheet->writeRow([
                '',
                '',
                '',
                '',
                '',
                'Saldo Awal',
                'Masuk',
                'Kirim Sec Luar',
                'Terima Repaired Sec Luar',
                'Terima Good Sec Luar',
                'Loading',
                'Saldo Akhir',
                'Saldo Awal Secondary',
                'Terima DC',
                'Kirim Rep ke DC',
                'Kirim Good ke DC',
                'Saldo Akhir Secondary'
        ], [
                'font-style' => 'bold',
                'border' => 'thin',
                'halign' => 'center',
                'valign'     => 'center',
                'text-align' => 'center',
        ]);

        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');
        $sheet->mergeCells('E4:E5');

        $sheet->mergeCells('F4:L4');
        $sheet->mergeCells('M4:Q4');

        foreach ($data as $row) {

        $rows = [
                $row->ws ?: '',
                $row->buyer ?: '',
                $row->style ?: '',
                $row->color ?: '',
                $row->size ?: '',

                (float) ($row->current_saldo_awal_adjustment ?? 0),
                (float) ($row->qty_in ?? 0),
                (float) ($row->kirim_secondary_luar ?? 0),
                (float) ($row->terima_repaired_secondary_luar ?? 0),
                (float) ($row->terima_good_secondary_luar ?? 0),
                (float) ($row->loading ?? 0),
                (float) ($row->current_saldo_akhir_adjustment ?? 0),

                (float) ($row->saldo_awal_secondary ?? 0),
                (float) ($row->kirim_secondary_luar ?? 0),
                (float) ($row->terima_repaired_secondary_luar ?? 0),
                (float) ($row->terima_good_secondary_luar ?? 0),
                (float) ($row->saldo_akhir_secondary ?? 0),
        ];

        $sheet->writeRow(
                $rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'Q') as $col) {
                $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}
