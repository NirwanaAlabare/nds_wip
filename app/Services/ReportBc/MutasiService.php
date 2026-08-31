<?php

namespace App\Services\ReportBc;

use Illuminate\Support\Facades\DB;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Carbon\Carbon;


class MutasiService
{
    // public function getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');
    //     if (strtolower($kategoriBarang) === 'fabric') {
    //         $whereClass = "mi.matclass = 'FABRIC'";
    //     } elseif (strtolower($kategoriBarang) === 'accesories') {
    //         $whereClass = "mi.matclass IN ('ACCESORIES PACKING', 'ACCESORIES SEWING')";
    //     } else {
    //         $whereClass = "mi.matclass IN ('FABRIC', 'ACCESORIES PACKING', 'ACCESORIES SEWING')";
    //     }

    //     $sql = "
    //         SELECT isi.*, mi.itemdesc, mi.goods_code, ac.kpno
    //         FROM (
    //             SELECT A.id_jo, A.id_item,
    //                    SUM(A.sain) - SUM(A.saout) AS saldoawal,
    //                    SUM(A.qtyin) AS qtyterima,
    //                    SUM(A.qtyout) AS qtykeluar,
    //                    (SUM(A.sain) - SUM(A.saout)) + SUM(A.qtyin) - SUM(A.qtyout) AS saldoakhir,
    //                    A.unit
    //             FROM (
    //                 SELECT id_item, id_jo, SUM(qty) AS sain, 0 AS saout, 0 AS qtyin, 0 AS qtyout, unit FROM bpb WHERE bpbdate < ? GROUP BY id_jo, id_item, unit
    //                 UNION ALL
    //                 SELECT id_item, id_jo, 0 AS sain, SUM(qty) AS saout, 0 AS qtyin, 0 AS qtyout, unit FROM bppb WHERE bppbdate < ? GROUP BY id_jo, id_item, unit
    //                 UNION ALL
    //                 SELECT id_item, id_jo, 0 AS sain, 0 AS saout, SUM(qty) AS qtyin, 0 AS qtyout, unit FROM bpb WHERE bpbdate >= ? AND bpbdate <= ? GROUP BY id_jo, id_item, unit
    //                 UNION ALL
    //                 SELECT id_item, id_jo, 0 AS sain, 0 AS saout, 0 AS qtyin, SUM(qty) AS qtyout, unit FROM bppb WHERE bppbdate >= ? AND bppbdate <= ? GROUP BY id_jo, id_item, unit
    //             ) A
    //             GROUP BY A.id_jo, A.id_item, A.unit
    //         ) isi
    //         INNER JOIN masteritem mi ON isi.id_item = mi.id_item
    //         INNER JOIN (
    //             SELECT jd.id_jo, ac.kpno FROM jo_det jd
    //             INNER JOIN so ON so.id = jd.id_so
    //             INNER JOIN act_costing ac ON ac.id = so.id_cost
    //         ) ac ON ac.id_jo = isi.id_jo
    //         INNER JOIN
    //         WHERE $whereClass
    //     ";



    //     return $mysql_sb->select($sql, [
    //         $fromDate,
    //         $fromDate,
    //         $fromDate, $toDate,
    //         $fromDate, $toDate
    //     ]);
    // }

    public function getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang)
    {
        $mysql_sb = DB::connection('mysql_sb');
        if (strtolower($kategoriBarang) === 'fabric') {
            $whereClass = "mi.matclass = 'FABRIC'";
        } elseif (strtolower($kategoriBarang) === 'accesories') {
            $whereClass = "mi.matclass IN ('ACCESORIES PACKING', 'ACCESORIES SEWING')";
        } else {
            $whereClass = "mi.matclass IN ('FABRIC', 'ACCESORIES PACKING', 'ACCESORIES SEWING')";
        }

        $contentJoin = "
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            INNER JOIN masterdesc bd ON bd.id = mi.id_gen
            INNER JOIN mastercolor mc2 ON mc2.id = bd.id_color
            INNER JOIN masterweight mw ON mw.id = mc2.id_weight
            INNER JOIN masterlength ml ON ml.id = mw.id_length
            INNER JOIN masterwidth mwd ON mwd.id = ml.id_width
            INNER JOIN mastercontents mcnt ON mcnt.id = mwd.id_contents
        ";

        $sql = "
            SELECT isi.*, mc.kode_contents AS goods_code, mc.nama_contents AS itemdesc, ac.kpno
            FROM (
                SELECT A.id_contents AS id_item,
                    A.id_jo, A.id_contents,
                    SUM(A.sain) - SUM(A.saout) AS saldoawal,
                    SUM(A.qtyin) AS qtyterima,
                    SUM(A.qtyout) AS qtykeluar,
                    (SUM(A.sain) - SUM(A.saout)) + SUM(A.qtyin) - SUM(A.qtyout) AS saldoakhir,
                    A.unit
                FROM (
                    SELECT mcnt.id AS id_contents, b.id_jo, SUM(b.qty) AS sain, 0 AS saout, 0 AS qtyin, 0 AS qtyout, b.unit
                    FROM bpb b
                    $contentJoin
                    WHERE b.bpbdate < ? AND $whereClass
                    GROUP BY mcnt.id, b.unit

                    UNION ALL

                    SELECT mcnt.id AS id_contents, b.id_jo, 0 AS sain, SUM(b.qty) AS saout, 0 AS qtyin, 0 AS qtyout, b.unit
                    FROM bppb b
                    $contentJoin
                    WHERE b.bppbdate < ? AND $whereClass
                    GROUP BY mcnt.id, b.unit

                    UNION ALL

                    SELECT mcnt.id AS id_contents, b.id_jo, 0 AS sain, 0 AS saout, SUM(b.qty) AS qtyin, 0 AS qtyout, b.unit
                    FROM bpb b
                    $contentJoin
                    WHERE b.bpbdate >= ? AND b.bpbdate <= ? AND $whereClass
                    GROUP BY mcnt.id, b.unit

                    UNION ALL

                    SELECT mcnt.id AS id_contents, b.id_jo, 0 AS sain, 0 AS saout, 0 AS qtyin, SUM(b.qty) AS qtyout, b.unit
                    FROM bppb b
                    $contentJoin
                    WHERE b.bppbdate >= ? AND b.bppbdate <= ? AND $whereClass
                    GROUP BY mcnt.id, b.unit
                ) A
                GROUP BY A.id_contents, A.unit
            ) isi
            LEFT JOIN mastercontents mc ON mc.id = isi.id_contents
            INNER JOIN (
                SELECT jd.id_jo, ac.kpno FROM jo_det jd
                INNER JOIN so ON so.id = jd.id_so
                INNER JOIN act_costing ac ON ac.id = so.id_cost
            ) ac ON ac.id_jo = isi.id_jo
        ";

        return $mysql_sb->select($sql, [
            $fromDate,
            $fromDate,
            $fromDate, $toDate,
            $fromDate, $toDate
        ]);
    }

    // public function getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');

    //     $whereCategory = "1=1";
    //     if (strtolower($kategoriBarang) === 'garment') {
    //         $whereCategory = "ms.kategori = 'GARMENT'";
    //     } elseif (strtolower($kategoriBarang) === 'sample') {
    //         $whereCategory = "ms.kategori = 'SAMPLE'";
    //     } elseif (strtolower($kategoriBarang) === 'kain') {
    //         $whereCategory = "ms.kategori = 'KAIN'";
    //     }

    //     $sql = "
    //         SELECT
    //             ms.goods_code, ms.itemname, ms.styleno, ms.kpno, ms.color, ms.size, ms.country,
    //             mutasi.id_item, mutasi.id_so_det,
    //             SUM(saldo_awal) AS saldoawal,
    //             SUM(penerimaan) AS qtyterima,
    //             SUM(pengeluaran) AS qtykeluar,
    //             SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldoakhir
    //         FROM (
    //             SELECT * FROM (
    //                 SELECT saldoawal.id_item, saldoawal.id_so_det,
    //                        SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldo_awal,
    //                        0 AS penerimaan,
    //                        0 AS pengeluaran
    //                 FROM (
    //                     SELECT id_item, id_so_det, saldo AS saldo_awal, 0 AS penerimaan, 0 AS pengeluaran
    //                     FROM saldoawal_fg
    //                     WHERE periode = '2022-10-01'

    //                     UNION ALL

    //                     SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran
    //                     FROM bpb
    //                     WHERE bpbdate >= '2022-10-01' AND bpbdate < ?
    //                     AND bpbno LIKE 'FG%'
    //                     GROUP BY id_item, id_so_det

    //                     UNION ALL

    //                     SELECT id_item, id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(qty) AS pengeluaran
    //                     FROM bppb
    //                     WHERE bppbdate >= '2022-10-01' AND bppbdate < ?
    //                     AND bppbno LIKE 'SJ-FG%'
    //                     GROUP BY id_item, id_so_det
    //                 ) saldoawal
    //                 INNER JOIN masterstyle ms ON saldoawal.id_item = ms.id_item AND saldoawal.id_so_det = ms.id_so_det
    //                 GROUP BY saldoawal.id_item, saldoawal.id_so_det
    //             ) sa

    //             UNION ALL

    //             SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran
    //             FROM bpb
    //             WHERE bpbdate >= ? AND bpbdate <= ?
    //             AND bpbno LIKE 'FG%'
    //             GROUP BY id_item, id_so_det

    //             UNION ALL

    //             SELECT id_item, id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(qty) AS pengeluaran
    //             FROM bppb
    //             WHERE bppbdate >= ? AND bppbdate <= ?
    //             AND bppbno LIKE 'SJ-FG%'
    //             GROUP BY id_item, id_so_det
    //         ) mutasi
    //         INNER JOIN masterstyle ms ON mutasi.id_item = ms.id_item AND mutasi.id_so_det = ms.id_so_det
    //         WHERE $whereCategory
    //         GROUP BY mutasi.id_item, mutasi.id_so_det, ms.goods_code, ms.itemname, ms.styleno, ms.kpno, ms.color, ms.size, ms.country
    //         HAVING SUM(saldo_awal) != 0
    //             OR SUM(penerimaan) != 0
    //             OR SUM(pengeluaran) != 0
    //             OR SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) != 0
    //     ";

    //     return $mysql_sb->select($sql, [
    //         $fromDate,
    //         $fromDate,
    //         $fromDate, $toDate,
    //         $fromDate, $toDate
    //     ]);
    // }

    // public function getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang)
    // {
    //     $mysql_sb = DB::connection('mysql_sb');

    //     $whereCategory = "1=1";
    //     if (strtolower($kategoriBarang) === 'garment') {
    //         $whereCategory = "ms.kategori = 'GARMENT'";
    //     } elseif (strtolower($kategoriBarang) === 'sample') {
    //         $whereCategory = "ms.kategori = 'SAMPLE'";
    //     } elseif (strtolower($kategoriBarang) === 'kain') {
    //         $whereCategory = "ms.kategori = 'KAIN'";
    //     }

    //     $sql = "
    //         SELECT
    //             ms.goods_code, ms.itemname, ms.styleno, ms.kpno,
    //             GROUP_CONCAT(DISTINCT ms.color ORDER BY ms.color SEPARATOR ', ') AS color,
    //             GROUP_CONCAT(DISTINCT ms.size ORDER BY ms.size SEPARATOR ', ') AS size,
    //             MAX(ms.country) AS country,
    //             GROUP_CONCAT(DISTINCT mutasi.id_so_det ORDER BY mutasi.id_so_det SEPARATOR ', ') AS id_so_det,
    //             SUM(saldo_awal) AS saldoawal,
    //             SUM(penerimaan) AS qtyterima,
    //             SUM(pengeluaran) AS qtykeluar,
    //             SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldoakhir
    //         FROM (
    //             SELECT * FROM (
    //                 SELECT saldoawal.id_item, saldoawal.id_so_det,
    //                     SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldo_awal,
    //                     0 AS penerimaan,
    //                     0 AS pengeluaran,
    //                     GROUP_CONCAT(DISTINCT ws) AS ws
    //                 FROM (
    //                     SELECT id_item, id_so_det, saldo AS saldo_awal, 0 AS penerimaan, 0 AS pengeluaran, NULL AS ws
    //                     FROM saldoawal_fg
    //                     WHERE periode = '2022-10-01'

    //                     UNION ALL

    //                     SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran, NULL AS ws
    //                     FROM bpb
    //                     WHERE bpbdate >= '2022-10-01' AND bpbdate < ?
    //                     AND bpbno LIKE 'FG%'
    //                     GROUP BY id_item, id_so_det

    //                     UNION ALL

    //                     SELECT bppb.id_item, bppb.id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(bppb.qty) AS pengeluaran,
    //                         act_costing.kpno AS ws
    //                     FROM bppb
    //                     LEFT JOIN so_det ON bppb.id_so_det = so_det.id
    //                     LEFT JOIN so ON so_det.id_so = so.id
    //                     LEFT JOIN act_costing ON so.id_cost = act_costing.id
    //                     WHERE bppb.bppbdate >= '2022-10-01' AND bppb.bppbdate < ?
    //                     AND bppb.bppbno LIKE 'SJ-FG%'
    //                     GROUP BY act_costing.kpno
    //                 ) saldoawal
    //                 INNER JOIN masterstyle ms ON saldoawal.id_item = ms.id_item AND saldoawal.id_so_det = ms.id_so_det
    //                 GROUP BY saldoawal.id_item, saldoawal.id_so_det
    //             ) sa

    //             UNION ALL

    //             SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran, NULL AS ws
    //             FROM bpb
    //             WHERE bpbdate >= ? AND bpbdate <= ?
    //             AND bpbno LIKE 'FG%'
    //             GROUP BY id_item, id_so_det

    //             UNION ALL

    //             SELECT bppb.id_item, bppb.id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(bppb.qty) AS pengeluaran,
    //                 act_costing.kpno AS ws
    //             FROM bppb
    //             LEFT JOIN so_det ON bppb.id_so_det = so_det.id
    //             LEFT JOIN so ON so_det.id_so = so.id
    //             LEFT JOIN act_costing ON so.id_cost = act_costing.id
    //             WHERE bppb.bppbdate >= ? AND bppb.bppbdate <= ?
    //             AND bppb.bppbno LIKE 'SJ-FG%'
    //             GROUP BY act_costing.kpno
    //         ) mutasi
    //         INNER JOIN masterstyle ms ON mutasi.id_item = ms.id_item AND mutasi.id_so_det = ms.id_so_det
    //         WHERE $whereCategory
    //         GROUP BY ms.kpno, ms.goods_code, ms.itemname, ms.styleno
    //         HAVING SUM(saldo_awal) != 0
    //             OR SUM(penerimaan) != 0
    //             OR SUM(pengeluaran) != 0
    //             OR SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) != 0
    //     ";

    //     return $mysql_sb->select($sql, [
    //         $fromDate,
    //         $fromDate,
    //         $fromDate, $toDate,
    //         $fromDate, $toDate
    //     ]);
    // }

    public function getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $whereCategory = "1=1";
        if (strtolower($kategoriBarang) === 'garment') {
            $whereCategory = "ms.kategori = 'GARMENT'";
        } elseif (strtolower($kategoriBarang) === 'sample') {
            $whereCategory = "ms.kategori = 'SAMPLE'";
        } elseif (strtolower($kategoriBarang) === 'kain') {
            $whereCategory = "ms.kategori = 'KAIN'";
        }

        $sql = "
            SELECT
                ms.goods_code, ms.itemname, ms.styleno, ms.kpno,
                GROUP_CONCAT(DISTINCT ms.color ORDER BY ms.color SEPARATOR ', ') AS color,
                GROUP_CONCAT(DISTINCT ms.size ORDER BY ms.size SEPARATOR ', ') AS size,
                MAX(ms.country) AS country,
                GROUP_CONCAT(DISTINCT mutasi.id_so_det ORDER BY mutasi.id_so_det SEPARATOR ', ') AS id_so_det,
                SUM(saldo_awal) AS saldoawal,
                SUM(penerimaan) AS qtyterima,
                SUM(pengeluaran) AS qtykeluar,
                SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldoakhir
            FROM (
                SELECT * FROM (
                    SELECT saldoawal.id_item, saldoawal.id_so_det,
                        SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldo_awal,
                        0 AS penerimaan,
                        0 AS pengeluaran,
                        GROUP_CONCAT(DISTINCT ws) AS ws
                    FROM (
                        SELECT id_item, id_so_det, saldo AS saldo_awal, 0 AS penerimaan, 0 AS pengeluaran, NULL AS ws
                        FROM saldoawal_fg
                        WHERE periode = '2022-10-01'

                        UNION ALL

                        SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran, NULL AS ws
                        FROM bpb
                        WHERE bpbdate >= '2022-10-01' AND bpbdate < ?
                        AND bpbno LIKE 'FG%'
                        GROUP BY id_item, id_so_det

                        UNION ALL

                        SELECT bppb.id_item, bppb.id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(bppb.qty) AS pengeluaran,
                            MAX(act_costing.kpno) AS ws
                        FROM bppb
                        LEFT JOIN so_det ON bppb.id_so_det = so_det.id
                        LEFT JOIN so ON so_det.id_so = so.id
                        LEFT JOIN act_costing ON so.id_cost = act_costing.id
                        WHERE bppb.bppbdate >= '2022-10-01' AND bppb.bppbdate < ?
                        AND bppb.bppbno LIKE 'SJ-FG%'
                        GROUP BY bppb.id_item, bppb.id_so_det
                    ) saldoawal
                    INNER JOIN masterstyle ms ON saldoawal.id_item = ms.id_item AND saldoawal.id_so_det = ms.id_so_det
                    GROUP BY saldoawal.id_item, saldoawal.id_so_det
                ) sa

                UNION ALL

                SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran, NULL AS ws
                FROM bpb
                WHERE bpbdate >= ? AND bpbdate <= ?
                AND bpbno LIKE 'FG%'
                GROUP BY id_item, id_so_det

                UNION ALL

                SELECT bppb.id_item, bppb.id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(bppb.qty) AS pengeluaran,
                    MAX(act_costing.kpno) AS ws
                FROM bppb
                LEFT JOIN so_det ON bppb.id_so_det = so_det.id
                LEFT JOIN so ON so_det.id_so = so.id
                LEFT JOIN act_costing ON so.id_cost = act_costing.id
                WHERE bppb.bppbdate >= ? AND bppb.bppbdate <= ?
                AND bppb.bppbno LIKE 'SJ-FG%'
                GROUP BY bppb.id_item, bppb.id_so_det
            ) mutasi
            INNER JOIN masterstyle ms ON mutasi.id_item = ms.id_item AND mutasi.id_so_det = ms.id_so_det
            WHERE $whereCategory
            GROUP BY ms.kpno, ms.goods_code, ms.itemname, ms.styleno
            HAVING SUM(saldo_awal) != 0
                OR SUM(penerimaan) != 0
                OR SUM(pengeluaran) != 0
                OR SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) != 0
        ";

        return $mysql_sb->select($sql, [
            $fromDate,
            $fromDate,
            $fromDate, $toDate,
            $fromDate, $toDate
        ]);
    }

    public function getDataMutasiWip($fromDate, $toDate)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $sql = "
            SELECT kode_barang, nama_barang, satuan,
                   saldo_buku, hasil_pencacahan, keterangan
            FROM tbl_mutasi_wip
        ";

        return $mysql_sb->select($sql);
    }

    public function getDataMutasiMesinSparepart($fromDate, $toDate, $kategoriBarang)
    {
        $mysql_sb = DB::connection('mysql_sb');

        if (strtolower($kategoriBarang) === 'sparepart') {
            $sql = "
                SELECT
                    id_item,
                    goods_code kode_brg,
                    itemdesc nama_brg,
                    sum(qty_sa) saldo_awal,
                    sum(qty_in) qtyrcv,
                    sum(qty_out) qtyout,
                    sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_akhir,
                    unit
                FROM (
                    select
                        id_item,
                        goods_code,
                        itemdesc,
                        sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa,
                        '0' qty_in,
                        '0' qty_out,
                        unit
                    from (
                        select id_item, kd_barang goods_code, mi.itemdesc, qty qty_sa, '0' qty_in, '0' qty_out, unit   from saldoawal_gd a
                        inner join masteritem mi on a.kd_barang = mi.goods_code
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where periode = '2022-01-01' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N'
                        union
                        select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, sum(bpb.qty) qty_in, '0' qty_out, bpb.unit from bpb
                        inner join masteritem mi on bpb.id_item = mi.id_item
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where bpbdate >= '2022-01-01' and bpbdate < ? and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
                        group by mi.id_item, bpb.unit
                        union
                        select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, '0' qty_in, sum(bppb.qty) qty_out, bppb.unit from bppb
                        inner join masteritem mi on bppb.id_item = mi.id_item
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where bppbdate >= '2022-01-01' and bppbdate < ? and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
                        group by mi.id_item, bppb.unit
                    ) trx
                    group by id_item, unit
                    UNION
                    select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, sum(bpb.qty) qty_in, '0' qty_out, bpb.unit from bpb
                    inner join masteritem mi on bpb.id_item = mi.id_item
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where bpbdate >= ? and bpbdate <= ? and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
                    group by mi.id_item, bpb.unit
                    UNION
                    select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, '0' qty_in, sum(bppb.qty) qty_out, bppb.unit from bppb
                    inner join masteritem mi on bppb.id_item = mi.id_item
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where bppbdate >= ? and bppbdate <= ? and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
                    group by mi.id_item, bppb.unit
                ) mutasi
                group by id_item, unit
                having sum(qty_sa) != '0' or sum(qty_in) != '0' or sum(qty_out) != '0' or sum(qty_sa) + sum(qty_in) - sum(qty_out) != '0'
                order by kode_brg asc
            ";

            return $mysql_sb->select($sql, [$fromDate, $fromDate, $fromDate, $toDate, $fromDate, $toDate]);

        } else {
            $sql = "
                WITH cek_dok as (
                    select id_item, jenis_dok from bpb where bpbdate >= '2023-12-31' and bpbno not like '%FG%'
                    AND jenis_dok IS NOT NULL AND jenis_dok NOT IN ('INHOUSE', '')
                    group by id_item, jenis_dok
                    union all
                    select id_item, 'saldo_awal' AS jenis_dok from whs_sa_asset where tgl_periode = '2023-12-31'
                ),
                saldo_awal as (
                    select id_item, goods_code, itemdesc, sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa, '0' qty_in, '0' qty_out, unit
                    from (
                        select a.id_item, mi.goods_code, mi.itemdesc, qty as qty_sa, '0' qty_in, '0' qty_out, unit from whs_sa_asset a
                        inner join masteritem mi on a.id_item = mi.id_item
                        left join mapping_category mc on mi.n_code_category = mc.n_id
                        where tgl_periode = '2023-12-31' and tipe_item = 'ASSET'
                        union all
                        select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, sum(bpb.qty) qty_in, '0' qty_out, bpb.unit from bpb
                        inner join masteritem mi on bpb.id_item = mi.id_item
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where bpbdate > '2023-12-31' and bpbdate < ? and tipe_item = 'ASSET' and non_aktif = 'N' and bpb.bpbno not like '%FG%' and jenis_dok <> 'INHOUSE'
                        group by mi.id_item, unit
                        union all
                        select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, '0' qty_in, sum(bppb.qty) qty_out, bppb.unit from bppb
                        inner join masteritem mi on bppb.id_item = mi.id_item
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where bppbdate > '2023-12-31' and bppbdate < ? and tipe_item = 'ASSET' and non_aktif = 'N' and bppb.bppbno not like '%FG%' and jenis_dok <> 'INHOUSE'
                        group by mi.id_item, unit
                    ) sa
                    group by id_item, unit
                ),
                trx as (
                    select id_item, goods_code, itemdesc, '0' qty_sa, sum(qty_in) qty_in, sum(qty_out) qty_out, unit
                    from (
                        select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, sum(bpb.qty) qty_in, '0' qty_out, bpb.unit from bpb
                        inner join masteritem mi on bpb.id_item = mi.id_item
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where bpbdate >= ? and bpbdate <= ? and tipe_item = 'ASSET' and non_aktif = 'N' and bpb.bpbno not like '%FG%' and jenis_dok <> 'INHOUSE'
                        group by mi.id_item, unit
                        union all
                        select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa, '0' qty_in, sum(bppb.qty) qty_out, bppb.unit from bppb
                        inner join masteritem mi on bppb.id_item = mi.id_item
                        inner join mapping_category mc on mi.n_code_category = mc.n_id
                        where bppbdate >= ? and bppbdate <= ? and tipe_item = 'ASSET' and non_aktif = 'N' and bppb.bppbno not like '%FG%' and jenis_dok <> 'INHOUSE'
                        group by mi.id_item, unit
                    ) a
                    group by id_item, unit
                ),
                mutasi as (
                    select id_item, goods_code as kode_brg, itemdesc as nama_brg, sum(qty_sa) saldo_awal, sum(qty_in) qtyrcv, sum(qty_out) qtyout, sum(qty_sa) + sum(qty_in) - sum(qty_out) as qty_akhir, unit
                    from (
                        select * from saldo_awal union all select * from trx
                    ) a
                    group by id_item, unit
                )
                select m.*, jenis_dok_list from mutasi m
                left join (select id_item, GROUP_CONCAT(DISTINCT jenis_dok ORDER BY jenis_dok SEPARATOR ', ') as jenis_dok_list from cek_dok group by id_item) c on m.id_item = c.id_item
                having jenis_dok_list is not null
                order by kode_brg asc
            ";

            return $mysql_sb->select($sql, [$fromDate, $fromDate, $fromDate, $toDate, $fromDate, $toDate]);
        }
    }

    public function getDataMutasiBarangSisa($fromDate, $toDate, $kategoriScrap)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $filterKategori = "";
        if (strtolower($kategoriScrap) === 'import') {
            $filterKategori = " AND (mi.matclass = 'IMPORT' OR mi.matclass LIKE '%Import%' OR mi.itemdesc LIKE '%Import%') ";
        } elseif (strtolower($kategoriScrap) === 'lokal') {
            $filterKategori = " AND (mi.matclass = 'LOKAL' OR mi.matclass LIKE '%Lokal%' OR mi.itemdesc LIKE '%Lokal%') ";
        }

        $sql = "
            SELECT
                id_item,
                kode_brg,
                nama_brg,
                SUM(qty_sa) AS saldo_awal,
                SUM(qty_in) AS qtyrcv,
                SUM(qty_out) AS qtyout,
                SUM(qty_sa) + SUM(qty_in) - SUM(qty_out) AS qty_akhir,
                unit
            FROM (
                -- 1. SALDO AWAL
                SELECT mi.id_item, mi.goods_code AS kode_brg, mi.itemdesc AS nama_brg,
                       SUM(bpb.qty) AS qty_sa, '0' AS qty_in, '0' AS qty_out, bpb.unit
                FROM bpb
                INNER JOIN masteritem mi ON bpb.id_item = mi.id_item
                WHERE bpbdate < ? AND mi.mattype IN ('S','L') AND mi.non_aktif = 'N' $filterKategori
                GROUP BY mi.id_item, bpb.unit

                UNION ALL

                SELECT mi.id_item, mi.goods_code AS kode_brg, mi.itemdesc AS nama_brg,
                       -SUM(bppb.qty) AS qty_sa, '0' AS qty_in, '0' AS qty_out, bppb.unit
                FROM bppb
                INNER JOIN masteritem mi ON bppb.id_item = mi.id_item
                WHERE bppbdate < ? AND mi.mattype IN ('S','L') AND mi.non_aktif = 'N' $filterKategori
                GROUP BY mi.id_item, bppb.unit

                UNION ALL

                -- 2. PENERIMAAN (CURR IN)
                SELECT mi.id_item, mi.goods_code AS kode_brg, mi.itemdesc AS nama_brg,
                       '0' AS qty_sa, SUM(bpb.qty) AS qty_in, '0' AS qty_out, bpb.unit
                FROM bpb
                INNER JOIN masteritem mi ON bpb.id_item = mi.id_item
                WHERE bpbdate >= ? AND bpbdate <= ? AND mi.mattype IN ('S','L') AND mi.non_aktif = 'N' $filterKategori
                GROUP BY mi.id_item, bpb.unit

                UNION ALL

                -- 3. PENGELUARAN (CURR OUT)
                SELECT mi.id_item, mi.goods_code AS kode_brg, mi.itemdesc AS nama_brg,
                       '0' AS qty_sa, '0' AS qty_in, SUM(bppb.qty) AS qty_out, bppb.unit
                FROM bppb
                INNER JOIN masteritem mi ON bppb.id_item = mi.id_item
                WHERE bppbdate >= ? AND bppbdate <= ? AND mi.mattype IN ('S','L') AND mi.non_aktif = 'N' $filterKategori
                GROUP BY mi.id_item, bppb.unit
            ) mutasi
            GROUP BY id_item, kode_brg, nama_brg, unit
            HAVING SUM(qty_sa) != 0 OR SUM(qty_in) != 0 OR SUM(qty_out) != 0 OR (SUM(qty_sa) + SUM(qty_in) - SUM(qty_out)) != 0
            ORDER BY kode_brg ASC
        ";

        return $mysql_sb->select($sql, [
            $fromDate,
            $fromDate,
            $fromDate, $toDate,
            $fromDate, $toDate
        ]);
    }


    // public function getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang)
    // {
    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', 120);

    //     $tgl_awal  = $fromDate;
    //     $tgl_akhir = $toDate;

    //     $data_preview = DB::select("
    //         SELECT
    //             buyer,
    //             ws,
    //             styleno,
    //             color,
    //             m.size,
    //             MIN(m.product_group) AS product_group,
    //             MIN(m.product_item) AS product_item,
    //             MIN(brand) AS brand,
    //             MIN(m.dest) AS dest,
    //             SUM(qty_awal) AS qty_awal,
    //             SUM(qty_in) AS qty_in,
    //             SUM(qty_out) AS qty_out,
    //             SUM(qty_awal) + SUM(qty_in) - SUM(qty_out) AS saldo_akhir
    //         FROM (

    //             SELECT
    //                 id_so_det,
    //                 SUM(qty_in) - SUM(qty_out) AS qty_awal,
    //                 0 AS qty_in,
    //                 0 AS qty_out
    //             FROM (
    //                 SELECT id_so_det, SUM(qty) AS qty_in, 0 AS qty_out
    //                 FROM fg_stok_bpb
    //                 WHERE tgl_terima < '$tgl_awal'
    //                     AND sumber_pemasukan IN ('SEWING', 'REJECT', 'EKSPEDISI')
    //                 GROUP BY id_so_det

    //                 UNION ALL

    //                 SELECT id_so_det, SUM(qty) AS qty_in, 0 AS qty_out
    //                 FROM fg_stok_bpb_scan
    //                 WHERE tgl_terima < '$tgl_awal'
    //                     AND sumber_pemasukan IN ('SEWING', 'REJECT', 'EKSPEDISI')
    //                 GROUP BY id_so_det

    //                 UNION ALL

    //                 SELECT id_so_det, 0 AS qty_in, SUM(qty_out) AS qty_out
    //                 FROM fg_stok_bppb
    //                 WHERE tgl_pengeluaran < '$tgl_awal'
    //                     AND tujuan IN ('PRODUCTION-SEWING', 'QA', 'EKSPEDISI')
    //                 GROUP BY id_so_det

    //                 UNION ALL

    //                 SELECT
    //                     mb.id_so_det,
    //                     COUNT(CASE WHEN b.status = 'rejected' THEN 1 END) AS qty_in,
    //                     0 AS qty_out
    //                 FROM signalbit_erp.output_reject_out_detail a
    //                 INNER JOIN signalbit_erp.output_reject_in b ON a.reject_in_id = b.id
    //                 INNER JOIN signalbit_erp.master_plan mp ON b.master_plan_id = mp.id
    //                 LEFT JOIN (
    //                     SELECT sd.id AS id_so_det
    //                     FROM signalbit_erp.so_det sd
    //                     INNER JOIN signalbit_erp.so ON sd.id_so = so.id
    //                     INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
    //                     INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
    //                     INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
    //                     WHERE jd.cancel = 'N'
    //                 ) mb ON b.so_det_id = mb.id_so_det
    //                 WHERE DATE(a.created_at) < '$tgl_awal'
    //                     AND mp.cancel = 'N'
    //                 GROUP BY mb.id_so_det

    //                 UNION ALL

    //                 SELECT
    //                     tmpjod.id_so_det,
    //                     SUM(bppb.qty) AS qty_in,
    //                     0 AS qty_out
    //                 FROM signalbit_erp.bppb
    //                 INNER JOIN signalbit_erp.masterstyle ON masterstyle.id_item = bppb.id_item
    //                 INNER JOIN signalbit_erp.mastersupplier ON mastersupplier.Id_Supplier = bppb.id_supplier
    //                 LEFT JOIN (SELECT sod.id AS id_so_det FROM signalbit_erp.so_det sod GROUP BY sod.id) tmpjod
    //                     ON tmpjod.id_so_det = bppb.id_so_det
    //                 WHERE MID(bppbno,4,2) IN ('FG')
    //                     AND bppbdate < '$tgl_awal'
    //                     AND mastersupplier.supplier = 'BARANG JADI STOCK'
    //                 GROUP BY tmpjod.id_so_det

    //                 UNION ALL

    //                 SELECT
    //                     m2.id_so_det,
    //                     SUM(wa.qty) AS qty_in,
    //                     0 AS qty_out
    //                 FROM wip_adjustment wa
    //                 LEFT JOIN master_sb_ws m2
    //                     ON wa.no_ws = m2.ws AND wa.style = m2.styleno
    //                     AND wa.color = m2.color AND wa.size = m2.size
    //                 WHERE wa.tgl_saldo < '$tgl_awal'
    //                     AND wa.type_report = 'TRANSIT_GUDANG_STOK'
    //                 GROUP BY m2.id_so_det
    //             ) sa
    //             GROUP BY id_so_det

    //             UNION ALL

    //             SELECT id_so_det, 0 AS qty_awal, SUM(qty) AS qty_in, 0 AS qty_out
    //             FROM fg_stok_bpb
    //             WHERE tgl_terima >= '$tgl_awal' AND tgl_terima <= '$tgl_akhir'
    //                 AND sumber_pemasukan IN ('SEWING', 'REJECT', 'EKSPEDISI')
    //             GROUP BY id_so_det

    //             UNION ALL

    //             SELECT id_so_det, 0 AS qty_awal, SUM(qty) AS qty_in, 0 AS qty_out
    //             FROM fg_stok_bpb_scan
    //             WHERE tgl_terima >= '$tgl_awal' AND tgl_terima <= '$tgl_akhir'
    //                 AND sumber_pemasukan IN ('SEWING', 'REJECT', 'EKSPEDISI')
    //             GROUP BY id_so_det

    //             UNION ALL

    //             SELECT
    //                 mb.id_so_det,
    //                 0 AS qty_awal,
    //                 COUNT(CASE WHEN b.status = 'rejected' THEN 1 END) AS qty_in,
    //                 0 AS qty_out
    //             FROM signalbit_erp.output_reject_out_detail a
    //             INNER JOIN signalbit_erp.output_reject_in b ON a.reject_in_id = b.id
    //             INNER JOIN signalbit_erp.master_plan mp ON b.master_plan_id = mp.id
    //             LEFT JOIN (
    //                 SELECT sd.id AS id_so_det
    //                 FROM signalbit_erp.so_det sd
    //                 INNER JOIN signalbit_erp.so ON sd.id_so = so.id
    //                 INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
    //                 INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
    //                 INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
    //                 WHERE jd.cancel = 'N'
    //             ) mb ON b.so_det_id = mb.id_so_det
    //             WHERE DATE(a.created_at) >= '$tgl_awal' AND DATE(a.created_at) <= '$tgl_akhir'
    //                 AND mp.cancel = 'N'
    //             GROUP BY mb.id_so_det

    //             UNION ALL

    //             SELECT
    //                 tmpjod.id_so_det,
    //                 0 AS qty_awal,
    //                 SUM(bppb.qty) AS qty_in,
    //                 0 AS qty_out
    //             FROM signalbit_erp.bppb
    //             INNER JOIN signalbit_erp.masterstyle ON masterstyle.id_item = bppb.id_item
    //             INNER JOIN signalbit_erp.mastersupplier ON mastersupplier.Id_Supplier = bppb.id_supplier
    //             LEFT JOIN (SELECT sod.id AS id_so_det FROM signalbit_erp.so_det sod GROUP BY sod.id) tmpjod
    //                 ON tmpjod.id_so_det = bppb.id_so_det
    //             WHERE MID(bppbno,4,2) IN ('FG')
    //                 AND bppbdate >= '$tgl_awal' AND bppbdate <= '$tgl_akhir'
    //                 AND mastersupplier.supplier = 'BARANG JADI STOCK'
    //             GROUP BY tmpjod.id_so_det

    //             UNION ALL

    //             SELECT
    //                 m2.id_so_det,
    //                 0 AS qty_awal,
    //                 SUM(wa.qty) AS qty_in,
    //                 0 AS qty_out
    //             FROM wip_adjustment wa
    //             LEFT JOIN master_sb_ws m2
    //                 ON wa.no_ws = m2.ws AND wa.style = m2.styleno
    //                 AND wa.color = m2.color AND wa.size = m2.size
    //             WHERE wa.tgl_saldo >= '$tgl_awal' AND wa.tgl_saldo <= '$tgl_akhir'
    //                 AND wa.type_report = 'TRANSIT_GUDANG_STOK'
    //             GROUP BY m2.id_so_det

    //             UNION ALL

    //             SELECT id_so_det, 0 AS qty_awal, 0 AS qty_in, SUM(qty_out) AS qty_out
    //             FROM fg_stok_bppb
    //             WHERE tgl_pengeluaran >= '$tgl_awal' AND tgl_pengeluaran <= '$tgl_akhir'
    //                 AND tujuan IN ('PRODUCTION-SEWING', 'QA', 'EKSPEDISI')
    //             GROUP BY id_so_det

    //         ) x
    //         LEFT JOIN master_sb_ws m ON x.id_so_det = m.id_so_det
    //         LEFT JOIN master_size_new ms ON m.size = ms.size
    //         GROUP BY buyer, ws, styleno, color, m.size
    //         ORDER BY buyer ASC, color ASC, ms.urutan ASC
    //     ");

    //     $rows = collect($data_preview)->map(fn ($row) => (array) $row)->toArray();

    //     if (strtolower($kategoriBarang) !== 'all') {
    //         $rows = array_filter($rows, function ($row) use ($kategoriBarang) {
    //             return isset($row['product_group'])
    //                 && strtolower($row['product_group']) === strtolower($kategoriBarang);
    //         });
    //     }

    //     return collect($rows)->map(function ($row) {
    //         return (object) [
    //             'ws'            => $row['ws'] ?? '-',
    //             'styleno'       => $row['styleno'] ?? '-',
    //             'product_group' => $row['product_group'] ?? '-',
    //             'product_item'  => $row['product_item'] ?? '-',
    //             'color'         => $row['color'] ?? '-',
    //             'size'          => $row['size'] ?? '-',
    //             'saldoawal'     => $row['qty_awal'] ?? 0,
    //             'qtyterima'     => $row['qty_in'] ?? 0,
    //             'qtykeluar'     => $row['qty_out'] ?? 0,
    //             'saldoakhir'    => $row['saldo_akhir'] ?? 0,
    //         ];
    //     });
    // }
    public function getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 120);

        $tgl_awal = $fromDate;
        $tgl_akhir = $toDate;
        $saldo_awal = '2026-05-01';

        $data_preview = DB::select("WITH

            saldo_awal AS (
                SELECT
                    buyer, ws, styleno, color, m.size,
                    SUM(qty_awal) AS qty_awal,
                    SUM(qty_in) AS qty_in,
                    SUM(qty_out) AS qty_out,
                    SUM(qty_awal) + SUM(qty_in) - SUM(qty_out) AS saldo_akhir
                FROM (
                    SELECT
                        id_so_det,
                        SUM(qty_in) - SUM(qty_out) AS qty_awal,
                        0 AS qty_in, 0 AS qty_out,
                        grade, lokasi, no_carton
                    FROM (
                        SELECT id_so_det, SUM(qty) AS qty_in, 0 AS qty_out, grade, lokasi, no_carton
                        FROM fg_stok_bpb
                        WHERE tgl_terima < '$saldo_awal'
                        GROUP BY id_so_det, grade, lokasi, no_carton

                        UNION ALL

                        SELECT id_so_det, SUM(qty) AS qty_in, 0 AS qty_out, grade, lokasi, no_carton
                        FROM fg_stok_bpb_scan
                        WHERE tgl_terima < '$saldo_awal'
                        GROUP BY id_so_det, grade, lokasi, no_carton

                        UNION ALL

                        SELECT id_so_det, 0 AS qty_in, SUM(qty_out) AS qty_out, grade, lokasi, no_carton
                        FROM fg_stok_bppb
                        WHERE tgl_pengeluaran < '$saldo_awal'
                        GROUP BY id_so_det, grade, lokasi, no_carton
                    ) sa
                    GROUP BY id_so_det, grade, lokasi, no_carton
                ) mt
                LEFT JOIN master_sb_ws m ON mt.id_so_det = m.id_so_det
                LEFT JOIN master_size_new ms ON m.size = ms.size
                GROUP BY mt.id_so_det, grade, lokasi, no_carton
            ),

            all_data AS (
                SELECT
                    x.buyer, x.ws, x.color, x.styleno, x.size,
                    SUM(x.qty_saldo_awal_adjustment_before) AS qty_saldo_awal_adjustment_before,
                    SUM(x.qty_in_qc_reject_before) AS qty_in_qc_reject_before,
                    SUM(x.qty_in_qc_reject) AS qty_in_qc_reject,
                    SUM(x.qty_in_ekspedisi_before) AS qty_in_ekspedisi_before,
                    SUM(x.qty_in_ekspedisi) AS qty_in_ekspedisi,
                    SUM(x.qty_out_qc_reject_before) AS qty_out_qc_reject_before,
                    SUM(x.qty_out_qc_reject) AS qty_out_qc_reject,
                    SUM(x.qty_out_ekspedisi_before) AS qty_out_ekspedisi_before,
                    SUM(x.qty_out_ekspedisi) AS qty_out_ekspedisi,
                    SUM(x.qty_adjustment_before) AS qty_adjustment_before,
                    SUM(x.qty_adjustment) AS qty_adjustment,
                    SUM(x.qty_terima_qc_reject_before) AS qty_terima_qc_reject_before,
                    SUM(x.qty_terima_qc_reject) AS qty_terima_qc_reject,
                    SUM(x.qty_terima_ekspedisi_before) AS qty_terima_ekspedisi_before,
                    SUM(x.qty_terima_ekspedisi) AS qty_terima_ekspedisi,
                    SUM(x.qty_keluar_sewing_before) AS qty_keluar_sewing_before,
                    SUM(x.qty_keluar_sewing) AS qty_keluar_sewing,
                    SUM(x.qty_keluar_qa_before) AS qty_keluar_qa_before,
                    SUM(x.qty_keluar_qa) AS qty_keluar_qa,
                    SUM(x.qty_keluar_ekspedisi_before) AS qty_keluar_ekspedisi_before,
                    SUM(x.qty_keluar_ekspedisi) AS qty_keluar_ekspedisi
                FROM (

                    SELECT
                        buyer, ws, color, styleno, size,
                        saldo_awal.qty_awal AS qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM saldo_awal

                    UNION ALL

                    SELECT
                        mb.buyer, mb.ws, mb.color, mb.styleno, mb.size,
                        0 qty_saldo_awal_adjustment_before,
                        COUNT(CASE WHEN b.status = 'rejected' AND DATE(a.created_at) >= '$saldo_awal' AND DATE(a.created_at) < '$tgl_awal' THEN 1 END) AS qty_in_qc_reject_before,
                        COUNT(CASE WHEN b.status = 'rejected' AND DATE(a.created_at) >= '$tgl_awal' THEN 1 END) AS qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM signalbit_erp.output_reject_out_detail a
                    INNER JOIN signalbit_erp.output_reject_in b ON a.reject_in_id = b.id
                    INNER JOIN signalbit_erp.master_plan mp ON b.master_plan_id = mp.id
                    LEFT JOIN (
                        SELECT sd.id AS id_so_det, ac.kpno AS ws, supplier AS buyer, styleno, color, size, dest
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb ON b.so_det_id = mb.id_so_det
                    WHERE DATE(a.created_at) <= '$tgl_akhir'
                    AND mp.cancel = 'N'
                    GROUP BY mb.buyer, mb.ws, mb.styleno

                    UNION ALL

                    SELECT
                        buyer.supplier AS buyer, act_costing.kpno ws, masterstyle.color, act_costing.styleno, masterstyle.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        IF(bppbdate >= '$saldo_awal' AND bppbdate < '$tgl_awal', bppb.qty, 0) qty_in_ekspedisi_before,
                        IF(bppbdate >= '$tgl_awal', bppb.qty, 0) qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM signalbit_erp.bppb
                    INNER JOIN signalbit_erp.masterstyle ON masterstyle.id_item = bppb.id_item
                    INNER JOIN signalbit_erp.mastersupplier ON mastersupplier.Id_Supplier = bppb.id_supplier
                    LEFT JOIN (SELECT sod.id_so, sod.id id_so_det FROM signalbit_erp.so_det sod GROUP BY sod.id) tmpjod ON tmpjod.id_so_det = bppb.id_so_det
                    LEFT JOIN signalbit_erp.so ON so.id = tmpjod.id_so
                    LEFT JOIN signalbit_erp.act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN signalbit_erp.mastersupplier buyer ON buyer.Id_Supplier = act_costing.id_buyer
                    WHERE MID(bppbno,4,2) IN ('FG') AND bppbdate <= '$tgl_akhir' AND mastersupplier.supplier = 'BARANG JADI STOCK'

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_out_qc_reject_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan IN ('SEWING', 'REJECT')

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_out_qc_reject_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb_scan a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan IN ('SEWING', 'REJECT')

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_out_ekspedisi_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan = 'EKSPEDISI'

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_out_ekspedisi_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb_scan a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan = 'EKSPEDISI'

                    UNION ALL

                    SELECT
                        buyer, no_ws ws, color, style styleno, size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        SUM(IF(tgl_saldo >= '$saldo_awal' AND tgl_saldo < '$tgl_awal', qty, 0)) qty_adjustment_before,
                        SUM(IF(tgl_saldo >= '$tgl_awal', qty, 0)) qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM wip_adjustment
                    WHERE tgl_saldo <= '$tgl_akhir' AND type_report = 'TRANSIT_GUDANG_STOK'
                    GROUP BY ws, color, size, panel, part

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan IN ('SEWING', 'REJECT')

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb_scan a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan IN ('SEWING', 'REJECT')

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_terima_ekspedisi_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan = 'EKSPEDISI'

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_terima_ekspedisi_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bpb_scan a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan = 'EKSPEDISI'

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        IF(tgl_pengeluaran >= '$saldo_awal' AND tgl_pengeluaran < '$tgl_awal', a.qty_out, 0) AS qty_keluar_sewing_before,
                        IF(tgl_pengeluaran >= '$tgl_awal', a.qty_out, 0) AS qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bppb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_pengeluaran <= '$tgl_akhir'
                    AND a.tujuan = 'PRODUCTION-SEWING'

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        IF(tgl_pengeluaran >= '$saldo_awal' AND tgl_pengeluaran < '$tgl_awal', a.qty_out, 0) AS qty_keluar_qa_before,
                        IF(tgl_pengeluaran >= '$tgl_awal', a.qty_out, 0) AS qty_keluar_qa,
                        0 qty_keluar_ekspedisi_before, 0 qty_keluar_ekspedisi
                    FROM fg_stok_bppb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_pengeluaran <= '$tgl_akhir'
                    AND a.tujuan = 'QA'

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_in_qc_reject_before, 0 qty_in_qc_reject,
                        0 qty_in_ekspedisi_before, 0 qty_in_ekspedisi,
                        0 qty_out_qc_reject_before, 0 qty_out_qc_reject,
                        0 qty_out_ekspedisi_before, 0 qty_out_ekspedisi,
                        0 qty_adjustment_before, 0 qty_adjustment,
                        0 qty_terima_qc_reject_before, 0 qty_terima_qc_reject,
                        0 qty_terima_ekspedisi_before, 0 qty_terima_ekspedisi,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa,
                        IF(tgl_pengeluaran >= '$saldo_awal' AND tgl_pengeluaran < '$tgl_awal', a.qty_out, 0) AS qty_keluar_ekspedisi_before,
                        IF(tgl_pengeluaran >= '$tgl_awal', a.qty_out, 0) AS qty_keluar_ekspedisi
                    FROM fg_stok_bppb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_pengeluaran <= '$tgl_akhir'
                    AND a.tujuan = 'EKSPEDISI'
                ) x
                GROUP BY x.buyer, x.ws, x.styleno
            )


            SELECT
                ad.buyer,
                ad.ws,
                ad.styleno,
                ad.color,
                ad.size,
                MIN(m.product_group) AS product_group,
                MIN(m.product_item) AS product_item,
                (
                    CASE
                        WHEN '$tgl_awal' = '$saldo_awal'
                        THEN COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                        ELSE
                            COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                            + COALESCE(ad.qty_terima_qc_reject_before,0)
                            + COALESCE(ad.qty_terima_ekspedisi_before,0)
                            - COALESCE(ad.qty_keluar_sewing_before,0)
                            - COALESCE(ad.qty_keluar_qa_before,0)
                            - COALESCE(ad.qty_keluar_ekspedisi_before,0)
                    END
                ) AS saldo_awal,
                COALESCE(ad.qty_terima_qc_reject,0)   AS terima_qc_reject,
                COALESCE(ad.qty_terima_ekspedisi,0)   AS terima_ekspedisi,
                COALESCE(ad.qty_keluar_sewing,0)      AS keluar_sewing,
                COALESCE(ad.qty_keluar_qa,0)          AS keluar_qa,
                COALESCE(ad.qty_keluar_ekspedisi,0)   AS keluar_ekspedisi,
                (
                    CASE
                        WHEN '$tgl_awal' = '$saldo_awal'
                        THEN COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                        ELSE
                            COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                            + COALESCE(ad.qty_terima_qc_reject_before,0)
                            + COALESCE(ad.qty_terima_ekspedisi_before,0)
                            - COALESCE(ad.qty_keluar_sewing_before,0)
                            - COALESCE(ad.qty_keluar_qa_before,0)
                            - COALESCE(ad.qty_keluar_ekspedisi_before,0)
                    END
                    + COALESCE(ad.qty_terima_qc_reject,0)
                    + COALESCE(ad.qty_terima_ekspedisi,0)
                    - COALESCE(ad.qty_keluar_sewing,0)
                    - COALESCE(ad.qty_keluar_qa,0)
                    - COALESCE(ad.qty_keluar_ekspedisi,0)
                ) AS saldo_akhir
            FROM all_data ad
            LEFT JOIN master_sb_ws m
                ON ad.buyer = m.buyer AND ad.ws = m.ws AND ad.styleno = m.styleno
                AND ad.color = m.color AND ad.size = m.size
            GROUP BY ad.buyer, ad.ws, ad.styleno
            ORDER BY ad.buyer ASC, ad.color ASC
        ");

        $rows = collect($data_preview)->map(fn ($row) => (array) $row)->toArray();

        // if (strtolower($kategoriBarang) !== 'all') {
        //     $rows = array_filter($rows, function ($row) use ($kategoriBarang) {
        //         return isset($row['product_group'])
        //             && strtolower($row['product_group']) === strtolower($kategoriBarang);
        //     });
        // }

        // 'ws'            => $row['ws'] ?? '-',
        //         'styleno'       => $row['styleno'] ?? '-',
        //         'product_group' => $row['product_group'] ?? '-',
        //         'product_item'  => $row['product_item'] ?? '-',
        //         'color'         => $row['color'] ?? '-',
        //         'size'          => $row['size'] ?? '-',
        //         'saldoawal'     => $row['qty_awal'] ?? 0,
        //         'qtyterima'     => $row['qty_in'] ?? 0,
        //         'qtykeluar'     => $row['qty_out'] ?? 0,
        //         'saldoakhir'    => $row['saldo_akhir'] ?? 0,

        return collect($rows)->map(function ($row) {
            return (object) [
                'ws'               => $row['ws'] ?? '-',
                'styleno'          => $row['styleno'] ?? '-',
                'product_group'    => $row['product_group'] ?? '-',
                'product_item'     => $row['product_item'] ?? '-',
                'color'            => $row['color'] ?? '-',
                'size'             => $row['size'] ?? '-',
                'saldoawal'        => $row['saldo_awal'] ?? 0,
                'qtyterima'        => ($row['terima_qc_reject'] ?? 0) + ($row['terima_ekspedisi'] ?? 0),
                'qtykeluar'        => ($row['keluar_sewing'] ?? 0) + ($row['keluar_qa'] ?? 0) + ($row['keluar_ekspedisi'] ?? 0),
                'saldoakhir'       => $row['saldo_akhir'] ?? 0,
            ];
        });
    }


    // function exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $data = $this->getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang);

    //     $excel = FastExcel::create('Laporan');
    //     $sheet = $excel->getSheet();

    //     $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
    //         'font' => ['size' => 14, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A1:J1');

    //     $judulLaporan = "LAPORAN MUTASI BAHAN BAKU - " . strtoupper(str_replace('-', ' ', $kategori));
    //     $sheet->writeTo('A2', $judulLaporan, [
    //         'font' => ['size' => 12, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A2:J2');

    //     $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     $sheet->writeTo('A3', $periode, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A3:J3');

    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
    //     $sheet->writeTo('A4', $filterText, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A4:J4');
    //     $sheet->setColWidths([
    //         6,   // A - No
    //         12,  // B - ID Item
    //         16,  // C - Kode Barang
    //         30,  // D - Nama Barang
    //         14,  // E - No WS
    //         14,  // F - Saldo Awal
    //         14,  // G - Pemasukan
    //         14,  // H - Pengeluaran
    //         14,  // I - Saldo Akhir
    //         10,  // J - Satuan
    //     ]);

    //     $headerKolom = [
    //         'No',
    //         'ID Item',
    //         'Kode Barang',
    //         'Nama Barang',
    //         'No WS',
    //         'Saldo Awal',
    //         'Pemasukan',
    //         'Pengeluaran',
    //         'Saldo Akhir',
    //         'Satuan',
    //     ];

    //     $styleHeaderKolom = [
    //         'font' => ['style' => 'bold'],
    //         'border' => 'thin',
    //         'background-color' => '#d9edf7',
    //         'text-align' => 'center'
    //     ];

    //     $kolomHuruf = range('A', 'Q');
    //     foreach ($headerKolom as $i => $judul) {
    //         $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
    //     }

    //     $no = 1;
    //     $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

    //     collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
    //         // $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $no++,
    //                 $row->id_item ?? '-',
    //                 $row->goods_code ?? '-',
    //                 $row->itemdesc ?? '-',
    //                 $row->kpno,
    //                 (float)($row->saldoawal),
    //                 (float)($row->qtyterima),
    //                 (float)($row->qtykeluar),
    //                 (float)($row->saldoakhir),
    //                 $row->unit ?? '-',
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return $excel->download($filename);
    // }

    // function exportExcelBarangJadi($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $data = $this->getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang);

    //     $excel = FastExcel::create('Laporan');
    //     $sheet = $excel->getSheet();
    //     $sheet->setColWidths([
    //         6,   // A - No
    //         14,  // B - Id So Det
    //         16,  // C - Kode Barang
    //         20,  // D - Style
    //         14,  // E - No WS
    //         12,  // F - Color
    //         10,  // G - Size
    //         16,  // H - Dest/Country
    //         8,   // I - Unit
    //         14,  // J - Saldo Awal
    //         14,  // K - Penerimaan
    //         14,  // L - Pengeluaran
    //         14,  // M - Saldo Akhir
    //     ]);

    //     $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
    //         'font' => ['size' => 14, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A1:H1');

    //     $judulLaporan = "LAPORAN MUTASI BARANG JADI - " . strtoupper(str_replace('-', ' ', $kategori));
    //     $sheet->writeTo('A2', $judulLaporan, [
    //         'font' => ['size' => 12, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A2:H2');

    //     $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     $sheet->writeTo('A3', $periode, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A3:H3');

    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
    //     $sheet->writeTo('A4', $filterText, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A4:H4');

    //     $headerKolom = [
    //         'No',
    //         // 'Id So Det',
    //         'WS',
    //         // 'Style',
    //         // 'No WS',
    //         // 'Color',
    //         // 'Size',
    //         'Dest / Country',
    //         'Unit',
    //         'Saldo Awal',
    //         'Penerimaan',
    //         'Pengeluaran',
    //         'Saldo Akhir',
    //     ];

    //     $styleHeaderKolom = [
    //         'font' => ['style' => 'bold'],
    //         'border' => 'thin',
    //         'background-color' => '#d9edf7',
    //         'text-align' => 'center'
    //     ];

    //     $kolomHuruf = range('A', 'H');
    //     foreach ($headerKolom as $i => $judul) {
    //         $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
    //     }

    //     $no = 1;
    //     $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

    //     collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
    //         // $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $no++,
    //                 // $row->id_so_det ?? '-',
    //                 // $row->goods_code ?? '-',
    //                 // $row->styleno ?? '-',
    //                 $row->kpno ?? '-',
    //                 // $row->color ?? '-',
    //                 // $row->size ?? '-',
    //                 $row->country ?? '-',
    //                 'PCS',
    //                 (float)($row->saldoawal),
    //                 (float)($row->qtyterima),
    //                 (float)($row->qtykeluar),
    //                 (float)($row->saldoakhir),
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return $excel->download($filename);
    // }

    // function exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $data = $this->getDataMutasiMesinSparepart($fromDate, $toDate, $kategoriBarang);

    //     $excel = FastExcel::create('Laporan');
    //     $sheet = $excel->getSheet();

    //     $sheet->setColWidths([
    //         6,   // A - No
    //         12,  // B - Id Item
    //         16,  // C - Kode Barang
    //         30,  // D - Nama Barang
    //         14,  // E - Saldo Awal
    //         14,  // F - Penerimaan
    //         14,  // G - Pengeluaran
    //         14,  // H - Saldo Akhir
    //         10,  // I - Unit
    //     ]);

    //     $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
    //         'font' => ['size' => 14, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A1:I1');

    //     $judulLaporan = "LAPORAN MUTASI MESIN/SPAREPART -" . strtoupper(str_replace('-', ' ', $kategori));
    //     $sheet->writeTo('A2', $judulLaporan, [
    //         'font' => ['size' => 12, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A2:I2');

    //     $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     $sheet->writeTo('A3', $periode, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A3:I3');

    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
    //     $sheet->writeTo('A4', $filterText, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A4:I4');

    //     $headerKolom = [
    //         'No',
    //         'Id Item',
    //         'Kode Barang',
    //         'Nama Barang',
    //         'Saldo Awal',
    //         'Penerimaan',
    //         'Pengeluaran',
    //         'Saldo Akhir',
    //         'Unit',
    //     ];

    //     $styleHeaderKolom = [
    //         'font' => ['style' => 'bold'],
    //         'border' => 'thin',
    //         'background-color' => '#d9edf7',
    //         'text-align' => 'center'
    //     ];

    //     $kolomHuruf = range('A', 'I');
    //     foreach ($headerKolom as $i => $judul) {
    //         $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
    //     }

    //     $no = 1;
    //     $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

    //     collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
    //         // $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $no++,
    //                 $row->id_item ?? '-',
    //                 $row->kode_brg ?? '-',
    //                 $row->nama_brg ?? '-',
    //                 (float)($row->saldo_awal),
    //                 (float)($row->qtyrcv),
    //                 (float)($row->qtyout),
    //                 (float)($row->qty_akhir),
    //                 $row->unit ?? '-',
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return $excel->download($filename);
    // }

    // function exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $data = $this->getDataMutasiBarangSisa($fromDate, $toDate, $kategoriBarang);

    //     $excel = FastExcel::create('Laporan');
    //     $sheet = $excel->getSheet();

    //     $sheet->setColWidths([
    //         6,   // A - No
    //         12,  // B - Id Item
    //         16,  // C - Kode Barang
    //         30,  // D - Nama Barang
    //         14,  // E - Saldo Awal
    //         14,  // F - Penerimaan
    //         14,  // G - Pengeluaran
    //         14,  // H - Saldo Akhir
    //         10,  // I - Unit
    //     ]);

    //     $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
    //         'font' => ['size' => 14, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A1:I1');

    //     $judulLaporan = "LAPORAN MUTASI BARANG SISA/SCRAP -" . strtoupper(str_replace('-', ' ', $kategori));
    //     $sheet->writeTo('A2', $judulLaporan, [
    //         'font' => ['size' => 12, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A2:I2');

    //     $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     $sheet->writeTo('A3', $periode, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A3:I3');

    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
    //     $sheet->writeTo('A4', $filterText, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A4:I4');

    //     $headerKolom = [
    //         'No',
    //         'Id Item',
    //         'Kode Barang',
    //         'Nama Barang',
    //         'Saldo Awal',
    //         'Penerimaan',
    //         'Pengeluaran',
    //         'Saldo Akhir',
    //         'Unit',
    //     ];

    //     $styleHeaderKolom = [
    //         'font' => ['style' => 'bold'],
    //         'border' => 'thin',
    //         'background-color' => '#d9edf7',
    //         'text-align' => 'center'
    //     ];

    //     $kolomHuruf = range('A', 'I');
    //     foreach ($headerKolom as $i => $judul) {
    //         $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
    //     }

    //     $no = 1;
    //     $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

    //     collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
    //         // $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $no++,
    //                 $row->id_item ?? '-',
    //                 $row->kode_brg ?? '-',
    //                 $row->nama_brg ?? '-',
    //                 (float)($row->saldo_awal),
    //                 (float)($row->qtyrcv),
    //                 (float)($row->qtyout),
    //                 (float)($row->qty_akhir),
    //                 $row->unit ?? '-',
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return $excel->download($filename);
    // }

    // function exportExcelBarangJadiGudang($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');
    //     ini_set('display_errors', 0);

    //     if (ob_get_level()) {
    //         ob_end_clean();
    //     }

    //     $data = $this->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);

    //     $excel = FastExcel::create('Laporan');
    //     $sheet = $excel->getSheet();

    //     $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
    //         'font' => ['size' => 14, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A1:K1');

    //     $judulLaporan = "LAPORAN MUTASI BARANG JADI - " . strtoupper(str_replace('-', ' ', $kategori));
    //     $sheet->writeTo('A2', $judulLaporan, [
    //         'font' => ['size' => 12, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A2:K2');

    //     $periode_date = Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     if($fromDate == $toDate){
    //         $periode_date = Carbon::parse($fromDate)->format('d/m/Y');
    //     }

    //     $periode = "PERIODE: " . $periode_date;
    //     $sheet->writeTo('A3', $periode, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A3:K3');

    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
    //     $sheet->writeTo('A4', $filterText, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A4:K4');

    //     $headerKolom = [
    //         'No',
    //         'No WS',
    //         'Style',
    //         // 'Id So Det',
    //         'Product Group',
    //         'Product Item',
    //         'Color',
    //         'Size',
    //         // 'Grade',
    //         // 'Lokasi',
    //         // 'No Carton',
    //         'Saldo Awal',
    //         'Penerimaan',
    //         'Pengeluaran',
    //         'Saldo Akhir',
    //     ];

    //     $styleHeaderKolom = [
    //         'font' => ['style' => 'bold'],
    //         'border' => 'thin',
    //         'background-color' => '#d9edf7',
    //         'text-align' => 'center'
    //     ];

    //     $kolomHuruf = range('A', 'K');
    //     foreach ($headerKolom as $i => $judul) {
    //         $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
    //     }

    //     $no = 1;
    //     $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

    //     collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
    //         // $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $no++,
    //                 $row->ws ?? '-',
    //                 $row->styleno ?? '-',
    //                 // $row->id_so_det ?? '-',
    //                 $row->product_group ?? '-',
    //                 $row->product_item ?? '-',
    //                 $row->color ?? '-',
    //                 $row->size ?? '-',
    //                 // $row->grade ?? '-',
    //                 // $row->lokasi ?? '-',
    //                 // $row->no_carton ?? '-',
    //                 $row->saldoawal ?? '-',
    //                 $row->qtyterima ?? '-',
    //                 $row->qtykeluar ?? '-',
    //                 $row->saldoakhir ?? '-',
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return $excel->download($filename);
    // }

    // function exportExcelBarangJadiGudang($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori)
    // {
    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $data = $this->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);

    //     $periode_date = Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     if ($fromDate == $toDate) {
    //         $periode_date = Carbon::parse($fromDate)->format('d/m/Y');
    //     }

    //     $judulLaporan = "LAPORAN MUTASI BARANG JADI - " . strtoupper(str_replace('-', ' ', $kategori));
    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));

    //     $headerKolom = [
    //         'No', 'No WS', 'Style', 'Product Group', 'Product Item',
    //         'Color', 'Size', 'Saldo Awal', 'Penerimaan', 'Pengeluaran', 'Saldo Akhir',
    //     ];

    //     // Susun array data mentah dulu
    //     $rows = [];
    //     $no = 1;
    //     foreach ($data as $row) {
    //         $rows[] = [
    //             $no++,
    //             $row->ws ?? '-',
    //             $row->styleno ?? '-',
    //             $row->product_group ?? '-',
    //             $row->product_item ?? '-',
    //             $row->color ?? '-',
    //             $row->size ?? '-',
    //             $row->saldoawal ?? '-',
    //             $row->qtyterima ?? '-',
    //             $row->qtykeluar ?? '-',
    //             $row->saldoakhir ?? '-',
    //         ];
    //     }

    //     $export = new class($judulLaporan, $periode_date, $filterText, $headerKolom, $rows) implements FromArray, WithEvents, WithStyles {
    //         protected $judulLaporan, $periode_date, $filterText, $headerKolom, $rows;

    //         public function __construct($judulLaporan, $periode_date, $filterText, $headerKolom, $rows)
    //         {
    //             $this->judulLaporan = $judulLaporan;
    //             $this->periode_date = $periode_date;
    //             $this->filterText   = $filterText;
    //             $this->headerKolom  = $headerKolom;
    //             $this->rows         = $rows;
    //         }

    //         public function array(): array
    //         {
    //             return array_merge([
    //                 ['PT NIRWANA ALABARE GARMENT'],
    //                 [$this->judulLaporan],
    //                 ['PERIODE: ' . $this->periode_date],
    //                 [$this->filterText],
    //                 $this->headerKolom,
    //             ], $this->rows);
    //         }

    //         public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    //         {
    //             $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    //             $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    //             $sheet->getStyle('A3:A4')->getFont()->setBold(true);
    //             $sheet->getStyle('A5:K5')->getFont()->setBold(true);
    //             $sheet->getStyle('A5:K5')->getFill()
    //                 ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //                 ->getStartColor()->setRGB('D9EDF7');

    //             $lastRow = 5 + count($this->rows);
    //             $sheet->getStyle('A5:K' . $lastRow)
    //                 ->getBorders()->getAllBorders()
    //                 ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    //             return [];
    //         }

    //         public function registerEvents(): array
    //         {
    //             return [
    //                 AfterSheet::class => function (AfterSheet $event) {
    //                     $event->sheet->mergeCells('A1:K1');
    //                     $event->sheet->mergeCells('A2:K2');
    //                     $event->sheet->mergeCells('A3:K3');
    //                     $event->sheet->mergeCells('A4:K4');
    //                     $event->sheet->getStyle('A1:K4')->getAlignment()
    //                         ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    //                 },
    //             ];
    //         }
    //     };

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return Excel::download($export, $filename);
    // }

    public function exportExcelBarangJadi($fromDate, $toDate)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangJadi($fromDate, $toDate, 'all');

        $fileName = 'laporan-mutasi-barang-jadi';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['PT NIRWANA ALABARE GARMENT'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['LAPORAN MUTASI BARANG JADI'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . date('Y-m-d', strtotime($fromDate)) . ' s/d ' . date('Y-m-d', strtotime($toDate))],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);


        $sheet->writeRow([
            'No',
            'WS',
            // 'Dest / Country',
            'Unit',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $no = 1;
        foreach ($data as $row) {

            $rows = [
                $no++,
                $row->kpno ?? '-',
                // $row->country ?? '-',
                'PCS',
                (float)($row->saldoawal),
                (float)($row->qtyterima),
                (float)($row->qtykeluar),
                (float)($row->saldoakhir),
            ];

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'H') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

    public function exportExcelBarangJadiGudang($fromDate, $toDate)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangJadiGudang($fromDate, $toDate, 'all');

        $fileName = 'laporan-mutasi-barang-jadi-gudang';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['PT NIRWANA ALABARE GARMENT'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['LAPORAN MUTASI BARANG JADI GUDANG'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . date('Y-m-d', strtotime($fromDate)) . ' s/d ' . date('Y-m-d', strtotime($toDate))],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);


        $sheet->writeRow([
            'No',
            'No WS',
            'Style',
            'Product Group',
            'Product Item',
            'Color',
            'Size',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $no = 1;
        foreach ($data as $row) {

            $rows = [
                $no++,
                $row->ws ?? '-',
                $row->styleno ?? '-',
                $row->product_group ?? '-',
                $row->product_item ?? '-',
                $row->color ?? '-',
                $row->size ?? '-',
                $row->saldoawal ?? '-',
                $row->qtyterima ?? '-',
                $row->qtykeluar ?? '-',
                $row->saldoakhir ?? '-',
            ];

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'K') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }


    function exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang);


        $fileName = 'laporan-mutasi-bahan-baku';
        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['PT NIRWANA ALABARE GARMENT'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['LAPORAN MUTASI BARANG BAKU'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . date('Y-m-d', strtotime($fromDate)) . ' s/d ' . date('Y-m-d', strtotime($toDate))],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);


        $sheet->writeRow([
            'No',
            'ID Item',
            'Nama Barang',
            'Satuan',
            'Saldo Awal',
            'Pemasukan',
            'Pengeluaran',
            'Saldo Akhir',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $no = 1;
        foreach ($data as $row) {

            $rows = [
                $no++,
                $row->id_item ?? '-',
                $row->itemdesc ?? '-',
                $row->unit ?? '-',
                number_format($row->saldoawal ?? 0, 2),
                number_format($row->qtyterima ?? 0, 2),
                number_format($row->qtykeluar ?? 0, 2),
                number_format($row->saldoakhir ?? 0, 2),
            ];

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'K') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }


    function exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiMesinSparepart($fromDate, $toDate, $kategoriBarang);


        $fileName = 'laporan-mutasi-mesin-sparepart';
        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['PT NIRWANA ALABARE GARMENT'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['LAPORAN MUTASI MESIN DAN SPAREPART'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . date('Y-m-d', strtotime($fromDate)) . ' s/d ' . date('Y-m-d', strtotime($toDate))],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);


        $sheet->writeRow([
            'No',
            'Id Item',
            'Kode Barang',
            'Nama Barang',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
            'Unit',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $no = 1;
        foreach ($data as $row) {

            $rows = [
                $no++,
                $row->id_item ?? '-',
                $row->kode_brg ?? '-',
                $row->nama_brg ?? '-',
                (float)($row->saldo_awal),
                (float)($row->qtyrcv),
                (float)($row->qtyout),
                (float)($row->qty_akhir),
                $row->unit ?? '-',
            ];

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'K') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

    function exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangSisa($fromDate, $toDate, $kategoriBarang);


        $fileName = 'laporan-mutasi-barang-sisa';
        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['PT NIRWANA ALABARE GARMENT'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['LAPORAN MUTASI BARANG SISA'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . date('Y-m-d', strtotime($fromDate)) . ' s/d ' . date('Y-m-d', strtotime($toDate))],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);


        $sheet->writeRow([
            'No',
            'Id Item',
            'Kode Barang',
            'Nama Barang',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
            'Unit',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $no = 1;
        foreach ($data as $row) {

            $rows = [
                $no++,
                $row->id_item ?? '-',
                $row->kode_brg ?? '-',
                $row->nama_brg ?? '-',
                (float)($row->saldo_awal),
                (float)($row->qtyrcv),
                (float)($row->qtyout),
                (float)($row->qty_akhir),
                $row->unit ?? '-',
            ];


            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'K') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }


}
