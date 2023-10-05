<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\MarkerDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarkerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $markers = Marker::all();

        if ($request->ajax()) {
            $tglAwal = $request->tgl_awal;
            $tglAkhir = $request->tgl_akhir;
            $keyword = $request->search["value"];

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
                gelar_qty,
                po_marker,
                urutan_marker
            ");


            if ($tglAwal) {
                $markersQuery->whereRaw("tgl_cutting >= '" . $tglAwal . "'");
            }

            if ($tglAkhir) {
                $markersQuery->whereRaw("tgl_cutting <= '" . $tglAkhir . "'");
            }

            if ($keyword) {
                $markersQuery->whereRaw("(
                    DATE_FORMAT(tgl_cutting, '%d-%m-%Y') like '%" . $keyword . "%' OR
                    kode like '%" . $keyword . "%' OR
                    act_costing_ws like '%" . $keyword . "%' OR
                    color like '%" . $keyword . "%' OR
                    panel like '%" . $keyword . "%' OR
                    po_marker like '%" . $keyword . "%' OR
                    urutan_marker like '%" . $keyword . "%'
                )");
            }

            $markers = $markersQuery->orderBy("id", "desc")->get();

            return json_encode([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval(count($markers)),
                "recordsFiltered" => intval(count($markers)),
                "data" => $markers
            ]);
        }

        return view('marker.marker');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            $markerDetail = MarkerDetail::selectRaw("marker_input_detail.so_det_id, marker_input.panel, SUM(marker_input_detail.cut_qty) total_cut_qty")->leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')->groupBy("marker_input_detail.so_det_id", "marker_input.panel")->get();

            return $markerDetail;
        }

        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('marker.create-marker', ['orders' => $orders]);
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
        $sizes = DB::connection('mysql_sb')->select("
                select sd.id, ac.kpno no_ws, sd.color, sd.qty order_qty, sd.size from so_det sd
                    inner join so on sd.id_so = so.id
                    inner join act_costing ac on so.id_cost = ac.id
                    inner join master_size_new msn on sd.size = msn.size
                where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and sd.cancel = 'N'
                group by sd.size
                order by msn.urutan asc
            ");

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
                    )a
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
            "cons_marker" => "required"
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
        SELECT size, ratio from marker_input_detail where marker_id = '$request->id_c'");

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
            <div class='col-sm-6'>
                <div class='form-group'>
                    <label class='form-label'><small>PO</small></label>
                    <input type='text' class='form-control' id='txtpo' name='txtpo' value = '" . $datanomarker->po_marker . "' readonly>
                </div>
            </div>
            <div class='col-sm-6'>
                <div class='form-group'>
                    <label class='form-label'><small>Urutan</small></label>
                    <input type='text' class='form-control' id='txturutan' name='txturutan'  value = '" . $datanomarker->urutan_marker . "' readonly>
                </div>
            </div>
        </div>
        </div>

        <div class='row'>
        <div class='col-sm-12'>
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
    public function update(Request $request, Marker $marker)
    {
        //
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
}
