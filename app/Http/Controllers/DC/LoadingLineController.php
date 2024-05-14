<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoadingLinePlan;
use App\Models\SignalBit\UserLine;
use App\Exports\ExportLaporanLoading;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Excel;

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
            $dateFilter = "";
            if ($request->dateFrom || $request->dateTo) {
                $dateFilter = "WHERE ";
                $dateFromFilter = " loading_line_plan.tanggal >= '".$request->dateFrom."' ";
                $dateToFilter = " loading_line_plan.tanggal <= '".$request->dateTo."' ";

                if ($request->dateFrom && $request->dateTo) {
                    $dateFilter .= $dateFromFilter." AND ".$dateToFilter;
                } else {
                    if ($request->dateTo) {
                        $dateFilter .= $dateFromFilter;
                    }

                    if ($request->dateFrom) {
                        $dateFilter .= $dateToFilter;
                    }
                }
            }

            $line = DB::select("
                SELECT
                    loading_line_plan.id,
                    loading_line_plan.line_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.style,
                    loading_line_plan.color,
                    loading_line_plan.target_sewing,
                    loading_line_plan.target_loading,
                    sum( loading_stock.qty ) loading_qty,
                    loading_line_plan.target_loading - sum( loading_stock.qty ) loading_balance,
                    loading_stock.nama_trolley nama_trolley,
                    trolley_stock.trolley_color trolley_color,
                    trolley_stock.trolley_qty trolley_qty
                FROM
                    loading_line_plan
                    LEFT JOIN (
                        SELECT
                            loading_line.loading_plan_id,
                            loading_line.qty,
                            trolley.id trolley_id,
                            trolley.nama_trolley
                        FROM
                            loading_line
                            LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                            LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                        GROUP BY
                            stocker_input.form_cut_id,
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.range_awal
                        ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
                    LEFT JOIN (
                        select
                            trolley.id trolley_id,
                            group_concat(distinct trolley_stock_bundle.trolley_ws) trolley_ws,
                            group_concat(distinct trolley_stock_bundle.trolley_color) trolley_color,
                            sum(trolley_stock_bundle.trolley_qty) trolley_qty
                        from
                            trolley
                            left join trolley_stocker on trolley_stocker.trolley_id = trolley.id
                            inner join (
                                SELECT
                                    trolley_stocker.stocker_id,
                                    stocker_input.act_costing_ws trolley_ws,
                                    stocker_input.color trolley_color,
                                    stocker_input.qty_ply trolley_qty
                                FROM
                                    trolley_stocker
                                    LEFT JOIN stocker_input ON stocker_input.id = trolley_stocker.stocker_id
                                WHERE
                                    trolley_stocker.STATUS = 'active'
                                GROUP BY
                                    stocker_input.form_cut_id,
                                    stocker_input.so_det_id,
                                    stocker_input.group_stocker,
                                    stocker_input.range_awal
                            ) trolley_stock_bundle on trolley_stock_bundle.stocker_id = trolley_stocker.stocker_id
                            group by trolley.id
                    ) trolley_stock ON trolley_stock.trolley_id = loading_stock.trolley_id
                ".$dateFilter."
                GROUP BY
                    loading_line_plan.id
                ORDER BY
                    loading_line_plan.line_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.color
            ");

            return DataTables::of($line)
                ->addColumn('nama_line', function ($row) {
                    $lineData = UserLine::where('line_id', $row->line_id)->first();
                    $line = $lineData ? strtoupper(str_replace("_", " ", $lineData->username)) : "";

                    return $line;
                })
                ->toJson();
        }

        return view("dc.loading-line.loading-line", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'loading-line']);
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

        return view("dc.loading-line.create-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'loading-line', 'lines' => $lines, 'orders' => $orders]);
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
                stocker_input.id_qr_stocker
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

        return view("dc.loading-line.detail-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'loading-line', "loadingLinePlan" => $loadingLinePlan]);
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

        return view("dc.loading-line.edit-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'loading-line', 'loadingLinePlan' => $loadingLinePlan, 'lines' => $lines, 'orders' => $orders]);
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

    public function summary(Request $request) {
        if ($request->ajax()) {
            $dateFilter = "";
            if ($request->tanggal) {
                $dateFilter = "HAVING loading_stock.tanggal_loading = '".$request->tanggal."' ";
            }

            $line = DB::select("
                    SELECT
                            loading_stock.tanggal_loading,
                            loading_line_plan.line_id,
                            loading_line_plan.act_costing_ws,
                            loading_line_plan.style,
                            loading_line_plan.color,
                            loading_stock.size size,
                            sum( loading_stock.qty ) loading_qty
                    FROM
                            loading_line_plan
                            LEFT JOIN (
                                    SELECT
                                            COALESCE(loading_line.tanggal_loading, DATE(loading_line.updated_at)) tanggal_loading,
                                            loading_line.loading_plan_id,
                                            loading_line.qty,
                                            trolley.id trolley_id,
                                            trolley.nama_trolley,
                                            stocker_input.size
                                    FROM
                                            loading_line
                                            LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                                            LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                                            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                                    GROUP BY
                                            loading_line.tanggal_loading,
                                            stocker_input.form_cut_id,
                                            stocker_input.so_det_id,
                                            stocker_input.group_stocker,
                                            stocker_input.range_awal
                            ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
                    WHERE
                            loading_stock.tanggal_loading is not null
                    GROUP BY
                            loading_stock.tanggal_loading,
                            loading_line_plan.line_id,
                            loading_line_plan.act_costing_ws,
                            loading_line_plan.style,
                            loading_line_plan.color,
                            loading_stock.size
                            ".$dateFilter."
                    ORDER BY
                            loading_stock.tanggal_loading,
                            loading_line_plan.line_id,
                            loading_line_plan.act_costing_ws,
                            loading_line_plan.color
            ");

            return DataTables::of($line)
                ->addColumn('nama_line', function ($row) {
                    $lineData = UserLine::where('line_id', $row->line_id)->first();
                    $line = $lineData ? strtoupper(str_replace("_", " ", $lineData->username)) : "";

                    return $line;
                })
                ->toJson();
        }

        return view("dc.loading-line.summary-loading", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'summary-loading']);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ExportLaporanLoading($request->tanggal), 'Laporan Loading ".$tanggal.".xlsx');
    }
}
