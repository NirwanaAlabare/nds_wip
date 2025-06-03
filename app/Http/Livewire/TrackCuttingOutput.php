<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ActCosting;
use App\Models\FormCutInput;
use DB;

class TrackCuttingOutput extends Component
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
    public $mejaFilter;
    public $sizeFilter;

    public $groupBy;

    public $baseUrl;

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

        $this->colorFilter = null;
        $this->mejaFilter = null;
        $this->sizeFilter = null;

        $this->groupBy = "size";
        $this->baseUrl = url('/');
    }

    public function clearFilter()
    {
        $this->colorFilter = null;
        $this->mejaFilter = null;
        $this->sizeFilter = null;
    }

    public function updatedSelectedOrder()
    {
        $firstPlan = FormCutInput::selectRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tanggal")->leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->where("marker_input.act_costing_id", $this->selectedOrder)->orderByRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) asc")->first();
        $lastPlan = FormCutInput::selectRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tanggal")->leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->where("marker_input.act_costing_id", $this->selectedOrder)->orderByRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) desc")->first();

        if ($firstPlan) {
            $this->dateFromFilter = $firstPlan->tanggal;
            $this->dateToFilter = $lastPlan->tanggal;
        } else {
            $this->dateFromFilter = date("Y-m-d");
            $this->dateToFilter = date("Y-m-d");
        }
    }

    public function render()
    {
        ini_set('max_execution_time', 3600);

        $this->loadingOrderOutput = false;

        $dateFilter = " AND COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) between '".$this->dateFromFilter."' and '".$this->dateToFilter."' ";
        $orderFilterQuery = $this->selectedOrder ? " AND marker_cutting.act_costing_id = '".$this->selectedOrder."' " : "";
        $colorFilterQuery = $this->colorFilter ? " AND marker_cutting.color = '".$this->colorFilter."' " : "";
        $mejaFilterQuery = $this->mejaFilter ? " AND marker_cutting.id_meja = '".$this->mejaFilter."' " : "";
        $sizeFilterQuery = $this->groupBy == "size" && $this->sizeFilter ? "AND marker_cutting.size = '".$this->mejaFilter."'" : "";

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
        if ($this->selectedSupplier) {
            $orderSql->where('id_buyer', $this->selectedSupplier);
        }
        $this->orders = $orderSql->
            orderBy('cost_date', 'desc')->
            orderBy('kpno', 'asc')->
            groupBy('kpno')->
            get();

        $orderFilterSql = FormCutInput::selectRaw("
                meja.id id_meja,
                meja.name meja,
                COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tanggal,
                marker_input.act_costing_id,
                marker_input.act_costing_ws ws,
                marker_input.style,
                marker_input.color,
                marker_input.panel
                ".($this->groupBy == 'size' ? ', marker_input_detail.so_det_id, marker_input_detail.size' : '')."
            ")->
            leftJoin(
            DB::raw("
                (
                    SELECT
                        meja.id id_meja,
                        meja.`name` meja,
                        COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form,
                        form_cut_input.id_marker,
                        form_cut_input.id,
                        form_cut_input.no_form,
                        form_cut_input.qty_ply,
                        form_cut_input.total_lembar,
                        form_cut_input.notes,
                        SUM(form_cut_input_detail.lembar_gelaran) detail
                    FROM
                        form_cut_input
                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                    WHERE
                        form_cut_input.`status` = 'SELESAI PENGERJAAN'
                        AND form_cut_input.id_marker is not null
                        ".$dateFilter."
                    GROUP BY
                        form_cut_input.id
                ) form_cut"
            ), "form_cut.id", "=", "form_cut_input.id")->
            leftJoin("users as meja", "meja.id", "=", "form_cut_input.no_meja")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", function ($join) { $join->on('marker_input.id', '=', 'marker_input_detail.marker_id'); $join->on('marker_input_detail.ratio', '>', DB::raw('0')); })->
            whereRaw("
                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                AND form_cut_input.id_marker is not null
                AND COALESCE(form_cut.total_lembar, form_cut.detail) > 0
            ");
            if ($this->dateFromFilter) {
                $orderFilterSql->whereRaw('COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) >= "'.$this->dateFromFilter.'"');
            }
            if ($this->dateToFilter) {
                $orderFilterSql->whereRaw('COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) <= "'.$this->dateToFilter.'"');
            }
            $orderFilterSql->
                groupByRaw("marker_input.act_costing_id, marker_input.style, marker_input.color, marker_input.panel, form_cut_input.no_meja, marker_input_detail.so_det_id")->
                orderBy("marker_input.act_costing_id", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderBy("marker_input.panel", "asc")->
                orderByRaw("form_cut_input.no_meja asc, marker_input_detail.so_det_id asc, marker_input_detail.size asc");

            $this->orderFilter = $orderFilterSql->get();

        $dailyOrderGroupSql = FormCutInput::selectRaw("
                meja.id id_meja,
                meja.name meja,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tanggal,
                marker_input.act_costing_id,
                marker_input.act_costing_ws ws,
                marker_input.style,
                marker_input.color,
                marker_input.panel
                ".($this->groupBy == 'size' ? ', marker_input_detail.so_det_id, marker_input_detail.size' : '')."
            ")->
            leftJoin(
            DB::raw("
                (
                    SELECT
                        meja.id id_meja,
                        meja.`name` meja,
                        COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form,
                        form_cut_input.id_marker,
                        form_cut_input.id,
                        form_cut_input.no_form,
                        form_cut_input.qty_ply,
                        form_cut_input.total_lembar,
                        form_cut_input.notes,
                        SUM(form_cut_input_detail.lembar_gelaran) detail
                    FROM
                        form_cut_input
                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                    WHERE
                        form_cut_input.`status` = 'SELESAI PENGERJAAN'
                        AND form_cut_input.id_marker is not null
                        ".$dateFilter."
                    GROUP BY
                        form_cut_input.id
                ) form_cut"
            ), "form_cut.id", "=", "form_cut_input.id")->
            leftJoin("users as meja", "meja.id", "=", "form_cut_input.no_meja")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", function ($join) { $join->on('marker_input.id', '=', 'marker_input_detail.marker_id'); $join->on('marker_input_detail.ratio', '>', DB::raw('0')); })->
            whereRaw("
                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                AND form_cut_input.id_marker is not null
                AND COALESCE(form_cut.total_lembar, form_cut.detail) > 0
            ");
            if ($this->dateFromFilter) {
                $dailyOrderGroupSql->whereRaw('COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) >= "'.$this->dateFromFilter.'"');
            }
            if ($this->dateToFilter) {
                $dailyOrderGroupSql->whereRaw('COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) <= "'.$this->dateToFilter.'"');
            }
            if ($this->colorFilter) {
                $dailyOrderGroupSql->where('marker_input.color', $this->colorFilter);
            }
            if ($this->mejaFilter) {
                $dailyOrderGroupSql->where('form_cut_input.no_meja', $this->mejaFilter);
            }
            if ($this->groupBy == "size" && $this->sizeFilter) {
                $dailyOrderGroupSql->where('marker_input_detail.size', $this->sizeFilter);
            }
            if ($this->selectedOrder) {
                $dailyOrderGroupSql->where("marker_input.act_costing_id", $this->selectedOrder);
            }
            $dailyOrderGroupSql->
                groupByRaw("marker_input.act_costing_id, marker_input.style, marker_input.color, marker_input.panel, form_cut_input.no_meja ".($this->groupBy == 'size' ? ', marker_input_detail.so_det_id ' : ''))->
                orderBy("marker_input.act_costing_id", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderByRaw("form_cut_input.no_meja asc, marker_input.panel, marker_input_detail.so_det_id asc, marker_input_detail.size asc");

            $this->dailyOrderGroup = $dailyOrderGroupSql->get();

        $dailyOrderOutputSql = collect(
                DB::select("
                    SELECT
                        marker_cutting.tgl_form_cut tanggal,
                        marker_cutting.id_meja,
                        UPPER(marker_cutting.meja) meja,
                        marker_cutting.act_costing_ws ws,
                        marker_cutting.style,
                        marker_cutting.color,
                        marker_cutting.panel,
                        ".($this->groupBy == 'size' ? ' marker_cutting.so_det_id, marker_cutting.size, ' : '')."
                        SUM((marker_cutting.form_gelar * marker_cutting.ratio) + COALESCE(marker_cutting.diff, 0)) qty
                    FROM
                        (
                            SELECT
                                marker_input.kode,
                                GROUP_CONCAT(form_cut.no_form, form_cut.meja) no_form_meja,
                                form_cut.id_meja,
                                form_cut.meja,
                                form_cut.tgl_form_cut,
                                marker_input.buyer,
                                marker_input.act_costing_id,
                                marker_input.act_costing_ws,
                                marker_input.style,
                                marker_input.color,
                                marker_input.panel,
                                marker_input.cons_ws,
                                marker_input.unit_panjang_marker unit,
                                marker_input_detail.so_det_id,
                                master_sb_ws.size,
                                marker_input_detail.ratio,
                                COALESCE(marker_input.notes, form_cut.notes) notes,
                                marker_input.gelar_qty marker_gelar,
                                SUM(form_cut.qty_ply) spreading_gelar,
                                SUM(COALESCE(form_cut.total_lembar, form_cut.detail)) form_gelar,
                                SUM(modify_size_qty.difference_qty) diff
                            FROM
                            marker_input
                            INNER JOIN
                                marker_input_detail on marker_input_detail.marker_id = marker_input.id
                            LEFT JOIN
                                master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                            INNER JOIN
                                (
                                    SELECT
                                        meja.id id_meja,
                                        meja.`name` meja,
                                        COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form_cut,
                                        form_cut_input.id_marker,
                                        form_cut_input.id,
                                        form_cut_input.no_form,
                                        form_cut_input.qty_ply,
                                        form_cut_input.total_lembar,
                                        form_cut_input.notes,
                                        SUM(form_cut_input_detail.lembar_gelaran) detail
                                    FROM
                                        form_cut_input
                                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                                    WHERE
                                        form_cut_input.`status` = 'SELESAI PENGERJAAN'
                                        ".$dateFilter."
                                        ".($this->mejaFilter ? "AND form_cut_input.no_meja = '".$this->mejaFilter."'" :  "")."
                                    GROUP BY
                                        form_cut_input.id
                                ) form_cut on form_cut.id_marker = marker_input.kode
                            LEFT JOIN
                                modify_size_qty ON modify_size_qty.form_cut_id = form_cut.id AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                            where
                                (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                                AND marker_input_detail.ratio > 0
                                ".($this->colorFilter ? "AND marker_input.color = '".$this->colorFilter."'" :  "")."
                                ".($this->groupBy == "size" && $this->sizeFilter ? "AND marker_input_detail.size = '".$this->sizeFilter."'" : "")."
                                ".($this->selectedOrder ? "AND marker_input.act_costing_id = '".$this->selectedOrder."'" : "")."
                            group by
                                marker_input.id,
                                marker_input_detail.so_det_id,
                                form_cut.tgl_form_cut,
                                form_cut.meja
                        ) marker_cutting
                    GROUP BY
                        marker_cutting.act_costing_id,
                        marker_cutting.color,
                        marker_cutting.id_meja,
                        marker_cutting.panel,
                        ".($this->groupBy == 'size' ? ' marker_cutting.so_det_id, marker_cutting.size, ' : '')."
                        marker_cutting.tgl_form_cut
                    ORDER BY
                        marker_cutting.act_costing_id,
                        marker_cutting.color,
                        marker_cutting.id_meja,
                        marker_cutting.panel,
                        ".($this->groupBy == 'size' ? ' marker_cutting.so_det_id, marker_cutting.size, ' : '')."
                        marker_cutting.tgl_form_cut
                ")
            );

            $this->dailyOrderOutputs = $dailyOrderOutputSql;

        if ($this->dailyOrderOutputs->count() > 500) {
            $this->emit("alert", "Big Data. '".$this->dailyOrderOutputs->count()."' data.");
        }

        \Log::info("Query Completed");

        return view('livewire.track-cutting-output');
    }

    // public function dehydrate()
    // {
    //     $this->emit("initFixedColumn");
    // }
}
