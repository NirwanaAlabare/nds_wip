<?php

namespace App\Http\Controllers;

use App\Models\Auth\Role;
use App\Models\Auth\Access;
use App\Models\Auth\RoleAccess;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ManageRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = Role::with(["accesses"]);

            return DataTables::eloquent($users)
                ->addColumn('accesses', function ($row) {
                    return $row->accesses->implode("access", ", ");
                })
                ->rawColumns(['accesses'])->
                toJson();
        }

        $roles = Role::all();
        $accesses = Access::all();

        return view("roles.roles", ["roles" => $roles, "accesses" => $accesses, "page" => "dashboard-manage-user", "subPageGroup" => "manage-user", "subPage" => "manage-role"]);
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
            "nama_role" => "required",
        ]);

        if ($validatedRequest) {
            $storeRole = Role::create([
                    "nama_role" => $validatedRequest['nama_role'],
                ]);

            if ($storeRole) {
                if ($request->roles && count($request->roles) > 0) {
                    $roleAccessArr = [];

                    for ($i = 0; $i < count($request->roles); $i++) {
                        array_push($roleAccessArr, [
                            "role_id" => $storeRole->id,
                            "access_id" => $request->roles[$i],
                        ]);
                    }

                    $insertRoleAccess = RoleAccess::insert($roleAccessArr);
                }
            }

            return array(
                "status" => 300,
                "message" => "Role berhasil disimpan",
                'table' => 'manage-role-table',
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_nama_role" => "required"
        ]);

        if ($validatedRequest) {
            $updateRole = Role::where("id", $validatedRequest["edit_id"])->create([
                    "nama_role" => $validatedRequest['edit_nama_role'],
                ]);

            if ($updateRole) {
                if ($request->edit_accesses && count($request->edit_accesses) > 0) {
                    $roleAccessArr = [];

                    for ($i = 0; $i < count($request->edit_accesses); $i++) {
                        array_push($roleAccessArr, [
                            "role_id" => $validatedRequest["edit_id"],
                            "access_id" => $request->edit_accesses[$i],
                        ]);
                    }

                    $insertRoleAccess = RoleAccess::insert($roleAccessArr);
                }

                return array(
                    "status" => 300,
                    "message" => "Role berhasil disimpan",
                    'table' => 'manage-role-table',
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Role gagal disimpan',
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role, $id)
    {
        if ($id) {
            $destroyRole = Role::where("id", $id)->delete();

            if ($destroyRole) {
                RoleAccess::where("role_id", $id)->delete();

                return array(
                    'status' => '200',
                    'message' => 'Role Deleted',
                    'table' => 'manage-role-table',
                    'redirect' => '',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Delete Role Failed',
        );
    }

    public function getRoleAccess(Request $request) {
        $roleAccess = RoleAccess::with(["role", "access"])->where("role_id", $request->id);

        return DataTables::eloquent($roleAccess)
            ->addColumn('role', function ($row) {
                return $row->role->nama_role;
            })
            ->addColumn('access', function ($row) {
                return $row->access->access;
            })
            ->rawColumns(['roles', 'access'])->
            toJson();
    }

    public function destroyRoleAccess($id = 0) {
        $deleteUserRole = RoleAccess::where("id", $id)->delete();

        if ($deleteUserRole) {
            return array(
                'status' => '200',
                'message' => 'Access Deleted',
                'table' => 'role-access-table',
                'redirect' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Delete Access failed',
            'redirect' => '',
            'additional' => [],
        );
    }
}
