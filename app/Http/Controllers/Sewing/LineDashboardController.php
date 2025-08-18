<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SignalBit\UserLine;

class LineDashboardController extends Controller
{
    public function index() {
        $lines = UserLine::select('username')->
            where('Groupp', 'SEWING')->
            whereRaw('Locked != 1')->
            orderBy('username', 'asc')->
            get();

        return view('sewing.dashboard-line', [
            'lines' => $lines,
            "subPageGroup" => "sewing-sewing", "subPage" => "sewing-dashboard", "page" => "dashboard-sewing-eff"
        ]);
    }
}
