<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\export_excel_qc_inspect_roll;
use App\Exports\export_excel_qc_inspect_lot;
use Illuminate\Support\Facades\Storage;
use PDF;


class QCInspectLaporanController extends Controller
{
    public function qc_inspect_laporan_roll(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        $data_input = DB::connection('mysql_sb')->select("WITH qc as (
select
*
from qc_inspect_form
where date(finish_form) >= '$tgl_awal' and date(finish_form) <= '$tgl_akhir'),
pd as (
SELECT
    a.no_form,
    SUM(up_to_3) * 1 +
    SUM(`3_6`) * 2 +
    SUM(`6_9`) * 3 +
    SUM(over_9) * 4 AS sum_point_def
FROM
    qc_inspect_form_det a
INNER JOIN qc_inspect_master_defect b on a.id_defect = b.id
left JOIN qc_inspect_form c on a.no_form = c.no_form
where date(finish_form) >= '$tgl_awal' and date(finish_form) <= '$tgl_akhir'
GROUP BY
     a.no_form
),
pos as (
SELECT
    no_form,
    ROUND(COALESCE(MAX(CASE WHEN urut = 1 THEN cuttable_width_act END), 0),6) AS front,
    ROUND(COALESCE(MAX(CASE WHEN urut = 2 THEN cuttable_width_act END), 0),6) AS middle,
    ROUND(COALESCE(MAX(CASE WHEN urut = 3 THEN cuttable_width_act END), 0),6) AS back
FROM (
				SELECT
        qd.no_form,
        cuttable_width_act,
        ROW_NUMBER() OVER (
        PARTITION BY no_form
        ORDER BY id_length ASC
        ) AS urut
				FROM qc_inspect_form_det qd
				INNER JOIN qc on qc.no_form = qd.no_form
				WHERE cuttable_width_act > '0'
			) AS t
GROUP BY no_form
)

SELECT
DATE_FORMAT(tgl_dok, '%d-%m-%Y') tgl_dok,
DATE_FORMAT(qc.tgl_form, '%d-%m-%Y') tgl_form,
DATE_FORMAT(qc.start_form, '%d-%m-%Y') tgl_start,
DATE_FORMAT(qc.finish_form, '%d-%m-%Y') tgl_finish,
qc.no_mesin,
qc.operator,
qc.nik,
qc.no_invoice,
qc.no_form,
supplier,
a.no_ws,
ms.supplier buyer,
ac.styleno,
qc.id_item,
mi.itemdesc,
mi.color,
qc.no_lot,
qc.barcode,
a.no_roll_buyer,
qc.proses,
qc.cek_inspect,
qc.group_inspect,
case
		when qc.unit_weight = 'KG' OR qc.unit_weight = 'KGM' THEN qc.weight
		ELSE '0'
END AS w_bintex,
case
		when qc.act_unit_weight = 'KG' OR qc.act_unit_weight = 'KGM' THEN qc.act_weight
		ELSE '0'
END AS w_act,
case
		when qc.unit_weight = 'KG' OR qc.unit_weight = 'KGM' THEN round(qc.weight * 2.205,2)
		ELSE '0'
END AS w_bintex_lbs,
case
		when qc.act_unit_weight = 'KG' OR qc.act_unit_weight = 'KGM' THEN round(qc.act_weight * 2.205,2)
		ELSE '0'
END AS w_act_lbs,

qc.bintex_width,
round(pos.front,2) front,
round(pos.middle,2) middle,
round(pos.back,2) back,
ROUND((front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0)
, 2) AS avg_width,

ROUND((front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0) - qc.bintex_width,2) AS shortage_width,

round(qc.bintex_width * 2.54,2) bintex_width_cm,
round(pos.front * 2.54,2) front_cm,
round(pos.middle * 2.54,2) middle_cm,
round(pos.back * 2.54,2) back_cm,

ROUND((front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0) * 2.54
, 2) AS avg_width_cm,
ROUND(((front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0) * 2.54) - qc.bintex_width * 2.54
, 2) AS shortage_width_cm,

ROUND((((front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0) - qc.bintex_width) / qc.bintex_width * 100),2) AS short_roll_percentage_width,

qc.bintex_length_act,
qc.act_length_fix,
ROUND(qc.act_length_fix - qc.bintex_length_act,2) shortage_length_yard,

        CASE
            WHEN qc.unit_bintex = 'meter' THEN bintex_length
            WHEN qc.unit_bintex = 'yard'  THEN round(bintex_length * 0.9144,2)
        END as bintex_length_meter,

				CASE
            WHEN qc.unit_act_length = 'meter' THEN act_length
            WHEN qc.unit_act_length = 'yard'  THEN round(act_length * 0.9144,2)
        END as bintex_act_length_meter,

ROUND(
    (
        CASE
            WHEN qc.unit_act_length = 'meter' THEN act_length
            WHEN qc.unit_act_length = 'yard'  THEN round(act_length * 0.9144,2)
        END
        -
        CASE
            WHEN qc.unit_bintex = 'meter' THEN bintex_length
            WHEN qc.unit_bintex = 'yard'  THEN round(bintex_length * 0.9144,2)
        END
    ), 2
) AS shortage_length_meter,
ROUND((qc.act_length_fix - qc.bintex_length_act) / qc.bintex_length_act * 100,2) short_roll_percentage_length,
coalesce(pd.sum_point_def,0) sum_point_def,
ROUND(COALESCE((pd.sum_point_def * 36 * 100 ) / (qc.act_length_fix * (front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0)),0),2) as point_system,
gi.individu,
IF( ROUND(COALESCE((pd.sum_point_def * 36 * 100 ) / (qc.act_length_fix * (front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0)),0),2) <= gi.individu,'A','B') as grade,
mfi.founding_issue,
CASE
    WHEN qc.status_proses_form = 'done'
         THEN IF(qc.founding_issue IS NULL, 'PASS', 'HOLD')
    ELSE NULL
END AS founding_issue_result,
CASE
		WHEN qc.result = 'REJECT' AND qc.pass_with_condition = 'Y' THEN 'PASS WITH CONDITION'
		ELSE UPPER(qc.result)
		END as result,
qc.short_roll_result,
qc.final_result
from whs_lokasi_inmaterial a
inner join qc on a.no_barcode = qc.barcode
inner join whs_inmaterial_fabric_det b on a.no_dok = b.no_dok and a.id_item = b.id_item and a.id_jo = b.id_jo
inner join jo_det jd on a.id_jo = jd.id_jo
inner join so on jd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
inner join masteritem mi on qc.id_item = mi.id_item
left join pos on qc.no_form = pos.no_form
left join pd on qc.no_form = pd.no_form
left join qc_inspect_master_founding_issue mfi on qc.founding_issue = mfi.id
left join qc_inspect_master_group_inspect gi on qc.group_inspect = gi.id
WHERE qc.status_proses_form = 'done'
            ");

        // return DataTables::of($data_input)->toJson();

        $defects = DB::connection('mysql_sb')->select("
    SELECT id, critical_defect, point_defect
    FROM qc_inspect_master_defect
    order by point_defect asc, critical_defect asc");

        $totalDefectCols = count($defects);

        $defects_group_kol = DB::connection('mysql_sb')->select("
    SELECT COUNT(id) AS tot_kolom, point_defect
    FROM qc_inspect_master_defect
    GROUP BY point_defect
    order by point_defect asc");

        $defects_group_det = DB::connection('mysql_sb')->select("
    SELECT critical_defect, point_defect
    FROM qc_inspect_master_defect
    order by point_defect asc, critical_defect asc");

        $formDetails = DB::connection('mysql_sb')->select("
SELECT
    id_defect,
    a.no_form,
		critical_defect,
    SUM(up_to_3) * 1 +
    SUM(`3_6`) * 2 +
    SUM(`6_9`) * 3 +
    SUM(over_9) * 4 AS tot_defect
FROM
    qc_inspect_form_det a
INNER JOIN qc_inspect_master_defect b on a.id_defect = b.id
left JOIN qc_inspect_form c on a.no_form = c.no_form
where date(finish_form) >= '$tgl_awal' and date(finish_form) <= '$tgl_akhir'
GROUP BY
    id_defect, a.no_form
");

        $defectData = [];

        foreach ($formDetails as $detail) {
            $form = $detail->no_form;
            $defectId = $detail->id_defect;
            $total = $detail->tot_defect;

            $defectData[$form][$defectId] = $total;
        }

        return view(
            'qc_inspect.laporan_qc_inspect_roll',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-laporan",
                "subPage" => "qc-inspect-laporan-roll",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                "data_input" => $data_input,
                "defectData" => $defectData,
                'defects' => $defects,
                'totalDefectCols' => $totalDefectCols,
                'defects_group_kol' => $defects_group_kol,
                'defects_group_det' => $defects_group_det,
                "containerFluid" => true,
                'tgl_skrg' => $tgl_skrg,
                "user" => $user,

            ]
        );
    }

    public function export_excel_qc_inspect_roll(Request $request)
    {
        return Excel::download(new export_excel_qc_inspect_roll($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }

    public function qc_inspect_laporan_lot(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        $data_input = DB::connection('mysql_sb')->select("WITH bd as
(
select
a.no_dok,
a.tgl_dok,
a.no_invoice,
a.supplier,
ac.kpno,
ms.supplier buyer,
ac.styleno,
b.id_item,
b.id_jo,
mi.itemdesc,
mi.color,
count(no_roll) jml_roll,
no_lot
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
where a.tgl_dok >= '$tgl_awal' and a.tgl_dok <= '$tgl_akhir'
group by id_item, id_jo, no_invoice, no_lot
),

qc_fmb as (
SELECT
    no_form,id_item, id_jo, no_invoice, no_lot,
    COALESCE(MAX(CASE WHEN urut = 1 THEN cuttable_width_act END), 0) AS front,
    COALESCE(MAX(CASE WHEN urut = 2 THEN cuttable_width_act END), 0) AS middle,
    COALESCE(MAX(CASE WHEN urut = 3 THEN cuttable_width_act END), 0) AS back
FROM (
				SELECT
        qd.no_form,
				id_item, qc.id_jo, no_invoice, no_lot,
        cuttable_width_act,
        ROW_NUMBER() OVER (
        PARTITION BY no_form
        ORDER BY id_length ASC
        ) AS urut
				FROM qc_inspect_form_det qd
				left JOIN qc_inspect_form qc on qc.no_form = qd.no_form
				WHERE cuttable_width_act > '0' and status_proses_form = 'done' and tgl_form >= '$tgl_awal'
			) AS t
GROUP BY no_form
),

pd as (
SELECT
    a.no_form,
    SUM(up_to_3) * 1 +
    SUM(`3_6`) * 2 +
    SUM(`6_9`) * 3 +
    SUM(over_9) * 4 AS point_def,
		act_width,
		act_length_fix,
		((SUM(up_to_3) * 1 +
    SUM(`3_6`) * 2 +
    SUM(`6_9`) * 3 +
    SUM(over_9) * 4 )/ (act_width * act_length_fix)) as point_def_calc,
group_concat(distinct(critical_defect))
FROM
    qc_inspect_form_det a
INNER JOIN qc_inspect_master_defect b on a.id_defect = b.id
inner JOIN qc_inspect_form c on a.no_form = c.no_form
where status_proses_form = 'done' and tgl_form >= '$tgl_awal'
GROUP BY
a.no_form
),

qc_calc as (
select
id_item, id_jo, no_invoice, no_lot,
sum(coalesce(point_def_calc,0)) as sum_point_def_calc,
sum(point_def) as sum_point_def,
sum(pd.act_width) sum_act_width,
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0) as cond_fmb
 from qc_fmb
left join pd on qc_fmb.no_form = pd.no_form
group by id_item, id_jo, no_invoice, no_lot
),

qc_avg_w as (
SELECT
id_item, a.id_jo, no_invoice, no_lot,kpno,
ROUND(
  CAST(
    SUM(
      (front + middle + back) /
      NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
        0
      )
    ) / COUNT(no_form)
    AS DECIMAL(10, 4)
  ),
  2
) avg_width_inch
FROM
qc_fmb a
left join (
select jd.id_jo, ac.kpno from jo_det jd
inner join so on jd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
where jd.cancel = 'N' and so.cancel_h = 'N'
) b on a.id_jo = b.id_jo
group by id_item, a.id_jo, no_invoice, no_lot
),

qc_avg_l as (
select
id_item,
id_jo,
no_invoice,
no_lot,
sum(act_length_fix) act_length_fix,
round(sum(act_length_fix) / count(no_form),2) avg_l,
group_concat(distinct(cek_inspect)) cek_inspect,
group_concat(distinct(group_inspect)) group_inspect,
count(no_form) jml_form,
group_concat(distinct(fi.founding_issue)) list_founding_issue
from qc_inspect_form qc
left join qc_inspect_master_founding_issue fi on qc.founding_issue = fi.id
where status_proses_form = 'done' and tgl_form >= '$tgl_awal'
group by id_item,id_jo,no_invoice,no_lot
),

qc_def_sum as (
select
		qc.id_item, qc.id_jo, qc.no_invoice, qc.no_lot,
    SUM(up_to_3) * 1 +
    SUM(`3_6`) * 2 +
    SUM(`6_9`) * 3 +
    SUM(over_9) * 4 AS point_def,jml_form,
		id_defect,
		critical_defect
FROM
qc_inspect_form_det qd
inner join qc_inspect_form qc on qd.no_form = qc.no_form
inner join qc_inspect_master_defect b on qd.id_defect = b.id
left join qc_avg_l a on qc.id_item = a.id_item and qc.id_jo = a.id_jo and qc.no_invoice = a.no_invoice and qc.no_lot = a.no_lot
where status_proses_form = 'done' and tgl_form >= '$tgl_awal'
group by id_defect, qc.id_item, qc.id_jo, qc.no_invoice, qc.no_lot
),

qc_list_def as (
select
id_item, id_jo, no_invoice, no_lot,
  GROUP_CONCAT(
    CONCAT(critical_defect, ' (', round(point_def / jml_form,2), ') ')
    ORDER BY point_def DESC, critical_defect asc
  ) AS list_defect,
round(sum(point_def / jml_form),2) as sum_calc_def
from qc_def_sum a
group by id_item, id_jo, no_invoice, no_lot
),

main as (
select
DATE_FORMAT(bd.tgl_dok, '%d-%m-%Y') tgl_dok,
bd.id_item,
bd.id_jo,
bd.no_invoice,
bd.no_lot,
bd.supplier,
bd.kpno,
bd.buyer,
bd.styleno,
bd.itemdesc,
bd.color,
bd.jml_roll,
qc_avg_l.cek_inspect,
qc_avg_l.jml_form,
qc_avg_l.group_inspect,
avg_width_inch,
round(avg_width_inch * 2.54,2) avg_width_cm,
avg_l,
round(avg_l * 0.9144,2) avg_l_meter,
round((sum_point_def_calc * 36 * 100) / qc_avg_l.jml_form,2) as avg_point,
g.shipment,
case
		when (sum_point_def_calc * 36 * 100) / qc_avg_l.jml_form <= g.shipment then 'A'
		ELSE 'B' END AS grade_visual_defect,
list_founding_issue,
qb.rate,
case
		when (sum_point_def_calc * 36 * 100) / qc_avg_l.jml_form < g.shipment then 'PASS'
		ELSE 'REJECT' END AS visual_defect_result,
case
		when qb.rate is null then '-'
		when qb.rate >= '4' then 'PASS'
		ELSE 'REJECT' END AS blanket_result,
qld.list_defect,
round(sum_calc_def * bd.jml_roll) as est_final_reject
from bd
left join qc_avg_l on bd.id_item = qc_avg_l.id_item and bd.id_jo = qc_avg_l.id_jo and bd.no_invoice = qc_avg_l.no_invoice and bd.no_lot = qc_avg_l.no_lot
left join qc_avg_w on bd.id_item = qc_avg_w.id_item and bd.id_jo = qc_avg_w.id_jo and bd.no_invoice = qc_avg_w.no_invoice and bd.no_lot = qc_avg_w.no_lot
left join qc_calc on bd.id_item = qc_calc.id_item and bd.id_jo = qc_calc.id_jo and bd.no_invoice = qc_calc.no_invoice and bd.no_lot = qc_calc.no_lot
inner join qc_inspect_master_group_inspect g on qc_avg_l.group_inspect = g.group_inspect
left join qc_inspect_form_blanket qb on bd.id_item = qb.id_item and bd.id_jo = qb.id_jo and bd.no_invoice = qb.no_invoice and bd.no_lot = qb.no_lot
left join qc_list_def qld on bd.id_item = qld.id_item and bd.id_jo = qld.id_jo and bd.no_invoice = qld.no_invoice and bd.no_lot = qld.no_lot
where jml_form is not null
group by bd.id_item, bd.id_jo, bd.no_invoice, bd.no_lot
)

select
main.*,
CASE
        WHEN main.blanket_result is null then '-'
				WHEN main.blanket_result = '-' then '-'
        WHEN main.blanket_result = 'PASS' AND main.visual_defect_result = 'PASS' THEN 'PASS'
        WHEN main.blanket_result = 'REJECT' AND main.visual_defect_result = 'PASS' THEN 'REJECT'
        WHEN main.blanket_result = 'PASS WITH CONDITION' AND main.visual_defect_result = 'PASS' THEN 'PASS WITH CONDITION'
        WHEN main.blanket_result = 'PASS' AND main.visual_defect_result = 'REJECT' THEN 'REJECT'
        WHEN main.blanket_result = 'REJECT' AND main.visual_defect_result = 'REJECT' THEN 'REJECT'
        WHEN main.blanket_result = 'PASS WITH CONDITION' AND main.visual_defect_result = 'REJECT' THEN 'REJECT'
        WHEN main.blanket_result = 'PASS' AND main.visual_defect_result = 'PASS WITH CONDITION' THEN 'PASS WITH CONDITION'
        WHEN main.blanket_result = 'REJECT' AND main.visual_defect_result = 'PASS WITH CONDITION' THEN 'REJECT'
        WHEN main.blanket_result = 'PASS WITH CONDITION' AND main.visual_defect_result = 'PASS WITH CONDITION' THEN 'PASS WITH CONDITION'
end as final_result
from main
            ");


        return view(
            'qc_inspect.laporan_qc_inspect_lot',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-laporan",
                "subPage" => "qc-inspect-laporan-lot",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                "data_input" => $data_input,
                "containerFluid" => true,
                'tgl_skrg' => $tgl_skrg,
                "user" => $user,

            ]
        );
    }

    public function export_excel_qc_inspect_lot(Request $request)
    {
        return Excel::download(new export_excel_qc_inspect_lot($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }


    public function qc_inspect_report_shade_band(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
max(qc.tgl_trans) tgl_update,
DATE_FORMAT(max(qc.tgl_trans), '%d-%M-%Y') AS tgl_update_fix,
c.supplier,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
mi.id_item,
b.id_jo,
mi.itemdesc,
qc.group,
count(qc.barcode) jml_roll,
d.result
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
left join qc_inspect_shade_band_result d on mi.id_item = d.id_item and a.id_jo = d.id_jo and qc.group = d.group
group by a.id_item, a.id_jo, qc.group
            ");
            return DataTables::of($data_input)->toJson();
        }
        return view(
            'qc_inspect.report_shade_band',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-laporan",
                "subPage" => "qc-inspect-report-shade-band",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                "containerFluid" => true,
                'tgl_skrg' => $tgl_skrg,
                "user" => $user,

            ]
        );
    }


    public function qc_inspect_report_shade_band_add($id_item, $id_jo, $group)
    {
        $user = Auth::user()->name;

        $get_header = DB::connection('mysql_sb')->select("SELECT
DATE_FORMAT(max(qc.tgl_trans), '%d-%M-%Y') AS tgl_update_fix,
c.supplier,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
mi.id_item,
mi.itemdesc,
qc.group
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
WHERE mi.id_item = ? AND a.id_jo = ? AND qc.`group` = ?", [$id_item, $id_jo, $group]);

        $supplier                   = $get_header[0]->supplier;
        $buyer                      = $get_header[0]->buyer;
        $ws                         = $get_header[0]->kpno;
        $style                      = $get_header[0]->styleno;
        $color                      = $get_header[0]->color;
        $group                      = $get_header[0]->group;
        $itemdesc                   = $get_header[0]->itemdesc;
        $tgl_update_fix               = $get_header[0]->tgl_update_fix;

        return view(
            'qc_inspect.report_shade_band_add',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-laporan",
                "subPage" => "qc-inspect-report-shade-band",
                "containerFluid" => true,
                "user" => $user,
                'supplier'      => $supplier,
                'buyer'         => $buyer,
                'ws'            => $ws,
                'style'         => $style,
                'id_item'       => $id_item,
                'id_jo'         => $id_jo,
                'color'         => $color,
                'group'         => $group,
                'itemdesc'      => $itemdesc,
                'tgl_update_fix'  => $tgl_update_fix,
            ]
        );
    }

    public function qc_inspect_report_shade_band_detail(Request $request)
    {
        $user = Auth::user()->name;
        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $group = $request->group;

        // dd($id_item, $id_jo, $group);

        $data_input = DB::connection('mysql_sb')->select("SELECT
no_invoice,
barcode,
no_roll_buyer,
no_lot,
qty_aktual,
satuan
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
WHERE mi.id_item = ? AND a.id_jo = ? AND qc.`group` = ?", [$id_item, $id_jo, $group]);
        return DataTables::of($data_input)->toJson();
    }

    public function save_report_shade_band_detail(Request $request)
    {
        $user = Auth::user()->name;

        $request->validate([
            'photo' => 'nullable|image|max:5120', // Make photo optional
            'txtid_item' => 'required',
            'txtid_jo' => 'required',
            'txtgroup' => 'required',
            'result' => 'required',
        ]);

        $id_item = $request->txtid_item;
        $id_jo = $request->txtid_jo;
        $group = $request->txtgroup;
        $ket = $request->txtket;
        $result = $request->result;

        $filename = null;

        // Check if a new photo is uploaded
        if ($request->hasFile('photo')) {
            $raw_filename = "{$id_item}_{$id_jo}_{$group}";
            $clean_filename = preg_replace('/[<>:"\/\\\\|?*]/', '_', $raw_filename);
            $extension = $request->file('photo')->getClientOriginalExtension();
            $filename = $clean_filename . '.' . $extension;

            $request->file('photo')->storeAs('public/gambar_shade_band', $filename);
        }

        $cek_result = DB::connection('mysql_sb')->table('qc_inspect_shade_band_result')
            ->where([
                ['id_item', '=', $id_item],
                ['id_jo', '=', $id_jo],
                ['group', '=', $group],
            ])
            ->first();

        if ($cek_result) {

            // ❗ If no photo in DB and no new photo uploaded, return error
            if (!$cek_result->photo && !$filename) {
                return response()->json(['error' => 'Photo is required because no photo exists.'], 422);
            }
            // Build update query dynamically based on whether photo was uploaded
            $updateData = [
                'result' => $result,
                'ket' => $ket,
                'created_by' => $user,
                'updated_at' => now(),
            ];

            if ($filename) {
                $updateData['photo'] = $filename;
            }

            DB::connection('mysql_sb')->table('qc_inspect_shade_band_result')
                ->where([
                    ['id_item', '=', $id_item],
                    ['id_jo', '=', $id_jo],
                    ['group', '=', $group],
                ])
                ->update($updateData);
        } else {
            // If no record, photo must be provided
            if (!$filename) {
                return response()->json(['error' => 'Photo is required for new records.'], 422);
            }

            DB::connection('mysql_sb')->table('qc_inspect_shade_band_result')->insert([
                'id_item' => $id_item,
                'id_jo' => $id_jo,
                'group' => $group,
                'ket' => $ket,
                'photo' => $filename,
                'result' => $result,
                'created_by' => $user,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Upload successful']);
    }


    public function get_photo_shade_band(Request $request)
    {
        $request->validate([
            'id_item' => 'required',
            'id_jo' => 'required',
            'group' => 'required',
        ]);

        $data = DB::connection('mysql_sb')->table('qc_inspect_shade_band_result')
            ->where('id_item', $request->id_item)
            ->where('id_jo', $request->id_jo)
            ->where('group', $request->group)
            ->select('photo', 'ket', 'result')
            ->first();

        if ($data) {
            return response()->json([
                'photo' => $data->photo,
                'ket' => $data->ket,
                'result' => $data->result,
            ]);
        } else {
            return response()->json([
                'photo' => null,
                'ket' => null,
                'result' => null,
            ]);
        }
    }

    public function delete_photo_shade_band(Request $request)
    {
        $photoName = $request->input('photo');

        if (!$photoName) {
            return response()->json(['error' => 'Photo name is required'], 400);
        }

        // Define full path in storage
        $path = 'public/gambar_shade_band/' . $photoName;

        if (Storage::exists($path)) {
            Storage::delete($path);
        } else {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Also update the database if needed
        DB::connection('mysql_sb')
            ->table('qc_inspect_shade_band_result')
            ->where('photo', $photoName)
            ->update(['photo' => null]);

        return response()->json(['message' => 'Photo deleted successfully']);
    }

    public function qc_inspect_report_shade_band_print($id_item, $id_jo, $group)
    {
        // Fetch header data using raw SQL query
        $data_header = DB::connection('mysql_sb')->select("SELECT
DATE_FORMAT(max(qc.tgl_trans), '%d-%M-%Y') AS tgl_update_fix,
c.supplier,
ms.Supplier buyer,
ac.kpno,
ac.styleno,
mi.color,
mi.id_item,
mi.itemdesc,
qc.group,
count(qc.barcode) tot_barcode,
d.result
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
left join qc_inspect_shade_band_result d on mi.id_item = d.id_item and a.id_jo = d.id_jo and qc.group = d.group
WHERE mi.id_item = ? AND a.id_jo = ? AND qc.`group` = ?", [$id_item, $id_jo, $group]);

        $data_detail = DB::connection('mysql_sb')->select("SELECT
no_invoice,
barcode,
no_roll_buyer,
no_lot,
qty_aktual,
satuan
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
WHERE mi.id_item = ? AND a.id_jo = ? AND qc.`group` = ?", [$id_item, $id_jo, $group]);

        $data_result = DB::connection('mysql_sb')->select("SELECT
photo,
result,
ket
from qc_inspect_shade_band_result
WHERE id_item = ? AND id_jo = ? AND `group` = ?", [$id_item, $id_jo, $group]);

        $photo  = $data_result[0]->photo;
        $result  = $data_result[0]->result;
        $ket  = $data_result[0]->ket;

        // Generate PDF from the view
        $pdf = PDF::loadView('qc_inspect.pdf_report_shade_band', [
            'data_header' => $data_header,
            'data_detail' => $data_detail,
            'photo' => $photo,
            'result' => $result,
            'ket' => $ket,
        ])->setPaper('a4', 'portrait');

        // Set filename and return download
        $fileName = 'pdf.pdf';
        return $pdf->download(str_replace("/", "_", $fileName));
    }


    public function qc_inspect_sticker_shade_band_print($id_item, $id_jo, $group)
    {

        // Now use the string in the raw SQL query
        $data_header = DB::connection('mysql_sb')->select("SELECT
qc.barcode,
qc.group
from qc_inspect_shade_band qc
left join whs_lokasi_inmaterial a on qc.barcode = a.no_barcode
LEFT JOIN whs_inmaterial_fabric_det b ON a.no_dok = b.no_dok AND a.id_item = b.id_item AND a.id_jo = b.id_jo
LEFT JOIN whs_inmaterial_fabric c ON a.no_dok = c.no_dok
INNER JOIN jo_det jd ON a.id_jo = jd.id_jo
INNER JOIN so ON jd.id_so = so.id
INNER JOIN act_costing ac ON so.id_cost = ac.id
INNER JOIN mastersupplier ms ON ac.id_buyer = ms.Id_Supplier
INNER JOIN masteritem mi ON a.id_item = mi.id_item
WHERE mi.id_item = ? AND a.id_jo = ? AND qc.`group` = ?", [$id_item, $id_jo, $group]);

        // Generate PDF from the view
        $pdf = PDF::loadView('qc_inspect.pdf_print_group_shade_band', [
            'data_header' => $data_header,
        ])->setPaper([0, 0, 113.39, 85.04]);

        // Set filename and return download
        $fileName = 'pdf.pdf';
        return $pdf->download(str_replace("/", "_", $fileName));
    }
}
