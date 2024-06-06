<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class PackingMasterKartonController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_carton = DB::select("
            select
            a.po,
            concat((DATE_FORMAT(b.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(b.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(b.tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            tot_carton from
                (
                SELECT po,count(no_carton) tot_carton
                FROM `packing_master_carton`
                group by po
                ) a
            left join (
                    select po, tgl_shipment from ppic_master_so
                    group by po
                ) b on a.po = b.po
            where b.tgl_shipment >= '$tgl_awal' and b.tgl_shipment <= '$tgl_akhir'
            order by tgl_shipment asc, po asc
            ");

            return DataTables::of($data_carton)->toJson();
        }

        $data_po = DB::select("SELECT po isi, po tampil from ppic_master_so group by po order by po asc");


        return view(
            'packing.packing_master_karton',
            [
                'page' => 'dashboard-packing', "subPageGroup" => "packing-master-karton",
                "subPage" => "master-karton",
                "data_po" => $data_po,
                "user" => $user,
            ]
        );
    }

    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $po = $request->cbopo;
        $tot_skrg = $request->tot_skrg;
        $tot_skrg_hit = $tot_skrg + 1;
        $tot_input = $request->txtinput_carton;
        $total = $tot_skrg + $tot_input;

        for ($i = $tot_skrg_hit; $i <= $total; $i++) {
            $insert = DB::insert(
                "insert into packing_master_carton
                    (po,no_carton,created_at,updated_at,created_by) values
                    ('$po','$i','$timestamp','$timestamp','$user')
                    "
            );
        }

        if ($insert) {
            return array(
                "status" => 200,
                "message" => 'Data Berhasil Di Upload',
                "additional" => [],
            );
        }
        // }
    }

    public function show_tot(Request $request)
    {
        $data_header = DB::select("
        SELECT coalesce(max(no_carton),0)tot_skrg
        FROM `packing_master_carton` where po = '$request->cbopo'
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }
}
