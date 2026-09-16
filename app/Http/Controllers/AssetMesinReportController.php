<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;


class AssetMesinReportController extends Controller
{
    // Nama lokasi selalu gabungan main - sub - status. Tiap bagian di-TRIM karena data
    // master banyak yang menyisakan spasi di ujung, dan spasi itu bikin filter meleset.
    // Dipakai bareng blok JOIN lok_det / lok_main di query-query bawah.
    private const SQL_NAMA_LOKASI = "NULLIF(TRIM(CONCAT_WS(' - ', NULLIF(TRIM(lok_main.main_lokasi), ''), NULLIF(TRIM(lok_det.sub_lokasi), ''), NULLIF(TRIM(lok_det.status), ''))), '')";

    public function asset_mesin_report_stok_jenis_area(Request $request)
    {

        // id_lokasi 0 = mesin yang belum didata lokasinya (id_lokasi masih NULL).
        // Dipakai sebagai key yang aman untuk dikirim balik ke get_area_jenis_unit.
        $tot_per_lokasi = DB::select("SELECT
    IFNULL(mesin.id_lokasi, 0) AS id_lokasi,
    " . self::SQL_NAMA_LOKASI . " AS lokasi,
    COUNT(*) AS total
FROM (
    SELECT id_lokasi
    FROM asset_penerimaan_mesin
    WHERE status IN ('ACTIVE','IDLE','BREAKDOWN')

    UNION ALL

    SELECT id_lokasi
    FROM asset_penerimaan_mesin_sewa
    WHERE status IN ('ACTIVE','IDLE')
) AS mesin
LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = mesin.id_lokasi
LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
GROUP BY id_lokasi, lokasi
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

        $tot_per_kepemilikan = DB::select("
            SELECT 'PEMBELIAN' AS kepemilikan, COUNT(*) AS total
            FROM asset_penerimaan_mesin
            WHERE status IN ('ACTIVE','IDLE','BREAKDOWN')

            UNION ALL

            SELECT 'SEWA' AS kepemilikan, COUNT(*) AS total
            FROM asset_penerimaan_mesin_sewa
            WHERE status IN ('ACTIVE','IDLE')
        ");

        $tot_area_x_jenis_mesin = DB::select("SELECT
    IFNULL(x.id_lokasi, 0) AS id_lokasi,
    x.nm_jenis,
    SUM(x.total) AS total
FROM (
    SELECT
        a.id_lokasi,
        j.nm_jenis,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin a
    INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
    INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
    INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
    WHERE a.status IN ('ACTIVE','IDLE','BREAKDOWN')
    GROUP BY a.id_lokasi, j.nm_jenis

    UNION ALL

    SELECT
        id_lokasi,
        nm_jenis,
        COUNT(*) AS total
    FROM asset_penerimaan_mesin_sewa
    WHERE status IN ('ACTIVE','IDLE')
    GROUP BY id_lokasi, nm_jenis
) AS x
GROUP BY
    x.id_lokasi,
    x.nm_jenis
ORDER BY
    x.id_lokasi ASC,
    x.nm_jenis ASC
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
            'tot_per_kepemilikan' => $tot_per_kepemilikan,
            'tot_area_x_jenis_mesin' => $tot_area_x_jenis_mesin,
        ]);
    }

    public function get_area_jenis_unit(Request $request)
    {
        $request->validate([
            'id_lokasi' => 'nullable|integer',
            'nm_jenis' => 'nullable|string',
            'status' => 'nullable|string|in:ACTIVE,IDLE,BREAKDOWN',
            'kepemilikan' => 'nullable|string|in:PEMBELIAN,SEWA',
        ]);

        // Minimal satu filter, supaya modal tidak pernah diminta memuat seluruh mesin sekaligus
        if (
            !$request->filled('id_lokasi') && !$request->filled('nm_jenis')
            && !$request->filled('status') && !$request->filled('kepemilikan')
        ) {
            abort(422, 'id_lokasi, nm_jenis, status, atau kepemilikan wajib diisi');
        }

        $whereMesin = "a.status IN ('ACTIVE','IDLE','BREAKDOWN')";
        $bindingsMesin = [];

        // Kepemilikan tidak punya kolom sendiri: dibedakan dari tabel asalnya, jadi cabang
        // yang tidak dipilih dimatikan dengan 1 = 0 supaya strukturnya tetap satu UNION.
        if ($request->kepemilikan === 'SEWA') {
            $whereMesin .= ' AND 1 = 0';
        }

        // id_lokasi 0 = baris "(Belum Didata)" di report, artinya id_lokasi masih NULL
        if ($request->filled('id_lokasi')) {
            if ((int) $request->id_lokasi === 0) {
                $whereMesin .= ' AND a.id_lokasi IS NULL';
            } else {
                $whereMesin .= ' AND a.id_lokasi = ?';
                $bindingsMesin[] = $request->id_lokasi;
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

        if ($request->kepemilikan === 'PEMBELIAN') {
            $whereSewa .= ' AND 1 = 0';
        }

        if ($request->filled('id_lokasi')) {
            if ((int) $request->id_lokasi === 0) {
                $whereSewa .= ' AND id_lokasi IS NULL';
            } else {
                $whereSewa .= ' AND id_lokasi = ?';
                $bindingsSewa[] = $request->id_lokasi;
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
            SELECT
                units.id,
                units.serial_number,
                units.id_lokasi,
                " . self::SQL_NAMA_LOKASI . " AS lokasi,
                units.status,
                units.bpbno_int,
                units.nm_merk,
                units.tipe,
                units.kepemilikan
            FROM (
                SELECT a.id, a.serial_number, a.id_lokasi, a.status, a.bpbno_int, k.nm_merk, m.tipe, 'PEMBELIAN' AS kepemilikan
                FROM asset_penerimaan_mesin a
                INNER JOIN asset_master_jenis_mesin m ON a.id_jenis = m.id_jenis
                INNER JOIN asset_master_kd_jenis j ON m.kd_jenis = j.kd_jenis
                INNER JOIN asset_master_kd_merk k ON m.kd_merk = k.kd_merk
                WHERE $whereMesin

                UNION ALL

                SELECT id, serial_number, id_lokasi, status, bpbno_int, nm_merk, tipe, 'SEWA' AS kepemilikan
                FROM asset_penerimaan_mesin_sewa
                WHERE $whereSewa
            ) AS units
            LEFT JOIN asset_master_lokasi_det lok_det ON lok_det.id = units.id_lokasi
            LEFT JOIN asset_master_main_lokasi lok_main ON lok_main.id = lok_det.id_main_lokasi
            ORDER BY units.id DESC
        ", array_merge($bindingsMesin, $bindingsSewa));

        return response()->json($units);
    }
}
