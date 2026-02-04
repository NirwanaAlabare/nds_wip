<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Marker\Marker;
use App\Models\Marker\MarkerDetail;
use App\Models\SignalBit\DefectInOut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Stocker\Stocker;
use App\Models\MarkerInput;
use App\Models\Part\Part;
use App\Models\Part\PartDetail;
use App\Models\Part\PartDetailItem;
use App\Models\CuttingPlanOutput;
use App\Models\Stocker\StockerAdditional;
use App\Models\Cutting\Piping;
use App\Models\MasterPiping;
use App\Models\PipingProcess;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Cutting\FormCutPiece;
use App\Models\Cutting\FormCutPieceDetail;
use App\Models\Cutting\FormCutReject;
use App\Models\Cutting\ScannedItem;
use App\Models\Dc\LoadingLinePlan;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\RejectIn;
use App\Models\Hris\MasterEmployee;
use App\Services\GeneralService;
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
        $formCuts = collect(
            DB::select("
                    SELECT
                        form_cut_input.id as form_cut_id, form_cut_input.no_form, COUNT(stocker_input.id) total_stocker, 'normal' type, form_cut_input.updated_at timestamp
                    FROM
                        form_cut_input
                        LEFT JOIN stocker_input ON stocker_input.form_cut_id = form_cut_input.id
                    WHERE
                        DATE(form_cut_input.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
                    GROUP BY
                        form_cut_input.id
                UNION ALL
                    SELECT
                        form_cut_reject.id as form_cut_id, form_cut_reject.no_form, COUNT(stocker_input.id) total_stocker, 'reject' type, form_cut_reject.updated_at timestamp
                    FROM
                        form_cut_reject
                        LEFT JOIN stocker_input ON stocker_input.form_reject_id = form_cut_reject.id
                    WHERE
                        DATE(form_cut_reject.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
                    GROUP BY
                        form_cut_reject.id
                UNION ALL
                    SELECT
                        form_cut_piece.id as form_cut_id, form_cut_piece.no_form, COUNT(stocker_input.id) total_stocker, 'piece' type, form_cut_piece.updated_at timestamp
                    FROM
                        form_cut_piece
                        LEFT JOIN stocker_input ON stocker_input.form_piece_id = form_cut_piece.id
                    WHERE
                        DATE(form_cut_piece.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()
                    GROUP BY
                        form_cut_piece.id
                ORDER BY
                    timestamp ASC
            "));

        return $formCuts ? json_encode($formCuts) : null;
    }

    public function getFormGroup(Request $request)
    {
        $formType = 'stocker_input.form_cut_id';
        switch ($request->form_type) {
            case 'reject' :
                $formType = 'stocker_input.form_reject_id';
                break;
            case 'piece' :
                $formType = 'stocker_input.form_piece_id';
                break;
            default :
                $formType = 'stocker_input.form_cut_id';
                break;
        }

        $groups = Stocker::selectRaw($formType.' form_cut_id, stocker_input.group_stocker, stocker_input.shade')
            ->whereRaw('DATE(stocker_input.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()')
            ->whereRaw($formType.' = "'.$request->form_cut_id.'"')
            ->groupByRaw($formType.', stocker_input.group_stocker, stocker_input.shade')
            ->orderBy('stocker_input.group_stocker', 'asc')
            ->get();

        return $groups ? json_encode($groups) : null;
    }

    public function getFormStocker(Request $request)
    {
        $formType = 'stocker_input.form_cut_id';
        switch ($request->form_type) {
            case 'reject' :
                $formType = 'stocker_input.form_reject_id';
                break;
            case 'piece' :
                $formType = 'stocker_input.form_piece_id';
                break;
            default :
                $formType = 'stocker_input.form_cut_id';
                break;
        }

        $stockers = Stocker::selectRaw('GROUP_CONCAT(stocker_input.id) stocker_ids, '.$formType.' form_cut_id, stocker_input.group_stocker, stocker_input.size, stocker_input.ratio, GROUP_CONCAT(stocker_input.id_qr_stocker) id_qr_stocker')
            ->whereRaw('DATE(stocker_input.updated_at) between DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND CURDATE()')
            ->whereRaw($formType.' = "'.$request->form_cut_id.'"')
            ->where('stocker_input.group_stocker', $request->form_group)
            ->groupByRaw($formType.', stocker_input.group_stocker, stocker_input.so_det_id, stocker_input.ratio')
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
        $colors = DB::connection("mysql_sb")->select("select color from so_det left join so on so.id = so_det.id_so left join act_costing on act_costing.id = so.id_cost where act_costing.id = '" . $request->act_costing_id . "' group by color");

        return $colors ? $colors : null;
    }

    public function getSizes(Request $request) {
        $sizes = DB::connection("mysql_sb")->table("so_det")->selectRaw("
                so_det.id as so_det_id,
                act_costing.kpno no_ws,
                so_det.color,
                so_det.size,
                so_det.dest,
                (CASE WHEN so_det.dest IS NOT NULL AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) size_dest
            ")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_size_new", "master_size_new.size", "=", "so_det.size")->
            where("act_costing.id", $request->act_costing_id)->
            where("so_det.color", $request->color)->
            groupBy("so_det.id")->
            orderBy("master_size_new.urutan")->
            get();

        return $sizes ? $sizes : null;
    }

    public function getPos(Request $request) {
        $pos = DB::table("ppic_master_so")->selectRaw("
                ppic_master_so.id,
                ppic_master_so.po as po
            ")
            ->leftJoin('signalbit_erp.so_det', 'so_det.id', '=', 'ppic_master_so.id_so_det')
            ->leftJoin('signalbit_erp.so', 'so.id', '=', 'so_det.id_so')
            ->leftJoin('signalbit_erp.act_costing', 'act_costing.id', '=', 'so.id_cost')
            ->leftJoin('signalbit_erp.mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('signalbit_erp.master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('signalbit_erp.masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->where('so_det.cancel', '!=', 'Y')
            ->where('act_costing.id', $request->act_costing_id)
            ->where('so_det.color', $request->color)
            ->where('so_det.id', $request->so_det_id)
            ->groupBy('ppic_master_so.id')
            ->get();

        $pos->push((object)[
            'id' => null,
            'po' => 'GUDANG_STOK',
        ]);

        return $pos ? $pos : null;
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
            where("master_sb_ws.color", $request->color);
            if ($request->size_list) {
                $sizeQuery->whereRaw("master_sb_ws.size in (".addQuotesAround(str_replace(", ", "\n", $request->size_list)).")");
            }
            $sizeQuery->leftJoin('marker_input_detail', 'marker_input_detail.so_det_id', '=', 'master_sb_ws.id_so_det')->
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
     * @param  \App\Models\Marker\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marker\Marker  $marker
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
     * @param  \App\Models\Marker\Marker  $marker
     * @return \Illuminate\Http\Response
     */
    public function update(Marker $marker, Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marker\Marker  $marker
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

    public function updateMasterSbWs(GeneralService $generalService) {
        return $generalService->updateMasterSbWs();
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

    public function getPartItemList(Request $request)
    {
        if ($request->act_costing_id) {
            $items = DB::connection("mysql_sb")->select("
                select bom_jo_item.id, masteritem.itemdesc from bom_jo_item
                left join jo_det on jo_det.id_jo = bom_jo_item.id_jo
                left join so on so.id = jo_det.id_so
                left join act_costing on act_costing.id = so.id_cost
                left join masteritem on bom_jo_item.id_item = masteritem.id_item
                where act_costing.id = '".$request->act_costing_id."' and bom_jo_item.`status` = 'P' and matclass != 'CMT'
                group by bom_jo_item.id_item
            ");

            return $items;
        }

        return null;
    }

    public function getScannedItem($id = 0, Request $request)
    {
        $newItemAdditional = "";
        $itemAdditional = "";
        if ($request->unit) {
            $newItemAdditional .= " and whs_bppb_det.satuan = '".$request->unit."'";
            $itemAdditional .= " and br.unit = '".$request->unit."'";
        }

        if ($request->act_costing_id) {
            $newItemAdditional .= " and (act_costing.id = '".$request->act_costing_id."' or whs_bppb_h.no_ws_aktual = '".$request->act_costing_ws."')";
            $itemAdditional .= " and ac.id = '".$request->act_costing_id."'";
        }

        // if ($request->color) {
        //     $newItemAdditional .= " and masteritem.color = '".$request->color."'";
        // }

        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                id_roll,
                id_jo,
                detail_item,
                detail_item_color,
                detail_item_size,
                id_item,
                lot,
                roll,
                roll_buyer,
                qty_stok,
                SUM(qty)-COALESCE(qty_ri, 0) as qty,
                unit,
                rule_bom,
                so_det_list,
                size_list
            FROM (
                SELECT
                    whs_bppb_det.id_roll,
                    whs_bppb_det.id_jo,
                    masteritem.itemdesc detail_item,
                    masteritem.color detail_item_color,
                    masteritem.size detail_item_size,
                    whs_bppb_det.id_item,
                    whs_bppb_det.no_lot lot,
                    whs_bppb_det.no_roll roll,
                    whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                    whs_bppb_det.qty_stok,
                    whs_bppb_det.qty_out qty,
                    whs_bppb_det.satuan unit,
                    bji.rule_bom,
                    GROUP_CONCAT(DISTINCT so_det.id ORDER BY so_det.id ASC SEPARATOR ', ') as so_det_list,
                    GROUP_CONCAT(DISTINCT so_det.size ORDER BY so_det.id ASC SEPARATOR ', ') as size_list
                FROM
                    whs_bppb_det
                    LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                    LEFT JOIN (SELECT no_barcode, id_item, no_roll_buyer FROM whs_lokasi_inmaterial where no_barcode = '".$id."' GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                    LEFT JOIN masteritem ON masteritem.id_item = whs_lokasi_inmaterial.id_item
                    LEFT JOIN bom_jo_item bji ON bji.id_item = masteritem.id_gen
                    LEFT JOIN so_det ON so_det.id = bji.id_so_det
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                WHERE
                    whs_bppb_det.id_roll = '".$id."'
                    AND whs_bppb_h.tujuan = 'Production - Cutting'
                    AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                    AND whs_bppb_det.no_bppb LIKE '%GK/OUT%'
                GROUP BY
                    whs_bppb_det.id
            ) item
            LEFT JOIN (select no_barcode, sum(qty_aktual) qty_ri from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.no_barcode = '".$id."' and supplier = 'Production - Cutting' and a.status = 'Y' GROUP BY no_barcode) as ri on ri.no_barcode = item.id_roll
            GROUP BY
                id_roll
            LIMIT 1
        ");
        if ($newItem) {
            $scannedItem = ScannedItem::selectRaw("
                scanned_item.id,
                scanned_item.id_roll,
                scanned_item.id_jo,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.detail_item_color,
                scanned_item.detail_item_size,
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
            ")->
            leftJoin(DB::raw("
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
                                sum( COALESCE(total_pemakaian_roll, 0) + COALESCE(sisa_kain, 0) ) total_pemakaian
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
                                sum( COALESCE(piping, 0) + COALESCE(qty_sisa, 0) ) total_pemakaian
                            FROM
                                form_cut_piping
                            WHERE
                                id_roll = '".$id."'
                            GROUP BY
                                id_roll
                            UNION
                            SELECT
                                barcode id_roll,
                                max( qty_roll ) qty_awal,
                                sum( COALESCE(qty_pakai, 0) + COALESCE(sisa_kain, 0) ) total_pemakaian
                            FROM
                                form_cut_reject_barcode
                            WHERE
                                barcode = '".$id."'
                            GROUP BY
                                barcode
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
                    $scannedItemUpdate->qty = ($scannedItem->qty_pakai > 0 ? floatval(($newItemQty - $scannedItem->qty_in) + $scannedItem->qty) : $newItemQty);
                    $scannedItemUpdate->so_det_list = $newItem[0]->so_det_list;
                    $scannedItemUpdate->size_list = $newItem[0]->size_list;
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
                        "id_roll" => strtoupper($id),
                        "id_item" => $newItem[0]->id_item,
                        "id_jo" => $newItem[0]->id_jo,
                        "color" => '-',
                        "detail_item" => $newItem[0]->detail_item,
                        "detail_item_color" => $newItem[0]->detail_item_color,
                        "detail_item_size" => $newItem[0]->detail_item_size,
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
                mi.color detail_item_color,
                mi.size detail_item_size,
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

                if (($item[0]->unit != "PCS" || $item[0]->unit != "PCE")) {
                    $itemQtyStok = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($item[0]->qty * 0.9144, 2) : $item[0]->qty;
                    $itemQty = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? round($item[0]->qty * 0.9144, 2) : $item[0]->qty;
                    $itemUnit = (($item[0]->unit == "YARD" || $item[0]->unit == "YRD") && $scannedItemUpdate->unit == "METER") ? 'METER' : $item[0]->unit;
                } else {
                    $itemQtyStok = $item[0]->qty;
                    $itemQty = $item[0]->qty;
                    $itemUnit = $item[0]->unit;
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
                        "id_roll" => strtoupper($id),
                        "id_item" => $item[0]->id_item,
                        "color" => '-',
                        "detail_item" => $item[0]->detail_item,
                        "detail_item_color" => $item[0]->detail_item_color,
                        "detail_item_size" => $item[0]->detail_item_size,
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

    public function getOutput(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);
        } else {
            $kodeNumbering = "'no_filter'";
        }

        if ($request->department) {
            $department = ($request->department == "packing_po" ? "_packing_po" : ($request->department == "packing" ? "_packing" : ""));
        } else {
            $department = "";
        }

        $kodeNumberingOutput = collect(
            DB::connection("mysql_sb")->select("
                SELECT output.*, act_costing.kpno as ws, act_costing.styleno style, so_det.color, so_det.size, userpassword.username as sewing_line, ".($department && $department == "_packing_po" ? "'packing' as type" : ($department && $department == "_packing" ? "'finishing' as type" : "'qc' as type"))." FROM (
                    select master_plan_id, so_det_id, created_by ".($department == "_packing_po" ? ", created_by_username, created_by_line" : "").", kode_numbering, id, created_at, updated_at, 'rft' as status, '-' as defect, '-' as allocation from output_rfts".$department." as output_rfts WHERE status = 'NORMAL' and kode_numbering in (".$kodeNumbering.")
                    ".
                    (
                        $department != "_packing_po" ?
                            "
                                UNION
                                select master_plan_id, so_det_id, created_by, kode_numbering, output_defects.id, output_defects.created_at, output_defects.updated_at, defect_status as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_defects".$department." as output_defects left join output_defect_types on output_defect_types.id = output_defects.defect_type_id WHERE kode_numbering in (".$kodeNumbering.")
                                UNION
                                select master_plan_id, so_det_id, created_by, kode_numbering, output_rejects.id, output_rejects.created_at, output_rejects.updated_at, reject_status as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_rejects".$department." as output_rejects left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id WHERE reject_status = 'mati' and kode_numbering in (".$kodeNumbering.")
                            "
                            :
                            ""
                    )
                    ."
                ) output
                ".
                (
                    $department && $department == "_packing_po" ?
                    "
                        left join userpassword on userpassword.username = output.created_by_line
                    "
                    :
                    (
                        $department && $department == "_packing" ?
                        "
                            left join userpassword on userpassword.username = output.created_by
                        "
                        :
                        "
                            left join user_sb_wip on user_sb_wip.id = output.created_by
                            left join userpassword on userpassword.line_id = user_sb_wip.line_id
                        "
                    )
                )."
                left join so_det on so_det.id = output.so_det_id
                left join so on so.id = so_det.id_so
                left join act_costing on act_costing.id = so.id_cost
            ")
        );

        return Datatables::of($kodeNumberingOutput)->toJson();
    }
    public function getMasterPlan(Request $request) {
        $masterPlanSql = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color,
                master_plan.cancel
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws');

            // Date Filter
            if ($request->tanggal) {
                $masterPlanSql->whereRaw('master_plan.tgl_plan = "'.$request->tanggal.'"');
            } else {
                $masterPlanSql->whereRaw('YEAR(master_plan.tgl_plan) = "'.date('Y').'"');
            }

            // Line Filter
            if ($request->line) {
                $masterPlanSql->where('master_plan.sewing_line', $request->line);
            }

            $masterPlan = $masterPlanSql->
                orderBy('master_plan.tgl_plan', 'desc')->
                orderBy('act_costing.kpno', 'asc')->
                get();

        return $masterPlan;
    }

    public function getMasterPlanDetail($id = 0) {
        $masterPlan = MasterPlan::selectRaw('
                master_plan.id,
                master_plan.tgl_plan as tanggal,
                master_plan.id_ws as id_ws,
                act_costing.kpno as no_ws,
                act_costing.styleno as style,
                master_plan.color as color,
                master_plan.cancel,
                master_plan.jam_kerja,
                master_plan.smv,
                master_plan.man_power,
                master_plan.plan_target
            ')->
            leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')->
            where('master_plan.id', $id)->
            orderBy('master_plan.tgl_plan', 'desc')->
            orderBy('act_costing.kpno', 'asc')->
            first();

        return $masterPlan;
    }

    public function getMasterPlanOutput(Request $request) {
        $output = collect(
            DB::connection("mysql_sb")->select("
                SELECT output.*, act_costing.kpno as ws, act_costing.styleno style, so_det.color, so_det.size, userpassword.username as sewing_line FROM (
                    select master_plan_id, so_det_id, created_by, ".($request->department == "_packing_po" ? ' created_by_username, created_by_line, ' : '')." kode_numbering, id, created_at, updated_at, 'RFT' as status, '-' as defect, '-' as allocation from output_rfts".$request->department." as output_rfts WHERE status = 'NORMAL' and master_plan_id = '".$request->id."'
                    ".
                    (
                        $request->department != "_packing_po" ?
                            "
                                UNION
                                select master_plan_id, so_det_id, created_by, kode_numbering, output_defects.id, output_defects.created_at, output_defects.updated_at, UPPER(defect_status) as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_defects left join output_defect_types on output_defect_types.id = output_defects.defect_type_id WHERE master_plan_id = '".$request->id."'
                                UNION
                                select master_plan_id, so_det_id, created_by, kode_numbering, output_rejects.id, output_rejects.created_at, output_rejects.updated_at, UPPER(reject_status) as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_rejects left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id WHERE reject_status = 'mati' and master_plan_id = '".$request->id."'
                            "
                        :
                            ""
                    )
                    ."
                ) output
                ".(
                    $request->department == "_packing_po" ?
                        "left join userpassword on userpassword.username = output_rfts.created_by_line"
                    :
                    (
                        $request->department == "_packing" ?
                            "left join userpassword on userpassword.username = output_rfts.created_by"
                        :
                        (
                            "
                                left join user_sb_wip on user_sb_wip.id = output.created_by
                                left join userpassword on userpassword.line_id = user_sb_wip.line_id
                            "
                        )
                    )
                )."
                left join so_det on so_det.id = output.so_det_id
                left join so on so.id = so_det.id_so
                left join act_costing on act_costing.id = so.id_cost
            ")
        );

        return Datatables::of($output)->toJson();
    }

    public function getMasterPlanOutputSize(Request $request) {
        $output = collect(
            DB::connection("mysql_sb")->select("
                SELECT
                    sewing_line,
                    id_ws,
                    ws,
                    style,
                    color,
                    size,
                    dest,
                    ".(
                        $request->department == "_packing_po" ?
                            "
                                po_id,
                                po,
                            "
                        :
                            ""
                    )."
                    so_det_id,
                    master_plan_id,
                    CONCAT(master_plan_id, ws, style, color, size) as grouping,
                    SUM(CASE WHEN status = 'RFT' THEN 1 ELSE 0 END) rft,
                    SUM(CASE WHEN status = 'defect' THEN 1 ELSE 0 END) defect,
                    SUM(CASE WHEN status = 'reworked' THEN 1 ELSE 0 END) rework,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) reject
                FROM
                (
                    SELECT output.*, act_costing.id as id_ws, act_costing.kpno as ws, act_costing.styleno style, so_det.color, so_det.size, so_det.dest, userpassword.username as sewing_line ".($request->department == "_packing_po" ? ', ppic_master_so.po ' : '')." FROM (
                        select master_plan_id, so_det_id, created_by, ".($request->department == "_packing_po" ? ' po_id, created_by_username, created_by_line, ' : '')." kode_numbering, id, created_at, updated_at, 'RFT' as status, '-' as defect, '-' as allocation from output_rfts".$request->department." as output_rfts WHERE status = 'NORMAL' and master_plan_id = '".$request->id."'
                        ".
                        (
                            $request->department != "_packing_po" ?
                                "
                                    UNION
                                    select master_plan_id, so_det_id, created_by, kode_numbering, output_defects.id, output_defects.created_at, output_defects.updated_at, UPPER(defect_status) as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_defects".$request->department." as output_defects left join output_defect_types on output_defect_types.id = output_defects.defect_type_id WHERE master_plan_id = '".$request->id."'
                                    UNION
                                    select master_plan_id, so_det_id, created_by, kode_numbering, output_rejects.id, output_rejects.created_at, output_rejects.updated_at, UPPER(reject_status) as status, output_defect_types.defect_type as defect, output_defect_types.allocation from output_rejects".$request->department." as output_rejects left join output_defect_types on output_defect_types.id = output_rejects.reject_type_id WHERE reject_status = 'mati' and master_plan_id = '".$request->id."'
                                "
                            :
                                ""
                        )
                        ."
                    ) output
                    ".(
                        $request->department == "_packing_po" ?
                            "left join userpassword on userpassword.username = output.created_by_line"
                        :
                        (
                            $request->department == "_packing" ?
                                "left join userpassword on userpassword.username = output.created_by"
                            :
                            (
                                "
                                    left join user_sb_wip on user_sb_wip.id = output.created_by
                                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                                "
                            )
                        )
                    )."
                    ".
                        (
                            $request->department == "_packing_po" ?
                                "
                                    left join laravel_nds.ppic_master_so on ppic_master_so.id = output.po_id
                                "
                            :
                                ""
                        )
                    ."
                    left join so_det on so_det.id = output.so_det_id
                    left join so on so.id = so_det.id_so
                    left join act_costing on act_costing.id = so.id_cost
                ) as output
                GROUP BY
                    master_plan_id,
                    ".(
                        $request->department == "_packing_po" ?
                            "
                                po_id,
                            "
                        :
                            ""
                    )."
                    so_det_id
            ")
        );

        return $output;
    }

    public function getRejectIn(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);
        } else {
            $kodeNumbering = "'no_filter'";
        }

        if ($request->department) {
            $department = $request->department == "packing" ? "_packing" : "";
        } else {
            $department = "";
        }

        $kodeNumberingOutput = RejectIn::selectRaw("
                output_reject_in.id,
                output_reject_in.kode_numbering,
                DATE(output_reject_in.created_at) as tanggal,
                output_reject_in.created_at time_in,
                output_reject_in.updated_at time_out,
                master_plan.sewing_line sewing_line,
                (CASE WHEN output_reject_in.output_type = 'packing' THEN 'finishing' ELSE output_reject_in.output_type END) as output_type,
                output_reject_in.kode_numbering,
                mastersupplier.Supplier as buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color color,
                so_det.size size,
                master_plan.gambar gambar,
                output_reject_in.reject_area_x reject_area_x,
                output_reject_in.reject_area_y reject_area_y,
                output_reject_in.status,
                output_reject_in.grade,
                COALESCE(reject_detail.defect_types_check, output_defect_types.defect_type) as defect_type,
                COALESCE(reject_detail.defect_areas_check, output_defect_areas.defect_area) as defect_area,
                output_reject_out.tujuan as allocation
            ")->
            // Reject
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            // Reject Detail
            leftJoin("output_reject_out_detail", "output_reject_out_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("output_reject_out", "output_reject_out.id", "=", "output_reject_out_detail.reject_out_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_reject_in.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_reject_in.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_reject_in.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin("master_plan", "master_plan.id", "=", "output_reject_in.master_plan_id")->
            leftJoin(DB::raw("(select output_reject_in_detail.reject_in_id, GROUP_CONCAT(output_defect_types.defect_type SEPARATOR ' , ') defect_types_check, GROUP_CONCAT(output_defect_areas.defect_area SEPARATOR ' , ') defect_areas_check from output_reject_in_detail left join output_defect_types on output_defect_types.id = output_reject_in_detail.reject_type_id left join output_defect_areas on output_defect_areas.id = output_reject_in_detail.reject_area_id where output_reject_in_detail.id is not null group by output_reject_in_detail.reject_in_id) as reject_detail"), "reject_detail.reject_in_id", "=", "output_reject_in.id")->
            leftJoin("userpassword", "userpassword.line_id", "=", "output_reject_in.line_id")->
            whereRaw("
                output_reject_in.kode_numbering IN (".$kodeNumbering.")
                AND
                output_reject_in.output_type = '".($request->department ? $request->department : 'qc')."'
            ")->
            groupByRaw("output_reject_in.id")->
            get();

        return Datatables::of($kodeNumberingOutput)->toJson();
    }

    public function getDefectInOut(Request $request) {
        if ($request->kode_numbering) {
            $kodeNumbering = addQuotesAround($request->kode_numbering);
        } else {
            $kodeNumbering = "'no_filter'";
        }

        $department = $request->department;

        $defectInOutList = DefectInOut::selectRaw("
            master_plan.id master_plan_id,
            master_plan.id_ws,
            master_plan.sewing_line,
            act_costing.kpno as ws,
            act_costing.styleno as style,
            master_plan.color as color,
            output_defect_in_out.created_at as time_in,
            output_defect_in_out.updated_at as time_out,
            output_defects.defect_type_id,
            output_defect_types.defect_type,
            output_defect_areas.defect_area,
            output_defects.so_det_id,
            output_defect_in_out.kode_numbering,
            output_defect_in_out.output_type,
            output_defect_in_out.updated_at as defect_time,
            output_defect_in_out.status,
            output_defect_types.allocation,
            so_det.size
        ")->
        leftJoin(($department == 'packing' ? 'output_defects_packing' : ($department == 'qcf' ? 'output_check_finishing' : 'output_defects'))." as output_defects", "output_defects.id", "=", "output_defect_in_out.defect_id")->
        leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")->
        leftJoin("master_plan", "master_plan.id", "=", "output_defects.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_defects.defect_type_id")->
        leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_defects.defect_area_id")->
        whereNotNull("output_defects.id")->
        where("output_defect_in_out.output_type", $department)->
        whereRaw("YEAR(output_defect_in_out.created_at) = '".date("Y")."' AND output_defects.kode_numbering in (".$kodeNumbering.")")->
        groupBy("output_defect_in_out.id")->
        orderBy("output_defect_in_out.updated_at", "desc")->
        get();

        return Datatables::of($defectInOutList)->toJson();
    }
}
