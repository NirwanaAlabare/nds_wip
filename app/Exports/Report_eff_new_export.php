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
    tgl_trans,
    concat((DATE_FORMAT(tgl_trans,  '%d')), '-',left(DATE_FORMAT(tgl_trans,  '%M'),3),'-',DATE_FORMAT(tgl_trans,  '%Y')) tgl_trans_fix,
    u.name sewing_line,
    ac.kpno,
    ms.Supplier buyer,
    ac.styleno,
    mp.smv,
    mp.man_power,
	ROUND(mp.man_power * ROUND(((sum(tot_output)/b.tot_output_line) * ROUND(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(MAX(b.jam_akhir_input), MIN(jam_kerja_awal)), MAX(a.istirahat))) / 3600,2)),2) * 60 / mp.smv,0) set_target,
    min(jam_kerja_awal)jam_kerja_awal,
    b.jam_akhir_input,
    max(istirahat) istirahat,
    ROUND(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(MAX(b.jam_akhir_input), MIN(jam_kerja_awal)), MAX(a.istirahat))) / 3600, 1) AS jam_kerja_line,
    ROUND(((sum(tot_output)/b.tot_output_line) * ROUND(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(MAX(b.jam_akhir_input), MIN(jam_kerja_awal)), MAX(a.istirahat))) / 3600,2)),2) jam_kerja_act,
    ROUND(mp.man_power * ROUND(((sum(tot_output)/b.tot_output_line) * ROUND(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(MAX(b.jam_akhir_input), MIN(jam_kerja_awal)), MAX(a.istirahat))) / 3600,2)),2) *60,0) min_available,
    ROUND(sum(tot_output) * mp.smv,0) min_prod,
    concat(ROUND(ROUND(sum(tot_output) * mp.smv,0) / ROUND(mp.man_power * ROUND(((sum(tot_output)/b.tot_output_line) * ROUND(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(MAX(b.jam_akhir_input), MIN(jam_kerja_awal)), MAX(a.istirahat))) / 3600,2)),2) *60,0) * 100,2), ' %') eff,
    sum(tot_output) output,
    b.tot_output_line,
    sd.price,
    ac.curr,
    acm.price cm_price,
    round(sum(tot_output) * acm.price,2) earning
    from
    (
    select
    date(updated_at)tgl_trans,
    so_det_id,
    master_plan_id,
    count(so_det_id) tot_output,
    time(max(a.updated_at)) jam_akhir_input,
    case
    when time(max(a.updated_at)) >= '12:00:00' and time(max(a.updated_at)) <= '18:44:59' THEN '01:00:00'
    when time(max(a.updated_at)) <= '12:00:00'  THEN '00:00:00'
    when time(max(a.updated_at)) >= '18:45:00'  THEN '01:30:00'
    END as istirahat,
    created_by
    from output_rfts a
    where updated_at >= '$this->tgl_awal' and updated_at <= '$this->tgl_akhir'
    group by master_plan_id, created_by, date(updated_at)
    ) a
    inner join master_plan mp on a.master_plan_id = mp.id
    inner join so_det sd on a.so_det_id = sd.id
    inner join so on sd.id_so = so.id
    inner join act_costing ac on so.id_cost = ac.id
    inner join user_sb_wip u on a.created_by = u.id
    inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
    left join
    (
    select * from act_costing_mfg where id_item = '8' group by id_act_cost
    ) acm on ac.id = acm.id_act_cost
    left join
    (
    select date(updated_at) tgl_b,created_by,max(time(a.updated_at)) jam_akhir_input, count(so_det_id) tot_output_line
    from output_rfts a where updated_at >= '$this->tgl_awal' and updated_at <= '$this->tgl_akhir' group by created_by, date(updated_at)
    ) b on a.created_by = b.created_by and a.tgl_trans = b.tgl_b
    group by ac.kpno, ac.styleno,u.name, a.tgl_trans
    order by tgl_trans asc, name asc, kpno asc
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
            'A4:P' . $event->getConcernable()->rowCount,
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
