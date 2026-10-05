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
use App\Http\Controllers\Cutting\ReportCuttingController;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportReportCuttingDaily implements FromView, WithEvents, ShouldAutoSize /*WithColumnWidths,*/
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
        $this->reportCutting = null;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $reportCutting = collect(
            DB::select(ReportCuttingController::buildCuttingDailyQuery($this->dateFrom, $this->dateTo))
        );

        $this->reportCutting = collect($reportCutting);

        $this->rowCount = count($reportCutting) + 6;

        return view('cutting.report.export.report-cutting-daily', [
            'reportCutting' => collect($reportCutting),
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
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
            'A5:L' . ($event->getConcernable()->rowCount),
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

}
?>
