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

class ExportTrackWorksheet implements FromView, WithEvents, /*WithColumnWidths,*/ ShouldAutoSize
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
        $outputSewing = DB::connection("mysql_sb")->select("
            SELECT
                output_rfts.so_det_id,
                COUNT( output_rfts.id ) total_output
            FROM
                output_rfts
            GROUP BY
                so_det_id
        ");

        $outputPacking = DB::connection("mysql_sb")->select("
            SELECT
                output_rfts_packing.so_det_id,
                COUNT( output_rfts_packing.id ) total_output
            FROM
                output_rfts_packing
            GROUP BY
                so_det_id
        ");

        $worksheet = DB::select("
            SELECT DATE
                    ( master_sb_ws.tgl_kirim ) tgl_kirim,
                    master_sb_ws.id_act_cost,
                    master_sb_ws.ws,
                    master_sb_ws.styleno,
                    master_sb_ws.color,
                    master_sb_ws.id_so_det,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    master_sb_ws.qty,
                    marker_track.kode,
                    marker_track.panel,
                    sum( marker_track.total_cut_marker ) total_cut_marker,
                    sum( marker_track.total_cut_form ) total_cut_form,
                    sum( marker_track.total_stocker ) total_stocker,
                    sum( marker_track.total_dc ) total_dc,
                    sum( marker_track.total_sec ) total_sec,
                    sum( marker_track.total_sec_in ) total_sec_in
                FROM
                    master_sb_ws
                    LEFT JOIN (
                    SELECT
                        marker.id,
                        marker.act_costing_id,
                        marker.kode,
                        marker.panel,
                        marker_detail.so_det_id,
                        marker.gelar_qty total_gelar_marker,
                        marker_detail.ratio total_ratio_marker,
                        marker_detail.cut_qty total_cut_marker,
                        form_cut.qty_ply total_lembar_form,
                        sum( marker_detail.ratio * form_cut.qty_ply ) total_cut_form,
                        sum( stocker.qty_ply ) total_stocker,
                        sum( stocker.dc_qty_ply ) total_dc,
                        sum( stocker.sec_qty_ply ) total_sec,
                        sum( stocker.sec_in_qty_ply ) total_sec_in
                    FROM
                        marker_input marker
                        LEFT JOIN (
                        SELECT
                            marker_input_detail.marker_id,
                            marker_input_detail.so_det_id,
                            marker_input_detail.size,
                            sum( marker_input_detail.ratio ) ratio,
                            sum( marker_input_detail.cut_qty ) cut_qty
                        FROM
                            marker_input_detail
                        WHERE
                            marker_input_detail.ratio > 0
                        GROUP BY
                            marker_id,
                            so_det_id
                        ) marker_detail ON marker_detail.marker_id = marker.id
                        LEFT JOIN (
                        SELECT
                            form_cut_input.id,
                            form_cut_input.id_marker,
                            form_cut_input.no_form,
                            COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply ) qty_ply
                        FROM
                            form_cut_input
                        WHERE
                            form_cut_input.qty_ply IS NOT NULL
                            AND form_cut_input.id_marker IS NOT NULL
                        ) form_cut ON form_cut.id_marker = marker.kode
                        LEFT JOIN (
                        SELECT
                            *
                        FROM
                            (
                            SELECT
                                stocker_input.form_cut_id,
                                stocker_input.part_detail_id,
                                stocker_input.so_det_id,
                                sum(
                                COALESCE ( stocker_input.qty_ply_mod, stocker_input.qty_ply )) qty_ply,
                                sum((
                                        dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace
                                    )) dc_qty_ply,
                                sum( secondary_in_input.qty_in ) sec_qty_ply,
                                sum( secondary_inhouse_input.qty_in ) sec_in_qty_ply
                            FROM
                                stocker_input
                                LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                                LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = dc_in_input.id_qr_stocker
                                LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                            GROUP BY
                                stocker_input.form_cut_id,
                                stocker_input.part_detail_id,
                                stocker_input.so_det_id
                            ) stocker
                        GROUP BY
                            stocker.form_cut_id,
                            stocker.so_det_id
                        ) stocker ON stocker.form_cut_id = form_cut.id
                        AND stocker.so_det_id = marker_detail.so_det_id
                    GROUP BY
                        marker.id,
                        marker_detail.so_det_id
                    ) marker_track ON marker_track.act_costing_id = master_sb_ws.id_act_cost
                    AND marker_track.so_det_id = master_sb_ws.id_so_det
                WHERE
                    MONTH ( master_sb_ws.tgl_kirim ) = '".$this->month."'
                    AND YEAR ( master_sb_ws.tgl_kirim ) = '".$this->year."'
                GROUP BY
                    master_sb_ws.id_so_det,
                    marker_track.panel
                ORDER BY
                    master_sb_ws.id_act_cost,
                    master_sb_ws.color,
                    marker_track.panel,
                    master_sb_ws.id_so_det
            ");

        $this->rowCount = count($worksheet) + 3;

        return view('track.worksheet.export.worksheet', [
            'worksheet' => collect($worksheet),
            'outputSewing' => collect($outputSewing),
            'outputPacking' => collect($outputPacking),
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
            'A3:O' . $event->getConcernable()->rowCount,
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
