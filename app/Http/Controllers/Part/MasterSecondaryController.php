<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\MasterSecondary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class MasterSecondaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterSecondaryQuery = MasterSecondary::query();

            return DataTables::eloquent($masterSecondaryQuery)->filterColumn('kode', function ($query, $keyword) {
                $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('nama_part', function ($query, $keyword) {
                $query->whereRaw("LOWER(jenis) LIKE LOWER('%" . $keyword . "%')");
            })->filterColumn('bag', function ($query, $keyword) {
                $query->whereRaw("LOWER(proses) LIKE LOWER('%" . $keyword . "%')");
            })->order(function ($query) {
                $query->orderBy('tujuan', 'asc')->orderBy('updated_at', 'asc');
            })->toJson();
        }

        $data_tujuan = DB::select("select id isi, tujuan tampil from master_tujuan");

        return view("marker.master-secondary.master-secondary", ["page" => "dashboard-marker",  "subPageGroup" => "master-marker", "subPage" => "master-secondary", 'data_tujuan' => $data_tujuan]);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $validatedRequest = $request->validate([
            "cbotuj" => "required",
            "cbojns" => "required",
            "proses" => "required",
        ]);

        $masterSecondaryCount = MasterSecondary::count();
        $masterSecondaryNumber = intval($masterSecondaryCount) + 1;
        $masterSecondaryCode = 'MS' . sprintf('%05s', $masterSecondaryNumber);

        $masterSecondaryStore = MasterSecondary::create([
            "kode" => $masterSecondaryCode,
            "id_tujuan" => $validatedRequest["cbotuj"],
            "tujuan" => $validatedRequest["cbojns"],
            "proses" =>  strtoupper($validatedRequest["proses"]),
            "cancel" =>  'N',
            'created_by_id' => Auth::user()->id,
            'created_by' => Auth::user()->name,
        ]);

        if ($masterSecondaryStore) {
            return array(
                "status" => 200,
                "message" => $masterSecondaryCode,
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */

    public function show_master_secondary(Request $request)
    {
        $data_master_secondary = DB::select("SELECT * FROM master_secondary where id = '$request->id_c'");
        return json_encode($data_master_secondary[0]);
    }

    public function update_master_secondary(Request $request)
    {
        $validatedRequest = $request->validate([
            "txttuj" => "required",
            "txtjns" => "required",
            "txtproses" => "required",
        ]);

        $update_master_secondary = DB::update("
            update master_secondary
            set
            id_tujuan = '" . $validatedRequest['txttuj'] . "',
            tujuan = '" . $validatedRequest['txtjns'] . "',
            proses = '" . $validatedRequest['txtproses'] . "',
            cancel = 'N'
            where id = '$request->id_c'");

        if ($update_master_secondary) {
            return array(
                'status' => 300,
                'message' => 'Data Master Secondary "' . $request->id_c . '" berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-master-secondary',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data produksi gagal diubah',
            'redirect' => '',
            'table' => 'datatable-master-secondary',
            'additional' => [],
        );
    }

    public function destroy(MasterSecondary $MasterSecondary, $id = 0)
    {
        $destroyMasterSecondary = MasterSecondary::find($id)->delete();

        if ($destroyMasterSecondary) {
            return array(
                'status' => 200,
                'message' => 'Master Secondary berhasil dihapus',
                'redirect' => '',
                'table' => 'datatable-master-secondary',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Master Secondary gagal dihapus',
            'redirect' => '',
            'table' => 'datatable-master-secondary',
            'additional' => [],
        );
    }
}
