<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class PPICDashboardController extends Controller
{
    public function dashboard_ppic(Request $request)
    {
        $bln = Carbon::now()->format('Ymd');
        $tgl_filter = $request->dateFilter;
        $thn = date('Y');
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
left join (select id_so_det, group_concat(distinct(po)) list_po from ppic_master_so where tgl_shipment >= '$tgl_filter' group by id_so_det) b on a.so_det_id = b.id_so_det
group by a.created_by, m.ws, m.color, m.size, m.dest
order by a.created_by asc, ws asc, color asc, dest asc, urutan asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_bulan = DB::select("SELECT bulan isi, nama_bulan tampil FROM `dim_date` where tahun = '2025'
GROUP BY bulan
order by cast(bulan as UNSIGNED) asc");


        return view('ppic.dashboard_ppic', [
            'page' => 'dashboard-ppic',
            "data_bulan" => $data_bulan,
            "thn" => $thn
        ]);
    }

    public function show_tot_dash_ppic(Request $request)
    {
        $data_header = DB::select("SELECT
sum(qty_order)qty_order,
sum(tot_buyer)tot_buyer,
sum(tot_po)tot_po,
sum(tot_out)tot_out
from
(
select sum(qty_po) qty_order, '0' tot_buyer, '0' tot_po, '0' tot_out from ppic_master_so where month(tgl_shipment) = '$request->blnFilter'
union
select '0' qty_order, count(distinct(buyer)) tot_buyer, '0' tot_po, '0' tot_out from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where month(p.tgl_shipment) = '$request->blnFilter'
union
select '0' qty_order, '0' tot_buyer, count(distinct(po)) tot_po,'0' tot_out from ppic_master_so where month(tgl_shipment) = '$request->blnFilter'
union
select '0' qty_order,'0' tot_buyer, '0' tot_po, sum(coalesce((tot_out),0)) tot_out from
(select barcode, po, dest, qty_po from ppic_master_so where month(tgl_shipment) = '$request->blnFilter') a
left join
(
select count(barcode) tot_out, barcode, po, dest from packing_packing_out_scan group by barcode, po, dest
) b on a.barcode = b.barcode and a.po = b.po and a.dest = b.dest
) x        ");
        return json_encode($data_header ? $data_header[0] : null);
    }

    public function get_data_dash_ppic(Request $request)
    {
        $data_order = DB::select("select
nama_bulan x,
coalesce(qty_order,0) y
from
(
select nama_bulan, bulan from dim_date
where tahun = '2025'
group by bulan
order by cast(bulan as int) asc ) a
left join
(
select month(tgl_shipment) bulan,sum(qty_po) qty_order from ppic_master_so
where year(tgl_shipment) = '2025'
group by month(tgl_shipment)
) b on a.bulan = b.bulan");
        return json_encode($data_order);
    }

    public function show_data_dash_ship_hr_ini(Request $request)
    {
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $bln_filter = $request->blnFilter;
        if ($request->ajax()) {

            $data_shp = DB::select("
            SELECT
            m.buyer,
            concat((DATE_FORMAT(a.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(a.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(a.tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            a.po,
            a.dest,
            a.desc,
            m.ws,
            m.styleno,
            m.color,
            sum(a.qty_po) qty_po,
            sum(coalesce(trf.qty_trf,0)) qty_trf,
            sum(coalesce(pck.qty_packing_in,0)) qty_packing_in,
            sum(coalesce(pck_out.qty_packing_out,0)) qty_packing_out
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
            where month(tgl_shipment) = '$bln_filter'
            group by po, color
            ");
            // dd($data_shp);

            return DataTables::of($data_shp)->toJson();
        }
    }
}
