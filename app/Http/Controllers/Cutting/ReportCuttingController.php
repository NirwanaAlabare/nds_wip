<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Exports\ExportReportCutting;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ReportCuttingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function cutting(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and form_cut_input.tgl_form_cut >= '".$request->dateFrom."'";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and form_cut_input.tgl_form_cut <= '".$request->dateTo."'";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        marker_cutting.tgl_form_cut like '%" . $request->search["value"] . "%' OR
                        marker_cutting.meja like '%" . $request->search["value"] . "%' OR
                        marker_cutting.buyer like '%" . $request->search["value"] . "%' OR
                        marker_cutting.act_costing_ws like '%" . $request->search["value"] . "%' OR
                        marker_cutting.style like '%" . $request->search["value"] . "%' OR
                        marker_cutting.color like '%" . $request->search["value"] . "%' OR
                        marker_cutting.notes like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $reportCutting = DB::select("
                SELECT
                    marker_cutting.tgl_form_cut,
                    marker_cutting.meja,
                    marker_cutting.buyer,
                    marker_cutting.act_costing_ws,
                    marker_cutting.style,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.cons_ws,
                    marker_cutting.unit,
                    marker_cutting.so_det_id,
                    marker_cutting.size,
                    COALESCE(marker_cutting.notes, '-') notes,
                    SUM(marker_cutting.marker_gelar * marker_cutting.ratio) marker_gelar,
                    SUM(marker_cutting.spreading_gelar  * marker_cutting.ratio) spreading_gelar,
                    SUM(marker_cutting.form_gelar * marker_cutting.ratio) form_gelar
                FROM
                    (
                        SELECT
                            marker_input.kode,
                            form_cut.meja,
                            form_cut.tgl_form_cut,
                            marker_input.buyer,
                            marker_input.act_costing_id,
                            marker_input.act_costing_ws,
                            marker_input.style,
                            marker_input.color,
                            marker_input.panel,
                            marker_input.cons_ws,
                            marker_input.unit_panjang_marker unit,
                            marker_input_detail.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            marker_input_detail.ratio,
                            COALESCE(marker_input.notes, form_cut.notes) notes,
                            marker_input.gelar_qty marker_gelar,
                            SUM(form_cut.qty_ply) spreading_gelar,
                            SUM(COALESCE(form_cut.total_lembar, form_cut.detail)) form_gelar
                        FROM
                            marker_input
                            INNER JOIN
                                marker_input_detail on marker_input_detail.marker_id = marker_input.id
                            INNER JOIN
                                master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                            INNER JOIN
                                (
                                    SELECT
                                        meja.`name` meja,
                                        DATE(form_cut_input.waktu_mulai) tgl_form_cut,
                                        form_cut_input.id_marker,
                                        form_cut_input.no_form,
                                        form_cut_input.qty_ply,
                                        form_cut_input.total_lembar,
                                        form_cut_input.notes,
                                        SUM(form_cut_input_detail.lembar_gelaran) detail
                                    FROM
                                        form_cut_input
                                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                                    WHERE
                                        form_cut_input.`status` != 'SPREADING'
                                        AND form_cut_input.waktu_mulai is not null
                                        ".$additionalQuery."
                                    GROUP BY
                                        form_cut_input.no_form
                                ) form_cut on form_cut.id_marker = marker_input.kode
                            where
                                (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                                AND marker_input_detail.ratio > 0
                            group by
                                marker_input.id,
                                marker_input_detail.so_det_id,
                                form_cut.tgl_form_cut
                    ) marker_cutting
                GROUP BY
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.so_det_id,
                    marker_cutting.tgl_form_cut
                ORDER BY
                    marker_cutting.panel,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.so_det_id,
                    marker_cutting.tgl_form_cut
                ");

            return DataTables::of($reportCutting)->toJson();
        }

        return view('cutting.report.report-cutting', ['page' => 'dashboard-cutting', "subPageGroup" => "cutting-report", "subPage" => "cutting"]);
    }

    public function pemakaianRoll(Request $request) {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
            $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

            $pemakaianRoll = DB::connection("mysql_sb")->select("
                select a.*,b.no_bppb no_out, COALESCE(total_roll,0) roll_out, ROUND(COALESCE(qty_out,0), 2) qty_out, c.no_dok no_retur, COALESCE(total_roll_ri,0) roll_retur, COALESCE(qty_out_ri,0) qty_retur from (select bppbno,bppbdate,s.supplier tujuan,ac.kpno no_ws,ac.styleno,ms.supplier buyer,a.id_item,
                mi.itemdesc,a.qty qty_req,a.unit
                from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier
                inner join jo_det jod on a.id_jo=jod.id_jo
                inner join so on jod.id_so=so.id
                inner join act_costing ac on so.id_cost=ac.id
                inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
                inner join masteritem mi on a.id_item=mi.id_item
                where bppbno like '%RQ-F%' and a.id_supplier = '432' and bppbdate between '".$dateFrom."' and '".$dateTo."'
                group by a.id_item,a.bppbno
                order by bppbdate,bppbno desc) a left join
                (select a.no_bppb,no_req,id_item,COUNT(id_roll) total_roll, sum(qty_out) qty_out,satuan from whs_bppb_h a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '2024-05-01' and '2024-08-31' GROUP BY bppbno) b on b.bppbno = a.no_req inner join whs_bppb_det c on c.no_bppb = a.no_bppb where a.status != 'Cancel' GROUP BY a.no_bppb,no_req,id_item) b on b.no_req = a.bppbno and b.id_item  =a.id_item left join
                (select a.no_dok, no_invoice no_req,id_item,COUNT(no_barcode) total_roll_ri, sum(qty_sj) qty_out_ri,satuan from (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting' ) a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '".$dateFrom."' and '".$dateTo."' GROUP BY bppbno) b on b.bppbno = a.no_invoice INNER JOIN whs_lokasi_inmaterial c on c.no_dok = a.no_dok GROUP BY a.no_dok,no_invoice,id_item) c on c.no_req = a.bppbno and c.id_item  =a.id_item
            ");

            $cutting = collect(
                DB::select("
                    SELECT
                        a.no_bppb,
                        a.no_req,
                        GROUP_CONCAT(cutting.id_roll) id_roll,
                        cutting.id_item,
                        sum( qty_out ) qty_out,
                        COUNT( cutting.id_roll ) total_roll,
                        ROUND ( (CASE WHEN satuan = 'YRD' OR satuan = 'YARD' THEN sum( cutting.total_qty ) * 1.09361 ELSE sum( cutting.total_qty ) END ) , 2) total_qty_roll,
                        ROUND ( (CASE WHEN satuan = 'YRD' OR satuan = 'YARD' THEN sum( cutting.total_pemakaian_roll ) * 1.09361 ELSE sum( cutting.total_pemakaian_roll ) END ) , 2) total_pakai_roll,
                        cutting.satuan
                    FROM
                        whs_bppb_h a
                        INNER JOIN ( SELECT bppbno, bppbdate FROM bppb_req WHERE bppbno LIKE '%RQ-F%' AND id_supplier = '432' AND bppbdate between '".$dateFrom."' and '".$dateTo."'  GROUP BY bppbno ) b ON b.bppbno = a.no_req
                        INNER JOIN ( select whs_bppb_det.id_roll, whs_bppb_det.id_item, whs_bppb_det.no_bppb, whs_bppb_det.satuan, whs_bppb_det.qty_out, COUNT(form_cut_input_detail.id) total_roll, MAX(CAST(form_cut_input_detail.qty as decimal(11,3))) total_qty, SUM(form_cut_input_detail.total_pemakaian_roll) total_pemakaian_roll from whs_bppb_det inner join form_cut_input_detail on form_cut_input_detail.id_roll = whs_bppb_det.id_roll group by whs_bppb_det.id_roll ) as cutting on cutting.no_bppb = a.no_bppb
                    WHERE
                        a.STATUS != 'Cancel'
                    GROUP BY
                        a.no_bppb,
                        no_req,
                        id_item
                ")
            );

            return DataTables::of($pemakaianRoll)->
                addColumn('id_roll', function ($row) use ($cutting) {
                    $data = $cutting->where("no_req", $row->bppbno)->where("id_item", $row->id_item)->first();

                    return $data ? $data->id_roll : 0;
                })->
                addColumn('total_roll_cutting', function ($row) use ($cutting) {
                    $data = $cutting->where("no_req", $row->bppbno)->where("id_item", $row->id_item)->first();

                    return $data ? $data->total_roll : 0;
                })->
                addColumn('total_qty_cutting', function ($row) use ($cutting) {
                    $data = $cutting->where("no_req", $row->bppbno)->where("id_item", $row->id_item)->first();

                    return $data ? $data->total_qty_roll : 0;
                })->
                addColumn('total_pakai_cutting', function ($row) use ($cutting) {
                    $data = $cutting->where("no_req", $row->bppbno)->where("id_item", $row->id_item)->first();

                    return $data ? $data->total_pakai_roll : 0;
                })->toJson();
        }

        return view('cutting.report.pemakaian-roll', ['page' => 'dashboard-cutting', "subPageGroup" => "cutting-report", "subPage" => "pemakaian-roll"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param  \Illuminate\Http\Request  $request
     */

    public function export(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportReportCutting($request->dateFrom, $request->dateTo), 'Report Cutting.xlsx');
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
