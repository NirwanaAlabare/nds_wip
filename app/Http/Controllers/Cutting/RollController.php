<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\ScannedItem;
use App\Models\FormCutInputDetail;
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
                    lebar_marker,
                    unit_lebar_marker,
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
                    and b.status != 'not completed'
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
                lebar_marker,
                unit_lebar_marker,
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
                and b.status != 'not completed'
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

    public function sisaKainRoll(Request $request)
    {
        return view("cutting.roll.sisa-kain-roll", ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "sisa-kain-roll"]);
    }

    public function getScannedItem($id)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                mastersupplier.Supplier buyer,
                act_costing.kpno no_ws,
                act_costing.styleno style,
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
                LEFT JOIN (SELECT jo_det.* FROM jo_det GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
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
                id_roll,
                id_item,
                detail_item,
                color,
                lot,
                COALESCE(roll, roll_buyer) no_roll,
                qty,
                qty_in,
                qty_stok,
                unit,
                COALESCE(updated_at, created_at) updated_at
            ")->
            where('id_roll', $id)->
            where('id_item', $newItem[0]->id_item)->
            first();

            if ($scannedItem) {
                if (floatval($newItem[0]->qty - $scannedItem->qty_in + $scannedItem->qty) > 0 ) {
                    $scannedItem->qty_stok = $newItem[0]->qty_stok;
                    $scannedItem->qty_in = $newItem[0]->qty;
                    $scannedItem->qty = floatval($newItem[0]->qty - $scannedItem->qty_in + $scannedItem->qty);
                    $scannedItem->save();

                    return json_encode($scannedItem);
                }
            }

            return json_encode($newItem ? $newItem[0] : null);
        }

        $item = DB::connection("mysql_sb")->select("
            SELECT
                br.id id_roll,
                mi.itemdesc detail_item,
                mi.id_item,
                goods_code,
                supplier,
                bpbno_int,
                pono,
                invno,
                ac.kpno,
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
                id_roll,
                id_item,
                detail_item,
                color,
                lot,
                COALESCE(roll, roll_buyer) no_roll,
                qty,
                qty_in,
                qty_stok,
                unit,
                COALESCE(updated_at, created_at) updated_at
            ")->
            where('id_roll', $id)->
            where('id_item', $item[0]->id_item)->
            first();

            if ($scannedItem) {

                return json_encode($scannedItem);
            }

            return json_encode($item ? $item[0] : null);
        }

        return  null;
    }

    public function getSisaKainForm($id) {
        $forms = FormCutInputDetail::selectRaw("
                no_form_cut_input,
                id_roll,
                qty,
                unit,
                total_pemakaian_roll,
                short_roll,
                sisa_kain,
                COALESCE(updated_at, created_at) updated_at
            ")->
            where("id_roll", $id)->
            orderBy("id")->
            get();

        return DataTables::of($forms)->toJson();
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
