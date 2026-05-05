<?php

namespace App\Exports\WhsSoljer;

use App\Models\WhsSoljer\PenerimaanGudangInputanAccesoriesDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanFgDetail;
use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanPengeluaranPerKategoriExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;
    protected $kategori;

    public function __construct($from, $to, $kategori)
    {
        $this->from = $from ? $from : date('Y-m-d');
        $this->to = $to ? $to : date('Y-m-d');
        $this->kategori = $kategori;
    }

    public function view(): View
    {
        if($this->kategori == 'FABRIC'){
            $data = PenerimaanGudangInputanDetail::selectRaw("
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
                pengeluaran_gudang_inputan_detail.qty_out
            ")
            ->leftJoin("pengeluaran_gudang_inputan_detail", "pengeluaran_gudang_inputan_detail.barcode", "=", "penerimaan_gudang_inputan_detail.barcode")
            ->leftJoin("pengeluaran_gudang_inputan", "pengeluaran_gudang_inputan.id", "=", "pengeluaran_gudang_inputan_detail.pengeluaran_gudang_inputan_id")
            ->where("pengeluaran_gudang_inputan.cancel", 0)
            ->whereBetween('pengeluaran_gudang_inputan.created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59'
            ])
            ->get();

            return view("whs-soljer.laporan-pengeluaran-per-kategori.export-fabric", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }else if($this->kategori == 'ACCESORIES'){
            $data = PenerimaanGudangInputanAccesoriesDetail::selectRaw("
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
                pengeluaran_gudang_inputan_accesories_detail.qty_kgm_out
            ")
            ->leftJoin("pengeluaran_gudang_inputan_accesories_detail", "pengeluaran_gudang_inputan_accesories_detail.barcode", "=", "penerimaan_gudang_inputan_accesories_detail.barcode")
            ->leftJoin("pengeluaran_gudang_inputan_accesories", "pengeluaran_gudang_inputan_accesories.id", "=", "pengeluaran_gudang_inputan_accesories_detail.pengeluaran_gudang_inputan_accesories_id")
            ->where("pengeluaran_gudang_inputan_accesories.cancel", 0)
            ->whereBetween('pengeluaran_gudang_inputan_accesories.created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59'
            ])
            ->get();

            return view("whs-soljer.laporan-pengeluaran-per-kategori.export-accesories", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }else if($this->kategori == 'FG'){
           $data = PenerimaanGudangInputanFgDetail::selectRaw("
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
                pengeluaran_gudang_inputan_fg_detail.qty_out
            ")
            ->leftJoin("pengeluaran_gudang_inputan_fg_detail", "pengeluaran_gudang_inputan_fg_detail.barcode", "=", "penerimaan_gudang_inputan_fg_detail.barcode")
            ->leftJoin("pengeluaran_gudang_inputan_fg", "pengeluaran_gudang_inputan_fg.id", "=", "pengeluaran_gudang_inputan_fg_detail.pengeluaran_gudang_inputan_fg_id")
            ->where("pengeluaran_gudang_inputan_fg.cancel", 0)
            ->whereBetween('pengeluaran_gudang_inputan_fg.created_at', [
                $this->from . ' 00:00:00',
                $this->to . ' 23:59:59'
            ])
            ->get();

            return view("whs-soljer.laporan-pengeluaran-per-kategori.export-fg", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }
    }
}
