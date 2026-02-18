<?php

// namespace App\Exports;

// use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\Exportable;
// use Maatwebsite\Excel\Concerns\FromView;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Events\AfterSheet;
// use Maatwebsite\Excel\Sheet;
// use Maatwebsite\Excel\Concerns\WithColumnFormatting;
// use Maatwebsite\Excel\Concerns\WithEvents;
// use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
// use DB;

// Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
//     $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
// });


// class Export_excel_rep_packing_mutasi implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
// {
//     use Exportable;

//     public function __construct()
//     {
//         $this->rowCount = 0;
//     }


//     public function view(): View

//     {
//         $data = DB::select("WITH Totals AS (
//     SELECT
//         po,
//         barcode,
//         no_carton,
//         COUNT(barcode) AS tot_scan
//     FROM packing_packing_out_scan
//     GROUP BY po, barcode, no_carton
// ),
// FgIn AS (
//     SELECT
//         po,
//         barcode,
//         no_carton,
//         SUM(qty) AS qty_fg_in,
//         lokasi
//     FROM fg_fg_in
//     WHERE status = 'NORMAL'
//     GROUP BY po, barcode, no_carton, lokasi
// ),
// FgOut AS (
//     SELECT
//         po,
//         barcode,
//         no_carton,
//         SUM(qty) AS qty_fg_out
//     FROM fg_fg_out
//     WHERE status = 'NORMAL'
//     GROUP BY po, barcode, no_carton
// )

// SELECT
//     p.po,
//     m.buyer,
//     m.ws,
//     m.color,
//     m.size,
//     a.dest,
//     a.barcode,
//     a.no_carton,
//     a.qty AS qty_pl,
//     COALESCE(b.tot_scan, 0) AS tot_scan,
//     COALESCE(c.qty_fg_in, 0) AS qty_fg_in,
//     COALESCE(d.qty_fg_out, 0) AS qty_fg_out,
//     c.lokasi,
//     COALESCE(a.qty, 0) - COALESCE(d.qty_fg_out, 0) AS balance
// FROM packing_master_packing_list a
// LEFT JOIN Totals b ON a.barcode = b.barcode AND a.po = b.po AND a.no_carton = b.no_carton
// LEFT JOIN FgIn c ON a.barcode = c.barcode AND a.po = c.po AND a.no_carton = c.no_carton
// LEFT JOIN FgOut d ON a.barcode = d.barcode AND a.po = d.po AND a.no_carton = d.no_carton
// INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
// INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
// LEFT JOIN master_size_new msn ON m.size = msn.size
// ORDER BY a.po ASC, m.buyer ASC, a.no_carton ASC;
//         ");


//         $this->rowCount = count($data) + 4;


//         return view('packing.export_excel_rep_packing_mutasi', [
//             'data' => $data
//         ]);
//     }

//     public function registerEvents(): array
//     {
//         return [
//             AfterSheet::class => [self::class, 'afterSheet']
//         ];
//     }



//     public static function afterSheet(AfterSheet $event)
//     {

//         $event->sheet->styleCells(
//             'A4:N' . $event->getConcernable()->rowCount,
//             [
//                 'borders' => [
//                     'allBorders' => [
//                         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                         'color' => ['argb' => '000000'],
//                     ],
//                 ],
//             ]
//         );
//     }

//     public function columnFormats(): array
//     {
//         return [
//             'B' => NumberFormat::FORMAT_NUMBER,
//         ];
//     }
// }

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

class Export_excel_rep_packing_mutasi implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;



    public function __construct(
        $from,
        $to
    ) {
        $this->from = $from ?: date('Y-m-d');
        $this->to   = $to   ?: date('Y-m-d');
    }


    public function view(): View
    {

        $tgl_awal = $this->from;
        $tgl_akhir = $this->to;

        $tanggal_saldo_awal = '2026-01-01';

        $tgl_saldo_akhir = date(
            'Y-m-d',
            strtotime($tgl_awal . ' -1 day')
        );


        $data_mut = DB::select("
                            WITH
                            pos_saldo_agg AS (
                                SELECT
                                    p.id_so_det,
                                    SUM(a.total_scan) AS total_keluar
                                FROM (
                                    SELECT id_ppic, COUNT(*) AS total_scan
                                    FROM laravel_nds.packing_packing_out_scan
                                    WHERE tgl_trans >= '{$tanggal_saldo_awal} 00:00:00'
                                    AND tgl_trans < '{$tgl_awal} 00:00:00'
                                    GROUP BY id_ppic
                                ) a
                                JOIN ppic_master_so p ON p.id = a.id_ppic
                                WHERE p.id_so_det IS NOT NULL
                                GROUP BY p.id_so_det
                            ),

                            pos_periode_agg AS (
                                SELECT
                                    p.id_so_det,
                                    SUM(a.total_scan) AS total_keluar
                                FROM (
                                    SELECT id_ppic, COUNT(*) AS total_scan
                                    FROM laravel_nds.packing_packing_out_scan
                                    WHERE tgl_trans BETWEEN '{$tgl_awal} 00:00:00'
                                    AND '{$tgl_akhir} 23:59:59'
                                    GROUP BY id_ppic
                                ) a
                                JOIN ppic_master_so p ON p.id = a.id_ppic
                                WHERE p.id_so_det IS NOT NULL
                                GROUP BY p.id_so_det
                            ),

                            trx_union ( so_det_id, pl_saldo_awal, pl_rft, pl_reject, tg_saldo_awal, tg_masuk, tg_keluar, pc_saldo_awal, pc_terima, pc_keluar ) AS (
                            /* ================= SALDO AWAL ================= */
                                SELECT
                                    id_so_det AS so_det_id,
                                    CASE WHEN type = 'packing_line' THEN saldo ELSE 0 END AS pl_saldo_awal,
                                    0 AS pl_rft,
                                    0 AS pl_reject,
                                    CASE WHEN type = 'transfer_garment' THEN saldo ELSE 0 END AS tg_saldo_awal,
                                    0 AS tg_masuk,
                                    0 AS tg_keluar,
                                    CASE WHEN type = 'packing_center' THEN saldo ELSE 0 END AS pc_saldo_awal,
                                    0 AS pc_terima,
                                    0 AS pc_keluar
                                FROM
                                    sa_report_pck

                                UNION ALL

                            /* ================= PACKING LINE SALDO ================= */
                                SELECT
                                    x.so_det_id AS so_det_id,
                                    SUM( x.masuk - x.keluar ) AS pl_saldo_awal,
                                    0 AS pl_rft,
                                    0 AS pl_reject,
                                    0 AS tg_saldo_awal,
                                    0 AS tg_masuk,
                                    0 AS tg_keluar,
                                    0 AS pc_saldo_awal,
                                    0 AS pc_terima,
                                    0 AS pc_keluar
                                FROM
                                    (
                                    SELECT
                                        so_det_id,
                                        COUNT(*) AS masuk,
                                        0 AS keluar
                                    FROM
                                        signalbit_erp.output_rfts_packing_po
                                    WHERE
                                        so_det_id IS NOT NULL
                                        AND updated_at >= '{$tanggal_saldo_awal} 00:00:00'
                                        AND updated_at < '{$tgl_awal} 00:00:00'
                                    GROUP BY
                                        so_det_id

                                    UNION ALL

                                    SELECT
                                        pms.id_so_det AS so_det_id,
                                        0 AS masuk,
                                        SUM( tg.qty ) AS keluar
                                    FROM
                                        laravel_nds.packing_trf_garment tg
                                        JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
                                    WHERE
                                        pms.id_so_det IS NOT NULL
                                        AND tg.tgl_trans >= '{$tanggal_saldo_awal} 00:00:00'
                                        AND tg.tgl_trans < '{$tgl_awal} 00:00:00'
                                    GROUP BY
                                        pms.id_so_det
                                    ) x
                                GROUP BY
                                    x.so_det_id

                                UNION ALL

                            /* ================= PACKING LINE PERIODE ================= */
                                SELECT
                                    so_det_id AS so_det_id,
                                    0 AS pl_saldo_awal,
                                    SUM( type = 'RFT' ) AS pl_rft,
                                    SUM( type = 'REJECT' ) AS pl_reject,
                                    0 AS tg_saldo_awal,
                                    0 AS tg_masuk,
                                    0 AS tg_keluar,
                                    0 AS pc_saldo_awal,
                                    0 AS pc_terima,
                                    0 AS pc_keluar
                                FROM
                                    signalbit_erp.output_rfts_packing_po
                                WHERE
                                    so_det_id IS NOT NULL
                                    AND updated_at BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                                GROUP BY
                                    so_det_id

                                UNION ALL

                            /* ================= TRANSFER GARMENT SALDO ================= */
                                SELECT
                                    x.id_so_det AS so_det_id,
                                    0 AS pl_saldo_awal,
                                    0 AS pl_rft,
                                    0 AS pl_reject,
                                    SUM( x.masuk - x.keluar ) AS tg_saldo_awal,
                                    0 AS tg_masuk,
                                    0 AS tg_keluar,
                                    0 AS pc_saldo_awal,
                                    0 AS pc_terima,
                                    0 AS pc_keluar
                                FROM
                                    (
                                    SELECT
                                        pms.id_so_det,
                                        SUM( qty ) AS masuk,
                                        0 AS keluar
                                    FROM
                                        laravel_nds.packing_trf_garment tg
                                        JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
                                    WHERE
                                        pms.id_so_det IS NOT NULL
                                        AND tg.tgl_trans >= '{$tanggal_saldo_awal} 00:00:00'
                                        AND tg.tgl_trans < '{$tgl_awal} 00:00:00'
                                    GROUP BY
                                        pms.id_so_det

                                    UNION ALL

                                    SELECT
                                        pms.id_so_det,
                                        0,
                                        SUM( qty )
                                    FROM
                                        laravel_nds.packing_packing_in pi
                                        JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
                                    WHERE
                                        pms.id_so_det IS NOT NULL
                                        AND pi.tgl_penerimaan >= '{$tanggal_saldo_awal} 00:00:00'
                                        AND pi.tgl_penerimaan < '{$tgl_awal} 00:00:00'
                                    GROUP BY
                                        pms.id_so_det
                                    ) x
                                GROUP BY
                                    x.id_so_det

                                UNION ALL

                            /* ================= TRANSFER GARMENT PERIODE ================= */
                                SELECT
                                    pms.id_so_det AS so_det_id,
                                    0 AS pl_saldo_awal,
                                    0 AS pl_rft,
                                    0 AS pl_reject,
                                    0 AS tg_saldo_awal,
                                    SUM( tg.qty ) AS tg_masuk,
                                    SUM( COALESCE ( pi.qty, 0 ) ) AS tg_keluar,
                                    0 AS pc_saldo_awal,
                                    0 AS pc_terima,
                                    0 AS pc_keluar
                                FROM
                                    laravel_nds.packing_trf_garment tg
                                    JOIN ppic_master_so pms ON pms.id = tg.id_ppic_master_so
                                    LEFT JOIN (
                                        SELECT id_trf_garment, SUM( qty ) AS qty
                                        FROM laravel_nds.packing_packing_in
                                        WHERE tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                                        GROUP BY id_trf_garment
                                    ) pi ON pi.id_trf_garment = tg.id
                                WHERE
                                    tg.tgl_trans BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                                GROUP BY
                                    pms.id_so_det

                                UNION ALL

                            /* ================= PACKING CENTRAL SALDO ================= */
                                SELECT
                                    x.id_so_det AS so_det_id,
                                    0 AS pl_saldo_awal,
                                    0 AS pl_rft,
                                    0 AS pl_reject,
                                    0 AS tg_saldo_awal,
                                    0 AS tg_masuk,
                                    0 AS tg_keluar,
                                    SUM( x.masuk - x.keluar ) AS pc_saldo_awal,
                                    0 AS pc_terima,
                                    0 AS pc_keluar
                                FROM
                                    (
                                    SELECT
                                        pms.id_so_det,
                                        SUM( qty ) AS masuk,
                                        0 AS keluar
                                    FROM
                                        laravel_nds.packing_packing_in pi
                                        JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
                                    WHERE
                                        pms.id_so_det IS NOT NULL
                                        AND pi.tgl_penerimaan >= '{$tanggal_saldo_awal} 00:00:00'
                                        AND pi.tgl_penerimaan < '{$tgl_awal} 00:00:00'
                                    GROUP BY
                                        pms.id_so_det

                                    UNION ALL

                                    SELECT
                                        id_so_det,
                                        0 AS masuk,
                                        total_keluar AS keluar
                                    FROM
                                        pos_saldo_agg
                                    ) x
                                GROUP BY
                                    x.id_so_det

                                UNION ALL

                            /* ================= PACKING CENTRAL PERIODE ================= */
                                SELECT
                                    x.id_so_det AS so_det_id,
                                    0 AS pl_saldo_awal,
                                    0 AS pl_rft,
                                    0 AS pl_reject,
                                    0 AS tg_saldo_awal,
                                    0 AS tg_masuk,
                                    0 AS tg_keluar,
                                    0 AS pc_saldo_awal,
                                    SUM( x.masuk ) AS pc_terima,
                                    SUM( x.keluar ) AS pc_keluar
                                FROM
                                    (
                                    SELECT
                                        pms.id_so_det,
                                        SUM( qty ) AS masuk,
                                        0 AS keluar
                                    FROM
                                        laravel_nds.packing_packing_in pi
                                        JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
                                    WHERE
                                        pms.id_so_det IS NOT NULL
                                        AND pi.tgl_penerimaan BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                                    GROUP BY
                                        pms.id_so_det

                                    UNION ALL

                                    SELECT
                                        id_so_det,
                                        0 AS masuk,
                                        total_keluar AS keluar
                                    FROM
                                        pos_periode_agg
                                    ) x
                                GROUP BY
                                    x.id_so_det
                            )

                            /* ================= FINAL RESULT ================= */
                            SELECT
                                msn.urutan,
                                msw.ws,
                                msw.color,
                                msw.styleno AS style,
                                msw.size,
                                msw.buyer,
                                SUM( pl_saldo_awal ) AS pl_saldo_awal,
                                SUM( pl_rft ) AS pl_rft,
                                SUM( pl_reject ) AS pl_reject,
                                SUM( tg_masuk ) AS pl_keluar,
                                SUM( pl_saldo_awal ) + SUM( pl_rft ) + SUM( pl_reject ) - SUM( tg_masuk ) AS pl_saldo_akhir,
                                SUM( tg_saldo_awal ) AS tg_saldo_awal,
                                SUM( tg_masuk ) AS tg_masuk,
                                SUM( tg_keluar ) AS tg_keluar,
                                SUM( tg_saldo_awal ) + SUM( tg_masuk ) - SUM( tg_keluar ) AS tg_saldo_akhir,
                                SUM( pc_saldo_awal ) AS pc_saldo_awal,
                                SUM( pc_terima ) AS pc_terima,
                                SUM( pc_keluar ) AS pc_packing_scan,
                                SUM( pc_saldo_awal ) + SUM( pc_terima ) - SUM( pc_keluar ) AS pc_saldo_akhir
                            FROM
                                trx_union t
                                JOIN master_sb_ws msw ON msw.id_so_det = t.so_det_id
                                JOIN master_size_new msn ON msn.size = msw.size
                            GROUP BY
                                msn.urutan,
                                msw.ws,
                                msw.color,
                                msw.styleno,
                                msw.size,
                                msw.buyer
                            ORDER BY
                                msw.ws,
                                msw.color,
                                msw.buyer,
                                msn.urutan;
                        ");


        $this->rowCount = count($data_mut) + 2;


        return view('packing.export_excel_rep_packing_mutasi', [
            "from" => $this->from,
            "to" => $this->to,
            "data" => $data_mut,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }

    // public static function afterSheet(AfterSheet $event)
    // {
    //     $event->sheet->styleCells(
    //         'A1:R' . ($event->getConcernable()->rowCount+2),
    //         [
    //             'borders' => [
    //                 'allBorders' => [
    //                     'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                     'color' => ['argb' => '000000'],
    //                 ],
    //             ],
    //         ]
    //     );
    // }


    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();

        $sheet->mergeCells('A1:R2');

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );
        $sheet->getStyle('A1')->getAlignment()->setVertical(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        );

        $sheet->getStyle('A1')->getFont()->setBold(true);

        $event->sheet->styleCells(
            'A3:R' . ($event->getConcernable()->rowCount + 2),
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

