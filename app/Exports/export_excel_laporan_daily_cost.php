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

        $rawData = DB::connection('mysql_sb')->select("WITH dd AS (
    SELECT a.bulan,
           a.nama_bulan,
           CAST(a.tahun AS UNSIGNED) AS tahun,
           COUNT(a.tanggal)          AS tot_working_days
    FROM   dim_date a
    LEFT   JOIN mgt_rep_hari_libur b ON a.tanggal = b.tanggal_libur
    WHERE  a.status_prod = 'KERJA'
      AND  (b.status_absen <> 'LN' OR b.status_absen IS NULL)
      AND  a.tahun = '$this->tahun' AND a.bulan = '$this->bulan'
    GROUP  BY a.bulan, a.nama_bulan, a.tahun
),
dim_tgl AS (
SELECT d.tanggal,
       CASE WHEN b.tanggal_libur IS NOT NULL THEN 'LIBUR'
       ELSE d.status_prod
       END AS stat_kerja
FROM   dim_date d
LEFT JOIN mgt_rep_hari_libur b ON d.tanggal = b.tanggal_libur
    WHERE  tahun = '$this->tahun' AND bulan = '$this->bulan'
),
dc AS (
    SELECT
no_coa,
dd.bulan,
nama_bulan,
dd.tahun,
projection,
round(sum(projection / tot_working_days),2) AS daily_cost
    FROM   mgt_rep_daily_cost a
    LEFT   JOIN dd ON a.bulan = dd.bulan AND a.tahun = dd.tahun
    WHERE  a.tahun = '$this->tahun' AND a.bulan = '$this->bulan'
    GROUP  BY a.no_coa
),
coa AS (                       -- pengganti coa_direct ... coa_expense
    SELECT no_coa,
           nama_coa,
           CASE
             WHEN eng_categori4 = 'DIRECT LABOR COST'                THEN 'direct labor'
             WHEN eng_categori4 = 'INDIRECT LABOR COST'              THEN 'indirect labor'
             WHEN eng_categori4 = 'FIXED OVERHEAD COST'              THEN 'overhead labor'
             WHEN eng_categori4 = 'SELLING EXPENSE'                  THEN 'selling expense'
             WHEN eng_categori4 = 'GENERAL & ADMINISTRATION EXPENSE' THEN 'ga expense'
             WHEN eng_categori3 = 'OTHER EXPENSE'                    THEN 'other expense'
           END AS nm_labor,
           CASE                -- NULL = tidak difilter group_department
             WHEN eng_categori4 = 'DIRECT LABOR COST'                THEN 'PRODUCTION'
             WHEN eng_categori4 = 'INDIRECT LABOR COST'              THEN 'SUPPORTING PRODUCTION'
             WHEN eng_categori4 = 'SELLING EXPENSE'                  THEN 'SUPPORTING SELLING'
             WHEN eng_categori4 = 'GENERAL & ADMINISTRATION EXPENSE' THEN 'SUPPORTING GENERAL & ADMINISTRATION'
           END AS dept_filter
    FROM   mastercoa_v2
		WHERE mgt_report = 'Y'
),
map_coa AS (
    SELECT DISTINCT a.no_coa, b.no_cc
    FROM   mastercoa_v2 a
    JOIN   b_master_cc  b
           ON CASE b.group2
                WHEN 'SUPPORTING GENERAL & ADMINISTRATION' THEN a.support_gen_adm
                WHEN 'SUPPORTING PRODUCTION'               THEN a.support_prod
                WHEN 'PRODUCTION'                          THEN a.prod
                WHEN 'SUPPORTING SELLING'                  THEN a.support_sell
              END = 'Y'
    WHERE  b.status = 'Active' AND b.id_pc <> 'NAK'
),
m_labor AS (
    SELECT tanggal_berjalan, sub_dept_id, group_department,
           SUM(bruto)   AS wage,
           SUM(bpjs_tk) AS bpjs_tk,
           SUM(bpjs_ks) AS bpjs_ks,
           SUM(thr)     AS thr
    FROM   mgt_rep_labor
    WHERE  tanggal_berjalan BETWEEN '$start_date' AND '$end_date'
      AND  status_staff = 'NON STAFF'
    GROUP  BY sub_dept_id, group_department, tanggal_berjalan
)
SELECT d.tanggal,
       d.stat_kerja,
       a.no_coa,
       a.nama_coa,
       COALESCE(dc.projection, 0) AS projection,
       COALESCE(dc.daily_cost, 0) AS daily_cost,
       CASE
         WHEN TRIM(a.no_coa) IN ('8.97.01','8.98.01','8.99.01')
           THEN CASE WHEN d.stat_kerja = 'KERJA' THEN COALESCE(dc.daily_cost, 0) ELSE 0 END
         ELSE
           CASE WHEN d.stat_kerja = 'KERJA' THEN COALESCE(dc.daily_cost, 0) ELSE 0 END
           + CASE WHEN d.stat_kerja IS NULL THEN 0 ELSE
               COALESCE(SUM(CASE
                 WHEN a.nama_coa LIKE '%GAJI%'                 THEN c.wage
                 WHEN a.nama_coa LIKE '%BPJS KETENAGAKERJAAN%' THEN c.bpjs_tk
                 WHEN a.nama_coa LIKE '%BPJS KESEHATAN%'       THEN c.bpjs_ks
                 WHEN a.nama_coa LIKE '%THR%'                  THEN c.thr
               END), 0)
             END
       END AS tot_labor,
       a.nm_labor
FROM   dim_tgl d
CROSS  JOIN coa a
LEFT   JOIN dc ON dc.no_coa = a.no_coa
                  AND CAST(dc.bulan AS UNSIGNED) = MONTH(d.tanggal)
                  AND CAST(dc.tahun AS UNSIGNED) = YEAR(d.tanggal)
LEFT   JOIN map_coa b  ON b.no_coa = a.no_coa
LEFT   JOIN m_labor c  ON c.sub_dept_id      = b.no_cc
                      AND c.tanggal_berjalan = d.tanggal
                      AND (a.dept_filter IS NULL OR c.group_department = a.dept_filter)
WHERE  a.nm_labor IS NOT NULL
GROUP  BY d.tanggal, a.no_coa
ORDER  BY d.tanggal ASC, a.no_coa ASC
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
