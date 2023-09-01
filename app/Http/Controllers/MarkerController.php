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
            table('jo_det')->
            select('act_costing.id', 'act_costing.kpno')->
            leftJoin('so', 'so.id', '=', 'jo_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->
            where('jo_det.cancel', 'N')->
            where('act_costing.status', '!=', 'CANCEL')->
            where('act_costing.cost_date', '>=', '2023-01-01')->
            where('act_costing.type_ws', 'STD')->
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
            get();

        return $order;
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

        return $colors;
    }

    public function getSizeList(Request $request)
    {
        $sizes = DB::connection('mysql_sb')->
            table('so_det')->
            selectRaw('so_det.id id, act_costing.kpno no_ws, so_det.color color, so_det.qty order_qty, so_det.size size')->
            leftJoin('so', 'so.id', '=', 'so_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->
            where('act_costing.id', $request->act_costing_id)->
            where('so_det.color', $request->color)->
            where('so_det.cancel', 'N')->
            where('so.cancel_h', 'N')->
            groupBy('so_det.size')->
            get();

        return json_encode($sizes);
    }

    public function getPanelList(Request $request)
    {
        $panels = DB::connection('mysql_sb')->
            table('masterpanel')->
            selectRaw('nama_panel panel')->
            leftJoin('bom_jo_item', 'bom_jo_item.id_panel', '=', 'masterpanel.id')->
            leftJoin('so_det', 'so_det.id', '=', 'bom_jo_item.id_so_det')->
            leftJoin('so', 'so.id', '=', 'so_det.id_so')->
            leftJoin('act_costing', 'act_costing.id', '=', 'so.id_cost')->
            leftJoin('masteritem', 'masteritem.id_gen', '=', 'bom_jo_item.id_item')->
            where('act_costing.id', $request->act_costing_id)->
            where('so_det.color', $request->color)->
            where('masteritem.mattype', 'F')->
            where('bom_jo_item.status', 'M')->
            where('bom_jo_item.cancel', 'N')->
            groupBy('id_panel')->
            get();

        return $panels;
    }

    // public function cekUrutan

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
            'ws_id' => ['required'],
            'ws' => ['required'],
            'p_marker' => ['required', 'numeric'],
            'p_unit' => ['required'],
            'comma_marker ' => ['required', 'numeric'],
            'comma_unit ' => ['required'],
            'l_marker ' => ['required', 'numeric'],
            'l_unit ' => ['required'],
            'cons_marker ' => ['required', 'numeric'],
            'gelar_marker_qty ' => ['required', 'numeric'],
            'po ' => ['required'],
            'no_urut_marker ' => ['required'],
        ]);

        Marker::all();

        $kodeMarker = sprintf('%08d', );
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
