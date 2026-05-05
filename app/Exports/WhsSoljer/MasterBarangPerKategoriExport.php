<?php

namespace App\Exports\WhsSoljer;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MasterBarangPerKategoriExport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    protected $kategori;

    public function __construct($kategori)
    {
        $this->kategori = $kategori;
    }

    public function view(): View
    {
        if($this->kategori == 'FABRIC'){
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
            "))->get();

            return view("whs-soljer.master-barang-per-kategori.export-fabric", [
                "data" => $data,
            ]);
        }else if($this->kategori == 'ACCESORIES'){
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
            "))->get();

            return view("whs-soljer.master-barang-per-kategori.export-accesories", [
                "data" => $data,
            ]);
        }else if($this->kategori == 'FG'){
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
            "))->get();

            return view("whs-soljer.master-barang-per-kategori.export-fg", [
                "data" => $data,
            ]);
        }
    }

    public function columnFormats(): array
    {
        switch ($this->kategori) {
            case 'FABRIC':
                return [
                    'I' => NumberFormat::FORMAT_NUMBER_00,
                ];

            case 'ACCESORIES':
                return [
                    'I' => NumberFormat::FORMAT_NUMBER_00,
                    'K' => NumberFormat::FORMAT_NUMBER_00,
                ];

            case 'FG':
                return [
                    'J' => NumberFormat::FORMAT_NUMBER_00,
                ];

            default:
                return [];
        }
    }
}
