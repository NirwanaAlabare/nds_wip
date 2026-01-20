<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use DB;

class DefectRateExport implements FromView, ShouldAutoSize, WithCharts, WithTitle
{
    protected $query;
    protected $dateFrom;
    protected $dateTo;
    protected $ws;
    protected $style;
    protected $color;
    protected $sewingLine;
    protected $department;
    protected $rowCount;

    function __construct($query, $dateFrom, $dateTo, $ws, $style, $color, $sewingLine, $department) {
        ini_set('max_execution_time', 36000); // boost only once here
        ini_set('memory_limit', '2048M'); // adjust as needed

        $this->query = $query;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->ws = $ws ? $ws : "All WS";
        $this->style = $style ? $style : "All Style";
        $this->color = $color ? $color : "All Color";
        $this->sewingLine = $sewingLine ? $sewingLine : "All Sewing Line";
        $this->department = $department ? $department : "";
    }

    public function title(): string
    {
        return 'DefectRate';
    }

    public function view(): View
    {
        $defectRates = collect(DB::connection("mysql_sb")->select($this->query));

        $this->rowCount = $defectRates->count();

        return view('sewing.export.defect-rate-export', [
            'rowCount' => $this->rowCount,
            'defectRates' => $defectRates,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'ws' => $this->ws,
            'style' => $this->style,
            'color' => $this->color,
            'sewingLine' => $this->sewingLine,
            'department' => $this->department,
        ]);
    }

    public function charts()
    {
        if ($this->rowCount > 0) {
            // RFT Rate
            $labelsRftRate = [];
            $categoriesRftRate = [];
            $valuesRftRate = [];

            $labelsRftRate = [new DataSeriesValues('String', 'DefectRate!$L$6', null, 1)];


            $categoriesRftRate = [new DataSeriesValues('String', 'DefectRate!$A$7:$F$'.($this->rowCount+6), null, $this->rowCount)];

            array_push($valuesRftRate,
                new DataSeriesValues('Number', 'DefectRate!$L$7:$L$'.($this->rowCount+6), null, $this->rowCount),
            );

            // Build RFT Rate Chart
            $seriesRftRate = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesRftRate) - 1), $labelsRftRate, $categoriesRftRate, $valuesRftRate);
            $plotRftRate   = new PlotArea(null, [$seriesRftRate]);

            $legendRftRate = new Legend();
            $chartRftRate  = new Chart('RFT Rate', new Title('RFT Rate'), $legendRftRate, $plotRftRate);

            $chartRftRate->setTopLeftPosition('P5');
            $chartRftRate->setBottomRightPosition('AO30');

            // Defect/Reject Rate
            $labelsDefectRate = [];
            $categoriesDefectRate = [];
            $valuesDefectRate = [];

            $labelsDefectRate = [new DataSeriesValues('String', 'DefectRate!$M$6', null, 1), new DataSeriesValues('String', '$N$6', null, 1)];

            $categoriesDefectRate = [new DataSeriesValues('String', 'DefectRate!$A$7:$F$'.($this->rowCount+6), null, $this->rowCount)];

            array_push($valuesDefectRate,
                new DataSeriesValues('Number', 'DefectRate!$M$7:$M$'.($this->rowCount+6), null, $this->rowCount),
                new DataSeriesValues('Number', 'DefectRate!$N$7:$N$'.($this->rowCount+6), null, $this->rowCount),
            );

            // Build Defect/Reject DefectRate Chart
            $seriesDefectRate = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesDefectRate) - 1), $labelsDefectRate, $categoriesDefectRate, $valuesDefectRate);
            $plotDefectRate   = new PlotArea(null, [$seriesDefectRate]);

            $legendDefectRate = new Legend();
            $chartDefectRate  = new Chart('Defect/Reject Rate', new Title('Defect/Reject Rate'), $legendDefectRate, $plotDefectRate);

            $chartDefectRate->setTopLeftPosition('P31');
            $chartDefectRate->setBottomRightPosition('AO55');

            return [$chartRftRate, $chartDefectRate];
        }

        return [];
    }
}
