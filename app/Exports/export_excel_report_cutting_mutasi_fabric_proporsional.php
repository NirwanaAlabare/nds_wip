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


class export_excel_report_cutting_mutasi_fabric_proporsional implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $cbotipe, $rowCount;

    public function __construct($start_date, $end_date, $cbotipe)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->cbotipe = $cbotipe;
    }

    public function view(): View
    {

        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $tipe = $this->cbotipe;
        $prev_date = date('Y-m-d', strtotime($start_date . ' -1 day'));

        if ($tipe == 'Barcode') {
            $barcode = 'id_roll as barcode';
            $group = 'group by id_roll, ws';
        } else {
            $barcode = 'NULL as barcode';
            $group = 'group by id_item, ws';
        }

        $rawData = DB::select("SELECT
ws,
buyer,
styleno,
color,
$barcode,
mut.id_item,
mi.itemdesc,
ROUND(SUM(saldo_awal), 2) AS saldo_awal,
ROUND(SUM(qty_in), 2) AS penerimaan,
ROUND(SUM(qty_pakai), 2) AS pemakaian,
ROUND(SUM(sr), 2) AS short_roll,
ROUND(SUM(gr_p), 2) AS gr_panel,
ROUND(SUM(gr_g), 2) AS gr_set,
ROUND(SUM(qty_retur), 2) AS retur,
ROUND(
SUM(saldo_awal)
    + SUM(qty_in)
    - SUM(qty_pakai)
    + SUM(sr)
    - SUM(gr_p)
    - SUM(gr_g)
    - SUM(qty_retur)
, 2) AS saldo_akhir,
satuan
from
(
select
ws, id_roll, id_item, 0 saldo_awal, sum(qty_in) qty_in, sum(qty_pakai) qty_pakai, sum(sr) sr,sum(gr_p) gr_p,sum(gr_g) gr_g,sum(qty_retur) qty_retur, sum(saldo) as saldo_akhir,satuan
from mut_cut_fab_saldo_tmp where tgl_trans >= '$start_date' and tgl_trans <= '$end_date'
$group
UNION ALL
select
ws, id_roll, id_item, sum(saldo) saldo_awal, 0,0,0,0,0,0,0,satuan
from mut_cut_fab_saldo_tmp where tgl_trans = '$prev_date'
$group
) mut
LEFT JOIN signalbit_erp.masteritem mi on mut.id_item = mi.id_item
LEFT JOIN (
SELECT
		ac.kpno,
        supplier as buyer,
        styleno
		FROM signalbit_erp.jo_det jd
		INNER JOIN signalbit_erp.so ON jd.id_so = so.id
		INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
		WHERE jd.cancel = 'N'
		GROUP BY jd.id_jo
) k on mut.ws = k.kpno
$group
order by ws asc, color asc
    ");


        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('cutting.report.export.export_excel_report_mutasi_fabric_proporsional', [
            'rawData' => $rawData,
            'tipe' => $tipe,
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
