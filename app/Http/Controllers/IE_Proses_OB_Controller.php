<?php

namespace App\Http\Controllers;

use App\Imports\ImportDailyCost;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IE_Proses_OB_Controller extends Controller
{
    public function IE_proses_op_breakdown(Request $request)
    {

        $user = Auth::user()->name;
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        if ($request->ajax()) {
            $data_input = DB::select("SELECT
            id_op_breakdown,
            DATE_FORMAT(a.updated_at, '%d-%m-%y') AS tgl_trans_fix,
            a.tgl_trans,
            a.picture,
            a.style,
            a.brand,
            product_group,
            round(sum(c.smv),3) tot_smv,
            request_by,
            request_date,
            DATE_FORMAT(a.request_date, '%d-%m-%y') AS request_date_fix,
            DATE_FORMAT(a.due_date, '%d-%m-%y') AS due_date_fix,
            a.status,
            a.created_by,
            DATE_FORMAT(a.created_at, '%d-%m-%y %H:%i:%s') AS tgl_create_fix
            from ie_op_breakdown a
            left join signalbit_erp.masterproduct mp on a.id_product = mp.id
            left join ie_master_part_process b on a.id_part_process = b.id_part_process
            left join ie_master_process c on b.id_process = c.id
            where a.tgl_trans >= '$tgl_awal' and a.tgl_trans <= '$tgl_akhir'
            group by id_op_breakdown
        ");

            return response()->json([
                'data' => $data_input // ✅ simplified response
            ]);
        }

        $data_product = DB::connection('mysql_sb')->select("SELECT id as isi, concat(product_group , ' - ', product_item) as tampil
            from masterproduct
            order by product_group asc");

        $data_request = DB::connection('mysql_hris')->select("SELECT employee_name as isi, employee_name as tampil from employee_atribut where status_aktif = 'AKTIF' AND sub_dept_name = 'MERCHANDISER'
ORDER BY employee_name asc");

        // For non-AJAX (initial page load)
        return view('IE.proses_op_breakdown', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-proses',
            'subPage' => 'IE-proses-op-breakdown',
            'containerFluid' => true,
            'data_product' => $data_product,
            'data_request' => $data_request,
            'user' => $user,
        ]);
    }


    public function show_modal_proses_breakdown_new(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("SELECT
            id_part_process,
            picture,
            nm_part_process,
            count(a.id) tot_process,
            ROUND(SUM(b.smv), 3) AS tot_smv,
            ROUND(SUM(b.amv), 3) AS tot_amv,
            a.created_by,
            DATE_FORMAT(a.updated_at, '%d-%m-%y %H:%i:%s') AS tgl_update_fix
            FROM ie_master_part_process a
            inner join ie_master_process b on a.id_process = b.id
            group by id_part_process
            ");

            return DataTables::of($data_input)->toJson();
        }
    }

    public function show_modal_summary_breakdown(Request $request)
    {
        if ($request->ajax()) {

            $ids = $request->ids; // array dari JS

            if (empty($ids)) {
                return DataTables::of([])->toJson();
            }

            // Buat placeholder untuk binding
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Raw query dengan binding
            $query = "SELECT
                    picture,
                    nm_part_process,
                    nm_process,
                    class,
                    smv,
                    machine_type,
                    remark
                    FROM ie_master_part_process a
                    left join ie_master_process b on a.id_process = b.id
                    where id_part_process IN ($placeholders)
                    ORDER BY nm_part_process ASC, nm_process ASC
        ";

            // Eksekusi query dengan binding array $ids
            $data_input = DB::select($query, $ids);

            return DataTables::of($data_input)->toJson();
        }
    }

    public function IE_save_op_breakdown(Request $request)
    {

        $request->validate([
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
        ]);

        $ids = $request->ids;

        // Validasi
        if (!$ids || count($ids) == 0) {
            return response()->json(['message' => 'No process selected'], 400);
        }

        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $today = date('Y-m-d'); // misal 11 Des 2025 → 111225

        // Ambil ID terakhir hari ini
        $lastId = DB::table('ie_op_breakdown')
            ->where('id_op_breakdown', 'like', 'OB' . $today . '_%')
            ->max('id_op_breakdown');

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
        $id_op_breakdown = 'OB' . $today . '_' . $numberFormatted;

        $style = strtoupper($request->style);
        $brand = strtoupper($request->brand);
        $id_product = $request->cbo_prod;
        $cbo_req = strtoupper($request->cbo_req);
        $req_date = strtoupper($request->req_date);
        $due_date = strtoupper($request->due_date);
        $stat = $request->stat;

        // CEK apakah nama sudah ada
        $exists = DB::table('ie_op_breakdown')
            ->where('style', $style)
            ->where('brand', $brand)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Operation Breakdown dengan style "' . $style . '" and brand "' . $brand . '" sudah ada'
            ], 400);
        }
        $filename = null;
        if ($request->hasFile('picture')) {
            $extension = $request->file('picture')->getClientOriginalExtension();
            $filename = $id_op_breakdown . '.' . $extension;
            $request->file('picture')->storeAs('public/gambar_op_breakdown', $filename);
        }


        // Siapkan data untuk batch insert
        $insertData = [];

        foreach ($ids as $processId) {
            $insertData[] = [
                'id_op_breakdown'   => $id_op_breakdown,
                'id_part_process'   => $processId,
                'tgl_trans'         => $today,
                'picture'           => $filename,
                'style'             => $style,
                'brand'             => $brand,
                'id_product'        => $id_product,
                'request_by'        => $cbo_req,
                'request_date'      => $req_date,
                'due_date'          => $due_date,
                'status'            => $stat,
                'created_by'        => $user,
                'created_at'        => $timestamp,
                'updated_at'        => $timestamp,
            ];
        }

        // Insert semua sekaligus
        DB::table('ie_op_breakdown')->insert($insertData);

        return response()->json([
            'status' => 'success',
            'message' => 'Style : ' . $style . ' Dengan Brand : ' . $brand . ' sudah ditambahkan',
        ]);
    }

    public function IE_show_op_breakdown(Request $request)
    {
        $data = DB::select("
        SELECT * from ie_op_breakdown where id_op_breakdown = '$request->id_c' limit 1");
        return json_encode($data[0]);
    }


    public function IE_show_op_breakdown_edit(Request $request)
    {
        $id_c = $request->id_c;

        $data = DB::select("
        SELECT
            pp.id_part_process,
            pp.picture,
            pp.nm_part_process,
            pp.tot_process,
            pp.tot_smv,
            pp.tot_amv,
            CASE
                WHEN ob.id_part_process IS NOT NULL THEN 1
                ELSE 0
            END AS selected
        FROM
        (
            SELECT
                a.id_part_process,
                a.picture,
                a.nm_part_process,
                COUNT(a.id) AS tot_process,
    ROUND(SUM(b.smv), 3) AS tot_smv,
    ROUND(SUM(b.amv), 3) AS tot_amv
            FROM ie_master_part_process a
            INNER JOIN ie_master_process b
                ON a.id_process = b.id
            GROUP BY a.id_part_process
        ) pp
        LEFT JOIN ie_op_breakdown ob
            ON pp.id_part_process = ob.id_part_process
            AND ob.id_op_breakdown = ?
        ORDER BY selected DESC, pp.nm_part_process ASC
    ", [$id_c]);

        return response()->json([
            'data' => $data
        ]);
    }

    public function IE_update_op_breakdown(Request $request)
    {

        $id_c = $request->id_c;
        $ids = json_decode($request->selected_ids, true);
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        if (!$ids || count($ids) == 0) {
            return response()->json(['message' => 'No process selected'], 400);
        }

        // ====== Ambil data lama untuk gambar ======
        $old = DB::table('ie_op_breakdown')
            ->where('id_op_breakdown', $id_c)
            ->first();

        if (!$old) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $old_picture = $old->picture;
        $tgl_trans = $old->tgl_trans;
        $newFileName = $old_picture;

        // ====== HANDLE GAMBAR BARU ======
        if ($request->hasFile('picture')) {
            $extension = $request->file('picture')->getClientOriginalExtension();
            $newFileName = $id_c . '.' . $extension;

            // Upload gambar baru
            $request->file('picture')->storeAs('public/gambar_op_breakdown', $newFileName);

            // Hapus gambar lama
            if ($old_picture && file_exists(storage_path("public/gambar_op_breakdown/" . $old_picture))) {
                unlink(storage_path("public/gambar_op_breakdown/" . $old_picture));
            }
        }

        $style = strtoupper($request->style);
        $brand = strtoupper($request->brand);
        $id_product = $request->cbo_prod;
        $cbo_req = strtoupper($request->cbo_req);
        $req_date = strtoupper($request->req_date);
        $due_date = strtoupper($request->due_date);
        $stat = $request->stat;

        // ====== CEK duplikasi nama (kecuali dirinya sendiri) ======
        $exists = DB::table('ie_op_breakdown')
            ->where('style', $style)
            ->where('brand', $brand)
            ->where('id_op_breakdown', '!=', $id_c)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Master Part Process dengan Style "' . $style . '" dan Brand "' . $brand . '" sudah ada'
            ], 400);
        }

        // ====== HAPUS SEMUA RECORD LAMA (seperti SAVE) ======
        DB::table('ie_op_breakdown')
            ->where('id_op_breakdown', $id_c)
            ->delete();

        // ====== INSERT ULANG DATA ======
        $insertData = [];

        foreach ($ids as $process_id) {
            $insertData[] = [
                'id_op_breakdown'   => $id_c,
                'id_part_process'   => $process_id,
                'tgl_trans'         => $tgl_trans,
                'picture'           => $newFileName,
                'style'             => $style,
                'brand'             => $brand,
                'id_product'        => $id_product,
                'request_by'        => $cbo_req,
                'request_date'      => $req_date,
                'due_date'          => $due_date,
                'status'            => $stat,
                'created_by'        => $user,
                'created_at'        => $timestamp,
                'updated_at'        => $timestamp,
            ];
        }

        DB::table('ie_op_breakdown')->insert($insertData);

        return response()->json([
            'status' => 'success',
            'message' => 'Operational Breakdown berhasil diupdate.',
        ]);
    }
}
