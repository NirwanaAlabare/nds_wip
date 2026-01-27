<?php

namespace App\Exports\DC;

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

class ExportDcIn implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to, $dc_filter_tipe, $dc_filter_buyer, $dc_filter_ws, $dc_filter_style, $dc_filter_color, $dc_filter_part, $dc_filter_size, $dc_filter_no_cut, $dc_filter_tujuan, $dc_filter_tempat, $dc_filter_lokasi)
    {
        $this->from = $from ? $from : date('Y-m-d');
        $this->to = $to ? $to : date('Y-m-d');

        $this->dc_filter_tipe = $dc_filter_tipe && count($dc_filter_tipe) > 0 ? $dc_filter_tipe : null;
        $this->dc_filter_buyer = $dc_filter_buyer && count($dc_filter_buyer) > 0 ? $dc_filter_buyer : null;
        $this->dc_filter_ws = $dc_filter_ws && count($dc_filter_ws) > 0 ? $dc_filter_ws : null;
        $this->dc_filter_style = $dc_filter_style && count($dc_filter_style) > 0 ? $dc_filter_style : null;
        $this->dc_filter_color = $dc_filter_color && count($dc_filter_color) > 0 ? $dc_filter_color : null;
        $this->dc_filter_part = $dc_filter_part && count($dc_filter_part) > 0 ? $dc_filter_part : null;
        $this->dc_filter_size = $dc_filter_size && count($dc_filter_size) > 0 ? $dc_filter_size : null;
        $this->dc_filter_no_cut = $dc_filter_no_cut && count($dc_filter_no_cut) > 0 ? $dc_filter_no_cut : null;
        $this->dc_filter_tujuan = $dc_filter_tujuan && count($dc_filter_tujuan) > 0 ? $dc_filter_tujuan : null;
        $this->dc_filter_tempat = $dc_filter_tempat && count($dc_filter_tempat) > 0 ? $dc_filter_tempat : null;
        $this->dc_filter_lokasi = $dc_filter_lokasi && count($dc_filter_lokasi) > 0 ? $dc_filter_lokasi : null;
    }

    public function view(): View
    {
        $from = $this->from ? $this->from : date('Y-m-d');
        $to = $this->to ? $this->to : date('Y-m-d');

        $additionalQuery = '';

        if ($from) {
            $additionalQuery .= " and a.tgl_trans >= '" . $from . "' ";
        }

        if ($to) {
            $additionalQuery .= " and a.tgl_trans <= '" . $to . "' ";
        }

        if ($this->dc_filter_tipe && count($this->dc_filter_tipe) > 0) {
            $additionalQuery .= " and (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) in (".addQuotesAround(implode("\n", $this->dc_filter_tipe)).")";
        }
        if ($this->dc_filter_buyer && count($this->dc_filter_buyer) > 0) {
            $additionalQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $this->dc_filter_buyer)).")";
        }
        if ($this->dc_filter_ws && count($this->dc_filter_ws) > 0) {
            $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $this->dc_filter_ws)).")";
        }
        if ($this->dc_filter_style && count($this->dc_filter_style) > 0) {
            $additionalQuery .= " and p.style in (".addQuotesAround(implode("\n", $this->dc_filter_style)).")";
        }
        if ($this->dc_filter_color && count($this->dc_filter_color) > 0) {
            $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $this->dc_filter_color)).")";
        }
        if ($this->dc_filter_part && count($this->dc_filter_part) > 0) {
            $additionalQuery .= " and mp.nama_part in (".addQuotesAround(implode("\n", $this->dc_filter_part)).")";
        }
        if ($this->dc_filter_size && count($this->dc_filter_size) > 0) {
            $additionalQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $this->dc_filter_size)).")";
        }
        if ($this->dc_filter_no_cut && count($this->dc_filter_no_cut) > 0) {
            $additionalQuery .= " and COALESCE(f.no_cut, fp.no_cut, '-') in (".addQuotesAround(implode("\n", $this->dc_filter_no_cut)).")";
        }
        if ($this->dc_filter_tujuan && count($this->dc_filter_tujuan) > 0) {
            $additionalQuery .= " and a.tujuan in (".addQuotesAround(implode("\n", $this->dc_filter_tujuan)).")";
        }
        if ($this->dc_filter_tempat && count($this->dc_filter_tempat) > 0) {
            $additionalQuery .= " and a.tempat in (".addQuotesAround(implode("\n", $this->dc_filter_tempat)).")";
        }
        if ($this->dc_filter_lokasi && count($this->dc_filter_lokasi) > 0) {
            $additionalQuery .= " and a.lokasi in (".addQuotesAround(implode("\n", $this->dc_filter_lokasi)).")";
        }

        $data = DB::select("
            SELECT
                UPPER(a.id_qr_stocker) id_qr_stocker,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                a.qty_awal,
                a.qty_reject,
                a.qty_replace,
                CONCAT(s.range_awal, ' - ', s.range_akhir) stocker_range,
                (a.qty_awal - a.qty_reject + a.qty_replace) qty_in,
                a.tujuan,
                a.lokasi,
                a.tempat,
                a.created_at,
                a.user,
                COALESCE(f.no_cut, fp.no_cut, '-') no_cut,
                COALESCE(msb.size, s.size) size,
                mp.nama_part
            from
                dc_in_input a
                left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                left join form_cut_reject fr on fr.id = s.form_reject_id
                left join form_cut_piece fp on fp.id = s.form_piece_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on pd.part_id = p.id
                left join master_part mp on mp.id = pd.master_part_id
            where
                a.tgl_trans is not null AND
                (s.cancel IS NULL OR s.cancel != 'y')
                ".$additionalQuery."
            order by
                a.tgl_trans desc
        ");

        $this->rowCount = count($data);

        return view("dc.dc-in.export.dc-in-excel", [
            "from" => $this->from,
            "to" => $this->to,
            "data" => $data
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
            'A1:S' . ($event->getConcernable()->rowCount+2),
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
