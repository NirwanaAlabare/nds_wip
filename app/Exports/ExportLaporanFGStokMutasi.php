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

class ExportLaporanFGStokMutasi implements FromView, WithEvents, ShouldAutoSize
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
        select mt.id_so_det,
        sum(qty_awal) qty_awal,
        sum(qty_in) qty_in,
        sum(qty_out) qty_out,
        sum(qty_awal) + sum(qty_in) - sum(qty_out) saldo_akhir,
        grade,
        lokasi,
        no_carton,
        buyer,
        color,
        m.size,
        ws,
        brand,
        styleno,
        m.product_group,
        m.product_item
        from
        (
            select id_so_det,sum(qty_in) - sum(qty_out) qty_awal,'0' qty_in,'0' qty_out, grade, lokasi, no_carton
            from
            (
            select id_so_det,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
            from fg_stok_bpb
            where tgl_terima < '$this->from'
            group by id_so_det, grade, lokasi, no_carton
            UNION
            select id_so_det,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
            from fg_stok_bppb
            where tgl_pengeluaran < '$this->from'
            group by id_so_det, grade, lokasi, no_carton
            ) sa
            group by id_so_det, grade, lokasi, no_carton
        union
        select id_so_det,'0' qty_awal,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
        from fg_stok_bpb
        where tgl_terima >= '$this->from' and tgl_terima <= '$this->to'
        group by id_so_det, grade, lokasi, no_carton
        union
        select id_so_det,'0' qty_awal,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
        from fg_stok_bppb
        where tgl_pengeluaran >= '$this->from' and tgl_pengeluaran <= '$this->to'
        group by id_so_det, grade, lokasi, no_carton
        )
        mt
        left join master_sb_ws m on mt.id_so_det = m.id_so_det
        left join master_size_new ms on m.size = ms.size
        group by mt.id_so_det, grade, lokasi, no_carton
        order by buyer asc, color asc, ms.urutan asc
        ");


        $this->rowCount = count($data) + 4;


        return view('fg-stock.export_mutasi_fg_stock', [
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
            'A4:Q' . $event->getConcernable()->rowCount,
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
