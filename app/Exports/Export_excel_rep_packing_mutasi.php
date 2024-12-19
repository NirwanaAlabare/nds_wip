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

class Export_excel_rep_packing_mutasi implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;


    // protected $from, $to;

    // public function __construct($from, $to)
    public function __construct()
    {

        // $this->from = $from;
        // $this->to = $to;
        $this->rowCount = 0;
    }


    public function view(): View

    {
        $data = DB::select("SELECT p.po, m.buyer, m.ws, m.color, m.size, p.dest,a.barcode, a.no_carton,a.qty qty_pl,
        coalesce(b.tot_scan,0) tot_scan, coalesce(c.qty_fg_in,0) qty_fg_in, coalesce(qty_fg_out,0) qty_fg_out , lokasi, coalesce(a.qty,0) - coalesce(qty_fg_out,0) balance
from packing_master_packing_list a
left join
	(
	select count(barcode) tot_scan, po, barcode, no_carton from packing_packing_out_scan
	group by po, barcode, no_carton
	) b on a.barcode = b.barcode and a.po = b.po and a.no_carton = b.no_carton
left join
	(
	select sum(qty) qty_fg_in, po, barcode, no_carton, lokasi from fg_fg_in where status = 'NORMAL' group by po, barcode, no_carton
	) c on a.barcode = c.barcode and a.po = c.po and a.no_carton = c.no_carton
left join
	(
	select sum(qty) qty_fg_out, po, barcode, no_carton from fg_fg_out where status = 'NORMAL' group by po, barcode, no_carton
	) d on a.barcode = d.barcode and a.po = d.po and a.no_carton = d.no_carton
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
order by a.po asc, buyer asc, no_carton asc, urutan asc
        ");


        $this->rowCount = count($data) + 4;


        return view('packing.export_excel_rep_packing_mutasi', [
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
            'A4:N' . $event->getConcernable()->rowCount,
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
            'B' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
