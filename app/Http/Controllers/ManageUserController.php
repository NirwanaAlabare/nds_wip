<?php

namespace App\Http\Controllers;

use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Models\Auth\UserRole;
use App\Models\Auth\Access;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ManageUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with(["roles", "roles.accesses"]);

            return DataTables::eloquent($users)
                ->addColumn('roles', function ($row) {
                    return $row->roles->implode("nama_role", ", ");
                })
                ->addColumn('accesses', function ($row) {
                    return $row->roles->map(function ($item, $key) {
                            return $item->accesses->implode("access", ", ");
                        })->
                        flatten(1)->
                        implode(",");
                })
                ->rawColumns(['roles', 'accesses'])->
                toJson();
        }

        $roles = Role::all();
        $accesses = Access::all();

        return view("users.users", ["roles" => $roles, "accesses" => $accesses]);
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
    public function store(StoreUserRequest $request)
    {
        $validatedRequest = $request->validated();

        if ($validatedRequest) {
            $create = User::create([
                "name" => $validatedRequest["name"],
                "username" => $validatedRequest["username"],
                "password" => Hash::make($validatedRequest["password"]),
                "type" => $request["type"]
            ]);

            if ($create) {
                $roleArray = [];
                for ($i = 0; $i < count($request['roles']); $i++) {
                    array_push($roleArray, ["user_id" => $create->id, "role_id" => $request['roles'][$i]]);
                }

                UserRole::insert($roleArray);
            }

            return array(
                'status' => '200',
                'message' => 'User Created',
                'redirect' => 'reload',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Create User failed',
            'redirect' => '',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user, $id)
    {
        $validatedRequest = $request->validated();

        if ($validatedRequest["password"]) {
            $updateUser = User::where("id", $id)->update([
                "name" => $validatedRequest["name"],
                "username" => $validatedRequest["username"],
                "password" => Hash::make($validatedRequest["password"])
            ]);
        } else {
            $updateUser = User::where("id", $id)->update([
                "name" => $validatedRequest["name"],
                "username" => $validatedRequest["username"]
            ]);
        }

        if ($updateUser) {
            if (count($request['roles']) > 0) {
                $roleArray = [];

                for ($i = 0; $i < count($request['roles']); $i++) {
                    array_push($roleArray, ["user_id" => $create->id, "role_id" => $request['roles'][$i]]);
                }

                UserRole::insert($roleArray);
            }

            return array(
                'status' => '200',
                'message' => 'Profile updated',
                'redirect' => 'reload',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Profile update failed',
            'redirect' => '',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, $id)
    {
        if ($id) {
            $deleteUser = User::where("id", $id)->delete();

            if ($deleteUser) {
                UserRole::where("user_id", $id)->delete();

                return array(
                    'status' => '200',
                    'message' => 'User Deleted',
                    'redirect' => '',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Delete User failed',
            'redirect' => '',
            'additional' => [],
        );
    }

    public function getApi()
    {
        try {
            $users = User::get();
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $users,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function storeApi(StoreUserRequest $request)
    {
        try {
            $validatedRequest = $request->validated();

            $user = User::create([
                "name" => $validatedRequest["name"],
                "username" => $validatedRequest["username"],
                "password" => Hash::make($validatedRequest["password"])
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $user,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }
}
