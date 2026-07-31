<?php

namespace App\Services\ReportBc;;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class PengeluaranService
{

    public function __construct()
    {
    }

    public function getDataRekap($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');

        $caseJenisDokumen = "
            CASE
                WHEN a.jenis_dok = 'BC 3.0' THEN 'BC 3.0'
                WHEN a.jenis_dok = 'BC 2.6.1' AND a.bcno != '-' THEN 'BC 2.6.1 KELUAR'
                WHEN a.jenis_dok = 'BC 2.7' AND a.tujuan NOT IN ('DIKEMBALIKAN', 'DISUBKONTRAKKAN') THEN 'BC 2.7'
                WHEN a.jenis_dok = 'BC 2.5' THEN 'BC 2.5'
                WHEN a.jenis_dok = 'BC 3.3' THEN 'BC 3.3'
                WHEN a.jenis_dok = 'BC 4.1' AND UPPER(a.remark) LIKE '%SEWA%' THEN 'BC 4.1 SEWA'
                WHEN a.jenis_dok = 'BC 4.1' AND UPPER(a.tujuan) LIKE '%SUBKON%' THEN 'BC 4.1 SUBKON'
                WHEN a.jenis_dok = 'BC 4.1' THEN 'BC 4.1 LOKAL'
                ELSE a.jenis_dok
            END
        ";

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $idItemExpr, $matclassExpr) => [
            DB::raw("$caseJenisDokumen as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price)), 2) as nilai_barang"),
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        // BAHAN BAKU / FABRIC / ACCESORIES (non-FG)
        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->whereIn('a.jenis_dok', ['BC 3.0', 'BC 2.6.1', 'BC 2.7', 'BC 2.5', 'BC 3.3', 'BC 4.1'])
                ->where(function ($query) {
                    $query->where('a.jenis_dok', '!=', 'BC 2.7')
                        ->orWhereNotIn('a.tujuan', ['DIKEMBALIKAN', 'DISUBKONTRAKKAN']);
                })
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) != 'FG'")
                ->whereRaw("a.cancel != 'Y'")
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
                "s.itemdesc",
                "a.id_item",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bppbno', 'a.id_item', 'a.price', 'a.jenis_dok', 'a.remark', 'a.tujuan');
        }

        // BARANG JADI (FG)
        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->whereIn('a.jenis_dok', ['BC 3.0', 'BC 2.6.1', 'BC 2.7', 'BC 3.3', 'BC 4.1'])
                ->where(function ($query) {
                    $query->where('a.jenis_dok', '!=', 'BC 2.7')
                        ->orWhereNotIn('a.tujuan', ['DIKEMBALIKAN', 'DISUBKONTRAKKAN']);
                })
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) = 'FG'")
                ->whereRaw("a.cancel != 'Y'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "s.id_so_det",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bppbno', 'a.id_item', 'a.price', 'a.jenis_dok', 'a.remark', 'a.tujuan');
        }

        if ($queryBahanBaku && $queryBarangJadi) {
            $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
        } elseif ($queryBahanBaku) {
            $unionQuery = $queryBahanBaku;
        } elseif ($queryBarangJadi) {
            $unionQuery = $queryBarangJadi;
        } else {
            return collect();
        }

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass as kategori_barang',
                'a.bcno as nomor_daftar',
                'a.bcdate as tanggal_daftar',
                'a.supplier as nama_pengirim',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->orderBy('a.trans_no', 'ASC')
            ->get();
    }

    public function getDataBc33($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 3.3');
        };

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw("'BC 3.3' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            'a.qty',
            'a.curr',
            DB::raw("ROUND(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price), 2) as nilai_barang"),
            DB::raw("ROUND(a.qty * a.price, 2) as nilai_cmt"),
            'a.id_item',
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);

        $queryBarangJadi = null;
        $queryFabric = null;
        $queryGeneral = null;

        // BARANG JADI
        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) = 'FG'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "'BARANG JADI'"
                ));
        }

        // FABRIC
        if (in_array($kategori, ['all', 'fabric'])) {
            $queryFabric = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where('a.bppbno_int', 'like', 'GK%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('F ', s.id_item))",
                    "s.itemdesc",
                    "'FABRIC'"
                ));
        }

        // GENERAL / ACCESORIES
        if (in_array($kategori, ['all', 'accesories'])) {
            $queryGeneral = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where('a.bppbno_int', 'like', 'GEN%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('F ', s.id_item))",
                    "s.itemdesc",
                    "s.matclass"
                ));
        }

        $queries = array_filter([$queryBarangJadi, $queryFabric, $queryGeneral]);

        if (empty($queries)) {
            return collect();
        }

        $unionQuery = array_shift($queries);
        foreach ($queries as $q) {
            $unionQuery = $unionQuery->unionAll($q);
        }

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass',
                'a.matclass as kategori_barang',
                'a.bcno',
                'a.bcno as nomor_daftar',
                'a.bcdate',
                'a.bcdate as tanggal_daftar',
                'a.supplier',
                'a.supplier as nama_pengirim',
                'a.trans_no',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.kode_brg',
                'a.itemdesc',
                'a.itemdesc as uraian_barang',
                'a.unit',
                'a.unit as jenis_satuan',
                'a.qty',
                'a.qty as jumlah_satuan',
                'a.curr',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                'a.nilai_cmt',
                DB::raw('COALESCE(mr.rate, 1) as rate'),
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                DB::raw('(a.nilai_cmt * COALESCE(mr.rate, 1)) as nilai_cmt_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }
    public function getDataBc30($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 3.0');
        };

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw("'BC 3.0' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            'a.qty',
            'a.curr',
            DB::raw("ROUND(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price), 2) as nilai_barang"),
            DB::raw("ROUND(a.qty * a.price, 2) as nilai_cmt"),
            'a.id_item',
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);
        $queryBarangJadi = null;
        $queryFabric = null;
        $queryGeneral = null;

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) = 'FG'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "'BARANG JADI'"
                ));
        }

        if (in_array($kategori, ['all', 'fabric'])) {
            $queryFabric = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where(function ($query) {
                    $query->where('a.bppbno_int', 'like', 'GK%')
                        ->orWhere('a.bppbno_int', 'like', 'OFC%')
                        ->orWhere('a.bppbno_int', 'like', 'GACC%');
                })
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('F ', s.id_item))",
                    "s.itemdesc",
                    "'FABRIC'"
                ));
        }

        if (in_array($kategori, ['all', 'accesories'])) {
            $queryGeneral = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where('a.bppbno_int', 'like', 'GEN%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('F ', s.id_item))",
                    "s.itemdesc",
                    "s.matclass"
                ));
        }

        $queries = array_filter([$queryBarangJadi, $queryFabric, $queryGeneral]);
        if (empty($queries)) {
            return collect();
        }
        $unionQuery = array_shift($queries);
        foreach ($queries as $q) {
            $unionQuery = $unionQuery->unionAll($q);
        }

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass',
                'a.matclass as kategori_barang',
                'a.bcno',
                'a.bcno as nomor_daftar',
                'a.bcdate',
                'a.bcdate as tanggal_daftar',
                'a.supplier',
                'a.supplier as nama_pengirim',
                'a.trans_no',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.kode_brg',
                'a.itemdesc',
                'a.itemdesc as uraian_barang',
                'a.unit',
                'a.unit as jenis_satuan',
                'a.qty',
                'a.qty as jumlah_satuan',
                'a.curr',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                'a.nilai_cmt',
                DB::raw('COALESCE(mr.rate, 1) as rate'),
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                DB::raw('(a.nilai_cmt * COALESCE(mr.rate, 1)) as nilai_cmt_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc261($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.bcno', '!=', '-')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.1');
        };

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idItemExpr, $matclassExpr) => [
            DB::raw("'BC 2.6.1' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit) as unit"),
            DB::raw("IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2) as nilai_barang"),
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);
        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) != 'FG'")
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectCommon(
                "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
                "s.itemdesc",
                "a.id_item",
                "s.matclass"
            ));
        }

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) = 'FG'")
                ->whereRaw("SUBSTRING(a.bppbno, 4, 1) != 'P'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "s.id_so_det",
                    "'BARANG JADI'"
                ));
        }

        $queries = array_filter([$queryBahanBaku, $queryBarangJadi]);
        if (empty($queries)) {
            return collect();
        }
        $unionQuery = array_shift($queries);
        foreach ($queries as $q) {
            $unionQuery = $unionQuery->unionAll($q);
        }

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass',
                'a.matclass as kategori_barang',
                'a.bcno',
                'a.bcno as nomor_daftar',
                'a.bcdate',
                'a.bcdate as tanggal_daftar',
                'a.supplier',
                'a.supplier as nama_pengirim',
                'a.trans_no',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.kode_brg',
                'a.itemdesc',
                'a.itemdesc as uraian_barang',
                'a.unit',
                'a.unit as jenis_satuan',
                'a.qty',
                'a.qty as jumlah_satuan',
                'a.curr',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc27($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';
        $mysql_sb = DB::connection('mysql_sb');

        // $baseFilter = function ($query) {
        //     $query->where('a.jenis_dok', 'BC 2.7')
        //         ->whereRaw("a.cancel != 'Y'");
        // };

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idItemExpr, $matclassExpr) => [
            DB::raw("'BC 2.7' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price)), 2) as nilai_barang"),
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);
        $queryBarangJadi = null;
        $queryBahanBaku = null;

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                // ->where($baseFilter)
                ->where('a.jenis_dok', 'BC 2.7')
                ->whereRaw("a.cancel != 'Y'")
                ->where('a.bppbno', 'like', 'SJ-FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "s.id_so_det",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bppbno', 's.goods_code', 's.itemname', 'a.price');

        }

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                // ->where($baseFilter)
                ->where('a.jenis_dok', 'BC 2.7')
                ->whereRaw("a.cancel != 'Y'")
                ->where('a.bppbno', 'not like', 'SJ-FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectCommon(
                "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
                "s.itemdesc",
                "a.id_item",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bppbno', 'a.id_item', 'a.price');
        }

        $queries = array_filter([$queryBarangJadi, $queryBahanBaku]);
        if (empty($queries)) {
            return collect();
        }
        $unionQuery = array_shift($queries);
        foreach ($queries as $q) {
            $unionQuery = $unionQuery->unionAll($q);
        }

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass',
                'a.matclass as kategori_barang',
                'a.bcno',
                'a.bcno as nomor_daftar',
                'a.bcdate',
                'a.bcdate as tanggal_daftar',
                'a.supplier',
                'a.supplier as nama_pengirim',
                'a.trans_no',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.kode_brg',
                'a.itemdesc',
                'a.itemdesc as uraian_barang',
                'a.unit',
                'a.unit as jenis_satuan',
                'a.qty',
                'a.qty as jumlah_satuan',
                'a.curr',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as rate'),
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc25($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.jenis_dok', 'BC 2.5')
                    ->where('a.cancel', 'N');
        };

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw("'BC 2.5' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price)), 2) as nilai_barang"),
            'a.id_item',
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);

        // BC 2.5 hanya untuk scrap bahan baku (non-FG), tidak ada barang jadi.
        // Kalau kategori diminta 'barang_jadi', tidak ada data yang match -> return kosong.
        if (!in_array($kategori, ['all', 'fabric', 'accesories'])) {
            return collect();
        }

        $queryScrap = $mysql_sb->table('bppb as a')
            ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where($baseFilter)
            ->whereRaw("SUBSTRING(a.bppbno, 4, 2) != 'FG'")
            ->whereBetween($dateField, [$fromDate, $toDate]);

        if ($kategori !== 'all') {
            $searchTerm = '%' . $kategori . '%';
            $queryScrap->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
        }

        $queryScrap->select($selectCommon(
            "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
            "s.itemdesc",
            "s.matclass"
        ))
        ->groupBy('a.bcno', 'a.bppbno', 's.goods_code', 's.itemdesc', 'a.price');

        $unionQuery = $queryScrap;

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass',
                'a.matclass as kategori_barang',
                'a.bcno',
                'a.bcno as nomor_daftar',
                'a.bcdate',
                'a.bcdate as tanggal_daftar',
                'a.supplier',
                'a.supplier as nama_pengirim',
                'a.trans_no',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.kode_brg',
                'a.itemdesc',
                'a.itemdesc as uraian_barang',
                'a.unit',
                'a.unit as jenis_satuan',
                'a.qty',
                'a.qty as jumlah_satuan',
                'a.curr',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as rate'),
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc41($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bppbdate' : 'a.bcdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.jenis_dok', 'BC 4.1')
                    ->where('a.cancel', 'N');
        };

        $jenisDokExpr = "
            CASE
                WHEN UPPER(a.remark) LIKE '%SEWA%' THEN 'BC 4.1 SEWA'
                WHEN UPPER(a.tujuan) LIKE '%SUBKON%' THEN 'BC 4.1 SUBKON'
                ELSE 'BC 4.1 LOKAL'
            END
        ";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idItemExpr, $matclassExpr) => [
            DB::raw("$jenisDokExpr as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price)), 2) as nilai_barang"),
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass")
        ];

        $kategori = strtolower($kategoriBarang);
        $queryBahanBaku = null;
        $queryFabric = null;
        $queryGeneral = null;
        $queryBarangJadi = null;

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) != 'FG'");

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectCommon(
                "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
                "s.itemdesc",
                "a.id_item",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bppbno', 's.goods_code', 's.itemdesc', 'a.price', 'a.remark', 'a.tujuan');
        }

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->whereRaw("SUBSTRING(a.bppbno, 4, 2) = 'FG'")
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "s.id_so_det",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bppbno', 's.goods_code', 's.itemname', 'a.price', 'a.remark', 'a.tujuan');
        }

        $queries = array_filter([$queryBahanBaku, $queryBarangJadi]);
        if (empty($queries)) {
            return collect();
        }
        $unionQuery = array_shift($queries);
        foreach ($queries as $q) {
            $unionQuery = $unionQuery->unionAll($q);
        }

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                DB::raw("'' as kode_kantor"),
                'a.jenis_dokumen',
                'a.matclass',
                'a.matclass as kategori_barang',
                'a.bcno',
                'a.bcno as nomor_daftar',
                'a.bcdate',
                'a.bcdate as tanggal_daftar',
                'a.supplier',
                'a.supplier as nama_pengirim',
                'a.trans_no',
                'a.trans_no as nomor_bpb',
                'a.bppbdate as tanggal_bpb',
                'a.id_item',
                'a.kode_brg',
                'a.itemdesc',
                'a.itemdesc as uraian_barang',
                'a.unit',
                'a.unit as jenis_satuan',
                'a.qty',
                'a.qty as jumlah_satuan',
                'a.curr',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as rate'),
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
        $methodName = 'getData' . ucfirst($cleanKategori);

        $data = $this->$methodName($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang);

        $excel = FastExcel::create('Laporan');
        $sheet = $excel->getSheet();

        $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
            'font' => ['size' => 14, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A1:Q1');

        $judulLaporan = "LAPORAN " . strtoupper($jenis) . " - " . strtoupper(str_replace('-', ' ', $kategori));
        $sheet->writeTo('A2', $judulLaporan, [
            'font' => ['size' => 12, 'style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A2:Q2');

        $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
        $sheet->writeTo('A3', $periode, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A3:Q3');

        $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
        $sheet->writeTo('A4', $filterText, [
            'font' => ['style' => 'bold'],
            'text-align' => 'center'
        ]);
        $sheet->mergeCells('A4:Q4');


        $headerKolom = [
            'No',
            'Kode Kantor',
            'Jenis Dokumen',
            'Kategori Barang',
            'Nomor Daftar',
            'Tanggal Daftar',
            'Nama Penerima / Pembeli',
            'Nomor Bukti Pengeluaran',
            'Tanggal Bukti Pengeluaran',
            'ID Item',
            'Uraian Barang',
            'Jenis Satuan',
            'Jumlah Satuan',
            'Kode Valuta',
            'Nilai Barang',
            'Kurs',
            'Nilai Barang IDR',
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
                    $row->kode_kantor ?? '-',
                    $row->jenis_dokumen ?? $jenisDokumenFixed,
                    $row->kategori_barang ?? '-',
                    $row->nomor_daftar ?? '-',
                    ($row->tanggal_daftar && $row->tanggal_daftar != '0000-00-00' && $row->tanggal_daftar != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '00-00-0000',
                    $row->nama_pengirim ?? '-',
                    $row->nomor_bpb ?? '-',
                    ($row->tanggal_bpb && $row->tanggal_bpb != '0000-00-00' && $row->tanggal_bpb != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '00-00-0000',
                    $row->id_item ?? '-',
                    $row->uraian_barang ?? '-',
                    $row->jenis_satuan ?? '-',
                    (float) ($row->jumlah_satuan ?? 0),
                    $row->kode_valuta ?? '-',
                    (float) ($row->nilai_barang ?? 0),
                    (float) ($row->kurs ?? 0),
                    (float) ($row->nilai_barang_idr ?? 0),
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
        return $excel->download($filename);
    }

}
