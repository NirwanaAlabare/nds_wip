<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Marketing_ApprovalCenterController extends Controller
{
    public function index()
    {
        return view('marketing.approval_center', [
            'page'           => 'dashboard-marketing',
            'subPageGroup'   => 'marketing-approval',
            'subPage'        => 'marketing-approval-center',
            'containerFluid' => true
        ]);
    }
}
