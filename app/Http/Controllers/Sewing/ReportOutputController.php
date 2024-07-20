<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Summary\DataDetailProduksiDay;
use App\Exports\ExportReportOutput;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ReportOutputController extends Controller
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

        $dataDetailProduksiDay = DataDetailProduksiDay::selectRaw("
                data_detail_produksi_day.tgl_produksi,
                data_produksi.tanggal_delivery,
                data_produksi.no_ws,
                data_produksi.no_style,
                data_produksi.kode_mata_uang,
                data_produksi.order_cfm_price,
                (CASE WHEN data_produksi.order_qty_cutting <= 0 OR data_produksi.order_qty_cutting IS NULL THEN data_produksi.order_qty ELSE data_produksi.order_qty_cutting END) order_qty_cutting,
                STRING_AGG(DISTINCT master_buyer.nama_buyer, ', ') nama_buyer,
                AVG(data_detail_produksi_day.smv) smv,
                SUM(data_detail_produksi_day.output)  output,
                SUM(data_detail_produksi_day.cumulative_output) cumulative_output,
                (SUM(data_detail_produksi_day.cumulative_output)-SUM(data_detail_produksi_day.output)) before_output,
                ((CASE WHEN data_produksi.order_qty_cutting <= 0 OR data_produksi.order_qty_cutting IS NULL THEN data_produksi.order_qty ELSE data_produksi.order_qty_cutting END) - (SUM(data_detail_produksi_day.cumulative_output) - SUM(data_detail_produksi_day.output))) before_balance,
                ((CASE WHEN data_produksi.order_qty_cutting <= 0 OR data_produksi.order_qty_cutting IS NULL THEN data_produksi.order_qty ELSE data_produksi.order_qty_cutting END) - (SUM(data_detail_produksi_day.cumulative_output) - SUM(data_detail_produksi_day.output)) - SUM(data_detail_produksi_day.output)) cumulative_balance,
                SUM(data_detail_produksi_day.earning) earning,
                SUM(data_detail_produksi_day.cumulative_earning) cumulative_earning
            ")
            ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
            ->leftJoin("data_produksi", "data_produksi.id", "=", "data_detail_produksi.data_produksi_id")
            ->leftJoin("master_buyer", "master_buyer.id", "=", "data_produksi.buyer_id")
            ->groupBy("data_produksi.id", "data_detail_produksi_day.tgl_produksi");

        if ($request->ajax()) {
            return
            DataTables::eloquent($dataDetailProduksiDay)->
                addColumn('smv', function($row) {
                    return num($row->smv,2);
                })->
                addColumn('order_cfm_price', function($row) {
                    return '<span class="'.($row->order_cfm_price <= 0 ? "text-danger" : "").'">'.$row->kode_mata_uang.' '.curr($row->order_cfm_price).'</span>';
                })->
                addColumn('earning', function($row) {
                    return '<span class="'.($row->order_cfm_price <= 0 ? "text-danger" : "").'">'.$row->kode_mata_uang.' '.curr($row->earning).'</span>';
                })->
                addColumn('cumulative_earning', function($row) {
                    return '<span class="'.($row->order_cfm_price <= 0 ? "text-danger" : "").'">'.$row->kode_mata_uang.' '.curr($row->cumulative_earning).'</span>';
                })->
                filter(function ($query) {
                    if (request()->has('date') && request('date') != '') {
                        $query->where('data_detail_produksi_day.tgl_produksi', "=", request('date'));
                    }
                }, true)->
                filterColumn('no_ws', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(data_produksi.no_ws as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('nama_buyer', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(master_buyer.nama_buyer as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('no_style', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(data_produksi.no_style as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('tanggal_delivery', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(data_produksi.tanggal_delivery as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                order(function ($query) {
                    $query->orderBy('data_detail_produksi_day.tgl_produksi', 'desc')->orderBy('data_produksi.no_ws', 'asc')->orderBy('data_produksi.no_style', 'asc');
                })->
                rawColumns(['order_cfm_price', 'earning', 'cumulative_earning'])->toJson();
        }

        return view('sewing.report.report-output', ['parentPage' => 'report', 'page' => 'dashboard-sewing-eff', 'months' => $months, 'years' => $years]);
    }

    public function exportData(Request $request) {
        ini_set('max_execution_time', 3600);

        $periode = $request->periode ? $request->periode : "monthly";
        $tanggal = $request->tanggal ? $request->tanggal : date("Y-m-d");

        return Excel::download(new ExportReportOutput($periode, $tanggal), 'output_report.xlsx');
    }
}
