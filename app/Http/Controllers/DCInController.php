<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\MutKaryawan;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiKaryawan;
use App\Models\DCIn;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class DCInController extends Controller
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

            $dc_in_index_group = DB::select("
            select a.no_form,
            a.no_cut,
            p.act_costing_ws,
            p.buyer,
            p.style,
            mi.color,
            b.list_part
            from part p
            inner join part_form pf on p.id = pf.part_id
            inner join form_cut_input a on pf.form_id = a.id
            inner join marker_input mi on a.id_marker = mi.kode
            inner join
            (
            select part_id,group_concat(mp.nama_part ORDER BY mp.id ASC) list_part from part_detail a
            inner join master_part mp on a.master_part_id = mp.id
            group by part_id
            ) b on p.id = b.part_id
            inner join stocker_input c on a.id = c.form_cut_id
            group by no_form
            order by act_costing_ws asc, no_cut asc
            ");


            return DataTables::of($dc_in_index_group)->toJson();
        }
        return view('dc-in.dc-in', ['page' => 'dashboard-dc'], ['tgl_skrg' => $tgl_skrg]);
    }

    public function create(Request $request, $no_form = 0)

    {
        $header_data = DB::select("
        select a.no_form,a.no_cut,p.*,b.list_part from part p
        inner join part_form pf on p.id = pf.part_id
        inner join form_cut_input a on pf.form_id = a.id
        inner join
        (
        select part_id,group_concat(mp.nama_part ORDER BY mp.id ASC) list_part from part_detail a
        inner join master_part mp on a.master_part_id = mp.id
        group by part_id
        ) b on p.id = b.part_id
        where a.no_form = '" . $no_form . "'
        order by act_costing_ws asc, no_cut asc
        ");

        return view('dc-in.create-dc-in', ['page' => 'dashboard-dc', 'header' => $header_data[0]],);
    }


    public function getdata_stocker_info(Request $request)
    {
        $det_dc_in = DB::select(
            "SELECT a.no_form,mp.nama_part,mp.id,s.* FROM `stocker_input` s
            inner join form_cut_input a on s.form_cut_id = a.id
            inner join part_detail p on s.part_detail_id = p.id
            inner join master_part mp on p.master_part_id = mp.id
            where no_form = '" . $request->no_form . "'
            order by color asc, size asc "
        );

        return DataTables::of($det_dc_in)->toJson();
    }

    public function getdata_dc_in(Request $request)
    {
        $det_dc_in = DB::select(
            "select * from dc_in_input a
            inner join stocker_input b on a.id_qr_stocker = b.id_qr_stocker"
        );

        return DataTables::of($det_dc_in)->toJson();
    }

    public function gettotal(Request $request)
    {
        $total =  DB::connection('mysql_hris')->select(
            "
        select count(nik) total from
        (select max(id) id from mut_karyawan_input a
        group by nik)a
        inner join mut_karyawan_input b on a.id = b.id
        where line ='" . $request->nm_line . "'
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
            where enroll_id ='" . $request->txtenroll_id . "' and status_aktif = 'AKTIF'",
        );
        return json_encode($master_karyawan[0]);
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $savemutasi = DCIn::create([
            'id_qr_stocker' => $request['txtqrstocker'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    public function export_excel_mut_karyawan(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    }
}
