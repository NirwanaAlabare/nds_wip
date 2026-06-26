<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use Illuminate\Support\Facades\Auth;

class AssetMesinPengeluaranSparepartsController extends Controller
{
    public function asset_mesin_spareparts_pengeluaran(Request $request)
    {
        // For non-AJAX (initial page load)
        return view('asset_management.mesin_spareparts_pengeluaran', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_spareparts_pengeluaran',
            'containerFluid' => true,
        ]);
    }

    // Daftar transaksi pengeluaran sparepart untuk tabel utama, di-join ke rak, BPB/item (mysql_sb) & unit mesin tujuan.
    // Nama mekanik diambil terpisah dari koneksi mysql_hris (host beda, tidak bisa di-join langsung lewat SQL)
    public function get_pengeluaran_spareparts_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT p.id, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                p.qty, p.created_by, p.enroll_id_mekanik,
                r.no_rak, r.nm_rak, r.`desc` as rak_desc,
                mi.itemdesc,
                bpb.bpbno_int,
                um.serial_number,
                umi.itemdesc as mesin_desc,
                umj.nm_jenis, umm.nm_merk, umjm.tipe
            FROM asset_pengeluaran_spareparts_mesin p
            LEFT JOIN asset_master_rak_spareparts r ON p.id_rak = r.id_rak
            LEFT JOIN signalbit_erp.bpb bpb ON p.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.masteritem mi ON p.id_item = mi.id_item
            LEFT JOIN asset_penerimaan_mesin um ON p.id_penerimaan_mesin = um.id
            LEFT JOIN signalbit_erp.masteritem umi ON um.id_item = umi.id_item
            LEFT JOIN asset_master_jenis_mesin umjm ON um.id_jenis = umjm.id_jenis
            LEFT JOIN asset_master_kd_jenis umj ON umjm.kd_jenis = umj.kd_jenis
            LEFT JOIN asset_master_kd_merk umm ON umjm.kd_merk = umm.kd_merk
            WHERE p.tgl_trans >= ? AND p.tgl_trans <= ?
            ORDER BY p.tgl_trans DESC
        ", [$tgl_awal, $tgl_akhir]);

        $this->mapMekanikName($data);

        return DataTables::of($data)->toJson();
    }

    public function export_excel_pengeluaran_spareparts_mesin(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("
            SELECT p.id, DATE_FORMAT(p.tgl_trans, '%d-%m-%Y') as tgl_trans_fix,
                p.qty, p.created_by, p.enroll_id_mekanik,
                r.no_rak, r.nm_rak, r.`desc` as rak_desc,
                mi.itemdesc,
                bpb.bpbno_int,
                um.serial_number,
                umi.itemdesc as mesin_desc,
                umj.nm_jenis, umm.nm_merk, umjm.tipe
            FROM asset_pengeluaran_spareparts_mesin p
            LEFT JOIN asset_master_rak_spareparts r ON p.id_rak = r.id_rak
            LEFT JOIN signalbit_erp.bpb bpb ON p.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.masteritem mi ON p.id_item = mi.id_item
            LEFT JOIN asset_penerimaan_mesin um ON p.id_penerimaan_mesin = um.id
            LEFT JOIN signalbit_erp.masteritem umi ON um.id_item = umi.id_item
            LEFT JOIN asset_master_jenis_mesin umjm ON um.id_jenis = umjm.id_jenis
            LEFT JOIN asset_master_kd_jenis umj ON umjm.kd_jenis = umj.kd_jenis
            LEFT JOIN asset_master_kd_merk umm ON umjm.kd_merk = umm.kd_merk
            WHERE p.tgl_trans >= ? AND p.tgl_trans <= ?
            ORDER BY p.tgl_trans ASC
        ", [$tgl_awal, $tgl_akhir]);

        $this->mapMekanikName($data);

        $excel = FastExcel::create('Pengeluaran Spareparts Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Pengeluaran Spareparts Mesin'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(["Periode {$tgl_awal} s/d {$tgl_akhir}"])->applyFontStyleBold();
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Tgl Keluar',
            'Nama Barang',
            'Rak',
            'Serial Number',
            'Jenis',
            'Merk',
            'Unit Mesin',
            'Mekanik',
            'Qty',
            'BPB',
            'Dibuat Oleh',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($data as $r) {
            $rak = trim(($r->no_rak ?? '') . ' - ' . ($r->nm_rak ?? ''), ' -');
            $rak .= $r->rak_desc ? " ({$r->rak_desc})" : '';
            $mesin = trim(($r->mesin_desc ?? '') . ($r->tipe ? " ({$r->tipe})" : ''));

            $sheet->writeRow([
                $no++,
                $r->tgl_trans_fix ?? '',
                $r->itemdesc ?? '',
                $rak ?: '-',
                $r->serial_number ?? '-',
                $r->nm_jenis ?? '-',
                $r->nm_merk ?? '-',
                $mesin ?: '-',
                $r->mekanik_name ?? '-',
                $r->qty ?? 0,
                $r->bpbno_int ?? '-',
                $r->created_by ?? '',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Pengeluaran Spareparts Mesin {$tgl_awal} sd {$tgl_akhir}.xlsx";

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Tempel nama mekanik (employee_atribut ada di koneksi mysql_hris, host beda dari default) ke tiap baris hasil query di atas
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

    // Daftar sparepart yang masih ada stok (dijumlah dari semua rak) untuk dropdown Sparepart di modal New,
    // dipilih dulu sebelum user menentukan mau ambil dari rak mana
    public function get_sparepart_select(Request $request)
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
                m.tot_masuk - COALESCE(k.tot_keluar, 0) AS stok
            FROM masuk m
            LEFT JOIN keluar k ON k.id_item = m.id_item
            INNER JOIN signalbit_erp.masteritem mi ON mi.id_item = m.id_item
            WHERE m.tot_masuk - COALESCE(k.tot_keluar, 0) > 0
            ORDER BY mi.itemdesc ASC
        ");

        return response()->json($rows);
    }

    // Daftar rak yang masih ada stok sparepart terpilih, dimuat setelah user pilih Sparepart, supaya user bisa
    // menentukan mau ambil dari rak mana
    public function get_rak_by_sparepart(Request $request)
    {
        $request->validate([
            'id_item' => 'required|integer',
        ]);

        $rows = DB::select("
            WITH masuk AS (
                SELECT id_rak, SUM(qty) AS tot_masuk
                FROM asset_penerimaan_spareparts_mesin
                WHERE id_item = ?
                GROUP BY id_rak
            ),
            keluar AS (
                SELECT id_rak, SUM(qty) AS tot_keluar
                FROM asset_pengeluaran_spareparts_mesin
                WHERE id_item = ?
                GROUP BY id_rak
            )
            SELECT m.id_rak, r.no_rak, r.nm_rak, r.`desc`,
                m.tot_masuk - COALESCE(k.tot_keluar, 0) AS stok
            FROM masuk m
            LEFT JOIN keluar k ON k.id_rak = m.id_rak
            INNER JOIN asset_master_rak_spareparts r ON r.id_rak = m.id_rak
            WHERE m.tot_masuk - COALESCE(k.tot_keluar, 0) > 0
            ORDER BY r.nm_rak ASC
        ", [$request->id_item, $request->id_item]);

        return response()->json($rows);
    }

    // Daftar unit mesin (pembelian) yang masih aktif (belum di-replace), untuk dropdown Unit Mesin di modal New
    public function get_unit_mesin_select(Request $request)
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
            WHERE a.status IN ('ACTIVE', 'REPLACE', 'IDLE', 'BREAKDOWN')
            ORDER BY a.serial_number ASC
        ");

        return response()->json($units);
    }

    // Daftar mekanik (employee aktif) untuk dropdown, dari koneksi mysql_hris
    public function get_mekanik_select(Request $request)
    {
        $mekanikList = DB::connection('mysql_hris')->table('employee_atribut')
            ->select('enroll_id', 'employee_name')
            ->where('department_name', 'MECHANIC')
            ->where('status_aktif', 'AKTIF')
            ->orderBy('employee_name', 'ASC')
            ->get();

        return response()->json($mekanikList);
    }

    // Simpan pengeluaran sparepart (bisa banyak baris sekaligus dari satu modal): qty tiap baris dialokasikan FIFO
    // ke baris-baris id_bpb yang masih ada sisa di rak tersebut (paling lama diterima dulu), supaya tetap
    // tertelusur ke BPB asalnya untuk laporan Bea Cukai. Tiap baris langsung di-insert sebelum lanjut ke baris
    // berikutnya (bukan ditampung lalu insert sekaligus di akhir) supaya 2 baris item+rak yang sama dalam satu
    // batch tetap saling mengurangi stok yang benar.
    public function store_pengeluaran_spareparts_mesin(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_rak' => 'required|integer|exists:asset_master_rak_spareparts,id_rak',
            'items.*.id_item' => 'required|integer',
            'items.*.id_penerimaan_mesin' => 'required|integer|exists:asset_penerimaan_mesin,id',
            'items.*.enroll_id_mekanik' => 'required|string|exists:mysql_hris.employee_atribut,enroll_id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $totalQty = 0;
        $error = null;

        DB::transaction(function () use ($request, $user, $timestamp, &$totalQty, &$error) {
            foreach ($request->items as $item) {
                $idItem = $item['id_item'];
                $idRak = $item['id_rak'];
                $qtyKeluar = (int) $item['qty'];

                // Sisa per BPB dihitung ulang di server (lockForUpdate) supaya 2 user yang menyimpan bersamaan
                // untuk item+rak yang sama tidak bisa over-allocate dari stok yang sama
                $penerimaanPerBpb = DB::table('asset_penerimaan_spareparts_mesin')
                    ->select('id_bpb', DB::raw('SUM(qty) as qty_masuk'), DB::raw('MIN(tgl_trans) as tgl_masuk_awal'))
                    ->where('id_item', $idItem)
                    ->where('id_rak', $idRak)
                    ->groupBy('id_bpb')
                    ->orderBy('tgl_masuk_awal', 'asc')
                    ->lockForUpdate()
                    ->get();

                $pengeluaranPerBpb = DB::table('asset_pengeluaran_spareparts_mesin')
                    ->select('id_bpb', DB::raw('SUM(qty) as qty_keluar'))
                    ->where('id_item', $idItem)
                    ->where('id_rak', $idRak)
                    ->groupBy('id_bpb')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_bpb');

                $sisaKeluar = $qtyKeluar;
                $rows = [];

                foreach ($penerimaanPerBpb as $row) {
                    if ($sisaKeluar <= 0) {
                        break;
                    }

                    $sudahKeluar = $pengeluaranPerBpb[$row->id_bpb]->qty_keluar ?? 0;
                    $sisaBpb = $row->qty_masuk - $sudahKeluar;

                    if ($sisaBpb <= 0) {
                        continue;
                    }

                    $ambil = min($sisaBpb, $sisaKeluar);

                    $rows[] = [
                        'tgl_trans' => $timestamp->format('Y-m-d'),
                        'id_item' => $idItem,
                        'id_bpb' => $row->id_bpb,
                        'id_rak' => $idRak,
                        'qty' => $ambil,
                        'id_penerimaan_mesin' => $item['id_penerimaan_mesin'],
                        'enroll_id_mekanik' => $item['enroll_id_mekanik'],
                        'created_by' => $user,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];

                    $sisaKeluar -= $ambil;
                }

                if ($sisaKeluar > 0) {
                    $error = "Stok sparepart tidak cukup untuk salah satu baris, sisa yang tersedia hanya " . ($qtyKeluar - $sisaKeluar) . ".";
                    return;
                }

                DB::table('asset_pengeluaran_spareparts_mesin')->insert($rows);
                $totalQty += $qtyKeluar;
            }
        });

        if ($error) {
            return response()->json([
                'status' => 'error',
                'message' => $error,
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Sparepart berhasil dikeluarkan sebanyak {$totalQty} (total " . count($request->items) . " baris).",
        ]);
    }
}
