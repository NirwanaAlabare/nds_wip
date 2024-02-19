<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanPemasukanRoll;
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

class LapDetPemasukanRollController extends Controller
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
                $additionalQuery .= " and a.tgl_dok >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_dok <= '" . $request->dateTo . "' ";
            }


            $data_pemasukan = DB::connection('mysql_sb')->select("select *, CONCAT_WS('',no_dok,tgl_dok,no_mut,supplier,rak,barcode,no_roll,no_lot,qty,qty_mut,satuan,id_item,id_jo,no_ws,goods_code,itemdesc,color,size,deskripsi,username,confirm_by) cari_data from (select a.no_dok,b.tgl_dok,COALESCE(c.no_mut,'-') no_mut,a.supplier,CONCAT(c.kode_lok,' FABRIC WAREHOUSE RACK') rak,c.no_barcode barcode,no_roll,no_lot,ROUND(qty_sj,2) qty, COALESCE(ROUND(qty_mutasi,2),0) qty_mut,satuan,b.id_item,b.id_jo,b.no_ws,d.goods_code,d.itemdesc,d.color,d.size,COALESCE(a.deskripsi,'-') deskripsi,CONCAT(a.created_by,' (',a.created_at, ') ') username,CONCAT(a.approved_by,' (',a.approved_date, ') ') confirm_by from whs_inmaterial_fabric a inner join whs_inmaterial_fabric_det b on b.no_dok = a.no_dok  inner join whs_lokasi_inmaterial c on c.no_dok = a.no_dok inner join masteritem d on d.id_item = c.id_item where c.status = 'Y' and left(a.no_dok,2) ='GK' " . $additionalQuery . " group by c.id) a");


            return DataTables::of($data_pemasukan)->toJson();
        }

        return view("lap-det-pemasukan.lap_pemasukan_roll", ["page" => "dashboard-warehouse"]);
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
        return Excel::download(new ExportLaporanPemasukanRoll($request->from, $request->to), 'Laporan_pemasukan_fabric_barcode.xlsx');
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
