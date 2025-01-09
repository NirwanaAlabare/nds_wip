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
        // $data = DB::connection('mysql_sb')->select("select *, CONCAT_WS('',no_dok,tgl_dok,no_mut,supplier,rak,barcode,no_roll,no_roll_buyer,no_lot,qty,qty_mut,satuan,id_item,id_jo,no_ws,goods_code,itemdesc,color,size,deskripsi,username,confirm_by) cari_data from (select a.no_dok,b.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_roll_buyer,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,c.id_item,c.id_jo,kpno no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,no_invoice,no_po,styleno from whs_lokasi_inmaterial c inner join whs_inmaterial_fabric_det b on b.no_dok = c.no_dok  inner join whs_inmaterial_fabric a on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=c.id_jo  where c.status = 'Y' and left(a.no_dok,2) ='GK' and a.tgl_dok BETWEEN  '" . $this->from . "' and '" . $this->to . "' group by c.id) a");

        $data = DB::connection('mysql_sb')->select("select *, IF(rate is null,1,rate) rates, round(price * IF(rate is null,1,rate),2) price_idr from (select a.no_dok,a.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_roll_buyer,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,c.id_item,c.id_jo,kpno no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,no_invoice,no_po,styleno from whs_lokasi_inmaterial c inner join (select no_dok,tgl_dok,supplier,deskripsi,created_by,created_at,approved_by,approved_date,no_invoice,no_po from whs_inmaterial_fabric
            UNION
            select a.no_mut,a.tgl_mut,supplier,a.deskripsi,a.created_by,a.created_at,a.approved_by,a.approved_date,c.no_invoice,no_po from whs_mut_lokasi_h a inner join whs_mut_lokasi b on a .no_mut = b.no_mut left JOIN whs_inmaterial_fabric c on c.no_dok = b.no_bpb GROUP BY a.no_mut) a on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=c.id_jo where c.status = 'Y' and a.tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' group by c.id) a left join (select no_barcode, curr, price from whs_inmaterial_fabric_det a INNER JOIN whs_lokasi_inmaterial b on b.no_dok = a.no_dok where tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' GROUP BY no_barcode) b on b.no_barcode = a.barcode left join (select tanggal, curr curr_rate, rate from masterrate where v_codecurr = 'PAJAK' GROUP BY tanggal, curr ) cr on cr.tanggal = a.tgl_dok and cr.curr_rate = b.curr");



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
            'A3:AD' . $event->getConcernable()->rowCount,
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
