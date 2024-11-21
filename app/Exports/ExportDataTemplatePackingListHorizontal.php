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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

// class ExportLaporanPemakaian implements FromCollection
// {
//     /**
//      * @return \Illuminate\Support\Collection
//      */
//     public function collection()
//     {
//         return Marker::all();
//     }
// }

class ExportDataTemplatePackingListHorizontal implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;


    protected $po, $dest;

    public function __construct($po, $dest)
    {

        $this->po = $po;
        $this->dest = $dest;
        $this->rowCount = 0;
    }


    public function view(): View

    {
        $data = DB::select("SELECT po, p.dest from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where po = '$this->po' and p.dest = '$this->dest'
group by po, p.dest
        ");


        $this->rowCount = count($data) + 1;


        return view('packing.export_excel_data_template_packing_list_horizontal', [
            'data' => $data,
            'po' => $this->po,
            'dest' => $this->dest
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
            'A1:E' . $event->getConcernable()->rowCount,
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

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
