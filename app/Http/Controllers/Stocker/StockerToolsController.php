<?php

namespace App\Http\Controllers\Stocker;

use App\Http\Controllers\Controller;
use App\Imports\ImportStockerManual;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutInputDetail;
use App\Models\Dc\DCIn;
use App\Models\Dc\LoadingLine;
use App\Models\Dc\LoadingLinePlan;
use App\Models\Dc\RackDetailStocker;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\SecondaryInhouse;
use App\Models\Dc\SecondaryInhouseIn;
use App\Models\Dc\TrolleyStocker;
use App\Models\SignalBit\FormCut;
use App\Models\Stocker\ModifySizeQty;
use App\Models\Stocker\Stocker;
use App\Models\Stocker\StockerAdditional;
use App\Models\Stocker\StockerAdditionalDetail;
use App\Models\Stocker\StockerSeparate;
use App\Models\Stocker\StockerSeparateDetail;
use App\Models\Stocker\YearSequence;
use App\Services\StockerService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class StockerToolsController extends Controller
{
    public function index()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno', 'styleno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('stocker.tools.tools', [
            "page" => "dashboard-stocker",
            "orders" => $orders
        ]);
    }

    public function resetStockerForm(Request $request)
    {
        ini_set('max_execution_time', 3600);

        // Check Closing
        if($request->form_type == 'reject'){
            $dataCheckClosing = DB::table("form_cut_reject")
            ->selectRaw("tanggal")
            ->where("id", $request->form_cut_id)
            ->first();
        }elseif($request->form_type == 'piece'){
            $dataCheckClosing = DB::table("form_cut_piece")
            ->selectRaw("DATE_FORMAT(waktu_selesai, '%Y-%m-%d') as tanggal")
            ->where("id", $request->form_cut_id)
            ->first();
        }else{
            $dataCheckClosing = DB::table("form_cut_input")
            ->selectRaw("DATE_FORMAT(waktu_selesai, '%Y-%m-%d') as tanggal")
            ->where("id", $request->form_cut_id)
            ->first();
        }

        if (checkClosingDate($dataCheckClosing->tanggal)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            );
        }

        $validatedRequest = $request->validate([
            "form_cut_id" => "required",
            "no_form" => "required",
        ]);

        $formType = 'form_cut_id';
        switch ($request->form_type) {
            case 'reject' :
                $formType = 'form_reject_id';
                break;
            case 'piece' :
                $formType = 'form_piece_id';
                break;
            default :
                $formType = 'form_cut_id';
                break;
        }

        if ($request->form_group) {
            if ($request->form_stocker && count(explode(",", $request->form_stocker)) > 0) {
                $checkYearSequence = YearSequence::leftJoin("stocker_input", function($join) use ($formType) {
                    $join->on("stocker_input.".$formType."", "=", "year_sequence.".$formType."");
                    $join->on("stocker_input.so_det_id", "=", "year_sequence.so_det_id");
                    $join->on("stocker_input.range_awal", "<=", "year_sequence.number");
                    $join->on("stocker_input.range_akhir", ">=", "year_sequence.number");
                })->
                where('year_sequence.'.$formType.'', $validatedRequest['form_cut_id'])->
                where('stocker_input.group_stocker', $request->form_group)->
                whereIn('stocker_input.id', explode(",", $request->form_stocker))->
                count();
            } else {
                $checkYearSequence = YearSequence::leftJoin("stocker_input", function($join) use ($formType) {
                    $join->on("stocker_input.".$formType."", "=", "year_sequence.".$formType."");
                    $join->on("stocker_input.so_det_id", "=", "year_sequence.so_det_id");
                    $join->on("stocker_input.range_awal", "<=", "year_sequence.number");
                    $join->on("stocker_input.range_akhir", ">=", "year_sequence.number");
                })->
                where('year_sequence.'.$formType.'', $validatedRequest['form_cut_id'])->
                where('stocker_input.group_stocker', $request->form_group)->
                count();
            }
        } else {
            $checkYearSequence = YearSequence::where($formType, $validatedRequest['form_cut_id'])->count();
        }

        if ($checkYearSequence > 0) {
            return array(
                'status' => 400,
                'message' => 'Stocker Form <br> "'.$validatedRequest['no_form'].'" <br> memiliki data year sequence (label qr).',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        if ($validatedRequest) {
            // Delete related stocker input
            if ($request->form_group) {
                if ($request->form_stocker && count(explode(",", $request->form_stocker)) > 0) {
                    $stockers = Stocker::where($formType, $validatedRequest['form_cut_id'])->where('group_stocker', $request->form_group)->whereIn('id', explode(",", $request->form_stocker))->get();
                } else {
                    $stockers = Stocker::where($formType, $validatedRequest['form_cut_id'])->where('group_stocker', $request->form_group)->get();
                }
            } else {
                $stockers = Stocker::where($formType, $validatedRequest['form_cut_id'])->get();
            }
            $stockerIdQrs = $stockers->pluck('id_qr_stocker')->toArray();
            $stockerIds = $stockers->pluck('id')->toArray();

            // Log the deletion
            Log::channel('resetStockerForm')->info([
                "Deleting Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                DB::table("stocker_input")->whereIn('id', $stockerIds)->get(),
                DB::table("dc_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("rack_detail_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("trolley_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("loading_line")->whereIn('stocker_id', $stockerIds)->get()
            ]);

            $deleteStocker = Stocker::whereIn('id', $stockerIds)->delete();
            $deleteDc = DCIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryIn = SecondaryIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryInHouseIn = SecondaryInHouseIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryInHouse = SecondaryInHouse::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteRackDetailStocker = RackDetailStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteTrolleyStocker = TrolleyStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteLoadingLine = LoadingLine::whereIn('stocker_id', $stockerIds)->delete();

            return array(
                'status' => 200,
                'message' => 'Stocker Form <br> "'.$validatedRequest['no_form'].'" <br> berhasil direset.',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker Form <br> "'.$validatedRequest['no_form'].'" <br> gagal direset.',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function resetStockerId(Request $request)
    {
        ini_set('max_execution_time', 3600);

        $validatedRequest = $request->validate([
            "stocker_ids" => "required"
        ]);

        $stockers = addQuotesAround($validatedRequest["stocker_ids"]);

        // Check Closing
        $dataCheckClosing = DB::table("form_cut_input")
            ->selectRaw("DATE_FORMAT(form_cut_input.waktu_selesai, '%Y-%m-%d') as tanggal")
            ->leftJoin("stocker_input", "stocker_input.form_cut_id", "=", "form_cut_input.id")
            ->whereRaw("stocker_input.id_qr_stocker in (" . $stockers . ")")
            ->get();

        if ($dataCheckClosing->isEmpty()) {
            $dataCheckClosing = DB::table("form_cut_piece")
                ->selectRaw("DATE_FORMAT(form_cut_piece.waktu_selesai, '%Y-%m-%d') as tanggal")
                ->leftJoin("stocker_input", "stocker_input.form_piece_id", "=", "form_cut_piece.id")
                ->whereRaw("stocker_input.id_qr_stocker in (" . $stockers . ")")
                ->get();
        }

        if ($dataCheckClosing->isEmpty()) {
            $dataCheckClosing = DB::table("form_cut_reject")
                ->selectRaw("tanggal")
                ->leftJoin("stocker_input", "stocker_input.form_reject_id", "=", "form_cut_reject.id")
                ->whereRaw("stocker_input.id_qr_stocker in (" . $stockers . ")")
                ->get();
        }

        foreach ($dataCheckClosing as $data) {
            if (checkClosingDate($data->tanggal)) {
                return [
                    "status" => 400,
                    "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                    "additional" => "Closing"
                ];
            }
        }

        if ($stockers) {
            $checkYearSequence = YearSequence::leftJoin("stocker_input", function($join) {
                $join->on("stocker_input.form_cut_id", "=", "year_sequence.form_cut_id");
                $join->on("stocker_input.form_piece_id", "=", "year_sequence.form_piece_id");
                $join->on("stocker_input.form_reject_id", "=", "year_sequence.form_reject_id");
                $join->on("stocker_input.so_det_id", "=", "year_sequence.so_det_id");
                $join->on("stocker_input.range_awal", "<=", "year_sequence.number");
                $join->on("stocker_input.range_akhir", ">=", "year_sequence.number");
            })->
            whereRaw("stocker_input.id_qr_stocker in (".$stockers.")")->
            count();
        }

        if ($checkYearSequence > 0) {
            return array(
                'status' => 400,
                'message' => 'Stocker Form memiliki data year sequence (label qr).',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        if ($stockers) {
            $stockerData = Stocker::whereRaw("id_qr_stocker IN (".$stockers.")")->get();
            $stockerIdQrs = $stockerData->pluck('id_qr_stocker')->toArray();
            $stockerIds = $stockerData->pluck('id')->toArray();

            // Log the deletion
            Log::channel('resetStockerForm')->info([
                "Deleting Data",
                "By ".(Auth::user() ? Auth::user()->id." ".Auth::user()->username : "System"),
                DB::table("stocker_input")->whereIn('id', $stockerIds)->get(),
                DB::table("dc_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_inhouse_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("secondary_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                DB::table("rack_detail_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("trolley_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                DB::table("loading_line")->whereIn('stocker_id', $stockerIds)->get()
            ]);

            $deleteStocker = Stocker::whereIn('id', $stockerIds)->delete();
            $deleteDc = DCIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryIn = SecondaryIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryInHouseIn = SecondaryInHouseIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteSecondaryInHouse = SecondaryInHouse::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
            $deleteRackDetailStocker = RackDetailStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteTrolleyStocker = TrolleyStocker::whereIn('stocker_id', $stockerIds)->delete();
            $deleteLoadingLine = LoadingLine::whereIn('stocker_id', $stockerIds)->delete();

            return array(
                'status' => 200,
                'message' => 'Stocker <br> "'.$validatedRequest['stocker_ids'].'" <br> berhasil direset.',
                'redirect' => '',
                'table' => '',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Stocker <br> "'.$validatedRequest['stocker_ids'].'" <br> gagal direset.',
            'redirect' => '',
            'table' => '',
            'additional' => [],
        );
    }

    public function resetRedundantStocker(Request $request)
    {
        ini_set('max_execution_time', 3600);

        $redundantStockers = collect(DB::select("
            select stocker_input.* from stocker_input where id_qr_stocker in (
                select id_qr_stocker from stocker_input group by id_qr_stocker having count(id) > 1
            ) order by CAST(SUBSTRING_INDEX(id_qr_stocker, '-', -1) AS UNSIGNED) asc, updated_at desc
        "));

        $currentStocker = "";
        $updatedStockerIds = [];
        foreach ($redundantStockers as $redundantStocker) {
            if ($currentStocker != $redundantStocker->id_qr_stocker) {
                $currentStocker = $redundantStocker->id_qr_stocker;
            } else {
                // Update Stocker
                $stockerCount = Stocker::lastId();

                $newIdQrStocker = "STK-".($stockerCount+1);

                // update stocker
                Stocker::where("id", $redundantStocker->id)->update([
                    "id_qr_stocker" => $newIdQrStocker,
                    "id_qr_stocker_old" => $redundantStocker->id_qr_stocker,
                ]);

                // // update dc
                // DCIn::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->
                //     update([
                //         "id_qr_stocker" => $newIdQrStocker,
                //     ]);

                // // update secondary inhouse
                // SecondaryInhouse::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->
                //     update([
                //         "id_qr_stocker" => $newIdQrStocker,
                //     ]);

                // // update secondary in
                // SecondaryIn::where("id_qr_stocker", $redundantStocker->id_qr_stocker)->
                //     update([
                //         "id_qr_stocker" => $newIdQrStocker,
                //     ]);

                array_push($updatedStockerIds, $redundantStocker->id);
            }
        }

        $dataStockers = Stocker::selectRaw("
            (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) != 0 THEN (CONCAT(stocker_input.qty_ply, (CASE WHEN (stocker_input.qty_ply_mod - stocker_input.qty_ply) > 0 THEN CONCAT('+', (stocker_input.qty_ply_mod - stocker_input.qty_ply)) ELSE (stocker_input.qty_ply_mod - stocker_input.qty_ply) END))) ELSE stocker_input.qty_ply END) bundle_qty,
            COALESCE(master_sb_ws.size, stocker_input.size) size,
            stocker_input.range_awal,
            stocker_input.range_akhir,
            stocker_input.id_qr_stocker,
            stocker_input.id_qr_stocker_old,
            marker_input.act_costing_ws,
            marker_input.buyer,
            marker_input.style,
            UPPER(TRIM(marker_input.color)) color,
            stocker_input.shade,
            stocker_input.group_stocker,
            COALESCE(stocker_input.notes) notes,
            form_cut_input.no_cut,
            master_part.nama_part part,
            master_sb_ws.dest
        ")->
        leftJoin("part_detail", "part_detail.id", "=", "stocker_input.part_detail_id")->
        leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
        leftJoin("part", "part.id", "=", "part_detail.part_id")->
        leftJoin("part_form", "part_form.part_id", "=", "part.id")->
        leftJoin("form_cut_input", "form_cut_input.id", "=", "stocker_input.form_cut_id")->
        leftJoin("marker_input", "marker_input.kode", "=", "form_cut_input.id_marker")->
        leftJoin("marker_input_detail", "marker_input_detail.marker_id", "=", "marker_input.id")->
        leftJoin("master_sb_ws", "stocker_input.so_det_id", "=", "master_sb_ws.id_so_det")->
        leftJoin("master_size_new", "master_size_new.size", "=", "master_sb_ws.size")->
        leftJoin("users", "users.id", "=", "form_cut_input.no_meja")->
        whereIn("stocker_input.id", $updatedStockerIds)->
        groupBy("form_cut_input.id", "part_detail.id", "stocker_input.size", "stocker_input.group_stocker", "stocker_input.shade", "stocker_input.ratio")->
        orderBy("stocker_input.group_stocker", "desc")->
        orderBy("stocker_input.so_det_id", "asc")->
        orderBy("stocker_input.ratio", "asc")->
        get();

        // generate pdf
        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $customPaper = array(0, 0, 300, 250);
        $pdf = PDF::loadView('stocker.stocker.pdf.print-stocker-redundant', ["dataStockers" => $dataStockers])->setPaper('A7', 'landscape');

        $fileName = 'STOCKER_REDUNDANT.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }

    public function previewImportStockerManual(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $rows = Excel::toArray([], $request->file('file'));

        $data = [];

        foreach ($rows[0] as $i => $row) {

            // skip header
            if ($i == 0) {
                continue;
            }

            $data[] = [
                'tanggal'             => $row[0] ?? '',
                'ws'                  => $row[1] ?? '',
                'color'               => $row[2] ?? '',
                'size'                => $row[3] ?? '',
                'panel'               => $row[4] ?? '',
                'panel_status'        => $row[5] ?? '',
                'part_detail'         => $row[6] ?? '',
                'main_complement'     => $row[7] ?? '',
                'complement_part'     => $row[8] ?? '',
                'tujuan'              => $row[9] ?? '',
                'proses'              => $row[10] ?? '',
                'part_status'         => $row[11] ?? '',
                'shade'               => $row[12] ?? '',
                'qty_stocker'         => $row[13] ?? '',
                'notes'               => $row[14] ?? '',
                'status'              => $row[15] ?? '',
                'tanggal_proses_dc'  => $row[16] ?? '',
                'dc_qty'              => $row[17] ?? '',
                'in_secondary_qty'   => $row[18] ?? '',
                'out_secondary_qty'  => $row[19] ?? '',
                'secondary_in_qty'   => $row[20] ?? '',
                'wip_out'             => $row[21] ?? '',
                'tanggal_wip_out'    => $row[22] ?? '',
                'loading_line_qty'   => $row[23] ?? '',
                'tanggal_loading'    => $row[24] ?? '',
                'loading_line'        => $row[25] ?? '',
                'no_bon_loading'      => $row[26] ?? '',
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }

    public function importStockerManual(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');
        $nama_file = rand() . '_' . $file->getClientOriginalName();
        $file->move('file_upload', $nama_file);

        // 1. Mulai Transaksi Database
        DB::beginTransaction();

        try {
            $importStockerManual = new ImportStockerManual;
            Excel::import($importStockerManual, public_path('/file_upload/' . $nama_file));

            // 2. Commit jika semua baris berhasil tanpa error
            DB::commit();

            return [
                "status" => 200,
                "message" => 'Data Berhasil Di Upload',
                "additional" => [
                    "batch" => $importStockerManual->batch,
                ],
            ];
        } catch (\Throwable $e) {
            // 3. Rollback SEMUA data jika ada 1 saja baris/proses yang error
            DB::rollBack();

            return [
                "status" => 400,
                "message" => 'Terjadi Kesalahan: ' . $e->getMessage(),
                "additional" => $e->getTrace(),
            ];
        }
    }

    /**
     * Ambil stocker hasil Import Stocker Manual.
     * type "stocker" : berdasarkan batch atau salah satu ID stocker di batch tersebut.
     * type "notes"   : berdasarkan notes stocker (bisa lebih dari satu batch).
     */
    private function getImportStockerManualBatch($type, $value)
    {
        $value = trim($value ?? '');
        if (!$value) {
            return null;
        }

        // Stocker inject: tanpa form cut, shade = group_stocker = uuid batch
        $stockers = Stocker::whereColumn("shade", "group_stocker")->whereNull("form_cut_id");

        if ($type == "notes") {
            $stockers->where("notes", $value);
        } else {
            $batch = $value;
            $stocker = Stocker::select("group_stocker")->where("id_qr_stocker", $value)->first();
            if ($stocker) {
                $batch = $stocker->group_stocker;
            }

            $stockers->where("group_stocker", $batch);
        }

        $stockers = $stockers->get();

        if ($stockers->isEmpty()) {
            return null;
        }

        return [
            "batch" => $stockers->pluck("group_stocker")->unique()->values(),
            "stockers" => $stockers,
        ];
    }

    public function checkUndoImportStockerManual(Request $request)
    {
        $importBatch = $this->getImportStockerManualBatch($request->type, $request->value);
        if (!$importBatch) {
            return [
                "status" => 404,
                "message" => "Data inject stocker tidak ditemukan",
            ];
        }

        $qrStockers = $importBatch["stockers"]->pluck("id_qr_stocker");
        $idStockers = $importBatch["stockers"]->pluck("id");

        return [
            "status" => 200,
            "message" => "Data inject stocker ditemukan",
            "data" => [
                "batch" => $importBatch["batch"],
                "stockers" => $importBatch["stockers"]->map(function ($stocker) {
                    return [
                        "id_qr_stocker" => $stocker->id_qr_stocker,
                        "act_costing_ws" => $stocker->act_costing_ws,
                        "color" => $stocker->color,
                        "size" => $stocker->size,
                        "panel" => $stocker->panel,
                        "qty" => $stocker->qty_ply,
                        "notes" => $stocker->notes,
                        "batch" => $stocker->group_stocker,
                        "created_at" => $stocker->created_at ? $stocker->created_at->format("Y-m-d") : null,
                        "created_by_username" => $stocker->created_by_username,
                    ];
                })->values(),
                "dc_in" => DCIn::whereIn("id_qr_stocker", $qrStockers)->count(),
                "secondary_inhouse_in" => SecondaryInhouseIn::whereIn("id_qr_stocker", $qrStockers)->count(),
                "secondary_inhouse" => SecondaryInhouse::whereIn("id_qr_stocker", $qrStockers)->count(),
                "secondary_in" => SecondaryIn::whereIn("id_qr_stocker", $qrStockers)->count(),
                "wip_out" => DB::table("wip_out_det")->whereIn("id_qr_stocker", $qrStockers)->count(),
                "loading_line" => LoadingLine::whereIn("stocker_id", $idStockers)->count(),
            ],
        ];
    }

    public function undoImportStockerManual(Request $request)
    {
        $importBatch = $this->getImportStockerManualBatch($request->type, $request->value);
        if (!$importBatch) {
            return [
                "status" => 404,
                "message" => "Data inject stocker tidak ditemukan",
            ];
        }

        $stockers = $importBatch["stockers"];
        $qrStockers = $stockers->pluck("id_qr_stocker");
        $idStockers = $stockers->pluck("id");

        // Check Closing (tanggal stocker inject mengikuti tanggal di excel)
        foreach ($stockers as $stocker) {
            if (checkClosingDate($stocker->created_at)) {
                return [
                    "status" => 400,
                    "message" => "Data tidak dapat dihapus karena periode sudah ditutup (" . $stocker->id_qr_stocker . ").",
                    "additional" => "Closing",
                ];
            }
        }

        // Stocker yang sudah masuk rak / trolley tidak boleh di-undo
        $usedRack = RackDetailStocker::whereIn("stocker_id", $qrStockers)->pluck("stocker_id");
        $usedTrolley = TrolleyStocker::whereIn("stocker_id", $idStockers)->count();
        if ($usedRack->isNotEmpty() || $usedTrolley > 0) {
            return [
                "status" => 400,
                "message" => "Stocker sudah digunakan di Rak/Trolley" . ($usedRack->isNotEmpty() ? " (" . $usedRack->unique()->implode(", ") . ")" : ""),
            ];
        }

        DB::beginTransaction();

        try {
            // Loading Line
            $loadingPlanIds = LoadingLine::whereIn("stocker_id", $idStockers)->pluck("loading_plan_id")->unique();
            $deletedLoadingLine = LoadingLine::whereIn("stocker_id", $idStockers)->delete();

            // Loading Plan yang sudah tidak punya loading line
            $deletedLoadingPlan = LoadingLinePlan::whereIn("id", $loadingPlanIds)
                ->whereNotIn("id", LoadingLine::select("loading_plan_id")->whereIn("loading_plan_id", $loadingPlanIds))
                ->delete();

            // WIP Out
            $wipOutIds = DB::table("wip_out_det")->whereIn("id_qr_stocker", $qrStockers)->pluck("id_wip_out")->unique();
            $deletedWipOutDet = DB::table("wip_out_det")->whereIn("id_qr_stocker", $qrStockers)->delete();
            $deletedWipOut = DB::table("wip_out")
                ->whereIn("id", $wipOutIds)
                ->whereNotIn("id", DB::table("wip_out_det")->select("id_wip_out")->whereIn("id_wip_out", $wipOutIds))
                ->delete();

            // Secondary & DC
            $deletedSecondaryIn = SecondaryIn::whereIn("id_qr_stocker", $qrStockers)->delete();
            $deletedSecondaryInhouse = SecondaryInhouse::whereIn("id_qr_stocker", $qrStockers)->delete();
            $deletedSecondaryInhouseIn = SecondaryInhouseIn::whereIn("id_qr_stocker", $qrStockers)->delete();
            $deletedDcIn = DCIn::whereIn("id_qr_stocker", $qrStockers)->delete();

            // Stocker
            $deletedStocker = Stocker::whereIn("id", $idStockers)->delete();

            DB::commit();

            $summary = [
                "stocker" => $deletedStocker,
                "dc_in" => $deletedDcIn,
                "secondary_inhouse_in" => $deletedSecondaryInhouseIn,
                "secondary_inhouse" => $deletedSecondaryInhouse,
                "secondary_in" => $deletedSecondaryIn,
                "wip_out_det" => $deletedWipOutDet,
                "wip_out" => $deletedWipOut,
                "loading_line" => $deletedLoadingLine,
                "loading_line_plan" => $deletedLoadingPlan,
            ];

            Log::channel("importStockerManual")->info("Undo Import Stocker Manual", [
                "type" => $request->type,
                "value" => $request->value,
                "batch" => $importBatch["batch"],
                "by" => Auth::user()->id . " " . Auth::user()->username,
                "stockers" => $qrStockers->values(),
                "deleted" => $summary,
            ]);

            return [
                "status" => 200,
                "message" => "Inject stocker berhasil di-undo (" . $deletedStocker . " stocker)",
                "additional" => $summary,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                "status" => 400,
                "message" => "Terjadi Kesalahan: " . $e->getMessage(),
            ];
        }
    }

    function rearrangeGroups(Request $request) {
        $formCut = FormCutInput::whereBetween("updated_at", [$request->date." 00:00:00", $request->date." 23:59:59"])->get();

        foreach ($formCut as $fc) {
            $formCutDetails = FormCutInputDetail::where("form_cut_id", $fc->id)->where("no_form_cut_input", $fc->no_form)->orderBy("created_at", "asc")->get();

            $currentGroup = "";
            $groupNumber = 0;
            foreach ($formCutDetails as $formCutDetail) {
                if ($currentGroup != $formCutDetail->group_roll) {
                    $currentGroup = $formCutDetail->group_roll;
                    $groupNumber += 1;
                }

                $formCutDetail->group_stocker = $groupNumber;
                $formCutDetail->save();
            }
        }

        return $formCutDetails;
    }

    function recalculateStockerTransaction(Request $request, StockerService $stockerService)
    {
        return $stockerService->recalculateStockerTransaction($request->formCutId);
    }

    function restoreStockerLog(Request $request)
    {
        $tableName = $request->table;

        // 1. Intent check
        $dump = trim($request->stocker_log);

        if (! Str::startsWith($dump, 'Illuminate\Support\Collection::__set_state')) {
            return 'Input is not a Collection dump.';
        }

        // 2. Strip the wrapper and eval ONLY the array
        // Find first "(" and last ")"
        $start = strpos($dump, '(');
        $end   = strrpos($dump, ')');

        if ($start === false || $end === false || $end <= $start) {
            return 'Malformed collection dump.';
        }

        // Extract only the array(...) part
        $arrayCode = substr($dump, $start + 1, $end - $start - 1);

        // Evaluate ONLY the array
        $data = eval('return ' . $arrayCode . ';');

        // 3. Validate structure
        if (! is_array($data) || ! isset($data['items']) || ! is_array($data['items'])) {
            return 'Parsed data does not contain items.';
        }

        // 3. Rebuild a fresh collection
        $collection = collect($data['items']);

        if (! $collection instanceof Collection) {
            return 'Eval did not return a Collection.';
        }

        // 4. Insert rows
        $inserted = 0;

        DB::transaction(function () use ($collection, $tableName, &$inserted) {
            foreach ($collection as $item) {
                // stdClass → array
                $row = (array) $item;

                // Optional: remove primary key to avoid collisions
                unset($row['id']);

                DB::table($tableName)->insert($row);
                $inserted++;
            }
        });

        return $inserted;
    }

    public function checkCancelStocker()
    {
        return view('stocker.tools.check-cancel-stocker', [
            'page' => 'dashboard-stocker',
        ]);
    }

    public function checkCancelStockerList(Request $request)
    {
        $data = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.id_qr_stocker,
                master_sb_ws.ws,
                master_sb_ws.color,
                master_sb_ws.size,
                form_cut_input.no_form,
                stocker_input.so_det_id,
                stocker_input.notes,
                stocker_input.cancel,
                stocker_input.created_at
            ")
            ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')
            ->leftJoin('master_sb_ws', 'master_sb_ws.id_so_det', '=', 'stocker_input.so_det_id')
            ->where('stocker_input.notes', 'LIKE', '%CANCEL')
            ->orderBy('stocker_input.created_at', 'desc');

        return DataTables::eloquent($data)->make(true);
    }

    public function fixCancelStocker(Request $request)
    {
        try {
            $count = Stocker::where('stocker_input.notes', 'LIKE', '%CANCEL')->count();

            Stocker::where('stocker_input.notes', 'LIKE', '%CANCEL')
                ->update(['cancel' => 'Y']);

            return [
                'status'  => 200,
                'message' => $count . ' data stocker berhasil diupdate cancel = Y.',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 400,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkMismatchPartStocker()
    {
        return view('stocker.tools.check-mismatch-part-stocker', [
            'page' => 'dashboard-stocker',
        ]);
    }

    public function checkMismatchPartStockerList(Request $request)
    {
        $data = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.id_qr_stocker,
                form_cut_input.no_form,
                stocker_input.so_det_id,
                stocker_input.part_detail_id,
                part.id part_id,
                part.panel,
                master_part.nama_part part_name,
                part.act_costing_ws part_ws,
                stocker_input.act_costing_ws stocker_ws,
                stocker_input.notes,
                stocker_input.cancel,
                stocker_input.created_at
            ")
            ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')
            ->leftJoin('part_detail', 'part_detail.id', '=', 'stocker_input.part_detail_id')
            ->leftJoin('part', 'part.id', '=', 'part_detail.part_id')
            ->leftJoin('master_part', 'master_part.id', '=', 'part_detail.master_part_id')
            ->whereNotNull('stocker_input.part_detail_id')
            ->whereRaw('(part_detail.id IS NULL OR part.id IS NULL OR part.act_costing_ws != stocker_input.act_costing_ws)')
            ->orderBy('stocker_input.created_at', 'desc');

        return DataTables::eloquent($data)->make(true);
    }

    function undoStockerAdditional(Request $request)
    {
        $validatedRequest = $request->validate([
            "id" => "required|integer|exists:form_cut_input,id"
        ]);

        // Check Closing
        $dataCheckClosing = DB::table("form_cut_input")->where("id", $validatedRequest["id"])->first();
        if ($dataCheckClosing && checkClosingDate($dataCheckClosing->waktu_selesai)) {
            return response()->json([
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            ]);
        }

        // Check Stocker with bypass parameter
        $bypass = $request->boolean('bypass');
        $isSuperAdmin = auth()->user()->roles->whereIn('nama_role', ['superadmin'])->count() > 0;
        $isAllowedBypass = $bypass && $isSuperAdmin;

        if (!$isAllowedBypass) {
            $checkStocker = Stocker::where("form_cut_id", $validatedRequest["id"])->whereRaw("notes LIKE '%ADDITIONAL%'")->first();
            if ($checkStocker) {
                return response()->json([
                    "status" => 401,
                    "message" => "Stocker Additional sudah di Print",
                ]);
            }
        }

        $stockerAdditional = StockerAdditional::where("form_cut_id", $validatedRequest["id"])->first();
        $stockerDeleteData = Stocker::where("form_cut_id", $validatedRequest["id"])
            ->whereRaw("notes LIKE '%ADDITIONAL%'")
            ->get();
        $stockerIds = $stockerDeleteData->pluck('id')->toArray();
        $stockerIdQrs = $stockerDeleteData->pluck('id_qr_stocker')->filter()->values()->toArray();

        if ($stockerDeleteData->isEmpty() && !$stockerAdditional) {
            return response()->json([
                "status" => 400,
                "message" => "Data tidak ditemukan"
            ]);
        }

        DB::beginTransaction();

        try {
            Log::channel('undoStockerAdditional')->info("Undo Stocker Additional", [
                "by" => Auth::user()
                    ? Auth::user()->id . " " . Auth::user()->username
                    : "System",
                "bypass" => $isAllowedBypass,
                "stocker_delete" => DB::table("stocker_input")->whereIn('id', $stockerIds)->get(),
                "dc_in_delete" => DB::table("dc_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                "secondary_in_delete" => DB::table("secondary_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                "secondary_inhouse_in_delete" => DB::table("secondary_inhouse_in_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                "secondary_inhouse_delete" => DB::table("secondary_inhouse_input")->whereIn('id_qr_stocker', $stockerIdQrs)->get(),
                "rack_detail_stocker_delete" => DB::table("rack_detail_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                "trolley_stocker_delete" => DB::table("trolley_stocker")->whereIn('stocker_id', $stockerIds)->get(),
                "loading_line_delete" => DB::table("loading_line")->whereIn('stocker_id', $stockerIds)->get(),
                "stocker_additional" => $stockerAdditional
                    ? DB::table("stocker_ws_additional")->where('id', $stockerAdditional->id)->get()
                    : [],
                "stocker_additional_detail" => $stockerAdditional
                    ? DB::table("stocker_ws_additional_detail")->where('stocker_additional_id', $stockerAdditional->id)->get()
                    : [],
            ]);

            if ($isAllowedBypass) {
                Stocker::whereIn('id', $stockerIds)->delete();
                DCIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
                SecondaryIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
                SecondaryInHouseIn::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
                SecondaryInHouse::whereIn('id_qr_stocker', $stockerIdQrs)->delete();
                RackDetailStocker::whereIn('stocker_id', $stockerIds)->delete();
                TrolleyStocker::whereIn('stocker_id', $stockerIds)->delete();
                LoadingLine::whereIn('stocker_id', $stockerIds)->delete();
            }

            if ($stockerAdditional) {
                StockerAdditionalDetail::where("stocker_additional_id", $stockerAdditional->id)->delete();
                StockerAdditional::where("id", $stockerAdditional->id)->delete();
            }

            DB::commit();

            return response()->json([
                "status" => 200,
                "message" => "Stocker Additional berhasil dihapus",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::channel('undoStockerAdditional')->error("Gagal undo Stocker Additional", [
                "form_cut_id" => $validatedRequest["id"],
                "error" => $e->getMessage(),
            ]);

            return response()->json([
                "status" => 400,
                "message" => "Gagal menghapus Stocker Additional: " . $e->getMessage(),
            ]);
        }
    }

    function checkMissmatchedOrderStocker(Request $request) {
        return view('stocker.tools.check-missmatched-order-stocker', [
            'page' => 'dashboard-stocker',
        ]);
    }

    function checkMissmatchedOrderStockerList(Request $request) {
        $data = Stocker::selectRaw("
                stocker_input.id,
                stocker_input.id_qr_stocker,
                form_cut_input.no_form,
                stocker_input.so_det_id,
                stocker_input.notes,
                stocker_input.cancel,
                stocker_input.created_at,
                stocker_input.act_costing_ws stocker_ws,
                stocker_input.color stocker_color,
                stocker_input.size stocker_size,
                master_sb_ws.ws actual_stocker_ws,
                master_sb_ws.color actual_stocker_color,
                master_sb_ws.size actual_stocker_size,
                marker_input.act_costing_ws marker_ws,
                marker_input.color marker_color
            ")
            ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')
            ->leftJoin('marker_input', 'marker_input.id', '=', 'form_cut_input.marker_id')
            ->leftJoin('master_sb_ws', 'master_sb_ws.id_so_det', '=', 'stocker_input.so_det_id')
            ->whereRaw("
                (
                    stocker_input.act_costing_ws != master_sb_ws.ws OR
                    stocker_input.color != master_sb_ws.color OR
                    stocker_input.so_det_id != master_sb_ws.id_so_det OR
                    marker_input.act_costing_ws != master_sb_ws.ws OR
                    marker_input.color != master_sb_ws.color
                ) AND
                (stocker_input.cancel IS NULL OR stocker_input.cancel != 'Y') AND
                (stocker_input.notes IS NULL OR stocker_input.notes NOT LIKE '%ADDITIONAL%')
            ")
            ->orderBy('stocker_input.created_at', 'desc')
            ->get();

        return DataTables::of($data)->toJson();
    }

    function fixMissmatchedOrderStocker(Request $request) {
        try {
            $missmatchedStockers = Stocker::selectRaw("
                    stocker_input.id,
                    stocker_input.id_qr_stocker,
                    form_cut_input.no_form,
                    stocker_input.so_det_id,
                    stocker_input.notes,
                    stocker_input.cancel,
                    stocker_input.created_at,
                    stocker_input.act_costing_ws stocker_ws,
                    stocker_input.color stocker_color,
                    stocker_input.size stocker_size,
                    master_sb_ws.ws actual_stocker_ws,
                    master_sb_ws.color actual_stocker_color,
                    master_sb_ws.size actual_stocker_size,
                    marker_input.act_costing_ws marker_ws,
                ")
                ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')
                ->leftJoin('marker_input', 'marker_input.id', '=', 'form_cut_input.marker_id')
                ->leftJoin('master_sb_ws', 'master_sb_ws.id_so_det', '=', 'stocker_input.so_det_id')
                ->whereRaw("
                    (
                        stocker_input.act_costing_ws != master_sb_ws.ws OR
                        stocker_input.color != master_sb_ws.color OR
                        stocker_input.size != master_sb_ws.size OR
                        marker_input.act_costing_ws != master_sb_ws.ws OR
                        marker_input.color != master_sb_ws.color
                    ) AND
                    (stocker_input.cancel IS NULL OR stocker_input.cancel != 'Y')
                ")
                ->get();

            foreach ($missmatchedStockers as $stocker) {
                $stocker->act_costing_ws = $stocker->actual_stocker_ws;
                $stocker->color = $stocker->actual_stocker_color;
                $stocker->size = $stocker->actual_stocker_size;
                $stocker->save();
            }

            return [
                'status'  => 200,
                'message' => count($missmatchedStockers) . ' data stocker berhasil diperbaiki.',
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 400,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkStokerTidakValid() {

        return view("stocker.tools.check-stocker-tidak-valid", [
            "page"    => "dashboard-stocker",
        ]);
    }

    public function checkStokerTidakValidList(Request $request)
    {
        $dateFrom = $request->date_from ?? date("Y-m-d", strtotime("-30 days"));
        $dateTo = $request->date_to ?? date("Y-m-d");

        $data = Stocker::select('stocker_input.*')
            ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')
            ->whereBetween('stocker_input.created_at', [$dateFrom . " 00:00:00", $dateTo . " 23:59:59"])
            ->whereNotNull('stocker_input.form_cut_id')
            ->whereNull('form_cut_input.id')
            ->orderBy('stocker_input.id', 'desc');

        return DataTables::eloquent($data)
            ->addIndexColumn()
            ->toJson();
    }

    public function checkStockerByFilter()
    {
        return view('stocker.tools.check-stocker-by-filter', [
            'page' => 'dashboard-stocker',
        ]);
    }

    public function checkStockerByFilterList(Request $request)
    {
        $data = Stocker::withoutGlobalScopes()
            ->selectRaw("
                MIN(stocker_input.id) as id,
                COUNT(*) as stocker_count,
                stocker_input.act_costing_ws,
                stocker_input.color,
                stocker_input.size,
                stocker_input.group_stocker,
                stocker_input.ratio,
                stocker_input.so_det_id,
                stocker_input.stocker_reject,
                form_cut_input.no_form,
                form_cut_input.no_cut,
                MIN(stocker_input.range_awal) as range_awal,
                MAX(stocker_input.range_akhir) as range_akhir,
                MIN(stocker_input.qty_ply) as qty_ply,
                SUM(stocker_input.qty_ply_mod) as qty_ply_mod,
                MAX(stocker_input.cancel) as cancel,
                MAX(stocker_input.notes) as notes,
                MIN(stocker_input.created_at) as created_at
            ")
            ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_input.form_cut_id')
            ->when($request->act_costing_ws, fn($q) => $q->where('stocker_input.act_costing_ws', $request->act_costing_ws))
            ->when($request->color, fn($q) => $q->where('stocker_input.color', $request->color))
            ->when($request->size, fn($q) => $q->where('stocker_input.size', $request->size))
            ->when($request->additional === 'Y', fn($q) => $q->where('stocker_input.notes', 'LIKE', '%ADDITIONAL%'))
            ->when($request->additional === 'N', fn($q) => $q->where(function ($q) {
                $q->whereNull('stocker_input.notes')->orWhere('stocker_input.notes', 'NOT LIKE', '%ADDITIONAL%');
            }))
            ->groupBy(
                'stocker_input.act_costing_ws',
                'stocker_input.color',
                'stocker_input.size',
                'stocker_input.group_stocker',
                'stocker_input.ratio',
                'stocker_input.so_det_id',
                'stocker_input.stocker_reject',
                'form_cut_input.no_form',
                'form_cut_input.no_cut'
            )
            ->orderByRaw('CAST(form_cut_input.no_cut AS UNSIGNED) ASC')
            ->orderBy('stocker_input.group_stocker', 'desc')
            ->orderBy('stocker_input.ratio', 'asc');

        return DataTables::eloquent($data)->make(true);
    }

    public function getNoFormModifySizeQty()
    {
        $data = ModifySizeQty::select('form_cut_id', 'no_form')
            ->groupBy('form_cut_id', 'no_form')
            ->orderBy('no_form')
            ->get();

        return response()->json($data);
    }

    public function getDataFormResetModifySizeQty(Request $request)
    {
        $data = ModifySizeQty::selectRaw("
                modify_size_qty.id,
                modify_size_qty.so_det_id,
                COALESCE((CASE WHEN master_sb_ws.dest IS NOT NULL AND master_sb_ws.dest != '-' THEN CONCAT(COALESCE(marker_input_detail.size, master_sb_ws.size), ' - ', master_sb_ws.dest) ELSE COALESCE(marker_input_detail.size, master_sb_ws.size) END), marker_input_detail.size) size,
                marker_input_detail.ratio,
                modify_size_qty.original_qty as qty_cut,
                modify_size_qty.note,
                modify_size_qty.group_stocker
            ")
            ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'modify_size_qty.form_cut_id')
            ->leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')
            ->leftJoin('marker_input_detail', function ($join) {
                $join->on('marker_input_detail.marker_id', '=', 'marker_input.id');
                $join->on('marker_input_detail.so_det_id', '=', 'modify_size_qty.so_det_id');
            })
            ->leftJoin('master_sb_ws', 'master_sb_ws.id_so_det', '=', 'marker_input_detail.so_det_id')
            ->where('modify_size_qty.form_cut_id', $request->noFormId)
            ->groupBy('modify_size_qty.id')
            ->orderBy('modify_size_qty.id', 'ASC')
            ->get();

        return response()->json($data);
    }

    public function resetModifySizeQty(Request $request, StockerService $stockerService)
    {
        $noFormId = $request->noFormId;

        // Check Closing
        $dataCheckClosing = DB::table("form_cut_input")->where("id", $noFormId)->first();
        if (checkClosingDate($dataCheckClosing->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            );
        }

        // Check Stocker
        $stockers = $stockerService->getStockerForm($noFormId);
        if ($stockers->count() > 0) {
            return array(
                "status" => 400,
                "message" => "Stocker sudah di Print",
            );
        }

        // Logging
        $data = ModifySizeQty::where('form_cut_id', $noFormId)->get();

        foreach ($data as $item) {
            logHistory(
                $item->id,
                $item->toArray()
            );
        }

        // Delete
        ModifySizeQty::where('form_cut_id', $noFormId)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Modify Size Qty berhasil di-reset.'
        ]);
    }

    public function getNoFormSeparateStocker()
    {
        $data = StockerSeparate::select('form_cut_id', 'no_form')
            ->groupBy('form_cut_id', 'no_form')
            ->orderBy('no_form')
            ->get();

        return response()->json($data);
    }

    public function getDataFormResetSeparateStocker(Request $request)
    {
        $latestStocker = StockerSeparate::selectRaw("
            MAX(id) as id
        ")
        ->where('form_cut_id', $request->noFormId)
        ->groupBy(
            'so_det_id',
            'group_roll',
            'group_stocker'
        );

        $data = StockerSeparate::selectRaw("
            stocker_separate.id,
            stocker_separate.so_det_id,
            COALESCE(
                CASE
                    WHEN master_sb_ws.dest IS NOT NULL
                        AND master_sb_ws.dest != '-'
                    THEN CONCAT(
                        COALESCE(marker_input_detail.size, master_sb_ws.size),
                        ' - ',
                        master_sb_ws.dest
                    )
                    ELSE COALESCE(marker_input_detail.size, master_sb_ws.size)
                END,
                marker_input_detail.size
            ) size,
            marker_input_detail.ratio,
            CASE
                WHEN modify_size_qty.id IS NULL THEN
                    CAST(SUM(stocker_separate_detail.qty) AS CHAR)
                WHEN modify_size_qty.modified_qty = modify_size_qty.original_qty THEN
                    CAST(modify_size_qty.modified_qty AS CHAR)
                ELSE
                    CONCAT(
                        modify_size_qty.modified_qty,
                        ' (',
                        modify_size_qty.original_qty,
                        CASE
                            WHEN modify_size_qty.difference_qty >= 0
                            THEN CONCAT('+', modify_size_qty.difference_qty)
                            ELSE modify_size_qty.difference_qty
                        END,
                        ')'
                    )
            END AS qty_cut,
            stocker_separate.group_roll,
            stocker_separate.group_stocker,
            GROUP_CONCAT(
                stocker_separate_detail.qty
                ORDER BY stocker_separate_detail.id
                SEPARATOR ' | '
            ) separate_qty
        ")
        ->joinSub($latestStocker,'latest',function($join){
            $join->on('latest.id','=','stocker_separate.id');
        })
        ->leftJoin('stocker_separate_detail', 'stocker_separate_detail.separate_id', '=', 'stocker_separate.id')
        ->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'stocker_separate.form_cut_id')
        ->leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')
        ->leftJoin('marker_input_detail',function($join){
            $join->on('marker_input_detail.marker_id', '=', 'marker_input.id');
            $join->on('marker_input_detail.so_det_id', '=', 'stocker_separate.so_det_id');
        })
        ->leftJoin('master_sb_ws', 'master_sb_ws.id_so_det', '=', 'marker_input_detail.so_det_id')
        ->leftJoin('modify_size_qty',function($join){
            $join->on('modify_size_qty.form_cut_id', '=', 'stocker_separate.form_cut_id');
            $join->on('modify_size_qty.so_det_id', '=', 'stocker_separate.so_det_id');
            $join->on('modify_size_qty.group_stocker', '=', 'stocker_separate.group_stocker');
        })
        ->where(function ($q) {
            $q->whereNull('modify_size_qty.id')
            ->orWhere('modify_size_qty.modified_qty', '>', 0);
        })
        ->groupBy(
            'stocker_separate.id',
            'stocker_separate.so_det_id',
            'master_sb_ws.dest',
            'master_sb_ws.size',
            'marker_input_detail.size',
            'marker_input_detail.ratio',
            'stocker_separate.group_roll',
            'stocker_separate.group_stocker',
            'modify_size_qty.modified_qty',
            'modify_size_qty.original_qty',
            'modify_size_qty.difference_qty'
        )
        ->orderBy('stocker_separate.group_stocker')
        ->orderBy('marker_input_detail.id')
        ->get();

        return response()->json($data);
    }

    public function resetSeparateStocker(Request $request, StockerService $stockerService)
    {
        $noFormId = $request->noFormId;

        // Check Closing
        $dataCheckClosing = DB::table("form_cut_input")->where("id", $noFormId)->first();
        if (checkClosingDate($dataCheckClosing->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Data tidak dapat disimpan karena periode sudah ditutup.",
                "additional" => "Closing"
            );
        }

        // Check Stocker
        $stockers = $stockerService->getStockerForm($noFormId);
        if ($stockers->count() > 0) {
            return array(
                "status" => 400,
                "message" => "Stocker sudah di Print",
            );
        }

        // Logging
        $data = StockerSeparate::where('form_cut_id', $noFormId)->get();
        $details = StockerSeparateDetail::whereIn('separate_id', $data->pluck('id'))->get();

        foreach ($data as $item) {
            logHistory($item->id, $item->toArray());
        }

        foreach ($details as $detail) {
            logHistory($detail->id, $detail->toArray());
        }

        // Delete
        StockerSeparateDetail::whereIn('separate_id', $data->pluck('id'))->delete();
        StockerSeparate::where('form_cut_id', $noFormId)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Separate Stocker berhasil di-reset.'
        ]);
    }
}
