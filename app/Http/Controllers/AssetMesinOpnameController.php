<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;

class AssetMesinOpnameController extends Controller
{
    // Status unit yang masih dianggap ada di pabrik & wajib ikut opname.
    // Mengikuti konvensi yang sudah dipakai di AssetMesinReportController:
    // mesin pembelian ACTIVE/IDLE/BREAKDOWN, mesin sewa ACTIVE/IDLE (CUT OFF sudah dikembalikan).
    private const STATUS_MESIN = ['ACTIVE', 'IDLE', 'BREAKDOWN'];
    private const STATUS_MESIN_SEWA = ['ACTIVE', 'IDLE'];

    // Daftar header opname (satu baris = satu No SO)
    public function asset_mesin_opname(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of($this->getHeaderOpname($request))->toJson();
        }

        return view('asset_management.opname_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_opname',
            'containerFluid' => true,
        ]);
    }

    // Header yang periodenya bersinggungan dengan rentang tanggal filter
    private function getHeaderOpname(Request $request): array
    {
        $tglAwal = $request->tgl_awal ?: date('Y-m-01');
        $tglAkhir = $request->tgl_akhir ?: date('Y-m-d');

        return DB::select("
            SELECT
                h.id,
                h.no_so,
                h.periode_tgl_awal,
                h.periode_tgl_akhir,
                DATE_FORMAT(h.periode_tgl_awal, '%d %M %Y') AS periode_awal,
                DATE_FORMAT(h.periode_tgl_akhir, '%d %M %Y') AS periode_akhir,
                h.ket,
                h.created_by,
                DATE_FORMAT(h.created_at, '%d %M %Y %H:%i') AS created_at,
                (SELECT COUNT(*) FROM asset_stok_opname_mesin d WHERE d.id_so = h.id) AS total_mesin
            FROM asset_stok_opname_header_mesin h
            WHERE h.periode_tgl_awal <= ? AND h.periode_tgl_akhir >= ?
            ORDER BY h.id DESC
        ", [$tglAkhir, $tglAwal]);
    }

    // Simpan header baru dari modal "New". No SO dibuat otomatis di sini,
    // supaya nomornya tidak pernah bentrok walau dua user membuka modalnya bersamaan.
    public function store_header_asset_mesin_opname(Request $request)
    {
        $request->validate([
            'periode_tgl_awal' => 'required|date',
            'periode_tgl_akhir' => 'required|date|after_or_equal:periode_tgl_awal',
            'ket' => 'nullable|string',
        ]);

        $timestamp = Carbon::now();

        $id = DB::transaction(function () use ($request, $timestamp) {
            return DB::table('asset_stok_opname_header_mesin')->insertGetId([
                'no_so' => $this->generateNoSo(),
                'periode_tgl_awal' => $request->periode_tgl_awal,
                'periode_tgl_akhir' => $request->periode_tgl_akhir,
                'ket' => $request->ket,
                'created_by' => Auth::user()->name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        });

        $header = DB::table('asset_stok_opname_header_mesin')->where('id', $id)->first();

        return response()->json([
            'id' => $id,
            'no_so' => $header->no_so,
            'redirect' => route('create_asset_mesin_opname', ['id_so' => $id]),
        ]);
    }

    // Format nomor: SO/MSN/09001 - "09" bulan berjalan, "001" urutan yang direset tiap bulan.
    private function generateNoSo(): string
    {
        $prefix = 'SO/MSN/' . date('m');

        $last = DB::table('asset_stok_opname_header_mesin')
            ->where('no_so', 'like', $prefix . '%')
            ->whereYear('created_at', date('Y'))
            ->orderByDesc('no_so')
            ->value('no_so');

        $urut = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    // ---- Master lokasi mesin (modal "Lokasi" di halaman list opname) ----
    public function getdata_lokasi_mesin()
    {
        $data = DB::select("
            SELECT
                l.id,
                l.lokasi,
                l.created_by,
                DATE_FORMAT(l.created_at, '%d %M %Y %H:%i') AS created_at
            FROM master_mesin_lokasi l
            ORDER BY l.lokasi ASC
        ");

        return DataTables::of($data)->toJson();
    }

    public function store_lokasi_mesin(Request $request)
    {
        $request->validate([
            'lokasi' => 'required|string|max:255',
        ]);

        $lokasi = strtoupper(trim($request->lokasi));

        // Nama lokasi dipakai sebagai penanda di tabel opname, jadi tidak boleh kembar
        $sudahAda = DB::table('master_mesin_lokasi')->where('lokasi', $lokasi)->exists();

        if ($sudahAda) {
            return response()->json(['message' => 'Lokasi ' . $lokasi . ' sudah ada.'], 422);
        }

        $timestamp = Carbon::now();

        DB::table('master_mesin_lokasi')->insert([
            'lokasi' => $lokasi,
            'created_by' => Auth::user()->name,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return response()->json(['message' => 'Lokasi ' . $lokasi . ' ditambahkan.']);
    }

    public function create_asset_mesin_opname(Request $request)
    {
        // Halaman input selalu terikat ke satu header; tanpa header tidak ada yang bisa diisi
        $header = DB::table('asset_stok_opname_header_mesin')->where('id', $request->id_so)->first();

        if (!$header) {
            return redirect()->route('asset_mesin_opname');
        }

        $lokasiList = DB::select("SELECT lokasi isi, lokasi tampil FROM master_mesin_lokasi ORDER BY lokasi ASC");

        return view('asset_management.create_opname_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_opname',
            'containerFluid' => true,
            'lokasiList' => $lokasiList,
            'header' => $header,
        ]);
    }

    // Detail mesin milik satu header. Dipakai halaman input & modal View di list.
    // Halaman input mengirim lokasi juga, supaya listnya menyempit ke lokasi yang sedang dikerjakan.
    public function getdata_asset_mesin_opname(Request $request)
    {
        $where = 'o.id_so = ?';
        $bindings = [$request->id_so];

        if ($request->cbolok) {
            $where .= ' AND o.lokasi = ?';
            $bindings[] = $request->cbolok;
        }

        $data = $this->getDetailOpname($where, $bindings);

        return DataTables::of($data)->toJson();
    }

    // Export detail seluruh mesin yang diopname, mengikuti filter periode di halaman list
    public function export_excel_asset_mesin_opname(Request $request)
    {
        $tglAwal = $request->tgl_awal ?: date('Y-m-01');
        $tglAkhir = $request->tgl_akhir ?: date('Y-m-d');

        $rows = $this->getDetailOpname(
            'h.periode_tgl_awal <= ? AND h.periode_tgl_akhir >= ?',
            [$tglAkhir, $tglAwal],
            'h.no_so ASC, o.lokasi ASC, o.created_at ASC'
        );

        $excel = FastExcel::create('Stok Opname Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Stok Opname Mesin'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(['Periode : ' . date('d-m-Y', strtotime($tglAwal)) . ' s/d ' . date('d-m-Y', strtotime($tglAkhir))]);
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'No SO',
            'Tgl. Scan',
            'Lokasi',
            'Sumber',
            'Kode QR',
            'Jenis',
            'Merk',
            'Tipe',
            'Serial Number',
            'User',
            'Waktu Scan',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $sheet->writeRow([
                $no++,
                $r->no_so ?? '',
                $r->tgl_opname ?? '',
                $r->lokasi ?? '',
                $r->sumber ?? '',
                $r->kode_qr ?? '',
                $r->nm_jenis ?? '',
                $r->nm_merk ?? '',
                $r->tipe ?? '',
                $r->serial_number ?? '',
                $r->created_by ?? '',
                $r->created_at ?? '',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = 'Stok Opname Mesin ' . $tglAwal . ' sd ' . $tglAkhir . '.xlsx';

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Detail per unit hasil opname, dipakai bareng oleh listing (halaman input & modal View)
    // dan export Excel supaya kolom & sumber datanya selalu sama.
    private function getDetailOpname(string $where, array $bindings, string $orderBy = 'o.created_at DESC'): array
    {
        return DB::select("
            SELECT
                o.id,
                o.id_so,
                h.no_so,
                o.tgl_trans,
                DATE_FORMAT(o.tgl_trans, '%d %M %Y') AS tgl_opname,
                o.kode_qr,
                o.lokasi,
                o.created_by,
                DATE_FORMAT(o.created_at, '%d %M %Y %H:%i') AS created_at,
                u.sumber,
                u.nm_jenis,
                u.nm_merk,
                u.tipe,
                u.serial_number
            FROM asset_stok_opname_mesin o
            INNER JOIN asset_stok_opname_header_mesin h ON o.id_so = h.id
            LEFT JOIN (" . $this->sqlUnitMesin() . ") u ON o.kode_qr = u.kode_qr
            WHERE $where
            ORDER BY $orderBy
        ", $bindings);
    }

    public function store_asset_mesin_opname(Request $request)
    {
        $kodeQr = trim((string) $request->txtqr);
        $lokasi = $request->cbolok;
        $idSo = $request->id_so;
        $tglTrans = date('Y-m-d');

        if ($kodeQr === '' || !$lokasi || !$idSo) {
            return [
                'icon' => 'error',
                'msg' => 'No SO, Lokasi & Kode QR wajib diisi.',
                'timer' => 1500,
                'prog' => false,
            ];
        }

        $header = DB::table('asset_stok_opname_header_mesin')->where('id', $idSo)->first();

        if (!$header) {
            return [
                'icon' => 'error',
                'msg' => 'Header opname tidak ditemukan.',
                'timer' => 2000,
                'prog' => false,
            ];
        }

        // Kode QR harus terdaftar sebagai unit mesin (pembelian / sewa) yang masih aktif dipakai
        $unit = DB::select('SELECT * FROM (' . $this->sqlUnitMesin() . ') u WHERE u.kode_qr = ? LIMIT 1', [$kodeQr]);

        if (empty($unit)) {
            return [
                'icon' => 'error',
                'msg' => 'QR tidak ditemukan di data penerimaan mesin.',
                'timer' => 2000,
                'prog' => false,
            ];
        }

        // Satu unit cukup sekali dalam satu No SO, supaya tidak dobel dihitung antar lokasi
        $sudahScan = DB::table('asset_stok_opname_mesin')
            ->where('kode_qr', $kodeQr)
            ->where('id_so', $idSo)
            ->first();

        if ($sudahScan) {
            return [
                'icon' => 'error',
                'msg' => 'QR Sudah Di Scan di : ' . $sudahScan->lokasi,
                'detail' => 'Kode QR ' . $kodeQr . ' sudah discan di lokasi <b>' . $sudahScan->lokasi . '</b>'
                    . ' pada ' . date('d-m-Y H:i', strtotime($sudahScan->created_at))
                    . ' oleh ' . ($sudahScan->created_by ?: '-') . '.',
                'timer' => null,
                'prog' => false,
            ];
        }

        $timestamp = Carbon::now();

        DB::table('asset_stok_opname_mesin')->insert([
            'id_so' => $idSo,
            'tgl_trans' => $tglTrans,
            'lokasi' => $lokasi,
            'kode_qr' => $kodeQr,
            'created_by' => Auth::user()->name,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return [
            'icon' => 'success',
            'msg' => 'Data Sudah Tersimpan',
            'timer' => 1500,
            'prog' => false,
        ];
    }

    public function delete_asset_mesin_opname(Request $request)
    {
        $request->validate(['id' => 'required']);

        DB::table('asset_stok_opname_mesin')->where('id', $request->id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // Unit mesin (pembelian + sewa) yang punya kode QR & statusnya masih terpakai.
    // Filter status inilah yang menyaring kode QR dobel: unit yang sudah dikeluarkan / CUT OFF
    // tidak ikut, jadi yang tersisa unit yang masih dipakai.
    private function sqlUnitMesin(): string
    {
        $statusMesin = "'" . implode("','", self::STATUS_MESIN) . "'";
        $statusSewa = "'" . implode("','", self::STATUS_MESIN_SEWA) . "'";

        return "
            SELECT
                a.kode_qr,
                'PEMBELIAN' AS sumber,
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
                a.kode_qr,
                'SEWA' AS sumber,
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
