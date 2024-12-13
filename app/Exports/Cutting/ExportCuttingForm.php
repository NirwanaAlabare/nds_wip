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
                marker_input_detail.size,
                form_cut_input_detail.group_roll,
                form_cut_input_detail.lot,
                form_cut_input.no_cut,
                form_cut_input.no_form,
                marker_input.kode no_marker,
                marker_input.panel,
                (marker_input_detail.ratio * form_cut_input_detail.total_lembar) qty
            FROM
                form_cut_input
                LEFT JOIN (
                    SELECT
                        no_form_cut_input,
                        form_cut_input_detail.group_roll,
                        form_cut_input_detail.lot,
                        SUM( form_cut_input_detail.lembar_gelaran ) total_lembar
                    FROM
                        form_cut_input_detail
                    WHERE
                        form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)
                    GROUP BY
                        form_cut_input_detail.no_form_cut_input,
                        form_cut_input_detail.group_roll
                ) form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                LEFT JOIN users as meja on meja.id = form_cut_input.no_meja
                LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id and marker_input_detail.ratio > 0
            WHERE
                form_cut_input.`status` = 'SELESAI PENGERJAAN' and
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), form_cut_input.tgl_input) between '".$this->dateFrom."' and '".$this->dateTo."'
                AND form_cut_input.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH)
            GROUP BY
                form_cut_input.no_form,
                form_cut_input_detail.group_roll,
                marker_input_detail.id
            ORDER BY
                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), form_cut_input.tgl_input) desc,
                form_cut_input.no_meja,
                marker_input.act_costing_id,
                marker_input.style,
                marker_input.color,
                marker_input.panel,
                marker_input_detail.id
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
