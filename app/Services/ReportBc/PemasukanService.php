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
        $dateField = 'a.bpbdate';

        $mysql_sb = DB::connection('mysql_sb');

        $kategori = strtolower(trim($kategoriBarang));

        $selectData = fn ($jenisDokExpr, $bcdateExpr, $kodeBrgExpr, $itemdescExpr, $matclassExpr, $idItemExpr, $qtySumExpr = "SUM(IF(a.qty = 0, IFNULL(a.qty_temp, 0), a.qty))", $qtyField = "a.qty") => [
            DB::raw("a.jenis_dok as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            DB::raw("$bcdateExpr as bcdate"),
            DB::raw("a.bpbno_int as trans_no"),
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("$qtySumExpr as qty"),
            'a.curr',
            DB::raw("SUM(ROUND(IFNULL(a.price_bc, a.price) * $qtyField, 2)) as nilai_barang"),
            DB::raw("SUM(a.berat_bersih) as berat_bersih"),
            DB::raw("SUM(a.berat_kotor) as berat_kotor"),
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku_Bpb = null;
        $queryBahanBaku_Whs = null;
        $queryBarangJadi = null;
        $queryFgStokBpb = null;
        $queryFgStokBpbScan = null;

        if (in_array($kategori, ['all', 'accesories', 'accessories', 'sample', 'bahan baku', 'bahan_baku'])) {
            $queryBahanBaku_Bpb = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.bpbno_int', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->whereRaw("NOT (IFNULL(a.jenis_dok, '') = 'INHOUSE' AND s.matclass = 'SAMPLE')");

            if (in_array($kategori, ['accesories', 'accessories'])) {
                $queryBahanBaku_Bpb->whereIn('s.matclass', ['ACCESORIES PACKING', 'ACCESORIES SEWING']);
            } elseif (in_array($kategori, ['bahan baku', 'bahan_baku', 'all'])) {
                $queryBahanBaku_Bpb->whereNotIn('s.matclass', ['BARANG JADI', 'SAMPLE', 'FABRIC']);
            } elseif ($kategori === 'sample') {
                $queryBahanBaku_Bpb->where('s.matclass', 'SAMPLE');
            }

            $queryBahanBaku_Bpb->select($selectData(
                "a.jenis_dok",
                "IF(a.bcdate IS NULL OR a.bcdate = '0000-00-00', a.bpbdate, a.bcdate)",
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                "mcnt.nama_contents",
                "s.matclass",
                "mcnt.id"
            ))
            ->groupBy('mcnt.id', 'a.unit');
        }

        // ===== QUERY WAREHOUSE (Khusus FABRIC - Murni WHS tanpa Join BPB) =====
        if (in_array($kategori, ['all', 'fabric', 'bahan baku', 'bahan_baku'])) {
            $queryBahanBaku_Whs = $mysql_sb->table('whs_inmaterial_fabric_det as wd')
                ->leftJoin('whs_inmaterial_fabric as wh', 'wd.no_dok', '=', 'wh.no_dok')
                ->join('masteritem as s', 'wd.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->where('wd.no_dok', 'not like', 'FG%')
                ->whereBetween('wd.tgl_dok', [$fromDate, $toDate])
                ->where('s.matclass', 'FABRIC')
                ->select([
                    DB::raw("wh.type_bc as jenis_dokumen"),
                    DB::raw("wh.no_daftar as bcno"),
                    DB::raw("wh.tgl_dok as bcdate"),
                    DB::raw("wh.no_dok as trans_no"),
                    DB::raw("wh.tgl_dok as bpbdate"),
                    DB::raw("wh.supplier as supplier"),
                    DB::raw("IFNULL(mcnt.kode_contents, mcnt.id) as kode_brg"),
                    DB::raw("mcnt.nama_contents as itemdesc"),
                    DB::raw("wd.unit as unit"),
                    DB::raw("SUM(wd.qty_good) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("wh.no_aju as nomor_aju"),
                    DB::raw("'-' as tujuan"),
                    DB::raw("mcnt.id as id_item"),
                    DB::raw("s.matclass as matclass"),
                    DB::raw("NULL as id_so_det")
                ])
                ->groupBy('wh.no_daftar', 'wd.unit');
        }

        // ===== 3. QUERY BARANG JADI =====
        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {

            $subMsw = function ($query) {
                $query->from('laravel_nds.master_sb_ws')
                      ->select('id_so_det', DB::raw('MAX(styleno) as styleno'), DB::raw('MAX(color) as color'), DB::raw('MAX(ws) as ws'))
                      ->groupBy('id_so_det');
            };

            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
                ->join('so', 'sod.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->leftJoinSub($subMsw, 'msw', 'a.id_so_det', '=', 'msw.id_so_det')
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
                ->where('sod.cancel', 'N')
                ->where('a.bpbno_int', 'like', 'FG%')
                ->whereNotIn('a.id_supplier', ['1038', '1039'])
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "a.jenis_dok as jenis_dokumen",
                    "a.bcdate",
                    "IFNULL(msw.styleno, ac.styleno)",
                    "msw.color",
                    "'BARANG JADI'",
                    "IFNULL(msw.ws, ac.kpno)"
                ))
                ->groupBy('ac.kpno', 'a.bpbno_int', 'a.id_so_det');

            $queryFgStokBpb = $mysql_sb->table('laravel_nds.fg_stok_bpb as a')
                ->leftJoinSub($subMsw, 'm', 'a.id_so_det', '=', 'm.id_so_det')
                ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
                ->join('so', 'sd.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
                ->where('sd.cancel', 'N')
                ->whereBetween('a.tgl_terima', [$fromDate, $toDate])
                ->whereNotIn('a.sumber_pemasukan', ['EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL'])
                ->select([
                    DB::raw("'INHOUSE' as jenis_dokumen"),
                    DB::raw("'-' as bcno"),
                    DB::raw("a.tgl_terima as bcdate"),
                    DB::raw("a.no_trans as trans_no"),
                    DB::raw("a.tgl_terima as bpbdate"),
                    DB::raw("'PRODUCTION-SEWING' as supplier"),
                    DB::raw("IFNULL(m.styleno, ac.styleno) as kode_brg"),
                    DB::raw("CONCAT(IFNULL(m.styleno, ac.styleno), ' - ', IFNULL(m.color,'-')) as itemdesc"),
                    DB::raw("'PCS' as unit"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("'-' as nomor_aju"),
                    DB::raw("a.sumber_pemasukan as tujuan"),
                    DB::raw("IFNULL(m.ws, ac.kpno) as id_item"),
                    DB::raw("'BARANG JADI' as matclass"),
                    'a.id_so_det',
                ])
                ->groupBy('ac.kpno', 'a.no_trans', 'a.id_so_det');

            $queryFgStokBpbScan = $mysql_sb->table('laravel_nds.fg_stok_bpb_scan as a')
                ->leftJoinSub($subMsw, 'm', 'a.id_so_det', '=', 'm.id_so_det')
                ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
                ->join('so', 'sd.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
                ->where('sd.cancel', 'N')
                ->whereBetween('a.tgl_terima', [$fromDate, $toDate])
                ->whereNotIn('a.sumber_pemasukan', ['EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL'])
                ->select([
                    DB::raw("'INHOUSE' as jenis_dokumen"),
                    DB::raw("'-' as bcno"),
                    DB::raw("a.tgl_terima as bcdate"),
                    DB::raw("a.no_trans as trans_no"),
                    DB::raw("a.tgl_terima as bpbdate"),
                    DB::raw("'PRODUCTION-SEWING' as supplier"),
                    DB::raw("IFNULL(m.styleno, ac.styleno) as kode_brg"),
                    DB::raw("CONCAT(IFNULL(m.styleno, ac.styleno), ' - ', IFNULL(m.color,'-')) as itemdesc"),
                    DB::raw("'PCS' as unit"),
                    DB::raw("COUNT(*) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("'-' as nomor_aju"),
                    DB::raw("a.sumber_pemasukan as tujuan"),
                    DB::raw("IFNULL(m.ws, ac.kpno) as id_item"),
                    DB::raw("'BARANG JADI' as matclass"),
                    'a.id_so_det',
                ])
                ->groupBy('ac.kpno', 'a.no_trans', 'a.id_so_det');
        }

        // 4. UNION ALL QUERIES
        $unionQuery = null;
        // Daftarkan semua query (termasuk yang dipecah jadi Bpb dan Whs)
        foreach ([$queryBahanBaku_Bpb, $queryBahanBaku_Whs, $queryBarangJadi, $queryFgStokBpb, $queryFgStokBpbScan] as $q) {
            if (!$q) continue;
            $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
        }

        if (!$unionQuery) {
            return collect([]);
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
                'a.bpbdate as tanggal_bpb',
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
                'a.tujuan',
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->orderBy('a.trans_no', 'ASC')
            ->get();
    }

    public function getDataBc23($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bpbdate';

        $mysql_sb = DB::connection('mysql_sb');

        $excludeInvno = function ($query) {
            $query->where('a.invno', 'not like', '%PJT%')
                ->where('a.invno', 'not like', '%PIB%')
                ->where('a.invno', 'not like', '%PIBK%');
        };

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr, $idItemExpr) => [
            DB::raw("'BC 2.3' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("a.bpbno_int as trans_no"),
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit) as unit"),
            DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)), 2) as nilai_barang"),
            DB::raw("SUM(a.berat_bersih) as berat_bersih"),
            DB::raw("SUM(a.berat_kotor) as berat_kotor"),
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            DB::raw("$idItemExpr as id_item"),
            'a.satuan_bc',
            DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty_bc"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku_Bpb = null;
        $queryBahanBaku_Whs = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku_Bpb = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.3')
                ->where('a.bpbno_int', 'not like', 'FG%')
                ->where($excludeInvno)
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku_Bpb->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku_Bpb->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                'mcnt.nama_contents',
                's.matclass',
                'mcnt.id'
            ))->groupBy('mcnt.id', 'a.bpbno_int');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'bahan baku', 'bahan_baku'])) {
            $queryBahanBaku_Whs = $mysql_sb->table('whs_inmaterial_fabric_det as wd')
                ->leftJoin('whs_inmaterial_fabric as wh', 'wd.no_dok', '=', 'wh.no_dok')
                ->join('masteritem as s', 'wd.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->where('wh.type_bc', 'BC 2.3')
                ->where('wd.no_dok', 'not like', 'FG%')
                ->whereBetween('wd.tgl_dok', [$fromDate, $toDate])
                ->where('s.matclass', 'FABRIC')
                ->select([
                    DB::raw("'BC 2.3' as jenis_dokumen"),
                    DB::raw("GROUP_CONCAT(DISTINCT wh.no_daftar ORDER BY wh.no_daftar SEPARATOR ', ') as bcno"),
                    DB::raw("wh.tgl_dok as bcdate"),
                    DB::raw("wh.no_dok as trans_no"),
                    DB::raw("wh.tgl_dok as bpbdate"),
                    DB::raw("wh.supplier as supplier"),
                    DB::raw("IFNULL(mcnt.kode_contents, mcnt.id) as kode_brg"),
                    DB::raw("mcnt.nama_contents as itemdesc"),
                    DB::raw("wd.unit as unit"),
                    DB::raw("SUM(wd.qty_good) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("wh.no_aju as nomor_aju"),
                    DB::raw("'-' as tujuan"),
                    DB::raw("mcnt.id as id_item"),
                    DB::raw("wd.unit as satuan_bc"),
                    DB::raw("SUM(wd.qty_good) as qty_bc"),
                    DB::raw("s.matclass as matclass"),
                    DB::raw("NULL as id_so_det")
                ])
                ->groupBy('mcnt.id', 'wd.unit');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
                ->join('so', 'sod.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.3')
                ->where('a.bpbno_int', 'like', 'FG%')
                ->where('d.supplier', '!=', 'BARANG JADI STOCK')
                ->where($excludeInvno)
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    'ac.kpno',
                    's.itemname',
                    "'BARANG JADI'",
                    'ac.kpno'
                ))
                ->groupBy('ac.kpno', 'a.bpbno_int');
        }

        $unionQuery = null;
        foreach ([$queryBahanBaku_Bpb, $queryBahanBaku_Whs, $queryBarangJadi] as $q) {
            if (!$q) continue;
            $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
        }

        if (!$unionQuery) return collect([]);

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
                'a.bpbdate as tanggal_bpb',
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
                'a.tujuan',
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc262($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bpbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $unitExpr, $qtyExpr, $nilaiBarangExpr, $matclassExpr, $idItemExpr) => [
            DB::raw("'BC 2.6.2 MASUK' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("$unitExpr as unit"),
            DB::raw("SUM($qtyExpr) as qty"),
            'a.curr',
            DB::raw("SUM($nilaiBarangExpr) as nilai_barang"),
            DB::raw("SUM(a.berat_bersih) as berat_bersih"),
            DB::raw("SUM(a.berat_kotor) as berat_kotor"),
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku_Bpb = null;
        $queryBahanBaku_Whs = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku_Bpb = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.2')
                ->where('a.bpbno_int', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku_Bpb->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku_Bpb->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                "mcnt.nama_contents",
                "IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit)",
                "IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)",
                "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2)",
                "s.matclass",
                "mcnt.id"
            ))->groupBy('mcnt.id', 'a.bpbno_int');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'bahan baku', 'bahan_baku'])) {
            $queryBahanBaku_Whs = $mysql_sb->table('whs_inmaterial_fabric_det as wd')
                ->leftJoin('whs_inmaterial_fabric as wh', 'wd.no_dok', '=', 'wh.no_dok')
                ->join('masteritem as s', 'wd.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->where('wh.type_bc', 'BC 2.6.2')
                ->where('wd.no_dok', 'not like', 'FG%')
                ->whereBetween('wd.tgl_dok', [$fromDate, $toDate])
                ->where('s.matclass', 'FABRIC')
                ->select([
                    DB::raw("'BC 2.6.2 MASUK' as jenis_dokumen"),
                    DB::raw("GROUP_CONCAT(DISTINCT wh.no_daftar ORDER BY wh.no_daftar SEPARATOR ', ') as bcno"),
                    DB::raw("wh.tgl_dok as bcdate"),
                    DB::raw("wh.no_dok as trans_no"),
                    DB::raw("wh.tgl_dok as bpbdate"),
                    DB::raw("wh.supplier as supplier"),
                    DB::raw("IFNULL(mcnt.kode_contents, mcnt.id) as kode_brg"),
                    DB::raw("mcnt.nama_contents as itemdesc"),
                    DB::raw("wd.unit as unit"),
                    DB::raw("SUM(wd.qty_good) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("wh.no_aju as nomor_aju"),
                    DB::raw("'-' as tujuan"),
                    DB::raw("mcnt.id as id_item"),
                    DB::raw("s.matclass as matclass"),
                    DB::raw("NULL as id_so_det")
                ])
                ->groupBy('mcnt.id', 'wd.unit');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
                ->join('so', 'sod.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.2')
                ->where('a.bpbno_int', 'like', 'FG%')
                ->where('d.supplier', '!=', 'BARANG JADI STOCK')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "ac.kpno",
                    "s.itemname",
                    "a.unit",
                    "a.qty",
                    "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty, 2)",
                    "'BARANG JADI'",
                    "ac.kpno"
                ))
                ->groupBy('ac.kpno', 'a.bpbno_int');
        }

        $unionQuery = null;
        foreach ([$queryBahanBaku_Bpb, $queryBahanBaku_Whs, $queryBarangJadi] as $q) {
            if (!$q) continue;
            $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
        }

        if (!$unionQuery) return collect([]);

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
                'a.bpbdate as tanggal_bpb',
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
                'a.tujuan',
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc40($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bpbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr, $idItemExpr) => [
            DB::raw("'BC 4.0' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
            DB::raw("SUM(a.berat_bersih) as berat_bersih"),
            DB::raw("SUM(a.berat_kotor) as berat_kotor"),
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            DB::raw("$idItemExpr as id_item"),
            'a.remark',
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku_Bpb = null;
        $queryBahanBaku_Whs = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku_Bpb = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 4.0')
                ->where('a.bpbno_int', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku_Bpb->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku_Bpb->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                'mcnt.nama_contents',
                's.matclass',
                'mcnt.id'
            ))->groupBy('mcnt.id', 'a.bpbno_int');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'bahan baku', 'bahan_baku'])) {
            $queryBahanBaku_Whs = $mysql_sb->table('whs_inmaterial_fabric_det as wd')
                ->leftJoin('whs_inmaterial_fabric as wh', 'wd.no_dok', '=', 'wh.no_dok')
                ->join('masteritem as s', 'wd.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->where('wh.type_bc', 'BC 4.0')
                ->where('wd.no_dok', 'not like', 'FG%')
                ->whereBetween('wd.tgl_dok', [$fromDate, $toDate])
                ->where('s.matclass', 'FABRIC')
                ->select([
                    DB::raw("'BC 4.0' as jenis_dokumen"),
                    DB::raw("GROUP_CONCAT(DISTINCT wh.no_daftar ORDER BY wh.no_daftar SEPARATOR ', ') as bcno"),
                    DB::raw("wh.tgl_dok as bcdate"),
                    DB::raw("wh.no_dok as trans_no"),
                    DB::raw("wh.tgl_dok as bpbdate"),
                    DB::raw("wh.supplier as supplier"),
                    DB::raw("IFNULL(mcnt.kode_contents, mcnt.id) as kode_brg"),
                    DB::raw("mcnt.nama_contents as itemdesc"),
                    DB::raw("wd.unit as unit"),
                    DB::raw("SUM(wd.qty_good) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("wh.no_aju as nomor_aju"),
                    DB::raw("'-' as tujuan"),
                    DB::raw("mcnt.id as id_item"),
                    DB::raw("'-' as remark"),
                    DB::raw("s.matclass as matclass"),
                    DB::raw("NULL as id_so_det")
                ])
                ->groupBy('mcnt.id', 'wd.unit');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
                ->join('so', 'sod.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 4.0')
                ->where('a.bpbno_int', 'like', 'FG%')
                ->where('d.supplier', '!=', 'BARANG JADI STOCK')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    'ac.kpno',
                    's.itemname',
                    "'BARANG JADI'",
                    'ac.kpno'
                ))
                ->groupBy('ac.kpno', 'a.bpbno_int');
        }

        $unionQuery = null;
        foreach ([$queryBahanBaku_Bpb, $queryBahanBaku_Whs, $queryBarangJadi] as $q) {
            if (!$q) continue;
            $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
        }

        if (!$unionQuery) return collect([]);

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
                'a.bpbdate as tanggal_bpb',
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
                'a.tujuan',
                'a.remark',
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc27($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bpbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr, $idItemExpr) => [
            DB::raw("'BC 2.7' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
            DB::raw("SUM(a.berat_bersih) as berat_bersih"),
            DB::raw("SUM(a.berat_kotor) as berat_kotor"),
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku_Bpb = null;
        $queryBahanBaku_Whs = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku_Bpb = $mysql_sb->table('bpb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.7')
                ->where('a.tujuan', 'not regexp', 'SUBKON')
                ->where('a.bpbno_int', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if (strtolower($kategoriBarang) !== 'all') {
                $searchTerm = '%' . strtolower($kategoriBarang) . '%';
                $queryBahanBaku_Bpb->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku_Bpb->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                'mcnt.nama_contents',
                's.matclass',
                'mcnt.id'
            ))->groupBy('mcnt.id', 'a.bpbno_int');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'bahan baku', 'bahan_baku'])) {
            $queryBahanBaku_Whs = $mysql_sb->table('whs_inmaterial_fabric_det as wd')
                ->leftJoin('whs_inmaterial_fabric as wh', 'wd.no_dok', '=', 'wh.no_dok')
                ->join('masteritem as s', 'wd.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->where('wh.type_bc', 'BC 2.7')
                ->where('wd.no_dok', 'not like', 'FG%')
                ->whereBetween('wd.tgl_dok', [$fromDate, $toDate])
                ->where('s.matclass', 'FABRIC')
                ->select([
                    DB::raw("'BC 2.7' as jenis_dokumen"),
                    DB::raw("GROUP_CONCAT(DISTINCT wh.no_daftar ORDER BY wh.no_daftar SEPARATOR ', ') as bcno"),
                    DB::raw("wh.tgl_dok as bcdate"),
                    DB::raw("wh.no_dok as trans_no"),
                    DB::raw("wh.tgl_dok as bpbdate"),
                    DB::raw("wh.supplier as supplier"),
                    DB::raw("IFNULL(mcnt.kode_contents, mcnt.id) as kode_brg"),
                    DB::raw("mcnt.nama_contents as itemdesc"),
                    DB::raw("wd.unit as unit"),
                    DB::raw("SUM(wd.qty_good) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("0 as berat_bersih"),
                    DB::raw("0 as berat_kotor"),
                    DB::raw("wh.no_aju as nomor_aju"),
                    DB::raw("'-' as tujuan"),
                    DB::raw("mcnt.id as id_item"),
                    DB::raw("s.matclass as matclass"),
                    DB::raw("NULL as id_so_det")
                ])
                ->groupBy('mcnt.id', 'wd.unit');
        }

        if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
                ->join('so', 'sod.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.7')
                ->where('a.tujuan', 'not regexp', 'SUBKON')
                ->where('a.bpbno_int', 'like', 'FG%')
                ->where('d.supplier', '!=', 'BARANG JADI STOCK')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    'ac.kpno',
                    's.itemname',
                    "'BARANG JADI'",
                    'ac.kpno'
                ))
                ->groupBy('ac.kpno', 'a.bpbno_int');
        }

        $unionQuery = null;
        foreach ([$queryBahanBaku_Bpb, $queryBahanBaku_Whs, $queryBarangJadi] as $q) {
            if (!$q) continue;
            $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
        }

        if (!$unionQuery) return collect([]);

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
                'a.bpbdate as tanggal_bpb',
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
                'a.tujuan',
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
        $methodName = 'getData' . ucfirst($cleanKategori);

        $data = $this->$methodName($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang);
        $fileName = 'laporan-pemasukan';

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
            ['LAPORAN PEMASUKAN '.strtoupper($cleanKategori).''],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $fromDate . ' s/d ' . $toDate],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
            'No',
            'ID So Det',
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
                $row->id_so_det ?? '-',
                $row->jenis_dokumen ?? '-',
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

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'K') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
    public function getData(string $fromDate, string $toDate): array
    {
        $dateField = 'a.bcdate';
        $mysql_sb  = DB::connection('mysql_sb');

        $caseJenisDokumen = "
            CASE
                WHEN a.jenis_dok = '2.3' AND a.invno LIKE '%PJT%' THEN 'BC 2.3 IMPOR PJT'
                WHEN a.jenis_dok = '2.3' AND a.invno NOT LIKE '%PJT%' AND a.invno NOT LIKE '%PIB%' AND a.invno NOT LIKE '%PIBK%' THEN 'BC 2.3'
                WHEN a.jenis_dok = '2.6.2' THEN 'BC 2.6.2'
                WHEN a.jenis_dok = '2.7' THEN 'BC 2.7'
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
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
            'a.berat_bersih',
            'a.berat_kotor',
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            'a.id_item',
            DB::raw("$matclassExpr as matclass"),
        ];

        $queryBahanBaku = $mysql_sb->table('bpb as a')
            ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where('a.cancel', 'N')
            ->where('a.jenis_dok', '!=', 'INHOUSE')
            ->where('a.bpbno', 'not like', 'FG%')
            ->whereBetween($dateField, [$fromDate, $toDate])
            ->select($selectData(
                'a.jenis_dok',
                "IF(a.bcdate IS NULL OR a.bcdate = '0000-00-00', a.bpbdate, a.bcdate)",
                "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT(s.mattype, ' ', a.id_item), s.goods_code)",
                "CONCAT_WS(' ', s.itemdesc, s.color, s.size, s.add_info)",
                's.matclass'
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        $queryBarangJadi = $mysql_sb->table('bpb as a')
            ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where('a.cancel', 'N')
            ->where('a.jenis_dok', '!=', 'INHOUSE')
            ->where('a.bpbno', 'like', 'FG%')
            ->whereBetween($dateField, [$fromDate, $toDate])
            ->select($selectData(
                "'N/A'",
                'a.bcdate',
                "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT('FG ', a.id_item), s.goods_code)",
                's.itemname',
                "'BARANG JADI'"
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

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
                'a.jenis_dokumen',
                'a.matclass as kategori_barang',
                'a.bcno as nomor_daftar',
                'a.bcdate as tanggal_daftar',
                'a.trans_no as nomor_bpb',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->get()
            ->toArray();
    }
}
