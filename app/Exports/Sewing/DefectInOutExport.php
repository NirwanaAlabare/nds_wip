<?php

namespace App\Exports\Sewing;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\DefectInOut;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class DefectInOutExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $type;
    protected $outputType;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $type, $outputType) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->type = $type;
        $this->outputType = $outputType;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        if ($this->outputType == 'packing') {
            $defectInOutList = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
                    userpassword.FullName,
                    output_defect_in_out.output_type,
                    act_costing.kpno,
                    act_costing.styleno,
                    so_det.color,
                    so_det.size,
                    output_defect_types.defect_type,
                    COUNT(output_defect_in_out.id) defect_qty
                ")->
                leftJoin("output_defects_packing", "output_defects_packing.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects_packing.defect_type_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")->
                where("output_defect_in_out.type", strtolower($this->type))->
                where("output_defect_in_out.output_type", strtolower($this->outputType))->
                whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
                groupBy("output_defect_in_out.created_at", "output_defects_packing.so_det_id")->
                orderBy("output_defect_in_out.created_at")->
                get();
        } else {
            $defectInOutList = DefectInOut::selectRaw("
                    output_defect_in_out.created_at,
                    userpassword.FullName,
                    output_defect_in_out.output_type,
                    act_costing.kpno,
                    act_costing.styleno,
                    so_det.color,
                    so_det.size,
                    output_defect_types.defect_type,
                    COUNT(output_defect_in_out.id) defect_qty
                ")->
                leftJoin("output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
                leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                where("output_defect_in_out.type", strtolower($this->type))->
                where("output_defect_in_out.output_type", strtolower($this->outputType))->
                whereBetween("output_defect_in_out.created_at", [$this->dateFrom." 00:00:00", $this->dateTo." 23:59:59"])->
                groupBy("output_defect_in_out.created_at", "output_defects.so_det_id")->
                orderBy("output_defect_in_out.created_at")->
                get();
        }

        $this->rowCount = $defectInOutList->count();

        return view('sewing.export.defect-in-out-export', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'type' => $this->type,
            'outputType' => $this->outputType,
            'defectInOutList' => $defectInOutList
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
        // $event->sheet->styleCells(
        //     'A2:J' . ($event->getConcernable()->rowCount+4),
        //     [
        //         'borders' => [
        //             'allBorders' => [
        //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //                 'color' => ['argb' => '000000'],
        //             ],
        //         ],
        //     ]
        // );
    }
}
