<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectDashboardController extends Controller
{
    public function dashboard_qc_inspect(Request $request)
    {
        return view('qc_inspect.dashboard_qc_inspect', [
            'page' => 'dashboard-qc-inspect'
        ]);
    }
}
