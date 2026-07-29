<?php

namespace App\Services\ReportBc;

use Illuminate\Support\Facades\DB;

class MutasiService
{
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


        $sql = "
            SELECT isi.*, mi.itemdesc, mi.goods_code, ac.kpno
            FROM (
                SELECT A.id_jo, A.id_item,
                       SUM(A.sain) - SUM(A.saout) AS saldoawal,
                       SUM(A.qtyin) AS qtyterima,
                       SUM(A.qtyout) AS qtykeluar,
                       (SUM(A.sain) - SUM(A.saout)) + SUM(A.qtyin) - SUM(A.qtyout) AS saldoakhir,
                       A.unit
                FROM (
                    SELECT id_item, id_jo, SUM(qty) AS sain, 0 AS saout, 0 AS qtyin, 0 AS qtyout, unit FROM bpb WHERE bpbdate < ? GROUP BY id_jo, id_item, unit
                    UNION ALL
                    SELECT id_item, id_jo, 0 AS sain, SUM(qty) AS saout, 0 AS qtyin, 0 AS qtyout, unit FROM bppb WHERE bppbdate < ? GROUP BY id_jo, id_item, unit
                    UNION ALL
                    SELECT id_item, id_jo, 0 AS sain, 0 AS saout, SUM(qty) AS qtyin, 0 AS qtyout, unit FROM bpb WHERE bpbdate >= ? AND bpbdate <= ? GROUP BY id_jo, id_item, unit
                    UNION ALL
                    SELECT id_item, id_jo, 0 AS sain, 0 AS saout, 0 AS qtyin, SUM(qty) AS qtyout, unit FROM bppb WHERE bppbdate >= ? AND bppbdate <= ? GROUP BY id_jo, id_item, unit
                ) A
                GROUP BY A.id_jo, A.id_item, A.unit
            ) isi
            INNER JOIN masteritem mi ON isi.id_item = mi.id_item
            INNER JOIN (
                SELECT jd.id_jo, ac.kpno FROM jo_det jd
                INNER JOIN so ON so.id = jd.id_so
                INNER JOIN act_costing ac ON ac.id = so.id_cost
            ) ac ON ac.id_jo = isi.id_jo
            WHERE $whereClass
        ";



        return $mysql_sb->select($sql, [
            $fromDate,
            $fromDate,
            $fromDate, $toDate,
            $fromDate, $toDate
        ]);
    }

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
                ms.goods_code, ms.itemname, ms.styleno, ms.kpno, ms.color, ms.size, ms.country,
                mutasi.id_item, mutasi.id_so_det,
                SUM(saldo_awal) AS saldoawal,
                SUM(penerimaan) AS qtyterima,
                SUM(pengeluaran) AS qtykeluar,
                SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldoakhir
            FROM (
                SELECT * FROM (
                    SELECT saldoawal.id_item, saldoawal.id_so_det,
                           SUM(saldo_awal) + SUM(penerimaan) - SUM(pengeluaran) AS saldo_awal,
                           0 AS penerimaan,
                           0 AS pengeluaran
                    FROM (
                        SELECT id_item, id_so_det, saldo AS saldo_awal, 0 AS penerimaan, 0 AS pengeluaran
                        FROM saldoawal_fg
                        WHERE periode = '2022-10-01'

                        UNION ALL

                        SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran
                        FROM bpb
                        WHERE bpbdate >= '2022-10-01' AND bpbdate < ?
                        AND bpbno LIKE 'FG%'
                        GROUP BY id_item, id_so_det

                        UNION ALL

                        SELECT id_item, id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(qty) AS pengeluaran
                        FROM bppb
                        WHERE bppbdate >= '2022-10-01' AND bppbdate < ?
                        AND bppbno LIKE 'SJ-FG%'
                        GROUP BY id_item, id_so_det
                    ) saldoawal
                    INNER JOIN masterstyle ms ON saldoawal.id_item = ms.id_item AND saldoawal.id_so_det = ms.id_so_det
                    GROUP BY saldoawal.id_item, saldoawal.id_so_det
                ) sa

                UNION ALL

                SELECT id_item, id_so_det, 0 AS saldo_awal, SUM(qty) AS penerimaan, 0 AS pengeluaran
                FROM bpb
                WHERE bpbdate >= ? AND bpbdate <= ?
                AND bpbno LIKE 'FG%'
                GROUP BY id_item, id_so_det

                UNION ALL

                SELECT id_item, id_so_det, 0 AS saldo_awal, 0 AS penerimaan, SUM(qty) AS pengeluaran
                FROM bppb
                WHERE bppbdate >= ? AND bppbdate <= ?
                AND bppbno LIKE 'SJ-FG%'
                GROUP BY id_item, id_so_det
            ) mutasi
            INNER JOIN masterstyle ms ON mutasi.id_item = ms.id_item AND mutasi.id_so_det = ms.id_so_det
            WHERE $whereCategory
            GROUP BY mutasi.id_item, mutasi.id_so_det, ms.goods_code, ms.itemname, ms.styleno, ms.kpno, ms.color, ms.size, ms.country
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
}
