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

class SewingReworkReportController extends Controller
{
    public function index()
    {
        $list_buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view('sewing.report.report-rework-sewing', [
            'page'           => 'dashboard-sewing-eff',
            'subPageGroup'   => 'sewing-report',
            'subPage'        => 'reportRework',
            'containerFluid' => true,
            'list_buyer'     => $list_buyer
        ]);
    }

    private function getReworkData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) {
            return [];
        }

        $bindings = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        $buyerFilter = "";
        if (!empty($buyer)) {
            $buyerFilter = "AND ms.supplier = ?";
            $bindings[] = $buyer;
        }
        // Binding kedua untuk union undo
        $bindings = array_merge($bindings, $bindings);

        $sql = "
            SELECT
                buyer, ws, style, color, size,
                SUM(qty_rework_sewing) AS qty_rework_sewing
            FROM (
            SELECT
                ms.supplier AS buyer,
                ac.kpno AS ws,
                ac.styleno AS style,
                sd.color,
                sd.size,
                msn.urutan,
                COUNT(a.id) AS qty_rework_sewing
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
            WHERE b.allocation = 'SEWING'
              AND a.defect_status IN ('REWORKED', 'REJECTED')
              AND a.updated_at >= ?
              AND a.updated_at <= ?
              AND (CASE WHEN a.updated_at > '2026-10-31 23:59:59' THEN reject_in.id IS NULL ELSE 1=1 END)
              AND jd.cancel = 'N'
              $buyerFilter
            GROUP BY ms.supplier, ac.kpno, ac.styleno, sd.color, sd.size, msn.urutan

            UNION ALL

            /* Undo reject (reworked) yang berasal dari defect, setelah 2026-10-31 */
            SELECT
                ms.supplier AS buyer,
                ac.kpno AS ws,
                ac.styleno AS style,
                sd.color,
                sd.size,
                msn.urutan,
                COUNT(a.id) AS qty_rework_sewing
            FROM signalbit_erp.output_undo a
            INNER JOIN signalbit_erp.output_reject_in reject_in ON reject_in.reject_id = a.output_reject_id AND reject_in.output_type = 'qc' AND reject_in.status = 'reworked'
            INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
            INNER JOIN signalbit_erp.so_det sd ON a.so_det_id = sd.id
            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            LEFT JOIN signalbit_erp.master_size_new msn ON sd.size = msn.size
            WHERE a.output_defect_id > 0
              AND a.updated_at > '2026-10-31 23:59:59'
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
        $data = $this->getReworkData($request->start_date, $request->end_date, $request->buyer);
        return response()->json(['data' => $data]);
    }

    public function exportExcel(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $data = collect($this->getReworkData($start_date, $end_date, $request->buyer));

        $fileName = 'Report_Rework_Sewing_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new class($data, $start_date, $end_date) extends DefaultValueBinder implements FromCollection, WithHeadings, WithStyles, WithCustomValueBinder {
            protected $data, $start, $end;
            public function __construct($data, $start, $end) {
                $this->data = $data; $this->start = $start; $this->end = $end;
            }
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
                    ["NIRWANA ALABARE GARMENT"],
                    ["REPORT REWORK SEWING"],
                    [$periode],
                    [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Rework Sewing"]
                ];
            }
            public function styles(Worksheet $sheet) {
                $sheet->mergeCells('A1:F1'); $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A1')->getFont()->setSize(14);
                $sheet->getStyle('A5:F5')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                foreach (['B', 'D', 'E'] as $col) { $sheet->getStyle($col . '6:' . $col . $sheet->getHighestRow())->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT); }
            }
        }, $fileName);
    }
}
