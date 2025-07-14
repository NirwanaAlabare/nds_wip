<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class QCInspectProsesPackingListController extends Controller
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
a.tgl_dok,
DATE_FORMAT(a.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
a.no_dok,
no_invoice,
a.supplier,
ms.supplier buyer,
ac.styleno,
b.id_item,
count(no_roll) jml_roll,
count(distinct(no_lot)) jml_lot,
mi.color,
a.type_pch,
min(c.id) id_lok_in_material
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
where a.tgl_dok >= '$tgl_awal' and a.tgl_dok <= '$tgl_akhir' and a.type_pch not like '%Pengembalian dari Produksi%' and b.status = 'Y' and c.status = 'Y' and a.status != 'Cancel'
group by a.tgl_dok, a.no_dok,b.id_item, b.id_jo, b.unit
order by tgl_dok asc, no_dok asc, no_invoice asc, color asc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'qc_inspect.proses_packinglist',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-packing-list",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function qc_inspect_proses_packing_list_det($id_lok_in_material)
    {
        $user = Auth::user()->name;

        $get_header = DB::connection('mysql_sb')->select("SELECT
    a.tgl_dok,
    DATE_FORMAT(a.tgl_dok, '%d-%M-%Y') AS tgl_dok_fix,
    a.no_dok,
    a.supplier,
    ms.supplier buyer,
		ac.styleno,
    no_invoice,
    b.id_item,
    b.id_jo,
    count(no_roll) jml_roll,
    count(distinct(no_lot)) jml_lot,
    mi.color,
    a.type_pch
from signalbit_erp.whs_inmaterial_fabric a
inner join signalbit_erp.whs_inmaterial_fabric_det b on a.no_dok = b.no_dok
left join signalbit_erp.whs_lokasi_inmaterial c on a.no_dok = c.no_dok and b.id_item = c.id_item and b.id_jo = c.id_jo
inner join
(
select no_dok, id_item, id_jo from signalbit_erp.whs_lokasi_inmaterial where id = ?
) d on a.no_dok = d.no_dok and b.id_item = d.id_item and b.id_jo = d.id_jo
inner join signalbit_erp.masteritem mi on b.id_item = mi.id_item
inner join signalbit_erp.jo_det jd on b.id_jo = jd.id_jo
inner join signalbit_erp.so so on jd.id_so = so.id
inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
group by a.tgl_dok, a.no_dok,b.id_item, b.id_jo, b.unit", [$id_lok_in_material]);

        $tgl_dok_fix = $get_header[0]->tgl_dok_fix;
        $no_invoice = $get_header[0]->no_invoice;
        $buyer = $get_header[0]->buyer;
        $styleno = $get_header[0]->styleno;
        $color = $get_header[0]->color;
        $id_item = $get_header[0]->id_item;
        $id_jo = $get_header[0]->id_jo;
        $jml_lot = $get_header[0]->jml_lot;
        $jml_roll = $get_header[0]->jml_roll;
        $type_pch = $get_header[0]->type_pch;

        $data_group = DB::connection('mysql_sb')->select("SELECT
group_inspect isi,
concat(group_inspect, ' - ', name_fabric_group) tampil
from qc_inspect_master_group_inspect");


        return view(
            'qc_inspect.proses_det_packinglist',
            [
                'page' => 'dashboard-qc-inspect',
                "subPageGroup" => "qc-inspect-proses",
                "subPage" => "qc-inspect-proses-packing-list",
                "containerFluid" => true,
                "user" => $user,
                "tgl_dok_fix" => $tgl_dok_fix,
                "no_invoice" => $no_invoice,
                "buyer" => $buyer,
                "styleno" => $styleno,
                "color" => $color,
                "id_item" => $id_item,
                "id_jo" => $id_jo,
                "jml_lot" => $jml_lot,
                "jml_roll" => $jml_roll,
                "type_pch" => $type_pch,
                "data_group" => $data_group
            ]
        );
    }

    public function show_calculate_qc_inspect(Request $request)
    {
        $id_item = $request->id_item;
        $id_jo = $request->id_jo;
        $no_inv = $request->no_inv;
        $user = Auth::user()->name;

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
}
