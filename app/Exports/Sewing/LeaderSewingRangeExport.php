<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use DB;

class LeaderSewingRangeExport implements FromArray, ShouldAutoSize, WithCustomStartCell, WithCharts
{
    protected $from;
    protected $to;
    protected $buyer;
    protected $rowCount;
    protected $colAlphabet;
    protected $lineCount;

    function __construct($from, $to, $buyer) {
        $this->from = $from;
        $this->to = $to;
        $this->buyer = $buyer;
        $this->lines = [];
        $this->lineCount = [];
    }

    public function array(): array
    {
        $buyerId = $this->buyer ? $this->buyer : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier = '".$buyerId."'" : "";

        $leaderPerformance = collect(DB::connection("mysql_sb")->select("
            select
                *,
                COALESCE(leader_name, 'KOSONG') as leader_name,
                SUM(rft) rft,
                SUM(defect) defect,
                SUM(output) output,
                SUM(mins_prod) mins_prod,
                SUM(mins_avail) mins_avail_old,
                SUM(cumulative_mins_avail) mins_avail
            from (
                select
                    output_employee_line.*,
                    output.sewing_line,
                    output.buyer,
                    SUM(rft) rft,
                    SUM(defect) defect,
                    SUM(output) output,
                    SUM(mins_prod) mins_prod,
                    SUM(mins_avail) mins_avail,
                    SUM(cumulative_mins_avail) cumulative_mins_avail
                from
                    output_employee_line
                    left join userpassword on userpassword.line_id = output_employee_line.line_id
                    inner join (
                        SELECT
                            output.tgl_output,
                            output.tgl_plan,
                            output.sewing_line,
                            output.buyer,
                            SUM(rft) rft,
                            SUM(defect) defect,
                            SUM(output) output,
                            SUM(output * output.smv) mins_prod,
                            SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                            MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                            MAX(output.last_update) last_update,
                            (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)))/60 jam_kerja,
                            (IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) mins_kerja,
                            MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60))) cumulative_mins_avail,
                            FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(IF(cast(MAX(output.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(output.last_update) as time), '07:00:00'))/60)-60)/AVG(output.smv) ))) cumulative_target
                        FROM
                            (
                                SELECT
                                    DATE( rfts.updated_at ) tgl_output,
                                    COUNT( rfts.id ) output,
                                    SUM( CASE WHEN rfts.status = 'NORMAL' THEN 1 ELSE 0 END ) rft,
                                    MAX(rfts.updated_at) last_update,
                                    master_plan.id master_plan_id,
                                    master_plan.tgl_plan,
                                    master_plan.sewing_line,
                                    master_plan.man_power,
                                    master_plan.jam_kerja,
                                    master_plan.smv,
                                    mastersupplier.Supplier buyer
                                FROM
                                    output_rfts rfts
                                    inner join master_plan on master_plan.id = rfts.master_plan_id
                                    inner join act_costing on act_costing.id = master_plan.id_ws
                                    inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                                where
                                    rfts.updated_at >= '".$this->from." 00:00:00' AND rfts.updated_at <= '".$this->to." 23:59:59'
                                    AND master_plan.tgl_plan >= DATE_SUB('".$this->from."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$this->to."'
                                    AND master_plan.cancel = 'N'
                                    ".$buyerFilter."
                                GROUP BY
                                    master_plan.id, master_plan.tgl_plan, DATE(rfts.updated_at)
                                order by
                                    sewing_line
                            ) output
                            LEFT JOIN (
                                SELECT
                                    DATE( defects.updated_at ) tgl_output,
                                    COUNT( defects.id ) defect,
                                    MAX(defects.updated_at) last_update,
                                    master_plan.id master_plan_id,
                                    master_plan.tgl_plan,
                                    master_plan.sewing_line,
                                    master_plan.man_power,
                                    master_plan.jam_kerja,
                                    master_plan.smv,
                                    mastersupplier.Supplier buyer
                                FROM
                                    output_defects defects
                                    inner join master_plan on master_plan.id = defects.master_plan_id
                                    inner join act_costing on act_costing.id = master_plan.id_ws
                                    inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                                where
                                    defects.updated_at >= '".$this->from." 00:00:00' AND defects.updated_at <= '".$this->to." 23:59:59'
                                    AND master_plan.tgl_plan >= DATE_SUB('".$this->from."', INTERVAL 7 DAY) AND master_plan.tgl_plan <= '".$this->to."'
                                    AND master_plan.cancel = 'N'
                                    ".$buyerFilter."
                                GROUP BY
                                    master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at)
                                order by
                                    sewing_line
                            ) defect on defect.master_plan_id = output.master_plan_id and output.tgl_plan = defect.tgl_plan and output.tgl_output = defect.tgl_output
                        GROUP BY
                            output.sewing_line,
                            output.tgl_output
                    ) output on output.sewing_line = userpassword.username and output.tgl_output = output_employee_line.tanggal
                group by
                    tanggal,
                    leader_id,
                    chief_id,
                    line_id
                order by
                    line_id asc,
                    tanggal asc
            ) chief_sewing
            group by
                line_id,
                tanggal
            order by
                line_id,
                tanggal
        "));

        $this->rowCount = $leaderPerformance->sortBy("tanggal")->groupBy("sewing_line")->count();
        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $leaderPerformance->groupBy("tanggal")->count()+1;
        if ($colCount > (count($alphabets)-1)) {
            $colStack = floor($colCount/(count($alphabets)-1));
            $colStackModulo = $colCount%(count($alphabets)-1);
            $this->colAlphabet = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabet = $alphabets[$colCount];
        }

        $leaderGroups = $leaderPerformance->groupBy("sewing_line");

        $leaderGroup = $leaderGroups->map(function ($group) {
            return [
                'sewing_line' => $group->first()->sewing_line,// opposition_id is constant inside the same group, so just take the first or whatever.
                'data' => $group,
                'total_rft' => $group->sum('rft'),
                'total_output' => $group->sum('output'),
                'total_mins_prod' => $group->sum('mins_prod'),
                'total_mins_avail' => $group->sum('mins_avail'),
            ];
        });

        $data = [];
        foreach ($leaderGroup as $lg) {
            array_push($data, ["Tanggal", "Sewing Line", "Buyer", "RFT Rate", "Defect Rate", "Efficiency Rate", "Leader"]);

            foreach($lg['data'] as $d) {
                array_push($data, [$d->tanggal, $d->sewing_line, $d->buyer, ($d->output > 0 ? round(($d->rft/$d->output)*100, 2) : '0'), ($d->output > 0 ? round(($d->defect/$d->output)*100, 2) : '0'), ($d->mins_avail > 0 ? round(($d->mins_prod/$d->mins_avail)*100, 2) : '0'), $d->leader_name]);
            }

            for ($i = 0; $i < 15; $i++) {
                array_push($data, ["", "", "", "", ""]);
            }

            array_push($this->lines, $lg['sewing_line']);
            array_push($this->lineCount, count($lg['data']));
        }

        return $data;
    }

    public function headings(): array
    {
        return ['tgl_output', 'sewing_line', 'buyer', 'rft_rate', 'defect_rate', 'eff_rate'];
    }

    public function startCell(): string
    {
        return 'A16';
    }

    public function charts()
    {
        // Eff
        $charts = [];

        for ($i = 0; $i < $this->rowCount; $i++) {
            $labelsEff = [];
            $categoriesEff = [];
            $valuesEff = [];

            if ($i == 0) {
                $addRow = 16;
            } else {
                $addRow = collect($this->lineCount)->filter(function ($item, $key) use ($i) {
                    return $key < $i;
                })->sum()+((15*($i+1))+(1));
            }

            // Eff
            $labelsEff = [new DataSeriesValues('String', 'Worksheet!$D$'.($i+$addRow).'', null, 1), new DataSeriesValues('String', 'Worksheet!$E$'.($i+$addRow).'', null, 1)];

            $categoriesEff = [new DataSeriesValues('String', 'Worksheet!$A$'.($i+$addRow+1).':$A$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i])];

            array_push($valuesEff,
                new DataSeriesValues('Number', 'Worksheet!$D$'.($i+$addRow+1).':$D$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i]),
                new DataSeriesValues('Number', 'Worksheet!$E$'.($i+$addRow+1).':$E$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i]),
            );

            // Eff
            $seriesEff = new DataSeries(DataSeries::TYPE_LINECHART, DataSeries::GROUPING_STANDARD, range(0, count($valuesEff) - 1), $labelsEff, $categoriesEff, $valuesEff);
            $plotEff   = new PlotArea(null, [$seriesEff]);

            $legendEff = new Legend();
            $chartEff  = new Chart('chart name', new Title('Efficiency '.strtoupper(str_replace("_", " ", $this->lines[$i]))), $legendEff, $plotEff);

            if ($i == 0) {
                $chartEff->setTopLeftPosition('A'.($i+2));
                $chartEff->setBottomRightPosition('G'.($i+$addRow-2));
            } else {
                $chartEff->setTopLeftPosition('A'.($i+$addRow-14));
                $chartEff->setBottomRightPosition('G'.($i+$addRow-2));
            }

            array_push($charts, $chartEff);
        }

        return $charts;
    }
}
