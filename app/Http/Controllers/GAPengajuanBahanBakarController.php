<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PPICMasterSo;
use App\Models\OutputPacking;

class GAPengajuanBahanBakarController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
            SELECT a.*,
            concat((DATE_FORMAT(a.tgl_trans,  '%d')), '-', left(DATE_FORMAT(a.tgl_trans,  '%M'),3),'-',DATE_FORMAT(a.tgl_trans,  '%Y')
                        ) tgl_trans_fix,
            c.jns_bhn_bakar,
            c.nm_bhn_bakar,
            CONCAT('Rp. ', FORMAT(tot_biaya,2,'id_ID')) AS tot_biaya_fix,
            concat(jml, ' L') jml_fix,
            CONCAT('Rp. ', FORMAT(realisasi_biaya,2,'id_ID')) AS tot_biaya_realisasi_fix,
            concat(realisasi_jml, ' L') realisasi_jml_fix,
            DATE_FORMAT(realisasi_tgl, '%d-%M-%Y %H:%i:%s') realisasi_tgl_fix,
            if (a.status = 'APPROVE',concat(a.status, ' ', a.user_app, ' (',DATE_FORMAT(tgl_app, '%d-%M-%Y %H:%i:%s'), ')'),a.status) status_fix
            FROM ga_trans_pengajuan_bhn_bakar a
            inner join ga_master_kendaraan b on a.plat_no = b.plat_no
            inner join ga_master_bahan_bakar c on a.id_bhn_bakar = c.id
            where a.tgl_trans >= '$tgl_awal' and a.tgl_trans <= '$tgl_akhir'
            order by tgl_trans asc, no_trans asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_jns_bhn_bakar =
            DB::select("select 'BENSIN' isi, 'BENSIN' tampil
                    UNION
                    select 'SOLAR' isi, 'SOLAR' tampil
        ");

        $data_bahan_bakar = DB::select("select id isi, nm_bhn_bakar tampil from ga_master_bahan_bakar where cancel = 'N' order by nm_bhn_bakar asc");

        $data_status = DB::select("select 'INVENTARIS' isi, 'INVENTARIS' tampil
        union
        select 'OPERATIONAL' isi, 'OPERATIONAL' tampil");

        $data_driver =
            DB::connection('mysql_hris')->select("
            select nik isi,
            employee_name tampil
            from employee_atribut where sub_dept_name like '%DRIVER%' and status_aktif = 'AKTIF'
            order by employee_name asc
            ");

        $data_no_kendaraan =
            DB::select("
            select plat_no isi,
            plat_no tampil
            from ga_master_kendaraan
            ");



        return view(
            'ga.pengajuan_bahan_bakar',
            [
                'page' => 'dashboard-ga', "subPageGroup" => "ga-pengajuan",
                "subPage" => "ga-pengajuan-bahan-bakar",
                "data_jns_bhn_bakar" => $data_jns_bhn_bakar,
                "data_bahan_bakar" => $data_bahan_bakar,
                "data_status" => $data_status,
                "data_driver" => $data_driver,
                "data_no_kendaraan" => $data_no_kendaraan
            ]
        );
    }

    public function store_ga_master_bahan_bakar(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        $validatedRequest = $request->validate([
            "cbo_bhn_bakar" => "required",
            "txtnm_bhn_bakar" => "required",
            "txtharga" => "required",
        ]);


        // $jns = $request->txtjns_bhn_bakar;
        // $harga = $request->txtharga;

        DB::insert(
            "insert into ga_master_bahan_bakar
            (jns_bhn_bakar,nm_bhn_bakar,harga,cancel,created_by,created_at,updated_at)
            VALUES
            (
                '" . strtoupper($validatedRequest['cbo_bhn_bakar']) . "',
                '" . strtoupper($validatedRequest['txtnm_bhn_bakar']) . "',
                '" . str_replace(['Rp. ', '.'], '', $validatedRequest['txtharga']) . "',
                'N',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data ' . $validatedRequest['txtnm_bhn_bakar']  . ' Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable-master-bensin',
            'additional' => [],
        );
    }

    public function show_master_bahan_bakar(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_list = DB::select("
            select *,CONCAT('Rp. ', FORMAT(harga,2,'id_ID')) AS
            harga_barang from ga_master_bahan_bakar
            order by jns_bhn_bakar asc, nm_bhn_bakar asc
            ");

            return DataTables::of($data_list)->toJson();
        }
    }

    public function show_data_bahan_bakar(Request $request)
    {
        $data_bahan_bakar = DB::select("
        SELECT * FROM ga_master_bahan_bakar
         where id = '$request->id_c'");
        return json_encode($data_bahan_bakar[0]);
    }

    public function show_data_transaksi(Request $request)
    {
        $data_master_trans = DB::select("
        SELECT * FROM ga_trans_pengajuan_bhn_bakar a
        inner join ga_master_kendaraan b on a.plat_no = b.plat_no
        inner join ga_master_bahan_bakar c on a.id_bhn_bakar = c.id
         where a.id = '$request->id_c'");
        return json_encode($data_master_trans[0]);
    }

    public function update_ga_master_bahan_bakar(Request $request)
    {
        DB::update(
            "update ga_master_bahan_bakar set harga = '" . $request->txtedharga . "'
            where id = '" . $request->txtedid_bhn_bakar . "'
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data ' . $request->txtednm  . ' Berhasil Dirubah',
            'redirect' => '',
            'table' => 'datatable-master-bensin',
            'additional' => [],
        );
    }

    public function update_ga_trans(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        DB::update(
            "update ga_trans_pengajuan_bhn_bakar set realisasi_jml = '" . $request->txtjml_realisasi . "',
            realisasi_biaya = '" . $request->txtbayar_realisasi . "',
            realisasi_tgl = '$timestamp',
            realisasi_user = '$user',
            status = 'PENDING APPROVE'
            where id = '" . $request->edtxt_id . "'
            "
        );

        return array(
            'status' => 200,
            'message' => 'Data  Berhasil Diupdate',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }


    public function store_ga_master_kendaraan(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        $validatedRequest = $request->validate([
            "txtplat_no" => "required",
            "txtthn_pembuatan" => "required",
            "txtno_mesin" => "required",
            "txtno_rangka" => "required",
            "txtmerk" => "required",
            "txttipe" => "required",
            "txtwarna" => "required",
            "cbojns_bhn_bakar" => "required",
            "txtisi_silinder" => "required",
            "cbostat" => "required",
        ]);


        // $jns = $request->txtjns_bhn_bakar;
        // $harga = $request->txtharga;

        DB::insert(
            "insert into ga_master_kendaraan
            (plat_no,thn_pembuatan,no_mesin,no_rangka,merk,tipe,warna,jns_bhn_bakar,isi_silinder,status,cancel,created_by,created_at,updated_at)
            VALUES
            (
                '" . $validatedRequest['txtplat_no'] . "',
                '" . $validatedRequest['txtthn_pembuatan'] . "',
                '" . $validatedRequest['txtno_mesin'] . "',
                '" . $validatedRequest['txtno_rangka'] . "',
                '" . $validatedRequest['txtmerk'] . "',
                '" . $validatedRequest['txttipe'] . "',
                '" . $validatedRequest['txtwarna'] . "',
                '" . $validatedRequest['cbojns_bhn_bakar'] . "',
                '" . $validatedRequest['txtisi_silinder'] . "',
                '" . $validatedRequest['cbostat'] . "',
                'N',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data ' . $validatedRequest['txtplat_no']  . ' Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable-master-kendaraan',
            'additional' => [],
        );
    }

    public function show_master_kendaraan(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_list_kendaraan = DB::select("
            select *,
            concat(isi_silinder, ' ', 'CC') isi_silinder_fix
            from ga_master_kendaraan
            order by id asc
            ");

            return DataTables::of($data_list_kendaraan)->toJson();
        }
    }

    public function show_getnip(Request $request)
    {
        $data_nip = DB::connection('mysql_hris')->select("select nik, employee_name from employee_atribut
        where nik = '" . $request->cbo_nm_driver . "'");

        return json_encode($data_nip ? $data_nip[0] : '-');
    }


    public function show_getjns(Request $request)
    {
        $data_jns = DB::select("select concat(merk, ' ', warna) jns_kendaraan, jns_bhn_bakar from ga_master_kendaraan
        where plat_no = '" . $request->cbo_no_kendaraan . "'");

        return json_encode($data_jns ? $data_jns[0] : '-');
    }

    public function show_getbhn_bakar(Request $request)
    {
        $data_bhn_bakar = DB::select("
            select id isi, nm_bhn_bakar tampil from ga_master_bahan_bakar
            where jns_bhn_bakar = '" . $request->txtjns_bhn_bakar . "'
            order by nm_bhn_bakar asc");


        $html = "<option value=''>Pilih Bahan Bakar</option>";

        if ($request->txtjns_bhn_bakar != '') {
            foreach ($data_bhn_bakar as $databhnbakar) {
                $html .= " <option value='" . $databhnbakar->isi . "'>" . $databhnbakar->tampil . "</option> ";
            }
        } else {
            $html .= " <option value='' disabled></option> ";
        }



        return $html;
    }

    public function show_getharga(Request $request)
    {
        $data_harga = DB::select("select harga from ga_master_bahan_bakar
        where id = '" . $request->cbobhn_bakar . "'");

        return json_encode($data_harga ? $data_harga[0] : '-');
    }

    public function store_ga_trans(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tahun = date('y', strtotime($tgltrans));
        $tahun_f = date('Y', strtotime($tgltrans));
        $bln = date('m', strtotime($tgltrans));
        $cek_nomor = DB::select("
        select max(mid(no_trans,19,3))nomor from ga_trans_pengajuan_bhn_bakar
        where year(tgl_trans) = '" . $tahun_f . "' and month(tgl_trans) = '" . $bln . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%03s", $urutan);

        // $kode_trans = $kode . $no . '/' . $kodepay;
        $kode_trans = 'F.' . $tahun . '.' . 'GA-NAG.P-14/' . $kodepay . '-' . $bln . '-' . $tahun;

        $validatedRequest = $request->validate([
            "txttgl_trans" => "required",
            "txtnm_driver" => "required",
            "cbo_nm_driver" => "required",
            "txtnip" => "required",
            "cbo_no_kendaraan" => "required",
            "txtjns_kendaraan" => "required",
            "txtodoometer" => "required",
            "txtjns_bhn_bakar" => "required",
            "cbobhn_bakar" => "required",
            "txtjml" => "required",
            "txttot_bayar" => "required",
        ]);

        DB::insert(
            "insert into ga_trans_pengajuan_bhn_bakar
            (no_trans,tgl_trans,nm_driver,nip,plat_no,jns_kendaraan,oddometer,id_bhn_bakar,jml,tot_biaya,status,cancel,created_by,created_at,updated_at)
            VALUES
            (
                '$kode_trans',
                '" . $validatedRequest['txttgl_trans'] . "',
                '" . $validatedRequest['txtnm_driver'] . "',
                '" . $validatedRequest['txtnip'] . "',
                '" . $validatedRequest['cbo_no_kendaraan'] . "',
                '" . $validatedRequest['txtjns_kendaraan'] . "',
                '" . $validatedRequest['txtodoometer'] . "',
                '" . $validatedRequest['cbobhn_bakar'] . "',
                '" . $validatedRequest['txtjml'] . "',
                '" . $validatedRequest['txttot_bayar'] . "',
                'WAITING',
                'N',
                '$user',
                '$timestamp',
                '$timestamp'
            )
            "
        );

        return array(
            "status" => 900,
            'message' => 'No Transaksi ' . $kode_trans . ' Sudah Terbuat',
            "additional" => [],
        );
    }

    public function export_pdf_pengajuan_bhn_bakar(Request $request)
    {
        $data = DB::select("
        SELECT a.*,
        concat((DATE_FORMAT(a.tgl_trans,  '%d')), '-', left(DATE_FORMAT(a.tgl_trans,  '%M'),3),'-',DATE_FORMAT(a.tgl_trans,  '%Y')
                    ) tgl_trans_fix,
        c.jns_bhn_bakar,
        c.nm_bhn_bakar,
        CONCAT('Rp. ', FORMAT(tot_biaya,2,'id_ID')) AS tot_biaya_fix,
        concat(jml, ' L') jml_fix
        FROM ga_trans_pengajuan_bhn_bakar a
        inner join ga_master_kendaraan b on a.plat_no = b.plat_no
        inner join ga_master_bahan_bakar c on a.id_bhn_bakar = c.id
        where a.id = '$request->id'
        order by tgl_trans asc, no_trans asc
        ");

        $no_trans = $data[0]->no_trans;
        $nm = $data[0]->nm_driver;
        $tgl_trans = $data[0]->tgl_trans;
        $nip = $data[0]->nip;
        $plat_no = $data[0]->plat_no;
        $jns_kendaraan = $data[0]->jns_kendaraan;
        $oddometer = $data[0]->oddometer;
        $nm_bhn_bakar = $data[0]->nm_bhn_bakar;
        $jml = $data[0]->jml;
        $tot_biaya_fix = $data[0]->tot_biaya_fix;
        $user = $data[0]->created_by;

        Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        $customPaper = array(0, 0,  269.2913, 396.8503);
        $pdf = Pdf::loadView('ga.export_pdf_pengajuan_bhn_bakar', [
            'no_trans' => $no_trans,
            'tgl_trans' => $tgl_trans,
            'nm' => $nm,
            'nip' => $nip,
            'plat_no' => $plat_no,
            'jns_kendaraan' => $jns_kendaraan,
            'oddometer' => $oddometer,
            'nm_bhn_bakar' => $nm_bhn_bakar,
            'jml' => $jml,
            'tot_biaya_fix' => $tot_biaya_fix,
            'user' => $user,
            'id' => $request->id
        ])->setPaper($customPaper, 'portrait');
        return $pdf->download('spl.pdf');
        // return Excel::download(new ExportSpl($data,$request->id), 'Laporan_SPL.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    }
}
