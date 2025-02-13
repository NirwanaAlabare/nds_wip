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
    protected $buyer;
    protected $colAlphabet;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $outputType, $groupBy, $order, $buyer) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->outputType = $outputType;
        $this->groupBy = $groupBy;
        $this->order = $order;
        $this->buyer = $buyer;
        $this->colAlphabet = '';
        $this->rowCount = 0;
    }

    public function view(): View
    {
        $masterPlanDateFilter = " between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'";
        $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-7 days', strtotime($this->dateFrom)))."' and '".$this->dateTo."'";

        $supplier = DB::connection('mysql_sb')->table('mastersupplier')->
            selectRaw('Id_Supplier as id, Supplier as name')->
            leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
            where('mastersupplier.tipe_sup', 'C')->
            where('status', '!=', 'CANCEL')->
            where('type_ws', 'STD')->
            where('cost_date', '>=', '2023-01-01')->
            where('Id_Supplier', $this->buyer)->
            orderBy('Supplier', 'ASC')->
            groupBy('Id_Supplier', 'Supplier')->
            first();

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
                    AND output_rfts".($this->outputType).".updated_at >= '".$this->dateFrom." 00:00:00'
                    AND output_rfts".($this->outputType).".updated_at <= '".$this->dateTo." 23:59:59'
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

            if ($this->order) {
                $orderGroupSql->
                    where("act_costing.id", $this->order);
            }

            if ($this->buyer) {
                $orderGroupSql->
                    where("act_costing.id_buyer", $this->buyer);
            }

            $orderGroupSql->
                groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.sewing_line, master_plan.sewing_line) ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
                orderBy("master_plan.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("master_plan.color", "asc")->
                orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

            $orderGroup = $orderGroupSql->get();

        $orderOutputsSql = MasterPlan::selectRaw("
                rfts.tanggal,
                ".($this->groupBy == 'size' ? ' rfts.so_det_id, so_det.size, ' : '')."
                SUM( rfts.rft ) output,
                act_costing.kpno ws,
                act_costing.styleno style,
                master_plan.color,
                COALESCE ( rfts.created_by, master_plan.sewing_line ) AS sewing_line,
                master_plan.smv smv,
                master_plan.jam_kerja jam_kerja,
                master_plan.man_power man_power,
                master_plan.plan_target plan_target,
                COALESCE ( rfts.last_rft, master_plan.tgl_plan ) latest_output
            ")->
            join(DB::raw("
                (
                    SELECT
                        coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                        max( rfts.updated_at ) last_rft,
                        count( rfts.id ) rft,
                        master_plan.id master_plan_id,
                        master_plan.id_ws master_plan_id_ws,
                        COALESCE ( userpassword.username, master_plan.sewing_line ) created_by
                        ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                    FROM
                        output_rfts".$this->outputType." rfts
                        INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id ".
                        (
                            $this->outputType != " _packing " ? "
                            LEFT JOIN user_sb_wip ON user_sb_wip.id = rfts.created_by
                            LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id " : "
                            LEFT JOIN userpassword ON userpassword.username = rfts.created_by "
                        )."
                        INNER JOIN act_costing on act_costing.id = master_plan.id_ws
                    WHERE
                        rfts.updated_at ".$masterPlanDateFilter."
                        AND master_plan.tgl_plan ".$masterPlanDateFilter1."
                        ".($this->order ? " AND master_plan.id_ws = '".$this->order."'" : "")."
                        ".($this->buyer ? " AND act_costing.id_buyer = '".$this->buyer."'" : "")."
                    GROUP BY
                        master_plan.id_ws,
                        DATE ( rfts.updated_at ),
                        COALESCE ( userpassword.username, master_plan.sewing_line )
                        ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                ) rfts
            "), "rfts.master_plan_id", "=", "master_plan.id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws");

            if ($this->groupBy == "size") {
                $orderOutputsSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
            }

            if ($this->order) {
                $orderOutputsSql->
                    where("act_costing.id", $this->order);
            }

            if ($this->buyer) {
                $orderOutputsSql->
                    where("act_costing.id_buyer", $this->buyer);
            }

            $orderOutputsSql->
                groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.created_by, master_plan.sewing_line) , rfts.tanggal ".($this->groupBy == 'size' ? ', so_det.size' : '')."")->
                orderBy("master_plan.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("master_plan.color", "asc")->
                orderByRaw("COALESCE(rfts.created_by, master_plan.sewing_line) asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));
            if ($this->dateFrom) {
                $orderOutputsSql->where('rfts.tanggal', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $orderOutputsSql->where('rfts.tanggal', '<=', $this->dateTo);
            }
            $orderOutputs = $orderOutputsSql->get();

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
            'buyer' => $this->buyer,
            'buyerName' => $supplier ? $supplier->name : null,
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
