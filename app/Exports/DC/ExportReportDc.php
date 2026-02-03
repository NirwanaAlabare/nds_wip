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

class ExportReportDc implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    protected $noWsColorSizeFilter;
    protected $noWsColorPartFilter;
    protected $noWsFilter;
    protected $buyerFilter;
    protected $styleFilter;
    protected $colorFilter;
    protected $sizeFilter;
    protected $partFilter;
    protected $saldoAwalFilter;
    protected $masukFilter;
    protected $kirimSecDalamFilter;
    protected $terimaRepairedSecDalamFilter;
    protected $terimaGoodSecDalamFilter;
    protected $kirimSecLuarFilter;
    protected $terimaRepairedSecLuarFilter;
    protected $terimaGoodSecLuarFilter;
    protected $loadingFilter;
    protected $saldoAkhirFilter;


    public function __construct(
        $from,
        $to,
        $noWsColorSizeFilter,
        $noWsColorPartFilter,
        $noWsFilter,
        $buyerFilter,
        $styleFilter,
        $colorFilter,
        $sizeFilter,
        $partFilter,
        $saldoAwalFilter,
        $masukFilter,
        $kirimSecDalamFilter,
        $terimaRepairedSecDalamFilter,
        $terimaGoodSecDalamFilter,
        $kirimSecLuarFilter,
        $terimaRepairedSecLuarFilter,
        $terimaGoodSecLuarFilter,
        $loadingFilter,
        $saldoAkhirFilter
    ) {
        $this->from = $from ?: date('Y-m-d');
        $this->to   = $to   ?: date('Y-m-d');

        $this->noWsColorSizeFilter = $noWsColorSizeFilter;
        $this->noWsColorPartFilter = $noWsColorPartFilter;
        $this->noWsFilter = $noWsFilter;
        $this->buyerFilter = $buyerFilter;
        $this->styleFilter = $styleFilter;
        $this->colorFilter = $colorFilter;
        $this->sizeFilter = $sizeFilter;
        $this->partFilter = $partFilter;
        $this->saldoAwalFilter = $saldoAwalFilter;
        $this->masukFilter = $masukFilter;
        $this->kirimSecDalamFilter = $kirimSecDalamFilter;
        $this->terimaRepairedSecDalamFilter = $terimaRepairedSecDalamFilter;
        $this->terimaGoodSecDalamFilter = $terimaGoodSecDalamFilter;
        $this->kirimSecLuarFilter = $kirimSecLuarFilter;
        $this->terimaRepairedSecLuarFilter = $terimaRepairedSecLuarFilter;
        $this->terimaGoodSecLuarFilter = $terimaGoodSecLuarFilter;
        $this->loadingFilter = $loadingFilter;
        $this->saldoAkhirFilter = $saldoAkhirFilter;
    }


    public function view(): View
    {
        $detailDateFilter = "";
        if ($this->from || $this->to) {
            $detailDateFilter = "WHERE ";
            $dateFromFilter = " loading_line_plan.tanggal >= '".$this->from."' ";
            $dateToFilter = " loading_line_plan.tanggal <= '".$this->to."' ";

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

        $generalFilter = "";
        if (
            $this->noWsColorSizeFilter ||
            $this->noWsColorPartFilter ||
            $this->noWsFilter ||
            $this->buyerFilter ||
            $this->styleFilter ||
            $this->colorFilter ||
            $this->sizeFilter ||
            $this->partFilter ||
            $this->saldoAwalFilter ||
            $this->masukFilter ||
            $this->kirimSecDalamFilter ||
            $this->terimaRepairedSecDalamFilter ||
            $this->terimaGoodSecDalamFilter ||
            $this->kirimSecLuarFilter ||
            $this->terimaRepairedSecLuarFilter ||
            $this->terimaGoodSecLuarFilter ||
            $this->loadingFilter ||
            $this->saldoAkhirFilter
        ) {
            $generalFilter .= " WHERE ( loading_line_plan.id IS NOT NULL ";
            // if ($this->lineFilter) {
            //     $generalFilter .= "AND loading_line_plan.line_id LIKE '%".$this->lineFilter."%'";
            // }
            // if ($this->wsFilter) {
            //     $generalFilter .= "AND loading_line_plan.act_costing_ws LIKE '%".$this->wsFilter."%'";
            // }
            // if ($this->styleFilter) {
            //     $generalFilter .= "AND loading_line_plan.style LIKE '%".$this->styleFilter."%'";
            // }
            // if ($this->colorFilter) {
            //     $generalFilter .= "AND loading_line_plan.color LIKE '%".$this->colorFilter."%'";
            // }
            // if ($this->targetSewingFilter) {
            //     $generalFilter .= "AND loading_line_plan.target_sewing LIKE '%".$this->targetSewingFilter."%'";
            // }
            // if ($this->targetLoadingFilter) {
            //     $generalFilter .= "AND loading_line_plan.target_loading LIKE '%".$this->targetLoadingFilter."%'";
            // }
            // if ($this->trolleyFilter) {
            //     $generalFilter .= "AND loading_stock.nama_trolley LIKE '%".$this->trolleyFilter."%'";
            // }
            // if ($this->trolleyColorFilter) {
            //     $generalFilter .= "AND trolley_stock.trolley_color LIKE '%".$this->trolleyColorFilter."%'";
            // }
            $generalFilter .= " )";
        }


         $dataReport = collect(
            DB::select("
                SELECT
                    loading_line_plan.color
                FROM
                    loading_line_plan
                ".$detailDateFilter."
                GROUP BY
                    loading_line_plan.id
                ORDER BY
                    loading_line_plan.line_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.color
            ")
        );

        $this->rowCount = count($dataReport);

        return view("dc.report.export.report-dc", [
            "from" => $this->from,
            "to" => $this->to,
            "dataReport" => $dataReport,
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
            'A1:T' . ($event->getConcernable()->rowCount+2),
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
