<?php
    namespace App\Exports\Sewing;

    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithMultipleSheets;
    use Illuminate\Contracts\Queue\ShouldQueue;

    class ReportDefectExport implements WithMultipleSheets, ShouldQueue
    {
        use Exportable;

        protected $defectReportQuery;
        protected $topDefectQuery;
        protected $dateFrom;
        protected $dateTo;
        protected $ws;
        protected $style;
        protected $color;
        protected $sewingLine;

        public function __construct($defectReportQuery, $topDefectQuery, $dateFrom, $dateTo, $ws, $style, $color, $sewingLine)
        {
            ini_set('max_execution_time', 3600); // boost only once here
            ini_set('memory_limit', '1024M'); // adjust as needed

            $this->defectReportQuery = $defectReportQuery;
            $this->topDefectQuery = $topDefectQuery;
            $this->dateFrom = $dateFrom;
            $this->dateTo = $dateTo;
            $this->ws = $ws ? $ws : "All WS";
            $this->style = $style ? $style : "All Style";
            $this->color = $color ? $color : "All Color";
            $this->sewingLine = $sewingLine ? $sewingLine : "All Sewing Line";
        }

        public function sheets(): array
        {
            $sheets = [];

            if ($this->defectReportQuery) {
                $sheets[] = new DefectRateExport($this->defectReportQuery, $this->dateFrom, $this->dateTo, $this->ws, $this->style, $this->color, $this->sewingLine);
            }

            if ($this->topDefectQuery) {
                $sheets[] = new TopDefectExport($this->topDefectQuery, $this->dateFrom, $this->dateTo, $this->ws, $this->style, $this->color, $this->sewingLine);
            }

            if(count($sheets) < 1) {
                $sheets[] = new NoDataExport();
            }

            return $sheets;
        }
    }
?>
