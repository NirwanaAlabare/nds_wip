<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\FormCutInputDetail;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportDetailPemakaianKain implements FromView, WithEvents, ShouldAutoSize /*WithColumnWidths,*/
{
    use Exportable;

    protected $no_req;
    protected $id_item;
    protected $rowCount;

    public function __construct($no_req, $id_item)
    {
        $this->no_req = $no_req;
        $this->id_item = $id_item;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '".$this->no_req."' and b.id_item = '".$this->id_item."' and b.status = 'Y' GROUP BY id_roll"));

        $rollIds = $rollIdsArr->pluck('id_roll');

        $rolls = FormCutInputDetail::selectRaw("
                id_roll,
                id_item,
                detail_item,
                lot,
                COALESCE(roll_buyer, roll) roll,
                MAX(qty) qty,
                unit,
                ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
                ROUND(SUM(CASE WHEN short_roll < 0 THEN short_roll ELSE 0 END), 2) total_short_roll
            ")->
            whereNotNull("id_roll")->
            whereIn("id_roll", $rollIds)->
            groupBy("id_item", "id_roll")->
            get();

        $this->rowCount = $rolls->count();

        return view('cutting.report.export.detail-pemakaian-roll', [
            'no_req' => $this->no_req,
            'id_item' => $this->id_item,
            'rolls' => $rolls
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
        $currentRow = 1;

        $event->sheet->styleCells(
            'A4:I' . ($event->getConcernable()->rowCount+3+1),
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

    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 15,
    //         'C' => 15,
    //         'D' => 15,
    //         'E' => 15,
    //         'G' => 25,
    //     ];
    // }
}
