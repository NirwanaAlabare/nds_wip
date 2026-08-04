<?php

namespace App\Http\Controllers\ReportBc;

use App\Http\Controllers\Controller;
use App\Services\ReportBc\DashboardReportBcService;
use Illuminate\Http\Request;

class DashboardReportBcController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardReportBcService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        return view('report-bc.dashboard', [
            'page' => 'dashboard-report-bc',
            'containerFluid' => true,
        ]);
    }

    public function getSummary(Request $request)
    {
        $summary = $this->dashboardService->getSummary();

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }
}
