<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\ScannedItem;
use App\Models\FormCutInputDetail;
use App\Exports\ExportLaporanRoll;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DNS1D;
use PDF;
use DB;

class RollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", 36000);

        // if ($request->ajax()) {
        //     $additionalQuery = "";

        //     if ($request->dateFrom) {
        //         $additionalQuery .= " and DATE(b.created_at) >= '" . $request->dateFrom . "'";
        //     }

        //     if ($request->dateTo) {
        //         $additionalQuery .= " and DATE(b.created_at) <= '" . $request->dateTo . "'";
        //     }

        //     $keywordQuery = "";
        //     if ($request->search["value"]) {
        //         $keywordQuery = "
        //             and (
        //                 act_costing_ws like '%" . $request->search["value"] . "%' OR
        //                 DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
        //             )
        //         ";
        //     }

        //     $data_pemakaian = DB::select("
        //         select
        //             DATE_FORMAT(b.updated_at, '%M) bulan,
        //             DATE_FORMAT(b.updated_at, '%d-%m-%Y) tgl_input,
        //             b.no_form_cut_input,
        //             UPPER(meja.name) nama_meja,
        //             act_costing_ws,
        //             buyer,
        //             style,
        //             color,
        //             b.color_act,
        //             panel,
        //             master_sb_ws.qty,
        //             cons_ws,
        //             cons_marker,
        //             a.cons_ampar,
        //             a.cons_act,
        //             COALESCE(a.cons_pipping, cons_piping) cons_piping,
        //             panjang_marker,
        //             unit_panjang_marker,
        //             comma_marker,
        //             unit_comma_marker,
        //             lebar_marker,
        //             unit_lebar_marker,
        //             a.p_act panjang_actual,
        //             a.unit_p_act unit_panjang_actual,
        //             a.comma_p_act comma_actual,
        //             a.unit_comma_p_act unit_comma_actual,
        //             a.l_act lebar_actual,
        //             a.unit_l_actual unit_lebar_actual,
        //             COALESCE(id_roll, '-') id_roll,
        //             id_item,
        //             detail_item,
        //             COALESCE(b.roll_buyer, b.roll) roll,
        //             COALESCE(b.lot, '-') lot,
        //             COALESCE(b.group_roll, '-') group_roll,
        //             b.qty qty_roll,
        //             b.unit unit_roll,
        //             COALESCE(b.berat_amparan, '-') berat_amparan,
        //             b.est_amparan,
        //             b.lembar_gelaran,
        //             mrk.total_ratio,
        //             (mrk.total_ratio * b.lembar_gelaran) qty_cut,
        //             b.average_time,
        //             b.sisa_gelaran,
        //             b.sambungan,
        //             b.sambungan_roll,
        //             b.kepala_kain,
        //             b.lembar_gelaran,
        //             b.sisa_tidak_bisa,
        //             b.reject,
        //             b.piping,
        //             COALESCE(b.sisa_kain, 0) sisa_kain,
        //             b.pemakaian_lembar,
        //             b.total_pemakaian_roll,
        //             b.short_roll,
        //             CONCAT(ROUND(((b.short_roll / b.qty) * 100), 2), ' %') short_roll_percentage,
        //             a.operator
        //         from
        //             form_cut_input a
        //             left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
        //             left join users meja on meja.id = a.no_meja
        //             left join marker_input mrk on a.id_marker = mrk.kode
        //         where
        //             (a.cancel = 'N'  OR a.cancel IS NULL)
	    //             AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
        //             and b.status != 'not completed'
        //             and id_item is not null
        //             " . $additionalQuery . "
        //             " . $keywordQuery . "
        //         group by
        //             b.id
        //         order by
        //             a.waktu_mulai asc,
        //             b.id asc
        //     ");

        //     return DataTables::of($data_pemakaian)->toJson();
        // }

        return view('cutting.roll.roll', ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "lap-pemakaian"]);
    }

    public function pemakaianRollData(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        $additionalQuery = "";
        $additionalQuery1 = "";
        $additionalQuery2 = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery1 .= " and form_cut_piping.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at >= '" . $request->dateFrom . " 00:00:00'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery1 .= " and form_cut_piping.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at <= '" . $request->dateTo . " 23:59:59'";
        }

        if ($request->supplier) {
            $additionalQuery .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery1 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery2 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
        }

        if ($request->id_ws) {
            $additionalQuery .= " and mrk.act_costing_id = " . $request->id_ws . "";
            $additionalQuery1 .= " and form_cut_piping.act_costing_id = " . $request->id_ws . "";
            $additionalQuery2 .= " and form_cut_piece.act_costing_id = " . $request->id_ws . "";
        }

        $keywordQuery = "";
        $keywordQuery1 = "";
        $keywordQuery2 = "";
        if ($request->search["value"]) {
            $keywordQuery = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";

            $keywordQuery1 = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";

            $keywordQuery2 = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(form_cut_piece_detail.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";
        }

        $data_pemakaian = DB::select("
            select * from (
                select
                    COALESCE(scanned_item.qty_in, b.qty) qty_in,
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
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) pemakaian_lembar,
                    ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2) total_pemakaian_roll,
                    CASE WHEN c.id IS NULL THEN round(((ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2)+b.sisa_kain)-b.qty), 2) ELSE round(((ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2)+b.sisa_kain)-c.qty), 2) END short_roll,
                    CASE WHEN c.id IS NULL THEN (round((CASE WHEN c.id IS NULL THEN round(((ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2)+b.sisa_kain)-b.qty), 2) ELSE round(((ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2)+b.sisa_kain)-c.qty), 2) END)/b.qty*100)) ELSE (round((CASE WHEN c.id IS NULL THEN round(((ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2)+b.sisa_kain)-b.qty), 2) ELSE round(((ROUND(ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran + COALESCE(c.sisa_gelaran, 0)) + (b.sambungan_roll + COALESCE(c.sisa_gelaran, 0)) + (CASE WHEN c.id is null THEN 0 ELSE c.sambungan END), 2) + (b.kepala_kain + COALESCE(c.kepala_kain, 0)) + (b.sisa_tidak_bisa + COALESCE(c.sisa_tidak_bisa, 0)) + (b.reject + COALESCE(c.reject, 0)) + (b.piping + COALESCE(c.piping, 0)), 2)+b.sisa_kain)-c.qty), 2) END)/c.qty*100, 2)) END short_roll_percentage,
                    b.status,
                    a.operator,
                    a.tipe_form_cut
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
                    AND b.created_at >= DATE(NOW()-INTERVAL 6 MONTH)
                    ".$additionalQuery."
                    ".$keywordQuery."
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
                    'PIPING' tipe_form_cut
                from
                    form_cut_piping
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
                    left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
                where
                    id_item is not null
                    ".$additionalQuery1."
                    ".$keywordQuery1."
                group by
                    form_cut_piping.id
                union
                select
                    COALESCE(scanned_item.qty_in, form_cut_piece_detail.qty) qty_in,
                    form_cut_piece.created_at waktu_mulai,
                    form_cut_piece.updated_at waktu_selesai,
                    form_cut_piece.id,
                    DATE_FORMAT(form_cut_piece.created_at, '%M') bulan,
                    DATE_FORMAT(form_cut_piece.created_at, '%d-%m-%Y') tgl_input,
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
                    COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    (CASE WHEN form_cut_piece_detail.qty >= COALESCE(scanned_item.qty_in, 0) THEN 'Roll Utuh' ELSE 'Sisa Kain' END) status_roll,
                    COALESCE(scanned_item.qty_in, form_cut_piece_detail.qty) qty_awal,
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
                    ROUND(form_cut_piece_detail.qty - (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa)) short_roll,
                    ROUND((form_cut_piece_detail.qty - (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa))/coalesce(scanned_item.qty_in, form_cut_piece_detail.qty) * 100, 2) short_roll_percentage,
                    form_cut_piece_detail.status `status`,
                    form_cut_piece.employee_name,
                    'PCS' tipe_form_cut
                from
                    form_cut_piece
                    left join form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
                    left join scanned_item on scanned_item.id_roll = form_cut_piece_detail.id_roll
                where
                    scanned_item.id_item is not null and
                    form_cut_piece_detail.status = 'complete'
                    ".$additionalQuery2."
                    ".$keywordQuery2."
                group by
                    form_cut_piece_detail.id
            ) roll_consumption
            order by
                waktu_mulai asc,
                waktu_selesai asc,
                id asc
        ");

        return DataTables::of($data_pemakaian)->toJson();
    }

    public function getSupplier(Request $request)
    {
        $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            where('mastersupplier.tipe_sup', 'C')->
            where('status', '!=', 'CANCEL')->
            where('type_ws', 'STD')->
            where('cost_date', '>=', '2023-01-01')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return $suppliers;
    }

    public function getOrder(Request $request)
    {
        $orderSql = DB::connection('mysql_sb')->
            table('act_costing')->
            selectRaw('
                id as id_ws,
                kpno as no_ws
            ')->
            where('status', '!=', 'CANCEL')->
            where('cost_date', '>=', '2023-01-01')->
            where('type_ws', 'STD');
            if ($request->supplier) {
                $orderSql->where('id_buyer', $request->supplier);
            }
            $orders = $orderSql->
                orderBy('cost_date', 'desc')->
                orderBy('kpno', 'asc')->
                groupBy('kpno')->
                get();

        return $orders;
    }

    public function export_excel(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportLaporanRoll($request->dateFrom, $request->dateTo, $request->supplier, $request->id_ws), 'Laporan pemakaian cutting '.$request->dateFrom.' - '.$request->dateTo.' ('.Carbon::now().').xlsx');
    }

    public function sisaKainRoll(Request $request)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                mastersupplier.Supplier buyer,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.item_desc detail_item,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, whs_bppb_det.no_roll) no_roll,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '".$request->id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        return view("cutting.roll.sisa-kain-roll", ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "sisa-kain-roll"]);
    }

    public function getScannedItem($id)
    {
        $newItemAdditional = "";
        $itemAdditional = "";

        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                mastersupplier.Supplier buyer,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.item_desc detail_item,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, whs_bppb_det.no_roll) no_roll,
                whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty,
                whs_bppb_det.satuan unit,
                bji.rule_bom,
                GROUP_CONCAT(DISTINCT so_det.id) as so_det_list,
                GROUP_CONCAT(DISTINCT so_det.size) as size_list
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN (SELECT * FROM whs_lokasi_inmaterial GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                LEFT JOIN bom_jo_item bji ON bji.id_item = whs_bppb_det.id_item AND bji.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so_det ON so_det.id = bji.id_so_det
            WHERE
                whs_bppb_det.id_roll = '".$id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                ".$newItemAdditional."
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");
        if ($newItem) {
            $scannedItem = ScannedItem::selectRaw("
                scanned_item.id,
                scanned_item.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.color,
                scanned_item.lot,
                scanned_item.roll,
                scanned_item.roll_buyer,
                scanned_item.qty,
                scanned_item.qty_stok,
                scanned_item.qty_in,
                COALESCE(pemakaian.total_pemakaian, scanned_item.qty_pakai) qty_pakai,
                scanned_item.unit,
                scanned_item.berat_amparan,
                scanned_item.so_det_list,
                scanned_item.size_list
            ")->leftJoin(DB::raw("
                (
                    select
                        id_roll,
                        max( qty_awal ) qty_awal,
                        sum( total_pemakaian ) total_pemakaian
                    from
                        (
                            SELECT
                                id_roll,
                                max( qty ) qty_awal,
                                sum( total_pemakaian_roll + sisa_kain ) total_pemakaian
                            FROM
                                form_cut_input_detail
                            WHERE
                                id_roll = '".$id."'
                            GROUP BY
                                id_roll
                            UNION
                            SELECT
                                id_roll,
                                max( qty ) qty_awal,
                                sum( piping + qty_sisa ) total_pemakaian
                            FROM
                                form_cut_piping
                            WHERE
                                id_roll = '".$id."'
                            GROUP BY
                                id_roll
                        ) pemakaian
                    group by
                        id_roll
                ) pemakaian
            "), "pemakaian.id_roll", "=", "scanned_item.id_roll")->
            where('scanned_item.id_roll', $id)->
            where('scanned_item.id_item', $newItem[0]->id_item)->
            first();
            if ($scannedItem) {
                $scannedItemUpdate = ScannedItem::where("id_roll", $id)->first();

                $newItemQtyStok = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($newItem[0]->qty_stok * 0.9144, 2) : $newItem[0]->qty_stok;
                $newItemQty = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($newItem[0]->qty * 0.9144, 2) : $newItem[0]->qty;
                $newItemUnit = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? 'METER' : $newItem[0]->unit;

                if ($scannedItemUpdate) {
                    $scannedItemUpdate->qty_stok = $newItemQtyStok;
                    $scannedItemUpdate->qty_in = $newItemQty;
                    $scannedItemUpdate->qty = floatval(($newItemQty - $scannedItem->qty_in) + $scannedItem->qty);
                    $scannedItemUpdate->save();
                }

                $formCutInputDetail = FormCutInputDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();
            } else {
                if ($newItem[0]->unit != "PCS" || $newItem[0]->unit != "PCE") {
                    $newItemQtyStok = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD")) ? round($newItem[0]->qty_stok * 0.9144, 2) : $newItem[0]->qty_stok;
                    $newItemQty = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD")) ? round($newItem[0]->qty * 0.9144, 2) : $newItem[0]->qty;
                    $newItemUnit = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD")) ? 'METER' : $newItem[0]->unit;
                } else {
                    $newItemQtyStok = $newItem[0]->qty_stok;
                    $newItemQty = $newItem[0]->qty;
                    $newItemUnit = $newItem[0]->unit;
                }

                ScannedItem::create(
                    [
                        "id_roll" => $id,
                        "id_item" => $newItem[0]->id_item,
                        "color" => '-',
                        "detail_item" => $newItem[0]->detail_item,
                        "lot" => $newItem[0]->lot,
                        "roll" => $newItem[0]->roll,
                        "roll_buyer" => $newItem[0]->roll_buyer,
                        "qty" => $newItemQty,
                        "qty_stok" => $newItemQtyStok,
                        "qty_in" => $newItemQty,
                        "qty_pakai" => 0,
                        "unit" => $newItemUnit,
                        "rule_bom" => $newItem[0]->rule_bom,
                        "so_det_list" => $newItem[0]->so_det_list,
                        "size_list" => $newItem[0]->size_list
                    ]
                );
            }

            return json_encode($newItem ? $newItem[0] : null);
        }

        $item = DB::connection("mysql_sb")->select("
            SELECT
                ms.Supplier buyer,
                ac.kpno no_ws,
                ac.styleno style,
                mi.color,
                br.id id_roll,
                mi.id_item,
                mi.itemdesc detail_item,
                goods_code,
                supplier,
                bpbno_int,
                pono,
                invno,
                ac.kpno,
                roll_no no_roll,
                roll_qty qty,
                lot_no lot,
                bpb.unit,
                kode_rak
            FROM
                bpb_roll br
                INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                INNER JOIN bpb ON brh.bpbno = bpb.bpbno AND brh.id_jo = bpb.id_jo AND brh.id_item = bpb.id_item
                INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                INNER JOIN so ON jd.id_so = so.id
                INNER JOIN act_costing ac ON so.id_cost = ac.id
                INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
            WHERE
                br.id = '" . $id . "'
                AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                ".$itemAdditional."
            GROUP BY
                br.id
            LIMIT 1
        ");
        if ($item) {
            $scannedItem = ScannedItem::where('id_roll', $id)->where('id_item', $item[0]->id_item)->first();

            if ($scannedItem) {

                $scannedItemUpdate = ScannedItem::where("id_roll", $id)->first();

                if ($newItem[0]->unit != "PCS" || $newItem[0]->unit != "PCE") {
                    $itemQtyStok = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($item[0]->qty_stok * 0.9144, 2) : $item[0]->qty_stok;
                    $itemQty = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($item[0]->qty * 0.9144, 2) : $item[0]->qty;
                    $itemUnit = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? 'METER' : $item[0]->unit;
                } else {
                    $newItemQtyStok = $newItem[0]->qty_stok;
                    $newItemQty = $newItem[0]->qty;
                    $newItemUnit = $newItem[0]->unit;
                }

                if ($scannedItemUpdate) {
                    $scannedItemUpdate->qty_stok = $itemQtyStok;
                    $scannedItemUpdate->qty_in = $itemQty;
                    $scannedItemUpdate->qty = floatval(($itemQty - $scannedItem->qty_in) + $scannedItem->qty);
                    $scannedItemUpdate->save();
                }

                $formCutInputDetail = FormCutInputDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();
            } else {
                $itemQtyStok = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD")) ? round($item[0]->qty_stok * 0.9144, 2) : $item[0]->qty_stok;
                $itemQty = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD")) ? round($item[0]->qty * 0.9144, 2) : $item[0]->qty;
                $itemUnit = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD")) ? 'METER' : $item[0]->unit;

                $itemData = ScannedItem::create(
                    [
                        "id_roll" => $id,
                        "id_item" => $item[0]->id_item,
                        "color" => '-',
                        "detail_item" => $item[0]->detail_item,
                        "lot" => $item[0]->lot,
                        "roll" => $item[0]->roll,
                        "roll_buyer" => $item[0]->roll_buyer,
                        "qty" => $itemQty,
                        "qty_stok" => $itemQtyStok,
                        "qty_in" => $itemQty,
                        "qty_pakai" => 0,
                        "unit" => $itemUnit
                    ]
                );
            }

            return json_encode($item ? $item[0] : null);
        }

        return  null;
    }

    public function getSisaKainForm(Request $request) {
        $forms = DB::select("
            SELECT
                form_cut_input.id id_form,
                no_form_cut_input,
                form_cut_input.no_cut,
                id_roll,
                MAX( qty ) qty,
                unit,
                SUM( total_pemakaian_roll ) total_pemakaian_roll,
                SUM( short_roll ) short_roll,
                MIN( CASE WHEN form_cut_input_detail.STATUS = 'extension' OR form_cut_input_detail.STATUS = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ) sisa_kain,
                form_cut_input.STATUS status_form,
                form_cut_input_detail.status,
                COALESCE ( form_cut_input_detail.created_at, form_cut_input_detail.updated_at ) updated_at
            FROM
                `form_cut_input_detail`
                LEFT JOIN `form_cut_input` ON `form_cut_input`.`id` = `form_cut_input_detail`.`form_cut_id`
            WHERE
                ( form_cut_input.status != 'SELESAI PENGERJAAN' OR ( form_cut_input.STATUS = 'SELESAI PENGERJAAN' AND form_cut_input.STATUS != 'not complete' AND form_cut_input.STATUS != 'extension' ) )
                AND `id_roll` = '".$request->id."'
                AND ( id_roll IS NOT NULL AND id_roll != '' )
                AND form_cut_input_detail.updated_at >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_input`.`id`

            UNION

            SELECT
                form_cut_piece_detail.id id_form,
                form_cut_piece.no_form no_form_cut_input,
                form_cut_piece.no_cut,
                id_roll,
                MAX( qty ) qty,
                qty_unit as unit,
                SUM( qty_pemakaian ) total_pemakaian_roll,
                SUM( qty - (qty_pemakaian + qty_sisa) ) short_roll,
                qty_sisa sisa_kain,
                form_cut_piece.status status_form,
                form_cut_piece_detail.status,
                COALESCE ( form_cut_piece_detail.created_at, form_cut_piece_detail.updated_at ) updated_at
            FROM
                `form_cut_piece_detail`
                LEFT JOIN `form_cut_piece` ON `form_cut_piece`.`id` = `form_cut_piece_detail`.`form_id`
            WHERE
                ( form_cut_piece_detail.status = 'complete' )
                AND `id_roll` = '".$request->id."'
                AND ( id_roll IS NOT NULL AND id_roll != '' )
                AND form_cut_piece_detail.updated_at >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_piece`.`id`
        ");

        return DataTables::of($forms)->toJson();
    }

    public function printSisaKain($id)
    {
        $sbItem = DB::connection("mysql_sb")->select("
            SELECT
                masteritem.itemdesc detail_item,
                masteritem.goods_code,
                masteritem.id_item,
                CONCAT(whs_bppb_h.no_bppb, ' | ', whs_bppb_h.tgl_bppb) bppb,
                whs_bppb_h.no_req,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, '-') no_roll_buyer,
                whs_lokasi_inmaterial.kode_lok lokasi,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '".$id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        if (!$sbItem) {
            $sbItem = DB::connection("mysql_sb")->select("
                SELECT
                    mi.itemdesc detail_item,
                    mi.goods_code,
                    mi.id_item,
                    CONCAT(bpb.bpbno_int, ' | ', bpb.bpbdate) bppb,
                    '-' no_req,
                    ac.kpno no_ws,
                    ac.styleno style,
                    mi.color,
                    br.id id_roll,
                    brh.id_item,
                    br.lot_no lot,
                    br.roll_no no_roll,
                    '-' no_roll_buyer,
                    '-' lokasi,
                    br.unit,
                    br.roll_qty qty
                FROM
                    bpb_roll br
                    INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                    INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                    INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                    AND brh.id_jo = bpb.id_jo
                    AND brh.id_item = bpb.id_item
                    INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                    INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                    INNER JOIN so ON jd.id_so = so.id
                    INNER JOIN act_costing ac ON so.id_cost = ac.id
                    INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
                WHERE
                    br.id = '" . $id . "'
                    AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                    LIMIT 1
            ");
        }

        $ndsItem = ScannedItem::selectRaw("
                GROUP_CONCAT(DISTINCT form_cut_input_detail.group_roll) group_roll,
                COALESCE(scanned_item.qty, MIN(form_cut_input_detail.sisa_kain)) sisa_kain,
                scanned_item.unit,
                GROUP_CONCAT(DISTINCT CONCAT( form_cut_input.no_form, ' | ', COALESCE(form_cut_input.operator, '-')) SEPARATOR '^') AS no_form
            ")->
            leftJoin("form_cut_input_detail", "form_cut_input_detail.id_roll", "=", "scanned_item.id_roll")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "form_cut_input_detail.form_cut_id")->
            where("scanned_item.id_roll", $id)->
            orderBy("scanned_item.id", "desc")->
            groupBy("scanned_item.id_roll")->
            first();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('cutting.roll.pdf.sisa-kain-roll', ["sbItem" => ($sbItem ? $sbItem[0] : null), "ndsItem" => $ndsItem])->setPaper('a7', 'landscape');

        $fileName = 'Sisa_Kain_'.$id.'.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }

    public function massPrintSisaKain(Request $request)
    {
        $idsStr = addQuotesAround($request->ids);

        $sbItems = DB::connection("mysql_sb")->select("
            SELECT
                masteritem.itemdesc detail_item,
                masteritem.goods_code,
                masteritem.id_item,
                CONCAT(whs_bppb_h.no_bppb, ' | ', whs_bppb_h.tgl_bppb) bppb,
                whs_bppb_h.no_req,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, '-') no_roll_buyer,
                whs_lokasi_inmaterial.kode_lok lokasi,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll in (".$idsStr.")
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
        ");

        if (!$sbItems) {
            $sbItems = DB::connection("mysql_sb")->select("
                SELECT
                    mi.itemdesc detail_item,
                    mi.goods_code,
                    mi.id_item,
                    CONCAT(bpb.bpbno_int, ' | ', bpb.bpbdate) bppb,
                    '-' no_req,
                    ac.kpno no_ws,
                    ac.styleno style,
                    mi.color,
                    br.id id_roll,
                    brh.id_item,
                    br.lot_no lot,
                    br.roll_no no_roll,
                    '-' no_roll_buyer,
                    '-' lokasi,
                    br.unit,
                    br.roll_qty qty
                FROM
                    bpb_roll br
                    INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                    INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                    INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                    AND brh.id_jo = bpb.id_jo
                    AND brh.id_item = bpb.id_item
                    INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                    INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                    INNER JOIN so ON jd.id_so = so.id
                    INNER JOIN act_costing ac ON so.id_cost = ac.id
                    INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
                WHERE
                    br.id in (".$idsStr.")
                    AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
            ");
        }

        $ndsItems = ScannedItem::selectRaw("
                scanned_item.id_roll,
                GROUP_CONCAT(DISTINCT form_cut_input_detail.group_roll) group_roll,
                COALESCE(scanned_item.qty, MIN(form_cut_input_detail.sisa_kain)) sisa_kain,
                scanned_item.unit,
                GROUP_CONCAT(DISTINCT CONCAT( form_cut_input.no_form, ' | ', COALESCE(form_cut_input.operator, '-')) SEPARATOR '^') AS no_form
            ")->
            leftJoin("form_cut_input_detail", "form_cut_input_detail.id_roll", "=", "scanned_item.id_roll")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "form_cut_input_detail.form_cut_id")->
            whereRaw("scanned_item.id_roll in (".$idsStr.")")->
            orderBy("scanned_item.id", "desc")->
            groupBy("scanned_item.id_roll")->
            get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('cutting.roll.pdf.mass-sisa-kain-roll', ["sbItems" => ($sbItems ? $sbItems : null), "ndsItems" => $ndsItems])->setPaper('a7', 'landscape');

        $fileName = 'Mass_Sisa_Kain.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
