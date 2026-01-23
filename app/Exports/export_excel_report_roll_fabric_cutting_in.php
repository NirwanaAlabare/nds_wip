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


class export_excel_report_roll_fabric_cutting_in implements FromView, ShouldAutoSize, WithEvents
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

        $rawData = DB::select("SELECT
a.no_bppb,
DATE_FORMAT(tgl_bppb, '%d-%M-%Y') AS tgl_bppb_fix,
tgl_bppb,
no_req,
id_roll,
no_roll,
no_roll_buyer,
no_lot,
mi.id_item,
no_ws,
no_ws_aktual,
styleno,
mi.itemdesc,
mi.color,
qty_out,
satuan,
        CASE
            WHEN satuan = 'YRD' THEN ROUND(qty_out * 0.9144,2)
            ELSE qty_out
        END as qty_out_konversi,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan_konversi
FROM signalbit_erp.whs_bppb_h a
inner join signalbit_erp.whs_bppb_det b on a.no_bppb = b.no_bppb
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
LEFT JOIN (SELECT
						jd.id_jo,
						ac.kpno,
            supplier as buyer,
            styleno
				FROM signalbit_erp.jo_det jd
				INNER JOIN signalbit_erp.so ON jd.id_so = so.id
				INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
				WHERE jd.cancel = 'N'
				GROUP BY jd.id_jo) k on a.no_ws_aktual = k.kpno
where tgl_bppb >= '$start_date' and tgl_bppb <= '$end_date' and tujuan = 'Production - Cutting' and b.status = 'Y'

    ");


        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('cutting.report.export.export_excel_report_roll_fabric_cutting_in', [

            'rawData' => $rawData,
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

                    foreach ([2] as $row) {
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
                $range = 'A1:' . $highestColumn . $highestRow;
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
