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

class ExportSecondaryIn implements FromView, WithEvents, ShouldAutoSize
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
                    a.id_qr_stocker,
                    (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                    DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                    a.tgl_trans,
                    s.act_costing_ws,
                    s.color,
                    p.buyer,
                    p.style,
                    COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                    COALESCE(mx.tujuan, dc.tujuan) tujuan,
                    COALESCE(mx.proses, dc.lokasi) lokasi,
                    COALESCE(s.lokasi, '-') lokasi_rak,
                    COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                    COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                    COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                    COALESCE(a.qty_in) qty_in,
                    CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                    a.created_at,
                    CONCAT(s.range_awal, ' - ', s.range_akhir,
                        (
                            CASE WHEN (mx.qty_reject IS NOT NULL AND mx.qty_replace IS NOT NULL) THEN
                                (CONCAT(' (', (COALESCE(mx.qty_replace, 0) - COALESCE(mx.qty_reject, 0)), ') ')) ELSE
                                (
                                    CASE WHEN ((dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL) OR (sii.qty_reject IS NOT NULL AND sii.qty_replace IS NOT NULL)) THEN
                                        CONCAT(' (', ((COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)) + (COALESCE(sii.qty_replace, 0) - COALESCE(sii.qty_reject, 0))), ') ') ELSE
                                        ' (0)'
                                    END
                                )
                            END
                        )
                    ) stocker_range_old,
                    CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range,
                    COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                    COALESCE(msb.size, s.size) size,
                    a.user,
                    mp.nama_part,
                    a.urutan
                from secondary_in_input a
                LEFT JOIN (
                    SELECT
                        secondary_in_input.id_qr_stocker,
                        MAX(qty_awal) as qty_awal,
                        SUM(qty_reject) qty_reject,
                        SUM(qty_replace) qty_replace,
                        (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                        MAX(secondary_in_input.urutan) AS max_urutan,
                        GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                        GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                    FROM secondary_in_input
                    LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                    LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_in_input.urutan
                    LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                    GROUP BY id_qr_stocker
                    having MAX(secondary_in_input.urutan) is not null
                ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
                left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                left join form_cut_reject fr on fr.id = s.form_reject_id
                left join form_cut_piece fp on fp.id = s.form_piece_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on p.id = pd.part_id
                left join part_detail pd_com on pd._com.id = pd.from_part_detail and pd.part_status = 'complement'
                left join part p_com on p_com.id = pd_com.part_id
                left join master_part mp on mp.id = pd.master_part_id
                left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
                left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
                where
                    a.tgl_trans is not null
                    AND (
                        a.urutan IS NULL
                        OR a.urutan = mx.max_urutan
                    )
                    ".$additionalQuery."
                group by a.id
                order by a.tgl_trans desc
        ");

        $this->rowCount = count($data);

        return view("dc.secondary-in.export.secondary-in-excel", [
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
            'A1:T' . ($event->getConcernable()->rowCount+2),
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
