<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\Cutting\FormCut;
use App\Models\Cutting\FormCutDetail;
use App\Models\Cutting\FormCutDetailLap;
use App\Models\Cutting\FormCutDetailSambungan;
use App\Models\Cutting\FormCutLostTime;
use App\Models\Cutting\FormCutPlan;
use App\Models\Cutting\ScannedItem;
use App\Models\Part;
use App\Models\PartForm;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class FormCutManualController extends Controller
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

            if ($request->dateFrom) {
                $additionalQuery .= "and (cutting_plan.tgl_plan >= '" . $request->dateFrom . "' or a.updated_at >= '". $request->dateFrom ."')";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and (cutting_plan.tgl_plan <= '" . $request->dateTo . "' or a.updated_at <= '". $request->dateTo ."')";
            }

            if (Auth::user()->type == "meja") {
                $additionalQuery .= " and a.meja_id = '" . Auth::user()->id . "' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        a.marker_id like '%" . $request->search["value"] . "%' OR
                        a.meja_id like '%" . $request->search["value"] . "%' OR
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
                    a.meja_id,
                    a.marker_id,
                    a.no_form,
                    a.tgl_form_cut,
                    b.kode id_marker,
                    b.act_costing_ws ws,
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
                    cutting_plan.app,
                    GROUP_CONCAT(CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC) marker_details
                FROM cutting_plan
                left join form_cut_input a on a.no_form = cutting_plan.no_form_cut_input
                left join marker_input b on a.marker_id = b.id
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.meja_id
                where
                    b.cancel = 'N' and
                    a.tipe_form_cut = 'MANUAL'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY b.cancel asc, a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        return view('cutting.cutting-form-manual.cutting-form-manual', ['page' => 'dashboard-cutting', "subPageGroup" => "proses-cutting", "subPage" => "form-cut-input"]);
    }

    public function getRatio(Request $request)
    {
        $markerId = $request->cbomarker ? $request->cbomarker : 0;

        $data_ratio = DB::select("
            select
                *
            from
                marker_input_detail
            where
                marker_id = '" . $markerId . "'
        ");

        return DataTables::of($data_ratio)->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (session('currentManualForm')) {
            return redirect()->route('process-manual-form-cut', ["id" => session('currentManualForm')]);
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view("cutting.cutting-form-manual.create-cutting-form-manual", [
            "orders" => $orders,
            "meja" => $meja,
            'page' => 'dashboard-cutting',
            "subPageGroup" => "proses-cutting",
            "subPage" => "form-cut-input"
        ]);
    }

    public function createNew()
    {
        session()->forget('currentManualForm');

        return array(
            'redirect' => route('create-manual-form-cut')
        );
    }

    public function getOrderInfo(Request $request)
    {
        $order = DB::connection('mysql_sb')->table('act_costing')->selectRaw('act_costing.id, act_costing.kpno, act_costing.styleno, act_costing.qty order_qty, mastersupplier.supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer')->where('id', $request->act_costing_id)->first();

        return json_encode($order);
    }

    public function getColorList(Request $request)
    {
        $colors = DB::connection('mysql_sb')->select("select sd.color from so_det sd
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            where ac.id = '" . $request->act_costing_id . "' and sd.cancel = 'N'
            group by sd.color");

        $html = "<option value=''>Pilih Color</option>";

        foreach ($colors as $color) {
            $html .= " <option value='" . $color->color . "'>" . $color->color . "</option> ";
        }

        return $html;
    }

    public function getSizeList(Request $request)
    {
        $sizeQuery = DB::table("master_sb_ws")->selectRaw("
                master_sb_ws.id_so_det so_det_id,
                master_sb_ws.ws no_ws,
                master_sb_ws.color,
                concat(master_sb_ws.size, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN ' - ' ELSE '' END, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN master_sb_ws.dest ELSE '' END) size,
                master_sb_ws.qty order_qty,
                COALESCE(marker_input_detail.ratio, 0) ratio,
                COALESCE(marker_input_detail.cut_qty, 0) cut_qty
            ")->
            where("master_sb_ws.id_act_cost", $request->act_costing_id)->
            where("master_sb_ws.color", $request->color);

        if ($request->marker_id) {
            $sizeQuery->
            leftJoin('marker_input_detail', function($join) use ($request) {
                $join->on('marker_input_detail.so_det_id', '=', 'master_sb_ws.id_so_det');
                $join->on('marker_input_detail.marker_id', '=', DB::raw($request->marker_id));
            })->
            leftJoin('master_size_new', 'master_size_new.size', '=', 'master_sb_ws.size')->
            leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id');
        } else {
            $sizeQuery->
            leftJoin('marker_input_detail', 'marker_input_detail.so_det_id', '=', 'master_sb_ws.id_so_det')->
            leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')->
            leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size");
        }

        $sizes = $sizeQuery->groupBy("master_sb_ws.id_so_det")->orderBy("master_size_new.urutan")->get();

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($sizes)),
            "recordsFiltered" => intval(count($sizes)),
            "data" => $sizes
        ]);
    }

    public function getPanelList(Request $request)
    {
        $panels = DB::connection('mysql_sb')->select("
                select nama_panel panel from
                    (select id_panel from bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join masteritem mi on k.id_item = mi.id_gen
                        where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and k.status = 'M'
                        and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                        group by id_panel
                    ) a
                inner join masterpanel mp on a.id_panel = mp.id
            ");

        $html = "<option value=''>Pilih Panel</option>";

        foreach ($panels as $panel) {
            $html .= " <option value='" . $panel->panel . "'>" . $panel->panel . "</option> ";
        }

        return $html;
    }

    public function getNumber(Request $request)
    {
        $number = DB::connection('mysql_sb')->select("
                select k.cons cons_ws,sum(sd.qty) order_qty from bom_jo_item k
                    inner join so_det sd on k.id_so_det = sd.id
                    inner join so on sd.id_so = so.id
                    inner join act_costing ac on so.id_cost = ac.id
                    inner join masteritem mi on k.id_item = mi.id_gen
                    inner join masterpanel mp on k.id_panel = mp.id
                where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and mp.nama_panel ='" . $request->panel . "' and k.status = 'M'
                and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                group by sd.color, k.id_item, k.unit
                limit 1
            ");

        return json_encode($number ? $number[0] : null);
    }

    public function getCount(Request $request)
    {
        $countMarker = Marker::where('act_costing_id', $request->act_costing_id)->where('color', $request->color)->where('panel', $request->panel)->count() + 1;

        return $countMarker ? $countMarker : 1;
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
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function show(FormCut $formCut)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCut $formCut)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormCut $formCut)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCut $formCut)
    {
        //
    }

    /**
     * Process the form cut input.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return \Illuminate\Http\Response
     */
    public function process($id = 0)
    {
        $formCutQuery = FormCut::selectRaw("*, marker_input.kode as id_marker")->leftJoin("marker_input", "marker_input.id", "=", "form_cut_input.marker_id")->leftJoin("users", "users.id", "=", "form_cut_input.meja_id")->where('form_cut_input.id', $id);
        if (Auth::user()->type != "admin") {
            $formCutQuery->where("form_cut_input.meja_id", Auth::user()->id);
        }

        $formCutData = $formCutQuery->first();
        if (!$formCutData) {
            session()->forget('currentManualForm');

            return redirect()->route('create-manual-form-cut');
        }

        $actCostingData = DB::connection("mysql_sb")->table('act_costing')->selectRaw('act_costing.id id, act_costing.styleno style, mastersupplier.Supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', 'act_costing.id_buyer')->groupBy('act_costing.id')->where('act_costing.id', $formCutData->act_costing_id)->get();

        $markerDetailData = MarkerDetail::selectRaw("
                marker_input.id marker_id,
                marker_input.kode kode_marker,
                concat(master_sb_ws.size, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN ' - ' ELSE '' END, CASE WHEN (master_sb_ws.dest != '-' AND master_sb_ws.dest is not null) THEN master_sb_ws.dest ELSE '' END) size,
                marker_input_detail.so_det_id,
                marker_input_detail.ratio,
                marker_input_detail.cut_qty
            ")->
            leftJoin("marker_input", "marker_input.id", "=", "marker_input_detail.marker_id")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "marker_input_detail.so_det_id")->
            where("marker_input.kode", $formCutData->kode)->
            where("marker_input.cancel", "N")->
            groupBy("marker_input_detail.so_det_id")->
            get();

        if (Auth::user()->type == "meja" && Auth::user()->id != $formCutData->meja_id) {
            return Redirect::to('/home');
        }

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view("cutting.cutting-form-manual.cutting-form-manual-process", [
            'id' => $id,
            'formCutData' => $formCutData,
            'actCostingData' => $actCostingData,
            'markerDetailData' => $markerDetailData,
            'orders' => $orders,
            'meja' => $meja,
            'page' => 'dashboard-cutting',
            "subPageGroup" => "proses-cutting",
            "subPage" => "form-cut-input"
        ]);
    }

    public function getNumberData(Request $request)
    {
        $numberData = DB::connection('mysql_sb')->table("bom_jo_item")->selectRaw("
                bom_jo_item.cons cons_ws
            ")->
            leftJoin("so_det", "so_det.id", "=", "bom_jo_item.id_so_det")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("masteritem", "masteritem.id_gen", "=", "bom_jo_item.id_item")->
            leftJoin("masterpanel", "masterpanel.id", "=", "bom_jo_item.id_panel")->
            where("act_costing.id", $request->act_costing_id)->where("so_det.color", $request->color)->
            where("masterpanel.nama_panel", $request->panel)->
            where("bom_jo_item.status", "M")->
            where("bom_jo_item.cancel", "N")->
            where("so_det.cancel", "N")->
            where("so.cancel_h", "N")->
            where("act_costing.status", "CONFIRM")->
            where("masteritem.mattype", "F")->
            where("masteritem.mattype", "F")->
            groupBy("so_det.color", "bom_jo_item.id_item", "bom_jo_item.unit")->first();

        return json_encode($numberData);
    }

    public function getScannedItem($id = 0)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                whs_bppb_det.id_roll,
                whs_bppb_det.item_desc detail_item,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll roll,
                whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN (SELECT * FROM whs_lokasi_inmaterial GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '".$id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");
        if ($newItem) {
            $scannedItem = ScannedItem::where('id_roll', $id)->where('id_item', $newItem[0]->id_item)->first();

            if ($scannedItem) {
                if (floatval($newItem[0]->qty - $scannedItem->qty_in + $scannedItem->qty) > 0 ) {
                    $scannedItem->qty_stok = $newItem[0]->qty_stok;
                    $scannedItem->qty_in = $newItem[0]->qty;
                    $scannedItem->qty = floatval($newItem[0]->qty - $scannedItem->qty_in + $scannedItem->qty);
                    $scannedItem->save();

                    return json_encode($scannedItem);
                }

                $formCutDetail = FormCutDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();

                return "Roll sudah terpakai di form '".$formCutDetail->no_form_cut_input."'";
            }

            return json_encode($newItem ? $newItem[0] : null);
        }

        $item = DB::connection("mysql_sb")->select("
            SELECT
                br.id id_roll,
                mi.itemdesc detail_item,
                mi.id_item,
                goods_code,
                supplier,
                bpbno_int,
                pono,
                invno,
                ac.kpno,
                roll_no roll,
                roll_qty qty,
                lot_no lot,
                bpb.unit,
                kode_rak
            FROM
                bpb_roll br
                INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                AND brh.id_jo = bpb.id_jo
                AND brh.id_item = bpb.id_item
                INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                INNER JOIN so ON jd.id_so = so.id
                INNER JOIN act_costing ac ON so.id_cost = ac.id
                INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
            WHERE
                br.id = '" . $id . "'
                AND cast(
                roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                LIMIT 1
        ");
        if ($item) {
            $scannedItem = ScannedItem::where('id_roll', $id)->where('id_item', $item[0]->id_item)->first();

            if ($scannedItem) {
                if (floatval($scannedItem->qty) > 0) {
                    return json_encode($scannedItem);
                }

                $formCutDetail = FormCutDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();

                return "Roll sudah terpakai di form '".$formCutDetail->no_form_cut_input."'";
            }

            return json_encode($item ? $item[0] : null);
        }

        return null;
    }

    public function getItem(Request $request) {
        $items = $items = DB::connection("mysql_sb")->select("
            select ac.id,ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno, mi.id_item, mi.itemdesc from jo_det jd
            inner join (select * from so where so_date >= '2023-01-01') so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
                inner join bom_jo_item k on jd.id_jo = k.id_jo
                inner join masteritem mi on k.id_item = mi.id_gen
            where jd.cancel = 'N' and k.cancel = 'N' and mi.Mattype = 'F' and ac.id = '".$request->act_costing_id."'
            group by id_cost, k.id_item
        ");

        return json_encode($items ? $items : null);
    }

    public function startProcess(Request $request, $id = 0)
    {
        $date = date('Y-m-d');
        $hari = substr($date, 8, 2);
        $bulan = substr($date, 5, 2);
        $now = Carbon::now();

        $lastForm = FormCut::select("no_form")->whereRaw("no_form LIKE '".$hari."-".$bulan."%'")->orderBy("id", "desc")->first();
        $urutan =  $lastForm ? (str_replace($hari."-".$bulan."-", "", $lastForm->no_form) + 1) : 1;

        $noForm = "$hari-$bulan-$urutan";

        if ($id) {
            $currentForm = FormCut::where("id", $id)->first();

            $updateFormCut = FormCut::where("id", $id)->
                update([
                    "meja_id" => Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id,
                    "status" => "PENGERJAAN MARKER",
                    "waktu_mulai" => ($request->startTime ? $request->startTime : Carbon::now()),
                    "app" => "Y",
                    "app_by" => Auth::user()->id,
                    "app_notes" => "MANUAL FORM CUT",
                    "app_at" => $now,
                ]);

            if ($updateFormCut) {
                session(['currentManualForm' => $id]);

                return array(
                    "status" => 200,
                    "message" => "alright",
                    "data" => $currentForm,
                    "additional" => ['id' => $id, 'no_form' => $currentForm->no_form],
                );
            }
        } else {
            $storeFormCut = FormCut::create([
                "tgl_form_cut" => $date,
                "no_form" => $noForm,
                "meja_id" => Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id,
                "status" => "PENGERJAAN MARKER",
                "tipe_form_cut" => "MANUAL",
                "waktu_mulai" => (($request->startTime == null || $request->startTime == "" || !preg_match("/^([01]?[0-9]|2[0-3]):([0-5]?[0-9]):([0-5]?[0-9])$/", $request->startTime)) ? Carbon::now() : $request->startTime),
                "app" => "Y",
                "app_by" => Auth::user()->id,
                "app_notes" => "MANUAL FORM CUT",
                "app_at" => $now,
            ]);

            if ($storeFormCut) {
                $dateFormat = date("dmY", strtotime($date));
                $formCutPlan = "CP-" . $dateFormat;

                $formCutPlan = FormCutPlan::create([
                    "no_cut_plan" => $formCutPlan,
                    "tgl_plan" => $date,
                    "form_cut_id" => $storeFormCut->id,
                    "no_form_cut_input" => $noForm,
                    "app" => "Y",
                    "app_by" => Auth::user()->id,
                    "app_at" => $now,
                    "created_by" => Auth::user()->id,
                    "created_by_username" => Auth::user()->username,
                ]);

                if ($formCutPlan) {
                    session(['currentManualForm' => $storeFormCut->id]);

                    return array(
                        "status" => 200,
                        "message" => "alright",
                        "data" => $storeFormCut,
                        "additional" => ['id' => $storeFormCut->id, 'no_form' => $noForm],
                    );
                }
            }
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "data" => null,
            "additional" => [],
        );
    }

    public function jumpToDetail(Request $request, $id = 0) {
        $updateFormCut = FormCut::where("id", $request->id)->update([
            "meja_id" => Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id,
            "status" => "PENGERJAAN FORM CUTTING DETAIL",
            "shell" => $request->shell,
        ]);

        if ($updateFormCut) {
            return array(
                "status" => 200,
                "message" => "alright",
            );
        }

        return array(
            "status" => 400,
            "message" => "fkin hell",
        );
    }

    public function storeMarker(Request $request)
    {
        $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
        $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
        $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);
        $totalQty = 0;
        $totalQtyEach = 0;

        $validatedRequest = $request->validate([
            "id" => "required",
            "no_form" => "required",
            "tgl_form" => "required",
            "act_costing_id" => "required",
            "no_ws" => "required",
            "buyer" => "required",
            "style" => "required",
            "cons_ws_marker" => "nullable",
            "color" => "required",
            "panel" => "required",
            "gelar_qty" => "required|numeric|gt:0",
            "urutan_marker" => "required|numeric|gt:0",
            "tipe_marker" => "required"
        ]);

        $idForm = $validatedRequest['id'];
        $noForm = $validatedRequest['no_form'];
        $tglForm = $validatedRequest['tgl_form'];

        foreach ($request["cut_qty"] as $qty) {
            $totalQtyEach += $qty;
        }

        if ($totalQtyEach == 0) {
            $totalQty = $request['total_qty_cut_ply'];
        } else if ($totalQtyEach > 0) {
            $totalQty = $totalQtyEach;
        }

        if ($totalQty > 0) {
            if ($request->marker_id) {
                $markerStore = Marker::where('id', $request->marker_id)->first();

                if ($markerStore) {
                    $markerUpdate = Marker::where('id', $request->marker_id)->
                        update([
                            'tgl_cutting' => $tglForm,
                            'act_costing_id' => $validatedRequest['act_costing_id'],
                            'act_costing_ws' => $validatedRequest['no_ws'],
                            'buyer' => $validatedRequest['buyer'],
                            'style' => $validatedRequest['style'],
                            'cons_ws' => $validatedRequest['cons_ws_marker'] ? $validatedRequest['cons_ws_marker'] : '0',
                            'color' => $validatedRequest['color'],
                            'panel' => $validatedRequest['panel'],
                            'gelar_qty' => $validatedRequest['gelar_qty'],
                            'po_marker' => $request->po ? $request->po : '-',
                            'urutan_marker' => $validatedRequest['urutan_marker'],
                            'tipe_marker' => $validatedRequest['tipe_marker'],
                            'cancel' => 'N',
                        ]);
                }
            } else {
                $markerStore = Marker::create([
                    'tgl_cutting' => $tglForm,
                    'kode' => $markerCode,
                    'act_costing_id' => $validatedRequest['act_costing_id'],
                    'act_costing_ws' => $validatedRequest['no_ws'],
                    'buyer' => $validatedRequest['buyer'],
                    'style' => $validatedRequest['style'],
                    'cons_ws' => $validatedRequest['cons_ws_marker'] ? $validatedRequest['cons_ws_marker'] : '0',
                    'color' => $validatedRequest['color'],
                    'panel' => $validatedRequest['panel'],
                    'gelar_qty' => $validatedRequest['gelar_qty'],
                    'po_marker' => $request->po ? $request->po : '-',
                    'urutan_marker' => $validatedRequest['urutan_marker'],
                    'tipe_marker' => $validatedRequest['tipe_marker'],
                    'cancel' => 'N',
                ]);
            }

            if ($totalQtyEach > 0) {
                $timestamp = Carbon::now();
                $markerId = $markerStore->id;
                $markerDetailData = [];

                for ($i = 0; $i < intval($request['total_size']); $i++) {
                    array_push($markerDetailData, [
                        "marker_id" => $markerId,
                        "so_det_id" => $request["so_det_id"][$i],
                        "size" => $request["size"][$i],
                        "ratio" => $request["ratio"][$i],
                        "cut_qty" => $request["cut_qty"][$i],
                        "cancel" => 'N',
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp,
                    ]);
                }

                $markerDetailStore = MarkerDetail::insert($markerDetailData);
            }

            $updateFormCut = FormCut::where("id", $idForm)->update([
                "meja_id" => $request->meja_id,
                "marker_id" => $request->marker_id ? $request->marker_id : $markerId,
                "status" => "PENGERJAAN FORM CUTTING DETAIL",
                "shell" => $request->shell,
                "qty_ply" => $validatedRequest['gelar_qty']
            ]);

            if ($updateFormCut) {
                return array(
                    "status" => 200,
                    "message" => "alright",
                    "additional" => ["id_marker" => ($markerStore && $markerStore->kode ? $markerStore->kode : $markerCode), "marker_id" => ($request->marker_id ? $request->marker_id : $markerId)]
                );
            }

            return array(
                "status" => 400,
                "message" => "nothing really matter anymore",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Total Cut Qty Kosong",
            "additional" => [],
        );
    }

    public function nextProcessOne($id = 0, Request $request)
    {
        $updateFormCut = FormCut::where("id", $id)->update([
            "meja_id" => Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id,
            "status" => "PENGERJAAN FORM CUTTING DETAIL",
            "shell" => $request->shell
        ]);

        if ($updateFormCut) {
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

    public function nextProcessTwo($id = 0, Request $request)
    {
        $validatedRequest = $request->validate([
            "marker_id" => "required",
            "p_act" => "required|numeric",
            "unit_p_act" => "required",
            "comma_act" => "required|numeric",
            "unit_comma_act" => "required",
            "l_act" => "required|numeric",
            "unit_l_act" => "required",
            "cons_ws" => "required|numeric",
            "cons_act" => "required|numeric",
            "cons_pipping" => "required|numeric",
            "cons_ampar" => "required|numeric",
            "est_pipping" => "required|numeric",
            "est_pipping_unit" => "required",
            "est_kain" => "required|numeric",
            "est_kain_unit" => "required",
            "gramasi" => "required|numeric|gt:0",
            "cons_marker" => "required|numeric|gt:0",
        ]);

        $updateMarker = Marker::where('id', $validatedRequest['marker_id'])->update([
            "panjang_marker" => $validatedRequest['p_act'],
            "unit_panjang_marker" => $validatedRequest['unit_p_act'],
            "comma_marker" => $validatedRequest['comma_act'],
            "unit_comma_marker" => $validatedRequest['unit_comma_act'],
            "lebar_marker" => $validatedRequest['l_act'],
            "unit_lebar_marker" => $validatedRequest['unit_l_act'],
            "cons_ws" => $validatedRequest['cons_ws'],
            "cons_marker" => $validatedRequest['cons_marker'],
            "gramasi" => $validatedRequest['gramasi'],
        ]);

        if ($updateMarker) {
            $updateFormCut = FormCut::where("id", $id)->update([
                "meja_id" => (Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id),
                "status" => "PENGERJAAN FORM CUTTING SPREAD",
                "p_act" => $validatedRequest['p_act'],
                "unit_p_act" => $validatedRequest['unit_p_act'],
                "comma_p_act" => $validatedRequest['comma_act'],
                "unit_comma_p_act" => $validatedRequest['unit_comma_act'],
                "l_act" => $validatedRequest['l_act'],
                "unit_l_act" => $validatedRequest['unit_l_act'],
                // "cons_act" => $validatedRequest['cons_act'],
                "cons_pipping" => $validatedRequest['cons_pipping'],
                "cons_ampar" => $validatedRequest['cons_act'],
                "est_pipping" => $validatedRequest['est_pipping'],
                "est_pipping_unit" => $validatedRequest['est_pipping_unit'],
                "est_kain" => $validatedRequest['est_kain'],
                "est_kain_unit" => $validatedRequest['est_kain_unit']
            ]);

            if ($updateFormCut) {
                return array(
                    "status" => 200,
                    "message" => "alright",
                    "additional" => [],
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function getTimeRecord($id = 0, $noForm = 0)
    {
        $timeRecordSummary = FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where("form_cut_id", $id)->where("no_form_cut_input", $noForm)->where('status', '!=', 'not complete')->where('status', '!=', 'extension')->whereRaw("form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)")->orderByRaw('CAST(form_cut_input_detail.id as UNSIGNED) asc')->get();

        return json_encode($timeRecordSummary);
    }

    public function storeTimeRecord(Request $request)
    {
        $validatedRequest = $request->validate([
            "id" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "meja_id" => "required",
            "color_act" => "nullable",
            "current_id_item" => "required",
            "detail_item" => "nullable",
            "current_group" => "required",
            "current_roll" => "nullable",
            "current_roll_buyer" => "nullable",
            "current_qty" => "required",
            "current_qty_real" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_average_time" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "required",
            "current_pemakaian_lembar" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_sambungan" => "required",
            "p_act" => "required",
        ]);

        $status = 'complete';

        if ($validatedRequest['current_sisa_gelaran'] > 0) {
            $status = 'need extension';
        }

        $beforeData = FormCutDetail::select('group_roll', 'group_stocker')->where('form_cut_id', $validatedRequest['id'])->where('no_form_cut_input', $validatedRequest['no_form_cut_input'])->whereRaw('(form_cut_input_detail.status = "complete" || form_cut_input_detail.status = "need extension" || form_cut_input_detail.status = "extension complete")')->orderBy('id', 'desc')->first();
        $groupStocker = $beforeData ? ($beforeData->group_roll  == $validatedRequest['current_group'] ? $beforeData->group_stocker : $beforeData->group_stocker + 1) : 1;
        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        $storeTimeRecordSummary = FormCutDetail::updateOrCreate(
            ["form_cut_id" => $validatedRequest['id'], "no_form_cut_input" => $validatedRequest['no_form_cut_input'], 'form_cut_input_detail.status' => 'not complete'],
            [
                "id_roll" => $validatedRequest['current_id_roll'],
                "id_item" => $validatedRequest['current_id_item'],
                "color_act" => $validatedRequest['color_act'],
                "detail_item" => $validatedRequest['detail_item'],
                "group_roll" => $validatedRequest['current_group'],
                "lot" => $request["current_lot"],
                "roll" => $validatedRequest['current_roll'],
                "roll_buyer" => $validatedRequest['current_roll_buyer'],
                "qty" => $itemQty,
                "unit" => $itemUnit,
                "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                "sambungan" => $validatedRequest['current_sambungan'],
                "est_amparan" => $validatedRequest['current_est_amparan'],
                "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                "average_time" => $validatedRequest['current_average_time'],
                "kepala_kain" => $validatedRequest['current_kepala_kain'],
                "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                "reject" => $validatedRequest['current_reject'],
                "sisa_kain" => $validatedRequest['current_sisa_kain'],
                "pemakaian_lembar" => $validatedRequest['current_pemakaian_lembar'],
                "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                "short_roll" => $validatedRequest['current_short_roll'],
                "piping" => $validatedRequest['current_piping'],
                "status" => $status,
                "metode" => $request->metode ? $request->metode : "scan",
                "group_stocker" => $groupStocker,
                "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
            ]
        );

        if ($storeTimeRecordSummary) {
            FormCut::where("id", $validatedRequest["id"])->where("no_form", $validatedRequest["no_form_cut_input"])->update([
                "meja_id" => (Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id),
                "total_lembar" => DB::raw('total_lembar + '.$validatedRequest['current_lembar_gelaran']),
            ]);

            $sambunganRoll = $request['sambungan_roll'] ? array_filter($request['sambungan_roll'], function ($var) {
                return ($var > 0);
            }) : [];

            if ($sambunganRoll && count($sambunganRoll) > 0) {
                for ($i = 0; $i < count($sambunganRoll); $i++) {
                    if ($sambunganRoll[$i] > 0) {
                        $storeSambungan = FormCutDetailSambungan::updateOrCreate(
                            ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "sambungan_ke" => $i+1],
                            [
                                "sambungan_roll" => $sambunganRoll[$i],
                            ]
                        );
                    }
                }
            }

            // $itemRemain = $itemQty - floatval($validatedRequest['current_total_pemakaian_roll']) - floatval($validatedRequest['current_kepala_kain']) - floatval($validatedRequest['current_sisa_tidak_bisa']) - floatval($validatedRequest['current_reject']) - floatval($validatedRequest['current_piping']);;
            $itemRemain = $validatedRequest['current_sisa_kain'];

            if ($status == 'need extension') {
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['current_id_roll']],
                    [
                        "id_item" => $validatedRequest['current_id_item'],
                        "color" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemRemain > 0 ? 0 : $itemRemain,
                        "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + ".$validatedRequest['current_total_pemakaian_roll']),
                        "unit" => $itemUnit,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                    ]
                );

                $storeTimeRecordSummaryExt = FormCutDetail::create([
                    "form_cut_id" => $validatedRequest['id'],
                    "group_roll" => $validatedRequest['current_group'],
                    "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                    "id_sambungan" => $storeTimeRecordSummary->id,
                    "status" => "extension",
                    "group_stocker" => $groupStocker,
                ]);

                if ($storeTimeRecordSummaryExt) {
                    return array(
                        "status" => 200,
                        "message" => "alright",
                        "additional" => [
                            FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->first(),
                            FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummaryExt->id)->first()
                        ],
                    );
                }
            } else {
                ScannedItem::updateOrCreate(
                    ["id_roll" => $validatedRequest['current_id_roll']],
                    [
                        "id_item" => $validatedRequest['current_id_item'],
                        "color" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemRemain,
                        "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + ".$validatedRequest['current_total_pemakaian_roll']),
                        "unit" => $itemUnit,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                    ]
                );
            }

            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [
                    FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->first(),
                    null
                ],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function storeThisTimeRecord(Request $request)
    {
        $lap = $request->lap;

        $itemQty = ($request["current_unit"] != "KGM" ? floatval($request['current_qty']) : floatval($request['current_qty_real']));
        $itemUnit = ($request["current_unit"] != "KGM" ? "METER" : $request['current_unit']);

        $storeTimeRecordSummary = FormCutDetail::
            updateOrCreate(
                ["form_cut_id" => $request->id, "no_form_cut_input" => $request->no_form_cut_input, 'form_cut_input_detail.status' => 'not complete'],
                [
                    "id_roll" => $request->current_id_roll,
                    "id_item" => $request->current_id_item,
                    "color_act" => $request->color_act,
                    "detail_item" => $request->detail_item,
                    "group_roll" => $request->current_group,
                    "lot" => $request->current_lot,
                    "roll" => $request->current_roll,
                    "roll_buyer" => $request->current_roll_buyer,
                    "qty" => $itemQty,
                    "unit" => $itemUnit,
                    "sisa_gelaran" => $request->current_sisa_gelaran,
                    "sambungan" => $request->current_sambungan,
                    "est_amparan" => $request->current_est_amparan,
                    "lembar_gelaran" => $request->current_lembar_gelaran,
                    "average_time" => $request->current_average_time,
                    "kepala_kain" => $request->current_kepala_kain,
                    "sisa_tidak_bisa" => $request->current_sisa_tidak_bisa,
                    "reject" => $request->current_reject,
                    "sisa_kain" => $request->current_sisa_kain,
                    "pemakaian_lembar" => $request->current_pemakaian_lembar,
                    "total_pemakaian_roll" => $request->current_total_pemakaian_roll,
                    "short_roll" => $request->current_short_roll,
                    "piping" => $request->current_piping,
                    "status" => "not complete",
                    "metode" => $request->metode ? $request->metode : "scan",
                    "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                ]
            );

        if ($storeTimeRecordSummary) {
            $now = Carbon::now();

            if ($lap > 0) {
                $storeTimeRecordLap = FormCutDetailLap::updateOrCreate(
                    ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "lembar_gelaran_ke" => $lap],
                    [
                        "waktu" => $request["time_record"][$lap]
                    ]
                );
            }

            if ($request['sambungan_roll'] && count($request['sambungan_roll']) > 0) {
                for ($i = 0; $i < count($request['sambungan_roll']); $i++) {
                    if ($request['sambungan_roll'][$i] > 0) {
                        $storeSambungan = FormCutDetailSambungan::updateOrCreate(
                            ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "sambungan_ke" => $i+1],
                            [
                                "sambungan_roll" => $request['sambungan_roll'][$i],
                            ]
                        );
                    }
                }
            }

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

    public function storeTimeRecordExtension(Request $request)
    {
        $lap = 1;

        $validatedRequest = $request->validate([
            "id" => "required",
            "status_sambungan" => "required",
            "id_sambungan" => "required",
            "current_id_roll" => "nullable",
            "no_form_cut_input" => "required",
            "meja_id" => "required",
            "color_act" => "nullable",
            "current_id_item" => "required",
            "detail_item" => "nullable",
            "current_group" => "required",
            "current_roll" => "nullable",
            "current_roll_buyer" => "nullable",
            "current_qty" => "required",
            "current_qty_real" => "required",
            "current_unit" => "required",
            "current_sisa_gelaran" => "required",
            "current_est_amparan" => "required",
            "current_lembar_gelaran" => "required",
            "current_average_time" => "required",
            "current_kepala_kain" => "required",
            "current_sisa_tidak_bisa" => "required",
            "current_reject" => "required",
            "current_sisa_kain" => "nullable",
            "current_pemakaian_lembar" => "required",
            "current_short_roll" => "required",
            "current_piping" => "required",
            "current_total_pemakaian_roll" => "required",
            "current_sambungan" => "required"
        ]);

        $beforeData = FormCutDetail::select('group_roll', 'group_stocker')->where('form_cut_id', $validatedRequest['id'])->where('no_form_cut_input', $validatedRequest['no_form_cut_input'])->whereRaw('(form_cut_input_detail.status = "complete" || form_cut_input_detail.status = "need extension" || form_cut_input_detail.status = "extension complete")')->orderBy('id', 'desc')->first();
        $groupStocker = $beforeData ? ($beforeData->group_roll  == $validatedRequest['current_group'] ? $beforeData->group_stocker : $beforeData->group_stocker + 1) : 1;
        $itemQty = ($validatedRequest["current_unit"] != "KGM" ? floatval($validatedRequest['current_qty']) : floatval($validatedRequest['current_qty_real']));
        $itemUnit = ($validatedRequest["current_unit"] != "KGM" ? "METER" : $validatedRequest['current_unit']);

        $storeTimeRecordSummary = FormCutDetail::
            updateOrCreate(
                ['form_cut_input_detail.form_cut_id' => $validatedRequest['id'], 'form_cut_input_detail.no_form_cut_input' => $validatedRequest['no_form_cut_input'], 'form_cut_input_detail.status' => 'extension'],
                [
                    "id_roll" => $validatedRequest['current_id_roll'],
                    "id_item" => $validatedRequest['current_id_item'],
                    "color_act" => $validatedRequest['color_act'],
                    "detail_item" => $validatedRequest['detail_item'],
                    "group_roll" => $validatedRequest['current_group'],
                    "lot" => $request['current_lot'],
                    "roll" => $validatedRequest['current_roll'],
                    "roll_buyer" => $validatedRequest['current_roll_buyer'],
                    "qty" => $itemQty,
                    "unit" => $itemUnit,
                    "sisa_gelaran" => $validatedRequest['current_sisa_gelaran'],
                    "sambungan" => $validatedRequest['current_sambungan'],
                    "sambungan_roll" => $request->current_total_sambungan_roll ? $request->current_total_sambungan_roll : 0,
                    "est_amparan" => $validatedRequest['current_est_amparan'],
                    "lembar_gelaran" => $validatedRequest['current_lembar_gelaran'],
                    "average_time" => $validatedRequest['current_average_time'],
                    "kepala_kain" => $validatedRequest['current_kepala_kain'],
                    "sisa_tidak_bisa" => $validatedRequest['current_sisa_tidak_bisa'],
                    "reject" => $validatedRequest['current_reject'],
                    "sisa_kain" => ($validatedRequest['current_sisa_kain'] ? $validatedRequest['current_sisa_kain'] : 0),
                    "pemakaian_lembar" => $validatedRequest['current_pemakaian_lembar'],
                    "total_pemakaian_roll" => $validatedRequest['current_total_pemakaian_roll'],
                    "short_roll" => $validatedRequest['current_short_roll'],
                    "piping" => $validatedRequest['current_piping'],
                    "status" => "extension complete",
                    "group_stocker" => $groupStocker,
                    "metode" => $request->metode ? $request->metode : "scan",
                    "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                ]
            );

        if ($storeTimeRecordSummary) {
            FormCut::where("id", $validatedRequest['id'])->where("no_form", $validatedRequest["no_form_cut_input"])->update([
                "meja_id" => (Auth::user()->type != "admin" ? Auth::user()->id : $request->meja_id),
                "total_lembar" => DB::raw('total_lembar + '.$validatedRequest['current_lembar_gelaran']),
            ]);

            $sambunganRoll = $request['sambungan_roll'] ? array_filter($request['sambungan_roll'], function ($var) {
                return ($var > 0);
            }) : [];

            if ($sambunganRoll && count($sambunganRoll) > 0) {
                for ($i = 0; $i < count($sambunganRoll); $i++) {
                    if ($sambunganRoll[$i] > 0) {
                        $storeSambungan = FormCutDetailSambungan::updateOrCreate(
                            ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "sambungan_ke" => $i+1],
                            [
                                "sambungan_roll" => $sambunganRoll[$i],
                            ]
                        );
                    }
                }
            }

            $itemRemain = $itemQty - floatval($validatedRequest['current_total_pemakaian_roll']);
            // $itemRemain = $validatedRequest['current_sisa_kain'];

            ScannedItem::updateOrCreate(
                ["id_roll" => $validatedRequest['current_id_roll']],
                [
                    "id_item" => $validatedRequest['current_id_item'],
                    "color" => $validatedRequest['color_act'],
                    "detail_item" => $validatedRequest['detail_item'],
                    "lot" => $request['current_lot'],
                    "roll" => $validatedRequest['current_roll'],
                    "roll_buyer" => $validatedRequest['current_roll_buyer'],
                    "qty" => $itemRemain,
                    "qty_pakai" => DB::raw("COALESCE(qty_pakai, 0) + ".$validatedRequest['current_total_pemakaian_roll']),
                    "unit" => $itemUnit,
                    "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                ]
            );

            $now = Carbon::now();

            if ($lap > 0) {
                $storeTimeRecordLap = FormCutDetailLap::updateOrCreate(
                    ["form_cut_input_detail_id" => $storeTimeRecordSummary->id, "lembar_gelaran_ke" => $lap],
                    [
                        "waktu" => $request["time_record"][$lap]
                    ]
                );

                if ($storeTimeRecordLap) {
                    $storeTimeRecordSummaryNext = FormCutDetail::create([
                        "form_cut_id" => $validatedRequest['id'],
                        "no_form_cut_input" => $validatedRequest['no_form_cut_input'],
                        "id_roll" => $validatedRequest['current_id_roll'],
                        "id_item" => $validatedRequest['current_id_item'],
                        "color_act" => $validatedRequest['color_act'],
                        "detail_item" => $validatedRequest['detail_item'],
                        "group_roll" => $validatedRequest['current_group'],
                        "lot" => $request['current_lot'],
                        "roll" => $validatedRequest['current_roll'],
                        "roll_buyer" => $validatedRequest['current_roll_buyer'],
                        "qty" => $itemRemain,
                        "unit" => $itemUnit,
                        "sambungan" => 0,
                        "status" => "not complete",
                        "metode" => $request->metode ? $request->metode : null,
                        "berat_amparan" => $itemUnit == 'KGM' ? ($request['current_berat_amparan'] ? $request['current_berat_amparan'] : 0) : 0,
                    ]);

                    return array(
                        "status" => 200,
                        "message" => "alright",
                        "additional" => [
                            FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->first(),
                            FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummaryNext->id)->first(),
                        ],
                    );
                }
            }

            return array(
                "status" => 200,
                "message" => "alright",
                "additional" => [
                    FormCutDetail::selectRaw("form_cut_input_detail.*, scanned_item.qty_in qty_awal")->leftJoin("scanned_item", "scanned_item.id_roll", "=", "form_cut_input_detail.id_roll")->where('form_cut_input_detail.id', $storeTimeRecordSummary->id)->first()
                ],
            );
        }

        return array(
            "status" => 400,
            "message" => "nothing really matter anymore",
            "additional" => [],
        );
    }

    public function checkSpreadingForm($id = 0, $noForm = 0, $mejaId = 0)
    {
        $formCutDetailData = FormCutDetail::selectRaw('
                form_cut_input_detail.*,
                scanned_item.qty_in
            ')->
            leftJoin('scanned_item', 'scanned_item.id_roll', '=', 'form_cut_input_detail.id_roll')->
            leftJoin('form_cut_input', 'form_cut_input.id', '=', 'form_cut_input_detail.form_cut_id')->
            where('form_cut_id', $id)->
            where('no_form_cut_input', $noForm)->
            where('meja_id', $mejaId)->
            orderBy('form_cut_input_detail.id', 'desc')->
            first();

        $formCutDetailCount = $formCutDetailData ? $formCutDetailData->count() : 0;

        if ($formCutDetailCount > 0) {
            if ($formCutDetailData->status == 'extension') {
                $thisFormCutDetail = FormCutDetail::select("sisa_gelaran", "unit")->where('id', $formCutDetailData->id_sambungan)->first();

                return array(
                    "count" => $formCutDetailCount,
                    "data" => $formCutDetailData,
                    "sisaGelaran" => $thisFormCutDetail->sisa_gelaran,
                    "unitSisaGelaran" => $thisFormCutDetail->unit,
                );
            } else if ($formCutDetailData->status == 'not complete') {

                return array(
                    "count" => $formCutDetailCount,
                    "data" => $formCutDetailData,
                    "sisaGelaran" => 0,
                    "unitSisaGelaran" => null,
                );
            }
        }

        return array(
            "count" => null,
            "data" => null
        );
    }

    public function checkTimeRecordLap($detailId = 0)
    {
        $formCutDetailLapData = FormCutDetailLap::where('form_cut_input_detail_id', $detailId)->get();

        return array(
            "count" => $formCutDetailLapData->count(),
            "data" => $formCutDetailLapData,
        );
    }

    public function storeLostTime(Request $request, $id = 0)
    {
        $now = Carbon::now();

        $current = $request["current_lost_time"];

        $storeTimeRecordLap = FormCutLostTime::updateOrCreate(
            ["form_cut_input_id" => $id, "lost_time_ke" => $request["current_lost_time"]],
            [
                "lost_time_ke" => $request["current_lost_time"],
                "waktu" => $request["lost_time"][$current],
            ]
        );
    }

    public function checkLostTime($id = 0)
    {
        $formCutLostTimeData = FormCutLostTime::where('form_cut_input_id', $id)->get();

        return array(
            "count" => $formCutLostTimeData->count(),
            "data" => $formCutLostTimeData,
        );
    }

    public function finishProcess($id = 0, Request $request)
    {
        $formCutData = FormCut::selectRaw("form_cut_input.*, form_cut_input.meja_id as meja_id")->where("id", $id)->first();

        $formCutSimilarCount = FormCut::leftJoin("marker_input", "marker_input.id", "=", "form_cut_input.marker_id")->
            where("marker_input.act_costing_ws", $formCutData->marker->act_costing_ws)->
            where("marker_input.color", $formCutData->marker->color)->
            where("marker_input.panel", $formCutData->marker->panel)->
            where("form_cut_input.status", "SELESAI PENGERJAAN")->
            count();

        $updateFormCut = FormCut::where("id", $id)->update([
            "meja_id" => $request->meja_id,
            "status" => "SELESAI PENGERJAAN",
            "waktu_selesai" => $request->finishTime,
            "cons_act" => $request->consAct,
            "unit_cons_act" => $request->unitConsAct,
            "cons_act_nosr" => $request->consActNoSr,
            "unit_cons_act_nosr" => $request->unitConsActNoSr,
            "total_lembar" => $request->totalLembar,
            "no_cut" => $formCutSimilarCount + 1,
            "cons_ws_uprate" => $request->consWsUprate,
            "cons_marker_uprate" => $request->consMarkerUprate,
            "cons_ws_uprate_nosr" => $request->consWsUprateNoSr,
            "cons_marker_uprate_nosr" => $request->consMarkerUprateNoSr,
            "operator" => $request->operator,
        ]);

        $notCompletedDetails = FormCutDetail::where("form_cut_id", $formCutData->id)->where("no_form_cut_input", $formCutData->no_form)->whereRaw("(status = 'not complete' OR status = 'extension')")->get();
        if ($notCompletedDetails->count() > 0) {
            foreach ($notCompletedDetails as $notCompletedDetail) {
                DB::table("form_cut_input_detail_delete")->insert([
                    "form_cut_id" => $notCompletedDetail['form_cut_id'],
                    "no_form_cut_input" => $notCompletedDetail['no_form_cut_input'],
                    "id_roll" => $notCompletedDetail['id_roll'],
                    "id_item" => $notCompletedDetail['id_item'],
                    "color_act" => $notCompletedDetail['color_act'],
                    "detail_item" => $notCompletedDetail['detail_item'],
                    "group_roll" => $notCompletedDetail['group_roll'],
                    "lot" => $notCompletedDetail['lot'],
                    "roll" => $notCompletedDetail['roll'],
                    "roll_buyer" => $notCompletedDetail['roll_buyer'],
                    "qty" => $notCompletedDetail['qty'],
                    "unit" => $notCompletedDetail['unit'],
                    "sisa_gelaran" => $notCompletedDetail['sisa_gelaran'],
                    "sambungan_roll" => $notCompletedDetail['sambungan_roll'],
                    "sambungan" => $notCompletedDetail['sambungan'],
                    "est_amparan" => $notCompletedDetail['est_amparan'],
                    "lembar_gelaran" => $notCompletedDetail['lembar_gelaran'],
                    "average_time" => $notCompletedDetail['average_time'],
                    "kepala_kain" => $notCompletedDetail['kepala_kain'],
                    "sisa_tidak_bisa" => $notCompletedDetail['sisa_tidak_bisa'],
                    "reject" => $notCompletedDetail['reject'],
                    "sisa_kain" => ($notCompletedDetail['sisa_kain'] ? $notCompletedDetail['sisa_kain'] : 0),
                    "pemakaian_lembar" => $notCompletedDetail['pemakaian_lembar'],
                    "short_roll" => $notCompletedDetail['short_roll'],
                    "piping" => $notCompletedDetail['piping'],
                    "total_pemakaian_roll" => $notCompletedDetail['total_pemakaian_roll'],
                    "status" => $notCompletedDetail['status'],
                    "metode" => $notCompletedDetail['metode'],
                    "group_stocker" => $notCompletedDetail['group_stocker'],
                    "created_at" => $notCompletedDetail['created_at'],
                    "updated_at" => $notCompletedDetail['updated_at'],
                    "deleted_by" => Auth::user()->username,
                    "deleted_at" => Carbon::now(),
                ]);

                FormCutDetailLap::where("form_cut_input_detail_id", $notCompletedDetail->id)->delete();
            }
        }

        FormCutDetail::where("form_cut_id", $formCutData->id)->where("no_form_cut_input", $formCutData->no_form)->whereRaw("(status = 'not complete' OR status = 'extension')")->delete();

        // store to part form
        $partData = Part::select('part.id')->
            where("act_costing_id", $formCutData->marker->act_costing_id)->
            where("act_costing_ws", $formCutData->marker->act_costing_ws)->
            where("panel", $formCutData->marker->panel)->
            // where("style", $formCutData->marker->style)->
            first();

        if ($partData) {
            $lastPartForm = PartForm::select("kode")->orderBy("kode", "desc")->first();
            $urutanPartForm = $lastPartForm ? intval(substr($lastPartForm->kode, -5)) + 1 : 1;
            $kodePartForm = "PFM" . sprintf('%05s', $urutanPartForm);

            $addToPartForm = PartForm::create([
                "kode" => $kodePartForm,
                "part_id" => $partData->id,
                "form_id" => $formCutData->id,
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
            ]);
        }

        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutData->alokasiMeja->username);

        return $updateFormCut;
    }
}
