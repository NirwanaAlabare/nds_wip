<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // if ($request->ajax()) {
        //     $partQuery = MasterPart::query();

        //     return DataTables::eloquent($partQuery)->
        //         filterColumn('kode_part', function ($query, $keyword) {
        //             $query->whereRaw("LOWER(kode_part) LIKE LOWER('%" . $keyword . "%')");
        //         })->filterColumn('nama_part', function ($query, $keyword) {
        //             $query->whereRaw("LOWER(nama_part) LIKE LOWER('%" . $keyword . "%')");
        //         })->filterColumn('bag', function ($query, $keyword) {
        //             $query->whereRaw("LOWER(bag) LIKE LOWER('%" . $keyword . "%')");
        //         })->order(function ($query) {
        //             $query->orderBy('cancel', 'asc')->orderBy('updated_at', 'desc')->orderBy('kode_part', 'desc');
        //         })->toJson();
        // }

        return view("part.part", ["page" => "dashboard-stocker"]);
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
    public function show(Part $part)
    {
        //
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
    public function update(Request $request, Part $part)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Part $part)
    {
        //
    }
}
