<?php

namespace App\Exports\MgtReportDashboard;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SheetEarning implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    use Exportable;

    /** columns (1-based) holding percentages: Eff + the four "% Of Earn" */
    private const PERCENT_COLS = [8, 12, 17, 21, 25];

    protected $rawData;

    public function __construct(array $rawData)
    {
        $this->rawData = $rawData;
    }

    public function view(): View
    {
        return view('management_report.export_excel_laporan_earning', [
            'rawData' => $this->rawData,
        ]);
    }

    public function title(): string
    {
        return 'mgt_report_earning';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet         = $event->sheet->getDelegate();
                $highestRow    = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A2:' . $highestColumn . '3')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD9EDF7'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                if ($highestRow > 3) {
                    for ($i = 5; $i <= Coordinate::columnIndexFromString($highestColumn); $i++) {
                        $colLetter = Coordinate::stringFromColumnIndex($i);
                        $format    = in_array($i, self::PERCENT_COLS, true) ? '0.00%' : '#,##0.00';

                        $sheet->getStyle($colLetter . '4:' . $colLetter . $highestRow)->applyFromArray([
                            'numberFormat' => ['formatCode' => $format],
                            'alignment'    => [
                                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }
                }

                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->freezePane('A4');
                $sheet->setTitle($this->title());
            },
        ];
    }
}
