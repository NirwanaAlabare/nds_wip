<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutReject;
use Illuminate\Http\Request;

class FormCutRejectController extends Controller
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

            $formCutReject = FormCutReject::whereBetween("tanggal", [$dateFrom." 00:00:00", $dateTo." 23:59:59"])->get();

            return DataTables::of($formCutReject);
        }

        return view("cutting.cutting-form-reject.cutting-form-reject");
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
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function show(FormCutReject $formCutReject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCutReject $formCutReject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormCutReject $formCutReject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cutting\FormCutReject  $formCutReject
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCutReject $formCutReject)
    {
        //
    }
}
