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

class ExportLaporanPenerimaanFGStokScanBPB implements FromView, WithEvents, ShouldAutoSize
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
                qr_code,
                CONCAT(
                    DATE_FORMAT(tgl_terima, '%d'), '-',
                    LEFT(DATE_FORMAT(tgl_terima, '%M'), 3), '-',
                    DATE_FORMAT(tgl_terima, '%Y')
                ) AS tgl_terima_fix,
                buyer,
                brand,
                styleno,
                ws,
                color,
                size,
                a.no_carton,
                a.qty,
                sumber_pemasukan,
                a.id_so_det,
                created_by,
                created_at
            FROM fg_stok_bpb_scan a
            LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
            WHERE tgl_terima >= '$this->from'
            AND tgl_terima <= '$this->to'
            ORDER BY a.id DESC
        ");

        $this->rowCount = count($data) + 4;

        return view('fg-stock.export_bpb_fg_stock_scan', [
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
            'A4:M' . $event->getConcernable()->rowCount,
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
