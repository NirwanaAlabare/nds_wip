<?php

namespace App\Exports;

use App\Services\WorkTrackerService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class WorkTrackerExport implements FromArray, WithHeadings, WithEvents, WithTitle, ShouldAutoSize
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'Work Tracker';
    }

    public function headings(): array
    {
        return [
            'No', 'Kode', 'Jenis', 'Kerjaan', 'Modul', 'Programmer', 'Peminta', 'Departemen', 'Level', 'Status',
            'Progress (%)', 'Mulai', 'Target', 'Selesai', 'Prediksi Selesai', 'Estimasi (hari)', 'Kondisi', 'Keterangan', 'Alasan Ditolak', 'Catatan',
        ];
    }

    public function array(): array
    {
        return collect($this->rows)->values()->map(function ($row, $index) {
            return [
                $index + 1,
                $row['code'],
                WorkTrackerService::TYPE_LABEL[$row['type']] ?? $row['type'],
                $this->text($row['title']),
                $this->text($row['module']),
                $this->text($row['programmer_name'] ?: 'Belum ditugaskan'),
                $this->text($row['requester']),
                $this->text($row['requester_dept']),
                WorkTrackerService::LEVEL_LABEL[$row['level']] ?? '',
                WorkTrackerService::STATUS_LABEL[$row['status']] ?? $row['status'],
                (int) $row['progress'],
                $this->date($row['start_date']),
                $this->date($row['target_date']),
                $this->date($row['finished_at']),
                $this->date($row['predicted_date']),
                $row['estimate_days'] !== null ? (float) $row['estimate_days'] : '',
                WorkTrackerService::HEALTH_LABEL[$row['health']] ?? $row['health'],
                $this->text($row['message']),
                $this->text($row['reject_reason']),
                $this->text($row['description']),
            ];
        })->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '082149']],
                ]);
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D5DBE5']]],
                ]);
            },
        ];
    }

    protected function date($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y') : '';
    }

    // Teks isian user tidak boleh terbaca sebagai formula saat file dibuka di Excel
    protected function text($value)
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? ' ' . $value : $value;
    }
}
