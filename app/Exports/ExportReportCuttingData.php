<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportReportCuttingData implements FromView, WithEvents, ShouldAutoSize, WithTitle /*WithColumnWidths,*/
{
    use Exportable;

    protected $date;

    public function __construct($date)
    {
        $this->date = $date ? $date : date('Y-m-d');
        $this->reportCutting = null;
        $this->rowCount = 0;
    }

    public function title(): string
    {
        return localeDateFormat($this->date);
    }

    public function view(): View
    {
        $additionalQuery = "";

        if ($this->date) {
            $additionalQuery .= " and DATE(form_cut_input.waktu_mulai) = '".$this->date."'";
        }

        $reportCutting = DB::select("
            SELECT
                marker_cutting.tgl_form_cut,
                marker_cutting.meja,
                marker_cutting.buyer,
                marker_cutting.act_costing_ws,
                marker_cutting.style,
                marker_cutting.color,
                marker_cutting.panel,
                marker_cutting.cons_ws,
                marker_cutting.unit,
                COALESCE(marker_cutting.notes, '-') notes,
                SUM(marker_cutting.marker_gelar) marker_gelar,
                SUM(marker_cutting.spreading_gelar) spreading_gelar,
                SUM(marker_cutting.form_gelar) form_gelar
            FROM
                (
                    SELECT
                        marker_input.kode,
                        form_cut.meja,
                        form_cut.tgl_form_cut,
                        marker_input.buyer,
                        marker_input.act_costing_id,
                        marker_input.act_costing_ws,
                        marker_input.style,
                        marker_input.color,
                        marker_input.panel,
                        marker_input.cons_ws,
                        marker_input.unit_panjang_marker unit,
                        COALESCE(marker_input.notes, form_cut.notes) notes,
                        marker_input.gelar_qty marker_gelar,
                        SUM(form_cut.qty_ply) spreading_gelar,
                        SUM(COALESCE(form_cut.total_lembar, form_cut.detail)) form_gelar
                    FROM
                        marker_input
                        INNER JOIN
                            (
                                SELECT
                                    meja.`name` meja,
                                    DATE(form_cut_input.waktu_mulai) tgl_form_cut,
                                    form_cut_input.id_marker,
                                    form_cut_input.no_form,
                                    form_cut_input.qty_ply,
                                    form_cut_input.total_lembar,
                                    form_cut_input.notes,
                                    SUM(form_cut_input_detail.lembar_gelaran) detail
                                FROM
                                    form_cut_input
                                    LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                                WHERE
                                    form_cut_input.`status` != 'SPREADING'
                                    AND form_cut_input.waktu_mulai is not null
                                    ".$additionalQuery."
                                GROUP BY
                                    form_cut_input.no_form
                            ) form_cut on form_cut.id_marker = marker_input.kode
                        where
                            (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                        group by
                            marker_input.id,
                            form_cut.tgl_form_cut
                ) marker_cutting
            GROUP BY
                marker_cutting.meja,
                marker_cutting.act_costing_id,
                marker_cutting.color,
                marker_cutting.panel,
                marker_cutting.tgl_form_cut
            ORDER BY
                marker_cutting.panel,
                marker_cutting.meja,
                marker_cutting.act_costing_id,
                marker_cutting.color,
                marker_cutting.tgl_form_cut
        ");

        $this->reportCutting = collect($reportCutting);

        $this->rowCount = count($reportCutting) + 2;

        return view('cutting.report.export.report-cutting', [
            'reportCutting' => collect($reportCutting),
            'date' => $this->date,
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
        $currentRow = 1;
        foreach ( $event->getConcernable()->reportCutting->groupBy('panel') as $cutting ) {
            if ($currentRow > 1) {
                $event->sheet->styleCells(
                    'A'.$currentRow.':X' . ($currentRow+$event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count()+1),
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]
                );

                $event->sheet->styleCells(
                    'F'.($currentRow+$event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count()+2).':X' . ($currentRow+$event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count()+2),
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]
                );
            } else {
                $event->sheet->styleCells(
                    'A1:X' . ($event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count()+2),
                    [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]
                );

                $event->sheet->styleCells(
                    'F'.($event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count()+3).':X' . ($event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count()+3),
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

            $currentRow += $event->getConcernable()->reportCutting->where('panel', $cutting->first()->panel)->count() + 5;
        }
    }

    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 15,
    //         'C' => 15,
    //         'D' => 15,
    //         'E' => 15,
    //         'G' => 25,
    //     ];
    // }
}
