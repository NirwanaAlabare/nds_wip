<?php

namespace App\Exports\MgtReportDashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SheetLabor implements FromView, ShouldAutoSize, WithEvents, WithTitle
{
    use Exportable;

    /** kolom identitas sebelum kolom per tanggal: No Dept, Dept Name, No Sub Dept, Sub Dept Name, Group */
    private const IDENTITY_COLS = 5;

    /** jumlah kolom untuk tiap tanggal: No of MP, Working M, Wage, BPJS TK, BPJS KS, Accrual THR, Total */
    private const COLS_PER_DATE = 7;

    protected $start_date;
    protected $end_date;
    protected $status_staff;
    protected $dates = [];

    public function __construct($start_date, $end_date, $status_staff = 'STAFF')
    {
        $this->start_date   = $start_date;
        $this->end_date     = $end_date;
        $this->status_staff = $status_staff;
    }

    public function title(): string
    {
        return 'Labor - ' . ucwords(strtolower($this->status_staff));
    }

    public function view(): View
    {
        [$dates, $rows] = $this->buildPivot();

        $this->dates = $dates;

        return view('management_report.export_excel_labor', [
            'title'      => 'LABOR - ' . $this->status_staff,
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
            'dates'      => $dates,
            'rows'       => $rows,
        ]);
    }

    /**
     * Susun data jadi pivot: 1 baris per sub department, kolom per tanggal.
     *
     * @return array{0: array<string, string>, 1: array<int, array>}
     */
    private function buildPivot(): array
    {
        $rawData = DB::connection('mysql_sb')->select("SELECT
                tanggal_berjalan,
                status_staff,
                department_id,
                department_name,
                sub_dept_id,
                sub_dept_name,
                group_department,
                man_power,
                absen_menit,
                bruto,
                total_lembur_rupiah,
                bpjs_tk,
                bpjs_ks,
                thr,
                gaji_perhari
            FROM mgt_rep_labor
            WHERE tanggal_berjalan >= ? AND tanggal_berjalan <= ?
              AND status_staff = ?
            ORDER BY department_id ASC, sub_dept_id ASC, tanggal_berjalan ASC", [
            $this->start_date,
            $this->end_date,
            $this->status_staff,
        ]);

        $dates = [];
        $rows  = [];

        foreach ($rawData as $row) {
            $tanggal = (string) $row->tanggal_berjalan;

            if (!isset($dates[$tanggal])) {
                $dates[$tanggal] = [
                    'hari'    => date('l', strtotime($tanggal)),
                    'tanggal' => date('j M y', strtotime($tanggal)),
                ];
            }

            $key = $row->department_id . '|' . $row->sub_dept_id;

            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'department_id'    => $row->department_id,
                    'department_name'  => $row->department_name,
                    'sub_dept_id'      => $row->sub_dept_id,
                    'sub_dept_name'    => $row->sub_dept_name,
                    'group_department' => $row->group_department,
                    'values'           => [],
                ];
            }

            $wage  = (float) $row->bruto;
            $tk    = (float) $row->bpjs_tk;
            $ks    = (float) $row->bpjs_ks;
            $thr   = (float) $row->thr;

            $rows[$key]['values'][$tanggal] = [
                'man_power'   => (float) $row->man_power,
                'absen_menit' => (float) $row->absen_menit,
                'wage'        => $wage,
                'bpjs_tk'     => $tk,
                'bpjs_ks'     => $ks,
                'thr'         => $thr,
                'total'       => $wage + $tk + $ks + $thr,
            ];
        }

        ksort($dates);

        return [$dates, array_values($rows)];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet         = $event->sheet->getDelegate();
                $highestRow    = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $lastColIndex  = Coordinate::columnIndexFromString($highestColumn);
                $headerRow     = 2;
                $firstDataRow  = $headerRow + 1;

                $sheet->getStyle('A1:' . $highestColumn . $headerRow)->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE2EFDA'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                if ($highestRow >= $firstDataRow && $lastColIndex > self::IDENTITY_COLS) {
                    $firstValueCol = Coordinate::stringFromColumnIndex(self::IDENTITY_COLS + 1);

                    $sheet->getStyle($firstValueCol . $firstDataRow . ':' . $highestColumn . $highestRow)
                        ->applyFromArray([
                            'numberFormat' => ['formatCode' => '#,##0.00;-#,##0.00;"-"'],
                            'alignment'    => [
                                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                }

                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->setAutoFilter('A' . $headerRow . ':' . $highestColumn . $highestRow);
                $sheet->freezePane(Coordinate::stringFromColumnIndex(self::IDENTITY_COLS + 1) . $firstDataRow);
                $sheet->setTitle($this->title());
            },
        ];
    }
}
