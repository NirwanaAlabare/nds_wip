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
            select count(o.barcode) tot,
            o.po,
            no_carton,
            o.barcode,
            m.color,
            m.size,
            m.ws,
            o.dest,
            concat((DATE_FORMAT(o.tgl_trans,  '%d')), '-', left(DATE_FORMAT(o.tgl_trans,  '%M'),3),'-',DATE_FORMAT(o.tgl_trans,  '%Y')
            ) tgl_trans_fix,
            o.created_by,
            o.created_at
            from packing_packing_out_scan o
            inner join ppic_master_so p on o.po = p.po and o.barcode = p.barcode
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            where o.tgl_trans >= '$this->from' and o.tgl_trans <= '$this->to'
            group by po, no_carton, tgl_trans, barcode, dest
            order by created_at desc
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
            'D' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
