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
        whereIn("status_jabatan", ["CHIEF"])->
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
        whereIn("status_jabatan", ["SPV", "CHIEF", "LEADER"])->
        where("department_name", "SEWING")->
        orderBy("enroll_id", "asc")->
        get();

        $employeesIe = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "department_name",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        whereIn("status_jabatan", ["SPV", "LEADER", "STAFF"])->
        where("department_name", "INDUSTRIAL ENGINEERING")->
        orderBy("enroll_id", "asc")->
        get();

        $employeesLeaderQc = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "department_name",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        whereIn("status_jabatan", ["SPV", "CHIEF", "LEADER", "STAFF"])->
        where("department_name", "QUALITY CONTROL")->
        orderBy("enroll_id", "asc")->
        get();

        $employeesMechanic = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "department_name",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        where("status_jabatan", "!=", "ADMINISTRASI")->
        where("department_name", "MECHANIC")->
        orderBy("enroll_id", "asc")->
        get();

        $employeesTechnical = MasterEmployee::select(
            "enroll_id",
            "employee_name",
            "status_jabatan",
            "department_name",
            "nik"
        )->
        where("status_aktif", "AKTIF")->
        where("department_name", "TECHNICAL")->
        orderBy("enroll_id", "asc")->
        get();

        $lines = UserLine::select('line_id', "username")->where('Groupp', 'SEWING')->whereRaw("(Locked != 1 || Locked IS NULL)")->orderBy('line_id', 'asc')->get();

        return view("sewing.master-line.master-line", ["lines" => $lines, "employeesChief" => $employeesChief, "employeesLeader" => $employeesLeader, "employeesIe" => $employeesIe, "employeesLeaderQc" => $employeesLeaderQc, "employeesMechanic" => $employeesMechanic, "employeesTechnical" => $employeesTechnical, "page" => "dashboard-sewing-eff", "subPageGroup" => "sewing-master", "subPage" => "master-line"]);
    }

    public function show($id = 0) {
        //
    }

    // Unused function
    public function create() {
        $employees = MasterEmployee::select(
                "enroll_id",
                "employee_name",
                "status_jabatan",
                "department_name",
                "nik"
            )->
            where("status_aktif", "AKTIF")->
            whereIn("status_jabatan", ["LEADER", "CHIEF"])->
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
            "chief_id" => "nullable",
            "chief_nik" => "nullable",
            "chief_name" => "nullable",
            "leader_id" => "nullable",
            "leader_nik" => "nullable",
            "leader_name" => "nullable",
            "ie_id" => "nullable",
            "ie_nik" => "nullable",
            "ie_name" => "nullable",
            "leaderqc_id" => "nullable",
            "leaderqc_nik" => "nullable",
            "leaderqc_name" => "nullable",
            "mechanic_id" => "nullable",
            "mechanic_nik" => "nullable",
            "mechanic_name" => "nullable",
            "technical_id" => "nullable",
            "technical_nik" => "nullable",
            "technical_name" => "nullable",
        ]);

        if ($validatedRequest["chief_id"] || $validatedRequest["leader_id"]) {
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
                "ie_id" => $validatedRequest["ie_id"],
                "ie_nik" => $validatedRequest["ie_nik"],
                "ie_name" => $validatedRequest["ie_name"],
                "leaderqc_id" => $validatedRequest["leaderqc_id"],
                "leaderqc_nik" => $validatedRequest["leaderqc_nik"],
                "leaderqc_name" => $validatedRequest["leaderqc_name"],
                "mechanic_id" => $validatedRequest["mechanic_id"],
                "mechanic_nik" => $validatedRequest["mechanic_nik"],
                "mechanic_name" => $validatedRequest["mechanic_name"],
                "technical_id" => $validatedRequest["technical_id"],
                "technical_nik" => $validatedRequest["technical_nik"],
                "technical_name" => $validatedRequest["technical_name"],
                "created_by" => Auth::user()->id,
                "created_by_username" => Auth::user()->username,
            ]);

            if ($storeEmployeeLine) {
                $chief = MasterEmployee::where("enroll_id", $validatedRequest["chief_id"])->first();
                $leader = MasterEmployee::where("enroll_id", $validatedRequest["leader_id"])->first();
                $ie = MasterEmployee::where("enroll_id", $validatedRequest["ie_id"])->first();
                $leaderqc = MasterEmployee::where("enroll_id", $validatedRequest["leaderqc_id"])->first();
                $mechanic = MasterEmployee::where("enroll_id", $validatedRequest["mechanic_id"])->first();
                $technical = MasterEmployee::where("enroll_id", $validatedRequest["technical_id"])->first();

                if ($chief) {
                    $storeIe = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $chief->enroll_id],
                        [
                            'name' => $chief->employee_name,
                            'role' => "chief",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

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

                if ($ie) {
                    $storeIe = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $ie->enroll_id],
                        [
                            'name' => $ie->employee_name,
                            'role' => "ie",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                if ($leaderqc) {
                    $storeLeaderqc = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $leaderqc->enroll_id],
                        [
                            'name' => $leaderqc->employee_name,
                            'role' => "leaderqc",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                if ($mechanic) {
                    $storeMechanic = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $mechanic->enroll_id],
                        [
                            'name' => $mechanic->employee_name,
                            'role' => "mechanic",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                if ($technical) {
                    $storeTechnical = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $technical->enroll_id],
                        [
                            'name' => $technical->employee_name,
                            'role' => "technical",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                return array(
                    "status" => 300,
                    "message" => "Leader Line berhasil disimpan.",
                    "table" => "datatable",
                    "additional" => [$storeEmployeeLine],
                );
            }
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
            "edit_chief_id" => "nullable",
            "edit_chief_nik" => "nullable",
            "edit_chief_name" => "nullable",
            "edit_leader_id" => "nullable",
            "edit_leader_nik" => "nullable",
            "edit_leader_name" => "nullable",
            "edit_ie_id" => "nullable",
            "edit_ie_nik" => "nullable",
            "edit_ie_name" => "nullable",
            "edit_leaderqc_id" => "nullable",
            "edit_leaderqc_nik" => "nullable",
            "edit_leaderqc_name" => "nullable",
            "edit_mechanic_id" => "nullable",
            "edit_mechanic_nik" => "nullable",
            "edit_mechanic_name" => "nullable",
            "edit_technical_id" => "nullable",
            "edit_technical_nik" => "nullable",
            "edit_technical_name" => "nullable",
        ]);

        if ($validatedRequest["edit_chief_id"] || $validatedRequest["edit_leader_id"]) {
            $updateEmployeeLine = EmployeeLine::where("id", $validatedRequest["edit_id"])->update([
                "tanggal" => $validatedRequest["edit_tanggal"],
                "line_id" => $validatedRequest["edit_line_id"],
                "line_name" => $validatedRequest["edit_line_name"],
                "chief_id" => $validatedRequest["edit_chief_id"],
                "chief_nik" => $validatedRequest["edit_chief_nik"],
                "chief_name" => $validatedRequest["edit_chief_name"],
                "leader_id" => $validatedRequest["edit_leader_id"],
                "leader_nik" => $validatedRequest["edit_leader_nik"],
                "leader_name" => $validatedRequest["edit_leader_name"],
                "ie_id" => $validatedRequest["edit_ie_id"],
                "ie_nik" => $validatedRequest["edit_ie_nik"],
                "ie_name" => $validatedRequest["edit_ie_name"],
                "leaderqc_id" => $validatedRequest["edit_leaderqc_id"],
                "leaderqc_nik" => $validatedRequest["edit_leaderqc_nik"],
                "leaderqc_name" => $validatedRequest["edit_leaderqc_name"],
                "mechanic_id" => $validatedRequest["edit_mechanic_id"],
                "mechanic_nik" => $validatedRequest["edit_mechanic_nik"],
                "mechanic_name" => $validatedRequest["edit_mechanic_name"],
                "technical_id" => $validatedRequest["edit_technical_id"],
                "technical_nik" => $validatedRequest["edit_technical_nik"],
                "technical_name" => $validatedRequest["edit_technical_name"],
            ]);

            if ($updateEmployeeLine) {
                $chief = MasterEmployee::where("enroll_id", $validatedRequest["edit_chief_id"])->first();
                $leader = MasterEmployee::where("enroll_id", $validatedRequest["edit_leader_id"])->first();
                $ie = MasterEmployee::where("enroll_id", $validatedRequest["edit_ie_id"])->first();
                $leaderqc = MasterEmployee::where("enroll_id", $validatedRequest["edit_leaderqc_id"])->first();
                $mechanic = MasterEmployee::where("enroll_id", $validatedRequest["edit_mechanic_id"])->first();
                $technical = MasterEmployee::where("enroll_id", $validatedRequest["edit_technical_id"])->first();

                if ($chief) {
                    $storeIe = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $chief->enroll_id],
                        [
                            'name' => $chief->employee_name,
                            'role' => "chief",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

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

                if ($ie) {
                    $storeIe = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $ie->enroll_id],
                        [
                            'name' => $ie->employee_name,
                            'role' => "ie",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                if ($leaderqc) {
                    $storeLeaderqc = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $leaderqc->enroll_id],
                        [
                            'name' => $leaderqc->employee_name,
                            'role' => "leaderqc",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                if ($mechanic) {
                    $storeMechanic = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $mechanic->enroll_id],
                        [
                            'name' => $mechanic->employee_name,
                            'role' => "mechanic",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                if ($technical) {
                    $storeTechnical = EmployeeProduction::updateOrCreate(
                        ['enroll_id' => $technical->enroll_id],
                        [
                            'name' => $technical->employee_name,
                            'role' => "technical",
                            'created_by' => Auth::user()->id,
                            "created_by_username" => Auth::user()->username,
                        ]
                    );
                }

                return array(
                    "status" => 300,
                    "message" => "Master Line berhasil diubah.",
                    "table" => "datatable",
                    "additional" => $updateEmployeeLine,
                );
            }
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
        ini_set("max_execution_time", "3600");
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
