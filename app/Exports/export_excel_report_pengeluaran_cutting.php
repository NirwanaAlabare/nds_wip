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


class export_excel_report_pengeluaran_cutting implements FromView, ShouldAutoSize, WithEvents
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

dc_awal as (
SELECT
	GROUP_CONCAT(id_qr_stocker) as stockers,
	no_form,
	no_cut,
	(DATE_FORMAT(MAX(created_at), '%Y-%m-%d')) as created_at,
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
				COALESCE (f.no_form, fr.no_form, fp.no_form, '-') as no_form,
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
				a.tgl_trans >= '$tgl_saldo' AND a.tgl_trans < '$start_date'
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
				COALESCE (f.no_form, fr.no_form, fp.no_form, '-') as no_form,
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
				a.tgl_trans >= '$tgl_saldo' AND a.tgl_trans < '$start_date'
				AND s.id is not null AND
				(s.cancel IS NULL OR s.cancel != 'y') and
				(pd.part_status != 'main' OR pd.part_status IS NULL)
	) dc
	left join master_sb_ws m on dc.so_det_id = m.id_so_det
group by
	dc.part_id,
	dc.so_det_id,
	dc.stocker_range,
	dc.no_form,
    dc.no_cut
),
dc_in as (
SELECT
	GROUP_CONCAT(id_qr_stocker) as stockers,
	no_form,
	no_cut,
	(DATE_FORMAT(MAX(created_at), '%Y-%m-%d')) as created_at,
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
				COALESCE (f.no_form, fr.no_form, fp.no_form, '-') as no_form,
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
				COALESCE (f.no_form, fr.no_form, fp.no_form, '-') as no_form,
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
	dc.stocker_range,
	dc.no_form,
    dc.no_cut
)

SELECT
a.id_so_det,
a.no_form,
a.no_cut,
DATE_FORMAT(a.created_at, '%d-%m-%Y') AS created_at,
buyer,
ws,
styleno,
color,
k.size,
dest,
panel,
sum(qty_dc) - sum(qty_replace) as qty_dc,
sum(qty_replace) as qty_replace,
k.cancel,
k.cancel_h,
k.status

FROM
(
SELECT id_so_det, panel, no_form, no_cut, created_at, 0 AS qty_cut_awal, sum(qty_dc) AS qty_dc_awal, 0 as qty_cutt, 0 as qty_dc, 0 as qty_replace FROM dc_awal group by id_so_det, no_form, panel
UNION ALL
SELECT id_so_det, panel, no_form, no_cut, created_at, 0 AS qty_cut_awal, 0 AS qty_dc_awal, 0 as qty_cutt, sum(qty_dc) as qty_dc, sum(qty_replace) as qty_replace  FROM dc_in group by id_so_det, no_form, panel
) a
LEFT JOIN (
SELECT sd.id as id_so_det, ac.kpno ws, ac.styleno, sd.color, sd.size, sd.dest, ms.supplier as buyer, sd.cancel, so.cancel_h, ac.status FROM signalbit_erp.so_det sd
INNER JOIN signalbit_erp.so ON sd.id_so = so.id
INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
) k on a.id_so_det = k.id_so_det
LEFT JOIN signalbit_erp.master_size_new msn on k.size = msn.size
group by ws, color, size, a.panel, a.no_form, a.no_cut, a.created_at
HAVING
    (SUM(qty_dc) - SUM(qty_replace)) <> 0
    OR SUM(qty_replace) <> 0
ORDER BY ws asc, color asc, urutan asc
    ");


        $this->rowCount = count($rawData) + 3; // 1 for header

        return view('cutting.report.export.export_excel_report_pengeluaran_cutting', [
            'rawData' => $rawData,
			'startDate' => $start_date,
            'endDate' => $end_date
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

                    foreach ([4] as $row) {
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
                $range = 'A4:' . $highestColumn . $highestRow;
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
