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

class IEMasterPartProcessController extends Controller
{
    public function IE_master_part_process(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("SELECT
            id_part_process,
            picture,
            nm_part_process,
            count(a.id) tot_process,
            sum(b.smv) tot_smv,
            sum(b.amv) tot_amv,
            a.created_by,
            DATE_FORMAT(a.updated_at, '%d-%m-%y %H:%i:%s') AS tgl_update_fix
            FROM ie_master_part_process a
            inner join ie_master_process b on a.id_process = b.id
            group by id_part_process
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('IE.master_part_process', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-master',
            'subPage' => 'IE-master-part-process',
            'containerFluid' => true,
        ]);
    }

    public function IE_master_part_process_show_new(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("SELECT * from ie_master_process order by nm_process asc, remark asc
            ");

            return DataTables::of($data_input)->toJson();
        }
    }

    public function IE_save_master_part_process(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $ids = $request->ids;

        // Validasi
        if (!$ids || count($ids) == 0) {
            return response()->json(['message' => 'No process selected'], 400);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $today = date('dmy'); // misal 11 Des 2025 → 111225

        // Ambil ID terakhir hari ini
        $lastId = DB::table('ie_master_part_process')
            ->where('id_part_process', 'like', 'PP' . $today . '_%')
            ->max('id_part_process');

        if ($lastId) {
            // Ambil angka urutan terakhir
            $parts = explode('_', $lastId);
            $lastNumber = (int) $parts[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // belum ada, mulai dari 1
        }

        // Format angka jadi 2 digit
        $numberFormatted = str_pad($newNumber, 2, '0', STR_PAD_LEFT);

        // Buat ID baru dengan prefix PP
        $id_part_process = 'PP' . $today . '_' . $numberFormatted;

        $name = strtoupper($request->name);

        // CEK apakah nama sudah ada
        $exists = DB::table('ie_master_part_process')
            ->where('nm_part_process', $name)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Master Part Process dengan nama "' . $name . '" sudah ada'
            ], 400);
        }
        $filename = null;
        if ($request->hasFile('picture')) {
            $extension = $request->file('picture')->getClientOriginalExtension();
            $filename = $id_part_process . '.' . $extension;
            $request->file('picture')->storeAs('public/gambar_part_process', $filename);
        }


        // Siapkan data untuk batch insert
        $insertData = [];

        foreach ($ids as $processId) {
            $insertData[] = [
                'id_part_process' => $id_part_process,
                'id_process'      => $processId,
                'nm_part_process' => $name,
                'picture'         => $filename,
                'created_by'      => $user,
                'created_at'      => $timestamp,
                'updated_at'      => $timestamp,
            ];
        }

        // Insert semua sekaligus
        DB::table('ie_master_part_process')->insert($insertData);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Part Process : ' . $name . ' sudah ditambahkan',
        ]);
    }

    public function IE_show_master_part_process(Request $request)
    {
        $data = DB::select("
        SELECT * from ie_master_part_process where id_part_process = '$request->id_c' limit 1");
        return json_encode($data[0]);
    }

    public function IE_master_part_process_show_edit(Request $request)
    {
        $id_c = $request->id_c;

        // 1. Ambil semua proses
        $all = DB::select("
        SELECT a.*, b.id_part_process,
        CASE WHEN b.id_part_process IS NOT NULL THEN 1 ELSE 0 END AS selected
        FROM ie_master_process a
        LEFT JOIN ie_master_part_process b
        ON a.id = b.id_process AND b.id_part_process = '$id_c'
        ORDER BY selected DESC, a.nm_process ASC;
    ");

        // 2. Ambil ID proses yang sudah dipilih sebelumnya
        $selected = DB::select("
        SELECT id_process
        FROM ie_master_part_process
        WHERE id_part_process = ?
    ", [$id_c]);

        // Ubah jadi array integer saja
        $selected_ids = array_map(function ($row) {
            return $row->id_process;
        }, $selected);

        // 3. Generate format DataTables
        $data = [];

        foreach ($all as $row) {
            $data[] = [
                'id'           => $row->id,
                'nm_process'   => $row->nm_process,
                'class'        => $row->class,
                'machine_type' => $row->machine_type,
                'smv'          => $row->smv,
                'amv'          => $row->amv,
                'remark'       => $row->remark,

                // checkbox default tercentang jika id masuk dalam selected
                'selected'     => in_array($row->id, $selected_ids)
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function IE_update_master_part_process(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $id_c = $request->id_c;
        $ids = json_decode($request->selected_ids, true);
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        if (!$ids || count($ids) == 0) {
            return response()->json(['message' => 'No process selected'], 400);
        }

        // ====== Ambil data lama untuk gambar ======
        $old = DB::table('ie_master_part_process')
            ->where('id_part_process', $id_c)
            ->first();

        if (!$old) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $old_picture = $old->picture;
        $newFileName = $old_picture;

        // ====== HANDLE GAMBAR BARU ======
        if ($request->hasFile('picture')) {
            $extension = $request->file('picture')->getClientOriginalExtension();
            $newFileName = $id_c . '.' . $extension;

            // Upload gambar baru
            $request->file('picture')->storeAs('public/gambar_part_process', $newFileName);

            // Hapus gambar lama
            if ($old_picture && file_exists(storage_path("public/gambar_part_process/" . $old_picture))) {
                unlink(storage_path("public/gambar_part_process/" . $old_picture));
            }
        }

        $name = strtoupper($request->name);

        // ====== CEK duplikasi nama (kecuali dirinya sendiri) ======
        $exists = DB::table('ie_master_part_process')
            ->where('nm_part_process', $name)
            ->where('id_part_process', '!=', $id_c)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Master Part Process dengan nama "' . $name . '" sudah ada'
            ], 400);
        }

        // ====== HAPUS SEMUA RECORD LAMA (seperti SAVE) ======
        DB::table('ie_master_part_process')
            ->where('id_part_process', $id_c)
            ->delete();

        // ====== INSERT ULANG DATA ======
        $insertData = [];

        foreach ($ids as $process_id) {
            $insertData[] = [
                'id_part_process' => $id_c,
                'id_process'      => $process_id,
                'nm_part_process' => $name,
                'picture'         => $newFileName,
                'created_by'      => $user,
                'created_at'      => $timestamp,
                'updated_at'      => $timestamp,
            ];
        }

        DB::table('ie_master_part_process')->insert($insertData);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Part Process berhasil diupdate.',
        ]);
    }
}
