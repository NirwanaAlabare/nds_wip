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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMesinMasterController extends Controller
{
    // Nama lokasi selalu gabungan main - sub - status. Tiap bagian di-TRIM karena data
    // master banyak yang menyisakan spasi di ujung, dan spasi itu bikin filter meleset.
    // Dipakai bareng blok JOIN lok_det / lok_main di query-query bawah.
    private const SQL_NAMA_LOKASI = "NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(TRIM(lok_main.main_lokasi), ''), NULLIF(TRIM(lok_det.sub_lokasi), ''), NULLIF(TRIM(lok_det.status), ''))), '')";

    public function asset_mesin_master(Request $request)
    {
        $jenisList = DB::table('asset_master_kd_jenis')->select('kd_jenis', 'nm_jenis')->orderBy('nm_jenis', 'ASC')->get();
        $merkList = DB::table('asset_master_kd_merk')->select('kd_merk', 'nm_merk')->orderBy('nm_merk', 'ASC')->get();
        $supplierList = DB::select("
            SELECT DISTINCT ms.Id_Supplier AS id_supplier, ms.Supplier AS Supplier
            FROM asset_penerimaan_mesin a
            INNER JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            INNER JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
            ORDER BY ms.Supplier ASC
        ");
        // Diambil dari master lokasi, bukan dari data mesin, supaya isi dropdown tetap lengkap
        // walaupun id_lokasi di tabel penerimaan belum banyak yang terisi
        $lokasiList = DB::select("
            SELECT lok_det.id, " . self::SQL_NAMA_LOKASI . " AS nama
            FROM asset_master_lokasi_det lok_det
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            HAVING nama IS NOT NULL
            ORDER BY nama ASC
        ");

        if ($request->ajax()) {
            $whereBeli = 'WHERE 1=1';
            $bindingsBeli = [];

            if ($request->kd_jenis) {
                $whereBeli .= ' AND m.kd_jenis = ?';
                $bindingsBeli[] = $request->kd_jenis;
            }
            if ($request->kd_merk) {
                $whereBeli .= ' AND m.kd_merk = ?';
                $bindingsBeli[] = $request->kd_merk;
            }
            if ($request->id_supplier) {
                $whereBeli .= ' AND bpb.id_supplier = ?';
                $bindingsBeli[] = $request->id_supplier;
            }
            $whereSewa = 'WHERE 1=1';
            $bindingsSewa = [];

            // Filter lokasi berlaku untuk mesin beli & sewa, karena keduanya sekarang
            // punya id_lokasi. Nilai 0 berarti "belum didata" (id_lokasi masih NULL).
            if ($request->filled('id_lokasi')) {
                if ((int) $request->id_lokasi === 0) {
                    $whereBeli .= ' AND a.id_lokasi IS NULL';
                    $whereSewa .= ' AND a.id_lokasi IS NULL';
                } else {
                    $whereBeli .= ' AND a.id_lokasi = ?';
                    $bindingsBeli[] = $request->id_lokasi;
                    $whereSewa .= ' AND a.id_lokasi = ?';
                    $bindingsSewa[] = $request->id_lokasi;
                }
            }

            $bindings = array_merge($bindingsBeli, $bindingsSewa);

            if ($request->mode === 'detail') {
                return DataTables::of($this->getDetailUnits($request))->toJson();
            }

            $data = DB::select("
                SELECT
                    m.id_jenis,
                    m.kd_jenis,
                    m.kd_merk,
                    j.nm_jenis,
                    m.tipe,
                    k.nm_merk,
                    COUNT(*) AS total_unit,
                    'PEMBELIAN' AS sumber
                FROM asset_penerimaan_mesin a
                INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
                INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
                INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
                LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
                $whereBeli
                GROUP BY m.id_jenis

                UNION ALL

                SELECT
                    '-' as id_jenis,
                    '-' as kd_jenis,
                    '-' as kd_merk,
                    a.nm_jenis,
                    a.tipe,
                    a.nm_merk,
                    COUNT(*) AS total_unit,
                    'SEWA' AS sumber
                FROM asset_penerimaan_mesin_sewa a
                $whereSewa
                GROUP BY a.nm_jenis, a.nm_merk, a.tipe

                ORDER BY nm_jenis ASC
            ", $bindings);

            return DataTables::of($data)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.master_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_master',
            'containerFluid' => true,
            'jenisList' => $jenisList,
            'merkList' => $merkList,
            'supplierList' => $supplierList,
            'lokasiList' => $lokasiList,
        ]);
    }

    public function get_master_mesin_unit(Request $request)
    {
        $request->validate([
            'id_jenis' => 'required',
            'sumber' => 'required|in:PEMBELIAN,SEWA',
        ]);

        // Baris 'sewa' hasil UNION dari asset_penerimaan_mesin_sewa (tidak punya id_jenis),
        // jadi detailnya dicari berdasarkan kombinasi nm_jenis, nm_merk & tipe
        if ($request->sumber === 'SEWA') {
            $request->validate([
                'nm_jenis' => 'nullable|string',
                'nm_merk' => 'nullable|string',
                'tipe' => 'nullable|string',
            ]);

            // Pakai <=> (null-safe equal) karena nm_jenis/nm_merk/tipe bisa NULL kalau unit sewa belum dilengkapi,
            // sedangkan "= ?" di SQL tidak akan pernah cocok dengan NULL meskipun parameternya juga NULL
            $units = DB::select("
                SELECT
                    a.id,
                    a.kode_qr,
                    a.serial_number,
                    a.foto,
                    a.id_lokasi,
                    " . self::SQL_NAMA_LOKASI . " AS lokasi,
                    a.bpbno_int,
                    a.status,
                    ms.supplier
                FROM asset_penerimaan_mesin_sewa a
                LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
                LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = a.id_lokasi
                LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
                WHERE a.nm_jenis <=> ? AND a.nm_merk <=> ? AND a.tipe <=> ?
                ORDER BY a.id DESC
            ", [$request->nm_jenis, $request->nm_merk, $request->tipe]);
        } else {
            $units = DB::select("
                SELECT
                    a.id,
                    a.kode_qr,
                    a.serial_number,
                    a.foto,
                    a.id_lokasi,
                    " . self::SQL_NAMA_LOKASI . " AS lokasi,
                    a.bpbno_int,
                    a.status,
                    ms.supplier
                FROM asset_penerimaan_mesin a
                LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
                LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = a.id_lokasi
                LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
                WHERE a.id_jenis = ?
                ORDER BY a.id DESC
            ", [$request->id_jenis]);
        }

        foreach ($units as $unit) {
            $complete = !empty($unit->serial_number) && !empty($unit->foto);
            $unit->qr = ($complete && $unit->kode_qr)
                ? base64_encode(QrCode::format('svg')->size(60)->generate($unit->kode_qr))
                : null;
        }

        return response()->json($units);
    }

    public function export_excel_master_mesin_detail(Request $request)
    {
        $rows = array_map(fn($r) => (array) $r, $this->getDetailUnits($request));

        $excel = FastExcel::create('List Detail Mesin');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['List Detail Mesin'])->applyFontStyleBold()->applyFontSize(16);
        $sheet->writeRow([]);

        $sheet->writeRow([
            'No',
            'Sumber',
            'Jenis',
            'Merk',
            'Tipe',
            'Serial Number',
            'Kode QR',
            'Lokasi',
            'Supplier',
            'No BPB',
            'Status',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $no = 1;
        foreach ($rows as $r) {
            $sheet->writeRow([
                $no++,
                $r['sumber'] ?? '',
                $r['nm_jenis'] ?? '',
                $r['nm_merk'] ?? '',
                $r['tipe'] ?? '',
                $r['serial_number'] ?? '',
                $r['kode_qr'] ?? '',
                $r['lokasi'] ?? '',
                $r['supplier'] ?? '',
                $r['bpbno_int'] ?? '',
                $r['status'] ?? '',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = 'List Detail Mesin ' . date('Y-m-d_His') . '.xlsx';

        // FastExcel::download() echo file langsung via header()+readfile() tanpa mengembalikan Response,
        // sehingga Laravel ikut mengirim response kosong di belakangnya & merusak isi file xlsx.
        // Simpan ke temp file lalu kirim lewat response()->download() bawaan Laravel supaya bersih.
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, $filename)->deleteFileAfterSend(true);
    }

    // Query unit mesin per baris (tanpa grouping), dipakai bareng oleh listing "List Detail" & export Excel-nya
    // supaya filter (Jenis/Merk/Supplier/Lokasi) & hasil selalu konsisten antara keduanya.
    private function getDetailUnits(Request $request): array
    {
        $whereBeli = 'WHERE 1=1';
        $bindingsBeli = [];

        if ($request->kd_jenis) {
            $whereBeli .= ' AND m.kd_jenis = ?';
            $bindingsBeli[] = $request->kd_jenis;
        }
        if ($request->kd_merk) {
            $whereBeli .= ' AND m.kd_merk = ?';
            $bindingsBeli[] = $request->kd_merk;
        }
        if ($request->id_supplier) {
            $whereBeli .= ' AND bpb.id_supplier = ?';
            $bindingsBeli[] = $request->id_supplier;
        }
        $whereSewa = 'WHERE 1=1';
        $bindingsSewa = [];

        // Nilai 0 berarti "belum didata" (id_lokasi masih NULL)
        if ($request->filled('id_lokasi')) {
            if ((int) $request->id_lokasi === 0) {
                $whereBeli .= ' AND a.id_lokasi IS NULL';
                $whereSewa .= ' AND a.id_lokasi IS NULL';
            } else {
                $whereBeli .= ' AND a.id_lokasi = ?';
                $bindingsBeli[] = $request->id_lokasi;
                $whereSewa .= ' AND a.id_lokasi = ?';
                $bindingsSewa[] = $request->id_lokasi;
            }
        }

        return DB::select("
            SELECT
                a.id,
                'PEMBELIAN' AS sumber,
                j.nm_jenis,
                k.nm_merk,
                m.tipe,
                a.serial_number,
                a.kode_qr,
                a.id_lokasi,
                " . self::SQL_NAMA_LOKASI . " AS lokasi,
                ms.Supplier AS supplier,
                a.bpbno_int,
                a.status
            FROM asset_penerimaan_mesin a
            INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
            INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
            INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
            LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = a.id_lokasi
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            $whereBeli

            UNION ALL

            SELECT
                a.id,
                'SEWA' AS sumber,
                a.nm_jenis,
                a.nm_merk,
                a.tipe,
                a.serial_number,
                a.kode_qr,
                a.id_lokasi,
                " . self::SQL_NAMA_LOKASI . " AS lokasi,
                ms.supplier AS supplier,
                a.bpbno_int,
                a.status
            FROM asset_penerimaan_mesin_sewa a
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
            LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = a.id_lokasi
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            $whereSewa

            ORDER BY nm_jenis ASC
        ", array_merge($bindingsBeli, $bindingsSewa));
    }
}
