<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoadingLinePlan;
use App\Models\SignalBit\UserLine;
use App\Exports\ExportLaporanLoading;
use App\Exports\DC\ExportLoadingLine;
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
            $detailDateFilter = "";
            if ($request->dateFrom || $request->dateTo) {
                $detailDateFilter = "WHERE ";
                $dateFromFilter = " loading_line.tanggal_loading >= '".$request->dateFrom."' ";
                $dateToFilter = " loading_line.tanggal_loading <= '".$request->dateTo."' ";

                if ($request->dateFrom && $request->dateTo) {
                    $detailDateFilter .= $dateFromFilter." AND ".$dateToFilter;
                } else {
                    if ($request->dateTo) {
                        $detailDateFilter .= $dateFromFilter;
                    }

                    if ($request->dateFrom) {
                        $detailDateFilter .= $dateToFilter;
                    }
                }
            }

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
                    INNER JOIN (
                        SELECT
                            (
                                ( COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                                ( COALESCE ( dc_in_input.qty_reject, 0 )) + ( COALESCE ( dc_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_in_input.qty_reject, 0 )) + ( COALESCE ( secondary_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + (COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                            ) qty_old,
                            MIN(loading_line.qty) qty,
                            trolley.id trolley_id,
                            trolley.nama_trolley,
                            stocker_input.so_det_id,
                            stocker_input.size,
                            loading_line.loading_plan_id
                        FROM
                            loading_line
                            LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                            LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
                            ".$detailDateFilter."
                        GROUP BY
                            loading_line.tanggal_loading,
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
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
                                    stocker_input.form_reject_id,
                                    stocker_input.so_det_id,
                                    stocker_input.group_stocker,
                                    stocker_input.range_awal
                            ) trolley_stock_bundle on trolley_stock_bundle.stocker_id = trolley_stocker.stocker_id
                        group by
                            trolley.id
                    ) trolley_stock ON trolley_stock.trolley_id = loading_stock.trolley_id
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

    public function totalLoading(Request $request)
    {
        $detailDateFilter = "";
        if ($request->dateFrom || $request->dateTo) {
            $detailDateFilter = "WHERE ";
            $dateFromFilter = " loading_line.tanggal_loading >= '".$request->dateFrom."' ";
            $dateToFilter = " loading_line.tanggal_loading <= '".$request->dateTo."' ";

            if ($request->dateFrom && $request->dateTo) {
                $detailDateFilter .= $dateFromFilter." AND ".$dateToFilter;
            } else {
                if ($request->dateTo) {
                    $detailDateFilter .= $dateFromFilter;
                }

                if ($request->dateFrom) {
                    $detailDateFilter .= $dateToFilter;
                }
            }
        }

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

        $lineFilter = "";
        $detailLineFilter = "";
        if ($request->lineFilter) {
            $lineData = UserLine::whereRaw('username like "%'.$request->lineFilter.'%" OR FullName like "%'.$request->lineFilter.'%"')->get();
            $lineIds = $lineData->count() > 0 ? $lineData->pluck("line_id")->toArray() : [];

            if (count($lineIds) > 0) {
                $lineIds = implode(",", $lineIds);
                $lineFilter .= " AND line_id in ('".$lineIds."')";
                $detailLineFilter .= " AND loading_line.line_id in ('".$lineIds."')";
            }
        }

        $wsFilter = "";
        if ($request->wsFilter) {
            $wsFilter .= " AND act_costing_ws LIKE '%".$request->wsFilter."%'";
        }

        $styleFilter = "";
        if ($request->styleFilter) {
            $styleFilter .= " AND style LIKE '%".$request->styleFilter."%'";
        }

        $colorFilter = "";
        if ($request->colorFilter) {
            $colorFilter .= " AND color LIKE '%".$request->colorFilter."%'";
        }

        $trolleyFilter = "";
        if ($request->trolleyFilter) {
            $trolleyFilter .= " AND nama_trolley LIKE '%".$request->trolleyFilter."%'";
        }

        $trolleyColorFilter = "";
        if ($request->trolleyColorFilter) {
            $trolleyColorFilter .= " AND trolley_color LIKE '%".$request->trolleyColorFilter."%'";
        }

        $loadingLine = DB::select("
            SELECT
                sum(target_sewing) total_target_loading,
                sum(target_loading) total_target_sewing,
                sum(loading_qty) total_loading,
                sum(loading_balance) total_balance_loading
            FROM
                (
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
                        INNER JOIN (
                            SELECT
                                (
                                    ( COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                                    ( COALESCE ( dc_in_input.qty_reject, 0 )) + ( COALESCE ( dc_in_input.qty_replace, 0 )) -
                                    ( COALESCE ( secondary_in_input.qty_reject, 0 )) + ( COALESCE ( secondary_in_input.qty_replace, 0 )) -
                                    ( COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + (COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                                ) qty_old,
                                MIN(loading_line.qty) qty,
                                trolley.id trolley_id,
                                trolley.nama_trolley,
                                stocker_input.so_det_id,
                                stocker_input.size,
                                loading_line.loading_plan_id
                            FROM
                                loading_line
                                LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                                LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                                LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                                LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                                LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                                LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                                LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
                                ".$detailDateFilter."
                                ".$detailLineFilter."
                            GROUP BY
                                loading_line.tanggal_loading,
                                stocker_input.form_cut_id,
                                stocker_input.form_reject_id,
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
                                        stocker_input.form_reject_id,
                                        stocker_input.so_det_id,
                                        stocker_input.group_stocker,
                                        stocker_input.range_awal
                                ) trolley_stock_bundle on trolley_stock_bundle.stocker_id = trolley_stocker.stocker_id
                                group by trolley.id
                        ) trolley_stock ON trolley_stock.trolley_id = loading_stock.trolley_id
                    WHERE
                        loading_line_plan.id IS NOT NULL
                        ".$lineFilter."
                        ".$wsFilter."
                        ".$styleFilter."
                        ".$colorFilter."
                        ".$trolleyFilter."
                        ".$trolleyColorFilter."
                    GROUP BY
                        loading_line_plan.id
                    ORDER BY
                        loading_line_plan.line_id,
                        loading_line_plan.act_costing_ws,
                        loading_line_plan.color
                ) loading
        ");

        return json_encode($loadingLine ? $loadingLine[0] : null);
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

        $loadingLinePlan = LoadingLinePlan::where("id", $id)->with(['loadingLines', 'loadingLines.stocker', 'loadingLines.stocker.dcIn', 'loadingLines.stocker.dcIn.secondaryIn', 'loadingLines.stocker.dcIn.secondaryInHouse'])->first();

        $loadingLines = DB::select("
            SELECT
                COALESCE( DATE ( loading_line.updated_at ), loading_line.tanggal_loading ) tanggal_loading,
                loading_line.loading_plan_id,
                loading_line.nama_line,
                (
                    (COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply )) -
                    (COALESCE ( MAX(dc_in_input.qty_reject), 0 )) +
                    (COALESCE ( MAX(dc_in_input.qty_replace), 0 )) -
                    (COALESCE ( MAX(secondary_in_input.qty_reject), 0 )) +
                    (COALESCE ( MAX(secondary_in_input.qty_replace), 0 )) -
                    (COALESCE ( MAX(secondary_inhouse_input.qty_reject), 0 )) +
                    (COALESCE ( MAX(secondary_inhouse_input.qty_replace), 0 ))
                ) qty_old,
                loading_line.qty,
                trolley.id trolley_id,
                trolley.nama_trolley,
                stocker_input.id_qr_stocker,
                stocker_input.so_det_id,
                stocker_input.size,
                stocker_input.shade,
                stocker_input.group_stocker,
                stocker_input.range_awal,
                stocker_input.range_akhir,
                (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) tipe,
                loading_line_plan.act_costing_id,
                loading_line_plan.act_costing_ws,
                loading_line_plan.buyer,
                loading_line_plan.style,
                loading_line_plan.color,
                loading_line_plan.line_id,
                COALESCE(form_cut_input.no_form, form_cut_reject.no_form) no_form,
                COALESCE(form_cut_input.no_cut, '-') no_cut
            FROM
                loading_line
                LEFT JOIN loading_line_plan ON loading_line_plan.id = loading_line.loading_plan_id
                LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
            WHERE
                loading_line_plan.id = '".$id."'
            GROUP BY
                stocker_input.id_qr_stocker
            ORDER BY
                loading_line.tanggal_loading,
                stocker_input.form_cut_id,
                stocker_input.form_reject_id,
                stocker_input.so_det_id,
                stocker_input.range_awal
        ");

        return view("dc.loading-line.detail-loading-plan", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'loading-line', "loadingLinePlan" => $loadingLinePlan, "loadingLines" => $loadingLines]);
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

    public function exportLoadingLine(Request $request) {
        ini_set("max_execution_time", 36000);

        $from = $request->from ? $request->from : date("Y-m-d");
        $to = $request->to ? $request->to : date("Y-m-d");

        return Excel::download(new ExportLoadingLine($from, $to, $request->lineFilter, $request->wsFilter, $request->styleFilter, $request->colorFilter, $request->targetSewingFilter, $request->targetLoadingFilter, $request->trolleyFilter, $request->trolleyColorFilter), 'Laporan Loading '.$from.' - '.$to.'.xlsx');
    }

    public function summary(Request $request) {
        ini_set("max_execution_time", 36000);

        if ($request->ajax()) {

            $dateFilter = "";
            if ($request->dateFrom || $request->dateTo) {
                $dateFilter = "HAVING ";
                $dateFromFilter = " loading_stock.tanggal_loading >= '".$request->dateFrom."' ";
                $dateToFilter = " loading_stock.tanggal_loading <= '".$request->dateTo."' ";

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

            $innerDateFilter = "";
            if ($request->dateFrom || $request->dateTo) {
                $innerDateFilter = "WHERE ";
                $innerDateFromFilter = " loading_line.tanggal_loading >= '".$request->dateFrom."' ";
                $innerDateToFilter = " loading_line.tanggal_loading <= '".$request->dateTo."' ";

                if ($request->dateFrom && $request->dateTo) {
                    $innerDateFilter .= $innerDateFromFilter." AND ".$innerDateToFilter;
                } else {
                    if ($request->dateTo) {
                        $innerDateFilter .= $innerDateFromFilter;
                    }

                    if ($request->dateFrom) {
                        $innerDateFilter .= $innerDateToFilter;
                    }
                }
            }

            $line = DB::select("
                SELECT
                    loading_stock.tanggal_loading,
                    loading_line_plan.id,
                    loading_line_plan.line_id,
                    loading_stock.nama_line,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.style,
                    loading_line_plan.color,
                    loading_stock.size size,
                    sum( loading_stock.qty ) loading_qty
                FROM
                    loading_line_plan
                    LEFT JOIN (
                        SELECT
                            COALESCE ( DATE ( loading_line.updated_at ), loading_line.tanggal_loading ) tanggal_loading,
                            loading_line.loading_plan_id,
                            loading_line.nama_line,
                            (
                                COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply ) -
                                ( COALESCE ( dc_in_input.qty_reject, 0 )) + ( COALESCE ( dc_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_in_input.qty_reject, 0 )) + ( COALESCE ( secondary_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + (COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                            ) qty_old,
                            MIN(loading_line.qty) qty,
                            trolley.id trolley_id,
                            trolley.nama_trolley,
                            stocker_input.so_det_id,
                            stocker_input.size
                        FROM
                            loading_line
                            INNER JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                            LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
                            ".$innerDateFilter."
                        GROUP BY
                            loading_line.tanggal_loading,
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.ratio
                    ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
                WHERE
                    loading_stock.tanggal_loading IS NOT NULL
                GROUP BY
                    loading_stock.tanggal_loading,
                    loading_line_plan.id,
                    loading_stock.size
                    ".$dateFilter."
            ");

            return DataTables::of($line)->toJson();
        }

        return view("dc.loading-line.summary-loading", ['page' => 'dashboard-dc', 'subPageGroup' => 'loading-dc', 'subPage' => 'summary-loading']);
    }

    public function getTotalSummary(Request $request) {
        ini_set("max_execution_time", 36000);

        $dateFilter = "";
        if ($request->dateFrom || $request->dateTo) {
            $dateFilter = "HAVING ";
            $dateFromFilter = " loading_stock.tanggal_loading >= '".$request->dateFrom."' ";
            $dateToFilter = " loading_stock.tanggal_loading <= '".$request->dateTo."' ";

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

        $innerDateFilter = "";
        if ($request->dateFrom || $request->dateTo) {
            $innerDateFilter = "WHERE ";
            $innerDateFromFilter = " loading_line.tanggal_loading >= '".$request->dateFrom."' ";
            $innerDateToFilter = " loading_line.tanggal_loading <= '".$request->dateTo."' ";

            if ($request->dateFrom && $request->dateTo) {
                $innerDateFilter .= $innerDateFromFilter." AND ".$innerDateToFilter;
            } else {
                if ($request->dateTo) {
                    $innerDateFilter .= $innerDateFromFilter;
                }

                if ($request->dateFrom) {
                    $innerDateFilter .= $innerDateToFilter;
                }
            }
        }

        $generalFilter = "";
        if ($request->tanggal_loading) {
            $generalFilter .= " AND loading_stock.tanggal_loading LIKE '%".$request->tanggal_loading."%'";
        }
        if ($request->nama_line) {
            $generalFilter .= " AND loading_stock.nama_line LIKE '%".$request->nama_line."%'";
        }
        if ($request->act_costing_ws) {
            $generalFilter .= " AND loading_line_plan.act_costing_ws LIKE '%".$request->act_costing_ws."%'";
        }
        if ($request->style) {
            $generalFilter .= " AND loading_line_plan.style LIKE '%".$request->style."%'";
        }
        if ($request->color) {
            $generalFilter .= " AND loading_line_plan.color LIKE '%".$request->color."%'";
        }
        if ($request->size) {
            $generalFilter .= " AND loading_stock.size LIKE '%".$request->size."%'";
        }

        $line = DB::select("
                SELECT
                    loading_stock.tanggal_loading,
                    loading_line_plan.id,
                    loading_line_plan.line_id,
                    loading_stock.nama_line,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.style,
                    loading_line_plan.color,
                    loading_stock.size size,
                    sum( loading_stock.qty ) loading_qty
                FROM
                    loading_line_plan
                    LEFT JOIN (
                        SELECT
                            COALESCE ( DATE ( loading_line.updated_at ), loading_line.tanggal_loading ) tanggal_loading,
                            loading_line.loading_plan_id,
                            loading_line.nama_line,
                            (
                                COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply ) -
                                ( COALESCE ( dc_in_input.qty_reject, 0 )) + ( COALESCE ( dc_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_in_input.qty_reject, 0 )) + ( COALESCE ( secondary_in_input.qty_replace, 0 )) -
                                ( COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + (COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                            ) qty_old,
                            MIN(loading_line.qty) qty,
                            trolley.id trolley_id,
                            trolley.nama_trolley,
                            stocker_input.so_det_id,
                            stocker_input.size
                        FROM
                            loading_line
                            LEFT JOIN stocker_input ON stocker_input.id = loading_line.stocker_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN trolley_stocker ON stocker_input.id = trolley_stocker.stocker_id
                            LEFT JOIN trolley ON trolley.id = trolley_stocker.trolley_id
                            LEFT JOIN master_size_new ON master_size_new.size = stocker_input.size
                            ".$innerDateFilter."
                        GROUP BY
                            loading_line.tanggal_loading,
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.range_awal
                    ) loading_stock ON loading_stock.loading_plan_id = loading_line_plan.id
                WHERE
                    loading_stock.tanggal_loading IS NOT NULL
                    ".$generalFilter."
                GROUP BY
                    loading_stock.tanggal_loading,
                    loading_line_plan.id,
                    loading_stock.size
                    ".$dateFilter."
                ORDER BY
                    loading_stock.tanggal_loading,
                    loading_line_plan.line_id,
                    loading_line_plan.act_costing_ws,
                    loading_line_plan.color,
                    loading_stock.so_det_id
            ");

        return $line ? array_sum(array_column($line, 'loading_qty')) : 0;
    }

    public function exportExcel(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportLaporanLoading($request->dateFrom, $request->dateTo), 'Laporan Loading '.$request->dateFrom.' - '.$request->dateTo.'.xlsx');
    }
}
