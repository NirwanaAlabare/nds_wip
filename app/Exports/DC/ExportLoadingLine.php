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
use App\Models\SignalBit\UserLine;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportLoadingLine implements FromView, WithEvents, ShouldAutoSize
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
        $detailDateFilter = "";
        if ($this->from || $this->to) {
            $detailDateFilter = "WHERE ";
            $dateFromFilter = " loading_line.tanggal_loading >= '".$this->from."' ";
            $dateToFilter = " loading_line.tanggal_loading <= '".$this->to."' ";

            if ($this->from && $this->to) {
                $detailDateFilter .= $dateFromFilter." AND ".$dateToFilter;
            } else {
                if ($this->to) {
                    $detailDateFilter .= $dateFromFilter;
                }

                if ($this->from) {
                    $detailDateFilter .= $dateToFilter;
                }
            }
        }

        $dateFilter = "";
        if ($this->from || $this->to) {
            $dateFilter = "WHERE ";
            $dateFromFilter = " loading_line_plan.tanggal >= '".$this->from."' ";
            $dateToFilter = " loading_line_plan.tanggal <= '".$this->to."' ";

            if ($this->from && $this->to) {
                $dateFilter .= $dateFromFilter." AND ".$dateToFilter;
            } else {
                if ($this->to) {
                    $dateFilter .= $dateFromFilter;
                }

                if ($this->from) {
                    $dateFilter .= $dateToFilter;
                }
            }
        }

        $dataLoadingLinePlan = collect(
            DB::select("
                SELECT
                    loading_line_plan.id,
                    loading_line_plan.line_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.style,
                    loading_line_plan.color,
                    loading_line_plan.target_sewing,
                    loading_line_plan.target_loading,
                    sum( loading_stock.qty ) loading_qty,
                    loading_line_plan.target_loading - sum( loading_stock.qty ) loading_balance,
                    loading_stock.nama_trolley nama_trolley,
                    trolley_stock.trolley_color trolley_color,
                    trolley_stock.trolley_qty trolley_qty
                FROM
                    loading_line_plan
                    INNER JOIN (
                        SELECT
                            (
                                ( COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                                ( COALESCE ( dc_in_input.qty_reject, 0 )) + ( COALESCE ( dc_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_in_input.qty_reject, 0 )) + ( COALESCE ( secondary_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + (COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                            ) qty,
                            trolley.id trolley_id,
                            trolley.nama_trolley,
                            stocker_input.so_det_id,
                            stocker_input.size,
                            loading_line.loading_plan_id
                        FROM
                            loading_line
                            LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                            LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
                            ".$detailDateFilter."
                        GROUP BY
                            loading_line.tanggal_loading,
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.range_awal
                        ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
                    LEFT JOIN (
                        select
                            trolley.id trolley_id,
                            group_concat(distinct trolley_stock_bundle.trolley_ws) trolley_ws,
                            group_concat(distinct trolley_stock_bundle.trolley_color) trolley_color,
                            sum(trolley_stock_bundle.trolley_qty) trolley_qty
                        from
                            trolley
                            left join trolley_stocker on trolley_stocker.trolley_id = trolley.id
                            inner join (
                                SELECT
                                    trolley_stocker.stocker_id,
                                    stocker_input.act_costing_ws trolley_ws,
                                    stocker_input.color trolley_color,
                                    stocker_input.qty_ply trolley_qty
                                FROM
                                    trolley_stocker
                                    LEFT JOIN stocker_input ON stocker_input.id = trolley_stocker.stocker_id
                                WHERE
                                    trolley_stocker.STATUS = 'active'
                                GROUP BY
                                    stocker_input.form_cut_id,
                                    stocker_input.form_reject_id,
                                    stocker_input.so_det_id,
                                    stocker_input.group_stocker,
                                    stocker_input.range_awal
                            ) trolley_stock_bundle on trolley_stock_bundle.stocker_id = trolley_stocker.stocker_id
                            group by trolley.id
                    ) trolley_stock ON trolley_stock.trolley_id = loading_stock.trolley_id

                GROUP BY
                    loading_line_plan.id
                ORDER BY
                    loading_line_plan.line_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.color
            ")
        );

        $loadingPlanIds = "('".$dataLoadingLinePlan->implode("id", "','")."')";

        $detailDateFilter = "";
        if ($this->from || $this->to) {
            $detailDateFilter = "AND ";
            $dateFromFilter = " COALESCE( DATE ( loading_line.updated_at ), loading_line.tanggal_loading ) >= '".$this->from."' ";
            $dateToFilter = " COALESCE( DATE ( loading_line.updated_at ), loading_line.tanggal_loading ) <= '".$this->to."' ";

            if ($this->from && $this->to) {
                $detailDateFilter .= $dateFromFilter." AND ".$dateToFilter;
            } else {
                if ($this->to) {
                    $detailDateFilter .= $dateFromFilter;
                }

                if ($this->from) {
                    $detailDateFilter .= $dateToFilter;
                }
            }
        }

        $dataLoadingLines = collect(
            DB::select("
                SELECT
                    COALESCE( DATE ( loading_line.updated_at ), loading_line.tanggal_loading ) tanggal_loading,
                    loading_line.loading_plan_id,
                    loading_line.nama_line,
                    (
                        (COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                        (COALESCE ( MAX(dc_in_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(dc_in_input.qty_replace), 0 )) -
                        (COALESCE ( MAX(secondary_in_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(secondary_in_input.qty_replace), 0 )) -
                        (COALESCE ( MAX(secondary_inhouse_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(secondary_inhouse_input.qty_replace), 0 ))
                    ) qty_old,
                    loading_line.qty,
                    trolley.id trolley_id,
                    trolley.nama_trolley,
                    stocker_input.id_qr_stocker,
                    stocker_input.so_det_id,
                    stocker_input.size,
                    stocker_input.shade,
                    stocker_input.group_stocker,
                    stocker_input.range_awal,
                    stocker_input.range_akhir,
                    loading_line_plan.act_costing_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.buyer,
                    loading_line_plan.style,
                    loading_line_plan.color,
                    loading_line_plan.line_id,
                    COALESCE(form_cut_input.no_form, form_cut_reject.no_form) no_form,
                    COALESCE(form_cut_input.no_cut, '-') no_cut,
                    (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) type
                FROM
                    loading_line
                    LEFT JOIN loading_line_plan ON loading_line_plan.id = loading_line.loading_plan_id
                    LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                    LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                    LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                    LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                    LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                    LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                    LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
                WHERE
                    loading_line_plan.id in ".$loadingPlanIds."
                    ".$detailDateFilter."
                GROUP BY
                    stocker_input.id_qr_stocker
                ORDER BY
                    loading_line_plan.id,
                    loading_line.tanggal_loading,
                    stocker_input.form_cut_id,
                    stocker_input.form_reject_id,
                    stocker_input.so_det_id,
                    stocker_input.range_awal
            ")
        );

        $this->rowCount = count($dataLoadingLines);

        return view("dc.loading-line.export.loading-line", [
            "from" => $this->from,
            "to" => $this->to,
            "loadingLines" => $dataLoadingLines,
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
            'A1:O' . ($event->getConcernable()->rowCount+2),
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
