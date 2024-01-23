<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoadingLinePlan;
use App\Models\SignalBit\UserLine;
use Yajra\DataTables\Facades\DataTables;
use DB;

class LoadingLineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $line = LoadingLinePlan::selectRaw("
                loading_line_plan.id,
                loading_line_plan.line_id,
                loading_line_plan.act_costing_ws,
                loading_line_plan.style,
                COALESCE(loading_line_plan.target_sewing, 0) target_sewing,
                loading_line_plan.color,
                COALESCE(loading_line_plan.target_loading, 0) target_loading,
                COALESCE(SUM(loading_line.qty), 0) loading_qty,
                COALESCE(SUM(loading_line.qty) - loading_line_plan.target_loading, 0) balance_loading,
                COALESCE(trolley.nama_trolley, '-') nama_trolley,
                COALESCE(trolley_qty.trolley_qty, 0) stock_trolley,
                COALESCE(GROUP_CONCAT(DISTINCT stocker_input.color), '-') trolley_color
            ")->
            leftJoin("loading_line", "loading_line.loading_plan_id", "=", "loading_line_plan.id")->
            leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "loading_line.stocker_id")->
            leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
            leftJoin("stocker_input", "stocker_input.id", "loading_line.stocker_id")->
            leftJoin(DB::raw("(select trolley_stocker.trolley_id, stocker_input.act_costing_ws, stocker_input.color , SUM(stocker_input.qty_ply) trolley_qty from trolley_stocker left join stocker_input on stocker_input.id = trolley_stocker.stocker_id where trolley_stocker.status = 'active' group by trolley_stocker.trolley_id, stocker_input.act_costing_ws, stocker_input.color) trolley_qty"), function ($join) {
                $join->on("trolley_qty.trolley_id", '=', "trolley.id");
                $join->on("trolley_qty.act_costing_ws", '=', "loading_line_plan.act_costing_ws");
            })->
            groupBy("loading_line_plan.id");

            return DataTables::eloquent($line)
                ->filter(function ($query) {
                    if (request()->has('dateFrom') && request('dateFrom') != null && request('dateFrom') != "") {
                        $query->where("loading_line_plan.tanggal", ">=", request('dateFrom'));
                    }

                    if (request()->has('dateTo') && request('dateTo') != null && request('dateTo') != "") {
                        $query->where("loading_line_plan.tanggal", "<=", request('dateTo'));
                    }
                })
                ->addColumn('nama_line', function ($row) {
                    $lineData = UserLine::where('line_id', $row->line_id)->first();
                    $line = $lineData ? strtoupper(str_replace("_", " ", $lineData->username)) : "";

                    return $line;
                })
                ->toJson();
        }

        return view("trolley.loading-line.loading-line", ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'loading-line']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();
        $lines = UserLine::where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("trolley.loading-line.create-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'loading-line', 'lines' => $lines, 'orders' => $orders]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
        $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
        $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

        $validatedRequest = $request->validate([
            "tanggal" => "required",
            "line_id" => "required",
            "ws_id" => "required",
            "ws" => "required",
            "buyer" => "required",
            "style" => "required",
            "color" => "required",
            "target_sewing" => "required",
            "target_loading" => "required",
        ]);

        $storeLoadingPlan = LoadingLinePlan::create([
            "line_id" => $validatedRequest['line_id'],
            "kode" => $kodeLoadingPlan,
            "act_costing_id" => $validatedRequest['ws_id'],
            "act_costing_ws" => $validatedRequest['ws'],
            "buyer" => $validatedRequest['buyer'],
            "style" => $validatedRequest['style'],
            "color" => $validatedRequest['color'],
            "target_sewing" => $validatedRequest['target_sewing'],
            "target_loading" => $validatedRequest['target_loading'],
            "tanggal" => $validatedRequest['tanggal'],
        ]);

        if ($storeLoadingPlan) {
            return array(
                "status" => 200,
                "message" => $kodeLoadingPlan,
                "redirect" => route("create-loading-plan"),
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Gagal Menyimpan Loading Plan",
            "redirect" => route("create-loading-plan"),
            "additional" => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->ajax()) {
            $lineStocker = LoadingLinePlan::selectRaw("
                stocker_input.color,
                stocker_input.id_qr_stocker,
                stocker_input.id_qr_stocker,
            ")->
            leftJoin("loading_line", "loading_line.loading_plan_id", "=", "loading_line_plan.id")->
            leftJoin("stocker_input", "stocker_input.id", "loading_line.stocker_id")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            groupBy("loading_line_plan.id");

            return DataTables::eloquent($line)
                ->filter(function ($query) {
                    if (request()->has('dateFrom') && request('dateFrom') != null && request('dateFrom') != "") {
                        $query->where("loading_line_plan.tanggal", ">=", request('dateFrom'));
                    }

                    if (request()->has('dateTo') && request('dateTo') != null && request('dateTo') != "") {
                        $query->where("loading_line_plan.tanggal", "<=", request('dateTo'));
                    }
                })
                ->addColumn('nama_line', function ($row) {
                    $lineData = UserLine::where('line_id', $row->line_id)->first();
                    $line = $lineData ? strtoupper(str_replace("_", " ", $lineData->username)) : "";

                    return $line;
                })
                ->toJson();
        }

        $loadingLinePlan = LoadingLinePlan::where("id", $id)->first();

        return view("trolley.loading-line.detail-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'loading-line', "loadingLinePlan" => $loadingLinePlan]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $loadingLinePlan = LoadingLinePlan::where("id", $id)->first();

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();
        $lines = UserLine::where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("trolley.loading-line.edit-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'loading-line', 'loadingLinePlan' => $loadingLinePlan, 'lines' => $lines, 'orders' => $orders]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedRequest = $request->validate([
            "tanggal" => "required",
            "line_id" => "required",
            "ws_id" => "required",
            "ws" => "required",
            "buyer" => "required",
            "style" => "required",
            "color" => "required",
            "target_sewing" => "required",
            "target_loading" => "required",
        ]);

        $updateLoadingPlan = LoadingLinePlan::where("id", $id)->
            update([
                "line_id" => $validatedRequest['line_id'],
                "act_costing_id" => $validatedRequest['ws_id'],
                "act_costing_ws" => $validatedRequest['ws'],
                "buyer" => $validatedRequest['buyer'],
                "style" => $validatedRequest['style'],
                "color" => $validatedRequest['color'],
                "target_sewing" => $validatedRequest['target_sewing'],
                "target_loading" => $validatedRequest['target_loading'],
                "tanggal" => $validatedRequest['tanggal'],
            ]);

        if ($updateLoadingPlan) {
            return array(
                "status" => 200,
                "message" => $request['kode'] ? $request['kode'] : $updateLoadingPlan->kode,
                "redirect" => route('loading-line'),
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Gagal Menyimpan Loading Plan",
            "redirect" => route('loading-line'),
            "additional" => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
