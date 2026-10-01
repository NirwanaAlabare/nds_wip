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

class FinishingTerimaRejectReportController extends Controller
{
    private function getRejectData($startDate, $endDate, $buyer)
    {
        if (!$startDate || !$endDate) return [];

        $bindings = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];
        $buyerFilter = !empty($buyer) ? "AND ms.supplier = ?" : "";
        if (!empty($buyer)) $bindings[] = $buyer;

        $sql = "
            select
                buyer,
                ws,
                style,
                color,
                size,
                SUM(qty_reject) qty_reject
            FROM (
                SELECT
                    b.so_det_id,
                    mb.buyer,
                    mb.ws,
                    mb.styleno style,
                    mb.color,
                    mb.size,
                    COUNT(*) AS qty_reject
                FROM signalbit_erp.output_reject_out_detail a
                INNER JOIN signalbit_erp.output_reject_in b ON b.id = a.reject_in_id
                INNER JOIN signalbit_erp.master_plan mp ON mp.id = b.master_plan_id
                LEFT JOIN (
                    SELECT
                    sd.id as id_so_det,
                    ac.kpno as ws,
                    supplier as buyer,
                    styleno,
                    color,
                    size,
                    dest
                    FROM signalbit_erp.so_det sd
                    INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                    INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                    INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                    INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                    WHERE jd.cancel = 'N'
                ) mb on b.so_det_id = mb.id_so_det
                left join output_undo_secondary_out undo_secondary ON undo_secondary.output_reject_id = b.reject_id and b.status = 'finishing_proses'
                left join output_secondary_master osm on osm.id = undo_secondary.secondary_id
                -- Invalid out
                left join output_rejects on output_rejects.kode_numbering = b.kode_numbering and output_rejects.created_at < b.created_at and b.output_type = 'qc'
                left join output_rejects_packing on output_rejects_packing.kode_numbering = b.kode_numbering and output_rejects_packing.created_at < b.created_at and b.output_type = 'packing'
                left join output_rejects_packing_po on output_rejects.kode_numbering = b.kode_numbering and output_rejects_packing_po.created_at < b.created_at and b.output_type = 'qc_fns_pck_retur'
                left join output_secondary_out_reject on output_secondary_out_reject.kode_numbering = b.kode_numbering and output_secondary_out_reject.created_at < b.created_at and b.output_type = 'finishing_proses'
                WHERE
                    (CASE WHEN COALESCE(output_rejects.created_at, output_rejects_packing.created_at, output_rejects_packing_po.created_at, output_secondary_out_reject.created_at) IS NOT NULL AND b.status = 'reworked' THEN COALESCE(output_rejects.created_at, output_rejects_packing.created_at, output_rejects_packing_po.created_at, output_secondary_out_reject.created_at) > b.created_at ELSE 1=1 END)
                    AND a.created_at >= '2026-09-01 00:00:00'
                    AND a.created_at >= ?
                    AND a.created_at <= ?
                    AND mp.cancel = 'N'
                    AND b.status = 'reworked'
                    AND b.output_type = 'packing'
                GROUP BY
                    so_det_id
            ) reject
            group by
                buyer,
                ws,
                style,
                color,
                size
            order by
                buyer,
                ws,
                style,
                color
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
                    ["NIRWANA ALABARE GARMENT"], ["REPORT TERIMA REJECT QC FINISHING"], [$periode], [""],
                    ["Buyer", "WS", "Style", "Color", "Size", "Jumlah Reject"]
                ];
            }
            public function styles(Worksheet $sheet) {
                $sheet->mergeCells('A1:F1'); $sheet->mergeCells('A2:F2'); $sheet->mergeCells('A3:F3');
                $sheet->getStyle('A1:A3')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A5:F5')->applyFromArray(['font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
            }
        }, 'Report_Terima_Reject_QC_Finishing_'.date('Ymd_His').'.xlsx');
    }
}
