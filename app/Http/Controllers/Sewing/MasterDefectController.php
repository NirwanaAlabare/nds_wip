<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;

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
                $defectTypeQuery = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy("updated_at", "desc");

                return DataTables::eloquent($defectTypeQuery)->toJson();
            } else if ($request->type == "area") {
                $defectAreaQuery = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy("updated_at", "desc");

                return DataTables::eloquent($defectAreaQuery)->toJson();
            }
        }

        $defectTypes = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();
        $defectAreas = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->get();

        return view("sewing.master-defect.master-defect", ["page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "master-defect", "defectTypes" => $defectTypes, "defectAreas" => $defectAreas]);
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
        $destroyDefectType = DefectType::where("id", $id)->update([
            "hidden" => "Y"
        ]);

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
        $destroyDefectArea = DefectArea::where("id", $id)->update([
            "hidden" => "Y"
        ]);

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

    // Merge defect type
    public function mergeDefectType(Request $request) {
        $validatedRequest = $request->validate([
            "defect_type_from" => "required",
            "defect_type_to" => "required"
        ]);

        if ($validatedRequest) {
            $totalDefect = Defect::where("defect_type_id", $validatedRequest["defect_type_from"])->count();
            $totalReject = Reject::where("reject_type_id", $validatedRequest["defect_type_from"])->count();

            // Update Defect About
            $updateDefectAbout = DB::transaction(function() use($validatedRequest) {
                // Hide Defect
                $updateDefectTypeFrom = DefectType::where("id", $validatedRequest["defect_type_from"])->update([
                    "hidden" => "Y"
                ]);
                // Update Defect
                $updateDefect = Defect::withoutTimestamps()->where("defect_type_id", $validatedRequest["defect_type_from"])->update([
                    "defect_type_id" => $validatedRequest["defect_type_to"]
                ]);
                // Update Reject
                $updateReject = Reject::withoutTimestamps()->where("reject_type_id", $validatedRequest["defect_type_from"])->update([
                    "reject_type_id" => $validatedRequest["defect_type_to"]
                ]);

                return true;
            }, 1);

            if ($updateDefectAbout) {
                return array(
                    "status" => 200,
                    "message" => "Defect berhasil di Merge <br> ".$totalDefect." Defect dan ". $totalReject ." Reject terpengaruh",
                );
            }
        }

        return array(
            "status" => 400,
            "message" => "Defect gagal di Merge",
        );
    }
}
