<?php
    namespace App\Exports;
    use App\Models\Summary\DataProduksi;
    use App\Models\Summary\DataDetailProduksi;
    use App\Models\Summary\DataDetailProduksiDay;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\WithMultipleSheets;
    use Illuminate\Contracts\Queue\ShouldQueue;

    class ExportReportOutput implements WithMultipleSheets, ShouldQueue
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
                    DATE_PART('month',tgl_produksi) = '".$this->bulan."'
                    AND DATE_PART('year',tgl_produksi) = '".$this->tahun."'
                ")
                ->groupBy('tgl_produksi')
                ->orderBy('tgl_produksi', 'ASC')
                ->get();

                $tanggalProduksiFirst = $monthly->first()->tgl_produksi;
                foreach ($monthly as $month) {
                    $sheets[] = new ExportReportOutputData($month->tgl_produksi, $tanggalProduksiFirst);
                }
            } else {
                $sheets[] = new ExportReportOutputData($this->tanggal, $this->tanggal);
            }

            if(count($sheets) < 1) {
                $sheets[] = new NoDataExport();
            }

            return $sheets;
        }
    }
?>
