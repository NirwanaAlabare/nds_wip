<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $markers = Marker::all();

        if ($request->ajax()) {
            $markers = Marker::all();

            return $markers;
        }

        return view('marker.marker');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $orders = DB::connection('mysql_sb')->
            table('act_costing')->
            select('id', 'kpno')->
            where('status', '!=', 'CANCEL')->
            where('cost_date', '>=', '2022-01-01')->
            where('type_ws', 'STD')->
            orderBy('cost_date', 'desc')->
            orderBy('kpno', 'asc')->
            groupBy('kpno')->
            get();

        return view('marker.create-marker', ['orders' => $orders]);
    }

    public function getOrderInfo(Request $request)
    {
        $order = DB::connection('mysql_sb')->
            table('act_costing')->
            selectRaw('act_costing.id, act_costing.kpno, act_costing.styleno, act_costing.qty order_qty, mastersupplier.supplier buyer')->
            leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer')->
            where('id', $request->act_costing_id)->
            first();

        return json_encode($order);
    }

    public function getColorList(Request $request)
    {
        $colors = DB::connection('mysql_sb')->
            table('so_det')->
            select('so_det.color')->
            leftJoin('so', 'so.id', '=', 'so_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->
            where('act_costing.id', $request->act_costing_id)->
            groupBy('so_det.color')->
            get();

        return json_encode($colors);
    }

    public function getSizeList(Request $request)
    {
        $sizesQuery = DB::connection('mysql_sb')->
            table('so_det')->
            selectRaw('so_det.id id, act_costing.kpno no_ws, so_det.color color, so_det.qty order_qty, so_det.size size')->
            leftJoin('so', 'so.id', '=', 'so_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost');

        if ($request) {
            $sizesQuery->where('act_costing.id', $request->act_costing_id)->
            where('so_det.color', $request->color);
        }

        $sizes = $sizesQuery->where('so_det.cancel', 'N')->
            where('so.cancel_h', 'N')->
            groupBy('so_det.size')->
            get();

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($sizes->count()),
            "recordsFiltered" => intval($sizes->count()),
            "data" => $sizes
        ]);
    }

    public function getPanelList(Request $request)
    {
        $panels = DB::connection('mysql_sb')->
            table('temporary_panels')->
            selectRaw('temporary_panels.nama_panel panel')->
            leftJoin('temporary_bom_items', 'temporary_bom_items.panel_id', '=', 'temporary_panels.id')->
            leftJoin('so_det', 'so_det.id', '=', 'temporary_bom_items.so_det_id')->
            leftJoin('so', 'so.id', '=', 'so_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->
            where('act_costing.id', $request->act_costing_id)->
            where('so_det.color', $request->color)->
            groupBy('temporary_panels.id')->
            get();

        return json_encode($panels);
    }

    public function getUrutanMarker(Request $request) {
        $urutanMarker = Marker::where('act_costing_id', $request->act_costing_id)->
            where('color', $request->color)->
            where('panel', $request->panel)->
            count() + 1;

        return $urutanMarker ? $urutanMarker : 1;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedRequest = $request->validate([
            'tgl_cutting' => ['required'],
            'ws' => ['required'],
            'color' => ['required'],
            'panel' => ['required'],
            'p_marker' => ['required', 'numeric'],
            'p_unit' => ['required'],
            'comma_marker ' => ['required', 'numeric'],
            'comma_unit ' => ['required'],
            'l_marker ' => ['required', 'numeric'],
            'l_unit ' => ['required'],
            'cons_marker ' => ['required', 'numeric'],
            'gelar_marker_qty ' => ['required', 'numeric'],
            'po ' => ['required'],
            'no_urut_marker ' => ['required', 'numeric'],
        ]);

        $markers = Marker::all();

        $markerCode = 'MRK/'.date('ym').'/'.sprintf('%08d', $markers->count()+1);$markers->count();

        $markerStore = Marker::create([
            'kode' => $markerCode,
            'act_costing_id' => $validatedRequest['ws'],
            'color' => $validatedRequest['color'],
            'panel' => $validatedRequest['panel'],
            'panjang_marker' => $validatedRequest['p_marker'],
            'unit_panjang_marker' => $validatedRequest['p_unit'],
            'comma_marker' => $validatedRequest['comma_marker'],
            'unit_comma_marker' => $validatedRequest['comma_unit'],
            'lebar_marker' => $validatedRequest['l_marker'],
            'unit_lebar_marke ' => $validatedRequest['l_unit'],
            'gelar_qty' => $validatedRequest['gelar_marker_qty'],
            'po_marker' => $validatedRequest['po'],
            'urut_marker ' => $validatedRequest['no_urut_marker'],
        ]);

        $markerDetailStore = Marker::create([
            'marker_id' => $markerStore->id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function show(Marker $marker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function edit(Marker $marker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Marker $marker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Marker $marker)
    {
        //
    }
}
