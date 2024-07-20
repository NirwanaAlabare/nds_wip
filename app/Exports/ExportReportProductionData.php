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

    class ExportReportProductionData implements FromView, ShouldAutoSize, ShouldQueue, withTitle, WithColumnFormatting, WithEvents
    {
        use Exportable;

        private $tanggal;

        public function __construct($tanggal)
        {
            $this->tanggal = $tanggal;
        }

        public function view(): View
        {
            $dataDetailProduksiDay =  DataDetailProduksiDay::selectRaw("
                    data_detail_produksi.sewing_line,
                    data_produksi.no_ws,
                    master_buyer.nama_buyer,
                    data_produksi.no_style,
                    data_detail_produksi_day.smv,
                    chief.nama chief_name,
                    leader.nama leader_name,
                    admin.nama admin_name,
                    data_detail_produksi_day.man_power,
                    data_detail_produksi_day.mins_avail,
                    data_detail_produksi_day.target,
                    data_detail_produksi_day.output,
                    data_produksi.order_cfm_price_dollar,
                    data_produksi.order_cfm_price_rupiah,
                    data_produksi.kode_mata_uang,
                    data_detail_produksi_day.earning,
                    data_detail_produksi_day.efficiency,
                    data_detail_produksi_day.mins_prod,
                    data_detail_produksi_day.jam_aktual,
                    summary_line.mins_prod_line,
                    summary_line.mins_avail_line
                ")
                ->leftJoin("data_detail_produksi","data_detail_produksi.id","=","data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("data_produksi","data_produksi.id","=","data_detail_produksi.data_produksi_id")
                ->leftJoin("master_buyer","master_buyer.id","=","data_produksi.buyer_id")
                ->leftJoin("master_kurs_bi","master_kurs_bi.id","=","data_detail_produksi_day.kurs_bi_id")
                ->leftJoin("master_karyawan as chief","chief.id","=","data_detail_produksi_day.chief_enroll_id")
                ->leftJoin("master_karyawan as leader","leader.id","=","data_detail_produksi_day.leader_enroll_id")
                ->leftJoin("master_karyawan as admin","admin.id","=","data_detail_produksi_day.adm_enroll_id")
                ->leftJoin(
                    DB::raw("
                        (
                            SELECT
                                data_detail_produksi.sewing_line,
                                data_detail_produksi_day.tgl_produksi as summary_tanggal,
                                SUM(data_detail_produksi_day.mins_prod) as mins_prod_line,
                                SUM(data_detail_produksi_day.mins_avail) as mins_avail_line
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
                    "summary_line.sewing_line", "=", "data_detail_produksi.sewing_line"
                )
                ->whereRaw("
                    data_detail_produksi_day.tgl_produksi = '".$this->tanggal."'
                    AND summary_line.summary_tanggal = '".$this->tanggal."'
                ")
                ->orderBy('data_detail_produksi.sewing_line','ASC')
                ->get();

            $jamProduksi = array('07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00', '18:00');

            $hourlyDataDay = MasterPlanSB::selectRaw("
                    act_costing.kpno no_ws,
                    master_plan.sewing_line,
                    DATE_FORMAT(output_rfts.updated_at, '%H:%i') hour,
                    COUNT(output_rfts.id) hour_output
                ")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_rfts", "output_rfts.master_plan_id", "=", "master_plan.id")->
                where("master_plan.tgl_plan", $this->tanggal)->
                groupBy('act_costing.kpno', 'output_rfts.updated_at', 'master_plan.sewing_line',)->
                get();

            $hourlyDataDaySum = MasterPlanSB::selectRaw("
                    DATE_FORMAT(output_rfts.updated_at, '%H:%i') hour,
                    COUNT(output_rfts.id) sum_hour_output
                ")->
                leftJoin("output_rfts", "output_rfts.master_plan_id", "=", "master_plan.id")->
                where("master_plan.tgl_plan", $this->tanggal)->
                groupByRaw("DATE_FORMAT(output_rfts.updated_at, '%H:%i')")->
                get();

            $totalDataDetailProduksiDay = DataDetailProduksiDay::selectRaw("
                    SUM(data_detail_produksi_day.mins_avail) total_mins_avail,
                    SUM(data_detail_produksi_day.target) total_target,
                    SUM(data_detail_produksi_day.output) total_output,
                    SUM(data_detail_produksi_day.earning) total_earning_rupiah,
                    SUM(data_detail_produksi_day.mins_prod) total_mins_prod
                ")
                ->leftJoin("data_detail_produksi","data_detail_produksi.id","=","data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("data_produksi","data_produksi.id","=","data_detail_produksi.data_produksi_id")
                ->leftJoin("master_buyer","master_buyer.id","=","data_produksi.buyer_id")
                ->whereRaw("
                    data_detail_produksi_day.tgl_produksi = '".$this->tanggal."'
                ")
                ->groupBy('data_detail_produksi_day.tgl_produksi')
                ->first();

            $mp = DataDetailProduksiDay::selectRaw("
                    MAX(data_detail_produksi_day.man_power) mp
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
                ->whereRaw("
                    data_detail_produksi_day.tgl_produksi = '".$this->tanggal."'
                ")
                ->groupBy('data_detail_produksi_day.tgl_produksi', 'data_detail_produksi.sewing_line')
                ->get();

            $totalDataDetailProduksiDay->total_mp = $mp->sum('mp');

            $kurs = MasterKursBi::select("kurs_tengah")->where("tanggal_kurs_bi", $this->tanggal)->first();

            return view('sewing.report.excel.production-excel', [
                'dataDetailProduksiDay' => $dataDetailProduksiDay,
                'totalDataDetailProduksiDay' => $totalDataDetailProduksiDay,
                'jamProduksi' => $jamProduksi,
                'hourlyDataDay' => $hourlyDataDay,
                'hourlyDataDaySum' => $hourlyDataDaySum,
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
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
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

            // 1 Row
            $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(30);
        }
    }
?>
