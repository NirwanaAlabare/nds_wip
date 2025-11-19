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

class ExportLaporanPackingOut implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
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
        $data = DB::select("
SELECT
o.tot,
DATE_FORMAT(o.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
p.po,
p.barcode,
sd.color,
sd.size,
no_carton,
ac.kpno as ws,
ac.styleno,
sd.reff_no,
p.dest,
DATE_FORMAT(o.tgl_akt_input, '%d-%m-%Y %H:%i:%s') AS tgl_akt_input,
DATE_FORMAT(p.tgl_shipment, '%d-%m-%Y') AS tgl_shipment,
o.created_by
from
(
select
count(barcode) as tot,
created_by,
po, no_carton, tgl_trans, barcode, dest,max(created_at)tgl_akt_input
from packing_packing_out_scan where tgl_trans >= '$this->from' and tgl_trans <= '$this->to'
group by po, no_carton, tgl_trans, barcode, dest
) o
inner join laravel_nds.ppic_master_so p on o.barcode = p.barcode and o.po = p.po
inner join signalbit_erp.so_det sd on p.id_so_det = sd.id
inner join signalbit_erp.so on sd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
where sd.cancel = 'N' and so.cancel_h = 'N'
order by o.tgl_trans desc, po asc
        ");


        $this->rowCount = count($data) + 4;


        return view('packing.export_excel_packing_out', [
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
            'A4:L' . $event->getConcernable()->rowCount,
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
            'D' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
