<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rack;

class DashboardController extends Controller
{
    public function dc() {
        $stockers = Stocker::select("
            stocker_input.id stocker_id,
            stocker_input.id_qr_stocker,
            stocker_input.act_costing_ws,
            stocker_input.style,
            stocker_input.color,
            stocker_input.latest_alokasi stocker_update,
            stocker_input.status,
            dc_in_input.id dc_in_id,
            dc_in_input.tujuan,
            dc_in_input.tempat,
            dc_in_input.lokasi
            (dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace) dc_in_qty,
            dc_in_input.updated_at dc_in_update,
        ")->
        leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
        leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
        get();

        dd($stockers);

        return view('dashboard', ['page' => 'dashboard-dc', 'data' => $stockers]);
    }
}
