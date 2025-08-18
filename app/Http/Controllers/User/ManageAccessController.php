<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Auth\Access;
use App\Models\Auth\RoleAccess;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ManageAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $accesses = Access::query();

            return DataTables::eloquent($accesses)->toJson();
        }

        return view("access.access", ["page" => "dashboard-manage-user", "subPageGroup" => "manage-user", "subPage" => "manage-access"]);
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
            "access" => "required",
        ]);

        if ($validatedRequest) {
            $storeAccess = Access::create([
                    "access" => $validatedRequest['access'],
                ]);

            if ($storeAccess) {
                return array(
                    "status" => 300,
                    "message" => "Access berhasil disimpan",
                    'table' => 'manage-access-table',
                );
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Access  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Access $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Access  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Access $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Access  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Access $role)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_access" => "required"
        ]);

        if ($validatedRequest) {
            $updateAccess = Access::where("id", $validatedRequest["edit_id"])->update([
                    "access" => $validatedRequest['edit_access'],
                ]);

            if ($updateAccess) {
                return array(
                    "status" => 300,
                    "message" => "Access berhasil disimpan",
                    'table' => 'manage-access-table',
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Access gagal disimpan',
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Access  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Access $role, $id)
    {
        if ($id) {
            $accessRole = RoleAccess::where("access_id", $id)->first();

            if (!$accessRole) {
                $destroyAccess = Access::where("id", $id)->delete();

                if ($destroyAccess) {
                    return array(
                        'status' => '200',
                        'message' => 'Access Deleted',
                        'table' => 'manage-access-table',
                        'redirect' => '',
                        'additional' => [],
                    );
                }
            } else {
                return array(
                    'status' => '400',
                    'message' => 'Ada role yang masih menggunakan access ini',
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Delete Access Failed',
        );
    }
}
