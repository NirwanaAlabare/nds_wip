<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Exports\WhsSoljer\LaporanPengeluaranPerKategoriExport;
use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\PenerimaanGudangInputanAccesoriesDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanFgDetail;
use App\Models\WhsSoljer\PengeluaranGudangInputanDetail;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class LaporanPengeluaranPerKategoriController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;

            $whereFabric = "";
            $whereAcc = "";
            $whereFg = "";
            $whereMutasi = "";

            if ($tglAwal && $tglAkhir) {
                $whereFabric = " AND pengeluaran_gudang_inputan.tgl_bpb BETWEEN '$tglAwal' AND '$tglAkhir' ";
                $whereAcc    = " AND pengeluaran_gudang_inputan_accesories.tgl_bpb BETWEEN '$tglAwal' AND '$tglAkhir' ";
                $whereFg     = " AND pengeluaran_gudang_inputan_fg.tgl_bpb BETWEEN '$tglAwal' AND '$tglAkhir' ";
                $whereMutasi = " AND mutasi_rak.tgl_mutasi BETWEEN '$tglAwal' AND '$tglAkhir' ";
            }

            if ($request->kategori == 'FABRIC') {

                $data = DB::select("
                    SELECT 
                        pengeluaran_gudang_inputan_detail.id,
                        pengeluaran_gudang_inputan.no_bpb, 
                        DATE_FORMAT(pengeluaran_gudang_inputan.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                        penerimaan_gudang_inputan_detail.barcode,
                        penerimaan_gudang_inputan_detail.lokasi,
                        penerimaan_gudang_inputan_detail.buyer,
                        penerimaan_gudang_inputan_detail.keterangan,
                        penerimaan_gudang_inputan_detail.jenis_item,
                        penerimaan_gudang_inputan_detail.warna,
                        penerimaan_gudang_inputan_detail.lot,
                        penerimaan_gudang_inputan_detail.no_roll,
                        pengeluaran_gudang_inputan_detail.qty_act AS qty,
                        penerimaan_gudang_inputan_detail.satuan,
                        pengeluaran_gudang_inputan_detail.qty_out,
                        penerimaan_gudang_inputan_detail.created_at
                    FROM penerimaan_gudang_inputan_detail
                    LEFT JOIN pengeluaran_gudang_inputan_detail 
                        ON pengeluaran_gudang_inputan_detail.barcode = penerimaan_gudang_inputan_detail.barcode
                        AND pengeluaran_gudang_inputan_detail.lokasi = penerimaan_gudang_inputan_detail.lokasi
                    LEFT JOIN pengeluaran_gudang_inputan 
                        ON pengeluaran_gudang_inputan.id = pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id
                    WHERE pengeluaran_gudang_inputan.cancel = 0
                    $whereFabric
                    ORDER BY created_at DESC
                ");

            } else if ($request->kategori == 'ACCESORIES') {

                $data = DB::select("
                    SELECT 
                        pengeluaran_gudang_inputan_accesories_detail.id,
                        pengeluaran_gudang_inputan_accesories.no_bpb, 
                        DATE_FORMAT(pengeluaran_gudang_inputan_accesories.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                        penerimaan_gudang_inputan_accesories_detail.barcode,
                        penerimaan_gudang_inputan_accesories_detail.no_box,
                        penerimaan_gudang_inputan_accesories_detail.buyer,
                        penerimaan_gudang_inputan_accesories_detail.worksheet,
                        penerimaan_gudang_inputan_accesories_detail.nama_barang,
                        penerimaan_gudang_inputan_accesories_detail.kode,
                        penerimaan_gudang_inputan_accesories_detail.warna,
                        penerimaan_gudang_inputan_accesories_detail.size,
                        penerimaan_gudang_inputan_accesories_detail.satuan,
                        penerimaan_gudang_inputan_accesories_detail.lokasi,
                        penerimaan_gudang_inputan_accesories_detail.keterangan,
                        pengeluaran_gudang_inputan_accesories_detail.qty_act AS qty,
                        pengeluaran_gudang_inputan_accesories_detail.qty_kgm_act AS qty_kgm,
                        pengeluaran_gudang_inputan_accesories_detail.qty_out,
                        pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out,
                        penerimaan_gudang_inputan_accesories_detail.created_at
                    FROM penerimaan_gudang_inputan_accesories_detail
                    LEFT JOIN pengeluaran_gudang_inputan_accesories_detail 
                        ON pengeluaran_gudang_inputan_accesories_detail.barcode = penerimaan_gudang_inputan_accesories_detail.barcode
                    LEFT JOIN pengeluaran_gudang_inputan_accesories 
                        ON pengeluaran_gudang_inputan_accesories.id = pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id
                    WHERE pengeluaran_gudang_inputan_accesories.cancel = 0
                    $whereAcc
                    ORDER BY created_at DESC
                ");

            } else if ($request->kategori == 'FG') {

                $data = DB::select("
                    SELECT 
                        pengeluaran_gudang_inputan_fg_detail.id,
                        pengeluaran_gudang_inputan_fg.no_bpb, 
                        DATE_FORMAT(pengeluaran_gudang_inputan_fg.tgl_bpb, '%d-%m-%Y') AS tgl_bpb,
                        penerimaan_gudang_inputan_fg_detail.barcode,
                        penerimaan_gudang_inputan_fg_detail.no_koli,
                        penerimaan_gudang_inputan_fg_detail.buyer,
                        penerimaan_gudang_inputan_fg_detail.no_ws,
                        penerimaan_gudang_inputan_fg_detail.style,
                        penerimaan_gudang_inputan_fg_detail.product_item,
                        penerimaan_gudang_inputan_fg_detail.warna,
                        penerimaan_gudang_inputan_fg_detail.size,
                        penerimaan_gudang_inputan_fg_detail.grade,
                        pengeluaran_gudang_inputan_fg_detail.qty_act AS qty,
                        penerimaan_gudang_inputan_fg_detail.satuan,
                        penerimaan_gudang_inputan_fg_detail.lokasi,
                        penerimaan_gudang_inputan_fg_detail.keterangan,
                        pengeluaran_gudang_inputan_fg_detail.qty_out,
                        penerimaan_gudang_inputan_fg_detail.created_at
                    FROM penerimaan_gudang_inputan_fg_detail
                    LEFT JOIN pengeluaran_gudang_inputan_fg_detail 
                        ON pengeluaran_gudang_inputan_fg_detail.penerimaan_gudang_inputan_fg_detail_id = penerimaan_gudang_inputan_fg_detail.id
                    LEFT JOIN pengeluaran_gudang_inputan_fg 
                        ON pengeluaran_gudang_inputan_fg.id = pengeluaran_gudang_inputan_fg_detail.pengeluaran_gudang_inputan_fg_id
                    WHERE pengeluaran_gudang_inputan_fg.cancel = 0
                    $whereFg
                    ORDER BY created_at DESC
                ");

            } else {
                $data = [];
            }

            return DataTables::collection($data)->toJson();
        }

        return view("whs-soljer.laporan-pengeluaran-per-kategori.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "laporan-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $kategori = $request->kategori;

        return Excel::download(new LaporanPengeluaranPerKategoriExport($from, $to, $kategori), 'laporan-pengeluaran-per-kategori.xlsx');
    }
}