<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\MutKaryawan;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiKaryawan;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $additionalQuery = "";

            // if ($request->dateFrom) {
            //     $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            // }

            // if ($request->dateTo) {
            //     $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
            // }

            $keywordQuery = "";
            if ($request->search["value"]) {
                $keywordQuery = "
                     (
                        line like '%" . $request->search["value"] . "%'
                    )
                ";
            }

            $data_line = DB::select("
            SELECT
                line,
                count(id) tot_orang
            from
            (
                select a.id, b.tgl_pindah,b.nik,b.nm_karyawan,b.line from
                (select max(id) id from mut_karyawan_input a
                group by nik)a
                inner join mut_karyawan_input b on a.id = b.id
            ) master_karyawan
            group by line
            ");

            return DataTables::of($data_line)->toJson();
        }


        // if ($request->ajax()) {
        //     $employeeQuery = Employee::get();

        //     return DataTables::eloquent($employeeQuery)->toJson();;
        // }
        return view('employee.employee', ['page' => 'dashboard-mut-karyawan']);
    }

    public function create()
    {

        return view('employee.create-employee', ['page' => 'dashboard-mut-karyawan']);
    }

    public function getdataline(Request $request)
    {

        $master_line = DB::connection('mysql_hris')
            ->select("SELECT cast(right(sub_dept_name,2) as unsigned) urutan,
            sub_dept_name nm_line
            from department_all
            where sub_dept_name like '" . $request->txtline . "%'
            group by sub_dept_name
            order by cast(right(sub_dept_name,2) as unsigned) asc");

        // '%" . $request->txtline . "%'
        // $data_marker = DB::select("select a.* from marker_input a
        // where a.id = '" . $request->cri_item . "'");

        return json_encode($master_line[0]);
    }


    public function gettotal(Request $request)
    {
        $total = DB::select("
        select count(nik) total from
        (select max(id) id from mut_karyawan_input a
        group by nik)a
        inner join mut_karyawan_input b on a.id = b.id
        where line ='" . $request->nm_line . "'
        ");
        return json_encode($total[0]);
    }

    public function getdatalinekaryawan(Request $request)
    {
        $det_karyawan_line = DB::select("
        select a.id, b.* from
        (select max(id) id from mut_karyawan_input a
        group by nik)a
        inner join mut_karyawan_input b on a.id = b.id
        where line ='" . $request->nm_line . "'
        order by updated_at desc
        ");
        return DataTables::of($det_karyawan_line)->toJson();
    }


    public function getdatanik(Request $request)
    {

        $master_karyawan = DB::connection('mysql_hris')
            ->select("select employee_id, employee_name from employee_atribut
            where nik ='" . $request->txtnik . "' or nik_new ='" . $request->txtnik . "' ");
        return json_encode($master_karyawan[0]);
    }

    public function store(Request $request)
    {
        $tglpindah = date('Y-m-d');
        $timestamp = Carbon::now();
        $nik       = $request->txtnik;

        $line_asal = DB::select("
        select line,nik, nm_karyawan from (
            select a.id, b.tgl_pindah,b.nik,b.nm_karyawan,b.line from
            (select max(id) id from mut_karyawan_input a
            group by nik)a
            inner join mut_karyawan_input b on a.id = b.id
            ) master_karyawan
        where nik ='$nik'
        ");

        if ($line_asal[0]->line == $request->nm_line) {
            return array(
                'icon' => 'error',
                'msg' => 'Data Sudah Ada',
                'timer' => false,
                'prog' => true,

            );
        } else {
            $savemutasi = MutKaryawan::create([
                "tgl_pindah" => $tglpindah,
                "nik" => $request["txtnik"],
                "nm_karyawan" => $request["nm_karyawan"],
                "line" => $request["nm_line"],
                "line_asal" => $line_asal[0]->line,
                "created_at" => $timestamp,
                "updated_at" => $timestamp,
            ]);
            // dd($savemutasi);
            // $message .= "$tglpindah <br>";

        }

        return array(
            'icon' => 'success',
            'msg' => 'Data Sudah Tersimpan',
            'timer' => 1500,
            'prog' => false,
        );
    }

    public function export_excel_mut_karyawan(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    }
}
