<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use DB;

class DefectRateExport implements FromView, ShouldAutoSize, WithCharts
{
    protected $query;

    function __construct($query) {
        $this->query = $query;
    }

    public function view(): View
    {
        $defectRates = collect(DB::connection("mysql_sb")->select($this->query));

        $this->rowCount = $defectRates->count();

        return view('sewing.export.defect-rate-export', [
            'rowCount' => $this->rowCount,
            'defectRates' => $defectRates
        ]);
    }

    public function charts()
    {
        // RFT Rate
        $labelsRftRate = [];
        $categoriesRftRate = [];
        $valuesRftRate = [];

        $labelsRftRate = [new DataSeriesValues('String', 'Worksheet!$L$1', null, 1)];


        $categoriesRftRate = [new DataSeriesValues('String', 'Worksheet!$A$2:$B$'.($this->rowCount+1), null, $this->rowCount)];

        array_push($valuesRftRate,
            new DataSeriesValues('Number', 'Worksheet!$L$2:$L$'.($this->rowCount+1), null, $this->rowCount),
        );

        // Build RFT Rate Chart
        $seriesRftRate = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesRftRate) - 1), $labelsRftRate, $categoriesRftRate, $valuesRftRate);
        $plotRftRate   = new PlotArea(null, [$seriesRftRate]);

        $legendRftRate = new Legend();
        $chartRftRate  = new Chart('RFT Rate', new Title('RFT Rate'), $legendRftRate, $plotRftRate);

        $chartRftRate->setTopLeftPosition('P2');
        $chartRftRate->setBottomRightPosition('AJ27');

        // Defect/Reject Rate
        $labelsDefectRate = [];
        $categoriesDefectRate = [];
        $valuesDefectRate = [];

        $labelsDefectRate = [new DataSeriesValues('String', 'Worksheet!$M$1', null, 1), new DataSeriesValues('String', 'Worksheet!$N$1', null, 1)];

        $categoriesDefectRate = [new DataSeriesValues('String', 'Worksheet!$A$2:$B$'.($this->rowCount+1), null, $this->rowCount)];

        array_push($valuesDefectRate,
            new DataSeriesValues('Number', 'Worksheet!$M$2:$M$'.($this->rowCount+1), null, $this->rowCount),
            new DataSeriesValues('Number', 'Worksheet!$N$2:$N$'.($this->rowCount+1), null, $this->rowCount),
        );

        // Build Defect/Reject DefectRate Chart
        $seriesDefectRate = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesDefectRate) - 1), $labelsDefectRate, $categoriesDefectRate, $valuesDefectRate);
        $plotDefectRate   = new PlotArea(null, [$seriesDefectRate]);

        $legendDefectRate = new Legend();
        $chartDefectRate  = new Chart('Defect/Reject Rate', new Title('Defect/Reject Rate'), $legendDefectRate, $plotDefectRate);

        $chartDefectRate->setTopLeftPosition('P28');
        $chartDefectRate->setBottomRightPosition('AJ53');

        return [$chartRftRate, $chartDefectRate];
    }
}
