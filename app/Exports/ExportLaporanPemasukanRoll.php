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

class ExportLaporanPemasukanRoll implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    protected $from, $to;

    public function __construct($from, $to)
    {

        $this->from = $from;
        $this->to = $to;
        $this->rowCount = 0;
    }


    public function view(): View

    {
        $data = DB::connection('mysql_sb')->select("select *, CONCAT_WS('',no_dok,tgl_dok,no_mut,supplier,rak,barcode,no_roll,no_lot,qty,qty_mut,satuan,id_item,id_jo,no_ws,goods_code,itemdesc,color,size,deskripsi,username,confirm_by) cari_data from (select a.no_dok,b.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,b.id_item,b.id_jo,b.no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by from whs_inmaterial_fabric a inner join whs_inmaterial_fabric_det b on b.no_dok = a.no_dok  inner join whs_lokasi_inmaterial c on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item where c.status = 'Y' and left(a.no_dok,2) ='GK' and a.tgl_dok >= '" . $this->from . "' and a.tgl_dok <= '" . $this->to . "' group by c.id) a");



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('lap-det-pemasukan.export_roll', [
            'data' => $data,
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
            'A3:V' . $event->getConcernable()->rowCount,
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
