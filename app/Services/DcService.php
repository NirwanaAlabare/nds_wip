<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use DB;

class DcService
{
    public function buildQuery($from, $to) {
        $dateFrom = $from ? $from : date("Y-m-d");
        $dateTo = $to ? $to : date("Y-m-d");

        $query = "
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
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_part mp on mp.id = pd.master_part_id
                                    where
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_part mp on mp.id = pd.master_part_id
                                    where
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            (CASE WHEN siu.tgl_trans >= '2026-05-01' THEN siu.replace ELSE null END) sec_inhouse_rep_main,
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
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            siu.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY siu.id
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            null sec_inhouse_rep_main,
                                            (CASE WHEN siu.tgl_trans >= '2026-05-01' THEN siu.replace ELSE null END) sec_inhouse_rep,
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
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            siu.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY siu.id
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            wo.tgl_form < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
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
                                            siu.replace sec_in_rep_main,
                                            null sec_in_rep,
                                            (0 - COALESCE(siu.reject, 0)) sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            siu.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY siu.id
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
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
                                            siu.replace sec_in_rep,
                                            null sec_in_out_main,
                                            (0 - COALESCE(siu.reject, 0)) sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            siu.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY siu.id
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
                                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    LEFT JOIN part_custom pcust ON pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                                                    GROUP_CONCAT(ll.stocker_id) stocker_id,
                                                    s.so_det_id,
                                                    MIN(ll.qty) loading_qty
                                            from
                                                    loading_line ll
                                                    left join stocker_input s on s.id = ll.stocker_id
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                    left join part p on p.id = pd.part_id
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join part p_com on p_com.id = pd_com.part_id
                                            where
                                                    ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    ll.tanggal_loading < '".$dateFrom."' and
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                            group by
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) panel_status,
                                                    pd.id as part_detail_id,
                                                    COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                    COALESCE(GROUP_CONCAT(DISTINCT UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')))) as part_status,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
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
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            left join part p on p.id = pd.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            dc_rekap
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
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_part mp on mp.id = pd.master_part_id
                                    where
                                            a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_part mp on mp.id = pd.master_part_id
                                    where
                                            a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join part p on p.id = pd.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join part p on p.id = pd.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY s.id, si.urutan
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            (CASE WHEN siu.tgl_trans >= '2026-05-01' THEN siu.replace ELSE null END) sec_inhouse_rep_main,
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
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY siu.id
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
                                            pd.id as part_detail_id,
                                            s.so_det_id,
                                            null qty_in_dc_main,
                                            null qty_in_dc,
                                            null sec_inhouse_in_main,
                                            null sec_inhouse_in,
                                            null sec_inhouse_rep_main,
                                            (CASE WHEN siu.tgl_trans >= '2026-05-01' THEN siu.replace ELSE null END) sec_inhouse_rep,
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
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY DALAM'
                                    GROUP BY siu.id
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY s.id, si.urutan
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
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
                                            siu.replace sec_in_rep_main,
                                            null sec_in_rep,
                                            (0 - COALESCE(siu.reject, 0)) sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            pd.part_status= 'main' AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY siu.id
                                    UNION ALL
                                    SELECT
                                            siu.id_qr_stocker,
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
                                            siu.replace sec_in_rep,
                                            null sec_in_out_main,
                                            (0 - COALESCE(siu.reject, 0)) sec_in_out,
                                            null loading_qty
                                    FROM
                                            secondary_in_update siu
                                            left join secondary_in_input si on si.id = siu.secondary_in_id
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join master_secondary ms on ms.id = pd.master_secondary_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                            left join master_secondary mms on mms.id = pds.master_secondary_id
                                            left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                    WHERE
                                            siu.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                            COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                    GROUP BY siu.id
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
                                                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                                                            GROUP_CONCAT(ll.stocker_id) stocker_id,
                                                            s.so_det_id,
                                                            MIN(ll.qty) loading_qty
                                                    from
                                                            loading_line ll
                                                            left join stocker_input s on s.id = ll.stocker_id
                                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                            left join part p_com on p_com.id = pd_com.part_id
                                                    where
                                                            ll.tanggal_loading between '".$dateFrom."' AND '$dateTo' AND
                                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                                    group by
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) panel_status,
                                            pd.id as part_detail_id,
                                            COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                            COALESCE(GROUP_CONCAT(DISTINCT UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')))) as part_status,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
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
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                    left join part p on p.id = pd.part_id
                                    left join part p_com on p_com.id = pd_com.part_id
                                    LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                            0 terima_good_secondary_luar_before,
                            0 terima_repaired_secondary_luar_before_new,
                            0 terima_good_secondary_luar_before_new
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
                dc_before_saldo_secondary AS (
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
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
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    where
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    from
                                            dc_in_input a
                                            left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join form_cut_input f on f.id = s.form_cut_id
                                            left join form_cut_reject fr on fr.id = s.form_reject_id
                                            left join form_cut_piece fp on fp.id = s.form_piece_id
                                            left join part_detail pd on s.part_detail_id = pd.id
                                            left join part p on pd.part_id = p.id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join part_detail pdcom on pdcom.id = pd.from_part_detail
                                            left join part pcom on pcom.id = pdcom.part_id
                                            left join master_part mp on mp.id = pd.master_part_id
                                    where
                                            a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            a.tgl_trans < '".$dateFrom."' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            sii_in.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_inhouse_in_input sii_in
                                            left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii_in.tgl_trans < '".$dateFrom."' AND
                                            sii_in.tgl_trans >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_inhouse_input sii
                                            left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                    WHERE
                                            sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            sii.tgl_trans < '".$dateFrom."' AND
                                            sii.tgl_trans >= '2026-05-01' AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            wip_out_det wod
                                            left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            left join wip_out wo on wo.id = wod.id_wip_out
                                    WHERE
                                            wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                            wo.tgl_form < '".$dateFrom."' AND
                                            wo.tgl_form >= '2026-05-01' AND
                                            s.id is not null AND
                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                            CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep_main,
                                            null sec_in_rep,
                                            CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out_main,
                                            null sec_in_out,
                                            null loading_qty,
                                            CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep,
                                            null sec_in_out_main,
                                            CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out,
                                            null loading_qty,
                                            null sec_in_rep_main_new,
                                            CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out_new
                                    FROM
                                            secondary_in_input si
                                            left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                            left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                            left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
                                                                    s.form_cut_id,
                                                                    s.form_reject_id,
                                                                    s.form_piece_id,
                                                                    s.so_det_id,
                                                                    s.group_stocker,
                                                                    s.ratio,
                                                                    s.stocker_reject
                                                    ),
                                                    ll.qty
                                            ) AS loading_qty,
                                            null sec_in_rep_main_new,
                                            null sec_in_rep_new,
                                            null sec_in_out_main_new,
                                            null sec_in_out_new
                                    FROM loading_line ll
                                    JOIN stocker_input s ON s.id = ll.stocker_id
                                    LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                    LEFT JOIN part p ON p.id = pd.part_id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = s.color
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
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                                                    GROUP_CONCAT(ll.stocker_id) stocker_id,
                                                    s.so_det_id,
                                                    MIN(ll.qty) loading_qty
                                            from
                                                    loading_line ll
                                                    left join stocker_input s on s.id = ll.stocker_id
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join part p on p.id = pd.part_id
                                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join part p_com on p_com.id = pd_com.part_id
                                            where
                                                    ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    ll.tanggal_loading < '".$dateFrom."' and
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                            group by
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                            SUM(terima_repaired_secondary_luar_new) terima_repaired_secondary_luar_new,
                            SUM(terima_good_secondary_luar_new) terima_good_secondary_luar_new,
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
                                            terima_repaired_secondary_luar_new,
                                            terima_good_secondary_luar_new,
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
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) panel_status,
                                                    pd.id as part_detail_id,
                                                    COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                    COALESCE(GROUP_CONCAT(DISTINCT UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')))) as part_status,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main_new, 0)), SUM(COALESCE(sec_in_rep_new,0))) ELSE SUM(COALESCE(sec_in_rep_new,0)) END) terima_repaired_secondary_luar_new,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main_new, 0)), SUM(COALESCE(sec_in_out_new,0))) ELSE SUM(COALESCE(sec_in_out_new, 0)) END) terima_good_secondary_luar_new,
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
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            left join part p on p.id = pd.part_id
                                                left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                            kirim_secondary_dalam,
                                            terima_repaired_secondary_dalam,
                                            terima_good_secondary_dalam,
                                            kirim_secondary_luar,
                                            CASE WHEN tanggal < '2026-07-01' THEN terima_repaired_secondary_luar ELSE 0 END AS terima_repaired_secondary_luar,
                                            CASE WHEN tanggal < '2026-07-01' THEN terima_good_secondary_luar ELSE 0 END AS terima_good_secondary_luar,
                                            CASE WHEN tanggal >= '2026-07-01' THEN terima_repaired_secondary_luar ELSE 0 END AS terima_repaired_secondary_luar_new,
                                            CASE WHEN tanggal >= '2026-07-01' THEN terima_good_secondary_luar ELSE 0 END AS terima_good_secondary_luar_new,
                                            0 loading_qty,
                                            0 saldo_akhir,
                                            CURRENT_TIMESTAMP() created_at,
                                            CURRENT_TIMESTAMP() updated_at
                                    from
                                            dc_rekap
                                    where
                                            tanggal < '".$dateFrom."'
                    ) saldo_dc
                    group by
                            so_det_id,
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
                            SUM(terima_good_secondary_luar_before) terima_good_secondary_luar_before,
                            SUM(terima_repaired_secondary_luar_before_new) terima_repaired_secondary_luar_before_new,
                            SUM(terima_good_secondary_luar_before_new) terima_good_secondary_luar_before_new
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
                                    0 as terima_good_secondary_luar_before,
                                    0 as terima_repaired_secondary_luar_before_new,
                                    0 as terima_good_secondary_luar_before_new
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
                                    0 kirim_secondary_dalam_before,
                                    0 terima_repaired_secondary_dalam_before,
                                    0 terima_good_secondary_dalam_before,
                                    0 kirim_secondary_luar_before,
                                    0 terima_repaired_secondary_luar_before,
                                    0 terima_good_secondary_luar_before,
                                    0 terima_repaired_secondary_luar_before_new,
                                    0 terima_good_secondary_luar_before_new
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
                                    GROUP_CONCAT(dc_before_saldo_secondary.stockers) as stockers,
                                    msb.buyer,
                                    msb.ws as act_costing_ws,
                                    msb.styleno as style,
                                    msb.color,
                                    msb.size,
                                    GROUP_CONCAT(dc_before_saldo_secondary.so_det_id) so_det_id,
                                    dc_before_saldo_secondary.panel,
                                    dc_before_saldo_secondary.panel_status,
                                    dc_before_saldo_secondary.part_detail_id,
                                    GROUP_CONCAT(DISTINCT dc_before_saldo_secondary.nama_part) as nama_part,
                                    GROUP_CONCAT(DISTINCT dc_before_saldo_secondary.part_status) as part_status,
                                    0 current_saldo_awal,
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
                                    SUM(terima_good_secondary_luar) as terima_good_secondary_luar_before,
                                    SUM(terima_repaired_secondary_luar_new) as terima_repaired_secondary_luar_before_new,
                                    SUM(terima_good_secondary_luar_new) as terima_good_secondary_luar_before_new
                            from
                                    dc_before_saldo_secondary
                                    left join master_sb_ws msb on msb.id_so_det = dc_before_saldo_secondary.so_det_id
                            GROUP BY
                                    msb.ws,
                                    msb.color,
                                    msb.size,
                                    dc_before_saldo_secondary.part_detail_id
                            HAVING
                                (
                                    kirim_secondary_dalam_before != 0 OR
                                    terima_repaired_secondary_dalam_before != 0 OR
                                    terima_good_secondary_dalam_before != 0 OR
                                    kirim_secondary_luar_before != 0 OR
                                    terima_repaired_secondary_luar_before != 0 OR
                                    terima_good_secondary_luar_before != 0 OR
                                    terima_repaired_secondary_luar_before_new != 0 OR
                                    terima_good_secondary_luar_before_new != 0
                                )
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
                                    0 as terima_good_secondary_luar_before,
                                    0 as terima_repaired_secondary_luar_before_new,
                                    0 as terima_good_secondary_luar_before_new
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
                                    terima_good_secondary_luar_before,
                                    terima_repaired_secondary_luar_before_new,
                                    terima_good_secondary_luar_before_new
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
                    SUM(terima_repaired_secondary_luar_before_new) terima_repaired_secondary_luar_before_new,
                    SUM(terima_good_secondary_luar_before_new) terima_good_secondary_luar_before_new,
                    SUM(qty_adjustment) adjustment,
                    SUM(switching_in) switching_in,
                    SUM(switching_out) switching_out,
                    (SUM(qty_adjustment_before) + SUM(switching_in_before) - SUM(switching_out_before)) + SUM(current_saldo_akhir) + (SUM(qty_adjustment) + SUM(switching_in) - SUM(switching_out)) current_saldo_akhir_adjustment,
                    SUM(qty_adjustment_secondary_dalam_before) qty_adjustment_secondary_dalam_before,
                    SUM(qty_adjustment_secondary_dalam) qty_adjustment_secondary_dalam,
                    SUM(qty_adjustment_secondary_luar_before) qty_adjustment_secondary_luar_before,
                    SUM(qty_adjustment_secondary_luar) qty_adjustment_secondary_luar,
                    SUM(qty_adjustment_transit_terima_secondary_luar_before) qty_adjustment_transit_terima_secondary_luar_before,
                    SUM(qty_adjustment_transit_terima_secondary_luar) qty_adjustment_transit_terima_secondary_luar,
                    SUM(qty_transit_terima_secondary_luar_before) qty_transit_terima_secondary_luar_before,
                    SUM(qty_transit_terima_secondary_luar) qty_transit_terima_secondary_luar
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
                        terima_repaired_secondary_luar_before_new,
                        terima_good_secondary_luar_before_new,
                        0 as qty_adjustment_before,
                        0 qty_adjustment,
                        0 as switching_in_before,
                        0 switching_in,
                        0 as switching_out_before,
                        0 switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        0 as qty_adjustment_before,
                        0 as qty_adjustment,
                        0 as switching_in_before,
                        0 as switching_in,
                        SUM(IF(from_tgl_saldo < '".$dateFrom."',qty,0)) switching_out_before,
                        SUM(IF(from_tgl_saldo >= '".$dateFrom."',qty,0)) as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        0 as qty_adjustment_before,
                        0 as qty_adjustment,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) switching_in_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_secondary_dalam_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) qty_adjustment_secondary_luar_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
                    FROM
                        wip_adjustment
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'DC_SECONDARY_LUAR'
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        SUM(IF(tgl_saldo < '".$dateFrom."',qty,0)) as qty_adjustment_transit_terima_secondary_luar_before,
                        SUM(IF(tgl_saldo >= '".$dateFrom."',qty,0)) as qty_adjustment_transit_terima_secondary_luar,
                        0 as qty_transit_terima_secondary_luar_before,
                        0 as qty_transit_terima_secondary_luar
                    FROM
                        wip_adjustment
                    WHERE
                        tgl_saldo <= '$dateTo' and
                        type_report = 'TERIMA_TRANSIT_SECONDARY_LUAR'
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
                        0 terima_repaired_secondary_luar_before_new,
                        0 terima_good_secondary_luar_before_new,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 switching_in_before,
                        0 as switching_in,
                        0 as switching_out_before,
                        0 as switching_out,
                        0 as qty_adjustment_secondary_dalam_before,
                        0 as qty_adjustment_secondary_dalam,
                        0 as qty_adjustment_secondary_luar_before,
                        0 as qty_adjustment_secondary_luar,
                        0 as qty_adjustment_transit_terima_secondary_luar_before,
                        0 as qty_adjustment_transit_terima_secondary_luar,
                        SUM(IF(tanggal < '".$dateFrom."',qty,0)) as qty_transit_terima_secondary_luar_before,
                        SUM(IF(tanggal >= '".$dateFrom."',qty,0)) as qty_transit_terima_secondary_luar
                    FROM
                        inject_mutasi_dc
                    WHERE
                        tanggal <= '$dateTo' and
                        type_report = 'SECONDARY_LUAR'
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
                        kirim_secondary_dalam_before != 0 OR
                        terima_repaired_secondary_dalam_before != 0 OR
                        terima_good_secondary_dalam_before != 0 OR
                        kirim_secondary_luar_before != 0 OR
                        terima_repaired_secondary_luar_before != 0 OR
                        terima_good_secondary_luar_before != 0 OR
                        terima_repaired_secondary_luar_before_new != 0 OR
                        terima_good_secondary_luar_before_new != 0 OR
                        current_saldo_akhir_adjustment != 0 OR
                        adjustment != 0 OR
                        switching_in != 0 OR
                        switching_out != 0 OR
                        qty_adjustment_secondary_dalam_before != 0 OR
                        qty_adjustment_secondary_dalam != 0 OR
                        qty_adjustment_secondary_luar_before != 0 OR
                        qty_adjustment_secondary_luar != 0 OR
                        qty_adjustment_transit_terima_secondary_luar_before != 0 OR
                        qty_adjustment_transit_terima_secondary_luar != 0 OR
                        qty_transit_terima_secondary_luar_before != 0 OR
                        qty_transit_terima_secondary_luar != 0
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
                            0 terima_repaired_secondary_luar_before_new,
                            0 terima_good_secondary_luar_before_new,
                            0 adjustment,
                            0 switching_in,
                            0 switching_out,
                            0 current_saldo_akhir_adjustment,
                            0 qty_adjustment_secondary_dalam_before,
                            0 qty_adjustment_secondary_dalam,
                            0 qty_adjustment_secondary_luar_before,
                            0 qty_adjustment_secondary_luar,
                            0 qty_adjustment_transit_terima_secondary_luar_before,
                            0 qty_adjustment_transit_terima_secondary_luar,
                            0 qty_transit_terima_secondary_luar_before,
                            0 qty_transit_terima_secondary_luar
                    from
                            dc
                            left join part on part.act_costing_ws = dc.ws and part.panel = dc.panel
                            left join part_detail on part_detail.part_id = part.id
                            left join master_part mp on mp.id = part_detail.master_part_id
                            left join part_custom pcust on pcust.part_id = part.id and pcust.part_detail_id = part_detail.id and pcust.color = dc.color
                    where
                            part.panel_status != 'COMPLEMENT' AND (COALESCE(pcust.set_part_status, part_detail.part_status) != 'complement' OR COALESCE(pcust.set_part_status, part_detail.part_status) IS NULL)
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
                        WHEN '".$dateFrom."' < '2026-06-01' THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_dalam_before)
                                - SUM(terima_repaired_secondary_dalam_before)
                                - SUM(terima_good_secondary_dalam_before)
                                + SUM(qty_adjustment_secondary_dalam_before)
                        )
                END
                ) saldo_awal_secondary_dalam,
                (
                (
                        CASE
                        WHEN '".$dateFrom."' < '2026-06-01' THEN 0
                        ELSE
                                (
                                SUM(kirim_secondary_dalam_before)
                                - SUM(terima_repaired_secondary_dalam_before)
                                - SUM(terima_good_secondary_dalam_before)
                                + SUM(qty_adjustment_secondary_dalam_before)
                                )
                        END
                )
                + SUM(kirim_secondary_dalam)
                - SUM(terima_repaired_secondary_dalam)
                - SUM(terima_good_secondary_dalam)
                + SUM(qty_adjustment_secondary_dalam)
                ) saldo_akhir_secondary_dalam,
                (
                        CASE
                        WHEN '".$dateFrom."' < '2026-06-01' THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_luar_before)
                                - SUM(terima_repaired_secondary_luar_before)
                                - SUM(terima_good_secondary_luar_before)
                                + SUM(qty_adjustment_secondary_luar_before)
                        )
                END
                ) saldo_awal_secondary_luar,
                (
                (
                        CASE
                        WHEN '".$dateFrom."' < '2026-06-01' THEN 0
                        ELSE
                                (
                                SUM(kirim_secondary_luar_before)
                                - SUM(terima_repaired_secondary_luar_before)
                                - SUM(terima_good_secondary_luar_before)
                                + SUM(qty_adjustment_secondary_luar_before)
                                )
                        END
                )
                + SUM(kirim_secondary_luar)
                - SUM(terima_repaired_secondary_luar)
                - SUM(terima_good_secondary_luar)
                + SUM(qty_adjustment_secondary_luar)
                ) saldo_akhir_secondary_luar,
                (
                CASE
                        WHEN '".$dateFrom."' < '2026-06-01' THEN 0
                        ELSE
                        (
                                SUM(kirim_secondary_luar_before)
                                - SUM(qty_transit_terima_secondary_luar_before)
                                + SUM(qty_adjustment_secondary_luar_before)
                                - SUM(terima_repaired_secondary_luar_before)
                                - SUM(terima_good_secondary_luar_before)
                        )
                END
                ) new_saldo_awal_secondary_luar,
                kirim_secondary_luar AS new_terima_dc,
                qty_transit_terima_secondary_luar AS new_kirim_dc,
                qty_adjustment_secondary_luar AS new_qty_adjustment_secondary_luar,
                (
                        (
                                CASE
                                WHEN '".$dateFrom."' < '2026-06-01' THEN 0
                                ELSE (
                                        SUM(kirim_secondary_luar_before)
                                        - SUM(qty_transit_terima_secondary_luar_before)
                                        + SUM(qty_adjustment_secondary_luar_before)
                                        - SUM(terima_repaired_secondary_luar_before)
                                        - SUM(terima_good_secondary_luar_before)
                                )
                                END
                        )
                        + SUM(kirim_secondary_luar)
                        - SUM(qty_transit_terima_secondary_luar)
                        + SUM(qty_adjustment_secondary_luar)
                ) AS new_saldo_akhir_secondary_luar,
                (
                CASE
                        WHEN '".$dateFrom."' <= '2026-07-01' THEN 0
                        ELSE
                        (
                                SUM(qty_transit_terima_secondary_luar_before)
                                - SUM(terima_repaired_secondary_luar_before_new)
                                - SUM(terima_good_secondary_luar_before_new)
                                + SUM(qty_adjustment_transit_terima_secondary_luar_before)
                        )
                END
                ) transit_saldo_awal_secondary_luar,
                qty_transit_terima_secondary_luar AS transit_terima_secondary_luar,
                terima_repaired_secondary_luar AS transit_kirim_rep_secondary_luar,
                terima_good_secondary_luar AS transit_kirim_good_secondary_luar,
                qty_adjustment_transit_terima_secondary_luar AS transit_qty_adjustment_transit_terima_secondary_luar,
                (
                        (
                                CASE
                                WHEN '".$dateFrom."' <= '2026-07-01' THEN 0
                                ELSE (
                                        SUM(qty_transit_terima_secondary_luar_before)
                                        - SUM(terima_repaired_secondary_luar_before_new)
                                        - SUM(terima_good_secondary_luar_before_new)
                                        + SUM(qty_adjustment_transit_terima_secondary_luar_before)
                                )
                                END
                        )
                        + SUM(qty_transit_terima_secondary_luar)
                        - SUM(terima_repaired_secondary_luar)
                        - SUM(terima_good_secondary_luar)
                        + SUM(qty_adjustment_transit_terima_secondary_luar)
                ) AS transit_saldo_akhir_secondary_luar
            FROM (
                select * from dc
                UNION
                select * from form_list
            ) dc
            group by
                ws, color, size, panel, COALESCE(nama_part, '')
            order by
                ws, color, size, panel, COALESCE(nama_part, '')
        ";

        return $query;
    }

    public function runRekap(): array
    {
        try {
            // Only rekap up to 30 days before today, leaving the most recent window untouched
            $dateTo = now()->subDays(30)->toDateString();

            $latestRekap = DB::select("
                SELECT
                    MAX(tanggal) tanggal
                FROM
                    dc_report_rekap
                WHERE
                    tanggal >= '2026-01-01' and
                    tanggal < '".$dateTo."'
            ");

            $dateFrom = $latestRekap[0]->tanggal ?? '2026-01-01';

            if ($dateFrom >= $dateTo) {
                return [
                    'status' => 200,
                    'message' => 'Tidak ada data baru untuk direkap.',
                ];
            }

            // Populate the dc_report_rekap table with aggregated data
            $query = "
                INSERT INTO dc_report_rekap (
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
                        saldo_awal,
                        qty_in,
                        kirim_secondary_dalam,
                        terima_repaired_secondary_dalam,
                        terima_good_secondary_dalam,
                        kirim_secondary_luar,
                        terima_repaired_secondary_luar,
                        terima_good_secondary_luar,
                        loading_qty,
                        saldo_akhir,
                        saldo_awal_secondary_dalam,
                        saldo_akhir_secondary_dalam,
                        saldo_awal_secondary_luar,
                        saldo_akhir_secondary_luar,
                        created_at,
                        updated_at
                )

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
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_part mp on mp.id = pd.master_part_id
                                            where
                                                    a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    a.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_part mp on mp.id = pd.master_part_id
                                            where
                                                    a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    a.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    sii_in.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    sii_in.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    sii.tgl_trans < '".$dateFrom."' AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join wip_out wo on wo.id = wod.id_wip_out
                                            WHERE
                                                    wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    wo.tgl_form < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                    siu.replace sec_in_rep_main,
                                                    null sec_in_rep,
                                                    (0 - COALESCE(siu.reject, 0)) sec_in_out_main,
                                                    null sec_in_out,
                                                    null loading_qty
                                            FROM
                                                    secondary_in_update siu
                                                    left join secondary_in_input si on si.id = siu.secondary_in_id
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    siu.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    pd.part_status= 'main' AND
                                                    COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                            GROUP BY siu.id
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
                                                    siu.replace sec_in_rep,
                                                    null sec_in_out_main,
                                                    (0 - COALESCE(siu.reject, 0)) sec_in_out,
                                                    null loading_qty
                                            FROM
                                                    secondary_in_update siu
                                                    left join secondary_in_input si on si.id = siu.secondary_in_id
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    siu.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                                    COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                            GROUP BY siu.id
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
                                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                            LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                                            LEFT JOIN part p ON p.id = pd.part_id
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                            LEFT JOIN part_custom pcust ON pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                                                            GROUP_CONCAT(ll.stocker_id) stocker_id,
                                                            s.so_det_id,
                                                            MIN(ll.qty) loading_qty
                                                    from
                                                            loading_line ll
                                                            left join stocker_input s on s.id = ll.stocker_id
                                                            left join part_detail pd on pd.id = s.part_detail_id
                                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                            left join part p on p.id = pd.part_id
                                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                            left join part p_com on p_com.id = pd_com.part_id
                                                    where
                                                            ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                            ll.tanggal_loading < '".$dateFrom."' and
                                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                                    group by
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) panel_status,
                                                            pd.id as part_detail_id,
                                                            COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                            COALESCE(GROUP_CONCAT(DISTINCT UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')))) as part_status,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
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
                                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                    left join part p on p.id = pd.part_id
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    dc_rekap
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
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_part mp on mp.id = pd.master_part_id
                                            where
                                                    a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_part mp on mp.id = pd.master_part_id
                                            where
                                                    a.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join part p on p.id = pd.part_id
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join part p on p.id = pd.part_id
                                                    left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii_in.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join wip_out wo on wo.id = wod.id_wip_out
                                            WHERE
                                                    wo.tgl_form between '".$dateFrom."' AND '$dateTo' and
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    si.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                    siu.replace sec_in_rep_main,
                                                    null sec_in_rep,
                                                    (0 - COALESCE(siu.reject, 0)) sec_in_out_main,
                                                    null sec_in_out,
                                                    null loading_qty
                                            FROM
                                                    secondary_in_update siu
                                                    left join secondary_in_input si on si.id = siu.secondary_in_id
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    siu.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    pd.part_status= 'main' AND
                                                    COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                            GROUP BY siu.id
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
                                                    siu.replace sec_in_rep,
                                                    null sec_in_out_main,
                                                    (0 - COALESCE(siu.reject, 0)) sec_in_out,
                                                    null loading_qty
                                            FROM
                                                    secondary_in_update siu
                                                    left join secondary_in_input si on si.id = siu.secondary_in_id
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    siu.tgl_trans between '".$dateFrom."' AND '$dateTo' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                                    COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                            GROUP BY siu.id
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
                                                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = s.color
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
                                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                                                                    GROUP_CONCAT(ll.stocker_id) stocker_id,
                                                                    s.so_det_id,
                                                                    MIN(ll.qty) loading_qty
                                                            from
                                                                    loading_line ll
                                                                    left join stocker_input s on s.id = ll.stocker_id
                                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                                    left join part p_com on p_com.id = pd_com.part_id
                                                            where
                                                                    ll.tanggal_loading between '".$dateFrom."' AND '$dateTo' AND
                                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                                            group by
                                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) panel_status,
                                                    pd.id as part_detail_id,
                                                    COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                    COALESCE(GROUP_CONCAT(DISTINCT UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')))) as part_status,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
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
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            left join part p on p.id = pd.part_id
                                            left join part p_com on p_com.id = pd_com.part_id
                                            LEFT JOIN master_part mp on mp.id = pd.master_part_id
                                            LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                                            GROUP BY
                                                    saldo_dc.so_det_id,
                                                    saldo_dc.part_detail_id
                                    ) saldo_dc
                        ),
                        dc_before_saldo_secondary AS (
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
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
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            where
                                                    a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    a.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            from
                                                    dc_in_input a
                                                    left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                    left join form_cut_input f on f.id = s.form_cut_id
                                                    left join form_cut_reject fr on fr.id = s.form_reject_id
                                                    left join form_cut_piece fp on fp.id = s.form_piece_id
                                                    left join part_detail pd on s.part_detail_id = pd.id
                                                    left join part p on pd.part_id = p.id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join part_detail pdcom on pdcom.id = pd.from_part_detail
                                                    left join part pcom on pcom.id = pdcom.part_id
                                                    left join master_part mp on mp.id = pd.master_part_id
                                            where
                                                    a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    a.tgl_trans < '".$dateFrom."' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_inhouse_in_input sii_in
                                                    left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    sii_in.tgl_trans < '".$dateFrom."' AND
                                                    sii_in.tgl_trans >= '2026-05-01' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    COALESCE(pcust.set_part_status, pd.part_status) = 'main'
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_inhouse_in_input sii_in
                                                    left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    sii_in.tgl_trans < '".$dateFrom."' AND
                                                    sii_in.tgl_trans >= '2026-05-01' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_inhouse_input sii
                                                    left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_inhouse_input sii
                                                    left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                            WHERE
                                                    sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    sii.tgl_trans < '".$dateFrom."' AND
                                                    sii.tgl_trans >= '2026-05-01' AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_in_input si
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_in_input si
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    wip_out_det wod
                                                    left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    wip_out_det wod
                                                    left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join wip_out wo on wo.id = wod.id_wip_out
                                            WHERE
                                                    wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    wo.tgl_form < '".$dateFrom."' AND
                                                    wo.tgl_form >= '2026-05-01' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL)
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
                                                    CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep_main,
                                                    null sec_in_rep,
                                                    CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out_main,
                                                    null sec_in_out,
                                                    null loading_qty,
                                                    CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_in_input si
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep,
                                                    null sec_in_out_main,
                                                    CASE WHEN si.tgl_trans < '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out,
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_replace ELSE NULL END AS sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    CASE WHEN si.tgl_trans >= '2026-07-01' THEN si.qty_in ELSE NULL END AS sec_in_out_new
                                            FROM
                                                    secondary_in_input si
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
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
                                                    CASE WHEN siu.tgl_trans < '2026-07-01' THEN siu.replace ELSE NULL END AS sec_in_rep_main,
                                                    null sec_in_rep,
                                                    CASE WHEN siu.tgl_trans < '2026-07-01' THEN (0 - COALESCE(siu.reject, 0)) ELSE NULL END AS sec_in_out_main,
                                                    null sec_in_out,
                                                    null loading_qty,
                                                    CASE WHEN siu.tgl_trans >= '2026-07-01' THEN siu.replace ELSE NULL END AS sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    CASE WHEN siu.tgl_trans >= '2026-07-01' THEN (0 - COALESCE(siu.reject, 0)) ELSE NULL END AS sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM
                                                    secondary_in_update siu
                                                    left join secondary_in_input si on si.id = siu.secondary_in_id
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    siu.tgl_trans < '".$dateFrom."' AND
                                                    siu.tgl_trans >= '2026-05-01' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    pd.part_status= 'main' AND
                                                    COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                            GROUP BY siu.id
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
                                                    CASE WHEN siu.tgl_trans < '2026-07-01' THEN siu.replace ELSE NULL END AS sec_in_rep,
                                                    null sec_in_out_main,
                                                    CASE WHEN siu.tgl_trans < '2026-07-01' THEN (0 - COALESCE(siu.reject, 0)) ELSE NULL END AS sec_in_out,
                                                    null loading_qty,
                                                    null sec_in_rep_main_new,
                                                    CASE WHEN siu.tgl_trans >= '2026-07-01' THEN siu.replace ELSE NULL END AS sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    CASE WHEN siu.tgl_trans >= '2026-07-01' THEN (0 - COALESCE(siu.reject, 0)) ELSE NULL END AS sec_in_out_new
                                            FROM
                                                    secondary_in_update siu
                                                    left join secondary_in_input si on si.id = siu.secondary_in_id
                                                    left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                                    left join dc_in_input dc on dc.id_qr_stocker = s.id_qr_stocker
                                                    left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                    left join master_secondary ms on ms.id = pd.master_secondary_id
                                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                                    left join master_secondary mms on mms.id = pds.master_secondary_id
                                                    left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                                            WHERE
                                                    siu.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                    siu.tgl_trans < '".$dateFrom."' AND
                                                    siu.tgl_trans >= '2026-05-01' AND
                                                    s.id is not null AND
                                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                                    (COALESCE(pcust.set_part_status, pd.part_status) != 'main' OR COALESCE(pcust.set_part_status, pd.part_status) IS NULL) AND
                                                    COALESCE(mms.tujuan, ms.tujuan, dc.tujuan) = 'SECONDARY LUAR'
                                            GROUP BY siu.id
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
                                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
                                                                            s.form_cut_id,
                                                                            s.form_reject_id,
                                                                            s.form_piece_id,
                                                                            s.so_det_id,
                                                                            s.group_stocker,
                                                                            s.ratio,
                                                                            s.stocker_reject
                                                            ),
                                                            ll.qty
                                                    ) AS loading_qty,
                                                    null sec_in_rep_main_new,
                                                    null sec_in_rep_new,
                                                    null sec_in_out_main_new,
                                                    null sec_in_out_new
                                            FROM loading_line ll
                                            JOIN stocker_input s ON s.id = ll.stocker_id
                                            LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
                                            LEFT JOIN part p ON p.id = pd.part_id
                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                            LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = s.color
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
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) as panel,
                                                            GROUP_CONCAT(ll.stocker_id) stocker_id,
                                                            s.so_det_id,
                                                            MIN(ll.qty) loading_qty
                                                    from
                                                            loading_line ll
                                                            left join stocker_input s on s.id = ll.stocker_id
                                                            left join part_detail pd on pd.id = s.part_detail_id
                                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                        left join part p on p.id = pd.part_id
                                                            left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
                                                            left join part p_com on p_com.id = pd_com.part_id
                                                    where
                                                            ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND
                                                            ll.tanggal_loading < '".$dateFrom."' and
                                                            (s.cancel IS NULL OR s.cancel != 'y') and
                                                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                                    group by
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
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
                                    SUM(terima_repaired_secondary_luar_new) terima_repaired_secondary_luar_new,
                                    SUM(terima_good_secondary_luar_new) terima_good_secondary_luar_new,
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
                                                    terima_repaired_secondary_luar_new,
                                                    terima_good_secondary_luar_new,
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
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel_status, p.panel_status) ELSE p.panel_status END) panel_status,
                                                            pd.id as part_detail_id,
                                                            COALESCE(GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                                            COALESCE(GROUP_CONCAT(DISTINCT UPPER(COALESCE(pcust.set_part_status, pd.part_status, '-')))) as part_status,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main_new, 0)), SUM(COALESCE(sec_in_rep_new,0))) ELSE SUM(COALESCE(sec_in_rep_new,0)) END) terima_repaired_secondary_luar_new,
                                                            (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main_new, 0)), SUM(COALESCE(sec_in_out_new,0))) ELSE SUM(COALESCE(sec_in_out_new, 0)) END) terima_good_secondary_luar_new,
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
                                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail
                                                    left join part p on p.id = pd.part_id
                                                        left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
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
                                                    kirim_secondary_dalam,
                                                    terima_repaired_secondary_dalam,
                                                    terima_good_secondary_dalam,
                                                    kirim_secondary_luar,
                                                    CASE WHEN tanggal < '2026-07-01' THEN terima_repaired_secondary_luar ELSE 0 END AS terima_repaired_secondary_luar,
                                                    CASE WHEN tanggal < '2026-07-01' THEN terima_good_secondary_luar ELSE 0 END AS terima_good_secondary_luar,
                                                    CASE WHEN tanggal >= '2026-07-01' THEN terima_repaired_secondary_luar ELSE 0 END AS terima_repaired_secondary_luar_new,
                                                    CASE WHEN tanggal >= '2026-07-01' THEN terima_good_secondary_luar ELSE 0 END AS terima_good_secondary_luar_new,
                                                    0 loading_qty,
                                                    0 saldo_akhir,
                                                    CURRENT_TIMESTAMP() created_at,
                                                    CURRENT_TIMESTAMP() updated_at
                                            from
                                                    dc_rekap
                                            where
                                                    tanggal < '".$dateFrom."'
                            ) saldo_dc
                            group by
                                    so_det_id,
                                    part_detail_id
                        ),
                        dc_saldo AS (
                                        select
                                                stockers,
                                                buyer,
                                                ws,
                                                style,
                                                UPPER(TRIM(color)) color,
                                                id_so_det,
                                                panel,
                                                panel_status,
                                                part_detail_id,
                                                nama_part,
                                                part_status,
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
                                                (
                                                    CASE WHEN '".$dateFrom."' < '2026-06-01' THEN 0 ELSE (
                                                        SUM(kirim_secondary_dalam_before)
                                                        -
                                                        SUM(terima_repaired_secondary_dalam_before)
                                                        -
                                                        SUM(terima_good_secondary_dalam_before)
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
                                                            )
                                                        END
                                                    )
                                                    +
                                                    SUM(kirim_secondary_dalam)
                                                    -
                                                    SUM(terima_repaired_secondary_dalam)
                                                    -
                                                    SUM(terima_good_secondary_dalam)
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
                                                            )
                                                        END
                                                    )
                                                    +
                                                    SUM(kirim_secondary_luar)
                                                    -
                                                    SUM(terima_repaired_secondary_luar)
                                                    -
                                                    SUM(terima_good_secondary_luar)
                                                ) saldo_akhir_secondary_luar
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
                                                                0 kirim_secondary_dalam_before,
                                                                0 terima_repaired_secondary_dalam_before,
                                                                0 terima_good_secondary_dalam_before,
                                                                0 kirim_secondary_luar_before,
                                                                0 terima_repaired_secondary_luar_before,
                                                                0 terima_good_secondary_luar_before
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
                                                                GROUP_CONCAT(dc_before_saldo_secondary.stockers) as stockers,
                                                                msb.buyer,
                                                                msb.ws as act_costing_ws,
                                                                msb.styleno as style,
                                                                msb.color,
                                                                msb.size,
                                                                GROUP_CONCAT(dc_before_saldo_secondary.so_det_id) so_det_id,
                                                                dc_before_saldo_secondary.panel,
                                                                dc_before_saldo_secondary.panel_status,
                                                                dc_before_saldo_secondary.part_detail_id,
                                                                GROUP_CONCAT(DISTINCT dc_before_saldo_secondary.nama_part) as nama_part,
                                                                GROUP_CONCAT(DISTINCT dc_before_saldo_secondary.part_status) as part_status,
                                                                0 current_saldo_awal,
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
                                                                dc_before_saldo_secondary
                                                                left join master_sb_ws msb on msb.id_so_det = dc_before_saldo_secondary.so_det_id
                                                GROUP BY
                                                                msb.ws,
                                                                msb.color,
                                                                msb.size,
                                                                dc_before_saldo_secondary.part_detail_id
                                                HAVING
                                                                (
                                                                        kirim_secondary_dalam_before != 0 OR
                                                                        terima_repaired_secondary_dalam_before != 0 OR
                                                                        terima_good_secondary_dalam_before != 0 OR
                                                                        kirim_secondary_luar_before != 0 OR
                                                                        terima_repaired_secondary_luar_before != 0 OR
                                                                        terima_good_secondary_luar_before != 0
                                                                )
                                        ) current_saldo
                                        group by
                                                ws,
                                                color,
                                                size,
                                                part_detail_id
                        )

                        select
                            '".$dateTo."',
                            stockers,
                            buyer,
                            ws,
                            color,
                            id_so_det,
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
                            loading_qty,
                            current_saldo_akhir,
                            saldo_awal_secondary_dalam,
                            saldo_akhir_secondary_dalam,
                            saldo_awal_secondary_luar,
                            saldo_akhir_secondary_luar,
                            CURRENT_TIMESTAMP,
                            CURRENT_TIMESTAMP
                        from
                            dc_saldo

                ON DUPLICATE KEY UPDATE
                        stockers = VALUES(stockers),
                        buyer = VALUES(buyer),
                        act_costing_ws = VALUES(act_costing_ws),
                        color = VALUES(color),
                        so_det_id = VALUES(so_det_id),
                        panel = VALUES(panel),
                        panel_status = VALUES(panel_status),
                        part_detail_id = VALUES(part_detail_id),
                        nama_part = VALUES(nama_part),
                        part_status = VALUES(part_status),
                        saldo_awal = VALUES(saldo_awal),
                        qty_in = VALUES(qty_in),
                        kirim_secondary_dalam = VALUES(kirim_secondary_dalam),
                        terima_repaired_secondary_dalam = VALUES(terima_repaired_secondary_dalam),
                        terima_good_secondary_dalam = VALUES(terima_good_secondary_dalam),
                        kirim_secondary_luar = VALUES(kirim_secondary_luar),
                        terima_repaired_secondary_luar = VALUES(terima_repaired_secondary_luar),
                        terima_good_secondary_luar = VALUES(terima_good_secondary_luar),
                        loading_qty = VALUES(loading_qty),
                        saldo_akhir = VALUES(saldo_akhir),
                        saldo_awal_secondary_dalam = VALUES(saldo_awal_secondary_dalam),
                        saldo_akhir_secondary_dalam = VALUES(saldo_akhir_secondary_dalam),
                        saldo_awal_secondary_luar = VALUES(saldo_awal_secondary_luar),
                        saldo_akhir_secondary_luar = VALUES(saldo_akhir_secondary_luar),
                        updated_at = VALUES(updated_at);
            ";

            DB::insert($query);

            Log::channel('rekapDC')->info("Rekap DC berhasil diupdate.");
            Log::channel('rekapDC')->info("Query yang dijalankan: \n" . $query);

            return [
                'status' => 200,
                'message' => 'Rekap DC berhasil diupdate.',
            ];
        } catch (\Exception $e) {
            Log::channel('rekapDC')->error("Error saat mengupdate Rekap DC: " . $e->getMessage());

            return [
                'status' => 400,
                'message' => 'Gagal mengupdate Rekap DC: ' . $e->getMessage(),
            ];
        }
    }
}
