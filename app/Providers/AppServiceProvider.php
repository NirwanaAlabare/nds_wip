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
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin");
        });

        Blade::if('marker', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "marker" || auth()->user()->type == "spreading");
        });

        Blade::if('spreading', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "spreading" || auth()->user()->type == "marker");
        });

        Blade::if('meja', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "meja");
        });

        Blade::if('sample', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "sample");
        });

        Blade::if('stocker', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "stocker" || auth()->user()->type == "spreading");
        });

        Blade::if('manager', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "manager");
        });

        Blade::if('dc', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "dc");
        });

        Blade::if('sewing', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "sewing");
        });

        Blade::if('hr', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "hr");
        });

        Blade::if('warehouse', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "warehouse");
        });

        Blade::if('ppic', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "ppic" || auth()->user()->type == "packing");
        });

        Blade::if('finishgood', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "ppic" || auth()->user()->type == "packing" || auth()->user()->type == "finishgood");
        });

        Blade::if('packing', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "packing" || auth()->user()->type == "ppic");
        });

        Blade::if('ga', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "ga");
        });
        Blade::if('wip', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->type == "wip");
        });
    }
}
