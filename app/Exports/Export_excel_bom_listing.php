<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use DB;

class Export_excel_bom_listing implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $id, $rowCount;

    public function __construct($id) {
         $this->id = $id;
    }

   public function view(): View
    {
        $mysql_sb = DB::connection('mysql_sb');

        $query = $mysql_sb->table('bom_marketing as bm')
            ->leftJoin('bom_marketing_detail as d', 'bm.id', '=', 'd.id_bom_marketing')
            ->leftJoin('signalbit_erp.masteritem as i', 'd.id_item', '=', 'i.id_item')
            ->leftJoin('master_colors_gmt as c', 'd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'd.id_size', '=', 's.id')
            ->leftJoin('signalbit_erp.mastercontents as e', 'd.id_contents', '=', 'e.id')
            ->leftJoin('signalbit_erp.mastertype2 as d2', 'e.id_type', '=', 'd2.id')
            ->leftJoin('signalbit_erp.mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
            ->leftJoin('signalbit_erp.mastergroup as a', 's_grp.id_group', '=', 'a.id')
            ->leftJoin('signalbit_erp.mastercf as mfg', 'd.id_contents', '=', 'mfg.id')
            ->leftJoin('signalbit_erp.masterpilihan as u', 'd.id_unit', '=', 'u.id')
            ->leftJoin('signalbit_erp.masterpilihan as cur', 'd.id_currency', '=', 'cur.id')
            ->leftJoin('signalbit_erp.mastersupplier as supp', 'bm.id_buyer', '=', 'supp.id_Supplier')
            ->select(
                'supp.Supplier as buyer',
                'bm.style',
                'bm.market',
                DB::raw("
                    CASE
                        WHEN d.category = 'Manufacturing'
                        THEN CONCAT(i.itemdesc, ' ', i.color, ' ', i.size, ' ', i.add_info)
                        ELSE i.itemdesc
                    END as item_name
                "),
                DB::raw("
                    CASE
                        WHEN d.category = 'Manufacturing'
                        THEN CONCAT(mfg.cfcode, ' ', mfg.cfdesc)
                        ELSE CONCAT(e.id, ' ', a.nama_group, ' ', s_grp.nama_sub_group, ' ', d2.nama_type, ' ', e.nama_contents)
                    END as content_name
                "),
                'd.qty',
                'c.name as color_name',
                's.size as size_name',
                'u.nama_pilihan as unit_name',
                'cur.nama_pilihan as currency',
                'd.shell',
                'd.category',
                'd.price'
            )
            ->where('bm.id', $this->id)
            ->orderBy('d.id', 'desc');

        $data = $query->get();
        $this->rowCount = count($data);

        return view('marketing.bom.export_excel_listing', [
            'data' => $data
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:N1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center');

                $event->sheet->getStyle('A2:N2')->getFont()->setBold(true);

                $lastRow = $this->rowCount + 2;
                $event->sheet->getStyle('A1:N' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
