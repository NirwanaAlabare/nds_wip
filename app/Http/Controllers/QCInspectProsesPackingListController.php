<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use PDF;

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
IF(d.no_invoice IS NULL, 'N', 'Y') AS status_inspect,
if(d.tot_form = d.tot_done, 'Y','N') as status_pdf
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
        select id_item, id_jo, no_invoice,
            COUNT(CASE WHEN status_proses_form = 'done' THEN 1 END) AS tot_done,
            COUNT(no_form) AS tot_form
        from signalbit_erp.qc_inspect_form a
        group by id_item, id_jo, no_invoice
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
    mi.itemdesc,
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
        $itemdesc = $get_header[0]->itemdesc;

        $cek_data = DB::connection('mysql_sb')->select("
    SELECT group_inspect, cek_inspect
    FROM qc_inspect_form
    WHERE id_item = ? AND id_jo = ? AND no_invoice = ?
    LIMIT 1
", [$id_item, $id_jo, $no_invoice]);

        // Set default as null if no data found
        $group_inspect = null;
        $cek_inspect = '0';

        if (!empty($cek_data)) {
            $group_inspect = $cek_data[0]->group_inspect;
            $cek_inspect = $cek_data[0]->cek_inspect;
        }


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
                "itemdesc" => $itemdesc,
                "data_group" => $data_group,
                "group_inspect" => $group_inspect,
                "cek_inspect" => $cek_inspect
            ]
        );
    }

    public function show_calculate_qc_inspect(Request $request)
    {
        $user = Auth::user()->name;

        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_inv = $request->no_inv;
        $cek_inspect = $request->cek_inspect ?? 0;
        $cbo_group_def = $request->cbo_group_def;

        $get_data_inspect_group = DB::connection('mysql_sb')->select("SELECT
        shipment
        from qc_inspect_master_group_inspect where id = '$cbo_group_def'");

        $max_shipment = !empty($get_data_inspect_group) ? $get_data_inspect_group[0]->shipment : '0';
        $data_input = DB::connection('mysql_sb')->select("WITH a AS (
    SELECT
        b.no_form,
				b.no_lot,
				id_item,
				id_jo,
				no_invoice,
        SUM(up_to_3) * 1 AS sum_up_to_3,
        SUM(`3_6`) * 2 AS sum_3_6,
        SUM(`6_9`) * 3 AS sum_6_9,
        SUM(over_9) * 4 AS sum_over_9,
				status_proses_form,
				c.individu,
				b.group_inspect,
				shipment,
				cek_inspect,
				proses,
				pass_with_condition
			FROM	qc_inspect_form b
			left JOIN qc_inspect_form_det a ON b.no_form = a.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv'
group by no_form, no_lot
),
b AS (
    SELECT
        a.no_form,
				b.no_lot,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
			FROM	qc_inspect_form b
			left JOIN qc_inspect_form_det a ON b.no_form = a.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv'
      AND cuttable_width_act > 0
    GROUP BY a.no_form, b.act_length_fix
),
c AS (
    SELECT
        a.no_form,
				id_item,
				id_jo,
				no_invoice,
				a.no_lot,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
        (sum_up_to_3 + sum_3_6 + sum_6_9 + sum_over_9) AS tot_point,
				individu,
				group_inspect,
				shipment,
				cek_inspect,
				proses,
				pass_with_condition,
				status_proses_form
    FROM a
		group by no_form, no_lot
),
d AS (
				SELECT
				COUNT(c.no_form) AS tot_form,
				COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END) AS tot_form_done,
				c.no_lot,
				id_item,
				id_jo,
				no_invoice,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
				c.tot_point,
				(SUM((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) / COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END)) AS act_point,
				round((SUM((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix)))) / COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END)) avg_act_point,
				individu,
				if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= individu,'PASS','REJECT') result,
				group_inspect,
				shipment,
				cek_inspect,
				max(proses) proses,
				max(pass_with_condition)pass_with_condition
FROM c
left JOIN b ON c.no_form = b.no_form
        GROUP BY
            no_lot,
            id_item,
            id_jo,
            no_invoice,
            group_inspect,
            shipment,
            cek_inspect
)

select
b.id_item,
b.id_jo,
a.no_invoice,
c.no_lot,
d.group_inspect,
count(no_roll) jml_roll,
(CEIL(count(no_roll) * ($cek_inspect /100)) * d.proses) jml_roll_cek,
if(d.tot_form is null, '0', d.tot_form) tot_form,
if(d.tot_form_done is null, '0', d.tot_form_done) tot_form_done,
IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect * proses, ' %')) AS cek_inspect,
CONCAT('Inspect Ke ', IF(d.proses IS NULL, 1, d.proses)) AS proses,
IF(d.proses IS NULL, 1, d.proses) AS proses_int,
IF(d.shipment IS NULL, $max_shipment, d.shipment) max_shipment,
d.avg_act_point shipment_point,
CASE
    WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point <= d.shipment THEN 'PASS'
    WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment and pass_with_condition = 'N' THEN 'REJECT'
	WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment and pass_with_condition = 'Y' THEN 'PASS WITH CONDITION'
    WHEN if(d.tot_form is null, '0', d.tot_form) <  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point <= d.shipment THEN '-'
	WHEN if(d.tot_form is null, '0', d.tot_form) > if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment THEN '-'
	WHEN if(d.tot_form is null, '0', d.tot_form) > if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point < d.shipment THEN '-'
    END AS result,
CASE
    WHEN proses = '2' AND pass_with_condition = 'N' AND  (
        CASE
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) = IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point <= d.shipment THEN 'PASS'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) = IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point > d.shipment THEN 'REJECT'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) < IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point <= d.shipment THEN '-'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) > IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point > d.shipment THEN '-'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) > IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point < d.shipment THEN '-'
        END = 'REJECT'
    ) THEN 'Y'
    ELSE 'N'
END AS stat_reject,
    IF(d.no_lot IS NULL, 'N', 'Y') AS status_lot,
				CASE
					WHEN if(d.tot_form is null, '0', d.tot_form) = if(d.tot_form_done is null, '0', d.tot_form_done) and d.proses = '1' and
						CASE
                        WHEN d.shipment IS NULL AND d.avg_act_point IS NULL THEN '-'
						WHEN d.avg_act_point IS NULL THEN '-'
                        WHEN d.avg_act_point <= d.shipment THEN 'PASS'
                        ELSE 'REJECT'
                  END = 'REJECT' THEN 'Y'
						ELSE 'N'
		END as gen_more,
        IF(photo IS NULL, 'N', 'Y') AS photo
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
left join d ON c.no_lot = d.no_lot
AND c.id_item = d.id_item
AND c.id_jo = d.id_jo
AND a.no_invoice = d.no_invoice
left join signalbit_erp.qc_inspect_form_blanket e on
c.no_lot = e.no_lot
AND c.id_item = e.id_item
AND c.id_jo = e.id_jo
AND a.no_invoice = e.no_invoice
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

        $data = DB::connection('mysql_sb')->select("
        SELECT
            c.no_lot,
            COUNT(no_roll) AS jml_roll,
            CEIL(COUNT(no_roll) * ($cek_inspect / 100)) AS jml_roll_cek,
            IF(d.tot_form IS NULL, '0', d.tot_form) AS tot_form,
            IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect, ' %')) AS cek_inspect,
            CONCAT('Inspect Ke ', IF(d.proses IS NULL, 1, d.proses)) AS proses,
            IF(d.proses IS NULL, 1, d.proses) AS proses_int,
            IF(d.shipment IS NULL, $max_shipment, d.shipment) AS max_shipment,
            '0' AS shipment_point,
            '-' AS result,
            IF(d.no_lot IS NULL, 'N', 'Y') AS status_lot
        FROM signalbit_erp.whs_inmaterial_fabric a
        INNER JOIN signalbit_erp.whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok
        LEFT JOIN signalbit_erp.whs_lokasi_inmaterial c ON a.no_dok = c.no_dok AND b.id_item = c.id_item AND b.id_jo = c.id_jo
        LEFT JOIN (
            SELECT no_lot, id_item, id_jo, no_invoice, MAX(proses) AS proses, a.group_inspect, MAX(shipment) AS shipment, MAX(cek_inspect) AS cek_inspect, COUNT(no_lot) AS tot_form
            FROM signalbit_erp.qc_inspect_form a
            INNER JOIN signalbit_erp.qc_inspect_master_group_inspect b ON a.group_inspect = b.id
            WHERE a.id_item = ? AND a.id_jo = ? AND a.no_invoice = ?
            GROUP BY no_lot, id_item, id_jo, no_invoice
        ) d ON c.no_lot = d.no_lot AND c.id_item = d.id_item AND c.id_jo = d.id_jo AND a.no_invoice = d.no_invoice
        WHERE c.id_item = ? AND c.id_jo = ? AND a.no_invoice = ? AND IF(d.no_lot IS NULL, 'N', 'Y') = 'N'
        GROUP BY c.no_lot
    ", [$id_item, $id_jo, $no_inv, $id_item, $id_jo, $no_inv]);

        $lot_summary = [];
        $generated_forms = [];
        $total_generated = 0;

        // Prepare date values
        $datePrefix = $timestamp->format('dmy'); // DDMMYY format
        $month = $timestamp->format('m');
        $year = $timestamp->format('Y');
        $currentDate = $timestamp->format('Y-m-d');

        $get_last_number = DB::connection('mysql_sb')->select("
        SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
        FROM qc_inspect_form
        WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
    ", [$month, $year]);

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
                    'status_proses_form'    => 'draft',
                    'pass_with_condition'   => 'N',
                    'created_by'            => $user,
                    'created_at'            => $timestamp,
                    'updated_at'            => $timestamp,
                ]);

                $generated_forms[] = [
                    'no_form' => $no_form,
                    'no_lot' => $row->no_lot,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data sudah di Generate',
            'summary' => $lot_summary,
            'total_generated_forms' => $total_generated,
            'generated_forms' => $generated_forms,
            'data' => [
                'id_item' => $id_item,
                'id_jo' => $id_jo,
                'no_inv' => $no_inv,
            ]
        ]);
    }

    public function show_qc_inspect_form_modal(Request $request)
    {
        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_invoice = $request->no_invoice;
        $no_lot = $request->no_lot;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
qc.id,
qc.tgl_form,
DATE_FORMAT(qc.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
qc.no_mesin,
qc.no_form,
qc.id_item,
a.itemdesc,
a.supplier,
qc.no_invoice,
a.buyer,
a.kpno,
a.styleno,
a.color,
qc.group_inspect,
qc.no_lot,
a.type_pch,
qc.proses,
qc.barcode,
b.no_roll,
CONCAT(
    ROUND(IFNULL(d.act_point, 0)),
    '/',
    IFNULL(c.individu, 0)
) AS point_max_point,
qc.result,
qc.status_proses_form
from signalbit_erp.qc_inspect_form  qc
inner join
(
select a.no_invoice, c.id_item, mi.itemdesc,c.id_jo, mi.color,a.supplier, ms.supplier buyer, ac.kpno, ac.styleno, a.type_pch
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
inner join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by c.id_item, c.id_jo, a.no_invoice
) a on qc.id_item = a.id_item and qc.id_jo = a.id_jo and qc.no_invoice = a.no_invoice
left join signalbit_erp.whs_lokasi_inmaterial b on qc.barcode = b.no_barcode
left join signalbit_erp.qc_inspect_master_group_inspect c on qc.group_inspect = c.id
left join
(
SELECT
a.no_form,
ROUND(
    (
        (
            SUM(up_to_3) * 1 +
            SUM(`3_6`) * 2 +
            SUM(`6_9`) * 3 +
            SUM(over_9) * 4
        ) * 36 * 100
    ) / (
        AVG(a.cuttable_width_act) *
        AVG(
            CASE
                WHEN b.unit_act_length = 'meter' THEN b.act_length / 0.9144
                ELSE b.act_length
            END
        )
    )
) AS act_point
FROM qc_inspect_form_det a
INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
INNER JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_invoice' and no_lot = '$no_lot'
group by no_form
)
d on qc.no_form = d.no_form
where qc.id_item = '$id_item' and qc.id_jo = '$id_jo' and qc.no_invoice = '$no_invoice' and qc.no_lot = '$no_lot'
order by no_form asc, tgl_form desc, color asc
            ");

            return DataTables::of($data_input)->toJson();
        }
    }

    public function generate_form_kedua(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_inv = $request->no_invoice;
        $no_lot = $request->no_lot;
        $cek_inspect = $request->cek_inspect;
        $group_inspect = $request->group_inspect;
        $tot_form = (int) $request->tot_form;

        // Prepare date values
        $datePrefix = $timestamp->format('dmy'); // e.g., 230725
        $month = $timestamp->format('m');
        $year = $timestamp->format('Y');
        $currentDate = $timestamp->format('Y-m-d');

        // Get the last form number of the current month/year
        $get_last_number = DB::connection('mysql_sb')->select("
        SELECT MAX(CAST(SUBSTRING_INDEX(no_form, '/', -1) AS UNSIGNED)) AS last_number
        FROM qc_inspect_form
        WHERE MONTH(tgl_form) = ? AND YEAR(tgl_form) = ?
    ", [$month, $year]);

        $last_number = $get_last_number[0]->last_number ?? 0;
        $formCounter = $last_number + 1;

        $generated_forms = [];

        for ($i = 0; $i < $tot_form; $i++) {
            $no_form = 'INS/' . $datePrefix . '/' . $formCounter++;

            DB::connection('mysql_sb')->table('qc_inspect_form')->insert([
                'no_form'               => $no_form,
                'tgl_form'              => $currentDate,
                'no_lot'                => $no_lot,
                'no_invoice'            => $no_inv,
                'id_item'               => $id_item,
                'id_jo'                 => $id_jo,
                'group_inspect'         => $group_inspect,
                'cek_inspect'           => $cek_inspect,
                'proses'                => '2',
                'status_proses_form'    => 'draft',
                'pass_with_condition'   => 'N',
                'created_by'            => $user,
                'created_at'            => $timestamp,
                'updated_at'            => $timestamp,
            ]);

            $generated_forms[] = [
                'no_form' => $no_form,
                'no_lot' => $no_lot,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Form kedua berhasil digenerate.',
            'total_generated_forms' => $tot_form,
            'generated_forms' => $generated_forms,
            'data' => [
                'id_item' => $id_item,
                'id_jo' => $id_jo,
                'no_inv' => $no_inv,
                'no_lot' => $no_lot,
            ]
        ]);
    }


    public function show_inspect_pertama(Request $request)
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
        $data_input = DB::connection('mysql_sb')->select("WITH a AS (
    SELECT
        b.no_form,
				b.no_lot,
				id_item,
				id_jo,
				no_invoice,
        SUM(up_to_3) * 1 AS sum_up_to_3,
        SUM(`3_6`) * 2 AS sum_3_6,
        SUM(`6_9`) * 3 AS sum_6_9,
        SUM(over_9) * 4 AS sum_over_9,
				status_proses_form,
				c.individu,
				b.group_inspect,
				shipment,
				cek_inspect,
				proses,
				pass_with_condition
			FROM	qc_inspect_form b
			left JOIN qc_inspect_form_det a ON b.no_form = a.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv' and proses = '1'
group by no_form, no_lot
),
b AS (
    SELECT
        a.no_form,
				b.no_lot,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
			FROM	qc_inspect_form b
			left JOIN qc_inspect_form_det a ON b.no_form = a.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv' and proses = '1'
      AND cuttable_width_act > 0
    GROUP BY a.no_form, b.act_length_fix
),
c AS (
    SELECT
        a.no_form,
				id_item,
				id_jo,
				no_invoice,
				a.no_lot,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
        (sum_up_to_3 + sum_3_6 + sum_6_9 + sum_over_9) AS tot_point,
				individu,
				group_inspect,
				shipment,
				cek_inspect,
				proses,
				pass_with_condition,
				status_proses_form
    FROM a
		group by no_form, no_lot
),
d AS (
				SELECT
				COUNT(c.no_form) AS tot_form,
				COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END) AS tot_form_done,
				c.no_lot,
				id_item,
				id_jo,
				no_invoice,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
				c.tot_point,
				(SUM((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) / COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END)) AS act_point,
				round((SUM((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix)))) / COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END)) avg_act_point,
				individu,
				if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= individu,'PASS','REJECT') result,
				group_inspect,
				shipment,
				cek_inspect,
				max(proses) proses,
				max(pass_with_condition)pass_with_condition
FROM c
left JOIN b ON c.no_form = b.no_form
        GROUP BY
            no_lot,
            id_item,
            id_jo,
            no_invoice,
            group_inspect,
            shipment,
            cek_inspect
)

select
b.id_item,
b.id_jo,
a.no_invoice,
c.no_lot,
d.group_inspect,
count(no_roll) jml_roll,
CEIL(count(no_roll) * ($cek_inspect /100)) jml_roll_cek,
if(d.tot_form is null, '0', d.tot_form) tot_form,
if(d.tot_form_done is null, '0', d.tot_form_done) tot_form_done,
IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect * proses, ' %')) AS cek_inspect,
CONCAT('Inspect Ke ', IF(d.proses IS NULL, 1, d.proses)) AS proses,
IF(d.proses IS NULL, 1, d.proses) AS proses_int,
IF(d.shipment IS NULL, $max_shipment, d.shipment) max_shipment,
d.avg_act_point shipment_point,
CASE
    WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point <= d.shipment THEN 'PASS'
    WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment and pass_with_condition = 'N' THEN 'REJECT'
	WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment and pass_with_condition = 'Y' THEN 'PASS WITH CONDITION'
    WHEN if(d.tot_form is null, '0', d.tot_form) <  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point <= d.shipment THEN '-'
	WHEN if(d.tot_form is null, '0', d.tot_form) > if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment THEN '-'
	WHEN if(d.tot_form is null, '0', d.tot_form) > if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point < d.shipment THEN '-'
    END AS result,
CASE
    WHEN proses = '2' AND pass_with_condition = 'N' AND  (
        CASE
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) = IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point <= d.shipment THEN 'PASS'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) = IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point > d.shipment THEN 'REJECT'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) < IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point <= d.shipment THEN '-'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) > IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point > d.shipment THEN '-'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) > IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point < d.shipment THEN '-'
        END = 'REJECT'
    ) THEN 'Y'
    ELSE 'N'
END AS stat_reject,
    IF(d.no_lot IS NULL, 'N', 'Y') AS status_lot,
				CASE
					WHEN if(d.tot_form is null, '0', d.tot_form) = if(d.tot_form_done is null, '0', d.tot_form_done) and d.proses = '1' and
						CASE
                        WHEN d.shipment IS NULL AND d.avg_act_point IS NULL THEN '-'
						WHEN d.avg_act_point IS NULL THEN '-'
                        WHEN d.avg_act_point <= d.shipment THEN 'PASS'
                        ELSE 'REJECT'
                  END = 'REJECT' THEN 'Y'
						ELSE 'N'
		END as gen_more
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
left join d ON c.no_lot = d.no_lot
AND c.id_item = d.id_item
AND c.id_jo = d.id_jo
AND a.no_invoice = d.no_invoice
where c.id_item = '$id_item' and c.id_jo = '$id_jo' and a.no_invoice = '$no_inv' and proses = '1'
group by c.no_lot
            ");
        return DataTables::of($data_input)->toJson();
    }


    public function show_inspect_kedua(Request $request)
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
        $data_input = DB::connection('mysql_sb')->select("WITH a AS (
    SELECT
        b.no_form,
				b.no_lot,
				id_item,
				id_jo,
				no_invoice,
        SUM(up_to_3) * 1 AS sum_up_to_3,
        SUM(`3_6`) * 2 AS sum_3_6,
        SUM(`6_9`) * 3 AS sum_6_9,
        SUM(over_9) * 4 AS sum_over_9,
				status_proses_form,
				c.individu,
				b.group_inspect,
				shipment,
				cek_inspect,
				proses,
				pass_with_condition
			FROM	qc_inspect_form b
			left JOIN qc_inspect_form_det a ON b.no_form = a.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv' and proses = '2'
group by no_form, no_lot
),
b AS (
    SELECT
        a.no_form,
				b.no_lot,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
			FROM	qc_inspect_form b
			left JOIN qc_inspect_form_det a ON b.no_form = a.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv' and proses = '2'
      AND cuttable_width_act > 0
    GROUP BY a.no_form, b.act_length_fix
),
c AS (
    SELECT
        a.no_form,
				id_item,
				id_jo,
				no_invoice,
				a.no_lot,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
        (sum_up_to_3 + sum_3_6 + sum_6_9 + sum_over_9) AS tot_point,
				individu,
				group_inspect,
				shipment,
				cek_inspect,
				proses,
				pass_with_condition,
				status_proses_form
    FROM a
		group by no_form, no_lot
),
d AS (
				SELECT
				COUNT(c.no_form) AS tot_form,
				COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END) AS tot_form_done,
				c.no_lot,
				id_item,
				id_jo,
				no_invoice,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
				c.tot_point,
				(SUM((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) / COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END)) AS act_point,
				round((SUM((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix)))) / COUNT(DISTINCT CASE WHEN status_proses_form = 'done' THEN b.no_form END)) avg_act_point,
				individu,
				if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= individu,'PASS','REJECT') result,
				group_inspect,
				shipment,
				cek_inspect,
				max(proses) proses,
				max(pass_with_condition)pass_with_condition
FROM c
left JOIN b ON c.no_form = b.no_form
        GROUP BY
            no_lot,
            id_item,
            id_jo,
            no_invoice,
            group_inspect,
            shipment,
            cek_inspect
)

select
b.id_item,
b.id_jo,
a.no_invoice,
c.no_lot,
d.group_inspect,
count(no_roll) jml_roll,
CEIL(count(no_roll) * ($cek_inspect /100)) jml_roll_cek,
if(d.tot_form is null, '0', d.tot_form) tot_form,
if(d.tot_form_done is null, '0', d.tot_form_done) tot_form_done,
IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect * proses, ' %')) AS cek_inspect,
CONCAT('Inspect Ke ', IF(d.proses IS NULL, 1, d.proses)) AS proses,
IF(d.proses IS NULL, 1, d.proses) AS proses_int,
IF(d.shipment IS NULL, $max_shipment, d.shipment) max_shipment,
d.avg_act_point shipment_point,
CASE
    WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point <= d.shipment THEN 'PASS'
    WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment and pass_with_condition = 'N' THEN 'REJECT'
	WHEN if(d.tot_form is null, '0', d.tot_form) =  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment and pass_with_condition = 'Y' THEN 'PASS WITH CONDITION'
    WHEN if(d.tot_form is null, '0', d.tot_form) <  if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point <= d.shipment THEN '-'
	WHEN if(d.tot_form is null, '0', d.tot_form) > if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point > d.shipment THEN '-'
	WHEN if(d.tot_form is null, '0', d.tot_form) > if(d.tot_form_done is null, '0', d.tot_form_done) and d.avg_act_point < d.shipment THEN '-'
    END AS result,
CASE
    WHEN proses = '2' AND pass_with_condition = 'N' AND  (
        CASE
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) = IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point <= d.shipment THEN 'PASS'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) = IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point > d.shipment THEN 'REJECT'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) < IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point <= d.shipment THEN '-'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) > IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point > d.shipment THEN '-'
            WHEN IF(d.tot_form IS NULL, '0', d.tot_form) > IF(d.tot_form_done IS NULL, '0', d.tot_form_done) AND d.avg_act_point < d.shipment THEN '-'
        END = 'REJECT'
    ) THEN 'Y'
    ELSE 'N'
END AS stat_reject,
    IF(d.no_lot IS NULL, 'N', 'Y') AS status_lot,
				CASE
					WHEN if(d.tot_form is null, '0', d.tot_form) = if(d.tot_form_done is null, '0', d.tot_form_done) and d.proses = '1' and
						CASE
                        WHEN d.shipment IS NULL AND d.avg_act_point IS NULL THEN '-'
						WHEN d.avg_act_point IS NULL THEN '-'
                        WHEN d.avg_act_point <= d.shipment THEN 'PASS'
                        ELSE 'REJECT'
                  END = 'REJECT' THEN 'Y'
						ELSE 'N'
		END as gen_more
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
left join d ON c.no_lot = d.no_lot
AND c.id_item = d.id_item
AND c.id_jo = d.id_jo
AND a.no_invoice = d.no_invoice
where c.id_item = '$id_item' and c.id_jo = '$id_jo' and a.no_invoice = '$no_inv' and proses = '2'
group by c.no_lot
            ");
        return DataTables::of($data_input)->toJson();
    }

    public function pass_with_condition(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_inv = $request->no_invoice;
        $no_lot = $request->no_lot;

        // Perform the update and get the number of affected rows
        $total_updated = DB::connection('mysql_sb')->table('qc_inspect_form')
            ->where('id_item', $id_item)
            ->where('id_jo', $id_jo)
            ->where('no_invoice', $no_inv)
            ->where('no_lot', $no_lot)
            ->where('proses', 2)
            ->update([
                'pass_with_condition' => 'Y',
                'updated_at' => $timestamp
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Form berhasil diupdate.',
            'total_updated_forms' => $total_updated
        ]);
    }


    public function export_qc_inspect($id_lok_in_material)
    {
        // Fetch header data using raw SQL query
        $data_header = DB::connection('mysql_sb')->select("
        SELECT
            a.tgl_dok,
            DATE_FORMAT(a.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
            a.no_dok,
            a.supplier,
            ms.supplier buyer,
            ac.styleno,
            no_invoice,
            b.id_item,
            b.id_jo,
            COUNT(no_roll) AS jml_roll,
            COUNT(DISTINCT no_lot) AS jml_lot,
            mi.color,
            mi.itemdesc,
            a.type_pch
        FROM signalbit_erp.whs_inmaterial_fabric a
        INNER JOIN signalbit_erp.whs_inmaterial_fabric_det b
            ON a.no_dok = b.no_dok
        LEFT JOIN signalbit_erp.whs_lokasi_inmaterial c
            ON a.no_dok = c.no_dok
            AND b.id_item = c.id_item
            AND b.id_jo = c.id_jo
        INNER JOIN (
            SELECT no_dok, id_item, id_jo
            FROM signalbit_erp.whs_lokasi_inmaterial
            WHERE id = ?
        ) d
            ON a.no_dok = d.no_dok
            AND b.id_item = d.id_item
            AND b.id_jo = d.id_jo
        INNER JOIN signalbit_erp.masteritem mi
            ON b.id_item = mi.id_item
        INNER JOIN signalbit_erp.jo_det jd
            ON b.id_jo = jd.id_jo
        INNER JOIN signalbit_erp.so so
            ON jd.id_so = so.id
        INNER JOIN signalbit_erp.act_costing ac
            ON so.id_cost = ac.id
        INNER JOIN signalbit_erp.mastersupplier ms
            ON ac.id_buyer = ms.Id_Supplier
        GROUP BY a.tgl_dok, a.no_dok, b.id_item, b.id_jo, b.unit
    ", [$id_lok_in_material]); // Use parameter binding for safety

        $id_item = $data_header[0]->id_item;
        $id_jo = $data_header[0]->id_jo;
        $no_inv  = $data_header[0]->no_invoice;

        $data_cek_inspect = DB::connection('mysql_sb')->select("SELECT
cek_inspect, group_inspect
from qc_inspect_form a
where no_invoice = '$no_inv' and a.id_item = '$id_item' and a.id_jo = '$id_jo' limit 1
");
        $cek_inspect = $data_cek_inspect[0]->cek_inspect;
        $group_inspect = $data_cek_inspect[0]->group_inspect;

        $data_lot_report = DB::connection('mysql_sb')->select("WITH a AS (
    SELECT
				b.no_lot,
        a.no_form,
        SUM(up_to_3) * 1 AS sum_up_to_3,
        SUM(`3_6`) * 2 AS sum_3_6,
        SUM(`6_9`) * 3 AS sum_6_9,
        SUM(over_9) * 4 AS sum_over_9,
				c.shipment,
				b.pass_with_condition
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
    WHERE id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv'
    group by a.no_form
),
b AS (
    SELECT
        a.no_form,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
    WHERE id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv'
      AND cuttable_width_act > 0
    GROUP BY a.no_form, b.act_length_fix
),
c AS (
    SELECT
				a.no_lot,
        a.no_form,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
        (sum_up_to_3 + sum_3_6 + sum_6_9 + sum_over_9) AS tot_point,
				shipment,
				a.pass_with_condition
    FROM a
    group by a.no_form
),
d AS (
SELECT
c.no_form,
c.no_lot,
sum_up_to_3,
sum_3_6,
sum_6_9,
sum_over_9,
avg_width,
c.tot_point,
round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) AS act_point,
shipment,
if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= shipment,'PASS','REJECT') result,
c.pass_with_condition
FROM c
INNER JOIN b ON c.no_form = b.no_form
GROUP BY c.no_form
)

SELECT
no_lot,
count(no_form) tot_form,
ROUND(SUM(act_point) / count(no_form)) act_point_total,
shipment,
max(pass_with_condition) pass_with_condition,
CASE
		WHEN ROUND(SUM(act_point) / count(no_form)) >= shipment and pass_with_condition = 'N' then 'REJECT'
		WHEN ROUND(SUM(act_point) / count(no_form)) >= shipment and pass_with_condition = 'Y' then 'PASS WITH CONDITION'
		WHEN ROUND(SUM(act_point) / count(no_form)) <= shipment and pass_with_condition = 'N' then 'PASS'
END as result
FROM d
GROUP BY no_lot");



        $data_header_form = DB::connection('mysql_sb')->select("SELECT
a.no_form,
a.tgl_form,
a.created_by,
a.operator,
a.barcode,
no_roll,
concat(a.weight, ' ', act_unit_weight) weight,
a.width,
gramage,
proses,
a.no_lot,
concat(bintex_length, ' ', upper(unit_bintex)) bintex,
concat(act_length_fix, ' ', upper(unit_act_length)) length
from qc_inspect_form a
left join whs_lokasi_inmaterial b on a.barcode = b.no_barcode
where no_invoice = '$no_inv' and a.id_item = '$id_item' and a.id_jo = '$id_jo'
order by a.no_lot asc, no_form asc
");

        $form_numbers = collect($data_header_form)->pluck('no_form')->unique()->toArray();

        $visual_inspection = [];
        if (!empty($form_numbers)) {
            $visual_inspection = DB::connection('mysql_sb')->select("
        SELECT
            a.no_form,
            CONCAT(b.from, ' - ', b.to) AS length,
            c.critical_defect AS defect_name,
            a.up_to_3,
            a.3_6,
            a.6_9,
            a.over_9,
            CONCAT(a.full_width_act, ' -> ', a.cuttable_width_act) AS width
        FROM qc_inspect_form_det a
        INNER JOIN qc_inspect_master_lenght b ON a.id_length = b.id
        INNER JOIN qc_inspect_master_defect c ON a.id_defect = c.id
        WHERE a.no_form IN (" . implode(',', array_fill(0, count($form_numbers), '?')) . ")
    ", $form_numbers);

            // Group the result by no_form
            $inspection_results_grouped = collect($visual_inspection)->groupBy('no_form');
        }

        $data_summary = [];
        if (!empty($form_numbers)) {
            $data_summary = DB::connection('mysql_sb')->select("
WITH a AS (
    SELECT
        a.no_form,
        SUM(up_to_3) * 1 AS sum_up_to_3,
        SUM(`3_6`) * 2 AS sum_3_6,
        SUM(`6_9`) * 3 AS sum_6_9,
        SUM(over_9) * 4 AS sum_over_9,
				c.individu
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
    WHERE a.no_form IN (" . implode(',', array_fill(0, count($form_numbers), '?')) . ")
    group by a.no_form
),
b AS (
    SELECT
        a.no_form,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
    WHERE a.no_form IN (" . implode(',', array_fill(0, count($form_numbers), '?')) . ")
      AND cuttable_width_act > 0
    GROUP BY a.no_form, b.act_length_fix
),
c AS (
    SELECT
        a.no_form,
				sum_up_to_3,
				sum_3_6,
				sum_6_9,
				sum_over_9,
        (sum_up_to_3 + sum_3_6 + sum_6_9 + sum_over_9) AS tot_point,
				individu
    FROM a
    group by a.no_form
)

SELECT
c.no_form,
sum_up_to_3,
sum_3_6,
sum_6_9,
sum_over_9,
avg_width,
c.tot_point,
round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) AS act_point,
individu,
if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= individu,'PASS','REJECT') result
FROM c
INNER JOIN b ON c.no_form = b.no_form
GROUP BY c.no_form
", array_merge($form_numbers, $form_numbers));

            // Group the result by no_form
            $data_summary_grouped = collect($data_summary)->groupBy('no_form');
        }



        // Generate PDF from the view
        $pdf = PDF::loadView('qc_inspect.pdf_qc_inspect', [
            'data_header' => $data_header,
            'data_header_form' => $data_header_form,
            'inspection_results_grouped' => $inspection_results_grouped,
            'data_summary_grouped' => $data_summary_grouped,
            'cek_inspect' => $cek_inspect,
            'group_inspect' => $group_inspect,
            'data_lot_report' => $data_lot_report,
        ])->setPaper('a4', 'portrait');

        // Set filename and return download
        $fileName = 'pdf.pdf';
        return $pdf->download(str_replace("/", "_", $fileName));
    }

    public function upload_blanket_photo(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
            'id_item' => 'required',
            'id_jo' => 'required',
            'no_invoice' => 'required',
            'no_lot' => 'required',
        ]);

        // Clean filename parts
        $id_item = str_replace(['/', '\\'], '_', $request->id_item);
        $id_jo = str_replace(['/', '\\'], '_', $request->id_jo);
        $no_invoice = str_replace(['/', '\\'], '_', $request->no_invoice);
        $no_lot = str_replace(['/', '\\'], '_', $request->no_lot);

        $filename = "{$id_item}_{$id_jo}_{$no_invoice}_{$no_lot}.jpg";

        $request->file('photo')->storeAs('public/gambar_blanket', $filename);

        $cek_blanket = DB::connection('mysql_sb')->select("SELECT
*
        from qc_inspect_form_blanket a
        where no_invoice = '$request->no_invoice' and a.id_item = '$request->id_item'
        and a.id_jo = '$request->id_jo' and a.no_lot = '$request->no_lot' limit 1
");

        if ($cek_blanket) {
            // UPDATE using raw SQL
            DB::connection('mysql_sb')->statement("
            UPDATE qc_inspect_form_blanket
            SET photo = ?, rate = ?, updated_at = NOW()
            WHERE no_invoice = ? AND id_item = ? AND id_jo = ? AND no_lot = ?
        ", [
                $filename,
                $request->rate,
                $request->no_invoice,
                $request->id_item,
                $request->id_jo,
                $request->no_lot
            ]);
        } else {
            // INSERT using raw SQL
            DB::connection('mysql_sb')->statement("
            INSERT INTO qc_inspect_form_blanket
            (id_item, id_jo, no_invoice, no_lot, photo, rate, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?,?, NOW(), NOW())
        ", [
                $request->id_item,
                $request->id_jo,
                $request->no_invoice,
                $request->no_lot,
                $filename,
                $request->rateSelect
            ]);
        }

        return response()->json(['message' => 'Upload successful']);
    }
}
