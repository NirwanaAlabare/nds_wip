<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Exports\WhsSoljer\LaporanMutasiPerKategoriExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class LaporanMutasiPerKategoriController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $kategori = $request->kategori;
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;

            if ($kategori == 'FABRIC') {

                $data = DB::table(DB::raw("
                    (
                        SELECT
                            x.barcode,
                            x.buyer,
                            x.jenis_item,
                            x.warna,
                            x.satuan,
                            ROUND((COALESCE(sa.masuk_awal, 0) - COALESCE(ka.keluar_awal, 0)), 2) AS saldo_awal,
                            ROUND(COALESCE(m.pemasukan, 0), 2) AS pemasukan,
                            ROUND(COALESCE(k.pengeluaran, 0), 2) AS pengeluaran,
                            ROUND(
                                (COALESCE(sa.masuk_awal, 0) - COALESCE(ka.keluar_awal, 0))
                                + COALESCE(m.pemasukan, 0)
                                - COALESCE(k.pengeluaran, 0)
                            , 2) AS saldo_akhir
                        FROM
                        (
                            SELECT 
                                d.barcode,
                                d.buyer,
                                d.jenis_item,
                                d.warna,
                                d.satuan
                            FROM penerimaan_gudang_inputan_detail d
                            JOIN penerimaan_gudang_inputan h 
                                    ON h.id = d.penerimaan_gudang_inputan_id
                            WHERE h.cancel = 0
                            GROUP BY barcode, buyer, jenis_item, warna, satuan
                        ) x
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty) AS masuk_awal
                            FROM penerimaan_gudang_inputan_detail d
                            JOIN penerimaan_gudang_inputan h
                                ON h.id = d.penerimaan_gudang_inputan_id
                            WHERE h.tgl_bpb < '{$tglAwal}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) sa ON sa.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty_out) AS keluar_awal
                            FROM pengeluaran_gudang_inputan_detail d
                            JOIN pengeluaran_gudang_inputan h
                                ON h.id = d.pengeluaran_gudang_inputan_id
                            WHERE h.tgl_bpb < '{$tglAwal}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) ka ON ka.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty) AS pemasukan
                            FROM penerimaan_gudang_inputan_detail d
                            JOIN penerimaan_gudang_inputan h
                                ON h.id = d.penerimaan_gudang_inputan_id
                            WHERE h.tgl_bpb BETWEEN '{$tglAwal}' AND '{$tglAkhir}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) m ON m.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty_out) AS pengeluaran
                            FROM pengeluaran_gudang_inputan_detail d
                            JOIN pengeluaran_gudang_inputan h
                                ON h.id = d.pengeluaran_gudang_inputan_id
                            WHERE h.tgl_bpb BETWEEN '{$tglAwal}' AND '{$tglAkhir}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) k ON k.barcode = x.barcode
                    ) as results
                "));

            } else if ($kategori == 'ACCESORIES') {

                $data = DB::table(DB::raw("
                    (
                        SELECT
                            x.barcode,
                            x.buyer,
                            x.nama_barang,
                            x.warna,
                            x.satuan,
                            ROUND((COALESCE(sa.masuk_awal, 0) - COALESCE(ka.keluar_awal, 0)), 2) AS saldo_awal,
                            ROUND(COALESCE(m.pemasukan, 0), 2) AS pemasukan,
                            ROUND(COALESCE(k.pengeluaran, 0), 2) AS pengeluaran,
                            ROUND(
                                (COALESCE(sa.masuk_awal, 0) - COALESCE(ka.keluar_awal, 0))
                                + COALESCE(m.pemasukan, 0)
                                - COALESCE(k.pengeluaran, 0)
                            , 2) AS saldo_akhir
                        FROM
                        (
                            SELECT 
                                d.barcode,
                                d.buyer,
                                d.nama_barang,
                                d.warna,
                                d.satuan
                            FROM penerimaan_gudang_inputan_accesories_detail d
                            JOIN penerimaan_gudang_inputan_accesories h 
                                    ON h.id = d.penerimaan_gudang_inputan_accesories_id
                            WHERE h.cancel = 0
                            GROUP BY barcode, buyer, nama_barang, warna, satuan
                        ) x
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty) AS masuk_awal
                            FROM penerimaan_gudang_inputan_accesories_detail d
                            JOIN penerimaan_gudang_inputan_accesories h
                                ON h.id = d.penerimaan_gudang_inputan_accesories_id
                            WHERE h.tgl_bpb < '{$tglAwal}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) sa ON sa.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty_out) AS keluar_awal
                            FROM pengeluaran_gudang_inputan_accesories_detail d
                            JOIN pengeluaran_gudang_inputan_accesories h
                                ON h.id = d.pengeluaran_gudang_inputan_accesories_id
                            WHERE h.tgl_bpb < '{$tglAwal}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) ka ON ka.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty) AS pemasukan
                            FROM penerimaan_gudang_inputan_accesories_detail d
                            JOIN penerimaan_gudang_inputan_accesories h
                                ON h.id = d.penerimaan_gudang_inputan_accesories_id
                            WHERE h.tgl_bpb BETWEEN '{$tglAwal}' AND '{$tglAkhir}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) m ON m.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty_out) AS pengeluaran
                            FROM pengeluaran_gudang_inputan_accesories_detail d
                            JOIN pengeluaran_gudang_inputan_accesories h
                                ON h.id = d.pengeluaran_gudang_inputan_accesories_id
                            WHERE h.tgl_bpb BETWEEN '{$tglAwal}' AND '{$tglAkhir}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) k ON k.barcode = x.barcode
                    ) as results
                "));

            } else if ($kategori == 'FG') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            x.barcode,
                            x.buyer,
                            x.product_item,
                            x.warna,
                            x.satuan,
                            ROUND((COALESCE(sa.masuk_awal, 0) - COALESCE(ka.keluar_awal, 0)), 2) AS saldo_awal,
                            ROUND(COALESCE(m.pemasukan, 0), 2) AS pemasukan,
                            ROUND(COALESCE(k.pengeluaran, 0), 2) AS pengeluaran,
                            ROUND(
                                (COALESCE(sa.masuk_awal, 0) - COALESCE(ka.keluar_awal, 0))
                                + COALESCE(m.pemasukan, 0)
                                - COALESCE(k.pengeluaran, 0)
                            , 2) AS saldo_akhir
                        FROM
                        (
                            SELECT 
                                d.barcode,
                                d.buyer,
                                d.product_item,
                                d.warna,
                                d.satuan
                            FROM penerimaan_gudang_inputan_fg_detail d
                            JOIN penerimaan_gudang_inputan_fg h 
                                    ON h.id = d.penerimaan_gudang_inputan_fg_id
                            WHERE h.cancel = 0
                            GROUP BY barcode, buyer, product_item, warna, satuan
                        ) x
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty) AS masuk_awal
                            FROM penerimaan_gudang_inputan_fg_detail d
                            JOIN penerimaan_gudang_inputan_fg h
                                ON h.id = d.penerimaan_gudang_inputan_fg_id
                            WHERE h.tgl_bpb < '{$tglAwal}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) sa ON sa.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty_out) AS keluar_awal
                            FROM pengeluaran_gudang_inputan_fg_detail d
                            JOIN pengeluaran_gudang_inputan_fg h
                                ON h.id = d.pengeluaran_gudang_inputan_fg_id
                            WHERE h.tgl_bpb < '{$tglAwal}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) ka ON ka.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty) AS pemasukan
                            FROM penerimaan_gudang_inputan_fg_detail d
                            JOIN penerimaan_gudang_inputan_fg h
                                ON h.id = d.penerimaan_gudang_inputan_fg_id
                            WHERE h.tgl_bpb BETWEEN '{$tglAwal}' AND '{$tglAkhir}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) m ON m.barcode = x.barcode
                        LEFT JOIN (
                            SELECT d.barcode, SUM(d.qty_out) AS pengeluaran
                            FROM pengeluaran_gudang_inputan_fg_detail d
                            JOIN pengeluaran_gudang_inputan_fg h
                                ON h.id = d.pengeluaran_gudang_inputan_fg_id
                            WHERE h.tgl_bpb BETWEEN '{$tglAwal}' AND '{$tglAkhir}' AND h.cancel = 0
                            GROUP BY d.barcode
                        ) k ON k.barcode = x.barcode
                    ) as results
                "));

            } else {
                $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
            }

            return DataTables::queryBuilder($data)->make(true);
        }

        return view("whs-soljer.laporan-mutasi-per-kategori.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "laporan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $kategori = $request->kategori;

        return Excel::download(new LaporanMutasiPerKategoriExport($from, $to, $kategori), 'laporan-mutasi-per-kategori.xlsx');
    }
}