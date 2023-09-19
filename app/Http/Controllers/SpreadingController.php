<?php

namespace App\Http\Controllers;

use App\Models\FormCutting;
use App\Models\Spreading;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class SpreadingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_spreading = DB::select("SELECT a.id_marker, a.no_form, a.tgl_form_cut, b.act_costing_ws ws,panel, b.color, a.status  FROM `form_cut_input` a
        left join marker_input b on a.id_marker = b.kode");
        return view('spreading.spreading', ['data_s' => $data_spreading]);
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

        $data_ws = DB::select("select act_costing_id, act_costing_ws ws from marker_input where tgl_cutting = '$tgl_f'");

        return view('spreading.create-spreading', ['data_ws' => $data_ws]);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function getno_marker(Request $request)
    {
        $datano_marker = DB::select("select *,  concat(kode,' - ',color, ' - (',panel, ' - ',urutan_marker, ' )') tampil
        from marker_input where act_costing_id = '" . $request->cbows . "' order by urutan_marker asc");
        $html = "<option value=''>Pilih No Marker</option>";

        foreach ($datano_marker as $datanomarker) {
            $html .= " <option value='" . $datanomarker->id . "'>" . $datanomarker->tampil . "</option> ";
        }

        return $html;
    }

    public function getdata_marker(Request $request)
    {
        $data_marker = DB::select("select a.* from marker_input a
        where a.id = '" . $request->cri_item . "'");

        return json_encode($data_marker[0]);
    }

    public function getdata_ratio(Request $request)
    {
        $data_ratio = DB::select("select * from marker_input_detail where marker_id = '" . $request->cbomarker . "'");

        // return json_encode($data_marker[0]);
        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($data_ratio)),
            "recordsFiltered" => intval(count($data_ratio)),
            "data" => $data_ratio
        ]);
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

        $timestamp = Carbon::now();
        $formcutDetailData = [];
        for ($i = 0; $i < intval($request['hitungform']); $i++) {

            $queryno_form     = DB::select("select count(id_marker) urutan from form_cut_input where tgl_form_cut = '$txttglcut'");
            $datano_form     = $queryno_form[0];
            $urutan          = $datano_form->urutan;
            $urutan_fix      = $urutan + $i;

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
                "tgl_input" => $timestamp
            ]);
        }

        $markerDetailStore = FormCutting::insert($formcutDetailData);

        return array(
            "status" => 200,
            "message" => $txttglcut,
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
    public function update(Request $request, Spreading $spreading)
    {
        //
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
