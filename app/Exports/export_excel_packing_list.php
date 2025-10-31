<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;

class export_excel_packing_list implements FromView, ShouldAutoSize, WithEvents, WithCustomCsvSettings
{
    use Exportable;

    protected $po, $buyer, $dest, $styleno;

    public function __construct($po, $buyer, $dest, $styleno)
    {
        $this->po = $po;
        $this->buyer = $buyer;
        $this->dest = $dest;
        $this->styleno = $styleno;
    }

    public function view(): View
    {
        $rawData = DB::select("
            SELECT
                a.po,
                no_carton,
                CONCAT('0', a.barcode) AS sku,
                CONCAT(sd.reff_no, ' ', sd.color, ' ', sd.size) AS short_desc,
                sd.size,
                sd.reff_no,
                sd.color,
                a.qty
            FROM packing_master_packing_list a
            LEFT JOIN ppic_master_so p ON a.id_ppic_master_so = p.id
            LEFT JOIN signalbit_erp.so_det sd ON p.id_so_det = sd.id
            LEFT JOIN signalbit_erp.so so ON sd.id_so = so.id
            LEFT JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
            LEFT JOIN signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
            WHERE a.po = '$this->po'
              AND ms.supplier = '$this->buyer'
              AND sd.dest = '$this->dest'
              AND sd.reff_no = '$this->styleno'
            ORDER BY no_carton ASC
        ");

        return view('packing.export_excel_packing_list', [
            'rawData' => $rawData,
        ]);
    }

    // ✅ Add this method to configure CSV output (remove quotes)
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',  // Use comma
            'enclosure' => '',   // Disable quotes
            'line_ending' => "\n",
            'use_bom' => false,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = 'A1:' . $highestColumn . $highestRow;

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
