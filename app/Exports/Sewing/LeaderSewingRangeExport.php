<?php

namespace App\Exports\Sewing;

use App\Models\SignalBit\Rft;
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

    function __construct($from, $to, $buyer, $ws ,$style ,$styleProd ,$color ,$size ,$sewingLine ,$lineLeader) {
        $this->from = $from;
        $this->to = $to;
        $this->buyer = $buyer;
        $this->ws = $ws;
        $this->style = $style;
        $this->style_prod = $styleProd;
        $this->color = $color;
        $this->size = $size;
        $this->sewing_line = $sewingLine;
        $this->line_leader = $lineLeader;

        $this->lines = [];
        $this->lineCount = [];
    }

    public function array(): array
    {
        $buyerId = $this->buyer ? $this->buyer : null;

        $buyerFilter = $buyerId ? "AND mastersupplier.Id_Supplier = '".$buyerId."'" : "";

        $ws = $this->ws ? addQuotesAround(implode("\r\n", $this->ws)) : null;
        $style = $this->style ? addQuotesAround(implode("\r\n", $this->style)) : null;
        $styleProd = $this->style_prod ? addQuotesAround(implode("\r\n", $this->style_prod)) : null;
        $color = $this->color ? addQuotesAround(implode("\r\n", $this->color)) : null;
        $size = $this->size ? addQuotesAround(implode("\r\n", $this->size)) : null;
        $sewingLine = $this->sewing_line ? addQuotesAround(implode("\r\n", $this->sewing_line)) : null;
        $lineLeader = $this->line_leader ? addQuotesAround(implode("\r\n", $this->line_leader)) : null;

        $wsFilter = $ws ? "AND act_costing.kpno in (".$ws.")" : "";
        $styleFilter = $style ? "AND act_costing.styleno in (".$style.")" : "";
        $styleProdFilter = $styleProd ? "AND so_det.styleno_prod in (".$styleProd.")" : "";
        $colorFilter = $color ? "AND so_det.color in (".$color.")" : "";
        $sizeFilter = $size ? "AND so_det.size in (".$size.")" : "";
        $sewingLineFilter = $sewingLine ? "AND output.sewing_line in (".$sewingLine.")" : "";
        $lineLeaderFilter = $lineLeader ? "AND output_employee_line.leader_name in (".$lineLeader.")" : "";

        $tanggalQuery = "";
        $lineQuery = "";
        $tanggal = Rft::selectRaw("DATE(output_rfts.updated_at) as tgl_plan")->leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id")->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws");
        $line = Rft::selectRaw("output_rfts.created_by")->leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id")->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws");
        if ($wsFilter || $styleFilter || $styleProdFilter) {
            $tanggal->whereRaw("".
                (
                    $wsFilter || $styleFilter || $styleProdFilter ? "output_rfts.id is not null" :
                    "output_rfts.updated_at >= '".$from." 00:00:00' AND output_rfts.updated_at <= '".$to." 23:59:59' AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'"
                ).
                "
                ".$wsFilter."
                ".$styleFilter."
                ".$styleProdFilter."
            ");

            $line->whereRaw("".
                (
                    $wsFilter || $styleFilter || $styleProdFilter ? "output_rfts.id is not null" :
                    "output_rfts.updated_at >= '".$from." 00:00:00' AND output_rfts.updated_at <= '".$to." 23:59:59' AND master_plan.tgl_plan >= DATE_SUB('".$from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$to."'"
                ).
                "
                ".$wsFilter."
                ".$styleFilter."
                ".$styleProdFilter."
            ");

            $tanggalData = $tanggal->orderByRaw("DATE(output_rfts.updated_at) asc")->groupByRaw("DATE(output_rfts.updated_at)")->get();
            $lineData = $line->orderByRaw("output_rfts.created_by asc")->groupByRaw("output_rfts.created_by")->get();

            $tanggalQuery = addQuotesAround($tanggalData->pluck("tgl_plan")->implode("\r\n"));
            $lineQuery = addQuotesAround($lineData->pluck("created_by")->implode("\r\n"));
        }

        $leaderPerformance = collect(DB::connection("mysql_sb")->select("
            select
                *,
                COALESCE(leader_name, 'KOSONG') as leader_name,
                ws,
                style,
                style_prod,
                color,
                size,
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
                    output.ws,
                    output.style,
                    output.style_prod,
                    output.color,
                    output.size,
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
                            GROUP_CONCAT(DISTINCT output.buyer) as buyer,
                            GROUP_CONCAT(DISTINCT output.ws) as ws,
                            GROUP_CONCAT(DISTINCT output.style) as style,
                            GROUP_CONCAT(DISTINCT output.style_prod) as style_prod,
                            GROUP_CONCAT(DISTINCT output.color) as color,
                            output.size,
                            SUM(rft) rft,
                            SUM(defect) defect,
                            SUM(output) output,
                            SUM(output * output.smv) mins_prod,
                            SUM(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power * output.jam_kerja END) * 60 mins_avail,
                            MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END) man_power,
                            MAX(output.last_update) last_update,
                            MAX(alloutput.last_update) last_update1,
                            ((SUM(output)/total_output) * IF(cast(MAX(alloutput.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60)-60)))/60 jam_kerja,
                            ((SUM(output)/total_output) * IF(cast(MAX(alloutput.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60)-60))) mins_kerja,
                            MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(SUM(output)/total_output)*(IF(cast(MAX(alloutput.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60)-60))) cumulative_mins_avail,
                            FLOOR(MAX(CASE WHEN output.tgl_output != output.tgl_plan THEN 0 ELSE output.man_power END)*(SUM(output)/total_output)*(IF(cast(MAX(alloutput.last_update) as time) <= '13:00:00', (TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60)/AVG(output.smv), ((TIME_TO_SEC(TIMEDIFF(cast(MAX(alloutput.last_update) as time), output.jam_kerja_awal))/60)-60)/AVG(output.smv) ))) cumulative_target
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
                                    master_plan.jam_kerja_awal,
                                    master_plan.smv,
                                    mastersupplier.Supplier buyer,
                                    act_costing.kpno ws,
                                    act_costing.styleno style,
                                    so_det.styleno_prod style_prod,
                                    so_det.color,
                                    so_det.size
                                FROM
                                    output_rfts rfts
                                    inner join master_plan on master_plan.id = rfts.master_plan_id
                                    inner join act_costing on act_costing.id = master_plan.id_ws
                                    inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                                    inner join so_det on so_det.id = rfts.so_det_id
                                where
                                    ".(
                                        $wsFilter || $styleFilter || $styleProdFilter ? "rfts.id is not null" :
                                        "rfts.updated_at >= '".$this->from." 00:00:00' AND rfts.updated_at <= '".$this->to." 23:59:59'
                                    AND master_plan.tgl_plan >= DATE_SUB('".$this->from."', INTERVAL 20 DAY) AND master_plan.tgl_plan <= '".$this->to."'"
                                    )."
                                    AND master_plan.cancel = 'N'
                                    ".$buyerFilter."
                                    ".$wsFilter."
                                    ".$styleFilter."
                                    ".$styleProdFilter."
                                    ".$colorFilter."
                                    ".$sizeFilter."
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
                                    mastersupplier.Supplier buyer,
                                    act_costing.kpno ws,
                                    act_costing.styleno style,
                                    so_det.styleno_prod style_prod,
                                    so_det.color,
                                    so_det.size
                                FROM
                                    output_defects defects
                                    inner join master_plan on master_plan.id = defects.master_plan_id
                                    inner join act_costing on act_costing.id = master_plan.id_ws
                                    inner join mastersupplier on mastersupplier.Id_Supplier = act_costing.id_buyer
                                    inner join so_det on so_det.id = defects.so_det_id
                                where
                                    ".(
                                        $wsFilter || $styleFilter || $styleProdFilter ? "defects.id is not null" :
                                        "defects.updated_at >= '".$this->from." 00:00:00' AND defects.updated_at <= '".$this->to." 23:59:59'
                                    AND master_plan.tgl_plan >= DATE_SUB('".$this->from."', INTERVAL 14 DAY) AND master_plan.tgl_plan <= '".$this->to."'"
                                    )."
                                    AND master_plan.cancel = 'N'
                                    ".$buyerFilter."
                                    ".$wsFilter."
                                    ".$styleFilter."
                                    ".$styleProdFilter."
                                    ".$colorFilter."
                                    ".$sizeFilter."
                                GROUP BY
                                    master_plan.id, master_plan.tgl_plan, DATE(defects.updated_at)
                                order by
                                    sewing_line
                            ) defect on defect.master_plan_id = output.master_plan_id and output.tgl_plan = defect.tgl_plan and output.tgl_output = defect.tgl_output
                            LEFT JOIN (
                                SELECT
                                    DATE( updated_at ) tgl_output,
                                    created_by AS sewing_line_id,
                                    master_plan.sewing_line,
                                    max(TIME ( updated_at )) last_update,
                                    count( so_det_id ) total_output
                                FROM
                                    output_rfts
                                    LEFT JOIN master_plan on master_plan.id = output_rfts.master_plan_id
                                WHERE
                                    output_rfts.id is not null
                                    ".($tanggalQuery ? "AND DATE ( output_rfts.updated_at ) IN ( ".$tanggalQuery." )" : "")."
                                    ".($lineQuery ? "AND output_rfts.created_by IN ( ".$lineQuery." )" : "")."
                                GROUP BY
                                    created_by,
                                    DATE ( updated_at )
                            ) alloutput ON alloutput.tgl_output = output.tgl_output AND alloutput.sewing_line = output.sewing_line
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
            array_push($data, ["Tanggal", "Sewing Line", "Buyer", "Style Prod", "RFT Rate", "Defect Rate", "Efficiency Rate", "Leader"]);

            foreach($lg['data'] as $d) {
                array_push($data, [$d->tanggal, $d->sewing_line, $d->buyer, $d->style_prod, ($d->output > 0 ? round(($d->rft/$d->output)*100, 2) : '0'), ($d->output > 0 ? round(($d->defect/$d->output)*100, 2) : '0'), ($d->mins_avail > 0 ? round(($d->mins_prod/$d->mins_avail)*100, 2) : '0'), $d->leader_name]);
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
        return ['tgl_output', 'sewing_line', 'buyer', 'style_prod', 'rft_rate', 'defect_rate', 'eff_rate'];
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
            $labelsEff = [new DataSeriesValues('String', 'Worksheet!$E$'.($i+$addRow).'', null, 1), new DataSeriesValues('String', 'Worksheet!$F$'.($i+$addRow).'', null, 1), new DataSeriesValues('String', 'Worksheet!$G$'.($i+$addRow).'', null, 1)];

            $categoriesEff = [new DataSeriesValues('String', 'Worksheet!$A$'.($i+$addRow+1).':$A$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i])];

            array_push($valuesEff,
                new DataSeriesValues('Number', 'Worksheet!$E$'.($i+$addRow+1).':$E$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i]),
                new DataSeriesValues('Number', 'Worksheet!$F$'.($i+$addRow+1).':$F$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i]),
                new DataSeriesValues('Number', 'Worksheet!$G$'.($i+$addRow+1).':$G$'.($i+$addRow+($this->lineCount[$i])).'', null, $this->lineCount[$i]),
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
