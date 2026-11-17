<?php

namespace App\Exports;

use App\Http\Controllers\General\ActionPointController;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ActionPointExport implements FromArray, WithHeadings, WithEvents, WithTitle, WithColumnWidths
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'Action Points';
    }

    public function headings(): array
    {
        return [
            'No', 'Jenis', 'Tanggal', 'Judul Meeting / Lokasi', 'Peserta', 'Bagian', 'Poin', 'Kategori', 'Prioritas',
            'PIC', 'Target', 'Status', 'Kondisi', 'Tindak Lanjut Terakhir', 'Tgl Selesai', 'Jumlah Foto', 'Riwayat',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 14, 'C' => 11, 'D' => 28, 'E' => 26, 'F' => 14, 'G' => 50, 'H' => 14, 'I' => 10,
            'J' => 16, 'K' => 11, 'L' => 10, 'M' => 16, 'N' => 40, 'O' => 11, 'P' => 8, 'Q' => 60,
        ];
    }

    public function array(): array
    {
        return collect($this->rows)->values()->map(function ($row, $index) {
            return [
                $index + 1,
                ActionPointController::JENIS[$row->jenis] ?? $row->jenis,
                $this->date($row->tanggal),
                $this->text($row->judul),
                $this->text($row->peserta),
                $this->text($row->bagian),
                $this->text($row->poin),
                $this->text($row->kategori),
                ActionPointController::PRIORITAS[$row->prioritas] ?? $row->prioritas,
                $this->text($row->pic),
                $this->date($row->target_date),
                ActionPointController::STATUS[$row->status] ?? $row->status,
                $this->condition($row),
                $this->text($row->tindak_lanjut),
                $this->date($row->done_date),
                count($row->foto_list),
                $this->text($row->history),
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
                    'alignment' => ['vertical' => 'top', 'wrapText' => true],
                ]);

                foreach (collect($this->rows)->values() as $index => $row) {
                    if ($row->overdue) {
                        $sheet->getStyle('M' . ($index + 2))->getFont()->getColor()->setRGB('C21F30');
                    }
                }
            },
        ];
    }

    protected function condition($row)
    {
        if ($row->status === 'done' || $row->status === 'batal') {
            return '';
        }
        if ($row->days_left === null) {
            return 'Tanpa target';
        }
        if ($row->days_left < 0) {
            return 'Lewat ' . abs($row->days_left) . ' hari';
        }

        return $row->days_left === 0 ? 'Jatuh tempo hari ini' : 'Sisa ' . $row->days_left . ' hari';
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
