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

class IEMasterProcessController extends Controller
{
    public function IE_master_process(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("SELECT *, DATE_FORMAT(created_at, '%d-%m-%y %H:%i:%s') AS tgl_update_fix from ie_master_process
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('IE.master_process', [
            'page' => 'dashboard-IE',
            'subPageGroup' => 'IE-master',
            'subPage' => 'IE-master-process',
            'containerFluid' => true,
        ]);
    }

    public function IE_save_master_process(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $process_name = strtoupper($request->process_name);
        $class_name = strtoupper($request->class_name);
        $type = strtoupper($request->type);
        $remark = strtoupper($request->remark);
        $smv = $request->smv;
        $amv = $request->amv;
        $smv = is_numeric($smv) ? $smv : 0;
        $amv = is_numeric($amv) ? $amv : 0;



        // Check if the nm_process already exist
        $exists = DB::table('ie_master_process')
            ->where('nm_process', $process_name)
            ->where('remark', $remark)
            ->exists();

        if ($exists) {
            // Return error JSON response if record exists
            return response()->json([
                'status' => 'error',
                'message' => 'Data already exist.',
            ], 409); // 409 Conflict status code
        }

        DB::insert("INSERT INTO ie_master_process (
        nm_process,
        class,
        machine_type,
        smv,
        amv,
        remark,
        created_by,
        created_at,
        updated_at
    ) VALUES (?,?,?,?,?,?,?,?,?)", [
            $process_name,
            $class_name,
            $type,
            $smv,
            $amv,
            $remark,
            $user,
            $timestamp,
            $timestamp
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Process Sudah di tambahkan',
            'process_name' => $process_name,
        ]);
    }

    public function IE_show_master_process(Request $request)
    {
        $data = DB::select("
        SELECT * from ie_master_process where id = '$request->id_c'");
        return json_encode($data[0]);
    }

    public function IE_edit_master_process(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $process_name = strtoupper($request->process_name);
        $class_name = strtoupper($request->class_name);
        $txttype = strtoupper($request->txttype);
        $remark = strtoupper($request->remark);
        $id_c = $request->id_c;
        $smv = $request->smv;
        $amv = $request->amv;
        $smv = is_numeric($smv) ? $smv : 0;
        $amv = is_numeric($amv) ? $amv : 0;

        DB::update("UPDATE ie_master_process
    SET
        nm_process = ?,
        class = ?,
        machine_type = ?,
        smv = ?,
        amv = ?,
        remark = ?,
        updated_by = ?,
        updated_at = ?
    WHERE id = ?
", [
            $process_name,
            $class_name,
            $txttype,
            $smv,
            $amv,
            $remark,
            $user,
            $timestamp,
            $id_c
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Master Process Sudah di edit',
            'process_name' => $process_name,
        ]);
    }

    public function contoh_upload_master_process()
    {
        $path = public_path('storage/contoh_upload_master_process.xlsx');
        return response()->download($path);
    }

    public function upload_excel_master_process(Request $request)
    {
        // Validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        try {
            $file = $request->file('file');

            $nama_file = rand() . $file->getClientOriginalName();

            $file->move('file_upload', $nama_file);

            // Import Excel
            Excel::import(new ImportIE_MasterProcess, public_path('/file_upload/' . $nama_file));

            return [
                "status" => 200,
                "message" => 'Data Berhasil Di Upload',
                "additional" => [],
            ];
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Jika terjadi error validasi pada Excel
            $failures = $e->failures();
            return [
                "status" => 422,
                "message" => 'Data Gagal Di Upload. Ada kesalahan pada file Excel.',
                "errors" => $failures
            ];
        } catch (\Exception $e) {
            // Jika terjadi error lain
            return [
                "status" => 500,
                "message" => 'Data Gagal Di Upload. Terjadi kesalahan server.',
                "errors" => $e->getMessage()
            ];
        }
    }
}
