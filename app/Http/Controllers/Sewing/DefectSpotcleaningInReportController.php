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

class DefectSpotcleaningInReportController extends Controller
{
    public function index()
    {
        $list_buyer = DB::connection('mysql_sb')->table('mastersupplier')->select('supplier')->orderBy('supplier', 'ASC')->get();

        $list_tipe = [
            'defect_in'  => 'Defect Spotcleaning IN',
            'defect_out' => 'Defect Spotcleaning OUT'
        ];

        return view('sewing.report.report-defect-spotcleaning-inout-sentral', [
            'page'           => 'dashboard-sewing-eff',
            'subPageGroup'   => 'sewing-report',
            'subPage'        => 'reportDefectSpotcleaningInOut',
            'list_buyer'     => $list_buyer,
            'list_tipe'      => $list_tipe,
            'containerFluid' => true
        ]);
    }

    private function getDefectInData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) return [];
        $filterBuyer = !empty($buyer) ? "AND buyer = '$buyer'" : "";

        // Kueri diekstrak dari Mutasi WIP (Fokus Defect Spotcleaning IN)
        $sql = "
            WITH
            saldo_sewing_defect AS (
                SELECT so_det_id, SUM(defect_spotcleaning) AS defect_spotcleaning
                FROM (
                    SELECT so_det_id, SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning
                    FROM signalbit_erp.output_defects a
                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                    WHERE allocation = 'spotcleaning' AND (a.created_at >= '$startDate 00:00:00') AND (a.created_at <= '$endDate 23:59:59')
                    GROUP BY so_det_id
                ) defect GROUP BY so_det_id
            ),
            saldo_finishing_defect AS (
                SELECT so_det_id, SUM(defect_spotcleaning) AS defect_spotcleaning_f
                FROM (
                    SELECT so_det_id, SUM(CASE WHEN allocation = 'spotcleaning' THEN 1 ELSE 0 END) AS defect_spotcleaning
                    FROM signalbit_erp.output_defects_packing a
                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                    WHERE allocation = 'spotcleaning' AND (a.created_at >= '$startDate 00:00:00') AND (a.created_at <= '$endDate 23:59:59')
                    GROUP BY so_det_id
                ) defect GROUP BY so_det_id
            ),
            mut AS (
                SELECT
                    m.so_det_id, mb.buyer, mb.ws, mb.styleno AS style, mb.color, mb.size,
                    (COALESCE(ss.defect_spotcleaning, 0) + COALESCE(sf.defect_spotcleaning_f, 0)) AS total_in
                FROM (
                    SELECT so_det_id FROM saldo_sewing_defect UNION SELECT so_det_id FROM saldo_finishing_defect
                ) m
                LEFT JOIN saldo_sewing_defect ss ON m.so_det_id = ss.so_det_id
                LEFT JOIN saldo_finishing_defect sf ON m.so_det_id = sf.so_det_id
                LEFT JOIN (
                    SELECT sd.id, ac.kpno AS ws, ms.supplier AS buyer, ac.styleno, sd.color, sd.size
                    FROM signalbit_erp.so_det sd
                    INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                    INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                    INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                ) mb ON m.so_det_id = mb.id
                WHERE 1=1 $filterBuyer
            )
            SELECT buyer, ws, style, color, mut.size, SUM(total_in) AS jumlah
            FROM mut
            LEFT JOIN signalbit_erp.master_size_new msn ON mut.size = msn.size
            GROUP BY buyer, ws, style, color, mut.size, msn.urutan
            HAVING SUM(total_in) > 0
            ORDER BY buyer ASC, ws ASC, color ASC, msn.urutan ASC
        ";

        return DB::connection('mysql_sb')->select($sql);
    }

    public function getData(Request $request) {
        return response()->json(['data' => $this->getDefectInData($request->start_date, $request->end_date, $request->buyer)]);
    }

    public function exportExcel(Request $request) {
        $start = $request->start_date; $end = $request->end_date;
        $data = collect($this->getDefectInData($start, $end, $request->buyer));

        return Excel::download(new class($data, $start, $end) implements FromCollection, WithHeadings, WithStyles {
            protected $data, $start, $end;
            public function __construct($data, $start, $end) { $this->data = $data; $this->start = $start; $this->end = $end; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return [ ["NIRWANA ALABARE GARMENT"], ["REPORT DEFECT SPOTCLEANING (IN)"], ["Periode: $this->start s/d $this->end"], [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Defect Spotcleaning IN"] ];
            }
            public function styles(Worksheet $sheet) {
                $sheet->mergeCells('A1:F1'); $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A5:F5')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            }
        }, 'Report_Defect_Spotcleaning_IN_'.date('Ymd_His').'.xlsx');
    }
}
