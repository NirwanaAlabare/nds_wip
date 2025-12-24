<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Auth\ConnectionList;
use App\Models\Auth\UserConnection;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ManageConnectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $connections = ConnectionList::query();

            return DataTables::eloquent($connections)->toJson();
        }

        return view("connection-list.connection-list", ["page" => "dashboard-manage-user", "subPageGroup" => "manage-user", "subPage" => "manage-connection-list"]);
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
            "connection_name" => "required",
            "connection_sb" => "required",
            "connection_nds" => "required",
        ]);

        if ($validatedRequest) {
            $storeConnection = ConnectionList::create([
                    "connection_name" => $validatedRequest['connection_name'],
                    "connection_sb" => $validatedRequest['connection_sb'],
                    "connection_nds" => $validatedRequest['connection_nds'],
                ]);

            if ($storeConnection) {
                return array(
                    "status" => 300,
                    "message" => "Connection berhasil disimpan",
                    'table' => 'manage-connection-table',
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Connection gagal disimpan',
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Auth\ConnectionList  $connectionList
     * @return \Illuminate\Http\Response
     */
    public function show(ConnectionList $connectionList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Auth\ConnectionList  $connectionList
     * @return \Illuminate\Http\Response
     */
    public function edit(ConnectionList $connectionList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Auth\ConnectionList  $connectionList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConnectionList $connectionList)
    {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_connection_name" => "required"
        ]);

        if ($validatedRequest) {
            $updateConnection = ConnectionList::where("id", $validatedRequest["edit_id"])->update([
                    "connection_name" => $validatedRequest['edit_connection_name'],
                    "connection_sb" => $validatedRequest['edit_connection_sb'],
                    "connection_nds" => $validatedRequest['edit_connection_nds'],
                ]);

            if ($updateConnection) {
                return array(
                    "status" => 300,
                    "message" => "Connection berhasil disimpan",
                    'table' => 'manage-connection-table',
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Connection gagal disimpan',
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Auth\ConnectionList  $connectionList
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConnectionList $connectionList, $id)
    {
        if ($id) {
            $destroyRole = ConnectionList::where("id", $id)->delete();

            if ($destroyRole) {
                UserConnection::where("connection_id", $id)->delete();

                return array(
                    'status' => '200',
                    'message' => 'Connection Deleted',
                    'table' => 'manage-connection-table',
                    'redirect' => '',
                    'additional' => [],
                );
            }
        }

        return array(
            'status' => '400',
            'message' => 'Delete Connection Failed',
        );
    }
}
