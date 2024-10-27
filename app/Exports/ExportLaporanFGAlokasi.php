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

class ExportLaporanFGAlokasi implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function __construct()
    {
        $this->rowCount = 0;
    }


    public function view(): View

    {
        $data = DB::select("select
a.lokasi,
l.lokasi penempatan,
id_ppic_master_so,
a.id_so_det,
barcode,
a.qty,
po,
no_carton,
buyer,
color,
m.size,
m.dest,
m.buyer,
ws
from
(
select * from fg_fg_in  where status  = 'NORMAL'
) a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
left join fg_fg_master_lok l on a.lokasi = l.kode_lok
inner join master_sb_ws m on a.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
where b.id_fg_in is null and a.lokasi != '-'
order by l.kode_lok asc, ws asc, color asc, urutan asc
        ");


        $this->rowCount = count($data) + 4;


        return view('finish_good.export_finish_good_alokasi', [
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
            'A4:K' . $event->getConcernable()->rowCount,
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
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
