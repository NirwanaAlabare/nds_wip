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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SewingReworkSpotcleaningReportController extends Controller
{
    private function getReworkSpotcleaningData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) return [];

        $bindings = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        $buyerFilter = !empty($buyer) ? "AND ms.supplier = ?" : "";
        if (!empty($buyer)) $bindings[] = $buyer;
        // Binding kedua untuk union undo
        $bindings = array_merge($bindings, $bindings);

        $sql = "
            SELECT
                buyer, ws, style, color, size,
                SUM(qty_rework_spotcleaning) AS qty_rework_spotcleaning
            FROM (
            SELECT
                ms.supplier AS buyer, ac.kpno AS ws, ac.styleno AS style, sd.color, sd.size, msn.urutan,
                COUNT(a.id) AS qty_rework_spotcleaning
            FROM signalbit_erp.output_defects a
            INNER JOIN signalbit_erp.output_defect_types b ON a.defect_type_id = b.id
            LEFT JOIN signalbit_erp.output_undo c ON c.output_defect_id = a.id
            LEFT JOIN signalbit_erp.output_reject_in reject_in ON reject_in.reject_id = c.output_reject_id AND reject_in.output_type = 'qc' AND reject_in.status = 'reworked' AND reject_in.updated_at <= a.updated_at
            INNER JOIN signalbit_erp.so_det sd ON a.so_det_id = sd.id
            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            LEFT JOIN signalbit_erp.master_size_new msn ON sd.size = msn.size
            WHERE b.allocation = 'spotcleaning'
              AND a.defect_status IN ('REWORKED', 'REJECTED')
              AND a.updated_at >= ?
              AND a.updated_at <= ?
              AND (CASE WHEN c.id IS NOT NULL AND c.updated_at >= '2026-10-01 00:00:00' THEN reject_in.id IS NULL ELSE 1=1 END)
              AND jd.cancel = 'N'
              $buyerFilter
            GROUP BY ms.supplier, ac.kpno, ac.styleno, sd.color, sd.size, msn.urutan

            UNION ALL

            /* Undo reject (reworked) yang berasal dari defect, mulai 2026-10-01 */
            SELECT
                ms.supplier AS buyer, ac.kpno AS ws, ac.styleno AS style, sd.color, sd.size, msn.urutan,
                COUNT(a.id) AS qty_rework_spotcleaning
            FROM signalbit_erp.output_undo a
            INNER JOIN signalbit_erp.output_reject_in reject_in ON reject_in.reject_id = a.output_reject_id AND reject_in.output_type = 'qc' AND reject_in.status = 'reworked'
            INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
            LEFT JOIN signalbit_erp.output_defects od ON od.id = a.output_defect_id
            LEFT JOIN signalbit_erp.output_defect_types dt ON dt.id = od.defect_type_id
            INNER JOIN signalbit_erp.so_det sd ON a.so_det_id = sd.id
            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            LEFT JOIN signalbit_erp.master_size_new msn ON sd.size = msn.size
            WHERE a.output_defect_id > 0
              AND dt.allocation = 'spotcleaning'
              AND a.updated_at >= '2026-10-01 00:00:00'
              AND a.updated_at >= ?
              AND a.updated_at <= ?
              AND mp.cancel = 'N'
              AND jd.cancel = 'N'
              $buyerFilter
            GROUP BY ms.supplier, ac.kpno, ac.styleno, sd.color, sd.size, msn.urutan
            ) rework
            GROUP BY buyer, ws, style, color, size, urutan
            ORDER BY buyer ASC, ws ASC, color ASC, urutan ASC
        ";

        return DB::connection('mysql_sb')->select($sql, $bindings);
    }

    public function getData(Request $request)
    {
        return response()->json(['data' => $this->getReworkSpotcleaningData($request->start_date, $request->end_date, $request->buyer)]);
    }

    public function exportExcel(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;
        $data = collect($this->getReworkSpotcleaningData($start, $end, $request->buyer));

        return Excel::download(new class($data, $start, $end) extends DefaultValueBinder implements FromCollection, WithHeadings, WithStyles, WithCustomValueBinder {
            protected $data, $start, $end;
            public function __construct($data, $start, $end) { $this->data = $data; $this->start = $start; $this->end = $end; }
            public function collection() { return $this->data; }
            public function bindValue(Cell $cell, $value) {
                // WS (B), Color (D), Size (E) selalu sebagai teks
                if ($value !== null && in_array($cell->getColumn(), ['B', 'D', 'E'], true) && $cell->getRow() > 5) {
                    $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                    return true;
                }
                return parent::bindValue($cell, $value);
            }
            public function headings(): array {
                $periode = "Periode: " . date('d M Y', strtotime($this->start)) . " s/d " . date('d M Y', strtotime($this->end));
                return [
                    ["NIRWANA ALABARE GARMENT"], ["REPORT REWORK SPOTCLEANING"], [$periode], [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Rework Spotcleaning"]
                ];
            }
            public function styles(Worksheet $sheet) {
                $sheet->mergeCells('A1:F1'); $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A5:F5')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                foreach (['B', 'D', 'E'] as $col) { $sheet->getStyle($col . '6:' . $col . $sheet->getHighestRow())->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); }
            }
        }, 'Report_Rework_Spotcleaning_'.date('Ymd_His').'.xlsx');
    }
}
