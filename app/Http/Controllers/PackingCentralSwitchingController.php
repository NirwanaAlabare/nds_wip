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
    // tanggal snapshot saldo_wip_packing_central (saldo akhir hari tgl ini)
    public const WIP_TGL_SALDO = '2026-11-01';

    public function index(Request $request)
    {
        
        return view(
            'packing.packing_central_switching',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-in",
                "subPage" => "packing_central_switching",
            ]
        );
    }
    public function preview(Request $request)
    {
        // Stok asal mengikuti Detail Transaksi (termasuk baris TANPA PO)
        $id = $request->id_ppic_master_so ?: null;
        $po = $request->po ?: 'TANPA PO';

        $item = DB::table('master_sb_ws')->where('id_so_det', $request->so_det_id)->first(['ws', 'styleno', 'color', 'size']);
        if (!$item) {
            return response()->json([]);
        }

        $data = [];
        foreach ($this->buildQueryDetailTransaksi(date('Y-m-d'), date('Y-m-d'), 'Tersedia', $po) as $row) {
            if (
                (int) $row->id_ppic_master_so === (int) $id
                && $row->ws == $item->ws
                && $row->styleno == $item->styleno
                && $row->color == $item->color
                && $row->size == $item->size
            ) {
                $data[] = $this->lengkapiStokAsal($row);
            }
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        // Check Closing
        // $dataCheckClosingAsal = DB::connection('mysql_sb')->table('output_rfts_packing_po')
        //     ->where('po_id', $request->asal_ppic_master_so_id)
        //     ->where('so_det_id', $request->asal_so_det_id)
        //     ->orderBy('updated_at', 'desc')
        //     ->first();

        // $dataCheckClosingTujuan = DB::connection('mysql_sb')->table('output_rfts_packing_po')
        //     ->where('po_id', $request->tujuan_ppic_master_so_id)
        //     ->where('so_det_id', $request->tujuan_so_det_id)
        //     ->orderBy('updated_at', 'desc')
        //     ->first();

        // if (
        //     ($dataCheckClosingAsal && checkClosingDate(date('Y-m-d', strtotime($dataCheckClosingAsal->updated_at)))) ||
        //     ($dataCheckClosingTujuan && checkClosingDate(date('Y-m-d', strtotime($dataCheckClosingTujuan->updated_at))))
        // ) {
        //     return [
        //         "status" => 400,
        //         "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
        //         "additional" => "Closing",
        //     ];
        // }

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

            $dataPackingCentralSwitching = PackingCentralSwitching::create([
                'no_trans'                 => $no_trans->kode,
                'packing_packing_in_id'    => $request->packing_packing_in_id ?: null,
                'asal_ppic_master_so_id'   => $request->asal_ppic_master_so_id ?: null,
                'asal_so_det_id'           => $request->asal_so_det_id,
                'tujuan_ppic_master_so_id' => $request->tujuan_ppic_master_so_id,
                'tujuan_so_det_id'         => $request->tujuan_so_det_id,
                'qty_switch'               => $request->qty_switch,
                "created_by"               => auth()->user()->id,
                "created_by_username"      => auth()->user()->username,
                "created_at"               => date('Y-m-d H:i:s'),
            ]);

            DB::table('packing_packing_in')->insert([
                'packing_central_switching_id' => $dataPackingCentralSwitching->id,
                'id_trf_garment'               => null,
                'no_trans'                     => $no_trans->kode,
                'tgl_penerimaan'               => date('Y-m-d'),
                'id_ppic_master_so'            => $request->tujuan_ppic_master_so_id,
                'id_so_det'                    => $request->tujuan_so_det_id,
                'qty'                          => $request->qty_switch,
                'line'                         => null,
                'po'                           => $request->tujuan_po,
                'barcode'                      => $request->tujuan_barcode,
                'dest'                         => $request->tujuan_dest,
                'sumber'                       => 'Switching',
                'created_at'                   => now(),
                'updated_at'                   => now(),
                'created_by'                   => auth()->user()->username,
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
                COALESCE(ppic_asal.po, packing_packing_in.po, 'TANPA PO') AS po,
                master_sb_ws.ws,
                master_sb_ws.buyer,
                packing_packing_in.line,
                packing_packing_in.barcode,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                ppic_master_so.po as po_tujuan,
                master_sb_ws_tujuan.ws as ws_tujuan,
                master_sb_ws_tujuan.buyer as buyer_tujuan,
                null as line_tujuan,
                ppic_master_so.barcode as barcode_tujuan,
                master_sb_ws_tujuan.styleno as styleno_tujuan,
                master_sb_ws_tujuan.color as color_tujuan,
                master_sb_ws_tujuan.size as size_tujuan,
                master_sb_ws_tujuan.dest as dest_tujuan,
                qty_switch,
                packing_central_switching.created_by_username,
                packing_central_switching.created_at
            FROM packing_central_switching
            LEFT JOIN packing_packing_in ON packing_packing_in.id = packing_central_switching.packing_packing_in_id
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = COALESCE(packing_central_switching.asal_so_det_id, packing_packing_in.id_so_det)
            LEFT JOIN ppic_master_so ppic_asal ON ppic_asal.id = packing_central_switching.asal_ppic_master_so_id
            LEFT JOIN ppic_master_so ON ppic_master_so.id = packing_central_switching.tujuan_ppic_master_so_id
            LEFT JOIN master_sb_ws AS master_sb_ws_tujuan ON master_sb_ws_tujuan.id_so_det = ppic_master_so.id_so_det
            WHERE DATE(packing_central_switching.created_at)
                BETWEEN ? AND ?
            ORDER BY packing_central_switching.id DESC
        ", [
            $request->dateFrom,
            $request->dateTo
        ]);

        return DataTables::of($data)->toJson();
    }

    public function getDataAsalPo(Request $request)
    {
        $search = strtoupper(trim((string) $request->search));

        $rows = array_filter($this->buildQueryDetailTransaksi(date('Y-m-d'), date('Y-m-d'), 'Tersedia', null), function ($row) use ($search) {
            return $search === '' || strpos(
                strtoupper($row->po . ' ' . $row->ws . ' ' . $row->styleno . ' ' . $row->color . ' ' . $row->size),
                $search
            ) !== false;
        });

        $data = [];
        foreach (array_slice($rows, 0, 100) as $row) {
            $data[] = $this->lengkapiStokAsal($row);
        }

        return response()->json($data);
    }

    /**
     * Baris stok Detail Transaksi dilengkapi data yang dibutuhkan form switching
     * (so_det_id, packing_packing_in_id, line, barcode).
     * - Ada ppic        : so_det dari ppic_master_so.
     * - PO tanpa ppic   : packing_packing_in dengan po tsb (supaya switching OUT jatuh ke PO itu).
     * - TANPA PO        : so_det dari master_sb_ws (ws/style/color/size), packing_packing_in_id NULL.
     */
    private function lengkapiStokAsal($row)
    {
        $pin = null;
        $soDetId = null;
        $barcode = null;

        if ($row->id_ppic_master_so) {
            $ppic = DB::table('ppic_master_so')->where('id', $row->id_ppic_master_so)->first(['id_so_det', 'barcode']);
            $soDetId = $ppic->id_so_det ?? null;
            $barcode = $ppic->barcode ?? null;

            $pin = DB::table('packing_packing_in')
                ->where('id_ppic_master_so', $row->id_ppic_master_so)
                ->orderBy('id')
                ->first(['id', 'id_so_det', 'line', 'barcode']);
        } else {
            $soDetIds = DB::table('master_sb_ws')
                ->where('ws', $row->ws)
                ->where('styleno', $row->styleno)
                ->where('color', $row->color)
                ->where('size', $row->size)
                ->orderBy('id_so_det')
                ->pluck('id_so_det')
                ->all();

            $soDetId = $soDetIds[0] ?? null;

            if ($row->po !== 'TANPA PO' && !empty($soDetIds)) {
                $pin = DB::table('packing_packing_in')
                    ->whereNull('id_ppic_master_so')
                    ->where('po', $row->po)
                    ->whereIn('id_so_det', $soDetIds)
                    ->orderBy('id')
                    ->first(['id', 'id_so_det', 'line', 'barcode']);
            }
        }

        if ($pin && $pin->id_so_det) {
            $soDetId = $pin->id_so_det;
        }

        return [
            'tgl_shipment'          => $row->tgl_shipment,
            'id_ppic_master_so'     => $row->id_ppic_master_so,
            'packing_packing_in_id' => $pin->id ?? null,
            'po'                    => $row->po,
            'line'                  => $pin->line ?? null,
            'barcode'               => $pin->barcode ?? $barcode,
            'ws'                    => $row->ws,
            'styleno'               => $row->styleno,
            'color'                 => $row->color,
            'size'                  => $row->size,
            'dest'                  => $row->dest,
            'so_det_id'             => $soDetId,
            'qty_sisa'              => (int) $row->saldo_akhir,
            'status'                => $row->status,
            'close_order'           => $row->status_order === 'Close Order' ? 'Y' : 'N',
        ];
    }

    /**
     * Stok asal Packing Central per ppic + so_det. Untuk ppic NULL
     * (GUDANG STOK / TEMPORARY PACKING) dipisah lagi per po.
     */
    private function sqlStokAsalPo(string $filter): string
    {
        return "
            WITH a AS (
                SELECT
                    a.id_ppic_master_so,
                    IF(a.id_ppic_master_so IS NULL, a.po, NULL) AS po,
                    a.id_so_det AS so_det_id,
                    SUM(a.qty) AS qty_pck_in
                FROM packing_packing_in a
                LEFT JOIN laravel_nds.ppic_master_so p
                    ON a.id_ppic_master_so = p.id
                WHERE a.sumber IN ('Sewing', 'FGS', 'TEMPORARY PACKING')
                    AND (
                        a.id_ppic_master_so IS NULL
                        OR p.tgl_shipment >= '2026-01-01'
                        OR p.po = 'HGL.CMT/X/2025/039/SGT/1025/165/BLACK'
                        OR p.po = '61297671'
                        OR p.po = '61297673'
                    )
                GROUP BY
                    a.id_ppic_master_so,
                    IF(a.id_ppic_master_so IS NULL, a.po, NULL),
                    a.id_so_det
            ),

            p AS (
                SELECT
                    pos.id_ppic,
                    IF(pos.id_ppic IS NULL, pos.po, NULL) AS po,
                    pos.id_so_det,
                    COUNT(*) AS qty_scan
                FROM packing_packing_out_scan pos
                GROUP BY
                    pos.id_ppic,
                    IF(pos.id_ppic IS NULL, pos.po, NULL),
                    pos.id_so_det
            ),

            s AS (
                SELECT
                    pcs.asal_ppic_master_so_id,
                    IF(pcs.asal_ppic_master_so_id IS NULL, asal.po, NULL) AS po,
                    pcs.asal_so_det_id,
                    SUM(pcs.qty_switch) AS qty_switch
                FROM packing_central_switching pcs
                INNER JOIN packing_packing_in asal ON asal.id = pcs.packing_packing_in_id
                GROUP BY
                    pcs.asal_ppic_master_so_id,
                    IF(pcs.asal_ppic_master_so_id IS NULL, asal.po, NULL),
                    pcs.asal_so_det_id
            ),

            t AS (
                SELECT
                    pcs.tujuan_ppic_master_so_id AS id_ppic_master_so,
                    pcs.tujuan_so_det_id AS so_det_id,
                    SUM(pcs.qty_switch) AS qty_switch_masuk
                FROM packing_central_switching pcs
                GROUP BY
                    pcs.tujuan_ppic_master_so_id,
                    pcs.tujuan_so_det_id
            ),

            r AS (
                SELECT
                    fo.id_ppic_master_so,
                    IF(fo.id_ppic_master_so IS NULL, fo.po, NULL) AS po,
                    fo.id_so_det,
                    SUM(fo.qty) AS qty
                FROM fg_fg_out fo
                WHERE fo.status = 'RETUR'
                GROUP BY
                    fo.id_ppic_master_so,
                    IF(fo.id_ppic_master_so IS NULL, fo.po, NULL),
                    fo.id_so_det
            ),

            g AS (
                SELECT
                    ogs.ppic_master_so_id AS id_ppic_master_so,
                    IF(ogs.ppic_master_so_id IS NULL, ogs.po, NULL) AS po,
                    ogs.so_det_id,
                    SUM(ogs.qty) AS qty_out_gudang_stok
                FROM packing_out_gudang_stok ogs
                WHERE ogs.lokasi_asal = 'PACKING CENTRAL'
                GROUP BY
                    ogs.ppic_master_so_id,
                    IF(ogs.ppic_master_so_id IS NULL, ogs.po, NULL),
                    ogs.so_det_id
            ),

            combined AS (
                SELECT
                    id_ppic_master_so,
                    po,
                    so_det_id,
                    qty_pck_in AS qty,
                    0 AS qty_scan,
                    0 AS qty_switch,
                    0 AS qty_switch_masuk,
                    0 AS qty_retur,
                    0 AS qty_out_gudang_stok
                FROM a

                UNION ALL

                SELECT
                    id_ppic AS id_ppic_master_so,
                    po,
                    id_so_det AS so_det_id,
                    0 AS qty,
                    qty_scan,
                    0 AS qty_switch,
                    0 AS qty_switch_masuk,
                    0 AS qty_retur,
                    0 AS qty_out_gudang_stok
                FROM p

                UNION ALL

                SELECT
                    asal_ppic_master_so_id AS id_ppic_master_so,
                    po,
                    asal_so_det_id AS so_det_id,
                    0 AS qty,
                    0 AS qty_scan,
                    qty_switch,
                    0 AS qty_switch_masuk,
                    0 AS qty_retur,
                    0 AS qty_out_gudang_stok
                FROM s

                UNION ALL

                SELECT
                    id_ppic_master_so,
                    NULL AS po,
                    so_det_id,
                    0 AS qty,
                    0 AS qty_scan,
                    0 AS qty_switch,
                    qty_switch_masuk,
                    0 AS qty_retur,
                    0 AS qty_out_gudang_stok
                FROM t

                UNION ALL

                SELECT
                    id_ppic_master_so,
                    po,
                    id_so_det AS so_det_id,
                    0 AS qty,
                    0 AS qty_scan,
                    0 AS qty_switch,
                    0 AS qty_switch_masuk,
                    qty AS qty_retur,
                    0 AS qty_out_gudang_stok
                FROM r

                UNION ALL

                SELECT
                    id_ppic_master_so,
                    po,
                    so_det_id,
                    0 AS qty,
                    0 AS qty_scan,
                    0 AS qty_switch,
                    0 AS qty_switch_masuk,
                    0 AS qty_retur,
                    qty_out_gudang_stok
                FROM g
            )

            SELECT
                DATE_FORMAT(ppic_master_so.tgl_shipment, '%d-%m-%Y') AS tgl_shipment,
                combined.id_ppic_master_so,
                packing_packing_in.id AS packing_packing_in_id,
                COALESCE(ppic_master_so.po, combined.po) AS po,
                pin_detail.line,
                pin_detail.barcode,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                combined.so_det_id,
                ppic_master_so.qty_po,
                SUM(combined.qty) AS qty_pck_in,
                SUM(combined.qty_switch_masuk) AS qty_switch_in,
                SUM(combined.qty_switch) AS qty_switch_out,
                SUM(combined.qty_scan) AS qty_scan,
                SUM(combined.qty_retur) AS qty_retur,
                SUM(combined.qty_out_gudang_stok) AS qty_out_gudang_stok,
                SUM(combined.qty)
                    + SUM(combined.qty_retur)
                    + SUM(combined.qty_switch_masuk)
                    - SUM(combined.qty_scan)
                    - SUM(combined.qty_switch)
                    - SUM(combined.qty_out_gudang_stok) AS qty_sisa,
                CASE
                    WHEN (
                        SUM(combined.qty)
                        + SUM(combined.qty_retur)
                        + SUM(combined.qty_switch_masuk)
                        - SUM(combined.qty_scan)
                        - SUM(combined.qty_switch)
                        - SUM(combined.qty_out_gudang_stok)
                    ) > 0
                    THEN 'Tersedia'
                    ELSE 'Kosong'
                END AS status,
                MAX(act_costing.close_order) AS close_order
            FROM combined
            LEFT JOIN (
                SELECT
                    id_ppic_master_so,
                    IF(id_ppic_master_so IS NULL, po, NULL) AS po,
                    id_so_det,
                    MIN(id) AS id
                FROM packing_packing_in
                GROUP BY
                    id_ppic_master_so,
                    IF(id_ppic_master_so IS NULL, po, NULL),
                    id_so_det
            ) packing_packing_in
                ON COALESCE(packing_packing_in.id_ppic_master_so, 0) = COALESCE(combined.id_ppic_master_so, 0)
                AND packing_packing_in.id_so_det = combined.so_det_id
                AND COALESCE(packing_packing_in.po, '') = COALESCE(combined.po, '')
            LEFT JOIN packing_packing_in pin_detail ON pin_detail.id = packing_packing_in.id
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = combined.so_det_id
            LEFT JOIN signalbit_erp.act_costing ON act_costing.id = master_sb_ws.id_act_cost
            LEFT JOIN ppic_master_so ON ppic_master_so.id = combined.id_ppic_master_so
            WHERE
                (
                    combined.id_ppic_master_so IS NULL
                    OR ppic_master_so.tgl_shipment >= '2026-01-01'
                    OR ppic_master_so.po = 'HGL.CMT/X/2025/039/SGT/1025/165/BLACK'
                    OR ppic_master_so.po = '61297671'
                    OR ppic_master_so.po = '61297673'
                )
                AND $filter
            GROUP BY
                combined.id_ppic_master_so,
                combined.po,
                combined.so_det_id,
                packing_packing_in.id,
                pin_detail.line,
                pin_detail.barcode,
                ppic_master_so.po,
                ppic_master_so.tgl_shipment,
                ppic_master_so.qty_po,
                master_sb_ws.ws,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest
            HAVING qty_sisa > 0
        ";
    }

    // public function getDataAsalPo(Request $request)
    // {
    //     $search = $request->search;

    //     $data = DB::select("
    //         WITH a AS (              
    //             SELECT
    //                 a.id_ppic_master_so,
    //                 a.id_so_det          AS so_det_id,
    //                 SUM(a.qty)           AS qty_trf_gmt
    //             FROM laravel_nds.packing_trf_garment a
    //             INNER JOIN laravel_nds.ppic_master_so p ON a.id_ppic_master_so = p.id
    //             WHERE YEAR(p.tgl_shipment) >= 2026
    //             GROUP BY a.id_ppic_master_so, a.id_so_det
    //         ),
    //         p AS (
    //             SELECT id_ppic, id_so_det, COUNT(*) AS qty_scan
    //             FROM packing_packing_out_scan
    //             GROUP BY id_ppic, id_so_det
    //         ),
    //         s AS(
    //             SELECT asal_ppic_master_so_id, asal_so_det_id, SUM(qty_switch) AS qty_switch
    //             FROM packing_central_switching
    //             GROUP BY asal_ppic_master_so_id, asal_so_det_id
    //         ),
    //         combined AS (
    //             SELECT id_ppic_master_so, so_det_id, qty_trf_gmt AS qty, 0 AS qty_scan, 0 AS qty_switch FROM a
    //             UNION ALL
    //             SELECT id_ppic as id_ppic_master_so, id_so_det as so_det_id, 0 as qty, qty_scan, 0 as qty_switch FROM p
    //             UNION ALL
    //             SELECT asal_ppic_master_so_id as id_ppic_master_so, asal_so_det_id as so_det_id, 0 as qty, 0 as qty_scan, qty_switch FROM s
    //         )

    //         SELECT
    //             combined.id_ppic_master_so,
    //             packing_packing_in.id AS packing_packing_in_id,
    //             packing_packing_in.po,
    //             master_sb_ws.ws,
    //             master_sb_ws.color,
    //             master_sb_ws.size,
    //             master_sb_ws.dest,
    //             combined.so_det_id,
    //             SUM(combined.qty)      AS qty_trf_gmt,
    //             SUM(combined.qty_scan) AS qty_scan,
    //             SUM(combined.qty_switch) AS qty_switch,
    //             SUM(combined.qty) - SUM(combined.qty_scan) - SUM(combined.qty_switch) AS qty_sisa
    //         FROM combined
    //         LEFT JOIN (
    //             SELECT
    //                 id_ppic_master_so,
    //                 id_so_det,
    //                 MIN(id) AS id,
    //                 MAX(po) AS po
    //             FROM packing_packing_in
    //             GROUP BY
    //                 id_ppic_master_so,
    //                 id_so_det
    //         ) packing_packing_in
    //             ON packing_packing_in.id_ppic_master_so = combined.id_ppic_master_so
    //         AND packing_packing_in.id_so_det = combined.so_det_id
    //         LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = packing_packing_in.id_so_det 
    //         GROUP BY id_ppic_master_so, so_det_id
    //         HAVING qty_sisa > 0 AND packing_packing_in.po LIKE ?
    //     ", ["%{$search}%"]);

    //     return response()->json($data);
    // }

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
                COALESCE(ppic_asal.po, packing_packing_in.po, 'TANPA PO') AS po,
                master_sb_ws.ws,
                master_sb_ws.buyer,
                packing_packing_in.line,
                packing_packing_in.barcode,
                master_sb_ws.styleno,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                ppic_master_so.po as po_tujuan,
                master_sb_ws_tujuan.ws as ws_tujuan,
                master_sb_ws_tujuan.buyer as buyer_tujuan,
                null as line_tujuan,
                ppic_master_so.barcode as barcode_tujuan,
                master_sb_ws_tujuan.styleno as styleno_tujuan,
                master_sb_ws_tujuan.color as color_tujuan,
                master_sb_ws_tujuan.size as size_tujuan,
                master_sb_ws_tujuan.dest as dest_tujuan,
                qty_switch,
                packing_central_switching.created_by_username,
                packing_central_switching.created_at
            FROM packing_central_switching
            LEFT JOIN packing_packing_in ON packing_packing_in.id = packing_central_switching.packing_packing_in_id
            LEFT JOIN master_sb_ws ON master_sb_ws.id_so_det = COALESCE(packing_central_switching.asal_so_det_id, packing_packing_in.id_so_det)
            LEFT JOIN ppic_master_so ppic_asal ON ppic_asal.id = packing_central_switching.asal_ppic_master_so_id
            LEFT JOIN ppic_master_so ON ppic_master_so.id = packing_central_switching.tujuan_ppic_master_so_id
            LEFT JOIN master_sb_ws AS master_sb_ws_tujuan ON master_sb_ws_tujuan.id_so_det = ppic_master_so.id_so_det
            WHERE DATE(packing_central_switching.created_at)
                BETWEEN ? AND ?
            ORDER BY packing_central_switching.id DESC
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

        // Header Group
        $headerTop = [
            'NO TRANSAKSI',
            '',
            'ASAL',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TUJUAN',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'HASIL',
            '',
            '',
        ];

        $sheet->writeRow(
            $headerTop,
            [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
            ]
        );

        $sheet->mergeCells('A4:B4');
        $sheet->mergeCells('C4:K4');
        $sheet->mergeCells('L4:S4');
        $sheet->mergeCells('T4:V4');

        $sheet->setCellStyle('A4:B4', [
            'fill'       => '#DBEAFE',
            'font-color' => '#172554',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('C4:K4', [
            'fill'       => '#F2E6FF',
            'font-color' => '#350E7D',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('L4:S4', [
            'fill'       => '#DCFCE7',
            'font-color' => '#166534',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('T4:V4', [
            'fill'       => '#FEF3C7',
            'font-color' => '#92400E',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        // Header Detail
        $sheet->writeRow([
            'No. Trans',
            'Tgl. Trans',
            'PO Asal',
            'WS Asal',
            'Buyer Asal',
            'Line Asal',
            'Barcode Asal',
            'Style Asal',
            'Color Asal',
            'Size Asal',
            'Dest Asal',
            'PO Tujuan',
            'WS Tujuan',
            'Buyer Tujuan',
            'Barcode Tujuan',
            'Style Tujuan',
            'Color Tujuan',
            'Size Tujuan',
            'Dest Tujuan',
            'Qty',
            'User',
            'Created At',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
        ]);

        $sheet->setCellStyle('A5:B5', [
            'fill'       => '#DBEAFE',
            'font-color' => '#172554',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('C5:K5', [
            'fill'       => '#F2E6FF',
            'font-color' => '#350E7D',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('L5:S5', [
            'fill'       => '#DCFCE7',
            'font-color' => '#166534',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('T5:V5', [
            'fill'       => '#FEF3C7',
            'font-color' => '#92400E',
            'font-style' => 'bold',
            'text-align' => 'center',
        ]);

        foreach ($data as $row) {

            $rows = [
                $row->no_trans ?? '',
                $row->tgl_trans ?? '',

                $row->po ?? '',
                $row->ws ?? '',
                $row->buyer ?? '',
                $row->line ?? '',
                $row->barcode ?? '',
                $row->styleno ?? '',
                $row->color ?? '',
                $row->size ?? '',
                $row->dest ?? '',

                $row->po_tujuan ?? '',
                $row->ws_tujuan ?? '',
                $row->buyer_tujuan ?? '',
                $row->barcode_tujuan ?? '',
                $row->styleno_tujuan ?? '',
                $row->color_tujuan ?? '',
                $row->size_tujuan ?? '',
                $row->dest_tujuan ?? '',

                (float) ($row->qty_switch ?? 0),
                $row->created_by_username ?? '',
                $row->created_at ?? '',
            ];

            $sheet->writeRow($rows, [
                'border' => 'thin',
            ]);
        }

        foreach (range('A', 'V') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

    public function summaryWipPo(Request $request){
        ini_set('memory_limit', '2048M');

        // Mengikuti Detail Transaksi per hari ini: PO dihitung dari baris Tersedia, 'TANPA PO' bukan PO.
        $today = date('Y-m-d');
        $dataPo = collect($this->buildQueryDetailTransaksi($today, $today, '', null));

        $totalPo = $dataPo
            ->filter(function ($row) {
                return $row->saldo_akhir > 0 && $row->po !== 'TANPA PO';
            })
            ->pluck('po')
            ->unique()
            ->count();

         return response()->json([
            'total_po' => $totalPo,
            'total_qty_sisa' => $dataPo->sum('saldo_akhir'),
        ]);
    }

    public function detailTransaksi(Request $request)
    {
        $dataPo = DB::select("
            SELECT DISTINCT po FROM ppic_master_so
            WHERE tgl_shipment >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)

            UNION

            SELECT DISTINCT po FROM packing_packing_in
            WHERE id_ppic_master_so IS NULL
                AND po IS NOT NULL
                AND tgl_penerimaan >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ");

        if ($request->ajax()) {
            ini_set('memory_limit', '2048M');

            $start_date = date('Y-m-d', strtotime($request->input('start_date') ?: 'today'));
            $end_date = date('Y-m-d', strtotime($request->input('end_date') ?: 'today'));

            $rawData = $this->buildQueryDetailTransaksi(
                $start_date,
                $end_date,
                $request->input('status'),
                $request->input('po')
            );

            return DataTables::of($rawData)->toJson();
        }

        // For non-AJAX (initial page load)
        return view(
            'packing.detail_transaksi_packing_central_switching',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-packing-in",
                "subPage" => "detail_transaksi_packing_central_switching",
                "containerFluid" => true,
                "dataPo" => $dataPo
            ]
        );
    }

    // Dipakai cek stok Packing Out: Saldo Akhir Detail Transaksi hari ini untuk PO + item (ws/style/color/size) dari so_det
    public function saldoAkhirDetailTransaksi($po, array $soDetIds)
    {
        if (empty($soDetIds)) {
            return 0;
        }

        $kunci = function ($ws, $style, $color, $size) {
            return strtoupper(trim($ws)) . '|' . strtoupper(trim($style)) . '|' . strtoupper(trim($color)) . '|' . strtoupper(trim($size));
        };

        $item = [];
        foreach (DB::table('master_sb_ws')->whereIn('id_so_det', $soDetIds)->get(['ws', 'styleno', 'color', 'size']) as $m) {
            $item[$kunci($m->ws, $m->styleno, $m->color, $m->size)] = true;
        }

        $today = date('Y-m-d');
        $saldo = 0;
        foreach ($this->buildQueryDetailTransaksi($today, $today, '', $po) as $row) {
            if (isset($item[$kunci($row->ws, $row->styleno, $row->color, $row->size)])) {
                $saldo += (int) $row->saldo_akhir;
            }
        }

        return $saldo;
    }

    // Sumber dan perhitungan kolom Packing Central mengikuti PackingReportController::buildQueryMutasiPacking,
    // tetapi dipecah per PO. Transaksi yang tidak punya PO masuk ke baris 'TANPA PO'.
    private function buildQueryDetailTransaksi($tgl_awal, $tgl_akhir, $status, $po)
    {
        // Saldo awal dari saldo_wip_packing_central (posisi akhir hari tgl ini), transaksi dihitung mulai besoknya
        $tgl_saldo = self::WIP_TGL_SALDO;
        $mulai = date('Y-m-d', strtotime($tgl_saldo . ' +1 day'));

        // periode tidak boleh sebelum snapshot
        if ($tgl_awal < $mulai) {
            $tgl_awal = $mulai;
        }
        if ($tgl_akhir < $tgl_awal) {
            $tgl_akhir = $tgl_awal;
        }

        $bindings = [];

        $statusCondition = '';
        if (!empty($status)) {
            $statusCondition = "AND status = ?";
            $bindings[] = $status;
        }

        $poCondition = '';
        if (!empty($po)) {
            $po = is_array($po) ? $po : [$po];
            $poCondition = "AND po IN (" . implode(',', array_fill(0, count($po), '?')) . ")";
            $bindings = array_merge($bindings, $po);
        }

        return DB::select("
            WITH trx ( id_ppic_master_so, po, ws, style, color, size, dest, tgl_penerimaan, saldo_awal, terima_packing_line, terima_temporary, terima_return, terima_gudang_stok, packing_scan_fg_in, keluar_gudang_stok, switching_out, switching_in, adjustment ) AS (

            /* ================= SALDO AWAL (SNAPSHOT saldo_wip_packing_central) ================= */
                SELECT
                    s.id_ppic_master_so,
                    IF(s.id_ppic_master_so IS NULL, s.po, NULL),
                    s.ws, s.styleno, s.color, s.size, s.dest, s.tgl_shipment,
                    s.qty,
                    0, 0, 0, 0, 0, 0, 0, 0, 0
                FROM
                    saldo_wip_packing_central s
                WHERE
                    s.tgl_saldo = '{$tgl_saldo}'

                UNION ALL

            /* ================= TERIMA PACKING LINE ================= */
                SELECT
                    pi.id_ppic_master_so, NULL, msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    IF(pi.tgl_penerimaan < '{$tgl_awal} 00:00:00', pi.qty, 0),
                    IF(pi.tgl_penerimaan >= '{$tgl_awal} 00:00:00', pi.qty, 0),
                    0, 0, 0, 0, 0, 0, 0, 0
                FROM
                    packing_packing_in pi
                    JOIN packing_trf_garment b ON pi.id_trf_garment = b.id
                    JOIN ppic_master_so pms ON pms.id = pi.id_ppic_master_so
                    INNER JOIN master_sb_ws msw ON msw.id_so_det = pms.id_so_det
                WHERE
                    pi.tgl_penerimaan >= '{$mulai} 00:00:00'
                    AND pi.tgl_penerimaan <= '{$tgl_akhir} 23:59:59'

                UNION ALL

            /* ================= TERIMA RETURN HISTORY (BPPB) ================= */
                SELECT
                    NULL, NULL, msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    b.qty,
                    0, 0, 0, 0, 0, 0, 0, 0, 0
                FROM
                    signalbit_erp.bppb b
                    INNER JOIN master_sb_ws msw ON msw.id_so_det = b.id_so_det
                WHERE
                    b.bppbno_int LIKE '%FG/RO%'
                    AND b.bppbdate >= '{$mulai} 00:00:00'
                    AND b.bppbdate < '{$tgl_awal} 00:00:00'
                    AND b.id_supplier NOT IN (458 , 927 , 2053)

                UNION ALL

            /* ================= TERIMA RETURN PERIODE (BPPB) ================= */
                SELECT
                    NULL, NULL, msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    0, 0, 0,
                    b.qty,
                    0, 0, 0, 0, 0, 0
                FROM
                    signalbit_erp.bppb b
                    INNER JOIN master_sb_ws msw ON msw.id_so_det = b.id_so_det
                WHERE
                    b.id_so_det != '0'
                    AND b.bppbno_int LIKE '%FG/RO%'
                    AND b.bppbdate >= '{$mulai} 00:00:00'
                    AND b.bppbdate BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'
                    AND b.id_supplier NOT IN (458 , 927 , 2053)

                UNION ALL

            /* ================= PACKING SCAN FG IN (PACKING OUT SCAN, 1 scan = 1 pcs) ================= */
                SELECT
                    s.id_ppic, NULL, msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    -SUM(s.tgl_trans < '{$tgl_awal}'),
                    0, 0, 0, 0,
                    SUM(s.tgl_trans >= '{$tgl_awal}'),
                    0, 0, 0, 0
                FROM
                    packing_packing_out_scan s
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = s.id_so_det
                WHERE
                    s.id_ppic IS NOT NULL
                    AND s.tgl_trans >= '{$mulai}'
                    AND s.tgl_trans <= '{$tgl_akhir}'
                GROUP BY
                    s.id_ppic, msw.ws, msw.styleno, msw.color, msw.size, msw.dest

                UNION ALL

            /* ================= INJECT MUTASI PERIODE ================= */
                SELECT
                    NULL, NULL, ws, styleno, color, size, NULL, NULL,
                    COALESCE(pc_saldo_awal, 0),
                    COALESCE(pc_terima, 0),
                    0, 0, 0,
                    COALESCE(pc_packing_scan, 0),
                    0, 0, 0, 0
                FROM
                    signalbit_erp.inject_mutasi_sewing
                WHERE
                    type_saldo = 'PACKING'
                    AND tgl_saldo >= '{$mulai}'
                    AND tgl_saldo BETWEEN '{$tgl_awal} 00:00:00' AND '{$tgl_akhir} 23:59:59'

                UNION ALL

            /* ================= INJECT MUTASI HISTORY ================= */
                SELECT
                    NULL, NULL, ws, styleno, color, size, NULL, NULL,
                    COALESCE(pc_saldo_awal, 0) + COALESCE(pc_terima, 0),
                    0, 0, 0, 0, 0, 0, 0, 0, 0
                FROM
                    signalbit_erp.inject_mutasi_sewing
                WHERE
                    type_saldo = 'PACKING'
                    AND tgl_saldo >= '{$mulai}'
                    AND tgl_saldo < '{$tgl_awal}'

                UNION ALL

            /* ================= ADJUSTMENT ================= */
                SELECT
                    NULL, NULL, no_ws, style, color, size, NULL, NULL,
                    IF(tgl_saldo < '{$tgl_awal}', qty, 0),
                    0, 0, 0, 0, 0, 0, 0, 0,
                    IF(tgl_saldo >= '{$tgl_awal}', qty, 0)
                FROM
                    wip_adjustment
                WHERE
                    tgl_saldo >= '{$mulai}'
                    AND tgl_saldo <= '{$tgl_akhir}'
                    AND type_report = 'PACKING_CENTRAL'
                    AND status = 'Y'

                UNION ALL

            /* ================= SWITCHING ADJUSTMENT OUT ================= */
                SELECT
                    NULL, NULL, from_no_ws, from_style, from_color, from_size, NULL, NULL,
                    IF(from_tgl_saldo < '{$tgl_awal}', -qty, 0),
                    0, 0, 0, 0, 0, 0,
                    IF(from_tgl_saldo >= '{$tgl_awal}', qty, 0),
                    0, 0
                FROM
                    wip_switching_adj
                WHERE
                    from_tgl_saldo >= '{$mulai}'
                    AND from_tgl_saldo <= '{$tgl_akhir}'
                    AND type_report = 'PACKING_CENTRAL'

                UNION ALL

            /* ================= SWITCHING ADJUSTMENT IN ================= */
                SELECT
                    NULL, NULL, no_ws, style, color, size, NULL, NULL,
                    IF(tgl_saldo < '{$tgl_awal}', qty, 0),
                    0, 0, 0, 0, 0, 0, 0,
                    IF(tgl_saldo >= '{$tgl_awal}', qty, 0),
                    0
                FROM
                    wip_switching_adj
                WHERE
                    tgl_saldo >= '{$mulai}'
                    AND tgl_saldo <= '{$tgl_akhir}'
                    AND type_report = 'PACKING_CENTRAL'

                UNION ALL

            /* ================= SWITCHING TRANSAKSI OUT ================= */
                SELECT
                    sw.asal_ppic_master_so_id,
                    IF(sw.asal_ppic_master_so_id IS NULL, asal.po, NULL),
                    msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    IF(date(sw.created_at) < '{$tgl_awal}', -sw.qty_switch, 0),
                    0, 0, 0, 0, 0, 0,
                    IF(date(sw.created_at) >= '{$tgl_awal}', sw.qty_switch, 0),
                    0, 0
                FROM
                    packing_central_switching sw
                    LEFT JOIN packing_packing_in asal ON asal.id = sw.packing_packing_in_id
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = sw.asal_so_det_id
                WHERE
                    date(sw.created_at) >= '{$mulai}'
                    AND date(sw.created_at) <= '{$tgl_akhir}'

                UNION ALL

            /* ================= SWITCHING TRANSAKSI IN ================= */
                SELECT
                    sw.tujuan_ppic_master_so_id, NULL, msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    IF(date(sw.created_at) < '{$tgl_awal}', sw.qty_switch, 0),
                    0, 0, 0, 0, 0, 0, 0,
                    IF(date(sw.created_at) >= '{$tgl_awal}', sw.qty_switch, 0),
                    0
                FROM
                    packing_central_switching sw
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = sw.tujuan_so_det_id
                WHERE
                    date(sw.created_at) >= '{$mulai}'
                    AND date(sw.created_at) <= '{$tgl_akhir}'

                UNION ALL

            /* ================= TERIMA GUDANG STOK ================= */
                SELECT
                    pi.id_ppic_master_so,
                    IF(pi.id_ppic_master_so IS NULL, pi.po, NULL),
                    msw.ws, msw.styleno, msw.color, msw.size, msw.dest, pi.tgl_penerimaan,
                    IF(pi.tgl_penerimaan < '{$tgl_awal}', pi.qty, 0),
                    0, 0, 0,
                    IF(pi.tgl_penerimaan >= '{$tgl_awal}', pi.qty, 0),
                    0, 0, 0, 0, 0
                FROM
                    packing_packing_in pi
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = pi.id_so_det
                WHERE
                    pi.tgl_penerimaan >= '{$mulai}'
                    AND pi.tgl_penerimaan <= '{$tgl_akhir}'
                    AND pi.sumber = 'FGS'

                UNION ALL

            /* ================= TERIMA TEMPORARY PACKING ================= */
                SELECT
                    pi.id_ppic_master_so,
                    IF(pi.id_ppic_master_so IS NULL, pi.po, NULL),
                    msw.ws, msw.styleno, msw.color, msw.size, msw.dest, pi.tgl_penerimaan,
                    IF(pi.tgl_penerimaan < '{$tgl_awal}', pi.qty, 0),
                    0,
                    IF(pi.tgl_penerimaan >= '{$tgl_awal}', pi.qty, 0),
                    0, 0, 0, 0, 0, 0, 0
                FROM
                    packing_packing_in pi
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = pi.id_so_det
                WHERE
                    pi.tgl_penerimaan >= '{$mulai}'
                    AND pi.tgl_penerimaan <= '{$tgl_akhir}'
                    AND pi.sumber = 'TEMPORARY PACKING'

                UNION ALL

            /* ================= KELUAR GUDANG STOK ================= */
                SELECT
                    g.ppic_master_so_id,
                    IF(g.ppic_master_so_id IS NULL, g.po, NULL),
                    msw.ws, msw.styleno, msw.color, msw.size, msw.dest, NULL,
                    IF(g.created_at < '{$tgl_awal} 00:00:00', -g.qty, 0),
                    0, 0, 0, 0, 0,
                    IF(g.created_at >= '{$tgl_awal} 00:00:00', g.qty, 0),
                    0, 0, 0
                FROM
                    packing_out_gudang_stok g
                    LEFT JOIN master_sb_ws msw ON msw.id_so_det = g.so_det_id
                WHERE
                    g.created_at >= '{$mulai} 00:00:00'
                    AND g.created_at <= '{$tgl_akhir} 23:59:59'
                    AND g.lokasi_asal = 'PACKING CENTRAL'
            ),

            mutasi AS (
                SELECT
                    DATE_FORMAT(
                        COALESCE(ppic_master_so.tgl_shipment, MAX(trx.tgl_penerimaan)),
                        '%d-%m-%Y'
                    ) AS tgl_shipment,
                    trx.id_ppic_master_so,
                    COALESCE(ppic_master_so.po, NULLIF(trx.po, ''), 'TANPA PO') AS po,
                    trx.ws,
                    trx.style AS styleno,
                    trx.color,
                    trx.size,
                    GROUP_CONCAT(DISTINCT trx.dest SEPARATOR ', ') AS dest,
                    SUM(trx.saldo_awal) AS saldo_awal,
                    SUM(trx.terima_packing_line) AS terima_packing_line,
                    SUM(trx.terima_temporary) AS terima_temporary,
                    SUM(trx.terima_return) AS terima_return,
                    SUM(trx.terima_gudang_stok) AS terima_gudang_stok,
                    SUM(trx.packing_scan_fg_in) AS packing_scan_fg_in,
                    SUM(trx.keluar_gudang_stok) AS keluar_gudang_stok,
                    SUM(trx.switching_out) AS switching_out,
                    SUM(trx.switching_in) AS switching_in,
                    SUM(trx.adjustment) AS adjustment,
                    SUM(trx.saldo_awal)
                        + SUM(trx.terima_packing_line)
                        + SUM(trx.terima_temporary)
                        + SUM(trx.terima_return)
                        + SUM(trx.terima_gudang_stok)
                        - SUM(trx.packing_scan_fg_in)
                        - SUM(trx.keluar_gudang_stok)
                        - SUM(trx.switching_out)
                        + SUM(trx.switching_in)
                        + SUM(trx.adjustment) AS saldo_akhir
                FROM trx
                LEFT JOIN ppic_master_so ON ppic_master_so.id = trx.id_ppic_master_so
                GROUP BY
                    trx.id_ppic_master_so,
                    COALESCE(ppic_master_so.po, NULLIF(trx.po, ''), 'TANPA PO'),
                    trx.ws,
                    trx.style,
                    trx.color,
                    trx.size
            ),

            result AS (
                SELECT
                    mutasi.*,
                    IF(saldo_akhir > 0, 'Tersedia', 'Kosong') AS status,
                    IF(ac.close_order = 'Y', 'Close Order', 'Open Order') AS status_order
                FROM mutasi
                LEFT JOIN (
                    SELECT
                        kpno,
                        MAX(close_order) AS close_order
                    FROM signalbit_erp.act_costing
                    GROUP BY kpno
                ) ac ON ac.kpno = mutasi.ws
            )

            SELECT
                *
            FROM result
            WHERE 1=1
                $statusCondition
                $poCondition
            ORDER BY ws, color, po
        ", $bindings);
    }

    public function export_detail_transaksi_packing_central_switching(Request $request)
    {
        ini_set('memory_limit', '2048M');

        $start_date = date('Y-m-d', strtotime($request->start_date ?: 'today'));
        $end_date = date('Y-m-d', strtotime($request->end_date ?: 'today'));
        $status = $request->status;
        $po = $request->po;

        $poList = '';
        if (!empty($po)) {
            if (!is_array($po)) {
                $po = [$po];
            }

            $poList = "'" . implode("','", $po) . "'";
        }

        $data = $this->buildQueryDetailTransaksi($start_date, $end_date, $status, $po);

        $fileName = 'detail-transaksi-wip-po';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Detail Transaksi WIP PO'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            [
                !empty($start_date) && !empty($end_date)
                    ? 'Periode : ' . $start_date . ' s/d ' . $end_date
                    : 'Periode : '
            ],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(
            ['PO : ' . str_replace(',', ', ', trim(str_replace("'", "", $poList), ", "))],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(
            ['Status : ' . (!empty($status) ? $status : 'Semua')],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $sheet->setColFormat('B', '@date');

        $header = [
            'Status Order',
            'Tgl Shipment',
            'PO',
            'WS',
            'Style',
            'Color',
            'Size',
            'Dest',
            'Saldo Awal',
            'Terima Packing Line',
            'Terima Temporary Packing',
            'Terima Return',
            'Terima Gudang Stok',
            'Packing Scan FG In',
            'Keluar Gudang Stok',
            'Switching OUT',
            'Switching IN',
            'Adjustment',
            'Saldo Akhir',
            'Status',
        ];

        $sheet->writeRow(
            $header,
            [
                'font-style' => 'bold',
                'border'     => 'thin',
            ]
        );

        foreach ($data as $row) {

            $rows = [
                $row->status_order,
                $row->tgl_shipment ?? '',
                $row->po ?? '',
                $row->ws ?? '',
                $row->styleno ?? '',
                $row->color ?? '',
                $row->size ?? '',
                $row->dest ?? '',
                (float) $row->saldo_awal,
                (float) $row->terima_packing_line,
                (float) $row->terima_temporary,
                (float) $row->terima_return,
                (float) $row->terima_gudang_stok,
                (float) $row->packing_scan_fg_in,
                (float) $row->keluar_gudang_stok,
                (float) $row->switching_out,
                (float) $row->switching_in,
                (float) $row->adjustment,
                (float) $row->saldo_akhir,
                $row->status,
            ];

            $sheet->writeRow(
                $rows,
                [
                    'border' => 'thin',
                ]
            );
        }

        foreach (range('A', 'Z') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}