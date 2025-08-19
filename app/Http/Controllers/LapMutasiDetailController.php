<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanMutDetail;
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

class LapMutasiDetailController extends Controller
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

            $data_mutasi = DB::connection('mysql_sb')->select("select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan,round(sal_awal,2) sal_awal,round(qty_in,2) qty_in,ROUND(qty_out_sbl,2) qty_out_sbl,ROUND(qty_out,2) qty_out, round((sal_awal + qty_in - qty_out_sbl - qty_out),2) sal_akhir, CONCAT_WS('',kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan) cari_item from (select concat(a.kode_lok,' FABRIC WAREHOUSE RACK') kode_lok,a.id_jo,no_ws,styleno,buyer,a.id_item,goods_code,itemdesc,a.satuan,sal_awal,qty_in,coalesce(qty_out_sbl,'0') qty_out_sbl,coalesce(qty_out,'0') qty_out from (select b.kode_lok,b.id_jo,b.no_ws,b.styleno,b.buyer,b.id_item,b.goods_code,b.itemdesc,b.satuan, sal_awal, qty_in from (
select a.id_item,b.unit,a.goods_code,a.itemdesc from masteritem a inner join po_item b on b.id_gen = a.id_gen where matclass = 'Fabric' GROUP BY a.id_item,b.unit) a left join
(select kode_lok,id_jo,no_ws,styleno,buyer,id_item,goods_code,itemdesc,satuan, sum(sal_awal) sal_awal,sum(qty_in) qty_in from (select a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan, sum(qty_sj) sal_awal,'0' qty_in from whs_lokasi_inmaterial a
inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok < '" . $request->dateFrom . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan
UNION
select a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.unit, round(sum(qty),2) sal_awal,'0' qty_in from whs_sa_fabric a
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.qty > 0 and a.tgl_bpb < '" . $request->dateFrom . "' group by a.kode_lok, a.id_item, a.id_jo, a.unit
UNION
select a.kode_lok,a.id_jo,a.no_ws,jd.styleno,mb.supplier buyer,a.id_item,b.goods_code,b.itemdesc,a.satuan,'0' sal_awal, round(sum(qty_sj),2) qty_in from whs_lokasi_inmaterial a
inner join whs_inmaterial_fabric bpb on bpb.no_dok = a.no_dok
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and bpb.tgl_dok BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by a.kode_lok, a.id_item, a.id_jo, a.satuan) a group by a.kode_lok, a.id_item, a.id_jo, a.satuan

) b on b.id_item = a.id_item and b.satuan = a.unit where kode_lok is not null) a left join (select kode_lok,id_item,id_jo,satuan,ROUND(sum(qty_out_sbl),2) qty_out_sbl,ROUND(sum(qty_out),2) qty_out from (select kode_lok,id_item,id_jo,satuan,qty_out_sbl,'0' qty_out from (select b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out_sbl from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on a.idbpb_det = b.id where a.status = 'Y' and tgl_mut < '" . $request->dateFrom . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
UNION
select no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out_sbl from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb < '" . $request->dateFrom . "' group by no_rak, id_item, id_jo, satuan) a
UNION
select kode_lok,id_item,id_jo,satuan,'0' qty_out_sbl, qty_out from (select b.kode_lok,b.id_item,b.id_jo,satuan,sum(a.qty_mutasi) qty_out from whs_mut_lokasi a inner join whs_lokasi_inmaterial b on a.idbpb_det = b.id where a.status = 'Y' and tgl_mut BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by b.kode_lok,b.id_item,b.id_jo,satuan
UNION
select no_rak kode_lok,id_item,id_jo,satuan,round(sum(qty_out),2) qty_out from whs_bppb_det a inner join whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by no_rak, id_item, id_jo, satuan) a) a group by kode_lok, id_item, id_jo, satuan) b on b.kode_lok = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item and b.satuan = a.satuan) a");


            return DataTables::of($data_mutasi)->toJson();
        }

        return view("lap-mutasi-detail.lap_mutasi_detail", ["page" => "dashboard-warehouse"]);
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


    public function export_excel_mut_detail(Request $request)
    {
        return Excel::download(new ExportLaporanMutDetail($request->from, $request->to), 'Laporan_mutasi_detail_fabric.xlsx');
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
