<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rack;
use App\Models\Stocker;
use App\Models\Marker;
use App\Models\DCInput;
use Yajra\DataTables\Facades\DataTables;
use DB;

class DashboardController extends Controller
{
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
                    marker_input.buyer,
                    marker_input.act_costing_ws,
                    marker_input.style,
                    marker_input.color,
                    marker_input.tgl_cutting,
                    marker_input.kode,
                    marker_input.urutan_marker,
                    marker_input.panel,
                    COALESCE(CONCAT(master_part.nama_part, ' / ', CONCAT(COALESCE(part_detail.cons, '-'), ' ', COALESCE(UPPER(part_detail.unit), '-')), ' / ', CONCAT(COALESCE(master_secondary.tujuan, '-'), ' - ', COALESCE(master_secondary.proses, '-')) ), '-') nama_part
                ")->
                leftJoin("part", function ($join) {
                    $join->on("part.act_costing_id", "=", "marker_input.act_costing_id");
                    $join->on("part.panel", "=", "marker_input.panel");
                })->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                whereRaw("(MONTH(marker_input.tgl_cutting) = '".$month."')")->
                whereRaw("(YEAR(marker_input.tgl_cutting) = '".$year."')")->
                groupBy("marker_input.act_costing_id", "marker_input.buyer", "marker_input.style", "marker_input.color", "marker_input.panel", "marker_input.id", "part_detail.id")->
                orderBy("marker_input.tgl_cutting", "desc")->
                orderBy("marker_input.buyer", "asc")->
                orderBy("marker_input.act_costing_ws", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderBy("marker_input.panel", "asc")->
                orderBy("marker_input.urutan_marker", "asc");

            return DataTables::eloquent($marker)->toJson();
        }

        return view('dashboard', ['page' => 'dashboard-marker', 'months' => $months, 'years' => $years]);
    }

    // Cutting
    // Marker
    public function cutting(Request $request) {
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

            $marker = FormCutInput::selectRaw("
                    marker_input.buyer,
                    marker_input.act_costing_ws,
                    marker_input.style,
                    marker_input.color,
                    marker_input.tgl_cutting,
                    marker_input.kode,
                    marker_input.urutan_marker,
                    marker_input.panel,
                    COALESCE(CONCAT(master_part.nama_part, ' / ', CONCAT(COALESCE(part_detail.cons, '-'), ' ', COALESCE(UPPER(part_detail.unit), '-')), ' / ', CONCAT(COALESCE(master_secondary.tujuan, '-'), ' - ', COALESCE(master_secondary.proses, '-')) ), '-') nama_part
                ")->
                leftJoin("part", function ($join) {
                    $join->on("part.act_costing_id", "=", "marker_input.act_costing_id");
                    $join->on("part.panel", "=", "marker_input.panel");
                })->
                leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                whereRaw("(MONTH(marker_input.tgl_cutting) = '".$month."')")->
                whereRaw("(YEAR(marker_input.tgl_cutting) = '".$year."')")->
                groupBy("marker_input.act_costing_id", "marker_input.buyer", "marker_input.style", "marker_input.color", "marker_input.panel", "marker_input.id", "part_detail.id")->
                orderBy("marker_input.tgl_cutting", "desc")->
                orderBy("marker_input.buyer", "asc")->
                orderBy("marker_input.act_costing_ws", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderBy("marker_input.panel", "asc")->
                orderBy("marker_input.urutan_marker", "asc");

            return DataTables::eloquent($marker)->toJson();
        }

        return view('dashboard', ['page' => 'dashboard-marker', 'months' => $months, 'years' => $years]);
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

            $dc = DCInput::selectRaw("
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
                orderBy("stocker_input.range_awal", "asc");

            return DataTables::eloquent($stockers)->toJson();
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
}
