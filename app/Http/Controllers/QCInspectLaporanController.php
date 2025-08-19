<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\export_excel_qc_inspect_roll;

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
where tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'),
pos as (
SELECT
    no_form,
    ROUND(COALESCE(MAX(CASE WHEN urut = 1 THEN cuttable_width_act END), 0),2) AS front,
    ROUND(COALESCE(MAX(CASE WHEN urut = 2 THEN cuttable_width_act END), 0),2) AS middle,
    ROUND(COALESCE(MAX(CASE WHEN urut = 3 THEN cuttable_width_act END), 0),2) AS back
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
DATE_FORMAT(qc.start_form, '%d-%m-%Y') tgl_form,
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
pos.front,
pos.middle,
pos.back,
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
    0) * 2.54) - qc.bintex_width
, 2) AS shortage_width_cm,

ROUND((((front + middle + back)
    /
    NULLIF(
        (CASE WHEN front  <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN middle <> 0 THEN 1 ELSE 0 END) +
        (CASE WHEN back   <> 0 THEN 1 ELSE 0 END),
    0) * 2.54) - qc.bintex_width) / qc.bintex_width * 100
, 2) AS short_roll_percentage_width,

qc.bintex_length_act,
qc.act_length_fix,
ROUND(qc.act_length_fix - qc.bintex_length_act,2) shortage_length_yard,

        CASE
            WHEN qc.unit_bintex = 'meter' THEN bintex_length
            WHEN qc.unit_bintex = 'yard'  THEN round(bintex_length / 0.9144,2)
        END as bintex_length_meter,

				CASE
            WHEN qc.unit_act_length = 'meter' THEN act_length
            WHEN qc.unit_act_length = 'yard'  THEN round(act_length / 0.9144,2)
        END as bintex_act_length_meter,

ROUND(
    (
        CASE
            WHEN qc.unit_act_length = 'meter' THEN act_length
            WHEN qc.unit_act_length = 'yard'  THEN round(act_length / 0.9144,2)
        END
        -
        CASE
            WHEN qc.unit_bintex = 'meter' THEN bintex_length
            WHEN qc.unit_bintex = 'yard'  THEN round(bintex_length / 0.9144,2)
        END
    ), 2
) AS shortage_length_meter,
ROUND((qc.act_length_fix - qc.bintex_length_act) / qc.bintex_length_act * 100,2) short_roll_percentage_length


from whs_lokasi_inmaterial a
inner join qc on a.no_barcode = qc.barcode
inner join whs_inmaterial_fabric_det b on a.no_dok = b.no_dok and a.id_item = b.id_item and a.id_jo = b.id_jo
inner join jo_det jd on a.id_jo = jd.id_jo
inner join so on jd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
inner join masteritem mi on qc.id_item = mi.id_item
left join pos on qc.no_form = pos.no_form
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
where tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'
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
}
