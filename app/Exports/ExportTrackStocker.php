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
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportTrackStocker implements FromView, WithEvents, /*WithColumnWidths,*/ ShouldAutoSize
{
    use Exportable;

    protected $month, $year;

    public function __construct($month, $year)
    {
        $this->month = $month ? $month : date('m');
        $this->year = $year ? $year : date('Y');
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $month = $this->month ? $this->month : date('m');
        $year = $this->year ? $this->year : date('Y');

        $worksheetStock = DB::select("
            SELECT
                stock.id_act_cost,
                stock.tgl_kirim,
                stock.act_costing_ws,
                stock.styleno,
                stock.color,
                SUM(stock.qty_ply) qty
            FROM (
                SELECT
                    master_sb_ws.id_act_cost,
                    DATE(master_sb_ws.tgl_kirim) tgl_kirim,
                    stocker_input.id,
                    ( CASE WHEN stocker_input.form_cut_id > 0 THEN stocker_input.form_cut_id ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN stocker_input.form_reject_id ELSE ( CASE WHEN stocker_input.form_piece_id > 0 THEN stocker_input.form_piece_id ELSE null END ) END ) END ) form_cut_id,
                    stocker_input.act_costing_ws,
                    master_sb_ws.styleno,
                    stocker_input.color,
                    stocker_input.size,
                    COALESCE (
                        (
                            MAX( dc_in_input.qty_awal ) - (
                                MAX(
                                    COALESCE ( dc_in_input.qty_reject, 0 )) + MAX(
                                COALESCE ( dc_in_input.qty_replace, 0 ))) - (
                                MAX(
                                    COALESCE ( secondary_in_input.qty_reject, 0 )) + MAX(
                                COALESCE ( secondary_in_input.qty_replace, 0 ))) - (
                                MAX(
                                    COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + MAX(
                                COALESCE ( secondary_inhouse_input.qty_replace, 0 )))
                        ),
                        COALESCE ( stocker_input.qty_ply_mod, stocker_input.qty_ply )
                    ) qty_ply
                FROM
                    stocker_input
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                    LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                WHERE
                    MONTH(master_sb_ws.tgl_kirim) = '".$month."' AND YEAR(master_sb_ws.tgl_kirim) = '".$year."'
                GROUP BY
                    stocker_input.form_cut_id,
                    stocker_input.form_reject_id,
                    stocker_input.form_piece_id,
                    stocker_input.so_det_id,
                    stocker_input.group_stocker,
                    stocker_input.ratio
            ) stock
            GROUP BY
                stock.act_costing_ws,
                stock.styleno,
                stock.color
        ");

        $this->rowCount = count($worksheetStock) + 3;

        return view('track.stocker.export.stocker', [
            'stocker' => collect($worksheetStock),
            'month' => $this->month,
            'year' => $this->year
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
            'A2:E' . $event->getConcernable()->rowCount,
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
