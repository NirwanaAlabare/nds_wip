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
order by cost_date desc
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

        $data_status = DB::connection('mysql_sb')->select("select nama_pilihan isi,nama_pilihan tampil from
									masterpilihan where kode_pilihan='ST_CST'");

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
                'data_status' => $data_status,
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
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
}
