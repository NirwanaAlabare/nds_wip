<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetailOutput;
use App\Models\Cutting\FormCutInputDetailOutputLog;
use App\Models\Marker\MarkerDetail;
use App\Models\Stocker\Stocker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;

class CuttingSwitchingController extends Controller
{
    // Kolom hasil query log switching (urutan sama dengan tabel & export)
    private $switchingLogColumns = [
        'group_roll', 'no_form', 'tanggal_form', 'ws', 'styleno', 'color', 'size', 'panel', 'part',
        'total_out', 'total_in', 'is_active', 'created_by', 'created_at', 'updated_at',
    ];

    // Log switching per form/size/part: sisi asal = out, sisi tujuan = in
    private function switchingLogQuery($dateFrom, $dateTo)
    {
        $start = $dateFrom . " 00:00:00";
        $end = $dateTo . " 23:59:59";

        // Log masuk periode jika waktu selesai form asal / form tujuan / update log ada di rentang tanggal.
        // Dipakai di kedua sisi UNION supaya out & in dari log yang sama selalu ikut bersamaan.
        $logFilter = "
            form_cut_input_detail_output_logs.id IN (
                SELECT l.id
                FROM form_cut_input_detail_output_logs l
                LEFT JOIN form_cut_input fa ON fa.id = l.form_cut_input_id_asal
                LEFT JOIN form_cut_input ft ON ft.id = l.form_cut_input_id_tujuan
                WHERE fa.waktu_selesai BETWEEN ? AND ?
                    OR ft.waktu_selesai BETWEEN ? AND ?
                    OR l.updated_at BETWEEN ? AND ?
            )
        ";
        $logFilterBindings = [$start, $end, $start, $end, $start, $end];

        $sql = "
                SELECT 
                    group_roll,
                    no_form,
                    tanggal_form,
                    ws,
                    styleno,
                    color,
                    size,
                    panel,
                    part,
                    SUM(`out`) total_out,
                    SUM(`in`) total_in,
                    is_active,
                    created_by,
                    created_at,
                    updated_at
                FROM (
                    select
                        form_cut_input_detail_output_logs.group_roll_asal group_roll,
                        form_asal.no_form no_form,
                        DATE(form_asal.waktu_selesai) tanggal_form,
                        msb_asal.ws ws,
                        msb_asal.styleno styleno,
                        msb_asal.color color,
                        msb_asal.size size,
                        marker_asal.panel panel,
                        mp_asal.nama_part part,
                        form_cut_input_detail_output_logs.qty_transfer `out`,
                        0 `in`,
                        form_cut_input_detail_output_logs.`is_active`,
                        form_cut_input_detail_output_logs.created_by,
                        form_cut_input_detail_output_logs.created_at,
                        form_cut_input_detail_output_logs.updated_at
                    from
                        form_cut_input_detail_output_logs
                        LEFT JOIN form_cut_input form_asal on form_asal.id = form_cut_input_detail_output_logs.form_cut_input_id_asal
                        LEFT JOIN marker_input marker_asal on marker_asal.id = form_asal.marker_id
                        LEFT JOIN marker_input_detail marker_detail_asal on marker_detail_asal.marker_id = marker_asal.id and marker_detail_asal.size = form_cut_input_detail_output_logs.size_asal
                        LEFT JOIN master_sb_ws msb_asal on msb_asal.id_so_det = (CASE WHEN form_cut_input_detail_output_logs.so_det_id_asal IS NOT NULL THEN form_cut_input_detail_output_logs.so_det_id_asal ELSE marker_detail_asal.so_det_id END)
                        LEFT JOIN part part_asal on part_asal.act_costing_id = marker_asal.act_costing_id and part_asal.panel = marker_asal.panel
                        LEFT JOIN part_detail pd_asal on pd_asal.part_id = part_asal.id and (pd_asal.status IS NULL OR pd_asal.status = 'active')
                        LEFT JOIN part_detail pd_com on pd_com.id = pd_asal.from_part_detail and pd_asal.part_status = 'complement'
                        LEFT JOIN master_part mp_asal on mp_asal.id = pd_asal.master_part_id and 
                    where
                        ".$logFilter."
                    UNION ALL
                    select
                        form_cut_input_detail_output_logs.group_roll_asal group_roll,
                        form_tujuan.no_form no_form,
                        DATE(form_tujuan.waktu_selesai) tanggal_form,
                        msb_tujuan.ws ws,
                        msb_tujuan.styleno styleno,
                        msb_tujuan.color color,
                        msb_tujuan.size size,
                        marker_tujuan.panel panel,
                        mp_tujuan.nama_part part,
                        0 `out`,
                        form_cut_input_detail_output_logs.qty_transfer `in`,
                        form_cut_input_detail_output_logs.`is_active`,
                        form_cut_input_detail_output_logs.created_by,
                        form_cut_input_detail_output_logs.created_at,
                        form_cut_input_detail_output_logs.updated_at
                    from
                        form_cut_input_detail_output_logs
                        LEFT JOIN form_cut_input form_tujuan on form_tujuan.id = form_cut_input_detail_output_logs.form_cut_input_id_tujuan
                        LEFT JOIN marker_input marker_tujuan on marker_tujuan.id = form_tujuan.marker_id
                        LEFT JOIN marker_input_detail marker_detail_tujuan on marker_detail_tujuan.marker_id = marker_tujuan.id and marker_detail_tujuan.size = form_cut_input_detail_output_logs.size_tujuan
                        LEFT JOIN master_sb_ws msb_tujuan on msb_tujuan.id_so_det = (CASE WHEN form_cut_input_detail_output_logs.so_det_id_tujuan IS NOT NULL THEN form_cut_input_detail_output_logs.so_det_id_tujuan ELSE marker_detail_tujuan.so_det_id END)
                        LEFT JOIN part part_tujuan on part_tujuan.act_costing_id = marker_tujuan.act_costing_id and part_tujuan.panel = marker_tujuan.panel
                        LEFT JOIN part_detail pd_tujuan on pd_tujuan.part_id = part_tujuan.id and (pd_tujuan.status IS NULL OR pd_tujuan.status = 'active')
                        LEFT JOIN part_detail pd_com on pd_com.id = pd_tujuan.from_part_detail and pd_tujuan.part_status = 'complement'
                        LEFT JOIN master_part mp_tujuan on mp_tujuan.id = pd_tujuan.master_part_id and 
                    where
                        ".$logFilter."
                ) form_cut_input_detail_output_logs
                GROUP BY
                    group_roll,
                    no_form,
                    tanggal_form,
                    ws,
                    styleno,
                    color,
                    size,
                    panel,
                    part,
                    is_active,
                    created_by,
                    created_at,
                    updated_at
        ";

        return DB::query()
            ->fromRaw("($sql) as switching_log", array_merge($logFilterBindings, $logFilterBindings))
            ->orderBy('created_at')
            ->orderBy('no_form')
            ->orderBy('size')
            ->orderBy('part');
    }

    public function index(Request $request) {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ?? date("Y-m-d");
            $dateTo = $request->dateTo ?? date("Y-m-d");

            $dataTable = DataTables::query($this->switchingLogQuery($dateFrom, $dateTo));

            // Search global & per kolom langsung ke kolom hasil query (binding, tanpa prefix tabel)
            foreach ($this->switchingLogColumns as $column) {
                $dataTable->filterColumn($column, function ($query, $keyword) use ($column) {
                    $query->whereRaw("CAST(`$column` AS CHAR) LIKE ?", ['%' . $keyword . '%']);
                });
            }

            return $dataTable->toJson();
        }

        return view('cutting.switching.index', ["page" => "dashboard-cutting"]);
    }

    public function create(Request $request) {
        if ($request->ajax()) {
            $additionalQuery = "";

            $dateFrom = $request->dateFrom ?? date("Y-m-d");
            $dateTo = $request->dateTo ?? date("Y-m-d");

            if ($dateFrom) {
                $additionalQuery .= " and (cutting_plan.tgl_plan >= '" . $dateFrom . "' or a.updated_at >= '". $dateFrom ."')";
            }

            if ($dateTo) {
                $additionalQuery .= " and (cutting_plan.tgl_plan <= '" . $dateTo . "' or a.updated_at <= '". $dateTo ."')";
            }

            if (Auth::user()->type == "meja") {
                $additionalQuery .= " and a.no_meja = '" . Auth::user()->id . "' ";
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
                    COALESCE(DATE(a.waktu_selesai), DATE(a.waktu_mulai), a.tgl_form_cut) tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color color,
                    a.status,
                    UPPER(users.name) nama_meja,
                    b.panjang_marker panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    CONCAT(COALESCE(a2.total_lembar, a.total_lembar, '0'), '/', a.qty_ply) ply_progress,
                    COALESCE(a.qty_ply, 0) qty_ply,
                    COALESCE(b.gelar_qty, 0) gelar_qty,
                    COALESCE(a2.total_lembar, a.total_lembar, '0') total_lembar,
                    b.po_marker po_marker,
                    b.urutan_marker urutan_marker,
                    b.cons_marker cons_marker,
                    b.unit_cons_marker unit_cons_marker,
                    UPPER(b.tipe_marker) tipe_marker,
                    cutting_plan.app,
                    a.tipe_form_cut,
                    COALESCE(b.notes, '-') notes,
                    GROUP_CONCAT(DISTINCT CONCAT(COALESCE(master_size_new.size, master_sb_ws.size, marker_input_detail.size), '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ' / ') marker_details,
                    a.created_by_username,
                    a.created_at,
                    a.updated_at,
                    user_app.username as app_by
                FROM cutting_plan
                left join form_cut_input a on a.id = cutting_plan.form_cut_id
                left join (select form_cut_input_detail.form_cut_id, SUM(form_cut_input_detail.lembar_gelaran) total_lembar from form_cut_input_detail group by form_cut_input_detail.form_cut_id) a2 on a2.form_cut_id = a.id
                left outer join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left outer join marker_input_detail on b.id = marker_input_detail.marker_id and marker_input_detail.ratio > 0
                left join master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                left join master_size_new on master_size_new.size = master_sb_ws.size
                left join users on users.id = a.no_meja
                left join users as user_app on user_app.id = cutting_plan.app_by
                where
                    a.id is not null
                    AND a.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH)
                    AND a.status = 'SELESAI PENGERJAAN'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY
                    FIELD(a.status, 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'),
                    FIELD(a.tipe_form_cut, null, 'NORMAL', 'MANUAL'),
                    FIELD(cutting_plan.app, 'Y', 'N', null),
                    a.no_form desc,
                    a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        return view('cutting.switching.switching', ["page" => "dashboard-cutting"]);
    }

    public function show($id = 0) {
        $form = FormCutInput::where("id", $id)->first();

        // Get Costing List (From act_costing SB) by Buyer
        $orders = DB::select("select id_act_cost, ws from master_sb_ws where tgl_kirim >= DATE_SUB( CURRENT_DATE, INTERVAL 1 YEAR ) group by id_act_cost");

        // Ambil data log transfer yang melibatkan form ini sebagai asal maupun tujuan
        $logs = FormCutInputDetailOutputLog::selectRaw("
                form_cut_input_detail_output_logs.*,
                asal.no_form as no_form_asal,
                tujuan.no_form as no_form_tujuan
            ")
            ->leftJoin("form_cut_input as asal", "asal.id", "=", "form_cut_input_detail_output_logs.form_cut_input_id_asal")
            ->leftJoin("form_cut_input as tujuan", "tujuan.id", "=", "form_cut_input_detail_output_logs.form_cut_input_id_tujuan")
            ->where('form_cut_input_id_asal', $id)
            ->orWhere('form_cut_input_id_tujuan', $id)
            ->orderBy('form_cut_input_detail_output_logs.created_at', 'desc')
            ->get();

        return view("cutting.switching.switching-detail", ["page" => "dashboard-cutting", "form" => $form, "orders" => $orders, "logs" => $logs]);
    }

    public function getFormList(Request $request) {
        $forms = FormCutInput::select("form_cut_input.id", "form_cut_input.no_form")->
            leftJoin("marker_input", "marker_input.id", "=", "form_cut_input.marker_id")->
            where("marker_input.act_costing_id", $request->act_costing_id)->
            get();

        return $forms;
    }

    public function getFormSizeList(Request $request) {
        $sizes = FormCutInput::select("marker_input_detail.id", "marker_input_detail.size")->
            leftJoin("marker_input", "marker_input.id", "=", "form_cut_input.marker_id")->
            leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
            where("form_cut_input.id", $request->form_cut_id)->
            // where("marker_input_detail.ratio", ">", 0)->
            groupBy("marker_input_detail.id")->
            get();

        return $sizes;
    }

    public function store(Request $request) {
        $validatedRequest = $request->validate([
            "id" => "required",
            "form_cut_id" => "required",
            "marker_detail_id" => "required",
            "group_roll" => "required",
            "qty" => "required|numeric|gt:0"
        ]);

        $checkStocker = Stocker::where("form_cut_id", $validatedRequest['form_cut_id'])->first();
        if ($checkStocker) {
            return array(
                "status" => 400,
                "message" => "Form sudah memiliki Stocker."
            );
        }

        // Check Form Cut Input Detail Output
        DB::beginTransaction();
        try {
            // 1. Ambil data output asal (Source)
            $fromOutput = FormCutInputDetailOutput::find($validatedRequest["id"]);
            if (!$fromOutput) {
                DB::rollBack();
                return response()->json(["status" => 404, "message" => "Data asal tidak ditemukan"], 404);
            }

            // Ambil informasi form dan marker detail asal (untuk keperluan pesan sukses)
            $fromForm = FormCutInput::find($fromOutput->form_cut_input_id);
            $fromMarkerDetail = MarkerDetail::find($fromOutput->marker_input_detail_id);

            if (!$fromForm || !$fromMarkerDetail) {
                DB::rollBack();
                return response()->json(["status" => 400, "message" => "Informasi data asal tidak lengkap"], 400);
            }

            if (checkClosingDate($fromForm->waktu_selesai)) {
                return array(
                    "status" => 400,
                    "message" => "Periode sudah ditutup."
                );
            }

            // 2. Ambil data tujuan (Destination)
            $toForm = FormCutInput::find($validatedRequest["form_cut_id"]);
            $toMarkerDetail = MarkerDetail::find($validatedRequest["marker_detail_id"]);

            if (!$toForm || !$toMarkerDetail) {
                DB::rollBack();
                return response()->json(["status" => 404, "message" => "Data tujuan tidak ditemukan"], 404);
            }

            if (checkClosingDate($toForm->waktu_selesai)) {
                return array(
                    "status" => 400,
                    "message" => "Periode sudah ditutup."
                );
            }

            $qty = $validatedRequest["qty"];
            $groupRoll = $validatedRequest["group_roll"];

            // 3. Validasi kuantitas sumber (Source)
            if ($fromOutput->qty_output_aktual < $qty) {
                DB::rollBack();
                return response()->json(["status" => 400, "message" => "Kuantitas sumber (".$fromOutput->qty_output_aktual.") tidak mencukupi untuk ditransfer (".$qty.")"], 400);
            }

            // 4. Update data sumber (Source)
            $fromOutput->qty_output_aktual -= $qty; // Langsung kurangi dari qty aktual
            $fromOutput->save();

            // 5. Update atau Buat data tujuan (Destination)
            $toOutput = FormCutInputDetailOutput::where("form_cut_input_id", $toForm->id)
                ->where("marker_input_detail_id", $toMarkerDetail->id)
                ->where("group_roll", $groupRoll)
                ->first();

            if ($toOutput) {
                $toOutput->qty_output_aktual += $qty; // Langsung tambahkan ke qty aktual
                $toOutput->save();
            } else {
                FormCutInputDetailOutput::create([
                    "form_cut_input_id" => $toForm->id,
                    "group_roll" => $groupRoll,
                    "marker_input_detail_id" => $toMarkerDetail->id,
                    "size_asal" => $toMarkerDetail->size,
                    "ratio" => $toMarkerDetail->ratio,
                    "total_lembar_gelaran" => 0, // Awalnya 0 karena ini adalah hasil transfer
                    "qty_output_original" => 0, // Awalnya 0 karena ini adalah hasil transfer
                    "qty_output_aktual" => $qty,
                    "is_active" => 1,
                    "created_by" => Auth::user()->username
                ]);
            }

            // 6. Log
            FormCutInputDetailOutputLog::create([
                "form_cut_input_id_asal" => $fromForm->id,
                "form_cut_input_id_tujuan" => $toForm->id,
                "group_roll_asal" => $fromOutput->group_roll,
                "size_asal" => $fromMarkerDetail->size,
                "size_tujuan" => $toMarkerDetail->size,
                "qty_transfer" => $qty,
                "is_active" => 1,
                "created_by" => Auth::user()->username
            ]);

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Data size " . $fromMarkerDetail->size . " form " . $fromForm->no_form . " berhasil di transfer ke size " . $toMarkerDetail->size . " form " . $toForm->no_form,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }

    public function massStore(Request $request) {
        $validatedRequest = $request->validate([
            'transfers' => 'required|array|min:1',
            'transfers.*.id' => 'required|exists:form_cut_input_detail_output,id',
            'transfers.*.from_form_cut_id' => 'required|exists:form_cut_input,id',
            'transfers.*.form_cut_id' => 'required|exists:form_cut_input,id',
            'transfers.*.marker_detail_id' => 'required|exists:marker_input_detail,id',
            'transfers.*.group_roll' => 'required',
            'transfers.*.qty' => 'required|numeric|gt:0',
        ]);

        DB::beginTransaction();
        try {
            $successCount = 0;
            $messages = [];

            foreach ($validatedRequest['transfers'] as $transfer) {
                $checkStocker = Stocker::where("form_cut_id", $transfer['form_cut_id'])->first();
                if ($checkStocker) {
                    return array(
                        "status" => 400,
                        "message" => "Form sudah memiliki Stocker."
                    );
                }

                // 1. Ambil data output asal (Source)
                $fromOutput = FormCutInputDetailOutput::find($transfer["id"]);
                if (!$fromOutput) {
                    // This should not happen due to validation, but as a safeguard
                    continue;
                }

                // Ambil informasi form dan marker detail asal
                $fromForm = FormCutInput::find($fromOutput->form_cut_input_id);
                $fromMarkerDetail = MarkerDetail::find($fromOutput->marker_input_detail_id);

                if (!$fromForm || !$fromMarkerDetail) {
                    DB::rollBack();
                    return response()->json(["status" => 400, "message" => "Informasi data asal tidak lengkap"], 400);
                }

                if (checkClosingDate($fromForm->waktu_selesai)) {
                    return array(
                        "status" => 400,
                        "message" => "Periode sudah ditutup."
                    );
                }

                // 2. Ambil data tujuan (Destination)
                $toForm = FormCutInput::find($transfer["form_cut_id"]);
                $toMarkerDetail = MarkerDetail::find($transfer["marker_detail_id"]);

                if (!$toForm || !$toMarkerDetail) {
                    DB::rollBack();
                    return response()->json(["status" => 400, "message" => "Informasi data asal tidak lengkap"], 400);
                }

                if (checkClosingDate($toForm->waktu_selesai)) {
                    return array(
                        "status" => 400,
                        "message" => "Periode sudah ditutup."
                    );
                }

                $qty = $transfer["qty"];

                // 3. Validasi kuantitas
                if ($fromOutput->qty_output_aktual < $qty) {
                    DB::rollBack();
                    return response()->json(["status" => 400, "message" => "Kuantitas untuk size ".$fromMarkerDetail->size." (".$fromOutput->qty_output_aktual.") tidak mencukupi untuk ditransfer (".$qty.")"], 400);
                }

                // 4. Update data sumber (Source)
                $fromOutput->qty_output_aktual -= $qty;
                $fromOutput->save();

                // 5. Update atau Buat data tujuan (Destination)
                $toOutput = FormCutInputDetailOutput::where("form_cut_input_id", $toForm->id)
                    ->where("marker_input_detail_id", $toMarkerDetail->id)
                    ->where("group_roll", $transfer["group_roll"])
                    ->first();

                if ($toOutput) {
                    $toOutput->qty_output_aktual += $qty;
                    $toOutput->save();
                } else {
                    // Logika create sama seperti di fungsi store
                    FormCutInputDetailOutput::create([ "form_cut_input_id" => $toForm->id, "group_roll" => $transfer["group_roll"], "marker_input_detail_id" => $toMarkerDetail->id, "size_asal" => $toMarkerDetail->size, "ratio" => $toMarkerDetail->ratio, "total_lembar_gelaran" => 0, "qty_output_original" => 0, "qty_output_aktual" => $qty, "created_by" => Auth::user()->username ]);
                }

                // 6. Log
                FormCutInputDetailOutputLog::create([ "form_cut_input_id_asal" => $fromForm->id, "form_cut_input_id_tujuan" => $toForm->id, "group_roll_asal" => $fromOutput->group_roll, "size_asal" => $fromMarkerDetail->size, "size_tujuan" => $toMarkerDetail->size, "qty_transfer" => $qty, "created_by" => Auth::user()->username ]);
                $successCount++;
            }

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => $successCount . " data output berhasil ditransfer.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "message" => "Terjadi kesalahan saat transfer massal: " . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id = 0)
    {
        DB::beginTransaction();
        try {
            // 1. Find the log entry
            $log = FormCutInputDetailOutputLog::find($id);
            if (!$log) {
                DB::rollBack();
                return response()->json(['status' => 404, 'message' => 'Log transfer tidak ditemukan.'], 404);
            }

            $qty = $log->qty_transfer;

            // 2. Get source and destination forms
            $fromForm = FormCutInput::with('marker')->find($log->form_cut_input_id_asal);
            $toForm = FormCutInput::with('marker')->find($log->form_cut_input_id_tujuan);

            $checkStockerFrom = Stocker::where("form_cut_id", $log->form_cut_input_id_asal)->first();
            if ($checkStockerFrom) {
                return array(
                    "status" => 400,
                    "message" => "Form sudah memiliki Stocker."
                );
            }
            if (checkClosingDate($fromForm->waktu_selesai)) {
                return array(
                    "status" => 400,
                    "message" => "Periode sudah ditutup."
                );
            }
            $checkStockerTo = Stocker::where("form_cut_id", $log->form_cut_input_id_tujuan)->first();
            if ($checkStockerTo) {
                return array(
                    "status" => 400,
                    "message" => "Form sudah memiliki Stocker."
                );
            }
            if (checkClosingDate($toForm->waktu_selesai)) {
                return array(
                    "status" => 400,
                    "message" => "Periode sudah ditutup."
                );
            }

            if (!$fromForm || !$toForm) {
                DB::rollBack();
                return response()->json(['status' => 404, 'message' => 'Form asal atau tujuan tidak ditemukan.'], 404);
            }

            // 3. Find source and destination marker details
            $fromMarkerDetail = MarkerDetail::where('marker_id', $fromForm->marker_id)->where('size', $log->size_asal)->first();
            $toMarkerDetail = MarkerDetail::where('marker_id', $toForm->marker_id)->where('size', $log->size_tujuan)->first();

            if (!$fromMarkerDetail || !$toMarkerDetail) {
                DB::rollBack();
                return response()->json(['status' => 404, 'message' => 'Detail marker asal atau tujuan tidak ditemukan.'], 404);
            }

            // 4. Find and revert destination output (subtract from destination)
            $toOutput = FormCutInputDetailOutput::where('form_cut_input_id', $toForm->id)
                ->where('marker_input_detail_id', $toMarkerDetail->id)
                ->where('group_roll', $log->group_roll_asal) // Assuming destination group_roll is same as source
                ->first();

            if (!$toOutput || $toOutput->qty_output_aktual < $qty) {
                DB::rollBack();
                return response()->json(['status' => 400, 'message' => 'Kuantitas pada form tujuan tidak mencukupi untuk dibatalkan.'], 400);
            }

            $toOutput->qty_output_aktual -= $qty;
            $toOutput->save();

            // 5. Find and revert source output (add back to source)
            $fromOutput = FormCutInputDetailOutput::where('form_cut_input_id', $fromForm->id)
                ->where('marker_input_detail_id', $fromMarkerDetail->id)
                ->where('group_roll', $log->group_roll_asal)
                ->first();

            if ($fromOutput) {
                $fromOutput->qty_output_aktual += $qty;
                $fromOutput->save();
            } else {
                // This case should be rare, but as a safeguard if the source record was deleted.
                DB::rollBack();
                return response()->json(['status' => 404, 'message' => 'Data output asal tidak ditemukan untuk pengembalian.'], 404);
            }

            // 6. Set inactive to the log
            $log->is_active = 0;
            $log->save();

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Transfer berhasil dibatalkan dan kuantitas telah dikembalikan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting switching log: " . $e->getMessage());
            return response()->json(['status' => 500, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function exportFormCutInputDetailOutputLog(Request $request) {
        // 1. Filter tanggal & keyword (keyword = search global tabel)
        $dateFrom = $request->dateFrom ?? date("Y-m-d");
        $dateTo = $request->dateTo ?? date("Y-m-d");

        $query = $this->switchingLogQuery($dateFrom, $dateTo);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                foreach ($this->switchingLogColumns as $column) {
                    $q->orWhereRaw("CAST(`$column` AS CHAR) LIKE ?", ['%' . $keyword . '%']);
                }
            });
        }

        // 2. Eksekusi SQL Query
        $data_spreading = $query->get();

        // 3. Set Up FastExcel
        $fileName = 'report-log-switching-cutting.xlsx';
        $excel = FastExcel::create($fileName);
        $sheet =$excel->sheet();

        // Judul Laporan
        $sheet->writeRow(
            ['Report Log Switching Cutting'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        // Menggunakan variabel $dateFrom dan $dateTo yang benar
        $sheet->writeRow(
            ['Periode ' . $dateFrom . ' s/d ' .$dateTo],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        // Header tabel disesuaikan dengan data hasil SQL Query
        $header = [
            'Group Roll',
            'No Form',
            'Tgl Form',
            'WS',
            'Style',
            'Color',
            'Size',
            'Panel',
            'Part',
            'Total Out',
            'Total In',
            'Status',
            'Created By',
            'Created At',
            'Updated At'
        ];

        $sheet->writeRow($header,
            [
                'font-style' => 'bold',
                'border'     => 'thin',
            ]
        );

        // Loop data dari variabel $data_spreading
        foreach ($data_spreading as$row) {
            // Urutan kolom sama dengan $header
            $rows = [
                $row->group_roll ?? '',
                $row->no_form ?? '',
                $row->tanggal_form ?? '',
                $row->ws ?? '',
                $row->styleno ?? '',
                $row->color ?? '',
                $row->size ?? '',
                (string) $row->panel,
                (string) $row->part,
                (int) ($row->total_out ?? 0),
                (int) ($row->total_in ?? 0),
                $row->is_active ? 'Aktif' : 'Tidak Aktif',
                $row->created_by ?? '',
                $row->created_at ?? '',
                $row->updated_at ?? '',
            ];

            $sheet->writeRow($rows,
                [
                    'border' => 'thin',
                ]
            );
        }

        // Mengatur lebar kolom agar tampilan Excel rapi
        foreach (range('A', 'O') as $col) {
            $sheet->setColWidth($col, 18);
        }

        return $excel->download();
    }
}
