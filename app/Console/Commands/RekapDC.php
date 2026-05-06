<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DB;

class RekapDC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dc:rekap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rekapitulasi Data DC';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Set Current Date as Reference Date
            $refDate = now()->toDateString();

            // Populate the dc_report_rekap table with aggregated data
            DB::insert("
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
                    created_at,
                    updated_at
                )

                WITH dc_before_saldo AS (
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
                                    tanggal < DATE_FORMAT('".$refDate."', '%Y-%m-01')
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
                                a.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                a.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii_in.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii_in.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                wo.tgl_form < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                wo.tgl_form < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                si.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                si.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
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
                                ll.tanggal_loading < DATE_FORMAT('".$refDate."', '%Y-%m-01') and
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
                                        ll.tanggal_loading < DATE_FORMAT('".$refDate."', '%Y-%m-01') and
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
                            '2026-01-31' tanggal,
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
                            tanggal < DATE_FORMAT('".$refDate."', '%Y-%m-01')
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
                                    a.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    a.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii_in.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii_in.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            WHERE
                                    wo.tgl_form between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' and
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
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            WHERE
                                    wo.tgl_form between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' and
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
                                    si.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    si.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                    COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
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
                            ll.tanggal_loading BETWEEN DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                            ll.tanggal_loading between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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

                SELECT
                    '".date('Y-m-d', strtotime($refDate.' -1 day'))."' AS tanggal,
                    stockers,
                    buyer,
                    ws,
                    color,
                    so_det_id,
                    panel,
                    panel_status,
                    part_detail_id,
                    nama_part,
                    part_status,
                    SUM(current_saldo_awal) AS current_saldo_awal,
                    SUM(qty_in) AS qty_in,
                    SUM(kirim_secondary_dalam) AS kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) AS terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) AS terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) AS kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) AS terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) AS terima_good_secondary_luar,
                    SUM(loading) AS loading_qty,
                    SUM(current_saldo_awal) + SUM(current_saldo_akhir) AS current_saldo_akhir,
                    CURRENT_TIMESTAMP() created_at,
                    CURRENT_TIMESTAMP() updated_at
                FROM (
                    SELECT
                        GROUP_CONCAT(dc_current_saldo.stockers) AS stockers,
                        dc_current_saldo.buyer,
                        dc_current_saldo.ws,
                        dc_current_saldo.style,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        GROUP_CONCAT(dc_current_saldo.id_so_det) AS so_det_id,
                        dc_current_saldo.panel,
                        dc_current_saldo.panel_status,
                        dc_current_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.nama_part) AS nama_part,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.part_status) AS part_status,
                        0 AS current_saldo_awal,
                        SUM(dc_current_saldo.qty_in) AS qty_in,
                        SUM(dc_current_saldo.kirim_secondary_dalam) AS kirim_secondary_dalam,
                        SUM(dc_current_saldo.terima_repaired_secondary_dalam) AS terima_repaired_secondary_dalam,
                        SUM(dc_current_saldo.terima_good_secondary_dalam) AS terima_good_secondary_dalam,
                        SUM(dc_current_saldo.kirim_secondary_luar) AS kirim_secondary_luar,
                        SUM(dc_current_saldo.terima_repaired_secondary_luar) AS terima_repaired_secondary_luar,
                        SUM(dc_current_saldo.terima_good_secondary_luar) AS terima_good_secondary_luar,
                        SUM(dc_current_saldo.loading_qty) AS loading,
                        SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) AS current_saldo_akhir
                    FROM dc_current_saldo
                    GROUP BY
                        dc_current_saldo.ws,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        dc_current_saldo.id_so_det,
                        dc_current_saldo.part_detail_id

                    UNION ALL

                    SELECT
                        GROUP_CONCAT(dc_before_saldo.stockers) AS stockers,
                        msb.buyer,
                        msb.ws,
                        msb.styleno AS style,
                        msb.color,
                        msb.size,
                        GROUP_CONCAT(dc_before_saldo.so_det_id) AS so_det_id,
                        dc_before_saldo.panel,
                        dc_before_saldo.panel_status,
                        dc_before_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) AS nama_part,
                        GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) AS part_status,
                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) AS current_saldo_awal,
                        0,0,0,0,0,0,0,0,
                        0 AS current_saldo_akhir
                    FROM dc_before_saldo
                    LEFT JOIN master_sb_ws msb
                        ON msb.id_so_det = dc_before_saldo.so_det_id
                    GROUP BY
                        msb.ws,
                        msb.color,
                        msb.size,
                        dc_before_saldo.so_det_id,
                        dc_before_saldo.part_detail_id
                    HAVING current_saldo_awal != 0
                ) current_saldo
                WHERE
                    ws IS NOT NULL AND
                    color IS NOT NULL AND
                    size IS NOT NULL AND
                    so_det_id IS NOT NULL AND
                    part_detail_id IS NOT NULL
                GROUP BY
                    ws,
                    color,
                    size,
                    so_det_id,
                    part_detail_id

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
                    updated_at = VALUES(updated_at);
            ");

            Log::channel('rekapDC')->info("Rekap DC berhasil diupdate.");
            Log::channel('rekapDC')->info("Query yang dijalankan: \n
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
                    created_at,
                    updated_at
                )

                WITH dc_before_saldo AS (
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
                                    tanggal < DATE_FORMAT('".$refDate."', '%Y-%m-01')
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
                                a.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                a.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii_in.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii_in.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                sii.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                wo.tgl_form < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                wo.tgl_form < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                si.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
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
                                si.tgl_trans < DATE_FORMAT('".$refDate."', '%Y-%m-01') AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
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
                                ll.tanggal_loading < DATE_FORMAT('".$refDate."', '%Y-%m-01') and
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
                                        ll.tanggal_loading < DATE_FORMAT('".$refDate."', '%Y-%m-01') and
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
                            '2026-01-31' tanggal,
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
                            tanggal < DATE_FORMAT('".$refDate."', '%Y-%m-01')
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
                                    a.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    a.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii_in.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii_in.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    sii.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            WHERE
                                    wo.tgl_form between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' and
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
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            WHERE
                                    wo.tgl_form between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' and
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
                                    si.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                    si.tgl_trans between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
                                    s.id is not null AND
                                    (s.cancel IS NULL OR s.cancel != 'y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                    COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
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
                            ll.tanggal_loading BETWEEN DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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
                                            ll.tanggal_loading between DATE_FORMAT('".$refDate."', '%Y-%m-01') AND '".$refDate."' AND
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

                SELECT
                    '".date('Y-m-d', strtotime($refDate.' -1 day'))."' AS tanggal,
                    stockers,
                    buyer,
                    ws,
                    color,
                    so_det_id,
                    panel,
                    panel_status,
                    part_detail_id,
                    nama_part,
                    part_status,
                    SUM(current_saldo_awal) AS current_saldo_awal,
                    SUM(qty_in) AS qty_in,
                    SUM(kirim_secondary_dalam) AS kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) AS terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) AS terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) AS kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) AS terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) AS terima_good_secondary_luar,
                    SUM(loading) AS loading_qty,
                    SUM(current_saldo_awal) + SUM(current_saldo_akhir) AS current_saldo_akhir,
                    CURRENT_TIMESTAMP() created_at,
                    CURRENT_TIMESTAMP() updated_at
                FROM (
                    SELECT
                        GROUP_CONCAT(dc_current_saldo.stockers) AS stockers,
                        dc_current_saldo.buyer,
                        dc_current_saldo.ws,
                        dc_current_saldo.style,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        GROUP_CONCAT(dc_current_saldo.id_so_det) AS so_det_id,
                        dc_current_saldo.panel,
                        dc_current_saldo.panel_status,
                        dc_current_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.nama_part) AS nama_part,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.part_status) AS part_status,
                        0 AS current_saldo_awal,
                        SUM(dc_current_saldo.qty_in) AS qty_in,
                        SUM(dc_current_saldo.kirim_secondary_dalam) AS kirim_secondary_dalam,
                        SUM(dc_current_saldo.terima_repaired_secondary_dalam) AS terima_repaired_secondary_dalam,
                        SUM(dc_current_saldo.terima_good_secondary_dalam) AS terima_good_secondary_dalam,
                        SUM(dc_current_saldo.kirim_secondary_luar) AS kirim_secondary_luar,
                        SUM(dc_current_saldo.terima_repaired_secondary_luar) AS terima_repaired_secondary_luar,
                        SUM(dc_current_saldo.terima_good_secondary_luar) AS terima_good_secondary_luar,
                        SUM(dc_current_saldo.loading_qty) AS loading,
                        SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) AS current_saldo_akhir
                    FROM dc_current_saldo
                    GROUP BY
                        dc_current_saldo.ws,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        dc_current_saldo.id_so_det,
                        dc_current_saldo.part_detail_id

                    UNION ALL

                    SELECT
                        GROUP_CONCAT(dc_before_saldo.stockers) AS stockers,
                        msb.buyer,
                        msb.ws,
                        msb.styleno AS style,
                        msb.color,
                        msb.size,
                        GROUP_CONCAT(dc_before_saldo.so_det_id) AS so_det_id,
                        dc_before_saldo.panel,
                        dc_before_saldo.panel_status,
                        dc_before_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) AS nama_part,
                        GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) AS part_status,
                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) AS current_saldo_awal,
                        0,0,0,0,0,0,0,0,
                        0 AS current_saldo_akhir
                    FROM dc_before_saldo
                    LEFT JOIN master_sb_ws msb
                        ON msb.id_so_det = dc_before_saldo.so_det_id
                    GROUP BY
                        msb.ws,
                        msb.color,
                        msb.size,
                        dc_before_saldo.so_det_id,
                        dc_before_saldo.part_detail_id
                    HAVING current_saldo_awal != 0
                ) current_saldo
                WHERE
                    ws IS NOT NULL AND
                    color IS NOT NULL AND
                    size IS NOT NULL AND
                    so_det_id IS NOT NULL AND
                    part_detail_id IS NOT NULL
                GROUP BY
                    ws,
                    color,
                    size,
                    so_det_id,
                    part_detail_id

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
                    updated_at = VALUES(updated_at);
            ");
        } catch (\Exception $e) {
            Log::channel('rekapDC')->error("Error saat mengupdate Rekap DC: " . $e->getMessage());
        }
    }
}
