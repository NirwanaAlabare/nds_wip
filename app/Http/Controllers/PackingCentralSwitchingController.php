<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPackingIn;
use App\Models\PackingCentralSwitching;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class PackingCentralSwitchingController extends Controller
{
    public function index(Request $request)
    {
        // $sourceData = DB::select("
        //     WITH a AS (              
        //         SELECT
        //             a.id_ppic_master_so,
        //             a.id_so_det          AS so_det_id,
        //             SUM(a.qty)           AS qty_trf_gmt
        //         FROM laravel_nds.packing_trf_garment a
        //         INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
        //         WHERE YEAR(p.tgl_shipment) >= 2026
        //         GROUP BY a.id_ppic_master_so, a.id_so_det
        //     ),
        //     p AS (
        //         SELECT id_ppic, id_so_det, COUNT(*) AS qty_scan
        //         FROM packing_packing_out_scan
        //         GROUP BY id_ppic, id_so_det
        //     ),
        //     s AS(
        //         SELECT asal_ppic_master_so_id, asal_so_det_id, SUM(qty_switch) AS qty_switch
        //         FROM packing_central_switching
        //         GROUP BY asal_ppic_master_so_id, asal_so_det_id
        //     ),
        //     combined AS (
        //         SELECT id_ppic_master_so, so_det_id, qty_trf_gmt AS qty, 0 AS qty_scan, 0 AS qty_switch FROM a
        //         UNION ALL
        //         SELECT id_ppic as id_ppic_master_so, id_so_det as so_det_id, 0 as qty, qty_scan, 0 as qty_switch FROM p
        //         UNION ALL
        //         SELECT asal_ppic_master_so_id as id_ppic_master_so, asal_so_det_id as so_det_id, 0 as qty, 0 as qty_scan, qty_switch FROM s
        //     )

        //     SELECT
        //         combined.id_ppic_master_so,
        //         packing_packing_in.id AS packing_packing_in_id,
        //         packing_packing_in.po,
        //         master_sb_ws.ws,
        //         master_sb_ws.color,
        //         master_sb_ws.size,
        //         master_sb_ws.dest,
        //         combined.so_det_id,
        //         SUM(combined.qty)      AS qty_trf_gmt,
        //         SUM(combined.qty_scan) AS qty_scan,
        //         SUM(combined.qty_switch) AS qty_switch,
        //         SUM(combined.qty) - SUM(combined.qty_scan) - SUM(combined.qty_switch) AS qty_sisa
        //     FROM combined
        //     LEFT JOIN (
        //         SELECT
        //             id_ppic_master_so,
        //             id_so_det,
        //             MIN(id) AS id,
        //             MAX(po) AS po
        //         FROM packing_packing_in
        //         GROUP BY
        //             id_ppic_master_so,
        //             id_so_det
        //     ) packing_packing_in
        //         ON packing_packing_in.id_ppic_master_so = combined.id_ppic_master_so
        //     AND packing_packing_in.id_so_det = combined.so_det_id
        //     LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_packing_in.id_so_det 
        //     GROUP BY id_ppic_master_so, so_det_id
        //     HAVING qty_sisa > 0
        // ");

        // $tujuanData = DB::select("
        //     SELECT
        //         ppic_master_so.id,
        //         ppic_master_so.po,
        //         ppic_master_so.barcode,
        //         ppic_master_so.dest,
        //         master_sb_ws.ws,
        //         master_sb_ws.color,
        //         master_sb_ws.size,
        //         master_sb_ws.dest,
        //         ppic_master_so.qty_po + COALESCE(sw.qty_switch, 0) AS qty_po,
        //         ppic_master_so.id_so_det
        //     FROM ppic_master_so
        //     LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det 
        //     LEFT JOIN (
        //         SELECT
        //             tujuan_ppic_master_so_id,
        //             tujuan_so_det_id,
        //             SUM(qty_switch) AS qty_switch
        //         FROM packing_central_switching
        //         GROUP BY
        //             tujuan_ppic_master_so_id,
        //             tujuan_so_det_id
        //     ) sw
        //         ON sw.tujuan_ppic_master_so_id = ppic_master_so.id
        //     AND sw.tujuan_so_det_id = ppic_master_so.id_so_det
        //     LIMIT 100
        // ");

        return view(
            'packing.packing_central_switching',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-in",
                "subPage" => "packing_central_switching",
                // "sourceData" => $sourceData,
                // "tujuanData" => $tujuanData,
            ]
        );
    }

    public function preview(Request $request)
    {
        $id = $request->id_ppic_master_so;

        $data = DB::select("
            WITH a AS (
                SELECT
                    a.id_ppic_master_so,
                    a.id_so_det AS so_det_id,
                    SUM(a.qty) AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p
                    ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026
                    AND a.id_ppic_master_so = ?
                GROUP BY a.id_ppic_master_so, a.id_so_det
            ),
            p AS (
                SELECT
                    id_ppic,
                    id_so_det,
                    COUNT(*) AS qty_scan
                FROM packing_packing_out_scan
                WHERE id_ppic = ?
                GROUP BY id_ppic, id_so_det
            ),
            s AS(
                SELECT asal_ppic_master_so_id, asal_so_det_id, SUM(qty_switch) AS qty_switch
                FROM packing_central_switching
                GROUP BY asal_ppic_master_so_id, asal_so_det_id
            ),
            combined AS (
                SELECT id_ppic_master_so, so_det_id, qty_trf_gmt AS qty, 0 AS qty_scan, 0 AS qty_switch FROM a
                UNION ALL
                SELECT id_ppic as id_ppic_master_so, id_so_det as so_det_id, 0 as qty, qty_scan, 0 as qty_switch FROM p
                UNION ALL
                SELECT asal_ppic_master_so_id as id_ppic_master_so, asal_so_det_id as so_det_id, 0 as qty, 0 as qty_scan, qty_switch FROM s
            )

            SELECT
                combined.id_ppic_master_so,
                combined.so_det_id,
                packing_packing_in.line,
                packing_packing_in.barcode,
                packing_packing_in.po,
                master_sb_ws.ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                SUM(combined.qty) AS qty_trf_gmt,
                SUM(combined.qty_scan) AS qty_scan,
                SUM(combined.qty_switch) AS qty_switch,
                SUM(combined.qty) - SUM(combined.qty_scan) - SUM(combined.qty_switch) AS qty_sisa
            FROM combined
            LEFT JOIN (
                SELECT
                    id_ppic_master_so,
                    id_so_det,
                    line,
                    barcode,
                    MIN(id) AS id,
                    MAX(po) AS po
                FROM packing_packing_in
                GROUP BY
                    id_ppic_master_so,
                    id_so_det
            ) packing_packing_in
                ON packing_packing_in.id_ppic_master_so = combined.id_ppic_master_so
            AND packing_packing_in.id_so_det = combined.so_det_id
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_packing_in.id_so_det 
            GROUP BY
                combined.id_ppic_master_so,
                combined.so_det_id
            HAVING qty_sisa > 0
        ", [$id, $id]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $no_trans = DB::selectOne("
                SELECT
                    CONCAT('PCK/SWT/', DATE_FORMAT(CURRENT_DATE(), '%Y')) AS Mattype,

                    IF(
                        MAX(no_trans) IS NULL,
                        '00001',
                        LPAD(MAX(RIGHT(no_trans, 5)) + 1, 5, 0)
                    ) AS nomor,

                    CONCAT(
                        'PCK/SWT/',
                        DATE_FORMAT(CURRENT_DATE(), '%m'),
                        DATE_FORMAT(CURRENT_DATE(), '%y'),
                        '/',
                        IF(
                            MAX(no_trans) IS NULL,
                            '00001',
                            LPAD(MAX(RIGHT(no_trans, 5)) + 1, 5, 0)
                        )
                    ) AS kode

                FROM packing_central_switching
                WHERE
                    MONTH(created_at) = MONTH(CURRENT_DATE())
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND LEFT(no_trans, 3) = 'PCK'
            ");

            PackingCentralSwitching::create([
                'no_trans'                 => $no_trans->kode,
                'packing_packing_in_id'    => $request->packing_packing_in_id,
                'asal_ppic_master_so_id'   => $request->asal_ppic_master_so_id,
                'asal_so_det_id'           => $request->asal_so_det_id,
                'tujuan_ppic_master_so_id' => $request->tujuan_ppic_master_so_id,
                'tujuan_so_det_id'         => $request->tujuan_so_det_id,
                'qty_switch'               => $request->qty_switch,
                "created_by"               => auth()->user()->id,
                "created_by_username"      => auth()->user()->username,
                "created_at"               => date('Y-m-d H:i:s'),
            ]);

            DB::table('packing_packing_in')->insert([
                'id_trf_garment'      => null,
                'no_trans'            => $no_trans->kode,
                'tgl_penerimaan'      => date('Y-m-d'),
                'id_ppic_master_so'   => $request->tujuan_ppic_master_so_id,
                'id_so_det'           => $request->tujuan_so_det_id,
                'qty'                 => $request->qty_switch,
                'line'                => null,
                'po'                  => $request->tujuan_po,
                'barcode'             => $request->tujuan_barcode,
                'dest'                => $request->tujuan_dest,
                'sumber'              => 'Switching',
                'created_at'          => now(),
                'updated_at'          => now(),
                'created_by'          => auth()->user()->username,
            ]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Data Packing Central Switching berhasil disimpan.",
                "additional" => [],
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return array(
                "status" => 500,
                "message" => "Terjadi kesalahan saat menyimpan data: " . $e->getMessage(),
                "additional" => [],
            );
        }
    }

    public function getData(Request $request)
    {
        $data = DB::select("
            SELECT
                packing_central_switching.id,
                packing_central_switching.no_trans,
                DATE_FORMAT(packing_central_switching.created_at, '%d-%b-%Y') AS tgl_trans,
                packing_packing_in.no_trans as no_trans_packing_in,
                packing_packing_in.line,
                ppic_master_so.po as tujuan,
                packing_packing_in.barcode,
                packing_packing_in.po,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                qty_switch,
                packing_central_switching.created_by_username,
                packing_central_switching.created_at
            FROM packing_central_switching
            LEFT JOIN packing_packing_in
                ON packing_packing_in.id = packing_central_switching.packing_packing_in_id
            LEFT JOIN master_sb_ws
                ON master_sb_ws.id_so_det = packing_packing_in.id_so_det
            LEFT JOIN ppic_master_so
                ON ppic_master_so.id = packing_central_switching.tujuan_ppic_master_so_id
            WHERE DATE(packing_central_switching.created_at)
                BETWEEN ? AND ?
        ", [
            $request->dateFrom,
            $request->dateTo
        ]);

        return DataTables::of($data)->toJson();
    }

    public function getDataAsalPo(Request $request)
    {
        $search = $request->search;

        $data = DB::select("
            WITH a AS (              
                SELECT
                    a.id_ppic_master_so,
                    a.id_so_det          AS so_det_id,
                    SUM(a.qty)           AS qty_trf_gmt
                FROM laravel_nds.packing_trf_garment a
                INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
                WHERE YEAR(p.tgl_shipment) >= 2026
                GROUP BY a.id_ppic_master_so, a.id_so_det
            ),
            p AS (
                SELECT id_ppic, id_so_det, COUNT(*) AS qty_scan
                FROM packing_packing_out_scan
                GROUP BY id_ppic, id_so_det
            ),
            s AS(
                SELECT asal_ppic_master_so_id, asal_so_det_id, SUM(qty_switch) AS qty_switch
                FROM packing_central_switching
                GROUP BY asal_ppic_master_so_id, asal_so_det_id
            ),
            combined AS (
                SELECT id_ppic_master_so, so_det_id, qty_trf_gmt AS qty, 0 AS qty_scan, 0 AS qty_switch FROM a
                UNION ALL
                SELECT id_ppic as id_ppic_master_so, id_so_det as so_det_id, 0 as qty, qty_scan, 0 as qty_switch FROM p
                UNION ALL
                SELECT asal_ppic_master_so_id as id_ppic_master_so, asal_so_det_id as so_det_id, 0 as qty, 0 as qty_scan, qty_switch FROM s
            )

            SELECT
                combined.id_ppic_master_so,
                packing_packing_in.id AS packing_packing_in_id,
                packing_packing_in.po,
                master_sb_ws.ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                combined.so_det_id,
                SUM(combined.qty)      AS qty_trf_gmt,
                SUM(combined.qty_scan) AS qty_scan,
                SUM(combined.qty_switch) AS qty_switch,
                SUM(combined.qty) - SUM(combined.qty_scan) - SUM(combined.qty_switch) AS qty_sisa
            FROM combined
            LEFT JOIN (
                SELECT
                    id_ppic_master_so,
                    id_so_det,
                    MIN(id) AS id,
                    MAX(po) AS po
                FROM packing_packing_in
                GROUP BY
                    id_ppic_master_so,
                    id_so_det
            ) packing_packing_in
                ON packing_packing_in.id_ppic_master_so = combined.id_ppic_master_so
            AND packing_packing_in.id_so_det = combined.so_det_id
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_packing_in.id_so_det 
            GROUP BY id_ppic_master_so, so_det_id
            HAVING qty_sisa > 0 AND packing_packing_in.po LIKE ?
        ", ["%{$search}%"]);

        return response()->json($data);
    }

    public function getDataTujuanPo(Request $request)
    {
        $search = $request->search;

        $data = DB::select("
            SELECT
                ppic_master_so.id,
                ppic_master_so.po,
                ppic_master_so.barcode,
                ppic_master_so.dest,
                master_sb_ws.ws,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                ppic_master_so.qty_po + COALESCE(sw.qty_switch, 0) AS qty_po,
                ppic_master_so.id_so_det
            FROM ppic_master_so
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = ppic_master_so.id_so_det 
            LEFT JOIN (
                SELECT
                    tujuan_ppic_master_so_id,
                    tujuan_so_det_id,
                    SUM(qty_switch) AS qty_switch
                FROM packing_central_switching
                GROUP BY
                    tujuan_ppic_master_so_id,
                    tujuan_so_det_id
            ) sw
                ON sw.tujuan_ppic_master_so_id = ppic_master_so.id
            AND sw.tujuan_so_det_id = ppic_master_so.id_so_det
            WHERE ppic_master_so.po LIKE ?
        ", ["%{$search}%"]);

        return response()->json($data);
    }

    public function export_excel_packing_central_switching(Request $request)
    {
        $tgl_awal = $request->from;
        $tgl_akhir = $request->to;

        $data = DB::select("
            SELECT
                packing_central_switching.id,
                packing_central_switching.no_trans,
                DATE_FORMAT(packing_central_switching.created_at, '%d-%b-%Y') AS tgl_trans,
                packing_packing_in.no_trans as no_trans_packing_in,
                packing_packing_in.line,
                ppic_master_so.po as tujuan,
                packing_packing_in.barcode,
                packing_packing_in.po,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                qty_switch,
                packing_central_switching.created_by_username,
                packing_central_switching.created_at
            FROM packing_central_switching
            LEFT JOIN packing_packing_in
                ON packing_packing_in.id = packing_central_switching.packing_packing_in_id
            LEFT JOIN master_sb_ws
                ON master_sb_ws.id_so_det = packing_packing_in.id_so_det
            LEFT JOIN ppic_master_so
                ON ppic_master_so.id = packing_central_switching.tujuan_ppic_master_so_id
            WHERE DATE(packing_central_switching.created_at)
                BETWEEN ? AND ?
        ", [
            $tgl_awal,
            $tgl_akhir
        ]);

        $fileName = 'packing-central-switching';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Laporan Packing Central Switching'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tgl_awal . ' s/d ' . $tgl_akhir],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $header = [
            'No Trans',
            'Tgl Trans',
            'No Trans Packing In',
            'Line Asal',
            'PO Asal',
            'Tujuan',
            'Barcode',
            'WS',
            'Style',
            'Color',
            'Size',
            'Dest',
            'Qty',
            'User',
            'Created_at',
        ];

        $sheet->writeRow(
            $header,
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
            ]
        );

        foreach ($data as $row) {

            $rows = [
                $row->no_trans ?? '',
                $row->tgl_trans ?? '',
                $row->no_trans_packing_in ?? '',
                $row->line ?? '',
                $row->po ?? '',
                $row->tujuan ?? '',
                $row->barcode ?? '',
                $row->ws ?? '',
                $row->styleno ?? '',
                $row->color ?? '',
                $row->size ?? '',
                $row->dest ?? '',
                (float) ($row->qty_switch ?? 0),
                $row->created_by_username ?? '',
                $row->created_at ?? '',
            ];

            $sheet->writeRow(
                $rows,
                [
                    'border' => 'thin',
                ]
            );
        }

        foreach (range('A', 'O') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}