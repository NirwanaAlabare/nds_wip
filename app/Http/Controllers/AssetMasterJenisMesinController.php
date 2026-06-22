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

        $jenisList = DB::table('asset_master_kd_jenis')
            ->select('id_jenis', 'kd_jenis', 'nm_jenis')
            ->orderBy('nm_jenis', 'ASC')
            ->get();

        $merkList = DB::table('asset_master_kd_merk')
            ->select('id_merk', 'kd_merk', 'nm_merk')
            ->orderBy('nm_merk', 'ASC')
            ->get();

        if ($request->ajax()) {
            $data_input = DB::select("SELECT * FROM asset_master_jenis_mesin ORDER BY id_jenis ASC");

            $supplierMap = $supplierList->keyBy('id_supplier');
            $jenisMap = $jenisList->keyBy('kd_jenis');
            $merkMap = $merkList->keyBy('kd_merk');
            foreach ($data_input as $row) {
                $row->supplier = $supplierMap[$row->id_supplier]->Supplier ?? '-';
                $row->jenis = $jenisMap[$row->kd_jenis]->nm_jenis ?? $row->kd_jenis;
                $row->merk = $merkMap[$row->kd_merk]->nm_merk ?? $row->kd_merk;
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
            'jenisList' => $jenisList,
            'merkList' => $merkList,
        ]);
    }

    public function store_kd_jenis(Request $request)
    {
        $request->validate([
            'kd_jenis' => 'required',
            'nm_jenis' => 'required',
        ]);

        $kd_jenis = strtoupper($request->kd_jenis);
        $nm_jenis = strtoupper($request->nm_jenis);

        $exists = DB::table('asset_master_kd_jenis')->where('kd_jenis', $kd_jenis)->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Jenis sudah digunakan',
            ], 422);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id_jenis = DB::table('asset_master_kd_jenis')->insertGetId([
            'kd_jenis' => $kd_jenis,
            'nm_jenis' => $nm_jenis,
            'created_by' => $user,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], 'id_jenis');

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis berhasil ditambahkan',
            'id_jenis' => $id_jenis,
            'kd_jenis' => $kd_jenis,
            'nm_jenis' => $nm_jenis,
        ]);
    }

    public function update_kd_jenis(Request $request)
    {
        $request->validate([
            'id_jenis' => 'required',
            'kd_jenis' => 'required',
            'nm_jenis' => 'required',
        ]);

        $id_jenis = $request->id_jenis;
        $kd_jenis = strtoupper($request->kd_jenis);
        $nm_jenis = strtoupper($request->nm_jenis);

        $old = DB::table('asset_master_kd_jenis')->where('id_jenis', $id_jenis)->first();
        if (!$old) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Jenis tidak ditemukan',
            ], 404);
        }

        // Kode Jenis tidak boleh diubah kalau sudah ada transaksi penerimaan mesin yang memakai jenis ini
        if ($old->kd_jenis !== $kd_jenis) {
            $hasTransaksi = DB::table('asset_penerimaan_mesin')
                ->join('asset_master_jenis_mesin', 'asset_penerimaan_mesin.id_jenis', '=',
                    'asset_master_jenis_mesin.id_jenis')
                ->where('asset_master_jenis_mesin.kd_jenis', $old->kd_jenis)
                ->exists();

            if ($hasTransaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode Jenis tidak bisa diubah karena sudah ada transaksi penerimaan mesin yang menggunakan jenis ini',
                ], 422);
            }
        }

        $exists = DB::table('asset_master_kd_jenis')
            ->where('kd_jenis', $kd_jenis)
            ->where('id_jenis', '!=', $id_jenis)
            ->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Jenis sudah digunakan',
            ], 422);
        }

        $timestamp = Carbon::now();

        DB::update("UPDATE asset_master_kd_jenis
            SET kd_jenis = ?, nm_jenis = ?, updated_at = ?
            WHERE id_jenis = ?", [
            $kd_jenis,
            $nm_jenis,
            $timestamp,
            $id_jenis,
        ]);

        if ($old->kd_jenis !== $kd_jenis) {
            DB::update("UPDATE asset_master_jenis_mesin SET kd_jenis = ? WHERE kd_jenis = ?", [
                $kd_jenis,
                $old->kd_jenis,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis berhasil diupdate',
            'old_kd_jenis' => $old->kd_jenis,
            'kd_jenis' => $kd_jenis,
            'nm_jenis' => $nm_jenis,
        ]);
    }

    public function store_kd_merk(Request $request)
    {
        $request->validate([
            'kd_merk' => 'required',
            'nm_merk' => 'required',
        ]);

        $kd_merk = strtoupper($request->kd_merk);
        $nm_merk = strtoupper($request->nm_merk);

        $exists = DB::table('asset_master_kd_merk')->where('kd_merk', $kd_merk)->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Merk sudah digunakan',
            ], 422);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id_merk = DB::table('asset_master_kd_merk')->insertGetId([
            'kd_merk' => $kd_merk,
            'nm_merk' => $nm_merk,
            'created_by' => $user,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], 'id_merk');

        return response()->json([
            'status' => 'success',
            'message' => 'Merk berhasil ditambahkan',
            'id_merk' => $id_merk,
            'kd_merk' => $kd_merk,
            'nm_merk' => $nm_merk,
        ]);
    }

    public function update_kd_merk(Request $request)
    {
        $request->validate([
            'id_merk' => 'required',
            'kd_merk' => 'required',
            'nm_merk' => 'required',
        ]);

        $id_merk = $request->id_merk;
        $kd_merk = strtoupper($request->kd_merk);
        $nm_merk = strtoupper($request->nm_merk);

        $old = DB::table('asset_master_kd_merk')->where('id_merk', $id_merk)->first();
        if (!$old) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Merk tidak ditemukan',
            ], 404);
        }

        // Kode Merk tidak boleh diubah kalau sudah ada transaksi penerimaan mesin yang memakai merk ini
        if ($old->kd_merk !== $kd_merk) {
            $hasTransaksi = DB::table('asset_penerimaan_mesin')
                ->join('asset_master_jenis_mesin', 'asset_penerimaan_mesin.id_jenis', '=',
                    'asset_master_jenis_mesin.id_jenis')
                ->where('asset_master_jenis_mesin.kd_merk', $old->kd_merk)
                ->exists();

            if ($hasTransaksi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode Merk tidak bisa diubah karena sudah ada transaksi penerimaan mesin yang menggunakan merk ini',
                ], 422);
            }
        }

        $exists = DB::table('asset_master_kd_merk')
            ->where('kd_merk', $kd_merk)
            ->where('id_merk', '!=', $id_merk)
            ->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Merk sudah digunakan',
            ], 422);
        }

        $timestamp = Carbon::now();

        DB::update("UPDATE asset_master_kd_merk
            SET kd_merk = ?, nm_merk = ?, updated_at = ?
            WHERE id_merk = ?", [
            $kd_merk,
            $nm_merk,
            $timestamp,
            $id_merk,
        ]);

        if ($old->kd_merk !== $kd_merk) {
            DB::update("UPDATE asset_master_jenis_mesin SET kd_merk = ? WHERE kd_merk = ?", [
                $kd_merk,
                $old->kd_merk,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Merk berhasil diupdate',
            'old_kd_merk' => $old->kd_merk,
            'kd_merk' => $kd_merk,
            'nm_merk' => $nm_merk,
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

        $exists = DB::table('asset_master_jenis_mesin')
            ->where('kd_jenis', $jenis)
            ->where('kd_merk', $merk)
            ->where('tipe', $tipe)
            ->where('id_supplier', $request->id_supplier)
            ->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Jenis Mesin dengan Jenis, Merk, Tipe, dan Supplier yang sama sudah ada',
            ], 422);
        }

        DB::insert("INSERT INTO asset_master_jenis_mesin (
            kd_jenis,
            kd_merk,
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

        $old = DB::table('asset_master_jenis_mesin')->where('id_jenis', $request->id)->first();
        if (!$old) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Jenis Mesin tidak ditemukan',
            ], 404);
        }

        // Kode Jenis, Merk & Tipe tidak boleh diubah kalau jenis mesin ini sudah pernah ada penerimaannya
        if (($old->kd_jenis !== $jenis || $old->kd_merk !== $merk || $old->tipe !== $tipe)
            && DB::table('asset_penerimaan_mesin')->where('id_jenis', $request->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode Jenis, Merk, dan Tipe tidak bisa diubah karena sudah ada penerimaan mesin untuk jenis ini',
            ], 422);
        }

        $exists = DB::table('asset_master_jenis_mesin')
            ->where('kd_jenis', $jenis)
            ->where('kd_merk', $merk)
            ->where('tipe', $tipe)
            ->where('id_supplier', $request->id_supplier)
            ->where('id_jenis', '!=', $request->id)
            ->exists();
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Jenis Mesin dengan Jenis, Merk, Tipe, dan Supplier yang sama sudah ada',
            ], 422);
        }

        DB::update("UPDATE asset_master_jenis_mesin
            SET kd_jenis = ?, kd_merk = ?, tipe = ?, id_supplier = ?, updated_at = ?
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
        $hasTransaksi = DB::table('asset_penerimaan_mesin')->where('id_jenis', $request->id)->exists();
        if ($hasTransaksi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jenis Mesin tidak bisa dihapus karena sudah ada penerimaan mesin untuk jenis ini',
            ], 422);
        }

        DB::delete("DELETE FROM asset_master_jenis_mesin WHERE id_jenis = ?", [$request->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jenis Mesin berhasil dihapus',
        ]);
    }
}
