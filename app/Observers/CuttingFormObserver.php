<?php

namespace App\Observers;

use App\Models\Cutting\FormCut;

class CuttingFormObserver
{
    /**
     * Handle the FormCut "created" event.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return void
     */
    public function created(FormCut $formCut)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCut && $formCut->alokasiMeja ? $formCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCut && $formCut->alokasiMeja ? $formCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCut "updated" event.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return void
     */
    public function updated(FormCut $formCut)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCut && $formCut->alokasiMeja ? $formCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCut && $formCut->alokasiMeja ? $formCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCut "deleted" event.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return void
     */
    public function deleted(FormCut $formCut)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCut && $formCut->alokasiMeja ? $formCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCut && $formCut->alokasiMeja ? $formCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCut "restored" event.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return void
     */
    public function restored(FormCut $formCut)
    {
        //
    }

    /**
     * Handle the FormCut "force deleted" event.
     *
     * @param  \App\Models\Cutting\FormCut  $formCut
     * @return void
     */
    public function forceDeleted(FormCut $formCut)
    {
        //
    }
}
