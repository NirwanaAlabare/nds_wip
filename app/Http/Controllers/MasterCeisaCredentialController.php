<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class MasterCeisaCredentialController extends Controller
{
    public function index()
    {
        $mysql_sb = DB::connection('mysql_sb');

        $isAdmin = strtolower(auth()->user()->groupp ?? '') == 'admin' || strtolower(auth()->user()->groupp ?? '') == 'admin_it';
        $currentUsername = auth()->user()->username;

        $credentials = $mysql_sb->table('master_ceisa_credentials')
            ->leftJoin('userpassword', 'master_ceisa_credentials.username', '=', 'userpassword.username')
            ->select('master_ceisa_credentials.*', 'userpassword.username as user_name')
            ->get();

        $hasCredential = $credentials->contains('username', $currentUsername);

        $users = [];
        if ($isAdmin) {

            $users = $mysql_sb->table('userpassword')
                ->whereNotIn('username', function ($query) {
                    $query->select('username')->from('master_ceisa_credentials');
                })->get();
        }

        return view('export-import.master-ceisa.index', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "master-ceisa",
            'credentials'    => $credentials,
            'users'          => $users,
            'isAdmin'        => $isAdmin,
            'hasCredential'  => $hasCredential,
            'containerFluid' => true
        ]);
    }

    public function store(Request $request)
    {
        try {
            $username = $request->username ?? auth()->user()->username;

            $request->merge(['username' => $username]);

            $request->validate([
                'username' => 'required',
                'ceisa_username' => 'required',
                'ceisa_password' => 'required',
                'ceisa_api_key' => 'required',
            ]);

            $mysql_sb = DB::connection('mysql_sb');

            $mysql_sb->table('master_ceisa_credentials')->insert([
                'username'       => $username,
                'ceisa_username' => $request->ceisa_username,
                'ceisa_password' => $request->ceisa_password,
                'ceisa_api_key'  => $request->ceisa_api_key,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Kredensial CEISA berhasil ditambahkan!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 400,
                'message' => implode('<br>', $e->validator->errors()->all())
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'ceisa_username' => 'required',
                'ceisa_password' => 'required',
                'ceisa_api_key' => 'required',
            ]);

            $mysql_sb = DB::connection('mysql_sb');

            $mysql_sb->table('master_ceisa_credentials')
                ->where('id', $id)
                ->update([
                    'ceisa_username' => $request->ceisa_username,
                    'ceisa_password' => $request->ceisa_password,
                    'ceisa_api_key'  => $request->ceisa_api_key,
                    'updated_at'     => now(),
                ]);

            return response()->json([
                'status' => 200,
                'message' => 'Kredensial CEISA berhasil diupdate!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 400,
                'message' => implode('<br>', $e->validator->errors()->all())
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        try {
            $mysql_sb->table('master_ceisa_credentials')
                ->where('id', $id)
                ->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Kredensial CEISA berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
