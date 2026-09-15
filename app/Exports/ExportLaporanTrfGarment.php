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

class ExportLaporanTrfGarment implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    protected $from, $to, $sumber;

    public function __construct($from, $to, $sumber)
    {

        $this->from = $from;
        $this->to = $to;
        $this->sumber = $sumber;
        $this->rowCount = 0;
    }


    public function view(): View
    {
        $data = DB::select("
            SELECT *
                FROM (
                    SELECT
                        a.no_trans,
                        CONCAT(
                            DATE_FORMAT(tgl_trans, '%d'), '-',
                            LEFT(DATE_FORMAT(tgl_trans, '%M'), 3), '-',
                            DATE_FORMAT(tgl_trans, '%Y')
                        ) AS tgl_trans_fix,
                        a.line,
                        UPPER(a.po) AS po,
                        m.ws,
                        m.color,
                        m.size,
                        m.styleno,
                        a.qty,
                        IF(a.qty - c.qty_in = 0, 'Full', '-') AS status,
                        a.id,
                        UPPER(
                            CASE
                                WHEN a.tujuan = 'Packing' THEN 'Packing Central'
                                ELSE a.tujuan
                            END
                        ) AS tujuan,
                        'PACKING LINE' AS sumber,
                        a.created_at,
                        a.created_by
                    FROM packing_trf_garment a
                    LEFT JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
                    INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    LEFT JOIN (
                        SELECT
                            id_trf_garment,
                            SUM(qty) AS qty_in
                        FROM packing_packing_in
                        WHERE sumber = 'Sewing'
                        GROUP BY id_trf_garment
                    ) c ON a.id = c.id_trf_garment
                    WHERE tgl_trans >= '$this->from'
                    AND tgl_trans <= '$this->to'

                    UNION

                    SELECT
                        a.no_trans,
                        CONCAT(
                            DATE_FORMAT(tgl_trans, '%d'), '-',
                            LEFT(DATE_FORMAT(tgl_trans, '%M'), 3), '-',
                            DATE_FORMAT(tgl_trans, '%Y')
                        ) AS tgl_trans_fix,
                        'TEMPORARY PACKING' AS line,
                        UPPER(a.po) AS po,
                        m.ws,
                        m.color,
                        m.size,
                        m.styleno,
                        a.qty,
                        IF(a.qty - c.qty_in = 0, 'Full', '-') AS status,
                        a.id,
                        'PACKING CENTRAL' AS tujuan,
                        'TEMPORARY PACKING' AS sumber,
                        a.created_at,
                        a.created_by
                    FROM packing_trf_garment_out_temporary a
                    INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    LEFT JOIN (
                        SELECT
                            packing_trf_garment_out_temporary_id,
                            SUM(qty) AS qty_in
                        FROM packing_packing_in
                        WHERE sumber = 'TEMPORARY PACKING'
                        GROUP BY packing_trf_garment_out_temporary_id
                    ) c ON a.id = c.packing_trf_garment_out_temporary_id
                    WHERE tgl_trans >= '$this->from'
                    AND tgl_trans <= '$this->to'
                ) x
                WHERE x.sumber = '" . strtoupper($this->sumber) . "'
                ORDER BY x.created_at DESC
        ");

        $this->rowCount = count($data) + 5;


        return view('packing.export_excel_trf_garment', [
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
            'sumber' => $this->sumber,
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
            'A5:O' . $event->getConcernable()->rowCount,
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
