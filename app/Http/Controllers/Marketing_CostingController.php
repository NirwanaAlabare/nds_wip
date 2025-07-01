<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
use App\Imports\ImportPPIC_SO;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Style;

class Marketing_CostingController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::connection('mysql_sb')->select("SELECT
ac.id,
ac.cost_no,
DATE_FORMAT(ac.cost_date, '%d-%b-%Y') AS cost_date,
ac.styleno,
Supplier as buyer,
ac.kpno,
product_group,
product_item,
brand,
main_dest,
ac.notes,
ac.app1,
ac.app1_by,
ac.username,
DATE_FORMAT(ac.dateinput, '%d-%b-%Y %H:%i:%s') AS dateinput,
ac.status,
so.id id_so,
jd.id_jo,
case
when id_so is null and id_jo is null then 'Costing'
when id_so is not null and id_jo is null then 'SO'
when id_so is not null and id_jo is not null then 'BOM'
end as status_order
from act_costing ac
inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
inner join masterproduct mp on ac.id_product = mp.id
left join so on ac.id = so.id_cost
left join jo_det jd on so.id = jd.id_so
where ac.cost_date >= '$tgl_awal' AND ac.cost_date <= '$tgl_akhir'
order by ac.cost_date desc, ac.dateinput desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        $data_buyer = DB::connection('mysql_sb')->select("SELECT id_supplier isi,supplier tampil
        from mastersupplier
        where tipe_sup='C' order by supplier");

        $data_curr = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil from
		masterpilihan where kode_pilihan='Curr'");

        $data_pgroup = DB::connection('mysql_sb')->select("SELECT product_group isi,product_group tampil from
        masterproduct group by product_group");

        $data_ship = DB::connection('mysql_sb')->select("SELECT id isi,shipmode tampil from mastershipmode");

        $data_vat = DB::connection('mysql_sb')->select("SELECT percentage isi, percentage tampil
        from mtax where category_tax = 'PPN'  and cancel = 'N'
        GROUP BY idtax
        UNION
        select 0 isi, 0 tampil");

        // $data_status = DB::connection('mysql_sb')->select("select nama_pilihan isi,nama_pilihan tampil from
        // 							masterpilihan where kode_pilihan='ST_CST'");

        return view(
            'marketing.master_costing',
            [
                'page' => 'dashboard-marketing',
                "subPageGroup" => "marketing-master",
                "subPage" => "marketing-master-costing",
                'data_buyer' => $data_buyer,
                'data_curr' => $data_curr,
                'data_pgroup' => $data_pgroup,
                'data_ship' => $data_ship,
                'data_vat' => $data_vat,
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }

    public function getprod_item_costing(Request $request)
    {
        $prod_group = $request->prod_group;

        $data_prod_item = DB::connection('mysql_sb')->select(
            "SELECT id AS isi, product_item AS tampil FROM masterproduct WHERE product_group = ?",
            [$prod_group]
        );

        return response()->json($data_prod_item);
    }

    public function store_master_costing_production(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $cbobuyer = $request->cbobuyer;
        $cbo_tipe = $request->cbo_tipe;
        $txtdel_date = $request->txtdel_date;
        $cbop_group = $request->cbop_group;
        $cbop_item = $request->cbop_item;
        $txtbrand = $request->txtbrand;
        $cbo_ship = $request->cbo_ship;
        $txtdest = $request->txtdest;
        $txtstyle = $request->txtstyle;
        $txtqty = $request->txtqty;
        $txtsmv = $request->txtsmv;
        $txtmin_prod = $request->txtmin_prod;
        $cbocurr = $request->cbocurr;
        $txtrate_jual = $request->txtrate_jual;
        $txtcfm_price = $request->txtcfm_price;
        $cbo_vat = $request->cbo_vat;
        $txtfinal_cfm_price = $request->txtfinal_cfm_price;
        $txtnotes = $request->txtnotes;


        $today = Carbon::now();
        $datePart = $today->format('my'); // e.g. 0625
        $prefix = "CST/$datePart";
        $currentYear = $today->year;

        // Get the last sequence number used in this year
        $latestNumber = DB::connection('mysql_sb')->selectOne("SELECT CAST(RIGHT(cost_no, 5) AS UNSIGNED) AS number
        FROM act_costing
        WHERE  YEAR(cost_date) = ?
        ORDER BY number DESC
        LIMIT 1", [$currentYear]);

        $nextNumber = $latestNumber ? $latestNumber->number + 1 : 1;
        $sequence = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $cost_no = "$prefix/$sequence";

        $get_kode_buyer = DB::connection('mysql_sb')->select("SELECT supplier_code, Supplier FROM mastersupplier WHERE Id_Supplier = ?", [$cbobuyer]);

        $kode_buyer = !empty($get_kode_buyer) ? $get_kode_buyer[0]->supplier_code : null;
        $nama_buyer = $get_kode_buyer[0]->Supplier; // ✅ get the Supplier name
        if ($cbo_tipe == 'standard') {
            $kode_cek_ws = $kode_buyer . '-' . $currentYear;
        } else {
            $kode_cek_ws = 'GLB' . '-' . $kode_buyer . '-' . $currentYear;
        }


        // Check for existing
        $cek_no_ws = DB::connection('mysql_sb')->select("SELECT BPBNo FROM tempbpb WHERE Mattype = ?", [$kode_cek_ws]);

        if (empty($cek_no_ws)) {
            $bpbno = 1;
            DB::connection('mysql_sb')->insert("
        INSERT INTO tempbpb (mattype, bpbno) VALUES (?, ?)", [$kode_cek_ws, $bpbno]);
        } else {
            // Increment the existing BPBNo
            $bpbno = $cek_no_ws[0]->BPBNo + 1;

            // Update the record with the new value
            DB::connection('mysql_sb')->update("
        UPDATE tempbpb SET bpbno = ? WHERE Mattype = ?", [$bpbno, $kode_cek_ws]);
        }

        // Format the BPBNo to 3 digits
        $no_urut = str_pad($bpbno, 3, '0', STR_PAD_LEFT);

        // Create the final WS code
        $ws = $kode_buyer . '/' . $datePart . '/' . $no_urut;

        DB::connection('mysql_sb')->insert("INSERT
        into act_costing (id_pre_cost,cost_no,cost_date,kpno,id_smode,smv_min,smv_sec,book_min,book_sec,notes,deldate,
		attach_file,id_buyer,id_product,styleno,qty,status,status_order,username,curr,
        vat,deal_allow,ga_cost,unit,cfm_price,comm_cost,dateinput,type_ws,app1,app1_by,app1_date,brand,main_dest,aktif,rate)
		values ('0','$cost_no','$timestamp','$ws','$cbo_ship','$txtsmv','0','$txtmin_prod','0','$txtnotes','$txtdel_date',
		'','$cbobuyer','$cbop_item','$txtstyle','$txtqty','CONFIRM','','$user','$cbocurr',
        '$cbo_vat','0','0','PCS','$txtcfm_price','0','$today','STD','W','','','$txtbrand','$txtdest','Y',$txtrate_jual)");


        return response()->json([
            'status' => 'success',
            'message' => 'Costing has been saved!',
            'data' => [
                'buyer' => $nama_buyer,
                'ws' => $ws,
                'style' => $txtstyle,
                'cost_no' => $cost_no
            ]
        ]);
    }

    public function edit_costing($id)
    {
        $user = Auth::user()->name;

        $get_data_cost = DB::connection('mysql_sb')->select("SELECT
cost_no,
cost_date,
kpno,
curr,
cfm_price,
vat,
id_smode,
shipmode,
smv_min,
smv_sec,
book_min,
book_sec,
notes,
deldate,
ac.status,
id_buyer,
ms.Supplier buyer,
mp.product_group,
mp.product_item,
ac.id_product,
styleno,
ac.qty qty_order,
ac.type_ws,
ac.main_dest,
ac.brand,
ac.rate,
ac.username,
ac.dateinput
from act_costing ac
inner join masterproduct mp on ac.id_product = mp.id
inner join mastersupplier ms on ac.id_buyer = ms.Id_Supplier
inner join mastershipmode msh on ac.id_smode = msh.id
where ac.id = ?", [$id]);

        $no_cost = $get_data_cost[0]->cost_no;
        $buyer = $get_data_cost[0]->buyer;
        $style = $get_data_cost[0]->styleno;
        $tgl_input = $get_data_cost[0]->dateinput;
        $ws = $get_data_cost[0]->kpno;
        $curr = $get_data_cost[0]->curr;
        $cfm_price = $get_data_cost[0]->cfm_price;
        $deldate = $get_data_cost[0]->deldate;
        $id_smode = $get_data_cost[0]->id_smode;
        $type_ws = $get_data_cost[0]->type_ws;
        $main_dest = $get_data_cost[0]->main_dest;
        $brand = $get_data_cost[0]->brand;
        $qty_order = $get_data_cost[0]->qty_order;
        $vat = $get_data_cost[0]->vat;
        $product_group = $get_data_cost[0]->product_group;
        $product_item = $get_data_cost[0]->product_item;
        $id_product = $get_data_cost[0]->id_product;
        $txtsmv = $get_data_cost[0]->smv_min;
        $txtmin_prod = $get_data_cost[0]->book_min;
        $txtrate = $get_data_cost[0]->rate;


        $data_pgroup = DB::connection('mysql_sb')->select("SELECT product_group isi,product_group tampil from
        masterproduct group by product_group order by product_group asc");

        $data_pitem = DB::connection('mysql_sb')->select("SELECT id isi,product_item tampil from
        masterproduct where id ='$id_product' order by product_item asc");

        $data_curr = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil from
		masterpilihan where kode_pilihan='Curr'");

        $data_ship = DB::connection('mysql_sb')->select("SELECT id isi,shipmode tampil from mastershipmode");

        $data_vat = DB::connection('mysql_sb')->select("SELECT percentage isi, percentage tampil
        from mtax where category_tax = 'PPN'  and cancel = 'N'
        GROUP BY idtax
        UNION
        select 0 isi, 0 tampil");

        $data_jns_cat = DB::connection('mysql_sb')->select("SELECT kode_group isi,kode_group tampil from mastergroup");

        $data_unit = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil from
							masterpilihan where kode_pilihan='Satuan'");

        return view(
            'marketing.edit_costing',
            [
                'page' => 'dashboard-marketing',
                "subPageGroup" => "marketing-master",
                "subPage" => "marketing-master-costing",
                "containerFluid" => true,
                "no_cost" => $no_cost,
                "buyer" => $buyer,
                "style" => $style,
                "tgl_input" => $tgl_input,
                "ws" => $ws,
                "curr" => $curr,
                "cfm_price" => $cfm_price,
                "deldate" => $deldate,
                "id_smode" => $id_smode,
                "type_ws" => $type_ws,
                "main_dest" => $main_dest,
                "brand" => $brand,
                "qty_order" => $qty_order,
                "vat" => $vat,
                "product_group" => $product_group,
                "product_item" => $product_item,
                "data_pgroup" => $data_pgroup,
                "data_pitem" => $data_pitem,
                "data_curr" => $data_curr,
                "data_ship" => $data_ship,
                "data_vat" => $data_vat,
                "data_unit" => $data_unit,
                "id_product" => $id_product,
                "txtsmv" => $txtsmv,
                "txtmin_prod" => $txtmin_prod,
                "txtrate" => $txtrate,
                "id" => $id,
                "user" => $user,
                "data_jns_cat" => $data_jns_cat
            ]
        );
    }


    public function update_header_master_costing(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        $id_cost = $request->id_cost;
        $cbocurr = $request->cbocurr;
        $txtcfm_price = $request->txtcfm_price;
        $txtdel_date = $request->txtdel_date;
        $cbop_item = $request->cbop_item;
        $cbo_ship = $request->cbo_ship;
        $txtmain_dest = $request->txtmain_dest;
        $txtstyle = $request->txtstyle;
        $txtqty_order = $request->txtqty_order;
        $txtbrand = $request->txtbrand;
        $cbo_vat = $request->cbo_vat;
        $txtrate = $request->txtrate;
        $txtfinal_cfm_price = $request->txtfinal_cfm_price;
        $txtsmv = $request->txtsmv;
        $txtmin_prod = $request->txtmin_prod;
        $today = Carbon::now();


        DB::connection('mysql_sb')->update("UPDATE act_costing
        set styleno = '$txtstyle',
        id_product = '$cbop_item',
        curr = '$cbocurr',
        deldate = '$txtdel_date',
        id_smode = '$cbo_ship',
        main_dest = '$txtmain_dest',
        brand = '$txtbrand',
        qty = '$txtqty_order',
        vat = '$cbo_vat',
        rate = '$txtrate',
        cfm_price = '$txtfinal_cfm_price',
        smv_min = '$txtsmv',
        book_min = '$txtmin_prod'
        where id = '$id_cost' ");


        return response()->json([
            'status' => 'success',
            'message' => 'Costing has been saved!',
            'data' => [
                'style' => $txtstyle
            ]
        ]);
    }
    public function get_jns_costing_material(Request $request)
    {
        $data_jns = DB::connection('mysql_sb')->select(
            "SELECT kode_group isi,nama_group tampil from mastergroup"
        );

        return response()->json($data_jns);
    }

    public function get_material_costing(Request $request)
    {
        $cbo_cat = $request->cbo_cat;
        $cbo_jns = $request->cbo_jns;

        if ($cbo_cat === 'material') {
            // Assuming cbo_jns = kode_group
            $data_mat = DB::connection('mysql_sb')->select(
                "SELECT
                e.id AS isi,
                CONCAT(e.id, ' ', nama_group, ' ', nama_sub_group, ' ', nama_type, ' ', nama_contents) AS tampil
            FROM mastergroup a
            INNER JOIN mastersubgroup s ON a.id = s.id_group
            INNER JOIN mastertype2 d ON s.id = d.id_sub_group
            INNER JOIN mastercontents e ON d.id = e.id_type
            WHERE
                a.kode_group = ? AND
                a.aktif = 'Y' AND
                s.aktif = 'Y' AND
                d.aktif = 'Y' AND
                e.is_active = 'Y' AND
                e.aktif = 'Y'
            ORDER BY nama_contents ASC",
                [$cbo_jns]
            );
        } elseif ($cbo_cat === 'manufacturing') {
            $data_mat = DB::connection('mysql_sb')->select(
                "SELECT id AS isi,
                    CONCAT(cfcode, ' ', cfdesc) AS tampil
             FROM mastercf
             ORDER BY id DESC"
            );
        } elseif ($cbo_cat === 'other') {
            $data_mat = DB::connection('mysql_sb')->select(
                "SELECT id isi,
              concat(otherscode,' ',othersdesc) tampil
              FROM masterothers
              ORDER BY id DESC"
            );
        } else {
            // Default: return empty if category is not handled
            $data_mat = [];
        }

        return response()->json($data_mat);
    }
}
