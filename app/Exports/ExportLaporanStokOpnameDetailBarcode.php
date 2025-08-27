<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class ExportLaporanStokOpnameDetailBarcode implements FromCollection, WithHeadings, WithMapping, WithEvents, WithColumnWidths
{
    use Exportable;

    protected $no_transaksi;
    protected $itemso;
    protected $rowCount;
    protected $rowNumber;

    public function __construct($no_transaksi, $itemso)
    {
        $this->no_transaksi = $no_transaksi;
        $this->itemso       = $itemso;
        $this->rowCount     = 0;
        $this->rowNumber    = 0;
    }

    public function collection()
    {
        $sql = "select *,FORMAT(qty,2) qty_show,FORMAT(qty_so,2) qty_so_show,FORMAT(qty_sisa,2) qty_sisa_show from (select a.*,COALESCE(qty_scan,0) qty_so, round(a.qty - COALESCE(qty_scan,0),2) qty_sisa from(select kpno no_ws, styleno, status,no_barcode,a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(sum(a.qty),2) qty,a.unit,no_lot,no_roll from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) c on a.id_jo = c.id_jo where a.no_transaksi = ? group by no_transaksi,kode_lok,id_jo,id_item,no_barcode) a left join (select no_barcode barcode,no_transaksi notr,lokasi_aktual,id_jo,id_item,sum(qty) qty_scan,COUNT(no_barcode) qty_roll_scan from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen GROUP BY no_transaksi,lokasi_aktual,id_item,id_jo,no_barcode) b on b.notr = a.no_transaksi and b.lokasi_aktual = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item and a.no_barcode = b.barcode) a order by kode_lok asc";

        $data = collect(DB::connection('mysql_sb')->select($sql, [$this->no_transaksi]));
        $this->rowCount = $data->count() + 3; // +3 karena ada judul & baris kosong

        return $data;
    }

    public function headings(): array
    {
        return [
            ["Laporan Detail Barcode"],   // judul di baris pertama
            [""],                         // baris kosong kedua
            [   // header tabel
                "No",
                "No Transaksi",
                "Item Tipe",
                "Tanggal Saldo",
                "No Barcode",
                "ID JO",
                "No WS",
                "Style",
                "ID Item",
                "Kode Item",
                "Deskripsi Item",
                "Lokasi",
                "No Lot",
                "No Roll",
                "Unit",
                "Qty Item",
                "Qty SO",
                "Sisa",
                "Status"
            ]
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->no_transaksi,
            $row->tipe_item,
            $row->tgl_saldo,
            $row->no_barcode,
            $row->id_jo,
            $row->no_ws,
            $row->styleno,
            $row->id_item,
            $row->goods_code,
            $row->itemdesc,
            $row->kode_lok,
            $row->no_lot,
            $row->no_roll,
            $row->unit,
            $row->qty,       // biarkan angka mentah
            $row->qty_so,    // biarkan angka mentah
            $row->qty_sisa,  // biarkan angka mentah
            $row->status,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 12,
            'D' => 12,
            'E' => 15,
            'F' => 10,
            'G' => 25,
            'H' => 30,
            'I' => 10,
            'J' => 50,
            'K' => 100,
            'L' => 12,
            'M' => 12,
            'N' => 12,
            'O' => 10,
            'P' => 15,
            'Q' => 15,
            'R' => 15,
            'S' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // style judul
                $event->sheet->mergeCells('A1:S1');
                $event->sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                    ]
                ]);

                // style header tabel (row ke-3)
                $event->sheet->getStyle('A3:S3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // format angka untuk Qty (kolom P-R = 16-18)
                $event->sheet->getStyle('P4:R' . $this->rowCount)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // freeze header tabel (baris ke-3)
                $event->sheet->freezePane('A4');

                // OPSIONAL: kalau tetap mau border semua data, aktifkan ini (akan lebih lambat)
                
                $event->sheet->getStyle('A3:S' . $this->rowCount)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color'       => ['argb' => '000000'],
                        ],
                    ],
                ]);
                
            }
        ];
    }
}
