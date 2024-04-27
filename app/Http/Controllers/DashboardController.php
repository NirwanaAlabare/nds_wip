<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rack;
use App\Models\Stocker;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    public function dc(Request $request) {
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

            $stockers = Stocker::selectRaw("
                    stocker_input.id stocker_id,
                    stocker_input.id_qr_stocker,
                    stocker_input.act_costing_ws,
                    stocker_input.color,
                    stocker_input.size,
                    stocker_input.so_det_id,
                    stocker_input.shade,
                    stocker_input.ratio,
                    master_part.nama_part,
                    CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir, (CASE WHEN dc_in_input.qty_reject IS NOT NULL AND dc_in_input.qty_replace IS NOT NULL THEN CONCAT(' (', (dc_in_input.qty_replace - dc_in_input.qty_reject), ') ') ELSE null END)) stocker_range,
                    stocker_input.status,
                    dc_in_input.id dc_in_id,
                    dc_in_input.tujuan,
                    dc_in_input.tempat,
                    dc_in_input.lokasi,
                    (CASE WHEN dc_in_input.tujuan = 'SECONDARY DALAM' OR dc_in_input.tujuan = 'SECONDARY LUAR' THEN dc_in_input.lokasi ELSE '-' END) secondary,
                    COALESCE(rack_detail_stocker.nm_rak, (CASE WHEN dc_in_input.tempat = 'RAK' THEN dc_in_input.lokasi ELSE null END), (CASE WHEN dc_in_input.lokasi = 'RAK' THEN dc_in_input.det_alokasi ELSE null END), '-') rak,
                    COALESCE(trolley.nama_trolley, (CASE WHEN dc_in_input.tempat = 'TROLLEY' THEN dc_in_input.lokasi ELSE null END), '-') troli,
                    (dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace) dc_in_qty,
                    CONCAT(form_cut_input.no_form, ' / ', form_cut_input.no_cut) no_cut,
                    COALESCE(UPPER(loading_line.nama_line), '-') line,
                    stocker_input.updated_at latest_update
                ")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("part_detail", "stocker_input.part_detail_id", "=", "part_detail.id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
                leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "stocker_input.id")->
                leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
                leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
                whereRaw("(MONTH(stocker_input.updated_at) = '".$month."' OR MONTH(form_cut_input.waktu_selesai) = '".$month."')")->
                whereRaw("(YEAR(stocker_input.updated_at) = '".$year."' OR YEAR(form_cut_input.waktu_selesai) = '".$year."')")->
                orderBy("stocker_input.act_costing_ws", "asc")->
                orderBy("stocker_input.color", "asc")->
                orderBy("form_cut_input.no_cut", "asc")->
                orderBy("master_part.nama_part", "asc")->
                orderBy("stocker_input.so_det_id", "asc")->
                orderBy("stocker_input.shade", "desc")->
                orderBy("stocker_input.range_awal", "asc");

            return DataTables::eloquent($stockers)->toJson();
        }

        $dataQty = Stocker::selectRaw("
                stocker_input.status,
                (CASE WHEN dc_in_input.tujuan = 'SECONDARY DALAM' OR dc_in_input.tujuan = 'SECONDARY LUAR' THEN dc_in_input.lokasi ELSE '-' END) secondary,
                COALESCE(rack_detail_stocker.nm_rak, (CASE WHEN dc_in_input.tempat = 'RAK' THEN dc_in_input.lokasi ELSE null END), (CASE WHEN dc_in_input.lokasi = 'RAK' THEN dc_in_input.det_alokasi ELSE null END), '-') rak,
                COALESCE(trolley.nama_trolley, (CASE WHEN dc_in_input.tempat = 'TROLLEY' THEN dc_in_input.lokasi ELSE null END), '-') troli,
                COALESCE(UPPER(loading_line.nama_line), '-') line,
                (dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace) dc_in_qty
            ")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("part_detail", "stocker_input.part_detail_id", "=", "part_detail.id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
            leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "stocker_input.id")->
            leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
            get();

        return view('dashboard', ['page' => 'dashboard-dc', 'months' => $months, 'years' => $years, 'dataQty' => $dataQty]);
    }
}
