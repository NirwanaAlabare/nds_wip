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

class AssetMasterJenisMesinController extends Controller
{
    public function asset_master_jenis_mesin(Request $request)
    {
        $supplierList = DB::connection('mysql_sb')->table('mastersupplier')
            ->select('id_supplier', 'Supplier')
            ->where('tipe_sup', '=', 'S')
            ->orderBy('Supplier', 'ASC')
            ->get();

        if ($request->ajax()) {
            $data_input = DB::select("SELECT * FROM asset_master_jenis_mesin ORDER BY id_jenis DESC");

            $supplierMap = $supplierList->keyBy('id_supplier');
            foreach ($data_input as $row) {
                $row->supplier = $supplierMap[$row->id_supplier]->Supplier ?? '-';
            }

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.master_jenis_mesin', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_master_jenis_mesin',
            'containerFluid' => true,
            'supplierList' => $supplierList,
        ]);
    }

    public function store_jenis_mesin(Request $request)
    {
        $request->validate([
            'jenis' => 'required',
            'merk' => 'required',
            'tipe' => 'required',
            'id_supplier' => 'required',
        ]);

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $jenis = strtoupper($request->jenis);
        $merk = strtoupper($request->merk);
        $tipe = strtoupper($request->tipe);

        DB::insert("INSERT INTO asset_master_jenis_mesin (
            jenis,
            merk,
            tipe,
            id_supplier,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?,?)", [
            $jenis,
            $merk,
            $tipe,
            $request->id_supplier,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis Mesin berhasil ditambahkan',
        ]);
    }

    public function show_jenis_mesin(Request $request)
    {
        $data = DB::select("SELECT * FROM asset_master_jenis_mesin WHERE id_jenis = ?", [$request->id]);
        return json_encode($data[0] ?? null);
    }

    public function update_jenis_mesin(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'jenis' => 'required',
            'merk' => 'required',
            'tipe' => 'required',
            'id_supplier' => 'required',
        ]);

        $timestamp = Carbon::now();
        $jenis = strtoupper($request->jenis);
        $merk = strtoupper($request->merk);
        $tipe = strtoupper($request->tipe);

        DB::update("UPDATE asset_master_jenis_mesin
            SET jenis = ?, merk = ?, tipe = ?, id_supplier = ?, updated_at = ?
            WHERE id_jenis = ?", [
            $jenis,
            $merk,
            $tipe,
            $request->id_supplier,
            $timestamp,
            $request->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis Mesin berhasil diupdate',
        ]);
    }

    public function delete_jenis_mesin(Request $request)
    {
        DB::delete("DELETE FROM asset_master_jenis_mesin WHERE id_jenis = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis Mesin berhasil dihapus',
        ]);
    }
}
