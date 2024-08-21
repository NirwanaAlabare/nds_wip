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

class ExportLaporanPackingMasterkarton implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;


    protected $from, $to;

    public function __construct($from, $to)
    {

        $this->from = $from;
        $this->to = $to;
        $this->rowCount = 0;
    }


    public function view(): View

    {
        $data = DB::select("SELECT
mc.no_carton,
mc.po,
a.barcode,
a.tot_isi,
a.dest,
a.notes,
a.ws,
a.color,
a.size,
a.tgl_shipment,
concat((DATE_FORMAT(a.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(a.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(a.tgl_shipment,  '%Y')
 ) tgl_shipment_fix,
a.buyer
from packing_master_carton mc
inner join ( select * from ppic_master_so where tgl_shipment >= '$this->from' and tgl_shipment <= '$this->to' group by po) p
on mc.po = p.po
left join
(
select
a.no_carton,
a.po,
a.barcode,
tot_isi,
a.dest,
a.notes,
m.ws,
m.color,
m.size,
p.tgl_shipment,
m.buyer
from
(
select count(barcode) tot_isi,barcode,no_carton, po, dest, notes
from packing_packing_out_scan
group by barcode, po, dest, notes, no_carton
) a
inner join (select * from ppic_master_so where tgl_shipment >= '$this->from' and tgl_shipment <= '$this->to' group by po) p on a.po = p.po and a.barcode = p.barcode and a.dest = p.dest
inner join master_sb_ws m on p.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
) a on mc.po = a.po and mc.no_carton = a.no_carton
        ");


        $this->rowCount = count($data) + 4;


        return view('packing.export_excel_packing_master_karton', [
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to
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
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
