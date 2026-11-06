<?php

namespace App\Services;

use DB;

/**
 * Query Track Order Output, dipakai bersama oleh Livewire TrackOrderOutput (layar)
 * dan OrderOutputExport (Excel) supaya hasil keduanya selalu sama.
 *
 * $filter berisi: dateFrom, dateTo, outputType, groupBy, order, buyer, color, line, size
 */
class SewingOrderOutputService
{
    /**
     * Batas mundur master_plan.tgl_plan dari dateFrom, untuk output yang plan-nya sudah lama
     */
    protected $planLookbackDays = 120;

    /**
     * outputType menjadi bagian nama tabel (output_rfts{outputType}), jadi hanya
     * nilai yang dikenal yang boleh dipakai
     */
    public static function normalizeOutputType($outputType)
    {
        return in_array($outputType, ['_packing', '_packing_po'], true) ? $outputType : '';
    }

    public function orderGroup(array $filter)
    {
        $filter = $this->normalizeFilter($filter);
        $table = 'output_rfts'.$filter['outputType'];

        $bindings = [$filter['dateFrom'].' 00:00:00', $filter['dateTo'].' 23:59:59'];
        if ($filter['order']) $bindings[] = $filter['order'];

        $orderGroupSql = DB::connection('mysql_sb')->query()->fromRaw("(
                SELECT
                    coalesce( date( ".$table.".updated_at ), master_plan.tgl_plan ) tanggal,
                    master_plan.id_ws,
                    ".$table.".master_plan_id,
                    userpassword.username sewing_line,
                    act_costing.id act_id_ws,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color
                FROM
                    ".$table."
                    ".$this->userJoin($table, $filter['outputType'])."
                    LEFT JOIN master_plan on master_plan.id = ".$table.".master_plan_id
                    LEFT JOIN so_det on so_det.id = ".$table.".so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                WHERE
                    ".$table.".created_by IS NOT NULL
                    AND ".$table.".updated_at >= ?
                    AND ".$table.".updated_at <= ?
                    AND (so_det.cancel IS NULL OR so_det.cancel != 'Y')
                    ".($filter['order'] ? " AND act_costing.id = ?" : "")."
                GROUP BY
                    ".$table.".master_plan_id,
                    ".$table.".created_by
            ) as rfts", $bindings)->
            selectRaw("
                master_plan.tgl_plan tanggal,
                rfts.ws ws,
                rfts.style,
                rfts.color,
                COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line
                ".($filter['groupBy'] == "size" ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "rfts.act_id_ws")->
            leftJoin('master_plan', 'master_plan.id', '=', 'rfts.master_plan_id');
            if ($filter['groupBy'] == "size") $orderGroupSql->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->leftJoin('so_det', function ($join) { $join->on('so_det.id_so', '=', 'so.id'); $join->on('so_det.color', '=', 'rfts.color'); })->whereRaw("(so_det.cancel IS NULL OR so_det.cancel != 'Y')");
            if ($filter['dateFrom']) $orderGroupSql->where('rfts.tanggal', '>=', date('Y-m-d', strtotime('-'.$this->planLookbackDays.' days', strtotime($filter['dateFrom']))));
            if ($filter['dateTo']) $orderGroupSql->where('rfts.tanggal', '<=', $filter['dateTo']);
            if ($filter['color']) $orderGroupSql->where('rfts.color', $filter['color']);
            if ($filter['line']) $orderGroupSql->where('rfts.sewing_line', $filter['line']);
            if ($filter['groupBy'] == "size" && $filter['size']) $orderGroupSql->where('so_det.size', $filter['size']);
            if ($filter['order']) $orderGroupSql->where("act_costing.id", $filter['order']);
            if ($filter['buyer']) $orderGroupSql->where("act_costing.id_buyer", $filter['buyer']);
            $orderGroupSql->
                groupByRaw("rfts.act_id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.sewing_line, master_plan.sewing_line) ".($filter['groupBy'] == "size" ? ", so_det.size" : "")."")->
                orderBy("rfts.act_id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("rfts.color", "asc")->
                orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc ".($filter['groupBy'] == 'size' ? ', so_det.id asc' : ''));

        return $orderGroupSql->get();
    }

    public function orderOutputs(array $filter)
    {
        $filter = $this->normalizeFilter($filter);

        $bindings = [
            $filter['dateFrom'].' 00:00:00',
            $filter['dateTo'].' 23:59:59',
            date('Y-m-d', strtotime('-'.$this->planLookbackDays.' days', strtotime($filter['dateFrom']))),
            $filter['dateTo'],
        ];
        if ($filter['order']) $bindings[] = $filter['order'];
        if ($filter['buyer']) $bindings[] = $filter['buyer'];

        $orderOutputsSql = DB::connection('mysql_sb')->query()->fromRaw("
                (
                    SELECT
                        coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                        max( rfts.updated_at ) last_rft,
                        count( rfts.id ) rft,
                        master_plan.id master_plan_id,
                        master_plan.id_ws master_plan_id_ws,
                        act_costing.id act_id_ws,
                        so_det.color,
                        COALESCE ( userpassword.username, master_plan.sewing_line ) created_by
                        ".($filter['groupBy'] == 'size' ? ', rfts.so_det_id ' : '')."
                    FROM
                        output_rfts".$filter['outputType']." rfts
                        LEFT JOIN so_det ON so_det.id = rfts.so_det_id
                        LEFT JOIN so ON so.id = so_det.id_so
                        LEFT JOIN act_costing ON act_costing.id = so.id_cost
                        INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                        ".$this->userJoin('rfts', $filter['outputType'])."
                    WHERE
                        rfts.updated_at between ? and ?
                        AND master_plan.tgl_plan between ? and ?
                        AND (so_det.cancel IS NULL OR so_det.cancel != 'Y')
                        ".($filter['order'] ? " AND act_costing.id = ?" : "")."
                        ".($filter['buyer'] ? " AND act_costing.id_buyer = ?" : "")."
                    GROUP BY
                        act_costing.id,
                        so_det.color,
                        DATE ( rfts.updated_at ),
                        COALESCE ( userpassword.username, master_plan.sewing_line )
                        ".($filter['groupBy'] == 'size' ? ', rfts.so_det_id ' : '')."
                ) rfts
            ", $bindings)->
            selectRaw("
                rfts.tanggal,
                ".($filter['groupBy'] == 'size' ? ' rfts.so_det_id, so_det.size, ' : '')."
                SUM( rfts.rft ) output,
                act_costing.kpno ws,
                act_costing.styleno style,
                rfts.color,
                COALESCE ( rfts.created_by, master_plan.sewing_line ) AS sewing_line,
                master_plan.smv smv,
                master_plan.jam_kerja jam_kerja,
                master_plan.man_power man_power,
                master_plan.plan_target plan_target,
                COALESCE ( rfts.last_rft, master_plan.tgl_plan ) latest_output
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "rfts.act_id_ws")->
            leftJoin('master_plan', 'master_plan.id', '=', 'rfts.master_plan_id');
            if ($filter['groupBy'] == "size") $orderOutputsSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id')->whereRaw("(so_det.cancel IS NULL OR so_det.cancel != 'Y')");
            if ($filter['order']) $orderOutputsSql->where("act_costing.id", $filter['order']);
            if ($filter['buyer']) $orderOutputsSql->where("act_costing.id_buyer", $filter['buyer']);
            if ($filter['dateFrom']) $orderOutputsSql->where('rfts.tanggal', '>=', $filter['dateFrom']);
            if ($filter['dateTo']) $orderOutputsSql->where('rfts.tanggal', '<=', $filter['dateTo']);
            if ($filter['color']) $orderOutputsSql->where('rfts.color', $filter['color']);
            if ($filter['line']) $orderOutputsSql->whereRaw('COALESCE(rfts.created_by, master_plan.sewing_line) = ?', [$filter['line']]);
            if ($filter['groupBy'] == "size" && $filter['size']) $orderOutputsSql->where('so_det.size', $filter['size']);
            $orderOutputsSql->
                groupByRaw("rfts.act_id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.created_by, master_plan.sewing_line), rfts.tanggal ".($filter['groupBy'] == 'size' ? ', so_det.size' : '')."")->
                orderBy("rfts.act_id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("rfts.color", "asc")->
                orderByRaw("COALESCE(rfts.created_by, master_plan.sewing_line) asc ".($filter['groupBy'] == 'size' ? ', so_det.id asc' : ''));

        return $orderOutputsSql->get();
    }

    /**
     * Susun output per baris (ws|style|color|line|size) dan per tanggal untuk tabel
     */
    public function aggregate($orderOutputs, $groupBy): array
    {
        $useSize = $groupBy === 'size';

        // Dates
        $dates = $orderOutputs
            ->pluck('tanggal')
            ->unique()
            ->sort()
            ->values();

        // Content
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
            "dates" => $dates,
            "outputMap" => $outputMap,
            "rowTotals" => $rowTotals,
            "dateTotals" => $dateTotals,
            "grandTotal" => $grandTotal,
        ];
    }

    protected function normalizeFilter(array $filter): array
    {
        $filter = array_merge([
            'dateFrom' => date('Y-m-d'),
            'dateTo' => date('Y-m-d'),
            'outputType' => '',
            'groupBy' => '',
            'order' => null,
            'buyer' => null,
            'color' => null,
            'line' => null,
            'size' => null,
        ], array_filter($filter, function ($value) {
            return $value !== null && $value !== '';
        }));

        $filter['outputType'] = self::normalizeOutputType($filter['outputType']);
        $filter['groupBy'] = $filter['groupBy'] === 'size' ? 'size' : '';

        return $filter;
    }

    /**
     * Join ke userpassword untuk nama line, beda kolom per tipe output
     */
    protected function userJoin($table, $outputType)
    {
        if ($outputType == "_packing_po") {
            return "LEFT JOIN userpassword ON userpassword.username = ".$table.".created_by_line";
        }

        if ($outputType == "_packing") {
            return "LEFT JOIN userpassword ON userpassword.username = ".$table.".created_by";
        }

        return "LEFT JOIN user_sb_wip ON user_sb_wip.id = ".$table.".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id";
    }
}
