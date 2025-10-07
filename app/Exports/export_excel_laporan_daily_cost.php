<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class export_excel_laporan_daily_cost implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected $bulan, $tahun, $rowCount;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        $start_date = \Carbon\Carbon::createFromDate($this->tahun, $this->bulan, 1)->startOfDay()->format('Y-m-d');
        $end_date = \Carbon\Carbon::createFromDate($this->tahun, $this->bulan, 1)->endOfMonth()->endOfDay()->format('Y-m-d');

        $tanggalList = [];

        if ($this->bulan && $this->tahun) {
            $startDate = Carbon::createFromDate($this->tahun, $this->bulan, 1);
            $endDate = $startDate->copy()->endOfMonth();

            while ($startDate->lte($endDate)) {
                $tanggalList[] = $startDate->copy();
                $startDate->addDay();
            }
        }

        $rawData = DB::connection('mysql_sb')->select("WITH dd as (
SELECT
a.bulan,
a.nama_bulan,
CAST(a.tahun AS UNSIGNED) AS tahun,
COUNT(tanggal) AS tot_working_days
FROM dim_date a
LEFT JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
WHERE status_prod = 'KERJA'
AND (status_absen != 'LN' OR status_absen IS NULL)
AND tahun = '$this->tahun' AND bulan = '$this->bulan'
GROUP BY bulan, tahun
ORDER BY
CAST(a.tahun AS UNSIGNED) ASC,
CAST(a.bulan AS UNSIGNED) ASC
),
dim_tgl as (
SELECT tanggal,
case
		when status_prod = 'KERJA' AND status_absen = 'LP' THEN 'KERJA'
		when status_prod = 'KERJA' AND status_absen = 'LN' THEN 'KERJA'
		when status_prod = 'KERJA' AND status_absen is null THEN 'KERJA'
		when status_prod = 'LIBUR' AND status_absen = 'LP' THEN 'LIBUR'
		when status_prod = 'LIBUR' AND status_absen = 'LN' THEN 'LIBUR'
		when status_prod = 'LIBUR' AND status_absen is null THEN 'LIBUR'

		END AS stat_kerja
FROM dim_date a
left join mgt_rep_hari_libur b on a.tanggal = b.tanggal_libur
where tahun = '$this->tahun' AND bulan = '$this->bulan'
),
dc as (
SELECT
no_coa,
dd.bulan,
nama_bulan,
dd.tahun,
projection,
round(sum(projection / tot_working_days),2) AS daily_cost
FROM mgt_rep_daily_cost a
LEFT JOIN dd ON a.bulan = dd.bulan AND a.tahun = dd.tahun
WHERE a.tahun = '$this->tahun' and a.bulan = '$this->bulan'
GROUP BY no_coa
),
coa_direct as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
case
		when stat_kerja = 'LIBUR' THEN 0
		ELSE coalesce(daily_cost,0)
		END AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'DIRECT LABOR COST'
),
coa_indirect as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
case
		when stat_kerja = 'LIBUR' THEN 0
		ELSE coalesce(daily_cost,0)
		END AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'INDIRECT LABOR COST'
),
coa_overhead as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
case
		when stat_kerja = 'LIBUR' THEN 0
		ELSE coalesce(daily_cost,0)
		END AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'FIXED OVERHEAD COST'
),
coa_selling as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
case
		when stat_kerja = 'LIBUR' THEN 0
		ELSE coalesce(daily_cost,0)
		END AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'SELLING EXPENSE'
),
coa_ga as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
case
		when stat_kerja = 'LIBUR' THEN 0
		ELSE coalesce(daily_cost,0)
		END AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'GENERAL & ADMINISTRATION EXPENSE'
),
coa_expense as (
select
tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
coalesce(projection,0) projection,
case
		when stat_kerja = 'LIBUR' THEN 0
		ELSE coalesce(daily_cost,0)
		END AS daily_cost
FROM dim_tgl d
cross join mastercoa_v2 a
left join dc on a.no_coa = dc.no_coa
where eng_categori4 = 'INTEREST EXPENSE'
),
map_coa as (
select no_coa, nama_coa, no_cc, cc_name, group2, id_pc from (select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' support_gen_adm from b_master_cc where group2 = 'SUPPORTING GENERAL & ADMINISTRATION' and status = 'Active') b on b.support_gen_adm = a. support_gen_adm
                                UNION
                                select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' support_prod from b_master_cc where group2 = 'SUPPORTING PRODUCTION' and status = 'Active') b on b.support_prod = a. support_prod
                                UNION
                                select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' prod from b_master_cc where group2 = 'PRODUCTION' and status = 'Active') b on b.prod = a.prod
                                UNION
                                select a.no_coa, a.nama_coa, b.no_cc, cc_name, b.id_pc, group2 from (select no_coa, nama_coa, support_gen_adm, support_prod, prod, support_sell from mastercoa_v2 where support_gen_adm != 'N' OR support_prod != 'N' OR prod != 'N' OR support_sell != 'N') a inner join
                                (select no_cc, cc_name, group2, id_pc, 'Y' support_sell from b_master_cc where group2 = 'SUPPORTING SELLING' and status = 'Active') b on b.support_sell = a.support_sell)a where id_pc != 'NAK' GROUP BY no_coa, no_cc, id_pc
                                ORDER BY no_coa asc
),
m_labor as (
select
tanggal_berjalan,
sub_dept_id,
group_department,
sum(bruto) wage,
sum(bpjs_tk) bpjs_tk,
sum(bpjs_ks) bpjs_ks,
sum(thr) thr
from mgt_rep_labor
WHERE tanggal_berjalan BETWEEN '$start_date' AND '$end_date' AND status_staff = 'NON STAFF' -- dynamic filter
group by sub_dept_id, group_department, tanggal_berjalan
)

-- Data Value Tabel
(
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
case
		when a.nama_coa like '%GAJI%' then sum(wage)
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk)
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks)
		when a.nama_coa like '%THR%' then sum(thr)
		ELSE '0'
		END AS nominal_labor,
case
		when a.nama_coa like '%GAJI%' then sum(wage) + a.daily_cost
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk) + a.daily_cost
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks) + a.daily_cost
		when a.nama_coa like '%THR%' then sum(thr) + a.daily_cost
		ELSE a.daily_cost
		END AS tot_labor,
'direct labor' as nm_labor
from coa_direct a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan and group_department = 'PRODUCTION'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
case
		when a.nama_coa like '%GAJI%' then sum(wage)
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk)
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks)
		when a.nama_coa like '%THR%' then sum(thr)
		ELSE '0'
		END AS nominal_labor,
case
		when a.nama_coa like '%GAJI%' then sum(wage) + a.daily_cost
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk) + a.daily_cost
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks) + a.daily_cost
		when a.nama_coa like '%THR%' then sum(thr) + a.daily_cost
		ELSE a.daily_cost
		END AS tot_labor,
'indirect labor' as nm_labor
from coa_indirect  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan and group_department = 'SUPPORTING PRODUCTION'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
case
		when a.nama_coa like '%GAJI%' then sum(wage)
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk)
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks)
		when a.nama_coa like '%THR%' then sum(thr)
		ELSE '0'
		END AS nominal_labor,
case
		when a.nama_coa like '%GAJI%' then sum(wage) + a.daily_cost
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk) + a.daily_cost
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks) + a.daily_cost
		when a.nama_coa like '%THR%' then sum(thr) + a.daily_cost
		ELSE a.daily_cost
		END AS tot_labor,
'overhead labor' as nm_labor
from coa_overhead  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
case
		when a.nama_coa like '%GAJI%' then sum(wage)
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk)
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks)
		when a.nama_coa like '%THR%' then sum(thr)
		ELSE '0'
		END AS nominal_labor,
case
		when a.nama_coa like '%GAJI%' then sum(wage) + a.daily_cost
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk) + a.daily_cost
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks) + a.daily_cost
		when a.nama_coa like '%THR%' then sum(thr) + a.daily_cost
		ELSE a.daily_cost
		END AS tot_labor,
'selling expense' as nm_labor
from coa_selling  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan  AND c.group_department = 'SUPPORTING SELLING'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
case
		when a.nama_coa like '%GAJI%' then sum(wage)
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk)
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks)
		when a.nama_coa like '%THR%' then sum(thr)
		ELSE '0'
		END AS nominal_labor,
case
		when a.nama_coa like '%GAJI%' then sum(wage) + a.daily_cost
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk) + a.daily_cost
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks) + a.daily_cost
		when a.nama_coa like '%THR%' then sum(thr) + a.daily_cost
		ELSE a.daily_cost
		END AS tot_labor,
'ga expense' as nm_labor
from coa_ga  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan  AND c.group_department = 'SUPPORTING GENERAL & ADMINISTRATION'
GROUP BY no_coa, a.tanggal
UNION ALL
SELECT
a.tanggal,
stat_kerja,
a.no_coa,
a.nama_coa,
a.projection,
a.daily_cost,
case
		when a.nama_coa like '%GAJI%' then sum(wage)
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk)
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks)
		when a.nama_coa like '%THR%' then sum(thr)
		ELSE '0'
		END AS nominal_labor,
case
		when a.nama_coa like '%GAJI%' then sum(wage) + a.daily_cost
		when a.nama_coa like '%BPJS KETENAGAKERJAAN%' then sum(bpjs_tk) + a.daily_cost
		when a.nama_coa like '%BPJS KESEHATAN%' then sum(bpjs_ks) + a.daily_cost
		when a.nama_coa like '%THR%' then sum(thr) + a.daily_cost
		ELSE a.daily_cost
		END AS tot_labor,
'other expense' as nm_labor
from coa_expense  a
left join map_coa b on a.no_coa = b.no_coa
left join m_labor c on b.no_cc = c.sub_dept_id and a.tanggal = c.tanggal_berjalan
GROUP BY no_coa, a.tanggal
)
ORDER BY tanggal asc,
no_coa asc
        ");

        $groupedData = [];

        foreach ($rawData as $row) {
            $coa = $row->no_coa;
            $tanggal = \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d');

            if (!isset($groupedData[$coa])) {
                $groupedData[$coa] = [
                    'no_coa' => $coa,
                    'nama_coa' => $row->nama_coa,
                    'projection' => $row->projection,
                    'daily_cost' => $row->daily_cost,
                    'totals_by_date' => [],
                ];
            }

            $groupedData[$coa]['totals_by_date'][$tanggal] = $row->tot_labor;
        };

        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('management_report.export_excel_laporan_daily_cost', [
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'tanggalList' => $tanggalList,
            'groupedData' => $groupedData,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // e.g. 'Z'
                $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                // ===== 1. Format header row (row 2) =====
                for ($i = 1; $i <= $columnIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $cell = $colLetter . '2';

                    $sheet->getStyle($cell)->applyFromArray([
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFD9EDF7'], // Light blue, you can change
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FF000000'], // Black text
                        ],
                    ]);
                }

                // ===== 2. Format value columns (C to end) =====
                for ($i = 3; $i <= $columnIndex; $i++) { // Column C (3) to last
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $range = $colLetter . '3:' . $colLetter . $highestRow;

                    // Set number format and align right
                    $sheet->getStyle($range)->applyFromArray([
                        'numberFormat' => [
                            'formatCode' => '#,##0.00',
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }

                // ===== 3. Apply border to whole table =====
                $range = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            }
        ];
    }
}
