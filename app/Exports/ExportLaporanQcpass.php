<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

// class ExportLaporanPemakaian implements FromCollection
// {
//     /**
//      * @return \Illuminate\Support\Collection
//      */
//     public function collection()
//     {
//         return Marker::all();
//     }
// }

class ExportLaporanQcpass implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    // protected $from, $to;

    public function __construct($id)
    {

        $this->id = $id;
        // $this->to = $to;
        $this->rowCount = 0;
    }


    public function view(): View

    {
        $kode_insp = DB::connection('mysql_sb')->select("select no_insp from whs_qc_insp where id = '".$this->id."'");
        $data_header = DB::connection('mysql_sb')->select("select * from whs_qc_insp where id = '".$this->id."'");
        $data_detail = DB::connection('mysql_sb')->select("select b.id,b.no_lot,a.no_form,a.tgl_form,a.weight_fabric,width_fabric,gramage,a.no_roll,fabric_supp,a.inspektor,no_mesin,c.lenght_barcode, lenght_actual, catatan from whs_qc_insp_det a inner join whs_qc_insp b on b.no_insp = a.no_insp inner join whs_qc_insp_sum c on c.no_form = a.no_form where b.id = '".$this->id."' GROUP BY a.no_roll,a.no_form order by a.no_form asc");
        $data_temuan = DB::connection('mysql_sb')->select("select id,no_form,lenght_fabric,GROUP_CONCAT(kode_def) kode_def,GROUP_CONCAT(ROUND(upto3,0)) upto3,GROUP_CONCAT(ROUND(over3,0)) over3,GROUP_CONCAT(ROUND(over6,0)) over6,GROUP_CONCAT(ROUND(over9,0)) over9,GROUP_CONCAT(width_det) width_det from (select a.id,no_form,lenght_fabric,kode_def,upto3, over3, over6, over9,CONCAT(width_det1,'->',width_det2) width_det  from whs_qc_insp a inner join whs_qc_insp_det b on b.no_insp = a.no_insp where a.id = '".$this->id."') a GROUP BY lenght_fabric,no_form order by no_form asc, lenght_fabric asc");
        $data_sum = DB::connection('mysql_sb')->select("select no_form,upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*,b.*,c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '".$this->id."'");
        $avg_poin = DB::connection('mysql_sb')->select("select ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) avg_poin,IF(ROUND(((ttl_poin * 36 * 100)/ ((akt_width/ttl_width) * akt_lenght)),2) > 15,'-','PASS') status from (select sum(ttl_poin) ttl_poin, COUNT(width_fabric)ttl_width, SUM(width_fabric) akt_width, SUM(l_actual) akt_lenght from (select upto3, over3,over6,over9,width_fabric,l_actual,ttl_poin,round((x/(width_fabric * l_actual)),2) akt_poin from (select a.*, b.*, c.*, (upto3 + over3 + over6 + over9) ttl_poin, ((upto3 + over3 + over6 + over9) * 36 * 100) x , b.lenght_actual l_actual,d.id id_h from (select no_insp, (COALESCE(SUM(upto3),0) * 1) upto3, (COALESCE(SUM(over3),0) * 2 ) over3, (COALESCE(SUM(over6),0) * 3) over6, (COALESCE(SUM(over9),0) * 4) over9,no_form from whs_qc_insp_det GROUP BY no_form) a inner join (select no_form noform,lenght_actual from whs_qc_insp_sum) b on b.noform = a.no_form inner join (select no_form form_no,ROUND(sum(width_det2)/COUNT(width_det2),2) width_fabric from (select no_form,width_det2 from whs_qc_insp_det where width_det2 is not null) a GROUP BY no_form) c on c.form_no = a.no_form inner join whs_qc_insp d on d.no_insp = a.no_insp) a where id_h = '".$this->id."') a) a");

        $this->rowCount = count($data_temuan) + 8;


        return view('qc-pass.excel.export-qcpass', ['kode_insp' => $kode_insp,'data_header' => $data_header,'data_detail' => $data_detail,'data_temuan' => $data_temuan,'data_sum' => $data_sum,'avg_poin' => $avg_poin, 'page' => 'dashboard-warehouse']);


        // return view('lap_pemakaian.export', [
        //     'data' => $data,
        //     'from' => $this->from,
        //     'to' => $this->to
        // ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }



    public static function afterSheet(AfterSheet $event)
    {

        $event->sheet->styleCells(
            'A9:G' . $event->getConcernable()->rowCount,
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]
        );
    }
}
