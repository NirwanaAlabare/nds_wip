<?php

namespace App\Exports\Packing;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportReportOutputPackingLine implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $tglAwal, $tglAkhir, $tipe, $buyer, $rowCount;

    public function __construct($tglAwal, $tglAkhir, $tipe, $buyer)
    {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
        $this->tipe = $tipe;
        $this->buyer = $buyer;
    }

    public function view(): View
    {
        $tglAwal = $this->tglAwal;
        $tglAkhir = $this->tglAkhir;
        $tipe = strtolower($this->tipe);
        $buyer = $this->buyer;

        if($tipe != ''){
            $data = DB::table(DB::raw("
                (
                        select so_det_id, buyer, ws, styleno, color, size, type, tgl, SUM(jumlah) jumlah from (SELECT
                            so_det_id,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            a.type,
                            DATE(created_at) AS tgl,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_rfts_packing_po a
                        INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
                        LEFT JOIN (
                            SELECT
                            sd.id as id_so_det,
                            ac.kpno as ws,
                            supplier as buyer,
                            styleno,
                            color,
                            size,
                            dest
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on a.so_det_id = mb.id_so_det
                        WHERE
                            created_at >= '{$tglAwal} 00:00:00'
                            AND created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                        GROUP BY so_det_id, a.type, DATE(created_at)
                                                
                                                UNION ALL
                                                select '-' so_det_id, buyer, ws, styleno, color, size, 'rft' type, tgl_saldo tgl, COALESCE(packing_rft,0) jumlah from signalbit_erp.inject_mutasi_sewing where type_saldo = 'PACKING' and tgl_saldo >= '{$tglAwal} 00:00:00' AND tgl_saldo <= '{$tglAkhir} 23:59:59') a GROUP BY buyer, ws, styleno, color, size, type, tgl
                    ) as results
            "))
            ->when($tipe, function ($query) use ($tipe) {
                return $query->where('results.type', $tipe);
            })
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else {
            $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0')->get();
        }

        $this->rowCount = count($data) + 5; // 1 for header

        return view('packing.export.export_report_output_packing_line', [
            'data' => $data,
            'startDate' => $tglAwal,
            'endDate' => $tglAkhir,
            'tipe' => $this->tipe,
            'buyer' => $buyer
        ]);
    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'F' => NumberFormat::FORMAT_NUMBER_00,
    //     ];
    // }

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
