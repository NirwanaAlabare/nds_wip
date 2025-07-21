<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectProsesPackingListController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
a.tgl_dok,
DATE_FORMAT(a.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
a.no_dok,
a.no_invoice,
a.supplier,
ms.supplier buyer,
ac.styleno,
b.id_item,
count(no_roll) jml_roll,
count(distinct(no_lot)) jml_lot,
mi.color,
a.type_pch,
min(c.id) id_lok_in_material,
IF(d.no_invoice IS NULL, 'N', 'Y') AS status_inspect
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join
(
select id_item, id_jo, no_invoice from signalbit_erp.qc_inspect_form group by id_item, id_jo, no_invoice
) d on c.id_item = d.id_item and c.id_jo = d.id_jo and a.no_invoice = d.no_invoice
where a.tgl_dok >= '$tgl_awal' and a.tgl_dok <= '$tgl_akhir' and a.type_pch not like '%Pengembalian dari Produksi%' and b.status = 'Y' and c.status = 'Y' and a.status != 'Cancel'
group by a.tgl_dok, a.no_invoice,b.id_item, b.id_jo, b.unit
order by tgl_dok asc, no_dok asc, no_invoice asc, color asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_packinglist',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-packing-list",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function qc_inspect_proses_packing_list_det($id_lok_in_material)
    {
        $user = Auth::user()->name;

        $get_header = DB::connection('mysql_sb')->select("SELECT
    a.tgl_dok,
    DATE_FORMAT(a.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
    a.no_dok,
    a.supplier,
    ms.supplier buyer,
	ac.styleno,
    no_invoice,
    b.id_item,
    b.id_jo,
    count(no_roll) jml_roll,
    count(distinct(no_lot)) jml_lot,
    mi.color,
    a.type_pch
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join
(
select no_dok, id_item, id_jo from signalbit_erp.whs_lokasi_inmaterial where id = ?
) d on a.no_dok = d.no_dok and b.id_item = d.id_item and b.id_jo = d.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by a.tgl_dok, a.no_dok,b.id_item, b.id_jo, b.unit", [$id_lok_in_material]);

        $tgl_dok_fix = $get_header[0]->tgl_dok_fix;
        $no_invoice = $get_header[0]->no_invoice;
        $buyer = $get_header[0]->buyer;
        $styleno = $get_header[0]->styleno;
        $color = $get_header[0]->color;
        $id_item = $get_header[0]->id_item;
        $id_jo = $get_header[0]->id_jo;
        $jml_lot = $get_header[0]->jml_lot;
        $jml_roll = $get_header[0]->jml_roll;
        $type_pch = $get_header[0]->type_pch;

        $data_group = DB::connection('mysql_sb')->select("SELECT
group_inspect isi,
concat(group_inspect, ' - ', name_fabric_group) tampil
from qc_inspect_master_group_inspect");


        return view(
            'qc_inspect.proses_det_packinglist',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-packing-list",
                "containerFluid" => true,
                "user" => $user,
                "tgl_dok_fix" => $tgl_dok_fix,
                "no_invoice" => $no_invoice,
                "buyer" => $buyer,
                "styleno" => $styleno,
                "color" => $color,
                "id_item" => $id_item,
                "id_jo" => $id_jo,
                "jml_lot" => $jml_lot,
                "jml_roll" => $jml_roll,
                "type_pch" => $type_pch,
                "data_group" => $data_group
            ]
        );
    }

    public function show_calculate_qc_inspect(Request $request)
    {
        $user = Auth::user()->name;

        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_inv = $request->no_inv;
        $cek_inspect = $request->cek_inspect;
        $cbo_group_def = $request->cbo_group_def;

        $get_data_inspect_group = DB::connection('mysql_sb')->select("SELECT
        shipment
        from qc_inspect_master_group_inspect where id = '$cbo_group_def'");

        $max_shipment = !empty($get_data_inspect_group) ? $get_data_inspect_group[0]->shipment : '0';
        $data_input = DB::connection('mysql_sb')->select("SELECT
                        c.no_lot,
                        count(no_roll) jml_roll,
                        CEIL(count(no_roll) * ($cek_inspect /100)) jml_roll_cek,
                        if(d.tot_form is null, '0', d.tot_form) tot_form,
						IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect, ' %')) AS cek_inspect,
						CONCAT('Inspect Ke ', IF(d.proses IS NULL, 1, d.proses)) AS proses,
                        IF(d.proses IS NULL, 1, d.proses) AS proses_int,
                        IF(d.shipment IS NULL, $max_shipment, d.shipment) max_shipment,
                        '0' shipment_point,
                        '-' result,
                        IF(d.no_lot IS NULL, 'N', 'Y') AS status_lot
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
LEFT JOIN
(
		SELECT no_lot, id_item, id_jo, no_invoice, max(proses) proses, a.group_inspect, max(shipment) shipment, max(cek_inspect) cek_inspect, count(no_lot) tot_form
		FROM signalbit_erp.qc_inspect_form a
		inner join signalbit_erp.qc_inspect_master_group_inspect b on a.group_inspect = b.id
		where a.id_item = '$id_item' and a.id_jo = '$id_jo' and a.no_invoice = '$no_inv'
		GROUP BY no_lot, id_item, id_jo, no_invoice
) d
ON c.no_lot = d.no_lot
AND c.id_item = d.id_item
AND c.id_jo = d.id_jo
AND a.no_invoice = d.no_invoice
where c.id_item = '$id_item' and c.id_jo = '$id_jo' and a.no_invoice = '$no_inv'
group by c.no_lot
            ");

        $statusLotNCount = collect($data_input)->where('status_lot', 'N')->count();

        return DataTables::of($data_input)
            ->with([
                'status_lot_n_count' => $statusLotNCount,
                'cbo_group_def' => $cbo_group_def,
            ])
            ->toJson();
    }

    public function generate_qc_inspect(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_inv = $request->no_inv;
        $cek_inspect = $request->cek_inspect;
        $cbo_group_def = $request->cbo_group_def;

        $get_data_inspect_group = DB::connection('mysql_sb')->select("
        SELECT shipment
        FROM qc_inspect_master_group_inspect
        WHERE id = ?", [$cbo_group_def]);

        $max_shipment = !empty($get_data_inspect_group) ? $get_data_inspect_group[0]->shipment : 0;

        $data = DB::connection('mysql_sb')->select("SELECT
                        c.no_lot,
                        count(no_roll) jml_roll,
                        CEIL(count(no_roll) * ($cek_inspect /100)) jml_roll_cek,
                        if(d.tot_form is null, '0', d.tot_form) tot_form,
						IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect, ' %')) AS cek_inspect,
						CONCAT('Inspect Ke ', IF(d.proses IS NULL, 1, d.proses)) AS proses,
                        IF(d.proses IS NULL, 1, d.proses) AS proses_int,
                        IF(d.shipment IS NULL, $max_shipment, d.shipment) max_shipment,
                        '0' shipment_point,
                        '-' result,
                        IF(d.no_lot IS NULL, 'N', 'Y') AS status_lot
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
LEFT JOIN
(
		SELECT no_lot, id_item, id_jo, no_invoice, max(proses) proses, a.group_inspect, max(shipment) shipment, max(cek_inspect) cek_inspect, count(no_lot) tot_form
		FROM signalbit_erp.qc_inspect_form a
		inner join signalbit_erp.qc_inspect_master_group_inspect b on a.group_inspect = b.id
		where a.id_item = '$id_item' and a.id_jo = '$id_jo' and a.no_invoice = '$no_inv'
		GROUP BY no_lot, id_item, id_jo, no_invoice
) d
ON c.no_lot = d.no_lot
AND c.id_item = d.id_item
AND c.id_jo = d.id_jo
AND a.no_invoice = d.no_invoice
where c.id_item = '$id_item' and c.id_jo = '$id_jo' and a.no_invoice = '$no_inv' and IF(d.no_lot IS NULL, 'N', 'Y')  = 'N'
group by c.no_lot
    ");
        // Collect data for response
        $lot_summary = [];
        $total_generated = 0;


        // Prepare date values
        $datePrefix = $timestamp->format('dmy'); // DDMMYY format for no_form

        $month = $timestamp->format('m'); // Gets the month as "01" to "12"
        $year = $timestamp->format('Y');  // Gets the year as "2025"
        $currentDate = $timestamp->format('Y-m-d');

        $get_last_number = DB::connection('mysql_sb')->select("SELECT
        MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
        from qc_inspect_form where month(tgl_form) = '$month' and year(tgl_form) = '$year'");

        $last_number = $get_last_number[0]->last_number ?? 0;

        $formCounter = $last_number + 1;


        foreach ($data as $row) {
            $lot_summary[] = [
                'no_lot' => $row->no_lot,
                'generated_forms' => $row->jml_roll_cek,
            ];
            $total_generated += $row->jml_roll_cek;

            for ($i = 0; $i < $row->jml_roll_cek; $i++) {
                $no_form = 'INS/' . $datePrefix . '/' . $formCounter++;
                DB::connection('mysql_sb')->table('qc_inspect_form')->insert([
                    'no_form'               => $no_form,
                    'tgl_form'              => $currentDate,
                    'no_lot'                => $row->no_lot,
                    'no_invoice'            => $no_inv,
                    'id_item'               => $id_item,
                    'id_jo'                 => $id_jo,
                    'group_inspect'         => $cbo_group_def,
                    'cek_inspect'           => $cek_inspect,
                    'proses'                => $row->proses_int,
                    'status'                => 'draft',
                    'created_by'            => $user,
                    'created_at'            => $timestamp,
                    'updated_at'            => $timestamp,
                ]);
            }
        }


        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Data sudah di Generate',
            'summary' => $lot_summary,
            'total_generated_forms' => $total_generated,
            'data' => [
                'id_item' => $id_item,
                'id_jo' => $id_jo,
                'no_inv' => $no_inv,
            ]
        ]);
    }
}
