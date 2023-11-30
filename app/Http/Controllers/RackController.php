<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Models\RackDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $rackQuery = Rack::selectRaw("
                    rack.id,
                    rack.kode,
                    rack.nama_rak,
                    COUNT(DISTINCT rack_detail.id) total_ruang
                ")->
                leftJoin("rack_detail", "rack_detail.rack_id", "=", "rack.id")->
                groupBy("rack.id");

            return DataTables::eloquent($rackQuery)->
                filterColumn('kode', function ($query, $keyword) {
                    $query->whereRaw("LOWER(kode) LIKE LOWER('%" . $keyword . "%')");
                })->filterColumn('nama_rak', function ($query, $keyword) {
                    $query->whereRaw("LOWER(nama_rak) LIKE LOWER('%" . $keyword . "%')");
                })->order(function ($query) {
                    $query->
                        orderBy('rack.kode', 'desc')->
                        orderBy('rack.updated_at', 'desc');
                })->toJson();
        }

        return view("rack.rack", ["page" => "dashboard-dc"]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('rack.create-rack', ["page" => "dashboard-dc"]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedRequest = $request->validate([
            "nama_rak" => "required|unique:rack,nama_rak,except,id",
            "jumlah_ruang" => "required|min:1",
        ]);

        $lastRack = Rack::select('kode')->orderBy('updated_at', 'desc')->first();
        $rackNumber = $lastRack ? intval(substr($lastRack->kode, -5)) + 1 : 1;
        $rackCode = 'RAK' . sprintf('%05s', $rackNumber);

        $storeRack = Rack::create([
            "kode" => $rackCode,
            "nama_rak" => $validatedRequest['nama_rak'],
        ]);

        if ($validatedRequest['jumlah_ruang'] > 0) {
            $lastRackDetail = RackDetail::select('kode')->orderBy('updated_at', 'desc')->first();
            $rackDetailNumber = $lastRackDetail ? intval(substr($lastRackDetail->kode, -5)) + 1 : 1;

            $rackDetailData = [];
            for ($i = 0; $i < $validatedRequest['jumlah_ruang']; $i++) {
                array_push($rackDetailData, [
                    "kode" => 'DRK' . sprintf('%05s', $rackNumber + $i),
                    "rack_id" => $storeRack->id,
                    "nama_detail_rak" => $validatedRequest['nama_rak'].".".($i+1),
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ]);
            }

            $storeRackDetail = RackDetail::insert($rackDetailData);

            return array(
                "status" => 200,
                "message" => $rackCode,
                "additional" => [],
                "redirect" => ""
            );
        } else {
            return array(
                "status" => 400,
                "message" => "Jumlah ruang tidak bisa 0",
                "additional" => [],
                "redirect" => ""
            );
        }

        return array(
            "status" => 400,
            "message" => "Terjadi kesalahan",
            "additional" => [],
            "redirect" => ""
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function show(Rack $rack)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function edit(Rack $rack)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rack $rack)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rack  $rack
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rack $rack)
    {
        //
    }

    public function rackDetail(Request $request) {
        $racks = Rack::all();

        return view('rack.rack-detail', ['page' => 'dashboard-dc', 'racks' => $racks]);
    }
}
