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
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        if ($request->ajax()) {
            $additionalQuery = '';

            // if ($request->dateFrom) {
            //     $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            // }

            // if ($request->dateTo) {
            //     $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
            // }

            $keywordQuery = '';
            if ($request->search['value']) {
                $keywordQuery =
                    "
                     (
                        line like '%" .
                    $request->search['value'] .
                    "%'
                    )
                ";
            }

            // $data_line = DB::select("
            // SELECT
            //     line,
            //     count(id) tot_orang,
            //     cast(right(line,2) as UNSIGNED) urutan
            // from
            // (
            //     select a.id, b.tgl_pindah,b.nik,b.nm_karyawan,b.line from
            //     (select max(id) id from mut_karyawan_input a
            //     group by nik)a
            //     inner join mut_karyawan_input b on a.id = b.id
            // ) master_karyawan
            // group by line
            // order by cast(right(line,2) as UNSIGNED) asc
            // ");

            $data_line = DB::select("
            SELECT
                line,
                count(id) tot_orang,
				count(absen_masuk_kerja is not null or absen_masuk_kerja != '') tot_absen,
				count(id) - count(absen_masuk_kerja is not null or absen_masuk_kerja != '') selisih,
                cast(right(line,2) as UNSIGNED) urutan,
                status_aktif
            from
            (
                select a.id, b.tgl_pindah,b.nik,b.nm_karyawan,b.line, absen_masuk_kerja,status_aktif from
                (select max(id) id from mut_karyawan_input a
                group by nik)a
                inner join mut_karyawan_input b on a.id = b.id
				left join (select enroll_id, absen_masuk_kerja, status_aktif from master_data_absen_kehadiran where tanggal_berjalan = '".$tglskrg."' and status_aktif = 'AKTIF') c on b.enroll_id = c.enroll_id
            ) master_karyawan
            where status_aktif = 'AKTIF' or status_aktif is null
            group by line
            order by cast(right(line,2) as UNSIGNED) asc
            ");


            return DataTables::of($data_line)->toJson();
        }

        // if ($request->ajax()) {
        //     $employeeQuery = Employee::get();

        //     return DataTables::eloquent($employeeQuery)->toJson();;
        // }
        return view('employee.employee', ['page' => 'dashboard-mut-karyawan'], ['tgl_skrg' => $tgl_skrg]);
    }

    public function lineChartData()
    {
        $data_line =  DB::connection('mysql_hris')->select("
            SELECT
                line,
                count(id) tot_orang,
                cast(right(line,2) as UNSIGNED) urutan
            from
            (
                select a.id, b.tgl_pindah,b.nik,b.nm_karyawan,b.line from
                (select max(id) id from mut_karyawan_input a
                group by nik)a
                inner join mut_karyawan_input b on a.id = b.id
            ) master_karyawan
            group by line
            order by cast(right(line,2) as UNSIGNED) asc
            ");

        return json_encode($data_line);
    }

    public function create()
    {
        return view('employee.create-employee', ['page' => 'dashboard-mut-karyawan']);
    }

    public function getdataline(Request $request)
    {
        $master_line = DB::connection('mysql_hris')->select(
            "SELECT cast(right(sub_dept_name,2) as unsigned) urutan,
            sub_dept_name nm_line
            from department_all
            where sub_dept_name like '" .
                $request->txtline .
                "%'
            group by sub_dept_name
            order by cast(right(sub_dept_name,2) as unsigned) asc",
        );

        // '%" . $request->txtline . "%'
        // $data_marker = DB::select("select a.* from marker_input a
        // where a.id = '" . $request->cri_item . "'");

        return json_encode($master_line[0]);
    }

    public function gettotal(Request $request)
    {
        $total =  DB::connection('mysql_hris')->select(
            "
        select count(nik) total from
        (select max(id) id from mut_karyawan_input a
        group by nik)a
        inner join mut_karyawan_input b on a.id = b.id
        where line ='" .
                $request->nm_line .
                "'
        ",
        );
        return json_encode($total[0]);
    }

    public function getdatalinekaryawan(Request $request)
    {
        $tglskrg = date('Y-m-d');
        // $det_karyawan_line = DB::select("
        // select a.id, b.*,
        // DATE_FORMAT(tgl_pindah, '%d-%m-%Y') tgl_pindah_fix,
        // DATE_FORMAT (updated_at, '%d-%m-%Y %H:%i:%s') tgl_update_fix
        // from
        // (select max(id) id from mut_karyawan_input a
        // group by nik)a
        // inner join mut_karyawan_input b on a.id = b.id
        // where line ='" . $request->nm_line . "'
        // order by updated_at desc
        // ");
        // return DataTables::of($det_karyawan_line)->toJson();

        $det_karyawan_line =  DB::connection('mysql_hris')->select("
        select a.id, b.*,
        c.absen_masuk_kerja,
        DATE_FORMAT(tgl_pindah, '%d-%m-%Y') tgl_pindah_fix,
        DATE_FORMAT(b.updated_at, '%d-%m-%Y %H:%i:%s') tgl_update_fix,
        c.status_aktif
        from
        (select max(id) id from mut_karyawan_input a
        group by nik)a
        inner join mut_karyawan_input b on a.id = b.id
        left join (select enroll_id, absen_masuk_kerja, status_aktif from master_data_absen_kehadiran where tanggal_berjalan = '" . $tglskrg . "') c on b.enroll_id = c.enroll_id
        where status_aktif = 'AKTIF' or status_aktif is null and line ='" . $request->nm_line . "'
        order by updated_at desc
        ");
        return DataTables::of($det_karyawan_line)->toJson();


    }

    public function getdatanik(Request $request)
    {
        $master_karyawan = DB::connection('mysql_hris')->select(
            "select enroll_id,ifnull(nik,nik_new) nik, employee_name from employee_atribut
            where enroll_id ='" .$request->txtenroll_id ."' and status_aktif = 'AKTIF'",
        );
        return json_encode($master_karyawan[0]);
    }

    public function store(Request $request)
    {
        $tglpindah = date('Y-m-d');
        $timestamp = Carbon::now();
        $enroll_id = $request->txtenroll_id;

        $line_asal =  DB::connection('mysql_hris')->select("
        select line,nik,enroll_id, nm_karyawan from (
            select a.id, b.tgl_pindah,b.enroll_id,b.nik,b.nm_karyawan,b.line from
            (select max(id) id from mut_karyawan_input a
            group by nik)a
            inner join mut_karyawan_input b on a.id = b.id
            ) master_karyawan
        where enroll_id ='$enroll_id'
        ");
        $line_asal_data = $line_asal ? $line_asal[0]->line : null;

        if ($line_asal_data == $request->nm_line) {
            return [
                'icon' => 'error',
                'msg' => 'Data Sudah Ada',
                'timer' => false,
                'prog' => true,
            ];
        } else {
            $savemutasi = MutKaryawan::create([
                'tgl_pindah' => $tglpindah,
                'enroll_id' => $request['txtenroll_id'],
                'nik' => $request['nik'],
                'nm_karyawan' => $request['nm_karyawan'],
                'line' => $request['nm_line'],
                'line_asal' => $line_asal_data,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            // dd($savemutasi);
            // $message .= "$tglpindah <br>";
        }

        return [
            'icon' => 'success',
            'msg' => 'Data Sudah Tersimpan',
            'timer' => 1500,
            'prog' => false,
        ];
    }

    public function export_excel_mut_karyawan(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    }
}
