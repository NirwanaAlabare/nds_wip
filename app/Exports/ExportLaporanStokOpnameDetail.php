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

class ExportLaporanStokOpnameDetail implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    protected $no_transaksi, $itemso;

    public function __construct($no_transaksi, $itemso)
    {

        $this->no_transaksi = $no_transaksi;
        $this->itemso = $itemso;
        $this->rowCount = 0;
    }


    public function view(): View

    {

        if ($this->itemso == 'Fabric') {
            $data = DB::connection('mysql_sb')->select("select *,FORMAT(qty,2) qty_show,FORMAT(qty_so1,2) qty_so_show,FORMAT(qty_sisa1,2) qty_sisa_show from (select a.*,COALESCE(qty_scan,0) qty_so1, round(a.qty - COALESCE(qty_scan,0),2) qty_sisa1 from(select a.status,a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(sum(a.qty),2) qty,a.unit,0 qty_so, round(sum(a.qty),2) qty_sisa from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.no_transaksi = '" . $this->no_transaksi . "' group by no_transaksi,kode_lok,id_jo,id_item) a left join (select no_transaksi notr,lokasi_aktual,id_jo,id_item,sum(qty) qty_scan,COUNT(no_barcode) qty_roll_scan from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen GROUP BY no_transaksi,lokasi_aktual,id_item,id_jo) b on b.notr = a.no_transaksi and b.lokasi_aktual = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item) a");
        }elseif ($this->itemso == 'Sparepart') {
            $data = DB::connection('mysql_sb')->select("select a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(a.qty,2) qty,a.unit,0 qty_so, round(a.qty,2) qty_sisa from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.tipe_item = '" . $this->itemso . "' and a.tgl_filter BETWEEN  '" . $this->from . "' and '" . $this->to . "'");
        }else{
            $data = DB::connection('mysql_sb')->select("select '' no_transaksi,'' tipe_item,'' tgl_saldo,'' kode_lok,'' id_jo,'' id_item,'' goods_code,'' itemdesc,0 qty,'' unit,0 qty_so, 0 qty_sisa, 0 qty_show, 0 qty_so_show, 0 qty_sisa_show");
        }


        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
$this->rowCount = count($data) + 2;


return view('stock_opname.export_detail_trans', [
    'data' => $data,
    'no_transaksi' => $this->no_transaksi,
    'itemso' => $this->itemso,
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
        'A2:N' . $event->getConcernable()->rowCount,
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
