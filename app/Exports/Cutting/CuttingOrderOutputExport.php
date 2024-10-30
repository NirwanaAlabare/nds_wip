<?php

namespace App\Exports\Cutting;

use App\Models\FormCutInput;
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

class CuttingOrderOutputExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
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
        $this->loadingOrderOutput = false;

        $dateFilter = " AND COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) between '".$this->dateFrom."' and '".$this->dateTo."' ";
        $orderFilterQuery = $this->order ? " AND marker_cutting.act_costing_id = '".$this->order."' " : "";

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

        $orderSql = DB::connection('mysql_sb')->
            table('act_costing')->
            selectRaw('
                id as id_ws,
                kpno as no_ws
            ')->
            where('status', '!=', 'CANCEL')->
            where('cost_date', '>=', '2023-01-01')->
            where('type_ws', 'STD');
        if ($this->buyer) {
            $orderSql->where('id_buyer', $this->buyer);
        }
        $this->orders = $orderSql->
            orderBy('cost_date', 'desc')->
            orderBy('kpno', 'asc')->
            groupBy('kpno')->
            get();

        $orderGroupSql = FormCutInput::selectRaw("
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
                        form_cut_input.no_form,
                        form_cut_input.qty_ply,
                        form_cut_input.total_lembar,
                        form_cut_input.notes,
                        SUM(form_cut_input_detail.lembar_gelaran) detail
                    FROM
                        form_cut_input
                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                    WHERE
                        form_cut_input.`status` != 'SPREADING'
                        AND form_cut_input.waktu_mulai is not null
                        AND form_cut_input.id_marker is not null
                        ".$dateFilter."
                    GROUP BY
                        form_cut_input.no_form
                ) form_cut"
            ), "form_cut.no_form", "=", "form_cut_input.no_form")->
            leftJoin("users as meja", "meja.id", "=", "form_cut_input.no_meja")->
            leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
            leftJoin("marker_input_detail", function ($join) { $join->on('marker_input.id', '=', 'marker_input_detail.marker_id'); $join->on('marker_input_detail.ratio', '>', DB::raw('0')); })->
            whereRaw("
                form_cut_input.`status` != 'SPREADING'
                AND form_cut_input.waktu_mulai is not null
                AND form_cut_input.id_marker is not null
                AND COALESCE(form_cut.total_lembar, form_cut.detail) > 0
            ");
            if ($this->order) {
                $orderGroupSql->where("marker_input.act_costing_id", $this->order);
            }
            $orderGroupSql->
                groupByRaw("marker_input.act_costing_id, marker_input.style, marker_input.color, marker_input.panel, form_cut_input.no_meja ".($this->groupBy == 'size' ? ', marker_input_detail.so_det_id ' : ''))->
                orderBy("marker_input.act_costing_id", "asc")->
                orderBy("marker_input.style", "asc")->
                orderBy("marker_input.color", "asc")->
                orderBy("marker_input.panel", "asc")->
                orderByRaw("form_cut_input.no_meja asc, marker_input_detail.so_det_id asc, marker_input_detail.size asc");

            $orderGroup = $orderGroupSql->get();

        $orderOutputSql = collect(
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
                            INNER JOIN
                                master_sb_ws on master_sb_ws.id_so_det = marker_input_detail.so_det_id
                            INNER JOIN
                                (
                                    SELECT
                                        meja.id id_meja,
                                        meja.`name` meja,
                                        COALESCE(DATE(waktu_selesai), DATE(waktu_mulai), tgl_form_cut) tgl_form_cut,
                                        form_cut_input.id_marker,
                                        form_cut_input.no_form,
                                        form_cut_input.qty_ply,
                                        form_cut_input.total_lembar,
                                        form_cut_input.notes,
                                        SUM(form_cut_input_detail.lembar_gelaran) detail
                                    FROM
                                        form_cut_input
                                        LEFT JOIN users meja ON meja.id = form_cut_input.no_meja
                                        INNER JOIN form_cut_input_detail ON form_cut_input_detail.no_form_cut_input = form_cut_input.no_form
                                    WHERE
                                        form_cut_input.`status` != 'SPREADING'
                                        AND form_cut_input.waktu_mulai is not null
                                        ".$dateFilter."
                                    GROUP BY
                                        form_cut_input.no_form
                                ) form_cut on form_cut.id_marker = marker_input.kode
                            LEFT JOIN
                                modify_size_qty ON modify_size_qty.no_form = form_cut.no_form AND modify_size_qty.so_det_id = marker_input_detail.so_det_id
                            where
                                (marker_input.cancel IS NULL OR marker_input.cancel != 'Y')
                                AND marker_input_detail.ratio > 0
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
            $orderOutputs = $orderOutputSql;

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

        return view('cutting.export.cutting-order-output-export', [
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