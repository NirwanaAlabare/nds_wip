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

class ExportSecondaryInHouseDetail implements FromView, WithEvents, ShouldAutoSize
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
                s.act_costing_ws, m.buyer,s.color,  styleno, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in, COALESCE(sum(sii.qty_reject), 0) qty_reject, COALESCE(sum(sii.qty_replace), 0) qty_replace, COALESCE(sum(sii.qty_in), 0) qty_out, COALESCE((sum(sii.qty_in) - sum(dc.qty_awal - dc.qty_reject + dc.qty_replace)), 0) balance, dc.lokasi
            from
                dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws m on s.so_det_id = m.id_so_det
                left join secondary_inhouse_input sii on dc.id_qr_stocker = sii.id_qr_stocker
            where
                dc.tujuan = 'SECONDARY DALAM' ".$additionalQuery."
            group by
                m.ws,m.buyer,m.styleno,m.color,dc.lokasi
        ");

        $this->rowCount = count($data);

        return view("dc.secondary-inhouse.export.secondary-inhouse-detail-excel", [
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
            'A1:I' . ($event->getConcernable()->rowCount+2),
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
