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
SELECT
a.po,
b.ws,
b.buyer,
b.styleno,
b.product_group,
b.product_item,
concat((DATE_FORMAT(b.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(b.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(b.tgl_shipment,  '%Y')) tgl_shipment_fix,
tot_karton,
tot_karton_isi,
tot_karton_kosong,
coalesce(s.tot_scan,0) tot_scan
from
  (
select a.po,
max(a.no_carton)tot_karton,
count(IF(b.no_carton is not null,1,null)) tot_karton_isi,
count(IF(b.no_carton is null,1,null)) tot_karton_kosong
from  packing_master_carton a
left join (
select no_carton, po from packing_packing_out_scan group by no_carton,po  ) b on
a.po = b.po and  a.no_carton = b.no_carton
group by a.po
) a
left join
(
select
p.po,
m.ws,
m.styleno,
tgl_shipment,
m.buyer,
m.product_group,
m.product_item
from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
group by po
) b on a.po = b.po
left join
(select po,count(barcode) tot_scan from packing_packing_out_scan group by po) s on a.po = s.po
where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
 order by tgl_shipment asc, po asc
          ");

            //   SELECT
            //   a.po,
            //   b.ws,
            //   b.buyer,
            //   b.styleno,
            //   b.product_group,
            //   b.product_item,
            //   concat((DATE_FORMAT(b.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(b.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(b.tgl_shipment,  '%Y')) tgl_shipment_fix,
            //   tot_carton,
            //   coalesce(s.tot_scan,0) tot_scan
            //   from
            //     (
            //      SELECT po,count(no_carton) tot_carton
            //      FROM `packing_master_carton`
            //      group by po) a
            //   left join (
            //   select
            //   p.po,
            //   m.ws,
            //   m.styleno,
            //   tgl_shipment,
            //   m.buyer,
            //   m.product_group,
            //   m.product_item
            //   from ppic_master_so p
            //   inner join master_sb_ws m on p.id_so_det = m.id_so_det
            //   ) b on a.po = b.po
            //   left join
            //   (select po,count(barcode) tot_scan from packing_packing_out_scan group by po) s on a.po = s.po
            //    where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
            //    group by po
            //   order by tgl_shipment asc, po asc


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

    public function show_detail_karton(Request $request)
    {
        $po = $request->po;

        $data_det_karton = DB::select("SELECT
mc.no_carton,
mc.po,
m.buyer,
dc.barcode,
m.ws,
m.color,
m.size,
p.dest,
p.desc,
m.styleno,
m.product_group,
m.product_item,
coalesce(dc.tot,'0') tot,
if (mc.po = dc.po,'isi','kosong')stat
from
(select * from packing_master_carton a where po = '$po')mc
left join
(
select count(barcode) tot, po, barcode, no_carton  from packing_packing_out_scan
where po = '$po'
group by po, no_carton, barcode, po
) dc on mc.po = dc.po and mc.no_carton = dc.no_carton
left join ppic_master_so p on dc.po = p.po and dc.barcode = p.barcode
left join master_sb_ws m on p.id_so_det = m.id_so_det
                    ");
        return DataTables::of($data_det_karton)->toJson();
    }
}
