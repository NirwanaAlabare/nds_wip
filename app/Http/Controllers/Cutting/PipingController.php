<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Piping;
use App\Models\Marker;
use App\Models\ScannedItem;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class PipingController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = Piping::selectRaw("
                form_cut_piping.id,
                form_cut_piping.tanggal_piping,
                form_cut_piping.act_costing_id,
                form_cut_piping.act_costing_ws,
                form_cut_piping.style,
                form_cut_piping.color,
                form_cut_piping.panel,
                form_cut_piping.cons_piping,
                form_cut_piping.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.lot,
                scanned_item.roll,
                form_cut_piping.qty,
                form_cut_piping.piping,
                form_cut_piping.qty_sisa,
                form_cut_piping.unit,
                operator
            ")->
            leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_piping.id_roll");

            return DataTables::eloquent($data)->filter(function ($query) {
                    $tglAwal = request('tgl_awal');
                    $tglAkhir = request('tgl_akhir');

                    if ($tglAwal) {
                        $query->whereRaw("form_cut_piping.tanggal_piping >= '" . $tglAwal . "'");
                    }

                    if ($tglAkhir) {
                        $query->whereRaw("form_cut_piping.tanggal_piping <= '" . $tglAkhir . "'");
                    }
                }, true)->order(function ($query) {
                    $query->orderBy('form_cut_piping.updated_at', 'desc');
                })->toJson();
        }

        return view('cutting.piping.piping', ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "form-cut-piping"]);
    }

    public function create() {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('cutting.piping.create-piping', ['orders' => $orders, 'page' => 'dashboard-cutting', "subPageGroup" => "proses-cutting", "subPage" => "form-cut-piping"]);
    }

    public function store(Request $request) {
        $validatedRequest = $request->validate([
            "tanggal" => "required",
            "ws_id" => "required",
            "ws" => "required",
            "buyer" => "required",
            "style" => "required",
            "color" => "required",
            "panel" => "required",
            "id_roll" => "required",
            "cons_piping" => "required|numeric|min:0",
            "qty_item" => "required|numeric|min:0",
            "piping" => "required|numeric|min:0",
            "qty_sisa" => "required|numeric|min:0",
            "unit" => "required",
            "operator" => "required",
        ]);

        if ($validatedRequest) {
            $storePiping = Piping::create([
                "tanggal_piping" => $validatedRequest['tanggal'],
                "act_costing_id" => $validatedRequest['ws_id'],
                "act_costing_ws" => $validatedRequest['ws'],
                "style" => $validatedRequest['style'],
                "color" => $validatedRequest['color'],
                "panel" => $validatedRequest['panel'],
                "id_roll" => $validatedRequest['id_roll'],
                "cons_piping" => $validatedRequest['cons_piping'],
                "qty" => $validatedRequest['qty_item'],
                "piping" => $validatedRequest['piping'],
                "qty_sisa" => $validatedRequest['qty_sisa'],
                "unit" => $validatedRequest['unit'],
                "operator" => $validatedRequest['operator']
            ]);

            if ($storePiping) {
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['id_roll']],
                    [
                        "id_item" => $request->id_item,
                        "detail_item" => $request->detail_item,
                        "lot" => $request->lot,
                        "roll" => $request->roll,
                        "roll_buyer" => $request->roll_buyer,
                        "qty" => $validatedRequest['qty_sisa'],
                        "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + ".$validatedRequest['piping']),
                        "unit" => $validatedRequest['unit']
                    ]
                );

                return array(
                    "status" => 200,
                    "message" => "Data Piping berhasil direkam.",
                    "additional" => [],
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
        );
    }

    public function getMarkerPiping(Request $request) {
        $markerPiping = Marker::selectRaw("cons_piping")->where("act_costing_id", $request->act_costing_id)->where("color", $request->color)->where("panel", $request->panel)->where("cons_piping", ">", DB::raw('0'))->first();

        return $markerPiping;
    }
}
