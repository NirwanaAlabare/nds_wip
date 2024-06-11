<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            $worksheet = DB::select("
                SELECT
                    master_sb_ws.id_act_cost,
                    master_sb_ws.ws,
                    master_sb_ws.styleno,
                    master_sb_ws.color,
                    master_sb_ws.id_so_det,
                    master_sb_ws.size,
                    master_sb_ws.dest,
                    COALESCE(marker.panel, '-') panel,
                    COALESCE(marker.total_gelar, 0) total_gelar_marker,
                    COALESCE(marker_detail.total_cut, 0) total_cut_marker,
                    COALESCE(form_cut.total_lembar, 0) total_lembar_form,
                    COALESCE(form_cut.total_cut, 0) total_cut_form
                FROM
                    master_sb_ws
                    LEFT JOIN (
                    SELECT
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
                        marker_input_detail.so_det_id,
                        SUM( marker_input_detail.ratio ) total_ratio,
                        SUM( marker_input_detail.cut_qty ) total_cut
                    FROM
                        marker_input_detail
                        LEFT JOIN marker_input ON marker_input.id = marker_input_detail.marker_id
                    GROUP BY
                        marker_input_detail.so_det_id
                    ) marker_detail ON marker_detail.so_det_id = master_sb_ws.id_so_det
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel,
                            marker_input_detail.so_det_id,
                            GROUP_CONCAT(no_form),
                            FLOOR(SUM(total_lembar)) total_lembar,
                            FLOOR(SUM(ratio * total_lembar)) total_cut
                        FROM
                            form_cut_input
                            LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                            LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                        GROUP BY
                            marker_input.act_costing_id,
                            marker_input.color,
                            marker_input.panel,
                            marker_input_detail.so_det_id
                    ) form_cut ON form_cut.act_costing_id = master_sb_ws.id_act_cost AND form_cut.color = master_sb_ws.color AND form_cut.panel = marker.panel AND form_cut.so_det_id = master_sb_ws.id_so_det
                WHERE
                    MONTH( master_sb_ws.tgl_kirim ) = '06' AND
                    YEAR( master_sb_ws.tgl_kirim ) = '2024'
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

        return view("track.worksheet");
    }
}
