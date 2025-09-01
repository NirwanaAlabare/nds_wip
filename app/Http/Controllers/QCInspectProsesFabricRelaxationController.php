<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectProsesFabricRelaxationController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("WITH qc as (
SELECT
qc.id,
qc.no_form,
qc.tgl_form,
qc.no_mesin,
DATE_FORMAT(qc.tgl_form, '%d-%M-%Y') AS tgl_form_fix,
qc.no_lot,
qc.id_item,
a.itemdesc,
a.supplier,
qc.no_invoice,
a.buyer,
a.kpno,
a.styleno,
a.color,
qc.group_inspect,
a.type_pch,
qc.proses,
qc.barcode,
b.no_roll_buyer,
qc.status_proses_form,
c.individu,
qc.pass_with_condition,
CASE
    WHEN qc.status_proses_form = 'done'
         THEN IF(qc.founding_issue IS NULL, 'PASS', 'HOLD')
    ELSE NULL
END AS founding_issue_result,
qc.short_roll_result,
qc.final_result
from qc_inspect_form qc
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
where qc.tgl_form >= '$tgl_awal' and qc.tgl_form <= '$tgl_akhir'
),
a AS (
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
		WHERE tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'
		GROUP BY no_form
),
b AS (
    SELECT
        a.no_form,
        AVG(cuttable_width_act) AS avg_width,
        b.act_length_fix
    FROM qc_inspect_form_det a
    INNER JOIN qc_inspect_form b ON a.no_form = b.no_form
    LEFT JOIN qc_inspect_master_group_inspect c ON b.group_inspect = c.id
		WHERE tgl_form >= '$tgl_awal' and tgl_form <= '$tgl_akhir'
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
		GROUP BY a.no_form
),
d AS (
SELECT
c.no_form,
sum_up_to_3,
sum_3_6,
sum_6_9,
sum_over_9,
c.tot_point,
round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix)),2) AS act_point,
individu,
if(round((((c.tot_point * 36) * 100) / (b.avg_width * b.act_length_fix))) <= individu,'PASS','REJECT') result
FROM c
INNER JOIN b ON c.no_form = b.no_form
GROUP BY c.no_form
)

SELECT qc.*,
d.act_point,
concat(d.act_point, '/', qc.individu) point_max_point,
CASE
		WHEN d.result = 'REJECT' AND qc.pass_with_condition = 'Y' THEN 'PASS WITH CONDITION'
		ELSE d.result
		END as result
FROM qc
left join d on qc.no_form = d.no_form
ORDER BY qc.tgl_form desc, no_form asc, no_invoice asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_fabric_relaxation',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-fabric-relaxation",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }
}
