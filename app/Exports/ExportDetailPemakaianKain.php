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
use App\Models\Cutting\FormCutInputDetail;
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

        $rollIds = addQuotesAround($rollIdsArr->pluck("id_roll")->implode("\n"));

        $rolls = collect(DB::select("
                SELECT
                    req.id_roll,
                    req.id_item,
                    req.item_desc detail_item,
                    req.no_lot lot,
                    req.styleno,
                    req.color,
                    COALESCE(roll.roll, req.no_roll) roll,
                    COALESCE(roll.qty, req.qty_out) qty,
                    COALESCE(roll.sisa_kain, 0) sisa_kain,
                    COALESCE(roll.unit, req.satuan) unit,
                    COALESCE(roll.total_pemakaian_roll, 0) total_pemakaian_roll,
                    COALESCE(roll.total_short_roll_2, 0) total_short_roll_2,
                    COALESCE(roll.total_short_roll, 0) total_short_roll
                FROM (
                    select b.*, c.color, tmpjo.styleno from signalbit_erp.whs_bppb_h a INNER JOIN signalbit_erp.whs_bppb_det b on b.no_bppb = a.no_bppb LEFT JOIN signalbit_erp.masteritem c ON c.id_item = b.id_item left join (select id_jo,kpno,styleno from signalbit_erp.act_costing ac inner join signalbit_erp.so on ac.id=so.id_cost inner join signalbit_erp.jo_det jod on signalbit_erp.so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=b.id_jo WHERE a.no_req = '".$this->no_req."' and b.id_item = '".$this->id_item."' and b.status = 'Y' GROUP BY id_roll
                ) req
                LEFT JOIN (
                    select
                        id_roll,
                        id_item,
                        detail_item,
                        lot,
                        COALESCE(roll_buyer, roll) roll,
                        MAX(qty) qty,
                        ROUND(MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END), 2) sisa_kain,
                        unit,
                        ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
                        ROUND(SUM(short_roll), 2) total_short_roll_2,
                        ROUND((SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) - MAX(qty), 2) total_short_roll
                    from
                        laravel_nds.form_cut_input_detail
                    WHERE
                        `status` in ('complete', 'need extension', 'extension complete')
                        ".($rollIds ? "and id_roll in (".$rollIds.")" : "")."
                    GROUP BY
                        id_item,
                        id_roll
                UNION ALL
                    SELECT
                        id_roll,
                        id_item,
                        detail_item,
                        lot,
                        COALESCE ( roll_buyer, roll ) roll,
                        MAX( form_cut_piece_detail.qty_pengeluaran ) qty,
                        MIN( form_cut_piece_detail.qty_sisa ) sisa_kain,
                        qty_unit as unit,
                        ROUND( SUM( form_cut_piece_detail.qty_pemakaian ) ) total_pemakaian_roll,
                        ROUND( SUM( form_cut_piece_detail.qty - (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa) ) ) total_short_roll_2,
                        ROUND( SUM( form_cut_piece_detail.qty - (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa) ) ) total_short_roll
                    FROM
                        `form_cut_piece_detail`
                    WHERE
                        `status` = 'complete'
                        ".($rollIds ? "and id_roll in (".$rollIds.")" : "")."
                    GROUP BY
                        `id_item`,
                        `id_roll`
                ) roll ON req.id_roll = roll.id_roll
            "));

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
            'A4:J' . ($event->getConcernable()->rowCount+3+1),
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
