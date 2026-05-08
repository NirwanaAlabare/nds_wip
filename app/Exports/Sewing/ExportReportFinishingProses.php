<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportReportFinishingProses implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $proses, $rowCount;

    public function __construct($start_date, $end_date, $proses)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->proses = $proses;
    }

    public function view(): View
    {
        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $proses = $this->proses;

        $start = $this->start_date . ' 00:00:00';
        $end   = $this->end_date . ' 23:59:59';

        if (!empty($proses)) {
            $rawData = DB::connection('mysql_sb')->select("SELECT
                    output_secondary_master.secondary AS proses,
                    userpassword.username AS line,
                    mastersupplier.supplier AS buyer,
                    act_costing.kpno AS no_ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    SUM(wip) wip,
                    SUM(`in`) `in`,
                    SUM(defect) defect,
                    SUM(rework) rework,
                    SUM(reject) reject,
                    SUM(output) + SUM(rework) output
                FROM
                (
                        -- IN
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                COUNT( DISTINCT CASE WHEN output_secondary_out.id IS NULL THEN output_secondary_in.id END ) AS wip,
                                COUNT( DISTINCT output_secondary_in.id ) AS 'in',
                                0 AS defect,
                                0 AS rework,
                                0 AS reject,
                                0 AS output
                        FROM
                                output_secondary_in
                                LEFT JOIN output_secondary_out on output_secondary_out.secondary_in_id = output_secondary_in.id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_in.updated_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- DEFECT
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                COUNT( DISTINCT output_secondary_out_defect.id ) AS defect,
                                0 AS rework,
                                0 AS reject,
                                0 AS output
                        FROM
                                output_secondary_out_defect
                                LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out_defect.created_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- REWORK
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                0 AS defect,
                                COUNT( DISTINCT output_secondary_out_defect.id ) AS rework,
                                0 AS reject,
                                0 AS output
                        FROM
                                output_secondary_out_defect
                                LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out_defect.updated_at BETWEEN ? and ?
                                AND output_secondary_out_defect.`status` = 'reworked'
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- REJECT
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                0 AS defect,
                                0 AS rework,
                                COUNT( DISTINCT output_secondary_out_reject.id ) AS reject,
                                0 AS output
                        FROM
                                output_secondary_out_reject
                                LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_reject.secondary_out_id
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out.updated_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- OUTPUT RFT & REWORK
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                0 AS defect,
                                0 AS rework,
                                0 AS reject,
                                COUNT(output_rfts.id) AS output
                        FROM
                                output_secondary_out
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out.updated_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                                AND output_secondary_out.`status` = 'rft'
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by

                ) secondary_process
                LEFT JOIN output_secondary_master ON output_secondary_master.id = secondary_process.secondary_id
                LEFT JOIN so_det ON so_det.id = secondary_process.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = secondary_process.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                WHERE
                    output_secondary_master.secondary = ?
                group by
                    proses,
                    line,
                    no_ws,
                    style,
                    color,
                    size
            ", [
                $start, $end,
                $start, $end,
                $start, $end,
                $start, $end,
                $start, $end,
                $proses
            ]);
        }else{
            $rawData = DB::connection('mysql_sb')->select("SELECT
                    output_secondary_master.secondary AS proses,
                    userpassword.username AS line,
                    mastersupplier.supplier AS buyer,
                    act_costing.kpno AS no_ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    SUM(wip) wip,
                    SUM(`in`) `in`,
                    SUM(defect) defect,
                    SUM(rework) rework,
                    SUM(reject) reject,
                    SUM(output) + SUM(rework) output
                FROM
                (
                        -- IN
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                COUNT( DISTINCT CASE WHEN output_secondary_out.id IS NULL THEN output_secondary_in.id END ) AS wip,
                                COUNT( DISTINCT output_secondary_in.id ) AS 'in',
                                0 AS defect,
                                0 AS rework,
                                0 AS reject,
                                0 AS output
                        FROM
                                output_secondary_in
                                LEFT JOIN output_secondary_out on output_secondary_out.secondary_in_id = output_secondary_in.id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_in.updated_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- DEFECT
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                COUNT( DISTINCT output_secondary_out_defect.id ) AS defect,
                                0 AS rework,
                                0 AS reject,
                                0 AS output
                        FROM
                                output_secondary_out_defect
                                LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out_defect.created_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- REWORK
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                0 AS defect,
                                COUNT( DISTINCT output_secondary_out_defect.id ) AS rework,
                                0 AS reject,
                                0 AS output
                        FROM
                                output_secondary_out_defect
                                LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_defect.secondary_out_id
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out_defect.updated_at BETWEEN ? and ?
                                AND output_secondary_out_defect.`status` = 'reworked'
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- REJECT
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                0 AS defect,
                                0 AS rework,
                                COUNT( DISTINCT output_secondary_out_reject.id ) AS reject,
                                0 AS output
                        FROM
                                output_secondary_out_reject
                                LEFT JOIN output_secondary_out ON output_secondary_out.id = output_secondary_out_reject.secondary_out_id
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out.updated_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by
                        UNION ALL

                        -- OUTPUT RFT & REWORK
                        SELECT
                                output_rfts.so_det_id,
                                output_rfts.created_by,
                                output_secondary_in.secondary_id,
                                0 AS wip,
                                0 AS 'in',
                                0 AS defect,
                                0 AS rework,
                                0 AS reject,
                                COUNT(output_rfts.id) AS output
                        FROM
                                output_secondary_out
                                LEFT JOIN output_secondary_in ON output_secondary_in.id = output_secondary_out.secondary_in_id
                                LEFT JOIN output_rfts ON output_rfts.id = output_secondary_in.rft_id
                        WHERE
                                output_secondary_out.updated_at BETWEEN ? and ?
                                AND output_rfts.id is not null
                                AND output_secondary_out.`status` = 'rft'
                        GROUP BY
                                output_secondary_in.secondary_id,
                                output_rfts.so_det_id,
                                output_rfts.created_by

                ) secondary_process
                LEFT JOIN output_secondary_master ON output_secondary_master.id = secondary_process.secondary_id
                LEFT JOIN so_det ON so_det.id = secondary_process.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = secondary_process.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                group by
                    proses,
                    line,
                    no_ws,
                    style,
                    color,
                    size
            ", [
                $start, $end,
                $start, $end,
                $start, $end,
                $start, $end,
                $start, $end,
            ]);
        }

        $this->rowCount = count($rawData) + 4; // 1 for header

        return view('sewing.report.excel.export_report_finishing_proses', [
            'rawData' => $rawData,
            'startDate' => $start_date,
            'endDate' => $end_date,
            'proses' => $proses,
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

                // ===== 1. Format header rows (row 2 and 3) =====
                for ($i = 1; $i <= $columnIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

                    foreach ([5] as $row) {
                        $cell = $colLetter . $row;

                        $sheet->getStyle($cell)->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFD9EDF7'], // Light blue
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FF000000'], // Black text
                            ],
                        ]);
                    }
                }
                // ===== 3. Apply border to whole table =====
                $range = 'A5:' . $highestColumn . $highestRow;
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
