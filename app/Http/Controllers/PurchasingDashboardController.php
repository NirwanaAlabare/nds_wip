<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class PurchasingDashboardController extends Controller
{
    public function dashboard_purchasing(Request $request)
    {
        return view('purchasing.dashboard_purchasing', [
            'page' => 'dashboard-purchasing',
        ]);
    }
}
