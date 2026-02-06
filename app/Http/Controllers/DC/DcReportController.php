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



           $dataReport = DB::select("WITH dc_before_saldo AS (
	WITH dc AS (
		SELECT
			a.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			dc_in_input a
			LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
			LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
			LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
			LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
			LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
			LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
			LEFT JOIN part p ON pd.part_id = p.id
			LEFT JOIN master_part mp ON mp.id = pd.master_part_id
		WHERE
			a.tgl_trans >= '".$dateFrom." 00:00:00'
			AND a.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			a.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			dc_in_input a
			LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
			LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
			LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
			LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
			LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
			LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
			LEFT JOIN part p ON pd.part_id = p.id
			LEFT JOIN part_detail pdcom ON pdcom.id = pd.from_part_detail
			LEFT JOIN part pcom ON pcom.id = pdcom.part_id
			LEFT JOIN master_part mp ON mp.id = pd.master_part_id
		WHERE
			a.tgl_trans >= '".$dateFrom." 00:00:00'
			AND a.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		sii_in AS (
		SELECT
			sii_in.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			( sii_in.qty_in ) sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_in_input sii_in
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii_in.tgl_trans >= '".$dateFrom." 00:00:00'
			AND sii_in.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			sii_in.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			( sii_in.qty_in ) sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_in_input sii_in
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii_in.tgl_trans >= '".$dateFrom." 00:00:00'
			AND sii_in.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		sii AS (
		SELECT
			sii.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			sii.qty_replace sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			sii.qty_in sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_input sii
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii.tgl_trans >= '".$dateFrom."  00:00:00'
			AND sii.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			sii.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			sii.qty_replace sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			sii.qty_in sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_input sii
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii.tgl_trans >= '".$dateFrom." 00:00:00'
			AND sii.tgl_trans < '".$dateTo." 23:59:59'
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		wod AS (
		SELECT
			wod.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			wod.qty sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			wip_out_det wod
			LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			wod.updated_at >= '".$dateFrom." 00:00:00'
			AND wod.updated_at < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			wod.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			wod.qty sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			wip_out_det wod
			LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			wod.updated_at >= '".$dateFrom." 00:00:00'
			AND wod.updated_at < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		si AS (
		SELECT
			si.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			si.qty_replace sec_in_rep_main,
			NULL sec_in_rep,
			si.qty_in sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_in_input si
			LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			si.tgl_trans >= '".$dateFrom."'
			AND si.tgl_trans < '".$dateTo."'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			si.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			si.qty_replace sec_in_rep,
			NULL sec_in_out_main,
			si.qty_in sec_in_out
		FROM
			secondary_in_input si
			LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			si.tgl_trans >= '".$dateFrom."'
			AND si.tgl_trans < '".$dateTo."'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		loading_line AS (
		SELECT
			panel,
			so_det_id,
			GROUP_CONCAT( stocker_id ) stockers,
			SUM( loading_qty ) loading_qty
		FROM
			(
			SELECT
				p.panel AS panel,
				GROUP_CONCAT( ll.stocker_id ) stocker_id,
				s.so_det_id,
				MIN( ll.qty ) loading_qty
			FROM
				loading_line ll
				LEFT JOIN stocker_input s ON s.id = ll.stocker_id
				LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
				LEFT JOIN part p ON p.id = pd.part_id
			WHERE
				ll.tanggal_loading >= '".$dateFrom."'
				AND ll.tanggal_loading < '".$dateTo."'
				AND ( s.cancel IS NULL OR s.cancel != 'y' )
			GROUP BY
				p.panel,
				s.form_cut_id,
				s.so_det_id,
				s.group_stocker,
				s.ratio
			) AS loading
		GROUP BY
			panel,
			so_det_id
		) SELECT CURRENT_DATE AS
		tanggal,
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
		0 saldo_awal,
		qty_in,
		kirim_secondary_dalam,
		terima_repaired_secondary_dalam,
		terima_good_secondary_dalam,
		kirim_secondary_luar,
		terima_repaired_secondary_luar,
		terima_good_secondary_luar,
		loading_qty,
		qty_in - kirim_secondary_dalam + terima_repaired_secondary_dalam + terima_good_secondary_dalam - kirim_secondary_luar + terima_repaired_secondary_luar + terima_good_secondary_luar - loading_qty saldo_akhir,
		CURRENT_TIMESTAMP created_at,
		CURRENT_TIMESTAMP updated_at
	FROM
		(
		SELECT
			GROUP_CONCAT( saldo_dc.id_qr_stocker ) AS stockers,
			max( msb.buyer ) AS buyer,
			max( msb.ws ) AS ws,
			max( msb.color ) AS color,
			max( msb.styleno ) AS style,
			msb.id_so_det,
			p.panel,
			p.panel_status,
			pd.id AS part_detail_id,
			GROUP_CONCAT( DISTINCT mp.nama_part ) AS nama_part,
			GROUP_CONCAT( DISTINCT pd.part_status ) AS part_status,
			(
			CASE

					WHEN panel_status = 'main' THEN
					COALESCE (
						SUM(
						COALESCE ( qty_in_dc_main, 0 )),
						SUM(
						COALESCE ( qty_in_dc, 0 ))) ELSE SUM(
					COALESCE ( qty_in_dc, 0 ))
				END
				) AS qty_in,
				(
				CASE

						WHEN panel_status = 'main' THEN
						COALESCE (
							SUM(
							COALESCE ( sec_inhouse_in_main, 0 )),
							SUM(
							COALESCE ( sec_inhouse_in, 0 ))) ELSE SUM(
						COALESCE ( sec_inhouse_in, 0 ))
					END
					) kirim_secondary_dalam,
					(
					CASE

							WHEN panel_status = 'main' THEN
							COALESCE (
								SUM(
								COALESCE ( sec_inhouse_rep_main, 0 )),
								SUM(
								COALESCE ( sec_inhouse_rep, 0 ))) ELSE SUM(
							COALESCE ( sec_inhouse_rep, 0 ))
						END
						) terima_repaired_secondary_dalam,
						(
						CASE

								WHEN panel_status = 'main' THEN
								COALESCE (
									SUM(
									COALESCE ( sec_inhouse_out_main, 0 )),
									SUM(
									COALESCE ( sec_inhouse_out, 0 ))) ELSE SUM(
								COALESCE ( sec_inhouse_out, 0 ))
							END
							) terima_good_secondary_dalam,
							(
							CASE

									WHEN panel_status = 'main' THEN
									COALESCE (
										SUM(
										COALESCE ( sec_in_in_main, 0 )),
										SUM(
										COALESCE ( sec_in_in, 0 ))) ELSE SUM(
									COALESCE ( sec_in_in, 0 ))
								END
								) kirim_secondary_luar,
								(
								CASE

										WHEN panel_status = 'main' THEN
										COALESCE (
											SUM(
											COALESCE ( sec_in_rep_main, 0 )),
											SUM(
											COALESCE ( sec_in_rep, 0 ))) ELSE SUM(
										COALESCE ( sec_in_rep, 0 ))
									END
									) terima_repaired_secondary_luar,
									(
									CASE

											WHEN panel_status = 'main' THEN
											COALESCE (
												SUM(
												COALESCE ( sec_in_out_main, 0 )),
												SUM(
												COALESCE ( sec_in_out, 0 ))) ELSE SUM(
											COALESCE ( sec_in_out, 0 ))
										END
										) terima_good_secondary_luar,
										COALESCE ( max( loading_line.loading_qty ), 0 ) loading_qty
									FROM
										(
										SELECT
											*
										FROM
											dc UNION ALL
										SELECT
											*
										FROM
											sii_in UNION ALL
										SELECT
											*
										FROM
											sii UNION ALL
										SELECT
											*
										FROM
											wod UNION ALL
										SELECT
											*
										FROM
											si
										) saldo_dc
										LEFT JOIN master_sb_ws msb ON msb.id_so_det = saldo_dc.so_det_id
										LEFT JOIN part_detail pd ON pd.id = saldo_dc.part_detail_id
										LEFT JOIN part p ON p.id = pd.part_id
										LEFT JOIN master_part mp ON mp.id = pd.master_part_id
										LEFT JOIN loading_line ON loading_line.so_det_id = saldo_dc.so_det_id
										AND loading_line.panel = p.panel
									GROUP BY
										saldo_dc.so_det_id,
										saldo_dc.part_detail_id
									) saldo_dc
								),
								dc_current_saldo AS (
									WITH dc AS (
									SELECT
										a.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										dc_in_input a
										LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
										LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
										LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
										LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
										LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
										LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
										LEFT JOIN part p ON pd.part_id = p.id
										LEFT JOIN master_part mp ON mp.id = pd.master_part_id
									WHERE
										a.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										a.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										dc_in_input a
										LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
										LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
										LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
										LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
										LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
										LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
										LEFT JOIN part p ON pd.part_id = p.id
										LEFT JOIN part_detail pdcom ON pdcom.id = pd.from_part_detail
										LEFT JOIN part pcom ON pcom.id = pdcom.part_id
										LEFT JOIN master_part mp ON mp.id = pd.master_part_id
									WHERE
										a.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									sii_in AS (
									SELECT
										sii_in.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										( sii_in.qty_in ) sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_in_input sii_in
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii_in.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										sii_in.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										( sii_in.qty_in ) sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_in_input sii_in
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii_in.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									sii AS (
									SELECT
										sii.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										sii.qty_replace sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										sii.qty_in sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_input sii
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										sii.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										sii.qty_replace sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										sii.qty_in sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_input sii
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									wod AS (
									SELECT
										wod.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										wod.qty sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										wip_out_det wod
										LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										wod.updated_at BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										wod.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										wod.qty sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										wip_out_det wod
										LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										wod.updated_at BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									si AS (
									SELECT
										si.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										si.qty_replace sec_in_rep_main,
										NULL sec_in_rep,
										si.qty_in sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_in_input si
										LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										si.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										si.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										si.qty_replace sec_in_rep,
										NULL sec_in_out_main,
										si.qty_in sec_in_out
									FROM
										secondary_in_input si
										LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										si.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									loading_line AS (
									SELECT
										panel,
										so_det_id,
										GROUP_CONCAT( stocker_id ) stockers,
										SUM( loading_qty ) loading_qty
									FROM
										(
										SELECT
											p.panel AS panel,
											GROUP_CONCAT( ll.stocker_id ) stocker_id,
											s.so_det_id,
											MIN( ll.qty ) loading_qty
										FROM
											loading_line ll
											LEFT JOIN stocker_input s ON s.id = ll.stocker_id
											LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
											LEFT JOIN part p ON p.id = pd.part_id
										WHERE
											ll.tanggal_loading BETWEEN '".$dateFrom."'
											AND '".$dateTo."'
											AND ( s.cancel IS NULL OR s.cancel != 'y' )
										GROUP BY
											p.panel,
											s.form_cut_id,
											s.so_det_id,
											s.group_stocker,
											s.ratio
										) AS loading
									GROUP BY
										panel,
										so_det_id
									) SELECT
									*,
									qty_in - kirim_secondary_dalam + terima_repaired_secondary_dalam + terima_good_secondary_dalam - kirim_secondary_luar + terima_repaired_secondary_luar + terima_good_secondary_luar - loading_qty saldo_akhir
								FROM
									(
									SELECT
										GROUP_CONCAT( saldo_dc.id_qr_stocker ) AS stockers,
										max( msb.buyer ) AS buyer,
										max( msb.ws ) AS ws,
										max( msb.color ) AS color,
										max( msb.size ) AS size,
										max( msb.styleno ) AS style,
										msb.id_so_det,
										p.panel,
										p.panel_status,
										pd.id AS part_detail_id,
										GROUP_CONCAT( DISTINCT mp.nama_part ) AS nama_part,
										GROUP_CONCAT( DISTINCT pd.part_status ) AS part_status,
										(
										CASE

												WHEN panel_status = 'main' THEN
												COALESCE (
													SUM(
													COALESCE ( qty_in_dc_main, 0 )),
													SUM(
													COALESCE ( qty_in_dc, 0 ))) ELSE SUM(
												COALESCE ( qty_in_dc, 0 ))
											END
											) AS qty_in,
											(
											CASE

													WHEN panel_status = 'main' THEN
													COALESCE (
														SUM(
														COALESCE ( sec_inhouse_in_main, 0 )),
														SUM(
														COALESCE ( sec_inhouse_in, 0 ))) ELSE SUM(
													COALESCE ( sec_inhouse_in, 0 ))
												END
												) kirim_secondary_dalam,
												(
												CASE

														WHEN panel_status = 'main' THEN
														COALESCE (
															SUM(
															COALESCE ( sec_inhouse_rep_main, 0 )),
															SUM(
															COALESCE ( sec_inhouse_rep, 0 ))) ELSE SUM(
														COALESCE ( sec_inhouse_rep, 0 ))
													END
													) terima_repaired_secondary_dalam,
													(
													CASE

															WHEN panel_status = 'main' THEN
															COALESCE (
																SUM(
																COALESCE ( sec_inhouse_out_main, 0 )),
																SUM(
																COALESCE ( sec_inhouse_out, 0 ))) ELSE SUM(
															COALESCE ( sec_inhouse_out, 0 ))
														END
														) terima_good_secondary_dalam,
														(
														CASE

																WHEN panel_status = 'main' THEN
																COALESCE (
																	SUM(
																	COALESCE ( sec_in_in_main, 0 )),
																	SUM(
																	COALESCE ( sec_in_in, 0 ))) ELSE SUM(
																COALESCE ( sec_in_in, 0 ))
															END
															) kirim_secondary_luar,
															(
															CASE

																	WHEN panel_status = 'main' THEN
																	COALESCE (
																		SUM(
																		COALESCE ( sec_in_rep_main, 0 )),
																		SUM(
																		COALESCE ( sec_in_rep, 0 ))) ELSE SUM(
																	COALESCE ( sec_in_rep, 0 ))
																END
																) terima_repaired_secondary_luar,
																(
																CASE

																		WHEN panel_status = 'main' THEN
																		COALESCE (
																			SUM(
																			COALESCE ( sec_in_out_main, 0 )),
																			SUM(
																			COALESCE ( sec_in_out, 0 ))) ELSE SUM(
																		COALESCE ( sec_in_out, 0 ))
																	END
																	) terima_good_secondary_luar,
																	COALESCE ( max( loading_line.loading_qty ), 0 ) loading_qty
																FROM
																	(
																	SELECT
																		*
																	FROM
																		dc UNION ALL
																	SELECT
																		*
																	FROM
																		sii_in UNION ALL
																	SELECT
																		*
																	FROM
																		sii UNION ALL
																	SELECT
																		*
																	FROM
																		wod UNION ALL
																	SELECT
																		*
																	FROM
																		si
																	) saldo_dc
																	LEFT JOIN master_sb_ws msb ON msb.id_so_det = saldo_dc.so_det_id
																	LEFT JOIN part_detail pd ON pd.id = saldo_dc.part_detail_id
																	LEFT JOIN part p ON p.id = pd.part_id
																	LEFT JOIN master_part mp ON mp.id = pd.master_part_id
																	LEFT JOIN loading_line ON loading_line.so_det_id = saldo_dc.so_det_id
																	AND loading_line.panel = p.panel
																GROUP BY
																	saldo_dc.so_det_id,
																	saldo_dc.part_detail_id
																) saldo_dc
															) SELECT
															dc_current_saldo.*,
															COALESCE ( dc_before_saldo.saldo_akhir, 0 ) AS current_saldo_awal,
															COALESCE ( dc_before_saldo.saldo_akhir, 0 )+ COALESCE ( dc_current_saldo.saldo_akhir, 0 ) AS current_saldo_akhir
														FROM
															dc_current_saldo
														LEFT JOIN dc_before_saldo ON dc_before_saldo.id_so_det = dc_current_saldo.id_so_det
	AND dc_before_saldo.part_detail_id = dc_current_saldo.part_detail_id
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
