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


class export_excel_loading_out implements FromView, ShouldAutoSize, WithEvents
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
no_form,
tgl_form,
DATE_FORMAT(tgl_form, '%d-%b-%Y') AS tgl_form_fix,
ph.pono,
ms.supplier,
a.jns_dok,
a.jns_pengeluaran,
a.berat_panel,
a.berat_karung,
a.ket,
mi.itemdesc,
sum(b.qty) as qty,
b.id_qr_stocker,
b.no_karung,
ac.kpno,
ac.styleno,
sd.color,
sd.size,
mb.supplier buyer
from wip_out a
left join wip_out_det b on a.id = b.id_wip_out
left join signalbit_erp.po_header ph on a.id_po = ph.id
left join stocker_input si on b.id_qr_stocker = si.id_qr_stocker
left join part_detail p on si.part_detail_id = p.id
left join part_detail_item pdi on p.id = pdi.part_detail_id
left join signalbit_erp.bom_jo_item k on pdi.bom_jo_item_id = k.id
left join signalbit_erp.masteritem mi on k.id_item = mi.id_item
left join signalbit_erp.mastersupplier ms on ph.id_supplier = ms.Id_Supplier
left join signalbit_erp.so_det sd on si.so_det_id = sd.id
left join signalbit_erp.so on sd.id_so = so.id
left join signalbit_erp.act_costing ac on so.id_cost = ac.id
left join signalbit_erp.mastersupplier mb on ac.id_buyer = mb.id_supplier
where tgl_form >= '$start_date' and tgl_form <= '$end_date'
group by no_form, b.id_qr_stocker
order by no_form asc, tgl_form asc, a.created_at desc
    ");


        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('dc.loading_out.export_excel_loading_out', [

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
