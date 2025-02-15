<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Hris\MasterEmployee;
use App\Models\SignalBit\EmployeeLine;
use App\Models\SignalBit\EmployeeProduction;
use App\Models\SignalBit\UserLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Facades\Image;
use DB;

class MasterLineController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = EmployeeLine::where("tanggal", ">=", $request->from)->where("tanggal", "<=", $request->to);

            return Datatables::eloquent($data)->toJson();
        }

        $employeesChief = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "department_name",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        whereIn("status_jabatan", ["SPV", "CHIEF"])->
        where("department_name", "SEWING")->
        orderBy("enroll_id", "asc")->
        get();

        $employeesLeader = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "department_name",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        whereIn("status_jabatan", ["LEADER", "SPV", "CHIEF"])->
        where("department_name", "SEWING")->
        orderBy("enroll_id", "asc")->
        get();

        $lines = UserLine::select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("sewing.master-line.master-line", ["lines" => $lines, "employeesChief" => $employeesChief, "employeesLeader" => $employeesLeader, "page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "master-line"]);
    }

    public function show($id = 0) {

    }

    public function create() {
        $employees = MasterEmployee::select(
                "enroll_id",
                "employee_name",
                "status_jabatan",
                "department_name",
                "nik"
            )->
            where("status_aktif", "AKTIF")->
            whereIn("status_jabatan", ["LEADER", "SPV", "CHIEF"])->
            where("department_name", "SEWING")->
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
            "chief_id" => "required",
            "chief_nik" => "required",
            "chief_name" => "required",
            "leader_id" => "nullable",
            "leader_nik" => "nullable",
            "leader_name" => "nullable",
        ]);

        $storeEmployeeLine = EmployeeLine::create([
            "tanggal" => $validatedRequest["tanggal"],
            "line_id" => $validatedRequest["line_id"],
            "line_name" => $validatedRequest["line_name"],
            "chief_id" => $validatedRequest["chief_id"],
            "chief_nik" => $validatedRequest["chief_nik"],
            "chief_name" => $validatedRequest["chief_name"],
            "leader_id" => $validatedRequest["leader_id"],
            "leader_nik" => $validatedRequest["leader_nik"],
            "leader_name" => $validatedRequest["leader_name"],
            "created_by" => Auth::user()->id,
            "created_by_username" => Auth::user()->username,
        ]);

        if ($storeEmployeeLine) {
            $chief = MasterEmployee::where("enroll_id", $validatedRequest["chief_id"])->first();
            $leader = MasterEmployee::where("enroll_id", $validatedRequest["leader_id"])->first();

            if ($leader) {
                $storeLeader = EmployeeProduction::updateOrCreate(
                    ['enroll_id' => $leader->enroll_id],
                    [
                        'name' => $leader->employee_name,
                        'role' => "leader",
                        'created_by' => Auth::user()->id,
                        "created_by_username" => Auth::user()->username,
                    ]
                );
            }

            return array(
                "status" => 200,
                "message" => "Leader Line berhasil disimpan.",
                "table" => "datatable",
                "additional" => [$storeEmployeeLine],
            );
        }

        return array(
            "status" => 400,
            "message" => "Leader Line gagal disimpan.",
            "additional" => $storeEmployeeLine,
        );
    }

    public function update(Request $request) {
        $validatedRequest = $request->validate([
            "edit_id" => "required",
            "edit_tanggal" => "required",
            "edit_line_id" => "required",
            "edit_line_name" => "required",
            "edit_chief_id" => "required",
            "edit_chief_nik" => "required",
            "edit_chief_name" => "required",
            "edit_leader_id" => "nullable",
            "edit_leader_nik" => "nullable",
            "edit_leader_name" => "nullable",
        ]);

        $updateEmployeeLine = EmployeeLine::where("id", $validatedRequest["edit_id"])->update([
            "tanggal" => $validatedRequest["edit_tanggal"],
            "line_id" => $validatedRequest["edit_line_id"],
            "line_name" => $validatedRequest["edit_line_name"],
            "chief_id" => $validatedRequest["edit_chief_id"],
            "chief_nik" => $validatedRequest["edit_chief_nik"],
            "chief_name" => $validatedRequest["edit_chief_name"],
            "leader_id" => $validatedRequest["edit_leader_id"],
            "leader_nik" => $validatedRequest["edit_leader_nik"],
            "leader_name" => $validatedRequest["edit_leader_name"]
        ]);

        if ($updateEmployeeLine) {
            $employeeChief = MasterEmployee::where("enroll_id", $validatedRequest["edit_chief_id"])->first();
            $employeeLeader = MasterEmployee::where("enroll_id", $validatedRequest["edit_leader_id"])->first();

            if ($employeeChief) {
                $storeEmployeeChief = EmployeeProduction::updateOrCreate(
                    ['enroll_id' => $employeeChief->enroll_id],
                    [
                        'name' => $employeeChief->employee_name,
                        'role' => "chief",
                    ]
                );
            }

            if ($employeeLeader) {
                $storeEmployeeLeader = EmployeeProduction::updateOrCreate(
                    ['enroll_id' => $employeeLeader->enroll_id],
                    [
                        'name' => $employeeLeader->employee_name,
                        'role' => "leader",
                    ]
                );
            }

            return array(
                "status" => 200,
                "message" => "Master Line berhasil diubah.",
                "table" => "datatable",
                "additional" => $updateEmployeeLine,
            );
        }

        return array(
            "status" => 400,
            "message" => "Master Line gagal diubah.",
            "additional" => $updateEmployeeLine,
        );
    }

    public function destroy($id = 0) {
        if ($id) {
            $destroyEmployeeLine = EmployeeLine::where("id", $id)->delete();

            if ($destroyEmployeeLine) {
                return array(
                    "status" => 200,
                    "message" => "Master Line berhasil dihapus.",
                    "table" => "datatable",
                    "additional" => $destroyEmployeeLine,
                );
            }

            return array(
                "status" => 400,
                "message" => "Master Line gagal dihapus.",
                "additional" => $destroyEmployeeLine,
            );
        }

        return array(
            "status" => 400,
            "message" => "Data tidak ditemukan.",
            "additional" => $destroyEmployeeLine,
        );
    }

    public function updateImage() {
        ini_set("memory_limit", "4086M");

        $employeeProduction = EmployeeProduction::get();

        $employees = MasterEmployee::whereIn("enroll_id", $employeeProduction->pluck("enroll_id"))->get();

        if ($employees->count() > 0) {
            foreach ($employees as $employee) {
                // Fetch the image content from the URL
                $employeeUrlEncode = str_replace(" ", "%20", $employee->nik." ".$employee->employee_name);

                $employeeImgUrl = 'http://10.10.5.111/hris/public/storage/app/public/images/'.$employeeUrlEncode.'.png';

                $response = Http::get($employeeImgUrl);

                // Check if the response status code is 200 (OK)
                if ($response->successful()) {
                    $employeeImgFile = file_get_contents($employeeImgUrl);

                    // Create the file name dynamically
                    $employeeImgName = $employee->nik.' '.$employee->employee_name.'.png';

                    // Create an instance of the image from the file contents
                    $employeeImgFileSize = Image::make($employeeImgFile)->filesize();
                    $employeeImgWidth = Image::make($employeeImgFile)->width();
                    $employeeImgHeight = Image::make($employeeImgFile)->height();

                    if ($employeeImgWidth >= 1500 || $employeeImgHeight >= 1500) {
                        $employeeImg = Image::make($employeeImgFile)->resize((10/100)*$employeeImgWidth, (10/100)*$employeeImgHeight);
                    } else {
                        $employeeImg = Image::make($employeeImgFile)->resize((75/100)*$employeeImgWidth, (75/100)*$employeeImgHeight);
                    }

                    // Define the path where you want to store the image
                    $filePath = $employeeImgName;  // Save in the public disk

                    // Save the image content to the storage
                    Storage::disk('public_employee_profile')->put($filePath, $employeeImg->stream());
                }
            }

            return array(
                "status" => 200,
                "message" => $employees->count()." Gambar Berhasil Disimpan.",
                "additional" => []
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan.",
            "additional" => []
        );
    }
}
