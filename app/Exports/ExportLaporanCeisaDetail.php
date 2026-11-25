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
use App\Support\RumusCeisa;
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
            END AS kode_dokumen_format FROM ( SELECT a.*,c.nama_entitas,kode_barang, uraian, qty, unit, (cif/qty) price, rates, cif, cif_rupiah FROM (SELECT no_dokumen, kode_dokumen ,nomor_aju,SUBSTRING(nomor_aju,-6) no_aju,DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju,13,8),'%Y%m%d'),'%Y-%m-%d') tgl_aju,LPAD(nomor_daftar,6,0) no_daftar,tanggal_daftar tgl_daftar, created_by, created_date, IF(kode_valuta = '' OR kode_valuta is null, 'IDR', kode_valuta) curr FROM exim_header) a LEFT JOIN ( SELECT nomor_aju,kode_barang,uraian,jumlah_satuan qty,kode_satuan unit,IF(fil_aju IN (33),(fob/jumlah_satuan),(cif/jumlah_satuan)) price, ndpbm rates, ".RumusCeisa::total()." AS cif, ".RumusCeisa::totalIdr()." AS cif_rupiah FROM (select eb.*, (select MAX(eh.kode_dokumen) from exim_header eh where eh.nomor_aju = eb.nomor_aju) + 0 fil_aju, ".RumusCeisa::kolomTanggal()." from exim_barang eb) exim_barang) b ON b.nomor_aju=a.nomor_aju left join ".RumusCeisa::supplier()." c on c.nomor_aju=a.nomor_aju) a) a where tgl_daftar >= '".$this->from."' and tgl_daftar <= '".$this->to."'
            UNION
            select no_dok, jenis_dok, nomor_aju, nomor_aju no_aju, tanggal_aju, nomor_daftar, tanggal_daftar, created_by, created_date, a.curr, supplier, '-' kode_barang, nama_item, qty, satuan, (price / qty) price, IF(rate is null,1,rate) rate, price cif, IF(rate is null,price,price * rate) cif_rupiah, jenis_dok dok_format from exim_ceisa_manual a left join (select tanggal, curr, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tanggal_daftar and cr.curr = a.curr INNER JOIN mastersupplier ms on ms.id_supplier = a.id_supplier where tanggal_daftar >= '".$this->from."' and tanggal_daftar <= '".$this->to."' and status != 'CANCEL') a where 1=1 ".$additionalQuery."");




        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('export-import.ceisa-detail.export', [
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
