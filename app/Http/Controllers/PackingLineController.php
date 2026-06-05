<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\Packing\PackingOutputExport;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PackingLineController extends Controller
{
    public function trackPackingOutput(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == "supplier") {
                $suppliersQuery = DB::connection('mysql_sb')->table('mastersupplier')->selectRaw('Id_Supplier as id, Supplier as name')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('mastersupplier.tipe_sup', 'C')->where('status', '!=', 'CANCEL')->where('type_ws', 'STD')->where('cost_date', '>=', '2023-01-01');
                $suppliers = $suppliersQuery->orderBy('Supplier', 'ASC')->groupBy('Id_Supplier', 'Supplier')->get();

                return $suppliers;
            }

            if ($request->type == "order") {
                $orderSql = DB::connection('mysql_sb')->table('act_costing')->selectRaw('
                        id as id_ws,
                        kpno as no_ws
                    ')->where('status', '!=', 'CANCEL')->where('type_ws', 'STD')->where('cost_date', '>=', '2023-01-01');
                if ($request->supplier) {
                    $orderSql->where('id_buyer', $request->supplier);
                }
                $orders = $orderSql->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

                return $orders;
            }
        }

        return view('packing.track-packing-output', [
            "subPageGroup" => "packing-line",
            "subPage" => "track-packing-output",
            "page" => "dashboard-packing"
        ]);
    }

    public function exportPackingOutput(Request $request)
    {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $groupBy = $request->groupBy;
        $order = $request->order;
        $buyer = $request->buyer;

        return Excel::download(new PackingOutputExport($dateFrom, $dateTo, $groupBy, $order, $buyer), 'order_output.xlsx');
    }

    public function wip_packing_line()
    {
        return view('packing.packing_wip_packing_line', ['page' => 'dashboard-packing', "containerFluid" => true, "subPageGroup" => "packing-line", "subPage" => "wip_packing_line"]);
    }

    public function wip_packing_line_detail(Request $request)
    {
        $po       = $request->po;
        $line     = $request->line;
        $today    = date('Y-m-d');
        $todayEnd = $today . ' 23:59:59';

        $po_esc   = addslashes($po);
        $line_esc = addslashes($line);

        try {
            $cacheKey = 'wip_detail_' . md5($po_esc . '|' . $line_esc) . '_' . $today;

            $data = Cache::remember($cacheKey, 60, function () use ($today, $todayEnd, $po_esc, $line_esc) {
                return DB::connection('mysql_sb')->select("
            WITH m AS (
                SELECT
                    a.po_id,
                    p.po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd'
                  AND YEAR(p.tgl_shipment) >= 2026
                  AND p.po = '$po_esc'
                  AND a.created_by_line = '$line_esc'
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
            ),
            g AS (
                SELECT
                    a.id_ppic_master_so,
                    a.po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans = '$today'
                  AND YEAR(p.tgl_shipment) >= 2026
                  AND a.po = '$po_esc'
                  AND a.line = '$line_esc'
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det
            ),
            mut AS (
                SELECT
                    a.po, a.line, a.so_det_id,
                    a.selisih AS sa, 0 AS qty_packing_line, 0 AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026
                  AND a.po = '$po_esc' AND a.line = '$line_esc'
                UNION ALL
                SELECT po, line, so_det_id, 0, qty_packing_line, 0 FROM m
                UNION ALL
                SELECT po, line, so_det_id, 0, 0, qty_trf_gmt     FROM g
            )
            SELECT
                mut.so_det_id,
                ws,
                COALESCE(d.buyer,   '-') AS buyer,
                COALESCE(d.color,   '-') AS color,
                COALESCE(d.size,    '-') AS size,
                COALESCE(d.styleno, '-') AS styleno,
                SUM(sa)                                             AS qty_sa,
                SUM(qty_packing_line)                               AS qty_output,
                SUM(qty_trf_gmt)                                    AS qty_trf,
                SUM(sa) + SUM(qty_packing_line) - SUM(qty_trf_gmt) AS selisih
            FROM mut
            LEFT JOIN laravel_nds.master_sb_ws d ON mut.so_det_id = d.id_so_det
            LEFT JOIN laravel_nds.master_size_new msn on d.size = msn.size
            WHERE mut.line IS NOT NULL AND mut.line != ''
            GROUP BY mut.so_det_id
            ORDER BY ws asc, d.color asc, urutan asc
        ");
            });

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function wip_packing_line_data()
    {
        $today    = date('Y-m-d');
        $todayEnd = $today . ' 23:59:59';

        $raw = Cache::remember('wip_packing_line_' . $today, 60, function () use ($today, $todayEnd) {
            return DB::connection('mysql_sb')->select("
            WITH m AS (
                SELECT
                    a.po_id,
                    p.po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd'
                  AND YEAR(p.tgl_shipment) >= 2026
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
            ),
            g AS (
                SELECT
                    a.id_ppic_master_so,
                    a.po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans = '$today'
                  AND YEAR(p.tgl_shipment) >= 2026
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det
            ),
            mut AS (
                SELECT
                    a.id_ppic_master_so,
                    a.po,
                    a.line,
                    a.so_det_id,
                    a.selisih           AS sa,
                    0                   AS qty_packing_line,
                    0                   AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026
                UNION ALL
                SELECT
                    po_id               AS id_ppic_master_so,
                    po,
                    line,
                    so_det_id,
                    0                   AS sa,
                    qty_packing_line,
                    0                   AS qty_trf_gmt
                FROM m
                UNION ALL
                SELECT
                    id_ppic_master_so,
                    po,
                    line,
                    so_det_id,
                    0                   AS sa,
                    0                   AS qty_packing_line,
                    qty_trf_gmt
                FROM g
            )
            SELECT
                po,
                line,
                SUM(sa)                                             AS qty_sa,
                SUM(qty_packing_line)                               AS qty_output,
                SUM(qty_trf_gmt)                                    AS qty_trf,
                SUM(sa) + SUM(qty_packing_line) - SUM(qty_trf_gmt) AS selisih
            FROM mut
            WHERE line IS NOT NULL AND line != ''
            GROUP BY po, line
            ORDER BY line, po
        ");
        });

        $grouped   = [];
        $total_wip = 0;
        foreach ($raw as $row) {
            $grouped[$row->line][] = [
                'po'         => $row->po,
                'qty_sa'     => (int) $row->qty_sa,
                'qty_output' => (int) $row->qty_output,
                'qty_trf'    => (int) $row->qty_trf,
                'wip'        => (int) $row->selisih,
            ];
            $total_wip += (int) $row->selisih;
        }

        return response()->json([
            'lines'     => $grouped,
            'total_wip' => $total_wip,
        ]);
    }

    public function wip_packing_line_export()
    {
        $today    = date('Y-m-d');
        $todayEnd = $today . ' 23:59:59';

        $data = DB::connection('mysql_sb')->select("
            WITH m AS (
                SELECT
                    a.po_id,
                    p.po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd'
                  AND YEAR(p.tgl_shipment) >= 2026
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
            ),
            g AS (
                SELECT
                    a.id_ppic_master_so,
                    a.po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans = '$today'
                  AND YEAR(p.tgl_shipment) >= 2026
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det
            ),
            mut AS (
                SELECT
                    a.id_ppic_master_so,
                    a.po, a.line, a.so_det_id,
                    a.selisih AS sa, 0 AS qty_packing_line, 0 AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026
                UNION ALL
                SELECT po_id, po, line, so_det_id, 0, qty_packing_line, 0 FROM m
                UNION ALL
                SELECT id_ppic_master_so, po, line, so_det_id, 0, 0, qty_trf_gmt FROM g
            )
            SELECT
                mut.so_det_id,
                po,
                line,
                ws,
                COALESCE(d.buyer,   '-') AS buyer,
                COALESCE(d.color,   '-') AS color,
                COALESCE(d.size,    '-') AS size,
                COALESCE(d.styleno, '-') AS styleno,
                SUM(sa)                                             AS qty_sa,
                SUM(qty_packing_line)                               AS qty_output,
                SUM(qty_trf_gmt)                                    AS qty_trf,
                SUM(sa) + SUM(qty_packing_line) - SUM(qty_trf_gmt) AS selisih
            FROM mut
            LEFT JOIN laravel_nds.master_sb_ws d ON mut.so_det_id = d.id_so_det
            LEFT JOIN laravel_nds.master_size_new msn on d.size = msn.size
            WHERE mut.line IS NOT NULL AND mut.line != ''
            GROUP BY mut.so_det_id,po, line
            ORDER BY  line, po, ws,  d.color, urutan
        ");

        $filename = 'WIP_Packing_Line_' . $today;
        $excel    = FastExcel::create($filename);
        $sheet    = $excel->sheet();

        /* ── Title ── */
        $sheet->writeRow(['WIP PACKING LINE — ' . $today], ['font-style' => 'bold', 'font-size' => 13]);
        $sheet->writeRow(['']);

        /* ── Header ── */
        $sheet->writeRow(
            ['Line', 'PO', 'Buyer', 'WS', 'Style', 'Color', 'Size', 'SA', 'Output ▲', 'Transfer ▼', 'WIP'],
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
                'fill'       => '#3A0CA3',
                'color'      => '#FFFFFF',
            ]
        );

        /* ── Data rows (mulai baris 4: title, kosong, header) ── */
        $rowNum = 4;
        foreach ($data as $row) {
            $wip      = (float) $row->selisih;
            $wipColor = $wip < 0 ? '#CC2C2C' : ($wip > 0 ? '#1A7F4B' : '#888888');

            $sheet->writeRow(
                [
                    $row->line    ?? '-',
                    $row->po      ?? '-',
                    $row->buyer   ?? '-',
                    $row->ws      ?? '-',
                    $row->styleno ?? '-',
                    $row->color   ?? '-',
                    $row->size    ?? '-',
                    (float) $row->qty_sa,
                    (float) $row->qty_output,
                    (float) $row->qty_trf,
                    $wip,
                ],
                ['border' => 'thin']
            );

            $sheet->setCellStyle('K' . $rowNum, ['color' => $wipColor, 'font-style' => 'bold']);
            $rowNum++;
        }

        /* ── Column widths ── */
        foreach (
            [
                'A' => 16,
                'B' => 14,
                'C' => 22,
                'D' => 16,
                'E' => 20,
                'F' => 18,
                'G' => 10,
                'H' => 12,
                'I' => 12,
                'J' => 14,
                'K' => 12,
            ] as $col => $width
        ) {
            $sheet->setColWidth($col, $width);
        }

        return $excel->download();
    }
}
