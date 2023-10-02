<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPemakaian;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Http\Request;


class LapPemakaianController extends Controller
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
                $additionalQuery .= " where b.created_at >= '" . $request->dateFrom . " 00:00:00' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        act_costing_ws like '%" . $request->search["value"] . "%' OR
                        a.tgl_form_cut like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $data_pemakaian = DB::select("select a.tgl_form_cut,
            DATE_FORMAT(tgl_form_cut, '%d-%m-%Y') tgl_form_cut_fix,
            b.detail_item,
            b.qty qty_item,
            b.unit unit_item,
            act_costing_ws,
            mrk.color,
            month(a.tgl_form_cut),
            b.roll,
            b.lot,
            mrk.cons_marker,
            a.cons_pipping,
            a.cons_ampar,
            a.cons_act,
            mrk.panel,
            b.unit,
            b.lembar_gelaran,
            mr.tot_ratio,
            concat (mrk.panjang_marker,'.',mrk.comma_marker) panjang_marker,
            mrk.unit_panjang_marker,
            concat (a.p_act,'.',a.comma_p_act) panjang_act,
            a.unit_p_act,
            mrk.lebar_marker,
            mrk.unit_lebar_marker,
            a.l_act,
            a.unit_l_act,
            b.lembar_gelaran * mr.tot_ratio qty_potong,
            b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) actual_gelar_kain,
            b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) + b.sambungan + b.piping + b.sisa_tidak_bisa kain_terpakai,
            b.sisa_kain,
            b.sisa_tidak_bisa,
            b.sambungan,
            b.piping,
            b.kepala_kain,
            b.reject,
            b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) + b.sambungan + b.piping + b.kepala_kain + b.sisa_kain + b.reject + b.sisa_tidak_bisa total_aktual_kain,
            round((b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) / b.lembar_gelaran * mr.tot_ratio /100),2) cons,
            b.created_at
            from form_cut_input a
            inner join form_cut_input_detail b on a.no_form = b.no_form_cut_input
            inner join marker_input mrk on a.id_marker = mrk.kode
            inner join (select marker_id, sum(ratio) tot_ratio from marker_input_detail group by marker_id) mr on mrk.id = mr.marker_id
                " . $additionalQuery . "
                " . $keywordQuery . "
            ");

            return json_encode([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval(count($data_pemakaian)),
                "recordsFiltered" => intval(count($data_pemakaian)),
                "data" => $data_pemakaian
            ]);
        }

        return view('lap_pemakaian.lap_pemakaian');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */

    public function export_excel(Request $request)
    {

        return Excel::download(new ExportLaporanPemakaian($request->from, $request->to), 'Laporan_pemakaian_cutting.xlsx');
    }



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
