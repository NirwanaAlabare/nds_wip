<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\SignalBit\UserLine;
use App\Models\Cutting\PipingProcess;
use App\Models\Cutting\PipingProcessDetail;
use App\Models\Cutting\PipingLoading;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class PipingLoadingController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = PipingLoading::query();

            return DataTables::eloquent($data)->
                filter(function ($query) {
                    $tglAwal = request('tgl_awal');
                    $tglAkhir = request('tgl_akhir');

                    if ($tglAwal) {
                        $query->whereRaw("piping_loading.updated_at >= '" . $tglAwal . " 00:00:00'");
                    }

                    if ($tglAkhir) {
                        $query->whereRaw("piping_loading.updated_at <= '" . $tglAkhir . " 23:59:59'");
                    }
                }, true)->
                addColumn('buyer', function ($row) {
                    return $row->pipingProcess->masterPiping->buyer;
                })->
                addColumn('act_costing_ws', function ($row) {
                    return $row->pipingProcess->masterPiping->act_costing_ws;
                })->
                addColumn('style', function ($row) {
                    return $row->pipingProcess->masterPiping->style;
                })->
                addColumn('color', function ($row) {
                    return $row->pipingProcess->masterPiping->color;
                })->
                addColumn('part', function ($row) {
                    return $row->pipingProcess->masterPiping->part;
                })->
                addColumn('lebar_roll', function ($row) {
                    return $row->pipingProcess->lebar_roll." ".$row->pipingProcess->lebar_roll_unit;
                })->
                order(function ($query) {
                    $query->orderBy('piping_loading.updated_at', 'desc');
                })->
                toJson();
        }

        return view('cutting.piping-loading.piping-loading', ["page" => "dashboard-cutting", "subPageGroup" => "cutting-piping", "subPage" => "piping-loading"]);
    }

    public function create() {
        $lines = UserLine::select("line_id", "username")->where("Groupp", "SEWING")->whereRaw("(Locked IS NULL OR Locked != 1)")->orderBy("line_id", "asc")->get();

        return view('cutting.piping-loading.create-piping-loading', ['lines' => $lines, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piping", "subPage" => "piping-loading"]);
    }

    public function process($id = 0)
    {
        $pipingProcess = PipingProcess::find($id);

        if (!$pipingProcess) {
            session()->forget('currentPipingProcess');

            return redirect()->route('create-piping-process');
        }

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->select('Id_Supplier as id', 'Supplier as buyer')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('tipe_sup', 'C')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('Supplier', 'asc')->groupBy('Id_Supplier')->get();

        return view('cutting.piping-process.create-piping-process', ['piping' => $pipingProcess, 'buyers' => $buyers, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piping", "subPage" => "piping-process"]);
    }

    public function createNew() {
        session()->forget('currentPipingProcess');

        return redirect()->route('create-piping-process');
    }

    public function store(Request $request) {
        $validatedRequest = $request->validate([
            "piping_process_id" => "required",
            "buyer" => "required",
            "act_costing_ws" => "required",
            "style" => "required",
            "color" => "required",
            "group" => "required",
            "lot" => "required",
            "part" => "required",
            "panjang" => "required",
            "unit" => "required",
            "lebar_roll_piping" => "required",
            "lebar_roll_piping_unit" => "required",
            "panjang_roll" => "required",
            "panjang_roll_unit" => "required",
            "output_total_roll_awal" => "required",
            "output_total_roll_awal_unit" => "required",
            "output_total_roll" => "required",
            "output_total_roll_unit" => "required",
            "qty_loading" => "required",
            "qty_loading_unit" => "required",
            "estimasi_output_roll" => "required",
            "estimasi_output_roll_unit" => "required",
            "output_total_loading" => "required",
            "output_total_loading_unit" => "required",
        ]);

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
        );
    }

    public function generate() {
        $latestPipingProcess = PipingLoading::select("no_transaksi")->orderBy("id", "desc")->first();

        $code = "PO-".($latestPipingProcess ? (substr($latestPipingProcess->kode_piping, 4)+1) : '1');

        return json_encode($code);
    }

    public function getPipingProcess($id = 0) {
        $pipingProcess = PipingProcess::selectRaw("
            piping_process.id,
            master_piping.buyer,
            master_piping.act_costing_ws,
            master_piping.style,
            master_piping.color,
            master_piping.color,
            piping_process.group,
            piping_process.lot,
            master_piping.part,
            master_piping.panjang,
            master_piping.unit,
            piping_process.lebar_roll_piping,
            piping_process.lebar_roll_piping_unit,
            piping_process.qty_konversi as panjang_roll,
            piping_process.qty_konversi_unit as panjang_roll_unit,
            piping_process.output_total_roll_awal,
            piping_process.output_total_roll,
            piping_process.estimasi_output_roll,
            piping_process.estimasi_output_roll_unit
        ")->
        leftJoin("master_piping", "master_piping.id", "=", "piping_process.master_piping_id")->
        where("piping_process.kode_piping", $id)->
        first();

        return $pipingProcess;
    }
}
