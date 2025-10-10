<?php

namespace App\Exports\Packing;

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

class PackingOutputExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $outputType;
    protected $groupBy;
    protected $order;
    protected $buyer;
    protected $colAlphabet;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $groupBy, $order, $buyer) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->groupBy = $groupBy;
        $this->order = $order;
        $this->buyer = $buyer;
        $this->colAlphabet = '';
        $this->rowCount = 0;
    }
    public function view(): View
    {
        $masterPlanDateFilter = " between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'";
        $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-120 days', strtotime($this->dateFrom)))."' and '".$this->dateTo."'";

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

        $orderGroupSql = DB::connection('mysql_sb')->table('master_plan')->
            selectRaw("
                master_plan.tgl_plan tanggal,
                act_costing.kpno ws,
                act_costing.styleno style,
                master_plan.color,
                COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line,
                COALESCE(ppic_master_so.po, 'GUDANG STOK') as po
                ".($this->groupBy == "size" ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            join(DB::raw("
                (
                    SELECT
                        master_plan.id_ws,
                        userpassword.username sewing_line,
                        coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                        max( rfts.updated_at ) last_rft,
                        count( rfts.id ) rft,
                        master_plan.id master_plan_id,
                        master_plan.id_ws master_plan_id_ws,
                        rfts.po_id
                        ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                    FROM
                        output_rfts_packing_po rfts
                        INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                        LEFT JOIN userpassword ON userpassword.username = rfts.created_by_line
                    WHERE
                        rfts.updated_at ".$masterPlanDateFilter."
                        AND master_plan.tgl_plan ".$masterPlanDateFilter1."
                        ". ($this->order ? " AND master_plan.id_ws = '".$this->order."'" : "") . "
                    GROUP BY
                        master_plan.id_ws,
                        master_plan.color,
                        DATE ( rfts.updated_at ),
                        COALESCE ( userpassword.username, master_plan.sewing_line ),
                        rfts.po_id
                        ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                    HAVING
                        count(rfts.id) > 0
                ) as rfts
            "), function ($join) {
                $join->on("rfts.master_plan_id", "=", "master_plan.id");
            })->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
            if ($this->groupBy == "size") $orderGroupSql->leftJoin('so_det', function ($join) { $join->on('rfts.so_det_id', '=', 'so_det.id'); });
            if ($this->dateFrom) $orderGroupSql->where('rfts.tanggal', '>=', date('Y-m-d', strtotime('-10 days', strtotime($this->dateFrom))));
            if ($this->dateTo) $orderGroupSql->where('rfts.tanggal', '<=', $this->dateTo);
            if ($this->order) $orderGroupSql->where("act_costing.id", $this->order);
            $orderGroupSql->
                groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.sewing_line, master_plan.sewing_line), ppic_master_so.po ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
                orderBy("master_plan.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("master_plan.color", "asc")->
                orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

            $orderGroup = $orderGroupSql->get();

        $orderOutputSql = DB::connection('mysql_sb')->table('master_plan')->
            selectRaw("
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
                COALESCE ( rfts.last_rft, master_plan.tgl_plan ) latest_output,
                COALESCE(ppic_master_so.po, 'GUDANG STOK') as po
            ")->
            join(DB::raw("
                (
                    SELECT
                        coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                        max( rfts.updated_at ) last_rft,
                        count( rfts.id ) rft,
                        master_plan.id master_plan_id,
                        master_plan.id_ws master_plan_id_ws,
                        COALESCE ( userpassword.username, master_plan.sewing_line ) created_by,
                        rfts.po_id
                        ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                    FROM
                        output_rfts_packing_po rfts
                        INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                        LEFT JOIN userpassword ON userpassword.username = rfts.created_by_line
                        LEFT JOIN laravel_nds.ppic_master_so ON ppic_master_so.id = rfts.po_id
                    WHERE
                        rfts.updated_at ".$masterPlanDateFilter."
                        AND master_plan.tgl_plan ".$masterPlanDateFilter1."
                        ". ($this->order ? " AND master_plan.id_ws = '".$this->order."'" : "") . "
                    GROUP BY
                        master_plan.id_ws,
                        master_plan.color,
                        DATE ( rfts.updated_at ),
                        COALESCE ( userpassword.username, master_plan.sewing_line ),
                        rfts.po_id
                        ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                    HAVING
                        count( rfts.id ) > 0
                ) rfts
            "), "rfts.master_plan_id", "=", "master_plan.id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");

            if ($this->groupBy == "size") $orderOutputSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
            if ($this->order) $orderOutputSql->where("act_costing.id", $this->order);
            if ($this->dateFrom) $orderOutputSql->whereRaw('rfts.tanggal >= "'.$this->dateFrom.'"');
            if ($this->dateTo) $orderOutputSql->whereRaw('rfts.tanggal <= "'.$this->dateTo.'"');
            $orderOutputSql->
                groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.created_by, master_plan.sewing_line) , master_plan.tgl_plan, rfts.tanggal, ppic_master_so.po ".($this->groupBy == 'size' ? ', so_det.size' : '')."")->
                orderBy("master_plan.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("master_plan.color", "asc")->
                orderByRaw("COALESCE(rfts.created_by, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));
            $orderOutputs = $orderOutputSql->get();

        $this->rowCount = $orderGroup->count() + 4;
        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = $orderOutputs->groupBy("tanggal")->count() + ($this->groupBy == "size" ? 6 : 5);
        if ($colCount > (count($alphabets)-1)) {
            $colStack = floor($colCount/(count($alphabets)-1));
            $colStackModulo = $colCount%(count($alphabets)-1);
            $this->colAlphabet = $alphabets[$colStack-1].$alphabets[($colStackModulo > 0 ? $colStackModulo - 1 : $colStackModulo)];
        } else {
            $this->colAlphabet = $alphabets[$colCount];
        }

        return view('packing.export.packing-output-export', [
            'order' => $this->order,
            'buyer' => $this->buyer,
            'buyerName' => $supplier ? $supplier->name : null,
            'groupBy' => $this->groupBy,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
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
