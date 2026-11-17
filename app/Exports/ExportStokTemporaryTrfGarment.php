<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportStokTemporaryTrfGarment implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $data, $rowCount;

    public function __construct($data)
    {
        $this->data = $data;
        // 3 baris judul + 1 header + data + 1 total
        $this->rowCount = count($data) + 5;
    }

    public function view(): View
    {
        return view('packing.export_excel_stok_temporary', [
            'data' => $this->data,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A4:I' . $this->rowCount)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
