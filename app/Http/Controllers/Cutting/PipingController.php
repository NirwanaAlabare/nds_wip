<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutInputDetail;
use Illuminate\Http\Request;
use App\Models\Cutting\Piping;
use App\Models\Marker\Marker;
use App\Models\Cutting\ScannedItem;
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
                COALESCE(form_cut_piping.short_roll, 0) short_roll,
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
                }, true)->
                filterColumn('id_item', function($query, $keyword) {
                    $sql = "scanned_item.id_item like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('detail_item', function($query, $keyword) {
                    $sql = "scanned_item.detail_item like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('lot', function($query, $keyword) {
                    $sql = "scanned_item.lot like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('roll', function($query, $keyword) {
                    $sql = "scanned_item.roll like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                order(function ($query) {
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
            "short_roll" => "required|numeric",
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
                "short_roll" => $validatedRequest['short_roll'],
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

    public function edit($id = 0) {
        if ($id) {
            $piping = Piping::selectRaw("form_cut_piping.*, master_sb_ws.buyer")->leftJoin("master_sb_ws", "form_cut_piping.act_costing_id", "=", "master_sb_ws.id_act_cost")->where("form_cut_piping.id", $id)->first();

            if ($piping) {
                return view('cutting.piping.edit-piping', ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "form-cut-piping", "piping" => $piping]);
            }
        }
    }

    public function update(Request $request) {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_tanggal" => "required",
            "edit_id_roll" => "required",
            "edit_id_item" => "required",
            "edit_lot" => "required",
            "edit_roll" => "required",
            "edit_roll_buyer" => "required",
            "edit_detail_item" => "required",
            "edit_ws_id" => "required",
            "edit_ws" => "required",
            "edit_color" => "required",
            "edit_panel" => "required",
            "edit_buyer" => "required",
            "edit_style" => "required",
            "edit_cons_piping" => "required",
            "edit_qty_item" => "required",
            "edit_unit" => "required",
            "edit_piping" => "required",
            "edit_qty_sisa" => "required",
            "edit_short_roll" => "required",
            "edit_operator" => "required"
        ]);

        $piping = Piping::where("id", $validatedRequest["edit_id"])->first();

        if ($piping) {
            $qty = $validatedRequest['edit_qty_sisa'];

            $formCutDetail = FormCutInputDetail::where("id_roll", $validatedRequest["edit_id_roll"])->where("created_at", ">=", $piping->created_at)->orderBy("form_cut_input_detail.created_at", "asc")->first();

            if ($formCutDetail) {
                $formCut = $formCutDetail->formCutInput;
                $pAct = $formCut->p_act + ($formCut->comma_p_act/100);
                $sambunganRoll = $formCutDetail->formCutInputDetailSambungan ? $formCutDetail->formCutInputDetailSambungan->sum("sambungan_roll") : 0;
                $shortRoll = (($pAct * $formCutDetail->lembar_gelaran) + $formCutDetail->sambungan + $formCutDetail->sisa_gelaran + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping + $formCutDetail->sisa_kain + $sambunganRoll) - $qty;

                $formCutDetail->shortRoll = $shortRoll;
                $formCutDetail->save();
            } else {
                $updateScannedItem = ScannedItem::where("id_roll", $validatedRequest["edit_id_roll"])->update([
                    "qty" => $qty
                ]);
            }

            $piping->id_roll = $validatedRequest["edit_id_roll"];
            $piping->act_costing_id = $validatedRequest["edit_ws_id"];
            $piping->act_costing_ws = $validatedRequest["edit_ws"];
            $piping->style = $validatedRequest["edit_style"];
            $piping->color = $validatedRequest["edit_color"];
            $piping->panel = $validatedRequest["edit_panel"];
            $piping->cons_piping = $validatedRequest["edit_cons_piping"];
            $piping->qty = $validatedRequest["edit_qty_item"];
            $piping->piping = $validatedRequest["edit_piping"];
            $piping->qty_sisa = $validatedRequest["edit_qty_sisa"];
            $piping->short_roll = $validatedRequest["edit_short_roll"];
            $piping->unit = $validatedRequest["edit_unit"];
            $piping->operator = $validatedRequest["edit_operator"];
            $piping->tanggal_piping = $validatedRequest["edit_tanggal"];
            $piping->save();

            return array(
                "status" => 200,
                "message" => "Data Piping berhasil diubah.",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
        );
    }

    public function destroy($id = 0) {
        if ($id) {
            $piping = Piping::where("id", $id)->first();

            if ($piping) {
                $qty = $piping->qty;

                $formCutDetail = FormCutInputDetail::where("id_roll", $piping->id_roll)->where("created_at", ">=", $piping->created_at)->orderBy("form_cut_input_detail.created_at", "asc")->first();

                if ($formCutDetail) {
                    $formCut = $formCutDetail->formCutInput;
                    $pAct = $formCut->p_act + ($formCut->comma_p_act/100);
                    $sambunganRoll = $formCutDetail->formCutInputDetailSambungan ? $formCutDetail->formCutInputDetailSambungan->sum("sambungan_roll") : 0;
                    $shortRoll = (($pAct * $formCutDetail->lembar_gelaran) + $formCutDetail->sambungan + $formCutDetail->sisa_gelaran + $formCutDetail->kepala_kain + $formCutDetail->sisa_tidak_bisa + $formCutDetail->reject + $formCutDetail->piping + $formCutDetail->sisa_kain + $sambunganRoll) - $qty;

                    $formCutDetail->qty = $qty;
                    $formCutDetail->shortRoll = $shortRoll;
                    $formCutDetail->save();
                } else {
                    $updateScannedItem = ScannedItem::where("id_roll", $piping->id_roll)->update([
                        "qty" => $qty
                    ]);
                }

                $piping->delete();

                return array(
                    "status" => 200,
                    "message" => "Data Piping berhasil diubah.",
                    'table' => 'datatable',
                    "additional" => [],
                );
            }

            return array(
                "status" => 400,
                "message" => "Terjadi Kesalahan",
                "additional" => [],
            );
        }
    }

    public function getMarkerPiping(Request $request) {
        $markerPiping = Marker::selectRaw("cons_piping")->where("act_costing_id", $request->act_costing_id)->where("color", $request->color)->where("panel", $request->panel)->where("cons_piping", ">", DB::raw('0'))->first();

        return $markerPiping;
    }
}
