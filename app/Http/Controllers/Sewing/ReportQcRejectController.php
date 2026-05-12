<?php

namespace App\Http\Controllers\Sewing;

use App\Exports\Sewing\ExportReportQcReject;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportQcRejectController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $kategori = $request->kategori;
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;
            $buyer = $request->buyer;

            if ($kategori == 'TERIMA') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            so_det_id,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            DATE(a.created_at) AS tgl,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_reject_in a
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = a.master_plan_id
                        LEFT JOIN (
                            SELECT
                            sd.id as id_so_det,
                            ac.kpno as ws,
                            supplier as buyer,
                            styleno,
                            color,
                            size,
                            dest
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on a.so_det_id = mb.id_so_det
                        WHERE
                            a.created_at >= '{$tglAwal} 00:00:00'
                            AND a.created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                        GROUP BY so_det_id, DATE(a.created_at)
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else if ($kategori == 'KELUAR GOOD') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            so_det_id,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            DATE(a.created_at) AS tgl,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_reject_out_detail a
                        INNER JOIN signalbit_erp.output_reject_in b ON b.id = a.reject_in_id
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = b.master_plan_id
                        LEFT JOIN (
                            SELECT
                            sd.id as id_so_det,
                            ac.kpno as ws,
                            supplier as buyer,
                            styleno,
                            color,
                            size,
                            dest
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on b.so_det_id = mb.id_so_det
                        WHERE
                            a.created_at >= '{$tglAwal} 00:00:00'
                            AND a.created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                            AND b.status = 'reworked'
                        GROUP BY so_det_id, DATE(a.created_at)
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else if ($kategori == 'KELUAR REJECT') {

                $data = DB::table(DB::raw("
                    (
                        SELECT
                            MAX(buyer) buyer,
                            ws,
                            styleno,
                            color,
                            size,
                            tgl,
                            SUM(jumlah) AS jumlah
                        FROM
                        (
                            SELECT
                                so_det_id,
                                mb.buyer buyer,
                                mb.ws,
                                mb.styleno,
                                mb.color,
                                mb.size,
                                DATE(a.created_at) AS tgl,
                                COUNT(*) AS jumlah
                            FROM signalbit_erp.output_reject_out_detail a
                            INNER JOIN signalbit_erp.output_reject_in b ON b.id = a.reject_in_id
                            INNER JOIN signalbit_erp.output_reject_out c ON c.id = a.reject_out_id
                            INNER JOIN signalbit_erp.master_plan mp ON mp.id = b.master_plan_id
                            LEFT JOIN (
                                SELECT
                                sd.id as id_so_det,
                                ac.kpno as ws,
                                supplier as buyer,
                                styleno,
                                color,
                                size,
                                dest
                                FROM signalbit_erp.so_det sd
                                INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                                WHERE jd.cancel = 'N'
                            ) mb on b.so_det_id = mb.id_so_det
                            WHERE
                                c.tanggal >= '{$tglAwal}'
                                AND c.tanggal <= '{$tglAkhir}'
                                AND b.status = 'rejected'
                                AND mp.cancel = 'N'
                            GROUP BY so_det_id, ws, color, size, DATE(c.tanggal)

                            UNION ALL

                            SELECT
                                null so_det_id,
                                null buyer,
                                ws,
                                styleno,
                                color,
                                size,
                                tgl_saldo AS tgl,
                                SUM(COALESCE(qty_rejected, 0)) AS jumlah
                            FROM signalbit_erp.inject_mutasi_sewing
                            WHERE buyer != '-' AND tgl_saldo >= '{$tglAwal}' AND tgl_saldo <= '{$tglAkhir}' and qty_rejected > 0
                            GROUP BY
                                ws,
                                color,
                                size,
                                tgl_saldo
                        ) as results
                        GROUP BY
                            ws,
                            color,
                            size
                    ) results
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

        return view("sewing.report.report_qc_reject", [
            'page' => 'dashboard-sewing-eff',
            "subPageGroup" => "sewing-report",
            "subPage" => "report-qc-reject",
            'containerFluid' => true,
            "buyer" => $buyer
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $kategori = $request->kategori;
        $buyer = $request->buyer;

        return Excel::download(new ExportReportQcReject($from, $to, $kategori, $buyer), 'report-qc-reject.xlsx');
    }
}
