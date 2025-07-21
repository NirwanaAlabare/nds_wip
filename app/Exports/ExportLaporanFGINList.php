<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class ExportLaporanFGINList implements FromView, ShouldAutoSize, WithEvents
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
        $data = DB::select("
            SELECT
                no_sb,
                tgl_penerimaan,
                CONCAT(DATE_FORMAT(a.tgl_penerimaan, '%d'), '-', LEFT(DATE_FORMAT(a.tgl_penerimaan, '%M'), 3), '-', DATE_FORMAT(a.tgl_penerimaan, '%Y')) AS tgl_penerimaan_fix,
                a.po,
                a.barcode,
                a.id_so_det,
                buyer,
                ws,
                color,
                size,
                a.qty,
                m.dest,
                a.no_carton,
                a.notes,
                a.created_at,
                a.created_by
            FROM fg_fg_in a
            INNER JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
            INNER JOIN master_sb_ws m ON p.id_so_det = m.id_so_det
            WHERE tgl_penerimaan >= ? AND tgl_penerimaan <= ? AND a.status = 'NORMAL'
            ORDER BY a.created_at DESC
        ", [$this->from, $this->to]);

        $this->rowCount = count($data) + 1; // 1 for header

        return view('finish_good.export_finish_good_penerimaan_list', [
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $endRow = $this->rowCount;
                $event->sheet->getDelegate()->getStyle("A1:P{$endRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'size' => 10,
                    ],
                ]);
            },
        ];
    }
}
