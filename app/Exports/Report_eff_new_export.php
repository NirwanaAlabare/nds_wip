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


    protected $tgl_awal, $tgl_akhir, $tgl_awal_n, $tgl_akhir_n;

    public function __construct($tgl_awal, $tgl_akhir)
    {

        $this->tgl_awal = $tgl_awal . ' 00:00:00';
        $this->tgl_akhir = $tgl_akhir . ' 23:59:59';
        $this->tgl_awal_n = $tgl_awal;
        $this->tgl_akhir_n = $tgl_akhir;

        $this->rowCount = 0;
    }


    public function view(): View
    {
        $data = DB::connection('mysql_sb')->select("
            SELECT
                    a.tgl_trans,
                    concat((DATE_FORMAT(a.tgl_trans,  '%d')), '-',left(DATE_FORMAT(a.tgl_trans,  '%M'),3),'-',DATE_FORMAT(a.tgl_trans,  '%Y')) tgl_trans_fix,
                    concat((DATE_FORMAT(mp.tgl_plan,  '%d')), '-',left(DATE_FORMAT(mp.tgl_plan,  '%M'),3),'-',DATE_FORMAT(mp.tgl_plan,  '%Y')) tgl_plan_fix,
                    ul.username sewing_line,
                    ms.supplier buyer,
                    ac.kpno,
                    ac.styleno,
                    mp.color,
                    mp.id,
                    mp.smv,
                    mp.man_power man_power_ori,
                    cmp.man_power,
                    mp.jam_kerja_awal,
                    istirahat,
                    op.jam_akhir_input_line,
                    round(TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600,2) AS jam_kerja_act_line,
                    round(((((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)) * 60) * cmp.man_power) / mp.smv) target,
                    sum(a.tot_output) tot_output,
                    sum(d_rfts.tot_rfts) tot_rfts,
                    op.tot_output_line,
                    so.curr,
                    CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * COALESCE(konv_sb.rate_jual, last_konv_sb.rate_jual), acm.price)
                    ELSE acm.price end AS cm_price,
                    round(
                    sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * COALESCE(konv_sb.rate_jual, last_konv_sb.rate_jual), acm.price)
                    ELSE acm.price end,2) AS earning,
                    COALESCE(mr.kurs_tengah,mkb.kurs_tengah) kurs_tengah,
                    round(
                    if (so.curr = 'IDR',
                    sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * COALESCE(konv_sb.rate_jual, last_konv_sb.rate_jual), acm.price)
                    ELSE acm.price end,
                    sum(a.tot_output) * CASE when so.curr = 'IDR' THEN if(acm.jenis_rate = 'J', acm.price * COALESCE(konv_sb.rate_jual, last_konv_sb.rate_jual), acm.price)
                    ELSE acm.price end * COALESCE(mr.kurs_tengah,mkb.kurs_tengah)
                    ),2) tot_earning_rupiah,
                    round((cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60),2) mins_avail,
                    round(sum(a.tot_output) * mp.smv,2) mins_prod,
                    round((((sum(a.tot_output) * mp.smv) / ( (cmp.man_power * (sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600) * 60)))*100),2) eff_line,
                    round(((sum(a.tot_output) / op.tot_output_line) * (TIME_TO_SEC(TIMEDIFF(TIMEDIFF(jam_akhir_input_line, istirahat), mp.jam_kerja_awal)) / 3600)),2) jam_kerja_act,
                    round((sum(d_rfts.tot_rfts) / sum(a.tot_output)) * 100,2) rfts
                from
                (
                    select
                    date(a.updated_at)tgl_trans,
                    so_det_id,
                    master_plan_id,
                    count(so_det_id) tot_output,
                    time(max(a.updated_at)) jam_akhir_input,
                    userpassword.username
                    from output_rfts a
                    left join user_sb_wip on user_sb_wip.id = a.created_by
                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where a.updated_at >= '".$this->tgl_awal."' and a.updated_at <= '".$this->tgl_akhir."'
                    group by master_plan_id, userpassword.username, date(a.updated_at)
                ) a
                inner join so_det sd on a.so_det_id = sd.id
                inner join so on sd.id_so = so.id
                inner join act_costing ac on so.id_cost = ac.id
                inner join userpassword ul on ul.username = a.username
                inner join master_plan mp on a.master_plan_id = mp.id
                inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                left join (
                    select date(output_rfts.updated_at) tgl_trans_line,max(time(output_rfts.updated_at)) jam_akhir_input_line,count(output_rfts.so_det_id) tot_output_line,
                            case
                            when time(max(output_rfts.updated_at)) >= '12:00:00' and time(max(output_rfts.updated_at)) <= '18:44:59' THEN '01:00:00'
                            when time(max(output_rfts.updated_at)) <= '12:00:00'  THEN '00:00:00'
                            when time(max(output_rfts.updated_at)) >= '18:45:00'  THEN '01:30:00'
                            END as istirahat,
                    userpassword.username
                    from output_rfts
                    left join user_sb_wip on user_sb_wip.id = output_rfts.created_by
                    left join userpassword on userpassword.line_id = user_sb_wip.line_id
                    where output_rfts.updated_at >= '".$this->tgl_awal."' and output_rfts.updated_at <= '".$this->tgl_akhir."' group by userpassword.username, date(output_rfts.updated_at)
                ) op on a.tgl_trans = op.tgl_trans_line and ul.username = op.username
                left join (
                    select * from act_costing_mfg where id_item = '8' group by id_act_cost
                ) acm on ac.id = acm.id_act_cost
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal
                ) konv_sb on ac.deldate = konv_sb.tanggal
                left join (
                    select * from masterrate where  curr='USD' and v_codecurr IN('COSTING3','COSTING6','COSTING8','COSTING12') group by tanggal ORDER BY tanggal DESC limit 1
                ) last_konv_sb on ac.deldate >= last_konv_sb.tanggal
                left join (
                    SELECT
                            master_plan_id,
                            tgl_trans_rfts,
                            sum(tot_rfts)tot_rfts
                    from
                    (
                            select
                            date(a.updated_at)tgl_trans_rfts,
                            master_plan_id,
                            count(so_det_id) tot_rfts,
                            userpassword.username
                            from output_rfts a
                            left join user_sb_wip on user_sb_wip.id = a.created_by
                            left join userpassword on userpassword.line_id = user_sb_wip.line_id
                            where a.updated_at >= '".$this->tgl_awal."' and a.updated_at <= '".$this->tgl_akhir."' and status = 'NORMAL'
                            group by master_plan_id, userpassword.username, date(a.updated_at)
                    ) a
                    inner join master_plan mp on a.master_plan_id = mp.id
                    group by tgl_trans_rfts, master_plan_id
                ) d_rfts on a.tgl_trans = d_rfts.tgl_trans_rfts and a.master_plan_id = d_rfts.master_plan_id
                left join
                (
                    select min(id), man_power, sewing_line, tgl_plan from master_plan
                    where tgl_plan >= '".$this->tgl_awal_n."' and  tgl_plan <= '".$this->tgl_akhir_n."' and cancel = 'N'
                    group by sewing_line, tgl_plan
                ) cmp on a.tgl_trans = cmp.tgl_plan and ul.username = cmp.sewing_line

                -- Kurs join for pre-MySQL 8
                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.kurs_tengah
                    FROM (
                            SELECT a_dates.tgl_trans, MAX(mkb.tanggal_kurs_bi) AS max_kurs_date
                            FROM (
                                    SELECT DISTINCT date(updated_at) AS tgl_trans
                                    FROM output_rfts
                                    WHERE updated_at >= '".$this->tgl_awal."' AND updated_at <= '".$this->tgl_akhir."'
                            ) a_dates
                            JOIN master_kurs_bi mkb
                            ON mkb.tanggal_kurs_bi <= a_dates.tgl_trans
                            GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN master_kurs_bi k
                    ON k.tanggal_kurs_bi = x.max_kurs_date
                ) mkb ON a.tgl_trans = mkb.tgl_trans

                LEFT JOIN (
                    SELECT x.tgl_trans, x.max_kurs_date, k.rate as kurs_tengah
                    FROM (
                        SELECT a_dates.tgl_trans, MAX(mr.tanggal) AS max_kurs_date
                        FROM (
                            SELECT DISTINCT date(updated_at) AS tgl_trans
                            FROM output_rfts
                            WHERE updated_at >= '".$this->tgl_awal."' AND updated_at <= '".$this->tgl_akhir."'
                        ) a_dates
                        JOIN masterrate mr
                        ON mr.tanggal <= a_dates.tgl_trans
                        GROUP BY a_dates.tgl_trans
                    ) x
                    JOIN masterrate k
                    ON k.tanggal = x.max_kurs_date
                    WHERE k.v_codecurr = 'HARIAN'
                ) mr ON a.tgl_trans = mr.tgl_trans

                group by ul.username, ac.kpno, ac.Styleno, a.tgl_trans
                order by a.tgl_trans asc, ul.username asc, ac.kpno asc;
        ");


        $this->rowCount = count($data) + 4;

        $totalManPower = collect($data)->groupBy('sewing_line')->flatMap(function ($group) {

            return $group->pluck('man_power')->unique();
        })->sum();

        // $totalManPower = collect($data)->sum('man_power');
        $totalTarget = collect($data)->sum('target');
        $totalOutput = collect($data)->sum('tot_output');
        $totalMinsAvail = collect($data)->sum('mins_avail');
        $totalEarningRupiah = collect($data)->sum('tot_earning_rupiah');
        $formattedEarningRupiah = 'Rp ' . number_format($totalEarningRupiah, 2, ',', '.');

        $totalMinsProd = collect($data)->sum('mins_prod');

        return view(
            'sewing.export.report_efficiency_new_export',
            [
                'data' => $data,
                'tgl_awal_n' => $this->tgl_awal_n,
                'tgl_akhir_n' => $this->tgl_akhir_n,
                'totalManPower' => $totalManPower,
                'totalTarget' => $totalTarget,
                'totalOutput' => $totalOutput,
                'totalMinsAvail' => $totalMinsAvail,
                'totalEarningRupiah' => $formattedEarningRupiah,
                'totalMinsProd' => $totalMinsProd
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
            'A4:T' . $event->getConcernable()->rowCount,
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
