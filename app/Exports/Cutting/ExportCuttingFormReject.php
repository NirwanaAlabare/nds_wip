<?php

namespace App\Exports\Cutting;

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

class ExportCuttingFormReject implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
    }

    public function view(): View
    {
        $data = DB::table("stocker_input")->selectRaw("
                form_cut_reject.tanggal,
                form_cut_reject.act_costing_ws,
                form_cut_reject.buyer,
                form_cut_reject.style,
                form_cut_reject.color,
                form_cut_reject.panel,
                master_part.nama_part part,
                form_cut_reject.no_form,
                stocker_input.size,
                form_cut_reject.group,
                stocker_input.qty_ply qty,
                stocker_input.notes,
                master_part.nama_part
            ")->
            leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
            leftJoin("form_cut_reject_detail", function ($join) {
                $join->on("form_cut_reject_detail.form_id", "=", "form_cut_reject.id");
                $join->on("form_cut_reject_detail.so_det_id", "=", "stocker_input.so_det_id");
            })->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            whereBetween("form_cut_reject.tanggal", [$this->dateFrom, $this->dateTo])->
            get();

        $this->rowCount = count($data);

        return view('cutting.cutting-form-reject.export.cutting-form-reject', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'data' => $data,
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
            'A2:B2',
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
        );

        $event->sheet->styleCells(
            'A4:L' . ($event->getConcernable()->rowCount+5),
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
