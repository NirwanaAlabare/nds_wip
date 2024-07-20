<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Summary\DataDetailProduksiDay;
use App\Models\Summary\MasterKursBi;
use App\Models\Summary\MasterKaryawan;
use App\Http\Requests\UpdateDataDetailProduksiDayRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use DB;

class DataDetailProduksiDayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $detailProduksiDay = DataDetailProduksiDay::with(['dataDetailProduksi', 'dataDetailProduksi.dataProduksi', 'masterKursBi', 'chief', 'leader', 'adm']);

        $chiefs = MasterKaryawan::selectRaw('master_karyawan.id, master_karyawan.nik, master_karyawan.nama')->leftJoin('master_jabatan', 'master_jabatan.id', '=', 'master_karyawan.jabatan_id')->
            where('master_jabatan.kode_jabatan', 'CHI')->get();

        $leaders = MasterKaryawan::selectRaw('master_karyawan.id, master_karyawan.nik, master_karyawan.nama')->leftJoin('master_jabatan', 'master_jabatan.id', '=', 'master_karyawan.jabatan_id')->
            where('master_jabatan.kode_jabatan', 'LEA')->get();

        $administrators = MasterKaryawan::selectRaw('master_karyawan.id, master_karyawan.nik, master_karyawan.nama')->leftJoin('master_jabatan', 'master_jabatan.id', '=', 'master_karyawan.jabatan_id')->
            where('master_jabatan.kode_jabatan', 'ADM')->get();

        if ($request->ajax()) {
            return
            DataTables::eloquent($detailProduksiDay)->
                addColumn('tim_produksi', function($row) {
                    $chief = $row->chief_enroll_id ? $row->chief->nama : '-';
                    $leader = $row->leader_enroll_id ? $row->leader->nama : '-';
                    $adm = $row->adm_enroll_id ? $row->adm->nama : '-';

                    $timProduksi = "<ul class='list-group'>";
                    $timProduksi = $timProduksi."<li class='list-group-item ".($chief == '-' ? 'text-danger' : '')."'>Chief :<br><b>".$chief."</b></li>";
                    $timProduksi = $timProduksi."<li class='list-group-item ".($leader == '-' ? 'text-danger' : '')."'>Leader :<br><b>".$leader."</b></li>";
                    $timProduksi = $timProduksi."<li class='list-group-item ".($adm == '-' ? 'text-danger' : '')."'>Admin :<br><b>".$adm."</b></li>";
                    $timProduksi = $timProduksi."</ul>";
                    return $timProduksi;
                })->
                addColumn('plan_info', function($row) {
                    $dataProduksi = $row->dataDetailProduksi->dataProduksi;
                    $dataDetailProduksi = $row->dataDetailProduksi;

                    $planInfo = "<ul class='list-group'>";
                    $planInfo = $planInfo."<li class='list-group-item'>WS Number :<br><b>".$dataProduksi->no_ws."</b></li>";
                    $planInfo = $planInfo."<li class='list-group-item'>Line :<br><b>".str_replace('_', ' ', strtoupper($dataDetailProduksi->sewing_line))."</b></li>";
                    $planInfo = $planInfo."<li class='list-group-item'>SMV :<br><b>".num($row->smv)."</b></li>";
                    $planInfo = $planInfo."<li class='list-group-item'>Man Power :<br><b>".num($row->man_power)."</b></li>";
                    $planInfo = $planInfo."<li class='list-group-item'>Jam Aktual :<br><b>".num($row->jam_aktual)."</b></li>";
                    $planInfo = $planInfo."<li class='list-group-item'>Mins Avail. :<br><b>".num($row->mins_avail)."</b></li>";
                    $planInfo = $planInfo."<li class='list-group-item'>Mins Prod. :<br><b>".num($row->mins_prod)."</b></li>";
                    $planInfo = $planInfo."</ul>";
                    return $planInfo;
                })->
                addColumn('production_info', function($row) {
                    $productionInfo = "<ul class='list-group'>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Tanggal :<br><b>".$row->tgl_produksi."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Target :<br><b>".num($row->target)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Output :<br><b>".num($row->output)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Cumulative Output :<br><b>".num($row->cumulative_output)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Cumulative Balance :<br><b>".num($row->cumulative_balance)."</b></li>";
                    $productionInfo = $productionInfo."<li class='list-group-item'>Efficiency :<br><b>".$row->efficiency." %</b></li>";
                    $productionInfo = $productionInfo."</ul>";
                    return $productionInfo;
                })->
                addColumn('earning_info', function($row) {
                    $kodeMataUang = $row->dataDetailProduksi->dataProduksi ? $row->dataDetailProduksi->dataProduksi->kode_mata_uang : '-';
                    $kursBi = $kodeMataUang != 'IDR' ? ($row->masterKursBi ? 'IDR '.curr(floatval($row->masterKursBi->kurs_tengah)) : '-') : '-';
                    $price = $row->dataDetailProduksi->dataProduksi ? $kodeMataUang.' '.curr(floatval($row->dataDetailProduksi->dataProduksi->order_cfm_price)) : '-';

                    $earningInfo = "<ul class='list-group'>";
                    $earningInfo = $earningInfo."<li class='list-group-item'>Kurs BI : <br><b>".$kursBi."</b></li>";
                    $earningInfo = $earningInfo."<li class='list-group-item'>Price : <br><b>".$price."</b></li>";
                    $earningInfo = $earningInfo."<li class='list-group-item'>Earning : <br><b>".$kodeMataUang." ".curr(floatval($row->earning))."</b></li>";
                    $earningInfo = $earningInfo."<li class='list-group-item'>Cumulative Earning : <br><b>".$kodeMataUang." ".curr(floatval($row->cumulative_earning))."</b></li>";
                    $earningInfo = $earningInfo."<li class='list-group-item'>Kurs Earning : <br><b>IDR ".curr(floatval($row->kurs_earning))."</b></li>";
                    $earningInfo = $earningInfo."</ul>";
                    return $earningInfo;
                })->
                addColumn('data_info', function($row) {
                    $dataInfo = "<ul class='list-group'>";
                    $dataInfo = $dataInfo."<li class='list-group-item'>Operator : <br><b>".$row->operator."</b></li>";
                    $dataInfo = $dataInfo."<li class='list-group-item'>Terakhir Diubah :<br><b>".$row->updated_at."</b></li>";
                    $dataInfo = $dataInfo."</ul>";
                    return $dataInfo;
                })->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateDataDetailProduksiDayModal\", [\"data_detail_produksi.data_produksi\",\"data_detail_produksi\", \"chief\", \"leader\", \"adm\"])'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('dataDetailProduksiDay.destroyData', ['id' => $row->id, ])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(
                    ['tim_produksi', 'plan_info', 'production_info', 'earning_info', 'data_info', 'action']
                )->
                filter(function ($query) {
                    if (request()->has('date') && request('date') != '') {
                        $query->where('data_detail_produksi_day.tgl_produksi', "=", request('date'));
                    }
                }, true)->
                filterColumn('production_info', function($query, $keyword) {
                    $query->whereHas('dataDetailProduksi', function($query) use ($keyword) {
                        $query->whereRaw("LOWER(CAST(data_detail_produksi.sewing_line as TEXT)) like LOWER('%".$keyword."%')");
                    });
                })->
                filterColumn('plan_info', function($query, $keyword) {
                    $query->whereHas('dataDetailProduksi.dataProduksi', function($query) use ($keyword) {
                        $query->whereRaw("(
                            LOWER(CAST(data_produksi.no_ws as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.no_style as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.product_group as TEXT)) LIKE LOWER('%".$keyword."%') OR
                            LOWER(CAST(data_produksi.product_item as TEXT)) LIKE LOWER('%".$keyword."%')
                        )");
                    });
                })->
                order(function ($query) {
                    $query->orderBy('data_detail_produksi_day.tgl_produksi', 'desc')->orderBy('data_detail_produksi_day.updated_at', 'desc');
                })->toJson();
        }

        return view('sewing.production.detail-production-day', ['parentPage' => 'produksi', 'page' => 'dashboard-sewing-effy', 'chiefs' => $chiefs, 'leaders' => $leaders, 'administrators' => $administrators]);
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
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function show(DataDetailProduksiDay $dataDetailProduksiDay)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function edit(DataDetailProduksiDay $dataDetailProduksiDay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDataDetailProduksiDayRequest $request)
    {
        $validatedRequest = $request;

        $updateDataDetailProduksiDay = DataDetailProduksiDay::leftJoin('data_detail_produksi', 'data_detail_produksi.id', '=', 'data_detail_produksi_day.data_detail_produksi_id')->
            where('data_detail_produksi.sewing_line', $validatedRequest['edit_sewing_line'])->
            where('data_detail_produksi_day.tgl_produksi', $validatedRequest['edit_tgl_produksi'])->
            update([
                'chief_enroll_id' => $validatedRequest['edit_chief_enroll_id'],
                'leader_enroll_id' => $validatedRequest['edit_leader_enroll_id'],
                'adm_enroll_id' => $validatedRequest['edit_adm_enroll_id'],
                'operator' => Auth::user()->username,
            ]);

        if ($updateDataDetailProduksiDay) {
            return array(
                'status' => 200,
                'message' => 'Data hari produksi berhasil diubah',
                'redirect' => '',
                'table' => 'detail-produksi-day-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Data hari produksi gagal diubah',
            'redirect' => '',
            'table' => 'detail-produksi-day-table',
            'additional' => ['edit_order_qty_loading' => ['message' => 'Qty loading melebihi qty balance (qty balance : '.$orderQtyBalance.')', 'value' => $orderQtyBalance]],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DataDetailProduksiDay  $dataDetailProduksiDay
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataDetailProduksiDay $dataDetailProduksiDay)
    {
        //
    }
}
