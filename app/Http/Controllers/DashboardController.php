<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Rack;
use App\Models\Stocker;
use App\Models\Marker;
use App\Models\DCIn;
use App\Models\FormCutInput;
use Yajra\DataTables\Facades\DataTables;
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

            $form = FormCutInput::selectRaw("
                    marker_input.id marker_id,
                    marker_input.buyer,
                    marker_input.act_costing_ws,
                    marker_input.style,
                    marker_input.color,
                    marker_input.kode,
                    marker_input.urutan_marker,
                    marker_input.panel,
                    form_cut_input.id form_id,
                    form_cut_input.status form_status,
                    form_cut_input.tipe_form_cut,
                    COALESCE(DATE(form_cut_input.waktu_mulai), '-') tgl_form_cut,
                    COALESCE(form_cut_input.no_form, '-') no_form,
                    COALESCE(form_cut_input.no_cut, '-') no_cut,
                    COALESCE(form_cut_input.total_lembar, '-') total_lembar,
                    COALESCE(GROUP_CONCAT(DISTINCT form_cut_input_detail.id_roll), '-') id_roll,
                    COALESCE(GROUP_CONCAT(DISTINCT form_cut_input_detail.id_item), '-') id_item,
                    COALESCE(form_cut_input_detail.lot, '-') lot,
                    COALESCE(form_cut_input_detail.roll, '-') roll,
                    COALESCE(ROUND(SUM(COALESCE(form_cut_input_detail.qty, 0)), 2), '-') qty,
                    COALESCE(form_cut_input_detail.unit, '-') unit,
                    COALESCE(ROUND(SUM(COALESCE(form_cut_input_detail.total_pemakaian_roll, 0)), 2), '-') total_pemakaian_roll,
                    COALESCE(ROUND(SUM(COALESCE(form_cut_input_detail.piping, 0)), 2), '-') piping,
                    COALESCE(ROUND(SUM(COALESCE(form_cut_input_detail.short_roll, 0)), 2), '-') short_roll,
                    COALESCE(ROUND(SUM(COALESCE(form_cut_input_detail.remark, 0)), 2), '-') remark
                ")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("form_cut_input_detail", "form_cut_input_detail.no_form_cut_input", "=", "form_cut_input.no_form")->
                whereRaw("(marker_input.cancel IS NULL OR marker_input.cancel != 'Y')")->
                whereRaw("(form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y')")->
                whereRaw("(COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."')")->
                groupBy("marker_input.id", "form_cut_input.id", "form_cut_input_detail.unit")->
                orderBy("form_cut_input.waktu_mulai", "desc")->
                orderBy("marker_input.buyer", "asc")->
                orderBy("marker_input.act_costing_ws", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderBy("marker_input.panel", "asc")->
                orderBy("marker_input.urutan_marker", "asc")->
                orderBy("form_cut_input.no_cut", "asc")->
                orderBy("form_cut_input_detail.id", "asc");

            return DataTables::eloquent($form)->toJson();
        }

        return view('dashboard', ['page' => 'dashboard-cutting', 'months' => $months, 'years' => $years]);
    }

    // Cutting Qty
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
                        LEFT JOIN marker_input ON marker_input.kode = form_cut_input.id_marker
                        LEFT JOIN cutting_plan ON cutting_plan.no_form_cut_input = form_cut_input.no_form
                    WHERE
                        ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' ) AND
                        ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' ) AND
                        ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."') )
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
            leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
            leftJoin("cutting_plan", "cutting_plan.no_form_cut_input", "form_cut_input.no_form")->
            join("users as meja", "meja.id", "form_cut_input.no_meja")->
            whereRaw("
                ( marker_input.cancel IS NULL OR marker_input.cancel != 'Y' ) AND
                ( form_cut_input.cancel IS NULL OR form_cut_input.cancel != 'Y' ) AND
                ( cutting_plan.tgl_plan = '".$date."' OR (cutting_plan.tgl_plan != '".$date."' AND COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) = '".$date."') )
            ")->
            groupByRaw("(CASE WHEN cutting_plan.tgl_plan != '".$date."' THEN COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai)) ELSE cutting_plan.tgl_plan END), meja.id")->
            get();

        return json_encode($cuttingForm);
    }

    // Stocker
    public function stocker(Request $request) {
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

            $stocker = MarkerDetail::selectRaw("
                    marker_input.buyer,
                    marker_input.act_costing_ws,
                    marker_input.style,
                    marker_input.color,
                    marker_input.kode,
                    marker_input.urutan_marker,
                    marker_input.panel,
                    marker_input_detail.so_det_id,
                    MAX(form_cut_input.no_form) no_form,
                    MAX(stocker_input.id_qr_stocker) id_qr_stocker,
                    COALESCE(stocker_input.ratio, marker_input_detail.ratio) ratio,
                    form_cut_input.no_cut,
                    MAX(stocker_input.id) stocker_id,
                    MAX(stocker_input.shade) shade,
                    MAX(stocker_input.group_stocker) group_stocker,
                    MAX(stocker_input.qty_ply) qty_ply,
                    MAX(CAST(stocker_input.range_akhir as UNSIGNED)) range_akhir,
                    modify_size_qty.difference_qty
                ")->
                leftJoin("marker_input", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                leftJoin("stocker_input", function ($join) {
                    $join->on("stocker_input.form_cut_id", "=", "form_cut_input.id");
                    $join->on("stocker_input.so_det_id", "=", "marker_input_detail.so_det_id");
                })->
                leftJoin("modify_size_qty", function ($join) {
                    $join->on("modify_size_qty.no_form", "=", "form_cut_input.no_form");
                    $join->on("modify_size_qty.so_det_id", "=", "marker_input_detail.so_det_id");
                })->
                where("marker_input.act_costing_ws", $dataSpreading->ws)->
                where("marker_input.color", $dataSpreading->color)->
                where("marker_input.panel", $dataSpreading->panel)->
                where("form_cut_input.no_cut", "<=", $dataSpreading->no_cut)->
                where("part_form.part_id", $dataSpreading->part_id)->
                // where("marker_input_detail.ratio", ">", "0")->
                groupBy("form_cut_input.no_form", "form_cut_input.no_cut", "marker_input_detail.so_det_id")->
                orderBy("form_cut_input.no_cut", "desc")->
                orderBy("form_cut_input.no_form", "desc")->
                get();

            $stocker = Stocker::selectRaw("
                    marker_input.buyer,
                    marker_input.act_costing_ws,
                    marker_input.style,
                    marker_input.color,
                    marker_input.kode,
                    marker_input.urutan_marker,
                    marker_input.panel,
                    COALESCE(form_cut_input.tgl_form_cut, '-') tgl_form_cut,
                    COALESCE(form_cut_input.no_form, '-') no_form,
                    COALESCE(form_cut_input.no_cut, '-') no_cut,
                    COALESCE(form_cut_input.total_lembar, '-') total_lembar,
                    COALESCE(form_cut_input_detail.id_roll, '-') id_roll,
                    COALESCE(form_cut_input_detail.id_item, '-') id_item,
                    COALESCE(LEFT(form_cut_input_detail.detail_item, 10), '-') detail_item,
                    COALESCE(form_cut_input_detail.group_roll, '-') group_roll,
                    COALESCE(form_cut_input_detail.lot, '-') lot,
                    COALESCE(form_cut_input_detail.roll, '-') roll,
                    COALESCE(form_cut_input_detail.qty, '-') qty,
                    COALESCE(form_cut_input_detail.unit, '-') unit,
                    COALESCE(form_cut_input_detail.total_pemakaian_roll, '-') total_pemakaian_roll,
                    COALESCE(form_cut_input_detail.piping, '-') piping,
                    COALESCE(form_cut_input_detail.short_roll, '-') short_roll,
                    COALESCE(form_cut_input_detail.remark, '-') remark
                ")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker.form_cut_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("form_cut_input_detail", "form_cut_input_detail.no_form_cut_input", "=", "form_cut_input.no_form")->
                whereRaw("(MONTH(form_cut_input.tgl_form_cut) = '".$month."')")->
                whereRaw("(YEAR(form_cut_input.tgl_form_cut) = '".$year."')")->
                groupBy("marker_input.id", "form_cut_input.id", "form_cut_input_detail.id")->
                orderBy("marker_input.tgl_cutting", "desc")->
                orderBy("marker_input.buyer", "asc")->
                orderBy("marker_input.act_costing_ws", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderBy("marker_input.panel", "asc")->
                orderBy("marker_input.urutan_marker", "asc")->
                orderBy("form_cut_input.no_cut", "asc")->
                orderBy("form_cut_input_detail.id", "asc");

            return DataTables::eloquent($stocker)->toJson();
        }

        return view('dashboard', ['page' => 'dashboard-stocker', 'months' => $months, 'years' => $years]);
    }

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
                leftJoin("part_detail", "stocker_input.part_detail_id", "=", "part_detail.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
                leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "stocker_input.id")->
                leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
                leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
                whereRaw("(MONTH(form_cut_input.waktu_selesai) = '".$month."')")->
                whereRaw("(YEAR(form_cut_input.waktu_selesai) = '".$year."')")->
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
                    stocker_input.form_cut_id,
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
                    form_cut_input.waktu_selesai
                FROM
                    `stocker_input`
                    LEFT JOIN `form_cut_input` ON `form_cut_input`.`id` = `stocker_input`.`form_cut_id`
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
                    SUM(IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) + IFNULL( rfts_1.rft, 0 ) output,
                    IFNULL( rfts_1.rft, 0 ) additional,
                    GROUP_CONCAT(IFNULL( rfts_1.rft, 0 )) additional_output,
                    SUM((IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) * master_plan.smv) mins_prod,
                    IFNULL( rfts_1.mins_prod, 0 ) additional_mins_prod,
                    SUM((IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) * master_plan.smv ) + IFNULL( rfts_1.mins_prod, 0 ) mins_prod_total,
                    (SUM( master_plan.man_power * master_plan.jam_kerja ) * 60 ) mins_avail,
                    (SUM((IFNULL( rfts.rft, 0 ) + IFNULL( reworks.rework, 0 )) * master_plan.smv ) + IFNULL( rfts_1.mins_prod, 0 ) / (SUM( master_plan.man_power * master_plan.jam_kerja ) * 60 )) * 100 efficiency
                ")->
                leftJoin(DB::raw("(SELECT count(rfts.id) rft, master_plan.id master_plan_id, DATE(rfts.updated_at) tgl_output from output_rfts rfts inner join master_plan on master_plan.id = rfts.master_plan_id where (MONTH(rfts.updated_at) = '".$month."' AND YEAR(rfts.updated_at) = '".$year."') and status = 'NORMAL' and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)) as rfts"), function ($join) { $join->on("master_plan.id", "=", "rfts.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "rfts.tgl_output"); })->
                leftJoin(DB::raw("(SELECT count(defects.id) defect, master_plan.id master_plan_id, DATE(defects.updated_at) tgl_output from output_defects defects inner join master_plan on master_plan.id = defects.master_plan_id where defects.defect_status = 'defect' and (MONTH(defects.updated_at) = '".$month."' AND YEAR(defects.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at)) as defects"), function ($join) { $join->on("master_plan.id", "=", "defects.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "defects.tgl_output"); })->
                leftJoin(DB::raw("(SELECT count(defrew.id) rework, master_plan.id master_plan_id, DATE(defrew.updated_at) tgl_output from output_defects defrew inner join master_plan on master_plan.id = defrew.master_plan_id where defrew.defect_status = 'reworked' and (MONTH(defrew.updated_at) = '".$month."' AND YEAR(defrew.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(defrew.updated_at)) as reworks"), function ($join) { $join->on("master_plan.id", "=", "reworks.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "reworks.tgl_output"); })->
                leftJoin(DB::raw("(SELECT count(rejects.id) reject, master_plan.id master_plan_id, DATE(rejects.updated_at) tgl_output from output_rejects rejects inner join master_plan on master_plan.id = rejects.master_plan_id where (MONTH(rejects.updated_at) = '".$month."' AND YEAR(rejects.updated_at) = '".$year."') and (MONTH(master_plan.tgl_plan) = '".$month."' AND YEAR(master_plan.tgl_plan) = '".$year."') GROUP BY master_plan.id, master_plan.tgl_plan, DATE(rejects.updated_at)) as rejects"), function ($join) { $join->on("master_plan.id", "=", "rejects.master_plan_id"); $join->on("master_plan.tgl_plan", "=", "rejects.tgl_output"); })->
                leftJoin(DB::raw("(
                    SELECT
                        tgl_output,
                        SUM(rft) rft,
                        SUM(rft * smv) mins_prod
                    FROM
                        (
                            SELECT
                                count( rfts.id ) rft,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                DATE ( rfts.updated_at ) tgl_output,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                            inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                (MONTH(rfts.updated_at) = '08' AND YEAR(rfts.updated_at) = '2024') and
                                (MONTH(master_plan.tgl_plan) = '08' AND YEAR(master_plan.tgl_plan) = '2024')
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            having tgl_plan != tgl_output
                        ) back_output
                    GROUP BY
                        back_output.tgl_output
                ) rfts_1"), "master_plan.tgl_plan", "=", "rfts_1.tgl_output")->
                where("master_plan.cancel", 'N')->
                whereRaw("(
                    MONTH(master_plan.tgl_plan) = '".$month."'
                    AND
                    YEAR(master_plan.tgl_plan) = '".$year."'
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
}
