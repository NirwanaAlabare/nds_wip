<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

// class ExportLaporanPemakaian implements FromCollection
// {
//     /**
//      * @return \Illuminate\Support\Collection
//      */
//     public function collection()
//     {
//         return Marker::all();
//     }
// }

class ExportLaporanRekonsiliasi implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    protected $from, $to, $jenis_dok, $status;

    public function __construct($from, $to, $jenis_dok, $status)
    {

        $this->from = $from;
        $this->to = $to;
        $this->jenis_dok = $jenis_dok;
        $this->status = $status;
        $this->rowCount = 0;
    }


    public function view(): View

    {

        $additionalQuery = "";

        if ($this->jenis_dok != 'ALL') {
            $additionalQuery .= " and kode_dokumen = '" . $this->jenis_dok . "' ";
        }

        if ($this->status != 'ALL') {
            $additionalQuery .= " and status = '" . $this->status . "' ";
        }

        $data = DB::connection('mysql_sb')->select("select a.*, COALESCE(b.keterangan,'-') keterangan_update from (select *, CASE
        -- 1. Semua sesuai
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SESUAI'

        -- 2. TOTAL CEISA kosong tapi data lain sesuai
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = 0 AND ROUND(COALESCE(total_sb_idr, 0), 2) > 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'TOTAL CEISA KOSONG'

        -- 3. Total tidak sesuai (lebih dari ±1000)
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND (diff_total > 1000 OR diff_total < -1000)
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'TOTAL TIDAK SESUAI'

        -- 4. Total selisih pembulatan (kurang dari ±1000)
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ABS(diff_total) < 1000
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SELISIH PEMBULATAN'

        -- 5. Satuan tidak sesuai tapi total & qty sama
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN TIDAK SESUAI'

        -- 6. Satuan tidak sesuai + TOTAL CEISA kosong
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ROUND(total_idr, 2) = 0 AND ROUND(COALESCE(total_sb_idr, 0), 2) > 0
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN TIDAK SESUAI, TOTAL CEISA KOSONG'

        -- 7. Satuan tidak sesuai + total selisih pembulatan
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ABS(diff_total) < 1000 AND diff_total != 0
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN TIDAK SESUAI, SELISIH PEMBULATAN'

        -- 8. Satuan dan total tidak sesuai (selisih besar)
        WHEN ROUND(qty, 2) = ROUND(COALESCE(qty_sb, 0), 2)
        AND ABS(diff_total) > 1000 AND diff_total != 0
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SATUAN DAN TOTAL TIDAK SESUAI'

        -- 9. QTY selisih kecil + total sama + satuan sama
        WHEN ABS(diff_qty) < 1 AND diff_qty != 0
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SELISIH PEMBULATAN'

        -- 10. QTY selisih kecil + total sama + satuan sama
        WHEN ABS(diff_qty) < 1 AND diff_qty != 0
        AND ABS(diff_total) < 1000
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'SELISIH PEMBULATAN'

        -- 11. QTY tidak sesuai + total sama
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY TIDAK SESUAI'

        -- 11. QTY tidak sesuai + total sama
        WHEN ABS(diff_qty) >= 1
        AND ABS(diff_total) < 1000
        AND diff_total != 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY TIDAK SESUAI'

        -- 12. QTY tidak sesuai + TOTAL CEISA kosong
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) = 0 AND ROUND(COALESCE(total_sb_idr, 0), 2) > 0
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY TIDAK SESUAI, TOTAL CEISA KOSONG'

        -- 13. QTY tidak sesuai + total tidak sesuai
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) != ROUND(COALESCE(total_sb_idr, 0), 2)
        AND (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY DAN TOTAL TIDAK SESUAI'

        -- 14. QTY dan satuan tidak sesuai, total sama
        WHEN ABS(diff_qty) >= 1
        AND ROUND(total_idr, 2) = ROUND(COALESCE(total_sb_idr, 0), 2)
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        THEN 'QTY DAN SATUAN TIDAK SESUAI'

        -- 15. QTY, satuan, dan total tidak sesuai
        WHEN ROUND(qty, 2) != ROUND(COALESCE(qty_sb, 0), 2)
        AND NOT (satuan_sb = satuan_ciesa OR satuan_ciesa REGEXP REPLACE(satuan_sb, ',', '|'))
        AND ROUND(total_idr, 2) != ROUND(COALESCE(total_sb_idr, 0), 2)
        THEN 'QTY, SATUAN DAN TOTAL TIDAK SESUAI'
        END AS status_kesesuaian from  (
            select kode_dokumen, no_aju, tgl_aju, a.no_daftar, a.tgl_daftar, ROUND(qty,2) qty, ROUND(total,2) total, ROUND(total_idr,2) total_idr, ROUND(COALESCE(qty_sb,0),2) qty_sb, ROUND(COALESCE(total_sb,0),2) total_sb, ROUND(COALESCE(total_sb_idr,0),2) total_sb_idr, ROUND(ROUND(qty,2) - ROUND(COALESCE(qty_sb,0),2),2) diff_qty, ROUND(ROUND(total_idr,2) - ROUND(COALESCE(total_sb_idr,0),2),2) diff_total, if(no_bpb is null,'-',no_bpb) no_bpb, IF(jenis_dok is null,'Not Updated','Updated') status, satuan_sb, satuan_sb_total, satuan_ciesa, satuan_ciesa_tampil, satuan_ciesa_total, nama_entitas, supplier from (select * from (SELECT CONCAT('BC ',GROUP_CONCAT(SUBSTRING(kode_dokumen,n,1) ORDER BY n SEPARATOR '.')) AS kode_dokumen,nomor_aju,no_aju,tgl_aju,no_daftar,tgl_daftar,qty,satuan_ciesa, satuan_ciesa_tampil, satuan_ciesa_total,total,total_idr, nama_entitas FROM (
            select kode_dokumen,nomor_aju,no_aju,tgl_aju,no_daftar,tgl_daftar,ROUND(SUM(qty),2) AS qty,satuan_sb kode_satuan,ROUND(SUM(total),2) AS total,ROUND(SUM(total_idr),2) AS total_idr, GROUP_CONCAT(DISTINCT satuan_sb SEPARATOR ', ') AS satuan_ciesa, GROUP_CONCAT(DISTINCT kode_satuan SEPARATOR ', ') AS satuan_ciesa_tampil, GROUP_CONCAT(CONCAT(kode_satuan, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_ciesa_total, nama_entitas from (select a.*, b.satuan_sb from (select a.*, b.nama_entitas from (SELECT kode_dokumen,nomor_aju,no_aju,tgl_aju,no_daftar,tgl_daftar,ROUND(SUM(jumlah_satuan),2) AS qty,kode_satuan,ROUND(SUM(cif),2) AS total,ROUND(SUM(cif_rupiah),2) AS total_idr FROM (
            SELECT a.*,kode_barang,uraian,jumlah_satuan,kode_satuan,cif,cif_rupiah FROM (
            SELECT kode_dokumen,nomor_aju,SUBSTRING(nomor_aju,-6) AS no_aju,DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju,13,8),'%Y%m%d'),'%Y-%m-%d') AS tgl_aju,LPAD(nomor_daftar,6,0) AS no_daftar,tanggal_daftar AS tgl_daftar FROM exim_header
            ) a LEFT JOIN (
            select nomor_aju, kode_barang, uraian, jumlah_satuan, CASE
            WHEN fil_aju IN (25, 40, 41) THEN harga_penyerahan
            WHEN fil_aju IN (23, 27, 261, 262) THEN cif
            WHEN fil_aju IN (30) THEN fob
            ELSE '0'
            END AS cif, CASE
            WHEN fil_aju IN (25, 40, 41) THEN harga_penyerahan
            WHEN fil_aju IN (23, 27, 261, 262) THEN cif_rupiah
            WHEN fil_aju IN (30) THEN (fob * ndpbm)
            ELSE '0'
            END AS cif_rupiah, kode_satuan
            from (SELECT nomor_aju, LEFT(nomor_aju,6) + 0 fil_aju, kode_barang,uraian,jumlah_satuan,kode_satuan, cif,cif_rupiah,harga_satuan,ndpbm, fob, harga_penyerahan FROM exim_barang) a
            ) b ON b.nomor_aju=a.nomor_aju
            ) a GROUP BY a.no_daftar, a.nomor_aju, kode_satuan) a LEFT JOIN
            (select * from (select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (25,27,41,261) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '9' and kode_entitas = '9' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (40,262) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where (seri != '4' and kode_entitas != '4' and (kode_jenis_identitas != '6')) and (LEFT(nomor_aju,6) + 0) IN (23) and (nama_entitas != 'PT NIRWANA ALABARE GARMENT' and nama_entitas != 'NIRWANA ALABARE GARMENT') GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '' and (LEFT(nomor_aju,6) + 0) IN (30) GROUP BY nomor_aju) a) b on b.nomor_aju = a.nomor_aju) a LEFT JOIN (select satuan_ceisa, GROUP_CONCAT(satuan_sb) satuan_sb from mapping_satuan_ceisa GROUP BY satuan_ceisa) b on b.satuan_ceisa = a.kode_satuan) a GROUP BY a.no_daftar, a.nomor_aju) a JOIN ( SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) numbers ON n<=LENGTH(a.kode_dokumen) GROUP BY no_daftar, nomor_aju ORDER BY nomor_aju asc) a
            UNION
            select jenis_dok, nomor_aju, no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, qty, GROUP_CONCAT(DISTINCT satuan SEPARATOR ', ') AS satuan_ciesa, GROUP_CONCAT(DISTINCT satuan SEPARATOR ', ') AS satuan_ciesa_tampil, GROUP_CONCAT(CONCAT(satuan, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_ciesa_total, total, total_idr, supplier from ( select jenis_dok, nomor_aju, nomor_aju no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, sum(qty) qty, satuan, sum(price) total, sum(IF(rate is null,price,price * rate)) total_idr, supplier from exim_ceisa_manual a left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tanggal_daftar and cr.curr = a.curr INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier where a.status != 'CANCEL' GROUP BY nomor_aju, nomor_daftar, satuan) a GROUP BY nomor_aju, nomor_daftar) a
            left join (
            select * from (select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, GROUP_CONCAT(DISTINCT bpbno_int SEPARATOR ', ') no_bpb, GROUP_CONCAT(DISTINCT unit SEPARATOR ', ') AS satuan_sb, GROUP_CONCAT(CONCAT(unit, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_sb_total, SUM(qty) qty_sb, sum(total) total_sb, sum(total * rate) total_sb_idr, supplier from (select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, bpbno_int, unit, SUM(qty) qty, sum(qty * coalesce(ifnull(price_bc,price),0)) total, IF(rate is null,'1',rate) rate, supplier from bpb a INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.bpbdate and cr.curr = a.curr where bcdate >= '".$this->from."' and bcdate <= '".$this->to."' and (bcno is not null and bcno not in ('','-')) GROUP BY bcno, jenis_dok, nomor_aju, unit) a GROUP BY bcno, jenis_dok, nomor_aju
            UNION
            select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, GROUP_CONCAT(DISTINCT bppbno_int SEPARATOR ', ') no_bpb, GROUP_CONCAT(DISTINCT unit SEPARATOR ', ') AS satuan_sb, GROUP_CONCAT(CONCAT(unit, ' (', round(qty,2), ')') SEPARATOR ', ') satuan_sb_total, SUM(qty) qty_sb, sum(total) total_sb, sum(total * rate) total_sb_idr, supplier from (select jenis_dok, nomor_aju, tanggal_aju, bcno, bcdate, bppbno_int, unit, SUM(qty) qty, sum(qty * coalesce(ifnull(price_bc,price),0)) total, IF(rate is null,'1',rate) rate, supplier from bppb a INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.bppbdate and cr.curr = a.curr where bcdate >= '".$this->from."' and bcdate <= '".$this->to."' and (bcno is not null and bcno not in ('','-')) GROUP BY bcno, jenis_dok, nomor_aju, unit) a GROUP BY bcno, jenis_dok, nomor_aju
            ) a GROUP BY bcno, jenis_dok) b on b.nomor_aju = a.no_aju and b.bcno = a.no_daftar and b.jenis_dok = a.kode_dokumen) a where a.tgl_daftar >= '".$this->from."' and a.tgl_daftar <= '".$this->to."' ".$additionalQuery.") a LEFT JOIN
            (select jenis_dok, no_aju, no_daftar, UPPER(keterangan) keterangan from exim_update_keterangan where tgl_daftar >= '".$this->from."' and tgl_daftar <= '".$this->to."' and status != 'CANCEL' GROUP BY jenis_dok, no_aju, no_daftar) b on b.jenis_dok = a.kode_dokumen and b.no_aju = a.no_aju and b.no_daftar = a.no_daftar");




        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
$this->rowCount = count($data) + 3;


return view('accounting.export', [
    'data' => $data,
    'from' => $this->from,
    'to' => $this->to,
    'jenis_dok' => $this->jenis_dok,
    'status' => $this->status
]);
}

public function registerEvents(): array
{
    return [
        AfterSheet::class => [self::class, 'afterSheet']
    ];
}



public static function afterSheet(AfterSheet $event)
{

    $event->sheet->styleCells(
        'A3:U' . $event->getConcernable()->rowCount,
        [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]
    );
}
}
