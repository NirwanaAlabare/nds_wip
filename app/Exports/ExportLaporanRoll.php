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

    protected $from, $to, $supplier, $id_ws;

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
        $additionalQuery2 = "";

        if ($this->from) {
            $additionalQuery .= " and b.created_at >= '" . $this->from . " 00:00:00'";
            $additionalQuery1 .= " and form_cut_piping.created_at >= '" . $this->from . " 00:00:00'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at >= '" . $this->from . " 00:00:00'";
        }

        if ($this->to) {
            $additionalQuery .= " and b.created_at <= '" . $this->to . " 23:59:59'";
            $additionalQuery1 .= " and form_cut_piping.created_at <= '" . $this->to . " 23:59:59'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at <= '" . $this->to . " 23:59:59'";
        }

        if ($this->supplier) {
            $additionalQuery .= " and msb.buyer LIKE '%" . $this->supplier . "%'";
            $additionalQuery1 .= " and msb.buyer LIKE '%" . $this->supplier . "%'";
            $additionalQuery2 .= " and msb.buyer LIKE '%" . $this->supplier . "%'";
        }

        if ($this->id_ws) {
            $additionalQuery .= " and mrk.act_costing_id = " . $this->id_ws . "";
            $additionalQuery1 .= " and form_cut_piping.act_costing_id = " . $this->id_ws . "";
            $additionalQuery2 .= " and form_cut_piece.act_costing_id = " . $this->id_ws . "";
        }

        $data = DB::select("
            select * from (
                select
                    COALESCE(b.qty) qty_in,
                    a.waktu_mulai,
                    a.waktu_selesai,
                    b.id,
                    DATE_FORMAT(b.created_at, '%M') bulan,
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
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
                    COALESCE(c.qty, b.qty) qty_awal,
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
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
                    ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
                    ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
                    b.status,
                    a.operator,
                    a.tipe_form_cut,
                    b.created_at,
                    b.updated_at,
                    (CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.id = b.form_cut_id
                    left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
                    LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at and d.created_at >= '2025-01-01 00:00:00' and d.created_at <= '2025-12-31 23:59:59'
                    LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '2025-01-01 00:00:00' and e.created_at <= '2025-12-31 23:59:59'
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
                    " . $additionalQuery . "
                group by
                    b.id
                UNION ALL
                select
                    COALESCE(form_cut_piping.qty) qty_in,
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
                    COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
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
                    form_cut_piping.operator,
                    'PIPING' tipe_form_cut,
                    form_cut_piping.created_at,
                    form_cut_piping.updated_at,
                    (CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                from
                    form_cut_piping
                    LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '2025-01-01 00:00:00' and b.created_at <= '2025-12-31 23:59:59'
                    LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '2025-01-01 00:00:00' and c.created_at <= '2025-12-31 23:59:59'
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
                    left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
                where
                    scanned_item.id_item is not null
                    " . $additionalQuery1 . "
                group by
                    form_cut_piping.id
                UNION ALL
                SELECT
                    form_cut_piece_detail.qty qty_in,
                    form_cut_piece.created_at waktu_mulai,
                    form_cut_piece.updated_at waktu_selesai,
                    form_cut_piece.id,
                    DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
                    DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
                    form_cut_piece.no_form no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piece.act_costing_ws,
                    master_sb_ws.buyer,
                    form_cut_piece.style,
                    form_cut_piece.color,
                    form_cut_piece.color color_act,
                    form_cut_piece.panel,
                    master_sb_ws.qty,
                    form_cut_piece.cons_ws cons_ws,
                    form_cut_piece.cons_ws cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    0 cons_piping,
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
                    form_cut_piece_detail.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    ( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
                    COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
                    form_cut_piece_detail.qty qty_roll,
                    form_cut_piece_detail.qty_unit unit_roll,
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
                    0 piping,
                    form_cut_piece_detail.qty_sisa sisa_kain,
                    form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
                    form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
                    ROUND(
                    form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
                    ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
                    form_cut_piece_detail.STATUS `status`,
                    form_cut_piece.employee_name,
                    'PCS' tipe_form_cut,
                    form_cut_piece.created_at,
                    form_cut_piece.updated_at,
                    (CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                FROM
                    form_cut_piece
                    LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                    LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
                    LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
                    LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
                WHERE
                    scanned_item.id_item IS NOT NULL
                    AND form_cut_piece_detail.STATUS = 'complete'
                    " . $additionalQuery2 . "
                GROUP BY
                    form_cut_piece_detail.id
            ) roll_consumption
            order by
                no_form_cut_input,
                id_roll asc,
                created_at asc
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
