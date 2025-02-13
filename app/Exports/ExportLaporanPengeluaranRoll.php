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

class ExportLaporanPengeluaranRoll implements FromView, WithEvents, ShouldAutoSize
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
//         $data = DB::connection('mysql_sb')->select("select * , CONCAT_WS('',no_bppb,tgl_bppb,no_req,tujuan,no_barcode,no_roll,no_lot,qty_out,unit,id_item,id_jo,ws,goods_code,itemdesc,color,size,remark,username,confirm_by)cari_data from (select a.no_bppb,a.tgl_bppb,a.no_req,a.tujuan,b.id_roll no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_out,4) qty_out, b.satuan unit,b.id_item,b.id_jo,ac.kpno ws,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by
// from whs_bppb_h a 
// inner join whs_bppb_det b on b.no_bppb = a.no_bppb
// inner join masteritem s on b.id_item=s.id_item 
// left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
// left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
// left join so on tmpjod.id_so=so.id 
// left join act_costing ac on so.id_cost=ac.id  
// where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb >= '" . $this->from . "' and a.tgl_bppb <= '" . $this->to . "' and matclass= 'FABRIC' GROUP BY b.id order by a.no_bppb) a");

$data = DB::connection('mysql_sb')->select("select * , CONCAT_WS('',no_bppb,tgl_bppb,no_req,tujuan,no_barcode,no_roll,no_lot,qty_out,unit,id_item,id_jo,ws,goods_code,itemdesc,color,size,remark,username,confirm_by)cari_data from (select br.idws_act,ac.styleno,a.no_bppb,a.tgl_bppb,a.no_req,a.tujuan,b.id_roll no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_out,4) qty_out, b.satuan unit,b.id_item,b.id_jo,ac.kpno ws,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by
from whs_bppb_h a 
inner join whs_bppb_det b on b.no_bppb = a.no_bppb
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
left join so on tmpjod.id_so=so.id 
left join act_costing ac on so.id_cost=ac.id  
where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $this->from . "' and '" . $this->to . "' and matclass= 'FABRIC' GROUP BY b.id order by a.no_bppb) a
UNION
select * , CONCAT_WS('',no_bppb,tgl_bppb,no_req,tujuan,no_barcode,no_roll,no_lot,qty_out,unit,id_item,id_jo,ws,goods_code,itemdesc,color,size,remark,username,confirm_by)cari_data from (select '' ws_aktual, ac.styleno,a.no_mut no_bppb,a.tgl_mut tgl_bppb,'' no_req,'Mutasi Lokasi' tujuan,c.no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_mutasi,4) qty_out, b.unit,b.id_item,c.id_jo,ac.kpno ws,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size,a.deskripsi remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by
from whs_mut_lokasi_h a 
inner join whs_mut_lokasi b on b.no_mut = a.no_mut
inner join (select no_barcode,no_dok,id_jo,id_item,'-' curr, '0' price,satuan unit FROM whs_lokasi_inmaterial GROUP BY no_barcode UNION
select no_barcode,no_bpb,id_jo,id_item,'-' curr, '0' price,unit FROM whs_sa_fabric GROUP BY no_barcode) c on c.no_barcode = b.idbpb_det
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=c.id_jo 
left join so on tmpjod.id_so=so.id 
left join act_costing ac on so.id_cost=ac.id  
where b.status != 'N' and a.status != 'cancel' and a.tgl_mut BETWEEN  '" . $this->from . "' and '" . $this->to . "' GROUP BY b.id order by a.no_mut) a");



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('lap-det-pengeluaran.export_roll', [
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
            'A3:T' . $event->getConcernable()->rowCount,
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
