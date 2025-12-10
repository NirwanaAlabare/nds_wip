<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class IE_Laporan_Controller extends Controller
{
    public function IE_lap_recap_smv(Request $request)
    {
        $user = Auth::user()->name;
        $rawData = DB::connection('mysql_sb')->select("SELECT ac.kpno, ac.styleno, ms.supplier as buyer, id_ws, smv, tgl_plan, DATE_FORMAT(tgl_plan, '%d-%m-%Y') AS tgl_plan_fix
        from master_plan mp
		inner join act_costing ac on mp.id_ws = ac.id
		inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
        where id_ws is not null and tgl_plan is not null and mp.cancel = 'N'
        group by id_ws, smv
        order by ms.supplier asc, ac.styleno asc, tgl_plan asc
        ");

        $groupedData = [];

        foreach ($rawData as $row) {
            $ws = $row->id_ws;

            if (!isset($groupedData[$ws])) {
                $groupedData[$ws] = [
                    'id_ws' => $ws,
                    'kpno' => $row->kpno,
                    'styleno' => $row->styleno,
                    'buyer' => $row->buyer,
                    'details' => []
                ];
            }

            $groupedData[$ws]['details'][] = [
                'tgl_plan_fix' => $row->tgl_plan_fix,
                'smv' => $row->smv
            ];
        }

        // Hitung total perubahan untuk semua WS
        foreach ($groupedData as &$wsData) {
            $wsData['total_changes'] = count($wsData['details']);
        }
        unset($wsData);

        // Kalau mau, reset key supaya 0,1,2,... untuk foreach di Blade
        $groupedData = array_values($groupedData);


        // For non-AJAX (initial page load)
        return view('IE.laporan_recap_smv', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-laporan',
            'subPage' => 'IE-laporan-recap-smv',
            'groupedData' => $groupedData,
            'containerFluid' => true,
            'user' => $user,
        ]);
    }

    public function IE_lap_recap_cm_price(Request $request)
    {
        $user = Auth::user()->name;
        $rawData = DB::connection('mysql_sb')->select("WITH mp as (
select id_ws from master_plan	mp
where cancel = 'N' and id_ws is not null and tgl_plan is not null
group by id_ws
),
mfg as (
select * from act_costing_mfg where id_item = '8'
),
a as (
select mp.id_ws, price, jenis_rate  from mp
inner join mfg on mp.id_ws = mfg.id_act_cost
),
mr as (
select rate, tanggal from masterrate where v_codecurr = 'HARIAN'
),
mr_n as (
select max(rate) max_rate from masterrate where v_codecurr = 'HARIAN'
)

select
a.id_ws,
ac.kpno,
ac.styleno,
ms.supplier as buyer,
a.price,
a.jenis_rate,
ac.deldate,
mr.rate,
max_rate,
b.created_at,
DATE_FORMAT(b.created_at, '%d-%m-%Y %H:%i:%s') AS tgl_upd_fix,
case
		when a.jenis_rate = 'J' and mr.rate is not null  then round(a.price * mr.rate,2)
		when a.jenis_rate = 'J' and mr.rate is null then round(a.price * max_rate,2)
		when a.jenis_rate = 'B' then round(a.price,2)
		end as price_act,
b.price as price_upd,
b.jenis_rate as jns_rate_upd,
case
		when b.jenis_rate = 'J' and mr.rate is not null  then round(b.price * mr.rate,2)
		when b.jenis_rate = 'J' and mr.rate is null then round(b.price * max_rate,2)
		when b.jenis_rate = 'B' then round(b.price,2)
		end as price_act_upd
from a
left join act_costing_mfg_log b on a.id_ws = b.id_act_cost
inner join act_costing ac on a.id_ws = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
LEFT JOIN mr on ac.deldate = mr.tanggal
CROSS JOIN mr_n
order by ms.supplier asc, ac.styleno asc, b.created_at asc
        ");

        $groupedData = [];

        foreach ($rawData as $row) {
            $ws = $row->id_ws;

            if (!isset($groupedData[$ws])) {
                $groupedData[$ws] = [
                    'id_ws' => $ws,
                    'kpno' => $row->kpno,
                    'styleno' => $row->styleno,
                    'buyer' => $row->buyer,
                    'price_act' => $row->price_act,
                    'details' => []
                ];
            }

            $groupedData[$ws]['details'][] = [
                'tgl_upd_fix' => $row->tgl_upd_fix,
                'price_act_upd' => $row->price_act_upd
            ];
        }

        foreach ($groupedData as &$wsData) {
            // total perubahan adalah jumlah log (b) yang memiliki price_upd / created_at
            $wsData['total_changes'] = collect($wsData['details'])
                ->filter(function ($d) {
                    return !empty($d['price_act_upd']); // atau pakai created_at
                })
                ->count();
        }
        unset($wsData);


        // Kalau mau, reset key supaya 0,1,2,... untuk foreach di Blade
        $groupedData = array_values($groupedData);


        // For non-AJAX (initial page load)
        return view('IE.laporan_recap_cm_price', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-laporan',
            'subPage' => 'IE-laporan-recap-cm-price',
            'groupedData' => $groupedData,
            'containerFluid' => true,
            'user' => $user,
        ]);
    }
}
