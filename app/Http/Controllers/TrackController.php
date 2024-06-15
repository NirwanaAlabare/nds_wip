<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Marker;
use App\Models\Part;
use App\Models\MasterPart;
use App\Models\MasterTujuan;
use Yajra\DataTables\Facades\DataTables;
use DB;

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
                SELECT
                    DATE(master_sb_ws.tgl_kirim) tgl_kirim,
                    master_sb_ws.id_act_cost,
                    master_sb_ws.ws,
                    master_sb_ws.styleno,
                    master_sb_ws.color,
                    master_sb_ws.id_so_det,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    master_sb_ws.qty,
                    COALESCE(marker.panel, '-') panel,
                    GROUP_CONCAT(marker.kode_marker),
                    COALESCE(marker.total_gelar, 0) total_gelar_marker,
                    COALESCE(marker_detail.total_ratio, 0) total_ratio_marker,
                    COALESCE(marker_detail.total_cut, 0) total_cut_marker,
                    COALESCE(form_cut.total_lembar, 0) total_lembar_form,
                    COALESCE(form_cut.total_cut, 0) total_cut_form,
                    COALESCE(form_cut.total_stocker, 0) total_stocker
                FROM
                    master_sb_ws
                    LEFT JOIN (
                        SELECT
                            group_concat(marker_input.kode) kode_marker,
                            marker_input.act_costing_ws,
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel,
                            GROUP_CONCAT(marker_input.gelar_qty) gelars,
                            SUM( marker_input.gelar_qty ) total_gelar
                        FROM
                            marker_input
                        GROUP BY
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel
                    ) marker ON marker.act_costing_id = master_sb_ws.id_act_cost AND marker.color = master_sb_ws.color
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel,
                            marker_input_detail.so_det_id,
                            SUM( marker_input_detail.ratio ) total_ratio,
                            SUM( marker_input_detail.cut_qty ) total_cut
                        FROM
                            marker_input_detail
                            LEFT JOIN marker_input ON marker_input.id = marker_input_detail.marker_id
                        GROUP BY
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel,
                            marker_input_detail.so_det_id
                    ) marker_detail ON marker_detail.so_det_id = master_sb_ws.id_so_det AND marker_detail.panel = marker.panel
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_id,
                            marker_input.act_costing_ws,
                            marker_input.color,
                            marker_input.panel,
                            marker_input_detail.so_det_id,
                            marker_input_detail.size,
                            GROUP_CONCAT(no_form),
                            FLOOR(SUM(total_lembar)) total_lembar,
                            FLOOR(SUM(marker_input_detail.ratio * COALESCE(total_lembar, form_cut_input.qty_ply))) total_cut,
                            FLOOR(SUM(stocker.stock_qty)) total_stocker
                        FROM
                            form_cut_input
                            LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                            LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                            LEFT JOIN (
                                SELECT
                                    stock.form_cut_id,
                                    stock.so_det_id,
                                    SUM(stock.stock_qty) stock_qty
                                FROM
                                    (
                                        SELECT
                                            stocker_input.act_costing_ws,
                                            stocker_input.color,
                                            stocker_input.size,
                                            stocker_input.group_stocker,
                                            stocker_input.shade,
                                            stocker_input.ratio,
                                            GROUP_CONCAT(stocker_input.id_qr_stocker),
                                            GROUP_CONCAT(stocker_input.part_detail_id),
                                            stocker_input.form_cut_id,
                                            stocker_input.so_det_id,
                                            COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) stock_qty
                                        FROM
                                            stocker_input
                                        GROUP BY
                                            stocker_input.form_cut_id,
                                            stocker_input.so_det_id,
                                            stocker_input.group_stocker,
                                            stocker_input.shade,
                                            stocker_input.ratio
                                    ) stock
                                    GROUP BY
                                        stock.form_cut_id,
                                        stock.so_det_id
                            ) stocker ON stocker.form_cut_id = form_cut_input.id AND stocker.so_det_id = marker_input_detail.so_det_id
                        WHERE
                            form_cut_input.`status` = 'SELESAI PENGERJAAN'
                        GROUP BY
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel,
                            marker_input_detail.so_det_id
                    ) form_cut ON form_cut.act_costing_id = master_sb_ws.id_act_cost AND form_cut.color = master_sb_ws.color AND form_cut.panel = marker.panel AND form_cut.so_det_id = master_sb_ws.id_so_det
                WHERE
                    MONTH( master_sb_ws.tgl_kirim ) = '".$month."' AND
                    YEAR( master_sb_ws.tgl_kirim ) = '".$year."'
                GROUP BY
                    master_sb_ws.id_act_cost,
                    master_sb_ws.color,
                    marker.panel,
                    master_sb_ws.id_so_det
                ORDER BY
                    FIELD(COALESCE(marker.panel, '-'), '-'),
                    FIELD(COALESCE(SUM( form_cut.total_lembar ), 0) , 0),
                    master_sb_ws.ws,
                    master_sb_ws.color,
                    master_sb_ws.id_so_det
            ");

            return DataTables::of($worksheet)->toJson();
        }

        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view("track.worksheet.worksheet", ["page" => "dashboard-track", "subPageGroup" => "track-ws", "subPage" => "ws", "months" => $months, "years" => $years]);
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

            return view("track.worksheet.worksheet-detail", ["ws" => $ws, "panels" => $panels, "months" => $months, "years" => $years, "masterPart" => $masterPart, "masterTujuan" => $masterTujuan, "meja" => $meja]);
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

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.id_marker like '%" . $request->search["value"] . "%' OR
                        a.no_meja like '%" . $request->search["value"] . "%' OR
                        a.no_form like '%" . $request->search["value"] . "%' OR
                        a.tgl_form_cut like '%" . $request->search["value"] . "%' OR
                        b.act_costing_ws like '%" . $request->search["value"] . "%' OR
                        panel like '%" . $request->search["value"] . "%' OR
                        b.color like '%" . $request->search["value"] . "%' OR
                        a.status like '%" . $request->search["value"] . "%' OR
                        users.name like '%" . $request->search["value"] . "%'
                    )
                ";
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
                    " . $keywordQuery . "
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
}
