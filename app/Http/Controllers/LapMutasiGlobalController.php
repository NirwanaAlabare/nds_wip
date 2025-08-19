<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanMutGlobal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLokasiMaterial;
use DB;
use QrCode;
use DNS1D;
use PDF;

class LapMutasiGlobalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";

            // if ($request->dateFrom) {
            //     $additionalQuery .= " and a.tgl_bppb >= '" . $request->dateFrom . "' ";
            // }

            // if ($request->dateTo) {
            //     $additionalQuery .= " and a.tgl_bppb <= '" . $request->dateTo . "' ";
            // }

            $data_mutasi = DB::connection('mysql_sb')->select("select id_item,goods_code,itemdesc,unit,round((sal_awal - qty_out_sbl),2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir, CONCAT_WS('',id_item,goods_code,itemdesc,unit) cari_item from (select id_item,goods_code,itemdesc,unit,SUM(sal_awal) sal_awal,SUM(qty_in) qty_in,SUM(qty_out_sbl) qty_out_sbl,SUM(qty_out) qty_out,SUM(fil) fil from (select a.id_item,a.goods_code,a.itemdesc,a.unit,COALESCE(sal_awal,0) sal_awal,COALESCE(qty_in,0) qty_in,COALESCE(qty_out_sbl,0) qty_out_sbl, COALESCE(qty_out,0) qty_out, (COALESCE(sal_awal,0) + COALESCE(qty_in,0)) fil from (
                select a.id_item,a.unit,b.goods_code,b.itemdesc from (select id_item,unit from whs_sa_fabric group by id_item,unit
                UNION
                select id_item,unit from whs_inmaterial_fabric_det  where tgl_dok < '" . $request->dateFrom . "' group by id_item,unit) a inner join masteritem b on b.id_item = a.id_item group by id_item,unit) a left join
                (select id_item,unit, sum(sal_awal) sal_awal from (select id_item,unit, sum(qty_good) sal_awal from whs_inmaterial_fabric_det where tgl_dok < '" . $request->dateFrom . "' and status = 'Y' GROUP BY id_item,unit union select id_item,unit, round(sum(qty),2) sal_awal from whs_sa_fabric  GROUP BY id_item,unit) a  GROUP BY id_item,unit) b on b.id_item = a.id_item and b.unit = a.unit left join
                (select id_item,unit, sum(qty_good) qty_in from whs_inmaterial_fabric_det where tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and status = 'Y' GROUP BY id_item,unit) c on c.id_item = a.id_item and c.unit = a.unit left join
                (select id_item,satuan, sum(qty_out) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb < '" . $request->dateFrom . "' and a.status = 'Y' GROUP BY id_item,satuan) d on d.id_item = a.id_item and d.satuan = a.unit left join
                (select id_item,satuan, sum(qty_out) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where b.tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' and a.status = 'Y' GROUP BY id_item,satuan) e on e.id_item = a.id_item and e.satuan = a.unit) a GROUP BY a.id_item,a.unit) a where fil != 0");


            return DataTables::of($data_mutasi)->toJson();
        }

        return view("lap-mutasi-global.lap_mutasi_global", ["page" => "dashboard-warehouse"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function export_excel_mut_global(Request $request)
    {
        return Excel::download(new ExportLaporanMutGlobal($request->from, $request->to), 'Laporan_mutasi_global_fabric.xlsx');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function edit(Stocker $stocker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }




}
