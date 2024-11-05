<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\ScannedItem;
use App\Models\FormCutInputDetail;
use App\Exports\ExportLaporanRoll;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DNS1D;
use PDF;
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

        // if ($request->ajax()) {
        //     $additionalQuery = "";

        //     if ($request->dateFrom) {
        //         $additionalQuery .= " and DATE(b.created_at) >= '" . $request->dateFrom . "'";
        //     }

        //     if ($request->dateTo) {
        //         $additionalQuery .= " and DATE(b.created_at) <= '" . $request->dateTo . "'";
        //     }

        //     $keywordQuery = "";
        //     if ($request->search["value"]) {
        //         $keywordQuery = "
        //             and (
        //                 act_costing_ws like '%" . $request->search["value"] . "%' OR
        //                 DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
        //             )
        //         ";
        //     }

        //     $data_pemakaian = DB::select("
        //         select
        //             DATE_FORMAT(b.updated_at, '%M) bulan,
        //             DATE_FORMAT(b.updated_at, '%d-%m-%Y) tgl_input,
        //             b.no_form_cut_input,
        //             UPPER(meja.name) nama_meja,
        //             act_costing_ws,
        //             buyer,
        //             style,
        //             color,
        //             b.color_act,
        //             panel,
        //             master_sb_ws.qty,
        //             cons_ws,
        //             cons_marker,
        //             a.cons_ampar,
        //             a.cons_act,
        //             COALESCE(a.cons_pipping, cons_piping) cons_piping,
        //             panjang_marker,
        //             unit_panjang_marker,
        //             comma_marker,
        //             unit_comma_marker,
        //             lebar_marker,
        //             unit_lebar_marker,
        //             a.p_act panjang_actual,
        //             a.unit_p_act unit_panjang_actual,
        //             a.comma_p_act comma_actual,
        //             a.unit_comma_p_act unit_comma_actual,
        //             a.l_act lebar_actual,
        //             a.unit_l_actual unit_lebar_actual,
        //             COALESCE(id_roll, '-') id_roll,
        //             id_item,
        //             detail_item,
        //             COALESCE(b.roll_buyer, b.roll) roll,
        //             COALESCE(b.lot, '-') lot,
        //             COALESCE(b.group_roll, '-') group_roll,
        //             b.qty qty_roll,
        //             b.unit unit_roll,
        //             COALESCE(b.berat_amparan, '-') berat_amparan,
        //             b.est_amparan,
        //             b.lembar_gelaran,
        //             mrk.total_ratio,
        //             (mrk.total_ratio * b.lembar_gelaran) qty_cut,
        //             b.average_time,
        //             b.sisa_gelaran,
        //             b.sambungan,
        //             b.sambungan_roll,
        //             b.kepala_kain,
        //             b.lembar_gelaran,
        //             b.sisa_tidak_bisa,
        //             b.reject,
        //             b.piping,
        //             COALESCE(b.sisa_kain, 0) sisa_kain,
        //             b.pemakaian_lembar,
        //             b.total_pemakaian_roll,
        //             b.short_roll,
        //             CONCAT(ROUND(((b.short_roll / b.qty) * 100), 2), ' %') short_roll_percentage,
        //             a.operator
        //         from
        //             form_cut_input a
        //             left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
        //             left join users meja on meja.id = a.no_meja
        //             left join marker_input mrk on a.id_marker = mrk.kode
        //         where
        //             (a.cancel = 'N'  OR a.cancel IS NULL)
	    //             AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
        //             and b.status != 'not completed'
        //             and id_item is not null
        //             " . $additionalQuery . "
        //             " . $keywordQuery . "
        //         group by
        //             b.id
        //         order by
        //             a.waktu_mulai asc,
        //             b.id asc
        //     ");

        //     return DataTables::of($data_pemakaian)->toJson();
        // }

        return view('cutting.roll.roll', ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "lap-pemakaian"]);
    }

    public function pemakaianRollData(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        $additionalQuery = "";
        $additionalQuery1 = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery1 .= " and form_cut_piping.created_at >= '" . $request->dateFrom . " 00:00:00'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery1 .= " and form_cut_piping.created_at <= '" . $request->dateTo . " 23:59:59'";
        }

        if ($request->supplier) {
            $additionalQuery .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery1 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
        }

        if ($request->id_ws) {
            $additionalQuery .= " and mrk.act_costing_id = " . $request->id_ws . "";
            $additionalQuery1 .= " and form_cut_piping.act_costing_id = " . $request->id_ws . "";
        }

        $keywordQuery = "";
        $keywordQuery1 = "";
        if ($request->search["value"]) {
            $keywordQuery = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";

            $keywordQuery1 = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";
        }

        $data_pemakaian = DB::select("
            SELECT
                *
            FROM
                (
                SELECT
                    b.created_at waktu_mulai,
                    b.updated_at waktu_selesai,
                    b.id,
                    DATE_FORMAT( b.updated_at, '%M' ) bulan,
                    DATE_FORMAT( b.updated_at, '%d-%m-%Y' ) tgl_input,
                    b.no_form_cut_input,
                    UPPER( meja.NAME ) nama_meja,
                    mrk.act_costing_ws,
                    msb.buyer,
                    mrk.style,
                    mrk.color,
                    COALESCE ( b.color_act, '-' ) color_act,
                    mrk.panel,
                    msb.qty,
                    cons_ws,
                    cons_marker,
                    a.cons_ampar,
                    a.cons_act,
                    COALESCE ( a.cons_pipping, cons_piping ) cons_piping,
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
                    COALESCE ( id_roll, '-' ) id_roll,
                    id_item,
                    detail_item,
                    COALESCE ( b.roll_buyer, b.roll ) roll,
                    COALESCE ( b.lot, '-' ) lot,
                    COALESCE ( b.group_roll, '-' ) group_roll,
                    b.qty qty_roll,
                    b.unit unit_roll,
                    COALESCE ( b.berat_amparan, '-' ) berat_amparan,
                    b.est_amparan,
                    b.lembar_gelaran,
                    mrk.total_ratio,
                    ( mrk.total_ratio * b.lembar_gelaran ) qty_cut,
                    b.average_time,
                    b.sisa_gelaran,
                    b.sambungan,
                    b.sambungan_roll,
                    b.kepala_kain,
                    b.sisa_tidak_bisa,
                    b.reject,
                    b.piping,
                    COALESCE ( b.sisa_kain, 0 ) sisa_kain,
                    b.pemakaian_lembar,
                    b.total_pemakaian_roll,
                    b.short_roll,
                    CONCAT( ROUND((( b.short_roll / b.qty ) * 100 ), 2 ), ' %' ) short_roll_percentage,
                    a.operator
                FROM
                    form_cut_input a
                    LEFT JOIN form_cut_input_detail b ON a.no_form = b.no_form_cut_input
                    LEFT JOIN users meja ON meja.id = a.no_meja
                    LEFT JOIN (
                        SELECT
                            marker_input.*,
                            SUM( marker_input_detail.ratio ) total_ratio
                        FROM
                            marker_input
                            LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id
                        GROUP BY
                            marker_input.id
                    ) mrk ON a.id_marker = mrk.kode
                    LEFT JOIN (select id_act_cost, buyer, qty from master_sb_ws group by id_act_cost) msb ON msb.id_act_cost = mrk.act_costing_id
                WHERE
                    ( a.cancel = 'N' OR a.cancel IS NULL )
                    AND ( mrk.cancel = 'N' OR mrk.cancel IS NULL )
                    AND b.STATUS != 'not completed'
                    AND id_item IS NOT NULL
                    ".$additionalQuery."
                    ".$keywordQuery."
                GROUP BY
                    b.id
            UNION
                SELECT
                    form_cut_piping.created_at waktu_mulai,
                    form_cut_piping.updated_at waktu_selesai,
                    form_cut_piping.id,
                    DATE_FORMAT( form_cut_piping.updated_at, '%M' ) bulan,
                    DATE_FORMAT( form_cut_piping.updated_at, '%d-%m-%Y' ) tgl_input,
                    'PIPING' no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piping.act_costing_ws,
                    msb.buyer,
                    form_cut_piping.style,
                    form_cut_piping.color,
                    form_cut_piping.color color_act,
                    form_cut_piping.panel,
                    msb.qty,
                    '0' cons_ws,
                    0 cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    '0' cons_piping,
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
                    COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
                    scanned_item.lot,
                    '-' group_roll,
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
                    ROUND(( form_cut_piping.piping + form_cut_piping.qty_sisa ) - form_cut_piping.qty, 2 ) short_roll,
                    CONCAT( ROUND((( form_cut_piping.piping + form_cut_piping.qty_sisa ) - form_cut_piping.qty )/ form_cut_piping.qty * 100, 2 ), ' %' ) short_roll_percentage,
                    form_cut_piping.operator
                FROM
                    form_cut_piping
                    LEFT JOIN (select id_act_cost, buyer, qty from master_sb_ws group by id_act_cost) msb ON msb.id_act_cost = form_cut_piping.act_costing_id
                    LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piping.id_roll
                WHERE
                    id_item IS NOT NULL
                    ".$additionalQuery1."
                    ".$keywordQuery1."
                GROUP BY
                    form_cut_piping.id
                ) roll_consumption
            ORDER BY
                waktu_mulai,
                waktu_selesai
        ");

        return DataTables::of($data_pemakaian)->toJson();
    }

    public function getSupplier(Request $request)
    {
        $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            where('mastersupplier.tipe_sup', 'C')->
            where('status', '!=', 'CANCEL')->
            where('type_ws', 'STD')->
            where('cost_date', '>=', '2023-01-01')->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            get();

        return $suppliers;
    }

    public function getOrder(Request $request)
    {
        $orderSql = DB::connection('mysql_sb')->
            table('act_costing')->
            selectRaw('
                id as id_ws,
                kpno as no_ws
            ')->
            where('status', '!=', 'CANCEL')->
            where('cost_date', '>=', '2023-01-01')->
            where('type_ws', 'STD');
            if ($request->supplier) {
                $orderSql->where('id_buyer', $request->supplier);
            }
            $orders = $orderSql->
                orderBy('cost_date', 'desc')->
                orderBy('kpno', 'asc')->
                groupBy('kpno')->
                get();

        return $orders;
    }

    public function export_excel(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        return Excel::download(new ExportLaporanRoll($request->dateFrom, $request->dateTo, $request->supplier, $request->id_ws), 'Laporan pemakaian cutting '.$request->dateFrom.' - '.$request->dateTo.' ('.Carbon::now().').xlsx');
    }

    public function sisaKainRoll(Request $request)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                mastersupplier.Supplier buyer,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.item_desc detail_item,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, whs_bppb_det.no_roll) no_roll,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '".$request->id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        return view("cutting.roll.sisa-kain-roll", ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "sisa-kain-roll"]);
    }

    public function getScannedItem($id)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                mastersupplier.Supplier buyer,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.item_desc detail_item,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, whs_bppb_det.no_roll) no_roll,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '".$id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        if ($newItem) {
            $scannedItem = ScannedItem::selectRaw("
                marker_input.buyer,
                marker_input.act_costing_ws no_ws,
                marker_input.style style,
                marker_input.color color,
                scanned_item.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.lot,
                COALESCE(scanned_item.roll, scanned_item.roll_buyer) no_roll,
                scanned_item.qty,
                scanned_item.qty_in,
                scanned_item.qty_stok,
                scanned_item.unit,
                COALESCE(scanned_item.updated_at, scanned_item.created_at) updated_at
            ")->
            leftJoin('form_cut_input_detail', 'form_cut_input_detail.id_roll', '=', 'scanned_item.id_roll')->
            leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->
            leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->
            where('scanned_item.id_roll', $id)->
            where('scanned_item.id_item', $newItem[0]->id_item)->
            first();

            if ($scannedItem) {
                $scannedItem->qty_stok = $newItem[0]->qty_stok;
                $scannedItem->qty_in = $newItem[0]->qty;
                $scannedItem->qty = floatval($newItem[0]->qty - $scannedItem->qty_in + $scannedItem->qty);
                $scannedItem->save();

                return json_encode($scannedItem);
            }

            return json_encode($newItem ? $newItem[0] : null);
        }

        $item = DB::connection("mysql_sb")->select("
            SELECT
                ms.Supplier buyer,
                ac.kpno no_ws,
                ac.styleno style,
                mi.color,
                br.id id_roll,
                mi.itemdesc detail_item,
                mi.id_item,
                goods_code,
                supplier,
                bpbno_int,
                pono,
                invno,
                roll_no no_roll,
                roll_qty qty,
                lot_no lot,
                bpb.unit,
                kode_rak
            FROM
                bpb_roll br
                INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                AND brh.id_jo = bpb.id_jo
                AND brh.id_item = bpb.id_item
                INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                INNER JOIN so ON jd.id_so = so.id
                INNER JOIN act_costing ac ON so.id_cost = ac.id
                INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
            WHERE
                br.id = '" . $id . "'
                AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                LIMIT 1
        ");

        if ($item) {
            $scannedItem = ScannedItem::selectRaw("
                marker_input.buyer,
                marker_input.act_costing_ws no_ws,
                marker_input.style style,
                marker_input.color color,
                scanned_item.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.lot,
                COALESCE(scanned_item.roll, scanned_item.roll_buyer) no_roll,
                scanned_item.qty,
                scanned_item.qty_in,
                scanned_item.qty_stok,
                scanned_item.unit,
                COALESCE(scanned_item.updated_at, scanned_item.created_at) updated_at
            ")->
            leftJoin('form_cut_input_detail', 'form_cut_input_detail.id_roll', '=', 'scanned_item.id_roll')->
            leftJoin('form_cut_input', 'form_cut_input.no_form', '=', 'form_cut_input_detail.no_form_cut_input')->
            leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->
            where('scanned_item.id_roll', $id)->
            where('scanned_item.id_item', $item[0]->id_item)->
            first();

            if ($scannedItem && $scannedItem->buyer) {
                return json_encode($scannedItem);
            }

            return json_encode($item ? $item[0] : null);
        }

        return  null;
    }

    public function getSisaKainForm(Request $request) {
        $forms = FormCutInputDetail::selectRaw("
                form_cut_input.id id_form,
                no_form_cut_input,
                form_cut_input.no_cut,
                id_roll,
                MAX(qty) qty,
                unit,
                SUM(total_pemakaian_roll) total_pemakaian_roll,
                SUM(short_roll) short_roll,
                MIN(sisa_kain) sisa_kain,
                form_cut_input.status status_form,
                form_cut_input_detail.status,
                COALESCE(form_cut_input_detail.updated_at, form_cut_input_detail.created_at) updated_at
            ")->
            leftJoin("form_cut_input", "form_cut_input.no_form", "=", "form_cut_input_detail.no_form_cut_input")->
            whereRaw("(form_cut_input.status != 'SELESAI PENGERJAAN' OR (form_cut_input.status = 'SELESAI PENGERJAAN' AND form_cut_input.status != 'not complete' AND form_cut_input.status != 'extension') )")->
            where("id_roll", $request->id)->
            whereRaw("(id_roll is not null AND id_roll != '')")->
            groupBy("form_cut_input.no_form")->
            orderBy("form_cut_input_detail.id")->
            get();

        return DataTables::of($forms)->toJson();
    }

    public function printSisaKain($id)
    {
        $sbItem = DB::connection("mysql_sb")->select("
            SELECT
                masteritem.itemdesc detail_item,
                masteritem.goods_code,
                masteritem.id_item,
                CONCAT(whs_bppb_h.no_bppb, ' | ', whs_bppb_h.tgl_bppb) bppb,
                whs_bppb_h.no_req,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, '-') no_roll_buyer,
                whs_lokasi_inmaterial.kode_lok lokasi,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '".$id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        if (!$sbItem) {
            $sbItem = DB::connection("mysql_sb")->select("
                SELECT
                    mi.itemdesc detail_item,
                    mi.goods_code,
                    mi.id_item,
                    CONCAT(bpb.bpbno_int, ' | ', bpb.bpbdate) bppb,
                    '-' no_req,
                    ac.kpno no_ws,
                    ac.styleno style,
                    mi.color,
                    br.id id_roll,
                    brh.id_item,
                    br.lot_no lot,
                    br.roll_no no_roll,
                    '-' no_roll_buyer,
                    '-' lokasi,
                    br.unit,
                    br.roll_qty qty
                FROM
                    bpb_roll br
                    INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                    INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                    INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                    AND brh.id_jo = bpb.id_jo
                    AND brh.id_item = bpb.id_item
                    INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                    INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                    INNER JOIN so ON jd.id_so = so.id
                    INNER JOIN act_costing ac ON so.id_cost = ac.id
                    INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
                WHERE
                    br.id = '" . $id . "'
                    AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                    LIMIT 1
            ");
        }

        $ndsItem = ScannedItem::selectRaw("
                GROUP_CONCAT(DISTINCT form_cut_input_detail.group_roll) group_roll,
                COALESCE(scanned_item.qty, MIN(form_cut_input_detail.sisa_kain)) sisa_kain,
                scanned_item.unit,
                GROUP_CONCAT(DISTINCT CONCAT( form_cut_input.no_form, ' | ', COALESCE(form_cut_input.operator, '-')) SEPARATOR '^') AS no_form
            ")->
            leftJoin("form_cut_input_detail", "form_cut_input_detail.id_roll", "=", "scanned_item.id_roll")->
            leftJoin("form_cut_input", "form_cut_input.no_form", "=", "form_cut_input_detail.no_form_cut_input")->
            where("scanned_item.id_roll", $id)->
            orderBy("scanned_item.id", "desc")->
            groupBy("scanned_item.id_roll")->
            first();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('cutting.roll.pdf.sisa-kain-roll', ["sbItem" => ($sbItem ? $sbItem[0] : null), "ndsItem" => $ndsItem])->setPaper('a7', 'landscape');

        $path = public_path('pdf/');
        $fileName = 'Sisa_Kain_'.$id.'.pdf';
        $pdf->save($path . '/' . str_replace("/", "_", $fileName));
        $generatedFilePath = public_path('pdf/' . str_replace("/", "_", $fileName));

        return response()->download($generatedFilePath);
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
