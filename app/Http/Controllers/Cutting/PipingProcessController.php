<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\Cutting\PipingProcess;
use App\Models\Cutting\PipingProcessDetail;
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
                addColumn('panjang', function ($row) {
                    return $row->masterPiping->panjang;
                })->
                addColumn('unit', function ($row) {
                    return $row->masterPiping->unit;
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
                    "tanggal" => date("Y-m-d"),
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username
                ]);

                if ($storePipingProcess) {
                    session(['currentPipingProcess' => $storePipingProcess->id]);

                    return array(
                        "status" => 200,
                        "message" => "Process Piping berhasil disimpan.",
                        "additional" => $storePipingProcess,
                    );
                }

                break;
            case 2 :
                $validatedRequest = $request->validate([
                    "id" => "required",
                    "method" => "required",
                ]);

                $idItem = $validatedRequest["method"] == "single" ? $request->id_item : implode(", ", array_unique($request->id_item_rolls));
                $group = $validatedRequest["method"] == "single" ? $request->group_item : implode(", ", array_unique($request->group_rolls));
                $lot = $validatedRequest["method"] == "single" ? $request->lot_item : implode(", ", array_unique($request->lot_rolls));
                $noRoll = $validatedRequest["method"] == "single" ? $request->no_roll_item : implode(", ", array_unique($request->no_rolls));
                $qty = $validatedRequest["method"] == "single" ? $request->qty_item : $request->total_qty;
                $unit = $validatedRequest["method"] == "single" ? $request->unit_item : $request->total_unit;
                $totalRoll = $validatedRequest["method"] == "single" ? 1 : $request->total_roll;


                $pipingProcessModel = PipingProcess::where("id", $validatedRequest["id"]);

                if ($pipingProcessModel) {
                    $storePipingProcessDetailArr = [];

                    if ($validatedRequest["method"] == "single") {
                        $validatedRequestDetail = $request->validate([
                            "id_roll" => "required",
                            "id_item" => "required",
                            "color_act" => "required",
                            "group_item" => "required",
                            "lot_item" => "required",
                            "no_roll_item" => "required",
                            "form_cut_id" => "required",
                            "no_form" => "required",
                            "qty_item" => "required",
                            "unit_item" => "required"
                        ]);

                        array_push($storePipingProcessDetailArr, [
                            "piping_process_id" => $validatedRequest["id"],
                            "id_roll" => $validatedRequestDetail["id_roll"],
                            "id_item" => $validatedRequestDetail["id_item"],
                            "color_act" => $validatedRequestDetail["color_act"],
                            "group" => $validatedRequestDetail["group_item"],
                            "lot" => $validatedRequestDetail["lot_item"],
                            "no_roll" => $validatedRequestDetail["no_roll_item"],
                            "form_cut_id" => $validatedRequestDetail["form_cut_id"],
                            "no_form" => $validatedRequestDetail["no_form"],
                            "qty" => $validatedRequestDetail["qty_item"],
                            "unit" => $validatedRequestDetail["unit_item"],
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username
                        ]);
                    } else if ($validatedRequest["method"] == "multi") {
                        for ($i = 1; $i <= count($request->id_rolls); $i++) {
                            array_push($storePipingProcessDetailArr, [
                                "piping_process_id" => $validatedRequest["id"],
                                "id_roll" => $request->id_rolls[$i],
                                "id_item" => $request->id_item_rolls[$i],
                                "color_act" => $request->color_rolls[$i],
                                "group" => $request->group_rolls[$i],
                                "lot" => $request->lot_rolls[$i],
                                "no_roll" => $request->no_rolls[$i],
                                "form_cut_id" => $request->id_form_rolls[$i],
                                "qty" => $request->qty_rolls[$i],
                                "unit" => $request->unit_rolls[$i],
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username
                            ]);
                        }
                    }

                    $storePipingProcessDetail = PipingProcessDetail::insert($storePipingProcessDetailArr);

                    if (count($storePipingProcessDetailArr) > 0) {
                        $updatePipingProcess = $pipingProcessModel->update([
                            "process" => $request->process,
                            "method" => $validatedRequest["method"],
                            "id_item" => $idItem,
                            "group" => $group,
                            "lot" => $lot,
                            "no_roll" => $noRoll,
                            "qty_awal" => $qty,
                            "unit" => $unit,
                            "panjang_roll_piping" => $pipingProcessModel->first()->masterPiping->panjang,
                            "panjang_roll_piping_unit" => $pipingProcessModel->first()->masterPiping->unit,
                            "lebar_kain_act_unit" => $unit,
                            "lebar_kain_cuttable_unit" => $unit,
                            // "lebar_roll_piping" => 0,
                            "lebar_roll_piping_unit" => $pipingProcessModel->first()->masterPiping->unit,
                            "total_roll" => $totalRoll
                        ]);

                        if ($updatePipingProcess) {
                            return array(
                                "status" => 200,
                                "message" => "Process Item Piping berhasil disimpan.",
                                "additional" => $pipingProcessModel->first(),
                            );
                        }
                    }
                }

                break;
            case 3 :
                $validatedRequest = $request->validate([
                    "id" => "required",
                    "arah_potong" => "required",
                    "group" => "required",
                    "id_item" => "required",
                    "lot" => "required",
                    "no_roll" => "required",
                    "qty_awal" => "required|gt:0",
                    "qty_awal_unit" => "required",
                    "qty" => "required|gt:0",
                    "qty_unit" => "required",
                    "qty_konversi" => "required|gt:0",
                    "qty_konversi_unit" => "required",
                    "panjang_roll_piping" => "required|gt:0",
                    "panjang_roll_piping_unit" => "required",
                    "lebar_kain_act" => "required|gt:0",
                    "lebar_kain_act_unit" => "required",
                    "lebar_kain_cuttable" => "required|gt:0",
                    "lebar_kain_cuttable_unit" => "required",
                    "lebar_roll_piping" => "required|gt:0",
                    "lebar_roll_piping_unit" => "required",
                    "output_total_roll" => "required|gt:0",
                    "jenis_potong_piping" => "required",
                    "estimasi_output_roll" => "required|gt:0",
                    "estimasi_output_roll_unit" => "required",
                    "estimasi_output_total" => "required|gt:0",
                    "estimasi_output_total_unit" => "required",
                ]);

                $pipingProcessModel = PipingProcess::where("id", $validatedRequest["id"]);

                if ($pipingProcessModel) {
                    $updatePipingProcess = $pipingProcessModel->update([
                        "process" => $request->process,
                        "arah_potong" => $validatedRequest["arah_potong"],
                        "group" => $validatedRequest["group"],
                        "id_item" => $validatedRequest["id_item"],
                        "lot" => $validatedRequest["lot"],
                        "no_roll" => $validatedRequest["no_roll"],
                        "qty_awal" => $validatedRequest["qty_awal"],
                        "qty_awal_unit" => $validatedRequest["qty_awal_unit"],
                        "qty" => $validatedRequest["qty"],
                        "qty_unit" => $validatedRequest["qty_unit"],
                        "qty_konversi" => $validatedRequest["qty_konversi"],
                        "qty_konversi_unit" => $validatedRequest["qty_konversi_unit"],
                        "panjang_roll_piping" => $validatedRequest["panjang_roll_piping"],
                        "panjang_roll_piping_unit" => $validatedRequest["panjang_roll_piping_unit"],
                        "lebar_kain_act" => $validatedRequest["lebar_kain_act"],
                        "lebar_kain_act_unit" => $validatedRequest["lebar_kain_act_unit"],
                        "lebar_kain_cuttable" => $validatedRequest["lebar_kain_cuttable"],
                        "lebar_kain_cuttable_unit" => $validatedRequest["lebar_kain_cuttable_unit"],
                        "lebar_roll_piping" => $validatedRequest["lebar_roll_piping"],
                        "lebar_roll_piping_unit" => $validatedRequest["lebar_roll_piping_unit"],
                        "output_total_roll" => $validatedRequest["output_total_roll"],
                        "jenis_potong_piping" => $validatedRequest["jenis_potong_piping"],
                        "estimasi_output_roll" => $validatedRequest["estimasi_output_roll"],
                        "estimasi_output_roll_unit" => $validatedRequest["estimasi_output_roll_unit"],
                        "estimasi_output_total" => $validatedRequest["estimasi_output_total"],
                        "estimasi_output_total_unit" => $validatedRequest["estimasi_output_total_unit"]
                    ]);

                    if ($updatePipingProcess) {
                        return array(
                            "status" => 200,
                            "message" => "Process Hitung Piping berhasil disimpan.",
                            "additional" => $pipingProcessModel->first(),
                        );
                    }
                }

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
            form_cut_input_detail.form_cut_id,
            form_cut_input_detail.no_form_cut_input,
            form_cut_input_detail.id_item,
            form_cut_input_detail.detail_item,
            form_cut_input_detail.color_act,
            form_cut_input_detail.group_roll,
            form_cut_input_detail.lot,
            COALESCE(form_cut_input_detail.roll_buyer, form_cut_input_detail.roll) no_roll,
            SUM(form_cut_input_detail.piping) piping,
            form_cut_input_detail.unit
        ")->
        where("form_cut_input_detail.id_roll", $id)->
        groupBy("form_cut_input_detail.id_roll")->
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
                form_cut_input_detail.form_cut_id,
                form_cut_input_detail.no_form_cut_input,
                form_cut_input_detail.id_item,
                form_cut_input_detail.color_act,
                form_cut_input_detail.group_roll,
                form_cut_input_detail.lot,
                COALESCE(form_cut_input_detail.roll_buyer, form_cut_input_detail.roll) no_roll,
                SUM(form_cut_input_detail.piping) piping,
                form_cut_input_detail.unit
            ")->
            leftJoin("form_cut_input_detail", "form_cut_input_detail.form_cut_id", "=", "form_cut_input.id")->
            where("form_cut_input_detail.id_roll", $id)->
            where("form_cut_input.id", $idForm)->
            groupBy("form_cut_input_detail.id_roll", "form_cut_input.id")->
            having("piping", ">", 0)->
            first();

        return $piping;
    }
}
