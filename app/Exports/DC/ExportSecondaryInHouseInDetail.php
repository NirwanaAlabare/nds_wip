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

class ExportSecondaryInHouseInDetail implements FromView, WithEvents, ShouldAutoSize
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
            $additionalQuery .= " and (sii.tgl_trans >= '" . $this->from . "') ";
        }

        if ($this->to) {
            $additionalQuery .= " and (sii.tgl_trans <= '" . $this->to . "') ";
        }

        $data = DB::select("
            select
                sii.tgl_trans, s.act_costing_ws, msb.buyer, styleno,
                COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                s.color, s.size, mp.nama_part, dc.tujuan, dc.lokasi as proses, COALESCE(sum(sii.qty_in), 0) qty_in
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on p.id = pd.part_id
                left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                left join part p_com on p_com.id = pd_com.part_id
                left join master_part mp on mp.id = pd.master_part_id
                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
            where
                dc.tujuan = 'SECONDARY DALAM' ".$additionalQuery."
            group by
                sii.tgl_trans, s.act_costing_ws, msb.buyer, styleno, s.color, s.size, mp.nama_part, dc.tujuan, dc.lokasi
        ");

        $this->rowCount = count($data);

        return view("dc.secondary-inhouse-in.export.secondary-inhouse-in-detail-excel", [
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
            'A1:J' . ($event->getConcernable()->rowCount+2),
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
