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
use Yajra\DataTables\Facades\DataTables;
use App\Events\TriggerWipLine;
use DB;

class DashboardWipLineController extends Controller
{
    public function track(Request $request) {
        return Redirect::to('/home');
    }

    public function index(Request $request) {
        ini_set("max_execution_time", 0);
        ini_set("memory_limit", '2048M');

        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        $query = DB::connection('mysql_dsb')->select("SELECT COALESCE(sum(jam_kerja),0) as jam_kerja from master_plan where tgl_plan = CURRENT_DATE() and sewing_line = 'line_01'");
        $lines = [];
            for ($i = 1; $i <= 30; $i++) {
                $lines[] = (object)[
                    'id' => 'line_' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'name' => 'Line ' . str_pad($i, 2, '0', STR_PAD_LEFT),
                ];
            }

        return view('wip/dashboard-wip', ['page' => 'dashboard-wip','lines' => $lines]);
    }

    public function show_wip_line($id)
    {
        return view('wip.wip-line', [
            'id' => $id
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
        $data['jamkerl1'] = $this->cari_jamker($tanggal, $lineId);
        $data['actuall1'] = $this->cari_actual($tanggal, $lineId);
        $data['day_target1'] = $this->cari_day_target($tanggal, $lineId);
        $data['deffectl1'] = $this->cari_deffect($tanggal, $lineId);

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


        $data['datajam7'] = $this->cari_datajam7($lineId);
        $data['datajam8'] = $this->cari_datajam8($lineId);
        $data['datajam9'] = $this->cari_datajam9($lineId);
        $data['datajam10'] = $this->cari_datajam10($lineId);
        $data['datajam11'] = $this->cari_datajam11($lineId);
        $data['datajam13'] = $this->cari_datajam13($lineId);
        $data['datajam14'] = $this->cari_datajam14($lineId);
        $data['datajam15'] = $this->cari_datajam15($lineId);

        broadcast(new TriggerWipLine($data, $lineId));
        return response()->json([
            'message' => 'Data diterima',
            'data' => [
                'tanggal' => $tanggal,
                'line_id' => $lineId,
                'data' => $data,
            ],
        ], 200);
    }


    function cari_datajam7($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 0)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 0)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '07:00' and DATE_FORMAT(created_at, '%H:%i') <= '08:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '07:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '07:00' and DATE_FORMAT(created_at, '%H:%i') <= '08:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam8($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 1)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 1)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '08:00' and DATE_FORMAT(created_at, '%H:%i') <= '09:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '08:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '08:00' and DATE_FORMAT(created_at, '%H:%i') <= '09:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam9($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 2)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 2)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '09:00' and DATE_FORMAT(created_at, '%H:%i') <= '10:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '09:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '09:00' and DATE_FORMAT(created_at, '%H:%i') <= '10:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam10($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 3)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 3)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '10:00' and DATE_FORMAT(created_at, '%H:%i') <= '11:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '10:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '10:00' and DATE_FORMAT(created_at, '%H:%i') <= '11:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam11($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 4)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 4)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '11:00' and DATE_FORMAT(created_at, '%H:%i') <= '12:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '11:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '11:00' and DATE_FORMAT(created_at, '%H:%i') <= '12:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam13($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 5)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 5)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '12:00' and DATE_FORMAT(created_at, '%H:%i') <= '14:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '12:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '12:00' and DATE_FORMAT(created_at, '%H:%i') <= '14:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam14($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 6)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 6)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '14:00' and DATE_FORMAT(created_at, '%H:%i') <= '15:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '14:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '14:00' and DATE_FORMAT(created_at, '%H:%i') <= '15:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function cari_datajam15($line)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT jam_kerja,target target1, actual_sekarang output, (target - actual_sekarang) variation1,round(actual_sekarang / target * 100,2) efficiency1,round(defect / target * 100,2) defect_rate1, (target + sisa) target2, ((target + sisa) - actual_sekarang) variation2,round(actual_sekarang / (target + sisa) * 100,2) efficiency2,round(defect / (target + sisa) * 100,2) defect_rate2 from(SELECT plan_target,MOD((plan_target - actual_sebelum),(jam_kerja - 7)) sisa,actual_sekarang,actual_sebelum,jam_kerja,defect,FLOOR((plan_target - actual_sebelum)/(jam_kerja - 7)) target from (
            select COALESCE(sum(plan_target),0) plan_target, COALESCE(sum(jam_kerja),0) jam_kerja, COALESCE(sum(actual_sebelum),0) actual_sebelum,COALESCE(sum(actual_sekarang),0) actual_sekarang, COALESCE(sum(defect),0) defect from (select tanggal from dim_date) a left join
            (SELECT id,tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$line."') b on b.tgl_plan = a.tanggal left join
            (SELECT master_plan_id,count(a.id) actual_sekarang from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '15:00' and DATE_FORMAT(created_at, '%H:%i') <= '16:00') c on c.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) actual_sebelum from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') < '15:00') d on d.master_plan_id = b.id
            left join
            (SELECT master_plan_id,count(a.id) defect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$line."' and b.tgl_plan = CURRENT_DATE() and DATE_FORMAT(created_at, '%H:%i') >= '15:00' and DATE_FORMAT(created_at, '%H:%i') <= '16:00') e on e.master_plan_id = b.id
            where a.tanggal = CURRENT_DATE())a) a");
        return $query;
    }

    function getbuyer($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT GROUP_CONCAT(Supplier) buyer from (select mp.id_ws,ms.Supplier from master_plan mp
        inner join act_costing ac on mp.id_ws = ac.id
        inner join mastersupplier ms on ms.Id_Supplier = ac.id_buyer
        where mp.tgl_plan = '".$tanggal."' and mp.sewing_line = '".$lineId."') a");
        return isset($query[0]) ? $query[0]->buyer : null;
    }

    function getno_ws($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT GROUP_CONCAT(kpno) no_ws from (select mp.id_ws,ac.kpno from master_plan mp
        inner join act_costing ac on mp.id_ws = ac.id
        where mp.tgl_plan = '".$tanggal."' and mp.sewing_line = '".$lineId."') a ");

        return isset($query[0]) ? $query[0]->no_ws : null;
    }

    function cari_target_floor($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT FLOOR(COALESCE(plan_target,0)/COALESCE(jam_kerja,1)) target from (select * from (select tanggal from dim_date) a left join
        (SELECT tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$lineId."') b on b.tgl_plan = a.tanggal where a.tanggal = '".$tanggal."') a");

        return isset($query[0]) ? $query[0]->target : null;
    }

    function cari_target_floordom($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT (target + sisa) target from (select FLOOR(COALESCE(plan_target,0)/COALESCE(jam_kerja,1)) target, MOD(COALESCE(plan_target,0),COALESCE(jam_kerja,1)) sisa from (select * from (select tanggal from dim_date) a left join
        (SELECT tgl_plan,sewing_line,plan_target,jam_kerja from master_plan where sewing_line = '".$lineId."') b on b.tgl_plan = a.tanggal where a.tanggal = '".$tanggal."') a) a");
        return isset($query[0]) ? $query[0]->target : null;
    }

    function cari_jamker($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT COALESCE(sum(jam_kerja),0) as jam_kerja from master_plan where tgl_plan = '".$tanggal."' and sewing_line = '".$lineId."'");

        return isset($query[0]) ? $query[0]->jam_kerja : null;
    }

    function cari_actual($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) actual from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and b.tgl_plan = '".$tanggal."' ");

        return isset($query[0]) ? $query[0]->actual : null;
    }

    function cari_day_target($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT COALESCE(sum(plan_target),0) as day_target from master_plan where tgl_plan = '".$tanggal."' and sewing_line = '".$lineId."'");

        return isset($query[0]) ? $query[0]->day_target : null;
    }

    function cari_deffect($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) deffect from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."'");

        return isset($query[0]) ? $query[0]->deffect : null;
    }

    function cari_output_jam7($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 07:00:00' and '".$tanggal." 08:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam8($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 08:00:00' and '".$tanggal." 09:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam9($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 09:00:00' and '".$tanggal." 10:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam10($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 10:00:00' and '".$tanggal." 11:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam11($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 11:00:00' and '".$tanggal." 12:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam13($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 13:00:00' and '".$tanggal." 14:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam14($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 14:00:00' and '".$tanggal." 15:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam15($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 15:00:00' and '".$tanggal." 16:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam16($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 16:00:00' and '".$tanggal." 17:00:00')");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     //deffect
     function cari_deffect_jam7($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 07:00:00' and '".$tanggal." 08:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam8($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 08:00:00' and '".$tanggal." 09:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam9($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 09:00:00' and '".$tanggal." 10:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam10($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 10:00:00' and '".$tanggal." 11:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam11($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 11:00:00' and '".$tanggal." 12:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam13($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 13:00:00' and '".$tanggal." 14:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam14($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 14:00:00' and '".$tanggal." 15:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam15($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 15:00:00' and '".$tanggal." 16:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

      function cari_deffect_jam16($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and (created_at BETWEEN '".$tanggal." 16:00:00' and '".$tanggal." 17:00:00')");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }

     function cari_rework($tanggal, $lineId)
     {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) as rework
                  FROM output_defects a
                  INNER JOIN master_plan b ON b.id = a.master_plan_id
                  WHERE b.sewing_line = :lineId
                  AND DATE_FORMAT(a.created_at, '%Y-%m-%d') = :tanggal
                  AND a.defect_status = 'reworked'", [
            'lineId' => $lineId,
            'tanggal' => $tanggal,
        ]);

        return isset($query[0]) ? $query[0]->rework : null;
     }

     function listdefect($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
             ->select("SELECT defect_type_id, defect_type, jml
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
                       LIMIT 5", [$lineId, $tanggal]);

         return $query; // This is an array already
     }

     function cari_link_gambar1($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
         ->select("SELECT gambar AS image
                   FROM master_plan
                   WHERE sewing_line = ?
                   AND DATE_FORMAT(tgl_plan, '%Y-%m-%d') = ?
                   GROUP BY gambar
                   ORDER BY id ASC
                   LIMIT 1", [$lineId, $tanggal]);
         return $query;
     }


     function cari_positiondefect($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
             ->select("SELECT
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
                           b.sewing_line = ?
                       AND
                           DATE_FORMAT(a.created_at, '%Y-%m-%d') = ?", [$lineId, $tanggal]);

         return $query;
     }

     function cari_smv($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT smv from master_plan where tgl_plan = '".$tanggal."' and sewing_line = '".$lineId."'");

        return isset($query[0]) ? $query[0]->smv : null;
    }

    function cari_menpower($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT man_power from master_plan where tgl_plan = '".$tanggal."' and sewing_line = '".$lineId."'");

        return isset($query[0]) ? $query[0]->man_power : null;
    }


}
