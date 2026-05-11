<?php

namespace App\Http\Controllers;

use App\Exports\Packing\ExportReportOutputPackingLine;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportOutputPackingLineController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;
            $tipe = strtolower($request->tipe);
            $buyer = $request->buyer;

            if($tipe != ''){
                $data = DB::table(DB::raw("
                    (
                        select so_det_id, buyer, ws, styleno, color, size, type, tgl, SUM(jumlah) jumlah from (SELECT
                            so_det_id,
                            mb.buyer,
                            mb.ws,
                            mb.styleno,
                            mb.color,
                            mb.size,
                            a.type,
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
                        GROUP BY so_det_id, a.type, DATE(created_at)
                                                
                                                UNION ALL
                                                select '-' so_det_id, buyer, ws, styleno, color, size, 'rft' type, tgl_saldo tgl, COALESCE(packing_rft,0) jumlah from signalbit_erp.inject_mutasi_sewing where type_saldo = 'FINISHING' and tgl_saldo >= '{$tglAwal} 00:00:00' AND tgl_saldo <= '{$tglAkhir} 23:59:59') a GROUP BY buyer, ws, styleno, color, size, type, tgl
                    ) as results
                "))
                ->when($tipe, function ($query) use ($tipe) {
                    return $query->where('results.type', $tipe);
                })
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });
            }else {
                $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
            }

            return DataTables::queryBuilder($data)->make(true);
        }

        $buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view("packing.report_output_packing_line", [
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-report",
            "subPage" => "report-output-packing-line",
            'containerFluid' => true,
            "buyer" => $buyer
        ]);
    }

    public function export(Request $request) {
        $from = $request->from;
        $to = $request->to;
        $tipe = $request->tipe;
        $buyer = $request->buyer;

        return Excel::download(new ExportReportOutputPackingLine($from, $to, $tipe, $buyer), 'report-output-packing-line.xlsx');
    }
}