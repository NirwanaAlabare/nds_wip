<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SignalBit\UserPassword;

class LineDashboardController extends Controller
{
    public function index() {
        $lines = UserPassword::select('username')->
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
