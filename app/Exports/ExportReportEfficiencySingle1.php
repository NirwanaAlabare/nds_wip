<?php
    namespace App\Exports;
    use App\Models\Summary\DataProduksi;
    use App\Models\Summary\DataDetailProduksi;
    use App\Models\Summary\DataDetailProduksiDay;
    use App\Models\Summary\MasterKursBI;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\FromView;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;
    use Maatwebsite\Excel\Concerns\WithTitle;
    use Maatwebsite\Excel\Concerns\WithColumnFormatting;
    use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
    use Maatwebsite\Excel\Concerns\WithEvents;
    use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
    use Maatwebsite\Excel\Events\AfterSheet;
    use Maatwebsite\Excel\Sheet;
    use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
    use PhpOffice\PhpSpreadsheet\Shared\Date;
    use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\View\View;
    use Carbon\Carbon;
    use DB;

    Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
        $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
    });

    class ExportReportEfficiencySingle implements FromView, ShouldAutoSize, ShouldQueue, withTitle, WithColumnFormatting, WithEvents
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

        public function view(): View
        {
            $dateFilter = "";
            $dateFilter2 = "";
            if ($this->periode == "monthly") {
                $dateFilter = "MONTH(data_detail_produksi_day.tgl_produksi) = '".$this->bulan."' AND YEAR(data_detail_produksi_day.tgl_produksi) = '".$this->tahun."'";
                $dateFilter2 = "MONTH(summary_line.tgl_produksi_line) = '".$this->bulan."' AND YEAR(summary_line.tgl_produksi_line) = '".$this->tahun."'";
            } else {
                $dateFilter = "data_detail_produksi_day.tgl_produksi = '".$this->tanggal."' ";
                $dateFilter2 = "summary_line.tgl_produksi_line = '".$this->tanggal."' ";
            }

            $dataDetailProduksiDay =  DataDetailProduksiDay::selectRaw("
                    data_detail_produksi_day.chief_enroll_id,
                    data_detail_produksi.sewing_line,
                    data_produksi.no_ws,
                    master_buyer.nama_buyer,
                    data_produksi.no_style,
                    data_detail_produksi_day.smv,
                    data_detail_produksi_day.man_power,
                    data_detail_produksi_day.mins_avail,
                    data_detail_produksi_day.target,
                    data_detail_produksi_day.output,
                    data_detail_produksi_day.output_rft,
                    data_produksi.kode_mata_uang,
                    data_produksi.order_cfm_price,
                    data_produksi.order_cfm_price_dollar,
                    data_produksi.order_cfm_price_rupiah,
                    data_detail_produksi_day.earning,
                    data_detail_produksi_day.efficiency,
                    data_detail_produksi_day.mins_prod,
                    (summary_line.mins_prod_line/summary_line.mins_avail_line * 100) line_efficiency,
                    data_detail_produksi_day.jam_aktual,
                    data_detail_produksi_day.tgl_produksi,
                    summary_line.tgl_produksi_line,
                    summary_line.mins_avail_line,
                    summary_line.mins_prod_line,
                    summary_line.output_line,
                    summary_line.output_rft_line
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("data_produksi","data_produksi.id","=","data_detail_produksi.data_produksi_id")
                ->leftJoin("master_buyer","master_buyer.id","=","data_produksi.buyer_id")
                ->leftJoin(
                    DB::raw("
                        (
                            SELECT
                                data_detail_produksi.sewing_line,
                                data_detail_produksi_day.tgl_produksi as tgl_produksi_line,
                                SUM(data_detail_produksi_day.mins_prod) as mins_prod_line,
                                SUM(data_detail_produksi_day.mins_avail) as mins_avail_line,
                                SUM(data_detail_produksi_day.output) as output_line,
                                SUM(data_detail_produksi_day.output_rft) as output_rft_line
                            FROM
                                data_detail_produksi_day
                            LEFT JOIN
                                data_detail_produksi
                            ON
                                data_detail_produksi.id = data_detail_produksi_day.data_detail_produksi_id
                            GROUP BY
                                data_detail_produksi.sewing_line, data_detail_produksi_day.tgl_produksi
                        ) summary_line
                    "),
                    function ($join) {
                        $join->on("summary_line.sewing_line", "=", "data_detail_produksi.sewing_line");
                        $join->on("summary_line.tgl_produksi_line", "=", "data_detail_produksi_day.tgl_produksi");
                    }
                )
                ->whereRaw($dateFilter)
                ->whereRaw($dateFilter2)
                ->orderBy('data_detail_produksi_day.tgl_produksi', 'desc')
                ->orderBy('data_detail_produksi.sewing_line', 'asc')
                ->orderBy('data_produksi.no_ws', 'asc')
                ->get();

            $totalDataDetailProduksiDay = DataDetailProduksiDay::selectRaw("
                    SUM(data_detail_produksi_day.mins_avail) total_mins_avail,
                    SUM(data_detail_produksi_day.target) total_target,
                    SUM(data_detail_produksi_day.output) total_output,
                    SUM(data_detail_produksi_day.output_rft) total_output_rft,
                    SUM(CASE WHEN data_produksi.kode_mata_uang = 'IDR' THEN data_detail_produksi_day.earning ELSE 0 END) total_earning_rupiah,
                    SUM(CASE WHEN data_produksi.kode_mata_uang != 'IDR' THEN data_detail_produksi_day.earning ELSE 0 END) total_earning_dollar,
                    SUM(data_detail_produksi_day.mins_prod) total_mins_prod
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("data_produksi", "data_produksi.id", "=", "data_detail_produksi.data_produksi_id")
                ->whereRaw($dateFilter)
                ->groupBy('data_detail_produksi_day.tgl_produksi')
                ->first();

            $allMp = DataDetailProduksiDay::selectRaw("
                    MAX(data_detail_produksi_day.man_power) mp
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=" ,"data_detail_produksi_day.data_detail_produksi_id")
                ->whereRaw($dateFilter)
                ->groupBy('data_detail_produksi_day.tgl_produksi', 'data_detail_produksi.sewing_line')
                ->get();

            $totalDataDetailProduksiDay->total_mp = $allMp->sum('mp');

            $summaryChiefDay = DataDetailProduksiDay::selectRaw("
                    master_karyawan.id,
                    master_karyawan.nama,
                    data_detail_produksi_day.chief_enroll_id,
                    SUM(data_detail_produksi_day.mins_avail) total_mins_avail,
                    SUM(data_detail_produksi_day.mins_prod) total_mins_prod
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("master_karyawan", "master_karyawan.id", "=", "data_detail_produksi_day.chief_enroll_id")
                ->whereRaw($dateFilter)
                ->groupBy('data_detail_produksi_day.tgl_produksi', 'data_detail_produksi_day.chief_enroll_id', "master_karyawan.id")
                ->get();

            $kurs = MasterKursBI::select("kurs_tengah")->
            whereRaw("
                MONTH(tanggal_kurs_bi) = '".$this->bulan."'
                AND YEAR(tanggal_kurs_bi) = '".$this->tahun."'
            ")->first();

            $this->rowCount = $dataDetailProduksiDay->count()+5;

            return view('sewing.report.excel.efficiency-excel', [
                'dataDetailProduksiDay' => $dataDetailProduksiDay,
                'totalDataDetailProduksiDay' => $totalDataDetailProduksiDay,
                'summaryChiefDay' => $summaryChiefDay,
                'kurs' => $kurs,
                'tanggal' => $this->periode == 'monthly' ? $this->tahun." - ".$this->bulan : $this->tanggal
            ]);
        }

        public function title(): string
        {
            return substr($this->tanggal,-2,2);
        }

        public function columnFormats(): array
        {
            return [
                'A2:A2' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            ];
        }

        public function registerEvents(): array
        {
            return [
                AfterSheet::class => [self::class, 'afterSheet']
            ];
        }

        public static function afterSheet(AfterSheet $event){
            //Single Column
            $event->sheet->styleCells(
                'M4',
                [
                    'font' => [
                        'size'=>12,
                        'bold'=>true
                    ]
                ]
            );

            // 1 Row
            $event->sheet->getDelegate()->getRowDimension(5)->setRowHeight(30);

            //Range Columns
            $event->sheet->styleCells(
                'K2:L2',
                [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => '7bd235']
                    ],
                    'font' => [
                        'size' => 13,
                        'bold' => true
                    ]
                ]
            );

            $event->sheet->styleCells(
                'E4:I4',
                [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'fcc283']
                    ],
                ]
            );

            $event->sheet->styleCells(
                'K4:L4',
                [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'fcc283']
                    ]
                ]
            );

            $event->sheet->styleCells(
                'O4:Q4',
                [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'fcc283']
                    ]
                ]
            );

            $event->sheet->styleCells(
                'P4:Q4',
                [
                    'font' => [
                        'size'=>12,
                        'bold'=>true
                    ]
                ]
            );

            $event->sheet->styleCells(
                'A5:Q5',
                [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => ['argb' => 'b3b9c4']
                    ]
                ]
            );

            $event->sheet->styleCells(
                'A5:W5',
                [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ],
                    'font' => [
                        'size' => 12
                    ]
                ]
            );

            $event->sheet->styleCells(
                'A6:S'.$event->getConcernable()->rowCount,
                [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]
            );
        }
    }
?>
