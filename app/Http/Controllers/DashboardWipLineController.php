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
        $data['user'] = 'Line 01 | End Line';
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

        broadcast(new TriggerWipLine($data, $lineId, $tanggal));
        return response()->json([
            'message' => 'Data diterima',
            'data' => [
                'tanggal' => $tanggal,
                'line_id' => $lineId,
                'data' => $data,
            ],
        ], 200);
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
        ->select("SELECT count(a.id) actual from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' ");

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
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '07:00' and '08:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam8($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '08:00' and '09:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam9($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '09:00' and '10:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam10($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '10:00' and '11:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam11($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '11:00' and '12:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam13($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '13:00' and '14:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam14($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '14:00' and '15:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam15($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '15:00' and '16:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     function cari_output_jam16($tanggal, $lineId)
    {
        $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_rfts a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '16:00' and '17:00'");

        return isset($query[0]) ? $query[0]->jumlah : null;
    }

     //deffect
     function cari_deffect_jam7($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '07:00' and '08:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam8($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '08:00' and '09:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam9($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '09:00' and '10:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam10($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '10:00' and '11:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam11($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '11:00' and '12:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam13($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '13:00' and '14:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam14($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '14:00' and '15:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam15($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '15:00' and '16:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
 
      function cari_deffect_jam16($tanggal, $lineId)
     {
         $query = DB::connection('mysql_dsb')
        ->select("SELECT count(a.id) jumlah from output_defects a inner join master_plan b on b.id = a.master_plan_id where b.sewing_line = '".$lineId."' and DATE_FORMAT(created_at, '%Y-%m-%d') = '".$tanggal."' and DATE_FORMAT(created_at, '%H:%i') BETWEEN '16:00' and '17:00'");

         return isset($query[0]) ? $query[0]->jumlah : null;
     }
}
