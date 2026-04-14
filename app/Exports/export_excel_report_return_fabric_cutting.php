<?php

namespace App\Exports;

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


class export_excel_report_return_fabric_cutting implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $rowCount;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function view(): View
    {

        $start_date = $this->start_date;
        $end_date = $this->end_date;

        $rawData = DB::connection('mysql_sb')->select("
                    SELECT 
                        DATE_FORMAT(whs_inmaterial_fabric.tgl_dok, '%d-%m-%Y') AS tanggal_keluar,
                        whs_lokasi_inmaterial.no_barcode,
                        whs_lokasi_inmaterial.qty_aktual,
                        whs_lokasi_inmaterial.satuan,
                        CASE 
                            WHEN whs_lokasi_inmaterial.satuan IN ('YRD', 'YARD') 
                                    THEN ROUND(whs_lokasi_inmaterial.qty_aktual * 0.9144, 2)
                            ELSE whs_lokasi_inmaterial.qty_aktual
                        END AS qty_konv,
                        CASE 
                            WHEN whs_lokasi_inmaterial.satuan IN ('YRD', 'YARD') 
                                    THEN 'METER'
                            ELSE whs_lokasi_inmaterial.satuan
                        END AS satuan_konv,
                        CONCAT(whs_lokasi_inmaterial.kode_lok, ' FABRIC WAREHOUSE RACK') AS rak,
                        whs_lokasi_inmaterial.no_dok,
                        whs_inmaterial_fabric.no_invoice,
                        whs_inmaterial_fabric.supplier,
                        whs_lokasi_inmaterial.no_ws,
                        IFNULL(ws.idws_act, '-') AS ws_aktual,
                        whs_lokasi_inmaterial.id_item,
                        buyer_ws.styleno,
                        masteritem.color AS warna,
                        whs_lokasi_inmaterial.no_lot,
                        whs_lokasi_inmaterial.no_roll,
                        whs_lokasi_inmaterial.no_roll_buyer,
                        whs_lokasi_inmaterial.created_by,
                        whs_lokasi_inmaterial.created_at
                    FROM 
                        whs_lokasi_inmaterial 
                    LEFT JOIN whs_inmaterial_fabric 
                        ON whs_inmaterial_fabric.no_dok = whs_lokasi_inmaterial.no_dok
                    LEFT JOIN masteritem 
                        ON masteritem.id_item = whs_lokasi_inmaterial.id_item
                    LEFT JOIN (
                        SELECT
                            jod.id_jo,
                            ac.kpno AS no_ws,
                            ac.styleno,
                            ms.supplier AS buyer
                        FROM act_costing ac
                        INNER JOIN mastersupplier ms 
                            ON ms.id_supplier = ac.id_buyer
                        INNER JOIN so 
                            ON ac.id = so.id_cost
                        INNER JOIN jo_det jod 
                            ON so.id = jod.id_so
                        GROUP BY jod.id_jo, ac.kpno, ac.styleno, ms.supplier
                    ) buyer_ws
                        ON buyer_ws.id_jo = whs_lokasi_inmaterial.id_jo
                    LEFT JOIN (
                        SELECT 
                            bppbno, 
                            idws_act 
                        FROM bppb_req 
                        WHERE 
                        bppbno LIKE '%RQ-F%'
                            AND idws_act IS NOT NULL
                        GROUP BY bppbno
                    ) ws 
                        ON ws.bppbno = whs_inmaterial_fabric.no_invoice
                    WHERE 
                        whs_lokasi_inmaterial.no_dok LIKE 'GK/RI%'
                        AND DATE(whs_inmaterial_fabric.tgl_dok) 
                            BETWEEN ? AND ?
                    ORDER BY whs_lokasi_inmaterial.created_at ASC

                ", [$start_date, $end_date]);


        $this->rowCount = count($rawData) + 3; // 1 for header

        return view('cutting.report.export.export_excel_report_return_fabric_cutting', [
            'rawData' => $rawData,
            'startDate' => $start_date,
            'endDate' => $end_date
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

                    foreach ([4] as $row) {
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
                $range = 'A4:' . $highestColumn . $highestRow;
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
