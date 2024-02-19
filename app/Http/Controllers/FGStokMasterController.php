<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class FGStokMasterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = '';

            // if ($request->dateFrom) {
            //     $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            // }

            // if ($request->dateTo) {
            //     $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
            // }

            // $keywordQuery = '';
            // if ($request->search['value']) {
            //     $keywordQuery =
            //         "
            //          (
            //             line like '%" .
            //         $request->search['value'] .
            //         "%'
            //         )
            //     ";
            // }

            $data_input = DB::select("
                select * from fg_stok_master_lok
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.master_lokasi_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-masterlokasi", "subPage" => "fg-stock"]);
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $lok = strtoupper($request->txtlok);

        DB::insert(
            "insert into fg_stok_master_lok
            (kode_lok_fg_stok,lokasi,tingkat,baris,cancel,created_by,created_at,updated_at)
            VALUES ('" . $request->txtkode_lok . "','$lok','" . $request->txttingkat . "','" . $request->txtbaris . "'
            ,'N','$user','$timestamp','$timestamp')
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data Sudah ' . $request->txtkode_lok . ' Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }

    public function destroy(Request $request)
    {
        $user = Auth::user()->name;

        DB::delete(
            "DELETE FROM tmp_dc_in_input_new where user = '$user'"
        );
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
