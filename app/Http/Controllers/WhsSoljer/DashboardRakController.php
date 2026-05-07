<?php

namespace App\Http\Controllers\WhsSoljer;

use DB;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DashboardRakController extends Controller
{
    public function index(Request $request){

        $data = DB::connection('mysql_sb')
            ->table('whs_master_lokasi')
            ->get();

        // $data = DB::connection('mysql_sb')
        //     ->select("
        //         SELECT 
        //             l.*,
        //             COALESCE(isi.jumlah_barang, 0) AS jumlah_barang
        //         FROM signalbit_erp.whs_master_lokasi l
        //         LEFT JOIN (
        //             SELECT 
        //                 penerimaan.lokasi,
        //                 COUNT(*) AS jumlah_barang
        //             FROM laravel_nds.penerimaan_gudang_inputan_detail penerimaan
        //             LEFT JOIN laravel_nds.penerimaan_gudang_inputan h 
        //                 ON h.id = penerimaan.penerimaan_gudang_inputan_id
        //             LEFT JOIN (
        //                 SELECT 
        //                     d.barcode,
        //                     SUM(d.qty_out) AS total_keluar
        //                 FROM laravel_nds.pengeluaran_gudang_inputan_detail d
        //                 LEFT JOIN laravel_nds.pengeluaran_gudang_inputan ph
        //                     ON ph.id = d.pengeluaran_gudang_inputan_id
        //                 WHERE ph.cancel = '0'
        //                 GROUP BY d.barcode
        //             ) pengeluaran
        //                 ON pengeluaran.barcode = penerimaan.barcode
        //             WHERE h.cancel = '0'
        //             AND ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) > 0
        //             GROUP BY penerimaan.lokasi
        //         ) isi
        //             ON isi.lokasi = l.kode_lok
        //         WHERE l.kapasitas > COALESCE(isi.jumlah_barang, 0)
        //     ");
        
        return view("whs-soljer.rak.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "dashboard-rak",
            'containerFluid' => true,
            'data' => $data
        ]);
    }

    public function detail($id){

        $data = DB::connection('mysql_sb')
            ->table('whs_master_lokasi')
            ->selectRaw('id, kode_lok, kapasitas')
            ->where('kode_lok', $id)
            ->first();

        $jumlah_barang = DB::table(DB::raw("
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
                AND ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) > 0
            ) as results
        "))
        ->where('lokasi', $id)
        ->count();

        // $kapasitas = (float) ($data->kapasitas ?? 0);
        // $jumlah = (float) ($jumlah_barang ?? 0);

        // $ruang_kosong = $kapasitas - $jumlah;

        return view("whs-soljer.rak.detail", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "dashboard-rak",
            'containerFluid' => true,
            'data' => $data,
            'jumlah_barang' => $jumlah_barang,
            'ruang_kosong' => '0'
        ]);
    }

    public function getDataRak(Request $request){
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
                AND ROUND((penerimaan.qty - COALESCE(pengeluaran.total_keluar, 0)), 2) > 0
            ) as results
        "))
        ->where('lokasi', $request->lokasi);

        return DataTables::queryBuilder($data)->make(true);
    }
}