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
                SELECT id, rfid_code, line_code, tab_code, lokasi, status
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
            lokasi,
            status,
            created_by,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?,?,?)", [
            $request->rfid_code,
            $request->line_code,
            $request->tab_code,
            'IT',
            'IDLE',
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

    // Tab rusak dikirim repair: hanya tab yang sudah dikembalikan (IDLE), supaya fisiknya memang sudah di IT.
    // Selama REPAIR, tab tidak bisa diambil di Transaksi Tab.
    public function repair_master_tab(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'keterangan' => 'required|string|max:255',
        ]);

        return $this->changeRepairStatus($request->id, 'IDLE', 'REPAIR', trim($request->keterangan));
    }

    public function selesai_repair_master_tab(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'keterangan' => 'nullable|string|max:255',
        ]);

        return $this->changeRepairStatus($request->id, 'REPAIR', 'IDLE', trim((string) $request->keterangan) ?: null);
    }

    // Ubah status repair dan catat di asset_trans_tab supaya muncul di Riwayat Monitoring Tab
    private function changeRepairStatus($id, $fromStatus, $toStatus, $keterangan)
    {
        $repair = $toStatus === 'REPAIR';

        DB::beginTransaction();

        try {
            $tab = DB::table('asset_master_tab')->where('id', $id)->lockForUpdate()->first();

            if (!$tab || $tab->status !== $fromStatus) {
                DB::rollBack();

                if (!$tab) {
                    $message = 'Tab tidak ditemukan.';
                } elseif ($repair && $tab->status === 'TAKEN') {
                    $message = 'Tab sedang dibawa. Kembalikan dulu lewat Transaksi Tab sebelum dikirim repair.';
                } elseif ($repair) {
                    $message = 'Tab sudah berstatus REPAIR.';
                } else {
                    $message = 'Tab tidak sedang REPAIR.';
                }

                return response()->json(['status' => 'error', 'message' => $message], $tab ? 409 : 404);
            }

            $timestamp = Carbon::now();

            DB::table('asset_master_tab')->where('id', $id)->update([
                'status' => $toStatus,
                'lokasi' => 'IT',
                'updated_at' => $timestamp,
            ]);

            DB::table('asset_trans_tab')->insert([
                'tgl_trans' => $timestamp->format('Y-m-d'),
                'rfid_code' => $tab->rfid_code,
                'status' => $repair ? 'REPAIR' : 'SELESAI_REPAIR',
                'tipe_input' => 'MASTER',
                'keterangan' => $keterangan,
                'created_by' => Auth::user()->name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tab ' . ($tab->tab_code ?: $tab->rfid_code) . ($repair ? ' dikirim repair' : ' selesai repair dan siap dipakai'),
        ]);
    }
}
