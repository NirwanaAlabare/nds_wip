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
}
