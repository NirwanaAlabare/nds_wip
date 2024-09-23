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

class ExportDetailStokOpname implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    protected $itemso, $from, $to;

    public function __construct($itemso, $from, $to)
    {

        $this->itemso = $itemso;
        $this->from = $from;
        $this->to = $to;
        $this->rowCount = 0;
    }


    public function view(): View

    {

        if ($this->itemso == 'Fabric') {
            $data = DB::connection('mysql_sb')->select("select d.tipe_item,a.no_dokumen,a.tgl_dokumen,b.no_barcode,lokasi_scan,lokasi_aktual,b.id_jo,b.id_item,c.goods_code,c.itemdesc,b.no_lot,b.no_roll,d.qty qty_so,b.qty,b.unit,a.status,a.created_by,a.created_at from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen INNER JOIN masteritem c on c.id_item = b.id_item left join whs_saldo_stockopname d on d.no_barcode = b.no_barcode and d.no_transaksi = a.no_transaksi where d.tipe_item = '" . $this->itemso . "' and a.tgl_dokumen BETWEEN '" . $this->itemso . "' and '" . $this->to . "' group by b.id");
        }else{
            $data = DB::connection('mysql_sb')->select("select '' tipe_item, '' no_dokumen,'' tgl_dokumen,'' no_barcode,'' lokasi_scan,'' lokasi_aktual,'' id_jo,'' id_item,'' goods_code,'' itemdesc,'' no_lot,'' no_roll,'' qty_so,'' qty,'' unit,'' status,'' created_by,'' created_at");
        }


        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
$this->rowCount = count($data) + 3;


return view('stock_opname.export_detail', [
    'data' => $data,
    'itemso' => $this->itemso,
    'from' => $this->from,
    'to' => $this->to
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
        'A3:R' . $event->getConcernable()->rowCount,
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
