<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Auth\UserConnection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                "password" => Hash::make($validatedRequest["password"]),
                "password_text" => $validatedRequest["password"]
            ]);
        } else {
            $updateUser = User::where("id", $id)->update([
                "name" => $validatedRequest["name"],
                "username" => $validatedRequest["username"]
            ]);
        }

        if ($updateUser) {
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
    public function destroy(User $user)
    {
        //
    }

    public function updateUserConnection(Request $request)
    {
        $user = Auth::user();

        if ($user && $request->id) {
            // Update Selected Connection to Active
            $updateUserConnection = UserConnection::where("id", $request->id)->
                where("user_id", $user->id)->
                update([
                    "is_active" => "active"
                ]);

            if ($updateUserConnection) {
                // Update Other Connections to Inactive
                UserConnection::where("id", "!=", $request->id)->
                    where("user_id", $user->id)->
                    update([
                        "is_active" => "inactive"
                    ]);

                return response()->json([
                    'status' => '200',
                    'message' => 'Connection update success',
                    'redirect' => '',
                    'additional' => [],
                ]);
            }
        }

        return response()->json([
            'status' => '400',
            'message' => 'Connection update failed',
            'redirect' => 'reload',
            'additional' => [],
        ]);
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
