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

class ExportLaporanPenerimaanFGStokLokasiScanBPB implements FromView, WithEvents, ShouldAutoSize
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
                a.no_trans,
                tgl_terima,
                concat((DATE_FORMAT(a.created_at,  '%d')), '-', left(DATE_FORMAT(a.created_at,  '%M'),3),'-',DATE_FORMAT(a.created_at,  '%Y')
                ) tgl_terima_fix,
                buyer,
                ws,
                brand,
                styleno,
                color,
                size,
                b.qty,
                b.grade,
                b.no_carton,
                lokasi,
                sumber_pemasukan,
                a.created_by,
                a.created_at,
                a.qr_code
            from fg_stok_bpb_lokasi_scan a
            left join fg_stok_bpb_scan b ON b.qr_code = a.qr_code
            left join master_sb_ws m on b.id_so_det = m.id_so_det
            where date(a.created_at) >= '$this->from' and date(a.created_at) <= '$this->to'
            order by a.id desc
        ");

        $this->rowCount = count($data) + 4;


        return view('fg-stock.export_bpb_fg_stock_lokasi_scan', [
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
            'A4:J' . $event->getConcernable()->rowCount,
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
