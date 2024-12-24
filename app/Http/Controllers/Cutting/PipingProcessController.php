<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cutting\PipingProcess;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class PipingProcessController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = PipingProcess::query();

            return DataTables::eloquent($data)->
                filter(function ($query) {
                    $tglAwal = request('tgl_awal');
                    $tglAkhir = request('tgl_akhir');

                    if ($tglAwal) {
                        $query->whereRaw("piping_process.updated_at >= '" . $tglAwal . " 00:00:00'");
                    }

                    if ($tglAkhir) {
                        $query->whereRaw("piping_process.updated_at <= '" . $tglAkhir . " 23:59:59'");
                    }
                }, true)->
                addColumn('buyer', function ($row) {
                    return $row->masterPiping->buyer;
                })->
                addColumn('act_costing_ws', function ($row) {
                    return $row->masterPiping->act_costing_ws;
                })->
                addColumn('style', function ($row) {
                    return $row->masterPiping->style;
                })->
                addColumn('color', function ($row) {
                    return $row->masterPiping->color;
                })->
                addColumn('part', function ($row) {
                    return $row->masterPiping->part;
                })->
                order(function ($query) {
                    $query->orderBy('piping_process.updated_at', 'desc');
                })->
                toJson();
        }

        return view('cutting.piping-process.piping-process', ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piping", "subPage" => "piping-process"]);
    }

    public function create() {
        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->select('Id_Supplier as id', 'Supplier as buyer')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('tipe_sup', 'C')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('Supplier', 'asc')->groupBy('Id_Supplier')->get();

        return view('cutting.piping-process.create-piping-process', ['buyers' => $buyers, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piping", "subPage" => "piping-process"]);
    }

    public function store(Request $request) {
        if ($request->process == 1) {
            $validatedRequest1 = $request->validate([
                "kode_piping" => "required|unique:piping_process",
                "master_piping_id" => "required",
            ]);

            $storePipingProcess = PipingProcess::create([
                "kode_piping" => $request["kode_piping"],
                "master_piping_id" => $request["master_piping_id"],
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username
            ]);

            if ($storePipingProcess) {

                return array(
                    "status" => 200,
                    "message" => "Process Piping berhasil disimpan.",
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

    public function generate() {
        $latestPipingProcess = PipingProcess::select("kode_piping")->orderBy("id", "desc")->first();

        $code = "PIP-".($latestPipingProcess ? (substr($latestPipingProcess->kode_piping, 4)+1) : '1');

        return json_encode($code);
    }
}
