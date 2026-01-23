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
            $dateFromFilter = " loading_stock.tanggal_loading >= '".($this->dateFrom ? $this->dateFrom : date("Y-m-d"))."' ";
            $dateToFilter = " loading_stock.tanggal_loading <= '".($this->dateTo ? $this->dateTo : date("Y-m-d"))."' ";

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

        $innerDateFilter = "";
        if ($this->dateFrom || $this->dateTo) {
            $innerDateFilter = "WHERE ";
            $innerDateFromFilter = " loading_line.tanggal_loading >= '".($this->dateFrom ? $this->dateFrom : date("Y-m-d"))."' ";
            $innerDateToFilter = " loading_line.tanggal_loading <= '".($this->dateTo ? $this->dateTo : date("Y-m-d"))."' ";

            if ($this->dateFrom && $this->dateTo) {
                $innerDateFilter .= $innerDateFromFilter." AND ".$innerDateToFilter;
            } else {
                if ($this->dateTo) {
                    $innerDateFilter .= $innerDateFromFilter;
                }

                if ($this->dateFrom) {
                    $innerDateFilter .= $innerDateToFilter;
                }
            }
        }

        $data = DB::select("
            SELECT
                loading_stock.tanggal_loading,
                loading_line_plan.id,
                loading_line_plan.line_id,
                loading_stock.nama_line,
                loading_line_plan.act_costing_ws,
                loading_line_plan.style,
                loading_line_plan.color,
                loading_stock.size size,
                sum( loading_stock.qty ) loading_qty
            FROM
                loading_line_plan
                LEFT JOIN (
                    SELECT
                        MAX(COALESCE ( loading_line.tanggal_loading, DATE ( loading_line.updated_at ) )) tanggal_loading,
                        loading_line.loading_plan_id,
                        loading_line.nama_line,
                        (
                            COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply ) -
                            ( COALESCE ( dc_in_input.qty_reject, 0 )) + ( COALESCE ( dc_in_input.qty_replace, 0 )) -
                            ( COALESCE ( secondary_in_input.qty_reject, 0 )) + ( COALESCE ( secondary_in_input.qty_replace, 0 )) -
                            ( COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + (COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                        ) qty_old,
                        loading_line.qty qty,
                        trolley.id trolley_id,
                        trolley.nama_trolley,
                        stocker_input.so_det_id,
                        COALESCE(master_sb_ws.size, stocker_input.size) size,
                        master_size_new.urutan
                    FROM
                        loading_line
                        LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                        LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                        LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                        LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                        LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                        LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                        LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                        LEFT JOIN master_size_new ON master_size_new.size = master_sb_ws.size
                        ".$innerDateFilter."
                    GROUP BY
                        stocker_input.form_cut_id,
                        stocker_input.form_reject_id,
                        stocker_input.form_piece_id,
                        stocker_input.so_det_id,
                        stocker_input.group_stocker,
                        stocker_input.ratio
                    ORDER BY
                        FIELD(part_detail.part_status, 'main', 'regular', 'complement') ASC
                ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
            WHERE
                loading_stock.tanggal_loading IS NOT NULL
            GROUP BY
                loading_stock.tanggal_loading,
                loading_line_plan.id,
                loading_stock.size
                ".$dateFilter."
            ORDER BY
                loading_stock.tanggal_loading,
                loading_line_plan.line_id,
                loading_line_plan.act_costing_ws,
                loading_line_plan.color,
                loading_stock.so_det_id,
                loading_stock.urutan
        ");

        $this->rowCount = count($data) + 4;

        return view('dc.loading-line.export.loading', [
            'data' => collect($data),
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
