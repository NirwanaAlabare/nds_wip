<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputLostTime;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use DB;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    public function cutting(Request $request) {
        $additionalQuery = "";

        if ($request->ajax()) {
            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.id_marker like '%" . $request->search["value"] . "%' OR
                        a.no_meja like '%" . $request->search["value"] . "%' OR
                        a.no_form like '%" . $request->search["value"] . "%' OR
                        a.tgl_form_cut like '%" . $request->search["value"] . "%' OR
                        b.act_costing_ws like '%" . $request->search["value"] . "%' OR
                        panel like '%" . $request->search["value"] . "%' OR
                        b.color like '%" . $request->search["value"] . "%' OR
                        a.status like '%" . $request->search["value"] . "%' OR
                        users.name like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $data_spreading = DB::select("
                SELECT
                    a.id,
                    a.no_meja,
                    a.id_marker,
                    a.no_form,
                    a.no_cut,
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    a.qty_ply,
                    b.gelar_qty,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ', ') marker_details,
                    cutting_plan.tgl_plan,
                    cutting_plan.app
                FROM `form_cut_input` a
                left join cutting_plan on cutting_plan.no_form_cut_input = a.no_form
                left join users on users.id = a.no_meja
                left join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                where
                    a.id is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY
                    FIELD(a.status, 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'),
                    FIELD(a.tipe_form_cut, null, 'PILOT', 'NORMAL', 'MANUAL'),
                    FIELD(a.app, 'Y', 'N', null),
                    a.no_form desc,
                    a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view('manager.cutting.cutting', ['meja' => $meja, 'page' => 'dashboard-cutting', "subPage" => "manage-cutting"]);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function detailCutting($id) {
        $formCutInputData = FormCutInput::leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->where('form_cut_input.id', $id)->first();

        $actCostingData = DB::connection("mysql_sb")->table('act_costing')->selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->groupBy('act_costing.id')->where('act_costing.id', $formCutInputData->act_costing_id)->get();

        $markerDetailData = MarkerDetail::selectRaw("
                marker_input.kode kode_marker,
                marker_input_detail.size,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            where("marker_input.kode", $formCutInputData->kode)->
            where("marker_input.cancel", "N")->
            get();

        $lostTimeData = FormCutInputLostTime::where('form_cut_input_id', $id)->get();

        return view("manager.cutting.detail-cutting", [
            'id' => $id,
            'formCutInputData' => $formCutInputData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'lostTimeData' => $lostTimeData,
            'page' => 'dashboard-cutting'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function generateStocker(Request $request, $id) {
        $generatedBy = Auth::user()->id;
        $generatedAt = Carbon::now();

        $validatedRequest = $request->validate([
            "generated_type" => "required"
        ]);

        $generateFormCut = FormCutInput::where("id", $id)->
            update([
                "generated" => $validatedRequest['generated_type'],
                "generated_by" => $generatedBy,
                "generated_at" => $generatedAt,
                "generated_notes" => $request['generated_notes'],
            ]);

        $generateFormCut = true;

        if ($generateFormCut) {
            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
