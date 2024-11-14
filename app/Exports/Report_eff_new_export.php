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
use Carbon\Carbon;
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

class Report_eff_new_export implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;


    protected $tgl_awal, $tgl_akhir;

    public function __construct($tgl_awal, $tgl_akhir)
    {

        $this->tgl_awal = $tgl_awal . ' 00:00:00';
        $this->tgl_akhir = $tgl_akhir . ' 23:59:59';

        $this->rowCount = 0;
    }


    public function view(): View

    {
        $data = DB::connection('mysql_sb')->select("SELECT
a.tgl_trans,
concat((DATE_FORMAT(tgl_trans,  '%d')), '-',left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')) tgl_trans_fix,
concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
u.name sewing_line,
ms.supplier buyer,
ac.kpno,
ac.styleno,
mp.color,
mp.id,
mp.smv,
mp.man_power,
mp.jam_kerja_awal,
istirahat,
op.jam_akhir_input_line,
round(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600,2) AS jam_kerja_act_line,
u.name sewing_line,
round(((((a.tot_output / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)) * 60) * mp.man_power) / mp.smv) target,
a.tot_output,
d_rfts.tot_rfts,
op.tot_output_line,
so.curr,
acm.price cm_price,
REPLACE(FORMAT(a.tot_output * acm.price, 2), '.', ',') AS earning,
round((mp.man_power * (a.tot_output / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
round(a.tot_output * mp.smv,2) mins_prod,
round((((a.tot_output * mp.smv) / ( (mp.man_power * (a.tot_output / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line,
round(((a.tot_output / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)),2) jam_kerja_act,
round((d_rfts.tot_rfts / a.tot_output) * 100,2) rfts
 from
(
    select
    date(updated_at)tgl_trans,
    so_det_id,
    master_plan_id,
    count(so_det_id) tot_output,
    time(max(a.updated_at)) jam_akhir_input,
    created_by
    from output_rfts a
    where updated_at >= '$this->tgl_awal' and updated_at <= '$this->tgl_akhir'
    group by master_plan_id, created_by, date(updated_at)
)		a
inner join so_det sd on a.so_det_id = sd.id
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join user_sb_wip u on a.created_by = u.id
inner join master_plan mp on a.master_plan_id = mp.id
inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
left join (
select date(updated_at) tgl_trans_line,max(time(updated_at)) jam_akhir_input_line,count(so_det_id) tot_output_line,
    case
    when time(max(updated_at)) >= '12:00:00' and time(max(updated_at)) <= '18:44:59' THEN '01:00:00'
    when time(max(updated_at)) <= '12:00:00'  THEN '00:00:00'
    when time(max(updated_at)) >= '18:45:00'  THEN '01:30:00'
    END as istirahat,
created_by from output_rfts where updated_at >= '$this->tgl_awal' and updated_at <= '$this->tgl_akhir' group by created_by, date(updated_at)
) op on a.tgl_trans = op.tgl_trans_line and a.created_by = op.created_by
left join (
select * from act_costing_mfg where id_item = '8' group by id_act_cost
) acm on ac.id = acm.id_act_cost
left join (
 SELECT
master_plan_id,
 tgl_trans_rfts,
 sum(tot_rfts)tot_rfts
 from
(
    select
    date(updated_at)tgl_trans_rfts,
    master_plan_id,
    count(so_det_id) tot_rfts,
    created_by
    from output_rfts a
    where updated_at >= '$this->tgl_awal' and updated_at <= '$this->tgl_akhir' and status = 'NORMAL'
    group by master_plan_id, created_by, date(updated_at)
)		a
inner join master_plan mp on a.master_plan_id = mp.id
group by tgl_trans_rfts, master_plan_id
) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
order by a.tgl_trans asc, u.name asc
        ");


        $this->rowCount = count($data) + 4;


        return view(
            'sewing.export.report_efficiency_new_export',
            [
                'data' => $data,
                'tgl_awal_n' => $this->tgl_awal,
                'tgl_akhir_n' => $this->tgl_akhir
            ]
        );
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
            'A4:R' . $event->getConcernable()->rowCount,
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
