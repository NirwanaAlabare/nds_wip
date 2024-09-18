<?php

namespace App\Exports;

use App\Models\SignalBit\MasterPlan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class OrderOutputExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $outputType;
    protected $groupBy;
    protected $order;
    protected $colAlphabet;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $outputType, $groupBy, $order) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->outputType = $outputType;
        $this->groupBy = $groupBy;
        $this->order = $order;
        $this->colAlphabet = '';
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $orderGroupSql = MasterPlan::selectRaw("
                master_plan.tgl_plan tanggal,
                act_costing.kpno ws,
                act_costing.styleno style,
                master_plan.color,
                COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line
                ".($this->groupBy == "size" ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin(DB::raw("(
                SELECT
                    master_plan.id_ws,
                    output_rfts".($this->outputType).".master_plan_id,
                    userpassword.username sewing_line
                FROM
                    output_rfts".($this->outputType)."
                    ".($this->outputType != "_packing" ?
                    "LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts".($this->outputType).".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" :
                    "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->outputType).".created_by")."
                    LEFT JOIN master_plan on master_plan.id = output_rfts".($this->outputType).".master_plan_id
                WHERE
                    output_rfts".($this->outputType).".created_by IS NOT NULL
                GROUP BY
                    output_rfts".($this->outputType).".master_plan_id,
                    output_rfts".($this->outputType).".created_by
            ) as rfts"), function ($join) {
                $join->on("rfts.master_plan_id", "=", "master_plan.id");
            });
            if ($this->groupBy == "size") {
                $orderGroupSql->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->leftJoin('so_det', function ($join) { $join->on('so_det.id_so', '=', 'so.id'); $join->on('so_det.color', '=', 'master_plan.color'); });
            }
            if ($this->dateFrom) {
                $orderGroupSql->where('master_plan.tgl_plan', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $orderGroupSql->where('master_plan.tgl_plan', '<=', $this->dateTo);
            }
            $orderGroupSql->
                where("act_costing.id", $this->order)->
                groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.sewing_line, master_plan.sewing_line) ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
                orderBy("master_plan.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("master_plan.color", "asc")->
                orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

            $orderGroup = $orderGroupSql->get();

        $masterPlanDateFilter = " between '".$this->dateFrom."' and '".$this->dateTo."'";
        $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-1 days', strtotime($this->dateFrom)))."' and '".$this->dateTo."'";

        $orderOutputSql = MasterPlan::selectRaw("
                master_plan.tgl_plan tanggal,
                ".($this->groupBy == 'size' ? ' output_rfts'.($this->outputType).'.so_det_id, so_det.size, ' : '')."
                count(output_rfts".($this->outputType).".id) output,
                act_costing.kpno ws,
                act_costing.styleno style,
                master_plan.color,
                COALESCE(line.username, master_plan.sewing_line) as sewing_line,
                master_plan.smv smv,
                master_plan.jam_kerja jam_kerja,
                master_plan.man_power man_power,
                master_plan.plan_target plan_target,
                coalesce(max(output_rfts".($this->outputType).".updated_at), master_plan.tgl_plan) latest_output
            ")->
            leftJoin("output_rfts".($this->outputType)."", "output_rfts".($this->outputType).".master_plan_id", "=", "master_plan.id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws");
            if ($this->groupBy == "size") {
                $orderOutputSql->leftJoin('so_det', 'so_det.id', '=', 'output_rfts'.($this->outputType).'.so_det_id');
            }

            if ($this->outputType == "_packing") {
                $orderOutputSql->leftJoin('userpassword as line', 'line.username', '=', 'output_rfts'.($this->outputType).'.created_by');
            } else {
                $orderOutputSql->leftJoin('user_sb_wip', 'user_sb_wip.id', '=', 'output_rfts'.($this->outputType).'.created_by')->
                    leftJoin('userpassword as line', 'line.username', '=', 'user_sb_wip.line_id');
            }

            $orderOutputSql->
                where("act_costing.id", $this->order)->
                groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(line.username, master_plan.sewing_line) , master_plan.tgl_plan ".($this->groupBy == 'size' ? ', so_det.size' : '')."")->
                orderBy("master_plan.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("master_plan.color", "asc")->
                orderByRaw("COALESCE(line.username, master_plan.sewing_line) asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));
            if ($this->dateFrom) {
                $orderOutputSql->where('master_plan.tgl_plan', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $orderOutputSql->where('master_plan.tgl_plan', '<=', $this->dateTo);
            }

            $orderOutputs = $orderOutputSql->get();

        $this->rowCount = $orderGroup->count() + 4;
        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $orderOutputs->groupBy("tanggal")->count() + ($this->groupBy == "size" ? 5 : 4);
        if ($colCount > (count($alphabets)-1)) {
            $colStack = floor($colCount/(count($alphabets)-1));
            $colStackModulo = $colCount%(count($alphabets)-1);
            $this->colAlphabet = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabet = $alphabets[$colCount];
        }

        return view('sewing.export.order-output-export', [
            'order' => $this->order,
            'groupBy' => $this->groupBy,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'outputType' => $this->outputType,
            'orderGroup' => $orderGroup,
            'orderOutputs' => $orderOutputs,
        ]);
    }

    public function columnFormats(): array
    {
        return [
            //
        ];
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
            'A3:' . $event->getConcernable()->colAlphabet . $event->getConcernable()->rowCount,
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
