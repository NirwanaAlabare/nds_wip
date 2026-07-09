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
        $lokasiList = DB::table('asset_penerimaan_mesin')
            ->select('lokasi')
            ->whereNotNull('lokasi')
            ->where('lokasi', '<>', '')
            ->distinct()
            ->orderBy('lokasi', 'ASC')
            ->get();

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
            if ($request->lokasi) {
                $whereBeli .= ' AND a.lokasi = ?';
                $bindingsBeli[] = $request->lokasi;
            }

            $whereSewa = 'WHERE 1=1';
            $bindingsSewa = [];

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
                    nm_jenis,
                    tipe,
                    nm_merk,
                    COUNT(*) AS total_unit,
                    'SEWA' AS sumber
                FROM asset_penerimaan_mesin_sewa
                $whereSewa
                GROUP BY nm_jenis, nm_merk, tipe

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
                    a.lokasi,
                    a.bpbno_int,
                    a.status,
                    ms.supplier
                FROM asset_penerimaan_mesin_sewa a
                LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
                LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
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
                    a.lokasi,
                    a.bpbno_int,
                    a.status,
                    ms.supplier
                FROM asset_penerimaan_mesin a
                LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
                LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
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
        if ($request->lokasi) {
            $whereBeli .= ' AND a.lokasi = ?';
            $bindingsBeli[] = $request->lokasi;
        }

        $whereSewa = 'WHERE 1=1';
        $bindingsSewa = [];

        return DB::select("
            SELECT
                a.id,
                'PEMBELIAN' AS sumber,
                j.nm_jenis,
                k.nm_merk,
                m.tipe,
                a.serial_number,
                a.kode_qr,
                a.lokasi,
                ms.Supplier AS supplier,
                a.bpbno_int,
                a.status
            FROM asset_penerimaan_mesin a
            INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
            INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
            INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
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
                a.lokasi,
                ms.supplier AS supplier,
                a.bpbno_int,
                a.status
            FROM asset_penerimaan_mesin_sewa a
            LEFT JOIN signalbit_erp.bpb bpb ON a.id_bpb = bpb.id
            LEFT JOIN signalbit_erp.mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
            $whereSewa

            ORDER BY nm_jenis ASC
        ", array_merge($bindingsBeli, $bindingsSewa));
    }
}
