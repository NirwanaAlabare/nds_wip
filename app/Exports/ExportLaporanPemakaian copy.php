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

class ExportLaporanPemakaian implements FromView, WithEvents, ShouldAutoSize
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
        $data = DB::select("SELECT
        DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
        a.no_form,
        a.no_meja,
        act_costing_ws,
        buyer,
        style,
        color,
        color_act,
        month(b.created_at) bulan,
        t.qty_order,
        b.id_roll,
        b.detail_item,
        roll roll_number,
        lot,
        cons_ws,
        cons_marker,
        cons_pipping,
        cons_ampar,
        cons_act,
        mrk.panel,
        b.qty,
        b.unit,
        sisa_kain,
        lembar_gelaran,
        mr.tot_ratio,
        concat(panjang_marker,'.',replace(comma_marker,'.','')) p_marker,
        concat(p_act,'.',replace(comma_p_act,'.','')) p_act,
        unit_p_act,
        lebar_marker,
        l_act,
        unit_l_act,
        total_pemakaian_roll,
        b.sisa_kain,
        sisa_gelaran,
        sambungan,
        est_amparan,
        average_time,
        kepala_kain,
        sisa_tidak_bisa,
        reject,
        piping,
        short_roll,
        remark,
        operator
        FROM form_cut_input a
        inner join form_cut_input_detail b on a.no_form = b.no_form_cut_input
        inner join marker_input mrk on a.id_marker = mrk.kode
        inner join (select marker_id, sum(ratio) tot_ratio from marker_input_detail group by marker_id) mr on mrk.id = mr.marker_id
        left join (select ws, sum(qty) qty_order from master_sb_ws group by ws) t on mrk.act_costing_ws = t.ws
        where b.created_at >='$this->from 00:00:00'
        and b.created_at <= '$this->to 23:59:59'");



        // select a.tgl_form_cut,
        // act_costing_ws,
        // b.detail_item,
        // mrk.color,
        // month(a.tgl_form_cut),
        // b.roll,
        // b.lot,
        // mrk.cons_marker,
        // a.cons_pipping,
        // a.cons_ampar,
        // a.cons_act,
        // mrk.panel,
        // b.unit,
        // b.lembar_gelaran,
        // mr.tot_ratio,
        // concat (mrk.panjang_marker,'.',mrk.comma_marker) panjang_marker,
        // mrk.unit_panjang_marker,
        // concat (a.p_act,'.',a.comma_p_act) panjang_act,
        // a.unit_p_act,
        // mrk.lebar_marker,
        // mrk.unit_lebar_marker,
        // a.l_act,
        // a.unit_l_act,
        // b.lembar_gelaran * mr.tot_ratio qty_potong,
        // b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) actual_gelar_kain,
        // b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) + b.sambungan + b.piping + b.sisa_tidak_bisa kain_terpakai,
        // b.sisa_kain,
        // b.sisa_tidak_bisa,
        // b.sambungan,
        // b.piping,
        // b.kepala_kain,
        // b.reject,
        // b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) + b.sambungan + b.piping + b.kepala_kain + b.sisa_kain + b.reject + b.sisa_tidak_bisa total_aktual_kain,
        // round((b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) / b.lembar_gelaran * mr.tot_ratio /100),2) cons,
        // b.created_at
        // from form_cut_input a
        // inner join form_cut_input_detail b on a.no_form = b.no_form_cut_input
        // inner join marker_input mrk on a.id_marker = mrk.kode
        // inner join (select marker_id, sum(ratio) tot_ratio from marker_input_detail group by marker_id) mr on mrk.id = mr.marker_id



        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        $this->rowCount = count($data) + 3;


        return view('lap_pemakaian.export', [
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
            'A3:AS' . $event->getConcernable()->rowCount,
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
