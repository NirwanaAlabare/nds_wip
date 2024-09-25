<?php
    namespace App\Exports;
    use App\Models\Summary\DataProduksi;
    use App\Models\Summary\DataDetailProduksi;
    use App\Models\Summary\DataDetailProduksiDay;
    use App\Models\Summary\MasterPlanSB;
    use App\Models\Summary\MasterKursBi;
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
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\View\View;
    use DB;

    Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
        $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
    });

    class ExportReportDetailOutputData implements FromView, ShouldAutoSize, ShouldQueue, withTitle, WithEvents
    {
        use Exportable;

        protected $periode;
        protected $tanggal;
        protected $bulan;
        protected $tahun;
        protected $rowCount;

        public function __construct($periode, $tanggal)
        {
            $this->periode = $periode;
            $this->tanggal = $tanggal;
            $this->bulan = substr($this->tanggal, 5,2);
            $this->tahun = substr($this->tanggal, 0,4);
        }

        public function view(): View
        {
            $dataDetailProduksiDay = MasterPlanSB::on("mysql_sb")->
                selectRaw("
                    master_plan.tgl_plan,
                    master_plan.id_ws,
                    so_det.id so_det_id,
                    GROUP_CONCAT(DISTINCT REPLACE(master_plan.sewing_line, 'line_', '') ORDER BY master_plan.sewing_line ASC SEPARATOR '/') sewing_line,
                    MAX(act_costing.kpno) no_ws,
                    MAX(act_costing.styleno) no_style,
                    MAX(so_det.color) color,
                    MAX(so_det.size) size,
                    MAX(mastersupplier.Supplier) nama_buyer
                ")
                ->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")
                ->leftJoin("so", "so.id_cost", "=", "act_costing.id")
                ->leftJoin("so_det", "so_det.id_so", "=", "so.id")
                ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer");

            if ($this->periode == 'monthly') {
                $this->tanggal = $this->tahun.'-'.$this->bulan;
                $dataDetailProduksiDay->whereRaw("
                    (MONTH(master_plan.tgl_plan) = '".$this->bulan."' AND YEAR(master_plan.tgl_plan) = '".$this->tahun."')
                ");
            } else {
                $this->tanggal = $this->tanggal;
                $dataDetailProduksiDay->whereRaw("master_plan.tgl_plan = '".$this->tanggal."'");
            }

            $filterDetailProduksiDay = $dataDetailProduksiDay->groupBy(
                'master_plan.tgl_plan',
                'master_plan.id_ws',
                'act_costing.id',
                'so_det.id'
            )->
            orderBy('master_plan.tgl_plan', 'asc')->
            orderBy('act_costing.kpno', 'asc')->
            orderBy('act_costing.styleno', 'asc')->
            get();

            foreach($filterDetailProduksiDay as $day) {
                $output = MasterPlanSB::on('mysql_sb')->
                    selectRaw('
                        count(output_rfts.id) output,
                        output_rfts.so_det_id,
                        master_plan.tgl_plan,
                        master_plan.id_ws
                    ')->
                    leftJoin('output_rfts', 'output_rfts.master_plan_id', '=', 'master_plan.id')->
                    where('master_plan.tgl_plan', $day->tgl_plan)->
                    where('master_plan.id_ws', $day->id_ws)->
                    where('output_rfts.so_det_id', $day->so_det_id)->
                    groupBy('master_plan.tgl_plan', 'master_plan.id_ws', 'output_rfts.so_det_id')->
                    first();
                $day->output = $output ? $output->output : 0;
            }

            $this->rowCount = $filterDetailProduksiDay->count() + 3;

            return view('sewing.report.excel.detail-output-excel', [
                'dataDetailProduksiDay' => $filterDetailProduksiDay,
                'tanggal' => $this->tanggal
            ]);
        }

        public function title(): string
        {
            if ($this->periode == 'monthly') {
                return $this->bulan.'-'.$this->tahun;
            } else {
                return $this->tanggal;
            }
        }

        public function columnFormats(): array
        {
            return [
                //
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
            // $event->sheet->styleCells(
            //             'M3',
            //             [
            //                 'font' => [
            //                     'size'=>12,
            //                     'bold'=>true
            //                 ]
            //             ]
            //         );

            //Range Columns
            $event->sheet->styleCells(
                2,
                [
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        // 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ],
                    // 'fill' => [
                    //     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    //     'color' => ['argb' => '3d7ec9']
                    // ],
                    'font' => [
                        'size' => 12,
                        // 'color' => ['argb' => 'fbfbfb']
                    ]
                ]
            );

            $event->sheet->styleCells(
                'A2:K'.$event->getConcernable()->rowCount,
                [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]
            );

            // 1 Row
            // $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(30);
        }
    }
?>
