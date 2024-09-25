<?php
    namespace App\Exports;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithMultipleSheets;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use DB;

    class ExportReportCutting implements WithMultipleSheets, ShouldQueue
    {
        use Exportable;

        protected $dateFrom, $dateTo;

        public function __construct($dateFrom, $dateTo)
        {
            $this->dateFrom = $dateFrom;
            $this->dateTo = $dateTo;
        }

        public function sheets(): array
        {
            $dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
            $dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');
            $additionalQuery = "";

            $sheets = [];

            if($dateFrom < $dateTo) {
                if ($dateFrom) {
                    $additionalQuery .= " and DATE(form_cut_input.waktu_mulai) >= '".$dateFrom."'";
                }

                if ($dateTo) {
                    $additionalQuery .= " and DATE(form_cut_input.waktu_mulai) <= '".$dateTo."'";
                }

                $reportCutting = DB::select("
                    SELECT
                        DATE(form_cut_input.waktu_mulai) tgl_form_cut
                    FROM
                        form_cut_input
                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                    WHERE
                        form_cut_input.`status` != 'SPREADING'
                        ".$additionalQuery."
                    GROUP BY
                        DATE(form_cut_input.waktu_mulai)
                ");


                foreach ($reportCutting as $cutting) {
                    $sheets[] = new ExportReportCuttingData($cutting->tgl_form_cut);
                }
            } else {
                $sheets[] = new ExportReportCuttingData($dateFrom);
            }

            if(count($sheets) < 1) {
                $sheets[] = new NoDataExport();
            }

            return $sheets;
        }
    }
?>
