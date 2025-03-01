<?php

namespace App\Observers;

use App\Models\Cutting\FormCutDetailLap;

class CuttingFormDetailLapObserver
{
    /**
     * Handle the FormCutDetailLap "created" event.
     *
     * @param  \App\Models\Cutting\FormCutDetailLap  $formCutDetailLap
     * @return void
     */
    public function created(FormCutDetailLap $formCutDetailLap)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutDetailLap && $formCutDetailLap->FormCutDetail && $formCutDetailLap->FormCutDetail->FormCut && $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja ? $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutDetailLap && $formCutDetailLap->FormCutDetail && $formCutDetailLap->FormCutDetail->FormCut && $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja ? $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutDetailLap "updated" event.
     *
     * @param  \App\Models\Cutting\FormCutDetailLap  $formCutDetailLap
     * @return void
     */
    public function updated(FormCutDetailLap $formCutDetailLap)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutDetailLap && $formCutDetailLap->FormCutDetail && $formCutDetailLap->FormCutDetail->FormCut && $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja ? $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutDetailLap && $formCutDetailLap->FormCutDetail && $formCutDetailLap->FormCutDetail->FormCut && $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja ? $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutDetailLap "deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutDetailLap  $formCutDetailLap
     * @return void
     */
    public function deleted(FormCutDetailLap $formCutDetailLap)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutDetailLap && $formCutDetailLap->FormCutDetail && $formCutDetailLap->FormCutDetail->FormCut && $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja ? $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutDetailLap && $formCutDetailLap->FormCutDetail && $formCutDetailLap->FormCutDetail->FormCut && $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja ? $formCutDetailLap->FormCutDetail->FormCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutDetailLap "restored" event.
     *
     * @param  \App\Models\Cutting\FormCutDetailLap  $formCutDetailLap
     * @return void
     */
    public function restored(FormCutDetailLap $formCutDetailLap)
    {
        //
    }

    /**
     * Handle the FormCutDetailLap "force deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutDetailLap  $formCutDetailLap
     * @return void
     */
    public function forceDeleted(FormCutDetailLap $formCutDetailLap)
    {
        //
    }
}
