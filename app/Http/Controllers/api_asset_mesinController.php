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

class api_asset_mesinController extends Controller
{


    public function asset_master_lokasi_api(Request $request)
    {
        $data = DB::select("
            SELECT det.id, main.main_lokasi, det.sub_lokasi, det.divisi
            FROM asset_master_lokasi_det det
            LEFT JOIN asset_master_main_lokasi main ON main.id = det.id_main_lokasi
            ORDER BY det.id DESC
        ");

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function asset_mekanik_cek_qr_api(Request $request)
    {
        $data = DB::select("
            SELECT pm.kode_qr, pm.serial_number, pm.status, jenis.nm_jenis
            FROM asset_penerimaan_mesin pm
            LEFT JOIN asset_master_kd_jenis jenis ON jenis.id_jenis = pm.id_jenis
            WHERE pm.kode_qr = ?
        ", [$request->kode_qr]);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function asset_mekanik_show_ticket_api(Request $request)
    {
        // Join ke asset_penerimaan_mesin (lewat kode_qr) & asset_master_lokasi
        // (lewat id_lok) supaya list tiket langsung bawa nama mesin & lokasi,
        // bukan cuma id/kode mentah — dipakai TicketCard di app Flutter.
        $data = DB::select("
            SELECT
                t.id,
                t.no_ticket,
                t.tgl_trans,
                t.username,
                t.`desc`,
                t.status,
                t.created_by,
                t.created_at,
                CONCAT(jenis.nm_jenis, ' - S/N ', pm.serial_number) AS machine_name,
                CONCAT(main.main_lokasi, ' - ', det.sub_lokasi) AS line
            FROM asset_mekanik_ticket t
            LEFT JOIN asset_penerimaan_mesin pm ON pm.kode_qr = t.kode_qr COLLATE utf8mb4_unicode_ci
            LEFT JOIN asset_master_kd_jenis jenis ON jenis.id_jenis = pm.id_jenis
            LEFT JOIN asset_master_lokasi_det det ON det.id = t.id_lok
            LEFT JOIN asset_master_main_lokasi main ON main.id = det.id_main_lokasi
            ORDER BY t.created_at DESC
        ");

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function asset_mekanik_status_ticket_api(Request $request)
    {
        $data = DB::selectOne("
            SELECT
                COALESCE(SUM(CASE WHEN LOWER(t.status) = 'open' THEN 1 ELSE 0 END), 0) AS open_count,
                COALESCE(SUM(CASE WHEN LOWER(t.status) = 'on progress' THEN 1 ELSE 0 END), 0) AS on_progress_count,
                COALESCE(SUM(CASE WHEN LOWER(t.status) = 'finished' THEN 1 ELSE 0 END), 0) AS finished_count,
                COUNT(*) AS total
            FROM asset_mekanik_ticket t
            LEFT JOIN asset_penerimaan_mesin pm ON pm.kode_qr = t.kode_qr COLLATE utf8mb4_unicode_ci
            LEFT JOIN asset_master_kd_jenis jenis ON jenis.id_jenis = pm.id_jenis
            LEFT JOIN asset_master_lokasi_det det ON det.id = t.id_lok
            LEFT JOIN asset_master_main_lokasi main ON main.id = det.id_main_lokasi
        ");

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function asset_mekanik_insert_ticket_api(Request $request)
    {
        $request->validate([
            'username' => 'required|exists:users,username',
            'id_lok' => 'required',
            'kode_qr' => 'required',
            'desc' => 'nullable|string',
            'created_by' => 'nullable|string',
        ]);

        $timestamp = Carbon::now();
        $tglTrans = $timestamp->format('Y-m-d');

        $id = DB::transaction(function () use ($request, $timestamp, $tglTrans) {
            // no_ticket: dd-mm-yyyy-urutan, urutan dihitung dari jumlah ticket yang sudah ada di tgl_trans tersebut
            $totalHariIni = DB::table('asset_mekanik_ticket')
                ->where('tgl_trans', $tglTrans)
                ->lockForUpdate()
                ->count();

            $urutan = $totalHariIni + 1;
            $noTicket = $timestamp->format('d-m-Y') . '-' . $urutan;

            DB::insert("INSERT INTO asset_mekanik_ticket (
                no_ticket,
                tgl_trans,
                username,
                id_lok,
                kode_qr,
                `desc`,
                status,
                created_by,
                created_at,
                updated_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?)", [
                $noTicket,
                $tglTrans,
                $request->username,
                $request->id_lok,
                $request->kode_qr,
                $request->desc,
                'open',
                $request->created_by,
                $timestamp,
                $timestamp,
            ]);

            return DB::getPdo()->lastInsertId();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket berhasil ditambahkan',
            'id' => $id,
        ]);
    }

    public function asset_mekanik_update_ticket_api(Request $request)
    {
        $request->validate([
            'id' => 'required_without:no_ticket',
            'no_ticket' => 'required_without:id',
            'status' => 'required|string',
        ]);

        $query = DB::table('asset_mekanik_ticket');

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        } else {
            $query->where('no_ticket', $request->no_ticket);
        }

        $updated = $query->update([
            'status' => $request->status,
            'updated_at' => Carbon::now(),
        ]);

        if ($updated < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ticket tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status ticket berhasil diupdate',
        ]);
    }
}
