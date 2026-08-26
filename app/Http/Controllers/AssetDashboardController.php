<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class AssetDashboardController extends Controller
{
    public function dashboard_asset(Request $request)
    {
        // Total mesin milik & sewa (status aktif di gudang / produksi)
        $totalMesinMilik = DB::table('asset_penerimaan_mesin')
            ->whereIn('status', ['ACTIVE', 'IDLE', 'BREAKDOWN', 'SERVICE'])
            ->count();

        $totalMesinSewa = DB::table('asset_penerimaan_mesin_sewa')
            ->whereIn('status', ['ACTIVE', 'IDLE'])
            ->count();

        $totalMesinBreakdown = DB::table('asset_penerimaan_mesin')
            ->whereIn('status', ['BREAKDOWN', 'SERVICE'])
            ->count();

        $totalKontrakSegeraBerakhir = DB::table('asset_penerimaan_mesin_sewa')
            ->whereIn('status', ['ACTIVE', 'IDLE'])
            ->whereRaw('tgl_akhir_kontrak BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)')
            ->count();

        // Mesin per status (milik + sewa)
        $mesinPerStatus = DB::select("
            SELECT
                COALESCE(NULLIF(status, ''), 'TANPA STATUS') AS status,
                SUM(total) AS total
            FROM (
                SELECT status, COUNT(*) AS total
                FROM asset_penerimaan_mesin
                WHERE status IN ('ACTIVE','IDLE','BREAKDOWN','SERVICE') OR status = '' OR status IS NULL
                GROUP BY status

                UNION ALL

                SELECT status, COUNT(*) AS total
                FROM asset_penerimaan_mesin_sewa
                WHERE status IN ('ACTIVE','IDLE') OR status = '' OR status IS NULL
                GROUP BY status
            ) x
            GROUP BY COALESCE(NULLIF(status, ''), 'TANPA STATUS')
            ORDER BY total DESC
        ");

        // Top 10 jenis mesin (milik + sewa)
        $mesinPerJenis = DB::select("
            SELECT nm_jenis, SUM(total) AS total
            FROM (
                SELECT j.nm_jenis, COUNT(*) AS total
                FROM asset_penerimaan_mesin a
                INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
                INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
                GROUP BY j.nm_jenis

                UNION ALL

                SELECT nm_jenis, COUNT(*) AS total
                FROM asset_penerimaan_mesin_sewa
                GROUP BY nm_jenis
            ) x
            WHERE NULLIF(nm_jenis, '') IS NOT NULL
            GROUP BY nm_jenis
            ORDER BY total DESC
            LIMIT 10
        ");

        // Penerimaan mesin 6 bulan terakhir (milik vs sewa)
        $penerimaanPerBulan = DB::select("
            SELECT
                DATE_FORMAT(bulan, '%Y-%m') AS periode,
                SUM(milik) AS milik,
                SUM(sewa) AS sewa
            FROM (
                SELECT DATE_FORMAT(tgl_trans, '%Y-%m-01') AS bulan, COUNT(*) AS milik, 0 AS sewa
                FROM asset_penerimaan_mesin
                WHERE tgl_trans >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
                GROUP BY DATE_FORMAT(tgl_trans, '%Y-%m-01')

                UNION ALL

                SELECT DATE_FORMAT(tgl_trans, '%Y-%m-01') AS bulan, 0 AS milik, COUNT(*) AS sewa
                FROM asset_penerimaan_mesin_sewa
                WHERE tgl_trans >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
                GROUP BY DATE_FORMAT(tgl_trans, '%Y-%m-01')
            ) x
            GROUP BY DATE_FORMAT(bulan, '%Y-%m')
            ORDER BY periode ASC
        ");

        return view('asset_management.dashboard_asset', [
            'page' => 'dashboard-asset',
            'totalMesinMilik' => $totalMesinMilik,
            'totalMesinSewa' => $totalMesinSewa,
            'totalMesinBreakdown' => $totalMesinBreakdown,
            'totalKontrakSegeraBerakhir' => $totalKontrakSegeraBerakhir,
            'mesinPerStatus' => $mesinPerStatus,
            'mesinPerJenis' => $mesinPerJenis,
            'penerimaanPerBulan' => $penerimaanPerBulan,
        ]);
    }
}
