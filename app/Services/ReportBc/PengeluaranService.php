<?php

namespace App\Services\ReportBc;

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
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $wsExpr = "(SELECT sub_ac.kpno
                    FROM so_det sub_sd
                    LEFT JOIN so sub_so ON sub_sd.id_so = sub_so.id
                    LEFT JOIN act_costing sub_ac ON sub_so.id_cost = sub_ac.id
                    WHERE sub_sd.id = a.id_so_det LIMIT 1)";

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $idContentsExpr, $matclassExpr, $wsValueExpr) => [
            DB::raw("a.jenis_dok as jenis_dokumen"),
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
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass"),
            DB::raw("$wsValueExpr as ws")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
                ->join('so as so', 'sd.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->leftJoin('laravel_nds.master_sb_ws as msw', 'a.id_so_det', '=', 'msw.id_so_det')
                ->whereIn('a.jenis_dok', ['BC 3.0', 'BC 2.6.1', 'BC 2.6.2', 'BC 2.7', 'BC 3.3', 'BC 4.1', 'INHOUSE', 'BC 2.5'])
                ->where(function ($query) {
                    $query->where('a.jenis_dok', '!=', 'BC 2.7')
                        ->orWhereNotIn('a.tujuan', ['DIKEMBALIKAN', 'DISUBKONTRAKKAN']);
                })
                ->whereRaw("IFNULL(d.supplier, '') != 'BARANG JADI STOCK'")
                ->where('a.bppbno_int', 'LIKE', 'FG%')
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectData(
                    "msw.styleno",
                    "msw.product_item",
                    "a.id_item",
                    "'BARANG JADI'",
                    "IFNULL(msw.ws, ac.kpno)"
                ))
                ->groupBy('a.bcno', 'a.bppbno', 'a.id_item', 'a.price', 'a.jenis_dok', 'a.remark', 'a.tujuan');

            $barangJadiDetail = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.jenis_dokumen ORDER BY a.jenis_dokumen SEPARATOR ', ') as jenis_dokumen"),
                    DB::raw("MAX(a.ws) as ws"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.bcno ORDER BY a.bcno SEPARATOR ', ') as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    'a.id_contents as id_item',
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadiDetail);

            $queryFgStokBppb = $mysql_sb->table('laravel_nds.fg_stok_bppb as a')
                ->join('so_det as sd', 'a.id_so_det', '=', 'sd.id')
                ->join('so as so', 'sd.id_so', '=', 'so.id')
                ->join('act_costing as ac', 'so.id_cost', '=', 'ac.id')
                ->leftJoin('laravel_nds.master_sb_ws as m', 'a.id_so_det', '=', 'm.id_so_det')
                ->whereBetween('a.tgl_pengeluaran', [$fromDate, $toDate])
                ->where('a.cancel', 'N')
                ->where('so.cancel_h', 'N')
                ->where('ac.aktif', 'Y')
                ->whereNotIn('a.tujuan', ['EXPEDISI', 'EKSPEDISI', 'MUTASI INTERNAL'])
                ->select([
                    DB::raw("'INHOUSE' as jenis_dokumen"),
                    DB::raw("'-' as bcno"),
                    DB::raw("a.tgl_pengeluaran as bcdate"),
                    DB::raw("a.no_trans_out as trans_no"),
                    DB::raw("a.tgl_pengeluaran as bpbdate"),
                    DB::raw("'PRODUCTION-SEWING' as supplier"),
                    DB::raw("IFNULL(m.styleno, ac.styleno) as kode_brg"),
                    DB::raw("CONCAT(IFNULL(m.styleno, ac.styleno), ' - ', IFNULL(m.color,'-')) as itemdesc"),
                    DB::raw("'PCS' as unit"),
                    DB::raw("SUM(a.qty_out) as qty"),
                    DB::raw("'-' as curr"),
                    DB::raw("0 as nilai_barang"),
                    DB::raw("'-' as nomor_aju"),
                    DB::raw("a.tujuan"),
                    DB::raw("a.id_so_det as id_contents"),
                    DB::raw("'BARANG JADI' as matclass"),
                    DB::raw("IFNULL(m.ws, ac.kpno) as ws"),
                ])
                ->groupBy('ws', 'a.no_trans_out');

            $fgStokBppbDetail = $mysql_sb->table(DB::raw("({$queryFgStokBppb->toSql()}) as a"))
                ->mergeBindings($queryFgStokBppb)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.jenis_dokumen ORDER BY a.jenis_dokumen SEPARATOR ', ') as jenis_dokumen"),
                    DB::raw("MAX(a.ws) as ws"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.bcno ORDER BY a.bcno SEPARATOR ', ') as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bpbdate) as tanggal_bpb"),
                    'a.id_contents as id_item',
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($fgStokBppbDetail);
        }

        if (in_array($kategori, ['all', 'fabric'])) {

            $contentsJoin = "
                LEFT JOIN masterdesc sd ON s.id_gen = sd.id
                LEFT JOIN mastercolor sc ON sd.id_color = sc.id
                LEFT JOIN masterweight sw ON sc.id_weight = sw.id
                LEFT JOIN masterlength sl ON sw.id_length = sl.id
                LEFT JOIN masterwidth swd ON sl.id_width = swd.id
                LEFT JOIN mastercontents mcnt ON swd.id_contents = mcnt.id
            ";

            $sqlFabric = "
                SELECT
                    a.dok_bc AS jenis_dokumen,
                    LPAD(a.no_daftar, 6, '0') AS bcno,
                    a.tgl_daftar AS bcdate,
                    a.no_bppb AS trans_no,
                    a.tgl_bppb AS bppbdate,
                    a.tujuan AS supplier,
                    b.id_item AS id_item,
                    IFNULL(mcnt.id, CONCAT('item_', s.id_item)) AS id_contents,
                    IFNULL(mcnt.nama_contents, s.itemdesc) AS itemdesc,
                    b.satuan AS unit,
                    SUM(b.qty_out) AS qty,
                    b.curr AS curr,
                    ROUND(SUM(b.qty_out * IFNULL(b.price, 0)), 2) AS nilai_barang,
                    ac.kpno AS ws,
                    s.matclass AS matclass
                FROM whs_bppb_h a
                INNER JOIN whs_bppb_det b ON b.no_bppb = a.no_bppb
                INNER JOIN masteritem s ON b.id_item = s.id_item
                {$contentsJoin}
                LEFT JOIN (SELECT id_jo, id_so FROM jo_det GROUP BY id_jo) tmpjod ON tmpjod.id_jo = b.id_jo
                LEFT JOIN so ON tmpjod.id_so = so.id
                LEFT JOIN act_costing ac ON so.id_cost = ac.id
                WHERE LEFT(a.no_bppb, 2) = 'GK'
                AND b.status != 'N'
                AND a.status != 'cancel'
                AND a.tgl_bppb BETWEEN ? AND ?
                GROUP BY b.id_jo, b.id_item, b.satuan, b.no_bppb
            ";

            $fabric = $mysql_sb->query()
                ->fromRaw("({$sqlFabric}) as a", [$fromDate, $toDate])
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("MAX(a.ws) as ws"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.bcno ORDER BY a.bcno SEPARATOR ', ') as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    'a.id_contents as id_item',
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->orderBy('a.trans_no', 'ASC')
                ->get();

            $result = $result->concat($fabric);
        }

        if (in_array($kategori, ['all', 'accesories'])) {

            $queryAcc = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->leftJoin('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->leftJoin('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->leftJoin('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->leftJoin('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->leftJoin('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->whereIn('a.jenis_dok', ['BC 3.0', 'BC 2.6.1', 'BC 2.6.2', 'BC 2.7', 'BC 4.1', 'INHOUSE', 'BC 2.5'])
                ->where(function ($query) {
                    $query->where('a.jenis_dok', '!=', 'BC 2.7')
                        ->orWhereNotIn('a.tujuan', ['DIKEMBALIKAN', 'DISUBKONTRAKKAN']);
                })
                ->where('a.bppbno_int', 'NOT LIKE', 'FG%')
                ->where('a.bppbno_int', 'NOT LIKE', 'OFC%')
                ->where('a.bppbno_int', 'NOT LIKE', 'GK%')
                ->where('a.bppbno_int', 'NOT LIKE', 'WIP%')
                ->where('a.cancel', 'N')
                ->whereBetween('a.bppbdate', [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryAcc->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryAcc->select($selectData(
                "IFNULL(mcnt.kode_contents, IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item)))",
                "IFNULL(mcnt.nama_contents, s.itemdesc)",
                "IFNULL(mcnt.id, CONCAT('item_', s.id_item))",
                "s.matclass",
                $wsExpr
            ))
            ->groupBy('a.bcno', 'a.bppbno', DB::raw('IFNULL(mcnt.id, s.id_item)'), 'a.price', 'a.jenis_dok', 'a.remark', 'a.tujuan');

            $accesories = $mysql_sb->table(DB::raw("({$queryAcc->toSql()}) as a"))
                ->mergeBindings($queryAcc)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("MAX(a.ws) as ws"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.bcno ORDER BY a.bcno SEPARATOR ', ') as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    'a.id_contents as id_item',
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->orderBy('a.trans_no', 'ASC')
                ->get();

            $result = $result->concat($accesories);
        }

        return $result;
    }

    public function getDataBc33($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 3.3');
        };

        $wsExpr = "(SELECT act_costing.kpno
                    FROM so_det
                    LEFT JOIN so ON so_det.id_so = so.id
                    LEFT JOIN act_costing ON so.id_cost = act_costing.id
                    WHERE so_det.id = a.id_so_det)";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr, $idContentsExpr) => [
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
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("a.bppbno_int LIKE 'FG%'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select(array_merge(
                    $selectCommon(
                        "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                        "s.itemname",
                        "'BARANG JADI'",
                        "s.id_item"
                    ),
                    [DB::raw("$wsExpr as ws")]
                ));

            $barangJadiDetail = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    'a.jenis_dokumen',
                    'a.ws',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.kode_brg ORDER BY a.kode_brg SEPARATOR ', ') as kode_brg"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as itemdesc"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw("SUM(a.nilai_cmt) as nilai_cmt"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                    DB::raw('SUM(a.nilai_cmt * COALESCE(mr.rate, 1)) as nilai_cmt_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadiDetail);
        }

        $queryFabric = null;
        $queryGeneral = null;

        if (in_array($kategori, ['all', 'fabric'])) {
            $queryFabric = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where('a.bppbno_int', 'like', 'GK%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IFNULL(mcnt.kode_contents, mcnt.id)",
                    "mcnt.nama_contents",
                    "'FABRIC'",
                    "mcnt.id"
                ));
        }

        if (in_array($kategori, ['all', 'accesories'])) {
            $queryGeneral = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where('a.bppbno_int', 'like', 'GEN%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IFNULL(mcnt.kode_contents, mcnt.id)",
                    "mcnt.nama_contents",
                    "s.matclass",
                    "mcnt.id"
                ));
        }

        $detailQueries = array_filter([$queryFabric, $queryGeneral]);
        if (!empty($detailQueries)) {
            $unionDetail = array_shift($detailQueries);
            foreach ($detailQueries as $q) {
                $unionDetail = $unionDetail->unionAll($q);
            }

            $detailRows = $mysql_sb->table(DB::raw("({$unionDetail->toSql()}) as a"))
                ->mergeBindings($unionDetail)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("NULL as ws"),
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("a.trans_no as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("a.id_contents as id_item"),
                    DB::raw("MAX(a.kode_brg) as kode_brg"),
                    DB::raw("MAX(a.itemdesc) as itemdesc"),
                    DB::raw("MAX(a.itemdesc) as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw("SUM(a.nilai_cmt) as nilai_cmt"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                    DB::raw('SUM(a.nilai_cmt * COALESCE(mr.rate, 1)) as nilai_cmt_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->get();

            $result = $result->concat($detailRows);
        }

        return $result;
    }

    public function getDataBc30($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 3.0');
        };

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $matclassExpr, $idContentsExpr) => [
            DB::raw("'BC 3.0' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbno_int',
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            'a.qty',
            'a.curr',
            DB::raw("ROUND(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price), 2) as nilai_barang"),
            DB::raw("ROUND(a.qty * a.price, 2) as nilai_cmt"),
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass"),
            DB::raw("(SELECT act_costing.kpno
                        FROM so_det
                        LEFT JOIN so ON so_det.id_so = so.id
                        LEFT JOIN act_costing ON so.id_cost = act_costing.id
                        WHERE so_det.id = a.id_so_det) as ws")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("a.bppbno_int LIKE 'FG%'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "'BARANG JADI'",
                    "s.id_item"
                ));

            $barangJadi = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    'a.jenis_dokumen',
                    'a.ws',
                    'a.ws as no_ws',
                    'a.bppbno_int',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("MAX(a.trans_no) as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.kode_brg ORDER BY a.kode_brg SEPARATOR ', ') as kode_brg"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as itemdesc"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw("SUM(a.nilai_cmt) as nilai_cmt"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                    DB::raw('SUM(a.nilai_cmt * COALESCE(mr.rate, 1)) as nilai_cmt_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadi);
        }

        $queryFabric = null;
        $queryGeneral = null;

        if (in_array($kategori, ['all', 'fabric'])) {
            $queryFabric = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where(function ($query) {
                    $query->where('a.bppbno_int', 'like', 'GK%')
                        ->orWhere('a.bppbno_int', 'like', 'OFC%')
                        ->orWhere('a.bppbno_int', 'like', 'GACC%');
                })
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IFNULL(mcnt.kode_contents, mcnt.id)",
                    "mcnt.nama_contents",
                    "'FABRIC'",
                    "mcnt.id"
                ));
        }

        if (in_array($kategori, ['all', 'accesories'])) {
            $queryGeneral = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->leftJoin('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->where('a.bppbno_int', 'like', 'GEN%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IFNULL(mcnt.kode_contents, mcnt.id)",
                    "mcnt.nama_contents",
                    "s.matclass",
                    "mcnt.id"
                ));
        }

        $detailQueries = array_filter([$queryFabric, $queryGeneral]);
        if (!empty($detailQueries)) {
            $unionDetail = array_shift($detailQueries);
            foreach ($detailQueries as $q) {
                $unionDetail = $unionDetail->unionAll($q);
            }

            $detailRows = $mysql_sb->table(DB::raw("({$unionDetail->toSql()}) as a"))
                ->mergeBindings($unionDetail)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("NULL as ws"),
                    DB::raw("NULL as no_ws"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("a.trans_no as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("a.id_contents as id_item"),
                    DB::raw("MAX(a.kode_brg) as kode_brg"),
                    DB::raw("MAX(a.itemdesc) as itemdesc"),
                    DB::raw("MAX(a.itemdesc) as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw("SUM(a.nilai_cmt) as nilai_cmt"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr'),
                    DB::raw('SUM(a.nilai_cmt * COALESCE(mr.rate, 1)) as nilai_cmt_idr')
                )
                ->groupBy('a.id_contents' , 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->get();

            $result = $result->concat($detailRows);
        }

        return $result;
    }

    public function getDataBc261($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.bcno', '!=', '-')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.1');
        };

        $wsExpr = "(SELECT act_costing.kpno
                    FROM so_det
                    LEFT JOIN so ON so_det.id_so = so.id
                    LEFT JOIN act_costing ON so.id_cost = act_costing.id
                    WHERE so_det.id = a.id_so_det)";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idContentsExpr, $matclassExpr) => [
            DB::raw("'BC 2.6.1' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("a.bppbno_int as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit) as unit"),
            DB::raw("IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2) as nilai_barang"),
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("a.bppbno_int LIKE 'FG%'")
                ->whereRaw("SUBSTRING(a.bppbno, 4, 1) != 'P'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select(array_merge(
                    $selectCommon(
                        "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                        "s.itemname",
                        "s.id_item",
                        "'BARANG JADI'"
                    ),
                    [DB::raw("$wsExpr as ws")]
                ));

            $barangJadiDetail = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    'a.jenis_dokumen',
                    'a.ws',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("a.trans_no as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.kode_brg ORDER BY a.kode_brg SEPARATOR ', ') as kode_brg"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as itemdesc"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadiDetail);
        }

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("a.bppbno_int NOT LIKE 'FG%'")
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select(array_merge($selectCommon(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                "mcnt.nama_contents",
                "mcnt.id",
                "s.matclass"
                ),
                [DB::raw("$wsExpr as ws")]
            ));

            $bahanBaku = $mysql_sb->table(DB::raw("({$queryBahanBaku->toSql()}) as a"))
                ->mergeBindings($queryBahanBaku)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("a.ws as ws"),
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("a.trans_no as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("a.id_contents as id_item"),
                    DB::raw("MAX(a.kode_brg) as kode_brg"),
                    DB::raw("MAX(a.itemdesc) as itemdesc"),
                    DB::raw("MAX(a.itemdesc) as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->get();

            $result = $result->concat($bahanBaku);
        }

        return $result;
    }

    public function getDataBc262($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.bcno', '!=', '-')
                ->where('a.cancel', 'N')
                ->where('a.jenis_dok', 'BC 2.6.2');
        };

        $wsExpr = "(SELECT act_costing.kpno
                    FROM so_det
                    LEFT JOIN so ON so_det.id_so = so.id
                    LEFT JOIN act_costing ON so.id_cost = act_costing.id
                    WHERE so_det.id = a.id_so_det)";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idContentsExpr, $matclassExpr) => [
            DB::raw("'BC 2.6.2' as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("a.bppbno_int as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            DB::raw("IFNULL(NULLIF(TRIM(a.satuan_bc), ''), a.unit) as unit"),
            DB::raw("IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * IFNULL(NULLIF(TRIM(a.qty_bc), ''), a.qty), 2) as nilai_barang"),
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("a.bppbno_int LIKE 'FG%'")
                ->whereRaw("SUBSTRING(a.bppbno, 4, 1) != 'P'")
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select(array_merge(
                    $selectCommon(
                        "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                        "s.itemname",
                        "s.id_item",
                        "'BARANG JADI'"
                    ),
                    [DB::raw("$wsExpr as ws")]
                ));

            $barangJadiDetail = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    'a.jenis_dokumen',
                    'a.ws',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("a.trans_no as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.kode_brg ORDER BY a.kode_brg SEPARATOR ', ') as kode_brg"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as itemdesc"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadiDetail);
        }

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereRaw("a.bppbno_int NOT LIKE 'FG%'")
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select(array_merge($selectCommon(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                "mcnt.nama_contents",
                "mcnt.id",
                "s.matclass"
                ),
                [DB::raw("$wsExpr as ws")]
            ));

            $bahanBaku = $mysql_sb->table(DB::raw("({$queryBahanBaku->toSql()}) as a"))
                ->mergeBindings($queryBahanBaku)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("a.ws as ws"),
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("a.trans_no as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("a.id_contents as id_item"),
                    DB::raw("MAX(a.kode_brg) as kode_brg"),
                    DB::raw("MAX(a.itemdesc) as itemdesc"),
                    DB::raw("MAX(a.itemdesc) as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->get();

            $result = $result->concat($bahanBaku);
        }

        return $result;
    }

    public function getDataBc27($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $wsExpr = "(SELECT act_costing.kpno
                    FROM so_det
                    LEFT JOIN so ON so_det.id_so = so.id
                    LEFT JOIN act_costing ON so.id_cost = act_costing.id
                    WHERE so_det.id = a.id_so_det)";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idContentsExpr, $matclassExpr) => [
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
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass"),
            DB::raw("$wsExpr as ws")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.jenis_dok', 'BC 2.7')
                ->whereRaw("a.cancel != 'Y'")
                ->where('a.bppbno_int', 'like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "s.id_item",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bppbno', 's.goods_code', 's.itemname', 'a.price');

            $barangJadiDetail = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    'a.jenis_dokumen',
                    'a.ws',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.kode_brg ORDER BY a.kode_brg SEPARATOR ', ') as kode_brg"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as itemdesc"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadiDetail);
        }

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->join('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->join('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->join('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->join('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->join('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->join('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where('a.jenis_dok', 'BC 2.7')
                ->whereRaw("a.cancel != 'Y'")
                ->where('a.bppbno_int', 'not like', 'FG%')
                ->whereBetween($dateField, [$fromDate, $toDate]);

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectCommon(
                "IFNULL(mcnt.kode_contents, mcnt.id)",
                "mcnt.nama_contents",
                "mcnt.id",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bppbno', 'mcnt.id', 'a.price');

            $bahanBaku = $mysql_sb->table(DB::raw("({$queryBahanBaku->toSql()}) as a"))
                ->mergeBindings($queryBahanBaku)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    'a.ws',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("a.id_contents as id_item"),
                    DB::raw("MAX(a.kode_brg) as kode_brg"),
                    DB::raw("MAX(a.itemdesc) as itemdesc"),
                    DB::raw("MAX(a.itemdesc) as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->get();

            $result = $result->concat($bahanBaku);
        }

        return $result;
    }

    public function getDataBc25($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
        $mysql_sb = DB::connection('mysql_sb');

        $baseFilter = function ($query) {
            $query->where('a.jenis_dok', 'BC 2.5')
                    ->where('a.cancel', 'N');
        };

        $wsExpr = "(SELECT act_costing.kpno
                    FROM so_det
                    LEFT JOIN so ON so_det.id_so = so.id
                    LEFT JOIN act_costing ON so.id_cost = act_costing.id
                    WHERE so_det.id = a.id_so_det)";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idContentsExpr, $matclassExpr) => [
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
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass"),
            DB::raw("$wsExpr as ws")
        ];

        $kategori = strtolower($kategoriBarang);

        if (!in_array($kategori, ['all', 'fabric', 'accesories'])) {
            return collect();
        }

        $queryScrap = $mysql_sb->table('bppb as a')
            ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
            ->leftJoin('masterdesc as sd', 's.id_gen', '=', 'sd.id')
            ->leftJoin('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
            ->leftJoin('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
            ->leftJoin('masterlength as sl', 'sw.id_length', '=', 'sl.id')
            ->leftJoin('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
            ->leftJoin('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where($baseFilter)
            ->whereRaw("a.bppbno_int NOT LIKE 'FG%'")
            ->whereBetween($dateField, [$fromDate, $toDate]);

        if ($kategori !== 'all') {
            $searchTerm = '%' . $kategori . '%';
            $queryScrap->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
        }

        $queryScrap->select($selectCommon(
            "IFNULL(mcnt.kode_contents, IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item)))",
            "IFNULL(mcnt.nama_contents, s.itemdesc)",
            "IFNULL(mcnt.id, CONCAT('item_', s.id_item))",
            "s.matclass"
        ))
        ->groupBy('a.bcno', 'a.bppbno', DB::raw('IFNULL(mcnt.id, s.id_item)'));

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
                DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                DB::raw("MAX(a.matclass) as matclass"),
                DB::raw("MAX(a.matclass) as kategori_barang"),
                'a.ws',
                DB::raw("MAX(a.bcno) as bcno"),
                DB::raw("MAX(a.bcno) as nomor_daftar"),
                DB::raw("MIN(a.bcdate) as bcdate"),
                DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                DB::raw("MAX(a.trans_no) as trans_no"),
                DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                DB::raw("a.id_contents as id_item"),
                DB::raw("MAX(a.kode_brg) as kode_brg"),
                DB::raw("MAX(a.itemdesc) as itemdesc"),
                DB::raw("MAX(a.itemdesc) as uraian_barang"),
                DB::raw("MAX(a.unit) as unit"),
                DB::raw("MAX(a.unit) as jenis_satuan"),
                DB::raw("SUM(a.qty) as qty"),
                DB::raw("SUM(a.qty) as jumlah_satuan"),
                DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                DB::raw('COALESCE(mr.rate, 1) as rate'),
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->groupBy('a.id_contents', 'a.trans_no')
            ->orderBy('a.bcdate', 'ASC')
            ->orderBy('a.bcno', 'ASC')
            ->get();
    }

    public function getDataBc41($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang)
    {
        $dateField = 'a.bppbdate';
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

        $wsExpr = "(SELECT act_costing.kpno
                    FROM so_det
                    LEFT JOIN so ON so_det.id_so = so.id
                    LEFT JOIN act_costing ON so.id_cost = act_costing.id
                    WHERE so_det.id = a.id_so_det)";

        $selectCommon = fn ($kodeBrgExpr, $itemdescExpr, $idContentsExpr, $matclassExpr) => [
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
            DB::raw("$idContentsExpr as id_contents"),
            DB::raw("$matclassExpr as matclass"),
            DB::raw("$wsExpr as ws")
        ];

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        $kategori = strtolower($kategoriBarang);
        $result = collect();

        if (in_array($kategori, ['all', 'fabric', 'accesories'])) {
            $queryBahanBaku = $mysql_sb->table('bppb as a')
                ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
                ->leftJoin('masterdesc as sd', 's.id_gen', '=', 'sd.id')
                ->leftJoin('mastercolor as sc', 'sd.id_color', '=', 'sc.id')
                ->leftJoin('masterweight as sw', 'sc.id_weight', '=', 'sw.id')
                ->leftJoin('masterlength as sl', 'sw.id_length', '=', 'sl.id')
                ->leftJoin('masterwidth as swd', 'sl.id_width', '=', 'swd.id')
                ->leftJoin('mastercontents as mcnt', 'swd.id_contents', '=', 'mcnt.id')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->whereRaw("a.bppbno_int NOT LIKE 'FG%'");

            if ($kategori !== 'all') {
                $searchTerm = '%' . $kategori . '%';
                $queryBahanBaku->whereRaw("LOWER(s.matclass) LIKE ?", [$searchTerm]);
            }

            $queryBahanBaku->select($selectCommon(
                "IFNULL(mcnt.kode_contents, IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item)))",
                "IFNULL(mcnt.nama_contents, s.itemdesc)",
                "IFNULL(mcnt.id, CONCAT('item_', s.id_item))",
                "s.matclass"
            ))
            ->groupBy('a.bcno', 'a.bppbno', DB::raw('IFNULL(mcnt.id, s.id_item)'), 'a.price', 'a.remark', 'a.tujuan');

            $bahanBaku = $mysql_sb->table(DB::raw("({$queryBahanBaku->toSql()}) as a"))
                ->mergeBindings($queryBahanBaku)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    DB::raw("'' as kode_kantor"),
                    DB::raw("MAX(a.jenis_dokumen) as jenis_dokumen"),
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    'a.ws',
                    DB::raw("NULL as no_ws"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("a.id_contents as id_item"),
                    DB::raw("MAX(a.kode_brg) as kode_brg"),
                    DB::raw("MAX(a.itemdesc) as itemdesc"),
                    DB::raw("MAX(a.itemdesc) as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.id_contents', 'a.trans_no')
                ->orderBy('a.bcdate', 'ASC')
                ->orderBy('a.bcno', 'ASC')
                ->get();

            $result = $result->concat($bahanBaku);
        }

        if (in_array($kategori, ['all', 'barang_jadi', 'barang jadi'])) {
            $queryBarangJadi = $mysql_sb->table('bppb as a')
                ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
                ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
                ->where($baseFilter)
                ->whereBetween($dateField, [$fromDate, $toDate])
                ->whereRaw("a.bppbno_int LIKE 'FG%'")
                ->select($selectCommon(
                    "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                    "s.itemname",
                    "s.id_item",
                    "'BARANG JADI'"
                ))
                ->groupBy('a.bcno', 'a.bppbno', 's.goods_code', 's.itemname', 'a.price', 'a.remark', 'a.tujuan');

            $barangJadiDetail = $mysql_sb->table(DB::raw("({$queryBarangJadi->toSql()}) as a"))
                ->mergeBindings($queryBarangJadi)
                ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                    $join->on('mr.tanggal', '=', 'a.bcdate')
                        ->on('mr.curr', '=', 'a.curr');
                })
                ->select(
                    'a.jenis_dokumen',
                    'a.ws',
                    DB::raw("MAX(a.matclass) as matclass"),
                    DB::raw("MAX(a.matclass) as kategori_barang"),
                    DB::raw("MAX(a.bcno) as bcno"),
                    DB::raw("MAX(a.bcno) as nomor_daftar"),
                    DB::raw("MIN(a.bcdate) as bcdate"),
                    DB::raw("MIN(a.bcdate) as tanggal_daftar"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as supplier"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.supplier ORDER BY a.supplier SEPARATOR ', ') as nama_pengirim"),
                    DB::raw("MAX(a.trans_no) as trans_no"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.trans_no ORDER BY a.trans_no SEPARATOR ', ') as nomor_bpb"),
                    DB::raw("MIN(a.bppbdate) as tanggal_bpb"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.kode_brg ORDER BY a.kode_brg SEPARATOR ', ') as kode_brg"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as itemdesc"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.itemdesc ORDER BY a.itemdesc SEPARATOR ', ') as uraian_barang"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw("MAX(a.unit) as jenis_satuan"),
                    DB::raw("SUM(a.qty) as qty"),
                    DB::raw("SUM(a.qty) as jumlah_satuan"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as curr"),
                    DB::raw("GROUP_CONCAT(DISTINCT a.curr) as kode_valuta"),
                    DB::raw("SUM(a.nilai_barang) as nilai_barang"),
                    DB::raw('COALESCE(mr.rate, 1) as rate'),
                    DB::raw('COALESCE(mr.rate, 1) as kurs'),
                    DB::raw('SUM(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
                )
                ->groupBy('a.ws', 'a.trans_no')
                ->orderBy('a.ws', 'ASC')
                ->get();

            $result = $result->concat($barangJadiDetail);
        }

        return $result;
    }

    public function exportExcel($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang, $kategori)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $cleanKategori = preg_replace('/[^a-zA-Z0-9]/', '', $kategori);
        $methodName = 'getData' . ucfirst($cleanKategori);

        $data = $this->$methodName($fromDate, $toDate, $filterBy, $jenis, $kategoriBarang);
        $fileName = 'laporan-pengeluaran';

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
            ['LAPORAN PENGELUARAN '.strtoupper($cleanKategori).''],
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
            'Jenis Dokumen',
            'Kategori Barang',
            'Nomor Daftar',
            'Tanggal Daftar',
            'Nama Penerima',
            'No BPB',
            'Tanggal BPB',
            'WS',
            'ID Content',
            'Uraian Barang',
            'Jenis Satuan',
            'Jumlah Satuan',
            'Kode Valuta',
            'Nilai Barang',
            'Kurs',
            'Nilai Barang IDR',
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
                $row->jenis_dokumen ?? '-',
                $row->kategori_barang ?? '-',
                $row->nomor_daftar ?? '-',
                ($row->tanggal_daftar && $row->tanggal_daftar != '0000-00-00' && $row->tanggal_daftar != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '00-00-0000',
                $row->nama_pengirim ?? '-',
                $row->nomor_bpb ?? '-',
                ($row->tanggal_bpb && $row->tanggal_bpb != '0000-00-00' && $row->tanggal_bpb != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '00-00-0000',
                $row->ws ?? '-',
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

        foreach (range('A', 'J') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

}
