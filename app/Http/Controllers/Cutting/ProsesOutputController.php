<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class ProsesOutputController extends Controller
{
    public function index()
    {
        $buyers = DB::table('master_sb_ws')
            ->whereNotNull('buyer')
            ->distinct()
            ->orderBy('buyer')
            ->pluck('buyer');

        return view('cutting.proses-output.index', [
            'containerFluid' => true,
            'page' => 'dashboard-cutting',
            'subPage' => 'proses-output',
            'buyers' => $buyers,
        ]);
    }

    // Pilihan filter dari master_sb_ws: ws (per buyer), style / color / size (per ws)
    public function getFilter(Request $request)
    {
        $columns = [
            'ws'    => 'ws',
            'style' => 'styleno',
            'color' => 'color',
            'size'  => 'size',
        ];

        if (!isset($columns[$request->type])) {
            return [];
        }

        $column = $columns[$request->type];

        $query = DB::table('master_sb_ws')
            ->whereNotNull($column)
            ->when($request->buyer, fn ($q) => $q->where('buyer', $request->buyer))
            ->when($request->ws, fn ($q) => $q->where('ws', $request->ws));

        // Size diurutkan sesuai urutan so_det, lainnya sesuai abjad
        if ($request->type == 'size') {
            return $query
                ->groupBy('size')
                ->orderByRaw('MIN(id_so_det)')
                ->pluck('size');
        }

        return $query->distinct()->orderBy($column)->pluck($column);
    }

    // Qty output cutting per buyer, ws, style, color, panel, size:
    // qty form (form cut, reject, piece, ws additional) + qty switching in / out (log switching yang masih aktif) + qty scrap + qty adjustment
    public function getData(Request $request)
    {
        return response()->json([
            'data'      => $this->queryData($request),
            'qty_order' => $this->queryQtyOrder($request),
        ]);
    }

    // WS yang dipakai: ws terpilih, atau semua ws milik buyer
    private function wsList(Request $request)
    {
        return $request->ws
            ? [$request->ws]
            : ($request->buyer ? DB::table('master_sb_ws')->where('buyer', $request->buyer)->distinct()->pluck('ws')->all() : []);
    }

    // Qty order (ppic_master_so.qty_po) per ws / color / size => [ 'ws|color|size' => qty ]
    private function queryQtyOrder(Request $request)
    {
        $wsList = $this->wsList($request);

        if (!$wsList) {
            return [];
        }

        return DB::table('ppic_master_so')
            ->leftJoin('master_sb_ws', 'master_sb_ws.id_so_det', '=', 'ppic_master_so.id_so_det')
            ->whereIn('master_sb_ws.ws', $wsList)
            ->when($request->style, fn ($q) => $q->where('master_sb_ws.styleno', $request->style))
            ->when($request->color, fn ($q) => $q->where('master_sb_ws.color', $request->color))
            ->when($request->size, fn ($q) => $q->where('master_sb_ws.size', $request->size))
            ->groupBy('master_sb_ws.ws', 'master_sb_ws.color', 'master_sb_ws.size')
            ->selectRaw("CONCAT(master_sb_ws.ws, '|', master_sb_ws.color, '|', master_sb_ws.size) row_key, SUM(ppic_master_so.qty_po) qty_po")
            ->pluck('qty_po', 'row_key')
            ->map(fn ($qty) => (float) $qty)
            ->all();
    }

    // Data per panel (dipakai tabel & export)
    private function queryData(Request $request)
    {
        $wsList = $this->wsList($request);

        if (!$wsList) {
            return [];
        }

        $wsIn = implode(', ', array_fill(0, count($wsList), '?'));

        $filters = '';
        $filterBindings = [];

        foreach (['style', 'color', 'size'] as $field) {
            if ($request->$field) {
                $filters .= " AND mc.$field = ? ";
                $filterBindings[] = $request->$field;
            }
        }

        return DB::select("
            SELECT
                mc.buyer,
                mc.act_costing_ws ws,
                mc.style,
                mc.color,
                mc.panel,
                mc.size,
                COALESCE(MIN(master_size_new.urutan), 9999) size_order,
                SUM((mc.form_gelar * mc.ratio) + COALESCE(mc.diff, 0)) qty_form,
                SUM(mc.qty_in) qty_switching_in,
                SUM(mc.qty_out) qty_switching_out,
                SUM(mc.qty_scrap) qty_scrap,
                SUM(mc.qty_adjustment) qty_adjustment
            FROM (
                -- 1. Form cut (marker)
                SELECT
                    marker_input.buyer,
                    marker_input.act_costing_id,
                    marker_input.act_costing_ws,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    marker_input_detail.so_det_id,
                    master_sb_ws.size,
                    marker_input_detail.ratio,
                    SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                    SUM(modify_size_qty.difference_qty) diff,
                    0 qty_in,
                    0 qty_out,
                    0 qty_scrap,
                    0 qty_adjustment
                FROM marker_input
                INNER JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                INNER JOIN master_sb_ws ON master_sb_ws.id_so_det = marker_input_detail.so_det_id
                INNER JOIN (
                    SELECT form_cut_input.id, form_cut_input.id_marker, form_cut_input.total_lembar,
                           SUM(form_cut_input_detail.lembar_gelaran) detail
                    FROM form_cut_input
                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                    WHERE form_cut_input.status = 'SELESAI PENGERJAAN'
                      AND form_cut_input.waktu_mulai IS NOT NULL
                    GROUP BY form_cut_input.id
                ) form_cut ON form_cut.id_marker = marker_input.kode
                LEFT JOIN (
                    SELECT so_det_id, form_cut_id, SUM(difference_qty) difference_qty
                    FROM modify_size_qty
                    GROUP BY so_det_id, form_cut_id
                ) modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                WHERE (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                  AND (marker_input_detail.ratio > 0 OR modify_size_qty.difference_qty != 0)
                  AND marker_input.act_costing_ws IN ($wsIn)
                GROUP BY marker_input.id, marker_input_detail.id, form_cut.id

                UNION ALL

                -- 2. Reject
                SELECT
                    form_cut_reject.buyer,
                    form_cut_reject.act_costing_id,
                    form_cut_reject.act_costing_ws,
                    form_cut_reject.style,
                    form_cut_reject.color,
                    form_cut_reject.panel,
                    form_cut_reject_detail.so_det_id,
                    master_sb_ws.size,
                    1 ratio,
                    SUM(form_cut_reject_detail.qty) form_gelar,
                    NULL diff,
                    0 qty_in,
                    0 qty_out,
                    0 qty_scrap,
                    0 qty_adjustment
                FROM form_cut_reject
                LEFT JOIN form_cut_reject_detail ON form_cut_reject_detail.form_id = form_cut_reject.id
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = form_cut_reject_detail.so_det_id
                WHERE form_cut_reject_detail.qty > 0
                  AND form_cut_reject.act_costing_ws IN ($wsIn)
                GROUP BY form_cut_reject.id, form_cut_reject_detail.so_det_id

                UNION ALL

                -- 3. Form piece
                SELECT
                    form_cut_piece.buyer,
                    form_cut_piece.act_costing_id,
                    form_cut_piece.act_costing_ws,
                    form_cut_piece.style,
                    form_cut_piece.color,
                    form_cut_piece.panel,
                    form_cut_piece_detail_size.so_det_id,
                    master_sb_ws.size,
                    1 ratio,
                    SUM(form_cut_piece_detail_size.qty) form_gelar,
                    NULL diff,
                    0 qty_in,
                    0 qty_out,
                    0 qty_scrap,
                    0 qty_adjustment
                FROM form_cut_piece
                LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                LEFT JOIN form_cut_piece_detail_size ON form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                WHERE form_cut_piece.status = 'complete'
                  AND form_cut_piece_detail_size.qty > 0
                  AND form_cut_piece.act_costing_ws IN ($wsIn)
                GROUP BY form_cut_piece.id, form_cut_piece_detail_size.so_det_id

                UNION ALL

                -- 4. WS additional
                SELECT
                    stocker_ws_additional.buyer,
                    stocker_ws_additional.act_costing_id,
                    stocker_ws_additional.act_costing_ws,
                    stocker_ws_additional.style,
                    stocker_ws_additional.color,
                    stocker_ws_additional.panel,
                    stocker_ws_additional_detail.so_det_id,
                    master_sb_ws.size,
                    stocker_ws_additional_detail.ratio,
                    SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                    SUM(modify_size_qty.difference_qty) diff,
                    0 qty_in,
                    0 qty_out,
                    0 qty_scrap,
                    0 qty_adjustment
                FROM form_cut_input
                LEFT JOIN (
                    SELECT form_cut_input.id, form_cut_input.total_lembar,
                           SUM(form_cut_input_detail.lembar_gelaran) detail
                    FROM form_cut_input
                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                    WHERE form_cut_input.status = 'SELESAI PENGERJAAN'
                      AND form_cut_input.id_marker IS NOT NULL
                    GROUP BY form_cut_input.id
                ) form_cut ON form_cut.id = form_cut_input.id
                LEFT JOIN stocker_ws_additional ON stocker_ws_additional.form_cut_id = form_cut_input.id
                LEFT JOIN stocker_ws_additional_detail ON stocker_ws_additional_detail.stocker_additional_id = stocker_ws_additional.id
                LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_ws_additional_detail.so_det_id
                LEFT JOIN (
                    SELECT so_det_id, form_cut_id, SUM(difference_qty) difference_qty
                    FROM modify_size_qty
                    GROUP BY so_det_id, form_cut_id
                ) modify_size_qty ON modify_size_qty.so_det_id = stocker_ws_additional_detail.so_det_id AND modify_size_qty.form_cut_id = form_cut_input.id
                WHERE form_cut_input.status = 'SELESAI PENGERJAAN'
                  AND (stocker_ws_additional_detail.ratio > 0 OR modify_size_qty.difference_qty != 0)
                  AND stocker_ws_additional.act_costing_ws IN ($wsIn)
                GROUP BY stocker_ws_additional.id, stocker_ws_additional_detail.so_det_id

                UNION ALL

                -- 5. Switching out (sisi form asal)
                SELECT
                    marker_asal.buyer,
                    marker_asal.act_costing_id,
                    marker_asal.act_costing_ws,
                    marker_asal.style,
                    marker_asal.color,
                    marker_asal.panel,
                    msb_asal.id_so_det so_det_id,
                    msb_asal.size,
                    0 ratio,
                    0 form_gelar,
                    NULL diff,
                    0 qty_in,
                    SUM(logs.qty_transfer) qty_out,
                    0 qty_scrap,
                    0 qty_adjustment
                FROM form_cut_input_detail_output_logs logs
                LEFT JOIN form_cut_input form_asal ON form_asal.id = logs.form_cut_input_id_asal
                LEFT JOIN marker_input marker_asal ON marker_asal.id = form_asal.marker_id
                LEFT JOIN master_sb_ws msb_asal ON msb_asal.id_so_det = COALESCE(
                    logs.so_det_id_asal,
                    (SELECT MIN(mid.so_det_id) FROM marker_input_detail mid WHERE mid.marker_id = marker_asal.id AND mid.size = logs.size_asal)
                )
                WHERE (logs.is_active IS NULL OR logs.is_active > 0)
                  AND marker_asal.act_costing_ws IN ($wsIn)
                GROUP BY marker_asal.id, msb_asal.id_so_det

                UNION ALL

                -- 6. Switching in (sisi form tujuan)
                SELECT
                    marker_tujuan.buyer,
                    marker_tujuan.act_costing_id,
                    marker_tujuan.act_costing_ws,
                    marker_tujuan.style,
                    marker_tujuan.color,
                    marker_tujuan.panel,
                    msb_tujuan.id_so_det so_det_id,
                    msb_tujuan.size,
                    0 ratio,
                    0 form_gelar,
                    NULL diff,
                    SUM(logs.qty_transfer) qty_in,
                    0 qty_out,
                    0 qty_scrap,
                    0 qty_adjustment
                FROM form_cut_input_detail_output_logs logs
                LEFT JOIN form_cut_input form_tujuan ON form_tujuan.id = logs.form_cut_input_id_tujuan
                LEFT JOIN marker_input marker_tujuan ON marker_tujuan.id = form_tujuan.marker_id
                LEFT JOIN master_sb_ws msb_tujuan ON msb_tujuan.id_so_det = COALESCE(
                    logs.so_det_id_tujuan,
                    (SELECT MIN(mid.so_det_id) FROM marker_input_detail mid WHERE mid.marker_id = marker_tujuan.id AND mid.size = logs.size_tujuan)
                )
                WHERE (logs.is_active IS NULL OR logs.is_active > 0)
                  AND marker_tujuan.act_costing_ws IN ($wsIn)
                GROUP BY marker_tujuan.id, msb_tujuan.id_so_det

                UNION ALL

                -- 7. Scrap: qty per panel = qty part terkecil di panel itu
                SELECT
                    scrap.buyer,
                    scrap.act_costing_id,
                    scrap.act_costing_ws,
                    scrap.style,
                    scrap.color,
                    scrap.panel,
                    scrap.so_det_id,
                    scrap.size,
                    0 ratio,
                    0 form_gelar,
                    NULL diff,
                    0 qty_in,
                    0 qty_out,
                    MIN(scrap.qty) qty_scrap,
                    0 qty_adjustment
                FROM (
                    SELECT
                        master_sb_ws.buyer,
                        master_sb_ws.id_act_cost act_costing_id,
                        master_sb_ws.ws act_costing_ws,
                        master_sb_ws.styleno style,
                        master_sb_ws.color,
                        (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                        master_sb_ws.id_so_det so_det_id,
                        master_sb_ws.size,
                        pd.id part_detail_id,
                        SUM(fss.qty) qty
                    FROM form_cut_scrap fcs
                    LEFT JOIN form_cut_scrap_detail fcsd ON fcs.id = fcsd.form_scrap_id
                    LEFT JOIN form_cut_scrap_part fcsp ON fcsd.id = fcsp.form_scrap_detail_id
                    LEFT JOIN part_detail pd ON fcsp.part_detail_id = pd.id
                    LEFT JOIN part p ON pd.part_id = p.id
                    LEFT JOIN part_detail pd_com ON pd_com.id = pd.from_part_detail
                    LEFT JOIN part p_com ON p_com.id = pd_com.part_id
                    LEFT JOIN form_cut_scrap_size fss ON fcsp.id = fss.form_scrap_part_id
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = fss.so_det_id
                    LEFT JOIN part_custom pcust ON pcust.part_id = p.id AND pcust.part_detail_id = pd.id AND pcust.color = master_sb_ws.color
                        AND (CASE WHEN pcust.tanggal_berlaku IS NOT NULL THEN pcust.tanggal_berlaku <= CONCAT(CURDATE(), ' 23:59:59') ELSE 1=1 END)
                    WHERE fcs.status = 'complete'
                      AND master_sb_ws.ws IN ($wsIn)
                    GROUP BY master_sb_ws.id_so_det, panel, pd.id
                ) scrap
                GROUP BY scrap.so_det_id, scrap.panel

                UNION ALL

                -- 8. Adjustment (wip_adjustment CUTTING): qty per panel = qty part terkecil di panel itu
                SELECT
                    adjustment.buyer,
                    adjustment.act_costing_id,
                    adjustment.act_costing_ws,
                    adjustment.style,
                    adjustment.color,
                    adjustment.panel,
                    adjustment.so_det_id,
                    adjustment.size,
                    0 ratio,
                    0 form_gelar,
                    NULL diff,
                    0 qty_in,
                    0 qty_out,
                    0 qty_scrap,
                    MIN(adjustment.qty) qty_adjustment
                FROM (
                    SELECT
                        master_sb_ws.buyer,
                        master_sb_ws.id_act_cost act_costing_id,
                        master_sb_ws.ws act_costing_ws,
                        master_sb_ws.styleno style,
                        master_sb_ws.color,
                        wip_adjustment.panel,
                        master_sb_ws.id_so_det so_det_id,
                        master_sb_ws.size,
                        SUM(wip_adjustment.qty) qty
                    FROM wip_adjustment
                    INNER JOIN (
                        SELECT
                            MIN(id_so_det) id_so_det,
                            MIN(id_act_cost) id_act_cost,
                            ws,
                            color,
                            size,
                            styleno,
                            buyer
                        FROM master_sb_ws
                        WHERE ws IN ($wsIn)
                        GROUP BY ws, color, size, styleno, buyer
                    ) master_sb_ws
                        ON master_sb_ws.ws = wip_adjustment.no_ws
                        AND master_sb_ws.color = wip_adjustment.color
                        AND master_sb_ws.size = wip_adjustment.size
                        AND master_sb_ws.styleno = wip_adjustment.style
                    WHERE wip_adjustment.type_report = 'CUTTING'
                      AND wip_adjustment.status = 'Y'
                      AND wip_adjustment.no_ws IN ($wsIn)
                    GROUP BY master_sb_ws.id_so_det, wip_adjustment.style, wip_adjustment.panel, wip_adjustment.part
                ) adjustment
                GROUP BY adjustment.so_det_id, adjustment.panel
            ) mc
            LEFT JOIN master_size_new ON master_size_new.size = mc.size
            WHERE 1 = 1
                $filters
            GROUP BY mc.act_costing_id, mc.color, mc.panel, mc.size
            ORDER BY mc.buyer, mc.act_costing_ws, mc.style, mc.color, size_order, mc.panel
        ", array_merge(array_merge(...array_fill(0, 9, $wsList)), $filterBindings));
    }

    // Sub kolom tiap panel: field => judul (urutan sama dengan tabel)
    private $qtyColumns = [
        'qty_form'          => 'Form',
        'qty_scrap'         => 'Scrap',
        'qty_switching_out' => 'Switching Out',
        'qty_switching_in'  => 'Switching In',
        'qty_adjustment'    => 'Adjustment',
    ];

    // Warna header per panel (sama dengan tabel)
    private $panelColors = ['#d1e7dd', '#fff3cd', '#f8d7da', '#e2d9f3', '#ffe5d0', '#d2f4ea', '#f7d6e6', '#e2e3e5'];

    // Data per panel jadi 1 baris per ws / color / size, panel jadi kolom (sama dengan pivotData di view)
    private function pivotData($data, $qtyOrder = [])
    {
        $panels = array_values(array_unique(array_map(fn ($item) => $item->panel ?? '-', $data)));
        sort($panels);

        $rows = [];

        foreach ($data as $item) {
            $key = $item->ws . '|' . $item->color . '|' . $item->size;

            $rows[$key] ??= [
                'buyer'      => $item->buyer,
                'ws'         => $item->ws,
                'style'      => $item->style,
                'color'      => $item->color,
                'size'       => $item->size,
                'size_order' => (int) $item->size_order,
                'panels'     => [],
            ];

            $panel = $item->panel ?? '-';

            foreach ($this->qtyColumns as $field => $title) {
                $rows[$key]['panels'][$panel][$field] = ($rows[$key]['panels'][$panel][$field] ?? 0) + (float) $item->$field;
            }

            $rows[$key]['size_order'] = min($rows[$key]['size_order'], (int) $item->size_order);
        }

        foreach ($rows as &$row) {
            foreach ($panels as $panel) {
                foreach ($this->qtyColumns as $field => $title) {
                    $row['panels'][$panel][$field] ??= 0;
                }

                $qty = $row['panels'][$panel];

                // Output per panel = form + scrap - switching out + switching in + adjustment
                $row['panels'][$panel]['output'] = $qty['qty_form'] + $qty['qty_scrap'] - $qty['qty_switching_out'] + $qty['qty_switching_in'] + $qty['qty_adjustment'];
            }

            // Output set = output panel terkecil
            $row['output_set'] = $panels ? min(array_map(fn ($panel) => $row['panels'][$panel]['output'], $panels)) : 0;

            // Balance = output set - qty order
            $row['qty_order'] = $qtyOrder[$row['ws'] . '|' . $row['color'] . '|' . $row['size']] ?? 0;
            $row['balance'] = $row['output_set'] - $row['qty_order'];
        }
        unset($row);

        $rows = array_values($rows);

        usort($rows, fn ($a, $b) => strcmp($a['buyer'], $b['buyer']) ?: strcmp($a['ws'], $b['ws']) ?: strcmp($a['style'], $b['style']) ?: strcmp($a['color'], $b['color']) ?: $a['size_order'] <=> $b['size_order']);

        return [$panels, $rows];
    }

    public function exportExcel(Request $request)
    {
        $data = $this->queryData($request);

        if (!$data) {
            return response()->json([
                'status'  => 422,
                'message' => 'Tidak ada data untuk diexport'
            ], 422);
        }

        [$panels, $rows] = $this->pivotData($data, $this->queryQtyOrder($request));

        $textColumns = ['Buyer', 'WS', 'Style', 'Color', 'Size'];
        $panelColumns = array_merge(array_values($this->qtyColumns), ['Output']);
        $endColumns = ['Output Set', 'Qty Order', 'Balance'];
        $totalColumns = count($textColumns) + count($panels) * count($panelColumns) + count($endColumns);
        $col = fn ($index) => Coordinate::stringFromColumnIndex($index);

        $excel = FastExcel::create('proses-output');
        $sheet = $excel->sheet();

        $sheet->writeRow(['Proses Output'], ['font-style' => 'bold', 'font-size' => 14]);

        // 1 baris per filter, hanya filter yang dipilih
        $filters = array_filter([
            'Buyer' => $request->buyer,
            'WS'    => $request->ws,
            'Style' => $request->style,
            'Color' => $request->color,
            'Size'  => $request->size,
        ]);

        foreach ($filters as $label => $value) {
            $sheet->writeRow([$label . ': ' . $value], ['font-size' => 12]);
        }

        $sheet->writeRow(['']);

        // Header 2 baris: panel (merge) & sub kolom qty
        $headerStyle = ['font-style' => 'bold', 'border' => 'thin', 'text-align' => 'center', 'vertical-align' => 'center'];
        $blue = $headerStyle + ['fill' => '#ADD8E6'];

        $panelRow = $textColumns;
        $subRow = array_fill(0, count($textColumns), '');
        $panelStyles = array_fill(0, count($textColumns), $blue);

        foreach ($panels as $p => $panel) {
            $style = $headerStyle + ['fill' => $this->panelColors[$p % count($this->panelColors)]];

            foreach ($panelColumns as $i => $title) {
                $panelRow[] = $i === 0 ? $panel : '';
                $subRow[] = $title;
                $panelStyles[] = $style;
            }
        }

        foreach ($endColumns as $title) {
            $panelRow[] = $title;
            $subRow[] = '';
            $panelStyles[] = $blue;
        }

        // Judul + baris filter + baris kosong, header mulai setelahnya
        $headerRow = 1 + count($filters) + 2;

        $sheet->writeRow($panelRow, null, $panelStyles);
        $sheet->writeRow($subRow, null, $panelStyles);

        // Merge: kolom teks & kolom ujung (output set, qty order, balance) 2 baris ke bawah, nama panel ke samping
        foreach (range(1, count($textColumns)) as $i) {
            $sheet->mergeCells($col($i) . $headerRow . ':' . $col($i) . ($headerRow + 1));
        }

        foreach ($panels as $p => $panel) {
            $start = count($textColumns) + $p * count($panelColumns) + 1;

            $sheet->mergeCells($col($start) . $headerRow . ':' . $col($start + count($panelColumns) - 1) . $headerRow);
        }

        foreach (range($totalColumns - count($endColumns) + 1, $totalColumns) as $i) {
            $sheet->mergeCells($col($i) . $headerRow . ':' . $col($i) . ($headerRow + 1));
        }

        // Detail
        $totals = array_fill(0, $totalColumns, 0);

        foreach ($rows as $row) {
            $values = [$row['buyer'], $row['ws'], $row['style'], $row['color'], $row['size']];

            foreach ($panels as $panel) {
                foreach (array_merge(array_keys($this->qtyColumns), ['output']) as $field) {
                    $values[] = $row['panels'][$panel][$field];
                }
            }

            $values[] = $row['output_set'];
            $values[] = $row['qty_order'];
            $values[] = $row['balance'];

            foreach ($values as $i => $value) {
                if ($i >= count($textColumns)) {
                    $totals[$i] += $value;
                }
            }

            $sheet->writeRow($values, ['border' => 'thin']);
        }

        // Total
        $totalRow = array_slice($totals, count($textColumns));
        $sheet->writeRow(array_merge(['Total'], array_fill(0, count($textColumns) - 1, ''), $totalRow), ['font-style' => 'bold', 'border' => 'thin']);

        $lastRow = $headerRow + 2 + count($rows);
        $sheet->mergeCells('A' . $lastRow . ':' . $col(count($textColumns)) . $lastRow);

        for ($i = 1; $i <= $totalColumns; $i++) {
            $sheet->setColWidth($col($i), $i <= count($textColumns) ? 22 : 14);
        }

        return $excel->download();
    }
}
