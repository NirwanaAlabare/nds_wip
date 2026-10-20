<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Tolak request ke route yang terdaftar di lock transaction berstatus locked (superadmin tetap boleh)
class LockTransactionMiddleware
{
    const CACHE_KEY = 'lock-transaction:locked-routes';

    public function handle(Request $request, Closure $next)
    {
        $routeName = optional($request->route())->getName();

        if (!$routeName) {
            return $next($request);
        }

        $transactionName = self::lockedRoutes()[$routeName] ?? null;

        if (!$transactionName || $this->isSuperadmin()) {
            return $next($request);
        }

        $message = 'Transaksi ' . $transactionName . ' sedang dikunci, tidak dapat melakukan perubahan data';

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status'  => 423,
                'message' => $message,
            ], 423);
        }

        return response()->view('errors.404', ['message' => $message], 404);
    }

    // [route_name => transaction_name] milik transaksi yang sedang locked
    public static function lockedRoutes()
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return DB::table('lock_transaction_detail as d')
                ->join('lock_transaction as t', 't.id', '=', 'd.transaction_id')
                ->where('t.is_locked', '1')
                ->pluck('t.transaction_name', 'd.route_name')
                ->all();
        });
    }

    // Dipanggil setiap ada perubahan lock transaction / detailnya
    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function isSuperadmin()
    {
        $user = Auth::user();

        return $user && $user->roles->whereIn('nama_role', ['superadmin'])->count() > 0;
    }
}
