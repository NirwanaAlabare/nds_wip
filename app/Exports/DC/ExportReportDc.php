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

class ExportReportDc implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    protected $noWsColorSizeFilter;
    protected $noWsColorPartFilter;
    protected $noWsFilter;
    protected $buyerFilter;
    protected $styleFilter;
    protected $colorFilter;
    protected $sizeFilter;
    protected $partFilter;
    protected $saldoAwalFilter;
    protected $masukFilter;
    protected $kirimSecDalamFilter;
    protected $terimaRepairedSecDalamFilter;
    protected $terimaGoodSecDalamFilter;
    protected $kirimSecLuarFilter;
    protected $terimaRepairedSecLuarFilter;
    protected $terimaGoodSecLuarFilter;
    protected $loadingFilter;
    protected $saldoAkhirFilter;


    public function __construct(
        $from,
        $to,
        $noWsColorSizeFilter,
        $noWsColorPartFilter,
        $noWsFilter,
        $buyerFilter,
        $styleFilter,
        $colorFilter,
        $sizeFilter,
        $partFilter,
        $saldoAwalFilter,
        $masukFilter,
        $kirimSecDalamFilter,
        $terimaRepairedSecDalamFilter,
        $terimaGoodSecDalamFilter,
        $kirimSecLuarFilter,
        $terimaRepairedSecLuarFilter,
        $terimaGoodSecLuarFilter,
        $loadingFilter,
        $saldoAkhirFilter
    ) {
        $this->from = $from ?: date('Y-m-d');
        $this->to   = $to   ?: date('Y-m-d');

        $this->noWsColorSizeFilter = $noWsColorSizeFilter;
        $this->noWsColorPartFilter = $noWsColorPartFilter;
        $this->noWsFilter = $noWsFilter;
        $this->buyerFilter = $buyerFilter;
        $this->styleFilter = $styleFilter;
        $this->colorFilter = $colorFilter;
        $this->sizeFilter = $sizeFilter;
        $this->partFilter = $partFilter;
        $this->saldoAwalFilter = $saldoAwalFilter;
        $this->masukFilter = $masukFilter;
        $this->kirimSecDalamFilter = $kirimSecDalamFilter;
        $this->terimaRepairedSecDalamFilter = $terimaRepairedSecDalamFilter;
        $this->terimaGoodSecDalamFilter = $terimaGoodSecDalamFilter;
        $this->kirimSecLuarFilter = $kirimSecLuarFilter;
        $this->terimaRepairedSecLuarFilter = $terimaRepairedSecLuarFilter;
        $this->terimaGoodSecLuarFilter = $terimaGoodSecLuarFilter;
        $this->loadingFilter = $loadingFilter;
        $this->saldoAkhirFilter = $saldoAkhirFilter;
    }


    public function view(): View
    {

        $generalFilter = "";
        if (
            $this->noWsColorSizeFilter ||
            $this->noWsColorPartFilter ||
            $this->noWsFilter ||
            $this->buyerFilter ||
            $this->styleFilter ||
            $this->colorFilter ||
            $this->sizeFilter ||
            $this->partFilter ||
            $this->saldoAwalFilter ||
            $this->masukFilter ||
            $this->kirimSecDalamFilter ||
            $this->terimaRepairedSecDalamFilter ||
            $this->terimaGoodSecDalamFilter ||
            $this->kirimSecLuarFilter ||
            $this->terimaRepairedSecLuarFilter ||
            $this->terimaGoodSecLuarFilter ||
            $this->loadingFilter ||
            $this->saldoAkhirFilter
        ) {
            $generalFilter .= " WHERE ( loading_line_plan.id IS NOT NULL ";
            $generalFilter .= " )";
        }
        // GROUP_CONCAT( nama_part ) as nama_part,
        // CONCAT_WS(' ', act_costing_ws, color, GROUP_CONCAT(nama_part)) AS ws_color_part,


         $dataReport = collect(
            DB::select("
                            SELECT
                                GROUP_CONCAT( id_qr_stocker ) as stockers,
                                buyer,
                                act_costing_ws,
                                color,
                                size,
                                style,
                                so_det_id,
                                panel,
                                panel_status,
                                nama_part,
                                GROUP_CONCAT( part_status ) as part_status,
                                CONCAT_WS(' ', act_costing_ws, color, size) AS ws_color_size,
                                CONCAT_WS(' ', act_costing_ws, color, nama_part) AS ws_color_part,
                            CASE

                                    WHEN panel_status = 'main' THEN
                                    COALESCE ( qty_in_main, qty_in ) ELSE MIN( qty_in )
                                END as qty_in,
                                kirim_secondary_dalam,
                                terima_repaired_secondary_dalam,
                                terima_good_secondary_dalam,
                                terima_repaired_secondary_dalam,
                                terima_good_secondary_dalam
                            FROM
                                (
                                SELECT
                                    UPPER( a.id_qr_stocker ) id_qr_stocker,
                                    DATE_FORMAT( a.tgl_trans, '%d-%m-%Y' ) tgl_trans_fix,
                                    a.tgl_trans,
                                    s.act_costing_ws,
                                    s.color,
                                    p.buyer,
                                    p.style,
                                    p.panel,
                                    p.id part_id,
                                    p.panel_status,
                                    s.so_det_id,
                                    s.ratio,
                                    a.qty_awal,
                                    a.qty_reject,
                                    a.qty_replace,
                                    CONCAT( s.range_awal, ' - ', s.range_akhir ) stocker_range,
                                    ( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_main,
                                    null qty_in,
                                    COALESCE ( sii_in.qty_in, 0 ) as kirim_secondary_dalam,
                                    COALESCE ( mx.qty_replace, sii.qty_replace, 0 ) as terima_repaired_secondary_dalam,
                                    COALESCE ( mx.qty_akhir, sii.qty_in, 0 ) as terima_good_secondary_dalam,
                                    a.tujuan,
                                    a.lokasi,
                                    a.tempat,
                                    a.created_at,
                                    a.user,
                                    COALESCE ( f.no_cut, fp.no_cut, '-' ) no_cut,
                                    COALESCE ( msb.size, s.size ) size,
                                    mp.nama_part,
                                    pd.id as part_detail_id,
                                    pd.part_status
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
                                    left join secondary_inhouse_in_input sii_in on sii_in.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN secondary_inhouse_input sii on sii.id_qr_stocker = a.id_qr_stocker
                                    left join wip_out_det wod on wod.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN secondary_in_input si on si.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN (
                                    SELECT
                                        secondary_in_input.id_qr_stocker,
                                        MAX( qty_awal ) as qty_awal,
                                        SUM( qty_reject ) qty_reject,
                                        SUM( qty_replace ) qty_replace,
                                        (
                                        MAX( qty_awal ) - SUM( qty_reject ) + SUM( qty_replace )) as qty_akhir,
                                        MAX( secondary_in_input.urutan ) AS max_urutan,
                                        GROUP_CONCAT( master_secondary.tujuan SEPARATOR ' | ' ) as tujuan,
                                        GROUP_CONCAT( master_secondary.proses SEPARATOR ' | ' ) as proses
                                    FROM
                                        secondary_in_input
                                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                                        and part_detail_secondary.urutan = secondary_in_input.urutan
                                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                                    where
                                        secondary_in_input.tgl_trans between '".$this->from."' AND '".$this->from."'
                                    GROUP BY
                                        id_qr_stocker
                                    having
                                        MAX( secondary_in_input.urutan ) is not null
                                    ) mx ON a.id_qr_stocker = mx.id_qr_stocker
                                where
                                    a.tgl_trans between '".$this->from."' AND '".$this->from."'
                                    AND s.id is not null
                                    AND ( s.cancel IS NULL OR s.cancel != 'y' )
                                    and pd.part_status = 'main' UNION ALL
                                SELECT
                                    UPPER( a.id_qr_stocker ) id_qr_stocker,
                                    DATE_FORMAT( a.tgl_trans, '%d-%m-%Y' ) tgl_trans_fix,
                                    a.tgl_trans,
                                    s.act_costing_ws,
                                    s.color,
                                CASE

                                        WHEN pd.part_status = 'complement' THEN
                                        pcom.buyer ELSE p.buyer
                                    END as buyer,
                                CASE

                                        WHEN pd.part_status = 'complement' THEN
                                        pcom.style ELSE p.style
                                    END as style,
                                CASE

                                        WHEN pd.part_status = 'complement' THEN
                                        pcom.panel ELSE p.panel
                                    END as panel,
                                CASE

                                        WHEN pd.part_status = 'complement' THEN
                                        pcom.id ELSE p.id
                                    END as part_id,
                                CASE

                                        WHEN pd.part_status = 'complement' THEN
                                        pcom.panel_status ELSE p.panel_status
                                    END as panel_status,
                                    s.so_det_id,
                                    s.ratio,
                                    a.qty_awal,
                                    a.qty_reject,
                                    a.qty_replace,
                                    CONCAT( s.range_awal, ' - ', s.range_akhir ) stocker_range,
                                    null qty_in_main,
                                    ( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in,
                                    COALESCE ( sii_in.qty_in, 0 ) as kirim_secondary_dalam,
                                    COALESCE ( mx.qty_replace, sii.qty_replace, 0 ) as terima_repaired_secondary_dalam,
                                    COALESCE ( mx.qty_akhir, sii.qty_in, 0 ) as terima_good_secondary_dalam,
                                    a.tujuan,
                                    a.lokasi,
                                    a.tempat,
                                    a.created_at,
                                    a.user,
                                    COALESCE ( f.no_cut, fp.no_cut, '-' ) no_cut,
                                    COALESCE ( msb.size, s.size ) size,
                                    mp.nama_part,
                                    pd.id as part_detail_id,
                                    pd.part_status
                                from
                                    dc_in_input a
                                    left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                    left join form_cut_input f on f.id = s.form_cut_id
                                    left join form_cut_reject fr on fr.id = s.form_reject_id
                                    left join form_cut_piece fp on fp.id = s.form_piece_id
                                    left join part_detail pd on s.part_detail_id = pd.id
                                    left join part p on pd.part_id = p.id
                                    left join part_detail pdcom on pdcom.id = pd.from_part_detail
                                    left join part pcom on pcom.id = pdcom.part_id
                                    left join master_part mp on mp.id = pd.master_part_id
                                    left join secondary_inhouse_in_input sii_in on sii_in.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN secondary_inhouse_input sii on sii.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN (
                                    SELECT
                                        secondary_inhouse_input.id_qr_stocker,
                                        MAX( qty_awal ) as qty_awal,
                                        SUM( qty_reject ) qty_reject,
                                        SUM( qty_replace ) qty_replace,
                                        (
                                        MAX( qty_awal ) - SUM( qty_reject ) + SUM( qty_replace )) as qty_akhir,
                                        MAX( secondary_inhouse_input.urutan ) AS max_urutan,
                                        GROUP_CONCAT( master_secondary.tujuan SEPARATOR ' | ' ) as tujuan,
                                        GROUP_CONCAT( master_secondary.proses SEPARATOR ' | ' ) as proses
                                    FROM
                                        secondary_inhouse_input
                                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                                        and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                                    where
                                        secondary_inhouse_input.tgl_trans between '".$this->from."' AND '".$this->from."'
                                    GROUP BY
                                        id_qr_stocker
                                    having
                                        MAX( secondary_inhouse_input.urutan ) is not null
                                    ) mx ON a.id_qr_stocker = mx.id_qr_stocker
                                    left join wip_out_det wod on wod.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN secondary_in_input si on si.id_qr_stocker = a.id_qr_stocker
                                    LEFT JOIN (
                                    SELECT
                                        secondary_in_input.id_qr_stocker,
                                        MAX( qty_awal ) as qty_awal,
                                        SUM( qty_reject ) qty_reject,
                                        SUM( qty_replace ) qty_replace,
                                        (
                                        MAX( qty_awal ) - SUM( qty_reject ) + SUM( qty_replace )) as qty_akhir,
                                        MAX( secondary_in_input.urutan ) AS max_urutan,
                                        GROUP_CONCAT( master_secondary.tujuan SEPARATOR ' | ' ) as tujuan,
                                        GROUP_CONCAT( master_secondary.proses SEPARATOR ' | ' ) as proses
                                    FROM
                                        secondary_in_input
                                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                                        and part_detail_secondary.urutan = secondary_in_input.urutan
                                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                                    where
                                        secondary_in_input.tgl_trans between '".$this->from."' AND '".$this->from."'

                                    GROUP BY
                                        id_qr_stocker
                                    having
                                        MAX( secondary_in_input.urutan ) is not null
                                    ) mxin ON a.id_qr_stocker = mxin.id_qr_stocker
                                where
                                    a.tgl_trans between '".$this->from."' AND '".$this->from."'
                                    AND s.id is not null
                                    AND ( s.cancel IS NULL OR s.cancel != 'y' )
                                    and ( pd.part_status != 'main' OR pd.part_status IS NULL )
                                ) dc
                            group by
                                dc.so_det_id,
                                dc.part_detail_id
            ")
        );

        $this->rowCount = count($dataReport) + 2;

        return view("dc.report.export.report-dc", [
            "from" => $this->from,
            "to" => $this->to,
            "dataReport" => $dataReport,
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
            'A1:T' . ($event->getConcernable()->rowCount+2),
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
