<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use DB;

class UndoOutputController extends Controller
{
    public function history(Request $request) {
        if ($request->ajax()) {
            $dateFrom = $request->tanggal_awal ? $request->tanggal_awal : date('Y-m-d');
            $dateTo = $request->tanggal_akhir ? $request->tanggal_akhir : date('Y-m-d');

            $data = DB::connection("mysql_sb")->
                table("output_undo")->
                selectRaw("
                    master_plan.tgl_plan,
                    COALESCE(userpassword.username, master_plan.sewing_line) line,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    output_undo.keterangan,
                    COUNT(output_undo.id) total_undo,
                    COALESCE(output_undo.updated_at, output_undo.created_at) updated_at
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_undo.master_plan_id")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_undo.master_plan_id")->
                leftJoin("so_det", "so_det.id", "=", "output_undo.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                whereBetween("output_undo.updated_at", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->
                groupBy("output_undo.updated_at", "output_undo.keterangan")->
                orderBy("output_undo.updated_at", "DESC")->
                get();

            return DataTables::of($data)->toJson();
        }

        return view("sewing.undo.undo-output-history", ["subPageGroup" => "sewing-sewing", "subPage" => "undo-output-history", "page" => "dashboard-sewing-eff"]);
    }
}
