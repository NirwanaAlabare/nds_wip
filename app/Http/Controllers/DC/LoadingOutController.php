<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoadingOutController extends Controller
{
    public function loading_out(Request $request)
    {
        if ($request->ajax()) {
            $data_input = DB::select("SELECT
            id_part_process,
            picture,
            nm_part_process,
            count(a.id) tot_process,
            ROUND(SUM(b.smv), 3) AS tot_smv,
            ROUND(SUM(b.amv), 3) AS tot_amv,
            a.created_by,
            DATE_FORMAT(a.updated_at, '%d-%m-%y %H:%i:%s') AS tgl_update_fix
            FROM ie_master_part_process a
            inner join ie_master_process b on a.id_process = b.id
            group by id_part_process
            ");

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('dc.loading_out.loading_out', [
            'page' => 'dashboard-dc',
            'subPageGroup' => 'loading-dc',
            'subPage' => 'loading_out',
            'containerFluid' => true,
        ]);
    }

    public function input_loading_out(Request $request)
    {
        $user = Auth::user()->name;

        $data_supplier = DB::connection('mysql_sb')->select("SELECT Id_Supplier as isi, Supplier as tampil
        from mastersupplier where tipe_sup = 'S'
        order by SUpplier asc");

        $data_dok = DB::connection('mysql_sb')->select("SELECT nama_pilihan isi,nama_pilihan tampil
            from masterpilihan where kode_pilihan='Status KB Out'");

        $data_jns = DB::connection('mysql_sb')->select("SELECT nama_trans isi,nama_trans tampil from mastertransaksi where
                            jenis_trans='OUT' and jns_gudang = 'FACC' order by id");

        return view(
            'dc.loading_out.input_loading_out',
            [
                'page' => 'dashboard-dc',
                'subPageGroup' => 'loading-dc',
                'subPage' => 'loading_out',
                'data_supplier' => $data_supplier,
                'data_dok' => $data_dok,
                'data_jns' => $data_jns,
                "containerFluid" => true,
                "user" => $user
            ]
        );
    }


    public function getpo_loading_out(Request $request)
    {

        $user = Auth::user()->name;
        $id_supplier = $request->cbo_sup;

        $data_po = DB::connection('mysql_sb')->select("SELECT id as isi, pono as tampil
        from po_header
        where jenis = 'P' and app = 'A' and id_supplier = '$id_supplier'
        order by podate desc
        ");

        $html = "<option value=''>Pilih No PO</option>";

        foreach ($data_po as $datapo) {
            $html .= " <option value='" . $datapo->isi . "'>" . $datapo->tampil . "</option> ";
        }

        return $html;
    }

    public function get_list_po_loading_out(Request $request)
    {
        $id_po = $request->id_po;
        $data_input = DB::connection('mysql_sb')->select("SELECT
            a.id,
            a.id_jo,
            ac.kpno ws,
            jo.jo_no,
            mi.itemdesc,
            mi.id_item,
            a.qty qty_po,
            a.unit
            from po_item a
            inner join jo_det jd on a.id_jo = jd.id_jo
            inner join jo on jd.id_jo = jo.id
            inner join so on jd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join masteritem mi on a.id_gen = mi.id_item
            where id_po = '$id_po' and a.cancel = 'N'
            order by kpno desc
            ");

        return DataTables::of($data_input)->toJson();
    }
}
