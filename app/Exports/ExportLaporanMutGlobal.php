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

class ExportLaporanMutGlobal implements FromView, WithEvents, ShouldAutoSize
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
        $data = DB::connection('mysql_sb')->select("select id_item,goods_code,itemdesc,unit,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir, CONCAT_WS('',id_item,goods_code,itemdesc,unit) cari_item from (select id_item,goods_code,itemdesc,unit,SUM(sal_awal) sal_awal,SUM(qty_in) qty_in,SUM(qty_out_sbl) qty_out_sbl,SUM(qty_out) qty_out,SUM(fil) fil from (select a.id_item,a.goods_code,a.itemdesc,a.unit,COALESCE(sal_awal,0) sal_awal,COALESCE(qty_in,0) qty_in,COALESCE(qty_out_sbl,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (COALESCE(sal_awal,0) + COALESCE(qty_in,0)) fil from (
            select a.id_item,a.unit,b.goods_code,b.itemdesc from (select id_item,unit from whs_sa_fabric  group by id_item,unit
            UNION
            select id_item,unit from whs_inmaterial_fabric_det group by id_item,unit) a inner join masteritem b on b.id_item = a.id_item group by id_item,unit) a left join
            (select id_item,unit, sum(sal_awal) sal_awal from (select 'tr' id,id_item,unit, sum(qty_good) sal_awal from whs_inmaterial_fabric_det where tgl_dok < '" . $this->from . "' and status = 'Y' GROUP BY id_item,unit union select 'sa' id,id_item,unit, round(sum(qty),2) sal_awal from whs_sa_fabric GROUP BY id_item,unit) a  GROUP BY id_item,unit) b on b.id_item = a.id_item and b.unit = a.unit left join
            (select id_item,unit, sum(qty_in) qty_in from (select 'T' id,id_item,unit, sum(qty_good) qty_in from whs_inmaterial_fabric_det where tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' and status = 'Y' GROUP BY id_item,unit
UNION                       
select 'M' id,a.id_item,unit satuan,sum(a.qty_mutasi) qty_in from whs_mut_lokasi a where a.status = 'Y' and tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' group by a.id_item,satuan) a group by id_item,unit) c on c.id_item = a.id_item and c.unit = a.unit left join
            (select id_item,satuan, sum(qty_out) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $this->from . "' and a.status = 'Y' GROUP BY id_item,satuan) d on d.id_item = a.id_item and d.satuan = a.unit left join
            (select id_item,satuan, sum(qty_out) qty_out from (select 'T' id,id_item,satuan, sum(qty_out) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y' GROUP BY id_item,satuan
UNION                       
select 'M' id,a.id_item,unit satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a where a.status = 'Y' and tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' group by a.id_item,satuan) a group by id_item,satuan
) e on e.id_item = a.id_item and e.satuan = a.unit) a GROUP BY a.id_item,a.unit) a where fil != 0");



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('lap-mutasi-global.export', [
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
            'A3:I' . $event->getConcernable()->rowCount,
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
