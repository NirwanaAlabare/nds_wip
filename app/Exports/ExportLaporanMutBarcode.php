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
//        $data = DB::connection('mysql_sb')->select("select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' GROUP BY no_barcode
//                 UNION
//                 select 'TB' id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, 0 qty_in, sum(qty_sj) qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok < '" . $this->from . "' GROUP BY no_barcode
//                 UNION
//                 select 'SA' id, id idnya, '-' supplier, no_bpb, tgl_bpb,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,unit from whs_sa_fabric GROUP BY no_barcode
//                 UNION
//                 select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y' GROUP BY no_barcode
//                 UNION
//                 select 'IMB' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_sj qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det where tgl_mut < '" . $this->from . "' and a.status = 'Y' GROUP BY no_barcode) a GROUP BY no_barcode) a LEFT JOIN
//                 (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
//                 UNION
//                 select 'OB' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, 0 qty_out, qty_out qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $this->from . "' and a.status = 'Y'
//                 UNION
//                 select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
//                 UNION
//                 select 'OMB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, a.qty_mutasi qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut < '" . $this->from . "' and a.status = 'Y'
//                 UNION
//                 select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
//                 UNION
//                 select 'OMSB' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, 0 qty_in, qty_mutasi qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut < '" . $this->from . "' and a.status = 'Y') a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0");

        $data = DB::connection('mysql_sb')->select("select * from (select a.*, kpno, styleno, mi.itemdesc from (select no_barcode, no_dok, tgl_dok, supplier, kode_lok, id_jo, id_item, no_lot, no_roll, satuan, round((qty_in_bfr - coalesce(qty_out_bfr,0)),2) sal_awal,round(qty_in,2) qty_in,ROUND(coalesce(qty_out_bfr,0),2) qty_out_sbl,ROUND(coalesce(qty_out,0),2) qty_out, round((qty_in_bfr + qty_in - coalesce(qty_out_bfr,0) - coalesce(qty_out,0)),2) sal_akhir  from (select no_dok, tgl_dok,supplier, no_barcode, kode_lok, id_jo, id_item, no_lot, no_roll, sum(qty_in) qty_in, sum(qty_in_bfr) qty_in_bfr, satuan from (
                select 'T'id, a.id idnya,b.supplier, b.no_dok, b.tgl_dok, no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_in_bfr,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok BETWEEN '" . $this->from . "' and '" . $this->to . "' GROUP BY no_barcode
                UNION
                select 'SA' id, id idnya, supplier, no_dok, tgl_dok,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,0 qty_in, qty qty_in_bfr,satuan from whs_sa_fabric_copy where tgl_periode = DATE_FORMAT('" . $this->from . "', '%Y-%m-01') GROUP BY no_barcode
                UNION
                select 'IM' id, a.id idnya, '-' supplier,a.no_mut, tgl_mut, no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_sj qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode_old = a.idbpb_det and b.kode_lok = a.rak_tujuan where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y' GROUP BY no_barcode
                ) a GROUP BY no_barcode) a LEFT JOIN
                (select id_roll, SUM(qty_out) qty_out, SUM(qty_out_bfr) qty_out_bfr from (
                select 'O' id, a.id idnya, id_roll, no_rak, id_jo, id_item, no_lot, no_roll, qty_out, 0 qty_out_bfr, satuan from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
                UNION
                select 'OM' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, a.qty_mutasi qty_in, 0 qty_in_bfr,satuan from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
                UNION
                select 'OMS' id, a.id idnya,no_barcode,kode_lok,b.id_jo,b.id_item,b.no_lot,b.no_roll, qty_mutasi qty_in, 0 qty_in_bfr,b.unit from whs_mut_lokasi a inner join whs_sa_fabric b on b.no_barcode = a.idbpb_det where tgl_mut BETWEEN '" . $this->from . "' and '" . $this->to . "' and a.status = 'Y'
            ) a GROUP BY id_roll) b on b.id_roll = a.no_barcode) a left join (select id_jo,kpno,styleno from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so group by id_jo) b on b.id_jo=a.id_jo INNER JOIN masteritem mi on mi.id_item = a.id_item where sal_awal != 0 OR qty_in != 0 OR qty_out != 0) a where no_barcode NOT IN ('391191','394292','F25704','F28526')");



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
