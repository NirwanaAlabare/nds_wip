<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->type == "admin";
        });

        Blade::if('marker', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "marker");
        });

        Blade::if('spreading', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "spreading");
        });

        Blade::if('meja', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "meja");
        });

        Blade::if('stocker', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "stocker");
        });

        Blade::if('manager', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "manager");
        });

        Blade::if('dc', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "dc");
        });
    }
}
