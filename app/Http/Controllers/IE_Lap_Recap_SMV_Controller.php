<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\export_excel_laporan_daily_cost;

class IE_Lap_Recap_SMV_Controller extends Controller
{
    public function IE_lap_recap_smv(Request $request)
    {
        $user = Auth::user()->name;
        $rawData = DB::connection('mysql_sb')->select("SELECT ac.kpno, ac.styleno, ms.supplier as buyer, id_ws, smv, tgl_plan, DATE_FORMAT(tgl_plan, '%d-%m-%Y') AS tgl_plan_fix
        from master_plan mp
		inner join act_costing ac on mp.id_ws = ac.id
		inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
        where id_ws is not null and tgl_plan is not null
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


    public function export_excel_laporan_daily_cost(Request $request)
    {
        return Excel::download(new export_excel_laporan_daily_cost($request->bulan, $request->tahun), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
}
