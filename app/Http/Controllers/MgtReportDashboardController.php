<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class MgtReportDashboardController extends Controller
{
    public function dashboard_mgt_report(Request $request)
    {
        return view('management_report.dashboard_mgt_report', [
            'page' => 'dashboard-mgt-report'
        ]);
    }
}
