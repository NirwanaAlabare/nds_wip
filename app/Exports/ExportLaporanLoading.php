<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use App\Models\SignalBit\UserLine;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportLaporanLoading implements FromView, WithEvents, WithColumnWidths, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $dateFilter = "";
        if ($this->dateFrom || $this->dateTo) {
            $dateFilter = "HAVING ";
            $dateFromFilter = " loading_stock.tanggal_loading >= '".$this->dateFrom."' ";
            $dateToFilter = " loading_stock.tanggal_loading <= '".$this->dateTo."' ";

            if ($this->dateFrom && $this->dateTo) {
                $dateFilter .= $dateFromFilter." AND ".$dateToFilter;
            } else {
                if ($this->dateTo) {
                    $dateFilter .= $dateFromFilter;
                }

                if ($this->dateFrom) {
                    $dateFilter .= $dateToFilter;
                }
            }
        }

        $data = DB::select("
            SELECT
                loading_stock.tanggal_loading,
                loading_line_plan.id,
                loading_line_plan.line_id,
                loading_line_plan.act_costing_ws,
                loading_line_plan.style,
                loading_line_plan.color,
                loading_stock.size size,
                sum( loading_stock.qty ) loading_qty
            FROM
                loading_line_plan
                LEFT JOIN (
                    SELECT
                        COALESCE(loading_line.tanggal_loading, DATE(loading_line.updated_at)) tanggal_loading,
                        loading_line.loading_plan_id,
                        COALESCE((MAX(dc_in_input.qty_awal) - (MAX(COALESCE(dc_in_input.qty_reject, 0)) + MAX(COALESCE(dc_in_input.qty_replace, 0))) - (MAX(COALESCE(secondary_in_input.qty_reject, 0)) + MAX(COALESCE(secondary_in_input.qty_replace, 0))) - (MAX(COALESCE(secondary_inhouse_input.qty_reject, 0)) + MAX(COALESCE(secondary_inhouse_input.qty_replace, 0)))), COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply)) qty,
                        trolley.id trolley_id,
                        trolley.nama_trolley,
                        stocker_input.size
                    FROM
                        loading_line
                        LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                        LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                        LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                        LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                        LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                        LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                    GROUP BY
                        loading_line.tanggal_loading,
                        stocker_input.form_cut_id,
                        stocker_input.so_det_id,
                        stocker_input.group_stocker,
                        stocker_input.range_awal
                ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
            WHERE
                loading_stock.tanggal_loading is not null
            GROUP BY
                loading_line_plan.id,
                loading_stock.size
                ".$dateFilter."
            ORDER BY
                loading_stock.tanggal_loading,
                loading_line_plan.line_id,
                loading_line_plan.act_costing_ws,
                loading_line_plan.color
        ");

        $lineData = UserLine::get();

        $this->rowCount = count($data) + 3;

        return view('dc.loading-line.export.loading', [
            'data' => collect($data),
            'lineData' => $lineData,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
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
            'A3:G' . $event->getConcernable()->rowCount,
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

    public function columnWidths(): array
    {
        return [
            'A' => 15,
        ];
    }
}
