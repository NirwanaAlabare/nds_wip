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


class ExportReportDefectReject implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $department, $ws, $rowCount;

    public function __construct($start_date, $end_date, $department, $ws)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->department = $department;
        $this->ws = $ws;
    }

    public function view(): View
    {
        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $department = $this->department;
        $ws = $this->ws;

        $start = $this->start_date . ' 00:00:00';
        $end   = $this->end_date . ' 23:59:59';

        if ($department == "_packing") {
            $rawData = DB::connection('mysql_sb')->select("
                SELECT
                    output_defects_packing.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    userpassword.username AS sewing_line,
                    'FINISHING-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    output_defects_packing.defect_status AS status,
                    DATE_FORMAT(output_defects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                    DATE_FORMAT(output_defects_packing.updated_at, '%d-%m-%Y') AS tgl_rework,
                    output_defect_types.allocation AS proses_type,
                    output_defect_in_out.status AS proses_status,
                    DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                    DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                FROM output_defects_packing
                LEFT JOIN so_det ON so_det.id = output_defects_packing.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN userpassword ON userpassword.username = output_defects_packing.created_by
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects_packing.id
                LEFT JOIN output_defect_types ON output_defect_types.id = output_defects_packing.defect_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects_packing.defect_area_id
                LEFT JOIN output_defect_in_out 
                    ON output_defect_in_out.output_type = 'packing' 
                    AND output_defect_in_out.defect_id = output_defects_packing.id
                WHERE
                    (
                        output_defects_packing.created_at BETWEEN ? AND ?
                        OR
                        output_defects_packing.updated_at BETWEEN ? AND ?
                    )
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                UNION ALL

                SELECT
                    output_rejects_packing.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    output_rejects_packing.created_by AS sewing_line,
                    'FINISHING-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    'REJECT MATI' AS status,
                    DATE_FORMAT(output_rejects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                    '' AS tgl_rework,
                    '' AS proses_type,
                    '' AS proses_status,
                    '' AS tgl_proses_in,
                    '' AS tgl_proses_out
                FROM output_rejects_packing
                LEFT JOIN so_det ON so_det.id = output_rejects_packing.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects_packing.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects_packing.reject_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects_packing.reject_area_id
                WHERE 
                    output_rejects_packing.reject_status = 'mati'
                    AND output_rejects_packing.created_at BETWEEN ? AND ?
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                ORDER BY tgl_defect DESC
            ", [
                $start, $end, $start, $end, $ws, $ws, $ws, // defects
                $start, $end, $ws, $ws, $ws  // rejects
            ]);

        } else if($department == "_end") {

            $rawData = DB::connection('mysql_sb')->select("
                SELECT
                    output_defects.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    userpassword.username AS sewing_line,
                    'END-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    output_defects.defect_status AS status,
                    DATE_FORMAT(output_defects.created_at, '%d-%m-%Y') AS tgl_defect,
                    DATE_FORMAT(output_defects.updated_at, '%d-%m-%Y') AS tgl_rework,
                    output_defect_types.allocation AS proses_type,
                    output_defect_in_out.STATUS AS proses_status,
                    DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                    DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                FROM output_defects
                LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects.id
                LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects.defect_area_id
                LEFT JOIN output_defect_in_out 
                    ON output_defect_in_out.output_type = 'qc'
                    AND output_defect_in_out.defect_id = output_defects.id
                WHERE
                    (
                        output_defects.created_at BETWEEN ? AND ?
                        OR
                        output_defects.updated_at BETWEEN ? AND ?
                    )
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                UNION ALL

                SELECT
                    output_rejects.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    userpassword.username AS sewing_line,
                    'END-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    'REJECT MATI' AS status,
                    DATE_FORMAT(output_rejects.created_at, '%d-%m-%Y') AS tgl_defect,
                    '' AS tgl_rework,
                    '' AS proses_type,
                    '' AS proses_status,
                    '' AS tgl_proses_in,
                    '' AS tgl_proses_out
                FROM output_rejects
                LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects.reject_area_id
                WHERE 
                    output_rejects.reject_status = 'mati'
                    AND output_rejects.created_at BETWEEN ? AND ?
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                ORDER BY tgl_defect DESC
            ", [
                $start, $end, $start, $end, $ws, $ws, $ws, // defects
                $start, $end, $ws, $ws, $ws  // rejects
            ]);

        }else{
            $rawData = DB::connection('mysql_sb')->select("
                SELECT
                    output_defects.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    userpassword.username AS sewing_line,
                    'END-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    output_defects.defect_status AS status,
                    DATE_FORMAT(output_defects.created_at, '%d-%m-%Y') AS tgl_defect,
                    DATE_FORMAT(output_defects.updated_at, '%d-%m-%Y') AS tgl_rework,
                    output_defect_types.allocation AS proses_type,
                    output_defect_in_out.STATUS AS proses_status,
                    DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                    DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                FROM output_defects
                LEFT JOIN so_det ON so_det.id = output_defects.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects.id
                LEFT JOIN output_defect_types ON output_defect_types.id = output_defects.defect_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects.defect_area_id
                LEFT JOIN output_defect_in_out 
                    ON output_defect_in_out.output_type = 'qc'
                    AND output_defect_in_out.defect_id = output_defects.id
                WHERE
                    (
                        output_defects.created_at BETWEEN ? AND ?
                        OR
                        output_defects.updated_at BETWEEN ? AND ?
                    )
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                UNION ALL

                SELECT
                    output_rejects.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    userpassword.username AS sewing_line,
                    'END-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    'REJECT MATI' AS status,
                    DATE_FORMAT(output_rejects.created_at, '%d-%m-%Y') AS tgl_defect,
                    '' AS tgl_rework,
                    '' AS proses_type,
                    '' AS proses_status,
                    '' AS tgl_proses_in,
                    '' AS tgl_proses_out
                FROM output_rejects
                LEFT JOIN so_det ON so_det.id = output_rejects.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects.reject_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects.reject_area_id
                WHERE 
                    output_rejects.reject_status = 'mati'
                    AND output_rejects.created_at BETWEEN ? AND ?
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                UNION ALL

                SELECT
                    output_defects_packing.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    userpassword.username AS sewing_line,
                    'FINISHING-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    output_defects_packing.defect_status AS status,
                    DATE_FORMAT(output_defects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                    DATE_FORMAT(output_defects_packing.updated_at, '%d-%m-%Y') AS tgl_rework,
                    output_defect_types.allocation AS proses_type,
                    output_defect_in_out.status AS proses_status,
                    DATE_FORMAT(output_defect_in_out.created_at, '%d-%m-%Y') AS tgl_proses_in,
                    DATE_FORMAT(output_defect_in_out.reworked_at, '%d-%m-%Y') AS tgl_proses_out
                FROM output_defects_packing
                LEFT JOIN so_det ON so_det.id = output_defects_packing.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN userpassword ON userpassword.username = output_defects_packing.created_by
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_reworks ON output_reworks.defect_id = output_defects_packing.id
                LEFT JOIN output_defect_types ON output_defect_types.id = output_defects_packing.defect_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_defects_packing.defect_area_id
                LEFT JOIN output_defect_in_out 
                    ON output_defect_in_out.output_type = 'packing'
                    AND output_defect_in_out.defect_id = output_defects_packing.id
                WHERE
                    (
                        output_defects_packing.created_at BETWEEN ? AND ?
                        OR
                        output_defects_packing.updated_at BETWEEN ? AND ?
                    )
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                UNION ALL

                SELECT
                    output_rejects_packing.kode_numbering,
                    mastersupplier.Supplier AS buyer,
                    act_costing.kpno AS ws,
                    act_costing.styleno AS style,
                    so_det.color,
                    so_det.size,
                    so_det.dest,
                    output_rejects_packing.created_by AS sewing_line,
                    'FINISHING-LINE' AS dept,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    'REJECT MATI' AS status,
                    DATE_FORMAT(output_rejects_packing.created_at, '%d-%m-%Y') AS tgl_defect,
                    '' AS tgl_rework,
                    '' AS proses_type,
                    '' AS proses_status,
                    '' AS tgl_proses_in,
                    '' AS tgl_proses_out
                FROM output_rejects_packing
                LEFT JOIN so_det ON so_det.id = output_rejects_packing.so_det_id
                LEFT JOIN so ON so.id = so_det.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects_packing.created_by
                LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN output_defect_types ON output_defect_types.id = output_rejects_packing.reject_type_id
                LEFT JOIN output_defect_areas ON output_defect_areas.id = output_rejects_packing.reject_area_id
                WHERE 
                    output_rejects_packing.reject_status = 'mati'
                    AND output_rejects_packing.created_at BETWEEN ? AND ?
                    AND (
                        ? IS NULL 
                        OR ? = '' 
                        OR act_costing.kpno = ?
                    )

                ORDER BY tgl_defect DESC
            ", [
                $start, $end, $start, $end, $ws, $ws, $ws,
                $start, $end, $ws, $ws, $ws,
                $start, $end, $start, $end, $ws, $ws, $ws,
                $start, $end, $ws, $ws, $ws
            ]);
        }

        $this->rowCount = count($rawData) + 5; // 1 for header

        return view('sewing.report.excel.export_report_defect_reject', [
            'rawData' => $rawData,
            'startDate' => $start_date,
            'endDate' => $end_date,
            'department' => $department,
            'ws' => $ws
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

                    foreach ([6] as $row) {
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
                $range = 'A6:' . $highestColumn . $highestRow;
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
