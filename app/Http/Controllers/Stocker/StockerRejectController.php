<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerReject;
use App\Models\DC\DCIn;
use App\Models\DC\SecondaryInhouse;
use App\Models\DC\SecondaryIn;
use App\Services\StockerService;
use App\Services\StockerProcessRejectService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use PDF;
use Carbon\Carbon;

class StockerRejectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

            $dataStockerReject = DB::select("
                SELECT
                    *
                FROM
                (
                    -- dc_in_input
                    SELECT
                        tgl_trans tanggal,
                        dc_in_input.id as id,
                        dc_in_input.id dc_in_id,
                        NULL AS secondary_inhouse_id,
                        NULL AS secondary_in_id,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        'DC In' AS proses,
                        COALESCE( ( dc_in_input.qty_reject - dc_in_input.qty_replace ) ) qty_reject,
                        COALESCE( stocker_reject.generated_qty_reject, 0 ) generated_qty_reject,
                        ( COALESCE( ( dc_in_input.qty_reject - dc_in_input.qty_replace ), 0) - COALESCE(stocker_reject.generated_qty_reject, 0) ) qty_reject_balance
                    FROM
                        dc_in_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = dc_in_input.id_qr_stocker
                        LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                        AND similar_stocker.so_det_id = stocker_input.so_det_id
                        AND similar_stocker.shade = stocker_input.shade
                        AND similar_stocker.group_stocker = stocker_input.group_stocker
                        AND similar_stocker.ratio = stocker_input.ratio
                        AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                        AND similar_stocker.stocker_reject is null
                        LEFT JOIN (
                            SELECT
                                stocker_reject.*,
                                SUM(stocker_reject.qty_reject) generated_qty_reject
                            FROM
                                dc_in_input
                                inner join stocker_reject on stocker_reject.dc_in_id = dc_in_input.id
                            WHERE
                                ( dc_in_input.qty_reject - dc_in_input.qty_replace ) > 0
                                AND dc_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                            GROUP BY
                                dc_in_input.id
                        ) stocker_reject on stocker_reject.dc_in_id = dc_in_input.id
                    WHERE
                        ( dc_in_input.qty_reject - dc_in_input.qty_replace ) > 0
                        AND dc_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                    GROUP BY
                        dc_in_input.id
                UNION ALL
                    -- secondary_inhouse_input
                    SELECT
                        tgl_trans tanggal,
                        secondary_inhouse_input.id as id,
                        NULL dc_in_id,
                        secondary_inhouse_input.id secondary_inhouse_id,
                        NULL AS secondary_in_id,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        'Secondary Inhouse' AS proses,
                        COALESCE( ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) ) qty_reject,
                        COALESCE( stocker_reject.generated_qty_reject, 0 ) generated_qty_reject,
                        ( COALESCE( ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ), 0) - COALESCE(stocker_reject.generated_qty_reject, 0) ) qty_reject_balance
                    FROM
                        secondary_inhouse_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                        LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                        AND similar_stocker.so_det_id = stocker_input.so_det_id
                        AND similar_stocker.shade = stocker_input.shade
                        AND similar_stocker.group_stocker = stocker_input.group_stocker
                        AND similar_stocker.ratio = stocker_input.ratio
                        AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                        AND similar_stocker.stocker_reject is null
                        LEFT JOIN (
                            SELECT
                                stocker_reject.*,
                                SUM(stocker_reject.qty_reject) generated_qty_reject
                            FROM
                                secondary_inhouse_input
                                inner join stocker_reject on stocker_reject.secondary_inhouse_id = secondary_inhouse_input.id
                            WHERE
                                ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) > 0
                                AND secondary_inhouse_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                            GROUP BY
                                secondary_inhouse_input.id
                        ) stocker_reject on stocker_reject.secondary_inhouse_id = secondary_inhouse_input.id
                    WHERE
                        ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) > 0
                        AND secondary_inhouse_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                    GROUP BY
                        secondary_inhouse_input.id
                UNION ALL
                    -- secondary_in_input
                    SELECT
                        tgl_trans tanggal,
                        secondary_in_input.id AS id,
                        NULL dc_in_id,
                        NULL secondary_inhouse_id,
                        secondary_in_input.id AS secondary_in_id,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        'Secondary In' AS proses,
                        COALESCE( ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) ) qty_reject,
                        COALESCE( stocker_reject.generated_qty_reject, 0 ) generated_qty_reject,
                        ( COALESCE( ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ), 0) - COALESCE(stocker_reject.generated_qty_reject, 0) ) qty_reject_balance
                    FROM
                        secondary_in_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                        LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                        AND similar_stocker.so_det_id = stocker_input.so_det_id
                        AND similar_stocker.shade = stocker_input.shade
                        AND similar_stocker.group_stocker = stocker_input.group_stocker
                        AND similar_stocker.ratio = stocker_input.ratio
                        AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                        AND similar_stocker.stocker_reject is null
                        LEFT JOIN (
                            SELECT
                                stocker_reject.*,
                                SUM(stocker_reject.qty_reject) generated_qty_reject
                            FROM
                                secondary_in_input
                                inner join stocker_reject on stocker_reject.secondary_inhouse_id = secondary_in_input.id
                            WHERE
                                ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) > 0
                                AND secondary_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                            GROUP BY
                                secondary_in_input.id
                        ) stocker_reject on stocker_reject.secondary_in_id = secondary_in_input.id
                    WHERE
                        ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) > 0
                        AND secondary_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                    GROUP BY
                        secondary_in_input.id
                ) dc_reject_transaction
            ");

            return Datatables::of($dataStockerReject)->toJson();
        }

        return view('stocker.stocker.stocker-reject.stocker-reject', ['page' => 'dashboard-dc', 'subPageGroup' => 'stocker-reject', 'subPage' => 'stocker-reject']);
    }

    public function getStockerReject(Request $request)
    {
        if ($request->id_qr_stocker) {
            $stocker = Stocker::selectRaw("
                    stocker_input.id_qr_stocker,
                    GROUP_CONCAT(similar_stocker.id_qr_stocker) id_qr_stocker_similar,
                    CONCAT(stocker_input.id_qr_stocker, GROUP_CONCAT(similar_stocker.id_qr_stocker)) id_qr_stocker_all,
                    master_sb_ws.ws,
                    master_sb_ws.styleno,
                    master_sb_ws.color,
                    master_sb_ws.size,
                    part.panel,
                    master_part.nama_part
                ")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
                leftJoin(DB::raw("stocker_input as similar_stocker"), function ($join) {
                    $join->on("stocker_input.form_cut_id", "=", "similar_stocker.form_cut_id");
                    $join->on("stocker_input.so_det_id", "=", "similar_stocker.so_det_id");
                    $join->on("stocker_input.shade", "=", "similar_stocker.shade");
                    $join->on("stocker_input.group_stocker", "=", "similar_stocker.group_stocker");
                    $join->on("stocker_input.ratio", "=", "similar_stocker.ratio");
                    $join->on("stocker_input.id_qr_stocker", "!=", "similar_stocker.id_qr_stocker");
                    $join->whereRaw("similar_stocker.stocker_reject IS NULL");
                })->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                where("stocker_input.id_qr_stocker", $request->id_qr_stocker)->
                groupBy("stocker_input.id")->
                first();

            if ($stocker) {
                $stockerProcessDc = DcIn::selectRaw("id_qr_stocker, (qty_reject+qty_replace) qty_reject, 'dc_in' as process")->whereRaw("id_qr_stocker in (".addQuotesAround(str_replace(",", "\n", $request->id_qr_stocker)).")");
                $stockerProcessSecInhouse = SecondaryInhouse::selectRaw("id_qr_stocker, (qty_reject+qty_replace) qty_reject, 'secondary_inhouse' as process")->whereRaw("id_qr_stocker in (".addQuotesAround(str_replace(",", "\n", $request->id_qr_stocker)).")");
                $stockerProcessSecIn = SecondaryIn::selectRaw("id_qr_stocker, (qty_reject+qty_replace) qty_reject, 'secondary_in' as process")->whereRaw("id_qr_stocker in (".addQuotesAround(str_replace(",", "\n", $request->id_qr_stocker)).")");

                $stockerProcess = $stockerProcessDc->unionAll($stockerProcessSecInhouse)->unionAll($stockerProcessSecIn)->get();

                return array(
                    "status" => 200,
                    "message" => "Stocker berhasil ditemukan",
                    "data" => $stocker,
                    "dataProcess" => $stockerProcess,
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Stocker tidak ditemukan",
            "data" => null,
            "dataProcess" => null,
        );
    }

    public function getStockerRejectProcess(Request $request)
    {
        // Additional filter
        $dcFilter = " AND dc_in_input.id_qr_stocker = '-'";
        $secondaryInhouseFilter = " AND secondary_inhouse_input.id_qr_stocker = '-'";
        $secondaryInFilter = " AND secondary_in_input.id_qr_stocker = '-'";
        if ($request->id_qr_stocker != null) {

            if ($request->process == "dc_in") {
                $dcFilter = " AND dc_in_input.id_qr_stocker = '".$request->id_qr_stocker."'";
            }
            if ($request->process == "secondary_inhouse") {
                $secondaryInhouseFilter = " AND secondary_inhouse_input.id_qr_stocker = '".$request->id_qr_stocker."'";
            }
            if ($request->process == "secondary_in") {
                $secondaryInFilter = " AND secondary_in_input.id_qr_stocker = '".$request->id_qr_stocker."'";
            }

        }

        $dataStockerReject = DB::select("
            SELECT
                *
            FROM
            (
                -- dc_in_input
                SELECT
                    tgl_trans tanggal,
                    dc_in_input.id as id,
                    dc_in_input.id dc_in_id,
                    NULL AS secondary_inhouse_id,
                    NULL AS secondary_in_id,
                    stocker_input.id_qr_stocker,
                    GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                    stocker_input.act_costing_ws,
                    stocker_input.color,
                    stocker_input.size,
                    'DC In' AS proses,
                    ( dc_in_input.qty_reject - dc_in_input.qty_replace ) qty_reject
                FROM
                    dc_in_input
                    LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = dc_in_input.id_qr_stocker
                    LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                    AND similar_stocker.so_det_id = stocker_input.so_det_id
                    AND similar_stocker.shade = stocker_input.shade
                    AND similar_stocker.group_stocker = stocker_input.group_stocker
                    AND similar_stocker.ratio = stocker_input.ratio
                    AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                    AND similar_stocker.stocker_reject is null
                WHERE
                    ( dc_in_input.qty_reject - dc_in_input.qty_replace ) > 0
                    ".$dcFilter."
                GROUP BY
                    dc_in_input.id
            UNION ALL
                -- secondary_inhouse_input
                SELECT
                    tgl_trans tanggal,
                    secondary_inhouse_input.id as id,
                    NULL dc_in_id,
                    secondary_inhouse_input.id secondary_inhouse_id,
                    NULL AS secondary_in_id,
                    stocker_input.id_qr_stocker,
                    GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                    stocker_input.act_costing_ws,
                    stocker_input.color,
                    stocker_input.size,
                    'Secondary Inhouse' AS proses,
                    ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) qty_reject
                FROM
                    secondary_inhouse_input
                    LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                    LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                    AND similar_stocker.so_det_id = stocker_input.so_det_id
                    AND similar_stocker.shade = stocker_input.shade
                    AND similar_stocker.group_stocker = stocker_input.group_stocker
                    AND similar_stocker.ratio = stocker_input.ratio
                    AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                    AND similar_stocker.stocker_reject is null
                WHERE
                    ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) > 0
                    ".$secondaryInhouseFilter."
                GROUP BY
                    secondary_inhouse_input.id
            UNION ALL
                -- secondary_in_input
                SELECT
                    tgl_trans tanggal,
                    secondary_in_input.id AS id,
                    NULL dc_in_id,
                    NULL secondary_inhouse_id,
                    secondary_in_input.id AS secondary_in_id,
                    stocker_input.id_qr_stocker,
                    GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                    stocker_input.act_costing_ws,
                    stocker_input.color,
                    stocker_input.size,
                    'Secondary In' AS proses,
                    ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) qty_reject
                FROM
                    secondary_in_input
                    LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                    LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                    AND similar_stocker.so_det_id = stocker_input.so_det_id
                    AND similar_stocker.shade = stocker_input.shade
                    AND similar_stocker.group_stocker = stocker_input.group_stocker
                    AND similar_stocker.ratio = stocker_input.ratio
                    AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                    AND similar_stocker.stocker_reject is null
                WHERE
                    ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) > 0
                    ".$secondaryInFilter."
                GROUP BY
                    secondary_in_input.id
            ) dc_reject_transaction
            -- secondary_in only transaction
        ");

        return Datatables::of($dataStockerReject)->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stocker.stocker.stocker-reject.create-stocker-reject', ['page' => 'dashboard-stocker', 'subPageGroup' => 'stocker-reject', 'subPage' => 'stocker-reject']);
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
    public function show(Request $request)
    {
        if ($request->id && $request->process) {

            $data = null;
            $filterColumn = "";
            switch ($request->process) {
                case 'DC In' :
                    $data = DB::select("
                        SELECT
                            tgl_trans tanggal,
                            dc_in_input.id dc_in_id,
                            NULL secondary_inhouse_id,
                            NULL AS secondary_in_id,
                            stocker_input.id_qr_stocker,
                            GROUP_CONCAT( similar_stocker.id_qr_stocker SEPARATOR ', ' ) AS id_qr_similar_stocker,
                            master_sb_ws.ws as act_costing_ws,
                            master_sb_ws.styleno as style,
                            master_sb_ws.color,
                            master_sb_ws.size,
                            stocker_input.panel,
                            stocker_input.form_cut_id,
                            stocker_input.urutan,
                            form_cut_input.no_form,
                            'DC In' AS proses,
                            ( dc_in_input.qty_reject - dc_in_input.qty_replace ) qty_reject
                        FROM
                            dc_in_input
                            LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = dc_in_input.id_qr_stocker
                            LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                                AND similar_stocker.so_det_id = stocker_input.so_det_id
                                AND similar_stocker.shade = stocker_input.shade
                                AND similar_stocker.group_stocker = stocker_input.group_stocker
                                AND similar_stocker.ratio = stocker_input.ratio
                                AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                                AND similar_stocker.stocker_reject is null
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                        WHERE
                            ( dc_in_input.qty_reject - dc_in_input.qty_replace ) > 0
                            AND dc_in_input.id = '".$request->id."'
                            AND stocker_input.stocker_reject is null
                        GROUP BY
                            dc_in_input.id
                    ");

                    $filterColumn = "dc_in_id";

                    break;
                case 'Secondary Inhouse' :
                    $data = DB::select("
                        SELECT
                            tgl_trans tanggal,
                            NULL dc_in_id,
                            secondary_inhouse_input.id secondary_inhouse_id,
                            NULL AS secondary_in_id,
                            stocker_input.id_qr_stocker,
                            GROUP_CONCAT( similar_stocker.id_qr_stocker SEPARATOR ', ' ) AS id_qr_similar_stocker,
                            master_sb_ws.ws as act_costing_ws,
                            master_sb_ws.styleno as style,
                            master_sb_ws.color,
                            master_sb_ws.size,
                            stocker_input.panel,
                            stocker_input.form_cut_id,
                            form_cut_input.no_form,
                            stocker_input.urutan,
                            'Secondary In' AS proses,
                            ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) qty_reject
                        FROM
                            secondary_inhouse_input
                            LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                            LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                                AND similar_stocker.so_det_id = stocker_input.so_det_id
                                AND similar_stocker.shade = stocker_input.shade
                                AND similar_stocker.group_stocker = stocker_input.group_stocker
                                AND similar_stocker.ratio = stocker_input.ratio
                                AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                                AND similar_stocker.stocker_reject is null
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                        WHERE
                            ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) > 0
                            AND secondary_inhouse_input.id = '".$request->id."'
                            AND stocker_input.stocker_reject is null
                        GROUP BY
                            secondary_inhouse_input.id
                    ");

                    $filterColumn = "secondary_inhouse_id";

                    break;
                case 'Secondary In' :
                    $data = DB::select("
                        SELECT
                            tgl_trans tanggal,
                            NULL dc_in_id,
                            NULL secondary_inhouse_id,
                            secondary_in_input.id AS secondary_in_id,
                            stocker_input.id_qr_stocker,
                            GROUP_CONCAT( similar_stocker.id_qr_stocker SEPARATOR ', ' ) AS id_qr_similar_stocker,
                            master_sb_ws.ws as act_costing_ws,
                            master_sb_ws.styleno as style,
                            master_sb_ws.color,
                            master_sb_ws.size,
                            stocker_input.panel,
                            stocker_input.form_cut_id,
                            form_cut_input.no_form,
                            stocker_input.urutan,
                            'Secondary In' AS proses,
                            ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) qty_reject
                        FROM
                            secondary_in_input
                            LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                            LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                                AND similar_stocker.so_det_id = stocker_input.so_det_id
                                AND similar_stocker.shade = stocker_input.shade
                                AND similar_stocker.group_stocker = stocker_input.group_stocker
                                AND similar_stocker.ratio = stocker_input.ratio
                                AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                                AND similar_stocker.stocker_reject is null
                            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = stocker_input.so_det_id
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                        WHERE
                            ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) > 0
                            AND secondary_in_input.id = '".$request->id."'
                            AND stocker_input.stocker_reject is null
                        GROUP BY
                            secondary_in_input.id
                    ");

                    $filterColumn = "secondary_in_id";

                    break;
            }

            if ($data && $data[0]) {
                $stockerList = $data[0]->id_qr_stocker.", ".$data[0]->id_qr_similar_stocker;
                $stockerListFilter = addQuotesAround(preg_replace("/,/", "\n", $stockerList));

                $dataStocker = Stocker::selectRaw("
                        stocker_input.id,
                        stocker_input.id_qr_stocker,
                        stocker_input.form_cut_id,
                        stocker_input.so_det_id,
                        stocker_input.size,
                        stocker_input.panel,
                        stocker_input.part_detail_id,
                        stocker_input.group_stocker,
                        stocker_input.shade,
                        stocker_input.ratio,
                        master_part.nama_part,
                        stocker_input.urutan
                    ")->
                    leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                    leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                    leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                    whereRaw("stocker_input.id_qr_stocker in (".$stockerListFilter.")")->
                    get();

                $dataStockerReject = StockerReject::selectRaw("id, ratio, qty_reject")->where($filterColumn, $request->id)->get();

                return view('stocker.stocker.stocker-reject.stocker-reject-detail', ['data' => $data[0], 'dataStocker' => $dataStocker, 'dataStockerReject' => $dataStockerReject, 'page' => 'dashboard-stocker', 'subPageGroup' => 'stocker-reject', 'subPage' => 'stocker-reject']);
            }
        }
    }

    function storeStockerProcessReject(Request $request, StockerProcessRejectService $stockerProcessRejectService)
    {
        if (($request['dc_in_id'] || $request['secondary_inhouse_id'] || $request['secondary_in_id']) && $request['qty_input']) {
            $createStockerReject = $stockerProcessRejectService->storeStockerProcessReject($request, true);

            return $createStockerReject;
        }

        return array(
            "status" => 400,
            "message" => "Data gagal disimpan."
        );
    }

    function printStocker(Request $request, StockerService $stockerService, StockerProcessRejectService $stockerProcessRejectService, $index = null) {
        if ($request->stocker_id) {
            // $stockerProcessRejectService->storeStockerProcessReject($request);

            $filterArr = [
                "formCutId" => $request['form_cut_id'],
            ];

            // Get stocker reject
            $currentStockerReject = StockerReject::where("dc_in_id", $request->dc_in_id)->where("secondary_inhouse_id", $request->secondary_inhouse_id)->where("secondary_in_id", $request->secondary_in_id)->get();

            if ($currentStockerReject) {

                // If single Print
                if ($index && isset($request['stocker_reject_id_detail'][$index])) {
                    $currentStockerRejectId = $request['stocker_reject_id_detail'][$index];
                    $currentStockerReject = $currentStockerReject->filter(function ($data) use ($currentStockerRejectId) {
                        return $data->id == $currentStockerRejectId;
                    });
                }

                // Get stocker ids
                $stockerIdsArr = [];
                foreach ($currentStockerReject as $stockerReject) {

                    // Get stocker ids
                    $stockerIds = Stocker::select("id")->
                        where('stocker_reject', $stockerReject->id);

                        // If single print
                        if ($index && isset($request['part_detail_id_detail'][$index])) {
                            $stockerIds->where("part_detail_id", $request['part_detail_id_detail'][$index]);
                        }

                    $newStockerIds = $stockerIds->get()->pluck("id")->toArray();

                    // Merge Array (Push to stocker ids arr)
                    $stockerIdsArr = array_merge($stockerIdsArr, $newStockerIds);
                }

                if ($stockerIdsArr) {
                    // Get Stocker Data
                    $filterArr['multiStockerId'] = $stockerIdsArr;
                    $dataStockers = $stockerService->getStockerForPrint($filterArr);

                    // generate pdf
                    PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
                    $customPaper = array(0, 0, 300, 250);
                    $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

                    $fileName = 'STOCKER_REJECT_'.$request["act_costing_ws"]."_".$request['color']."_".$request['panel'].($index && isset($request['shade'][$index]) ? "_".$request['shade'][$index] : "")."_".($index && isset($request['size'][$index]) ? "_".$request['size'][$index] : "") . '.pdf';

                    return $pdf->download(str_replace("/", "_", $fileName));
                }
            }
        }

        return null;
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

    public function exportStockerReject(Request $request)
    {
        $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
        $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

        // Create Excel file using FastExcel
        $excel = FastExcel::create('Stocker Reject List');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'STOCKER REJECT LIST', ['font-size' => 16, 'font-bold' => true]);
        $sheet->mergeCells('A1:G1');

        // Period
        $sheet->writeTo('A2', 'Periode : ' . $dateFrom . ' s/d ' . $dateTo);
        $sheet->mergeCells('A2:G2');

        // Headers
        $sheet->writeTo('A4', 'Tanggal')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B4', 'Stocker')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C4', 'Proses')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D4', 'Qty Reject')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E4', 'Generated Qty')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F4', 'Qty Reject Balance')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G4', 'Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $dataStockerReject = DB::select("
                SELECT
                    *
                FROM
                (
                    -- dc_in_input
                    SELECT
                        tgl_trans tanggal,
                        dc_in_input.id as id,
                        dc_in_input.id dc_in_id,
                        NULL AS secondary_inhouse_id,
                        NULL AS secondary_in_id,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        'DC In' AS proses,
                        COALESCE( ( dc_in_input.qty_reject - dc_in_input.qty_replace ) ) qty_reject,
                        COALESCE( stocker_reject.generated_qty_reject, 0 ) generated_qty_reject,
                        ( COALESCE( ( dc_in_input.qty_reject - dc_in_input.qty_replace ), 0) - COALESCE(stocker_reject.generated_qty_reject, 0) ) qty_reject_balance
                    FROM
                        dc_in_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = dc_in_input.id_qr_stocker
                        LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                        AND similar_stocker.so_det_id = stocker_input.so_det_id
                        AND similar_stocker.shade = stocker_input.shade
                        AND similar_stocker.group_stocker = stocker_input.group_stocker
                        AND similar_stocker.ratio = stocker_input.ratio
                        AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                        AND similar_stocker.stocker_reject is null
                        LEFT JOIN (
                            SELECT
                                stocker_reject.*,
                                SUM(stocker_reject.qty_reject) generated_qty_reject
                            FROM
                                dc_in_input
                                inner join stocker_reject on stocker_reject.dc_in_id = dc_in_input.id
                            WHERE
                                ( dc_in_input.qty_reject - dc_in_input.qty_replace ) > 0
                                AND dc_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                            GROUP BY
                                dc_in_input.id
                        ) stocker_reject on stocker_reject.dc_in_id = dc_in_input.id
                    WHERE
                        ( dc_in_input.qty_reject - dc_in_input.qty_replace ) > 0
                        AND dc_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                    GROUP BY
                        dc_in_input.id
                UNION ALL
                    -- secondary_inhouse_input
                    SELECT
                        tgl_trans tanggal,
                        secondary_inhouse_input.id as id,
                        NULL dc_in_id,
                        secondary_inhouse_input.id secondary_inhouse_id,
                        NULL AS secondary_in_id,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        'Secondary Inhouse' AS proses,
                        COALESCE( ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) ) qty_reject,
                        COALESCE( stocker_reject.generated_qty_reject, 0 ) generated_qty_reject,
                        ( COALESCE( ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ), 0) - COALESCE(stocker_reject.generated_qty_reject, 0) ) qty_reject_balance
                    FROM
                        secondary_inhouse_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_inhouse_input.id_qr_stocker
                        LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                        AND similar_stocker.so_det_id = stocker_input.so_det_id
                        AND similar_stocker.shade = stocker_input.shade
                        AND similar_stocker.group_stocker = stocker_input.group_stocker
                        AND similar_stocker.ratio = stocker_input.ratio
                        AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                        AND similar_stocker.stocker_reject is null
                        LEFT JOIN (
                            SELECT
                                stocker_reject.*,
                                SUM(stocker_reject.qty_reject) generated_qty_reject
                            FROM
                                secondary_inhouse_input
                                inner join stocker_reject on stocker_reject.secondary_inhouse_id = secondary_inhouse_input.id
                            WHERE
                                ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) > 0
                                AND secondary_inhouse_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                            GROUP BY
                                secondary_inhouse_input.id
                        ) stocker_reject on stocker_reject.secondary_inhouse_id = secondary_inhouse_input.id
                    WHERE
                        ( secondary_inhouse_input.qty_reject - secondary_inhouse_input.qty_replace ) > 0
                        AND secondary_inhouse_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                    GROUP BY
                        secondary_inhouse_input.id
                UNION ALL
                    -- secondary_in_input
                    SELECT
                        tgl_trans tanggal,
                        secondary_in_input.id AS id,
                        NULL dc_in_id,
                        NULL secondary_inhouse_id,
                        secondary_in_input.id AS secondary_in_id,
                        stocker_input.id_qr_stocker,
                        GROUP_CONCAT( similar_stocker.id_qr_stocker ) AS id_qr_similar_stocker,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        'Secondary In' AS proses,
                        COALESCE( ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) ) qty_reject,
                        COALESCE( stocker_reject.generated_qty_reject, 0 ) generated_qty_reject,
                        ( COALESCE( ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ), 0) - COALESCE(stocker_reject.generated_qty_reject, 0) ) qty_reject_balance
                    FROM
                        secondary_in_input
                        LEFT JOIN stocker_input ON stocker_input.id_qr_stocker = secondary_in_input.id_qr_stocker
                        LEFT JOIN stocker_input AS similar_stocker ON similar_stocker.form_cut_id = stocker_input.form_cut_id
                        AND similar_stocker.so_det_id = stocker_input.so_det_id
                        AND similar_stocker.shade = stocker_input.shade
                        AND similar_stocker.group_stocker = stocker_input.group_stocker
                        AND similar_stocker.ratio = stocker_input.ratio
                        AND similar_stocker.id_qr_stocker != stocker_input.id_qr_stocker
                        AND similar_stocker.stocker_reject is null
                        LEFT JOIN (
                            SELECT
                                stocker_reject.*,
                                SUM(stocker_reject.qty_reject) generated_qty_reject
                            FROM
                                secondary_in_input
                                inner join stocker_reject on stocker_reject.secondary_inhouse_id = secondary_in_input.id
                            WHERE
                                ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) > 0
                                AND secondary_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                            GROUP BY
                                secondary_in_input.id
                        ) stocker_reject on stocker_reject.secondary_in_id = secondary_in_input.id
                    WHERE
                        ( secondary_in_input.qty_reject - secondary_in_input.qty_replace ) > 0
                        AND secondary_in_input.tgl_trans BETWEEN '".$dateFrom."' AND '".$dateTo."'
                    GROUP BY
                        secondary_in_input.id
                ) dc_reject_transaction
            ");

        $sheet->writeAreas();
        foreach ($dataStockerReject as $data) {
            $rowArr = [
                $data->tanggal ?? '-',
                $data->id_qr_stocker ?? '-',
                $data->proses ?? '-',
                $data->qty_reject ?? 0,
                $data->generated_qty_reject ?? 0,
                $data->qty_reject_balance ?? 0,
                $data->qty_reject_balance > 0 ? 'AVAILABLE' : 'EXHAUSTED',
            ];

            $statusColor = "#000";
            if ($data->qty_reject_balance > 0) {
                $statusColor = "#118036";
            } else {
                $statusColor = "#cb461d";
            }

            $cellStyles = [
                // [],
                // [],
                // [],
                // [],
                // [],
                // [],
                "G" => ['font-color' => $statusColor, 'font-style' => 'bold',],
            ];

            $sheet->writeRow($rowArr, [], $cellStyles)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = 'Stocker Reject List ' . $dateFrom . ' - ' . $dateTo . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }
}
