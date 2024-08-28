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

class ExportLaporanPengeluaran implements FromView, WithEvents, ShouldAutoSize
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

// $data = DB::connection('mysql_sb')->select("(select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans,cp.nama_panel, cc.color_gmt 
// from whs_bppb_h a 
// inner join whs_bppb_det b on b.no_bppb = a.no_bppb
// inner join masteritem s on b.id_item=s.id_item 
// left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
// left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
// left join so on tmpjod.id_so=so.id 
// left join act_costing ac on so.id_cost=ac.id 
// left join (select id_jo,bom_jo_item.id_item,group_concat(distinct(nama_panel)) nama_panel from bom_jo_item inner join masterpanel mp on bom_jo_item.id_panel = mp.id where id_panel != '0' group by id_item, id_jo) cp on s.id_gen = cp.id_item and b.id_jo = cp.id_jo 
// left join (select id_item, id_jo, group_concat(distinct(color)) color_gmt from bom_jo_item k inner join so_det sd on k.id_so_det = sd.id where status = 'M' and k.cancel = 'N' group by id_item, id_jo) cc on s.id_gen = cc.id_item and b.id_jo = cc.id_jo 
// where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $this->from . "' and '" . $this->to . "' and matclass= 'FABRIC' GROUP BY b.id_jo,b.id_item,b.no_bppb order by a.no_bppb)
// UNION
// (select a.no_mut bpbno,'' bppbno_req,a.tgl_mut bpbdate,a.no_invoice invno,a.type_bc jenis_dok,right(a.no_aju,6) no_aju,a.tgl_aju, lpad(a.no_daftar,6,'0') bcno,a.tgl_daftar bcdate,a.supplier,a.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, qty,'0' qty_good, qty_reject, a.unit,'' berat_bersih,a.deskripsi remark,a.username,a.confirm_by,tmpjo.kpno ws,tmpjo.styleno,a.curr,if(z.tipe_com !='Regular','0',a.price)price, tmpjo.kpno idws_act,'' jenis_trans,cp.nama_panel, cc.color_gmt from (select a.no_mut,a.tgl_mut,c.type_bc,c.no_aju,c.tgl_aju, c.no_daftar,c.tgl_daftar,c.supplier,c.no_po,c.no_invoice,b.id_item, sum(qty_mutasi) qty,sum(qty_mutasi) as qty_good,'0' as qty_reject, b.unit,mut.deskripsi,CONCAT(mut.created_by,' (',mut.created_at, ') ') username,CONCAT(mut.approved_by,' (',mut.approved_date, ') ') confirm_by,b.curr,b.price, c.type_pch,b.id_jo from whs_mut_lokasi a 
// inner join whs_mut_lokasi_h mut on mut.no_mut = a.no_mut
// inner join whs_inmaterial_fabric c on c.no_dok = a.no_bpb
// inner join (select * FROM whs_inmaterial_fabric_det GROUP BY no_dok,id_item) b on b.no_dok = a.no_bpb and a.id_item = b.id_item where a.status = 'Y' GROUP BY a.no_mut,id_item) a
// inner join masteritem s on a.id_item=s.id_item 
// left join (select no_mut,id_jo,id_item, GROUP_CONCAT(DISTINCT CONCAT(kode_lok,' FABRIC WAREHOUSE RACK')) rak from whs_lokasi_inmaterial  where status = 'Y' and no_mut is not null group by no_mut) lr on a.no_mut = lr.no_mut
// left join po_header po on po.pono = a.no_po
// left join po_header_draft z on z.id = po.id_draft
// left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=a.id_jo 
// left join (select id_jo,bom_jo_item.id_item,group_concat(distinct(nama_panel)) nama_panel from bom_jo_item inner join masterpanel mp on bom_jo_item.id_panel = mp.id where id_panel != '0' group by id_item, id_jo) cp on s.id_gen = cp.id_item and a.id_jo = cp.id_jo 
// left join (select id_item, id_jo, group_concat(distinct(color)) color_gmt from bom_jo_item k inner join so_det sd on k.id_so_det = sd.id where status = 'M' and k.cancel = 'N' group by id_item, id_jo) cc on s.id_gen = cc.id_item and a.id_jo = cc.id_jo
//  where a.tgl_mut BETWEEN  '" . $this->from . "' and '" . $this->to . "')");


        $data = DB::connection('mysql_sb')->select("(select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans,cp.nama_panel, cc.color_gmt 
            from whs_bppb_h a 
            inner join whs_bppb_det b on b.no_bppb = a.no_bppb
            inner join masteritem s on b.id_item=s.id_item 
            left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
            left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
            left join so on tmpjod.id_so=so.id 
            left join act_costing ac on so.id_cost=ac.id 
            left join (select id_jo,bom_jo_item.id_item,group_concat(distinct(nama_panel)) nama_panel from bom_jo_item inner join masterpanel mp on bom_jo_item.id_panel = mp.id where id_panel != '0' group by id_item, id_jo) cp on s.id_gen = cp.id_item and b.id_jo = cp.id_jo 
            left join (select id_item, id_jo, group_concat(distinct(color)) color_gmt from bom_jo_item k inner join so_det sd on k.id_so_det = sd.id where status = 'M' and k.cancel = 'N' group by id_item, id_jo) cc on s.id_gen = cc.id_item and b.id_jo = cc.id_jo 
            where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel' and a.tgl_bppb BETWEEN  '" . $this->from . "' and '" . $this->to . "' and matclass= 'FABRIC' GROUP BY b.id_jo,b.id_item,b.no_bppb order by a.no_bppb)
            UNION
            (select a.no_mut bpbno,'' bppbno_req,a.tgl_mut bpbdate,a.no_invoice invno,a.type_bc jenis_dok,right(a.no_aju,6) no_aju,a.tgl_aju, lpad(a.no_daftar,6,'0') bcno,a.tgl_daftar bcdate,a.supplier,a.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, qty,'0' qty_good, qty_reject, a.unit,'' berat_bersih,a.deskripsi remark,a.username,a.confirm_by,tmpjo.kpno ws,tmpjo.styleno,a.curr,if(z.tipe_com !='Regular','0',a.price)price, tmpjo.kpno idws_act,'' jenis_trans,cp.nama_panel, cc.color_gmt from (select a.no_mut,a.tgl_mut,c.type_bc,c.no_aju,c.tgl_aju, c.no_daftar,c.tgl_daftar,c.supplier,c.no_po,c.no_invoice,b.id_item, sum(qty_mutasi) qty,sum(qty_mutasi) as qty_good,'0' as qty_reject, b.unit,mut.deskripsi,CONCAT(mut.created_by,' (',mut.created_at, ') ') username,CONCAT(mut.approved_by,' (',mut.approved_date, ') ') confirm_by,b.curr,b.price, c.type_pch,b.id_jo from whs_mut_lokasi a 
            inner join whs_mut_lokasi_h mut on mut.no_mut = a.no_mut
            inner join whs_inmaterial_fabric c on c.no_dok = a.no_bpb
            left join (select no_dok,id_jo,id_item,'-' curr, '0' price,satuan unit FROM whs_lokasi_inmaterial GROUP BY no_dok,id_item UNION
            select no_bpb,id_jo,id_item,'-' curr, '0' price,unit FROM whs_sa_fabric GROUP BY no_bpb,id_item) b on b.no_dok = a.no_bpb and a.id_item = b.id_item where a.status = 'Y' GROUP BY a.no_mut,id_item) a
            inner join masteritem s on a.id_item=s.id_item 
            left join (select no_dok no_mut,id_jo,id_item, GROUP_CONCAT(DISTINCT CONCAT(kode_lok,' FABRIC WAREHOUSE RACK')) rak from whs_lokasi_inmaterial  where status = 'Y' and no_mut is not null group by no_dok) lr on a.no_mut = lr.no_mut
            left join po_header po on po.pono = a.no_po
            left join po_header_draft z on z.id = po.id_draft
            left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=a.id_jo 
            left join (select id_jo,bom_jo_item.id_item,group_concat(distinct(nama_panel)) nama_panel from bom_jo_item inner join masterpanel mp on bom_jo_item.id_panel = mp.id where id_panel != '0' group by id_item, id_jo) cp on s.id_gen = cp.id_item and a.id_jo = cp.id_jo 
            left join (select id_item, id_jo, group_concat(distinct(color)) color_gmt from bom_jo_item k inner join so_det sd on k.id_so_det = sd.id where status = 'M' and k.cancel = 'N' group by id_item, id_jo) cc on s.id_gen = cc.id_item and a.id_jo = cc.id_jo
            where a.tgl_mut BETWEEN  '" . $this->from . "' and '" . $this->to . "')");



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
$this->rowCount = count($data) + 3;


return view('lap-det-pengeluaran.export', [
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
        'A3:AJ' . $event->getConcernable()->rowCount,
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
