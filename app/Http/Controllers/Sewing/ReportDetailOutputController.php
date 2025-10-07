<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Summary\MasterPlanSB;
use App\Exports\Sewing\ExportReportDetailOutputData;
use App\Exports\Sewing\ExportReportDetailOutputDataPacking;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ReportDetailOutputController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        $dataDetailProduksiDay = MasterPlanSB::on("mysql_sb")->
            selectRaw("
                master_plan.tgl_plan,
                master_plan.id_ws,
                so_det.id so_det_id,
                GROUP_CONCAT(DISTINCT REPLACE(master_plan.sewing_line, 'line_', '') ORDER BY master_plan.sewing_line ASC SEPARATOR '/') sewing_line,
                MAX(act_costing.kpno) no_ws,
                MAX(act_costing.styleno) no_style,
                MAX(so_det.color) color,
                MAX(so_det.size) size,
                MAX(mastersupplier.Supplier) nama_buyer
            ")
            ->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")
            ->leftJoin("so", "so.id_cost", "=", "act_costing.id")
            ->leftJoin("so_det", "so_det.id_so", "=", "so.id")
            ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")
            ->groupBy(
                'master_plan.tgl_plan',
                'master_plan.id_ws',
                'act_costing.id',
                'so_det.id'
            );

        if ($request->ajax()) {
            return
            DataTables::eloquent($dataDetailProduksiDay)->
                addColumn('no_bon', function($row) {
                    return "-";
                })->
                addColumn('output', function($row) {
                    $output = MasterPlanSB::on('mysql_sb')->
                        selectRaw('
                            count(output_rfts.id) output,
                            output_rfts.so_det_id,
                            master_plan.tgl_plan,
                            master_plan.id_ws
                        ')->
                        leftJoin('output_rfts', 'output_rfts.master_plan_id', '=', 'master_plan.id')->
                        where('master_plan.tgl_plan', $row->tgl_plan)->
                        where('master_plan.id_ws', $row->id_ws)->
                        where('output_rfts.so_det_id', $row->so_det_id)->
                        groupBy('master_plan.tgl_plan', 'master_plan.id_ws', 'output_rfts.so_det_id')->
                        first();

                    return $output ? $output->output : 0;
                })->
                filter(function ($query) {
                    if (request()->has('date') && request('date') != '') {
                        $query->where('master_plan.tgl_plan', request('date'));
                    }
                }, true)->
                filterColumn('no_ws', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(act_costing.kpno as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('nama_buyer', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(master_buyer.nama_buyer as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('no_style', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(act_costing.styleno as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('color', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(so_det.color as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('size', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(so_det.size as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                order(function ($query) {
                    $query->orderBy('master_plan.tgl_plan', 'desc')->orderBy('act_costing.kpno', 'asc')->orderBy('act_costing.styleno', 'asc');
                })->
                rawColumns(['order_cfm_price', 'earning', 'cumulative_earning'])->toJson();
        }

        return view('sewing.report.report-detail-output', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'reportDetailOutput', 'months' => $months, 'years' => $years]);
    }

    public function packing(Request $request)
    {
        $months = [['angka' => 1,'nama' => 'Januari'],['angka' => 2,'nama' => 'Februari'],['angka' => 3,'nama' => 'Maret'],['angka' => 4,'nama' => 'April'],['angka' => 5,'nama' => 'Mei'],['angka' => 6,'nama' => 'Juni'],['angka' => 7,'nama' => 'Juli'],['angka' => 8,'nama' => 'Agustus'],['angka' => 9,'nama' => 'September'],['angka' => 10,'nama' => 'Oktober'],['angka' => 11,'nama' => 'November'],['angka' => 12,'nama' => 'Desember']];
        $years = array_reverse(range(1999, date('Y')));

        $dataDetailProduksiDay = MasterPlanSB::on("mysql_sb")->
            selectRaw("
                master_plan.tgl_plan,
                master_plan.id_ws,
                so_det.id so_det_id,
                GROUP_CONCAT(DISTINCT REPLACE(master_plan.sewing_line, 'line_', '') ORDER BY master_plan.sewing_line ASC SEPARATOR '/') sewing_line,
                MAX(act_costing.kpno) no_ws,
                MAX(act_costing.styleno) no_style,
                MAX(so_det.color) color,
                MAX(so_det.size) size,
                MAX(mastersupplier.Supplier) nama_buyer
            ")
            ->leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")
            ->leftJoin("so", "so.id_cost", "=", "act_costing.id")
            ->leftJoin("so_det", "so_det.id_so", "=", "so.id")
            ->leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")
            ->groupBy(
                'master_plan.tgl_plan',
                'master_plan.id_ws',
                'act_costing.id',
                'so_det.id'
            );

        if ($request->ajax()) {
            return
            DataTables::eloquent($dataDetailProduksiDay)->
                addColumn('no_bon', function($row) {
                    return "-";
                })->
                addColumn('output', function($row) {
                    $output = MasterPlanSB::on('mysql_sb')->
                        selectRaw('
                            count(output_rfts_packing.id) output,
                            output_rfts_packing.so_det_id,
                            master_plan.tgl_plan,
                            master_plan.id_ws
                        ')->
                        leftJoin('output_rfts_packing', 'output_rfts_packing.master_plan_id', '=', 'master_plan.id')->
                        where('master_plan.tgl_plan', $row->tgl_plan)->
                        where('master_plan.id_ws', $row->id_ws)->
                        where('output_rfts_packing.so_det_id', $row->so_det_id)->
                        groupBy('master_plan.tgl_plan', 'master_plan.id_ws', 'output_rfts_packing.so_det_id')->
                        first();

                    return $output ? $output->output : 0;
                })->
                filter(function ($query) {
                    if (request()->has('date') && request('date') != '') {
                        $query->where('master_plan.tgl_plan', request('date'));
                    }
                }, true)->
                filterColumn('no_ws', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(act_costing.kpno as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('nama_buyer', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(master_buyer.nama_buyer as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('no_style', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(act_costing.styleno as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('color', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(so_det.color as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('size', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(so_det.size as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                order(function ($query) {
                    $query->orderBy('master_plan.tgl_plan', 'desc')->orderBy('act_costing.kpno', 'asc')->orderBy('act_costing.styleno', 'asc');
                })->
                rawColumns(['order_cfm_price', 'earning', 'cumulative_earning'])->toJson();
        }

        return view('report.report-detail-output', ['parentPage' => 'report', 'page' => 'dashboard-sewing-eff', 'months' => $months, 'years' => $years]);
    }

    public function exportData(Request $request) {
        ini_set('max_execution_time', 3600);

        $periode = $request->periode ? $request->periode : "monthly";
        $tanggal = $request->tanggal ? $request->tanggal : date("Y-m-d");

        return Excel::download(new ExportReportDetailOutputData($periode, $tanggal), 'detail_output_report.xlsx');
    }

    public function exportDataPacking(Request $request) {
        ini_set('max_execution_time', 3600);

        $periode = $request->periode ? $request->periode : "monthly";
        $tanggal = $request->tanggal ? $request->tanggal : date("Y-m-d");

        return Excel::download(new ExportReportDetailOutputDataPacking($periode, $tanggal), 'detail_output_packing_report.xlsx');
    }
}
