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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LaporanPenerimaanPerKategoriExport implements FromView, ShouldAutoSize, WithColumnFormatting
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
        $tglAwal = $this->from;
        $tglAkhir = $this->to;

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

        if ($this->kategori == 'FABRIC') {

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

            return view("whs-soljer.laporan-penerimaan-per-kategori.export-fabric", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
            
        } else if ($this->kategori == 'ACCESORIES') {

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

            return view("whs-soljer.laporan-penerimaan-per-kategori.export-accesories", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);

        } else if ($this->kategori == 'FG') {

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

            return view("whs-soljer.laporan-penerimaan-per-kategori.export-fg", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        } else {
            $data = [];

            return view("whs-soljer.laporan-penerimaan-per-kategori.export-fabric", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }
    }

    public function columnFormats(): array
    {
        switch ($this->kategori) {
            case 'FABRIC':
                return [
                    'K' => NumberFormat::FORMAT_NUMBER_00,
                ];

            case 'ACCESORIES':
                return [
                    'K' => NumberFormat::FORMAT_NUMBER_00,
                ];

            case 'FG':
                return [
                    'L' => NumberFormat::FORMAT_NUMBER_00,
                ];

            default:
                return [];
        }
    }
}
