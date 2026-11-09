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

    private const WIP_QTY_COLUMNS = [
        'saldo_awal', 'terima_good', 'terima_reject', 'keluar_central', 'keluar_temporary',
        'switching_out', 'switching_in', 'adjustment', 'saldo_akhir',
    ];

    private const WIP_TANPA_LINE = 'TANPA LINE';
    private const WIP_TANPA_PO = 'TANPA PO';

    // WS ini tidak ditampilkan selama statusnya Close Order
    private const WIP_WS_DISEMBUNYIKAN = ['AMZ/1025/017', 'AMZ/1025/018'];

    // Saldo awal dari saldo_wip_packing_line (posisi akhir hari tgl ini), transaksi dihitung mulai besoknya.
    // Transaksi sebelum hari ini masuk ke saldo awal, kolom terima/keluar/switching/adjustment hanya hari ini.
    public const WIP_TGL_SALDO = '2026-11-01';

    // sementara untuk pengecekan: hanya saldo snapshot, transaksi/switching/adjustment setelahnya belum ditambahkan
    private const WIP_HANYA_SALDO = false;

    private function wipPackingLineRows($today, $withTempDate = false)
    {
        $tglSaldo  = self::WIP_TGL_SALDO;
        $mulai     = date('Y-m-d', strtotime($tglSaldo . ' +1 day'));
        $todayEnd  = $today . ' 23:59:59';
        $tanpaLine = self::WIP_TANPA_LINE;
        $tanpaPo   = self::WIP_TANPA_PO;

        // subquery ini berat, hanya dipakai saat export
        $tglShipmentTemp = $withTempDate ? "(
                        SELECT DATE(MIN(o.created_at))
                        FROM output_rfts_packing_po o
                        WHERE o.created_by_line = x.line
                            AND o.so_det_id = x.so_det_id
                            AND o.alokasi = 'temporary packing'
                    )" : "NULL";

        $sumber = self::WIP_HANYA_SALDO
            ? "SELECT * FROM s"
            : "SELECT * FROM s UNION ALL SELECT * FROM m UNION ALL SELECT * FROM g";

        return DB::connection('mysql_sb')->select("
            WITH s AS (
                SELECT
                    CASE WHEN po = 'TEMPORARY PACKING' THEN NULL ELSE id_ppic_master_so END AS id_ppic,
                    TRIM(REGEXP_REPLACE(po, '[[:space:]]+', ' ')) AS po,
                    tgl_shipment,
                    line,
                    so_det_id,
                    ws, buyer, styleno, color, size,
                    qty AS saldo_awal, 0 AS terima_good, 0 AS terima_reject, 0 AS keluar_central, 0 AS keluar_temporary
                FROM laravel_nds.saldo_wip_packing_line
                WHERE tgl_saldo = '$tglSaldo'
            ),
            m (id_ppic, po, tgl_shipment, line, so_det_id, ws, buyer, styleno, color, size, saldo_awal, terima_good, terima_reject, keluar_central, keluar_temporary) AS (
                SELECT
                    a.po_id,
                    TRIM(REGEXP_REPLACE(p.po, '[[:space:]]+', ' ')),
                    p.tgl_shipment,
                    a.created_by_line,
                    a.so_det_id,
                    NULL, NULL, NULL, NULL, NULL,
                    SUM(a.type IN ('RFT', 'REJECT') AND a.updated_at < '$today'),
                    SUM(a.type = 'RFT' AND a.updated_at >= '$today'),
                    SUM(a.type = 'REJECT' AND a.updated_at >= '$today'),
                    0, 0
                FROM output_rfts_packing_po a
                INNER JOIN laravel_nds.ppic_master_so p ON a.po_id = p.id
                WHERE a.updated_at BETWEEN '$mulai' AND '$todayEnd'
                  AND COALESCE(a.alokasi, '') != 'temporary packing'
                GROUP BY a.po_id, a.created_by_line, a.so_det_id

                UNION ALL

                SELECT
                    NULL, 'TEMPORARY PACKING', NULL,
                    a.created_by_line,
                    a.so_det_id,
                    NULL, NULL, NULL, NULL, NULL,
                    SUM(a.type IN ('RFT', 'REJECT') AND a.updated_at < '$today'),
                    SUM(a.type = 'RFT' AND a.updated_at >= '$today'),
                    SUM(a.type = 'REJECT' AND a.updated_at >= '$today'),
                    0, 0
                FROM output_rfts_packing_po a
                WHERE a.updated_at BETWEEN '$mulai' AND '$todayEnd' AND a.alokasi = 'temporary packing'
                GROUP BY a.created_by_line, a.so_det_id
            ),
            g (id_ppic, po, tgl_shipment, line, so_det_id, ws, buyer, styleno, color, size, saldo_awal, terima_good, terima_reject, keluar_central, keluar_temporary) AS (
                SELECT
                    a.id_ppic_master_so,
                    TRIM(REGEXP_REPLACE(p.po, '[[:space:]]+', ' ')),
                    p.tgl_shipment,
                    a.line,
                    a.id_so_det,
                    NULL, NULL, NULL, NULL, NULL,
                    -SUM(IF(a.tgl_trans < '$today', a.qty, 0)), 0, 0, SUM(IF(a.tgl_trans = '$today', a.qty, 0)), 0
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE a.tgl_trans BETWEEN '$mulai' AND '$today'
                GROUP BY a.id_ppic_master_so, a.line, a.id_so_det

                UNION ALL

                SELECT
                    NULL, '$tanpaPo', NULL,
                    a.line,
                    a.id_so_det,
                    NULL, NULL, NULL, NULL, NULL,
                    -SUM(IF(a.tgl_trans < '$today', a.qty, 0)), 0, 0, SUM(IF(a.tgl_trans = '$today', a.qty, 0)), 0
                FROM laravel_nds.packing_trf_garment a
                WHERE a.tgl_trans BETWEEN '$mulai' AND '$today'
                  AND a.line = '$tanpaLine' AND a.id_ppic_master_so IS NULL
                  AND COALESCE(a.tujuan, '') != 'Temporary Packing'
                GROUP BY a.line, a.id_so_det

                UNION ALL

                SELECT
                    NULL, 'TEMPORARY PACKING', NULL,
                    a.line,
                    a.id_so_det,
                    NULL, NULL, NULL, NULL, NULL,
                    -SUM(IF(a.tgl_trans < '$today', a.qty, 0)), 0, 0, 0, SUM(IF(a.tgl_trans = '$today', a.qty, 0))
                FROM laravel_nds.packing_trf_garment a
                WHERE a.tgl_trans BETWEEN '$mulai' AND '$today' AND a.tujuan = 'Temporary Packing'
                GROUP BY a.line, a.id_so_det
            ),
            u AS (
                $sumber
            ),
            x AS (
                SELECT
                    MAX(id_ppic)        AS id_ppic,
                    MAX(po)             AS po,
                    MAX(tgl_shipment)   AS tgl_shipment,
                    line,
                    MAX(so_det_id)      AS so_det_id,
                    MAX(ws)             AS ws,
                    MAX(buyer)          AS buyer,
                    MAX(styleno)        AS styleno,
                    MAX(color)          AS color,
                    MAX(size)           AS size,
                    SUM(saldo_awal)         AS saldo_awal,
                    SUM(terima_good)        AS terima_good,
                    SUM(terima_reject)      AS terima_reject,
                    SUM(keluar_central)     AS keluar_central,
                    SUM(keluar_temporary)   AS keluar_temporary
                FROM u
                WHERE line IS NOT NULL AND line != ''
                GROUP BY line,
                    COALESCE(so_det_id, CONCAT_WS('|', ws, buyer, styleno, color, size)),
                    COALESCE(id_ppic, po)
            )
            SELECT
                x.so_det_id,
                x.po,
                CASE WHEN x.po = 'TEMPORARY PACKING' THEN COALESCE($tglShipmentTemp, x.tgl_shipment) ELSE x.tgl_shipment END AS tgl_shipment,
                x.line,
                COALESCE(d.ws,      x.ws,      '-') AS ws,
                COALESCE(d.buyer,   x.buyer,   '-') AS buyer,
                COALESCE(d.color,   x.color,   '-') AS color,
                COALESCE(d.size,    x.size,    '-') AS size,
                COALESCE(d.styleno, x.styleno, '-') AS styleno,
                x.saldo_awal,
                x.terima_good,
                x.terima_reject,
                x.keluar_central,
                x.keluar_temporary,
                0                       AS switching_out,
                0                       AS switching_in,
                0                       AS adjustment,
                x.saldo_awal + x.terima_good + x.terima_reject - x.keluar_central - x.keluar_temporary AS saldo_akhir
            FROM x
            LEFT JOIN laravel_nds.master_sb_ws d ON x.so_det_id = d.id_so_det
            LEFT JOIN laravel_nds.master_size_new msn ON COALESCE(d.size, x.size) = msn.size
            WHERE x.saldo_awal != 0
            OR x.terima_good != 0
            OR x.terima_reject != 0
            OR x.keluar_central != 0
            OR x.keluar_temporary != 0
            ORDER BY x.line, x.po, ws, color, msn.urutan
        ");
    }

    private function wipNormalize($value)
    {
        return strtoupper(trim((string) $value));
    }

    private function wipItemKey($row, $style)
    {
        return implode('|', array_map([$this, 'wipNormalize'], [$row->ws, $row->color, $style, $row->size, $row->buyer]));
    }

    // Switching & adjustment setelah tgl saldo per item, sumbernya tidak punya line/PO
    private function wipSwitchingAdjustment($today)
    {
        $mulai = date('Y-m-d', strtotime(self::WIP_TGL_SALDO . ' +1 day'));

        $data = DB::select("
            SELECT no_ws AS ws, color, style, size, buyer,
                SUM(IF(tgl_saldo < ?, qty, 0)) AS qty_sebelum,
                SUM(IF(tgl_saldo = ?, qty, 0)) AS qty_hari_ini,
                'adjustment' AS jenis
            FROM wip_adjustment
            WHERE type_report = 'PACKING' AND status = 'Y' AND tgl_saldo BETWEEN ? AND ?
            GROUP BY no_ws, color, style, size, buyer

            UNION ALL

            SELECT from_no_ws, from_color, from_style, from_size, from_buyer,
                SUM(IF(from_tgl_saldo < ?, qty, 0)),
                SUM(IF(from_tgl_saldo = ?, qty, 0)),
                'switching_out'
            FROM wip_switching_adj
            WHERE type_report = 'PACKING' AND from_tgl_saldo BETWEEN ? AND ?
            GROUP BY from_no_ws, from_color, from_style, from_size, from_buyer

            UNION ALL

            SELECT no_ws, color, style, size, buyer,
                SUM(IF(tgl_saldo < ?, qty, 0)),
                SUM(IF(tgl_saldo = ?, qty, 0)),
                'switching_in'
            FROM wip_switching_adj
            WHERE type_report = 'PACKING' AND tgl_saldo BETWEEN ? AND ?
            GROUP BY no_ws, color, style, size, buyer
        ", [
            $today, $today, $mulai, $today,
            $today, $today, $mulai, $today,
            $today, $today, $mulai, $today,
        ]);

        $kosong = ['adjustment' => 0, 'switching_out' => 0, 'switching_in' => 0];
        $mutasi = [];
        foreach ($data as $d) {
            $key = $this->wipItemKey($d, $d->style);
            if (!isset($mutasi[$key])) {
                $mutasi[$key] = ['item' => $d, 'sebelum' => $kosong, 'hari_ini' => $kosong];
            }
            $mutasi[$key]['sebelum'][$d->jenis]  += (int) $d->qty_sebelum;
            $mutasi[$key]['hari_ini'][$d->jenis] += (int) $d->qty_hari_ini;
        }

        return $mutasi;
    }

    // Baris TANPA LINE digabung per item, ditambah switching & adjustment, lalu WS close order tertentu disembunyikan
    private function wipPackingLineSusun(array $rows, $today)
    {
        if (self::WIP_HANYA_SALDO) {
            return $this->wipSembunyikanCloseOrder($rows);
        }

        $perLine   = [];
        $tanpaLine = [];
        foreach ($rows as $row) {
            if ($row->line !== self::WIP_TANPA_LINE) {
                $perLine[] = $row;
                continue;
            }

            $key = $this->wipItemKey($row, $row->styleno);
            if (!isset($tanpaLine[$key])) {
                $tanpaLine[$key] = $row;
                continue;
            }

            // utamakan baris yang PO-nya dari snapshot
            $utama  = $tanpaLine[$key];
            $tambah = $row;
            if ($utama->po === self::WIP_TANPA_PO && $row->po !== self::WIP_TANPA_PO) {
                [$utama, $tambah] = [$row, $utama];
            }
            foreach (self::WIP_QTY_COLUMNS as $column) {
                $utama->{$column} = (int) $utama->{$column} + (int) $tambah->{$column};
            }
            $tanpaLine[$key] = $utama;
        }

        foreach ($this->wipSwitchingAdjustment($today) as $key => $mutasi) {
            if (!isset($tanpaLine[$key])) {
                $tanpaLine[$key] = (object) ([
                    'so_det_id'    => null,
                    'po'           => self::WIP_TANPA_PO,
                    'tgl_shipment' => null,
                    'line'         => self::WIP_TANPA_LINE,
                    'ws'           => $mutasi['item']->ws,
                    'buyer'        => $mutasi['item']->buyer,
                    'color'        => $mutasi['item']->color,
                    'size'         => $mutasi['item']->size,
                    'styleno'      => $mutasi['item']->style,
                ] + array_fill_keys(self::WIP_QTY_COLUMNS, 0));
            }

            $item     = $tanpaLine[$key];
            $sebelum  = $mutasi['sebelum'];
            $hariIni  = $mutasi['hari_ini'];
            $netoLalu = $sebelum['switching_in'] - $sebelum['switching_out'] + $sebelum['adjustment'];
            $netoKini = $hariIni['switching_in'] - $hariIni['switching_out'] + $hariIni['adjustment'];

            $item->saldo_awal    = (int) $item->saldo_awal + $netoLalu;
            $item->switching_out = (int) $item->switching_out + $hariIni['switching_out'];
            $item->switching_in  = (int) $item->switching_in + $hariIni['switching_in'];
            $item->adjustment    = (int) $item->adjustment + $hariIni['adjustment'];
            $item->saldo_akhir   = (int) $item->saldo_akhir + $netoLalu + $netoKini;
        }

        $rows = array_merge($perLine, array_values(array_filter($tanpaLine, function ($item) {
            foreach (self::WIP_QTY_COLUMNS as $column) {
                if ($item->{$column} != 0) {
                    return true;
                }
            }
            return false;
        })));

        return $this->wipSembunyikanCloseOrder($rows);
    }

    private function wipSembunyikanCloseOrder(array $rows)
    {
        $statusOrder = $this->wipStatusOrder(self::WIP_WS_DISEMBUNYIKAN);
        $wsCloseOrder = array_map([$this, 'wipNormalize'], array_keys(array_filter($statusOrder, function ($status) {
            return $status === 'Close Order';
        })));

        return array_values(array_filter($rows, function ($row) use ($wsCloseOrder) {
            return !in_array($this->wipNormalize($row->ws), $wsCloseOrder, true);
        }));
    }

    private function wipCacheKey($today)
    {
        return 'wip_packing_line_saldo_' . $today . '_' . self::WIP_TGL_SALDO;
    }

    // dipanggil setelah transfer garment disimpan supaya qty WIP langsung terbarui
    public function wipLupakanCache()
    {
        Cache::forget($this->wipCacheKey(date('Y-m-d')));
    }

    private function wipPackingLineData($today)
    {
        return Cache::remember($this->wipCacheKey($today), 300, function () use ($today) {
            return $this->wipPackingLineSusun($this->wipPackingLineRows($today), $today);
        });
    }

    public function wipPackingLineTanpaLineData()
    {
        return array_values(array_filter($this->wipPackingLineData(date('Y-m-d')), function ($row) {
            return $row->line === self::WIP_TANPA_LINE && $row->po === self::WIP_TANPA_PO;
        }));
    }

    public function wipPackingLineByLinePo($line, $po)
    {
        $line = $this->wipNormalize($line);
        $po   = $this->wipNormalize(preg_replace('/\s+/', ' ', (string) $po));

        return array_values(array_filter($this->wipPackingLineData(date('Y-m-d')), function ($row) use ($line, $po) {
            return $this->wipNormalize($row->line) === $line && $this->wipNormalize($row->po) === $po;
        }));
    }

    // Pilihan line / PO tujuan Packing Central: baris yang punya PO dan masih punya saldo (termasuk TANPA LINE ber-PO)
    private function wipPackingLineCentralRows()
    {
        return array_values(array_filter($this->wipPackingLineData(date('Y-m-d')), function ($row) {
            return $row->po !== self::WIP_TANPA_PO
                && $this->wipNormalize($row->po) !== 'TEMPORARY PACKING'
                && (int) $row->saldo_akhir > 0;
        }));
    }

    public function wipPackingLineCentralByLine($line)
    {
        $line = $this->wipNormalize($line);

        return array_values(array_filter($this->wipPackingLineCentralRows(), function ($row) use ($line) {
            return $this->wipNormalize($row->line) === $line;
        }));
    }

    public function wipPackingLineCentralByPo($po)
    {
        $po = $this->wipNormalize(preg_replace('/\s+/', ' ', (string) $po));

        return array_values(array_filter($this->wipPackingLineCentralRows(), function ($row) use ($po) {
            return $this->wipNormalize($row->po) === $po;
        }));
    }

    // Pilihan tujuan Temporary Packing: baris PO TEMPORARY PACKING yang masih punya saldo, bisa difilter line
    public function wipPackingLineTemporaryRows($line = null)
    {
        $line = $line ? $this->wipNormalize($line) : null;

        return array_values(array_filter($this->wipPackingLineData(date('Y-m-d')), function ($row) use ($line) {
            return $this->wipNormalize($row->po) === 'TEMPORARY PACKING'
                && (int) $row->saldo_akhir > 0
                && ($line === null || $this->wipNormalize($row->line) === $line);
        }));
    }

    private function wipStatusOrder(array $wsList)
    {
        return DB::table('master_sb_ws as m')
            ->leftJoin('signalbit_erp.act_costing as act', 'm.id_act_cost', '=', 'act.id')
            ->selectRaw('m.ws, MAX(act.close_order) AS close_order')
            ->whereIn('m.ws', array_values(array_unique($wsList)))
            ->groupBy('m.ws')
            ->pluck('close_order', 'ws')
            ->map(function ($closeOrder) {
                return $closeOrder === 'Y' ? 'Close Order' : 'Open Order';
            })
            ->all();
    }

    public function wip_packing_line_detail(Request $request)
    {
        $po    = $this->wipNormalize(preg_replace('/\s+/', ' ', (string) $request->po));
        $line  = $this->wipNormalize($request->line);
        $today = date('Y-m-d');

        try {
            $data = [];
            foreach ($this->wipPackingLineData($today) as $row) {
                if ($this->wipNormalize($row->po) !== $po || $this->wipNormalize($row->line) !== $line) {
                    continue;
                }

                $key = $this->wipItemKey($row, $row->styleno);
                if (!isset($data[$key])) {
                    $data[$key] = [
                        'buyer'   => $row->buyer,
                        'ws'      => $row->ws ?? '-',
                        'styleno' => $row->styleno,
                        'color'   => $row->color,
                        'size'    => $row->size,
                    ] + array_fill_keys(self::WIP_QTY_COLUMNS, 0);
                }
                foreach (self::WIP_QTY_COLUMNS as $column) {
                    $data[$key][$column] += (int) $row->{$column};
                }
            }

            $statusOrder = $this->wipStatusOrder(array_column($data, 'ws'));

            foreach ($data as $key => $item) {
                $data[$key]['status_order'] = $statusOrder[$item['ws']] ?? 'Open Order';
            }

            return response()->json(array_values($data));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function wip_packing_line_data(Request $request)
    {
        $today = date('Y-m-d');

        // tombol Refresh: ambil data baru, jangan pakai cache
        if ($request->boolean('refresh')) {
            Cache::forget($this->wipCacheKey($today));
        }

        $grouped   = [];
        $total_wip = 0;
        foreach ($this->wipPackingLineData($today) as $row) {
            $lineKey = $this->wipNormalize($row->line);
            $poKey   = $this->wipNormalize($row->po);

            if (!isset($grouped[$lineKey][$poKey])) {
                $grouped[$lineKey][$poKey] = [
                    'line'       => $row->line,
                    'po'         => $row->po,
                    'qty_sa'     => 0,
                    'qty_output' => 0,
                    'qty_trf'    => 0,
                    'wip'        => 0,
                ];
            }
            $grouped[$lineKey][$poKey]['qty_sa']     += (int) $row->saldo_awal;
            $grouped[$lineKey][$poKey]['qty_output'] += (int) $row->terima_good + (int) $row->terima_reject;
            $grouped[$lineKey][$poKey]['qty_trf']    += (int) $row->keluar_central + (int) $row->keluar_temporary;
            $grouped[$lineKey][$poKey]['wip']        += (int) $row->saldo_akhir;
            $total_wip += (int) $row->saldo_akhir;
        }

        $lines = [];
        foreach ($grouped as $items) {
            $items = array_values($items);
            $lines[$items[0]['line']] = array_map(function ($item) {
                unset($item['line']);
                return $item;
            }, $items);
        }

        return response()->json([
            'lines'     => $lines,
            'total_wip' => $total_wip,
        ]);
    }

    public function wip_packing_line_export()
    {
        $today = date('Y-m-d');

        $data = $this->wipPackingLineSusun($this->wipPackingLineRows($today, true), $today);
        $statusOrder = $this->wipStatusOrder(array_column($data, 'ws'));

        $filename = 'WIP_Packing_Line_' . $today;
        $excel    = FastExcel::create($filename);
        $sheet    = $excel->sheet();

        /* ── Title ── */
        $sheet->writeRow(['WIP PACKING LINE — ' . $today], ['font-style' => 'bold', 'font-size' => 13]);
        $sheet->writeRow(['']);

        /* ── Header ── */
        $sheet->writeRow(
            ['Line', 'PO', 'Tgl Shipment', 'Status', 'Buyer', 'WS', 'Style', 'Color', 'Size', 'Saldo Awal', 'Terima Good', 'Terima Reject', 'Keluar Packing Central', 'Keluar Temporary Packing', 'Switching OUT', 'Switching IN', 'Adjustment', 'Saldo Akhir'],
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
            $wip      = (float) $row->saldo_akhir;
            $wipColor = $wip < 0 ? '#CC2C2C' : ($wip > 0 ? '#1A7F4B' : '#888888');

            $sheet->writeRow(
                [
                    $row->line    ?? '-',
                    $row->po      ?? '-',
                    $row->tgl_shipment ?? '-',
                    $statusOrder[$row->ws] ?? 'Open Order',
                    $row->buyer   ?? '-',
                    $row->ws      ?? '-',
                    $row->styleno ?? '-',
                    $row->color   ?? '-',
                    $row->size    ?? '-',
                    (float) $row->saldo_awal,
                    (float) $row->terima_good,
                    (float) $row->terima_reject,
                    (float) $row->keluar_central,
                    (float) $row->keluar_temporary,
                    (float) $row->switching_out,
                    (float) $row->switching_in,
                    (float) $row->adjustment,
                    $wip,
                ],
                ['border' => 'thin']
            );

            $sheet->setCellStyle('R' . $rowNum, ['color' => $wipColor, 'font-style' => 'bold']);
            $rowNum++;
        }

        /* ── Column widths ── */
        foreach (
            [
                'A' => 16,
                'B' => 14,
                'C' => 22,
                'D' => 14,
                'E' => 16,
                'F' => 20,
                'G' => 18,
                'H' => 10,
                'I' => 12,
                'J' => 12,
                'K' => 14,
                'L' => 12,
                'M' => 12,
                'N' => 14,
                'O' => 14,
                'P' => 12,
                'Q' => 12,
                'R' => 12,
            ] as $col => $width
        ) {
            $sheet->setColWidth($col, $width);
        }

        return $excel->download();
    }
}
