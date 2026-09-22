<?php

namespace App\Http\Controllers\Cutting;

use App\Exports\Cutting\PenerimaanCuttingExport;
use App\Http\Controllers\Controller;
use App\Models\Cutting\PenerimaanCutting;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CuttingService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class PenerimaanCuttingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;

            $data = DB::table('penerimaan_cutting')
                ->selectRaw("
                    penerimaan_cutting.id,
                    DATE_FORMAT(penerimaan_cutting.tanggal_terima, '%d/%m/%Y') as tanggal_terima,
                    penerimaan_cutting.id_roll AS barcode,
                    penerimaan_cutting.created_by_username,
                    DATE_FORMAT(penerimaan_cutting.created_at, '%d/%m/%Y %H:%i:%s') as created_at_format,
                    whs_bppb_h.no_req,
                    whs_bppb_det.no_bppb,
                    whs_bppb_h.tgl_bppb AS tanggal_bppb,
                    whs_bppb_h.tujuan,
                    whs_bppb_h.no_ws,
                    whs_bppb_h.no_ws_aktual AS no_ws_act,
                    whs_bppb_det.qty_out,
                    whs_bppb_det.satuan AS unit,
                    penerimaan_cutting.qty_konv,
                    penerimaan_cutting.unit_konv,
                    whs_bppb_det.no_lot,
                    whs_bppb_det.no_roll,
                    whs_bppb_det.no_roll_buyer,
                    whs_bppb_det.id_item,
                    whs_bppb_det.item_desc AS nama_barang,
                    buyer_ws.styleno AS style,
                    masteritem.color AS warna
                ")
                ->leftJoin('signalbit_erp.whs_bppb_det', 'signalbit_erp.whs_bppb_det.id', '=', 'penerimaan_cutting.whs_bppb_det_id')
                ->leftJoin('signalbit_erp.whs_bppb_h', 'signalbit_erp.whs_bppb_h.no_bppb', '=', 'signalbit_erp.whs_bppb_det.no_bppb')
                ->leftJoin('signalbit_erp.masteritem', 'signalbit_erp.masteritem.id_item', '=', 'signalbit_erp.whs_bppb_det.id_item')
                ->leftJoinSub(
                    DB::table('signalbit_erp.act_costing as ac')
                        ->selectRaw('jod.id_jo, ac.kpno AS no_ws, ac.styleno')
                        ->join('signalbit_erp.so as so', 'ac.id', '=', 'so.id_cost')
                        ->join('signalbit_erp.jo_det as jod', 'so.id', '=', 'jod.id_so')
                        ->groupBy('jod.id_jo', 'ac.kpno', 'ac.styleno'),
                    'buyer_ws',
                    function ($join) {
                        $join->on('buyer_ws.id_jo', '=', 'signalbit_erp.whs_bppb_det.id_jo');
                    }
                );

            if ($tglAwal) {
                $data->where('penerimaan_cutting.tanggal_terima', '>=', $tglAwal);
            }

            if ($tglAkhir) {
                $data->where('penerimaan_cutting.tanggal_terima', '<=', $tglAkhir);
            }

            return DataTables::query($data)
                ->filterColumn('tanggal_terima', function ($query, $keyword) {
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_cutting.tanggal_terima, '%d/%m/%Y') LIKE ?
                    ", ["%{$keyword}%"]);
                })
                ->filterColumn('barcode', function ($query, $keyword) {
                    $query->whereRaw("penerimaan_cutting.id_roll LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('qty_out', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.qty_out LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('unit', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.satuan LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_req', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_h.no_req LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_bppb', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.no_bppb LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('tanggal_bppb', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_h.tgl_bppb LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('tujuan', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_h.tujuan LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_ws', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_h.no_ws LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_ws_act', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_h.no_ws_aktual LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('id_item', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.id_item LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('style', function ($query, $keyword) {
                    $query->whereRaw("buyer_ws.styleno LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('warna', function ($query, $keyword) {
                    $query->whereRaw("masteritem.color LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_lot', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.no_lot LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_roll', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.no_roll LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('no_roll_buyer', function ($query, $keyword) {
                    $query->whereRaw("whs_bppb_det.no_roll_buyer LIKE ?", ["%{$keyword}%"]);
                })
                ->filterColumn('created_at_format', function ($query, $keyword) {
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_cutting.created_at, '%d/%m/%Y %H:%i:%s') LIKE ?
                    ", ["%{$keyword}%"]);
                })
                ->order(function ($query) {
                    $query->orderBy('penerimaan_cutting.created_at', 'desc');
                })
                ->toJson();
        }

        return view('cutting.penerimaan-cutting.penerimaan-cutting', ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "penerimaan-cutting"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('cutting.penerimaan-cutting.create-penerimaan-cutting', ['orders' => $orders, 'page' => 'dashboard-cutting', "subPageGroup" => "proses-cutting", "subPage" => "penerimaan-cutting"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $items = json_decode($request->items, true);

        $user = Auth::user();
        $now = Carbon::now();

        foreach ($items as $item) {
            $penerimaanCutting = PenerimaanCutting::create([
                'tanggal_terima'        => date('Y-m-d'),
                'whs_bppb_det_id'       => $item['whs_bppb_det_id'],
                'id_roll'               => $item['barcode'],
                'qty_konv'              => $item['qty_konv'],
                'unit_konv'             => $item['unit_konv'],
                "created_by"            => $user ? $user->id : null,
                "created_by_username"   => $user ? $user->username : null,
                "created_at"            => $now,
            ]);
        }

        if ($penerimaanCutting) {

            return array(
                "status" => 200,
                "message" => "Data Penerimaan Cutting berhasil disimpan.",
                "additional" => [],
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi Kesalahan",
            "additional" => [],
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
        $data = DB::connection("mysql_sb")
            ->table('laravel_nds.penerimaan_cutting')
            ->select(
                'penerimaan_cutting.id',
                'penerimaan_cutting.tanggal_terima',
                'penerimaan_cutting.id_roll AS barcode',
                'penerimaan_cutting.whs_bppb_det_id',
                'penerimaan_cutting.qty_konv',
                'penerimaan_cutting.unit_konv',
                'whs_bppb_h.tgl_bppb',
                'whs_bppb_h.no_req',
                'whs_bppb_det.no_bppb',
                'whs_bppb_h.tgl_bppb AS tanggal_bppb',
                'whs_bppb_h.tujuan',
                'whs_bppb_h.no_ws',
                'whs_bppb_h.no_ws_aktual AS no_ws_act',
                'whs_bppb_det.qty_out',
                'whs_bppb_det.satuan AS unit',
                'whs_bppb_det.no_lot',
                'whs_bppb_det.no_roll',
                'whs_bppb_det.no_roll_buyer',
                'whs_bppb_det.id_item',
                'whs_bppb_det.item_desc AS nama_barang',
                'buyer_ws.styleno AS style',
                'masteritem.color AS warna'
            )
            // Join ke whs_bppb_det
            ->leftJoin('whs_bppb_det', 'penerimaan_cutting.whs_bppb_det_id', '=', 'whs_bppb_det.id')
            ->leftJoin('whs_bppb_h', 'whs_bppb_h.no_bppb', '=', 'whs_bppb_det.no_bppb')
            ->leftJoin('masteritem', 'masteritem.id_item', '=', 'whs_bppb_det.id_item')
            ->leftJoinSub(
                DB::table('signalbit_erp.act_costing as ac')
                    ->selectRaw('jod.id_jo, ac.kpno AS no_ws, ac.styleno')
                    ->join('signalbit_erp.so as so', 'ac.id', '=', 'so.id_cost')
                    ->join('signalbit_erp.jo_det as jod', 'so.id', '=', 'jod.id_so')
                    ->groupBy('jod.id_jo', 'ac.kpno', 'ac.styleno'),
                'buyer_ws',
                function ($join) {
                    // DIPERBAIKI: Hapus prefix 'signalbit_erp.' agar tidak error SQL
                    $join->on('buyer_ws.id_jo', '=', 'whs_bppb_det.id_jo');
                }
            )
            // DIPERBAIKI: Mengambil data berdasarkan penerimaan_cutting.id atau id_roll yang di-edit
            ->where(function ($query) use ($id) {
                $query->where('penerimaan_cutting.id', $id)
                    ->orWhere('penerimaan_cutting.id_roll', $id);
            })
            // DIPERBAIKI: whereNull('penerimaan_cutting.id') DIHAPUS karena ini mode edit data yang sudah ada
            ->orderBy('penerimaan_cutting.id', 'DESC')
            ->first();

        return view("cutting.penerimaan-cutting.edit-penerimaan-cutting", ["data" => $data]);
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
        // Cutting service
        $cuttingService = new CuttingService();

        // 1. Validasi Request Data menggunakan Validator::make
        $validator = Validator::make($request->all(), [
            'id'         => 'required',
            'tgl_terima' => 'required|date',
        ], [
            'id.required'         => 'ID Penerimaan tidak ditemukan.',
            'tgl_terima.required' => 'Tanggal terima wajib diisi.',
            'tgl_terima.date'     => 'Format tanggal terima tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 400,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 400);
        }

        $user = Auth::user();
        $now  = Carbon::now();
        $tglTerimaBaru = $request->tgl_terima;

        // 2. Cari Data Penerimaan Cutting
        $penerimaanCutting = PenerimaanCutting::where('id', $request->id)
            ->orWhere('whs_bppb_det_id', $request->whs_bppb_det_id)
            ->first();

        if (!$penerimaanCutting) {
            return response()->json([
                'status'  => 404,
                'message' => 'Data Penerimaan Fabric Cutting tidak ditemukan.'
            ], 404);
        }

        // Cek jika sudah ada pemakaian (menggunakan id_roll dari database/request)
        $idRoll = $request->id_roll ?? $penerimaanCutting->id_roll;
        $firstUsage = $cuttingService->isRollUsed($idRoll, true);
        if ($firstUsage && $tglTerimaBaru > $firstUsage) {

            return response()->json([
                'status'     => 400,
                'message'    => 'Roll tidak dapat diubah ke tanggal setelah ada pemakaian.',
                'table'      => 'datatable',
                'additional' => [],
            ], 400);
        }

        // 3. Cek perubahan & susun catatan perubahan (notes)
        $tglTerimaLama = $penerimaanCutting->tanggal_terima;
        $message = "Edit Penerimaan";

        if ($tglTerimaLama != $tglTerimaBaru) {
            $message .= " Dari " . $tglTerimaLama . " ke " . $tglTerimaBaru;
        }

        // 4. Update Data Model
        $penerimaanCutting->tanggal_terima = $tglTerimaBaru;
        $penerimaanCutting->created_at     = $tglTerimaBaru." 00:00:00";
        $penerimaanCutting->edited_notes   = $message;
        $penerimaanCutting->edited_by      = $user->id;
        $penerimaanCutting->edited_at      = $now;

        // Simpan Perubahan ke Database
        if ($penerimaanCutting->save()) {
            return response()->json([
                'status'     => 200,
                'message'    => 'Data Penerimaan Fabric Cutting berhasil diperbarui.',
                'table'      => 'datatable',
                'data'       => $penerimaanCutting
            ], 200);
        }

        return response()->json([
            'status'  => 500,
            'message' => 'Gagal memperbarui data penerimaan.'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        $data = PenerimaanCutting::find($id);

        $cuttingService = new CuttingService();
        if ($cuttingService->isRollUsed($data->id_roll)) {
            return [
                "status" => 400,
                "message" => "Roll sudah digunakan.",
                "table" => "datatable",
                "additional" => [],
            ];
        }

        if ($data) {
            $data->delete();

            return [
                "status" => 200,
                "message" => "Data berhasil dihapus.",
                "table" => "datatable",
                "additional" => [],
            ];
        }

        return [
            "status" => 400,
            "message" => "Data tidak ditemukan.",
            "additional" => [],
        ];
    }

    public function getBarcodeFabric($id){

        // $isExist = DB::table('penerimaan_cutting')
        //     ->where('id_roll', $id)
        //     ->exists();

        // if ($isExist) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Roll sudah pernah diterima!'
        //     ]);
        // }

        $data = DB::connection("mysql_sb")
            ->table('whs_bppb_det')
            ->select(
                'whs_bppb_h.tgl_bppb',
                'whs_bppb_det.id',
                'whs_bppb_det.id_roll AS barcode',
                'whs_bppb_h.no_req',
                'whs_bppb_det.no_bppb',
                'whs_bppb_h.tgl_bppb AS tanggal_bppb',
                'whs_bppb_h.tujuan',
                'whs_bppb_h.no_ws',
                'whs_bppb_h.no_ws_aktual AS no_ws_act',
                'whs_bppb_det.qty_out',
                'whs_bppb_det.satuan AS unit',
                'whs_bppb_det.no_lot',
                'whs_bppb_det.no_roll',
                'whs_bppb_det.no_roll_buyer',
                'whs_bppb_det.id_item',
                'whs_bppb_det.item_desc AS nama_barang',
                'buyer_ws.styleno AS style',
                'masteritem.color AS warna'
            )
            ->leftJoin('whs_bppb_h', 'whs_bppb_h.no_bppb', '=' ,'whs_bppb_det.no_bppb')
            ->leftJoin('masteritem', 'masteritem.id_item', 'whs_bppb_det.id_item')
            ->leftJoinSub(
                DB::table('signalbit_erp.act_costing as ac')
                    ->selectRaw('jod.id_jo, ac.kpno AS no_ws, ac.styleno')
                    ->join('signalbit_erp.so as so', 'ac.id', '=', 'so.id_cost')
                    ->join('signalbit_erp.jo_det as jod', 'so.id', '=', 'jod.id_so')
                    ->groupBy('jod.id_jo', 'ac.kpno', 'ac.styleno'),
                'buyer_ws',
                function ($join) {
                    $join->on('buyer_ws.id_jo', '=', 'signalbit_erp.whs_bppb_det.id_jo');
                }
            )
            ->leftJoin('laravel_nds.penerimaan_cutting', 'penerimaan_cutting.whs_bppb_det_id', '=', 'whs_bppb_det.id')
            ->where('whs_bppb_det.id_roll', $id)
            ->where('whs_bppb_det.no_bppb', 'NOT LIKE', 'MT/%')
            ->where("whs_bppb_h.tujuan", 'Production - Cutting')
            ->whereNull('penerimaan_cutting.id')
            ->orderBy('whs_bppb_det.id', 'DESC')
            ->first();

        // When data is nowhere to be found
        if (!$data) {
            $isExist = DB::table('penerimaan_cutting')
            ->where('id_roll', $id)
            ->exists();

            if ($isExist) {
                return response()->json([
                    'status' => false,
                    'message' => 'Roll sudah pernah diterima'
                ]);
            }
        }

        // When tgl_bppb > current_date
        if ($data && $data->tgl_bppb > date("Y-m-d")) {
            return response()->json([
                'status' => false,
                'message' => 'Tanggal bppb melebihi tanggal hari ini'
            ]);
        }

        return $data;
    }

    public function exportPenerimaanCutting(Request $request) {
        $from = $request->from;
        $to = $request->to;

        return Excel::download(new PenerimaanCuttingExport($from, $to), 'penerimaan-cutting.xlsx');
    }
}
