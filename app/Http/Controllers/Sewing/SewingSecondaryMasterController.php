<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\SewingSecondaryIn;
use App\Models\SignalBit\SewingSecondaryMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

class SewingSecondaryMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sewingSecondaryMaster = SewingSecondaryMaster::where("hidden", "N");

            return DataTables::eloquent($sewingSecondaryMaster)->toJson();
        }

        $allSewingSecondary = SewingSecondaryMaster::get();

        return view("sewing.sewing-secondary-master.sewing-secondary-master", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "sewing-secondary-master", "allSewingSecondary" => $allSewingSecondary]);
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
    public function storeSecondaryMaster(Request $request)
    {
        $validatedRequest = $request->validate([
            "secondary" => "required",
        ]);

        $storeSewingSecondaryMaster = SewingSecondaryMaster::create([
            "secondary" => $validatedRequest['secondary'],
            "created_by" => Auth::user()->id,
            "created_by_username" => Auth::user()->username,
        ]);

        if ($storeSewingSecondaryMaster) {
            return array(
                'status' => 200,
                'message' => 'Secondary Master berhasil dibuat',
                'redirect' => '',
                'table' => 'datatable-secondary-master',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Secondary Master tidak berhasil dibuat',
            'redirect' => '',
            'table' => 'datatable-secondary-master',
            'additional' => [],
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
    public function updateSecondaryMaster(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_secondary" => "required",
        ]);

        $storeSecondaryMaster = SewingSecondaryMaster::where("id", $validatedRequest['edit_id'])->update([
            "secondary" => $validatedRequest['edit_secondary'],
        ]);

        if ($storeSecondaryMaster) {
            return array(
                'status' => 200,
                'message' => 'Secondary Master berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-secondary-master',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Secondary Master tidak berhasil diubah',
            'redirect' => '',
            'table' => 'datatable-secondary-master',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroySecondaryMaster($id)
    {
        $destroySecondaryMaster = SewingSecondaryMaster::where("id", $id)->update([
            "hidden" => "Y"
        ]);

        if ($destroySecondaryMaster) {
            return array(
                'status' => 200,
                'message' => 'Secondary Master berhasil dihapus',
                'redirect' => '',
                'table' => 'datatable-secondary-master',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Secondary Master gagal dihapus',
            'redirect' => '',
            'table' => 'datatable-secondary-master',
            'additional' => [],
        );
    }
}
