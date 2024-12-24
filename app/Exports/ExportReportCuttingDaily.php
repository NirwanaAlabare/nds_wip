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
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportReportCuttingDaily implements FromView, WithEvents, ShouldAutoSize /*WithColumnWidths,*/
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
        $this->reportCutting = null;
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $additionalQuery = "";

        $sheets = [];

        if ($this->dateFrom) {
            $additionalQuery .= " and COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) >= '".$this->dateFrom."'";
        }

        if ($this->dateTo) {
            $additionalQuery .= " and COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) <= '".$this->dateTo."'";
        }

        $reportCutting = collect(
            DB::select("
                SELECT
                    marker_cutting.tgl_form_cut,
                    UPPER(marker_cutting.meja) meja,
                    marker_cutting.no_form,
                    marker_cutting.buyer,
                    marker_cutting.act_costing_ws,
                    marker_cutting.style,
                    marker_cutting.color,
                    marker_cutting.panel,
                    SUM((marker_cutting.form_gelar * marker_cutting.ratio) + COALESCE(marker_cutting.diff, 0)) qty
                FROM
                    (
                        SELECT
                            marker_input.kode,
                            GROUP_CONCAT(form_cut.no_form, form_cut.meja) no_form_meja,
                            form_cut.id form_cut_id,
                            form_cut.no_form,
                            form_cut.id_meja,
                            form_cut.meja,
                            form_cut.tgl_form_cut,
                            marker_input.buyer,
                            marker_input.act_costing_id,
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            marker_input.cons_ws,
                            marker_input.unit_panjang_marker unit,
                            marker_input_detail.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            marker_input_detail.ratio,
                            COALESCE(marker_input.notes, form_cut.notes) notes,
                            marker_input.gelar_qty marker_gelar,
                            SUM(form_cut.qty_ply) spreading_gelar,
                            SUM(COALESCE(form_cut.total_lembar, form_cut.detail)) form_gelar,
                            SUM(modify_size_qty.difference_qty) diff
                        FROM
                        marker_input
                        INNER JOIN
                            marker_input_detail on marker_input_detail.marker_id = marker_input.id
                        INNER JOIN
                            master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                        INNER JOIN
                            (
                                SELECT
                                    meja.id id_meja,
                                    meja.`name` meja,
                                    COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form_cut,
                                    form_cut_input.id_marker,
                                    form_cut_input.id,
                                    form_cut_input.no_form,
                                    form_cut_input.qty_ply,
                                    form_cut_input.total_lembar,
                                    form_cut_input.notes,
                                    SUM(form_cut_input_detail.lembar_gelaran) detail
                                FROM
                                    form_cut_input
                                    LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                WHERE
                                    form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                    AND form_cut_input.waktu_mulai is not null
                                    ".$additionalQuery."
                                GROUP BY
                                    form_cut_input.id
                            ) form_cut on form_cut.id_marker = marker_input.kode
                        LEFT JOIN
                            modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                        where
                            (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                            AND marker_input_detail.ratio > 0
                        group by
                            marker_input.id,
                            marker_input_detail.so_det_id,
                            form_cut.tgl_form_cut,
                            form_cut.meja,
                            form_cut.id
                    ) marker_cutting
                GROUP BY
                    marker_cutting.id_meja,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.tgl_form_cut,
                    marker_cutting.form_cut_id
                ORDER BY
                    marker_cutting.id_meja,
                    marker_cutting.tgl_form_cut,
                    marker_cutting.panel,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.form_cut_id
            ")
        );

        $this->reportCutting = collect($reportCutting);

        $this->rowCount = count($reportCutting) + 6;

        return view('cutting.report.export.report-cutting-daily', [
            'reportCutting' => collect($reportCutting),
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
            'A5:I' . ($event->getConcernable()->rowCount),
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

}
?>
