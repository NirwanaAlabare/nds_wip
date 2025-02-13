<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\UserLine;
use App\Exports\Sewing\LineWipExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class LineWipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tanggalAwal = $request->tanggal_awal ? $request->tanggal_awal : date('Y-m-d');
            $tanggalAkhir = $request->tanggal_akhir ? $request->tanggal_akhir : date('Y-m-d');
            $lineIdFilter = $request->line_id ? "AND line_id = '".$request->line_id."'" : null;
            $lineFilter = $request->line ? "AND line = '".$request->line."'" : null;

            $ppicList = collect(
                DB::select("
                    SELECT
                        MAX(tgl_shipment) tanggal,
                        ppic_master_so.id_so_det,
                        master_sb_ws.ws,
                        master_sb_ws.color,
                        master_sb_ws.size
                    FROM
                        ppic_master_so
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                    GROUP BY
                        ppic_master_so.id_so_det
                    HAVING
                        MAX(tgl_shipment) between '".$request->tanggal_awal."' AND '".$request->tanggal_akhir."'
                ")
            );

            if ($ppicList->count() > 0) {
                $soDetList = implode(',', $ppicList->pluck("id_so_det")->toArray());

                $dataOutput = collect(DB::connection("mysql_sb")->select("
                    SELECT
                        so_det_id,
                        user_sb_wip.line_id,
                        act_costing.id as id_ws,
                        so_det.color,
                        so_det.size,
                        COUNT(output_rfts.id) total_output
                    FROM
                        output_rfts
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                        LEFT JOIN so_det on so_det.id = output_rfts.so_det_id
                        LEFT JOIN so on so.id = so_det.id_so
                        LEFT JOIN act_costing on act_costing.id = so.id_cost
                    WHERE
                        output_rfts.so_det_id in (".$soDetList.")
                        ".$lineIdFilter."
                    GROUP BY
                        user_sb_wip.line_id,
                        so_det_id
                "));

                $dataDefect = collect(DB::connection("mysql_sb")->select("
                    SELECT
                        so_det_id,
                        user_sb_wip.line_id,
                        act_costing.id as id_ws,
                        so_det.color,
                        so_det.size,
                        COUNT(output_defects.id) total_output
                    FROM
                        output_defects
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                        LEFT JOIN so_det on so_det.id = output_defects.so_det_id
                        LEFT JOIN so on so.id = so_det.id_so
                        LEFT JOIN act_costing on act_costing.id = so.id_cost
                    WHERE
                        output_defects.defect_status = 'defect' and
                        output_defects.so_det_id in (".$soDetList.")
                        ".$lineIdFilter."
                    GROUP BY
                        user_sb_wip.line_id,
                        so_det_id
                "));

                $dataReject = collect(DB::connection("mysql_sb")->select("
                    SELECT
                        so_det_id,
                        user_sb_wip.line_id,
                        act_costing.id as id_ws,
                        so_det.color,
                        so_det.size,
                        COUNT(output_rejects.id) total_output
                    FROM
                        output_rejects
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                        LEFT JOIN so_det on so_det.id = output_rejects.so_det_id
                        LEFT JOIN so on so.id = so_det.id_so
                        LEFT JOIN act_costing on act_costing.id = so.id_cost
                    WHERE
                        output_rejects.so_det_id in (".$soDetList.")
                        ".$lineIdFilter."
                    GROUP BY
                        user_sb_wip.line_id,
                        so_det_id
                "));

                $dataOutputPacking = collect(DB::connection("mysql_sb")->select("
                    SELECT
                        so_det_id,
                        userpassword.line_id,
                        act_costing.id as id_ws,
                        so_det.color,
                        so_det.size,
                        COUNT(output_rfts_packing.id) total_output
                    FROM
                        output_rfts_packing
                        LEFT JOIN userpassword ON userpassword.username = output_rfts_packing.created_by
                        LEFT JOIN so_det on so_det.id = output_rfts_packing.so_det_id
                        LEFT JOIN so on so.id = so_det.id_so
                        LEFT JOIN act_costing on act_costing.id = so.id_cost
                    WHERE
                        output_rfts_packing.so_det_id in (".$soDetList.")
                        $lineIdFilter
                    GROUP BY
                        userpassword.line_id,
                        so_det_id
                "));

                $data = collect(DB::select("
                    SELECT
                        ppic_master.tanggal,
                        ppic_master.id_ws,
                        ppic_master.ws,
                        ppic_master.styleno,
                        ppic_master.color,
                        ppic_master.size,
                        ppic_master.id_so_det,
                        loading_stock.line_id,
                        loading_stock.nama_line,
                        loading_stock.loading_qty,
                        transfer_garment.total_transfer_garment
                    FROM
                    (
                        SELECT
                            MAX(tgl_shipment) tanggal,
                            ppic_master_so.id_so_det,
                            master_sb_ws.id_act_cost as id_ws,
                            master_sb_ws.ws,
                            master_sb_ws.styleno,
                            master_sb_ws.color,
                            master_sb_ws.size,
                            master_sb_ws.dest
                        FROM
                            ppic_master_so
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                        WHERE
                            ppic_master_so.id_so_det in (".$soDetList.")
                        GROUP BY
                            master_sb_ws.id_act_cost,
                            master_sb_ws.color,
                            master_sb_ws.size
                    ) ppic_master
                    LEFT JOIN
                    (
                        SELECT
                            MAX(tanggal_loading) tanggal_loading,
                            line_id,
                            nama_line,
                            so_det_id,
                            act_costing_ws,
                            color,
                            size,
                            SUM(loading_qty) loading_qty
                        FROM (
                            SELECT
                                MAX(ll.tanggal_loading) tanggal_loading,
                                ll.line_id,
                                ll.nama_line,
                                si.act_costing_ws,
                                si.so_det_id,
                                si.color,
                                si.size,
                                (
                                    COALESCE(di.qty_awal, si.qty_ply_mod, si.qty_ply, 0)
                                    - COALESCE(di.qty_reject, 0)
                                    + COALESCE(di.qty_replace, 0)
                                    - COALESCE(sii.qty_reject, 0)
                                    + COALESCE(sii.qty_replace, 0)
                                    - COALESCE(sii_h.qty_reject, 0)
                                    + COALESCE(sii_h.qty_replace, 0)
                                ) AS loading_qty
                            FROM
                                loading_line ll
                                INNER JOIN stocker_input si ON si.id = ll.stocker_id
                                LEFT JOIN dc_in_input di ON di.id_qr_stocker = si.id_qr_stocker
                                LEFT JOIN secondary_in_input sii ON sii.id_qr_stocker = si.id_qr_stocker
                                LEFT JOIN secondary_inhouse_input sii_h ON sii_h.id_qr_stocker = si.id_qr_stocker
                            where
                                si.so_det_id in (".$soDetList.")
                                ".$lineIdFilter."
                            GROUP BY
                                ll.line_id,
                                si.form_cut_id,
                                si.so_det_id,
                                si.group_stocker,
                                si.ratio
                            HAVING
                                loading_qty > 0
                        ) ll
                        GROUP BY
                            line_id,
                            act_costing_ws,
                            color,
                            size
                    ) loading_stock on loading_stock.act_costing_ws = ppic_master.ws and loading_stock.color = ppic_master.color and loading_stock.size = ppic_master.size
                    LEFT JOIN (
                        SELECT
                            packing_trf_garment.line,
                            master_sb_ws.id_act_cost as id_ws,
                            master_sb_ws.ws,
                            master_sb_ws.color,
                            master_sb_ws.size,
                            packing_trf_garment.id_so_det,
                            sum(packing_trf_garment.qty) total_transfer_garment
                        FROM
                            packing_trf_garment
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
                        WHERE
                            packing_trf_garment.id_so_det in (".$soDetList.")
                            ".$lineFilter."
                        GROUP BY
                            packing_trf_garment.line,
                            master_sb_ws.id_act_cost,
                            master_sb_ws.color,
                            master_sb_ws.size
                    ) transfer_garment ON transfer_garment.line = loading_stock.nama_line and transfer_garment.id_ws = ppic_master.id_ws and transfer_garment.color = ppic_master.color and transfer_garment.size = ppic_master.size
                    GROUP BY
                        ppic_master.id_ws,
                        ppic_master.color,
                        ppic_master.size,
                        loading_stock.line_id
                    HAVING
                        ppic_master.id_so_det is not null
                        ".$lineIdFilter."
                    ORDER BY
                        ppic_master.id_so_det
                "));
            } else {
                $data = [];
                $dataReject = [];
                $dataDefect = [];
                $dataOutput = [];
                $dataOutputPacking = [];
            }

            return DataTables::of($data)->
                addColumn("reject", function ($data) use ($dataReject) {
                    $reject = $dataReject ? $dataReject->where("line_id", $data->line_id)->where("id_ws", $data->id_ws)->where("color", $data->color)->where("size", $data->size)->sum("total_output") : null;

                    return $reject;
                })->
                addColumn("defect", function ($data) use ($dataDefect) {
                    $defect = $dataDefect ? $dataDefect->where("line_id", $data->line_id)->where("id_ws", $data->id_ws)->where("color", $data->color)->where("size", $data->size)->sum("total_output") : null;

                    return $defect;
                })->
                addColumn("output", function ($data) use ($dataOutput) {
                    $output = $dataOutput ? $dataOutput->where("line_id", $data->line_id)->where("id_ws", $data->id_ws)->where("color", $data->color)->where("size", $data->size)->sum("total_output") : null;

                    return $output;
                })->
                addColumn("output_packing", function ($data) use ($dataOutputPacking) {
                    $output = $dataOutputPacking ? $dataOutputPacking->where("line_id", $data->line_id)->where("id_ws", $data->id_ws)->where("color", $data->color)->where("size", $data->size)->sum("total_output") : null;

                    return $output;
                })->
                toJson();
        }

        $lines = UserLine::where("Groupp", "SEWING")->whereRaw("(Locked is null OR Locked != 1)")->orderBy("line_id", 'asc')->get();

        return view("sewing.line-wip", ["page" => "dashboard-sewing-eff",  "subPageGroup" => "sewing-wip", "subPage" => "line-wip"], ["lines" => $lines]);
    }

    public function total(Request $request) {
        $tanggal_awal = $request->tanggal_awal ? $request->tanggal_awal : date('Y-m-d');
        $tanggal_akhir = $request->tanggal_akhir ? $request->tanggal_akhir : date('Y-m-d');
        $lineIdFilter = $request->line_id ? "AND line_id = '".$request->line_id."'" : null;
        $lineIdFilter1 = $request->line_id ? "AND userpassword.line_id = '".$request->line_id."'" : null;
        $lineFilter = $request->line ? "AND line = '".$request->line."'" : null;

        $lineNameFilter = "";
        $tanggalFilter = "";
        $lineNameFilter1 = "";
        $lineNameFilter2 = "";
        $lineNameFilter3 = "";
        $tanggalFilter = "";
        $wsFilter = "";
        $styleFilter = "";
        $colorFilter = "";
        $sizeFilter = "";
        $searchFilter = "";

        if ($request->lineNameFilter) {
            $lineNameFilter1 = "AND userpassword.username LIKE '%".(str_replace(" ", "_", $request->lineNameFilter))."%'";
            $lineNameFilter2 = "AND nama_line LIKE '%".(str_replace(" ", "_", $request->lineNameFilter))."%'";
            $lineNameFilter3 = "AND line LIKE '%".(str_replace(" ", "_", $request->lineNameFilter))."%'";
        }

        if ($request->tanggalFilter) {
            $tanggalFilter = "AND MAX(tgl_shipment) LIKE '%".($request->tanggalFilter)."%'";
        }

        if ($request->wsFilter) {
            $wsFilter = "AND master_sb_ws.ws LIKE '%".($request->wsFilter)."%'";
        }

        if ($request->styleFilter) {
            $styleFilter = "AND master_sb_ws.styleno LIKE '%".($request->styleFilter)."%'";
        }

        if ($request->colorFilter) {
            $colorFilter = "AND master_sb_ws.color LIKE '%".($request->colorFilter)."%'";
        }

        if ($request->sizeFilter) {
            $sizeFilter = "AND master_sb_ws.size LIKE '%".($request->sizeFilter)."%'";
        }

        if ($request->search) {
            $searchFilter = "
                AND
                (
                    ppic_master.tanggal LIKE '%".$request->search."%' OR
                    ppic_master.id_ws LIKE '%".$request->search."%' OR
                    ppic_master.ws LIKE '%".$request->search."%' OR
                    ppic_master.styleno LIKE '%".$request->search."%' OR
                    ppic_master.color LIKE '%".$request->search."%' OR
                    ppic_master.size LIKE '%".$request->search."%' OR
                    ppic_master.id_so_det LIKE '%".$request->search."%' OR
                    loading_stock.line_id LIKE '%".$request->search."%' OR
                    loading_stock.nama_line LIKE '%".$request->search."%' OR
                    loading_stock.loading_qty LIKE '%".$request->search."%' OR
                    transfer_garment.total_transfer_garment LIKE '%".$request->search."%'
                )
            ";
        }

        $ppicList = collect(
            DB::select("
                SELECT
                    MAX(tgl_shipment) tanggal,
                    ppic_master_so.id_so_det,
                    master_sb_ws.ws,
                    master_sb_ws.color,
                    master_sb_ws.size
                FROM
                    ppic_master_so
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                WHERE
                    ppic_master_so.id_so_det is not null
                    ".$wsFilter."
                    ".$styleFilter."
                    ".$colorFilter."
                    ".$sizeFilter."
                GROUP BY
                    id_so_det
                HAVING
                    (MAX(tgl_shipment) between '".$tanggal_awal."' AND '".$tanggal_akhir."')
                    ".$tanggalFilter."
            ")
        );

        if ($ppicList->count() > 0) {
            $soDetList = implode(',', $ppicList->pluck("id_so_det")->toArray());

            $dataOutput = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_rfts.id) total_output
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det on so_det.id = output_rfts.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_rfts.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $dataDefect = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_defects.id) total_output
                FROM
                    output_defects
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det on so_det.id = output_defects.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_defects.defect_status = 'defect' and
                    output_defects.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $dataReject = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_rejects.id) total_output
                FROM
                    output_rejects
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det on so_det.id = output_rejects.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_rejects.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $dataOutputPacking = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_rfts_packing.id) total_output
                FROM
                    output_rfts_packing
                    LEFT JOIN userpassword ON userpassword.username = output_rfts_packing.created_by
                    LEFT JOIN so_det on so_det.id = output_rfts_packing.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_rfts_packing.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $data = collect(DB::select("
                 SELECT
                    ppic_master.tanggal,
                    ppic_master.id_ws,
                    ppic_master.ws,
                    ppic_master.styleno,
                    ppic_master.color,
                    ppic_master.size,
                    ppic_master.id_so_det,
                    loading_stock.line_id,
                    loading_stock.nama_line,
                    loading_stock.loading_qty,
                    transfer_garment.total_transfer_garment
                FROM
                (
                    SELECT
                        MAX(tgl_shipment) tanggal,
                        ppic_master_so.id_so_det,
                        master_sb_ws.id_act_cost as id_ws,
                        master_sb_ws.ws,
                        master_sb_ws.styleno,
                        master_sb_ws.color,
                        master_sb_ws.size,
                        master_sb_ws.dest
                    FROM
                        ppic_master_so
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                    WHERE
                        ppic_master_so.id_so_det in (".$soDetList.")
                    GROUP BY
                        master_sb_ws.id_act_cost,
                        master_sb_ws.color,
                        master_sb_ws.size
                ) ppic_master
                LEFT JOIN
                (
                    SELECT
                        MAX(tanggal_loading) tanggal_loading,
                        line_id,
                        nama_line,
                        so_det_id,
                        act_costing_ws,
                        color,
                        size,
                        SUM(loading_qty) loading_qty
                    FROM (
                        SELECT
                            MAX(ll.tanggal_loading) tanggal_loading,
                            ll.line_id,
                            ll.nama_line,
                            si.act_costing_ws,
                            si.so_det_id,
                            si.color,
                            si.size,
                            (
                                COALESCE(di.qty_awal, si.qty_ply_mod, si.qty_ply, 0)
                                - COALESCE(di.qty_reject, 0)
                                + COALESCE(di.qty_replace, 0)
                                - COALESCE(sii.qty_reject, 0)
                                + COALESCE(sii.qty_replace, 0)
                                - COALESCE(sii_h.qty_reject, 0)
                                + COALESCE(sii_h.qty_replace, 0)
                            ) AS loading_qty
                        FROM
                            loading_line ll
                            INNER JOIN stocker_input si ON si.id = ll.stocker_id
                            LEFT JOIN dc_in_input di ON di.id_qr_stocker = si.id_qr_stocker
                            LEFT JOIN secondary_in_input sii ON sii.id_qr_stocker = si.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input sii_h ON sii_h.id_qr_stocker = si.id_qr_stocker
                        where
                            si.so_det_id in (".$soDetList.")
                            ".$lineIdFilter."
                        GROUP BY
                            ll.line_id,
                            si.form_cut_id,
                            si.so_det_id,
                            si.group_stocker,
                            si.ratio
                        HAVING
                            loading_qty > 0
                    ) ll
                    GROUP BY
                        line_id,
                        act_costing_ws,
                        color,
                        size
                ) loading_stock on loading_stock.act_costing_ws = ppic_master.ws and loading_stock.color = ppic_master.color and loading_stock.size = ppic_master.size
                LEFT JOIN (
                    SELECT
                        packing_trf_garment.line,
                        master_sb_ws.id_act_cost as id_ws,
                        master_sb_ws.ws,
                        master_sb_ws.color,
                        master_sb_ws.size,
                        packing_trf_garment.id_so_det,
                        sum(packing_trf_garment.qty) total_transfer_garment
                    FROM
                        packing_trf_garment
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
                    WHERE
                        packing_trf_garment.id_so_det in (".$soDetList.")
                        ".$lineFilter."
                        ".$lineNameFilter3."
                    GROUP BY
                        packing_trf_garment.line,
                        master_sb_ws.id_act_cost,
                        master_sb_ws.color,
                        master_sb_ws.size
                ) transfer_garment ON transfer_garment.line = loading_stock.nama_line and transfer_garment.id_ws = ppic_master.id_ws and transfer_garment.color = ppic_master.color and transfer_garment.size = ppic_master.size
                WHERE
                    ppic_master.id_so_det is not null
                    ".$lineIdFilter."
                    ".$lineNameFilter2."
                    ".$lineNameFilter3."
                    ".$searchFilter."
                GROUP BY
                    ppic_master.id_ws,
                    ppic_master.color,
                    ppic_master.size,
                    loading_stock.line_id
                HAVING
                    ppic_master.id_so_det is not null
                    ".$lineIdFilter."
                    ".$lineNameFilter2."
                ORDER BY
                    ppic_master.id_so_det
            "));
        } else {
            $data = collect([]);
            $dataReject = collect([]);
            $dataDefect = collect([]);
            $dataOutput = collect([]);
            $dataOutputPacking = collect([]);
        }

        $dataRejectOutput = $dataReject->whereIn("line_id", $data->pluck("line_id")->toArray())->whereIn("id_ws", $data->pluck("id_ws")->toArray())->whereIn("color", $data->pluck("color")->toArray())->whereIn("size", $data->pluck('size')->toArray());
        $dataDefectOutput = $dataDefect->whereIn("line_id", $data->pluck("line_id")->toArray())->whereIn("id_ws", $data->pluck("id_ws")->toArray())->whereIn("color", $data->pluck("color")->toArray())->whereIn("size", $data->pluck('size')->toArray());
        $dataOutputOutput = $dataOutput->whereIn("line_id", $data->pluck("line_id")->toArray())->whereIn("id_ws", $data->pluck("id_ws")->toArray())->whereIn("color", $data->pluck("color")->toArray())->whereIn("size", $data->pluck('size')->toArray());
        $dataOutputPackingOutput = $dataOutputPacking->whereIn("line_id", $data->pluck("line_id")->toArray())->whereIn("id_ws", $data->pluck("id_ws")->toArray())->whereIn("color", $data->pluck("color")->toArray())->whereIn("size", $data->pluck('size')->toArray());

        return json_encode(array(
            "total_loading" => $data ? $data->sum("loading_qty") : null,
            "total_transfer_garment" => $data ? $data->sum("total_transfer_garment") : null,
            "total_reject" => $dataRejectOutput ? $dataRejectOutput->sum("total_output") : null,
            "total_defect" => $dataDefectOutput ? $dataDefectOutput->sum("total_output") : null,
            "total_output" => $dataOutputOutput ? $dataOutputOutput->sum("total_output") : null,
            "total_output_packing" => $dataOutputPackingOutput ? $dataOutputPackingOutput->sum("total_output") : null
        ));
    }

    /**
     * Export Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportExcel(Request $request) {
        ini_set("max_execution_time", 36000);

        $tanggalAwal = $request->tanggal_awal ? $request->tanggal_awal : date('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ? $request->tanggal_akhir : date('Y-m-d');
        $lineIdFilter = $request->line_id ? $request->line_id : null;
        $lineFilter = $request->line ? $request->line : null;
        $lineNameFilter = $request->lineNameFilter ? $request->lineNameFilter : null;
        $tanggalFilter = $request->tanggalFilter ? $request->tanggalFilter : null;
        $wsFilter = $request->wsFilter ? $request->wsFilter : null;
        $styleFilter = $request->styleFilter ? $request->styleFilter : null;
        $colorFilter = $request->colorFilter ? $request->colorFilter : null;
        $sizeFilter = $request->sizeFilter ? $request->sizeFilter : null;

        return Excel::download(new LineWipExport($tanggalAwal, $tanggalAkhir, $lineIdFilter, $lineFilter, $lineNameFilter, $tanggalFilter, $wsFilter, $styleFilter, $colorFilter, $sizeFilter), 'production_excel.xlsx');
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
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function show(DataDetailProduksiDay $dataDetailProduksiDay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function edit(DataDetailProduksiDay $dataDetailProduksiDay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDataDetailProduksiDayRequest $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataDetailProduksiDay $dataDetailProduksiDay)
    {
        //
    }
}
