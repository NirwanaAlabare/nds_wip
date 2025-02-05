<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPPICTracking;
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
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-laporan",
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
        $ws = $request->ws;

        $filterWs = "";
        $filterKpno = "";
        if ($ws) {
            $filterWs .= " and ws = '" . $ws . "'";
            $filterKpno = " and ac.kpno = '" . $ws . "'";
        }

        $delete_tmp_qc =  DB::delete("delete from ppic_laporan_tracking_tmp_qc_output where created_by = '$user' and buyer = '$buyer' " . $filterWs);

        $delete_tmp_p_line =  DB::delete("delete from ppic_laporan_tracking_tmp_packing_line where created_by = '$user' and buyer = '$buyer' " . $filterWs);

        $data_qc = DB::connection('mysql_sb')->select("SELECT
ms.supplier buyer, ac.kpno ws, sd.color, sd.size, dest, sum(a.tot) tot_qc from
(select so_det_id,count(so_det_id) tot from output_rfts group by so_det_id) a
inner join so_det sd on a.so_det_id = sd.id
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
left join master_size_new msn on sd.size = msn.size
where ms.supplier = '$buyer' " . $filterKpno . "
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
        left join master_size_new msn on m.size = msn.size
        where m.buyer = '$buyer' " . $filterWs . "
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
from master_sb_ws where buyer = '$buyer' " . $filterWs . "
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
where buyer = '$buyer' and created_by = '$user' " . $filterWs . "
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
where buyer = '$buyer' and created_by = '$user' " . $filterWs . "
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
where buyer = '$buyer' " . $filterWs . "
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
where m.buyer = '$buyer' " . $filterWs . "
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
sum(o.qty_packing_out) qty_packing_out
from
    (
        select count(barcode) qty_packing_out,po, barcode, dest from packing_packing_out_scan
        group by barcode, po, dest
    ) o
inner join ppic_master_so p on o.barcode = p.barcode and o.po = p.po and o.dest = p.dest
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where m.buyer = '$buyer' " . $filterWs . "
group by ws, color, size
) a
left join master_size_new msn on a.size = msn.size
group by ws, color, a.size
order by ws asc, color asc, urutan asc, a.size asc
                    ");
        return DataTables::of($data_tracking)->toJson();
    }


    public function export_excel_tracking(Request $request)
    {
        $user = Auth::user()->name;
        return Excel::download(new ExportLaporanPPICTracking($request->buyer, $request->ws, $user), 'Laporan_Tracking.xlsx');
    }


    public function ppic_monitoring_order(Request $request)
    {

        $data_buyer = DB::connection('mysql_sb')->select("SELECT supplier isi, supplier tampil from signalbit_erp.so
                                inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                                inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                                where so_date >= '2024-05-01' and ac.status = 'CONFIRM'
                                GROUP BY supplier
                                order by supplier asc
");

        return view(
            'ppic.monitoring_order',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-laporan",
                "subPage" => "ppic_monitoring_order",
                "containerFluid" => true,
                "data_buyer" => $data_buyer
            ]
        );
    }

    public function get_ppic_monitoring_order_style(Request $request)
    {
        $data_style =  DB::connection('mysql_sb')->select("select sd.styleno_prod isi, sd.styleno_prod tampil from signalbit_erp.so
inner join signalbit_erp.so_det sd on so.id = sd.id_so
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
where so_date >= '2024-05-01' and ac.status = 'CONFIRM' and supplier = '" . $request->buyer . "'
GROUP BY styleno_prod
order by styleno_prod asc
        ");

        $html = "<option value=''>Pilih Style</option>";

        foreach ($data_style as $datastyle) {
            $html .= " <option value='" . $datastyle->isi . "'>" . $datastyle->tampil . "</option> ";
        }

        return $html;
    }

    public function get_ppic_monitoring_order_reff(Request $request)
    {
        $data_reff =  DB::connection('mysql_sb')->select("SELECT sd.reff_no isi, sd.reff_no tampil from signalbit_erp.so
                        inner join signalbit_erp.so_det sd on so.id = sd.id_so
                        inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        where so_date >= '2024-05-01' and ac.status = 'CONFIRM' and supplier = '" . $request->buyer . "' and sd.styleno_prod = '" . $request->style . "'
                        GROUP BY sd.reff_no
                        order by sd.reff_no  asc
        ");
        $html = "<option value=''>Pilih Reff</option>";

        foreach ($data_reff as $datareff) {
            $html .= " <option value='" . $datareff->isi . "'>" . $datareff->tampil . "</option> ";
        }

        return $html;
    }

    public function get_ppic_monitoring_order_ws(Request $request)
    {
        $data_ws =  DB::connection('mysql_sb')->select("SELECT ac.kpno isi, ac.kpno tampil from signalbit_erp.so
                        inner join signalbit_erp.so_det sd on so.id = sd.id_so
                        inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        where so_date >= '2024-05-01' and ac.status = 'CONFIRM' and supplier = '" . $request->buyer . "' and sd.styleno_prod = '" . $request->style . "' and sd.reff_no = '" . $request->reff . "'
                        GROUP BY ac.kpno
                        order by ac.kpno  asc
        ");
        $html = "<option value=''>Pilih WS</option>";

        foreach ($data_ws as $dataws) {
            $html .= " <option value='" . $dataws->isi . "'>" . $dataws->tampil . "</option> ";
        }

        return $html;
    }

    public function get_ppic_monitoring_order_color(Request $request)
    {
        $data_color =  DB::connection('mysql_sb')->select("SELECT sd.color isi, sd.color tampil from signalbit_erp.so
                        inner join signalbit_erp.so_det sd on so.id = sd.id_so
                        inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        where so_date >= '2024-05-01' and ac.status = 'CONFIRM'  and supplier = '" . $request->buyer . "' and sd.styleno_prod = '" . $request->style . "'
                        and sd.reff_no = '" . $request->reff . "' and ac.kpno = '" . $request->ws . "'
                        GROUP BY sd.color
                        order by sd.color  asc

        ");
        $html = "<option value=''>Pilih Color</option>";

        foreach ($data_color as $datacolor) {
            $html .= " <option value='" . $datacolor->isi . "'>" . $datacolor->tampil . "</option> ";
        }

        return $html;
    }

    public function get_ppic_monitoring_order_size(Request $request)
    {
        $data_size =  DB::connection('mysql_sb')->select("SELECT sd.size isi, sd.size tampil from signalbit_erp.so
                        inner join signalbit_erp.so_det sd on so.id = sd.id_so
                        inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        left join signalbit_erp.master_size_new msn on sd.size = msn.size
                        where so_date >= '2024-05-01' and ac.status = 'CONFIRM' and supplier = '" . $request->buyer . "' and sd.styleno_prod = '" . $request->style . "'
                        and sd.reff_no = '" . $request->reff . "' and ac.kpno = '" . $request->ws . "' and sd.color = '" . $request->color . "'
                        GROUP BY sd.size
                        order by msn.urutan  asc


        ");
        $html = "<option value=''>Pilih Size</option>";

        foreach ($data_size as $datasize) {
            $html .= " <option value='" . $datasize->isi . "'>" . $datasize->tampil . "</option> ";
        }

        return $html;
    }


    public function show_lap_monitoring_order(Request $request)
    {
        $user = Auth::user()->name;
        $buyer = $request->buyer_filter;
        $style = $request->style_filter;
        $reff = $request->reff_filter;
        $ws = $request->ws_filter;
        $color = $request->color_filter;
        $size = $request->size_filter;
        if (!empty($style)) {
            $cond_style = " and sd.styleno_prod = '" . $style  . "'";
        } else {
            $cond_style = "";
        }
        if (!empty($reff)) {
            $cond_reff = " and sd.reff_no = '" . $reff  . "'";
        } else {
            $cond_reff = "";
        }
        if (!empty($ws)) {
            $cond_ws = " and ac.kpno = '" . $ws  . "'";
        } else {
            $cond_ws = "";
        }
        if (!empty($color)) {
            $cond_color = " and sd.color = '" . $color  . "'";
        } else {
            $cond_color = "";
        }
        if (!empty($size)) {
            $cond_size = " and sd.size = '" . $size  . "'";
        } else {
            $cond_size = "";
        }

        $data_monitoring_order = DB::connection('mysql_sb')->select("WITH CTE AS (
SELECT
            a.buyer,
			a.ws,
			a.color,
			a.size,
			a.styleno_prod,
			a.reff_no,
			a.tgl_shipment,
			a.qty_po,
			qty_cut,
			qty_loading,
			output_rfts,
			output_rfts_packing,
			ROW_NUMBER() OVER (PARTITION BY a.ws, a.color, a.size ORDER BY a.tgl_shipment) AS rn,
			SUM(a.qty_po) OVER (PARTITION BY a.ws, a.color, a.size ORDER BY a.tgl_shipment) AS prev_tot_po,
			qty_cut - 	SUM(a.qty_po) OVER (PARTITION BY a.ws, a.color, a.size ORDER BY a.tgl_shipment) balance_cut,
			qty_loading - 	SUM(a.qty_po) OVER (PARTITION BY a.ws, a.color, a.size ORDER BY a.tgl_shipment) balance_loading,
			output_rfts - 	SUM(a.qty_po) OVER (PARTITION BY a.ws, a.color, a.size ORDER BY a.tgl_shipment) balance_output_rfts,
			output_rfts_packing - 	SUM(a.qty_po) OVER (PARTITION BY a.ws, a.color, a.size ORDER BY a.tgl_shipment) balance_output_rfts_packing
FROM
(
			SELECT
            supplier buyer,
            kpno ws,
            color,
            size,
			styleno_prod,
			reff_no,
            tgl_shipment,
            sum(qty_po) qty_po
        FROM
            laravel_nds.ppic_master_so p
        INNER JOIN
            signalbit_erp.so_det sd ON p.id_so_det = sd.id
	      INNER JOIN
            signalbit_erp.so so ON sd.id_so = so.id
	      INNER JOIN
            signalbit_erp.act_costing ac ON so.id_cost = ac.id
	      INNER JOIN
            signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            where ms.supplier = '$buyer' $cond_style $cond_reff $cond_ws $cond_color $cond_size
				GROUP BY
				ws, color, size,styleno_prod, reff_no, tgl_shipment
				ORDER BY
            tgl_shipment asc
) a
LEFT JOIN
(
				SELECT
						kpno ws,
						color,
						size,
						styleno_prod,
						reff_no,
            SUM(qty_cut) AS qty_cut,
						SUM(qty_loading) AS qty_loading,
            SUM(output_rfts) AS output_rfts,
            SUM(output_rfts_packing) AS output_rfts_packing
        FROM
        (
SELECT
		id_so_det,
		MIN(qty_cut) AS qty_cut,
		0 AS qty_loading,
		0 AS output_rfts,
		0 AS output_rfts_packing
FROM
(
		SELECT
				marker_input_detail.so_det_id AS id_so_det,
				CASE WHEN modify_size_qty.modified_qty > 0 THEN modify_size_qty.modified_qty ELSE SUM(marker_input_detail.ratio * form_cut_input.total_lembar) END AS qty_cut
		FROM
				laravel_nds.form_cut_input
		LEFT JOIN
				laravel_nds.marker_input ON marker_input.kode = form_cut_input.id_marker
		LEFT JOIN
				laravel_nds.marker_input_detail ON marker_input_detail.marker_id = marker_input.id
		LEFT JOIN
				laravel_nds.users AS meja ON meja.id = form_cut_input.no_meja
		LEFT JOIN
				laravel_nds.modify_size_qty ON modify_size_qty.so_det_id = marker_input_detail.so_det_id and modify_size_qty.form_cut_id = form_cut_input.id
		WHERE
				COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), form_cut_input.tgl_form_cut) >= '2024-01-01'
				AND form_cut_input.status = 'SELESAI PENGERJAAN'
				AND (marker_input_detail.ratio > 0 OR modify_size_qty.modified_qty > 0)
		GROUP BY
				marker_input.panel,
				marker_input_detail.so_det_id
) cutting
GROUP BY
		id_so_det
            UNION ALL
            SELECT
                so_det_id AS id_so_det,
                0 AS qty_cut,
                qty AS qty_loading,
                0 AS output_rfts,
                0 AS output_rfts_packing
            FROM
                laravel_nds.loading_line a
            INNER JOIN
                laravel_nds.stocker_input b ON a.stocker_id = b.id
            WHERE
                a.updated_at >= '2024-01-01'
            GROUP BY
						b.so_det_id,
						b.form_cut_id,
						b.group_stocker,
						b.ratio
            UNION ALL
            SELECT
                so_det_id AS id_so_det,
                0 AS qty_cut,
                0 AS qty_loading,
				COUNT(so_det_id) AS output_rfts,
                0 AS output_rfts_packing
            FROM
                signalbit_erp.output_rfts a
            WHERE
                a.updated_at >= '2024-01-01'
            GROUP BY
                so_det_id
            UNION ALL
            SELECT
                so_det_id AS id_so_det,
                 0 AS qty_cut,
                0 AS qty_loading,
                0 AS output_rfts,
                COUNT(so_det_id) AS output_rfts_packing
            FROM
                signalbit_erp.output_rfts_packing a
            WHERE
                a.updated_at >= '2024-01-01'
            GROUP BY
                so_det_id
        ) d
        INNER JOIN
            signalbit_erp.so_det sd ON d.id_so_det = sd.id
	      INNER JOIN
            signalbit_erp.so so ON sd.id_so = so.id
	      INNER JOIN
            signalbit_erp.act_costing ac ON so.id_cost = ac.id
	      INNER JOIN
            signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
            where ms.supplier = '$buyer' $cond_style $cond_reff $cond_ws $cond_color $cond_size
				group by kpno, color, size
) b on a.ws = b.ws and a.color = b.color and a.size = b.size and a.styleno_prod = b.styleno_prod and a.reff_no = b.reff_no
GROUP BY
				ws, color, size, tgl_shipment
)
SELECT
buyer,
CTE.ws,
CTE.color,
CTE.size,
CTE.styleno_prod,
CTE.reff_no,
CTE.tgl_shipment,
DATE_FORMAT(CTE.tgl_shipment, '%d-%m-%Y') tgl_shipment_fix,
qty_po,
coalesce(qty_cut,0) qty_cut,
coalesce(case
		when LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and qty_cut >= qty_po then qty_po
		when LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and qty_cut <= qty_po then qty_cut
		when LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) >= qty_po then qty_po
		when LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= qty_cut and LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) > '0'
					then LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment)
		when LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= qty_cut and LAG(balance_cut) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) < '0'
					then '0'
end,0) as final_cut,
coalesce(qty_loading,0) qty_loading,
coalesce(case
		when LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and qty_loading >= qty_po then qty_po
		when LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and qty_loading <= qty_po then qty_loading
		when LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) >= qty_po then qty_po
		when LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= qty_loading and LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) > '0'
					then LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment)
		when LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= qty_loading and LAG(balance_loading) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) < '0'
					then '0'
end,0) as final_loading,
coalesce(output_rfts,0) output_rfts,
coalesce(case
		when LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and output_rfts >= qty_po then qty_po
		when LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and output_rfts <= qty_po then output_rfts
		when LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) >= qty_po then qty_po
		when LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= output_rfts and LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) > '0'
					then LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment)
		when LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= output_rfts and LAG(balance_output_rfts) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) < '0'
					then '0'
end,0) as final_output_rfts,
coalesce(output_rfts_packing,0) output_rfts_packing,
coalesce(case
		when LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and output_rfts_packing >= qty_po then qty_po
		when LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) is null and output_rfts_packing <= qty_po then output_rfts_packing
		when LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) >= qty_po then qty_po
		when LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= output_rfts_packing and LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) > '0'
					then LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment)
		when LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) <= output_rfts_packing and LAG(balance_output_rfts_packing) OVER (PARTITION BY ws, color, size ORDER BY tgl_shipment) < '0'
					then '0'
end,0) as final_output_rfts_packing,
coalesce(c.tot_scan,0) tot_scan,
coalesce(d.tot_fg_out,0) tot_fg_out
FROM CTE
left join signalbit_erp.master_size_new msn on CTE.size = msn.size
left join
(
			select
			ac.kpno,
			sd.color,
			sd.size,
			sd.styleno_prod,
			sd.reff_no,
			sum(tot_scan) tot_scan,
			tgl_shipment
			from
			(
			select count(barcode) tot_scan, barcode, po, dest from laravel_nds.packing_packing_out_scan
			group by barcode, po
			) a
			inner join laravel_nds.ppic_master_so b on a.barcode = b.barcode and a.po = b.po and a.dest = b.dest
			inner join signalbit_erp.so_det sd on b.id_so_det = sd.id
			inner join signalbit_erp.so on sd.id_so = so.id
			inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
			inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
			where ms.supplier = '$buyer' $cond_style $cond_reff $cond_ws $cond_color $cond_size
			group by
			ac.kpno,
			sd.color,
			sd.size,
			sd.styleno_prod,
			sd.reff_no,
			b.tgl_shipment
) c on CTE.ws = c.kpno and CTE.color = c.color and CTE.size = c.size and CTE.styleno_prod = c.styleno_prod and CTE.reff_no = c.reff_no and CTE.tgl_shipment = c.tgl_shipment
left join
(
			select
			ac.kpno,
			sd.color,
			sd.size,
			sd.styleno_prod,
			sd.reff_no,
			sum(a.qty) tot_fg_out,
			tgl_shipment
			from laravel_nds.fg_fg_out a
			inner join laravel_nds.ppic_master_so b on a.id_ppic_master_so = b.id
			inner join signalbit_erp.so_det sd on b.id_so_det = sd.id
			inner join signalbit_erp.so on sd.id_so = so.id
			inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
			inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
			where ms.supplier = '$buyer' $cond_style $cond_reff $cond_ws $cond_color $cond_size and a.status = 'NORMAL'
			group by
			ac.kpno,
			sd.color,
			sd.size,
			sd.styleno_prod,
			sd.reff_no,
			b.tgl_shipment
) d on CTE.ws = d.kpno and CTE.color = d.color and CTE.size = d.size and CTE.styleno_prod = d.styleno_prod and CTE.reff_no = d.reff_no and CTE.tgl_shipment = d.tgl_shipment
order by tgl_shipment asc, urutan asc
                    ");

        return DataTables::of($data_monitoring_order)->toJson();
    }
}
