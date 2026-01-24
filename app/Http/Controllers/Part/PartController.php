<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part\MasterPart;
use App\Models\Part\MasterTujuan;
use App\Models\Part\MasterSecondary;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartDetailItem;
use App\Models\Part\PartItem;
use App\Models\Part\PartDetailSecondary;
use App\Models\Part\PartForm;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutPiece;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\RackDetailStocker;
use App\Models\Dc\TrolleyStocker;
use App\Models\Dc\LoadingLine;
use App\Models\Stocker\ModifySizeQty;
use App\Services\PartService;
use App\Services\StockerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get Part Data
            $partQuery = Part::selectRaw("
                    part.id,
                    part.kode,
                    part.buyer,
                    part.act_costing_ws,
                    REPLACE(part.style, '\"', ' ') style,
                    part.color,
                    part.panel,
                    UPPER(COALESCE(part.panel_status, '')) panel_status,
                    COUNT(DISTINCT form_cut_input.id) total_form,
                    GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, '-', master_part.bag) ORDER BY master_part.nama_part SEPARATOR ' || ') part_details,
                    a.sisa
                ")->leftJoin("part_detail", "part_detail.part_id", "=", "part.id")
                ->leftJoin("master_part", "master_part.id", "part_detail.master_part_id")
                ->leftJoin("part_form", "part_form.part_id", "part.id")
                ->leftJoin("form_cut_input", "form_cut_input.id", "part_form.form_id")
                ->leftJoin(
                    DB::raw("
                        (
                            select
                            part_id,
                            count(id) total,
                            SUM(CASE WHEN cons IS NULL THEN 0 ELSE 1 END) terisi,
                            count(id) - SUM(CASE WHEN cons IS NULL THEN 0 ELSE 1 END) sisa
                            from part_detail
                            group by part_id
                        ) a
                    "),
                    "part.id", "=", "a.part_id"
                )
                ->groupBy("part.id");

            return DataTables::eloquent($partQuery)->
            filterColumn('act_costing_ws', function ($query, $keyword) {
                $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('style', function ($query, $keyword) {
                $query->whereRaw("LOWER(style) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('color', function ($query, $keyword) {
                $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('panel', function ($query, $keyword) {
                $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
            })->order(function ($query) {
                $query->orderBy('part.kode', 'desc')->orderBy('part.updated_at', 'desc');
            })->toJson();
        }

        return view("marker.part.part", ["page" => "dashboard-marker", "subPageGroup" => "proses-marker", "subPage" => "part"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        $partDetail = PartDetail::all();
        $masterParts = MasterPart::all();
        $masterTujuan = MasterTujuan::all();
        $masterSecondary = MasterSecondary::all();

        return view('marker.part.create-part', ['orders' => $orders, 'masterParts' => $masterParts, 'masterTujuan' => $masterTujuan, 'masterSecondary' => $masterSecondary, 'page' => 'dashboard-marker',  "subPageGroup" => "proses-marker", "subPage" => "part"]);
    }

    public function getOrderInfo(Request $request)
    {
        $order = DB::connection('mysql_sb')->table('act_costing')->selectRaw('
                act_costing.id,
                act_costing.kpno,
                act_costing.styleno,
                act_costing.qty order_qty,
                mastersupplier.supplier buyer,
                GROUP_CONCAT(DISTINCT so_det.color SEPARATOR ", ") colors
            ')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer')->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')->where('act_costing.id', $request->act_costing_id)->groupBy('act_costing.id')->first();

        return json_encode($order);
    }

    public function getColorList(Request $request)
    {
        $colors = DB::connection('mysql_sb')->select("
            select sd.color from so_det sd
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            where ac.id = '" . $request->act_costing_id . "' and sd.cancel = 'N'
            group by sd.color");

        return $colors ? $colors[0] : null;
    }

    public function getPanelList(Request $request)
    {
        $notInclude = "";
        $existParts = Part::where("act_costing_id", $request->act_costing_id)->get();

        if ($existParts->count() > 0) {
            $i = 0;
            $notInclude = "where nama_panel not in (";

            foreach ($existParts as $existPart) {
                $notInclude .= ($i == 0 ? "'" . $existPart->panel . "'" : ", '" . $existPart->panel . "'");
                $i++;
            }

            $notInclude .= ")";
        }

        $panels = DB::connection('mysql_sb')->select("
                select mp.id, nama_panel panel from
                    (select id_panel from bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join masteritem mi on k.id_item = mi.id_gen
                        where ac.id = '" . $request->act_costing_id . "' and k.status = 'M' and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and mi.mattype = 'F'
                        group by id_panel
                    ) a
                inner join masterpanel mp on a.id_panel = mp.id
                " . $notInclude . "
            ");

        $html = "<option value=''>Pilih Panel</option>";

        foreach ($panels as $panel) {
            $html .= " <option value='" . $panel->id . "'>" . $panel->panel . "</option> ";
        }

        return $html;
    }

    public function getComplementPanelList(Request $request)
    {
        $include = "";
        $complementPanels = Part::select("part.id", "part.panel")->where("part.act_costing_id", $request->act_costing_id)->where("part.panel", "!=", $request->panel)->get();

        $html = "<option value=''>Pilih Panel</option>";

        foreach ($complementPanels as $panel) {
            $html .= " <option value='" . $panel->id . "'>" . $panel->panel . "</option> ";
        }

        return $html;
    }

    public function getComplementPanelPartList(Request $request)
    {
        $complementPanelParts = PartDetail::select("part_detail.id", "master_part.nama_part")->leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            where("part_detail.part_id", $request->part_id)->
            get();

        $html = "<option value=''>Pilih Part</option>";

        foreach ($complementPanelParts as $panelPart) {
            $html .= " <option value='" . $panelPart->id . "'>" . ($panelPart->nama_part) . "</option> ";
        }

        return $html;
    }

    public function getMasterParts(Request $request)
    {
        $masterParts = MasterPart::all();

        $masterPartOptions = "<option value=''>Pilih Part</option>";
        foreach ($masterParts as $masterPart) {
            $masterPartOptions .= "<option value='".$masterPart->id."'>".$masterPart->nama_part." - ".$masterPart->bag."</option>";
        }

        return $masterPartOptions;
    }

    public function getMasterTujuan(Request $request)
    {
        $masterTujuan = MasterTujuan::all();

        $masterTujuanOptions = "<option value=''>Pilih Proses</option>";
        foreach ($masterTujuan as $tujuan) {
            $masterTujuanOptions .= "<option value='".$tujuan->id."'>".$tujuan->tujuan."</option>";
        }

        return $masterTujuanOptions;
    }

    public function getMasterSecondary(Request $request)
    {
        if ($request->type) {
            if ($request->type == "non secondary") {
                $masterSecondary = MasterSecondary::where("tujuan", "NON SECONDARY")->get();
            } else if ($request->type == "secondary") {
                $masterSecondary = MasterSecondary::whereIn("tujuan", ["SECONDARY DALAM", "SECONDARY LUAR"])->get();
            } else {
                $masterSecondary = MasterSecondary::all();
            }
        } else {
            $masterSecondary = MasterSecondary::all();
        }

        $masterSecondaryOptions = '';
        foreach ($masterSecondary as $secondary) {
            $masterSecondaryOptions .= "<option value='".$secondary->id."' data-tujuan='".$secondary->id_tujuan."'>".$secondary->proses." / ".$secondary->tujuan."</option>";
        }

        return $masterSecondaryOptions;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartService $partService, Request $request)
    {
        $part = Part::select("kode")->orderBy("kode", "desc")->first();
        $partNumber = $part ? intval(substr($part->kode, -5)) + 1 : 1;
        $partCode = 'PRT' . sprintf('%05s', $partNumber);
        $totalPartDetail = intval($request["jumlah_part_detail"]);
        $totalComplementPartDetail = intval($request["jumlah_complement_part_detail"]);

        $validatedRequest = $request->validate([
            "ws_id" => "required",
            "ws" => "required",
            "color" => "required",
            "panel_id" => "required",
            "panel" => "required",
            "buyer" => "required",
            "style" => "required",
            "panel_status" => "required",
        ]);

        // Check Remaining Panel
        $checkRemainingPanel = $partService->checkRemainingPanel($validatedRequest['ws_id'], $validatedRequest['panel_id'], $validatedRequest['panel'], $validatedRequest['panel_status']);
        if ($checkRemainingPanel && $checkRemainingPanel['status'] && $checkRemainingPanel['status'] != 200) {
            return $checkRemainingPanel;
        }

        if ($totalPartDetail > 0) {
            DB::beginTransaction();

            $partStore = Part::create([
                "kode" => $partCode,
                "act_costing_id" => $validatedRequest['ws_id'],
                "act_costing_ws" => $validatedRequest['ws'],
                "color" => $validatedRequest['color'],
                "panel_id" => $validatedRequest['panel_id'],
                "panel" => $validatedRequest['panel'],
                "buyer" => $validatedRequest['buyer'],
                "style" => $validatedRequest['style'],
                "panel_status" => $validatedRequest['panel_status'],
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username,
            ]);

            // Main/Regular Part
            $batch = Str::uuid();

            $timestamp = Carbon::now();
            $partId = $partStore->id;
            $partDetailSecondaryData = [];
            for ($i = 0; $i < $totalPartDetail; $i++) {
                if (isset($request["part_details"][$i]) && isset($request["cons"][$i]) && isset($request["cons_unit"][$i]) && isset($request["tujuan"][$i])) {
                    // Store to Part Detail
                    $currentPartDetail = PartDetail::create( [
                        "part_id" => $partId,
                        "batch" => $batch,
                        "master_part_id" => $request["part_details"][$i],
                        // "master_secondary_id" => $request["proses"][$i],
                        "tujuan" => $request["tujuan"][$i],
                        "cons" => $request["cons"][$i],
                        "from_part_detail" => null,
                        "part_status" => isset($request["main_part"][$i]) ? 'main' : 'regular',
                        "created_by" => Auth::user()->id,
                        "created_by_username" => Auth::user()->username,
                        "unit" => $request["cons_unit"][$i],
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp
                    ]);

                    // Part Detail Item
                    if ($currentPartDetail) {
                        if (isset($request["item"][$i]) && $request["item"][$i] && count($request["item"][$i]) > 0) {
                            $partItemData = [];

                            for ($j = 0; $j < count($request["item"][$i]); $j++) {
                                array_push($partItemData, [
                                    "part_detail_id" => $currentPartDetail->id,
                                    "bom_jo_item_id" => $request["item"][$i][$j],
                                    "created_at" => $timestamp,
                                    "updated_at" => $timestamp,
                                ]);
                            }

                            PartDetailItem::insert($partItemData);
                        }

                        // Store Secondaries
                        if (isset($request["urutan"][$i])) {
                            $currentSecondaries = explode(',', $request["urutan"][$i]);
                            for ($j = 0; $j < count($currentSecondaries); $j++) {
                                array_push($partDetailSecondaryData, [
                                    "part_detail_id" => $currentPartDetail->id,
                                    "master_secondary_id" => $currentSecondaries[$j],
                                    "urutan" => $j+1,
                                    "batch" => $batch,
                                    "created_by" => Auth::user()->id,
                                    "created_by_username" => Auth::user()->username,
                                    "created_at" => $timestamp,
                                    "updated_at" => $timestamp,
                                ]);
                            }
                        }
                    }
                }
            }

            // Complement Part
            for ($i = 0; $i < $totalComplementPartDetail; $i++) {
                if ($request["com_part_details"][$i] && $request["com_from_part_id"][$i]) {
                    $currentFromPartDetail = PartDetail::where("id", $request["com_from_part_id"][$i])->first();

                    if ($currentFromPartDetail) {
                        // Store to Part Detail
                        $currentPartDetail = PartDetail::create([
                            "part_id" => $partId,
                            "batch" => $batch,
                            "master_part_id" => $request["com_part_details"][$i],
                            // "master_secondary_id" => $request["com_proses"][$i],
                            "cons" => $currentFromPartDetail->cons,
                            "unit" => $currentFromPartDetail->unit,
                            "from_part_detail" => $request["com_from_part_id"][$i],
                            "tujuan" => $currentFromPartDetail->tujuan,
                            "part_status" => 'complement',
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                            "created_at" => $timestamp,
                            "updated_at" => $timestamp,
                        ]);

                        // Store Secondaries
                        $currentPartDetailSecondaries = $currentFromPartDetail->secondaries;
                        if ($currentPartDetail && $currentPartDetailSecondaries) {
                            foreach ($currentPartDetailSecondaries as $secondary) {
                                array_push($partDetailSecondaryData, [
                                    "part_detail_id" => $currentPartDetail->id,
                                    "master_secondary_id" => $secondary->master_secondary_id,
                                    "urutan" => $secondary->urutan,
                                    "batch" => $batch,
                                    "created_by" => Auth::user()->id,
                                    "created_by_username" => Auth::user()->username,
                                    "created_at" => $timestamp,
                                    "updated_at" => $timestamp,
                                ]);
                            }
                        }
                    }
                }
            }

            // Store Part Detail Secondary Detail
            $partDetailSecondaryStore = PartDetailSecondary::insert($partDetailSecondaryData);

            // Part Form IN
            $formCutData = FormCutInput::select('form_cut_input.id')->leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->where("marker_input.act_costing_id", $partStore->act_costing_id)->where("marker_input.act_costing_ws", $partStore->act_costing_ws)->where("marker_input.panel", $partStore->panel)->where("marker_input.buyer", $partStore->buyer)->where("marker_input.style", $partStore->style)->where("form_cut_input.status", "SELESAI PENGERJAAN")->orderBy("no_cut", "asc")->get();
            foreach ($formCutData as $formCut) {
                $isExist = PartForm::where("part_id", $partId)->where("form_id", $formCut->id)->count();

                if ($isExist < 1) {
                    $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
                    $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
                    $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

                    $addToPartForm = PartForm::create([
                        "kode" => $kodePartForm,
                        "part_id" => $partId,
                        "form_id" => $formCut->id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }
            }

            // Part Form Piece IN
            $formPieceData = FormCutPiece::select('form_cut_piece.id')->where("form_cut_piece.act_costing_id", $partStore->act_costing_id)->where("form_cut_piece.act_costing_ws", $partStore->act_costing_ws)->where("form_cut_piece.panel", $partStore->panel)->where("form_cut_piece.buyer", $partStore->buyer)->where("form_cut_piece.style", $partStore->style)->where("form_cut_piece.status", "complete")->orderBy("no_cut", "asc")->get();
            foreach ($formPieceData as $formPiece) {
                $isExist = PartForm::where("part_id", $partId)->where("form_pcs_id", $formPiece->id)->count();

                if ($isExist < 1) {
                    $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
                    $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
                    $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

                    $addToPartForm = PartForm::create([
                        "kode" => $kodePartForm,
                        "part_id" => $partId,
                        "form_pcs_id" => $formPiece->id,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => $partCode,
                "additional" => [],
                "redirect" => route('manage-part-form', ["id" => $partStore->id])
            );
        }

        return array(
            "status" => 400,
            "message" => "Harap pilih part",
            "additional" => []
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show(Part $part)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Part\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function edit(Part $part)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Part\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Part $part, $id = 0)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Part $part, $id = 0)
    {
        $countPartForm = PartForm::where("part_id", $id)->count();

        if ($countPartForm < 1 || Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0) {
            $deletePartForm = PartForm::where("part_id", $id)->delete();

            $deletePart = Part::where("id", $id)->delete();

            if ($deletePart) {
                return array(
                    'status' => 200,
                    'message' => 'Part berhasil dihapus',
                    'redirect' => '',
                    'table' => 'datatable-part',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => 400,
            'message' => 'Part ini tidak dapat dihapus',
            'redirect' => '',
            'table' => 'datatable-part',
            'additional' => [],
        );
    }

    public function managePartForm(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $formCutInputs = DB::select("
                SELECT
                    form_cut_input.id,
                    form_cut_input.id_marker,
                    form_cut_input.no_form,
                    COALESCE ( DATE ( form_cut_input.waktu_mulai ), form_cut_input.tgl_form_cut ) tgl_mulai_form,
                    users.NAME nama_meja,
                    marker_input.id AS marker_id,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.urutan_marker,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    GROUP_CONCAT(DISTINCT CONCAT((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE master_sb_ws.size  END  ), '(', marker_input_detail.ratio, ')'  )  ORDER BY master_size_new.urutan ASC SEPARATOR ' / '  ) marker_details,
                    form_cut_input.qty_ply,
                    form_cut_input.no_cut,
                    'GENERAL' as type
                FROM
                    `form_cut_input`
                    LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                    LEFT JOIN `marker_input_detail` ON `marker_input_detail`.`marker_id` = `marker_input`.`id`
                    LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `marker_input_detail`.`so_det_id`
                    LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `master_sb_ws`.`size`
                    LEFT JOIN `users` ON `users`.`id` = `form_cut_input`.`no_meja`
                    LEFT JOIN `part_form` ON `part_form`.`form_id` = `form_cut_input`.`id`
                WHERE
                    `form_cut_input`.`status` = 'SELESAI PENGERJAAN'
                    AND part_form.id IS NOT NULL
                    AND `part_form`.`part_id` = '".$id."'
                    AND `marker_input`.`act_costing_ws` = '".$request->act_costing_ws."'
                    AND `marker_input`.`panel` = '".$request->panel."'
                    AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 2 YEAR )
                GROUP BY
                    `form_cut_input`.`id`
            UNION
                 SELECT
                    form_cut_piece.id,
                    null as id_marker,
                    form_cut_piece.no_form,
                    COALESCE ( DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) tgl_mulai_form,
                    null nama_meja,
                    form_cut_piece.id AS marker_id,
                    form_cut_piece.act_costing_ws,
                    form_cut_piece.buyer,
                    null as urutan_marker,
                    form_cut_piece.style,
                    form_cut_piece.color,
                    form_cut_piece.panel,
                    GROUP_CONCAT(DISTINCT CONCAT((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE master_sb_ws.size  END  ), '(', form_cut_piece_detail_size.qty, ')'  )  ORDER BY master_size_new.urutan ASC SEPARATOR ' / '  ) marker_details,
                    SUM(form_cut_piece_detail.qty) total_qty,
                    form_cut_piece.no_cut,
                    'PIECE' as type
                FROM
                    `form_cut_piece`
                    LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                    LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                    LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `form_cut_piece_detail_size`.`so_det_id`
                    LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `master_sb_ws`.`size`
                    LEFT JOIN `part_form` ON `part_form`.`form_pcs_id` = `form_cut_piece`.`id`
                WHERE
                    `form_cut_piece`.`status` = 'complete'
                    AND part_form.id IS NOT NULL
                    AND `part_form`.`part_id` = '".$id."'
                    AND `form_cut_piece`.`act_costing_ws` = '".$request->act_costing_ws."'
                    AND `form_cut_piece`.`panel` = '".$request->panel."'
                    AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
                GROUP BY
                    `form_cut_piece`.`id`
                ORDER BY
                    CAST(no_cut as UNSIGNED),
                    color
            ");

            return Datatables::of($formCutInputs)->toJson();
        }

        $part = Part::selectRaw("
                part.id,
                part.kode,
                part.buyer,
                part.act_costing_ws,
                part.style,
                part.color,
                part.panel,
                GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) ORDER BY master_part.nama_part SEPARATOR ', ') part_details
            ")->leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->leftJoin("master_part", "master_part.id", "part_detail.master_part_id")->where("part.id", $id)->groupBy("part.id")->first();

        return view("marker.part.manage-part-form", ["part" => $part, "page" => "dashboard-marker",  "subPageGroup" => "proses-marker", "subPage" => "part"]);
    }

    public function managePartSecondary(Request $request, $id = 0)
    {
        if ($request->ajax()) {
            $formCutInputs = FormCutInput::selectRaw("
                    form_cut_input.id,
                    form_cut_input.id_marker,
                    form_cut_input.no_form,
                    form_cut_input.tgl_form_cut,
                    users.name nama_meja,
                    marker_input.act_costing_ws,
                    marker_input.buyer,
                    marker_input.urutan_marker,
                    marker_input.style,
                    marker_input.color,
                    marker_input.panel,
                    GROUP_CONCAT(DISTINCT CONCAT((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END), '(', marker_input_detail.ratio, ')') SEPARATOR ', ') marker_details,
                    form_cut_input.qty_ply,
                    form_cut_input.no_cut
                ")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
                leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size")->
                leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
                leftJoin("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                where("form_cut_input.status", "SELESAI PENGERJAAN")->
                whereRaw("part_form.id is not null")->
                where("part_form.part_id", $id)->
                where("marker_input.act_costing_ws", $request->act_costing_ws)->
                where("marker_input.panel", $request->panel)->
                groupBy("form_cut_input.id");

            return Datatables::eloquent($formCutInputs)->filterColumn('act_costing_ws', function ($query, $keyword) {
                $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('buyer', function ($query, $keyword) {
                $query->whereRaw("LOWER(buyer) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('style', function ($query, $keyword) {
                $query->whereRaw("LOWER(style) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('color', function ($query, $keyword) {
                $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('panel', function ($query, $keyword) {
                $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('nama_meja', function ($query, $keyword) {
                $query->whereRaw("LOWER(users.name) LIKE LOWER('%" . $keyword . "%')");
            })->order(function ($query) {
                $query->orderBy('form_cut_input.no_cut', 'asc');
            })->toJson();
        }

        $part = Part::selectRaw("
                part.id,
                part.kode,
                part.buyer,
                part.act_costing_id,
                part.act_costing_ws,
                part.style,
                part.color,
                part.panel,
                part.panel_status,
                GROUP_CONCAT(DISTINCT CONCAT(master_part.nama_part, ' - ', master_part.bag) ORDER BY master_part.nama_part SEPARATOR ', ') part_details
            ")->
            leftJoin("part_detail", "part_detail.part_id", "=", "part.id")->
            leftJoin("master_part", "master_part.id", "part_detail.master_part_id")->
            where("part.id", $id)->
            groupBy("part.id")->
            first();

        // $data_part = DB::select("select pd.id isi, concat(nama_part,' - ',bag) tampil from part_detail pd
        // inner join master_part mp on pd.master_part_id = mp.id
        // where part_id = '$id'");

        $data_part = MasterPart::all();
        $data_secondary = MasterSecondary::all();
        $data_tujuan = DB::select("select tujuan isi, tujuan tampil from master_tujuan");

        $complementPanels = Part::select("part.id", "part.panel")->where("part.act_costing_id", $part->act_costing_id)->where("part.color", $part->color)->where("part.panel", "!=", $part->panel)->get();

        $partItemList = DB::connection("mysql_sb")->select("
                select bom_jo_item.id, masteritem.itemdesc from bom_jo_item
                left join jo_det on jo_det.id_jo = bom_jo_item.id_jo
                left join so on so.id = jo_det.id_so
                left join act_costing on act_costing.id = so.id_cost
                left join masteritem on bom_jo_item.id_item = masteritem.id_item
                where act_costing.id = '".$part->act_costing_id."' and bom_jo_item.`status` = 'P' and matclass != 'CMT'
                group by bom_jo_item.id_item
            ");

        return view("marker.part.manage-part-secondary", ["part" => $part, "partItemList" => $partItemList, "data_part" => $data_part, "data_tujuan" => $data_tujuan, "data_secondary" => $data_secondary, "complementPanels" => $complementPanels, "page" => "dashboard-marker",  "subPageGroup" => "proses-marker", "subPage" => "part"]);
    }

    public function get_proses(Request $request)
    {
        $data_proses = DB::select("select id isi, proses tampil from master_secondary where tujuan = '" . $request->cbotuj . "'");
        $html = "<option value=''>Pilih Proses</option>";

        foreach ($data_proses as $dataproses) {
            $html .= " <option value='" . $dataproses->isi . "'>" . $dataproses->tampil . "</option> ";
        }

        return $html;
    }

    public function store_part_secondary(Request $request)
    {
        // Deprecated
            // $update_part = DB::update("
            //     update part_detail
            //     set
            //     master_secondary_id = '" . $validatedRequest['cboproses'] . "',
            //     cons = '$request->txtcons',
            //     unit = 'METER'
            //     where id = '$request->txtpart'");

        // IF COMPLEMENT
        if ($request->is_complement) {
            $validatedRequest = $request->validate([
                // "cbotuj" => "required",
                // "cboproses" => "required",
                "txtpart" => "required",
                "partSource" => "required",
                // "txtcons" => "required",
                // "txtconsunit" => "required",
            ]);

            // Check Part Detail
            $checkPartDetail = PartDetail::where("part_id", $request->id)->where('master_part_id', $request->txtpart)->first();
            if (!$checkPartDetail) {

                // Part Detail Source
                $currentFromPartDetail = PartDetail::where("id", $request["partSource"])->first();
                if ($currentFromPartDetail) {

                    // Create New Part Detail
                    $createNewPartDetail = PartDetail::create([
                        'part_id' => $request->id,
                        'master_part_id' => $validatedRequest['txtpart'],
                        'part_status' => 'complement',
                        'from_part_detail' => $currentFromPartDetail->id,
                        'cons' => $currentFromPartDetail->cons,
                        'unit' => $currentFromPartDetail->unit,
                        'tujuan' => $currentFromPartDetail->tujuan,
                        "created_by" => Auth::user()->id,
                        "created_by_username" => Auth::user()->username,
                    ]);

                    if ($createNewPartDetail) {
                        // Get Current Secondaries
                        $currentPartDetailSecondaries = $currentFromPartDetail->secondaries;
                        if ($currentPartDetailSecondaries) {
                            // Store Secondaries
                            $batch = Str::uuid();
                            $timestamp = Carbon::now();
                            $partDetailSecondaryData = [];
                            foreach ($currentPartDetailSecondaries as $secondary) {
                                array_push($partDetailSecondaryData, [
                                    "part_detail_id" => $createNewPartDetail->id,
                                    "master_secondary_id" => $secondary->master_secondary_id,
                                    "urutan" => $secondary->urutan,
                                    "batch" => $batch,
                                    "created_by" => Auth::user()->id,
                                    "created_by_username" => Auth::user()->username,
                                    "created_at" => $timestamp,
                                    "updated_at" => $timestamp,
                                ]);
                            }

                            PartDetailSecondary::insert($partDetailSecondaryData);
                        }

                        return array(
                            'icon' => 'benar',
                            'msg' => 'Data Part "' . $request->txtpart . '" berhasil diupdate',
                        );
                    } else {
                        return array(
                            'icon' => 'salah',
                            'msg' => 'Data Part "' . $request->txtpart . '" gagal disimpan.',
                        );
                    }
                } else {
                    return array(
                        'icon' => 'salah',
                        'msg' => 'Data Part Sumber "' . $request->txtpart . '" sudah tidak ada.',
                    );
                }
            } else {
                return array(
                    'icon' => 'salah',
                    'msg' => 'Data Part "' . $request->txtpart . '" sudah ada.',
                );
            }
        }
        // IF NOT COMPLEMENT
        else {
            $validatedRequest = $request->validate([
                // "cbotuj" => "required",
                // "cboproses" => "required",
                "txtpart" => "required",
                "txtcons" => "required",
                "txtconsunit" => "required",
                "tujuan" => "required",
            ]);

            // Check Part Detail
            $checkPartDetail = PartDetail::where("part_id", $request->id)->where('master_part_id', $request->txtpart)->first();
            if (!$checkPartDetail) {

                // Part Detail
                $createNewPartDetail = PartDetail::create([
                    'part_id' => $request->id,
                    'master_part_id' => $request->txtpart,
                    'part_status' => 'regular',
                    'cons' => $validatedRequest['txtcons'],
                    'unit' => $validatedRequest['txtconsunit'],
                    'tujuan' => $validatedRequest['tujuan'],
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username,
                ]);

                if ($createNewPartDetail) {
                    // Insert Part Detail Secondary Urutan
                    if ($request["urutan"]) {
                        $currentSecondaries = explode(',', $request["urutan"]);
                        if ($currentSecondaries) {
                            $batch = Str::uuid();
                            $timestamp = Carbon::now();
                            $partDetailSecondaryData = [];
                            for ($j = 0; $j < count($currentSecondaries); $j++) {
                                array_push($partDetailSecondaryData, [
                                    "part_detail_id" => $createNewPartDetail->id,
                                    "master_secondary_id" => $currentSecondaries[$j],
                                    "urutan" => $j+1,
                                    "batch" => $batch,
                                    "created_by" => Auth::user()->id,
                                    "created_by_username" => Auth::user()->username,
                                    "created_at" => $timestamp,
                                    "updated_at" => $timestamp,
                                ]);
                            }
                        }

                        PartDetailSecondary::insert($partDetailSecondaryData);
                    }

                    return array(
                        'icon' => 'benar',
                        'msg' => 'Data Part "' . $request->txtpart . '" berhasil diupdate',
                    );
                }
            } else {
                return array(
                    'icon' => 'salah',
                    'msg' => 'Data Part "' . $request->txtpart . '" sudah ada.',
                );
            }
        }

        return array(
            'icon' => 'salah',
            'msg' => 'Data Part "' . $request->txtpart . '" gagal diupdate',
        );
    }

    public function getEditPartDetailProcess(Request $request) {
        if ($request->edit_id) {
            $currentPartDetail = PartDetail::select("master_secondary_id")->where("id", $request->edit_id)->first();

            if ($currentPartDetail && $currentPartDetail->master_secondary_id) {
                return $currentPartDetail->master_secondary_id;
            }
        }

        return null;
    }

    public function getEditPartDetailItems(Request $request) {
        if ($request->edit_id) {
            $currentPartDetailItems = PartDetailItem::select("bom_jo_item_id")->where("part_detail_id", $request->edit_id)->pluck('bom_jo_item_id');

            if ($currentPartDetailItems) {
                return $currentPartDetailItems;
            }
        }

        return null;
    }

    public function updatePartSecondary(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_tujuan" => "required",
        ]);

        // Phase 1
        $checkDc = DcIn::leftJoin("stocker_input", "stocker_input.id_qr_stocker", "=", "dc_in_input.id_qr_stocker")->
            where("part_detail_id", $validatedRequest['edit_id'])->
            count();

        if ($checkDc < 1 || Auth::user()->roles->whereIn("nama_role", ["superadmin"])) {
            $update_part = PartDetail::where("id", $validatedRequest['edit_id'])->
                update([
                    'tujuan' => $validatedRequest['edit_tujuan'],
                ]);

            if ($update_part) {
                // Phase 2 (Update Master Part)
                $partDetail = PartDetail::where("id", $validatedRequest['edit_id'])->first();
                if ($request->edit_master_part_id && $request->edit_master_part_id != $partDetail->master_part_id) {
                    $updatePartDetail = $partDetail->update([
                        "master_part_id" => $request->edit_master_part_id
                    ]);
                }

                // Phase 3 (Update Cons)
                if ($request->edit_cons && $request->edit_cons != $partDetail->edit_cons) {
                    $updatePartDetail = $partDetail->update([
                        "cons" => $request->edit_cons
                    ]);
                }

                // Phase 3 (Update Secondary(s))
                if ($request->edit_urutan) {
                    // Create New Secondaries
                    $partDetailSecondaryData = [];
                    $batch = Str::uuid();
                    $timestamp = Carbon::now();
                    $currentSecondaries = explode(',', $request->edit_urutan);
                    for ($j = 0; $j < count($currentSecondaries); $j++) {


                        array_push($partDetailSecondaryData, [
                            "part_detail_id" => $validatedRequest['edit_id'],
                            "master_secondary_id" => $currentSecondaries[$j],
                            "urutan" => $j+1,
                            "batch" => $batch,
                            "created_by" => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                            "created_at" => $timestamp,
                            "updated_at" => $timestamp,
                        ]);
                    }
                    $storeCurrentSecondaries = PartDetailSecondary::insert($partDetailSecondaryData);

                    if ($storeCurrentSecondaries) {
                        // Delete Old Secondaries
                        $deleteOldSecondaries = PartDetailSecondary::where("part_detail_id", $validatedRequest['edit_id'])->
                            where("batch", "!=", $batch)->
                            delete();
                    }
                }

                // Phase 4 (Update Part Status)
                if ($request->edit_part_status && $request->edit_part_status != $partDetail->part_status) {
                    // Update Current Part Status
                    $updatePartDetail = $partDetail->update([
                        "part_status" => $request->edit_part_status
                    ]);

                    if ($updatePartDetail && $request->edit_part_status == "main") {
                        // Update Other Part Details (Main Part to Regular Part)
                        $updateMainPartDetail = PartDetail::where("part_id", $partDetail->part_id)->
                            where("id", "!=", $partDetail->id)->
                            where("part_status", $request->edit_part_status)->
                            update([
                                "part_status" => "regular"
                            ]);
                    }
                }

                // Phase 5 (Update Part Item)
                if ($request->edit_item && count($request->edit_item) > 0) {
                    // Delete Current Part Detail
                    PartDetailItem::where("part_detail_id", $partDetail->id)->delete();

                    // Repopulate Part Detail Item
                    $partItemData = [];
                    for ($i = 0; $i < count($request->edit_item); $i++) {
                        array_push($partItemData, [
                            "part_detail_id" => $partDetail->id,
                            "bom_jo_item_id" => $request->edit_item[$i],
                            "created_at" => $timestamp,
                            "updated_at" => $timestamp,
                        ]);
                    }

                    PartDetailItem::upsert($partItemData, ['part_detail_id', 'bom_jo_item_id'], ["updated_at"]);
                }

                return array(
                    'status' => '201',
                    'table' => 'datatable_list_part',
                    'message' => 'Data Part Secondary "' . $validatedRequest["edit_id"] . '" berhasil diupdate',
                );
            }
        } else {
            return array(
                'status' => '400',
                'table' => 'datatable_list_part',
                'message' => 'Data Part Secondary "' . $validatedRequest["edit_id"] . '" sudah masuk ke DC',
            );
        }

        return array(
            'status' => '400',
            'table' => 'datatable_list_part',
            'message' => 'Data Part Secondary "' . $validatedRequest["edit_id"] . '" gagal diupdate',
        );
    }

    public function updatePartSecondaryComplement(Request $request) {
        $validatedRequest = $request->validate([
            "edit_com_id" => "required",
            "edit_com_master_part_id" => "required",
            "edit_com_from_part_id" => "required",
        ]);

        // Status & Message
        $status = "";
        $message = "";

        // Phase 1 (Check DC In Input)
        $checkDc = DcIn::leftJoin("stocker_input", "stocker_input.id_qr_stocker", "=", "dc_in_input.id_qr_stocker")->
            where("part_detail_id", $validatedRequest['edit_com_id'])->
            count();

        if ($checkDc < 1 || Auth::user()->roles->whereIn("nama_role", ["superadmin"])) {
            // When DC In was not inputted

            // Phase 2 (Check Part Detail)
            $currentPartDetail = PartDetail::where("id", $validatedRequest['edit_com_id'])->first();
            if ($currentPartDetail) {
                // Phase 3 (Check New Part Detail's Sources)
                $fromPartDetail = PartDetail::where("id", $validatedRequest['edit_com_from_part_id'])->first();
                if ($fromPartDetail) {
                    // Phase 4 (Delete Part Detail's old Secondaries)
                    $deletePartDetailSecondary = PartDetailSecondary::where("part_detail_id", $validatedRequest['edit_com_id'])->delete();
                    if ($deletePartDetailSecondary) {
                        // Phase 5 (Create New Part Detail's Secondaries)
                        $partDetailSecondaryData = [];
                        $batch = Str::uuid();
                        $timestamp = Carbon::now();
                        $currentSecondaries = $fromPartDetail->secondaries;
                        if ($currentSecondaries) {
                            foreach ($currentSecondaries as $secondary) {
                                array_push($partDetailSecondaryData, [
                                    "part_detail_id" => $validatedRequest['edit_com_id'],
                                    "master_secondary_id" => $secondary->master_secondary_id,
                                    "urutan" => $secondary->urutan,
                                    "batch" => $batch,
                                    "created_by" => Auth::user()->id,
                                    "created_by_username" => Auth::user()->username,
                                    "created_at" => $timestamp,
                                    "updated_at" => $timestamp,
                                ]);
                            }

                            PartDetailSecondary::insert($partDetailSecondaryData);
                        }

                        // Phase 6 (Update Part Detail)
                        PartDetail::where("id", $validatedRequest['edit_com_id'])->update([
                            "master_part_id" => $validatedRequest['edit_com_master_part_id'],
                            "from_part_detail" => $validatedRequest['edit_com_from_part_id'],
                        ]);

                        $status = "200";
                        $message = 'Data Part Secondary "' . $validatedRequest["edit_com_id"] . '" berhasil disimpan.';
                    } else {
                        $status = "400";
                        $message = 'Data Part Secondary Tujuan "' . $validatedRequest["edit_com_id"] . '" tidak ditemukan.';
                    }
                } else {
                    $status = "400";
                    $message = 'Data Part Secondary "' . $validatedRequest["edit_com_id"] . '" gagal dihapus.';
                }
            } else {
                $status = "400";
                $message = 'Data Part Secondary Tujuan "' . $validatedRequest["edit_com_id"] . '" tidak ditemukan.';
            }
        } else {
            $status = "400";
            $message = 'Data Part Secondary Tujuan "' . $validatedRequest["edit_com_id"] . '" sudah masuk ke DC.';
        }

        return array(
            'status' => $status,
            'table' => 'datatable_list_part_complement',
            'message' => $message,
        );
    }

    public function getFormCut(Request $request, $id = 0)
    {
        $formCutInputs = DB::select("
            SELECT
                form_cut_input.id,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                COALESCE ( DATE ( form_cut_input.waktu_mulai ), form_cut_input.tgl_form_cut ) tgl_mulai_form,
                users.NAME nama_meja,
                marker_input.id AS marker_id,
                marker_input.act_costing_ws ws,
                marker_input.buyer,
                marker_input.urutan_marker,
                marker_input.color,
                marker_input.style,
                marker_input.panel,
                GROUP_CONCAT( DISTINCT CONCAT((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE master_sb_ws.size END ), '(', marker_input_detail.ratio, ')'  ) ORDER BY master_size_new.urutan ASC SEPARATOR ' / ' ) marker_details,
                form_cut_input.qty_ply,
                form_cut_input.no_cut,
                'GENERAL' as type
            FROM
                `form_cut_input`
                LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                LEFT JOIN `marker_input_detail` ON `marker_input_detail`.`marker_id` = `marker_input`.`id`
                LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `marker_input_detail`.`so_det_id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `master_sb_ws`.`size`
                LEFT JOIN `users` ON `users`.`id` = `form_cut_input`.`no_meja`
                LEFT JOIN `part_form` ON `part_form`.`form_id` = `form_cut_input`.`id`
            WHERE
                `form_cut_input`.`status` = 'SELESAI PENGERJAAN'
                AND part_form.id IS NULL
                AND `marker_input`.`act_costing_ws` = '".$request->act_costing_ws."'
                AND `marker_input`.`panel` = '".$request->panel."'
                AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
            `form_cut_input`.`id`

            UNION

            SELECT
                form_cut_piece.id,
                null as id_marker,
                form_cut_piece.no_form,
                COALESCE ( DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) tgl_mulai_form,
                null as nama_meja,
                form_cut_piece.id AS marker_id,
                form_cut_piece.act_costing_ws ws,
                form_cut_piece.buyer,
                null as urutan_marker,
                form_cut_piece.color,
                form_cut_piece.style,
                form_cut_piece.panel,
                GROUP_CONCAT( DISTINCT CONCAT(( CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN  CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE master_sb_ws.size END ), '(', form_cut_piece_detail_size.qty, ')' ) ORDER BY master_size_new.urutan ASC SEPARATOR ' / ' ) marker_details,
                SUM(form_cut_piece_detail_size.qty) as qty_ply,
                form_cut_piece.no_cut,
                'PIECE' as type
            FROM
                `form_cut_piece`
                LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `form_cut_piece_detail_size`.`so_det_id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `master_sb_ws`.`size`
                LEFT JOIN `part_form` ON `part_form`.`form_pcs_id` = `form_cut_piece`.`id`
            WHERE
                `form_cut_piece`.`status` = 'complete'
                AND part_form.id IS NULL
                AND `form_cut_piece`.`act_costing_ws` = '".$request->act_costing_ws."'
                AND `form_cut_piece`.`panel` = '".$request->panel."'
                AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_piece`.`id`
            ORDER BY
                no_cut,
                color
        ");

        return Datatables::of($formCutInputs)->toJson();
    }

    public function storePartForm(Request $request, StockerService $stockerService)
    {
        $success = [];
        $fail = [];
        $exist = [];

        foreach ($request->partForms as $partForm) {
            $isExist = PartForm::where("part_id", $request->part_id)->where("form_id", $partForm['no_form'])->count();

            if ($isExist < 1) {
                $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
                $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
                $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

                if ($partForm['type'] == 'PIECE') {
                    $addToPartForm = PartForm::create([
                        "kode" => $kodePartForm,
                        "part_id" => $request->part_id,
                        "form_pcs_id" => $partForm['form_id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                } else {
                    $addToPartForm = PartForm::create([
                        "kode" => $kodePartForm,
                        "part_id" => $request->part_id,
                        "form_id" => $partForm['form_id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }


                if ($addToPartForm) {
                    array_push($success, ['no_form' => $partForm['no_form']]);
                } else {
                    array_push($fail, ['no_form' => $partForm['no_form']]);
                }
            } else {
                array_push($exist, ['no_form' => $partForm['no_form']]);
            }
        }

        if (count($success) > 0) {
            ini_set('max_execution_time', 360000);

            // Reorder Stocker Numbering
            $stockerService->reorderStockerNumbering($request->part_id);

            return array(
                'status' => 200,
                'message' => 'Form berhasil ditambahkan',
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

    public function destroyPartForm(Request $request)
    {
        $success = [];
        $fail = [];
        $exist = [];

        foreach ($request->partForms as $partForm) {
            if ($partForm['type'] == "PIECE") {
                $isExist = PartForm::where("part_id", $request->part_id)->where("form_pcs_id", $partForm['form_id'])->count();
            } else {
                $isExist = PartForm::where("part_id", $request->part_id)->where("form_id", $partForm['form_id'])->count();
            }

            if ($isExist > 0) {
                if ($partForm['type'] == "PIECE") {
                    $removeCutPlan = PartForm::where("part_id", $request->part_id)->where("form_pcs_id", $partForm['form_id'])->delete();
                } else {
                    $removeCutPlan = PartForm::where("part_id", $request->part_id)->where("form_id", $partForm['form_id'])->delete();
                }

                if ($removeCutPlan) {
                    array_push($success, ['no_form' => $partForm['no_form']]);
                } else {
                    array_push($fail, ['no_form' => $partForm['no_form']]);
                }
            } else {
                array_push($exist, ['no_form' => $partForm['no_form']]);
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Part Form berhasil disingkirkan',
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

    public function showPartForm(Request $request)
    {
        $formCutInputs = DB::select("
            SELECT
                part_detail.id part_detail_id,
                form_cut_input.id form_cut_id,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                DATE ( form_cut_input.waktu_selesai ) tanggal_selesai,
                users.NAME nama_meja,
                marker_input.id marker_id,
                marker_input.act_costing_ws,
                marker_input.buyer,
                marker_input.urutan_marker,
                marker_input.style,
                marker_input.color,
                marker_input.panel,
                form_cut_input.no_cut,
                form_cut_input.total_lembar,
                part_form.kode kode_part_form,
                part.kode kode_part,
                GROUP_CONCAT( DISTINCT master_part.nama_part ORDER BY master_part.nama_part ASC SEPARATOR ' || ' ) part_details,
                GROUP_CONCAT(DISTINCT CONCAT((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE master_sb_ws.size END ),'(',marker_input_detail.ratio,')' ) ORDER BY master_sb_ws.dest,master_size_new.urutan SEPARATOR ' / ' ) marker_details,
                'GENERAL' as type
            FROM
                `form_cut_input`
                LEFT JOIN `part_form` ON `part_form`.`form_id` = `form_cut_input`.`id`
                LEFT JOIN `part` ON `part`.`id` = `part_form`.`part_id`
                LEFT JOIN `part_detail` ON `part_detail`.`part_id` = `part`.`id`
                LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                LEFT JOIN `marker_input_detail` ON `marker_input_detail`.`marker_id` = `marker_input`.`id`
                LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `marker_input_detail`.`so_det_id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `master_sb_ws`.`size`
                LEFT JOIN `users` ON `users`.`id` = `form_cut_input`.`no_meja`
            WHERE
                part_form.id IS NOT NULL
                AND `marker_input_detail`.`ratio` > 0
                AND `part`.`id` = '".$request->id."'
                AND form_cut_input.tgl_form_cut >= DATE (NOW()- INTERVAL 2 YEAR)
            GROUP BY
                form_cut_input.id

            UNION

            SELECT
                part_detail.id part_detail_id,
                form_cut_piece.id form_cut_id,
                null as id_marker,
                form_cut_piece.no_form,
                DATE ( form_cut_piece.updated_at ) tanggal_selesai,
                null nama_meja,
                form_cut_piece.id marker_id,
                form_cut_piece.act_costing_ws,
                form_cut_piece.buyer,
                null urutan_marker,
                form_cut_piece.style,
                form_cut_piece.color,
                form_cut_piece.panel,
                form_cut_piece.no_cut,
                SUM(form_cut_piece_detail_size.qty) total_lembar,
                part_form.kode kode_part_form,
                part.kode kode_part,
                GROUP_CONCAT( DISTINCT master_part.nama_part ORDER BY master_part.nama_part ASC SEPARATOR ' || ' ) part_details,
                GROUP_CONCAT(DISTINCT CONCAT((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT( master_sb_ws.size, ' - ', master_sb_ws.dest ) ELSE master_sb_ws.size END ),'(',form_cut_piece_detail_size.qty,')' ) ORDER BY master_sb_ws.dest,master_size_new.urutan SEPARATOR ' / ' ) marker_details,
                'PIECE' as type
            FROM
                `form_cut_piece`
                LEFT JOIN `part_form` ON `part_form`.`form_pcs_id` = `form_cut_piece`.`id`
                LEFT JOIN `part` ON `part`.`id` = `part_form`.`part_id`
                LEFT JOIN `part_detail` ON `part_detail`.`part_id` = `part`.`id`
                LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `form_cut_piece_detail_size`.`so_det_id`
                LEFT JOIN `master_size_new` ON `master_size_new`.`size` = `master_sb_ws`.`size`
            WHERE
                part_form.id IS NOT NULL
                AND `form_cut_piece_detail`.`qty` > 0
                AND `part`.`id` = '".$request->id."'
                AND form_cut_piece.tanggal >= DATE (NOW()- INTERVAL 2 YEAR)
            GROUP BY
                form_cut_piece.id
            order by
                CAST(no_cut AS UNSIGNED) asc,
                color asc
            ");

        return Datatables::of($formCutInputs)->toJson();
    }

    public function datatable_list_part(Request $request)
    {
        $list_part = DB::select(
            "
            SELECT
                pd.id,
                CONCAT(mp.nama_part, ' - ', mp.bag) nama_part,
                master_part_id,
                master_secondary_id,
                UPPER(COALESCE(pd.tujuan, ms.tujuan)) as tujuan,
                COALESCE(pds.proses, ms.proses) as proses,
                pd.cons,
                UPPER(pd.unit) unit,
                COALESCE(pd.part_status, '-') part_status,
                stocker.total total_stocker,
                GROUP_CONCAT(DISTINCT masteritem.itemdesc) item
            FROM
                `part_detail` pd
                inner join master_part mp on pd.master_part_id = mp.id
                left join master_secondary ms on pd.master_secondary_id = ms.id
                left join part_detail_item on part_detail_item.part_detail_id = pd.id
                left join signalbit_erp.bom_jo_item on bom_jo_item.id = part_detail_item.bom_jo_item_id
                left join signalbit_erp.masteritem on masteritem.id_item = bom_jo_item.id_item
                left join (
                    select
                        part_detail_id,
                        GROUP_CONCAT(CONCAT(master_secondary.proses, ' - ', master_secondary.tujuan) SEPARATOR ' // ') as proses
                    from
                        part_detail_secondary
                        left join master_secondary on master_secondary.id = part_detail_secondary.master_secondary_id
                    group by
                        part_detail_id
                ) pds on pds.part_detail_id = pd.id
                left join (
                    select
                        COUNT(id) total,
                        part_detail_id
                    from
                        stocker_input
                    group by
                        part_detail_id
                ) stocker on stocker.part_detail_id = pd.id
                left join part_detail_item pdi on pdi.part_detail_id = pd.id
                left join signalbit_erp.bom_jo_item bji on bji.id = pdi.bom_jo_item_id
                left join signalbit_erp.masteritem mi on mi.id_item = bji.id_item
            where
                part_id = '" . $request->id . "' and
                (part_status != 'complement')
            GROUP BY
                pd.id
            order by
                pd.id asc
            "
        );

        return DataTables::of($list_part)->toJson();
    }

    public function datatable_list_part_complement(Request $request)
    {
        $list_part = DB::select(
            "
            SELECT
                pd.id com_id,
                CONCAT(mp.nama_part, ' - ', mp.bag) com_nama_part,
                CONCAT(from_master_part.nama_part, ' - ', from_master_part.bag) as com_from_part,
                pd.master_part_id com_master_part_id,
                pd.master_secondary_id com_master_secondary_id,
                COALESCE(pd.tujuan, ms.tujuan) as com_tujuan,
                COALESCE(pds.proses, ms.proses) as com_proses,
                pd.cons com_cons,
                UPPER(pd.unit) com_unit,
                COALESCE(pd.part_status, '-') com_part_status,
                stocker.total com_total_stocker,
                GROUP_CONCAT(DISTINCT masteritem.itemdesc) com_item
            FROM
                `part_detail` pd
                inner join master_part mp on pd.master_part_id = mp.id
                left join master_secondary ms on pd.master_secondary_id = ms.id
                left join part_detail_item on part_detail_item.part_detail_id = pd.id
                left join signalbit_erp.bom_jo_item on bom_jo_item.id = part_detail_item.bom_jo_item_id
                left join signalbit_erp.masteritem on masteritem.id_item = bom_jo_item.id_item
                left join part_detail as from_part_detail on from_part_detail.id = pd.from_part_detail
                left join master_part as from_master_part on from_master_part.id = from_part_detail.master_part_id
                left join (
                    select
                        part_detail_id,
                        GROUP_CONCAT(CONCAT(master_secondary.proses, ' - ', master_secondary.tujuan) SEPARATOR ' // ') as proses
                    from
                        part_detail_secondary
                        left join master_secondary on master_secondary.id = part_detail_secondary.master_secondary_id
                    group by
                        part_detail_id
                ) pds on pds.part_detail_id = pd.id
                left join (
                    select
                        COUNT(id) total,
                        part_detail_id
                    from
                        stocker_input
                    group by
                        part_detail_id
                ) stocker on stocker.part_detail_id = pd.id
            where
                pd.part_id = '" . $request->id . "' and
                (pd.part_status = 'complement')
            GROUP BY
                pd.id
            order by
                pd.id asc
            "
        );

        return DataTables::of($list_part)->toJson();
    }

    public function destroyPartDetail($id=0) {
        ini_set('max_execution_time', 3600);

        $partDetail = PartDetail::with('masterPart')->find($id);

        if ($partDetail->delete()) {
            // Delete related stocker input
            $stockers = Stocker::where('part_detail_id', $id)->get();
            $stockerIdQrs = $stockers->pluck('id_qr_stocker')->toArray();
            $stockerIds = $stockers->pluck('id')->toArray();

            // Log the deletion
            Log::channel('deletePartDetail')->info([
                "Deleting Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                DB::table("part_detail")->where('id', $id)->get(),
                DB::table("part_detail_secondary")->where('part_detail_id', $id)->get(),
                DB::table("dc_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("rack_detail_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("trolley_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("loading_line")->whereIn('stocker_id', $stockerIds)->get()
            ]);

            $deletePartDetailSecondary = PartDetailSecondary::where('part_detail_id', $id)->delete();
            $deleteStocker = Stocker::where('part_detail_id', $id)->delete();
            $deleteDc = DCIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryIn = SecondaryIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryInHouse = SecondaryInHouse::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteRackDetailStocker = RackDetailStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteTrolleyStocker = TrolleyStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteLoadingLine = LoadingLine::whereIn('stocker_id', $stockerIds)->delete();

            return array(
                'status' => 200,
                'message' => 'Part Detail <br> "'.$partDetail->masterPart->nama_part.'" <br> berhasil dihapus. <br> "'.$partDetail->id.'"',
                'redirect' => '',
                'table' => $partDetail->part_status == 'complement' ? 'datatable_list_part_complement' : 'datatable_list_part_complement',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Master Part <br> "'.$partDetail->masterPart->nama_part.'" <br> gagal dihapus. <br> "'.$partDetail->id.'"',
            'redirect' => '',
            'table' => 'datatable_list_part',
            'additional' => [],
        );
    }
}
