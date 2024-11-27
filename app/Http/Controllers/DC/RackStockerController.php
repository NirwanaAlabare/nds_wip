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
        whereRaw("
            stocker_input.status = 'non secondary' and
            stocker_input.updated_at >= '".(date('Y-m-d', strtotime('-30 days')))." 00:00:00'
        ")->
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

    public function currentRackStock(Request $request)
    {
        $racks = RackDetail::selectRaw("
                rack_detail_stocker.id as id,
                rack_detail.nama_detail_rak no_rak,
                stocker_input.id_qr_stocker no_stocker,
                marker_input.act_costing_ws no_ws,
                form_cut_input.no_cut,
                marker_input.style,
                marker_input.color,
                master_part.nama_part part,
                stocker_input.size
            ")->
            leftJoin("rack_detail_stocker", "rack_detail_stocker.detail_rack_id", "=", "rack_detail.id")->
            leftJoin("stocker_input", "stocker_input.id_qr_stocker", "=", "rack_detail_stocker.stocker_id")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            whereRaw("
                rack_detail.id is not null and
                (rack_detail_stocker.status is null or rack_detail_stocker.status = 'active') and
                stocker_input.status = 'non secondary' and
                stocker_input.updated_at >= '".(date('Y-m-d', strtotime('-30 days')))." 00:00:00'
            ")->
            where("rack_detail.id", $request->id)->
            groupBy("rack_detail_stocker.detail_rack_id", "stocker_input.form_cut_id", "stocker_input.so_det_id", "stocker_input.group_stocker", "stocker_input.ratio", "stocker_input.part_detail_id")->
            orderBy("rack_detail_stocker.updated_at", "desc")->
            get();

        return DataTables::of($racks)->toJson();
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
            "rack_detail_id" => "required",
            "stocker_kode" => "required",
            "qty_in" => "required|gt:0"
        ]);

        $rackDetail = RackDetail::where("id", $validatedRequest['rack_detail_id'])->first();

        $stockerData = Stocker::where("id_qr_stocker", $validatedRequest["stocker_kode"])->first();
        $similarStockerData = Stocker::where("form_cut_id", $stockerData->form_cut_id)->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
            get();

        $oldRackStockArr = [];
        $rackStockArr = [];

        $i = 0;
        foreach ($similarStockerData as $stocker) {
            $oldRackDetailStocker = Stocker::where("id_qr_stocker", $stocker->id_qr_stocker)->first();

            if ($oldRackDetailStocker) {
                array_push($oldRackStockArr, $oldRackDetailStocker->id_qr_stocker);
            }

            array_push($rackStockArr, [
                "detail_rack_id" => $rackDetail->id,
                "nm_rak" => $rackDetail->nama_detail_rak,
                "stocker_id" => $stocker['id_qr_stocker'],
                "qty_in" => $validatedRequest['qty_in'],
                "status" => "active",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);

            $i++;
        }

        $updateOldRackStock = RackDetailStocker::whereIn("stocker_id", $oldRackStockArr)->update(["status" => "not active"]);
        $updateStocker = Stocker::whereIn("id_qr_stocker", $oldRackStockArr)->update(["tempat" => "RAK", "lokasi" => $rackDetail->nama_detail_rak, "latest_alokasi" => Carbon::now(), "status" => "non secondary"]);
        $storeRackStock = RackDetailStocker::insert($rackStockArr);

        if (count($rackStockArr)) {
            return array(
                'status' => 200,
                'message' => 'Stocker berhasil dialokasi',
                'redirect' => '',
                'table' => 'rack-stock-datatable',
                'callback' => 'clearAll()',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal dialokasi',
            'redirect' => '',
            'table' => 'rack-stock-datatable',
            'callback' => 'clearAll()',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RackDetailStocker  $rackDetailStocker
     * @return \Illuminate\Http\Response
     */
    public function show(RackDetailStocker $rackDetailStocker)
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
        whereRaw("
            stocker_input.status = 'non secondary' and
            stocker_input.updated_at >= '".(date('Y-m-d', strtotime('-30 days')))." 00:00:00'
        ")->
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
     * @param  \App\Models\RackDetailStocker  $rackDetailStocker
     * @return \Illuminate\Http\Response
     */
    public function edit(RackDetailStocker $rackDetailStocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RackDetailStocker  $rackDetailStocker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RackDetailStocker $rackDetailStocker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RackDetailStocker  $rackDetailStocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(RackDetailStocker $rackStocker, $id)
    {
        if ($id) {
            $deleteRackStock = RackDetailStocker::where("id", $id)->delete();

            if ($deleteRackStock) {
                return array(
                    'status' => 200,
                    'message' => 'Rak Stocker berhasil dihapus',
                    'redirect' => '',
                    'table' => 'rack-stock-datatable',
                    'callback' => '',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => 400,
            'message' => 'Rak Stocker gagal dihapus',
            'redirect' => '',
            'table' => 'rack-stock-datatable',
            'callback' => '',
            'additional' => [],
        );
    }
}
