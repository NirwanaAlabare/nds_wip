<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class IE_Output_perfomance_Controller extends Controller
{
    public function IE_output_performance(Request $request)
    {
        $user = Auth::user()->name;
        $data = DB::connection('mysql_sb')->select("SELECT master_plan_id, up.username,date(a.updated_at) tgl_trans, count(*) tot_rfts, man_power, smv, color
from output_rfts a
left join master_plan mp on a.master_plan_id = mp.id
inner join act_costing ac on mp.id_ws = ac.id
left join user_sb_wip u on a.created_by = u.id
left join userpassword up on up.line_id = u.line_id
where  mp.cancel = 'N' and styleno = ?
group by master_plan_id, up.username, date(a.created_at)
ORDER BY COUNT(*) DESC
LIMIT 1;
        ", [$request->input('styleno')]);

        // Data untuk chart perbandingan output & efficiency per line (semua line, tidak dibatasi LIMIT 1)
        $chartData = DB::connection('mysql_sb')->select("SELECT master_plan_id, up.username,date(a.updated_at) tgl_trans, count(*) tot_rfts, man_power, smv, color
from output_rfts a
left join master_plan mp on a.master_plan_id = mp.id
inner join act_costing ac on mp.id_ws = ac.id
left join user_sb_wip u on a.created_by = u.id
left join userpassword up on up.line_id = u.line_id
where  mp.cancel = 'N' and styleno = ?
group by master_plan_id, up.username, date(a.created_at)
ORDER BY COUNT(*) DESC
        ", [$request->input('styleno')]);


        // For non-AJAX (initial page load)
        return view('IE.output_perfomance', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-laporan',
            'subPage' => 'IE-laporan-output-performance',
            'containerFluid' => true,
            'user' => $user,
            'data' => $data,
            'chartData' => $chartData,
            'styleno' => $request->input('styleno'),
        ]);
    }
}
