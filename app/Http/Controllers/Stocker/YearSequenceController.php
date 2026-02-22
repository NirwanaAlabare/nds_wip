<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailLap;
use App\Models\Cutting\FormCutReject;
use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\Cutting\FormCutPieceDetailSize;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartForm;
use App\Models\Stocker\ModifySizeQty;
use App\Models\Stocker\MonthCount;
use App\Models\Stocker\YearSequence;
use App\Models\Stocker\StockerAdditional;
use App\Models\Stocker\StockerAdditionalDetail;
use App\Models\Dc\DCIn;
use App\Models\Stocker\StockerSeparate;
use App\Models\Stocker\StockerSeparateDetail;
use App\Models\SignalBit\SoDet;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\OutputPacking;
use App\Exports\Stocker\StockerListExport;
use App\Exports\Stocker\StockerListDetailExport;
use App\Services\StockerService;
use App\Services\SewingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DB;
use QrCode;
use PDF;

class YearSequenceController extends Controller
{
    // Stocker List
    public function stockerList(Request $request) {
        if ($request->ajax()) {
            $additionalQuery = "";

            $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
            $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

            // Convert the dates to timestamps
            $timestampFrom = strtotime($dateFrom);
            $timestampTo = strtotime($dateTo);

            // Calculate the difference in seconds
            $diffInSeconds = abs($timestampTo - $timestampFrom);

            // Convert seconds to days
            $daysInterval = $diffInSeconds / (60 * 60 * 24);

            // Limit to 1 month
            if ($daysInterval > 30) {
                $dateTo = date("Y-m-d", strtotime($dateFrom . " +30 days"));
            }

            // Deprecated
            // if ($daysInterval > 3) {
            //     $stockerList = DB::select("
            //         SELECT
            //             year_sequence_num.updated_at,
            //             stocker_input.id_qr_stocker,
            //             stocker_input.part,
            //             stocker_input.form_cut_id,
            //             stocker_input.act_costing_ws,
            //             stocker_input.so_det_id,
            //             stocker_input.buyer,
            //             stocker_input.style,
            //             UPPER(TRIM(stocker_input.color)) color,
            //             stocker_input.size,
            //             stocker_input.dest,
            //             stocker_input.group_stocker,
            //             stocker_input.shade,
            //             stocker_input.ratio,
            //             stocker_input.stocker_range,
            //             stocker_input.qty_stocker,
            //             stocker_input.no_form,
            //             stocker_input.no_cut,
            //             stocker_input.panel,
            //             year_sequence_num.year_sequence,
            //             ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
            //             CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
            //             stocker_input.tipe
            //         FROM
            //             (
            //                 SELECT
            //                     ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
            //                     so_det_id,
            //                     CONCAT( YEAR, '_', year_sequence ) year_sequence,
            //                     MIN( number ) range_numbering_awal,
            //                     MAX( number ) range_numbering_akhir,
            //                     MIN( year_sequence_number ) range_awal,
            //                     MAX( year_sequence_number ) range_akhir,
            //                     id_qr_stocker,
            //                     updated_at,
            //                     (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
            //                 FROM
            //                     year_sequence
            //                 WHERE
            //                     year_sequence.so_det_id IS NOT NULL
            //                     AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
            //                     AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
            //                 GROUP BY
            //                     form_cut_id,
            //                     form_reject_id,
            //                     form_piece_id,
            //                     so_det_id,
            //                     id_qr_stocker,
            //                     COALESCE ( updated_at, created_at )
            //             ) year_sequence_num
            //             LEFT JOIN (
            //                 SELECT
            //                     GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
            //                     ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
            //                     stocker_input.act_costing_ws,
            //                     stocker_input.so_det_id,
            //                     master_sb_ws.buyer buyer,
            //                     master_sb_ws.styleno style,
            //                     UPPER(TRIM(master_sb_ws.color)) color,
            //                     master_sb_ws.size,
            //                     master_sb_ws.dest,
            //                     stocker_input.part_detail_id,
            //                     stocker_input.shade,
            //                     stocker_input.group_stocker,
            //                     stocker_input.ratio,
            //                     stocker_input.range_awal,
            //                     stocker_input.range_akhir,
            //                     stocker_input.created_at,
            //                     stocker_input.updated_at,
            //                     COALESCE(form_cut_input.waktu_mulai, form_cut_reject.created_at, form_cut_piece.created_at) waktu_mulai,
            //                     COALESCE(form_cut_input.waktu_selesai, form_cut_reject.updated_at, form_cut_piece.updated_at) waktu_selesai,
            //                     COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
            //                     COALESCE(form_cut_input.no_cut, form_cut_piece.no_form, '-') no_cut,
            //                     COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
            //                     GROUP_CONCAT( DISTINCT CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL THEN CONCAT(' - ', part_detail.part_status) ELSE '' END)) ) part,
            //                     CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
            //                     ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
            //                     (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
            //                 FROM
            //                     stocker_input
            //                     left join part_detail on stocker_input.part_detail_id = part_detail.id
            //                     left join part on part.id = part_detail.part_id
            //                     left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
            //                     left join part part_com on part_com.id = part_detail_com.part_id
            //                     LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
            //                     LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
            //                     LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
            //                     LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
            //                     LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
            //                 GROUP BY
            //                     stocker_input.id_qr_stocker,
            //                     stocker_input.form_cut_id,
            //                     stocker_input.form_reject_id,
            //                     stocker_input.form_piece_id,
            //                     (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
            //                     stocker_input.so_det_id,
            //                     stocker_input.group_stocker,
            //                     stocker_input.ratio
            //             ) stocker_input ON
            //             -- (year_sequence_num.form_cut_id = stocker_input.form_cut_id and year_sequence_num.tipe = stocker_input.tipe AND year_sequence_num.so_det_id = stocker_input.so_det_id
            //             -- AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
            //             -- AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED))
            //             -- OR stocker_input.id_qr_stocker = year_sequence_num.id_qr_stocker
            //             WHERE
            //             (
            //                 stocker_input.waktu_mulai >='".$dateFrom." 00:00:00'
            //                 OR stocker_input.waktu_selesai >= '".$dateFrom." 00:00:00'
            //                 OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
            //                 OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
            //                 OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
            //             )
            //             AND (
            //                 stocker_input.waktu_mulai <= '".$dateTo." 23:59:59'
            //                 OR stocker_input.waktu_selesai <= '".$dateTo." 23:59:59'
            //                 OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
            //                 OR stocker_input.created_at <= '".$dateTo." 23:59:59'
            //                 OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
            //             )
            //             AND
            //             year_sequence_num.so_det_id is not null
            //         GROUP BY
            //             stocker_input.id_qr_stocker,
            //             stocker_input.form_cut_id,
            //             stocker_input.tipe,
            //             stocker_input.so_det_id,
            //             year_sequence_num.updated_at
            //         HAVING
            //             stocker_input.form_cut_id is not null
            //         ORDER BY
            //             year_sequence_num.updated_at DESC
            //     ");
            // } else {
            //     $stockerList = DB::select("
            //         SELECT
            //             year_sequence_num.updated_at,
            //             GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
            //             GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
            //             COALESCE(form_cut_input.id, form_cut_reject.id, form_cut_piece.id) form_cut_id,
            //             stocker_input.act_costing_ws,
            //             stocker_input.so_det_id,
            //             master_sb_ws.buyer buyer,
            //             master_sb_ws.styleno style,
            //             UPPER(TRIM(master_sb_ws.color)) color,
            //             master_sb_ws.size,
            //             master_sb_ws.dest,
            //             COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
            //             GROUP_CONCAT( DISTINCT CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL THEN CONCAT(' - ', part_detail.part_status) ELSE '' END)) ) part,
            //             COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
            //             COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') no_cut,
            //             stocker_input.group_stocker,
            //             stocker_input.shade,
            //             stocker_input.ratio,
            //             CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
            //             ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
            //             year_sequence_num.year_sequence,
            //             ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
            //             CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
            //             (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
            //         FROM
            //             stocker_input
            //             left join part_detail on stocker_input.part_detail_id = part_detail.id
            //             left join part on part.id = part_detail.part_id
            //             left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
            //             left join part part_com on part_com.id = part_detail_com.part_id
            //             LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
            //             LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
            //             LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
            //             LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
            //             LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
            //             INNER JOIN (
            //                 SELECT
            //                     ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
            //                     so_det_id,
            //                     CONCAT( `year`, '_', year_sequence ) year_sequence,
            //                     MIN( number ) range_numbering_awal,
            //                     MAX( number ) range_numbering_akhir,
            //                     MIN( year_sequence_number ) range_awal,
            //                     MAX( year_sequence_number ) range_akhir,
            //                     id_qr_stocker,
            //                     COALESCE ( updated_at, created_at ) updated_at,
            //                     (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
            //                 FROM
            //                     year_sequence
            //                 WHERE
            //                     year_sequence.so_det_id IS NOT NULL
            //                     AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
            //                     AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
            //                 GROUP BY
            //                     id_qr_stocker,
            //                     form_cut_id,
            //                     form_reject_id,
            //                     form_piece_id,
            //                     (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
            //                     so_det_id,
            //                     COALESCE ( updated_at, created_at )
            //                 ORDER BY
            //                     COALESCE ( updated_at, created_at)
            //             ) year_sequence_num ON
            //             -- (year_sequence_num.form_cut_id = (CASE WHEN year_sequence_num.tipe = 'PIECE' THEN stocker_input.form_piece_id ELSE (CASE WHEN year_sequence_num.tipe = 'REJECT' THEN stocker_input.form_reject_id ELSE stocker_input.form_cut_id END) END)
            //             -- AND year_sequence_num.so_det_id = stocker_input.so_det_id
            //             -- AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
            //             -- AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED))
            //             -- OR
            //             stocker_input.id_qr_stocker = year_sequence_num.id_qr_stocker
            //         WHERE
            //             (
            //                 form_cut_input.waktu_mulai >= '".$dateFrom." 00:00:00'
            //                 OR form_cut_input.waktu_selesai >= '".$dateFrom." 00:00:00'
            //                 OR form_cut_reject.updated_at >= '".$dateFrom." 00:00:00'
            //                 OR form_cut_piece.updated_at >= '".$dateFrom." 00:00:00'
            //                 OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
            //                 OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
            //                 OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
            //             )
            //             AND (
            //                 form_cut_input.waktu_mulai <= '".$dateTo." 23:59:59'
            //                 OR form_cut_input.waktu_selesai <= '".$dateTo." 23:59:59'
            //                 OR form_cut_reject.updated_at <= '".$dateTo." 23:59:59'
            //                 OR form_cut_piece.updated_at <= '".$dateTo." 23:59:59'
            //                 OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
            //                 OR stocker_input.created_at <= '".$dateTo." 23:59:59'
            //                 OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
            //             )
            //             AND
            //             year_sequence_num.so_det_id is not null
            //         GROUP BY
            //             stocker_input.id_qr_stocker,
            //             stocker_input.form_cut_id,
            //             stocker_input.form_reject_id,
            //             stocker_input.form_piece_id,
            //             stocker_input.so_det_id,
            //             year_sequence_num.updated_at
            //         HAVING
            //             (stocker_input.form_cut_id is not null or stocker_input.form_reject_id is not null or stocker_input.form_piece_id is not null)
            //         ORDER BY
            //             year_sequence_num.updated_at DESC
            //     ");
            // }
            $stockerList = DB::select("
                with
                    stocker_label as (
                        select
                            id_qr_stocker,
                            so_det_id,
                            CONCAT( YEAR, '_', year_sequence ) year_sequence,
                            MIN( number ) range_numbering_awal,
                            MAX( number ) range_numbering_akhir,
                            MIN( year_sequence_number ) range_awal,
                            MAX( year_sequence_number ) range_akhir,
                            COUNT(*) as total,
                            updated_at,
                            (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                        from
                            year_sequence
                        where
                            year_sequence.updated_at between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'
                        group by
                            year_sequence.id_qr_stocker
                    ),

                    stocker_bundle as (
                        select
                            stocker_label.updated_at,
                            stocker_input.id_qr_stocker,
                            GROUP_CONCAT(DISTINCT stocker_bundle.id_qr_stocker) id_qr_stocker_bundle,
                            master_part.nama_part part,
                            COALESCE(stocker_input.form_cut_id, stocker_input.form_reject_id, stocker_input.form_piece_id) form_cut_id,
                            master_sb_ws.ws act_costing_ws,
                            master_sb_ws.id_so_det so_det_id,
                            master_sb_ws.buyer,
                            master_sb_ws.styleno style,
                            UPPER(TRIM(master_sb_ws.color)) color,
                            master_sb_ws.size,
                            master_sb_ws.dest,
                            stocker_input.group_stocker,
                            stocker_input.shade,
                            stocker_input.ratio,
                            CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir )) stocker_range,
                            stocker_input.qty_ply qty_stocker,
                            COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
                            COALESCE(form_cut_input.no_cut, '-') no_cut,
                            COALESCE(part_com.panel, part.panel) panel,
                            stocker_label.year_sequence,
                            stocker_label.total qty,
                            CONCAT( MIN( stocker_label.range_awal ), ' - ', MAX( stocker_label.range_akhir )) numbering_range,
                            (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                        from
                            stocker_input
                            left join stocker_input stocker_bundle on stocker_bundle.form_cut_id <=> stocker_input.form_cut_id
                                AND stocker_bundle.form_reject_id <=> stocker_input.form_reject_id
                                AND stocker_bundle.form_piece_id  <=> stocker_input.form_piece_id
                                AND stocker_bundle.so_det_id      <=> stocker_input.so_det_id
                                AND stocker_bundle.group_stocker  <=> stocker_input.group_stocker
                                AND stocker_bundle.ratio          <=> stocker_input.ratio
                                AND stocker_bundle.stocker_reject <=> stocker_input.stocker_reject
                            inner join stocker_label on stocker_label.id_qr_stocker = stocker_input.id_qr_stocker
                            left join part_detail on stocker_input.part_detail_id = part_detail.id
                            left join part on part.id = part_detail.part_id
                            left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                            left join part part_com on part_com.id = part_detail_com.part_id
                            LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                            LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                            LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
                        GROUP BY
                            stocker_input.id_qr_stocker,
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
                            stocker_input.form_piece_id,
                            (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.ratio
                    )

                    select * from stocker_bundle
            ");

            return DataTables::of($stockerList)->toJson();
        }

        $months = [['angka' => '01','nama' => 'Januari'],['angka' => '02','nama' => 'Februari'],['angka' => '03','nama' => 'Maret'],['angka' => '04','nama' => 'April'],['angka' => '05','nama' => 'Mei'],['angka' => '06','nama' => 'Juni'],['angka' => '07','nama' => 'Juli'],['angka' => '08','nama' => 'Agustus'],['angka' => '09','nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y', strtotime('+1 years'))));

        return view("stocker.stocker.stocker-list", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "stocker-list", "months" => $months, "years" => $years]);
    }

    public function stockerListTotal(Request $request) {
        $additionalQuery = "";

        $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
        $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

        // Convert the dates to timestamps
        $timestampFrom = strtotime($dateFrom);
        $timestampTo = strtotime($dateTo);

        // Calculate the difference in seconds
        $diffInSeconds = abs($timestampTo - $timestampFrom);

        // Convert seconds to days
        $daysInterval = $diffInSeconds / (60 * 60 * 24);

        $tanggal_filter = "";
        if ($request->tanggal_filter) {
            $tanggal_filter = "AND year_sequence_num.updated_at LIKE '%".$request->tanggal_filter."%' ";
        }
        $no_form_filter = "";
        if ($request->no_form_filter) {
            $no_form_filter = "AND COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) LIKE '%".$request->no_form_filter."%' ";
        }
        $no_cut_filter = "";
        if ($request->no_cut_filter) {
            $no_cut_filter = "AND COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') LIKE '%".$request->no_cut_filter."%' ";
        }
        $color_filter = "";
        if ($request->color_filter) {
            $color_filter = "AND UPPER(TRIM(master_sb_ws.color)) LIKE '%".strtoupper(trim($request->color_filter))."%' ";
        }
        $size_filter = "";
        if ($request->size_filter) {
            $size_filter = "AND master_sb_ws.size LIKE '%".$request->size_filter."%' ";
        }
        $dest_filter = "";
        if ($request->dest_filter) {
            $dest_filter = "AND master_sb_ws.dest LIKE '%".$request->dest_filter."%' ";
        }
        $qty_filter = "";
        if ($request->qty_filter) {
            $qty_filter = "AND (MAX(year_sequence_num.range_akhir) - MIN(year_sequence_num.range_awal) + 1) LIKE '%".$request->qty_filter."%' ";
        }
        $year_sequence_filter = "";
        if ($request->year_sequence_filter) {
            $year_sequence_filter = "AND year_sequence_num.year_sequence LIKE '%".$request->year_sequence_filter."%' ";
        }
        $numbering_range_filter = "";
        if ($request->numbering_range_filter) {
            $numbering_range_filter = "AND CONCAT( MIN(year_sequence_num.range_awal), ' - ', MAX(year_sequence_num.range_akhir) ) LIKE '%".$request->numbering_range_filter."%' ";
        }
        $buyer_filter = "";
        if ($request->buyer_filter) {
            $buyer_filter = "AND master_sb_ws.buyer LIKE '%".$request->buyer_filter."%' ";
        }
        $ws_filter = "";
        if ($request->ws_filter) {
            $ws_filter = "AND master_sb_ws.ws LIKE '%".$request->ws_filter."%' ";
        }
        $style_filter = "";
        if ($request->style_filter) {
            $style_filter = "AND master_sb_ws.styleno LIKE '%".$request->style_filter."%' ";
        }
        $stocker_filter = "";
        if ($request->stocker_filter) {
            $stocker_filter = "AND GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker) LIKE '%".$request->stocker_filter."%' ";
        }
        $tipe_filter = "";
        if ($request->tipe_filter) {
            $tipe_filter = "AND tipe LIKE '%".$request->tipe_filter."%' ";
        }
        $part_filter = "";
        if ($request->part_filter) {
            $part_filter = "AND GROUP_CONCAT(DISTINCT master_part.nama_part) LIKE '%".$request->part_filter."%' ";
        }
        $group_filter = "";
        if ($request->group_filter) {
            $group_filter = "AND stocker_input.group_stocker LIKE '%".$request->group_filter."%' ";
        }
        $shade_filter = "";
        if ($request->shade_filter) {
            $shade_filter = "AND stocker_input.shade LIKE '%".$request->shade_filter."%' ";
        }
        $ratio_filter = "";
        if ($request->ratio_filter) {
            $ratio_filter = "AND stocker_input.ratio LIKE '%".$request->ratio_filter."%' ";
        }
        $stocker_range_filter = "";
        if ($request->stocker_range_filter) {
            $stocker_range_filter = "AND CONCAT( MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir) ) LIKE '%".$request->stocker_range_filter."%' ";
        }

        // Limit to 1 month
        if ($daysInterval > 30) {
            $dateTo = date("Y-m-d", strtotime($dateFrom . " +30 days"));
        }

        // Deprecated
        // if ($daysInterval > 3) {
        //     $stockerList = DB::select("
        //         SELECT
        //             COUNT(*) total_row,
        //             SUM(qty) total_qty
        //         FROM
        //         (
        //             SELECT
        //                 year_sequence_num.updated_at,
        //                 stocker_input.id_qr_stocker,
        //                 stocker_input.part,
        //                 stocker_input.form_cut_id,
        //                 stocker_input.act_costing_ws,
        //                 stocker_input.so_det_id,
        //                 stocker_input.buyer,
        //                 stocker_input.style,
        //                 UPPER(TRIM(stocker_input.color)) color,
        //                 stocker_input.size,
        //                 stocker_input.dest,
        //                 stocker_input.group_stocker,
        //                 stocker_input.shade,
        //                 stocker_input.ratio,
        //                 stocker_input.stocker_range,
        //                 stocker_input.qty_stocker,
        //                 stocker_input.no_form,
        //                 stocker_input.no_cut,
        //                 year_sequence_num.year_sequence,
        //                 ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
        //                 CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
        //                 stocker_input.tipe
        //             FROM
        //                 (
        //                     SELECT
        //                         ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
        //                         so_det_id,
        //                         CONCAT( YEAR, '_', year_sequence ) year_sequence,
        //                         MIN( number ) range_numbering_awal,
        //                         MAX( number ) range_numbering_akhir,
        //                         MIN( year_sequence_number ) range_awal,
        //                         MAX( year_sequence_number ) range_akhir,
        //                         updated_at,
        //                         (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //                     FROM
        //                         year_sequence
        //                     WHERE
        //                         year_sequence.so_det_id IS NOT NULL
        //                         AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
        //                         AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
        //                     GROUP BY
        //                         form_cut_id,
        //                         form_reject_id,
        //                         form_piece_id,
        //                         so_det_id,
        //                         updated_at
        //                 ) year_sequence_num
        //                 LEFT JOIN (
        //                     SELECT
        //                         GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
        //                         COALESCE(form_cut_input.id, form_cut_reject.id, form_cut_piece.id) form_cut_id,
        //                         stocker_input.act_costing_ws,
        //                         stocker_input.so_det_id,
        //                         master_sb_ws.buyer buyer,
        //                         master_sb_ws.styleno style,
        //                         UPPER(TRIM(master_sb_ws.color)) color,
        //                         master_sb_ws.size,
        //                         master_sb_ws.dest,
        //                         stocker_input.part_detail_id,
        //                         stocker_input.shade,
        //                         stocker_input.group_stocker,
        //                         stocker_input.ratio,
        //                         stocker_input.range_awal,
        //                         stocker_input.range_akhir,
        //                         stocker_input.created_at,
        //                         stocker_input.updated_at,
        //                         COALESCE(form_cut_input.waktu_mulai, form_cut_reject.created_at, form_cut_piece.created_at) waktu_mulai,
        //                         COALESCE(form_cut_input.waktu_selesai, form_cut_reject.updated_at, form_cut_piece.updated_at) waktu_selesai,
        //                         COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
        //                         COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') no_cut,
        //                         GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
        //                         CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
        //                         ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
        //                         (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //                     FROM
        //                         stocker_input
        //                         LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
        //                         LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
        //                         LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
        //                         LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
        //                         LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
        //                         LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
        //                     GROUP BY
        //                         stocker_input.form_cut_id,
        //                         stocker_input.form_reject_id,
        //                         stocker_input.form_piece_id,
        //                         stocker_input.so_det_id,
        //                         stocker_input.group_stocker,
        //                         stocker_input.ratio
        //                 ) stocker_input ON year_sequence_num.form_cut_id = stocker_input.form_cut_id and year_sequence_num.tipe = stocker_input.tipe
        //                 AND year_sequence_num.so_det_id = stocker_input.so_det_id
        //                 AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
        //                 AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED)
        //                 WHERE
        //                 (
        //                     stocker_input.waktu_mulai >='".$dateFrom." 00:00:00'
        //                     OR stocker_input.waktu_selesai >= '".$dateFrom." 00:00:00'
        //                     OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
        //                     OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
        //                     OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
        //                 )
        //                 AND (
        //                     stocker_input.waktu_mulai <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.waktu_selesai <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.created_at <= '".$dateTo." 23:59:59'
        //                     OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
        //                 )
        //                 ".$tanggal_filter."
        //                 ".$no_form_filter."
        //                 ".$no_cut_filter."
        //                 ".$color_filter."
        //                 ".$size_filter."
        //                 ".$dest_filter."
        //                 ".$year_sequence_filter."
        //                 ".$buyer_filter."
        //                 ".$ws_filter."
        //                 ".$style_filter."
        //                 ".$group_filter."
        //                 ".$shade_filter."
        //                 ".$ratio_filter."
        //                 ".$tipe_filter."
        //             GROUP BY
        //                 stocker_input.form_cut_id,
        //                 stocker_input.tipe,
        //                 stocker_input.so_det_id,
        //                 year_sequence_num.updated_at
        //             HAVING
        //                 stocker_input.form_cut_id is not null
        //                 ".$qty_filter."
        //                 ".$numbering_range_filter."
        //                 ".$stocker_filter."
        //                 ".$part_filter."
        //                 ".$stocker_range_filter."
        //             ORDER BY
        //                 year_sequence_num.updated_at DESC
        //         ) stock_list
        //     ");
        // } else {
        //     $stockerList = DB::select("
        //         SELECT
        //             COUNT(*) total_row,
        //             SUM(qty) total_qty
        //         FROM
        //         (
        //             SELECT
        //                 year_sequence_num.updated_at,
        //                 GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
        //                 GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
        //                 COALESCE(form_cut_input.id, form_cut_reject.id, form_cut_piece.id) form_cut_id,
        //                 stocker_input.act_costing_ws,
        //                 stocker_input.so_det_id,
        //                 master_sb_ws.buyer buyer,
        //                 master_sb_ws.styleno style,
        //                 UPPER(TRIM(master_sb_ws.color)) color,
        //                 master_sb_ws.size,
        //                 master_sb_ws.dest,
        //                 COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
        //                 COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') no_cut,
        //                 stocker_input.group_stocker,
        //                 stocker_input.shade,
        //                 stocker_input.ratio,
        //                 CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
        //                 ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
        //                 year_sequence_num.year_sequence,
        //                 ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
        //                 CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
        //                 (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //             FROM
        //                 stocker_input
        //                 LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
        //                 LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
        //                 LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
        //                 LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
        //                 LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
        //                 LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
        //                 INNER JOIN (
        //                     SELECT
        //                         ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
        //                         so_det_id,
        //                         CONCAT( `year`, '_', year_sequence ) year_sequence,
        //                         MIN( number ) range_numbering_awal,
        //                         MAX( number ) range_numbering_akhir,
        //                         MIN( year_sequence_number ) range_awal,
        //                         MAX( year_sequence_number ) range_akhir,
        //                         COALESCE ( updated_at, created_at ) updated_at,
        //                         (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //                     FROM
        //                         year_sequence
        //                     WHERE
        //                         year_sequence.so_det_id IS NOT NULL
        //                         AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
        //                         AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
        //                     GROUP BY
        //                         form_cut_id,
        //                         form_reject_id,
        //                         form_piece_id,
        //                         (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
        //                         so_det_id,
        //                         COALESCE ( updated_at, created_at )
        //                     ORDER BY
        //                         COALESCE ( updated_at, created_at)
        //                 ) year_sequence_num ON year_sequence_num.form_cut_id = (CASE WHEN year_sequence_num.tipe = 'PIECE' THEN stocker_input.form_piece_id ELSE (CASE WHEN year_sequence_num.tipe = 'REJECT' THEN stocker_input.form_reject_id ELSE stocker_input.form_cut_id END) END)
        //                 AND year_sequence_num.so_det_id = stocker_input.so_det_id
        //                 AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
        //                 AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED)
        //             WHERE
        //                 (
        //                     form_cut_input.waktu_mulai >= '".$dateFrom." 00:00:00'
        //                     OR form_cut_input.waktu_selesai >= '".$dateFrom." 00:00:00'
        //                     OR form_cut_piece.updated_at >= '".$dateFrom." 00:00:00'
        //                     OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
        //                     OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
        //                     OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
        //                 )
        //                 AND (
        //                     form_cut_input.waktu_mulai <= '".$dateTo." 23:59:59'
        //                     OR form_cut_input.waktu_selesai <= '".$dateTo." 23:59:59'
        //                     OR form_cut_piece.updated_at <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.created_at <= '".$dateTo." 23:59:59'
        //                     OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
        //                 )
        //                 ".$tanggal_filter."
        //                 ".$no_form_filter."
        //                 ".$no_cut_filter."
        //                 ".$color_filter."
        //                 ".$size_filter."
        //                 ".$dest_filter."
        //                 ".$year_sequence_filter."
        //                 ".$buyer_filter."
        //                 ".$ws_filter."
        //                 ".$style_filter."
        //                 ".$group_filter."
        //                 ".$shade_filter."
        //                 ".$ratio_filter."
        //             GROUP BY
        //                 stocker_input.form_cut_id,
        //                 stocker_input.form_reject_id,
        //                 stocker_input.form_piece_id,
        //                 stocker_input.so_det_id,
        //                 year_sequence_num.updated_at
        //             HAVING
        //                 (stocker_input.form_cut_id is not null or stocker_input.form_reject_id is not null or stocker_input.form_piece_id is not null)
        //                 ".$qty_filter."
        //                 ".$numbering_range_filter."
        //                 ".$stocker_filter."
        //                 ".$tipe_filter."
        //                 ".$part_filter."
        //                 ".$stocker_range_filter."
        //             ORDER BY
        //                 year_sequence_num.updated_at DESC
        //         ) stock_list
        //     ");
        // }
        $stockerList = DB::select("
            with
                stocker_label as (
                    select
                        id_qr_stocker,
                        so_det_id,
                        CONCAT( YEAR, '_', year_sequence ) year_sequence,
                        MIN( number ) range_numbering_awal,
                        MAX( number ) range_numbering_akhir,
                        MIN( year_sequence_number ) range_awal,
                        MAX( year_sequence_number ) range_akhir,
                        COUNT(*) as total,
                        updated_at,
                        (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                    from
                        year_sequence
                    where
                        year_sequence.updated_at between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'
                    group by
                        year_sequence.id_qr_stocker
                ),

                stocker_bundle as (
                    select
                        year_sequence_num.updated_at,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT(DISTINCT stocker_bundle.id_qr_stocker) id_qr_stocker_bundle,
                        master_part.nama_part part,
                        COALESCE(stocker_input.form_cut_id, stocker_input.form_reject_id, stocker_input.form_piece_id) form_cut_id,
                        master_sb_ws.ws act_costing_ws,
                        master_sb_ws.id_so_det so_det_id,
                        master_sb_ws.buyer,
                        master_sb_ws.styleno style,
                        UPPER(TRIM(master_sb_ws.color)) color,
                        master_sb_ws.size,
                        master_sb_ws.dest,
                        stocker_input.group_stocker,
                        stocker_input.shade,
                        stocker_input.ratio,
                        CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir )) stocker_range,
                        stocker_input.qty_ply qty_stocker,
                        COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
                        COALESCE(form_cut_input.no_cut, '-') no_cut,
                        COALESCE(part_com.panel, part.panel) panel,
                        year_sequence_num.year_sequence,
                        year_sequence_num.total qty,
                        CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
                        (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                    from
                        stocker_input
                        left join stocker_input stocker_bundle on stocker_bundle.form_cut_id <=> stocker_input.form_cut_id
                            AND stocker_bundle.form_reject_id <=> stocker_input.form_reject_id
                            AND stocker_bundle.form_piece_id  <=> stocker_input.form_piece_id
                            AND stocker_bundle.so_det_id      <=> stocker_input.so_det_id
                            AND stocker_bundle.group_stocker  <=> stocker_input.group_stocker
                            AND stocker_bundle.ratio          <=> stocker_input.ratio
                            AND stocker_bundle.stocker_reject <=> stocker_input.stocker_reject
                        inner join stocker_label year_sequence_num on year_sequence_num.id_qr_stocker = stocker_input.id_qr_stocker
                        left join part_detail on stocker_input.part_detail_id = part_detail.id
                        left join part on part.id = part_detail.part_id
                        left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                        left join part part_com on part_com.id = part_detail_com.part_id
                        LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                        LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                        LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                        LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
                    WHERE
                        stocker_input.id is not null
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
                        stocker_input.id_qr_stocker,
                        stocker_input.form_cut_id,
                        stocker_input.form_reject_id,
                        stocker_input.form_piece_id,
                        (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
                        stocker_input.so_det_id,
                        stocker_input.group_stocker,
                        stocker_input.ratio
                    HAVING
                        (stocker_input.form_cut_id is not null or stocker_input.form_reject_id is not null or stocker_input.form_piece_id is not null)
                        ".$qty_filter."
                        ".$numbering_range_filter."
                        ".$stocker_filter."
                        ".$part_filter."
                        ".$stocker_range_filter."
                )

                SELECT
                    COUNT(*) total_row,
                    SUM(qty) total_qty
                FROM
                (
                    select * from stocker_bundle
                ) total
        ");

        return $stockerList;
    }

    public function stockerListExport(Request $request) {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
        $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

        return Excel::download(new StockerListExport($dateFrom, $dateTo, $request->tanggal_filter, $request->no_form_filter, $request->no_cut_filter, $request->color_filter, $request->size_filter, $request->dest_filter, $request->qty_filter, $request->year_sequence_filter, $request->numbering_range_filter, $request->buyer_filter, $request->ws_filter, $request->style_filter, $request->stocker_filter, $request->part_filter, $request->group_filter, $request->shade_filter, $request->ratio_filter, $request->stocker_range_filter), 'production_excel.xlsx');
    }

    public function stockerListDetail($form_cut_id, $group_stocker, $ratio, $so_det_id, $normal = 1) {
        if (($form_cut_id && $group_stocker && $ratio && $so_det_id && $normal == 1) || ($form_cut_id && $so_det_id && $normal == 2) || ($form_cut_id && $so_det_id && $normal == 3)) {
            $months = [['angka' => '01','nama' => 'Januari'],['angka' => '02','nama' => 'Februari'],['angka' => '03','nama' => 'Maret'],['angka' => '04','nama' => 'April'],['angka' => '05','nama' => 'Mei'],['angka' => '06','nama' => 'Juni'],['angka' => '07','nama' => 'Juli'],['angka' => '08','nama' => 'Agustus'],['angka' => '09','nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            $formFilter = $normal == 1 ? "stocker_input.form_cut_id = '".$form_cut_id."' and" : ($normal == 2 ? "stocker_input.form_reject_id = '".$form_cut_id."' and" : ($normal  == 3 ? "stocker_input.form_piece_id = '".$form_cut_id."' and" : "stocker_input.form_cut_id = '".$form_cut_id."' and"));
            $yearSequenceFormFilter = $normal == 1 ? "year_sequence.form_cut_id = '".$form_cut_id."' and" : ($normal == 2 ? "year_sequence.form_reject_id = '".$form_cut_id."' and" : ($normal  == 3 ? "year_sequence.form_piece_id = '".$form_cut_id."' and" : "year_sequence.form_cut_id = '".$form_cut_id."' and"));

            $stockerList = DB::select("
                SELECT
                    GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker) id_qr_stocker,
                    GROUP_CONCAT(DISTINCT master_part.nama_part) part,
                    COALESCE(form_cut_input.id, form_cut_reject.id, form_cut_piece.id) form_cut_id,
                    stocker_input.act_costing_ws,
                    stocker_input.so_det_id,
                    master_sb_ws.buyer buyer,
                    master_sb_ws.styleno style,
                    UPPER(TRIM(master_sb_ws.color)) color,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
                    COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, 'REJECT') no_cut,
                    stocker_input.group_stocker,
                    stocker_input.shade,
                    stocker_input.ratio,
                    MIN(stocker_input.range_awal) range_awal,
                    MAX(stocker_input.range_akhir) range_akhir,
                    CONCAT(MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir)) stocker_range,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                FROM
                    stocker_input
                LEFT JOIN
                    part_detail on part_detail.id = stocker_input.part_detail_id
                LEFT JOIN
                    master_part on master_part.id = part_detail.master_part_id
                LEFT JOIN
                    master_sb_ws on master_sb_ws.id_so_det = stocker_input.so_det_id
                LEFT JOIN
                    form_cut_input on form_cut_input.id = stocker_input.form_cut_id
                LEFT JOIN
                    form_cut_reject on form_cut_reject.id = stocker_input.form_reject_id
                LEFT JOIN
                    form_cut_piece on form_cut_piece.id = stocker_input.form_piece_id
                WHERE
                    ".$formFilter."
                    ".($normal == 1 ? ("stocker_input.group_stocker = '".$group_stocker."' AND") : (""))."
                    ".($normal == 1 ? ("stocker_input.ratio = '".$ratio."' AND") : (""))."
                    stocker_input.so_det_id = '".$so_det_id."'
                GROUP BY
                    stocker_input.form_cut_id,
                    stocker_input.form_reject_id,
                    stocker_input.form_piece_id,
                    stocker_input.so_det_id,
                    stocker_input.group_stocker,
                    stocker_input.ratio
                ORDER BY
                    stocker_input.updated_at desc,
                    stocker_input.created_at desc,
                    form_cut_input.waktu_selesai desc,
                    form_cut_input.waktu_mulai desc
                LIMIT 1
            ");

            if ($stockerList[0]) {
                $stockerListNumber = YearSequence::selectRaw("
                    year_sequence.id_year_sequence,
                    year_sequence.number,
                    year_sequence.year,
                    year_sequence.year_sequence,
                    year_sequence.year_sequence_number,
                    master_sb_ws.size,
                    master_sb_ws.dest
                ")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
                whereRaw("
                    ".$yearSequenceFormFilter."
                    year_sequence.so_det_id = '".$so_det_id."' and
                    year_sequence.number >= '".$stockerList[0]->range_awal."' and
                    year_sequence.number <= '".$stockerList[0]->range_akhir."'
                ")->
                orderByRaw("CAST(year_sequence_number as UNSIGNED) ASC")->
                get();

                $output = DB::connection("mysql_sb")->
                    table("output_rfts")->
                    selectRaw("
                        output_rfts.kode_numbering,
                        so_det.id,
                        userpassword.username sewing_line,
                        coalesce(output_rfts.updated_at) sewing_update,
                        output_rfts_packing.created_by packing_line,
                        coalesce(output_rfts_packing.updated_at) packing_update
                    ")->
                    leftJoin("output_rfts_packing", "output_rfts_packing.kode_numbering", "=", "output_rfts.kode_numbering")->
                    leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->
                    leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rfts.created_by")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                    whereIn("output_rfts.kode_numbering", $stockerListNumber->pluck("id_year_sequence"))->
                    get();

                return view("stocker.stocker.stocker-list-detail", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "stocker-list", "stockerList" => $stockerList[0], "stockerListNumber" => $stockerListNumber, "output" => $output, "months" => $months, "years" => $years]);
            }
        }

        return redirect()->route('stocker-list');
    }

    public function stockerListDetailExport($form_cut_id, $group_stocker, $ratio, $so_det_id, $normal = 1) {
        ini_set("max_execution_time", 36000);

        return Excel::download(new StockerListDetailExport($form_cut_id, $group_stocker, $ratio, $so_det_id, $normal), 'stocker-list-detail.xlsx');
    }

    public function setMonthCountNumber(Request $request) {
        $validatedRequest = $request->validate([
            "month" => 'required',
            "year" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_month_count" => 'required',
            "range_akhir_month_count" => 'required',
        ]);

        if ($validatedRequest) {
            $currentData = MonthCount::selectRaw("
                    number
                ")->
                where('form_cut_id', $validatedRequest['form_cut_id'])->
                where('so_det_id', $validatedRequest['so_det_id'])->
                orderBy('number')->
                get();

            if ($validatedRequest['range_awal_month_count'] > 0 && $validatedRequest['range_awal_month_count'] <= $validatedRequest['range_akhir_month_count']) {

                $upsertData = [];

                $n = 0;
                $n1 = 0;
                for ($i = $validatedRequest['range_awal_month_count']; $i <= $validatedRequest['range_akhir_month_count']; $i++) {

                    if ($currentData->where('number', $validatedRequest['range_awal_stocker']+$n)->count() < 1) {
                        array_push($upsertData, [
                            "id_month_year" => $validatedRequest['year']."-".$validatedRequest['month']."_".($validatedRequest['range_awal_month_count'] + $n1),
                            "month_year" => $validatedRequest['year']."-".$validatedRequest['month'],
                            "month_year_number" => ($validatedRequest['range_awal_month_count'] + $n1),
                            "form_cut_id" => $validatedRequest['form_cut_id'],
                            "so_det_id" => $validatedRequest['so_det_id'],
                            "size" => $validatedRequest['size'],
                            "number" => $validatedRequest['range_awal_stocker']+$n,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                        ]);

                        $n1++;
                    }

                    $n++;
                }

                if (count($upsertData) > 0) {
                    MonthCount::upsert($upsertData, ['id_month_year', 'month_year', 'month_year_number'], ['form_cut_id', 'so_det_id', 'size', 'number', 'created_at', 'updated_at']);

                    $customPaper = array(0, 0, 35.35, 110.90);
                    $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearmonth-1', ["data" => $upsertData])->setPaper($customPaper);

                    $fileName = str_replace("/", "-", ('Month Count.pdf'));

                    return $pdf->download(str_replace("/", "_", $fileName));;
                }
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    // Set Year Sequence
    public function checkYearSequenceNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        $now = Carbon::now();

        $validatedRequest = $request->validate([
            "year" => 'required',
            "year_sequence" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_year_sequence" => 'required',
            "range_akhir_year_sequence" => 'required',
        ]);

        if ($validatedRequest) {
            $restrictYearSequence = YearSequence::where("year", $validatedRequest['year'])->where("year_sequence", $validatedRequest['year_sequence'])->whereBetween('year_sequence_number', [$validatedRequest['range_awal_year_sequence'], $validatedRequest['range_akhir_year_sequence']])->whereNotNull("so_det_id")->orderBy('year_sequence_number')->get();

            if ($restrictYearSequence->count() > 0) {

                return array(
                    "status" => 400,
                    "message" => "Kode <br><b>".($restrictYearSequence->implode('id_year_sequence', ' <br> '))."</b><br> Sudah di Regis"
                );
            }
        }

        return array(
            "status" => 200,
            "message" => "Range tersedia"
        );
    }

    public function setYearSequenceNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        $now = Carbon::now();

        $validatedRequest = $request->validate([
            "year" => 'required',
            "year_sequence" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_year_sequence" => 'required',
            "range_akhir_year_sequence" => 'required',
            "tipe" => 'required',
        ]);

        if ($validatedRequest) {
            // if ($request->replace) {
            //     $deleteYearSequence = YearSequence::where("year", $validatedRequest['year'])->
            //         where("year_sequence", $validatedRequest['year_sequence'])->
            //         where("form_cut_id", $validatedRequest['form_cut_id'])->
            //         where("so_det_id", $validatedRequest['so_det_id'])->
            //         where("number", ">=", $validatedRequest['range_awal_stocker'])->
            //         where("number", "<=", $validatedRequest['range_akhir_stocker'])->
            //         delete();
            // }
            $formColumn = $validatedRequest['tipe'] == 'PIECE' ? 'form_piece_id' : ($validatedRequest['tipe'] == 'REJECT' ? 'form_reject_id' : 'form_cut_id');

            $currentData = YearSequence::selectRaw("
                    number
                ")->
                where($formColumn, $validatedRequest['form_cut_id'])->
                where('so_det_id', $validatedRequest['so_det_id'])->
                where("number", ">=", $validatedRequest['range_awal_stocker'])->
                where("number", "<=", $validatedRequest['range_akhir_stocker'])->
                orderBy('number')->
                get();

            if ($validatedRequest['range_awal_year_sequence'] > 0 && $validatedRequest['range_awal_year_sequence'] <= $validatedRequest['range_akhir_year_sequence'] && $validatedRequest['range_akhir_year_sequence'] <= 999999 && $validatedRequest['year_sequence'] > 0) {
                $yearSequence = collect(
                    DB::select("
                        SELECT
                            `year`,
                            year_sequence,
                            MAX(year_sequence_number) year_sequence_number
                        FROM
                            `year_sequence`
                        WHERE
                            `year_sequence`.`year` = '".$validatedRequest['year']."'
                            AND `year_sequence`.`year_sequence` = '".$validatedRequest['year_sequence']."'
                        GROUP BY
                            `year`,
                            `year_sequence`
                    ")
                )->first();
                $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : $validatedRequest['year_sequence'];
                $yearSequenceNumber = $yearSequence ? $yearSequence->year_sequence_number + 1 : 1;

                $upsertData = [];
                $restrictData = [];

                $n = 0;
                $n1 = 0;
                $largeCount = 0;

                for ($i = $validatedRequest['range_awal_year_sequence']; $i <= $validatedRequest['range_akhir_year_sequence']; $i++) {
                    if ($i > 999999) {
                        $yearSequenceSequence = $yearSequenceSequence + 1;
                        $yearSequenceNumber = 1;
                    }

                    if ($currentData->where('number', $validatedRequest['range_awal_stocker']+$n)->count() < 1 || $request['method'] == "add" ) {
                        $currentNumber = ($currentData->count() > 0 ? $currentData->max("number")+1+$n : $validatedRequest['range_awal_stocker']+$n);

                        $currentYearSequence = YearSequence::where("id_year_sequence", $validatedRequest['year']."_".($yearSequenceSequence)."_".($validatedRequest['range_awal_year_sequence']+$n1))->first();

                        if (!($currentYearSequence && $currentYearSequence->so_det_id)) {
                            array_push($upsertData, [
                                "id_year_sequence" => $validatedRequest['year']."_".($yearSequenceSequence)."_".($validatedRequest['range_awal_year_sequence']+$n1),
                                "year" => $validatedRequest['year'],
                                "year_sequence" => $yearSequenceSequence,
                                "year_sequence_number" => ($validatedRequest['range_awal_year_sequence']+$n1),
                                $formColumn => $validatedRequest['form_cut_id'],
                                "so_det_id" => $validatedRequest['so_det_id'],
                                "size" => $validatedRequest['size'],
                                "number" => ($currentNumber > $validatedRequest['range_akhir_stocker'] ? $validatedRequest['range_akhir_stocker'] : ($currentNumber)),
                                "id_qr_stocker" => $request["id_qr_stocker"],
                                "created_at" => $now,
                                "updated_at" => $now,
                            ]);

                            if (count($upsertData) % 5000 == 0) {
                                YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], [$formColumn, 'so_det_id', 'size', 'number', 'id_qr_stocker', 'created_at', 'updated_at']);

                                $upsertData = [];

                                $largeCount++;
                            }

                            $n1++;
                        } else {
                            array_push($restrictData, $validatedRequest['year']."_".($yearSequenceSequence)."_".($validatedRequest['range_awal_year_sequence']+$n1));
                        }
                    }

                    $n++;
                }

                if (count($upsertData) > 0 || $largeCount > 0) {
                    if (count($upsertData) > 0) {
                        YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], [$formColumn, 'so_det_id', 'size', 'number', 'id_qr_stocker', 'created_at', 'updated_at']);
                    }

                    $stockerData = Stocker::where("id_qr_stocker", $request->id_qr_stocker)->first();

                    $customPaper = array(0,0,275,175);
                    $pdf = PDF::loadView('stocker.stocker.pdf.print-year-sequence-stock', ["stockerData" => $stockerData, "year_sequence" => $validatedRequest['year']."_".($yearSequenceSequence), "range_awal" => $validatedRequest['range_awal_year_sequence'], "range_akhir" => $validatedRequest['range_akhir_year_sequence']])->setPaper($customPaper);

                    $fileName = str_replace("/", "-", ('Stock Year Sequence.pdf'));

                    return $pdf->download(str_replace("/", "_", $fileName));;
                } else {
                    return array(
                        "status" => 400,
                        "message" => "Fkin Hell"
                    );
                }
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function checkAllStockNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
        $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

        // Convert the dates to timestamps
        $timestampFrom = strtotime($dateFrom);
        $timestampTo = strtotime($dateTo);

        // Calculate the difference in seconds
        $diffInSeconds = abs($timestampTo - $timestampFrom);

        // Convert seconds to days
        $daysInterval = $diffInSeconds / (60 * 60 * 24);

        // Limit to 1 month
        if ($daysInterval > 30) {
            $dateTo = date("Y-m-d", strtotime($dateFrom . " +30 days"));
        }

        $tanggal_filter = "";
        if ($request->tanggal_filter) {
            $tanggal_filter = "AND year_sequence_num.updated_at LIKE '%".$request->tanggal_filter."%' ";
        }
        $no_form_filter = "";
        if ($request->no_form_filter) {
            $no_form_filter = "AND COALESCE(form_cut_input.no_form, form_cut_piece.no_form, form_cut_reject.no_form) LIKE '%".$request->no_form_filter."%' ";
        }
        $no_cut_filter = "";
        if ($request->no_cut_filter) {
            $no_cut_filter = "AND COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') LIKE '%".$request->no_cut_filter."%' ";
        }
        $color_filter = "";
        if ($request->color_filter) {
            $color_filter = "AND UPPER(TRIM(master_sb_ws.color)) LIKE '%".strtoupper(trim($request->color_filter))."%' ";
        }
        $size_filter = "";
        if ($request->size_filter) {
            $size_filter = "AND master_sb_ws.size LIKE '%".$request->size_filter."%' ";
        }
        $dest_filter = "";
        if ($request->dest_filter) {
            $dest_filter = "AND master_sb_ws.dest LIKE '%".$request->dest_filter."%' ";
        }
        $qty_filter = "";
        if ($request->qty_filter) {
            $qty_filter = "AND (MAX(year_sequence_num.range_akhir) - MIN(year_sequence_num.range_awal) + 1) LIKE '%".$request->qty_filter."%' ";
        }
        $year_sequence_filter = "";
        if ($request->year_sequence_filter) {
            $year_sequence_filter = "AND year_sequence_num.year_sequence LIKE '%".$request->year_sequence_filter."%' ";
        }
        $numbering_range_filter = "";
        if ($request->numbering_range_filter) {
            $numbering_range_filter = "AND CONCAT( MIN(year_sequence_num.range_awal), ' - ', MAX(year_sequence_num.range_akhir) ) LIKE '%".$request->numbering_range_filter."%' ";
        }
        $buyer_filter = "";
        if ($request->buyer_filter) {
            $buyer_filter = "AND master_sb_ws.buyer LIKE '%".$request->buyer_filter."%' ";
        }
        $ws_filter = "";
        if ($request->ws_filter) {
            $ws_filter = "AND master_sb_ws.ws LIKE '%".$request->ws_filter."%' ";
        }
        $style_filter = "";
        if ($request->style_filter) {
            $style_filter = "AND master_sb_ws.styleno LIKE '%".$request->style_filter."%' ";
        }
        $stocker_filter = "";
        if ($request->stocker_filter) {
            $stocker_filter = "AND GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker) LIKE '%".$request->stocker_filter."%' ";
        }
        $tipe_filter = "";
        if ($request->tipe_filter) {
            $tipe_filter = "AND tipe LIKE '%".$request->tipe_filter."%' ";
        }
        $part_filter = "";
        if ($request->part_filter) {
            $part_filter = "AND GROUP_CONCAT(DISTINCT master_part.nama_part) LIKE '%".$request->part_filter."%' ";
        }
        $group_filter = "";
        if ($request->group_filter) {
            $group_filter = "AND stocker_input.group_stocker LIKE '%".$request->group_filter."%' ";
        }
        $shade_filter = "";
        if ($request->shade_filter) {
            $shade_filter = "AND stocker_input.shade LIKE '%".$request->shade_filter."%' ";
        }
        $ratio_filter = "";
        if ($request->ratio_filter) {
            $ratio_filter = "AND stocker_input.ratio LIKE '%".$request->ratio_filter."%' ";
        }
        $stocker_range_filter = "";
        if ($request->stocker_range_filter) {
            $stocker_range_filter = "AND CONCAT( MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir) ) LIKE '%".$request->stocker_range_filter."%' ";
        }

        // Deprecated
        // if ($daysInterval > 3) {
        //     $stockerList = DB::select("
        //         SELECT
        //             year_sequence_num.updated_at,
        //             stocker_input.id_qr_stocker,
        //             stocker_input.part,
        //             stocker_input.form_cut_id,
        //             stocker_input.act_costing_ws,
        //             stocker_input.so_det_id,
        //             stocker_input.buyer,
        //             stocker_input.style,
        //             UPPER(TRIM(stocker_input.color)) color,
        //             stocker_input.size,
        //             stocker_input.dest,
        //             stocker_input.group_stocker,
        //             stocker_input.shade,
        //             stocker_input.ratio,
        //             stocker_input.stocker_range,
        //             stocker_input.qty_stocker,
        //             stocker_input.no_form,
        //             stocker_input.no_cut,
        //             stocker_input.panel,
        //             year_sequence_num.year_sequence,
        //             ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
        //             CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
        //             stocker_input.tipe
        //         FROM
        //             (
        //                 SELECT
        //                     ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
        //                     so_det_id,
        //                     CONCAT( YEAR, '_', year_sequence ) year_sequence,
        //                     MIN( number ) range_numbering_awal,
        //                     MAX( number ) range_numbering_akhir,
        //                     MIN( year_sequence_number ) range_awal,
        //                     MAX( year_sequence_number ) range_akhir,
        //                     id_qr_stocker,
        //                     updated_at,
        //                     (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //                 FROM
        //                     year_sequence
        //                 WHERE
        //                     year_sequence.so_det_id IS NOT NULL
        //                     AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
        //                     AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
        //                 GROUP BY
        //                     form_cut_id,
        //                     form_reject_id,
        //                     form_piece_id,
        //                     so_det_id,
        //                     id_qr_stocker,
        //                     COALESCE ( updated_at, created_at )
        //             ) year_sequence_num
        //             LEFT JOIN (
        //                 SELECT
        //                     GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
        //                     ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
        //                     stocker_input.act_costing_ws,
        //                     stocker_input.so_det_id,
        //                     master_sb_ws.buyer buyer,
        //                     master_sb_ws.styleno style,
        //                     UPPER(TRIM(master_sb_ws.color)) color,
        //                     master_sb_ws.size,
        //                     master_sb_ws.dest,
        //                     stocker_input.part_detail_id,
        //                     stocker_input.shade,
        //                     stocker_input.group_stocker,
        //                     stocker_input.ratio,
        //                     stocker_input.range_awal,
        //                     stocker_input.range_akhir,
        //                     stocker_input.created_at,
        //                     stocker_input.updated_at,
        //                     COALESCE(form_cut_input.waktu_mulai, form_cut_reject.created_at, form_cut_piece.created_at) waktu_mulai,
        //                     COALESCE(form_cut_input.waktu_selesai, form_cut_reject.updated_at, form_cut_piece.updated_at) waktu_selesai,
        //                     COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
        //                     COALESCE(form_cut_input.no_cut, form_cut_piece.no_form, '-') no_cut,
        //                     COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
        //                     GROUP_CONCAT( DISTINCT CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL THEN CONCAT(' - ', part_detail.part_status) ELSE '' END)) ) part,
        //                     CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
        //                     ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
        //                     (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //                 FROM
        //                     stocker_input
        //                     left join part_detail on stocker_input.part_detail_id = part_detail.id
        //                     left join part on part.id = part_detail.part_id
        //                     left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
        //                     left join part part_com on part_com.id = part_detail_com.part_id
        //                     LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
        //                     LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
        //                     LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
        //                     LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
        //                     LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
        //                 GROUP BY
        //                     stocker_input.id_qr_stocker,
        //                     stocker_input.form_cut_id,
        //                     stocker_input.form_reject_id,
        //                     stocker_input.form_piece_id,
        //                     (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
        //                     stocker_input.so_det_id,
        //                     stocker_input.group_stocker,
        //                     stocker_input.ratio
        //             ) stocker_input ON
        //             -- (year_sequence_num.form_cut_id = stocker_input.form_cut_id and year_sequence_num.tipe = stocker_input.tipe AND year_sequence_num.so_det_id = stocker_input.so_det_id
        //             -- AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
        //             -- AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED))
        //             -- OR
        //             stocker_input.id_qr_stocker = year_sequence_num.id_qr_stocker
        //             WHERE
        //             (
        //                 stocker_input.waktu_mulai >='".$dateFrom." 00:00:00'
        //                 OR stocker_input.waktu_selesai >= '".$dateFrom." 00:00:00'
        //                 OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
        //                 OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
        //                 OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
        //             )
        //             AND (
        //                 stocker_input.waktu_mulai <= '".$dateTo." 23:59:59'
        //                 OR stocker_input.waktu_selesai <= '".$dateTo." 23:59:59'
        //                 OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
        //                 OR stocker_input.created_at <= '".$dateTo." 23:59:59'
        //                 OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
        //             )
        //             AND
        //             year_sequence_num.so_det_id is not null
        //             ".$tanggal_filter."
        //             ".$no_form_filter."
        //             ".$no_cut_filter."
        //             ".$color_filter."
        //             ".$size_filter."
        //             ".$dest_filter."
        //             ".$year_sequence_filter."
        //             ".$buyer_filter."
        //             ".$ws_filter."
        //             ".$style_filter."
        //             ".$group_filter."
        //             ".$shade_filter."
        //             ".$ratio_filter."
        //         GROUP BY
        //             stocker_input.id_qr_stocker,
        //             stocker_input.form_cut_id,
        //             stocker_input.tipe,
        //             stocker_input.so_det_id,
        //             year_sequence_num.updated_at
        //         HAVING
        //             stocker_input.form_cut_id is not null
        //             ".$qty_filter."
        //             ".$numbering_range_filter."
        //             ".$stocker_filter."
        //             ".$tipe_filter."
        //             ".$part_filter."
        //             ".$stocker_range_filter."
        //         ORDER BY
        //             year_sequence_num.updated_at DESC
        //     ");
        // } else {
        //     $stockerList = DB::select("
        //         SELECT
        //                 year_sequence_num.updated_at,
        //                 GROUP_CONCAT( DISTINCT stocker_input.id_qr_stocker ) id_qr_stocker,
        //                 GROUP_CONCAT( DISTINCT master_part.nama_part ) part,
        //                 COALESCE(form_cut_input.id, form_cut_reject.id, form_cut_piece.id) form_cut_id,
        //                 stocker_input.act_costing_ws,
        //                 stocker_input.so_det_id,
        //                 master_sb_ws.buyer buyer,
        //                 master_sb_ws.styleno style,
        //                 UPPER(TRIM(master_sb_ws.color)) color,
        //                 master_sb_ws.size,
        //                 master_sb_ws.dest,
        //                 COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
        //                 GROUP_CONCAT( DISTINCT CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL THEN CONCAT(' - ', part_detail.part_status) ELSE '' END)) ) part,
        //                 COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
        //                 COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') no_cut,
        //                 stocker_input.group_stocker,
        //                 stocker_input.shade,
        //                 stocker_input.ratio,
        //                 CONCAT( MIN( stocker_input.range_awal ), '-', MAX( stocker_input.range_akhir )) stocker_range,
        //                 ( MAX( stocker_input.range_akhir ) - MIN( stocker_input.range_awal ) + 1 ) qty_stocker,
        //                 year_sequence_num.year_sequence,
        //                 ( MAX( year_sequence_num.range_akhir ) - MIN( year_sequence_num.range_awal ) + 1 ) qty,
        //                 CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
        //                 (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //             FROM
        //                 stocker_input
        //                 left join part_detail on stocker_input.part_detail_id = part_detail.id
        //                 left join part on part.id = part_detail.part_id
        //                 left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
        //                 left join part part_com on part_com.id = part_detail_com.part_id
        //                 LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
        //                 LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
        //                 LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
        //                 LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
        //                 LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
        //                 INNER JOIN (
        //                     SELECT
        //                         ( CASE WHEN form_cut_id > 0 THEN form_cut_id ELSE ( CASE WHEN form_reject_id > 0 THEN form_reject_id ELSE ( CASE WHEN form_piece_id > 0 THEN form_piece_id ELSE null END ) END ) END ) form_cut_id,
        //                         so_det_id,
        //                         CONCAT( `year`, '_', year_sequence ) year_sequence,
        //                         MIN( number ) range_numbering_awal,
        //                         MAX( number ) range_numbering_akhir,
        //                         MIN( year_sequence_number ) range_awal,
        //                         MAX( year_sequence_number ) range_akhir,
        //                         id_qr_stocker,
        //                         COALESCE ( updated_at, created_at ) updated_at,
        //                         (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
        //                     FROM
        //                         year_sequence
        //                     WHERE
        //                         year_sequence.so_det_id IS NOT NULL
        //                         AND year_sequence.updated_at >= '".$dateFrom." 00:00:00'
        //                         AND year_sequence.updated_at <= '".$dateTo." 23:59:59'
        //                     GROUP BY
        //                         id_qr_stocker,
        //                         form_cut_id,
        //                         form_reject_id,
        //                         form_piece_id,
        //                         (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
        //                         so_det_id,
        //                         COALESCE ( updated_at, created_at )
        //                     ORDER BY
        //                         COALESCE ( updated_at, created_at)
        //                 ) year_sequence_num ON
        //                 -- (year_sequence_num.form_cut_id = (CASE WHEN year_sequence_num.tipe = 'PIECE' THEN stocker_input.form_piece_id ELSE (CASE WHEN year_sequence_num.tipe = 'REJECT' THEN stocker_input.form_reject_id ELSE stocker_input.form_cut_id END) END)
        //                 -- AND year_sequence_num.so_det_id = stocker_input.so_det_id
        //                 -- AND CAST(year_sequence_num.range_numbering_awal AS UNSIGNED) >= CAST(stocker_input.range_awal AS UNSIGNED)
        //                 -- AND CAST(year_sequence_num.range_numbering_akhir AS UNSIGNED) <= CAST(stocker_input.range_akhir AS UNSIGNED))
        //                 -- OR
        //                 stocker_input.id_qr_stocker = year_sequence_num.id_qr_stocker
        //             WHERE
        //                 (
        //                     form_cut_input.waktu_mulai >= '".$dateFrom." 00:00:00'
        //                     OR form_cut_input.waktu_selesai >= '".$dateFrom." 00:00:00'
        //                     OR form_cut_reject.updated_at >= '".$dateFrom." 00:00:00'
        //                     OR form_cut_piece.updated_at >= '".$dateFrom." 00:00:00'
        //                     OR stocker_input.updated_at >= '".$dateFrom." 00:00:00'
        //                     OR stocker_input.created_at >= '".$dateFrom." 00:00:00'
        //                     OR year_sequence_num.updated_at >= '".$dateFrom." 00:00:00'
        //                 )
        //                 AND (
        //                     form_cut_input.waktu_mulai <= '".$dateTo." 23:59:59'
        //                     OR form_cut_input.waktu_selesai <= '".$dateTo." 23:59:59'
        //                     OR form_cut_reject.updated_at <= '".$dateTo." 23:59:59'
        //                     OR form_cut_piece.updated_at <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.updated_at <= '".$dateTo." 23:59:59'
        //                     OR stocker_input.created_at <= '".$dateTo." 23:59:59'
        //                     OR year_sequence_num.updated_at <= '".$dateTo." 23:59:59'
        //                 )
        //                 AND
        //                 year_sequence_num.so_det_id is not null
        //                 ".$tanggal_filter."
        //                 ".$no_form_filter."
        //                 ".$no_cut_filter."
        //                 ".$color_filter."
        //                 ".$size_filter."
        //                 ".$dest_filter."
        //                 ".$year_sequence_filter."
        //                 ".$buyer_filter."
        //                 ".$ws_filter."
        //                 ".$style_filter."
        //                 ".$group_filter."
        //                 ".$shade_filter."
        //                 ".$ratio_filter."
        //             GROUP BY
        //                 stocker_input.id_qr_stocker,
        //                 stocker_input.form_cut_id,
        //                 stocker_input.form_reject_id,
        //                 stocker_input.form_piece_id,
        //                 stocker_input.so_det_id,
        //                 year_sequence_num.updated_at
        //             HAVING
        //                 (stocker_input.form_cut_id is not null or stocker_input.form_reject_id is not null or stocker_input.form_piece_id is not null)
        //                 ".$qty_filter."
        //                 ".$numbering_range_filter."
        //                 ".$stocker_filter."
        //                 ".$tipe_filter."
        //                 ".$part_filter."
        //                 ".$stocker_range_filter."
        //             ORDER BY
        //                 year_sequence_num.updated_at DESC
        //     ");
        // }

        $stockerList = DB::select("
            with
                stocker_label as (
                    select
                        id_qr_stocker,
                        so_det_id,
                        CONCAT( YEAR, '_', year_sequence ) year_sequence,
                        MIN( number ) range_numbering_awal,
                        MAX( number ) range_numbering_akhir,
                        MIN( year_sequence_number ) range_awal,
                        MAX( year_sequence_number ) range_akhir,
                        COUNT(*) as total,
                        updated_at,
                        (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                    from
                        year_sequence
                    where
                        year_sequence.updated_at between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'
                    group by
                        year_sequence.id_qr_stocker
                ),

                stocker_bundle as (
                    select
                        year_sequence_num.updated_at,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT(DISTINCT stocker_bundle.id_qr_stocker) id_qr_stocker_bundle,
                        master_part.nama_part part,
                        COALESCE(stocker_input.form_cut_id, stocker_input.form_reject_id, stocker_input.form_piece_id) form_cut_id,
                        master_sb_ws.ws act_costing_ws,
                        master_sb_ws.id_so_det so_det_id,
                        master_sb_ws.buyer,
                        master_sb_ws.styleno style,
                        UPPER(TRIM(master_sb_ws.color)) color,
                        master_sb_ws.size,
                        master_sb_ws.dest,
                        stocker_input.group_stocker,
                        stocker_input.shade,
                        stocker_input.ratio,
                        CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir )) stocker_range,
                        stocker_input.qty_ply qty_stocker,
                        COALESCE(form_cut_input.no_form, form_cut_reject.no_form, form_cut_piece.no_form) no_form,
                        COALESCE(form_cut_input.no_cut, '-') no_cut,
                        COALESCE(part_com.panel, part.panel) panel,
                        year_sequence_num.year_sequence,
                        year_sequence_num.total qty,
                        CONCAT( MIN( year_sequence_num.range_awal ), ' - ', MAX( year_sequence_num.range_akhir )) numbering_range,
                        (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                    from
                        stocker_input
                        left join stocker_input stocker_bundle on stocker_bundle.form_cut_id <=> stocker_input.form_cut_id
                            AND stocker_bundle.form_reject_id <=> stocker_input.form_reject_id
                            AND stocker_bundle.form_piece_id  <=> stocker_input.form_piece_id
                            AND stocker_bundle.so_det_id      <=> stocker_input.so_det_id
                            AND stocker_bundle.group_stocker  <=> stocker_input.group_stocker
                            AND stocker_bundle.ratio          <=> stocker_input.ratio
                            AND stocker_bundle.stocker_reject <=> stocker_input.stocker_reject
                        inner join stocker_label year_sequence_num on year_sequence_num.id_qr_stocker = stocker_input.id_qr_stocker
                        left join part_detail on stocker_input.part_detail_id = part_detail.id
                        left join part on part.id = part_detail.part_id
                        left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                        left join part part_com on part_com.id = part_detail_com.part_id
                        LEFT JOIN master_part ON master_part.id = part_detail.master_part_id
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                        LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                        LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                        LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
                    WHERE
                        stocker_input.id is not null
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
                        stocker_input.id_qr_stocker,
                        stocker_input.form_cut_id,
                        stocker_input.form_reject_id,
                        stocker_input.form_piece_id,
                        (CASE WHEN form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END),
                        stocker_input.so_det_id,
                        stocker_input.group_stocker,
                        stocker_input.ratio
                    HAVING
                        (stocker_input.form_cut_id is not null or stocker_input.form_reject_id is not null or stocker_input.form_piece_id is not null)
                        ".$qty_filter."
                        ".$numbering_range_filter."
                        ".$stocker_filter."
                        ".$part_filter."
                        ".$stocker_range_filter."
                )

                select * from stocker_bundle
        ");

        return $stockerList;
    }

    public function printStockNumber(Request $request) {
        ini_set("max_execution_time", 36000);

        if ($request->stockNumbers && count($request->stockNumbers) > 0) {
            $customPaper = array(0,0,275,175);
            $pdf = PDF::loadView('stocker.stocker.pdf.print-year-sequence-stocks', ["stockNumbers" => $request->stockNumbers])->setPaper($customPaper);

            $fileName = str_replace("/", "-", ('Stock Year Sequence.pdf'));

            return $pdf->download(str_replace("/", "_", $fileName));;
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function deleteYearSequence(Request $request) {
        ini_set("max_execution_time", 36000);

        $validatedRequest = $request->validate([
            "year" => 'required',
            "year_sequence" => 'required',
            "form_cut_id" => 'required',
            "so_det_id" => 'required',
            "size" => 'required',
            "range_awal_stocker" => 'required',
            "range_akhir_stocker" => 'required',
            "range_awal_year_sequence" => 'required',
            "range_akhir_year_sequence" => 'required',
        ]);

        $deleteYearSequence = YearSequence::where("year", $validatedRequest['year'])->
            where("year_sequence", $validatedRequest['year_sequence'])->
            where("form_cut_id", $validatedRequest['form_cut_id'])->
            where("so_det_id", $validatedRequest['so_det_id'])->
            where("number", ">=", $validatedRequest['range_awal_stocker'])->
            delete();
    }

    public function customMonthCount() {
        $months = [['angka' => '01','nama' => 'Januari'],['angka' => '02','nama' => 'Februari'],['angka' => '03','nama' => 'Maret'],['angka' => '04','nama' => 'April'],['angka' => '05','nama' => 'Mei'],['angka' => '06','nama' => 'Juni'],['angka' => '07','nama' => 'Juli'],['angka' => '08','nama' => 'Agustus'],['angka' => '09','nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view("stocker.stocker.month-count", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "month-count", "months" => $months,  "years" => $years]);
    }

    public function yearSequence() {
        $years = array_reverse(range(1999, date('Y', strtotime('+1 years'))));

        return view("stocker.stocker.year-sequence", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "year-sequence", "years" => $years]);
    }

    public function printYearSequence(Request $request) {
        ini_set("max_execution_time", 360000);
        ini_set("memory_limit", '2048M');

        $method = $request['method'] ? $request['method'] : 'qty';
        $yearSequenceYear = $request->year ? $request->year : Carbon::now()->format('Y');
        $yearSequenceSequence = $request->yearSequence ? $request->yearSequence : 0;
        $qty = $request->qty ? $request->qty : 0;
        $rangeAwal = $request->rangeAwal ? $request->rangeAwal : 0;
        $rangeAkhir = $request->rangeAkhir ? $request->rangeAkhir : 0;

        if ($method == 'qty' && $qty > 0) {
            $insertData = [];

            $yearSequence = YearSequence::selectRaw("year_sequence, year_sequence_number")->where("year", $yearSequenceYear)->where("year_sequence", $yearSequenceSequence)->orderBy("year_sequence", "desc")->orderBy("year_sequence_number", "desc")->first();
            $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : $yearSequenceSequence;
            $yearSequenceNumber = $yearSequence ? $yearSequence->year_sequence_number + 1 : 1;

            for ($i = 0; $i < $qty; $i++) {
                if ($yearSequenceNumber > 999999) {
                    $yearSequenceSequence = $yearSequenceSequence + 1;
                    $yearSequenceNumber = 1;
                }

                array_push($insertData, [
                    "id_year_sequence" => $yearSequenceYear."_".sprintf('%03d', $yearSequenceSequence)."_".$yearSequenceNumber,
                    "year" => $yearSequenceYear,
                    "year_sequence" => $yearSequenceSequence,
                    "year_sequence_number" => $yearSequenceNumber,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);

                $yearSequenceNumber++;
            }

            if (count($insertData) > 0) {
                YearSequence::insert($insertData);

                $customPaper = array(0, 0, 35.35, 110.90);
                $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence', ["data" => $insertData])->setPaper($customPaper);

                $fileName = str_replace("/", "-", ('Year Sequence.pdf'));

                return $pdf->download(str_replace("/", "_", $fileName));
            }

            return array(
                "status" => 400,
                "message" => "Something went wrong",
            );
        } else if ($method == 'range' && $rangeAwal > 0 && $rangeAkhir > 0 && $rangeAwal <= $rangeAkhir && $rangeAkhir <= 999999) {
            $upsertData = [];

            $yearSequence = YearSequence::selectRaw("year_sequence, year_sequence_number")->where("year", $yearSequenceYear)->where("year_sequence", $yearSequenceSequence)->orderBy("year_sequence", "desc")->orderBy("year_sequence_number", "desc")->first();
            $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : $yearSequenceSequence;

            for ($i = $rangeAwal; $i <= $rangeAkhir; $i++) {

                array_push($upsertData, [
                    "id_year_sequence" => $yearSequenceYear."_".$yearSequenceSequence."_".$i,
                    "year" => $yearSequenceYear,
                    "year_sequence" => $yearSequenceSequence,
                    "year_sequence_number" => $i,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
            }

            if (count($upsertData) > 0) {
                YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], ['created_at', 'updated_at']);

                $customPaper = array(0, 0, 35.35, 110.90);
                $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence', ["data" => $upsertData])->setPaper($customPaper);

                $fileName = str_replace("/", "-", ('Year Sequence.pdf'));

                return $pdf->download(str_replace("/", "_", $fileName));;
            }
        }

        return array(
            "status" => 400,
            "message" => "Data kosong",
        );
    }

    public function printYearSequenceNew(Request $request) {
        $yearSequence = YearSequence::selectRaw("size, id_year_sequence, year, year_sequence, year_sequence_number")->
            where("year", $request->year)->
            where("year_sequence", $request->yearSequence)->
            where("year_sequence_number", ">=", $request->rangeAwal)->
            where("year_sequence_number", "<=", $request->rangeAkhir)->
            orderBy("year_sequence", "asc")->
            orderBy("year_sequence_number", "asc")->
            get()->toArray();

        $customPaper = array(0, 0, 35.35, 110.90);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence-1', ["data" => $yearSequence])->setPaper($customPaper);

        $fileName = str_replace("/", "-", ('Year Sequence.pdf'));

        return $pdf->download(str_replace("/", "_", $fileName));;
    }

    public function printYearSequenceNewFormat(Request $request) {
        $yearSequence = YearSequence::selectRaw("(CASE WHEN COALESCE(master_sb_ws.reff_no, '-') != '-' THEN master_sb_ws.reff_no ELSE master_sb_ws.styleno END) style, UPPER(TRIM(master_sb_ws.color)) color, master_sb_ws.size, id_year_sequence, year, year_sequence, year_sequence_number")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            where("year", $request->year)->
            where("year_sequence", $request->yearSequence)->
            where("year_sequence_number", ">=", $request->rangeAwal)->
            where("year_sequence_number", "<=", $request->rangeAkhir)->
            orderBy("year_sequence", "asc")->
            orderBy("year_sequence_number", "asc")->
            get()->
            toArray();

        $customPaper = array(0, 0, 35.35, 110.90);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-numbering-yearsequence-1-new', ["data" => $yearSequence])->setPaper($customPaper);

        $fileName = str_replace("/", "-", ('Year Sequence.pdf'));

        return $pdf->download(str_replace("/", "_", $fileName));
    }

    public function getStocker(Request $request) {
        if ($request->stocker) {
            $stockerData = Stocker::selectRaw("
                    stocker_input.id_qr_stocker,
                    COALESCE(form_cut_input.id, form_cut_piece.id, form_cut_reject.id) form_cut_id,
                    stocker_input.so_det_id,
                    stocker_input.act_costing_ws,
                    part.act_costing_id,
                    part.style,
                    UPPER(TRIM(stocker_input.color)) color,
                    stocker_input.size,
                    stocker_input.act_costing_ws,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    COALESCE(form_cut_input.no_form, form_cut_piece.no_form, form_cut_reject.no_form) no_form,
                    (
                        (COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                        (COALESCE ( MAX(dc_in_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(dc_in_input.qty_replace), 0 )) -
                        (COALESCE ( MAX(secondary_in_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(secondary_in_input.qty_replace), 0 )) -
                        (COALESCE ( MAX(secondary_inhouse_input.qty_reject), 0 )) +
                        (COALESCE ( MAX(secondary_inhouse_input.qty_replace), 0 ))
                    ) qty,
                    stocker_input.range_awal,
                    stocker_input.range_akhir,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
                ")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_detail as part_detail_com", function ($join) {
                    $join->on("part_detail_com.id", "=", "part_detail.from_part_detail");
                    $join->on("part_detail.part_status", "=", DB::raw("'complement'"));
                })->
                leftJoin("part as part_com", "part_com.id", "=", "part_detail_com.part_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
                leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
                leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                where("stocker_input.id_qr_stocker", $request->stocker)->
                first();

            if ($stockerData) {
                return json_encode($stockerData);
            }

            return array(
                "status" => "400",
                "message" => "Stocker tidak ditemukan",
            );
        }

        return array(
            "status" => "400",
            "message" => "Stocker tidak valid",
        );
    }

    public function getStockerMonthCount(Request $request) {
        $stockerListNumber = MonthCount::selectRaw("
                month_count.id_month_year,
                month_count.number,
                month_count.month_year,
                month_count.month_year_number,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "month_count.so_det_id")->
            whereRaw("
                month_count.form_cut_id = '".$request->form_cut_id."' and
                month_count.so_det_id = '".$request->so_det_id."' and
                (month_count.number >= '".$request->range_awal."' and month_count.number <= '".$request->range_akhir."')
            ")->
            get();

        return Datatables::of($stockerListNumber)->toJson();
    }

    public function getStockerYearSequence(Request $request) {
        $yearSequenceFormFilter = $request->tipe == 'PIECE' ? "year_sequence.form_piece_id = '".$request->form_cut_id."' and" : ($request->tipe == 'REJECT' ? "year_sequence.form_reject_id = '".$request->form_cut_id."' and" : "year_sequence.form_cut_id = '".$request->form_cut_id."' and");

        $stockerListNumber = YearSequence::selectRaw("
                year_sequence.id_year_sequence,
                year_sequence.number,
                year_sequence.year,
                year_sequence.year_sequence,
                year_sequence.year_sequence_number,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            whereRaw("
                ".$yearSequenceFormFilter."
                year_sequence.so_det_id = '".$request->so_det_id."' and
                (year_sequence.number >= '".$request->range_awal."') and
                (year_sequence.number <= '".$request->range_akhir."')
            ")->
            orderByRaw("CAST(year_sequence_number AS UNSIGNED) asc")->
            get();

        return Datatables::of($stockerListNumber)->toJson();
    }

    public function getRangeMonthCount(Request $request) {
        if ($request->month && $request->year) {

            $monthYear = $request->year."-".$request->month;

            $availableMonthCount = MonthCount::selectRaw("
                    month_year,
                    month_year_number
                ")->
                where("month_count.month_year", $monthYear)->
                whereRaw('number IS NOT NULL')->
                whereRaw('(form_cut_id IS NOT NULL OR form_piece_id IS NOT NULL OR form_reject_id IS NOT NULL)')->
                whereRaw('so_det_id IS NOT NULL')->
                orderBy('month_year_number', 'desc')->
                first();

            if ($availableMonthCount) {
                return json_encode($availableMonthCount);
            } else {
                return json_encode(["month_year" => $monthYear, "month_year_number" => 1]);
            }
        }

        return array(
            "status" => 400,
            "message" => "Bulan dan tahun tidak valid",
        );
    }

    public function getSequenceYearSequence(Request $request) {
        if ($request->year) {
            $availableYearSequence = DB::select("
                SELECT `year_sequence`
                FROM `year_sequence`
                WHERE `year` = '".$request->year."'
                ORDER BY `year_sequence` DESC
                LIMIT 1
            ");

            $max = $availableYearSequence[0]->year_sequence ?? 0;

            $sequenceList = range(0, $max+1);

            if (count($sequenceList) > 0) {
                return json_encode($sequenceList);
            } else {
                return json_encode([["year" => $request->year, "year_sequence" => 1]]);
            }
        }

        return array(
            "status" => 400,
            "message" => "Tahun tidak valid",
        );
    }

    public function getRangeYearSequence(Request $request) {
        if (($request->year != null || $request->year != "") && $request->sequence) {

            $availableYearSequence = collect(DB::select("
                SELECT
                    `year`,
                    year_sequence,
                    MAX(year_sequence_number) year_sequence_number
                FROM
                    `year_sequence`
                WHERE
                    `year_sequence`.`year` = '".$request->year."'
                    AND `year_sequence`.`year_sequence` = '".$request->sequence."'
                GROUP BY
                    `year`,
                    `year_sequence`
            "))->first();

            if ($availableYearSequence) {
                return json_encode($availableYearSequence);
            } else {
                return json_encode(["year" => $request->year, "year_sequence" => $request->sequence, "year_sequence_number" => 1]);
            }
        }

        return array(
            "status" => 400,
            "message" => "Tahun tidak valid",
        );
    }

    // Modify Year Sequence Module
    public function modifyYearSequence(Request $request) {
        $years = array_reverse(range(1999, date('Y', strtotime('+1 years'))));

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno', 'styleno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view("stocker.stocker.modify-year-sequence", ["page" => "dashboard-dc",  "subPageGroup" => "stocker-number", "subPage" => "modify-year-sequence", "years" => $years, "orders" => $orders]);
    }

    public function modifyYearSequenceList(Request $request) {
        if ($request['method'] == "list") {
            $yearSequenceIds = "'-'";
            if ($request->year_sequence_ids) {
                // Decompress
                $binary = base64_decode($request->year_sequence_ids);
                $decompressBinary = gzuncompress($binary);

                $yearSequenceIds = addQuotesAround($decompressBinary);
            }

            $data = YearSequence::selectRaw("
                year_sequence.id_year_sequence,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                UPPER(TRIM(master_sb_ws.color)) color,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            whereRaw("year_sequence.id_year_sequence in (".$yearSequenceIds.")");

            if ($yearSequenceIds) {
                $dataOutput = collect(
                        DB::connection("mysql_sb")->select("
                            SELECT output.*, userpassword.username as sewing_line FROM (
                                select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE kode_numbering in (".$yearSequenceIds.")
                                UNION
                                select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE kode_numbering in (".$yearSequenceIds.")
                                UNION
                                select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE kode_numbering in (".$yearSequenceIds.")
                            ) output
                            left join user_sb_wip on user_sb_wip.id = output.created_by
                            left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        ")
                    );
            } else {
                $dataOutput = collect([]);
            }

            if ($request->range_awal && $request->range_akhir) {
                $dataOutputPacking = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by sewing_line, kode_numbering, id, created_at, updated_at from output_rfts_packing WHERE kode_numbering in (".$yearSequenceIds.")
                    ")
                );
            } else {
                $dataOutputPacking = collect([]);
            }
        } else {
            $data = YearSequence::selectRaw("
                year_sequence.id_year_sequence,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                UPPER(TRIM(master_sb_ws.color)) color,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            where("year", $request->year)->
            where("year_sequence", $request->sequence)->
            whereBetween("year_sequence_number", [($request->range_awal ? $request->range_awal : '-'), ($request->range_akhir ? $request->range_akhir : '-')]);

            if ($request->range_awal && $request->range_akhir) {
                $dataOutput = collect(
                        DB::connection("mysql_sb")->select("
                            SELECT output.*, userpassword.username as sewing_line FROM (
                                select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : '-')." and ".($request->range_akhir ? $request->range_akhir : '-')."
                                UNION
                                select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : '-')." and ".($request->range_akhir ? $request->range_akhir : '-')."
                                UNION
                                select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : '-')." and ".($request->range_akhir ? $request->range_akhir : '-')."
                            ) output
                            left join user_sb_wip on user_sb_wip.id = output.created_by
                            left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        ")
                    );
            } else {
                $dataOutput = collect([]);
            }

            if ($request->range_awal && $request->range_akhir) {
                $dataOutputPacking = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by sewing_line, kode_numbering, id, created_at, updated_at from output_rfts_packing WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : '-')."
                    ")
                );
            } else {
                $dataOutputPacking = collect([]);
            }

            if ($request->range_awal && $request->range_akhir) {
                $dataOutputPackingPo = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by sewing_line, kode_numbering, id, created_at, updated_at from output_rfts_packing_po WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : '-')."
                    ")
                );
            } else {
                $dataOutputPackingPo = collect([]);
            }
        }

        return Datatables::of($data)->
            filterColumn('ws', function($query, $keyword) {
                $query->whereRaw("master_sb_ws.ws LIKE '%".$keyword."%'" );
            })->
            filterColumn('styleno', function($query, $keyword) {
                $query->whereRaw("master_sb_ws.styleno LIKE '%".$keyword."%'" );
            })->
            filterColumn('color', function($query, $keyword) {
                $query->whereRaw("UPPER(TRIM(master_sb_ws.color)) LIKE '%".$keyword."%'" );
            })->
            filterColumn('size', function($query, $keyword) {
                $query->whereRaw("master_sb_ws.size LIKE '%".$keyword."%'" );
            })->
            filterColumn('dest', function($query, $keyword) {
                $query->whereRaw("master_sb_ws.dest LIKE '%".$keyword."%'" );
            })->
            addColumn('qc', function($data) use ($dataOutput) {
                return $dataOutput->where("kode_numbering", $data->id_year_sequence)->first() ? $dataOutput->where("kode_numbering", $data->id_year_sequence)->first()->sewing_line : null;
            })->
            addColumn('packing', function($data) use ($dataOutputPacking) {
                return $dataOutputPacking->where("kode_numbering", $data->id_year_sequence)->first() ? $dataOutputPacking->where("kode_numbering", $data->id_year_sequence)->first()->sewing_line : null;
            })->
            orderColumns(['qc', 'packing'], '-:column $1 $2')->
            toJson();
    }

    public function modifyYearSequenceUpdate(Request $request, SewingService $sewingService) {
        ini_set("max_execution_time", 360000);
        ini_set("memory_limit", '2048M');

        $stocker = Stocker::selectRaw("stocker_input.id_qr_stocker, stocker_input.form_cut_id, stocker_input.form_reject_id, stocker_input.form_piece_id, stocker_input.so_det_id, stocker_input.size, stocker_input.range_akhir, (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe")->where("stocker_input.id_qr_stocker", $request->stocker)->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->first();

        if (Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() < 1) {
            if (!$stocker) {
                return array(
                    "status" => 400,
                    "message" => "Stocker tidak ditemukan",
                );
            }
        }

        $request->size = $stocker ? $stocker->so_det_id : $request->size;
        $request->size_text = $stocker ? $stocker->size : $request->size_text;

        $request->validate([
            "year" => "required",
            "sequence" => "required"
        ]);

        if ($request->size != null && $request->size_text != null) {
            if ($request['method'] == "list") {
                // Decompress
                $yearSequenceIds = "'-'";
                if ($request->year_sequence_ids) {
                    // Decompress
                    $binary = base64_decode($request->year_sequence_ids);
                    $decompressBinary = gzuncompress($binary);

                    $yearSequenceIds = addQuotesAround($decompressBinary);
                }

                $yearSequences = YearSequence::whereRaw("id_year_sequence in (".$yearSequenceIds.")")->
                    get();

                $output = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE kode_numbering in (".$yearSequenceIds.")
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE kode_numbering in (".$yearSequenceIds.")
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE kode_numbering in (".$yearSequenceIds.")
                    ")
                );
            } else {
                $yearSequences = YearSequence::where("year", $request->year)->
                    where("year_sequence", $request->sequence)->
                    whereBetween("year_sequence_number", [$request->range_awal, $request->range_akhir])->
                    get();

                $output = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                    ")
                );
            }

            $yearSequenceArr = [];
            $yearSequenceFailArr = [];
            foreach ($yearSequences as $yearSequence) {
                if (Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) {
                    array_push($yearSequenceArr, $yearSequence->id_year_sequence);
                } else {
                    if ($output->where("kode_numbering", $yearSequence->id_year_sequence)->count() < 1) {
                        array_push($yearSequenceArr, $yearSequence->id_year_sequence);
                    } else {
                        array_push($yearSequenceFailArr, $yearSequence->id_year_sequence);
                    }
                }
            }

            $failMessage = "";
            for ($i = 0; $i < count($yearSequenceFailArr); $i++) {
                $failMessage .= "<small>'".$yearSequenceFailArr[$i]." sudah ada output'</small><br>";
            }

            if (count($yearSequenceArr) > 0 && count($yearSequenceArr) <= 5000) {

                $yearSequence = YearSequence::whereIn("id_year_sequence", $yearSequenceArr)->update([
                    "id_qr_stocker" => $stocker ? $stocker->id_qr_stocker : null,
                    "form_cut_id" => $stocker ? $stocker->form_cut_id : null,
                    "form_reject_id" => $stocker ? $stocker->form_reject_id : null,
                    "number" => $stocker ? $stocker->range_akhir : null,
                    "so_det_id" => $request->size,
                    "size" => $request->size_text,
                ]);
                $rft = DB::connection("mysql_sb")->table("output_rfts")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);
                $defect = DB::connection("mysql_sb")->table("output_defects")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);
                $reject = DB::connection("mysql_sb")->table("output_rejects")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);
                $outputPacking = DB::connection("mysql_sb")->table("output_rfts_packing")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);
                $outputPackingNDS = DB::table("output_rfts_packing")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);
                $outputPackingPo = DB::connection("mysql_sb")->table("output_rfts_packing_po")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);
                $outputGudangStok = DB::connection("mysql_sb")->table("output_gudang_stok")->whereNotNull("packing_po_id")->whereIn("kode_numbering", $yearSequenceArr)->update([
                    "so_det_id" => $request->size,
                ]);

                // When the updated Size Was in different Plan
                $sewingService->missMasterPlan(addQuotesAround(implode("\n", $yearSequenceArr)), false);

                // When the updated Size Was in different PO
                $sewingService->missPackingPo();

                if ($request['method'] == "list") {
                    if ($yearSequenceIds) {
                        return array(
                            "status" => 200,
                            "message" => "Year Sequence <br> ".$yearSequenceIds.". <br> <b>Berhasil di Update</b>".(strlen($failMessage) > 0 ? "<br> Kecuali: <br>".$failMessage : "")
                        );
                    } else {
                        return array(
                            "status" => 400,
                            "message" => "Terjadi Kesalahan"
                        );
                    }
                } else {
                    return array(
                        "status" => 200,
                        "message" => "Year ".$request->year."' <br> Sequence '".$request->sequence."' <br> Range '".$request->range_awal." - ".$request->range_akhir."'. <br> <b>Berhasil di Update</b>".(strlen($failMessage) > 0 ? "<br> Kecuali: <br>".$failMessage : "")
                    );
                }
            } else if (count($yearSequenceArr) < 1) {
                return array(
                    "status" => 400,
                    "message" => "Gagal di ubah ".(strlen($failMessage) > 0 ? "<br> Info : <br>".$failMessage : "")
                );
            } else if (count($yearSequenceArr) > 5000) {
                return array(
                    "status" => 400,
                    "message" => "Maksimal QTY '5000'"
                );
            }
        } else {
            if ($request['method'] == "list") {
                // Decompress
                $yearSequenceIds = "'-'";
                if ($request->year_sequence_ids) {
                    // Decompress
                    $binary = base64_decode($request->year_sequence_ids);
                    $decompressBinary = gzuncompress($binary);

                    $yearSequenceIds = addQuotesAround($decompressBinary);
                }

                $yearSequences = YearSequence::whereRaw("id_year_sequence in (".$yearSequenceIds.")")->
                    get();

                $output = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE kode_numbering in (".$yearSequenceIds.")
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE kode_numbering in (".$yearSequenceIds.")
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE kode_numbering in (".$yearSequenceIds.")
                    ")
                );
            } else {
                $yearSequences = YearSequence::where("year", $request->year)->
                    where("year_sequence", $request->sequence)->
                    whereBetween("year_sequence_number", [$request->range_awal, $request->range_akhir])->
                    get();

                $output = collect(
                    DB::connection("mysql_sb")->select("
                        select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                        UNION
                        select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                    ")
                );
            }

            $yearSequenceArr = [];
            $yearSequenceFailArr = [];
            foreach ($yearSequences as $yearSequence) {
                if (Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) {
                    array_push($yearSequenceArr, $yearSequence->id_year_sequence);
                } else {
                    if ($output->where("kode_numbering", $yearSequence->id_year_sequence)->count() < 1) {
                        array_push($yearSequenceArr, $yearSequence->id_year_sequence);
                    } else {
                        array_push($yearSequenceFailArr, $yearSequence->id_year_sequence);
                    }
                }
            }

            $failMessage = "";
            for ($i = 0; $i < count($yearSequenceFailArr); $i++) {
                $failMessage .= "<small>'".$yearSequenceFailArr[$i]." sudah ada output'</small><br>";
            }

            if (count($yearSequenceArr) > 0 && count($yearSequenceArr) <= 5000) {
                $idWs = $request->id_ws;
                $color = $request->color;

                if ($idWs && $color) {

                    // Loop over year seq
                    foreach ($yearSequenceArr as $ys) {

                        // Check current year seq
                        $currentYearSequence = YearSequence::select("so_det.size", "so_det.dest")->
                            leftJoin("signalbit_erp.so_det", "so_det.id", "=", "year_sequence.so_det_id")->
                            where("id_year_sequence", $ys)->first();

                        if ($currentYearSequence) {

                            // Check current so det
                            $currentSoDet = SoDet::selectRaw("so_det.id, act_costing.id as id_ws, UPPER(TRIM(so_det.color)) color, so_det.size")->
                                leftJoin("so", "so.id", "=", "so_det.id_so")->
                                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                                where("act_costing.id", $idWs)->
                                whereRaw("UPPER(TRIM(so_det.color)) = '".strtoupper(trim($color))."'")->
                                where("so_det.size", $currentYearSequence->size)->
                                where("so_det.dest", $currentYearSequence->dest)->
                                first();
                            if (!$currentSoDet) {
                                $currentSoDet = SoDet::selectRaw("so_det.id, act_costing.id as id_ws, UPPER(TRIM(so_det.color)) color, so_det.size")->
                                    leftJoin("so", "so.id", "=", "so_det.id_so")->
                                    leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                                    where("act_costing.id", $idWs)->
                                    whereRaw("UPPER(TRIM(so_det.color)) = '".strtoupper(trim($color))."'")->
                                    where("so_det.size", $currentYearSequence->size)->
                                    first();
                            }

                            // Update if so det was found
                            if ($currentSoDet && $currentSoDet->id && $currentSoDet->size) {
                                $yearSequence = YearSequence::where("id_year_sequence", $ys)->update([
                                    "id_qr_stocker" => $stocker ? $stocker->id_qr_stocker : null,
                                    "form_cut_id" => $stocker ? $stocker->form_cut_id : null,
                                    "form_reject_id" => $stocker ? $stocker->form_reject_id : null,
                                    "number" => $stocker ? $stocker->range_akhir : null,
                                    "so_det_id" => $currentSoDet->id,
                                    "size" => $currentSoDet->size,
                                ]);
                                $rft = DB::connection("mysql_sb")->table("output_rfts")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                                $defect = DB::connection("mysql_sb")->table("output_defects")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                                $reject = DB::connection("mysql_sb")->table("output_rejects")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                                $outputPacking = DB::connection("mysql_sb")->table("output_rfts_packing")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                                $outputPackingNDS = DB::table("output_rfts_packing")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                                $outputPackingPo = DB::connection("mysql_sb")->table("output_rfts_packing_po")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                                $outputGudangStok = DB::connection("mysql_sb")->table("output_gudang_stok")->whereNotNull("packing_po_id")->where("kode_numbering", $ys)->update([
                                    "so_det_id" => $currentSoDet->id,
                                ]);
                            } else {
                                $failMessage .= "<small>'".$ys." tidak ditemukan size yang cocok'</small><br>";
                            }
                        }
                    }

                    // When the updated Size Was in different Plan
                    $sewingService->missMasterPlan(addQuotesAround(implode("\n", $yearSequenceArr)), false);

                    // When the updated Size Was in different PO
                    $sewingService->missPackingPo();

                    // Message
                    if ($request['method'] == "list") {
                        if ($yearSequenceIds) {
                            return array(
                                "status" => 200,
                                "message" => "Year Sequence <br> ".$yearSequenceIds.". <br> <b>Berhasil di Update</b>".(strlen($failMessage) > 0 ? "<br> Kecuali: <br>".$failMessage : "")
                            );
                        } else {
                            return array(
                                "status" => 400,
                                "message" => "Terjadi Kesalahan"
                            );
                        }
                    } else {
                        return array(
                            "status" => 200,
                            "message" => "Year ".$request->year."' <br> Sequence '".$request->sequence."' <br> Range '".$request->range_awal." - ".$request->range_akhir."'. <br> <b>Berhasil di Update</b>".(strlen($failMessage) > 0 ? "<br> Kecuali: <br>".$failMessage : "")
                        );
                    }
                } else {
                    return array(
                        "status" => 400,
                        "message" => "Harap lengkapi form tujuan."
                    );
                }
            } else if (count($yearSequenceArr) < 1) {
                return array(
                    "status" => 400,
                    "message" => "Gagal di ubah ".(strlen($failMessage) > 0 ? "<br> Info : <br>".$failMessage : "")
                );
            } else if (count($yearSequenceArr) > 5000) {
                return array(
                    "status" => 400,
                    "message" => "Maksimal QTY '5000'"
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Year '".$request->year."' <br> Sequence '".$request->sequence."' <br> Range '".$request->range_awal." - ".$request->range_akhir."'. <br> <b>Gagal di Update</b>"
        );
    }

    public function modifyYearSequenceDelete(Request $request) {
        $request->validate([
            "year" => "required",
            "sequence" => "required",
        ]);

        if ($request['method'] == "list") {
            $yearSequenceIds = "'-'";
            if ($request->year_sequence_ids) {
                // Decompress
                $binary = base64_decode($request->year_sequence_ids);
                $decompressBinary = gzuncompress($binary);

                $yearSequenceIds = addQuotesAround($decompressBinary);
            }

            $yearSequences = YearSequence::whereRaw("id_year_sequence in (".$yearSequenceIds.")")->
                get();

            $output = collect(
                DB::connection("mysql_sb")->select("
                    select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE kode_numbering in (".$yearSequenceIds.")
                    UNION
                    select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE kode_numbering in (".$yearSequenceIds.")
                    UNION
                    select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE kode_numbering in (".$yearSequenceIds.")
                ")
            );
        } else {
            $yearSequences = YearSequence::where("year", $request->year)->
                where("year_sequence", $request->sequence)->
                whereBetween("year_sequence_number", [$request->range_awal, $request->range_akhir])->
                get();

            $output = collect(
                DB::connection("mysql_sb")->select("
                    select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                    UNION
                    select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                    UNION
                    select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE SUBSTR(kode_numbering, 1, ".strlen($request->year."_".$request->sequence).") = '".$request->year."_".$request->sequence."' and SUBSTR(kode_numbering, ".(strlen($request->year."_".$request->sequence)+2).") BETWEEN ".($request->range_awal ? $request->range_awal : 0)." and ".($request->range_akhir ? $request->range_akhir : 0)."
                ")
            );
        }

        if ($output->count() < 1) {
            $yearSequenceArr = [];
            foreach ($yearSequences as $yearSequence) {
                array_push($yearSequenceArr, $yearSequence->id_year_sequence);
            }

            if (count($yearSequenceArr) > 0 && count($yearSequenceArr) <= 5000) {
                $yearSequence = YearSequence::whereIn("id_year_sequence", $yearSequenceArr)->update([
                    "form_cut_id" => null,
                    "so_det_id" => null,
                    "size" => null,
                    "number" => null,
                    "id_qr_stocker" => null,
                ]);

                return array(
                    "status" => 200,
                    "message" => "Year '".$request->year."' <br> Sequence '".$request->sequence."' <br> Range '".$request->range_awal." - ".$request->range_akhir."' <br> <b>Berhasil di HAPUS</b>"
                );
            } else if (count($yearSequenceArr) < 1) {
                return array(
                    "status" => 400,
                    "message" => "Gagal di hapus"
                );
            } else if (count($yearSequenceArr) > 5000) {
                return array(
                    "status" => 400,
                    "message" => "Maksimal QTY '5000'"
                );
            }
        } else {
            return array(
                "status" => 400,
                "message" => "Range sudah memiliki input"
            );
        }

        return array(
            "status" => 400,
            "message" => "Year '".$request->year."' <br> Sequence '".$request->sequence."' <br> Range '".$request->range_awal." - ".$request->range_akhir."'. <br> <b>Gagal di Update</b>"
        );
    }
}

