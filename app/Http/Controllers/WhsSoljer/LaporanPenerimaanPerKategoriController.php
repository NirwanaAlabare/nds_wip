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
            if ($request->kategori == 'FABRIC') {
                $data = PenerimaanGudangInputanDetail::selectRaw("
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
                    penerimaan_gudang_inputan_detail.satuan
                ")
                ->leftJoin("penerimaan_gudang_inputan", "penerimaan_gudang_inputan.id", "=", "penerimaan_gudang_inputan_detail.penerimaan_gudang_inputan_id")
                ->where("penerimaan_gudang_inputan.cancel", 0);
            }else if ($request->kategori == 'ACCESORIES') {
                $data = PenerimaanGudangInputanAccesoriesDetail::selectRaw("
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
                    penerimaan_gudang_inputan_accesories_detail.keterangan
                ")
                ->leftJoin("penerimaan_gudang_inputan_accesories", "penerimaan_gudang_inputan_accesories.id", "=", "penerimaan_gudang_inputan_accesories_detail.penerimaan_gudang_inputan_accesories_id")
                ->where("penerimaan_gudang_inputan_accesories.cancel", 0);
            }else if ($request->kategori == 'FG') {
                $data = PenerimaanGudangInputanFgDetail::selectRaw("
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
                    penerimaan_gudang_inputan_fg_detail.keterangan
                ")
                ->leftJoin("penerimaan_gudang_inputan_fg", "penerimaan_gudang_inputan_fg.id", "=", "penerimaan_gudang_inputan_fg_detail.penerimaan_gudang_inputan_fg_id")
                ->where("penerimaan_gudang_inputan_fg.cancel", 0);
            }else{
                $data = PenerimaanGudangInputan::whereRaw('1=0');
            }

            $kategori = $request->kategori;

            return DataTables::eloquent($data)->filter(function ($query) use ($kategori) {
                $tglAwal = request('dateFrom');
                $tglAkhir = request('dateTo');

                if($kategori == 'FABRIC'){
                    if ($tglAwal) {
                        $query->whereRaw("penerimaan_gudang_inputan.tgl_bpb >= '" . $tglAwal . "'");
                    }
                    if ($tglAkhir) {
                        $query->whereRaw("penerimaan_gudang_inputan.tgl_bpb <= '" . $tglAkhir . "'");
                    }
                }else if($kategori == 'ACCESORIES'){
                    if ($tglAwal) {
                        $query->whereRaw("penerimaan_gudang_inputan_accesories.tgl_bpb >= '" . $tglAwal . "'");
                    }
                    if ($tglAkhir) {
                        $query->whereRaw("penerimaan_gudang_inputan_accesories.tgl_bpb <= '" . $tglAkhir . "'");
                    }
                }else if($kategori == 'FG'){
                    if ($tglAwal) {
                        $query->whereRaw("penerimaan_gudang_inputan_fg.tgl_bpb >= '" . $tglAwal . "'");
                    }
                    if ($tglAkhir) {
                        $query->whereRaw("penerimaan_gudang_inputan_fg.tgl_bpb <= '" . $tglAkhir . "'");
                    }
                }else{
                    if ($tglAwal) {
                        $query->whereRaw("penerimaan_gudang_inputan.tgl_bpb >= '" . $tglAwal . "'");
                    }
                    if ($tglAkhir) {
                        $query->whereRaw("penerimaan_gudang_inputan.tgl_bpb <= '" . $tglAkhir . "'");
                    }
                }
            }, true)
            ->filterColumn('tgl_bpb', function($query, $keyword) use ($kategori) {
                if($kategori == 'FABRIC'){
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_gudang_inputan.tgl_bpb, '%d/%m/%Y') LIKE ?
                    ", ["%{$keyword}%"]);
                }else if($kategori == 'ACCESORIES'){
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_gudang_inputan_accesories.tgl_bpb, '%d/%m/%Y') LIKE ?
                    ", ["%{$keyword}%"]);
                }else if($kategori == 'FG'){
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_gudang_inputan_fg.tgl_bpb, '%d/%m/%Y') LIKE ?
                    ", ["%{$keyword}%"]);
                }
            })
            ->filterColumn('no_bpb', function($query, $keyword) use ($kategori) {
                if($kategori == 'FABRIC'){
                    $query->whereRaw("
                        penerimaan_gudang_inputan.no_bpb LIKE ?
                    ", ["%{$keyword}%"]);
                } else if($kategori == 'ACCESORIES'){
                    $query->whereRaw("
                        penerimaan_gudang_inputan_accesories.no_bpb LIKE ?
                    ", ["%{$keyword}%"]);
                } else if($kategori == 'FG'){
                    $query->whereRaw("
                        penerimaan_gudang_inputan_fg.no_bpb LIKE ?
                    ", ["%{$keyword}%"]);
                }
            })
            ->filterColumn('barcode', function($query, $keyword) use ($kategori) {
                if($kategori == 'FABRIC'){
                    $query->whereRaw("
                        penerimaan_gudang_inputan_detail.barcode LIKE ?
                    ", ["%{$keyword}%"]);
                } else if($kategori == 'ACCESORIES'){
                    $query->whereRaw("
                        penerimaan_gudang_inputan_accesories_detail.barcode LIKE ?
                    ", ["%{$keyword}%"]);
                } else if($kategori == 'FG'){
                    $query->whereRaw("
                        penerimaan_gudang_inputan_fg_detail.barcode LIKE ?
                    ", ["%{$keyword}%"]);
                }
            })
            ->order(function ($query) use ($kategori) {
                if($kategori == 'FABRIC'){
                    $query->orderBy('penerimaan_gudang_inputan.created_at', 'desc');
                }else if($kategori == 'ACCESORIES'){
                    $query->orderBy('penerimaan_gudang_inputan_accesories.created_at', 'desc');
                }else if($kategori == 'FG'){
                    $query->orderBy('penerimaan_gudang_inputan_fg.created_at', 'desc');
                }
            })
            ->toJson();
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
