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
                        si.qty_in sec_in_out,
                        null loading_qty
                    FROM
                        secondary_in_input si
                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
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
                        COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                ),

                loading_line_qty as (
                    select
                        stocker_input.id_qr_stocker,
                        part_detail.id as part_detail_id,
                        stocker_input.so_det_id,
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
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        COALESCE(loading_qty.loading_qty, loading_line.qty) loading_qty
                    from
                        loading_line
                        LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                        left join part_detail on stocker_input.part_detail_id = part_detail.id
                        left join part on part.id = part_detail.part_id
                        left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                        left join part part_com on part_com.id = part_detail_com.part_id
                        LEFT JOIN (
                            select
                                COALESCE(p_com.panel, p.panel) as panel,
                                s.form_cut_id,
                                s.form_reject_id,
                                s.form_piece_id,
                                s.so_det_id,
                                s.group_stocker,
                                s.ratio,
                                s.stocker_reject,
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
                        ) as loading_qty on loading_qty.panel = COALESCE(part_com.panel, part.panel)
                        AND loading_qty.form_cut_id    <=> stocker_input.form_cut_id
                        AND loading_qty.form_reject_id <=> stocker_input.form_reject_id
                        AND loading_qty.form_piece_id  <=> stocker_input.form_piece_id
                        AND loading_qty.so_det_id      <=> stocker_input.so_det_id
                        AND loading_qty.group_stocker  <=> stocker_input.group_stocker
                        AND loading_qty.ratio          <=> stocker_input.ratio
                        AND loading_qty.stocker_reject <=> stocker_input.stocker_reject
                    WHERE
                        loading_line.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND loading_line.tanggal_loading < '".$dateFrom."' and
                        (stocker_input.cancel IS NULL OR stocker_input.cancel != 'y') and
                        (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%STOCKER MANUAL%')
                    group by
                        stocker_input.id
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
                            '".date('Y-m-d',strtotime($dateFrom.' -1 day'))."' tanggal,
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
                                COALESCE(pd_com.id, pd.id) as part_detail_id,
                                COALESCE(GROUP_CONCAT(DISTINCT mp_com.nama_part), GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                COALESCE(GROUP_CONCAT(DISTINCT pd_com.part_status), GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
                            FROM
                                wip_out_det wod
                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join wip_out wo on wo.id = wod.id_wip_out
                            WHERE
                                wo.tgl_form between '".$dateFrom."' AND '".$dateTo."' and
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
                                null sec_in_out,
                                null loading_qty
                            FROM
                                wip_out_det wod
                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join wip_out wo on wo.id = wod.id_wip_out
                            WHERE
                                wo.tgl_form between '".$dateFrom."' AND '".$dateTo."' and
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
                                si.qty_in sec_in_out,
                                null loading_qty
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

                        loading_line_qty as (
                            select
                                stocker_input.id_qr_stocker,
                                part_detail.id as part_detail_id,
                                stocker_input.so_det_id,
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
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                COALESCE(loading_qty.loading_qty, loading_line.qty) loading_qty
                            from
                                loading_line
                                LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                                left join part_detail on stocker_input.part_detail_id = part_detail.id
                                left join part on part.id = part_detail.part_id
                                left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                                left join part part_com on part_com.id = part_detail_com.part_id
                                LEFT JOIN (
                                    select
                                        COALESCE(p_com.panel, p.panel) as panel,
                                        s.form_cut_id,
                                        s.form_reject_id,
                                        s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject,
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
                                ) as loading_qty on loading_qty.panel = COALESCE(part_com.panel, part.panel)
                            AND loading_qty.form_cut_id    <=> stocker_input.form_cut_id
                            AND loading_qty.form_reject_id <=> stocker_input.form_reject_id
                            AND loading_qty.form_piece_id  <=> stocker_input.form_piece_id
                            AND loading_qty.so_det_id      <=> stocker_input.so_det_id
                            AND loading_qty.group_stocker  <=> stocker_input.group_stocker
                            AND loading_qty.ratio          <=> stocker_input.ratio
                            AND loading_qty.stocker_reject <=> stocker_input.stocker_reject
                            WHERE loading_line.tanggal_loading between '".$dateFrom."' AND '".$dateTo."' and
                            (stocker_input.cancel IS NULL OR stocker_input.cancel != 'y') and
                            (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%STOCKER MANUAL%')
                            group by
                                stocker_input.id
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
                                COALESCE(p_com.panel, p.panel) panel,
                                COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                COALESCE(pd_com.id, pd.id) as part_detail_id,
                                COALESCE(GROUP_CONCAT(DISTINCT mp_com.nama_part), GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                COALESCE(GROUP_CONCAT(DISTINCT pd_com.part_status), GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
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
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
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
                ) current_saldo
                group by
                    ws,
                    color,
                    size,
                    part_detail_id
            ");

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report', [
            "page" => "dashboard-dc"
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
                        si.qty_in sec_in_out,
                        null loading_qty
                    FROM
                        secondary_in_input si
                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
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
                        COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                ),

                loading_line_qty as (
                    select
                        stocker_input.id_qr_stocker,
                        part_detail.id as part_detail_id,
                        stocker_input.so_det_id,
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
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        COALESCE(loading_qty.loading_qty, loading_line.qty) loading_qty
                    from
                        loading_line
                        LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                        left join part_detail on stocker_input.part_detail_id = part_detail.id
                        left join part on part.id = part_detail.part_id
                        left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                        left join part part_com on part_com.id = part_detail_com.part_id
                        LEFT JOIN (
                            select
                                COALESCE(p_com.panel, p.panel) as panel,
                                s.form_cut_id,
                                s.form_reject_id,
                                s.form_piece_id,
                                s.so_det_id,
                                s.group_stocker,
                                s.ratio,
                                s.stocker_reject,
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
                        ) as loading_qty on loading_qty.panel = COALESCE(part_com.panel, part.panel)
                        AND loading_qty.form_cut_id    <=> stocker_input.form_cut_id
                        AND loading_qty.form_reject_id <=> stocker_input.form_reject_id
                        AND loading_qty.form_piece_id  <=> stocker_input.form_piece_id
                        AND loading_qty.so_det_id      <=> stocker_input.so_det_id
                        AND loading_qty.group_stocker  <=> stocker_input.group_stocker
                        AND loading_qty.ratio          <=> stocker_input.ratio
                        AND loading_qty.stocker_reject <=> stocker_input.stocker_reject
                    WHERE
                        loading_line.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2026-01-01') AND loading_line.tanggal_loading < '".$dateFrom."' and
                        (stocker_input.cancel IS NULL OR stocker_input.cancel != 'y') and
                        (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%STOCKER MANUAL%')
                    group by
                        stocker_input.id
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
                            '".date('Y-m-d',strtotime($dateFrom.' -1 day'))."' tanggal,
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
                                COALESCE(pd_com.id, pd.id) as part_detail_id,
                                COALESCE(GROUP_CONCAT(DISTINCT mp_com.nama_part), GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                COALESCE(GROUP_CONCAT(DISTINCT pd_com.part_status), GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
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
                                null sec_in_out,
                                null loading_qty
                            FROM
                                wip_out_det wod
                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join wip_out wo on wo.id = wod.id_wip_out
                            WHERE
                                wo.tgl_form between '".$dateFrom."' AND '".$dateTo."' and
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
                                null sec_in_out,
                                null loading_qty
                            FROM
                                wip_out_det wod
                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join wip_out wo on wo.id = wod.id_wip_out
                            WHERE
                                wo.tgl_form between '".$dateFrom."' AND '".$dateTo."' and
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
                                si.qty_in sec_in_out,
                                null loading_qty
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

                        loading_line_qty as (
                            select
                                stocker_input.id_qr_stocker,
                                part_detail.id as part_detail_id,
                                stocker_input.so_det_id,
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
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                COALESCE(loading_qty.loading_qty, loading_line.qty) loading_qty
                            from
                                loading_line
                                LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                                left join part_detail on stocker_input.part_detail_id = part_detail.id
                                left join part on part.id = part_detail.part_id
                                left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                                left join part part_com on part_com.id = part_detail_com.part_id
                                LEFT JOIN (
                                    select
                                        COALESCE(p_com.panel, p.panel) as panel,
                                        s.form_cut_id,
                                        s.form_reject_id,
                                        s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject,
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
                                ) as loading_qty on loading_qty.panel = COALESCE(part_com.panel, part.panel)
                            AND loading_qty.form_cut_id    <=> stocker_input.form_cut_id
                            AND loading_qty.form_reject_id <=> stocker_input.form_reject_id
                            AND loading_qty.form_piece_id  <=> stocker_input.form_piece_id
                            AND loading_qty.so_det_id      <=> stocker_input.so_det_id
                            AND loading_qty.group_stocker  <=> stocker_input.group_stocker
                            AND loading_qty.ratio          <=> stocker_input.ratio
                            AND loading_qty.stocker_reject <=> stocker_input.stocker_reject
                            WHERE loading_line.tanggal_loading between '".$dateFrom."' AND '".$dateTo."' and
                            (stocker_input.cancel IS NULL OR stocker_input.cancel != 'y') and
                            (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%STOCKER MANUAL%')
                            group by
                                stocker_input.id
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
                                COALESCE(p_com.panel, p.panel) panel,
                                COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                COALESCE(pd_com.id, pd.id) as part_detail_id,
                                COALESCE(GROUP_CONCAT(DISTINCT mp_com.nama_part), GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                COALESCE(GROUP_CONCAT(DISTINCT pd_com.part_status), GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
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
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
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
                ) current_saldo
                group by
                    ws,
                    color,
                    size,
                    part_detail_id
        ");

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Laporan DC');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'LAPORAN DC', ['font-size' => 16, 'font-bold' => true]);
        $sheet->mergeCells('A1:P1');

        // Period
        $sheet->writeTo('A2', 'Periode : ' . $from . ' s/d ' . $to);
        $sheet->mergeCells('A2:P2');

        // Headers
        $sheet->writeTo('A4', 'No. WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B4', 'Buyer')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C4', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D4', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E4', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F4', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G4', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H4', 'Saldo Awal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I4', 'Masuk')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J4', 'Kirim Sec Dalam')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K4', 'Terima Rep Sec Dalam')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L4', 'Terima Good Sec Dalam')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M4', 'Kirim Sec Luar')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N4', 'Terima Rep Sec Luar')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O4', 'Terima Good Sec Luar')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P4', 'Loading')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q4', 'Saldo Akhir')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Data rows - chunk by 10,000
        $row = 5;
        $totalSaldoAwal = 0;
        $totalMasuk = 0;
        $totalKirimSecDalam = 0;
        $totalTerimaRepairedSecDalam = 0;
        $totalTerimaGoodSecDalam = 0;
        $totalKirimSecLuar = 0;
        $totalTerimaRepairedSecLuar = 0;
        $totalTerimaGoodSecLuar = 0;
        $totalLoading = 0;
        $totalSaldoAkhir = 0;

        collect($dataReport)->chunk(10000)->each(function ($chunk) use ($sheet, &$row, &$totalSaldoAwal, &$totalMasuk, &$totalKirimSecDalam, &$totalTerimaRepairedSecDalam, &$totalTerimaGoodSecDalam, &$totalKirimSecLuar, &$totalTerimaRepairedSecLuar, &$totalTerimaGoodSecLuar, &$totalLoading, &$totalSaldoAkhir) {
            foreach ($chunk as $data) {
                $saldoAwal = $data->current_saldo_awal ?? 0;
                $masuk = $data->qty_in ?? 0;
                $kirimDalam = $data->kirim_secondary_dalam ?? 0;
                $repDalam = $data->terima_repaired_secondary_dalam ?? 0;
                $goodDalam = $data->terima_good_secondary_dalam ?? 0;
                $kirimLuar = $data->kirim_secondary_luar ?? 0;
                $repLuar = $data->terima_repaired_secondary_luar ?? 0;
                $goodLuar = $data->terima_good_secondary_luar ?? 0;
                $loading = $data->loading_qty ?? 0;

                $saldoAkhir = $saldoAwal + $masuk - $kirimDalam + $repDalam + $goodDalam - $kirimLuar + $repLuar + $goodLuar - $loading;

                $totalSaldoAwal += $saldoAwal;
                $totalMasuk += $masuk;
                $totalKirimSecDalam += $kirimDalam;
                $totalTerimaRepairedSecDalam += $repDalam;
                $totalTerimaGoodSecDalam += $goodDalam;
                $totalKirimSecLuar += $kirimLuar;
                $totalTerimaRepairedSecLuar += $repLuar;
                $totalTerimaGoodSecLuar += $goodLuar;
                $totalLoading += $loading;
                $totalSaldoAkhir += $saldoAkhir;

                $sheet->writeTo('A' . $row, $data->ws)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('B' . $row, $data->buyer)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('C' . $row, $data->style)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('D' . $row, $data->color)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('E' . $row, $data->size)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('F' . $row, $data->panel ?? '-')->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('G' . $row, $data->nama_part)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('H' . $row, $saldoAwal)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('I' . $row, $masuk)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('J' . $row, $kirimDalam)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('K' . $row, $repDalam)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('L' . $row, $goodDalam)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('M' . $row, $kirimLuar)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('N' . $row, $repLuar)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('O' . $row, $goodLuar)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('P' . $row, $loading)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;
                $sheet->writeTo('Q' . $row, $saldoAkhir)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);;

                $row++;
            }
        });

        // Total row
        $sheet->writeTo('A' . $row, 'TOTAL')->applyFontStyleBold();
        $sheet->writeTo('H' . $row, $totalSaldoAwal)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I' . $row, $totalMasuk)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J' . $row, $totalKirimSecDalam)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K' . $row, $totalTerimaRepairedSecDalam)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L' . $row, $totalTerimaGoodSecDalam)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M' . $row, $totalKirimSecLuar)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N' . $row, $totalTerimaRepairedSecLuar)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O' . $row, $totalTerimaGoodSecLuar)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P' . $row, $totalLoading)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q' . $row, $totalSaldoAkhir)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->mergeCells('A'.$row.':G'.$row.'')->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        return $excel->download('Laporan DC ' . $from . ' - ' . $to . '.xlsx');
    }
}
