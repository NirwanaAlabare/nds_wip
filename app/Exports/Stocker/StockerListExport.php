<?php

namespace App\Exports\Stocker;

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

class StockerListExport implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;
    protected $lineId;
    protected $line;
    protected $tanggal_filter;
    protected $no_form_filter;
    protected $no_cut_filter;
    protected $color_filter;
    protected $size_filter;
    protected $dest_filter;
    protected $qty_filter;
    protected $year_sequence_filter;
    protected $numbering_range_filter;
    protected $buyer_filter;
    protected $ws_filter;
    protected $style_filter;
    protected $stocker_filter;
    protected $part_filter;
    protected $group_filter;
    protected $shade_filter;
    protected $ratio_filter;
    protected $stocker_range_filter;

    public function __construct($dateFrom, $dateTo, $tanggal_filter, $no_form_filter, $no_cut_filter, $color_filter, $size_filter, $dest_filter, $qty_filter, $year_sequence_filter, $numbering_range_filter, $buyer_filter, $ws_filter, $style_filter, $stocker_filter, $part_filter, $group_filter, $shade_filter, $ratio_filter, $stocker_range_filter)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
        $this->tanggal_filter = $tanggal_filter ? $tanggal_filter : null;
        $this->no_form_filter = $no_form_filter ? $no_form_filter : null;
        $this->no_cut_filter = $no_cut_filter ? $no_cut_filter : null;
        $this->color_filter = $color_filter ? $color_filter : null;
        $this->size_filter = $size_filter ? $size_filter : null;
        $this->dest_filter = $dest_filter ? $dest_filter : null;
        $this->qty_filter = $qty_filter ? $qty_filter : null;
        $this->year_sequence_filter = $year_sequence_filter ? $year_sequence_filter : null;
        $this->numbering_range_filter = $numbering_range_filter ? $numbering_range_filter : null;
        $this->buyer_filter = $buyer_filter ? $buyer_filter : null;
        $this->ws_filter = $ws_filter ? $ws_filter : null;
        $this->style_filter = $style_filter ? $style_filter : null;
        $this->stocker_filter = $stocker_filter ? $stocker_filter : null;
        $this->part_filter = $part_filter ? $part_filter : null;
        $this->group_filter = $group_filter ? $group_filter : null;
        $this->shade_filter = $shade_filter ? $shade_filter : null;
        $this->ratio_filter = $ratio_filter ? $ratio_filter : null;
        $this->stocker_range_filter = $stocker_range_filter ? $stocker_range_filter : null;
    }

    public function view(): View
    {
        $dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');

        // Convert the dates to timestamps
        $timestampFrom = strtotime($dateFrom);
        $timestampTo = strtotime($dateTo);

        // Calculate the difference in seconds
        $diffInSeconds = abs($timestampTo - $timestampFrom);

        // Convert seconds to days
        $daysInterval = $diffInSeconds / (60 * 60 * 24);

        $tanggal_filter = "";
        if ($this->tanggal_filter) {
            $tanggal_filter = "AND year_sequence_num.updated_at LIKE '%".$this->tanggal_filter."%' ";
        }
        $no_form_filter = "";
        if ($this->no_form_filter) {
            $no_form_filter = "AND COALESCE(form_cut_input.no_form, form_cut_reject.no_form) LIKE '%".$this->no_form_filter."%' ";
        }
        $no_cut_filter = "";
        if ($this->no_cut_filter) {
            $no_cut_filter = "AND form_cut_input.no_cut LIKE '%".$this->no_cut_filter."%' ";
        }
        $color_filter = "";
        if ($this->color_filter) {
            $color_filter = "AND master_sb_ws.color LIKE '%".$this->color_filter."%' ";
        }
        $size_filter = "";
        if ($this->size_filter) {
            $size_filter = "AND master_sb_ws.size LIKE '%".$this->size_filter."%' ";
        }
        $dest_filter = "";
        if ($this->dest_filter) {
            $dest_filter = "AND master_sb_ws.dest LIKE '%".$this->dest_filter."%' ";
        }
        $qty_filter = "";
        if ($this->qty_filter) {
            $qty_filter = "AND (MAX(year_sequence_num.range_akhir) - MIN(year_sequence_num.range_awal) + 1) LIKE '%".$this->qty_filter."%' ";
        }
        $year_sequence_filter = "";
        if ($this->year_sequence_filter) {
            $year_sequence_filter = "AND year_sequence_num.year_sequence LIKE '%".$this->year_sequence_filter."%' ";
        }
        $numbering_range_filter = "";
        if ($this->numbering_range_filter) {
            $numbering_range_filter = "AND CONCAT( MIN(year_sequence_num.range_awal), ' - ', MAX(year_sequence_num.range_akhir) ) LIKE '%".$this->numbering_range_filter."%' ";
        }
        $buyer_filter = "";
        if ($this->buyer_filter) {
            $buyer_filter = "AND master_sb_ws.buyer LIKE '%".$this->buyer_filter."%' ";
        }
        $ws_filter = "";
        if ($this->ws_filter) {
            $ws_filter = "AND master_sb_ws.ws LIKE '%".$this->ws_filter."%' ";
        }
        $style_filter = "";
        if ($this->style_filter) {
            $style_filter = "AND master_sb_ws.styleno LIKE '%".$this->style_filter."%' ";
        }
        $stocker_filter = "";
        if ($this->stocker_filter) {
            $stocker_filter = "AND GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker) LIKE '%".$this->stocker_filter."%' ";
        }
        $part_filter = "";
        if ($this->part_filter) {
            $part_filter = "AND GROUP_CONCAT(DISTINCT master_part.nama_part) LIKE '%".$this->part_filter."%' ";
        }
        $group_filter = "";
        if ($this->group_filter) {
            $group_filter = "AND stocker_input.group_stocker LIKE '%".$this->group_filter."%' ";
        }
        $shade_filter = "";
        if ($this->shade_filter) {
            $shade_filter = "AND stocker_input.shade LIKE '%".$this->shade_filter."%' ";
        }
        $ratio_filter = "";
        if ($this->ratio_filter) {
            $ratio_filter = "AND stocker_input.ratio LIKE '%".$this->ratio_filter."%' ";
        }
        $stocker_range_filter = "";
        if ($this->stocker_range_filter) {
            $stocker_range_filter = "AND CONCAT( MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir) ) LIKE '%".$this->stocker_range_filter."%' ";
        }

        if ($daysInterval > 3) {
            $stockerList = DB::select("
                SELECT
                    year_sequence_num.updated_at,
                    stocker_input.id_qr_stocker,
                    stocker_input.part,
                    stocker_input.form_cut_id,
                    stocker_input.act_costing_ws,
                    stocker_input.so_det_id,
                    stocker_input.buyer,
                    stocker_input.style,
                    stocker_input.color,
                    stocker_input.size,
                    stocker_input.dest,
                    stocker_input.group_stocker,
                    stocker_input.shade,
                    stocker_input.ratio,
                    stocker_input.stocker_range,
                    stocker_input.qty_stocker,
                    stocker_input.no_form,
                    stocker_input.no_cut,
                    year_sequence_num.year_sequence,
                    ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
                    CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
                    stocker_input.tipe
                FROM
                    (
                        SELECT
                            coalesce(form_cut_id, form_reject_id) form_cut_id,
                            so_det_id,
                            CONCAT( YEAR, '_', year_sequence ) year_sequence,
                            MIN( number ) range_numbering_awal,
                            MAX( number ) range_numbering_akhir,
                            MIN( year_sequence_number ) range_awal,
                            MAX( year_sequence_number ) range_akhir,
                            COALESCE ( updated_at, created_at ) updated_at,
                            (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe
                        FROM
                            year_sequence
                        WHERE
                            year_sequence.so_det_id IS NOT NULL
                            AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
                            AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
                        GROUP BY
                            form_cut_id,
                            form_reject_id,
                            (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END),
                            so_det_id,
                            COALESCE ( updated_at, created_at )
                    ) year_sequence_num
                    LEFT JOIN (
                        SELECT
                            GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
                            COALESCE(form_cut_input.id, form_cut_reject.id) form_cut_id,
                            stocker_input.act_costing_ws,
                            stocker_input.so_det_id,
                            master_sb_ws.buyer buyer,
                            master_sb_ws.styleno style,
                            master_sb_ws.color,
                            master_sb_ws.size,
                            master_sb_ws.dest,
                            stocker_input.part_detail_id,
                            stocker_input.shade,
                            stocker_input.group_stocker,
                            stocker_input.ratio,
                            stocker_input.range_awal,
                            stocker_input.range_akhir,
                            stocker_input.created_at,
                            stocker_input.updated_at,
                            COALESCE(form_cut_input.waktu_mulai, form_cut_reject.created_at) waktu_mulai,
                            COALESCE(form_cut_input.waktu_selesai, form_cut_reject.updated_at) waktu_selesai,
                            COALESCE(form_cut_input.no_form, form_cut_reject.no_form) no_form,
                            COALESCE(form_cut_input.no_cut, '-') no_cut,
                            GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
                            CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
                            ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
                            (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe
                        FROM
                            stocker_input
                            LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                            LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                            LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                        GROUP BY
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
                            (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END),
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.ratio
                    ) stocker_input ON year_sequence_num.form_cut_id = stocker_input.form_cut_id and year_sequence_num.tipe = stocker_input.tipe
                    AND year_sequence_num.so_det_id = stocker_input.so_det_id
                    AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
                    AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED)
                    WHERE
                    (
                        stocker_input.waktu_mulai >='".$dateFrom." 00:00:00'
                        OR stocker_input.waktu_selesai >= '".$dateFrom." 00:00:00'
                        OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
                        OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
                        OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
                    )
                    AND (
                        stocker_input.waktu_mulai <= '".$dateTo." 23:59:59'
                        OR stocker_input.waktu_selesai <= '".$dateTo." 23:59:59'
                        OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
                        OR stocker_input.created_at <= '".$dateTo." 23:59:59'
                        OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
                    )
                    ".$tanggal_filter."
                    ".$no_form_filter."
                    ".$no_cut_filter."
                    ".$color_filter."
                    ".$size_filter."
                    ".$dest_filter."
                    ".$year_sequence_filter."
                    ".$buyer_filter."
                    ".$ws_filter."
                    ".$style_filter."
                    ".$group_filter."
                    ".$shade_filter."
                    ".$ratio_filter."
                GROUP BY
                    stocker_input.form_cut_id,
                    stocker_input.tipe,
                    stocker_input.so_det_id,
                    year_sequence_num.updated_at
                HAVING
                    stocker_input.form_cut_id is not null
                    ".$qty_filter."
                    ".$numbering_range_filter."
                    ".$stocker_filter."
                    ".$part_filter."
                    ".$stocker_range_filter."
                ORDER BY
                    year_sequence_num.updated_at DESC
            ");
        } else {
            $stockerList = DB::select("
                SELECT
                    year_sequence_num.updated_at,
                    GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
                    GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
                    COALESCE(form_cut_input.id, form_cut_reject.id) form_cut_id,
                    stocker_input.act_costing_ws,
                    stocker_input.so_det_id,
                    master_sb_ws.buyer buyer,
                    master_sb_ws.styleno style,
                    master_sb_ws.color,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    COALESCE(form_cut_input.no_form, form_cut_reject.no_form) no_form,
                    COALESCE(form_cut_input.no_cut, '-') no_cut,
                    stocker_input.group_stocker,
                    stocker_input.shade,
                    stocker_input.ratio,
                    CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
                    ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
                    year_sequence_num.year_sequence,
                    ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
                    CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
                    (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe
                FROM
                    stocker_input
                    LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                    LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                    LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                    LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                    LEFT JOIN (
                        SELECT
                            COALESCE(form_cut_id, form_reject_id) form_cut_id,
                            so_det_id,
                            CONCAT( `year`, '_', year_sequence ) year_sequence,
                            MIN( number ) range_numbering_awal,
                            MAX( number ) range_numbering_akhir,
                            MIN( year_sequence_number ) range_awal,
                            MAX( year_sequence_number ) range_akhir,
                            COALESCE ( updated_at, created_at ) updated_at,
                            (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe
                        FROM
                            year_sequence
                        WHERE
                            year_sequence.so_det_id IS NOT NULL
                            AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
                            AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
                        GROUP BY
                            form_cut_id,
                            form_reject_id,
                            (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END),
                            so_det_id,
                            COALESCE ( updated_at, created_at )
                        ORDER BY
                            COALESCE ( updated_at, created_at)
                    ) year_sequence_num ON year_sequence_num.form_cut_id = (CASE WHEN year_sequence_num.tipe = 'REJECT' THEN stocker_input.form_reject_id ELSE stocker_input.form_cut_id END)
                    AND year_sequence_num.so_det_id = stocker_input.so_det_id
                    AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
                    AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED)
                WHERE
                    (
                        form_cut_input.waktu_mulai >= '".$dateFrom." 00:00:00'
                        OR form_cut_input.waktu_selesai >= '".$dateFrom." 00:00:00'
                        OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
                        OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
                        OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
                    )
                    AND (
                        form_cut_input.waktu_mulai <= '".$dateTo." 23:59:59'
                        OR form_cut_input.waktu_selesai <= '".$dateTo." 23:59:59'
                        OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
                        OR stocker_input.created_at <= '".$dateTo." 23:59:59'
                        OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
                    )
                    ".$tanggal_filter."
                    ".$no_form_filter."
                    ".$no_cut_filter."
                    ".$color_filter."
                    ".$size_filter."
                    ".$dest_filter."
                    ".$year_sequence_filter."
                    ".$buyer_filter."
                    ".$ws_filter."
                    ".$style_filter."
                    ".$group_filter."
                    ".$shade_filter."
                    ".$ratio_filter."
                GROUP BY
                    stocker_input.form_cut_id,
                    stocker_input.form_reject_id,
                    stocker_input.so_det_id,
                    year_sequence_num.updated_at
                HAVING
                    (stocker_input.form_cut_id is not null or stocker_input.form_reject_id is not null)
                    ".$qty_filter."
                    ".$numbering_range_filter."
                    ".$stocker_filter."
                    ".$part_filter."
                    ".$stocker_range_filter."
                ORDER BY
                    year_sequence_num.updated_at DESC
            ");
        }

        $this->rowCount = count($stockerList);

        return view("stocker.stocker.export.stocker-list-export", [
            "dateFrom" => $this->dateFrom,
            "dateTo" => $this->dateTo,
            "stockerList" => $stockerList
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
            'A3:S' . ($event->getConcernable()->rowCount+4),
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

    // public function columnFormats(): array
    // {
    //     return [
    //         'E' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }
}
