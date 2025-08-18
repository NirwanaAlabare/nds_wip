<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\WorksheetProduction;
use DB;
use QrCode;
use PDF;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $worksheetQuery = DB::connection("mysql_sb")->
                table("so_det")->
                selectRaw("
                    so_det.id,
                    mastersupplier.Supplier buyer,
                    act_costing.id ws_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color color,
                    so_det.size size,
                    so_det.dest dest,
                    so_det.qty qty,
                    act_costing.cost_date tgl_cost,
                    act_costing.deldate tgl_del
                ")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
                where("act_costing.status", "!=", "CANCEL")->
                where("act_costing.cost_date", ">=", "2023-01-01")->
                where("act_costing.type_ws", "STD")->
                groupBy("so_det.id")->
                orderBy("act_costing.cost_date")->
                orderBy("act_costing.kpno")->
                orderBy("so_det.id");

            if ($request->month) {
                $worksheetQuery->whereRaw("(MONTH(act_costing.cost_date) = '".$request->month."' OR MONTH(act_costing.del_date) = '".$request->month."')");
            }

            if ($request->year) {
                $worksheetQuery->whereRaw("(YEAR(act_costing.cost_date) = '".$request->year."' OR YEAR(act_costing.del_date) = '".$request->year."')");
            }

            return DataTables::of($worksheetQuery)->toJson();
        }

        return view("worksheet.worksheet");
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function printQr(Request $request) {
        ini_set('memory', '2000M');
        ini_set('max_execution_time', '360000');

        if ($request->id) {
            $soDet = DB::connection("mysql_sb")->
                table("so_det")->
                selectRaw("
                    act_costing.id ws_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.id,
                    so_det.color,
                    so_det.size,
                    so_det.qty
                ")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                where("act_costing.id", $request->id)->
                orderBy("so_det.id")->
                get();

            if ($soDet) {
                $printData = [];

                foreach ($soDet->groupBy('id') as $sdGroup) {
                    for ($i = 1;$i <= $sdGroup->sum('qty'); $i++) {
                        $upsertWorksheetProduction = WorksheetProduction::updateOrCreate(
                            ['so_det_id' => $sdGroup->first()->id, 'number' => $i],
                            [
                                'act_costing_id' => $sdGroup->first()->ws_id,
                                'act_costing_ws' => $sdGroup->first()->ws,
                                'color' => $sdGroup->first()->color,
                                'size' => $sdGroup->first()->size
                            ]
                        );

                        if ($upsertWorksheetProduction) {
                            array_push(
                                $printData,
                                [
                                    'so_det_id' => $sdGroup->first()->id,
                                    'number' => $i,
                                ]
                            );
                        }
                    }
                }
            }

            if (count($printData) > 0) {
                $customPaper = array(0, 0, 56.70, 33.39);
                $pdf = PDF::loadView('worksheet.pdf.print-qr', ["data" => $printData])->setPaper($customPaper);

                $fileName = str_replace("/", "-", ($soDet->first()->ws. '-' . $soDet->first()->style . ' - Number.pdf'));

                return $pdf->download(str_replace("/", "_", $fileName));
            }
        }

        return array(
            'status' => 400,
            'message' => "Data tidak ditemukan"
        );
    }
}
