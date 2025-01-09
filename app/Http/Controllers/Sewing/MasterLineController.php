<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Hris\MasterEmployee;
use App\Models\SignalBit\LeaderLine;
use App\Models\SignalBit\EmployeeProduction;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use DB;

class MasterLineController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = LeaderLine::where("tanggal", ">=", $request->from)->where("tanggal", "<=", $request->to);

            return Datatables::eloquent($data)->toJson();
        }

        $employees = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "sewing_nonsewing",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        where("status_jabatan", "LEADER")->
        where("sewing_nonsewing", "SEWING")->
        orderBy("enroll_id", "asc")->
        get();

        $lines = UserLine::select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("sewing.master-line.master-line", ["lines" => $lines, "employees" => $employees, "page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "master-line"]);
    }

    public function show($id = 0) {

    }

    public function create() {
        $employees = MasterEmployee::select(
                "enroll_id",
                "employee_name",
                "status_jabatan",
                "sewing_nonsewing",
                "nik"
            )->
            where("status_aktif", "AKTIF")->
            where("status_jabatan", "LEADER")->
            where("sewing_nonsewing", "SEWING")->
            orderBy("enroll_id", "asc")->
            get();

        $lines = UserLine::select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("sewing.master-line.create-master-line", ["lines" => $lines, "employees" => $employees, "page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "master-line"]);
    }

    public function store(Request $request) {
        $validatedRequest = $request->validate([
            "tanggal" => "required",
            "line_id" => "required",
            "line_name" => "required",
            "employee_id" => "required",
            "employee_nik" => "required",
            "employee_name" => "required",
        ]);

        $storeLeaderLine = LeaderLine::create([
            "tanggal" => $validatedRequest["tanggal"],
            "line_id" => $validatedRequest["line_id"],
            "line_name" => $validatedRequest["line_name"],
            "employee_id" => $validatedRequest["employee_id"],
            "employee_nik" => $validatedRequest["employee_nik"],
            "employee_name" => $validatedRequest["employee_name"],
            "created_by" => Auth::user()->id,
            "created_by_username" => Auth::user()->username,
        ]);

        if ($storeLeaderLine) {
            $employee = MasterEmployee::where("enroll_id", $validatedRequest["employee_id"])->first();

            $storeEmployee = EmployeeProduction::updateOrCreate(
                ['enroll_id' => $employee->enroll_id],
                [
                    'name' => $employee->employee_name,
                    'role' => "leader",
                    'created_by' => Auth::user()->id,
                    "created_by_username" => Auth::user()->username,
                ]
            );

            return array(
                "status" => 200,
                "message" => "Leader Line berhasil disimpan.",
                "additional" => $storeLeaderLine,
            );
        }

        return array(
            "status" => 400,
            "message" => "Leader Line gagal disimpan.",
            "additional" => $storeLeaderLine,
        );
    }

    public function update(Request $request) {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_tanggal" => "required",
            "edit_line_id" => "required",
            "edit_line_name" => "required",
            "edit_employee_id" => "required",
            "edit_employee_nik" => "required",
            "edit_employee_name" => "required",
        ]);

        $updateLeaderLine = LeaderLine::where("id", $validatedRequest["edit_id"])->update([
            "tanggal" => $validatedRequest["edit_tanggal"],
            "line_id" => $validatedRequest["edit_line_id"],
            "line_name" => $validatedRequest["edit_line_name"],
            "employee_id" => $validatedRequest["edit_employee_id"],
            "employee_nik" => $validatedRequest["edit_employee_nik"],
            "employee_name" => $validatedRequest["edit_employee_name"]
        ]);

        if ($updateLeaderLine) {
            $employee = MasterEmployee::where("enroll_id", $validatedRequest["edit_employee_id"])->first();

            $storeEmployee = EmployeeProduction::updateOrCreate(
                ['enroll_id' => $employee->enroll_id],
                [
                    'name' => $employee->employee_name,
                    'role' => "leader",
                ]
            );

            return array(
                "status" => 200,
                "message" => "Leader Line berhasil diubah.",
                "additional" => $updateLeaderLine,
            );
        }

        return array(
            "status" => 400,
            "message" => "Leader Line gagal diubah.",
            "additional" => $updateLeaderLine,
        );
    }

    public function destroy($id = 0) {
        if ($id) {
            $destroyLeaderLine = LeaderLine::where("id", $id)->delete();

            if ($destroyLeaderLine) {
                return array(
                    "status" => 200,
                    "message" => "Leader Line berhasil dihapus.",
                    "additional" => $destroyLeaderLine,
                );
            }

            return array(
                "status" => 400,
                "message" => "Leader Line gagal dihapus.",
                "additional" => $destroyLeaderLine,
            );
        }

        return array(
            "status" => 400,
            "message" => "Data tidak ditemukan.",
            "additional" => $destroyLeaderLine,
        );
    }
}
