<?php

namespace App\Http\Controllers;

use App\Models\Trolley;
use App\Models\TrolleyStocker;
use App\Models\SignalBit\UserLine;
use App\Models\Stocker;
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
        if ($request->ajax()) {
            $additionalQuery = "";

            $trolleyStock = Trolley::selectRaw("
                    trolley.id,
                    stocker_input.act_costing_ws,
                    marker_input.style,
                    stocker_input.color,
                    trolley.nama_trolley,
                    SUM(stocker_input.qty_ply) qty
                ")->
                leftJoin('trolley_stocker', 'trolley_stocker.trolley_id', '=', 'trolley.id')->
                leftJoin('stocker_input', 'stocker_input.id', '=', 'trolley_stocker.stocker_id')->
                leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')->
                leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->
                groupBy('trolley_stocker.id');

            return DataTables::eloquent($trolleyStock)
                ->filter(function ($query) {
                    if (request()->has('dateFrom') && request('dateFrom') != null && request('dateFrom') != "") {
                        $query->where("tanggal_alokasi", ">=", request('dateFrom'));
                    }

                    if (request()->has('dateTo') && request('dateTo') != null && request('dateTo') != "") {
                        $query->where("tanggal_alokasi", "<=", request('dateTo'));
                    }
                })
                ->toJson();
        }

        return view('trolley.stock-trolley.stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley']);
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

    public function allocate()
    {
        $trolleys = Trolley::orderBy('nama_trolley', 'asc')->get();

        return view('trolley.stock-trolley.allocate-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolleys' => $trolleys]);
    }

    public function allocateThis(Request $request, $id)
    {
        if ($request->ajax()) {
            $trolley = TrolleyStocker::selectRaw("
                    trolley_stocker.id,
                    stocker_input.id_qr_stocker,
                    stocker_input.act_costing_ws,
                    form_cut_input.no_cut,
                    marker_input.style,
                    stocker_input.color,
                    GROUP_CONCAT(DISTINCT master_part.nama_part) nama_part,
                    stocker_input.size
                ")->
                leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                where('trolley_id', $id)->
                groupBy('form_cut_input.no_cut', 'stocker_input.size')->
                get();

            return DataTables::of($trolley)->toJson();
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
            array_push($trolleyStockArr, [
                "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                "trolley_id" => $validatedRequest['trolley_id'],
                "stocker_id" => $stocker['id'],
                "status" => "active",
                "tanggal_alokasi" => date('Y-m-d')
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
                    "lokasi" => "trolley",
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
                    "lokasi" => "idle",
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

    public function getStockerData($id = 0)
    {
        $scannedStocker = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.act_costing_ws,
                stocker_input.color,
                stocker_input.id_qr_stocker,
                stocker_input.size,
                stocker_input.qty_ply,
                stocker_input.lokasi,
                form_cut_input.no_cut,
                marker_input.buyer,
                marker_input.style
            ")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
            where('id_qr_stocker', $id)->
            first();

        if ($scannedStocker && $scannedStocker->lokasi != "trolley") {
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
