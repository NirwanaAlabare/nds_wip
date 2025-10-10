<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\Packing\PackingOutputExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class PackingLineController extends Controller
{
    public function trackPackingOutput(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == "supplier") {
                $suppliersQuery = DB::connection('mysql_sb')->table('mastersupplier')->
                    selectRaw('Id_Supplier as id, Supplier as name')->
                    leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
                    where('mastersupplier.tipe_sup', 'C')->
                    where('status', '!=', 'CANCEL')->
                    where('type_ws', 'STD')->
                    where('cost_date', '>=', '2023-01-01');
                $suppliers = $suppliersQuery->
                    orderBy('Supplier', 'ASC')->
                    groupBy('Id_Supplier', 'Supplier')->
                    get();

                return $suppliers;
            }

            if ($request->type == "order") {
                $orderSql = DB::connection('mysql_sb')->
                    table('act_costing')->
                    selectRaw('
                        id as id_ws,
                        kpno as no_ws
                    ')->
                    where('status', '!=', 'CANCEL')->
                    where('type_ws', 'STD')->
                    where('cost_date', '>=', '2023-01-01');
                if ($request->supplier) {
                    $orderSql->where('id_buyer', $request->supplier);
                }
                $orders = $orderSql->
                    orderBy('cost_date', 'desc')->
                    orderBy('kpno', 'asc')->
                    groupBy('kpno')->
                    get();

                return $orders;
            }
        }

        return view('packing.track-packing-output', [
            "subPageGroup" => "packing-line", "subPage" => "track-packing-output", "page" => "dashboard-packing"
        ]);
    }

    public function exportPackingOutput (Request $request) {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $groupBy = $request->groupBy;
        $order = $request->order;
        $buyer = $request->buyer;

        return Excel::download(new PackingOutputExport($dateFrom, $dateTo, $groupBy, $order, $buyer), 'order_output.xlsx');
    }
}
