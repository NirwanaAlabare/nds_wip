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
use Storage;

class MutasiMesinMasterController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        if ($request->ajax()) {

            $data_mesin = DB::select("
            select m.*, coalesce(jml,0) jml, if(m.gambar is null or m.gambar = '' or m.gambar = '-','N','Y')stat_foto from master_mesin m
            left join
            (
            select id_qr, count(id_qr) jml from mut_mesin_input group by id_qr
            ) mut on m.id_qr = mut.id_qr
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

    public function hapus_data_mesin(Request $request)
    {
        $id = $request->id;

        $del =  DB::delete("
        delete from master_mesin where id = '$id'");
    }


    public function getdata_mesin(Request $request)
    {
        $cek_data = DB::select("
        SELECT * FROM master_mesin	where id_qr = '$request->id_qr_edit'
        ");
        return json_encode($cek_data ? $cek_data[0] : null);
    }

    public function edit_master_mut_mesin(Request $request)
    {
        $user = Auth::user()->name;
        $tglpindah = date('Y-m-d');
        $timestamp = Carbon::now();
        $txtedit_qr = $request->txtedit_qr;
        $txtedit_jenis = $request->txtedit_jenis;
        $txtedit_brand = $request->txtedit_brand;
        $txtedit_tipe = $request->txtedit_tipe;
        $txtedit_serial = $request->txtedit_serial;
        $img = $request->uploadphoto;

        // $folderPath = "public/";
        // $image_parts = explode(";base64,", $img);
        // $image_type_aux = explode("image/", $image_parts[0]);
        // $image_type = $image_type_aux[1];
        // $image_base64 = base64_decode($image_parts[1]);
        // $fileName = $txtedit_qr . '.png';
        // $file = $folderPath . $fileName;
        // Storage::put($file, $image_base64);

        if ($request->hasFile('uploadphoto')) {
            $folderPath = "public/";
            // $img_ext = $request->file('uploadphoto')->getClientOriginalExtension();
            $filename = $txtedit_qr .  '.' . 'jpeg';
            // $path = $request->file('uploadphoto')->move(public_path() . '/storage/gambar_mesin', $filename);
            $path = $request->file('uploadphoto')->storeAs('public/gambar_mesin', $filename);
        } else {
            if ($request->nm_gambar == '-') {
                $filename = '-';
            } else {
                $filename = $request->nm_gambar;
            }
        }




        // $filename = $txtedit_qr . '.' . $img_ext;
        // $file = $folderPath . $filename;
        // Storage::put($file, $filename);
        // $path = $request->file('uploadphoto')->move(public_path() / storage, $filename);

        DB::update(
            "update master_mesin
            set jenis_mesin = '$txtedit_jenis', brand = '$txtedit_brand', tipe_mesin = '$txtedit_tipe',
            serial_no = '$txtedit_serial', updated_at = '$timestamp', gambar = '$filename'
            where id_qr = '$txtedit_qr'
            "
        );
        return array(
            'status' => 300,
            'message' => 'Data ' . $txtedit_qr . ' Sudah Berhasil Dirubah',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }

    public function lap_stok_mesin(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        if ($request->ajax()) {

            $data_stok = DB::select("SELECT
        m.jenis_mesin, m.brand, count(m.id_qr) total, 'UNIT' satuan
        from master_mesin m
        left join
        (
        select * from mut_mesin_input group by id_qr
        ) mut on m.id_qr = mut.id_qr
        where mut.id_qr is not null
        group by  jenis_mesin, brand
        order by jenis_mesin asc, brand asc
            ");


            return DataTables::of($data_stok)->toJson();
        }
        return view('mut-mesin.lap_stok_mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'lap-mut-mesin', 'subPage' => 'lap_stok_mesin'], ['tgl_skrg' => $tgl_skrg]);
    }

    public function export_excel_master_mesin(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiMesinMaster(), 'Laporan_Mutasi_Master_Mesin.xlsx');
    }



    public function master_mesin_lokasi(Request $request)
    {
        $tgl_skrg = date('Y-m-d');

        $timestamp = Carbon::now();

        if ($request->ajax()) {

            $data_lokasi = DB::select("
select * from master_mesin_lokasi order by lokasi asc
            ");


            return DataTables::of($data_lokasi)->toJson();
        }
        return view('mut-mesin.master_lokasi_mesin', ['page' => 'dashboard-mut-mesin', 'subPageGroup' => 'master-mut-mesin', 'subPage' => 'master_mesin_lokasi'], ['tgl_skrg' => $tgl_skrg]);
    }


    public function store_master_lokasi_mesin(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $txtlok = $request->txtlok;

        DB::insert(
            "INSERT into master_mesin_lokasi
            (lokasi,created_by,created_at,updated_at)
            VALUES ('" . $txtlok . "','$user','$timestamp','$timestamp')"
        );

        return array(
            'status' => 300,
            'message' => 'Data ' . $txtlok . ' Sudah Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }
}
