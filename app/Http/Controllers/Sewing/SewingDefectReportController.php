<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SewingDefectReportController extends Controller
{
    public function index()
    {
        $list_buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view('sewing.report.report-defect-sewing', [
            'page'           => 'dashboard-sewing-eff',
            'subPageGroup'   => 'sewing-report',
            'subPage'        => 'reportDefect',
            'containerFluid' => true,
            'list_buyer'     => $list_buyer
        ]);
    }

    private function getDefectData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) {
            return [];
        }


        $bindings = [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ];

        $buyerFilter = "";
        if (!empty($buyer)) {
            $buyerFilter = "AND ms.supplier = ?";
            $bindings[] = $buyer;
        }


        $sql = "
            SELECT
                ms.supplier AS buyer,
                ac.kpno AS ws,
                ac.styleno AS style,
                sd.color,
                sd.size,
                COUNT(a.id) AS defect_sewing
            FROM signalbit_erp.output_defects a
            INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
            INNER JOIN signalbit_erp.so_det sd ON a.so_det_id = sd.id
            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            LEFT JOIN signalbit_erp.master_size_new msn ON sd.size = msn.size
            WHERE b.allocation = 'SEWING'
              AND a.created_at >= ?
              AND a.created_at <= ?
              AND jd.cancel = 'N'
              $buyerFilter
            GROUP BY ms.supplier, ac.kpno, ac.styleno, sd.color, sd.size, msn.urutan
            ORDER BY buyer ASC, ws ASC, color ASC, msn.urutan ASC
        ";

        return DB::connection('mysql_sb')->select($sql, $bindings);
    }

    public function getData(Request $request)
    {
        $data = $this->getDefectData($request->start_date, $request->end_date, $request->buyer);
        return response()->json(['data' => $data]);
    }

    public function exportExcel(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $buyer = $request->buyer;

        $data = collect($this->getDefectData($start_date, $end_date, $buyer));

        $fileName = 'Report_Defect_Sewing_' . date('Ymd_His') . '.xlsx';


        return Excel::download(new class($data, $start_date, $end_date) implements FromCollection, WithHeadings, WithStyles {
            protected $data;
            protected $startDate;
            protected $endDate;

            public function __construct($data, $startDate, $endDate) {
                $this->data = $data;
                $this->startDate = $startDate;
                $this->endDate = $endDate;
            }

            public function collection() {
                return $this->data;
            }

            public function headings(): array {

                $periode = "Periode: " . date('d M Y', strtotime($this->startDate)) . " s/d " . date('d M Y', strtotime($this->endDate));

                return [
                    ["NIRWANA ALABARE GARMENT"],
                    ["REPORT DEFECT SEWING"],
                    [$periode],
                    [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Defect Sewing"]
                ];
            }

            public function styles(Worksheet $sheet) {

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');


                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);


                $sheet->getStyle('A1')->getFont()->setSize(14);
                $sheet->getStyle('A2')->getFont()->setSize(12);
                $sheet->getStyle('A3')->getFont()->setSize(11);


                $sheet->getStyle('A5:F5')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }
        }, $fileName);
    }
}
