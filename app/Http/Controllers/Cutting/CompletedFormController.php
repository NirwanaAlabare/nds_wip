<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutInputDetailOutput;
use App\Models\Cutting\FormCutInputLostTime;
use App\Models\Cutting\FormCutAlokasiGantiRejectPanel;
use App\Models\Cutting\Piping;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\PenerimaanCutting;
use App\Models\Part\Part;
use App\Models\Part\PartForm;
use App\Models\Auth\User;
use App\Models\Stocker\ModifySizeQty;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Services\CuttingService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\StockerService;
use DB;

class CompletedFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function cutting(Request $request) {
        $additionalQuery = "";

        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

            if ($dateFrom) {
                $additionalQuery .= " and DATE(a.waktu_selesai) >= '" . $dateFrom . "' ";
            }

            if ($dateTo) {
                $additionalQuery .= " and DATE(a.waktu_selesai) <= '" . $dateTo . "' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.id_marker like '%" . $request->search["value"] . "%' OR
                        a.no_meja like '%" . $request->search["value"] . "%' OR
                        a.no_form like '%" . $request->search["value"] . "%' OR
                        COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai), a.tgl_form_cut) like '%" . $request->search["value"] . "%' OR
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
                    COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai), a.tgl_form_cut) tgl_form_cut,
                    COALESCE(a.updated_at, a.waktu_selesai) waktu_selesai,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color,
                    a.status,
                    UPPER(users.name) nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a2.total_lembar, a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    COALESCE(a.qty_ply, 0) qty_ply,
                    COALESCE(b.gelar_qty, 0) gelar_qty,
                    COALESCE(a2.total_lembar, a.total_lembar, '0') total_lembar,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(COALESCE(master_size_new.size, master_sb_ws.size, marker_input_detail.size), '(', marker_input_detail.ratio, ')') ORDER BY COALESCE(master_size_new.urutan, marker_input_detail.id) ASC SEPARATOR ', ') marker_details,
                    cutting_plan.tgl_plan,
                    cutting_plan.app
                FROM `form_cut_input` a
                    left join (select form_cut_input_detail.form_cut_id, SUM(form_cut_input_detail.lembar_gelaran) total_lembar from form_cut_input_detail group by form_cut_input_detail.form_cut_id) a2 on a2.form_cut_id = a.id
                    left join cutting_plan on cutting_plan.form_cut_id = a.id
                    left join users on users.id = a.no_meja
                    left join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                    left join marker_input_detail on b.id = marker_input_detail.marker_id
                    left join master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                    left join master_size_new on master_sb_ws.size = master_size_new.size
                where
                    a.id is not null and
                    a.status = 'SELESAI PENGERJAAN'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY
                    FIELD(a.tipe_form_cut, null, 'PILOT', 'NORMAL', 'MANUAL'),
                    FIELD(a.app, 'Y', 'N', null),
                    a.no_form desc,
                    a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view('cutting.completed-form.completed-form', ['meja' => $meja, 'page' => 'dashboard-cutting', "subPage" => "manage-cutting"]);
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
        $stockerData = Stocker::where("form_cut_id", $id)->first();

        $formCutInputData = FormCutInput::leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->where('form_cut_input.id', $id)->
            leftJoin(DB::raw("(
                    SELECT
                        *
                    FROM (
                        select
                            ROW_NUMBER() OVER (PARTITION BY marker_id ORDER BY created_at, SUBSTRING_INDEX(no_form,'-',-1)) AS nomor,
                            id,
                            no_form,
                            created_at
                        from
                            form_cut_input
                    ) form
                    WHERE
                        form.id = '".$id."'
                ) form"), "form.id", "=", "form_cut_input.id")->
            first();

        $actCostingData = DB::connection("mysql_sb")->table('act_costing')->selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->groupBy('act_costing.id')->where('act_costing.id', $formCutInputData->act_costing_id)->get();

        $markerDetailData = MarkerDetail::selectRaw("
                marker_input.kode kode_marker,
                concat(master_sb_ws.size, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN ' - ' ELSE '' END, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN master_sb_ws.dest ELSE '' END) size,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
            where("marker_input.kode", $formCutInputData->kode)->
            where("marker_input.cancel", "N")->
            groupBy("marker_input_detail.so_det_id")->
            get();

        $lostTimeData = FormCutInputLostTime::where('form_cut_input_id', $id)->get();

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view("cutting.completed-form.completed-form-detail", [
            'id' => $id,
            'meja' => $meja,
            'stockerData' => $stockerData,
            'formCutInputData' => $formCutInputData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'lostTimeData' => $lostTimeData,
            'page' => 'dashboard-cutting',
            "subPage" => "manage-cutting"
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

    public function checkStockerForm(Request $request) {
        if ($request->id) {

            // Check Closing
            $dataCheckClosing = DB::table("form_cut_input")->where("id", $request->id)->first();
            if (checkClosingDate($dataCheckClosing->waktu_selesai)) {
                return array(
                    "status" => 400,
                    "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                    "additional" => "Closing"
                );
            }

            // Check Stocker
            $stockerForm = Stocker::where('form_cut_id', $request->id)->first();

            if ($stockerForm) {
                return array(
                    'status' => 400,
                    'message' => '<b>Form sudah memiliki stocker</b> <br> <br> Jika mengubah qty form setelah ada stocker maka ada juga kemungkinan qty stocker menjadi selisih di proses selanjutnya.',
                    'redirect' => '',
                    'table' => 'datatable',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => 200,
            'message' => "It's fine",
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }

    public function updateCutting(Request $request, CuttingService $cuttingService) {
        $validatedRequest = $request->validate([
            "id" => "required",
            "current_id" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "start" => "nullable",
            "finish" => "nullable",
            "current_id_item" => "required",
            "current_detail_item" => "nullable",
            "current_group" => "required",
            "current_group_stocker" => "nullable",
            "current_roll" => "nullable",
            "current_roll_buyer" => "nullable",
            "current_qty" => "required",
            "current_qty_real" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_sambungan" => "required",
            "current_berat_amparan" => "nullable",
            "p_act" => "required"
        ]);

        // Get Current Roll
        $currentFormCutDetail = FormCutInputDetail::where('form_cut_id', $validatedRequest['id'])->
            where('id', $validatedRequest['current_id'])->
            first();

        // Check Form Cut Input Detail Output
        $formCutOutputs = FormCutInputDetailOutput::where("form_cut_input_id", $validatedRequest['id'])->get();
        foreach ($formCutOutputs as $formCutOutput) {
            if ($formCutOutput->qty_output_original != $formCutOutput->qty_output_aktual) {
                return array(
                    "status" => 400,
                    "message" => "Form sudah ada transfer switching."
                );
            }
        }

        // Check Stocker
        $stockerForm = Stocker::where('form_cut_id', $validatedRequest['id'])->first();
        Log::channel("completedFormBypassStocker")->info($stockerForm);
        if (!((Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) || (Auth::user()->id == 233 && ($currentFormCutDetail->lembar_gelaran == $validatedRequest['current_lembar_gelaran']))) && $stockerForm) {
            return array(
                'status' => 400,
                'message' => 'Form sudah memiliki stocker',
                'redirect' => '',
                'table' => 'datatable',
                'additional' => [],
            );
        }

        // Check Penerimaan
        // Current Form Detail
        $detailBefore = FormCutInputDetail::selectRaw("form_cut_input_detail.*")
            ->where('form_cut_id', $validatedRequest['id'])
            ->where('id', $validatedRequest['current_id'])
            ->first();

        if ($detailBefore && !empty($validatedRequest['current_id_roll']) && $detailBefore->id_roll != $validatedRequest['current_id_roll']) {
            // FIX: Menggunakan key spesifik 'current_id_roll' dari array $validatedRequest
            $checkPenerimaan = PenerimaanCutting::where("id_roll", $validatedRequest['current_id_roll'])->min("created_at");

            if ($checkPenerimaan && $checkPenerimaan > $detailBefore->created_at) {
                return [
                    'status' => 400,
                    'message' => "Roll ".$validatedRequest['current_id_roll']." belum ada penerimaan pada tanggal : ".$detailBefore->created_at."<br><br> Penerimaan pertama : ".$checkPenerimaan,
                    'redirect' => '',
                    'table' => 'datatable',
                    'additional' => [],
                ];
            }
        }

        // Unit roll harus sesuai dengan roll lain di form (cek saat ganti roll)
        if ($detailBefore && !empty($validatedRequest['current_id_roll']) && $detailBefore->id_roll != $validatedRequest['current_id_roll']) {
            $unitError = $this->checkRollUnit($validatedRequest['id'], $validatedRequest['current_id_roll'], $validatedRequest['current_unit'], $detailBefore->id);
            if ($unitError) {
                return $unitError;
            }
        }

        // Sambungan saat edit:
        // - sisa gelaran diisi pada roll yang belum punya roll sambungan -> roll sambungan wajib diisi & dibuat
        // - sisa gelaran dikosongkan pada roll yang punya roll sambungan -> tolak (hapus sambungannya dulu)
        $editExt = null;
        $editFormCut = null;
        $releaseRows = [];
        $releaseIdRoll = null;
        $releaseFirstId = null;
        if ($currentFormCutDetail && floatval($validatedRequest['current_sambungan']) <= 0) {
            $editFormCut = FormCutInput::where("id", $validatedRequest['id'])->first();
            [$editDetails, $editBlocks] = $this->getRollBlocks($editFormCut);

            $editBlockKey = null;
            $hasExtensionRows = false;
            $tailRows = [];
            foreach ($editBlocks as $blockKey => $blockRows) {
                $found = false;
                foreach ($blockRows as $blockRow) {
                    if ($found) {
                        $tailRows[] = $blockRow;
                        if (in_array($blockRow->status, ["extension", "extension complete"])) {
                            $hasExtensionRows = true;
                        }
                    }
                    if ($blockRow->id == $currentFormCutDetail->id) {
                        $found = true;
                        $editBlockKey = $blockKey;
                    }
                }
                if ($found) {
                    break;
                }
            }

            $requestSisaGelaran = floatval($validatedRequest['current_sisa_gelaran']);

            // Lepas sambungan: sisa gelaran dikosongkan -> baris roll sambungan (+ sisa rollnya) dihapus
            if ($requestSisaGelaran <= 0 && $hasExtensionRows) {
                foreach ($tailRows as $tailRow) {
                    if ($tailRow->status == "not complete") {
                        continue;
                    }

                    if ($tailRow->status == "need extension") {
                        return array(
                            "status" => 400,
                            "message" => "Roll sambungan <b>".$tailRow->id_roll."</b> juga memiliki sambungan.<br><br>Lepas sambungan roll <b>".$tailRow->id_roll."</b> terlebih dahulu (kosongkan Sisa Gelaran-nya), lalu ulangi.",
                        );
                    }

                    if ($tailRow->status == "extension complete" && !$releaseIdRoll) {
                        $releaseIdRoll = $tailRow->id_roll;

                        $similarReleaseBef = $releaseIdRoll ? $cuttingService->takeSimilarFormCutDetailBef($releaseIdRoll, $tailRow->created_at) : true;
                        $releaseFirstId = $similarReleaseBef ? null : $tailRow->id;
                    }

                    $releaseRows[] = $tailRow;
                }
            }

            if ($requestSisaGelaran > 0 && !$hasExtensionRows) {
                if (!$request->ext_id_roll) {
                    return array(
                        "status" => 400,
                        "message" => "Roll memiliki sisa gelaran. Harap lengkapi bagian Roll Sambungan.",
                    );
                }

                if ($editBlockKey === null || $editBlockKey === "_leading") {
                    return array(
                        "status" => 400,
                        "message" => "Posisi roll tidak ditemukan. Silahkan refresh halaman lalu coba lagi.",
                    );
                }

                // Roll sambungan disisipkan tepat setelah blok roll ini
                $editExtAt = $this->resolveInsertTime($editFormCut, $editBlockKey, 2);
                if (is_array($editExtAt)) {
                    return $editExtAt;
                }

                $editExt = $this->prepareExtension(
                    $request,
                    $editFormCut,
                    $validatedRequest['current_id_roll'],
                    ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']),
                    $requestSisaGelaran,
                    $validatedRequest['current_berat_amparan'] ?? 0,
                    $editExtAt
                );

                if (isset($editExt["status"])) {
                    return $editExt;
                }
            }
        }

        DB::beginTransaction();

        try {
            // Check Stocker
            $stockerForm = Stocker::where('form_cut_id', $validatedRequest['id'])->first();
            Log::channel("completedFormBypassStocker")->info($stockerForm);
            if (!(Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) && $stockerForm) {
                DB::rollBack();

                return array(
                    'status' => 400,
                    'message' => 'Form sudah memiliki stocker',
                    'redirect' => '',
                    'table' => 'datatable',
                    'additional' => [],
                );
            }

        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        // Check next roll qty as sisa kain
        $checkNextFormCutDetail = FormCutInputDetail::where("id_roll", $currentFormCutDetail->id_roll)->where("created_at", ">", $currentFormCutDetail->created_at)->
            where("id", "!=", $currentFormCutDetail->id)->
            orderBy("created_at", "asc")->
            first();
        if ($checkNextFormCutDetail) {
            // Fail when sisa_kain is less than next roll qty
            if ($validatedRequest['current_sisa_kain'] < $checkNextFormCutDetail->qty && $validatedRequest['current_sambungan'] < 0) {
                return array(
                    "status" => 400,
                    "message" => "Sisa Kain tidak bisa lebih kecil dari <br> Form : ".($checkNextFormCutDetail->no_form_cut_input ? $checkNextFormCutDetail->no_form_cut_input : ($checkNextFormCutDetail->formCutInput ? $checkNextFormCutDetail->formCutInput->no_form : '-'))." <br> Qty : ".$checkNextFormCutDetail->qty." ".$checkNextFormCutDetail->unit."",
                );
            }
        }

        // Update time record summary
        $updateTimeRecordSummary = FormCutInputDetail::where('form_cut_id', $validatedRequest['id'])->
            where('id', $validatedRequest['current_id'])->
            update([
                "id_roll" => $validatedRequest['current_id_roll'],
                "id_item" => $validatedRequest['current_id_item'],
                "detail_item" => $validatedRequest['current_detail_item'],
                "group_roll" => $validatedRequest['current_group'],
                "lot" => $request["current_lot"],
                "roll" => $validatedRequest['current_roll'],
                "roll_buyer" => $validatedRequest['current_roll_buyer'],
                "qty" => $itemQty,
                "unit" => $itemUnit,
                "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                "sambungan" => $validatedRequest['current_sambungan'],
                "sambungan_roll" => $request->current_total_sambungan_roll,
                "est_amparan" => $validatedRequest['current_est_amparan'],
                "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                "kepala_kain" => $validatedRequest['current_kepala_kain'],
                "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                "reject" => $validatedRequest['current_reject'],
                "sisa_kain" => $validatedRequest['current_sisa_kain'],
                "pemakaian_lembar" => $request->current_pemakaian_lembar,
                "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                "short_roll" => $validatedRequest['current_short_roll'],
                "piping" => $validatedRequest['current_piping'],
                "berat_amparan" => $validatedRequest['current_berat_amparan'],
                "status" => floatval($validatedRequest['current_sisa_gelaran']) > 0 ? "need extension" : (floatval($validatedRequest['current_sambungan']) > 0 ? "extension complete" : "complete"),
                "edited" => 1,
                "edited_by" => Auth::user()->id,
                "edited_by_username" => Auth::user()->username,
                "edited_at" => Carbon::now(),
            ]);

        $itemRemain = $validatedRequest['current_sisa_kain'];

        // Roll sambungan baru (roll diubah menjadi need extension)
        $editExtRolls = [];
        if ($editExt) {
            $editExtRolls = $this->createExtensionRows(
                $editFormCut,
                $currentFormCutDetail->id,
                $validatedRequest['current_group'],
                $editExt,
                $currentFormCutDetail->color_act,
                Auth::user(),
                $cuttingService
            );

            Log::info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Edit Roll to Need Extension Form ID : '".$validatedRequest['id']."' Detail ID : '".$currentFormCutDetail->id."' Sambungan Roll : '".$editExt["id_roll"]."' Created At : '".$editExt["at"]->format("Y-m-d H:i:s")."'");
        }

        // Lepas sambungan (roll diubah dari need extension menjadi complete)
        foreach ($releaseRows as $releaseRow) {
            $this->deleteDetailRowWithLog($releaseRow);
        }
        if (count($releaseRows) > 0) {
            Log::info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Release Sambungan Form ID : '".$validatedRequest['id']."' Detail ID : '".$currentFormCutDetail->id."' Sambungan Roll : '".$releaseIdRoll."' Deleted : ".json_encode(array_map(fn ($row) => $row->id, $releaseRows)));
        }

        // Extension Things
        if ($validatedRequest['current_sambungan'] > 0) {
            // After Extension
            $detailAfter = FormCutInputDetail::where('form_cut_id', $currentFormCutDetail->form_cut_id)->
                where('id_roll', $currentFormCutDetail->id_roll)->
                where('id', '!=', $currentFormCutDetail->id)->
                where('created_at', '>', $currentFormCutDetail->created_at)->
                orderBy('created_at', 'asc')->
                first();

            if ($detailAfter) {
                $itemRemain = $detailAfter->sisa_kain;

                $detailAfter->id_roll = $validatedRequest['current_id_roll'];
                $detailAfter->detail_item = $validatedRequest['current_detail_item'];
                $detailAfter->id_item = $validatedRequest['current_id_item'];
                $detailAfter->group_roll = $validatedRequest['current_group'];
                $detailAfter->lot = $request["current_lot"];
                $detailAfter->roll = $validatedRequest['current_roll'];
                $detailAfter->roll_buyer = $validatedRequest['current_roll_buyer'];
                $detailAfter->qty = ($itemQty-$validatedRequest['current_total_pemakaian_roll']);
                $detailAfter->short_roll = $detailAfter->total_pemakaian_roll-($itemQty-$validatedRequest['current_total_pemakaian_roll']);
                $detailAfter->berat_amparan = $validatedRequest['current_berat_amparan'];
                $detailAfter->edited = 1;
                $detailAfter->edited_by = Auth::user()->id;
                $detailAfter->edited_by_username = Auth::user()->username;
                $detailAfter->edited_at = Carbon::now();
                $detailAfter->save();
            }
        }

        // Current Form Detail
        $detail = FormCutInputDetail::selectRaw("form_cut_input_detail.*")->
            where('form_cut_id', $validatedRequest['id'])->
            where('id', $validatedRequest['current_id'])->
            first();

        if ($updateTimeRecordSummary) {
            // Update Original Scanned Item Qty
            if ($request->current_id_roll_ori && ($request->current_id_roll_ori != $validatedRequest['current_id_roll'])) {

                if ($validatedRequest['current_sambungan'] > 0) {
                    ScannedItem::where("id_roll", $request->current_id_roll_ori)->
                    update([
                        "qty" => DB::raw("COALESCE(qty, 0) + COALESCE(".(round(floatval($validatedRequest['current_total_pemakaian_roll']) + ($detailAfter ? floatval($detailAfter->qty - $detailAfter->sisa_kain) : 0), 2)).", 0)"),
                        "unit" => $request->current_unit_ori,
                    ]);
                } else {
                    // On change ID Roll
                    ScannedItem::where("id_roll", $request->current_id_roll_ori)->
                    update([
                        "qty" => DB::raw("COALESCE(qty, 0) + COALESCE(".(round(floatval($request->current_qty_ori - $request->current_sisa_kain_ori), 2)).", 0)"),
                        "unit" => $request->current_unit_ori,
                    ]);
                }
            }

            // Update Current Scanned Item Qty

            // Compare Current Form Detail to Latest ID Roll usage
            $lastFormCutDetailRoll = FormCutInputDetail::selectRaw("form_cut_input_detail.*")->
                where("id_roll", $validatedRequest['current_id_roll'])->
                orderBy("qty", "asc")->
                orderBy("created_at", "desc")->
                first();

            if (!$lastFormCutDetailRoll || ($lastFormCutDetailRoll && $lastFormCutDetailRoll->id == $detail->id)) {
                // Check penerimaan before updating
                $penerimaan = DB::table("penerimaan_cutting")->where("id_roll", $validatedRequest['current_id_roll'])->where("created_at", ">", $detail->created_at)->sum("qty_konv");

                // On exist ID Roll
                ScannedItem::where("id_roll", $validatedRequest['current_id_roll'])->
                    update([
                        "id_item" => $validatedRequest['current_id_item'],
                        "detail_item" => $validatedRequest['current_detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemRemain+$penerimaan,
                        "unit" => $itemUnit,
                    ]);

            } else {
                if ($lastFormCutDetailRoll) {
                    // Check penerimaan before updating
                    $penerimaan = DB::table("penerimaan_cutting")->where("id_roll", $validatedRequest['current_id_roll'])->where("created_at", ">", $lastFormCutDetailRoll->created_at)->sum("qty_konv");

                    ScannedItem::where("id_roll", $validatedRequest['current_id_roll'])->
                        update([
                            "id_item" => $validatedRequest['current_id_item'],
                            "detail_item" => $validatedRequest['current_detail_item'],
                            "lot" => $request['current_lot'],
                            "roll" => $validatedRequest['current_roll'],
                            "roll_buyer" => $validatedRequest['current_roll_buyer'],
                            "qty" => $lastFormCutDetailRoll->sisa_kain+$penerimaan,
                            "unit" => $itemUnit,
                        ]);
                }
            }

            // Form Cut Detail Reorder Group Stocker
            $formCutDetails = FormCutInputDetail::where("form_cut_id", $validatedRequest['id'])->orderBy("created_at", "asc")->orderBy("updated_at", "asc")->get();
            $currentGroup = "";
            $groupNumber = 0;
            foreach ($formCutDetails as $formCutDetail) {
                if ($currentGroup != $formCutDetail->group_roll) {
                    $currentGroup = $formCutDetail->group_roll;
                    $groupNumber += 1;
                }

                $formCutDetail->group_stocker = $groupNumber;
                $formCutDetail->save();
            }

            // Update Meja
            $updateFormCut = FormCutInput::where('id', $validatedRequest['id'])->
                update([
                    "no_meja" => $validatedRequest['no_meja']
                ]);

            // Form Recalculate
            $formCutInput = FormCutInput::where("id", $validatedRequest['id'])->first();

            if ($formCutInput) {
                if ($request->p_act != $formCutInput->p_act || $request->comma_act != $formCutInput->comma_p_act) {

                    if ($request->p_act && $request->p_act != $formCutInput->p_act) {
                        $formCutInput->p_act = $request->p_act;
                    }

                    if ($request->comma_act && $request->comma_act != $formCutInput->comma_p_act) {
                        $formCutInput->comma_p_act = $request->comma_act;
                    }

                    $formCutInput->save();

                    $cuttingService->recalculateForm($validatedRequest['id']);
                }
            }

            // Check if first usage
            $firstId = null;
            $similarFormCutDetailBef = $cuttingService->takeSimilarFormCutDetailBef($formCutDetail->id_roll, $formCutDetail->created_at);
            if (!$similarFormCutDetailBef) {
                $firstId = $formCutDetail->id;
            }

            if (!empty($detail->id_roll)) {
                $cuttingService->fixChainedQty($detail->id_roll, $firstId);
            }

            if (!empty($request->current_id_roll_ori)) {
                $cuttingService->fixChainedQty($request->current_id_roll_ori, null);
            }

            // Roll sambungan baru: rantai qty roll sambungan + hitung ulang sambungan form
            if ($editExt) {
                foreach ($editExtRolls as $editExtIdRoll => $editExtFirstId) {
                    $cuttingService->fixChainedQty($editExtIdRoll, $editExtFirstId);
                }

                $cuttingService->recalculateForm($validatedRequest['id']);
            }

            // Sambungan dilepas: rantai qty roll sambungan + hitung ulang form
            if (count($releaseRows) > 0) {
                if ($releaseIdRoll) {
                    $cuttingService->fixChainedQty($releaseIdRoll, $releaseFirstId);
                }

                $cuttingService->recalculateForm($validatedRequest['id']);
            }

            // generate form cut output
            $cuttingService = new CuttingService();
            $cuttingService->generateFormCutInputDetailOutput($formCutInput->id);

            DB::commit();

            $message = "alright";
            if ($editExt) {
                $message = "Roll sambungan ".$editExt["id_roll"]." berhasil ditambahkan. Roll ".$validatedRequest['current_id_roll']." sekarang need extension.";
            } else if (count($releaseRows) > 0) {
                $message = "Sambungan roll ".$releaseIdRoll." berhasil dilepas (".count($releaseRows)." baris dihapus). Roll ".$validatedRequest['current_id_roll']." sekarang complete.";
            }

            return array(
                "status" => 200,
                "message" => $message,
            );
        }

        DB::commit();

        return $detail;
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => 400,
                "message" => $e->getMessage(),
                "details" => $e
            ], 400);
        }
    }

    public function updateFinish(Request $request, $id) {
        // Check Form Cut Input Detail Output
        $formCutOutputs = FormCutInputDetailOutput::where("form_cut_input_id", $id)->get();
        foreach ($formCutOutputs as $formCutOutput) {
            if ($formCutOutput->qty_output_original != $formCutOutput->qty_output_aktual) {
                return array(
                    "status" => 400,
                    "message" => "Form sudah ada transfer switching."
                );
            }
        }

        // Stocker
        $stockerForm = Stocker::where('form_cut_id', $id)->first();
        Log::channel("completedFormBypassStocker")->info($stockerForm);
        if (!((Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) || (Auth::user()->id == 233)) && $stockerForm) {
            return array(
                'status' => 400,
                'message' => 'Form sudah memiliki stocker',
                'redirect' => '',
                'table' => 'datatable',
                'additional' => [],
            );
        }

        $formCutInputData = FormCutInput::selectRaw("form_cut_input.*, marker_input.act_costing_ws, marker_input.color, marker_input.panel")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            where("form_cut_input.id", $id)->
            first();

        $updateFormCutInput = FormCutInput::where("id", $id)->update([
            "cons_act" => $request->consAct,
            "unit_cons_act" => $request->unitConsAct,
            "cons_act_nosr" => $request->consActNoSr,
            "unit_cons_act_nosr" => $request->unitConsActNoSr,
            "cons_ws_uprate" => $request->consWsUprate,
            "cons_marker_uprate" => $request->consMarkerUprate,
            "cons_ws_uprate_nosr" => $request->consWsUprateNoSr,
            "cons_marker_uprate_nosr" => $request->consMarkerUprateNoSr,
            "total_lembar" => $request->totalLembar,
            "operator" => $request->operator,
        ]);

        // store to part form
        $partData = Part::select('part.id')->
            where("act_costing_id", $formCutInputData->marker->act_costing_id)->
            where("act_costing_ws", $formCutInputData->marker->act_costing_ws)->
            where("panel", $formCutInputData->marker->panel)->
            where("buyer", $formCutInputData->marker->buyer)->
            where("style", $formCutInputData->marker->style)->
            first();

        if ($updateFormCutInput) {
            if ($partData) {
                $checkPartForm = PartForm::where("form_id", $formCutInputData->id)->first();

                if (!$checkPartForm) {
                    $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
                    $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
                    $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

                    $addToPartForm = PartForm::create([
                        "kode" => $kodePartForm,
                        "part_id" => $partData->id,
                        "form_id" => $formCutInputData->id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                } else {
                    // Reorder Stocker
                    ini_set('max_execution_time', 360000);

                    // $stockerService->reorderStockerNumbering($partData->id, $formCutInputData->color);
                }
            }

            // generate form cut output
            $cuttingService = new CuttingService();
            $cuttingService->generateFormCutInputDetailOutput($id);

            return array(
                "status" => 200,
                "message" => "alright",
            );
        }

        return array(
            "status" => 400,
            "message" => "Data tidak berubah.",
        );
    }

    public function updateDetail(Request $request, CuttingService $cuttingService) {

        // Check Closing
        $dataCheckClosing = DB::table("form_cut_input")->where("id", $request->id)->first();
        if (checkClosingDate($dataCheckClosing->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            );
        }

        $validatedRequest = $request->validate([
            "id" => "required",
            "no_form_cut_input" => "required",
            "p_act" => "required",
            "comma_act" => "required",
            "l_act" => "required",
        ]);

        // Check Stocker
        $stockerForm = Stocker::where('form_cut_id', $validatedRequest['id'])->first();
        Log::channel("completedFormBypassStocker")->info($stockerForm);
        if (!(Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) && $stockerForm) {
            return array(
                'status' => 400,
                'message' => 'Form sudah memiliki stocker',
                'redirect' => '',
                'table' => 'datatable',
                'additional' => [],
            );
        }

        // Check Form Cut Input Detail Output
        $formCutOutputs = FormCutInputDetailOutput::where("form_cut_input_id", $validatedRequest['id'])->get();
        foreach ($formCutOutputs as $formCutOutput) {
            if ($formCutOutput->qty_output_original != $formCutOutput->qty_output_aktual) {
                return array(
                    "status" => 400,
                    "message" => "Form sudah ada transfer switching."
                );
            }
        }

        // Form Recalculate
        $formCutInput = FormCutInput::where("id", $validatedRequest['id'])->first();

        if ($formCutInput) {
            if ($validatedRequest['p_act'] != $formCutInput->p_act || $validatedRequest['comma_act'] != $formCutInput->comma_p_act || $validatedRequest['l_act'] != $formCutInput->l_act) {

                if ($validatedRequest['p_act'] != null && $validatedRequest['p_act'] != $formCutInput->p_act) {
                    $formCutInput->p_act = $validatedRequest['p_act'];
                }

                if ($validatedRequest['comma_act'] != null && $validatedRequest['comma_act'] != $formCutInput->comma_p_act) {
                    $formCutInput->comma_p_act = $validatedRequest['comma_act'];
                }

                if ($validatedRequest['l_act'] != null && $validatedRequest['l_act'] != $formCutInput->l_act) {
                    $formCutInput->l_act = $validatedRequest['l_act'];
                }

                $formCutInput->save();

                $cuttingService->recalculateForm($validatedRequest['id']);
            }

            // generate form cut output
            $cuttingService = new CuttingService();
            $cuttingService->generateFormCutInputDetailOutput($formCutInput->id);

            return array(
                "status" => 200,
                "message" => "alright",
            );
        }

        return array(
            "status" => 400,
            "message" => "Data tidak berubah.",
        );
    }

    public function updateHeader(Request $request) {

        // Check Closing
        $dataCheckClosing = DB::table("form_cut_input")->where("id", $request->id)->first();
        if (checkClosingDate($dataCheckClosing->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            );
        }

        $validatedRequest = $request->validate([
            "id" => "required",
            "no_form_cut_input" => "required",
            "no_meja" => "required",
            "qty_ply" => "required"
        ]);

        // // Check Stocker
        // $stockerForm = Stocker::where('form_cut_id', $validatedRequest['id'])->first();
        // Log::channel("completedFormBypassStocker")->info($stockerForm);
        // if (!(Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) && $stockerForm) {
        //     return array(
        //         'status' => 400,
        //         'message' => 'Form sudah memiliki stocker',
        //         'redirect' => '',
        //         'table' => 'datatable',
        //         'additional' => [],
        //     );
        // }

        // // Check Form Cut Input Detail Output
        // $formCutOutputs = FormCutInputDetailOutput::where("form_cut_input_id", $validatedRequest['id'])->get();
        // foreach ($formCutOutputs as $formCutOutput) {
        //     if ($formCutOutput->qty_output_original != $formCutOutput->qty_output_aktual) {
        //         return array(
        //             "status" => 400,
        //             "message" => "Form sudah ada transfer switching."
        //         );
        //     }
        // }

        // Form Recalculate
        $formCutInput = FormCutInput::where("id", $validatedRequest['id'])->first();

        if ($formCutInput) {
            if ($request->finish < ($request->start ?? $formCutInput->waktu_mulai)) {
                return array(
                    "status" => 400,
                    "message" => "Waktu selesai tidak bisa kurang dari Waktu Mulai : '".($request->start ?? $formCutInput->waktu_mulai)."'"
                );
            }

            if ($validatedRequest['no_meja'] != $formCutInput->no_meja || $validatedRequest['qty_ply'] != $formCutInput->qty_ply || ($request->start && $request->start != $formCutInput->waktu_mulai) || ($request->finish && $request->finish != $formCutInput->waktu_selesai)) {

                if ($validatedRequest['no_meja'] && $validatedRequest['no_meja'] != $formCutInput->no_meja) {
                    $formCutInput->no_meja = $validatedRequest['no_meja'];
                }

                if ($validatedRequest['qty_ply'] && $validatedRequest['qty_ply'] != $formCutInput->no_meja) {
                    $formCutInput->qty_ply = $validatedRequest['qty_ply'];
                }

                if ($request->start && $request->start != $formCutInput->waktu_mulai) {
                    $formCutInput->waktu_mulai = $request->start;
                }

                if ($request->finish && $request->finish != $formCutInput->waktu_selesai) {
                    $formCutInput->waktu_selesai = $request->finish;
                }

                $formCutInput->edited = 1;
                $formCutInput->edited_by = Auth::user()->id;
                $formCutInput->edited_by_username = Auth::user()->username;
                $formCutInput->edited_at = Carbon::now();
                $formCutInput->edit_notes .= ($formCutInput->edit_notes ? " | " : "")."Edit Waktu Mulai to ".$formCutInput->waktu_mulai."  |  Edit Waktu Selesai to ".$formCutInput->waktu_selesai."  |  Edit Meja to ".$formCutInput->no_meja." by ".Auth::user()->username." on ".Carbon::now()->format("d/m/Y H:i:s");
                $formCutInput->save();
            }

            // generate form cut output
            $cuttingService = new CuttingService();//
            $cuttingService->generateFormCutInputDetailOutput($formCutInput->id);

            return array(
                "status" => 200,
                "message" => "alright",
            );
        }

        return array(
            "status" => 400,
            "message" => "Data tidak berubah.",
        );
    }

    /**
     * Atur ulang urutan roll pada form dengan menukar created_at.
     *
     * "order" berisi id baris kepala (baris yang tampil sebagai roll di summary) sesuai urutan baru.
     * Baris lanjutan sambungan (setelah 'need extension') dan baris tersembunyi ('extension' / 'not complete')
     * selalu ikut pindah bersama baris kepala sebelumnya.
     */
    public function reorderRoll(Request $request, CuttingService $cuttingService) {
        $validatedRequest = $request->validate([
            "id" => "required",
            "order" => "required|array|min:1",
            "order.*" => "required|string",
        ]);

        $formCut = FormCutInput::where("id", $validatedRequest["id"])->first();
        if (!$formCut) {
            return array(
                "status" => 400,
                "message" => "Form tidak ditemukan.",
            );
        }

        $notEditable = $this->checkFormDetailEditable($formCut);
        if ($notEditable) {
            return $notEditable;
        }

        [$details, $blocks] = $this->getRollBlocks($formCut);

        $order = array_map("strval", $validatedRequest["order"]);
        $headIds = array_map("strval", array_values(array_filter(array_keys($blocks), fn ($key) => $key !== "_leading")));

        $sortedOrder = $order;
        $sortedHeadIds = $headIds;
        sort($sortedOrder);
        sort($sortedHeadIds);
        if ($sortedOrder !== $sortedHeadIds) {
            return array(
                "status" => 400,
                "message" => "Data roll sudah berubah. Silahkan refresh halaman lalu coba lagi.",
            );
        }

        // Susun urutan baru
        $newRows = isset($blocks["_leading"]) ? $blocks["_leading"] : [];
        foreach ($order as $headId) {
            foreach ($blocks[$headId] as $row) {
                $newRows[] = $row;
            }
        }

        // Roll yang sama tidak boleh berubah urutan relatifnya (sisa kain roll mengalir ke pemakaian berikutnya)
        $oldSequence = [];
        foreach ($details as $detail) {
            $oldSequence[$detail->id_roll][] = $detail->id;
        }
        $newSequence = [];
        foreach ($newRows as $row) {
            $newSequence[$row->id_roll][] = $row->id;
        }
        foreach ($oldSequence as $idRoll => $ids) {
            if ($idRoll && $newSequence[$idRoll] !== $ids) {
                return array(
                    "status" => 400,
                    "message" => "Roll ".$idRoll." dipakai lebih dari sekali di form ini, urutan pemakaiannya tidak boleh dibalik.",
                );
            }
        }

        // Pool created_at lama, dibuat naik tegas agar urutan tidak ambigu
        $pool = [];
        foreach ($details as $detail) {
            $time = Carbon::parse($detail->created_at);
            if (count($pool) > 0 && $time->lte(end($pool))) {
                $time = end($pool)->copy()->addSecond();
            }
            $pool[] = $time;
        }

        // Tolak jika roll yang created_at-nya berubah sudah punya penerimaan cutting setelah detail tsb
        foreach ($newRows as $index => $row) {
            if (!$row->id_roll) {
                continue;
            }

            $oldCreatedAt = Carbon::parse($row->created_at);
            $newCreatedAt = $pool[$index];

            if ($oldCreatedAt->format("Y-m-d H:i:s") == $newCreatedAt->format("Y-m-d H:i:s")) {
                continue;
            }

            $penerimaan = DB::table("penerimaan_cutting")->
                where("id_roll", $row->id_roll)->
                where("created_at", ">", $oldCreatedAt->min($newCreatedAt)->format("Y-m-d H:i:s"))->
                first();

            if ($penerimaan) {
                return array(
                    "status" => 400,
                    "message" => "Roll ".$row->id_roll." sudah memiliki penerimaan cutting setelah waktu gelar, urutan tidak dapat diubah.",
                );
            }

            // Pemakaian roll yang sama di form lain / piping tidak boleh terlewati (rantai qty roll berubah)
            $from = $oldCreatedAt->min($newCreatedAt)->format("Y-m-d H:i:s");
            $to = $oldCreatedAt->max($newCreatedAt)->format("Y-m-d H:i:s");
            $otherUsage = DB::table("form_cut_input_detail")->
                where("id_roll", $row->id_roll)->
                where("form_cut_id", "!=", $formCut->id)->
                whereBetween("created_at", [$from, $to])->
                exists() ||
                DB::table("form_cut_piping")->
                where("id_roll", $row->id_roll)->
                whereBetween("created_at", [$from, $to])->
                exists();

            if ($otherUsage) {
                return array(
                    "status" => 400,
                    "message" => "Roll ".$row->id_roll." dipakai di form / piping lain di antara waktu gelar lama dan baru, urutan tidak dapat diubah.",
                );
            }
        }

        DB::beginTransaction();
        try {
            foreach ($newRows as $index => $row) {
                $newCreatedAt = $pool[$index]->format("Y-m-d H:i:s");

                if (Carbon::parse($row->created_at)->format("Y-m-d H:i:s") != $newCreatedAt) {
                    DB::table("form_cut_input_detail")->where("id", $row->id)->update(["created_at" => $newCreatedAt]);
                }
            }

            Log::info("User : '".Auth::user()->id."' Username : '".Auth::user()->username."' Reorder Roll Form ID : '".$formCut->id."' No. Form : '".$formCut->no_form."' Order : ".json_encode(array_map(fn ($row) => $row->id, $newRows)));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => "Gagal menyimpan urutan roll. ".$e->getMessage(),
            );
        }

        $cuttingService->afterFormDetailChanged($formCut->id);

        return array(
            "status" => 200,
            "message" => "Urutan roll berhasil disimpan.",
        );
    }

    /**
     * Waktu (created_at) untuk roll yang akan disisipkan.
     */
    public function getInsertTime(Request $request) {
        $formCut = FormCutInput::where("id", $request->id)->first();
        if (!$formCut) {
            return array(
                "status" => 400,
                "message" => "Form tidak ditemukan.",
            );
        }

        $insertAt = $this->resolveInsertTime($formCut, $request->insert_after);
        if (is_array($insertAt)) {
            return $insertAt;
        }

        return array(
            "status" => 200,
            "insert_at" => $insertAt->format("Y-m-d H:i:s"),
        );
    }

    /**
     * Tambah roll (status complete) ke form yang sudah selesai, disisipkan setelah roll tertentu.
     */
    public function storeAddedRoll(Request $request, CuttingService $cuttingService) {
        $validatedRequest = $request->validate([
            "id" => "required",
            "insert_after" => "nullable|string",
            "current_id_roll" => "nullable",
            "current_id_item" => "required",
            "current_detail_item" => "nullable",
            "current_group" => "required",
            "current_roll" => "nullable",
            "current_roll_buyer" => "nullable",
            "current_qty" => "required|numeric|gt:0",
            "current_qty_real" => "required|numeric|gt:0",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required|numeric",
            "current_sambungan" => "required|numeric",
            "current_est_amparan" => "required|numeric",
            "current_lembar_gelaran" => "required|numeric|gt:0",
            "current_kepala_kain" => "required|numeric",
            "current_sisa_tidak_bisa" => "required|numeric",
            "current_reject" => "required|numeric",
            "current_sisa_kain" => "required|numeric|min:0",
            "current_pemakaian_lembar" => "required|numeric",
            "current_total_pemakaian_roll" => "required|numeric",
            "current_short_roll" => "required|numeric",
            "current_piping" => "required|numeric",
            "current_berat_amparan" => "nullable",
        ], [
            'required' => 'Harap tentukan :attribute.',
        ]);

        // Roll sambungan (wajib jika roll pertama punya sisa gelaran)
        $hasExtension = floatval($validatedRequest["current_sisa_gelaran"]) > 0;

        $formCut = FormCutInput::where("id", $validatedRequest["id"])->first();
        if (!$formCut) {
            return array(
                "status" => 400,
                "message" => "Form tidak ditemukan.",
            );
        }

        $notEditable = $this->checkFormDetailEditable($formCut);
        if ($notEditable) {
            return $notEditable;
        }

        // Roll pertama tidak boleh berupa sambungan
        if (floatval($validatedRequest["current_sambungan"]) > 0) {
            return array(
                "status" => 400,
                "message" => "Roll yang ditambahkan tidak bisa berupa sambungan. Isi sisa gelaran lalu lengkapi bagian Roll Sambungan.",
            );
        }

        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        if (floatval($validatedRequest["current_sisa_kain"]) > $itemQty) {
            return array(
                "status" => 400,
                "message" => "Sisa kain tidak bisa lebih besar dari qty roll.",
            );
        }

        $insertAt = $this->resolveInsertTime($formCut, $validatedRequest["insert_after"] ?? null, $hasExtension ? 3 : 1);
        if (is_array($insertAt)) {
            return $insertAt;
        }

        $idRoll = $validatedRequest["current_id_roll"] && $validatedRequest["current_id_roll"] != "-" ? $validatedRequest["current_id_roll"] : null;

        // Unit roll harus sesuai dengan roll lain di form
        $unitError = $this->checkRollUnit($formCut->id, $idRoll ?? "-", $validatedRequest["current_unit"]);
        if ($unitError) {
            return $unitError;
        }

        if ($idRoll) {
            // Roll harus sudah diterima cutting sebelum waktu gelar
            $checkPenerimaan = PenerimaanCutting::where("id_roll", $idRoll)->min("created_at");
            if ($checkPenerimaan && $checkPenerimaan > $insertAt->format("Y-m-d H:i:s")) {
                return array(
                    "status" => 400,
                    "message" => "Roll ".$idRoll." belum ada penerimaan pada : ".$insertAt->format("Y-m-d H:i:s")."<br><br> Penerimaan pertama : ".$checkPenerimaan,
                );
            }
        }

        // Roll sambungan
        $ext = null;
        if ($hasExtension) {
            $ext = $this->prepareExtension(
                $request,
                $formCut,
                $idRoll,
                $itemUnit,
                $validatedRequest["current_sisa_gelaran"],
                $validatedRequest["current_berat_amparan"] ?? 0,
                $insertAt->copy()->addSecond()
            );

            if (isset($ext["status"])) {
                return $ext;
            }
        }

        $user = Auth::user();

        $colorAct = FormCutInputDetail::where("form_cut_id", $formCut->id)->whereNotNull("color_act")->value("color_act");

        DB::beginTransaction();
        try {
            $newDetail = FormCutInputDetail::create([
                "form_cut_id" => $formCut->id,
                "no_form_cut_input" => $formCut->no_form,
                "id_roll" => $idRoll,
                "id_item" => $validatedRequest["current_id_item"],
                "color_act" => $colorAct,
                "detail_item" => $validatedRequest["current_detail_item"],
                "group_roll" => $validatedRequest["current_group"],
                "lot" => $request["current_lot"],
                "roll" => $validatedRequest["current_roll"],
                "roll_buyer" => $validatedRequest["current_roll_buyer"],
                "qty" => $itemQty,
                "unit" => $itemUnit,
                "sisa_gelaran" => $validatedRequest["current_sisa_gelaran"],
                "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                "sambungan" => 0,
                "est_amparan" => $validatedRequest["current_est_amparan"],
                "lembar_gelaran" => $validatedRequest["current_lembar_gelaran"],
                "average_time" => "00:00",
                "kepala_kain" => $validatedRequest["current_kepala_kain"],
                "sisa_tidak_bisa" => $validatedRequest["current_sisa_tidak_bisa"],
                "reject" => $validatedRequest["current_reject"],
                "sisa_kain" => $validatedRequest["current_sisa_kain"],
                "pemakaian_lembar" => $validatedRequest["current_pemakaian_lembar"],
                "short_roll" => $validatedRequest["current_short_roll"],
                "piping" => $validatedRequest["current_piping"],
                "total_pemakaian_roll" => $validatedRequest["current_total_pemakaian_roll"],
                "status" => $hasExtension ? "need extension" : "complete",
                "metode" => $idRoll ? "scan" : "manual",
                "berat_amparan" => $itemUnit == 'KGM' ? ($validatedRequest["current_berat_amparan"] ?? 0) : 0,
                "created_by" => $user ? $user->id : null,
                "created_by_username" => $user ? $user->username : null,
                "edited" => 1,
                "edited_by" => $user ? $user->id : null,
                "edited_by_username" => $user ? $user->username : null,
                "edited_at" => Carbon::now(),
                "created_at" => $insertAt,
                "updated_at" => $insertAt,
            ]);

            // Pastikan roll ada di scanned_item (qty dibetulkan oleh fixChainedQty)
            $rolls = [];
            if ($idRoll) {
                ScannedItem::firstOrCreate(
                    ["id_roll" => $idRoll],
                    [
                        "id_item" => $validatedRequest["current_id_item"],
                        "color" => $colorAct,
                        "detail_item" => $validatedRequest["current_detail_item"],
                        "lot" => $request["current_lot"],
                        "roll" => $validatedRequest["current_roll"],
                        "roll_buyer" => $validatedRequest["current_roll_buyer"],
                        "qty" => $validatedRequest["current_sisa_kain"],
                        "qty_pakai" => $validatedRequest["current_total_pemakaian_roll"],
                        "unit" => $itemUnit,
                    ]
                );

                // Pemakaian pertama roll ini?
                $similarFormCutDetailBef = $cuttingService->takeSimilarFormCutDetailBef($idRoll, $insertAt->format("Y-m-d H:i:s"));
                $rolls[$idRoll] = $similarFormCutDetailBef ? null : $newDetail->id;
            }

            if ($ext) {
                foreach ($this->createExtensionRows($formCut, $newDetail->id, $validatedRequest["current_group"], $ext, $colorAct, $user, $cuttingService) as $extIdRoll => $extFirstId) {
                    $rolls[$extIdRoll] = $extFirstId;
                }
            }

            $cuttingService->afterFormDetailChanged($formCut->id, $rolls);

            Log::info("User : '".$user->id."' Username : '".$user->username."' Add Roll Form ID : '".$formCut->id."' No. Form : '".$formCut->no_form."' Detail ID : '".$newDetail->id."' ID Roll : '".$idRoll."' Sambungan Roll : '".($ext ? $ext["id_roll"] : "")."' Created At : '".$insertAt->format("Y-m-d H:i:s")."'");

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => "Gagal menambah roll. ".$e->getMessage(),
            );
        }

        return array(
            "status" => 200,
            "message" => "Roll berhasil ditambahkan.",
        );
    }

    /**
     * Validasi & hitung data roll sambungan (field ext_*) untuk roll yang punya sisa gelaran.
     * Baris sambungan diletakkan di $extAt, sisa rollnya di $extAt + 1 detik.
     *
     * @return array data sambungan, atau array error (punya key "status")
     */
    private function prepareExtension(Request $request, $formCut, $firstIdRoll, $firstUnit, $sisaGelaran, $firstBeratAmparan, Carbon $extAt) {
        $extRequest = $request->validate([
            "ext_id_roll" => "required",
            "ext_id_item" => "required",
            "ext_detail_item" => "nullable",
            "ext_lot" => "nullable",
            "ext_roll" => "nullable",
            "ext_roll_buyer" => "nullable",
            "ext_qty" => "required|numeric|gt:0",
            "ext_qty_real" => "required|numeric|gt:0",
            "ext_unit" => "required",
            "ext_lembar_sambungan" => "required|numeric|gt:0",
            "ext_lembar_gelaran" => "required|numeric|min:0",
            "ext_kepala_kain" => "required|numeric|min:0",
            "ext_sisa_tidak_bisa" => "required|numeric|min:0",
            "ext_reject" => "required|numeric|min:0",
            "ext_piping" => "required|numeric|min:0",
            "ext_sisa_kain" => "required|numeric|min:0",
            "ext_berat_amparan" => "nullable|numeric",
        ], [
            'required' => 'Harap tentukan :attribute (roll sambungan).',
        ]);

        $extIdRoll = $extRequest["ext_id_roll"] != "-" ? $extRequest["ext_id_roll"] : null;
        if (!$extIdRoll) {
            return array(
                "status" => 400,
                "message" => "Harap isi ID Roll sambungan.",
            );
        }

        if ($firstIdRoll && $extIdRoll == $firstIdRoll) {
            return array(
                "status" => 400,
                "message" => "Roll sambungan harus berbeda dengan roll yang disambung.",
            );
        }

        $checkPenerimaan = PenerimaanCutting::where("id_roll", $extIdRoll)->min("created_at");
        if ($checkPenerimaan && $checkPenerimaan > $extAt->format("Y-m-d H:i:s")) {
            return array(
                "status" => 400,
                "message" => "Roll ".$extIdRoll." belum ada penerimaan pada : ".$extAt->format("Y-m-d H:i:s")."<br><br> Penerimaan pertama : ".$checkPenerimaan,
            );
        }

        // Unit roll sambungan harus sekelompok dengan roll yang disambung
        if ($this->rollUnitGroup($extRequest["ext_unit"]) != $this->rollUnitGroup($firstUnit)) {
            return array(
                "status" => 400,
                "title" => "Unit roll tidak sesuai",
                "message" => $this->rollUnitMessage($extIdRoll, $extRequest["ext_unit"], $firstUnit),
            );
        }

        $extQty = ($extRequest["ext_unit"] != "KGM" ? floatval($extRequest['ext_qty']) : floatval($extRequest['ext_qty_real']));
        $extUnit = ($extRequest["ext_unit"] != "KGM" ? "METER" : $extRequest['ext_unit']);

        // Panjang sambungan = panjang amparan roll yang disambung - sisa gelaran
        $pAct = $formCut->p_act + ($formCut->comma_p_act / 100);
        if ($firstUnit == "KGM" && floatval($firstBeratAmparan) > 0) {
            $pAct = floatval($firstBeratAmparan);
        }
        $extSambungan = round($pAct - floatval($sisaGelaran), 2);

        if ($extSambungan <= 0) {
            return array(
                "status" => 400,
                "message" => "Sisa gelaran tidak boleh lebih besar atau sama dengan panjang amparan.",
            );
        }

        // Sisa roll sambungan setelah dipakai untuk menyambung
        $extPAct = $extUnit == "KGM" && floatval($extRequest["ext_berat_amparan"] ?? 0) > 0 ? floatval($extRequest["ext_berat_amparan"]) : ($formCut->p_act + ($formCut->comma_p_act / 100));
        $extUsage = $extSambungan * floatval($extRequest["ext_lembar_sambungan"]);
        $extRemain = $extQty - $extUsage;
        $extRestUsage = ($extPAct * floatval($extRequest["ext_lembar_gelaran"])) + floatval($extRequest["ext_kepala_kain"]) + floatval($extRequest["ext_sisa_tidak_bisa"]) + floatval($extRequest["ext_reject"]) + floatval($extRequest["ext_piping"]);

        if ($extRemain <= 0) {
            return array(
                "status" => 400,
                "message" => "Qty roll sambungan tidak cukup untuk menyambung.",
            );
        }

        if (floatval($extRequest["ext_sisa_kain"]) > round($extRemain, 2)) {
            return array(
                "status" => 400,
                "message" => "Sisa kain roll sambungan (".$extRequest["ext_sisa_kain"].") tidak bisa lebih besar dari sisa roll setelah menyambung (".round($extRemain, 2).").",
            );
        }

        return [
            "id_roll" => $extIdRoll,
            "request" => $extRequest,
            "qty" => $extQty,
            "unit" => $extUnit,
            "sambungan" => $extSambungan,
            "usage" => $extUsage,
            "rest_usage" => $extRestUsage,
            "at" => $extAt->copy(),
            "rest_at" => $extAt->copy()->addSecond(),
        ];
    }

    /**
     * Buat baris roll sambungan ('extension complete') + sisa rollnya ('complete') untuk roll $headDetailId.
     *
     * @return array [id_roll => firstId|null] untuk fixChainedQty
     */
    private function createExtensionRows($formCut, $headDetailId, $groupRoll, array $ext, $colorAct, $user, CuttingService $cuttingService) {
        $extRequest = $ext["request"];

        $extBase = [
            "form_cut_id" => $formCut->id,
            "no_form_cut_input" => $formCut->no_form,
            "id_roll" => $ext["id_roll"],
            "id_item" => $extRequest["ext_id_item"],
            "color_act" => $colorAct,
            "detail_item" => $extRequest["ext_detail_item"],
            "group_roll" => $groupRoll,
            "lot" => $extRequest["ext_lot"],
            "roll" => $extRequest["ext_roll"],
            "roll_buyer" => $extRequest["ext_roll_buyer"],
            "unit" => $ext["unit"],
            "sisa_gelaran" => 0,
            "sambungan_roll" => 0,
            "average_time" => "00:00",
            "metode" => "scan",
            "berat_amparan" => $ext["unit"] == 'KGM' ? floatval($extRequest['ext_berat_amparan'] ?? 0) : 0,
            "created_by" => $user ? $user->id : null,
            "created_by_username" => $user ? $user->username : null,
            "edited" => 1,
            "edited_by" => $user ? $user->id : null,
            "edited_by_username" => $user ? $user->username : null,
            "edited_at" => Carbon::now(),
        ];

        // Bagian roll sambungan yang menyambung roll sebelumnya
        $extDetail = FormCutInputDetail::create(array_merge($extBase, [
            "id_sambungan" => $headDetailId,
            "qty" => $ext["qty"],
            "sambungan" => $ext["sambungan"],
            "est_amparan" => 0,
            "lembar_gelaran" => $extRequest["ext_lembar_sambungan"],
            "kepala_kain" => 0,
            "sisa_tidak_bisa" => 0,
            "reject" => 0,
            "piping" => 0,
            "sisa_kain" => 0,
            "pemakaian_lembar" => round($ext["usage"], 2),
            "total_pemakaian_roll" => round($ext["usage"], 2),
            "short_roll" => 0,
            "status" => "extension complete",
            "created_at" => $ext["at"],
            "updated_at" => $ext["at"],
        ]));

        // Sisa roll sambungan (dipakai lanjut sebagai roll biasa)
        $extRestQty = round($ext["qty"] - $ext["usage"], 2);
        FormCutInputDetail::create(array_merge($extBase, [
            "qty" => $extRestQty,
            "sambungan" => 0,
            "est_amparan" => 0,
            "lembar_gelaran" => $extRequest["ext_lembar_gelaran"],
            "kepala_kain" => $extRequest["ext_kepala_kain"],
            "sisa_tidak_bisa" => $extRequest["ext_sisa_tidak_bisa"],
            "reject" => $extRequest["ext_reject"],
            "piping" => $extRequest["ext_piping"],
            "sisa_kain" => $extRequest["ext_sisa_kain"],
            "pemakaian_lembar" => round($ext["rest_usage"], 2),
            "total_pemakaian_roll" => round($ext["rest_usage"], 2),
            "short_roll" => round($ext["rest_usage"] + floatval($extRequest["ext_sisa_kain"]) - $extRestQty, 2),
            "status" => "complete",
            "created_at" => $ext["rest_at"],
            "updated_at" => $ext["rest_at"],
        ]));

        // Pastikan roll ada di scanned_item (qty dibetulkan oleh fixChainedQty)
        ScannedItem::firstOrCreate(
            ["id_roll" => $ext["id_roll"]],
            [
                "id_item" => $extRequest["ext_id_item"],
                "color" => $colorAct,
                "detail_item" => $extRequest["ext_detail_item"],
                "lot" => $extRequest["ext_lot"],
                "roll" => $extRequest["ext_roll"],
                "roll_buyer" => $extRequest["ext_roll_buyer"],
                "qty" => $extRequest["ext_sisa_kain"],
                "qty_pakai" => round($ext["usage"] + $ext["rest_usage"], 2),
                "unit" => $ext["unit"],
            ]
        );

        // Pemakaian pertama roll sambungan?
        $similarExtBef = $cuttingService->takeSimilarFormCutDetailBef($ext["id_roll"], $ext["at"]->format("Y-m-d H:i:s"));

        return [$ext["id_roll"] => $similarExtBef ? null : $extDetail->id];
    }

    /**
     * Hapus satu baris detail: kembalikan qty scanned_item, catat ke form_cut_input_detail_delete, lalu hapus.
     * (sama seperti destroySpreadingRoll, tanpa cascade). Rantai qty roll dibetulkan pemanggil lewat fixChainedQty.
     */
    private function deleteDetailRowWithLog($detail) {
        if ($detail->id_roll) {
            $roll = ScannedItem::where("id_roll", $detail->id_roll)->first();
            if ($roll) {
                $roll->qty_pakai = ($roll->qty_pakai - round($detail->total_pemakaian_roll, 2) > 0 ? $roll->qty_pakai - round($detail->total_pemakaian_roll, 2) : 0);
                $roll->qty += ($detail->status == 'extension complete' || $detail->status == 'extension' ? round($detail->total_pemakaian_roll, 2) : round(($detail->qty ?? 0) - ($detail->sisa_kain ?? 0), 2));
                $roll->save();
            }
        }

        DB::table("form_cut_input_detail_delete")->insert([
            "old_id" => $detail->id,
            "form_cut_id" => $detail->form_cut_id,
            "no_form_cut_input" => $detail->no_form_cut_input,
            "id_roll" => $detail->id_roll,
            "id_item" => $detail->id_item,
            "color_act" => $detail->color_act,
            "detail_item" => $detail->detail_item,
            "group_roll" => $detail->group_roll,
            "lot" => $detail->lot,
            "roll" => $detail->roll,
            "qty" => $detail->qty,
            "unit" => $detail->unit,
            "sisa_gelaran" => $detail->sisa_gelaran,
            "sambungan" => $detail->sambungan,
            "sambungan_roll" => $detail->sambungan_roll,
            "est_amparan" => $detail->est_amparan,
            "lembar_gelaran" => $detail->lembar_gelaran,
            "average_time" => $detail->average_time,
            "kepala_kain" => $detail->kepala_kain,
            "sisa_tidak_bisa" => $detail->sisa_tidak_bisa,
            "reject" => $detail->reject,
            "sisa_kain" => ($detail->sisa_kain ? $detail->sisa_kain : 0),
            "pemakaian_lembar" => $detail->pemakaian_lembar,
            "total_pemakaian_roll" => $detail->total_pemakaian_roll,
            "short_roll" => $detail->short_roll,
            "piping" => $detail->piping,
            "status" => $detail->status,
            "metode" => $detail->metode,
            "group_stocker" => $detail->group_stocker,
            "created_at" => $detail->created_at,
            "updated_at" => $detail->updated_at,
            "deleted_by" => Auth::user()->username,
            "deleted_at" => Carbon::now(),
        ]);

        FormCutInputDetail::where("id", $detail->id)->delete();
    }

    /**
     * Kelompok unit roll: METER/YARD (panjang, yard dikonversi ke meter) atau KG (berat).
     */
    private function rollUnitGroup($unit) {
        $unit = strtoupper(trim((string) $unit));

        if (in_array($unit, ["KGM", "KG"])) {
            return "KG";
        }

        if (in_array($unit, ["METER", "M", "MTR", "YARD", "YRD", "YD"])) {
            return "METER";
        }

        return $unit;
    }

    private function rollUnitMessage($idRoll, $unit, $formUnit) {
        $formGroup = $this->rollUnitGroup($formUnit);

        return "Roll <b>".($idRoll ?: "-")."</b> memakai unit <b>".strtoupper($unit)."</b>, sedangkan roll lain di form ini memakai unit <b>".strtoupper($formUnit)."</b>.".
            "<br><br>".($formGroup == "KG"
                ? "Form ini hanya bisa memakai roll <b>KG</b>."
                : "Form ini hanya bisa memakai roll <b>METER</b> atau <b>YARD</b> (yard otomatis dikonversi ke meter). Roll <b>KG</b> tidak bisa dipakai.");
    }

    /**
     * Cek unit roll terhadap roll lain di form (kecuali $excludeId).
     * Mengembalikan array error, atau null jika sesuai / belum ada roll lain.
     */
    private function checkRollUnit($formCutId, $idRoll, $unit, $excludeId = null) {
        $formUnit = FormCutInputDetail::where("form_cut_id", $formCutId)->
            whereNotIn("status", ["not complete", "extension"])->
            whereNotNull("unit")->
            where("unit", "!=", "")->
            when($excludeId, fn ($q) => $q->where("id", "!=", $excludeId))->
            orderBy("created_at")->
            value("unit");

        if ($formUnit && $this->rollUnitGroup($unit) != $this->rollUnitGroup($formUnit)) {
            return array(
                "status" => 400,
                "title" => "Unit roll tidak sesuai",
                "message" => $this->rollUnitMessage($idRoll, $unit, $formUnit),
            );
        }

        return null;
    }

    /**
     * Cek apakah roll boleh dihapus langsung terkait sambungan.
     * - Baris roll sambungan ('extension complete' & sisa rollnya) tidak boleh dihapus langsung.
     * - Roll yang masih punya roll sambungan tidak boleh dihapus.
     * Mengembalikan array error, atau null jika boleh.
     */
    private function checkSambunganDelete($formCutDetail) {
        $formCut = FormCutInput::where("id", $formCutDetail->form_cut_id)->first();
        if (!$formCut) {
            return null;
        }

        [$details, $blocks] = $this->getRollBlocks($formCut);

        foreach ($blocks as $blockKey => $blockRows) {
            $index = null;
            foreach ($blockRows as $i => $blockRow) {
                if ($blockRow->id == $formCutDetail->id) {
                    $index = $i;
                    break;
                }
            }

            if ($index === null) {
                continue;
            }

            $visibleRows = array_values(array_filter($blockRows, fn ($row) => !in_array($row->status, ["not complete", "extension"])));
            $head = $visibleRows[0] ?? null;

            // Baris roll sambungan (bukan kepala blok)
            if ($blockKey !== "_leading" && $head && $head->id != $formCutDetail->id) {
                // Roll yang disambung = 'need extension' terakhir sebelum baris ini
                $parent = null;
                foreach ($blockRows as $i => $blockRow) {
                    if ($i >= $index) {
                        break;
                    }
                    if ($blockRow->status == "need extension") {
                        $parent = $blockRow;
                    }
                }

                $parentRoll = $parent ? ($parent->id_roll ?? "-") : ($head->id_roll ?? "-");

                return array(
                    "status" => 400,
                    "title" => "Roll sambungan tidak bisa dihapus",
                    "message" => "Roll <b>".($formCutDetail->id_roll ?? "-")."</b> adalah roll sambungan untuk roll <b>".$parentRoll."</b>.".
                        "<br><br>Untuk melepas sambungan: buka roll <b>".$parentRoll."</b>, kosongkan <b>Sisa Gelaran</b>, lalu simpan. Baris roll sambungan akan terhapus otomatis.",
                );
            }

            // Roll kepala yang masih punya roll sambungan
            foreach ($blockRows as $i => $blockRow) {
                if ($i > $index && in_array($blockRow->status, ["extension", "extension complete"])) {
                    return array(
                        "status" => 400,
                        "title" => "Roll masih memiliki sambungan",
                        "message" => "Roll <b>".($formCutDetail->id_roll ?? "-")."</b> masih disambung oleh roll <b>".($blockRow->id_roll ?? "-")."</b>.".
                            "<br><br>Lepas sambungan terlebih dahulu: kosongkan <b>Sisa Gelaran</b> roll ini lalu simpan. Setelah itu roll ini bisa dihapus.",
                    );
                }
            }

            return null;
        }

        return null;
    }

    /**
     * Cek apakah detail form boleh diubah (closing, stocker, switching).
     * Mengembalikan array error, atau null jika boleh.
     */
    private function checkFormDetailEditable($formCut) {
        // Check Closing
        if (checkClosingDate($formCut->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            );
        }

        // Check Stocker (superadmin boleh bypass)
        $stockerForm = Stocker::where('form_cut_id', $formCut->id)->first();
        if (!(Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) && $stockerForm) {
            return array(
                "status" => 400,
                "message" => "Form sudah memiliki stocker",
            );
        }

        // Check Form Cut Input Detail Output
        $formCutOutputs = FormCutInputDetailOutput::where("form_cut_input_id", $formCut->id)->get();
        foreach ($formCutOutputs as $formCutOutput) {
            if ($formCutOutput->qty_output_original != $formCutOutput->qty_output_aktual) {
                return array(
                    "status" => 400,
                    "message" => "Form sudah ada transfer switching."
                );
            }
        }

        return null;
    }

    /**
     * Kelompokkan detail form menjadi blok roll.
     * Blok = baris kepala + baris lanjutan sambungan + baris tersembunyi ('extension' / 'not complete').
     * Lanjutan: baris setelah 'need extension', atau roll yang sama setelah 'extension complete'.
     *
     * @return array [$details, $blocks] dengan $blocks = [headId => [detail, ...]] (urut), key "_leading" untuk baris tersembunyi sebelum roll pertama
     */
    private function getRollBlocks($formCut) {
        $details = FormCutInputDetail::where("form_cut_id", $formCut->id)->
            orderBy("created_at")->
            orderBy("updated_at")->
            orderBy("id")->
            get();

        $blocks = [];
        $currentHeadId = null;
        $latestVisibleStatus = "";
        $latestVisibleIdRoll = null;
        foreach ($details as $detail) {
            $isVisible = !in_array($detail->status, ["not complete", "extension"]);

            $isContinuation = $latestVisibleStatus == "need extension" ||
                ($latestVisibleStatus == "extension complete" && $detail->id_roll && $detail->id_roll == $latestVisibleIdRoll);

            if ($isVisible && !$isContinuation) {
                $currentHeadId = (string) $detail->id;
                $blocks[$currentHeadId] = [];
            }

            if ($currentHeadId === null) {
                $currentHeadId = "_leading";
                $blocks[$currentHeadId] = [];
            }

            $blocks[$currentHeadId][] = $detail;

            if ($isVisible) {
                $latestVisibleStatus = $detail->status;
                $latestVisibleIdRoll = $detail->id_roll;
            }
        }

        return [$details, $blocks];
    }

    /**
     * Hitung created_at untuk roll yang disisipkan setelah blok $insertAfter ("_start" / kosong = paling awal).
     * Diambil di tengah celah waktu antara blok sebelum dan sesudahnya.
     * $rowsNeeded = jumlah baris yang disisipkan (masing-masing berjarak 1 detik), dikembalikan waktu baris pertama.
     *
     * @return Carbon|array Carbon jika berhasil, array error jika gagal
     */
    private function resolveInsertTime($formCut, $insertAfter, $rowsNeeded = 1) {
        [$details, $blocks] = $this->getRollBlocks($formCut);

        $keys = array_keys($blocks);
        $prev = null;
        $next = null;

        if (!$insertAfter || $insertAfter == "_start") {
            // Sisipkan sebelum roll pertama (setelah baris tersembunyi di depan jika ada)
            if (isset($blocks["_leading"])) {
                $prev = end($blocks["_leading"]);
            }
            foreach ($keys as $key) {
                if ($key !== "_leading") {
                    $next = $blocks[$key][0];
                    break;
                }
            }
        } else {
            if (!isset($blocks[(string) $insertAfter])) {
                return array(
                    "status" => 400,
                    "message" => "Roll acuan tidak ditemukan. Silahkan refresh halaman lalu coba lagi.",
                );
            }

            $prev = end($blocks[(string) $insertAfter]);
            $position = array_search((string) $insertAfter, $keys, true);
            if (isset($keys[$position + 1])) {
                $next = $blocks[$keys[$position + 1]][0];
            }
        }

        if ($prev && $next) {
            $prevTime = Carbon::parse($prev->created_at);
            $nextTime = Carbon::parse($next->created_at);
            $gap = $prevTime->diffInSeconds($nextTime, false);

            if ($gap < $rowsNeeded + 1) {
                return array(
                    "status" => 400,
                    "message" => "Tidak ada celah waktu di posisi ini untuk menyisipkan roll. Pilih posisi lain.",
                );
            }

            return $prevTime->copy()->addSeconds(max(1, intdiv($gap - ($rowsNeeded - 1), 2)));
        }

        if ($prev) {
            return Carbon::parse($prev->created_at)->addSecond();
        }

        if ($next) {
            return Carbon::parse($next->created_at)->subSeconds($rowsNeeded);
        }

        return Carbon::parse($formCut->waktu_mulai ?? $formCut->waktu_selesai ?? Carbon::now());
    }

    public function recalculateForm($id, CuttingService $cuttingService)
    {
        if ($id) {
            $cuttingService->recalculateForm($id);

            return array(
                "status" => 200,
                "message" => "alright",
            );
        }
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

    public function destroySpreadingRoll($id, CuttingService $cuttingService, StockerService $stockerService, $cascade = false) {
        ini_set('max_execution_time', 3600);

        // Sambungan tidak boleh dihapus langsung (hanya untuk hapus dari user, bukan cascade internal)
        if (!$cascade) {
            $checkFormCutDetail = FormCutInputDetail::find($id);
            if ($checkFormCutDetail) {
                $sambunganError = $this->checkSambunganDelete($checkFormCutDetail);
                if ($sambunganError) {
                    return $sambunganError;
                }
            }
        }

        DB::beginTransaction();

        try {
            $formCutDetail = FormCutInputDetail::find($id);

            if ($formCutDetail) {
                // Check Stocker
                $stockerForm = Stocker::where('form_cut_id', $formCutDetail->form_cut_id)->first();
                Log::channel("completedFormBypassStocker")->info($stockerForm);
                if (!(Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) && $stockerForm) {
                    DB::rollBack();

                    return array(
                        'status' => 400,
                        'message' => 'Form sudah memiliki stocker',
                        'redirect' => '',
                        'table' => 'datatable',
                        'additional' => [],
                    );
                }

                // Check Form Cut Input Detail Output
                $formCutOutputs = FormCutInputDetailOutput::where("form_cut_input_id", $formCutDetail->form_cut_id)->get();
                foreach ($formCutOutputs as $formCutOutput) {
                    if ($formCutOutput->qty_output_original != $formCutOutput->qty_output_aktual) {
                        DB::rollBack();

                        return array(
                            "status" => 400,
                            "message" => "Form sudah ada transfer switching."
                        );
                    }
                }

                // Update scanned item qty
                if ($formCutDetail->id_roll) {
                    // No need to update qty if it is redundant
                    $checkSimilarTimeRecord = DB::table("form_cut_input_detail")->where("form_cut_id", $formCutDetail->form_cut_id)->where("id_roll", $formCutDetail->id_roll)->where("qty", $formCutDetail->qty)->where("id", "!=", $formCutDetail->id)->first();
                    if (!$checkSimilarTimeRecord) {

                        // Get scanned item roll
                        $formCutDetailRoll = ScannedItem::where("id_roll", $formCutDetail->id_roll)->first();
                        if ($formCutDetailRoll) {

                            // Update scanned item qty
                            $formCutDetailRoll->qty_pakai = ($formCutDetailRoll->qty_pakai - round($formCutDetail->total_pemakaian_roll, 2) > 0 ? $formCutDetailRoll->qty_pakai - round($formCutDetail->total_pemakaian_roll, 2) : 0);
                            $formCutDetailRoll->qty += ($formCutDetail->status == 'extension complete' || $formCutDetail->status == 'extension' ? round($formCutDetail->total_pemakaian_roll, 2) : round(($formCutDetail->qty ?? 0) - ($formCutDetail->sisa_kain ?? 0), 2));

                            $formCutDetailRoll->save();
                        }
                    }
                }

                // Add to Form Cut Detail Deleted Logs
                DB::table("form_cut_input_detail_delete")->insert([
                    "old_id" => $formCutDetail['id'],
                    "form_cut_id" => $formCutDetail['form_cut_id'],
                    "no_form_cut_input" => $formCutDetail['no_form_cut_input'],
                    "id_roll" => $formCutDetail['id_roll'],
                    "id_item" => $formCutDetail['id_item'],
                    "color_act" => $formCutDetail['color_act'],
                    "detail_item" => $formCutDetail['detail_item'],
                    "group_roll" => $formCutDetail['group_roll'],
                    "lot" => $formCutDetail['lot'],
                    "roll" => $formCutDetail['roll'],
                    "qty" => $formCutDetail['qty'],
                    "unit" => $formCutDetail['unit'],
                    "sisa_gelaran" => $formCutDetail['sisa_gelaran'],
                    "sambungan" => $formCutDetail['sambungan'],
                    "sambungan_roll" => $formCutDetail['sambungan_roll'],
                    "est_amparan" => $formCutDetail['est_amparan'],
                    "lembar_gelaran" => $formCutDetail['lembar_gelaran'],
                    "average_time" => $formCutDetail['average_time'],
                    "kepala_kain" => $formCutDetail['kepala_kain'],
                    "sisa_tidak_bisa" => $formCutDetail['sisa_tidak_bisa'],
                    "reject" => $formCutDetail['reject'],
                    "sisa_kain" => ($formCutDetail['sisa_kain'] ? $formCutDetail['sisa_kain'] : 0),
                    "pemakaian_lembar" => $formCutDetail['pemakaian_lembar'],
                    "total_pemakaian_roll" => $formCutDetail['total_pemakaian_roll'],
                    "short_roll" => $formCutDetail['short_roll'],
                    "piping" => $formCutDetail['piping'],
                    "status" => $formCutDetail['status'],
                    "metode" => $formCutDetail['metode'],
                    "group_stocker" => $formCutDetail['group_stocker'],
                    "created_at" => $formCutDetail['created_at'],
                    "updated_at" => $formCutDetail['updated_at'],
                    "deleted_by" => Auth::user()->username,
                    "deleted_at" => Carbon::now(),
                ]);

                // Check if first usage
                $firstId = null;
                $similarFormCutDetailBef = $cuttingService->takeSimilarFormCutDetailBef($formCutDetail->id_roll, $formCutDetail->created_at);
                if (!$similarFormCutDetailBef) {
                    $firstId = $formCutDetail->id;
                }

                // Delete Form Cut Detail
                if ($formCutDetail->delete()) {

                    // Delete sambungan If after Sambungan
                    $before = FormCutInputDetail::where("form_cut_id", $formCutDetail->form_cut_id)->where('created_at', '<', $formCutDetail->created_at)->orderBy("created_at", "desc")->first();
                    if ($before && $before->id_roll == $formCutDetail->id_roll && $before->status == 'extension complete') {
                        $this->destroySpreadingRoll($before->id, $cuttingService, $stockerService, true);
                    }

                    // Delete Sambungan Reference
                    if ($formCutDetail->id_sambungan) {
                        $formCutDetailReference = FormCutInputDetail::where("id", $formCutDetail['id_sambungan'])->first();

                        if ($formCutDetailReference) {
                            $this->destroySpreadingRoll($formCutDetailReference->id, $cuttingService, $stockerService, true);
                        }

                        $after = FormCutInputDetail::where("form_cut_id", $formCutDetail->form_cut_id)->where('created_at', '<', $formCutDetail->created_at)->orderBy("created_at", "desc")->first();
                        if ($after && $after->id_roll == $formCutDetail->id_roll) {
                            $this->destroySpreadingRoll($after->id, $cuttingService, $stockerService, true);
                        }
                    }

                    // Fix Roll Qty after form_cut_detail has been deleted, in case the qty is still not updating
                    $cuttingService->fixChainedQty($formCutDetail['id_roll'], $firstId);

                    // Jika hanya tersisa 1 row, update status FormCutInput
                    $hasFormCutDetail = FormCutInputDetail::where("form_cut_id", $formCutDetail->form_cut_id)->exists();
                    if (!$hasFormCutDetail) {

                        // Update status
                        FormCutInput::where('id', $formCutDetail->form_cut_id)
                            ->update([
                                'status' => 'PENGERJAAN FORM CUTTING SPREAD',
                                'updated_at' => now(),
                            ]);

                        $updatedData = FormCutInput::where('id', $formCutDetail->form_cut_id)->first();

                        // Redirect Route
                        $redirect = '';
                        switch ($updatedData->tipe_form_cut) {
                            case 'NORMAL' :
                                $redirect = route('process-form-cut-input', $updatedData->id);
                                break;
                            case 'MANUAL' :
                                $redirect = route('process-manual-form-cut', $updatedData->id);
                                break;
                            case 'PILOT' :
                                $redirect = route('process-pilot-form-cut', $updatedData->id);
                                break;
                            default :
                                $redirect = null;
                                break;
                        }

                        // If the form has part form (delete part form & reorder)
                        $partForm = PartForm::where('form_id', $formCutDetail->form_cut_id)->first();
                        if ($partForm) {
                            // Delete part form
                            $deletePartForm = PartForm::where('form_id', $formCutDetail->form_cut_id)->delete();

                            if ($deletePartForm) {
                                // Reorder part form group
                                $stockerService->reorderStockerNumbering($partForm->part_id);
                            }
                        }

                        // generate form cut output
                        FormCutInputDetailOutput::where("form_cut_input_id", $formCutDetail->form_cut_id)->delete();

                        DB::commit();

                        return array(
                            'status' => 201,
                            'message' => 'Form  "' . $updatedData->no_form. '" berhasil diubah ke status PENGERJAAN FORM CUTTING SPREAD',
                            'redirect' => $redirect,
                            'table' => 'datatable',
                            'additional' => [],
                        );
                    }

                    // $hasFormCutDetail = FormCutInputDetail::where("form_cut_id", $formCutDetail->form_cut_id)->exists();

                    // if (!$hasFormCutDetail) {
                    //     DB::rollBack();

                    //     return array(
                    //         'status' => 400,
                    //         'message' => 'Tidak bisa hapus roll terakhir pada form ini',
                    //         'redirect' => '',
                    //         'table' => 'datatable',
                    //         'additional' => [],
                    //     );
                    // }

                    // generate form cut output
                    $cuttingService = new CuttingService();
                    $cuttingService->generateFormCutInputDetailOutput($formCutDetail->form_cut_id);

                    DB::commit();

                    return array(
                        "status" => 200,
                        "message" => "alright"
                    );
                }
            }

            DB::rollBack();

            return array(
                "status" => 400,
                "message" => "Data tidak berubah."
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "status" => 400,
                "message" => $e->getMessage(),
                "details" => $e
            ], 400);
        }

        return array(
            "status" => 400,
            "message" => "Data tidak berubah."
        );
    }
}
