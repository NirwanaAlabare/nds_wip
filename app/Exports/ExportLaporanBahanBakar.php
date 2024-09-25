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

class ExportLaporanBahanBakar implements FromView, WithEvents, ShouldAutoSize
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
        $data = DB::select("SELECT a.*,
            concat((DATE_FORMAT(a.tgl_trans,  '%d')), '-', left(DATE_FORMAT(a.tgl_trans,  '%M'),3),'-',DATE_FORMAT(a.tgl_trans,  '%Y')) tgl_trans_fix,
            c.jns_bhn_bakar,
            c.nm_bhn_bakar,
            CONCAT('Rp. ', FORMAT(tot_biaya,2,'id_ID')) AS tot_biaya_fix,
            concat(jml, ' L') jml_fix,
            CONCAT('Rp. ', FORMAT(realisasi_biaya,2,'id_ID')) AS tot_biaya_realisasi_fix,
            concat(realisasi_jml, ' L') realisasi_jml_fix,
            DATE_FORMAT(realisasi_tgl, '%d-%M-%Y %H:%i:%s') realisasi_tgl_fix,
            if (a.status = 'APPROVE',concat(a.status, ' ', a.user_app, ' (',DATE_FORMAT(tgl_app, '%d-%M-%Y %H:%i:%s'), ')'),a.status) status_fix
            FROM ga_trans_pengajuan_bhn_bakar a
            inner join ga_master_kendaraan b on a.plat_no = b.plat_no
            inner join ga_master_bahan_bakar c on a.id_bhn_bakar = c.id
            where a.tgl_trans >= '$this->from' and a.tgl_trans <= '$this->to'
            order by tgl_trans asc, no_trans asc
        ");


        $this->rowCount = count($data) + 4;


        return view('ga.export_excel_bahan_bakar', [
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

    // public function columnFormats(): array
    // {
    //     return [
    //         'F' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }
}
