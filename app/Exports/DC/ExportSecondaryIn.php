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
            SELECT a.id_qr_stocker,
            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
            a.tgl_trans,
            s.act_costing_ws,
            s.color,
            p.buyer,
            p.style,
            dc.tujuan,
            dc.lokasi,
            s.lokasi lokasi_rak,
            a.qty_awal,
            a.qty_reject,
            a.qty_replace,
            a.qty_in,
            a.created_at,
            CONCAT(s.range_awal, ' - ', s.range_akhir, (CASE WHEN ((dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL) OR (sii.qty_reject IS NOT NULL AND sii.qty_replace IS NOT NULL)) THEN CONCAT(' (', ((COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)) + (COALESCE(sii.qty_replace, 0) - COALESCE(sii.qty_reject, 0))), ') ') ELSE ' (0)' END)) stocker_range,
            COALESCE(f.no_cut, '-') no_cut,
            COALESCE(msb.size, s.size) size,
            a.user,
            mp.nama_part
            from secondary_in_input a
            left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join form_cut_input f on f.id = s.form_cut_id
            left join form_cut_reject fr on f.id = s.form_reject_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on pd.part_id = p.id
            left join master_part mp on mp.id = pd.master_part_id
            left join dc_in_input dc on a.id_qr_stocker = dc.id_qr_stocker
            left join secondary_inhouse_input sii on a.id_qr_stocker = sii.id_qr_stocker
            where
            a.tgl_trans is not null and (s.cancel IS NULL OR s.cancel != 'y')
            ".$additionalQuery."
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
            'A1:S' . ($event->getConcernable()->rowCount+2),
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
