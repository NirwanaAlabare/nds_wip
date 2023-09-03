<?php

namespace App\Http\Controllers;

use App\Models\MarkerDetail;
use App\Http\Requests\StoreMarkerDetailRequest;
use App\Http\Requests\UpdateMarkerDetailRequest;

class MarkerDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreMarkerDetailRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMarkerDetailRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MarkerDetail  $markerDetail
     * @return \Illuminate\Http\Response
     */
    public function show(MarkerDetail $markerDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MarkerDetail  $markerDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(MarkerDetail $markerDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMarkerDetailRequest  $request
     * @param  \App\Models\MarkerDetail  $markerDetail
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMarkerDetailRequest $request, MarkerDetail $markerDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MarkerDetail  $markerDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(MarkerDetail $markerDetail)
    {
        //
    }
}
