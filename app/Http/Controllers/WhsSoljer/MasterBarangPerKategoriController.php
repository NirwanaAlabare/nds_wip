<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Exports\WhsSoljer\MasterBarangPerKategoriExport;
use App\Exports\WhsSoljer\MasterBarangHistoryPerKategoriExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class MasterBarangPerKategoriController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $kategori = $request->kategori;

            if ($kategori == 'FABRIC') {

                $data = DB::table(DB::raw("
                    (
                        SELECT 
                            penerimaan.barcode,
                            penerimaan.lokasi,
                            penerimaan.buyer,
                            penerimaan.keterangan,
                            penerimaan.jenis_item,
                            penerimaan.warna,
                            penerimaan.lot,
                            penerimaan.no_roll,
                            penerimaan.satuan,
                            penerimaan.qty,
                            ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini
                        FROM penerimaan_gudang_inputan_detail penerimaan
                        LEFT JOIN penerimaan_gudang_inputan h ON h.id = penerimaan.penerimaan_gudang_inputan_id
                        LEFT JOIN (
                            SELECT 
                                barcode,
                                SUM(qty_out) AS total_keluar
                            FROM pengeluaran_gudang_inputan_detail
                            LEFT JOIN pengeluaran_gudang_inputan ON pengeluaran_gudang_inputan.id = pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id
		                    WHERE pengeluaran_gudang_inputan.cancel = '0'
                            GROUP BY barcode
                        ) pengeluaran 
                        ON pengeluaran.barcode = penerimaan.barcode
                        WHERE h.cancel = '0'
                    ) as results
                "));

            } else if ($kategori == 'ACCESORIES') {

                $data = DB::table(DB::raw("
                    (
                        SELECT 
                            penerimaan.barcode,
                            penerimaan.no_box,
                            penerimaan.buyer,
                            penerimaan.worksheet,
                            penerimaan.nama_barang,
                            penerimaan.kode,
                            penerimaan.warna,
                            penerimaan.size,
                            penerimaan.satuan,
                            penerimaan.keterangan,
                            penerimaan.lokasi,
                            ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini,
                            ROUND((penerimaan.qty_kgm - COALESCE(pengeluaran.total_keluar_kgm, 0)), 2) AS qty_kgm_saat_ini
                        FROM penerimaan_gudang_inputan_accesories_detail penerimaan
                        LEFT JOIN penerimaan_gudang_inputan_accesories h ON h.id = penerimaan.penerimaan_gudang_inputan_accesories_id
                        LEFT JOIN (
                            SELECT 
                                barcode,
                                SUM(qty_out) AS total_keluar,
                                SUM(qty_kgm_out) AS total_keluar_kgm
                            FROM pengeluaran_gudang_inputan_accesories_detail
                            LEFT JOIN pengeluaran_gudang_inputan_accesories ON pengeluaran_gudang_inputan_accesories.id = pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id
		                    WHERE pengeluaran_gudang_inputan_accesories.cancel = '0'
                            GROUP BY barcode
                        ) pengeluaran 
                        ON pengeluaran.barcode = penerimaan.barcode
                        WHERE h.cancel = '0'
                    ) as results
                "));

            } else if ($kategori == 'FG') {
                $data = DB::table(DB::raw("
                    (
                        SELECT 
                            penerimaan.barcode,
                            penerimaan.no_koli,
                            penerimaan.buyer,
                            penerimaan.no_ws,
                            penerimaan.style,
                            penerimaan.product_item,
                            penerimaan.warna,
                            penerimaan.size,
                            penerimaan.grade,
                            penerimaan.satuan,
                            penerimaan.keterangan,
                            penerimaan.lokasi,
                            ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini
                        FROM penerimaan_gudang_inputan_fg_detail penerimaan
                        LEFT JOIN penerimaan_gudang_inputan_fg h ON h.id = penerimaan.penerimaan_gudang_inputan_fg_id
                        LEFT JOIN (
                            SELECT 
                                barcode,
                                SUM(qty_out) AS total_keluar
                            FROM pengeluaran_gudang_inputan_fg_detail
                            LEFT JOIN pengeluaran_gudang_inputan_fg ON pengeluaran_gudang_inputan_fg.id = pengeluaran_gudang_inputan_fg_detail.pengeluaran_gudang_inputan_fg_id
		                    WHERE pengeluaran_gudang_inputan_fg.cancel = '0'
                            GROUP BY barcode
                        ) pengeluaran 
                        ON pengeluaran.barcode = penerimaan.barcode
                        WHERE h.cancel = '0'
                    ) as results
                "));

            } else {
                $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
            }

            return DataTables::queryBuilder($data)->make(true);
        }

        return view("whs-soljer.master-barang-per-kategori.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "master-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function historyFabric(Request $request){
        $data = DB::table(DB::raw("
            (
                SELECT 
                    penerimaan.barcode,
                    penerimaan.lokasi,
                    penerimaan.buyer,
                    penerimaan.keterangan,
                    penerimaan.jenis_item,
                    penerimaan.warna,
                    penerimaan.lot,
                    penerimaan.no_roll,
                    penerimaan.satuan,
                    penerimaan.qty,
                    ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini,
                    'FABRIC' AS kategori
                FROM penerimaan_gudang_inputan_detail penerimaan
                LEFT JOIN penerimaan_gudang_inputan h ON h.id = penerimaan.penerimaan_gudang_inputan_id
                LEFT JOIN (
                    SELECT 
                        barcode,
                        SUM(qty_out) AS total_keluar
                    FROM pengeluaran_gudang_inputan_detail
                    LEFT JOIN pengeluaran_gudang_inputan ON pengeluaran_gudang_inputan.id = pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id
                    WHERE pengeluaran_gudang_inputan.cancel = '0'
                    GROUP BY barcode
                ) pengeluaran 
                ON pengeluaran.barcode = penerimaan.barcode
                WHERE penerimaan.barcode = '{$request->barcode}' AND h.cancel = '0'
            ) as results
        "));

        return DataTables::queryBuilder($data)->make(true);
    }

    public function historyDetailFabric(Request $request){
        $data = DB::table(DB::raw("
            (
                SELECT 
                    'PENERIMAAN' AS jenis_tipe,
                    no_bpb,
                    DATE_FORMAT(tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                    barcode,
                    lokasi,
                    buyer,
                    keterangan,
                    jenis_item,
                    warna,
                    lot,
                    no_roll,
                    qty,
                    satuan
                FROM
                    penerimaan_gudang_inputan
                LEFT JOIN penerimaan_gudang_inputan_history ON penerimaan_gudang_inputan_history.penerimaan_gudang_inputan_id = penerimaan_gudang_inputan.id
                WHERE barcode = '{$request->barcode}' AND penerimaan_gudang_inputan.cancel = '0'

                UNION ALL

                SELECT 
                    'PENGELUARAN' AS jenis_tipe,
                    no_bpb,
                    DATE_FORMAT(tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                    pengeluaran_gudang_inputan_history.barcode,
                    lokasi,
                    buyer,
                    keterangan,
                    jenis_item,
                    warna,
                    lot,
                    no_roll,
                    pengeluaran_gudang_inputan_history.qty_out AS qty,
                    satuan
                FROM
                    pengeluaran_gudang_inputan
                LEFT JOIN pengeluaran_gudang_inputan_history ON pengeluaran_gudang_inputan_history.pengeluaran_gudang_inputan_id = pengeluaran_gudang_inputan.id
                LEFT JOIN penerimaan_gudang_inputan_history ON penerimaan_gudang_inputan_history.barcode = pengeluaran_gudang_inputan_history.barcode
                WHERE pengeluaran_gudang_inputan_history.barcode = '{$request->barcode}' AND pengeluaran_gudang_inputan.cancel = '0'
            ) as results
        "));

        return DataTables::queryBuilder($data)->make(true);
    }

    public function historyAccesories(Request $request){
        $data = DB::table(DB::raw("
            (
                SELECT 
                    penerimaan.barcode,
                    penerimaan.no_box,
                    penerimaan.buyer,
                    penerimaan.worksheet,
                    penerimaan.nama_barang,
                    penerimaan.kode,
                    penerimaan.warna,
                    penerimaan.size,
                    penerimaan.satuan,
                    penerimaan.keterangan,
                    penerimaan.lokasi,
                    ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini,
                    ROUND((penerimaan.qty_kgm - COALESCE(pengeluaran.total_keluar_kgm, 0)), 2) AS qty_kgm_saat_ini
                FROM penerimaan_gudang_inputan_accesories_detail penerimaan
                LEFT JOIN penerimaan_gudang_inputan_accesories h ON h.id = penerimaan.penerimaan_gudang_inputan_accesories_id
                LEFT JOIN (
                    SELECT 
                        barcode,
                        SUM(qty_out) AS total_keluar,
                        SUM(qty_kgm_out) AS total_keluar_kgm
                    FROM pengeluaran_gudang_inputan_accesories_detail
                    LEFT JOIN pengeluaran_gudang_inputan_accesories ON pengeluaran_gudang_inputan_accesories.id = pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id
                    WHERE pengeluaran_gudang_inputan_accesories.cancel = '0'
                    GROUP BY barcode
                ) pengeluaran
                ON pengeluaran.barcode = penerimaan.barcode
                WHERE penerimaan.barcode = '{$request->barcode}' AND h.cancel = '0'
            ) as results
        "));

        return DataTables::queryBuilder($data)->make(true);
    }

    public function historyDetailAccesories(Request $request){
        $data = DB::table(DB::raw("
            (
                SELECT 
                    'PENERIMAAN' AS jenis_tipe,
                    no_bpb,
                    DATE_FORMAT(tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                    barcode,
                    no_box,
                    buyer,
                    worksheet,
                    nama_barang,
                    kode,
                    warna,
                    size,
                    qty,
                    satuan,
                    qty_kgm,
                    keterangan,
                    lokasi
                FROM
                    penerimaan_gudang_inputan_accesories
                LEFT JOIN penerimaan_gudang_inputan_accesories_history ON penerimaan_gudang_inputan_accesories_history.penerimaan_gudang_inputan_accesories_id = penerimaan_gudang_inputan_accesories.id
                WHERE barcode = '{$request->barcode}' AND penerimaan_gudang_inputan_accesories.cancel = '0'

                UNION ALL

                SELECT 
                    'PENGELUARAN' AS jenis_tipe,
                    no_bpb,
                    DATE_FORMAT(tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                    pengeluaran_gudang_inputan_accesories_history.barcode,
                    no_box,
                    buyer,
                    worksheet,
                    nama_barang,
                    kode,
                    warna,
                    size,
                    pengeluaran_gudang_inputan_accesories_history.qty_out AS qty,
                    satuan,
                    pengeluaran_gudang_inputan_accesories_history.qty_kgm_out AS qty_kgm,
                    keterangan,
                    lokasi
                FROM
                    pengeluaran_gudang_inputan_accesories
                LEFT JOIN pengeluaran_gudang_inputan_accesories_history ON pengeluaran_gudang_inputan_accesories_history.pengeluaran_gudang_inputan_accesories_id = pengeluaran_gudang_inputan_accesories.id
                LEFT JOIN penerimaan_gudang_inputan_accesories_history ON penerimaan_gudang_inputan_accesories_history.barcode = pengeluaran_gudang_inputan_accesories_history.barcode
                WHERE pengeluaran_gudang_inputan_accesories_history.barcode = '{$request->barcode}' AND pengeluaran_gudang_inputan_accesories.cancel = '0'
            ) as results
        "));

        return DataTables::queryBuilder($data)->make(true);
    }

    public function historyFg(Request $request){
        $data = DB::table(DB::raw("
            (
                 SELECT 
                    penerimaan.barcode,
                    penerimaan.no_koli,
                    penerimaan.buyer,
                    penerimaan.no_ws,
                    penerimaan.style,
                    penerimaan.product_item,
                    penerimaan.warna,
                    penerimaan.size,
                    penerimaan.grade,
                    penerimaan.satuan,
                    penerimaan.keterangan,
                    penerimaan.lokasi,
                    ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) AS qty_saat_ini
                FROM penerimaan_gudang_inputan_fg_detail penerimaan
                LEFT JOIN penerimaan_gudang_inputan_fg h ON h.id = penerimaan.penerimaan_gudang_inputan_fg_id
                LEFT JOIN (
                    SELECT 
                        barcode,
                        SUM(qty_out) AS total_keluar
                    FROM pengeluaran_gudang_inputan_fg_detail
                    LEFT JOIN pengeluaran_gudang_inputan_fg ON pengeluaran_gudang_inputan_fg.id = pengeluaran_gudang_inputan_fg_detail.pengeluaran_gudang_inputan_fg_id
                    WHERE pengeluaran_gudang_inputan_fg.cancel = '0'
                    GROUP BY barcode
                ) pengeluaran 
                ON pengeluaran.barcode = penerimaan.barcode
                WHERE penerimaan.barcode = '{$request->barcode}' AND h.cancel = '0'
            ) as results
        "));

        return DataTables::queryBuilder($data)->make(true);
    }

    public function historyDetailFg(Request $request){
        $data = DB::table(DB::raw("
            (
                SELECT 
                    'PENERIMAAN' AS jenis_tipe,
                    no_bpb,
                    DATE_FORMAT(tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                    barcode,
                    no_koli,
                    buyer,
                    no_ws,
                    style,
                    product_item,
                    warna,
                    size,
                    grade,
                    qty,
                    satuan,
                    keterangan,
                    lokasi
                FROM
                    penerimaan_gudang_inputan_fg
                LEFT JOIN penerimaan_gudang_inputan_fg_history ON penerimaan_gudang_inputan_fg_history.penerimaan_gudang_inputan_fg_id = penerimaan_gudang_inputan_fg.id
                WHERE barcode = '{$request->barcode}' AND penerimaan_gudang_inputan_fg.cancel = '0'

                UNION ALL

                SELECT 
                    'PENGELUARAN' AS jenis_tipe,
                    no_bpb,
                    DATE_FORMAT(tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                    pengeluaran_gudang_inputan_fg_history.barcode,
                    no_koli,
                    buyer,
                    no_ws,
                    style,
                    product_item,
                    warna,
                    size,
                    grade,
                    pengeluaran_gudang_inputan_fg_history.qty_out AS qty,
                    satuan,
                    keterangan,
                    lokasi
                FROM
                    pengeluaran_gudang_inputan_fg
                LEFT JOIN pengeluaran_gudang_inputan_fg_history ON pengeluaran_gudang_inputan_fg_history.pengeluaran_gudang_inputan_fg_id = pengeluaran_gudang_inputan_fg.id
                LEFT JOIN penerimaan_gudang_inputan_fg_history ON penerimaan_gudang_inputan_fg_history.barcode = pengeluaran_gudang_inputan_fg_history.barcode
                WHERE pengeluaran_gudang_inputan_fg_history.barcode = '{$request->barcode}' AND pengeluaran_gudang_inputan_fg.cancel = '0'
            ) as results
        "));

        return DataTables::queryBuilder($data)->make(true);
    }

    public function export(Request $request) {
        $kategori = $request->kategori;

        return Excel::download(new MasterBarangPerKategoriExport($kategori), 'master-barang-per-kategori.xlsx');
    }

    public function exportHistory(Request $request) {
        $kategori = $request->kategori;
        $barcode = $request->barcode;

        return Excel::download(new MasterBarangHistoryPerKategoriExport($kategori, $barcode), 'master-barang-per-kategori.xlsx');
    }
}