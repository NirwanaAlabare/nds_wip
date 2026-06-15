<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Marker\Marker;
use App\Models\Part\Part;
use App\Models\Stocker\Stocker;
use DB;
use Excel;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yajra\DataTables\Facades\DataTables;

class Lockcontroller extends Controller
{

    public function index() {
        return view("general.tools.lock");
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_awal'  => 'required|date',
            'tanggal_akhir' => 'required|date',
            'deskripsi'     => 'required|string'
        ]);

        DB::table('data_locks')->insert([
            'start_date'            => $request->tanggal_awal,
            'end_date'              => $request->tanggal_akhir,
            'description'           => $request->deskripsi,
            'is_locked'             => 0,
            'created_by'            => auth()->user()->id,
            'created_by_username'   => auth()->user()->username,
            'created_at'            => now(),
            'updated_at'            => now()
        ]);

        return response()->json([
            'status'  => 200,
            'message' => 'Data berhasil disimpan'
        ]);
    }

    public function delete(Request $request)
    {
        DB::table('data_locks')
            ->where('id', $request->id)
            ->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function getData(Request $request)
    {
        $tglAwal = $request->dateFrom;
        $tglAkhir = $request->dateTo;

        $data = DB::connection("mysql")->select("
            SELECT
                *,
                DATE_FORMAT(start_date, '%d-%m-%Y') AS start_date,
                DATE_FORMAT(end_date, '%d-%m-%Y') AS end_date,
                CASE
                    WHEN is_locked = 1 THEN 'Locked'
                    ELSE 'Unlocked'
                END AS status
            FROM
                data_locks
            ORDER BY
                id DESC
        ");

        return DataTables::of($data)->toJson();
    }

    public function locked(Request $request)
    {
        DB::table('data_locks')
            ->where('id', $request->id)
            ->update([
                'is_locked' => 1,
                'locked_by' => auth()->user()->id,
                'locked_by_username' => auth()->user()->username,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil di-lock'
        ]);
    }

    public function unlocked(Request $request)
    {
        DB::table('data_locks')
            ->where('id', $request->id)
            ->update([
                'is_locked' => 0,
                'unlocked_by' => auth()->user()->id,
                'unlocked_by_username' => auth()->user()->username,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => 200,
            'message' => 'Data berhasil di-unlock'
        ]);
    }
}