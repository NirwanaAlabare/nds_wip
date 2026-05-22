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

class ExportLaporanFGStokMutasiInternal implements FromView, WithEvents, ShouldAutoSize
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
                no_mut,
                tgl_mut,
                CONCAT(
                    (DATE_FORMAT(tgl_mut, '%d')),
                    '-',
                    LEFT(DATE_FORMAT(tgl_mut, '%M'),3),
                    '-',
                    DATE_FORMAT(tgl_mut, '%Y')
                ) tgl_mut_fix,
                buyer,
                ws,
                brand,
                styleno,
                color,
                size,
                a.qty_mut,
                a.grade,
                lokasi_asal,
                no_carton_asal,
                lokasi_tujuan,
                no_carton_tujuan,
                a.created_by,
                created_at,
                bpb.no_trans,
                bppb.no_trans_out
            FROM fg_stok_mutasi_log a
            INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
            INNER JOIN (
                SELECT
                    no_mutasi,
                    no_trans_out
                FROM fg_stok_bppb
                WHERE cancel = 'N'
                    AND mutasi = 'Y'
                GROUP BY no_trans_out
            ) bppb
                ON a.no_mut = bppb.no_mutasi
            INNER JOIN (
                SELECT
                    no_mutasi,
                    no_trans
                FROM
                (
                    SELECT
                        no_mutasi,
                        no_trans
                    FROM fg_stok_bpb
                    WHERE cancel = 'N'
                        AND mutasi = 'Y'

                    UNION ALL

                    SELECT
                        no_mutasi,
                        no_trans
                    FROM fg_stok_bpb_scan
                    WHERE cancel = 'N'
                        AND mutasi = 'Y'
                ) bpb_all

                GROUP BY no_mutasi, no_trans

            ) bpb
                ON a.no_mut = bpb.no_mutasi
            WHERE tgl_mut >= '$this->from' AND tgl_mut <= '$this->to'
            ORDER BY SUBSTR(no_trans,14) DESC
        ");


        $this->rowCount = count($data) + 4;


        return view('fg-stock.export_mutasi_int_fg_stock', [
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
