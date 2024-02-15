<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPemakaian;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

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
                $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59' ";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        act_costing_ws like '%" . $request->search["value"] . "%' OR
                        DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $data_pemakaian = DB::select("
                select
                    a.tgl_form_cut,
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
                    act_costing_ws,
                    id_item,
                    id_roll,
                    detail_item,
                    b.group_roll,
                    b.lot,
                    b.roll,
                    b.qty qty_item,
                    b.unit unit_item,
                    lembar_gelaran
                from
                    form_cut_input a
                inner join form_cut_input_detail b on a.no_form = b.no_form_cut_input
                inner join marker_input mrk on a.id_marker = mrk.kode
                where
                    a.cancel = 'N' and mrk.cancel = 'N'
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                ");

            return DataTables::of($data_pemakaian)->toJson();
        }

        return view('lap_pemakaian.lap_pemakaian', ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "lap-pemakaian"]);
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
