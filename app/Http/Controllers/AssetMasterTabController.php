<?php

namespace App\Http\Controllers;

use App\Imports\ImportAssetMasterTab;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMasterTabController extends Controller
{
    public function asset_master_tab(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("
                SELECT id, rfid_code, line_code, tab_code
                FROM asset_master_tab
                ORDER BY id DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.master_tab', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_master_tab',
            'containerFluid' => true,
        ]);
    }

    public function store_master_tab(Request $request)
    {
        $request->validate([
            'rfid_code' => 'required',
        ]);

        $exists = DB::table('asset_master_tab')
            ->where('rfid_code', $request->rfid_code)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID Code sudah terdaftar.',
            ], 409);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        DB::insert("INSERT INTO asset_master_tab (
            rfid_code,
            line_code,
            tab_code,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?)", [
            $request->rfid_code,
            $request->line_code,
            $request->tab_code,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Tab berhasil ditambahkan',
        ]);
    }

    public function show_master_tab(Request $request)
    {
        $data = DB::select("SELECT * FROM asset_master_tab WHERE id = ?", [$request->id]);
        return json_encode($data[0] ?? null);
    }

    public function update_master_tab(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'rfid_code' => 'required',
        ]);

        $exists = DB::table('asset_master_tab')
            ->where('rfid_code', $request->rfid_code)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID Code sudah terdaftar.',
            ], 409);
        }

        $timestamp = Carbon::now();

        DB::update("UPDATE asset_master_tab
            SET rfid_code = ?, line_code = ?, tab_code = ?, updated_at = ?
            WHERE id = ?", [
            $request->rfid_code,
            $request->line_code,
            $request->tab_code,
            $timestamp,
            $request->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Tab berhasil diupdate',
        ]);
    }

    public function delete_master_tab(Request $request)
    {
        DB::delete("DELETE FROM asset_master_tab WHERE id = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Tab berhasil dihapus',
        ]);
    }

    public function delete_all_master_tab(Request $request)
    {
        DB::table('asset_master_tab')->truncate();

        return response()->json([
            'status' => 'success',
            'message' => 'Semua data Master Tab berhasil dihapus',
        ]);
    }

    public function import_master_tab(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv',
        ]);

        $import = new ImportAssetMasterTab;
        Excel::import($import, $request->file('file'));

        if ($import->invalidFormat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format file tidak sesuai. Gunakan format upload dengan kolom: RFID Code, Line Code, Tab Code.',
            ], 422);
        }

        if (!empty($import->duplicates)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Upload ditolak, RFID Code dobel: ' . implode(', ', $import->duplicates),
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => "{$import->inserted} data berhasil diupload",
        ]);
    }
}
