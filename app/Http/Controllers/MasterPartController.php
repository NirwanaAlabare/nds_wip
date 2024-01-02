<?php

namespace App\Http\Controllers;

use App\Models\MasterPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MasterPartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterPartsQuery = MasterPart::query();

            return DataTables::eloquent($masterPartsQuery)->
                filterColumn('kode_master_part', function ($query, $keyword) {
                    $query->whereRaw("LOWER(kode_master_part) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_part', function ($query, $keyword) {
                    $query->whereRaw("LOWER(nama_part) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('bag', function ($query, $keyword) {
                    $query->whereRaw("LOWER(bag) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->orderBy('cancel', 'asc')->orderBy('updated_at', 'desc')->orderBy('kode_master_part', 'desc');
                })->toJson();
        }

        return view("master-part.master-part", ["page" => "dashboard-marker",  "subPageGroup" => "master-marker", "subPage" => "master-part"]);
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
        $validatedRequest = $request->validate([
            "nama_part" => "required",
            "bag" => "required",
        ]);

        $masterPartCount = MasterPart::count();
        $masterPartNumber = intval($masterPartCount) + 1;
        $masterPartCode = 'MP' . sprintf('%05s', $masterPartNumber);

        $masterPartStore = MasterPart::create([
            "kode_master_part" => $masterPartCode,
            "nama_part" => $validatedRequest["nama_part"],
            "bag" => $validatedRequest["bag"],
        ]);

        if ($masterPartStore) {
            return array(
                "status" => 200,
                "message" => $masterPartCode,
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
    public function show(MasterPart $masterPart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterPart $masterPart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MasterPart $masterPart, $id = 0)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_nama_part" => "required",
            "edit_bag" => "required",
        ]);

        $updateMasterPart = MasterPart::where('id', $validatedRequest['edit_id'])->update([
            'nama_part' => $validatedRequest['edit_nama_part'],
            'bag' => $validatedRequest['edit_bag']
        ]);

        if ($updateMasterPart) {
            return array(
                'status' => 200,
                'message' => 'Data master part berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-master-part',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data master part gagal diubah',
            'redirect' => '',
            'table' => 'datatable-master-part',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterPart  $masterPart
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterPart $masterPart, $id = 0)
    {
        $destroyMasterPart = MasterPart::find($id)->delete();

        if ($destroyMasterPart) {
            return array(
                'status' => 200,
                'message' => 'Master Part berhasil dihapus',
                'redirect' => '',
                'table' => 'datatable-master-part',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Master Part gagal dihapus',
            'redirect' => '',
            'table' => 'datatable-master-part',
            'additional' => [],
        );
    }
}
