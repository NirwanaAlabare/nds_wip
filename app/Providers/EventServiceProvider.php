<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\Cutting\FormCut;
use App\Observers\CuttingFormObserver;
use App\Models\Cutting\FormCutDetail;
use App\Observers\CuttingFormDetailObserver;
use App\Models\Cutting\FormCutDetailLap;
use App\Observers\CuttingFormDetailLapObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        FormCut::observe(CuttingFormObserver::class);
        FormCutDetail::observe(CuttingFormDetailObserver::class);
        FormCutDetailLap::observe(CuttingFormDetailLapObserver::class);
    }
}
