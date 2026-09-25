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


    // public function getDataRekap($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    // {
    //     $dateField = 'a.bpbdate';

    //     $mysql_sb = DB::connection('mysql_sb');
        
    //     $kategori = strtolower(trim($kategoriBarang));

    //     $selectData = fn ($jenisDokElse, $bcdateExpr, $kodeBrgExpr, $itemdescExpr, $matclassExpr, $idItemExpr) => [
    //         DB::raw("a.jenis_dok as jenis_dokumen"),
    //         DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
    //         DB::raw("$bcdateExpr as bcdate"),
    //         DB::raw("a.bpbno_int as trans_no"),
    //         'a.bpbdate',
    //         'd.supplier',
    //         DB::raw("$kodeBrgExpr as kode_brg"),
    //         DB::raw("$itemdescExpr as itemdesc"),
    //         'a.unit',
    //         DB::raw("SUM(IF(a.qty = 0, IFNULL(a.qty_temp, 0), a.qty)) as qty"),
    //         'a.curr',
    //         DB::raw("ROUND(IFNULL(a.price_bc, a.price) * a.qty, 2) as nilai_barang"),
    //         'a.berat_bersih',
    //         'a.berat_kotor',
    //         DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
    //         'a.tujuan',
    //         DB::raw("$idItemExpr as id_item"),
    //         DB::raw("$matclassExpr as matclass"),
    //         'a.id_so_det'
    //     ];

    //     $queryBahanBaku = null;
    //     $queryBarangJadi = null;
    //     $queryFgStokBpb = null;
    //     $queryFgStokBpbScan = null; 

    //     if (in_array($kategori, ['all', 'fabric', 'accesories', 'accessories', 'sample', 'bahan baku', 'bahan_baku'])) {
    //         $queryBahanBaku = $mysql_sb->table('bpb as a')
    //             ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //             ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
    //             ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
    //             ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
    //             ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
    //             ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
    //             ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
    //             ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.bpbno_int', 'not like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate])
    //             ->whereRaw("NOT (IFNULL(a.jenis_dok, '') = 'INHOUSE' AND s.matclass = 'SAMPLE')"); // Exclude Inhouse Sample

    //         if ($kategori === 'fabric') {
    //             $queryBahanBaku->where('s.matclass', 'FABRIC');
    //         } elseif (in_array($kategori, ['accesories', 'accessories'])) {
    //             $queryBahanBaku->whereIn('s.matclass', ['ACCESORIES PACKING', 'ACCESORIES SEWING']);
    //         } elseif (in_array($kategori, ['bahan baku', 'bahan_baku'])) {
    //             $queryBahanBaku->whereNotIn('s.matclass', ['BARANG JADI', 'SAMPLE']);
    //         } elseif ($kategori === 'sample') {
    //             $queryBahanBaku->where('s.matclass', 'SAMPLE');
    //         }

    //         $queryBahanBaku->select($selectData(
    //             "a.jenis_dok",
    //             "IF(a.bcdate IS NULL OR a.bcdate = '0000-00-00', a.bpbdate, a.bcdate)",
    //             "IFNULL(mcnt.kode_contents, mcnt.id)",
    //             "mcnt.nama_contents",
    //             "s.matclass",
    //             "mcnt.id"
    //         ))
    //         ->groupBy('mcnt.id', 'a.unit');
    //     }

    //     // ===== 2. QUERY BARANG JADI =====
    //     if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {

    //         $queryBarangJadi = $mysql_sb->table('bpb as a')
    //             ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
    //             ->join('so', 'sod.id_so', '=', 'so.id')
    //             ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
    //             ->leftJoin('laravel_nds.master_sb_ws as msw', 'a.id_so_det', '=', 'msw.id_so_det')
    //             ->where('a.cancel', 'N')
    //             ->where('a.bpbno_int', 'like', 'FG%')
    //             ->whereRaw("IFNULL(d.supplier, '') != 'BARANG JADI STOCK'")
    //             ->whereBetween($dateField, [$fromDate, $toDate])
    //             ->select($selectData(
    //                 "a.jenis_dok as jenis_dokumen",
    //                 "a.bcdate",
    //                 "IFNULL(msw.styleno, ac.styleno)",
    //                 "msw.color",
    //                 "'BARANG JADI'",
    //                 "IFNULL(msw.ws, ac.kpno)"
    //             ))
    //             ->groupBy('ac.kpno', 'a.bpbno_int', 'a.id_so_det'); 

    //         $queryFgStokBpb = $mysql_sb->table('laravel_nds.fg_stok_bpb as a')
    //             ->leftJoin('laravel_nds.master_sb_ws as m', 'a.id_so_det', '=', 'm.id_so_det')
    //             ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
    //             ->join('so', 'sd.id_so', '=', 'so.id')
    //             ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
    //             ->where('a.cancel', 'N')
    //             ->where('so.cancel_h', 'N')
    //             ->where('ac.aktif', 'Y')
    //             ->whereBetween('a.tgl_terima', [$fromDate, $toDate])
    //             ->whereNotIn('a.sumber_pemasukan', ['EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL'])
    //             ->select([
    //                 DB::raw("'INHOUSE' as jenis_dokumen"),
    //                 DB::raw("'-' as bcno"),
    //                 DB::raw("a.tgl_terima as bcdate"),
    //                 DB::raw("a.no_trans as trans_no"),
    //                 DB::raw("a.tgl_terima as bpbdate"),
    //                 DB::raw("'PRODUCTION-SEWING' as supplier"),
    //                 DB::raw("IFNULL(m.styleno, ac.styleno) as kode_brg"),
    //                 DB::raw("CONCAT(IFNULL(m.styleno, ac.styleno), ' - ', IFNULL(m.color,'-')) as itemdesc"),
    //                 DB::raw("'PCS' as unit"),
    //                 DB::raw("SUM(a.qty) as qty"),
    //                 DB::raw("'-' as curr"),
    //                 DB::raw("0 as nilai_barang"),
    //                 DB::raw("0 as berat_bersih"),
    //                 DB::raw("0 as berat_kotor"),
    //                 DB::raw("'-' as nomor_aju"),
    //                 DB::raw("a.sumber_pemasukan as tujuan"),
    //                 DB::raw("IFNULL(m.ws, ac.kpno) as id_item"),
    //                 DB::raw("'BARANG JADI' as matclass"),
    //                 'a.id_so_det',
    //             ])
    //             ->groupBy('ac.kpno', 'a.no_trans', 'a.id_so_det');
                
    //         $queryFgStokBpbScan = $mysql_sb->table('laravel_nds.fg_stok_bpb_scan as a')
    //             ->leftJoin('laravel_nds.master_sb_ws as m', 'a.id_so_det', '=', 'm.id_so_det')
    //             ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
    //             ->join('so', 'sd.id_so', '=', 'so.id')
    //             ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
    //             ->where('a.cancel', 'N')
    //             ->where('so.cancel_h', 'N')
    //             ->where('ac.aktif', 'Y')
    //             ->whereBetween('a.tgl_terima', [$fromDate, $toDate])
    //             ->whereNotIn('a.sumber_pemasukan', ['EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL'])
    //             ->select([
    //                 DB::raw("'INHOUSE' as jenis_dokumen"),
    //                 DB::raw("'-' as bcno"),
    //                 DB::raw("a.tgl_terima as bcdate"),
    //                 DB::raw("a.no_trans as trans_no"),
    //                 DB::raw("a.tgl_terima as bpbdate"),
    //                 DB::raw("'PRODUCTION-SEWING' as supplier"),
    //                 DB::raw("IFNULL(m.styleno, ac.styleno) as kode_brg"),
    //                 DB::raw("CONCAT(IFNULL(m.styleno, ac.styleno), ' - ', IFNULL(m.color,'-')) as itemdesc"),
    //                 DB::raw("'PCS' as unit"),
    //                 DB::raw("SUM(a.qty) as qty"),
    //                 DB::raw("'-' as curr"),
    //                 DB::raw("0 as nilai_barang"),
    //                 DB::raw("0 as berat_bersih"),
    //                 DB::raw("0 as berat_kotor"),
    //                 DB::raw("'-' as nomor_aju"),
    //                 DB::raw("a.sumber_pemasukan as tujuan"),
    //                 DB::raw("IFNULL(m.ws, ac.kpno) as id_item"),
    //                 DB::raw("'BARANG JADI' as matclass"),
    //                 'a.id_so_det',
    //             ])
    //             ->groupBy('ac.kpno', 'a.no_trans', 'a.id_so_det');
    //     }

    //     $unionQuery = null;
    //     foreach ([$queryBahanBaku, $queryBarangJadi, $queryFgStokBpb, $queryFgStokBpbScan] as $q) {
    //         if (!$q) continue;
    //         $unionQuery = $unionQuery ? $unionQuery->unionAll($q) : $q;
    //     }

    //     if (!$unionQuery) {
    //         return collect([]);
    //     }

    //     $rateSubQuery = $mysql_sb->table('masterrate')
    //         ->select('tanggal', 'curr', 'rate')
    //         ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
    //         ->groupBy('tanggal', 'curr');

    //     return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
    //         ->mergeBindings($unionQuery)
    //         ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
    //             $join->on('mr.tanggal', '=', 'a.bcdate')
    //                 ->on('mr.curr', '=', 'a.curr');
    //         })
    //         ->select(
    //             DB::raw("'' as kode_kantor"),
    //             'a.jenis_dokumen',
    //             'a.matclass as kategori_barang',
    //             'a.nomor_aju',
    //             'a.bcno as nomor_daftar',
    //             'a.bcdate as tanggal_daftar',
    //             'a.supplier as nama_pengirim',
    //             'a.trans_no as nomor_bpb',
    //             'a.bpbdate as tanggal_bpb',
    //             'a.id_item as id_item',
    //             'a.itemdesc as uraian_barang',
    //             'a.unit as jenis_satuan',
    //             'a.qty as jumlah_satuan',
    //             'a.curr as kode_valuta',
    //             'a.nilai_barang',
    //             DB::raw('COALESCE(mr.rate, 1) as kurs'),
    //             DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
    //             'a.berat_bersih',
    //             'a.berat_kotor',
    //             'a.tujuan',
    //             'a.id_so_det'
    //         )
    //         ->orderBy('a.bcdate', 'ASC')
    //         ->orderBy('a.bcno', 'ASC')
    //         ->orderBy('a.trans_no', 'ASC')
    //         ->get();
    // }

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
                    DB::raw("'-' as bcno"),
                    DB::raw("wh.tgl_dok as bcdate"),
                    DB::raw("wh.no_dok as trans_no"),
                    DB::raw("wh.tgl_dok as bpbdate"),
                    DB::raw("'-' as supplier"),
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

        // ===== 3. QUERY BARANG JADI =====
        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {

            $queryBarangJadi = $mysql_sb->table('bpb as a')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sod', 'a.id_so_det', '=', 'sod.id')
                ->join('so', 'sod.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->leftJoin('laravel_nds.master_sb_ws as msw', 'a.id_so_det', '=', 'msw.id_so_det')
                ->where('a.cancel', 'N')
                ->where('a.bpbno_int', 'like', 'FG%')
                ->whereRaw("IFNULL(d.supplier, '') != 'BARANG JADI STOCK'")
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
                ->leftJoin('laravel_nds.master_sb_ws as m', 'a.id_so_det', '=', 'm.id_so_det')
                ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
                ->join('so', 'sd.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
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
                ->leftJoin('laravel_nds.master_sb_ws as m', 'a.id_so_det', '=', 'm.id_so_det')
                ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
                ->join('so', 'sd.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
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

    // public function getDataBc23($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    // {
    //     $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

    //     $mysql_sb = DB::connection('mysql_sb');

    //     $excludeInvno = function ($query) {
    //         $query->where('a.invno', 'not like', '%PJT%')
    //             ->where('a.invno', 'not like', '%PIB%')
    //             ->where('a.invno', 'not like', '%PIBK%');
    //     };

    //     $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
    //         DB::raw("'BC 2.3' as jenis_dokumen"),
    //         DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
    //         'a.bcdate',
    //         DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
    //         'a.bpbdate',
    //         'd.supplier',
    //         DB::raw("$kodeBrgExpr as kode_brg"),
    //         DB::raw("$itemdescExpr as itemdesc"),
    //         DB::raw("IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit) as unit"),
    //         DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty"),
    //         DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
    //         DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)), 2) as nilai_barang"),
    //         'a.id_item',
    //         'a.satuan_bc',
    //         DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty_bc"),
    //         DB::raw("$matclassExpr as matclass"),
    //     ];

    //     $queryBahanBaku = null;
    //     $queryBarangJadi = null;

    //     if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
    //         $queryBahanBaku = $mysql_sb->table('bpb as a')
    //             ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 2.3')
    //             ->where('a.bpbno_int', 'not like', 'FG%')
    //             ->where($excludeInvno)
    //             ->whereBetween($dateField, [$fromDate, $toDate]);

    //         if (strtolower($kategoriBarang) !== 'all') {
    //             $searchTerm = '%' . strtolower($kategoriBarang) . '%';
    //             $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
    //         }

    //         $queryBahanBaku->select($selectData(
    //             "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, ' ', s.id_item))",
    //             's.itemdesc',
    //             's.matclass'
    //         ))
    //         ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

    //         // $sql = vsprintf(str_replace('?', "'%s'", $queryBahanBaku->toSql()), $queryBahanBaku->getBindings());
    //         // dd($sql);
    //     }

    //     if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
    //         $queryBarangJadi = $mysql_sb->table('bpb as a')
    //             ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 2.3')
    //             ->where('a.bpbno_int', 'like', 'FG%')
    //             ->where($excludeInvno)
    //             ->whereBetween($dateField, [$fromDate, $toDate])
    //             ->select($selectData(
    //                 "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
    //                 's.itemname',
    //                 "'BARANG JADI'"
    //             ))
    //             ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
    //     }

    //     if ($queryBahanBaku && $queryBarangJadi) {
    //         $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
    //     } elseif ($queryBahanBaku) {
    //         $unionQuery = $queryBahanBaku;
    //     } else {
    //         $unionQuery = $queryBarangJadi;
    //     }

    //     $rateSubQuery = $mysql_sb->table('masterrate')
    //         ->select('tanggal', 'curr', 'rate')
    //         ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
    //         ->groupBy('tanggal', 'curr');

    //     return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
    //         ->mergeBindings($unionQuery)
    //         ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
    //             $join->on('mr.tanggal', '=', 'a.bcdate')
    //                  ->on('mr.curr', '=', 'a.curr');
    //         })
    //         ->select(
    //             DB::raw("'' as kode_kantor"),
    //             'a.jenis_dokumen',
    //             'a.matclass as kategori_barang',
    //             'a.bcno as nomor_daftar',
    //             'a.bcdate as tanggal_daftar',
    //             'a.supplier as nama_pengirim',
    //             'a.trans_no as nomor_bpb',
    //             'a.bpbdate as tanggal_bpb',
    //             'a.id_item as id_item',
    //             'a.itemdesc as uraian_barang',
    //             'a.unit as jenis_satuan',
    //             'a.qty as jumlah_satuan',
    //             'a.curr as kode_valuta',
    //             'a.nilai_barang',
    //             DB::raw('COALESCE(mr.rate, 1) as kurs'),
    //             DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
    //         )
    //         ->orderBy('a.bcdate', 'ASC')
    //         ->orderBy('a.bcno', 'ASC')
    //         ->get();
    // }

    public function getDataBc23($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        // $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';
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
            DB::raw("$idItemExpr as id_item"),
            'a.satuan_bc',
            DB::raw("SUM(IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)) as qty_bc"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
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
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                'mcnt.nama_contents',
                's.matclass',
                'mcnt.id'
            ))
            ->groupBy('mcnt.id', 'a.bpbno_int');

            // $sql = vsprintf(str_replace('?', "'%s'", $queryBahanBaku->toSql()), $queryBahanBaku->getBindings());
            // dd($sql);
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
                'a.bpbdate as tanggal_bpb',
                'a.id_item as id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    // public function getDataBc262($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    // {
    //     $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

    //     $mysql_sb = DB::connection('mysql_sb');

    //     $selectData = fn ($kodeBrgExpr, $itemdescExpr, $unitExpr, $qtyExpr, $nilaiBarangExpr, $matclassExpr) => [
    //         DB::raw("'BC 2.6.2 MASUK' as jenis_dokumen"),
    //         DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
    //         'a.bcdate',
    //         DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
    //         'a.bpbdate',
    //         'd.supplier',
    //         DB::raw("$kodeBrgExpr as kode_brg"),
    //         DB::raw("$itemdescExpr as itemdesc"),
    //         DB::raw("$unitExpr as unit"),
    //         DB::raw("$qtyExpr as qty"),
    //         'a.curr',
    //         DB::raw("$nilaiBarangExpr as nilai_barang"),
    //         'a.id_item',
    //         DB::raw("$matclassExpr as matclass"),
    //     ];

    //     $queryBahanBaku = null;
    //     $queryBarangJadi = null;

    //     if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
    //         $queryBahanBaku = $mysql_sb->table('bpb as a')
    //             ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 2.6.2')
    //             ->where('a.bpbno_int', 'not like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate]);

    //         if (strtolower($kategoriBarang) !== 'all') {
    //             $searchTerm = '%' . strtolower($kategoriBarang) . '%';
    //             $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
    //         }

    //         $queryBahanBaku->select($selectData(
    //             "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
    //             "s.itemdesc",
    //             "IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit)",
    //             "IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)",
    //             "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2)",
    //             "s.matclass"
    //         ))
    //         ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
    //     }

    //     if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
    //         $queryBarangJadi = $mysql_sb->table('bpb as a')
    //             ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 2.6.2')
    //             ->where('a.bpbno_int', 'like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate])
    //             ->select($selectData(
    //                 "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
    //                 "s.itemname",
    //                 "a.unit",
    //                 "a.qty",
    //                 "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty, 2)",
    //                 "'BARANG JADI'"
    //             ))
    //             ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
    //     }

    //     if ($queryBahanBaku && $queryBarangJadi) {
    //         $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
    //     } elseif ($queryBahanBaku) {
    //         $unionQuery = $queryBahanBaku;
    //     } else {
    //         $unionQuery = $queryBarangJadi;
    //     }

    //     $rateSubQuery = $mysql_sb->table('masterrate')
    //         ->select('tanggal', 'curr', 'rate')
    //         ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
    //         ->groupBy('tanggal', 'curr');


    //     return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
    //         ->mergeBindings($unionQuery)
    //         ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
    //             $join->on('mr.tanggal', '=', 'a.bcdate')
    //                  ->on('mr.curr', '=', 'a.curr');
    //         })
    //         ->select(
    //             DB::raw("'' as kode_kantor"),
    //             'a.jenis_dokumen',
    //             'a.matclass as kategori_barang',
    //             'a.bcno as nomor_daftar',
    //             'a.bcdate as tanggal_daftar',
    //             'a.supplier as nama_pengirim',
    //             'a.trans_no as nomor_bpb',
    //             'a.bpbdate as tanggal_bpb',
    //             'a.id_item as id_item',
    //             'a.itemdesc as uraian_barang',
    //             'a.unit as jenis_satuan',
    //             'a.qty as jumlah_satuan',
    //             'a.curr as kode_valuta',
    //             'a.nilai_barang',
    //             DB::raw('COALESCE(mr.rate, 1) as kurs'),
    //             DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
    //         )
    //         ->orderBy('a.bcdate', 'ASC')
    //         ->orderBy('a.bcno', 'ASC')
    //         ->get();
    // }

    public function getDataBc262($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        // $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';
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
            DB::raw("$qtyExpr as qty"),
            'a.curr',
            DB::raw("$nilaiBarangExpr as nilai_barang"),
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
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
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                "mcnt.nama_contents",
                "IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit)",
                "IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty)",
                "ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2)",
                "s.matclass",
                "mcnt.id"
            ))
            ->groupBy('mcnt.id', 'a.bpbno_int');
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
                'a.bpbdate as tanggal_bpb',
                'a.id_item as id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    // public function getDataBc40($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    // {
    //     $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

    //     $mysql_sb = DB::connection('mysql_sb');

    //     $excludeCommon = function ($query) {
    //         $query->where('a.cancel', 'N')
    //               ->where('a.jenis_dok', 'BC 4.0')
    //               ->whereRaw("UPPER(a.invno) NOT LIKE '%SEWA%'")
    //               ->where('a.tujuan', 'not like', '%SUBKON%');
    //     };

    //     $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
    //         DB::raw("'BC 4.0' as jenis_dokumen"),
    //         DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
    //         'a.bcdate',
    //         DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
    //         'a.bpbdate',
    //         'd.supplier',
    //         DB::raw("$kodeBrgExpr as kode_brg"),
    //         DB::raw("$itemdescExpr as itemdesc"),
    //         'a.unit',
    //         DB::raw("SUM(a.qty) as qty"),
    //         DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
    //         DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
    //         'a.id_item',
    //         'a.remark',
    //         DB::raw("$matclassExpr as matclass"),
    //     ];

    //     // $queryBahanBaku = $mysql_sb->table('bpb as a')
    //     //     ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //     //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //     //     ->where('a.bpbno_int', 'not like', 'FG%')
    //     //     ->where($excludeCommon)
    //     //     ->whereBetween($dateField, [$fromDate, $toDate])
    //     //     ->select($selectData(
    //     //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
    //     //         "s.itemdesc",
    //     //         "s.matclass"
    //     //     ))
    //     //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

    //     // $queryBarangJadi = $mysql_sb->table('bpb as a')
    //     //     ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
    //     //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //     //     ->where('a.bpbno_int', 'like', 'FG%')
    //     //     ->where('d.area', '=', 'L')
    //     //     ->where($excludeCommon)
    //     //     ->whereBetween($dateField, [$fromDate, $toDate])
    //     //     ->select($selectData(
    //     //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
    //     //         "s.itemname",
    //     //         "'BARANG JADI'"
    //     //     ))
    //     //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

    //     // $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

    //     $queryBahanBaku = null;
    //     $queryBarangJadi = null;

    //     if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
    //         $queryBahanBaku = $mysql_sb->table('bpb as a')
    //             ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 4.0')
    //             ->where('a.bpbno_int', 'not like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate]);

    //         if (strtolower($kategoriBarang) !== 'all') {
    //             $searchTerm = '%' . strtolower($kategoriBarang) . '%';
    //             $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
    //         }

    //         $queryBahanBaku->select($selectData(
    //             "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, ' ', s.id_item))",
    //             's.itemdesc',
    //             's.matclass'
    //         ))
    //         ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
    //     }

    //     if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
    //         $queryBarangJadi = $mysql_sb->table('bpb as a')
    //             ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 4.0')
    //             ->where('a.bpbno_int', 'like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate])
    //             ->select($selectData(
    //                 "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
    //                 's.itemname',
    //                 "'BARANG JADI'"
    //             ))
    //             ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
    //     }

    //     if ($queryBahanBaku && $queryBarangJadi) {
    //         $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
    //     } elseif ($queryBahanBaku) {
    //         $unionQuery = $queryBahanBaku;
    //     } else {
    //         $unionQuery = $queryBarangJadi;
    //     }

    //     $rateSubQuery = $mysql_sb->table('masterrate')
    //         ->select('tanggal', 'curr', 'rate')
    //         ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
    //         ->groupBy('tanggal', 'curr');

    //     return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
    //         ->mergeBindings($unionQuery)
    //         ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
    //             $join->on('mr.tanggal', '=', 'a.bcdate')
    //                  ->on('mr.curr', '=', 'a.curr');
    //         })
    //         ->select(
    //             DB::raw("'' as kode_kantor"),
    //             'a.jenis_dokumen',
    //             'a.matclass as kategori_barang',
    //             'a.bcno as nomor_daftar',
    //             'a.bcdate as tanggal_daftar',
    //             'a.supplier as nama_pengirim',
    //             'a.trans_no as nomor_bpb',
    //             'a.bpbdate as tanggal_bpb',
    //             'a.id_item as id_item',
    //             'a.itemdesc as uraian_barang',
    //             'a.unit as jenis_satuan',
    //             'a.qty as jumlah_satuan',
    //             'a.curr as kode_valuta',
    //             'a.nilai_barang',
    //             DB::raw('COALESCE(mr.rate, 1) as kurs'),
    //             DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
    //             'a.remark'
    //         )
    //         ->orderBy('a.bcdate', 'ASC')
    //         ->orderBy('a.bcno', 'ASC')
    //         ->get();
    // }
    public function getDataBc40($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        // $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';
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
            DB::raw("$idItemExpr as id_item"),
            'a.remark',
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
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
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                'mcnt.nama_contents',
                's.matclass',
                'mcnt.id'
            ))
            ->groupBy('mcnt.id', 'a.bpbno_int');
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
                'a.bpbdate as tanggal_bpb',
                'a.id_item as id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                'a.remark',
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }


    // public function getDataBc27($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    // {
    //     $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';

    //     $mysql_sb = DB::connection('mysql_sb');

    //     $selectData = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
    //         DB::raw("'BC 2.7' as jenis_dokumen"),
    //         DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
    //         'a.bcdate',
    //         DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
    //         'a.bpbdate',
    //         'd.supplier',
    //         DB::raw("$kodeBrgExpr as kode_brg"),
    //         DB::raw("$itemdescExpr as itemdesc"),
    //         'a.unit',
    //         DB::raw("SUM(a.qty) as qty"),
    //         DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
    //         DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
    //         'a.id_item',
    //         DB::raw("$matclassExpr as matclass"),
    //     ];

    //     // $queryBahanBaku = $mysql_sb->table('bpb as a')
    //     //     ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //     //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //     //     ->where('a.cancel', 'N')
    //     //     ->where('a.jenis_dok', 'BC 2.7')
    //     //     ->where('a.bpbno_int', 'not like', 'FG%')
    //     //     ->where('a.tujuan', 'not regexp', 'SUBKON')
    //     //     ->whereBetween($dateField, [$fromDate, $toDate])
    //     //     ->select($selectData(
    //     //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
    //     //         "s.itemdesc",
    //     //         "s.matclass"
    //     //     ))
    //     //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');


    //     // $queryBarangJadi = $mysql_sb->table('bpb as a')
    //     //     ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
    //     //     ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //     //     ->where('a.cancel', 'N')
    //     //     ->where('a.jenis_dok', 'BC 2.7')
    //     //     ->where('a.bpbno_int', 'like', 'FG%')
    //     //     ->where('a.tujuan', 'not regexp', 'SUBKON')
    //     //     ->whereBetween($dateField, [$fromDate, $toDate])
    //     //     ->select($selectData(
    //     //         "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
    //     //         "s.itemname",
    //     //         "'BARANG JADI'"
    //     //     ))
    //     //     ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

    //     // $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

    //     $queryBahanBaku = null;
    //     $queryBarangJadi = null;

    //     if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
    //         $queryBahanBaku = $mysql_sb->table('bpb as a')
    //             ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 2.7')
    //             ->where('a.tujuan', 'not regexp', 'SUBKON')
    //             ->where('a.bpbno_int', 'not like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate]);

    //         if (strtolower($kategoriBarang) !== 'all') {
    //             $searchTerm = '%' . strtolower($kategoriBarang) . '%';
    //             $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
    //         }

    //         $queryBahanBaku->select($selectData(
    //             "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT(s.mattype, ' ', s.id_item))",
    //             's.itemdesc',
    //             's.matclass'
    //         ))
    //         ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

    //     }

    //     if (in_array(strtolower($kategoriBarang), ['all', 'barang_jadi', 'barang jadi'])) {
    //         $queryBarangJadi = $mysql_sb->table('bpb as a')
    //             ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
    //             ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
    //             ->where('a.cancel', 'N')
    //             ->where('a.jenis_dok', 'BC 2.7')
    //              ->where('a.tujuan', 'not regexp', 'SUBKON')
    //             ->where('a.bpbno_int', 'like', 'FG%')
    //             ->whereBetween($dateField, [$fromDate, $toDate])
    //             ->select($selectData(
    //                 "IF(s.goods_code <> '' AND s.goods_code <> '-' AND s.goods_code <> '0', s.goods_code, CONCAT('FG ', s.id_item))",
    //                 's.itemname',
    //                 "'BARANG JADI'"
    //             ))
    //             ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');
    //     }

    //     if ($queryBahanBaku && $queryBarangJadi) {
    //         $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);
    //     } elseif ($queryBahanBaku) {
    //         $unionQuery = $queryBahanBaku;
    //     } else {
    //         $unionQuery = $queryBarangJadi;
    //     }

    //     $rateSubQuery = $mysql_sb->table('masterrate')
    //         ->select('tanggal', 'curr', 'rate')
    //         ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
    //         ->groupBy('tanggal', 'curr');

    //     return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
    //         ->mergeBindings($unionQuery)
    //         ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
    //             $join->on('mr.tanggal', '=', 'a.bcdate')
    //                  ->on('mr.curr', '=', 'a.curr');
    //         })
    //         ->select(
    //             DB::raw("'' as kode_kantor"),
    //             'a.jenis_dokumen',
    //             'a.matclass as kategori_barang',
    //             'a.bcno as nomor_daftar',
    //             'a.bcdate as tanggal_daftar',
    //             'a.supplier as nama_pengirim',
    //             'a.trans_no as nomor_bpb',
    //             'a.bpbdate as tanggal_bpb',
    //             'a.id_item as id_item',
    //             'a.itemdesc as uraian_barang',
    //             'a.unit as jenis_satuan',
    //             'a.qty as jumlah_satuan',
    //             'a.curr as kode_valuta',
    //             'a.nilai_barang',
    //             DB::raw('COALESCE(mr.rate, 1) as kurs'),
    //             DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
    //         )
    //         ->orderBy('a.bcdate', 'ASC')
    //         ->orderBy('a.bcno', 'ASC')
    //         ->get();
    // }

    public function getDataBc27($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        // $dateField = ($filterBy == 'transaksi') ? 'a.bpbdate' : 'a.bcdate';
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
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass"),
            'a.id_so_det'
        ];

        $queryBahanBaku = null;
        $queryBarangJadi = null;

        if (in_array(strtolower($kategoriBarang), ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bpb as a')
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
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectData(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                'mcnt.nama_contents',
                's.matclass',
                'mcnt.id'
            ))
            ->groupBy('mcnt.id', 'a.bpbno_int');
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
                'a.bpbdate as tanggal_bpb',
                'a.id_item as id_item',
                'a.itemdesc as uraian_barang',
                'a.unit as jenis_satuan',
                'a.qty as jumlah_satuan',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                'a.id_so_det'
            )
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    // public function exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori){

    //     ini_set('memory_limit', '1024M');
    //     ini_set('max_execution_time', '3600');

    //     $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
    //     $methodName = 'getData' . ucfirst($cleanKategori);

    //     $data = $this->$methodName($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang);

    //     $excel = FastExcel::create('Laporan');
    //     $sheet = $excel->getSheet();

    //     $sheet->writeTo('A1', 'PT NIRWANA ALABARE GARMENT', [
    //         'font' => ['size' => 14, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A1:Q1');

    //     $judulLaporan = "LAPORAN " . strtoupper($jenis) . " - " . strtoupper(str_replace('-', ' ', $kategori));
    //     $sheet->writeTo('A2', $judulLaporan, [
    //         'font' => ['size' => 12, 'style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A2:Q2');

    //     $periode = "PERIODE: " . Carbon::parse($fromDate)->format('d/m/Y') . " S/D " . Carbon::parse($toDate)->format('d/m/Y');
    //     $sheet->writeTo('A3', $periode, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A3:Q3');

    //     $filterText = "FILTER BERDASARKAN : " . strtoupper($kategoriBarang) . " | TANGGAL " . strtoupper(str_replace('-', ' ', $filterBy));
    //     $sheet->writeTo('A4', $filterText, [
    //         'font' => ['style' => 'bold'],
    //         'text-align' => 'center'
    //     ]);
    //     $sheet->mergeCells('A4:Q4');


    //     $headerKolom = [
    //         'No',
    //         'Kode Kantor',
    //         'Jenis Dokumen',
    //         'Kategori Barang',
    //         'Nomor Daftar',
    //         'Tanggal Daftar',
    //         'Nama ' . ($jenis == 'pemasukan' ? 'Pengirim' : 'Penerima'),
    //         'Nomor BPB',
    //         'Tanggal BPB',
    //         'ID Item',
    //         'Uraian Barang',
    //         'Jenis Satuan',
    //         'Jumlah Satuan',
    //         'Kode Valuta',
    //         'Nilai Barang',
    //         'Kurs',
    //         'Nilai Barang IDR'
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
    //         $sheet->writeAreas();

    //         foreach ($rows as $row) {
    //             $rowArr = [
    //                 $no++,
    //                 $row->kode_kantor ?? '-',
    //                 $row->jenis_dokumen ?? $jenisDokumenFixed,
    //                 $row->kategori_barang ?? '-',
    //                 $row->nomor_daftar ?? '-',
    //                 ($row->tanggal_daftar && $row->tanggal_daftar != '0000-00-00' && $row->tanggal_daftar != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '00-00-0000',
    //                 $row->nama_pengirim ?? '-',
    //                 $row->nomor_bpb ?? '-',
    //                 ($row->tanggal_bpb && $row->tanggal_bpb != '0000-00-00' && $row->tanggal_bpb != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '00-00-0000',
    //                 $row->id_item ?? '-',
    //                 $row->uraian_barang ?? '-',
    //                 $row->jenis_satuan ?? '-',
    //                 (float) ($row->jumlah_satuan ?? 0),
    //                 $row->kode_valuta ?? '-',
    //                 (float) ($row->nilai_barang ?? 0),
    //                 (float) ($row->kurs ?? 0),
    //                 (float) ($row->nilai_barang_idr ?? 0),
    //             ];

    //             $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    //         }
    //     });

    //     $filename = "Laporan_" . ucfirst($jenis) . "_" . Carbon::now()->format('Ymd_His') . ".xlsx";
    //     return $excel->download($filename);
    // }

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
            'Kode Kantor',
            'ID So Det',
            'Jenis Dokumen',
            'Kategori Barang',
            'Nomor Daftar',
            'Tanggal Daftar',
            'Nama ' . ($jenis == 'pemasukan' ? 'Pengirim' : 'Penerima'),
            'Nomor BPB',
            'Tanggal BPB',
            'No WS',
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
                $row->kode_kantor ?? '-',
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

        foreach (range('A', 'L') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}
