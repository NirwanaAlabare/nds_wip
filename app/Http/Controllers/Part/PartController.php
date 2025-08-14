<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part\MasterPart;
use App\Models\Part\MasterTujuan;
use App\Models\Part\MasterSecondary;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartForm;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerDetail;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\RackDetailStocker;
use App\Models\Dc\TrolleyStocker;
use App\Models\Dc\LoadingLine;
use App\Models\Stocker\ModifySizeQty;
use App\Services\StockerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
                    part.style,
                    part.color,
                    part.panel,
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
        $masterSecondary = MasterSecondary::all();

        $masterSecondaryOptions = "<option value=''>Pilih Proses</option>";
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
    public function store(Request $request)
    {
        $part = Part::select("kode")->orderBy("kode", "desc")->first();
        $partNumber = $part ? intval(substr($part->kode, -5)) + 1 : 1;
        $partCode = 'PRT' . sprintf('%05s', $partNumber);
        $totalPartDetail = intval($request["jumlah_part_detail"]);

        $validatedRequest = $request->validate([
            "ws_id" => "required",
            "ws" => "required",
            "color" => "required",
            "panel_id" => "required",
            "panel" => "required",
            "buyer" => "required",
            "style" => "required",
        ]);

        if ($totalPartDetail > 0) {
            $partStore = Part::create([
                "kode" => $partCode,
                "act_costing_id" => $validatedRequest['ws_id'],
                "act_costing_ws" => $validatedRequest['ws'],
                "color" => $validatedRequest['color'],
                "panel_id" => $validatedRequest['panel_id'],
                "panel" => $validatedRequest['panel'],
                "buyer" => $validatedRequest['buyer'],
                "style" => $validatedRequest['style'],
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username,
            ]);

            $timestamp = Carbon::now();
            $partId = $partStore->id;
            $partDetailData = [];
            for ($i = 0; $i < $totalPartDetail; $i++) {
                if ($request["part_details"][$i] && $request["proses"][$i] && $request["cons"][$i] && $request["cons_unit"][$i]) {
                    array_push($partDetailData, [
                        "part_id" => $partId,
                        "master_part_id" => $request["part_details"][$i],
                        "master_secondary_id" => $request["proses"][$i],
                        "cons" => $request["cons"][$i],
                        "unit" => $request["cons_unit"][$i],
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp,
                    ]);
                }
            }

            $partDetailStore = PartDetail::insert($partDetailData);

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

        if ($countPartForm < 1) {
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
                part.act_costing_ws,
                part.style,
                part.color,
                part.panel,
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

        $data_tujuan = DB::select("select tujuan isi, tujuan tampil from master_tujuan");

        return view("marker.part.manage-part-secondary", ["part" => $part, "data_part" => $data_part, "data_tujuan" => $data_tujuan, "page" => "dashboard-marker",  "subPageGroup" => "proses-marker", "subPage" => "part"]);
    }

    public function get_proses(Request $request)
    {
        $data_proses = DB::select("select id isi, proses tampil from master_secondary
        where tujuan = '" . $request->cbotuj . "'");
        $html = "<option value=''>Pilih Proses</option>";

        foreach ($data_proses as $dataproses) {
            $html .= " <option value='" . $dataproses->isi . "'>" . $dataproses->tampil . "</option> ";
        }

        return $html;
    }

    public function store_part_secondary(Request $request)
    {
        $validatedRequest = $request->validate([
            "cbotuj" => "required",
            "txtpart" => "required",
            "txtcons" => "required",
            "txtconsunit" => "required",
            "cboproses" => "required",
        ]);

        // $update_part = DB::update("
        //     update part_detail
        //     set
        //     master_secondary_id = '" . $validatedRequest['cboproses'] . "',
        //     cons = '$request->txtcons',
        //     unit = 'METER'
        //     where id = '$request->txtpart'");

        $update_part = PartDetail::updateOrCreate(['part_id' => $request->id, 'master_part_id' => $request->txtpart],[
            'master_secondary_id' => $validatedRequest['cboproses'],
            'cons' => $validatedRequest['txtcons'],
            'unit' => $validatedRequest['txtconsunit'],
        ]);

        if ($update_part) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data Part "' . $request->txtpart . '" berhasil diupdate',
            );
        }
        return array(
            'icon' => 'salah',
            'msg' => 'Data Part "' . $request->txtpart . '" berhasil diupdate',
        );
    }

    public function updatePartSecondary(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_tujuan" => "required",
            "edit_proses" => "required",
        ]);

        $checkDc = DcIn::leftJoin("stocker_input", "stocker_input.id_qr_stocker", "=", "dc_in_input.id_qr_stocker")->
            where("part_detail_id", $validatedRequest['edit_id'])->
            count();

        if ($checkDc < 1) {
            $update_part = PartDetail::where("id", $validatedRequest['edit_id'])->
                update([
                    'master_secondary_id' => $validatedRequest['edit_proses'],
                ]);

            if ($update_part) {
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
            $isExist = PartForm::where("part_id", $request->part_id)->where("form_id", $partForm['form_id'])->count();

            if ($isExist > 0) {
                $removeCutPlan = PartForm::where("part_id", $request->part_id)->where("form_id", $partForm['form_id'])->delete();

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
                CONCAT(nama_part, ' - ', bag) nama_part,
                master_secondary_id,
                ms.tujuan,
                ms.proses,
                cons,
                UPPER(unit) unit,
                stocker.total total_stocker
            FROM
                `part_detail` pd
                inner join master_part mp on pd.master_part_id = mp.id
                left join master_secondary ms on pd.master_secondary_id = ms.id
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
                part_id = '" . $request->id . "'
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
                DB::table("dc_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("rack_detail_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("trolley_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("loading_line")->whereIn('stocker_id', $stockerIds)->get()
            ]);

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
                'table' => 'datatable_list_part',
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
