<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FormCutInput;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class SummaryController extends Controller
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

            if ($request->date) {
                $additionalQuery .= "and date(a.waktu_selesai) = '" . $request->date . "' ";
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
                    b.style,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    b.color color,
                    a.status,
                    users.name nama_meja,
                    b.panjang_marker panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    a.qty_ply,
                    b.gelar_qty gelar_qty,
                    b.po_marker po_marker,
                    b.urutan_marker urutan_marker,
                    b.cons_marker cons_marker,
                    cutting_plan.app,
                    a.tipe_form_cut,
                    b.notes notes,
                    GROUP_CONCAT(DISTINCT CONCAT(' ', master_size_new.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC) marker_details
                FROM cutting_plan
                left join form_cut_input a on a.no_form = cutting_plan.no_form_cut_input
                left outer join marker_input b on a.id_marker = b.kode and b.cancel = 'N'
                left outer join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                where
                    a.id is not null and
                    a.status != 'SPREADING'
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

        return view('summary', ["page" => "dashboard-cutting", "subPage" => "summary-cutting"]);
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
}
