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
        $thn_view = $request->periode_tahun_view;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("WITH b AS (
                SELECT
                    a.bulan,
                    a.nama_bulan,
                    CAST(a.tahun AS UNSIGNED) AS tahun,
                    COUNT(tanggal) AS tot_working_days
                FROM dim_date a
                LEFT JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
                WHERE status_prod = 'KERJA'
                    AND (status_absen != 'LN' OR status_absen IS NULL)
                    AND tahun >= '2025' AND tahun <= '2030'
                GROUP BY bulan, tahun
                ORDER BY
                    CAST(a.tahun AS UNSIGNED) ASC,
                    CAST(a.bulan AS UNSIGNED) ASC
            )

            SELECT
                b.bulan,
                nama_bulan,
                b.tahun,
                round(sum(projection / tot_working_days),2) AS tot_daily_cost
            FROM mgt_rep_daily_cost a
            LEFT JOIN b ON a.bulan = b.bulan AND a.tahun = b.tahun
            WHERE a.tahun >= ?
            GROUP BY b.bulan, b.nama_bulan, b.tahun
            ORDER BY
                CAST(b.tahun AS UNSIGNED) ASC,
                CAST(b.bulan AS UNSIGNED) ASC
        ", [$thn_view]);

            return response()->json([
                'data' => $data_input // ✅ simplified response
            ]);
        }

        // For non-AJAX (initial page load)
        return view('IE.master_process', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-master',
            'subPage' => 'IE-master-process',
            'containerFluid' => true,
            'user' => $user,
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
        created_by,
        created_at,
        updated_at
    ) VALUES (?,?,?,?,?,?,?,?)", [
            $process_name,
            $class_name,
            $cbotype,
            $smv,
            $amv,
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
