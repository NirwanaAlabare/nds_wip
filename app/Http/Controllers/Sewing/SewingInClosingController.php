<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\UserLine;
use App\Services\SewingInStockService;
use Illuminate\Http\Request;
use DB;

class SewingInClosingController extends Controller
{
    protected $stock;

    public function __construct(SewingInStockService $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Closing stok Sewing In per line per tanggal (manual).
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lines = UserLine::where("Groupp", "SEWING")->whereRaw("(Locked is null OR Locked != 1)")->orderBy("line_id", 'asc')->get();

        return view("sewing.sewing-in-closing", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-packing-in", "subPage" => "sewing-in-closing"], ["lines" => $lines]);
    }

    /**
     * Data closing line + tanggal: hasil yang sudah tersimpan, atau hitungan pratinjau kalau belum closing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $request->validate([
            "line_id" => "required",
            "tanggal" => "required|date",
        ]);

        $saved = DB::table("sewing_in_closing")->
            where("line_id", $request->line_id)->
            where("tanggal", $request->tanggal)->
            get();

        // Sudah closing: tampilkan yang tersimpan
        if ($saved->count() > 0) {
            $first = $saved->first();

            return response()->json([
                "status" => 200,
                "closed" => true,
                "closed_by" => $first->created_by_username,
                "closed_at" => $first->created_at,
                "rows" => $saved->where("act_costing_ws", "!=", "")->sortBy(fn ($row) => [$row->act_costing_ws, $row->color, $row->size])->values(),
                "stockers" => DB::table("sewing_in_closing_stocker")->
                    where("line_id", $request->line_id)->
                    where("tanggal", $request->tanggal)->
                    orderBy("act_costing_ws")->orderBy("id_qr_stocker")->
                    get(),
            ]);
        }

        return response()->json([
            "status" => 200,
            "closed" => false,
            "blocked" => $this->blockedReason($request->line_id, $request->tanggal),
            "rows" => $this->stock->closingPreview($request->line_id, $request->tanggal)->values(),
            "stockers" => $this->closingStockers($request->line_id, $request->tanggal),
        ]);
    }

    /**
     * Riwayat closing per rentang tanggal (dan line kalau dipilih).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            "tanggal_awal" => "required|date",
            "tanggal_akhir" => "required|date|after_or_equal:tanggal_awal",
        ]);

        $closings = DB::table("sewing_in_closing")->
            selectRaw("
                tanggal, line_id, nama_line,
                SUM(act_costing_ws != '') as total_ws,
                SUM(qty_sewing_in) as qty_sewing_in,
                SUM(qty_output) as qty_output,
                SUM(saldo_akhir) as saldo_akhir,
                MAX(created_by_username) as created_by_username,
                MAX(created_at) as created_at
            ")->
            whereBetween("tanggal", [$request->tanggal_awal, $request->tanggal_akhir])->
            when($request->line_id, fn ($query) => $query->where("line_id", $request->line_id))->
            groupBy("tanggal", "line_id", "nama_line")->
            orderBy("tanggal", "desc")->
            orderBy("line_id")->
            get();

        $stockers = DB::table("sewing_in_closing_stocker")->
            selectRaw("tanggal, line_id, COUNT(*) as total_stocker, SUM(qty_sisa) as qty_sisa")->
            whereBetween("tanggal", [$request->tanggal_awal, $request->tanggal_akhir])->
            when($request->line_id, fn ($query) => $query->where("line_id", $request->line_id))->
            groupBy("tanggal", "line_id")->
            get()->
            keyBy(fn ($row) => $row->tanggal."|".$row->line_id);

        $closings->each(function ($closing) use ($stockers) {
            $stocker = $stockers->get($closing->tanggal."|".$closing->line_id);

            $closing->total_stocker = $stocker->total_stocker ?? 0;
            $closing->qty_sisa = $stocker->qty_sisa ?? 0;
        });

        return response()->json(["status" => 200, "data" => $closings]);
    }

    /**
     * Simpan closing line + tanggal (saldo WIP per WS/color/size & snapshot stocker outstanding).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "line_id" => "required",
            "tanggal" => "required|date|before_or_equal:today",
        ], [
            "tanggal.before_or_equal" => "Tidak bisa closing untuk tanggal yang akan datang",
        ]);

        $line = UserLine::where("line_id", $request->line_id)->first();

        if (!$line) {
            return response()->json(["status" => 400, "message" => "Line tidak ditemukan"]);
        }

        // Antri dengan Sewing In & mutasi supaya tidak ada transaksi masuk saat closing dihitung
        $locked = DB::selectOne("SELECT GET_LOCK('sewing_in_store', 10) as locked")->locked;

        if (!$locked) {
            return response()->json(["status" => 409, "busy" => true, "message" => "Menunggu antrian simpan"]);
        }

        try {
            return DB::transaction(fn () => $this->storeClosing($line, $request->tanggal), 3);
        } finally {
            DB::select("SELECT RELEASE_LOCK('sewing_in_store')");
        }
    }

    private function storeClosing($line, $tanggal)
    {
        $blocked = $this->blockedReason($line->line_id, $tanggal);

        if ($blocked) {
            return response()->json(["status" => 400, "message" => $blocked]);
        }

        $rows = $this->stock->closingPreview($line->line_id, $tanggal);
        $stockers = $this->closingStockers($line->line_id, $tanggal);

        $now = now();

        $base = [
            "tanggal" => $tanggal,
            "line_id" => $line->line_id,
            "nama_line" => $line->username,
            "created_at" => $now,
            "updated_at" => $now,
        ];

        $closingRows = $rows->map(fn ($row) => $base + [
            "act_costing_ws" => $row->act_costing_ws,
            "color" => $row->color,
            "size" => $row->size,
            "saldo_awal" => $row->saldo_awal,
            "qty_sewing_in" => $row->qty_sewing_in,
            "qty_mutasi_in" => $row->qty_mutasi_in,
            "qty_mutasi_out" => $row->qty_mutasi_out,
            "qty_output" => $row->qty_output,
            "saldo_akhir" => $row->saldo_akhir,
            "created_by" => auth()->id(),
            "created_by_username" => auth()->user() ? auth()->user()->username : null,
        ])->values()->all();

        // Line kosong tetap disimpan 1 baris penanda supaya tanggalnya terkunci
        if (count($closingRows) < 1) {
            $closingRows[] = $base + [
                "act_costing_ws" => "",
                "color" => "",
                "size" => "",
                "created_by" => auth()->id(),
                "created_by_username" => auth()->user() ? auth()->user()->username : null,
            ];
        }

        DB::table("sewing_in_closing")->insert($closingRows);

        if ($stockers->count() > 0) {
            DB::table("sewing_in_closing_stocker")->insert($stockers->map(fn ($stocker) => $base + [
                "stocker_id" => $stocker->stocker_id,
                "id_qr_stocker" => $stocker->id_qr_stocker,
                "act_costing_ws" => $stocker->act_costing_ws,
                "color" => $stocker->color,
                "size" => $stocker->size,
                "part_detail_id" => $stocker->part_detail_id,
                "nama_part" => $stocker->nama_part,
                "qty_stocker" => $stocker->qty_stocker,
                "qty_used" => $stocker->qty_used,
                "qty_sisa" => $stocker->qty_sisa,
            ])->all());
        }

        return response()->json([
            "status" => 200,
            "message" => "Closing ".$line->FullName." tanggal ".date("d-m-Y", strtotime($tanggal))." berhasil: saldo ".$rows->sum("saldo_akhir")." set, ".$stockers->count()." stocker outstanding",
        ]);
    }

    /**
     * Alasan line + tanggal tidak bisa di-closing (null = boleh).
     */
    private function blockedReason($lineId, $tanggal)
    {
        if ($tanggal > date("Y-m-d")) {
            return "Tidak bisa closing untuk tanggal yang akan datang";
        }

        $lastClosing = DB::table("sewing_in_closing")->where("line_id", $lineId)->max("tanggal");

        if ($lastClosing && $lastClosing >= $tanggal) {
            return $lastClosing == $tanggal ?
                "Line ini sudah closing untuk tanggal ini" :
                "Line ini sudah closing tanggal ".date("d-m-Y", strtotime($lastClosing)).", tanggal sebelumnya sudah terkunci";
        }

        return null;
    }

    /**
     * Stocker outstanding di line per akhir tanggal closing.
     */
    private function closingStockers($lineId, $tanggal)
    {
        return $this->stock->outstandingStockers($lineId, null, $tanggal)->
            sortBy(fn ($stocker) => [$stocker->act_costing_ws, $stocker->color, $stocker->size, $stocker->id_qr_stocker])->
            values();
    }
}
