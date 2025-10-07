<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Summary\DataDetailProduksiDay;
use App\Exports\Sewing\ExportReportProduction;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class ReportProductionController extends Controller
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
            chief.nama chief_name,
            leader.nama leader_name,
            admin.nama admin_name,
            data_detail_produksi_day.man_power,
            data_detail_produksi_day.mins_avail,
            data_detail_produksi_day.target,
            data_detail_produksi_day.output,
            data_produksi.order_cfm_price,
            data_produksi.kode_mata_uang,
            data_detail_produksi_day.earning,
            data_detail_produksi_day.efficiency,
            data_detail_produksi_day.mins_prod,
            data_detail_produksi_day.jam_aktual,
            summary_line.mins_prod_line,
            summary_line.mins_avail_line,
            data_detail_produksi_day.tgl_produksi
        ")
        ->leftJoin("data_detail_produksi","data_detail_produksi.id","=","data_detail_produksi_day.data_detail_produksi_id")
        ->leftJoin("data_produksi","data_produksi.id","=","data_detail_produksi.data_produksi_id")
        ->leftJoin("master_buyer","master_buyer.id","=","data_produksi.buyer_id")
        ->leftJoin("master_karyawan as chief","chief.id","=","data_detail_produksi_day.chief_enroll_id")
        ->leftJoin("master_karyawan as leader","leader.id","=","data_detail_produksi_day.leader_enroll_id")
        ->leftJoin("master_karyawan as admin","admin.id","=","data_detail_produksi_day.adm_enroll_id")
        ->leftJoin("master_kurs_bi","master_kurs_bi.id","=","data_detail_produksi_day.kurs_bi_id")
        ->leftJoin(
            DB::raw("
                (
                    SELECT
                        data_detail_produksi.sewing_line,
                        data_detail_produksi_day.tgl_produksi as summary_tanggal,
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
            "summary_line.sewing_line","=","data_detail_produksi.sewing_line"
        )
        ->whereRaw("data_detail_produksi_day.tgl_produksi = '".request('date')."' AND summary_line.summary_tanggal = '".request('date')."'")
        ->groupByRaw("data_detail_produksi_day.tgl_produksi, data_detail_produksi.sewing_line, data_produksi.id");

        if ($request->ajax()) {
            return
            DataTables::eloquent($dataDetailProduksiDay)->
                addColumn('smv', function($row) {
                    return num($row->smv,2);
                })->
                addColumn('efficiency', function($row) {
                    return num($row->efficiency,2)." %";
                })->
                addColumn('order_cfm_price', function($row) {
                    return '<span class="'.($row->order_cfm_price <= 0 ? "text-danger" : "").'">'.$row->kode_mata_uang.' '.curr($row->order_cfm_price).'</span>';
                })->
                filterColumn('sewing_line', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(data_detail_produksi.sewing_line as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('chief_name', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(chief.nama as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('leader_name', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(leader.nama as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('admin_name', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(admin.nama as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
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
                filterColumn('order_cfm_price', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(data_produksi.order_cfm_price as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                filterColumn('kode_mata_uang', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(data_produksi.kode_mata_uang as TEXT)) LIKE LOWER('%".$keyword."%')");
                })->
                order(function ($query) {
                    $query->orderBy('data_detail_produksi_day.tgl_produksi','DESC')
                        ->orderBy('data_detail_produksi.sewing_line','ASC')
                        ->orderBy('data_produksi.id','DESC');
                })->
                rawColumns(['order_cfm_price', 'earning', 'cumulative_earning'])->toJson();
        }

        return view('sewing.report.report-production', ['page' => 'dashboard-sewing-eff', 'subPageGroup' => 'sewing-report', 'subPage' => 'reportProduction', 'months' => $months, 'years' => $years]);
    }

    public function exportData(Request $request) {
        ini_set('max_execution_time', 3600);

        $periode = $request->periode ? $request->periode : "monthly";
        $tanggal = $request->tanggal ? $request->tanggal : date("Y-m-d");

        return Excel::download(new ExportReportProduction($periode, $tanggal), 'output_report.xlsx');
    }
}
