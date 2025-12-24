<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetTenantConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $userConnection = Auth::user()
            ->userConnection()
            ->where('is_active', 'active')
            ->first();

        if (!$userConnection) {
            return $next($request);
        }

        $connectionNameSb = $userConnection->connectionList->connection_sb;
        $connectionNameNds = $userConnection->connectionList->connection_nds;

        if ($connectionNameNds) {
            Config::set(
                'database.default',
                $connectionNameNds
            );

            Config::set(
                'database.connections.mysql_nds',
                config("database.connections.".$connectionNameNds."")
            );

            DB::purge('mysql_nds');
        }

        if ($connectionNameSb) {
            Config::set(
                'database.connections.mysql_sb',
                config("database.connections.".$connectionNameSb."")
            );

            DB::purge('mysql_sb');
        }

        return $next($request);
    }
}
