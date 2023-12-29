<?php

namespace App\Http\Controllers;

use App\Models\Trolley;
use App\Models\TrolleyStocker;
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
                join('trolley_stocker', 'trolley_stocker.trolley_id', '=', 'trolley.id')->
                join('stocker_input', 'stocker_input.id', '=', 'trolley_stocker.stocker_id')->
                join('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')->
                join('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->
                groupBy('stocker_input.act_costing_ws', 'marker_input.style', 'stocker_input.color', 'trolley.id');

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

        return view('trolley.stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley']);
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

        return view('trolley.allocate-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolleys' => $trolleys]);
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
