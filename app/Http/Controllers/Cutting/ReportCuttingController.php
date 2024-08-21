<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Exports\ExportReportCutting;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ReportCuttingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cutting(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and form_cut_input.tgl_form_cut >= '".$request->dateFrom."'";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and form_cut_input.tgl_form_cut <= '".$request->dateTo."'";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        marker_cutting.tgl_form_cut like '%" . $request->search["value"] . "%' OR
                        marker_cutting.meja like '%" . $request->search["value"] . "%' OR
                        marker_cutting.buyer like '%" . $request->search["value"] . "%' OR
                        marker_cutting.act_costing_ws like '%" . $request->search["value"] . "%' OR
                        marker_cutting.style like '%" . $request->search["value"] . "%' OR
                        marker_cutting.color like '%" . $request->search["value"] . "%' OR
                        marker_cutting.notes like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $reportCutting = DB::select("
                SELECT
                    marker_cutting.tgl_form_cut,
                    marker_cutting.meja,
                    marker_cutting.buyer,
                    marker_cutting.act_costing_ws,
                    marker_cutting.style,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.cons_ws,
                    marker_cutting.unit,
                    marker_cutting.so_det_id,
                    marker_cutting.size,
                    COALESCE(marker_cutting.notes, '-') notes,
                    SUM(marker_cutting.marker_gelar * marker_cutting.ratio) marker_gelar,
                    SUM(marker_cutting.spreading_gelar  * marker_cutting.ratio) spreading_gelar,
                    SUM(marker_cutting.form_gelar * marker_cutting.ratio) form_gelar
                FROM
                    (
                        SELECT
                            marker_input.kode,
                            form_cut.meja,
                            form_cut.tgl_form_cut,
                            marker_input.buyer,
                            marker_input.act_costing_id,
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            marker_input.cons_ws,
                            marker_input.unit_panjang_marker unit,
                            marker_input_detail.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            marker_input_detail.ratio,
                            COALESCE(marker_input.notes, form_cut.notes) notes,
                            marker_input.gelar_qty marker_gelar,
                            SUM(form_cut.qty_ply) spreading_gelar,
                            SUM(COALESCE(form_cut.total_lembar, form_cut.detail)) form_gelar
                        FROM
                            marker_input
                            INNER JOIN
                                marker_input_detail on marker_input_detail.marker_id = marker_input.id
                            INNER JOIN
                                master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                            INNER JOIN
                                (
                                    SELECT
                                        meja.`name` meja,
                                        DATE(form_cut_input.waktu_mulai) tgl_form_cut,
                                        form_cut_input.id_marker,
                                        form_cut_input.no_form,
                                        form_cut_input.qty_ply,
                                        form_cut_input.total_lembar,
                                        form_cut_input.notes,
                                        SUM(form_cut_input_detail.lembar_gelaran) detail
                                    FROM
                                        form_cut_input
                                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                                    WHERE
                                        form_cut_input.`status` != 'SPREADING'
                                        AND form_cut_input.waktu_mulai is not null
                                        ".$additionalQuery."
                                    GROUP BY
                                        form_cut_input.no_form
                                ) form_cut on form_cut.id_marker = marker_input.kode
                            where
                                (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                            group by
                                marker_input.id,
                                marker_input_detail.so_det_id,
                                form_cut.tgl_form_cut
                    ) marker_cutting
                GROUP BY
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.tgl_form_cut
                ORDER BY
                    marker_cutting.panel,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.so_det_id,
                    marker_cutting.tgl_form_cut
                ");

            return DataTables::of($reportCutting)->toJson();
        }

        return view('cutting.report.report-cutting', ['page' => 'dashboard-cutting', "subPageGroup" => "cutting-report", "subPage" => "cutting"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */

    public function export(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportReportCutting($request->dateFrom, $request->dateTo), 'Report Cutting.xlsx');
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
