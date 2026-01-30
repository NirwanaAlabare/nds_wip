<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Stocker\Stocker;
use App\Models\Dc\SecondaryInhouse;
use App\Exports\DC\ExportSecondaryInHouse;
use App\Exports\DC\ExportSecondaryInHouseDetail;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DB;

class SecondaryInhouseOutController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        // dd($data_rak);
        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
            }

            $keywordQuery = '';
            if ($request->search['value']) {
                $keywordQuery =
                    "
                     (
                        line like '%" .
                    $request->search['value'] .
                    "%'
                    )
                ";
            }

            if ($request->sec_filter_tipe && count($request->sec_filter_tipe) > 0) {
                $keywordQuery .= " and (CASE WHEN fp.id > 0 THEN 'PIECE' ELSE (CASE WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) in (".addQuotesAround(implode("\n", $request->sec_filter_tipe)).")";
            }
            if ($request->sec_filter_buyer && count($request->sec_filter_buyer) > 0) {
                $keywordQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $request->sec_filter_buyer)).")";
            }
            if ($request->sec_filter_ws && count($request->sec_filter_ws) > 0) {
                $keywordQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->sec_filter_ws)).")";
            }
            if ($request->sec_filter_style && count($request->sec_filter_style) > 0) {
                $keywordQuery .= " and p.style in (".addQuotesAround(implode("\n", $request->sec_filter_style)).")";
            }
            if ($request->sec_filter_color && count($request->sec_filter_color) > 0) {
                $keywordQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->sec_filter_color)).")";
            }
            if ($request->sec_filter_panel && count($request->sec_filter_panel) > 0) {
                $keywordQuery .= " and COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) in (".addQuotesAround(implode("\n", $request->sec_filter_panel)).")";
            }
            if ($request->sec_filter_part && count($request->sec_filter_part) > 0) {
                $keywordQuery .= " and CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) in (".addQuotesAround(implode("\n", $request->sec_filter_part)).")";
            }
            if ($request->sec_filter_size && count($request->sec_filter_size) > 0) {
                $keywordQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->sec_filter_size)).")";
            }
            if ($request->sec_filter_no_cut && count($request->sec_filter_no_cut) > 0) {
                $keywordQuery .= " and COALESCE(f.no_cut, fp.no_cut, '-') in (".addQuotesAround(implode("\n", $request->sec_filter_no_cut)).")";
            }
            if ($request->sec_filter_tujuan && count($request->sec_filter_tujuan) > 0) {
                $keywordQuery .= " and a.tujuan in (".addQuotesAround(implode("\n", $request->sec_filter_tujuan)).")";
            }
            if ($request->sec_filter_tempat && count($request->sec_filter_tempat) > 0) {
                $keywordQuery .= " and a.tempat in (".addQuotesAround(implode("\n", $request->sec_filter_tempat)).")";
            }
            if ($request->sec_filter_lokasi && count($request->sec_filter_lokasi) > 0) {
                $keywordQuery .= " and a.lokasi in (".addQuotesAround(implode("\n", $request->sec_filter_lokasi)).")";
            }
            if ($request->size_filter && count($request->size_filter) > 0) {
                $keywordQuery .= " and COALESCE(msb.size, s.size) in (".addQuotesAround(implode("\n", $request->size_filter)).")";
            }

            $data_input = DB::select("
                SELECT
                    a.*,
                    (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
                    DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                    a.tgl_trans,
                    s.act_costing_ws,
                    s.color,
                    p.buyer,
                    p.style,
                    COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                    COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                    COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                    COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                    COALESCE(a.qty_in) qty_in,
                    a.created_at,
                    COALESCE(mx.tujuan, dc.tujuan) as tujuan,
                    COALESCE(mx.proses, dc.lokasi) lokasi,
                    dc.tempat,
                    COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                    COALESCE(msb.size, s.size) AS size,
                    a.user,
                    CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                    CONCAT(
                        s.range_awal, ' - ', s.range_akhir,
                        CASE
                        WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL
                            THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                        ELSE ' (0)'
                        END
                    ) AS stocker_range_old,
                    CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range
                FROM secondary_inhouse_input a
                LEFT JOIN (
                    SELECT
                        secondary_inhouse_input.id_qr_stocker,
                        MAX(qty_awal) as qty_awal,
                        SUM(qty_reject) qty_reject,
                        SUM(qty_replace) qty_replace,
                        (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                        MAX(secondary_inhouse_input.urutan) AS max_urutan,
                        GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                        GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                    FROM secondary_inhouse_input
                    LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                    LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                    LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                    GROUP BY id_qr_stocker
                    having MAX(secondary_inhouse_input.urutan) is not null
                ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
                LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
                LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
                LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
                LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
                left join part_detail pd on s.part_detail_id = pd.id
                left join part p on p.id = pd.part_id
                left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                left join part p_com on p_com.id = pd_com.part_id
                LEFT JOIN master_part mp ON mp.id = pd.master_part_id
                LEFT JOIN (
                    SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                    FROM dc_in_input
                ) dc ON a.id_qr_stocker = dc.id_qr_stocker
                WHERE
                    a.tgl_trans IS NOT NULL
                    AND (
                        a.urutan IS NULL
                        OR a.urutan = mx.max_urutan
                    )
                    $additionalQuery
                    $keywordQuery
                ORDER BY a.tgl_trans DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterSecondaryInhouse(Request $request)
    {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
        }

        $data_input = collect(DB::select("
            SELECT
                a.*,
                (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                COALESCE(mx.qty_akhir, a.qty_in) qty_in,
                a.created_at,
                COALESCE(mx.tujuan, dc.tujuan) as tujuan,
                COALESCE(mx.proses, dc.lokasi) lokasi,
                dc.tempat,
                COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                COALESCE(msb.size, s.size) AS size,
                a.user,
                CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                CONCAT(
                    s.range_awal, ' - ', s.range_akhir,
                    CASE
                    WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL
                        THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                    ELSE ' (0)'
                    END
                ) AS stocker_range_old,
                CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range
            FROM secondary_inhouse_input a
            LEFT JOIN (
                SELECT
                    a.id_qr_stocker,
                    MAX(other_sec_inhouse.qty_awal) as qty_awal,
                    SUM(other_sec_inhouse.qty_reject) qty_reject,
                    SUM(other_sec_inhouse.qty_replace) qty_replace,
                    MAX(a.qty_in) as qty_akhir,
                    MAX(a.urutan) AS max_urutan,
                    GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                    GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                FROM secondary_inhouse_input a
                LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = a.id_qr_stocker
                LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = a.urutan
                LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                left join secondary_inhouse_input as other_sec_inhouse on other_sec_inhouse.id_qr_stocker = stocker_input.id_qr_stocker
                    $additionalQuery
                GROUP BY
                    a.id
                having
                    MAX(a.urutan) is not null
            ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
            LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
            LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
            LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
            LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
            LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
            left join part_detail pd on s.part_detail_id = pd.id
            left join part p on p.id = pd.part_id
            left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
            left join part p_com on p_com.id = pd_com.part_id
            LEFT JOIN master_part mp ON mp.id = pd.master_part_id
            LEFT JOIN (
                SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                FROM dc_in_input
            ) dc ON a.id_qr_stocker = dc.id_qr_stocker
            WHERE
                a.tgl_trans IS NOT NULL
                AND (
                    a.urutan IS NULL
                    OR a.urutan = mx.max_urutan
                )
                $additionalQuery
            ORDER BY
                a.tgl_trans DESC
        "));

        $tipe = $data_input->groupBy("tipe")->keys();
        $act_costing_ws = $data_input->groupBy("act_costing_ws")->keys();
        $color = $data_input->groupBy("color")->keys();
        $buyer = $data_input->groupBy("buyer")->keys();
        $style = $data_input->groupBy("style")->keys();
        $tujuan = $data_input->groupBy("tujuan")->keys();
        $lokasi = $data_input->groupBy("lokasi")->keys();
        $lokasi_rak = $data_input->groupBy("lokasi_rak")->keys();
        $panel = $data_input->groupBy("panel")->keys();
        $part = $data_input->groupBy("nama_part")->keys();
        $no_cut = $data_input->groupBy("no_cut")->keys();
        $size = $data_input->groupBy("size")->keys();

        return  array(
            "tipe" => $tipe,
            "ws" => $act_costing_ws,
            "color" => $color,
            "buyer" => $buyer,
            "style" => $style,
            "tujuan" => $tujuan,
            "lokasi" => $lokasi,
            "lokasi_rak" => $lokasi_rak,
            "panel" => $panel,
            "part" => $part,
            "no_cut" => $no_cut,
            "size" => $size
        );
    }

    public function detail_stocker_inhouse(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');

        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and (a.tgl_trans >= '" . $request->dateFrom . "') ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and (a.tgl_trans <= '" . $request->dateTo . "') ";
            }

            if ($request->detail_sec_filter_buyer && count($request->detail_sec_filter_buyer) > 0) {
                $additionalQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $request->detail_sec_filter_buyer)).")";
            }
            if ($request->detail_sec_filter_ws && count($request->detail_sec_filter_ws) > 0) {
                $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->detail_sec_filter_ws)).")";
            }
            if ($request->detail_sec_filter_style && count($request->detail_sec_filter_style) > 0) {
                $additionalQuery .= " and p.style in (".addQuotesAround(implode("\n", $request->detail_sec_filter_style)).")";
            }
            if ($request->detail_sec_filter_color && count($request->detail_sec_filter_color) > 0) {
                $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->detail_sec_filter_color)).")";
            }
            if ($request->detail_sec_filter_lokasi && count($request->detail_sec_filter_lokasi) > 0) {
                $additionalQuery .= " and COALESCE(mx.proses, dc.lokasi) in (".addQuotesAround(implode("\n", $request->detail_sec_filter_lokasi)).")";
            }

            $data_detail = DB::select("
                select
                    act_costing_ws, buyer, color, style as styleno, COALESCE(SUM(qty_awal), 0) qty_in, COALESCE(sum(qty_reject), 0) qty_reject, COALESCE(sum(qty_replace), 0) qty_replace, COALESCE(sum(qty_in), 0) qty_out, COALESCE(sum(qty_awal) - sum(qty_in), 0) balance, lokasi
                from
                    (
                        SELECT
                            (CASE WHEN fp.id > 0 THEN 'PIECE'
                                    WHEN fr.id > 0 THEN 'REJECT'
                                    ELSE 'NORMAL' END) AS tipe,
                            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                            a.tgl_trans,
                            s.act_costing_ws,
                            s.color,
                            p.buyer,
                            p.style,
                            COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                            COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                            COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                            COALESCE(mx.qty_akhir, a.qty_in) qty_in,
                            a.created_at,
                            COALESCE(mx.tujuan, dc.tujuan) as tujuan,
                            COALESCE(mx.proses, dc.lokasi) lokasi,
                            dc.tempat,
                            COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                            COALESCE(msb.size, s.size) AS size,
                            a.user,
                            mp.nama_part,
                            CONCAT(
                                s.range_awal, ' - ', s.range_akhir,
                                CASE
                                WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL
                                    THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                                ELSE ' (0)'
                                END
                            ) AS stocker_range_old,
                            CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range
                        FROM secondary_inhouse_input a
                        LEFT JOIN (
                            SELECT
                                secondary_inhouse_input.id_qr_stocker,
                                MAX(qty_awal) as qty_awal,
                                SUM(qty_reject) qty_reject,
                                SUM(qty_replace) qty_replace,
                                (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                                MAX(secondary_inhouse_input.urutan) AS max_urutan,
                                GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                                GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                            FROM secondary_inhouse_input
                            LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                            LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                            LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                            GROUP BY id_qr_stocker
                            having MAX(secondary_inhouse_input.urutan) is not null
                        ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
                        LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
                        LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                        LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
                        LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
                        LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
                        LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
                        LEFT JOIN part p ON pd.part_id = p.id
                        LEFT JOIN master_part mp ON mp.id = pd.master_part_id
                        LEFT JOIN (
                            SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                            FROM dc_in_input
                        ) dc ON a.id_qr_stocker = dc.id_qr_stocker
                        WHERE
                            a.tgl_trans IS NOT NULL
                            AND (
                                a.urutan IS NULL
                                OR a.urutan = mx.max_urutan
                            )
                            $additionalQuery
                        GROUP BY
                            a.id_qr_stocker
                    ) a
                GROUP BY
                    act_costing_ws,buyer,style,color,lokasi
            ");

            return DataTables::of($data_detail)->toJson();
        }

        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function filterDetailSecondaryInhouse(Request $request)
    {
        $additionalQuery = '';

        if ($request->dateFrom) {
            $additionalQuery .= " and (a.tgl_trans >= '" . $request->dateFrom . "') ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and (a.tgl_trans <= '" . $request->dateTo . "') ";
        }

        if ($request->detail_sec_filter_buyer && count($request->detail_sec_filter_buyer) > 0) {
            $additionalQuery .= " and p.buyer in (".addQuotesAround(implode("\n", $request->detail_sec_filter_buyer)).")";
        }
        if ($request->detail_sec_filter_ws && count($request->detail_sec_filter_ws) > 0) {
            $additionalQuery .= " and s.act_costing_ws in (".addQuotesAround(implode("\n", $request->detail_sec_filter_ws)).")";
        }
        if ($request->detail_sec_filter_style && count($request->detail_sec_filter_style) > 0) {
            $additionalQuery .= " and p.style in (".addQuotesAround(implode("\n", $request->detail_sec_filter_style)).")";
        }
        if ($request->detail_sec_filter_color && count($request->detail_sec_filter_color) > 0) {
            $additionalQuery .= " and s.color in (".addQuotesAround(implode("\n", $request->detail_sec_filter_color)).")";
        }
        if ($request->detail_sec_filter_lokasi && count($request->detail_sec_filter_lokasi) > 0) {
            $additionalQuery .= " and COALESCE(mx.proses, dc.lokasi) in (".addQuotesAround(implode("\n", $request->detail_sec_filter_lokasi)).")";
        }

        $data_detail = collect(DB::select("
            select
                act_costing_ws, buyer, color, style as styleno, COALESCE(SUM(qty_awal), 0) qty_in, COALESCE(sum(qty_reject), 0) qty_reject, COALESCE(sum(qty_replace), 0) qty_replace, COALESCE(sum(qty_in), 0) qty_out, COALESCE(sum(qty_awal) - sum(qty_in), 0) balance, lokasi
            from
                (
                    SELECT
                        (CASE WHEN fp.id > 0 THEN 'PIECE' WHEN fr.id > 0 THEN 'REJECT' ELSE 'NORMAL' END) AS tipe,
                        DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') AS tgl_trans_fix,
                        a.tgl_trans,
                        s.act_costing_ws,
                        s.color,
                        p.buyer,
                        p.style,
                        COALESCE(mx.qty_awal, a.qty_awal) qty_awal,
                        COALESCE(mx.qty_reject, a.qty_reject) qty_reject,
                        COALESCE(mx.qty_replace, a.qty_replace) qty_replace,
                        COALESCE(mx.qty_akhir, a.qty_in) qty_in,
                        a.created_at,
                        COALESCE(mx.tujuan, dc.tujuan) as tujuan,
                        COALESCE(mx.proses, dc.lokasi) lokasi,
                        dc.tempat,
                        COALESCE(f.no_cut, fp.no_cut, '-') AS no_cut,
                        COALESCE(msb.size, s.size) AS size,
                        a.user,
                        mp.nama_part,
                        CONCAT(
                            s.range_awal, ' - ', s.range_akhir,
                            CASE
                            WHEN dc.qty_reject IS NOT NULL AND dc.qty_replace IS NOT NULL THEN CONCAT(' (', (COALESCE(dc.qty_replace, 0) - COALESCE(dc.qty_reject, 0)), ') ')
                            ELSE ' (0)'
                            END
                        ) AS stocker_range_old,
                        CONCAT(s.range_awal, ' - ', s.range_akhir) as stocker_range
                    FROM secondary_inhouse_input a
                    LEFT JOIN (
                        SELECT
                            secondary_inhouse_input.id_qr_stocker,
                            MAX(qty_awal) as qty_awal,
                            SUM(qty_reject) qty_reject,
                            SUM(qty_replace) qty_replace,
                            (MAX(qty_awal) - SUM(qty_reject) + SUM(qty_replace)) as qty_akhir,
                            MAX(secondary_inhouse_input.urutan) AS max_urutan,
                            GROUP_CONCAT(master_secondary.tujuan SEPARATOR ' | ') as tujuan,
                            GROUP_CONCAT(master_secondary.proses SEPARATOR ' | ') as proses
                        FROM secondary_inhouse_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id and part_detail_secondary.urutan = secondary_inhouse_input.urutan
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                        GROUP BY id_qr_stocker
                        having MAX(secondary_inhouse_input.urutan) is not null
                    ) mx ON a.id_qr_stocker = mx.id_qr_stocker AND a.urutan = mx.max_urutan
                    LEFT JOIN stocker_input s ON a.id_qr_stocker = s.id_qr_stocker
                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                    LEFT JOIN form_cut_input f ON f.id = s.form_cut_id
                    LEFT JOIN form_cut_reject fr ON fr.id = s.form_reject_id
                    LEFT JOIN form_cut_piece fp ON fp.id = s.form_piece_id
                    LEFT JOIN part_detail pd ON s.part_detail_id = pd.id
                    LEFT JOIN part p ON pd.part_id = p.id
                    LEFT JOIN master_part mp ON mp.id = pd.master_part_id
                    LEFT JOIN (
                        SELECT id_qr_stocker, qty_reject, qty_replace, tujuan, lokasi, tempat
                        FROM dc_in_input
                    ) dc ON a.id_qr_stocker = dc.id_qr_stocker
                    WHERE
                        a.tgl_trans IS NOT NULL
                        AND (
                            a.urutan IS NULL
                            OR a.urutan = mx.max_urutan
                        )
                        $additionalQuery
                    GROUP BY
                        a.id_qr_stocker
                ) a
            GROUP BY
                act_costing_ws,buyer,style,color,lokasi
        "));

        $act_costing_ws = $data_detail->groupBy("act_costing_ws")->keys();
        $color = $data_detail->groupBy("color")->keys();
        $buyer = $data_detail->groupBy("buyer")->keys();
        $style = $data_detail->groupBy("styleno")->keys();
        $lokasi = $data_detail->groupBy("lokasi")->keys();

        return  array(
            "ws" => $act_costing_ws,
            "color" => $color,
            "buyer" => $buyer,
            "style" => $style,
            "lokasi" => $lokasi
        );
    }

    public function cek_data_stocker_inhouse_old(Request $request)
    {
        $cekdata =  DB::select("
            SELECT
                dc.id_qr_stocker,
                s.act_costing_ws,
                msb.buyer,
                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                msb.styleno as style,
                s.color,
                COALESCE(msb.size, s.size) size,
                mp.nama_part,
                dc.tujuan,
                dc.lokasi,
                COALESCE(sii.id, '-') as in_id,
                COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                COALESCE(sii.user, '-') as author_in,
                COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                ifnull(si.id_qr_stocker,'x')
            from dc_in_input dc
                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input a on s.form_cut_id = a.id
                left join form_cut_reject b on s.form_reject_id = b.id
                left join form_cut_piece c on s.form_piece_id = c.id
                left join part_detail p on s.part_detail_id = p.id
                left join master_part mp on p.master_part_id = mp.id
                left join marker_input mi on a.id_marker = mi.kode
                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
            where
                dc.id_qr_stocker =  '" . $request->txtqrstocker . "'
                and dc.tujuan = 'SECONDARY DALAM'
                and ifnull(si.id_qr_stocker,'x') = 'x'
        ");

        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
    }

    public function cek_data_stocker_inhouse(Request $request)
    {
        // When i wrote this code only god and i knew how it worked, now only god knows it
        // Therefore if you trying to optimize this and fail please increase this counter as a warning for the next person
        // total_wasted_hours = 696

        $stocker = Stocker::where('id_qr_stocker', $request->txtqrstocker)->first();

        if ($stocker) {
            // Check Part Detail
            $partDetail = $stocker->partDetail;
            if ($partDetail) {

                // Check Part Detail Secondary
                $partDetailSecondary = $partDetail->secondaries;

                if ($partDetailSecondary && $partDetailSecondary->count() > 0) {

                    // If there ain't no urutan
                    if ($stocker->urutan == null) {
                        $cekdata = DB::select("
                            SELECT
                                dc.id_qr_stocker,
                                s.act_costing_ws,
                                msb.buyer,
                                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                msb.styleno as style,
                                s.color,
                                COALESCE(msb.size, s.size) size,
                                COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                                CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                                dc.tujuan,
                                dc.lokasi,
                                COALESCE(sii.id, '-') as in_id,
                                COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                COALESCE(sii.user, '-') as author_in,
                                COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                                ifnull(si.id_qr_stocker,'x'),
                                1 as urutan
                            from dc_in_input dc
                                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                left join form_cut_input a on s.form_cut_id = a.id
                                left join form_cut_reject b on s.form_reject_id = b.id
                                left join form_cut_piece c on s.form_piece_id = c.id
                                left join part_detail pd on s.part_detail_id = pd.id
                                left join part p on p.id = pd.part_id
                                left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                                left join part p_com on p_com.id = pd_com.part_id
                                left join master_part mp on p.master_part_id = mp.id
                                left join marker_input mi on a.id_marker = mi.kode
                                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker
                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                            where
                                dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                                and ifnull(si.id_qr_stocker,'x') = 'x'
                        ");

                        return $cekdata && $cekdata[0] ? json_encode($cekdata[0]) : null;
                    }
                    // If there is urutan
                    else {
                        // Current Secondary
                        $currentPartDetailSecondary = $partDetailSecondary->where('urutan', $stocker->urutan)->first();

                        if ($currentPartDetailSecondary && ($currentPartDetailSecondary->secondary && $currentPartDetailSecondary->secondary->tujuan == 'SECONDARY DALAM')) {

                            // Check the Secondary Inhouse IN first
                            $cekdata =  DB::select("
                                SELECT
                                    dc.id_qr_stocker,
                                    s.act_costing_ws,
                                    msb.buyer,
                                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                    msb.styleno as style,
                                    s.color,
                                    COALESCE(msb.size, s.size) size,
                                    COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                                    CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                                    ms.tujuan,
                                    ms.proses lokasi,
                                    COALESCE(sii.id, '-') as in_id,
                                    COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                    COALESCE(sii.user, '-') as author_in,
                                    sii.qty_in qty_awal,
                                    ifnull(si.id_qr_stocker,'x'),
                                    (pds.urutan) as urutan
                                from
                                    dc_in_input dc
                                    left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                    left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                    left join form_cut_input a on s.form_cut_id = a.id
                                    left join form_cut_reject b on s.form_reject_id = b.id
                                    left join form_cut_piece c on s.form_piece_id = c.id
                                    left join part_detail pd on s.part_detail_id = pd.id
                                    left join part p on p.id = pd.part_id
                                    left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                                    left join part p_com on p_com.id = pd_com.part_id
                                    left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                    left join master_part mp on pd.master_part_id = mp.id
                                    left join master_secondary ms on pds.master_secondary_id = ms.id
                                    left join marker_input mi on a.id_marker = mi.kode
                                    left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = pds.urutan
                                    left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                where
                                    ms.tujuan = 'SECONDARY DALAM' and
                                    dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                    pds.urutan = '".$currentPartDetailSecondary->urutan."' and
                                    sii.urutan = '".$currentPartDetailSecondary->urutan."' and
                                    sii.id is not null
                            ");

                            if ($cekdata && $cekdata[0]) {
                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                            }

                            // Check the one step before
                            $multiSecondaryBefore = DB::table("stocker_input")->selectRaw("
                                    stocker_input.id,
                                    stocker_input.id_qr_stocker,
                                    part_detail_secondary.urutan,
                                    master_secondary.tujuan
                                ")->
                                where('id_qr_stocker', $request->txtqrstocker)->
                                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                                leftJoin("part_detail_secondary", "part_detail_secondary.part_detail_id", "=", "part_detail.id")->
                                leftJoin("master_secondary", "master_secondary.id", "=",  "part_detail_secondary.master_secondary_id")->
                                where("part_detail_secondary.urutan", "<", $currentPartDetailSecondary->urutan)->
                                orderBy("part_detail_secondary.urutan", "desc")->
                                first();

                            // When there is a step before
                            if ($multiSecondaryBefore) {

                                // When the tujuan is different
                                if ($multiSecondaryBefore->tujuan != $currentPartDetailSecondary->secondary->tujuan) {

                                    // Check Secondary Before on Secondary In (where the secondary should've finished)
                                    $multiSecondaryBeforeSecondaryIn = DB::table("secondary_in_input")->
                                        where("id_qr_stocker", $request->txtqrstocker)->
                                        where("urutan", $multiSecondaryBefore->urutan)->
                                        first();

                                    // When there is secondary in on the step before then
                                    if ($multiSecondaryBeforeSecondaryIn) {

                                        // Return the data
                                        $cekdata =  DB::select("
                                            SELECT
                                                dc.id_qr_stocker,
                                                s.act_costing_ws,
                                                msb.buyer,
                                                COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                                msb.styleno as style,
                                                s.color,
                                                COALESCE(msb.size, s.size) size,
                                                COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                                                CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                                                ms.tujuan,
                                                ms.proses lokasi,
                                                COALESCE(sii.id, '-') as in_id,
                                                COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                                COALESCE(sii.user, '-') as author_in,
                                                ".($multiSecondaryBeforeSecondaryIn->qty_in)." qty_awal,
                                                ifnull(si.id_qr_stocker,'x'),
                                                (pds.urutan) as urutan
                                            from
                                                dc_in_input dc
                                                left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                                left join form_cut_input a on s.form_cut_id = a.id
                                                left join form_cut_reject b on s.form_reject_id = b.id
                                                left join form_cut_piece c on s.form_piece_id = c.id
                                                left join part_detail pd on s.part_detail_id = pd.id
                                                left join part p on p.id = pd.part_id
                                                left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                                                left join part p_com on p_com.id = pd_com.part_id
                                                left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                                left join master_part mp on pd.master_part_id = mp.id
                                                left join master_secondary ms on pds.master_secondary_id = ms.id
                                                left join marker_input mi on a.id_marker = mi.kode
                                                left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = pds.urutan
                                                left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                            where
                                                dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                                ms.tujuan = 'SECONDARY DALAM' and
                                                pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                        ");

                                        return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                    }
                                }
                                // When the tujuan is the same
                                else {

                                    // Check the before tujuan
                                    $multiSecondaryBeforeSecondary = null;
                                    if ($multiSecondaryBefore->tujuan == 'SECONDARY DALAM') {
                                        $multiSecondaryBeforeSecondary = DB::table("secondary_inhouse_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();
                                    } else {
                                        $multiSecondaryBeforeSecondary = DB::table("secondary_in_input")->
                                            where("id_qr_stocker", $request->txtqrstocker)->
                                            where("urutan", $multiSecondaryBefore->urutan)->
                                            first();
                                    }

                                    // Return the data
                                    $cekdata =  DB::select("
                                        SELECT
                                            dc.id_qr_stocker,
                                            s.act_costing_ws,
                                            msb.buyer,
                                            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                            msb.styleno as style,
                                            s.color,
                                            COALESCE(msb.size, s.size) size,
                                            COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                                            CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                                            ms.tujuan,
                                            ms.proses lokasi,
                                            COALESCE(sii.id, '-') as in_id,
                                            COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                            COALESCE(sii.user, '-') as author_in,
                                            '".($multiSecondaryBeforeSecondary->qty_in)."' qty_awal,
                                            ifnull(si.id_qr_stocker,'x'),
                                            (pds.urutan) as urutan
                                        from
                                            dc_in_input dc
                                            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                            left join form_cut_input a on s.form_cut_id = a.id
                                            left join form_cut_reject b on s.form_reject_id = b.id
                                            left join form_cut_piece c on s.form_piece_id = c.id
                                            left join part_detail pd on s.part_detail_id = pd.id
                                            left join part p on p.id = pd.part_id
                                            left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                                            left join part p_com on p_com.id = pd_com.part_id
                                            left join part_detail_secondary pds on pds.part_detail_id = pd.id
                                            left join master_part mp on pd.master_part_id = mp.id
                                            left join master_secondary ms on pds.master_secondary_id = ms.id
                                            left join marker_input mi on a.id_marker = mi.kode
                                            left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = pds.urutan
                                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                        where
                                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and
                                            ms.tujuan = 'SECONDARY DALAM' and
                                            pds.urutan = '".$currentPartDetailSecondary->urutan."'
                                    ");

                                    return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                                }
                            } else {
                                $cekdata =  DB::select("
                                    SELECT
                                        dc.id_qr_stocker,
                                        s.act_costing_ws,
                                        msb.buyer,
                                        COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                                        msb.styleno as style,
                                        s.color,
                                        COALESCE(msb.size, s.size) size,
                                        COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                                        CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                                        dc.tujuan,
                                        dc.lokasi,
                                        COALESCE(sii.id, '-') as in_id,
                                        COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                                        COALESCE(sii.user, '-') as author_in,
                                        COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                                        ifnull(si.id_qr_stocker,'x'),
                                        1 as urutan
                                    from dc_in_input dc
                                        left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                                        left join form_cut_input a on s.form_cut_id = a.id
                                        left join form_cut_reject b on s.form_reject_id = b.id
                                        left join form_cut_piece c on s.form_piece_id = c.id
                                        left join part_detail pd on s.part_detail_id = pd.id
                                        left join part p on p.id = pd.part_id
                                        left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                                        left join part p_com on p_com.id = pd_com.part_id
                                        left join master_part mp on pd.master_part_id = mp.id
                                        left join marker_input mi on a.id_marker = mi.kode
                                        left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = 1
                                        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                                    where
                                        dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                                        and ifnull(si.id_qr_stocker,'x') = 'x'
                                ");

                                return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                            }
                        } else {
                            return "Part Detail Secondary tidak sesuai.";
                        }
                    }
                }
                // Default
                else {
                    $cekdata =  DB::select("
                        SELECT
                            dc.id_qr_stocker,
                            s.act_costing_ws,
                            msb.buyer,
                            COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                            msb.styleno as style,
                            s.color,
                            COALESCE(msb.size, s.size) size,
                            COALESCE(CONCAT(p_com.panel, (CASE WHEN p_com.panel_status IS NOT NULL THEN CONCAT(' - ', p_com.panel_status) ELSE '' END)), CONCAT(p.panel, (CASE WHEN p.panel_status IS NOT NULL THEN CONCAT(' - ', p.panel_status) ELSE '' END))) panel,
                            CONCAT(mp.nama_part, (CASE WHEN pd.part_status IS NOT NULL THEN CONCAT(' - ', pd.part_status) ELSE '' END)) nama_part,
                            dc.tujuan,
                            dc.lokasi,
                            COALESCE(sii.id, '-') as in_id,
                            COALESCE(sii.updated_at, sii.created_at, '-') as waktu_in,
                            COALESCE(sii.user, '-') as author_in,
                            COALESCE(sii.qty_in, coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace) qty_awal,
                            ifnull(si.id_qr_stocker,'x'),
                            1 as urutan
                        from dc_in_input dc
                            left join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
                            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                            left join form_cut_input a on s.form_cut_id = a.id
                            left join form_cut_reject b on s.form_reject_id = b.id
                            left join form_cut_piece c on s.form_piece_id = c.id
                            left join part_detail pd on s.part_detail_id = pd.id
                            left join part p on p.id = pd.part_id
                            left join part_detail pd_com on pd.id = pd.from_part_detail and pd.part_status = 'complement'
                            left join part p_com on p_com.id = pd_com.part_id
                            left join master_part mp on pd.master_part_id = mp.id
                            left join marker_input mi on a.id_marker = mi.kode
                            left join secondary_inhouse_in_input sii on dc.id_qr_stocker = sii.id_qr_stocker and sii.urutan = 1
                            left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
                        where
                            dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
                            and ifnull(si.id_qr_stocker,'x') = 'x'
                    ");

                    return $cekdata && $cekdata[0] ? json_encode( $cekdata[0]) : null;
                }
            } else {
                return "No Part Detail Found.";
            }
        }

        return "No Stocker Data Found.";
    }

    // public function get_rak(Request $request)
    // {
    //     $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
    //     $html = "<option value=''>Pilih Rak</option>";

    //     foreach ($data_rak as $datarak) {
    //         $html .= " <option value='" . $datarak->isi . "'>" . $datarak->tampil . "</option> ";
    //     }

    //     return $html;
    // }

    public function create()
    {
        return view('dc.secondary-in.create-secondary-in', ['page' => 'dashboard-dc']);
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $validatedRequest = $request->validate([
            "txtqtyreject" => "required"
        ]);

        $saveinhouse = SecondaryInhouse::create([
            'tgl_trans' => $tgltrans,
            'id_qr_stocker' => $request['txtno_stocker'],
            'qty_awal' => $request['txtqtyawal'],
            'qty_reject' => $request['txtqtyreject'],
            'qty_replace' => $request['txtqtyreplace'],
            'qty_in' => $request['txtqtyawal'] - $request['txtqtyreject'] + $request['txtqtyreplace'],
            'user' => Auth::user()->name,
            'urutan' => $request['txturutan'],
            'ket' => $request['txtket'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        DB::update(
            "update stocker_input set status = 'secondary' ".($request->txturutan ? ", urutan = '" . ($request->txturutan + 1) . "'" : "")." where id_qr_stocker = '" . $request->txtno_stocker . "'"
        );

        // dd($savemutasi);
        // $message .= "$tglpindah <br>";

        return array(
            'status' => 300,
            'message' => 'Data Sudah Disimpan',
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    public function massStore(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $thisStocker = Stocker::selectRaw("stocker_input.id_qr_stocker, stocker_input.act_costing_ws, stocker_input.color, COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut, '-') as no_cut")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
            leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
            where("id_qr_stocker", $request['txtno_stocker'])->
            first();

        if ($thisStocker) {
            $cekdata = DB::select("
                SELECT
                    dc.id_qr_stocker,
                    s.act_costing_ws,
                    msb.buyer,
                    COALESCE(a.no_cut, c.no_cut, '-') as no_cut,
                    style,
                    s.color,
                    COALESCE ( msb.size, s.size ) size,
                    mp.nama_part,
                    dc.tujuan,
                    dc.lokasi,
                    COALESCE ( s.qty_ply_mod, s.qty_ply ) - dc.qty_reject + dc.qty_replace qty_awal,
                    ifnull( si.id_qr_stocker, 'x' )
                FROM
                    dc_in_input dc
                    LEFT JOIN stocker_input s ON dc.id_qr_stocker = s.id_qr_stocker
                    LEFT JOIN master_sb_ws msb ON msb.id_so_det = s.so_det_id
                    LEFT JOIN form_cut_input a ON s.form_cut_id = a.id
                    LEFT JOIN form_cut_reject b ON s.form_reject_id = b.id
                    LEFT JOIN form_cut_piece c ON s.form_piece_id = c.id
                    LEFT JOIN part_detail p ON s.part_detail_id = p.id
                    LEFT JOIN master_part mp ON p.master_part_id = mp.id
                    LEFT JOIN marker_input mi ON a.id_marker = mi.kode
                    LEFT JOIN secondary_inhouse_input si ON dc.id_qr_stocker = si.id_qr_stocker
                WHERE
                    s.act_costing_ws = '".$thisStocker->act_costing_ws."' AND
                    s.color = '".$thisStocker->color."' AND
                    COALESCE(a.no_cut, c.no_cut, '-') = '".$thisStocker->no_cut."'
                    AND dc.tujuan = 'SECONDARY DALAM'
                    AND ifnull( si.id_qr_stocker, 'x' ) = 'x'
            ");

            foreach ($cekdata as $d) {
                $saveinhouse = SecondaryInhouse::create([
                    'tgl_trans' => $tgltrans,
                    'id_qr_stocker' => $d->id_qr_stocker,
                    'qty_awal' => $d->qty_awal,
                    'qty_reject' => 0,
                    'qty_replace' => 0,
                    'qty_in' => $d->qty_awal,
                    'user' => Auth::user()->name,
                    'ket' => '',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);


                DB::update(
                    "update stocker_input set status = 'secondary' where id_qr_stocker = '" . $d->id_qr_stocker . "'"
                );
            }

            // dd($savemutasi);
            // $message .= "$tglpindah <br>";

            return array(
                'status' => 300,
                'message' => 'Data Sudah Disimpan',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data gagal Disimpan',
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ExportSecondaryInHouse($request->from, $request->to), 'Laporan sec inhouse '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }

    public function exportExcelDetail(Request $request)
    {
        return Excel::download(new ExportSecondaryInHouseDetail($request->from, $request->to), 'Laporan sec inhouse detail '.$request->from.' - '.$request->to.' ('.Carbon::now().').xlsx');
    }
}
