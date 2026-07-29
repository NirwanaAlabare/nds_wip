<?php

namespace App\Http\Controllers\ReportBc;

use App\Http\Controllers\Controller; // <-- Tambahkan baris ini
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class DashboardReportBcController extends Controller
{
    public function index(Request $request)
    {
        return view('report-bc.dashboard', [
            'page' => 'index-dashboard-report-bc', // Sesuaikan nilainya di sini
            'containerFluid' => true,
        ]);
    }
}
