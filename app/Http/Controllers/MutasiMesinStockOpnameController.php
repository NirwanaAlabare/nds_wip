<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;


class MutasiMesinStockOpnameController extends Controller
{
    public function so_mesin(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("SELECT
            a.tgl_so,
            DATE_FORMAT(a.tgl_so, '%d %M %Y') AS tgl_so_fix,
            COUNT(a.id_qr) AS tot_mesin,
            a.lokasi
            FROM mut_mesin_stock_opname a
            WHERE a.tgl_so >= '$tgl_awal' and a.tgl_so <= '$tgl_akhir'
            GROUP BY a.lokasi, a.tgl_so
            ORDER BY a.tgl_so ASC, a.lokasi ASC;
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view(
            'mut-mesin.so_mesin',
            [
                'page' => 'dashboard-mut-mesin',
                "subPageGroup" => "proses-mut-mesin",
                "subPage" => "so_mesin",
                "user" => $user
            ]
        );
    }


    public function export_excel_so_mesin(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("SELECT
                            a.id id_so,
                            DATE_FORMAT(a.tgl_so, '%d %M %Y') AS tgl_so,
                            a.id_qr,
                            a.lokasi,
                            a.ket,
                            a.created_by,
                            DATE_FORMAT(a.created_at, '%d %M %Y %H:%i') AS created_at,
                            b.jenis_mesin,
                            b.brand,
                            b.tipe_mesin,
                            b.serial_no
                            FROM mut_mesin_stock_opname a
                            left join master_mesin b on a.id_qr = b.id_qr
                            WHERE a.tgl_so >= '$tgl_awal' and a.tgl_so <= '$tgl_akhir'
                            order by created_at asc
            ");

            return DataTables::of($data_input)->toJson();
        }
    }

    public function so_mesin_detail_modal(Request $request)
    {
        $tgl_so = $request->tgl_so;
        $lokasi = $request->lokasi;

        $data = DB::select("
        SELECT
            a.id id_so,
            a.id_qr,
            b.jenis_mesin,
            b.brand,
            b.tipe_mesin,
            b.serial_no,
            a.ket,
            a.created_by,
            DATE_FORMAT(a.created_at, '%d %M %Y %H:%i') AS created_at
        FROM mut_mesin_stock_opname a
        LEFT JOIN master_mesin b ON a.id_qr = b.id_qr
        WHERE DATE_FORMAT(a.tgl_so, '%d %M %Y') = ? AND a.lokasi = ?
        ORDER BY a.created_at ASC
    ", [$tgl_so, $lokasi]);

        return DataTables::of($data)->toJson();
    }


    public function create_so_mesin()
    {
        $user = Auth::user()->name;
        $data_lokasi = DB::select("SELECT lokasi isi,lokasi tampil
        from master_mesin_lokasi order by lokasi asc");

        return view('mut-mesin.create_so_mesin', [
            'page' => 'dashboard-mut-mesin',
            'subPageGroup' => 'proses-so-mesin',
            'subPage' => 'create_so_mesin',
            'data_lokasi' => $data_lokasi,
            'user' => $user
        ]);
    }

    public function getdata_so_mesin(Request $request)
    {
        $lokasi = $request->input('cbolok');

        $data_so = DB::select(
            "SELECT
            a.id id_so,
            DATE_FORMAT(a.tgl_so, '%d %M %Y') AS tgl_so,
            a.id_qr,
            a.lokasi,
            a.ket,
            a.created_by,
            DATE_FORMAT(a.created_at, '%d %M %Y %H:%i') AS created_at,
            b.jenis_mesin,
            b.brand,
            b.tipe_mesin,
            b.serial_no
FROM mut_mesin_stock_opname a
left join master_mesin b on a.id_qr = b.id_qr
            WHERE lokasi = '$lokasi'
            order by created_at asc"
        );

        return DataTables::of($data_so)->toJson();
    }


    public function store_so_mesin(Request $request)
    {
        $txtqr = $request->txtqr;
        $cbolok = $request->cbolok;
        $tgl_skrg = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;


        // Step 1: Check if QR exists in master_mesin
        $cek_qr = DB::select("SELECT * FROM master_mesin WHERE id_qr = ?", [$txtqr]);
        if (empty($cek_qr)) {
            return [
                'icon' => 'error',
                'msg' => 'QR tidak ditemukan di master mesin.',
                'timer' => 1500,
                'prog' => false,
            ];
        }

        // Use parameter binding to prevent SQL injection
        $cek_date = DB::select("SELECT * FROM mut_mesin_stock_opname WHERE id_qr = ? AND tgl_so = ?", [$txtqr, $tgl_skrg]);

        if (!empty($cek_date)) {
            $lok = $cek_date[0]->lokasi;

            return [
                'icon' => 'error',
                'msg' => 'QR Sudah Di Scan di : ' . $lok,
                'timer' => 1500,
                'prog' => false,
            ];
        } else {
            DB::insert("INSERT INTO mut_mesin_stock_opname (tgl_so, id_qr, lokasi,created_by,created_at,updated_at) VALUES (?, ?, ?,?,?,?)", [
                $tgl_skrg,
                $txtqr,
                $cbolok,
                $user,
                $timestamp,
                $timestamp
            ]);

            return [
                'icon' => 'success',
                'msg' => 'Data Sudah Tersimpan',
                'timer' => 1500,
                'prog' => false,
            ];
        }
    }


    public function update_ket_so_mesin(Request $request)
    {
        $keterangan = $request->keterangan;

        // Handle both single and multi-update
        $id_so_list = $request->id_so_list ?? ($request->id_so ? [$request->id_so] : null);

        if (!$id_so_list || empty($id_so_list)) {
            return response()->json(['message' => 'No row selected.'], 400);
        }

        DB::table('mut_mesin_stock_opname')
            ->whereIn('id', $id_so_list)
            ->update(['ket' => $keterangan]);

        return response()->json(['message' => 'Updated successfully']);
    }

    public function so_mesin_delete(Request $request)
    {
        $id_so = $request->id_so;

        DB::delete(
            "DELETE FROM mut_mesin_stock_opname WHERE id  = '$id_so'"
        );

        return response()->json(['message' => 'Deleted successfully']);
    }
}
