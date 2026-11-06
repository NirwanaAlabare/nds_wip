<?php

namespace App\Http\Livewire\Sewing;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\RftPacking;
use App\Models\SignalBit\RftPackingPo;
use App\Services\SewingOrderOutputService;
use DB;

class TrackOrderOutput extends Component
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

        $this->groupBy = "size";
        $this->baseUrl = url('/');

        $this->search = false;
    }

    public function clearFilter()
    {
        $this->colorFilter = null;
        $this->lineFilter = null;
        $this->sizeFilter = null;
        $this->search = false;
    }

    public function updatedSelectedOrder()
    {
        if ($this->selectedOrder) {
            $firstPlan = MasterPlan::selectRaw("tgl_plan")->where("id_ws", $this->selectedOrder)->orderBy("tgl_plan", "asc")->first();

            $lastPlan = collect(DB::connection("mysql_sb")->select("
                SELECT
                    MAX(tgl_plan) as tgl_plan
                FROM
                    (
                        SELECT DATE(MAX(output_rfts_packing_po.updated_at)) as tgl_plan FROM output_rfts_packing_po LEFT JOIN so_det ON so_det.id = output_rfts_packing_po.so_det_id LEFT JOIN so ON so.id = so_det.id_so LEFT JOIN act_costing ON act_costing.id = so.id_cost LEFT JOIN master_plan ON master_plan.id = output_rfts_packing_po.master_plan_id WHERE act_costing.id = '".$this->selectedOrder."'
                            UNION ALL
                        SELECT DATE(MAX(output_rfts_packing.updated_at)) as tgl_plan FROM output_rfts_packing LEFT JOIN so_det ON so_det.id = output_rfts_packing.so_det_id LEFT JOIN so ON so.id = so_det.id_so LEFT JOIN act_costing ON act_costing.id = so.id_cost LEFT JOIN master_plan ON master_plan.id = output_rfts_packing.master_plan_id WHERE act_costing.id = '".$this->selectedOrder."'
                            UNION ALL
                        SELECT DATE(MAX(output_rfts.updated_at)) as tgl_plan FROM output_rfts LEFT JOIN so_det ON so_det.id = output_rfts.so_det_id LEFT JOIN so ON so.id = so_det.id_so LEFT JOIN act_costing ON act_costing.id = so.id_cost LEFT JOIN master_plan ON master_plan.id = output_rfts.master_plan_id WHERE act_costing.id = '".$this->selectedOrder."'
                    ) as output
            "))->first();

            if ($firstPlan) {
                $this->dateFromFilter = $firstPlan->tgl_plan;
            }
            else {
                $this->dateFromFilter = date("Y-m-d");
            }

            if ($lastPlan) {
                $this->dateToFilter = $lastPlan->tgl_plan;
            }
            else {
                $this->dateToFilter = date("Y-m-d");
            }

            // $this->search = true;
        }
    }

    public function setSearch() {
        $this->search = true;
    }

    public function render()
    {
        ini_set("max_execution_time", 3600);
        ini_set("memory_limit", "2048M");

        if (!$this->dateFromFilter) {
            $this->dateFromFilter = date("Y-m-d");
        }

        if (!$this->dateToFilter) {
            $this->dateToFilter = date("Y-m-d");
        }

        $this->loadingOrderOutput = false;

        // outputType menjadi bagian nama tabel, nilainya bisa diubah dari browser
        $this->outputType = SewingOrderOutputService::normalizeOutputType($this->outputType);

        // Supplier List
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

        // Order List
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

        // Query for Filtering
        $orderFilterSql = DB::connection('mysql_sb')->table(DB::raw("(
                SELECT
                    DATE(output_rfts".($this->outputType).".updated_at) tanggal,
                    master_plan.id_ws,
                    act_costing.id act_id_ws,
                    so_det.color,
                    output_rfts".($this->outputType).".master_plan_id,
                    userpassword.username sewing_line
                FROM
                    output_rfts".($this->outputType)."
                    ".(
                        $this->outputType == "_packing_po" ?
                        "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->outputType).".created_by_line" :
                        (
                            $this->outputType != "_packing" ?
                            "LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts".($this->outputType).".created_by LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id" :
                            "LEFT JOIN userpassword ON userpassword.username = output_rfts".($this->outputType).".created_by"
                        )
                    )."
                    LEFT JOIN master_plan on master_plan.id = output_rfts".($this->outputType).".master_plan_id
                    LEFT JOIN so_det ON so_det.id = output_rfts".($this->outputType).".so_det_id
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                WHERE
                    output_rfts".($this->outputType).".created_by IS NOT NULL
                    AND output_rfts".($this->outputType).".updated_at >= '".$this->dateFromFilter." 00:00:00'
                    AND output_rfts".($this->outputType).".updated_at <= '".$this->dateToFilter." 23:59:59'
                    " . ($this->selectedOrder ? " AND act_costing.id = '".$this->selectedOrder."'" : "") . "
                GROUP BY
                    output_rfts".($this->outputType).".master_plan_id,
                    output_rfts".($this->outputType).".created_by
            ) as rfts"))->
            selectRaw("
                rfts.tanggal tanggal,
                act_costing.kpno ws,
                act_costing.styleno style,
                rfts.color,
                COALESCE(rfts.sewing_line, master_plan.sewing_line) as sewing_line
                ".($this->groupBy == "size" ? ", so_det.id as so_det_id, so_det.size, (CASE WHEN so_det.dest is not null AND so_det.dest != '-' THEN CONCAT(so_det.size, ' - ', so_det.dest) ELSE so_det.size END) sizedest" : "")."
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "rfts.act_id_ws")->
            leftJoin("master_plan", "master_plan.id", "=", "rfts.master_plan_id");
            if ($this->dateFromFilter) $orderFilterSql->where('master_plan.tgl_plan', '>=', date('Y-m-d', strtotime('-120 days', strtotime($this->dateFromFilter))));
            if ($this->dateToFilter) $orderFilterSql->where('master_plan.tgl_plan', '<=', $this->dateToFilter);
            if ($this->groupBy == "size") $orderFilterSql->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')->leftJoin('so_det', function ($join) { $join->on('so_det.id_so', '=', 'so.id'); $join->on('so_det.color', '=', 'master_plan.color'); });
            if ($this->groupBy == "size" && $this->sizeFilter) $orderFilterSql->where('so_det.size', $this->sizeFilter);
            if ($this->selectedOrder) $orderFilterSql->where("act_costing.id", $this->selectedOrder);
            $orderFilterSql->
                groupByRaw("rfts.act_id_ws, act_costing.styleno, rfts.color, COALESCE(rfts.sewing_line, master_plan.sewing_line) ".($this->groupBy == "size" ? ", so_det.size" : "")."")->
                orderBy("rfts.act_id_ws", "asc")->
                orderBy("act_costing.styleno", "asc")->
                orderBy("rfts.color", "asc")->
                orderByRaw("COALESCE(rfts.sewing_line, master_plan.sewing_line) asc ".($this->groupBy == 'size' ? ', so_det.id asc' : ''));

            $this->orderFilter = $orderFilterSql->get();

        $dates = [];
        $outputMap = [];
        $rowTotals = [];
        $dateTotals = [];
        $grandTotal = 0;

        // Only run the query when the search is active
        if ($this->search) {
            $this->search = false;

            // Query sama dengan export Excel (OrderOutputExport)
            $sewingOrderOutputService = new SewingOrderOutputService();
            $filter = [
                'dateFrom' => $this->dateFromFilter,
                'dateTo' => $this->dateToFilter,
                'outputType' => $this->outputType,
                'groupBy' => $this->groupBy,
                'order' => $this->selectedOrder,
                'buyer' => $this->selectedSupplier,
                'color' => $this->colorFilter,
                'line' => $this->lineFilter,
                'size' => $this->sizeFilter,
            ];

            $this->dailyOrderGroup = $sewingOrderOutputService->orderGroup($filter);
            $this->dailyOrderOutputs = $sewingOrderOutputService->orderOutputs($filter);

            if ($this->dailyOrderOutputs->sum("output") > 50000) {
                $this->emit("alert", "Big Data. '".$this->dailyOrderOutputs->sum("output")."' data.");
            }

            // Pre-aggregation
            [
                "dates" => $dates,
                "outputMap" => $outputMap,
                "rowTotals" => $rowTotals,
                "dateTotals" => $dateTotals,
                "grandTotal" => $grandTotal,
            ] = $sewingOrderOutputService->aggregate($this->dailyOrderOutputs, $this->groupBy);

            \Log::info("Query Completed");
        }

        return view('livewire.sewing.track-order-output', [
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
