<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use PDF;
use QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMesinSewaPengeluaranController extends Controller
{


    public function asset_mesin_sewa_pengeluaran(Request $request)
    {
        $tgl_trans = '2026-05-01';
        $supplierList = DB::connection('mysql_sb')->table('mastersupplier')
            ->select('id_supplier', 'Supplier')
            ->where('tipe_sup', '=', 'S')
            ->orderBy('Supplier', 'ASC')
            ->get();

        $bpbList = DB::connection('mysql_sb')->select("
        WITH bpb AS (
            SELECT
                b.id_item,
                b.bpbdate,
                b.bpbno,
                b.bpbno_int,
                ms.supplier,
                SUM(b.qty) AS qty
            FROM bpb b
            INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            WHERE b.bpbdate >= '$tgl_trans'
                AND b.bpbno LIKE 'N%'
                AND mi.n_code_category = '6'
                AND b.cancel = 'N'
            GROUP BY b.id_item, b.bpbno
        ),
        p AS (
            SELECT
                bpbno,
                id_item,
                COUNT(*) AS tot
            FROM laravel_nds.asset_penerimaan_mesin_sewa
            GROUP BY bpbno, id_item
        )

        SELECT
            bpb.*,
            COALESCE(p.tot, 0) AS tot
        FROM bpb
        LEFT JOIN p
            ON bpb.bpbno = p.bpbno
            AND bpb.id_item = p.id_item
        WHERE bpb.qty > COALESCE(p.tot, 0)
        GROUP BY bpbno
        ORDER BY bpbdate ASC;
        ");


        // For non-AJAX (initial page load)
        return view('asset_management.mesin_sewa_pengeluaran', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_sewa_pengeluaran',
            'containerFluid' => true
        ]);
    }

    // Daftar transaksi pengeluaran (cut off) mesin sewa untuk tabel utama, di-join ke asset_penerimaan_mesin_sewa
    // (id_penerimaan_sewa -> id) supaya detail mesin per unit-nya ikut tampil
    public function get_pengeluaran_mesin_sewa(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT p.id, p.created_by, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_keluar_fix,
                a.serial_number, a.nm_jenis, a.nm_merk, a.tipe, a.bpbno_int,
                DATE_FORMAT(a.tgl_akhir_kontrak, '%d-%m-%Y') as tgl_akhir_kontrak_fix,
                mi.itemdesc, supplier
            FROM asset_pengeluaran_mesin_sewa p
            LEFT JOIN asset_penerimaan_mesin_sewa a ON p.id_penerimaan_sewa = a.id
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE p.tgl_trans >= ? AND p.tgl_trans <= ?
            ORDER BY p.tgl_trans DESC
        ", [$tgl_awal, $tgl_akhir]);

        return DataTables::of($data)->toJson();
    }

    // Export Excel daftar transaksi pengeluaran (cut off) mesin sewa, sama datanya dengan tabel utama
    public function export_excel_pengeluaran_mesin_sewa(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT p.id, p.created_by, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_keluar_fix,
                a.serial_number, a.nm_jenis, a.nm_merk, a.tipe, a.bpbno_int,
                DATE_FORMAT(a.tgl_akhir_kontrak, '%d-%m-%Y') as tgl_akhir_kontrak_fix,
                DATE_FORMAT(a.tgl_awal_kontrak, '%d-%m-%Y') as tgl_awal_kontrak_fix,
                mi.itemdesc, supplier
            FROM asset_pengeluaran_mesin_sewa p
            LEFT JOIN asset_penerimaan_mesin_sewa a ON p.id_penerimaan_sewa = a.id
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE p.tgl_trans >= ? AND p.tgl_trans <= ?
            ORDER BY p.tgl_trans ASC
        ", [$tgl_awal, $tgl_akhir]);

        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('Pengeluaran Mesin Sewa');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Cut Off Mesin Sewa'])->applyFontStyleBold()->applyFontSize(16);
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
            'Tgl Akhir Kontrak',
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
                $r['tgl_awal_kontrak_fix'] ?? '',
                $r['supplier'] ?? '',
                $r['tgl_akhir_kontrak_fix'] ?? '',
                $r['created_by'] ?? '',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Pengeluaran Mesin Sewa {$tgl_awal} sd {$tgl_akhir}.xlsx";

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Daftar unit mesin sewa yang masih aktif & kontraknya belum berakhir, untuk dipilih saat tombol New dipakai
    public function get_active_unit_mesin_sewa(Request $request)
    {
        $units = DB::select("
            SELECT a.id, a.serial_number, a.nm_jenis, a.nm_merk, a.tipe,
                a.bpbno_int, a.tgl_awal_kontrak, a.tgl_akhir_kontrak, mi.itemdesc, supplier
            FROM asset_penerimaan_mesin_sewa a
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE a.status = 'ACTIVE' AND a.tgl_akhir_kontrak >= CURDATE()
            ORDER BY a.serial_number ASC
        ");

        return response()->json($units);
    }

    // Simpan unit mesin sewa yang dipilih sebagai catatan pengeluaran (cukup referensi id unit-nya)
    public function store_pengeluaran_mesin_sewa(Request $request)
    {
        $request->validate([
            'id_unit' => 'required|array|min:1',
            'id_unit.*' => 'required|integer|exists:asset_penerimaan_mesin_sewa,id',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        DB::transaction(function () use ($request, $timestamp, $user) {
            // Format no_trans: RENT/OUT/<bulan+tahun 2 digit>/<urutan 3 digit>, urutan reset tiap bulan
            $periode = $timestamp->format('my'); // contoh: 0626 untuk Juni 2026
            $prefix = "RENT/OUT/{$periode}/";

            $lastNoTrans = DB::table('asset_pengeluaran_mesin_sewa')
                ->where('no_trans', 'like', $prefix . '%')
                ->orderByDesc('no_trans')
                ->lockForUpdate()
                ->value('no_trans');

            $urutan = $lastNoTrans ? (int) substr($lastNoTrans, -3) : 0;

            $rows = [];
            foreach ($request->id_unit as $id) {
                $urutan++;
                $rows[] = [
                    'id_penerimaan_sewa' => $id,
                    'no_trans' => $prefix . sprintf('%03d', $urutan),
                    'tgl_trans' => $timestamp->format('Y-m-d'),
                    'created_by' => $user,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::table('asset_pengeluaran_mesin_sewa')->insert($rows);

            // Unit yang sudah dikeluarkan tidak lagi ACTIVE, supaya tidak muncul lagi di dropdown pemilihan
            DB::table('asset_penerimaan_mesin_sewa')
                ->whereIn('id', $request->id_unit)
                ->update([
                    'status' => 'CUTT OFF',
                    'updated_at' => $timestamp,
                ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => count($request->id_unit) . ' unit mesin sewa berhasil dikeluarkan.',
        ]);
    }
}
