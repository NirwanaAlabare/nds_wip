<?php

namespace App\Exports\DC;

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

class ExportSecondaryInHouse implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from ? $from : date('Y-m-d');
        $this->to = $to ? $to : date('Y-m-d');
    }

    public function view(): View
    {
        $from = $this->from ? $this->from : date('Y-m-d');
        $to = $this->to ? $this->to : date('Y-m-d');

        $additionalQuery = "";

        if ($this->from) {
            $additionalQuery .= " and a.tgl_trans >= '" . $this->from . "' ";
        }

        if ($this->to) {
            $additionalQuery .= " and a.tgl_trans <= '" . $this->to . "' ";
        }

        $data = DB::select("
            SELECT
                a.*,
                (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                COALESCE(a.qty_in) qty_in,
                a.created_at,
                COALESCE(mx.tujuan, dc.tujuan) as tujuan,
                COALESCE(mx.proses, dc.lokasi) lokasi,
                dc.tempat,
                COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                COALESCE(msb.size, s.size) AS size,
                a.user,
                mp.nama_part,
                CONCAT(
                    s.range_awal, ' - ', s.range_akhir,
                    CASE
                    WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL
                        THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                    ELSE ' (0)'
                    END
                ) AS stocker_range_old,
                CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range
            FROM secondary_inhouse_input a
            LEFT JOIN (
                SELECT
                    secondary_inhouse_input.id_qr_stocker,
                    MAX(qty_awal) as qty_awal,
                    SUM(qty_reject) qty_reject,
                    SUM(qty_replace) qty_replace,
                    (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                    MAX(secondary_inhouse_input.urutan) AS max_urutan,
                    GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                    GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                FROM secondary_inhouse_input
                LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                GROUP BY id_qr_stocker
                having MAX(secondary_inhouse_input.urutan) is not null
            ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
            LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
            LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
            LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
            LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
            LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
            LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
            LEFT JOIN part p ON pd.part_id = p.id
            LEFT JOIN master_part mp ON mp.id = pd.master_part_id
            LEFT JOIN (
                SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                FROM dc_in_input
            ) dc ON a.id_qr_stocker = dc.id_qr_stocker
            WHERE
                a.tgl_trans IS NOT NULL
                AND (
                    a.urutan IS NULL
                    OR a.urutan = mx.max_urutan
                )
                $additionalQuery
            ORDER BY
                a.tgl_trans DESC
        ");
        $this->rowCount = count($data);

        return view("dc.secondary-inhouse.export.secondary-inhouse-excel", [
            "from" => $this->from,
            "to" => $this->to,
            "data" => $data
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
            'A1:Q' . ($event->getConcernable()->rowCount+2),
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
    //         'E' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }
}
