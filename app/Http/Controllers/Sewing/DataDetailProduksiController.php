<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Summary\DataProduksi;
use App\Models\Summary\DataDetailProduksi;
use App\Models\Summary\DataDetailProduksiDay;
use App\Http\Requests\UpdateDataDetailProduksiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use DB;

class DataDetailProduksiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $detailProduksi = DataDetailProduksi::with('dataProduksi');

            return
            DataTables::eloquent($detailProduksi)->
                addColumn('order_info', function($row) {
                    $dataProduksi = $row->dataProduksi;
                    $orderQty = $dataProduksi->order_qty_cutting > 0 ? $dataProduksi->order_qty_cutting : $dataProduksi->order_qty;
                    $orderInfo = "<ul class='list-group'>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>WS Number :<br><b>".$dataProduksi->no_ws."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Style :<br><b>".$dataProduksi->no_style."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Group :<br><b>".$dataProduksi->product_group."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Item :<br><b>".$dataProduksi->product_item."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Tanggal Alokasi Awal :<br><b>".$row->tgl_alokasi."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>QTY Order :<br><b>".num($orderQty)."</b></li>";
                    $orderInfo = $orderInfo."</ul>";
                    return $orderInfo;
                })->
                addColumn('production_info', function($row) {
                    $dataProduksi = $row->dataProduksi;
                    $productionInfo = "<ul class='list-group'>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Line :<br><b>".str_replace('_', ' ', strtoupper($row->sewing_line))."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Price :<br><b>".$dataProduksi->kode_mata_uang." ".curr(floatval($dataProduksi->order_cfm_price))."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item ".($row->order_qty_loading <= 0 ? 'text-danger' : '')."'>QTY Loading :<br><b>".num($row->order_qty_loading)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>QTY Output :<br><b>".num($row->order_qty_output)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>QTY Balance :<br><b>".num($row->order_qty_balance)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Earning :<br><b>".$dataProduksi->kode_mata_uang." ".curr($row->earning)."</b></li>";
                    $productionInfo = $productionInfo."</ul>";
                    return $productionInfo;
                })->
                addColumn('data_info', function($row) {
                    $dataProduksi = $row->dataProduksi;
                    $productionInfo = "<ul class='list-group'>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Operator :<br><b>".$row->operator."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Terakhir diubah :<br><b>".$row->updated_at."</b></li>";
                    $productionInfo = $productionInfo."</ul>";
                    return $productionInfo;
                })->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateDataDetailProduksiModal\", [\"data_produksi\"])'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('dataDetailProduksi.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(['order_info', 'production_info', 'data_info', 'action'])->
                order(
                    function ($query) {
                        $query->orderBy('data_detail_produksi.tgl_alokasi', 'desc')->orderBy('data_detail_produksi.updated_at', 'desc')->orderBy('sewing_line', 'asc');
                    }
                )->
                filterColumn('production_info', function($query, $keyword) {
                    $query->whereRaw("LOWER(CAST(sewing_line as TEXT)) like LOWER('%".$keyword."%')");
                })->
                filterColumn('order_info', function($query, $keyword) {
                    $query->whereHas('dataProduksi', function($query) use ($keyword) {
                        $query->whereRaw("(
                            LOWER(CAST(data_produksi.no_ws as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.no_style as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.product_group as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.product_item as TEXT)) LIKE LOWER('%".$keyword."%')
                        )");
                    });
                })->toJson();
        }

        return view('sewing.production.production.detail-production', ['parentPage' => 'produksi', 'page' => 'dashboard-sewing-effy']);
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
     * @param  \App\Models\DataDetailProduksi  $dataDetailProduksi
     * @return \Illuminate\Http\Response
     */
    public function show(DataDetailProduksi $dataDetailProduksi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DataDetailProduksi  $dataDetailProduksi
     * @return \Illuminate\Http\Response
     */
    public function edit(DataDetailProduksi $dataDetailProduksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DataDetailProduksi  $dataDetailProduksi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDataDetailProduksiRequest $request)
    {
        $validatedRequest = $request;

        $dataDetailProduksi = DataDetailProduksi::find($validatedRequest['edit_id']);

        if ($dataDetailProduksi->dataProduksi) {
            $dataProduksi = $dataDetailProduksi->dataProduksi;
            $dataProduksiQty = $dataProduksi->order_qty_cutting > 0 ? $dataProduksi->order_qty_cutting : $dataProduksi->order_qty;

            $orderQtyBalance = $dataProduksiQty - ($dataProduksi->dataDetailProduksi->sum('order_qty_loading')-$dataDetailProduksi->order_qty_loading) ;
        }

        if (($orderQtyBalance-$validatedRequest['edit_order_qty_loading']) >= 0) {
            $updateDataProduksi = DataDetailProduksi::where('id', $validatedRequest['edit_id'])->
            update([
                'order_qty_loading' => $validatedRequest['edit_order_qty_loading'],
                'order_qty_balance' => $validatedRequest['edit_order_qty_balance'],
                'operator' => Auth::user()->username,
            ]);

            if ($updateDataProduksi) {
                $dataProduksiDay = DataDetailProduksiDay::where('data_detail_produksi_id', $validatedRequest['edit_id'])->
                    orderBy('tgl_produksi', 'asc')->get();

                $cumulativeOutput = 0;
                $cumulativeBalance = $validatedRequest['edit_order_qty_loading'];
                $cumulativeEarning = 0;
                foreach ($dataProduksiDay as $day) {
                    $cumulativeOutput += $day->output;
                    $cumulativeBalance -= $day->output;

                    DataDetailProduksiDay::where('id', $day->id)->
                    update([
                        'cumulative_output' => $cumulativeOutput,
                        'cumulative_balance' => $cumulativeBalance,
                    ]);
                }

                $updateDataProduksiQty = DataDetailProduksi::where('id', $validatedRequest['edit_id'])->
                update([
                    'order_qty_output' => $cumulativeOutput,
                    'order_qty_loading' => $validatedRequest['edit_order_qty_loading'],
                    'order_qty_balance' => $validatedRequest['edit_order_qty_loading'] - $cumulativeOutput,
                    'operator' => Auth::user()->username,
                ]);

                return array(
                    'status' => 200,
                    'message' => 'Data detail produksi berhasil diubah',
                    'redirect' => '',
                    'table' => 'detail-produksi-table',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Data detail produksi gagal diubah',
                'redirect' => '',
                'table' => 'detail-produksi-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data detail produksi gagal diubah',
            'redirect' => '',
            'table' => 'detail-produksi-table',
            'additional' => ['edit_order_qty_loading' => ['message' => 'Qty loading melebihi qty balance (qty balance : '.$orderQtyBalance.')', 'value' => $orderQtyBalance]],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DataDetailProduksi  $dataDetailProduksi
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataDetailProduksi $dataDetailProduksi)
    {
        //
    }
}
