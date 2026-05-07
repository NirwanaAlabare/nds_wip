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

class DefectSewingOutReportController extends Controller
{
    private function getDefectOutData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) return [];
        $filterBuyer = !empty($buyer) ? "AND buyer = '$buyer'" : "";

        $sql = "
            WITH
            saldo_sewing_defect AS (
                SELECT so_det_id, SUM(input_rework_sewing) AS input_rework_sewing
                FROM (
                    SELECT so_det_id, SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing
                    FROM signalbit_erp.output_defects a
                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                    WHERE allocation = 'SEWING' AND (a.updated_at >= '$startDate 00:00:00') AND (a.updated_at <= '$endDate 23:59:59') AND defect_status IN ('REWORKED','REJECTED')
                    GROUP BY so_det_id
                ) defect GROUP BY so_det_id
            ),
            saldo_finishing_defect AS (
                SELECT so_det_id, SUM(input_rework_sewing) AS input_rework_sewing_f
                FROM (
                    SELECT so_det_id, SUM(CASE WHEN allocation = 'SEWING' AND defect_status IN ('REWORKED','REJECTED') THEN 1 ELSE 0 END) AS input_rework_sewing
                    FROM signalbit_erp.output_defects_packing a
                    INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
                    WHERE allocation = 'SEWING' AND (a.updated_at >= '$startDate 00:00:00') AND (a.updated_at <= '$endDate 23:59:59') AND defect_status IN ('REWORKED','REJECTED')
                    GROUP BY so_det_id
                ) defect GROUP BY so_det_id
            ),
            mut AS (
                SELECT
                    m.so_det_id, mb.buyer, mb.ws, mb.styleno AS style, mb.color, mb.size,
                    (COALESCE(ss.input_rework_sewing, 0) + COALESCE(sf.input_rework_sewing_f, 0)) AS total_out
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
            SELECT buyer, ws, style, color, mut.size, SUM(total_out) AS jumlah
            FROM mut
            LEFT JOIN signalbit_erp.master_size_new msn ON mut.size = msn.size
            GROUP BY buyer, ws, style, color, mut.size, msn.urutan
            HAVING SUM(total_out) > 0
            ORDER BY buyer ASC, ws ASC, color ASC, msn.urutan ASC
        ";

        return DB::connection('mysql_sb')->select($sql);
    }

    public function getData(Request $request) {
        return response()->json(['data' => $this->getDefectOutData($request->start_date, $request->end_date, $request->buyer)]);
    }

    public function exportExcel(Request $request) {
        $start = $request->start_date; $end = $request->end_date;
        $data = collect($this->getDefectOutData($start, $end, $request->buyer));

        return Excel::download(new class($data, $start, $end) implements FromCollection, WithHeadings, WithStyles {
            protected $data, $start, $end;
            public function __construct($data, $start, $end) { $this->data = $data; $this->start = $start; $this->end = $end; }
            public function collection() { return $this->data; }
            public function headings(): array {
                return [ ["NIRWANA ALABARE GARMENT"], ["REPORT DEFECT SEWING (OUT)"], ["Periode: $this->start s/d $this->end"], [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Defect OUT"] ];
            }
            public function styles(Worksheet $sheet) {
                $sheet->mergeCells('A1:F1'); $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A5:F5')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            }
        }, 'Report_Defect_OUT_'.date('Ymd_His').'.xlsx');
    }
}
