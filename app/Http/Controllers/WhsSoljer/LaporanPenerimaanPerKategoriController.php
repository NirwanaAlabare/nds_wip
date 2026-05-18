<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Exports\WhsSoljer\LaporanPenerimaanPerKategoriExport;
use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputan;
use App\Models\WhsSoljer\PenerimaanGudangInputanAccesoriesDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanFgDetail;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class LaporanPenerimaanPerKategoriController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            // if ($request->kategori == 'FABRIC') {
            //     $data = DB::select("
            //         SELECT 
            //             penerimaan_gudang_inputan_detail.id,
            //             penerimaan_gudang_inputan.no_bpb, 
            //             DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            //             penerimaan_gudang_inputan_detail.barcode,
            //             penerimaan_gudang_inputan_detail.lokasi,
            //             penerimaan_gudang_inputan_detail.buyer,
            //             penerimaan_gudang_inputan_detail.keterangan,
            //             penerimaan_gudang_inputan_detail.jenis_item,
            //             penerimaan_gudang_inputan_detail.warna,
            //             penerimaan_gudang_inputan_detail.lot,
            //             penerimaan_gudang_inputan_detail.no_roll,
            //             penerimaan_gudang_inputan_detail.qty,
            //             penerimaan_gudang_inputan_detail.satuan
            //         FROM penerimaan_gudang_inputan_detail
            //         LEFT JOIN penerimaan_gudang_inputan 
            //             ON penerimaan_gudang_inputan.id = penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id
            //         WHERE penerimaan_gudang_inputan.cancel = 0
            //     ");
            // }else if ($request->kategori == 'ACCESORIES') {
            //     $data = PenerimaanGudangInputanAccesoriesDetail::selectRaw("
            //         penerimaan_gudang_inputan_accesories_detail.id,
            //         penerimaan_gudang_inputan_accesories.no_bpb, 
            //         DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            //         penerimaan_gudang_inputan_accesories_detail.barcode,
            //         penerimaan_gudang_inputan_accesories_detail.no_box,
            //         penerimaan_gudang_inputan_accesories_detail.buyer,
            //         penerimaan_gudang_inputan_accesories_detail.worksheet,
            //         penerimaan_gudang_inputan_accesories_detail.nama_barang,
            //         penerimaan_gudang_inputan_accesories_detail.kode,
            //         penerimaan_gudang_inputan_accesories_detail.warna,
            //         penerimaan_gudang_inputan_accesories_detail.size,
            //         penerimaan_gudang_inputan_accesories_detail.qty,
            //         penerimaan_gudang_inputan_accesories_detail.satuan,
            //         penerimaan_gudang_inputan_accesories_detail.qty_kgm,
            //         penerimaan_gudang_inputan_accesories_detail.lokasi,
            //         penerimaan_gudang_inputan_accesories_detail.keterangan
            //     ")
            //     ->leftJoin("penerimaan_gudang_inputan_accesories", "penerimaan_gudang_inputan_accesories.id", "=", "penerimaan_gudang_inputan_accesories_detail.penerimaan_gudang_inputan_accesories_id")
            //     ->where("penerimaan_gudang_inputan_accesories.cancel", 0);
            // }else if ($request->kategori == 'FG') {
            //     $data = PenerimaanGudangInputanFgDetail::selectRaw("
            //         penerimaan_gudang_inputan_fg_detail.id,
            //         penerimaan_gudang_inputan_fg.no_bpb, 
            //         DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
            //         penerimaan_gudang_inputan_fg_detail.barcode,
            //         penerimaan_gudang_inputan_fg_detail.no_koli,
            //         penerimaan_gudang_inputan_fg_detail.buyer,
            //         penerimaan_gudang_inputan_fg_detail.no_ws,
            //         penerimaan_gudang_inputan_fg_detail.style,
            //         penerimaan_gudang_inputan_fg_detail.product_item,
            //         penerimaan_gudang_inputan_fg_detail.warna,
            //         penerimaan_gudang_inputan_fg_detail.size,
            //         penerimaan_gudang_inputan_fg_detail.grade,
            //         penerimaan_gudang_inputan_fg_detail.qty,
            //         penerimaan_gudang_inputan_fg_detail.satuan,
            //         penerimaan_gudang_inputan_fg_detail.lokasi,
            //         penerimaan_gudang_inputan_fg_detail.keterangan
            //     ")
            //     ->leftJoin("penerimaan_gudang_inputan_fg", "penerimaan_gudang_inputan_fg.id", "=", "penerimaan_gudang_inputan_fg_detail.penerimaan_gudang_inputan_fg_id")
            //     ->where("penerimaan_gudang_inputan_fg.cancel", 0);
            // }else{
            //     $data = PenerimaanGudangInputan::whereRaw('1=0');
            // }

            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;

            $whereFabric = "";
            $whereAcc = "";
            $whereFg = "";
            $whereMutasi = "";

            /* =========================
            FILTER TANGGAL
            ========================= */
            if ($tglAwal && $tglAkhir) {
                $whereFabric = " AND penerimaan_gudang_inputan.tgl_bpb BETWEEN '$tglAwal' AND '$tglAkhir' ";
                $whereAcc    = " AND penerimaan_gudang_inputan_accesories.tgl_bpb BETWEEN '$tglAwal' AND '$tglAkhir' ";
                $whereFg     = " AND penerimaan_gudang_inputan_fg.tgl_bpb BETWEEN '$tglAwal' AND '$tglAkhir' ";
                $whereMutasi = " AND mutasi_rak.tgl_mutasi BETWEEN '$tglAwal' AND '$tglAkhir' ";
            }

            if ($request->kategori == 'FABRIC') {
                $data = DB::select("
                    SELECT 
                        penerimaan_gudang_inputan_detail.id,
                        penerimaan_gudang_inputan.no_bpb, 
                        DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                        penerimaan_gudang_inputan_detail.barcode,
                        penerimaan_gudang_inputan_detail.lokasi,
                        penerimaan_gudang_inputan_detail.buyer,
                        penerimaan_gudang_inputan_detail.keterangan,
                        penerimaan_gudang_inputan_detail.jenis_item,
                        penerimaan_gudang_inputan_detail.warna,
                        penerimaan_gudang_inputan_detail.lot,
                        penerimaan_gudang_inputan_detail.no_roll,
                        penerimaan_gudang_inputan_detail.qty,
                        penerimaan_gudang_inputan_detail.satuan,
                        penerimaan_gudang_inputan.created_at
                    FROM penerimaan_gudang_inputan_detail
                    LEFT JOIN penerimaan_gudang_inputan 
                        ON penerimaan_gudang_inputan.id = penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id
                    WHERE penerimaan_gudang_inputan.cancel = 0
                    $whereFabric
                    ORDER BY created_at DESC
                ");

            } else if ($request->kategori == 'ACCESORIES') {

                $data = DB::select("
                    SELECT 
                        penerimaan_gudang_inputan_accesories_detail.id,
                        penerimaan_gudang_inputan_accesories.no_bpb, 
                        DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                        penerimaan_gudang_inputan_accesories_detail.barcode,
                        penerimaan_gudang_inputan_accesories_detail.no_box,
                        penerimaan_gudang_inputan_accesories_detail.buyer,
                        penerimaan_gudang_inputan_accesories_detail.worksheet,
                        penerimaan_gudang_inputan_accesories_detail.nama_barang,
                        penerimaan_gudang_inputan_accesories_detail.kode,
                        penerimaan_gudang_inputan_accesories_detail.warna,
                        penerimaan_gudang_inputan_accesories_detail.size,
                        penerimaan_gudang_inputan_accesories_detail.qty,
                        penerimaan_gudang_inputan_accesories_detail.satuan,
                        penerimaan_gudang_inputan_accesories_detail.qty_kgm,
                        penerimaan_gudang_inputan_accesories_detail.lokasi,
                        penerimaan_gudang_inputan_accesories_detail.keterangan,
                        penerimaan_gudang_inputan_accesories.created_at
                    FROM penerimaan_gudang_inputan_accesories_detail
                    LEFT JOIN penerimaan_gudang_inputan_accesories 
                        ON penerimaan_gudang_inputan_accesories.id = penerimaan_gudang_inputan_accesories_detail.penerimaan_gudang_inputan_accesories_id
                    WHERE penerimaan_gudang_inputan_accesories.cancel = 0
                    $whereAcc
                    ORDER BY created_at DESC
                ");

            } else if ($request->kategori == 'FG') {

                $data = DB::select("
                    SELECT 
                        penerimaan_gudang_inputan_fg_detail.id,
                        penerimaan_gudang_inputan_fg.no_bpb, 
                        DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                        penerimaan_gudang_inputan_fg_detail.barcode,
                        penerimaan_gudang_inputan_fg_detail.no_koli,
                        penerimaan_gudang_inputan_fg_detail.buyer,
                        penerimaan_gudang_inputan_fg_detail.no_ws,
                        penerimaan_gudang_inputan_fg_detail.style,
                        penerimaan_gudang_inputan_fg_detail.product_item,
                        penerimaan_gudang_inputan_fg_detail.warna,
                        penerimaan_gudang_inputan_fg_detail.size,
                        penerimaan_gudang_inputan_fg_detail.grade,
                        penerimaan_gudang_inputan_fg_detail.qty,
                        penerimaan_gudang_inputan_fg_detail.satuan,
                        penerimaan_gudang_inputan_fg_detail.lokasi,
                        penerimaan_gudang_inputan_fg_detail.keterangan,
                        penerimaan_gudang_inputan_fg.created_at
                    FROM penerimaan_gudang_inputan_fg_detail
                    LEFT JOIN penerimaan_gudang_inputan_fg 
                        ON penerimaan_gudang_inputan_fg.id = penerimaan_gudang_inputan_fg_detail.penerimaan_gudang_inputan_fg_id
                    WHERE penerimaan_gudang_inputan_fg.cancel = 0
                    $whereFg
                    ORDER BY created_at DESC
                ");

            } else {

                $data = [];
            }

            return DataTables::collection($data)->toJson();
        }

        return view("whs-soljer.laporan-penerimaan-per-kategori.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "laporan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $kategori = $request->kategori;

        return Excel::download(new LaporanPenerimaanPerKategoriExport($from, $to, $kategori), 'laporan-penerimaan-per-kategori.xlsx');
    }
}
