<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class IEDashboardController extends Controller
{
    public function dashboard_IE(Request $request)
    {
        return view('IE.dashboard_IE', [
            'page' => 'dashboard-IE'
        ]);
    }
}
