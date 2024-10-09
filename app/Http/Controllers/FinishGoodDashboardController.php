<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class FinishGoodDashboardController extends Controller
{
    public function dashboard_finish_good(Request $request)
    {
        $tgl_filter = $request->dateFilter;
        $tgl_shipment = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT
a.created_by line,
m.ws,
m.color,
m.size,
m.dest,
sum(qty_p_line) tot_qty_p_line,
list_po
from
(
select created_by,so_det_id,count(so_det_id) qty_p_line from output_rfts_packing
where created_at >= '$tgl_filter'
group by created_by, so_det_id
) a
left join master_sb_ws m on a.so_det_id = m.id_so_det
left join master_size_new msn on m.size = msn.size
left join (select id_so_det, group_concat(distinct(po)) list_po from ppic_master_so where tgl_shipment >= '$tgl_shipment' group by id_so_det) b on a.so_det_id = b.id_so_det
group by a.created_by, m.ws, m.color, m.size, m.dest
order by a.created_by asc, ws asc, color asc, dest asc, urutan asc
            ");

            return DataTables::of($data_input)->toJson();
        }
        $data_buyer = DB::select("SELECT buyer isi, buyer tampil from
(
select * from fg_fg_in where status = 'NORMAL'
) a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
inner join master_sb_ws m on a.id_so_det = m.id_so_det
where b.id_fg_in is null
group by buyer
order by buyer asc");

        return view('finish_good.dashboard_finish_good', [
            'page' => 'dashboard_finish_good',
            "data_buyer" => $data_buyer
        ]);
    }


    public function get_data_dashboard_fg_ekspedisi(Request $request)
    {
        $data_dash = DB::select("SELECT kode_lok x, coalesce(tot_karton,0)y from fg_fg_master_lok a
left join
(
select lokasi,count(no_carton) tot_karton from
(
select * from (select * from fg_fg_in where status  = 'NORMAL') a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
where b.id_fg_in is null
group by a.no_carton, a.po
)
b
group by lokasi
) b on a.kode_lok = b.lokasi");
        return json_encode($data_dash);
    }


    public function show_tot_dash_fg_ekspedisi(Request $request)
    {
        $data_header = DB::select("SELECT
sum(tot_karton_non) tot_karton_non,
sum(tot_karton_lok) tot_karton_lok,
sum(tot_karton) tot_karton
FROM
(
SELECT COUNT(*) OVER () as tot_karton_non, '0' tot_karton_lok,  '0' tot_karton
FROM fg_fg_in a
left join fg_fg_out b on a.id = b.id_fg_in
WHERE a.lokasi = '-' and a.status = 'NORMAL' and b.id_fg_in is null
group by a.po, a.no_carton
UNION
SELECT '0' tot_karton_non,'0' tot_karton_lok,count(*) over ()  tot_karton FROM fg_fg_in a
left join fg_fg_out b on a.id = b.id_fg_in
where a.status = 'NORMAL' and b.id_fg_in is null
group by a.po, a.no_carton
UNION
SELECT '0' tot_karton_non, count(*) over ()  tot_karton_lok, '0' tot_karton FROM fg_fg_in a
left join fg_fg_out b on a.id = b.id_fg_in
where a.status = 'NORMAL' and a.lokasi != '-' and b.id_fg_in is null
group by a.po, a.no_carton
) td
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }


    public function getws_dashboard_ekspedisi(Request $request)
    {
        $data_ws = DB::select("SELECT ws isi, ws tampil
        FROM (
select * from fg_fg_in a where status = 'NORMAL' ) a
left join
(
select * from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
left join master_sb_ws m on a.id_so_det = m.id_so_det
where b.id_fg_in is null and m.buyer = '" . $request->cbobuyer . "'
group by m.ws
        ");

        $html = "<option value=''>Pilih WS</option>";

        foreach ($data_ws as $dataws) {
            $html .= " <option value='" . $dataws->isi . "'>" . $dataws->tampil . "</option> ";
        }

        return $html;
    }

    public function getpo_dashboard_ekspedisi(Request $request)
    {
        $data_po = DB::select("SELECT po isi, po tampil
        FROM (
select * from fg_fg_in a where status = 'NORMAL' ) a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
left join master_sb_ws m on a.id_so_det = m.id_so_det
where b.id_fg_in is null and m.buyer = '" . $request->cbobuyer . "'
group by po
order by po asc
        ");

        $html = "<option value=''>Pilih PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_detail_dashboard_ekspedisi(Request $request)
    {
        $buyer = $request->buyer;
        $po = $request->po;
        $ws = $request->ws;
        $no_karton = $request->no_karton;
        $no_karton = str_replace(' ', '', $no_karton);

        if (strpos($no_karton, '-') !== false) {
            $no_karton = str_replace(' ', '', $no_karton);
            $cekArray = explode('-', $no_karton);
            $no_awal = $cekArray[0];
            $no_akhir = $cekArray[1];
            if ($no_karton == "" || $no_karton == null) {
                $add_no_karton = "";
            } else {
                $add_no_karton = "and cast(a.no_carton as INT) >= '$no_awal' and cast(a.no_carton as INT) <= '$no_akhir'";
            }
        } else {
            if ($no_karton == "" || $no_karton == null) {
                $add_no_karton = "";
            } else {
                $add_no_karton = "and cast(a.no_carton as INT) = '$no_karton'";
            }
        }


        if ($ws == "" || $ws == null) {
            $add_ws = "";
        } else {
            $add_ws = "and m.ws = '$ws'";
        }

        if ($po == "" || $po == null) {
            $add_po = "";
        } else {
            $add_po = "and a.po = '$po'";
        }

        if ($request->ajax()) {
            $data_det = DB::select("SELECT
m.buyer,
m.ws,
m.color,
m.size,
m.dest,
a.po,
a.no_carton,
a.notes,
a.qty,
a.lokasi,
p.tgl_shipment
FROM
(
select * from fg_fg_in a where status = 'NORMAL' ) a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
left join master_sb_ws m on a.id_so_det = m.id_so_det
left join ppic_master_so p on a.id_ppic_master_so = p.id
where b.id_fg_in is null and m.buyer = '$buyer' $add_ws $add_po $add_no_karton
order by ws asc, po asc, color asc, size asc
        ");
            return DataTables::of($data_det)->toJson();
        }
    }
}
