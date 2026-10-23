<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\UserLine;
use App\Models\Stocker\Stocker;
use App\Services\SewingInStockService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;

class SewingInController extends Controller
{
    protected $stock;

    public function __construct(SewingInStockService $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Sewing In.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lines = UserLine::where("Groupp", "SEWING")->whereRaw("(Locked is null OR Locked != 1)")->orderBy("line_id", 'asc')->get();

        return view("sewing.sewing-in", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-packing-in", "subPage" => "sewing-in"], ["lines" => $lines]);
    }

    /**
     * Ambil data stocker (join part_detail) beserta list part 1 set (part_detail dari part yang sama dengan stocker).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanStocker(Request $request)
    {
        $stocker = $this->stockerQuery()->
            where("stocker_input.id_qr_stocker", $request->kode_stocker)->
            first();

        if (!$stocker) {
            return response()->json([
                "status" => 400,
                "message" => "Stocker '".$request->kode_stocker."' tidak ditemukan",
            ]);
        }

        // Sisa qty stocker = qty stocker - qty act yang sudah masuk Sewing In
        $stocker->qty_used = $this->usedQty([$stocker->id_qr_stocker])->get($stocker->id_qr_stocker, 0);
        $stocker->qty_sisa = $stocker->qty - $stocker->qty_used;

        if ($stocker->qty_sisa <= 0) {
            return response()->json([
                "status" => 400,
                "message" => "Stocker '".$stocker->id_qr_stocker."' sudah habis dipakai (".$stocker->qty_used." / ".$stocker->qty.")",
            ]);
        }

        if ($request->line_id) {
            // Line sudah closing hari ini, tidak bisa Sewing In lagi
            if ($this->stock->isClosed($request->line_id, date("Y-m-d"))) {
                return response()->json(["status" => 400, "message" => "Line ini sudah closing hari ini, tidak bisa Sewing In lagi"]);
            }

            // Sisa stocker hanya bisa dipakai di line lokasinya (setelah Sewing In / mutasi terakhir)
            $location = $this->wrongLocation([$stocker->stocker_id], $request->line_id)->first();

            if ($location) {
                return response()->json(["status" => 400, "message" => "Sisa stocker '".$stocker->id_qr_stocker."' ada di ".$location->nama_line.", pindahkan dulu lewat Mutasi Line"]);
            }
        }

        $parts = $this->partSet($stocker->part_id);

        return response()->json([
            "status" => 200,
            "stocker" => $stocker,
            "style" => collect($parts)->pluck("style")->filter()->first(),
            "parts" => $parts,
        ]);
    }

    /**
     * Riwayat Sewing In per rentang tanggal (dan line kalau dipilih), beserta detail stocker.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            "tanggal_awal" => "required|date",
            "tanggal_akhir" => "required|date|after_or_equal:tanggal_awal",
        ], [
            "tanggal_akhir.after_or_equal" => "Tanggal sampai harus sama atau setelah tanggal dari",
        ]);

        $headers = DB::table("sewing_in")->
            whereBetween("tanggal", [$request->tanggal_awal, $request->tanggal_akhir])->
            when($request->line_id, fn ($query) => $query->where("line_id", $request->line_id))->
            orderBy("id", "desc")->
            get();

        $details = DB::table("sewing_in_detail")->
            select(
                "sewing_in_detail.sewing_in_id",
                "sewing_in_detail.id_qr_stocker",
                "sewing_in_detail.so_det_id",
                "sewing_in_detail.qty_stocker",
                "sewing_in_detail.qty_act",
                "master_part.nama_part"
            )->
            leftJoin("part_detail", "part_detail.id", "=", "sewing_in_detail.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            whereIn("sewing_in_detail.sewing_in_id", $headers->pluck("id"))->
            orderBy("sewing_in_detail.id")->
            get()->
            groupBy("sewing_in_id");

        $headers->each(function ($header) use ($details) {
            $header->details = $details->get($header->id, collect())->values();
        });

        $active = $headers->where("cancel", "N");

        return response()->json([
            "status" => 200,
            "data" => $headers->values(),
            "total_set" => $active->count(),
            "total_qty" => $active->sum("qty_set"),
        ]);
    }

    /**
     * Stocker outstanding: masih ada sisa qty & lokasi terakhirnya (Sewing In / mutasi) di line terpilih.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function outstanding(Request $request)
    {
        $data = $this->stock->outstandingStockers($request->line_id ?: null)->values()->map(fn ($stocker) => [
            "id_qr_stocker" => $stocker->id_qr_stocker,
            "act_costing_ws" => $stocker->act_costing_ws,
            "color" => $stocker->color,
            "size" => $stocker->size,
            "nama_part" => $stocker->nama_part,
            "qty" => $stocker->qty_stocker,
            "qty_used" => $stocker->qty_used,
            "qty_sisa" => $stocker->qty_sisa,
            "nama_line" => $stocker->nama_line,
            "last_at" => $stocker->last_at,
        ]);

        return response()->json([
            "status" => 200,
            "data" => $data,
            "total_stocker" => $data->count(),
            "total_sisa" => $data->sum("qty_sisa"),
        ]);
    }

    /**
     * Simpan 1 set Sewing In (header + detail per stocker).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "line_id" => "required",
            "stockers" => "required|array|min:1",
            "stockers.*.id_qr_stocker" => "required|string",
            "stockers.*.qty_act" => "required|integer|min:1",
        ]);

        $line = UserLine::where("line_id", $request->line_id)->first();

        if (!$line) {
            return response()->json(["status" => 400, "message" => "Line tidak ditemukan"]);
        }

        // Urutan stockers dari halaman: index 0 = stocker pertama (patokan)
        $qtyActs = collect($request->stockers)->pluck("qty_act", "id_qr_stocker")->map(fn ($qty) => intval($qty));
        $kodeStockers = $qtyActs->keys()->all();

        if (count($kodeStockers) != count($request->stockers)) {
            return response()->json(["status" => 400, "message" => "Ada stocker yang di-scan dobel"]);
        }

        // Simpan bergantian (antri) supaya kode & sisa stocker tidak bentrok saat Apply bersamaan
        $locked = DB::selectOne("SELECT GET_LOCK('sewing_in_store', 10) as locked")->locked;

        if (!$locked) {
            // Halaman akan otomatis mencoba lagi (tetap menunggu, tidak gagal)
            return response()->json(["status" => 409, "busy" => true, "message" => "Menunggu antrian simpan"]);
        }

        try {
            return $this->storeTransaction($line, $kodeStockers, $qtyActs);
        } finally {
            DB::select("SELECT RELEASE_LOCK('sewing_in_store')");
        }
    }

    /**
     * Transaksi simpan Sewing In, diulang kalau kode bentrok (unique) / deadlock.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function storeTransaction($line, $kodeStockers, $qtyActs, $attempt = 1)
    {
        try {
            return DB::transaction(fn () => $this->storeSet($line, $kodeStockers, $qtyActs), 3);
        } catch (QueryException $e) {
            // 1062 = duplicate entry (kode sudah dipakai)
            if (($e->errorInfo[1] ?? null) == 1062 && $attempt < 3) {
                return $this->storeTransaction($line, $kodeStockers, $qtyActs, $attempt + 1);
            }

            throw $e;
        }
    }

    /**
     * Validasi ulang & simpan header + detail (dijalankan di dalam transaksi).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function storeSet($line, $kodeStockers, $qtyActs)
    {
        // Line sudah closing hari ini, transaksi hari ini terkunci
        if ($this->stock->isClosed($line->line_id, date("Y-m-d"))) {
            return response()->json(["status" => 400, "message" => $line->FullName." sudah closing hari ini, tidak bisa Sewing In lagi"]);
        }

        // Kunci baris stocker supaya tidak dipakai bersamaan dari tablet lain
        Stocker::whereIn("id_qr_stocker", $kodeStockers)->lockForUpdate()->get();

        $stockers = $this->stockerQuery()->
            whereIn("stocker_input.id_qr_stocker", $kodeStockers)->
            get()->
            keyBy("id_qr_stocker");

        $notFound = collect($kodeStockers)->diff($stockers->keys());

        if ($notFound->count() > 0) {
            return response()->json(["status" => 400, "message" => "Stocker tidak ditemukan: ".$notFound->implode(", ")]);
        }

        // Sisa qty per stocker (dihitung ulang setelah baris stocker dikunci)
        $usedQty = $this->usedQty($kodeStockers);

        $stockers->each(function ($item) use ($usedQty) {
            $item->qty_sisa = $item->qty - $usedQty->get($item->id_qr_stocker, 0);
        });

        // Sisa stocker hanya bisa dipakai di line lokasinya
        $wrongLocation = $this->wrongLocation($stockers->pluck("stocker_id")->all(), $line->line_id);

        if ($wrongLocation->count() > 0) {
            return response()->json(["status" => 400, "message" => "Sisa stocker ada di line lain, pindahkan dulu lewat Mutasi Line: ".$wrongLocation->map(fn ($item) => $item->id_qr_stocker." (".$item->nama_line.")")->implode(", ")]);
        }

        // WS, color & size harus sama dengan stocker pertama
        $first = $stockers[$kodeStockers[0]];

        $different = $stockers->filter(fn ($item) => $item->act_costing_ws != $first->act_costing_ws || $item->color != $first->color || $item->size != $first->size);

        if ($different->count() > 0) {
            return response()->json(["status" => 400, "message" => "WS / Color / Size berbeda dengan stocker pertama: ".$different->keys()->implode(", ")]);
        }

        // Semua part 1 set harus terisi, 1 part 1 stocker
        $parts = collect($this->partSet($first->part_id));
        $partIds = $parts->pluck("part_detail_id");
        $stockerPartIds = $stockers->pluck("part_detail_id");

        if ($stockerPartIds->diff($partIds)->count() > 0) {
            return response()->json(["status" => 400, "message" => "Ada stocker yang part-nya tidak termasuk set ini"]);
        }

        if ($stockerPartIds->duplicates()->count() > 0) {
            return response()->json(["status" => 400, "message" => "Ada part yang terisi lebih dari 1 stocker"]);
        }

        if ($partIds->diff($stockerPartIds)->count() > 0) {
            return response()->json(["status" => 400, "message" => "Part 1 set belum lengkap (".$stockerPartIds->count()." / ".$partIds->count().")"]);
        }

        // Qty act maksimal sisa qty stocker
        $overQty = $stockers->filter(fn ($item) => $qtyActs[$item->id_qr_stocker] > $item->qty_sisa);

        if ($overQty->count() > 0) {
            return response()->json(["status" => 400, "message" => "Qty act melebihi sisa stocker: ".$overQty->map(fn ($item) => $item->id_qr_stocker." (sisa ".max($item->qty_sisa, 0).")")->implode(", ")]);
        }

        // Qty 1 set = qty act terkecil
        $qtySet = $qtyActs->min();

        $now = now();
        $kode = $this->generateKode();

        $sewingInId = DB::table("sewing_in")->insertGetId([
            "kode" => $kode,
            "tanggal" => $now->format("Y-m-d"),
            "line_id" => $line->line_id,
            "nama_line" => $line->username,
            "part_id" => $first->part_id,
            "act_costing_ws" => $first->act_costing_ws,
            "style" => $parts->pluck("style")->filter()->first(),
            "color" => $first->color,
            "size" => $first->size,
            "total_part" => $parts->count(),
            "qty_set" => $qtySet,
            "cancel" => "N",
            "created_by" => auth()->id(),
            "created_by_username" => auth()->user() ? auth()->user()->username : null,
            "created_at" => $now,
            "updated_at" => $now,
        ]);

        DB::table("sewing_in_detail")->insert(
            collect($kodeStockers)->map(fn ($kodeStocker) => [
                "sewing_in_id" => $sewingInId,
                "stocker_id" => $stockers[$kodeStocker]->stocker_id,
                "id_qr_stocker" => $kodeStocker,
                "part_detail_id" => $stockers[$kodeStocker]->part_detail_id,
                "so_det_id" => $stockers[$kodeStocker]->so_det_id,
                "qty_stocker" => $stockers[$kodeStocker]->qty, // qty awal stocker
                "qty_act" => $qtySet, // qty terpakai per stocker = qty 1 set
                "created_at" => $now,
                "updated_at" => $now,
            ])->all()
        );

        return response()->json([
            "status" => 200,
            "message" => "Sewing In ".$kode." berhasil disimpan",
            "kode" => $kode,
        ]);
    }

    /**
     * Query data stocker (belum di-cancel) beserta part-nya.
     */
    private function stockerQuery()
    {
        return Stocker::selectRaw("
                stocker_input.id as stocker_id,
                stocker_input.id_qr_stocker,
                stocker_input.act_costing_ws,
                stocker_input.color,
                stocker_input.size,
                stocker_input.so_det_id,
                stocker_input.panel,
                stocker_input.part_detail_id,
                part_detail.part_id,
                master_part.nama_part,
                CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) as stocker_range,
                COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) as qty
            ")->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            whereRaw("(stocker_input.cancel IS NULL OR stocker_input.cancel != 'Y')");
    }

    /**
     * List part 1 set, panel complement diambil dari part asalnya.
     */
    private function partSet($partId)
    {
        return DB::select("
            SELECT
                part_detail.id as part_detail_id,
                part.style,
                ( CASE WHEN part_detail.part_status = 'complement' THEN COALESCE ( part_com.panel, part.panel ) ELSE part.panel END ) panel,
                master_part.nama_part,
                part_detail.part_status,
                part_detail.tujuan
            FROM
                part_detail
                LEFT JOIN part ON part.id = part_detail.part_id
                LEFT JOIN part_detail part_detail_com ON part_detail_com.id = part_detail.from_part_detail AND part_detail.part_status = 'complement'
                LEFT JOIN part part_com ON part_com.id = part_detail_com.part_id
                LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
            WHERE
                part.id <=> ? AND
                ( part_detail.status = 'active' OR part_detail.status IS NULL )
        ", [$partId]);
    }

    /**
     * Stocker yang lokasi terakhirnya (Sewing In / mutasi) bukan di line ini.
     * Stocker yang belum pernah dipakai tidak punya lokasi, jadi boleh di line mana pun.
     *
     * @return \Illuminate\Support\Collection
     */
    private function wrongLocation(array $stockerIds, $lineId)
    {
        $locations = $this->stock->stockerLocations($stockerIds);

        if (count($locations) < 1) {
            return collect();
        }

        $kode = DB::table("stocker_input")->whereIn("id", array_keys($locations))->pluck("id_qr_stocker", "id");

        return collect($locations)->
            filter(fn ($location) => $location->line_id != $lineId)->
            map(fn ($location) => (object) [
                "id_qr_stocker" => $kode[$location->stocker_id] ?? $location->stocker_id,
                "nama_line" => $location->nama_line,
            ])->
            values();
    }

    /**
     * Total qty act per stocker yang sudah masuk Sewing In (header belum di-cancel).
     *
     * @return \Illuminate\Support\Collection [id_qr_stocker => qty]
     */
    private function usedQty(array $kodeStockers)
    {
        return DB::table("sewing_in_detail")->
            selectRaw("sewing_in_detail.id_qr_stocker, SUM(sewing_in_detail.qty_act) as qty_used")->
            join("sewing_in", "sewing_in.id", "=", "sewing_in_detail.sewing_in_id")->
            whereIn("sewing_in_detail.id_qr_stocker", $kodeStockers)->
            where("sewing_in.cancel", "N")->
            groupBy("sewing_in_detail.id_qr_stocker")->
            pluck("qty_used", "id_qr_stocker")->
            map(fn ($qty) => intval($qty));
    }

    /**
     * Kode Sewing In: SWI-yymm-00001 (reset tiap bulan).
     */
    private function generateKode()
    {
        $prefix = "SWI-".date("ym")."-";

        $lastKode = DB::table("sewing_in")->
            where("kode", "like", $prefix."%")->
            orderBy("kode", "desc")->
            lockForUpdate()->
            value("kode");

        $number = $lastKode ? intval(substr($lastKode, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad($number, 5, "0", STR_PAD_LEFT);
    }
}
