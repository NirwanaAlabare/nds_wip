<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\FormCutInputDetail;
use App\Exports\ExportReportCutting;
use App\Exports\ExportReportCuttingSinglePage;
use App\Exports\ExportPemakaianKain;
use App\Exports\ExportDetailPemakaianKain;
use App\Exports\ExportReportCuttingDaily;
use App\Exports\export_excel_report_cutting_mutasi_fabric;
use App\Exports\Cutting\CuttingOrderOutputExport;
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
            $additionalQuery1 = "";

            $dateFrom = ($request->dateFrom ?  $request->dateFrom : date("Y-m-d"));
            $dateTo = ($request->dateFrom ? ($request->dateTo ? $request->dateTo : null) : ($request->dateTo ? $request->dateTo : date("Y-m-d")));

            $additionalQuery .= " and COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) >= '" . ($dateFrom) . "'";
            $additionalQuery1 .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) >= '" . ($dateFrom) . "'";

            $additionalQuery .= " and COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) <= '" . ($dateTo) . "'";
            $additionalQuery1 .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) <= '" . ($dateTo) . "'";

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
                    marker_input.kode,
                    form_cut.no_form,
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
                    SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                    SUM(modify_size_qty.difference_qty) form_diff
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
                                COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) tgl_form_cut,
                                form_cut_input.id_marker,
                                form_cut_input.id,
                                form_cut_input.no_form,
                                form_cut_input.qty_ply,
                                form_cut_input.total_lembar,
                                form_cut_input.notes,
                                SUM(form_cut_input_detail.lembar_gelaran) detail
                            FROM
                                form_cut_input
                                LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                            WHERE
                                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                AND form_cut_input.waktu_mulai is not null
                                " . $additionalQuery . "
                            GROUP BY
                                form_cut_input.id
                        ) form_cut on form_cut.id_marker = marker_input.kode
                    LEFT JOIN
                        modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                where
                    (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                    AND marker_input_detail.ratio > 0
                group by
                    marker_input.id,
                    marker_input_detail.so_det_id,
                    form_cut.tgl_form_cut
                UNION
                SELECT
                    null as kode,
                    form_cut_piece.no_form,
                    null as meja,
                    form_cut_piece.tanggal as tgl_form_cut,
                    form_cut_piece.buyer,
                    form_cut_piece.act_costing_id,
                    form_cut_piece.act_costing_ws,
                    form_cut_piece.style,
                    form_cut_piece.color,
                    form_cut_piece.panel,
                    form_cut_piece.cons_ws,
                    'PCS' unit,
                    form_cut_piece_detail_size.so_det_id,
                    CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                    1 as ratio,
                    'PCS' notes,
                    SUM(form_cut_piece_detail_size.qty) marker_gelar,
                    SUM(form_cut_piece_detail_size.qty) spreading_gelar,
                    SUM(form_cut_piece_detail_size.qty) form_gelar,
                    null form_diff
                FROM
                    form_cut_piece
                    INNER JOIN
                        form_cut_piece_detail on form_cut_piece_detail.form_id = form_cut_piece.id
                    INNER JOIN
                        form_cut_piece_detail_size on form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                    INNER JOIN
                        master_sb_ws on master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                where
                    form_cut_piece_detail_size.qty > 0
                    " . $additionalQuery1 . "
                group by
                    form_cut_piece.id,
                    form_cut_piece_detail_size.so_det_id,
                    form_cut_piece.tanggal
            ");

            return DataTables::of($reportCutting)->toJson();
        }

        return view('cutting.report.report-cutting', ['page' => 'dashboard-cutting', "subPageGroup" => "cutting-report", "subPage" => "cutting"]);
    }

    public function totalCutting(Request $request)
    {
        $additionalQuery = "";
        $additionalQueryPcs = "";
        $additionalQuery1 = "";
        $additionalQueryPcs1 = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) >= '" . $request->dateFrom . "'";
            $additionalQueryPcs .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) >= '" . $request->dateFrom . "'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) <= '" . $request->dateTo . "'";
            $additionalQueryPcs .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) <= '" . $request->dateTo . "'";
        }

        if ($request->tgl_form_cut) {
            $additionalQuery .= " and COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) LIKE '%" . $request->tgl_form_cut . "%'";
            $additionalQueryPcs .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), DATE(form_cut_piece.tanggal)) LIKE '%" . $request->tgl_form_cut . "%'";
        }

        if ($request->buyer) {
            $additionalQuery1 .= " and marker_input.buyer LIKE '%" . $request->buyer . "%'";
            $additionalQueryPcs1 .= " and form_cut_piece.buyer LIKE '%" . $request->buyer . "%'";
        }

        if ($request->ws) {
            $additionalQuery1 .= " and marker_input.act_costing_ws LIKE '%" . $request->ws . "%'";
            $additionalQueryPcs1 .= " and form_cut_piece.act_costing_ws LIKE '%" . $request->ws . "%'";
        }

        if ($request->style) {
            $additionalQuery1 .= " and marker_input.style LIKE '%" . $request->style . "%'";
            $additionalQueryPcs1 .= " and form_cut_piece.style LIKE '%" . $request->style . "%'";
        }

        if ($request->color) {
            $additionalQuery1 .= " and marker_input.color LIKE '%" . $request->color . "%'";
            $additionalQueryPcs1 .= " and form_cut_piece.color LIKE '%" . $request->color . "%'";
        }

        if ($request->panel) {
            $additionalQuery1 .= " and marker_input.panel LIKE '%" . $request->panel . "%'";
            $additionalQueryPcs1 .= " and form_cut_piece.panel LIKE '%" . $request->panel . "%'";
        }

        if ($request->size) {
            $additionalQuery1 .= " and marker_input_detail.buyer LIKE '%" . $request->size . "%'";
            $additionalQueryPcs1 .= " and form_cut_piece.buyer LIKE '%" . $request->size . "%'";
        }

        if ($request->notes) {
            $additionalQuery1 .= " and (form_cut.notes LIKE '%" . $request->notes . "%' or marker_input.notes LIKE '%" . $request->notes . "%')";
            $additionalQueryPcs1 .= " and ('PCS' LIKE '%" . $request->notes . "%')";
        }

        $reportCutting = DB::select("
            SELECT
                SUM(marker_cutting.marker_gelar * marker_cutting.ratio) marker_gelar,
                SUM(marker_cutting.spreading_gelar  * marker_cutting.ratio) spreading_gelar,
                SUM((marker_cutting.form_gelar * marker_cutting.ratio) + COALESCE(marker_cutting.diff, 0)) form_gelar,
                SUM(COALESCE(marker_cutting.diff, 0)) form_diff
            FROM
                (
                    SELECT
                        marker_input.kode,
                        form_cut.no_form,
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
                        SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                        SUM(modify_size_qty.difference_qty) diff
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
                                    COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) tgl_form_cut,
                                    form_cut_input.id_marker,
                                    form_cut_input.id,
                                    form_cut_input.no_form,
                                    form_cut_input.qty_ply,
                                    form_cut_input.total_lembar,
                                    form_cut_input.notes,
                                    SUM(form_cut_input_detail.lembar_gelaran) detail
                                FROM
                                    form_cut_input
                                    LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                WHERE
                                    form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                    AND form_cut_input.waktu_mulai is not null
                                    " . $additionalQuery . "
                                GROUP BY
                                    form_cut_input.id
                            ) form_cut on form_cut.id_marker = marker_input.kode
                        LEFT JOIN
                            modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                    where
                        (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                        AND marker_input_detail.ratio > 0
                        " . $additionalQuery1 . "
                    group by
                        marker_input.id,
                        marker_input_detail.so_det_id,
                        form_cut.tgl_form_cut
                    UNION
                    SELECT
                        null as kode,
                        form_cut_piece.no_form,
                        null as meja,
                        form_cut_piece.tanggal as tgl_form_cut,
                        form_cut_piece.buyer,
                        form_cut_piece.act_costing_id,
                        form_cut_piece.act_costing_ws,
                        form_cut_piece.style,
                        form_cut_piece.color,
                        form_cut_piece.panel,
                        form_cut_piece.cons_ws,
                        'PCS' unit,
                        form_cut_piece_detail_size.so_det_id,
                        CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                        1 as ratio,
                        'PCS' notes,
                        SUM(form_cut_piece_detail_size.qty) marker_gelar,
                        SUM(form_cut_piece_detail_size.qty) spreading_gelar,
                        SUM(form_cut_piece_detail_size.qty) form_gelar,
                        null form_diff
                    FROM
                        form_cut_piece
                        INNER JOIN
                            form_cut_piece_detail on form_cut_piece_detail.form_id = form_cut_piece.id
                        INNER JOIN
                            form_cut_piece_detail_size on form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                        INNER JOIN
                            master_sb_ws on master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                    where
                        form_cut_piece_detail_size.qty > 0
                        " . $additionalQueryPcs . "
                        " . $additionalQueryPcs1 . "
                    group by
                        form_cut_piece.id,
                        form_cut_piece_detail_size.so_det_id,
                        form_cut_piece.tanggal
                ) marker_cutting
            ");

        return $reportCutting;
    }

    public function pemakaianRoll(Request $request)
    {
        ini_set("max_execution_time", 36000);

        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
            $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

            $pemakaianRoll = DB::connection("mysql_sb")->select("
                select a.*,b.no_bppb no_out, COALESCE(total_roll,0) roll_out, ROUND(COALESCE(qty_out,0), 2) qty_out, c.no_dok no_retur, COALESCE(total_roll_ri,0) roll_retur, ROUND(COALESCE(qty_out_ri,0), 2) qty_retur from (select bppbno,bppbdate,s.supplier tujuan,ac.kpno no_ws,ac.styleno,ms.supplier buyer,a.id_item,
                REPLACE(mi.itemdesc, '\"', '\\\\\"') itemdesc,a.qty qty_req,a.unit
                from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier
                inner join jo_det jod on a.id_jo=jod.id_jo
                inner join so on jod.id_so=so.id
                inner join act_costing ac on so.id_cost=ac.id
                inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
                inner join masteritem mi on a.id_item=mi.id_item
                where bppbno like '%RQ-F%' and a.id_supplier = '432' and bppbdate between '" . $dateFrom . "' and '" . $dateTo . "'
                group by a.id_item,a.bppbno
                order by bppbdate,bppbno desc) a left join
                (select a.no_bppb,no_req,id_item,COUNT(id_roll) total_roll, sum(qty_out) qty_out,satuan from whs_bppb_h a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '" . $dateFrom . "' and '" . $dateTo . "' GROUP BY bppbno) b on b.bppbno = a.no_req inner join whs_bppb_det c on c.no_bppb = a.no_bppb where a.status != 'Cancel' and c.status = 'Y' GROUP BY a.no_bppb,no_req,id_item) b on b.no_req = a.bppbno and b.id_item = a.id_item left join
                (select a.no_dok, no_invoice no_req,id_item,COUNT(no_barcode) total_roll_ri, sum(qty_sj) qty_out_ri,satuan from (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting' ) a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '" . $dateFrom . "' and '" . $dateTo . "' GROUP BY bppbno) b on b.bppbno = a.no_invoice INNER JOIN whs_lokasi_inmaterial c on c.no_dok = a.no_dok GROUP BY a.no_dok,no_invoice,id_item) c on c.no_req = a.bppbno and c.id_item  =a.id_item
            ");

            // $cutting = collect(
            //     DB::select("
            //         SELECT
            //             a.no_bppb,
            //             a.no_req,
            //             cutting.id_item,
            //             sum( qty_out ) qty_out,
            //             COUNT( cutting.id_roll ) total_roll,
            //             ROUND ( (CASE WHEN satuan = 'YRD' OR satuan = 'YARD' THEN sum( cutting.total_qty ) * 1.09361 ELSE sum( cutting.total_qty ) END ) , 2) total_qty_roll,
            //             ROUND ( (CASE WHEN satuan = 'YRD' OR satuan = 'YARD' THEN sum( cutting.total_pemakaian_roll ) * 1.09361 ELSE sum( cutting.total_pemakaian_roll ) END ) , 2) total_pakai_roll,
            //             cutting.satuan
            //         FROM
            //             whs_bppb_h a
            //             INNER JOIN ( SELECT bppbno, bppbdate FROM bppb_req WHERE bppbno LIKE '%RQ-F%' AND id_supplier = '432' AND bppbdate between '".$dateFrom."' and '".$dateTo."'  GROUP BY bppbno ) b ON b.bppbno = a.no_req
            //             INNER JOIN ( select whs_bppb_det.id_roll, whs_bppb_det.id_item, whs_bppb_det.no_bppb, whs_bppb_det.satuan, whs_bppb_det.qty_out, COUNT(form_cut_input_detail.id) total_roll, MAX(CAST(form_cut_input_detail.qty as decimal(11,3))) total_qty, SUM(form_cut_input_detail.total_pemakaian_roll) total_pemakaian_roll from whs_bppb_det inner join form_cut_input_detail on form_cut_input_detail.id_roll = whs_bppb_det.id_roll group by whs_bppb_det.id_roll ) as cutting on cutting.no_bppb = a.no_bppb
            //         WHERE
            //             a.STATUS != 'Cancel'
            //         GROUP BY
            //             a.no_bppb,
            //             no_req,
            //             id_item
            //     ")
            // );

            $rollData = collect();
            foreach ($pemakaianRoll as $row) {
                $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '" . $row->bppbno . "' and b.id_item = '" . $row->id_item . "' and b.status = 'Y' GROUP BY id_roll"));

                $rollIds = addQuotesAround($rollIdsArr->pluck("id_roll")->implode("\n"));

                $rolls = collect(DB::select("
                    SELECT
                        req.id_roll,
                        req.id_item,
                        req.itemdesc detail_item,
                        req.no_lot lot,
                        req.styleno,
                        req.color,
                        req.size,
                        COALESCE(roll.roll, req.no_roll) roll,
                        (CASE WHEN piping.id_roll IS NOT NULL THEN GREATEST(COALESCE(roll.qty, req.qty_out, 0), piping.qty) ELSE COALESCE(roll.qty, req.qty_out, 0) END) qty,
                        (CASE WHEN piping.id_roll IS NOT NULL THEN LEAST(COALESCE(roll.sisa_kain, req.qty_out, 0), COALESCE(piping.sisa_kain, 0)) ELSE COALESCE(roll.sisa_kain, req.qty_out, 0) END) sisa_kain,
                        COALESCE(roll.sisa_kain, req.qty_out, 0) - COALESCE(piping.piping, 0) as sisa_kain,
                        COALESCE(roll.unit, req.satuan) unit,
                        COALESCE(roll.total_pemakaian_roll, 0) + COALESCE(piping.piping, 0) as total_pemakaian_roll,
                        COALESCE(roll.total_short_roll_2, 0) total_short_roll_2,
                        COALESCE(roll.total_short_roll, 0) total_short_roll
                    FROM (
                        select b.*, c.itemdesc, c.color, c.size, tmpjo.styleno from signalbit_erp.whs_bppb_h a INNER JOIN signalbit_erp.whs_bppb_det b on b.no_bppb = a.no_bppb LEFT JOIN signalbit_erp.masteritem c ON c.id_item = b.id_item left join (select id_jo,kpno,styleno from signalbit_erp.act_costing ac inner join signalbit_erp.so on ac.id=so.id_cost inner join signalbit_erp.jo_det jod on signalbit_erp.so.id=jod.id_so group by id_jo) tmpjo on tmpjo.id_jo=b.id_jo WHERE a.no_req = '" . $row->bppbno . "' and b.id_item = '" . $row->id_item . "' and b.status = 'Y' GROUP BY id_roll
                    ) req
                    LEFT JOIN (
                        select
                            id_roll,
                            id_item,
                            detail_item,
                            lot,
                            COALESCE(roll_buyer, roll) roll,
                            MAX(qty) qty,
                            ROUND(MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END), 2) sisa_kain,
                            unit,
                            ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
                            ROUND(SUM(short_roll), 2) total_short_roll_2,
                            ROUND((SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) - MAX(qty), 2) total_short_roll
                        from
                            laravel_nds.form_cut_input_detail
                        WHERE
                            `status` in ('complete', 'need extension', 'extension complete')
                            " . ($rollIds ? "and id_roll in (" . $rollIds . ")" : "") . "
                        GROUP BY
                            id_item,
                            id_roll
                    UNION ALL
                        SELECT
                            id_roll,
                            id_item,
                            detail_item,
                            lot,
                            COALESCE ( roll_buyer, roll ) roll,
                            MAX( form_cut_piece_detail.qty_pengeluaran ) qty,
                            MIN( form_cut_piece_detail.qty_sisa ) sisa_kain,
                            qty_unit as unit,
                            ROUND( SUM( form_cut_piece_detail.qty_pemakaian ) ) total_pemakaian_roll,
                            ROUND( SUM( form_cut_piece_detail.qty - (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa) ) ) total_short_roll_2,
                            ROUND( SUM( form_cut_piece_detail.qty - (form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa) ) ) total_short_roll
                        FROM
                            `form_cut_piece_detail`
                        WHERE
                            `status` = 'complete'
                            " . ($rollIds ? "and id_roll in (" . $rollIds . ")" : "") . "
                        GROUP BY
                            `id_item`,
                            `id_roll`
                    ) roll ON req.id_roll = roll.id_roll
                    left join (
                        select
                            id_roll,
                            SUM(form_cut_piping.qty) qty,
                            SUM(form_cut_piping.piping) piping,
                            MIN(form_cut_piping.qty_sisa) sisa_kain
                        from
                            form_cut_piping
                        where
                            id_roll IS NOT NULL
                            " . ($rollIds ? "and id_roll in (" . $rollIds . ")" : "") . "
                        group by
                            id_roll
                    ) piping on piping.id_roll = req.id_roll
                "));

                $rollCutting = $rolls ? $rolls->where("total_pemakaian_roll", ">", 0)->count() : '0';
                $balanceRoll = $rolls ? $row->roll_out - $rollCutting : $row->roll_out;
                $balancePakai = $rolls ? $row->qty_out - (($row->unit == 'YARD' || $row->unit == 'YRD') ? $rolls->sum("total_pemakaian_roll") * 1.0361 : $rolls->sum("total_pemakaian_roll")) : $row->qty_out;

                $rollData->push(collect([
                    'bppbno' => $row->bppbno,
                    'bppbno' => $row->bppbno,
                    'bppbdate' => $row->bppbdate,
                    'no_out' => $row->no_out,
                    'tujuan' => $row->tujuan,
                    'no_ws' => $row->no_ws,
                    'styleno' => $row->styleno,
                    'buyer' => $row->buyer,
                    'id_item' => $row->id_item,
                    'itemdesc' => $row->itemdesc,
                    'qty_req' => $row->qty_req,
                    'unit' => $row->unit,
                    'no_out' => $row->no_out,
                    'roll_out' => $row->roll_out,
                    'qty_out' => $row->qty_out,
                    'total_roll_cutting' => $rollCutting,
                    'total_pakai_cutting' => $rolls ? (($row->unit == 'YARD' || $row->unit == 'YRD') ? round($rolls->sum("total_pemakaian_roll") * 1.0361, 2) : round($rolls->sum("total_pemakaian_roll"), 2)) : 0,
                    'total_qty_cutting' => $rolls ? (($row->unit == 'YARD' || $row->unit == 'YRD') ? round($rolls->sum("qty") * 1.09361, 2) : round($rolls->sum("qty"), 2)) : 0,
                    'total_short_cutting' => $rolls ? (($row->unit == 'YARD' || $row->unit == 'YRD') ? round($rolls->sum("total_short_roll") * 1.0361, 2) : round($rolls->sum("total_short_roll"), 2)) : 0,
                    'total_roll_balance' => $balanceRoll > 0 ? $balanceRoll : ($balanceRoll < 0 ? str_replace("-", "+", round($balanceRoll, 2)) : round($balanceRoll, 2)),
                    'total_pakai_balance' => $balancePakai > 0 ? round($balancePakai, 2) : ($balancePakai < 0 ? (str_replace("-", "+", round($balancePakai, 2))) : round($balancePakai, 2)),
                    'no_retur' => $row->no_retur,
                    'roll_retur' => $row->roll_retur,
                    'qty_retur' => $row->qty_retur,
                ]));
            }

            return DataTables::of($rollData)->toJson();
        }

        return view('cutting.report.pemakaian-roll', ['page' => 'dashboard-cutting', "subPageGroup" => "cutting-report", "subPage" => "pemakaian-roll"]);
    }

    public function detailPemakaianRoll(Request $request)
    {
        $rollIdsArr = collect(DB::connection("mysql_sb")->select("select b.id_roll, b.id_item, c.itemdesc as item_desc, c.color, c.size, no_lot, no_roll, satuan, COALESCE(retur.tgl_dok, '-') tgl_dok, b.qty_out from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb LEFT JOIN masteritem c on c.id_item = b.id_item LEFT JOIN (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting') retur on a.no_bppb = retur.no_invoice WHERE a.no_req = '" . $request->no_req . "' and b.id_item = '" . $request->id_item . "' and b.status = 'Y' GROUP BY id_roll"));

        $rollData = collect();
        foreach ($rollIdsArr as $rollId) {
            $rolls = collect(DB::select("
                SELECT
                    scanned_item.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    scanned_item.lot,
                    scanned_item.roll,
                    scanned_item.qty_in as qty,
                    scanned_item.unit,
                    COALESCE(roll_use.total_pemakaian_roll, 0) + COALESCE(piping.piping, 0) as total_pemakaian_roll,
                    roll_use.total_sisa_kain_1,
                    ROUND((CASE WHEN roll_use.total_sisa_kain != 0 THEN COALESCE(roll_use.total_sisa_kain, 0) - COALESCE(piping.piping, 0) ELSE scanned_item.qty END), 2) total_sisa_kain,
                    ROUND((CASE WHEN roll_use.total_short_roll != 0 THEN roll_use.total_short_roll ELSE piping.short_roll END), 2) total_short_roll,
                    ROUND((CASE WHEN roll_use.total_short_roll_percentage != 0 THEN roll_use.total_short_roll_percentage ELSE ((piping.short_roll/piping.qty)*100) END), 2) total_short_roll_percentage,
                    '" . $rollId->tgl_dok . "' tanggal_return
                FROM
                    scanned_item
                LEFT JOIN (
                    SELECT
                        id_roll,
                        id_item,
                        detail_item,
                        lot,
                        COALESCE ( roll_buyer, roll ) roll,
                        MAX( qty ) qty,
                        unit,
                        ROUND( SUM( total_pemakaian_roll ), 2 ) total_pemakaian_roll,
                        ROUND(MAX(qty) - SUM(total_pemakaian_roll), 2) total_sisa_kain_1,
                        ROUND(MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END), 2) total_sisa_kain,
                        ROUND((SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) - MAX(qty), 2) total_short_roll,
                        CONCAT(ROUND((((SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) - MAX(qty))/(SUM(total_pemakaian_roll) + MIN(CASE WHEN status != 'extension' AND status != 'extension complete' THEN (sisa_kain) ELSE (qty - total_pemakaian_roll) END)) * 100), 2), ' %') total_short_roll_percentage,
                        '" . $rollId->tgl_dok . "' tanggal_return
                    FROM
                        `form_cut_input_detail`
                    WHERE
                        `id_roll` IS NOT NULL
                        AND `id_roll` = '" . $rollId->id_roll . "'
                        AND form_cut_input_detail.updated_at >= DATE ( NOW()- INTERVAL 1 YEAR )
                        AND form_cut_input_detail.status in ('complete', 'need extension', 'extension complete')
                    GROUP BY
                        `id_item`,
                        `id_roll`
                    UNION ALL
                    SELECT
                        id_roll,
                        id_item,
                        detail_item,
                        lot,
                        COALESCE ( roll_buyer, roll ) roll,
                        MAX( form_cut_piece_detail.qty_pengeluaran ) qty,
                        qty_unit as unit,
                        ROUND(SUM( form_cut_piece_detail.qty_pemakaian )) total_pemakaian_roll,
                        ROUND(MAX( form_cut_piece_detail.qty_pengeluaran ) - SUM( form_cut_piece_detail.qty_pemakaian )) total_sisa_kain_1,
                        ROUND(MIN( form_cut_piece_detail.qty_sisa )) total_sisa_kain,
                        ROUND(( SUM( form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) )) total_short_roll,
                        CONCAT(ROUND( SUM( form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) / MAX( form_cut_piece_detail.qty_pengeluaran ), 2), ' %') total_short_roll_percentage,
                        '" . $rollId->tgl_dok . "' tanggal_return
                    FROM
                        `form_cut_piece_detail`
                    WHERE
                        `id_roll` IS NOT NULL
                        AND `id_roll` = '" . $rollId->id_roll . "'
                        AND form_cut_piece_detail.updated_at >= DATE ( NOW()- INTERVAL 1 YEAR )
                        AND status = 'complete'
                    GROUP BY
                        `id_item`,
                        `id_roll`
                ) roll_use on roll_use.id_roll = scanned_item.id_roll
                left join (
                    select
                        id_roll,
                        SUM(form_cut_piping.qty) qty,
                        SUM(form_cut_piping.piping) piping,
                        SUM(short_roll) as short_roll
                    from
                        form_cut_piping
                    where
                        id_roll IS NOT NULL
                        AND `id_roll` = '" . $rollId->id_roll . "'
                    group by
                        id_roll
                ) piping on piping.id_roll = scanned_item.id_roll
                where
                    scanned_item.id_roll is not null
                    AND scanned_item.`id_roll` = '" . $rollId->id_roll . "'
                GROUP BY
                    scanned_item.id_roll
            "));

            if ($rolls && $rolls->first()) {
                $rollData->push($rolls->first());
            } else {
                $rollData->push(collect([
                    "id_roll" => $rollId->id_roll,
                    "id_item" => $rollId->id_item,
                    "detail_item" => $rollId->item_desc,
                    "lot" => $rollId->no_lot,
                    "roll" => $rollId->no_roll,
                    "qty" => $rollId->qty_out,
                    "unit" => $rollId->satuan,
                    "total_pemakaian_roll" => 0,
                    "total_sisa_kain" => 0,
                    "total_short_roll" => 0,
                    "total_short_roll_percentage" => '0.00 %',
                    "tanggal_return" => $rollId->tgl_dok
                ]));
            }
        }

        return DataTables::of($rollData)->toJson();
    }

    public function totalPemakaianRoll(Request $request)
    {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date('Y-m-d');
        $dateTo = $request->dateTo ? $request->dateTo : date('Y-m-d');

        $filterQuery = "";
        if ($request->bppbno) {
            $filterQuery = " and bppbno LIKE '%" . $request->bppbno . "%'";
        }
        if ($request->no_out) {
            $filterQuery = " and b.no_bppb LIKE '%" . $request->no_out . "%'";
        }
        if ($request->bppbdate) {
            $filterQuery = " and b.bppbdate LIKE '%" . $request->bppbdate . "%'";
        }
        if ($request->no_ws) {
            $filterQuery = " and ac.kpno LIKE '%" . $request->no_ws . "%'";
        }
        if ($request->styleno) {
            $filterQuery = " and ac.styleno LIKE '%" . $request->styleno . "%'";
        }
        if ($request->buyer) {
            $filterQuery = " and ms.supplier LIKE '%" . $request->buyer . "%'";
        }
        if ($request->id_item) {
            $filterQuery = " and a.id_item LIKE '%" . $request->id_item . "%'";
        }
        if ($request->itemdesc) {
            $filterQuery = " and mi.itemdesc LIKE '%" . $request->itemdesc . "%'";
        }

        $requestRoll = DB::connection("mysql_sb")->select("
            select a.*,b.no_bppb no_out, COALESCE(total_roll,0) roll_out, ROUND(COALESCE(qty_out,0), 2) qty_out, c.no_dok no_retur, COALESCE(total_roll_ri,0) roll_retur, ROUND(COALESCE(qty_out_ri,0), 2) qty_retur from (select bppbno,bppbdate,s.supplier tujuan,ac.kpno no_ws,ac.styleno,ms.supplier buyer,a.id_item,
            REPLACE(mi.itemdesc, '\"', '\\\\\"') itemdesc,a.qty qty_req,a.unit, idws_act no_ws_aktual
            from bppb_req a inner join mastersupplier s on a.id_supplier=s.id_supplier
            inner join jo_det jod on a.id_jo=jod.id_jo
            inner join so on jod.id_so=so.id
            inner join act_costing ac on so.id_cost=ac.id
            inner join mastersupplier ms on ac.id_buyer=ms.id_supplier
            inner join masteritem mi on a.id_item=mi.id_item
            where bppbno like '%RQ-F%' and a.id_supplier = '432' and bppbdate between '" . $dateFrom . "' and '" . $dateTo . "' " . $filterQuery . "
            group by a.id_item,a.bppbno
            order by bppbdate,bppbno desc) a left join
            (select a.no_bppb,no_req,id_item,COUNT(id_roll) total_roll, sum(qty_out) qty_out,satuan from whs_bppb_h a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '" . $dateFrom . "' and '" . $dateTo . "' GROUP BY bppbno) b on b.bppbno = a.no_req inner join whs_bppb_det c on c.no_bppb = a.no_bppb where a.status != 'Cancel' and c.status = 'Y' GROUP BY a.no_bppb,no_req,id_item) b on b.no_req = a.bppbno and b.id_item = a.id_item left join
            (select a.no_dok, no_invoice no_req,id_item,COUNT(no_barcode) total_roll_ri, sum(qty_sj) qty_out_ri,satuan from (select * from whs_inmaterial_fabric where no_dok like '%RI%' and supplier = 'Production - Cutting' ) a INNER JOIN (select bppbno,bppbdate from bppb_req where bppbno like '%RQ-F%' and id_supplier = '432' and bppbdate between '" . $dateFrom . "' and '" . $dateTo . "' GROUP BY bppbno) b on b.bppbno = a.no_invoice INNER JOIN whs_lokasi_inmaterial c on c.no_dok = a.no_dok GROUP BY a.no_dok,no_invoice,id_item) c on c.no_req = a.bppbno and c.id_item = a.id_item
        ");

        $totalQtyRequest = 0;
        $totalRollIn = 0;
        $totalQtyIn = 0;
        $totalRollCutting = 0;
        $totalQtyCutting = 0;
        $totalRollBalance = 0;
        $totalQtyBalance = 0;
        $totalRollReturn = 0;
        $totalQtyReturn = 0;

        foreach ($requestRoll as $req) {
            $rollIdsArr = collect(DB::connection("mysql_sb")->select("select id_roll from whs_bppb_h a INNER JOIN whs_bppb_det b on b.no_bppb = a.no_bppb WHERE a.no_req = '" . $req->bppbno . "' and b.id_item = '" . $req->id_item . "' and b.status = 'Y' GROUP BY id_roll"));

            $rollIds = $rollIdsArr->pluck('id_roll');

            $rollIdsStr = addQuotesAround($rollIds->implode("\n"));

            $rolls = ScannedItem::selectRaw("
                    form_cut_input_detail.id_roll,
                    form_cut_input_detail.id_item,
                    form_cut_input_detail.detail_item,
                    form_cut_input_detail.lot,
                    COALESCE(form_cut_input_detail.roll_buyer, form_cut_input_detail.roll) roll,
                    MAX(form_cut_input_detail.qty) qty,
                    form_cut_input_detail.unit,
                    ROUND(SUM(form_cut_input_detail.total_pemakaian_roll) + COALESCE(piping.piping, 0), 2) total_pemakaian_roll,
                    ROUND(SUM(CASE WHEN form_cut_input_detail.short_roll < 0 THEN form_cut_input_detail.short_roll ELSE 0 END), 2) total_short_roll
                ")->leftJoin("form_cut_input_detail", "form_cut_input_detail.id_roll", "=", "scanned_item.id_roll")->leftJoin(DB::raw("
                    (
                        select
                            id_roll,
                            SUM(form_cut_piping.qty) qty,
                            SUM(form_cut_piping.piping) piping
                        from
                            form_cut_piping
                        where
                            id_roll IS NOT NULL
                            " . ($rollIdsStr ? "AND `id_roll` IN (" . $rollIdsStr . ")" : "") . "
                        group by
                            id_roll
                    ) piping
                "), "piping.id_roll", "=", "scanned_item.id_roll")->whereNotNull("scanned_item.id_roll")->whereNotNull("form_cut_input_detail.id_roll")->whereIn("scanned_item.id_roll", $rollIds)->groupBy("scanned_item.id_item", "scanned_item.id_roll")->get();

            if ($rolls->count() > 0) {
                $totalQtyRequest += $req->qty_req;
                $totalRollIn += $req->roll_out;
                $totalQtyIn += $req->qty_out;
                $totalRollCutting += $rolls->count("id_roll");
                $totalQtyCutting += (($req->unit == 'YARD' || $req->unit == 'YRD') ? round($rolls->sum("total_pemakaian_roll") * 1.0361, 2) : round($rolls->sum("total_pemakaian_roll"), 2));
                $totalRollBalance += $rolls ? $req->roll_out - $rolls->count() : $req->roll_out;
                $totalQtyBalance += $rolls ? $req->qty_out - (($req->unit == 'YARD' || $req->unit == 'YRD') ? $rolls->sum("total_pemakaian_roll") * 1.0361 : $rolls->sum("total_pemakaian_roll")) : $req->qty_out;
                $totalRollReturn += $req->roll_retur;
                $totalQtyReturn += $req->qty_retur;
            }
        }

        return array(
            "totalQtyRequest" => $totalQtyRequest,
            "totalRollIn" => $totalRollIn,
            "totalQtyIn" => $totalQtyIn,
            "totalRollCutting" => $totalRollCutting,
            "totalQtyCutting" => $totalQtyCutting,
            "totalRollBalance" => $totalRollBalance,
            "totalQtyBalance" => $totalQtyBalance,
            "totalRollReturn" => $totalRollReturn,
            "totalQtyReturn" => $totalQtyReturn
        );
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

        return Excel::download(new ExportReportCuttingSinglePage($request->dateFrom, $request->dateTo), 'Report Cutting.xlsx');
    }

    public function pemakaianRollExport(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportPemakaianKain($request->dateFrom, $request->dateTo), 'Report Detail Pemakaian Kain.xlsx');
    }

    public function detailPemakaianRollExport(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportDetailPemakaianKain($request->no_req, $request->id_item), 'Report Detail Pemakaian Kain.xlsx');
    }

    public function cuttingDaily(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $additionalQuery1 = "";

            if ($request->dateFrom) {
                $additionalQuery .= " and COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) >= '" . $request->dateFrom . "'";
                $additionalQuery1 .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), form_cut_piece.tanggal) >= '" . $request->dateFrom . "'";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) <= '" . $request->dateTo . "'";
                $additionalQuery1 .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), form_cut_piece.tanggal) <= '" . $request->dateTo . "'";
            }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                    and (
                        marker_cutting.tgl_form_cut like '%" . $request->search["value"] . "%' OR
                        marker_cutting.meja like '%" . $request->search["value"] . "%' OR
                        marker_cutting.no_form like '%" . $request->search["value"] . "%' OR
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
                    UPPER(marker_cutting.meja) meja,
                    marker_cutting.no_form,
                    marker_cutting.buyer,
                    marker_cutting.act_costing_ws,
                    marker_cutting.style,
                    marker_cutting.color,
                    marker_cutting.panel,
                    SUM((marker_cutting.form_gelar * marker_cutting.ratio) + COALESCE(marker_cutting.diff, 0)) qty
                FROM
                    (
                        SELECT
                            marker_input.kode,
                            GROUP_CONCAT(form_cut.no_form, form_cut.meja) no_form_meja,
                            form_cut.id form_cut_id,
                            form_cut.no_form,
                            form_cut.id_meja,
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
                            SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                            SUM(modify_size_qty.difference_qty) diff
                        FROM
                        marker_input
                        INNER JOIN
                            marker_input_detail on marker_input_detail.marker_id = marker_input.id
                        INNER JOIN
                            master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                        INNER JOIN
                            (
                                SELECT
                                    meja.id id_meja,
                                    meja.`name` meja,
                                    COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form_cut,
                                    form_cut_input.id_marker,
                                    form_cut_input.id,
                                    form_cut_input.no_form,
                                    form_cut_input.qty_ply,
                                    form_cut_input.total_lembar,
                                    form_cut_input.notes,
                                    SUM(form_cut_input_detail.lembar_gelaran) detail
                                FROM
                                    form_cut_input
                                    LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                WHERE
                                    form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                    AND form_cut_input.waktu_mulai is not null
                                    " . $additionalQuery . "
                                GROUP BY
                                    form_cut_input.id
                            ) form_cut on form_cut.id_marker = marker_input.kode
                        LEFT JOIN
                            modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                        where
                            (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                            AND marker_input_detail.ratio > 0
                        group by
                            marker_input.id,
                            marker_input_detail.so_det_id,
                            form_cut.tgl_form_cut,
                            form_cut.meja,
                            form_cut.id
                        UNION
                        SELECT
                            null as kode,
                            form_cut_piece.no_form no_form_meja,
                            form_cut_piece.id form_cut_id,
                            form_cut_piece.no_form,
                            '-' id_meja,
                            form_cut_piece.employee_name meja,
                            form_cut_piece.tanggal tgl_form_cut,
                            form_cut_piece.buyer,
                            form_cut_piece.act_costing_id,
                            form_cut_piece.act_costing_ws,
                            form_cut_piece.style,
                            form_cut_piece.color,
                            form_cut_piece.panel,
                            form_cut_piece.cons_ws,
                            'PCS' unit,
                            form_cut_piece_detail_size.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            1 ratio,
                            'PCS' notes,
                            SUM(form_cut_piece_detail_size.qty) marker_gelar,
                            SUM(form_cut_piece_detail_size.qty) spreading_gelar,
                            SUM(form_cut_piece_detail_size.qty) form_gelar,
                            0 diff
                        FROM
                            form_cut_piece
                            INNER JOIN
                                form_cut_piece_detail on form_cut_piece_detail.form_id = form_cut_piece.id
                            INNER JOIN
                                form_cut_piece_detail_size on form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                            INNER JOIN
                                master_sb_ws on master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                        where
                            form_cut_piece_detail_size.qty > 0
                            " . $additionalQuery1 . "
                        group by
                            form_cut_piece_detail_size.so_det_id,
                            form_cut_piece.tanggal,
                            form_cut_piece.employee_name,
                            form_cut_piece.id
                    ) marker_cutting
                GROUP BY
                    marker_cutting.id_meja,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.tgl_form_cut,
                    marker_cutting.form_cut_id
                ORDER BY
                    marker_cutting.id_meja,
                    marker_cutting.tgl_form_cut,
                    marker_cutting.panel,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.form_cut_id
            ");

            return DataTables::of($reportCutting)->toJson();
        }

        return view('cutting.report.report-cutting-output-daily', ['page' => 'dashboard-cutting', "subPageGroup" => "cutting-report", "subPage" => "cutting-daily"]);
    }

    public function totalCuttingDaily(Request $request)
    {
        $additionalQuery = "";
        $additionalQuery1 = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) >= '" . $request->dateFrom . "'";
            $additionalQuery1 .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), form_cut_piece.tanggal) >= '" . $request->dateFrom . "'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) <= '" . $request->dateTo . "'";
            $additionalQuery1 .= " and COALESCE(DATE(form_cut_piece.updated_at), DATE(form_cut_piece.created_at), form_cut_piece.tanggal) <= '" . $request->dateTo . "'";
        }

        $tanggalFilter = "";
        $tanggalFilter1 = "";
        if ($request->tanggal) {
            $tanggalFilter = " and form_cut.tgl_form_cut LIKE '%" . $request->tanggal . "%'";
            $tanggalFilter1 = " and form_cut_piece.tgl_form_cut LIKE '%" . $request->tanggal . "%'";
        }
        $noMejaFilter = "";
        $noMejaFilter1 = "";
        if ($request->noMeja) {
            $noMejaFilter = " and form_cut.meja LIKE '%" . $request->noMeja . "%'";
            $noMejaFilter1 = " and form_cut_piece.employee_name LIKE '%" . $request->noMeja . "%'";
        }
        $buyerFilter = "";
        $buyerFilter1 = "";
        if ($request->buyer) {
            $buyerFilter = " and marker_input.buyer LIKE '%" . $request->buyer . "%'";
            $buyerFilter1 = " and form_cut_piece.buyer LIKE '%" . $request->buyer . "%'";
        }
        $noFormFilter = "";
        $noFormFilter1 = "";
        if ($request->noForm) {
            $noFormFilter = " and form_cut.no_form LIKE '%" . $request->noForm . "%'";
            $noFormFilter1 = " and form_cut_piece.no_form LIKE '%" . $request->noForm . "%'";
        }
        $wsFilter = "";
        $wsFilter1 = "";
        if ($request->ws) {
            $wsFilter = " and marker_input.act_costing_ws LIKE '%" . $request->ws . "%'";
            $wsFilter1 = " and form_cut_piece.act_costing_ws LIKE '%" . $request->ws . "%'";
        }
        $styleFilter = "";
        $styleFilter1 = "";
        if ($request->style) {
            $styleFilter = " and marker_input.style LIKE '%" . $request->style . "%'";
            $styleFilter1 = " and form_cut_piece.style LIKE '%" . $request->style . "%'";
        }
        $colorFilter = "";
        $colorFilter1 = "";
        if ($request->color) {
            $colorFilter = " and marker_input.color LIKE '%" . $request->color . "%'";
            $colorFilter1 = " and form_cut_piece.color LIKE '%" . $request->color . "%'";
        }
        $panelFilter = "";
        $panelFilter1 = "";
        if ($request->panel) {
            $panelFilter = " and marker_input.panel LIKE '%" . $request->panel . "%'";
            $panelFilter1 = " and form_cut_piece.panel LIKE '%" . $request->panel . "%'";
        }

        $reportCutting = collect(
            DB::select("
                SELECT
                    marker_cutting.tgl_form_cut,
                    UPPER(marker_cutting.meja) meja,
                    marker_cutting.no_form,
                    marker_cutting.buyer,
                    marker_cutting.act_costing_ws,
                    marker_cutting.style,
                    marker_cutting.color,
                    marker_cutting.panel,
                    SUM((marker_cutting.form_gelar * marker_cutting.ratio) + COALESCE(marker_cutting.diff, 0)) qty
                FROM
                    (
                        SELECT
                            marker_input.kode,
                            GROUP_CONCAT(form_cut.no_form, form_cut.meja) no_form_meja,
                            form_cut.id form_cut_id,
                            form_cut.no_form,
                            form_cut.id_meja,
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
                            SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                            SUM(modify_size_qty.difference_qty) diff
                        FROM
                        marker_input
                        INNER JOIN
                            marker_input_detail on marker_input_detail.marker_id = marker_input.id
                        INNER JOIN
                            master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                        INNER JOIN
                            (
                                SELECT
                                    meja.id id_meja,
                                    meja.`name` meja,
                                    COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form_cut,
                                    form_cut_input.id_marker,
                                    form_cut_input.id,
                                    form_cut_input.no_form,
                                    form_cut_input.qty_ply,
                                    form_cut_input.total_lembar,
                                    form_cut_input.notes,
                                    SUM(form_cut_input_detail.lembar_gelaran) detail
                                FROM
                                    form_cut_input
                                    LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                WHERE
                                    form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                    AND form_cut_input.waktu_mulai is not null
                                    AND form_cut_input.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH)
                                    AND form_cut_input_detail.updated_at >= DATE(NOW()-INTERVAL 6 MONTH)
                                    " . $additionalQuery . "
                                GROUP BY
                                    form_cut_input.id
                            ) form_cut on form_cut.id_marker = marker_input.kode
                        LEFT JOIN
                            modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                        where
                            (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                            AND marker_input_detail.ratio > 0
                            " . $tanggalFilter . "
                            " . $noMejaFilter . "
                            " . $buyerFilter . "
                            " . $wsFilter . "
                            " . $styleFilter . "
                            " . $colorFilter . "
                            " . $panelFilter . "
                        group by
                            marker_input.id,
                            marker_input_detail.so_det_id,
                            form_cut.tgl_form_cut,
                            form_cut.meja,
                            form_cut.id
                        UNION
                        SELECT
                            null as kode,
                            form_cut_piece.no_form no_form_meja,
                            form_cut_piece.id form_cut_id,
                            form_cut_piece.no_form,
                            '-' id_meja,
                            form_cut_piece.employee_name meja,
                            form_cut_piece.tanggal tgl_form_cut,
                            form_cut_piece.buyer,
                            form_cut_piece.act_costing_id,
                            form_cut_piece.act_costing_ws,
                            form_cut_piece.style,
                            form_cut_piece.color,
                            form_cut_piece.panel,
                            form_cut_piece.cons_ws,
                            'PCS' unit,
                            form_cut_piece_detail_size.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            1 ratio,
                            'PCS' notes,
                            SUM(form_cut_piece_detail_size.qty) marker_gelar,
                            SUM(form_cut_piece_detail_size.qty) spreading_gelar,
                            SUM(form_cut_piece_detail_size.qty) form_gelar,
                            null diff
                        FROM
                            form_cut_piece
                            INNER JOIN
                                form_cut_piece_detail on form_cut_piece_detail.form_id = form_cut_piece.id
                            INNER JOIN
                                form_cut_piece_detail_size on form_cut_piece_detail_size.form_detail_id = form_cut_piece_detail.id
                            INNER JOIN
                                master_sb_ws on master_sb_ws.id_so_det = form_cut_piece_detail_size.so_det_id
                        where
                            form_cut_piece_detail_size.qty > 0
                            " . $additionalQuery1 . "
                            " . $tanggalFilter1 . "
                            " . $noMejaFilter1 . "
                            " . $buyerFilter1 . "
                            " . $wsFilter1 . "
                            " . $styleFilter1 . "
                            " . $colorFilter1 . "
                            " . $panelFilter1 . "
                        group by
                            form_cut_piece_detail.id,
                            form_cut_piece_detail_size.so_det_id,
                            form_cut_piece.tanggal,
                            form_cut_piece.id
                    ) marker_cutting
                GROUP BY
                    marker_cutting.id_meja,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.panel,
                    marker_cutting.tgl_form_cut,
                    marker_cutting.form_cut_id
                ORDER BY
                    marker_cutting.id_meja,
                    marker_cutting.tgl_form_cut,
                    marker_cutting.panel,
                    marker_cutting.act_costing_id,
                    marker_cutting.color,
                    marker_cutting.form_cut_id
            ")
        );

        return array(
            "totalCuttingDaily" => $reportCutting->sum("qty")
        );
    }

    public function cuttingDailyExport(Request $request)
    {
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportReportCuttingDaily($request->dateFrom, $request->dateTo), 'Report Cutting Output Daily.xlsx');
    }

    public function trackCuttingOutput(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == "supplier") {
                $suppliersQuery = DB::connection('mysql_sb')->table('mastersupplier')->selectRaw('Id_Supplier as id, Supplier as name')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('mastersupplier.tipe_sup', 'C')->where('status', '!=', 'CANCEL')->where('type_ws', 'STD')->where('cost_date', '>=', '2023-01-01');
                $suppliers = $suppliersQuery->orderBy('Supplier', 'ASC')->groupBy('Id_Supplier', 'Supplier')->get();

                return $suppliers;
            }

            if ($request->type == "order") {
                $orderSql = DB::connection('mysql_sb')->table('act_costing')->selectRaw('
                        id as id_ws,
                        kpno as no_ws
                    ')->where('status', '!=', 'CANCEL')->where('type_ws', 'STD')->where('cost_date', '>=', '2023-01-01');
                if ($request->supplier) {
                    $orderSql->where('id_buyer', $request->supplier);
                }
                $orders = $orderSql->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

                return $orders;
            }
        }

        $dateFromFilter = $request->dateFromFilter;
        $dateToFilter = $request->dateToFilter;
        $selectedSupplier = $request->selectedSupplier;
        $selectedOrder = $request->selectedOrder;
        $colorFilter = $request->colorFilter;
        $panelFilter = $request->panelFilter;
        $mejaFilter = $request->mejaFilter;
        $sizeFilter = $request->sizeFilter;
        $groupBy = $request->groupBy;

        return view('cutting.report.track-cutting-output', ["subPageGroup" => "cutting-report", "subPage" => "cutting-track", "page" => "dashboard-cutting", "dateFromFilter" => $dateFromFilter, "dateToFilter" => $dateToFilter, "selectedSupplier" => $selectedSupplier, "selectedOrder" => $selectedOrder, "colorFilter" => $colorFilter, "panelFilter" => $panelFilter, "mejaFilter" => $mejaFilter, "sizeFilter" => $sizeFilter, "groupBy" => $groupBy]);
    }

    public function cuttingOrderOutputExport(Request $request)
    {
        ini_set("max_execution_time", 36000);

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;
        $groupBy = $request->groupBy;
        $order = $request->order;
        $buyer = $request->buyer;

        return Excel::download(new CuttingOrderOutputExport($dateFrom, $dateTo, $groupBy, $order, $buyer), 'order_output.xlsx');
    }



    public function report_cutting_mutasi_fabric(Request $request)
    {

        $start_date = $request->input('start_date'); // example: 9 (September)
        $end_date = $request->input('end_date'); // example: 2025

        $bulan_awal = date('n', strtotime($start_date)); // Returns month as number without leading zero (e.g., 9)
        $tahun_awal = date('Y', strtotime($start_date)); // Returns full year (e.g., 2025)

        $bulan_akhir = date('n', strtotime($end_date)); // Returns month as number without leading zero (e.g., 9)
        $tahun_akhir = date('Y', strtotime($end_date)); // Returns full year (e.g., 2025)

        $today = date('Y-m-d');
        $month = date('m');
        $year = date('Y');

        if ($request->ajax()) {
            // ✅ If bulan or tahun is missing, return no data
            if ($start_date === null || $end_date === null) {
                return response()->json(['data' => []]);
            } else {
                $rawData = DB::select("WITH gk_out_sa as (
SELECT
id_roll,
id_item,
id_jo,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ) AS qty_out,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
ifnull(no_ws_aktual,no_ws) ws
from signalbit_erp.whs_bppb_h a
inner join signalbit_erp.whs_bppb_det b on a.no_bppb = b.no_bppb
where tgl_bppb >= '2026-01-01' and tgl_bppb < '$start_date' and tujuan = 'Production - Cutting' and b.status = 'Y'
group by id_roll, ws
),
gr_set_sa as (
select
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN b.qty_pakai * 0.9144
            ELSE b.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
a.act_costing_ws as ws
from form_cut_reject a
inner join form_cut_reject_barcode b on a.id = b.form_id
left join scanned_item s on b.barcode = s.id_roll
where b.created_at >= '2026-01-01 00:00:00' and b.created_at < '$start_date 00:00:00'
group by barcode, ws
),
gr_set_alokasi_sa as (
SELECT
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN a.qty_pakai * 0.9144
            ELSE a.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
ws,
min(sisa_kain) sisa_kain
from form_cut_alokasi_gr_panel_barcode a
left join scanned_item s on a.barcode = s.id_roll
where a.created_at >= '2026-01-01 00:00:00' and a.created_at < '$start_date 00:00:00'
group by barcode, ws
),
gk_retur_sa as (
SELECT
no_barcode,
id_item,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ) AS qty_retur,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
a.no_ws
from signalbit_erp.whs_lokasi_inmaterial a
inner join signalbit_erp.whs_inmaterial_fabric b on a.no_dok = b.no_dok
where b.tgl_dok >= '2026-01-01' and b.tgl_dok < '$start_date' and supplier = 'Production - Cutting' and a.status = 'Y'
group by no_barcode, a.no_ws
),
cutt_sa as (
SELECT
	id_roll,
	id_item,
	roll_status,
	ROUND(SUM(qty_in) - SUM(CASE WHEN roll_status != 'latest' THEN sisa_kain ELSE 0 END), 2) qty_in,
	ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
	MIN(sisa_kain) sisa_kain,
	SUM(short_roll) as short_roll,
	unit_roll,
	act_costing_ws
FROM (
	select
		COALESCE(b.qty) qty_in,
		a.waktu_mulai,
		a.waktu_selesai,
		b.id,
		DATE_FORMAT(b.created_at, '%M') bulan,
		DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
		b.no_form_cut_input,
		UPPER(meja.name) nama_meja,
		mrk.act_costing_ws,
		master_sb_ws.buyer,
		mrk.style,
		mrk.color,
		COALESCE(b.color_act, '-') color_act,
		mrk.panel,
		master_sb_ws.qty,
		cons_ws,
		cons_marker,
		a.cons_ampar,
		a.cons_act,
		(CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
		panjang_marker,
		unit_panjang_marker,
		comma_marker,
		unit_comma_marker,
		lebar_marker,
		unit_lebar_marker,
		a.p_act panjang_actual,
		a.unit_p_act unit_panjang_actual,
		a.comma_p_act comma_actual,
		a.unit_comma_p_act unit_comma_actual,
		a.l_act lebar_actual,
		a.unit_l_act unit_lebar_actual,
		COALESCE(b.id_roll, '-') id_roll,
		b.id_item,
		b.detail_item,
		COALESCE(b.roll_buyer, b.roll) roll,
		COALESCE(b.lot, '-') lot,
		COALESCE(b.group_roll, '-') group_roll,
		(
				CASE WHEN
						b.status != 'extension' AND b.status != 'extension complete'
				THEN
						(CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
				ELSE
						'Sambungan'
				END
		) status_roll,
		COALESCE(c.qty, b.qty) qty_awal,
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
		b.sisa_tidak_bisa,
		b.reject,
		b.piping,
		ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
		ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
		ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
		b.status,
		a.operator,
		a.tipe_form_cut,
		b.created_at,
		b.updated_at,
		(CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_input a
		left join form_cut_input_detail b on a.id = b.form_cut_id
		left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
		LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at
		and d.created_at >= '2026-01-01 00:00:00' and d.created_at < '$start_date 00:00:00'
		LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '2026-01-01 00:00:00' and e.created_at < '$start_date 00:00:00'
		left join users meja on meja.id = a.no_meja
		left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
		left join scanned_item on scanned_item.id_roll = b.id_roll
	where
		(a.cancel = 'N'  OR a.cancel IS NULL)
		AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
		AND a.status = 'SELESAI PENGERJAAN'
		and b.status != 'not complete'
		and b.id_item is not null
		and b.created_at >= '2026-01-01 00:00:00' and b.created_at < '$start_date 00:00:00'
	group by
		b.id
	UNION ALL
	select
		COALESCE(form_cut_piping.qty) qty_in,
		form_cut_piping.created_at waktu_mulai,
		form_cut_piping.updated_at waktu_selesai,
		form_cut_piping.id,
		DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
		DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
		'PIPING' no_form_cut_input,
		'-' nama_meja,
		form_cut_piping.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piping.style,
		form_cut_piping.color,
		form_cut_piping.color color_act,
		form_cut_piping.panel,
		master_sb_ws.qty,
		'0' cons_ws,
		0 cons_marker,
		'0' cons_ampar,
		0 cons_act,
		form_cut_piping.cons_piping cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piping.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
		scanned_item.lot,
		'-' group_roll,
		'Piping' status_roll,
		COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
		form_cut_piping.qty qty_roll,
		form_cut_piping.unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		form_cut_piping.piping piping,
		form_cut_piping.qty_sisa sisa_kain,
		form_cut_piping.piping pemakaian_lembar,
		form_cut_piping.piping total_pemakaian_roll,
		ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
		ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
		null `status`,
		form_cut_piping.operator,
		'PIPING' tipe_form_cut,
		form_cut_piping.created_at,
		form_cut_piping.updated_at,
		(CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_piping
		LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '2026-01-01 00:00:00'
		and b.created_at < '$start_date 00:00:00'
		LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '2026-01-01 00:00:00'
        and c.created_at < '$start_date 00:00:00'
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
		left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
	where
		scanned_item.id_item is not null
		and form_cut_piping.created_at >= '2026-01-01 00:00:00' and form_cut_piping.created_at < '$start_date 00:00:00'
	group by
		form_cut_piping.id
	UNION ALL
	SELECT
		form_cut_piece_detail.qty qty_in,
		form_cut_piece.created_at waktu_mulai,
		form_cut_piece.updated_at waktu_selesai,
		form_cut_piece.id,
		DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
		DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
		form_cut_piece.no_form no_form_cut_input,
		'-' nama_meja,
		form_cut_piece.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piece.style,
		form_cut_piece.color,
		form_cut_piece.color color_act,
		form_cut_piece.panel,
		master_sb_ws.qty,
		form_cut_piece.cons_ws cons_ws,
		form_cut_piece.cons_ws cons_marker,
		'0' cons_ampar,
		0 cons_act,
		0 cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piece_detail.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
		scanned_item.lot,
		'-' group_roll,
		( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
		COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
		form_cut_piece_detail.qty qty_roll,
		form_cut_piece_detail.qty_unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		0 piping,
		form_cut_piece_detail.qty_sisa sisa_kain,
		form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
		form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
		ROUND(
		form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
		ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
		form_cut_piece_detail.STATUS `status`,
		form_cut_piece.employee_name,
		'PCS' tipe_form_cut,
		form_cut_piece.created_at,
		form_cut_piece.updated_at,
		(CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	FROM
		form_cut_piece
		LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
		LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
		LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
		LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
	WHERE
		scanned_item.id_item IS NOT NULL
		AND form_cut_piece_detail.STATUS = 'complete'
		and form_cut_piece_detail.created_at >= '2026-01-01 00:00:00' and form_cut_piece_detail.created_at < '$start_date 00:00:00'
	GROUP BY
		form_cut_piece_detail.id
) cutting
where
	cutting.id_roll is not null and cutting.id_roll != '-'
group by
	id_roll,
	act_costing_ws
order by
	created_at
),
-- Periode Transaksi
gk_out as (SELECT
id_roll,
id_item,
id_jo,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ) AS qty_out,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
ifnull(no_ws_aktual,no_ws) ws
from signalbit_erp.whs_bppb_h a
inner join signalbit_erp.whs_bppb_det b on a.no_bppb = b.no_bppb
where tgl_bppb >= '$start_date' and tgl_bppb <= '$end_date' and tujuan = 'Production - Cutting' and b.status = 'Y'
group by id_roll, ws
),
gr_set as (
select
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN b.qty_pakai * 0.9144
            ELSE b.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
a.act_costing_ws as ws
from form_cut_reject a
inner join form_cut_reject_barcode b on a.id = b.form_id
left join scanned_item s on b.barcode = s.id_roll
where b.created_at >= '$start_date 00:00:00' and b.created_at <= '$end_date 23:59:59'
group by barcode, ws
),
gr_set_alokasi as (
SELECT
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN a.qty_pakai * 0.9144
            ELSE a.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
ws,
min(sisa_kain) sisa_kain
from form_cut_alokasi_gr_panel_barcode a
left join scanned_item s on a.barcode = s.id_roll
where a.created_at >= '$start_date 00:00:00' and a.created_at <= '$end_date 23:59:59'
group by barcode, ws
),
gk_retur as (
SELECT
no_barcode,
id_item,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ) AS qty_retur,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
a.no_ws
from signalbit_erp.whs_lokasi_inmaterial a
inner join signalbit_erp.whs_inmaterial_fabric b on a.no_dok = b.no_dok
where b.tgl_dok >= '$start_date' and b.tgl_dok <= '$end_date' and supplier = 'Production - Cutting' and a.status = 'Y'
group by no_barcode, a.no_ws
),
cutt as  (
SELECT
	id_roll,
	id_item,
	roll_status,
	ROUND(SUM(qty_in) - SUM(CASE WHEN roll_status != 'latest' THEN sisa_kain ELSE 0 END), 2) qty_in,
	ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
	MIN(sisa_kain) sisa_kain,
	SUM(short_roll) as short_roll,
	unit_roll,
	act_costing_ws
FROM (
	select
		COALESCE(b.qty) qty_in,
		a.waktu_mulai,
		a.waktu_selesai,
		b.id,
		DATE_FORMAT(b.created_at, '%M') bulan,
		DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
		b.no_form_cut_input,
		UPPER(meja.name) nama_meja,
		mrk.act_costing_ws,
		master_sb_ws.buyer,
		mrk.style,
		mrk.color,
		COALESCE(b.color_act, '-') color_act,
		mrk.panel,
		master_sb_ws.qty,
		cons_ws,
		cons_marker,
		a.cons_ampar,
		a.cons_act,
		(CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
		panjang_marker,
		unit_panjang_marker,
		comma_marker,
		unit_comma_marker,
		lebar_marker,
		unit_lebar_marker,
		a.p_act panjang_actual,
		a.unit_p_act unit_panjang_actual,
		a.comma_p_act comma_actual,
		a.unit_comma_p_act unit_comma_actual,
		a.l_act lebar_actual,
		a.unit_l_act unit_lebar_actual,
		COALESCE(b.id_roll, '-') id_roll,
		b.id_item,
		b.detail_item,
		COALESCE(b.roll_buyer, b.roll) roll,
		COALESCE(b.lot, '-') lot,
		COALESCE(b.group_roll, '-') group_roll,
		(
				CASE WHEN
						b.status != 'extension' AND b.status != 'extension complete'
				THEN
						(CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
				ELSE
						'Sambungan'
				END
		) status_roll,
		COALESCE(c.qty, b.qty) qty_awal,
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
		b.sisa_tidak_bisa,
		b.reject,
		b.piping,
		ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
		ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
		ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
		b.status,
		a.operator,
		a.tipe_form_cut,
		b.created_at,
		b.updated_at,
		(CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_input a
		left join form_cut_input_detail b on a.id = b.form_cut_id
		left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
		LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at and d.created_at >= '$start_date 00:00:00' and d.created_at <= '$end_date 23:59:59'
		LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '$start_date 00:00:00' and e.created_at <= '$end_date 23:59:59'
		left join users meja on meja.id = a.no_meja
		left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
		left join scanned_item on scanned_item.id_roll = b.id_roll
	where
		(a.cancel = 'N'  OR a.cancel IS NULL)
		AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
		AND a.status = 'SELESAI PENGERJAAN'
		and b.status != 'not complete'
		and b.id_item is not null
		and b.created_at >= '$start_date 00:00:00' and b.created_at <= '$end_date 23:59:59'
	group by
		b.id
	UNION ALL
	select
		COALESCE(form_cut_piping.qty) qty_in,
		form_cut_piping.created_at waktu_mulai,
		form_cut_piping.updated_at waktu_selesai,
		form_cut_piping.id,
		DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
		DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
		'PIPING' no_form_cut_input,
		'-' nama_meja,
		form_cut_piping.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piping.style,
		form_cut_piping.color,
		form_cut_piping.color color_act,
		form_cut_piping.panel,
		master_sb_ws.qty,
		'0' cons_ws,
		0 cons_marker,
		'0' cons_ampar,
		0 cons_act,
		form_cut_piping.cons_piping cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piping.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
		scanned_item.lot,
		'-' group_roll,
		'Piping' status_roll,
		COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
		form_cut_piping.qty qty_roll,
		form_cut_piping.unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		form_cut_piping.piping piping,
		form_cut_piping.qty_sisa sisa_kain,
		form_cut_piping.piping pemakaian_lembar,
		form_cut_piping.piping total_pemakaian_roll,
		ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
		ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
		null `status`,
		form_cut_piping.operator,
		'PIPING' tipe_form_cut,
		form_cut_piping.created_at,
		form_cut_piping.updated_at,
		(CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_piping
		LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '$start_date 00:00:00' and b.created_at < '$end_date 23:59:59'
		LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '$start_date 00:00:00' and c.created_at < '$end_date 23:59:59'
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
		left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
	where
		scanned_item.id_item is not null
		and form_cut_piping.created_at >= '$start_date 00:00:00' and form_cut_piping.created_at <= '$end_date 23:59:59'
	group by
		form_cut_piping.id
	UNION ALL
	SELECT
		form_cut_piece_detail.qty qty_in,
		form_cut_piece.created_at waktu_mulai,
		form_cut_piece.updated_at waktu_selesai,
		form_cut_piece.id,
		DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
		DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
		form_cut_piece.no_form no_form_cut_input,
		'-' nama_meja,
		form_cut_piece.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piece.style,
		form_cut_piece.color,
		form_cut_piece.color color_act,
		form_cut_piece.panel,
		master_sb_ws.qty,
		form_cut_piece.cons_ws cons_ws,
		form_cut_piece.cons_ws cons_marker,
		'0' cons_ampar,
		0 cons_act,
		0 cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piece_detail.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
		scanned_item.lot,
		'-' group_roll,
		( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
		COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
		form_cut_piece_detail.qty qty_roll,
		form_cut_piece_detail.qty_unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		0 piping,
		form_cut_piece_detail.qty_sisa sisa_kain,
		form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
		form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
		ROUND(
		form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
		ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
		form_cut_piece_detail.STATUS `status`,
		form_cut_piece.employee_name,
		'PCS' tipe_form_cut,
		form_cut_piece.created_at,
		form_cut_piece.updated_at,
		(CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	FROM
		form_cut_piece
		LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
		LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
		LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
		LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
	WHERE
		scanned_item.id_item IS NOT NULL
		AND form_cut_piece_detail.STATUS = 'complete'
		and form_cut_piece_detail.created_at >= '$start_date 00:00:00' and form_cut_piece_detail.created_at <= '$end_date 23:59:59'
	GROUP BY
		form_cut_piece_detail.id
) cutting
where
	cutting.id_roll is not null and cutting.id_roll != '-'
group by
	id_roll,
	act_costing_ws
order by
	created_at
),
saldo_awal as (
SELECT
barcode,
a.id_item,
MIN(NULLIF(sisa_kain, 0)) AS min_sisa_kain,
SUM(qty_pakai) + SUM(qty_reject_set) + SUM(qty_reject_panel) + COALESCE(MIN(NULLIF(sisa_kain, 0)), 0) + SUM(qty_retur) - SUM(qty_out) as short_roll,
ws,
satuan
FROM
		(
		SELECT id_roll as barcode,id_item, qty_out, 0 as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel, 0 as sisa_kain, satuan, ws FROM gk_out_sa
		UNION ALL
		SELECT id_roll as barcode,id_item, 0 qty_out, total_pemakaian_roll as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,sisa_kain, unit_roll as satuan, act_costing_ws as ws  FROM cutt_sa
		UNION ALL
		SELECT no_barcode as barcode,id_item, 0 qty_out, 0 as qty_pakai, qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, no_ws as ws  FROM gk_retur_sa
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, 0 qty_reject_set, qty_pakai as qty_reject_panel, sisa_kain, satuan, ws  FROM gr_set_alokasi_sa
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, qty_pakai as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, ws  FROM gr_set_sa
		) a
group by barcode, ws, satuan
),
mut as (
SELECT
barcode,
a.id_item,
MIN(NULLIF(sisa_kain, 0)) AS min_sisa_kain,
SUM(qty_pakai) + SUM(qty_reject_set) + SUM(qty_reject_panel) + COALESCE(MIN(NULLIF(sisa_kain, 0)), 0) + SUM(qty_retur) - SUM(qty_out) as short_roll,
SUM(qty_out) as qty_out,
SUM(qty_pakai) as qty_pakai,
SUM(qty_retur) qty_retur,
SUM(qty_reject_set) qty_reject_set,
SUM(qty_reject_panel) qty_reject_panel,
SUM(qty_out) - SUM(qty_pakai)  - SUM(qty_retur) - SUM(qty_reject_set) - SUM(qty_reject_panel) as qty_sakhir,
SUM(sisa_kain) as sisa_kain,
ws,
satuan
FROM
		(
		SELECT id_roll as barcode,id_item, qty_out, 0 as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel, 0 as sisa_kain, satuan, ws FROM gk_out
		UNION ALL
		SELECT id_roll as barcode,id_item, 0 qty_out, total_pemakaian_roll as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,sisa_kain, unit_roll as satuan, act_costing_ws as ws  FROM cutt
		UNION ALL
		SELECT no_barcode as barcode,id_item, 0 qty_out, 0 as qty_pakai, qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, no_ws as ws  FROM gk_retur
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, 0 qty_reject_set, qty_pakai as qty_reject_panel, sisa_kain, satuan, ws  FROM gr_set_alokasi
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, qty_pakai as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, ws  FROM gr_set
		) a
group by barcode, ws, satuan
)


SELECT
mi.id_item,
mi.itemdesc,
buyer,
styleno,
mi.color,
ROUND(SUM(qty_sawal),2) as qty_sawal,
ROUND(SUM(qty_out),2) as qty_out,
ROUND(SUM(qty_pakai),2) as qty_pakai,
ROUND(SUM(qty_retur),2) as qty_retur,
ROUND(SUM(qty_reject_set),2) as qty_reject_set,
ROUND(SUM(qty_reject_panel),2) as qty_reject_panel,
ROUND(SUM(short_roll_sawal) + SUM(short_roll),2) as short_roll,
ROUND(SUM(qty_sawal) + SUM(sisa_kain),2) as saldo_akhir,
ws,
satuan
FROM
(
select id_item, SUM(short_roll) as short_roll_sawal , SUM(min_sisa_kain) as qty_sawal, 0 as qty_out, 0 as qty_pakai, 0 as qty_retur, 0 as qty_reject_set, 0 as qty_reject_panel, 0 as short_roll, 0 as sisa_kain, ws, satuan
from saldo_awal
GROUP BY id_item, ws, satuan
UNION ALL
select id_item, 0 as short_roll_sawal , 0 as qty_sawal, SUM(qty_out) as qty_out, SUM(qty_pakai) as qty_pakai, SUM(qty_retur) as qty_retur, SUM(qty_reject_set) as qty_reject_set, SUM(qty_reject_panel) as qty_reject_panel, SUM(short_roll) as short_roll, SUM(sisa_kain) as sisa_kain, ws, satuan
from mut
GROUP BY id_item, ws, satuan
) a
inner join signalbit_erp.masteritem mi on a.id_item = mi.id_item
LEFT JOIN (SELECT
						jd.id_jo,
						ac.kpno,
            supplier as buyer,
            styleno
				FROM signalbit_erp.jo_det jd
				INNER JOIN signalbit_erp.so ON jd.id_so = so.id
				INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
				WHERE jd.cancel = 'N'
				GROUP BY jd.id_jo) k on a.ws = k.kpno
GROUP BY id_item, ws, satuan

        ");

                return response()->json([
                    'data' => $rawData // ✅ simplified response
                ]);
            }
        }


        // For non-AJAX (initial page load)
        return view('cutting.report.report_mutasi_fabric', [
            'page' => 'dashboard-cutting',
            'subPageGroup' => 'cutting-report',
            'subPage' => '"cutting"]);',
            'containerFluid' => true,
        ]);
    }


    public function export_excel_report_cutting_mutasi_fabric(Request $request)
    {
        return Excel::download(new export_excel_report_cutting_mutasi_fabric($request->start_date, $request->end_date), 'Laporan_Penerimaan FG_Stok.xlsx');
    }
}
