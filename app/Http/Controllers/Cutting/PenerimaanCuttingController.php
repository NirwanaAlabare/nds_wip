<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\PenerimaanCutting;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CuttingService;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
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

            if ($request->bppbFrom) {
                $data->where('whs_bppb_h.tgl_bppb', '>=', $request->bppbFrom);
            }

            if ($request->bppbTo) {
                $data->where('whs_bppb_h.tgl_bppb', '<=', $request->bppbTo);
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

        return view('cutting.penerimaan-cutting.penerimaan-cutting', ["page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "penerimaan-cutting", "containerFluid" => true]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('cutting.penerimaan-cutting.create-penerimaan-cutting', ['orders' => $orders, 'page' => 'dashboard-cutting', "subPageGroup" => "proses-cutting", "subPage" => "penerimaan-cutting", "containerFluid" => true]);
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

        return view("cutting.penerimaan-cutting.edit-penerimaan-cutting", ["data" => $data, "page" => "dashboard-cutting", "subPageGroup" => "proses-cutting", "subPage" => "penerimaan-cutting", "containerFluid" => true]);
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
        // $firstUsage = $cuttingService->isRollUsed($idRoll, true);
        // if (checkClosingDate($firstUsage)) {

        //     return response()->json([
        //         'status'     => 400,
        //         'message'    => 'Periode pemakaian sudah closing.',
        //         'table'      => 'datatable',
        //         'additional' => [],
        //     ], 400);
        // }

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
            // Langsung calculate jika belum ada yang sudah close
            $firstUsage = $cuttingService->isRollUsed($idRoll, true);
            $checkClosing = checkClosingDate($firstUsage);
            if (!$checkClosing) {
                $cuttingService->fixChainedQty($penerimaanCutting->id_roll, null);
            }

            return response()->json([
                'status'     => 200,
                'message'    => 'Data Penerimaan Fabric Cutting berhasil diperbarui.'.($checkClosing ? "<br>Ada pemakaian yang masuk ke periode yang sudah ditutup." : ""),
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

    /**
     * Berapa bulan ke belakang roll pending ikut ditampilkan sebagai "bulan sebelumnya".
     */
    private const CARRY_MONTHS = 3;

    /**
     * Pengeluaran gudang ke Cutting hanya diambil mulai tanggal BPPB ini.
     */
    private const MIN_TGL_BPPB = '2026-10-01';

    /**
     * Konversi qty ke satuan konversi (YRD/YARD -> METER).
     */
    private function konversiQty($qty, $unit)
    {
        $unit = strtoupper(trim((string) $unit));

        if ($unit == 'YRD' || $unit == 'YARD') {
            return [round(((float) $qty) * 0.9144, 2), 'METER'];
        }

        return [(float) $qty, $unit];
    }

    /**
     * Query dasar pengeluaran gudang ke Cutting (whs_bppb_det + status penerimaan).
     */
    private function pengeluaranCuttingQuery()
    {
        return DB::connection("mysql_sb")
            ->table('whs_bppb_det')
            ->selectRaw("
                whs_bppb_det.id,
                whs_bppb_det.id_roll AS barcode,
                whs_bppb_det.no_bppb,
                whs_bppb_det.id_jo,
                whs_bppb_h.tgl_bppb,
                whs_bppb_h.no_req,
                whs_bppb_h.tujuan,
                whs_bppb_h.no_ws,
                whs_bppb_h.no_ws_aktual AS no_ws_act,
                whs_bppb_det.qty_out,
                whs_bppb_det.satuan AS unit,
                whs_bppb_det.no_lot,
                whs_bppb_det.no_roll,
                whs_bppb_det.no_roll_buyer,
                whs_bppb_det.id_item,
                masteritem.color AS warna,
                pc.id AS terima_id,
                pc.tanggal_terima,
                pc.created_at AS terima_at,
                pc.created_by_username AS terima_by,
                pc.qty_konv AS terima_qty_konv,
                pc.unit_konv AS terima_unit_konv
            ")
            ->leftJoin('whs_bppb_h', 'whs_bppb_h.no_bppb', '=', 'whs_bppb_det.no_bppb')
            ->leftJoin('masteritem', 'masteritem.id_item', '=', 'whs_bppb_det.id_item')
            ->leftJoin('laravel_nds.penerimaan_cutting as pc', 'pc.whs_bppb_det_id', '=', 'whs_bppb_det.id')
            ->where('whs_bppb_det.no_bppb', 'NOT LIKE', 'MT/%')
            ->where('whs_bppb_h.tujuan', 'Production - Cutting')
            ->where('whs_bppb_h.tgl_bppb', '>=', self::MIN_TGL_BPPB)
            ->whereRaw("COALESCE(whs_bppb_det.status, 'Y') <> 'N'");
    }

    /**
     * Format ringkas satu baris pengeluaran untuk tampilan scan.
     */
    private function formatPengeluaran($row)
    {
        [$qtyKonv, $unitKonv] = $this->konversiQty($row->qty_out, $row->unit);

        return [
            'id'            => $row->id,
            'bc'            => $row->barcode,
            'bppb'          => $row->no_bppb,
            'tgl'           => $row->tgl_bppb ? substr($row->tgl_bppb, 0, 10) : null,
            'req'           => $row->no_req,
            'ws'            => $row->no_ws,
            'wsAct'         => $row->no_ws_act,
            'roll'          => $row->no_roll,
            'rollBuyer'     => $row->no_roll_buyer,
            'lot'           => $row->no_lot,
            'item'          => $row->id_item,
            'warna'         => $row->warna,
            'qty'           => (float) $row->qty_out,
            'unit'          => $row->unit,
            'qtyK'          => $row->terima_id ? (float) $row->terima_qty_konv : $qtyKonv,
            'unitK'         => $row->terima_id ? $row->terima_unit_konv : $unitKonv,
            'rcv'           => $row->terima_id ? [
                'tgl' => substr((string) $row->tanggal_terima, 0, 10),
                'jam' => $row->terima_at ? Carbon::parse($row->terima_at)->format('H:i') : null,
                'by'  => $row->terima_by ?? '-',
            ] : null,
        ];
    }

    /**
     * Semua pengeluaran ke Cutting untuk satu barcode, beserta status penerimaannya.
     */
    public function scanBarcode($id = null)
    {
        $id = strtoupper(trim((string) $id));

        if (!$id) {
            return response()->json(['status' => false, 'message' => 'Barcode kosong.', 'rows' => []]);
        }

        $rows = $this->pengeluaranCuttingQuery()
            ->addSelect('whs_bppb_det.item_desc AS nama_barang')
            ->where('whs_bppb_det.id_roll', $id)
            ->orderBy('whs_bppb_h.tgl_bppb')
            ->orderBy('whs_bppb_det.id')
            ->get();

        // Style per id_jo (query kecil, hanya untuk jo milik barcode ini)
        $styles = collect();
        $idJos = $rows->pluck('id_jo')->filter()->unique()->values();
        if ($idJos->count() > 0) {
            $styles = DB::connection("mysql_sb")
                ->table('act_costing as ac')
                ->selectRaw('jod.id_jo, MAX(ac.styleno) AS styleno')
                ->join('so', 'ac.id', '=', 'so.id_cost')
                ->join('jo_det as jod', 'so.id', '=', 'jod.id_so')
                ->whereIn('jod.id_jo', $idJos)
                ->groupBy('jod.id_jo')
                ->pluck('styleno', 'id_jo');
        }

        $today = date('Y-m-d');
        $result = $rows->map(function ($row) use ($styles, $today) {
            $data = $this->formatPengeluaran($row);
            $data['style'] = $styles[$row->id_jo] ?? null;
            $data['nama'] = $row->nama_barang;
            $data['future'] = $row->tgl_bppb > $today;

            return $data;
        })->values();

        // Data lama: penerimaan tercatat dengan id_roll tapi tanpa relasi pengeluaran
        $legacyReceived = $result->count() < 1 && DB::table('penerimaan_cutting')->where('id_roll', $id)->exists();

        return response()->json([
            'status' => true,
            'barcode' => $id,
            'legacy_received' => $legacyReceived,
            'rows' => $result,
        ]);
    }

    /**
     * Simpan penerimaan satu roll langsung saat di-scan.
     */
    public function storeScan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'whs_bppb_det_id' => 'required|integer',
        ], [
            'whs_bppb_det_id.required' => 'Data pengeluaran tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $row = $this->pengeluaranCuttingQuery()
            ->addSelect('whs_bppb_det.item_desc AS nama_barang')
            ->where('whs_bppb_det.id', $request->whs_bppb_det_id)
            ->first();

        if (!$row) {
            return response()->json(['status' => 404, 'message' => 'Data pengeluaran ke Cutting tidak ditemukan.'], 404);
        }

        if ($row->tgl_bppb > date('Y-m-d')) {
            return response()->json(['status' => 400, 'message' => 'Tanggal BPPB melebihi tanggal hari ini.'], 400);
        }

        $user = Auth::user();
        $now = Carbon::now();
        [$qtyKonv, $unitKonv] = $this->konversiQty($row->qty_out, $row->unit);

        $saved = DB::transaction(function () use ($row, $user, $now, $qtyKonv, $unitKonv) {
            $exists = PenerimaanCutting::where('whs_bppb_det_id', $row->id)->lockForUpdate()->exists();

            if ($exists) {
                return null;
            }

            return PenerimaanCutting::create([
                'tanggal_terima'        => $now->format('Y-m-d'),
                'whs_bppb_det_id'       => $row->id,
                'id_roll'               => $row->barcode,
                'qty_konv'              => $qtyKonv,
                'unit_konv'             => $unitKonv,
                'created_by'            => $user ? $user->id : null,
                'created_by_username'   => $user ? $user->username : null,
                'created_at'            => $now,
            ]);
        });

        if (!$saved) {
            return response()->json(['status' => 409, 'message' => 'Kiriman ini sudah diterima sebelumnya.'], 409);
        }

        $row->terima_id = $saved->id;
        $row->tanggal_terima = $saved->tanggal_terima;
        $row->terima_at = $now;
        $row->terima_by = $saved->created_by_username;
        $row->terima_qty_konv = $qtyKonv;
        $row->terima_unit_konv = $unitKonv;

        $data = $this->formatPengeluaran($row);
        $data['nama'] = $row->nama_barang;

        return response()->json([
            'status' => 200,
            'message' => 'Penerimaan tersimpan.',
            'data' => $data,
        ]);
    }

    /**
     * Ringkasan penerimaan per bulan BPPB: semua pengeluaran bulan terpilih
     * + roll pending dari beberapa bulan sebelumnya + penerimaan hari ini.
     */
    public function summary(Request $request)
    {
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->month) ? $request->month : date('Y-m');

        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();
        $carryStart = $start->copy()->subMonthsNoOverflow(self::CARRY_MONTHS);

        $rows = $this->pengeluaranCuttingQuery()
            ->whereBetween('whs_bppb_h.tgl_bppb', [$carryStart->format('Y-m-d'), $end->format('Y-m-d')])
            ->where(function ($query) use ($start) {
                $query->where('whs_bppb_h.tgl_bppb', '>=', $start->format('Y-m-d'))
                    ->orWhereNull('pc.id');
            })
            ->orderBy('whs_bppb_h.tgl_bppb')
            ->orderBy('whs_bppb_det.id')
            ->get()
            ->map(fn ($row) => $this->formatPengeluaran($row))
            ->values();

        // Total roll keluar per tanggal untuk bulan-bulan sebelumnya (baris yang sudah diterima tidak ikut di $rows)
        $carryDateTotals = DB::connection("mysql_sb")
            ->table('whs_bppb_det')
            ->selectRaw('whs_bppb_h.tgl_bppb AS tgl, COUNT(*) AS total')
            ->leftJoin('whs_bppb_h', 'whs_bppb_h.no_bppb', '=', 'whs_bppb_det.no_bppb')
            ->where('whs_bppb_det.no_bppb', 'NOT LIKE', 'MT/%')
            ->where('whs_bppb_h.tujuan', 'Production - Cutting')
            ->where('whs_bppb_h.tgl_bppb', '>=', self::MIN_TGL_BPPB)
            ->whereRaw("COALESCE(whs_bppb_det.status, 'Y') <> 'N'")
            ->where('whs_bppb_h.tgl_bppb', '>=', $carryStart->format('Y-m-d'))
            ->where('whs_bppb_h.tgl_bppb', '<', $start->format('Y-m-d'))
            ->groupBy('whs_bppb_h.tgl_bppb')
            ->pluck('total', 'tgl');

        $today = date('Y-m-d');
        $todayRows = DB::connection("mysql_sb")
            ->table('laravel_nds.penerimaan_cutting as pc')
            ->selectRaw("
                pc.id,
                pc.id_roll AS barcode,
                pc.qty_konv,
                pc.unit_konv,
                pc.created_at,
                pc.created_by_username,
                masteritem.color AS warna
            ")
            ->leftJoin('whs_bppb_det', 'whs_bppb_det.id', '=', 'pc.whs_bppb_det_id')
            ->leftJoin('masteritem', 'masteritem.id_item', '=', 'whs_bppb_det.id_item')
            ->whereBetween('pc.tanggal_terima', [$today . ' 00:00:00', $today . ' 23:59:59'])
            ->orderBy('pc.created_at')
            ->orderBy('pc.id')
            ->get()
            ->map(fn ($r) => [
                'id'    => $r->id,
                'bc'    => $r->barcode,
                'warna' => $r->warna,
                'qtyK'  => (float) $r->qty_konv,
                'unitK' => $r->unit_konv,
                'jam'   => $r->created_at ? Carbon::parse($r->created_at)->format('H:i') : null,
                'by'    => $r->created_by_username ?? '-',
            ])
            ->values();

        return response()->json([
            'status' => true,
            'month' => $month,
            'carry_from' => $carryStart->format('Y-m'),
            'today' => $today,
            'rows' => $rows,
            'carry_date_totals' => $carryDateTotals,
            'today_rows' => $todayRows,
        ]);
    }

    public function exportPenerimaanCutting(Request $request) {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '3600');

        $from = $request->from ? $request->from : date('Y-m-d');
        $to = $request->to ? $request->to : date('Y-m-d');

        $data = PenerimaanCutting::selectRaw("
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
            )
            ->when($request->bppbFrom, function ($query, $bppbFrom) {
                $query->where('whs_bppb_h.tgl_bppb', '>=', $bppbFrom);
            })
            ->when($request->bppbTo, function ($query, $bppbTo) {
                $query->where('whs_bppb_h.tgl_bppb', '<=', $bppbTo);
            })
            ->whereBetween('penerimaan_cutting.tanggal_terima', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ])->get();

        // Create Excel file using FastExcel
        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Penerimaan Fabric Cutting', ['font-size' => 16]);
        $sheet->mergeCells('A1:T1');
        $sheet->writeTo('A2', $from . ' / ' . $to);
        $sheet->mergeCells('A2:D2');

        // Headers
        $headers = [
            'Tanggal Terima', 'Barcode', 'Qty Out', 'Unit', 'Qty Konv', 'Unit Konv', 'No. Req', 'No. BPPB', 'Tgl. BPPB', 'Tujuan',
            'No. WS', 'No. WS Act', 'ID Item', 'Style', 'Warna', 'No. Lot', 'No. Roll', 'No. Roll Buyer', 'User', 'Detail Tgl Terima',
        ];

        $sheet->writeRow(['']);
        $sheet->writeRow($headers)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($data)->chunk(1000)->each(function ($rows) use ($sheet) {
            foreach ($rows as $row) {
                $rowArr = [
                    $row->tanggal_terima ?? "-",
                    $row->barcode ?? "-",
                    (float) ($row->qty_out ?? 0),
                    $row->unit ?? "-",
                    (float) ($row->qty_konv ?? 0),
                    $row->unit_konv ?? "-",
                    $row->no_req ?? "-",
                    $row->no_bppb ?? "-",
                    $row->tanggal_bppb ?? "-",
                    $row->tujuan ?? "-",
                    $row->no_ws ?? "-",
                    $row->no_ws_act ?? "-",
                    $row->id_item ?? "-",
                    $row->style ?? "-",
                    $row->warna ?? "-",
                    $row->no_lot ?? "-",
                    $row->no_roll ?? "-",
                    $row->no_roll_buyer ?? "-",
                    $row->created_by_username ?? "-",
                    $row->created_at_format ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        foreach (range('A', 'T') as $col) {
            $sheet->setColWidth($col, 18);
        }

        $filename = 'Penerimaan Fabric Cutting ' . $from . ' - ' . $to . '.xlsx';

        return $excel->download($filename);
    }
}
