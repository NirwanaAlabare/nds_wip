<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PPICMasterSo;
use App\Models\OutputPacking;

class PackingTransferGarmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                select * from fg_stok_master_lok
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('packing.packing_transfer_garment', ['page' => 'dashboard-packing', "subPageGroup" => "packing-transfer-garment", "subPage" => "transfer-garment"]);
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_line = DB::connection('mysql_sb')->select("SELECT username isi, username tampil from userpassword where groupp = 'sewing' order by isi asc");

        return view('packing.create_packing_transfer_garment', [
            'page' => 'dashboard-packing', "subPageGroup" => "packing-transfer-garment",
            "subPage" => "transfer-garment",
            "data_line" => $data_line,
            "user" => $user
        ]);
    }

    public function gettipe_garment(Request $request)
    {
        // $data_ws = DB::connection('mysql_sb')->select("
        //     select so_det_id isi,
        //         concat(ac.kpno,' - ', ac.styleno,' - ', sd.color,' - ', sd.size, ' - > ',count(so_det_id)) tampil
        //     from output_rfts_packing a
        //         inner join master_plan mp on a.master_plan_id = mp.id
        //         inner join act_costing ac on mp.id_ws = ac.id
        //         inner join so_det sd on a.so_det_id = sd.id
        //         left join master_size_new msn on sd.size = msn.size
        //     where sewing_line = '" . $request->cbo_line . "'
        //     group by so_det_id
        //     having count(so_det_id) != '0'
        //     order by ac.kpno asc, sd.color asc, styleno asc, msn.urutan asc
        // ");

        $data_ws = PPICMasterSo::all();

        $html = "<option value=''>Pilih Garment</option>";

        foreach ($data_ws as $dataws) {
            if ($dataws->outputPacking) {
                $res = $dataws->outputPacking->ppicOutput($request->cbo_line)->get();

                foreach ($res as $r) {
                    $html .= " <option value='" . $r->isi . "'>" . $r->tampil . "</option> ";
                }
            }
        }

        return $html;
    }


    public function master_sumber_penerimaan(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                select * from fg_stok_master_sumber_penerimaan
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.master_sumber_penerimaan_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-masterlokasi", "subPage" => "master-sumber-penerimaan"]);
    }

    public function store_master_sumber_penerimaan(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $sumber = strtoupper($request->txtsumber);

        DB::insert(
            "insert into fg_stok_master_sumber_penerimaan
            (sumber,cancel,created_by,created_at,updated_at)
            VALUES ('$sumber','N','$user','$timestamp','$timestamp')
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data Sudah ' . $request->txtsumber . ' Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
    }


    public function master_tujuan_pengeluaran(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                select * from fg_stok_master_tujuan
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.master_tujuan_pengeluaran_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-masterlokasi", "subPage" => "master-tujuan-pengeluaran"]);
    }

    public function store_master_tujuan_pengeluaran(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tujuan = strtoupper($request->txttujuan);

        DB::insert(
            "insert into fg_stok_master_tujuan
            (tujuan,cancel,created_by,created_at,updated_at)
            VALUES ('$tujuan','N','$user','$timestamp','$timestamp')
            "
        );

        return array(
            'status' => 300,
            'message' => 'Data Sudah ' . $request->txttujuan . ' Berhasil Ditambahkan',
            'redirect' => '',
            'table' => 'datatable',
            'additional' => [],
        );
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
