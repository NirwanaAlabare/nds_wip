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

        $idleCount = DB::table('asset_master_tab')->where('status', 'IDLE')->count();
        $takenCount = DB::table('asset_master_tab')->where('status', 'TAKEN')->count();

        // For non-AJAX (initial page load)
        return view('asset_management.trans_tab', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-master',
            'subPage' => 'asset_trans_tab',
            'containerFluid' => true,
            'mainLokasiList' => $mainLokasiList,
            'subLokasiList' => $subLokasiList,
            'divisiList' => $divisiList,
            'idleCount' => $idleCount,
            'takenCount' => $takenCount,
        ]);
    }

    public function nik_suggest_trans_tab(Request $request)
    {
        $term = trim($request->q);

        if ($term === '') {
            return response()->json([]);
        }

        $data = DB::connection('mysql_hris')->select("
            SELECT enroll_id, nik, employee_name
            FROM employee_atribut
            WHERE enroll_id LIKE ? AND status_aktif = 'AKTIF'
            ORDER BY enroll_id ASC
            LIMIT 10
        ", [$term . '%']);

        return response()->json($data);
    }

    public function check_rfid_trans_tab(Request $request)
    {
        $rfidCode = trim($request->rfid_code);
        $action = $request->action;

        $tag = DB::selectOne("
            SELECT rfid_code, line_code, tab_code, status
            FROM asset_master_tab
            WHERE rfid_code = ?
        ", [$rfidCode]);

        if (!$tag) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID Code tidak terdaftar di Master Tab.',
            ], 404);
        }

        if ($tag->status === 'REPAIR') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tag ' . $rfidCode . ' sedang REPAIR, tidak bisa digunakan.',
            ], 409);
        }

        if ($action === 'ambil' && $tag->status === 'TAKEN') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tag ' . $rfidCode . ' sedang dibawa (taken), tidak bisa diambil lagi.',
            ], 409);
        }

        if ($action === 'kembalikan' && $tag->status !== 'TAKEN') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tag ' . $rfidCode . ' tidak sedang dibawa keluar (idle), tidak bisa dikembalikan.',
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'data' => $tag,
        ]);
    }

    public function history_trans_tab(Request $request)
    {
        $rows = DB::select("
            SELECT tt.id, tt.tgl_trans, tt.rfid_code, tt.enroll_id, tt.tujuan, tt.status, tt.created_by, tt.created_at,
                mt.line_code, mt.tab_code
            FROM asset_trans_tab tt
            LEFT JOIN asset_master_tab mt ON mt.rfid_code = tt.rfid_code
            ORDER BY tt.id DESC
        ");

        $enrollIds = collect($rows)->pluck('enroll_id')->filter()->unique()->values();

        $employeeNames = [];
        if ($enrollIds->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $enrollIds->count(), '?'));
            $employees = DB::connection('mysql_hris')->select("
                SELECT enroll_id, employee_name
                FROM employee_atribut
                WHERE enroll_id IN ($placeholders)
            ", $enrollIds->all());

            foreach ($employees as $employee) {
                $employeeNames[$employee->enroll_id] = $employee->employee_name;
            }
        }

        foreach ($rows as $row) {
            $row->employee_name = $employeeNames[$row->enroll_id] ?? null;
        }

        return DataTables::of($rows)->toJson();
    }

    public function store_trans_tab(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:single,bulk',
            'action' => 'required|in:ambil,kembalikan',
            'enroll_id' => 'required_if:mode,single',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|string',
            'id_tujuan' => 'required_if:action,ambil|nullable|integer',
        ]);

        if ($request->mode === 'bulk' && count($request->tags) <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mode Bulk minimal scan lebih dari 1 tag.',
            ], 422);
        }

        $enrollId = null;

        if ($request->mode === 'single') {
            $employee = DB::connection('mysql_hris')->select("
                SELECT enroll_id FROM employee_atribut WHERE enroll_id = ?
            ", [$request->enroll_id]);

            if (empty($employee)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Enroll ID tidak ditemukan di HRIS.',
                ], 404);
            }

            $enrollId = $employee[0]->enroll_id;
        }

        $tujuan = null;

        if ($request->action === 'ambil') {
            $lokasi = DB::selectOne("SELECT main_lokasi FROM asset_master_main_lokasi WHERE id = ?", [$request->id_tujuan]);

            if (!$lokasi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tujuan tidak ditemukan.',
                ], 404);
            }

            $tujuan = $lokasi->main_lokasi;
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $rows = [];
        foreach ($request->tags as $rfidCode) {
            $rows[] = [
                'tgl_trans' => $timestamp->format('Y-m-d'),
                'rfid_code' => $rfidCode,
                'enroll_id' => $enrollId,
                'tujuan' => $tujuan,
                'status' => $request->action === 'ambil' ? 'AMBIL' : 'KEMBALI',
                'tipe_input' => strtoupper($request->mode),
                'created_by' => $user,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('asset_trans_tab')->insert($rows);

        DB::table('asset_master_tab')
            ->whereIn('rfid_code', $request->tags)
            ->update([
                'status' => $request->action === 'ambil' ? 'TAKEN' : 'IDLE',
                'lokasi' => $request->action === 'ambil' ? $tujuan : 'IT',
                'updated_at' => $timestamp,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi Tab berhasil disimpan',
        ]);
    }
}
