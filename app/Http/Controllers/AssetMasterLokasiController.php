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

class AssetMasterLokasiController extends Controller
{
    public function asset_master_lokasi(Request $request)
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

        // For non-AJAX (initial page load)
        return view('asset_management.master_lokasi', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_master_lokasi',
            'containerFluid' => true,
            'mainLokasiList' => $mainLokasiList,
        ]);
    }

    public function store_main_lokasi(Request $request)
    {
        $request->validate([
            'main_lokasi' => 'required',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $main_lokasi = strtoupper($request->main_lokasi);

        $exists = DB::table('asset_master_main_lokasi')
            ->where('main_lokasi', $main_lokasi)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Main Lokasi already exist.',
            ], 409);
        }

        DB::insert("INSERT INTO asset_master_main_lokasi (
            main_lokasi,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?)", [
            $main_lokasi,
            $user,
            $timestamp,
            $timestamp
        ]);

        $id = DB::getPdo()->lastInsertId();

        return response()->json([
            'status' => 'success',
            'message' => 'Main Lokasi berhasil ditambahkan',
            'id' => $id,
            'main_lokasi' => $main_lokasi,
        ]);
    }

    public function show_main_lokasi(Request $request)
    {
        $data = DB::select("SELECT * FROM asset_master_main_lokasi WHERE id = ?", [$request->id]);
        return json_encode($data[0] ?? null);
    }

    public function update_main_lokasi(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'main_lokasi' => 'required',
        ]);

        $timestamp = Carbon::now();
        $main_lokasi = strtoupper($request->main_lokasi);

        DB::update("UPDATE asset_master_main_lokasi SET main_lokasi = ?, updated_at = ? WHERE id = ?", [
            $main_lokasi,
            $timestamp,
            $request->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Main Lokasi berhasil diupdate',
        ]);
    }

    public function delete_main_lokasi(Request $request)
    {
        DB::delete("DELETE FROM asset_master_main_lokasi WHERE id = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Main Lokasi berhasil dihapus',
        ]);
    }

    public function store_lokasi_det(Request $request)
    {
        $request->validate([
            'id_main_lokasi' => 'required',
            'sub_lokasi' => 'required',
            'divisi' => 'required',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $sub_lokasi = strtoupper($request->sub_lokasi);
        $divisi = strtoupper($request->divisi);

        DB::insert("INSERT INTO asset_master_lokasi_det (
            id_main_lokasi,
            sub_lokasi,
            divisi,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?)", [
            $request->id_main_lokasi,
            $sub_lokasi,
            $divisi,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil ditambahkan',
        ]);
    }

    public function show_lokasi_det(Request $request)
    {
        $data = DB::select("SELECT * FROM asset_master_lokasi_det WHERE id = ?", [$request->id]);
        return json_encode($data[0] ?? null);
    }

    public function update_lokasi_det(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'id_main_lokasi' => 'required',
            'sub_lokasi' => 'required',
            'divisi' => 'required',
        ]);

        $timestamp = Carbon::now();
        $sub_lokasi = strtoupper($request->sub_lokasi);
        $divisi = strtoupper($request->divisi);

        DB::update("UPDATE asset_master_lokasi_det
            SET id_main_lokasi = ?, sub_lokasi = ?, divisi = ?, updated_at = ?
            WHERE id = ?", [
            $request->id_main_lokasi,
            $sub_lokasi,
            $divisi,
            $timestamp,
            $request->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil diupdate',
        ]);
    }

    public function delete_lokasi_det(Request $request)
    {
        DB::delete("DELETE FROM asset_master_lokasi_det WHERE id = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lokasi berhasil dihapus',
        ]);
    }
}
