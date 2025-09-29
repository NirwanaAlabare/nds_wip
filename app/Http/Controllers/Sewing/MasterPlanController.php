<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\MasterPlan;
use App\Imports\ImportMasterPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DB;

class MasterPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tglPlan = $request->date ? $request->date : date('Y-m-d');

            $masterPlan = MasterPlan::selectRaw("
                master_plan.id,
                master_plan.tgl_plan,
                master_plan.sewing_line,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.styleno_prod style_production,
                master_plan.color,
                master_plan.smv,
                master_plan.jam_kerja,
                master_plan.man_power,
                master_plan.plan_target,
                master_plan.target_effy
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            join("so", "so.id_cost", "=", "act_costing.id")->
            join(DB::raw("(select so_det.id, so_det.id_so, so_det.color, so_det.styleno_prod from so_det group by id_so, color) so_det"), function ($join) {
                $join->on("so_det.id_so", "=", "so.id");
                $join->on("so_det.color", "=", "master_plan.color");
            })->
            where("tgl_plan", $tglPlan)->
            where("master_plan.cancel", "N")->
            orderBy("sewing_line", "asc")->
            get();

            return Datatables::of($masterPlan)->toJson();
        }

        return view('sewing.master-plan.master-plan', ["subPageGroup" => "sewing-master", "subPage" => "master-plan", "page" => "dashboard-sewing-eff"]);
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
            "sewing_line" => "required",
            "tgl_plan" => "required",
            "id_ws" => "required",
            "color_select" => "required",
            "smv" => "required|numeric|min:0|not_in:0",
            "jam_kerja" => "required|numeric|min:0|not_in:0",
            "man_power" => "required|numeric|min:0|not_in:0",
            "plan_target" => "required|numeric|min:0|not_in:0",
            "target_effy" => "required|numeric|min:0|not_in:0",
        ]);

        $storeMasterPlan = MasterPlan::create([
            "id_plan" => str_replace("-", "", $validatedRequest['tgl_plan']),
            "sewing_line" => $validatedRequest['sewing_line'],
            "tgl_plan" => $validatedRequest['tgl_plan'],
            "tgl_input" => Carbon::now(),
            "id_ws" => $validatedRequest['id_ws'],
            "color" => $validatedRequest['color_select'],
            "smv" => $validatedRequest['smv'],
            "jam_kerja" => $validatedRequest['jam_kerja'],
            "man_power" => $validatedRequest['man_power'],
            "plan_target" => $validatedRequest['plan_target'],
            "target_effy" => $validatedRequest['target_effy'],
            "create_by" => Auth::user()->username,
            "cancel" => "N",
        ]);

        if ($storeMasterPlan) {
            if($request->hasFile('gambar')) {
                $file = $request->file('gambar');

                //you also need to keep file extension as well
                // $name = $file->getClientOriginalName().'.'.$file->getClientOriginalExtension();
                $name = $file->getClientOriginalName();

                //using the array instead of object
                $image['filePath'] = $name;
                $file->move(public_path().'/storage/', $name);
                $storeMasterPlan->gambar = $name;
                $storeMasterPlan->save();
            }

            return array(
                'status' => 200,
                'message' => 'Master plan berhasil dibuat',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Master plan tidak berhasil dibuat',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($line = null, $date = null)
    {
        if ($line && $date) {
            $actCosting = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();
            $color = ActCosting::where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();
            $masterPlan = MasterPlan::selectRaw("
                master_plan.id,
                master_plan.id_ws,
                master_plan.tgl_plan,
                master_plan.sewing_line,
                act_costing.kpno no_ws,
                act_costing.styleno style,
                so_det.styleno_prod style_production,
                master_plan.color,
                master_plan.smv,
                master_plan.jam_kerja,
                master_plan.man_power,
                master_plan.plan_target,
                master_plan.target_effy,
                master_plan.cancel,
                CONCAT('http://10.10.5.62:8080/erp/pages/prod_new/upload_files/', master_plan.gambar) gambar
            ")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            join("so", "so.id_cost", "=", "act_costing.id")->
            join(DB::raw("(select so_det.id, so_det.id_so, so_det.color, so_det.styleno_prod from so_det group by id_so, color) so_det"), function ($join) {
                $join->on("so_det.id_so", "=", "so.id");
                $join->on("so_det.color", "=", "master_plan.color");
            })->
            where("sewing_line", $line)->
            where("tgl_plan", $date)->
            orderBy("cancel", "asc")->
            orderBy("smv", "desc")->
            get();

            return view("sewing.master-plan.master-plan-detail", [
                "line" => $line, "date" => $date, "actCosting" => $actCosting, "masterPlan" => $masterPlan,
                "subPageGroup" => "sewing-master", "subPage" => "master-plan", "page" => "dashboard-sewing-eff"
            ]);
        }

        return Redirect::to('/master-plan');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_tgl_plan" => "required",
            "edit_id" => "required",
            "edit_id_ws" => "required",
            "edit_color_select" => "required",
            "edit_smv" => "required|numeric|min:0|not_in:0",
            "edit_jam_kerja" => "required|numeric|min:0|not_in:0",
            "edit_man_power" => "required|numeric|min:0|not_in:0",
            "edit_plan_target" => "required|numeric|min:0|not_in:0",
            "edit_target_effy" => "required|numeric|min:0|not_in:0",
            "edit_status" => "nullable",
        ]);

        $editGambarNew = null;
        if($request->hasFile('edit_gambar_new')) {
            $file = $request->file('edit_gambar_new');

            //you also need to keep file extension as well
            // $name = $file->getClientOriginalName().'.'.$file->getClientOriginalExtension();
            $name = $file->getClientOriginalName();

            //using the array instead of object
            $image['filePath'] = $name;
            $file->move(public_path().'/storage/', $name);
            $editGambarNew = $name;
        }

        $masterPlan = MasterPlan::where("id", $request->edit_id)->first();

        if ($masterPlan->rfts->count()+$masterPlan->defects->count()+$masterPlan->rejects->count() < 1) {
            $updateMasterPlan = MasterPlan::where("id", $request->edit_id)->update([
                "tgl_plan" => $request->edit_tgl_plan,
                "id_ws" => $request->edit_id_ws,
                "color" => $request->edit_color,
                "smv" => $request->edit_smv,
                "jam_kerja" => $request->edit_jam_kerja,
                "man_power" => $request->edit_man_power,
                "plan_target" => $request->edit_plan_target,
                "target_effy" => $request->edit_target_effy,
                "cancel" => $request->edit_status ? 'N' : $request->edit_status,
                // "gambar" => $editGambarNew
            ]);

            if ($updateMasterPlan) {
                return array(
                    'status' => 200,
                    'message' => 'Data master plan berhasil diubah',
                    'redirect' => '',
                    'table' => '',
                    'additional' => [],
                );
            }
        } else {
            return array(
                'status' => 400,
                'message' => 'Data master plan sudah memiliki output',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data master plan tidak berubah',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dataOutput = collect(
            DB::connection("mysql_sb")->select("
                SELECT output.* FROM (
                    (select master_plan_id, kode_numbering, id, created_at, updated_at from output_rfts WHERE master_plan_id = '".$id."' LIMIT 1)
                    UNION
                    (select master_plan_id, kode_numbering, id, created_at, updated_at from output_defects WHERE master_plan_id = '".$id."' LIMIT 1)
                    UNION
                    (select master_plan_id, kode_numbering, id, created_at, updated_at from output_rejects WHERE master_plan_id = '".$id."' LIMIT 1)
                ) output
            ")
        )->count();

        if ($dataOutput < 1) {
            $destroyMasterPlan = MasterPlan::find($id)->update(["cancel" => "Y"]);

            if ($destroyMasterPlan) {
                return array(
                    'status' => 200,
                    'message' => 'Plan berhasil dihapus',
                    'redirect' => '',
                    'table' => '',
                    'additional' => [],
                    'callback' => 'reloadWindow()',
                );
            }
        } else {
            return array(
                'status' => 400,
                'message' => 'Plan sudah memiliki output',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Plan gagal dihapus',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function importMasterPlan(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = rand().$file->getClientOriginalName();

        $file->move('file_upload',$nama_file);

        Excel::import(new ImportMasterPlan, public_path('/file_upload/'.$nama_file));

        return array(
            "status" => 200,
            "message" => 'Data Berhasil Di Upload',
            "additional" => [],
        );
    }
}
