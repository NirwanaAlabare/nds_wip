<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use App\Models\Summary\MasterBuyer;
use App\Models\Summary\MasterSupplierSB;
use App\Models\Summary\DataProduksi;
use App\Models\Summary\ActCostingSB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\UpdateBuyerRequest;

class MasterBuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $masterBuyer = MasterBuyer::query();

            return
            DataTables::eloquent($masterBuyer)->
                addIndexColumn()->
                addColumn('action', function($row) {
                    $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateBuyerModal\")'>Edit</a>";
                    $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('buyer.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                    return $btn;
                })->
                rawColumns(['action'])->
                order(
                    function ($query) {
                        $query->orderBy('nama_buyer', 'asc');
                    }
                )->toJson();
        }

        return view('sewing.master.master-buyer', ['parentPage' => 'master', 'page' => 'master-buyer']);
    }

    /**
     * Fetch Data.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $masterBuyer = MasterBuyer::query();

        return
        DataTables::eloquent($masterBuyer)->
            addIndexColumn()->
            addColumn('action', function($row) {
                $btn = "<a href='javascript:void(0)' class='edit btn btn-info btn-sm mx-1 my-1' data='".$row."' onclick='editData(this, \"updateBuyerModal\")'>Edit</a>";
                $btn = $btn."<a href='javascript:void(0)' class='edit btn btn-danger btn-sm mx-1 my-1' data='".$row."' data-url='".route('buyer.destroyData', ['id' => $row->id])."' onclick='deleteData(this)'>Delete</a>";
                return $btn;
            })->
            rawColumns(['action'])->
            order(
                function ($query) {
                    if (request()->has('updated_at')) {
                        $query->orderBy('updated_at', 'desc');
                    }
                }
            )->toJson();
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
     * @param  \App\Models\MasterKaryawan  $masterBuyer
     * @return \Illuminate\Http\Response
     */
    public function show(MasterKaryawan $masterBuyer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterBuyer  $masterBuyer
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterBuyer $masterBuyer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterBuyer  $masterBuyer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBuyerRequest $request)
    {
        $validatedRequest = $request;

        $this->validate($validatedRequest,[
            'edit_kode_buyer'=>'unique:master_buyer,kode_buyer,'.$request['edit_id'],
        ]);

        $updateBuyer = MasterBuyer::where('id', $validatedRequest['edit_id'])->
            update([
                'kode_buyer' => $validatedRequest['edit_kode_buyer'],
                'nama_buyer' => $validatedRequest['edit_nama_buyer'],
                'negara' => $validatedRequest['edit_negara'],
                'brand' => $validatedRequest['edit_brand'],
                'operator' => Auth::user()->username,
            ]);

        if ($updateBuyer) {
            return array(
                'status' => 200,
                'message' => 'Buyer berhasil diubah',
                'redirect' => '',
                'table' => 'buyer-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Buyer gagal diubah',
            'redirect' => '',
            'table' => 'buyer-table',
            'additional' => [],
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterBuyer  $masterBuyer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $destroyBuyer = MasterBuyer::find($id)->delete();

        if ($destroyBuyer) {
            return array(
                'status' => 200,
                'message' => 'Buyer berhasil dihapus',
                'redirect' => '',
                'table' => 'buyer-table',
                'additional' => [],
            );
        }

        return array(
            'status' => 400,
            'message' => 'Buyer gagal dihapus',
            'redirect' => '',
            'table' => 'buyer-table',
            'additional' => [],
        );
    }

    /**
     * Fetch data from another source.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function otherSource(Request $request)
    {
        if ($request->ajax()) {
            $buyerOtherSource = MasterSupplierSB::on('mysql_sb')->
                selectRaw('
                    Id_Supplier,
                    UPPER(supplier_code) supplier_code,
                    UPPER(Supplier) Supplier,
                    UPPER(tipe_sup) tipe_sup,
                    UPPER(country) country,
                    UPPER(short_name) short_name
                ')->
                where('tipe_sup','C')->
                orderBy("Supplier", 'asc');

            return DataTables::eloquent($buyerOtherSource)->
                order(
                    function ($query) {
                        if (request()->has('updated_at')) {
                            $query->orderBy('updated_at', 'desc');
                        }
                    }
                )->toJson();
        }

        return view('sewing.master.master-buyer', ['page' => 'dashboard-sewing-effy']);
    }

    /**
     * Transfer data from another database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transfer(Request $request)
    {
        $success = [];
        $fail = [];
        $exist = [];

        foreach ($request->buyerTransfer as $req) {
            $isExist = MasterBuyer::where('other_source_id', $req['id_buyer'])->count();

            if ($isExist < 1) {
                $transferBuyer = MasterBuyer::create([
                    "other_source_id" => $req['id_buyer'],
                    "kode_buyer" => $req['kode_buyer'],
                    "nama_buyer" => $req['nama_buyer'],
                    "negara" => $req['negara'],
                    "brand" => $req['brand'],
                    "operator" => Auth::user()->username,
                ]);

                if ($transferBuyer) {
                    $buyerWs = ActCostingSB::select('act_costing.kpno')->where('id_buyer', $req['id_buyer'])->groupBy('act_costing.kpno')->get();

                    foreach ($buyerWs as $buyer) {
                        DataProduksi::where('no_ws', $buyer->kpno)->
                            update([
                                'buyer_id' => $transferBuyer->id
                            ]);
                    }

                    array_push($success, ['kode_buyer' => $req['kode_buyer'], 'nama_buyer' => $req['nama_buyer']]);
                } else {
                    array_push($fail, ['kode_buyer' => $req['kode_buyer'], 'nama_buyer' => $req['nama_buyer']]);
                }
            } else {
                array_push($exist, ['kode_buyer' => $req['kode_buyer'], 'nama_buyer' => $req['nama_buyer']]);
            }
        }

        if (count($success) > 0) {
            return array(
                'status' => 200,
                'message' => 'Transfer data berhasil',
                'redirect' => '',
                'table' => 'buyer-table',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        } else {
            return array(
                'status' => 400,
                'message' => 'Hasil transfer kosong',
                'redirect' => '',
                'table' => 'buyer-table',
                'additional' => ["success" => $success, "fail" => $fail, "exist" => $exist],
            );
        }
    }
}
