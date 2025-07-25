<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Models\Trolley;
use App\Models\TrolleyStocker;
use App\Models\Stocker;
use App\Models\LoadingLine;
use App\Models\LoadingLinePlan;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
                                        MAX(dc_in_input.qty_awal)
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
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END) END) no_cut,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) style,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                    stocker_input.color,
                    CONCAT(users.username, ' (',trolley_stocker.updated_at, ')') user,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    COALESCE(MIN(COALESCE(dc_in_input.qty_awal, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) ), COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply)) qty,
                    CONCAT(MIN(stocker_input.range_awal), ' - ', MAX(stocker_input.range_akhir), (CONCAT(' (', MIN( COALESCE(dc_in_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) ), ') ' ))) rangeAwalAkhir
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
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("users", "users.id", "=", "trolley_stocker.created_by")->
                where('trolley_id', $request->trolley_id)->
                where('trolley_stocker.status', "active")->
                // where('stocker_input.status', "trolley")->
                groupBy('form_cut_input.no_cut', 'form_cut_piece.no_cut', 'stocker_input.form_cut_id', 'stocker_input.form_reject_id', 'stocker_input.form_piece_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio');

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
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END) END) no_cut,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) style,
                    (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                    stocker_input.color,
                    CONCAT(users.username, ' (',trolley_stocker.updated_at, ')') user,
                    GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                    COALESCE(master_sb_ws.size, stocker_input.size) size,
                    COALESCE(MIN(COALESCE(dc_in_input.qty_awal, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) ), COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply)) qty,
                    CONCAT(MIN(stocker_input.range_awal), ' - ', MAX(stocker_input.range_akhir), (CONCAT(' (', MIN( COALESCE(dc_in_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) ), ') ' ))) rangeAwalAkhir
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
                leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
                leftJoin("users", "users.id", "=", "trolley_stocker.created_by")->
                where('trolley_id', $id)->
                where('trolley_stocker.status', "active")->
                // where('stocker_input.status', "trolley")->
                groupBy('form_cut_input.no_cut', 'stocker_input.form_cut_id', 'stocker_input.form_reject_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio');

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

        $similarStockerData = Stocker::selectRaw("stocker_input.*, master_secondary.tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id, trolley.nama_trolley, loading_line.id as loading_line_id, loading_line.nama_line as loading_line_name")->
            where(($stockerData->form_piece_id > 0 ? "form_piece_id" : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")), ($stockerData->form_piece_id > 0 ? $stockerData->form_piece_id : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id : $stockerData->form_cut_id)))->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("trolley_stocker", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
            leftJoin("trolley", "trolley.id", "=", "trolley_stocker.trolley_id")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
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
        $similarStockerData = Stocker::selectRaw("stocker_input.*, master_secondary.tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id, loading_line.id as loading_line_id, loading_line.nama_line as loading_line_name")->
            where(($stockerData->form_piece_id > 0 ? "form_piece_id" : ($stockerData->form_reject_id > 0 ? "form_reject_id" : "form_cut_id")), ($stockerData->form_piece_id > 0 ? $stockerData->form_piece_id : ($stockerData->form_reject_id > 0 ? $stockerData->form_reject_id : $stockerData->form_cut_id)))->
            leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
            leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
            leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
            leftJoin("loading_line", "loading_line.stocker_id", "=", "stocker_input.id")->
            where("so_det_id", $stockerData->so_det_id)->
            where("group_stocker", $stockerData->group_stocker)->
            where("ratio", $stockerData->ratio)->
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
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function show(TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
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
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrolleyStocker $trolleyStocker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TrolleyStocker  $trolleyStocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrolleyStocker $trolleyStocker, $id)
    {
        $getTrolleyStockData = TrolleyStocker::where("id", $id)->first();

        $stockerData = Stocker::where("id", $getTrolleyStockData->stocker_id)->first();

        $deleteTrolleyStock = TrolleyStocker::leftJoin("stocker_input", "stocker_input.id", "=", "trolley_stocker.stocker_id")->
            whereRaw("trolley_stocker.trolley_id = '".$getTrolleyStockData->trolley_id."'")->
            whereRaw("COALESCE(stocker_input.form_cut_id, stocker_input.form_reject_id, stocker_input.form_piece_id) = '".($stockerData->form_cut_id ?? $stockerData->form_reject_id ?? $stockerData->form_piece_id)."'")->
            whereRaw("stocker_input.so_det_id = '".$stockerData->so_det_id."'")->
            whereRaw("stocker_input.group_stocker = '".$stockerData->group_stocker."'")->
            whereRaw("stocker_input.ratio = '".$stockerData->ratio."'")->
            delete();

        if ($deleteTrolleyStock) {
            $updateStocker = Stocker::whereRaw("COALESCE(stocker_input.form_cut_id, stocker_input.form_reject_id, stocker_input.form_piece_id) = '".($stockerData->form_cut_id ?? $stockerData->form_reject_id ?? $stockerData->form_piece_id)."'")->
                where("stocker_input.so_det_id", $stockerData->so_det_id)->
                where("stocker_input.group_stocker", $stockerData->group_stocker)->
                where("stocker_input.ratio", $stockerData->ratio)->
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

        $trolleyStocks = TrolleyStocker::selectRaw("
                trolley_stocker.id,
                GROUP_CONCAT(DISTINCT stocker_input.id ORDER BY stocker_input.id ASC) stocker_id,
                GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker ORDER BY stocker_input.id ASC SEPARATOR ', ') id_qr_stocker,
                stocker_input.act_costing_ws,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.no_cut ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN '-' ELSE form_cut_input.no_cut END) END) no_cut,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN form_cut_piece.style ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN form_cut_reject.style ELSE master_sb_ws.styleno END) END) style,
                (CASE WHEN stocker_input.form_piece_id > 0 THEN 'PIECE' ELSE (CASE WHEN stocker_input.form_reject_id > 0 THEN 'REJECT' ELSE 'NORMAL' END) END) tipe,
                stocker_input.color,
                GROUP_CONCAT(DISTINCT master_part.nama_part SEPARATOR ', ') nama_part,
                COALESCE(master_sb_ws.size, stocker_input.size) size,
                COALESCE(MIN(COALESCE(dc_in_input.qty_awal, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(dc_in_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) ), COALESCE(stocker_input.qty_ply_mod, stocker_input.qty_ply)) qty,
                CONCAT(MIN(stocker_input.range_awal), ' - ', MAX(stocker_input.range_akhir), (CONCAT(' (', MIN( COALESCE(dc_in_input.qty_replace, 0) - COALESCE(dc_in_input.qty_reject, 0) + COALESCE(secondary_inhouse_input.qty_replace, 0) - COALESCE(secondary_inhouse_input.qty_reject, 0) + COALESCE(secondary_in_input.qty_replace, 0) - COALESCE(secondary_in_input.qty_reject, 0) ), ') ' ))) rangeAwalAkhir,
                CONCAT(users.username, ' (', trolley_stocker.updated_at, ')') user
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
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            leftJoin("users", "users.id", "=", "trolley_stocker.created_by")->
            where('trolley_id', $id)->
            where('trolley_stocker.status', 'active')->
            where('stocker_input.status', "!=", "line")->
            groupBy('form_cut_input.no_cut', 'form_cut_piece.no_cut', 'stocker_input.form_cut_id', 'stocker_input.form_reject_id', 'stocker_input.form_piece_id', 'stocker_input.so_det_id', 'stocker_input.group_stocker', 'stocker_input.ratio')->
            get();

        return view('dc.trolley.stock-trolley.send-stock-trolley', ['page' => 'dashboard-dc', 'subPageGroup' => 'trolley-dc', 'subPage' => 'stock-trolley', 'trolley' => $trolley, 'lines' => $lines, 'trolleys' => $trolleys, 'trolleyStocks' => $trolleyStocks]);
    }

    public function submitSend(Request $request) {
        $success = [];
        $fail = [];
        $exist = [];

        $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
        $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;

        $lineData = UserLine::where("line_id", $request->line_id)->first();

        if ($request->destination != "trolley") {
            foreach ($request->selectedStocker as $req) {
                $loadingStockArr = [];

                $stockerIds = explode(',', $req['stocker_ids']);

                $stockerData = Stocker::selectRaw("stocker_input.*, master_secondary.tujuan, dc_in_input.id dc_id, secondary_in_input.id secondary_id, secondary_inhouse_input.id secondary_inhouse_id")->
                    whereIn("stocker_input.id", $stockerIds)->
                    leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
                    leftJoin("master_secondary", "master_secondary.id", "=", "part_detail.master_secondary_id")->
                    leftJoin("dc_in_input", "dc_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("secondary_in_input", "secondary_in_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    leftJoin("secondary_inhouse_input", "secondary_inhouse_input.id_qr_stocker", "=", "stocker_input.id_qr_stocker")->
                    get();

                $incompleteNonSecondary = $stockerData->where("tujuan", "NON SECONDARY")->
                    whereNull("dc_id");

                $incompleteSecondary = $stockerData->whereIn("tujuan", ["SECONDARY DALAM", "SECONDARY LUAR"])->
                    whereNull("secondary_id");

                if ($incompleteNonSecondary->count() > 0 || $incompleteSecondary->count() > 0) {
                    return array(
                        'status' => 400,
                        'message' =>
                            "Stocker tidak bisa dialokasikan".
                            ($incompleteNonSecondary->count() > 0 ? "<br><br> Stocker Non Secondary belum masuk DC In : <br> <b>".$incompleteNonSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("create-dc-in")."' class='text-sb' target='_blank'>Ke DC In</a></u>" : "").
                            ($incompleteSecondary->count() > 0 ? "<br><br> Stocker Secondary belum masuk Secondary In : <br> <b>".$incompleteSecondary->pluck("id_qr_stocker")->implode(", ")."</b> <br> <u><a href='".route("secondary-in")."' class='text-sb' target='_blank'>Ke Secondary In</a></u>" : ""),
                        'redirect' => '',
                        'table' => 'trolley-stock-datatable',
                        'callback' => 'clearAll()',
                        'additional' => [],
                    );
                }

                for ($i = 0; $i < count($stockerIds); $i++) {
                    $thisStockerData = Stocker::where('id', $stockerIds[$i])->first();

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
                                "qty" => ($thisStockerData->qty_ply_mod > 0 ? $thisStockerData->qty_ply_mod : $thisStockerData->qty_ply) + ($thisStockerData->dcIn ? ((0 - $thisStockerData->dcIn->qty_reject) + $thisStockerData->dcIn->qty_replace) : 0) + ($thisStockerData->secondaryInHouse ? ((0 - $thisStockerData->secondaryInHouse->qty_reject) + $thisStockerData->secondaryInHouse->qty_replace) : 0) + ($thisStockerData->secondaryIn ? ((0 - $thisStockerData->secondaryIn->qty_reject) + $thisStockerData->secondaryIn->qty_replace) : 0),
                                "status" => "active",
                                "tanggal_loading" => $request['tanggal_loading'],
                                "no_bon" => $request['no_bon'],
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
                                "act_costing_id" => ($thisStockerData->masterSbWs ? $thisStockerData->masterSbWs->id_act_cost : ($thisStockerData->formPiece->act_costing_id ? $thisStockerData->formPiece->act_costing_id : $thisStockerData->formReject->act_costing_id)),
                                "act_costing_ws" => ($thisStockerData->masterSbWs ? $thisStockerData->masterSbWs->ws : ($thisStockerData->formPiece->act_costing_ws ? $thisStockerData->formPiece->act_costing_ws : $thisStockerData->formReject->act_costing_ws)),
                                "buyer" => ($thisStockerData->masterSbWs ? $thisStockerData->masterSbWs->buyer : ($thisStockerData->formPiece->buyer ? $thisStockerData->formPiece->buyer : $thisStockerData->formReject->buyer)),
                                "style" => ($thisStockerData->masterSbWs ? $thisStockerData->masterSbWs->styleno : ($thisStockerData->formPiece->style ? $thisStockerData->formPiece->style : $thisStockerData->formReject->style)),
                                "color" => ($thisStockerData->masterSbWs ? $thisStockerData->masterSbWs->color : ($thisStockerData->formPiece->color ? $thisStockerData->formPiece->color : $thisStockerData->formReject->color)),
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
                                "qty" => ($thisStockerData->qty_ply_mod > 0 ? $thisStockerData->qty_ply_mod : $thisStockerData->qty_ply) + ($thisStockerData->dcIn ? ((0 - $thisStockerData->dcIn->qty_reject) + $thisStockerData->dcIn->qty_replace) : 0) + ($thisStockerData->secondaryInHouse ? ((0 - $thisStockerData->secondaryInHouse->qty_reject) + $thisStockerData->secondaryInHouse->qty_replace) : 0) + ($thisStockerData->secondaryIn ? ((0 - $thisStockerData->secondaryIn->qty_reject) + $thisStockerData->secondaryIn->qty_replace) : 0),
                                "status" => "active",
                                "tanggal_loading" => $request['tanggal_loading'],
                                "no_bon" => $request['no_bon'],
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

                $storeLoadingStock = LoadingLine::insert($loadingStockArr);

                if (count($loadingStockArr) > 0) {
                    $updateStocker = Stocker::whereIn("id", $stockerIds)->
                        update([
                            "status" => "line",
                            "latest_alokasi" => Carbon::now()
                        ]);

                    $updateTrolleyStocker = TrolleyStocker::whereIn("stocker_id", $stockerIds)->
                        update([
                            "status" => "not active"
                        ]);

                    if ($updateStocker) {
                        array_push($success, ['stocker' => $stockerIds]);
                    } else {
                        array_push($fail, ['stocker' => $stockerIds]);
                    }
                }
            }
        } else {
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

                if ($updateStocker) {
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
