<?php

namespace App\Exports\Cutting;

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


class export_excel_report_mut_wip_cutting_detail implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $rowCount;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function view(): View
    {

        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $tgl_saldo = '2026-03-01';

        $rawData = DB::select("WITH
                    cutt_awal as (
                            SELECT
                                COALESCE( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) tanggal,
                                UPPER( meja.`name` ) meja,
                                marker_input.act_costing_ws worksheet,
                                marker_input.buyer,
                                marker_input.style,
                                marker_input.color,
                                master_sb_ws.id_so_det,
                                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE marker_input_detail.size END ) size,
                                form_cut_input_detail.group_roll,
                                form_cut_input_detail.lot,
                                form_cut_input.no_cut,
                                form_cut_input.no_form,
                                marker_input.kode no_marker,
                                marker_input.panel,
                                similar.max_group,
                                form_cut_input_detail.group_stocker,
                                COALESCE ( modify_size_qty.difference_qty, 0 ),
                                COALESCE ( modify_size_qty.modified_qty, 0 ),
                                ((COALESCE ( marker_input_detail.ratio, 0 ) * COALESCE ( form_cut_input_detail.total_lembar, 0 )) + (COALESCE ( modify_size_qty.difference_qty, 0 ))) qty,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.id, part.id ) ELSE part.id END ) part_id1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel_status, part.panel_status ) ELSE part.panel_status END ) panel_status1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel, part.panel ) ELSE part.panel END ) part_panel1,
                                part.id as part_id,
                                part.panel_status panel_status,
                                part.panel part_panel,
                                part_detail.id part_detail_id,
                                part_detail.part_status part_status,
                                master_part.id master_part_id,
                                master_part.nama_part
                            FROM
                                form_cut_input
                                LEFT JOIN (
                                    SELECT
                                        form_cut_id,
                                        no_form_cut_input,
                                        group_roll,
                                        group_stocker,
                                        lot,
                                        SUM( lembar_gelaran ) total_lembar
                                    FROM
                                        form_cut_input_detail
                                    WHERE
                                        ( STATUS != 'not complete' AND STATUS != 'extension' )
                                    GROUP BY
                                        form_cut_id,
                                        group_stocker
                                ) form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                LEFT JOIN ( SELECT form_cut_id, MAX( group_stocker ) max_group FROM form_cut_input_detail WHERE ( STATUS != 'not complete' AND STATUS != 'extension' ) GROUP BY form_cut_id ) similar ON similar.form_cut_id = form_cut_input_detail.form_cut_id
                                LEFT JOIN users AS meja ON meja.id = form_cut_input.no_meja
                                LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                                LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                                LEFT JOIN modify_size_qty ON modify_size_qty.form_cut_id = form_cut_input.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id AND form_cut_input_detail.group_stocker = COALESCE ( modify_size_qty.group_stocker, similar.max_group )
                                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = marker_input_detail.so_det_id
                                LEFT JOIN part_form ON part_form.form_id = form_cut_input.id
                                LEFT JOIN part ON part.act_costing_ws = marker_input.act_costing_ws and part.panel = marker_input.panel
                                LEFT JOIN part_detail ON part_detail.part_id = part.id
                                LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail AND part_detail.part_status = 'complement'
                                LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            WHERE
                                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) >= '$tgl_saldo'
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) < '$start_date'
                                AND ( marker_input_detail.ratio > 0 OR ( similar.max_group = form_cut_input_detail.group_stocker AND modify_size_qty.difference_qty > 0 ))
                                AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            GROUP BY
                                form_cut_input.id,
                                form_cut_input_detail.group_stocker,
                                marker_input_detail.id,
                                part_detail_id
                        UNION ALL
                            SELECT
                                COALESCE(DATE(form_cut_piece.tanggal), DATE(form_cut_piece.created_at), DATE(form_cut_piece.updated_at)) tanggal,
                                '-' meja,
                                form_cut_piece.act_costing_ws worksheet,
                                form_cut_piece.buyer,
                                form_cut_piece.style,
                                form_cut_piece.color,
                                master_sb_ws.id_so_det,
                                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE form_cut_piece_detail_size.size  END ) size,
                                form_cut_piece_detail.`group_roll`,
                                form_cut_piece_detail.lot,
                                form_cut_piece.no_cut,
                                form_cut_piece.no_form,
                                '-' no_marker,
                                form_cut_piece.panel,
                                '-' max_group,
                                form_cut_piece_detail.group_stocker,
                                NULL,
                                NULL,
                                SUM( form_cut_piece_detail_size.qty ) AS qty,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.id, part.id ) ELSE part.id END ) part_id1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel_status, part.panel_status ) ELSE part.panel_status END ) panel_status1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel, part.panel ) ELSE part.panel END ) part_panel1,
                                part.id as part_id,
                                part.panel_status panel_status,
                                part.panel part_panel,
                                part_detail.id part_detail_id,
                                part_detail.part_status part_status,
                                master_part.id master_part_id,
                                master_part.nama_part
                            FROM
                                form_cut_piece
                                LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                                LEFT JOIN form_cut_piece_detail_size ON form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                                LEFT JOIN part_form ON part_form.form_pcs_id = form_cut_piece.id
                                LEFT JOIN part ON part.act_costing_ws = form_cut_piece.act_costing_ws and part.panel = form_cut_piece.panel
                                LEFT JOIN part_detail ON part_detail.part_id = part.id
                                LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail AND part_detail.part_status = 'complement'
                                LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            WHERE
                                COALESCE(DATE(form_cut_piece.tanggal), DATE(form_cut_piece.created_at), DATE(form_cut_piece.updated_at)) >= '$tgl_saldo'
                                AND COALESCE(DATE(form_cut_piece.tanggal), DATE(form_cut_piece.created_at), DATE(form_cut_piece.updated_at)) < '$start_date'
                                AND form_cut_piece_detail.STATUS = 'complete'
                                AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            GROUP BY
                                form_cut_piece.id,
                                form_cut_piece_detail.group_stocker,
                                form_cut_piece_detail_size.id,
                                part_detail.id
                        UNION ALL
                            SELECT
                                COALESCE( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) tanggal,
                                UPPER( meja.`name` ) meja,
                                stocker_ws_additional.act_costing_ws worksheet,
                                stocker_ws_additional.buyer,
                                stocker_ws_additional.style,
                                stocker_ws_additional.color,
                                master_sb_ws.id_so_det,
                                ( CASE WHEN master_sb_ws.dest IS NOT NULL  AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE stocker_ws_additional_detail.size  END  ) size,
                                form_cut_input_detail.group_roll,
                                form_cut_input_detail.lot,
                                form_cut_input.no_cut,
                                form_cut_input.no_form,
                                '-' no_marker,
                                stocker_ws_additional.panel,
                                similar.max_group,
                                form_cut_input_detail.group_stocker,
                                COALESCE ( modify_size_qty.difference_qty, 0 ),
                                COALESCE ( modify_size_qty.modified_qty, 0 ),
                                (( COALESCE ( stocker_ws_additional_detail.ratio, 0 ) * COALESCE ( form_cut_input_detail.total_lembar, 0 )) + ( COALESCE ( modify_size_qty.difference_qty, 0 ))) qty,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.id, part.id ) ELSE part.id END ) part_id1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel_status, part.panel_status ) ELSE part.panel_status END ) panel_status1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel, part.panel ) ELSE part.panel END ) part_panel1,
                                part.id as part_id,
                                part.panel_status panel_status,
                                part.panel part_panel,
                                part_detail.id part_detail_id,
                                part_detail.part_status part_status,
                                master_part.id master_part_id,
                                master_part.nama_part
                            FROM
                                laravel_nds.form_cut_input
                                LEFT JOIN (
                                    SELECT
                                        form_cut_id,
                                        no_form_cut_input,
                                        group_roll,
                                        group_stocker,
                                        lot,
                                        SUM( lembar_gelaran ) total_lembar
                                    FROM
                                        form_cut_input_detail
                                    WHERE
                                        ( STATUS != 'not complete' AND STATUS != 'extension' )
                                    GROUP BY
                                        form_cut_id,
                                        group_stocker
                                ) form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                LEFT JOIN ( SELECT form_cut_id, MAX( group_stocker ) max_group FROM form_cut_input_detail WHERE ( STATUS != 'not complete' AND STATUS != 'extension' ) GROUP BY form_cut_id ) similar ON similar.form_cut_id = form_cut_input_detail.form_cut_id
                                LEFT JOIN laravel_nds.stocker_ws_additional ON stocker_ws_additional.form_cut_id = form_cut_input.id
                                LEFT JOIN laravel_nds.stocker_ws_additional_detail ON stocker_ws_additional_detail.stocker_additional_id = stocker_ws_additional.id
                                LEFT JOIN laravel_nds.master_sb_ws ON master_sb_ws.id_so_det = stocker_ws_additional_detail.so_det_id
                                LEFT JOIN laravel_nds.users AS meja ON meja.id = form_cut_input.no_meja
                                LEFT JOIN laravel_nds.modify_size_qty ON modify_size_qty.so_det_id = stocker_ws_additional_detail.so_det_id
                                AND modify_size_qty.form_cut_id = form_cut_input.id
                                LEFT JOIN part_form ON part_form.form_id = form_cut_input.id
                                LEFT JOIN part ON part.act_costing_ws = stocker_ws_additional.act_costing_ws and part.panel = stocker_ws_additional.panel
                                LEFT JOIN part_detail ON part_detail.part_id = part.id
                                LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail AND part_detail.part_status = 'complement'
                                LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            WHERE
                                form_cut_input.STATUS = 'SELESAI PENGERJAAN'
                                AND ( stocker_ws_additional_detail.ratio > 0 OR modify_size_qty.difference_qty != 0 )
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) >= '$tgl_saldo'
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) < '$start_date'
                                AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            GROUP BY
                                form_cut_input.id,
                                form_cut_input_detail.group_stocker,
                                stocker_ws_additional_detail.id,
                                part_detail.id
                            ORDER BY
                                tanggal DESC,
                                meja,
                                worksheet,
                                style,
                                color,
                                panel,
                                part_detail_id,
                                id_so_det,
                                group_stocker
                    ),
                    cutt_in as (
                            SELECT
                                COALESCE( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) tanggal,
                                UPPER( meja.`name` ) meja,
                                marker_input.act_costing_ws worksheet,
                                marker_input.buyer,
                                marker_input.style,
                                marker_input.color,
                                master_sb_ws.id_so_det,
                                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE marker_input_detail.size END ) size,
                                form_cut_input_detail.group_roll,
                                form_cut_input_detail.lot,
                                form_cut_input.no_cut,
                                form_cut_input.no_form,
                                marker_input.kode no_marker,
                                marker_input.panel,
                                similar.max_group,
                                form_cut_input_detail.group_stocker,
                                COALESCE ( modify_size_qty.difference_qty, 0 ),
                                COALESCE ( modify_size_qty.modified_qty, 0 ),
                                ((COALESCE ( marker_input_detail.ratio, 0 ) * COALESCE ( form_cut_input_detail.total_lembar, 0 )) + (COALESCE ( modify_size_qty.difference_qty, 0 ))) qty,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.id, part.id ) ELSE part.id END ) part_id1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel_status, part.panel_status ) ELSE part.panel_status END ) panel_status1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel, part.panel ) ELSE part.panel END ) part_panel1,
                                part.id as part_id,
                                part.panel_status panel_status,
                                part.panel part_panel,
                                part_detail.id part_detail_id,
                                part_detail.part_status part_status,
                                master_part.id master_part_id,
                                master_part.nama_part
                            FROM
                                form_cut_input
                                LEFT JOIN (
                                    SELECT
                                        form_cut_id,
                                        no_form_cut_input,
                                        group_roll,
                                        group_stocker,
                                        lot,
                                        SUM( lembar_gelaran ) total_lembar
                                    FROM
                                        form_cut_input_detail
                                    WHERE
                                        ( STATUS != 'not complete' AND STATUS != 'extension' )
                                    GROUP BY
                                        form_cut_id,
                                        group_stocker
                                ) form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                LEFT JOIN ( SELECT form_cut_id, MAX( group_stocker ) max_group FROM form_cut_input_detail WHERE ( STATUS != 'not complete' AND STATUS != 'extension' ) GROUP BY form_cut_id ) similar ON similar.form_cut_id = form_cut_input_detail.form_cut_id
                                LEFT JOIN users AS meja ON meja.id = form_cut_input.no_meja
                                LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                                LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                                LEFT JOIN modify_size_qty ON modify_size_qty.form_cut_id = form_cut_input.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id AND form_cut_input_detail.group_stocker = COALESCE ( modify_size_qty.group_stocker, similar.max_group )
                                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = marker_input_detail.so_det_id
                                LEFT JOIN part_form ON part_form.form_id = form_cut_input.id
                                LEFT JOIN part ON part.act_costing_ws = marker_input.act_costing_ws and part.panel = marker_input.panel
                                LEFT JOIN part_detail ON part_detail.part_id = part.id
                                LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail AND part_detail.part_status = 'complement'
                                LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            WHERE
                                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) >= '$start_date'
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) <= '$end_date'
                                AND ( marker_input_detail.ratio > 0 OR ( similar.max_group = form_cut_input_detail.group_stocker AND modify_size_qty.difference_qty > 0 ))
                                AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            GROUP BY
                                form_cut_input.id,
                                form_cut_input_detail.group_stocker,
                                marker_input_detail.id,
                                part_detail_id
                        UNION ALL
                            SELECT
                                COALESCE(DATE(form_cut_piece.tanggal), DATE(form_cut_piece.created_at), DATE(form_cut_piece.updated_at)) tanggal,
                                '-' meja,
                                form_cut_piece.act_costing_ws worksheet,
                                form_cut_piece.buyer,
                                form_cut_piece.style,
                                form_cut_piece.color,
                                master_sb_ws.id_so_det,
                                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE form_cut_piece_detail_size.size  END ) size,
                                form_cut_piece_detail.`group_roll`,
                                form_cut_piece_detail.lot,
                                form_cut_piece.no_cut,
                                form_cut_piece.no_form,
                                '-' no_marker,
                                form_cut_piece.panel,
                                '-' max_group,
                                form_cut_piece_detail.group_stocker,
                                NULL,
                                NULL,
                                SUM( form_cut_piece_detail_size.qty ) AS qty,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.id, part.id ) ELSE part.id END ) part_id1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel_status, part.panel_status ) ELSE part.panel_status END ) panel_status1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel, part.panel ) ELSE part.panel END ) part_panel1,
                                part.id as part_id,
                                part.panel_status panel_status,
                                part.panel part_panel,
                                part_detail.id part_detail_id,
                                part_detail.part_status part_status,
                                master_part.id master_part_id,
                                master_part.nama_part
                            FROM
                                form_cut_piece
                                LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                                LEFT JOIN form_cut_piece_detail_size ON form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                                LEFT JOIN part_form ON part_form.form_pcs_id = form_cut_piece.id
                                LEFT JOIN part ON part.act_costing_ws = form_cut_piece.act_costing_ws and part.panel = form_cut_piece.panel
                                LEFT JOIN part_detail ON part_detail.part_id = part.id
                                LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail
                                AND part_detail.part_status = 'complement'
                                LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            WHERE
                                COALESCE(DATE(form_cut_piece.tanggal), DATE(form_cut_piece.created_at), DATE(form_cut_piece.updated_at)) >= '$start_date'
                                AND COALESCE(DATE(form_cut_piece.tanggal), DATE(form_cut_piece.created_at), DATE(form_cut_piece.updated_at)) <= '$end_date'
                                AND form_cut_piece_detail.STATUS = 'complete'
                                AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            GROUP BY
                                form_cut_piece.id,
                                form_cut_piece_detail.group_stocker,
                                form_cut_piece_detail_size.id,
                                part_detail.id
                        UNION ALL
                            SELECT
                                COALESCE( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) tanggal,
                                UPPER( meja.`name` ) meja,
                                stocker_ws_additional.act_costing_ws worksheet,
                                stocker_ws_additional.buyer,
                                stocker_ws_additional.style,
                                stocker_ws_additional.color,
                                master_sb_ws.id_so_det,
                                ( CASE WHEN master_sb_ws.dest IS NOT NULL  AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE stocker_ws_additional_detail.size  END  ) size,
                                form_cut_input_detail.group_roll,
                                form_cut_input_detail.lot,
                                form_cut_input.no_cut,
                                form_cut_input.no_form,
                                '-' no_marker,
                                stocker_ws_additional.panel,
                                similar.max_group,
                                form_cut_input_detail.group_stocker,
                                COALESCE ( modify_size_qty.difference_qty, 0 ),
                                COALESCE ( modify_size_qty.modified_qty, 0 ),
                                (( COALESCE ( stocker_ws_additional_detail.ratio, 0 ) * COALESCE ( form_cut_input_detail.total_lembar, 0 )) + ( COALESCE ( modify_size_qty.difference_qty, 0 ))) qty,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.id, part.id ) ELSE part.id END ) part_id1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel_status, part.panel_status ) ELSE part.panel_status END ) panel_status1,
                                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( p_com.panel, part.panel ) ELSE part.panel END ) part_panel1,
                                part.id as part_id,
                                part.panel_status panel_status,
                                part.panel part_panel,
                                part_detail.id part_detail_id,
                                part_detail.part_status part_status,
                                master_part.id master_part_id,
                                master_part.nama_part
                            FROM
                                laravel_nds.form_cut_input
                                LEFT JOIN (
                                    SELECT
                                        form_cut_id,
                                        no_form_cut_input,
                                        group_roll,
                                        group_stocker,
                                        lot,
                                        SUM( lembar_gelaran ) total_lembar
                                    FROM
                                        form_cut_input_detail
                                    WHERE
                                        ( STATUS != 'not complete' AND STATUS != 'extension' )
                                    GROUP BY
                                        form_cut_id,
                                        group_stocker
                                ) form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                LEFT JOIN ( SELECT form_cut_id, MAX( group_stocker ) max_group FROM form_cut_input_detail WHERE ( STATUS != 'not complete' AND STATUS != 'extension' ) GROUP BY form_cut_id ) similar ON similar.form_cut_id = form_cut_input_detail.form_cut_id
                                LEFT JOIN laravel_nds.stocker_ws_additional ON stocker_ws_additional.form_cut_id = form_cut_input.id
                                LEFT JOIN laravel_nds.stocker_ws_additional_detail ON stocker_ws_additional_detail.stocker_additional_id = stocker_ws_additional.id
                                LEFT JOIN laravel_nds.master_sb_ws ON master_sb_ws.id_so_det = stocker_ws_additional_detail.so_det_id
                                LEFT JOIN laravel_nds.users AS meja ON meja.id = form_cut_input.no_meja
                                LEFT JOIN laravel_nds.modify_size_qty ON modify_size_qty.so_det_id = stocker_ws_additional_detail.so_det_id
                                AND modify_size_qty.form_cut_id = form_cut_input.id
                                LEFT JOIN part_form ON part_form.form_id = form_cut_input.id
                                LEFT JOIN part ON part.act_costing_ws = stocker_ws_additional.act_costing_ws and part.panel = stocker_ws_additional.panel
                                LEFT JOIN part_detail ON part_detail.part_id = part.id
                                LEFT JOIN part_detail pd_com ON pd_com.id = part_detail.from_part_detail
                                AND part_detail.part_status = 'complement'
                                LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            WHERE
                                form_cut_input.STATUS = 'SELESAI PENGERJAAN'
                                AND ( stocker_ws_additional_detail.ratio > 0 OR modify_size_qty.difference_qty != 0 )
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) >= '$start_date'
                                AND COALESCE ( DATE( form_cut_input.waktu_selesai ), DATE( form_cut_input.waktu_mulai ), DATE( form_cut_input.tgl_input )) <= '$end_date'
                                AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            GROUP BY
                                form_cut_input.id,
                                form_cut_input_detail.group_stocker,
                                stocker_ws_additional_detail.id,
                                part_detail.id
                            ORDER BY
                                tanggal DESC,
                                meja,
                                worksheet,
                                style,
                                color,
                                panel,
                                part_detail_id,
                                id_so_det,
                                group_stocker
                    ),
                    dc_awal as (
                        WITH stocker as (
                            select
                                id_so_det,
                                form_cut_id,
                                form_reject_id,
                                form_piece_id,
                                no_form,
                                no_cut,
                                created_at,
                                buyer,
                                ws,
                                styleno,
                                color,
                                size,
                                dest,
                                panel,
                                panel_status,
                                part_detail_id,
                                master_part_id,
                                nama_part,
                                part_status,
                                SUM(qty_out) qty_dc,
                                cancel,
                                cancel_h,
                                status,
                                part_id
                            from (
                                select
                                    s.id_qr_stocker,
                                    msb.id_so_det,
                                    f.id form_cut_id,
                                    fr.id form_reject_id,
                                    fp.id form_piece_id,
                                    COALESCE(f.no_form, fr.no_form, fp.no_form) no_form,
                                    COALESCE(f.no_cut, fp.no_cut) no_cut,
                                    DATE_FORMAT(s.created_at, '%d-%m-%Y') AS created_at,
                                    msb.buyer,
                                    msb.ws,
                                    msb.styleno,
                                    msb.color,
                                    s.so_det_id,
                                    k.size,
                                    msb.dest,
                                    (CASE WHEN pd.part_status = 'complement' THEN p_com.panel ELSE p.panel END) panel,
                                    (CASE WHEN pd.part_status = 'complement' THEN p_com.panel_status ELSE p.panel_status END) panel_status,
                                    pd.id part_detail_id,
                                    mp.id master_part_id,
                                    mp.nama_part,
                                    pd.part_status,
                                    (CASE WHEN s.qty_ply_mod > 0 THEN s.qty_ply_mod ELSE s.qty_ply END) qty_out,
                                    k.cancel,
                                    k.cancel_h,
                                    k.status,
                                    (CASE WHEN pd.part_status = 'complement' THEN p_com.id ELSE p.id END) part_id
                                FROM
                                    stocker_input s
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                    left join form_cut_input f on f.id = s.form_cut_id
                                    left join form_cut_reject fr on fr.id = s.form_reject_id
                                    left join form_cut_piece fp on fp.id = s.form_piece_id
                                    left join part_detail pd on s.part_detail_id = pd.id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                    left join part p on p.id = pd.part_id
                                    left join part p_com on p_com.id = pd_com.part_id
                                    left join master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN (
                                        SELECT sd.id as id_so_det, ac.kpno ws, ac.styleno, sd.color, sd.size, sd.dest, ms.supplier as buyer, sd.cancel, so.cancel_h, ac.status FROM signalbit_erp.so_det sd
                                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                                    ) k on msb.id_so_det = k.id_so_det
                                    where
                                    (s.cancel IS NULL OR s.cancel != 'Y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    s.created_at >= '$start_date 00:00:00' and s.created_at < '$start_date 00:00:00'
                                ) cutting
                                group by
                                    no_form,
                                    id_so_det,
                                    part_id,
                                    part_detail_id
                        ),

                        form_list as (
                            select
                                id_so_det,
                                stocker.form_cut_id,
                                stocker.form_reject_id,
                                stocker.form_piece_id,
                                no_form,
                                no_cut,
                                stocker.created_at,
                                stocker.buyer,
                                ws,
                                styleno,
                                stocker.color,
                                stocker.size,
                                dest,
                                part.panel,
                                part.panel_status,
                                part_detail.id part_detail_id,
                                mp.id master_part_id,
                                mp.nama_part,
                                part_detail.part_status,
                                0 qty_dc,
                                '-' cancel,
                                '-' cancel_h,
                                '-' status,
                                part.id part_id
                            from
                                stocker
                                left join part on part.act_costing_ws = stocker.ws and part.id = stocker.part_id
                                left join part_detail on part_detail.part_id = part.id
                                left join master_part mp on mp.id = part_detail.master_part_id
                            where
                            part.panel_status != 'COMPLEMENT' AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            group by
                                no_form,
                                id_so_det,
                                part.id,
                                part_detail.id
                        )

                        select
                            MAX(id_so_det) id_so_det ,
                            MAX(no_form) no_form ,
                            MAX(no_cut) no_cut ,
                            MAX(created_at) created_at ,
                            MAX(buyer) buyer ,
                            MAX(ws) ws ,
                            MAX(styleno) styleno ,
                            MAX(color) color ,
                            MAX(size) size ,
                            MAX(dest) dest ,
                            MAX(panel) panel ,
                            MAX(panel_status) panel_status ,
                            MAX(part_detail_id ) part_detail_id,
                            MAX(master_part_id) master_part_id,
                            MAX(nama_part) nama_part ,
                            MAX(part_status) part_status ,
                            SUM(qty_dc) qty_dc,
                            '-' cancel,
                            '-' cancel_h,
                            '-' status,
                            MAX(part_id) part_id
                        from (
                            select * from stocker
                            union all
                            select * from form_list
                        ) stocker
                        group by
                            no_form,
                            id_so_det,
                            part_id,
                            part_detail_id
                        order by
                            no_form,
                            id_so_det,
                            part_id,
                            part_detail_id
                    ),
                    dc_in as (

                        WITH stocker as (
                            select
                                id_so_det,
                                form_cut_id,
                                form_reject_id,
                                form_piece_id,
                                no_form,
                                no_cut,
                                created_at,
                                buyer,
                                ws,
                                styleno,
                                color,
                                size,
                                dest,
                                panel,
                                panel_status,
                                part_detail_id,
                                master_part_id,
                                nama_part,
                                part_status,
                                SUM(qty_out) qty_dc,
                                cancel,
                                cancel_h,
                                status,
                                part_id
                            from (
                                select
                                    s.id_qr_stocker,
                                    msb.id_so_det,
                                    f.id form_cut_id,
                                    fr.id form_reject_id,
                                    fp.id form_piece_id,
                                    COALESCE(f.no_form, fr.no_form, fp.no_form) no_form,
                                    COALESCE(f.no_cut, fp.no_cut) no_cut,
                                    DATE_FORMAT(s.created_at, '%d-%m-%Y') AS created_at,
                                    msb.buyer,
                                    msb.ws,
                                    msb.styleno,
                                    msb.color,
                                    s.so_det_id,
                                    k.size,
                                    msb.dest,
                                    (CASE WHEN pd.part_status = 'complement' THEN p_com.panel ELSE p.panel END) panel,
                                    (CASE WHEN pd.part_status = 'complement' THEN p_com.panel_status ELSE p.panel_status END) panel_status,
                                    pd.id part_detail_id,
                                    mp.id master_part_id,
                                    mp.nama_part,
                                    pd.part_status,
                                    (CASE WHEN s.qty_ply_mod > 0 THEN s.qty_ply_mod ELSE s.qty_ply END) qty_out,
                                    k.cancel,
                                    k.cancel_h,
                                    k.status,
                                    (CASE WHEN pd.part_status = 'complement' THEN p_com.id ELSE p.id END) part_id
                                FROM
                                    stocker_input s
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                    left join form_cut_input f on f.id = s.form_cut_id
                                    left join form_cut_reject fr on fr.id = s.form_reject_id
                                    left join form_cut_piece fp on fp.id = s.form_piece_id
                                    left join part_detail pd on s.part_detail_id = pd.id
                                    left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                    left join part p on p.id = pd.part_id
                                    left join part p_com on p_com.id = pd_com.part_id
                                    left join master_part mp on mp.id = pd.master_part_id
                                    LEFT JOIN (
                                        SELECT sd.id as id_so_det, ac.kpno ws, ac.styleno, sd.color, sd.size, sd.dest, ms.supplier as buyer, sd.cancel, so.cancel_h, ac.status FROM signalbit_erp.so_det sd
                                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                                    ) k on msb.id_so_det = k.id_so_det
                                    where
                                    (s.cancel IS NULL OR s.cancel != 'Y') and
                                    (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                    s.created_at between '$start_date 00:00:00' and '$end_date 23:59:59'
                                ) cutting
                                group by
                                    no_form,
                                    id_so_det,
                                    part_id,
                                    part_detail_id
                        ),

                        form_list as (
                            select
                                id_so_det,
                                stocker.form_cut_id,
                                stocker.form_reject_id,
                                stocker.form_piece_id,
                                no_form,
                                no_cut,
                                stocker.created_at,
                                stocker.buyer,
                                ws,
                                styleno,
                                stocker.color,
                                stocker.size,
                                dest,
                                part.panel,
                                part.panel_status,
                                part_detail.id part_detail_id,
                                mp.id master_part_id,
                                mp.nama_part,
                                part_detail.part_status,
                                0 qty_dc,
                                '-' cancel,
                                '-' cancel_h,
                                '-' status,
                                part.id part_id
                            from
                                stocker
                                left join part on part.act_costing_ws = stocker.ws and part.id = stocker.part_id
                                left join part_detail on part_detail.part_id = part.id
                                left join master_part mp on mp.id = part_detail.master_part_id
                            where
                            part.panel_status != 'COMPLEMENT' AND (part_detail.part_status != 'complement' OR part_detail.part_status IS NULL)
                            group by
                                no_form,
                                id_so_det,
                                part.id,
                                part_detail.id
                        )

                        select
                            MAX(id_so_det) id_so_det ,
                            MAX(no_form) no_form ,
                            MAX(no_cut) no_cut ,
                            MAX(created_at) created_at ,
                            MAX(buyer) buyer ,
                            MAX(ws) ws ,
                            MAX(styleno) styleno ,
                            MAX(color) color ,
                            MAX(size) size ,
                            MAX(dest) dest ,
                            MAX(panel) panel ,
                            MAX(panel_status) panel_status ,
                            MAX(part_detail_id ) part_detail_id,
                            MAX(master_part_id ) master_part_id,
                            MAX(nama_part) nama_part ,
                            MAX(part_status) part_status ,
                            SUM(qty_dc) qty_dc,
                            '-' cancel,
                            '-' cancel_h,
                            '-' status,
                            MAX(part_id) part_id
                        from (
                            select * from stocker
                            union all
                            select * from form_list
                        ) stocker
                        group by
                            no_form,
                            id_so_det,
                            part_id,
                            part_detail_id
                        order by
                            no_form,
                            id_so_det,
                            part_id,
                            part_detail_id
                    )

                    SELECT
                        a.id_so_det,
                        buyer,
                        ws,
                        styleno,
                        color,
                        k.size,
                        dest,
                        part_id,
                        panel,
                        panel_status,
                        part_detail_id,
                        nama_part,
                        part_status,
                        sum(qty_cut_awal) - sum(qty_dc_awal) as saldo_awal,
                        sum(qty_cut) as qty_cut,
                        sum(qty_dc) - sum(qty_replace) as qty_dc_1,
                        sum(qty_dc) as qty_dc,
                        sum(qty_replace) as qty_replace,
                        (sum(qty_cut_awal) - sum(qty_dc_awal)) + sum(qty_cut) - sum(qty_dc) as saldo_akhir,
                        k.cancel,
                        k.cancel_h,
                        k.status
                    FROM
                    (
                        SELECT id_so_det, part_id, COALESCE(part_panel, panel) as panel, panel_status, part_detail_id, master_part_id, nama_part, part_status, sum(qty) qty_cut_awal, 0 as qty_dc_awal, 0 AS qty_cut, 0 AS qty_dc, 0 as qty_replace FROM cutt_awal WHERE (part_status != 'complement' OR part_status IS NULL) group by id_so_det, part_id, COALESCE(part_panel, panel), panel_status, part_detail_id
                        UNION ALL
                        SELECT id_so_det, part_id, COALESCE(part_panel, panel) as panel, panel_status, part_detail_id, master_part_id, nama_part, part_status, 0 AS qty_cut_awal, 0 AS qty_dc_awal, sum(qty) qty_cut, 0 AS qty_dc, 0 as qty_replace FROM cutt_in WHERE (part_status != 'complement' OR part_status IS NULL) group by id_so_det, part_id, COALESCE(part_panel, panel), panel_status, part_detail_id
                        UNION ALL
                        SELECT id_so_det, part_id, panel, panel_status, part_detail_id, master_part_id, nama_part, part_status, 0 AS qty_cut_awal, sum(qty_dc) AS qty_dc_awal, 0 as qty_cutt, 0 as qty_dc, 0 as qty_replace FROM dc_awal group by id_so_det, part_id, panel, panel_status, part_detail_id
                        UNION ALL
                        SELECT id_so_det, part_id, panel, panel_status, part_detail_id, master_part_id, nama_part, part_status, 0 AS qty_cut_awal, 0 AS qty_dc_awal, 0 as qty_cutt, sum(qty_dc) as qty_dc, 0 as qty_replace  FROM dc_in group by id_so_det, part_id, panel, panel_status, part_detail_id
                    ) a
                    LEFT JOIN (
                        SELECT sd.id as id_so_det, ac.kpno ws, ac.styleno, sd.color, sd.size, sd.dest, ms.supplier as buyer, sd.cancel, so.cancel_h, ac.status FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                    ) k on a.id_so_det = k.id_so_det
                    LEFT JOIN signalbit_erp.master_size_new msn on k.size = msn.size
                    group by
                        ws, color, size, a.panel, master_part_id
                    HAVING
                        (SUM(qty_cut_awal) - SUM(qty_dc_awal)) <> 0
                        OR SUM(qty_cut) <> 0
                        OR SUM(qty_dc) <> 0
                        OR ( (SUM(qty_cut_awal) - SUM(qty_dc_awal) ) + SUM(qty_cut) - SUM(qty_dc) ) <> 0
                    ORDER BY
                        ws asc, color asc, size asc, panel, part_detail_id, urutan asc
        ");

        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('cutting.report.export.export_excel_report_mut_wip_cutting_detail', [
            'rawData' => $rawData,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // e.g. 'Z'
                $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                // ===== 1. Format header rows (row 2 and 3) =====
                for ($i = 1; $i <= $columnIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

                    foreach ([2] as $row) {
                        $cell = $colLetter . $row;

                        $sheet->getStyle($cell)->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFD9EDF7'], // Light blue
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FF000000'], // Black text
                            ],
                        ]);
                    }
                }
                // ===== 3. Apply border to whole table =====
                $range = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            }
        ];
    }
}
