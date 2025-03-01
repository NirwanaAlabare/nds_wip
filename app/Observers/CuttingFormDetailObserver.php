<?php

namespace App\Observers;

use App\Models\Cutting\FormCutDetail;

class CuttingFormDetailObserver
{
    /**
     * Handle the FormCutDetail "created" event.
     *
     * @param  \App\Models\Cutting\FormCutDetail  $formCutDetail
     * @return void
     */
    public function created(FormCutDetail $formCutDetail)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutDetail && $formCutDetail->FormCut && $formCutDetail->FormCut->alokasiMeja ? $formCutDetail->FormCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutDetail && $formCutDetail->FormCut && $formCutDetail->FormCut->alokasiMeja ? $formCutDetail->FormCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutDetail "updated" event.
     *
     * @param  \App\Models\Cutting\FormCutDetail  $formCutDetail
     * @return void
     */
    public function updated(FormCutDetail $formCutDetail)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutDetail && $formCutDetail->FormCut && $formCutDetail->FormCut->alokasiMeja ? $formCutDetail->FormCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutDetail && $formCutDetail->FormCut && $formCutDetail->FormCut->alokasiMeja ? $formCutDetail->FormCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutDetail "deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutDetail  $formCutDetail
     * @return void
     */
    public function deleted(FormCutDetail $formCutDetail)
    {
        app('App\Http\Controllers\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutDetail && $formCutDetail->FormCut && $formCutDetail->FormCut->alokasiMeja ? $formCutDetail->FormCut->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutDetail && $formCutDetail->FormCut && $formCutDetail->FormCut->alokasiMeja ? $formCutDetail->FormCut->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutDetail "restored" event.
     *
     * @param  \App\Models\Cutting\FormCutDetail  $formCutDetail
     * @return void
     */
    public function restored(FormCutDetail $formCutDetail)
    {
        //
    }

    /**
     * Handle the FormCutDetail "force deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutDetail  $formCutDetail
     * @return void
     */
    public function forceDeleted(FormCutDetail $formCutDetail)
    {
        //
    }
}
