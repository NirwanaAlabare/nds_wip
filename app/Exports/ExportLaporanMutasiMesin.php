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

class ExportLaporanMutasiMesin implements FromView, WithEvents, ShouldAutoSize
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
        // $data = DB::select("
        // select *,cast(right(line,2) as UNSIGNED) urutan from
        // (
        // select max(a.id)id from
        // (
        // select * from mut_karyawan_input
        // where tgl_pindah <= '$this->from'
        // union all
        // select * from mut_karyawan_input
        // where tgl_pindah >= '$this->to'
        // ) a
        // group by a.nik
        // ) b
        // inner join  mut_karyawan_input c on b.id = c.id
        // order by tgl_pindah asc,nm_karyawan asc, urutan asc
        // ");

        $data = DB::select("
        select a.id, b.tgl_pindah,b.*,b.updated_at updated_data, c.*,DATE_FORMAT(b.tgl_pindah, '%d-%m-%Y') tgl_pindah_fix
        from (
            select * from (
            select max(id) id,tgl_pindah from mut_mesin_input a
            where tgl_pindah <= '$this->from'
            group by id_qr
            union all
            select max(id) id,tgl_pindah from mut_mesin_input a
            where tgl_pindah >= '$this->from' and tgl_pindah <= '$this->to'
            group by id_qr
            ) x group by tgl_pindah, id
            ) a
            inner join mut_mesin_input b on a.id = b.id
						inner join master_mesin c on b.id_qr = c.id_qr
            order by tgl_pindah asc,cast(right(line,2) as UNSIGNED) asc
        ");


        $this->rowCount = count($data) + 3;


        return view('mut-mesin.export', [
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
            'A3:I' . $event->getConcernable()->rowCount,
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
