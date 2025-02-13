<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class PackingDashboardController extends Controller
{
    public function dashboard_packing(Request $request)
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

        return view('packing.dashboard_packing', ['page' => 'dashboard-packing']);
    }

    public function show_tot_dash_packing(Request $request)
    {
        $data_header = DB::select("
SELECT
sum(tot_p_line) tot_p_line,
sum(tot_trf_garment) tot_trf_garment,
sum(tot_packing_in) tot_central_in,
sum(tot_packing_out) tot_packing_out
from
(
select count(so_det_id) tot_p_line , '0' tot_trf_garment, '0' tot_packing_in , '0' tot_packing_out
from output_rfts_packing where created_at >='$request->dateFilter'
union
select '0' tot_p_line,sum(qty) tot_trf_garment ,'0' tot_packing_in , '0' tot_packing_out
from packing_trf_garment where created_at >='$request->dateFilter'
union
select '0' tot_p_line,'0' tot_trf_garment,sum(qty) tot_packing_in, '0' tot_packing_out
from packing_packing_in where created_at >='$request->dateFilter'
union
select '0' tot_p_line,'0' tot_trf_garment,'0' tot_packing_in,count(barcode) tot_packing_out
from packing_packing_out_scan where created_at >='$request->dateFilter'
) a
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }
}
