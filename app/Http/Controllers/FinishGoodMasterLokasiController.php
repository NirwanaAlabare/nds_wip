<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class FinishGoodMasterLokasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT * from fg_fg_master_lok
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view(
            'finish_good.finish_good_master_lokasi',
            [
                'page' => 'dashboard-finish-good',
                "subPageGroup" => "finish_good_master_lokasi",
                "subPage" => "finish_good_master_lokasi",
            ]
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $tglinput = date('Y-m-d');
        $timestamp = Carbon::now();
        $txtkode_lokasi = strtoupper($request->txtkode_lokasi);
        $txtlokasi = strtoupper($request->txtlokasi);
        $txtket = strtoupper($request->txtket);
        DB::insert(
            "insert into fg_fg_master_lok
            (kode_lok,lokasi,ket,cancel,created_by,created_at,updated_at)
            VALUES ('$txtkode_lokasi','$txtlokasi','$txtket','N','$user','$timestamp','$timestamp')"
        );

        return array(
            'status' => 300,
            'message' => 'Data ' . $txtkode_lokasi . ' Sudah Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }

    public function getdata_lokasi_alokasi(Request $request)
    {
        $cek_data = DB::select("
        SELECT * FROM fg_fg_master_lok	where id = '$request->id_e'
        ");
        return json_encode($cek_data ? $cek_data[0] : null);
    }

    public function edit_finish_good_master_lokasi(Request $request)
    {
        $user = Auth::user()->name;
        $tglpindah = date('Y-m-d');
        $timestamp = Carbon::now();
        $txtid_lokasi_edit = $request->txtid_lokasi_edit;
        $txtkode_lokasi_edit = strtoupper($request->txtkode_lokasi_edit);
        $txt_lokasi_edit = strtoupper($request->txtlok_edit);
        $txtket_edit = strtoupper($request->txtket_edit);

        DB::update(
            "update fg_fg_master_lok
            set kode_lok = '$txtkode_lokasi_edit', lokasi = '$txt_lokasi_edit', ket = '$txtket_edit', cancel = 'N', updated_at = '$timestamp'
            where id = '$txtid_lokasi_edit'
            "
        );
        return array(
            'status' => 300,
            'message' => 'Data ' . $txtkode_lokasi_edit . ' Sudah Berhasil Dirubah',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }
}
