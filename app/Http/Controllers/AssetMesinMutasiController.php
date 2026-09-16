<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class AssetMesinMutasiController extends Controller
{
    // Status unit yang masih dianggap ada di pabrik & boleh dimutasi.
    // Mengikuti konvensi AssetMesinOpnameController: mesin pembelian ACTIVE/IDLE/BREAKDOWN,
    // mesin sewa ACTIVE/IDLE (CUT OFF sudah dikembalikan ke vendor).
    private const STATUS_MESIN = ['ACTIVE', 'IDLE', 'BREAKDOWN'];
    private const STATUS_MESIN_SEWA = ['ACTIVE', 'IDLE'];

    // Status yang dipasang ke mesin begitu dimutasi: masuk lokasi = dipakai lagi.
    private const STATUS_SETELAH_MUTASI = 'ACTIVE';

    // Tabel history mutasi
    private const TABEL_MUTASI = 'asset_mutasi_mesin_hist';

    // Label lokasi: main_lokasi - sub_lokasi - alias (kolom `status`).
    // Alias tabelnya bisa diganti karena query history men-join master lokasi dua kali
    // sekaligus (lokasi asal & lokasi tujuan), jadi tidak bisa dipaku ke "a" dan "b".
    private function sqlNamaLokasi(string $det = 'a', string $main = 'b'): string
    {
        return "NULLIF(TRIM(CONCAT_WS(' - ',
            NULLIF(TRIM($main.main_lokasi), ''),
            NULLIF(TRIM($det.sub_lokasi), ''),
            NULLIF(TRIM($det.status), '')
        )), '')";
    }

    // ---- Dashboard ----
    // Halaman utama mutasi: ringkasan sebaran mesin per lokasi, dikelompokkan per main lokasi.
    // Inputannya sendiri ada di halaman terpisah (tombol "Mutasi Baru").
    public function asset_mesin_mutasi()
    {
        $sebaran = $this->getSebaranPerLokasi();

        return view('asset_management.mutasi_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_mutasi',
            'containerFluid' => true,
            'ringkasan' => $this->getRingkasan($sebaran),
            'grupLokasi' => $this->groupPerMainLokasi($sebaran),
            'tanpaLokasi' => $this->getRincianTanpaLokasi(),
        ]);
    }

    // Rincian mesin yang belum punya lokasi, supaya kelompok ini bisa ditampilkan
    // setara dengan lokasi lain di dashboard (lengkap dengan cacah beli/sewa/status).
    private function getRincianTanpaLokasi()
    {
        return DB::selectOne("
            SELECT
                COUNT(*) AS total_mesin,
                COALESCE(SUM(u.sumber = 'PEMBELIAN'), 0) AS total_beli,
                COALESCE(SUM(u.sumber = 'SEWA'), 0) AS total_sewa,
                COALESCE(SUM(u.status = 'ACTIVE'), 0) AS total_active,
                COALESCE(SUM(u.status = 'IDLE'), 0) AS total_idle,
                COALESCE(SUM(u.status = 'BREAKDOWN'), 0) AS total_breakdown
            FROM (" . $this->sqlUnitMesin() . ") u
            LEFT JOIN asset_master_lokasi_det a ON a.id = u.id_lokasi
            WHERE a.id IS NULL
        ");
    }

    // Satu baris = satu sub lokasi, lengkap dengan cacah mesin di dalamnya.
    // Lokasi yang kosong tetap ikut supaya kelihatan mana area yang belum terisi.
    private function getSebaranPerLokasi(): array
    {
        return DB::select("
            SELECT
                b.id AS id_main,
                TRIM(b.main_lokasi) AS main_lokasi,
                a.id AS id_lokasi,
                " . $this->sqlNamaLokasi() . " AS nama_lokasi,
                TRIM(COALESCE(NULLIF(TRIM(a.sub_lokasi), ''), '-')) AS sub_lokasi,
                TRIM(COALESCE(a.status, '')) AS alias_lokasi,
                TRIM(COALESCE(a.divisi, '')) AS divisi,
                COUNT(u.kode_qr) AS total_mesin,
                COALESCE(SUM(u.sumber = 'PEMBELIAN'), 0) AS total_beli,
                COALESCE(SUM(u.sumber = 'SEWA'), 0) AS total_sewa,
                COALESCE(SUM(u.status = 'ACTIVE'), 0) AS total_active,
                COALESCE(SUM(u.status = 'IDLE'), 0) AS total_idle,
                COALESCE(SUM(u.status = 'BREAKDOWN'), 0) AS total_breakdown
            FROM asset_master_lokasi_det a
            INNER JOIN asset_master_main_lokasi b ON a.id_main_lokasi = b.id
            LEFT JOIN (" . $this->sqlUnitMesin() . ") u ON u.id_lokasi = a.id
            GROUP BY a.id
            ORDER BY b.main_lokasi ASC, a.sub_lokasi ASC, a.status ASC
        ");
    }

    // Kartu angka di atas dashboard.
    // Cacah mesin sengaja dihitung dari SELURUH unit (termasuk yang belum punya lokasi),
    // bukan dari $sebaran: kalau beli/sewa diambil dari sebaran sementara totalnya dari
    // seluruh unit, angkanya jadi tidak berjumlah - persis bug "2.745 tapi Beli 637, Sewa 0".
    // Yang diambil dari $sebaran cuma cacah lokasinya.
    private function getRingkasan(array $sebaran): array
    {
        $unit = DB::selectOne("
            SELECT
                COUNT(*) AS total_mesin,
                COALESCE(SUM(u.sumber = 'PEMBELIAN'), 0) AS total_beli,
                COALESCE(SUM(u.sumber = 'SEWA'), 0) AS total_sewa,
                COALESCE(SUM(u.status = 'ACTIVE'), 0) AS total_active,
                COALESCE(SUM(u.status = 'IDLE'), 0) AS total_idle,
                COALESCE(SUM(u.status = 'BREAKDOWN'), 0) AS total_breakdown,
                -- Mesin yang id_lokasi-nya kosong / menunjuk lokasi yang sudah dihapus
                -- tidak ikut ter-group di sebaran, padahal unitnya tetap ada
                COALESCE(SUM(a.id IS NULL), 0) AS tanpa_lokasi
            FROM (" . $this->sqlUnitMesin() . ") u
            LEFT JOIN asset_master_lokasi_det a ON a.id = u.id_lokasi
        ");

        $ringkasan = [
            'total_mesin' => (int) ($unit->total_mesin ?? 0),
            'total_beli' => (int) ($unit->total_beli ?? 0),
            'total_sewa' => (int) ($unit->total_sewa ?? 0),
            'total_active' => (int) ($unit->total_active ?? 0),
            'total_idle' => (int) ($unit->total_idle ?? 0),
            'total_breakdown' => (int) ($unit->total_breakdown ?? 0),
            'tanpa_lokasi' => (int) ($unit->tanpa_lokasi ?? 0),
            'lokasi_terisi' => 0,
            'lokasi_kosong' => 0,
        ];

        foreach ($sebaran as $row) {
            $row->total_mesin > 0 ? $ringkasan['lokasi_terisi']++ : $ringkasan['lokasi_kosong']++;
        }

        return $ringkasan;
    }

    // Susun jadi main lokasi -> daftar sub lokasi, supaya view tinggal merender accordion.
    private function groupPerMainLokasi(array $sebaran): array
    {
        $grup = [];

        foreach ($sebaran as $row) {
            $key = $row->id_main;

            if (!isset($grup[$key])) {
                $grup[$key] = [
                    'id_main' => $row->id_main,
                    'main_lokasi' => $row->main_lokasi ?: '-',
                    'total_mesin' => 0,
                    'total_beli' => 0,
                    'total_sewa' => 0,
                    'total_active' => 0,
                    'total_idle' => 0,
                    'total_breakdown' => 0,
                    'sub' => [],
                ];
            }

            $grup[$key]['total_mesin'] += (int) $row->total_mesin;
            $grup[$key]['total_beli'] += (int) $row->total_beli;
            $grup[$key]['total_sewa'] += (int) $row->total_sewa;
            $grup[$key]['total_active'] += (int) $row->total_active;
            $grup[$key]['total_idle'] += (int) $row->total_idle;
            $grup[$key]['total_breakdown'] += (int) $row->total_breakdown;
            $grup[$key]['sub'][] = $row;
        }

        foreach ($grup as &$g) {
            usort($g['sub'], fn($x, $y) => $this->urutanSubLokasi($x, $y));

            // Dipakai view untuk menghitung lebar bar: panjang bar relatif terhadap
            // sub lokasi terpadat di grup yang sama, bukan terhadap seluruh pabrik,
            // supaya area kecil tetap kebaca bentuknya.
            $g['maks_sub'] = max(array_map(fn($s) => (int) $s->total_mesin, $g['sub']));
        }
        unset($g);

        // Area yang isinya paling banyak ditaruh di atas: itu yang paling sering dibuka
        usort($grup, fn($x, $y) => $y['total_mesin'] <=> $x['total_mesin']);

        return $grup;
    }

    // Urutan sub lokasi di dashboard:
    // 1. LINE duluan - ini yang paling sering dicari orang produksi
    // 2. yang ada isinya duluan
    // 3. sisanya urut nama secara natural (LINE 2 sebelum LINE 10, bukan sesudahnya)
    private function urutanSubLokasi($x, $y): int
    {
        $lineX = stripos($x->sub_lokasi, 'LINE') !== false ? 0 : 1;
        $lineY = stripos($y->sub_lokasi, 'LINE') !== false ? 0 : 1;

        if ($lineX !== $lineY) {
            return $lineX <=> $lineY;
        }

        $isiX = $x->total_mesin > 0 ? 0 : 1;
        $isiY = $y->total_mesin > 0 ? 0 : 1;

        if ($isiX !== $isiY) {
            return $isiX <=> $isiY;
        }

        return strnatcasecmp($x->sub_lokasi, $y->sub_lokasi);
    }

    // Isi modal "Detail" saat satu sub lokasi diklik di dashboard.
    // tanpa_lokasi=1 dipakai kelompok "Belum Ada Lokasi": id_lokasi-nya memang kosong /
    // menunjuk lokasi yang sudah dihapus, jadi tidak bisa dicari lewat id.
    public function getdata_mesin_per_lokasi(Request $request)
    {
        if ($request->tanpa_lokasi) {
            $where = 'a.id IS NULL';
            $bindings = [];
        } else {
            $where = 'u.id_lokasi = ?';
            $bindings = [$request->id_lokasi];
        }

        $data = DB::select("
            SELECT
                u.kode_qr,
                u.sumber,
                u.nm_jenis,
                u.nm_merk,
                u.tipe,
                u.serial_number,
                u.status
            FROM (" . $this->sqlUnitMesin() . ") u
            LEFT JOIN asset_master_lokasi_det a ON a.id = u.id_lokasi
            WHERE $where
            ORDER BY u.sumber ASC, u.nm_jenis ASC, u.kode_qr ASC
        ", $bindings);

        return DataTables::of($data)->toJson();
    }

    // ---- Rekap history di dashboard ----
    // Paging & pencarian ditulis manual, tidak lewat DataTables::of(): separuh kolom di
    // sini berasal dari ekspresi raw (nama lokasi asal/tujuan & tanggal terformat), dan
    // yajra tidak bisa menyaring alias semacam itu - hasilnya pencarian diam-diam tidak
    // menyaring apa pun. Formatnya tetap format yang diminta DataTables.
    public function getdata_history_mutasi(Request $request)
    {
        $cari = trim((string) $request->input('search.value'));
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;

        $query = DB::table(self::TABEL_MUTASI . ' as m')
            ->leftJoin(DB::raw('(' . $this->sqlUnitMesin() . ') u'), 'u.kode_qr', '=', 'm.kode_qr')
            ->leftJoin('asset_master_lokasi_det as la', 'la.id', '=', 'm.id_lokasi_asal')
            ->leftJoin('asset_master_main_lokasi as lb', 'lb.id', '=', 'la.id_main_lokasi')
            ->leftJoin('asset_master_lokasi_det as ta', 'ta.id', '=', 'm.id_lokasi_tujuan')
            ->leftJoin('asset_master_main_lokasi as tb', 'tb.id', '=', 'ta.id_main_lokasi');

        if ($cari !== '') {
            $like = '%' . $cari . '%';

            $query->where(function ($q) use ($like) {
                $q->where('m.kode_qr', 'like', $like)
                    ->orWhere('m.sumber', 'like', $like)
                    ->orWhere('m.created_by', 'like', $like)
                    ->orWhere('u.nm_jenis', 'like', $like)
                    ->orWhere('u.nm_merk', 'like', $like)
                    ->orWhere('u.tipe', 'like', $like)
                    ->orWhere('u.serial_number', 'like', $like)
                    // Nama lokasi & tanggal harus dicari lewat ekspresinya, bukan aliasnya:
                    // MySQL tidak mengizinkan alias SELECT dipakai di WHERE.
                    ->orWhereRaw($this->sqlNamaLokasi('la', 'lb') . ' LIKE ?', [$like])
                    ->orWhereRaw($this->sqlNamaLokasi('ta', 'tb') . ' LIKE ?', [$like])
                    ->orWhereRaw("DATE_FORMAT(m.tgl_trans, '%d %b %Y') LIKE ?", [$like]);
            });
        }

        $rows = (clone $query)
            ->select([
                'm.id',
                'm.kode_qr',
                'm.sumber',
                'm.created_by',
                DB::raw("DATE_FORMAT(m.tgl_trans, '%d %b %Y') AS tgl_mutasi"),
                DB::raw("DATE_FORMAT(m.created_at, '%H:%i') AS jam"),
                DB::raw($this->sqlNamaLokasi('la', 'lb') . ' AS lokasi_asal'),
                DB::raw($this->sqlNamaLokasi('ta', 'tb') . ' AS lokasi_tujuan'),
                'u.nm_jenis',
                'u.nm_merk',
                'u.tipe',
                'u.serial_number',
            ])
            // History dibaca sebagai catatan kejadian, jadi selalu terbaru di atas
            ->orderByDesc('m.created_at')
            ->orderByDesc('m.id')
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => DB::table(self::TABEL_MUTASI)->count(),
            'recordsFiltered' => $cari === '' ? DB::table(self::TABEL_MUTASI)->count() : (clone $query)->count(),
            'data' => $rows,
        ]);
    }

    // Isi modal saat satu baris rekap diklik: seluruh perpindahan milik satu kode QR,
    // dari yang terbaru ke yang paling lama.
    public function getdata_history_mesin(Request $request)
    {
        $kodeQr = trim((string) $request->kode_qr);

        if ($kodeQr === '') {
            return response()->json(['message' => 'Kode QR wajib diisi.'], 422);
        }

        $riwayat = DB::select("
            SELECT
                m.id,
                DATE_FORMAT(m.tgl_trans, '%d %b %Y') AS tgl_mutasi,
                DATE_FORMAT(m.created_at, '%H:%i') AS jam,
                " . $this->sqlNamaLokasi('la', 'lb') . " AS lokasi_asal,
                " . $this->sqlNamaLokasi('ta', 'tb') . " AS lokasi_tujuan,
                m.created_by
            FROM " . self::TABEL_MUTASI . " m
            LEFT JOIN asset_master_lokasi_det la ON la.id = m.id_lokasi_asal
            LEFT JOIN asset_master_main_lokasi lb ON lb.id = la.id_main_lokasi
            LEFT JOIN asset_master_lokasi_det ta ON ta.id = m.id_lokasi_tujuan
            LEFT JOIN asset_master_main_lokasi tb ON tb.id = ta.id_main_lokasi
            WHERE m.kode_qr = ?
            ORDER BY m.created_at DESC, m.id DESC
        ", [$kodeQr]);

        // Identitas & posisi mesin saat ini. Bisa null kalau unitnya sudah tidak
        // memenuhi filter status - riwayatnya tetap ditampilkan.
        $unit = $this->cariUnit($kodeQr);

        return response()->json([
            'kode_qr' => $kodeQr,
            'unit' => $unit,
            'riwayat' => $riwayat,
        ]);
    }

    // ---- Halaman input ----
    // Terpisah dari dashboard supaya proses scan punya layar sendiri yang bersih,
    // terutama saat dipakai di HP.
    public function create_asset_mesin_mutasi()
    {
        return view('asset_management.create_mutasi_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_mutasi',
            'containerFluid' => true,
            'lokasiList' => $this->getLokasiList(),
        ]);
    }

    // Lokasi tujuan diambil dari master lokasi asset (sama dengan yang dipakai opname)
    private function getLokasiList(): array
    {
        return DB::select("
            SELECT
                a.id AS isi,
                " . $this->sqlNamaLokasi() . " AS tampil
            FROM asset_master_lokasi_det a
            INNER JOIN asset_master_main_lokasi b ON a.id_main_lokasi = b.id
            ORDER BY b.main_lokasi ASC, a.sub_lokasi ASC, a.status ASC
        ");
    }

    // ---- Cek QR ----
    // Dipakai view untuk menampilkan identitas & lokasi mesin SEBELUM mutasi disimpan,
    // supaya user sempat melihat "dari mana ke mana" mesin ini akan dipindah.
    public function cek_qr_asset_mesin_mutasi(Request $request)
    {
        $kodeQr = trim((string) $request->kode_qr);

        if ($kodeQr === '') {
            return response()->json(['icon' => 'error', 'msg' => 'Kode QR wajib diisi.'], 422);
        }

        $unit = $this->cariUnit($kodeQr);

        if (!$unit) {
            return response()->json($this->pesanQrDitolak($kodeQr), 404);
        }

        return response()->json(['icon' => 'success', 'data' => $unit]);
    }

    // Mesin yang boleh dimutasi + nama lokasinya saat ini (= calon lokasi asal)
    private function cariUnit(string $kodeQr)
    {
        return DB::selectOne("
            SELECT
                u.*,
                " . $this->sqlNamaLokasi() . " AS lokasi_asal
            FROM (" . $this->sqlUnitMesin() . ") u
            LEFT JOIN asset_master_lokasi_det a ON a.id = u.id_lokasi
            LEFT JOIN asset_master_main_lokasi b ON b.id = a.id_main_lokasi
            WHERE u.kode_qr = ?
            LIMIT 1
        ", [$kodeQr]);
    }

    // Kenapa sebuah QR ditolak. Dibedakan supaya operator tahu harus berbuat apa:
    // QR yang sama sekali tidak terdaftar beda penanganannya dengan QR yang terdaftar
    // tapi statusnya belum/tidak layak dimutasi - yang kedua harus dibetulkan di master.
    private function pesanQrDitolak(string $kodeQr): array
    {
        $mentah = DB::selectOne("
            SELECT kode_qr, 'PEMBELIAN' AS sumber, status FROM asset_penerimaan_mesin WHERE kode_qr = ?
            UNION ALL
            SELECT kode_qr, 'SEWA' AS sumber, status FROM asset_penerimaan_mesin_sewa WHERE kode_qr = ?
            LIMIT 1
        ", [$kodeQr, $kodeQr]);

        if (!$mentah) {
            return [
                'icon' => 'error',
                'msg' => 'QR tidak terdaftar di data penerimaan mesin.',
            ];
        }

        $status = trim((string) $mentah->status);

        return [
            'icon' => 'error',
            'msg' => 'Mesin ini belum bisa dimutasi.',
            'detail' => 'Kode QR <b>' . e($kodeQr) . '</b> terdaftar sebagai mesin ' . e($mentah->sumber)
                . ', tapi statusnya <b>' . ($status !== '' ? e($status) : 'belum diisi') . '</b>.'
                . ' Betulkan dulu statusnya di master mesin.',
        ];
    }

    // ---- History mutasi ----
    // Section paling bawah di halaman input.
    // Kalau ada lokasi dipilih: seluruh pergerakan lokasi itu - yang MASUK (jadi tujuan)
    // maupun yang KELUAR (jadi asal), supaya riwayat satu area terbaca utuh.
    // Tanpa lokasi: transaksi terakhir dari semua lokasi.
    // Dimuat 5 baris sekali jalan, sisanya lewat tombol "muat lagi".
    public const HISTORY_PER_MUAT = 5;

    public function getdata_asset_mesin_mutasi(Request $request)
    {
        $idLokasi = $request->id_lokasi;
        $offset = max(0, (int) $request->offset);
        $limit = self::HISTORY_PER_MUAT;

        $where = '1';
        $bindings = [];

        if ($idLokasi) {
            $where = '(m.id_lokasi_tujuan = ? OR m.id_lokasi_asal = ?)';
            $bindings = [$idLokasi, $idLokasi];
        }

        // LIMIT sengaja ditempel sebagai angka, bukan binding: MySQL menolak parameter
        // ber-quote di LIMIT. Keduanya sudah di-cast int jadi aman.
        // Diambil satu lebih banyak dari yang dipakai, cuma untuk tahu masih ada sisa
        // atau tidak - tanpa perlu query COUNT terpisah.
        $rows = DB::select("
            SELECT
                m.id,
                DATE_FORMAT(m.tgl_trans, '%d %b %Y') AS tgl_mutasi,
                DATE_FORMAT(m.created_at, '%H:%i') AS jam,
                m.kode_qr,
                m.sumber,
                m.id_lokasi_asal,
                m.id_lokasi_tujuan,
                " . $this->sqlNamaLokasi('la', 'lb') . " AS lokasi_asal,
                " . $this->sqlNamaLokasi('ta', 'tb') . " AS lokasi_tujuan,
                m.created_by,
                u.nm_jenis,
                u.nm_merk,
                u.tipe,
                u.serial_number
            FROM " . self::TABEL_MUTASI . " m
            LEFT JOIN (" . $this->sqlUnitMesin() . ") u ON u.kode_qr = m.kode_qr
            LEFT JOIN asset_master_lokasi_det la ON la.id = m.id_lokasi_asal
            LEFT JOIN asset_master_main_lokasi lb ON lb.id = la.id_main_lokasi
            LEFT JOIN asset_master_lokasi_det ta ON ta.id = m.id_lokasi_tujuan
            LEFT JOIN asset_master_main_lokasi tb ON tb.id = ta.id_main_lokasi
            WHERE $where
            ORDER BY m.created_at DESC, m.id DESC
            LIMIT " . ($limit + 1) . " OFFSET " . $offset . "
        ", $bindings);

        $adaLagi = count($rows) > $limit;
        $rows = array_slice($rows, 0, $limit);

        // Arah cuma berarti kalau sedang melihat satu lokasi: baris yang sama bisa
        // "masuk" bagi lokasi tujuan & "keluar" bagi lokasi asal.
        foreach ($rows as $row) {
            $row->arah = null;

            if ($idLokasi) {
                $row->arah = (int) $row->id_lokasi_tujuan === (int) $idLokasi ? 'MASUK' : 'KELUAR';
            }
        }

        return response()->json([
            'data' => $rows,
            'ada_lagi' => $adaLagi,
        ]);
    }

    // ---- Simpan mutasi ----
    // Satu scan = satu mutasi. History dicatat & master ikut pindah dalam satu transaksi,
    // supaya tidak pernah ada history tanpa perpindahan (atau sebaliknya).
    public function store_asset_mesin_mutasi(Request $request)
    {
        $kodeQr = trim((string) $request->txtqr);
        $idTujuan = $request->id_lokasi_tujuan;

        if ($kodeQr === '' || !$idTujuan) {
            return response()->json([
                'icon' => 'error',
                'msg' => 'Lokasi tujuan & Kode QR wajib diisi.',
            ]);
        }

        if (!DB::table('asset_master_lokasi_det')->where('id', $idTujuan)->exists()) {
            return response()->json([
                'icon' => 'error',
                'msg' => 'Lokasi tujuan tidak ditemukan di master lokasi.',
            ]);
        }

        $unit = $this->cariUnit($kodeQr);

        if (!$unit) {
            return response()->json($this->pesanQrDitolak($kodeQr));
        }

        // Mesin yang sudah berada di lokasi tujuan tetap diproses, cuma tidak dicatat
        // sebagai mutasi: yang terjadi bukan perpindahan, melainkan penegasan "unit ini
        // memang ada di sini & dipakai". Kalau tetap masuk history, cacah perpindahan
        // jadi salah & timeline-nya penuh baris "LINE 02 -> LINE 02".
        // id_lokasi NULL ikut dihitung pindah: itu perpindahan nyata dari "belum ada lokasi".
        $pindah = (int) $unit->id_lokasi !== (int) $idTujuan;

        $timestamp = Carbon::now();

        DB::transaction(function () use ($unit, $kodeQr, $idTujuan, $timestamp, $pindah) {
            if ($pindah) {
                DB::table(self::TABEL_MUTASI)->insert([
                    'tgl_trans' => $timestamp->toDateString(),
                    'id_penerimaan' => $unit->id_penerimaan,
                    'kode_qr' => $kodeQr,
                    'id_lokasi_asal' => $unit->id_lokasi,
                    'id_lokasi_tujuan' => $idTujuan,
                    'sumber' => $unit->sumber,
                    'created_by' => Auth::user()->name,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            // Selalu dijalankan, pindah atau tidak: mesin yang QR-nya kescan di sebuah
            // lokasi berarti terlihat dipakai di situ, jadi statusnya dinaikkan ke ACTIVE
            // - termasuk yang sebelumnya IDLE / BREAKDOWN.
            DB::table($this->tabelPenerimaan($unit->sumber))
                ->where('id', $unit->id_penerimaan)
                ->update([
                    'id_lokasi' => $idTujuan,
                    'status' => self::STATUS_SETELAH_MUTASI,
                    'updated_at' => $timestamp,
                ]);
        });

        return response()->json([
            'icon' => 'success',
            'pindah' => $pindah,
            'msg' => $pindah ? 'Mesin berhasil dimutasi.' : 'Mesin sudah di lokasi ini.',
        ]);
    }

    // Mesin pembelian & sewa disimpan di tabel berbeda; cuma bagian update inilah yang
    // benar-benar perlu bercabang - pembacaannya sudah disatukan lewat sqlUnitMesin().
    private function tabelPenerimaan(?string $sumber): string
    {
        return $sumber === 'SEWA' ? 'asset_penerimaan_mesin_sewa' : 'asset_penerimaan_mesin';
    }

    // Unit mesin (pembelian + sewa) yang punya kode QR & statusnya masih terpakai.
    // id_lokasi di sini adalah lokasi mesin saat ini = lokasi asal mutasi.
    private function sqlUnitMesin(): string
    {
        $statusMesin = "'" . implode("','", self::STATUS_MESIN) . "'";
        $statusSewa = "'" . implode("','", self::STATUS_MESIN_SEWA) . "'";

        return "
            SELECT
                a.id AS id_penerimaan,
                a.kode_qr,
                'PEMBELIAN' AS sumber,
                a.id_lokasi,
                j.nm_jenis,
                k.nm_merk,
                m.tipe,
                a.serial_number,
                a.status
            FROM asset_penerimaan_mesin a
            INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
            INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
            INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
            WHERE a.kode_qr IS NOT NULL AND a.kode_qr <> '' AND a.status IN ($statusMesin)

            UNION ALL

            SELECT
                a.id AS id_penerimaan,
                a.kode_qr,
                'SEWA' AS sumber,
                a.id_lokasi,
                a.nm_jenis,
                a.nm_merk,
                a.tipe,
                a.serial_number,
                a.status
            FROM asset_penerimaan_mesin_sewa a
            WHERE a.kode_qr IS NOT NULL AND a.kode_qr <> '' AND a.status IN ($statusSewa)
        ";
    }
}
