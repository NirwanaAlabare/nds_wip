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

class AssetMesinSewaController extends Controller
{
    public function get_notif_mesin_sewa(Request $request)
    {
        $items = DB::select("
            SELECT id, bpbno_int, nm_jenis, nm_merk, tipe, serial_number, lokasi,
                   masa_kontrak, tgl_awal_kontrak, tgl_akhir_kontrak,
                   DATEDIFF(tgl_akhir_kontrak, CURDATE()) AS sisa_hari
            FROM asset_penerimaan_mesin_sewa
            WHERE status <> 'CUTT OFF'
                AND tgl_akhir_kontrak BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
            ORDER BY tgl_akhir_kontrak ASC
        ");

        return response()->json([
            'count' => count($items),
            'items' => $items,
        ]);
    }

    public function asset_mesin_sewa(Request $request)
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

        $jenisMasterList = DB::table('asset_master_kd_jenis')->select('kd_jenis', 'nm_jenis')->get();
        $merkMasterList = DB::table('asset_master_kd_merk')->select('kd_merk', 'nm_merk')->get();
        $jenisMap = $jenisMasterList->keyBy('kd_jenis');
        $merkMap = $merkMasterList->keyBy('kd_merk');

        $jenisList = DB::table('asset_master_jenis_mesin')
            ->select('id_jenis', 'kd_jenis', 'kd_merk', 'tipe', 'id_supplier')
            ->orderBy('kd_jenis', 'ASC')
            ->get();
        foreach ($jenisList as $row) {
            $row->jenis = $jenisMap[$row->kd_jenis]->nm_jenis ?? $row->kd_jenis;
            $row->merk = $merkMap[$row->kd_merk]->nm_merk ?? $row->kd_merk;
        }

        if ($request->ajax()) {
            $data_input = DB::select("SELECT * FROM asset_master_jenis_mesin ORDER BY id_jenis DESC");

            $supplierMap = $supplierList->keyBy('id_supplier');
            foreach ($data_input as $row) {
                $row->supplier = $supplierMap[$row->id_supplier]->Supplier ?? '-';
                $row->jenis = $jenisMap[$row->kd_jenis]->nm_jenis ?? $row->kd_jenis;
                $row->merk = $merkMap[$row->kd_merk]->nm_merk ?? $row->kd_merk;
            }

            return DataTables::of($data_input)->toJson();
        }

        $qrList = $this->getMesinSewaQrList();

        // For non-AJAX (initial page load)
        return view('asset_management.mesin_sewa', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_sewa',
            'containerFluid' => true,
            'supplierList' => $supplierList,
            'bpbList' => $bpbList,
            'jenisList' => $jenisList,
            'jenisMasterList' => $jenisMasterList,
            'merkMasterList' => $merkMasterList,
            'qrList' => $qrList,
        ]);
    }

    public function get_penerimaan_mesin_sewa(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("SELECT id_bpb, tgl_trans,DATE_FORMAT(tgl_trans, '%d-%m-%Y') as tgl_trans_fix, a.id_item, count(*) as tot_qty, bcno, jenis_dok,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' THEN 1 ELSE 0 END) as tot_filled,
        SUM(CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_foto,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' AND a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_complete,
        a.bpbno_int, bpb.unit, bpb.id_supplier, supplier, nm_jenis, nm_merk, a.id_item, mi.itemdesc, tipe
        FROM asset_penerimaan_mesin_sewa a
        left join signalbit_erp.bpb on a.id_bpb = bpb.id
        left join signalbit_erp.mastersupplier ms on bpb.id_supplier = ms.id_supplier
        left join signalbit_erp.masteritem mi on a.id_item = mi.id_item
        where a.tgl_trans >= ? and a.tgl_trans <= ?
        group by id_bpb, id_item
        ", [$tgl_awal, $tgl_akhir]);

        return DataTables::of($data)->toJson();
    }

    public function export_excel_penerimaan_mesin_sewa(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $data = DB::select("SELECT a.id_bpb, a.tgl_trans,DATE_FORMAT(tgl_trans, '%d-%m-%Y') as tgl_trans_fix, a.id_item, count(*) as tot_qty,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' THEN 1 ELSE 0 END) as tot_filled,
        SUM(CASE WHEN a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_foto,
        SUM(CASE WHEN a.serial_number IS NOT NULL AND a.serial_number <> '' AND a.foto IS NOT NULL AND a.foto <> '' THEN 1 ELSE 0 END) as tot_complete,
        a.bpbno_int, supplier, a.nm_jenis, a.nm_merk, a.tipe, mi.itemdesc
        FROM asset_penerimaan_mesin_sewa a
        left join signalbit_erp.bpb on a.id_bpb = bpb.id
        left join signalbit_erp.mastersupplier ms on bpb.id_supplier = ms.id_supplier
        left join signalbit_erp.masteritem mi on a.id_item = mi.id_item
        where a.tgl_trans >= ? and a.tgl_trans <= ?
        group by a.id_bpb, a.id_item
        order by a.tgl_trans ASC
        ", [$tgl_awal, $tgl_akhir]);

        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('Penerimaan Mesin Sewa');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Penerimaan Mesin Sewa'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow(["Periode {$tgl_awal} s/d {$tgl_akhir}"])->applyFontStyleBold();
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Tgl Transaksi',
            'BPB',
            'Supplier',
            'Jenis',
            'Merk',
            'Nama Mesin',
            'Tipe',
            'Total Unit',
            'Sudah Lengkap',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $sheet->writeRow([
                $no++,
                $r['tgl_trans_fix'] ?? '',
                $r['bpbno_int'] ?? '',
                $r['supplier'] ?? '',
                $r['nm_jenis'] ?? '',
                $r['nm_merk'] ?? '',
                $r['itemdesc'] ?? '',
                $r['tipe'] ?? '',
                $r['tot_qty'] ?? 0,
                $r['tot_complete'] ?? 0,
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = "Laporan Penerimaan Mesin Sewa {$tgl_awal} sd {$tgl_akhir}.xlsx";

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    public function get_bpb_detail_sewa(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("SELECT id, b.id_item, b.bpbno, b.bpbno_int, mi.itemdesc, qty, unit, b.id_supplier FROM bpb b
                INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
                INNER JOIN masteritem mi on b.id_item = mi.id_item
                WHERE bpbno = ? AND mi.n_code_category = '6' AND b.cancel = 'N'", [$request->bpbno]);

        return DataTables::of($data)->toJson();
    }

    public function store_penerimaan_mesin_sewa(Request $request)
    {
        $request->validate([
            'id_item' => 'required',
            'id_bpb' => 'required',
            'bpbno' => 'required',
            'bpbno_int' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        // tgl_awal_kontrak mengikuti tanggal BPB, bukan tanggal input, supaya masa kontrak dihitung dari saat mesin diterima
        $masaKontrak = 30;
        $tglAwalKontrak = DB::connection('mysql_sb')->table('bpb')->where('id', $request->id_bpb)->value('bpbdate');
        $tglAkhirKontrak = $tglAwalKontrak ? Carbon::parse($tglAwalKontrak)->addDays($masaKontrak)->format('Y-m-d') : null;

        for ($i = 0; $i < $request->qty; $i++) {
            DB::insert("INSERT INTO asset_penerimaan_mesin_sewa (
                tgl_trans,
                id_item,
                id_bpb,
                bpbno,
                bpbno_int,
                tgl_awal_kontrak,
                masa_kontrak,
                tgl_akhir_kontrak,
                status,
                created_by,
                created_at,
                updated_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)", [
                $timestamp->format('Y-m-d'),
                $request->id_item,
                $request->id_bpb,
                $request->bpbno,
                $request->bpbno_int,
                $tglAwalKontrak,
                $masaKontrak,
                $tglAkhirKontrak,
                'IDLE',
                $user,
                $timestamp,
                $timestamp
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Mesin sewa berhasil disimpan sebanyak ' . $request->qty . ' unit',
        ]);
    }

    public function get_mesin_sewa_qr_list(Request $request)
    {
        return response()->json($this->getMesinSewaQrList());
    }

    // Daftar Kode QR beserta gambar QR (svg base64) untuk ditampilkan & dipilih saat print
    private function getMesinSewaQrList()
    {
        $qrList = DB::table('asset_master_mesin_sewa_qr')
            ->select('kode_qr', 'status', 'created_by', 'created_at')
            ->orderBy('kode_qr', 'ASC')
            ->get();

        foreach ($qrList as $row) {
            $row->qr = base64_encode(QrCode::format('svg')->size(80)->generate($row->kode_qr));
        }

        return $qrList;
    }

    public function print_mesin_sewa_qr(Request $request)
    {
        $request->validate([
            'kode_qr' => 'required|array|min:1',
            'kode_qr.*' => 'required|string|exists:asset_master_mesin_sewa_qr,kode_qr',
        ]);

        $pdf = PDF::loadView('asset_management.print_qr_mesin_sewa_list', [
            'kodeQrList' => $request->kode_qr,
        ])->setPaper([0, 0, 200, 200]);

        return $pdf->stream('QR Code Mesin Sewa.pdf');
    }

    // Print Kode QR terpilih dalam format stiker grid A4 (sama seperti print_qr_list_mesin di AssetMesinTambahController),
    // dibuka lewat window.print() bawaan browser, bukan PDF stream, supaya lebih cepat untuk cetak banyak stiker sekaligus.
    public function print_mesin_sewa_qr_grid(Request $request)
    {
        $request->validate([
            'kode_qr' => 'required|array|min:1',
            'kode_qr.*' => 'required|string|exists:asset_master_mesin_sewa_qr,kode_qr',
        ]);

        $color = ltrim((string) $request->color, '#');
        $color = preg_match('/^[0-9a-fA-F]{6}$/', $color) ? strtolower($color) : 'fff59d';

        $codes = collect($request->kode_qr)->map(function ($kodeQr) use ($color) {
            $qr = QrCode::format('svg')->size(80)->backgroundColor(...array_map('hexdec', str_split($color, 2)));

            return (object) [
                'kode_qr' => $kodeQr,
                'qr' => base64_encode($qr->generate($kodeQr)),
            ];
        });

        return view('asset_management.print_qr_mesin_sewa_grid', [
            'codes' => $codes,
            'color' => $color,
        ]);
    }

    // Cari unit mesin sewa yang sedang memakai suatu Kode QR (kalau ada), untuk ditampilkan di tombol History
    public function get_mesin_sewa_qr_usage(Request $request)
    {
        $request->validate([
            'kode_qr' => 'required|string|exists:asset_master_mesin_sewa_qr,kode_qr',
        ]);

        $unit = DB::selectOne("
            SELECT a.bpbno_int, a.serial_number, a.nm_jenis, a.nm_merk, a.tipe,
                supplier, mi.itemdesc, a.tgl_awal_kontrak, a.tgl_akhir_kontrak
            FROM asset_penerimaan_mesin_sewa a
            LEFT JOIN signalbit_erp.bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.id_supplier
            LEFT JOIN signalbit_erp.masteritem mi ON a.id_item = mi.id_item
            WHERE a.kode_qr = ?
        ", [$request->kode_qr]);

        return response()->json($unit);
    }

    // Kode QR selalu dibuat dengan format tetap RENT_xxx (xxx = nomor urut, minimal 3 digit) supaya
    // konsisten & bisa digenerate banyak sekaligus lewat range nomor "Dari" - "Sampai".
    public function store_mesin_sewa_qr(Request $request)
    {
        $request->validate([
            'dari' => 'required|integer|min:1',
            'sampai' => 'required|integer|min:1|gte:dari',
        ]);

        $dari = (int) $request->dari;
        $sampai = (int) $request->sampai;

        if (($sampai - $dari + 1) > 500) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maksimal 500 Kode QR sekali generate.',
            ], 422);
        }

        $padLength = max(3, strlen((string) $sampai));
        $kodeQrList = [];
        for ($i = $dari; $i <= $sampai; $i++) {
            $kodeQrList[] = 'RENT_' . str_pad((string) $i, $padLength, '0', STR_PAD_LEFT);
        }

        $duplicates = DB::table('asset_master_mesin_sewa_qr')
            ->whereIn('kode_qr', $kodeQrList)
            ->pluck('kode_qr');

        if ($duplicates->isNotEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode QR berikut sudah terdaftar, tidak boleh duplikat: ' . $duplicates->implode(', '),
            ], 422);
        }

        $timestamp = Carbon::now();
        $rows = array_map(fn($kodeQr) => [
            'kode_qr' => $kodeQr,
            'status' => 'AVAILABLE',
            'created_by' => Auth::user()->name,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $kodeQrList);

        DB::table('asset_master_mesin_sewa_qr')->insert($rows);

        return response()->json([
            'status' => 'success',
            'message' => count($kodeQrList) > 1
                ? count($kodeQrList) . ' Kode QR berhasil disimpan (' . $kodeQrList[0] . ' s/d ' . end($kodeQrList) . ').'
                : 'Kode QR ' . $kodeQrList[0] . ' berhasil disimpan.',
        ]);
    }

    public function update_mesin_sewa_qr(Request $request)
    {
        $request->validate([
            'kode_qr_old' => 'required|string|exists:asset_master_mesin_sewa_qr,kode_qr',
            'kode_qr_new' => 'required|string|max:20',
        ]);

        $old = $request->kode_qr_old;
        $new = $request->kode_qr_new;

        if ($old === $new) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kode QR tidak berubah.',
            ]);
        }

        $duplicate = DB::table('asset_master_mesin_sewa_qr')->where('kode_qr', $new)->exists();
        if ($duplicate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode QR "' . $new . '" sudah terdaftar, tidak boleh duplikat.',
            ], 422);
        }

        DB::transaction(function () use ($old, $new) {
            DB::table('asset_master_mesin_sewa_qr')->where('kode_qr', $old)->update([
                'kode_qr' => $new,
                'updated_at' => Carbon::now(),
            ]);

            // kode_qr juga dipakai sebagai referensi di unit mesin sewa, ikut disinkronkan agar tidak orphan
            DB::table('asset_penerimaan_mesin_sewa')->where('kode_qr', $old)->update([
                'kode_qr' => $new,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Kode QR berhasil diubah.',
        ]);
    }

    public function get_penerimaan_mesin_sewa_unit(Request $request)
    {
        $request->validate([
            'id_bpb' => 'required',
            'id_item' => 'required',
        ]);

        $units = DB::table('asset_penerimaan_mesin_sewa')
            ->select('id', 'serial_number', 'foto', 'kode_qr', 'nm_jenis', 'nm_merk', 'tipe', 'tgl_awal_kontrak', 'masa_kontrak', 'tgl_akhir_kontrak')
            ->where('id_bpb', $request->id_bpb)
            ->where('id_item', $request->id_item)
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json($units);
    }

    public function store_penerimaan_mesin_sewa_unit(Request $request)
    {
        $request->validate([
            'units' => 'required|array',
            'units.*.id' => 'required|integer',
            'units.*.serial_number' => 'nullable|string|max:255',
            'units.*.foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'units.*.kode_qr' => 'nullable|exists:asset_master_mesin_sewa_qr,kode_qr',
            'units.*.nm_jenis' => 'nullable|string|max:255',
            'units.*.nm_merk' => 'nullable|string|max:255',
            'units.*.tipe' => 'nullable|string|max:255',
            'units.*.tgl_awal_kontrak' => 'nullable|date',
            'units.*.masa_kontrak' => 'nullable|integer|min:1',
        ]);

        $timestamp = Carbon::now();

        // Tiap request hanya kirim field yang berubah (serial_number / foto / kode_qr / nm_jenis / nm_merk / tipe / tgl_awal_kontrak / masa_kontrak),
        // jadi field lain tidak boleh ikut ditimpa kalau memang tidak dikirim.
        foreach ($request->units as $unit) {
            $update = ['updated_at' => $timestamp];

            if (array_key_exists('serial_number', $unit)) {
                $serialNumber = $unit['serial_number'] ?: null;

                if ($serialNumber) {
                    $idItem = DB::table('asset_penerimaan_mesin_sewa')->where('id', $unit['id'])->value('id_item');

                    $duplicate = DB::table('asset_penerimaan_mesin_sewa')
                        ->where('id_item', $idItem)
                        ->where('serial_number', $serialNumber)
                        ->where('id', '!=', $unit['id'])
                        ->exists();

                    if ($duplicate) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Serial Number "' . $serialNumber . '" sudah digunakan pada jenis mesin yang sama',
                        ], 422);
                    }
                }

                $update['serial_number'] = $serialNumber;
            }

            if (array_key_exists('kode_qr', $unit)) {
                $newKodeQr = $unit['kode_qr'] ?: null;
                $oldKodeQr = DB::table('asset_penerimaan_mesin_sewa')->where('id', $unit['id'])->value('kode_qr');

                if ($newKodeQr && $newKodeQr !== $oldKodeQr) {
                    $usedByOther = DB::table('asset_penerimaan_mesin_sewa')
                        ->where('kode_qr', $newKodeQr)
                        ->where('id', '!=', $unit['id'])
                        ->exists();

                    if ($usedByOther) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Kode QR "' . $newKodeQr . '" sudah dipakai unit lain.',
                        ], 422);
                    }
                }

                $update['kode_qr'] = $newKodeQr;

                // Kode QR adalah stiker fisik 1:1 dengan unit, jadi status di master ikut disinkronkan:
                // QR lama dilepas balik jadi Available, QR baru ditandai Used.
                if ($newKodeQr !== $oldKodeQr) {
                    if ($oldKodeQr) {
                        DB::table('asset_master_mesin_sewa_qr')->where('kode_qr', $oldKodeQr)->update([
                            'status' => 'AVAILABLE',
                        ]);
                    }

                    if ($newKodeQr) {
                        DB::table('asset_master_mesin_sewa_qr')->where('kode_qr', $newKodeQr)->update([
                            'status' => 'USED',
                        ]);
                    }
                }
            }

            if (array_key_exists('nm_jenis', $unit)) {
                $update['nm_jenis'] = $unit['nm_jenis'] ?: null;
            }

            if (array_key_exists('nm_merk', $unit)) {
                $update['nm_merk'] = $unit['nm_merk'] ?: null;
            }

            if (array_key_exists('tipe', $unit)) {
                $update['tipe'] = $unit['tipe'] ?: null;
            }

            if (array_key_exists('tgl_awal_kontrak', $unit)) {
                $update['tgl_awal_kontrak'] = $unit['tgl_awal_kontrak'] ?: null;
            }

            if (array_key_exists('masa_kontrak', $unit)) {
                $update['masa_kontrak'] = $unit['masa_kontrak'] ?: null;
            }

            // Tanggal Akhir Kontrak dihitung otomatis dari Tanggal Terima + Masa Kontrak (hari),
            // selalu dihitung ulang dari nilai efektif (gabungan data lama & yang baru dikirim) supaya tetap akurat
            // walau Tanggal Terima dan Masa Kontrak disimpan lewat request yang berbeda.
            if (array_key_exists('tgl_awal_kontrak', $update) || array_key_exists('masa_kontrak', $update)) {
                $current = DB::table('asset_penerimaan_mesin_sewa')->where('id', $unit['id'])->first();
                $tglAwalKontrak = array_key_exists('tgl_awal_kontrak', $update) ? $update['tgl_awal_kontrak'] : $current->tgl_awal_kontrak;
                $masaKontrak = array_key_exists('masa_kontrak', $update) ? $update['masa_kontrak'] : $current->masa_kontrak;

                // Default Masa Kontrak ke 30 hari kalau Tanggal Terima sudah diisi tapi Masa Kontrak belum pernah diisi
                if ($tglAwalKontrak && !$masaKontrak) {
                    $masaKontrak = 30;
                    $update['masa_kontrak'] = 30;
                }

                $update['tgl_akhir_kontrak'] = ($tglAwalKontrak && $masaKontrak)
                    ? Carbon::parse($tglAwalKontrak)->addDays((int) $masaKontrak)->format('Y-m-d')
                    : null;
            }

            if (isset($unit['foto']) && $unit['foto'] instanceof \Illuminate\Http\UploadedFile) {
                $filename = $unit['id'] . '.' . $unit['foto']->getClientOriginalExtension();
                $unit['foto']->storeAs('public/gambar_penerimaan_mesin_sewa', $filename);
                $update['foto'] = $filename;
            }

            DB::table('asset_penerimaan_mesin_sewa')->where('id', $unit['id'])->update($update);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data unit mesin sewa berhasil disimpan.',
        ]);
    }
}
