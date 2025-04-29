<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPackingOut;

class PackingPackingOutController extends Controller
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
no_carton,
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
select tgl_trans,count(barcode) tot, barcode, po, dest, no_carton, created_by, max(created_at)created_at
from packing_packing_out_scan
where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
group by tgl_trans, po, barcode, dest, no_carton
) o
inner join ppic_master_so p on o.po = p.po and o.barcode = p.barcode
inner join master_sb_ws m on p.id_so_det = m.id_so_det
order by o.created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view(
            'packing.packing_out',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-out",
                "subPage" => "packing-out"
            ]
        );
    }

    public function getno_carton(Request $request)
    {
        $cek_po = DB::select("
        select po, dest from ppic_master_so where id = '" . $request->cbopo . "'
        ");

        $po = $cek_po ? $cek_po[0]->po : null;
        $dest = $cek_po ? $cek_po[0]->dest : null;


        $data_carton = DB::select("SELECT
        a.no_carton isi, a.no_carton tampil
        from
        (
        select po, no_carton, dest, barcode, qty qty_pl
        from packing_master_packing_list where po = '$po' and dest = '$dest'
        ) a
        left join
        (
        select po, no_carton, dest, barcode, count(barcode) qty_scan
        from packing_packing_out_scan where po = '$po' and dest = '$dest'
        group by po, no_carton, dest, barcode
        ) b on a.po = b.po and a.no_carton = b.no_carton and a.dest = b.dest and a.barcode = b.barcode
		where a.qty_pl -  coalesce(qty_scan,0) != '0'
        group by a.no_carton
        ");

        $html = "<option value=''>Pilih No Carton</option>";

        foreach ($data_carton as $datacarton) {
            $html .= " <option value='" . $datacarton->isi . "'>" . $datacarton->tampil . "</option> ";
        }

        return $html;
    }


    public function getpo(Request $request)
    {
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-120 days'));
        // $cek_po = DB::select("
        // select * from ppic_master_so where id = '" . $request->cbopo . "' and tgl_shipment >= '$tgl_skrg_min_sebulan'
        // ");
        $cek_po = DB::select("
        select * from ppic_master_so where id = '" . $request->cbopo . "'
        ");

        // return json_encode($cek_po[0]);
        return json_encode($cek_po ? $cek_po[0] : '-');
    }



    public function packing_out_show_summary(Request $request)
    {
        $user = Auth::user()->name;

        $po = $request->cbopo ? $request->cbopo : null;
        $cbono_carton = $request->cbono_carton ? $request->cbono_carton : null;
        $dest = $request->txtdest ? $request->txtdest : null;

        // $cekArray = explode('_', $cbono_carton);
        // $no_carton = $cekArray[0];
        // $notes = $cekArray[1];

        if ($request->ajax()) {


            $data_summary = DB::select("
select a.*, coalesce(tot_scan,0) tot_scan from
(
select no_carton, a.po, a.dest, id_ppic_master_so, a.id_so_det, m.size, m.color, a.barcode, a.qty
from packing_master_packing_list a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on a.id_so_det = m.id_so_det
where a.po = '$po' and a.dest = '$dest' and no_carton = '$cbono_carton'
) a
left join
(
                select count(barcode)tot_scan, barcode, po, no_carton,dest
                from packing_packing_out_scan
                where po = '$po'  and dest = '$dest' and no_carton = '$cbono_carton '
                group by barcode, no_carton
) b on a.po = b.po and a.dest = b.dest and a.no_carton = b.no_carton and a.barcode = b.barcode
left join master_size_new msn on a.size = msn.size
order by color asc, urutan asc
            ");

            return DataTables::of($data_summary)->toJson();
        }
    }

    public function packing_out_show_history(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_trans = date('Y-m-d');

        $cbono_carton = $request->cbono_carton ? $request->cbono_carton : null;

        // if ($cbono_carton == null) {
        //     $no_carton = '-';
        //     $notes = '-';
        // } else {
        //     $cekArray = explode('_', $cbono_carton);
        //     $no_carton = $cekArray[0];
        //     $notes = $cekArray[1];
        // }



        if ($request->ajax()) {

            $data_history = DB::select("
select
o.id,
tgl_trans,
if (o.tgl_trans = '" . $tgl_trans . "'  and c.po is null,'ok','no') cek_stat,
DATE_FORMAT(o.created_at, '%d-%m-%Y %H:%i:%s') created_at,
o.po,
o.barcode,
m.color,
m.size
from packing_packing_out_scan o
inner join ppic_master_so p on o.barcode = p.barcode and o.po = p.po and o.po = p.po and o.dest = p.dest
inner join master_sb_ws m on p.id_so_det = m.id_so_det
left join
(
select po, barcode, dest, no_carton, sum(qty)tot_fg from fg_fg_in where po = '" . $request->cbopo . "'
and dest = '" . $request->txtdest . "' and no_carton = '" . $request->cbono_carton . "' and status = 'NORMAL') c
on o.barcode = c.barcode and o.po = c.po and o.po = c.po and o.dest = c.dest
where o.no_carton = '" . $request->cbono_carton . "' and o.po = '" . $request->cbopo . "' and o.dest = '" . $request->txtdest . "'
order by o.created_at desc
            ");
            return DataTables::of($data_history)->toJson();
        }
    }

    public function packing_out_hapus_history(Request $request)
    {
        $id_history = $request->id_history;

        $ins_history =  DB::insert("
insert into packing_packing_out_scan_log (id_packing_Packing_out_scan, tgl_trans, barcode, po, no_carton, created_at, updated_at, created_by)
SELECT id, tgl_trans, barcode, po, no_carton,created_at, updated_at, created_by  FROM `packing_packing_out_scan` where id = '$id_history'");

        $del_history =  DB::delete("
        delete from packing_packing_out_scan where id = '$id_history'");
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $tgl_skrg_4_bln = date('Y-m-d', strtotime('-90 days'));

        $data_po = DB::select("SELECT
a.id_ppic_master_so isi,
concat(a.po, ' - ', a.dest, ' ( ', count(distinct(a.no_carton)), ' ) ') tampil
from packing_master_packing_list a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
where p.tgl_shipment >= '$tgl_skrg_4_bln'
group by a.po, a.dest
");


        // $data_po = DB::select("SELECT p.po isi, concat(p.po, ' - ( ', coalesce(max(m.no_carton),0) , ' ) ') tampil
        // from ppic_master_so p
        // left join packing_master_carton m on p.po = m.po
        // where barcode is not null and barcode != '' and barcode != '-'
        // group by p.po");



        return view('packing.create_packing_out', [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-packing-out",
            "subPage" => "packing-out",
            "data_po" => $data_po,
            "user" => $user
        ]);
    }


    public function store(Request $request)
    {
        $timestamp  = Carbon::now();
        $user       = Auth::user()->name;
        $barcode    = $request->barcode;
        $cbopo    = $request->cbopo;
        $no_carton    = $request->cbono_carton;
        $dest    = $request->txtdest;
        // $no_carton_cek    = $request->cbono_carton;
        // $cekArray = explode('_', $no_carton_cek);
        // $no_carton = $cekArray[0];
        // $notes = $cekArray[1];
        $tgl_trans = date('Y-m-d');

        $cek_po = DB::select("
        select * from ppic_master_so where id = '$cbopo'
        ");

        $cek_dest_po = $cek_po[0]->po;

        $cek_data = DB::select("
        select count(barcode) cek from ppic_master_so p
        where barcode = '$barcode' and po = '$cek_dest_po' and dest = '$dest'
        ");

        $cek_data_fix = $cek_data[0]->cek;
        // dd("select count(barcode) cek from ppic_master_so p
        // where barcode = '$barcode' and po = '$cek_dest_po' and dest = '$dest'");

        if ($cek_data_fix >= '1') {

            $cek_stok = DB::select("
            select coalesce(pack_in.tot_in,0)  - coalesce(pack_out.tot_out,0) tot_s
            from ppic_master_so p
            left join
            (
                select sum(qty) tot_in, id_ppic_master_so from packing_packing_in
                where barcode = '$barcode' and po = '$cek_dest_po' and dest = '$dest'
                group by id_ppic_master_so
            ) pack_in on p.id = pack_in.id_ppic_master_so
            left join
            (
                select count(p.barcode) tot_out, p.id
                from packing_packing_out_scan a
                inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
                where p.barcode = '$barcode' and p.po = '$cek_dest_po' and p.dest = '$dest'
                group by a.barcode, a.po
            ) pack_out on p.id = pack_out.id
            where p.barcode = '$barcode' and p.po = '$cek_dest_po' and dest = '$dest'
            ");
            $cek_stok_fix = $cek_stok[0]->tot_s;


            $cek_qty_isi_karton = DB::select("SELECT qty, coalesce(tot_input,0) tot_input from
(select po, no_carton, barcode, dest ,qty from packing_master_packing_list
where po = '$cek_dest_po' and no_carton = '$no_carton' and barcode = '$barcode' and dest = '$dest'
)a
left join
(
select po, no_carton, barcode, dest,count(barcode) tot_input
from packing_packing_out_scan
where po = '$cek_dest_po' and no_carton = '$no_carton' and barcode = '$barcode' and dest = '$dest'
) b on a.po = b.po and a.dest = b.dest and a.no_carton = b.no_carton and a.barcode = b.barcode");

    if ($cek_qty_isi_karton) {
        $cek_qty_isi = $cek_qty_isi_karton[0]->qty;
        $tot_out = $cek_qty_isi_karton[0]->tot_input;
        if ($cek_stok_fix >= '1') {

            if ($cek_qty_isi > $tot_out) {
                $insert = DB::insert("
                insert into packing_packing_out_scan
                (tgl_trans,barcode,po,dest,no_carton,notes,created_by,created_at,updated_at)
                values
                (
                    '$tgl_trans',
                    '$barcode',
                    '$cek_dest_po',
                    '$dest',
                    '$no_carton',
                    '-',
                    '$user',
                    '$timestamp',
                    '$timestamp'
                )
                ");
                return array(
                    'icon' => 'benar',
                    'msg' => 'Data berhasil Disimpan',
                );
            } else if ($cek_qty_isi == $tot_out) {
                return array(
                    'icon' => 'lebih',
                    'msg' => 'Data sudah melebihi qty karton',
                );
            } else {
                return array(
                    'icon' => 'salah',
                    'msg' => 'Tidak Ada Data 1',
                );
            }
        } else
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak Ada Stok',
            );
        } else
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak Ada Data 2',
            );
    } else {
        return array(
            'icon' => 'salah',
            'msg' => 'Datat tidak ada di packing list',
        );
    }

    }

    public function packing_out_show_tot_input(Request $request)
    {
        $user       = Auth::user()->name;
        $tgl_trans = date('Y-m-d');
        $data_header = DB::select("
        SELECT count(barcode)tot_input
        from packing_packing_out_scan
        where created_by = '$user' and tgl_trans = '$tgl_trans'
        ");

        return json_encode($data_header ? $data_header[0] : '-');
    }

    public function packing_out_tot_barcode(Request $request)
    {
        $user = Auth::user()->name;
        $po    = $request->cbopo;
        $dest    = $request->dest;
        if ($request->ajax()) {


            $data_summary = DB::select("
            SELECT
            a.id,
            a.id_so_det,
            m.buyer,
            concat((DATE_FORMAT(a.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(a.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(a.tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            a.barcode,
            m.reff_no,
            a.po,
            a.dest,
            a.desc,
            m.ws,
            m.styleno,
            m.color,
            m.size,
            a.qty_po,
            coalesce(trf.qty_trf,0) qty_trf,
            coalesce(pck.qty_packing_in,0) qty_packing_in,
            coalesce(pck_out.qty_packing_out,0) qty_packing_out,
            coalesce(pck.qty_packing_in,0) - coalesce(pck_out.qty_packing_out,0) sisa,
            m.ws,
            a.created_by,
            a.created_at
            FROM ppic_master_so a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            left join
            (
                select id_ppic_master_so, coalesce(sum(qty),0) qty_trf from packing_trf_garment group by id_ppic_master_so
            ) trf on trf.id_ppic_master_so = a.id
            left join
            (
                select id_ppic_master_so, coalesce(sum(qty),0) qty_packing_in from packing_packing_in group by id_ppic_master_so
            ) pck on pck.id_ppic_master_so = a.id
            left join
            (
            select p.id, qty_packing_out from
                (
                select count(barcode) qty_packing_out,po, barcode, dest from packing_packing_out_scan
                group by barcode, po, dest
                ) a
            inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
            group by p.id
            ) pck_out on pck_out.id = a.id
            where a.po = '$po' and a.dest = '$dest'
            order by tgl_shipment desc, buyer asc, ws asc , msn.urutan asc
            ");

            return DataTables::of($data_summary)->toJson();
        }
    }



    public function export_excel_packing_out(Request $request)
    {
        return Excel::download(new ExportLaporanPackingOut($request->from, $request->to), 'Laporan_Hasil_Scan.xlsx');
    }


    public function show_sum_max_carton(Request $request)
    {
        $po = $request->po_data ? $request->po_data : null;
        $no_carton_data_arr = $request->no_carton_data ? $request->no_carton_data : null;
        $cekArray = explode('_', $no_carton_data_arr);
        $no_carton = $cekArray[0];
        $notes = $cekArray[1];

        $data_kapasitas_karton = DB::select("SELECT a.*, coalesce(b.tot_out,0)tot_out from
(
select * from packing_master_carton
where po = '$po' and no_carton = '$no_carton' and notes = '$notes') a
left join
(
select count(barcode) tot_out, po, no_carton, notes from packing_packing_out_scan where po = '$po' and no_carton = '$no_carton ' and notes = '$notes'
group by po, no_carton, notes
) b on a.po = b.po and a.no_carton = b.no_carton and a.notes = b.notes
        ");

        return json_encode($data_kapasitas_karton ? $data_kapasitas_karton[0] : null);
    }
}
