<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMasterRakSparepartController extends Controller
{
    public function asset_master_rak_sparepart(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("
                SELECT id_rak, nm_rak, no_rak, `desc`
                FROM asset_master_rak_spareparts
                ORDER BY nm_rak ASC
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.master_rak_sparepart', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_master_rak_sparepart',
            'containerFluid' => true,
        ]);
    }

    public function store_rak_sparepart(Request $request)
    {
        $request->validate([
            'nm_rak' => 'required',
            'no_rak' => 'required',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $nm_rak = strtoupper($request->nm_rak);
        $no_rak = strtoupper($request->no_rak);
        $desc = strtoupper($request->desc);

        $exists = DB::table('asset_master_rak_spareparts')
            ->where('nm_rak', $nm_rak)
            ->where('no_rak', $no_rak)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rak Sparepart dengan Nama Rak dan No Rak tersebut sudah ada.',
            ], 409);
        }

        DB::insert("INSERT INTO asset_master_rak_spareparts (
            nm_rak,
            no_rak,
            `desc`,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?)", [
            $nm_rak,
            $no_rak,
            $desc,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rak Sparepart berhasil ditambahkan',
        ]);
    }

    public function show_rak_sparepart(Request $request)
    {
        $data = DB::select("SELECT id_rak, nm_rak, no_rak, `desc` FROM asset_master_rak_spareparts WHERE id_rak = ?", [$request->id]);
        return json_encode($data[0] ?? null);
    }

    public function update_rak_sparepart(Request $request)
    {
        $request->validate([
            'id_rak' => 'required',
            'nm_rak' => 'required',
            'no_rak' => 'required',
        ]);

        $timestamp = Carbon::now();
        $nm_rak = strtoupper($request->nm_rak);
        $no_rak = strtoupper($request->no_rak);
        $desc = strtoupper($request->desc);

        $exists = DB::table('asset_master_rak_spareparts')
            ->where('nm_rak', $nm_rak)
            ->where('no_rak', $no_rak)
            ->where('id_rak', '!=', $request->id_rak)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rak Sparepart dengan Nama Rak dan No Rak tersebut sudah ada.',
            ], 409);
        }

        DB::update("UPDATE asset_master_rak_spareparts
            SET nm_rak = ?, no_rak = ?, `desc` = ?, updated_at = ?
            WHERE id_rak = ?", [
            $nm_rak,
            $no_rak,
            $desc,
            $timestamp,
            $request->id_rak,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rak Sparepart berhasil diupdate',
        ]);
    }

    public function delete_rak_sparepart(Request $request)
    {
        $adaTransaksi = DB::table('asset_penerimaan_spareparts_mesin')->where('id_rak', $request->id)->exists()
            || DB::table('asset_pengeluaran_spareparts_mesin')->where('id_rak', $request->id)->exists();

        if ($adaTransaksi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rak Sparepart tidak dapat dihapus karena sudah memiliki riwayat transaksi (masuk/keluar).',
            ], 422);
        }

        DB::delete("DELETE FROM asset_master_rak_spareparts WHERE id_rak = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rak Sparepart berhasil dihapus',
        ]);
    }

    // Stok saat ini per id_item di rak tertentu (total masuk - total keluar), dipakai oleh modal History di Master Rak Sparepart
    public function get_stok_rak(Request $request)
    {
        $request->validate([
            'id_rak' => 'required|integer',
        ]);

        $rows = DB::select("
            WITH masuk AS (
                SELECT id_item, SUM(qty) AS tot_masuk
                FROM asset_penerimaan_spareparts_mesin
                WHERE id_rak = ?
                GROUP BY id_item
            ),
            keluar AS (
                SELECT id_item, SUM(qty) AS tot_keluar
                FROM asset_pengeluaran_spareparts_mesin
                WHERE id_rak = ?
                GROUP BY id_item
            )
            SELECT m.id_item, mi.itemdesc, mi.goods_code,
                m.tot_masuk,
                COALESCE(k.tot_keluar, 0) AS tot_keluar,
                m.tot_masuk - COALESCE(k.tot_keluar, 0) AS stok
            FROM masuk m
            LEFT JOIN keluar k ON k.id_item = m.id_item
            INNER JOIN signalbit_erp.masteritem mi ON mi.id_item = m.id_item
            ORDER BY mi.itemdesc ASC
        ", [$request->id_rak, $request->id_rak]);

        return DataTables::of($rows)->toJson();
    }

    // Riwayat transaksi masuk & keluar (digabung) untuk rak tertentu, dipakai oleh modal History di Master Rak Sparepart
    public function get_history_rak(Request $request)
    {
        $request->validate([
            'id_rak' => 'required|integer',
        ]);

        $data = DB::select("
            SELECT 'MASUK' AS jenis, p.tgl_trans, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                p.qty, p.created_by, p.id_item, mi.itemdesc, p.bpbno_int,
                NULL AS enroll_id_mekanik, NULL AS serial_number
            FROM asset_penerimaan_spareparts_mesin p
            LEFT JOIN signalbit_erp.masteritem mi ON p.id_item = mi.id_item
            WHERE p.id_rak = ?

            UNION ALL

            SELECT 'KELUAR' AS jenis, k.tgl_trans, DATE_FORMAT(k.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                k.qty, k.created_by, k.id_item, mi.itemdesc, bpb.bpbno_int,
                k.enroll_id_mekanik, um.serial_number
            FROM asset_pengeluaran_spareparts_mesin k
            LEFT JOIN signalbit_erp.masteritem mi ON k.id_item = mi.id_item
            LEFT JOIN signalbit_erp.bpb bpb ON k.id_bpb = bpb.id
            LEFT JOIN asset_penerimaan_mesin um ON k.id_penerimaan_mesin = um.id
            WHERE k.id_rak = ?

            ORDER BY tgl_trans DESC
        ", [$request->id_rak, $request->id_rak]);

        $this->mapMekanikName($data);

        return DataTables::of($data)->toJson();
    }

    // Export Excel laporan satu rak: sheet Stok Saat Ini + sheet Riwayat Transaksi (masuk & keluar)
    public function export_excel_rak(Request $request)
    {
        $request->validate([
            'id_rak' => 'required|integer',
        ]);

        $rak = DB::selectOne("SELECT nm_rak, no_rak, `desc` FROM asset_master_rak_spareparts WHERE id_rak = ?", [$request->id_rak]);
        $rakLabel = trim(($rak->nm_rak ?? '') . ' - ' . ($rak->no_rak ?? ''), ' -') ?: "Rak {$request->id_rak}";

        $stok = DB::select("
            WITH masuk AS (
                SELECT id_item, SUM(qty) AS tot_masuk
                FROM asset_penerimaan_spareparts_mesin
                WHERE id_rak = ?
                GROUP BY id_item
            ),
            keluar AS (
                SELECT id_item, SUM(qty) AS tot_keluar
                FROM asset_pengeluaran_spareparts_mesin
                WHERE id_rak = ?
                GROUP BY id_item
            )
            SELECT m.id_item, mi.itemdesc, mi.goods_code,
                m.tot_masuk,
                COALESCE(k.tot_keluar, 0) AS tot_keluar,
                m.tot_masuk - COALESCE(k.tot_keluar, 0) AS stok
            FROM masuk m
            LEFT JOIN keluar k ON k.id_item = m.id_item
            INNER JOIN signalbit_erp.masteritem mi ON mi.id_item = m.id_item
            ORDER BY mi.itemdesc ASC
        ", [$request->id_rak, $request->id_rak]);

        $riwayat = DB::select("
            SELECT 'MASUK' AS jenis, p.tgl_trans, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                p.qty, p.created_by, p.id_item, mi.itemdesc, p.bpbno_int,
                NULL AS enroll_id_mekanik, NULL AS serial_number
            FROM asset_penerimaan_spareparts_mesin p
            LEFT JOIN signalbit_erp.masteritem mi ON p.id_item = mi.id_item
            WHERE p.id_rak = ?

            UNION ALL

            SELECT 'KELUAR' AS jenis, k.tgl_trans, DATE_FORMAT(k.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                k.qty, k.created_by, k.id_item, mi.itemdesc, bpb.bpbno_int,
                k.enroll_id_mekanik, um.serial_number
            FROM asset_pengeluaran_spareparts_mesin k
            LEFT JOIN signalbit_erp.masteritem mi ON k.id_item = mi.id_item
            LEFT JOIN signalbit_erp.bpb bpb ON k.id_bpb = bpb.id
            LEFT JOIN asset_penerimaan_mesin um ON k.id_penerimaan_mesin = um.id
            WHERE k.id_rak = ?

            ORDER BY tgl_trans DESC
        ", [$request->id_rak, $request->id_rak]);

        $this->mapMekanikName($riwayat);

        $excel = FastExcel::create(['Stok Saat Ini', 'Riwayat Transaksi']);

        $sheetStok = $excel->sheet('Stok Saat Ini');
        $sheetStok->writeRow(["Laporan Stok Rak Sparepart - {$rakLabel}"])->applyFontStyleBold()->applyFontSize(14);
        $sheetStok->writeRow([]);
        $sheetStok->writeRow([
            'No', 'Nama Barang', 'Goods Code', 'Total Masuk', 'Total Keluar', 'Stok Saat Ini',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($stok as $r) {
            $sheetStok->writeRow([
                $no++,
                $r->itemdesc ?? '-',
                $r->goods_code ?? '-',
                $r->tot_masuk ?? 0,
                $r->tot_keluar ?? 0,
                $r->stok ?? 0,
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $sheetRiwayat = $excel->sheet('Riwayat Transaksi');
        $sheetRiwayat->writeRow(["Riwayat Transaksi Rak Sparepart - {$rakLabel}"])->applyFontStyleBold()->applyFontSize(14);
        $sheetRiwayat->writeRow([]);
        $sheetRiwayat->writeRow([
            'No', 'Tanggal', 'Jenis', 'Nama Barang', 'Qty', 'BPB', 'Mekanik', 'Serial Number', 'Dibuat Oleh',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($riwayat as $r) {
            $sheetRiwayat->writeRow([
                $no++,
                $r->tgl_trans_fix ?? '',
                $r->jenis ?? '',
                $r->itemdesc ?? '-',
                $r->qty ?? 0,
                $r->bpbno_int ?? '-',
                $r->mekanik_name ?? '-',
                $r->serial_number ?? '-',
                $r->created_by ?? '-',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Rak Sparepart {$rakLabel}.xlsx";
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Export Excel laporan stok sparepart global, per id_item dijumlah dari semua rak
    public function export_excel_stok_global()
    {
        $rows = DB::select("
            WITH masuk AS (
                SELECT id_item, SUM(qty) AS tot_masuk
                FROM asset_penerimaan_spareparts_mesin
                GROUP BY id_item
            ),
            keluar AS (
                SELECT id_item, SUM(qty) AS tot_keluar
                FROM asset_pengeluaran_spareparts_mesin
                GROUP BY id_item
            )
            SELECT m.id_item, mi.itemdesc, mi.goods_code,
                m.tot_masuk,
                COALESCE(k.tot_keluar, 0) AS tot_keluar,
                m.tot_masuk - COALESCE(k.tot_keluar, 0) AS stok
            FROM masuk m
            LEFT JOIN keluar k ON k.id_item = m.id_item
            INNER JOIN signalbit_erp.masteritem mi ON mi.id_item = m.id_item
            ORDER BY mi.itemdesc ASC
        ");

        $excel = FastExcel::create('Stok Sparepart Global');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Stok Sparepart Global (Semua Rak)'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow([]);
        $sheet->writeRow([
            'No', 'Nama Barang', 'Goods Code', 'Total Masuk', 'Total Keluar', 'Stok Saat Ini',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $sheet->writeRow([
                $no++,
                $r->itemdesc ?? '-',
                $r->goods_code ?? '-',
                $r->tot_masuk ?? 0,
                $r->tot_keluar ?? 0,
                $r->stok ?? 0,
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Stok Sparepart Global.xlsx";
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Tempel nama mekanik (employee_atribut ada di koneksi mysql_hris, host beda dari default) ke tiap baris hasil query keluar
    private function mapMekanikName(array $data): void
    {
        $enrollIds = collect($data)->pluck('enroll_id_mekanik')->filter()->unique()->values();

        $mekanikMap = $enrollIds->isEmpty()
            ? collect()
            : DB::connection('mysql_hris')->table('employee_atribut')
            ->whereIn('enroll_id', $enrollIds)
            ->pluck('employee_name', 'enroll_id');

        foreach ($data as $row) {
            $row->mekanik_name = $mekanikMap[$row->enroll_id_mekanik] ?? null;
        }
    }
}
