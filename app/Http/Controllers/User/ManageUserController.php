<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Auth\Role;
use App\Models\Auth\UserRole;
use App\Models\Auth\Access;
use App\Models\Auth\ConnectionList;
use App\Models\Auth\UserConnection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserDetailRequest;
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
        $connectionList = ConnectionList::all();

        return view("users.users", ["roles" => $roles, "accesses" => $accesses, "connectionList" => $connectionList, "page" => "dashboard-manage-user", "subPageGroup" => "manage-user", "subPage" => "manage-user"]);
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
                "password_text" => $validatedRequest["password"],
                "type" => $request["type"]
            ]);

            if ($create) {
                $roleArray = [];
                for ($i = 0; $i < count($request['roles']); $i++) {
                    array_push($roleArray, ["user_id" => $create->id, "role_id" => $request['roles'][$i]]);
                }

                UserRole::insert($roleArray);

                $connectionArray = [];
                for ($j = 0; $j < count($request['connectionList']); $j++) {
                    array_push($connectionArray, ["user_id" => $create->id, "connection_id" => $request['connectionList'][$j], "is_active" => ($j == 0 ? 'active' : 'inactive')]);
                }

                UserConnection::insert($connectionArray);
            }

            return array(
                'status' => '200',
                'message' => 'User Created',
                'table' => 'manage-user-table',
                'redirect' => '',
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
    public function update(UpdateUserDetailRequest $request)
    {
        $validatedRequest = $request->validated();

        if ($validatedRequest["edit_password"]) {
            $updateUser = User::where("id", $validatedRequest["edit_id"])->update([
                "name" => $validatedRequest["edit_name"],
                "username" => $validatedRequest["edit_username"],
                "password" => Hash::make($validatedRequest["edit_password"]),
                "password_text" => $validatedRequest["edit_password"],
                "type" => $validatedRequest["edit_type"]
            ]);
        } else {
            $updateUser = User::where("id", $validatedRequest["edit_id"])->update([
                "name" => $validatedRequest["edit_name"],
                "username" => $validatedRequest["edit_username"],
                "type" => $validatedRequest["edit_type"]
            ]);
        }

        if ($updateUser) {
            // Update User Role
            if ($request['edit_roles'] && count($request['edit_roles']) > 0) {
                $roleArray = [];

                for ($i = 0; $i < count($request['edit_roles']); $i++) {
                    array_push($roleArray, ["user_id" => $validatedRequest["edit_id"], "role_id" => $request['edit_roles'][$i]]);
                }

                UserRole::insert($roleArray);
            }

            // Update User Connection
            if ($request['edit_connectionList'] && count($request['edit_connectionList']) > 0) {
                $connectionArray = [];

                for ($i = 0; $i < count($request['edit_connectionList']); $i++) {
                    array_push($connectionArray, ["user_id" => $validatedRequest["edit_id"], "connection_id" => $request['edit_connectionList'][$i], 'is_active' => 'inactive']);
                }

                UserConnection::insert($connectionArray);

                // Check Active Connection
                $checkUserConnectionActive = UserConnection::where("user_id", $validatedRequest["edit_id"])->
                    where("is_active", "active")->
                    first();

                // If there is no Active Connection, set the first connection as Active
                if (!$checkUserConnectionActive) {
                    $firstUserConnection = UserConnection::where("user_id", $validatedRequest["edit_id"])->first();

                    if ($firstUserConnection) {
                        $firstUserConnection->is_active = "active";
                        $firstUserConnection->save();
                    }
                }
            }

            return array(
                'status' => '300',
                'message' => 'Profile updated',
                'table' => 'manage-user-table',
                'redirect' => '',
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

                UserConnection::where("user_id", $id)->delete();

                return array(
                    'status' => '200',
                    'message' => 'User Deleted',
                    'table' => 'manage-user-table',
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

    public function getUserRole(Request $request) {
        $userRole = UserRole::with(["role", "role.accesses"])->where("user_id", $request->id);

        return DataTables::eloquent($userRole)
            ->addColumn('role', function ($row) {
                return $row->role->nama_role;
            })
            ->addColumn('accesses', function ($row) {
                return $row->role->accesses->implode("access", ", ");
            })
            ->rawColumns(['roles', 'accesses'])->
            toJson();
    }

    public function destroyUserRole($id = 0) {
        $deleteUserRole = UserRole::where("id", $id)->delete();

        if ($deleteUserRole) {
            return array(
                'status' => '200',
                'message' => 'Role Deleted',
                'table' => 'user-role-table',
                'redirect' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Delete Role failed',
            'redirect' => '',
            'additional' => [],
        );
    }

    public function getUserConnection(Request $request) {
        $userConnection = UserConnection::with(["connectionList"])->where("user_id", $request->id);

        return DataTables::eloquent($userConnection)
            ->addColumn('connection_name', function ($row) {
                return $row->connectionList->connection_name;
            })
            ->addColumn('connection_sb', function ($row) {
                return $row->connectionList->connection_sb;
            })
            ->addColumn('connection_nds', function ($row) {
                return $row->connectionList->connection_nds;
            })
            ->rawColumns(['connection_name', 'connection_sb','connection_nds'])->
            toJson();
    }

    public function destroyUserConnection($id = 0) {
        $currentUserConnection = UserConnection::where("id", $id)->first();

        if ($currentUserConnection) {
            if ($currentUserConnection->is_active != 'active') {
                $currentUserConnection->delete();
            } else {
                return array(
                    'status' => '200',
                    'message' => 'Connection Deleted',
                    'table' => 'user-connection-table',
                    'redirect' => '',
                    'additional' => [],
                );
            }

            return array(
                'status' => '200',
                'message' => 'Connection Deleted',
                'table' => 'user-connection-table',
                'redirect' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Delete Connection failed',
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
                "password" => Hash::make($validatedRequest["password"]),
                "password_text" => $validatedRequest["password"]
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
