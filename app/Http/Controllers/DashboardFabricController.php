<?php

namespace App\Http\Controllers;

use App\Models\Stocker;
use App\Models\StockerDetail;
use App\Models\FormCutInput;
use App\Models\FormCutInputDetail;
use App\Models\FormCutInputDetailLap;
use App\Models\Marker;
use App\Models\MasterLokasi;
use App\Models\UnitLokasi;
use App\Models\InMaterialFabric;
use App\Models\InMaterialFabricDet;
use App\Models\Bpb;
use App\Models\Tempbpb;
use App\Models\InMaterialLokTemp;
use Illuminate\Support\Facades\Auth;
use App\Models\MarkerDetail;
use App\Models\InMaterialLokasi;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportLokasiMaterial;
use DB;
use QrCode;
use DNS1D;
use PDF;

class DashboardFabricController extends Controller
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
            $keywordQuery = "";


            $data_rak = DB::connection('mysql_sb')->select("select kode_lok,concat(kode_lok,' FABRIC WAREHOUSE RACK') nama_lok,concat(kapasitas,' ROLL') kapasitas, concat(roll_stok, ' ROLL') stok, round(IF(kapasitas > roll_stok,(roll_stok/kapasitas * 100),100),2) balance,concat(round(IF(kapasitas > roll_stok,(roll_stok/kapasitas * 100),100),2),'%') persentase from (select a.kode_lok,kapasitas,count(no_barcode) roll_stok from (select a.kode_lok,a.no_barcode,a.buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item where a.qty > 0 and qty_mut is null
UNION
select a.kode_lok,a.no_barcode,mb.supplier buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty_sj from whs_lokasi_inmaterial a 
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and qty_mutasi is null) a left JOIN
(select id_roll,no_rak from whs_bppb_det where status = 'Y' GROUP BY id_roll) b on a.no_barcode = b.id_roll left join (select kode_lok,kapasitas from whs_master_lokasi GROUP BY kode_lok) c on c.kode_lok = a.kode_lok where id_roll is null GROUP BY a.kode_lok) a");



            return DataTables::of($data_rak)->toJson();
        }
            $tot_roll = DB::connection('mysql_sb')->select("select concat(format(sum(roll_stok),0), ' ROLL') stok from (select a.kode_lok,kapasitas,count(no_barcode) roll_stok from (select a.kode_lok,a.no_barcode,a.buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item where a.qty > 0 and qty_mut is null
UNION
select a.kode_lok,a.no_barcode,mb.supplier buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty_sj from whs_lokasi_inmaterial a 
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and qty_mutasi is null) a left JOIN
(select id_roll,no_rak from whs_bppb_det where status = 'Y' GROUP BY id_roll) b on a.no_barcode = b.id_roll left join (select kode_lok,kapasitas from whs_master_lokasi GROUP BY kode_lok) c on c.kode_lok = a.kode_lok where id_roll is null GROUP BY a.kode_lok) a");

            $qty_in = DB::connection('mysql_sb')->select("select concat(format(count(id),0), ' ROLL') qty_in from whs_lokasi_inmaterial where DATE_FORMAT(created_at, '%Y-%m-%d') = CURRENT_DATE() and status = 'Y'");

            $qty_out = DB::connection('mysql_sb')->select("select concat(format(count(id),0), ' ROLL') qty_out from whs_bppb_det where DATE_FORMAT(created_at, '%Y-%m-%d') = CURRENT_DATE() and status = 'Y'");

        return view("dashboard-fabric", ['tot_roll' => $tot_roll,'qty_in' => $qty_in,'qty_out' => $qty_out,"page" => "dashboard-warehouse"]);
    }


    public function getListbarcodero(Request $request)
    {
        $listbarcode = DB::connection('mysql_sb')->select("select id isi,tampil,tampil2 from (select a.id, concat(a.id,' - ' ,a.item_desc, ' - ', a.no_ws) tampil,concat(a.id,' - ', a.item_desc) tampil2 ,a.qty_aktual, COALESCE(c.qty_out,0) qty_out,(a.qty_aktual - COALESCE(c.qty_out,0)) qty_sisa from whs_lokasi_inmaterial a left join (select id_roll,sum(qty_out) qty_out from whs_bppb_det GROUP BY id_roll) c on c.id_roll = a.id where a.id_jo='" . $request->id_jo . "' and a.id_item='" . $request->id_item . "') a where a.qty_sisa > 0");

        $html = "";

        foreach ($listbarcode as $barcode) {
            $html .= " <option value='" . $barcode->isi . "'>" . $barcode->isi . "</option> ";
        }

        return $html;
    }

    public function getdatarak(Request $request)
    {

    $det_item = DB::connection('mysql_sb')->select("select buyer,no_ws,id_jo,id_item,goods_code,itemdesc,concat(count(no_barcode), ' ROLL') qty_roll from (select a.no_barcode,a.buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty from whs_sa_fabric a inner join masteritem b on b.id_item = a.id_item where a.qty > 0 and qty_mut is null and a.kode_lok = '" . $request->kode_lok . "'
UNION
select a.no_barcode,mb.supplier buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty_sj from whs_lokasi_inmaterial a 
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and qty_mutasi is null and a.kode_lok = '" . $request->kode_lok . "') a left JOIN
(select id_roll,no_rak from whs_bppb_det where no_rak = '" . $request->kode_lok . "' and status = 'Y' GROUP BY id_roll) b on a.no_barcode = b.id_roll where id_roll is null GROUP BY id_jo,id_item");

        $html = '<div class="table-responsive">
            <table id="tableshow" class="table table-head-fixed table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">Buyer</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">No WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Id JO</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 24%;">Nama Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Qty</th>
                    </tr>
                </thead>
                <tbody>';
            $jml_qty_sj = 0;
            $jml_qty_ak = 0;
            $x = 1;
        foreach ($det_item as $detitem) {
            $html .= ' <tr>
                        <td> '.$detitem->buyer.'</td>
                        <td> '.$detitem->no_ws.'</td>
                        <td> '.$detitem->id_jo.'</td>
                        <td> '.$detitem->id_item.'</td>
                        <td> '.$detitem->itemdesc.'</td>
                        <td> '.$detitem->qty_roll.'</td>
                       </tr>';
                       $x++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }


    public function getdatarak2(Request $request)
    {

    $det_item = DB::connection('mysql_sb')->select("select buyer,no_ws,id_jo,id_item,goods_code,itemdesc,concat(count(qty_sj), ' ROLL') qty_roll from (
select a.no_barcode,mb.supplier buyer,a.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty_sj from whs_lokasi_inmaterial a 
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and qty_mutasi is null and DATE_FORMAT(a.created_at, '%Y-%m-%d') = CURRENT_DATE()) a GROUP BY id_jo,id_item");

        $html = '<div class="table-responsive">
            <table id="tableshow2" class="table table-head-fixed table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">Buyer</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">No WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Id JO</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 24%;">Nama Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Qty</th>
                    </tr>
                </thead>
                <tbody>';
            $jml_qty_sj = 0;
            $jml_qty_ak = 0;
            $x = 1;
        foreach ($det_item as $detitem) {
            $html .= ' <tr>
                        <td> '.$detitem->buyer.'</td>
                        <td> '.$detitem->no_ws.'</td>
                        <td> '.$detitem->id_jo.'</td>
                        <td> '.$detitem->id_item.'</td>
                        <td> '.$detitem->itemdesc.'</td>
                        <td> '.$detitem->qty_roll.'</td>
                       </tr>';
                       $x++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }


    public function getdatarak3(Request $request)
    {

    $det_item = DB::connection('mysql_sb')->select("select buyer,no_ws,id_jo,id_item,goods_code,itemdesc,concat(count(qty_out), ' ROLL') qty_roll from (
select a.no_roll,mb.supplier buyer,h.no_ws,a.id_jo,a.id_item,b.goods_code,b.itemdesc,a.qty_out from whs_bppb_det a 
inner join whs_bppb_h h on h.no_bppb = a.no_bppb
inner join masteritem b on b.id_item = a.id_item
inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo
inner join mastersupplier mb on jd.id_buyer = mb.id_supplier where a.status = 'Y' and DATE_FORMAT(a.created_at, '%Y-%m-%d') = CURRENT_DATE()) a GROUP BY id_jo,id_item");

        $html = '<div class="table-responsive">
            <table id="tableshow3" class="table table-head-fixed table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">Buyer</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 15%;">No WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Id JO</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 11%;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 24%;">Nama Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 10%;">Qty</th>
                    </tr>
                </thead>
                <tbody>';
            $jml_qty_sj = 0;
            $jml_qty_ak = 0;
            $x = 1;
        foreach ($det_item as $detitem) {
            $html .= ' <tr>
                        <td> '.$detitem->buyer.'</td>
                        <td> '.$detitem->no_ws.'</td>
                        <td> '.$detitem->id_jo.'</td>
                        <td> '.$detitem->id_item.'</td>
                        <td> '.$detitem->itemdesc.'</td>
                        <td> '.$detitem->qty_roll.'</td>
                       </tr>';
                       $x++;
        }

        $html .= '</tbody>
            </table>
        </div>';

        return $html;
    }





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
