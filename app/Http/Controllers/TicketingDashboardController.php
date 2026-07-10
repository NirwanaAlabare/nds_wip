<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class TicketingDashboardController extends Controller
{
    public function dashboard_ticketing(Request $request)
    {
        return view('ticketing.dashboard_ticketing', [
            'page' => 'dashboard-ticketing'
        ]);
    }
}
