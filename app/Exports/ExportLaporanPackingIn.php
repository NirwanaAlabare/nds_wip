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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
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

class ExportLaporanPackingIn extends DefaultValueBinder implements FromView, WithEvents, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    use Exportable;

    // Semua kolom kecuali No (A) & Qty (M), data rows start after heading row 4
    const TEXT_COLUMNS = [
        'B' => 'no_trans',
        'C' => 'tgl_penerimaan_fix',
        'D' => 'no_trf_garment',
        'E' => 'line',
        'F' => 'barcode',
        'G' => 'po',
        'H' => 'ws',
        'I' => 'styleno',
        'J' => 'color',
        'K' => 'size',
        'L' => 'dest',
        'N' => 'source',
        'O' => 'created_by',
        'P' => 'created_at',
    ];

    protected $from, $to, $data;

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
                act.close_order,
                'PACKING LINE' source
            from packing_packing_in a
            left join packing_trf_garment b on a.id_trf_garment = b.id
            left join ppic_master_so p on a.id_ppic_master_so = p.id
            left join master_sb_ws m on p.id_so_det = m.id_so_det
            left join signalbit_erp.act_costing act on m.id_act_cost = act.id
            where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to' AND sumber IN ('Sewing')
            union all
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
                act.close_order,
                'TEMPORARY PACKING' source
            from packing_packing_in a
            inner join packing_trf_garment_out_temporary b on a.packing_trf_garment_out_temporary_id = b.id
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join signalbit_erp.act_costing act on m.id_act_cost = act.id
            where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to' and sumber = 'TEMPORARY PACKING' and a.line = 'TEMPORARY PACKING'
            union all
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
                act.close_order,
                'GUDANG STOK' source
            from packing_packing_in a
            inner join fg_stok_bppb b on a.fg_stok_bppb_id = b.id
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join signalbit_erp.act_costing act on m.id_act_cost = act.id
            where a.tgl_penerimaan >= '$this->from' and a.tgl_penerimaan <= '$this->to' and sumber = 'FGS' and a.line = 'FGS'
            order by created_at desc

        ");


        $this->rowCount = count($data) + 4;
        $this->data = $data;


        return view('packing.export_excel_packing_in', [
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to
        ]);
    }

    public function bindValue(Cell $cell, $value)
    {
        if (isset(self::TEXT_COLUMNS[$cell->getColumn()]) && $cell->getRow() > 4 && $value !== null) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }



    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();

        // HTML reader collapses multiple spaces, rewrite text columns from original data
        $row = 5;
        foreach ($event->getConcernable()->data ?? [] as $item) {
            foreach (self::TEXT_COLUMNS as $col => $field) {
                if ($item->$field !== null) {
                    $sheet->setCellValueExplicit($col . $row, (string) $item->$field, DataType::TYPE_STRING);
                }
            }
            $row++;
        }

        $event->sheet->styleCells(
            'A4:P' . $event->getConcernable()->rowCount,
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
        return array_fill_keys(array_keys(self::TEXT_COLUMNS), NumberFormat::FORMAT_TEXT);
    }
}
