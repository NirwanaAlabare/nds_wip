<?php

namespace App\Http\Controllers\WhsSoljer;

use App\Http\Controllers\Controller;
use App\Models\WhsSoljer\MasterKoli;
use App\Models\WhsSoljer\PenerimaanGudangInputan;
use App\Models\WhsSoljer\PenerimaanGudangInputanDetail;
use App\Models\WhsSoljer\PenerimaanGudangInputanHistory;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class MasterKoliWhsSoljerController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $data = MasterKoli::query();

            return DataTables::eloquent($data)->filter(function ($query) {
                
            }, true)
            ->order(function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->toJson();
        }

        return view("whs-soljer.master-koli.index", [
            "page" => "dashboard-whs-soljer",
            "subPageGroup" => "master-whs-soljer",
            'containerFluid' => true
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_koli' => 'required|integer|unique:whs_master_koli,no_koli'
        ], [
            'no_koli.unique' => 'No Koli sudah digunakan'
        ]);

        if ($validator->fails()) {
            return [
                "status" => 400,
                "message" => $validator->errors()->first(),
            ];
        }

        $kodeKoli = 'WHSKOLI_' . $request->no_koli;
        
        $user = Auth::user();
        $now = Carbon::now();

        $data = new MasterKoli();
        $data->no_koli             = $request->no_koli;
        $data->kode_koli           = $kodeKoli;
        $data->created_by          = $user ? $user->id : null;
        $data->created_by_username = $user ? $user->username : null;
        $data->created_at          = $now;

        $data->save();

        return [
            "status" => 200,
            "message" => "Data berhasil disimpan",
            "additional" => [],
        ];
    }

    public function delete($id)
    {
        $data = MasterKoli::findOrFail($id);
        $data->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didelete'
        ]);
    }

    public function printBarcode($id)
    {

        $data = MasterKoli::where('id', $id)->first();
        
        // PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('whs-soljer.master-koli.print-barcode', ["data" => $data])->setPaper('a7', 'landscape');

        $fileName = 'Master_Koli_' . $id . '.pdf';

        return $pdf->stream(str_replace("/", "_", $fileName));
    }
}
