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

class TopRejectExport implements FromView, ShouldAutoSize, WithCharts, WithTitle
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
        return 'reject';
    }

    public function view(): View
    {
        $topReject = collect(DB::connection("mysql_sb")->select($this->query));

        $this->rowCount = $topReject->groupBy("grouping")->count();
        $this->rowCountLine = $topReject->groupBy("line_grouping")->count();
        $this->rowCountStyle = $topReject->groupBy("style_grouping")->count();

        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $topReject->groupBy("tanggal")->count()+5;
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

        $colAlphabetChartStart = $colCount+3+1+1;
        if ($colAlphabetChartStart > (count($alphabets)-1)) {
            $colStack = floor($colAlphabetChartStart/(count($alphabets)-1));
            $colStackModulo = $colAlphabetChartStart%(count($alphabets)-1);
            $this->colAlphabetChartStart = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabetChartStart = $alphabets[$colAlphabetChartStart];
        }

        $colAlphabetChartEnd = $colCount+($this->rowCount > 23 ? 23 : ($this->rowCount < 11 ? 11 : $this->rowCount))+1+1;
        if ($colAlphabetChartEnd > (count($alphabets)-1)) {
            $colStack = floor($colAlphabetChartEnd/(count($alphabets)-1));
            $colStackModulo = $colAlphabetChartEnd%(count($alphabets)-1);
            $this->colAlphabetChartEnd = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabetChartEnd = $alphabets[$colAlphabetChartEnd];
        }

        return view('sewing.export.top-reject-export', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'ws' => $this->ws,
            'style' => $this->style,
            'color' => $this->color,
            'sewingLine' => $this->sewingLine,
            'department' => $this->department,
            'rowCount' => $this->rowCount,
            'topReject' => $topReject
        ]);
    }

    public function charts()
    {
        if ($this->rowCount > 0) {
            // Reject
            $labelsReject = [];
            $categoriesReject = [];
            $valuesReject = [];

            for ($i = 0; $i < $this->rowCount; $i++) {
                array_push($labelsReject,
                    new DataSeriesValues('String', 'reject!$A$'.($i+7).':$F$'.($i+7).'', null, 5)
                );

                array_push($categoriesReject,
                    new DataSeriesValues('String', 'reject!$G$6:$'.$this->colAlphabet.'$6', null, 5)
                );

                array_push($valuesReject,
                    new DataSeriesValues('Number', 'reject!$G$'.($i+7).':$'.$this->colAlphabet.'$'.($i+7).'', null, 5)
                );
            }

            $seriesReject = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesReject) - 1), $labelsReject, $categoriesReject, $valuesReject);
            $plotReject   = new PlotArea(null, [$seriesReject]);

            $legendReject = new Legend();
            $chartReject  = new Chart('Line Style Reject Chart', new Title('Reject Chart'), $legendReject, $plotReject);

            $chartReject->setTopLeftPosition($this->colAlphabetChartStart.'6');
            $chartReject->setBottomRightPosition($this->colAlphabetChartEnd.($this->rowCount > 36 ? 36 : ($this->rowCount < 5 ? 5+6 : $this->rowCount+6)));

            // // Reject Line
            // $labelsRejectLine = [];
            // $categoriesRejectLine = [];
            // $valuesRejectLine = [];

            // for ($i = 0; $i < $this->rowCountLine; $i++) {
            //     array_push($labelsRejectLine,
            //         new DataSeriesValues('String', 'reject!$A$'.($i+$this->rowCount+10).':$B$'.($i+$this->rowCount+10).'', null, 5)
            //     );

            //     array_push($categoriesRejectLine,
            //         new DataSeriesValues('String', 'reject!$C$'.($this->rowCount+9).':$'.$this->colAlphabetSub.'$'.($this->rowCount+9), null, 5)
            //     );

            //     array_push($valuesRejectLine,
            //         new DataSeriesValues('Number', 'reject!$C$'.($i+$this->rowCount+10).':$'.$this->colAlphabetSub.'$'.($i+$this->rowCount+10).'', null, 5)
            //     );
            // }

            // $seriesRejectLine = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesRejectLine) - 1), $labelsRejectLine, $categoriesRejectLine, $valuesRejectLine);
            // $plotRejectLine   = new PlotArea(null, [$seriesRejectLine]);

            // $legendRejectLine = new Legend();
            // $chartRejectLine  = new Chart('Line Reject Chart', new Title('Line Reject Chart'), $legendRejectLine, $plotRejectLine);

            // $chartRejectLine->setTopLeftPosition($this->colAlphabetChartStart.($this->rowCount+9));
            // $chartRejectLine->setBottomRightPosition($this->colAlphabetChartEnd.($this->rowCount+9+($this->rowCountLine > 36 ? 36 : ($this->rowCountLine < 5 ? 5 : $this->rowCountLine))));

            // // Reject Style
            // $labelsRejectStyle = [];
            // $categoriesRejectStyle = [];
            // $valuesRejectStyle = [];

            // for ($i = 0; $i < $this->rowCountStyle; $i++) {
            //     array_push($labelsRejectStyle,
            //         new DataSeriesValues('String', 'reject!$A$'.($i+$this->rowCount+$this->rowCountLine+12).':$B$'.($i+$this->rowCount+$this->rowCountLine+12).'', null, 5)
            //     );

            //     array_push($categoriesRejectStyle,
            //         new DataSeriesValues('String', 'reject!$C$'.($this->rowCount+$this->rowCountLine+11).':$'.$this->colAlphabetSub.'$'.($this->rowCount+$this->rowCountLine+11), null, 5)
            //     );

            //     array_push($valuesRejectStyle,
            //         new DataSeriesValues('Number', 'reject!$C$'.($i+$this->rowCount+$this->rowCountLine+12).':$'.$this->colAlphabetSub.'$'.($i+$this->rowCount+$this->rowCountLine+12).'', null, 5)
            //     );
            // }

            // $seriesRejectStyle = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesRejectStyle) - 1), $labelsRejectStyle, $categoriesRejectStyle, $valuesRejectStyle);
            // $plotRejectStyle   = new PlotArea(null, [$seriesRejectStyle]);

            // $legendRejectStyle = new Legend();
            // $chartRejectStyle  = new Chart('Style Reject Chart', new Title('Style Reject Chart'), $legendRejectStyle, $plotRejectStyle);

            // $chartRejectStyle->setTopLeftPosition($this->colAlphabetChartStart.($this->rowCount+$this->rowCountLine+11));
            // $chartRejectStyle->setBottomRightPosition($this->colAlphabetChartEnd.($this->rowCount+$this->rowCountLine+11+($this->rowCountStyle > 36 ? 36 : ($this->rowCountStyle < 5 ? 5 : $this->rowCountStyle))));

            // return [$chartReject, $chartRejectLine, $chartRejectStyle];
            return [$chartReject];
        }

        return [];
    }
}
