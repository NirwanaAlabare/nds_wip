<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\MasterPiping;
use App\Models\Cutting\PipingProcess;
use App\Models\Cutting\PipingStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class PipingStockController extends Controller
{
    function index(Request $request) {
        if ($request->ajax()) {
            $dateFrom = $request->from ? $request->from : date('Y-m-d');
            $dateTo = $request->to ? $request->to : date('Y-m-d');

            $pipingStock = PipingProcess::selectRaw("
                master_piping.buyer,
                master_piping.act_costing_ws,
                master_piping.style,
                piping_process.color,
                master_piping.part,
                CONCAT(SUM(piping_process.output_total_roll), ' ', GROUP_CONCAT(DISTINCT piping_process.output_total_roll_unit)) output_total_roll,
                CONCAT(SUM(piping_process.output_total_roll * piping_process.estimasi_output_roll), ' ', GROUP_CONCAT(DISTINCT piping_process.estimasi_output_roll_unit)) estimasi_output_total
            ")->
            leftJoin("master_piping", "master_piping.id", "=", "piping_process.master_piping_id")->
            whereBetween("piping_process.updated_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
            groupBy("master_piping.act_costing_ws", "piping_process.color", "master_piping.part")->
            get();

            return DataTables::of($pipingStock)->toJson();
        }

        return view("cutting.piping-stock.piping-stock", ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piping", "subPage" => "piping-stock"]);
    }

    function total(Request $request) {
        $dateFrom = $request->dateFrom ? $request->dateFrom : "";
        $dateTo = $request->dateTo ? $request->dateTo : "";
        $buyer = $request->buyer ? $request->buyer : "";
        $act_costing_ws = $request->act_costing_ws ? $request->act_costing_ws : "";
        $style = $request->style ? $request->style : "";
        $color = $request->color ? $request->color : "";
        $part = $request->part ? $request->part : "";
        $roll = $request->roll ? $request->roll : "";
        $output = $request->output ? $request->output : "";

        $pipingStock = DB::select("
                SELECT
                    master_piping.buyer,
                    master_piping.act_costing_ws,
                    master_piping.style,
                    piping_process.color,
                    master_piping.part,
                    SUM(piping_process.output_total_roll) AS output_total_roll,
                    GROUP_CONCAT(DISTINCT piping_process.output_total_roll_unit) AS output_total_roll_unit,
                    SUM(piping_process.output_total_roll * piping_process.estimasi_output_roll) AS estimasi_output_total,
                    GROUP_CONCAT(DISTINCT piping_process.estimasi_output_roll_unit) AS estimasi_output_total_unit
                FROM
                    piping_process
                LEFT JOIN master_piping
                    ON master_piping.id = piping_process.master_piping_id
                WHERE
                    piping_process.updated_at BETWEEN '".$dateFrom." 00:00:00' AND '".$dateTo." 23:59:59'
                    AND master_piping.buyer LIKE '%".$buyer."%'
                    AND master_piping.act_costing_ws LIKE '%".$act_costing_ws."%'
                    AND master_piping.style LIKE '%".$style."%'
                    AND master_piping.color LIKE '%".$color."%'
                    AND master_piping.part LIKE '%".$part."%'
                GROUP BY
                    master_piping.act_costing_ws, piping_process.color, master_piping.part
                HAVING
                    CONCAT(SUM(piping_process.output_total_roll), ' ', GROUP_CONCAT(DISTINCT piping_process.output_total_roll_unit)) LIKE '%".$roll."%'
                    AND CONCAT(SUM(piping_process.output_total_roll * piping_process.estimasi_output_roll), ' ', GROUP_CONCAT(DISTINCT piping_process.estimasi_output_roll_unit)) LIKE '%".$output."%'
            ");

        return $pipingStock ? ($pipingStock[0] ? $pipingStock[0] : null) : null;
    }

    function show($id = 0) {
        if ($id) {
            $piping = PipingProcess::where("act_costing_ws", $id);
        }
    }
}
