<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class AssetMesinRequirementController extends Controller
{
    public function asset_mesin_requirement(Request $request)
    {
        // Nama lokasi disusun sama seperti di Master Mesin (main - sub - status), tiap bagian di-TRIM
        $lineList = DB::select("
            SELECT lok_det.id, NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(TRIM(lok_main.main_lokasi), ''), NULLIF(TRIM(lok_det.sub_lokasi), ''), NULLIF(TRIM(lok_det.status), ''))), '') AS nama
            FROM asset_master_lokasi_det lok_det
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            HAVING nama IS NOT NULL
            ORDER BY lok_det.id ASC
        ");

        $jenisList = DB::table('asset_master_kd_jenis')
            ->select('id_jenis', 'kd_jenis', 'nm_jenis')
            ->orderBy('kd_jenis', 'asc')
            ->get();

        return view('asset_management.machine_requirement', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_requirement',
            'containerFluid' => true,
            'lineList' => $lineList,
            'jenisList' => $jenisList,
        ]);
    }

    // Plan yang rentang tanggalnya bersinggungan dengan periode filter ikut tampil,
    // jadi plan yang mulai sebelum periode tapi masih berjalan di dalamnya tidak terlewat.
    // Di database satu requirement = beberapa baris (satu per jenis mesin); di listing digabung lagi
    // jadi satu baris per tgl + style + line, jenis mesinnya jadi daftar.
    public function getdata_asset_mesin_requirement(Request $request)
    {
        $data = DB::select("
            SELECT
                req.id,
                req.tgl_awal,
                req.tgl_akhir,
                DATEDIFF(req.tgl_akhir, req.tgl_awal) + 1 AS total_days,
                req.style,
                req.id_lokasi,
                NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(TRIM(lok_det.sub_lokasi), ''), NULLIF(TRIM(lok_det.status), ''))), '') AS nama_line,
                NULLIF(TRIM(lok_main.main_lokasi), '') AS nama_gedung,
                kj.kd_jenis,
                kj.nm_jenis,
                req.qty,
                req.created_by,
                req.created_at
            FROM asset_mesin_req req
            LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = req.id_lokasi
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            LEFT JOIN asset_master_kd_jenis kj ON kj.id_jenis = req.id_kd_jenis
            WHERE req.tgl_awal <= ? AND req.tgl_akhir >= ?
            ORDER BY req.tgl_awal DESC, req.id ASC
        ", [$request->tgl_akhir, $request->tgl_awal]);

        $rows = collect($data)
            ->groupBy(fn ($r) => $r->tgl_awal . '|' . $r->tgl_akhir . '|' . $r->style . '|' . $r->id_lokasi)
            ->map(fn ($items) => [
                'tgl_awal' => $items[0]->tgl_awal,
                'tgl_akhir' => $items[0]->tgl_akhir,
                'total_days' => (int) $items[0]->total_days,
                'style' => $items[0]->style,
                'nama_line' => $items[0]->nama_line,
                'nama_gedung' => $items[0]->nama_gedung,
                'jenis' => $items->map(fn ($r) => [
                    'kd_jenis' => $r->kd_jenis,
                    'nm_jenis' => $r->nm_jenis,
                    'qty' => (int) $r->qty,
                ])->sortByDesc('qty')->values(),
                'total_qty' => $items->sum('qty'),
                'created_by' => $items[0]->created_by,
                'created_at' => $items->min('created_at'),
            ])
            ->values();

        return DataTables::of($rows)->toJson();
    }

    // Preview ala sheet "MC REQ": baris = line + style, kolom = jenis mesin, ditutup Stok / Total Req / Balance.
    // Diambil semua plan yang bersinggungan dengan periode. Kebutuhan per hari & status PASS dihitung di
    // browser, karena plan beda minggu di line yang sama tidak boleh dijumlah (mesinnya dipakai bergantian).
    public function preview_asset_mesin_requirement(Request $request)
    {
        $request->validate([
            'tgl_dari' => 'required|date',
            'tgl_sampai' => 'required|date|after_or_equal:tgl_dari',
        ], [
            'tgl_sampai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal dari',
        ]);

        // Dibatasi supaya kolom harian di tampilan tidak kepanjangan
        if (Carbon::parse($request->tgl_dari)->diffInDays(Carbon::parse($request->tgl_sampai)) > 61) {
            return response()->json(['message' => 'Periode preview maksimal 62 hari'], 422);
        }

        $reqs = DB::select("
            SELECT
                req.id_lokasi,
                NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(TRIM(lok_main.main_lokasi), ''), NULLIF(TRIM(lok_det.sub_lokasi), ''), NULLIF(TRIM(lok_det.status), ''))), '') AS nama_lokasi,
                req.style,
                req.tgl_awal,
                req.tgl_akhir,
                kj.kd_jenis,
                SUM(req.qty) AS qty,
                MIN(req.id) AS urutan
            FROM asset_mesin_req req
            LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = req.id_lokasi
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            INNER JOIN asset_master_kd_jenis kj ON kj.id_jenis = req.id_kd_jenis
            WHERE req.tgl_awal <= ? AND req.tgl_akhir >= ?
            GROUP BY req.id_lokasi, nama_lokasi, req.style, req.tgl_awal, req.tgl_akhir, kj.kd_jenis
            ORDER BY req.id_lokasi ASC, req.style ASC, req.tgl_awal ASC
        ", [$request->tgl_sampai, $request->tgl_dari]);

        // Kolom yang tampil hanya jenis yang dibutuhkan di periode ini, supaya tabelnya tidak melebar ke 46 jenis
        $kdJenis = collect($reqs)->pluck('kd_jenis')->unique()->values()->all();
        $jenis = $this->stokPerJenis('kj.kd_jenis', $kdJenis);

        // Satu baris per line + style + periode plan, qty per jenis jadi map {kd_jenis: qty}.
        // urutan (id input paling awal) dipakai sebagai penentu prioritas stok kalau tgl mulai sama.
        $rows = collect($reqs)
            ->groupBy(fn ($r) => $r->id_lokasi . '|' . $r->style . '|' . $r->tgl_awal . '|' . $r->tgl_akhir)
            ->map(fn ($items) => [
                'nama_lokasi' => $items[0]->nama_lokasi,
                'style' => $items[0]->style,
                'tgl_awal' => $items[0]->tgl_awal,
                'tgl_akhir' => $items[0]->tgl_akhir,
                'urutan' => (int) $items->min('urutan'),
                'qty' => $items->mapWithKeys(fn ($r) => [$r->kd_jenis => (int) $r->qty]),
            ])
            ->values();

        return response()->json([
            'jenis' => $jenis,
            'rows' => $rows,
            'stok' => $this->stokMesin(),
            'sewa' => $this->stokSewa(),
        ]);
    }

    // Dipanggil sebelum simpan: apakah requirement baru ini bikin total kebutuhan di suatu hari melebihi stok.
    // Hasilnya hanya peringatan, user tetap boleh menyimpan.
    public function cek_stok_asset_mesin_requirement(Request $request)
    {
        $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
            'items' => 'required|array|min:1',
            'items.*.id_kd_jenis' => 'required|exists:asset_master_kd_jenis,id_jenis',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $qtyBaru = collect($request->items)
            ->groupBy('id_kd_jenis')
            ->map(fn ($items) => $items->sum('qty'));
        $stok = collect($this->stokPerJenis('kj.id_jenis', $qtyBaru->keys()->all()))->keyBy('id_jenis');

        $existing = DB::table('asset_mesin_req')
            ->select('id_kd_jenis', 'tgl_awal', 'tgl_akhir', 'qty')
            ->whereIn('id_kd_jenis', $qtyBaru->keys())
            ->where('tgl_awal', '<=', $request->tgl_akhir)
            ->where('tgl_akhir', '>=', $request->tgl_awal)
            ->get()
            ->groupBy('id_kd_jenis');

        $kurang = [];
        foreach ($qtyBaru as $idKdJenis => $qty) {
            $jenis = $stok[$idKdJenis];
            $totalStok = $jenis->stok_beli + $jenis->stok_sewa;
            $hariKurang = [];
            $kebutuhanMax = 0;

            for ($tgl = Carbon::parse($request->tgl_awal); $tgl->lte(Carbon::parse($request->tgl_akhir)); $tgl->addDay()) {
                $hari = $tgl->toDateString();
                $kebutuhan = $qty + collect($existing[$idKdJenis] ?? [])
                    ->filter(fn ($r) => $r->tgl_awal <= $hari && $r->tgl_akhir >= $hari)
                    ->sum('qty');

                $kebutuhanMax = max($kebutuhanMax, $kebutuhan);
                if ($kebutuhan > $totalStok) {
                    $hariKurang[] = $hari;
                }
            }

            if ($hariKurang) {
                $kurang[] = [
                    'kd_jenis' => $jenis->kd_jenis,
                    'stok' => $totalStok,
                    'kebutuhan' => $kebutuhanMax,
                    'kurang' => $kebutuhanMax - $totalStok,
                    'tgl_dari' => $hariKurang[0],
                    'tgl_sampai' => end($hariKurang),
                ];
            }
        }

        return response()->json(['kurang' => $kurang]);
    }

    // Stok per kd_jenis = mesin pembelian ACTIVE/IDLE/BREAKDOWN (lewat asset_master_jenis_mesin)
    // + mesin sewa ACTIVE/IDLE. Tabel sewa tidak punya kd_jenis, jadi dicocokkan lewat nm_jenis = kd_jenis;
    // nm_jenis sewa yang belum diisi kode jenis otomatis tidak terhitung.
    // $kolomFilter: 'kj.kd_jenis' / 'kj.id_jenis' sesuai kunci yang dipegang pemanggil, null = semua jenis.
    private function stokPerJenis(?string $kolomFilter, array $nilai = []): array
    {
        if ($kolomFilter && !$nilai) {
            return [];
        }

        $where = $kolomFilter
            ? "WHERE $kolomFilter IN (" . implode(',', array_fill(0, count($nilai), '?')) . ")"
            : '';

        return DB::select("
            SELECT
                kj.id_jenis,
                kj.kd_jenis,
                kj.nm_jenis,
                (
                    SELECT COUNT(*)
                    FROM asset_penerimaan_mesin a
                    INNER JOIN asset_master_jenis_mesin m ON m.id_jenis = a.id_jenis
                    WHERE m.kd_jenis = kj.kd_jenis
                      AND a.status IN ('ACTIVE','IDLE','BREAKDOWN')
                ) AS stok_beli,
                (
                    SELECT COUNT(*)
                    FROM asset_penerimaan_mesin_sewa s
                    WHERE TRIM(UPPER(s.nm_jenis)) = TRIM(UPPER(kj.kd_jenis))
                      AND s.status IN ('ACTIVE','IDLE')
                ) AS stok_sewa
            FROM asset_master_kd_jenis kj
            $where
            ORDER BY kj.kd_jenis ASC
        ", $nilai);
    }

    // Semua jenis yang punya mesin (beli atau sewa), untuk tabel stok mesin di preview
    private function stokMesin(): array
    {
        return array_values(array_filter(
            $this->stokPerJenis(null),
            fn ($j) => $j->stok_beli + $j->stok_sewa > 0
        ));
    }

    // Stok mesin sewa per nm_jenis. kd_jenis terisi kalau nm_jenis-nya sudah sama dengan kode jenis di master,
    // null berarti belum terpetakan (tidak ikut dihitung di stok per jenis).
    private function stokSewa(): array
    {
        return DB::select("
            SELECT s.nm_jenis, kj.kd_jenis, COUNT(*) AS total
            FROM asset_penerimaan_mesin_sewa s
            LEFT JOIN asset_master_kd_jenis kj ON TRIM(UPPER(kj.kd_jenis)) = TRIM(UPPER(s.nm_jenis))
            WHERE s.status IN ('ACTIVE','IDLE') AND s.nm_jenis IS NOT NULL
            GROUP BY s.nm_jenis, kj.kd_jenis
            ORDER BY kj.kd_jenis IS NULL, s.nm_jenis ASC
        ");
    }

    // Satu requirement disimpan jadi beberapa baris di asset_mesin_req (satu baris per jenis mesin),
    // header-nya (tgl, style, lokasi) diulang di tiap baris
    public function store_asset_mesin_requirement(Request $request)
    {
        $request->validate([
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
            'style' => 'required',
            'id_lokasi' => 'required|exists:asset_master_lokasi_det,id',
            'items' => 'required|array|min:1',
            'items.*.id_kd_jenis' => 'required|distinct|exists:asset_master_kd_jenis,id_jenis',
            'items.*.qty' => 'required|integer|min:1',
        ], [
            'items.*.id_kd_jenis.required' => 'Jenis mesin wajib dipilih di semua baris',
            'items.*.id_kd_jenis.distinct' => 'Jenis mesin tidak boleh dipilih lebih dari sekali',
            'items.*.qty.min' => 'Qty minimal 1',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $rows = collect($request->items)->map(fn ($item) => [
            'tgl_awal' => $request->tgl_awal,
            'tgl_akhir' => $request->tgl_akhir,
            'style' => trim($request->style),
            'id_lokasi' => $request->id_lokasi,
            'id_kd_jenis' => $item['id_kd_jenis'],
            'qty' => $item['qty'],
            'created_by' => $user,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->all();

        DB::table('asset_mesin_req')->insert($rows);

        return response()->json([
            'status' => 'success',
            'message' => 'Machine Requirement berhasil disimpan',
        ]);
    }

    // Style jumlahnya ribuan, jadi dropdown-nya dicari lewat ajax (ketik), bukan dimuat semua di halaman
    public function get_style(Request $request)
    {
        $styles = DB::table('master_sb_ws')
            ->select('styleno')
            ->whereNotNull('styleno')
            ->where('styleno', '!=', '')
            ->when($request->q, fn ($query) => $query->where('styleno', 'like', '%' . $request->q . '%'))
            ->distinct()
            ->orderBy('styleno', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'results' => $styles->map(fn ($row) => ['id' => $row->styleno, 'text' => $row->styleno]),
        ]);
    }
}
