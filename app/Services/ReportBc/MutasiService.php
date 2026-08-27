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
                GROUP BY A.id_jo, A.id_contents, A.unit
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
                            act_costing.kpno AS ws
                        FROM bppb
                        LEFT JOIN so_det ON bppb.id_so_det = so_det.id
                        LEFT JOIN so ON so_det.id_so = so.id
                        LEFT JOIN act_costing ON so.id_cost = act_costing.id
                        WHERE bppb.bppbdate >= '2022-10-01' AND bppb.bppbdate < ?
                        AND bppb.bppbno LIKE 'SJ-FG%'
                        GROUP BY act_costing.kpno
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
                    act_costing.kpno AS ws
                FROM bppb
                LEFT JOIN so_det ON bppb.id_so_det = so_det.id
                LEFT JOIN so ON so_det.id_so = so.id
                LEFT JOIN act_costing ON so.id_cost = act_costing.id
                WHERE bppb.bppbdate >= ? AND bppb.bppbdate <= ?
                AND bppb.bppbno LIKE 'SJ-FG%'
                GROUP BY act_costing.kpno
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

    public function getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 120);

        $ch = curl_init();
        $params = http_build_query([
            "tgl_awal"  => $fromDate,
            "tgl_akhir" => $toDate,
        ]);
        $apiUrl = "http://10.10.5.62:8123/api/laporan-fg-stock/show_fg_stok_mutasi?" . $params;

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);

        $output    = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr   = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        if ($curlErrno) {
            \Log::error('[MutasiService::getDataMutasiBarangJadiGudang] cURL error: ' . $curlErr);
            return collect();
        }

        if ($httpCode != 200) {
            \Log::error('[MutasiService::getDataMutasiBarangJadiGudang] HTTP ' . $httpCode);
            return collect();
        }

        $decoded = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('[MutasiService::getDataMutasiBarangJadiGudang] JSON decode error: ' . json_last_error_msg());
            return collect();
        }

        $rows = $decoded['data'] ?? [];

        if (strtolower($kategoriBarang) !== 'all') {
            $rows = array_filter($rows, function ($row) use ($kategoriBarang) {
                return isset($row['product_group'])
                    && strtolower($row['product_group']) === strtolower($kategoriBarang);
            });
        }

        return collect($rows)
            ->groupBy(function ($row) {
                return $row['ws'] ?? '-';
            })
            ->map(function ($group, $ws) {
                return (object) [
                    'ws'            => $ws,
                    'styleno'       => $group->pluck('styleno')->filter()->unique()->implode(', ') ?: '-',
                    'id_so_det'     => $group->pluck('id_so_det')->filter()->unique()->implode(', ') ?: '-',
                    'product_group' => $group->pluck('product_group')->filter()->unique()->implode(', ') ?: '-',
                    'product_item'  => $group->pluck('product_item')->filter()->unique()->implode(', ') ?: '-',
                    'color'         => $group->pluck('color')->filter()->unique()->implode(', ') ?: '-',
                    'size'          => $group->pluck('size')->filter()->unique()->implode(', ') ?: '-',
                    'grade'         => $group->pluck('grade')->filter()->unique()->implode(', ') ?: '-',
                    'lokasi'        => $group->pluck('lokasi')->filter()->unique()->implode(', ') ?: '-',
                    'no_carton'     => $group->pluck('no_carton')->filter()->unique()->implode(', ') ?: '-',
                    'saldoawal'     => $group->sum(fn ($r) => $r['qty_awal'] ?? 0),
                    'qtyterima'     => $group->sum(fn ($r) => $r['qty_in'] ?? 0),
                    'qtykeluar'     => $group->sum(fn ($r) => $r['qty_out'] ?? 0),
                    'saldoakhir'    => $group->sum(fn ($r) => $r['saldo_akhir'] ?? 0),
                ];
            })
            ->values();
    }

    // public function getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang)
    // {
    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', 120);

    //     $ch = curl_init();
    //     $params = http_build_query([
    //         "tgl_awal"  => $fromDate,
    //         "tgl_akhir" => $toDate,
    //     ]);
    //     $apiUrl = "http://10.10.5.62:8123/api/laporan-fg-stock/show_fg_stok_mutasi?" . $params;


    //     curl_setopt($ch, CURLOPT_URL, $apiUrl);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    //     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //     curl_setopt($ch, CURLOPT_TIMEOUT, 90);

    //     $output    = curl_exec($ch);
    //     $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     $curlErr   = curl_error($ch);
    //     $curlErrno = curl_errno($ch);
    //     curl_close($ch);

    //     if ($curlErrno) {
    //         \Log::error('[MutasiService::getDataMutasiBarangJadiGudang] cURL error: ' . $curlErr);
    //         return collect();
    //     }

    //     if ($httpCode != 200) {
    //         \Log::error('[MutasiService::getDataMutasiBarangJadiGudang] HTTP ' . $httpCode);
    //         return collect();
    //     }

    //     $decoded = json_decode($output, true);

    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         \Log::error('[MutasiService::getDataMutasiBarangJadiGudang] JSON decode error: ' . json_last_error_msg());
    //         return collect();
    //     }

    //     $rows = $decoded['data'] ?? [];

    //     if (strtolower($kategoriBarang) !== 'all') {
    //         $rows = array_filter($rows, function ($row) use ($kategoriBarang) {
    //             return isset($row['product_group'])
    //                 && strtolower($row['product_group']) === strtolower($kategoriBarang);
    //         });
    //     }

    //     return collect($rows)
    //         ->groupBy(function ($row) {
    //             return $row['ws'] ?? '-';
    //         })
    //         ->map(function ($group, $ws) {
    //             return (object) [
    //                 'ws'            => $ws,
    //                 'styleno'       => $group->pluck('styleno')->filter()->unique()->implode(', ') ?: '-',
    //                 'id_so_det'     => $group->pluck('id_so_det')->filter()->unique()->implode(', ') ?: '-',
    //                 'product_group' => $group->pluck('product_group')->filter()->unique()->implode(', ') ?: '-',
    //                 'product_item'  => $group->pluck('product_item')->filter()->unique()->implode(', ') ?: '-',
    //                 'color'         => $group->pluck('color')->filter()->unique()->implode(', ') ?: '-',
    //                 'size'          => $group->pluck('size')->filter()->unique()->implode(', ') ?: '-',
    //                 'grade'         => $group->pluck('grade')->filter()->unique()->implode(', ') ?: '-',
    //                 'lokasi'        => $group->pluck('lokasi')->filter()->unique()->implode(', ') ?: '-',
    //                 'no_carton'     => $group->pluck('no_carton')->filter()->unique()->implode(', ') ?: '-',
    //                 'saldoawal'     => $group->sum(fn ($r) => $r['qty_awal'] ?? 0),
    //                 'qtyterima'     => $group->sum(fn ($r) => $r['qty_in'] ?? 0),
    //                 'qtykeluar'     => $group->sum(fn ($r) => $r['qty_out'] ?? 0),
    //                 'saldoakhir'    => $group->sum(fn ($r) => $r['saldo_akhir'] ?? 0),
    //             ];
    //         })
    //         ->values();
    // }

    function exportExcelBahanBaku($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang);

        $excel = FastExcel::create('Laporan');
        $sheet = $excel->getSheet();

        $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
            'font' => ['size' => 14, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A1:J1');

        $judulLaporan = "LAPORAN MUTASI BAHAN BAKU - " . strtoupper(str_replace('-', ' ', $kategori));
        $sheet->writeTo('A2', $judulLaporan, [
            'font' => ['size' => 12, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A2:J2');

        $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
        $sheet->writeTo('A3', $periode, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A3:J3');

        $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
        $sheet->writeTo('A4', $filterText, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A4:J4');
        $sheet->setColWidths([
            6,   // A - No
            12,  // B - ID Item
            16,  // C - Kode Barang
            30,  // D - Nama Barang
            14,  // E - No WS
            14,  // F - Saldo Awal
            14,  // G - Pemasukan
            14,  // H - Pengeluaran
            14,  // I - Saldo Akhir
            10,  // J - Satuan
        ]);

        $headerKolom = [
            'No',
            'ID Item',
            'Kode Barang',
            'Nama Barang',
            'No WS',
            'Saldo Awal',
            'Pemasukan',
            'Pengeluaran',
            'Saldo Akhir',
            'Satuan',
        ];

        $styleHeaderKolom = [
            'font' => ['style' => 'bold'],
            'border' => 'thin',
            'background-color' => '#d9edf7',
            'text-align' => 'center'
        ];

        $kolomHuruf = range('A', 'Q');
        foreach ($headerKolom as $i => $judul) {
            $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
        }

        $no = 1;
        $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $no++,
                    $row->id_item ?? '-',
                    $row->goods_code ?? '-',
                    $row->itemdesc ?? '-',
                    $row->kpno,
                    (float)($row->saldoawal),
                    (float)($row->qtyterima),
                    (float)($row->qtykeluar),
                    (float)($row->saldoakhir),
                    $row->unit ?? '-',
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        return $excel->download($filename);
    }

    function exportExcelBarangJadi($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang);

        $excel = FastExcel::create('Laporan');
        $sheet = $excel->getSheet();
        $sheet->setColWidths([
            6,   // A - No
            14,  // B - Id So Det
            16,  // C - Kode Barang
            20,  // D - Style
            14,  // E - No WS
            12,  // F - Color
            10,  // G - Size
            16,  // H - Dest/Country
            8,   // I - Unit
            14,  // J - Saldo Awal
            14,  // K - Penerimaan
            14,  // L - Pengeluaran
            14,  // M - Saldo Akhir
        ]);

        $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
            'font' => ['size' => 14, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A1:H1');

        $judulLaporan = "LAPORAN MUTASI BARANG JADI - " . strtoupper(str_replace('-', ' ', $kategori));
        $sheet->writeTo('A2', $judulLaporan, [
            'font' => ['size' => 12, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A2:H2');

        $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
        $sheet->writeTo('A3', $periode, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A3:H3');

        $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
        $sheet->writeTo('A4', $filterText, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A4:H4');

        $headerKolom = [
            'No',
            // 'Id So Det',
            'Kode Barang',
            // 'Style',
            // 'No WS',
            // 'Color',
            // 'Size',
            'Dest / Country',
            'Unit',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
        ];

        $styleHeaderKolom = [
            'font' => ['style' => 'bold'],
            'border' => 'thin',
            'background-color' => '#d9edf7',
            'text-align' => 'center'
        ];

        $kolomHuruf = range('A', 'H');
        foreach ($headerKolom as $i => $judul) {
            $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
        }

        $no = 1;
        $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $no++,
                    // $row->id_so_det ?? '-',
                    // $row->goods_code ?? '-',
                    // $row->styleno ?? '-',
                    $row->kpno ?? '-',
                    // $row->color ?? '-',
                    // $row->size ?? '-',
                    $row->country ?? '-',
                    'PCS',
                    (float)($row->saldoawal),
                    (float)($row->qtyterima),
                    (float)($row->qtykeluar),
                    (float)($row->saldoakhir),
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        return $excel->download($filename);
    }

    function exportExcelMesinSparepart($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiMesinSparepart($fromDate, $toDate, $kategoriBarang);

        $excel = FastExcel::create('Laporan');
        $sheet = $excel->getSheet();

        $sheet->setColWidths([
            6,   // A - No
            12,  // B - Id Item
            16,  // C - Kode Barang
            30,  // D - Nama Barang
            14,  // E - Saldo Awal
            14,  // F - Penerimaan
            14,  // G - Pengeluaran
            14,  // H - Saldo Akhir
            10,  // I - Unit
        ]);

        $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
            'font' => ['size' => 14, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A1:I1');

        $judulLaporan = "LAPORAN MUTASI MESIN/SPAREPART -" . strtoupper(str_replace('-', ' ', $kategori));
        $sheet->writeTo('A2', $judulLaporan, [
            'font' => ['size' => 12, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A2:I2');

        $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
        $sheet->writeTo('A3', $periode, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A3:I3');

        $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
        $sheet->writeTo('A4', $filterText, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A4:I4');

        $headerKolom = [
            'No',
            'Id Item',
            'Kode Barang',
            'Nama Barang',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
            'Unit',
        ];

        $styleHeaderKolom = [
            'font' => ['style' => 'bold'],
            'border' => 'thin',
            'background-color' => '#d9edf7',
            'text-align' => 'center'
        ];

        $kolomHuruf = range('A', 'I');
        foreach ($headerKolom as $i => $judul) {
            $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
        }

        $no = 1;
        $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
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

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        return $excel->download($filename);
    }

    function exportExcelBarangSisa($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangSisa($fromDate, $toDate, $kategoriBarang);

        $excel = FastExcel::create('Laporan');
        $sheet = $excel->getSheet();

        $sheet->setColWidths([
            6,   // A - No
            12,  // B - Id Item
            16,  // C - Kode Barang
            30,  // D - Nama Barang
            14,  // E - Saldo Awal
            14,  // F - Penerimaan
            14,  // G - Pengeluaran
            14,  // H - Saldo Akhir
            10,  // I - Unit
        ]);

        $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
            'font' => ['size' => 14, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A1:I1');

        $judulLaporan = "LAPORAN MUTASI BARANG SISA/SCRAP -" . strtoupper(str_replace('-', ' ', $kategori));
        $sheet->writeTo('A2', $judulLaporan, [
            'font' => ['size' => 12, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A2:I2');

        $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
        $sheet->writeTo('A3', $periode, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A3:I3');

        $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
        $sheet->writeTo('A4', $filterText, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A4:I4');

        $headerKolom = [
            'No',
            'Id Item',
            'Kode Barang',
            'Nama Barang',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
            'Unit',
        ];

        $styleHeaderKolom = [
            'font' => ['style' => 'bold'],
            'border' => 'thin',
            'background-color' => '#d9edf7',
            'text-align' => 'center'
        ];

        $kolomHuruf = range('A', 'I');
        foreach ($headerKolom as $i => $judul) {
            $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
        }

        $no = 1;
        $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
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

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        return $excel->download($filename);
    }

    function exportExcelBarangJadiGudang($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangJadiGudang($fromDate, $toDate, $kategoriBarang);

        $excel = FastExcel::create('Laporan');
        $sheet = $excel->getSheet();

        $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
            'font' => ['size' => 14, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A1:I1');

        $judulLaporan = "LAPORAN MUTASI BARANG JADI - " . strtoupper(str_replace('-', ' ', $kategori));
        $sheet->writeTo('A2', $judulLaporan, [
            'font' => ['size' => 12, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A2:I2');

        $periode_date = Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
        if($fromDate == $toDate){
            $periode_date = Carbon::parse($fromDate)->format('d/m/Y');
        }

        $periode = "PERIODE: " . $periode_date;
        $sheet->writeTo('A3', $periode, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A3:I3');

        $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
        $sheet->writeTo('A4', $filterText, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A4:I4');

        $headerKolom = [
            'No',
            'No WS',
            'Style',
            // 'Id So Det',
            'Product Group',
            'Product Item',
            // 'Color',
            // 'Size',
            // 'Grade',
            // 'Lokasi',
            // 'No Carton',
            'Saldo Awal',
            'Penerimaan',
            'Pengeluaran',
            'Saldo Akhir',
        ];

        $styleHeaderKolom = [
            'font' => ['style' => 'bold'],
            'border' => 'thin',
            'background-color' => '#d9edf7',
            'text-align' => 'center'
        ];

        $kolomHuruf = range('A', 'I');
        foreach ($headerKolom as $i => $judul) {
            $sheet->writeTo($kolomHuruf[$i] . '5', $judul, $styleHeaderKolom);
        }

        $no = 1;
        $jenisDokumenFixed = strtoupper(str_replace('-', ' ', $kategori));

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet, &$no, $jenisDokumenFixed) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $no++,
                    $row->ws ?? '-',
                    $row->styleno ?? '-',
                    // $row->id_so_det ?? '-',
                    $row->product_group ?? '-',
                    $row->product_item ?? '-',
                    // $row->color ?? '-',
                    // $row->size ?? '-',
                    // $row->grade ?? '-',
                    // $row->lokasi ?? '-',
                    // $row->no_carton ?? '-',
                    $row->saldoawal ?? '-',
                    $row->qtyterima ?? '-',
                    $row->qtykeluar ?? '-',
                    $row->saldoakhir ?? '-',
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        return $excel->download($filename);
    }


}
