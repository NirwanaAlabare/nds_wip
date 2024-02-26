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

class ExportLaporanMutDetail implements FromView, WithEvents, ShouldAutoSize
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
        $data = DB::connection('mysql_sb')->select("select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir, CONCAT_WS('',kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan) cari_item from (select concat(a.kode_lok,' FABRIC WAREHOUSE RACK') kode_lok,a.id_jo,no_ws,styleno,buyer,a.id_item,goods_code,itemdesc,a.satuan,sal_awal,qty_in,coalesce(qty_out_sbl,'0') qty_out_sbl,coalesce(qty_out,'0') qty_out from (select b.kode_lok,b.id_jo,b.no_ws,b.styleno,b.buyer,b.id_item,b.goods_code,b.itemdesc,b.satuan, sal_awal, qty_in from (select id_item,unit from whs_sa_fabric  group by id_item,unit
        UNION
        select id_item,unit from whs_inmaterial_fabric_det  where tgl_dok < '" . $this->from . "' group by id_item,unit) a left join
(select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan, sum(sal_awal) sal_awal,sum(qty_in) qty_in from (select 'TR' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_lokasi_inmaterial a 
inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok < '" . $this->from . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
UNION
select 'SA' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.unit, round(sum(qty),2) sal_awal,'0' qty_in from whs_sa_fabric a
inner join masteritem b on b.id_item = a.id_item
left join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_jo order by id_jo asc) jd on a.id_jo = jd.id_jo
left join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.qty > 0  group by a.kode_lok, a.id_item, a.id_jo, a.unit
UNION 
select 'TRI' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan,'0' sal_awal, round(sum(qty_sj),2) qty_in from whs_lokasi_inmaterial a 
inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan) a group by a.kode_lok, a.id_item, a.id_jo, a.satuan

) b on b.id_item = a.id_item and b.satuan = a.unit where kode_lok is not null) a left join (select kode_lok,id_item,id_jo,satuan,ROUND(sum(qty_out_sbl),2) qty_out_sbl,ROUND(sum(qty_out),2) qty_out from (select id,kode_lok,id_item,id_jo,satuan,qty_out_sbl,'0' qty_out from (select 'OMB' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out_sbl from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on a.idbpb_det = b.id where a.status = 'Y' and tgl_mut < '" . $this->from . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
UNION
select 'OTB' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb < '" . $this->from . "' group by no_rak, id_item, id_jo, satuan) a
UNION
select id,kode_lok,id_item,id_jo,satuan,'0' qty_out_sbl, qty_out from (select 'OM' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on a.idbpb_det = b.id where a.status = 'Y' and tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
UNION
select 'OT' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb BETWEEN '" . $this->from . "' and '" . $this->to . "' group by no_rak, id_item, id_jo, satuan) a) a group by kode_lok, id_item, id_jo, satuan) b on b.kode_lok = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item and b.satuan = a.satuan) a");



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('lap-mutasi-detail.export', [
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
            'A3:N' . $event->getConcernable()->rowCount,
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
