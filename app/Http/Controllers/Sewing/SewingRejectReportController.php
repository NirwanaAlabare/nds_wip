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

class SewingRejectReportController extends Controller
{
    private function getRejectData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) return [];

        $bindings = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        $buyerFilter = !empty($buyer) ? "AND ms.supplier = ?" : "";
        if (!empty($buyer)) $bindings[] = $buyer;

        $sql = "
            SELECT
                ms.supplier AS buyer, ac.kpno AS ws, ac.styleno AS style, sd.color, sd.size,
                COUNT(a.id) AS qty_reject
            FROM signalbit_erp.output_rejects a
            INNER JOIN signalbit_erp.so_det sd ON a.so_det_id = sd.id
            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
            INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            LEFT JOIN signalbit_erp.master_size_new msn ON sd.size = msn.size
            WHERE a.updated_at >= ?
              AND a.updated_at <= ?
              AND jd.cancel = 'N'
              AND mp.cancel = 'N'
              $buyerFilter
            GROUP BY ms.supplier, ac.kpno, ac.styleno, sd.color, sd.size, msn.urutan
            ORDER BY buyer ASC, ws ASC, color ASC, msn.urutan ASC
        ";

        return DB::connection('mysql_sb')->select($sql, $bindings);
    }

    public function getData(Request $request)
    {
        return response()->json(['data' => $this->getRejectData($request->start_date, $request->end_date, $request->buyer)]);
    }

    public function exportExcel(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;
        $data = collect($this->getRejectData($start, $end, $request->buyer));

        return Excel::download(new class($data, $start, $end) implements FromCollection, WithHeadings, WithStyles {
            protected $data, $start, $end;
            public function __construct($data, $start, $end) { $this->data = $data; $this->start = $start; $this->end = $end; }
            public function collection() { return $this->data; }
            public function headings(): array {
                $periode = "Periode: " . date('d M Y', strtotime($this->start)) . " s/d " . date('d M Y', strtotime($this->end));
                return [
                    ["NIRWANA ALABARE GARMENT"], ["REPORT REJECT SEWING"], [$periode], [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Reject"]
                ];
            }
            public function styles(Worksheet $sheet) {
                $sheet->mergeCells('A1:F1'); $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A5:F5')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            }
        }, 'Report_Reject_Sewing_'.date('Ymd_His').'.xlsx');
    }
}
