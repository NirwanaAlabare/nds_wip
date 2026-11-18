<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \avadim\FastExcelLaravel\Excel as FastExcel;

/**
 * Report Mutasi In Transit Fabric
 *
 * Fabric "in transit" = sudah dikeluarkan gudang (BPPB tujuan Production - Cutting)
 * tetapi belum diterima di cutting (penerimaan_cutting), per roll.
 *
 * Saldo Awal  = saldo rekap terakhir (mutasi_in_transit_fabric_rekap) sebelum tanggal awal
 *               + keluar gudang - terima cutting antara tanggal rekap s/d sehari sebelum tanggal awal.
 * Keluar      = BPPB gudang ke cutting pada periode.
 * Terima      = penerimaan cutting pada periode.
 * Saldo Akhir = Saldo Awal + Keluar - Terima.
 */
class ReportMutasiInTransitFabricController extends Controller
{
    /**
     * Tanggal mulai berlakunya modul penerimaan cutting.
     * Transaksi sebelum tanggal ini tidak dihitung sebagai mutasi in transit.
     */
    const START_DATE = '2026-10-01';

    /**
     * Tanggal rekap (snapshot saldo) terakhir sebelum tanggal awal periode.
     */
    private function getRekapDate($dateFrom)
    {
        return DB::table('mutasi_in_transit_fabric_rekap')
            ->where('tanggal', '<', $dateFrom)
            ->max('tanggal');
    }

    /**
     * Query mutasi in transit fabric.
     * Mengembalikan [sql, bindings].
     */
    public function buildQuery($dateFrom, $dateTo)
    {
        $rekapDate = $this->getRekapDate($dateFrom);

        // Batas bawah mutasi saldo awal : setelah tanggal rekap (atau sejak START_DATE bila belum ada rekap)
        $awalLowerOperator = $rekapDate ? '>' : '>=';
        $awalLowerDate = $rekapDate ?: self::START_DATE;

        $sql = "
            WITH
            rekap AS (
                SELECT
                    id_roll,
                    id_item,
                    MAX(itemdesc) itemdesc,
                    COALESCE(satuan_out, satuan_in) satuan,
                    COALESCE(satuan_out_konv, satuan_in_konv) satuan_konv,
                    SUM(qty_out) - SUM(qty_in) saldo_akhir,
                    SUM(qty_out_konv) - SUM(qty_in_konv) saldo_akhir_konv
                FROM mutasi_in_transit_fabric_rekap
                WHERE tanggal = ?
                GROUP BY
                    id_roll,
                    id_item,
                    COALESCE(satuan_out, satuan_in),
                    COALESCE(satuan_out_konv, satuan_in_konv)
            ),

            gudang_out_all AS (
                SELECT
                    a.tgl_bppb tanggal,
                    b.id_roll,
                    mi.id_item,
                    mi.itemdesc,
                    b.qty_out,
                    b.satuan,
                    CASE WHEN b.satuan = 'YRD' THEN ROUND(b.qty_out * 0.9144, 2) ELSE b.qty_out END qty_out_konv,
                    CASE WHEN b.satuan = 'YRD' THEN 'METER' ELSE b.satuan END satuan_konv
                FROM signalbit_erp.whs_bppb_h a
                INNER JOIN signalbit_erp.whs_bppb_det b ON a.no_bppb = b.no_bppb
                INNER JOIN signalbit_erp.masteritem mi ON b.id_item = mi.id_item
                WHERE
                    a.tgl_bppb >= ? AND a.tgl_bppb <= ?
                    AND a.tujuan = 'Production - Cutting'
                    AND b.status = 'Y'
            ),

            cutting_in_all AS (
                SELECT
                    a.tanggal_terima tanggal,
                    a.id_roll,
                    mi.id_item,
                    mi.itemdesc,
                    b.qty_out qty_in,
                    b.satuan,
                    a.qty_konv qty_in_konv,
                    a.unit_konv satuan_konv
                FROM penerimaan_cutting a
                LEFT JOIN signalbit_erp.whs_bppb_det b ON b.id = a.whs_bppb_det_id
                LEFT JOIN signalbit_erp.masteritem mi ON b.id_item = mi.id_item
                WHERE
                    a.tanggal_terima >= ? AND a.tanggal_terima <= ?
            ),

            mutasi AS (
                -- Saldo rekap
                SELECT
                    id_roll, id_item, itemdesc, satuan, satuan_konv,
                    saldo_akhir saldo_awal, saldo_akhir_konv saldo_awal_konv,
                    0 qty_out, 0 qty_out_konv,
                    0 qty_in, 0 qty_in_konv
                FROM rekap

                UNION ALL

                -- Keluar gudang antara rekap s/d sebelum tanggal awal (masuk saldo awal)
                SELECT
                    id_roll, id_item, MAX(itemdesc), satuan, satuan_konv,
                    SUM(qty_out), SUM(qty_out_konv),
                    0, 0,
                    0, 0
                FROM gudang_out_all
                WHERE tanggal {$awalLowerOperator} ? AND tanggal < ?
                GROUP BY id_roll, id_item, satuan, satuan_konv

                UNION ALL

                -- Terima cutting antara rekap s/d sebelum tanggal awal (mengurangi saldo awal)
                SELECT
                    id_roll, id_item, MAX(itemdesc), satuan, satuan_konv,
                    -SUM(qty_in), -SUM(qty_in_konv),
                    0, 0,
                    0, 0
                FROM cutting_in_all
                WHERE tanggal {$awalLowerOperator} ? AND tanggal < ?
                GROUP BY id_roll, id_item, satuan, satuan_konv

                UNION ALL

                -- Keluar gudang periode berjalan
                SELECT
                    id_roll, id_item, MAX(itemdesc), satuan, satuan_konv,
                    0, 0,
                    SUM(qty_out), SUM(qty_out_konv),
                    0, 0
                FROM gudang_out_all
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY id_roll, id_item, satuan, satuan_konv

                UNION ALL

                -- Terima cutting periode berjalan
                SELECT
                    id_roll, id_item, MAX(itemdesc), satuan, satuan_konv,
                    0, 0,
                    0, 0,
                    SUM(qty_in), SUM(qty_in_konv)
                FROM cutting_in_all
                WHERE tanggal BETWEEN ? AND ?
                GROUP BY id_roll, id_item, satuan, satuan_konv
            )

            SELECT
                id_roll,
                id_item,
                MAX(itemdesc) itemdesc,
                satuan,
                ROUND(SUM(saldo_awal), 2) saldo_awal,
                ROUND(SUM(qty_out), 2) qty_out,
                ROUND(SUM(qty_in), 2) qty_in,
                ROUND(SUM(saldo_awal) + SUM(qty_out) - SUM(qty_in), 2) saldo_akhir,
                satuan_konv,
                ROUND(SUM(saldo_awal_konv), 2) saldo_awal_konv,
                ROUND(SUM(qty_out_konv), 2) qty_out_konv,
                ROUND(SUM(qty_in_konv), 2) qty_in_konv,
                ROUND(SUM(saldo_awal_konv) + SUM(qty_out_konv) - SUM(qty_in_konv), 2) saldo_akhir_konv
            FROM mutasi
            GROUP BY id_roll, id_item, satuan, satuan_konv
            HAVING
                ROUND(SUM(saldo_awal), 2) <> 0
                OR ROUND(SUM(qty_out), 2) <> 0
                OR ROUND(SUM(qty_in), 2) <> 0
            ORDER BY id_item, id_roll
        ";

        // Rentang data mentah : dari batas bawah saldo awal s/d tanggal akhir
        $rawFrom = max($awalLowerDate, self::START_DATE);

        $bindings = [
            $rekapDate,                         // rekap
            $rawFrom, $dateTo,                  // gudang_out_all
            $rawFrom, $dateTo,                  // cutting_in_all
            $awalLowerDate, $dateFrom,          // gudang out saldo awal
            $awalLowerDate, $dateFrom,          // cutting in saldo awal
            $dateFrom, $dateTo,                 // gudang out periode
            $dateFrom, $dateTo,                 // cutting in periode
        ];

        return [$sql, $bindings];
    }

    /**
     * View dan data (ajax).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom ?: date('Y-m-01');
            $dateTo = $request->dateTo ?: date('Y-m-d');

            [$sql, $bindings] = $this->buildQuery($dateFrom, $dateTo);

            return response()->json([
                'data' => DB::select($sql, $bindings),
            ]);
        }

        return view('cutting.report.mutasi-in-transit-fabric', [
            'page' => 'dashboard-cutting',
            'subPageGroup' => 'cutting-report',
            'subPage' => 'mutasi-in-transit-fabric',
            'containerFluid' => true,
        ]);
    }

    /**
     * Export Excel (FastExcel).
     */
    public function export(Request $request)
    {
        ini_set('max_execution_time', 3600);
        ini_set('memory_limit', '2048M');

        $dateFrom = $request->dateFrom ?: date('Y-m-01');
        $dateTo = $request->dateTo ?: date('Y-m-d');

        [$sql, $bindings] = $this->buildQuery($dateFrom, $dateTo);
        $data = collect(DB::select($sql, $bindings));

        // Terapkan filter kolom dari thead (sama dengan yang tampil di tabel)
        $filters = array_filter((array) $request->input('filters', []), fn ($value) => $value !== null && $value !== '');

        if (count($filters) > 0) {
            $data = $data->filter(function ($row) use ($filters) {
                foreach ($filters as $column => $keyword) {
                    if (!property_exists($row, $column)) {
                        continue;
                    }

                    if (stripos((string) $row->{$column}, (string) $keyword) === false) {
                        return false;
                    }
                }

                return true;
            });
        }

        $numericKeys = [
            'saldo_awal', 'qty_out', 'qty_in', 'saldo_akhir',
            'saldo_awal_konv', 'qty_out_konv', 'qty_in_konv', 'saldo_akhir_konv',
        ];

        $totals = [];
        foreach ($numericKeys as $key) {
            $totals[$key] = round($data->sum(fn ($row) => (float) $row->{$key}), 2);
        }

        $excel = FastExcel::create('mutasi-in-transit-fabric');
        $sheet = $excel->sheet();

        $numberFormat = ['format' => '#,##0.00'];
        $blueFill = ['fill-color' => '#9BC2E6'];
        $greenFill = ['fill-color' => '#92D050'];

        // Baris 1 : periode
        $sheet->writeRow(['Periode ' . $dateFrom . ' s/d ' . $dateTo], ['font-style' => 'italic']);

        // Baris 2 : header (kolom satuan asli biru, kolom konversi hijau)
        $sheet->writeRow(
            [
                'Barcode', 'ID Item', 'Item Name',
                'Satuan', 'Saldo Awal', 'Pemasukan', 'Pengeluaran', 'Saldo Akhir',
                'Satuan Konv', 'Saldo Awal', 'Pemasukan Konv', 'Pengeluaran Konv', 'Saldo Akhir Konv',
            ],
            ['font-style' => 'bold', 'border' => 'thin'],
            [
                3 => $blueFill, 4 => $blueFill, 5 => $blueFill, 6 => $blueFill, 7 => $blueFill,
                8 => $greenFill, 9 => $greenFill, 10 => $greenFill, 11 => $greenFill, 12 => $greenFill,
            ]
        );

        foreach ($data as $row) {
            $sheet->writeRow([
                $row->id_roll ?? '',
                $row->id_item ?? '',
                $row->itemdesc ?? '',
                $row->satuan ?? '',
                (float) $row->saldo_awal,
                (float) $row->qty_out,
                (float) $row->qty_in,
                (float) $row->saldo_akhir,
                $row->satuan_konv ?? '',
                (float) $row->saldo_awal_konv,
                (float) $row->qty_out_konv,
                (float) $row->qty_in_konv,
                (float) $row->saldo_akhir_konv,
            ], ['border' => 'thin']);
        }

        // Baris total di bawah data
        $sheet->writeRow(
            [
                'Total', '', '', '',
                $totals['saldo_awal'], $totals['qty_out'], $totals['qty_in'], $totals['saldo_akhir'],
                '',
                $totals['saldo_awal_konv'], $totals['qty_out_konv'], $totals['qty_in_konv'], $totals['saldo_akhir_konv'],
            ],
            ['font-style' => 'bold', 'border' => 'thin'],
            [4 => $numberFormat, 5 => $numberFormat, 6 => $numberFormat, 7 => $numberFormat, 9 => $numberFormat, 10 => $numberFormat, 11 => $numberFormat, 12 => $numberFormat]
        );

        $sheet->setFreezeRows(2);

        $sheet->setColWidth('A', 12);
        $sheet->setColWidth('B', 10);
        $sheet->setColWidth('C', 40);
        $sheet->setColWidth('D', 9);
        foreach (range('E', 'M') as $col) {
            $sheet->setColWidth($col, 18);
        }
        $sheet->setColWidth('I', 12);

        return $excel->download('Mutasi In Transit Fabric ' . $dateFrom . ' - ' . $dateTo . '.xlsx');
    }
}
