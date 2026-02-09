<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class TransferOutputController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $orderSql = DB::connection('mysql_sb')->
                table('act_costing')->
                selectRaw('
                    id as id_ws,
                    kpno as no_ws
                ')->
                where('status', '!=', 'CANCEL')->
                where('cost_date', '>=', '2023-01-01')->
                where('type_ws', 'STD');
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

        return view('sewing.transfer-output', [
            "page" => "dashboard-sewing-eff"
        ]);
    }
}
