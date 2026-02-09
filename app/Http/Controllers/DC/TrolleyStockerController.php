<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\Trolley;
use App\Models\Dc\TrolleyStocker;
use App\Models\Stocker\Stocker;
use App\Models\Dc\LoadingLine;
use App\Models\Dc\LoadingLinePlan;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class TrolleyStockerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $trolleyStocks = Trolley::selectRaw("
                trolley.id,
                trolley_stocker.tanggal_alokasi,
                stocker.act_costing_ws,
                (CASE WHEN stocker.tipe = 'REJECT' THEN stocker.style ELSE master_sb_ws.styleno END) style,
                stocker.color,
                trolley.nama_trolley,
                SUM(stocker.qty_ply) qty
            ")->
            leftJoin("trolley_stocker", function($join)
                {
                    $join->on('trolley_stocker.trolley_id', '=', 'trolley.id');
                    $join->on('trolley_stocker.status', '=', DB::raw('"active"'));
                }
            )->
            leftJoin(
                DB::raw('
                    (
                        SELECT
                            stocker_input.id,
                            stocker_input.so_det_id,
                            (CASE WHEN stocker_input.form_piece_id > 0 THEN stocker_input.form_piece_id ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN stocker_input.form_reject_id ELSE stocker_input.form_cut_id END) END) form_cut_id,
                            stocker_input.act_costing_ws,
                            stocker_input.color,
                            COALESCE (
                                COALESCE(
                                    (
                                        MAX(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0))
                                        - MAX(COALESCE ( dc_in_input.qty_reject, 0 )) + MAX(COALESCE ( dc_in_input.qty_replace, 0 ))
                                        - MAX(COALESCE ( secondary_inhouse_input.qty_reject, 0 )) + MAX(COALESCE ( secondary_inhouse_input.qty_replace, 0 ))
                                        - MAX(COALESCE ( secondary_in_input.qty_reject, 0 )) + MAX(COALESCE ( secondary_in_input.qty_replace, 0 ))
                                    )
                                , 0),
                                COALESCE ( stocker_input.qty_ply_mod, stocker_input.qty_ply, 0)
                            ) qty_ply,
                            form_cut_reject.style,
                            form_cut_input.id_marker,
                            (CASE WHEN stocker_input.form_reject_id > 0 THEN "REJECT" ELSE "NORMAL" END) tipe
                        FROM
                            stocker_input
                            LEFT JOIN form_cut_input ON form_cut_input.id = stocker_input.form_cut_id
                            LEFT JOIN form_cut_reject ON form_cut_reject.id = stocker_input.form_reject_id
                            LEFT JOIN form_cut_piece ON form_cut_piece.id = stocker_input.form_piece_id
                            LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_in_input ON secondary_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input ON secondary_inhouse_input.id_qr_stocker = stocker_input.id_qr_stocker
                            LEFT JOIN trolley_stocker ON trolley_stocker.stocker_id = stocker_input.id
                        WHERE
                            trolley_stocker.id is not null AND
                            stocker_input.id is not null AND
                            trolley_stocker.`status` = "active"
                        GROUP BY
                            stocker_input.form_cut_id,
                            stocker_input.form_reject_id,
                            stocker_input.form_piece_id,
                            stocker_input.so_det_id,
                            stocker_input.group_stocker,
                            stocker_input.ratio
                    ) stocker
                '),
                'stocker.id', '=', 'trolley_stocker.stocker_id'
            )->
            leftJoin("marker_input", "marker_input.kode", "=", "stocker.id_marker")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker.so_det_id")->
            groupBy('trolley.id', 'stocker.act_costing_ws', 'marker_input.style', 'stocker.color')->
            orderByRaw("ISNULL(SUM(stocker.qty_ply)) asc")->
            orderByRaw("CAST(trolley.nama_trolley AS UNSIGNED) asc")->
            orderByRaw("trolley.id asc")->
            get();

        return view('dc.trolley.stock-trolley.stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolleyStocks' => $trolleyStocks]);
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

    public function allocate(Request $request)
    {
        if ($request->ajax()) {
            $trolley = TrolleyStocker::selectRaw("
                    trolley_stocker.id,
                    GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker ORDER BY stocker_input.id ASC SEPARATOR ', ') id_qr_stocker,
                    stocker_input.act_costing_ws,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END) END) no_cut,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) style,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                    stocker_input.color,
                    CONCAT(users.username, ' (',trolley_stocker.updated_at, ')') user,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    COALESCE(MIN(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) ), COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply)) qty,
                    CONCAT(MIN(stocker_input.range_awal), ' - ', MAX(stocker_input.range_akhir), (CONCAT(' (', MIN( COALESCE((stocker_input.qty_ply_mod - stocker_input.qty_ply), 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) ), ') ' ))) rangeAwalAkhir
                ")->
                leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
                leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
                leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_detail as part_detail_com", function ($join) {
                    $join->on("part_detail_com.id", "=", "part_detail.from_part_detail");
                    $join->on("part_detail.part_status", "=", DB::raw("'complement'"));
                })->
                leftJoin("part as part_com", "part_com.id", "=", "part_detail_com.part_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("users", "users.id", "=", "trolley_stocker.created_by")->
                where('trolley_id', $request->trolley_id)->
                where('trolley_stocker.status', "active")->
                // where('stocker_input.status', "trolley")->
                groupBy('form_cut_input.no_cut', 'form_cut_piece.no_cut', 'stocker_input.form_cut_id', 'stocker_input.form_reject_id', 'stocker_input.form_piece_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio', 'stocker_input.stocker_reject')->
                orderBy('trolley_stocker.updated_at', 'desc');

            return DataTables::eloquent($trolley)->
                filterColumn('id', function($query, $keyword) {
                    $query->whereRaw("trolley_stocker.id LIKE '%".$keyword."%'");
                })->
                filterColumn('id_qr_stocker', function($query, $keyword) {
                    $query->whereRaw("stocker_input.id_qr_stocker LIKE '%".$keyword."%'");
                })->
                filterColumn('act_costing_ws', function($query, $keyword) {
                    $query->whereRaw("stocker_input.act_costing_ws LIKE '%".$keyword."%'");
                })->
                filterColumn('no_cut', function($query, $keyword) {
                    $query->whereRaw("COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut) LIKE '%".$keyword."%'");
                })->
                filterColumn('style', function($query, $keyword) {
                    $query->whereRaw("(CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) LIKE '%".$keyword."%'");
                })->
                filterColumn('color', function($query, $keyword) {
                    $query->whereRaw("stocker_input.color LIKE '%".$keyword."%'");
                })->
                filterColumn('panel', function($query, $keyword) {
                    $query->whereRaw("COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) '%".$keyword."%'");
                })->
                filterColumn('nama_part', function($query, $keyword) {
                    $query->whereRaw("master_part.nama_part LIKE '%".$keyword."%'");
                })->
                filterColumn('qty', function($query, $keyword) {
                    $query->whereRaw("stocker_input.qty_ply LIKE '%".$keyword."%'");
                })->
                filterColumn('rangeAwalAkhir', function($query, $keyword) {
                    $query->whereRaw("CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) LIKE '%".$keyword."%'");
                })->
                filterColumn('size', function($query, $keyword) {
                    $query->whereRaw("stocker_input.size LIKE '%".$keyword."%'");
                })->
                toJson();
        }

        $trolleys = Trolley::orderBy('nama_trolley', 'asc')->get();

        return view('dc.trolley.stock-trolley.allocate-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolleys' => $trolleys]);
    }

    public function allocateThis(Request $request, $id)
    {
        if ($request->ajax()) {
            $trolley = TrolleyStocker::selectRaw("
                    trolley_stocker.id,
                    GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker ORDER BY stocker_input.id ASC SEPARATOR ', ') id_qr_stocker,
                    stocker_input.act_costing_ws,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END) END) no_cut,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) style,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                    stocker_input.color,
                    CONCAT(users.username, ' (',trolley_stocker.updated_at, ')') user,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    COALESCE(MIN(COALESCE(dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) ), COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply)) qty,
                    CONCAT(MIN(stocker_input.range_awal), ' - ', MAX(stocker_input.range_akhir), (CONCAT(' (', MIN( COALESCE((COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply) - stocker_input.qty_ply), 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) ), ') ' ))) rangeAwalAkhir
                ")->
                leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
                leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "stocker_input.so_det_id")->
                leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
                leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
                leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
                leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
                leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                leftJoin("part", "part.id", "=", "part_detail.part_id")->
                leftJoin("part_detail as part_detail_com", function ($join) {
                    $join->on("part_detail_com.id", "=", "part_detail.from_part_detail");
                    $join->on("part_detail.part_status", "=", DB::raw("'complement'"));
                })->
                leftJoin("part as part_com", "part_com.id", "=", "part_detail_com.part_id")->
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("users", "users.id", "=", "trolley_stocker.created_by")->
                where('trolley_id', $id)->
                where('trolley_stocker.status', "active")->
                // where('stocker_input.status', "trolley")->
                groupBy('form_cut_input.no_cut', 'stocker_input.form_cut_id', 'stocker_input.form_reject_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio', 'stocker_input.stocker_reject')->
                orderBy('trolley_stocker.updated_at', 'desc');

            return DataTables::eloquent($trolley)->
                filterColumn('id', function($query, $keyword) {
                    $query->whereRaw("trolley_stocker.id LIKE '%".$keyword."%'");
                })->
                filterColumn('id_qr_stocker', function($query, $keyword) {
                    $query->whereRaw("stocker_input.id_qr_stocker LIKE '%".$keyword."%'");
                })->
                filterColumn('act_costing_ws', function($query, $keyword) {
                    $query->whereRaw("stocker_input.act_costing_ws LIKE '%".$keyword."%'");
                })->
                filterColumn('no_cut', function($query, $keyword) {
                    $query->whereRaw("COALESCE(form_cut_input.no_cut, form_cut_piece.no_cut) LIKE '%".$keyword."%'");
                })->
                filterColumn('style', function($query, $keyword) {
                    $query->whereRaw("(CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) LIKE '%".$keyword."%'");
                })->
                filterColumn('color', function($query, $keyword) {
                    $query->whereRaw("stocker_input.color LIKE '%".$keyword."%'");
                })->
                filterColumn('panel', function($query, $keyword) {
                    $query->whereRaw("COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) '%".$keyword."%'");
                })->
                filterColumn('nama_part', function($query, $keyword) {
                    $query->whereRaw("master_part.nama_part LIKE '%".$keyword."%'");
                })->
                filterColumn('qty', function($query, $keyword) {
                    $query->whereRaw("stocker_input.qty_ply LIKE '%".$keyword."%'");
                })->
                filterColumn('rangeAwalAkhir', function($query, $keyword) {
                    $query->whereRaw("CONCAT(stocker_input.range_awal, ' - ', stocker_input.range_akhir) LIKE '%".$keyword."%'");
                })->
                filterColumn('size', function($query, $keyword) {
                    $query->whereRaw("stocker_input.size LIKE '%".$keyword."%'");
                })->
                toJson();
        }

        $trolley = Trolley::with('userLine')->where('id', $id)->first();

        return view('dc.trolley.stock-trolley.allocate-this-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolley' => $trolley]);
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

    public function storeAllocate(Request $request)
    {
        $validatedRequest = $request->validate([
            "trolley_id" => "required",
            "stocker_id" => "required",
        ]);

        $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
        $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

        $stockerData = Stocker::where("id", $validatedRequest["stocker_id"])->first();

        $similarStockerData = Stocker::selectRaw("stocker_input.*, COALESCE(master_secondary.tujuan, master_secondary_multi.tujuan) as tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id, loading_line.id as loading_line_id, loading_line.nama_line as loading_line_name")->
            where(($stockerData->form_piece_id > 0 ? "form_piece_id" : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")), ($stockerData->form_piece_id > 0 ? $stockerData->form_piece_id : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id : $stockerData->form_cut_id)))->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
            leftJoin(DB::raw("
                (
                    SELECT
                        stocker_input.id_qr_stocker,
                        MAX( part_detail_secondary.urutan ) AS max_urutan
                    FROM
                        stocker_input
                        LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                    GROUP BY
                        id_qr_stocker
                    HAVING
                        MAX( part_detail_secondary.urutan ) IS NOT NULL
                ) as pds
            "), "pds.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("part_detail_secondary", function ($join) {
                $join->on("part_detail_secondary.part_detail_id", "=", "stocker_input.part_detail_id");
                $join->on("part_detail_secondary.urutan", "=", "pds.max_urutan");
            })->
            leftJoin(DB::raw("master_secondary as master_secondary_multi"), "master_secondary_multi.id", "=", "part_detail_secondary.master_secondary_id")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
            where("stocker_reject", $stockerData->stocker_reject)->
            get();

        $incompleteLoading = $similarStockerData->whereNull("loading_line_id");

        if ($incompleteLoading->count() < 1) {
            return array(
                'status' => 400,
                'message' => "Stocker sudah di loading ke line",
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'clearStockerId();',
                'additional' => [],
            );
        }

        $incompleteNonSecondary = $similarStockerData->whereIn("tujuan", ["NON SECONDARY", "SECONDARY DALAM", "SECONDARY LUAR"])->
            whereNull("dc_id");

        $incompleteSecondary = $similarStockerData->whereIn("tujuan", ["SECONDARY DALAM", "SECONDARY LUAR"])->
            whereNull("secondary_id");

        if ($incompleteNonSecondary->count() > 0 || $incompleteSecondary->count() > 0) {
            return array(
                'status' => 400,
                'message' =>
                    "Stocker tidak bisa dialokasikan".
                    ($incompleteNonSecondary->count() > 0 ? "<br><br> Stocker belum masuk DC In : <br> <b>".$incompleteNonSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("create-dc-in")."' class='text-sb' target='_blank'>Ke DC In</a></u>" : "").
                    ($incompleteSecondary->count() > 0 ? "<br><br> Stocker Secondary belum masuk Secondary In : <br> <b>".$incompleteSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("secondary-in")."' class='text-sb' target='_blank'>Ke Secondary In</a></u>" : ""),
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'clearStockerId();',
                'additional' => [],
            );
        }

        $trolleyStockArr = [];

        $i = 0;
        foreach ($similarStockerData as $stocker) {
            array_push($trolleyStockArr, [
                "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                "trolley_id" => $validatedRequest['trolley_id'],
                "stocker_id" => $stocker['id'],
                "status" => "active",
                "tanggal_alokasi" => date('Y-m-d'),
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now(),
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username
            ]);

            $i++;
        }

        $storeTrolleyStock = TrolleyStocker::upsert($trolleyStockArr, ['stocker_id'], ['trolley_id', 'status', 'tanggal_alokasi', 'created_at', 'updated_at', 'created_by', 'created_by_username']);

        if (count($trolleyStockArr) > 0) {
            $updateStocker = Stocker::where(($stockerData->form_piece_id > 0 ? "form_piece_id" : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")), ($stockerData->form_piece_id > 0 ? $stockerData->form_piece_id : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id : $stockerData->form_cut_id)))->
                where("so_det_id", $stockerData->so_det_id)->
                where("group_stocker", $stockerData->group_stocker)->
                where("ratio", $stockerData->ratio)->
                where("stocker_reject", $stockerData->stocker_reject)->
                update([
                    "status" => "trolley",
                    "latest_alokasi" => Carbon::now()
                ]);

            if ($updateStocker) {
                return array(
                    'status' => 202,
                    'message' => 'Stocker berhasil dialokasi',
                    'redirect' => '',
                    'table' => 'trolley-stock-datatable',
                    'callback' => 'clearStockerId();',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Stocker gagal dialokasi',
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'clearStockerId();',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal dialokasi',
            'redirect' => '',
            'table' => 'trolley-stock-datatable',
            'callback' => 'clearStockerId();',
            'additional' => [],
        );
    }

    public function storeAllocateThis(Request $request)
    {
        $validatedRequest = $request->validate([
            "trolley_id" => "required",
            "stocker_id" => "required",
        ]);

        $lastTrolleyStock = TrolleyStocker::select('kode')->orderBy('id', 'desc')->first();
        $trolleyStockNumber = $lastTrolleyStock ? intval(substr($lastTrolleyStock->kode, -5)) + 1 : 1;

        $stockerData = Stocker::where("id", $validatedRequest["stocker_id"])->first();
        $similarStockerData = Stocker::selectRaw("stocker_input.*, COALESCE(master_secondary.tujuan, master_secondary_multi.tujuan) as tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id, loading_line.id as loading_line_id, loading_line.nama_line as loading_line_name")->
            where(($stockerData->form_piece_id > 0 ? "form_piece_id" : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")), ($stockerData->form_piece_id > 0 ? $stockerData->form_piece_id : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id : $stockerData->form_cut_id)))->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
            leftJoin(DB::raw("
                (
                    SELECT
                        stocker_input.id_qr_stocker,
                        MAX( part_detail_secondary.urutan ) AS max_urutan
                    FROM
                        stocker_input
                        LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                    GROUP BY
                        id_qr_stocker
                    HAVING
                        MAX( part_detail_secondary.urutan ) IS NOT NULL
                ) as pds
            "), "pds.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("part_detail_secondary", function ($join) {
                $join->on("part_detail_secondary.part_detail_id", "=", "part_detail.id");
                $join->on("part_detail_secondary.urutan", "=", "pds.max_urutan");
            })->
            leftJoin(DB::raw("master_secondary as master_secondary_multi"), "master_secondary_multi.id", "=", "part_detail_secondary.master_secondary_id")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
            where("stocker_reject", $stockerData->stocker_reject)->
            get();

        $incompleteLoading = $similarStockerData->whereNull("loading_line_id");

        if ($incompleteLoading->count() < 1) {
            return array(
                'status' => 400,
                'message' => "Stocker sudah di loading ke line",
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'clearStockerId();',
                'additional' => [],
            );
        }

        $incompleteNonSecondary = $similarStockerData->whereIn("tujuan", ["NON SECONDARY", "SECONDARY DALAM", "SECONDARY LUAR"])->
            whereNull("dc_id");

        $incompleteSecondary = $similarStockerData->whereIn("tujuan", ["SECONDARY DALAM", "SECONDARY LUAR"])->
            whereNull("secondary_id");

        if ($incompleteNonSecondary->count() > 0 || $incompleteSecondary->count() > 0) {
            return array(
                'status' => 400,
                'message' =>
                    "Stocker tidak bisa dialokasikan".
                    ($incompleteNonSecondary->count() > 0 ? "<br><br> Stocker belum masuk DC In : <br> <b>".$incompleteNonSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("create-dc-in")."' class='text-sb' target='_blank'>Ke DC In</a></u>" : "").
                    ($incompleteSecondary->count() > 0 ? "<br><br> Stocker Secondary belum masuk Secondary In : <br> <b>".$incompleteSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("secondary-in")."' class='text-sb' target='_blank'>Ke Secondary In</a></u>" : ""),
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'clearStockerId();',
                'additional' => [],
            );
        }

        $trolleyStockArr = [];

        $i = 0;
        foreach ($similarStockerData as $stocker) {
            array_push($trolleyStockArr, [
                "kode" => "TLS".sprintf('%05s', ($trolleyStockNumber+$i)),
                "trolley_id" => $validatedRequest['trolley_id'],
                "stocker_id" => $stocker['id'],
                "status" => "active",
                "tanggal_alokasi" => date('Y-m-d'),
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username
            ]);

            $i++;
        }

        $storeTrolleyStock = TrolleyStocker::upsert($trolleyStockArr, ['stocker_id'], ['trolley_id', 'status', 'tanggal_alokasi', 'created_at', 'updated_at', 'created_by', 'created_by_username']);

        if (count($trolleyStockArr) > 0) {
            $updateStocker = Stocker::where(($stockerData->form_piece_id > 0 ? "form_piece_id" : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")), ($stockerData->form_piece_id > 0 ? $stockerData->form_piece_id : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id : $stockerData->form_cut_id)))->
                where("so_det_id", $stockerData->so_det_id)->
                where("group_stocker", $stockerData->group_stocker)->
                where("ratio", $stockerData->ratio)->
                where("stocker_reject", $stockerData->stocker_reject)->
                update([
                    "status" => "trolley",
                    "latest_alokasi" => Carbon::now()
                ]);

            if ($updateStocker) {
                return array(
                    'status' => 202,
                    'message' => 'Stocker berhasil dialokasi',
                    'redirect' => '',
                    'table' => 'trolley-stock-datatable',
                    'callback' => 'trolleyStockDatatableReload(); clearStockerId();',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Stocker gagal dialokasi',
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'trolleyStockDatatableReload(); clearStockerId();',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal dialokasi',
            'redirect' => '',
            'table' => 'trolley-stock-datatable',
            'callback' => 'trolleyStockDatatableReload(); clearStockerId',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Dc\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function show(TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Dc\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function edit(TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dc\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Dc\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrolleyStocker $trolleyStocker, $id)
    {
        $getTrolleyStockData = TrolleyStocker::where("id", $id)->first();

        $stockerData = Stocker::where("id", $getTrolleyStockData->stocker_id)->first();

        $deleteTrolleyStock = TrolleyStocker::leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
            whereRaw("trolley_stocker.trolley_id = '".$getTrolleyStockData->trolley_id."'")->
            whereRaw("( CASE WHEN stocker_input.form_cut_id > 0 THEN stocker_input.form_cut_id ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN stocker_input.form_reject_id ELSE ( CASE WHEN stocker_input.form_piece_id > 0 THEN stocker_input.form_piece_id ELSE null END ) END ) END ) = '".($stockerData->form_cut_id ?: $stockerData->form_reject_id ?: $stockerData->form_piece_id)."'")->
            where("stocker_input.so_det_id", $stockerData->so_det_id)->
            where("stocker_input.group_stocker", $stockerData->group_stocker)->
            where("stocker_input.ratio", $stockerData->ratio)->
            where("stocker_input.stocker_reject", $stockerData->stocker_reject)->
            delete();

        if ($deleteTrolleyStock) {
            $updateStocker = Stocker::whereRaw("( CASE WHEN stocker_input.form_cut_id > 0 THEN stocker_input.form_cut_id ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN stocker_input.form_reject_id ELSE ( CASE WHEN stocker_input.form_piece_id > 0 THEN stocker_input.form_piece_id ELSE null END ) END ) END ) = '".($stockerData->form_cut_id ?: $stockerData->form_reject_id ?: $stockerData->form_piece_id)."'")->
                where("stocker_input.so_det_id", $stockerData->so_det_id)->
                where("stocker_input.group_stocker", $stockerData->group_stocker)->
                where("stocker_input.ratio", $stockerData->ratio)->
                where("stocker_input.stocker_reject", $stockerData->stocker_reject)->
                update([
                    "status" => "idle",
                    "latest_alokasi" => Carbon::now()
                ]);

            if ($updateStocker) {
                return array(
                    'status' => 200,
                    'message' => 'Stocker berhasil disingkirkan',
                    'redirect' => '',
                    'table' => 'trolley-stock-datatable',
                    'callback' => 'trolleyStockDatatableReload()',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Stocker gagal  disingkirkan',
                'redirect' => '',
                'table' => 'trolley-stock-datatable',
                'callback' => 'trolleyStockDatatableReload()',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker gagal  disingkirkan',
            'redirect' => '',
            'table' => 'trolley-stock-datatable',
            'callback' => 'trolleyStockDatatableReload()',
            'additional' => [],
        );
    }

    public function send(Request $request, $id) {
        $trolley = Trolley::with('userLine')->where('id', $id)->first();

        $lines = UserLine::where('Groupp', 'SEWING')->whereRaw('(Locked != 1 || Locked is NULL)')->orderBy('line_id', 'asc')->get();
        $trolleys = Trolley::with('userLine')->where('id', "!=", $id)->orderBy("nama_trolley")->get();

        $trolleyStocks = collect(DB::select("
            SELECT
                id,
                GROUP_CONCAT( DISTINCT stocker_id ORDER BY stocker_id ASC SEPARATOR ', ' ) stocker_id,
                GROUP_CONCAT( DISTINCT id_qr_stocker ORDER BY id ASC SEPARATOR ', ' ) id_qr_stocker,
                act_costing_ws,
                no_cut,
                style,
                tipe,
                color,
                panel,
                GROUP_CONCAT( DISTINCT nama_part SEPARATOR ', ' ) nama_part,
                size,
                COALESCE (MAX(qty_main), MIN(qty), 0) as qty,
                rangeAwalAkhir,
                MAX( user ) as user
            FROM
            (
                SELECT
                    trolley_stocker.id,
                    stocker_input.id as stocker_id,
                    stocker_input.id_qr_stocker as id_qr_stocker,
                    stocker_input.act_costing_ws,
                    COALESCE(form_cut_input.id, form_cut_piece.id, form_cut_reject.id) as form_cut_id,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END )  END  ) no_cut,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END )  END ) style,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END ) END ) tipe,
                    stocker_input.color,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
                    CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL THEN CONCAT(' - ', part_detail.part_status) ELSE '' END)) nama_part,
                    COALESCE ( master_sb_ws.size, stocker_input.size ) size,
                    COALESCE (last_in.qty_in, MIN(COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0 ) - COALESCE ( dc_in_input.qty_reject, 0 ) + COALESCE ( dc_in_input.qty_replace, 0 ) - COALESCE ( secondary_inhouse_input.qty_reject, 0 ) + COALESCE ( secondary_inhouse_input.qty_replace, 0 ) - COALESCE ( secondary_in_input.qty_reject, 0 ) + COALESCE ( secondary_in_input.qty_replace, 0 ) )) qty_main,
                    COALESCE (last_in.qty_in, MIN(COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0 ) - COALESCE ( dc_in_input.qty_reject, 0 ) + COALESCE ( dc_in_input.qty_replace, 0 ) - COALESCE ( secondary_inhouse_input.qty_reject, 0 ) + COALESCE ( secondary_inhouse_input.qty_replace, 0 ) - COALESCE ( secondary_in_input.qty_reject, 0 ) + COALESCE ( secondary_in_input.qty_replace, 0 ) )) qty,
                    CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir ), ( CONCAT( ' (', MIN( COALESCE (( stocker_input.qty_ply_mod - stocker_input.qty_ply ), 0 ) + COALESCE ( dc_in_input.qty_replace, 0 ) - COALESCE ( dc_in_input.qty_reject, 0 ) + COALESCE ( secondary_inhouse_input.qty_replace, 0 ) - COALESCE ( secondary_inhouse_input.qty_reject, 0 ) + COALESCE ( secondary_in_input.qty_replace, 0 ) - COALESCE ( secondary_in_input.qty_reject, 0 )  ), ') ' ))) rangeAwalAkhirOld,
                    CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir )) rangeAwalAkhir,
                    CONCAT( users.username, ' (', trolley_stocker.updated_at, ')' ) USER,
                    stocker_input.so_det_id,
                    stocker_input.group_stocker,
                    stocker_input.ratio,
                    stocker_input.stocker_reject
                FROM
                    `trolley_stocker`
                    LEFT JOIN `stocker_input` ON `stocker_input`.`id` = `trolley_stocker`.`stocker_id`
                    LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `stocker_input`.`so_det_id`
                    LEFT JOIN `dc_in_input` ON `dc_in_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `secondary_in_input` ON `secondary_in_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `secondary_inhouse_input` ON `secondary_inhouse_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `form_cut_input` ON `form_cut_input`.`id` = `stocker_input`.`form_cut_id`
                    LEFT JOIN `form_cut_reject` ON `form_cut_reject`.`id` = `stocker_input`.`form_reject_id`
                    LEFT JOIN `form_cut_piece` ON `form_cut_piece`.`id` = `stocker_input`.`form_piece_id`
                    LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                    left join part_detail on stocker_input.part_detail_id = part_detail.id
                    left join part on part.id = part_detail.part_id
                    left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                    left join part part_com on part_com.id = part_detail_com.part_id
                    LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                    LEFT JOIN `users` ON `users`.`id` = `trolley_stocker`.`created_by`
                    LEFT JOIN  (
                        SELECT
                            stocker_input.id_qr_stocker,
                            MAX( part_detail_secondary.urutan ) AS max_urutan
                        FROM
                            stocker_input
                            LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                            LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                            LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                        GROUP BY
                            id_qr_stocker
                        HAVING
                             MAX( part_detail_secondary.urutan ) IS NOT NULL
                    ) AS multi_secondary ON `multi_secondary`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `secondary_in_input` AS `last_in` ON `last_in`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    AND `last_in`.`urutan` >= `multi_secondary`.`max_urutan`
                WHERE
                    `trolley_id` = ".$id."
                    AND `trolley_stocker`.`status` = 'active'
                    AND `stocker_input`.`status` != 'line'
                    AND part_detail.part_status = 'main'
                GROUP BY
                    stocker_input.id_qr_stocker
                UNION ALL
                SELECT
                    trolley_stocker.id,
                    stocker_input.id as stocker_id,
                    stocker_input.id_qr_stocker as id_qr_stocker,
                    stocker_input.act_costing_ws,
                    COALESCE(form_cut_input.id, form_cut_piece.id, form_cut_reject.id) as form_cut_id,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END )  END  ) no_cut,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END )  END ) style,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE ( CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END ) END ) tipe,
                    stocker_input.color,
                    COALESCE(CONCAT(part_com.panel, (CASE WHEN part_com.panel_status IS NOT NULL THEN CONCAT(' - ', part_com.panel_status) ELSE '' END)), CONCAT(part.panel, (CASE WHEN part.panel_status IS NOT NULL THEN CONCAT(' - ', part.panel_status) ELSE '' END))) panel,
                    CONCAT(master_part.nama_part, (CASE WHEN part_detail.part_status IS NOT NULL THEN CONCAT(' - ', part_detail.part_status) ELSE '' END)) nama_part,
                    COALESCE ( master_sb_ws.size, stocker_input.size ) size,
                    null as qty_main,
                    COALESCE (last_in.qty_in, MIN(COALESCE ( dc_in_input.qty_awal, stocker_input.qty_ply_mod, stocker_input.qty_ply, 0 ) - COALESCE ( dc_in_input.qty_reject, 0 ) + COALESCE ( dc_in_input.qty_replace, 0 ) - COALESCE ( secondary_inhouse_input.qty_reject, 0 ) + COALESCE ( secondary_inhouse_input.qty_replace, 0 ) - COALESCE ( secondary_in_input.qty_reject, 0 ) + COALESCE ( secondary_in_input.qty_replace, 0 ) )) qty,
                    CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir ), ( CONCAT( ' (', MIN( COALESCE (( stocker_input.qty_ply_mod - stocker_input.qty_ply ), 0 ) + COALESCE ( dc_in_input.qty_replace, 0 ) - COALESCE ( dc_in_input.qty_reject, 0 ) + COALESCE ( secondary_inhouse_input.qty_replace, 0 ) - COALESCE ( secondary_inhouse_input.qty_reject, 0 ) + COALESCE ( secondary_in_input.qty_replace, 0 ) - COALESCE ( secondary_in_input.qty_reject, 0 )  ), ') ' ))) rangeAwalAkhirOld,
                    CONCAT( MIN( stocker_input.range_awal ), ' - ', MAX( stocker_input.range_akhir )) rangeAwalAkhir,
                    CONCAT( users.username, ' (', trolley_stocker.updated_at, ')' ) USER,
                    stocker_input.so_det_id,
                    stocker_input.group_stocker,
                    stocker_input.ratio,
                    stocker_input.stocker_reject
                FROM
                    `trolley_stocker`
                    LEFT JOIN `stocker_input` ON `stocker_input`.`id` = `trolley_stocker`.`stocker_id`
                    LEFT JOIN `master_sb_ws` ON `master_sb_ws`.`id_so_det` = `stocker_input`.`so_det_id`
                    LEFT JOIN `dc_in_input` ON `dc_in_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `secondary_in_input` ON `secondary_in_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `secondary_inhouse_input` ON `secondary_inhouse_input`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `form_cut_input` ON `form_cut_input`.`id` = `stocker_input`.`form_cut_id`
                    LEFT JOIN `form_cut_reject` ON `form_cut_reject`.`id` = `stocker_input`.`form_reject_id`
                    LEFT JOIN `form_cut_piece` ON `form_cut_piece`.`id` = `stocker_input`.`form_piece_id`
                    LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                    left join part_detail on stocker_input.part_detail_id = part_detail.id
                    left join part on part.id = part_detail.part_id
                    left join part_detail part_detail_com on part_detail_com.id = part_detail.from_part_detail and part_detail.part_status = 'complement'
                    left join part part_com on part_com.id = part_detail_com.part_id
                    LEFT JOIN `master_part` ON `master_part`.`id` = `part_detail`.`master_part_id`
                    LEFT JOIN `users` ON `users`.`id` = `trolley_stocker`.`created_by`
                    LEFT JOIN  (
                    SELECT
                        stocker_input.id_qr_stocker,
                        MAX( part_detail_secondary.urutan ) AS max_urutan
                    FROM
                        stocker_input
                        LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                        LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                        LEFT JOIN master_secondary ON master_secondary.id = part_detail_secondary.master_secondary_id
                    GROUP BY
                        id_qr_stocker
                    HAVING
                        MAX( part_detail_secondary.urutan ) IS NOT NULL
                    ) AS multi_secondary ON `multi_secondary`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    LEFT JOIN `secondary_in_input` AS `last_in` ON `last_in`.`id_qr_stocker` = `stocker_input`.`id_qr_stocker`
                    AND `last_in`.`urutan` >= `multi_secondary`.`max_urutan`
                WHERE
                    `trolley_id` = ".$id."
                    AND `trolley_stocker`.`status` = 'active'
                    AND `stocker_input`.`status` != 'line'
                    AND (part_detail.part_status != 'main' OR part_detail.part_status IS NULL)
                GROUP BY
                    stocker_input.id_qr_stocker
            ) a
            GROUP BY
                `no_cut`,
                `form_cut_id`,
                `so_det_id`,
                `group_stocker`,
                `ratio`,
                `stocker_reject`
        "));

        return view('dc.trolley.stock-trolley.send-stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolley' => $trolley, 'lines' => $lines, 'trolleys' => $trolleys, 'trolleyStocks' => $trolleyStocks]);
    }

    public function submitSend(Request $request) {
        $success = [];
        $fail = [];
        $exist = [];

        $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
        $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;

        $lineData = UserLine::where("line_id", $request->line_id)->first();

        // Get costing Data function
        function getCostingDataTrolley($data, $field) {
            if (isset($data->masterSbWs)) {
                switch ($field) {
                    case "act_costing_id" :
                        $field = "id_act_cost";
                        break;
                    case "act_costing_ws" :
                        $field = "ws";
                        break;
                    case "style" :
                        $field = "styleno";
                        break;
                }

                if (isset($data->masterSbWs->{$field})) {
                    return $data->masterSbWs->{$field};
                }
            } elseif (isset($data->formPiece->{$field})) {
                return $data->formPiece->{$field};
            } elseif (isset($data->formReject->{$field})) {
                return $data->formReject->{$field};
            } elseif (isset($data->formCut->marker->{$field})) {
                return $data->formCut->marker->{$field};
            }
            return null;
        }

        // When Loading to Line
        if ($request->destination != "trolley") {
            foreach ($request->selectedStocker as $req) {
                $loadingStockArr = [];

                $stockerIds = explode(',', $req['stocker_ids']);
                $stockerIdsStr = addQuotesAround(str_replace(', ', ' \n ', $req['stocker_ids']));

                $stockerData = Stocker::selectRaw("stocker_input.*, COALESCE(multi_secondary.tujuan, master_secondary.tujuan) as tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id, multi_secondary.max_urutan, multi_secondary.last_in_id as last_in_id")->
                    leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                    leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                    leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin(DB::raw("
                        (
                            SELECT
                                multi_secondary.id_qr_stocker,
                                multi_secondary.max_urutan,
                                master_secondary.tujuan,
                                master_secondary.proses,
                                COALESCE(secondary_in_input.id, dc_in_input.id) as last_in_id
                            FROM
                            (
                                SELECT
                                    stocker_input.id_qr_stocker,
                                    part_detail.id as part_detail_id,
                                    MAX(part_detail_secondary.urutan) as max_urutan
                                FROM stocker_input
                                LEFT JOIN part_detail ON part_detail.id = stocker_input.part_detail_id
                                LEFT JOIN part_detail_secondary ON part_detail_secondary.part_detail_id = stocker_input.part_detail_id
                                WHERE
                                    stocker_input.id is not null
                                    ".(strlen($stockerIdsStr) > 0 ? " and stocker_input.id in (".$stockerIdsStr.") " : "")."
                                GROUP BY
                                    id_qr_stocker
                                HAVING
                                    MAX(part_detail_secondary.urutan) is not null
                            ) as multi_secondary
                            left join part_detail_secondary on part_detail_secondary.urutan = multi_secondary.max_urutan and part_detail_secondary.part_detail_id = multi_secondary.part_detail_id
                            left join master_secondary on master_secondary.id = part_detail_secondary.master_secondary_id
                            left join secondary_in_input on secondary_in_input.id_qr_stocker = multi_secondary.id_qr_stocker and secondary_in_input.urutan = multi_secondary.max_urutan and master_secondary.tujuan != 'NON SECONDARY'
                            left join dc_in_input on dc_in_input.id_qr_stocker = multi_secondary.id_qr_stocker and master_secondary.tujuan = 'NON SECONDARY'
                        ) as multi_secondary
                    "), "multi_secondary.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    whereIn("stocker_input.id", $stockerIds)->
                    get();

                // Check Stocker Processes
                $incompleteNonSecondary = collect([]);
                $incompleteSecondary = collect([]);
                $incompleteMultiMsg = "";
                foreach($stockerData as $stocker) {
                    if ($stocker->max_urutan == null) {
                        if (($stocker->tujuan == "NON SECONDARY" || $stocker->tujuan == NULL) && $stocker->dc_id == null) {
                            $incompleteNonSecondary->push($stocker);
                        }

                        if ($stocker->tujuan == "SECONDARY" && $stocker->secondary_id == null) {
                            $incompleteSecondary->push($stocker);
                        }
                    } else {
                        if ($stocker->tujuan != "NON SECONDARY" && $stocker->tujuan != NULL) {
                            if ($stocker->urutan < $stocker->max_urutan) {
                                $incompleteMultiMsg .= "<br><br> Stocker <b>".$stocker->id_qr_stocker."</b> masih di proses ke <b>".($stocker->urutan ?? 1)."/".$stocker->max_urutan."</b> <br> <u><a href='".route("secondary-inhouse")."' class='text-sb' target='_blank'>Ke Secondary Inhouse</a></u> <br> <u><a href='".route("secondary-in")."' class='text-sb' target='_blank'>Ke Secondary In</a></u>";
                            } else {
                                if (!$stocker->last_in_id) {
                                    $incompleteMultiMsg .= "<br><br> Stocker <b>".$stocker->id_qr_stocker."</b> sudah di proses ke <b>".($stocker->urutan ?? 1)."/".$stocker->max_urutan."</b> belum masuk Secondary IN. <br> <u><a href='".route("secondary-in")."' class='text-sb' target='_blank'>Ke Secondary In</a></u>";
                                }
                            }
                        } else {
                            if (!$stocker->last_in_id) {
                                $incompleteNonSecondary->push($stocker);
                            }
                        }
                    }
                }

                if ($incompleteNonSecondary->count() > 0 || $incompleteSecondary->count() > 0 || strlen($incompleteMultiMsg) > 0) {
                    return array(
                        'status' => 400,
                        'message' =>
                            "Stocker tidak bisa dialokasikan".
                            ($incompleteNonSecondary->count() > 0 ? "<br><br> Stocker Non Secondary belum masuk DC In : <br> <b>".$incompleteNonSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("create-dc-in")."' class='text-sb' target='_blank'>Ke DC In</a></u>" : "").
                            ($incompleteSecondary->count() > 0 ? "<br><br> Stocker Secondary belum masuk Secondary In : <br> <b>".$incompleteSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("secondary-in")."' class='text-sb' target='_blank'>Ke Secondary In</a></u>" : "").
                            ($incompleteMultiMsg),
                        'redirect' => '',
                        'table' => 'trolley-stock-datatable',
                        'callback' => 'clearAll()',
                        'additional' => [],
                    );
                }

                // BatchId
                $batchId = Str::uuid()->toString();

                for ($i = 0; $i < count($stockerIds); $i++) {
                    $thisStockerData = Stocker::where('id', $stockerIds[$i])->first();

                    // Qty
                    $currentQty = 0;

                    $thisStockerPartDetailSecondaries = $thisStockerData->partDetail ? ($thisStockerData->partDetail->secondaries ? $thisStockerData->partDetail->secondaries : null) : null;
                    if ($thisStockerPartDetailSecondaries) {
                        $currentSecondary = $thisStockerPartDetailSecondaries->sortByDesc("urutan")->first();

                        if ($currentSecondary && $currentSecondary->secondary) {
                            if ($currentSecondary->secondary->tujuan != 'NON SECONDARY') {
                                $currentQty = SecondaryIn::where("id_qr_stocker", $thisStockerData->id_qr_stocker)->where("urutan", $currentSecondary->urutan)->value("qty_in");
                            } else {
                                $currentDc = DCIn::where("id_qr_stocker", $thisStockerData->id_qr_stocker)->first();
                                $currentQty = $currentDc->qty_awal - $currentDc->qty_reject + $currentDc->qty_replace;
                            }
                        } else {
                            $currentQty = ($thisStockerData->qty_ply_mod > 0 ? $thisStockerData->qty_ply_mod : $thisStockerData->qty_ply) + ($thisStockerData->dcIn ? ((0 - $thisStockerData->dcIn->qty_reject) + $thisStockerData->dcIn->qty_replace) : 0) + ($thisStockerData->secondaryInHouse ? ((0 - $thisStockerData->secondaryInHouse->qty_reject) + $thisStockerData->secondaryInHouse->qty_replace) : 0) + ($thisStockerData->secondaryIn ? ((0 - $thisStockerData->secondaryIn->qty_reject) + $thisStockerData->secondaryIn->qty_replace) : 0);
                        }
                    } else {
                        $currentQty = ($thisStockerData->qty_ply_mod > 0 ? $thisStockerData->qty_ply_mod : $thisStockerData->qty_ply) + ($thisStockerData->dcIn ? ((0 - $thisStockerData->dcIn->qty_reject) + $thisStockerData->dcIn->qty_replace) : 0) + ($thisStockerData->secondaryInHouse ? ((0 - $thisStockerData->secondaryInHouse->qty_reject) + $thisStockerData->secondaryInHouse->qty_replace) : 0) + ($thisStockerData->secondaryIn ? ((0 - $thisStockerData->secondaryIn->qty_reject) + $thisStockerData->secondaryIn->qty_replace) : 0);
                    }

                    $loadingLinePlan = LoadingLinePlan::where("act_costing_ws", $thisStockerData->act_costing_ws)->where("color", $thisStockerData->color)->where("line_id", $lineData['line_id'])->where("tanggal", $request['tanggal_loading'])->first();

                    $isExist = LoadingLine::where("stocker_id", $stockerIds[$i])->count();
                    if ($isExist < 1) {
                        if ($loadingLinePlan) {
                            array_push($loadingStockArr, [
                                "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                "line_id" => $lineData['line_id'],
                                "loading_plan_id" => $loadingLinePlan['id'],
                                "nama_line" => $lineData['username'],
                                "stocker_id" => $thisStockerData['id'],
                                "qty" => $currentQty,
                                "status" => "active",
                                "tanggal_loading" => $request['tanggal_loading'],
                                "no_bon" => $request['no_bon'],
                                "batch" => $batchId,
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now(),
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username,
                            ]);
                        } else {
                            $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                            $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                            $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                            $storeLoadingPlan = LoadingLinePlan::create([
                                "line_id" => $lineData['line_id'],
                                "kode" => $kodeLoadingPlan,
                                "act_costing_id" => getCostingDataTrolley($thisStockerData, "act_costing_id"),
                                "act_costing_ws" => getCostingDataTrolley($thisStockerData, "act_costing_ws"),
                                "buyer" => getCostingDataTrolley($thisStockerData, "buyer"),
                                "style" => getCostingDataTrolley($thisStockerData, "style"),
                                "color" => getCostingDataTrolley($thisStockerData, "color"),
                                "tanggal" => $request['tanggal_loading'],
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username,
                            ]);

                            array_push($loadingStockArr, [
                                "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                "line_id" => $lineData['line_id'],
                                "loading_plan_id" => $storeLoadingPlan['id'],
                                "nama_line" => $lineData['username'],
                                "stocker_id" => $thisStockerData['id'],
                                "qty" => $currentQty,
                                "status" => "active",
                                "tanggal_loading" => $request['tanggal_loading'],
                                "no_bon" => $request['no_bon'],
                                "batch" => $batchId,
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now(),
                                "created_by" => Auth::user()->id,
                                "created_by_username" => Auth::user()->username,
                            ]);
                        }
                    } else {
                        array_push($exist, ['stocker' => $thisStockerData['id']]);
                    }
                }

                // Store Loading Stock
                $storeLoadingStock = LoadingLine::insert($loadingStockArr);
                // Get Stored Loading Stock
                $storedLoadingStock = LoadingLine::where('batch', $batchId)->pluck("stocker_id")->toArray();

                if (count($storedLoadingStock) > 0) {
                    $updateStocker = Stocker::whereIn("id", $storedLoadingStock)->
                        update([
                            "status" => "line",
                            "latest_alokasi" => Carbon::now()
                        ]);

                    $updateTrolleyStocker = TrolleyStocker::whereIn("stocker_id", $storedLoadingStock)->
                        update([
                            "status" => "not active"
                        ]);

                    if ($updateStocker) {
                        array_push($success, ['stocker' => $storedLoadingStock]);
                    } else {
                        array_push($fail, ['stocker' => $storedLoadingStock]);
                    }
                }
            }
        }
        // When Moving to another Trolley
        else {
            foreach ($request->selectedStocker as $req) {
                $stockerIds = explode(',', $req['stocker_ids']);

                $updateStocker = Stocker::whereIn("id", $stockerIds)->
                    update([
                        "latest_alokasi" => Carbon::now()
                    ]);

                $updateTrolleyStocker = TrolleyStocker::whereIn("stocker_id", $stockerIds)->
                    update([
                        "trolley_id" => $request->destination_trolley_id
                    ]);

                if ($updateStocker && $updateTrolleyStocker) {
                    array_push($success, ['stocker' => $stockerIds]);
                } else {
                    array_push($fail, ['stocker' => $stockerIds]);
                }
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Stocker berhasil dikirim',
                'redirect' => '',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Data tidak ditemukan',
                'redirect' => '',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }
    }

    public function getStockerData($id = 0)
    {
        $scannedStocker = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.act_costing_ws,
                stocker_input.color,
                stocker_input.id_qr_stocker,
                stocker_input.size,
                coalesce(stocker_input.qty_ply_mod, stocker_input.qty_ply) qty_ply,
                stocker_input.status,
                trolley_stocker.id trolley_stock_id,
                loading_line.id line_id,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END) END) no_cut,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.buyer ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.buyer ELSE marker_input.buyer END) END) buyer,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE marker_input.style END) END) style,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe
            ")->
            leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
            leftJoin("form_cut_reject", "form_cut_reject.id", "=", "stocker_input.form_reject_id")->
            leftJoin("form_cut_piece", "form_cut_piece.id", "=", "stocker_input.form_piece_id")->
            leftJoin("marker_input", "marker_input.kode", "form_cut_input.id_marker")->
            leftJoin("trolley_stocker", "trolley_stocker.stocker_id", "=", "stocker_input.id")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
            where('id_qr_stocker', $id)->
            first();

        if ($scannedStocker) {
            if ($scannedStocker->line_id) {
                return json_encode(
                    array(
                        'status' => 400,
                        'message' => 'Stocker sudah ada di sebuah line',
                        'data' => null,
                        'redirect' => '',
                        'additional' => []
                    )
                );
            } else if ($scannedStocker->trolley_stock_id) {
                return json_encode(
                    array(
                        'status' => 400,
                        'message' => 'Stocker sudah ada di sebuah troli',
                        'data' => null,
                        'redirect' => '',
                        'additional' => []
                    )
                );
            } else {
                return json_encode(
                    array(
                        'status' => 200,
                        'message' => 'Stocker berhasil ditemukan',
                        'data' => $scannedStocker,
                        'redirect' => '',
                        'additional' => []
                    )
                );
            }
        }

        return json_encode(
            array(
                'status' => 400,
                'message' => 'Stocker tidak ditemukan',
                'data' => null,
                'redirect' => '',
                'additional' => []
            )
        );
    }
}
