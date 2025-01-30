<?php

namespace App\Observers;

use App\Models\FormCutInputDetailLap;

class CuttingFormDetailLapObserver
{
    /**
     * Handle the FormCutInputDetailLap "created" event.
     *
     * @param  \App\Models\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function created(FormCutInputDetailLap $formCutInputDetailLap)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username);
        // dd(date("Y-m-d"), $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username);
    }

    /**
     * Handle the FormCutInputDetailLap "updated" event.
     *
     * @param  \App\Models\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function updated(FormCutInputDetailLap $formCutInputDetailLap)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username);
        // dd(date("Y-m-d"), $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username);
    }

    /**
     * Handle the FormCutInputDetailLap "deleted" event.
     *
     * @param  \App\Models\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function deleted(FormCutInputDetailLap $formCutInputDetailLap)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username);
        // dd(date("Y-m-d"), $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username);
    }

    /**
     * Handle the FormCutInputDetailLap "restored" event.
     *
     * @param  \App\Models\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function restored(FormCutInputDetailLap $formCutInputDetailLap)
    {
        //
    }

    /**
     * Handle the FormCutInputDetailLap "force deleted" event.
     *
     * @param  \App\Models\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function forceDeleted(FormCutInputDetailLap $formCutInputDetailLap)
    {
        //
    }
}
