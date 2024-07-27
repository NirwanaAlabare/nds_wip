<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class ReportDocController extends Controller
{
    public function report_doc_laporan_wip(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("
                SELECT
                a.no_trans,
                concat((DATE_FORMAT(tgl_trans,  '%d')), '-', left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')
                ) tgl_trans_fix,
                a.line,
                a.po,
                m.ws,
                m.color,
                m.size,
                a.qty,
                if(a.qty - c.qty_in = '0','Full','-') status,
                a.id,
                a.tujuan,
                a.created_at,
                a.created_by
                from packing_trf_garment a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in
                    where sumber = 'Sewing'
                    group by id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
            union
                SELECT
                a.no_trans,
                concat((DATE_FORMAT(tgl_trans,  '%d')), '-', left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')
                ) tgl_trans_fix,
                'Temporary' line,
                a.po,
                m.ws,
                m.color,
                m.size,
                a.qty,
                if(a.qty - c.qty_in = '0','Full','-') status,
                a.id,
                'Packing' tujuan,
                a.created_at,
                a.created_by
                from packing_trf_garment_out_temporary a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                inner join master_sb_ws m on a.id_so_det = m.id_so_det
                left join
                    (
                    select id_trf_garment, sum(qty) qty_in from packing_packing_in
                    where sumber = 'Temporary'
                    group by id_trf_garment
                    ) c on a.id = c.id_trf_garment
                where tgl_trans >= '$tgl_awal' and tgl_trans <= '$tgl_akhir'
								order by created_at desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('report_doc.laporan_wip', ['page' => 'dashboard-report-doc', "subPageGroup" => "report-doc-laporan", "subPage" => "report-doc-laporan-wip"]);
    }

    public function show_report_doc_lap_wip(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        $delete_tmp_fab_in =  DB::delete("
        delete from report_doc_lap_wip_tmp_fab_in where created_by = '$user'");

        $data_fab_in = DB::connection('mysql_sb')->select("SELECT
bppb.id_item,
br.ws,
mi.itemdesc,
sum(bppb.qty) qty_in,
bppb.unit
from bppb
inner join (
select bppbno,ifnull(idws_act,ac.kpno) ws from bppb_req br
inner join jo_det jd on br.id_jo = jd.id_jo
inner join so on jd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
 group by bppbno, idws_act) br on bppb.bppbno_req = br.bppbno
inner join masteritem mi on bppb.id_item = mi.id_item
where bppbno_int like '%GK/OUT%' and bppbno_req != '' and bppbdate >= '2024-01-01' and bppb.id_supplier = '432'
group by bppb.id_item, bppb.id_jo
            ");
        for ($i = 0; $i < count($data_fab_in); $i++) {
            $i_id_item = $data_fab_in[$i]->id_item;
            $i_itemdesc = $data_fab_in[$i]->itemdesc;
            $i_ws = $data_fab_in[$i]->ws;
            $i_qty_in = $data_fab_in[$i]->qty_in;
            $i_unit = $data_fab_in[$i]->unit;
            $insert_mut =  DB::insert("
                insert into report_doc_lap_wip_tmp_fab_in
                (id_item,itemdesc,ws,qty_in,unit,created_by,created_at,updated_at)
                values('$i_id_item','$i_itemdesc','$i_ws','$i_qty_in','$i_unit','$user','$timestamp','$timestamp')");
        }

        $data_wip = DB::select("select
a.ws,
a.id_item,
a.itemdesc,
'0' qty_sawal,
a.qty_in,
a.unit,
b.qty_out,
b.unit,
case
when a.unit = 'YRD' and b.unit = 'METER' then b.qty_out * 1.09
when a.unit = 'KGM' and b.unit = 'KGM' then b.qty_out
when a.unit = 'METER' and b.unit = 'METER' then b.qty_out
END AS qty_out_konversi,
a.unit konversi,
'0' sisa
from report_doc_lap_wip_tmp_fab_in a
left join (
select
id_item,
m.act_costing_ws ws,
detail_item itemdesc,
'0' qty_in,
round(sum(total_pemakaian_roll),2) qty_out,
a.unit
from form_cut_input_detail a
inner join form_cut_input f on a.no_form_cut_input = f.no_form
left join marker_input m on f.id_marker = m.kode
where a.created_at >= '2024-01-01'
group by id_item, act_costing_ws
) b on a.id_item = b.id_item and a.ws = b.ws
order by ws asc");
        return DataTables::of($data_wip)->toJson();
    }
}
