<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ActCosting;
use App\Models\Cutting\FormCutInput;
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
    public $panelFilter;
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
        $this->panelFilter = null;
        $this->mejaFilter = null;
        $this->sizeFilter = null;

        $this->groupBy = "size";
        $this->baseUrl = url('/');
    }

    public function clearFilter()
    {
        $this->colorFilter = null;
        $this->panelFilter = null;
        $this->mejaFilter = null;
        $this->sizeFilter = null;
    }

    public function updatedSelectedOrder()
    {
        $formCutFirstDate = DB::table("form_cut_input")
            ->selectRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) AS tanggal")
            ->leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")
            ->where("marker_input.act_costing_id", $this->selectedOrder)
            ->orderByRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut)")
            ->value("tanggal");

        $formCutLastDate = DB::table("form_cut_input")
            ->selectRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) AS tanggal")
            ->leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")
            ->where("marker_input.act_costing_id", $this->selectedOrder)
            ->orderByRaw("COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) DESC")
            ->value("tanggal");

        $formRejectFirstDate = DB::table("form_cut_reject")
            ->selectRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal) AS tanggal")
            ->where("form_cut_reject.act_costing_id", $this->selectedOrder)
            ->orderByRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal)")
            ->value("tanggal");

        $formRejectLastDate = DB::table("form_cut_reject")
            ->selectRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal) AS tanggal")
            ->where("form_cut_reject.act_costing_id", $this->selectedOrder)
            ->orderByRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal) DESC")
            ->value("tanggal");

        $formPcsFirstDate = DB::table("form_cut_piece")
            ->selectRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal) AS tanggal")
            ->where("form_cut_piece.act_costing_id", $this->selectedOrder)
            ->orderByRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal)")
            ->value("tanggal");

        $formPcsLastDate = DB::table("form_cut_piece")
            ->selectRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal) AS tanggal")
            ->where("form_cut_piece.act_costing_id", $this->selectedOrder)
            ->orderByRaw("COALESCE(DATE(updated_at), DATE(created_at), tanggal) DESC")
            ->value("tanggal");

        $dates = collect([
            $formCutFirstDate,
            $formCutLastDate,
            $formRejectFirstDate,
            $formRejectLastDate,
            $formPcsFirstDate,
            $formPcsLastDate
        ])->filter(); // remove nulls

        $this->dateFromFilter = $dates->min() ?? date("Y-m-d");
        $this->dateToFilter   = $dates->max() ?? date("Y-m-d");
    }

    public function render()
    {
        ini_set('max_execution_time', 3600);

        $this->loadingOrderOutput = false;

        $dateFilter = " AND COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) between '".$this->dateFromFilter."' and '".$this->dateToFilter."' ";
        $orderFilterQuery = $this->selectedOrder ? " AND marker_cutting.act_costing_id = '".$this->selectedOrder."' " : "";
        $colorFilterQuery = $this->colorFilter ? " AND marker_cutting.color = '".$this->colorFilter."' " : "";
        $panelFilterQuery = $this->panelFilter ? " AND marker_cutting.panel = '".$this->panelFilter."' " : "";
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

        $orderFilterSql = DB::select("
            SELECT
                meja.id id_meja,
                meja.NAME meja,
                COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) tanggal,
                marker_input.act_costing_id,
                marker_input.act_costing_ws ws,
                marker_input.style,
                marker_input.color,
                marker_input.panel,
                marker_input_detail.so_det_id,
                marker_input_detail.size
            FROM
                `form_cut_input`
                LEFT JOIN (
                SELECT
                    meja.id id_meja,
                    meja.`name` meja,
                    COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) tgl_form,
                    form_cut_input.id_marker,
                    form_cut_input.id,
                    form_cut_input.no_form,
                    form_cut_input.qty_ply,
                    form_cut_input.total_lembar,
                    form_cut_input.notes,
                    SUM( form_cut_input_detail.lembar_gelaran ) detail
                FROM
                    form_cut_input
                    LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                    INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                WHERE
                    form_cut_input.`status` = 'SELESAI PENGERJAAN'
                    AND form_cut_input.id_marker IS NOT NULL
                    ".$dateFilter."
                GROUP BY
                    form_cut_input.id
                ) form_cut ON `form_cut`.`id` = `form_cut_input`.`id`
                LEFT JOIN `users` AS `meja` ON `meja`.`id` = `form_cut_input`.`no_meja`
                LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                LEFT JOIN `marker_input_detail` ON `marker_input`.`id` = `marker_input_detail`.`marker_id`
                AND `marker_input_detail`.`ratio` > 0
            WHERE
                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                AND form_cut_input.id_marker IS NOT NULL
                AND COALESCE ( form_cut.total_lembar, form_cut.detail ) > 0
                AND COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) >= '".$this->dateFromFilter."'
                AND COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) <= '".$this->dateToFilter."' AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                marker_input.act_costing_id,
                marker_input.style,
                marker_input.color,
                marker_input.panel,
                form_cut_input.no_meja,
                marker_input_detail.so_det_id
        UNION
            SELECT
                '-' id_meja,
                '-' meja,
                COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) tanggal,
                form_cut_reject.act_costing_id,
                form_cut_reject.act_costing_ws ws,
                form_cut_reject.style,
                form_cut_reject.color,
                form_cut_reject.panel,
                form_cut_reject_detail.so_det_id,
                form_cut_reject_detail.size
            FROM
                `form_cut_reject`
                LEFT JOIN `form_cut_reject_detail` ON `form_cut_reject_detail`.`form_id` = `form_cut_reject`.`id`
            WHERE
                form_cut_reject_detail.`qty` > 0
                AND COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) >= '".$this->dateFromFilter."'
                AND COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) <= '".$this->dateToFilter."' AND form_cut_reject.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                form_cut_reject.act_costing_id,
                form_cut_reject.style,
                form_cut_reject.color,
                form_cut_reject.panel,
                form_cut_reject_detail.so_det_id
        UNION
            SELECT
                '-' id_meja,
                '-' meja,
                COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) tanggal,
                form_cut_piece.act_costing_id,
                form_cut_piece.act_costing_ws ws,
                form_cut_piece.style,
                form_cut_piece.color,
                form_cut_piece.panel,
                form_cut_piece_detail_size.so_det_id,
                form_cut_piece_detail_size.size
            FROM
                `form_cut_piece`
                LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
            WHERE
                form_cut_piece.`status` = 'complete'
                AND COALESCE ( form_cut_piece_detail_size.qty ) > 0
                AND COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) >= '".$this->dateFromFilter."'
                AND COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) <= '".$this->dateToFilter."' AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                form_cut_piece.act_costing_id,
                form_cut_piece.style,
                form_cut_piece.color,
                form_cut_piece.panel,
                form_cut_piece_detail_size.so_det_id
            ORDER BY
                `act_costing_id` ASC,
                `style` ASC,
                `color` ASC,
                `panel` ASC,
                `id_meja` ASC,
                `so_det_id` ASC,
                `size` ASC
        ");

        $this->orderFilter = collect($orderFilterSql);

        $dailyOrderGroupSql = DB::select("
            SELECT
                meja.id id_meja,
                meja.NAME meja,
                form_cut_input.id_marker,
                form_cut_input.no_form,
                COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) tanggal,
                marker_input.act_costing_id,
                marker_input.act_costing_ws ws,
                marker_input.style,
                marker_input.color,
                marker_input.panel
                ".($this->groupBy == 'size' ? ", marker_input_detail.so_det_id, CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size" : '')."
            FROM
                `form_cut_input`
                LEFT JOIN (
                    SELECT
                        meja.id id_meja,
                        meja.`name` meja,
                        COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) tgl_form,
                        form_cut_input.id_marker,
                        form_cut_input.id,
                        form_cut_input.no_form,
                        form_cut_input.qty_ply,
                        form_cut_input.total_lembar,
                        form_cut_input.notes,
                        SUM( form_cut_input_detail.lembar_gelaran ) detail
                    FROM
                        form_cut_input
                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.form_cut_id = form_cut_input.id
                    WHERE
                        form_cut_input.`status` = 'SELESAI PENGERJAAN'
                        AND form_cut_input.id_marker IS NOT NULL
                        ".$dateFilter."
                    GROUP BY
                        form_cut_input.id
                ) form_cut ON `form_cut`.`id` = `form_cut_input`.`id`
                LEFT JOIN `users` AS `meja` ON `meja`.`id` = `form_cut_input`.`no_meja`
                LEFT JOIN `marker_input` ON `marker_input`.`kode` = `form_cut_input`.`id_marker`
                LEFT JOIN `marker_input_detail` ON `marker_input`.`id` = `marker_input_detail`.`marker_id`
                AND `marker_input_detail`.`ratio` > 0
                LEFT JOIN `master_sb_ws` ON `marker_input_detail`.`so_det_id` = `master_sb_ws`.`id_so_det`
            WHERE
                form_cut_input.`status` = 'SELESAI PENGERJAAN'
                AND form_cut_input.id_marker IS NOT NULL
                AND COALESCE ( form_cut.total_lembar, form_cut.detail ) > 0
                AND COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) >= '".$this->dateFromFilter."'
                AND COALESCE ( DATE ( waktu_selesai ), DATE ( waktu_mulai ), tgl_form_cut ) <= '".$this->dateToFilter."' AND form_cut_input.tgl_form_cut >= DATE ( NOW()- INTERVAL 2 YEAR )
                ".($this->colorFilter ? "AND marker_input.color = '".$this->colorFilter."'" : "")."
                ".($this->panelFilter ? "AND marker_input.panel = '".$this->panelFilter."'" : "")."
                ".($this->mejaFilter ? "AND form_cut_input.no_meja = '".$this->mejaFilter."'" : "")."
                ".($this->sizeFilter ? "AND master_sb_ws.size = '".$this->sizeFilter."'" : "")."
                ".($this->selectedOrder ? "AND marker_input.act_costing_id = '".$this->selectedOrder."'" : "")."
            GROUP BY
                marker_input.act_costing_id,
                marker_input.style,
                marker_input.color,
                marker_input.panel,
                form_cut_input.no_meja,
                marker_input_detail.so_det_id
        UNION
            SELECT
                null id_meja,
                '-' meja,
                '-' id_marker,
                form_cut_reject.no_form,
                COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) tanggal,
                form_cut_reject.act_costing_id,
                form_cut_reject.act_costing_ws ws,
                form_cut_reject.style,
                form_cut_reject.color,
                form_cut_reject.panel
                ".($this->groupBy == 'size' ? ", form_cut_reject_detail.so_det_id, CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size" : '')."
            FROM
                `form_cut_reject`
                LEFT JOIN `form_cut_reject_detail` ON `form_cut_reject_detail`.`form_id` = `form_cut_reject`.`id`
                LEFT JOIN `master_sb_ws` ON `form_cut_reject_detail`.`so_det_id` = `master_sb_ws`.`id_so_det`
            WHERE
                form_cut_reject_detail.`qty` > 0
                AND COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) >= '".$this->dateFromFilter."'
                AND COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) <= '".$this->dateToFilter."' AND form_cut_reject.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
                ".($this->colorFilter ? "AND form_cut_reject.color = '".$this->colorFilter."'" : "")."
                ".($this->panelFilter ? "AND form_cut_reject.panel = '".$this->panelFilter."'" : "")."
                ".($this->mejaFilter ? "AND '-' = '".$this->mejaFilter."'" : "")."
                ".($this->sizeFilter ? "AND master_sb_ws.size = '".$this->sizeFilter."'" : "")."
                ".($this->selectedOrder ? "AND form_cut_reject.act_costing_id = '".$this->selectedOrder."'" : "")."
            GROUP BY
                form_cut_reject.act_costing_id,
                form_cut_reject.style,
                form_cut_reject.color,
                form_cut_reject.panel,
                form_cut_reject_detail.so_det_id
        UNION
            SELECT
                null id_meja,
                '-' meja,
                '-' id_marker,
                form_cut_piece.no_form,
                COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) tanggal,
                form_cut_piece.act_costing_id,
                form_cut_piece.act_costing_ws ws,
                form_cut_piece.style,
                form_cut_piece.color,
                form_cut_piece.panel
                ".($this->groupBy == 'size' ? ", form_cut_piece_detail_size.so_det_id, CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size" : '')."
            FROM
                `form_cut_piece`
                LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                LEFT JOIN `master_sb_ws` ON `form_cut_piece_detail_size`.`so_det_id` = `master_sb_ws`.`id_so_det`
            WHERE
                form_cut_piece.`status` = 'complete'
                AND COALESCE ( form_cut_piece_detail_size.qty ) > 0
                AND COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) >= '".$this->dateFromFilter."'
                AND COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) <= '".$this->dateToFilter."' AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
                ".($this->colorFilter ? "AND form_cut_piece.color = '".$this->colorFilter."'" : "")."
                ".($this->panelFilter ? "AND form_cut_piece.panel = '".$this->panelFilter."'" : "")."
                ".($this->mejaFilter ? "AND '-' = '".$this->mejaFilter."'" : "")."
                ".($this->sizeFilter ? "AND master_sb_ws.size = '".$this->sizeFilter."'" : "")."
                ".($this->selectedOrder ? "AND form_cut_piece.act_costing_id = '".$this->selectedOrder."'" : "")."
            GROUP BY
                form_cut_piece.act_costing_id,
                form_cut_piece.style,
                form_cut_piece.color,
                form_cut_piece.panel,
                form_cut_piece_detail_size.so_det_id
            ORDER BY
                `act_costing_id` ASC,
                `style` ASC,
                `color` ASC,
                id_meja ASC,
                panel,
                so_det_id ASC,
                size ASC
        ");

        $this->dailyOrderGroup = collect($dailyOrderGroupSql);

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
                            form_cut.no_form,
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
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            marker_input_detail.ratio,
                            COALESCE(marker_input.notes, form_cut.notes) notes,
                            marker_input.gelar_qty marker_gelar,
                            SUM(form_cut.qty_ply) spreading_gelar,
                            SUM(COALESCE(form_cut.detail, form_cut.total_lembar)) form_gelar,
                            SUM(modify_size_qty.difference_qty) diff
                        FROM
                            marker_input
                            INNER JOIN
                                marker_input_detail on marker_input_detail.marker_id = marker_input.id
                            INNER JOIN
                                master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                            INNER JOIN
                                (
                                    SELECT
                                        form_cut_input.no_meja id_meja,
                                        meja.`name` meja,
                                        COALESCE(DATE(form_cut_input.waktu_selesai), DATE(form_cut_input.waktu_mulai), DATE(form_cut_input.tgl_input)) tgl_form_cut,
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
                                        AND form_cut_input.waktu_mulai is not null
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
                            ".($this->panelFilter ? "AND marker_input.panel = '".$this->panelFilter."'" :  "")."
                            ".($this->groupBy == "size" && $this->sizeFilter ? "AND master_sb_ws.size = '".$this->sizeFilter."'" : "")."
                            ".($this->selectedOrder ? "AND marker_input.act_costing_id = '".$this->selectedOrder."'" : "")."
                        group by
                            marker_input.id,
                            marker_input_detail.so_det_id,
                            form_cut.id
                    union
                        SELECT
                            '-' as kode,
                            form_cut_reject.no_form,
                            null as id_meja,
                            '-' as meja,
                            COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) tgl_form_cut,
                            form_cut_reject.buyer,
                            form_cut_reject.act_costing_id,
                            form_cut_reject.act_costing_ws,
                            form_cut_reject.style,
                            form_cut_reject.color,
                            form_cut_reject.panel,
                            '-' cons_ws,
                            'PCS' unit,
                            form_cut_reject_detail.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            1 as ratio,
                            COALESCE('REJECT') notes,
                            SUM(form_cut_reject_detail.qty) marker_gelar,
                            SUM(form_cut_reject_detail.qty) spreading_gelar,
                            SUM(form_cut_reject_detail.qty) form_gelar,
                            null diff
                        FROM
                            `form_cut_reject`
                            LEFT JOIN `form_cut_reject_detail` ON `form_cut_reject_detail`.`form_id` = `form_cut_reject`.`id`
                            LEFT JOIN `master_sb_ws` ON `form_cut_reject_detail`.`so_det_id` = `master_sb_ws`.`id_so_det`
                        WHERE
                            form_cut_reject_detail.`qty` > 0
                            AND COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) >= '".$this->dateFromFilter."'
                            AND COALESCE ( DATE ( form_cut_reject.updated_at ), DATE ( form_cut_reject.created_at ), form_cut_reject.tanggal ) <= '".$this->dateToFilter."' AND form_cut_reject.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
                            ".($this->colorFilter ? "AND form_cut_reject.color = '".$this->colorFilter."'" : "")."
                            ".($this->panelFilter ? "AND form_cut_reject.panel = '".$this->panelFilter."'" : "")."
                            ".($this->mejaFilter ? "AND '-' = '".$this->mejaFilter."'" : "")."
                            ".($this->sizeFilter ? "AND master_sb_ws.size = '".$this->sizeFilter."'" : "")."
                            ".($this->selectedOrder ? "AND form_cut_reject.act_costing_id = '".$this->selectedOrder."'" : "")."
                        GROUP BY
                            form_cut_reject.id,
                            form_cut_reject_detail.so_det_id
                    union
                        SELECT
                            '-' as kode,
                            form_cut_piece.no_form,
                            null as id_meja,
                            '-' as meja,
                            COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) tgl_form_cut,
                            form_cut_piece.buyer,
                            form_cut_piece.act_costing_id,
                            form_cut_piece.act_costing_ws,
                            form_cut_piece.style,
                            form_cut_piece.color,
                            form_cut_piece.panel,
                            form_cut_piece.cons_ws,
                            'PCS' unit,
                            form_cut_piece_detail_size.so_det_id,
                            CONCAT(master_sb_ws.size, CASE WHEN master_sb_ws.dest != '-' AND master_sb_ws.dest IS NOT NULL THEN CONCAT(' - ', master_sb_ws.dest) ELSE '' END) size,
                            1 as ratio,
                            COALESCE(form_cut_piece.keterangan, 'PIECE') notes,
                            SUM(form_cut_piece_detail_size.qty) marker_gelar,
                            SUM(form_cut_piece_detail_size.qty) spreading_gelar,
                            SUM(form_cut_piece_detail_size.qty) form_gelar,
                            null diff
                        FROM
                            `form_cut_piece`
                            LEFT JOIN `form_cut_piece_detail` ON `form_cut_piece_detail`.`form_id` = `form_cut_piece`.`id`
                            LEFT JOIN `form_cut_piece_detail_size` ON `form_cut_piece_detail_size`.`form_detail_id` = `form_cut_piece_detail`.`id`
                            LEFT JOIN `master_sb_ws` ON `form_cut_piece_detail_size`.`so_det_id` = `master_sb_ws`.`id_so_det`
                        WHERE
                            form_cut_piece.`status` = 'complete'
                            AND COALESCE ( form_cut_piece_detail_size.qty ) > 0
                            AND COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) >= '".$this->dateFromFilter."'
                            AND COALESCE ( DATE ( form_cut_piece.updated_at ), DATE ( form_cut_piece.created_at ), form_cut_piece.tanggal ) <= '".$this->dateToFilter."' AND form_cut_piece.tanggal >= DATE ( NOW()- INTERVAL 2 YEAR )
                            ".($this->colorFilter ? "AND form_cut_piece.color = '".$this->colorFilter."'" : "")."
                            ".($this->panelFilter ? "AND form_cut_piece.panel = '".$this->panelFilter."'" : "")."
                            ".($this->mejaFilter ? "AND '-' = '".$this->mejaFilter."'" : "")."
                            ".($this->sizeFilter ? "AND master_sb_ws.size = '".$this->sizeFilter."'" : "")."
                            ".($this->selectedOrder ? "AND form_cut_piece.act_costing_id = '".$this->selectedOrder."'" : "")."
                        GROUP BY
                            form_cut_piece.id,
                            form_cut_piece_detail_size.so_det_id
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
