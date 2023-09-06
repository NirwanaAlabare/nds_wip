<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;

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
                "password" => Hash::make($validatedRequest["password"])
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
}
