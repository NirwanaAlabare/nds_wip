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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        //
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
