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
use Yajra\DataTables\Facades\DataTables;

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
                whs_bppb_h.style_aktual AS style,
                masteritem.color AS warna
            ")
            ->leftJoin('signalbit_erp.whs_bppb_det', 'signalbit_erp.whs_bppb_det.id', '=', 'penerimaan_cutting.whs_bppb_det_id')
            ->leftJoin('signalbit_erp.whs_bppb_h', 'signalbit_erp.whs_bppb_h.no_bppb', '=', 'signalbit_erp.whs_bppb_det.no_bppb')
            ->leftJoin('signalbit_erp.masteritem', 'signalbit_erp.masteritem.id_item', '=', 'signalbit_erp.whs_bppb_det.id_item');

            return DataTables::eloquent($data)->filter(function ($query) {
                    $tglAwal = request('dateFrom');
                    $tglAkhir = request('dateTo');

                    if ($tglAwal) {
                        $query->whereRaw("penerimaan_cutting.tanggal_terima >= '" . $tglAwal . "'");
                    }

                    if ($tglAkhir) {
                        $query->whereRaw("penerimaan_cutting.tanggal_terima <= '" . $tglAkhir . "'");
                    }
                }, true)->
                filterColumn('tanggal_terima', function($query, $keyword) {
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_cutting.tanggal_terima, '%d/%m/%Y') LIKE ?
                    ", ["%{$keyword}%"]);
                })->
                filterColumn('barcode', function($query, $keyword) {
                    $sql = "penerimaan_cutting.id_roll like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('qty_out', function($query, $keyword) {
                    $sql = "whs_bppb_det.qty_out like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('unit', function($query, $keyword) {
                    $sql = "whs_bppb_det.satuan like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_req', function($query, $keyword) {
                    $sql = "whs_bppb_h.no_req like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_bppb', function($query, $keyword) {
                    $sql = "whs_bppb_det.no_bppb like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('tanggal_bppb', function($query, $keyword) {
                    $sql = "whs_bppb_h.tgl_bppb like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('tujuan', function($query, $keyword) {
                    $sql = "whs_bppb_h.tujuan like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_ws', function($query, $keyword) {
                    $sql = "whs_bppb_h.no_ws like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_ws_act', function($query, $keyword) {
                    $sql = "whs_bppb_h.no_ws_aktual like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('id_item', function($query, $keyword) {
                    $sql = "whs_bppb_det.id_item like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('style', function($query, $keyword) {
                    $sql = "whs_bppb_h.style_aktual like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('warna', function($query, $keyword) {
                    $sql = "masteritem.color like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_lot', function($query, $keyword) {
                    $sql = "whs_bppb_det.no_lot like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_roll', function($query, $keyword) {
                    $sql = "whs_bppb_det.no_roll like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('no_roll_buyer', function($query, $keyword) {
                    $sql = "whs_bppb_det.no_roll_buyer like ?";
                    $query->whereRaw($sql, ["%{$keyword}%"]);
                })->
                filterColumn('created_at_format', function($query, $keyword) {
                    $query->whereRaw("
                        DATE_FORMAT(penerimaan_cutting.created_at, '%d/%m/%Y %H:%i:%s') LIKE ?
                    ", ["%{$keyword}%"]);
                })->
                order(function ($query) {
                    $query->orderBy('penerimaan_cutting.created_at', 'desc');
                })->toJson();
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
    public function destroy($id){
        $data = PenerimaanCutting::find($id);

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

        $isExist = DB::table('penerimaan_cutting')
            ->where('id_roll', $id)
            ->exists();

        if ($isExist) {
            return response()->json([
                'status' => false,
                'message' => 'Roll sudah pernah diterima!'
            ]);
        }

        $data = DB::connection("mysql_sb")
            ->table('whs_bppb_det')
            ->select(
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
                'whs_bppb_h.style_aktual AS style',
                'masteritem.color AS warna'
            )
            ->leftJoin('whs_bppb_h', 'whs_bppb_h.no_bppb', '=' ,'whs_bppb_det.no_bppb')
            ->leftJoin('masteritem', 'masteritem.id_item', 'whs_bppb_det.id_item')
            ->where('id_roll', $id)
            ->orderBy('id', 'DESC')
            ->first();

        return $data;
    }

    public function exportPenerimaanCutting(Request $request) {
        $from = $request->from;
        $to = $request->to;

        return Excel::download(new PenerimaanCuttingExport($from, $to), 'penerimaan-cutting.xlsx');
    }
}
