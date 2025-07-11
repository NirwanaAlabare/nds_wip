<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectDashboardController extends Controller
{
    public function dashboard_qc_inspect(Request $request)
    {
        $bln = Carbon::now()->format('Ymd');
        $tgl_filter = $request->dateFilter;
        $thn = date('Y');
        $user = Auth::user()->name;

        $data_bulan = DB::select("SELECT bulan isi, nama_bulan tampil FROM `dim_date` where tahun = '2025'
GROUP BY bulan
order by cast(bulan as UNSIGNED) asc");

        $data_tahun = DB::connection('mysql_sb')->select("SELECT DISTINCT
    YEAR(so_date) AS isi,
    YEAR(so_date) AS tampil
FROM so_det sd
INNER JOIN so ON sd.id_so = so.id
WHERE so.cancel_h = 'N'
  AND sd.cancel = 'N'
  AND YEAR(so_date) >= 2023
ORDER BY isi");


        return view('qc_inspect.dashboard_qc_inspect', [
            'page' => 'dashboard-qc-inspect',
            "data_bulan" => $data_bulan,
            "data_tahun" => $data_tahun,
            "thn" => $thn
        ]);
    }

    public function get_data_dash_marketing(Request $request)
    {
        $tahun = $request->tahun;
        $data_order = DB::connection('mysql_sb')->select("SELECT
a.bulan,
nama_bulan as x,
coalesce(y,0) as y
from
(
select bulan, nama_bulan from signalbit_erp.dim_date where tahun = '$tahun'
group by nama_bulan
ORDER BY CAST(bulan AS UNSIGNED) ASC
) a
left join
(
SELECT
    MONTHNAME(deldate) AS x,
    SUM(sd.qty) AS y,
		month(deldate) as bulan
FROM so_det sd
INNER JOIN so ON sd.id_so = so.id
INNER JOIN act_costing ac on so.id_cost = ac.id
WHERE so.cancel_h = 'N' AND sd.cancel = 'N' and ac.aktif = 'Y' and ac.app1 = 'A' and year(deldate) >= '$tahun'
GROUP BY MONTH(deldate)
ORDER BY MONTH(deldate)
) b on a.bulan = b.bulan
");

        return json_encode($data_order);
    }

    public function get_data_dash_marketing_top_buyer(Request $request)
    {
        $tahun = $request->tahun;
        $data_top_buyer = DB::connection('mysql_sb')->select("SELECT
ms.Supplier,
SUM(sd.qty) qty_order
from so_det sd
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
WHERE so.cancel_h = 'N' AND sd.cancel = 'N' and ac.aktif = 'Y' and ac.app1 = 'A' and year(ac.deldate) = '$tahun'
GROUP BY ms.Supplier
order by SUM(sd.qty) desc
LIMIT 5
");

        return json_encode($data_top_buyer);
    }
}
