<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMasterPipingRequest;
use App\Models\Cutting\MasterPiping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class MasterPipingController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = MasterPiping::query();

            return DataTables::eloquent($data)->
                filter(function ($query) {
                    $tglAwal = request('tgl_awal');
                    $tglAkhir = request('tgl_akhir');

                    if ($tglAwal) {
                        $query->whereRaw("master_piping.updated_at >= '" . $tglAwal . " 00:00:00'");
                    }

                    if ($tglAkhir) {
                        $query->whereRaw("master_piping.updated_at <= '" . $tglAkhir . " 23:59:59'");
                    }
                }, true)->
                order(function ($query) {
                    $query->orderBy('master_piping.updated_at', 'desc');
                })->
                toJson();
        }

        return view('cutting.master-piping.master-piping', ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piping", "subPage" => "master-process"]);
    }

    public function create() {
        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->select('Id_Supplier as id', 'Supplier as buyer')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('tipe_sup', 'C')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('Supplier', 'asc')->groupBy('Id_Supplier')->get();

        return view('cutting.master-piping.create-master-piping', ['buyers' => $buyers, 'page' => 'dashboard-cutting', "subPageGroup" => "proses-cutting", "subPage" => "form-cut-piping"]);
    }

    public function store(StoreMasterPipingRequest $request) {
        $storeMasterPiping = MasterPiping::create([
            "buyer_id" => $request["buyer_id"],
            "buyer" => $request["buyer"],
            "act_costing_id" => $request["act_costing_id"],
            "act_costing_ws" => $request["act_costing_ws"],
            "style" => $request["style"],
            "color" => $request["color"],
            "part" => $request["part"],
            "panjang" => $request["panjang"],
            "unit" => $request["unit"],
            "created_by" => Auth::user()->id,
            "created_by_username" => Auth::user()->username
        ]);

        if ($storeMasterPiping) {

            return array(
                "status" => 200,
                "message" => "Data Master Piping berhasil disimpan.",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
        );
    }

    public function list(Request $request) {
        $data = null;
        switch ($request->data) {
            case 'buyer' :
                $data = MasterPiping::select("buyer_id", "buyer")->
                    groupBy("buyer_id")->
                    get();

                break;
            case 'worksheet' :
                $data = MasterPiping::select("act_costing_id", "act_costing_ws")->
                    where("buyer_id", $request->buyer_id)->
                    groupBy("act_costing_id")->
                    get();

                break;
            case 'color' :
                $data = MasterPiping::select("color")->
                    where("act_costing_id", $request->act_costing_id)->
                    groupBy("act_costing_id", "color")->
                    get();

                break;
            case 'part' :
                $data = MasterPiping::select("id", "part")->
                    where("act_costing_id", $request->act_costing_id)->
                    where("color", $request->color)->
                    groupBy("act_costing_id", "color", "part")->
                    get();

                break;
            default :
                $data = null;

                break;
        }

        if ($data) {
            return json_encode($data);
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
        );
    }

    public function take($id = 0) {
        $masterPiping = MasterPiping::where("id", $id)->first();

        return json_encode($masterPiping);
    }
}
