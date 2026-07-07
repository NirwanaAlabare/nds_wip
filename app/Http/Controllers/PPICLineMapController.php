<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;


class AssetMesinReportController extends Controller
{
    public function asset_mesin_report_stok_jenis_area(Request $request)
    {

        $tot_per_lokasi = DB::select("SELECT
    lokasi,
    COUNT(*) AS total
FROM (
    SELECT lokasi
    FROM asset_penerimaan_mesin
    WHERE status IN ('ACTIVE','IDLE','BREAKDOWN')

    UNION ALL

    SELECT lokasi
    FROM asset_penerimaan_mesin_sewa
    WHERE status IN ('ACTIVE','IDLE')
) AS mesin
GROUP BY lokasi
ORDER BY lokasi ASC
        ");

        $tot_jenis = DB::select("SELECT
    nm_jenis,
    SUM(total) AS total
FROM (
    SELECT
        nm_jenis,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin a
    INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
    INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
    INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
    WHERE status IN ('ACTIVE','IDLE','BREAKDOWN')
    GROUP BY nm_jenis

    UNION ALL

    -- Query sewa tetap apa adanya
    SELECT
        nm_jenis,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin_sewa
    WHERE status IN ('ACTIVE','IDLE')
    GROUP BY nm_jenis

) x
GROUP BY nm_jenis
ORDER BY nm_jenis ASC
        ");

        $tot_per_status = DB::select("SELECT
    status,
    SUM(total) AS total
FROM (
    SELECT
        status,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin a
    INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
    INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
    INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
    WHERE status IN ('ACTIVE','IDLE','BREAKDOWN')
    GROUP BY status

    UNION ALL

    SELECT
        status,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin_sewa
    WHERE status IN ('ACTIVE','IDLE')
    GROUP BY status
) AS x
GROUP BY status
ORDER BY status ASC
        ");

        $tot_area_x_jenis_mesin = DB::select("SELECT
    lokasi,
    nm_jenis,
    SUM(total) AS total
FROM (
    SELECT
        lokasi,
        nm_jenis,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin a
    INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
    INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
    INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
    WHERE status IN ('ACTIVE','IDLE','BREAKDOWN')
    GROUP BY lokasi, nm_jenis

    UNION ALL

    SELECT
        lokasi,
        nm_jenis,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin_sewa
    WHERE status IN ('ACTIVE','IDLE')
    GROUP BY lokasi, nm_jenis
) AS x
GROUP BY
    lokasi,
    nm_jenis
ORDER BY
    lokasi ASC,
    nm_jenis ASC
        ");


        // For non-AJAX (initial page load)
        return view('ppic.line_map', [
            'page' => 'dashboard-ppic',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'ppic_line_map',
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

        $whereMesin = "a.status IN ('ACTIVE','IDLE','BREAKDOWN')";
        $bindingsMesin = [];

        if ($request->filled('lokasi')) {
            if ($request->lokasi === '__NULL__') {
                $whereMesin .= ' AND a.lokasi IS NULL';
            } else {
                $whereMesin .= ' AND a.lokasi = ?';
                $bindingsMesin[] = $request->lokasi;
            }
        }
        if ($request->filled('nm_jenis')) {
            $whereMesin .= ' AND j.nm_jenis = ?';
            $bindingsMesin[] = $request->nm_jenis;
        }
        if ($request->filled('status')) {
            $whereMesin .= ' AND a.status = ?';
            $bindingsMesin[] = $request->status;
        }

        // Query sewa tetap apa adanya: nm_merk & tipe sudah kolom langsung, tidak perlu join
        $whereSewa = "status IN ('ACTIVE','IDLE')";
        $bindingsSewa = [];

        if ($request->filled('lokasi')) {
            if ($request->lokasi === '__NULL__') {
                $whereSewa .= ' AND lokasi IS NULL';
            } else {
                $whereSewa .= ' AND lokasi = ?';
                $bindingsSewa[] = $request->lokasi;
            }
        }
        if ($request->filled('nm_jenis')) {
            $whereSewa .= ' AND nm_jenis = ?';
            $bindingsSewa[] = $request->nm_jenis;
        }
        if ($request->filled('status')) {
            $whereSewa .= ' AND status = ?';
            $bindingsSewa[] = $request->status;
        }

        $units = DB::select("
            SELECT id, serial_number, lokasi, status, bpbno_int, nm_merk, tipe
            FROM (
                SELECT a.id, a.serial_number, a.lokasi, a.status, a.bpbno_int, k.nm_merk, m.tipe
                FROM asset_penerimaan_mesin a
                INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
                INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
                INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
                WHERE $whereMesin

                UNION ALL

                SELECT id, serial_number, lokasi, status, bpbno_int, nm_merk, tipe
                FROM asset_penerimaan_mesin_sewa
                WHERE $whereSewa
            ) AS units
            ORDER BY id DESC
        ", array_merge($bindingsMesin, $bindingsSewa));

        return response()->json($units);
    }
}
