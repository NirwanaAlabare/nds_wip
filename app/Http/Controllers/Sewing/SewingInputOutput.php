<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SewingInputOutput extends Controller
{
    public function index() {


        return view("sewing.input-output");
    }
}
