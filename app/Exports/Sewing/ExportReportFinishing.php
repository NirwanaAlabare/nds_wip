<?php

namespace App\Exports\Sewing;

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

class ExportReportFinishing implements FromView, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    use Exportable;
    protected $tglAwal, $tglAkhir, $kategori, $buyer, $rowCount;

    public function __construct($tglAwal, $tglAkhir, $kategori, $buyer)
    {
        $this->tglAwal = $tglAwal;
        $this->tglAkhir = $tglAkhir;
        $this->kategori = $kategori;
        $this->buyer = $buyer;
    }

    public function view(): View
    {
        $tglAwal = $this->tglAwal;
        $tglAkhir = $this->tglAkhir;
        $kategori = $this->kategori;
        $buyer = $this->buyer;

        if ($kategori == "TERIMA") {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        DATE(a.created_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_secondary_in a
                    INNER JOIN signalbit_erp.output_rfts output ON output.id = a.rft_id
                    INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id 
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
                    ) mb on output.so_det_id = mb.id_so_det
                    WHERE
                        a.created_at >= '{$tglAwal} 00:00:00'
                        AND a.created_at <= '{$tglAkhir} 23:59:59'
                        AND mp.cancel = 'N'
                    GROUP BY so_det_id, DATE(a.created_at)
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        }else if ($kategori == "DEFECT") {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        DATE(a.created_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_secondary_out_defect a
                    INNER JOIN signalbit_erp.output_secondary_out b ON b.id = a.secondary_out_id
                    INNER JOIN signalbit_erp.output_secondary_in c ON c.id = b.secondary_in_id
                    INNER JOIN signalbit_erp.output_rfts output ON output.id = c.rft_id
                    INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id 
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
                    ) mb on output.so_det_id = mb.id_so_det
                    WHERE
                        a.created_at >= '{$tglAwal} 00:00:00'
                        AND a.created_at <= '{$tglAkhir} 23:59:59'
                        AND mp.cancel = 'N'
                        AND a.status = 'defect'
                    GROUP BY so_det_id, DATE(a.created_at)
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        }else if ($kategori == "REWORK") {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        DATE(a.reworked_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_secondary_out_defect a
                    INNER JOIN signalbit_erp.output_secondary_out b ON b.id = a.secondary_out_id
                    INNER JOIN signalbit_erp.output_secondary_in c ON c.id = b.secondary_in_id
                    INNER JOIN signalbit_erp.output_rfts output ON output.id = c.rft_id
                    INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id 
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
                    ) mb on output.so_det_id = mb.id_so_det
                    WHERE
                        a.reworked_at >= '{$tglAwal} 00:00:00'
                        AND a.reworked_at <= '{$tglAkhir} 23:59:59'
                        AND mp.cancel = 'N'
                        AND a.status = 'reworked'
                    GROUP BY so_det_id, DATE(a.reworked_at)
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        }else if ($kategori == "REJECT") {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        DATE(a.created_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_secondary_out_reject a
                    INNER JOIN signalbit_erp.output_secondary_out b ON b.id = a.secondary_out_id
                    INNER JOIN signalbit_erp.output_secondary_in c ON c.id = b.secondary_in_id
                    INNER JOIN signalbit_erp.output_rfts output ON output.id = c.rft_id
                    INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id 
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
                    ) mb on output.so_det_id = mb.id_so_det
                    WHERE
                        a.created_at >= '{$tglAwal} 00:00:00'
                        AND a.created_at <= '{$tglAkhir} 23:59:59'
                        AND mp.cancel = 'N'
                    GROUP BY so_det_id, DATE(a.created_at)
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        }else if ($kategori == "OUTPUT") {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        DATE(a.created_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_secondary_out a
                    INNER JOIN signalbit_erp.output_secondary_in b ON b.id = a.secondary_in_id
                    INNER JOIN signalbit_erp.output_rfts output ON output.id = b.rft_id
                    INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id 
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
                    ) mb on output.so_det_id = mb.id_so_det
                    WHERE
                        a.created_at >= '{$tglAwal} 00:00:00'
                        AND a.created_at <= '{$tglAkhir} 23:59:59'
                        AND mp.cancel = 'N'
                    GROUP BY so_det_id, DATE(a.created_at)
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();
        }

        $this->rowCount = count($data) + 5; // 1 for header

        return view('sewing.report.excel.export_report_finishing', [
            'data' => $data,
            'startDate' => $tglAwal,
            'endDate' => $tglAkhir,
            'kategori' => $kategori,
            'buyer' => $buyer
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00,
        ];
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
