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

        //                             WHEN panel_status = 'main' THEN
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


        $dataReport = DB::select("WITH dc_before_saldo AS (
	WITH dc AS (
		SELECT
			a.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			dc_in_input a
			LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
			LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
			LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
			LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
			LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
			LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
			LEFT JOIN part p ON pd.part_id = p.id
			LEFT JOIN master_part mp ON mp.id = pd.master_part_id
		WHERE
			a.tgl_trans >= '".$dateFrom." 00:00:00'
			AND a.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			a.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			dc_in_input a
			LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
			LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
			LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
			LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
			LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
			LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
			LEFT JOIN part p ON pd.part_id = p.id
			LEFT JOIN part_detail pdcom ON pdcom.id = pd.from_part_detail
			LEFT JOIN part pcom ON pcom.id = pdcom.part_id
			LEFT JOIN master_part mp ON mp.id = pd.master_part_id
		WHERE
			a.tgl_trans >= '".$dateFrom." 00:00:00'
			AND a.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		sii_in AS (
		SELECT
			sii_in.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			( sii_in.qty_in ) sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_in_input sii_in
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii_in.tgl_trans >= '".$dateFrom." 00:00:00'
			AND sii_in.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			sii_in.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			( sii_in.qty_in ) sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_in_input sii_in
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii_in.tgl_trans >= '".$dateFrom." 00:00:00'
			AND sii_in.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		sii AS (
		SELECT
			sii.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			sii.qty_replace sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			sii.qty_in sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_input sii
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii.tgl_trans >= '".$dateFrom."  00:00:00'
			AND sii.tgl_trans < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			sii.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			sii.qty_replace sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			sii.qty_in sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_inhouse_input sii
			LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			sii.tgl_trans >= '".$dateFrom." 00:00:00'
			AND sii.tgl_trans < '".$dateTo." 23:59:59'
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		wod AS (
		SELECT
			wod.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			wod.qty sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			wip_out_det wod
			LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			wod.updated_at >= '".$dateFrom." 00:00:00'
			AND wod.updated_at < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			wod.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			wod.qty sec_in_in,
			NULL sec_in_rep_main,
			NULL sec_in_rep,
			NULL sec_in_out_main,
			NULL sec_in_out
		FROM
			wip_out_det wod
			LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			wod.updated_at >= '".$dateFrom." 00:00:00'
			AND wod.updated_at < '".$dateTo." 23:59:59'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		si AS (
		SELECT
			si.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			si.qty_replace sec_in_rep_main,
			NULL sec_in_rep,
			si.qty_in sec_in_out_main,
			NULL sec_in_out
		FROM
			secondary_in_input si
			LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			si.tgl_trans >= '".$dateFrom."'
			AND si.tgl_trans < '".$dateTo."'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND pd.part_status = 'main' UNION ALL
		SELECT
			si.id_qr_stocker,
			pd.id AS part_detail_id,
			s.so_det_id,
			NULL qty_in_dc_main,
			NULL qty_in_dc,
			NULL sec_inhouse_in_main,
			NULL sec_inhouse_in,
			NULL sec_inhouse_rep_main,
			NULL sec_inhouse_rep,
			NULL sec_inhouse_out_main,
			NULL sec_inhouse_out,
			NULL sec_in_in_main,
			NULL sec_in_in,
			NULL sec_in_rep_main,
			si.qty_replace sec_in_rep,
			NULL sec_in_out_main,
			si.qty_in sec_in_out
		FROM
			secondary_in_input si
			LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
			LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
		WHERE
			si.tgl_trans >= '".$dateFrom."'
			AND si.tgl_trans < '".$dateTo."'
			AND s.id IS NOT NULL
			AND ( s.cancel IS NULL OR s.cancel != 'y' )
			AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
		),
		loading_line AS (
		SELECT
			panel,
			so_det_id,
			GROUP_CONCAT( stocker_id ) stockers,
			SUM( loading_qty ) loading_qty
		FROM
			(
			SELECT
				p.panel AS panel,
				GROUP_CONCAT( ll.stocker_id ) stocker_id,
				s.so_det_id,
				MIN( ll.qty ) loading_qty
			FROM
				loading_line ll
				LEFT JOIN stocker_input s ON s.id = ll.stocker_id
				LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
				LEFT JOIN part p ON p.id = pd.part_id
			WHERE
				ll.tanggal_loading >= '".$dateFrom."'
				AND ll.tanggal_loading < '".$dateTo."'
				AND ( s.cancel IS NULL OR s.cancel != 'y' )
			GROUP BY
				p.panel,
				s.form_cut_id,
				s.so_det_id,
				s.group_stocker,
				s.ratio
			) AS loading
		GROUP BY
			panel,
			so_det_id
		) SELECT CURRENT_DATE AS
		tanggal,
		stockers,
		buyer,
		ws,
		color,
		id_so_det,
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
		qty_in - kirim_secondary_dalam + terima_repaired_secondary_dalam + terima_good_secondary_dalam - kirim_secondary_luar + terima_repaired_secondary_luar + terima_good_secondary_luar - loading_qty saldo_akhir,
		CURRENT_TIMESTAMP created_at,
		CURRENT_TIMESTAMP updated_at
	FROM
		(
		SELECT
			GROUP_CONCAT( saldo_dc.id_qr_stocker ) AS stockers,
			max( msb.buyer ) AS buyer,
			max( msb.ws ) AS ws,
			max( msb.color ) AS color,
			max( msb.styleno ) AS style,
			msb.id_so_det,
			p.panel,
			p.panel_status,
			pd.id AS part_detail_id,
			GROUP_CONCAT( DISTINCT mp.nama_part ) AS nama_part,
			GROUP_CONCAT( DISTINCT pd.part_status ) AS part_status,
			(
			CASE

					WHEN panel_status = 'main' THEN
					COALESCE (
						SUM(
						COALESCE ( qty_in_dc_main, 0 )),
						SUM(
						COALESCE ( qty_in_dc, 0 ))) ELSE SUM(
					COALESCE ( qty_in_dc, 0 ))
				END
				) AS qty_in,
				(
				CASE

						WHEN panel_status = 'main' THEN
						COALESCE (
							SUM(
							COALESCE ( sec_inhouse_in_main, 0 )),
							SUM(
							COALESCE ( sec_inhouse_in, 0 ))) ELSE SUM(
						COALESCE ( sec_inhouse_in, 0 ))
					END
					) kirim_secondary_dalam,
					(
					CASE

							WHEN panel_status = 'main' THEN
							COALESCE (
								SUM(
								COALESCE ( sec_inhouse_rep_main, 0 )),
								SUM(
								COALESCE ( sec_inhouse_rep, 0 ))) ELSE SUM(
							COALESCE ( sec_inhouse_rep, 0 ))
						END
						) terima_repaired_secondary_dalam,
						(
						CASE

								WHEN panel_status = 'main' THEN
								COALESCE (
									SUM(
									COALESCE ( sec_inhouse_out_main, 0 )),
									SUM(
									COALESCE ( sec_inhouse_out, 0 ))) ELSE SUM(
								COALESCE ( sec_inhouse_out, 0 ))
							END
							) terima_good_secondary_dalam,
							(
							CASE

									WHEN panel_status = 'main' THEN
									COALESCE (
										SUM(
										COALESCE ( sec_in_in_main, 0 )),
										SUM(
										COALESCE ( sec_in_in, 0 ))) ELSE SUM(
									COALESCE ( sec_in_in, 0 ))
								END
								) kirim_secondary_luar,
								(
								CASE

										WHEN panel_status = 'main' THEN
										COALESCE (
											SUM(
											COALESCE ( sec_in_rep_main, 0 )),
											SUM(
											COALESCE ( sec_in_rep, 0 ))) ELSE SUM(
										COALESCE ( sec_in_rep, 0 ))
									END
									) terima_repaired_secondary_luar,
									(
									CASE

											WHEN panel_status = 'main' THEN
											COALESCE (
												SUM(
												COALESCE ( sec_in_out_main, 0 )),
												SUM(
												COALESCE ( sec_in_out, 0 ))) ELSE SUM(
											COALESCE ( sec_in_out, 0 ))
										END
										) terima_good_secondary_luar,
										COALESCE ( max( loading_line.loading_qty ), 0 ) loading_qty
									FROM
										(
										SELECT
											*
										FROM
											dc UNION ALL
										SELECT
											*
										FROM
											sii_in UNION ALL
										SELECT
											*
										FROM
											sii UNION ALL
										SELECT
											*
										FROM
											wod UNION ALL
										SELECT
											*
										FROM
											si
										) saldo_dc
										LEFT JOIN master_sb_ws msb ON msb.id_so_det = saldo_dc.so_det_id
										LEFT JOIN part_detail pd ON pd.id = saldo_dc.part_detail_id
										LEFT JOIN part p ON p.id = pd.part_id
										LEFT JOIN master_part mp ON mp.id = pd.master_part_id
										LEFT JOIN loading_line ON loading_line.so_det_id = saldo_dc.so_det_id
										AND loading_line.panel = p.panel
									GROUP BY
										saldo_dc.so_det_id,
										saldo_dc.part_detail_id
									) saldo_dc
								),
								dc_current_saldo AS (
									WITH dc AS (
									SELECT
										a.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										dc_in_input a
										LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
										LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
										LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
										LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
										LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
										LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
										LEFT JOIN part p ON pd.part_id = p.id
										LEFT JOIN master_part mp ON mp.id = pd.master_part_id
									WHERE
										a.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										a.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										( a.qty_awal - a.qty_reject + a.qty_replace ) qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										dc_in_input a
										LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
										LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
										LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
										LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
										LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
										LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
										LEFT JOIN part p ON pd.part_id = p.id
										LEFT JOIN part_detail pdcom ON pdcom.id = pd.from_part_detail
										LEFT JOIN part pcom ON pcom.id = pdcom.part_id
										LEFT JOIN master_part mp ON mp.id = pd.master_part_id
									WHERE
										a.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									sii_in AS (
									SELECT
										sii_in.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										( sii_in.qty_in ) sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_in_input sii_in
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii_in.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										sii_in.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										( sii_in.qty_in ) sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_in_input sii_in
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii_in.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii_in.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									sii AS (
									SELECT
										sii.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										sii.qty_replace sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										sii.qty_in sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_input sii
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										sii.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										sii.qty_replace sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										sii.qty_in sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_inhouse_input sii
										LEFT JOIN stocker_input s ON s.id_qr_stocker = sii.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										sii.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									wod AS (
									SELECT
										wod.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										wod.qty sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										wip_out_det wod
										LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										wod.updated_at BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										wod.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										wod.qty sec_in_in,
										NULL sec_in_rep_main,
										NULL sec_in_rep,
										NULL sec_in_out_main,
										NULL sec_in_out
									FROM
										wip_out_det wod
										LEFT JOIN stocker_input s ON s.id_qr_stocker = wod.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										wod.updated_at BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									si AS (
									SELECT
										si.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										si.qty_replace sec_in_rep_main,
										NULL sec_in_rep,
										si.qty_in sec_in_out_main,
										NULL sec_in_out
									FROM
										secondary_in_input si
										LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										si.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND pd.part_status = 'main' UNION ALL
									SELECT
										si.id_qr_stocker,
										pd.id AS part_detail_id,
										s.so_det_id,
										NULL qty_in_dc_main,
										NULL qty_in_dc,
										NULL sec_inhouse_in_main,
										NULL sec_inhouse_in,
										NULL sec_inhouse_rep_main,
										NULL sec_inhouse_rep,
										NULL sec_inhouse_out_main,
										NULL sec_inhouse_out,
										NULL sec_in_in_main,
										NULL sec_in_in,
										NULL sec_in_rep_main,
										si.qty_replace sec_in_rep,
										NULL sec_in_out_main,
										si.qty_in sec_in_out
									FROM
										secondary_in_input si
										LEFT JOIN stocker_input s ON s.id_qr_stocker = si.id_qr_stocker
										LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
									WHERE
										si.tgl_trans BETWEEN '".$dateFrom."'
										AND '".$dateTo."'
										AND s.id IS NOT NULL
										AND ( s.cancel IS NULL OR s.cancel != 'y' )
										AND ( pd.part_status != 'main' OR pd.part_status IS NULL )
									),
									loading_line AS (
									SELECT
										panel,
										so_det_id,
										GROUP_CONCAT( stocker_id ) stockers,
										SUM( loading_qty ) loading_qty
									FROM
										(
										SELECT
											p.panel AS panel,
											GROUP_CONCAT( ll.stocker_id ) stocker_id,
											s.so_det_id,
											MIN( ll.qty ) loading_qty
										FROM
											loading_line ll
											LEFT JOIN stocker_input s ON s.id = ll.stocker_id
											LEFT JOIN part_detail pd ON pd.id = s.part_detail_id
											LEFT JOIN part p ON p.id = pd.part_id
										WHERE
											ll.tanggal_loading BETWEEN '".$dateFrom."'
											AND '".$dateTo."'
											AND ( s.cancel IS NULL OR s.cancel != 'y' )
										GROUP BY
											p.panel,
											s.form_cut_id,
											s.so_det_id,
											s.group_stocker,
											s.ratio
										) AS loading
									GROUP BY
										panel,
										so_det_id
									) SELECT
									*,
									qty_in - kirim_secondary_dalam + terima_repaired_secondary_dalam + terima_good_secondary_dalam - kirim_secondary_luar + terima_repaired_secondary_luar + terima_good_secondary_luar - loading_qty saldo_akhir
								FROM
									(
									SELECT
										GROUP_CONCAT( saldo_dc.id_qr_stocker ) AS stockers,
										max( msb.buyer ) AS buyer,
										max( msb.ws ) AS ws,
										max( msb.color ) AS color,
										max( msb.size ) AS size,
										max( msb.styleno ) AS style,
										msb.id_so_det,
										p.panel,
										p.panel_status,
										pd.id AS part_detail_id,
										GROUP_CONCAT( DISTINCT mp.nama_part ) AS nama_part,
										GROUP_CONCAT( DISTINCT pd.part_status ) AS part_status,
										(
										CASE

												WHEN panel_status = 'main' THEN
												COALESCE (
													SUM(
													COALESCE ( qty_in_dc_main, 0 )),
													SUM(
													COALESCE ( qty_in_dc, 0 ))) ELSE SUM(
												COALESCE ( qty_in_dc, 0 ))
											END
											) AS qty_in,
											(
											CASE

													WHEN panel_status = 'main' THEN
													COALESCE (
														SUM(
														COALESCE ( sec_inhouse_in_main, 0 )),
														SUM(
														COALESCE ( sec_inhouse_in, 0 ))) ELSE SUM(
													COALESCE ( sec_inhouse_in, 0 ))
												END
												) kirim_secondary_dalam,
												(
												CASE

														WHEN panel_status = 'main' THEN
														COALESCE (
															SUM(
															COALESCE ( sec_inhouse_rep_main, 0 )),
															SUM(
															COALESCE ( sec_inhouse_rep, 0 ))) ELSE SUM(
														COALESCE ( sec_inhouse_rep, 0 ))
													END
													) terima_repaired_secondary_dalam,
													(
													CASE

															WHEN panel_status = 'main' THEN
															COALESCE (
																SUM(
																COALESCE ( sec_inhouse_out_main, 0 )),
																SUM(
																COALESCE ( sec_inhouse_out, 0 ))) ELSE SUM(
															COALESCE ( sec_inhouse_out, 0 ))
														END
														) terima_good_secondary_dalam,
														(
														CASE

																WHEN panel_status = 'main' THEN
																COALESCE (
																	SUM(
																	COALESCE ( sec_in_in_main, 0 )),
																	SUM(
																	COALESCE ( sec_in_in, 0 ))) ELSE SUM(
																COALESCE ( sec_in_in, 0 ))
															END
															) kirim_secondary_luar,
															(
															CASE

																	WHEN panel_status = 'main' THEN
																	COALESCE (
																		SUM(
																		COALESCE ( sec_in_rep_main, 0 )),
																		SUM(
																		COALESCE ( sec_in_rep, 0 ))) ELSE SUM(
																	COALESCE ( sec_in_rep, 0 ))
																END
																) terima_repaired_secondary_luar,
																(
																CASE

																		WHEN panel_status = 'main' THEN
																		COALESCE (
																			SUM(
																			COALESCE ( sec_in_out_main, 0 )),
																			SUM(
																			COALESCE ( sec_in_out, 0 ))) ELSE SUM(
																		COALESCE ( sec_in_out, 0 ))
																	END
																	) terima_good_secondary_luar,
																	COALESCE ( max( loading_line.loading_qty ), 0 ) loading_qty
																FROM
																	(
																	SELECT
																		*
																	FROM
																		dc UNION ALL
																	SELECT
																		*
																	FROM
																		sii_in UNION ALL
																	SELECT
																		*
																	FROM
																		sii UNION ALL
																	SELECT
																		*
																	FROM
																		wod UNION ALL
																	SELECT
																		*
																	FROM
																		si
																	) saldo_dc
																	LEFT JOIN master_sb_ws msb ON msb.id_so_det = saldo_dc.so_det_id
																	LEFT JOIN part_detail pd ON pd.id = saldo_dc.part_detail_id
																	LEFT JOIN part p ON p.id = pd.part_id
																	LEFT JOIN master_part mp ON mp.id = pd.master_part_id
																	LEFT JOIN loading_line ON loading_line.so_det_id = saldo_dc.so_det_id
																	AND loading_line.panel = p.panel
																GROUP BY
																	saldo_dc.so_det_id,
																	saldo_dc.part_detail_id
																) saldo_dc
															)

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
                                                            SUM(COALESCE(dc_before_saldo.saldo_akhir, 0)) as current_saldo_awal,
                                                            sum(dc_current_saldo.qty_in) qty_in,
                                                            sum(dc_current_saldo.kirim_secondary_dalam) kirim_secondary_dalam,
                                                            sum(dc_current_saldo.terima_repaired_secondary_dalam) terima_repaired_secondary_dalam,
                                                            sum(dc_current_saldo.terima_good_secondary_dalam) terima_good_secondary_dalam,
                                                            sum(dc_current_saldo.kirim_secondary_luar) kirim_secondary_luar,
                                                            sum(dc_current_saldo.terima_repaired_secondary_luar) terima_repaired_secondary_luar,
                                                            sum(dc_current_saldo.terima_good_secondary_luar) terima_good_secondary_luar,
                                                            SUM(COALESCE(dc_before_saldo.saldo_akhir, 0))+SUM(COALESCE(dc_current_saldo.saldo_akhir, 0)) as current_saldo_akhir
                                                        from
                                                            dc_current_saldo
                                                            left join dc_before_saldo on dc_before_saldo.id_so_det = dc_current_saldo.id_so_det and dc_before_saldo.part_detail_id = dc_current_saldo.part_detail_id
                                                        GROUP BY
                                                            dc_current_saldo.ws,
                                                            dc_current_saldo.color,
                                                            dc_current_saldo.size,
                                                            dc_current_saldo.part_detail_id
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
