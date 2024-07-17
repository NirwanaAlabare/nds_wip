<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
use App\Imports\ImportPPIC_SO;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Style;

class PPIC_LaporanTrackingController extends Controller
{
    public function index(Request $request)
    {

        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->name;
        $buyer = $request->buyer;

        $data_buyer = DB::select("select buyer isi, buyer tampil from master_sb_ws
where tgl_kirim >= '2024-01-01'
group by buyer
order by buyer asc");

        return view(
            'ppic.laporan_tracking',
            [
                'page' => 'dashboard-ppic', "subPageGroup" => "ppic-laporan",
                "subPage" => "ppic-laporan-tracking",
                'data_buyer' => $data_buyer,
                "user" => $user
            ]
        );
    }

    // $no = 0;
    // foreach ($data_tracking as $key => $value) {
    //     $i_buyer = $data_tracking[$no]->buyer;
    //     $i_ws = $data_tracking[$no]->ws;
    //     $i_color = $data_tracking[$no]->color;
    //     $i_size = $data_tracking[$no]->size;
    //     $i_tot_qc = $data_tracking[$no]->tot_qc;

    //     $insert_mut =  DB::insert("
    //         insert into ppic_laporan_tracking_tmp_qc_output
    //         (buyer,ws,color,size,tot_qc,created_by,created_at,updated_at)
    //         values('$i_buyer','$i_ws','$i_color','$i_size','$i_tot_qc','$user','$timestamp','$timestamp')");
    //     $no++;
    // }


    public function show_lap_tracking_ppic(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;
        $tgl_skrg = date('Y-m-d');
        $buyer = $request->buyer;

        $delete_tmp_qc =  DB::delete("
        delete from ppic_laporan_tracking_tmp_qc_output where created_by = '$user' and buyer = '$buyer'");

        $delete_tmp_p_line =  DB::delete("
        delete from ppic_laporan_tracking_tmp_packing_line where created_by = '$user' and ws = '$buyer'");

        $data_qc = DB::connection('mysql_sb')->select("SELECT
ms.supplier buyer, ac.kpno ws, sd.color, sd.size, dest, sum(a.tot) tot_qc from
(select so_det_id,count(so_det_id) tot from output_rfts group by so_det_id) a
inner join so_det sd on a.so_det_id = sd.id
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
inner join master_size_new msn on sd.size = msn.size
where ms.supplier = '$buyer'
group by ac.kpno, sd.color, sd.size, ac.styleno
order by ac.kpno asc, sd.color asc, msn.urutan asc
            ");
        for ($i = 0; $i < count($data_qc); $i++) {
            $i_buyer = $data_qc[$i]->buyer;
            $i_ws = $data_qc[$i]->ws;
            $i_color = $data_qc[$i]->color;
            $i_size = $data_qc[$i]->size;
            $i_tot_qc = $data_qc[$i]->tot_qc;

            $insert_mut =  DB::insert("
                insert into ppic_laporan_tracking_tmp_qc_output
                (buyer,ws,color,size,tot_qc,created_by,created_at,updated_at)
                values('$i_buyer','$i_ws','$i_color','$i_size','$i_tot_qc','$user','$timestamp','$timestamp')");
        }

        $data_packing_line = DB::select("SELECT
        buyer, ws, color, m.size, tot_p_line from
        (select so_det_id,count(so_det_id) tot_p_line from output_rfts_packing a group by so_det_id) a
        inner join master_sb_ws m on a.so_det_id = m.id_so_det
        inner join master_size_new msn on m.size = msn.size
        where m.buyer = '$buyer'
        group by ws, color, m.size, m.styleno
        order by ws asc, color asc, msn.urutan asc
                            ");
        for ($i = 0; $i < count($data_packing_line); $i++) {
            $i_buyer = $data_packing_line[$i]->buyer;
            $i_ws = $data_packing_line[$i]->ws;
            $i_color = $data_packing_line[$i]->color;
            $i_size = $data_packing_line[$i]->size;
            $i_tot_qc = $data_packing_line[$i]->tot_p_line;

            $insert_mut =  DB::insert("
                                    insert into ppic_laporan_tracking_tmp_packing_line
                                    (buyer,ws,color,size,tot_p_line,created_by,created_at,updated_at)
                                    values('$i_buyer','$i_ws','$i_color','$i_size','$i_tot_qc','$user','$timestamp','$timestamp')");
        }

        $data_tracking = DB::select("SELECT
buyer,
ws,
color,
a.size,
coalesce(sum(tot_qc),0) tot_qc,
coalesce(sum(tot_p_line),0) tot_p_line,
coalesce(sum(qty_trf_garment),0) qty_trf_garment,
coalesce(sum(qty_packing_in),0) qty_packing_in,
coalesce(sum(qty_packing_out),0) qty_packing_out
from
(
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from master_sb_ws where buyer = '$buyer'
group by ws, color, size, styleno
union
select
buyer,
ws,
color,
size,
tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from ppic_laporan_tracking_tmp_qc_output
where buyer = '$buyer' and created_by = '$user'
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from ppic_laporan_tracking_tmp_packing_line
where buyer = '$buyer' and created_by = '$user'
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
sum(t.qty) as qty_trf_garment,
'0' qty_packing_in,
'0' qty_packing_out
from packing_trf_garment t
inner join ppic_master_so p on t.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where buyer = '$buyer'
group by ws, color, size
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
sum(pi.qty) qty_packing_in,
'0' qty_packing_out
from packing_packing_in pi
inner join ppic_master_so p on pi.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.buyer = '$buyer'
group by ws, color, size
union
select
buyer,
ws,
color,
size,
'0' tot_qc,
'0' tot_p_line,
'0' qty_trf_garment,
'0' qty_packing_in,
count(o.id) qty_packing_out
from packing_packing_out_scan o
inner join ppic_master_so p on o.barcode = p.barcode and o.po = p.po
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.buyer = '$buyer'
group by ws, color, size
) a
left join master_size_new msn on a.size = msn.size
group by ws, color, a.size
order by ws asc, color asc, urutan asc, a.size asc
                    ");
        return DataTables::of($data_tracking)->toJson();
    }
}
