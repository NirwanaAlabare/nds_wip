<?php

namespace App\Http\Livewire\Packing;

use App\Models\SignalBit\RftPackingPo;
use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Rft;
use DB;

class TrackPackingOutput extends Component
{
    public $suppliers;
    public $orders;
    public $selectedSupplier;
    public $selectedOrder;

    public $orderFilter;
    public $dailyOrderGroup;
    public $dailyOrderOutputs;
    public $loadingOrder;

    public $dateFromFilter;
    public $dateToFilter;

    public $colorFilter;
    public $lineFilter;
    public $sizeFilter;
    public $poFilter;

    public $outputType;

    public $groupBy;

    public $baseUrl;

    public $search;

    public function mount()
    {
        $this->suppliers = null;
        $this->orders = null;
        $this->selectedSupplier = null;
        $this->selectedOrder = null;
        $this->dailyOrderGroup = null;
        $this->dailyOrderOutputs = null;
        $this->loadingOrderOutput = false;

        $this->dateFromFilter = date('Y-m-d');
        $this->dateToFilter = date("Y-m-d");

        $this->outputType = null;

        $this->colorFilter = null;
        $this->lineFilter = null;
        $this->sizeFilter = null;
        $this->poFilter = null;

        $this->groupBy = "size";
        $this->baseUrl = url('/');

        $this->isSearch = false;
    }

    public function clearFilter()
    {
        $this->colorFilter = null;
        $this->lineFilter = null;
        $this->sizeFilter = null;
        $this->poFilter = null;
        $this->isSearch = false;
    }

    public function updatedSelectedOrder()
    {
        $firstPlan = MasterPlan::selectRaw("tgl_plan")->where("id_ws", $this->selectedOrder)->orderBy("tgl_plan", "asc")->first();
        $lastPlan = RftPackingPo::selectRaw("DATE(output_rfts_packing_po.updated_at) as tgl_plan")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts_packing_po.master_plan_id")->where("master_plan.id_ws", $this->selectedOrder)->orderBy("output_rfts_packing_po.updated_at", "desc")->first();

        if ($firstPlan) {
            $this->dateFromFilter = $firstPlan->tgl_plan;
        }
        // else {
        //     $this->dateFromFilter = date("Y-m-d");
        // }

        if ($lastPlan) {
            $this->dateToFilter = $lastPlan->tgl_plan;
        }
        // else {
        //     $this->dateToFilter = date("Y-m-d");
        // }
    }

    public function setSearch() {
        $this->isSearch = true;
    }

    public function render()
    {
        ini_set("max_execution_time", 3600);

        if (!$this->dateFromFilter) {
            $this->dateFromFilter = date("Y-m-d");
        }

        if (!$this->dateToFilter) {
            $this->dateToFilter = date("Y-m-d");
        }

        $this->suppliers = DB::connection('mysql_sb')->table('mastersupplier')->
                selectRaw('Id_Supplier as id, Supplier as name')->
                leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->
                where('mastersupplier.tipe_sup', 'C')->
                where('status', '!=', 'CANCEL')->
                where('type_ws', 'STD')->
                where('cost_date', '>=', '2023-01-01')->
                orderBy('Supplier', 'ASC')->
                groupBy('Id_Supplier', 'Supplier')->
                get();

        $orderSql = DB::connection('mysql_sb')->
            table('act_costing')->
            selectRaw('
                id as id_ws,
                kpno as no_ws
            ')->
            where('status', '!=', 'CANCEL')->
            where('cost_date', '>=', '2023-01-01')->
            where('type_ws', 'STD');
            if ($this->selectedSupplier) $orderSql->where('id_buyer', $this->selectedSupplier);

        $this->orders = $orderSql->
            orderBy('cost_date', 'desc')->
            orderBy('kpno', 'asc')->
            groupBy('kpno')->
            get();

        $this->loadingOrderOutput = false;

        $dates = [];
        $outputMap = [];
        $rowTotals = [];
        $dateTotals = [];
        $grandTotal = 0;

        if ($this->isSearch === true) {
            $this->isSearch = false;

            $orderFilterSql = DB::connection('mysql_sb')->table('master_plan')->
                selectRaw("
                    master_plan.tgl_plan tanggal,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    master_plan.color,
                    COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line,
                    COALESCE(ppic_master_so.po, 'GUDANG STOK') as po,
                    COALESCE(rfts.type, 'rft') as type
                    ".($this->groupBy == "size" ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
                ")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin(DB::raw("(
                    SELECT
                        master_plan.id_ws,
                        output_rfts_packing_po.master_plan_id,
                        userpassword.username sewing_line,
                        output_rfts_packing_po.po_id,
                        output_rfts_packing_po.type
                    FROM
                        output_rfts_packing_po
                        LEFT JOIN userpassword ON userpassword.username = output_rfts_packing_po.created_by_line
                        LEFT JOIN master_plan on master_plan.id = output_rfts_packing_po.master_plan_id
                    WHERE
                        output_rfts_packing_po.created_by IS NOT NULL
                        AND output_rfts_packing_po.updated_at >= '".$this->dateFromFilter." 00:00:00'
                        AND output_rfts_packing_po.updated_at <= '".$this->dateToFilter." 23:59:59'
                        " . ($this->selectedOrder ? " AND master_plan.id_ws = '".$this->selectedOrder."'" : "") . "
                    GROUP BY
                        output_rfts_packing_po.master_plan_id,
                        output_rfts_packing_po.created_by,
                        output_rfts_packing_po.po_id,
                        output_rfts_packing_po.type
                ) as rfts"), function ($join) {
                    $join->on("rfts.master_plan_id", "=", "master_plan.id");
                })->
                leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
                if ($this->dateFromFilter) $orderFilterSql->where('master_plan.tgl_plan', '>=', date('Y-m-d', strtotime('-10 days', strtotime($this->dateFromFilter))));
                if ($this->dateToFilter) $orderFilterSql->where('master_plan.tgl_plan', '<=', $this->dateToFilter);
                if ($this->groupBy == "size") $orderFilterSql->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->leftJoin('so_det', function ($join) { $join->on('so_det.id_so', '=', 'so.id'); $join->on('so_det.color', '=', 'master_plan.color'); });
                if ($this->groupBy == "size" && $this->sizeFilter) $orderFilterSql->where('so_det.size', $this->sizeFilter);
                if ($this->groupBy == "size" && $this->poFilter) $orderFilterSql->where(DB::raw("COALESCE(ppic_master_so.po, 'GUDANG STOK')"), 'like', '%'.$this->poFilter.'%');
                if ($this->selectedOrder) $orderFilterSql->where("act_costing.id", $this->selectedOrder);
                $orderFilterSql->
                    groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.sewing_line, master_plan.sewing_line), ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
                    orderBy("master_plan.id_ws", "asc")->
                    orderBy("act_costing.styleno", "asc")->
                    orderBy("master_plan.color", "asc")->
                    orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

                $this->orderFilter = $orderFilterSql->get();

            $masterPlanDateFilter = " between '".$this->dateFromFilter." 00:00:00' and '".$this->dateToFilter." 23:59:59'";
            $masterPlanDateFilter1 = " between '".date('Y-m-d', strtotime('-10 days', strtotime($this->dateFromFilter)))."' and '".$this->dateToFilter."'";

            $dailyOrderGroupSql = DB::connection('mysql_sb')->table('master_plan')->
                selectRaw("
                    master_plan.tgl_plan tanggal,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    master_plan.color,
                    COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line,
                    COALESCE(ppic_master_so.po, 'GUDANG STOK') as po,
                    COALESCE(rfts.type, 'rft') as type
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
                            ". ($this->selectedOrder ? " AND master_plan.id_ws = '".$this->selectedOrder."'" : "") . "
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
                "), function ($join) {
                    $join->on("rfts.master_plan_id", "=", "master_plan.id");
                })->
                leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");
                if ($this->groupBy == "size") $dailyOrderGroupSql->leftJoin('so_det', function ($join) { $join->on('rfts.so_det_id', '=', 'so_det.id'); });
                if ($this->dateFromFilter) $dailyOrderGroupSql->where('rfts.tanggal', '>=', date('Y-m-d', strtotime('-10 days', strtotime($this->dateFromFilter))));
                if ($this->dateToFilter) $dailyOrderGroupSql->where('rfts.tanggal', '<=', $this->dateToFilter);
                if ($this->colorFilter) $dailyOrderGroupSql->where('master_plan.color', $this->colorFilter);
                if ($this->lineFilter) $dailyOrderGroupSql->where('rfts.sewing_line', $this->lineFilter);
                if ($this->groupBy == "size" && $this->sizeFilter) $dailyOrderGroupSql->where('so_det.size', $this->sizeFilter);
                if ($this->poFilter) $dailyOrderGroupSql->where(DB::raw("COALESCE(ppic_master_so.po, 'GUDANG STOK')"), $this->poFilter);
                if ($this->selectedOrder) $dailyOrderGroupSql->where("act_costing.id", $this->selectedOrder);
                $dailyOrderGroupSql->
                    groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.sewing_line, master_plan.sewing_line), ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
                    orderBy("master_plan.id_ws", "asc")->
                    orderBy("act_costing.styleno", "asc")->
                    orderBy("master_plan.color", "asc")->
                    orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

                $this->dailyOrderGroup = $dailyOrderGroupSql->get();

            $dailyOrderOutputSql = DB::connection('mysql_sb')->table('master_plan')->
                selectRaw("
                    rfts.tanggal,
                    COALESCE(rfts.type, 'rft') as type,
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
                            rfts.po_id,
                            rfts.type
                            ".($this->groupBy == 'size' ? ', rfts.so_det_id ' : '')."
                        FROM
                            output_rfts_packing_po rfts
                            INNER JOIN master_plan ON master_plan.id = rfts.master_plan_id
                            LEFT JOIN userpassword ON userpassword.username = rfts.created_by_line
                            LEFT JOIN laravel_nds.ppic_master_so ON ppic_master_so.id = rfts.po_id
                        WHERE
                            rfts.updated_at ".$masterPlanDateFilter."
                            AND master_plan.tgl_plan ".$masterPlanDateFilter1."
                            ". ($this->selectedOrder ? " AND master_plan.id_ws = '".$this->selectedOrder."'" : "") . "
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
                    ) rfts
                "), "rfts.master_plan_id", "=", "master_plan.id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("laravel_nds.ppic_master_so", "ppic_master_so.id", "=", "rfts.po_id");

                if ($this->groupBy == "size") $dailyOrderOutputSql->leftJoin('so_det', 'so_det.id', '=', 'rfts.so_det_id');
                if ($this->selectedOrder) $dailyOrderOutputSql->where("act_costing.id", $this->selectedOrder);
                if ($this->dateFromFilter) $dailyOrderOutputSql->whereRaw('rfts.tanggal >= "'.$this->dateFromFilter.'"');
                if ($this->dateToFilter) $dailyOrderOutputSql->whereRaw('rfts.tanggal <= "'.$this->dateToFilter.'"');
                if ($this->colorFilter) $dailyOrderOutputSql->where('master_plan.color', $this->colorFilter);
                if ($this->lineFilter) $dailyOrderOutputSql->whereRaw('COALESCE(rfts.created_by, master_plan.sewing_line) = "'.$this->lineFilter.'"');
                if ($this->poFilter) $dailyOrderGroupSql->where(DB::raw("COALESCE(ppic_master_so.po, 'GUDANG STOK')"), $this->poFilter);
                if ($this->groupBy == "size" && $this->sizeFilter) $dailyOrderOutputSql->where('so_det.size', $this->sizeFilter);
                $dailyOrderOutputSql->
                    groupByRaw("master_plan.id_ws, act_costing.styleno, master_plan.color, COALESCE(rfts.created_by, master_plan.sewing_line) , master_plan.tgl_plan, rfts.tanggal, ppic_master_so.po, COALESCE(rfts.type, 'rft') ".($this->groupBy == 'size' ? ', so_det.size' : '')."")->
                    orderBy("master_plan.id_ws", "asc")->
                    orderBy("act_costing.styleno", "asc")->
                    orderBy("master_plan.color", "asc")->
                    orderByRaw("COALESCE(rfts.created_by, master_plan.sewing_line) asc, ppic_master_so.po asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));
                $this->dailyOrderOutputs = $dailyOrderOutputSql->get();

            if ($this->dailyOrderOutputs->sum("output") > 50000) {
                $this->emit("alert", "Big Data. '".$this->dailyOrderOutputs->sum("output")."' data.");
            }

            // Pre-aggregation
            $useSize = $this->groupBy === 'size';

            // Dates (sorted unique)
            $dates = $this->dailyOrderOutputs
                ->pluck('tanggal')
                ->unique()
                ->sort()
                ->values()
                ->all();

            // Containers
            $outputMap  = [];
            $rowTotals  = [];
            $dateTotals = [];
            $grandTotal = 0;

            foreach ($this->dailyOrderOutputs as $row) {

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

            \Log::info("Query Completed");
        }

        return view('livewire.packing.track-packing-output', [
            "dates" => $dates,
            "outputMap" => $outputMap,
            "rowTotals" => $rowTotals,
            "dateTotals" => $dateTotals,
            "grandTotal" => $grandTotal,
        ]);
    }

    public function dehydrate()
    {
        $this->emit("initFixedColumn");
    }
}
