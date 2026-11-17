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

class ExportLaporanFGReturList implements FromView, WithEvents, ShouldAutoSize
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
        // Sumber & filter disamakan dengan PACKING CENTRAL PERIODE TERIMA RETURN (BPPB) di report mutasi
        $data = DB::connection('mysql_sb')->select("SELECT
                    b.bppbno_int AS no_sb,
                    b.bppbdate AS tgl_pengeluaran,
                    DATE_FORMAT(b.bppbdate, '%d-%b-%Y') AS tgl_pengeluaran_fix,
                    b.id_so_det,
                    COALESCE(pp.barcode, '-') AS barcode,
                    COALESCE(pp.po, '-') AS po,
                    ac.kpno AS ws,
                    sd.color,
                    sd.size,
                    sd.dest,
                    COALESCE(pp.no_carton, '-') AS no_carton,
                    b.qty,
                    '-' AS notes,
                    ms.Supplier AS buyer,
                    b.invno,
                    b.remark,
                    b.jenis_dok,
                    b.dateinput AS created_at,
                    b.username AS created_by
                FROM (
                    SELECT
                        id_so_det,
                        bppbno_int,
                        DATE(bppbdate) AS bppbdate,
                        invno,
                        remark,
                        jenis_dok,
                        dateinput,
                        username,
                        SUM( qty ) AS qty
                    FROM signalbit_erp.bppb b
                    WHERE b.id_so_det IS NOT NULL AND b.id_so_det != '0'
                        AND bppbno_int LIKE '%FG/RO%'
                        AND bppbdate BETWEEN '{$this->from} 00:00:00' AND '{$this->to} 23:59:59'
                        AND b.id_supplier NOT IN (458 , 927 , 2053)
                    GROUP BY
                        id_so_det, bppbno_int, DATE(bppbdate), invno, remark, jenis_dok, dateinput, username
                ) b
                LEFT JOIN signalbit_erp.so_det sd ON b.id_so_det = sd.id
                LEFT JOIN signalbit_erp.so ON sd.id_so = so.id
                LEFT JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                LEFT JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
                LEFT JOIN (
                    SELECT
                        p.id_so_det,
                        p.po,
                        p.barcode,
                        pl.no_carton
                    FROM laravel_nds.ppic_master_so p
                    LEFT JOIN laravel_nds.packing_master_packing_list pl ON pl.po = p.po AND pl.id_so_det = p.id_so_det
                    WHERE p.id_so_det IN (
                        SELECT id_so_det FROM signalbit_erp.bppb
                        WHERE bppbno_int LIKE '%FG/RO%'
                            AND bppbdate BETWEEN '{$this->from} 00:00:00' AND '{$this->to} 23:59:59'
                    )
                    GROUP BY p.id_so_det
                ) pp ON b.id_so_det = pp.id_so_det
                ORDER BY b.dateinput DESC
        ");

        /* Query lama dari laravel_nds.fg_fg_out
        $data = DB::select("SELECT
a.id,
no_sb,
tgl_pengeluaran,
concat((DATE_FORMAT(a.tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(a.tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(a.tgl_pengeluaran,  '%Y')) tgl_pengeluaran_fix,
a.id_so_det,
a.barcode,
a.po,
m.ws,
m.color,
m.size,
m.dest,
no_carton,
a.qty,
notes,
a.buyer,
invno,
remark,
jenis_dok,
a.created_at,
a.created_by
from fg_fg_out a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tgl_pengeluaran >='$this->from' and tgl_pengeluaran <= '$this->to' and a.status = 'RETUR'
group by
no_sb,
tgl_pengeluaran,
concat((DATE_FORMAT(a.tgl_pengeluaran,  '%d')), '-', left(DATE_FORMAT(a.tgl_pengeluaran,  '%M'),3),'-',DATE_FORMAT(a.tgl_pengeluaran,  '%Y')),
a.id_so_det,
a.barcode,
a.po,
m.ws,
m.color,
m.size,
m.dest,
no_carton,
a.qty,
notes,
a.buyer,
invno,
remark,
jenis_dok,
a.created_at,
a.created_by
order by a.created_at desc
        ");


        */

        $this->rowCount = count($data) + 4;


        return view('finish_good.export_finish_good_retur_list', [
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
            'A4:P' . $event->getConcernable()->rowCount,
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
