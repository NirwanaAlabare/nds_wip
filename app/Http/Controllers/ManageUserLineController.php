<?php

namespace App\Http\Controllers;

use App\Models\SignalBit\UserLine;
use App\Models\SignalBit\UserSbWip;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ManageUserLineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = UserLine::selectRaw("line_id as id, FullName as name, username, Password as password, Groupp as type")->whereIn("Groupp", ["SEWING", "ALLSEWING", "MENDING", "SPOTCLEANING"])->orderBy("line_id", "desc");

            return DataTables::eloquent($users)->toJson();
        }

        return view("user_lines.user_lines", ["page" => "dashboard-manage-user", "subPageGroup" => "manage-user-line", "subPage" => "manage-user-line"]);
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
            "name" => "required",
            "username" => "required",
            "password" => "required",
            "type" => "required"
        ]);

        if ($validatedRequest) {
            $maxId = UserLine::whereNotNull("line_id")->max("line_id")+1;

            $create = UserLine::create([
                "FullName" => $validatedRequest["name"],
                "username" => $validatedRequest["username"],
                "Password" => $validatedRequest["password"],
                "password_encrypt" => Hash::make($validatedRequest["password"]),
                "Groupp" => $request["type"],
                "line_id" => $maxId
            ]);

            if ($create) {
                $subUserArr = [];

                // Required User
                array_push($subUserArr, ["name" => str_replace("_", " ", $validatedRequest["username"]), "username" => $validatedRequest["username"], "password" => Hash::make($validatedRequest["password"]), "password_text" => $validatedRequest["password"], "line_id" => $maxId]);
                // Sub User
                for ($i = 0; $i < $request['sub_user']; $i++) {
                    array_push($subUserArr, ["name" => str_replace("_", " ", $validatedRequest["username"])."_".($i+1), "username" => $validatedRequest["username"]."_".($i+1), "password" => Hash::make($validatedRequest["password"]), "password_text" => $validatedRequest["password"], "line_id" => $maxId]);
                }

                UserSbWip::insert($subUserArr);
            }

            return array(
                'status' => '200',
                'message' => 'User Created',
                'table' => 'manage-user-line-table',
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
     * @param  \App\Models\UserLine  $user
     * @return \Illuminate\Http\Response
     */
    public function show(UserLine $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserLine  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(UserLine $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserLine  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_name" => "required",
            "edit_username" => "required",
            "edit_password" => "required",
            "edit_type" => "required"
        ]);

        $userBefore = UserLine::where("line_id", $validatedRequest["edit_id"])->first();

        if ($validatedRequest["edit_password"]) {
            $updateUser = UserLine::where("line_id", $validatedRequest["edit_id"])->update([
                "FullName" => $validatedRequest["edit_name"],
                "username" => $validatedRequest["edit_username"],
                "Password" => $validatedRequest["edit_password"],
                "password_encrypt" => Hash::make($validatedRequest["edit_password"]),
                "Groupp" => $request["edit_type"]
            ]);

            $subUserUpdate = UserSbWip::where("username", $userBefore->username)->update([
                "name" => str_replace("_", " ", $validatedRequest["edit_username"]),
                "username" => $validatedRequest["edit_username"],
                "password" => Hash::make($validatedRequest["edit_password"]),
                "password_text" => $validatedRequest["edit_password"],
            ]);
        } else {
            $updateUser = UserLine::where("line_id", $validatedRequest["edit_edit_id"])->update([
                "FullName" => $validatedRequest["edit_name"],
                "username" => $validatedRequest["edit_username"],
                "Groupp" => $request["edit_type"]
            ]);

            $subUserUpdate = UserSbWip::where("username", $userBefore->username)->update([
                "name" => str_replace("_", " ", $validatedRequest["edit_username"]),
                "username" => $validatedRequest["edit_username"],
            ]);
        }

        if ($updateUser) {
            if ($request['edit_sub_user'] && $request['edit_sub_user'] > 0) {
                $totalSubUser = UserSbWip::where("line_id", $validatedRequest["edit_id"])->count();

                $subUserArr = [];
                for ($i = 0; $i < $request['edit_sub_user']; $i++) {
                    array_push($subUserArr, ["name" => str_replace("_", " ", $validatedRequest["edit_username"])."_".($totalSubUser+$i+1), "username" => $validatedRequest["edit_username"]."_".($totalSubUser+$i+1), "password" => Hash::make($validatedRequest["edit_password"]), "password_text" => $validatedRequest["edit_password"], "line_id" => $validatedRequest["edit_id"]]);
                }

                UserSbWip::insert($subUserArr);
            }

            return array(
                'status' => '200',
                'message' => 'Profile updated',
                'table' => 'manage-user-line-table',
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
     * @param  \App\Models\UserLine  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserLine $user, $id)
    {
        if ($id) {
            $subUserLine = addQuotesAround(UserSbWip::where("line_id", $id)->pluck("id")->implode(" "));

            $dataOutput = collect(
                    DB::connection("mysql_sb")->select("
                        SELECT output.* FROM (
                            (select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE created_by in (".$subUserLine.") LIMIT 1)
                            UNION
                            (select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE created_by in (".$subUserLine.") LIMIT 1)
                            UNION
                            (select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE created_by in (".$subUserLine.") LIMIT 1)
                        ) output
                    ")
                )->count();

            $dataOutputPacking = collect(
                    DB::connection("mysql_sb")->select("
                        SELECT output.* FROM (
                            (select created_by, kode_numbering, id, created_at, updated_at from output_rfts_packing WHERE created_by = ".$id." LIMIT 1)
                            UNION
                            (select created_by, kode_numbering, id, created_at, updated_at from output_defects_packing WHERE created_by = ".$id." LIMIT 1)
                            UNION
                            (select created_by, kode_numbering, id, created_at, updated_at from output_rejects_packing WHERE created_by = ".$id." LIMIT 1)
                        ) output
                    ")
                )->count();

            if ($dataOutput + $dataOutputPacking < 1) {
                $deleteUser = UserLine::where("line_id", $id)->delete();

                if ($deleteUser) {
                    UserSbWip::where("line_id", $id)->delete();

                    return array(
                        'status' => '200',
                        'message' => 'User Deleted',
                        'table' => 'manage-user-line-table',
                        'redirect' => '',
                        'additional' => [],
                    );
                }
            }

            return array(
                'status' => '400',
                'message' => 'User sudah memiliki Output',
                'redirect' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Delete User failed',
            'redirect' => '',
            'additional' => [],
        );
    }

    public function getUserLineSub(Request $request) {
        $userLineSub = UserSbWip::where("line_id", $request->id);

        return DataTables::eloquent($userLineSub)->toJson();
    }

    public function destroyUserLineSub($id = 0) {
        $dataOutput = collect(
            DB::connection("mysql_sb")->select("
                SELECT output.* FROM (
                    (select created_by, kode_numbering, id, created_at, updated_at from output_rfts WHERE created_by = '".$id."' LIMIT 1)
                    UNION
                    (select created_by, kode_numbering, id, created_at, updated_at from output_defects WHERE created_by = '".$id."' LIMIT 1)
                    UNION
                    (select created_by, kode_numbering, id, created_at, updated_at from output_rejects WHERE created_by = '".$id."' LIMIT 1)
                ) output
            ")
        )->count();

        if ($dataOutput < 1) {
            $deleteSubUser = UserSbWip::where("id", $id)->delete();

            if ($deleteSubUser) {
                return array(
                    'status' => '200',
                    'message' => 'Sub User Deleted',
                    'table' => 'sub-user-table',
                    'redirect' => '',
                    'additional' => [],
                );
            }
        } else {
            return array(
                'status' => '400',
                'message' => 'User sudah memiliki Output',
                'redirect' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => '400',
            'message' => 'Delete Sub User failed',
            'redirect' => '',
            'additional' => [],
        );
    }

    // public function getApi()
    // {
    //     try {
    //         $users = User::get();
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'data' => [],
    //             'message'=>$e->getMessage()
    //         ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    //     }

    //     return response()->json([
    //         'data' => $users,
    //         'message' => 'Succeed'
    //     ], JsonResponse::HTTP_OK);
    // }

    // public function storeApi(StoreUserRequest $request)
    // {
    //     try {
    //         $validatedRequest = $request->validated();

    //         $user = User::create([
    //             "name" => $validatedRequest["name"],
    //             "username" => $validatedRequest["username"],
    //             "password" => Hash::make($validatedRequest["password"]),
    //             "password_text" => $validatedRequest["password"]
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'data' => [],
    //             'message'=>$e->getMessage()
    //         ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    //     }

    //     return response()->json([
    //         'data' => $user,
    //         'message' => 'Succeed'
    //     ], JsonResponse::HTTP_OK);
    // }
}
