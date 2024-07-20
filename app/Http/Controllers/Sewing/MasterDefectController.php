<?php

namespace App\Http\Controllers\Sewings;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use Illuminate\Http\Request;

class MasterDefectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function defectType(Request $request)
    {
        if ($request->ajax()) {
            $defectTypeQuery = DefectType::eloquent();

            return DataTables::eloquent($defectTypeQuery)->toJson();
        }

        return view("sewing.master-defect.defect-type", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "sewing-master-defect-type"]);
    }

    public function defectArea(Request $request)
    {
        if ($request->ajax()) {
            $defectAreaQuery = DefectArea::eloquent();

            return DataTables::eloquent($defectAreaQuery)->toJson();
        }

        return view("sewing.master-defect.defect-area", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "sewing-master-defect-area"]);
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
