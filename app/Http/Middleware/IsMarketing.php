<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class IsMarketing
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
        if (Auth::user() &&  (Auth::user()->type == 'admin' || Auth::user()->type == 'superadmin' || Auth::user()->type == 'ppic' || Auth::user()->type == 'marketing' || Auth::user()->type == 'packing')) {
            return $next($request);
        }

        return redirect('home')->with('error', 'You have not marketing access');
    }
}
