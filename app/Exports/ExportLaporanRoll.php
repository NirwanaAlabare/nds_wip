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

    public function __construct($from, $to, $supplier, $id_ws)
    {
        $this->from = $from;
        $this->to = $to;
        $this->supplier = $supplier;
        $this->id_ws = $id_ws;

        $this->rowCount = 0;
    }

    public function view(): View
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        $additionalQuery = "";
        $additionalQuery1 = "";

        if ($this->from) {
            $additionalQuery .= " and b.created_at >= '" . $this->from . " 00:00:00'";
            $additionalQuery1 .= " and form_cut_piping.created_at >= '" . $this->from . " 00:00:00'";
        }

        if ($this->to) {
            $additionalQuery .= " and b.created_at <= '" . $this->to . " 23:59:59'";
            $additionalQuery1 .= " and form_cut_piping.created_at <= '" . $this->to . " 23:59:59'";
        }

        if ($this->supplier) {
            $additionalQuery .= " and msb.buyer LIKE '%" . $this->supplier . "%'";
            $additionalQuery1 .= " and msb.buyer LIKE '%" . $this->supplier . "%'";
        }

        if ($this->id_ws) {
            $additionalQuery .= " and mrk.act_costing_id = " . $this->id_ws . "";
            $additionalQuery1 .= " and form_cut_piping.act_costing_id = " . $this->id_ws . "";
        }

        $data = DB::select("
            select * from (
                select
                    COALESCE(scanned_item.qty_in, b.qty) qty_in,
                    a.waktu_mulai,
                    a.waktu_selesai,
                    b.id,
                    DATE_FORMAT(b.updated_at, '%M') bulan,
                    DATE_FORMAT(b.updated_at, '%d-%m-%Y') tgl_input,
                    b.no_form_cut_input,
                    UPPER(meja.name) nama_meja,
                    mrk.act_costing_ws,
                    master_sb_ws.buyer,
                    mrk.style,
                    mrk.color,
                    COALESCE(b.color_act, '-') color_act,
                    mrk.panel,
                    master_sb_ws.qty,
                    cons_ws,
                    cons_marker,
                    a.cons_ampar,
                    a.cons_act,
                    (CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
                    panjang_marker,
                    unit_panjang_marker,
                    comma_marker,
                    unit_comma_marker,
                    lebar_marker,
                    unit_lebar_marker,
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
                    COALESCE(b.group_roll, '-') group_roll,
                    (
                        CASE WHEN
                            b.status != 'extension' AND b.status != 'extension complete'
                        THEN
                            (CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
                        ELSE
                            'Sambungan'
                        END
                    ) status_roll,
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
                    ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
                    b.pemakaian_lembar,
                    b.total_pemakaian_roll,
                    ROUND((SUM(b.total_pemakaian_roll) + MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END)) - MAX(b.qty), 2) short_roll,
                    ROUND((((SUM(b.total_pemakaian_roll) + MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END)) - MAX(b.qty))/(SUM(b.total_pemakaian_roll) + MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END)) * 100), 2) short_roll_percentage,
                    b.status,
                    a.operator
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.id = b.form_cut_id
                    left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
                    left join users meja on meja.id = a.no_meja
                    left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
                    left join scanned_item on scanned_item.id_roll = b.id_roll
                where
                    (a.cancel = 'N'  OR a.cancel IS NULL)
                    AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
                    AND a.status = 'SELESAI PENGERJAAN'
                    and b.status != 'not complete'
                    and b.id_item is not null
                    AND a.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH)
                    AND b.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)
                    and b.id_roll in (
                        'F141545',
                        'F141546',
                        'F141547',
                        'F141549',
                        'F144038',
                        'F144035',
                        'F144034',
                        'F144033',
                        'F144032',
                        'F144031',
                        'F144037',
                        'F144036',
                        'F144029',
                        'F144040',
                        'F144042',
                        'F144039',
                        'F144041',
                        'F144030',
                        'F144046',
                        'F144045',
                        'F144044',
                        'F144043',
                        'F142715',
                        'F142714',
                        'F142712',
                        'F142711',
                        'F141306',
                        'F141298',
                        'F141296',
                        'F141260',
                        'F141181',
                        'F146855',
                        'F146854',
                        'F146992',
                        'F148378',
                        'F148379',
                        'F148380',
                        'F148382',
                        'F148351',
                        'F102043',
                        'F144808',
                        'F144814',
                        'F144821',
                        'F144822',
                        'F144823',
                        'F144824',
                        'F144825',
                        'F144826',
                        'F144827',
                        'F144830',
                        'F144831',
                        'F144813',
                        'F144812',
                        'F144828',
                        'F144807',
                        'F144789',
                        'F144803',
                        'F144787',
                        'F144790',
                        'F144791',
                        'F144799',
                        'F144800',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F148336',
                        'F148311',
                        'F148314',
                        'F148322',
                        'F131858',
                        'F131838',
                        'F131834',
                        'F131833',
                        'F131842',
                        'F131845',
                        'F131857',
                        'F131869',
                        'F131878',
                        'F131882',
                        'F131804',
                        'F131816',
                        'F130786',
                        'F148452',
                        'F148467',
                        'F148457',
                        'F148454',
                        'F148447',
                        'F134394',
                        'F134366',
                        'F134337',
                        'F140238',
                        'F140220',
                        'F140213',
                        'F140207',
                        'F140206',
                        'F140223',
                        'F140242',
                        'F140243',
                        'F146117',
                        'F149365',
                        'F148965',
                        'F142744',
                        'F142746',
                        'F148429',
                        'F147318',
                        'F147317',
                        'F147314',
                        'F146901',
                        'F146900',
                        'F146898',
                        'F146897',
                        'F146896',
                        'F146895',
                        'F146894',
                        'F146893',
                        'F146890',
                        'F146899',
                        'F146892',
                        'F146891',
                        'F150556',
                        'F150555',
                        'F150554',
                        'F150552',
                        'F150551',
                        'F150550',
                        'F150549',
                        'F150548',
                        'F150547',
                        'F150546',
                        'F150545',
                        'F150553',
                        'F147623',
                        'F147617',
                        'F151034',
                        'F149929',
                        'F149931',
                        'F149926',
                        'F149927',
                        'F149928',
                        'F150067',
                        'F150065',
                        'F150063',
                        'F150039',
                        'F150032',
                        'F149942',
                        'F149939',
                        'F149936',
                        'F149933',
                        'F149932',
                        'F149930',
                        'F149890',
                        'F149897'
                    )
                    ".$additionalQuery."
                group by
                    b.id
                union
                select
                    COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_in,
                    form_cut_piping.created_at waktu_mulai,
                    form_cut_piping.updated_at waktu_selesai,
                    form_cut_piping.id,
                    DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
                    DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
                    'PIPING' no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piping.act_costing_ws,
                    master_sb_ws.buyer,
                    form_cut_piping.style,
                    form_cut_piping.color,
                    form_cut_piping.color color_act,
                    form_cut_piping.panel,
                    master_sb_ws.qty,
                    '0' cons_ws,
                    0 cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    form_cut_piping.cons_piping cons_piping,
                    0 panjang_marker,
                    '-' unit_panjang_marker,
                    0 comma_marker,
                    '-' unit_comma_marker,
                    0 lebar_marker,
                    '-' unit_lebar_marker,
                    0 panjang_actual,
                    '-' unit_panjang_actual,
                    0 comma_actual,
                    '-' unit_comma_actual,
                    0 lebar_actual,
                    '-' unit_lebar_actual,
                    form_cut_piping.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    'Piping' status_roll,
                    form_cut_piping.qty qty_roll,
                    form_cut_piping.unit unit_roll,
                    0 berat_amparan,
                    0 est_amparan,
                    0 lembar_gelaran,
                    0 total_ratio,
                    0 qty_cut,
                    '00:00' average_time,
                    '0' sisa_gelaran,
                    0 sambungan,
                    0 sambungan_roll,
                    0 kepala_kain,
                    0 sisa_tidak_bisa,
                    0 reject,
                    form_cut_piping.piping piping,
                    form_cut_piping.qty_sisa sisa_kain,
                    form_cut_piping.piping pemakaian_lembar,
                    form_cut_piping.piping total_pemakaian_roll,
                    ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
                    ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
                    null `status`,
                    form_cut_piping.operator
                from
                    form_cut_piping
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
                    left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
                where
                    id_item is not null and
                    form_cut_piping.id_roll in (
                        'F141545',
                        'F141546',
                        'F141547',
                        'F141549',
                        'F144038',
                        'F144035',
                        'F144034',
                        'F144033',
                        'F144032',
                        'F144031',
                        'F144037',
                        'F144036',
                        'F144029',
                        'F144040',
                        'F144042',
                        'F144039',
                        'F144041',
                        'F144030',
                        'F144046',
                        'F144045',
                        'F144044',
                        'F144043',
                        'F142715',
                        'F142714',
                        'F142712',
                        'F142711',
                        'F141306',
                        'F141298',
                        'F141296',
                        'F141260',
                        'F141181',
                        'F146855',
                        'F146854',
                        'F146992',
                        'F148378',
                        'F148379',
                        'F148380',
                        'F148382',
                        'F148351',
                        'F102043',
                        'F144808',
                        'F144814',
                        'F144821',
                        'F144822',
                        'F144823',
                        'F144824',
                        'F144825',
                        'F144826',
                        'F144827',
                        'F144830',
                        'F144831',
                        'F144813',
                        'F144812',
                        'F144828',
                        'F144807',
                        'F144789',
                        'F144803',
                        'F144787',
                        'F144790',
                        'F144791',
                        'F144799',
                        'F144800',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F144802',
                        'F148336',
                        'F148311',
                        'F148314',
                        'F148322',
                        'F131858',
                        'F131838',
                        'F131834',
                        'F131833',
                        'F131842',
                        'F131845',
                        'F131857',
                        'F131869',
                        'F131878',
                        'F131882',
                        'F131804',
                        'F131816',
                        'F130786',
                        'F148452',
                        'F148467',
                        'F148457',
                        'F148454',
                        'F148447',
                        'F134394',
                        'F134366',
                        'F134337',
                        'F140238',
                        'F140220',
                        'F140213',
                        'F140207',
                        'F140206',
                        'F140223',
                        'F140242',
                        'F140243',
                        'F146117',
                        'F149365',
                        'F148965',
                        'F142744',
                        'F142746',
                        'F148429',
                        'F147318',
                        'F147317',
                        'F147314',
                        'F146901',
                        'F146900',
                        'F146898',
                        'F146897',
                        'F146896',
                        'F146895',
                        'F146894',
                        'F146893',
                        'F146890',
                        'F146899',
                        'F146892',
                        'F146891',
                        'F150556',
                        'F150555',
                        'F150554',
                        'F150552',
                        'F150551',
                        'F150550',
                        'F150549',
                        'F150548',
                        'F150547',
                        'F150546',
                        'F150545',
                        'F150553',
                        'F147623',
                        'F147617',
                        'F151034',
                        'F149929',
                        'F149931',
                        'F149926',
                        'F149927',
                        'F149928',
                        'F150067',
                        'F150065',
                        'F150063',
                        'F150039',
                        'F150032',
                        'F149942',
                        'F149939',
                        'F149936',
                        'F149933',
                        'F149932',
                        'F149930',
                        'F149890',
                        'F149897'
                    )
                    ".$additionalQuery1."
                group by
                    form_cut_piping.id
            ) roll_consumption
            order by
                waktu_mulai asc,
                waktu_selesai asc,
                id asc
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
            'A3:BE' . $event->getConcernable()->rowCount,
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
