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
            if ($this->periode == "monthly") {
                $dateFilter = "date(a.updated_at) >= '".$this->tahun."-".$this->bulan."-01' AND date(a.updated_at) <= '".$this->tahun."-".$this->bulan."-31'";
            } else {
                $dateFilter = "date(a.updated_at) = '".$this->tanggal."' ";
            }

            $dataDetailProduksiDay = collect(
                DB::connection("mysql_sb")->select("
                    select
                    u.name sewing_line,
                    ac.kpno no_ws,
                    ms.Supplier nama_buyer,
                    ac.styleno no_style,
                    date(a.updated_at) tgl_produksi,
                    mp.smv,
                    mp.man_power,
                    mp.man_power * mp.jam_kerja * 60 mins_avail,
                    mp.plan_target target,
                    count(so_det_id) output,
                    acm.price order_cfm_price,
                    ac.curr kode_mata_uang,
                    round(count(so_det_id) * acm.price,2) earning,
                    (CASE WHEN ac.curr = 'USD' THEN round(count(so_det_id) * acm.price * mk.kurs_tengah, 2) ELSE round(count(so_det_id) * acm.price,2) END) earning_rupiah,
                    (CASE WHEN ac.curr = 'USD' THEN round(count(so_det_id) * acm.price,2) ELSE 0 END) earning_dollar,
                    round(count(so_det_id) * mp.smv,2) mins_prod,
                    round(round(tot.tot_output * mp.smv,2) / (mp.man_power * 8 * 60) * 100,2) efficiency,
                    mp.jam_kerja jam_aktual,
                    mk.kurs_tengah,
                    created_by,
                    master_plan_id,
                    so_det_id
                    from output_rfts a
                    inner join user_sb_wip u on a.created_by = u.id
                    inner join master_plan mp on a.master_plan_id = mp.id
                    inner join so_det sd on a.so_det_id = sd.id
                    inner join so on sd.id_so = so.id
                    inner join act_costing ac on so.id_cost = ac.id
                    left join (
                    select * from act_costing_mfg where id_item = '8'
                    ) acm on ac.id = acm.id_act_cost
                    inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                    left join master_kurs_bi mk on DATE(a.updated_at) = mk.tanggal_kurs_bi and ac.curr = mk.mata_uang
                    left join (
                    select count(so_det_id) tot_output, u.name from output_rfts a
                    inner join user_sb_wip u on a.created_by = u.id
                    where a.updated_at >= '".$this->tanggal." 00:00:00' and a.updated_at <= '".$this->tanggal." 23:59:59'
                    group by u.name, date(a.updated_at)
                    ) tot on u.name = tot.name
                    where a.updated_at >= '".$this->tanggal." 00:00:00' and a.updated_at <= '".$this->tanggal." 23:59:59'
                    group by u.name, ac.styleno, date(updated_at)
                    order by u.name asc
                ")
            );

            $totalDataDetailProduksiDay = collect([
                "total_mins_avail" => $dataDetailProduksiDay->sum("mins_avail"),
                "total_target" => $dataDetailProduksiDay->sum("target"),
                "total_output" => $dataDetailProduksiDay->sum("output"),
                "total_earning" => $dataDetailProduksiDay->sum("earning"),
                "total_earning_rupiah" => $dataDetailProduksiDay->sum("earning_rupiah"),
                "total_earning_dollar" => $dataDetailProduksiDay->sum("earning_dollar"),
                "total_mins_prod" => $dataDetailProduksiDay->sum("mins_prod"),
                "total_mp" => $dataDetailProduksiDay->groupBy("sewing_line")->map(function ($row) { return $row->avg('man_power'); })->sum()
            ]);

            $kurs = DB::connection("mysql_sb")->table("master_kurs_bi")->select("kurs_tengah")->where("tanggal_kurs_bi", $this->tanggal)->first();

            $this->rowCount = $dataDetailProduksiDay->count()+5;

            return view('sewing.report.excel.efficiency-excel', [
                'dataDetailProduksiDay' => $dataDetailProduksiDay,
                'totalDataDetailProduksiDay' => $totalDataDetailProduksiDay,
                'kurs' => $kurs,
                'tanggal' => $this->tanggal
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
