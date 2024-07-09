<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Exports\ExportLaporanRoll;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class RollController extends Controller
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
                $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00'";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59'";
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
                    COALESCE(id_roll, '-') id_roll,
                    detail_item,
                    COALESCE(b.color_act, '-') color_act,
                    COALESCE(b.group_roll, '-') group_roll,
                    COALESCE(b.lot, '-') lot,
                    COALESCE(b.roll, '-') roll,
                    b.no_form_cut_input,
                    b.qty qty_item,
                    b.unit unit_item,
                    b.sisa_gelaran,
                    b.sambungan,
                    b.est_amparan,
                    b.lembar_gelaran,
                    b.kepala_kain,
                    b.sisa_tidak_bisa,
                    b.reject,
                    COALESCE(b.sisa_kain, 0) sisa_kain,
                    b.total_pemakaian_roll,
                    b.short_roll,
                    b.piping,
                    b.remark,
                    UPPER(meja.name) nama_meja
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
                    left join marker_input mrk on a.id_marker = mrk.kode
                    left join users meja on meja.id = a.no_meja
                where
                    a.cancel = 'N' and mrk.cancel = 'N' and id_item is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                order by
                    DATE(b.created_at) desc,
                    act_costing_ws asc,
                    b.id_roll desc,
                    b.id_item desc,
                    b.no_form_cut_input desc
                ");

            return DataTables::of($data_pemakaian)->toJson();
        }

        return view('cutting.roll.roll', ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "lap-pemakaian"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */

    public function export_excel(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportLaporanRoll($request->from, $request->to), 'Laporan_pemakaian_cutting.xlsx');
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
