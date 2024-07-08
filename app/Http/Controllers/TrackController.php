<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\Marker;
use App\Models\Part;
use App\Models\MasterPart;
use App\Models\MasterTujuan;
use App\Models\Stocker;
use App\Exports\ExportTrackWorksheet;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Excel;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function worksheet(Request $request)
    {
        if ($request->ajax()) {
            $month = date("m");
            $year = date("Y");

            if ($request->month) {
                $month = $request->month;
            }
            if ($request->year) {
                $year = $request->year;
            }

            $worksheet = DB::select("
                SELECT DATE
                    ( master_sb_ws.tgl_kirim ) tgl_kirim,
                    master_sb_ws.id_act_cost,
                    master_sb_ws.ws,
                    master_sb_ws.styleno,
                    master_sb_ws.color,
                    master_sb_ws.id_so_det,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    master_sb_ws.qty,
                    marker_track.kode,
                    marker_track.panel,
                    sum( marker_track.total_gelar_marker ) total_gelar_marker,
                    sum( marker_track.total_ratio_marker ) total_ratio_marker,
                    sum( marker_track.total_cut_marker ) total_cut_marker,
                    sum( marker_track.total_lembar_form ) total_lembar_form,
                    sum( marker_track.total_cut_form ) total_cut_form,
                    sum( marker_track.total_stocker ) total_stocker,
                    sum( marker_track.total_dc ) total_dc,
                    sum( marker_track.total_sec ) total_sec,
                    sum( marker_track.total_sec_in ) total_sec_in
                FROM
                    master_sb_ws
                    LEFT JOIN (
                    SELECT
                        marker.id,
                        marker.act_costing_id,
                        marker.kode,
                        marker.panel,
                        marker_detail.so_det_id,
                        marker.gelar_qty total_gelar_marker,
                        marker_detail.ratio total_ratio_marker,
                        marker_detail.cut_qty total_cut_marker,
                        form_cut.qty_ply total_lembar_form,
                        sum( marker_detail.ratio * form_cut.qty_ply ) total_cut_form,
                        sum( stocker.qty_ply ) total_stocker,
                        sum( stocker.dc_qty_ply ) total_dc,
                        sum( stocker.sec_qty_ply ) total_sec,
                        sum( stocker.sec_in_qty_ply ) total_sec_in
                    FROM
                        marker_input marker
                        LEFT JOIN (
                        SELECT
                            marker_input_detail.marker_id,
                            marker_input_detail.so_det_id,
                            marker_input_detail.size,
                            sum( marker_input_detail.ratio ) ratio,
                            sum( marker_input_detail.cut_qty ) cut_qty
                        FROM
                            marker_input_detail
                        WHERE
                            marker_input_detail.ratio > 0
                        GROUP BY
                            marker_id,
                            so_det_id
                        ) marker_detail ON marker_detail.marker_id = marker.id
                        LEFT JOIN (
                        SELECT
                            form_cut_input.id,
                            form_cut_input.id_marker,
                            form_cut_input.no_form,
                            COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply ) qty_ply
                        FROM
                            form_cut_input
                        WHERE
                            form_cut_input.qty_ply IS NOT NULL
                            AND form_cut_input.id_marker IS NOT NULL
                        ) form_cut ON form_cut.id_marker = marker.kode
                        LEFT JOIN (
                        SELECT
                            *
                        FROM
                            (
                            SELECT
                                stocker_input.form_cut_id,
                                stocker_input.part_detail_id,
                                stocker_input.so_det_id,
                                sum(
                                COALESCE ( stocker_input.qty_ply_mod, stocker_input.qty_ply )) qty_ply,
                                sum((
                                        dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace
                                    )) dc_qty_ply,
                                sum( secondary_in_input.qty_in ) sec_qty_ply,
                                sum( secondary_inhouse_input.qty_in ) sec_in_qty_ply
                            FROM
                                stocker_input
                                LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                                LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = dc_in_input.id_qr_stocker
                                LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                            GROUP BY
                                stocker_input.form_cut_id,
                                stocker_input.part_detail_id,
                                stocker_input.so_det_id
                            ) stocker
                        GROUP BY
                            stocker.form_cut_id,
                            stocker.so_det_id
                        ) stocker ON stocker.form_cut_id = form_cut.id
                        AND stocker.so_det_id = marker_detail.so_det_id
                    GROUP BY
                        marker.id,
                        marker_detail.so_det_id
                    ) marker_track ON marker_track.act_costing_id = master_sb_ws.id_act_cost
                    AND marker_track.so_det_id = master_sb_ws.id_so_det
                WHERE
                    MONTH ( master_sb_ws.tgl_kirim ) = '".$month."'
                    AND YEAR ( master_sb_ws.tgl_kirim ) = '".$year."'
                GROUP BY
                    master_sb_ws.id_so_det,
                    marker_track.panel
                ORDER BY
                    master_sb_ws.id_act_cost,
                    master_sb_ws.color,
                    marker_track.panel,
                    master_sb_ws.id_so_det
            ");

            return DataTables::of($worksheet)->toJson();
        }

        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view("track.worksheet.worksheet", ["page" => "dashboard-track", "subPageGroup" => "track-ws", "subPage" => "ws", "head" => "Track", "months" => $months, "years" => $years]);
    }

    public function worksheetExport(Request $request) {
        ini_set('max_execution_time', 36000);

        $month = $request->month ? $request->month : date('m');
        $year = $request->year ? $request->year : date('Y');

        return Excel::download(new ExportTrackWorksheet($month, $year), 'Laporan_track_worksheet.xlsx');
    }

    public function showWorksheet($actCostingId = null)
    {
        if ($actCostingId) {
            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            $ws = DB::table("master_sb_ws")->
                where("master_sb_ws.id_act_cost", $actCostingId)->
                get();

            $panels = DB::connection('mysql_sb')->select("
                    select nama_panel panel from
                        (select id_panel from bom_jo_item k
                            inner join so_det sd on k.id_so_det = sd.id
                            inner join so on sd.id_so = so.id
                            inner join act_costing ac on so.id_cost = ac.id
                            inner join masteritem mi on k.id_item = mi.id_gen
                            where ac.id = '" . $actCostingId . "' and k.status = 'M'
                            and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                            group by id_panel
                        ) a
                    inner join masterpanel mp on a.id_panel = mp.id
                ");

            $masterPart = MasterPart::all();
            $masterTujuan = DB::select("select tujuan isi, tujuan tampil from master_tujuan");
            $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

            return view("track.worksheet.worksheet-detail", ["page" => "dashboard-track", "subPageGroup" => "track-ws", "subPage" => "ws", "head" => "Track ".$ws->first()->ws, "ws" => $ws, "panels" => $panels, "months" => $months, "years" => $years, "masterPart" => $masterPart, "masterTujuan" => $masterTujuan, "meja" => $meja]);
        }
    }

    public function wsPart(Request $request)
    {
        if ($request->ajax()) {
            // dd($request->actCostingId, $request->panel);

            $part = DB::select("
                SELECT
                    pd.id,
                    p.panel nama_panel,
                    CONCAT(nama_part, ' - ', bag) nama_part,
                    master_secondary_id,
                    ms.tujuan,
                    ms.proses,
                    cons,
                    UPPER(unit) unit
                FROM
                    `part_detail` pd
                    inner join part p on p.id = pd.part_id
                    inner join master_part mp on pd.master_part_id = mp.id
                    left join master_secondary ms on pd.master_secondary_id = ms.id
                where
                    p.act_costing_id = '".$request->actCostingId."' AND
                    p.panel = '".$request->panel."'
                group by
                    pd.id
            ");

            return DataTables::of($part)->toJson();
        }
    }

    public function wsPartId(Request $request)
    {
        $part = Part::where("act_costing_id", $request->actCostingId)->where("panel", $request->panel)->first();

        return $part;
    }

    public function wsMarker(Request $request)
    {
        if ($request->ajax()) {
            $markersQuery = Marker::selectRaw("
                id,
                tgl_cutting,
                DATE_FORMAT(tgl_cutting, '%d-%m-%Y') tgl_cut_fix,
                kode,
                act_costing_ws,
                style,
                color,
                panel,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker)) panjang_marker,
                CONCAT(comma_marker, ' ', UPPER(unit_comma_marker)) comma_marker,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
                CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker,
                COALESCE(gramasi, 0) gramasi,
                gelar_qty,
                gelar_qty_balance,
                po_marker,
                urutan_marker,
                tipe_marker,
                COALESCE(b.total_form, 0) total_form,
                COALESCE(b.total_lembar, 0) total_lembar,
                CONCAT(COALESCE(b.total_lembar, 0), '/', gelar_qty) ply_progress,
                COALESCE(notes, '-') notes,
                cancel
            ")->
            leftJoin(
                DB::raw("
                    (
                        select
                            id_marker,
                            count(id_marker) total_form,
                            sum(total_lembar) total_lembar
                        from
                            form_cut_input
                        group by
                            id_marker
                    ) b"
                ),
                "marker_input.kode",
                "=",
                "b.id_marker"
            );

            return DataTables::eloquent($markersQuery)->filter(function ($query) {
                    $actCostingId = request('actCostingId');
                    $color = request('color');
                    $panel = request('panel');
                    $dateFrom = request('dateFrom');
                    $dateTo = request('dateTo');

                    if ($actCostingId) {
                        $query->whereRaw("act_costing_id = '" . $actCostingId . "'");
                    }

                    if ($color) {
                        $query->whereRaw("color = '" . $color . "'");
                    }

                    if ($panel) {
                        $query->whereRaw("panel = '" . $panel . "'");
                    }

                    if ($dateFrom) {
                        $query->whereRaw("tgl_cutting >= '" . $dateFrom . "'");
                    }

                    if ($dateTo) {
                        $query->whereRaw("tgl_cutting <= '" . $dateTo . "'");
                    }
                }, true)->filterColumn('kode', function ($query, $keyword) {
                    $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('act_costing_ws', function ($query, $keyword) {
                    $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('color', function ($query, $keyword) {
                    $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('panel', function ($query, $keyword) {
                    $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('po_marker', function ($query, $keyword) {
                    $query->whereRaw("LOWER(po_marker) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('cancel', 'asc')->orderBy('color', 'asc')->orderBy('panel', 'asc')->orderBy('urutan_marker', 'desc')->orderBy('updated_at', 'desc');
                })->toJson();
        }

        return view('marker.marker.marker', ["subPageGroup" => "proses-marker", "subPage" => "marker", "page" => "dashboard-marker"]);
    }

    public function wsMarkerTotal(Request $request) {
        $markersQuery = Marker::selectRaw("
                id,
                tgl_cutting,
                DATE_FORMAT(tgl_cutting, '%d-%m-%Y') tgl_cut_fix,
                kode,
                act_costing_ws,
                style,
                color,
                panel,
                panjang_marker marker_p,
                comma_marker marker_c,
                lebar_marker marker_l,
                unit_panjang_marker unit_marker_p,
                unit_comma_marker unit_marker_c,
                unit_lebar_marker unit_marker_l,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker)) panjang_marker,
                CONCAT(comma_marker, ' ', UPPER(unit_comma_marker)) comma_marker,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
                CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker,
                COALESCE(gramasi, 0) gramasi,
                gelar_qty,
                gelar_qty_balance,
                po_marker,
                urutan_marker,
                tipe_marker,
                COALESCE(b.total_form, 0) total_form,
                COALESCE(b.total_lembar, 0) total_lembar,
                CONCAT(COALESCE(b.total_lembar, 0), '/', gelar_qty) ply_progress,
                COALESCE(notes, '-') notes,
                cancel
            ")->
            leftJoin(
                DB::raw("
                    (
                        select
                            id_marker,
                            count(id_marker) total_form,
                            sum(total_lembar) total_lembar
                        from
                            form_cut_input
                        group by
                            id_marker
                    ) b"
                ),
                "marker_input.kode",
                "=",
                "b.id_marker"
            );

        if ($request->actCostingId) {
            $markersQuery->whereRaw("act_costing_id = '" . $request->actCostingId . "'");
        }

        if ($request->color) {
            $markersQuery->whereRaw("color = '" . $request->color . "'");
        }

        if ($request->mrk_color) {
            $markersQuery->whereRaw("LOWER(color) LIKE '%" . $request->mrk_color . "%'");
        }

        if ($request->panel) {
            $markersQuery->whereRaw("panel = '" . $request->panel . "'");
        }

        if ($request->mrk_panel) {
            $markersQuery->whereRaw("LOWER(panel) LIKE '%" . $request->mrk_panel . "%'");
        }

        if ($request->dateFrom) {
            $markersQuery->whereRaw("tgl_cutting >= '" . $request->dateFrom . "'");
        }

        if ($request->dateTo) {
            $markersQuery->whereRaw("tgl_cutting <= '" . $request->dateTo . "'");
        }

        if ($request->kode) {
            $markersQuery->whereRaw("LOWER(kode) LIKE LOWER('%" . $request->kode . "%')");
        }

        if ($request->color) {
            $markersQuery->whereRaw("LOWER(color) LIKE LOWER('%" . $request->color . "%')");
        }

        if ($request->panel) {
            $markersQuery->whereRaw("LOWER(panel) LIKE LOWER('%" . $request->panel . "%')");
        }

        if ($request->urutan) {
            $markersQuery->whereRaw("LOWER(urutan_marker) LIKE LOWER('%" . $request->urutan . "%')");
        }

        if ($request->panjang) {
            $markersQuery->whereRaw("LOWER(panjang_marker) LIKE LOWER('%" . $request->panjang . "%')");
        }

        if ($request->lebar) {
            $markersQuery->whereRaw("LOWER(lebar_marker) LIKE LOWER('%" . $request->lebar . "%')");
        }

        if ($request->gramasi) {
            $markersQuery->whereRaw("LOWER(gramasi_marker) LIKE LOWER('%" . $request->gramasi . "%')");
        }

        if ($request->gelar_qty) {
            $markersQuery->whereRaw("LOWER(gelar_qty) LIKE LOWER('%" . $request->gelar_qty . "%')");
        }

        if ($request->total_form) {
            $markersQuery->whereRaw("COALESCE(b.total_form, 0) LIKE Lrequest->OWER('%" . $total_form . "%')");
        }

        if ($request->po) {
            $markersQuery->whereRaw("LOWER(po_marker) LIKE LOWER('%" . $request->po . "%')");
        }

        if ($request->ket) {
            $markersQuery->whereRaw("LOWER(notes) LIKE LOWER('%" . $request->ket . "%')");
        }

        $totalMarker = $markersQuery ? ($markersQuery->count()) : 0;
        $totalMarkerGramasi =  $markersQuery ? (round($markersQuery->sum("marker_input.gramasi"), 2)) : 0;
        $totalMarkerPanjang =  $markersQuery ? (round($markersQuery->sum("marker_input.panjang_marker") + ($markersQuery->sum("marker_input.comma_marker") / 100), 2)." ".(substr($markersQuery->first()->unit_marker_p, 0, 1))) : 0;
        $totalMarkerLebar =  $markersQuery ? (round($markersQuery->sum("marker_input.lebar_marker") / 100, 2)." ".(substr($markersQuery->first()->unit_marker_p, 0, 1))) : 0;
        $totalMarkerGelar =  $markersQuery ? (round($markersQuery->sum("gelar_qty"), 2)) : 0;
        $totalMarkerForm =  $markersQuery ? (round($markersQuery->sum("total_form"), 2)) : 0;
        $totalMarkerFormLembar =  $markersQuery ? (round($markersQuery->sum("total_lembar"), 2)) : 0;

        return array(
            "totalMarker" => $totalMarker,
            "totalMarkerGramasi" => $totalMarkerGramasi,
            "totalMarkerPanjang" => $totalMarkerPanjang,
            "totalMarkerLebar" => $totalMarkerLebar,
            "totalMarkerGelar" => $totalMarkerGelar,
            "totalMarkerForm" => $totalMarkerForm,
            "totalMarkerFormLembar" => $totalMarkerFormLembar
        );
    }

    public function wsForm(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->actCostingId) {
                $additionalQuery .= " and b.act_costing_id = '" . $request->actCostingId . "' ";
            }

            if ($request->panel) {
                $additionalQuery .= " and b.panel = '" . $request->panel . "' ";
            }

            if ($request->color) {
                $additionalQuery .= " and b.color = '" . $request->color . "' ";
            }

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
            }

            $form = DB::select("
                SELECT
                    a.id,
                    a.no_meja,
                    a.id_marker,
                    a.no_form,
                    a.no_cut,
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color,
                    a.status,
                    UPPER(users.name) nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    COALESCE(a.qty_ply, 0) qty_ply,
                    COALESCE(b.gelar_qty, 0) gelar_qty,
                    COALESCE(a.total_lembar, '0') total_lembar,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(marker_input_detail.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ' /  ') marker_details,
                    cutting_plan.tgl_plan,
                    cutting_plan.app
                FROM `form_cut_input` a
                    left join cutting_plan on cutting_plan.no_form_cut_input = a.no_form
                    left join users on users.id = a.no_meja
                    left join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                    left join marker_input_detail on b.id = marker_input_detail.marker_id and marker_input_detail.ratio > 0
                    left join master_size_new on marker_input_detail.size = master_size_new.size
                where
                    a.id is not null
                    " . $additionalQuery . "
                GROUP BY a.id
                ORDER BY
                    FIELD(a.status, 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'),
                    b.color,
                    b.panel,
                    b.urutan_marker desc
            ");

            return DataTables::of($form)->toJson();
        }
    }

    public function wsFormTotal(Request $request) {
        $additionalQuery = "";

        if ($request->actCostingId) {
            $additionalQuery .= " and b.act_costing_id = '" . $request->actCostingId . "' ";
        }

        if ($request->color) {
            $additionalQuery .= " and b.color = '" . $request->color . "' ";
        }

        if ($request->panel) {
            $additionalQuery .= " and b.panel = '" . $request->panel . "' ";
        }

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
        }

        if ($request->marker) {
            $additionalQuery .= " and a.id_marker like '%" . $request->marker . "%' ";
        }

        if ($request->meja) {
            $additionalQuery .= " and users.name like '%" . $request->meja . "%' ";
        }

        if ($request->no_form) {
            $additionalQuery .= " and a.no_form like '%" . $request->meja . "%' ";
        }

        if ($request->tanggal) {
            $additionalQuery .= " and a.tgl_form_cut like '%" . $request->tanggal . "%' ";
        }

        if ($request->frm_color) {
            $additionalQuery .= " and b.color like '%" . $request->color . "%' ";
        }

        if ($request->frm_panel) {
            $additionalQuery .= " and panel like '%" . $request->color . "%' ";
        }

        if ($request->qty_ply) {
            $additionalQuery .= " and (a.qty_ply like '%" . $request->qty_ply . "% OR a.total_lembar like '%" . $request->qty_ply . "%') ";
        }

        if ($request->plan) {
            $additionalQuery .= " and cutting_plan.tgl_plan like '%" . $request->plan . "%' ";
        }

        $form = collect(DB::select("
            SELECT
                COALESCE(a.qty_ply, 0) qty_ply,
                COALESCE(a.total_lembar, '0') total_lembar
            FROM `form_cut_input` a
                left join cutting_plan on cutting_plan.no_form_cut_input = a.no_form
                left join users on users.id = a.no_meja
                left join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left join marker_input_detail on b.id = marker_input_detail.marker_id and marker_input_detail.ratio > 0
                left join master_size_new on marker_input_detail.size = master_size_new.size
            where
                a.id is not null
                " . $additionalQuery . "
            GROUP BY a.id
            ORDER BY
                FIELD(a.status, 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'),
                b.color,
                b.panel,
                b.urutan_marker desc
        "));

        return array(
            "total_form" => $form ? $form->count() : 0,
            "qty_ply" => $form ? $form->sum('qty_ply') : 0,
            "total_lembar" => $form ? $form->sum('total_lembar') : 0,
        );
    }

    public function wsRoll(Request $request) {
        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->actCostingId) {
                $additionalQuery .= " and mrk.act_costing_id = '" . $request->actCostingId . "' ";
            }

            if ($request->panel) {
                $additionalQuery .= " and mrk.panel = '" . $request->panel . "' ";
            }

            if ($request->color) {
                $additionalQuery .= " and mrk.color = '" . $request->color . "' ";
            }

            if ($request->dateFrom) {
                $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00'";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59'";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        act_costing_ws like '%" . $request->search["value"] . "%' OR
                        DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $pemakaianRoll = DB::select("
                select
                    a.tgl_form_cut,
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
                    act_costing_ws,
                    mrk.color,
                    mrk.panel,
                    COALESCE(id_roll, '-') id_roll,
                    id_item,
                    detail_item,
                    COALESCE(b.color_act, '-') color_act,
                    COALESCE(b.group_roll, '-') group_roll,
                    COALESCE(b.lot, '-') lot,
                    COALESCE(b.roll, '-') roll,
                    b.no_form_cut_input,
                    SUM(b.qty) qty_item,
                    MAX(b.unit) unit_item,
                    SUM(b.sisa_gelaran) sisa_gelaran,
                    SUM(b.sambungan) sambungan,
                    SUM(b.est_amparan) est_amparan,
                    SUM(b.lembar_gelaran) lembar_gelaran,
                    SUM(b.kepala_kain) kepala_kain,
                    SUM(b.sisa_tidak_bisa) sisa_tidak_bisa,
                    SUM(b.reject) reject,
                    SUM(COALESCE(b.sisa_kain, 0)) sisa_kain,
                    SUM(b.total_pemakaian_roll) total_pemakaian_roll,
                    SUM(b.short_roll) short_roll,
                    SUM(b.piping) piping,
                    SUM(b.remark) remark,
                    UPPER(meja.name) nama_meja
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
                    left join marker_input mrk on a.id_marker = mrk.kode
                    left join users meja on meja.id = a.no_meja
                where
                    a.cancel = 'N' and mrk.cancel = 'N' and id_item is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                group by
                    mrk.act_costing_id,
                    mrk.color,
                    mrk.panel,
                    a.no_form,
                    b.id_item
                order by
                    mrk.color asc,
                    mrk.panel asc,
                    b.id_item asc,
                    b.no_form_cut_input desc
            ");

            return DataTables::of($pemakaianRoll)->toJson();
        }
    }

    public function wsStocker(Request $request) {
        if ($request->ajax()) {
            $actCostingId = $request->actCostingId;
            $color = $request->color;
            $panel = $request->panel;
            $size = $request->size;
            $dateFrom = $request->dateFrom;
            $dateTo = $request->dateTo;

            $stockerSql = Stocker::selectRaw("
                marker_input.color,
                marker_input.panel,
                form_cut_input.no_form,
                form_cut_input.no_cut,
                stocker_input.id stocker_id,
                stocker_input.id_qr_stocker,
                stocker_input.act_costing_ws,
                stocker_input.so_det_id,
                stocker_input.size,
                stocker_input.shade,
                stocker_input.ratio,
                COALESCE(master_part.nama_part, ' - ') nama_part,
                CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir, (CASE WHEN dc_in_input.qty_reject IS NOT NULL AND dc_in_input.qty_replace IS NOT NULL THEN CONCAT(' (', (COALESCE(dc_in_input.qty_replace, 0) + COALESCE(secondary_in_input.qty_replace, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) - COALESCE(secondary_in_input.qty_reject, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0)), ') ') ELSE ' (0)' END)) stocker_range,
                stocker_input.status,
                dc_in_input.id dc_in_id,
                dc_in_input.tujuan,
                dc_in_input.tempat,
                dc_in_input.lokasi,
                (CASE WHEN dc_in_input.tujuan = 'SECONDARY DALAM' OR dc_in_input.tujuan = 'SECONDARY LUAR' THEN dc_in_input.lokasi ELSE '-' END) secondary,
                COALESCE(rack_detail_stocker.nm_rak, (CASE WHEN dc_in_input.tempat = 'RAK' THEN dc_in_input.lokasi ELSE null END), (CASE WHEN dc_in_input.lokasi = 'RAK' THEN dc_in_input.det_alokasi ELSE null END), '-') rak,
                COALESCE(trolley.nama_trolley, (CASE WHEN dc_in_input.tempat = 'TROLLEY' THEN dc_in_input.lokasi ELSE null END), '-') troli,
                COALESCE((COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) - COALESCE(secondary_in_input.qty_reject, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) + COALESCE(secondary_in_input.qty_replace, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0)), stocker_input.qty_ply) qty_ply,
                COALESCE(UPPER(loading_line.nama_line), '-') line,
                stocker_input.updated_at latest_update
            ")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("part_detail", "stocker_input.part_detail_id", "=", "part_detail.id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
            leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "stocker_input.id")->
            leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id");

            if ($actCostingId) {
                $stockerSql->whereRaw("marker_input.act_costing_id = '" . $actCostingId . "'");
            }

            if ($color) {
                $stockerSql->whereRaw("marker_input.color = '" . $color . "'");
            }

            if ($panel) {
                $stockerSql->whereRaw("marker_input.panel = '" . $panel . "'");
            }

            if ($size) {
                $stockerSql->whereRaw("stocker_input.size = '" . $size . "'");
            }

            if ($dateFrom) {
                $stockerSql->whereRaw("DATE(stocker_input.created_at) >= '" . $dateFrom . "'");
            }

            if ($dateTo) {
                $stockerSql->whereRaw("DATE(stocker_input.updated_at) <= '" . $dateTo . "'");
            }

            $stocker = $stockerSql->
                groupBy("stocker_input.id_qr_stocker")->
                orderBy("stocker_input.act_costing_ws", "asc")->
                orderBy("stocker_input.color", "asc")->
                orderBy("form_cut_input.no_cut", "asc")->
                orderBy("master_part.nama_part", "asc")->
                orderBy("stocker_input.so_det_id", "asc")->
                orderBy("stocker_input.shade", "desc")->
                orderBy("stocker_input.id_qr_stocker", "asc");

            return DataTables::eloquent($stocker)->filter(function ($query) {
                $tglAwal = request('tgl_awal');
                $tglAkhir = request('tgl_akhir');

                if ($tglAwal) {
                    $query->whereRaw("tgl_cutting >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("tgl_cutting <= '" . $tglAkhir . "'");
                }

                if (request('search')['value']) {
                    $query->whereRaw("(
                        marker_input.color LIKE '%".request('search')['value']."%' OR
                        marker_input.panel LIKE '%".request('search')['value']."%' OR
                        master_part.nama_part LIKE '%".request('search')['value']."%' OR
                        form_cut_input.no_form LIKE '%".request('search')['value']."%' OR
                        form_cut_input.no_cut LIKE '%".request('search')['value']."%' OR
                        stocker_input.size LIKE '%".request('search')['value']."%' OR
                        stocker_input.shade LIKE '%".request('search')['value']."%' OR
                        stocker_input.id_qr_stocker LIKE '%".request('search')['value']."%' OR
                        secondary_in_input. LIKE '%".request('search')['value']."%' OR
                        (CASE WHEN dc_in_input.tujuan = 'SECONDARY DALAM' OR dc_in_input.tujuan = 'SECONDARY LUAR' THEN dc_in_input.lokasi ELSE '-' END) LIKE '%".request('search')['value']."%' OR
                        COALESCE(rack_detail_stocker.nm_rak, (CASE WHEN dc_in_input.tempat = 'RAK' THEN dc_in_input.lokasi ELSE null END), (CASE WHEN dc_in_input.lokasi = 'RAK' THEN dc_in_input.det_alokasi ELSE null END), '-') LIKE '%".request('search')['value']."%' OR
                        COALESCE(trolley.nama_trolley, (CASE WHEN dc_in_input.tempat = 'TROLLEY' THEN dc_in_input.lokasi ELSE null END), '-') LIKE '%".request('search')['value']."%' OR
                        COALESCE(UPPER(loading_line.nama_line), '-') LIKE '%".request('search')['value']."%'
                    )");
                }
            }, true)->
            filterColumn('color', function ($query, $keyword) {
                $query->whereRaw("LOWER(marker_input.color) LIKE LOWER('%" . $keyword . "%')");
            })->
            filterColumn('panel', function ($query, $keyword) {
                $query->whereRaw("LOWER(marker_input.panel) LIKE LOWER('%" . $keyword . "%')");
            })->
            filterColumn('nama_part', function ($query, $keyword) {
                $query->whereRaw("LOWER(master_part.nama_part) LIKE LOWER('%" . $keyword . "%')");
            })->
            filterColumn('kode', function ($query, $keyword) {
                $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
            })->
            filterColumn('kode', function ($query, $keyword) {
                $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
            })->
            toJson();
        }
    }
}
