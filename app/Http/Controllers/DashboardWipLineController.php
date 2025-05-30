<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Rack;
use App\Models\Stocker;
use App\Models\Marker;
use App\Models\DCIn;
use App\Models\FormCutInput;
use App\Models\SignalBit\UserLine;
use App\Exports\Sewing\ChiefSewingRangeExport;
use App\Exports\Sewing\LeaderSewingRangeExport;
use Yajra\DataTables\Facades\DataTables;
use App\Events\TriggerWipLine;
use DB;
use Excel;

class DashboardWipLineController extends Controller
{
    public function track(Request $request)
    {
        return Redirect::to('/home');
    }

    public function index(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '2048M');

        $months = [['angka' => 1, 'nama' => 'Januari'], ['angka' => 2, 'nama' => 'Februari'], ['angka' => 3, 'nama' => 'Maret'], ['angka' => 4, 'nama' => 'April'], ['angka' => 5, 'nama' => 'Mei'], ['angka' => 6, 'nama' => 'Juni'], ['angka' => 7, 'nama' => 'Juli'], ['angka' => 8, 'nama' => 'Agustus'], ['angka' => 9, 'nama' => 'September'], ['angka' => 10, 'nama' => 'Oktober'], ['angka' => 11, 'nama' => 'November'], ['angka' => 12, 'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        $query = DB::connection('mysql_dsb')->select("SELECT COALESCE(sum(jam_kerja),0) as jam_kerja from master_plan where tgl_plan = CURRENT_DATE() and sewing_line = 'line_01'");
        $lines = DB::connection('mysql_dsb')->select("SELECT username as id, FullName as name from userpassword where Groupp = 'SEWING' and (Locked is NULL OR Locked != 1) ORDER BY line_id");

        return view('wip/dashboard-wip', ['page' => 'dashboard-wip', 'lines' => $lines, 'months' => $months, 'years' => $years]);
    }

    public function show_wip_line($id)
    {
        return view('wip.wip-line', [
            'id' => $id,
        ]);
    }

    public function trigger_wip_line(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'line_id' => 'required|string',
        ]);
        $tanggal = $validated['tanggal'];
        $lineId = $validated['line_id'];

        $data['title'] = 'Dashboard WIP';
        $data['user'] = "$lineId - End Line";
        $data['buyer'] = $this->getbuyer($tanggal, $lineId);
        $data['no_ws'] = $this->getno_ws($tanggal, $lineId);
        $data['target_floor'] = $this->cari_target_floor($tanggal, $lineId);

        $data['target_floordom'] = $this->cari_target_floordom($tanggal, $lineId);
        $data['jamkerl1'] = $this->cari_jamker($lineId);
        $data['actuall1'] = $this->cari_actual($tanggal, $lineId);
        $data['day_target1'] = $this->cari_day_target($tanggal, $lineId);
        $data['deffectl1'] = $this->cari_deffect($tanggal, $lineId);
        $data['target_menit'] = $this->cari_target_floor_menit($lineId);
        $data['current_actual'] = $this->cari_current_actual($tanggal, $lineId);

        $data['output7'] = $this->cari_output_jam7($tanggal, $lineId);
        $data['output8'] = $this->cari_output_jam8($tanggal, $lineId);
        $data['output9'] = $this->cari_output_jam9($tanggal, $lineId);
        $data['output10'] = $this->cari_output_jam10($tanggal, $lineId);
        $data['output11'] = $this->cari_output_jam11($tanggal, $lineId);
        $data['output13'] = $this->cari_output_jam13($tanggal, $lineId);
        $data['output14'] = $this->cari_output_jam14($tanggal, $lineId);
        $data['output15'] = $this->cari_output_jam15($tanggal, $lineId);
        $data['output16'] = $this->cari_output_jam16($tanggal, $lineId);
        // //deffect
        $data['deffect7'] = $this->cari_deffect_jam7($tanggal, $lineId);
        $data['deffect8'] = $this->cari_deffect_jam8($tanggal, $lineId);
        $data['deffect9'] = $this->cari_deffect_jam9($tanggal, $lineId);
        $data['deffect10'] = $this->cari_deffect_jam10($tanggal, $lineId);
        $data['deffect11'] = $this->cari_deffect_jam11($tanggal, $lineId);
        $data['deffect13'] = $this->cari_deffect_jam13($tanggal, $lineId);
        $data['deffect14'] = $this->cari_deffect_jam14($tanggal, $lineId);
        $data['deffect15'] = $this->cari_deffect_jam15($tanggal, $lineId);
        $data['deffect16'] = $this->cari_deffect_jam16($tanggal, $lineId);

        $data['rework'] = $this->cari_rework($tanggal, $lineId);
        $data['list_defect'] = $this->listdefect($tanggal, $lineId);
        $data['link_gambar1'] = $this->cari_link_gambar1($tanggal, $lineId);
        $data['positiondefect'] = $this->cari_positiondefect($tanggal, $lineId);
        $data['man_power'] = $this->cari_menpower($tanggal, $lineId);
        $data['smv'] = $this->cari_smv($tanggal, $lineId);

        // $data['datajam7'] = null;
        $data['datajam7'] = $this->cari_datajam7($lineId);
        // $data['datajam8'] = null;
        $data['datajam8'] = $this->cari_datajam8($lineId);
        // $data['datajam9'] = null;
        $data['datajam9'] = $this->cari_datajam9($lineId);
        // $data['datajam10'] = null;
        $data['datajam10'] = $this->cari_datajam10($lineId);
        // $data['datajam11'] = null;
        $data['datajam11'] = $this->cari_datajam11($lineId);
        // $data['datajam13'] = null;
        $data['datajam13'] = $this->cari_datajam13($lineId);
        // $data['datajam14'] = null;
        $data['datajam14'] = $this->cari_datajam14($lineId);
        // $data['datajam15'] = null;
        $data['datajam15'] = $this->cari_datajam15($lineId);

        $data['dashboard_indicators'] = $this->show_chart_dashboard($lineId);
        $data['dashboard_indicators2'] = $this->show_chart_dashboard2($lineId);

        // broadcast(new TriggerWipLine($data, $lineId));
        return response()->json(
            [
                'message' => 'Data diterima',
                'data' => [
                    'tanggal' => $tanggal,
                    'line_id' => $lineId,
                    'data' => $data,
                ],
            ],
            200,
        );
    }

    function cari_datajam7($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(min_prod / (man_power * 60) * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(min_prod / (man_power * 60) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 0)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 0)) target, min_prod, man_power from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect, max(man_power) man_power, COALESCE(sum(smv * actual_sekarang),0) min_prod from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja, man_power, smv from master_plan where sewing_line = '" .
                $line .
                "' and tgl_plan = CURRENT_DATE()) b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 00:00:00') and updated_at < CONCAT(CURRENT_DATE,' 08:00:00')) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 06:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 07:00:00' and created_at < '".date("Y-m-d")." 08:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam8($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(1 > jam,jam,1) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a)
)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(1 > jam,jam,1) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a)
)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 08:00:00') and updated_at < CONCAT(CURRENT_DATE,' 09:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 08:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 08:00:00' and created_at < '".date("Y-m-d")." 09:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam9($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(2 > jam,jam,2) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(2 > jam,jam,2) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 09:00:00') and updated_at < CONCAT(CURRENT_DATE,' 10:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 09:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 09:00:00' and created_at < '".date("Y-m-d")." 10:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam10($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(3 > jam,jam,3) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(3 > jam,jam,3) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 10:00:00') and updated_at < CONCAT(CURRENT_DATE,' 11:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 10:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 10:00:00' and created_at < '".date("Y-m-d")." 11:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam11($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(4 > jam,jam,4) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(4 > jam,jam,4) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 11:00:00') and updated_at < CONCAT(CURRENT_DATE,' 12:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 11:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 11:00:00' and created_at < '".date("Y-m-d")." 12:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam13($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(5 > jam,jam,5) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(5 > jam,jam,5) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 12:00:00') and updated_at < CONCAT(CURRENT_DATE,' 14:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 12:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 12:00:00' and created_at < '".date("Y-m-d")." 14:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam14($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,GREATEST(target,0) target1, actual_sekarang output, (GREATEST(target,0) - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (GREATEST(target,0) + sisa) target2, ((GREATEST(target,0) + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(6 > jam,jam,6) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(6 > jam,jam,6) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 14:00:00') and updated_at < CONCAT(CURRENT_DATE,' 15:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 14:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 14:00:00' and created_at < '".date("Y-m-d")." 15:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function cari_datajam15($line)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT jam_kerja,GREATEST(target,0) target1, actual_sekarang output, (GREATEST(target,0) - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / actual_sekarang * 100,2) defect_rate1, (GREATEST(target,0) + sisa) target2, ((GREATEST(target,0) + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / actual_sekarang * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - (select IF(7 > jam,jam,7) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - (select IF(7 > jam,jam,7) jam from (select CASE
    WHEN HOUR(NOW()) = 7 THEN 0
    WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
    ELSE HOUR(NOW()) - 7
END jam) a))) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $line .
                "') b on b.tgl_plan = a.tanggal left join
            (SELECT (SELECT master_plan_id from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan = CURRENT_DATE() limit 1) master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $line . "' and b.tgl_plan >= CURRENT_DATE() -10  and updated_at >= CONCAT(CURRENT_DATE,' 15:00:00') and updated_at < CONCAT(CURRENT_DATE,' 16:00:00') ) c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at < '".date("Y-m-d")." 15:00:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" .
                $line .
                "' and b.tgl_plan = CURRENT_DATE() and created_at >= '".date("Y-m-d")." 15:00:00' and created_at < '".date("Y-m-d")." 16:00:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a",
        );
        return $query;
    }

    function getbuyer($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT GROUP_CONCAT(DISTINCT Supplier) buyer from (select mp.id_ws,ms.Supplier from master_plan mp
        inner join act_costing ac on mp.id_ws = ac.id
        inner join mastersupplier ms on ms.Id_Supplier = ac.id_buyer
        where mp.tgl_plan = '" .
                $tanggal .
                "' and mp.sewing_line = '" .
                $lineId .
                "' and mp.cancel = 'N') a",
        );
        return isset($query[0]) ? $query[0]->buyer : null;
    }

    function getno_ws($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT GROUP_CONCAT(DISTINCT kpno) no_ws from (select mp.id_ws,ac.kpno from master_plan mp
        inner join act_costing ac on mp.id_ws = ac.id
        where mp.tgl_plan = '" .
                $tanggal .
                "' and mp.sewing_line = '" .
                $lineId .
                "' and mp.cancel = 'N') a ",
        );

        return isset($query[0]) ? $query[0]->no_ws : null;
    }

    function cari_target_floor($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "select output, IF(jam_kerja = jam_berjalan,target2,target1) target from (select jam_kerja, output, IF(target1 < 0,0,target1) target1, IF(target2 < 0,0,target2) target2 from (SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - CASE
            WHEN HOUR(NOW()) = 7 THEN 0
            WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
            ELSE HOUR(NOW()) - 7
        END)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - CASE
        WHEN HOUR(NOW()) = 7 THEN 0
        WHEN HOUR(NOW()) >= 13 THEN HOUR(NOW()) - 8
        ELSE HOUR(NOW()) - 7
    END)) target from (
                select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
                (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja, man_power, smv from master_plan where sewing_line = '".$lineId."' and tgl_plan = CURRENT_DATE()) b on b.tgl_plan = a.tanggal left join
                (SELECT master_plan_id,COUNT(a.id) AS actual_sekarang FROM output_rfts a INNER JOIN master_plan b ON b.id=a.master_plan_id WHERE b.sewing_line='".$lineId."' AND b.tgl_plan >= CURRENT_DATE() -10 AND ((HOUR (NOW())=7 AND DATE_FORMAT(updated_at,'%H:%i') BETWEEN '00:00' AND '07:59') OR (HOUR(NOW()) = 13 AND DATE_FORMAT(updated_at, '%H:%i') >= '12:00' AND DATE_FORMAT(updated_at, '%H:%i') < '14:00')
            OR (HOUR(NOW()) != 7 AND HOUR(NOW()) != 13  AND DATE_FORMAT(updated_at,'%H:%i') BETWEEN CONCAT(LPAD(HOUR (NOW()),2,'0'),':00') AND CONCAT(LPAD(HOUR (NOW())+1,2,'0'),':00'))) and DATE_FORMAT(updated_at,'%Y-%m-%d') = CURRENT_DATE GROUP BY master_plan_id) c on c.master_plan_id = b.id
                left join
                (SELECT master_plan_id,COUNT(a.id) AS actual_sebelum FROM output_rfts a INNER JOIN master_plan b ON b.id=a.master_plan_id WHERE b.sewing_line='".$lineId."' AND b.tgl_plan=CURRENT_DATE () AND (
            (HOUR(NOW()) = 7 AND created_at < '".date("Y-m-d")." 06:00:00')
            OR (HOUR(NOW()) = 13 AND created_at < '".date("Y-m-d")." 12:00:00')
            OR (HOUR(NOW()) != 7 AND HOUR(NOW()) != 13 AND DATE_FORMAT(created_at,'%H:%i') < CONCAT(LPAD(HOUR(NOW()), 2, '0'), ':00'))
        ) GROUP BY master_plan_id
    ) d on d.master_plan_id = b.id
                left join
                (SELECT master_plan_id,COUNT(a.id) AS defect FROM output_defects a INNER JOIN master_plan b ON b.id=a.master_plan_id WHERE b.sewing_line='".$lineId."' AND b.tgl_plan=CURRENT_DATE () AND ((HOUR (NOW())=7 AND DATE_FORMAT(created_at,'%H:%i') BETWEEN '00:00' AND '07:59') OR (HOUR(NOW()) = 13 AND created_at >= '".date("Y-m-d")." 12:00:00' AND created_at < '".date("Y-m-d")." 14:00:00')
            OR (HOUR(NOW()) != 7 AND HOUR(NOW()) != 13  AND DATE_FORMAT(created_at,'%H:%i') BETWEEN CONCAT(LPAD(HOUR (NOW()),2,'0'),':00') AND CONCAT(LPAD(HOUR (NOW())+1,2,'0'),':00'))) GROUP BY master_plan_id) e on e.master_plan_id = b.id
                where a.tanggal = CURRENT_DATE())a) a) a) a JOIN (SELECT
        CASE
            WHEN NOW() < CONCAT(CURRENT_DATE, ' 07:00:00') THEN 0
            WHEN NOW() >= CONCAT(CURRENT_DATE, ' 07:00:00') AND NOW() < CONCAT(CURRENT_DATE, ' 12:00:00') THEN
                FLOOR(TIMESTAMPDIFF(MINUTE, CONCAT(CURRENT_DATE, ' 07:00:00'), NOW()) / 60)
            WHEN NOW() >= CONCAT(CURRENT_DATE, ' 13:00:00') THEN
                FLOOR((TIMESTAMPDIFF(MINUTE, CONCAT(CURRENT_DATE, ' 07:00:00'), CONCAT(CURRENT_DATE, ' 12:00:00')) +
                       TIMESTAMPDIFF(MINUTE, CONCAT(CURRENT_DATE, ' 13:00:00'), NOW())) / 60)
            ELSE
                FLOOR(TIMESTAMPDIFF(MINUTE, CONCAT(CURRENT_DATE, ' 07:00:00'), CONCAT(CURRENT_DATE, ' 12:00:00')) / 60)
        END AS jam_berjalan) b",
        );

        return isset($query[0]) ? $query[0]->target : null;
    }

    function cari_target_floordom($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT (target + sisa) target from (select FLOOR(COALESCE(plan_target,0)/COALESCE(jam_kerja,1)) target, MOD(COALESCE(plan_target,0),COALESCE(jam_kerja,1)) sisa from (select * from (select tanggal from dim_date) a left join
        (SELECT tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '" .
                $lineId .
                "') b on b.tgl_plan = a.tanggal where a.tanggal = '" .
                $tanggal .
                "') a) a",
        );
        return isset($query[0]) ? $query[0]->target : null;
    }

    function cari_actual($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) actual from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and a.updated_at >= CURRENT_DATE() AND a.updated_at < CURRENT_DATE() + INTERVAL 1 DAY");

        return isset($query[0]) ? $query[0]->actual : null;
    }

    function cari_current_actual($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT
        COUNT(a.id) AS actual_sekarang
    FROM
        output_rfts a
    INNER JOIN
        master_plan b
        ON b.id = a.master_plan_id
    WHERE
        b.sewing_line = '" .
                $lineId .
                "'
        AND b.tgl_plan = CURRENT_DATE()
        AND (
            (HOUR(NOW()) = 7 AND created_at >= '".date("Y-m-d")." 00:00:00' AND created_at <= '".date("Y-m-d")." 07:59:59')
            OR (HOUR(NOW()) = 13 AND created_at >= '".date("Y-m-d")." 12:00:00' AND created_at <= '".date("Y-m-d")." 13:59:59')
            OR (HOUR(NOW()) != 7 AND HOUR(NOW()) != 13 AND DATE_FORMAT(created_at,'%H:%i') >= CONCAT(LPAD(HOUR(NOW()), 2, '0'), ':00') AND DATE_FORMAT(created_at,'%H:%i') <= CONCAT(LPAD(HOUR(NOW()) + 1, 2, '0'), ':00'))
        )",
        );

        return isset($query[0]) ? $query[0]->actual_sekarang : null;
    }

    function cari_day_target($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT COALESCE(sum(plan_target),0) as day_target from master_plan where tgl_plan = '" . $tanggal . "' and sewing_line = '" . $lineId . "' and cancel = 'N'");

        return isset($query[0]) ? $query[0]->day_target : null;
    }

    function cari_deffect($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) deffect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and DATE_FORMAT(created_at, '%Y-%m-%d') = '" . $tanggal . "'");

        return isset($query[0]) ? $query[0]->deffect : null;
    }

    function cari_output_jam7($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 07:00:00' and '" . $tanggal . " 08:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam8($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 08:00:00' and '" . $tanggal . " 09:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam9($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 09:00:00' and '" . $tanggal . " 10:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam10($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 10:00:00' and '" . $tanggal . " 11:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam11($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 11:00:00' and '" . $tanggal . " 12:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam13($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 13:00:00' and '" . $tanggal . " 14:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam14($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 14:00:00' and '" . $tanggal . " 15:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam15($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 15:00:00' and '" . $tanggal . " 16:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_output_jam16($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 16:00:00' and '" . $tanggal . " 17:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    //deffect
    function cari_deffect_jam7($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 07:00:00' and '" . $tanggal . " 08:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam8($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 08:00:00' and '" . $tanggal . " 09:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam9($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 09:00:00' and '" . $tanggal . " 10:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam10($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 10:00:00' and '" . $tanggal . " 11:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam11($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 11:00:00' and '" . $tanggal . " 12:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam13($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 13:00:00' and '" . $tanggal . " 14:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam14($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 14:00:00' and '" . $tanggal . " 15:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam15($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 15:00:00' and '" . $tanggal . " 16:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_deffect_jam16($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '" . $lineId . "' and (created_at BETWEEN '" . $tanggal . " 16:00:00' and '" . $tanggal . " 17:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

    function cari_rework($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT count(a.id) as rework
                  FROM output_defects a
                  INNER JOIN master_plan b ON b.id = a.master_plan_id
                  WHERE b.sewing_line = :lineId
                  AND DATE_FORMAT(a.created_at, '%Y-%m-%d') = :tanggal
                  AND a.defect_status = 'reworked'",
            [
                'lineId' => $lineId,
                'tanggal' => $tanggal,
            ],
        );

        return isset($query[0]) ? $query[0]->rework : null;
    }

    function listdefect($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT defect_type_id, defect_type, jml
                       FROM (
                         SELECT a.defect_type_id, c.defect_type, COUNT(c.defect_type) AS jml
                         FROM output_defects a
                         INNER JOIN master_plan b ON b.id = a.master_plan_id
                         INNER JOIN output_defect_types c ON c.id = a.defect_type_id
                         WHERE b.sewing_line = ?
                         AND DATE_FORMAT(a.created_at, '%Y-%m-%d') = ?
                         GROUP BY c.defect_type
                       ) a
                       ORDER BY a.jml DESC
                       LIMIT 5",
            [$lineId, $tanggal],
        );

        return $query; // This is an array already
    }

    function cari_link_gambar1($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT gambar AS image
                   FROM master_plan
                   WHERE sewing_line = ?
                   AND DATE_FORMAT(tgl_plan, '%Y-%m-%d') = ?
                   AND cancel = ?
                   GROUP BY gambar
                   ORDER BY id ASC",
            [$lineId, $tanggal, 'N'],
        );
        return $query;
    }

    function cari_positiondefect($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT
                a.defect_area_x,
                a.defect_area_y,
                a.defect_type_id,
                b.gambar AS image
            FROM
                output_defects a
            INNER JOIN
                master_plan b
            ON
                b.id = a.master_plan_id
            WHERE
                b.sewing_line = ? AND
                b.cancel = ? AND
                DATE_FORMAT(a.created_at, '%Y-%m-%d') = ?",
            [$lineId, "N", $tanggal],
        );

        return $query;
    }

    function cari_smv($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT smv from master_plan where tgl_plan = '" . $tanggal . "' and sewing_line = '" . $lineId . "'");

        return isset($query[0]) ? $query[0]->smv : null;
    }

    function cari_menpower($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT man_power from master_plan where tgl_plan = '" . $tanggal . "' and sewing_line = '" . $lineId . "'");

        return isset($query[0]) ? $query[0]->man_power : null;
    }

    function show_chart_dashboard($lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT effi, per_rft, per_defect from (SELECT nama_line,username,COALESCE(effi,0) effi,COALESCE(per_rft,0) per_rft,COALESCE(per_defect,0) per_defect from (select username,SUBSTR(FullName FROM 8) nama_line from userpassword where Groupp = 'sewing' order by username asc
            ) line left join
            (SELECT sewing_line,effi,per_rft,per_defect from (SELECT a.sewing_line,plan_target,actual,rft,deffect,effi,ROUND((COALESCE(rft,0) / (COALESCE(rft,0) + COALESCE(deffect,0)) * 100),2) per_rft, ROUND((COALESCE(deffect,0) / (COALESCE(rft,0) + COALESCE(deffect,0)) * 100),2) per_defect FROM (select sewing_line,min_prod,(man_power * menit_real) min_tersedia, actual,plan_target, ROUND((min_prod / (man_power * menit_real) * 100),2) effi from (Select a.sewing_line,actual,min_prod,man_power,if(menit_real > (jam_kerja * 60),jam_kerja,jam_real) jam_real, if(menit_real > (jam_kerja * 60),(jam_kerja * 60),menit_real) menit_real, jam_kerja,plan_target from (SELECT a.sewing_line,sum(actual) actual,sum(min_prod) min_prod,man_power,jam_real,menit_real FROM (SELECT id,sewing_line,smv,actual,round(smv * actual,4) min_prod FROM (SELECT sewing_line,id,smv from master_plan where tgl_plan = CURRENT_DATE() ) a inner join
            (SELECT master_plan_id, count(a.id) actual from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.tgl_plan = CURRENT_DATE() GROUP BY master_plan_id) b on b.master_plan_id = a.id) a inner join
            (SELECT sewing_line,man_power,jam_masuk,jam_sekarang, if(jam_kerja >= '6',jam_kerja -1,jam_kerja) jam_real, if(jam_kerja >= '6',menit_kerja -60,menit_kerja) menit_real from (SELECT sewing_line, man_power, CONCAT(CURRENT_DATE,' ','07:00:00') jam_masuk,CURRENT_DATE() as jam_sekarang, TIMESTAMPDIFF(hour,CONCAT(CURRENT_DATE,' ','07:00:00'),CURRENT_TIMESTAMP) jam_kerja,TIMESTAMPDIFF(minute,CONCAT(CURRENT_DATE,' ','07:00:00'),CURRENT_TIMESTAMP) menit_kerja from master_plan where tgl_plan = CURRENT_DATE() GROUP BY sewing_line order by sewing_line asc ) a) b on b.sewing_line = a.sewing_line GROUP BY a.sewing_line) a inner join
            (SELECT sewing_line,COALESCE(sum(jam_kerja),0) as jam_kerja,COALESCE(sum(plan_target),0) as plan_target from master_plan where tgl_plan = CURRENT_DATE() GROUP BY sewing_line) b on b.sewing_line = a.sewing_line) a) a left join
            (SELECT b.sewing_line,count(a.id) deffect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.tgl_plan = CURRENT_DATE() GROUP BY b.sewing_line) b on b.sewing_line = a.sewing_line LEFT JOIN
            (select sewing_line,(rft) rft from (select a.sewing_line,COALESCE(rft,0) rft, COALESCE(deffect,0) deffect from (SELECT b.sewing_line,count(a.id) rft from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.tgl_plan = CURRENT_DATE() and status = 'NORMAL' GROUP BY b.sewing_line) a left join
            (SELECT b.sewing_line,- count(a.id) deffect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.tgl_plan = CURRENT_DATE() and a.defect_status = 'defect' GROUP BY b.sewing_line) b on b.sewing_line = a.sewing_line) a) c on c.sewing_line = a.sewing_line) a order by per_rft desc) b on b.sewing_line = line.username order by b.effi desc) a
            where username = '" . $lineId ."'",
        );

        return $query;
    }

    function show_chart_dashboard2($lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT round(min_prod / min_avail * 100,2) effi from (select SUM(min_prod) min_prod, man_power, MAX(updated_at) updated_at, (ABS(TIME_TO_SEC(MAX(updated_at)) -TIME_TO_SEC('07:00:00') - if(HOUR(MAX(updated_at)) >= 12,TIME_TO_SEC('01:00:00'),TIME_TO_SEC('00:00:00'))) / 3600) jam_kerja, ((ABS(TIME_TO_SEC(MAX(updated_at)) -TIME_TO_SEC('07:00:00') - if(HOUR(MAX(updated_at)) >= 12,TIME_TO_SEC('01:00:00'),TIME_TO_SEC('00:00:00'))) / 3600) * man_power * 60) min_avail from (SELECT master_plan_id,count(a.id) actual_sekarang, smv, (count(a.id) * smv) min_prod, MAX(man_power) man_power, MAX(updated_at) updated_at from output_rfts a inner join master_plan b on b.id = a.master_plan_id where  b.sewing_line = '" . $lineId ."' and DATE_FORMAT(a.updated_at,'%Y-%m-%d') = CURRENT_DATE() GROUP BY master_plan_id) a) a",
        );

        return $query;
    }

    function cari_target_floor_menit($lineId)
    {
        $query = DB::connection('mysql_dsb')->select(
            "SELECT COALESCE(plan_target,0)/COALESCE((jam_kerja * 60),1) target from (select sum(plan_target) plan_target, sum(jam_kerja) jam_kerja from (select tanggal from dim_date) a left join
                (SELECT tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where cancel != 'Y' and sewing_line = '" .
                $lineId .
                "') b on b.tgl_plan = a.tanggal where a.tanggal = CURRENT_DATE()) a",
        );

        return isset($query[0]) ? $query[0]->target : null;
    }

    function cari_jamker($lineId)
    {
        $query = DB::connection('mysql_dsb')->select("SELECT COALESCE(sum(jam_kerja),0) as jam_kerja from master_plan where cancel != 'Y' and tgl_plan = CURRENT_DATE() and sewing_line = '" . $lineId . "'");
        return isset($query[0]) ? $query[0]->jam_kerja : null;
    }

    function preChiefSewing() {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        return view('wip.pre-dashboard-chief-sewing', ['page' => 'dashboard-wip', "years" => $years, "months" => $months]);
    }

    function chiefSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-chief-support-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function chiefSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 10 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                line_id
            order by
                line_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function preChiefSewingRange() {
        return view('wip.pre-dashboard-chief-sewing-range', ['page' => 'dashboard-wip']);
    }

    function chiefSewingRange($from = 0, $to = 0) {
        $from = $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $to ? $to : date("Y-m-d");

        return view('wip.dashboard-chief-sewing-range', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'chief-sewing-range', "from" => $from, "to" => $to]);
    }

    function chiefSewingRangeData(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $request->to ? $request->to : date("Y-m-d");

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$to."'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                leader_id,
                chief_id,
                line_id
            order by
                chief_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function chiefSewingRangeDataExport(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $request->to ? $request->to : date("Y-m-d");

        return Excel::download(new ChiefSewingRangeExport($from, $to), 'chief_sewing_range.xlsx');
    }

    function leaderSewing(Request $request, $from = 0, $to = 0) {
        $from = $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $to ? $to : date("Y-m-d");
        $buyerId = $request->buyer_id;

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            leftJoin('master_plan', 'master_plan.id_ws', '=', 'act_costing.id')->
            where('mastersupplier.tipe_sup', 'C')->
            where('master_plan.cancel', 'N')->
            whereRaw('tgl_plan between cast((now() - interval 1 year) as date) AND cast(CURRENT_DATE() as date)')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return view('wip.dashboard-leader-sewing', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'leader-sewing', "from" => $from, "to" => $to, "buyers" => $buyers]);
    }

    function leaderSewingData(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyerId = $request->buyer_id ? $request->buyer_id : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier = '".$buyerId."'" : "";

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$to."'
                                AND master_plan.cancel = 'N'
                                ".$buyerFilter."
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                line_id,
                tanggal
            order by
                line_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function leaderSewingRangeDataExport(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyer = $request->buyer ? $request->buyer : "";

        return Excel::download(new LeaderSewingRangeExport($from, $to, $buyer), 'chief_sewing_range.xlsx');
    }

    function supportLineSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        return view('wip.dashboard-support-line-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function supportLineSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 14 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                technical_id,
                mechanic_id,
                leaderqc_id,
                ie_id,
                chief_id
            order by
                chief_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function factoryDailyPerformance($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        return view('wip.dashboard-factory-daily-sewing', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'factory-daily-sewing', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function factoryDailyPerformanceData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $factoryDailyEfficiency = DB::connection("mysql_sb")->select("
            select
                output.tgl_output as tanggal,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 10 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output
            group by
                tgl_output
            order by
                tgl_output asc
        ");

        return $factoryDailyEfficiency;
    }

    function chiefLeaderSewing(Request $request, $from = 0, $to = 0) {
        $from = $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $to ? $to : date("Y-m-d");
        $buyerId = $request->buyer_id;

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            leftJoin('master_plan', 'master_plan.id_ws', '=', 'act_costing.id')->
            where('mastersupplier.tipe_sup', 'C')->
            where('master_plan.cancel', 'N')->
            whereRaw('tgl_plan between cast((now() - interval 1 year) as date) AND cast(CURRENT_DATE() as date)')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return view('wip.dashboard-chief-leader-sewing', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'leader-sewing', "from" => $from, "to" => $to, "buyers" => $buyers]);
    }

    function chiefLeaderSewingData(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyerId = $request->buyer_id ? $request->buyer_id : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier = '".$buyerId."'" : "";

        $efficiencyLine = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$from." 00:00:00' AND rfts.updated_at <= '".$to." 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 14 DAY) AND master_plan.tgl_plan <= '".$to."'
                                AND master_plan.cancel = 'N'
                                ".$buyerFilter."
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                tanggal,
                leader_id,
                chief_id,
                line_id
            order by
                chief_id asc,
                tanggal asc
        ");

        return $efficiencyLine;
    }

    function chiefLeaderSewingRangeDataExport(Request $request) {
        $from = $request->from ? $request->from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days"));
        $to = $request->to ? $request->to : date("Y-m-d");
        $buyer = $request->buyer ? $request->buyer : "";

        return Excel::download(new LeaderSewingRangeExport($from, $to, $buyer), 'chief_sewing_range.xlsx');
    }

    function topChiefSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-top-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function topChiefSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyChief = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) efficiency,
                (SUM(rft)/SUM(output)*100) rft,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) + (SUM(rft)/SUM(output)*100) rft_efficiency
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 14 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                chief_nik
            order by
                rft_efficiency desc
        ");

        return $efficiencyChief;
    }

    function topLeaderSewing($year = 0, $month = 0) {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];

        $yearVar = $year ? $year : date("Y");
        $monthVar = $month ? $month : date("m");

        // return view('wip.dashboard-chief-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
        return view('wip.dashboard-top-leader-sewing', ['page' => 'dashboard-wip', "year" => $yearVar, "month" => $monthVar, "monthName" => $months[num($monthVar)-1]["nama"], "months" => $months]);
    }

    function topLeaderSewingData(Request $request) {
        $month = $request->month ? $request->month : date("m");
        $year = $request->year ? $request->year : date("Y");

        $efficiencyLeader = DB::connection("mysql_sb")->select("
            select
                output_employee_line.*,
                output.sewing_line,
                SUM(rft) rft,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail,
                SUM(cumulative_mins_avail) cumulative_mins_avail,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) efficiency,
                (SUM(rft)/SUM(output)*100) rft,
                (SUM(mins_prod)/SUM(cumulative_mins_avail)*100) + (SUM(rft)/SUM(output)*100) rft_efficiency
            from
                output_employee_line
                left join userpassword on userpassword.line_id = output_employee_line.line_id
                inner join (
                    SELECT
                        output.tgl_output,
                        output.tgl_plan,
                        output.sewing_line,
                        SUM(rft) rft,
                        SUM(output) output,
                        SUM(output * output.smv) mins_prod,
                        SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                        MAX(output.last_update) last_update,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                        (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                        MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                        FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                    FROM
                        (
                            SELECT
                                DATE( rfts.updated_at ) tgl_output,
                                COUNT( rfts.id ) output,
                                SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                MAX(rfts.updated_at) last_update,
                                master_plan.id master_plan_id,
                                master_plan.tgl_plan,
                                master_plan.sewing_line,
                                master_plan.man_power,
                                master_plan.jam_kerja,
                                master_plan.smv
                            FROM
                                output_rfts rfts
                                inner join master_plan on master_plan.id = rfts.master_plan_id
                                inner join act_costing on act_costing.id = master_plan.id_ws
                                inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                            where
                                rfts.updated_at >= '".$year."-".$month."-01 00:00:00' AND rfts.updated_at <= '".$year."-".$month."-31 23:59:59'
                                AND master_plan.tgl_plan >= DATE_SUB('".$year."-".$month."-01', INTERVAL 14 DAY) AND master_plan.tgl_plan <= '".$year."-".$month."-31'
                                AND master_plan.cancel = 'N'
                            GROUP BY
                                master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                            order by
                                sewing_line
                        ) output
                    GROUP BY
                        output.sewing_line,
                        output.tgl_output
                ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
            group by
                leader_nik
            order by
                rft_efficiency desc
        ");

        return $efficiencyLeader;
    }
}
