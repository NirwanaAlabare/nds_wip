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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
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

class ExportPPIC_Master_so_ppic implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;


    protected $from, $to, $ws, $style, $filter;

    public function __construct($from, $to, $ws, $style, $filter)
    {

        $this->from = $from;
        $this->to = $to;
        $this->ws = $ws;
        $this->style = $style;
        $this->filter = $filter;
        $this->rowCount = 0;
    }


    public function view(): View

    {

        $tgl_awal = $this->from;
        $tgl_akhir = $this->to;
        $ws = $this->ws;
        $style = $this->style;
        $filter = $this->filter;
        if ($filter == 'all') {
            $condition_tgl = " where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'";
            $condition = "";
            if (empty($ws) && empty($style)) {
                $condition = "";
            } else if (!empty($ws) && empty($style)) {
                $condition = "where kpno = '$ws'";
            } else if (empty($ws) && !empty($style)) {
                $condition = "where styleno = '$style'";
            } else if (!empty($ws) && !empty($style)) {
                $condition = "where kpno = '$ws' and styleno = '$style'";
            }
        } else if ($filter == 'date') {
            $condition_tgl = " where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'";
            $condition = "";
        } else if ($filter == 'ws-style') {
            $condition_tgl = "";
            if (!empty($ws) && empty($style)) {
                $condition = "where kpno = '$ws'";
            } else if (empty($ws) && !empty($style)) {
                $condition = "where styleno = '$style'";
            } else if (!empty($ws) && !empty($style)) {
                $condition = "where kpno = '$ws' and styleno = '$style'";
            }
        }

        $data = DB::select("WITH ppic as (
                select * from laravel_nds.ppic_master_so p
                $condition_tgl
                ),
                gmt as (
                select sd.id as id_so_det,id_jo,kpno,styleno, sd.*, Supplier buyer, product_group from signalbit_erp.so_det sd
                inner join signalbit_erp.so on sd.id_so = so.id
                inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                inner join signalbit_erp.jo_det jd on so.id = jd.id_so
                inner join signalbit_erp.masterproduct mp on ac.id_product = mp.id
                inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
                where sd.cancel = 'N' and so.cancel_h = 'N'
                ),
                pck_trf_gmt as (
                select id_ppic_master_so, sum(qty) qty_trf from packing_trf_garment
                group by id_ppic_master_so
                ),
                pck_in as (
                select id_ppic_master_so, sum(qty) qty_pck_in from packing_packing_in
                group by id_ppic_master_so
                ),
                pck_out as (
                select barcode,po,dest,count(barcode) qty_pck_out from packing_packing_out_scan
                group by barcode,po,dest
                )

                select
                p.id,
                p.id_so_det,
                gmt.buyer,
                CONCAT(DATE_FORMAT(p.tgl_shipment, '%d'), '-', LEFT(DATE_FORMAT(p.tgl_shipment, '%M'), 3), '-', DATE_FORMAT(p.tgl_shipment, '%Y')) AS tgl_shipment_fix,
                kpno ws,
                styleno,
                p.barcode,
                reff_no,
                p.desc,
                p.po,
                gmt.dest,
                product_group,
                gmt.color,
                gmt.size,
                p.qty_po,
                coalesce(pck_trf_gmt.qty_trf,0) qty_trf,
                coalesce(pck_in.qty_pck_in,0) qty_packing_in,
                coalesce(pck_out.qty_pck_out,0) qty_packing_out,
                p.created_by,
                p.created_at
                from ppic p
                inner join gmt on p.id_so_det = gmt.id_so_det
                left join pck_trf_gmt on p.id = pck_trf_gmt.id_ppic_master_so
                left join pck_in on p.id = pck_in.id_ppic_master_so
                left join pck_out on p.barcode = pck_out.barcode and p.po = pck_out.po and p.dest = pck_out.dest
                LEFT JOIN signalbit_erp.master_size_new msn ON gmt.size = msn.size
                $condition
                ORDER BY
                p.tgl_shipment DESC,
                gmt.buyer ASC,
                kpno ASC,
                gmt.dest ASC,
                gmt.color ASC,
                msn.urutan ASC,
                gmt.dest ASC;

        ");


        $this->rowCount = count($data) + 4;


        return view('ppic.export_master_so_ppic', [
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
            'A4:Q' . $event->getConcernable()->rowCount,
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

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
