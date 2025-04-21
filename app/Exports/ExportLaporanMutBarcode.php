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

class ExportLaporanMutBarcode implements FromView, WithEvents, ShouldAutoSize
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
//         $data = DB::connection('mysql_sb')->select("select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir, CONCAT_WS('',kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan) cari_item from (select concat(a.kode_lok,' FABRIC WAREHOUSE RACK') kode_lok,a.id_jo,no_ws,styleno,buyer,a.id_item,goods_code,itemdesc,a.satuan,sal_awal,qty_in,coalesce(qty_out_sbl,'0') qty_out_sbl,coalesce(qty_out,'0') qty_out from (select b.kode_lok,b.id_jo,b.no_ws,b.styleno,b.buyer,b.id_item,b.goods_code,b.itemdesc,b.satuan, sal_awal, qty_in from (select id_item,unit from whs_sa_fabric  group by id_item,unit
//         UNION
//         select id_item,unit from whs_inmaterial_fabric_det group by id_item,unit) a left join
// (select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan, sum(sal_awal) sal_awal,sum(qty_in) qty_in from (select 'TR' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_lokasi_inmaterial a 
// inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
// inner join masteritem b on b.id_item = a.id_item
// inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
// inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok < '" . $this->from . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
// UNION
// select 'SA' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.unit, round(sum(qty),2) sal_awal,'0' qty_in from whs_sa_fabric a
// inner join masteritem b on b.id_item = a.id_item
// left join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_jo order by id_jo asc) jd on a.id_jo = jd.id_jo
// left join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.qty > 0  group by a.kode_lok, a.id_item, a.id_jo, a.unit
// UNION 
// select 'TRI' id,a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan,'0' sal_awal, round(sum(qty_sj),2) qty_in from whs_lokasi_inmaterial a 
// inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
// inner join masteritem b on b.id_item = a.id_item
// inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
// inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan) a group by a.kode_lok, a.id_item, a.id_jo, a.satuan

// ) b on b.id_item = a.id_item and b.satuan = a.unit where kode_lok is not null) a left join (select kode_lok,id_item,id_jo,satuan,ROUND(sum(qty_out_sbl),2) qty_out_sbl,ROUND(sum(qty_out),2) qty_out from (select id,kode_lok,id_item,id_jo,satuan,qty_out_sbl,'0' qty_out from (select 'OMB' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out_sbl from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on a.idbpb_det = b.no_barcode where a.status = 'Y' and tgl_mut < '" . $this->from . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
// UNION
// select 'OTB' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb < '" . $this->from . "' group by no_rak, id_item, id_jo, satuan) a
// UNION
// select id,kode_lok,id_item,id_jo,satuan,'0' qty_out_sbl, qty_out from (select 'OM' id,b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on a.idbpb_det = b.no_barcode where a.status = 'Y' and tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
// UNION
// select 'OT' id,no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb BETWEEN '" . $this->from . "' and '" . $this->to . "' group by no_rak, id_item, id_jo, satuan) a) a group by kode_lok, id_item, id_jo, satuan) b on b.kode_lok = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item and b.satuan = a.satuan) a");

        $data = DB::connection('mysql_sb')->select("select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' GROUP BY no_barcode
                UNION
                select 'TB' id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, 0 qty_in, sum(qty_sj) qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok < '" . $this->from . "' GROUP BY no_barcode
                UNION
                select 'SA' id, id idnya, '-' supplier, no_bpb, tgl_bpb,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,unit from whs_sa_fabric GROUP BY no_barcode
                UNION
                select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y' GROUP BY no_barcode
                UNION
                select 'IMB' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_sj qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut < '" . $this->from . "' and a.status = 'Y' GROUP BY no_barcode) a GROUP BY no_barcode) a LEFT JOIN
                (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
                UNION
                select 'OB' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, 0 qty_out, qty_out qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $this->from . "' and a.status = 'Y'
                UNION
                select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y' 
                UNION
                select 'OMB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, a.qty_mutasi qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut < '" . $this->from . "' and a.status = 'Y'
                UNION
                select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
                UNION
                select 'OMSB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_mutasi qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut < '" . $this->from . "' and a.status = 'Y') a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0");



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
$this->rowCount = count($data) + 3;


return view('lap-mutasi-barcode.export', [
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
