<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MutMesin;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiMesin;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class MutasiMesinController extends Controller
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
            select line, count(id_qr) tot_mesin from
            (select max(id) id from mut_mesin_input a group by id_qr)a
            inner join mut_mesin_input b on a.id = b.id
            group by line
            order by cast(right(line,2) as UNSIGNED) asc
            ");


            return DataTables::of($data_line)->toJson();
        }

        // if ($request->ajax()) {
        //     $employeeQuery = Employee::get();

        //     return DataTables::eloquent($employeeQuery)->toJson();;
        // }
        return view('mut-mesin.mut-mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'proses-mut-mesin', 'subPage' => 'mut-mesin'], ['tgl_skrg' => $tgl_skrg]);
    }

    public function lineChartData()
    {
        $data_line =  DB::select("
        SELECT
        line,
        count(id) tot_mesin,
        cast(right(line,2) as UNSIGNED) urutan
    from
    (
        select a.id, b.tgl_pindah,b.id_qr, line from
        (select max(id) id from mut_mesin_input a
        group by id_qr)a
        inner join mut_mesin_input b on a.id = b.id
    ) master_karyawan
    group by line
    order by cast(right(line,2) as UNSIGNED) asc
            ");

        return json_encode($data_line);
    }

    public function create()
    {
        return view('mut-mesin.create-mut-mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'proses-mut-mesin', 'subPage' => 'mut-mesin']);
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
        $total =  DB::select(
            "
            select count(id_qr) total from
            (select max(id) id from mut_mesin_input a
            group by id_qr)a
            inner join mut_mesin_input b on a.id = b.id
        where line ='" .
                $request->nm_line .
                "'
        ",
        );
        return json_encode($total[0]);
    }

    public function getdatalinemesin(Request $request)
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

        $det_mesin_line =  DB::select("
        select a.id, b.*,
        c.jenis_mesin,
        c.brand,
        c.tipe_mesin,
        c.serial_no,
        DATE_FORMAT(tgl_pindah, '%d-%m-%Y') tgl_pindah_fix,
        DATE_FORMAT(b.updated_at, '%d-%m-%Y %H:%i:%s') tgl_update_fix
        from
        (select max(id) id from mut_mesin_input a
        group by id_qr)a
        inner join mut_mesin_input b on a.id = b.id
        inner join master_mesin c on b.id_qr = c.id_qr
        where line ='" . $request->nm_line . "'
        order by updated_at desc
        ");
        return DataTables::of($det_mesin_line)->toJson();
    }

    public function getdatamesin(Request $request)
    {
        $master_karyawan = DB::select(
            "select *  from master_mesin
            where id_qr ='" . $request->txtenroll_id . "'",
        );
        return json_encode($master_karyawan[0]);
    }

    public function store(Request $request)
    {
        $tglpindah = date('Y-m-d');
        $timestamp = Carbon::now();
        $enroll_id = $request->txtenroll_id;

        $line_asal =  DB::select("
        select line,id_qr from (
            select a.id, b.tgl_pindah,b.id_qr,b.line from
            (select max(id) id from mut_mesin_input a
            group by id_qr)a
            inner join mut_mesin_input b on a.id = b.id
            ) master_mesin
        where id_qr ='$enroll_id'
        ");
        $line_asal_data = $line_asal ? $line_asal[0]->line : null;

        // if ($line_asal_data == $request->nm_line) {
        //     return [
        //         'icon' => 'error',
        //         'msg' => 'Data Sudah Ada',
        //         'timer' => false,
        //         'prog' => true,
        //     ];
        // } else {
        $savemutasi = MutMesin::create([
            'tgl_pindah' => $tglpindah,
            'id_qr' => $request['txtenroll_id'],
            'line' => $request['nm_line'],
            'line_asal' => $line_asal_data,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
        // dd($savemutasi);
        // $message .= "$tglpindah <br>";
        // }

        return [
            'icon' => 'success',
            'msg' => 'Data Sudah Tersimpan',
            'timer' => 1500,
            'prog' => false,
        ];
    }

    public function export_excel_mut_mesin(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiMesin($request->from, $request->to), 'Laporan_Mutasi_Mesin.xlsx');
    }
}
