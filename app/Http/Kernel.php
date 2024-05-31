<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\IsAdmin::class,
        'marker' => \App\Http\Middleware\IsMarker::class,
        'spreading' => \App\Http\Middleware\IsSpreading::class,
        'stocker' => \App\Http\Middleware\IsStocker::class,
        'dc' => \App\Http\Middleware\IsDc::class,
        'meja' => \App\Http\Middleware\IsMeja::class,
        'warehouse' => \App\Http\Middleware\IsWarehouse::class,
        'master-lokasi' => \App\Http\Middleware\IsWarehouse::class,
        'in-material' => \App\Http\Middleware\IsMaterial::class,
        'req-material' => \App\Http\Middleware\IsReqMaterial::class,
        'out-material' => \App\Http\Middleware\IsMaterial::class,
        'mutasi-lokasi' => \App\Http\Middleware\IsMaterial::class,
        'retur-material' => \App\Http\Middleware\IsMaterial::class,
        'retur-inmaterial' => \App\Http\Middleware\IsMaterial::class,
        'qc-pass' => \App\Http\Middleware\IsQcpass::class,
        'manager' => \App\Http\Middleware\IsManager::class,
        'hr' => \App\Http\Middleware\IsHr::class,
        'fg-stock' => \App\Http\Middleware\IsFGStock::class,
        'packing' => \App\Http\Middleware\IsPacking::class,
        'ga' => \App\Http\Middleware\IsGa::class,
    ];
}
