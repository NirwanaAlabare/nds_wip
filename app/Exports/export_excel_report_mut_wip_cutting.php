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


class export_excel_report_mut_wip_cutting implements FromView, ShouldAutoSize, WithEvents
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

        $rawData = DB::select("WITH
cutt_awal as (
SELECT
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) tanggal,
                UPPER(meja.`name`) meja,
                marker_input.act_costing_ws worksheet,
                marker_input.buyer,
                marker_input.style,
                marker_input.color,
                master_sb_ws.id_so_det,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE marker_input_detail.size END) size,
                form_cut_input_detail.group_roll,
                form_cut_input_detail.lot,
                form_cut_input.no_cut,
                form_cut_input.no_form,
                marker_input.kode no_marker,
                marker_input.panel,
                similar.max_group,
                form_cut_input_detail.group_stocker,
                COALESCE(modify_size_qty.difference_qty, 0),
                COALESCE(modify_size_qty.modified_qty, 0),
                ((COALESCE(marker_input_detail.ratio, 0) * COALESCE(form_cut_input_detail.total_lembar, 0)) + (COALESCE(modify_size_qty.difference_qty, 0))) qty
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
                        (status != 'not complete' and status != 'extension')
                    GROUP BY
                        form_cut_id,
                        group_stocker
                ) form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                LEFT JOIN (
                    SELECT
                        form_cut_id,
                        MAX(group_stocker) max_group
                    FROM
                        form_cut_input_detail
                    WHERE
                        (status != 'not complete' and status != 'extension')
                    GROUP BY
                        form_cut_id
                ) similar ON similar.form_cut_id = form_cut_input_detail.form_cut_id
                LEFT JOIN users as meja on meja.id = form_cut_input.no_meja
                LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                LEFT JOIN modify_size_qty ON modify_size_qty.form_cut_id = form_cut_input.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id AND form_cut_input_detail.group_stocker = COALESCE(modify_size_qty.group_stocker, similar.max_group)
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = marker_input_detail.so_det_id
            WHERE
                form_cut_input.`status` = 'SELESAI PENGERJAAN' and
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) >= '2026-01-01'
								and
								COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) < '$start_date'
								and
                (marker_input_detail.ratio > 0 OR (similar.max_group = form_cut_input_detail.group_stocker AND modify_size_qty.difference_qty > 0))
            GROUP BY
                form_cut_input.id,
                form_cut_input_detail.group_stocker,
                marker_input_detail.id
            UNION ALL
            SELECT
                COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) tanggal,
                '-' meja,
                form_cut_piece.act_costing_ws worksheet,
                form_cut_piece.buyer,
                form_cut_piece.style,
                form_cut_piece.color,
                master_sb_ws.id_so_det,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE form_cut_piece_detail_size.size END) size,
                form_cut_piece_detail.`group_roll`,
                form_cut_piece_detail.lot,
                form_cut_piece.no_cut,
                form_cut_piece.no_form,
                '-' no_marker,
                form_cut_piece.panel,
                '-' max_group,
                form_cut_piece_detail.group_stocker,
                null,
                null,
                SUM(form_cut_piece_detail_size.qty) as qty
            FROM
                form_cut_piece
                LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                LEFT JOIN form_cut_piece_detail_size ON form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
            WHERE
                DATE(form_cut_piece_detail.created_at) >= '2026-01-01' and DATE(form_cut_piece_detail.created_at) < '$start_date'
								and form_cut_piece_detail.status = 'complete'
            GROUP BY
                form_cut_piece.id,
                form_cut_piece_detail.group_stocker,
                form_cut_piece_detail_size.id
            ORDER BY
                tanggal desc,
                meja,
                worksheet,
                style,
                color,
                panel,
                id_so_det,
                group_stocker
),
cutt_in as
(
            SELECT
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) tanggal,
                UPPER(meja.`name`) meja,
                marker_input.act_costing_ws worksheet,
                marker_input.buyer,
                marker_input.style,
                marker_input.color,
                master_sb_ws.id_so_det,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE marker_input_detail.size END) size,
                form_cut_input_detail.group_roll,
                form_cut_input_detail.lot,
                form_cut_input.no_cut,
                form_cut_input.no_form,
                marker_input.kode no_marker,
                marker_input.panel,
                similar.max_group,
                form_cut_input_detail.group_stocker,
                COALESCE(modify_size_qty.difference_qty, 0),
                COALESCE(modify_size_qty.modified_qty, 0),
                ((COALESCE(marker_input_detail.ratio, 0) * COALESCE(form_cut_input_detail.total_lembar, 0)) + (COALESCE(modify_size_qty.difference_qty, 0))) qty
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
                        (status != 'not complete' and status != 'extension')
                    GROUP BY
                        form_cut_id,
                        group_stocker
                ) form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                LEFT JOIN (
                    SELECT
                        form_cut_id,
                        MAX(group_stocker) max_group
                    FROM
                        form_cut_input_detail
                    WHERE
                        (status != 'not complete' and status != 'extension')
                    GROUP BY
                        form_cut_id
                ) similar ON similar.form_cut_id = form_cut_input_detail.form_cut_id
                LEFT JOIN users as meja on meja.id = form_cut_input.no_meja
                LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                LEFT JOIN modify_size_qty ON modify_size_qty.form_cut_id = form_cut_input.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id AND form_cut_input_detail.group_stocker = COALESCE(modify_size_qty.group_stocker, similar.max_group)
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = marker_input_detail.so_det_id
            WHERE
                form_cut_input.`status` = 'SELESAI PENGERJAAN' and
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) >= '$start_date'
								and
								COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) <= '$end_date'
								and
                (marker_input_detail.ratio > 0 OR (similar.max_group = form_cut_input_detail.group_stocker AND modify_size_qty.difference_qty > 0))
            GROUP BY
                form_cut_input.id,
                form_cut_input_detail.group_stocker,
                marker_input_detail.id
            UNION ALL
            SELECT
                COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) tanggal,
                '-' meja,
                form_cut_piece.act_costing_ws worksheet,
                form_cut_piece.buyer,
                form_cut_piece.style,
                form_cut_piece.color,
                master_sb_ws.id_so_det,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE form_cut_piece_detail_size.size END) size,
                form_cut_piece_detail.`group_roll`,
                form_cut_piece_detail.lot,
                form_cut_piece.no_cut,
                form_cut_piece.no_form,
                '-' no_marker,
                form_cut_piece.panel,
                '-' max_group,
                form_cut_piece_detail.group_stocker,
                null,
                null,
                SUM(form_cut_piece_detail_size.qty) as qty
            FROM
                form_cut_piece
                LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                LEFT JOIN form_cut_piece_detail_size ON form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
            WHERE
                DATE(form_cut_piece_detail.created_at) >= '$start_date' and DATE(form_cut_piece_detail.created_at) <= '$end_date'
								and form_cut_piece_detail.status = 'complete'
            GROUP BY
                form_cut_piece.id,
                form_cut_piece_detail.group_stocker,
                form_cut_piece_detail_size.id
            ORDER BY
                tanggal desc,
                meja,
                worksheet,
                style,
                color,
                panel,
                id_so_det,
                group_stocker
),
dc_awal as (
SELECT
	GROUP_CONCAT(id_qr_stocker) as stockers,
	m.buyer,
	act_costing_ws,
	m.color,
	panel,
	so_det_id as id_so_det,
	m.size,
	panel_status,
	GROUP_CONCAT(nama_part) as nama_part,
	GROUP_CONCAT(part_status) as part_status,
	sum(qty_replace) as qty_replace,
	CASE WHEN panel_status = 'main' THEN COALESCE(qty_in_main, qty_in) ELSE MIN(qty_in) END as qty_dc
FROM
	(
		SELECT
				UPPER(a.id_qr_stocker) id_qr_stocker,
				DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
				a.tgl_trans,
				s.act_costing_ws,
				s.color,
				p.buyer,
				p.style,
				p.panel,
				p.id part_id,
				p.panel_status,
				s.so_det_id,
				s.ratio,
				a.qty_awal,
				a.qty_reject,
				a.qty_replace,
				CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range,
				(a.qty_awal - a.qty_reject + a.qty_replace) qty_in_main,
				null qty_in,
				a.tujuan,
				a.lokasi,
				a.tempat,
				a.created_at,
				a.user,
				COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
				COALESCE(msb.size, s.size) size,
				mp.nama_part,
				pd.id as part_detail_id,
				pd.part_status
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
				a.tgl_trans >= '2026-01-01' AND a.tgl_trans < '$start_date'
				AND s.id is not null AND
				(s.cancel IS NULL OR s.cancel != 'y') and
				pd.part_status = 'main'
		UNION ALL
		SELECT
				UPPER(a.id_qr_stocker) id_qr_stocker,
				DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
				a.tgl_trans,
				s.act_costing_ws,
				s.color,
				CASE WHEN pd.part_status = 'complement' THEN pcom.buyer ELSE p.buyer END as buyer,
				CASE WHEN pd.part_status = 'complement' THEN pcom.style ELSE p.style END as style,
				CASE WHEN pd.part_status = 'complement' THEN pcom.panel ELSE p.panel END as panel,
				CASE WHEN pd.part_status = 'complement' THEN pcom.id ELSE p.id  END as part_id,
				CASE WHEN pd.part_status = 'complement' THEN pcom.panel_status ELSE p.panel_status END as panel_status,
				s.so_det_id,
				s.ratio,
				a.qty_awal,
				a.qty_reject,
				a.qty_replace,
				CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range,
				null qty_in_main,
				(a.qty_awal - a.qty_reject + a.qty_replace) qty_in,
				a.tujuan,
				a.lokasi,
				a.tempat,
				a.created_at,
				a.user,
				COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
				COALESCE(msb.size, s.size) size,
				mp.nama_part,
				pd.id as part_detail_id,
				pd.part_status
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
				a.tgl_trans >= '2026-01-01' AND a.tgl_trans < '$start_date'
				AND s.id is not null AND
				(s.cancel IS NULL OR s.cancel != 'y') and
				(pd.part_status != 'main' OR pd.part_status IS NULL)
	) dc
	left join master_sb_ws m on dc.so_det_id = m.id_so_det
group by
	dc.part_id,
	dc.so_det_id,
	dc.stocker_range
),
dc_in as (
SELECT
	GROUP_CONCAT(id_qr_stocker) as stockers,
	m.buyer,
	act_costing_ws,
	m.color,
	panel,
	so_det_id as id_so_det,
	m.size,
	panel_status,
	GROUP_CONCAT(nama_part) as nama_part,
	GROUP_CONCAT(part_status) as part_status,
	sum(qty_replace) as qty_replace,
	CASE WHEN panel_status = 'main' THEN COALESCE(qty_in_main, qty_in) ELSE MIN(qty_in) END as qty_dc
FROM
	(
		SELECT
				UPPER(a.id_qr_stocker) id_qr_stocker,
				DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
				a.tgl_trans,
				s.act_costing_ws,
				s.color,
				p.buyer,
				p.style,
				p.panel,
				p.id part_id,
				p.panel_status,
				s.so_det_id,
				s.ratio,
				a.qty_awal,
				a.qty_reject,
				a.qty_replace,
				CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range,
				(a.qty_awal - a.qty_reject + a.qty_replace) qty_in_main,
				null qty_in,
				a.tujuan,
				a.lokasi,
				a.tempat,
				a.created_at,
				a.user,
				COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
				COALESCE(msb.size, s.size) size,
				mp.nama_part,
				pd.id as part_detail_id,
				pd.part_status
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
				a.tgl_trans >= '$start_date' AND a.tgl_trans <= '$end_date'
				AND s.id is not null AND
				(s.cancel IS NULL OR s.cancel != 'y') and
				pd.part_status = 'main'
		UNION ALL
		SELECT
				UPPER(a.id_qr_stocker) id_qr_stocker,
				DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
				a.tgl_trans,
				s.act_costing_ws,
				s.color,
				CASE WHEN pd.part_status = 'complement' THEN pcom.buyer ELSE p.buyer END as buyer,
				CASE WHEN pd.part_status = 'complement' THEN pcom.style ELSE p.style END as style,
				CASE WHEN pd.part_status = 'complement' THEN pcom.panel ELSE p.panel END as panel,
				CASE WHEN pd.part_status = 'complement' THEN pcom.id ELSE p.id  END as part_id,
				CASE WHEN pd.part_status = 'complement' THEN pcom.panel_status ELSE p.panel_status END as panel_status,
				s.so_det_id,
				s.ratio,
				a.qty_awal,
				a.qty_reject,
				a.qty_replace,
				CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range,
				null qty_in_main,
				(a.qty_awal - a.qty_reject + a.qty_replace) qty_in,
				a.tujuan,
				a.lokasi,
				a.tempat,
				a.created_at,
				a.user,
				COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
				COALESCE(msb.size, s.size) size,
				mp.nama_part,
				pd.id as part_detail_id,
				pd.part_status
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
				a.tgl_trans >= '$start_date' AND a.tgl_trans <= '$end_date'
				AND s.id is not null AND
				(s.cancel IS NULL OR s.cancel != 'y') and
				(pd.part_status != 'main' OR pd.part_status IS NULL)
	) dc
	left join master_sb_ws m on dc.so_det_id = m.id_so_det
group by
	dc.part_id,
	dc.so_det_id,
	dc.stocker_range
)

SELECT
a.id_so_det,
buyer,
ws,
styleno,
color,
k.size,
dest,
panel,
sum(qty_cut_awal) - sum(qty_dc_awal) as saldo_awal,
sum(qty_cut) as qty_cut,
sum(qty_dc) - sum(qty_replace) as qty_dc,
sum(qty_replace) as qty_replace,
(sum(qty_cut_awal) - sum(qty_dc_awal)) + sum(qty_cut) - sum(qty_dc) as saldo_akhir,
k.cancel,
k.cancel_h,
k.status

FROM
(
SELECT id_so_det, panel, sum(qty) qty_cut_awal, 0 as qty_dc_awal, 0 AS qty_cut, 0 AS qty_dc, 0 as qty_replace FROM cutt_awal group by id_so_det, panel
UNION ALL
SELECT id_so_det, panel, 0 AS qty_cut_awal, 0 AS qty_dc_awal, sum(qty) qty_cut, 0 AS qty_dc, 0 as qty_replace FROM cutt_in group by id_so_det, panel
UNION ALL
SELECT id_so_det, panel, 0 AS qty_cut_awal, sum(qty_dc) AS qty_dc_awal, 0 as qty_cutt, 0 as qty_dc, 0 as qty_replace FROM dc_awal group by id_so_det, panel
UNION ALL
SELECT id_so_det, panel, 0 AS qty_cut_awal, 0 AS qty_dc_awal, 0 as qty_cutt, sum(qty_dc) as qty_dc, sum(qty_replace) as qty_replace  FROM dc_in group by id_so_det, panel
) a
LEFT JOIN (
SELECT sd.id as id_so_det, ac.kpno ws, ac.styleno, sd.color, sd.size, sd.dest, ms.supplier as buyer, sd.cancel, so.cancel_h, ac.status FROM signalbit_erp.so_det sd
INNER JOIN signalbit_erp.so ON sd.id_so = so.id
INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
) k on a.id_so_det = k.id_so_det
LEFT JOIN signalbit_erp.master_size_new msn on k.size = msn.size
group by a.id_so_det, a.panel
ORDER BY ws asc, color asc, urutan asc
    ");


        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('cutting.report.export.export_excel_report_mut_wip_cutting', [

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
