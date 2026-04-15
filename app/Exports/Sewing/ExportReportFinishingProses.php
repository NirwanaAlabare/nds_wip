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
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class ExportReportFinishingProses implements FromView, ShouldAutoSize, WithEvents
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

        if ($department == "_packing") {
            $rawData = DefectPacking::selectRaw("
                output_defects_packing.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size
            ")
            ->leftJoin("so_det", "so_det.id", "=", "output_defects_packing.so_det_id")
            ->leftJoin("so", "so.id", "=", "so_det.id_so")
            ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
            ->leftJoin("userpassword", "userpassword.username", "=", "output_defects_packing.created_by")
            ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")
            ->when($ws != "", function ($q) use ($ws) {
                $q->where('act_costing.kpno', $ws);
            })
            ->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('output_defects_packing.created_at', [$start_date.' 00:00:00', $end_date.' 23:59:59'])
                ->orWhereBetween('output_defects_packing.updated_at', [$start_date.' 00:00:00', $end_date.' 23:59:59']);
            })
            ->orderBy("output_defects_packing.updated_at", "desc")
            ->get();
        } else {
            $rawData = Defect::selectRaw("
                output_defects.kode_numbering,
                mastersupplier.Supplier buyer,
                act_costing.kpno ws,
                act_costing.styleno style,
                so_det.color,
                so_det.size
            ")
            ->leftJoin("so_det", "so_det.id", "=", "output_defects.so_det_id")
            ->leftJoin("so", "so.id", "=", "so_det.id_so")
            ->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")
            ->leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_defects.created_by")
            ->leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")
            ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")
            ->when($ws != "", function ($q) use ($ws) {
                $q->where('act_costing.kpno', $ws);
            })
            ->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('output_defects.created_at', [$start_date.' 00:00:00', $end_date.' 23:59:59'])
                ->orWhereBetween('output_defects.updated_at', [$start_date.' 00:00:00', $end_date.' 23:59:59']);
            })
            ->orderBy("output_defects.updated_at", "desc")
            ->get();
        }

        $this->rowCount = count($rawData) + 5; // 1 for header

        return view('sewing.report.excel.export_report_finishing_proses', [
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
