<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class GAApprovalBahanBakarController extends Controller
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
            concat(realisasi_jml, ' L') realisasi_jml_fix
            FROM ga_trans_pengajuan_bhn_bakar a
            inner join ga_master_kendaraan b on a.plat_no = b.plat_no
            inner join ga_master_bahan_bakar c on a.id_bhn_bakar = c.id
            where a.status = 'PENDING APPROVE'
            order by tgl_trans asc, no_trans asc
            ");

            return DataTables::of($data_input)->toJson();
        }


        return view(
            'ga.approval_bahan_bakar',
            [
                'page' => 'dashboard-ga', "subPageGroup" => "ga-approval",
                "subPage" => "ga-approval-bahan-bakar"
            ]
        );
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user               = Auth::user()->name;

        $JmlArray           = $_POST['cek_data'];

        if ($JmlArray != '') {

            foreach ($JmlArray as $key => $value) {
                if ($value != '') {
                    $txtid         = $JmlArray[$key]; {
                        $insert_det =  DB::update("
                    update ga_trans_pengajuan_bhn_bakar
                    set status = 'APPROVE',
                    user_app = '$user',
                    tgl_app = '$timestamp'
                    where id = '$txtid'");
                    }
                }
            }

            return array(
                "status" => 200,
                "message" => 'Data Sudah Terapprove',
                "additional" => [],
                "redirect" => 'reload'
            );
        } else {
            return array(
                "status" => 400,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }
}
