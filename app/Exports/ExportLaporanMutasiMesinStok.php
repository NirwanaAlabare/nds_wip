<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportLaporanMutasiMesinStok implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    public function __construct()
    {
        $this->rowCount = 0;
    }


    public function view(): View

    {

        $data = DB::select("SELECT
        m.jenis_mesin, m.brand, count(m.id_qr) total, 'UNIT' satuan
        from master_mesin m
        left join
        (
        select * from mut_mesin_input group by id_qr
        ) mut on m.id_qr = mut.id_qr
        where mut.id_qr is not null
        group by  jenis_mesin, brand
        order by jenis_mesin asc, brand asc
        ");


        $this->rowCount = count($data) + 3;


        return view('mut-mesin.export-stok-mesin', [
            'data' => $data
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
            'A3:E' . $event->getConcernable()->rowCount,
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
