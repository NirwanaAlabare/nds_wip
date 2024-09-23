<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SoLogCopySaldo;
use App\Models\SoCopySaldo;
use App\Models\SoDetailTemp;
use App\Models\SoDetail;
use App\Models\SoHeader;
use App\Exports\ExportLaporanStokOpname;
use App\Exports\ExportLaporanStokOpnameDetail;
use App\Exports\ExportDetailStokOpname;
use Maatwebsite\Excel\Facades\Excel;

use Carbon\Carbon;
use DB;
use QrCode;
use PDF;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // if ($request->ajax()) {
        //     $worksheetQuery = DB::connection("mysql_sb")->
        //         table("so_det")->
        //         selectRaw("
        //             so_det.id,
        //             mastersupplier.Supplier buyer,
        //             act_costing.id ws_id,
        //             act_costing.kpno ws,
        //             act_costing.styleno style,
        //             so_det.color color,
        //             so_det.size size,
        //             so_det.dest dest,
        //             so_det.qty qty,
        //             act_costing.cost_date tgl_cost,
        //             act_costing.deldate tgl_del
        //         ")->
        //         leftJoin("so", "so.id", "=", "so_det.id_so")->
        //         leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
        //         leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
        //         where("act_costing.status", "!=", "CANCEL")->
        //         where("act_costing.cost_date", ">=", "2023-01-01")->
        //         where("act_costing.type_ws", "STD")->
        //         groupBy("so_det.id")->
        //         orderBy("act_costing.cost_date")->
        //         orderBy("act_costing.kpno")->
        //         orderBy("so_det.id");

        //     if ($request->month) {
        //         $worksheetQuery->whereRaw("(MONTH(act_costing.cost_date) = '".$request->month."' OR MONTH(act_costing.del_date) = '".$request->month."')");
        //     }

        //     if ($request->year) {
        //         $worksheetQuery->whereRaw("(YEAR(act_costing.cost_date) = '".$request->year."' OR YEAR(act_costing.del_date) = '".$request->year."')");
        //     }

        //     return DataTables::of($worksheetQuery)->toJson();
        // }

        return view("stock_opname.homepage", ["page" => "stock_opname"]);
    }

    public function datarak(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $keywordQuery = "";

            if ($request->item_so == 'Fabric') {

                $data_rak = DB::connection('mysql_sb')->select("select a.*,b.itemdesc,jd.*,mb.supplier buyer from (select kode_lok, id_jo,id_item,round(sum(qty_sisa),2) qty, unit,COUNT(no_barcode) ttl_roll from (select no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,COUNT(no_barcode) ttl_roll, sum(qty_in) qty_in, sum(qty_out) qty_out, round(sum(qty_in) - sum(qty_out),2) qty_sisa,unit from (select 'saldo_awal' id,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty qty_in, 0 qty_out,unit from whs_sa_fabric GROUP BY no_barcode
                    UNION
                    select 'transaksi',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_out,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok <= '".$request->tgl_filter."' GROUP BY no_barcode
                    UNION
                    select 'pengeluaran',id_roll,no_rak,id_jo,id_item,no_lot,no_roll,0 qty_in, sum(qty_out) qty_out,satuan from whs_bppb_det a INNER JOIN whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb <= '".$request->tgl_filter."' GROUP BY id_roll
                    UNION
                    select 'mutasi',idbpb_det,b.kode_lok,b.id_jo,a.id_item,a.no_lot,a.no_roll,0 qty_in, sum(a.qty_mutasi) qty_out,satuan from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where a.status = 'Y' and tgl_mut <= '".$request->tgl_filter."' GROUP BY idbpb_det) a GROUP BY no_barcode) a where qty_sisa > 0 GROUP BY kode_lok, id_jo,id_item) a INNER JOIN masteritem b on b.id_item = a.id_item inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo inner join mastersupplier mb on jd.id_buyer = mb.id_supplier");
            }elseif ($request->item_so == 'Sparepart') {

                $data_rak = DB::connection('mysql_sb')->select("select 
                    '' kode_lok, '' id_jo, '' kpno, '' styleno, '' buyer,id_item,
                    goods_code kode_brg,
                    itemdesc,
                    '' ttl_roll,
                    sum(qty_sa) + sum(qty_in) - sum(qty_out) qty,
                    unit 
                    FROM (
                    select 
                    id_item,
                    goods_code,
                    itemdesc,
                    sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa,
                    '0' qty_in,
                    '0' qty_out,
                    unit 
                    from 
                    (
                    select id_item, kd_barang goods_code, mi.itemdesc, qty qty_sa, '0' qty_in, '0' qty_out, unit   from saldoawal_gd a
                    inner join masteritem mi on a.kd_barang = mi.goods_code
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where periode = '2022-01-01' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N'
                    union 
                    select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
                    inner join masteritem mi on bpb.id_item = mi.id_item
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where bpbdate >= '2022-01-01' and bpbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
                    group by mi.id_item, bpb.unit 
                    union 
                    select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
                    inner join masteritem mi on bppb.id_item = mi.id_item
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where bppbdate >= '2022-01-01' and bppbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
                    group by mi.id_item, bppb.unit
                    ) trx
                    group by id_item, unit
                    UNION
                    select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
                    inner join masteritem mi on bpb.id_item = mi.id_item
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where bpbdate >= '".$request->tgl_filter."' and bpbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
                    group by mi.id_item, bpb.unit 
                    UNION
                    select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
                    inner join masteritem mi on bppb.id_item = mi.id_item
                    inner join mapping_category mc on mi.n_code_category = mc.n_id
                    where bppbdate >= '".$request->tgl_filter."' and bppbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
                    group by mi.id_item, bppb.unit
                    ) mutasi
                    group by id_item, unit
                    having sum(qty_sa) != '0' or sum(qty_in) != '0' or sum(qty_out) != '0' or sum(qty_sa) + sum(qty_in) - sum(qty_out) != '0'
                    order by kode_brg asc

                    ");
            }else{
             $data_rak = DB::connection('mysql_sb')->select("select '' kode_lok, '' id_jo, '' kpno, '' styleno, '' buyer, '' id_item, '' itemdesc, '' ttl_roll, '' qty, '' unit");
         }
         // dd($data_rak);


         return DataTables::of($data_rak)->toJson();
     }

     $item_so = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'item_stok_opname')->where('status', '=', 'Active')->get();

        // $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('tipe_sup', '=', 'S')->get();

     return view("stock_opname.data-rak", ['item_so' => $item_so,"page" => "stock_opname"]);
 }


 public function getListpartialso(Request $request)
 {
    if ($request->item_so == 'Fabric') {

        $data_rak = DB::connection('mysql_sb')->select("select DISTINCT kode_lok data from (select kode_lok, id_jo,id_item,round(sum(qty_sisa),2) qty, unit,COUNT(no_barcode) ttl_roll from (select no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,COUNT(no_barcode) ttl_roll, sum(qty_in) qty_in, sum(qty_out) qty_out, round(sum(qty_in) - sum(qty_out),2) qty_sisa,unit from (select 'saldo_awal' id,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty qty_in, 0 qty_out,unit from whs_sa_fabric GROUP BY no_barcode
            UNION
            select 'transaksi',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_out,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok <= '".$request->tgl_filter."' GROUP BY no_barcode
            UNION
            select 'pengeluaran',id_roll,no_rak,id_jo,id_item,no_lot,no_roll,0 qty_in, sum(qty_out) qty_out,satuan from whs_bppb_det a INNER JOIN whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb <= '".$request->tgl_filter."' GROUP BY id_roll
            UNION
            select 'mutasi',idbpb_det,b.kode_lok,b.id_jo,a.id_item,a.no_lot,a.no_roll,0 qty_in, sum(a.qty_mutasi) qty_out,satuan from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where a.status = 'Y' and tgl_mut <= '".$request->tgl_filter."' GROUP BY idbpb_det) a GROUP BY no_barcode) a where qty_sisa > 0 GROUP BY kode_lok, id_jo,id_item) a INNER JOIN masteritem b on b.id_item = a.id_item inner join (select ac.id_buyer,ac.styleno,jd.id_jo, ac.kpno from jo_det jd inner join so on jd.id_so = so.id inner join act_costing ac on so.id_cost = ac.id where jd.cancel = 'N' group by id_cost order by id_jo asc) jd on a.id_jo = jd.id_jo inner join mastersupplier mb on jd.id_buyer = mb.id_supplier");
    }elseif ($request->item_so == 'Sparepart') {

        $data_rak = DB::connection('mysql_sb')->select("select DISTINCT id_item data
            FROM (
            select 
            id_item,
            goods_code,
            itemdesc,
            sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa,
            '0' qty_in,
            '0' qty_out,
            unit 
            from 
            (
            select id_item, kd_barang goods_code, mi.itemdesc, qty qty_sa, '0' qty_in, '0' qty_out, unit   from saldoawal_gd a
            inner join masteritem mi on a.kd_barang = mi.goods_code
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where periode = '2022-01-01' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N'
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '2022-01-01' and bpbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '2022-01-01' and bppbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) trx
            group by id_item, unit
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '".$request->tgl_filter."' and bpbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '".$request->tgl_filter."' and bppbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) mutasi
            group by id_item, unit
            having sum(qty_sa) != '0' or sum(qty_in) != '0' or sum(qty_out) != '0' or sum(qty_sa) + sum(qty_in) - sum(qty_out) != '0'
            order by id_item asc");
    }else{
     $data_rak = DB::connection('mysql_sb')->select("select '' data");
 }
// dd($data_rak);

 $html = "";

 foreach ($data_rak as $data) {
    $html .= " <option value='" . $data->data . "'>" . $data->data . "</option> ";
}

return $html;
}

public function getListpartialsoreplace(Request $request)
{
    if ($request->item_so == 'Fabric') {

        $data_rak = DB::connection('mysql_sb')->select("select distinct kode_lok data from whs_saldo_stockopname where no_transaksi = '".$request->no_dok_cs."' order by id asc");
    }elseif ($request->item_so == 'Sparepart') {

        $data_rak = DB::connection('mysql_sb')->select("select distinct id_item data from whs_saldo_stockopname where no_transaksi = '".$request->no_dok_cs."' order by id asc");
    }else{
     $data_rak = DB::connection('mysql_sb')->select("select '' data");
 }
// dd($data_rak);

 $html = "";

 foreach ($data_rak as $data) {
    $html .= " <option value='" . $data->data . "'>" . $data->data . "</option> ";
}

return $html;
}


public function getNolapSO(Request $request)
{
    if ($request->item_so != '') {
        $nomordok = DB::connection('mysql_sb')->select("select DISTINCT no_transaksi from whs_saldo_stockopname WHERE tipe_item = '".$request->item_so."'");
    }else{
        $nomordok = DB::connection('mysql_sb')->select("select '' no_transaksi");
    }

    $html = "<option value=''>Pilih No Dokumen</option>";

    foreach ($nomordok as $nodok) {
        $html .= " <option value='" . $nodok->no_transaksi . "'>" . $nodok->no_transaksi . "</option> ";
    }

    return $html;
}


public function copysaldostok(Request $request)
{
    $timestamp = Carbon::now();
    $h_cs = '';
    if ($request->item_so == 'Fabric') {
        $h_cs = 'STO/FAB/';
    }elseif ($request->item_so == 'Accessories') {
        $h_cs = 'STO/ACC/';
    }elseif ($request->item_so == 'Sparepart') {
        $h_cs = 'STO/SPP/';
    }elseif ($request->item_so == 'Ekspedisi') {
        $h_cs = 'STO/EXP/';
    }elseif ($request->item_so == 'Barang Jadi') {
        $h_cs = 'STO/FGD/';
    }else{

    }
    $data = DB::connection('mysql_sb')->select("select CONCAT('".$h_cs."',DATE_FORMAT(current_date(), '%m'),DATE_FORMAT(current_date(), '%y'),'/',IF(MAX(no_transaksi) IS NULL,'001',LPAD(MAX(SUBSTR(no_transaksi,14,3))+1,3,0))) no_transaksi FROM whs_saldo_stockopname WHERE tipe_item = '".$request->item_so."' and MONTH(current_date()) = MONTH(current_date()) AND YEAR(current_date()) = YEAR(current_date()) ");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
    $no_transaksi = $data[0]->no_transaksi;

    $inmaterialStore2 = SoLogCopySaldo::create([
        'no_transaksi' => $no_transaksi,
        'tipe_item' => $request->item_so,
        'status' => 'Copy saldo',
        'copy_user' => Auth::user()->name,
        'filter_date' => $request->tgl_filter,
    ]);

    if ($request->item_so == 'Fabric') {
        $sql_barcode = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname select '','".$no_transaksi."','".$request->item_so."','".$request->tgl_filter."',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty_sisa,unit,'OPEN' status,'".$timestamp."','".$timestamp."' from (select no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, sum(qty_in) qty_in, sum(qty_out) qty_out, round(sum(qty_in) - sum(qty_out),2) qty_sisa,unit from (select 'saldo_awal' id,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty qty_in, 0 qty_out,unit from whs_sa_fabric GROUP BY no_barcode
            UNION
            select 'transaksi',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_out,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok <= '".$request->tgl_filter."' GROUP BY no_barcode
            UNION
            select 'pengeluaran',id_roll,no_rak,id_jo,id_item,no_lot,no_roll,0 qty_in, sum(qty_out) qty_out,satuan from whs_bppb_det a INNER JOIN whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb <= '".$request->tgl_filter."' GROUP BY id_roll
            UNION
            select 'mutasi',idbpb_det,b.kode_lok,b.id_jo,a.id_item,a.no_lot,a.no_roll,0 qty_in, sum(a.qty_mutasi) qty_out,satuan from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where a.status = 'Y' and tgl_mut <= '".$request->tgl_filter."' GROUP BY idbpb_det) a GROUP BY no_barcode) a where qty_sisa > 0");
    }elseif ($request->item_so == 'Sparepart') {
        $sql_barcode = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname select '','".$no_transaksi."','".$request->item_so."','".$request->tgl_filter."','' no_barcode,'' kode_lok,'' id_jo,id_item,'' no_lot,'' no_roll,sum(qty_sa) + sum(qty_in) - sum(qty_out) qty,unit,'OPEN' status,'".$timestamp."','".$timestamp."'
            FROM (
            select 
            id_item,
            goods_code,
            itemdesc,
            sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa,
            '0' qty_in,
            '0' qty_out,
            unit 
            from 
            (
            select id_item, kd_barang goods_code, mi.itemdesc, qty qty_sa, '0' qty_in, '0' qty_out, unit   from saldoawal_gd a
            inner join masteritem mi on a.kd_barang = mi.goods_code
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where periode = '2022-01-01' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N'
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '2022-01-01' and bpbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '2022-01-01' and bppbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) trx
            group by id_item, unit
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '".$request->tgl_filter."' and bpbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '".$request->tgl_filter."' and bppbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) mutasi
            group by id_item, unit
            having sum(qty_sa) != '0' or sum(qty_in) != '0' or sum(qty_out) != '0' or sum(qty_sa) + sum(qty_in) - sum(qty_out) != '0'
            ");
    }else{

    }


    $massage = 'Data berhasil dicopy,nomor: '.$no_transaksi;

    return array(
        "status" => 200,
        "message" => $massage,
        "additional" => [],
        "redirect" => url('/stock_opname')
    );

}


public function copysaldostokpartial(Request $request)
{
    $timestamp = Carbon::now();
    $h_cs = '';
    if ($request->item_so == 'Fabric') {
        $h_cs = 'STO/FAB/';
    }elseif ($request->item_so == 'Accessories') {
        $h_cs = 'STO/ACC/';
    }elseif ($request->item_so == 'Sparepart') {
        $h_cs = 'STO/SPP/';
    }elseif ($request->item_so == 'Ekspedisi') {
        $h_cs = 'STO/EXP/';
    }elseif ($request->item_so == 'Barang Jadi') {
        $h_cs = 'STO/FGD/';
    }else{

    }
    $data = DB::connection('mysql_sb')->select("select CONCAT('".$h_cs."',DATE_FORMAT(current_date(), '%m'),DATE_FORMAT(current_date(), '%y'),'/',IF(MAX(no_transaksi) IS NULL,'001',LPAD(MAX(SUBSTR(no_transaksi,14,3))+1,3,0))) no_transaksi FROM whs_saldo_stockopname WHERE tipe_item = '".$request->item_so."' and MONTH(current_date()) = MONTH(current_date()) AND YEAR(current_date()) = YEAR(current_date()) ");
         // $kode_ins = $kodeins ? $kodeins[0]->kode : null;
    $no_transaksi = $data[0]->no_transaksi;

    $inmaterialStore2 = SoLogCopySaldo::create([
        'no_transaksi' => $no_transaksi,
        'tipe_item' => $request->item_so,
        'status' => 'Copy saldo',
        'copy_user' => Auth::user()->name,
        'filter_date' => $request->tgl_filter,
    ]);

    if ($request->item_so == 'Fabric') {
        $sql_barcode = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname select '','".$no_transaksi."','".$request->item_so."','".$request->tgl_filter."',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty_sisa,unit,'OPEN' status,'".$timestamp."','".$timestamp."' from (select no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, sum(qty_in) qty_in, sum(qty_out) qty_out, round(sum(qty_in) - sum(qty_out),2) qty_sisa,unit from (select 'saldo_awal' id,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty qty_in, 0 qty_out,unit from whs_sa_fabric GROUP BY no_barcode
            UNION
            select 'transaksi',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_out,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok <= '".$request->tgl_filter."' GROUP BY no_barcode
            UNION
            select 'pengeluaran',id_roll,no_rak,id_jo,id_item,no_lot,no_roll,0 qty_in, sum(qty_out) qty_out,satuan from whs_bppb_det a INNER JOIN whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb <= '".$request->tgl_filter."' GROUP BY id_roll
            UNION
            select 'mutasi',idbpb_det,b.kode_lok,b.id_jo,a.id_item,a.no_lot,a.no_roll,0 qty_in, sum(a.qty_mutasi) qty_out,satuan from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where a.status = 'Y' and tgl_mut <= '".$request->tgl_filter."' GROUP BY idbpb_det) a GROUP BY no_barcode) a where qty_sisa > 0 and kode_lok in (".$request->data_partial.")");
    }elseif ($request->item_so == 'Sparepart') {
        $sql_barcode = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname select * from (select '','".$no_transaksi."','".$request->item_so."','".$request->tgl_filter."','' no_barcode,'' kode_lok,'' id_jo,id_item,'' no_lot,'' no_roll,sum(qty_sa) + sum(qty_in) - sum(qty_out) qty,unit,'OPEN' status,'".$timestamp."' q,'".$timestamp."'
            FROM (
            select 
            id_item,
            goods_code,
            itemdesc,
            sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa,
            '0' qty_in,
            '0' qty_out,
            unit 
            from 
            (
            select id_item, kd_barang goods_code, mi.itemdesc, qty qty_sa, '0' qty_in, '0' qty_out, unit   from saldoawal_gd a
            inner join masteritem mi on a.kd_barang = mi.goods_code
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where periode = '2022-01-01' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N'
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '2022-01-01' and bpbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '2022-01-01' and bppbdate < '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) trx
            group by id_item, unit
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '".$request->tgl_filter."' and bpbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '".$request->tgl_filter."' and bppbdate <= '".$request->tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) mutasi
            group by id_item, unit
            having sum(qty_sa) != '0' or sum(qty_in) != '0' or sum(qty_out) != '0' or sum(qty_sa) + sum(qty_in) - sum(qty_out) != '0') a where id_item in (".$request->data_partial.")
            ");
    }else{

    }


    $massage = 'Data berhasil dicopy,nomor: '.$no_transaksi;

    return array(
        "status" => 200,
        "message" => $massage,
        "additional" => [],
        "redirect" => url('/stock_opname')
    );

}

public function replacesaldostok(Request $request)
{
    $timestamp = Carbon::now();
    $no_dok_cs = $request->no_dok_cs;

    $datatgl = DB::connection('mysql_sb')->select("select DISTINCT tgl_filter from whs_saldo_stockopname where no_transaksi = '".$request->no_dok_cs."'");
    $tgl_filter = $datatgl[0]->tgl_filter;

    $inmaterialStore2 = SoLogCopySaldo::create([
        'no_transaksi' => $request->no_dok_cs,
        'tipe_item' => $request->item_so,
        'status' => 'Replace saldo',
        'copy_user' => Auth::user()->name,
        'filter_date' => $tgl_filter,
    ]);

    $copas = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname_old select * from whs_saldo_stockopname where no_transaksi = '".$request->no_dok_cs."'");

    $delete =  DB::connection('mysql_sb')->delete("delete from whs_saldo_stockopname where no_transaksi = '".$request->no_dok_cs."'");

    if ($request->item_so == 'Fabric') {
        $sql_barcode = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname select '','".$request->no_dok_cs."','".$request->item_so."','".$tgl_filter."',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty_sisa,unit,'OPEN' status,".$timestamp."','".$timestamp."' from (select no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll, sum(qty_in) qty_in, sum(qty_out) qty_out, round(sum(qty_in) - sum(qty_out),2) qty_sisa,unit from (select 'saldo_awal' id,no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,qty qty_in, 0 qty_out,unit from whs_sa_fabric GROUP BY no_barcode
            UNION
            select 'transaksi',no_barcode,kode_lok,id_jo,id_item,no_lot,no_roll,sum(qty_sj) qty_in, 0 qty_out,satuan from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.status = 'Y' and tgl_dok <= '".$tgl_filter."' GROUP BY no_barcode
            UNION
            select 'pengeluaran',id_roll,no_rak,id_jo,id_item,no_lot,no_roll,0 qty_in, sum(qty_out) qty_out,satuan from whs_bppb_det a INNER JOIN whs_bppb_h b on b.no_bppb = a.no_bppb where a.status = 'Y' and tgl_bppb <= '".$tgl_filter."' GROUP BY id_roll
            UNION
            select 'mutasi',idbpb_det,b.kode_lok,b.id_jo,a.id_item,a.no_lot,a.no_roll,0 qty_in, sum(a.qty_mutasi) qty_out,satuan from whs_mut_lokasi a INNER JOIN whs_lokasi_inmaterial b on b.no_barcode = a.idbpb_det where a.status = 'Y' and tgl_mut <= '".$tgl_filter."' GROUP BY idbpb_det) a GROUP BY no_barcode) a where qty_sisa > 0");
    }elseif ($request->item_so == 'Sparepart') {
        $sql_barcode = DB::connection('mysql_sb')->select("insert into whs_saldo_stockopname select '','".$request->no_dok_cs."','".$request->item_so."','".$tgl_filter."','' no_barcode,'' kode_lok,'' id_jo,id_item,'' no_lot,'' no_roll,sum(qty_sa) + sum(qty_in) - sum(qty_out) qty,unit,'OPEN' status,'".$timestamp."','".$timestamp."'
            FROM (
            select 
            id_item,
            goods_code,
            itemdesc,
            sum(qty_sa) + sum(qty_in) - sum(qty_out) qty_sa,
            '0' qty_in,
            '0' qty_out,
            unit 
            from 
            (
            select id_item, kd_barang goods_code, mi.itemdesc, qty qty_sa, '0' qty_in, '0' qty_out, unit   from saldoawal_gd a
            inner join masteritem mi on a.kd_barang = mi.goods_code
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where periode = '2022-01-01' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N'
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '2022-01-01' and bpbdate < '".$tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            union 
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '2022-01-01' and bppbdate < '".$tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) trx
            group by id_item, unit
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,sum(bpb.qty) qty_in,'0' qty_out, bpb.unit from bpb 
            inner join masteritem mi on bpb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bpbdate >= '".$tgl_filter."' and bpbdate <= '".$tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bpb.bpbno like 'N%'
            group by mi.id_item, bpb.unit 
            UNION
            select mi.id_item, mi.goods_code, mi.itemdesc, '0' qty_sa,'0' qty_in,sum(bppb.qty) qty_out, bppb.unit from bppb 
            inner join masteritem mi on bppb.id_item = mi.id_item
            inner join mapping_category mc on mi.n_code_category = mc.n_id
            where bppbdate >= '".$tgl_filter."' and bppbdate <= '".$tgl_filter."' and mi.mattype = 'N' and mc.description = 'PERSEDIAAN SPAREPARTS - FACTORY SUPPLIES' and non_aktif = 'N' and bppb.bppbno like 'SJ-N%'
            group by mi.id_item, bppb.unit
            ) mutasi
            group by id_item, unit
            having sum(qty_sa) != '0' or sum(qty_in) != '0' or sum(qty_out) != '0' or sum(qty_sa) + sum(qty_in) - sum(qty_out) != '0'
            ");
    }else{

    }


    $massage = $no_dok_cs . ' Sudah diubah!';

    return array(
        "status" => 200,
        "message" => $massage,
        "additional" => [],
        "redirect" => url('/stock_opname')
    );

}

public function stokopname(Request $request)
{
    if ($request->ajax()) {
        $additionalQuery = "";
        $keywordQuery = "";

        // if ($request->kode_lok != 'ALL') {
        //     $where = " and a.kode_lok = '" . $request->kode_lok . "' ";
        // }else{
        //     $where = "";
        // }

        // if ($request->status != 'ALL') {
        //     $where2 = " and a.status = '" . $request->status . "' ";
        // }else{
        //     $where2 = "";
        // }


        $data_inmaterial = DB::connection('mysql_sb')->select("select a.*,IF(qty_scan > 0,'Scanning','Pending') status,COALESCE(qty_scan,0) qty_scan, COALESCE(qty_roll_scan,0) qty_roll_scan from (select * from (select a.* from (select no_transaksi,a.tipe_item,kode_lok,GROUP_CONCAT(distinct b.itemdesc) itemdesc,round(sum(qty),2) qty, COUNT(no_barcode) qty_roll  from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.tipe_item = 'Fabric' GROUP BY kode_lok,no_transaksi) a) a left join (select id,no_transaksi no_trans from whs_log_copysaldo where status = 'Copy saldo' GROUP BY no_transaksi) b on b.no_trans = a.no_transaksi where no_transaksi = '" . $request->no_transaksi . "' order by kode_lok asc) a LEFT JOIN
            (select no_transaksi notr,lokasi_aktual,sum(qty) qty_scan,COUNT(no_barcode) qty_roll_scan from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen where no_transaksi = '" . $request->no_transaksi . "' GROUP BY no_transaksi,lokasi_aktual) b on b.notr = a.no_transaksi and b.lokasi_aktual = a.kode_lok");


        return DataTables::of($data_inmaterial)->toJson();
    }

    $mrak = DB::connection('mysql_sb')->table('whs_master_lokasi')->select('kode_lok')->where('status', '=', 'Active')->get();
    $status_so = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'status_stok_opname')->where('status', '=', 'Active')->get();
    $no_transaksi = DB::connection('mysql_sb')->select("select DISTINCT no_transaksi, created_at from whs_saldo_stockopname where tipe_item = 'Fabric' and status != 'CANCEL' ORDER BY created_at desc");

    // dd($no_transaksi);

    return view("stock_opname.stok-opname", ['no_transaksi' => $no_transaksi,'status_so' => $status_so,'mrak' => $mrak,"page" => "stock_opname"]);
}

public function prosesscanso($lok = 0,$nodok = 0)
{

    $datadok = DB::connection('mysql_sb')->select("select no_transaksi from whs_log_copysaldo where id = '$nodok'");
    $no_transaksi = $datadok[0]->no_transaksi;

    $notrans = DB::connection('mysql_sb')->select("select CONCAT('$no_transaksi','-',nomor) no_transaksi from (select IF(MAX(SUBSTR(no_dokumen,18,6)) is null,'000001',LPAD(MAX(SUBSTR(no_dokumen,18,6))+1,6,0)) nomor from whs_so_h where no_transaksi = '$no_transaksi') a");

    $lokasi = DB::connection('mysql_sb')->select("select DISTINCT UPPER(kode_lok) kode_lok,IF(kode_lok = 'AREA DUCKDOWN',UPPER(kode_lok),CONCAT(UPPER(kode_lok),' FABRIC WAREHOUSE RACK')) rak from whs_master_lokasi where kode_lok = '$lok'");

    $qty_rak = DB::connection('mysql_sb')->select("select sum(qty) qty from (select round(SUM(qty),2) qty from whs_saldo_stockopname where kode_lok = '$lok' and no_transaksi = '$no_transaksi'
        UNION
        select -coalesce(round(SUM(qty),2),0) qty_scan from whs_so_detail a inner join whs_so_h b on a.no_dokumen = b.no_dokumen where lokasi_aktual = '$lok' and no_transaksi = '$no_transaksi') a");



    return view('stock_opname.proses-scan-so', ['notrans' => $notrans,'qty_rak' => $qty_rak,'lokasi' => $lokasi,"page" => "stock_opname"]);
}

public function deletesotemp(Request $request)
{

    $deletescan = SoDetailTemp::where('no_barcode',$request['no_barcode'])->delete();

}


public function getbarcodeso(Request $request)
{
        // $barcode = DB::connection('mysql_sb')->select("select no_barcode,kode_lok,id_item,id_jo,no_lot,no_roll,qty,unit from whs_saldo_stockopname where no_barcode = '".$request->no_barcode."'");

    $barcode = DB::connection('mysql_sb')->table('whs_saldo_stockopname')->selectRaw('whs_saldo_stockopname.no_barcode, whs_saldo_stockopname.kode_lok, whs_saldo_stockopname.id_item, whs_saldo_stockopname.id_jo, whs_saldo_stockopname.no_lot, whs_saldo_stockopname.no_roll,whs_saldo_stockopname.qty,whs_saldo_stockopname.unit,masteritem.itemdesc')->leftJoin('masteritem', 'masteritem.id_item', '=', 'whs_saldo_stockopname.id_item')->leftJoin('whs_so_detail', 'whs_so_detail.no_barcode', '=', 'whs_saldo_stockopname.no_barcode')->where('whs_saldo_stockopname.no_barcode', $request->no_barcode)->whereNull('whs_so_detail.no_barcode')->first();
        // dd($barcode);

    return json_encode($barcode);
}

public function simpanbarcodeso(Request $request)
{
    $validatedRequest = $request->validate([
        "no_barcode" => "required",
        "qty" => "required|min:0.1",
        "id_item" => "required",
        "id_jo" => "required",
    ]);

    if ($validatedRequest["qty"] > 0) {
        $SoDetailTempStore = SoDetailTemp::create([
            'no_barcode' => $validatedRequest['no_barcode'],
            'lokasi_scan' => $request['lokasi_scan'],
            'lokasi_aktual' => $request['lokasi_so'],
            'id_item' => $validatedRequest['id_item'],
            'id_jo' => $validatedRequest['id_jo'],
            'no_lot' => $request['no_lot'],
            'no_roll' => $request['no_roll'],
            'qty' => $validatedRequest['qty'],
            'qty_old' => 0,
            'unit' => $request['unit'],
            'created_by' => Auth::user()->name,
        ]);

        if ($SoDetailTempStore) {
            return array(
                "status" => 200,
                "message" => "",
                "additional" => [],
            );
        }
    }

}

public function editbarcodeso(Request $request)
{
    $validatedRequest = $request->validate([
        "barcode" => "required",
        "qty" => "required|min:0.1",
        "qty_old" => "required",
    ]);

    if ($validatedRequest["qty"] > 0) {
        $updateLokasi = SoDetailTemp::where('no_barcode', $validatedRequest['barcode'])->update([
            'qty' => $validatedRequest['qty'],
            'qty_old' => $validatedRequest['qty_old']
        ]);

        return array(
            "status" => 200,
            "message" => "",
            "additional" => [],
        );
    }

}

public function listscanbarcode(Request $request)
{
    if ($request->ajax()) {
        $additionalQuery = "";
        $keywordQuery = "";

        $data_scan = DB::connection('mysql_sb')->select("select a.id,a.no_barcode,a.id_item,a.id_jo,c.itemdesc,b.qty,a.qty qty_scan,a.unit,a.lokasi_aktual,a.lokasi_scan,a.qty_old from whs_so_detail_temp a INNER JOIN whs_saldo_stockopname b on b.no_barcode = a.no_barcode INNER JOIN masteritem c on c.id_item = a.id_item where a.created_by  = '".Auth::user()->name."' and a.lokasi_scan = '".$request->kode_lok."' GROUP BY a.no_barcode");

        return DataTables::of($data_scan)->toJson();
    }

}

public function getsumbarcodeso(Request $request)
{
        // $barcode = DB::connection('mysql_sb')->select("select no_barcode,kode_lok,id_item,id_jo,no_lot,no_roll,qty,unit from whs_saldo_stockopname where no_barcode = '".$request->no_barcode."'");

    $sumbarcode = DB::connection('mysql_sb')->table('whs_so_detail_temp')->selectRaw('COALESCE(round(SUM(qty),2),0) qty')->where('created_by', Auth::user()->name)->where('lokasi_scan', $request->kode_lok)->first();
        // dd($barcode);

    return json_encode($sumbarcode);
}

public function laporanstokopname(Request $request)
{
    if ($request->ajax()) {
        $additionalQuery = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_filter >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_filter <= '" . $request->dateTo . "' ";
        }

        if ($request->itemSO == 'Fabric') {
            $data_opname = DB::connection('mysql_sb')->select("select *,FORMAT(qty,2) qty_show,FORMAT(qty_so,2) qty_so_show,FORMAT(qty_sisa,2) qty_sisa_show from (select a.*,COALESCE(qty_scan,0) qty_so, round(a.qty - COALESCE(qty_scan,0),2) qty_sisa from(select a.id,a.status,a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(sum(a.qty),2) qty,a.unit from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.tipe_item = '" . $request->itemSO . "' " . $additionalQuery . " group by no_transaksi) a left join (select no_transaksi notr,lokasi_aktual,id_jo,id_item,sum(qty) qty_scan,COUNT(no_barcode) qty_roll_scan from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen GROUP BY no_transaksi) b on b.notr = a.no_transaksi) a ");
            //select a.*,COALESCE(qty_scan,0) qty_so, round(a.qty - COALESCE(qty_scan,0),2) qty_sisa from(select a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(sum(a.qty),2) qty,a.unit from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.tipe_item = '" . $request->itemSO . "' " . $additionalQuery . " group by no_transaksi,kode_lok,id_jo,id_item) a left join (select no_transaksi notr,lokasi_aktual,id_jo,id_item,sum(qty) qty_scan,COUNT(no_barcode) qty_roll_scan from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen GROUP BY no_transaksi,lokasi_aktual,id_item,id_jo) b on b.notr = a.no_transaksi and b.lokasi_aktual = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item
        }elseif ($request->itemSO == 'Sparepart'){
            $data_opname = DB::connection('mysql_sb')->select("select a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(a.qty,2) qty,a.unit,0 qty_so, round(a.qty,2) qty_sisa from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.tipe_item = '" . $request->itemSO . "' " . $additionalQuery . "");
        }else{
            $data_opname = DB::connection('mysql_sb')->select("select '' no_transaksi,'' tipe_item,'' tgl_saldo,'' kode_lok,'' id_jo,'' id_item,'' goods_code,'' itemdesc,'' qty,'' unit,'' qty_so, '' qty_sisa, 0 qty_show, 0 qty_so_show, 0 qty_sisa_show, '' status");

        }


        return DataTables::of($data_opname)->toJson();
    }
    $item_so = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'item_stok_opname')->where('status', '=', 'Active')->get();

    return view("stock_opname.laporan-stok-opname", ['item_so' => $item_so, "page" => "stock_opname"]);
}

public function export_excel_laporanso(Request $request)
{
    return Excel::download(new ExportLaporanStokOpname($request->itemso, $request->from, $request->to), 'Laporan_stock_opname.xlsx');
}

public function detailstokopname(Request $request)
{
    if ($request->ajax()) {
        $additionalQuery = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and a.tgl_filter >= '" . $request->dateFrom . "' ";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and a.tgl_filter <= '" . $request->dateTo . "' ";
        }

        if ($request->itemSO == 'Fabric') {
            $data_opname = DB::connection('mysql_sb')->select("select d.tipe_item,a.no_dokumen,a.tgl_dokumen,b.no_barcode,lokasi_scan,lokasi_aktual,b.id_jo,b.id_item,c.goods_code,c.itemdesc,b.no_lot,b.no_roll,d.qty qty_so,b.qty,b.unit,a.status,a.created_by,a.created_at from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen INNER JOIN masteritem c on c.id_item = b.id_item left join whs_saldo_stockopname d on d.no_barcode = b.no_barcode and d.no_transaksi = a.no_transaksi where d.tipe_item = '" . $request->itemSO . "' and a.tgl_dokumen BETWEEN '" . $request->dateFrom . "' and '" . $request->dateTo . "' group by b.id");
        }else{
            $data_opname = DB::connection('mysql_sb')->select("select '' tipe_item, '' no_dokumen,'' tgl_dokumen,'' no_barcode,'' lokasi_scan,'' lokasi_aktual,'' id_jo,'' id_item,'' goods_code,'' itemdesc,'' no_lot,'' no_roll,'' qty_so,'' qty,'' unit,'' status,'' created_by,'' created_at");

        }


        return DataTables::of($data_opname)->toJson();
    }
    $item_so = DB::connection('mysql_sb')->table('whs_master_pilihan')->select('id', 'nama_pilihan')->where('type_pilihan', '=', 'item_stok_opname')->where('status', '=', 'Active')->get();

    return view("stock_opname.detail-stok-opname", ['item_so' => $item_so, "page" => "stock_opname"]);
}

public function export_excel_detailso(Request $request)
{
    return Excel::download(new ExportDetailStokOpname($request->itemso, $request->from, $request->to), 'list_detail_stock_opname.xlsx');
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    // whs_saldo_stockopname whs_log_copysaldo
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validatedRequest = $request->validate([
            "txt_qty_scan" => "required|min:0.1",
        ]);

        $no_transaksi = substr($request['txt_no_dokumen'],0,16);

        if (intval($validatedRequest['txt_qty_scan']) > 0) {

            $soheaderstore = SoHeader::create([
                'no_transaksi' => $no_transaksi,
                'no_dokumen' => $request['txt_no_dokumen'],
                'tgl_dokumen' => $request['txt_tgl_so'],
                'status' => 'Post',
                'created_by' => Auth::user()->name,
            ]);

            $so_detail = DB::connection('mysql_sb')->insert("insert into whs_so_detail select '','".$request['txt_no_dokumen']."',no_barcode,lokasi_scan,lokasi_aktual,id_item,id_jo,no_lot,no_roll,qty,qty_old,unit,created_by,created_at,updated_at from whs_so_detail_temp where lokasi_scan = '".$request['txt_lokasi_h']."' and created_by = '".Auth::user()->name."'");
            $so_detail_temp = SoDetailTemp::where('created_by',Auth::user()->name)->where('lokasi_scan',$request['txt_lokasi_h'])->delete();

            $massage = $request['txt_no_dokumen'] . ' Saved Succesfully';
            $stat = 200;

        }else{
            $massage = ' Please Input Data';
            $stat = 400;
        }

        return array(
            "status" =>  $stat,
            "message" => $massage,
            "additional" => [],
            "redirect" => url('/so/list-stok-opname')
        );
        //
    }

    public function showdetailso($id)
    {

        $datadok = DB::connection('mysql_sb')->select("select no_transaksi,tipe_item,tgl_filter,status from whs_saldo_stockopname where id = '$id' GROUP BY no_transaksi");
        $no_transaksi = $datadok ? $datadok[0]->no_transaksi : null;
        $tipe_item = $datadok ? $datadok[0]->tipe_item : null;
        $tgl_filter = $datadok ? $datadok[0]->tgl_filter : null;
        $status = $datadok ? $datadok[0]->status : null;


        // $lokasi = DB::connection('mysql_sb')->select("select DISTINCT UPPER(kode_lok) kode_lok,IF(kode_lok = 'AREA DUCKDOWN',UPPER(kode_lok),CONCAT(UPPER(kode_lok),' FABRIC WAREHOUSE RACK')) rak from whs_master_lokasi where kode_lok = '$lok'");


        return view("stock_opname.show-detail-so", ['no_transaksi' => $no_transaksi,'tipe_item' => $tipe_item,'tgl_filter' => $tgl_filter,'status' => $status,"page" => "stock_opname"]);
    }

    public function listsodetailshow(Request $request)
    {
        if ($request->ajax()) {
            $additionalQuery = "";
            $keywordQuery = "";

            $data_scan = DB::connection('mysql_sb')->select("select *,FORMAT(qty,2) qty_show,FORMAT(qty_so,2) qty_so_show,FORMAT(qty_sisa,2) qty_sisa_show from (select a.*,COALESCE(qty_scan,0) qty_so, round(a.qty - COALESCE(qty_scan,0),2) qty_sisa from(select a.no_transaksi,a.tipe_item,a.tgl_filter tgl_saldo,a.kode_lok,a.id_jo,a.id_item,b.goods_code,b.itemdesc,round(sum(a.qty),2) qty,a.unit from whs_saldo_stockopname a inner join masteritem b on b.id_item = a.id_item where a.no_transaksi = '" . $request->no_dokumen . "' " . $additionalQuery . " group by no_transaksi,kode_lok,id_jo,id_item) a left join (select no_transaksi notr,lokasi_aktual,id_jo,id_item,sum(qty) qty_scan,COUNT(no_barcode) qty_roll_scan from whs_so_h a INNER JOIN whs_so_detail b on b.no_dokumen = a.no_dokumen GROUP BY no_transaksi,lokasi_aktual,id_item,id_jo) b on b.notr = a.no_transaksi and b.lokasi_aktual = a.kode_lok and b.id_jo = a.id_jo and b.id_item = a.id_item) a order by kode_lok asc");

            return DataTables::of($data_scan)->toJson();
        }

    }

    public function export_excel_laporanso_detail(Request $request)
    {
        return Excel::download(new ExportLaporanStokOpnameDetail($request->no_transaksi, $request->itemso), 'Laporan_stock_opname.xlsx');
    }

    public function cancelreportso(Request $request)
    {
            $timestamp = Carbon::now();
            $updateLokasi = SoCopySaldo::where('no_transaksi', $request['no_transaksi'])->update([
                'status' => 'CANCEL',
                // 'approved_by' => Auth::user()->name,
                // 'approved_date' => $timestamp,
            ]);
        
        $massage = '';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                // "redirect" => url('/in-material')
            );
        
    }

    public function draftreportso(Request $request)
    {
            $timestamp = Carbon::now();
            $updateLokasi = SoCopySaldo::where('no_transaksi', $request['no_transaksi'])->update([
                'status' => 'DRAFT',
                // 'approved_by' => Auth::user()->name,
                // 'approved_date' => $timestamp,
            ]);
        
        $massage = '';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                // "redirect" => url('/in-material')
            );
        
    }

    public function finalreportso(Request $request)
    {
            $timestamp = Carbon::now();
            $updateLokasi = SoCopySaldo::where('no_transaksi', $request['no_transaksi'])->update([
                'status' => 'FINAL',
                // 'approved_by' => Auth::user()->name,
                // 'approved_date' => $timestamp,
            ]);
        
        $massage = '';

            return array(
                "status" => 200,
                "message" => $massage,
                "additional" => [],
                // "redirect" => url('/in-material')
            );
        
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
