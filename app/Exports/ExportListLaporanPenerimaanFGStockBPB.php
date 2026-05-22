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

class ExportListLaporanPenerimaanFGStockBPB implements FromView, WithEvents, ShouldAutoSize
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
                a.id,
                a.no_trans,
                a.tgl_terima,
                CONCAT(
                    DATE_FORMAT(a.tgl_terima, '%d'), '-',
                    LEFT(DATE_FORMAT(a.tgl_terima, '%M'), 3), '-',
                    DATE_FORMAT(a.tgl_terima, '%Y')
                ) AS tgl_terima_fix,
                buyer,
                ws,
                brand,
                styleno,
                color,
                size,
                a.qty,
                a.grade,
                no_carton,
                lokasi,
                sumber_pemasukan,
                created_by,
                created_at
            FROM fg_stok_bpb a
            LEFT JOIN master_sb_ws m
                ON a.id_so_det = m.id_so_det
            WHERE a.tgl_terima BETWEEN '$this->from' AND '$this->to'

            UNION ALL

            SELECT
                a.id,
                a.no_trans,
                a.tgl_terima,
                CONCAT(
                    DATE_FORMAT(a.tgl_terima, '%d'), '-',
                    LEFT(DATE_FORMAT(a.tgl_terima, '%M'), 3), '-',
                    DATE_FORMAT(a.tgl_terima, '%Y')
                ) AS tgl_terima_fix,
                buyer,
                ws,
                brand,
                styleno,
                color,
                size,
                a.qty,
                a.grade,
                no_carton,
                lokasi,
                sumber_pemasukan,
                created_by,
                created_at
            FROM fg_stok_bpb_scan a
            LEFT JOIN master_sb_ws m
                ON a.id_so_det = m.id_so_det
            WHERE a.tgl_terima BETWEEN '$this->from' AND '$this->to'

            ORDER BY SUBSTR(no_trans, 13) DESC
        ");


        $this->rowCount = count($data) + 4;


        return view('fg-stock.export_laporan_penerimaan_bpb_fg_stock', [
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
