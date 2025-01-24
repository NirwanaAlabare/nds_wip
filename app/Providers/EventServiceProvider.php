<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\FormCutInput;
use App\Observers\CuttingFormObserver;
use App\Models\FormCutInputDetail;
use App\Observers\CuttingFormDetailObserver;
use App\Models\FormCutInputDetailLap;
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
        // FormCutInput::observe(CuttingFormObserver::class);
        // FormCutInputDetail::observe(CuttingFormDetailObserver::class);
        // FormCutInputDetailLap::observe(CuttingFormDetailLapObserver::class);
    }
}
