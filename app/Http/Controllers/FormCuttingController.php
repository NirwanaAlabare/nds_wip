<?php

namespace App\Http\Controllers;

use App\Models\FormCutting;
use App\Http\Requests\StoreFormCuttingRequest;
use App\Http\Requests\UpdateFormCuttingRequest;

class FormCuttingController extends Controller
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
     * @param  \App\Http\Requests\StoreFormCuttingRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFormCuttingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FormCutting  $formCutting
     * @return \Illuminate\Http\Response
     */
    public function show(FormCutting $formCutting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FormCutting  $formCutting
     * @return \Illuminate\Http\Response
     */
    public function edit(FormCutting $formCutting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateFormCuttingRequest  $request
     * @param  \App\Models\FormCutting  $formCutting
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFormCuttingRequest $request, FormCutting $formCutting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FormCutting  $formCutting
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormCutting $formCutting)
    {
        //
    }
}
