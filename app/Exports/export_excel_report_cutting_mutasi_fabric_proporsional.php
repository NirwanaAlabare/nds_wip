<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class export_excel_report_cutting_mutasi_fabric_proporsional implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected $start_date, $end_date, $cbotipe;

    public function __construct($start_date, $end_date, $cbotipe)
    {
        $this->start_date = $start_date;
        $this->end_date   = $end_date;
        $this->cbotipe    = $cbotipe;
    }

    public function view(): View
    {
        $start_date = $this->start_date;
        $end_date   = $this->end_date;
        $tipe       = $this->cbotipe;
        $prev_date  = date('Y-m-d', strtotime($start_date . ' -1 day'));

        if ($tipe == 'Barcode') {
            $barcode = 'id_roll as barcode';
            $groupBy = 'id_roll, ws';
        } else {
            $barcode = 'NULL as barcode';
            $groupBy = 'id_item, ws';
        }

        $rawData = DB::select("
            SELECT
                ws,
                buyer,
                styleno,
                color,
                $barcode,
                mut.id_item,
                mi.itemdesc,

                ROUND(SUM(saldo_awal),2) AS saldo_awal,
                ROUND(SUM(qty_in),2) AS penerimaan,
                ROUND(SUM(qty_pakai),2) AS pemakaian,
                ROUND(SUM(sr),2) AS short_roll,
                ROUND(SUM(gr_p),2) AS gr_panel,
                ROUND(SUM(gr_g),2) AS gr_set,
                ROUND(SUM(qty_retur),2) AS retur,

                ROUND(
                    SUM(saldo_awal)
                    + SUM(qty_in)
                    - SUM(qty_pakai)
                    + SUM(sr)
                    - SUM(gr_p)
                    - SUM(gr_g)
                    - SUM(qty_retur)
                ,2) AS saldo_akhir,
                satuan

            FROM (
                SELECT
                    ws, id_roll, id_item,
                    0 saldo_awal,
                    SUM(qty_in) qty_in,
                    SUM(qty_pakai) qty_pakai,
                    SUM(sr) sr,
                    SUM(gr_p) gr_p,
                    SUM(gr_g) gr_g,
                    SUM(qty_retur) qty_retur,
                    SUM(saldo) saldo,
                    satuan
                FROM mut_cut_fab_saldo_tmp
                WHERE tgl_trans BETWEEN ? AND ?
                GROUP BY $groupBy

                UNION ALL

                SELECT
                    ws, id_roll, id_item,
                    SUM(saldo) saldo_awal,
                    0,0,0,0,0,0,0,
                    satuan
                FROM mut_cut_fab_saldo_tmp
                WHERE tgl_trans = ?
                GROUP BY $groupBy
            ) mut

            LEFT JOIN signalbit_erp.masteritem mi
                ON mut.id_item = mi.id_item

            LEFT JOIN (
                SELECT
                    ac.kpno,
                    supplier as buyer,
                    styleno
                FROM signalbit_erp.jo_det jd
                INNER JOIN signalbit_erp.so so ON jd.id_so = so.id
                INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                WHERE jd.cancel = 'N'
                GROUP BY jd.id_jo
            ) k ON mut.ws = k.kpno

            GROUP BY $groupBy
HAVING
    ROUND(SUM(saldo_awal), 2) <> 0
    OR ROUND(SUM(qty_in), 2) <> 0
    OR ROUND(SUM(qty_pakai), 2) <> 0
    OR ROUND(SUM(sr), 2) <> 0
    OR ROUND(SUM(gr_p), 2) <> 0
    OR ROUND(SUM(gr_g), 2) <> 0
    OR ROUND(SUM(qty_retur), 2) <> 0
    OR ROUND(
        SUM(saldo_awal)
        + SUM(qty_in)
        - SUM(qty_pakai)
        + SUM(sr)
        - SUM(gr_p)
        - SUM(gr_g)
        - SUM(qty_retur),
    2) <> 0

            ORDER BY ws ASC, color ASC
        ", [$start_date, $end_date, $prev_date]);

        return view('cutting.report.export.export_excel_report_mutasi_fabric_proporsional', [
            'rawData' => $rawData,
            'tipe'    => $tipe,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Header style (row 1)
                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);

                // Border all table
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);
            },
        ];
    }
}
