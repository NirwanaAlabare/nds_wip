<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CutPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class CutPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            $cutPlanQuery = CutPlan::selectRaw("
                cutting_plan.id,
                tgl_plan,
                DATE_FORMAT(tgl_plan, '%d-%m-%Y') tgl_plan_fix,
                no_cut_plan,
                COUNT(no_form_cut_input) total_form,
                count(IF(form_cut_input.status ='SPREADING',1,null)) total_belum,
                count(IF(form_cut_input.status ='PENGERJAAN FORM CUTTING DETAIL' or form_cut_input.status ='PENGERJAAN FORM CUTTING SPREAD' ,1,null)) total_on_progress,
                count(IF(form_cut_input.status='SELESAI PENGERJAAN',1,null)) total_beres
            ")
                ->leftJoin('form_cut_input', 'cutting_plan.no_form_cut_input', '=', 'form_cut_input.no_form')
                ->groupBy("tgl_plan", "no_cut_plan");

            return DataTables::eloquent($cutPlanQuery)->filter(function ($query) {
                $tglAwal = request('tgl_awal');
                $tglAkhir = request('tgl_akhir');

                if ($tglAwal) {
                    $query->whereRaw("tgl_plan >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("tgl_plan <= '" . $tglAkhir . "'");
                }
            }, true)->filterColumn('no_cut_plan', function ($query, $keyword) {
                $query->whereRaw("LOWER(no_cut_plan) LIKE LOWER('%" . $keyword . "%')");
            })->order(function ($query) {
                $query->orderBy('cutting_plan.updated_at', 'desc');
            })->toJson();
        }

        return view('cut-plan.cut-plan', ["page" => "dashboard-cutting"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            $thisStoredCutPlan = CutPlan::select("no_form_cut_input")->where("tgl_plan", $request->tgl_plan)->get();

            if ($thisStoredCutPlan->count() > 0) {
                foreach ($thisStoredCutPlan as $cutPlan) {
                    $additionalQuery .= " and a.no_form != '" . $cutPlan->no_form_cut_input . "' ";
                }
            }

            if ($request->tgl_form) {
                $additionalQuery .= " and tgl_form_cut = '" . $request->tgl_form . "' ";
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
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    panel,
                    b.color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    a.qty_ply,
                    b.gelar_qty,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    GROUP_CONCAT(CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC) marker_details
                FROM `form_cut_input` a
                left join marker_input b on a.id_marker = b.kode
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                where
                    a.status = 'SPREADING' and
                    b.cancel = 'N'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY b.cancel asc, a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        return view('cut-plan.create-cut-plan', ["page" => "dashboard-cutting"]);
    }

    public function getSelectedForm(Request $request, $noCutPlan = 0)
    {
        $additionalQuery = "";

        $thisStoredCutPlan = CutPlan::select("no_form_cut_input")->where("tgl_plan", $request->tgl_plan)->get();

        if ($thisStoredCutPlan->count() > 0) {
            $additionalQuery .= " and (";

            $i = 0;
            $length = $thisStoredCutPlan->count();
            foreach ($thisStoredCutPlan as $cutPlan) {
                if ($i == 0) {
                    $additionalQuery .= " a.no_form = '" . $cutPlan->no_form_cut_input . "' ";
                } else {
                    $additionalQuery .= " or a.no_form = '" . $cutPlan->no_form_cut_input . "' ";
                }

                $i++;
            }

            $additionalQuery .= " ) ";
        } else {
            $additionalQuery .= " and a.no_form = '0' ";
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
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    panel,
                    b.color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    a.qty_ply,
                    b.gelar_qty,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    GROUP_CONCAT(CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC) marker_details,
                    sum(marker_input_detail.ratio) * a.qty_ply	qty_output,
                    coalesce(sum(marker_input_detail.ratio) * c.tot_lembar_akt,0) qty_act
                FROM `form_cut_input` a
                left join marker_input b on a.id_marker = b.kode
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                left join (select no_form_cut_input,sum(lembar_gelaran) tot_lembar_akt from form_cut_input_detail
                group by no_form_cut_input) c on a.no_form = c.no_form_cut_input
                where
                    b.cancel = 'N'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY b.cancel asc, a.tgl_form_cut desc, panel asc
            ");
        return DataTables::of($data_spreading)->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dateFormat = date("dmY", strtotime($request->tgl_plan));
        $noCutPlan = "CP-" . $dateFormat;

        $success = [];
        $fail = [];
        $exist = [];

        foreach ($request->formCutPlan as $req) {
            $isExist = CutPlan::where("tgl_plan", $request->tgl_plan)->where("no_form_cut_input", $req['no_form'])->count();

            if ($isExist < 1) {
                $addToCutPlan = CutPlan::create([
                    "no_cut_plan" => $noCutPlan,
                    "tgl_plan" => $request->tgl_plan,
                    "no_form_cut_input" => $req['no_form']
                ]);

                if ($addToCutPlan) {
                    array_push($success, ['no_form' => $req['no_form']]);
                } else {
                    array_push($fail, ['no_form' => $req['no_form']]);
                }
            } else {
                array_push($exist, ['no_form' => $req['no_form']]);
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Cut Plan berhasil ditambahkan',
                'redirect' => '',
                'table' => 'datatable-selected',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Data tidak ditemukan',
                'redirect' => '',
                'table' => 'datatable-selected',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $success = [];
        $fail = [];

        foreach ($request->formCutPlan as $req) {
            $isExist = CutPlan::where("tgl_plan", $request->tgl_plan)->where("no_form_cut_input", $req['no_form'])->count();

            if ($isExist > 0) {
                $removeCutPlan = CutPlan::where("tgl_plan", $request->tgl_plan)->where("no_form_cut_input", $req['no_form'])->delete();

                if ($removeCutPlan) {
                    array_push($success, ['no_form' => $req['no_form']]);
                } else {
                    array_push($fail, ['no_form' => $req['no_form']]);
                }
            } else {
                array_push($exist, ['no_form' => $req['no_form']]);
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Cut Plan berhasil disingkirkan',
                'redirect' => '',
                'table' => 'datatable-selected',
                'additional' => ["success" => $success, "fail" => $fail],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Data tidak ditemukan',
                'redirect' => '',
                'table' => 'datatable-selected',
                'additional' => ["success" => $success, "fail" => $fail],
            );
        }
    }
}
