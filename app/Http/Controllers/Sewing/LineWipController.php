<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
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

            $ppicList = collect(
                DB::select("
                    SELECT
                        MAX(tgl_shipment) tanggal,
                        ppic_master_so.id_so_det,
                        master_sb_ws.id_ws,
                        master_sb_ws.ws,
                        master_sb_ws.color,
                        master_sb_ws.size
                    FROM
                        ppic_master_so
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                    GROUP BY
                        id_so_det
                    HAVING
                        tgl_shipment between '".$request->tanggal_awal."' AND '".$request->tanggal_akhir."'
                ")
            );

            if ($ppicList->count() > 0) {
                $soDetList = implode(',', $ppicList->pluck("id_so_det"));

                $dataOutput = DB::select("
                    SELECT
                        so_det_id,
                        user_sb_wip.line_id,
                        COUNT(output_rfts.id) total_output
                    FROM
                        output_rfts
                        LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                    WHERE
                        output_rfts.so_det_id in (".$soDetList.")
                    GROUP BY
                        user_sb_wip.line_id,
                        so_det_id
                ");

                $dataOutputPacking = DB::select("
                    SELECT
                        so_det_id,
                        userpassword.line_id,
                        COUNT(output_rfts_packing.id) total_output
                    FROM
                        output_rfts_packing
                        LEFT JOIN userpassword ON userpassword.username = output_rfts_packing.created_by
                    WHERE
                        output_rfts.so_det_id in (".$soDetList.")
                    GROUP BY
                        user_sb_wip.line_id,
                        so_det_id
                ");

                $data = DB::select("
                    SELECT
                        *
                    FROM
                    (
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
                            ppic_master_so.id_so_det in (".$soDetList.")
                        GROUP BY
                            id_so_det
                    ) ppic_master
                    LEFT JOIN (
                        SELECT
                            id_so_det,
                            sum(qty) total_transfer_garment
                        FROM
                            packing_trf_garment
                        WHERE
                            id_so_det in (".$soDetList.")
                        GROUP BY
                            packing_trf_garment.id_so_det
                    ) transfer_garment ON transfer_garment.id_so_det = ppic_master.id_so_det
                    LEFT JOIN
                    (
                        SELECT
                            MAX(ll.tanggal_loading) tanggal_loading,
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
                            stocker_input.so_det_id in (".$soDetList.")
                        GROUP BY
                            ll.nama_line,
                            si.so_det_id
                        HAVING
                            loading_qty > 0
                    ) loading_stock on loading_stock.so_det_id = ppic_master.id_so_det
                ");
            } else {
                $data = [];
            }

            return DataTable::of($data)->toJson();
        }

        return view("sewing.line-wip", ["page" => "dashboard-stocker",  "subPageGroup" => "proses-stocker", "subPage" => "stocker"]);
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
