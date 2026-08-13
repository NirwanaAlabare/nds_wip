<?php

namespace App\Http\Controllers\Sewing;

use App\Exports\Sewing\ExportReportFinishing;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportFinishingController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $kategori = $request->kategori;
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;
            $buyer = $request->buyer;
            $proses = $request->proses;

            if ($kategori == 'TERIMA') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            master.secondary AS proses,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_secondary_in a
                        INNER JOIN signalbit_erp.output_rfts output ON output.id = a.rft_id
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id
                        INNER JOIN signalbit_erp.output_secondary_master master ON master.id = a.secondary_id
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
                        ) mb on output.so_det_id = mb.id_so_det
                        WHERE
                            a.created_at >= '{$tglAwal} 00:00:00'
                            AND a.created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                        GROUP BY
                            master.secondary,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                })
                ->when($proses, function ($query) use ($proses) {
                    return $query->where('results.proses', $proses);
                });

            } else if ($kategori == 'DEFECT') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            master.secondary AS proses,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_secondary_out_defect a
                        INNER JOIN signalbit_erp.output_secondary_out b ON b.id = a.secondary_out_id
                        INNER JOIN signalbit_erp.output_secondary_in c ON c.id = b.secondary_in_id
                        INNER JOIN signalbit_erp.output_rfts output ON output.id = c.rft_id
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id
                        INNER JOIN signalbit_erp.output_secondary_master master ON master.id = c.secondary_id
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
                        ) mb on output.so_det_id = mb.id_so_det
                        WHERE
                            a.created_at >= '{$tglAwal} 00:00:00'
                            AND a.created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                        GROUP BY
                            master.secondary,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                })
                ->when($proses, function ($query) use ($proses) {
                    return $query->where('results.proses', $proses);
                });

            } else if ($kategori == 'REWORK') {

                $data = DB::table(DB::raw("
                    (
                        SELECT
                            master.secondary AS proses,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_secondary_out_defect a
                        INNER JOIN signalbit_erp.output_secondary_out b ON b.id = a.secondary_out_id
                        INNER JOIN signalbit_erp.output_secondary_in c ON c.id = b.secondary_in_id
                        INNER JOIN signalbit_erp.output_rfts output ON output.id = c.rft_id
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id
                        INNER JOIN signalbit_erp.output_secondary_master master ON master.id = c.secondary_id
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
                        ) mb on output.so_det_id = mb.id_so_det
                        WHERE
                            a.reworked_at >= '{$tglAwal} 00:00:00'
                            AND a.reworked_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                            AND (a.status = 'reworked' OR a.status = 'rejected')
                        GROUP BY
                            master.secondary,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                })
                ->when($proses, function ($query) use ($proses) {
                    return $query->where('results.proses', $proses);
                });

            } else if ($kategori == 'REJECT') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            master.secondary AS proses,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_secondary_out_reject a
                        INNER JOIN signalbit_erp.output_secondary_out b ON b.id = a.secondary_out_id
                        INNER JOIN signalbit_erp.output_secondary_in c ON c.id = b.secondary_in_id
                        INNER JOIN signalbit_erp.output_rfts output ON output.id = c.rft_id
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id
                        INNER JOIN signalbit_erp.output_secondary_master master ON master.id = c.secondary_id
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
                        ) mb on output.so_det_id = mb.id_so_det
                        WHERE
                            a.created_at >= '{$tglAwal} 00:00:00'
                            AND a.created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                        GROUP BY
                            master.secondary,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                })
                ->when($proses, function ($query) use ($proses) {
                    return $query->where('results.proses', $proses);
                });

            } else if ($kategori == 'OUTPUT') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            master.secondary AS proses,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            COUNT(*) AS jumlah
                        FROM signalbit_erp.output_secondary_out a
                        INNER JOIN signalbit_erp.output_secondary_in b ON b.id = a.secondary_in_id
                        INNER JOIN signalbit_erp.output_rfts output ON output.id = b.rft_id
                        INNER JOIN signalbit_erp.master_plan mp ON mp.id = output.master_plan_id
                        INNER JOIN signalbit_erp.output_secondary_master master ON master.id = b.secondary_id
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
                        ) mb on output.so_det_id = mb.id_so_det
                        WHERE
                            a.created_at >= '{$tglAwal} 00:00:00'
                            AND a.created_at <= '{$tglAkhir} 23:59:59'
                            AND mp.cancel = 'N'
                            AND (a.status = 'rft' OR a.status = 'rework')
                        GROUP BY
                            master.secondary,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                })
                ->when($proses, function ($query) use ($proses) {
                    return $query->where('results.proses', $proses);
                });

            } else {
                $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
            }

            return DataTables::queryBuilder($data)->make(true);
        }

        $buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->whereNotNull('supplier')
            ->where('supplier', '!=', '')
            ->orderBy('supplier', 'ASC')
            ->get();

        $proses = DB::connection('mysql_sb')
            ->table('output_secondary_master')
            ->select('secondary')
            ->orderBy('id', 'ASC')
            ->get();

        return view("sewing.report.report_finishing", [
            'page' => 'dashboard-sewing-eff',
            "subPageGroup" => "sewing-report",
            "subPage" => "report-finishing",
            'containerFluid' => true,
            "buyer" => $buyer,
            "proses" => $proses,
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $kategori = $request->kategori;
        $buyer = $request->buyer;
        $proses = $request->proses;

        return Excel::download(new ExportReportFinishing($from, $to, $kategori, $buyer, $proses), 'report-finishing.xlsx');
    }
}
