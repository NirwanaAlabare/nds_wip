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

    public function __construct($dateFrom, $dateTo, $lineId, $line)
    {
        $this->dateFrom = $dateFrom ? $dateFrom : date('Y-m-d');
        $this->dateTo = $dateTo ? $dateTo : date('Y-m-d');
        $this->lineId = $lineId ? $lineId : null;
        $this->line = $line ? $line : null;
    }


    public function view(): View
    {
        $lineIdFilter = $this->lineId ? "AND line_id = '".$this->lineId."'" : null;
        $lineFilter = $this->line ? "AND line = '".$this->line."'" : null;

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
                    so_det_id,
                    user_sb_wip.line_id,
                    COUNT(output_rfts.id) total_output
                FROM
                    output_rfts
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rfts.created_by
                WHERE
                    output_rfts.status = 'NORMAL' AND
                    output_rfts.so_det_id in (".$soDetList.")
                    ".$lineIdFilter."
                GROUP BY
                    user_sb_wip.line_id,
                    so_det_id
            "));

            $dataDefect = collect(DB::connection("mysql_sb")->select("
                SELECT
                    so_det_id,
                    user_sb_wip.line_id,
                    COUNT(output_defects.id) total_output
                FROM
                    output_defects
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_defects.created_by
                WHERE
                    output_defects.defect_status = 'defect' and
                    output_defects.so_det_id in (".$soDetList.")
                    ".$lineIdFilter."
                GROUP BY
                    user_sb_wip.line_id,
                    so_det_id
            "));

            $dataReject = collect(DB::connection("mysql_sb")->select("
                SELECT
                    so_det_id,
                    user_sb_wip.line_id,
                    COUNT(output_rejects.id) total_output
                FROM
                    output_rejects
                    LEFT JOIN user_sb_wip ON user_sb_wip.id = output_rejects.created_by
                WHERE
                    output_rejects.so_det_id in (".$soDetList.")
                    ".$lineIdFilter."
                GROUP BY
                    user_sb_wip.line_id,
                    so_det_id
            "));

            $dataOutputPacking = collect(DB::connection("mysql_sb")->select("
                SELECT
                    so_det_id,
                    userpassword.line_id,
                    COUNT(output_rfts_packing.id) total_output
                FROM
                    output_rfts_packing
                    LEFT JOIN userpassword ON userpassword.username = output_rfts_packing.created_by
                WHERE
                    output_rfts_packing.so_det_id in (".$soDetList.")
                    $lineIdFilter
                GROUP BY
                    userpassword.line_id,
                    so_det_id
            "));

            $data = collect(DB::select("
                SELECT
                    ppic_master.tanggal,
                    ppic_master.ws,
                    ppic_master.styleno,
                    ppic_master.color,
                    ppic_master.size,
                    ppic_master.dest,
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
                        id_so_det
                ) ppic_master
                LEFT JOIN
                (
                    SELECT
                        MAX(ll.tanggal_loading) tanggal_loading,
                        ll.line_id,
                        ll.nama_line,
                        si.so_det_id,
                        si.size,
                        SUM(
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
                        ll.nama_line,
                        si.so_det_id
                    HAVING
                        loading_qty > 0
                ) loading_stock on loading_stock.so_det_id = ppic_master.id_so_det
                LEFT JOIN (
                    SELECT
                        id_so_det,
                        sum(qty) total_transfer_garment
                    FROM
                        packing_trf_garment
                    WHERE
                        id_so_det in (".$soDetList.")
                        ".$lineFilter."
                    GROUP BY
                        packing_trf_garment.id_so_det
                ) transfer_garment ON transfer_garment.id_so_det = ppic_master.id_so_det
                GROUP BY
                    ppic_master.id_so_det,
                    loading_stock.line_id
                HAVING
                    loading_stock.line_id is not null
                    ".$lineIdFilter."
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
            'A3:P' . ($event->getConcernable()->rowCount+3),
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
