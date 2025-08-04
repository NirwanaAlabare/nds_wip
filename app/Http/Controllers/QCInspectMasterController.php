<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectMasterController extends Controller
{
    public function qc_inspect_master_critical_defect_show(Request $request)
    {
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
            critical_defect,
            point_defect
            from signalbit_erp.qc_inspect_master_defect
            order by critical_defect asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.master_critical_defect',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-master",
                "subPage" => "qc-inspect-master-critical-defect",
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }

    public function qc_inspect_master_critical_defect_add(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $criticalDefect = $request->criticalDefect;
        $pointDefect = $request->pointDefect;

        // Check if the same critical_defect and point_defect already exist
        $exists = DB::connection('mysql_sb')->table('qc_inspect_master_defect')
            ->where('critical_defect', $criticalDefect)
            ->where('point_defect', $pointDefect)
            ->exists();

        if ($exists) {
            // Return error JSON response if record exists
            return response()->json([
                'status' => 'error',
                'message' => 'This Critical Defect with the specified Point Defect already exists.',
            ], 409); // 409 Conflict status code
        }

        DB::connection('mysql_sb')->insert("INSERT INTO qc_inspect_master_defect (
        critical_defect,
        point_defect,
        created_by,
        created_at,
        updated_at
    ) VALUES (?, ?, ?,?,?)", [
            $criticalDefect,
            $pointDefect,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Critical Defect Sudah di tambahkan',
            'critical_defect' => $criticalDefect,
            'point_defect' => $pointDefect,
        ]);
    }
}
