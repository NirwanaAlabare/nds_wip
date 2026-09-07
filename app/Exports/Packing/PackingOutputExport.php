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
    protected $color;
    protected $line;
    protected $po;
    protected $size;
    protected $colAlphabet;
    protected $rowCount;

    function __construct($dateFrom, $dateTo, $groupBy, $order, $buyer, $color, $line, $po, $size) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->groupBy = $groupBy;
        $this->order = $order;
        $this->buyer = $buyer;
        $this->color = $color;
        $this->line = $line;
        $this->po = $po;
        $this->size = $size;
        $this->colAlphabet = '';
        $this->rowCount = 0;
    }
    public function view(): View
    {
        $masterPlanDateFilter = " between '".$this->dateFrom." 00:00:00' and '".$this->dateTo." 23:59:59'";
        $masterPlanDateFilter1 = " between '2024-01-01' and '".$this->dateTo."'";

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

        // Query for Grouping
        $dailyOrderGroupSql = DB::connection('mysql_sb')->table(DB::raw("
            (
                SELECT
                    coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                    max( rfts.updated_at ) last_rft,
                    count( rfts.id ) rft,
                    master_plan.id master_plan_id,
                    master_plan.id_ws,
                    master_plan.color,
                    userpassword.username sewing_line,
                    rfts.po_id,
                    rfts.type
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
                    rfts.po_id,
                    rfts.type
                    ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                HAVING
                    count(rfts.id) > 0
            ) as rfts
        "))->
        selectRaw("
            rfts.tanggal tanggal,
            act_costing.kpno ws,
            act_costing.styleno style,
            rfts.color,
            COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line,
            COALESCE(ppic_master_so.po, 'GUDANG STOK') as po,
            COALESCE(rfts.type, 'rft') as type
            ".($this->groupBy == "size" ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
        ")->
        leftJoin("master_plan", "master_plan.id", "=", "rfts.master_plan_id")->
        leftJoin("act_costing", "act_costing.id", "=", "rfts.id_ws")->
        leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
        if ($this->groupBy == "size") $dailyOrderGroupSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
        if ($this->dateFrom) $dailyOrderGroupSql->where('rfts.tanggal', '>=', $this->dateFrom);
        if ($this->dateTo) $dailyOrderGroupSql->where('rfts.tanggal', '<=', $this->dateTo);
        if ($this->color) $dailyOrderGroupSql->where('rfts.color', $this->color);
        if ($this->line) $dailyOrderGroupSql->where('rfts.sewing_line', $this->line);
        if ($this->groupBy == "size" && $this->size) $dailyOrderGroupSql->where('so_det.size', $this->size);
        if ($this->po) $dailyOrderGroupSql->where(DB::raw("COALESCE(ppic_master_so.po, 'GUDANG STOK')"), $this->po);
        if ($this->order) $dailyOrderGroupSql->where("act_costing.id", $this->order);
        $dailyOrderGroupSql->
            groupByRaw("rfts.id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.sewing_line, master_plan.sewing_line), ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
            orderBy("rfts.id_ws", "asc")->
            orderBy("act_costing.styleno", "asc")->
            orderBy("rfts.color", "asc")->
            orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

        $orderGroup = $dailyOrderGroupSql->get();

        // Query for Output
        $dailyOrderOutputSql = DB::connection('mysql_sb')->table(DB::raw("
            (
                SELECT
                    coalesce( date( rfts.updated_at ), master_plan.tgl_plan ) tanggal,
                    max( rfts.updated_at ) last_rft,
                    count( rfts.id ) rft,
                    master_plan.id master_plan_id,
                    master_plan.id_ws,
                    master_plan.color,
                    COALESCE ( userpassword.username, master_plan.sewing_line ) created_by,
                    rfts.po_id,
                    rfts.type
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
                    rfts.po_id,
                    rfts.type
                    ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                HAVING
                    count( rfts.id ) > 0
            ) as rfts
        "))->
            selectRaw("
                rfts.tanggal,
                COALESCE(rfts.type, 'rft') as type,
                ".($this->groupBy == 'size' ? ' rfts.so_det_id, so_det.size, ' : '')."
                SUM( rfts.rft ) output,
                act_costing.kpno ws,
                act_costing.styleno style,
                rfts.color,
                COALESCE ( rfts.created_by, master_plan.sewing_line ) AS sewing_line,
                master_plan.smv smv,
                master_plan.jam_kerja jam_kerja,
                master_plan.man_power man_power,
                master_plan.plan_target plan_target,
                COALESCE ( rfts.last_rft, master_plan.tgl_plan ) latest_output,
                COALESCE(ppic_master_so.po, 'GUDANG STOK') as po
            ")->
            leftJoin("master_plan", "master_plan.id", "=", "rfts.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "rfts.id_ws")->
            leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
            if ($this->groupBy == "size") $dailyOrderOutputSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
            if ($this->order) $dailyOrderOutputSql->where("act_costing.id", $this->order);
            if ($this->dateFrom) $dailyOrderOutputSql->whereRaw('rfts.tanggal >= "'.$this->dateFrom.'"');
            if ($this->dateTo) $dailyOrderOutputSql->whereRaw('rfts.tanggal <= "'.$this->dateTo.'"');
            if ($this->color) $dailyOrderOutputSql->where('rfts.color', $this->color);
            if ($this->line) $dailyOrderOutputSql->whereRaw('COALESCE(rfts.created_by, master_plan.sewing_line) = "'.$this->line.'"');
            if ($this->po) $dailyOrderOutputSql->where(DB::raw("COALESCE(ppic_master_so.po, 'GUDANG STOK')"), $this->po);
            if ($this->groupBy == "size" && $this->size) $dailyOrderOutputSql->where('so_det.size', $this->size);
            $dailyOrderOutputSql->
                groupByRaw("rfts.id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.created_by, master_plan.sewing_line) , master_plan.tgl_plan, rfts.tanggal, ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($this->groupBy == 'size' ? ', so_det.size' : '')."")->
                orderBy("rfts.id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("rfts.color", "asc")->
                orderByRaw("COALESCE(rfts.created_by, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));
            $orderOutputs = $dailyOrderOutputSql->get();

        $useSize = $this->groupBy === 'size';

        $dates = $orderOutputs
            ->pluck('tanggal')
            ->filter(function ($tanggal) { return $tanggal !== null && $tanggal !== '' && $tanggal !== '0'; })
            ->unique()
            ->sort()
            ->values()
            ->all();

        // Containers
        $outputMap  = [];
        $rowTotals  = [];
        $dateTotals = [];
        $grandTotal = 0;

        foreach ($orderOutputs as $row) {

            // Normalize type (important for consistent keys)
            $type = $row->type ?: 'rft';

            $key = implode('|', [
                $row->ws,
                $row->style,
                $row->color,
                $row->sewing_line,
                $row->po,
                $type,
                $useSize ? $row->size : '_',
            ]);

            $date = $row->tanggal;
            $qty  = (int) $row->output;

            // Per cell
            $outputMap[$key][$date] =
                ($outputMap[$key][$date] ?? 0) + $qty;

            // Per row
            $rowTotals[$key] =
                ($rowTotals[$key] ?? 0) + $qty;

            // Per date column
            $dateTotals[$date] =
                ($dateTotals[$date] ?? 0) + $qty;

            // Grand total
            $grandTotal += $qty;
        }

        $this->rowCount = $orderGroup->count() + 4;
        $alphabets = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        $colCount = count($dates) + ($this->groupBy == "size" ? 7 : 6);
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
            'dates' => $dates,
            'outputMap' => $outputMap,
            'rowTotals' => $rowTotals,
            'dateTotals' => $dateTotals,
            'grandTotal' => $grandTotal,
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
