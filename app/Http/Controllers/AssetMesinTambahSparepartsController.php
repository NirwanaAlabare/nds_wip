<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use QrCode;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMesinTambahSparepartsController extends Controller
{
    public function asset_mesin_spareparts_tambah(Request $request)
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
                AND mi.n_code_category = '3'
                AND b.cancel = 'N'
            GROUP BY b.id_item, b.bpbno
        ),
        p AS (
            SELECT
                bpbno,
                id_item,
                sum(qty) AS tot
            FROM laravel_nds.asset_penerimaan_spareparts_mesin
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
        return view('asset_management.mesin_spareparts_tambah', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_spareparts_tambah',
            'containerFluid' => true,
            'supplierList' => $supplierList,
            'bpbList' => $bpbList,
        ]);
    }

    // Daftar transaksi penerimaan sparepart untuk tabel utama, di-join ke master rak & item/supplier (mysql_sb)
    public function get_penerimaan_spareparts_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT a.id, DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                a.bpbno_int, a.qty, a.created_by,
                r.no_rak, r.nm_rak,
                mi.itemdesc, supplier
            FROM asset_penerimaan_spareparts_mesin a
            LEFT JOIN asset_master_rak_spareparts r ON a.id_rak = r.id_rak
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE a.tgl_trans >= ? AND a.tgl_trans <= ?
            ORDER BY a.tgl_trans DESC
        ", [$tgl_awal, $tgl_akhir]);

        return DataTables::of($data)->toJson();
    }

    public function export_excel_spareparts_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT a.id, DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                a.bpbno_int, a.qty, a.created_by,
                r.no_rak, r.nm_rak,
                mi.itemdesc, supplier
            FROM asset_penerimaan_spareparts_mesin a
            LEFT JOIN asset_master_rak_spareparts r ON a.id_rak = r.id_rak
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE a.tgl_trans >= ? AND a.tgl_trans <= ?
            ORDER BY a.tgl_trans ASC
        ", [$tgl_awal, $tgl_akhir]);

        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('Penerimaan Spareparts Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Penerimaan Spareparts Mesin'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(["Periode {$tgl_awal} s/d {$tgl_akhir}"])->applyFontStyleBold();
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Tgl Transaksi',
            'BPB',
            'Supplier',
            'Nama Barang',
            'Rak',
            'Qty',
            'Dibuat Oleh',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $rak = trim(($r['no_rak'] ?? '') . ' - ' . ($r['nm_rak'] ?? ''), ' -');

            $sheet->writeRow([
                $no++,
                $r['tgl_trans_fix'] ?? '',
                $r['bpbno_int'] ?? '',
                $r['supplier'] ?? '',
                $r['itemdesc'] ?? '',
                $rak ?: '-',
                $r['qty'] ?? 0,
                $r['created_by'] ?? '',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Penerimaan Spareparts Mesin {$tgl_awal} sd {$tgl_akhir}.xlsx";

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Daftar rak sparepart untuk dropdown Rak di modal Detail Spareparts Mesin (dimuat via AJAX, bukan saat page load)
    public function get_rak_select(Request $request)
    {
        $rakList = DB::table('asset_master_rak_spareparts')
            ->select('id_rak', 'nm_rak', 'no_rak', 'desc')
            ->orderBy('nm_rak', 'ASC')
            ->get();

        return response()->json($rakList);
    }

    public function get_bpb_detail(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("					WITH p AS (
    SELECT
        bpbno,
        id_item,
        SUM(qty) AS tot_terima
    FROM laravel_nds.asset_penerimaan_spareparts_mesin
    GROUP BY bpbno, id_item
)

SELECT
    b.id,
    b.id_item,
    b.bpbno,
    b.bpbno_int,
    mi.itemdesc,
    b.qty,
    b.unit,
    b.id_supplier,
    COALESCE(p.tot_terima, 0) AS tot_terima,
    b.qty - COALESCE(p.tot_terima, 0) AS selisih
FROM bpb b
INNER JOIN mastersupplier ms
    ON ms.Id_Supplier = b.id_supplier
INNER JOIN masteritem mi
    ON mi.id_item = b.id_item
LEFT JOIN p
    ON p.bpbno = b.bpbno
    AND p.id_item = b.id_item
WHERE b.bpbno = ?
    AND mi.n_code_category = '3'
    AND b.cancel = 'N'", [$request->bpbno]);

        return DataTables::of($data)->toJson();
    }

    // Simpan penerimaan sparepart, qty dari satu baris BPB bisa displit ke beberapa rak sekaligus
    // (1 baris asset_penerimaan_spareparts_mesin per alokasi rak)
    public function store_penerimaan_spareparts_mesin(Request $request)
    {
        $request->validate([
            'id_item' => 'required',
            'id_bpb' => 'required',
            'bpbno' => 'required',
            'bpbno_int' => 'required',
            'rak' => 'required|array|min:1',
            'rak.*.id_rak' => 'required|integer|exists:asset_master_rak_spareparts,id_rak',
            'rak.*.qty' => 'required|integer|min:1',
        ]);

        // Qty BPB & total yang sudah diterima dicek ulang di server (bukan cuma di FE) supaya tidak bisa
        // over-allocate kalau ada 2 user yang menyimpan bersamaan untuk BPB yang sama
        $bpbLine = DB::connection('mysql_sb')->table('bpb')
            ->where('bpbno', $request->bpbno)
            ->where('id_item', $request->id_item)
            ->value('qty');

        $totTerima = DB::table('asset_penerimaan_spareparts_mesin')
            ->where('bpbno', $request->bpbno)
            ->where('id_item', $request->id_item)
            ->sum('qty');

        $selisih = (int) $bpbLine - (int) $totTerima;
        $totAlokasi = array_sum(array_column($request->rak, 'qty'));

        if ($totAlokasi > $selisih) {
            return response()->json([
                'status' => 'error',
                'message' => "Total qty yang dialokasikan ({$totAlokasi}) melebihi sisa qty yang belum diterima ({$selisih}).",
            ], 422);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $rows = [];
        foreach ($request->rak as $alokasi) {
            $rows[] = [
                'tgl_trans' => $timestamp->format('Y-m-d'),
                'id_item' => $request->id_item,
                'id_bpb' => $request->id_bpb,
                'bpbno' => $request->bpbno,
                'bpbno_int' => $request->bpbno_int,
                'id_rak' => $alokasi['id_rak'],
                'qty' => $alokasi['qty'],
                'created_by' => $user,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('asset_penerimaan_spareparts_mesin')->insert($rows);

        return response()->json([
            'status' => 'success',
            'message' => 'Sparepart berhasil diterima sebanyak ' . $totAlokasi . ' ' . ($request->unit ?? '') . '.',
        ]);
    }
}
