<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $employeeQuery = Employee::get();

            return DataTables::eloquent($employeeQuery)->toJson();;
        }

        return view('employee.employee');
    }
}
