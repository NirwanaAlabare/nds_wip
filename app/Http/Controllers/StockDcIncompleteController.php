<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class StockDcIncompleteController extends Controller
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
            $stockDcIncomplete = DB::select("
                SELECT
                    stk.part_id,
                    stk.buyer,
                    stk.act_costing_ws,
                    stk.color,
                    stk.size,
                    sum( stk.qty ) qty,
                    sum( stk.complete ) complete,
                    sum( stk.stocker ) stocker,
                    sum( stk.counting ) bundle
                FROM
                    (
                    SELECT
                        stocker_input.id,
                        part.id part_id,
                        part.buyer buyer,
                        form_cut_input.id form_cut_id,
                        stocker_input.act_costing_ws,
                        stocker_input.color,
                        stocker_input.size,
                        MIN(CAST( stocker_input.range_awal AS INTEGER )) range_awal,
                        MAX(CAST( stocker_input.range_akhir AS INTEGER )) range_akhir,
                        ( MAX( (CASE WHEN dc_in_input.id is null THEN CAST(stocker_input.range_akhir AS INTEGER) ELSE 0 END) ) - MIN( (CASE WHEN dc_in_input.id is null THEN CAST(stocker_input.range_awal AS INTEGER) ELSE 0 END) ) + (CASE WHEN dc_in_input.id is null THEN 1 ELSE 0 END) ) qty,
                        COUNT( dc_in_input.id ) complete,
                        COUNT( stocker_input.id ) stocker,
                        (CASE WHEN dc_in_input.id is null THEN 1 ELSE 0 END) counting,
                        MAX(dc_in_input.updated_at) last_update
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
                    stk.part_id,
                    stk.color,
                    stk.size
                HAVING
                    sum( stk.complete ) != sum( stk.stocker )
                ORDER BY
                    stk.last_update DESC,
                    stk.part_id ASC,
                    stk.color ASC,
                    master_size_new.urutan ASC
            ");

            return DataTables::of($stockDcIncomplete)->toJson();
        }

        return view("stok-dc.stok-dc-incomplete.stok-dc-incomplete", ["page" => "dashboard-dc", "subPageGroup" => "stok-dc", "subPage" => "stok-dc-incomplete"]);
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
    public function show($partId = 0, $color = 0, $size = 0)
    {
        $stockDcIncomplete = DB::select("
            SELECT
                GROUP_CONCAT(stocker_input.id_qr_stocker) stockers,
                part.id part_id,
                part.kode part_kode,
                part.act_costing_ws,
                part.buyer,
                part.style,
                form_cut_input.id form_cut_id,
                form_cut_input.no_form,
                form_cut_input.no_cut,
                stocker_input.color,
                stocker_input.size,
                stocker_input.shade,
                MIN(CAST(stocker_input.range_awal AS INTEGER)) range_awal,
                MAX(CAST(stocker_input.range_akhir AS INTEGER)) range_akhir,
                (MAX(CAST(stocker_input.range_akhir AS INTEGER)) - MIN(CAST(stocker_input.range_awal AS INTEGER)) + 1) qty,
                COALESCE(stocker_input.lokasi, master_secondary.proses, master_secondary.tujuan, '-') lokasi,
                GROUP_CONCAT(DISTINCT (master_part.nama_part) ORDER BY (part_detail.id) ASC) part
            FROM
                part
                LEFT JOIN part_form on part_form.part_id = part.id
                LEFT JOIN form_cut_input on form_cut_input.id = part_form.form_id
                LEFT JOIN stocker_input on stocker_input.form_cut_id = form_cut_input.id
                LEFT JOIN dc_in_input on dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
                LEFT JOIN part_detail on stocker_input.part_detail_id = part_detail.id
                LEFT JOIN master_part on master_part.id = part_detail.master_part_id
                LEFT JOIN master_secondary on master_secondary.id = part_detail.master_secondary_id
            WHERE
                part.id = '".$partId."' AND
                stocker_input.color = '".$color."' AND
                stocker_input.size = '".$size."' AND
                dc_in_input.id is null
            GROUP BY
                part_form.part_id,
                form_cut_input.id,
                stocker_input.color,
                stocker_input.group_stocker,
                stocker_input.size
            ORDER BY
                form_cut_input.no_cut ASC,
                stocker_input.group_stocker DESC,
                stocker_input.shade DESC
        ");

        return view('stok-dc.stok-dc-incomplete.stok-dc-incomplete-detail', ["page" => "dashboard-dc", "subPageGroup" => "stok-dc", "subPage" => "stok-dc-incomplete", "stockDcIncomplete" => $stockDcIncomplete]);
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
