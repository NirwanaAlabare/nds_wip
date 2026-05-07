<?php

namespace App\Http\Controllers\Sewing;

use App\Exports\Sewing\ExportReportPacking;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportPackingController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;
            $buyer = $request->buyer;

            $data = DB::table(DB::raw("
                (
                    SELECT
                        so_det_id,
                        mb.buyer,
                        mb.ws,
                        mb.styleno,
                        mb.color,
                        mb.size,
                        DATE(created_at) AS tgl,
                        COUNT(*) AS jumlah
                    FROM signalbit_erp.output_rfts_packing_po a
                    INNER JOIN signalbit_erp.master_plan mp ON a.master_plan_id = mp.id
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
                        created_at >= '{$tglAwal} 00:00:00'
                        AND created_at <= '{$tglAkhir} 23:59:59'
                        AND mp.cancel = 'N'
                    GROUP BY so_det_id, DATE(created_at)
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            });

            return DataTables::queryBuilder($data)->make(true);
        }

        $buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view("sewing.report.report_packing", [
            'page' => 'dashboard-sewing-eff',
            "subPageGroup" => "sewing-report",
            "subPage" => "report-packing",
            'containerFluid' => true,
            "buyer" => $buyer
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $buyer = $request->buyer;

        return Excel::download(new ExportReportPacking($from, $to, $buyer), 'report-finishing.xlsx');
    }
}