<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IEMasterController extends Controller
{
    public function IE_master_process(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("SELECT * from ie_master_process
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('IE.master_process', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-master',
            'subPage' => 'IE-master-process',
            'containerFluid' => true,
        ]);
    }

    public function IE_save_master_process(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $process_name = $request->process_name;
        $class_name = $request->class_name;
        $cbotype = $request->cbotype;
        $smv = $request->smv;
        $amv = $request->amv;
        $remark = $request->remark;


        // Check if the nm_process already exist
        $exists = DB::table('ie_master_process')
            ->where('nm_process', $process_name)
            ->exists();

        if ($exists) {
            // Return error JSON response if record exists
            return response()->json([
                'status' => 'error',
                'message' => 'Data already exist.',
            ], 409); // 409 Conflict status code
        }

        DB::insert("INSERT INTO ie_master_process (
        nm_process,
        class,
        machine_type,
        smv,
        amv,
        remark,
        created_by,
        created_at,
        updated_at
    ) VALUES (?,?,?,?,?,?,?,?)", [
            $process_name,
            $class_name,
            $cbotype,
            $smv,
            $amv,
            $remark,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Process Sudah di tambahkan',
            'process_name' => $process_name,
        ]);
    }
}
