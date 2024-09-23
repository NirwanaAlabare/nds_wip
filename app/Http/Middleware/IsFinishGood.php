<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class IsFinishGood
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
        if (Auth::user() &&  (Auth::user()->type == 'admin' || Auth::user()->type == 'superadmin' || Auth::user()->type == 'ppic' || Auth::user()->type == 'packing' || Auth::user()->type == 'finishgood')) {
            return $next($request);
        }

        return redirect('home')->with('error', 'You have not packing access');
    }
}
