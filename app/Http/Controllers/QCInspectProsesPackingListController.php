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
        $cek_inspect = null;

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
        $data_input = DB::connection('mysql_sb')->select("WITH d as (
               SELECT
				COUNT(DISTINCT a.no_form) AS tot_form,
				COUNT(DISTINCT CASE WHEN a.status_proses_form = 'done' THEN a.no_form END) AS tot_form_done,
				        CEIL(
        ROUND(
            (
            (
                SUM(up_to_3) * 1 +
                SUM(`3_6`) * 2 +
                SUM(`6_9`) * 3 +
                SUM(over_9) * 4
            ) * 36 * 100
            ) / (
            AVG(b.cuttable_width_act) *
            AVG(
                CASE
                WHEN a.unit_act_length = 'meter' THEN a.act_length / 0.9144
                ELSE a.act_length
                END
            ) * COUNT(DISTINCT b.no_form)
            ),
            2
        )
        ) AS avg_act_point,
				no_lot,
        id_item,
        id_jo,
        no_invoice,
        max(proses) proses,
        a.group_inspect,
        shipment,
        cek_inspect,
        a.pass_with_condition
		FROM qc_inspect_form a
        left JOIN qc_inspect_form_det b ON a.no_form = b.no_form
        INNER JOIN qc_inspect_master_group_inspect c ON a.group_inspect = c.id
        where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv'
        GROUP BY
            a.no_lot,
            a.id_item,
            a.id_jo,
            a.no_invoice,
            a.group_inspect,
            c.shipment,
            a.cek_inspect,
            pass_with_condition

)

select
d.id_item,
d.id_jo,
d.no_invoice,
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
        $data_input = DB::connection('mysql_sb')->select("WITH d as (
               SELECT
				COUNT(DISTINCT a.no_form) AS tot_form,
				COUNT(DISTINCT CASE WHEN a.status_proses_form = 'done' THEN a.no_form END) AS tot_form_done,
				        CEIL(
        ROUND(
            (
            (
                SUM(up_to_3) * 1 +
                SUM(`3_6`) * 2 +
                SUM(`6_9`) * 3 +
                SUM(over_9) * 4
            ) * 36 * 100
            ) / (
            AVG(b.cuttable_width_act) *
            AVG(
                CASE
                WHEN a.unit_act_length = 'meter' THEN a.act_length / 0.9144
                ELSE a.act_length
                END
            ) * COUNT(DISTINCT b.no_form)
            ),
            2
        )
        ) AS avg_act_point,
				no_lot,
        id_item,
        id_jo,
        no_invoice,
        max(proses) proses,
        a.group_inspect,
        shipment,
        cek_inspect,
        pass_with_condition
				FROM qc_inspect_form a
        left JOIN qc_inspect_form_det b ON a.no_form = b.no_form
        INNER JOIN qc_inspect_master_group_inspect c ON a.group_inspect = c.id
        where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv' and proses = '1'
        GROUP BY
            a.no_lot,
            a.id_item,
            a.id_jo,
            a.no_invoice,
            a.group_inspect,
            c.shipment,
            a.cek_inspect,
            pass_with_condition
)

select
d.id_item,
d.id_jo,
d.no_invoice,
d.no_lot,
d.group_inspect,
count(no_roll) jml_roll,
CEIL(count(no_roll) * ($cek_inspect /100)) jml_roll_cek,
if(d.tot_form is null, '0', d.tot_form) tot_form,
if(d.tot_form_done is null, '0', d.tot_form_done) tot_form_done,
IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect, ' %')) AS cek_inspect,
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
        $data_input = DB::connection('mysql_sb')->select("WITH d as (
               SELECT
				COUNT(DISTINCT a.no_form) AS tot_form,
				COUNT(DISTINCT CASE WHEN a.status_proses_form = 'done' THEN a.no_form END) AS tot_form_done,
				        CEIL(
        ROUND(
            (
            (
                SUM(up_to_3) * 1 +
                SUM(`3_6`) * 2 +
                SUM(`6_9`) * 3 +
                SUM(over_9) * 4
            ) * 36 * 100
            ) / (
            AVG(b.cuttable_width_act) *
            AVG(
                CASE
                WHEN a.unit_act_length = 'meter' THEN a.act_length / 0.9144
                ELSE a.act_length
                END
            ) * COUNT(DISTINCT b.no_form)
            ),
            2
        )
        ) AS avg_act_point,
				no_lot,
        id_item,
        id_jo,
        no_invoice,
        max(proses) proses,
        a.group_inspect,
        shipment,
        cek_inspect,
        pass_with_condition
				FROM qc_inspect_form a
        left JOIN qc_inspect_form_det b ON a.no_form = b.no_form
        INNER JOIN qc_inspect_master_group_inspect c ON a.group_inspect = c.id
        where id_item = '$id_item' and id_jo = '$id_jo' and no_invoice = '$no_inv' and proses = '2'
        GROUP BY
            a.no_lot,
            a.id_item,
            a.id_jo,
            a.no_invoice,
            a.group_inspect,
            c.shipment,
            a.cek_inspect,
            pass_with_condition
)

select
d.id_item,
d.id_jo,
d.no_invoice,
d.no_lot,
d.group_inspect,
count(no_roll) jml_roll,
CEIL(count(no_roll) * ($cek_inspect /100)) jml_roll_cek,
if(d.tot_form is null, '0', d.tot_form) tot_form,
if(d.tot_form_done is null, '0', d.tot_form_done) tot_form_done,
IF(d.cek_inspect IS NULL, CONCAT($cek_inspect, ' %'), CONCAT(d.cek_inspect, ' %')) AS cek_inspect,
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


    public function export_qc_inspect($id)
    {
        // return Excel::download(new ExportLaporanQcpass($id), 'Laporan_qcpass.xlsx');
        $kode_insp = DB::connection('mysql_sb')->select("select no_insp from whs_qc_insp where id = '" . $id . "'");
        $data_header = DB::connection('mysql_sb')->select("select *,UPPER(fabric_name) fabricname from whs_qc_insp where id = '" . $id . "'");
        $data_detail = DB::connection('mysql_sb')->select("select b.id,b.no_lot,a.no_form,a.tgl_form,a.weight_fabric,width_fabric,gramage,a.no_roll,fabric_supp,a.inspektor,no_mesin,c.lenght_barcode, lenght_actual, catatan from whs_qc_insp_det a inner join whs_qc_insp b on b.no_insp = a.no_insp inner join whs_qc_insp_sum c on c.no_form = a.no_form where b.id = '" . $id . "' GROUP BY a.no_roll,a.no_form order by a.no_form asc");
        $data_temuan = DB::connection('mysql_sb')->select("select * from (select id,no_form,lenght_fabric,GROUP_CONCAT(kode_def) kode_def,GROUP_CONCAT(nama_defect) nama_defect,GROUP_CONCAT(ROUND(upto3,0)) upto3,GROUP_CONCAT(ROUND(over3,0)) over3,GROUP_CONCAT(ROUND(over6,0)) over6,GROUP_CONCAT(ROUND(over9,0)) over9,GROUP_CONCAT(width_det) width_det from (select DISTINCT a.id,b.no_form,lenght_fabric,kode_def,CONCAT('(',UPPER(c.nama_defect),')') nama_defect,upto3, over3, over6, over9,CONCAT(width_det1,'->',width_det2) width_det  from whs_qc_insp a inner join whs_qc_insp_det b on b.no_insp = a.no_insp left join whs_qc_insp_def c on c.kode = b.kode_def and c.no_form = b.no_form and c.lenght = b.lenght_fabric where a.id = '" . $id . "') a GROUP BY lenght_fabric,no_form order by no_form asc, lenght_fabric asc) a left join (select id id_pil,nama_pilihan from whs_master_pilihan where type_pilihan = 'Lenght_qc_pass' and status = 'Active') b on b.nama_pilihan = a.lenght_fabric order by no_form asc,id_pil asc");
        $data_sum = DB::connection('mysql_sb')->select("select no_form,upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*,b.*,c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '" . $id . "'");
        $avg_poin = DB::connection('mysql_sb')->select("select ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) avg_poin,IF(ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) > 15,'-','PASS') status from (select sum(ttl_poin) ttl_poin, COUNT(width_fabric)ttl_width, SUM(width_fabric) akt_width, SUM(l_actual) akt_lenght from (select upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*, b.*, c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '" . $id . "') a) a");

        // PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('qc_inspect.pdf_qc_inspect', ['kode_insp' => $kode_insp, 'data_header' => $data_header, 'data_detail' => $data_detail, 'data_temuan' => $data_temuan, 'data_sum' => $data_sum, 'avg_poin' => $avg_poin])->setPaper('a4', 'potrait');

        // $pdf = PDF::loadView('master.pdf.print-lokasi', ["dataLokasi" => $dataLokasi]);

        $fileName = 'pdf.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }
}
