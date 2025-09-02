<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPengeluaran;
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

class LapDetPengeluaranController extends Controller
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

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_bppb >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_bppb <= '" . $request->dateTo . "' ";
            }

            // $keywordQuery = "";
            // if ($request->search["value"]) {
            //     $keywordQuery = "
            //         and (
            //             act_costing_ws like '%" . $request->search["value"] . "%' OR
            //             DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
            //         )
            //     ";
            // }

            $data_pemasukan = DB::connection('mysql_sb')->select("select *, CONCAT_WS(bppbno,bppbno_req,bppbdate,invno,jenis_dok,supplier,id_item,goods_code,itemdesc,color,size,qty,qty_good,qty_reject,unit,remark,username,confirm_by,ws,styleno,curr,price,idws_act,jenis_trans) cari_data from (select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans
from whs_bppb_h a
inner join whs_bppb_det b on b.no_bppb = a.no_bppb
inner join masteritem s on b.id_item=s.id_item
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo
left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req
left join so on tmpjod.id_so=so.id
left join act_costing ac on so.id_cost=ac.id
where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel'  " . $additionalQuery . " GROUP BY b.id_jo,b.id_item,b.no_bppb order by a.no_bppb) a");



//             $data_pemasukan = DB::connection('mysql_sb')->select("select a.no_bppb bppbno,a.no_req bppbno_req,a.tgl_bppb bppbdate,no_invoice invno,a.dok_bc jenis_dok,right(no_aju,6) no_aju,tgl_aju tanggal_aju, lpad(no_daftar,6,'0') bcno,tgl_daftar bcdate,a.tujuan supplier,b.id_item,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size, sum(b.qty_out) qty,0 as qty_good,0 as qty_reject, b.satuan unit,'' berat_bersih,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by,ac.kpno ws,ac.styleno,b.curr,b.price,br.idws_act,'' jenis_trans,cp.nama_panel, cc.color_gmt
// from whs_bppb_h a
// inner join whs_bppb_det b on b.no_bppb = a.no_bppb
// inner join masteritem s on b.id_item=s.id_item
// left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo
// left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req
// left join so on tmpjod.id_so=so.id
// left join act_costing ac on so.id_cost=ac.id
// left join (select id_jo,bom_jo_item.id_item,group_concat(distinct(nama_panel)) nama_panel from bom_jo_item inner join masterpanel mp on bom_jo_item.id_panel = mp.id where id_panel != '0' group by id_item, id_jo) cp on s.id_gen = cp.id_item and b.id_jo = cp.id_jo
// left join (select id_item, id_jo, group_concat(distinct(color)) color_gmt from bom_jo_item k inner join so_det sd on k.id_so_det = sd.id where status = 'M' and k.cancel = 'N' group by id_item, id_jo) cc on s.id_gen = cc.id_item and b.id_jo = cc.id_jo
// where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel'  " . $additionalQuery . " and matclass= 'FABRIC' GROUP BY b.id_item,b.no_bppb order by a.no_bppb");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("lap-det-pengeluaran.lap_pengeluaran", ["page" => "dashboard-warehouse"]);
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


    public function export_excel_pengeluaran(Request $request)
    {
        return Excel::download(new ExportLaporanPengeluaran($request->from, $request->to), 'Laporan_pengeluaran_fabric.xlsx');
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
