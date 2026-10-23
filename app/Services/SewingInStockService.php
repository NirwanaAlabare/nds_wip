<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Perhitungan stok Sewing In per line.
 *
 * - WIP set (per WS + color + size):
 *   saldo = saldo_akhir closing terakhir + Sewing In + mutasi masuk − mutasi keluar − output_rfts
 * - Stocker outstanding: sisa qty stocker (qty − SUM qty_act Sewing In),
 *   lokasinya = line dari kejadian terakhir (Sewing In / mutasi stocker).
 */
class SewingInStockService
{
    // Pemisah key kombinasi WS|color|size
    const SEP = "\x1F";

    public static function comboKey($ws, $color, $size)
    {
        return $ws.self::SEP.$color.self::SEP.$size;
    }

    /**
     * Tanggal closing terakhir line (<= $tanggal), null kalau belum pernah closing.
     */
    public function lastClosingDate($lineId, $tanggal)
    {
        return DB::table("sewing_in_closing")->
            where("line_id", $lineId)->
            where("tanggal", "<=", $tanggal)->
            max("tanggal");
    }

    /**
     * Line sudah closing di tanggal ini atau sesudahnya (transaksi tanggal tsb terkunci).
     */
    public function isClosed($lineId, $tanggal)
    {
        return DB::table("sewing_in_closing")->
            where("line_id", $lineId)->
            where("tanggal", ">=", $tanggal)->
            exists();
    }

    /**
     * Stok WIP set line per WS + color + size sampai akhir $tanggal.
     *
     * @return \Illuminate\Support\Collection [comboKey => object]
     */
    public function lineStock($lineId, $tanggal)
    {
        $closingDate = $this->lastClosingDate($lineId, $tanggal);

        $stock = collect();

        // Saldo awal dari closing terakhir (baris penanda '' diabaikan)
        if ($closingDate) {
            DB::table("sewing_in_closing")->
                where("line_id", $lineId)->
                where("tanggal", $closingDate)->
                where("act_costing_ws", "!=", "")->
                get()->
                each(function ($row) use ($stock) {
                    $this->addQty($stock, $row->act_costing_ws, $row->color, $row->size, "saldo_awal", $row->saldo_akhir);
                });
        }

        // Transaksi setelah closing terakhir sampai $tanggal
        $movements = $this->movements($lineId, $closingDate, $tanggal, $stock->keys()->all());

        foreach ($movements as $key => $movement) {
            foreach (["qty_sewing_in", "qty_mutasi_in", "qty_mutasi_out", "qty_output"] as $field) {
                $this->addQty($stock, $movement->act_costing_ws, $movement->color, $movement->size, $field, $movement->$field);
            }
        }

        return $stock->each(function ($row) use ($closingDate) {
            $row->closing_terakhir = $closingDate;
            $row->saldo_akhir = $row->saldo_awal + $row->qty_sewing_in + $row->qty_mutasi_in - $row->qty_mutasi_out - $row->qty_output;
        })->sortBy(fn ($row) => [$row->act_costing_ws, $row->color, $row->size]);
    }

    /**
     * Hitungan closing line untuk 1 tanggal:
     * saldo_awal = stok sampai kemarin (closing terakhir + transaksi yang belum di-closing),
     * lalu transaksi tanggal tsb, saldo_akhir = awal + in + mutasi_in − mutasi_out − output.
     *
     * @return \Illuminate\Support\Collection [comboKey => object]
     */
    public function closingPreview($lineId, $tanggal)
    {
        $kemarin = Carbon::parse($tanggal)->subDay()->format("Y-m-d");

        $previous = $this->lineStock($lineId, $kemarin);
        $movements = $this->movements($lineId, $kemarin, $tanggal, $previous->keys()->all());

        $rows = collect();

        foreach ($previous as $key => $row) {
            $this->addQty($rows, $row->act_costing_ws, $row->color, $row->size, "saldo_awal", $row->saldo_akhir);
        }

        foreach ($movements as $key => $movement) {
            foreach (["qty_sewing_in", "qty_mutasi_in", "qty_mutasi_out", "qty_output"] as $field) {
                $this->addQty($rows, $movement->act_costing_ws, $movement->color, $movement->size, $field, $movement->$field);
            }
        }

        return $rows->each(function ($row) {
            $row->saldo_akhir = $row->saldo_awal + $row->qty_sewing_in + $row->qty_mutasi_in - $row->qty_mutasi_out - $row->qty_output;
        })->filter(fn ($row) => $row->saldo_awal != 0 || $row->qty_sewing_in != 0 || $row->qty_mutasi_in != 0 || $row->qty_mutasi_out != 0 || $row->qty_output != 0)->
            sortBy(fn ($row) => [$row->act_costing_ws, $row->color, $row->size]);
    }

    /**
     * Pergerakan stok line per WS + color + size dengan tanggal > $setelah (null = dari awal) dan <= $sampai.
     *
     * Output hanya dihitung untuk kombinasi yang memang ada di line (punya saldo closing / Sewing In / mutasi masuk),
     * dan kalau belum pernah closing, mulai dari tanggal kejadian pertama kombinasi tsb di line (go-live otomatis).
     *
     * @return \Illuminate\Support\Collection [comboKey => object]
     */
    public function movements($lineId, $setelah, $sampai, array $closingKeys = [])
    {
        $rows = collect();

        // Sewing In
        $sewingIn = DB::table("sewing_in")->
            selectRaw("act_costing_ws, color, size, MIN(tanggal) as first_date, SUM(qty_set) as qty")->
            where("line_id", $lineId)->
            where("cancel", "N")->
            when($setelah, fn ($query) => $query->where("tanggal", ">", $setelah))->
            where("tanggal", "<=", $sampai)->
            groupBy("act_costing_ws", "color", "size")->
            get();

        foreach ($sewingIn as $item) {
            $this->addQty($rows, $item->act_costing_ws, $item->color, $item->size, "qty_sewing_in", $item->qty, $item->first_date);
        }

        // Mutasi set masuk & keluar
        $mutasi = DB::table("sewing_in_mutasi")->
            selectRaw("
                act_costing_ws, color, size,
                MIN(CASE WHEN line_id_tujuan = ? THEN tanggal END) as first_date,
                SUM(CASE WHEN line_id_tujuan = ? THEN qty ELSE 0 END) as qty_in,
                SUM(CASE WHEN line_id_asal = ? THEN qty ELSE 0 END) as qty_out
            ", [$lineId, $lineId, $lineId])->
            where("tipe", "set")->
            where("cancel", "N")->
            where(fn ($query) => $query->where("line_id_tujuan", $lineId)->orWhere("line_id_asal", $lineId))->
            when($setelah, fn ($query) => $query->where("tanggal", ">", $setelah))->
            where("tanggal", "<=", $sampai)->
            groupBy("act_costing_ws", "color", "size")->
            get();

        foreach ($mutasi as $item) {
            $this->addQty($rows, $item->act_costing_ws, $item->color, $item->size, "qty_mutasi_in", $item->qty_in, $item->first_date);
            $this->addQty($rows, $item->act_costing_ws, $item->color, $item->size, "qty_mutasi_out", $item->qty_out);
        }

        // Kombinasi yang dihitung output-nya + tanggal mulai
        $startDates = [];

        foreach ($closingKeys as $key) {
            $startDates[$key] = $setelah ? Carbon::parse($setelah)->addDay()->format("Y-m-d") : null;
        }

        foreach ($rows as $key => $row) {
            if (!array_key_exists($key, $startDates)) {
                $startDates[$key] = $this->firstEventDate($lineId, $row->act_costing_ws, $row->color, $row->size) ?? $row->first_date;
            }
        }

        if (count($startDates) > 0) {
            $minStart = collect($startDates)->filter()->min();
            $dari = $setelah ? Carbon::parse($setelah)->addDay()->format("Y-m-d") : $minStart;

            foreach ($this->outputPerDay($lineId, $dari, $sampai, collect($startDates)->keys()) as $output) {
                $key = self::comboKey($output->ws, $output->color, $output->size);

                // Hanya kombinasi yang ada di line, mulai dari tanggal go-live-nya
                if (!array_key_exists($key, $startDates) || ($startDates[$key] && $output->tanggal < $startDates[$key])) {
                    continue;
                }

                $this->addQty($rows, $output->ws, $output->color, $output->size, "qty_output", $output->qty);
            }
        }

        return $rows;
    }

    /**
     * Tanggal kejadian pertama kombinasi di line (Sewing In / mutasi masuk), untuk go-live output.
     */
    private function firstEventDate($lineId, $ws, $color, $size)
    {
        $sewingIn = DB::table("sewing_in")->
            where("line_id", $lineId)->where("cancel", "N")->
            where("act_costing_ws", $ws)->where("color", $color)->where("size", $size)->
            min("tanggal");

        $mutasi = DB::table("sewing_in_mutasi")->
            where("line_id_tujuan", $lineId)->where("tipe", "set")->where("cancel", "N")->
            where("act_costing_ws", $ws)->where("color", $color)->where("size", $size)->
            min("tanggal");

        return collect([$sewingIn, $mutasi])->filter()->min();
    }

    /**
     * Output (output_rfts, NORMAL + REWORK) line per WS + color + size per hari.
     */
    private function outputPerDay($lineId, $dari, $sampai, $comboKeys)
    {
        if (!$dari || $comboKeys->count() < 1) {
            return [];
        }

        $wsList = $comboKeys->map(fn ($key) => explode(self::SEP, $key)[0])->unique()->values()->all();

        return DB::connection("mysql_sb")->table("output_rfts")->
            selectRaw("act_costing.kpno as ws, so_det.color, so_det.size, DATE(output_rfts.created_at) as tanggal, COUNT(output_rfts.id) as qty")->
            join("user_sb_wip", "user_sb_wip.id", "=", "output_rfts.created_by")->
            join("so_det", "so_det.id", "=", "output_rfts.so_det_id")->
            join("so", "so.id", "=", "so_det.id_so")->
            join("act_costing", "act_costing.id", "=", "so.id_cost")->
            where("user_sb_wip.line_id", $lineId)->
            whereIn("act_costing.kpno", $wsList)->
            where("output_rfts.created_at", ">=", $dari." 00:00:00")->
            where("output_rfts.created_at", "<=", $sampai." 23:59:59")->
            groupBy("act_costing.kpno", "so_det.color", "so_det.size", DB::raw("DATE(output_rfts.created_at)"))->
            get();
    }

    /**
     * Stocker outstanding (sisa > 0) beserta lokasi line terakhirnya.
     * $lineId null = semua line. $stockerIds = batasi ke stocker tertentu.
     * $sampai = hitung per akhir tanggal tsb (untuk closing), null = saat ini.
     *
     * @return \Illuminate\Support\Collection [stocker_id => object]
     */
    public function outstandingStockers($lineId = null, ?array $stockerIds = null, $sampai = null)
    {
        if ($stockerIds !== null && count($stockerIds) < 1) {
            return collect();
        }

        $stockers = collect(DB::select("
            SELECT
                stocker_input.id as stocker_id,
                stocker_input.id_qr_stocker,
                stocker_input.act_costing_ws,
                stocker_input.color,
                stocker_input.size,
                stocker_input.part_detail_id,
                master_part.nama_part,
                COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) as qty_stocker,
                used.qty_used,
                COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) - used.qty_used as qty_sisa
            FROM
                (
                    SELECT sewing_in_detail.stocker_id, SUM(sewing_in_detail.qty_act) as qty_used
                    FROM sewing_in_detail
                    JOIN sewing_in ON sewing_in.id = sewing_in_detail.sewing_in_id
                    WHERE sewing_in.cancel = 'N'
                    ".($stockerIds !== null ? "AND sewing_in_detail.stocker_id IN (".implode(",", array_map("intval", $stockerIds)).")" : "")."
                    ".($sampai ? "AND sewing_in.tanggal <= ".DB::getPdo()->quote($sampai) : "")."
                    GROUP BY sewing_in_detail.stocker_id
                ) used
                JOIN stocker_input ON stocker_input.id = used.stocker_id
                LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
            WHERE
                COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) - used.qty_used > 0
        "))->keyBy("stocker_id");

        $locations = $this->stockerLocations($stockers->keys()->all(), $sampai);

        return $stockers->each(function ($stocker) use ($locations) {
            $location = $locations[$stocker->stocker_id] ?? null;

            $stocker->line_id = $location->line_id ?? null;
            $stocker->nama_line = $location->nama_line ?? null;
            $stocker->last_at = $location->last_at ?? null;
        })->filter(fn ($stocker) => $lineId === null || $stocker->line_id == $lineId)->
            sortByDesc("last_at");
    }

    /**
     * Lokasi line terakhir per stocker = kejadian terakhir (Sewing In / mutasi stocker yang tidak cancel).
     *
     * @return array [stocker_id => object {line_id, nama_line, last_at}]
     */
    public function stockerLocations(array $stockerIds, $sampai = null)
    {
        if (count($stockerIds) < 1) {
            return [];
        }

        $events = DB::table("sewing_in_detail")->
            selectRaw("sewing_in_detail.stocker_id, sewing_in.line_id, sewing_in.nama_line, sewing_in.created_at as last_at, 0 as urutan, sewing_in_detail.id as event_id")->
            join("sewing_in", "sewing_in.id", "=", "sewing_in_detail.sewing_in_id")->
            where("sewing_in.cancel", "N")->
            whereIn("sewing_in_detail.stocker_id", $stockerIds)->
            when($sampai, fn ($query) => $query->where("sewing_in.tanggal", "<=", $sampai))->
            unionAll(
                DB::table("sewing_in_mutasi")->
                    selectRaw("stocker_id, line_id_tujuan as line_id, nama_line_tujuan as nama_line, created_at as last_at, 1 as urutan, id as event_id")->
                    where("tipe", "stocker")->
                    where("cancel", "N")->
                    whereIn("stocker_id", $stockerIds)->
                    when($sampai, fn ($query) => $query->where("tanggal", "<=", $sampai))
            )->
            get();

        // Kejadian terakhir; kalau waktunya sama, mutasi (urutan 1) dianggap sesudah Sewing In
        return $events->sortBy(fn ($event) => [$event->last_at, $event->urutan, $event->event_id])->
            keyBy("stocker_id")->
            all();
    }

    private function addQty($collection, $ws, $color, $size, $field, $qty, $firstDate = null)
    {
        $key = self::comboKey($ws, $color, $size);

        if (!$collection->has($key)) {
            $collection->put($key, (object) [
                "act_costing_ws" => $ws,
                "color" => $color,
                "size" => $size,
                "saldo_awal" => 0,
                "qty_sewing_in" => 0,
                "qty_mutasi_in" => 0,
                "qty_mutasi_out" => 0,
                "qty_output" => 0,
                "first_date" => null,
            ]);
        }

        $row = $collection->get($key);
        $row->$field += intval($qty);

        if ($firstDate && (!$row->first_date || $firstDate < $row->first_date)) {
            $row->first_date = $firstDate;
        }
    }
}
