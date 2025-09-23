<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class export_excel_qc_inspect_lot implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected $from, $to, $rowCount;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function view(): View
    {
        $data_input = DB::connection('mysql_sb')->select("
WITH bd as
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
where a.tgl_dok >= '$this->from' and a.tgl_dok <= '$this->to'
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
				WHERE cuttable_width_act > '0' and status_proses_form = 'done' and tgl_form >= '$this->from'
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
where status_proses_form = 'done' and tgl_form >= '$this->from'
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
where status_proses_form = 'done' and tgl_form >= '$this->from'
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
where status_proses_form = 'done' and tgl_form >= '$this->from'
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

        $this->rowCount = count($data_input) + 1; // 1 for header


        return view('qc_inspect.export_excel_qc_inspect_lot', [
            'data_input' => $data_input,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // e.g., 'Z'
                $range = 'A1:' . $highestColumn . $highestRow;

                // 🔲 Apply border to the whole data range
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // 🔄 Center align specific rows (headers)
                $sheet->getStyle('A2:' . $highestColumn . '4')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // 🛑 Prevent text wrapping for entire sheet
                $sheet->getStyle($range)->getAlignment()->setWrapText(false);

                // 📏 Set fixed column widths (customize as needed)
                $columns = range('A', $highestColumn);
                foreach ($columns as $column) {
                    $sheet->getColumnDimension($column)->setWidth(20); // change width if needed
                }
            },
        ];
    }
}
