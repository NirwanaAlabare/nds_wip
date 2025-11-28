<?php
    namespace App\Exports\Sewing;

    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithMultipleSheets;
    use Illuminate\Contracts\Queue\ShouldQueue;

    class ReportDefectExport implements WithMultipleSheets, ShouldQueue
    {
        use Exportable;

        protected $defectRateQuery;
        protected $topDefectQuery;
        protected $dateFrom;
        protected $dateTo;
        protected $ws;
        protected $style;
        protected $color;
        protected $sewingLine;
        protected $department;

        public function __construct($defectRateQuery, $topDefectQuery, $topRejectQuery, $dateFrom, $dateTo, $base_ws, $ws, $style, $color, $sewingLine, $department)
        {
            ini_set('max_execution_time', 36000); // boost only once here
            ini_set('memory_limit', '2048M'); // adjust as needed

            $this->defectRateQuery = $defectRateQuery;
            $this->topDefectQuery = $topDefectQuery;
            $this->topRejectQuery = $topRejectQuery;
            $this->dateFrom = $dateFrom;
            $this->dateTo = $dateTo;
            $this->ws = ($base_ws ? $base_ws : ($ws ? $ws : "All WS"));
            $this->style = $style ? $style : "All Style";
            $this->color = $color ? $color : "All Color";
            $this->sewingLine = $sewingLine ? $sewingLine : "All Sewing Line";
            $this->department = $department ? $department : "";
        }

        public function sheets(): array
        {
            $sheets = [];

            if ($this->defectRateQuery) {
                $sheets[] = new DefectRateExport($this->defectRateQuery, $this->dateFrom, $this->dateTo, $this->ws, $this->style, $this->color, $this->sewingLine, $this->department);
            }

            if ($this->topDefectQuery) {
                $sheets[] = new TopDefectExport($this->topDefectQuery, $this->dateFrom, $this->dateTo, $this->ws, $this->style, $this->color, $this->sewingLine, $this->department);
            }

            if ($this->topRejectQuery) {
                $sheets[] = new TopRejectExport($this->topRejectQuery, $this->dateFrom, $this->dateTo, $this->ws, $this->style, $this->color, $this->sewingLine, $this->department);
            }

            if(count($sheets) < 1) {
                $sheets[] = new NoDataExport();
            }

            return $sheets;
        }
    }
?>
