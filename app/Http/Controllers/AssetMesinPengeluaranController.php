<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use Illuminate\Support\Facades\Auth;

class AssetMesinPengeluaranController extends Controller
{
    public function asset_mesin_pengeluaran(Request $request)
    {
        // For non-AJAX (initial page load)
        return view('asset_management.mesin_pengeluaran', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_pengeluaran',
            'containerFluid' => true
        ]);
    }

    // Daftar transaksi pengeluaran (replacement) mesin pembelian untuk tabel utama, di-join ke asset_penerimaan_mesin
    // (id_penerimaan -> id) supaya detail mesin per unit-nya ikut tampil
    public function get_pengeluaran_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT p.id, p.created_by, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_keluar_fix,
                a.id as id_penerimaan, a.serial_number, a.status, d.nm_jenis, e.nm_merk, c.tipe, a.bpbno_int,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') as tgl_terima_fix,
                mi.itemdesc, supplier
            FROM asset_pengeluaran_mesin p
            LEFT JOIN asset_penerimaan_mesin a ON p.id_penerimaan = a.id
            LEFT JOIN asset_master_jenis_mesin c ON a.id_jenis = c.id_jenis
            LEFT JOIN asset_master_kd_jenis d ON c.kd_jenis = d.kd_jenis
            LEFT JOIN asset_master_kd_merk e ON c.kd_merk = e.kd_merk
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE p.tgl_trans >= ? AND p.tgl_trans <= ?
            ORDER BY p.tgl_trans DESC
        ", [$tgl_awal, $tgl_akhir]);

        return DataTables::of($data)->toJson();
    }

    // Export Excel daftar transaksi pengeluaran (replacement) mesin pembelian, sama datanya dengan tabel utama
    public function export_excel_pengeluaran_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT p.id, p.created_by, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_keluar_fix,
                a.serial_number, a.status, d.nm_jenis, e.nm_merk, c.tipe, a.bpbno_int,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') as tgl_terima_fix,
                mi.itemdesc, supplier
            FROM asset_pengeluaran_mesin p
            LEFT JOIN asset_penerimaan_mesin a ON p.id_penerimaan = a.id
            LEFT JOIN asset_master_jenis_mesin c ON a.id_jenis = c.id_jenis
            LEFT JOIN asset_master_kd_jenis d ON c.kd_jenis = d.kd_jenis
            LEFT JOIN asset_master_kd_merk e ON c.kd_merk = e.kd_merk
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE p.tgl_trans >= ? AND p.tgl_trans <= ?
            ORDER BY p.tgl_trans ASC
        ", [$tgl_awal, $tgl_akhir]);

        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('Pengeluaran Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Pengeluaran Mesin (Pembelian)'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(["Periode {$tgl_awal} s/d {$tgl_akhir}"])->applyFontStyleBold();
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Tgl Keluar',
            'Serial Number',
            'Nama Mesin',
            'Jenis',
            'Merk',
            'Tipe',
            'BPB',
            'Tgl Terima',
            'Supplier',
            'Status',
            'Dibuat Oleh',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $sheet->writeRow([
                $no++,
                $r['tgl_keluar_fix'] ?? '',
                $r['serial_number'] ?? '',
                $r['itemdesc'] ?? '',
                $r['nm_jenis'] ?? '',
                $r['nm_merk'] ?? '',
                $r['tipe'] ?? '',
                $r['bpbno_int'] ?? '',
                $r['tgl_terima_fix'] ?? '',
                $r['supplier'] ?? '',
                $r['status'] ?? '',
                $r['created_by'] ?? '',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Pengeluaran Mesin {$tgl_awal} sd {$tgl_akhir}.xlsx";

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Daftar unit mesin (pembelian) yang masih aktif (belum di-replace), untuk dipilih saat tombol New dipakai
    public function get_active_unit_mesin(Request $request)
    {
        $units = DB::select("
            SELECT a.id, a.serial_number, d.nm_jenis, e.nm_merk, c.tipe,
                a.bpbno_int, a.tgl_trans, mi.itemdesc, supplier
            FROM asset_penerimaan_mesin a
            LEFT JOIN asset_master_jenis_mesin c ON a.id_jenis = c.id_jenis
            LEFT JOIN asset_master_kd_jenis d ON c.kd_jenis = d.kd_jenis
            LEFT JOIN asset_master_kd_merk e ON c.kd_merk = e.kd_merk
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE a.status = 'IDLE'
            ORDER BY a.serial_number ASC
        ");

        return response()->json($units);
    }

    // Cari item (sparepart) dari master item ERP (mysql_sb), dipakai di modal sparepart untuk pilih item yang diservice
    public function search_item_sparepart(Request $request)
    {
        $keyword = $request->q;

        $items = DB::connection('mysql_sb')->table('masteritem')
            ->select('id_item', 'itemdesc', 'goods_code')
            ->where('non_aktif', 'N')
            ->where('n_code_category', '3')
            ->where(function ($q) use ($keyword) {
                $q->where('itemdesc', 'like', "%{$keyword}%")
                    ->orWhere('goods_code', 'like', "%{$keyword}%");
            })
            ->limit(50)
            ->get();

        return response()->json($items);
    }

    // Daftar sparepart yang sudah dicatat untuk satu unit (status SERVICE), ditampilkan di modal sparepart
    // dari tabel riwayat utama setelah transaksi pengeluarannya tersimpan
    public function get_pengeluaran_mesin_service(Request $request)
    {
        $request->validate([
            'id_penerimaan' => 'required|integer',
        ]);

        $items = DB::table('asset_pengeluaran_mesin_service')
            ->select('id', 'id_item', 'desc', 'created_by')
            ->where('id_penerimaan', $request->id_penerimaan)
            ->orderByDesc('id')
            ->get();

        // id_item merujuk ke mysql_sb.masteritem (koneksi DB beda dari tabel di atas), jadi itemdesc/goods_code
        // diambil terpisah lalu di-map manual
        $itemMap = DB::connection('mysql_sb')->table('masteritem')
            ->whereIn('id_item', $items->pluck('id_item')->unique()->values())
            ->get()
            ->keyBy('id_item');

        $items->transform(function ($row) use ($itemMap) {
            $row->itemdesc = $itemMap[$row->id_item]->itemdesc ?? null;
            $row->goods_code = $itemMap[$row->id_item]->goods_code ?? null;
            return $row;
        });

        return response()->json($items);
    }

    // Tambah satu baris sparepart untuk unit yang berstatus SERVICE (dipanggil langsung dari modal sparepart,
    // tersimpan seketika tanpa menunggu form lain)
    public function store_pengeluaran_mesin_service(Request $request)
    {
        $request->validate([
            'id_penerimaan' => 'required|integer|exists:asset_penerimaan_mesin,id',
            'id_item' => 'required|integer',
            'desc' => 'nullable|string|max:255',
        ]);

        DB::table('asset_pengeluaran_mesin_service')->insert([
            'id_penerimaan' => $request->id_penerimaan,
            'id_item' => $request->id_item,
            'tgl_trans' => Carbon::now()->format('Y-m-d'),
            'desc' => $request->desc,
            'created_by' => Auth::user()->name,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sparepart berhasil ditambahkan.',
        ]);
    }

    // Hapus satu baris sparepart yang sudah tercatat
    public function delete_pengeluaran_mesin_service($id)
    {
        DB::table('asset_pengeluaran_mesin_service')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sparepart berhasil dihapus.',
        ]);
    }

    // Simpan unit mesin (pembelian) yang dipilih sebagai catatan pengeluaran/replacement (cukup referensi id unit-nya)
    public function store_pengeluaran_mesin(Request $request)
    {
        $request->validate([
            'units' => 'required|array|min:1',
            'units.*.id' => 'required|integer|exists:asset_penerimaan_mesin,id',
            'units.*.status' => 'required|in:REPLACE,BREAKDOWN,DISPOSE,SELL,RETUR,SERVICE',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        DB::transaction(function () use ($request, $timestamp, $user) {
            // Format no_trans: MSN/OUT/<bulan+tahun 2 digit>/<urutan 4 digit>, urutan reset tiap bulan
            // (otomatis reset ke 0001 juga saat ganti tahun, karena periode menyertakan tahun)
            $periode = $timestamp->format('my'); // contoh: 0626 untuk Juni 2026
            $prefix = "MSN/OUT/{$periode}/";

            $lastNoTrans = DB::table('asset_pengeluaran_mesin')
                ->where('no_trans', 'like', $prefix . '%')
                ->orderByDesc('no_trans')
                ->lockForUpdate()
                ->value('no_trans');

            $urutan = $lastNoTrans ? (int) substr($lastNoTrans, -4) : 0;

            $rows = [];
            foreach ($request->units as $unit) {
                $urutan++;
                $rows[] = [
                    'id_penerimaan' => $unit['id'],
                    'no_trans' => $prefix . sprintf('%04d', $urutan),
                    'tgl_trans' => $timestamp->format('Y-m-d'),
                    'created_by' => $user,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('asset_pengeluaran_mesin')->insert($rows);

            // Unit yang sudah dikeluarkan ditandai sesuai status yang dipilih per unit, supaya tidak muncul lagi di dropdown pemilihan
            foreach ($request->units as $unit) {
                DB::table('asset_penerimaan_mesin')
                    ->where('id', $unit['id'])
                    ->update([
                        'status' => $unit['status'],
                        'updated_at' => $timestamp,
                    ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => count($request->units) . ' unit mesin berhasil dikeluarkan.',
        ]);
    }
}
