<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\MarkerDetail;
use Illuminate\Http\Request;
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
                color,
                panel,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker)) panjang_marker,
                CONCAT(comma_marker, ' ', UPPER(unit_comma_marker)) comma_marker,
                CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
                CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker,
                gramasi,
                gelar_qty,
                po_marker,
                urutan_marker,
                ifnull(b.tot_form,0) tot_form,
                notes,
                cancel
            ")->leftJoin(DB::raw("(select id_marker,coalesce(count(id_marker),0) tot_form from form_cut_input group by id_marker)b"), "marker_input.kode", "=", "b.id_marker");

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

        return view('marker.marker', ["page" => "dashboard-cutting"]);
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
                ")->leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')->where('marker_input.cancel', 'N')->groupBy("marker_input_detail.so_det_id", "marker_input.panel")->get();

            return $markerDetail;
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('marker.create-marker', ['orders' => $orders, 'page' => 'dashboard-cutting']);
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
        $sizes = DB::table("master_sb_ws")->selectRaw("
                master_sb_ws.id_so_det so_det_id,
                master_sb_ws.ws no_ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.qty order_qty
            ")->where("id_act_cost", $request->act_costing_id)->where("color", $request->color)->join("master_size_new", "master_size_new.size", "=", "master_sb_ws.size")->groupBy("id_act_cost", "id_so_det")->orderBy("master_size_new.urutan")->get();

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
            "cons_ws" => "required",
            "color" => "required",
            "panel" => "required",
            "p_marker" => "required",
            "p_unit" => "required",
            "comma_marker" => "required",
            "comma_unit" => "required",
            "l_marker" => "required",
            "l_unit" => "required",
            "gelar_marker_qty" => "required",
            "po" => "required",
            "no_urut_marker" => "required",
            "cons_marker" => "required",
            "gramasi" => "required",
            "tipe_marker" => "required"
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
                'panel' => $validatedRequest['panel'],
                'panjang_marker' => $validatedRequest['p_marker'],
                'unit_panjang_marker' => $validatedRequest['p_unit'],
                'comma_marker' => $validatedRequest['comma_marker'],
                'unit_comma_marker' => $validatedRequest['comma_unit'],
                'lebar_marker' => $validatedRequest['l_marker'],
                'unit_lebar_marker' => $validatedRequest['l_unit'],
                'gelar_qty' => $validatedRequest['gelar_marker_qty'],
                'po_marker' => $validatedRequest['po'],
                'urutan_marker' => $validatedRequest['no_urut_marker'],
                'cons_marker' => $validatedRequest['cons_marker'],
                'gramasi' => $validatedRequest['gramasi'],
                'tipe_marker' => $validatedRequest['tipe_marker'],
                'notes' => $request['notes'],
                'cancel' => 'N',
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
        DATE_FORMAT(tgl_cutting, '%d-%m-%Y') tgl_cut_fix,
        CONCAT(panjang_marker, ' ', UPPER(unit_panjang_marker), ' ',comma_marker, ' ', UPPER(unit_comma_marker)) panjang_marker_fix,
        CONCAT(lebar_marker, ' ', UPPER(unit_lebar_marker)) lebar_marker_fix,
        b.qty_order
        from marker_input a
        left join (select id_act_cost,sum(qty) qty_order from master_sb_ws group by id_act_cost) b on a.act_costing_id = b.id_act_cost
        where id = '$request->id_c'");

        $data_marker_det = DB::select("
        SELECT a.size, ratio
        from marker_input_detail a
        inner join master_size_new b on a.size = b.size
        where marker_id = '$request->id_c'
        order by urutan asc");

        $data_marker_tracking = DB::select("
        select no_form,
        DATE_FORMAT(tgl_form_cut, '%d-%m-%Y') tgl_form_cut,
        no_meja,
        DATE_FORMAT(waktu_mulai, '%d-%m-%Y %T') waktu_mulai,
        DATE_FORMAT(waktu_selesai, '%d-%m-%Y %T') waktu_selesai,
        status
        from form_cut_input a
        inner join marker_input b on  a.id_marker = b.kode
        where b.id = '$request->id_c'");

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
                $html_tracking .= "
            <tr>
            <td>$track->no_form</td>
            <td>$track->tgl_form_cut</td>
            <td>$track->no_meja</td>
            <td>$track->waktu_mulai</td>
            <td>$track->waktu_selesai</td>
            <td>$track->status</td>
            </tr>
 ";
            endforeach;


            $html = "

        <div class='row'>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Tgl Cutting</small></label>
                    <input type='text' class='form-control' id='txttgl_cutting' name='txttgl_cutting' value = '" . $datanomarker->tgl_cut_fix . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>No. WS</small></label>
                    <input type='text' class='form-control' id='txtno_ws' name='txtno_ws'  value = '" . $datanomarker->act_costing_ws . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Color</small></label>
                    <input type='text' class='form-control' id='txtcol' name='txtcol'  value = '" . $datanomarker->color . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Panel</small></label>
                    <input type='text' class='form-control' id='txtpanel' name='txtpanel'  value = '" . $datanomarker->panel . "' readonly>
                </div>
            </div>
        </div>


        <div class='row'>
            <div class='col-sm-6'>
                <div class='form-group'>
                    <label class='form-label'><small>Buyer</small></label>
                    <input type='text' class='form-control' id='txtbuyer' name='txtbuyer' value = '" . $datanomarker->buyer . "' readonly>
                </div>
            </div>
            <div class='col-sm-6'>
                <div class='form-group'>
                    <label class='form-label'><small>Panjang Marker</small></label>
                    <input type='text' class='form-control' id='txtp_marker' name='txtp_marker'  value = '" . $datanomarker->panjang_marker_fix . "' readonly>
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-sm-6'>
                <div class='form-group'>
                    <label class='form-label'><small>Style</small></label>
                    <input type='text' class='form-control' id='txtstyle' name='txtstyle' value = '" . $datanomarker->style . "' readonly>
                </div>
            </div>
            <div class='col-sm-6'>
                <div class='form-group'>
                    <label class='form-label'><small>Lebar Marker</small></label>
                    <input type='text' class='form-control' id='txtl_marker' name='txtl_marker'  value = '" . $datanomarker->lebar_marker_fix . "' readonly>
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Cons WS</small></label>
                    <input type='text' class='form-control' id='txtcons_ws' name='txtcons_ws' value = '" . $datanomarker->cons_ws . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Qty Order</small></label>
                    <input type='text' class='form-control' id='txtqty_order' name='txtqty_order' value = '" . $datanomarker->qty_order . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Cons Marker</small></label>
                    <input type='text' class='form-control' id='txtcons_marker' name='txtcons_marker'  value = '" . $datanomarker->cons_marker . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Qty Gelar Marker</small></label>
                    <input type='text' class='form-control' id='txtgelar' name='txtgelar'  value = '" . $datanomarker->gelar_qty . "' readonly>
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>PO</small></label>
                    <input type='text' class='form-control' id='txtpo' name='txtpo' value = '" . $datanomarker->po_marker . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Gramasi</small></label>
                    <input type='text' class='form-control' id='txturutan' name='txturutan'  value = '" . $datanomarker->gramasi . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Urutan</small></label>
                    <input type='text' class='form-control' id='txturutan' name='txturutan'  value = '" . $datanomarker->urutan_marker . "' readonly>
                </div>
            </div>
            <div class='col-sm-3'>
                <div class='form-group'>
                    <label class='form-label'><small>Catatan</small></label>
                    <textarea class='form-control' id='txtarea' name='txtarea' readonly>"
                . ($datanomarker->notes ? $datanomarker->notes : '-') .
                "</textarea>
                </div>
            </div>
        </div>
        </div>


        <div class='row'>
        <div class='col-sm-12'>
            <div class='card card-primary collapsed-card'>
                <div class='card-header'>
                <h1 class='card-title'>Detail Size</h1>
                <div class='card-tools'>
                <button type='button' class='btn btn-tool' data-card-widget='collapse'><i
                        class='fas fa-plus'></i></button>
            </div>
                </div>
                <div class='card-body' style='display: none;'>
        <div class='table-responsive'>
        <table class='table table-bordered table-striped table-sm w-100'>
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
            <div class='card card-warning collapsed-card'>
                <div class='card-header'>
                    <h1 class='card-title'>Status Form</h1>
                    <div class='card-tools'>
                        <button type='button' class='btn btn-tool' data-card-widget='collapse'>
                            <i class='fas fa-plus'></i>
                        </button>
                    </div>
                </div>
                <div class='card-body' style='display: none;'>
                    <div class='table-responsive'>
                        <table class='table table-bordered table-striped'>
                            <thead>
                                <tr>
                                    <th>No. Form</th>
                                    <th>Tgl. Form</th>
                                    <th>No. Meja</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Status</th>
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
    public function edit(Marker $marker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function show_gramasi(Request $request)
    {
        $data_gramasi = DB::select("
        select id,gramasi from marker_input
        where id = '$request->id_c'");
        return json_encode($data_gramasi[0]);
    }

    public function update_status(Request $request, Marker $marker)
    {
        $update_data = DB::update("
        update marker_input set cancel = case when cancel = 'Y' then'N' else 'Y' end
        where id = '$request->id_c'");
    }

    public function update_marker(Request $request)
    {
        $update_gramasi = DB::update("
        update marker_input set gramasi = '$request->txt_gramasi'
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
            ')->leftJoin('so', 'so.id', '=', 'so_det.id_so')->leftJoin('act_costing', 'so.id_cost', '=', 'act_costing.id')->where('act_costing.id', $markerData->act_costing_id)->where('so_det.color', $markerData->color)->get();
        $orderQty = DB::connection('mysql_sb')->select("
            select k.cons cons_ws,sum(sd.qty) order_qty from bom_jo_item k
                inner join so_det sd on k.id_so_det = sd.id
                inner join so on sd.id_so = so.id
                inner join act_costing ac on so.id_cost = ac.id
                inner join masteritem mi on k.id_item = mi.id_gen
                inner join masterpanel mp on k.id_panel = mp.id
            where ac.id = '" . $markerData->act_costing_id . "' and sd.color = '" . $markerData->color . "' and mp.nama_panel ='" . $markerData->panel . "' and k.status = 'M'
            and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
            group by sd.color, k.id_item, k.unit
            limit 1");

        if ($markerData) {
            // generate pdf
            PDF::setOption(['dpi' => 150]);
            $pdf = PDF::loadView('marker.pdf.print-marker', ["markerData" => $markerData, "actCostingData" => $actCostingData, "soDetData" => $soDetData, "orderQty" => $orderQty])->setPaper('a4', 'landscape');

            $path = public_path('pdf/');
            $fileName = 'stocker-' . str_replace("/", "_", $kodeMarker) . '.pdf';
            $pdf->save($path . '/' . str_replace("/", "_", $kodeMarker));
            $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $kodeMarker));

            return response()->download($generatedFilePath);
        }
    }
}
