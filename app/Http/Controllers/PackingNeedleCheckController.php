<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPackingNeedleCheck;

class PackingNeedleCheckController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
select
tot,
o.po,
o.barcode,
m.color,
m.size,
m.ws,
o.dest,
concat((DATE_FORMAT(o.tgl_trans,  '%d')), '-', left(DATE_FORMAT(o.tgl_trans,  '%M'),3),'-',DATE_FORMAT(o.tgl_trans,  '%Y')
 ) tgl_trans_fix,
 o.created_by,
 o.created_at
 from
(
select tgl_trans,count(barcode) tot, barcode, po, dest, created_by, max(created_at)created_at
from packing_packing_needle_check
where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
group by tgl_trans, po, barcode, dest
) o
inner join ppic_master_so p on o.po = p.po and o.barcode = p.barcode and o.dest = p.dest
inner join master_sb_ws m on p.id_so_det = m.id_so_det
order by o.created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view(
            'packing.packing_needle_check',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-out",
                "subPage" => "packing-needle-check"
            ]
        );
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-90 days'));

        $data_po = DB::select("SELECT p.id isi, concat(p.po, ' - ', p.dest) tampil
from
(
select id, po, dest from ppic_master_so
where barcode is not null and barcode != '' and barcode != '-' and tgl_shipment >= '$tgl_skrg_min_sebulan'
group by po	, dest
) p
group by p.po, p.dest");


        return view('packing.create_packing_needle_check', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-packing-out",
            "subPage" => "packing-needle-check",
            "data_po" => $data_po,
            "user" => $user
        ]);
    }

    public function store_packing_needle(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $barcode    = $request->barcode;
        $po         = $request->cbopo_det;
        $dest       = $request->dest;

        $tgl_trans = date('Y-m-d');

        $cek_data = DB::select("
        select count(barcode) cek from ppic_master_so p
        where barcode = '$barcode' and po = '$po' and dest = '$dest'
        ");

        $cek_data_fix = $cek_data[0]->cek;

        if ($cek_data_fix >= '1') {
            $insert = DB::insert("
                insert into packing_packing_needle_check
                (tgl_trans,barcode,po,dest,created_by,created_at,updated_at)
                values
                (
                    '$tgl_trans',
                    '$barcode',
                    '$po',
                    '$dest',
                    '$user',
                    '$timestamp',
                    '$timestamp'
                )
                ");
            return array(
                'icon' => 'benar',
                'msg' => 'Data berhasil Disimpan',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak Ada Data',
            );
        }
    }

    public function packing_needle_check_show_summary(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $po         = $request->cbopo_det;
        $dest       = $request->dest;

        if ($request->ajax()) {


            $data_summary = DB::select("
            select p.barcode, p.po, m.color, m.size,m.dest,coalesce(s.tot_scan,0)tot_scan
            from ppic_master_so p
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            left join
            (
                select count(barcode)tot_scan, barcode, po, dest
                from packing_packing_needle_check
                where po = '$po' and dest = '$dest'
                group by po, barcode
            ) s on s.barcode = p.barcode and s.po = p.po and s.dest = p.dest
            where p.po = '$po' and p.barcode is not null and p.barcode != '-' and coalesce(s.tot_scan,0) != '0'
            group by p.barcode, po, m.color, m.size
            order by p.po asc, color asc, msn.urutan asc
            ");

            return DataTables::of($data_summary)->toJson();
        }
    }


    public function getpo(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-120 days'));
        $cek_po = DB::select("
        select * from ppic_master_so where id = '" . $request->cbopo . "'
        ");

        // return json_encode($cek_po[0]);
        return json_encode($cek_po ? $cek_po[0] : '-');
    }

    public function packing_needle_check_show_history(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $po         = $request->cbopo_det;
        $dest       = $request->dest;
        $tgl_trans = date('Y-m-d');

        if ($request->ajax()) {

            $data_history = DB::select("
select
o.id,
tgl_trans,
if (o.tgl_trans = '" . $tgl_trans . "','ok','no') cek_stat,
DATE_FORMAT(o.created_at, '%d-%m-%Y %H:%i:%s') created_at,
o.po,
o.barcode,
m.color,
m.dest,
m.size
from packing_packing_needle_check o
inner join ppic_master_so p on o.barcode = p.barcode and o.po = p.po and o.po = p.po and o.dest = p.dest
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where o.po = '$po' and o.dest = '$dest'
order by o.created_at desc
            ");
            return DataTables::of($data_history)->toJson();
        }
    }

    public function packing_needle_check_show_tot_input(Request $request)
    {
        $user       = Auth::user()->name;
        $tgl_trans = date('Y-m-d');
        $data_header = DB::select("
        SELECT count(barcode)tot_input
        from packing_packing_needle_check
        where created_by = '$user' and tgl_trans = '$tgl_trans'
        ");

        return json_encode($data_header ? $data_header[0] : '-');
    }


    public function packing_needle_check_hapus_history(Request $request)
    {
        $id_history = $request->id_history;

        $ins_history =  DB::insert("
insert into packing_packing_needle_check_log (id_packing_packing_needle_check, tgl_trans, barcode, po, dest, created_at, updated_at, created_by)
SELECT id, tgl_trans, barcode, po, dest,created_at, updated_at, created_by  FROM `packing_packing_needle_check` where id = '$id_history'");

        $del_history =  DB::delete("
        delete from packing_packing_needle_check where id = '$id_history'");
    }

    public function export_excel_packing_needle_check(Request $request)
    {
        return Excel::download(new ExportLaporanPackingNeedleCheck($request->from, $request->to), 'Laporan_Hasil_Scan.xlsx');
    }
}
