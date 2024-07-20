<?php
    namespace App\Exports;
    use App\Models\Summary\DataProduksi;
    use App\Models\Summary\DataDetailProduksi;
    use App\Models\Summary\DataDetailProduksiDay;
    use App\Models\Summary\UserPassword;
    use App\Models\Summary\MasterKursBi;
    use Maatwebsite\Excel\Concerns\Exportable;
    use Maatwebsite\Excel\Concerns\FromView;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;
    use Maatwebsite\Excel\Concerns\WithTitle;
    use Maatwebsite\Excel\Concerns\WithColumnFormatting;
    use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
    use Maatwebsite\Excel\Concerns\WithEvents;
    use Maatwebsite\Excel\Events\AfterSheet;
    use Maatwebsite\Excel\Sheet;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Contracts\View\View;
    use DB;

    Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
        $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
    });

    class ExportReportOutputData implements FromView, ShouldAutoSize, ShouldQueue, withTitle, WithColumnFormatting, WithEvents
    {
        use Exportable;

        private $tanggalProduksi;
        private $tanggalProduksiFirst;
        private $tanggalProduksiBefore;
        private $bulanProduksi;

        public function __construct($tanggalProduksi, $tanggalProduksiFirst)
        {
            $this->tanggalProduksi = $tanggalProduksi;
            $this->tanggalProduksiFirst = $tanggalProduksiFirst;
            $this->tanggalProduksiBefore = date("Y-m-d", strtotime( $tanggalProduksi.' - 1 days' ));
            $this->bulanProduksi = substr($tanggalProduksi, 5,2);
            $this->colAlphabet = '';
            $this->rowNumber = 0;
        }

        public function view(): View
        {
            $dataDetailProduksiDay = DataDetailProduksiDay::selectRaw("
                    data_detail_produksi_day.tgl_produksi,
                    data_produksi.id data_produksi_id,
                    data_produksi.tanggal_delivery,
                    data_produksi.no_ws,
                    data_produksi.no_style,
                    data_produksi.kode_mata_uang,
                    data_produksi.order_cfm_price,
                    data_produksi.order_cfm_price_rupiah,
                    data_produksi.order_cfm_price_dollar,
                    (CASE WHEN data_produksi.order_qty_cutting <= 0 OR data_produksi.order_qty_cutting IS NULL THEN data_produksi.order_qty ELSE data_produksi.order_qty_cutting END) order_qty_cutting,
                    STRING_AGG(DISTINCT master_buyer.nama_buyer, ', ') nama_buyer,
                    AVG(data_detail_produksi_day.smv) smv,
                    SUM(data_detail_produksi_day.output)  output,
                    SUM(data_detail_produksi_day.cumulative_output) cumulative_output,
                    (SUM(data_detail_produksi_day.cumulative_output)-SUM(data_detail_produksi_day.output)) before_output,
                    ((CASE WHEN data_produksi.order_qty_cutting <= 0 OR data_produksi.order_qty_cutting IS NULL THEN data_produksi.order_qty ELSE data_produksi.order_qty_cutting END) - (SUM(data_detail_produksi_day.cumulative_output) - SUM(data_detail_produksi_day.output)) - SUM(data_detail_produksi_day.output)) cumulative_balance,
                    ((CASE WHEN data_produksi.order_qty_cutting <= 0 OR data_produksi.order_qty_cutting IS NULL THEN data_produksi.order_qty ELSE data_produksi.order_qty_cutting END) - (SUM(data_detail_produksi_day.cumulative_output) - SUM(data_detail_produksi_day.output))) before_balance,
                    SUM(data_detail_produksi_day.earning) earning,
                    SUM(data_detail_produksi_day.cumulative_earning) cumulative_earning
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("data_produksi", "data_produksi.id", "=", "data_detail_produksi.data_produksi_id")
                ->leftJoin("master_buyer", "master_buyer.id", "=", "data_produksi.buyer_id")
                ->where('data_detail_produksi_day.tgl_produksi', $this->tanggalProduksi)
                ->groupBy("data_produksi.id", "data_detail_produksi_day.tgl_produksi")
                ->get();

            $dataLineProduksiDay = DataDetailProduksiDay::selectRaw("
                    data_produksi.id data_produksi_id,
                    data_detail_produksi.sewing_line,
                    SUM(data_detail_produksi_day.output) output,
                    MAX(cumulative.output) cumulative_output
                ")
                ->leftJoin("data_detail_produksi", "data_detail_produksi.id","=","data_detail_produksi_day.data_detail_produksi_id")
                ->leftJoin("data_produksi", "data_produksi.id","=","data_detail_produksi.data_produksi_id")
                ->leftJoin(
                    DB::raw("
                        (
                            SELECT
                                data_detail_produksi.sewing_line,
                                SUM(data_detail_produksi_day.output) output
                            FROM
                                data_detail_produksi_day
                            LEFT JOIN
                                data_detail_produksi ON data_detail_produksi.id = data_detail_produksi_day.data_detail_produksi_id
                            WHERE
                                data_detail_produksi_day.tgl_produksi BETWEEN '".$this->tanggalProduksiFirst."' AND '".$this->tanggalProduksi."'
                            GROUP BY
                                data_detail_produksi.sewing_line
                        ) cumulative
                    "),
                    "cumulative.sewing_line", "=", "data_detail_produksi.sewing_line"
                )
                ->where("data_detail_produksi_day.tgl_produksi", $this->tanggalProduksi)
                ->groupBy("data_produksi.id", "data_detail_produksi.sewing_line")
                ->orderBy("data_detail_produksi.sewing_line", "ASC")
                ->get();

            $allLine = UserPassword::select('username')->where('Groupp', 'SEWING')->whereRaw('(Locked != 1 OR Locked IS NULL)')->orderBy('username', 'ASC')->get();

            $kurs = MasterKursBi::select("kurs_tengah")->where("tanggal_kurs_bi", $this->tanggalProduksi)->first();

            $this->rowNumber = $dataDetailProduksiDay->count() + 4;
            $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
            $colCount = $allLine->count() + 17 - 1;
            if ($colCount > (count($alphabets)-1)) {
                $colStack = floor($colCount/(count($alphabets)-1));
                $colStackModulo = $colCount%(count($alphabets)-1);
                $this->colAlphabet = $alphabets[$colStack-1].$alphabets[$colStackModulo];
            } else {
                $this->colAlphabet = $alphabets[$colCount];
            }

            return view('sewing.report.excel.output-excel', [
                'dataDetailProduksiDay' => $dataDetailProduksiDay,
                'dataLineProduksiDay' => $dataLineProduksiDay,
                'allLine' => $allLine,
                'kurs' => $kurs,
                'tanggal' => $this->tanggalProduksi
            ]);
        }

        public function title(): string
        {
            return substr($this->tanggalProduksi,-2,2);
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

            // 1 Row
            // $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(30);

            //Range Columns
            $event->sheet->styleCells(
                '3',
                [
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ],
                    'font' => [
                        'size' => 12
                    ]
                ],
            );

            $event->sheet->styleCells(
                'A3:'.$event->getConcernable()->colAlphabet.$event->getConcernable()->rowNumber,
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
