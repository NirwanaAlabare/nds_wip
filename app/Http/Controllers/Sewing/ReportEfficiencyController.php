<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Summary\DataDetailProduksiDay;
use App\Exports\ExportReportEfficiency;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ReportEfficiencyController extends Controller
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
                data_detail_produksi.sewing_line,
                data_produksi.no_ws,
                master_buyer.nama_buyer,
                data_produksi.no_style,
                data_detail_produksi_day.smv,
                data_detail_produksi_day.man_power,
                data_detail_produksi_day.mins_avail,
                data_detail_produksi_day.target,
                data_detail_produksi_day.output,
                data_detail_produksi_day.output_rft,
                data_produksi.kode_mata_uang,
                data_produksi.order_cfm_price,
                data_produksi.order_cfm_price_dollar,
                data_produksi.order_cfm_price_rupiah,
                data_detail_produksi_day.earning,
                data_detail_produksi_day.efficiency,
                data_detail_produksi_day.mins_prod,
                (summary_line.mins_prod_line/summary_line.mins_avail_line * 100) line_efficiency,
                data_detail_produksi_day.jam_aktual,
                data_detail_produksi_day.tgl_produksi,
                summary_line.tgl_produksi_line,
                summary_line.mins_avail_line,
                summary_line.mins_prod_line
            ")
            ->leftJoin("data_detail_produksi", "data_detail_produksi.id", "=", "data_detail_produksi_day.data_detail_produksi_id")
            ->leftJoin("data_produksi","data_produksi.id","=","data_detail_produksi.data_produksi_id")
            ->leftJoin("master_buyer","master_buyer.id","=","data_produksi.buyer_id")
            ->leftJoin(
                DB::raw("
                    (
                        SELECT
                            data_detail_produksi.sewing_line,
                            data_detail_produksi_day.tgl_produksi as tgl_produksi_line,
                            SUM(data_detail_produksi_day.mins_prod) as mins_prod_line,
                            SUM(data_detail_produksi_day.mins_avail) as mins_avail_line
                        FROM
                            data_detail_produksi_day
                        LEFT JOIN
                            data_detail_produksi
                        ON
                            data_detail_produksi.id = data_detail_produksi_day.data_detail_produksi_id
                        GROUP BY
                            data_detail_produksi.sewing_line, data_detail_produksi_day.tgl_produksi
                    ) summary_line
                "),
                "summary_line.sewing_line", "=", "data_detail_produksi.sewing_line"
            );

        if ($request->ajax()) {
            return
            DataTables::eloquent($dataDetailProduksiDay)->
                addColumn('rft_rate', function($row) {
                    return num(($row->output ? (($row->output_rft/$row->output)*100) : 0),2)." %";
                })->
                addColumn('efficiency', function($row) {
                    return num($row->efficiency,2)." %";
                })->
                addColumn('line_efficiency', function($row) {
                    return num($row->line_efficiency,2)." %";
                })->
                addColumn('smv', function($row) {
                    return num($row->smv,2);
                })->
                addColumn('mins_prod', function($row) {
                    return num($row->mins_prod,2);
                })->
                addColumn('mins_avail', function($row) {
                    return num($row->mins_avail,2);
                })->
                addColumn('order_cfm_price', function($row) {
                    return '<span class="'.($row->order_cfm_price <= 0 ? "text-danger" : "").'">'.$row->kode_mata_uang.' '.curr($row->order_cfm_price).'</span>';
                })->
                addColumn('earning', function($row) {
                    return $row->kode_mata_uang.' '.curr($row->earning);
                })->
                filter(function ($query) {
                    if (request()->has('date') && request('date') != '') {
                        $query->where('data_detail_produksi_day.tgl_produksi', "=", request('date'))
                            ->where('summary_line.tgl_produksi_line', "=", request('date'));
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
                order(function ($query) {
                    $query->orderBy('data_detail_produksi_day.tgl_produksi', 'desc')->orderBy('data_detail_produksi.sewing_line', 'asc');
                })->
                rawColumns(['order_cfm_price', 'earning'])->toJson();
        }

        return view('sewing.report.report-efficiency', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'reportEfficiency', 'months' => $months, 'years' => $years]);
    }

    public function exportData(Request $request) {
        ini_set('max_execution_time', 3600);

        $periode = $request->periode ? $request->periode : "monthly";
        $tanggal = $request->tanggal ? $request->tanggal : date("Y-m-d");

        return Excel::download(new ExportReportEfficiency($periode, $tanggal), 'efficiency_report.xlsx');
    }
}
