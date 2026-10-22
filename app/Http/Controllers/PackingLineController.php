<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\Packing\PackingOutputExport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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
        $color = $request->color;
        $line = $request->line;
        $po = $request->po;
        $size = $request->size;

        // Langsung query & tulis dengan FastExcel (tanpa render Blade, nilai tidak di-trim seperti FromView)
        $data = $this->packingOutputData($dateFrom, $dateTo, $groupBy, $order, $buyer, $color, $line, $po, $size);

        return $this->writePackingOutputExcel($data, 'order_output.xlsx');
    }

    // Subquery output rfts packing per tanggal/line/po/type (dipakai query group & output)
    private function packingOutputRftsSubquery($lineColumn, $dateFrom, $dateTo, $groupBy, $order)
    {
        $sql = "
            (
                SELECT
                    coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                    max( rfts.updated_at ) last_rft,
                    count( rfts.id ) rft,
                    master_plan.id master_plan_id,
                    act_costing.id id_ws,
                    so_det.color,
                    ".$lineColumn.",
                    rfts.po_id,
                    rfts.alokasi,
                    rfts.type
                    ".($groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                FROM
                    output_rfts_packing_po rfts
                    INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                    LEFT JOIN userpassword ON userpassword.username = rfts.created_by_line
                    LEFT JOIN so_det ON so_det.id = rfts.so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                WHERE
                    rfts.updated_at BETWEEN ? AND ?
                    AND master_plan.tgl_plan BETWEEN '2024-01-01' AND ?
                    ".($order ? " AND master_plan.id_ws = ?" : "")."
                GROUP BY
                    act_costing.id, 
                    so_det.color,
                    DATE ( rfts.updated_at ),
                    COALESCE ( userpassword.username, master_plan.sewing_line ),
                    rfts.po_id,
                    rfts.type
                    ".($groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                HAVING
                    count( rfts.id ) > 0
            ) as rfts
        ";

        $bindings = [$dateFrom.' 00:00:00', $dateTo.' 23:59:59', $dateTo];

        if ($order) {
            $bindings[] = $order;
        }

        return DB::connection('mysql_sb')->query()->fromRaw($sql, $bindings);
    }

    // Data export packing output (sama dengan PackingOutputExport::view), query memakai binding
    private function packingOutputData($dateFrom, $dateTo, $groupBy, $order, $buyer, $color, $line, $po, $size)
    {
        $useSize = $groupBy == 'size';
        $poExpression = "(CASE WHEN rfts.alokasi = 'po' THEN COALESCE(ppic_master_so.po, 'PO tidak ditemukan') ELSE UPPER(rfts.alokasi) END)";

        $supplier = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            where('mastersupplier.tipe_sup', 'C')->
            where('status', '!=', 'CANCEL')->
            where('type_ws', 'STD')->
            where('cost_date', '>=', '2023-01-01')->
            where('Id_Supplier', $buyer)->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            first();

        // Query for Grouping
        $groupSql = $this->packingOutputRftsSubquery("userpassword.username sewing_line", $dateFrom, $dateTo, $groupBy, $order)->
            selectRaw("
                rfts.tanggal tanggal,
                act_costing.kpno ws,
                act_costing.styleno style,
                rfts.color,
                COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line,
                ".$poExpression." as po,
                COALESCE(rfts.type, 'rft') as type
                ".($useSize ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
            ")->
            leftJoin("master_plan", "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "rfts.id_ws")->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
        if ($useSize) $groupSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
        if ($dateFrom) $groupSql->where('rfts.tanggal', '>=', $dateFrom);
        if ($dateTo) $groupSql->where('rfts.tanggal', '<=', $dateTo);
        if ($color) $groupSql->where('rfts.color', $color);
        if ($line) $groupSql->where('rfts.sewing_line', $line);
        if ($useSize && $size) $groupSql->where('so_det.size', $size);
        if ($po) $groupSql->where(DB::raw($poExpression), $po);
        if ($order) $groupSql->where("act_costing.id", $order);
        $orderGroup = $groupSql->
            groupByRaw("rfts.id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.sewing_line, master_plan.sewing_line), ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($useSize ? ", so_det.size" : ""))->
            orderBy("rfts.id_ws", "asc")->
            orderBy("act_costing.styleno", "asc")->
            orderBy("rfts.color", "asc")->
            orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc, ppic_master_so.po asc ".($useSize ? ', so_det.id asc' : ''))->
            get();

        // Query for Output
        $outputSql = $this->packingOutputRftsSubquery("COALESCE ( userpassword.username, master_plan.sewing_line ) created_by", $dateFrom, $dateTo, $groupBy, $order)->
            selectRaw("
                rfts.tanggal,
                COALESCE(rfts.type, 'rft') as type,
                ".($useSize ? ' rfts.so_det_id, so_det.size, ' : '')."
                SUM( rfts.rft ) output,
                act_costing.kpno ws,
                act_costing.styleno style,
                rfts.color,
                COALESCE ( rfts.created_by, master_plan.sewing_line ) AS sewing_line,
                ".$poExpression." as po
            ")->
            leftJoin("master_plan", "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "rfts.id_ws")->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
        if ($useSize) $outputSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
        if ($order) $outputSql->where("act_costing.id", $order);
        if ($dateFrom) $outputSql->where('rfts.tanggal', '>=', $dateFrom);
        if ($dateTo) $outputSql->where('rfts.tanggal', '<=', $dateTo);
        if ($color) $outputSql->where('rfts.color', $color);
        if ($line) $outputSql->whereRaw('COALESCE(rfts.created_by, master_plan.sewing_line) = ?', [$line]);
        if ($po) $outputSql->where(DB::raw($poExpression), $po);
        if ($useSize && $size) $outputSql->where('so_det.size', $size);
        $orderOutputs = $outputSql->
            groupByRaw("rfts.id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.created_by, master_plan.sewing_line) , master_plan.tgl_plan, rfts.tanggal, ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($useSize ? ', so_det.size' : ''))->
            orderBy("rfts.id_ws", "asc")->
            orderBy("act_costing.styleno", "asc")->
            orderBy("rfts.color", "asc")->
            orderByRaw("COALESCE(rfts.created_by, master_plan.sewing_line) asc, ppic_master_so.po asc ".($useSize ? ', so_det.id asc' : ''))->
            get();

        $dates = $orderOutputs
            ->pluck('tanggal')
            ->filter(function ($tanggal) { return $tanggal !== null && $tanggal !== '' && $tanggal !== '0'; })
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Pivot output per baris & tanggal
        $outputMap  = [];
        $rowTotals  = [];
        $dateTotals = [];
        $grandTotal = 0;

        foreach ($orderOutputs as $row) {
            $key = implode('|', [
                $row->ws,
                $row->style,
                $row->color,
                $row->sewing_line,
                $row->po,
                $row->type ?: 'rft',
                $useSize ? $row->size : '_',
            ]);

            $date = $row->tanggal;
            $qty  = (int) $row->output;

            $outputMap[$key][$date] = ($outputMap[$key][$date] ?? 0) + $qty;
            $rowTotals[$key] = ($rowTotals[$key] ?? 0) + $qty;
            $dateTotals[$date] = ($dateTotals[$date] ?? 0) + $qty;
            $grandTotal += $qty;
        }

        return [
            'order'      => $order,
            'buyer'      => $buyer,
            'buyerName'  => $supplier ? $supplier->name : null,
            'groupBy'    => $groupBy,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'orderGroup' => $orderGroup,
            'dates'      => $dates,
            'outputMap'  => $outputMap,
            'rowTotals'  => $rowTotals,
            'dateTotals' => $dateTotals,
            'grandTotal' => $grandTotal,
        ];
    }

    // Tulis export packing output (layout sama dengan packing-output-export.blade.php)
    private function writePackingOutputExcel(array $data, $fileName)
    {
        $useSize = $data['groupBy'] === 'size';
        $dates = $data['dates'];
        $fixedColumns = $useSize ? 7 : 6;
        $colCount = $fixedColumns + count($dates) + 1;
        $lastColumn = Coordinate::stringFromColumnIndex($colCount);

        $excel = FastExcel::create('Packing Output');
        $sheet = $excel->sheet();

        // Judul
        $sheet->writeRow(
            ['Tanggal Export : '.($data['dateFrom'] || $data['dateTo'] ? $data['dateFrom'].' - '.$data['dateTo'] : 'All Day')],
            ['text-align' => 'center']
        );
        $sheet->mergeCells('A1:'.$lastColumn.'1');

        $title = trim(($data['buyer'] ? "'".$data['buyerName']."' " : '').($data['order'] ? "'".$data['order']."' " : '').'Packing Output');
        $sheet->writeRow([$title], ['text-align' => 'center', 'font-style' => 'bold']);
        $sheet->mergeCells('A2:'.$lastColumn.'2');

        $border = ['border' => 'thin'];
        $bold = ['border' => 'thin', 'font-style' => 'bold'];

        if (!$data['orderGroup'] || $data['orderGroup']->isEmpty()) {
            $sheet->writeRow(['Data tidak ditemukan.'], ['text-align' => 'center'] + $border);
            $sheet->mergeCells('A3:'.$lastColumn.'3');

            return $excel->download($fileName);
        }

        // Header
        $header = ['No. WS', 'Style', 'Color', 'Line', 'PO', 'Quality'];
        if ($useSize) $header[] = 'Size';
        foreach ($dates as $date) {
            $header[] = Carbon::parse($date)->format('d-m-Y');
        }
        $header[] = 'TOTAL';

        $sheet->writeRow($header, $bold);

        // Baris data (nilai teks ditulis apa adanya)
        foreach ($data['orderGroup'] as $row) {
            $key = implode('|', [
                $row->ws,
                $row->style,
                $row->color,
                $row->sewing_line,
                $row->po,
                $row->type,
                $useSize ? $row->size : '_',
            ]);

            $values = [
                (string) $row->ws,
                (string) $row->style,
                (string) $row->color,
                strtoupper(str_replace('_', ' ', (string) $row->sewing_line)),
                (string) $row->po,
                strtoupper((string) $row->type),
            ];
            if ($useSize) $values[] = (string) $row->size;
            foreach ($dates as $date) {
                $values[] = (int) ($data['outputMap'][$key][$date] ?? 0);
            }
            $values[] = (int) ($data['rowTotals'][$key] ?? 0);

            // Kolom TOTAL tebal
            $sheet->writeRow($values, ['vertical-align' => 'top'] + $border, [$colCount - 1 => ['font-style' => 'bold']]);
        }

        // Baris TOTAL
        $totalRow = array_merge(['TOTAL'], array_fill(0, $fixedColumns - 1, ''));
        foreach ($dates as $date) {
            $totalRow[] = (int) ($data['dateTotals'][$date] ?? 0);
        }
        $totalRow[] = (int) $data['grandTotal'];

        $sheet->writeRow($totalRow, $bold);
        $totalRowNumber = 3 + 1 + $data['orderGroup']->count();
        $sheet->mergeCells('A'.$totalRowNumber.':'.Coordinate::stringFromColumnIndex($fixedColumns).$totalRowNumber);

        for ($i = 1; $i <= $colCount; $i++) {
            $sheet->setColWidth(Coordinate::stringFromColumnIndex($i), $i <= $fixedColumns ? 20 : 12);
        }

        return $excel->download($fileName);
    }

    public function wip_packing_line()
    {
        return view('packing.packing_wip_packing_line', ['page' => 'dashboard-packing', "containerFluid" => true, "subPageGroup" => "packing-line", "subPage" => "wip_packing_line"]);
    }

    public function wip_packing_line_detail(Request $request)
    {
        $po          = preg_replace('/\s+/', ' ', trim((string) $request->po));
        $line        = $request->line;
        $today       = date('Y-m-d');
        $todayEnd    = $today . ' 23:59:59';
        $filterMonth = 6; // ganti angka ini untuk filter bulan lain

        $po_esc   = addslashes($po);
        $line_esc = addslashes($line);

        try {
            $cacheKey = 'wip_detail_' . md5($po_esc . '|' . $line_esc) . '_' . $today;

            $data = Cache::remember($cacheKey, 60, function () use ($today, $todayEnd, $po_esc, $line_esc, $filterMonth) {
                return DB::connection('mysql_sb')->select("
            WITH m AS (
                SELECT
                    a.po_id,
                    TRIM(REGEXP_REPLACE(p.po, '[[:space:]]+', ' ')) AS po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd'
                  AND YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                  AND TRIM(REGEXP_REPLACE(p.po, '[[:space:]]+', ' ')) = '$po_esc'
                  AND a.created_by_line = '$line_esc'
                GROUP BY a.po_id, a.created_by_line, a.so_det_id

                UNION ALL

                SELECT
                    a.po_id,
                    'TEMPORARY PACKING' AS po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd' AND a.alokasi = 'temporary packing'
                  AND a.created_by_line = '$line_esc' AND '$po_esc' = 'TEMPORARY PACKING'
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
            ),
            g AS (
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans = '$today'
                  AND YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                  AND TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) = '$po_esc'
                  AND a.line = '$line_esc'
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det

                UNION ALL
                
                SELECT
                    a.id_ppic_master_so,
                    UPPER(TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' '))) AS po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                WHERE a.tgl_trans = '$today'
                    AND TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) = '$po_esc'
                    AND a.line = '$line_esc'
                    AND a.tujuan = 'Temporary Packing'
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det
            ),
            mut AS (
                SELECT
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po, a.line, a.so_det_id,
                    a.selisih AS sa, 0 AS qty_packing_line, 0 AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                  AND TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) = '$po_esc' AND a.line = '$line_esc' AND a.po != 'TEMPORARY PACKING'
                UNION ALL
                SELECT
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    a.line,
                    a.so_det_id,
                    a.selisih           AS sa,
                    0                   AS qty_packing_line,
                    0                   AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                WHERE a.line = '$line_esc' AND a.po = 'TEMPORARY PACKING' AND '$po_esc' = 'TEMPORARY PACKING'
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
        $today       = date('Y-m-d');
        $todayEnd    = $today . ' 23:59:59';
        $filterMonth = 6; // ganti angka ini untuk filter bulan lain

        $raw = Cache::remember('wip_packing_line_' . $today, 60, function () use ($today, $todayEnd, $filterMonth) {
            return DB::connection('mysql_sb')->select("
            WITH m AS (
                SELECT
                    a.po_id,
                    TRIM(REGEXP_REPLACE(p.po, '[[:space:]]+', ' ')) AS po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd'
                  AND YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                GROUP BY a.po_id, a.created_by_line, a.so_det_id

                UNION ALL

                SELECT
                    a.po_id,
                    'TEMPORARY PACKING' AS po,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd' AND a.alokasi = 'temporary packing'
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
            ),
            g AS (
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans = '$today'
                  AND YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det

                UNION ALL
                
                SELECT
                    a.id_ppic_master_so,
                    UPPER(TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' '))) AS po,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                WHERE a.tgl_trans = '$today' AND a.tujuan = 'Temporary Packing'
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det
            ),
            mut AS (
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    a.line,
                    a.so_det_id,
                    a.selisih           AS sa,
                    0                   AS qty_packing_line,
                    0                   AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth AND a.po != 'TEMPORARY PACKING'
                UNION ALL
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    a.line,
                    a.so_det_id,
                    a.selisih           AS sa,
                    0                   AS qty_packing_line,
                    0                   AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                WHERE a.po = 'TEMPORARY PACKING'
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
            HAVING SUM(sa) != 0
            OR SUM(qty_packing_line) != 0
            OR SUM(qty_trf_gmt) != 0
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
        $today       = date('Y-m-d');
        $todayEnd    = $today . ' 23:59:59';
        $filterMonth = 6; // ganti angka ini untuk export bulan lain

        $data = DB::connection('mysql_sb')->select("
            WITH m AS (
                SELECT
                    a.po_id,
                    TRIM(REGEXP_REPLACE(p.po, '[[:space:]]+', ' ')) AS po,
                    tgl_shipment,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd'
                  AND YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
                
                UNION ALL

                SELECT
                    a.po_id,
                    'TEMPORARY PACKING' AS po,
                    DATE(a.created_at)  AS tgl_shipment,
                    a.created_by_line   AS line,
                    a.so_det_id         AS so_det_id,
                    COUNT(*)            AS qty_packing_line
                FROM output_rfts_packing_po a
                WHERE a.updated_at BETWEEN '$today' AND '$todayEnd' AND a.alokasi = 'temporary packing'
                GROUP BY a.po_id, a.created_by_line, a.so_det_id
            ),
            g AS (
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    tgl_shipment,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans = '$today'
                  AND YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det

                UNION ALL
                
                SELECT
                    a.id_ppic_master_so,
                    UPPER(TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' '))) AS po,
                    a.tgl_trans AS tgl_shipment,
                    a.line,
                    a.id_so_det         AS so_det_id,
                    SUM(a.qty)          AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                WHERE a.tgl_trans = '$today' AND a.tujuan = 'Temporary Packing'
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det
            ),
            mut AS (
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po, a.line, a.so_det_id, tgl_shipment,
                    a.selisih AS sa, 0 AS qty_packing_line, 0 AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026 AND MONTH(p.tgl_shipment) >= $filterMonth AND a.po != 'TEMPORARY PACKING'
                UNION ALL
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(a.po, '[[:space:]]+', ' ')) AS po,
                    a.line,
                    a.so_det_id,
                    (
                        SELECT DATE(o.created_at)
                        FROM output_rfts_packing_po o
                        WHERE o.created_by_line = a.line
                            AND o.so_det_id = a.so_det_id
                            AND UPPER(o.alokasi) = a.po
                        ORDER BY o.created_at ASC
                        LIMIT 1
                    ) AS tgl_shipment,
                    a.selisih           AS sa,
                    0                   AS qty_packing_line,
                    0                   AS qty_trf_gmt
                FROM mut_packing_line_to_trf_gmt a
                WHERE a.po = 'TEMPORARY PACKING'
                UNION ALL
                SELECT po_id, po, line, so_det_id, tgl_shipment, 0, qty_packing_line, 0 FROM m
                UNION ALL
                SELECT id_ppic_master_so, po, line, so_det_id, tgl_shipment, 0, 0, qty_trf_gmt FROM g
            )
            SELECT
                mut.so_det_id,
                po,
                tgl_shipment,
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
            GROUP BY mut.so_det_id,po, line, tgl_shipment
            HAVING SUM(sa) != 0
            OR SUM(qty_packing_line) != 0
            OR SUM(qty_trf_gmt) != 0
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
            ['Line', 'PO', 'Tgl Shipment', 'Buyer', 'WS', 'Style', 'Color', 'Size', 'SA', 'Output ▲', 'Transfer ▼', 'WIP'],
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
                'fill'       => '#3A0CA3',
                'color'      => '#FFFFFF',
            ]
        );

        $sheet->setColFormat('C', '@date');

        /* ── Data rows (mulai baris 4: title, kosong, header) ── */
        $rowNum = 4;
        foreach ($data as $row) {
            $wip      = (float) $row->selisih;
            $wipColor = $wip < 0 ? '#CC2C2C' : ($wip > 0 ? '#1A7F4B' : '#888888');

            $sheet->writeRow(
                [
                    $row->line    ?? '-',
                    $row->po      ?? '-',
                    $row->tgl_shipment ?? '-',
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

            $sheet->setCellStyle('L' . $rowNum, ['color' => $wipColor, 'font-style' => 'bold']);
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
                'L' => 12,
            ] as $col => $width
        ) {
            $sheet->setColWidth($col, $width);
        }

        return $excel->download();
    }
}
