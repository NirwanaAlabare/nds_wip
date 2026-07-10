<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class ReportPackingLineReturnController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $tipe = $request->tipe;
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;
            $buyer = $request->buyer;

            if ($tipe == 'Detail') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            DATE_FORMAT(rfts.created_at, '%d-%m-%Y') AS tgl_return,
                            rfts.kode_numbering,
                            rfts.po,
                            mb.buyer,
                            rfts.kpno as ws,
                            rfts.style,
                            rfts.color,
                            rfts.size,
                            rfts.packing_line,
                            rfts.line_qc_finishing,
                            rfts.created_at
                        FROM
                            signalbit_erp.output_rfts_packing_po_return rfts
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                supplier as buyer
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on rfts.so_det_id = mb.id_so_det
                        WHERE
                            DATE(rfts.created_at) >= '{$tglAwal}'
                            AND DATE(rfts.created_at) <= '{$tglAkhir}'
                        ORDER BY rfts.id DESC
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else if ($tipe == 'Summary') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            mb.buyer,
                            rfts.kpno as ws,
                            rfts.style,
                            rfts.color,
                            rfts.size,
                            COUNT(*) AS qty_return
                        FROM
                            signalbit_erp.output_rfts_packing_po_return rfts
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                supplier as buyer
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on rfts.so_det_id = mb.id_so_det
                        WHERE
                            DATE(rfts.created_at) >= '{$tglAwal}'
                            AND DATE(rfts.created_at) <= '{$tglAkhir}'
                        GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                        ORDER BY mb.buyer ASC
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else {
                $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
            }

            return DataTables::queryBuilder($data)->make(true);
        }

        $buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view("packing.report-packing-line-return", [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-report",
            "subPage" => "report-packing-line-return",
            'containerFluid' => true,
            "buyer" => $buyer
        ]);
    }

    public function export(Request $request)
    {
        $tipe = $request->tipe;
        $tglAwal = $request->dateFrom;
        $tglAkhir = $request->dateTo;
        $buyer = $request->buyer;

        if ($tipe == 'Detail') {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        DATE_FORMAT(rfts.created_at, '%d-%m-%Y') AS tgl_return,
                        rfts.kode_numbering,
                        rfts.po,
                        mb.buyer,
                        rfts.kpno as ws,
                        rfts.style,
                        rfts.color,
                        rfts.size,
                        rfts.packing_line,
                        rfts.line_qc_finishing,
                        rfts.created_at
                    FROM
                        signalbit_erp.output_rfts_packing_po_return rfts
                    LEFT JOIN (
                        SELECT
                            sd.id as id_so_det,
                            supplier as buyer
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on rfts.so_det_id = mb.id_so_det
                    WHERE
                        DATE(rfts.created_at) >= '{$tglAwal}'
                        AND DATE(rfts.created_at) <= '{$tglAkhir}'
                    ORDER BY rfts.id DESC
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else if ($tipe == 'Summary') {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        mb.buyer,
                        rfts.kpno as ws,
                        rfts.style,
                        rfts.color,
                        rfts.size,
                        COUNT(*) AS qty_return
                    FROM
                        signalbit_erp.output_rfts_packing_po_return rfts
                    LEFT JOIN (
                        SELECT
                            sd.id as id_so_det,
                            supplier as buyer
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on rfts.so_det_id = mb.id_so_det
                    WHERE
                        DATE(rfts.created_at) >= '{$tglAwal}'
                        AND DATE(rfts.created_at) <= '{$tglAkhir}'
                    GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                    ORDER BY mb.buyer ASC
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else {
            $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0')->get();
        }

        $fileName = 'report-packing-line-return';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Report Packing Line Return'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tglAwal . ' s/d ' . $tglAkhir],
            [
                'font-size' => 12,
            ]
        );
        $sheet->writeRow(
            ['Tipe : ' . $tipe],
            [
                'font-size' => 12,
            ]
        );
        $sheet->writeRow(
            ['Buyer : ' . $buyer],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        if ($tipe == 'Detail') {
            $header = [
                'Tgl Return',
                'Nomor QR',
                'PO',
                'Buyer',
                'Worksheet',
                'Style',
                'Color',
                'Size',
                'Packing Line',
                'Kirim QC Finishing',
                'Created At',
            ];
        } elseif ($tipe == 'Summary') {
            $header = [
                'Buyer',
                'Worksheet',
                'Style',
                'Color',
                'Size',
                'Qty Return',
            ];
        } else {
            $header = [];
        }

        $sheet->writeRow($header, [
            'font-style' => 'bold',
            'border' => 'thin',
        ]);

        foreach ($data as $row) {
            if ($tipe == 'Detail') {
                $rows = [
                    $row->tgl_return,
                    $row->kode_numbering ?? '',
                    $row->po,
                    $row->buyer,
                    $row->ws,
                    $row->style,
                    $row->color,
                    $row->size,
                    $row->packing_line,
                    $row->line_qc_finishing,
                    $row->created_at,
                ];

                foreach (range('A', 'K') as $col) {
                    $sheet->setColWidth($col, 20);
                }
            } elseif ($tipe == 'Summary') {
                $rows = [
                    $row->buyer,
                    $row->ws,
                    $row->style,
                    $row->color,
                    $row->size,
                    (float) $row->qty_return,
                ];

                foreach (range('A', 'F') as $col) {
                    $sheet->setColWidth($col, 20);
                }
            }else {
                $rows = [];
            }

            $sheet->writeRow($rows, [
                'border' => 'thin',
            ]);
        }

        return $excel->download();
    }
}
