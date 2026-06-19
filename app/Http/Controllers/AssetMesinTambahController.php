<?php

namespace App\Http\Controllers;

use App\Imports\ImportIE_MasterProcess;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AssetMesinTambahController extends Controller
{
    public function asset_mesin_tambah(Request $request)
    {
        $tgl_trans = '2026-05-01';
        $supplierList = DB::connection('mysql_sb')->table('mastersupplier')
            ->select('id_supplier', 'Supplier')
            ->where('tipe_sup', '=', 'S')
            ->orderBy('Supplier', 'ASC')
            ->get();

        $bpbList = DB::connection('mysql_sb')->select("
        SELECT DISTINCT
            b.bpbno,
            b.bpbno_int,
            ms.supplier
        FROM bpb b
        INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
        INNER JOIN masteritem mi on b.id_item = mi.id_item
        WHERE b.bpbdate >= '$tgl_trans' AND b.bpbno LIKE 'N%' AND mi.n_code_category = '4' AND b.cancel = 'N'
        ORDER BY bpbno_int ASC, bpbdate ASC;
        ");

        $jenisList = DB::table('asset_master_jenis_mesin')
            ->select('id_jenis', 'jenis', 'merk', 'tipe', 'id_supplier')
            ->orderBy('jenis', 'ASC')
            ->get();

        if ($request->ajax()) {
            $data_input = DB::select("SELECT * FROM asset_master_jenis_mesin ORDER BY id_jenis DESC");

            $supplierMap = $supplierList->keyBy('id_supplier');
            foreach ($data_input as $row) {
                $row->supplier = $supplierMap[$row->id_supplier]->Supplier ?? '-';
            }

            return DataTables::of($data_input)->toJson();
        }

        // For non-AJAX (initial page load)
        return view('asset_management.mesin_tambah', [
            'page' => 'dashboard-asset',
            'subPageGroup' => 'asset-mesin',
            'subPage' => 'asset_mesin_tambah',
            'containerFluid' => true,
            'supplierList' => $supplierList,
            'bpbList' => $bpbList,
            'jenisList' => $jenisList,
        ]);
    }

    public function get_bpb_detail(Request $request)
    {
        $data = DB::connection('mysql_sb')->select("SELECT id, b.id_item, mi.itemdesc, qty, unit, b.id_supplier FROM bpb b
                INNER JOIN mastersupplier ms ON ms.Id_Supplier = b.id_supplier
                INNER JOIN masteritem mi on b.id_item = mi.id_item
                WHERE bpbno = ? AND mi.n_code_category = '4' AND b.cancel = 'N'", [$request->bpbno]);

        return DataTables::of($data)->toJson();
    }
}
