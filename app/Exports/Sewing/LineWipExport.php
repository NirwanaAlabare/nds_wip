<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class LineWipExport implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;
    protected $lineId;
    protected $line;

    public function __construct($dateFrom, $dateTo, $lineId, $line, $lineNameFilter, $tanggalFilter, $wsFilter, $styleFilter, $colorFilter, $sizeFilter)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
        $this->lineId = $lineId ? $lineId : null;
        $this->line = $line ? $line : null;
        $this->lineNameFilter = $lineNameFilter ? $lineNameFilter : null;
        $this->tanggalFilter = $tanggalFilter ? $tanggalFilter : null;
        $this->wsFilter = $wsFilter ? $wsFilter : null;
        $this->styleFilter = $styleFilter ? $styleFilter : null;
        $this->colorFilter = $colorFilter ? $colorFilter : null;
        $this->sizeFilter = $sizeFilter ? $sizeFilter : null;
    }


    public function view(): View
    {
        $tanggal_awal = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $tanggal_akhir = $this->dateTo ? $this->dateTo : date('Y-m-d');
        $lineIdFilter = $this->lineId ? "AND line_id = '".$this->lineId."'" : null;
        $lineIdFilter1 = $this->lineId ? "AND userpassword.line_id = '".$this->lineId."'" : null;
        $lineFilter = $this->line ? "AND line = '".$this->line."'" : null;

        $lineNameFilter = "";
        $tanggalFilter = "";
        $lineNameFilter1 = "";
        $lineNameFilter2 = "";
        $lineNameFilter3 = "";
        $tanggalFilter = "";
        $wsFilter = "";
        $styleFilter = "";
        $colorFilter = "";
        $sizeFilter = "";

        if ($this->lineNameFilter) {
            $lineNameFilter1 = "AND userpassword.username LIKE '%".($this->lineNameFilter)."%'";
            $lineNameFilter2 = "AND nama_line LIKE '%".($this->lineNameFilter)."%'";
            $lineNameFilter3 = "AND line LIKE '%".($this->lineNameFilter)."%'";
        }

        if ($this->tanggalFilter) {
            $tanggalFilter = "AND MAX(tgl_shipment) LIKE '%".($this->tanggalFilter)."%'";
        }

        if ($this->wsFilter) {
            $wsFilter = "AND master_sb_ws.ws LIKE '%".($this->wsFilter)."%'";
        }

        if ($this->styleFilter) {
            $styleFilter = "AND master_sb_ws.style LIKE '%".($this->styleFilter)."%'";
        }

        if ($this->colorFilter) {
            $colorFilter = "AND master_sb_ws.color LIKE '%".($this->colorFilter)."%'";
        }

        if ($this->sizeFilter) {
            $sizeFilter = "AND master_sb_ws.size LIKE '%".($this->sizeFilter)."%'";
        }

        $ppicList = collect(
            DB::select("
                SELECT
                    MAX(tgl_shipment) tanggal,
                    ppic_master_so.id_so_det,
                    master_sb_ws.ws,
                    master_sb_ws.color,
                    master_sb_ws.size
                FROM
                    ppic_master_so
                    LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                WHERE
                    ppic_master_so.id_so_det is not null
                    ".$wsFilter."
                    ".$styleFilter."
                    ".$colorFilter."
                    ".$sizeFilter."
                GROUP BY
                    id_so_det
                HAVING
                    MAX(tgl_shipment) between '".$this->dateFrom."' AND '".$this->dateTo."'
            ")
        );

        if ($ppicList->count() > 0) {
            $soDetList = implode(',', $ppicList->pluck("id_so_det")->toArray());

            $dataOutput = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_rfts.id) total_output
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det on so_det.id = output_rfts.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_rfts.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $dataDefect = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_defects.id) total_output
                FROM
                    output_defects
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det on so_det.id = output_defects.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_defects.defect_status = 'defect' and
                    output_defects.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $dataReject = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_rejects.id) total_output
                FROM
                    output_rejects
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                    LEFT JOIN userpassword ON userpassword.line_id = user_sb_wip.line_id
                    LEFT JOIN so_det on so_det.id = output_rejects.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_rejects.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $dataOutputPacking = collect(DB::connection("mysql_sb")->select("
                SELECT
                    userpassword.line_id,
                    so_det_id,
                    act_costing.id as id_ws,
                    so_det.color,
                    so_det.size,
                    COUNT(output_rfts_packing.id) total_output
                FROM
                    output_rfts_packing
                    LEFT JOIN userpassword ON userpassword.username = output_rfts_packing.created_by
                    LEFT JOIN so_det on so_det.id = output_rfts_packing.so_det_id
                    LEFT JOIN so on so.id = so_det.id_so
                    LEFT JOIN act_costing on act_costing.id = so.id_cost
                WHERE
                    output_rfts_packing.so_det_id in (".$soDetList.")
                    ".$lineIdFilter1."
                    ".$lineNameFilter1."
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $data = collect(DB::select("
                 SELECT
                    ppic_master.tanggal,
                    ppic_master.id_ws,
                    ppic_master.ws,
                    ppic_master.styleno,
                    ppic_master.color,
                    ppic_master.size,
                    ppic_master.id_so_det,
                    loading_stock.line_id,
                    loading_stock.nama_line,
                    loading_stock.loading_qty,
                    transfer_garment.total_transfer_garment
                FROM
                (
                    SELECT
                        MAX(tgl_shipment) tanggal,
                        ppic_master_so.id_so_det,
                        master_sb_ws.id_act_cost as id_ws,
                        master_sb_ws.ws,
                        master_sb_ws.styleno,
                        master_sb_ws.color,
                        master_sb_ws.size,
                        master_sb_ws.dest
                    FROM
                        ppic_master_so
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det
                    WHERE
                        ppic_master_so.id_so_det in (".$soDetList.")
                    GROUP BY
                        master_sb_ws.id_act_cost,
                        master_sb_ws.color,
                        master_sb_ws.size
                ) ppic_master
                LEFT JOIN
                (
                    SELECT
                        MAX(tanggal_loading) tanggal_loading,
                        line_id,
                        nama_line,
                        so_det_id,
                        act_costing_ws,
                        color,
                        size,
                        SUM(loading_qty) loading_qty
                    FROM (
                        SELECT
                            MAX(ll.tanggal_loading) tanggal_loading,
                            ll.line_id,
                            ll.nama_line,
                            si.act_costing_ws,
                            si.so_det_id,
                            si.color,
                            si.size,
                            (
                                COALESCE(di.qty_awal, si.qty_ply_mod, si.qty_ply, 0)
                                - COALESCE(di.qty_reject, 0)
                                + COALESCE(di.qty_replace, 0)
                                - COALESCE(sii.qty_reject, 0)
                                + COALESCE(sii.qty_replace, 0)
                                - COALESCE(sii_h.qty_reject, 0)
                                + COALESCE(sii_h.qty_replace, 0)
                            ) AS loading_qty
                        FROM
                            loading_line ll
                            INNER JOIN stocker_input si ON si.id = ll.stocker_id
                            LEFT JOIN dc_in_input di ON di.id_qr_stocker = si.id_qr_stocker
                            LEFT JOIN secondary_in_input sii ON sii.id_qr_stocker = si.id_qr_stocker
                            LEFT JOIN secondary_inhouse_input sii_h ON sii_h.id_qr_stocker = si.id_qr_stocker
                        where
                            si.so_det_id in (".$soDetList.")
                            ".$lineIdFilter."
                        GROUP BY
                            ll.line_id,
                            si.form_cut_id,
                            si.so_det_id,
                            si.group_stocker,
                            si.ratio
                        HAVING
                            loading_qty > 0
                    ) ll
                    GROUP BY
                        line_id,
                        act_costing_ws,
                        color,
                        size
                ) loading_stock on loading_stock.act_costing_ws = ppic_master.ws and loading_stock.color = ppic_master.color and loading_stock.size = ppic_master.size
                LEFT JOIN (
                    SELECT
                        packing_trf_garment.line,
                        master_sb_ws.id_act_cost as id_ws,
                        master_sb_ws.ws,
                        master_sb_ws.color,
                        master_sb_ws.size,
                        packing_trf_garment.id_so_det,
                        sum(packing_trf_garment.qty) total_transfer_garment
                    FROM
                        packing_trf_garment
                        LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_trf_garment.id_so_det
                    WHERE
                        packing_trf_garment.id_so_det in (".$soDetList.")
                        ".$lineFilter."
                        ".$lineNameFilter3."
                    GROUP BY
                        packing_trf_garment.line,
                        master_sb_ws.id_act_cost,
                        master_sb_ws.color,
                        master_sb_ws.size
                ) transfer_garment ON transfer_garment.line = loading_stock.nama_line and transfer_garment.id_ws = ppic_master.id_ws and transfer_garment.color = ppic_master.color and transfer_garment.size = ppic_master.size
                WHERE
                    ppic_master.id_so_det is not null
                    ".$lineIdFilter."
                    ".$lineNameFilter2."
                    ".$lineNameFilter3."
                GROUP BY
                    ppic_master.id_ws,
                    ppic_master.color,
                    ppic_master.size,
                    loading_stock.line_id
                HAVING
                    ppic_master.id_so_det is not null
                    ".$lineIdFilter."
                    ".$lineNameFilter2."
                    ".$lineNameFilter3."
                ORDER BY
                    ppic_master.id_so_det
            "));
        } else {
            $data = [];
            $dataReject = [];
            $dataDefect = [];
            $dataOutput = [];
            $dataOutputPacking = [];
        }

        $this->rowCount = count($data);

        return view('sewing.export.line-wip-export', [
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'line' => $this->line,
            'data' => $data,
            'dataReject' => $dataReject,
            'dataDefect' => $dataDefect,
            'dataOutput' => $dataOutput,
            'dataOutputPacking' => $dataOutputPacking,
        ]);
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
            'A3:O' . ($event->getConcernable()->rowCount+4),
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

    // public function columnFormats(): array
    // {
    //     return [
    //         'E' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }
}
