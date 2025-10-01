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

        Blade::if('role', function (...$roles) {
            $user = auth()->user();

            if (in_array("superadmin", $roles)) {
                if ($user->roles->whereIn("nama_role", ["superadmin"])->count() > 0) {
                    return true;
                }
            } else if (in_array("admin", $roles)) {
                if ($user->roles->whereIn("nama_role", ["admin", "superadmin"])->count() > 0) {
                    return true;
                }
            } else {
                if ((!(in_array("accounting", $roles)) && !(in_array("management", $roles))) && $user->roles->whereIn("nama_role", ["admin", "superadmin"])->count() > 0) {
                    return true;
                }

                foreach($roles as $role) {
                    // Check if user has the role This check will depend on how your roles are set up
                    foreach ($user->roles as $userRole) {
                        if ((($role == 'accounting' || $role == 'management') && $userRole->accesses->whereIn("access", [$role])->count() > 0) || (($role != 'accounting' && $role != 'management') && $userRole->accesses->whereIn("access", [$role, "all"])->count() > 0)) {
                            return true;
                        }
                    }
                }
            }
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

        Blade::if('strictmeja', function () {
            return auth()->check() && (auth()->user()->type == "admin" || auth()->user()->type == "superadmin" || auth()->user()->roles->where("nama_role", "meja")->count() < 1);
        });
    }
}
