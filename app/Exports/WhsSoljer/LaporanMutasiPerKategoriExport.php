<?php

namespace App\Exports\WhsSoljer;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LaporanMutasiPerKategoriExport implements FromView, ShouldAutoSize, WithColumnFormatting
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
                        WHERE h.tgl_bpb < '{$this->from}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) sa ON sa.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty_out) AS keluar_awal
                        FROM pengeluaran_gudang_inputan_detail d
                        JOIN pengeluaran_gudang_inputan h
                            ON h.id = d.pengeluaran_gudang_inputan_id
                        WHERE h.tgl_bpb < '{$this->from}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) ka ON ka.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty) AS pemasukan
                        FROM penerimaan_gudang_inputan_detail d
                        JOIN penerimaan_gudang_inputan h
                            ON h.id = d.penerimaan_gudang_inputan_id
                        WHERE h.tgl_bpb BETWEEN '{$this->from}' AND '{$this->to}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) m ON m.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty_out) AS pengeluaran
                        FROM pengeluaran_gudang_inputan_detail d
                        JOIN pengeluaran_gudang_inputan h
                            ON h.id = d.pengeluaran_gudang_inputan_id
                        WHERE h.tgl_bpb BETWEEN '{$this->from}' AND '{$this->to}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) k ON k.barcode = x.barcode
                ) as results
            "))->get();

            return view("whs-soljer.laporan-mutasi-per-kategori.export-fabric", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }else if($this->kategori == 'ACCESORIES'){
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
                        WHERE h.tgl_bpb < '{$this->from}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) sa ON sa.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty_out) AS keluar_awal
                        FROM pengeluaran_gudang_inputan_accesories_detail d
                        JOIN pengeluaran_gudang_inputan_accesories h
                            ON h.id = d.pengeluaran_gudang_inputan_accesories_id
                        WHERE h.tgl_bpb < '{$this->from}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) ka ON ka.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty) AS pemasukan
                        FROM penerimaan_gudang_inputan_accesories_detail d
                        JOIN penerimaan_gudang_inputan_accesories h
                            ON h.id = d.penerimaan_gudang_inputan_accesories_id
                        WHERE h.tgl_bpb BETWEEN '{$this->from}' AND '{$this->to}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) m ON m.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty_out) AS pengeluaran
                        FROM pengeluaran_gudang_inputan_accesories_detail d
                        JOIN pengeluaran_gudang_inputan_accesories h
                            ON h.id = d.pengeluaran_gudang_inputan_accesories_id
                        WHERE h.tgl_bpb BETWEEN '{$this->from}' AND '{$this->to}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) k ON k.barcode = x.barcode
                ) as results
            "))->get();

            return view("whs-soljer.laporan-mutasi-per-kategori.export-accesories", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }else if($this->kategori == 'FG'){
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
                        WHERE h.tgl_bpb < '{$this->from}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) sa ON sa.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty_out) AS keluar_awal
                        FROM pengeluaran_gudang_inputan_fg_detail d
                        JOIN pengeluaran_gudang_inputan_fg h
                            ON h.id = d.pengeluaran_gudang_inputan_fg_id
                        WHERE h.tgl_bpb < '{$this->from}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) ka ON ka.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty) AS pemasukan
                        FROM penerimaan_gudang_inputan_fg_detail d
                        JOIN penerimaan_gudang_inputan_fg h
                            ON h.id = d.penerimaan_gudang_inputan_fg_id
                        WHERE h.tgl_bpb BETWEEN '{$this->from}' AND '{$this->to}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) m ON m.barcode = x.barcode
                    LEFT JOIN (
                        SELECT d.barcode, SUM(d.qty_out) AS pengeluaran
                        FROM pengeluaran_gudang_inputan_fg_detail d
                        JOIN pengeluaran_gudang_inputan_fg h
                            ON h.id = d.pengeluaran_gudang_inputan_fg_id
                        WHERE h.tgl_bpb BETWEEN '{$this->from}' AND '{$this->to}' AND h.cancel = 0
                        GROUP BY d.barcode
                    ) k ON k.barcode = x.barcode
                ) as results
            "))->get();

            return view("whs-soljer.laporan-mutasi-per-kategori.export-fg", [
                "from" => $this->from,
                "to" => $this->to,
                "data" => $data,
            ]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
