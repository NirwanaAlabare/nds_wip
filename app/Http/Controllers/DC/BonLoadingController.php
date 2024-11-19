<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\LoadingLinePlan;
use App\Models\LoadingLine;
use App\Models\LoadingLineHistory;
use App\Models\Trolley;
use App\Models\TrolleyStocker;
use App\Models\Stocker;
use App\Models\YearSequence;
use App\Models\SignalBit\UserLine;

use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DB;

class BonLoadingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $years = array_reverse(range(1999, date('Y')));
        $lines = UserLine::where("Groupp", "SEWING")->whereRaw("(Locked is null OR Locked != 1)")->orderBy("line_id", 'asc')->get();

        return view("dc.loading-line.bon-loading", ['page' => 'dashboard-dc', "subPageGroup" => "loading-dc", "subPage" => "bon-loading-line"], ["years" => $years, "lines" => $lines]);
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
        $now = Carbon::now();

        $lastLoadingLine = LoadingLine::select('kode')->orderBy("id", "desc")->first();
        $lastLoadingLineNumber = $lastLoadingLine ? intval(substr($lastLoadingLine->kode, -5)) + 1 : 1;
        $line = UserLine::where("line_id", $request->line_id)->first();

        $success = [];
        $fail = [];
        $exist = [];

        for ($i = 1; $i <= count($request['stocker']); $i++) {
            if ($request['stocker'][$i]) {
                $thisStockerData = Stocker::where('id_qr_stocker', $request['stocker'][$i])->first();

                if ($thisStockerData) {
                    // Loading Line
                    $loadingStockArr = [];
                    $stockerIds = [];

                    $similarStockerData = Stocker::where('form_cut_id', $thisStockerData->form_cut_id)->
                        where('so_det_id', $thisStockerData->so_det_id)->
                        where('group_stocker', $thisStockerData->group_stocker)->
                        where('ratio', $thisStockerData->ratio)->
                        get();

                    foreach ($similarStockerData as $stocker) {
                        $loadingLinePlan = LoadingLinePlan::where("act_costing_ws", $thisStockerData->act_costing_ws)->where("color", $thisStockerData->color)->where("line_id", $line->line_id)->first();

                        $isExist = LoadingLine::where("stocker_id", $stocker->id)->count();

                        if ($isExist < 1) {
                            if ($loadingLinePlan) {
                                array_push($loadingStockArr, [
                                    "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                    "line_id" => $line->line_id,
                                    "loading_plan_id" => $loadingLinePlan->id,
                                    "nama_line" => $line->username,
                                    "stocker_id" => $stocker->id,
                                    "qty" => ($stocker->qty_ply_mod > 0 ? $stocker->qty_ply_mod : $stocker->qty_ply) + ($stocker->dcIn ? ((0 - $stocker->dcIn->qty_reject) + $stocker->dcIn->qty_replace) : 0) + ($stocker->secondaryInHouse ? ((0 - $stocker->secondaryInHouse->qty_reject) + $stocker->secondaryInHouse->qty_replace) : 0) + ($stocker->secondaryIn ? ((0 - $stocker->secondaryIn->qty_reject) + $stocker->secondaryIn->qty_replace) : 0),
                                    "status" => "active",
                                    "tanggal_loading" => $request->tanggal_loading,
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ]);

                                array_push($stockerIds, $stocker->id);
                            } else {
                                $lastLoadingPlan = LoadingLinePlan::selectRaw("MAX(kode) latest_kode")->first();
                                $lastLoadingPlanNumber = intval(substr($lastLoadingPlan->latest_kode, -5)) + 1;
                                $kodeLoadingPlan = 'LLP'.sprintf('%05s', $lastLoadingPlanNumber);

                                $storeLoadingPlan = LoadingLinePlan::create([
                                    "line_id" => $line->line_id,
                                    "kode" => $kodeLoadingPlan,
                                    "act_costing_id" => $thisStockerData->formCut->marker->act_costing_id,
                                    "act_costing_ws" => $thisStockerData->formCut->marker->act_costing_ws,
                                    "buyer" => $thisStockerData->formCut->marker->buyer,
                                    "style" => $thisStockerData->formCut->marker->style,
                                    "color" => $thisStockerData->formCut->marker->color,
                                    "tanggal" => $request->tanggal_loading,
                                ]);

                                array_push($loadingStockArr, [
                                    "kode" => "LOAD".sprintf('%05s', ($lastLoadingLineNumber+$i)),
                                    "line_id" => $line->line_id,
                                    "loading_plan_id" => $storeLoadingPlan->id,
                                    "nama_line" => $line->username,
                                    "stocker_id" => $stocker->id,
                                    "qty" => ($stocker->qty_ply_mod > 0 ? $stocker->qty_ply_mod : $stocker->qty_ply) + ($stocker->dcIn ? ((0 - $stocker->dcIn->qty_reject) + $stocker->dcIn->qty_replace) : 0) + ($stocker->secondaryInHouse ? ((0 - $stocker->secondaryInHouse->qty_reject) + $stocker->secondaryInHouse->qty_replace) : 0) + ($stocker->secondaryIn ? ((0 - $stocker->secondaryIn->qty_reject) + $stocker->secondaryIn->qty_replace) : 0),
                                    "status" => "active",
                                    "tanggal_loading" => $request['tanggal_loading'],
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                ]);

                                array_push($stockerIds, $stocker->id);
                            }
                        } else {
                            array_push($exist, ['stocker' => $stocker->id]);
                        }
                    }

                    $storeLoadingStock = LoadingLine::insert($loadingStockArr);

                    if (count($loadingStockArr) > 0) {
                        $updateStocker = Stocker::whereIn("id", $stockerIds)->
                            update([
                                "status" => "line",
                                "latest_alokasi" => Carbon::now()
                            ]);

                        $updateTrolleyStocker = TrolleyStocker::whereIn("stocker_id", $stockerIds)->
                            update([
                                "status" => "not active"
                            ]);

                        if ($updateStocker) {
                            array_push($success, ['stocker' => $stockerIds]);
                        } else {
                            array_push($fail, ['stocker' => $stockerIds]);
                        }
                    }

                    // Year Sequence
                    $currentData = YearSequence::selectRaw("
                        number
                    ")->
                    where('form_cut_id', $thisStockerData['form_cut_id'])->
                    where('so_det_id', $thisStockerData['so_det_id'])->
                    where("number", ">=", $thisStockerData['range_awal'])->
                    where("number", "<=", $thisStockerData['range_akhir'])->
                    orderBy('number')->
                    get();

                    if ($request['range_awal'][$i] > 0 && $request['range_awal'][$i] <= $request['range_akhir'][$i] && $request['range_akhir'][$i] <= 999999 && $request['sequence'] > 0) {
                        $yearSequence = YearSequence::selectRaw("year_sequence, year_sequence_number")->where("year", $request['year'])->where("year_sequence", $request['sequence'])->orderBy("year_sequence", "desc")->orderBy("year_sequence_number", "desc")->first();
                        $yearSequenceSequence = $yearSequence ? $yearSequence->year_sequence : $request['sequence'];
                        $yearSequenceNumber = $yearSequence ? $yearSequence->year_sequence_number + 1 : 1;

                        $upsertData = [];

                        $n = 0;
                        $n1 = 0;
                        $largeCount = 0;

                        for ($j = $request['range_awal'][$i]; $j <= $request['range_akhir'][$i]; $j++) {

                            if ($j > 999999) {
                                $yearSequenceSequence = $yearSequenceSequence + 1;
                                $yearSequenceNumber = 1;
                            }

                            if ($currentData->where('number', $thisStockerData['range_awal']+$n)->count() < 1) {
                                $currentNumber = ($currentData->count() > 0 ? $currentData->max("number")+1+$n : $thisStockerData['range_awal']+$n);

                                array_push($upsertData, [
                                    "id_year_sequence" => $request['year']."_".($yearSequenceSequence)."_".($request['range_awal'][$i]+$n1),
                                    "year" => $request['year'],
                                    "year_sequence" => $yearSequenceSequence,
                                    "year_sequence_number" => ($request['range_awal'][$i]+$n1),
                                    "form_cut_id" => $thisStockerData['form_cut_id'],
                                    "so_det_id" => $thisStockerData['so_det_id'],
                                    "size" => $thisStockerData['size'],
                                    "number" => ($currentNumber > $thisStockerData['range_akhir'] ? $thisStockerData['range_akhir'] : ($currentNumber)),
                                    "id_qr_stocker" => $thisStockerData["id_qr_stocker"],
                                    "created_at" => $now,
                                    "updated_at" => $now,
                                ]);

                                if (count($upsertData) % 5000 == 0) {
                                    YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], ['form_cut_id', 'so_det_id', 'size', 'number', 'id_qr_stocker', 'created_at', 'updated_at']);

                                    $upsertData = [];

                                    $largeCount++;
                                }

                                $n1++;
                            }

                            $n++;
                        }

                        if (count($upsertData) > 0) {
                            YearSequence::upsert($upsertData, ['id_year_sequence', 'year', 'year_sequence', 'year_sequence_number'], ['form_cut_id', 'so_det_id', 'size', 'number', 'id_qr_stocker', 'created_at', 'updated_at']);
                        }
                    }

                    // Bon Loading

                }
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 201,
                'message' => 'Stocker berhasil di loading',
                'redirect' => 'reload',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data tidak ditemukan',
            'redirect' => 'reload',
            'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
        );
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
}
