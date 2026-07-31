<?php

namespace App\Services\ReportBc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class PemasukanService
{

    public function __construct()
    {
    }

    public function getDataRekap($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');


        $caseJenisDokumen = "
            CASE
                WHEN a.jenis_dok = '2.3' AND a.invno LIKE '%PJT%' THEN 'BC 2.3 IMPOR PJT'
                WHEN a.jenis_dok = '2.3' AND a.invno NOT LIKE '%PJT%' AND a.invno NOT LIKE '%PIB%' AND a.invno NOT LIKE '%PIBK%' THEN 'BC 2.3 IMPOR'
                WHEN a.jenis_dok = '2.6.2' THEN 'BC 2.6.2 MASUK'
                WHEN a.jenis_dok = '2.7' THEN 'BC 2.7 MASUK'
                WHEN a.jenis_dok = '4.0' AND UPPER(a.invno) NOT LIKE '%SEWA%' AND UPPER(a.tujuan) NOT LIKE '%SUBKON%' THEN 'BC 4.0'
                WHEN a.jenis_dok = '4.0' AND UPPER(a.invno) LIKE '%SEWA%' THEN 'BC 4.0 (SEWA)'
                WHEN a.jenis_dok = '4.0' AND UPPER(a.invno) NOT LIKE '%SEWA%' AND UPPER(a.tujuan) LIKE '%SUBKON%' THEN 'BC 4.0 SUBKON'
                WHEN d.area = 'I' AND a.invno LIKE '%PIB%' AND a.invno NOT LIKE '%PIBK%' THEN 'BC 2.0 IMPOR PIB'
                WHEN d.area = 'I' AND a.invno LIKE '%PIBK%' THEN 'BC 2.1 IMPOR PIBK'
                WHEN d.status_kb = 'KITTE' AND d.area = 'L' THEN 'BC 2.4 KITTE'
                ELSE __ELSE_RULE__
            END
        ";


        $selectData = fn ($jenisDokElse, $bcdateExpr, $kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw(str_replace('__ELSE_RULE__', $jenisDokElse, $caseJenisDokumen) . " as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            DB::raw("$bcdateExpr as bcdate"),
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate as trans_date',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            'a.qty',
            'a.curr',
            DB::raw("ROUND(IFNULL(a.price_bc, a.price) * a.qty, 2) as nilai_barang"),
            'a.berat_bersih',
            'a.berat_kotor',
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            'a.id_item',
            DB::raw("$matclassExpr as matclass")
        ];

        // $queryBahanBaku = $mysql_sb->table('bpb as a')
        //     ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
        //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
        //     ->where('a.cancel', 'N')
        //     ->where('a.jenis_dok', '!=', 'INHOUSE')
        //     ->where('a.bpbno', 'not like', 'FG%')
        //     ->where()
        //     ->whereBetween($dateField, [$fromDate, $toDate])
        //     ->select($selectData(
        //         "a.jenis_dok",
        //         "IF(a.bcdate IS NULL OR a.bcdate = '0000-00-00', a.bpbdate, a.bcdate)",
        //         "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT(s.mattype, ' ', a.id_item), s.goods_code)",
        //         "CONCAT_WS(' ', s.itemdesc, s.color, s.size, s.add_info)",
        //         "s.matclass"
        //     ));


        // $queryBarangJadi = $mysql_sb->table('bpb as a')
        //     ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
        //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
        //     ->where('a.cancel', 'N')
        //     ->where('a.jenis_dok', '!=', 'INHOUSE')
        //     ->where('a.bpbno', 'like', 'FG%')
        //     ->whereBetween($dateField, [$fromDate, $toDate])
        //     ->select($selectData(
        //         "'N/A'",
        //         "a.bcdate",
        //         "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT('FG ', a.id_item), s.goods_code)",
        //         "s.itemname",
        //         "'BARANG JADI'"
        //     ));

        // $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', '!=', 'INHOUSE')
                ->where('a.bpbno', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                 "a.jenis_dok",
                "IF(a.bcdate IS NULL OR a.bcdate = '0000-00-00', a.bpbdate, a.bcdate)",
                "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT(s.mattype, ' ', a.id_item), s.goods_code)",
                "CONCAT_WS(' ', s.itemdesc, s.color, s.size, s.add_info)",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', '!=', 'INHOUSE')
                ->where('a.bpbno', 'like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "'N/A'",
                    "a.bcdate",
                    "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT('FG ', a.id_item), s.goods_code)",
                    "s.itemname",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if ($queryBahanBaku && $queryBarangJadi) {
            $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
        } elseif ($queryBahanBaku) {
            $unionQuery = $queryBahanBaku;
        } else {
            $unionQuery = $queryBarangJadi;
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
                'a.nomor_aju',
                'a.bcno as nomor_daftar',
                'a.bcdate as tanggal_daftar',
                'a.supplier as nama_pengirim',
                'a.trans_no as nomor_bpb',
                'a.trans_date as tanggal_bpb',
                'a.id_item as id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                'a.berat_bersih',
                'a.berat_kotor',
                'a.tujuan'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->orderBy('a.trans_no', 'ASC')
            ->get();
    }

    public function getDataBc23($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');

        $excludeInvno = function ($query) {
            $query->where('a.invno', 'not like', '%PJT%')
                ->where('a.invno', 'not like', '%PIB%')
                ->where('a.invno', 'not like', '%PIBK%');
        };

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw("'BC 2.3' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate as trans_date',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit) as unit"),
            DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)), 2) as nilai_barang"),
            'a.id_item',
            'a.satuan_bc',
            DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty_bc"),
            DB::raw("$matclassExpr as matclass"),
        ];

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.3')
                ->where('a.bpbno', 'not like', 'FG%')
                ->where($excludeInvno)
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, ' ', s.id_item))",
                's.itemdesc',
                's.matclass'
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

            // $sql = vsprintf(str_replace('?', "'%s'", $queryBahanBaku->toSql()), $queryBahanBaku->getBindings());
            // dd($sql);
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.3')
                ->where('a.bpbno', 'like', 'FG%')
                ->where($excludeInvno)
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    's.itemname',
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if ($queryBahanBaku && $queryBarangJadi) {
            $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
        } elseif ($queryBahanBaku) {
            $unionQuery = $queryBahanBaku;
        } else {
            $unionQuery = $queryBarangJadi;
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
                'a.trans_date as tanggal_bpb',
                'a.id_item as id_item',
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
            ->get();
    }

    public function getDataBc262($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $unitExpr, $qtyExpr, $nilaiBarangExpr, $matclassExpr) => [
            DB::raw("'BC 2.6.2 MASUK' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate as trans_date',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("$unitExpr as unit"),
            DB::raw("$qtyExpr as qty"),
            'a.curr',
            DB::raw("$nilaiBarangExpr as nilai_barang"),
            'a.id_item',
            DB::raw("$matclassExpr as matclass"),
        ];

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.2')
                ->where('a.bpbno', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
                "s.itemdesc",
                "IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit)",
                "IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)",
                "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2)",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.2')
                ->where('a.bpbno', 'like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "a.unit",
                    "a.qty",
                    "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty, 2)",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if ($queryBahanBaku && $queryBarangJadi) {
            $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
        } elseif ($queryBahanBaku) {
            $unionQuery = $queryBahanBaku;
        } else {
            $unionQuery = $queryBarangJadi;
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
                'a.trans_date as tanggal_bpb',
                'a.id_item as id_item',
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
            ->get();
    }

    public function getDataBc40($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');

        $excludeCommon = function ($query) {
            $query->where('a.cancel', 'N')
                  ->where('a.jenis_dok', 'BC 4.0')
                  ->whereRaw("UPPER(a.invno) NOT LIKE '%SEWA%'")
                  ->where('a.tujuan', 'not like', '%SUBKON%');
        };

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw("'BC 4.0' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate as trans_date',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
            'a.id_item',
            'a.remark',
            DB::raw("$matclassExpr as matclass"),
        ];

        // $queryBahanBaku = $mysql_sb->table('bpb as a')
        //     ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
        //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
        //     ->where('a.bpbno', 'not like', 'FG%')
        //     ->where($excludeCommon)
        //     ->whereBetween($dateField, [$fromDate, $toDate])
        //     ->select($selectData(
        //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
        //         "s.itemdesc",
        //         "s.matclass"
        //     ))
        //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        // $queryBarangJadi = $mysql_sb->table('bpb as a')
        //     ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
        //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
        //     ->where('a.bpbno', 'like', 'FG%')
        //     ->where('d.area', '=', 'L')
        //     ->where($excludeCommon)
        //     ->whereBetween($dateField, [$fromDate, $toDate])
        //     ->select($selectData(
        //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
        //         "s.itemname",
        //         "'BARANG JADI'"
        //     ))
        //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        // $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 4.0')
                ->where('a.bpbno', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, ' ', s.id_item))",
                's.itemdesc',
                's.matclass'
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 4.0')
                ->where('a.bpbno', 'like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    's.itemname',
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if ($queryBahanBaku && $queryBarangJadi) {
            $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
        } elseif ($queryBahanBaku) {
            $unionQuery = $queryBahanBaku;
        } else {
            $unionQuery = $queryBarangJadi;
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
                'a.trans_date as tanggal_bpb',
                'a.id_item as id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                'a.remark'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }


    public function getDataBc27($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

        $mysql_sb = DB::connection('mysql_sb');

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw("'BC 2.7' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate as trans_date',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
            'a.id_item',
            DB::raw("$matclassExpr as matclass"),
        ];

        // $queryBahanBaku = $mysql_sb->table('bpb as a')
        //     ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
        //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
        //     ->where('a.cancel', 'N')
        //     ->where('a.jenis_dok', 'BC 2.7')
        //     ->where('a.bpbno', 'not like', 'FG%')
        //     ->where('a.tujuan', 'not regexp', 'SUBKON')
        //     ->whereBetween($dateField, [$fromDate, $toDate])
        //     ->select($selectData(
        //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
        //         "s.itemdesc",
        //         "s.matclass"
        //     ))
        //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');


        // $queryBarangJadi = $mysql_sb->table('bpb as a')
        //     ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
        //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
        //     ->where('a.cancel', 'N')
        //     ->where('a.jenis_dok', 'BC 2.7')
        //     ->where('a.bpbno', 'like', 'FG%')
        //     ->where('a.tujuan', 'not regexp', 'SUBKON')
        //     ->whereBetween($dateField, [$fromDate, $toDate])
        //     ->select($selectData(
        //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
        //         "s.itemname",
        //         "'BARANG JADI'"
        //     ))
        //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        // $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.7')
                ->where('a.tujuan', 'not regexp', 'SUBKON')
                ->where('a.bpbno', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, ' ', s.id_item))",
                's.itemdesc',
                's.matclass'
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.7')
                 ->where('a.tujuan', 'not regexp', 'SUBKON')
                ->where('a.bpbno', 'like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    's.itemname',
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
        }

        if ($queryBahanBaku && $queryBarangJadi) {
            $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
        } elseif ($queryBahanBaku) {
            $unionQuery = $queryBahanBaku;
        } else {
            $unionQuery = $queryBarangJadi;
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
                'a.trans_date as tanggal_bpb',
                'a.id_item as id_item',
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
            'Nama ' . ($jenis == 'pemasukan' ? 'Pengirim' : 'Penerima'),
            'Nomor BPB',
            'Tanggal BPB',
            'ID Item',
            'Uraian Barang',
            'Jenis Satuan',
            'Jumlah Satuan',
            'Kode Valuta',
            'Nilai Barang',
            'Kurs',
            'Nilai Barang IDR'
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
