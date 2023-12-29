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

class ExportLaporanMutasiKaryawan implements FromView, WithEvents, ShouldAutoSize
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

        $data = DB::connection('mysql_hris')->select("
        select * from (
            select a.id, b.tgl_pindah,b.nik,b.nm_karyawan,b.line,b.line_asal,b.updated_at, absen_masuk_kerja,status_aktif,tanggal_berjalan,b.enroll_id from (
            select * from (
            select max(id) id,tgl_pindah from mut_karyawan_input a
            where tgl_pindah <= '$this->from'
            group by nik
            union all
            select max(id) id,tgl_pindah from mut_karyawan_input a
            where tgl_pindah >= '$this->from' and tgl_pindah <= '$this->to'
            group by nik
            ) x group by tgl_pindah, id
            ) a
            inner join mut_karyawan_input b on a.id = b.id
            left join (select enroll_id, absen_masuk_kerja,tanggal_berjalan, status_aktif from master_data_absen_kehadiran where tanggal_berjalan >= '$this->from' and tanggal_berjalan <= '$this->to' and status_aktif = 'AKTIF' group by tanggal_berjalan, enroll_id) c on b.enroll_id = c.enroll_id
            )master_karyawan
            where status_aktif = 'AKTIF' or status_aktif is null
            group by enroll_id,tanggal_berjalan
            order by tanggal_berjalan asc,cast(right(line,2) as UNSIGNED) asc, nm_karyawan asc
        ");


        $this->rowCount = count($data) + 3;


        return view('Mutasi_karyawan.export', [
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
            'A3:G' . $event->getConcernable()->rowCount,
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
