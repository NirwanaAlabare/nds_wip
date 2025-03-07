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

class ExportLaporanPengeluaranFGStokBPPB implements FromView, WithEvents, ShouldAutoSize
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
        select
        a.id,
        no_trans_out,
        tgl_pengeluaran,
        concat((DATE_FORMAT(tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(tgl_pengeluaran,  '%Y')
        ) tgl_pengeluaran_fix,
        a.id_so_det,
        m.product_group,
        m.product_item,
        buyer,
        ws,
        brand,
        styleno,
        color,
        size,
        a.qty_out,
        a.grade,
        no_carton,
        lokasi,
        tujuan_pengeluaran,
        tujuan,
        no_dok,
        a.created_by,
        created_at
        from fg_stok_bppb a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        where tgl_pengeluaran >= '$this->from' and tgl_pengeluaran <= '$this->to'
        order by tgl_pengeluaran desc,substr(no_trans_out,14) desc
        ");


        $this->rowCount = count($data) + 4;


        return view('fg-stock.export_bppb_fg_stock', [
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
            'A4:U' . $event->getConcernable()->rowCount,
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
