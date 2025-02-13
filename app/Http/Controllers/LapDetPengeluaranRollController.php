<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPengeluaranRoll;
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

class LapDetPengeluaranRollController extends Controller
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


            $data_pemasukan = DB::connection('mysql_sb')->select("select * , CONCAT_WS('',no_bppb,tgl_bppb,no_req,tujuan,no_barcode,no_roll,no_lot,qty_out,unit,id_item,id_jo,ws,goods_code,itemdesc,color,size,remark,username,styleno,confirm_by)cari_data from (select ac.styleno,a.no_bppb,a.tgl_bppb,a.no_req,a.tujuan,b.id_roll no_barcode, b.no_roll,b.no_lot,ROUND(b.qty_out,4) qty_out, b.satuan unit,b.id_item,b.id_jo,ac.kpno ws,goods_code,concat(itemdesc,' ',add_info) itemdesc,s.color,s.size,a.catatan remark,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by
from whs_bppb_h a 
inner join whs_bppb_det b on b.no_bppb = a.no_bppb
inner join masteritem s on b.id_item=s.id_item 
left join (select id_jo,id_so from jo_det group by id_jo ) tmpjod on tmpjod.id_jo=b.id_jo 
left join (select bppbno as no_req,idws_act from bppb_req group by no_req) br on a.no_req = br.no_req 
left join so on tmpjod.id_so=so.id 
left join act_costing ac on so.id_cost=ac.id  
where LEFT(a.no_bppb,2) = 'GK' and b.status != 'N' and a.status != 'cancel'  " . $additionalQuery . " and matclass= 'FABRIC' GROUP BY b.id order by a.no_bppb) a");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("lap-det-pengeluaran.lap_pengeluaran_roll", ["page" => "dashboard-warehouse"]);
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


    public function export_excel_roll(Request $request)
    {
        return Excel::download(new ExportLaporanPengeluaranRoll($request->from, $request->to), 'Laporan_pengeluaran_fabric_barcode.xlsx');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stocker  $stocker
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
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stocker  $stocker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stocker $stocker)
    {
        //
    }

  

    
}
