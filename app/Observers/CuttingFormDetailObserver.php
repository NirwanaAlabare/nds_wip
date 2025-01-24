<?php

namespace App\Observers;

use App\Models\FormCutInputDetail;

class CuttingFormDetailObserver
{
    /**
     * Handle the FormCutInputDetail "created" event.
     *
     * @param  \App\Models\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function created(FormCutInputDetail $formCutInputDetail)
    {
        dd("observer form cut detail create");
        Log::info("observer form cut");
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailDetail->formCutInputDetail->alokasiMeja->username);

    }

    /**
     * Handle the FormCutInputDetail "updated" event.
     *
     * @param  \App\Models\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function updated(FormCutInputDetail $formCutInputDetail)
    {
        dd("observer form cut detail update");
        Log::info("observer form cut");
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailDetail->formCutInputDetail->alokasiMeja->username);

    }

    /**
     * Handle the FormCutInputDetail "deleted" event.
     *
     * @param  \App\Models\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function deleted(FormCutInputDetail $formCutInputDetail)
    {
        dd("observer form cut detail delete");
        Log::info("observer form cut detail delete");
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailDetail->formCutInputDetail->alokasiMeja->username);

    }

    /**
     * Handle the FormCutInputDetail "restored" event.
     *
     * @param  \App\Models\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function restored(FormCutInputDetail $formCutInputDetail)
    {
        //
    }

    /**
     * Handle the FormCutInputDetail "force deleted" event.
     *
     * @param  \App\Models\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function forceDeleted(FormCutInputDetail $formCutInputDetail)
    {
        //
    }
}
