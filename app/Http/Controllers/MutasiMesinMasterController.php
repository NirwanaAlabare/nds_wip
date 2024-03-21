<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MutMesin;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiMesinMaster;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use DB;

class MutasiMesinMasterController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        if ($request->ajax()) {

            $data_mesin = DB::select("
            select * from master_mesin
            order by id_qr asc,brand asc, tipe_mesin asc, serial_no asc
            ");


            return DataTables::of($data_mesin)->toJson();
        }
        return view('mut-mesin.master_mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'master-mut-mesin', 'subPage' => 'master-mut-mesin'], ['tgl_skrg' => $tgl_skrg]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $tglpindah = date('Y-m-d');
        $timestamp = Carbon::now();
        $txtkode_qr = $request->txtkode_qr;
        $txtjenis = $request->txtjenis;
        $txtbrand = $request->txtbrand;
        $txttipe = $request->txttipe;
        $txtserial_no = $request->txtserial_no;

        DB::insert(
            "insert into master_mesin
            (id_qr,jenis_mesin,brand,tipe_mesin,serial_no,created_by,created_at,updated_at)
            VALUES ('" . $txtkode_qr . "','$txtjenis','" . $txtbrand . "','" . $txttipe . "'
            ,'$txtserial_no','$user','$timestamp','$timestamp')
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data ' . $txtkode_qr . ' Sudah Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }

    public function export_excel_master_mesin(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiMesinMaster(), 'Laporan_Mutasi_Master_Mesin.xlsx');
    }
}
