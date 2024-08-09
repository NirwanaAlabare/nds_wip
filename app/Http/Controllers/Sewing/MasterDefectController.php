<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MasterDefectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->type == "type") {
                $defectTypeQuery = DefectType::orderBy("updated_at", "desc");

                return DataTables::eloquent($defectTypeQuery)->toJson();
            } else if ($request->type == "area") {
                $defectAreaQuery = DefectArea::orderBy("updated_at", "desc");

                return DataTables::eloquent($defectAreaQuery)->toJson();
            }
        }

        return view("sewing.master-defect.master-defect", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "master-defect"]);
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
    public function storeDefectType(Request $request)
    {
        $validatedRequest = $request->validate([
            "defect_type" => "required",
            "allocation" => "required",
        ]);

        $storeDefectType = DefectType::create([
            "defect_type" => $validatedRequest['defect_type'],
            "allocation" => $validatedRequest['allocation'],
            "hidden" => "N",
        ]);

        if ($storeDefectType) {
            return array(
                'status' => 200,
                'message' => 'Defect Type berhasil dibuat',
                'redirect' => '',
                'table' => 'datatable-defect-type',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Defect Type tidak berhasil dibuat',
            'redirect' => '',
            'table' => 'datatable-defect-type',
            'additional' => [],
        );
    }

    public function storeDefectArea(Request $request)
    {
        $validatedRequest = $request->validate([
            "defect_area" => "required",
        ]);

        $storeDefectArea = DefectArea::create([
            "defect_area" => $validatedRequest['defect_area'],
            "hidden" => "N",
        ]);

        if ($storeDefectArea) {
            return array(
                'status' => 200,
                'message' => 'Defect Area berhasil dibuat',
                'redirect' => '',
                'table' => 'datatable-defect-area',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Defect Area tidak berhasil dibuat',
            'redirect' => '',
            'table' => 'datatable-defect-area',
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
    public function updateDefectType(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_defect_type" => "required",
            "edit_allocation" => "required",
        ]);

        $storeDefectType = DefectType::where("id", $validatedRequest['edit_id'])->update([
            "defect_type" => $validatedRequest['edit_defect_type'],
            "allocation" => $validatedRequest['edit_allocation'],
        ]);

        if ($storeDefectType) {
            return array(
                'status' => 200,
                'message' => 'Defect Type berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-defect-type',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Defect Type tidak berhasil diubah',
            'redirect' => '',
            'table' => 'datatable-defect-type',
            'additional' => [],
        );
    }

    public function updateDefectArea(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_defect_area" => "required",
        ]);

        $storeDefectArea = DefectArea::where("id", $validatedRequest['edit_id'])->update([
            "defect_area" => $validatedRequest['edit_defect_area'],
        ]);

        if ($storeDefectArea) {
            return array(
                'status' => 200,
                'message' => 'Defect Area berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-defect-area',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Defect Area tidak berhasil diubah',
            'redirect' => '',
            'table' => 'datatable-defect-area',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyDefectType($id)
    {
        $destroyDefectType = DefectType::find($id)->delete();

        if ($destroyDefectType) {
            return array(
                'status' => 200,
                'message' => 'Defect Type berhasil dihapus',
                'redirect' => '',
                'table' => 'datatable-defect-type',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Defect Type gagal dihapus',
            'redirect' => '',
            'table' => 'datatable-defect-type',
            'additional' => [],
        );
    }

    public function destroyDefectArea($id)
    {
        $destroyDefectArea = DefectArea::find($id)->delete();

        if ($destroyDefectArea) {
            return array(
                'status' => 200,
                'message' => 'Defect Area berhasil dihapus',
                'redirect' => '',
                'table' => 'datatable-defect-area',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Defect Area gagal dihapus',
            'redirect' => '',
            'table' => 'datatable-defect-area',
            'additional' => [],
        );
    }
}
