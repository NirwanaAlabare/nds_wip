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

class ExportDetailReturSB implements FromView, WithEvents, ShouldAutoSize
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

        if ($this->itemso != '') {
            $data = DB::connection('mysql_sb')->select("select kode,bppbno_int, bppbdate, status_return, stylenya, wsno, supplier, a.id_jo, a.id_item, goods_code, itemdesc, qty, unit, price, invno,jenis_dok, nomor_aju, IF(nomor_aju = '' OR nomor_aju is null,'-',tanggal_aju) tanggal_aju, CASE
                WHEN a.confirm = 'Y' THEN 'Confirm'
                WHEN a.cancel = 'Y' THEN 'Cancel'
                ELSE '-'
                END AS status, username created_by,a.dateinput from (SELECT mid(bppbno,4,1) kode,jo_no,ac.kpno wsno,ac.styleno stylenya,a.*,s.goods_code,s.itemdesc itemdesc,supplier FROM bppb a inner join masteritem s on a.id_item=s.id_item inner join mastersupplier ms on a.id_supplier=ms.id_supplier left join jo_det jod on a.id_jo=jod.id_jo left join jo on jod.id_jo=jo.id left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id where mid(bppbno,4,1) in ('A','F','B','N') and mid(bppbno,4,2)!='FG' and right(bppbno,1)='R' and a.bppbdate BETWEEN '" . $this->from . "' and '" . $this->to . "' order by a.id desc) a where kode = '" . $this->itemso . "' order by bppbdate asc");
        }else{
            $data = DB::connection('mysql_sb')->select("select kode,bppbno_int, bppbdate, status_return, stylenya, wsno, supplier, a.id_jo, a.id_item, goods_code, itemdesc, qty, unit, price, invno,jenis_dok, nomor_aju, IF(nomor_aju = '' OR nomor_aju is null,'-',tanggal_aju) tanggal_aju, CASE
                WHEN a.confirm = 'Y' THEN 'Confirm'
                WHEN a.cancel = 'Y' THEN 'Cancel'
                ELSE '-'
                END AS status, username created_by,a.dateinput from (SELECT mid(bppbno,4,1) kode,jo_no,ac.kpno wsno,ac.styleno stylenya,a.*,s.goods_code,s.itemdesc itemdesc,supplier FROM bppb a inner join masteritem s on a.id_item=s.id_item inner join mastersupplier ms on a.id_supplier=ms.id_supplier left join jo_det jod on a.id_jo=jod.id_jo left join jo on jod.id_jo=jo.id left join so on jod.id_so=so.id left join act_costing ac on so.id_cost=ac.id where mid(bppbno,4,1) in ('A','F','B','N') and mid(bppbno,4,2)!='FG' and right(bppbno,1)='R' and a.bppbdate BETWEEN '" . $this->from . "' and '" . $this->to . "' order by a.id desc) a order by bppbdate asc");
        }


        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('procurement.export_detail', [
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
