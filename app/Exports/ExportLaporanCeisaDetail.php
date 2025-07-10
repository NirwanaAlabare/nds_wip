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

class ExportLaporanCeisaDetail implements FromView, WithEvents, ShouldAutoSize
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
            $additionalQuery .= " and kode_dokumen_format = '" . $this->jenis_dok . "' ";
        }

        $data = DB::connection('mysql_sb')->select("select * from (
            select * from (SELECT *, CASE 
            WHEN LENGTH(kode_dokumen) = 3 THEN 
            CONCAT('BC ', 
            SUBSTRING(kode_dokumen, 1, 1), '.', 
            SUBSTRING(kode_dokumen, 2, 1), '.', 
            SUBSTRING(kode_dokumen, 3, 1))
            WHEN LENGTH(kode_dokumen) = 2 THEN 
            CONCAT('BC ', 
            SUBSTRING(kode_dokumen, 1, 1), '.', 
            SUBSTRING(kode_dokumen, 2, 1))
            ELSE kode_dokumen 
            END AS kode_dokumen_format FROM ( SELECT a.*,c.nama_entitas,kode_barang, uraian, qty, unit, curr, (cif/qty) price, rates, cif, cif_rupiah FROM (SELECT no_dokumen, kode_dokumen ,nomor_aju,SUBSTRING(nomor_aju,-6) no_aju,DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju,13,8),'%Y%m%d'),'%Y-%m-%d') tgl_aju,LPAD(nomor_daftar,6,0) no_daftar,tanggal_daftar tgl_daftar, created_by, created_date FROM exim_header) a LEFT JOIN ( SELECT nomor_aju,kode_barang,uraian,jumlah_satuan qty,kode_satuan unit, IF(ndpbm <= 1,'IDR','USD') curr,(cif/jumlah_satuan) price, ndpbm rates, CASE 
            WHEN (LEFT(nomor_aju,6) + 0) IN (25, 40, 41) THEN harga_penyerahan
            WHEN (LEFT(nomor_aju,6) + 0) IN (23, 27, 261, 262) THEN cif
            WHEN (LEFT(nomor_aju,6) + 0) IN (30) THEN fob
            ELSE '0'
            END AS cif, CASE 
            WHEN (LEFT(nomor_aju,6) + 0) IN (25, 40, 41) THEN harga_penyerahan
            WHEN (LEFT(nomor_aju,6) + 0) IN (23, 27, 261, 262) THEN cif_rupiah
            WHEN (LEFT(nomor_aju,6) + 0) IN (30) THEN (fob * ndpbm)
            ELSE '0'
            END AS cif_rupiah FROM exim_barang) b ON b.nomor_aju=a.nomor_aju left join (select * from (select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (25,27,41,261) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '9' and kode_entitas = '9' and kode_jenis_identitas = '6' and (LEFT(nomor_aju,6) + 0) IN (40,262) GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where (seri != '4' and kode_entitas != '4' and (kode_jenis_identitas != '6')) and (LEFT(nomor_aju,6) + 0) IN (23) and (nama_entitas != 'PT NIRWANA ALABARE GARMENT' and nama_entitas != 'NIRWANA ALABARE GARMENT') GROUP BY nomor_aju
            UNION
            select nomor_aju, nomor_identitas, nama_entitas, alamat_entitas from exim_entitas where seri = '8' and kode_entitas = '8' and kode_jenis_identitas = '' and (LEFT(nomor_aju,6) + 0) IN (30) GROUP BY nomor_aju) a) c on c.nomor_aju=a.nomor_aju) a) a where tgl_daftar >= '".$this->from."' and tgl_daftar <= '".$this->to."'
            UNION
            select no_dok, jenis_dok, nomor_aju, nomor_aju no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, created_by, created_date, supplier, '-' kode_barang, nama_item, qty, satuan, a.curr, (price / qty) price, IF(rate is null,1,rate) rate, price cif, IF(rate is null,price,price * rate) cif_rupiah, jenis_dok dok_format from exim_ceisa_manual a left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tanggal_daftar and cr.curr = a.curr INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier where tanggal_daftar >= '".$this->from."' and tanggal_daftar <= '".$this->to."' and status != 'CANCEL') a where 1=1 ".$additionalQuery."");




        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('accounting.export-ceisa-detail', [
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
            'A3:S' . $event->getConcernable()->rowCount,
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
