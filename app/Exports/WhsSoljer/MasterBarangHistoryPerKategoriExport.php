<?php

namespace App\Exports\WhsSoljer;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MasterBarangHistoryPerKategoriExport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    protected $kategori;
    protected $barcode;

    public function __construct($kategori, $barcode)
    {
        $this->kategori = $kategori;
        $this->barcode = $barcode;
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
                    WHERE penerimaan.barcode = '{$this->barcode}' AND h.cancel = '0'
                ) as results
            "))->get();

            $dataDetail = DB::table(DB::raw("
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
                    WHERE barcode = '{$this->barcode}' AND penerimaan_gudang_inputan.cancel = '0'

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
                    WHERE pengeluaran_gudang_inputan_history.barcode = '{$this->barcode}' AND pengeluaran_gudang_inputan.cancel = '0'
                ) as results
            "))->get();

            return view("whs-soljer.master-barang-per-kategori.export-history-fabric", [
                "data" => $data,
                "dataDetail" => $dataDetail,
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
                    WHERE penerimaan.barcode = '{$this->barcode}' AND h.cancel = '0'
                ) as results
            "))->get();

            $dataDetail = DB::table(DB::raw("
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
                    WHERE barcode = '{$this->barcode}' AND penerimaan_gudang_inputan_accesories.cancel = '0'

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
                    WHERE pengeluaran_gudang_inputan_accesories_history.barcode = '{$this->barcode}' AND pengeluaran_gudang_inputan_accesories.cancel = '0'
                ) as results
            "))->get();

            return view("whs-soljer.master-barang-per-kategori.export-history-accesories", [
                "data" => $data,
                "dataDetail" => $dataDetail,
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
                    WHERE penerimaan.barcode = '{$this->barcode}' AND h.cancel = '0'
                ) as results
            "))->get();

            $dataDetail = DB::table(DB::raw("
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
                    WHERE barcode = '{$this->barcode}' AND penerimaan_gudang_inputan_fg.cancel = '0'

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
                    WHERE pengeluaran_gudang_inputan_fg_history.barcode = '{$this->barcode}' AND pengeluaran_gudang_inputan_fg.cancel = '0'
                ) as results
            "))->get();

            return view("whs-soljer.master-barang-per-kategori.export-history-fg", [
                "data" => $data,
                "dataDetail" => $dataDetail,
            ]);
        }
    }

    public function columnFormats(): array
    {
        switch ($this->kategori) {
            case 'FABRIC':
                return [
                    'I' => NumberFormat::FORMAT_NUMBER_00,
                    'L' => NumberFormat::FORMAT_NUMBER_00,
                ];

            case 'ACCESORIES':
                return [
                    'I' => NumberFormat::FORMAT_NUMBER_00,
                    'K' => NumberFormat::FORMAT_NUMBER_00,
                    'L' => NumberFormat::FORMAT_NUMBER_00,
                    'N' => NumberFormat::FORMAT_NUMBER_00,
                ];

            case 'FG':
                return [
                    'J' => NumberFormat::FORMAT_NUMBER_00,
                    'M' => NumberFormat::FORMAT_NUMBER_00,
                ];

            default:
                return [];
        }
    }
}
