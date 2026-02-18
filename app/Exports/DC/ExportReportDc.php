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

        // $generalFilter = "";
        // if (
        //     $this->noWsColorSizeFilter ||
        //     $this->noWsColorPartFilter ||
        //     $this->noWsFilter ||
        //     $this->buyerFilter ||
        //     $this->styleFilter ||
        //     $this->colorFilter ||
        //     $this->sizeFilter ||
        //     $this->partFilter ||
        //     $this->saldoAwalFilter ||
        //     $this->masukFilter ||
        //     $this->kirimSecDalamFilter ||
        //     $this->terimaRepairedSecDalamFilter ||
        //     $this->terimaGoodSecDalamFilter ||
        //     $this->kirimSecLuarFilter ||
        //     $this->terimaRepairedSecLuarFilter ||
        //     $this->terimaGoodSecLuarFilter ||
        //     $this->loadingFilter ||
        //     $this->saldoAkhirFilter
        // ) {
        //     $generalFilter .= " WHERE ( loading_line_plan.id IS NOT NULL ";
        //     $generalFilter .= " )";
        // }
        // GROUP_CONCAT( nama_part ) as nama_part,
        // CONCAT_WS(' ', act_costing_ws, color, GROUP_CONCAT(nama_part)) AS ws_color_part,


        //  $dataReport = collect(
        //     DB::select("
        //                     SELECT
        //                         GROUP_CONCAT( id_qr_stocker ) as stockers,
        //                         buyer,
        //                         act_costing_ws,
        //                         color,
        //                         size,
        //                         style,
        //                         so_det_id,
        //                         panel,
        //                         panel_status,
        //                         nama_part,
        //                         GROUP_CONCAT( part_status ) as part_status,
        //                         CONCAT_WS(' ', act_costing_ws, color, size) AS ws_color_size,
        //                         CONCAT_WS(' ', act_costing_ws, color, nama_part) AS ws_color_part,
        //                     CASE

        //                             WHEN part_status = 'main' THEN
        //                             COALESCE ( qty_in_main, qty_in ) ELSE MIN( qty_in )
        //                         END as qty_in,
        //                         kirim_secondary_dalam,
        //                         terima_repaired_secondary_dalam,
        //                         terima_good_secondary_dalam,
        //                         terima_repaired_secondary_dalam,
        //                         terima_good_secondary_dalam
        //                     FROM
        //                         (
        //                         SELECT
        //                             UPPER( a.id_qr_stocker ) id_qr_stocker,
        //                             DATE_FORMAT( a.tgl_trans, '%d-%m-%Y' ) tgl_trans_fix,
        //                             a.tgl_trans,
        //                             s.act_costing_ws,
        //                             s.color,
        //                             p.buyer,
        //                             p.style,
        //                             p.panel,
        //                             p.id part_id,
        //                             p.panel_status,
        //                             s.so_det_id,
        //                             s.ratio,
        //                             a.qty_awal,
        //                             a.qty_reject,
        //                             a.qty_replace,
        //                             CONCAT( s.range_awal, ' - ', s.range_akhir ) stocker_range,
        //                             ( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_main,
        //                             null qty_in,
        //                             COALESCE ( sii_in.qty_in, 0 ) as kirim_secondary_dalam,
        //                             COALESCE ( mx.qty_replace, sii.qty_replace, 0 ) as terima_repaired_secondary_dalam,
        //                             COALESCE ( mx.qty_akhir, sii.qty_in, 0 ) as terima_good_secondary_dalam,
        //                             a.tujuan,
        //                             a.lokasi,
        //                             a.tempat,
        //                             a.created_at,
        //                             a.user,
        //                             COALESCE ( f.no_cut, fp.no_cut, '-' ) no_cut,
        //                             COALESCE ( msb.size, s.size ) size,
        //                             mp.nama_part,
        //                             pd.id as part_detail_id,
        //                             pd.part_status
        //                         from
        //                             dc_in_input a
        //                             left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
        //                             left join master_sb_ws msb on msb.id_so_det = s.so_det_id
        //                             left join form_cut_input f on f.id = s.form_cut_id
        //                             left join form_cut_reject fr on fr.id = s.form_reject_id
        //                             left join form_cut_piece fp on fp.id = s.form_piece_id
        //                             left join part_detail pd on s.part_detail_id = pd.id
        //                             left join part p on pd.part_id = p.id
        //                             left join master_part mp on mp.id = pd.master_part_id
        //                             left join secondary_inhouse_in_input sii_in on sii_in.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN secondary_inhouse_input sii on sii.id_qr_stocker = a.id_qr_stocker
        //                             left join wip_out_det wod on wod.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN secondary_in_input si on si.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN (
        //                             SELECT
        //                                 secondary_in_input.id_qr_stocker,
        //                                 MAX( qty_awal ) as qty_awal,
        //                                 SUM( qty_reject ) qty_reject,
        //                                 SUM( qty_replace ) qty_replace,
        //                                 (
        //                                 MAX( qty_awal ) - SUM( qty_reject ) + SUM( qty_replace )) as qty_akhir,
        //                                 MAX( secondary_in_input.urutan ) AS max_urutan,
        //                                 GROUP_CONCAT( master_secondary.tujuan SEPARATOR ' | ' ) as tujuan,
        //                                 GROUP_CONCAT( master_secondary.proses SEPARATOR ' | ' ) as proses
        //                             FROM
        //                                 secondary_in_input
        //                                 LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
        //                                 LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
        //                                 and part_detail_secondary.urutan = secondary_in_input.urutan
        //                                 LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
        //                             where
        //                                 secondary_in_input.tgl_trans between '".$this->from."' AND '".$this->from."'
        //                             GROUP BY
        //                                 id_qr_stocker
        //                             having
        //                                 MAX( secondary_in_input.urutan ) is not null
        //                             ) mx ON a.id_qr_stocker = mx.id_qr_stocker
        //                         where
        //                             a.tgl_trans between '".$this->from."' AND '".$this->from."'
        //                             AND s.id is not null
        //                             AND ( s.cancel IS NULL OR s.cancel != 'y' )
        //                             and pd.part_status = 'main' UNION ALL
        //                         SELECT
        //                             UPPER( a.id_qr_stocker ) id_qr_stocker,
        //                             DATE_FORMAT( a.tgl_trans, '%d-%m-%Y' ) tgl_trans_fix,
        //                             a.tgl_trans,
        //                             s.act_costing_ws,
        //                             s.color,
        //                         CASE

        //                                 WHEN pd.part_status = 'complement' THEN
        //                                 pcom.buyer ELSE p.buyer
        //                             END as buyer,
        //                         CASE

        //                                 WHEN pd.part_status = 'complement' THEN
        //                                 pcom.style ELSE p.style
        //                             END as style,
        //                         CASE

        //                                 WHEN pd.part_status = 'complement' THEN
        //                                 pcom.panel ELSE p.panel
        //                             END as panel,
        //                         CASE

        //                                 WHEN pd.part_status = 'complement' THEN
        //                                 pcom.id ELSE p.id
        //                             END as part_id,
        //                         CASE

        //                                 WHEN pd.part_status = 'complement' THEN
        //                                 pcom.panel_status ELSE p.panel_status
        //                             END as panel_status,
        //                             s.so_det_id,
        //                             s.ratio,
        //                             a.qty_awal,
        //                             a.qty_reject,
        //                             a.qty_replace,
        //                             CONCAT( s.range_awal, ' - ', s.range_akhir ) stocker_range,
        //                             null qty_in_main,
        //                             ( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in,
        //                             COALESCE ( sii_in.qty_in, 0 ) as kirim_secondary_dalam,
        //                             COALESCE ( mx.qty_replace, sii.qty_replace, 0 ) as terima_repaired_secondary_dalam,
        //                             COALESCE ( mx.qty_akhir, sii.qty_in, 0 ) as terima_good_secondary_dalam,
        //                             a.tujuan,
        //                             a.lokasi,
        //                             a.tempat,
        //                             a.created_at,
        //                             a.user,
        //                             COALESCE ( f.no_cut, fp.no_cut, '-' ) no_cut,
        //                             COALESCE ( msb.size, s.size ) size,
        //                             mp.nama_part,
        //                             pd.id as part_detail_id,
        //                             pd.part_status
        //                         from
        //                             dc_in_input a
        //                             left join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
        //                             left join master_sb_ws msb on msb.id_so_det = s.so_det_id
        //                             left join form_cut_input f on f.id = s.form_cut_id
        //                             left join form_cut_reject fr on fr.id = s.form_reject_id
        //                             left join form_cut_piece fp on fp.id = s.form_piece_id
        //                             left join part_detail pd on s.part_detail_id = pd.id
        //                             left join part p on pd.part_id = p.id
        //                             left join part_detail pdcom on pdcom.id = pd.from_part_detail
        //                             left join part pcom on pcom.id = pdcom.part_id
        //                             left join master_part mp on mp.id = pd.master_part_id
        //                             left join secondary_inhouse_in_input sii_in on sii_in.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN secondary_inhouse_input sii on sii.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN (
        //                             SELECT
        //                                 secondary_inhouse_input.id_qr_stocker,
        //                                 MAX( qty_awal ) as qty_awal,
        //                                 SUM( qty_reject ) qty_reject,
        //                                 SUM( qty_replace ) qty_replace,
        //                                 (
        //                                 MAX( qty_awal ) - SUM( qty_reject ) + SUM( qty_replace )) as qty_akhir,
        //                                 MAX( secondary_inhouse_input.urutan ) AS max_urutan,
        //                                 GROUP_CONCAT( master_secondary.tujuan SEPARATOR ' | ' ) as tujuan,
        //                                 GROUP_CONCAT( master_secondary.proses SEPARATOR ' | ' ) as proses
        //                             FROM
        //                                 secondary_inhouse_input
        //                                 LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
        //                                 LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
        //                                 and part_detail_secondary.urutan = secondary_inhouse_input.urutan
        //                                 LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
        //                             where
        //                                 secondary_inhouse_input.tgl_trans between '".$this->from."' AND '".$this->from."'
        //                             GROUP BY
        //                                 id_qr_stocker
        //                             having
        //                                 MAX( secondary_inhouse_input.urutan ) is not null
        //                             ) mx ON a.id_qr_stocker = mx.id_qr_stocker
        //                             left join wip_out_det wod on wod.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN secondary_in_input si on si.id_qr_stocker = a.id_qr_stocker
        //                             LEFT JOIN (
        //                             SELECT
        //                                 secondary_in_input.id_qr_stocker,
        //                                 MAX( qty_awal ) as qty_awal,
        //                                 SUM( qty_reject ) qty_reject,
        //                                 SUM( qty_replace ) qty_replace,
        //                                 (
        //                                 MAX( qty_awal ) - SUM( qty_reject ) + SUM( qty_replace )) as qty_akhir,
        //                                 MAX( secondary_in_input.urutan ) AS max_urutan,
        //                                 GROUP_CONCAT( master_secondary.tujuan SEPARATOR ' | ' ) as tujuan,
        //                                 GROUP_CONCAT( master_secondary.proses SEPARATOR ' | ' ) as proses
        //                             FROM
        //                                 secondary_in_input
        //                                 LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
        //                                 LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
        //                                 and part_detail_secondary.urutan = secondary_in_input.urutan
        //                                 LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
        //                             where
        //                                 secondary_in_input.tgl_trans between '".$this->from."' AND '".$this->from."'

        //                             GROUP BY
        //                                 id_qr_stocker
        //                             having
        //                                 MAX( secondary_in_input.urutan ) is not null
        //                             ) mxin ON a.id_qr_stocker = mxin.id_qr_stocker
        //                         where
        //                             a.tgl_trans between '".$this->from."' AND '".$this->from."'
        //                             AND s.id is not null
        //                             AND ( s.cancel IS NULL OR s.cancel != 'y' )
        //                             and ( pd.part_status != 'main' OR pd.part_status IS NULL )
        //                         ) dc
        //                     group by
        //                         dc.so_det_id,
        //                         dc.part_detail_id
        //     ")
        // );

        $dateFrom = $this->from;
        $dateTo = $this->to;


        $dataReport = DB::select("
            WITH
                dc_before_saldo AS (
                    -- before saldo
				    WITH
                        dc_rekap AS (
                            SELECT
                                dc_report_rekap.*
                            FROM dc_report_rekap
                            INNER JOIN (
                                SELECT
                                    MAX(tanggal) tanggal
                                FROM
                                    dc_report_rekap
                                WHERE
                                    tanggal < '".$dateFrom."'
                            ) tanggal_akhir_rekap on tanggal_akhir_rekap.tanggal = dc_report_rekap.tanggal
                        ),
                        dc as (
                    SELECT
                        a.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
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
                        a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        a.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        pd.part_status = 'main'
                    UNION ALL
                    SELECT
                        a.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
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
                    where
                        a.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        a.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                ),

                sii_in as (
                    SELECT
                        sii_in.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        (sii_in.qty_in) sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        secondary_inhouse_in_input sii_in
                        left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                    WHERE
                        sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        sii_in.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        pd.part_status = 'main'
                    UNION ALL
                    SELECT
                        sii_in.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        (sii_in.qty_in) sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        secondary_inhouse_in_input sii_in
                        left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                    WHERE
                        sii_in.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        sii_in.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                ),

                sii as (
                    SELECT
                        sii.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        sii.qty_replace sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        sii.qty_in sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        secondary_inhouse_input sii
                        left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                    WHERE
                        sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        sii.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        pd.part_status= 'main'
                    UNION ALL
                    SELECT
                        sii.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        sii.qty_replace sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        sii.qty_in sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        secondary_inhouse_input sii
                        left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                    WHERE
                        sii.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        sii.tgl_trans < '".$dateFrom."' AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                ),

                wod as (
                    SELECT
                        wod.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        wod.qty sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        wip_out_det wod
                        left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                        left join wip_out wo on wo.id = wod.id_wip_out
                    WHERE
                        wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        wo.tgl_form < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        pd.part_status= 'main'
                    UNION ALL
                    SELECT
                        wod.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        wod.qty sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        wip_out_det wod
                        left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                        left join wip_out wo on wo.id = wod.id_wip_out
                    WHERE
                        wo.tgl_form > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        wo.tgl_form < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        (pd.part_status != 'main' OR pd.part_status IS NULL)
                    group by
                        s.id
                ),

                si as (
                    SELECT
                        si.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        si.qty_replace sec_in_rep_main,
                        null sec_in_rep,
                        si.qty_in sec_in_out_main,
                        null sec_in_out,
                        null loading_qty
                    FROM
                        secondary_in_input si
                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                        left join master_secondary ms on ms.id = pd.master_secondary_id
                        left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                        left join master_secondary mms on mms.id = pds.master_secondary_id
                        left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                    WHERE
                        si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        si.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        pd.part_status= 'main' AND
                        COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                    UNION ALL
                    SELECT
                        si.id_qr_stocker,
                        pd.id as part_detail_id,
                        s.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        si.qty_replace sec_in_rep,
                        null sec_in_out_main,
                        si.qty_in sec_in_out,
                        null loading_qty
                    FROM
                        secondary_in_input si
                        left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                        left join part_detail pd on pd.id = s.part_detail_id
                        left join master_secondary ms on ms.id = pd.master_secondary_id
                        left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                        left join master_secondary mms on mms.id = pds.master_secondary_id
                        left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                    WHERE
                        si.tgl_trans > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                        si.tgl_trans < '".$dateFrom."' AND
                        s.id is not null AND
                        (s.cancel IS NULL OR s.cancel != 'y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                        COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                ),

                loading_line_qty as (
                    select
                        stocker_input.id_qr_stocker,
                        part_detail.id as part_detail_id,
                        stocker_input.so_det_id,
                        null qty_in_dc_main,
                        null qty_in_dc,
                        null sec_inhouse_in_main,
                        null sec_inhouse_in,
                        null sec_inhouse_rep_main,
                        null sec_inhouse_rep,
                        null sec_inhouse_out_main,
                        null sec_inhouse_out,
                        null sec_in_in_main,
                        null sec_in_in,
                        null sec_in_rep_main,
                        null sec_in_rep,
                        null sec_in_out_main,
                        null sec_in_out,
                        COALESCE(loading_qty.loading_qty, loading_line.qty) loading_qty
                    from
                        loading_line
                        LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                        left join part_detail on stocker_input.part_detail_id = part_detail.id
                        left join part on part.id = part_detail.part_id
                        left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                        left join part part_com on part_com.id = part_detail_com.part_id
                        LEFT JOIN (
                            select
                                COALESCE(p_com.panel, p.panel) as panel,
                                s.form_cut_id,
                                s.form_reject_id,
                                s.form_piece_id,
                                s.so_det_id,
                                s.group_stocker,
                                s.ratio,
                                s.stocker_reject,
                                MIN(ll.qty) loading_qty
                            from
                                loading_line ll
                                left join stocker_input s on s.id = ll.stocker_id
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                left join part p on p.id = pd.part_id
                                left join part p_com on p_com.id = pd_com.part_id
                            where
                                ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                                ll.tanggal_loading < '".$dateFrom."' and
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                            group by
                                COALESCE(p_com.panel, p.panel),
                                s.form_cut_id,
                                s.form_reject_id,
                                s.form_piece_id,
                                s.so_det_id,
                                s.group_stocker,
                                s.ratio,
                                s.stocker_reject
                        ) as loading_qty on loading_qty.panel = COALESCE(part_com.panel, part.panel)
                        AND loading_qty.form_cut_id    <=> stocker_input.form_cut_id
                        AND loading_qty.form_reject_id <=> stocker_input.form_reject_id
                        AND loading_qty.form_piece_id  <=> stocker_input.form_piece_id
                        AND loading_qty.so_det_id      <=> stocker_input.so_det_id
                        AND loading_qty.group_stocker  <=> stocker_input.group_stocker
                        AND loading_qty.ratio          <=> stocker_input.ratio
                        AND loading_qty.stocker_reject <=> stocker_input.stocker_reject
                    WHERE
                        loading_line.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND loading_line.tanggal_loading < '".$dateFrom."' and
                        (stocker_input.cancel IS NULL OR stocker_input.cancel != 'y') and
                        (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%STOCKER MANUAL%')
                    group by
                        stocker_input.id
                ),

                loading_line as (
                    select
                        panel,
                        so_det_id,
                        GROUP_CONCAT(stocker_id) stockers,
                        SUM(loading_qty) loading_qty
                    from (
                        select
                            COALESCE(p_com.panel, p.panel) as panel,
                            GROUP_CONCAT(ll.stocker_id) stocker_id,
                            s.so_det_id,
                            MIN(ll.qty) loading_qty
                        from
                            loading_line ll
                            left join stocker_input s on s.id = ll.stocker_id
                            left join part_detail pd on pd.id = s.part_detail_id
                            left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                            left join part p on p.id = pd.part_id
                            left join part p_com on p_com.id = pd_com.part_id
                        where
                            ll.tanggal_loading > COALESCE((select MAX(tanggal) from dc_rekap), '2021-01-01') AND
                            ll.tanggal_loading < '".$dateFrom."' and
                            (s.cancel IS NULL OR s.cancel != 'y') and
                            (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                        group by
                            COALESCE(p_com.panel, p.panel),
                            s.form_cut_id,
                            s.form_reject_id,
                            s.form_piece_id,
                            s.so_det_id,
                            s.group_stocker,
                            s.ratio,
                            s.stocker_reject
                    ) as loading
                    group by
                        panel,
                        so_det_id
                )

                SELECT
                    tanggal,
                    stockers,
                    act_costing_ws,
                    buyer,
                    color,
                    so_det_id,
                    panel,
                    panel_status,
                    part_detail_id,
                    nama_part,
                    part_status,
                    SUM(saldo_awal) saldo_awal,
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                    SUM(loading_qty) loading_qty,
                    SUM(saldo_awal)+SUM(saldo_akhir) saldo_akhir,
                    CURRENT_TIMESTAMP() created_at,
                    CURRENT_TIMESTAMP() updated_at
                FROM (
                        SELECT
                            '2025-12-31' tanggal,
                            stockers,
                            buyer,
                            ws act_costing_ws,
                            color,
                            id_so_det so_det_id,
                            panel,
                            panel_status,
                            part_detail_id,
                            nama_part,
                            part_status,
                            0 saldo_awal,
                            qty_in,
                            kirim_secondary_dalam,
                            terima_repaired_secondary_dalam,
                            terima_good_secondary_dalam,
                            kirim_secondary_luar,
                            terima_repaired_secondary_luar,
                            terima_good_secondary_luar,
                            loading_qty,
                            qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir,
                            CURRENT_TIMESTAMP() created_at,
                            CURRENT_TIMESTAMP() updated_at
                        FROM (
                            SELECT
                                GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                msb.buyer,
                                msb.ws,
                                msb.styleno as style,
                                msb.color,
                                msb.size,
                                msb.id_so_det,
                                COALESCE(p_com.panel, p.panel) panel,
                                COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                COALESCE(pd_com.id, pd.id) as part_detail_id,
                                COALESCE(GROUP_CONCAT(DISTINCT mp_com.nama_part), GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                COALESCE(GROUP_CONCAT(DISTINCT pd_com.part_status), GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                -- loading.stockers,
                                SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                COALESCE(loading_line.loading_qty, 0) loading_qty1
                            FROM (
                                SELECT
                                    *
                                FROM
                                    dc
                                UNION ALL
                                SELECT
                                    *
                                FROM
                                    sii_in
                                UNION ALL
                                SELECT
                                    *
                                FROM
                                    sii
                                UNION ALL
                                SELECT
                                    *
                                FROM
                                    wod
                                UNION ALL
                                SELECT
                                    *
                                FROM
                                    si
                                UNION ALL
                                SELECT
                                    *
                                FROM
                                    loading_line_qty
                            ) saldo_dc
                            LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                            left join part_detail pd on pd.id = saldo_dc.part_detail_id
                            left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                            left join part p on p.id = pd.part_id
                            left join part p_com on p_com.id = pd_com.part_id
                            LEFT JOIN master_part mp on mp.id = pd.master_part_id
                            LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                            LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                            GROUP BY
                                saldo_dc.so_det_id,
                                saldo_dc.part_detail_id
                        ) saldo_dc
                        UNION ALL
                        select
                            tanggal,
                            stockers,
                            buyer,
                            act_costing_ws,
                            color,
                            so_det_id,
                            panel,
                            panel_status,
                            part_detail_id,
                            nama_part,
                            part_status,
                            saldo_akhir saldo_awal,
                            0 qty_in,
                            0 kirim_secondary_dalam,
                            0 terima_repaired_secondary_dalam,
                            0 terima_good_secondary_dalam,
                            0 kirim_secondary_luar,
                            0 terima_repaired_secondary_luar,
                            0 terima_good_secondary_luar,
                            0 loading_qty,
                            0 saldo_akhir,
                            CURRENT_TIMESTAMP() created_at,
                            CURRENT_TIMESTAMP() updated_at
                        from
                            dc_report_rekap
                        where
                            tanggal < '".$dateFrom."'
                ) saldo_dc
                group by
                    so_det_id,
                    part_detail_id
                ),
                dc_current_saldo AS (
                    -- current saldo
                    WITH
                        dc as (
                            SELECT
                                a.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
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
                                a.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                pd.part_status = 'main'
                            UNION ALL
                            SELECT
                                a.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                (a.qty_awal - a.qty_reject + a.qty_replace) qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
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
                            where
                                a.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                        ),

                        sii_in as (
                            SELECT
                                sii_in.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                (sii_in.qty_in) sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                secondary_inhouse_in_input sii_in
                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                            WHERE
                                sii_in.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                pd.part_status = 'main'
                            UNION ALL
                            SELECT
                                sii_in.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                (sii_in.qty_in) sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                secondary_inhouse_in_input sii_in
                                left join stocker_input s on s.id_qr_stocker = sii_in.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                            WHERE
                                sii_in.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                        ),

                        sii as (
                            SELECT
                                sii.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                sii.qty_replace sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                sii.qty_in sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                secondary_inhouse_input sii
                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                            WHERE
                                sii.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                pd.part_status= 'main'
                            UNION ALL
                            SELECT
                                sii.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                sii.qty_replace sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                sii.qty_in sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                secondary_inhouse_input sii
                                left join stocker_input s on s.id_qr_stocker = sii.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                            WHERE
                                sii.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                        ),

                        wod as (
                            SELECT
                                wod.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                wod.qty sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                wip_out_det wod
                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join wip_out wo on wo.id = wod.id_wip_out
                            WHERE
                                wo.tgl_form between '".$dateFrom."' AND '".$dateTo."' and
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                pd.part_status= 'main'
                            UNION ALL
                            SELECT
                                wod.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                wod.qty sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                wip_out_det wod
                                left join stocker_input s on s.id_qr_stocker = wod.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join wip_out wo on wo.id = wod.id_wip_out
                            WHERE
                                wo.tgl_form between '".$dateFrom."' AND '".$dateTo."' and
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL)
                            group by
                                s.id
                        ),

                        si as (
                            SELECT
                                si.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                si.qty_replace sec_in_rep_main,
                                null sec_in_rep,
                                si.qty_in sec_in_out_main,
                                null sec_in_out,
                                null loading_qty
                            FROM
                                secondary_in_input si
                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                            WHERE
                                si.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                pd.part_status= 'main' AND
                                COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                            UNION ALL
                            SELECT
                                si.id_qr_stocker,
                                pd.id as part_detail_id,
                                s.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                si.qty_replace sec_in_rep,
                                null sec_in_out_main,
                                si.qty_in sec_in_out,
                                null loading_qty
                            FROM
                                secondary_in_input si
                                left join stocker_input s on s.id_qr_stocker = si.id_qr_stocker
                                left join part_detail pd on pd.id = s.part_detail_id
                                left join master_secondary ms on ms.id = pd.master_secondary_id
                                left join part_detail_secondary pds on pds.part_detail_id = pd.id and si.urutan = pds.urutan
                                left join master_secondary mms on mms.id = pds.master_secondary_id
                                left join secondary_inhouse_input sii on sii.id_qr_stocker = si.id_qr_stocker
                            WHERE
                                si.tgl_trans between '".$dateFrom."' AND '".$dateTo."' AND
                                s.id is not null AND
                                (s.cancel IS NULL OR s.cancel != 'y') and
                                (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                                (pd.part_status != 'main' OR pd.part_status IS NULL) AND
                                COALESCE(mms.tujuan, ms.tujuan) = 'SECONDARY LUAR'
                        ),

                        loading_line_qty as (
                            select
                                stocker_input.id_qr_stocker,
                                part_detail.id as part_detail_id,
                                stocker_input.so_det_id,
                                null qty_in_dc_main,
                                null qty_in_dc,
                                null sec_inhouse_in_main,
                                null sec_inhouse_in,
                                null sec_inhouse_rep_main,
                                null sec_inhouse_rep,
                                null sec_inhouse_out_main,
                                null sec_inhouse_out,
                                null sec_in_in_main,
                                null sec_in_in,
                                null sec_in_rep_main,
                                null sec_in_rep,
                                null sec_in_out_main,
                                null sec_in_out,
                                COALESCE(loading_qty.loading_qty, loading_line.qty) loading_qty
                            from
                                loading_line
                                LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                                left join part_detail on stocker_input.part_detail_id = part_detail.id
                                left join part on part.id = part_detail.part_id
                                left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                                left join part part_com on part_com.id = part_detail_com.part_id
                                LEFT JOIN (
                                    select
                                        COALESCE(p_com.panel, p.panel) as panel,
                                        s.form_cut_id,
                                        s.form_reject_id,
                                        s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject,
                                        MIN(ll.qty) loading_qty
                                    from
                                        loading_line ll
                                        left join stocker_input s on s.id = ll.stocker_id
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p on p.id = pd.part_id
                                        left join part p_com on p_com.id = pd_com.part_id
                                    where
                                        ll.tanggal_loading between '".$dateFrom."' AND '".$dateTo."' AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                    group by
                                        COALESCE(p_com.panel, p.panel),
                                        s.form_cut_id,
                                        s.form_reject_id,
                                        s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject
                                ) as loading_qty on loading_qty.panel = COALESCE(part_com.panel, part.panel)
                            AND loading_qty.form_cut_id    <=> stocker_input.form_cut_id
                            AND loading_qty.form_reject_id <=> stocker_input.form_reject_id
                            AND loading_qty.form_piece_id  <=> stocker_input.form_piece_id
                            AND loading_qty.so_det_id      <=> stocker_input.so_det_id
                            AND loading_qty.group_stocker  <=> stocker_input.group_stocker
                            AND loading_qty.ratio          <=> stocker_input.ratio
                            AND loading_qty.stocker_reject <=> stocker_input.stocker_reject
                            WHERE loading_line.tanggal_loading between '".$dateFrom."' AND '".$dateTo."' and
                            (stocker_input.cancel IS NULL OR stocker_input.cancel != 'y') and
                            (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%STOCKER MANUAL%')
                            group by
                                stocker_input.id
                        ),

                        loading_line as (
                                select
                                    panel,
                                    so_det_id,
                                    GROUP_CONCAT(stocker_id) stockers,
                                    SUM(loading_qty) loading_qty
                                from (
                                    select
                                        COALESCE(p_com.panel, p.panel) as panel,
                                        GROUP_CONCAT(ll.stocker_id) stocker_id,
                                        s.so_det_id,
                                        MIN(ll.qty) loading_qty
                                    from
                                        loading_line ll
                                        left join stocker_input s on s.id = ll.stocker_id
                                        left join part_detail pd on pd.id = s.part_detail_id
                                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p on p.id = pd.part_id
                                        left join part p_com on p_com.id = pd_com.part_id
                                    where
                                        ll.tanggal_loading between '".$dateFrom."' AND '".$dateTo."' AND
                                        (s.cancel IS NULL OR s.cancel != 'y') and
                                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%')
                                    group by
                                        COALESCE(p_com.panel, p.panel),
                                        s.form_cut_id,
                                        s.form_reject_id,
                                        s.form_piece_id,
                                        s.so_det_id,
                                        s.group_stocker,
                                        s.ratio,
                                        s.stocker_reject
                                ) as loading
                            group by
                                panel,
                                so_det_id
                        )

                        SELECT
                            *,
                            qty_in-kirim_secondary_dalam+terima_repaired_secondary_dalam+terima_good_secondary_dalam-kirim_secondary_luar+terima_repaired_secondary_luar+terima_good_secondary_luar-loading_qty saldo_akhir
                        FROM (
                            SELECT
                                GROUP_CONCAT(saldo_dc.id_qr_stocker) as stockers,
                                msb.buyer,
                                msb.ws,
                                msb.styleno as style,
                                msb.color,
                                msb.size,
                                msb.id_so_det,
                                COALESCE(p_com.panel, p.panel) panel,
                                COALESCE(p_com.panel_status, p.panel_status) panel_status,
                                COALESCE(pd_com.id, pd.id) as part_detail_id,
                                COALESCE(GROUP_CONCAT(DISTINCT mp_com.nama_part), GROUP_CONCAT(DISTINCT mp.nama_part)) as nama_part,
                                COALESCE(GROUP_CONCAT(DISTINCT pd_com.part_status), GROUP_CONCAT(DISTINCT pd.part_status)) as part_status,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(qty_in_dc_main, 0)), SUM(COALESCE(qty_in_dc,0))) ELSE SUM(COALESCE(qty_in_dc, 0)) END) as qty_in,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_in_main, 0)), SUM(COALESCE(sec_inhouse_in,0))) ELSE SUM(COALESCE(sec_inhouse_in, 0)) END) kirim_secondary_dalam,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_rep_main, 0)), SUM(COALESCE(sec_inhouse_rep,0))) ELSE SUM(COALESCE(sec_inhouse_rep, 0)) END) terima_repaired_secondary_dalam,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_inhouse_out_main, 0)), SUM(COALESCE(sec_inhouse_out,0))) ELSE SUM(COALESCE(sec_inhouse_out, 0)) END) terima_good_secondary_dalam,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_in_main, 0)), SUM(COALESCE(sec_in_in,0))) ELSE SUM(COALESCE(sec_in_in, 0)) END) kirim_secondary_luar,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_rep_main, 0)), SUM(COALESCE(sec_in_rep,0))) ELSE SUM(COALESCE(sec_in_rep,0)) END) terima_repaired_secondary_luar,
                                (CASE WHEN pd.part_status = 'main' THEN COALESCE(SUM(COALESCE(sec_in_out_main, 0)), SUM(COALESCE(sec_in_out,0))) ELSE SUM(COALESCE(sec_in_out, 0)) END) terima_good_secondary_luar,
                                -- loading.stockers,
                                SUM(COALESCE(saldo_dc.loading_qty, 0)) loading_qty,
                                COALESCE(loading_line.loading_qty, 0) loading_qty1
                            FROM (
                                    SELECT
                                            *
                                    FROM
                                            dc
                                    UNION ALL
                                    SELECT
                                            *
                                    FROM
                                            sii_in
                                    UNION ALL
                                    SELECT
                                            *
                                    FROM
                                            sii
                                    UNION ALL
                                    SELECT
                                            *
                                    FROM
                                            wod
                                    UNION ALL
                                    SELECT
                                            *
                                    FROM
                                            si
                                    UNION ALL
                                    SELECT
                                            *
                                    FROM
                                            loading_line_qty
                            ) saldo_dc
                            LEFT JOIN master_sb_ws msb on msb.id_so_det = saldo_dc.so_det_id
                            left join part_detail pd on pd.id = saldo_dc.part_detail_id
                            left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                            left join part p on p.id = pd.part_id
                            left join part p_com on p_com.id = pd_com.part_id
                            LEFT JOIN master_part mp on mp.id = pd.master_part_id
                            LEFT JOIN master_part mp_com on mp_com.id = pd_com.master_part_id
                            LEFT JOIN loading_line on loading_line.so_det_id = saldo_dc.so_det_id and (CASE WHEN p_com.panel is not null THEN loading_line.panel = p_com.panel ELSE loading_line.panel = p.panel END)
                            GROUP BY
                                saldo_dc.so_det_id,
                                saldo_dc.part_detail_id
                        ) saldo_dc
                )

                select
                    stockers,
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    nama_part,
                    SUM(current_saldo_awal) current_saldo_awal,
                    SUM(qty_in) qty_in,
                    SUM(kirim_secondary_dalam) kirim_secondary_dalam,
                    SUM(terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                    SUM(terima_good_secondary_dalam) terima_good_secondary_dalam,
                    SUM(kirim_secondary_luar) kirim_secondary_luar,
                    SUM(terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                    SUM(terima_good_secondary_luar) terima_good_secondary_luar,
                    SUM(loading) loading,
                    SUM(current_saldo_awal)+SUM(current_saldo_akhir) as current_saldo_akhir
                from (
                    select
                        GROUP_CONCAT(dc_current_saldo.stockers) as stockers,
                        dc_current_saldo.buyer,
                        dc_current_saldo.ws,
                        dc_current_saldo.style,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        GROUP_CONCAT(dc_current_saldo.id_so_det) id_so_det,
                        dc_current_saldo.panel,
                        dc_current_saldo.panel_status,
                        dc_current_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.nama_part) as nama_part,
                        GROUP_CONCAT(DISTINCT dc_current_saldo.part_status) as part_status,
                        0 as current_saldo_awal,
                        sum(dc_current_saldo.qty_in) qty_in,
                        sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                        sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                        sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                        sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                        sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                        sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                        sum(dc_current_saldo.loading_qty) loading,
                        SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir
                    from
                        dc_current_saldo
                    GROUP BY
                        dc_current_saldo.ws,
                        dc_current_saldo.color,
                        dc_current_saldo.size,
                        dc_current_saldo.part_detail_id
                    UNION ALL
                    select
                        GROUP_CONCAT(dc_before_saldo.stockers) as stockers,
                        msb.buyer,
                        msb.ws as act_costing_ws,
                        msb.styleno as style,
                        msb.color,
                        msb.size,
                        GROUP_CONCAT(dc_before_saldo.so_det_id) so_det_id,
                        dc_before_saldo.panel,
                        dc_before_saldo.panel_status,
                        dc_before_saldo.part_detail_id,
                        GROUP_CONCAT(DISTINCT dc_before_saldo.nama_part) as nama_part,
                        GROUP_CONCAT(DISTINCT dc_before_saldo.part_status) as part_status,
                        SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                        0 qty_in,
                        0 kirim_secondary_dalam,
                        0 terima_repaired_secondary_dalam,
                        0 terima_good_secondary_dalam,
                        0 kirim_secondary_luar,
                        0 terima_repaired_secondary_luar,
                        0 terima_good_secondary_luar,
                        0 loading,
                        0 as current_saldo_akhir
                    from
                        dc_before_saldo
                        left join master_sb_ws msb on msb.id_so_det = dc_before_saldo.so_det_id
                    GROUP BY
                        msb.ws,
                        msb.color,
                        msb.size,
                        dc_before_saldo.part_detail_id
                    HAVING
                        current_saldo_awal != 0
                ) current_saldo
                group by
                    ws,
                    color,
                    size,
                    part_detail_id
        ");

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
            'A1:P' . ($event->getConcernable()->rowCount+2),
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
