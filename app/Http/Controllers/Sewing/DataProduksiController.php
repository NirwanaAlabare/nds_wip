<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Summary\DataProduksi;
use App\Models\Summary\DataDetailProduksi;
use App\Models\Summary\DataDetailProduksiDay;
use App\Models\Summary\MasterBuyer;
use App\Models\Summary\MasterKursBi;
use App\Models\Summary\ActCostingSB;
use App\Models\Summary\MasterPlanSB;
use App\Http\Requests\UpdateDataProduksiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use DB;

class DataProduksiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dataProduksi = DataProduksi::with(['masterBuyer', 'dataDetailProduksi']);

            return
            DataTables::eloquent($dataProduksi)->
                addColumn('tanggal', function($row) {
                    $tanggal = "<ul class='list-group'>";
                    $tanggal = $tanggal."<li class='list-group-item'>Order :<br><b>".$row->tanggal_order."</b></li>";
                    $tanggal = $tanggal."<li class='list-group-item'>Delivery :<br><b>".$row->tanggal_delivery."</b></li>";
                    $tanggal = $tanggal."</ul>";
                    return $tanggal;
                })->
                addColumn('order_info', function($row) {
                    $namaBuyer = $row->masterBuyer ? $row->masterBuyer->nama_buyer : '-';

                    $orderInfo = "<ul class='list-group'>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Buyer :<br><b>".$namaBuyer."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>WS Number :<br><b>".$row->no_ws."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Style :<br><b>".$row->no_style."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Group :<br><b>".$row->product_group."</b></li>";
                    $orderInfo = $orderInfo."<li class='list-group-item'>Item :<br><b>".$row->product_item."</b></li>";
                    $orderInfo = $orderInfo."</ul>";
                    return $orderInfo;
                })->
                addColumn('quantity_info', function($row) {
                    $orderQtyLoad = $row->dataDetailProduksi->sum('order_qty_loading');
                    $orderQtyCutting = $row->order_qty_cutting > 0 ? $row->order_qty_cutting : $row->order_qty;
                    $orderQtyLoadBalance = $orderQtyCutting - $orderQtyLoad;
                    $lineAllocation = '-';
                    foreach ($row->dataDetailProduksi as $data) {
                        $lineAllocation = $lineAllocation == '-' ? str_replace('_', ' ',strtoupper($data->sewing_line)) : $lineAllocation.', '.str_replace('_', ' ',strtoupper($data->sewing_line));
                    }

                    $productionInfo = "<ul class='list-group'>";
                    $productionInfo = $productionInfo."<li class='list-group-item w-100 overflow-auto'>Line :<br><b>".$lineAllocation."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Satuan :<br><b>".$row->order_satuan."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Qty Order :<br><b>".num($row->order_qty)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item ".($row->order_qty_cutting <= 0 ? 'text-danger' : '')."'>Qty Cutting :<br><b>".num($row->order_qty_cutting)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Qty Load :<br><b>".num($orderQtyLoad)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Qty Load Balance :<br><b>".num($orderQtyCutting - $orderQtyLoad)."</b></li>";
                    $productionInfo = $productionInfo."</ul>";
                    return $productionInfo;
                })->
                addColumn('production_info', function($row) {
                    $productionInfo = "<ul class='list-group'>";
                    $productionInfo = $productionInfo."<li class='list-group-item ".($row->order_cfm_price <= 0 ? 'text-danger' : '')."'>Price :<br><b>".$row->kode_mata_uang." ".curr($row->order_cfm_price)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Qty Output :<br><b>".num($row->order_qty_output)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Qty Output Balance :<br><b>".num($row->order_qty_balance)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Earning :<br><b>".$row->kode_mata_uang." ".curr($row->earning)."</b></li>";
                    $productionInfo = $productionInfo."</ul>";
                    return $productionInfo;
                })->
                addColumn('data_info', function($row) {
                    $productionInfo = "<ul class='list-group'>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Operator :<br><b>".$row->operator."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Terakhir diubah :<br><b>".$row->updated_at."</b></li>";
                    $productionInfo = $productionInfo."</ul>";
                    return $productionInfo;
                })->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateProduksiModal\", [\"master_buyer\", \"data_detail_produksi\"])'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('dataProduksi.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(['tanggal', 'order_info', 'quantity_info', 'production_info', 'data_info', 'action'])->
                order(
                    function ($query) {
                        $query->orderBy('data_produksi.tanggal_order', 'desc')->
                            orderBy('data_produksi.no_ws', 'asc')->
                            orderBy('data_produksi.updated_at', 'desc');
                    }
                )->
                filterColumn('tanggal', function($query, $keyword) {
                    $query->whereRaw("
                        LOWER(CAST(data_produksi.tanggal_order as TEXT)) LIKE LOWER('%".$keyword."%') OR
                        LOWER(CAST(data_produksi.tanggal_delivery as TEXT)) LIKE ('%".$keyword."%')
                    ");
                })->
                filterColumn('order_info', function($query, $keyword) {
                    $query->whereHas('masterBuyer', function($query) use ($keyword) {
                        $query->whereRaw("
                            LOWER(CAST(master_buyer.nama_buyer as TEXT)) LIKE LOWER('%".$keyword."%')
                        ");
                    });

                    $query->whereRaw("
                            LOWER(CAST(data_produksi.tanggal_delivery as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.no_ws as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.no_style as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.product_group as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.product_item as TEXT)) LIKE LOWER('%".$keyword."%')
                        ");
                })->
                toJson();
        }

        $lastUpdate = DataProduksi::selectRaw('MAX(updated_at) as last_update')->first();

        return view('sewing.production.production', ['parentPage' => 'produksi', 'page' => 'dashboard-sewing-effy', 'lastUpdate' => $lastUpdate]);
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
     * @param  \App\Models\Summary\DataProduksi  $dataProduksi
     * @return \Illuminate\Http\Response
     */
    public function show(DataProduksi $dataProduksi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Summary\DataProduksi  $dataProduksi
     * @return \Illuminate\Http\Response
     */
    public function edit(DataProduksi $dataProduksi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Summary\DataProduksi  $dataProduksi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDataProduksiRequest $request)
    {
        $validatedRequest = $request;

        $dataProduksi = DataProduksi::find($validatedRequest['edit_id']);

        if ($dataProduksi->dataDetailProduksi) {
            $orderQtyBalance = $validatedRequest['edit_order_qty_cutting'] - $dataProduksi->dataDetailProduksi->sum('qty_loading');
        }

        if ($orderQtyBalance >= 0) {
            $updateDataProduksi = DataProduksi::where('id', $validatedRequest['edit_id'])->
                update([
                    'order_cfm_price' => $validatedRequest['edit_order_cfm_price'],
                    'order_qty_cutting' => $validatedRequest['edit_order_qty_cutting'],
                    'order_qty_balance' => $validatedRequest['edit_order_qty_balance'],
                    'operator' => Auth::user()->username,
                ]);

            if ($updateDataProduksi) {

                $dataDetailProduksi = DataDetailProduksi::where('data_produksi_id', $validatedRequest['edit_id'])->
                    orderBy('tgl_alokasi', 'asc')->
                    get();

                $cumulativeEarningOrder = 0;
                foreach ($dataDetailProduksi as $detail) {
                    $dataDetailProduksiDay = DataDetailProduksiDay::where('data_detail_produksi_id', $detail->id)->
                        orderBy('tgl_produksi', 'asc')->
                        get();

                    $cumulativeEarningDay = 0;
                    foreach ($dataDetailProduksiDay as $day) {
                        $kursBi = MasterKursBi::where('tanggal_kurs_bi', $day->tgl_produksi)->first();
                        $earning = $day->output * $validatedRequest['edit_order_cfm_price'];
                        $cumulativeEarningDay += $earning;
                        $kursEarning = $dataProduksi->kode_mata_uang != 'IDR' ? ($kursBi ? ($earning * $kursBi->kurs_tengah) : 0) : $earning;

                        DataDetailProduksiDay::where('id', $day->id)->
                            update([
                                'earning' => $earning,
                                'cumulative_earning' => $cumulativeEarningDay,
                                'kurs_earning' => $kursEarning
                            ]);
                    }
                    $cumulativeEarningOrder += $cumulativeEarningDay;
                    DataDetailProduksi::where('id', $detail->id)->
                        update([
                            'earning' => $cumulativeEarningDay
                        ]);
                }

                DataProduksi::where('id', $validatedRequest['edit_id'])->
                    update([
                        'earning' => $cumulativeEarningOrder
                    ]);

                return array(
                    'status' => 200,
                    'message' => 'Data produksi berhasil diubah',
                    'redirect' => '',
                    'table' => 'produksi-table',
                    'additional' => [],
                );
            }

            return array(
                'status' => 400,
                'message' => 'Data produksi gagal diubah',
                'redirect' => '',
                'table' => 'produksi-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data produksi gagal diubah',
            'redirect' => '',
            'table' => 'produksi-table',
            'additional' => ['edit_order_qty_cutting' => ['message' => 'Qty cutting harus melebihi qty loading (qty loading : '.$dataProduksi->dataDetailProduksi->sum('qty_loading').')', 'value' => $dataProduksi->dataDetailProduksi->sum('qty_loading')]],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Summary\DataProduksi  $dataProduksi
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        //
    }

    /**
     * Transfer data from another database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $lastUpdate = DataProduksi::selectRaw('MAX(updated_at) as last_update')->first();

        ini_set('max_execution_time', 3600);

        $success = [];
        $fail = [];
        $exist = [];

        $dataToTransfer = MasterPlanSB::on('mysql_sb')->
            selectRaw('
                act_costing.id id,
                act_costing.id_buyer,
                act_costing.cost_date tanggal_order,
                act_costing.deldate tanggal_delivery,
                act_costing.kpno no_ws,
                act_costing.styleno no_style,
                masterproduct.product_group product_group,
                masterproduct.product_item product_item,
                act_costing.unit order_satuan,
                master_plan.smv smv,
                act_costing.curr kode_mata_uang,
                COALESCE(act_costing.qty, 0) order_qty,
                ROUND((act_costing_mfg.price * act_costing_mfg.cons) + ((act_costing_mfg.price * act_costing_mfg.cons) * (act_costing_mfg.allowance/100)), 4) order_cfm_price
            ')->
            join('act_costing','master_plan.id_ws','=','act_costing.id')->
            join('masterproduct','act_costing.id_product','=','masterproduct.id')->
            join('act_costing_mfg','act_costing_mfg.id_act_cost','=','act_costing.id')->
            join('mastercf','mastercf.id','=','act_costing_mfg.id_item')->
            whereRaw("
                act_costing.status = 'CONFIRM'
                AND mastercf.cfcode = 'CMT'
                AND SUBSTR(master_plan.tgl_plan, 1, 7)
                    BETWEEN
                        SUBSTR(".($lastUpdate ? "'".$lastUpdate->last_update."'" : "DATE_SUB( NOW(), INTERVAL 3 MONTH )").", 1, 7) AND SUBSTR(DATE_ADD( NOW(), INTERVAL 0 MONTH ), 1, 7)
            ")->
            orderBy('act_costing.dateinput','asc')->
            groupBy(
                'act_costing.id',
                'act_costing.id_buyer',
                'act_costing.cost_date',
                'act_costing.deldate',
                'act_costing.kpno',
                'act_costing.styleno',
                'masterproduct.product_group',
                'masterproduct.product_item',
                'act_costing.unit',
                'master_plan.smv',
                'act_costing.curr',
                'act_costing_mfg.price',
                'act_costing_mfg.cons',
                'act_costing_mfg.allowance',
                'act_costing.qty'
            )->
            get();

        // Data Produksi
        foreach ($dataToTransfer as $data) {
            $dataBuyer = MasterBuyer::where('other_source_id', $data->id_buyer)->first();
            $dataProduksi = DataProduksi::where('no_ws', $data->no_ws)->first();
            $dataProduksiArray = [
                'buyer_id' => $dataBuyer ? $dataBuyer->id : null,
                'tanggal_order' => $data->tanggal_order,
                'tanggal_delivery' => $data->tanggal_delivery,
                'no_ws' => $data->no_ws,
                'no_style' => $data->no_style,
                'product_group' => $data->product_group,
                'product_item' => $data->product_item,
                'order_satuan' => $data->order_satuan,
                'smv' => $data->smv,
                'kode_mata_uang' => $data->kode_mata_uang,
                'order_cfm_price' => $data->order_cfm_price,
                'order_cfm_price_rupiah' => $data->kode_mata_uang == 'IDR' ? $data->order_cfm_price : 0,
                'order_cfm_price_dollar' => $data->kode_mata_uang == 'USD' ? $data->order_cfm_price : 0,
                'order_qty' => $data->order_qty,
                'order_qty_output' => 0,
                'order_qty_output_rft' => 0,
                'order_qty_balance' => $data->order_qty,
                'earning' => 0,
                'operator' => Auth::user()->username,
            ];
            $transferDataProduksi = DataProduksi::updateOrCreate(['no_ws' => $data->no_ws], $dataProduksiArray);

            $actCostingId = $data->id;
            $dataDetailProduksi = MasterPlanSB::on('mysql_sb')->
                selectRaw('
                    master_plan.id_ws,
                    master_plan.sewing_line,
                    MIN(master_plan.tgl_plan) tgl_alokasi
                ')->
                where('master_plan.id_ws', $actCostingId)->
                where("master_plan.cancel", 'N')->
                groupBy('master_plan.id_ws', 'master_plan.sewing_line')->
                orderByRaw('MIN(master_plan.tgl_plan) asc')->
                get();

            // Kumulatif Order
            $cumulativeOutputOrder = 0;
            $cumulativeOutputRftOrder = 0;
            $cumulativeEarningOrder = 0;
            if (count($dataDetailProduksi) > 0) {
                // Detail Produksi
                foreach ($dataDetailProduksi as $dataDetail) {
                    $transferDataDetailProduksi = DataDetailProduksi::updateOrCreate([
                        'data_produksi_id' => $transferDataProduksi->id,
                        'sewing_line' => $dataDetail->sewing_line
                    ],[
                        'data_produksi_id' => $transferDataProduksi->id,
                        'sewing_line' => $dataDetail->sewing_line,
                        'order_qty_output' => 0,
                        'order_qty_output_rft' => 0,
                        'order_qty_balance' => 0,
                        'earning' => 0,
                        'tgl_alokasi' => $dataDetail->tgl_alokasi,
                        'operator' => Auth::user()->username,
                    ]);

                    $dataDetailProduksiDay = MasterPlanSB::on('mysql_sb')->
                        selectRaw('
                            master_plan.tgl_plan,
                            MAX(master_plan.man_power) man_power,
                            SUM(master_plan.jam_kerja) jam_kerja,
                            SUM(master_plan.plan_target) plan_target,
                            AVG(master_plan.smv) smv,
                            SUM(((master_plan.jam_kerja * 60) * master_plan.man_power)) mins_avail,
                            SUM((master_plan.smv * IFNULL(outputs.rft, 0))) mins_prod,
                            SUM(IFNULL(outputs.rft, 0)) output,
                            SUM(IFNULL(rfts.rft, 0)) output_rft
                        ')->
                        leftJoin(
                            DB::raw("
                                (
                                    SELECT
                                        master_plan.id master_plan_id,
                                        count( outputs.id ) rft,
                                        master_plan.tgl_plan,
                                        master_plan.sewing_line
                                    FROM
                                        output_rfts outputs
                                        INNER JOIN master_plan ON master_plan.id = outputs.master_plan_id
                                    WHERE
                                        `master_plan`.`id_ws` = '".$dataDetail->id_ws."'
                                        AND `master_plan`.`sewing_line` = '".$dataDetail->sewing_line."'
                                        AND `master_plan`.`cancel` = 'N'
                                    GROUP BY
                                        master_plan.id,
                                        master_plan.tgl_plan,
                                        master_plan.sewing_line
                                ) as outputs
                            "),
                            "master_plan.id", "=", "outputs.master_plan_id"
                        )->
                        leftJoin(
                            DB::raw("
                                (
                                    SELECT
                                        master_plan.id master_plan_id,
                                        count( outputs.id ) rft,
                                        master_plan.tgl_plan,
                                        master_plan.sewing_line
                                    FROM
                                        output_rfts outputs
                                        INNER JOIN master_plan ON master_plan.id = outputs.master_plan_id
                                    WHERE
                                        outputs.status = 'NORMAL'
                                        AND `master_plan`.`id_ws` = '".$dataDetail->id_ws."'
                                        AND `master_plan`.`sewing_line` = '".$dataDetail->sewing_line."'
                                        AND `master_plan`.`cancel` = 'N'
                                    GROUP BY
                                        master_plan.id,
                                        master_plan.tgl_plan,
                                        master_plan.sewing_line
                                ) as rfts
                            "),
                            "master_plan.id", "=", "rfts.master_plan_id"
                        )->
                        whereRaw("master_plan.id_ws = '".$dataDetail->id_ws."'")->
                        whereRaw("master_plan.sewing_line = '".$dataDetail->sewing_line."'")->
                        whereRaw("master_plan.cancel = 'N'")->
                        groupBy(
                            'master_plan.id_ws',
                            'master_plan.tgl_plan',
                            'master_plan.sewing_line'
                        )->
                        orderBy('master_plan.tgl_plan', 'asc')->
                        get();

                    // Kumulatif Harian
                    $cumulativeOutputDay = 0;
                    $cumulativeOutputRftDay = 0;
                    $cumulativeEarningDay = 0;
                    $cumulativeBalanceDay = $dataDetail->order_qty_loading ? $dataDetail->order_qty_loading : 0;
                    if (count($dataDetailProduksiDay) > 0) {
                        // Data Detail Produksi Day
                        foreach ($dataDetailProduksiDay as $dataDetailDay) {
                            $kursBi = MasterKursBi::where('tanggal_kurs_bi', $dataDetailDay->tgl_plan)->first();

                            $efficiency = $dataDetailDay->mins_avail > 0 ? round(($dataDetailDay->mins_prod/$dataDetailDay->mins_avail)*100, 2) : 0;
                            $cumulativeOutputDay += $dataDetailDay->output;
                            $cumulativeOutputRftDay += $dataDetailDay->output_rft;
                            $cumulativeBalanceDay = $cumulativeBalanceDay > 0 ? ($cumulativeBalanceDay - $dataDetailDay->output) : $cumulativeBalanceDay;

                            $cfm_price = $data->order_cfm_price;
                            $earning = $dataDetailDay->output*$cfm_price;
                            $kursEarning = $data->kode_mata_uang != 'IDR' ? ($kursBi ? $earning*$kursBi->kurs_tengah : 0) : $earning;
                            $cumulativeEarningDay += $earning;

                            $transferDataDetailProduksiDay = DataDetailProduksiDay::updateOrCreate([
                                'data_detail_produksi_id' => $transferDataDetailProduksi->id,
                                'tgl_produksi' => $dataDetailDay->tgl_plan,
                            ],[
                                'data_detail_produksi_id' => $transferDataDetailProduksi->id,
                                'kurs_bi_id' => $kursBi ? $kursBi->id : null,
                                'smv' => $dataDetailDay->smv,
                                'man_power' => $dataDetailDay->man_power,
                                'jam_aktual' => $dataDetailDay->jam_kerja,
                                'mins_avail' => $dataDetailDay->mins_avail,
                                'mins_prod' => $dataDetailDay->mins_prod,
                                'target' => $dataDetailDay->plan_target,
                                'output' => $dataDetailDay->output < 1 ? 0 : $dataDetailDay->output,
                                'output_rft' => $dataDetailDay->output_rft < 1 ? 0 : $dataDetailDay->output_rft,
                                'earning' => $earning,
                                'kurs_earning' => $kursEarning,
                                'cumulative_output' => $cumulativeOutputDay,
                                'cumulative_output_rft' => $cumulativeOutputRftDay,
                                'cumulative_balance' => $cumulativeBalanceDay,
                                'cumulative_earning' => $cumulativeEarningDay,
                                'efficiency' => $efficiency,
                                'tgl_produksi' => $dataDetailDay->tgl_plan,
                                'operator' => Auth::user()->username,
                            ]);
                        }

                        $calculateDataDetailProduksi = DataDetailProduksi::where('id', $transferDataDetailProduksi->id)->
                        update([
                            'data_produksi_id' => $transferDataProduksi->id,
                            'sewing_line' => $dataDetail->sewing_line,
                            'order_qty_loading' => 0,
                            'order_qty_output' => $cumulativeOutputDay,
                            'order_qty_output_rft' => $cumulativeOutputRftDay,
                            'order_qty_balance' => ($transferDataDetailProduksi->order_qty_loading > 0 ? ($transferDataDetailProduksi->order_qty_loading - $cumulativeOutputDay) : 0),
                            'earning' => $cumulativeEarningDay,
                            'tgl_alokasi' => $dataDetail->tgl_alokasi,
                            'operator' => Auth::user()->username,
                        ]);

                        $cumulativeOutputOrder += $cumulativeOutputDay;
                        $cumulativeOutputRftOrder += $cumulativeOutputRftDay;
                        $cumulativeEarningOrder += $cumulativeEarningDay;
                    }
                }

                $calculateDataProduksi = DataProduksi::where('id', $transferDataProduksi->id)->
                update([
                    'order_qty' => $data->order_qty,
                    'order_qty_output' => $cumulativeOutputOrder,
                    'order_qty_output_rft' => $cumulativeOutputRftOrder,
                    'order_qty_cutting' => 0,
                    'order_qty_balance' => ($data->order_qty_cutting > 0 ? ($data->order_qty_cutting - $cumulativeOutputOrder) : ($data->order_qty - $cumulativeOutputOrder)),
                    'earning' => $cumulativeEarningOrder,
                    'operator' => Auth::user()->username,
                ]);
            }

            array_push($success, $data->no_ws);
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Hasil transfer',
                'redirect' => '',
                'table' => 'produksi-table',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Hasil transfer kosong',
                'redirect' => '',
                'table' => 'produksi-table',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }
    }
}
