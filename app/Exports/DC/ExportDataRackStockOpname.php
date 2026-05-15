<?php

namespace App\Exports\DC;

use DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportDataRackStockOpname implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnFormatting,
    WithColumnWidths,
    WithEvents
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from ?: date('Y-m-d');
        $this->to = $to ?: date('Y-m-d');
    }

    public function query()
    {
        return DB::table('rack_detail')
            ->leftJoin(
                'rack_detail_stocker',
                'rack_detail_stocker.detail_rack_id',
                '=',
                'rack_detail.id'
            )
            ->leftJoin(
                'stocker_input',
                'stocker_input.id_qr_stocker',
                '=',
                'rack_detail_stocker.stocker_id'
            )
            ->leftJoin(
                'form_cut_input',
                'form_cut_input.id',
                '=',
                'stocker_input.form_cut_id'
            )
            ->leftJoin(
                'marker_input',
                'marker_input.kode',
                '=',
                'form_cut_input.id_marker'
            )
            ->leftJoin(
                'part_detail',
                'part_detail.id',
                '=',
                'stocker_input.part_detail_id'
            )
            ->leftJoin(
                'master_part',
                'master_part.id',
                '=',
                'part_detail.master_part_id'
            )
            ->leftJoin(
                'master_sb_ws',
                'master_sb_ws.id_so_det',
                '=',
                'stocker_input.so_det_id'
            )
            ->where('rack_detail_stocker.status', 'active')
            ->select([
                'rack_detail.nama_detail_rak as no_rak',
                'stocker_input.id_qr_stocker as no_stocker',
                'marker_input.act_costing_ws as no_ws',
                'form_cut_input.no_cut',
                'marker_input.style',
                'marker_input.color',
                'master_part.nama_part as part',
                DB::raw('COALESCE(master_sb_ws.size, stocker_input.size) as size'),
                'rack_detail_stocker.qty_in',
                'rack_detail_stocker.updated_at'
            ])
            ->orderBy('rack_detail.nama_detail_rak', 'asc');
    }

    public function headings(): array
    {
        return [
            'No Rak',
            'No Stocker',
            'No WS',
            'No Cut',
            'Style',
            'Color',
            'Part',
            'Size',
            'Qty',
            'Tgl Scan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->no_rak,
            $row->no_stocker,
            $row->no_ws,
            $row->no_cut,
            $row->style,
            $row->color,
            $row->part,
            $row->size,
            $row->qty_in,
            date('d-m-Y', strtotime($row->updated_at)),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 15,
            'C' => 20,
            'D' => 10,
            'E' => 45,
            'F' => 25,
            'G' => 25,
            'H' => 10,
            'I' => 10,
            'J' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                $sheet->insertNewRowBefore(1, 2);

                $sheet->mergeCells('A1:J1');

                $sheet->setCellValue('A1', 'Laporan Data Stock Opname');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle('A3:J3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => [
                            'rgb' => 'FFFFFF',
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => '4472C4',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A3:J{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $sheet->getStyle("A3:J{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);
            },
        ];
    }
}