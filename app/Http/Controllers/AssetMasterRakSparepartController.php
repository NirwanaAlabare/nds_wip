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

class AssetMasterRakSparepartController extends Controller
{
    public function asset_master_rak_sparepart(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("
                SELECT id_rak, nm_rak, no_rak, `desc`
                FROM asset_master_rak_spareparts
                ORDER BY id_rak DESC
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.master_rak_sparepart', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_master_rak_sparepart',
            'containerFluid' => true,
        ]);
    }

    public function store_rak_sparepart(Request $request)
    {
        $request->validate([
            'nm_rak' => 'required',
            'no_rak' => 'required',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $nm_rak = strtoupper($request->nm_rak);
        $no_rak = strtoupper($request->no_rak);
        $desc = strtoupper($request->desc);

        $exists = DB::table('asset_master_rak_spareparts')
            ->where('nm_rak', $nm_rak)
            ->where('no_rak', $no_rak)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rak Sparepart dengan Nama Rak dan No Rak tersebut sudah ada.',
            ], 409);
        }

        DB::insert("INSERT INTO asset_master_rak_spareparts (
            nm_rak,
            no_rak,
            `desc`,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?)", [
            $nm_rak,
            $no_rak,
            $desc,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rak Sparepart berhasil ditambahkan',
        ]);
    }

    public function show_rak_sparepart(Request $request)
    {
        $data = DB::select("SELECT id_rak, nm_rak, no_rak, `desc` FROM asset_master_rak_spareparts WHERE id_rak = ?", [$request->id]);
        return json_encode($data[0] ?? null);
    }

    public function update_rak_sparepart(Request $request)
    {
        $request->validate([
            'id_rak' => 'required',
            'nm_rak' => 'required',
            'no_rak' => 'required',
        ]);

        $timestamp = Carbon::now();
        $nm_rak = strtoupper($request->nm_rak);
        $no_rak = strtoupper($request->no_rak);
        $desc = strtoupper($request->desc);

        $exists = DB::table('asset_master_rak_spareparts')
            ->where('nm_rak', $nm_rak)
            ->where('no_rak', $no_rak)
            ->where('id_rak', '!=', $request->id_rak)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rak Sparepart dengan Nama Rak dan No Rak tersebut sudah ada.',
            ], 409);
        }

        DB::update("UPDATE asset_master_rak_spareparts
            SET nm_rak = ?, no_rak = ?, `desc` = ?, updated_at = ?
            WHERE id_rak = ?", [
            $nm_rak,
            $no_rak,
            $desc,
            $timestamp,
            $request->id_rak,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rak Sparepart berhasil diupdate',
        ]);
    }

    public function delete_rak_sparepart(Request $request)
    {
        DB::delete("DELETE FROM asset_master_rak_spareparts WHERE id_rak = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rak Sparepart berhasil dihapus',
        ]);
    }
}
