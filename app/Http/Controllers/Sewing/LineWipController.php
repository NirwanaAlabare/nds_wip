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
                        id_so_det
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
                        COUNT(output_rfts.id) total_output
                    FROM
                        output_rfts
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                    WHERE
                        output_rfts.status = 'NORMAL' AND
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
                        COUNT(output_defects.id) total_output
                    FROM
                        output_defects
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
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
                        COUNT(output_rejects.id) total_output
                    FROM
                        output_rejects
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
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
                        COUNT(output_rfts_packing.id) total_output
                    FROM
                        output_rfts_packing
                        LEFT JOIN userpassword ON userpassword.username = output_rfts_packing.created_by
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
                        ppic_master.ws,
                        ppic_master.styleno,
                        ppic_master.color,
                        ppic_master.size,
                        ppic_master.dest,
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
                            id_so_det
                    ) ppic_master
                    LEFT JOIN
                    (
                        SELECT
                            MAX(ll.tanggal_loading) tanggal_loading,
                            ll.line_id,
                            ll.nama_line,
                            si.so_det_id,
                            si.size,
                            SUM(
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
                            ll.nama_line,
                            si.so_det_id
                        HAVING
                            loading_qty > 0
                    ) loading_stock on loading_stock.so_det_id = ppic_master.id_so_det
                    LEFT JOIN (
                        SELECT
                            id_so_det,
                            sum(qty) total_transfer_garment
                        FROM
                            packing_trf_garment
                        WHERE
                            id_so_det in (".$soDetList.")
                            ".$lineFilter."
                        GROUP BY
                            packing_trf_garment.id_so_det
                    ) transfer_garment ON transfer_garment.id_so_det = ppic_master.id_so_det
                    GROUP BY
                        ppic_master.id_so_det,
                        loading_stock.line_id
                    HAVING
                        loading_stock.line_id is not null
                        ".$lineIdFilter."
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
                    $reject = $dataReject->where("line_id", $data->line_id)->where("so_det_id", $data->id_so_det)->first();

                    return $reject ? $reject->total_output : '0';
                })->
                addColumn("defect", function ($data) use ($dataDefect) {
                    $defect = $dataDefect->where("line_id", $data->line_id)->where("so_det_id", $data->id_so_det)->first();

                    return $defect ? $defect->total_output : '0';
                })->
                addColumn("output", function ($data) use ($dataOutput) {
                    $output = $dataOutput->where("line_id", $data->line_id)->where("so_det_id", $data->id_so_det)->first();

                    return $output ? $output->total_output : '0';
                })->
                addColumn("output_packing", function ($data) use ($dataOutputPacking) {
                    $output = $dataOutputPacking->where("line_id", $data->line_id)->where("so_det_id", $data->id_so_det)->first();

                    return $output ? $output->total_output : '0';
                })->
                toJson();
        }

        $lines = UserLine::where("Groupp", "SEWING")->whereRaw("(Locked is null OR Locked != 1)")->orderBy("line_id", 'asc')->get();

        return view("sewing.line-wip", ["page" => "dashboard-sewing-eff",  "subPageGroup" => "sewing-wip", "subPage" => "line-wip"], ["lines" => $lines]);
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

        return Excel::download(new LineWipExport($tanggalAwal, $tanggalAkhir, $lineIdFilter, $lineFilter), 'production_excel.xlsx');
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
