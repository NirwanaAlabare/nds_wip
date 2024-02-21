<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class StockDcWipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get Stocker Data
            $stockDcWip = DB::select("
                SELECT
                    stk.part_id,
                    stk.buyer,
                    stk.style,
                    stk.act_costing_ws,
                    stk.color,
                    stk.size,
                    sum( stk.qty_complete ) qty_complete,
                    sum( stk.qty_incomplete ) qty_incomplete
                FROM
                    (
                    SELECT
                        stocker_input.id,
                        part.id part_id,
                        part.style style,
                        part.buyer buyer,
                        form_cut_input.id form_cut_id,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        MIN(CAST( stocker_input.range_awal AS INTEGER )) range_awal,
                        MAX(CAST( stocker_input.range_akhir AS INTEGER )) range_akhir,
                        ( MAX( (CASE WHEN dc_in_input.id is not null THEN CAST(stocker_input.range_akhir AS INTEGER) ELSE 0 END) ) - MIN( (CASE WHEN dc_in_input.id is not null THEN CAST(stocker_input.range_awal AS INTEGER) ELSE 0 END) ) + (CASE WHEN dc_in_input.id is not null THEN 1 ELSE 0 END) ) qty_complete,
                        ( MAX( (CASE WHEN dc_in_input.id is null THEN CAST(stocker_input.range_akhir AS INTEGER) ELSE 0 END) ) - MIN( (CASE WHEN dc_in_input.id is null THEN CAST(stocker_input.range_awal AS INTEGER) ELSE 0 END) ) + (CASE WHEN dc_in_input.id is null THEN 1 ELSE 0 END) ) qty_incomplete
                    FROM
                        part
                        LEFT JOIN part_form ON part_form.part_id = part.id
                        LEFT JOIN form_cut_input ON form_cut_input.id = part_form.form_id
                        LEFT JOIN stocker_input ON stocker_input.form_cut_id = form_cut_input.id
                        LEFT JOIN dc_in_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                    GROUP BY
                        part_form.part_id,
                        form_cut_input.id,
                        stocker_input.color,
                        stocker_input.size,
                        stocker_input.group_stocker
                    HAVING
                        stocker_input.id IS NOT NULL
                    ORDER BY
                        stocker_input.id
                    ) stk
                    LEFT JOIN master_size_new ON master_size_new.size = stk.size
                GROUP BY
                    stk.part_id
                ORDER BY
                    stk.part_id
            ");

            return DataTables::of($stockDcWip)->toJson();
        }

        return view("stok-dc.stok-dc-wip.stok-dc-wip", ["page" => "dashboard-dc", "subPageGroup" => "stok-dc", "subPage" => "stok-dc-wip"]);
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
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show($partId = 0)
    {
        $detail = Part::where("id", $partId)->first();

        $stockDcComplete = DB::select("
            SELECT
                *,
                part.id part_id,
                stocker_input.color,
                stocker_input.size,
                MIN(CAST(stocker_input.range_awal AS INTEGER)) range_awal,
                MAX(CAST(stocker_input.range_akhir AS INTEGER)) range_akhir,
                ( MAX( (CASE WHEN dc_in_input.id is not null THEN CAST(stocker_input.range_akhir AS INTEGER) ELSE 0 END) ) - MIN( (CASE WHEN dc_in_input.id is not null THEN CAST(stocker_input.range_awal AS INTEGER) ELSE 0 END) ) + (CASE WHEN dc_in_input.id is not null THEN 1 ELSE 0 END) ) qty_complete, qty
            FROM
                part
                LEFT JOIN part_form on part_form.part_id = part.id
                LEFT JOIN form_cut_input on form_cut_input.id = part_form.form_id
                LEFT JOIN stocker_input on stocker_input.form_cut_id = form_cut_input.id
                LEFT JOIN dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                LEFT JOIN master_size_new on master_size_new.size = stocker_input.size
            WHERE
                part.id = '".$partId."' AND
                stocker_input.id is not null
            GROUP BY
                part.id,
                stocker_input.color,
                stocker_input.size
            ORDER BY
                master_size_new.urutan ASC
        ");

        $stockDcIncomplete = DB::select("
            SELECT
                *,
                part.id part_id,
                stocker_input.color,
                stocker_input.size,
                MIN(CAST(stocker_input.range_awal AS INTEGER)) range_awal,
                MAX(CAST(stocker_input.range_akhir AS INTEGER)) range_akhir,
                (MAX(CAST(stocker_input.range_akhir AS INTEGER)) - MIN(CAST(stocker_input.range_awal AS INTEGER)) + 1) qty
            FROM
                part
                LEFT JOIN part_form on part_form.part_id = part.id
                LEFT JOIN form_cut_input on form_cut_input.id = part_form.form_id
                LEFT JOIN stocker_input on stocker_input.form_cut_id = form_cut_input.id
                LEFT JOIN dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                LEFT JOIN master_size_new on master_size_new.size = stocker_input.size
            WHERE
                part.id = '".$partId."' AND
                stocker_input.id is not null AND
                dc_in_input.id is null
            GROUP BY
                part.id,
                stocker_input.color,
                stocker_input.size
            ORDER BY
                master_size_new.urutan ASC
        ");

        return view('stok-dc.stok-dc-wip.stok-dc-wip-detail', ["page" => "dashboard-dc", "subPageGroup" => "stok-dc", "subPage" => "stok-dc-wip", "detail" => $detail, "stockDcComplete" => $stockDcComplete, "stockDcIncomplete" => $stockDcIncomplete]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function edit(Part $part)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Part $part, $id = 0)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Part $part, $id = 0)
    {
        //
    }
}
