<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
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
        if (session('currentPipingProcess')) {
            return redirect()->route('process-piping-process', ["id" => session('currentPipingProcess')]);
        }

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->select('Id_Supplier as id', 'Supplier as buyer')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('tipe_sup', 'C')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('Supplier', 'asc')->groupBy('Id_Supplier')->get();

        return view('cutting.piping-process.create-piping-process', ['buyers' => $buyers, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piping", "subPage" => "piping-process"]);
    }

    public function process($id = 0)
    {
        $pipingProcess = PipingProcess::find($id);

        if (!$pipingProcess) {
            return redirect()->route('create-piping-process');
        }

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->select('Id_Supplier as id', 'Supplier as buyer')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('tipe_sup', 'C')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('Supplier', 'asc')->groupBy('Id_Supplier')->get();

        return view('cutting.piping-process.create-piping-process', ['piping' => $pipingProcess, 'buyers' => $buyers, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piping", "subPage" => "piping-process"]);
    }

    public function createNew() {
        session()->forget('currentPipingProcess');

        $buyers = DB::connection('mysql_sb')->table('mastersupplier')->select('Id_Supplier as id', 'Supplier as buyer')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('tipe_sup', 'C')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('Supplier', 'asc')->groupBy('Id_Supplier')->get();

        return view('cutting.piping-process.create-piping-process', ['buyers' => $buyers, 'page' => 'dashboard-cutting', "subPageGroup" => "cutting-piping", "subPage" => "piping-process"]);
    }

    public function store(Request $request) {
        switch ($request->process) {
            case 1 :
                $validatedRequest = $request->validate([
                    "kode_piping" => "required|unique:piping_process",
                    "master_piping_id" => "required",
                ]);

                $storePipingProcess = PipingProcess::create([
                    "process" => $request->process,
                    "kode_piping" => $validatedRequest["kode_piping"],
                    "master_piping_id" => $validatedRequest["master_piping_id"],
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username
                ]);

                if ($storePipingProcess) {
                    session(['currentPipingProcess' => $storePipingProcess->id]);

                    return array(
                        "status" => 200,
                        "message" => "Process Piping berhasil disimpan.",
                        "additional" => [],
                    );
                }

                break;
            case 2 :
                dd($request);

                break;
            case 3 :
                dd($request);

                break;
            default :
                return array(
                    "status" => 400,
                    "message" => "Proses tidak ditemukan",
                    "additional" => [],
                );
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

    public function item($id = 0) {
        $piping = FormCutInputDetail::selectRaw("
            form_cut_input_detail.id_item,
            form_cut_input_detail.detail_item,
            form_cut_input_detail.color_act,
            form_cut_input_detail.group_roll,
            form_cut_input_detail.lot,
            SUM(form_cut_input_detail.piping) piping,
            form_cut_input_detail.unit
        ")->
        where("form_cut_input_detail.id_roll", $id)->
        groupBy("form_cut_input_detail.form_cut_id")->
        having("piping", ">", 0)->
        first();

        return $piping;
    }

    public function itemForms($id = 0) {
        $forms = FormCutInput::select("form_cut_input.id", "form_cut_input.no_form")->
            leftJoin("form_cut_input_detail", "form_cut_input_detail.form_cut_id", "=", "form_cut_input.id")->
            where("form_cut_input_detail.id_roll", $id)->
            groupBy("form_cut_input.id")->
            get();

        return $forms;
    }

    public function itemPiping($id = 0, $idForm = 0) {
        $piping = FormCutInput::selectRaw("
                form_cut_input_detail.color_act,
                form_cut_input_detail.group_roll,
                form_cut_input_detail.lot,
                SUM(form_cut_input_detail.piping) piping,
                form_cut_input_detail.unit
            ")->
            leftJoin("form_cut_input_detail", "form_cut_input_detail.form_cut_id", "=", "form_cut_input.id")->
            where("form_cut_input_detail.id_roll", $id)->
            where("form_cut_input.id", $idForm)->
            groupBy("form_cut_input.id")->
            having("piping", ">", 0)->
            first();

        return $piping;
    }
}
