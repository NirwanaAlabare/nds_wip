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
            END AS kode_dokumen_format FROM ( SELECT a.*,c.nama_entitas,kode_barang, uraian, qty, unit, (cif/qty) price, rates, cif, cif_rupiah FROM (SELECT no_dokumen, kode_dokumen ,nomor_aju,SUBSTRING(nomor_aju,-6) no_aju,DATE_FORMAT(STR_TO_DATE(SUBSTRING(nomor_aju,13,8),'%Y%m%d'),'%Y-%m-%d') tgl_aju,LPAD(nomor_daftar,6,0) no_daftar,tanggal_daftar tgl_daftar, created_by, created_date, IF(kode_valuta = '' OR kode_valuta is null, 'IDR', kode_valuta) curr FROM exim_header) a LEFT JOIN ( SELECT nomor_aju,kode_barang,uraian,jumlah_satuan qty,kode_satuan unit,(cif/jumlah_satuan) price, ndpbm rates, CASE 
            WHEN (LEFT(nomor_aju,6) + 0) IN (25, 40, 41) THEN harga_penyerahan
            WHEN (LEFT(nomor_aju,6) + 0) IN (23, 27, 261, 262) THEN cif
            WHEN (LEFT(nomor_aju,6) + 0) IN (30, 33) THEN (harga_satuan * jumlah_satuan)
            ELSE '0'
            END AS cif, CASE
            WHEN (LEFT(nomor_aju,6) + 0) IN (25, 40, 41) THEN harga_penyerahan
            WHEN (LEFT(nomor_aju,6) + 0) IN (23, 27, 261, 262) THEN cif_rupiah
            WHEN (LEFT(nomor_aju,6) + 0) IN (30, 33) THEN ((harga_satuan * jumlah_satuan) * ndpbm)
            ELSE '0'
            END AS cif_rupiah FROM exim_barang) b ON b.nomor_aju=a.nomor_aju left join (SELECT *
FROM
(
    SELECT
    b.nomor_aju,
    a.nomor_identitas,
    a.nama_entitas,
    a.alamat_entitas
FROM exim_entitas a
INNER JOIN exim_header b
    ON b.nomor_aju = a.nomor_aju
WHERE
(
    (
        (LEFT(b.nomor_aju,6)+0) = 27
        AND
        (
            -- Prioritas 1 : 3,3,6
            (
                a.seri = '3'
                AND a.kode_entitas = '3'
                AND a.kode_jenis_identitas = '6'
            )

            OR

            -- Tambahan : 3,8,6
            (
                a.seri = '3'
                AND a.kode_entitas = '8'
                AND a.kode_jenis_identitas = '6'
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM exim_entitas x
                    WHERE x.nomor_aju = a.nomor_aju
                      AND x.seri = '3'
                      AND x.kode_entitas = '3'
                      AND x.kode_jenis_identitas = '6'
                )
            )

            OR

            -- Terakhir : 8,8,6
            (
                a.seri = '8'
                AND a.kode_entitas = '8'
                AND a.kode_jenis_identitas = '6'
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM exim_entitas x
                    WHERE x.nomor_aju = a.nomor_aju
                      AND x.seri = '3'
                      AND x.kode_entitas IN ('3','8')
                      AND x.kode_jenis_identitas = '6'
                )
            )
        )
    )

    OR

    (
        (LEFT(b.nomor_aju,6)+0) IN (25,41,261)
        AND a.seri='8'
        AND a.kode_entitas='8'
        AND a.kode_jenis_identitas='6'
    )
)
GROUP BY b.nomor_aju

    UNION

    SELECT
        b.nomor_aju,
        a.nomor_identitas,
        a.nama_entitas,
        a.alamat_entitas
    FROM exim_entitas a
    INNER JOIN exim_header b
        ON b.nomor_aju = a.nomor_aju
    WHERE
    (
        (
            (LEFT(b.nomor_aju,6)+0) IN (40,262)
            AND
            (
                (
                    a.seri='3'
                    AND a.kode_entitas='9'
                    AND a.kode_jenis_identitas='6'
                )
                OR
                (
                    a.seri='9'
                    AND a.kode_entitas='9'
                    AND a.kode_jenis_identitas='6'
                    AND NOT EXISTS
                    (
                        SELECT 1
                        FROM exim_entitas x
                        WHERE x.nomor_aju = a.nomor_aju
                          AND x.seri='3'
                          AND x.kode_entitas='9'
                          AND x.kode_jenis_identitas='6'
                    )
                )
            )
        )

    )
    GROUP BY b.nomor_aju

    UNION

    SELECT
        b.nomor_aju,
        a.nomor_identitas,
        a.nama_entitas,
        a.alamat_entitas
    FROM exim_entitas a
    INNER JOIN exim_header b
        ON b.nomor_aju = a.nomor_aju
    WHERE
        a.seri <> '4'
        AND a.kode_entitas <> '4'
        AND a.kode_jenis_identitas <> '6'
        AND (LEFT(b.nomor_aju,6)+0)=23
        AND a.nama_entitas NOT IN
        (
            'PT NIRWANA ALABARE GARMENT',
            'NIRWANA ALABARE GARMENT'
        )
    GROUP BY b.nomor_aju

    UNION

    /* BC 30,33 */
    SELECT
        b.nomor_aju,
        a.nomor_identitas,
        a.nama_entitas,
        a.alamat_entitas
    FROM exim_entitas a
    INNER JOIN exim_header b
        ON b.nomor_aju = a.nomor_aju
    WHERE
        a.seri='8'
        AND a.kode_entitas='8'
        AND a.kode_jenis_identitas=''
        AND (LEFT(b.nomor_aju,6)+0) IN (30,33)
    GROUP BY b.nomor_aju

) a) c on c.nomor_aju=a.nomor_aju) a) a where tgl_daftar >= '".$this->from."' and tgl_daftar <= '".$this->to."'
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
