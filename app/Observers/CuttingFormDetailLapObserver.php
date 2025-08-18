<?php

namespace App\Observers;

use App\Models\Cutting\FormCutInputDetailLap;

class CuttingFormDetailLapObserver
{
    /**
     * Handle the FormCutInputDetailLap "created" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function created(FormCutInputDetailLap $formCutInputDetailLap)
    {
        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailLap && $formCutInputDetailLap->formCutInputDetail && $formCutInputDetailLap->formCutInputDetail->formCutInput && $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutInputDetailLap && $formCutInputDetailLap->formCutInputDetail && $formCutInputDetailLap->formCutInputDetail->formCutInput && $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutInputDetailLap "updated" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function updated(FormCutInputDetailLap $formCutInputDetailLap)
    {
        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailLap && $formCutInputDetailLap->formCutInputDetail && $formCutInputDetailLap->formCutInputDetail->formCutInput && $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutInputDetailLap && $formCutInputDetailLap->formCutInputDetail && $formCutInputDetailLap->formCutInputDetail->formCutInput && $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutInputDetailLap "deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function deleted(FormCutInputDetailLap $formCutInputDetailLap)
    {
        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetailLap && $formCutInputDetailLap->formCutInputDetail && $formCutInputDetailLap->formCutInputDetail->formCutInput && $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutInputDetailLap && $formCutInputDetailLap->formCutInputDetail && $formCutInputDetailLap->formCutInputDetail->formCutInput && $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetailLap->formCutInputDetail->formCutInput->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutInputDetailLap "restored" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function restored(FormCutInputDetailLap $formCutInputDetailLap)
    {
        //
    }

    /**
     * Handle the FormCutInputDetailLap "force deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetailLap  $formCutInputDetailLap
     * @return void
     */
    public function forceDeleted(FormCutInputDetailLap $formCutInputDetailLap)
    {
        //
    }
}
