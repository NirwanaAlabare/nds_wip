<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\UserLine;
use App\Services\SewingInStockService;
use Illuminate\Http\Request;
use DB;

class SewingInMutasiController extends Controller
{
    protected $stock;

    public function __construct(SewingInStockService $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Mutasi (pindah) line: stok WIP set & stocker outstanding pindah ke line lain.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lines = UserLine::where("Groupp", "SEWING")->whereRaw("(Locked is null OR Locked != 1)")->orderBy("line_id", 'asc')->get();

        return view("sewing.sewing-in-mutasi", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-packing-in", "subPage" => "sewing-in-mutasi"], ["lines" => $lines]);
    }

    /**
     * Stok line saat ini per WS + color + size, beserta stocker outstanding-nya.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stock(Request $request)
    {
        $request->validate(["line_id" => "required"]);

        return response()->json([
            "status" => 200,
            "closed" => $this->stock->isClosed($request->line_id, date("Y-m-d")),
            "data" => $this->groupStock($request->line_id)->values(),
        ]);
    }

    /**
     * Riwayat mutasi per rentang tanggal (dan line asal/tujuan kalau dipilih).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            "tanggal_awal" => "required|date",
            "tanggal_akhir" => "required|date|after_or_equal:tanggal_awal",
        ]);

        $rows = DB::table("sewing_in_mutasi")->
            whereBetween("tanggal", [$request->tanggal_awal, $request->tanggal_akhir])->
            when($request->line_id, fn ($query) => $query->where(fn ($q) => $q->where("line_id_asal", $request->line_id)->orWhere("line_id_tujuan", $request->line_id)))->
            orderBy("id", "desc")->
            get();

        $data = $rows->groupBy("kode")->map(function ($items) {
            $first = $items->first();

            return [
                "kode" => $first->kode,
                "tanggal" => $first->tanggal,
                "created_at" => $first->created_at,
                "nama_line_asal" => $first->nama_line_asal,
                "nama_line_tujuan" => $first->nama_line_tujuan,
                "keterangan" => $first->keterangan,
                "cancel" => $first->cancel,
                "created_by_username" => $first->created_by_username,
                "qty_set" => $items->where("tipe", "set")->sum("qty"),
                "total_stocker" => $items->where("tipe", "stocker")->count(),
                "qty_stocker" => $items->where("tipe", "stocker")->sum("qty"),
                "items" => $items->values(),
            ];
        })->values();

        return response()->json(["status" => 200, "data" => $data]);
    }

    /**
     * Simpan mutasi: kelompok WS + color + size yang dipilih pindah semua (WIP set & stocker outstanding).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "line_id_asal" => "required",
            "line_id_tujuan" => "required|different:line_id_asal",
            "items" => "required|array|min:1",
            "items.*.act_costing_ws" => "required|string",
            "items.*.color" => "required|string",
            "items.*.size" => "required|string",
            "keterangan" => "nullable|string|max:255",
        ], [
            "line_id_tujuan.different" => "Line tujuan harus berbeda dengan line asal",
            "items.required" => "Pilih minimal 1 WS / color / size yang mau dipindah",
        ]);

        $lineAsal = UserLine::where("line_id", $request->line_id_asal)->first();
        $lineTujuan = UserLine::where("line_id", $request->line_id_tujuan)->first();

        if (!$lineAsal || !$lineTujuan) {
            return response()->json(["status" => 400, "message" => "Line tidak ditemukan"]);
        }

        // Antri dengan Sewing In supaya stok yang dihitung tidak berubah di tengah proses
        $locked = DB::selectOne("SELECT GET_LOCK('sewing_in_store', 10) as locked")->locked;

        if (!$locked) {
            return response()->json(["status" => 409, "busy" => true, "message" => "Menunggu antrian simpan"]);
        }

        try {
            return DB::transaction(fn () => $this->storeMutasi($request, $lineAsal, $lineTujuan), 3);
        } finally {
            DB::select("SELECT RELEASE_LOCK('sewing_in_store')");
        }
    }

    private function storeMutasi(Request $request, $lineAsal, $lineTujuan)
    {
        $today = date("Y-m-d");

        foreach ([$lineAsal, $lineTujuan] as $line) {
            if ($this->stock->isClosed($line->line_id, $today)) {
                return response()->json(["status" => 400, "message" => $line->FullName." sudah closing, tidak bisa mutasi hari ini"]);
            }
        }

        // Stok dihitung ulang di server, tidak pakai angka dari halaman
        $stock = $this->groupStock($lineAsal->line_id);

        $now = now();
        $kode = $this->generateKode();
        $rows = [];

        $base = [
            "kode" => $kode,
            "tanggal" => $today,
            "line_id_asal" => $lineAsal->line_id,
            "nama_line_asal" => $lineAsal->username,
            "line_id_tujuan" => $lineTujuan->line_id,
            "nama_line_tujuan" => $lineTujuan->username,
            "keterangan" => $request->keterangan,
            "cancel" => "N",
            "created_by" => auth()->id(),
            "created_by_username" => auth()->user() ? auth()->user()->username : null,
            "created_at" => $now,
            "updated_at" => $now,
        ];

        foreach ($request->items as $item) {
            $group = $stock->get(SewingInStockService::comboKey($item["act_costing_ws"], $item["color"], $item["size"]));

            if (!$group) {
                continue;
            }

            // WIP set (hanya yang masih ada saldo)
            if ($group->saldo_akhir > 0) {
                $rows[] = $base + [
                    "tipe" => "set",
                    "act_costing_ws" => $group->act_costing_ws,
                    "color" => $group->color,
                    "size" => $group->size,
                    "stocker_id" => null,
                    "id_qr_stocker" => null,
                    "qty" => $group->saldo_akhir,
                ];
            }

            // Stocker outstanding ikut pindah
            foreach ($group->stockers as $stocker) {
                $rows[] = $base + [
                    "tipe" => "stocker",
                    "act_costing_ws" => $stocker->act_costing_ws,
                    "color" => $stocker->color,
                    "size" => $stocker->size,
                    "stocker_id" => $stocker->stocker_id,
                    "id_qr_stocker" => $stocker->id_qr_stocker,
                    "qty" => $stocker->qty_sisa,
                ];
            }
        }

        if (count($rows) < 1) {
            return response()->json(["status" => 400, "message" => "Tidak ada stok yang bisa dipindah dari ".$lineAsal->FullName]);
        }

        DB::table("sewing_in_mutasi")->insert($rows);

        $rows = collect($rows);

        return response()->json([
            "status" => 200,
            "message" => "Mutasi ".$kode." berhasil: ".$rows->where("tipe", "set")->sum("qty")." set & ".$rows->where("tipe", "stocker")->count()." stocker pindah ke ".$lineTujuan->FullName,
            "kode" => $kode,
        ]);
    }

    /**
     * Stok line digabung per WS + color + size: WIP set + stocker outstanding.
     */
    private function groupStock($lineId)
    {
        $stock = $this->stock->lineStock($lineId, date("Y-m-d"));
        $stockers = $this->stock->outstandingStockers($lineId);

        $groups = collect();

        foreach ($stock as $key => $row) {
            $groups->put($key, (object) [
                "act_costing_ws" => $row->act_costing_ws,
                "color" => $row->color,
                "size" => $row->size,
                "saldo_awal" => $row->saldo_awal,
                "qty_sewing_in" => $row->qty_sewing_in,
                "qty_mutasi_in" => $row->qty_mutasi_in,
                "qty_mutasi_out" => $row->qty_mutasi_out,
                "qty_output" => $row->qty_output,
                "saldo_akhir" => $row->saldo_akhir,
                "stockers" => collect(),
            ]);
        }

        foreach ($stockers as $stocker) {
            $key = SewingInStockService::comboKey($stocker->act_costing_ws, $stocker->color, $stocker->size);

            if (!$groups->has($key)) {
                $groups->put($key, (object) [
                    "act_costing_ws" => $stocker->act_costing_ws,
                    "color" => $stocker->color,
                    "size" => $stocker->size,
                    "saldo_awal" => 0,
                    "qty_sewing_in" => 0,
                    "qty_mutasi_in" => 0,
                    "qty_mutasi_out" => 0,
                    "qty_output" => 0,
                    "saldo_akhir" => 0,
                    "stockers" => collect(),
                ]);
            }

            $groups->get($key)->stockers->push($stocker);
        }

        // Tampilkan hanya yang masih ada stok (saldo set / stocker outstanding)
        return $groups->filter(fn ($group) => $group->saldo_akhir != 0 || $group->stockers->count() > 0)->
            each(function ($group) {
                $group->stockers = $group->stockers->values();
                $group->total_stocker = $group->stockers->count();
                $group->qty_stocker = $group->stockers->sum("qty_sisa");
            })->
            sortBy(fn ($group) => [$group->act_costing_ws, $group->color, $group->size]);
    }

    /**
     * Kode mutasi: SWM-yymm-00001 (reset tiap bulan). Aman karena store() sudah antri lewat GET_LOCK.
     */
    private function generateKode()
    {
        $prefix = "SWM-".date("ym")."-";

        $lastKode = DB::table("sewing_in_mutasi")->
            where("kode", "like", $prefix."%")->
            orderBy("kode", "desc")->
            value("kode");

        $number = $lastKode ? intval(substr($lastKode, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad($number, 5, "0", STR_PAD_LEFT);
    }
}
