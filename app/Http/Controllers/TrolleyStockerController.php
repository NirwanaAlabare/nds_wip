<?php

namespace App\Http\Controllers;

use App\Models\Trolley;
use App\Models\TrolleyStocker;
use App\Models\Stocker;
use App\Models\LoadingLine;
use App\Models\LoadingLinePlan;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class TrolleyStockerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
        $trolleyStocks = Trolley::selectRaw("
                trolley.id,
                trolley_stocker.tanggal_alokasi,
                stocker.act_costing_ws,
                marker_input.style,
                stocker.color,
                trolley.nama_trolley,
                stocker.qty_ply,
                SUM(stocker.qty_ply) qty
            ")->
            leftJoin("trolley_stocker", function($join)
                {
                    $join->on('trolley_stocker.trolley_id', '=', 'trolley.id');
                    $join->on('trolley_stocker.status', '=', DB::raw('"active"'));
                }
            )->
            leftJoin(
                DB::raw('
                    (
                        SELECT
                            stocker_input.id,
                            stocker_input.form_cut_id,
                            stocker_input.act_costing_ws,
                            stocker_input.color,
                            COALESCE((dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace), stocker_input.qty_ply) qty_ply,
                            form_cut_input.id_marker
                        FROM
                            stocker_input
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                        GROUP BY
                            stocker_input.form_cut_id, stocker_input.so_det_id, stocker_input.group_stocker, stocker_input.ratio
                    ) stocker
                '),
                'stocker.id', '=', 'trolley_stocker.stocker_id'
            )->
            leftJoin("marker_input", "marker_input.kode", "=", "stocker.id_marker")->
            groupBy('trolley.id', 'stocker.act_costing_ws', 'marker_input.style', 'stocker.color')->
            orderByRaw("ISNULL(SUM(stocker.qty_ply)) asc")->
            orderByRaw("CAST(trolley.nama_trolley AS UNSIGNED) asc")->
            orderByRaw("trolley.id asc")->
            get();

        return view('trolley.stock-trolley.stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolleyStocks' => $trolleyStocks]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function allocate(Request $request)
    {
        if ($request->ajax()) {
            $trolley = TrolleyStocker::selectRaw("
                    trolley_stocker.id,
                    GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker ORDER BY stocker_input.id ASC SEPARATOR ', ') id_qr_stocker,
                    stocker_input.act_costing_ws,
                    form_cut_input.no_cut,
                    marker_input.style,
                    stocker_input.color,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    stocker_input.size,
                    SUM(stocker_input.qty_ply) qty,
                    CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) rangeAwalAkhir
                ")->
                leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                where('trolley_id', $request->trolley_id)->
                where('trolley_stocker.status', "active")->
                where('stocker_input.status', "trolley")->
                groupBy('form_cut_input.no_cut', 'stocker_input.form_cut_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio');

            return DataTables::eloquent($trolley)->
                filterColumn('id', function($query, $keyword) {
                    $query->whereRaw("trolley_stocker.id LIKE '%".$keyword."%'");
                })->
                filterColumn('id_qr_stocker', function($query, $keyword) {
                    $query->whereRaw("stocker_input.id_qr_stocker LIKE '%".$keyword."%'");
                })->
                filterColumn('act_costing_ws', function($query, $keyword) {
                    $query->whereRaw("stocker_input.act_costing_ws LIKE '%".$keyword."%'");
                })->
                filterColumn('no_cut', function($query, $keyword) {
                    $query->whereRaw("form_cut_input.no_cut LIKE '%".$keyword."%'");
                })->
                filterColumn('style', function($query, $keyword) {
                    $query->whereRaw("marker_input.style LIKE '%".$keyword."%'");
                })->
                filterColumn('color', function($query, $keyword) {
                    $query->whereRaw("marker_input.color LIKE '%".$keyword."%'");
                })->
                filterColumn('nama_part', function($query, $keyword) {
                    $query->whereRaw("master_part.nama_part LIKE '%".$keyword."%'");
                })->
                filterColumn('qty', function($query, $keyword) {
                    $query->whereRaw("stocker_input.qty_ply LIKE '%".$keyword."%'");
                })->
                filterColumn('rangeAwalAkhir', function($query, $keyword) {
                    $query->whereRaw("CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) LIKE '%".$keyword."%'");
                })->
                filterColumn('size', function($query, $keyword) {
                    $query->whereRaw("stocker_input.size LIKE '%".$keyword."%'");
                })->
                toJson();
        }

        $trolleys = Trolley::orderBy('nama_trolley', 'asc')->get();

        return view('trolley.stock-trolley.allocate-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolleys' => $trolleys]);
    }

    public function allocateThis(Request $request, $id)
    {
        if ($request->ajax()) {
            $trolley = TrolleyStocker::selectRaw("
                    trolley_stocker.id,
                    GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker ORDER BY stocker_input.id ASC SEPARATOR ', ') id_qr_stocker,
                    stocker_input.act_costing_ws,
                    form_cut_input.no_cut,
                    marker_input.style,
                    stocker_input.color,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    stocker_input.size,
                    stocker_input.qty_ply qty,
                    CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) rangeAwalAkhir
                ")->
                leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                where('trolley_id', $id)->
                where('trolley_stocker.status', "active")->
                where('stocker_input.status', "trolley")->
                groupBy('form_cut_input.no_cut', 'stocker_input.form_cut_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio');

            return DataTables::eloquent($trolley)->
                filterColumn('id', function($query, $keyword) {
                    $query->whereRaw("trolley_stocker.id LIKE '%".$keyword."%'");
                })->
                filterColumn('id_qr_stocker', function($query, $keyword) {
                    $query->whereRaw("stocker_input.id_qr_stocker LIKE '%".$keyword."%'");
                })->
                filterColumn('act_costing_ws', function($query, $keyword) {
                    $query->whereRaw("stocker_input.act_costing_ws LIKE '%".$keyword."%'");
                })->
                filterColumn('no_cut', function($query, $keyword) {
                    $query->whereRaw("form_cut_input.no_cut LIKE '%".$keyword."%'");
                })->
                filterColumn('style', function($query, $keyword) {
                    $query->whereRaw("marker_input.style LIKE '%".$keyword."%'");
                })->
                filterColumn('color', function($query, $keyword) {
                    $query->whereRaw("marker_input.color LIKE '%".$keyword."%'");
                })->
                filterColumn('nama_part', function($query, $keyword) {
                    $query->whereRaw("master_part.nama_part LIKE '%".$keyword."%'");
                })->
                filterColumn('qty', function($query, $keyword) {
                    $query->whereRaw("stocker_input.qty_ply LIKE '%".$keyword."%'");
                })->
                filterColumn('rangeAwalAkhir', function($query, $keyword) {
                    $query->whereRaw("CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) LIKE '%".$keyword."%'");
                })->
                filterColumn('size', function($query, $keyword) {
                    $query->whereRaw("stocker_input.size LIKE '%".$keyword."%'");
                })->
                toJson();
        }

        $trolley = Trolley::with('userLine')->where('id', $id)->first();

        return view('trolley.stock-trolley.allocate-this-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolley' => $trolley]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function storeAllocate(Request $request)
    {
        $validatedRequest = $request->validate([
            "trolley_id" => "required",
            "stocker_id" => "required",
        ]);

        $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
        $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

        $stockerData = Stocker::where("id", $validatedRequest["stocker_id"])->first();
        $similarStockerData = Stocker::where("form_cut_id", $stockerData->form_cut_id)->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
            get();

        $trolleyStockArr = [];

        $i = 0;
        foreach ($similarStockerData as $stocker) {
            array_push($trolleyStockArr, [
                "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                "trolley_id" => $validatedRequest['trolley_id'],
                "stocker_id" => $stocker['id'],
                "status" => "active",
                "tanggal_alokasi" => date('Y-m-d'),
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            $i++;
        }

        $storeTrolleyStock = TrolleyStocker::insert($trolleyStockArr);

        if (count($trolleyStockArr) > 0) {
            $updateStocker = Stocker::where("form_cut_id", $stockerData->form_cut_id)->
                where("so_det_id", $stockerData->so_det_id)->
                where("group_stocker", $stockerData->group_stocker)->
                where("ratio", $stockerData->ratio)->
                update([
                    "status" => "trolley",
                    "latest_alokasi" => Carbon::now()
                ]);

            if ($updateStocker) {
                return array(
                    'status' => 200,
                    'message' => 'Stocker berhasil dialokasi',
                    'redirect' => '',
                    'table' => 'trolley-stock-datatable',
                    'callback' => 'clearAll()',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Stocker gagal dialokasi',
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'clearAll()',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal dialokasi',
            'redirect' => '',
            'table' => 'trolley-stock-datatable',
            'callback' => 'clearAll()',
            'additional' => [],
        );
    }

    public function storeAllocateThis(Request $request)
    {
        $validatedRequest = $request->validate([
            "trolley_id" => "required",
            "stocker_id" => "required",
        ]);

        $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
        $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

        $stockerData = Stocker::where("id", $validatedRequest["stocker_id"])->first();
        $similarStockerData = Stocker::where("form_cut_id", $stockerData->form_cut_id)->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
            get();

        $trolleyStockArr = [];

        $i = 0;
        foreach ($similarStockerData as $stocker) {

            $trolleyStockCheck = TrolleyStocker::where("stocker_id", $stocker['id'])->first();
            if (!$trolleyStockCheck) {
                array_push($trolleyStockArr, [
                    "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                    "trolley_id" => $validatedRequest['trolley_id'],
                    "stocker_id" => $stocker['id'],
                    "status" => "active",
                    "tanggal_alokasi" => date('Y-m-d')
                ]);
            }

            $i++;
        }

        $storeTrolleyStock = TrolleyStocker::insert($trolleyStockArr);

        if (count($trolleyStockArr) > 0) {
            $updateStocker = Stocker::where("form_cut_id", $stockerData->form_cut_id)->
                where("so_det_id", $stockerData->so_det_id)->
                where("group_stocker", $stockerData->group_stocker)->
                where("ratio", $stockerData->ratio)->
                update([
                    "status" => "trolley",
                    "latest_alokasi" => Carbon::now()
                ]);

            if ($updateStocker) {
                return array(
                    'status' => 200,
                    'message' => 'Stocker berhasil dialokasi',
                    'redirect' => '',
                    'table' => 'datatable-trolley-stock',
                    'callback' => 'datatableTrolleyStockReload()',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Stocker gagal dialokasi',
                'redirect' => '',
                'table' => 'datatable-trolley-stock',
                'callback' => 'datatableTrolleyStockReload()',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal dialokasi',
            'redirect' => '',
            'table' => 'datatable-trolley-stock',
            'callback' => 'datatableTrolleyStockReload()',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function show(TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function edit(TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrolleyStocker $trolleyStocker, $id)
    {
        $getTrolleyStockData = TrolleyStocker::where("id", $id)->first();

        $stockerData = Stocker::where("id", $getTrolleyStockData->stocker_id)->first();

        $deleteTrolleyStock = TrolleyStocker::leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
            whereRaw("trolley_stocker.trolley_id = '".$getTrolleyStockData->trolley_id."'")->
            whereRaw("stocker_input.form_cut_id = '".$stockerData->form_cut_id."'")->
            whereRaw("stocker_input.so_det_id = '".$stockerData->so_det_id."'")->
            whereRaw("stocker_input.group_stocker = '".$stockerData->group_stocker."'")->
            whereRaw("stocker_input.ratio = '".$stockerData->ratio."'")->
            delete();

        if ($deleteTrolleyStock) {
            $updateStocker = Stocker::where("stocker_input.form_cut_id", $stockerData->form_cut_id)->
                where("stocker_input.so_det_id", $stockerData->so_det_id)->
                where("stocker_input.group_stocker", $stockerData->group_stocker)->
                where("stocker_input.ratio", $stockerData->ratio)->
                update([
                    "status" => "idle",
                    "latest_alokasi" => Carbon::now()
                ]);

            if ($updateStocker) {
                return array(
                    'status' => 200,
                    'message' => 'Stocker berhasil disingkirkan',
                    'redirect' => '',
                    'table' => 'datatable-trolley-stock',
                    'callback' => 'datatableTrolleyStockReload()',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Stocker gagal  disingkirkan',
                'redirect' => '',
                'table' => 'datatable-trolley-stock',
                'callback' => 'datatableTrolleyStockReload()',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal  disingkirkan',
            'redirect' => '',
            'table' => 'datatable-trolley-stock',
            'callback' => 'datatableTrolleyStockReload()',
            'additional' => [],
        );
    }

    public function send(Request $request, $id) {
        $trolley = Trolley::with('userLine')->where('id', $id)->first();

        $lines = UserLine::where('Groupp', 'SEWING')->whereRaw('(Locked != 1 || Locked is NULL)')->orderBy('line_id', 'asc')->get();

        $trolleyStocks = TrolleyStocker::selectRaw("
                trolley_stocker.id,
                GROUP_CONCAT(stocker_input.id ORDER BY stocker_input.id ASC) stocker_id,
                GROUP_CONCAT(stocker_input.id_qr_stocker ORDER BY stocker_input.id ASC SEPARATOR ', ') id_qr_stocker,
                stocker_input.act_costing_ws,
                form_cut_input.no_cut,
                marker_input.style,
                stocker_input.color,
                GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                stocker_input.size,
                COALESCE((dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace), stocker_input.qty_ply) qty,
                CONCAT(MIN(stocker_input.range_awal), ' - ', MAX(stocker_input.range_akhir)) rangeAwalAkhir
            ")->
            leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
            leftJoin("dc_in_input", "dc_in_input.id", "=", "stocker_input.id_qr_stocker")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            where('trolley_id', $id)->
            where('trolley_stocker.status', 'active')->
            where('stocker_input.status', "!=", "line")->
            groupBy('form_cut_input.no_cut', 'stocker_input.form_cut_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio')->
            get();

        return view('trolley.stock-trolley.send-stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolley' => $trolley, 'lines' => $lines, 'trolleyStocks' => $trolleyStocks]);
    }

    public function submitSend(Request $request) {
        $success = [];
        $fail = [];
        $exist = [];

        $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
        $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;

        $lineData = UserLine::where("line_id", $request->line_id)->first();

        // if ($request->selectedStocker)
        foreach ($request->selectedStocker as $req) {
            $loadingStockArr = [];

            $stockerIds = explode(',', $req['stocker_ids']);

            for ($i = 0; $i < count($stockerIds); $i++) {
                $thisStockerData = Stocker::where('id', $stockerIds[$i])->first();

                $loadingLinePlan = LoadingLinePlan::where("act_costing_ws", $thisStockerData->act_costing_ws)->where("line_id", $lineData['line_id'])->first();

                $isExist = LoadingLine::where("stocker_id", $stockerIds[$i])->count();

                if ($isExist < 1) {
                    if ($loadingLinePlan) {
                        array_push($loadingStockArr, [
                            "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                            "line_id" => $lineData['line_id'],
                            "loading_plan_id" => $loadingLinePlan['id'],
                            "nama_line" => $lineData['username'],
                            "stocker_id" => $thisStockerData['id'],
                            "qty" => $thisStockerData['qty_ply'],
                            "status" => "active",
                            "tanggal_loading" => $request['tanggal_loading'],
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                        ]);
                    } else {
                        $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                        $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                        $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                        $storeLoadingPlan = LoadingLinePlan::create([
                            "line_id" => $lineData['line_id'],
                            "kode" => $kodeLoadingPlan,
                            "act_costing_id" => $thisStockerData->formCut->marker->act_costing_id,
                            "act_costing_ws" => $thisStockerData->formCut->marker->act_costing_ws,
                            "buyer" => $thisStockerData->formCut->marker->buyer,
                            "style" => $thisStockerData->formCut->marker->style,
                            "color" => $thisStockerData->formCut->marker->color,
                            "tanggal" => $request['tanggal_loading'],
                        ]);

                        array_push($loadingStockArr, [
                            "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                            "line_id" => $lineData['line_id'],
                            "loading_plan_id" => $storeLoadingPlan['id'],
                            "nama_line" => $lineData['username'],
                            "stocker_id" => $thisStockerData['id'],
                            "qty" => $thisStockerData['qty_ply'],
                            "status" => "active",
                            "tanggal_loading" => $request['tanggal_loading'],
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                        ]);
                    }
                } else {
                    array_push($exist, ['stocker' => $thisStockerData['id']]);
                }
            }

            $storeLoadingStock = LoadingLine::insert($loadingStockArr);

            if (count($loadingStockArr) > 0) {
                $updateStocker = Stocker::whereIn("id", $stockerIds)->
                    update([
                        "status" => "line",
                        "latest_alokasi" => Carbon::now()
                    ]);

                $updateTrolleyStocker = TrolleyStocker::whereIn("stocker_id", $stockerIds)->
                    update([
                        "status" => "not active"
                    ]);

                if ($updateStocker) {
                    array_push($success, ['stocker' => $stockerIds]);
                } else {
                    array_push($fail, ['stocker' => $stockerIds]);
                }
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Stocker berhasil dikirim',
                'redirect' => '',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Data tidak ditemukan',
                'redirect' => '',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }
    }

    public function getStockerData($id = 0)
    {
        $scannedStocker = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.act_costing_ws,
                stocker_input.color,
                stocker_input.id_qr_stocker,
                stocker_input.size,
                stocker_input.qty_ply,
                stocker_input.status,
                form_cut_input.no_cut,
                marker_input.buyer,
                marker_input.style
            ")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
            where('id_qr_stocker', $id)->
            first();

        if ($scannedStocker) {
            if ($scannedStocker->status == "line") {
                return json_encode(
                    array(
                        'status' => 400,
                        'message' => 'Stocker sudah ada di sebuah line',
                        'data' => null,
                        'redirect' => '',
                        'additional' => []
                    )
                );
            } else if ($scannedStocker->status == "trolley") {
                return json_encode(
                    array(
                        'status' => 400,
                        'message' => 'Stocker sudah ada di sebuah troli',
                        'data' => null,
                        'redirect' => '',
                        'additional' => []
                    )
                );
            } else {
                return json_encode(
                    array(
                        'status' => 200,
                        'message' => 'Stocker berhasil ditemukan',
                        'data' => $scannedStocker,
                        'redirect' => '',
                        'additional' => []
                    )
                );
            }
        }

        return json_encode(
            array(
                'status' => 400,
                'message' => 'Stocker tidak ditemukan',
                'data' => null,
                'redirect' => '',
                'additional' => []
            )
        );
    }
}
