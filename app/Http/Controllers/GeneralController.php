<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\MarkerDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Stocker;
use App\Models\MarkerInput;
use App\Models\Part;
use App\Models\CuttingPlanOutput;
use App\Models\StockerAdditional;
use App\Models\Piping;
use App\Models\MasterPiping;
use App\Models\PipingProcess;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutPiece;
use App\Models\FormCutPieceDetail;
use App\Models\FormCutReject;
use App\Models\ScannedItem;
use App\Models\LoadingLinePlan;
use App\Models\Hris\MasterEmployee;
use PDF;

class GeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
    }

    public function getNoFormCut(Request $request)
    {
        $formCuts = FormCutInput::selectRaw('form_cut_input.id as form_cut_id, form_cut_input.no_form, COUNT(stocker_input.id) as total_stocker')
            ->leftJoin('stocker_input', 'stocker_input.form_cut_id', '=', 'form_cut_input.id')
            ->whereRaw('DATE(form_cut_input.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()')
            ->groupBy('form_cut_input.id')
            ->havingRaw('COUNT(stocker_input.id) > 0')
            ->orderBy('form_cut_input.updated_at', 'desc')
            ->get();

        return $formCuts ? json_encode($formCuts) : null;
    }

    public function getFormGroup(Request $request)
    {
        $groups = Stocker::selectRaw('form_cut_input.id form_cut_id, stocker_input.group_stocker, stocker_input.shade')
            ->leftJoin('form_cut_input', 'form_cut_input.id',  '=', 'stocker_input.form_cut_id' )
            ->whereRaw('DATE(form_cut_input.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()')
            ->where('form_cut_input.id', $request->form_cut_id)
            ->groupBy('form_cut_input.id', 'stocker_input.group_stocker', 'stocker_input.shade')
            ->orderBy('stocker_input.group_stocker', 'asc')
            ->get();

        return $groups ? json_encode($groups) : null;
    }

    public function getFormStocker(Request $request)
    {
        $stockers = Stocker::selectRaw('GROUP_CONCAT(stocker_input.id) stocker_ids, form_cut_input.id form_cut_id, stocker_input.group_stocker, stocker_input.size, stocker_input.ratio, GROUP_CONCAT(stocker_input.id_qr_stocker) id_qr_stocker')
            ->leftJoin('form_cut_input', 'form_cut_input.id',  '=', 'stocker_input.form_cut_id' )
            ->whereRaw('DATE(form_cut_input.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()')
            ->where('form_cut_input.id', $request->form_cut_id)
            ->where('stocker_input.group_stocker', $request->form_group)
            ->groupBy('form_cut_input.id', 'stocker_input.group_stocker', 'stocker_input.so_det_id', 'stocker_input.ratio')
            ->orderBy('stocker_input.so_det_id', 'asc')
            ->get();

        return $stockers ? json_encode($stockers) : null;
    }

    public function getBuyers(Request $request)
    {
        $buyers = DB::select("
            SELECT
                Id_Supplier as id,
                Supplier as buyer
            FROM
                mastersupplier
                LEFT JOIN act_costing ON act_costing.id_buyer = mastersupplier.Id_Supplier
            WHERE
                tipe_sup = 'C'
                AND act_costing.cost_date > DATE_SUB( CURRENT_DATE, INTERVAL 1 YEAR )
            GROUP BY
                mastersupplier.Id_Supplier
            ORDER BY
                mastersupplier.Supplier ASC
        ");

        return $buyers ? $buyers : null;
    }

    public function getOrders(Request $request)
    {
        $orders = DB::select("select id_act_cost, ws from master_sb_ws where buyer = '" . $request->buyer . "' and tgl_kirim >= DATE_SUB( CURRENT_DATE, INTERVAL 1 YEAR ) group by id_act_cost");

        return $orders ? $orders : null;
    }

    public function getColors(Request $request)
    {
        $colors = DB::select("select color from master_sb_ws where id_act_cost = '" . $request->act_costing_id . "'group by color");

        return $colors ? $colors : null;
    }

    public function getSizes(Request $request) {
        $sizes = DB::table("master_sb_ws")->selectRaw("
                master_sb_ws.id_so_det so_det_id,
                master_sb_ws.ws no_ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END) size_dest
            ")->
            where("master_sb_ws.id_act_cost", $request->act_costing_id)->
            where("master_sb_ws.color", $request->color)->
            leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size")->
            orderBy("master_size_new.urutan")->
            get();

        return $sizes ? $sizes : null;
    }

    public function getPanelListNew(Request $request)
    {
        $panels = DB::connection('mysql_sb')->select("
                select nama_panel panel from
                    (select id_panel from bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join masteritem mi on k.id_item = mi.id_gen
                        where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and k.status = 'M'
                        and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                        group by id_panel
                    ) a
                inner join masterpanel mp on a.id_panel = mp.id
            ");

        return $panels;
    }

    public function getOrderInfo(Request $request)
    {
        $order = DB::connection('mysql_sb')->
            table('act_costing')->
            selectRaw('
                act_costing.id,
                act_costing.id_buyer,
                act_costing.kpno,
                act_costing.styleno,
                act_costing.qty order_qty,
                mastersupplier.supplier buyer,
                GROUP_CONCAT(DISTINCT so_det.color SEPARATOR ", ") colors
            ')->
            leftJoin('mastersupplier', 'mastersupplier.Id_Supplier', '=', 'act_costing.id_buyer')->
            leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->
            leftJoin('so_det', 'so_det.id_so', '=', 'so.id')->
            where('act_costing.id', $request->act_costing_id)->
            groupBy('act_costing.id')->
            first();

        return json_encode($order);
    }

    public function getColorList(Request $request)
    {
        $colors = DB::connection('mysql_sb')->select("
            select sd.color from so_det sd
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            where ac.id = '" . $request->act_costing_id . "' and sd.cancel = 'N'
            group by sd.color");

        return $colors ? $colors : null;
    }

    public function getSizeList(Request $request)
    {
        $sizeQuery = DB::table("master_sb_ws")->selectRaw("
                master_sb_ws.id_so_det so_det_id,
                master_sb_ws.ws no_ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                (CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(master_sb_ws.size, ' - ', master_sb_ws.dest) ELSE master_sb_ws.size END) size_dest,
                master_sb_ws.qty order_qty,
                COALESCE(marker_input_detail.ratio, 0) ratio,
                COALESCE(marker_input_detail.cut_qty, 0) cut_qty
            ")->
            where("master_sb_ws.id_act_cost", $request->act_costing_id)->
            where("master_sb_ws.color", $request->color)->
            leftJoin('marker_input_detail', 'marker_input_detail.so_det_id', '=', 'master_sb_ws.id_so_det')->
            leftJoin('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')->
            leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size");

        $thisMarkerDetail = MarkerDetail::where("marker_id", $request->marker_id)->count();
        if ($thisMarkerDetail > 0) {
            $sizeQuery->where("marker_input_detail.marker_id", $request->marker_id);
        }

        $sizes = $sizeQuery->groupBy("id_so_det")->orderBy("master_size_new.urutan")->get();

        return json_encode([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval(count($sizes)),
            "recordsFiltered" => intval(count($sizes)),
            "data" => $sizes
        ]);
    }

    public function getPanelList(Request $request)
    {
        $panels = DB::connection('mysql_sb')->select("
                select nama_panel panel from
                    (select id_panel from bom_jo_item k
                        inner join so_det sd on k.id_so_det = sd.id
                        inner join so on sd.id_so = so.id
                        inner join act_costing ac on so.id_cost = ac.id
                        inner join masteritem mi on k.id_item = mi.id_gen
                        where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and k.status = 'M'
                        and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                        group by id_panel
                    ) a
                inner join masterpanel mp on a.id_panel = mp.id
            ");

        $html = "<option value=''>Pilih Panel</option>";

        foreach ($panels as $panel) {
            $html .= " <option value='" . $panel->panel . "'>" . $panel->panel . "</option> ";
        }

        return $html;
    }

    public function getNumber(Request $request)
    {
        $number = DB::connection('mysql_sb')->select("
                select k.cons cons_ws, k.unit unit_cons_ws, sum(sd.qty) order_qty from bom_jo_item k
                    inner join so_det sd on k.id_so_det = sd.id
                    inner join so on sd.id_so = so.id
                    inner join act_costing ac on so.id_cost = ac.id
                    inner join masteritem mi on k.id_item = mi.id_gen
                    inner join masterpanel mp on k.id_panel = mp.id
                where ac.id = '" . $request->act_costing_id . "' and sd.color = '" . $request->color . "' and mp.nama_panel ='" . $request->panel . "' and k.status = 'M'
                and k.cancel = 'N' and sd.cancel = 'N' and so.cancel_h = 'N' and ac.status = 'confirm' and mi.mattype = 'F'
                group by sd.color, k.id_item, k.unit
                limit 1
            ");

        return json_encode($number ? $number[0] : null);
    }

    public function getCount(Request $request)
    {
        $countMarker = Marker::where('act_costing_id', $request->act_costing_id)->where('color', $request->color)->where('panel', $request->panel)->count() + 1;

        return $countMarker ? $countMarker : 1;
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
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function edit(Marker $marker, $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function update(Marker $marker, Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Marker $marker)
    {
        //
    }

    public function generateUnlockToken(Request $request) {
        if ($request->id) {
            $user = User::where("type", "admin")->where("id", $request->id)->first();

            if ($user) {
                $user->unlock_token = ($user->unlock_token ? $user->id."".Carbon::now()->format('ymd')."".(intval(substr($user->unlock_token, -1))+1) : $user->id."".Carbon::now()->format('ymd')."1");
                $user->save();
            }

            return $user->unlock_token;
        } else {
            $users = User::where("type", "admin")->get();

            if ($users->count() > 0) {
                foreach ($users as $user) {
                    $user->unlock_token = ($user->unlock_token ? $user->id."".Carbon::now()->format('ymd')."".(intval(substr($user->unlock_token, -1))+1) : $user->id."".Carbon::now()->format('ymd')."1");
                    $user->save();
                }
            }

            return $users;
        }
    }

    public function generalTools(Request $request) {
        $orders = DB::table("master_sb_ws")->
            selectRaw("
                master_sb_ws.id_act_cost act_costing_id,
                master_sb_ws.ws act_costing_ws
            ")->
            groupBy("master_sb_ws.id_act_cost")->
            orderBy("master_sb_ws.id_act_cost", "desc")->
            limit(1000)->
            get();

        return view('general.tools.tools', [
            "orders" => $orders,
        ]);
    }

    public function updateGeneralOrder(Request $request) {
        ini_set('max_execution_time', 3600); // 1 hour

        $orderInfo = DB::table("master_sb_ws")->
            selectRaw("
                master_sb_ws.buyer buyer,
                master_sb_ws.id_act_cost act_costing_id,
                master_sb_ws.ws act_costing_ws,
                master_sb_ws.styleno style,
                master_sb_ws.color,
                GROUP_CONCAT(DISTINCT marker_input.id) as marker_ids,
                GROUP_CONCAT(DISTINCT part.id) as part_ids,
                GROUP_CONCAT(DISTINCT cutting_plan_output.id) as cutting_plan_ids,
                null as stocker_ids,
                GROUP_CONCAT(DISTINCT stocker_ws_additional.id) as stocker_ws_additional_ids,
                GROUP_CONCAT(DISTINCT master_piping.id) as master_piping_ids,
                GROUP_CONCAT(DISTINCT form_cut_piping.id) as form_cut_piping_ids,
                GROUP_CONCAT(DISTINCT form_cut_reject.id) as form_cut_reject_ids,
                GROUP_CONCAT(DISTINCT loading_line_plan.id) as loading_line_plan_ids
            ")->
            leftJoin("marker_input", function ($join) {
                $join->on("marker_input.act_costing_id", "=", "master_sb_ws.id_act_cost");
                $join->on("master_sb_ws.color", 'LIKE', DB::raw('CONCAT("%", marker_input.color, "%")'));
            })->
            leftJoin("part", function ($join) {
                $join->on("part.act_costing_id", "=", "master_sb_ws.id_act_cost");
            })->
            leftJoin("cutting_plan_output", function ($join) {
                $join->on("cutting_plan_output.id_ws", "=", "master_sb_ws.id_act_cost");
                $join->on("master_sb_ws.color", 'LIKE', DB::raw('CONCAT("%", cutting_plan_output.color, "%")'));
            })->
            leftJoin("stocker_ws_additional", function ($join) {
                $join->on("stocker_ws_additional.act_costing_id", "=", "master_sb_ws.id_act_cost");
                $join->on("master_sb_ws.color", 'LIKE', DB::raw('CONCAT("%", stocker_ws_additional.color, "%")'));
            })->
            leftJoin("master_piping", function ($join) {
                $join->on("master_piping.act_costing_id", "=", "master_sb_ws.id_act_cost");
            })->
            leftJoin("form_cut_piping", function ($join) {
                $join->on("form_cut_piping.act_costing_id", "=", "master_sb_ws.id_act_cost");
                $join->on("master_sb_ws.color", 'LIKE', DB::raw('CONCAT("%", form_cut_piping.color, "%")'));
            })->
            leftJoin("form_cut_reject", function ($join) {
                $join->on("form_cut_reject.act_costing_id", "=", "master_sb_ws.id_act_cost");
                $join->on("master_sb_ws.color", 'LIKE', DB::raw('CONCAT("%", form_cut_reject.color, "%")'));
            })->
            leftJoin("loading_line_plan", function ($join) {
                $join->on("loading_line_plan.act_costing_id", "=", "master_sb_ws.id_act_cost");
                $join->on("master_sb_ws.color", 'LIKE', DB::raw('CONCAT("%", loading_line_plan.color, "%")'));
            })->
            whereIn("master_sb_ws.id_act_cost", $request->ids)->
            groupBy("master_sb_ws.id_act_cost", "master_sb_ws.color")->
            get();

        $marker = 0;
        $part = 0;
        $cuttingPlan = 0;
        $stocker = 0;
        $stockerAdditional = 0;
        $piping = 0;
        $masterPiping = 0;
        $pipingProcess = 0;
        $formCutReject = 0;
        $loadingLinePlan = 0;
        foreach ($orderInfo as $oi) {
            if (strlen($oi->marker_ids) > 0) {
                $updateData = Marker::whereRaw("id in (".$oi->marker_ids.")")->update([
                    "buyer" => $oi->buyer,
                    "act_costing_id" => $oi->act_costing_id,
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $marker++;
                }
            }

            if (strlen($oi->part_ids) > 0) {
                $updateData = Part::whereRaw("id in (".$oi->part_ids.")")->update([
                    "buyer" => $oi->buyer,
                    "act_costing_id" => $oi->act_costing_id,
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style
                ]);

                if ($updateData) {
                    $part++;
                }
            }

            if (strlen($oi->cutting_plan_ids) > 0) {
                $updateData = CuttingPlanOutput::whereRaw("id in (".$oi->cutting_plan_ids.")")->update([
                    "ws" => $oi->act_costing_ws,
                    "style" => $oi->style,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $cuttingPlan++;
                }
            }

            if ($oi->act_costing_ws && $oi->color) {
                $updateData = Stocker::where("act_costing_ws", $oi->act_costing_ws)->whereRaw("'".$oi->color."' LIKE CONCAT('%', stocker_input.color, '%')")->update([
                    "act_costing_ws" => $oi->act_costing_ws,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $stocker++;
                }
            }

            if (strlen($oi->stocker_ws_additional_ids) > 0) {
                $updateData = StockerAdditional::whereRaw("id in (".$oi->stocker_ws_additional_ids.")")->update([
                    "buyer" => $oi->buyer,
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $stockerAdditional++;
                }
            }

            if (strlen($oi->master_piping_ids) > 0) {
                $updateData = MasterPiping::whereRaw("id in (".$oi->master_piping_ids.")")->update([
                    "buyer" => $oi->buyer,
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style
                ]);

                if ($updateData) {
                    $masterPiping++;
                }
            }

            if (strlen($oi->form_cut_piping_ids) > 0) {
                $updateData = Piping::whereRaw("id in (".$oi->form_cut_piping_ids.")")->update([
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $piping++;
                }
            }

            if (strlen($oi->form_cut_reject_ids) > 0) {
                $updateData = FormCutReject::whereRaw("id in (".$oi->form_cut_reject_ids.")")->update([
                    "buyer" => $oi->buyer,
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $formCutReject++;
                }
            }

            if (strlen($oi->loading_line_plan_ids) > 0) {
                $updateData = LoadingLinePlan::whereRaw("id in (".$oi->loading_line_plan_ids.")")->update([
                    "buyer" => $oi->buyer,
                    "act_costing_ws" => $oi->act_costing_ws,
                    "style" => $oi->style,
                    "color" => $oi->color
                ]);

                if ($updateData) {
                    $loadingLinePlan++;
                }
            }
        }

        return array(
            'status' => 200,
            'message' => 'Berhasil memperbarui '.$marker.' data marker <br> Berhasil memperbarui '.$part.' data part <br> Berhasil memperbarui '.$cuttingPlan.' data cutting plan <br> Berhasil memperbarui '.$stocker.' data stocker <br> Berhasil memperbarui '.$stockerAdditional.' data stocker additional <br> Berhasil memperbarui '.$piping.' data piping <br> Berhasil memperbarui '.$masterPiping.' data master piping <br> Berhasil memperbarui '.$pipingProcess.' data piping process <br> Berhasil memperbarui '.$formCutReject.' data form cut reject <br> Berhasil memperbarui '.$loadingLinePlan.' data loading line plan',
        );
    }

    public function getScannedEmployee($id = 0)
    {
        $employee = MasterEmployee::select(
                "enroll_id",
                "employee_name",
                "status_jabatan",
                "department_name",
                "nik"
            )->
            where("enroll_id", $id)->
            first();

        return $employee;
    }

    public function getScannedItem($id = 0, Request $request)
    {
        $newItemAdditional = "";
        $itemAdditional = "";
        if ($request->unit) {
            $newItemAdditional .= " and whs_bppb_det.satuan = '".$request->unit."'";
            $itemAdditional .= " and br.unit = '".$request->unit."'";
        }

        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                whs_bppb_det.id_roll,
                whs_bppb_det.item_desc detail_item,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll roll,
                whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty,
                whs_bppb_det.satuan unit,
                bji.rule_bom,
                GROUP_CONCAT(DISTINCT so_det.id) as so_det_list,
                GROUP_CONCAT(DISTINCT so_det.size) as size_list
            FROM
                whs_bppb_det
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN (SELECT * FROM whs_lokasi_inmaterial GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                LEFT JOIN bom_jo_item bji ON bji.id_item = whs_bppb_det.id_item AND bji.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so_det ON so_det.id = bji.id_so_det
            WHERE
                whs_bppb_det.id_roll = '".$id."'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                ".$newItemAdditional."
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");
        if ($newItem) {
            $scannedItem = ScannedItem::selectRaw("
                scanned_item.id,
                scanned_item.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.color,
                scanned_item.lot,
                scanned_item.roll,
                scanned_item.roll_buyer,
                scanned_item.qty,
                scanned_item.qty_stok,
                scanned_item.qty_in,
                COALESCE(pemakaian.total_pemakaian, scanned_item.qty_pakai) qty_pakai,
                scanned_item.unit,
                scanned_item.berat_amparan,
                scanned_item.so_det_list,
                scanned_item.size_list
            ")->leftJoin(DB::raw("
                (
                    select
                        id_roll,
                        max( qty_awal ) qty_awal,
                        sum( total_pemakaian ) total_pemakaian
                    from
                        (
                            SELECT
                                id_roll,
                                max( qty ) qty_awal,
                                sum( total_pemakaian_roll + sisa_kain ) total_pemakaian
                            FROM
                                form_cut_input_detail
                            WHERE
                                id_roll = '".$id."'
                            GROUP BY
                                id_roll
                            UNION
                            SELECT
                                id_roll,
                                max( qty ) qty_awal,
                                sum( piping + qty_sisa ) total_pemakaian
                            FROM
                                form_cut_piping
                            WHERE
                                id_roll = '".$id."'
                            GROUP BY
                                id_roll
                        ) pemakaian
                    group by
                        id_roll
                ) pemakaian
            "), "pemakaian.id_roll", "=", "scanned_item.id_roll")->
            where('scanned_item.id_roll', $id)->
            where('scanned_item.id_item', $newItem[0]->id_item)->
            first();
            if ($scannedItem) {
                $scannedItemUpdate = ScannedItem::where("id_roll", $id)->first();

                $newItemQtyStok = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($newItem[0]->qty_stok * 0.9144, 2) : $newItem[0]->qty_stok;
                $newItemQty = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($newItem[0]->qty * 0.9144, 2) : $newItem[0]->qty;
                $newItemUnit = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? 'METER' : $newItem[0]->unit;

                if ($scannedItemUpdate) {
                    $scannedItemUpdate->qty_stok = $newItemQtyStok;
                    $scannedItemUpdate->qty_in = $newItemQty;
                    $scannedItemUpdate->qty = floatval(($newItemQty - $scannedItem->qty_in) + $scannedItem->qty);
                    $scannedItemUpdate->save();

                    if ($scannedItemUpdate->qty > 0) {
                        return json_encode($scannedItemUpdate);
                    }
                }

                $formCutInputDetail = FormCutInputDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();

                if ($formCutInputDetail) {
                    return "Roll sudah terpakai di form '".$formCutInputDetail->no_form_cut_input."'";
                } else {
                    $formCutPieceDetail = FormCutPieceDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();

                    if ($formCutPieceDetail) {
                        return "Roll sudah terpakai di form '".($formCutPieceDetail->formCutPiece ? $formCutPieceDetail->formCutpiece->no_form : "-")."'";
                    }
                }
            } else {
                if ($newItem[0]->unit != "PCS" || $newItem[0]->unit != "PCE") {
                    $newItemQtyStok = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD")) ? round($newItem[0]->qty_stok * 0.9144, 2) : $newItem[0]->qty_stok;
                    $newItemQty = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD")) ? round($newItem[0]->qty * 0.9144, 2) : $newItem[0]->qty;
                    $newItemUnit = (($newItem[0]->unit == "YARD" || $newItem[0]->unit == "YRD")) ? 'METER' : $newItem[0]->unit;
                } else {
                    $newItemQtyStok = $newItem[0]->qty_stok;
                    $newItemQty = $newItem[0]->qty;
                    $newItemUnit = $newItem[0]->unit;
                }

                ScannedItem::create(
                    [
                        "id_roll" => $id,
                        "id_item" => $newItem[0]->id_item,
                        "color" => '-',
                        "detail_item" => $newItem[0]->detail_item,
                        "lot" => $newItem[0]->lot,
                        "roll" => $newItem[0]->roll,
                        "roll_buyer" => $newItem[0]->roll_buyer,
                        "qty" => $newItemQty,
                        "qty_stok" => $newItemQtyStok,
                        "qty_in" => $newItemQty,
                        "qty_pakai" => 0,
                        "unit" => $newItemUnit,
                        "rule_bom" => $newItem[0]->rule_bom,
                        "so_det_list" => $newItem[0]->so_det_list,
                        "size_list" => $newItem[0]->size_list
                    ]
                );
            }

            return json_encode($newItem ? $newItem[0] : null);
        }

        $item = DB::connection("mysql_sb")->select("
            SELECT
                br.id id_roll,
                mi.id_item,
                mi.itemdesc detail_item,
                goods_code,
                supplier,
                bpbno_int,
                pono,
                invno,
                ac.kpno,
                roll_no roll,
                roll_qty qty,
                lot_no lot,
                bpb.unit,
                kode_rak
            FROM
                bpb_roll br
                INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                INNER JOIN bpb ON brh.bpbno = bpb.bpbno AND brh.id_jo = bpb.id_jo AND brh.id_item = bpb.id_item
                INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                INNER JOIN so ON jd.id_so = so.id
                INNER JOIN act_costing ac ON so.id_cost = ac.id
                INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
            WHERE
                br.id = '" . $id . "'
                AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                ".$itemAdditional."
            GROUP BY
                br.id
            LIMIT 1
        ");
        if ($item) {
            $scannedItem = ScannedItem::where('id_roll', $id)->where('id_item', $item[0]->id_item)->first();

            if ($scannedItem) {

                $scannedItemUpdate = ScannedItem::where("id_roll", $id)->first();

                if ($newItem[0]->unit != "PCS" || $newItem[0]->unit != "PCE") {
                    $itemQtyStok = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($item[0]->qty_stok * 0.9144, 2) : $item[0]->qty_stok;
                    $itemQty = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($item[0]->qty * 0.9144, 2) : $item[0]->qty;
                    $itemUnit = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? 'METER' : $item[0]->unit;
                } else {
                    $newItemQtyStok = $newItem[0]->qty_stok;
                    $newItemQty = $newItem[0]->qty;
                    $newItemUnit = $newItem[0]->unit;
                }

                if ($scannedItemUpdate) {
                    $scannedItemUpdate->qty_stok = $itemQtyStok;
                    $scannedItemUpdate->qty_in = $itemQty;
                    $scannedItemUpdate->qty = floatval(($itemQty - $scannedItem->qty_in) + $scannedItem->qty);
                    $scannedItemUpdate->save();

                    if ($scannedItemUpdate->qty > 0) {
                        return json_encode($scannedItemUpdate);
                    }
                }

                $formCutInputDetail = FormCutInputDetail::where("id_roll", $id)->orderBy("updated_at", "desc")->first();

                if ($formCutInputDetail) {
                    return "Roll sudah terpakai di form '".$formCutInputDetail->no_form_cut_input."'";
                }
            } else {
                $itemQtyStok = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD")) ? round($item[0]->qty_stok * 0.9144, 2) : $item[0]->qty_stok;
                $itemQty = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD")) ? round($item[0]->qty * 0.9144, 2) : $item[0]->qty;
                $itemUnit = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD")) ? 'METER' : $item[0]->unit;

                $itemData = ScannedItem::create(
                    [
                        "id_roll" => $id,
                        "id_item" => $item[0]->id_item,
                        "color" => '-',
                        "detail_item" => $item[0]->detail_item,
                        "lot" => $item[0]->lot,
                        "roll" => $item[0]->roll,
                        "roll_buyer" => $item[0]->roll_buyer,
                        "qty" => $itemQty,
                        "qty_stok" => $itemQtyStok,
                        "qty_in" => $itemQty,
                        "qty_pakai" => 0,
                        "unit" => $itemUnit
                    ]
                );
            }

            return json_encode($item ? $item[0] : null);
        }

        return  null;
    }

    public function getItem(Request $request) {
        $additional = "";
        if ($request->unit) {
            $additional .= " and k.unit = '".$request->unit."'";
        }

        $items = DB::connection("mysql_sb")->select("
            select ac.id,ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno, mi.id_item, mi.itemdesc, k.unit, GROUP_CONCAT(DISTINCT sd.size ORDER BY sd.id ASC) sizes from jo_det jd
            inner join (select * from so where so_date >= '2023-01-01') so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join bom_jo_item k on jd.id_jo = k.id_jo
            inner join masteritem mi on k.id_item = mi.id_gen
            left join so_det sd on sd.id = k.id_so_det
            where jd.cancel = 'N' and k.cancel = 'N' and mi.Mattype = 'F' and ac.id = '".$request->act_costing_id."'
            ".$additional."
            group by id_cost, k.id_item
        ");

        return json_encode($items ? $items : null);
    }
}
