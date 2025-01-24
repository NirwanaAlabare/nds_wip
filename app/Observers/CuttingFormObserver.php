<?php

namespace App\Observers;

use App\Models\FormCutInput;

class CuttingFormObserver
{
    /**
     * Handle the FormCutInput "created" event.
     *
     * @param  \App\Models\FormCutInput  $formCutInput
     * @return void
     */
    public function created(FormCutInput $formCutInput)
    {
        dd("observer form cut creat");
        Log::info("observer form cut");
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInput->alokasiMeja->username);

    }

    /**
     * Handle the FormCutInput "updated" event.
     *
     * @param  \App\Models\FormCutInput  $formCutInput
     * @return void
     */
    public function updated(FormCutInput $formCutInput)
    {
        dd("observer form cut upda");
        Log::info("observer form cut");
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInput->alokasiMeja->username);

    }

    /**
     * Handle the FormCutInput "deleted" event.
     *
     * @param  \App\Models\FormCutInput  $formCutInput
     * @return void
     */
    public function deleted(FormCutInput $formCutInput)
    {
        dd("observer form cut del");
        Log::info("observer form cut");
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInput->alokasiMeja->username);

    }

    /**
     * Handle the FormCutInput "restored" event.
     *
     * @param  \App\Models\FormCutInput  $formCutInput
     * @return void
     */
    public function restored(FormCutInput $formCutInput)
    {
        //
    }

    /**
     * Handle the FormCutInput "force deleted" event.
     *
     * @param  \App\Models\FormCutInput  $formCutInput
     * @return void
     */
    public function forceDeleted(FormCutInput $formCutInput)
    {
        //
    }
}
