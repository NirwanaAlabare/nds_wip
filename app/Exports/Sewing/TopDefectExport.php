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

class TopDefectExport implements FromView, ShouldAutoSize, WithCharts, WithTitle
{
    protected $query;
    protected $dateFrom;
    protected $dateTo;
    protected $ws;
    protected $style;
    protected $color;
    protected $sewingLine;
    protected $rowCount;
    protected $colAlphabet;
    protected $colAlphabetChartStart;
    protected $colAlphabetChartEnd;

    function __construct($query, $dateFrom, $dateTo, $ws, $style, $color, $sewingLine) {
        $this->query = $query;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->ws = $ws ? $ws : "All WS";
        $this->style = $style ? $style : "All Style";
        $this->color = $color ? $color : "All Color";
        $this->sewingLine = $sewingLine ? $sewingLine : "All Sewing Line";
    }

    public function title(): string
    {
        return 'Top Defect';
    }

    public function view(): View
    {
        $topDefect = collect(DB::connection("mysql_sb")->select($this->query));

        $this->rowCount = $topDefect->groupBy("grouping")->count();
        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $topDefect->groupBy("tanggal")->count()+3;
        if ($colCount > (count($alphabets)-1)) {
            $colStack = floor($colCount/(count($alphabets)-1));
            $colStackModulo = $colCount%(count($alphabets)-1);
            $this->colAlphabet = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabet = $alphabets[$colCount];
        }

        $colAlphabetChartStart = $colCount+3;
        if ($colAlphabetChartStart > (count($alphabets)-1)) {
            $colStack = floor($colAlphabetChartStart/(count($alphabets)-1));
            $colStackModulo = $colAlphabetChartStart%(count($alphabets)-1);
            $this->colAlphabetChartStart = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabetChartStart = $alphabets[$colAlphabetChartStart];
        }

        $colAlphabetChartEnd = $colCount+23;
        if ($colAlphabetChartEnd > (count($alphabets)-1)) {
            $colStack = floor($colAlphabetChartEnd/(count($alphabets)-1));
            $colStackModulo = $colAlphabetChartEnd%(count($alphabets)-1);
            $this->colAlphabetChartEnd = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabetChartEnd = $alphabets[$colAlphabetChartEnd];
        }

        return view('sewing.export.top-defect-export', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'ws' => $this->ws,
            'style' => $this->style,
            'color' => $this->color,
            'sewingLine' => $this->sewingLine,
            'rowCount' => $this->rowCount,
            'topDefect' => $topDefect
        ]);
    }

    public function charts()
    {
        // Defect
        $labelsDefect = [];
        $categoriesDefect = [];
        $valuesDefect = [];

        for ($i = 0; $i < $this->rowCount; $i++) {
            // Defect
            array_push($labelsDefect,
                new DataSeriesValues('String', '$A$'.($i+7).':$D$'.($i+7).'', null, 5)
            );

            array_push($categoriesDefect,
                new DataSeriesValues('String', '$E$6:$'.$this->colAlphabet.'$6', null, 5)
            );

            array_push($valuesDefect,
                new DataSeriesValues('Number', '$E$'.($i+7).':$'.$this->colAlphabet.'$'.($i+7).'', null, 5)
            );
        }

        // Eff
        $seriesDefect = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesDefect) - 1), $labelsDefect, $categoriesDefect, $valuesDefect);
        $plotDefect   = new PlotArea(null, [$seriesDefect]);

        $legendDefect = new Legend();
        $chartDefect  = new Chart('Line Style Defect Chart', new Title('Defect Chart'), $legendDefect, $plotDefect);

        $chartDefect->setTopLeftPosition($this->colAlphabetChartStart.'6');
        $chartDefect->setBottomRightPosition($this->colAlphabetChartEnd.'36');

        return $chartDefect;

        // $labels = [new DataSeriesValues('String', '$A$7:$F$7', null, 1)];
        // $categories = [new DataSeriesValues('String', '$G$6:$G$6', null, 3)];
        // $values = [new DataSeriesValues('Number', '$G$10:$G$10', null, 3)];

        // $series = new DataSeries(
        //     DataSeries::TYPE_LINECHART,
        //     DataSeries::GROUPING_STANDARD,
        //     [0],
        //     $labels,
        //     $categories,
        //     $values
        // );

        // $plotArea = new PlotArea(null, [$series]);
        // $legend = new Legend();
        // $title = new Title('Test Chart');

        // $chart = new Chart('test_chart', $title, $legend, $plotArea);
        // $chart->setTopLeftPosition('F2');
        // $chart->setBottomRightPosition('O15');

        // return $chart;
    }
}
