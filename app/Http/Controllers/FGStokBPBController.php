<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class FGStokBPBController extends Controller
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
                select * from fg_stok_bpb
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.bpb_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock"]);
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

    public function create(Request $request)
    {
        $data_lok = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok");

        $data_buyer = DB::connection('mysql_sb')->select("select id_buyer isi, ms.supplier tampil from act_costing ac
        inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
        group by id_buyer
        order by supplier asc");

        $data_grade = DB::select("select grade isi , grade tampil from fg_stok_master_grade");

        return view('fg-stock.create_bpb_fg_stock', [
            'page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-bpb", "subPage" => "bpb-fg-stock",
            "data_lok" => $data_lok, "data_buyer" => $data_buyer, "data_grade" => $data_grade
        ]);
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
