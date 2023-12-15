<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rack;

class DashboardController extends Controller
{
    public function dc() {
        $racks = Rack::all();

        return view('dashboard', ['page' => 'dashboard-dc', 'racks' => $racks]);
    }
}
