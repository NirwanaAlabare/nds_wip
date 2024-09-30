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
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", 36000);

        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and DATE(b.created_at) >= '" . $request->dateFrom . "'";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and DATE(b.created_at) <= '" . $request->dateTo . "'";
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
                    DATE_FORMAT(b.updated_at, '%M) bulan,
                    DATE_FORMAT(b.updated_at, '%d-%m-%Y) tgl_input,
                    b.no_form_cut_input,
                    UPPER(meja.name) nama_meja,
                    act_costing_ws,
                    buyer,
                    style,
                    color,
                    b.color_act,
                    panel,
                    master_sb_ws.qty,
                    cons_ws,
                    cons_marker,
                    a.cons_ampar,
                    a.cons_act,
                    COALESCE(a.cons_pipping, cons_piping) cons_piping,
                    panjang_marker,
                    unit_panjang_marker,
                    comma_marker,
                    unit_comma_marker,
                    a.p_act panjang_actual,
                    a.unit_p_act unit_panjang_actual,
                    a.comma_p_act comma_actual,
                    a.unit_comma_p_act unit_comma_actual,
                    a.l_act lebar_actual,
                    a.unit_l_actual unit_lebar_actual,
                    COALESCE(id_roll, '-') id_roll,
                    id_item,
                    detail_item,
                    COALESCE(b.roll_buyer, b.roll) roll,
                    COALESCE(b.lot, '-') lot,
                    b.qty qty_roll,
                    b.unit unit_roll,
                    COALESCE(b.berat_amparan, '-') berat_amparan,
                    b.est_amparan,
                    b.lembar_gelaran,
                    mrk.total_ratio,
                    (mrk.total_ratio * b.lembar_gelaran) qty_cut,
                    b.average_time,
                    b.sisa_gelaran,
                    b.sambungan,
                    b.sambungan_roll,
                    b.kepala_kain,
                    b.lembar_gelaran,
                    b.sisa_tidak_bisa,
                    b.reject,
                    b.piping,
                    COALESCE(b.sisa_kain, 0) sisa_kain,
                    b.pemakaian_lembar,
                    b.total_pemakaian_roll,
                    b.short_roll,
                    CONCAT(ROUND(((b.short_roll / b.qty) * 100), 2), ' %') short_roll_percentage,
                    a.operator
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
                    left join users meja on meja.id = a.no_meja
                    left join marker_input mrk on a.id_marker = mrk.kode
                    left join master_sb_ws.id_ws
                where
                    (a.cancel = 'N'  OR a.cancel IS NULL)
	                AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
                    and id_item is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                group by
                    b.id
                order by
                    a.waktu_mulai asc,
                    b.id asc
            ");

            return DataTables::of($data_pemakaian)->toJson();
        }

        return view('cutting.roll.roll', ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "lap-pemakaian"]);
    }

    public function pemakaianRollData(Request $request)
    {
        $additionalQuery = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and DATE(b.created_at) >= '" . $request->dateFrom . "'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and DATE(b.created_at) <= '" . $request->dateTo . "'";
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
                DATE_FORMAT(b.updated_at, '%M') bulan,
                DATE_FORMAT(b.updated_at, '%d-%m-%Y') tgl_input,
                b.no_form_cut_input,
                UPPER(meja.name) nama_meja,
                mrk.act_costing_ws,
                mrk.buyer,
                mrk.style,
                mrk.color,
                COALESCE(b.color_act, '-') color_act,
                mrk.panel,
                master_sb_ws.qty,
                cons_ws,
                cons_marker,
                a.cons_ampar,
                a.cons_act,
                COALESCE(a.cons_pipping, cons_piping) cons_piping,
                panjang_marker,
                unit_panjang_marker,
                comma_marker,
                unit_comma_marker,
                a.p_act panjang_actual,
                a.unit_p_act unit_panjang_actual,
                a.comma_p_act comma_actual,
                a.unit_comma_p_act unit_comma_actual,
                a.l_act lebar_actual,
                a.unit_l_act unit_lebar_actual,
                COALESCE(id_roll, '-') id_roll,
                id_item,
                detail_item,
                COALESCE(b.roll_buyer, b.roll) roll,
                COALESCE(b.lot, '-') lot,
                b.qty qty_roll,
                b.unit unit_roll,
                COALESCE(b.berat_amparan, '-') berat_amparan,
                b.est_amparan,
                b.lembar_gelaran,
                mrk.total_ratio,
                (mrk.total_ratio * b.lembar_gelaran) qty_cut,
                b.average_time,
                b.sisa_gelaran,
                b.sambungan,
                b.sambungan_roll,
                b.kepala_kain,
                b.lembar_gelaran,
                b.sisa_tidak_bisa,
                b.reject,
                b.piping,
                COALESCE(b.sisa_kain, 0) sisa_kain,
                b.pemakaian_lembar,
                b.total_pemakaian_roll,
                b.short_roll,
                CONCAT(ROUND(((b.short_roll / b.qty) * 100), 2), ' %') short_roll_percentage,
                a.operator
            from
                form_cut_input a
                left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
                left join users meja on meja.id = a.no_meja
                left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
                left join master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
            where
                (a.cancel = 'N'  OR a.cancel IS NULL)
                AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
                and id_item is not null
                " . $additionalQuery . "
                " . $keywordQuery . "
            group by
                b.id
            order by
                act_costing_ws asc,
                a.no_form desc,
                b.id asc
        ");

        return DataTables::of($data_pemakaian)->toJson();
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
