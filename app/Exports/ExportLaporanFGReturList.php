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

class ExportLaporanFGReturList implements FromView, WithEvents, ShouldAutoSize
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
a.id,
no_sb,
tgl_pengeluaran,
concat((DATE_FORMAT(a.tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(a.tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(a.tgl_pengeluaran,  '%Y')) tgl_pengeluaran_fix,
a.id_so_det,
a.barcode,
a.po,
m.ws,
m.color,
m.size,
m.dest,
no_carton,
a.qty,
notes,
a.buyer,
invno,
remark,
jenis_dok,
a.created_at,
a.created_by
from fg_fg_out a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tgl_pengeluaran >='$this->from' and tgl_pengeluaran <= '$this->to' and a.status = 'RETUR'
order by a.created_at desc
        ");


        $this->rowCount = count($data) + 4;


        return view('finish_good.export_finish_good_retur_list', [
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
            'A4:P' . $event->getConcernable()->rowCount,
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
