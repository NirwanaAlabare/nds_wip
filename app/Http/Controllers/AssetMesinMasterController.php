<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
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
            $where = 'WHERE 1=1';
            $bindings = [];

            if ($request->kd_jenis) {
                $where .= ' AND m.kd_jenis = ?';
                $bindings[] = $request->kd_jenis;
            }
            if ($request->kd_merk) {
                $where .= ' AND m.kd_merk = ?';
                $bindings[] = $request->kd_merk;
            }
            if ($request->id_supplier) {
                $where .= ' AND bpb.id_supplier = ?';
                $bindings[] = $request->id_supplier;
            }
            if ($request->lokasi) {
                $where .= ' AND a.lokasi = ?';
                $bindings[] = $request->lokasi;
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
                $where
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
}
