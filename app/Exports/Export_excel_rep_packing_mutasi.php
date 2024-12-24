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
        $data = DB::select("WITH Totals AS (
    SELECT
        po,
        barcode,
        no_carton,
        COUNT(barcode) AS tot_scan
    FROM packing_packing_out_scan
    GROUP BY po, barcode, no_carton
),
FgIn AS (
    SELECT
        po,
        barcode,
        no_carton,
        SUM(qty) AS qty_fg_in,
        lokasi
    FROM fg_fg_in
    WHERE status = 'NORMAL'
    GROUP BY po, barcode, no_carton, lokasi
),
FgOut AS (
    SELECT
        po,
        barcode,
        no_carton,
        SUM(qty) AS qty_fg_out
    FROM fg_fg_out
    WHERE status = 'NORMAL'
    GROUP BY po, barcode, no_carton
)

SELECT
    p.po,
    m.buyer,
    m.ws,
    m.color,
    m.size,
    a.dest,
    a.barcode,
    a.no_carton,
    a.qty AS qty_pl,
    COALESCE(b.tot_scan, 0) AS tot_scan,
    COALESCE(c.qty_fg_in, 0) AS qty_fg_in,
    COALESCE(d.qty_fg_out, 0) AS qty_fg_out,
    c.lokasi,
    COALESCE(a.qty, 0) - COALESCE(d.qty_fg_out, 0) AS balance
FROM packing_master_packing_list a
LEFT JOIN Totals b ON a.barcode = b.barcode AND a.po = b.po AND a.no_carton = b.no_carton
LEFT JOIN FgIn c ON a.barcode = c.barcode AND a.po = c.po AND a.no_carton = c.no_carton
LEFT JOIN FgOut d ON a.barcode = d.barcode AND a.po = d.po AND a.no_carton = d.no_carton
INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
LEFT JOIN master_size_new msn ON m.size = msn.size
ORDER BY a.po ASC, m.buyer ASC, a.no_carton ASC;
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
