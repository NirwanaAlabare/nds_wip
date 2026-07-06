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

class AssetMesinReportController extends Controller
{
    public function asset_mesin_report_stok_jenis_area(Request $request)
    {

        $tot_per_lokasi = DB::select("SELECT lokasi, count(*) as total
FROM asset_penerimaan_mesin a
inner join asset_master_jenis_mesin m on a.id_jenis = m.id_jenis
inner join asset_master_kd_jenis j on m.kd_jenis = j.kd_jenis
inner join asset_master_kd_merk k on m.kd_merk = k.kd_merk
where status IN ('ACTIVE','IDLE','BREAKDOWN') group by lokasi
        ");

        $tot_jenis = DB::select("SELECT nm_jenis, count(*) as total
FROM asset_penerimaan_mesin a
inner join asset_master_jenis_mesin m on a.id_jenis = m.id_jenis
inner join asset_master_kd_jenis j on m.kd_jenis = j.kd_jenis
inner join asset_master_kd_merk k on m.kd_merk = k.kd_merk
where status IN ('ACTIVE','IDLE','BREAKDOWN') group by nm_jenis
ORDER BY nm_jenis ASC
        ");

        $tot_per_status = DB::select("SELECT status, count(*) as total
FROM asset_penerimaan_mesin a
inner join asset_master_jenis_mesin m on a.id_jenis = m.id_jenis
inner join asset_master_kd_jenis j on m.kd_jenis = j.kd_jenis
inner join asset_master_kd_merk k on m.kd_merk = k.kd_merk
where status IN ('ACTIVE','IDLE','BREAKDOWN') group by status
        ");

        $tot_area_x_jenis_mesin = DB::select("SELECT lokasi, nm_jenis, count(*) as total
FROM asset_penerimaan_mesin a
inner join asset_master_jenis_mesin m on a.id_jenis = m.id_jenis
inner join asset_master_kd_jenis j on m.kd_jenis = j.kd_jenis
inner join asset_master_kd_merk k on m.kd_merk = k.kd_merk
where status IN ('ACTIVE','IDLE','BREAKDOWN') group by lokasi, nm_jenis
        ");


        // For non-AJAX (initial page load)
        return view('asset_management.asset_mesin_report_stok_jenis_area', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_report',
            'containerFluid' => true,
            'tot_jenis' => $tot_jenis,
            'tot_per_status' => $tot_per_status,
            'tot_per_lokasi' => $tot_per_lokasi,
            'tot_area_x_jenis_mesin' => $tot_area_x_jenis_mesin,
        ]);
    }

    public function get_area_jenis_unit(Request $request)
    {
        $request->validate([
            'lokasi' => 'nullable|string',
            'nm_jenis' => 'nullable|string',
            'status' => 'nullable|string|in:ACTIVE,IDLE,BREAKDOWN',
        ]);

        if (!$request->filled('lokasi') && !$request->filled('nm_jenis') && !$request->filled('status')) {
            abort(422, 'lokasi, nm_jenis, atau status wajib diisi');
        }

        $where = "a.status IN ('ACTIVE','IDLE','BREAKDOWN')";
        $bindings = [];

        if ($request->filled('lokasi')) {
            if ($request->lokasi === '__NULL__') {
                $where .= ' AND a.lokasi IS NULL';
            } else {
                $where .= ' AND a.lokasi = ?';
                $bindings[] = $request->lokasi;
            }
        }
        if ($request->filled('nm_jenis')) {
            $where .= ' AND j.nm_jenis = ?';
            $bindings[] = $request->nm_jenis;
        }
        if ($request->filled('status')) {
            $where .= ' AND a.status = ?';
            $bindings[] = $request->status;
        }

        $units = DB::select("
            SELECT a.id, a.serial_number, a.lokasi, a.status, a.bpbno_int, k.nm_merk, m.tipe
            FROM asset_penerimaan_mesin a
            INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
            INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
            INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
            WHERE $where
            ORDER BY a.id DESC
        ", $bindings);

        return response()->json($units);
    }
}
