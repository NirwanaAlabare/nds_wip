<?php
    namespace App\Exports\Sewing;

    use App\Models\Summary\DataProduksi;
    use App\Models\Summary\DataDetailProduksi;
    use App\Models\Summary\DataDetailProduksiDay;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithMultipleSheets;
    use Illuminate\Contracts\Queue\ShouldQueue;

    class ExportReportProduction implements WithMultipleSheets, ShouldQueue
    {
        use Exportable;

        protected $periode;
        protected $tanggal;
        protected $bulan;
        protected $tahun;

        public function __construct($periode, $tanggal)
        {
            $this->periode = $periode;
            $this->tanggal = $tanggal;
            $this->bulan = substr($this->tanggal, 5,2);
            $this->tahun = substr($this->tanggal, 0,4);
        }

        public function sheets(): array
        {
            $sheets = [];

            if($this->periode == "monthly") {
                $monthly =  DataDetailProduksiDay::selectRaw("
                    tgl_produksi
                ")
                ->whereRaw("
                    MONTH(tgl_produksi) = '".$this->bulan."'
                    AND YEAR(tgl_produksi) = '".$this->tahun."'
                ")
                ->groupBy('tgl_produksi')
                ->get();

                foreach ($monthly as $month) {
                    $sheets[] = new ExportReportProductionData($month->tgl_produksi);
                }
            } else {
                $sheets[] = new ExportReportProductionData($this->tanggal);
            }

            if(count($sheets) < 1) {
                $sheets[] = new NoDataExport();
            }

            return $sheets;
        }
    }
?>
