<?php

namespace App\Services\ReportBc;

use Illuminate\Support\Facades\DB;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use Carbon\Carbon;


class MutasiService
{


    public function getDataMutasiBahanBaku($fromDate, $toDate, $kategoriBarang)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $kategori = strtolower($kategoriBarang);
        $result = collect();

        $contentJoinFromMi = "
            INNER JOIN masterdesc bd ON bd.id = mi.id_gen
            INNER JOIN mastercolor mc2 ON mc2.id = bd.id_color
            INNER JOIN masterweight mw ON mw.id = mc2.id_weight
            INNER JOIN masterlength ml ON ml.id = mw.id_length
            INNER JOIN masterwidth mwd ON mwd.id = ml.id_width
            INNER JOIN mastercontents mcnt ON mcnt.id = mwd.id_contents
        ";

        // ===== FABRIC: group by mastercontents.id + unit =====
        if (in_array($kategori, ['all', 'semua', 'fabric'])) {
            $sqlFabric = "
                SELECT isi.id_contents AS id_item, mc.kode_contents AS goods_code, mc.nama_contents AS itemdesc, isi.unit,
                    ROUND(SUM(sal_awal - qty_out_sbl), 2) AS saldoawal,
                    ROUND(SUM(qty_in), 2) AS qtyterima,
                    ROUND(SUM(qty_out), 2) AS qtykeluar,
                    ROUND(SUM(sal_awal + qty_in - qty_out_sbl - qty_out), 2) AS saldoakhir,
                    NULL AS kpno
                FROM (
                    SELECT a.id_item, a.unit, mcnt.id AS id_contents,
                        COALESCE(sal_awal, 0) sal_awal,
                        COALESCE(qty_in, 0) qty_in,
                        COALESCE(qty_out_sbl, 0) qty_out_sbl,
                        COALESCE(qty_out, 0) qty_out,
                        (COALESCE(sal_awal, 0) + COALESCE(qty_in, 0)) fil
                    FROM (
                        SELECT id_item, unit FROM whs_sa_fabric GROUP BY id_item, unit
                        UNION
                        SELECT id_item, unit FROM whs_inmaterial_fabric_det GROUP BY id_item, unit
                    ) a
                    LEFT JOIN (
                        SELECT id_item, unit, SUM(sal_awal) sal_awal FROM (
                            SELECT 'tr' id, id_item, unit, SUM(qty_good) sal_awal
                            FROM whs_inmaterial_fabric_det
                            WHERE tgl_dok < ? AND status = 'Y' GROUP BY id_item, unit

                            UNION

                            SELECT 'sa' id, id_item, unit, ROUND(SUM(qty), 2) sal_awal
                            FROM whs_sa_fabric GROUP BY id_item, unit
                        ) x GROUP BY id_item, unit
                    ) b ON b.id_item = a.id_item AND b.unit = a.unit
                    LEFT JOIN (
                        SELECT id_item, unit, SUM(qty_in) qty_in FROM (
                            SELECT 'T' id, id_item, unit, SUM(qty_good) qty_in
                            FROM whs_inmaterial_fabric_det
                            WHERE tgl_dok BETWEEN ? AND ? AND status = 'Y' GROUP BY id_item, unit

                            UNION

                            SELECT 'M' id, id_item, unit satuan, SUM(qty_mutasi) qty_in
                            FROM whs_mut_lokasi
                            WHERE status = 'Y' AND tgl_mut BETWEEN ? AND ? GROUP BY id_item, satuan
                        ) x GROUP BY id_item, unit
                    ) c ON c.id_item = a.id_item AND c.unit = a.unit
                    LEFT JOIN (
                        SELECT id_item, satuan, SUM(qty_out) qty_out_sbl
                        FROM whs_bppb_det a
                        INNER JOIN whs_bppb_h b ON b.no_bppb = a.no_bppb
                        WHERE b.tgl_bppb < ? AND a.status = 'Y' GROUP BY id_item, satuan
                    ) d ON d.id_item = a.id_item AND d.satuan = a.unit
                    LEFT JOIN (
                        SELECT id_item, satuan, SUM(qty_out) qty_out FROM (
                            SELECT 'T' id, id_item, satuan, SUM(qty_out) qty_out
                            FROM whs_bppb_det a
                            INNER JOIN whs_bppb_h b ON b.no_bppb = a.no_bppb
                            WHERE b.tgl_bppb BETWEEN ? AND ? AND a.status = 'Y' GROUP BY id_item, satuan

                            UNION

                            SELECT 'M' id, id_item, unit satuan, SUM(qty_mutasi) qty_out
                            FROM whs_mut_lokasi
                            WHERE status = 'Y' AND tgl_mut BETWEEN ? AND ? GROUP BY id_item, satuan
                        ) x GROUP BY id_item, satuan
                    ) e ON e.id_item = a.id_item AND e.satuan = a.unit
                    INNER JOIN masteritem mi ON mi.id_item = a.id_item
                    $contentJoinFromMi
                    WHERE (COALESCE(sal_awal, 0) + COALESCE(qty_in, 0)) != 0
                ) isi
                LEFT JOIN mastercontents mc ON mc.id = isi.id_contents
                GROUP BY isi.id_contents, isi.unit
            ";

            $bindings = [
                $fromDate,
                $fromDate, $toDate,
                $fromDate, $toDate,
                $fromDate,
                $fromDate, $toDate,
                $fromDate, $toDate,
            ];

            $fabricRows = $mysql_sb->select($sqlFabric, $bindings);
            $result = $result->concat($fabricRows);
        }

        // ===== ACCESSORIES: group by mastercontents.id + unit (dari bpb/bppb) =====
        if (in_array($kategori, ['all', 'semua', 'accesories', 'accessories'])) {
            $contentJoin = "
                INNER JOIN masteritem mi ON mi.id_item = b.id_item
                $contentJoinFromMi
            ";

            $sqlAcc = "
                SELECT isi.id_contents AS id_item, mc.kode_contents AS goods_code, mc.nama_contents AS itemdesc, isi.unit,
                    SUM(isi.sain) - SUM(isi.saout) AS saldoawal,
                    SUM(isi.qtyin) AS qtyterima,
                    SUM(isi.qtyout) AS qtykeluar,
                    (SUM(isi.sain) - SUM(isi.saout)) + SUM(isi.qtyin) - SUM(isi.qtyout) AS saldoakhir,
                    NULL AS kpno
                FROM (
                    SELECT mcnt.id AS id_contents, SUM(b.qty) AS sain, 0 AS saout, 0 AS qtyin, 0 AS qtyout, b.unit
                    FROM bpb b
                    $contentJoin
                    WHERE b.bpbdate < ? AND mi.matclass IN ('ACCESORIES PACKING', 'ACCESORIES SEWING')
                    GROUP BY mcnt.id, b.unit

                    UNION ALL

                    SELECT mcnt.id AS id_contents, 0 AS sain, SUM(b.qty) AS saout, 0 AS qtyin, 0 AS qtyout, b.unit
                    FROM bppb b
                    $contentJoin
                    WHERE b.bppbdate < ? AND mi.matclass IN ('ACCESORIES PACKING', 'ACCESORIES SEWING')
                    GROUP BY mcnt.id, b.unit

                    UNION ALL

                    SELECT mcnt.id AS id_contents, 0 AS sain, 0 AS saout, SUM(b.qty) AS qtyin, 0 AS qtyout, b.unit
                    FROM bpb b
                    $contentJoin
                    WHERE b.bpbdate >= ? AND b.bpbdate <= ? AND mi.matclass IN ('ACCESORIES PACKING', 'ACCESORIES SEWING')
                    GROUP BY mcnt.id, b.unit

                    UNION ALL

                    SELECT mcnt.id AS id_contents, 0 AS sain, 0 AS saout, 0 AS qtyin, SUM(b.qty) AS qtyout, b.unit
                    FROM bppb b
                    $contentJoin
                    WHERE b.bppbdate >= ? AND b.bppbdate <= ? AND mi.matclass IN ('ACCESORIES PACKING', 'ACCESORIES SEWING')
                    GROUP BY mcnt.id, b.unit
                ) isi
                LEFT JOIN mastercontents mc ON mc.id = isi.id_contents
                GROUP BY isi.id_contents, isi.unit
            ";

            $accRows = $mysql_sb->select($sqlAcc, [
                $fromDate,
                $fromDate,
                $fromDate, $toDate,
                $fromDate, $toDate,
            ]);

            $result = $result->concat($accRows);
        }

        return $result;
    }


    public function getDataMutasiBarangJadi($fromDate, $toDate, $kategoriBarang, $filterInhouse = false)
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
                sbws.product_item,
                sbws.product_group,
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

                        -- TIDAK ada filter INHOUSE di sini (baseline historic)
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
            LEFT JOIN laravel_nds.master_sb_ws sbws ON ms.kpno = sbws.ws AND ms.styleno = sbws.styleno AND ms.color = sbws.color AND ms.size = sbws.size
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
            'Style',
            'Product Group',
            'Product Item',
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
                $row->styleno ?? '-',
                $row->product_group ?? '-',
                $row->product_item ?? '-',
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
            // 'Color',
            // 'Size',
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
                // $row->color ?? '-',
                // $row->size ?? '-',
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


    public function getDataMutasiBarangJadiMerge($fromDate, $toDate, $kategoriBarang)
    {
        $produksi = collect($this->getDataMutasiBarangJadiNew($fromDate, $toDate, $kategoriBarang, false))
            ->map(function ($row) {
                return (object) [
                    'sumber'        => 'FG',
                    'ws'            => $row->kpno,
                    'styleno'       => $row->styleno,
                    'color'         => $row->color,
                    'size'          => $row->size,
                    'product_group' => $row->product_group ?? '-',
                    'product_item'  => $row->product_item ?? '-',
                    'saldoawal'     => $row->saldoawal,
                    'qtyterima'     => $row->qtyterima,
                    'qtykeluar'     => $row->qtykeluar,
                    'saldoakhir'    => $row->saldoakhir,
                ];
            });

        $gudang = collect($this->getDataMutasiBarangJadiGudangNew($fromDate, $toDate, $kategoriBarang, false))
            ->map(function ($row) {
                return (object) [
                    'sumber'        => 'FG WAREHOUSE',
                    'ws'            => $row->ws,
                    'styleno'       => $row->styleno,
                    'color'         => $row->color,
                    'size'          => $row->size,
                    'product_group' => $row->product_group,
                    'product_item'  => $row->product_item,
                    'saldoawal'     => $row->saldoawal,
                    'qtyterima'     => $row->qtyterima,
                    'qtykeluar'     => $row->qtykeluar,
                    'saldoakhir'    => $row->saldoakhir,
                ];
            });

        return $produksi->concat($gudang)
            ->groupBy(fn ($row) => $row->ws . '|' . $row->styleno)
            ->map(function ($rows) {
                $first = $rows->first();

                return (object) [
                    'ws'            => $first->ws,
                    'styleno'       => $first->styleno,
                    'color'         => $rows->pluck('color')->filter()->unique()->implode(', '),
                    'size'          => $rows->pluck('size')->filter()->unique()->implode(', '),
                    'product_group' => $rows->pluck('product_group')->first(fn ($v) => $v && $v !== '-') ?? '-',
                    'product_item'  => $rows->pluck('product_item')->first(fn ($v) => $v && $v !== '-') ?? '-',
                    'saldoawal'     => $rows->sum('saldoawal'),   // dijumlah dari kedua sumber
                    'qtyterima'     => $rows->sum('qtyterima'),
                    'qtykeluar'     => $rows->sum('qtykeluar'),
                    'saldoakhir'    => $rows->sum('saldoakhir'),
                ];
            })
            ->values();
    }


    public function exportExcelBarangJadiMerge($fromDate, $toDate)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $data = $this->getDataMutasiBarangJadiMerge($fromDate, $toDate, 'all');

        $fileName = 'laporan-mutasi-barang-jadi-merge';

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
            ['LAPORAN MUTASI BARANG JADI MERGE'],
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

    public function getDataMutasiBarangJadiGudangNew($fromDate, $toDate, $kategoriBarang)
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
                        AND sumber_pemasukan NOT IN ('EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL')
                        GROUP BY id_so_det, grade, lokasi, no_carton
                        UNION ALL
                        SELECT id_so_det, SUM(qty) AS qty_in, 0 AS qty_out, grade, lokasi, no_carton
                        FROM fg_stok_bpb_scan
                        WHERE tgl_terima < '$saldo_awal'
                        AND sumber_pemasukan NOT IN ('MUTASI INTERNAL')
                        GROUP BY id_so_det, grade, lokasi, no_carton
                        UNION ALL
                        SELECT id_so_det, 0 AS qty_in, SUM(qty_out) AS qty_out, grade, lokasi, no_carton
                        FROM fg_stok_bppb
                        WHERE tgl_pengeluaran < '$saldo_awal'
                        AND tujuan NOT IN ('EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL')
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
                    x.buyer,
                    x.ws,
                    x.color,
                    x.styleno,
                    x.size,
                    SUM(x.qty_saldo_awal_adjustment_before) AS qty_saldo_awal_adjustment_before,
                    SUM(x.qty_adjustment_before) AS qty_adjustment_before,
                    SUM(x.qty_adjustment) AS qty_adjustment,
                    SUM(x.qty_terima_qc_reject_before) AS qty_terima_qc_reject_before,
                    SUM(x.qty_terima_qc_reject) AS qty_terima_qc_reject,
                    SUM(x.qty_keluar_sewing_before) AS qty_keluar_sewing_before,
                    SUM(x.qty_keluar_sewing) AS qty_keluar_sewing,
                    SUM(x.qty_keluar_qa_before) AS qty_keluar_qa_before,
                    SUM(x.qty_keluar_qa) AS qty_keluar_qa
                FROM (
                    SELECT
                        buyer,
                        ws,
                        color,
                        styleno,
                        size,
                        saldo_awal.qty_awal AS qty_saldo_awal_adjustment_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 qty_terima_qc_reject_before,
                        0 qty_terima_qc_reject,
                        0 qty_keluar_sewing_before,
                        0 qty_keluar_sewing,
                        0 qty_keluar_qa_before,
                        0 qty_keluar_qa

                    FROM saldo_awal

                    UNION ALL

                    SELECT
                        buyer.supplier AS buyer,
                        act_costing.kpno ws,
                        masterstyle.color,
                        act_costing.styleno,
                        masterstyle.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        IF(bppbdate >= '$saldo_awal' AND bppbdate < '$tgl_awal', bppb.qty, 0) AS qty_terima_qc_reject_before,
                        IF(bppbdate >= '$tgl_awal', bppb.qty, 0) AS qty_terima_qc_reject,
                        0 qty_keluar_sewing_before,
                        0 qty_keluar_sewing,
                        0 qty_keluar_qa_before,
                        0 qty_keluar_qa

                    FROM signalbit_erp.bppb
                    INNER JOIN signalbit_erp.masterstyle ON masterstyle.id_item = bppb.id_item
                    INNER JOIN signalbit_erp.mastersupplier ON mastersupplier.Id_Supplier = bppb.id_supplier
                    LEFT JOIN (SELECT sod.id_so, sod.id id_so_det FROM signalbit_erp.so_det sod GROUP BY sod.id) tmpjod ON tmpjod.id_so_det = bppb.id_so_det
                    LEFT JOIN signalbit_erp.so ON so.id = tmpjod.id_so
                    LEFT JOIN signalbit_erp.act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN signalbit_erp.mastersupplier buyer ON buyer.Id_Supplier = act_costing.id_buyer
                    WHERE MID(bppbno,4,2) IN ('FG') AND bppbdate <= '$tgl_akhir' AND mastersupplier.supplier = 'BARANG JADI STOCK' AND COALESCE(bppb.jenis_trans, '-') NOT IN ('Pengiriman ke Gudang Barang Jadi', '')
                    UNION ALL

                    SELECT
                        m.buyer,
                        m.ws,
                        m.color,
                        m.styleno,
                        m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject,
                        0 qty_keluar_sewing_before, 0 qty_keluar_sewing,
                        0 qty_keluar_qa_before, 0 qty_keluar_qa
                    FROM fg_stok_bpb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan NOT IN ('EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL')

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        IF(a.tgl_terima >= '$saldo_awal' AND a.tgl_terima < '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject_before,
                        IF(a.tgl_terima >= '$tgl_awal', a.qty, 0) AS qty_terima_qc_reject,
                        0 qty_keluar_sewing_before,
                        0 qty_keluar_sewing,
                        0 qty_keluar_qa_before,
                        0 qty_keluar_qa
                    FROM fg_stok_bpb_scan a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_terima <= '$tgl_akhir'
                    AND a.sumber_pemasukan NOT IN ('MUTASI INTERNAL')

                    UNION ALL

                    SELECT
                        m.buyer, m.ws, m.color, m.styleno, m.size,
                        0 qty_saldo_awal_adjustment_before,
                        0 qty_adjustment_before,
                        0 qty_adjustment,
                        0 qty_terima_qc_reject_before,
                        0 qty_terima_qc_reject,
                        IF(tgl_pengeluaran >= '$saldo_awal' AND tgl_pengeluaran < '$tgl_awal', a.qty_out, 0) AS qty_keluar_sewing_before,
                        IF(tgl_pengeluaran >= '$tgl_awal', a.qty_out, 0) AS qty_keluar_sewing,
                        0 qty_keluar_qa_before,
                        0 qty_keluar_qa
                    FROM fg_stok_bppb a
                    LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                    WHERE a.tgl_pengeluaran <= '$tgl_akhir'
                    AND a.tujuan NOT IN ('EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL')
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
                    CASE WHEN '$tgl_awal' = '$saldo_awal'
                        THEN COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                        ELSE COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                            + COALESCE(ad.qty_terima_qc_reject_before,0)
                            - COALESCE(ad.qty_keluar_sewing_before,0)
                            - COALESCE(ad.qty_keluar_qa_before,0)
                    END
                ) AS saldo_awal,
                COALESCE(ad.qty_terima_qc_reject,0) AS terima_qc_reject,
                COALESCE(ad.qty_keluar_sewing,0)    AS keluar_sewing,
                COALESCE(ad.qty_keluar_qa,0)        AS keluar_qa,
                (
                    CASE WHEN '$tgl_awal' = '$saldo_awal'
                        THEN COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                        ELSE COALESCE(ad.qty_saldo_awal_adjustment_before,0)
                            + COALESCE(ad.qty_terima_qc_reject_before,0)
                            - COALESCE(ad.qty_keluar_sewing_before,0)
                            - COALESCE(ad.qty_keluar_qa_before,0)
                    END
                    + COALESCE(ad.qty_terima_qc_reject,0)
                    - COALESCE(ad.qty_keluar_sewing,0)
                    - COALESCE(ad.qty_keluar_qa,0)
                ) AS saldo_akhir
            FROM all_data ad
            LEFT JOIN master_sb_ws m
                ON ad.buyer = m.buyer AND ad.ws = m.ws AND ad.styleno = m.styleno
                AND ad.color = m.color AND ad.size = m.size
            GROUP BY ad.buyer, ad.ws, ad.styleno
            ORDER BY ad.buyer ASC, ad.color ASC
        ");

        $rows = collect($data_preview)->map(fn ($row) => (array) $row)->toArray();

        return collect($rows)->map(function ($row) {
            return (object) [
                'ws'            => $row['ws'] ?? '-',
                'styleno'       => $row['styleno'] ?? '-',
                'product_group' => $row['product_group'] ?? '-',
                'product_item'  => $row['product_item'] ?? '-',
                'color'         => $row['color'] ?? '-',
                'size'          => $row['size'] ?? '-',
                'saldoawal'     => $row['saldo_awal'] ?? 0,
                'qtyterima'     => $row['terima_qc_reject'] ?? 0,
                'qtykeluar'     => ($row['keluar_sewing'] ?? 0) + ($row['keluar_qa'] ?? 0),
                'saldoakhir'    => $row['saldo_akhir'] ?? 0,
            ];
        });
    }
    public function getDataMutasiBarangJadiNew($fromDate, $toDate, $kategoriBarang, $filterInhouse = false)
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
                sbws.product_item,
                sbws.product_group,
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
                        AND bppb.bppbno LIKE 'SJ-FG%' AND COALESCE(bppb.jenis_trans, '-') NOT IN ('Pengiriman ke Gudang Barang Jadi', '')
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
                AND bppb.bppbno LIKE 'SJ-FG%' AND COALESCE(bppb.jenis_trans, '-') NOT IN ('Pengiriman ke Gudang Barang Jadi', '')
                GROUP BY bppb.id_item, bppb.id_so_det
            ) mutasi
            INNER JOIN masterstyle ms ON mutasi.id_item = ms.id_item AND mutasi.id_so_det = ms.id_so_det
            LEFT JOIN laravel_nds.master_sb_ws sbws ON ms.kpno = sbws.ws AND ms.styleno = sbws.styleno AND ms.color = sbws.color AND ms.size = sbws.size
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




}
