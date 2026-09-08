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

    public function asset_mesin_opname(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::select("
                SELECT
                    tgl_trans,
                    DATE_FORMAT(tgl_trans, '%d %M %Y') AS tgl_opname,
                    lokasi,
                    COUNT(*) AS total_mesin
                FROM asset_stok_opname_mesin
                WHERE tgl_trans BETWEEN ? AND ?
                GROUP BY tgl_trans, lokasi
                ORDER BY tgl_trans DESC, lokasi ASC
            ", [$request->tgl_awal, $request->tgl_akhir]);

            return DataTables::of($data)->toJson();
        }

        return view('asset_management.opname_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_opname',
            'containerFluid' => true,
        ]);
    }

    public function create_asset_mesin_opname(Request $request)
    {
        $lokasiList = DB::select("SELECT lokasi isi, lokasi tampil FROM master_mesin_lokasi ORDER BY lokasi ASC");

        // Dipanggil dari tombol "+" di list: lokasi & tanggalnya mengikuti baris opname yang dipilih
        // dan tidak boleh diganti, supaya scan tambahan masuk ke opname yang sama.
        return view('asset_management.create_opname_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_opname',
            'containerFluid' => true,
            'lokasiList' => $lokasiList,
            'lokasiTerkunci' => $request->lokasi,
            'tglTerkunci' => $request->tgl,
        ]);
    }

    // Daftar hasil scan pada satu tanggal & lokasi.
    // Dipakai halaman input (default tanggal hari ini) dan modal View di list opname.
    public function getdata_asset_mesin_opname(Request $request)
    {
        $tglTrans = $request->tgl ?: date('Y-m-d');

        $data = $this->getDetailOpname(
            'o.tgl_trans = ? AND o.lokasi = ?',
            [$tglTrans, $request->cbolok]
        );

        return DataTables::of($data)->toJson();
    }

    // Export detail seluruh mesin yang diopname dalam rentang tanggal di halaman list
    public function export_excel_asset_mesin_opname(Request $request)
    {
        $tglAwal = $request->tgl_awal ?: date('Y-m-01');
        $tglAkhir = $request->tgl_akhir ?: date('Y-m-d');

        $rows = $this->getDetailOpname(
            'o.tgl_trans BETWEEN ? AND ?',
            [$tglAwal, $tglAkhir],
            'o.tgl_trans ASC, o.lokasi ASC, o.created_at ASC'
        );

        $excel = FastExcel::create('Stok Opname Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Stok Opname Mesin'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(['Periode : ' . date('d-m-Y', strtotime($tglAwal)) . ' s/d ' . date('d-m-Y', strtotime($tglAkhir))]);
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Tgl. Opname',
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
            LEFT JOIN (" . $this->sqlUnitMesin() . ") u ON o.kode_qr = u.kode_qr
            WHERE $where
            ORDER BY $orderBy
        ", $bindings);
    }

    public function store_asset_mesin_opname(Request $request)
    {
        $kodeQr = trim((string) $request->txtqr);
        $lokasi = $request->cbolok;

        // Scan tambahan lewat tombol "+" masuk ke tanggal opname baris tersebut, bukan tanggal hari ini.
        // Tanggal yang tidak berformat Y-m-d diabaikan supaya tidak masuk sebagai tanggal kosong.
        $tglTrans = date('Y-m-d');
        if ($request->tgl_trans && preg_match('/^\d{4}-\d{2}-\d{2}$/', $request->tgl_trans)) {
            $tglTrans = $request->tgl_trans;
        }

        if ($kodeQr === '' || !$lokasi) {
            return [
                'icon' => 'error',
                'msg' => 'Lokasi & Kode QR wajib diisi.',
                'timer' => 1500,
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

        // Satu unit cukup sekali per tanggal opname, supaya tidak dobel dihitung antar lokasi
        $sudahScan = DB::table('asset_stok_opname_mesin')
            ->where('kode_qr', $kodeQr)
            ->where('tgl_trans', $tglTrans)
            ->first();

        if ($sudahScan) {
            return [
                'icon' => 'error',
                'msg' => 'QR Sudah Di Scan di : ' . $sudahScan->lokasi,
                'detail' => 'Kode QR ' . $kodeQr . ' sudah discan di lokasi <b>' . $sudahScan->lokasi . '</b>'
                    . ' pada jam ' . date('H:i', strtotime($sudahScan->created_at))
                    . ' oleh ' . ($sudahScan->created_by ?: '-') . '.',
                'timer' => null,
                'prog' => false,
            ];
        }

        $timestamp = Carbon::now();

        DB::table('asset_stok_opname_mesin')->insert([
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
