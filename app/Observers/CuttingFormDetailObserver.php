<?php

namespace App\Observers;

use App\Models\Cutting\FormCutInputDetail;

class CuttingFormDetailObserver
{
    /**
     * Handle the FormCutInputDetail "created" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function created(FormCutInputDetail $formCutInputDetail)
    {
        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetail && $formCutInputDetail->formCutInput && $formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetail->formCutInput->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutInputDetail && $formCutInputDetail->formCutInput && $formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetail->formCutInput->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutInputDetail "updated" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function updated(FormCutInputDetail $formCutInputDetail)
    {
        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetail && $formCutInputDetail->formCutInput && $formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetail->formCutInput->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutInputDetail && $formCutInputDetail->formCutInput && $formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetail->formCutInput->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutInputDetail "deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function deleted(FormCutInputDetail $formCutInputDetail)
    {
        app('App\Http\Controllers\General\DashboardController')->cutting_chart_trigger_all(date("Y-m-d"));
        app('App\Http\Controllers\General\DashboardController')->cutting_trigger_chart_by_mejaid(date("Y-m-d"), $formCutInputDetail && $formCutInputDetail->formCutInput && $formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetail->formCutInput->alokasiMeja->username : null);
        // dd(date("Y-m-d"), $formCutInputDetail && $formCutInputDetail->formCutInput && $formCutInputDetail->formCutInput->alokasiMeja ? $formCutInputDetail->formCutInput->alokasiMeja->username : null);
    }

    /**
     * Handle the FormCutInputDetail "restored" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function restored(FormCutInputDetail $formCutInputDetail)
    {
        //
    }

    /**
     * Handle the FormCutInputDetail "force deleted" event.
     *
     * @param  \App\Models\Cutting\FormCutInputDetail  $formCutInputDetail
     * @return void
     */
    public function forceDeleted(FormCutInputDetail $formCutInputDetail)
    {
        //
    }
}
