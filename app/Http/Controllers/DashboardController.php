<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Rack;
use App\Models\Stocker;
use App\Models\Marker;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\DCIn;
use App\Models\Auth\User;
use Yajra\DataTables\Facades\DataTables;
use App\Events\CuttingChartUpdated;
use App\Events\CuttingChartUpdatedAll;
use DB;

class DashboardController extends Controller
{
    public function track(Request $request) {
        return Redirect::to('/home');
    }

    // Marker
        public function marker(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            if ($request->ajax()) {
                $month = date("m");
                $year = date("Y");

                if ($request->month) {
                    $month = $request->month;
                }
                if ($request->year) {
                    $year = $request->year;
                }

                $marker = Marker::selectRaw("
                        marker_input.id as marker_id,
                        marker_input.buyer,
                        marker_input.act_costing_ws,
                        marker_input.style,
                        marker_input.color,
                        marker_input.tgl_cutting,
                        marker_input.kode,
                        marker_input.urutan_marker,
                        marker_input.gelar_qty,
                        marker_input.panel,
                        GROUP_CONCAT(DISTINCT CONCAT(master_sb_ws.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ' / ') marker_details,
                        COALESCE(CONCAT(master_part.nama_part, ' / ', CONCAT(COALESCE(part_detail.cons, '-'), ' ', COALESCE(UPPER(part_detail.unit), '-')), ' / ', CONCAT(COALESCE(master_secondary.tujuan, '-'), ' - ', COALESCE(master_secondary.proses, '-')) ), '-') nama_part
                    ")->
                    leftJoin("part", function ($join) {
                        $join->on("part.act_costing_id", "=", "marker_input.act_costing_id");
                        $join->on("part.panel", "=", "marker_input.panel");
                    })->
                    leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                    leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                    leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                    leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                    leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
                    leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size")->
                    whereRaw("(marker_input.cancel IS NULL OR marker_input.cancel != 'Y')")->
                    whereRaw("(MONTH(marker_input.tgl_cutting) = '".$month."')")->
                    whereRaw("(YEAR(marker_input.tgl_cutting) = '".$year."')")->
                    whereRaw("marker_input_detail.ratio > 0")->
                    groupBy("marker_input.act_costing_id", "marker_input.buyer", "marker_input.style", "marker_input.color", "marker_input.panel", "marker_input.id", "part_detail.id")->
                    orderBy("marker_input.buyer", "asc")->
                    orderBy("marker_input.act_costing_ws", "asc")->
                    orderBy("marker_input.style", "asc")->
                    orderBy("marker_input.color", "asc")->
                    orderBy("marker_input.panel", "asc")->
                    orderByRaw("CAST(marker_input.urutan_marker AS UNSIGNED) asc");

                return DataTables::eloquent($marker)->toJson();
            }

            return view('dashboard', ['page' => 'dashboard-marker', 'months' => $months, 'years' => $years]);
        }

        public function markerQty(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $month = date("m");
            $year = date("Y");

            if ($request->month) {
                $month = $request->month;
            }
            if ($request->year) {
                $year = $request->year;
            }

            $markerQty = DB::select("
                SELECT
                    COUNT(id) marker_count,
                    SUM(gelar_qty) total_gelar
                FROM (
                    SELECT
                        id,
                        gelar_qty
                    FROM
                        marker_input
                    WHERE
                        MONTH(tgl_cutting) = '".$month."' AND YEAR(tgl_cutting) = '".$year."' AND
                        (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                ) marker
            ")[0];

            $partQty = DB::select("
                SELECT
                    COUNT(id) part_count
                FROM (
                    SELECT
                        id
                    FROM
                        part
                    WHERE
                        (MONTH(created_at) = '".$month."' AND  YEAR(created_at) = '".$year."') OR (MONTH(updated_at) = '".$month."' AND  YEAR(updated_at) = '".$year."')
                ) part
            ")[0]->part_count;

            $wsQty = DB::select("
                SELECT
                    COUNT(act_costing_ws) ws_count
                FROM (
                    SELECT
                        act_costing_ws
                    FROM
                        marker_input
                    WHERE
                        ((MONTH(tgl_cutting) = '".$month."' AND  YEAR(tgl_cutting) = '".$year."') OR (MONTH(tgl_cutting) = '".$month."' AND  YEAR(tgl_cutting) = '".$year."')) AND
                        (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                    GROUP BY
                        act_costing_ws
                ) ws
            ")[0]->ws_count;

            return array(
                "markerQty" => $markerQty->marker_count,
                "markerSum" => $markerQty->total_gelar,
                "partQty" => $partQty,
                "wsQty" => $wsQty
            );
        }
    // End of Marker

    // Cutting
        public function cutting(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            if ($request->ajax()) {
                // $month = date("m");
                // $year = date("Y");

                // if ($request->month) {
                //     $month = $request->month;
                // }
                // if ($request->year) {
                //     $year = $request->year;
                // }

                $date = $request->date ? $request->date : date("Y-m-d");

                $form = DB::select("
                    SELECT
                        master_sb.ws,
                        master_sb.styleno,
                        master_sb.total_order,
                        form_cut_all.total_lembar,
                        COALESCE(form_marker.tanggal, 0) tanggal,
                        COALESCE(form_marker.total_plan, 0) total_plan,
                        COALESCE(form_marker.total_complete, 0) total_complete
                    FROM
                    (
                        SELECT
                            master_sb_ws.ws,
                            master_sb_ws.styleno,
                            sum( qty ) total_order
                        FROM
                            master_sb_ws
                        GROUP BY
                            master_sb_ws.ws,
                            master_sb_ws.styleno
                    ) master_sb
                    INNER JOIN
                    (
                        SELECT
                            marker_input.act_costing_ws,
                            marker_input.style,
                            form_cut_plan.tanggal,
                            SUM(marker_detail.total_ratio * form_cut_plan.total_lembar) total_plan,
                            SUM(marker_detail.total_ratio * form_cut_complete.total_lembar) total_complete
                        FROM
                            marker_input
                        INNER JOIN
                            (
                                select
                                    marker_input_detail.marker_id,
                                    SUM(marker_input_detail.ratio) total_ratio
                                from
                                    marker_input_detail
                                group by
                                    marker_input_detail.marker_id
                            ) marker_detail on marker_detail.marker_id = marker_input.id
                        INNER JOIN
                            (
                                select
                                    form_cut_input.id_marker,
                                    (CASE WHEN	cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END) tanggal,
                                    SUM(COALESCE(form_cut_input.total_lembar, form_cut_input.qty_ply)) total_lembar
                                from
                                    form_cut_input
                                    left join cutting_plan on cutting_plan.form_cut_id = form_cut_input.id
                                where
                                    ( form_cut_input.cancel is null or form_cut_input.cancel != 'Y' )
                                    and ( cutting_plan.tgl_plan = '".$date."' OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' ) )
                                group by
                                    form_cut_input.id_marker
                            ) form_cut_plan on form_cut_plan.id_marker = marker_input.kode
                        LEFT JOIN
                            (
                                select
                                    form_cut_input.id_marker,
                                    (CASE WHEN	cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END) tanggal,
                                    SUM(COALESCE(form_cut_input.total_lembar, form_cut_input.qty_ply)) total_lembar
                                from
                                    form_cut_input
                                    left join cutting_plan on cutting_plan.form_cut_id = form_cut_input.id
                                where
                                    ( form_cut_input.cancel is null or form_cut_input.cancel != 'Y' )
                                    and ( cutting_plan.tgl_plan = '".$date."' OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' ) )
                                    and form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                group by
                                    form_cut_input.id_marker
                            ) form_cut_complete on form_cut_complete.id_marker = marker_input.kode
                        WHERE
                            (marker_input.cancel IS NULL or marker_input.cancel != 'Y')
                        GROUP BY
                            marker_input.act_costing_ws,
                            marker_input.style
                    ) form_marker on form_marker.act_costing_ws = master_sb.ws and form_marker.style = master_sb.styleno
                    LEFT JOIN
                        (
                            select
                                marker_input.act_costing_ws,
                                marker_input.style,
                                ROUND(SUM(marker_detail.total_ratio * form.total_lembar)) total_lembar
                            from
                                marker_input
                            left join (
                                select
                                    form_cut_input.id_marker,
                                    SUM(COALESCE(form_cut_input.total_lembar, form_cut_input.qty_ply,0)) total_lembar
                                from
                                    form_cut_input
                                    left join cutting_plan on cutting_plan.form_cut_id = form_cut_input.id
                                where
                                    form_cut_input.status = 'SELESAI PENGERJAAN' AND
                                    ( form_cut_input.cancel is null or form_cut_input.cancel != 'Y' )
                                group by
                                    form_cut_input.id_marker
                            ) form on form.id_marker = marker_input.kode
                            left join (
                                select
                                    marker_input_detail.marker_id,
                                    sum(marker_input_detail.ratio) total_ratio
                                from
                                    marker_input_detail
                                group by
                                    marker_input_detail.marker_id
                            ) marker_detail on marker_detail.marker_id = marker_input.id
                            group by
                                marker_input.act_costing_ws,
                                marker_input.style
                        ) form_cut_all on form_cut_all.act_costing_ws = master_sb.ws and form_cut_all.style = master_sb.styleno
                ");

                return DataTables::of($form)->toJson();
            }

            return view('dashboard', ['page' => 'dashboard-cutting', 'months' => $months, 'years' => $years]);
        }

        public function cuttingMeja() {
            return view('cutting.chart.dashboard-chart');
        }

        public function cuttingMejaDetail(Request $request, $mejaId = null)
        {
            // Ambil parameter tgl_plan dari query string
            $tglPlan = $request->query('tgl_plan', null);

            // Lakukan proses sesuai kebutuhan
            return view('cutting.chart.dashboard-chart-detail', [
                'mejaId' => $mejaId,
                'tglPlan' => $tglPlan
            ]);
        }

        public function cuttingDashboardList() {
            $listMeja = User::where("type", "meja")->get();

            return view('cutting.chart.dashboard-chart-list', ['page' => 'dashboard-cutting', 'listMeja' => $listMeja]);
        }

        public function cuttingFormList(Request $request) {
            $mejaId = $request->meja_id ? $request->meja_id : null;
            $date = $request->date ? $request->date : date("Y-m-d");

            $data = DB::select("
                SELECT
                    form_cut_input.no_form,
                    marker_input.panel,
                    marker_input.style,
                    marker_input.color,
                    marker_detail.total_ratio,
                    COALESCE(form_cut_input.total_lembar, form_detail.total_gelaran) total_lembar,
                    COALESCE(form_cut_input.qty_ply, 0) target_lembar,
                    COALESCE((marker_detail.total_ratio * form_cut_input.total_lembar), (marker_detail.total_ratio * form_detail.total_gelaran)) total_output,
                    COALESCE(marker_detail.total_ratio * form_cut_input.qty_ply, 0) target_output,
                    form_cut_input.`status`
                FROM
                    form_cut_input
                    left join (select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail ON form_detail.form_cut_id = form_cut_input.id
                    left join marker_input on marker_input.kode = form_cut_input.id_marker
                    left join (select marker_id, SUM(ratio) total_ratio from marker_input_detail group by marker_id) as marker_detail on marker_detail.marker_id = marker_input.id
                    left join users as meja on meja.id = form_cut_input.no_meja
                    left join cutting_plan on cutting_plan.form_cut_id = form_cut_input.id
                WHERE
                    (cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')))
                    and
                    meja.username = '".$mejaId."'
                ORDER BY
                    FIELD(form_cut_input.status, 'PENGERJAAN FORM CUTTING SPREAD', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN MARKER', 'SPREADING', 'SELESAI PENGERJAAN'),
                    FIELD(form_cut_input.tipe_form_cut, null, 'NORMAL', 'MANUAL'),
                    FIELD(cutting_plan.app, 'Y', 'N', null)
            ");

            return DataTables::of($data)->toJson();
        }

        public function cuttingWorksheetList(Request $request) {
            $mejaId = $request->meja_id ? $request->meja_id : null;
            $date = $request->date ? $request->date : date("Y-m-d");

            $data = DB::select("
                SELECT
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    marker_detail.total_ratio,
                    SUM(COALESCE(form_cut_input.total_lembar, form_detail.total_gelaran)) total_lembar,
                    SUM(COALESCE((marker_detail.total_ratio * form_cut_input.total_lembar), (marker_detail.total_ratio * form_detail.total_gelaran))) output,
                    form_cut_input.`status`
                FROM
                    form_cut_input
                    left join (select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail ON form_detail.form_cut_id = form_cut_input.id
                    left join marker_input on marker_input.kode = form_cut_input.id_marker
                    left join (select marker_id, SUM(ratio) total_ratio from marker_input_detail group by marker_id) as marker_detail on marker_detail.marker_id = marker_input.id
                    left join users as meja on meja.id = form_cut_input.no_meja
                    left join cutting_plan on form_cut_input.id = cutting_plan.form_cut_id
                WHERE
                    (cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')))
                    and
                    meja.username = '".$mejaId."'
                GROUP BY
                    marker_input.act_costing_id,
                    marker_input.color,
                    marker_input.panel,
                    meja.id
            ");

            return DataTables::of($data)->toJson();
        }

        public function cuttingWorksheetTotal(Request $request) {
            $mejaId = $request->meja_id ? $request->meja_id : null;
            $date = $request->date ? $request->date : date("Y-m-d");

            $data = DB::select("
                SELECT
                    SUM(output) total_output
                FROM (
                    SELECT
                        marker_input.act_costing_ws,
                        marker_input.buyer,
                        marker_input.style,
                        marker_input.color,
                        marker_input.panel,
                        marker_detail.total_ratio,
                        SUM(COALESCE(form_cut_input.total_lembar, form_detail.total_gelaran)) total_lembar,
                        SUM(COALESCE((marker_detail.total_ratio * form_cut_input.total_lembar), (marker_detail.total_ratio * form_detail.total_gelaran))) output,
                        form_cut_input.`status`
                    FROM
                        form_cut_input
                        left join (select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail ON form_detail.form_cut_id = form_cut_input.id
                        left join marker_input on marker_input.kode = form_cut_input.id_marker
                        left join (select marker_id, SUM(ratio) total_ratio from marker_input_detail group by marker_id) as marker_detail on marker_detail.marker_id = marker_input.id
                        left join users as meja on meja.id = form_cut_input.no_meja
                        left join cutting_plan on form_cut_input.id = cutting_plan.form_cut_id
                    WHERE
                        (cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')))
                        and
                        meja.username = '".$mejaId."'
                    GROUP BY
                        marker_input.act_costing_id,
                        marker_input.color,
                        marker_input.panel,
                        (CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN ( CASE WHEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) != '".$date."' THEN DATE(form_detail.last_update) ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END ) ELSE cutting_plan.tgl_plan END),
                        meja.id
                ) output
            ");

            return $data;
        }

        public function cutting_chart(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $date = $request->date ? $request->date : date("Y-m-d");
            // $date = '2024-12-04';

            $query = DB::table('form_cut_input')->
            selectRaw("
                meja.username no_meja,
                cutting_plan.tgl_plan,
                COUNT(form_cut_input.id) total_form,
                SUM(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) incomplete_form,
                SUM(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) completed_form
            ")->
            leftJoin(DB::raw("(select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail"), "form_detail.form_cut_id", "=", "form_cut_input.id")->
            leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
            leftJoin("cutting_plan", "cutting_plan.form_cut_id", "form_cut_input.id")->
            join("users as meja", "meja.id", "form_cut_input.no_meja")->
            whereRaw("
                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' ) AND
                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' ) AND
                ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
            ")->
            groupByRaw("(CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN ( CASE WHEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) != '".$date."' THEN DATE(form_detail.last_update) ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END ) ELSE cutting_plan.tgl_plan END), meja.id")->
            get();

            return json_encode($query);
        }

        public function cutting_chart_trigger_all($currentDate) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $date = $currentDate;

            if (!$date) {
                $date = date('Y-m-d');
            }

            $query = DB::table('form_cut_input')->
            selectRaw("
                meja.username no_meja,
                cutting_plan.tgl_plan,
                COUNT(form_cut_input.id) total_form,
                SUM(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) incomplete_form,
                SUM(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) completed_form
            ")->
            leftJoin(DB::raw("(select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail"), "form_detail.form_cut_id", "=", "form_cut_input.id")->
            leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
            leftJoin("cutting_plan", "cutting_plan.form_cut_id", "form_cut_input.id")->
            join("users as meja", "meja.id", "form_cut_input.no_meja")->
            whereRaw("
                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' ) AND
                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' ) AND
                ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
            ")->
            groupByRaw("(CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN ( CASE WHEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) != '".$date."' THEN DATE(form_detail.last_update) ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END ) ELSE cutting_plan.tgl_plan END), meja.id")->
            get();

            broadcast(new CuttingChartUpdatedAll($query, $date));
            return json_encode($query);

        }

        public function cutting_chart_by_mejaid(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $meja_ids = $request->meja_id ? $request->meja_id : null;
            $date = $request->date ? $request->date : date("Y-m-d");
            // $date = '2024-12-04';

            $query = DB::table('form_cut_input')
            ->selectRaw("
                meja.username no_meja,
                cutting_plan.tgl_plan,
                COUNT(form_cut_input.id) total_form,
                GROUP_CONCAT(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN form_cut_input.no_form ELSE '-    ' END) forms_belum,
                GROUP_CONCAT(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN form_cut_input.no_form ELSE '-' END) forms_selesai,
                SUM(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) incomplete_form,
                SUM(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) completed_form
            ")
            ->leftJoin(DB::raw("(select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail"), "form_detail.form_cut_id", "=", "form_cut_input.id")
            ->leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")
            ->leftJoin("cutting_plan", "cutting_plan.form_cut_id", "form_cut_input.id")
            ->join("users as meja", "meja.id", "form_cut_input.no_meja")
            ->whereRaw("
                (marker_input.cancel IS NULL OR marker_input.cancel != 'Y') AND
                (form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y') AND
                ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_detail.last_update), DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."')) )
            ")
            ->when($meja_ids, function ($query) use ($meja_ids) {
                return $query->whereIn('meja.username', $meja_ids);
            })
            ->groupByRaw("(CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN ( CASE WHEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) != '".$date."' THEN DATE(form_detail.last_update) ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END ) ELSE cutting_plan.tgl_plan END), meja.id")
            ->get();

            return response()->json($query);
        }

        public function cutting_trigger_chart_by_mejaid($currentDate, $mejaId) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $meja_ids = $mejaId ? [$mejaId] : null;

            $date = $currentDate;

            if (!$date) {
                $date = date('Y-m-d');
            }

            $query = DB::table('form_cut_input')
            ->selectRaw("
                meja.username no_meja,
                cutting_plan.tgl_plan,
                COUNT(form_cut_input.id) total_form,
                GROUP_CONCAT(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN form_cut_input.no_form ELSE '-    ' END) forms_belum,
                GROUP_CONCAT(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN form_cut_input.no_form ELSE '-' END) forms_selesai,
                SUM(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) incomplete_form,
                SUM(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) completed_form
            ")
            ->leftJoin(DB::raw("(select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail"), "form_detail.form_cut_id", "=", "form_cut_input.id")
            ->leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")
            ->leftJoin("cutting_plan", "cutting_plan.form_cut_id", "form_cut_input.id")
            ->join("users as meja", "meja.id", "form_cut_input.no_meja")
            ->whereRaw("
                (marker_input.cancel IS NULL OR marker_input.cancel != 'Y') AND
                (form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y') AND
                ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
            ")
            ->when($meja_ids, function ($query) use ($meja_ids) {
                return $query->whereIn('meja.username', $meja_ids);
            })
            ->groupByRaw("(CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN ( CASE WHEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) != '".$date."' THEN DATE(form_detail.last_update) ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END ) ELSE cutting_plan.tgl_plan END), meja.id")
            ->get();

            $dataSpreading = DB::select("
                SELECT
                    a.id,
                    a.no_meja,
                    a.id_marker,
                    a.no_form,
                    COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai), a.tgl_form_cut) tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color color,
                    a.status,
                    UPPER(users.name) nama_meja,
                    b.panjang_marker panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    COALESCE(a.qty_ply, 0) qty_ply,
                    COALESCE(b.gelar_qty, 0) gelar_qty,
                    COALESCE(a.total_lembar, '0') total_lembar,
                    b.po_marker po_marker,
                    b.urutan_marker urutan_marker,
                    b.cons_marker cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    cutting_plan.app,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(marker_input_detail.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ' / ') marker_details
                FROM cutting_plan
                left join form_cut_input a on a.id = cutting_plan.form_cut_id
                left join (select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail on form_detail.form_cut_id = a.id
                left outer join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left outer join marker_input_detail on b.id = marker_input_detail.marker_id and marker_input_detail.ratio > 0
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                where
                    a.id is not null
                    and ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
                    and users.username
                GROUP BY a.id
                ORDER BY
                    FIELD(a.status, 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'),
                    FIELD(a.tipe_form_cut, null, 'NORMAL', 'MANUAL'),
                    FIELD(cutting_plan.app, 'Y', 'N', null),
                    a.no_form desc,
                    a.updated_at desc
            ");

            broadcast(new CuttingChartUpdated($query, $mejaId, $date, $dataSpreading));

            return response()->json($query);
        }

        public function get_cutting_chart_meja(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $date = $request->date ? $request->date : date("Y-m-d");

            $query = DB::table('form_cut_input')
                ->select('meja.username as no_meja')  // Hanya mengambil meja.username
                ->leftJoin(DB::raw("(select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail"), "form_detail.form_cut_id", "=", "form_cut_input.id")
                ->leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')
                ->leftJoin('cutting_plan', 'cutting_plan.form_cut_id', '=', 'form_cut_input.id')
                ->join('users as meja', 'meja.id', '=', 'form_cut_input.no_meja')
                ->whereRaw("
                    (marker_input.cancel IS NULL OR marker_input.cancel != 'Y') AND
                    (form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y') AND
                    ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
                ")
                ->groupBy('meja.username')  // Grup berdasarkan meja.username untuk menghindari duplikasi
                ->get();

            return response()->json($query);
        }

        public function cuttingQty(Request $request) {
            // $month = date("m");
            // $year = date("Y");

            // if ($request->month) {
            //     $month = $request->month;
            // }
            // if ($request->year) {
            //     $year = $request->year;
            // }

            $date = $request->date ? $request->date : date('Y-m-d');

            $dataQty = DB::select("
                SELECT
                    FLOOR( SUM( CASE WHEN `status` = 'SPREADING' AND cutting_plan_id IS NULL THEN 1 ELSE 0 END ) ) pending,
                    FLOOR( SUM( CASE WHEN `status` = 'SPREADING' AND cutting_plan_id IS NULL THEN total_lembar ELSE 0 END ) ) pending_total,
                    FLOOR( SUM( CASE WHEN `status` = 'SPREADING' AND cutting_plan_id IS NOT NULL THEN 1 ELSE 0 END ) ) plan,
                    FLOOR( SUM( CASE WHEN `status` = 'SPREADING' AND cutting_plan_id IS NOT NULL THEN total_lembar ELSE 0 END ) ) plan_total,
                    FLOOR( SUM( CASE WHEN `status` != 'SPREADING' AND `status` != 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END ) ) progress,
                    FLOOR( SUM( CASE WHEN `status` != 'SPREADING' AND `status` != 'SELESAI PENGERJAAN' THEN total_lembar ELSE 0 END ) ) progress_total,
                    FLOOR( SUM( CASE WHEN `status` = 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END ) ) finished,
                    FLOOR( SUM( CASE WHEN `status` = 'SELESAI PENGERJAAN' THEN total_lembar ELSE 0 END ) ) finished_total
                FROM
                    (
                        SELECT
                            form_cut_input.id form_id,
                            form_cut_input.`status`,
                            COALESCE(form_cut_input.total_lembar, form_cut_input.qty_ply, 0) total_lembar,
                            cutting_plan.id cutting_plan_id
                        FROM
                            form_cut_input
                            LEFT JOIN (select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail on form_detail.form_cut_id = form_cut_input.id
                            LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                            LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                        WHERE
                            ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' ) AND
                            ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' ) AND
                            ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
                        GROUP BY
                            form_cut_input.id
                    ) frm
            ");

            return $dataQty;
        }

        public function cuttingFormChart(Request $request) {
            $date = $request->date ? $request->date : date('Y-m-d');

            $cuttingForm = DB::table('form_cut_input')->
                selectRaw("
                    meja.username no_meja,
                    cutting_plan.tgl_plan,
                    COUNT(form_cut_input.id) total_form,
                    SUM(CASE WHEN form_cut_input.status != 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) incomplete_form,
                    SUM(CASE WHEN form_cut_input.status = 'SELESAI PENGERJAAN' THEN 1 ELSE 0 END) completed_form
                ")->
                leftJoin(DB::raw("(select form_cut_id, SUM(lembar_gelaran) total_gelaran, MAX(created_at) last_update FROM form_cut_input_detail GROUP BY form_cut_id) form_detail"), "form_detail.form_cut_id", "=", "form_cut_input.id")->
                leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
                leftJoin("cutting_plan", "cutting_plan.form_cut_id", "form_cut_input.id")->
                join("users as meja", "meja.id", "form_cut_input.no_meja")->
                whereRaw("
                    ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' ) AND
                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' ) AND
                    ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND (COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."' OR DATE(form_detail.last_update) = '".$date."')) )
                ")->
                groupByRaw("(CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN ( CASE WHEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) != '".$date."' THEN DATE(form_detail.last_update) ELSE COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) END ) ELSE cutting_plan.tgl_plan END), meja.id")->
                get();

            return json_encode($cuttingForm);
        }

        public function cuttingOutputList(Request $request) {
            $date = $request->date ? $request->date : date('Y-m-d');
            $panel = $request->panel ? $request->panel : "";

            $data = DB::select("
                SELECT
                    form_marker.act_costing_ws,
                    form_marker.style,
                    form_marker.color,
                    form_marker.panel,
                    COALESCE ( form_marker.tanggal, 0 ) tanggal,
                    COALESCE ( form_marker.total_plan, 0 ) total_plan,
                    COALESCE ( form_cut_all.total_balance, 0 ) balance_plan,
                    COALESCE ( form_marker.total_complete, 0 ) total_complete,
                    (COALESCE ( form_marker.total_plan, 0 ) + COALESCE ( form_cut_all.total_balance, 0 )) - COALESCE ( form_marker.total_complete, 0 ) balance
                FROM
                (
                    SELECT
                        marker_input.act_costing_ws,
                        marker_input.style,
                        marker_input.color,
                        marker_input.panel,
                        form_cut_plan.tanggal,
                        SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                        SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete
                    FROM
                        marker_input
                        INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                        INNER JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                (CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal, SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                        LEFT JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                ( CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal,
                                    SUM(
                                    COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                                AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                        WHERE
                            ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                        GROUP BY
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel
                    ) form_marker
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            form_cut_plan.tanggal,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                            SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) - SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_balance
                        FROM
                            marker_input
                            INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                            INNER JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM(COALESCE ( form_cut_input.qty_ply, 0)) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND (
                                        cutting_plan.tgl_plan <= '".$date."'
                                    )
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                            LEFT JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply, 0 )) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND ( cutting_plan.tgl_plan <= '".$date."' )
                                    AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                            WHERE
                                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                            GROUP BY
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel
                    ) form_cut_all ON form_cut_all.act_costing_ws = form_marker.act_costing_ws AND form_cut_all.style = form_marker.style AND form_cut_all.color = form_marker.color AND form_cut_all.panel = form_marker.panel
                WHERE
                    form_marker.panel = '".$panel."'
            ");

            return DataTables::of($data)->toJson();
        }

        public function cuttingOutputListAll(Request $request) {
            $date = $request->date ? $request->date : date('Y-m-d');

            $data = DB::select("
                SELECT
                    form_marker.act_costing_ws,
                    form_marker.style,
                    form_marker.color,
                    form_marker.panel,
                    COALESCE ( form_marker.tanggal, 0 ) tanggal,
                    COALESCE ( form_marker.total_plan, 0 ) total_plan,
                    COALESCE ( form_cut_all.total_balance, 0 ) balance_plan,
                    COALESCE ( form_marker.total_complete, 0 ) total_complete,
                    (COALESCE ( form_marker.total_plan, 0 ) + COALESCE ( form_cut_all.total_balance, 0 )) - COALESCE ( form_marker.total_complete, 0 ) balance,
                    form_marker_set.total_set as qty_set
                FROM
                (
                    SELECT
                        marker_input.act_costing_ws,
                        marker_input.style,
                        marker_input.color,
                        marker_input.panel,
                        form_cut_plan.tanggal,
                        SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                        SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete
                    FROM
                        marker_input
                        INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                        INNER JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                (CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal, SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                        LEFT JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                ( CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal,
                                    SUM(
                                    COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                                AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                        WHERE
                            ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                        GROUP BY
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel
                    ) form_marker
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            form_cut_plan.tanggal,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                            SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) - SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_balance
                        FROM
                            marker_input
                            INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                            INNER JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM(COALESCE ( form_cut_input.qty_ply, 0)) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND (
                                        cutting_plan.tgl_plan <= '".$date."'
                                    )
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                            LEFT JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply, 0 )) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND ( cutting_plan.tgl_plan <= '".$date."' )
                                    AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                            WHERE
                                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                            GROUP BY
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel
                    ) form_cut_all ON form_cut_all.act_costing_ws = form_marker.act_costing_ws AND form_cut_all.style = form_marker.style AND form_cut_all.color = form_marker.color AND form_cut_all.panel = form_marker.panel
                    LEFT JOIN (
                        SELECT
                            form_marker_set.act_costing_ws,
                            form_marker_set.style,
                            form_marker_set.color,
                            form_marker_set.panel,
                            MIN(COALESCE(form_marker_set.total_complete, 0)) total_set
                        FROM (
                            SELECT
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel,
                                SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete
                            FROM
                                marker_input
                                INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                                INNER JOIN (
                                    SELECT
                                        form_cut_input.id_marker,
                                        (CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal, SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                                    FROM
                                        form_cut_input
                                        LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                    WHERE
                                        ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                        AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                        AND (
                                            cutting_plan.tgl_plan = '".$date."'
                                            OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                        )
                                    GROUP BY
                                        form_cut_input.id_marker
                                ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                                LEFT JOIN (
                                    SELECT
                                        form_cut_input.id_marker,
                                        ( CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal,
                                            SUM(
                                            COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                                    FROM
                                        form_cut_input
                                        LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                    WHERE
                                        ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                        AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                        AND (
                                            cutting_plan.tgl_plan = '".$date."'
                                            OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                        )
                                        AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                    GROUP BY
                                        form_cut_input.id_marker
                                ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                            WHERE
                                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                            GROUP BY
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel
                        ) form_marker_set
                        GROUP BY
                            form_marker_set.act_costing_ws,
                            form_marker_set.style,
                            form_marker_set.color
                    ) form_marker_set ON form_marker_set.act_costing_ws = form_marker.act_costing_ws AND form_marker_set.style = form_marker.style AND form_marker_set.color = form_marker.color
                ORDER BY
                    form_marker.act_costing_ws,
                    form_marker.style,
                    form_marker.color,
                    form_marker.panel
            ");

            return DataTables::of($data)->toJson();
        }

        public function cuttingOutputListPanels(Request $request) {
            $date = $request->date ? $request->date : date('Y-m-d');

            $data = DB::select("
                SELECT
                    form_marker.act_costing_ws,
                    form_marker.style,
                    form_marker.color,
                    form_marker.panel,
                    COALESCE ( form_marker.tanggal, 0 ) tanggal,
                    COALESCE ( form_marker.total_plan, 0 ) total_plan,
                    COALESCE ( form_cut_all.total_balance, 0 ) balance_plan,
                    COALESCE ( form_marker.total_complete, 0 ) total_complete,
                    (COALESCE ( form_marker.total_plan, 0 ) + COALESCE ( form_cut_all.total_balance, 0 )) - COALESCE ( form_marker.total_complete, 0 ) balance
                FROM
                (
                    SELECT
                        marker_input.act_costing_ws,
                        marker_input.style,
                        marker_input.color,
                        marker_input.panel,
                        form_cut_plan.tanggal,
                        SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                        SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete
                    FROM
                        marker_input
                        INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                        INNER JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                (CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal, SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                        LEFT JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                ( CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal,
                                    SUM(
                                    COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                                AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                        WHERE
                            ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                        GROUP BY
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel
                    ) form_marker
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            form_cut_plan.tanggal,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                            SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) - SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_balance
                        FROM
                            marker_input
                            INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                            INNER JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM(COALESCE ( form_cut_input.qty_ply, 0)) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND (
                                        cutting_plan.tgl_plan <= '".$date."'
                                    )
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                            LEFT JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply, 0 )) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND ( cutting_plan.tgl_plan <= '".$date."' )
                                    AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                            WHERE
                                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                            GROUP BY
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel
                    ) form_cut_all ON form_cut_all.act_costing_ws = form_marker.act_costing_ws AND form_cut_all.style = form_marker.style AND form_cut_all.color = form_marker.color AND form_cut_all.panel = form_marker.panel
                GROUP BY
                    form_marker.panel
            ");

            return $data;
        }

        public function cuttingOutputListData(Request $request) {
            $date = $request->date ? $request->date : date('Y-m-d');

            $data = DB::select("
                SELECT
                    form_marker.act_costing_ws,
                    form_marker.style,
                    form_marker.color,
                    form_marker.panel,
                    COALESCE ( form_marker.tanggal, 0 ) tanggal,
                    COALESCE ( form_marker.total_plan, 0 ) total_plan,
                    COALESCE ( form_cut_all.total_balance, 0 ) balance_plan,
                    COALESCE ( form_marker.total_complete, 0 ) total_complete,
                    (COALESCE ( form_marker.total_plan, 0 ) + COALESCE ( form_cut_all.total_balance, 0 )) - COALESCE ( form_marker.total_complete, 0 ) balance
                FROM
                (
                    SELECT
                        marker_input.act_costing_ws,
                        marker_input.style,
                        marker_input.color,
                        marker_input.panel,
                        form_cut_plan.tanggal,
                        SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                        SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete
                    FROM
                        marker_input
                        INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                        INNER JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                (CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal, SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                        LEFT JOIN (
                            SELECT
                                form_cut_input.id_marker,
                                ( CASE WHEN cutting_plan.tgl_plan = '".$date."' THEN cutting_plan.tgl_plan ELSE COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) END ) tanggal,
                                    SUM(
                                    COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply )) total_lembar
                            FROM
                                form_cut_input
                                LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                            WHERE
                                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 6 MONTH )
                                AND (
                                    cutting_plan.tgl_plan = '".$date."'
                                    OR ( cutting_plan.tgl_plan != '".$date."' AND COALESCE ( DATE ( form_cut_input.waktu_selesai ), DATE ( form_cut_input.waktu_mulai )) = '".$date."' )
                                )
                                AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                            GROUP BY
                                form_cut_input.id_marker
                        ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                        WHERE
                            ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                        GROUP BY
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel
                    ) form_marker
                    LEFT JOIN (
                        SELECT
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            form_cut_plan.tanggal,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) total_plan,
                            SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_complete,
                            SUM( marker_detail.total_ratio * form_cut_plan.total_lembar ) - SUM( marker_detail.total_ratio * form_cut_complete.total_lembar ) total_balance
                        FROM
                            marker_input
                            INNER JOIN ( SELECT marker_input_detail.marker_id, SUM( marker_input_detail.ratio ) total_ratio FROM marker_input_detail GROUP BY marker_input_detail.marker_id ) marker_detail ON marker_detail.marker_id = marker_input.id
                            INNER JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM(COALESCE ( form_cut_input.qty_ply, 0)) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND (
                                        cutting_plan.tgl_plan <= '".$date."'
                                    )
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_plan ON form_cut_plan.id_marker = marker_input.kode
                            LEFT JOIN (
                                SELECT
                                    form_cut_input.id_marker,
                                    cutting_plan.tgl_plan tanggal,
                                    SUM( COALESCE ( form_cut_input.total_lembar, form_cut_input.qty_ply, 0 )) total_lembar
                                FROM
                                    form_cut_input
                                    LEFT JOIN cutting_plan ON cutting_plan.form_cut_id = form_cut_input.id
                                WHERE
                                    ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' )
                                    AND ( cutting_plan.tgl_plan <= '".$date."' )
                                    AND form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                GROUP BY
                                    form_cut_input.id_marker
                            ) form_cut_complete ON form_cut_complete.id_marker = marker_input.kode
                            WHERE
                                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' )
                            GROUP BY
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel
                    ) form_cut_all ON form_cut_all.act_costing_ws = form_marker.act_costing_ws AND form_cut_all.style = form_marker.style AND form_cut_all.color = form_marker.color AND form_cut_all.panel = form_marker.panel
            ");

            return $data;
        }

        // Legacy
            // public function cuttingStockListData(Request $request) {
            //     $date = $request->date ? $request->date : date('Y-m-d');

            //     $additionalQuery = "('".$itemCutting->implode("id_item", "', '")."')";

            //     $pemakaianRoll = DB::connection("mysql_sb")->select("
            //         select a.*,b.no_bppb no_out, COALESCE(total_roll,0) roll_out, ROUND(COALESCE(qty_out,0), 2) qty_out, c.no_dok no_retur, COALESCE(total_roll_ri,0) roll_retur, ROUND(COALESCE(qty_out_ri,0), 2) qty_retur, coalesce(b.no_ws_aktual, a.no_ws) no_ws_aktual from (select bppbno,bppbdate,s.supplier tujuan,ac.kpno no_ws, ac.styleno,ms.supplier buyer,a.id_item,
            //         REPLACE(mi.itemdesc, '\"', '\\\\\"') itemdesc, mi.color, a.qty qty_req,a.unit
            //         from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier
            //         inner join jo_det jod on a.id_jo=jod.id_jo
            //         inner join so on jod.id_so=so.id
            //         inner join act_costing ac on so.id_cost=ac.id
            //         inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
            //         inner join masteritem mi on a.id_item=mi.id_item
            //         where bppbno like '%RQ-F%' and a.id_supplier = '432' and (bppbdate between '".$date."' and '".date("Y-m-d", strtotime($date." -7 days"))."') and (CASE WHEN )
            //         group by a.id_item,a.bppbno
            //         order by bppbdate,bppbno desc) a left join
            //         (select a.no_ws_aktual,a.no_bppb,no_req,id_item,COUNT(id_roll) total_roll, sum(qty_out) qty_out,satuan from whs_bppb_h a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and (bppbdate = '".$date."' OR id_item in ".$additionalQuery.") GROUP BY bppbno) b on b.bppbno = a.no_req inner join whs_bppb_det c on c.no_bppb = a.no_bppb where a.status != 'Cancel' and c.status = 'Y' GROUP BY a.no_bppb,no_req,id_item) b on b.no_req = a.bppbno and b.id_item = a.id_item left join
            //         (select a.no_dok, no_invoice no_req,id_item,COUNT(no_barcode) total_roll_ri, sum(qty_sj) qty_out_ri,satuan from (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting' ) a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and (bppbdate = '".$date."' OR id_item in ".$additionalQuery.") GROUP BY bppbno) b on b.bppbno = a.no_invoice INNER JOIN whs_lokasi_inmaterial c on c.no_dok = a.no_dok GROUP BY a.no_dok,no_invoice,id_item) c on c.no_req = a.bppbno and c.id_item  =a.id_item
            //         where COALESCE(total_roll,0) > 0
            //         order by a.no_ws, a.color
            //     ");

            //     // $cutting = collect(
            //     //     DB::select("
            //     //         SELECT
            //     //             a.no_bppb,
            //     //             a.no_req,
            //     //             cutting.id_item,
            //     //             sum( qty_out ) qty_out,
            //     //             COUNT( cutting.id_roll ) total_roll,
            //     //             ROUND ( (CASE WHEN satuan = 'YRD' OR satuan = 'YARD' THEN sum( cutting.total_qty ) * 1.09361 ELSE sum( cutting.total_qty ) END ) , 2) total_qty_roll,
            //     //             ROUND ( (CASE WHEN satuan = 'YRD' OR satuan = 'YARD' THEN sum( cutting.total_pemakaian_roll ) * 1.09361 ELSE sum( cutting.total_pemakaian_roll ) END ) , 2) total_pakai_roll,
            //     //             cutting.satuan
            //     //         FROM
            //     //             whs_bppb_h a
            //     //             INNER JOIN ( SELECT bppbno, bppbdate FROM bppb_req WHERE bppbno LIKE '%RQ-F%' AND id_supplier = '432' AND bppbdate between '".$dateFrom."' and '".$dateTo."'  GROUP BY bppbno ) b ON b.bppbno = a.no_req
            //     //             INNER JOIN ( select whs_bppb_det.id_roll, whs_bppb_det.id_item, whs_bppb_det.no_bppb, whs_bppb_det.satuan, whs_bppb_det.qty_out, COUNT(form_cut_input_detail.id) total_roll, MAX(CAST(form_cut_input_detail.qty as decimal(11,3))) total_qty, SUM(form_cut_input_detail.total_pemakaian_roll) total_pemakaian_roll from whs_bppb_det inner join form_cut_input_detail on form_cut_input_detail.id_roll = whs_bppb_det.id_roll group by whs_bppb_det.id_roll ) as cutting on cutting.no_bppb = a.no_bppb
            //     //         WHERE
            //     //             a.STATUS != 'Cancel'
            //     //         GROUP BY
            //     //             a.no_bppb,
            //     //             no_req,
            //     //             id_item
            //     //     ")
            //     // );

            //     return DataTables::of($pemakaianRoll)->
            //         addColumn('saldo_awal', function ($row) use ($date) {
            //             $pemakaianRoll = collect(DB::connection("mysql_sb")->select("
            //                 SELECT
            //                     a.*,
            //                     b.no_bppb no_out,
            //                     COALESCE ( total_roll, 0 ) roll_out,
            //                     ROUND( COALESCE ( qty_out, 0 ), 2 ) qty_out,
            //                     c.no_dok no_retur,
            //                     COALESCE ( total_roll_ri, 0 ) roll_retur,
            //                     ROUND( COALESCE ( qty_out_ri, 0 ), 2 ) qty_retur,
            //                     COALESCE ( b.no_ws_aktual, a.no_ws ) no_ws_aktual
            //                 FROM
            //                     (
            //                     SELECT
            //                         bppbno,
            //                         bppbdate,
            //                         s.supplier tujuan,
            //                         ac.kpno no_ws,
            //                         ac.styleno,
            //                         ms.supplier buyer,
            //                         a.id_item,
            //                         REPLACE ( mi.itemdesc, '\"', '\\\\\"' ) itemdesc,
            //                         mi.color,
            //                         a.qty qty_req,
            //                         a.unit
            //                     FROM
            //                         bppb_req a
            //                         INNER JOIN mastersupplier s ON a.id_supplier = s.id_supplier
            //                         INNER JOIN jo_det jod ON a.id_jo = jod.id_jo
            //                         INNER JOIN so ON jod.id_so = so.id
            //                         INNER JOIN act_costing ac ON so.id_cost = ac.id
            //                         INNER JOIN mastersupplier ms ON ac.id_buyer = ms.id_supplier
            //                         INNER JOIN masteritem mi ON a.id_item = mi.id_item
            //                     WHERE
            //                         bppbno LIKE '%RQ-F%'
            //                         AND a.id_supplier = '432'
            //                         AND bppbdate < '".$date."'
            //                         AND a.id_item = '".$row->id_item."'
            //                     GROUP BY
            //                         a.id_item,
            //                         a.bppbno
            //                     ORDER BY
            //                         bppbdate,
            //                         bppbno DESC
            //                     ) a
            //                     LEFT JOIN (
            //                         SELECT
            //                             a.no_ws_aktual,
            //                             a.no_bppb,
            //                             no_req,
            //                             id_item,
            //                             COUNT( id_roll ) total_roll,
            //                             sum( qty_out ) qty_out,
            //                             satuan
            //                         FROM
            //                             whs_bppb_h a
            //                             INNER JOIN ( SELECT bppbno, bppbdate FROM bppb_req WHERE bppbno LIKE '%RQ-F%' AND id_supplier = '432' AND bppbdate < '".$date."' GROUP BY bppbno ) b ON b.bppbno = a.no_req
            //                             INNER JOIN whs_bppb_det c ON c.no_bppb = a.no_bppb
            //                         WHERE
            //                             a.STATUS != 'Cancel'
            //                             AND c.STATUS = 'Y'
            //                             AND id_item = '".$row->id_item."'
            //                         GROUP BY
            //                             a.no_bppb,
            //                             no_req,
            //                             id_item
            //                         HAVING
            //                             COUNT( id_roll ) > 0
            //                     ) b ON b.no_req = a.bppbno AND b.id_item = a.id_item
            //                     LEFT JOIN (
            //                         SELECT
            //                             a.no_dok,
            //                             no_invoice no_req,
            //                             id_item,
            //                             COUNT( no_barcode ) total_roll_ri,
            //                             sum( qty_sj ) qty_out_ri,
            //                             satuan
            //                         FROM
            //                             (
            //                             SELECT * FROM
            //                                 whs_inmaterial_fabric
            //                             WHERE no_dok LIKE '%RI%' AND supplier = 'Production - Cutting' ) a
            //                                 INNER JOIN ( SELECT bppbno, bppbdate FROM bppb_req WHERE bppbno LIKE '%RQ-F%' AND id_supplier = '432' AND bppbdate < '".$date."' and id_item = '".$row->id_item."' GROUP BY bppbno ) b ON b.bppbno = a.no_invoice
            //                                 INNER JOIN whs_lokasi_inmaterial c ON c.no_dok = a.no_dok
            //                             GROUP BY
            //                                 a.no_dok,
            //                                 no_invoice,
            //                                 id_item
            //                         ) c ON c.no_req = a.bppbno
            //                     AND c.id_item = a.id_item
            //                 ORDER BY
            //                     a.no_ws,
            //                     a.color
            //             "));

            //             $saldoAwal = $pemakaianRoll->sum("roll_out");
            //             $saldoBalance = $saldoAwal;
            //             $totalRollCutting = 0;

            //             foreach ($pemakaianRoll as $roll) {
            //                 $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '".$roll->bppbno."' and b.id_item = '".$roll->id_item."' and b.status = 'Y' GROUP BY id_roll"));

            //                 $rollIds = $rollIdsArr->pluck("id_roll");

            //                 $rolls = FormCutInputDetail::selectRaw("
            //                     id_roll,
            //                     id_item,
            //                     detail_item,
            //                     lot,
            //                     COALESCE(roll_buyer, roll) roll,
            //                     MAX(qty) qty,
            //                     unit,
            //                     ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
            //                     ROUND(SUM(CASE WHEN short_roll < 0 THEN short_roll ELSE 0 END), 2) total_short_roll
            //                 ")->
            //                 whereNotNull("id_roll")->
            //                 whereIn("id_roll", $rollIds)->
            //                 groupBy("id_item", "id_roll")->
            //                 get();

            //                 $totalRollCutting += $rolls->count();
            //                 $saldoBalance -= $rolls->count();
            //             }

            //             return $saldoAwal;
            //         })->
            //         addColumn('total_roll_cutting', function ($row) {
            //             $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '".$row->bppbno."' and b.id_item = '".$row->id_item."' and b.status = 'Y' GROUP BY id_roll"));

            //             $rollIds = $rollIdsArr->pluck("id_roll");

            //             $rolls = FormCutInputDetail::selectRaw("
            //                     id_roll,
            //                     id_item,
            //                     detail_item,
            //                     lot,
            //                     COALESCE(roll_buyer, roll) roll,
            //                     MAX(qty) qty,
            //                     unit,
            //                     ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
            //                     ROUND(SUM(CASE WHEN short_roll < 0 THEN short_roll ELSE 0 END), 2) total_short_roll
            //                 ")->
            //                 whereNotNull("id_roll")->
            //                 whereIn("id_roll", $rollIds)->
            //                 groupBy("id_item", "id_roll")->
            //                 get();

            //             return $rolls->count();
            //         })->
            //         addColumn('total_roll_balance', function ($row) {
            //             $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '".$row->bppbno."' and b.id_item = '".$row->id_item."' and b.status = 'Y' GROUP BY id_roll"));

            //             $rollIds = $rollIdsArr->pluck("id_roll");

            //             $rolls = FormCutInputDetail::selectRaw("
            //                     id_roll,
            //                     id_item,
            //                     detail_item,
            //                     lot,
            //                     COALESCE(roll_buyer, roll) roll,
            //                     MAX(qty) qty,
            //                     unit,
            //                     ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
            //                     ROUND(SUM(CASE WHEN short_roll < 0 THEN short_roll ELSE 0 END), 2) total_short_roll
            //                 ")->
            //                 whereNotNull("id_roll")->
            //                 whereIn("id_roll", $rollIds)->
            //                 groupBy("id_item", "id_roll")->
            //                 get();

            //             $balance = $rolls ? $row->roll_out - $rolls->count() : $row->roll_out;

            //             return $balance > 0 ? $balance : ($balance < 0 ? str_replace("-", "+", round($balance, 2)) : round($balance, 2));
            //         })->
            //         addColumn('total_pakai_balance', function ($row) {
            //             $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '".$row->bppbno."' and b.id_item = '".$row->id_item."' and b.status = 'Y' GROUP BY id_roll"));

            //             $rollIds = $rollIdsArr->pluck("id_roll");

            //             $rolls = FormCutInputDetail::selectRaw("
            //                     id_roll,
            //                     id_item,
            //                     detail_item,
            //                     lot,
            //                     COALESCE(roll_buyer, roll) roll,
            //                     MAX(qty) qty,
            //                     unit,
            //                     ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
            //                     ROUND(SUM(CASE WHEN short_roll < 0 THEN short_roll ELSE 0 END), 2) total_short_roll
            //                 ")->
            //                 whereNotNull("id_roll")->
            //                 whereIn("id_roll", $rollIds)->
            //                 groupBy("id_item", "id_roll")->
            //                 get();

            //             $balance = $rolls ? $row->qty_out - (($row->unit == 'YARD' || $row->unit == 'YRD') ? $rolls->sum("total_pemakaian_roll") * 1.0361 : $rolls->sum("total_pemakaian_roll") ) : $row->qty_out;

            //             return $balance > 0 ? round($balance, 2) : ($balance < 0 ? ( str_replace("-", "+", round($balance, 2)) ) : round($balance, 2));
            //         })->
            //         toJson();
            // }

        public function cuttingStockListData(Request $request) {
            $date = $request->date ? $request->date : date('Y-m-d');

            $pemakaianRoll = DB::connection("mysql_sb")->select("
                SELECT
                    *
                FROM
                    (
                        select
                            a.*,b.no_bppb no_out, COALESCE(total_roll_all,0) roll_out, COALESCE(total_roll, 0) roll_out_today, ROUND(COALESCE(qty_out,0), 2) qty_out, c.no_dok no_retur, COALESCE(total_roll_ri,0) roll_retur, ROUND(COALESCE(qty_out_ri,0), 2) qty_retur, coalesce(b.no_ws_aktual, a.no_ws) no_ws_aktual from (select bppbno,bppbdate,s.supplier tujuan,ac.kpno no_ws, ac.styleno,ms.supplier buyer,a.id_item, REPLACE(mi.itemdesc, '\"', '\\\\\"') itemdesc, mi.color, a.qty qty_req,a.unit
                        from
                            bppb_req a
                            inner join mastersupplier s on a.id_supplier=s.id_supplier
                            inner join jo_det jod on a.id_jo=jod.id_jo
                            inner join so on jod.id_so=so.id
                            inner join act_costing ac on so.id_cost=ac.id
                            inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
                            inner join masteritem mi on a.id_item=mi.id_item
                        where
                            bppbno like '%RQ-F%' and a.id_supplier = '432'
                        group by
                            a.id_item,a.bppbno
                        order by
                            bppbdate,bppbno desc
                    ) a
                    left join (
                        select a.no_ws_aktual,a.no_bppb,no_req,id_item,COUNT(id_roll) total_roll_all, SUM(CASE WHEN GREATEST(a.tgl_bppb, a.created_at) = '".$date."' THEN 1 ELSE 0 END) total_roll, sum(qty_out) qty_out,satuan from whs_bppb_h a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' GROUP BY bppbno) b on b.bppbno = a.no_req inner join whs_bppb_det c on c.no_bppb = a.no_bppb where a.status != 'Cancel' and c.status = 'Y' GROUP BY id_item
                    ) b on b.no_req = a.bppbno and b.id_item = a.id_item
                    left join (
                        select a.no_dok, no_invoice no_req,id_item,COUNT(no_barcode) total_roll_all_ri, SUM(CASE WHEN GREATEST(a.tgl_daftar, a.created_at) = '".$date."' THEN 1 ELSE 0 END) total_roll_ri, sum(qty_sj) qty_out_ri,satuan from (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting' ) a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' GROUP BY bppbno) b on b.bppbno = a.no_invoice INNER JOIN whs_lokasi_inmaterial c on c.no_dok = a.no_dok GROUP BY id_item
                    ) c on c.no_req = a.bppbno and c.id_item  =a.id_item
                WHERE
                    COALESCE(total_roll_all,0) > 0
                order by
                    a.no_ws, a.color
                ) req_roll
                LEFT JOIN (
                    SELECT
                        id_item,
                        COUNT(id_roll) total_roll,
                        SUM(CASE WHEN sisa_kain <= 0 THEN 1 ELSE 0 END) total_roll_habis,
                        SUM(CASE WHEN tanggal_pemakaian = CURRENT_DATE THEN 1 ELSE 0 END)  total_roll_today
                    FROM (
                        SELECT
                            id_roll,
                            id_item,
                            MAX(qty) AS qty,
                            SUM(total_pemakaian_roll) AS total_pemakaian_roll,
                            SUM(short_roll) AS short_roll,
                            MIN(CASE
                                WHEN form_cut_input_detail.STATUS IN ('extension', 'extension complete')
                                THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll
                                ELSE form_cut_input_detail.sisa_kain
                            END) AS sisa_kain,
                            DATE(form_cut_input_detail.updated_at) tanggal_pemakaian
                        FROM
                            laravel_nds.form_cut_input_detail
                        LEFT JOIN laravel_nds.form_cut_input
                            ON form_cut_input.id = form_cut_input_detail.form_cut_id
                        WHERE
                            (form_cut_input.status != 'SELESAI PENGERJAAN' OR
                            (form_cut_input.status = 'SELESAI PENGERJAAN' AND form_cut_input.status NOT IN ('not complete', 'extension')))
                            AND id_roll IS NOT NULL
                            AND id_roll != ''
                            AND form_cut_input_detail.updated_at BETWEEN '".date("Y-m-d", strtotime($date." -360 days"))." 00:00:00' AND '".$date." 23:59:59'
                        GROUP BY
                            id_roll,
                            id_item
                    ) roll
                    GROUP BY
                        id_item
                ) cutting_roll ON cutting_roll.id_item = req_roll.id_item
                where
                    (roll_out_today > 0 OR total_roll_today > 0)
                order by
                    roll_out_today desc,
                    total_roll_today desc,
                    buyer asc,
                    no_ws_aktual asc,
                    styleno asc,
                    color asc
            ");

            // (((COALESCE(roll_out, 0) - COALESCE(roll_out_today, 0)) - (COALESCE(total_roll, 0) - COALESCE(total_roll_today, 0)) > 0) OR (COALESCE(roll_out_today, 0) > 0) OR (COALESCE(total_roll_today, 0) > 0) OR ((COALESCE(roll_out, 0) - COALESCE(total_roll, 0)) > 0))

            return DataTables::of($pemakaianRoll)->
                addColumn('saldo_awal', function ($row) {
                    return ($row->roll_out - $row->roll_out_today) - ($row->total_roll - $row->total_roll_today);
                })->
                addColumn('total_roll_cutting', function ($row) {
                    return $row->total_roll_today;
                })->
                addColumn('total_roll_balance', function ($row) {
                    return $row->roll_out - $row->total_roll;
                })->
                toJson();
        }
    // End of Cutting

    // Stocker
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        public function stocker(Request $request)
        {
            if ($request->ajax()) {
                $month = $request->month ? $request->month : date('m');
                $year = $request->year ? $request->year : date('Y');

                $worksheetStock = DB::select("
                    SELECT
                        stock.id_act_cost,
                        stock.tgl_kirim,
                        stock.act_costing_ws,
                        stock.styleno,
                        stock.color,
                        SUM(stock.qty_ply) qty
                    FROM (
                        SELECT
                            master_sb_ws.id_act_cost,
                            DATE(master_sb_ws.tgl_kirim) tgl_kirim,
                            stocker_input.id,
                            stocker_input.form_cut_id,
                            stocker_input.act_costing_ws,
                            master_sb_ws.styleno,
                            stocker_input.color,
                            stocker_input.size,
                            COALESCE (
                                (
                                    MAX( dc_in_input.qty_awal ) - (
                                        MAX(
                                            COALESCE ( dc_in_input.qty_reject, 0 )) + MAX(
                                        COALESCE ( dc_in_input.qty_replace, 0 ))) - (
                                        MAX(
                                            COALESCE ( secondary_in_input.qty_reject, 0 )) + MAX(
                                        COALESCE ( secondary_in_input.qty_replace, 0 ))) - (
                                        MAX(
                                            COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + MAX(
                                        COALESCE ( secondary_inhouse_input.qty_replace, 0 )))
                                ),
                                COALESCE ( stocker_input.qty_ply_mod, stocker_input.qty_ply )
                            ) qty_ply
                        FROM
                            stocker_input
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                        WHERE
                            MONTH(master_sb_ws.tgl_kirim) = '".$month."' AND YEAR(master_sb_ws.tgl_kirim) = '".$year."'
                        GROUP BY
                            stocker_input.form_cut_id,
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.ratio
                    ) stock
                    GROUP BY
                        stock.act_costing_ws,
                        stock.styleno,
                        stock.color
                ");

                return DataTables::of($worksheetStock)->toJson();
            }

            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            return view("track.stocker.stocker", ["page" => "dashboard-stocker", "head" => "Track", "months" => $months, "years" => $years]);
        }

        public function showStocker($actCostingId = null)
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

                return view("track.stocker.stocker-detail", ["page" => "dashboard-stocker", "head" => "Track ".$ws->first()->ws, "ws" => $ws, "panels" => $panels, "months" => $months, "years" => $years]);
            }
        }

        public function stockerExport(Request $request) {
            ini_set('max_execution_time', 36000);

            $month = $request->month ? $request->month : date('m');
            $year = $request->year ? $request->year : date('Y');

            return Excel::download(new ExportTrackStocker($month, $year), 'Laporan_track_stocker.xlsx');
        }
    // End of Stocker

    // DC
        public function dc(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            if ($request->ajax()) {
                $month = date("m");
                $year = date("Y");

                if ($request->month) {
                    $month = $request->month;
                }
                if ($request->year) {
                    $year = $request->year;
                }

                $dc = Stocker::selectRaw("
                        stocker_input.id stocker_id,
                        stocker_input.id_qr_stocker,
                        (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        stocker_input.so_det_id,
                        stocker_input.shade,
                        stocker_input.ratio,
                        master_part.nama_part,
                        CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir, (CASE WHEN dc_in_input.qty_reject IS NOT NULL AND dc_in_input.qty_replace IS NOT NULL THEN CONCAT(' (', (COALESCE(dc_in_input.qty_replace, 0) + COALESCE(secondary_in_input.qty_replace, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) - COALESCE(secondary_in_input.qty_reject, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0)), ') ') ELSE ' (0)' END)) stocker_range,
                        stocker_input.status,
                        dc_in_input.id dc_in_id,
                        dc_in_input.tujuan,
                        dc_in_input.tempat,
                        dc_in_input.lokasi,
                        (CASE WHEN dc_in_input.tujuan = 'SECONDARY DALAM' OR dc_in_input.tujuan = 'SECONDARY LUAR' THEN dc_in_input.lokasi ELSE '-' END) secondary,
                        COALESCE(rack_detail_stocker.nm_rak, (CASE WHEN dc_in_input.tempat = 'RAK' THEN dc_in_input.lokasi ELSE null END), (CASE WHEN dc_in_input.lokasi = 'RAK' THEN dc_in_input.det_alokasi ELSE null END), '-') rak,
                        COALESCE(trolley.nama_trolley, (CASE WHEN dc_in_input.tempat = 'TROLLEY' THEN dc_in_input.lokasi ELSE null END), '-') troli,
                        COALESCE((COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) - COALESCE(secondary_in_input.qty_reject, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) + COALESCE(secondary_in_input.qty_replace, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0)), stocker_input.qty_ply) dc_in_qty,
                        CONCAT(form_cut_input.no_form, ' / ', form_cut_input.no_cut) no_cut,
                        COALESCE(UPPER(loading_line.nama_line), '-') line,
                        stocker_input.updated_at latest_update
                    ")->
                    leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                    leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
                    leftJoin("part_detail", "stocker_input.part_detail_id", "=", "part_detail.id")->
                    leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                    leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "stocker_input.id")->
                    leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
                    leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
                    whereRaw("(MONTH(form_cut_input.waktu_selesai) = '".$month."' OR MONTH(form_cut_reject.updated_at) = '".$month."')")->
                    whereRaw("(YEAR(form_cut_input.waktu_selesai) = '".$year."' OR YEAR(form_cut_reject.updated_at) = '".$year."')")->
                    whereRaw("(form_cut_input.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH) OR form_cut_reject.tanggal >= DATE(NOW()-INTERVAL 6 MONTH))")->
                    orderBy("stocker_input.act_costing_ws", "asc")->
                    orderBy("stocker_input.color", "asc")->
                    orderBy("form_cut_input.no_cut", "asc")->
                    orderBy("master_part.nama_part", "asc")->
                    orderBy("stocker_input.so_det_id", "asc")->
                    orderBy("stocker_input.shade", "desc")->
                    orderBy("stocker_input.id_qr_stocker", "asc");

                return DataTables::eloquent($dc)->toJson();
            }

            return view('dashboard', ['page' => 'dashboard-dc', 'months' => $months, 'years' => $years]);
        }

        public function dcQty(Request $request) {
            $month = date("m");
            $year = date("Y");

            if ($request->month) {
                $month = $request->month;
            }
            if ($request->year) {
                $year = $request->year;
            }

            $dataQty = DB::select("
                SELECT
                    MIN(secondary) secondary,
                    MIN(rak) rak,
                    MIN(troli) troli,
                    MIN(line) line,
                    MIN(qty_ply) qty_ply,
                    MIN(dc_in_qty) dc_in_qty
                FROM
                (
                    SELECT
                        COALESCE(stocker_input.form_cut_id, stocker_input.form_reject_id) form_cut_id,
                        (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
                        stocker_input.so_det_id,
                        stocker_input.group_stocker,
                        stocker_input.ratio,
                        stocker_input.STATUS,
                        (
                            CASE WHEN dc_in_input.tujuan = 'SECONDARY DALAM'
                            OR dc_in_input.tujuan = 'SECONDARY LUAR'
                            THEN dc_in_input.lokasi ELSE '-' END
                        ) secondary,
                        COALESCE (
                            rack_detail_stocker.nm_rak,
                            ( CASE WHEN dc_in_input.tempat = 'RAK' THEN dc_in_input.lokasi ELSE NULL END ),
                            ( CASE WHEN dc_in_input.lokasi = 'RAK' THEN dc_in_input.det_alokasi ELSE NULL END ),
                            '-'
                        ) rak,
                        COALESCE (
                            trolley.nama_trolley,
                            ( CASE WHEN dc_in_input.tempat = 'TROLLEY' THEN dc_in_input.lokasi ELSE NULL END ),
                            '-'
                        ) troli,
                        COALESCE (
                            UPPER( loading_line.nama_line ),
                            '-'
                        ) line,
                        COALESCE((COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) - COALESCE(secondary_in_input.qty_reject, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) + COALESCE(secondary_in_input.qty_replace, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0)), stocker_input.qty_ply) dc_in_qty,
                        stocker_input.qty_ply,
                        stocker_input.updated_at,
                        COALESCE(form_cut_input.waktu_selesai, form_cut_reject.updated_at) waktu_selesai
                    FROM
                        `stocker_input`
                        LEFT JOIN `form_cut_input` ON `form_cut_input`.`id` = `stocker_input`.`form_cut_id`
                        LEFT JOIN `form_cut_reject` ON `form_cut_reject`.`id` = `stocker_input`.`form_reject_id`
                        LEFT JOIN `part_detail` ON `stocker_input`.`part_detail_id` = `part_detail`.`id`
                        LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                        LEFT JOIN `dc_in_input` ON `dc_in_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                        LEFT JOIN `secondary_in_input` ON `secondary_in_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                        LEFT JOIN `secondary_inhouse_input` ON `secondary_inhouse_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                        LEFT JOIN `rack_detail_stocker` ON `rack_detail_stocker`.`stocker_id` = `stocker_input`.`id_qr_stocker`
                        LEFT JOIN `trolley_stocker` ON `trolley_stocker`.`stocker_id` = `stocker_input`.`id`
                        LEFT JOIN `trolley` ON `trolley`.`id` = `trolley_stocker`.`trolley_id`
                        LEFT JOIN `loading_line` ON `loading_line`.`stocker_id` = `stocker_input`.`id`
                ) stock_location
                WHERE
                    (MONTH(stock_location.waktu_selesai) = '".$month."') AND
                    (YEAR(stock_location.waktu_selesai) = '".$year."')
                GROUP BY
                    `stock_location`.`form_cut_id`,
                    `stock_location`.`so_det_id`,
                    `stock_location`.`group_stocker`,
                    `stock_location`.`ratio`
            ");

            return $dataQty;
        }
    // End of DC

    // Sewing
        public function sewingEff(Request $request) {
            ini_set("max_execution_time", 0);
            ini_set("memory_limit", '2048M');

            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            if ($request->ajax()) {
                $month = date("m");
                $year = date("Y");

                if ($request->month) {
                    $month = $request->month;
                }
                if ($request->year) {
                    $year = $request->year;
                }

                $sewingEfficiencyData = DB::connection('mysql_sb')->table('master_plan')->
                    selectRaw("
                        tgl_plan tgl_produksi,
                        ROUND((SUM(IFNULL( rfts.rft, 0 )) / SUM((IFNULL( rfts.rft, 0 ) + IFNULL( defects.defect, 0 ) + IFNULL( reworks.rework, 0 ) + IFNULL( rejects.reject, 0 ))) * 100 ), 2) rft,
                        AVG(master_plan.target_effy) target_efficiency,
                        SUM(IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) output_awal,
                        SUM(IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) + IFNULL( rfts_1.rft, 0 ) output,
                        IFNULL( rfts_1.rft, 0 ) additional,
                        GROUP_CONCAT(IFNULL( rfts_1.rft, 0 )) additional_output,
                        SUM((IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) * master_plan.smv) mins_prod,
                        IFNULL( rfts_1.mins_prod, 0 ) additional_mins_prod,
                        SUM((IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) * master_plan.smv ) + IFNULL( rfts_1.mins_prod, 0 ) mins_prod_total,
                        (SUM( master_plan.man_power * master_plan.jam_kerja ) * 60 ) mins_avail,
                        (SUM( master_plan.man_power * master_plan.jam_kerja ) * 60 ) total_mins_avail,
                        ((SUM((IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) * master_plan.smv ) + IFNULL( rfts_1.mins_prod, 0 )) / (SUM( master_plan.man_power * master_plan.jam_kerja ) * 60 ) * 100) efficiency
                    ")->
                    leftJoin(DB::raw("(SELECT count(rfts.id) rft, master_plan.id master_plan_id, DATE(rfts.updated_at) tgl_output from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where (MONTH(rfts.updated_at) = '".$month."' AND YEAR(rfts.updated_at) = '".$year."') and status = 'NORMAL' and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "rfts.tgl_output"); })->
                    leftJoin(DB::raw("(SELECT count(defects.id) defect, master_plan.id master_plan_id, DATE(defects.updated_at) tgl_output from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and (MONTH(defects.updated_at) = '".$month."' AND YEAR(defects.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at)) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "defects.tgl_output"); })->
                    leftJoin(DB::raw("(SELECT count(defrew.id) rework, master_plan.id master_plan_id, DATE(defrew.updated_at) tgl_output from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and (MONTH(defrew.updated_at) = '".$month."' AND YEAR(defrew.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at)) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "reworks.tgl_output"); })->
                    leftJoin(DB::raw("(SELECT count(rejects.id) reject, master_plan.id master_plan_id, DATE(rejects.updated_at) tgl_output from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where (MONTH(rejects.updated_at) = '".$month."' AND YEAR(rejects.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at)) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "rejects.tgl_output"); })->
                    leftJoin(DB::raw("(
                        SELECT
                            tgl_output,
                            SUM(rft) rft,
                            SUM(rft * smv) mins_prod,
                            SUM(man_power * jam_kerja) * 60 mins_avail
                        FROM
                            (
                                SELECT
                                    count( rfts.id ) rft,
                                    master_plan.id master_plan_id,
                                    master_plan.tgl_plan,
                                    DATE ( rfts.updated_at ) tgl_output,
                                    master_plan.man_power,
                                    master_plan.jam_kerja,
                                    master_plan.smv
                                FROM
                                    output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                where
                                    rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                    AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                GROUP BY
                                    master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                                having
                                    tgl_plan != tgl_output
                            ) back_output
                        GROUP BY
                            back_output.tgl_output
                    ) rfts_1"), "master_plan.tgl_plan", "=", "rfts_1.tgl_output")->
                    where("master_plan.cancel", 'N')->
                    whereRaw("(
                        MONTH(master_plan.tgl_plan) = '".$month."'
                        AND
                        YEAR(master_plan.tgl_plan) = '".$year."'
                        AND
                        master_plan.tgl_plan <= '".date("Y-m-d")."'
                    )")->
                    groupBy("master_plan.tgl_plan")->
                    get();

                return json_encode($sewingEfficiencyData);
            }

            return view('dashboard', ['page' => 'dashboard-sewing-eff', 'months' => $months, 'years' => $years]);
        }

        public function sewingSummary(Request $request) {
            $month = date("m");
            $year = date("Y");

            if ($request->month) {
                $month = $request->month;
            }
            if ($request->year) {
                $year = $request->year;
            }

            $sewingSummaryData = DB::connection('mysql_sb')->table('master_plan')->
                selectRaw("
                    COUNT(DISTINCT master_plan.id_ws) total_order,
                    SUM(IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) total_output,
                    ROUND((SUM(((IFNULL( rfts.rft, 0 )+ IFNULL( reworks.rework, 0 ))* master_plan.smv ))/SUM( master_plan.man_power * master_plan.jam_kerja * 60 ) * 100 ), 2) total_efficiency
                ")->
                leftJoin(DB::raw("(SELECT count(rfts.id) rft, master_plan.id master_plan_id from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where (MONTH(rfts.updated_at) = '".$month."' AND YEAR(rfts.updated_at) = '".$year."') and status = 'NORMAL' and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan) as rfts"), "master_plan.id", "=", "rfts.master_plan_id")->
                leftJoin(DB::raw("(SELECT count(defects.id) defect, master_plan.id master_plan_id from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and (MONTH(defects.updated_at) = '".$month."' AND YEAR(defects.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan) as defects"), "master_plan.id", "=", "defects.master_plan_id")->
                leftJoin(DB::raw("(SELECT count(defrew.id) rework, master_plan.id master_plan_id from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and (MONTH(defrew.updated_at) = '".$month."' AND YEAR(defrew.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan) as reworks"), "master_plan.id", "=", "reworks.master_plan_id")->
                leftJoin(DB::raw("(SELECT count(rejects.id) reject, master_plan.id master_plan_id from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where (MONTH(rejects.updated_at) = '".$month."' AND YEAR(rejects.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan) as rejects"), "master_plan.id", "=", "rejects.master_plan_id")->
                where("master_plan.cancel", 'N')->
                whereRaw("(
                    MONTH(master_plan.tgl_plan) = '".$month."'
                    AND
                    YEAR(master_plan.tgl_plan) = '".$year."'
                )")->
                groupByRaw("MONTH(master_plan.tgl_plan), YEAR(master_plan.tgl_plan)")->first();

            return json_encode($sewingSummaryData);
        }

        public function sewingOutputData(Request $request) {
            $month = $request->month ? $request->month : date('m');
            $year = $request->year ? $request->year : date('Y');

            $sewingOutputData = DB::connection('mysql_sb')->select("
                    SELECT
                        ( act_costing.cost_date ) tanggal_order,
                        ( mastersupplier.Supplier ) buyer,
                        ( act_costing.kpno ) act_costing_ws,
                        ( act_costing.styleno ) style,
                        ( master_plan.color ),
                        SUM( so_det.qty ) qty,
                        ( so_det.size ),
                        SUM( rfts.rft ) qty_output,
                        SUM( so_det.qty ) - SUM( rfts.rft ) qty_balance,
                        SUM( defects.defect ),
                        SUM( reworks.rework ),
                        SUM( rejects.reject ),
                        SUM( rfts_packing.rft ) qty_output_p,
                        SUM( so_det.qty ) - SUM( rfts_packing.rft ) qty_balance_p,
                        ROUND((
                                SUM(
                                    IFNULL( rfts.rft, 0 )) / SUM((
                                    IFNULL( rfts.rft, 0 ) + IFNULL( defects.defect, 0 ) + IFNULL( reworks.rework, 0 ) + IFNULL( rejects.reject, 0 ))) * 100
                                ),
                            2
                        ) rft_rate,
                        ROUND((
                                SUM(
                                    IFNULL( defects.defect, 0 ) + IFNULL( reworks.rework, 0 )) / SUM((
                                    IFNULL( rfts.rft, 0 ) + IFNULL( defects.defect, 0 ) + IFNULL( reworks.rework, 0 ) + IFNULL( rejects.reject, 0 ))) * 100
                                ),
                            2
                        ) defect_rate,
                        ( act_costing.deldate ) tanggal_delivery
                    FROM
                        (
                        SELECT
                            id_ws,
                            color
                        FROM
                            master_plan
                        WHERE
                            master_plan.cancel = 'N'
                            AND MONTH ( master_plan.tgl_plan ) = '".$month."'
                            AND YEAR ( master_plan.tgl_plan ) = '".$year."'
                        GROUP BY
                            MONTH ( master_plan.tgl_plan ),
                            YEAR ( master_plan.tgl_plan ),
                            master_plan.id_ws,
                            master_plan.color
                        ) master_plan
                        LEFT JOIN `act_costing` ON `act_costing`.`id` = `master_plan`.`id_ws`
                        LEFT JOIN `mastersupplier` ON `mastersupplier`.`Id_Supplier` = `act_costing`.`id_buyer`
                        LEFT JOIN `so` ON `so`.`id_cost` = `act_costing`.`id`
                        LEFT JOIN `so_det` ON `so_det`.`id_so` = `so`.`id`
                        AND so_det.color = master_plan.color
                        LEFT JOIN master_size_new ON master_size_new.size = so_det.size
                        LEFT JOIN (
                        SELECT
                            count( rfts.id ) rft,
                            so_det.qty,
                            so_det.id AS so_det_id,
                            so_det.size,
                            master_plan.id master_plan_id
                        FROM
                            output_rfts rfts
                            INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                            INNER JOIN so_det ON so_det.id = rfts.so_det_id
                        WHERE
                            STATUS = 'NORMAL'
                            AND MONTH ( rfts.updated_at ) = '".$month."'
                            AND YEAR ( rfts.updated_at ) = '".$year."'
                            AND MONTH ( master_plan.tgl_plan ) = '".$month."'
                            AND YEAR ( master_plan.tgl_plan ) = '".$year."'
                        GROUP BY
                            rfts.so_det_id
                        ) AS rfts ON `so_det`.`id` = `rfts`.`so_det_id`
                        LEFT JOIN (
                        SELECT
                            count( defects.id ) defect,
                            so_det.qty,
                            so_det.id AS so_det_id,
                            so_det.size,
                            master_plan.id master_plan_id
                        FROM
                            output_defects defects
                            INNER JOIN master_plan ON master_plan.id = defects.master_plan_id
                            INNER JOIN so_det ON so_det.id = defects.so_det_id
                        WHERE
                            defects.defect_status = 'defect'
                            AND MONTH ( defects.updated_at ) = '".$month."'
                            AND YEAR ( defects.updated_at ) = '".$year."'
                            AND MONTH ( master_plan.tgl_plan ) = '".$month."'
                            AND YEAR ( master_plan.tgl_plan ) = '".$year."'
                        GROUP BY
                            defects.so_det_id
                        ) AS defects ON `so_det`.`id` = `defects`.`so_det_id`
                        LEFT JOIN (
                        SELECT
                            count( defrew.id ) rework,
                            so_det.qty,
                            so_det.id AS so_det_id,
                            so_det.size,
                            master_plan.id master_plan_id
                        FROM
                            output_defects defrew
                            INNER JOIN master_plan ON master_plan.id = defrew.master_plan_id
                            INNER JOIN so_det ON so_det.id = defrew.so_det_id
                        WHERE
                            defrew.defect_status = 'reworked'
                            AND MONTH ( defrew.updated_at ) = '".$month."'
                            AND YEAR ( defrew.updated_at ) = '".$year."'
                            AND MONTH ( master_plan.tgl_plan ) = '".$month."'
                            AND YEAR ( master_plan.tgl_plan ) = '".$year."'
                        GROUP BY
                            defrew.so_det_id
                        ) AS reworks ON `so_det`.`id` = `reworks`.`so_det_id`
                        LEFT JOIN (
                        SELECT
                            count( rejects.id ) reject,
                            so_det.qty,
                            so_det.id AS so_det_id,
                            so_det.size,
                            master_plan.id master_plan_id
                        FROM
                            output_rejects rejects
                            INNER JOIN master_plan ON master_plan.id = rejects.master_plan_id
                            INNER JOIN so_det ON so_det.id = rejects.so_det_id
                        WHERE
                            STATUS = 'NORMAL'
                            AND MONTH ( rejects.updated_at ) = '".$month."'
                            AND YEAR ( rejects.updated_at ) = '".$year."'
                            AND MONTH ( master_plan.tgl_plan ) = '".$month."'
                            AND YEAR ( master_plan.tgl_plan ) = '".$year."'
                        GROUP BY
                            rejects.so_det_id
                        ) AS rejects ON `so_det`.`id` = `rejects`.`so_det_id`
                        LEFT JOIN (
                        SELECT
                            count( rfts.id ) rft,
                            so_det.qty,
                            so_det.id AS so_det_id,
                            so_det.size,
                            master_plan.id master_plan_id
                        FROM
                            output_rfts_packing rfts
                            INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                            INNER JOIN so_det ON so_det.id = rfts.so_det_id
                        WHERE
                            STATUS = 'NORMAL'
                            AND MONTH ( rfts.updated_at ) = '".$month."'
                            AND YEAR ( rfts.updated_at ) = '".$year."'
                            AND MONTH ( master_plan.tgl_plan ) = '".$month."'
                            AND YEAR ( master_plan.tgl_plan ) = '".$year."'
                        GROUP BY
                            rfts.so_det_id
                        ) AS rfts_packing ON `so_det`.`id` = `rfts_packing`.`so_det_id`
                    GROUP BY
                        master_plan.id_ws,
                        act_costing.id,
                        master_plan.color,
                        so_det.color,
                        so_det.size
                    ORDER BY
                        act_costing.kpno,
                        so_det.color,
                        master_size_new.urutan
                ");

            return DataTables::of($sewingOutputData)->toJson();
        }
    // End of Sewing

    // Manage User
        public function manageUser(Request $request) {
            $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
            $years = array_reverse(range(1999, date('Y')));

            return view('dashboard', ['page' => 'dashboard-manage-user', "months" => $months, "years" => $years]);
        }
    // End of Manage User
}
