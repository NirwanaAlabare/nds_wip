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
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class ExportLaporanRoll implements FromView, WithEvents, WithColumnWidths, ShouldAutoSize
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
        $additionalQuery = "";

        if ($this->from) {
            $additionalQuery .= " and DATE(b.created_at) >= '" . $this->from . "'";
        }

        if ($this->to) {
            $additionalQuery .= " and DATE(b.created_at) <= '" . $this->to . "'";
        }

        $data = DB::select("
            select
                DATE_FORMAT(b.updated_at, '%M') bulan,
                DATE_FORMAT(b.updated_at, '%d-%m-%Y') tgl_input,
                b.no_form_cut_input,
                UPPER(meja.name) nama_meja,
                mrk.act_costing_ws,
                mrk.buyer,
                mrk.style,
                mrk.color,
                COALESCE(b.color_act, '-') color_act,
                mrk.panel,
                master_sb_ws.qty,
                cons_ws,
                cons_marker,
                a.cons_ampar,
                a.cons_act,
                COALESCE(a.cons_pipping, cons_piping) cons_piping,
                panjang_marker,
                unit_panjang_marker,
                comma_marker,
                unit_comma_marker,
                a.p_act panjang_actual,
                a.unit_p_act unit_panjang_actual,
                a.comma_p_act comma_actual,
                a.unit_comma_p_act unit_comma_actual,
                a.l_act lebar_actual,
                a.unit_l_act unit_lebar_actual,
                COALESCE(b.id_roll, '-') id_roll,
                b.id_item,
                b.detail_item,
                COALESCE(b.roll_buyer, b.roll) roll,
                COALESCE(b.lot, '-') lot,
                b.qty qty_roll,
                b.unit unit_roll,
                COALESCE(b.berat_amparan, '-') berat_amparan,
                b.est_amparan,
                b.lembar_gelaran,
                mrk.total_ratio,
                (mrk.total_ratio * b.lembar_gelaran) qty_cut,
                b.average_time,
                b.sisa_gelaran,
                b.sambungan,
                b.sambungan_roll,
                b.kepala_kain,
                b.sisa_tidak_bisa,
                b.reject,
                b.piping,
                COALESCE(b.sisa_kain, 0) sisa_kain,
                b.pemakaian_lembar,
                b.total_pemakaian_roll,
                ROUND((CASE WHEN MAX(before_roll.qty) > 0 THEN (SUM(before_roll.total_pemakaian_roll)+b.total_pemakaian_roll) - MAX(before_roll.qty) ELSE b.short_roll END), 2) short_roll,
                ROUND((CASE WHEN MAX(before_roll.qty) > 0 THEN ((SUM(before_roll.total_pemakaian_roll)+b.total_pemakaian_roll) - MAX(before_roll.qty))/MAX(before_roll.qty) ELSE (b.short_roll / b.qty) END), 2) short_roll_percentage,
                b.status,
                a.operator
            from
                form_cut_input a
                left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
                left join users meja on meja.id = a.no_meja
                left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
                left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
                left join (SELECT * FROM form_cut_input_detail WHERE id_roll IS NOT NULL GROUP BY id) before_roll ON b.id_roll = before_roll.id_roll AND b.id > before_roll.id
            where
                (a.cancel = 'N'  OR a.cancel IS NULL)
                AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
                and b.id_item is not null
                " . $additionalQuery . "
            group by
                b.id
            order by
                a.waktu_mulai asc,
                a.waktu_selesai asc,
                b.id asc
        ");

        $this->rowCount = count($data) + 3;

        return view('cutting.roll.export.roll', [
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
            'A3:BA' . $event->getConcernable()->rowCount,
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

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'G' => 25,
        ];
    }
}
