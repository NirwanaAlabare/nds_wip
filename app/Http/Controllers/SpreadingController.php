<?php

namespace App\Http\Controllers;

use App\Models\FormCutInput;
use App\Models\MarkerDetail;
use App\Models\Marker;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class SpreadingController extends Controller
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
                $additionalQuery .= " where a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
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
                " . $additionalQuery . "
                " . $keywordQuery . "
                GROUP BY a.id
                ORDER BY a.updated_at desc
            ");

            return DataTables::of($data_spreading)->toJson();
        }

        $meja = User::select("id", "name", "username")->where('type', 'meja')->get();

        return view('spreading.spreading', ['meja' => $meja, 'page' => 'dashboard-cutting']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tgl_f = Carbon::today()->toDateString();
        // dd($tgl_f);

        // $data_ws = DB::select("select act_costing_id, act_costing_ws ws from marker_input where tgl_cutting = '$tgl_f' group by act_costing_id");

        $data_ws = DB::select("select act_costing_id, act_costing_ws ws from marker_input a
        left join (select id_marker from form_cut_input group by id_marker ) b on a.kode = b.id_marker
        where a.cancel = 'N' and b.id_marker is null
        group by act_costing_id");


        return view('spreading.create-spreading', ['data_ws' => $data_ws, 'page' => 'dashboard-cutting']);
    }

    public function getOrderInfo(Request $request)
    {
        $order = DB::connection('mysql_sb')->table('act_costing')->selectRaw('act_costing.id, act_costing.kpno, act_costing.styleno, act_costing.qty order_qty, mastersupplier.supplier buyer')->leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer')->where('act_costing.kpno', $request->ws)->first();

        return json_encode($order);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function getno_marker(Request $request)
    {
        $tgl_f = Carbon::today()->toDateString();
        // $datano_marker = DB::select("select *,  concat(kode,' - ',color, ' - (',panel, ' - ',urutan_marker, ' )') tampil
        // from marker_input where act_costing_id = '" . $request->cbows . "' and tgl_cutting = '$tgl_f' order by urutan_marker asc");
        $datano_marker = DB::select("select *,  concat(kode,' - ',color, ' - (',panel, ' - ',urutan_marker, ' )') tampil  from marker_input a
        left join (select id_marker from form_cut_input group by id_marker ) b on a.kode = b.id_marker
        where act_costing_id = '" . $request->cbows . "' and b.id_marker is null order by urutan_marker asc");
        $html = "<option value=''>Pilih No Marker</option>";

        foreach ($datano_marker as $datanomarker) {
            $html .= " <option value='" . $datanomarker->id . "'>" . $datanomarker->tampil . "</option> ";
        }

        return $html;
    }

    public function getdata_marker(Request $request)
    {
        $data_marker = DB::select("select a.* from marker_input a
        where a.id = '" . $request->cri_item . "' and a.cancel = 'N'");

        return json_encode($data_marker[0]);
    }

    public function getdata_ratio(Request $request)
    {
        $markerId = $request->cbomarker ? $request->cbomarker : 0;

        $data_ratio = DB::select("
            select
                *
            from
                marker_input_detail
            where marker_id = '" . $markerId . "'
        ");

        return DataTables::of($data_ratio)->toJson();
    }

    public function store(Request $request)
    {
        $txttglcut             = date('Y-m-d');
        $validatedRequest = $request->validate([
            "txtqty_ply_cut" => "required",
            "txtpanel" => "required",
            "txtcolor" => "required",
            "txtbuyer" => "required",
            "txtstyle" => "required",
            "txt_p_marker" => "required",
            "txt_unit_p_marker" => "required",
            "txt_comma_p_marker" => "required",
            "txt_unit_comma_p_marker" => "required",
            "txt_po_marker" => "required",
            "txt_l_marker" => "required",
            "txt_unit_l_marker" => "required",
            "txt_qty_gelar" => "required",
            "txt_ws" => "required",
            "txt_cons_ws" => "required",
            "txt_cons_marker" => "required",
            "txtid_marker" => "required"
        ]);

        $qtyPlyMarkerModulus = intval($request['hitungmarker']) % intval($request['txtqty_ply_cut']);
        $timestamp = Carbon::now();
        $formcutDetailData = [];
        $message = "";
        for ($i = 1; $i <= intval($request['hitungform']); $i++) {
            $queryno_form     = DB::select("select count(id_marker) urutan from form_cut_input where tgl_form_cut = '$txttglcut'");
            $datano_form     = $queryno_form[0];
            $urutan          = $datano_form->urutan;
            $urutan_fix      = $urutan + $i;
            $qtyPly = $request['txtqty_ply_cut'];

            if (intval($request['hitungform'] > 1)) {
                if ($i == intval($request['hitungform'])) {
                    $qtyPly = $qtyPlyMarkerModulus;
                }
            }

            $hari          = substr($txttglcut, 8, 2);
            $bulan         = substr($txttglcut, 5, 2);
            $no_form       = "$hari-$bulan-$urutan_fix";

            array_push($formcutDetailData, [
                "id_marker" => $request["txtid_marker"],
                "no_form" => $no_form,
                "tgl_form_cut" => $txttglcut,
                "status" => "SPREADING",
                "user" => "user",
                "cancel" => "N",
                "qty_ply" => $qtyPly,
                "tgl_input" => $timestamp,
                "created_at" => $timestamp,
                "updated_at" => $timestamp,
            ]);

            $message .= "$no_form <br>";
        }

        $markerDetailStore = FormCutInput::insert($formcutDetailData);

        return array(
            "status" => 200,
            "message" => $message,
            "additional" => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Spreading  $spreading
     * @return \Illuminate\Http\Response
     */
    public function show(Spreading $spreading)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Spreading  $spreading
     * @return \Illuminate\Http\Response
     */
    public function edit(Spreading $spreading)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Spreading  $spreading
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_no_meja" => "required",
        ]);

        $updateNoMeja = FormCutInput::where('id', $validatedRequest['edit_id'])->update([
            'no_meja' => $validatedRequest['edit_no_meja']
        ]);

        if ($updateNoMeja) {
            $updatedData = FormCutInput::where('id', $validatedRequest['edit_id'])->first();
            $meja = User::where('id', $validatedRequest['edit_no_meja'])->first();
            return array(
                'status' => 200,
                'message' => 'Alokasi Meja "' . ucfirst($meja->name) . '" ke form "' . $updatedData->no_form . '" berhasil',
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
     * @param  \App\Models\Spreading  $spreading
     * @return \Illuminate\Http\Response
     */
    public function destroy(Spreading $spreading)
    {
        //
    }
}
