<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Summary\MasterKaryawan;
use App\Models\Summary\KaryawanHRIS;
use App\Models\Summary\MasterJabatan;
use App\Http\Requests\UpdateKaryawanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MasterKaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterKaryawan = MasterKaryawan::with('masterJabatan');

            return
            DataTables::eloquent($masterKaryawan)->
                addColumn('nama_jabatan', function($row) {
                    return $row->masterJabatan ? $row->masterJabatan->nama_jabatan : '-';
                })->
                addIndexColumn()->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateKaryawanModal\")'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('karyawan.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(['action'])->
                order(
                    function ($query) {
                        $query->orderBy('jabatan_id', 'asc')->orderBy('nama', 'asc');
                    }
                )->toJson();
        }

        $jabatans = MasterJabatan::all();

        return view('sewing.master.master-karyawan', ['parentPage' => 'master', 'page' => 'dashboard-sewing-effy', 'jabatans' => $jabatans]);
    }

    /**
     * Fetch Data.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $masterKaryawan = MasterKaryawan::leftJoin('master_jabatan', 'master_jabatan.id', '=', 'master_karyawan.id_jabatan');

            return
            DataTables::eloquent($masterKaryawan)->
                addIndexColumn()->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1' id='edit-jabatan' data='".$row."' onclick='editData(this, \"updateKaryawanModal\")'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1' id='delete-jabatan' data='".$row."' data-url='".route('karyawan.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(['action'])->
                order(
                    function ($query) {
                        if (request()->has('updated_at')) {
                            $query->orderBy('updated_at', 'desc');
                        }
                    }
                )->toJson();
        }

        return view('master/master-karyawan', ['page' => 'master-karyawan']);
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
     * @param  \App\Models\MasterKaryawan  $masterKaryawan
     * @return \Illuminate\Http\Response
     */
    public function show(MasterKaryawan $masterKaryawan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterKaryawan  $masterKaryawan
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterKaryawan $masterKaryawan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterKaryawan  $masterKaryawan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateKaryawanRequest $request)
    {
        $validatedRequest = $request;

        $updateKaryawan = MasterKaryawan::where('id', $validatedRequest['edit_id'])->
            update([
                'jabatan_id' => $validatedRequest['edit_jabatan_id'],
                'operator' => Auth::user()->username,
            ]);

        if ($updateKaryawan) {
            return array(
                'status' => 200,
                'message' => 'Karyawan berhasil diubah',
                'redirect' => '',
                'table' => 'karyawan-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Karyawan gagal diubah',
            'redirect' => '',
            'table' => 'karyawan-table',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterKaryawan  $masterKaryawan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $destroyKaryawan = MasterKaryawan::find($id)->delete();

        if ($destroyKaryawan) {
            return array(
                'status' => 200,
                'message' => 'Karyawan berhasil dihapus',
                'redirect' => '',
                'table' => 'karyawan-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Karyawan gagal dihapus',
            'redirect' => '',
            'table' => 'karyawan-table',
            'additional' => [],
        );
    }

    /**
     * Fetch data from another source.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function otherSource(Request $request)
    {
        if ($request->ajax()) {
            $karyawanOtherSource = KaryawanHRIS::on('mysql_hris')->
                select(
                    'employee_atribut.nik',
                    'employee_atribut.enroll_id',
                    'employee_atribut.employee_name',
                    'employee_atribut.status_jabatan',
                    'employee_atribut.status_staff',
                    'department_all.department_name',
                    'department_all.sub_dept_name'
                )->
                whereRaw('
                    employee_atribut.enroll_id IS NOT NULL
                    AND (employee_atribut.tanggal_resign IS NULL OR employee_atribut.tanggal_resign = "0000-00-00")
                    AND employee_atribut.status_aktif="AKTIF"
                ')->
                leftJoin('department_all', 'employee_atribut.sub_dept_id', '=', 'department_all.sub_dept_id')->
                orderBy('employee_atribut.employee_name', 'asc');

            return DataTables::eloquent($karyawanOtherSource)->toJson();
        }

        return view('master/master-karyawan', ['page' => 'master-karyawan']);
    }

    /**
     * Transfer data from another database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $success = [];
        $fail = [];
        $exist = [];

        foreach ($request->karyawanTransfer as $req) {
            $isExist = MasterKaryawan::where('absen', $req['absen'])->count();

            if ($isExist < 1) {
                $transferKaryawan = MasterKaryawan::create([
                    "nik" => $req['nik'],
                    "absen" => $req['absen'],
                    "nama" => $req['nama'],
                    "jabatan_id" => $req['jabatan_id'],
                    "operator" => Auth::user()->username,
                ]);

                if ($transferKaryawan) {
                    array_push($success, ['nik' => $req['nik'], 'nama' => $req['nama']]);
                } else {
                    array_push($fail, ['nik' => $req['nik'], 'nama' => $req['nama']]);
                }
            } else {
                array_push($exist, ['nik' => $req['nik'], 'nama' => $req['nama']]);
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Transfer data berhasil',
                'redirect' => '',
                'table' => 'karyawan-table',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Hasil transfer kosong',
                'redirect' => '',
                'table' => 'karyawan-table',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }
    }
}
