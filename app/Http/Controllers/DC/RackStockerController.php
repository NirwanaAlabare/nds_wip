<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use App\Models\RackDetail;
use App\Models\RackDetailStocker;
use App\Models\Stocker;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class RackStockerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $racks = Rack::with('rackDetails')->get();

        $stockers = Stocker::selectRaw("
            CONCAT(stocker_input.id_qr_stocker) stockers,
            rack_detail_stocker.detail_rack_id,
            stocker_input.act_costing_ws,
            marker_input.buyer,
            marker_input.style,
            stocker_input.form_cut_id,
            stocker_input.color,
            stocker_input.size,
            stocker_input.so_det_id,
            form_cut_input.no_cut,
            stocker_input.shade,
            stocker_input.group_stocker,
            stocker_input.ratio,
            stocker_input.qty_ply,
            CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) as full_range
        ")->
        leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
        leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
        leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
        leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
        leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
        groupBy("rack_detail_stocker.detail_rack_id", "stocker_input.form_cut_id", "stocker_input.so_det_id", "stocker_input.group_stocker")->
        get();

        return view('dc.rack.stock-rack', ['page' => 'dashboard-dc', "subPageGroup" => "rak-dc", "subPage" => "stock-rack", 'racks' => $racks, 'stockers' => $stockers]);
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
        $racks = Rack::orderBy('nama_rak', 'asc')->get();

        return view('dc.rack.allocate-rack', ['page' => 'dashboard-dc', 'subPageGroup' => 'rak-dc', 'subPage' => 'stock-rack', 'racks' => $racks]);
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

    public function stockRackVisual() {
        $racks = Rack::with('rackDetails', 'rackDetails.rackDetailStockers')->get();

        $stockers = Stocker::selectRaw("
            rack_detail_stocker.detail_rack_id,
            stocker_input.act_costing_ws,
            marker_input.buyer,
            marker_input.style,
            stocker_input.form_cut_id,
            stocker_input.color,
            stocker_input.size,
            stocker_input.so_det_id,
            form_cut_input.no_cut,
            stocker_input.shade,
            stocker_input.group_stocker,
            stocker_input.ratio,
            stocker_input.qty_ply,
            CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) as full_range
        ")->
        leftJoin("rack_detail_stocker", "rack_detail_stocker.stocker_id", "=", "stocker_input.id_qr_stocker")->
        leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
        leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
        leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
        leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
        groupBy("rack_detail_stocker.detail_rack_id", "stocker_input.form_cut_id", "stocker_input.so_det_id", "stocker_input.group_stocker", "stocker_input.ratio")->
        get();

        return view('dc.rack.stock-rack-visual', ['page' => 'dashboard-dc', 'subPageGroup' => 'rak-dc', 'subPage' => 'stock-rack-visual', 'racks' => $racks, 'stockers' => $stockers]);
    }

    public function stockRackVisualDetail(Request $request) {
        $stocker = Stocker::selectRaw("
                CONCAT(stocker_input.id_qr_stocker, ' - ', master_part.nama_part) stocker,
                stocker_input.lokasi
            ")->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            where("form_cut_id", $request->form_cut_id)->
            where("so_det_id", $request->so_det_id)->
            where("group_stocker", $request->group_stocker)->
            where("ratio", $request->ratio)->
            get();

        return $stocker ? $stocker : null;
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
    public function destroy(TrolleyStocker $trolleyStocker)
    {
        //
    }
}
