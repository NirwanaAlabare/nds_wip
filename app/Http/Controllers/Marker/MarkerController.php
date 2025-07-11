<?php

namespace App\Http\Controllers\Marker;

use App\Http\Controllers\Controller;
use App\Models\Marker;
use App\Models\MarkerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use PDF;

class MarkerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $markersQuery = Marker::selectRaw("
                id,
                tgl_cutting,
                DATE_FORMAT(tgl_cutting, '%d-%m-%Y') tgl_cut_fix,
                kode,
                act_costing_ws,
                style,
                color,
                panel,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker)) panjang_marker,
                CONCAT(comma_marker, ' ', UPPER(unit_comma_marker)) comma_marker,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
                CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker,
                COALESCE(gramasi, 0) gramasi,
                gelar_qty,
                gelar_qty_balance,
                po_marker,
                urutan_marker,
                tipe_marker,
                COALESCE(b.total_form, 0) total_form,
                COALESCE(b.total_lembar, 0) total_lembar,
                CONCAT(COALESCE(b.total_lembar, 0), '/', gelar_qty) ply_progress,
                COALESCE(notes, '-') notes,
                cancel
            ")->
            leftJoin(
                DB::raw("
                    (
                        select
                            id_marker,
                            count(id_marker) total_form,
                            sum(total_lembar) total_lembar
                        from
                            form_cut_input
                        group by
                            id_marker
                    ) b"
                ),
                "marker_input.kode",
                "=",
                "b.id_marker"
            );

            return DataTables::eloquent($markersQuery)->filter(function ($query) {
                    $tglAwal = request('tgl_awal');
                    $tglAkhir = request('tgl_akhir');

                    if ($tglAwal) {
                        $query->whereRaw("tgl_cutting >= '" . $tglAwal . "'");
                    }

                    if ($tglAkhir) {
                        $query->whereRaw("tgl_cutting <= '" . $tglAkhir . "'");
                    }
                }, true)->filterColumn('kode', function ($query, $keyword) {
                    $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('act_costing_ws', function ($query, $keyword) {
                    $query->whereRaw("LOWER(act_costing_ws) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('color', function ($query, $keyword) {
                    $query->whereRaw("LOWER(color) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('panel', function ($query, $keyword) {
                    $query->whereRaw("LOWER(panel) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('po_marker', function ($query, $keyword) {
                    $query->whereRaw("LOWER(po_marker) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('cancel', 'asc')->orderBy('updated_at', 'desc');
                })->toJson();
        }

        return view('marker.marker.marker', ["subPageGroup" => "proses-marker", "subPage" => "marker", "page" => "dashboard-marker"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            $markerDetail = MarkerDetail::selectRaw("
                    marker_input_detail.so_det_id,
                    marker_input.panel,
                    SUM(marker_input_detail.cut_qty) total_cut_qty
                ")->
                leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')->
                where('marker_input.cancel', 'N')->
                where('marker_input.act_costing_id', $request->act_costing_id)->
                where('marker_input.color', $request->color);

                if ($request->panel) {
                    $markerDetail->where('marker_input.panel', $request->panel);
                }

            $markerDetailData = $markerDetail->groupBy("marker_input_detail.so_det_id", "marker_input.panel")->get();

            return $markerDetailData;
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('marker.marker.create-marker', ['orders' => $orders, 'page' => 'dashboard-marker', "subPageGroup" => "proses-marker", "subPage" => "marker"]);
    }

    public function getOrderInfo(Request $request)
    {
        $order = DB::connection('mysql_sb')->table('act_costing')->selectRaw('act_costing.id, act_costing.kpno, act_costing.styleno, act_costing.qty order_qty, mastersupplier.supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer')->where('id', $request->act_costing_id)->first();

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
                master_sb_ws.size,
                master_sb_ws.qty order_qty,
                COALESCE(marker_input_detail.ratio, 0) ratio,
                COALESCE(marker_input_detail.cut_qty, 0) cut_qty
            ")->
            where("master_sb_ws.id_act_cost", $request->act_costing_id)->
            where("master_sb_ws.color", $request->color);

        $thisMarkerDetail = MarkerDetail::where("marker_id", $request->marker_id)->count();
        if ($thisMarkerDetail > 0) {
            $sizeQuery->leftJoin('marker_input_detail', function ($join) use ($request) {
                    $join->on('marker_input_detail.so_det_id', '=', 'master_sb_ws.id_so_det');
                    $join->on('marker_input_detail.marker_id', '=', DB::raw($request->marker_id));
                })->
                leftJoin('master_size_new', 'master_size_new.size', '=', 'master_sb_ws.size')->
                leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id');
        } else {
            $sizeQuery->leftJoin('marker_input_detail', 'marker_input_detail.so_det_id', '=', 'master_sb_ws.id_so_det')->
            leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')->
            leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size");
        }

        $sizes = $sizeQuery->groupBy("id_so_det")->orderBy("master_size_new.urutan")->get();

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
                select mp.id as id_panel, nama_panel panel from
                    (select id_panel from bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join masteritem mi on k.id_item = mi.id_gen
                        where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and k.status = 'M'
                        and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and mi.mattype = 'F'
                        group by id_panel
                    ) a
                inner join masterpanel mp on a.id_panel = mp.id
            ");

        $html = "<option value=''>Pilih Panel</option>";

        foreach ($panels as $panel) {
            $html .= " <option value='" . $panel->id_panel . "'>" . $panel->panel . "</option> ";
        }

        return $html;
    }

    public function getNumber(Request $request)
    {
        $number = DB::connection('mysql_sb')->select("
                select k.cons cons_ws,sum(coalesce(sd.qty, 0)) order_qty from bom_jo_item k
                    inner join so_det sd on k.id_so_det = sd.id
                    inner join so on sd.id_so = so.id
                    inner join act_costing ac on so.id_cost = ac.id
                    inner join masteritem mi on k.id_item = mi.id_gen
                    inner join masterpanel mp on k.id_panel = mp.id
                where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and mp.nama_panel ='" . $request->panel . "' and k.status = 'M'
                and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and mi.mattype = 'F'
                group by sd.color, k.id_item, k.unit
                limit 1
            ");

        return json_encode($number ? $number[0] : null);
    }

    public function getCount(Request $request)
    {
        $countMarker = Marker::where('act_costing_id', $request->act_costing_id)->where('color', $request->color)->where('panel', $request->panel)->whereRaw('(cancel != "Y" OR cancel is null)')->count() + 1;

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
        $markerCount = Marker::selectRaw("MAX(kode) latest_kode")->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")->first();
        $markerNumber = intval(substr($markerCount->latest_kode, -5)) + 1;
        $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $markerNumber);
        $totalQty = 0;

        $validatedRequest = $request->validate([
            "tgl_cutting" => "required",
            "ws_id" => "required",
            "ws" => "required",
            "buyer" => "required",
            "style" => "required",
            "cons_ws" => "required|numeric|min:0",
            "color" => "required",
            "panel_id" => "required",
            "panel" => "required",
            "p_marker" => "required|numeric|min:0",
            "p_unit" => "required",
            "comma_marker" => "required|numeric|min:0",
            "comma_unit" => "required",
            "l_marker" => "required|numeric|min:0",
            "l_unit" => "required",
            "gelar_marker_qty" => "required|numeric|gt:0",
            "po" => "required",
            "no_urut_marker" => "required|numeric|gt:0",
            "cons_marker" => "required|numeric|gt:0",
            "gramasi" => "required|numeric|gt:0",
            "tipe_marker" => "required",
            "cons_piping" => "required|numeric|min:0"
        ]);

        foreach ($request["cut_qty"] as $qty) {
            $totalQty += $qty;
        }

        if ($totalQty > 0) {
            $markerStore = Marker::create([
                'tgl_cutting' => $validatedRequest['tgl_cutting'],
                'kode' => $markerCode,
                'act_costing_id' => $validatedRequest['ws_id'],
                'act_costing_ws' => $validatedRequest['ws'],
                'buyer' => $validatedRequest['buyer'],
                'style' => $validatedRequest['style'],
                'cons_ws' => $validatedRequest['cons_ws'],
                'color' => $validatedRequest['color'],
                'panel_id' => $validatedRequest['panel_id'],
                'panel' => $validatedRequest['panel'],
                'panjang_marker' => $validatedRequest['p_marker'],
                'unit_panjang_marker' => $validatedRequest['p_unit'],
                'comma_marker' => $validatedRequest['comma_marker'],
                'unit_comma_marker' => $validatedRequest['comma_unit'],
                'lebar_marker' => $validatedRequest['l_marker'],
                'unit_lebar_marker' => $validatedRequest['l_unit'],
                'gelar_qty' => $validatedRequest['gelar_marker_qty'],
                'gelar_qty_balance' => $validatedRequest['gelar_marker_qty'],
                'po_marker' => $validatedRequest['po'],
                'urutan_marker' => $validatedRequest['no_urut_marker'],
                'cons_marker' => $validatedRequest['cons_marker'],
                'gramasi' => $validatedRequest['gramasi'],
                'tipe_marker' => $validatedRequest['tipe_marker'],
                'notes' => $request['notes'],
                'cons_piping' => $validatedRequest['cons_piping'],
                'cancel' => 'N',
                'created_by' => Auth::user()->id,
                'created_by_username' => Auth::user()->username
            ]);

            $timestamp = Carbon::now();
            $markerId = $markerStore->id;
            $markerDetailData = [];
            for ($i = 0; $i < intval($request['jumlah_so_det']); $i++) {
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

            return array(
                "status" => 200,
                "message" => $markerCode,
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Total Cut Qty Kosong",
            "additional" => [],
        );
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $data_marker = DB::select("
        SELECT a.*,
        a.kode kode_marker,
        DATE_FORMAT(tgl_cutting, '%d-%m-%Y') tgl_cut_fix,
        CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
        CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker_fix,
        b.qty_order
        from marker_input a
        left join (select id_act_cost,sum(qty) qty_order from master_sb_ws group by id_act_cost) b on a.act_costing_id = b.id_act_cost
        where id = '$request->id_c'");

        $data_marker_det = DB::select("
        SELECT master_sb_ws.size, ratio
        from marker_input_detail a
        left join master_sb_ws on master_sb_ws.id_so_det = a.so_det_id
        left join master_size_new b on master_sb_ws.size = b.size
        where marker_id = '$request->id_c' and a.ratio > 0
        order by urutan asc");

        $data_marker_tracking = DB::select("
        select
        a.id form_cut_id,
        no_form,
        a.tipe_form_cut tipe_form,
        DATE_FORMAT(tgl_form_cut, '%d-%m-%Y') tgl_form_cut,
        UPPER(u.name) no_meja,
        DATE_FORMAT(waktu_mulai, '%d-%m-%Y %T') waktu_mulai,
        DATE_FORMAT(waktu_selesai, '%d-%m-%Y %T') waktu_selesai,
        a.status,
        a.tipe_form_cut,
        COALESCE(a.no_cut, '-') no_cut,
        COALESCE(a.total_lembar, 0) total_lembar
        from form_cut_input a
        inner join marker_input b on  a.id_marker = b.kode
        left join users u on a.no_meja = u.id
        where b.id = '$request->id_c'
        ORDER BY
        FIELD(a.status, 'SELESAI PENGERJAAN', 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING'),
        a.no_cut asc,
        a.no_form desc,
        a.updated_at desc");

        foreach ($data_marker as $datanomarker) {

            $html_table = "";

            foreach ($data_marker_det as $item) :
                $html_table .= "
                    <tr>
                        <td align='center' valign='center'>$item->size</td>
                        <td align='center' valign='center'>$item->ratio</td>
                    </tr>
                ";
            endforeach;


            $html_tracking = "";

            foreach ($data_marker_tracking as $track) :
                $bgColor = null;
                $textColor = null;

                if ($track->tipe_form_cut == "MANUAL") {
                    $bgColor = '#e7dcf7';
                } else if ($track->tipe_form_cut == "PILOT") {
                    $bgColor = '#c5e0fa';
                }

                $actButton = '';

                switch ($track->status) {
                    case "SPREADING":
                        if ($track->tipe_form_cut == 'MANUAL') {
                            $actButton = "
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-success btn-sm' href='".route('process-manual-form-cut')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                        <i class='fa fa-plus'></i>
                                    </a>
                                </div>
                            ";
                        } else if ($track->tipe_form_cut == 'PILOT') {
                            $actButton = "
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-success btn-sm' href='".route('process-pilot-form-cut')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                        <i class='fa fa-plus'></i>
                                    </a>
                                </div>
                            ";
                        } else {
                            $actButton = "
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-success btn-sm' href='".route('process-manual-form-cut')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                        <i class='fa fa-plus'></i>
                                    </a>
                                </div>
                            ";
                        }

                        break;
                    case "PENGERJAAN PILOT MARKER":
                    case "PENGERJAAN PILOT DETAIL":
                    case "PENGERJAAN MARKER":
                    case "PENGERJAAN FORM CUTTING":
                    case "PENGERJAAN FORM CUTTING DETAIL":
                    case "PENGERJAAN FORM CUTTING SPREAD":
                        $textColor = '#2243d6';

                        if ($track->tipe_form_cut == 'MANUAL') {
                            $actButton = "
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' href='".route('process-manual-form-cut')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                        <i class='fa fa-search-plus'></i>
                                    </a>
                                </div>
                            ";
                        } else if ($track->tipe_form_cut == 'PILOT') {
                            $actButton = "
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' href='".route('process-pilot-form-cut')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                        <i class='fa fa-search-plus'></i>
                                    </a>
                                </div>
                            ";
                        } else {
                            $actButton = "
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' href='".route('process-manual-form-cut')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                        <i class='fa fa-search-plus'></i>
                                    </a>
                                </div>
                            ";
                        }

                        break;
                    case "SELESAI PENGERJAAN":
                        $textColor = '#087521';

                        $actButton = "
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' href='".route('show-stocker')."/".$track->form_cut_id."' data-bs-toggle='tooltip' target='_blank'>
                                    <i class='fa fa-search-plus'></i>
                                </a>
                            </div>
                        ";

                        break;
                }

                $html_tracking .= "
                    <tr style='".($bgColor ? "background-color:".$bgColor.";border:0.15px solid #d0d0d0;" : "")." ".($textColor ? "color:".$textColor.";" : "")."'>
                        <td class='text-nowrap' style='font-weight: 600;'>
                            ".$actButton."
                        </td>
                        <td class='text-nowrap' style='font-weight: 600;'>$track->tgl_form_cut</td>
                        <td class='text-nowrap' style='font-weight: 600;'>$track->no_form</td>
                        <td class='text-nowrap' style='font-weight: 600;'>" . ($track->no_meja ? $track->no_meja : '-') . "</td>
                        <td class='text-nowrap' style='font-weight: 600;'>" . ($track->waktu_mulai ? $track->waktu_mulai : '-') . "</td>
                        <td class='text-nowrap' style='font-weight: 600;'>" . ($track->waktu_selesai ? $track->waktu_selesai : '-') . "</td>
                        <td class='text-nowrap' style='font-weight: 600;'>$track->status</td>
                        <td class='text-nowrap' style='font-weight: 600;'>$track->total_lembar</td>
                        <td class='text-nowrap' style='font-weight: 600;'>$track->no_cut</td>
                    </tr>
                ";
            endforeach;

            $html = "
                <div class='row'>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Kode</label>
                            <input type='text' class='form-control' id='txtkode_marker' name='txtkode_marker' value = '" . $datanomarker->kode_marker . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Tanggal</label>
                            <input type='text' class='form-control' id='txttgl_cutting' name='txttgl_cutting' value = '" . $datanomarker->tgl_cut_fix . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-4'>
                        <div class='form-group'>
                            <label class='form-label'>No. WS</label>
                            <input type='text' class='form-control' id='txtno_ws' name='txtno_ws'  value = '" . $datanomarker->act_costing_ws . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-4'>
                        <div class='form-group'>
                            <label class='form-label'>Color</label>
                            <input type='text' class='form-control' id='txtcol' name='txtcol'  value = '" . $datanomarker->color . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-4'>
                        <div class='form-group'>
                            <label class='form-label'>Panel</label>
                            <input type='text' class='form-control' id='txtpanel' name='txtpanel'  value = '" . $datanomarker->panel . "' readonly>
                        </div>
                    </div>
                </div>


                <div class='row'>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Buyer</label>
                            <input type='text' class='form-control' id='txtbuyer' name='txtbuyer' value = '" . $datanomarker->buyer . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-3'>
                        <div class='form-group'>
                            <label class='form-label'>Panjang Marker</label>
                            <input type='text' class='form-control' id='txtp_marker' name='txtp_marker'  value = '" . $datanomarker->panjang_marker_fix . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-3'>
                        <div class='form-group'>
                            <label class='form-label'>Lebar Marker</label>
                            <input type='text' class='form-control' id='txtl_marker' name='txtl_marker'  value = '" . $datanomarker->lebar_marker_fix . "' readonly>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Style</label>
                            <input type='text' class='form-control' id='txtstyle' name='txtstyle' value = '" . $datanomarker->style . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Qty Order</label>
                            <input type='text' class='form-control' id='txtqty_order' name='txtqty_order' value = '" . $datanomarker->qty_order . "' readonly>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-sm-3'>
                        <div class='form-group'>
                            <label class='form-label'>Cons WS</label>
                            <input type='text' class='form-control' id='txtcons_ws' name='txtcons_ws' value = '" . $datanomarker->cons_ws . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-3'>
                        <div class='form-group'>
                            <label class='form-label'>Cons Piping</label>
                            <input type='text' class='form-control' id='txtcons_piping' name='txtcons_piping' value = '" . $datanomarker->cons_piping . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-3'>
                        <div class='form-group'>
                            <label class='form-label'>Cons Marker</label>
                            <input type='text' class='form-control' id='txtcons_marker' name='txtcons_marker'  value = '" . $datanomarker->cons_marker . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-3'>
                        <div class='form-group'>
                            <label class='form-label'>Qty Gelar Marker</label>
                            <input type='text' class='form-control' id='txtgelar' name='txtgelar'  value = '" . $datanomarker->gelar_qty . "' readonly>
                        </div>
                    </div>
                </div>

                <div class='row'>
                    <div class='col-sm-4'>
                        <div class='form-group'>
                            <label class='form-label'>PO</label>
                            <input type='text' class='form-control' id='txtpo' name='txtpo' value = '" . $datanomarker->po_marker . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-4'>
                        <div class='form-group'>
                            <label class='form-label'>Gramasi</label>
                            <input type='text' class='form-control' id='txturutan' name='txturutan'  value = '" . $datanomarker->gramasi . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-4'>
                        <div class='form-group'>
                            <label class='form-label'>Urutan</label>
                            <input type='text' class='form-control' id='txturutan' name='txturutan'  value='" . $datanomarker->urutan_marker . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Tipe Marker</label>
                            <input type='text' class='form-control' id='tipemarker' name='tipemarker' value='" . strtoupper(str_replace(" marker", "", $datanomarker->tipe_marker)) . "' readonly>
                        </div>
                    </div>
                    <div class='col-sm-6'>
                        <div class='form-group'>
                            <label class='form-label'>Catatan</label>
                            <textarea class='form-control' id='txtarea' name='txtarea' readonly>" . ($datanomarker->notes ? $datanomarker->notes : '-') . "</textarea>
                        </div>
                    </div>
                </div>
            </div>


            <div class='row'>
                <div class='col-sm-12'>
                    <div class='card card-primary collapsed-card'>
                        <div class='card-header'>
                            <h1 class='card-title'><i class='fa-solid fa-expand fa-sm'></i> Detail Size</h1>
                            <div class='card-tools'>
                                <button type='button' class='btn btn-tool' data-card-widget='collapse'><i class='fas fa-plus'></i></button>
                            </div>
                        </div>
                        <div class='card-body' style='display: none;'>
                            <div class='table-responsive'>
                                <table class='table table-bordered table w-100' id='detail-marker-ratio'>
                                    <thead>
                                        <tr>
                                            <th class='text-center'>Size</th>
                                            <th class='text-center'>Ratio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        $html_table
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class='row'>
                <div class='col-md-12'>
                    <div class='card card-primary collapsed-card'>
                        <div class='card-header'>
                            <h1 class='card-title'><i class='fa-solid fa-copy fa-sm'></i> Status Form</h1>
                            <div class='card-tools'>
                                <button type='button' class='btn btn-tool' data-card-widget='collapse'>
                                    <i class='fas fa-plus'></i>
                                </button>
                            </div>
                        </div>
                        <div class='card-body' style='display: none;'>
                            <div class='table-responsive'>
                                <table class='table table-bordered table w-100' id='detail-marker-form'>
                                    <thead>
                                        <tr>
                                            <th>Act</th>
                                            <th>Tanggal Form</th>
                                            <th>No. Form</th>
                                            <th>No. Meja</th>
                                            <th>Waktu Mulai</th>
                                            <th>Waktu Selesai</th>
                                            <th>Status</th>
                                            <th>Total Lembar</th>
                                            <th>No. Cut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        $html_tracking
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ";
        }
        return $html;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function edit(Marker $marker, $id)
    {
        $marker = Marker::where('id', $id)->first();

        return view('marker.marker.edit-marker', ['page' => 'dashboard-marker', "subPageGroup" => "proses-marker", "subPage" => "marker", 'marker' => $marker]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function update(Marker $marker, Request $request, $id)
    {
        $validatedRequest = $request->validate([
            "tgl_cutting" => "required",
            "ws_id" => "required",
            "ws" => "required",
            "buyer" => "required",
            "style" => "required",
            "cons_ws" => "required|numeric|min:0",
            "color" => "required",
            "panel_id" => "required",
            "panel" => "required",
            "p_marker" => "required|numeric|min:0",
            "p_unit" => "required",
            "comma_marker" => "required|numeric|min:0",
            "comma_unit" => "required",
            "l_marker" => "required|numeric|min:0",
            "l_unit" => "required",
            "gelar_marker_qty" => "required|numeric|gt:0",
            "po" => "required",
            "no_urut_marker" => "required|numeric|gt:0",
            "cons_marker" => "required|numeric|gt:0",
            "gramasi" => "required|numeric|gt:0",
            "tipe_marker" => "required",
            "cons_piping" => "nullable"
        ]);
        $totalQty = 0;

        foreach ($request["cut_qty"] as $qty) {
            $totalQty += $qty;
        }

        $updateMarkerPanel = false;
        if ($totalQty > 0) {

            // Marker Panel Additional Validation
            $markerPartForm = Marker::select("marker_input.id", "form_cut_input.id", "form_cut_input.no_form")->
                join("form_cut_input", "form_cut_input.id_marker", "=", "marker_input.kode")->
                join("part_form", "part_form.form_id", "=", "form_cut_input.id")->
                where("marker_input.id", $id)->
                get();

            $updateMarkerArr = [];
            if ($markerPartForm->count() < 1) {
                $updateMarkerArr = [
                    'tgl_cutting' => $validatedRequest['tgl_cutting'],
                    'act_costing_id' => $validatedRequest['ws_id'],
                    'act_costing_ws' => $validatedRequest['ws'],
                    'buyer' => $validatedRequest['buyer'],
                    'style' => $validatedRequest['style'],
                    'cons_ws' => $validatedRequest['cons_ws'],
                    'color' => $validatedRequest['color'],
                    'panel_id' => $validatedRequest['panel_id'],
                    'panel' => $validatedRequest['panel'],
                    'panjang_marker' => $validatedRequest['p_marker'],
                    'unit_panjang_marker' => $validatedRequest['p_unit'],
                    'comma_marker' => $validatedRequest['comma_marker'],
                    'unit_comma_marker' => $validatedRequest['comma_unit'],
                    'lebar_marker' => $validatedRequest['l_marker'],
                    'unit_lebar_marker' => $validatedRequest['l_unit'],
                    'gelar_qty' => $validatedRequest['gelar_marker_qty'],
                    'gelar_qty_balance' => $validatedRequest['gelar_marker_qty'],
                    'po_marker' => $validatedRequest['po'],
                    'urutan_marker' => $validatedRequest['no_urut_marker'],
                    'cons_marker' => $validatedRequest['cons_marker'],
                    'gramasi' => $validatedRequest['gramasi'],
                    'tipe_marker' => $validatedRequest['tipe_marker'],
                    'notes' => $request['notes'],
                    'cons_piping' => $validatedRequest['cons_piping'],
                    'cancel' => 'N',
                ];

                $updateMarkerPanel = true;
            } else {
                $updateMarkerArr = [
                    'tgl_cutting' => $validatedRequest['tgl_cutting'],
                    'act_costing_id' => $validatedRequest['ws_id'],
                    'act_costing_ws' => $validatedRequest['ws'],
                    'buyer' => $validatedRequest['buyer'],
                    'style' => $validatedRequest['style'],
                    'cons_ws' => $validatedRequest['cons_ws'],
                    'color' => $validatedRequest['color'],
                    'panjang_marker' => $validatedRequest['p_marker'],
                    'unit_panjang_marker' => $validatedRequest['p_unit'],
                    'comma_marker' => $validatedRequest['comma_marker'],
                    'unit_comma_marker' => $validatedRequest['comma_unit'],
                    'lebar_marker' => $validatedRequest['l_marker'],
                    'unit_lebar_marker' => $validatedRequest['l_unit'],
                    'gelar_qty' => $validatedRequest['gelar_marker_qty'],
                    'gelar_qty_balance' => $validatedRequest['gelar_marker_qty'],
                    'po_marker' => $validatedRequest['po'],
                    'urutan_marker' => $validatedRequest['no_urut_marker'],
                    'cons_marker' => $validatedRequest['cons_marker'],
                    'gramasi' => $validatedRequest['gramasi'],
                    'tipe_marker' => $validatedRequest['tipe_marker'],
                    'notes' => $request['notes'],
                    'cons_piping' => $validatedRequest['cons_piping'],
                    'cancel' => 'N',
                ];

                $updateMarkerPanel = false;
            }
            if ($updateMarkerArr) {
                $markerUpdate = Marker::where('id', $id)->update($updateMarkerArr);
            }

            // Marker Ratio
            $timestamp = Carbon::now();
            $markerDetailData = [];
            for ($i = 0; $i < intval($request['jumlah_so_det']); $i++) {
                if (MarkerDetail::where('marker_id', $id)->where('so_det_id', $request["so_det_id"][$i])->first()) {
                    MarkerDetail::where('marker_id', $id)->where('so_det_id', $request["so_det_id"][$i])->update([
                        "size" => $request["size"][$i],
                        "ratio" => $request["ratio"][$i],
                        "cut_qty" => $request["cut_qty"][$i],
                        "cancel" => 'N',
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp,
                    ]);
                } else {
                    MarkerDetail::create([
                        "marker_id" => $id,
                        'so_det_id' => $request["so_det_id"][$i],
                        "size" => $request["size"][$i],
                        "ratio" => $request["ratio"][$i],
                        "cut_qty" => $request["cut_qty"][$i],
                        "cancel" => 'N',
                        "created_at" => $timestamp,
                        "updated_at" => $timestamp,
                    ]);
                }
            }

            return array(
                "status" => 200,
                "message" => "Ratio Marker ".$id." berhasil di ubah <br> ".($updateMarkerPanel ? "" : "Panel Marker tidak diubah <br> (Form Marker sudah memiliki part : <b>'".$markerPartForm->implode("no_form", ", ")."'</b>)"),
                "redirect" => route('marker'),
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Total Cut Qty Kosong",
            "redirect" => route('marker'),
            "additional" => [],
        );
    }

    public function show_gramasi(Request $request)
    {
        $data_gramasi = DB::select("
        select marker_input.kode,marker_input.id,gramasi,tipe_marker,status_marker, count(form_cut_input.id) jumlah_form from marker_input
        left join form_cut_input on form_cut_input.id_marker = marker_input.kode
        where marker_input.id = '$request->id_c'
        group by marker_input.id");
        return json_encode($data_gramasi[0]);
    }

    public function update_status(Request $request)
    {
        $thisMarker = Marker::find($request->id_c);

        if ($thisMarker) {
            if ($thisMarker->cancel == "Y") {
                $afterMarker = Marker::where('act_costing_id', $thisMarker->act_costing_id)->
                    where('style', $thisMarker->style)->
                    where('color', $thisMarker->color)->
                    where('panel', $thisMarker->panel)->
                    whereRaw('(cancel != "Y" OR cancel IS NULL)')->
                    update([
                        'urutan_marker' => DB::raw('(urutan_marker + 1)')
                    ]);
            } else {
                $afterMarker = Marker::where('act_costing_id', $thisMarker->act_costing_id)->
                    where('style', $thisMarker->style)->
                    where('color', $thisMarker->color)->
                    where('panel', $thisMarker->panel)->
                    whereRaw('(cancel != "Y" OR cancel IS NULL)')->
                    update([
                        'urutan_marker' => DB::raw('(urutan_marker - 1)')
                    ]);
            }
        }

        $update_data = DB::update("
            update marker_input set cancel = case when cancel = 'Y' then'N' else 'Y' end
            where id = '$request->id_c'
        ");
    }

    public function update_marker(Request $request)
    {
        $updateStatus = "";
        if ($request->pilot_status) {
            $updateStatus .= ", status_marker = '" . $request->pilot_status . "'";

            if ($request->pilot_status == "active") {
                $updateStatus .= ", tipe_marker = 'bulk marker', notes = 'Pilot to Bulk'";
            }
        }

        $update_gramasi = DB::update("
        update marker_input set
        gramasi = '$request->txt_gramasi'
        " . $updateStatus . "
        where id = '$request->id_c'");

        if ($update_gramasi) {
            $kode = Marker::where('id', $request->id_c)->first();
            return array(
                'status' => 200,
                'message' => 'Data form "' . $kode->kode . '" berhasil diubah',
                'redirect' => '',
                'table' => 'datatable',
                'additional' => [],
            );
        }
        return array(
            'status' => 400,
            'message' => 'Data produksi gagal diubah',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Marker $marker)
    {
        //
    }

    public function printMarker($kodeMarker)
    {
        $kodeMarker = str_replace("_", "/", $kodeMarker);

        $markerData = Marker::where('kode', $kodeMarker)->first();

        $actCostingData = DB::connection('mysql_sb')->table('act_costing')->selectRaw('
                SUM(so_det.qty) order_qty,
                so_det.unit unit_qty
            ')->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')->where('act_costing.id', $markerData->act_costing_id)->where('so_det.color', $markerData->color)->groupBy('act_costing.id')->first();

        $soDetData = DB::connection('mysql_sb')->table('so_det')->selectRaw('
                so_det.id,
                so_det.size as size,
                so_det.qty as qty
            ')->leftJoin('so', 'so.id', '=', 'so_det.id_so')->leftJoin('act_costing', 'so.id_cost', '=', 'act_costing.id')->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')->where('act_costing.id', $markerData->act_costing_id)->where('so_det.color', $markerData->color)->where('so_det.qty', '>', '0')->groupBy('so_det.size')->orderBy('master_size_new.urutan')->get();

        $orderQty = DB::connection('mysql_sb')->select("
            select k.cons cons_ws, sum(sd.qty) order_qty from bom_jo_item k
                inner join so_det sd on k.id_so_det = sd.id
                inner join so on sd.id_so = so.id
                inner join act_costing ac on so.id_cost = ac.id
                inner join masteritem mi on k.id_item = mi.id_gen
                inner join masterpanel mp on k.id_panel = mp.id
            where
                ac.id = '" . $markerData->act_costing_id . "' and
                sd.color = '" . $markerData->color . "' and
                mp.nama_panel ='" . $markerData->panel . "' and
                k.status = 'M' and
                k.cancel = 'N' and
                sd.cancel = 'N' and
                so.cancel_h = 'N' and
                mi.mattype = 'F' and
                sd.qty > 0
            group by sd.color, k.id_item, k.unit
            limit 1");

        if ($markerData) {
            // generate pdf
            PDF::setOption(['dpi' => 150]);
            $pdf = PDF::loadView('marker.marker.pdf.print-marker', ["markerData" => $markerData, "actCostingData" => $actCostingData, "soDetData" => $soDetData, "orderQty" => $orderQty])->setPaper('a4', 'landscape');

            $fileName = 'stocker-' . str_replace("/", "_", $kodeMarker) . '.pdf';

            return $pdf->download(str_replace("/", "_", $fileName));
        }
    }

    public function fixMarkerBalanceQty() {
        ini_set("maximum_execution_time", 3600);

        $markers = Marker::selectRaw("
                id,
                tgl_cutting,
                DATE_FORMAT(tgl_cutting, '%d-%m-%Y') tgl_cut_fix,
                kode,
                act_costing_ws,
                style,
                color,
                panel,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker)) panjang_marker,
                CONCAT(comma_marker, ' ', UPPER(unit_comma_marker)) comma_marker,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
                CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker,
                COALESCE(gramasi, 0) gramasi,
                gelar_qty,
                gelar_qty_balance,
                po_marker,
                urutan_marker,
                tipe_marker,
                COALESCE(b.total_form, 0) total_form,
                COALESCE(b.total_lembar, 0) total_lembar,
                CONCAT(COALESCE(b.total_lembar, 0), '/', gelar_qty) ply_progress,
                COALESCE(notes, '-') notes,
                cancel
            ")->
            leftJoin(
                DB::raw("
                    (
                        select
                            id_marker,
                            count(id_marker) total_form,
                            sum(qty_ply) total_lembar
                        from
                            form_cut_input
                        group by
                            id_marker
                    ) b"
                ),
                "marker_input.kode",
                "=",
                "b.id_marker"
            )->
            where("b.total_lembar", "<", DB::raw("gelar_qty"))->
            where("gelar_qty_balance", "!=", DB::raw("(gelar_qty - b.total_lembar)"))->
            get();

        foreach ($markers as $marker) {
            $thisMarker = Marker::where("id", $marker->id)->first();
            $thisMarker->gelar_qty_balance = $marker->gelar_qty - $marker->total_lembar;
            $thisMarker->save();
        }

        return array(
            "status" => 200,
            "message" => $markers->count()." Balance Marker telah berhasil dihitung ulang.",
            "redirect" => '',
            "additional" => []
        );
    }
}
