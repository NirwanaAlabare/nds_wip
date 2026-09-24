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

class ExportLaporanPackingIn implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting
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
        // $data = DB::select("
        //     select
        //     a.no_trans,
        //     concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
        //     ) tgl_penerimaan_fix,
        //     b.no_trans no_trf_garment,
        //     b.line,
        //     p.barcode,
        //     p.po,
        //     p.dest,
        //     a.qty,
        //     m.ws,
        //     m.styleno,
        //     m.color,
        //     m.size,
        //     a.created_at,
        //     a.created_by
        //     from packing_packing_in a
        //     inner join packing_trf_garment b on a.id_trf_garment = b.id
        //     inner join ppic_master_so p on a.id_ppic_master_so = p.id
        //     inner join master_sb_ws m on p.id_so_det = m.id_so_det
        //         where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to'
        //         order by a.created_at desc
        // ");

        $data = DB::select("
            select
                a.no_trans,
                concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')) tgl_penerimaan_fix,
                b.no_trans no_trf_garment,
                b.line,
                p.barcode,
                p.po,
                p.dest,
                a.qty,
                m.ws,
                m.styleno,
                m.color,
                m.size,
                a.created_at,
                a.created_by,
                act.close_order
            from packing_packing_in a
            left join packing_trf_garment b on a.id_trf_garment = b.id
            left join ppic_master_so p on a.id_ppic_master_so = p.id
            left join master_sb_ws m on p.id_so_det = m.id_so_det
            left join signalbit_erp.act_costing act on m.id_act_cost = act.id
            where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to' AND sumber IN ('Sewing')
            union
            select
                a.no_trans,
                concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
                ) tgl_penerimaan_fix,
                b.no_trans no_trf_garment,
                a.line,
                a.barcode,
                a.po,
                a.dest,
                a.qty,
                m.ws,
                m.styleno,
                m.color,
                m.size,
                a.created_at,
                a.created_by,
                act.close_order
            from packing_packing_in a
            inner join packing_trf_garment_out_temporary b on a.packing_trf_garment_out_temporary_id = b.id
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join signalbit_erp.act_costing act on m.id_act_cost = act.id
            where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to' and sumber = 'TEMPORARY PACKING' and a.line = 'TEMPORARY PACKING'
            union
            select
                a.no_trans,
                concat((DATE_FORMAT(a.tgl_penerimaan,  '%d')), '-', left(DATE_FORMAT(a.tgl_penerimaan,  '%M'),3),'-',DATE_FORMAT(a.tgl_penerimaan,  '%Y')
                ) tgl_penerimaan_fix,
                b.no_trans_out no_trf_garment,
                'FGS' line,
                a.barcode,
                a.po,
                a.dest,
                a.qty,
                m.ws,
                m.styleno,
                m.color,
                m.size,
                a.created_at,
                a.created_by,
                act.close_order
            from packing_packing_in a
            inner join fg_stok_bppb b on a.fg_stok_bppb_id = b.id
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join signalbit_erp.act_costing act on m.id_act_cost = act.id
            where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to' and sumber = 'FGS' and a.line = 'FGS'
            order by created_at desc

        ");


        $this->rowCount = count($data) + 4;


        return view('packing.export_excel_packing_in', [
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
            'A4:N' . $event->getConcernable()->rowCount,
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
