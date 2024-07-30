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

    public function show_report_doc_lap_fab(Request $request)
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
ws,
id_item,
itemdesc,
'0' qty_sawal,
round(sum(qty_in),2) qty_in,
round(sum(qty_out),2) qty_out,
round(round(sum(qty_in),2) - round(sum(qty_out),2),2) qty_sisa,
unit
from (
SELECT
	ws,
	id_item,
	itemdesc,
	case
	when  a.unit = 'YRD' then qty_in * 0.9144
	else qty_in
	end as qty_in,
	'0' qty_out,
	case
	when  a.unit = 'YRD' then  'METER'
	else a.unit
	end as unit
FROM
	report_doc_lap_wip_tmp_fab_in a
UNION
SELECT
	c.act_costing_ws ws,
	a.id_item,
	detail_item itemdesc,
	'0' qty_in,
	SUM(a.total_pemakaian_roll) qty_out,
	a.unit
FROM
	form_cut_input_detail a
	INNER JOIN form_cut_input b ON b.no_form = a.no_form_cut_input
	INNER JOIN marker_input c ON c.kode = b.id_marker
WHERE
	a.total_pemakaian_roll > 0.0001 AND
	(b.cancel IS NULL OR b.cancel != 'Y') AND
	(c.cancel IS NULL OR c.cancel != 'Y') AND
	(a.created_at >= '2024-01-01' OR a.updated_at >= '2024-01-01')
GROUP BY
	c.act_costing_ws,
	a.id_item,
	a.unit
) mut_data
group by mut_data.ws,
 mut_data.unit
");

        // select
        // a.ws,
        // a.id_item,
        // a.itemdesc,
        // '0' qty_sawal,
        // a.qty_in,
        // a.unit,
        // b.qty_out,
        // b.unit,
        // case
        // when a.unit = 'YRD' and b.unit = 'METER' then b.qty_out * 1.09
        // when a.unit = 'KGM' and b.unit = 'KGM' then b.qty_out
        // when a.unit = 'METER' and b.unit = 'METER' then b.qty_out
        // END AS qty_out_konversi,
        // a.unit konversi,
        // '0' sisa
        // from report_doc_lap_wip_tmp_fab_in a
        // left join (
        // select
        // id_item,
        // m.act_costing_ws ws,
        // detail_item itemdesc,
        // '0' qty_in,
        // round(sum(total_pemakaian_roll),2) qty_out,
        // a.unit
        // from form_cut_input_detail a
        // inner join form_cut_input f on a.no_form_cut_input = f.no_form
        // left join marker_input m on f.id_marker = m.kode
        // where a.created_at >= '2024-01-01'
        // group by id_item, act_costing_ws
        // ) b on a.id_item = b.id_item and a.ws = b.ws
        // order by ws asc

        return DataTables::of($data_wip)->toJson();
    }

    public function show_report_doc_lap_wip(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->name;

        $delete_tmp_sew_in =  DB::delete("
        delete from report_doc_lap_wip_tmp_sew_in where created_by = '$user'");

        $data_sew_in = DB::connection('mysql_sb')->select("SELECT
ac.kpno ws,
sum(qc_in) sew_in,
'PCS' unit
from (
select master_plan_id,count(master_plan_id) qc_in from output_rfts where created_at >= '2024-01-01'
group by master_plan_id
) a
inner join master_plan m on a.master_plan_id = m.id
inner join act_costing ac on m.id_ws = ac.id
group by ac.kpno
order by ac.kpno asc
            ");
        for ($i = 0; $i < count($data_sew_in); $i++) {
            $i_ws = $data_sew_in[$i]->ws;
            $i_sew_in = $data_sew_in[$i]->sew_in;
            $i_unit = $data_sew_in[$i]->unit;
            $insert_mut =  DB::insert("
                insert into report_doc_lap_wip_tmp_sew_in
                (ws,sew_in,unit,created_by,created_at,updated_at)
                values('$i_ws','$i_sew_in','$i_unit','$user','$timestamp','$timestamp')");
        }

        $data_wip = DB::select("
SELECT
ws,
sum(dc_in) dc_in,
sum(dc_out) dc_out,
sum(sew_in) sew_in,
sum(sew_out) sew_out,
sum(pck_in) pck_in,
sum(pck_out) pck_out
FROM
(
select
	stocker_group.act_costing_ws ws,
	SUM(COALESCE(stocker_group.dc_qty_ply, 0)) dc_in,
	SUM(COALESCE(stocker_group.loading_line_qty, 0)) dc_out,
	'0' sew_in,
	'0' sew_out,
	'0' pck_in,
	'0' pck_out
from
	(
		SELECT
			stocker_input.act_costing_ws,
			stocker_input.form_cut_id,
			stocker_input.part_detail_id,
			stocker_input.color,
			stocker_input.group_stocker,
			stocker_input.ratio,
			stocker_input.so_det_id,
			min(CASE WHEN stocker_input.qty_ply_mod > 0 THEN stocker_input.qty_ply_mod ELSE stocker_input.qty_ply END) qty_ply,
			min(COALESCE(dc_in_input.qty_awal - dc_in_input.qty_reject + dc_in_input.qty_replace, 0)) dc_qty_ply,
			min(COALESCE(loading_line.qty, 0)) loading_line_qty
		FROM
			dc_in_input
			INNER JOIN stocker_input ON dc_in_input.id_qr_stocker = stocker_input.id_qr_stocker
			LEFT JOIN loading_line ON loading_line.stocker_id = stocker_input.id
		WHERE
			(dc_in_input.created_at >= '2024-01-01' OR dc_in_input.updated_at >= '2024-01-01')
		GROUP BY
			stocker_input.form_cut_id,
			stocker_input.group_stocker,
			stocker_input.ratio,
			stocker_input.so_det_id
	) stocker_group
GROUP BY
	stocker_group.act_costing_ws
UNION
SELECT ws, '0' dc_in, '0' dc_out, sew_in, '0' sew_out,'0' pck_in, '0' pck_out
FROM report_doc_lap_wip_tmp_sew_in
UNION
select ws,'0' dc_in, '0' dc_out, '0' sew_in, count(so_det_id) sew_out, '0' pck_in, '0' pck_out
from output_rfts_packing a
inner join master_sb_ws m on a.so_det_id = m.id_so_det
where created_at >= '2024-01-01'
group by m.ws
UNION
select ws, '0' dc_in, '0' dc_out, '0' sew_in, '0' sew_out, sum(a.qty) pck_in, '0' pck_out
from packing_packing_in a
inner join master_sb_ws m on a.id_so_det = m.id_so_det
group by ws
UNION
select m.ws, '0' dc_in, '0' dc_out, '0' sew_in, '0' sew_out, '0' pck_in,sum(qty_packing_out) pck_out
from
 (
  select count(barcode) qty_packing_out,po, barcode, dest from packing_packing_out_scan
	where created_at >= '2024-01-01'
  group by barcode, po, dest
 ) a
  inner join ppic_master_so p on a.barcode = p.barcode and a.po = p.po and a.dest = p.dest
	inner join master_sb_ws m on p.id_so_det = m.id_so_det
	group by ws
) dd
group by dd.ws
");

        return DataTables::of($data_wip)->toJson();
    }
}
