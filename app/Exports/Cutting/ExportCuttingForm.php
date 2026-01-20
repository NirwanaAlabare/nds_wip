<?php

namespace App\Exports\Cutting;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportCuttingForm implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
    }

    public function view(): View
    {
        $data = DB::select("
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
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) between '".$this->dateFrom."' and '".$this->dateTo."' and
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
                DATE(form_cut_piece_detail.created_at) between '".$this->dateFrom."' and '".$this->dateTo."' and
                form_cut_piece_detail.status = 'complete'
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
        ");

        $this->rowCount = count($data);

        return view('cutting.spreading.export.cutting-form', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'data' => $data,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $event->sheet->styleCells(
            'B2:C2',
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
        );

        $event->sheet->styleCells(
            'A4:N' . ($event->getConcernable()->rowCount+5),
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]
        );
    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'E' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }
}
