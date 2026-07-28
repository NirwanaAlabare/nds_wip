<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetTransTabController extends Controller
{
    public function asset_trans_tab(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("
                SELECT det.id, main.main_lokasi, det.sub_lokasi, det.divisi
                FROM asset_master_lokasi_det det
                LEFT JOIN asset_master_main_lokasi main ON main.id = det.id_main_lokasi
                ORDER BY det.id DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        $mainLokasiList = DB::select("SELECT id, main_lokasi FROM asset_master_main_lokasi ORDER BY main_lokasi ASC");
        $subLokasiList = DB::select("
            SELECT DISTINCT sub_lokasi
            FROM asset_master_lokasi_det
            WHERE sub_lokasi IS NOT NULL AND sub_lokasi != ''
            ORDER BY sub_lokasi ASC
        ");
        $divisiList = DB::select("
            SELECT DISTINCT divisi
            FROM asset_master_lokasi_det
            WHERE divisi IS NOT NULL AND divisi != ''
            ORDER BY divisi ASC
        ");

        // For non-AJAX (initial page load)
        return view('asset_management.trans_tab', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_trans_tab',
            'containerFluid' => true,
            'mainLokasiList' => $mainLokasiList,
            'subLokasiList' => $subLokasiList,
            'divisiList' => $divisiList,
        ]);
    }
}
