<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\StoreJabatanRequest;
use App\Http\Requests\UpdateJabatanRequest;

class MasterJabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterJabatan = MasterJabatan::query();

            return
                DataTables::eloquent($masterJabatan)->
                addIndexColumn()->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateJabatanModal\")'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('jabatan.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(['action'])->
                order(
                    function ($query) {
                        $query->orderBy('kode_jabatan', 'asc')->orderBy('nama_jabatan', 'asc');
                    }
                )->toJson();
        }

        return view('sewing.master.master-jabatan', ['parentPage' => 'master', 'page' => 'dashboard-sewing-effy']);
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
    public function store(StoreJabatanRequest $request)
    {
        $validatedRequest = $request;

        $storeJabatan = MasterJabatan::create([
            'kode_jabatan' => $validatedRequest['kode_jabatan'],
            'nama_jabatan' => $validatedRequest['nama_jabatan'],
            'operator' => Auth::user()->username,
        ]);

        if ($storeJabatan) {
            return array(
                'status' => 200,
                'message' => 'Jabatan berhasil ditambahkan',
                'redirect' => '',
                'table' => 'jabatan-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Jabatan gagal ditambahkan',
            'redirect' => '',
            'table' => 'jabatan-table',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterJabatan  $masterJabatan
     * @return \Illuminate\Http\Response
     */
    public function show(MasterJabatan $masterJabatan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterJabatan  $masterJabatan
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterJabatan $masterJabatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterJabatan  $masterJabatan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateJabatanRequest $request)
    {
        $validatedRequest = $request;

        $this->validate($validatedRequest,[
            'edit_kode_jabatan'=>'unique:master_jabatan,kode_jabatan,'.$request['edit_id'],
        ]);

        $updateJabatan = MasterJabatan::where('id', $validatedRequest['edit_id'])->
            update([
                'kode_jabatan' => $validatedRequest['edit_kode_jabatan'],
                'nama_jabatan' => $validatedRequest['edit_nama_jabatan'],
                'operator' => Auth::user()->username,
            ]);

        if ($updateJabatan) {
            return array(
                'status' => 200,
                'message' => 'Jabatan berhasil diubah',
                'redirect' => '',
                'table' => 'jabatan-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Jabatan gagal diubah',
            'redirect' => '',
            'table' => 'jabatan-table',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterJabatan  $masterJabatan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $destroyJabatan = MasterJabatan::find($id)->delete();

        if ($destroyJabatan) {
            return array(
                'status' => 200,
                'message' => 'Jabatan berhasil dihapus',
                'redirect' => '',
                'table' => 'jabatan-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Jabatan gagal dihapus',
            'redirect' => '',
            'table' => 'jabatan-table',
            'additional' => ['kode-jabatan', 'nama-jabatan'],
        );
    }
}
