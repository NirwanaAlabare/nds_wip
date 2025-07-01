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
    protected $department;
    protected $rowCount;
    protected $colAlphabet;
    protected $colAlphabetSub;
    protected $colAlphabetChartStart;
    protected $colAlphabetChartEnd;

    function __construct($query, $dateFrom, $dateTo, $ws, $style, $color, $sewingLine, $department) {
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
        return 'defect';
    }

    public function view(): View
    {
        $topDefect = collect(DB::connection("mysql_sb")->select($this->query));

        $this->rowCount = $topDefect->groupBy("grouping")->count();
        $this->rowCountLine = $topDefect->groupBy("line_grouping")->count();
        $this->rowCountStyle = $topDefect->groupBy("style_grouping")->count();

        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $topDefect->groupBy("tanggal")->count()+4;
        if ($colCount > (count($alphabets)-1)) {
            $colStack = floor($colCount/(count($alphabets)-1));
            $colStackModulo = $colCount%(count($alphabets)-1);
            $this->colAlphabet = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabet = $alphabets[$colCount];
        }

        $colAlphabetSub = $colCount-2;
        if ($colAlphabetSub > (count($alphabets)-1)) {
            $colStack = floor($colAlphabetSub/(count($alphabets)-1));
            $colStackModulo = $colAlphabetSub%(count($alphabets)-1);
            $this->colAlphabetSub = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabetSub = $alphabets[$colAlphabetSub];
        }

        $colAlphabetChartStart = $colCount+3+1;
        if ($colAlphabetChartStart > (count($alphabets)-1)) {
            $colStack = floor($colAlphabetChartStart/(count($alphabets)-1));
            $colStackModulo = $colAlphabetChartStart%(count($alphabets)-1);
            $this->colAlphabetChartStart = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabetChartStart = $alphabets[$colAlphabetChartStart];
        }

        $colAlphabetChartEnd = $colCount+($this->rowCount > 23 ? 23 : ($this->rowCount < 11 ? 11 : $this->rowCount))+1;
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
            'department' => $this->department,
            'rowCount' => $this->rowCount,
            'topDefect' => $topDefect
        ]);
    }

    public function charts()
    {
        if ($this->rowCount > 0) {
            // Defect
            $labelsDefect = [];
            $categoriesDefect = [];
            $valuesDefect = [];

            for ($i = 0; $i < $this->rowCount; $i++) {
                array_push($labelsDefect,
                    new DataSeriesValues('String', 'defect!$A$'.($i+7).':$E$'.($i+7).'', null, 5)
                );

                array_push($categoriesDefect,
                    new DataSeriesValues('String', 'defect!$F$6:$'.$this->colAlphabet.'$6', null, 5)
                );

                array_push($valuesDefect,
                    new DataSeriesValues('Number', 'defect!$F$'.($i+7).':$'.$this->colAlphabet.'$'.($i+7).'', null, 5)
                );
            }

            $seriesDefect = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesDefect) - 1), $labelsDefect, $categoriesDefect, $valuesDefect);
            $plotDefect   = new PlotArea(null, [$seriesDefect]);

            $legendDefect = new Legend();
            $chartDefect  = new Chart('Line Style Defect Chart', new Title('Defect Chart'), $legendDefect, $plotDefect);

            $chartDefect->setTopLeftPosition($this->colAlphabetChartStart.'6');
            $chartDefect->setBottomRightPosition($this->colAlphabetChartEnd.($this->rowCount > 36 ? 36 : ($this->rowCount < 5 ? 5+6 : $this->rowCount+6)));

            // // Defect Line
            // $labelsDefectLine = [];
            // $categoriesDefectLine = [];
            // $valuesDefectLine = [];

            // for ($i = 0; $i < $this->rowCountLine; $i++) {
            //     array_push($labelsDefectLine,
            //         new DataSeriesValues('String', 'defect!$A$'.($i+$this->rowCount+10).':$B$'.($i+$this->rowCount+10).'', null, 5)
            //     );

            //     array_push($categoriesDefectLine,
            //         new DataSeriesValues('String', 'defect!$C$'.($this->rowCount+9).':$'.$this->colAlphabetSub.'$'.($this->rowCount+9), null, 5)
            //     );

            //     array_push($valuesDefectLine,
            //         new DataSeriesValues('Number', 'defect!$C$'.($i+$this->rowCount+10).':$'.$this->colAlphabetSub.'$'.($i+$this->rowCount+10).'', null, 5)
            //     );
            // }

            // $seriesDefectLine = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesDefectLine) - 1), $labelsDefectLine, $categoriesDefectLine, $valuesDefectLine);
            // $plotDefectLine   = new PlotArea(null, [$seriesDefectLine]);

            // $legendDefectLine = new Legend();
            // $chartDefectLine  = new Chart('Line Defect Chart', new Title('Line Defect Chart'), $legendDefectLine, $plotDefectLine);

            // $chartDefectLine->setTopLeftPosition($this->colAlphabetChartStart.($this->rowCount+9));
            // $chartDefectLine->setBottomRightPosition($this->colAlphabetChartEnd.($this->rowCount+9+($this->rowCountLine > 36 ? 36 : ($this->rowCountLine < 5 ? 5 : $this->rowCountLine))));

            // // Defect Style
            // $labelsDefectStyle = [];
            // $categoriesDefectStyle = [];
            // $valuesDefectStyle = [];

            // for ($i = 0; $i < $this->rowCountStyle; $i++) {
            //     array_push($labelsDefectStyle,
            //         new DataSeriesValues('String', 'defect!$A$'.($i+$this->rowCount+$this->rowCountLine+12).':$B$'.($i+$this->rowCount+$this->rowCountLine+12).'', null, 5)
            //     );

            //     array_push($categoriesDefectStyle,
            //         new DataSeriesValues('String', 'defect!$C$'.($this->rowCount+$this->rowCountLine+11).':$'.$this->colAlphabetSub.'$'.($this->rowCount+$this->rowCountLine+11), null, 5)
            //     );

            //     array_push($valuesDefectStyle,
            //         new DataSeriesValues('Number', 'defect!$C$'.($i+$this->rowCount+$this->rowCountLine+12).':$'.$this->colAlphabetSub.'$'.($i+$this->rowCount+$this->rowCountLine+12).'', null, 5)
            //     );
            // }

            // $seriesDefectStyle = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesDefectStyle) - 1), $labelsDefectStyle, $categoriesDefectStyle, $valuesDefectStyle);
            // $plotDefectStyle   = new PlotArea(null, [$seriesDefectStyle]);

            // $legendDefectStyle = new Legend();
            // $chartDefectStyle  = new Chart('Style Defect Chart', new Title('Style Defect Chart'), $legendDefectStyle, $plotDefectStyle);

            // $chartDefectStyle->setTopLeftPosition($this->colAlphabetChartStart.($this->rowCount+$this->rowCountLine+11));
            // $chartDefectStyle->setBottomRightPosition($this->colAlphabetChartEnd.($this->rowCount+$this->rowCountLine+11+($this->rowCountStyle > 36 ? 36 : ($this->rowCountStyle < 5 ? 5 : $this->rowCountStyle))));

            // return [$chartDefect, $chartDefectLine, $chartDefectStyle];

            return [$chartDefect];
        }

        return [];
    }
}
