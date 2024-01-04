<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CutPlan;
use App\Models\FormCutInput;
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
                    count(IF(form_cut_input.status ='PENGERJAAN MARKER' or form_cut_input.status ='PENGERJAAN FORM CUTTING' or form_cut_input.status ='PENGERJAAN FORM CUTTING DETAIL' or form_cut_input.status ='PENGERJAAN FORM CUTTING SPREAD' ,1,null)) total_on_progress,
                    count(IF(form_cut_input.status='SELESAI PENGERJAAN',1,null)) total_beres
                ")
                ->leftJoin('form_cut_input', 'cutting_plan.no_form_cut_input', '=', 'form_cut_input.no_form')
                ->groupBy("tgl_plan", "no_cut_plan")
                ->orderBy('tgl_plan', 'desc');

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
            })->filterColumn('tgl_plan_fix', function ($query, $keyword) {
                $query->whereRaw("LOWER(DATE_FORMAT(tgl_plan, '%d-%m-%Y')) LIKE LOWER('%" . $keyword . "%')");
            })->order(function ($query) {
                $query->orderBy('cutting_plan.updated_at', 'desc');
            })->toJson();
        }

        return view('cut-plan.cut-plan', ["page" => "dashboard-cutting", "subPageGroup" => "cuttingplan-cutting", "subPage" => "cut-plan"]);
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

            $thisStoredCutPlan = CutPlan::select("no_form_cut_input")->groupBy("no_form_cut_input")->get();

            if ($thisStoredCutPlan->count() > 0) {
                foreach ($thisStoredCutPlan as $cutPlan) {
                    $additionalQuery .= " and a.no_form != '" . $cutPlan->no_form_cut_input . "' ";
                }
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
                    b.panel,
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
                    a.tipe_form_cut,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
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
                ORDER BY b.cancel asc, a.tgl_form_cut desc, a.no_form desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        return view('cut-plan.create-cut-plan', ["page" => "dashboard-cutting", "subPageGroup" => "cuttingplan-cutting", "subPage" => "cut-plan"]);
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
                    a.tipe_form_cut,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
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
                    a.id is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY b.cancel desc, FIELD(a.status, 'PENGERJAAN FORM CUTTING', 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'), a.tgl_form_cut desc, panel asc
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
    public function update(Request $request, $id = 0)
    {
        $now = Carbon::now();

        $success = [];
        $fail = [];
        $exist = [];

        $approvedBy = Auth::user()->id;
        $approvedAt = $now;

        if (count($request['no_form_cut']) > 0) {
            foreach ($request['no_form_cut'] as $noFormId => $noFormVal) {
                $updateCutPlan = CutPlan::where('no_cut_plan', $request['manage_no_cut_plan'])->where('no_form_cut_input', $request['no_form_cut'][$noFormId])->update([
                    'app' => $request['approve'] ? ((array_key_exists($noFormId, $request['approve'])) ? $request['approve'][$noFormId] : 'N') : 'N',
                    'app_by' => $request['approve'] ? ((array_key_exists($noFormId, $request['approve'])) ? $approvedBy : null) : 'N',
                    'app_at' => $request['approve'] ? ((array_key_exists($noFormId, $request['approve'])) ? $approvedAt : null) : 'N',
                ]);

                $updateForm = FormCutInput::where('no_form', $request['no_form_cut'][$noFormId])->update([
                    'no_meja' => (array_key_exists($noFormId, $request['no_meja'])) ? $request['no_meja'][$noFormId] : null,
                    'app' => $request['approve'] ? ((array_key_exists($noFormId, $request['approve'])) ? $request['approve'][$noFormId] : 'N') : 'N',
                    'app_by' => $request['approve'] ? ((array_key_exists($noFormId, $request['approve'])) ? $approvedBy : null) : 'N',
                    'app_at' => $request['approve'] ? ((array_key_exists($noFormId, $request['approve'])) ? $approvedAt : null) : 'N',
                ]);

                if ($updateCutPlan) {
                    array_push($success, $noFormVal);
                } else {
                    array_push($fail, $noFormVal);
                }
            }

            return array(
                'status' => 200,
                'message' => 'Form berhasil diubah',
                'redirect' => '',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data tidak ditemukan',
            'redirect' => '',
            'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
        );
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

    public function getCutPlanForm(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            $cutPlanForm = CutPlan::with('formCutInput')->where("no_cut_plan", $request->no_cut_plan)->groupBy("no_form_cut_input");

            return DataTables::eloquent($cutPlanForm)->filter(function ($query) {
                $tglAwal = request('tgl_awal');
                $tglAkhir = request('tgl_akhir');
                $formInfoFilter = request('form_info_filter');
                $markerInfoFilter = request('marker_info_filter');
                $mejaFilter = request('meja_filter');
                $approveFilter = request('approve_filter');

                if ($tglAwal) {
                    $query->whereRaw("tgl_cutting >= '" . $tglAwal . "'");
                }

                if ($tglAkhir) {
                    $query->whereRaw("tgl_cutting <= '" . $tglAkhir . "'");
                }

                // dd($formInfoFilter, $markerInfoFilter, $mejaFilter, $approveFilter);

                if ($formInfoFilter) {
                    $query->whereHas('formCutInput', function ($query) use ($formInfoFilter) {
                        $query->whereRaw("(
                                LOWER(form_cut_input.tgl_form_cut) LIKE LOWER('%" . $formInfoFilter . "%') OR
                                LOWER(form_cut_input.no_form) LIKE LOWER('%" . $formInfoFilter . "%') OR
                                LOWER(form_cut_input.qty_ply) LIKE LOWER('%" . $formInfoFilter . "%') OR
                                LOWER(form_cut_input.tipe_form_cut) LIKE LOWER('%" . $formInfoFilter . "%') OR
                                LOWER(form_cut_input.status) LIKE LOWER('%" . $formInfoFilter . "%')
                            )");
                    });
                }

                if ($markerInfoFilter) {
                    $query->whereHas('formCutInput', function ($query) use ($markerInfoFilter) {
                        $query->whereHas('marker', function ($query) use ($markerInfoFilter) {
                            $query->whereRaw("(
                                    LOWER(marker_input.kode) LIKE LOWER('%" . $markerInfoFilter . "%') OR
                                    LOWER(marker_input.buyer) LIKE LOWER('%" . $markerInfoFilter . "%') OR
                                    LOWER(marker_input.act_costing_ws) LIKE LOWER('%" . $markerInfoFilter . "%') OR
                                    LOWER(marker_input.style) LIKE LOWER('%" . $markerInfoFilter . "%') OR
                                    LOWER(marker_input.color) LIKE LOWER('%" . $markerInfoFilter . "%') OR
                                    LOWER(marker_input.panel) LIKE LOWER('%" . $markerInfoFilter . "%')
                                )");
                        });
                    });
                }

                if ($mejaFilter) {
                    $query->whereHas('formCutInput', function ($query) use ($mejaFilter) {
                        $query->whereHas('alokasiMeja', function ($query) use ($mejaFilter) {
                            $query->whereRaw("(
                                    LOWER(users.name) LIKE LOWER('%" . $mejaFilter . "%') OR
                                    LOWER(users.username) LIKE LOWER('%" . $mejaFilter . "%')
                                )");
                        });
                    });
                }

                if ($approveFilter) {
                    $query->whereRaw("app = '" . $approveFilter . "'");
                }
            })->addIndexColumn()->addColumn('form_info', function ($row) {
                $totalLembar = ($row->formCutInput ? $row->formCutInput->total_lembar : 0);
                $qtyPly = ($row->formCutInput ? $row->formCutInput->qty_ply : 0);

                $formInfo = "<ul class='list-group'>";
                $formInfo = $formInfo . "<li class='list-group-item'>Tanggal Form :<br><b>" . ($row->formCutInput ? $row->formCutInput->tgl_form_cut : '-') . "</b></li>";
                $formInfo = $formInfo . "<li class='list-group-item'>No. Form :<br><b>" . $row->no_form_cut_input . "</b></li>";
                $formInfo = $formInfo . "<li class='list-group-item'>Qty Ply :<br><b>".'<div class="progress border border-sb position-relative my-1" style="min-width: 50px;height: 21px"><p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">'.($totalLembar ? $totalLembar : 0).'/'.($qtyPly ? $qtyPly : 0).'</p><div class="progress-bar" style="background-color: #75baeb;width: '.((($totalLembar ? $totalLembar : 0)/($qtyPly ? $qtyPly : 1))*100).'%" role="progressbar"></div></div>' . "</b></li>";
                $formInfo = $formInfo . "<li class='list-group-item'>Status :<br><b>" . ($row->formCutInput ? $row->formCutInput->status : '-') . "</b></li>";
                $formInfo = $formInfo . "</ul>";
                return $formInfo;
            })->addColumn('marker_info', function ($row) {
                $markerData = $row->formCutInput ? $row->formCutInput->marker : null;

                $markerInfo = "<ul class='list-group'>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Kode Marker :<br><b>" . ($markerData ? $markerData->kode : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>WS Number :<br><b>" . ($markerData ? $markerData->act_costing_ws : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Buyer :<br><b>" . ($markerData ? $markerData->buyer : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Style :<br><b>" . ($markerData ? $markerData->style : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Color :<br><b>" . ($markerData ? $markerData->color : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Panel :<br><b>" . ($markerData ? $markerData->panel . ' - ' . $markerData->urutan_marker : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Tipe Marker :<br><b>" . ($markerData ? strtoupper($markerData->tipe_marker) : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>PO :<br><b>" . ($markerData ? ($markerData->po ? $markerData->po : '-') : '-') . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Keterangan :<br><b>" . ($markerData ? ($markerData->notes ? $markerData->notes : '-') : '-') . "</b></li>";
                $markerInfo = $markerInfo . "</ul>";
                return $markerInfo;
            })->addColumn('marker_detail_info', function ($row) {
                $markerData = $row->formCutInput ? $row->formCutInput->marker : null;

                $markerInfo = "<ul class='list-group'>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Panjang : <br><b>" . ($markerData ? $markerData->panjang_marker . " " . $markerData->unit_panjang_marker . " " . $markerData->comma_marker . " " . $markerData->unit_comma_marker : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Lebar : <br><b>" . ($markerData ? $markerData->lebar_marker . " " . $markerData->unit_lebar_marker : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Gelar Qty : <br> <b>" . ($markerData ? $markerData->gelar_qty : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Urutan : <br><b>" . ($markerData ? $markerData->urutan_marker : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Cons WS : <br><b>" . ($markerData ? $markerData->cons_ws : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Cons Marker : <br><b>" . ($markerData ? $markerData->cons_marker : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Cons Piping : <br><b>" . ($markerData ? $markerData->cons_piping : "-") . "</b></li>";
                $markerInfo = $markerInfo . "<li class='list-group-item'>Gramasi : <br><b>" . ($markerData ? $markerData->gramasi : "-") . "</b></li>";
                $markerInfo = $markerInfo . "</ul>";
                return $markerInfo;
            })->addColumn('ratio_info', function ($row) {
                $markerDetailData = $row->formCutInput && $row->formCutInput->marker ? $row->formCutInput->marker->markerDetails : null;

                $markerDetailInfo = "
                        <table class='table table-bordered table-sm w-auto'>
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Ratio</th>
                                    <th>Cut</th>
                                    <th>Output</th>
                                </tr>
                            </thead>
                            <tbody>
                    ";

                if ($markerDetailData) {
                    foreach ($markerDetailData as $markerDetail) {
                        $markerDetailInfo .= "
                                <tr>
                                    <td>" . $markerDetail->size . "</td>
                                    <td>" . $markerDetail->ratio . "</td>
                                    <td>" . $markerDetail->cut_qty . "</td>
                                    <td>" . ($markerDetail->ratio * ($row->formCutInput ? $row->formCutInput->qty_ply : 1)) . "</td>
                                </tr>
                            ";
                    }
                }

                $markerDetailInfo .= "
                            </tbody>
                        </table>
                    ";

                return $markerDetailInfo;
            })->addColumn('input_no_form', function ($row) {
                $input = "<input type='hidden' class='form-control' id='no_form_cut_" . $row->id . "' name='no_form_cut[" . $row->id . "]' value='" . $row->no_form_cut_input . "'>";

                return $input;
            })->addColumn('meja', function ($row) {
                $meja = User::where('type', 'meja')->get();

                $input = "
                        <select class='form-select select2bs4' id='no_meja_" . $row->id . "' name='no_meja[" . $row->id . "]'>
                            <option value=''>Pilih Meja</option>
                    ";

                foreach ($meja as $m) {
                    $input .= "<option value='" . $m->id . "' " . ($row->formCutInput && $m->id == $row->formCutInput->no_meja ? 'class="fw-bold" selected' : '') . ">" . strtoupper($m->name) . "</option>";
                }

                $input .= "
                        </select>
                    ";

                if ($row->formCutInput && $row->formCutInput->status != 'SPREADING') {
                    $input = "
                            <input class='form-control' type='hidden' id='no_meja_" . $row->id . "' name='no_meja[" . $row->id . "]' value='" . ($row->formCutInput ? $row->formCutInput : '-')->no_meja . "' readonly>
                            <input class='form-control' type='text' value='" . ($row->formCutInput ? strtoupper($row->formCutInput->alokasiMeja ? $row->formCutInput->alokasiMeja->name : '') : '') . "' readonly>
                        ";
                }

                return $input;
            })->addColumn('approve', function ($row) {
                $input = "
                        <div class='form-check w-100 text-center'><input type='checkbox' class='form-check-input border-success' id='approve_" . $row->id . "' name='approve[" . $row->id . "]' value='Y' " . ($row->app == 'Y' ? 'checked' : '') . " " . ($row->formCutInput ? ($row->formCutInput->status != 'SPREADING' ? 'disabled' : '') : '') . "></div>
                        " . ($row->formCutInput ? ($row->formCutInput->status != 'SPREADING' ? '<input type="hidden" class="form-control" id="approve_' . $row->id . '" name="approve[' . $row->id . ']" value="' . $row->formCutInput->app . '">' : '') : '');

                return $input;
            })
            ->rawColumns(['form_info', 'marker_info', 'marker_detail_info', 'ratio_info', 'input_no_form', 'meja', 'approve'])
            ->filterColumn('marker_info', function ($query, $keyword) {
                $query->whereHas('formCutInput', function ($query) use ($keyword) {
                    $query->whereHas('marker', function ($query) use ($keyword) {
                        $query->whereRaw("(
                                marker_input.kode LIKE '%" . $keyword . "%' OR
                                marker_input.act_costing_ws LIKE '%" . $keyword . "%' OR
                                marker_input.style LIKE '%" . $keyword . "%' OR
                                marker_input.color LIKE '%" . $keyword . "%' OR
                                marker_input.panel LIKE '%" . $keyword . "%'
                            )");
                    });
                });
            })->filterColumn('form_info', function ($query, $keyword) {
                $query->whereHas('formCutInput', function ($query) use ($keyword) {
                    $query->whereRaw("(
                            form_cut_input.no_form LIKE '%" . $keyword . "%' OR
                            form_cut_input.tgl_form_cut LIKE '%" . $keyword . "%'
                        )");
                });
            })->order(function ($query) {
                $query->orderByRaw('FIELD(app, "N", "Y")')->orderBy('no_form_cut_input', 'desc');
            })->toJson();
        }
    }
}
