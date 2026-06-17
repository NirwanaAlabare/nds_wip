<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class AssetDashboardController extends Controller
{
    public function dashboard_asset(Request $request)
    {
        return view('asset_management.dashboard_asset', [
            'page' => 'dashboard-asset'
        ]);
    }
}
